<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Taco Menu - Menú Digital</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- CSS files -->
    <link href="./dist/css/tabler.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <script src="assets/jquery/jquery.min.js"></script>
    <style>
      :root {
        --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        --secondary-color: #c0392b; /* Deep Red */
        --primary-color: #e67e22; /* Appentizing Orange */
      }
      body {
        font-family: 'Inter', sans-serif;
        overflow-x: hidden;
      }
      h1,h2,h3, .navbar-brand { font-family: 'Outfit', sans-serif; }
    </style>
  </head>
  <body>
    <?php 
    $bcv_rate_header = 0;
    $bcv_row = ConfigurationData::getByPreffix("bcv_rate");
    if($bcv_row && $bcv_row->val){ $bcv_rate_header = floatval($bcv_row->val); }
    ?>
    <div class="page">
      <!-- Top Navbar -->
      <header class="navbar navbar-expand-md navbar-light d-print-none shadow-sm sticky-top">
        <div class="container-xl">
          <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="navbar-brand pe-0 pe-md-3">
            <a href="./" class="text-decoration-none d-flex align-items-center">
              <span class="avatar avatar-sm bg-primary-lt text-primary me-2 rounded">
                 <i class="bi bi-shop h3 mb-0"></i>
              </span>
              <span class="h2 mb-0 fw-bold">TACO <span class="text-primary">MENU</span></span>
            </a>
          </div>
          
          <div class="navbar-nav flex-row order-md-last ms-auto">
            <div class="nav-item me-3">
              <!-- BCV Rate Badge -->
              <span class="nav-link px-2 d-none d-md-flex align-items-center gap-1 small fw-bold text-success" title="Dólar BCV">
                <i class="bi bi-currency-dollar"></i>
                <span id="bcv_rate_badge"><?php echo number_format($bcv_rate_header,2); ?> Bs</span>
              </span>
            </div>            <div class="nav-item me-3">
              <!-- Desktop/Mobile Cart Button -->
              <a href="#" class="nav-link px-2 d-flex align-items-center gap-2" title="Mi Orden" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
                <span class="d-none d-md-inline fw-bold text-primary" id="header-total">
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
          </div>
        </div>
      </header>

      <!-- Offcanvas Cart (Mobile/Desktop Swipe) -->
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
        <div class="offcanvas-header bg-primary text-white">
          <h5 class="offcanvas-title fw-bold" id="offcanvasCartLabel"><i class="bi bi-bag-heart me-2"></i> Mi Orden de Tacos</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
            <div class="h2 fw-bold text-white mb-0" id="mobile-total-display">
               <?php echo $coin_symbol.number_format($total_header,2,".",","); ?>
            </div>
          </div>
          <button class="btn btn-warning btn-lg rounded-pill fw-bold px-4" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
            VER MI ORDEN <i class="bi bi-chevron-right ms-1"></i>
          </button>
        </div>
      </div>

      <div class="page-wrapper">
        <div class="collapse navbar-collapse bg-white border-bottom" id="navbar-menu">
          <div class="container-xl py-3">
             <ul class="navbar-nav">
                <li class="nav-item active"><a class="nav-link" href="./">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="./">Nuestro Menú Completo</a></li>
             </ul>
          </div>
        </div>

        <?php View::load("index"); ?>
        
        <script>
        $(document).ready(function() {
          const urlParams = new URLSearchParams(window.location.search);
          if (urlParams.get('msg') === 'order_success') {
             // SweetAlert or similar could be used here, but for now standard Bootstrap/JS
             alert("¡Pedido registrado exitosamente! Tu WhatsApp se ha abierto para enviar el detalle.");
          }
        });
        </script>
          <div class="container-xl">
            <div class="row">
              <div class="col-md-4 mb-4 mb-md-0">
                <h3 class="text-white mb-3">Taco Menu</h3>
                <p class="text-white-50 small">Disfruta de la mejor experiencia gastronómica desde tu celular. Escanea, ordena y disfruta.</p>
                <div class="d-flex gap-3">
                   <a href="#" class="text-white-50 h4"><i class="bi bi-facebook"></i></a>
                   <a href="#" class="text-white-50 h4"><i class="bi bi-instagram"></i></a>
                   <a href="#" class="text-white-50 h4"><i class="bi bi-whatsapp"></i></a>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <h4 class="text-white mb-3">Menú</h4>
                <ul class="list-unstyled text-white-50 small">
                  <li><a href="#" class="text-reset text-decoration-none">Tacos</a></li>
                  <li><a href="#" class="text-reset text-decoration-none">Bebidas</a></li>
                  <li><a href="#" class="text-reset text-decoration-none">Postres</a></li>
                </ul>
              </div>
              <div class="col-6 col-md-4">
                <h4 class="text-white mb-3">Contacto</h4>
                <ul class="list-unstyled text-white-50 small">
                  <li>Av. Principal #123</li>
                  <li>Tel: 555-MENU</li>
                  <li>Abierto: 10:00 - 22:00</li>
                </ul>
              </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center text-white-50 small">
              &copy; 2026 Taco Menu Digital. Powered by Antigravity.
            </div>
          </div>
        </footer>
      </div>
    </div>
    
    <style>
      .scroll-hide::-webkit-scrollbar { display: none; }
      .scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
      .navbar-light { background: white !important; }
      .category-pill {
        display: inline-block;
        white-space: nowrap;
        padding: 0.5rem 1.25rem;
        border-radius: 2rem;
        background: #f1f3f5;
        color: #495057;
        font-weight: 600;
        text-decoration: none;
        transition: 0.2s;
        border: 1px solid transparent;
      }
      .category-pill.active {
        background: var(--primary-color);
        color: white;
      }
      .category-pill:hover:not(.active) {
        background: #e9ecef;
      }
      .sticky-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #e67e22; /* Primary Orange */
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

    <!-- Cart Toast Notification -->
    <div id="cartToast" class="cart-toast d-flex align-items-center gap-2 shadow-lg">
      <i class="bi bi-check-circle-fill text-success h4 mb-0"></i>
      <span id="cartToastMsg" class="fw-bold"></span>
    </div>
    <style>
      .cart-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1060;
        background: #fff;
        color: #212529;
        border-left: 4px solid #2fb344;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 0.85rem;
        max-width: 280px;
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
        transition: opacity 0.3s ease, transform 0.3s ease;
      }
      .cart-toast.show {
        opacity: 1;
        transform: translateY(0);
      }
    </style>
    <script>
    let cartToastTimer = null;
    function showCartToast(msg) {
      $("#cartToastMsg").text(msg);
      const el = document.getElementById("cartToast");
      el.classList.add("show");
      clearTimeout(cartToastTimer);
      cartToastTimer = setTimeout(function() { el.classList.remove("show"); }, 2200);
    }
    </script>
  </body>
</html>

