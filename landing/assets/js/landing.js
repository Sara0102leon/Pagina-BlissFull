/* ═══════════════════════════════════════════════════
   sazón — landing interactions (vanilla JS)
   ═══════════════════════════════════════════════════ */
(function () {
  "use strict";

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---------- Nav: sticky + burger ---------- */
  var nav = document.getElementById("nav");
  var burger = document.getElementById("burger");
  var navMobile = document.getElementById("navMobile");

  var onScroll = function () { nav.classList.toggle("scrolled", window.scrollY > 24); };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  burger.addEventListener("click", function () {
    var open = navMobile.classList.toggle("open");
    burger.classList.toggle("open", open);
    burger.setAttribute("aria-expanded", String(open));
  });
  navMobile.querySelectorAll("a").forEach(function (a) {
    a.addEventListener("click", function () {
      navMobile.classList.remove("open");
      burger.classList.remove("open");
      burger.setAttribute("aria-expanded", "false");
    });
  });

  /* ---------- Reveal on scroll ---------- */
  var revealEls = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(
      function (entries) { entries.forEach(function (e) { e.target.classList.toggle("in", e.isIntersecting); }); },
      { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add("in"); });
  }

  /* ---------- Marquee: pause on hover ---------- */
  var marquee = document.querySelector(".marquee__track");
  if (marquee) {
    marquee.addEventListener("mouseenter", function () { marquee.style.animationPlayState = "paused"; });
    marquee.addEventListener("mouseleave", function () { marquee.style.animationPlayState = "running"; });
  }

  /* ---------- Toast ---------- */
  var toastTimer = null;
  function toast(msg) {
    var t = document.getElementById("toast");
    if (!t) return;
    t.textContent = msg;
    t.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { t.classList.remove("show"); }, 2400);
  }

  /* Screen-reader announcements (reordering) */
  function announce(msg) {
    var el = document.getElementById("sortLive");
    if (!el) return;
    el.textContent = "";
    setTimeout(function () { el.textContent = msg; }, 70);
  }

  /* ═══════════════════════════════════════════════════
     Sortable lists — pointer events + FLIP
     Works for the category list and the product list.
     ═══════════════════════════════════════════════════ */
  function makeSortable(list, opts) {
    opts = opts || {};
    var handleSel = opts.handle || ".md-cat, .md-card__grip";
    var active = null, placeholder = null, pointerId = null;
    var startX = 0, startY = 0, orderBefore = "";

    function order() {
      return Array.prototype.map.call(list.children, function (el) { return el.dataset.id; }).join(",");
    }

    function nextReal(el) {
      var n = el.nextElementSibling;
      while (n && (n === active || n === placeholder)) n = n.nextElementSibling;
      return n || null;
    }

    function siblings() {
      return Array.prototype.filter.call(list.children, function (el) {
        return el !== active && el !== placeholder;
      });
    }

    function onDown(e) {
      if (e.button !== undefined && e.button !== 0) return;
      var handle = e.target.closest(handleSel);
      if (!handle || !list.contains(handle)) return;
      var item = handle.closest("li");
      if (!item || item.parentElement !== list) return;

      e.preventDefault();
      active = item;
      pointerId = e.pointerId;
      try { active.setPointerCapture(pointerId); } catch (err) {}

      var r = active.getBoundingClientRect();
      startX = e.clientX; startY = e.clientY;

      placeholder = document.createElement("li");
      placeholder.className = "sort-placeholder";
      placeholder.style.height = r.height + "px";
      list.insertBefore(placeholder, active);

      orderBefore = order();
      active.classList.add("is-lifting");
      active.style.position = "fixed";
      active.style.left = r.left + "px";
      active.style.top = r.top + "px";
      active.style.width = r.width + "px";
      active.style.height = r.height + "px";
      active.style.margin = "0";
      active.style.transform = reduceMotion ? "translate(0,0)" : "rotate(1.2deg)";
      list.classList.add("is-sorting");
      document.body.style.cursor = "grabbing";

      document.addEventListener("pointermove", onMove, { passive: false });
      document.addEventListener("pointerup", onUp);
      document.addEventListener("pointercancel", onUp);
    }

    function flipMove(mutate) {
      if (reduceMotion) { mutate(); return; }
      var items = siblings();
      var first = new Map();
      items.forEach(function (el) { first.set(el, el.getBoundingClientRect()); });
      mutate();
      items.forEach(function (el) {
        var f = first.get(el);
        var l = el.getBoundingClientRect();
        var dx = f.left - l.left, dy = f.top - l.top;
        if (Math.abs(dx) > 0.5 || Math.abs(dy) > 0.5) {
          el.animate(
            [{ transform: "translate(" + dx + "px," + dy + "px)" }, { transform: "translate(0,0)" }],
            { duration: 220, easing: "cubic-bezier(.2,.8,.2,1)" }
          );
        }
      });
    }

    function onMove(e) {
      if (e.pointerId !== pointerId) return;
      e.preventDefault();
      var dx = e.clientX - startX, dy = e.clientY - startY;
      active.style.transform = reduceMotion
        ? "translate(" + dx + "px," + dy + "px)"
        : "translate(" + dx + "px," + dy + "px) rotate(1.2deg)";

      var x = e.clientX, y = e.clientY;
      var best = null, bestDist = Infinity;
      siblings().forEach(function (el) {
        var b = el.getBoundingClientRect();
        var cx = b.left + b.width / 2, cy = b.top + b.height / 2;
        var d = (cx - x) * (cx - x) + (cy - y) * (cy - y);
        if (d < bestDist) { bestDist = d; best = { el: el, r: b, cx: cx, cy: cy }; }
      });

      var ref = null;
      if (best) {
        var sameRow = Math.abs(y - best.cy) < best.r.height * 0.6;
        var before = sameRow ? (x < best.cx) : (y < best.cy);
        ref = before ? best.el : nextReal(best.el);
      }
      if (ref !== placeholder.nextElementSibling) {
        flipMove(function () {
          if (ref) list.insertBefore(placeholder, ref);
          else list.appendChild(placeholder);
        });
      }
    }

    function onUp(e) {
      if (e.pointerId !== pointerId) return;
      document.removeEventListener("pointermove", onMove);
      document.removeEventListener("pointerup", onUp);
      document.removeEventListener("pointercancel", onUp);
      document.body.style.cursor = "";

      var from = active.getBoundingClientRect();

      active.classList.remove("is-lifting");
      active.removeAttribute("style");
      list.classList.remove("is-sorting");

      list.insertBefore(active, placeholder);
      placeholder.remove();
      placeholder = null;

      var to = active.getBoundingClientRect();
      if (!reduceMotion) {
        var dx = from.left - to.left, dy = from.top - to.top;
        if (Math.abs(dx) > 0.5 || Math.abs(dy) > 0.5) {
          active.animate(
            [{ transform: "translate(" + dx + "px," + dy + "px)" }, { transform: "translate(0,0)" }],
            { duration: 250, easing: "cubic-bezier(.2,.8,.2,1)" }
          );
        }
      }

      var moved = orderBefore !== order();
      active = null; pointerId = null;
      if (moved && typeof opts.onDrop === "function") opts.onDrop();
    }

    list.addEventListener("pointerdown", onDown);

    /* Keyboard reorder: enfocar un grip/pill y usar las flechas */
    function onKey(e) {
      var handle = e.target.closest(handleSel);
      if (!handle || !list.contains(handle)) return;
      var item = handle.closest("li");
      if (!item || item.parentElement !== list) return;

      var dir = null;
      if (e.key === "ArrowLeft" || e.key === "ArrowUp") dir = "prev";
      else if (e.key === "ArrowRight" || e.key === "ArrowDown") dir = "next";
      else return;

      var target = dir === "prev" ? item.previousElementSibling : item.nextElementSibling;
      if (!target) return;
      e.preventDefault();

      var before = order();
      if (dir === "prev") list.insertBefore(item, target);
      else list.insertBefore(item, target.nextElementSibling);

      if (before === order()) return;
      if (typeof opts.onDrop === "function") opts.onDrop();
      if (typeof opts.announce === "function") opts.announce(item);
    }
    list.addEventListener("keydown", onKey);
  }

  /* Order persistence + "Guardado" indicator */
  var savedEl = document.getElementById("editorSaved");
  var savedTimer = null;
  function markSaved() {
    if (!savedEl) return;
    savedEl.textContent = "Guardando…";
    savedEl.classList.add("is-saving");
    clearTimeout(savedTimer);
    savedTimer = setTimeout(function () {
      savedEl.textContent = "Guardado ✓";
      savedEl.classList.remove("is-saving");
    }, 700);
  }

  function restoreOrder(list, key) {
    var saved;
    try { saved = JSON.parse(localStorage.getItem(key)); } catch (err) { return; }
    if (!Array.isArray(saved)) return;
    saved.forEach(function (id) {
      var el = list.querySelector('[data-id="' + id + '"]');
      if (el && el.parentElement === list) list.appendChild(el);
    });
  }

  function persistOrder(list, key) {
    var ids = Array.prototype.map.call(list.children, function (el) { return el.dataset.id; });
    try { localStorage.setItem(key, JSON.stringify(ids)); } catch (err) {}
  }

  function wireSortable(id, key, msg) {
    var list = document.getElementById(id);
    if (!list) return;
    restoreOrder(list, key);
    makeSortable(list, {
      onDrop: function () {
        persistOrder(list, key);
        markSaved();
        toast(msg);
      },
      announce: function (item) {
        var idx = Array.prototype.indexOf.call(list.children, item) + 1;
        var total = list.children.length;
        var label = (item.getAttribute("aria-label") || item.dataset.id || "Elemento").split(".")[0];
        label = label.replace(/^Reordenar\s+|^Categoría\s+/i, "");
        announce(label + ", posición " + idx + " de " + total);
      }
    });
  }

  wireSortable("editorCats", "sazon_landing_cats_order", "Categorías reordenadas — así se guarda en tu panel ✓");
  wireSortable("editorProds", "sazon_landing_prods_order", "Productos reordenados — se guarda al instante ✓");

  /* ═══════════════════════════════════════════════════
     FAQ — animate <details> open / close
     ═══════════════════════════════════════════════════ */
  document.querySelectorAll(".faq-item").forEach(function (details) {
    var summary = details.querySelector("summary");
    var body = details.querySelector(".faq-item__body");
    if (!summary || !body) return;
    var busy = false;

    summary.addEventListener("click", function (e) {
      e.preventDefault();
      if (busy) return;

      if (reduceMotion) {
        details.open = !details.open;
        return;
      }

      busy = true;
      var wasOpen = details.open;

      body.style.overflow = "hidden";
      if (wasOpen) {
        // closing: pin current height, then collapse
        body.style.height = body.scrollHeight + "px";
        void body.offsetHeight; // reflow
        body.style.height = "0px";
      } else {
        // opening: reveal from zero
        details.open = true;
        body.style.height = "0px";
        void body.offsetHeight; // reflow
        body.style.height = body.scrollHeight + "px";
      }

      var finish = function () {
        body.removeEventListener("transitionend", finish);
        body.style.height = "";
        body.style.overflow = "";
        if (wasOpen) details.open = false;
        busy = false;
      };
      body.addEventListener("transitionend", finish);
      setTimeout(function () { if (busy) finish(); }, 380); // fallback
    });
  });

  /* ═══════════════════════════════════════════════════
     Demo payment flow (Zenobank) — modal
     ═══════════════════════════════════════════════════ */
  var pay = document.getElementById("pay");
  if (pay) {
    var plans = {
      emprendedor: { name: "Emprendedor", price: "$9" },
      restaurante: { name: "Restaurante", price: "$19" },
      cadena: { name: "Cadena", price: "$39" }
    };
    var payTimer = null;
    var lastFocus = null;
    var domain = "mi-local.sazon.app";
    var payLive = document.getElementById("payLive");
    var bgEls = [document.querySelector(".nav"), document.getElementById("main"), document.querySelector(".footer")];

    function announcePay(msg) {
      if (!payLive) return;
      payLive.textContent = "";
      setTimeout(function () { payLive.textContent = msg; }, 70);
    }

    function setStep(n) {
      pay.querySelectorAll(".pay__step").forEach(function (s) {
        s.hidden = s.dataset.step !== String(n);
      });
      announcePay(
        n === 1 ? "Paso 1 de 3: resumen de pago." :
        n === 2 ? "Paso 2 de 3: conectando con Zenobank." :
                  "Paso 3 de 3: pago aprobado. Tu subdominio está listo."
      );
    }

    function openPay(plan) {
      var d = plans[plan] || plans.restaurante;
      document.getElementById("payPlan").textContent = d.name;
      document.getElementById("payTotal").textContent = d.price;
      document.getElementById("payDomain").textContent = domain;
      document.getElementById("payDomain2").textContent = domain;
      lastFocus = document.activeElement;
      pay.hidden = false;
      document.body.style.overflow = "hidden";
      bgEls.forEach(function (el) { if (el) el.setAttribute("inert", ""); });
      setStep(1);
      document.getElementById("payGo").focus();
    }

    function closePay() {
      clearTimeout(payTimer);
      pay.hidden = true;
      document.body.style.overflow = "";
      bgEls.forEach(function (el) { if (el) el.removeAttribute("inert"); });
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    pay.addEventListener("click", function (e) {
      if (e.target.closest("[data-pay-close]")) closePay();
    });
    document.addEventListener("keydown", function (e) {
      if (!pay.hidden && e.key === "Escape") { closePay(); return; }
      if (e.key !== "Tab" || pay.hidden) return;
      var f = Array.prototype.filter.call(
        pay.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'),
        function (el) { return !el.hidden && el.offsetParent !== null; }
      );
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
    document.getElementById("payGo").addEventListener("click", function () {
      setStep(2);
      payTimer = setTimeout(function () { setStep(3); }, 1700);
    });

    document.querySelectorAll(".js-pay").forEach(function (el) {
      el.addEventListener("click", function (e) {
        e.preventDefault();
        openPay(el.dataset.plan || "restaurante");
      });
    });
  }

  /* ---------- Footer year ---------- */
  var yr = document.getElementById("year");
  if (yr) yr.textContent = new Date().getFullYear();
})();
