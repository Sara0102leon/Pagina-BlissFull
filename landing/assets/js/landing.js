/* ═══════════════════════════════════════════════════
   sazón — landing interactions (vanilla JS)
   ═══════════════════════════════════════════════════ */
(function () {
  "use strict";

  /* ---------- Nav: sticky + burger ---------- */
  const nav = document.getElementById("nav");
  const burger = document.getElementById("burger");
  const navMobile = document.getElementById("navMobile");

  const onScroll = () => nav.classList.toggle("scrolled", window.scrollY > 24);
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  burger.addEventListener("click", () => {
    const open = navMobile.classList.toggle("open");
    burger.classList.toggle("open", open);
    burger.setAttribute("aria-expanded", String(open));
  });
  navMobile.querySelectorAll("a").forEach((a) =>
    a.addEventListener("click", () => {
      navMobile.classList.remove("open");
      burger.classList.remove("open");
      burger.setAttribute("aria-expanded", "false");
    })
  );

  /* ---------- Reveal on scroll ---------- */
  const revealEls = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(
      (entries) => entries.forEach((e) => e.target.classList.toggle("in", e.isIntersecting)),
      { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add("in"));
  }

  /* ---------- Marquee: pause on hover ---------- */
  const marquee = document.querySelector(".marquee__track");
  if (marquee) {
    marquee.addEventListener("mouseenter", () => (marquee.style.animationPlayState = "paused"));
    marquee.addEventListener("mouseleave", () => (marquee.style.animationPlayState = "running"));
  }

  /* ---------- Mini drag & drop demo (cusca: vanilla HTML5 DnD) ---------- */
  const sortBox = document.getElementById("miniSort");
  if (sortBox) {
    const KEY = "sazon_landing_demo_order";
    const saved = (() => {
      try { return JSON.parse(localStorage.getItem(KEY)); } catch { return null; }
    })();

    if (saved && Array.isArray(saved)) {
      saved.forEach((id) => {
        const el = sortBox.querySelector('[data-id="' + id + '"]');
        if (el) sortBox.appendChild(el);
      });
    }

    let dragEl = null;

    sortBox.addEventListener("dragstart", (e) => {
      const item = e.target.closest(".mini-sort__item");
      if (!item) return;
      dragEl = item;
      item.classList.add("dragging");
      e.dataTransfer.effectAllowed = "move";
      try { e.dataTransfer.setData("text/plain", item.dataset.id); } catch (err) {}
    });

    sortBox.addEventListener("dragend", (e) => {
      const item = e.target.closest(".mini-sort__item");
      if (item) item.classList.remove("dragging");
      sortBox.querySelectorAll(".drag-over").forEach((x) => x.classList.remove("drag-over"));
      if (dragEl) {
        persist();
        toast("Orden guardado — así se siente en tu panel ✓");
        dragEl = null;
      }
    });

    sortBox.addEventListener("dragover", (e) => {
      e.preventDefault();
      const item = e.target.closest(".mini-sort__item");
      if (!item || !dragEl || item === dragEl) return;
      const rect = item.getBoundingClientRect();
      const before = e.clientY < rect.top + rect.height / 2;
      item.classList.remove("drag-over");
      if (before) {
        item.previousElementSibling && item.previousElementSibling.classList.remove("drag-over");
        sortBox.insertBefore(dragEl, item);
      } else {
        item.classList.add("drag-over");
        item.nextElementSibling && item.nextElementSibling.classList.remove("drag-over");
        sortBox.insertBefore(dragEl, item.nextSibling);
      }
    });

    sortBox.addEventListener("drop", (e) => e.preventDefault());

    /* Touch fallback: reorder on tap via "swap" buttons — keep it simple,
       mobile users can still reorder by tapping the grip to move down. */
    sortBox.addEventListener("click", (e) => {
      if (e.target.closest(".ms-grip")) {
        const item = e.target.closest(".mini-sort__item");
        if (!item || dragEl) return;
        const next = item.nextElementSibling;
        if (next && next.classList.contains("mini-sort__item")) {
          sortBox.insertBefore(next, item);
          persist();
          toast("Orden actualizado ✓");
        }
      }
    });

    function persist() {
      const ids = [...sortBox.querySelectorAll(".mini-sort__item")].map((i) => i.dataset.id);
      try { localStorage.setItem(KEY, JSON.stringify(ids)); } catch (err) {}
    }
  }

  /* ---------- Toast ---------- */
  let toastTimer = null;
  function toast(msg) {
    const t = document.getElementById("toast");
    if (!t) return;
    t.textContent = msg;
    t.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove("show"), 2400);
  }

  /* ---------- Footer year ---------- */
  const yr = document.getElementById("year");
  if (yr) yr.textContent = new Date().getFullYear();
})();