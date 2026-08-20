<?php 
$current_view = isset($_GET["view"]) ? $_GET["view"] : "";
$admin_titles = array(
  "home"=>"Dashboard","sells"=>"Ventas","sellreport"=>"Reportes de Ventas",
  "products"=>"Productos","categories"=>"CategorÃ­as","clients"=>"Clientes",
  "slider"=>"Slider","users"=>"Usuarios","settings"=>"ConfiguraciÃ³n",
  "spends"=>"Gastos","persons"=>"Personas","forms"=>"Formularios",
  "table"=>"Tablas","login"=>"Iniciar SesiÃ³n"
);
$page_title = isset($admin_titles[$current_view]) ? $admin_titles[$current_view] : "Panel Administrativo";
$is_auth = isset($_SESSION["user_id"]);
if($is_auth && $current_view=="login"){ header("Location: ./"); exit; }
if(!$is_auth && $current_view!="login"){ header("Location: ./?view=login"); exit; }
$sys_active = ($current_view=="users" || $current_view=="settings") ? "active" : "";
$sys_open = $sys_active!="" ? " show" : "";
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>.: Alianzas Blissful - Panel Administrativo :.</title>
    <link rel="icon" type="image/png" href="assets/favicon-32.png"/>
    <!-- CSS files -->
    <link href="./dist/css/tabler.min.css" rel="stylesheet"/>
    <link href="./dist/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.css">
    <!-- Admin Jobie Style (cargado al final para sobreescribir Tabler) -->
    <link rel="stylesheet" href="assets/css/admin-custom.css?v=8">
    <!-- Guía interactiva del panel (tour con spotlight) -->
    <link rel="stylesheet" href="assets/css/admin-tour.css?v=3">
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="assets/datatables/datatables.min.css">
    <script src="assets/datatables/datatables.min.js"></script>
    <style>
      @import url('https://rsms.me/inter/inter.css');
      :root {
        --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      }
      body {
        font-feature-settings: "cv03", "cv04", "cv11";
      }
    </style>
  </head>
  <body class="admin-jobie<?php echo $is_auth ? " is-auth" : ""; ?>">
    <div id="tt-admin-loader" class="tt-admin-loader" aria-hidden="true">
      <div class="tt-admin-loader-box">
        <img src="assets/logo.png" alt="Blissful" class="tt-admin-loader-logo">
        <div class="tt-admin-loader-ring"></div>
        <p class="tt-admin-loader-text">Cargando panel...</p>
      </div>
    </div>
    <div class="app-shell">

      <?php if($is_auth): ?>
      <!-- ============ SIDEBAR VERTICAL (Jobie) ============ -->
      <aside class="app-sidebar" id="appSidebar">
        <a href="./" class="sidebar-logo">
          <img src="assets/logo.png" alt="Logo Blissful" class="sidebar-logo-img">
          <span class="brand-name">ALIANZAS <span>BLISSFUL</span><small>Panel Admin</small></span>
        </a>

        <nav class="sidebar-nav">
          <div class="sidebar-section-title">MenÃº Principal</div>

          <a class="sidebar-item<?php echo ($current_view=="" || $current_view=="home") ? " active" : ""; ?>" href="./">
            <i class="bi bi-grid-fill"></i><span>Dashboard</span>
          </a>
          <a class="sidebar-item<?php echo $current_view=="sells" ? " active" : ""; ?>" href="./?view=sells&opt=all">
            <i class="bi bi-cart-check"></i><span>Ventas</span>
          </a>
          <a class="sidebar-item<?php echo $current_view=="sellreport" ? " active" : ""; ?>" href="./?view=sellreport">
            <i class="bi bi-graph-up"></i><span>Reportes</span>
          </a>
          <a class="sidebar-item<?php echo $current_view=="products" ? " active" : ""; ?>" href="./?view=products&opt=all">
            <i class="bi bi-box-seam"></i><span>Productos</span>
          </a>
          <a class="sidebar-item<?php echo $current_view=="categories" ? " active" : ""; ?>" href="./?view=categories&opt=all">
            <i class="bi bi-tags"></i><span>CategorÃ­as</span>
          </a>
          <a class="sidebar-item<?php echo $current_view=="clients" ? " active" : ""; ?>" href="./?view=clients&opt=all">
            <i class="bi bi-people"></i><span>Clientes</span>
          </a>
          <a class="sidebar-item<?php echo $current_view=="slider" ? " active" : ""; ?>" href="./?view=slider&opt=all">
            <i class="bi bi-images"></i><span>Slider</span>
          </a>

          <button type="button" class="sidebar-item sidebar-toggle<?php echo $sys_active!="" ? " active" : ""; ?>" data-bs-toggle="collapse" data-bs-target="#menuSistema" aria-expanded="<?php echo $sys_active!="" ? "true" : "false"; ?>">
            <i class="bi bi-gear"></i><span>Sistema</span>
            <i class="bi bi-chevron-right chevron"></i>
          </button>
          <div class="collapse<?php echo $sys_open; ?>" id="menuSistema">
            <div class="sidebar-submenu">
              <a class="sidebar-subitem<?php echo $current_view=="users" ? " active" : ""; ?>" href="./?view=users&opt=all"><i class="bi bi-person-badge"></i> Usuarios</a>
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && (!isset($_GET["opt"]) || $_GET["opt"]=="all")) ? " active" : ""; ?>" href="./?view=settings&opt=all"><i class="bi bi-sliders"></i> Ajustes</a>
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && isset($_GET["opt"]) && $_GET["opt"]=="sedes") ? " active" : ""; ?>" href="./?view=settings&opt=sedes"><i class="bi bi-shop"></i> Sedes</a>
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && isset($_GET["opt"]) && $_GET["opt"]=="payment") ? " active" : ""; ?>" href="./?view=settings&opt=payment"><i class="bi bi-credit-card"></i> MÃ©todos de Pago</a>
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && isset($_GET["opt"]) && $_GET["opt"]=="units") ? " active" : ""; ?>" href="./?view=settings&opt=units"><i class="bi bi-rulers"></i> Unidades</a>
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && isset($_GET["opt"]) && $_GET["opt"]=="ingredients") ? " active" : ""; ?>" href="./?view=settings&opt=ingredients"><i class="bi bi-egg-fried"></i> Ingredientes</a>
            </div>
          </div>

          <a class="sidebar-item sidebar-logout" href="./?action=access&opt=logout">
            <i class="bi bi-box-arrow-right"></i><span>Salir</span>
          </a>
        </nav>
      </aside>
      <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
      <?php endif; ?>

      <!-- ============ MAIN + HEADER (Jobie) ============ -->
      <div class="app-main">
        <header class="app-header">
          <?php if($is_auth): ?>
          <button type="button" class="icon-btn" id="btnSidebarToggle" aria-label="Abrir menÃº">
            <i class="bi bi-list"></i>
          </button>
          <?php else: ?>
          <a href="./" class="app-brand">
            <img src="assets/logo.png" alt="Logo Blissful" class="app-brand-img">
            <span>ALIANZAS <b>BLISSFUL</b></span>
          </a>
          <div class="app-header-right">
            <a href="./?view=login" class="icon-btn btn-login-link">
              <i class="bi bi-box-arrow-in-right"></i>
            </a>
          </div>
          <?php endif; ?>

          <h1 class="app-title"><?php echo htmlspecialchars($page_title); ?></h1>

          <?php if($is_auth): ?>
          <div class="app-header-right">
            <!-- Botón Guía del panel -->
            <button type="button" class="icon-btn me-2" id="btn-tour-guide" title="Guía del panel: aprende a usar cada módulo" aria-label="Guía del panel">
              <i class="bi bi-question-lg"></i>
            </button>

            <div class="nav-item dropdown me-2" id="notif-wrap">
              <a href="#" class="icon-btn" data-bs-toggle="dropdown" aria-label="Notificaciones" title="Pedidos pendientes de pago">
                <i class="bi bi-bell"></i>
                <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem;">0</span>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow dropdown-menu-card shadow">
                <div class="dropdown-header d-flex align-items-center border-bottom">
                  <span class="fw-bold h5 mb-0"><i class="bi bi-bell-fill text-danger me-1"></i> Pedidos sin pagar (30+ min)</span>
                </div>
                <div id="notif-list" class="list-group list-group-flush overflow-auto" style="max-height: 65vh;"></div>
                <div class="dropdown-header border-top d-flex justify-content-between">
                  <span class="small text-muted" id="notif-updated"></span>
                  <a href="./?view=sells&opt=all" class="small fw-bold link-primary">Ver todas las ventas <i class="bi bi-arrow-right"></i></a>
                </div>
              </div>
            </div>

            <?php $u = UserData::getById($_SESSION["user_id"]); ?>
            <div class="nav-item dropdown">
              <a href="#" class="profile-chip" data-bs-toggle="dropdown">
                <span class="avatar avatar-sm"><i class="bi bi-person"></i></span>
                <span class="profile-name d-none d-xl-block">
                  <span class="fw-bold d-block" style="line-height:1.1;"><?php echo htmlspecialchars($u->name." ".$u->lastname); ?></span>
                  <span class="small text-muted" style="line-height:1.1;">Administrador</span>
                </span>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <a href="./?view=settings&opt=all" class="dropdown-item">ConfiguraciÃ³n</a>
                <div class="dropdown-divider"></div>
                <a href="./?action=access&opt=logout" class="dropdown-item text-danger">Salir</a>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </header>

        <div class="app-content">
          <?php View::load("index"); ?>

          <footer class="app-footer">
            &copy; 2026 Alianzas Blissful. Todos los derechos reservados. Desarrollado por Sara0102leon y Keyler948
          </footer>
        </div>
      </div>
    </div>

    <style>
      @keyframes notifFlash{ 0%,100%{ transform: translate(-50%,-50%) scale(1); } 50%{ transform: translate(-50%,-50%) scale(1.6); } }
      #notif-badge.anim-flash{ animation: notifFlash 0.5s ease-in-out 2; }
      .notif-item{ border-left: 4px solid rgba(184,126,56,0.4); }
      .notif-item:hover{ border-left-width: 4px; }
      .tt-admin-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(ellipse at 50% 30%, #2a1c10 0%, #16120c 65%, #0c0a07 100%);
        transition: opacity 0.35s ease, visibility 0.35s ease;
      }
      .tt-admin-loader.done { opacity: 0; visibility: hidden; pointer-events: none; }
      .tt-admin-loader-box { text-align: center; }
      .tt-admin-loader-logo { height: 52px; width: auto; margin-bottom: 16px; }
      .tt-admin-loader-ring {
        width: 46px;
        height: 46px;
        margin: 0 auto 12px;
        border: 4px solid rgba(184, 126, 56, 0.2);
        border-top-color: #b87e38;
        border-right-color: #e0a96d;
        border-radius: 50%;
        animation: ttAdminSpin 0.85s linear infinite;
      }
      @keyframes ttAdminSpin { to { transform: rotate(360deg); } }
      .tt-admin-loader-text { color: #e0a96d; font-weight: 600; margin: 0; letter-spacing: 0.04em; }
    </style>
    <script src="./dist/libs/apexcharts/dist/apexcharts.min.js" defer></script>
    <script src="./dist/js/tabler.min.js" defer></script>
    <script type="text/javascript">
      $(document).ready(function(){
        $(".datatable").DataTable();
        initNotifications();
        var $toggle = document.getElementById("btnSidebarToggle");
        var $backdrop = document.getElementById("sidebarBackdrop");
        if($toggle){ $toggle.addEventListener("click", function(){ document.body.classList.toggle("sidebar-open"); }); }
        if($backdrop){ $backdrop.addEventListener("click", function(){ document.body.classList.remove("sidebar-open"); }); }
      });

      // Oculta la pantalla de carga cuando termina de cargar
      (function(){
        var hideLoader = function(){
          setTimeout(function(){
            var el = document.getElementById("tt-admin-loader");
            if(el){ el.classList.add("done"); }
          }, 300);
        };
        if(document.readyState === "complete"){ hideLoader(); }
        else { window.addEventListener("load", hideLoader); }
        setTimeout(hideLoader, 4500);
      })();

      function initNotifications(){
        var lastCount = -1;
        var LEVEL = {
          risk:     { label: "Riesgo de no pagar",   color: "#b87e38" },
          critical: { label: "Posible pedido falso", color: "#e0a96d" }
        };

        function fmtDur(sec){
          sec = Math.max(0, parseInt(sec) || 0);
          var h = Math.floor(sec/3600), m = Math.floor((sec%3600)/60), s = sec%60;
          if(h > 0){ return h + "h " + String(m).padStart(2,"0") + "m"; }
          return String(m).padStart(2,"0") + ":" + String(s).padStart(2,"0");
        }

        function renderOrders(orders){
          if(!orders || !orders.length){
            $("#notif-list").html('<div class="text-center py-4 text-muted small"><i class="bi bi-check-circle text-success d-block h3 mb-1"></i>No hay pedidos sin pago a tiempo</div>');
            return;
          }
          var html = "";
          orders.forEach(function(o){
            var lvl = LEVEL[o.level] || LEVEL.risk;
            var zone = o.pickup ? "Recoger en sucursal" : (o.zone ? o.zone : "Delivery");
            html += '<a class="list-group-item list-group-item-action notif-item" href="./?view=sells&opt=open&id=' + o.id + '">';
            html += '<div class="d-flex align-items-start">';
            html += '<div class="flex-fill">';
            html += '<div class="fw-bold small">#' + o.id + ' \u00b7 ' + o.client + '</div>';
            html += '<div class="small text-muted">' + o.paymethod + ' \u00b7 ' + zone + ' \u00b7 $' + Number(o.total).toFixed(2) + (o.phone ? ' \u00b7 ' + o.phone : '') + '</div>';
            html += '</div>';
            html += '<span class="ms-2 badge rounded-pill text-white" style="background:' + lvl.color + ';">' + lvl.label + '</span>';
            html += '</div>';
            html += '<div class="small mt-1" style="color:' + lvl.color + ';"><i class="bi bi-clock-history me-1"></i>Sin pago desde ' + o.created_at + '</div>';
            html += '</a>';
          });
          $("#notif-list").html(html);
          $("#notif-updated").text("Actualizado: " + new Date().toLocaleTimeString());
        }

        function flashBell(){
          var $b = $("#notif-badge");
          $b.addClass("anim-flash");
          setTimeout(function(){ $b.removeClass("anim-flash"); }, 1100);
        }

        function loadNotifications(){
          $.getJSON("./?action=notifications&opt=json", function(res){
            if(!res || !res.ok){ return; }
            var count = res.count || 0;
            if(count > 0){
              $("#notif-badge").removeClass("d-none").text(count > 99 ? "99+" : count);
            } else {
              $("#notif-badge").addClass("d-none").text("0");
            }
            if(lastCount >= 0 && count > lastCount){ flashBell(); }
            lastCount = count;
            renderOrders(res.orders || []);
          }).fail(function(){});
        }

        loadNotifications();
        setInterval(loadNotifications, 20000);
      }
    </script>
    <!-- Guía interactiva del panel (tour con spotlight) -->
    <script src="assets/js/admin-tour.js?v=3"></script>
  </body>
</html>