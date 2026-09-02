<!doctype html>
<html lang="es" data-bs-theme="dark">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Alianzas Blissful - Menú Digital</title>
    <link rel="icon" type="image/png" href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>/assets/img/favicon.png">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Caveat:wght@500;600;700&family=Inter:wght@400;600;800;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- CSS files -->
    <link href="./dist/css/tabler.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Dark Theme Tito Burger (cargado al final para sobreescribir Tabler/Bootstrap) -->
    <link rel="stylesheet" href="assets/css/custom-dark.css?v=8">
    
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      :root {
        --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        --secondary-color: #b87e38; /* Dorado Tito */
        --primary-color: #e0a96d;   /* Dorado Brillante */
      }
      body {
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
      }
      h1,h2,h3, .navbar-brand { font-family: 'Outfit', sans-serif; }
      .brand-logo {
        height: 44px;
        width: auto;
        max-width: 70vw;
        object-fit: contain;
      }
      @media (max-width: 575.98px) {
        .brand-logo { height: 36px; }
      }
      /* ===== Pantalla de carga (página completa) ===== */
      .tt-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(ellipse at 50% 30%, #1c1210 0%, #0d0a09 65%, #000 100%);
        transition: opacity 0.35s ease, visibility 0.35s ease;
      }
      .tt-loader.done { opacity: 0; visibility: hidden; pointer-events: none; }
      .tt-loader-ring {
        width: 62px;
        height: 62px;
        margin: 0 auto 16px;
        border: 5px solid rgba(255, 183, 3, 0.18);
        border-top-color: #ffb703;
        border-right-color: #e0a96d;
        border-radius: 50%;
        animation: ttLoaderSpin 0.85s linear infinite;
      }
      @keyframes ttLoaderSpin { to { transform: rotate(360deg); } }
      .tt-loader-box { text-align: center; }
      .tt-loader-brand {
        font-family: 'Bebas Neue', 'Outfit', sans-serif;
        font-size: 1.9rem;
        letter-spacing: 0.1em;
        color: #ffffff;
        line-height: 1;
      }
      .tt-loader-brand span { color: #ffb703; }
      .tt-loader-sub {
        font-family: 'Caveat', cursive;
        font-size: 1.25rem;
        color: #e0a96d;
        margin-top: 4px;
      }
      .tt-grid-loading {
        padding: 4rem 0;
        text-align: center;
      }
      .tt-grid-ring {
        width: 46px;
        height: 46px;
        margin: 0 auto 12px;
        border: 4px solid rgba(255, 183, 3, 0.18);
        border-top-color: #ffb703;
        border-radius: 50%;
        animation: ttLoaderSpin 0.85s linear infinite;
      }
      .tt-grid-loading p {
        font-family: 'Caveat', cursive;
        font-size: 1.35rem;
        color: #e0a96d;
        margin: 0;
      }
    </style>
  </head>
  <body>
    <div id="tt-loader" class="tt-loader" aria-hidden="true">
      <div class="tt-loader-box">
        <div class="tt-loader-ring"></div>
        <div class="tt-loader-brand">ALIANZAS <span>BLISSFUL</span></div>
        <div class="tt-loader-sub">cargando menú...</div>
      </div>
    </div>
    <?php 
    $bcv_rate_header = 0;
    $bcv_row = ConfigurationData::getByPreffix("bcv_rate");
    if($bcv_row && $bcv_row->val){ $bcv_rate_header = floatval($bcv_row->val); }
    $whatsapp_footer = ConfigurationData::getByPreffix("general_whatsapp")?ConfigurationData::getByPreffix("general_whatsapp")->val:"+5215574506232";
    $fb_footer = trim(ConfigurationData::getByPreffix("general_facebook")?ConfigurationData::getByPreffix("general_facebook")->val:"");
    $ig_footer = trim(ConfigurationData::getByPreffix("general_instagram")?ConfigurationData::getByPreffix("general_instagram")->val:"");
    $horario_open_raw = ConfigurationData::getByPreffix("horario_open")?ConfigurationData::getByPreffix("horario_open")->val:"11:00";
    $horario_close_raw = ConfigurationData::getByPreffix("horario_close")?ConfigurationData::getByPreffix("horario_close")->val:"23:00";
    $horario_open_raw = $horario_open_raw=="" ? "11:00" : $horario_open_raw;
    $horario_close_raw = $horario_close_raw=="" ? "23:00" : $horario_close_raw;
    $map_dia_en = array("sunday"=>"domingo","monday"=>"lunes","tuesday"=>"martes","wednesday"=>"miercoles","thursday"=>"jueves","friday"=>"viernes","saturday"=>"sabado");
    $hoy_key = strtolower(date("l"));
    if(isset($map_dia_en[$hoy_key])){
      $dia_cfg = ConfigurationData::getByPreffix("horario_".$map_dia_en[$hoy_key]);
      if($dia_cfg && $dia_cfg->val!="" && strpos($dia_cfg->val,"-")!==false){
        $dia_parts = array_map("trim", explode("-", $dia_cfg->val));
        if(isset($dia_parts[0]) && $dia_parts[0]!="" && isset($dia_parts[1]) && $dia_parts[1]!=""){
          $horario_open_raw = $dia_parts[0];
          $horario_close_raw = $dia_parts[1];
        }
      }
    }
    $h_open_min = (int)explode(":",$horario_open_raw)[0]*60 + (int)explode(":",$horario_open_raw)[1];
    $h_close_min = (int)explode(":",$horario_close_raw)[0]*60 + (int)explode(":",$horario_close_raw)[1];
    $now_min = (int)date("G")*60 + (int)date("i");
    // Determine today's schedule (per-day override takes priority)
    $dia_actual = date("N"); // 1=Lunes .. 7=Domingo
    $day_names = ["lunes","martes","miercoles","jueves","viernes","sabado","domingo"];
    $dia_key = $day_names[$dia_actual-1];
    $horario_hoy_raw = ConfigurationData::getByPreffix("horario_".$dia_key);
    // Always use per-day schedule if set, otherwise general schedule
    $usar_horario_hoy = false;
    $h_open_str = $horario_open_raw;
    $h_close_str = $horario_close_raw;
    if($horario_hoy_raw && trim((string)$horario_hoy_raw->val)!=""){
      $val = trim((string)$horario_hoy_raw->val);
      $parts = preg_split("/\s*-\s*/", $val);
      if(count($parts) >= 2 && trim($parts[0])!="" && trim($parts[1])!=""){
        $h_open_str = trim($parts[0]);
        $h_close_str = trim($parts[1]);
        $usar_horario_hoy = true;
      } else if(count($parts) == 1 && trim($parts[0])!=""){
        // Solo escribieron la apertura: usar apertura del día + cierre general
        $h_open_str = trim($parts[0]);
        $usar_horario_hoy = true;
      }
    }
    $h_open_parts = explode(":", $h_open_str);
    $h_close_parts = explode(":", $h_close_str);
    $h_open_min = (int)$h_open_parts[0]*60 + (int)(isset($h_open_parts[1]) ? $h_open_parts[1] : 0);
    $h_close_min = (int)$h_close_parts[0]*60 + (int)(isset($h_close_parts[1]) ? $h_close_parts[1] : 0);
    // El cierre se maneja por sede (sedeClosedGuard en el frontend), no globalmente.
    $store_closed = false;
    // Display: use per-day schedule if set, otherwise general
    if($usar_horario_hoy){
      $h_open_d = date("g:i A", strtotime($h_open_str));
      $h_close_d = date("g:i A", strtotime($h_close_str));
      $horario_display = $h_open_d." - ".$h_close_d;
    } else {
      $h_open_d = date("g:i A", strtotime($horario_open_raw));
      $h_close_d = date("g:i A", strtotime($horario_close_raw));
      $horario_display = $h_open_d." - ".$h_close_d;
    }
    $horario_open_display = date("g:i A", strtotime($h_open_str));
    ?>
    <div class="page">
      <!-- Top Navbar (Sticky Minimal) -->
      <header class="navbar navbar-expand-md navbar-dark tt-navbar d-print-none shadow-sm sticky-top">
        <div class="container-xl">
          <div class="navbar-brand pe-0 pe-md-3">
            <a href="./" class="text-decoration-none d-flex align-items-center">
              <img src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>/assets/img/logo.png" alt="Alianzas Blissful" class="brand-logo">
            </a>
          </div>

          <div class="navbar-nav flex-row align-items-center order-md-last ms-auto">
            <div class="nav-item">
              <!-- Sede Selection Badge -->
              <a href="#" class="nav-link px-2 d-flex align-items-center gap-1 small fw-bold" id="btn-change-sede" title="Cambiar sede">
                <i class="bi bi-geo-alt-fill text-gold"></i>
                <span id="header-sede-name" class="d-inline">Elegir sede</span>
                <i class="bi bi-chevron-down extra-small d-none d-md-inline"></i>
              </a>
            </div>
            <div class="nav-item me-2 me-md-3">
              <!-- BCV Rate Badge -->
              <span class="nav-link px-2 d-none d-md-flex align-items-center gap-1 small fw-bold text-gold" title="Dólar BCV">
                <i class="bi bi-currency-dollar"></i>
                <span id="bcv_rate_badge"><?php echo number_format($bcv_rate_header,2); ?> Bs</span>
              </span>
            </div>
            <div class="nav-item">
              <!-- Desktop/Mobile Cart Button -->
              <a href="#" class="nav-link px-2 d-flex align-items-center gap-2" title="Mi Orden" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
                <span class="d-none d-md-inline fw-bold" id="header-total">
                  <?php 
                  $total_header = 0;
                  $cart_count_header = 0;
                  if(isset($_SESSION["cart"])){
                    foreach($_SESSION["cart"] as $s){
                      $p = ProductData::getById($s["product_id"]);
                      $total_header += $p->price * $s["q"];
                      $cart_count_header += $s["q"];
                    }
                  }
                  $coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
                  echo $coin_symbol.number_format($total_header,2,".",",");
                  ?>
                </span>
                <div class="position-relative">
                  <i class="bi bi-cart3 h2 mb-0"></i>
                  <?php if($cart_count_header>0): ?>
                  <span class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle-x" id="global-cart-badge"><?php echo $cart_count_header; ?></span>
                  <?php endif; ?>
                </div>
              </a>
            </div>
            <div class="nav-item ms-2 ms-md-3">
              <!-- PEDIR (último, pegado a la esquina superior derecha) -->
              <a href="#menu-anchor" id="btn-nav-pedir" class="btn btn-warning style-yellow-btn rounded-pill fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 text-nowrap" title="Ver menú y pedir">
                <i class="bi bi-basket3"></i>
                <span>PEDIR</span>
              </a>
            </div>
          </div>
        </div>
      </header>

      <!-- CINTA SUPERIOR: cierre por sede (dinámica según la sede elegida) -->
      <div id="sede-closed-ribbon" class="tt-ribbon d-print-none d-none">
        <i class="bi bi-clock-history"></i>
        <span class="tt-ribbon-msg">ESTAMOS CERRADOS</span>
        <span class="tt-ribbon-sep">·</span>
        <span class="tt-ribbon-time" id="sede-closed-name"></span>
        <span class="tt-ribbon-sep">·</span>
        <span class="tt-ribbon-time" id="sede-closed-horario"></span>
      </div>

      <!-- Offcanvas Cart (Mobile/Desktop Swipe) -->
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
        <div class="offcanvas-header" style="background: linear-gradient(135deg,#e0a96d,#b87e38); color: #000000;">
          <h5 class="offcanvas-title fw-bold" id="offcanvasCartLabel"><i class="bi bi-bag-heart me-2"></i> Mi Orden</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0" id="offcanvas-cart-container">
           <?php View::load("cart-side"); ?>
        </div>
      </div>

      <!-- Sticky Bottom Order Bar (Mobile only) -->
      <div class="sticky-bottom-bar d-md-none shadow-lg">
        <div class="container d-flex align-items-center justify-content-between py-2">
          <div>
            <div class="small text-white-50">Total de tu orden:</div>
            <div class="h2 fw-bold mb-0" id="mobile-total-display">
               <?php echo $coin_symbol.number_format($total_header,2,".",","); ?>
            </div>
          </div>
          <button class="btn btn-warning btn-lg rounded-pill fw-bold px-4" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
            VER MI ORDEN <i class="bi bi-chevron-right ms-1"></i>
          </button>
        </div>
      </div>

      <div class="page-wrapper">

        <?php View::load("index"); ?>
        
        <script>
        const TITO_STORE_CLOSED = <?php echo $store_closed ? "true" : "false"; ?>;
        const TITO_CLOSED_MSG = "Estamos cerrados. Abrimos hoy a las <?php echo $horario_open_display; ?> y atendemos de <?php echo $horario_display; ?>. ¡Te esperamos!";
        function showStoreClosedAlert() {
          if (!TITO_STORE_CLOSED) { return false; }
          Swal.fire({
            icon: "error",
            title: "ESTAMOS CERRADOS",
            html: TITO_CLOSED_MSG,
            background: "#000000",
            color: "#ffffff",
            iconColor: "#ff2a2a",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#ff2a2a",
            customClass: { title: "tt-swal-closed-title" }
          });
          return true;
        }
        $(document).ready(function() {
          $("#btn-nav-pedir").on("click", function(e) {
            e.preventDefault();
            const target = document.getElementById("menu-anchor");
            if (target) { $("html,body").animate({ scrollTop: target.offsetTop - 90 }, 500); }
          });
          $("#btn-change-sede").click(function(e) {
            e.preventDefault();
            if(typeof openSedeModal === "function") { openSedeModal(); }
          });
          const urlParams = new URLSearchParams(window.location.search);
          if (urlParams.get('msg') === 'order_success') {
             Swal.fire({
               icon: "success",
               title: "¡Pedido registrado!",
               html: "Tu WhatsApp se ha abierto para enviar el detalle.",
               confirmButtonText: "Perfecto",
               confirmButtonColor: "#e0a96d"
             });
          }
        });
        </script>

        <?php 
        $footer_sedes = SedeData::getActives();
        $footer_sede = count($footer_sedes)>0 ? $footer_sedes[0] : null;
        ?>
        <footer class="tt-footer pt-5 pb-3" id="footer-contact">
          <div class="container-xl">
            <div class="row g-4">
              <div class="col-md-5 col-lg-4 mb-4 mb-md-0">
                <img src="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>/assets/img/logo.png" alt="Alianzas Blissful" class="brand-logo mb-3" style="filter: brightness(1);">
                <p class="text-white-50 small mb-3">Disfruta de la mejor experiencia gastronómica desde tu celular. Escanea, ordena y disfruta.</p>
                <div class="d-flex gap-2">
                   <a href="<?php echo $fb_footer ? (preg_match('/^https?:\/\//i',$fb_footer) ? $fb_footer : "https://facebook.com/".ltrim($fb_footer,"@")) : "#"; ?>" class="social-icon" title="Facebook" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
                   <a href="<?php echo $ig_footer ? (preg_match('/^https?:\/\//i',$ig_footer) ? $ig_footer : "https://instagram.com/".ltrim($ig_footer,"@")) : "#"; ?>" class="social-icon" title="Instagram" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
                   <a href="https://api.whatsapp.com/send?phone=<?php echo preg_replace('/\D/','',$whatsapp_footer); ?>" class="social-icon" target="_blank" rel="noopener" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                </div>
              </div>
              <div class="col-6 col-md-3 col-lg-2">
                <h4 class="h5 mb-3">Mapa del Sitio</h4>
                <ul class="list-unstyled small d-grid gap-2">
                  <li><a href="./">Inicio</a></li>
                  <li><a href="#menu-anchor">Menú Completo</a></li>
                  <li><a href="#horarios-section">Horarios y Sucursales</a></li>
                </ul>
              </div>
              <div class="col-6 col-md-4 col-lg-3">
                <h4 class="h5 mb-3">Horario</h4>
                <ul class="list-unstyled small d-grid gap-1 mb-0">
                  <li><span class="tt-hand tt-hand-sm">Horarios por sede</span> <span class="float-end text-white fw-bold">consulta tu sede</span></li>
                  <li class="text-white-50">Entrega a domicilio y recogida en sucursal.</li>
                </ul>
              </div>
              <div class="col-12 col-lg-3">
                <h4 class="h5 mb-3">Contacto</h4>
                <ul class="list-unstyled small d-grid gap-2 mb-0">
                  <li class="d-flex gap-2"><i class="bi bi-geo-alt-fill text-gold mt-1"></i><span class="text-white-50"><?php echo $footer_sede ? htmlspecialchars($footer_sede->address) : "Av. Principal #123"; ?></span></li>
                  <li class="d-flex gap-2"><i class="bi bi-telephone-fill text-gold mt-1"></i><span class="text-white-50"><?php echo $footer_sede ? htmlspecialchars($footer_sede->phone) : "555-MENU"; ?></span></li>
                  <li class="d-flex gap-2"><i class="bi bi-whatsapp text-gold mt-1"></i><a href="https://api.whatsapp.com/send?phone=<?php echo preg_replace('/\D/','',$whatsapp_footer); ?>" target="_blank" rel="noopener" class="text-white-50">Pedir por WhatsApp</a></li>
                </ul>
              </div>
            </div>
            <div class="tt-footer-bottom text-center small py-3 mt-4">
              <div class="mb-1">&copy; 2026 Alianzas Blissful. Todos los derechos reservados.</div>
              <div class="d-flex justify-content-center gap-2 flex-wrap">
                <span>Términos y condiciones</span>
                <span class="tt-footer-sep" aria-hidden="true">&middot;</span>
                <span>Tratamiento de datos</span>
              </div>
              <div class="mt-1">Desarrollado por Sara0102leon y Keyler948</div>
            </div>
          </div>
        </footer>
      </div>
    </div>
    
    <style>
      .scroll-hide::-webkit-scrollbar { display: none; }
      .scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
      .sticky-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        z-index: 1040; /* Just below offcanvas */
        padding-bottom: env(safe-area-inset-bottom);
      }
      .offcanvas { width: 350px !important; }
      @media (max-width: 576px) {
        .offcanvas { width: 100% !important; }
      }
      @media (min-width: 1200px) {
        .col-xl-custom-8 {
          flex: 0 0 auto;
          width: 12.5%; /* 100/8 */
        }
      }
      .extra-small { font-size: 0.75rem; line-height: 1.2; }
    </style>
    <script src="./dist/js/tabler.min.js" defer></script>
    <script>
    // Shím de compatibilidad: Bootstrap 5 (Tabler) no trae el plugin jQuery .modal()
    // que usan los scripts del menú. Reimplementa show/hide sobre la API nativa.
    (function($){
      if ($.fn.modal) { return; }
      $.fn.modal = function(cmd, opts){
        return this.each(function(){
          var el = this;
          if (!window.bootstrap || typeof window.bootstrap.Modal === "undefined") { return; }
          var inst = window.bootstrap.Modal.getInstance(el);
          if (cmd === "show" || cmd === "toggle") {
            if (!inst) { inst = new window.bootstrap.Modal(el, opts || {}); }
            inst.show();
          } else if (cmd === "hide") {
            if (inst) { inst.hide(); }
          } else if (cmd === "dispose") {
            if (inst) { inst.dispose(); }
          }
        });
      };
    })(jQuery);
    </script>
    <script>
    // Oculta la pantalla de carga cuando la página termina de cargar
    (function() {
      var hideLoader = function() { setTimeout(function() { var el = document.getElementById("tt-loader"); if (el) { el.classList.add("done"); } }, 350); };
      if (document.readyState === "complete") { hideLoader(); }
      else { window.addEventListener("load", hideLoader); }
      setTimeout(hideLoader, 4500);
    })();
    </script>

    <!-- Cart Alert (SweetAlert2 accesible, grande y con paleta del proyecto) -->
    <script>
    function showCartToast(msg, title) {
      try {
        if (typeof Swal === "undefined") { return; }
        Swal.close();
      } catch(e) {}
      Swal.fire({
        icon: "success",
        title: title || "¡AGREGADO AL CARRITO!",
        html: '<span class="tt-swal-msg">' + msg + '</span>',
        background: "#000000",
        color: "#e0a96d",
        iconColor: "#e0a96d",
        confirmButtonText: "OK",
        confirmButtonColor: "#e0a96d",
        customClass: {
          popup: "tt-swal-popup",
          htmlContainer: "tt-swal-html",
          confirmButton: "tt-swal-confirm"
        },
        timer: 3400,
        timerProgressBar: true,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        returnFocus: false
      });
    }
    </script>
  </body>
</html>