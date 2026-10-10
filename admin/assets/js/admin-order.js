/* =============================================================
   sazón · admin-order.js (A4)
   Drag & drop del orden del menú (categorías y productos).
   Patrón: pointer events + FLIP (igual que la landing).
   Persiste vía fetch a  ./?action=order&opt=cats|prods
   ============================================================= */
(function () {
  "use strict";

  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var savedEl = document.getElementById("orderSaved");
  var saveTimer = null;

  function announce(msg) {
    if (!savedEl) return;
    savedEl.className = "order-saved is-saving";
    savedEl.textContent = msg;
  }
  function markSaved() {
    if (!savedEl) return;
    savedEl.className = "order-saved";
    savedEl.textContent = "Guardado ✓";
    clearTimeout(saveTimer);
    saveTimer = setTimeout(function () { savedEl.textContent = ""; }, 2200);
  }
  function markError(msg) {
    if (!savedEl) return;
    savedEl.className = "order-saved is-error";
    savedEl.textContent = msg || "No se pudo guardar";
    clearTimeout(saveTimer);
    saveTimer = setTimeout(function () { savedEl.textContent = ""; }, 3200);
  }

  function save(url, payload) {
    announce("Guardando…");
    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(payload)
    })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j && j.ok) { markSaved(); }
        else { markError(j && j.error === "forbidden" ? "Sin permiso" : "No se pudo guardar"); }
      })
      .catch(function () { markError("Error de red"); });
  }

  function makeSortable(list, opts) {
    opts = opts || {};
    var handleSel = opts.handle || ".order-grip";
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
      active.style.transform = reduceMotion ? "translate(0,0)" : "translate(0,0) rotate(1.2deg)";
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

    /* Reordenar con el teclado (flechas) sobre el elemento enfocado */
    function onKey(e) {
      var item = e.target.closest("li");
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
    }
    list.addEventListener("keydown", onKey);
  }

  function idsOf(el) {
    return Array.prototype.map.call(el.children, function (li) { return li.dataset.id; })
      .filter(function (id) { return id; });
  }

  function init() {
    var cats = document.getElementById("orderCats");
    if (cats) {
      makeSortable(cats, {
        onDrop: function () { save("./?action=order&opt=cats", { ids: idsOf(cats) }); }
      });
    }
    Array.prototype.forEach.call(document.querySelectorAll(".order-prods"), function (list) {
      makeSortable(list, {
        onDrop: function () {
          save("./?action=order&opt=prods", { category_id: list.dataset.cat, ids: idsOf(list) });
        }
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
