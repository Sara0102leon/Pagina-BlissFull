/* ============================================================
   GUÍA INTERACTIVA DEL PANEL ADMIN (Tour con spotlight)
   - Botón flotante "?" para activar
   - Pregunta qué módulo ver o guía completa
   - Spotlight: oscurece todo excepto el elemento explicado
   - No depende de SweetAlert2: usa modal Bootstrap local
   ============================================================ */
(function () {
  if (!window.jQuery) { return; }
  var $ = window.jQuery;

  var MODULOS = {
    dashboard:   { url: "./",                              titulo: "Dashboard" },
    products:    { url: "./?view=products&opt=all",        titulo: "Productos" },
    categories:  { url: "./?view=categories&opt=all",      titulo: "Categorías" },
    sells:       { url: "./?view=sells&opt=all",           titulo: "Ventas" },
    slider:      { url: "./?view=slider&opt=all",          titulo: "Slider" },
    settings:    { url: "./?view=settings&opt=all",        titulo: "Ajustes" },
    horarios:    { url: "./?view=settings&opt=horarios",   titulo: "Horarios" },
    ingredients: { url: "./?view=settings&opt=ingredients",titulo: "Ingredientes" }
  };
  var ORDEN_COMPLETO = ["dashboard", "products", "categories", "sells", "slider", "settings", "horarios", "ingredients"];

  var PASOS = {
    dashboard: [
      { sel: ".page-title", t: "Dashboard", d: "Este es el panel principal. Aquí ves el resumen del negocio de un vistazo." },
      { sel: ".row-cards .card", t: "Contadores de pedidos", d: "Cada tarjeta muestra cuántos pedidos hay por estado: pendientes, pagados, cancelados, etc." },
      { sel: "#chart-buys", t: "Gráfica de ventas", d: "Resumen de las ventas de los últimos 30 días, día por día." },
      { sel: "#appSidebar", t: "Menú lateral", d: "Desde aquí navegas por todos los módulos: Ventas, Productos, Categorías, Slider y Sistema (ajustes, sedes, pagos, unidades e ingredientes)." }
    ],
    products: [
      { sel: ".app-title", t: "Módulo Productos", d: "Gestiona el catálogo que tus clientes ven en la página web: precios, ofertas, ingredientes y más." },
      { sel: 'a[href*="opt=new"]', t: "Nuevo Producto", d: "Este botón abre el formulario para crear un producto nuevo desde cero." },
      { sel: 'select[name="sede"]', t: "Filtrar por sede", d: "Filtra la lista de productos según la sucursal a la que pertenecen." },
      { sel: "table.datatable", t: "Tabla de productos", d: "Lista completa con precios, ofertas y stock. Usa el buscador para encontrar un producto al instante." },
      { sel: ".btn-delete-product", t: "Eliminar producto", d: "Cada fila tiene su botón para eliminar. El sistema te pedirá confirmación antes de borrar." }
    ],
    categories: [
      { sel: ".app-title", t: "Módulo Categorías", d: "Organiza tus productos por categorías: pizzas, hamburguesas, bebidas, etc." },
      { sel: 'a[href*="opt=new"]', t: "Nueva Categoría", d: "Crea una categoría nueva para agrupar productos." },
      { sel: "table.datatable", t: "Tabla de categorías", d: "Aquí ves todas las categorías creadas y puedes editarlas o eliminarlas." }
    ],
    sells: [
      { sel: ".app-title", t: "Módulo Ventas", d: "Consulta todos los pedidos que llegan desde la página web." },
      { sel: "#notif-wrap", t: "Notificaciones", d: "La campana te avisa de pedidos sin pago. Clic para verlos y entrar al pedido." },
      { sel: "table.datatable", t: "Tabla de pedidos", d: "Todos los pedidos con cliente, método de pago, zona y total." },
      { sel: ".btn-status-change", t: "Cambiar estado", d: "Con estos botones avanzas el pedido: pagado, enviado, finalizado o cancelado." },
      { sel: 'a[href*="opt=open"]', t: "Detalles del pedido", d: "Abre el detalle completo: productos, extras, dirección y totales." }
    ],
    slider: [
      { sel: ".app-title", t: "Módulo Slider", d: "Administra las imágenes del carrusel del inicio de la página web." },
      { sel: 'a[href*="opt=new"]', t: "Agregar Slide", d: "Sube una imagen nueva para que aparezca en el carrusel principal." },
      { sel: "table.datatable", t: "Tabla de slides", d: "Lista de imágenes activas. Puedes editarlas o quitarlas." }
    ],
    settings: [
      { sel: ".app-title", t: "Módulo Ajustes", d: "Configuración general del negocio: datos, moneda, WhatsApp, tasa BCV y más." },
      { sel: 'a[href*="opt=payment"], a[href*="opt=zones"], a[href*="opt=sedes"], a[href*="opt=horarios"], a[href*="opt=units"], a[href*="opt=ingredients"]', t: "Accesos rápidos", d: "Estos botones te llevan a cada sección: métodos de pago, zonas de delivery, sedes, horarios, unidades e ingredientes." },
      { sel: ".table-responsive table, form[action*='settings'] table", t: "Ajustes generales", d: "Aquí editas nombre del negocio, moneda, WhatsApp, imagen por defecto y tasa BCV." },
      { sel: 'button[type="submit"], input[type="submit"]', t: "Actualizar Ajustes", d: "Guarda todos los cambios de esta sección." },
      { sel: "#menuSistema", t: "Menú Sistema", d: "En el menú lateral, dentro de Sistema, también encuentras Usuarios, Ajustes, Sedes, Métodos de Pago, Unidades e Ingredientes." }
    ],
    horarios: [
      { sel: ".app-title", t: "Horarios de atención", d: "Define cuándo el negocio está abierto. Este horario controla el cierre/apertura automático de la página." },
      { sel: 'input[name="horario_open"]', t: "Apertura general", d: "Hora en que abre el negocio todos los días (se usa cuando un día no tiene horario propio)." },
      { sel: 'input[name="horario_close"]', t: "Cierre general", d: "Hora en que cierra el negocio." },
      { sel: 'input[name^="horario_"]', t: "Horarios por día", d: "Puedes dar un horario distinto a cada día de la semana (ej: jueves abre 9:00). Si un día queda vacío, se usa el horario general." }
    ],
    ingredients: [
      { sel: ".app-title", t: "Módulo Ingredientes", d: "Administra los ingredientes y extras que se ofrecen al personalizar cada producto." },
      { sel: "#form-add-extra", t: "Nuevo ingrediente / extra", d: "Escribe el nombre y el precio. Sin precio o con 0 = gratis. Marca 'Es ingrediente' si se cuenta como ingrediente base del producto." },
      { sel: ".btn-pick-products", t: "Elegir productos", d: "Abre la ventana para seleccionar a qué productos se aplica este ingrediente o extra." },
      { sel: ".row-is-ingredient", t: "¿Es ingrediente?", d: "Si está marcado, se muestra en la sección de INGREDIENTES al personalizar (con cantidad gratis según el producto). Si no, aparece como EXTRA con precio." },
      { sel: ".btn-extra-save", t: "Guardar cambios", d: "Guarda los cambios de cada fila. Recuerda: los ingredientes gratis no pueden tener precio." }
    ]
  };

  var state = null;

  function moduloActual() {
    var q = new URLSearchParams(window.location.search);
    var v = q.get("view") || "";
    var o = q.get("opt") || "";
    if (v === "") { return "dashboard"; }
    if (v === "products") { return "products"; }
    if (v === "categories") { return "categories"; }
    if (v === "sells") { return "sells"; }
    if (v === "slider") { return "slider"; }
    if (v === "settings") {
      if (o === "horarios") { return "horarios"; }
      if (o === "ingredients" || o === "extras") { return "ingredients"; }
      return "settings";
    }
    return "dashboard";
  }

  function urlConTour(modulo, extra) {
    var u = MODULOS[modulo].url;
    return u + (u.indexOf("?") >= 0 ? "&" : "?") + "tour=" + (extra || modulo);
  }

  /* ---------- Mensajes (Swal si existe, si no toast propio) ---------- */
  function notify(title, text, icon) {
    if (window.Swal) {
      Swal.fire({
        icon: icon || "info",
        title: title,
        text: text,
        background: "#14100c",
        color: "#f0e6d8",
        iconColor: "#e0a96d",
        confirmButtonColor: "#b87e38"
      });
      return;
    }
    var $t = $("#tt-tour-toast");
    if (!$t.length) {
      $("body").append('<div id="tt-tour-toast"></div>');
      $t = $("#tt-tour-toast");
    }
    $t.html('<div class="tt-toast-box"><b>' + title + '</b><p>' + text + '</p><button type="button" class="tt-toast-ok">Entendido</button></div>');
    $t.addClass("show");
    $t.find(".tt-toast-ok").one("click", function () { $t.removeClass("show"); });
  }

  /* ---------- Spotlight ---------- */
  function posicionar(sel) {
    var $el = $(sel).first();
    if (!$el.length) { return false; }
    if (!$el.is(":visible")) {
      var $col = $el.closest(".collapse");
      if ($col.length) { $col.collapse("show"); }
    }
    if ($el.is(":hidden")) { return false; }
    var el = $el[0];
    if (typeof el.scrollIntoView === "function") {
      el.scrollIntoView({ block: "center", behavior: "smooth" });
    }
    setTimeout(function () {
      var rect = el.getBoundingClientRect();
      var hole = document.getElementById("tt-tour-hole");
      var pop = document.getElementById("tt-tour-pop");
      if (!hole || !pop) { return; }
      hole.style.left = rect.left - 6 + "px";
      hole.style.top = rect.top - 6 + "px";
      hole.style.width = rect.width + 12 + "px";
      hole.style.height = rect.height + 12 + "px";
      var pw = pop.offsetWidth;
      var ph = pop.offsetHeight;
      var x = rect.left + rect.width / 2 - pw / 2;
      x = Math.max(12, Math.min(x, window.innerWidth - pw - 12));
      var y = rect.bottom + 16;
      pop.classList.remove("on-top");
      if (y + ph > window.innerHeight - 12 && rect.top - ph - 16 > 12) {
        y = rect.top - ph - 16;
        pop.classList.add("on-top");
      }
      pop.style.left = x + "px";
      pop.style.top = y + "px";
    }, 350);
    return true;
  }

  function renderPaso(idx) {
    var pasos = state.steps;
    var p = pasos[idx];
    $("#tt-tour-pop .pop-title").text(p.t);
    $("#tt-tour-pop .pop-desc").text(p.d);
    $("#tt-tour-pop .pop-count").text("Paso " + (idx + 1) + " de " + pasos.length);
    var $next = $("#tt-tour-pop .pop-btn.next");
    $next.text(idx === pasos.length - 1 ? "Finalizar" : "Siguiente");
    $("#tt-tour-pop .pop-btn.prev").toggle(idx > 0);
    posicionar(p.sel);
  }

  function cerrar() {
    $("#tt-tour-hole, #tt-tour-pop").remove();
    $(document).off("keydown.tt-tour");
    $(window).off("resize.tt-tour");
    state = null;
  }

  function siguiente() {
    if (!state) { return; }
    var idx = state.idx + 1;
    if (idx >= state.steps.length) {
      if (state.full) {
        var i = ORDEN_COMPLETO.indexOf(state.modulo);
        if (i >= 0 && i < ORDEN_COMPLETO.length - 1) {
          var nxt = ORDEN_COMPLETO[i + 1];
          cerrar();
          window.location.href = urlConTour(nxt, "completo") + "&tidx=" + (i + 1);
          return;
        }
        var url = window.location.href.replace(/[?&]tour=[^&]*/g, "").replace(/[?&]tidx=[^&]*/g, "");
        history.replaceState(null, "", url);
        cerrar();
        notify("¡Guía terminada!", "Ya conoces el panel completo. ¡A trabajar!", "success");
        return;
      }
      cerrar();
      return;
    }
    state.idx = idx;
    renderPaso(idx);
  }

  function anterior() {
    if (!state || state.idx <= 0) { return; }
    state.idx--;
    renderPaso(state.idx);
  }

  function startTour(modulo, opts) {
    opts = opts || {};
    var steps = (PASOS[modulo] || []).filter(function (p) {
      return $(p.sel).length > 0;
    });
    if (!steps.length) {
      notify("Sin pasos", "No se encontraron elementos para explicar en este módulo.", "info");
      return;
    }
    cerrar();
    state = { modulo: modulo, steps: steps, idx: 0, full: opts.full };
    $("body").append(
      '<div id="tt-tour-hole"></div>' +
      '<div id="tt-tour-pop">' +
        '<div class="pop-head">' +
          '<h3 class="pop-title"></h3>' +
          '<button type="button" class="pop-close" title="Cerrar guía"><i class="bi bi-x-lg"></i></button>' +
        '</div>' +
        '<p class="pop-desc"></p>' +
        '<div class="pop-foot">' +
          '<span class="pop-count"></span>' +
          '<div class="pop-nav">' +
            '<button type="button" class="pop-btn prev"><i class="bi bi-arrow-left me-1"></i>Anterior</button>' +
            '<button type="button" class="pop-btn next">Siguiente</button>' +
          '</div>' +
        '</div>' +
      '</div>'
    );
    $("#tt-tour-pop .pop-close").on("click", cerrar);
    $("#tt-tour-pop .pop-btn.prev").on("click", anterior);
    $("#tt-tour-pop .pop-btn.next").on("click", siguiente);
    $("#tt-tour-hole").on("click", siguiente);
    $(document).on("keydown.tt-tour", function (e) {
      if (e.key === "Escape") { cerrar(); }
      if (e.key === "ArrowRight") { siguiente(); }
      if (e.key === "ArrowLeft") { anterior(); }
    });
    $(window).on("resize.tt-tour", function () {
      if (state) { posicionar(state.steps[state.idx].sel); }
    });
    renderPaso(0);
  }

  /* ---------- Selector de módulos (modal Bootstrap propio) ---------- */
  var modalHTML =
    '<div class="modal fade" id="tt-tour-modal" tabindex="-1" role="dialog" aria-hidden="true">' +
      '<div class="modal-dialog modal-dialog-centered" role="document">' +
        '<div class="modal-content tt-tour-modal-content">' +
          '<div class="modal-header">' +
            '<h5 class="modal-title"><i class="bi bi-compass me-2"></i>¿Qué módulo quieres explorar?</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>' +
          '</div>' +
          '<div class="modal-body">' +
            '<div class="tt-tour-modules" id="tt-tour-mod-list"></div>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';

  function openModalSelector() {
    var mods = [
      { id: "dashboard",   i: "bi-grid-fill",     l: "Dashboard" },
      { id: "products",    i: "bi-box-seam",      l: "Productos" },
      { id: "categories",  i: "bi-tags",          l: "Categorías" },
      { id: "sells",       i: "bi-cart-check",    l: "Ventas" },
      { id: "slider",      i: "bi-images",        l: "Slider" },
      { id: "settings",    i: "bi-sliders",       l: "Ajustes" },
      { id: "horarios",    i: "bi-clock-history", l: "Horarios" },
      { id: "ingredients", i: "bi-egg-fried",     l: "Ingredientes" }
    ];
    var html = "";
    mods.forEach(function (m) {
      html += '<button type="button" class="tt-tour-module" data-mod="' + m.id + '">' +
              '<i class="bi ' + m.i + '"></i><span>' + m.l + '</span></button>';
    });
    html += '<button type="button" class="tt-tour-module full" data-mod="completo">' +
            '<i class="bi bi-compass"></i><span>Guía completa (recorre todos los módulos)</span></button>';

    if (!$("#tt-tour-modal").length) { $("body").append(modalHTML); }
    $("#tt-tour-mod-list").html(html);
    var $m = $("#tt-tour-modal");
    $m.off("click", ".tt-tour-module");
    $m.on("click", ".tt-tour-module", function () {
      var mod = $(this).data("mod");
      $m.modal("hide");
      if (mod === "completo") {
        arrancarCompleto(0);
      } else {
        arrancar(mod);
      }
    });
    $m.modal("show");
  }

  function arrancar(mod) {
    if (moduloActual() === mod) {
      startTour(mod, { full: false });
    } else {
      window.location.href = urlConTour(mod);
    }
  }

  function arrancarCompleto(idx) {
    if (idx >= ORDEN_COMPLETO.length) {
      var url = window.location.href.replace(/[?&]tour=[^&]*/g, "").replace(/[?&]tidx=[^&]*/g, "");
      history.replaceState(null, "", url);
      return;
    }
    var mod = ORDEN_COMPLETO[idx];
    if (moduloActual() !== mod) {
      window.location.href = urlConTour(mod, "completo") + "&tidx=" + idx;
    } else {
      startTour(mod, { full: true });
    }
  }

  /* ---------- Auto-arranque desde la URL ---------- */
  function autoStart() {
    var q = new URLSearchParams(window.location.search);
    var t = q.get("tour");
    if (!t) { return; }
    var tidx = parseInt(q.get("tidx") || "0", 10);
    if (t === "completo") {
      setTimeout(function () { arrancarCompleto(isNaN(tidx) ? 0 : tidx); }, 900);
    } else if (MODULOS[t]) {
      setTimeout(function () { startTour(t, { full: false }); }, 900);
    }
  }

  /* ---------- Botón de la guía (en el header, junto a notificaciones) ---------- */
  $(function () {
    if (!$("body").hasClass("is-auth")) { return; }
    var $btn = $("#btn-tour-guide");
    if ($btn.length) {
      $btn.on("click", openModalSelector);
    } else {
      // Respaldo: si el botón del header no existe, crea uno flotante
      $("body").append(
        '<button type="button" id="tt-tour-fab" title="Guía del panel">' +
          '<span class="fab-label">¿Cómo usar el panel?</span>' +
          '<b>?</b>' +
        '</button>'
      );
      $("#tt-tour-fab").on("click", openModalSelector);
    }
    autoStart();
  });
})();