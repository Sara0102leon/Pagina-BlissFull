/* ============================================================
   CONFIRMACIÓN GENÉRICA DE ELIMINAR (admin)
   - Cubre los links de borrado que no tenían ninguna confirmación
     (categorías, clientes, personas, slider, gastos, usuarios...)
   - Usa SweetAlert2 si está cargado; si no, confirm() nativo
     para que NUNCA quede en silencio (sin alerta ni acción)
   - No toca los borrados que ya tienen su propio manejo
     (.btn-delete-product de productos y los onclick de settings)
   ============================================================ */
(function () {
  if (!window.jQuery) { return; }
  var $ = window.jQuery;

  function confirmar(href, name, ok) {
    if (window.Swal) {
      Swal.fire({
        title: "¿Eliminar?",
        text: (name ? "'" + name + "' se " : "Se ") + "ocultará del sistema y de la página web.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#b02a37",
        cancelButtonColor: "#6c757d",
        background: "#14100c",
        color: "#f0e6d8"
      }).then(function (r) {
        if (r.isConfirmed) { ok(); }
      });
    } else {
      if (window.confirm(name ? "¿Eliminar '" + name + "'?" : "¿Eliminar? Esta acción oculta el elemento del sistema.")) {
        ok();
      }
    }
  }

  $(function () {
    $(document).on("click", "a.btn-danger[href*='opt=del']", function (e) {
      var $a = $(this);
      if ($a.hasClass("btn-delete-product")) { return; }
      if ($a.attr("onclick")) { return; }
      e.preventDefault();
      var href = $a.attr("href");
      var name = $a.data("name") || $a.attr("title") || "";
      confirmar(href, name, function () { window.location.href = href; });
    });
  });
})();