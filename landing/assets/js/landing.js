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
     Pago real (Zenobank / modo demo) — modal
     ═══════════════════════════════════════════════════ */
  var pay = document.getElementById("pay");
  if (pay) {
    var PLANS_FALLBACK = {
      emprendedor: { name: "Emprendedor", price: "9", symbol: "$" },
      restaurante: { name: "Restaurante", price: "19", symbol: "$" },
      cadena: { name: "Cadena", price: "39", symbol: "$" }
    };
    var apiBase = "api";          // landing/api/
    var cfg = null;               // {mode, symbol, baseDomain, plans[]}
    var plansMap = {};            // code -> {name, price, symbol}
    var payTimer = null;
    var lastFocus = null;
    var pollTimer = null;
    var pollFails = 0;
    var currentPlan = "restaurante";
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
        n === 1 ? "Paso 1 de 3: tus datos y resumen de pago." :
        n === 2 ? "Paso 2 de 3: conectando con Zenobank." :
        n === 3 ? "Paso 3 de 3: pago aprobado. Tu subdominio está listo." :
        n === 5 ? "Verificando tu pago con Zenobank." :
                  "Ocurrió un error, vuelve a intentarlo."
      );
    }

    function planPrice(code) {
      var p = (cfg && cfg.plans && cfg.plans.filter(function (x) { return x.code === code; })[0]) ||
              PLANS_FALLBACK[code];
      if (!p) return "—";
      var s = (cfg && cfg.symbol) || p.symbol || "$";
      return s + parseFloat(p.price).toFixed(0);
    }

    function baseDomain() { return (cfg && cfg.baseDomain) || "sazon.app"; }

    function slugify(s) {
      return (s || "").toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "")
        .slice(0, 63);
    }

    function updateUrlPreview() {
      var slug = slugify(document.getElementById("payDomain").value);
      var url = (slug || "tu-local") + "." + baseDomain();
      document.getElementById("payUrl").textContent = url;
      document.getElementById("payDomain2").textContent = url;
    }

    function openPay(plan) {
      currentPlan = plan;
      var d = plansMap[plan] || PLANS_FALLBACK.restaurante;
      document.getElementById("payPlan").textContent = (d && d.name) || "Restaurante";
      document.getElementById("payTotal").textContent = planPrice(plan);
      updateUrlPreview();
      lastFocus = document.activeElement;
      pay.hidden = false;
      document.body.style.overflow = "hidden";
      bgEls.forEach(function (el) { if (el) el.setAttribute("inert", ""); });
      setStep(1);
      var f = document.getElementById("payNombre");
      if (f) f.focus();
    }

    function closePay() {
      clearTimeout(payTimer);
      if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
      pay.hidden = true;
      document.body.style.overflow = "";
      bgEls.forEach(function (el) { if (el) el.removeAttribute("inert"); });
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    function showPayError(msg) {
      document.getElementById("payErrSub").textContent = msg || "Ocurrió un error. Inténtalo de nuevo.";
      setStep(4);
    }

    function finalizeOk() {
      document.getElementById("payGo").disabled = false;
      setStep(3);
    }

    /* ---------- iniciar checkout ---------- */
    function startCheckout() {
      var nombreEl = document.getElementById("payNombre");
      var domainEl = document.getElementById("payDomain");
      var emailEl = document.getElementById("payEmail");
      var passEl = document.getElementById("payPassword");
      var nombre = (nombreEl.value || "").trim();
      var sub = (domainEl.value || "").trim();
      var email = (emailEl.value || "").trim();
      var pass = passEl.value || "";

      if (nombre.length < 3) { showPayError("Escribe el nombre de tu local (mínimo 3 letras)."); nombreEl.focus(); return; }
      if (!/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/.test(sub)) {
        showPayError("El subdominio solo admite letras minúsculas, números y guiones.");
        domainEl.focus(); return;
      }
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showPayError("Ese email no parece válido."); emailEl.focus(); return; }
      if (pass && pass.length < 6) { showPayError("La contraseña debe tener al menos 6 caracteres."); passEl.focus(); return; }

      var btn = document.getElementById("payGo");
      btn.disabled = true;
      setStep(2);

      fetch(apiBase + "/checkout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ plan: currentPlan, nombre: nombre, subdominio: sub, email: email, password: pass })
      })
        .then(function (r) { return r.json().then(function (d) { return { http: r.ok, d: d }; }); })
        .then(function (res) {
          var d = res.d || {};
          if (!res.http || !d.ok) throw d;

          // demo: activación inmediata (misma animación de 3 pasos)
          if (d.demo) {
            document.getElementById("payDomain2").textContent = d.subdominio || "";
            payTimer = setTimeout(finalizeOk, 1400);
            return;
          }

          // live: guardamos el checkout y vamos a Zenobank
          try {
            sessionStorage.setItem("saz_checkout", d.checkoutId || "");
            sessionStorage.setItem("saz_subdominio", d.subdominio || "");
          } catch (e) {}
          document.getElementById("payStep2Sub").textContent = "Te llevamos a Zenobank para completar el pago de forma segura…";
          setTimeout(function () { window.location.href = d.checkoutUrl; }, 600);
        })
        .catch(function (err) {
          btn.disabled = false;
          showPayError((err && err.error) ? err.error : "No pudimos iniciar el pago. Inténtalo de nuevo.");
        });
    }

    /* ---------- vuelta desde Zenobank: ?pago=exito -> verificar ---------- */
    function verifyReturn(checkoutId) {
      setStep(5);
      pollFails = 0;
      pollTimer = setInterval(function () {
        if (!checkoutId) { clearInterval(pollTimer); pollTimer = null; showPayError("No encontramos tu pago. Si ya pagaste, tu subdominio se activará igualmente en unos minutos."); return; }
        fetch(apiBase + "/checkout-status.php?checkout=" + encodeURIComponent(checkoutId))
          .then(function (r) { return r.json(); })
          .then(function (d) {
            if (!d) return;
            if (d.status === "COMPLETED") {
              clearInterval(pollTimer); pollTimer = null;
              if (d.subdominio) document.getElementById("payDomain2").textContent = d.subdominio;
              finalizeOk();
            } else if (d.status === "EXPIRED" || d.status === "CANCELED") {
              clearInterval(pollTimer); pollTimer = null;
              showPayError("El pago no se completó. Puedes volver a intentarlo cuando quieras.");
            }
          })
          .catch(function () {
            pollFails++;
            if (pollFails > 3) { clearInterval(pollTimer); pollTimer = null; showPayError("Tardó más de lo esperado. Si ya pagaste, tu subdominio se activará en unos minutos."); }
          });
      }, 3000);
    }

    function handleReturnParams() {
      var qs = new URLSearchParams(window.location.search);
      if (qs.get("pago") !== "exito") return;
      var chk = "";
      try { chk = sessionStorage.getItem("saz_checkout") || ""; } catch (e) {}
      if (!chk) chk = qs.get("checkout") || "";
      if (!chk) return;
      // limpiar la URL sin recargar
      history.replaceState(null, "", window.location.pathname + window.location.search.replace(/[?&](pago|checkout|tienda)=[^&]*/g, "").replace(/^&/, "?"));
      openPay(currentPlan);
      verifyReturn(chk);
    }

    /* ---------- eventos ---------- */
    pay.addEventListener("click", function (e) {
      if (e.target.closest("[data-pay-close]")) closePay();
      if (e.target.closest("[data-pay-back]")) {
        document.getElementById("payGo").disabled = false;
        setStep(1);
      }
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

    var payNombre = document.getElementById("payNombre");
    var payDomain = document.getElementById("payDomain");
    payNombre.addEventListener("input", function () {
      // solo autocompleta si el usuario no tocó el subdominio aún
      if (!payDomain.dataset.touched) payDomain.value = slugify(payNombre.value);
      updateUrlPreview();
    });
    payDomain.addEventListener("input", function () { payDomain.dataset.touched = "1"; updateUrlPreview(); });

    document.getElementById("payGo").addEventListener("click", startCheckout);

    document.querySelectorAll(".js-pay").forEach(function (el) {
      el.addEventListener("click", function (e) {
        e.preventDefault();
        openPay(el.dataset.plan || "restaurante");
      });
    });

    // configuración del backend (modo, moneda, dominio, planes)
    fetch(apiBase + "/config.php")
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.ok) return;
        cfg = d;
        (d.plans || []).forEach(function (p) { plansMap[p.code] = p; });
        document.getElementById("payBadge").textContent = d.mode === "live" ? "Pago seguro con Zenobank" : "Modo demo · se activa al instante";
        updateUrlPreview();
      })
      .catch(function () {});

    // si volvimos de Zenobank con pago aprobado
    handleReturnParams();
  }

  /* ---------- Footer year ---------- */
  var yr = document.getElementById("year");
  if (yr) yr.textContent = new Date().getFullYear();
})();
