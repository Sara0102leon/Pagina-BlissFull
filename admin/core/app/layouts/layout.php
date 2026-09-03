<?php 
$current_view = isset($_GET["view"]) ? $_GET["view"] : "";
$admin_titles = array(
  "home"=>"Dashboard","sells"=>"Ventas","sellreport"=>"Reportes de Ventas",
  "products"=>"Productos","categories"=>"Categorías","clients"=>"Clientes",
  "slider"=>"Slider","users"=>"Usuarios","settings"=>"Configuración",
  "spends"=>"Gastos","persons"=>"Personas","forms"=>"Formularios",
  "table"=>"Tablas","login"=>"Iniciar Sesión"
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
          <div class="sidebar-section-title">Menú Principal</div>

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
            <i class="bi bi-tags"></i><span>Categorías</span>
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
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && isset($_GET["opt"]) && $_GET["opt"]=="payment") ? " active" : ""; ?>" href="./?view=settings&opt=payment"><i class="bi bi-credit-card"></i> Métodos de Pago</a>
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && isset($_GET["opt"]) && $_GET["opt"]=="units") ? " active" : ""; ?>" href="./?view=settings&opt=units"><i class="bi bi-rulers"></i> Unidades</a>
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && isset($_GET["opt"]) && $_GET["opt"]=="ingredients") ? " active" : ""; ?>" href="./?view=settings&opt=ingredients"><i class="bi bi-egg-fried"></i> Ingredientes</a>
              <a class="sidebar-subitem<?php echo ($current_view=="settings" && isset($_GET["opt"]) && $_GET["opt"]=="bebidas") ? " active" : ""; ?>" href="./?view=settings&opt=bebidas"><i class="bi bi-cup-straw"></i> Bebidas</a>
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
          <button type="button" class="icon-btn" id="btnSidebarToggle" aria-label="Abrir menú">
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
              <a href="#" class="icon-btn" data-bs-toggle="dropdown" aria-label="Notificaciones" title="Pedidos pendientes de pago y pedidos programados">
                <i class="bi bi-bell"></i>
                <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem;">0</span>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow dropdown-menu-card shadow" style="max-width: 420px;">
                <div class="dropdown-header d-flex align-items-center border-bottom">
                  <span class="fw-bold h5 mb-0"><i class="bi bi-calendar-check text-info me-1"></i> Pedidos programados</span>
                  <span class="ms-auto badge bg-info text-dark" id="notif-sch-count">0</span>
                </div>
                <div id="notif-list-scheduled" class="list-group list-group-flush overflow-auto border-bottom" style="max-height: 40vh;"></div>
                <div class="dropdown-header d-flex align-items-center border-bottom">
                  <span class="fw-bold h6 mb-0"><i class="bi bi-bell-fill text-danger me-1"></i> Pedidos sin pagar (30+ min)</span>
                  <span class="ms-auto badge bg-danger" id="notif-pend-count">0</span>
                </div>
                <div id="notif-list" class="list-group list-group-flush overflow-auto" style="max-height: 40vh;"></div>
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
                <a href="./?view=settings&opt=all" class="dropdown-item">Configuración</a>
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
      @keyframes notifToastIn{ from{ transform: translateX(120%); opacity:0; } to{ transform: translateX(0); opacity:1; } }
      #notif-badge.anim-flash{ animation: notifFlash 0.5s ease-in-out 2; }
      .notif-item{ border-left: 4px solid rgba(184,126,56,0.4); }
      .notif-item.notif-sch{ border-left-color: rgba(23,162,184,0.6); }
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
            $("#notif-pend-count").text("0");
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
          $("#notif-pend-count").text(orders.length);
          $("#notif-updated").text("Actualizado: " + new Date().toLocaleTimeString());
        }

        function fmtUntil(sec){
          sec = Math.max(0, parseInt(sec) || 0);
          var d = Math.floor(sec/86400), h = Math.floor((sec%86400)/3600), m = Math.floor((sec%3600)/60);
          if(d > 0){ return "en " + d + "d " + h + "h"; }
          if(h > 0){ return "en " + h + "h " + m + "m"; }
          return "en " + m + "m";
        }

        function renderScheduled(list){
          var $el = $("#notif-list-scheduled");
          var $cnt = $("#notif-sch-count");
          if(!list || !list.length){
            $el.html('<div class="text-center py-3 text-muted small"><i class="bi bi-calendar-x text-muted d-block h4 mb-1"></i>No hay pedidos programados pendientes</div>');
            $cnt.text("0");
            return;
          }
          var html = "";
          list.slice().sort(function(a,b){ return a.scheduled_ts - b.scheduled_ts; }).forEach(function(o){
            var when = fmtUntil(o.diff);
            html += '<a class="list-group-item list-group-item-action notif-item notif-sch" href="./?view=sells&opt=open&id=' + o.id + '">';
            html += '<div class="d-flex align-items-start">';
            html += '<div class="flex-fill">';
            html += '<div class="fw-bold small"><i class="bi bi-calendar-check me-1 text-info"></i>#' + o.id + ' \u00b7 ' + o.client + '</div>';
            html += '<div class="small text-muted">' + o.scheduled_at + ' \u00b7 ' + o.paymethod + ' \u00b7 $' + Number(o.total).toFixed(2) + (o.phone ? ' \u00b7 ' + o.phone : '') + '</div>';
            html += '</div>';
            html += '<span class="ms-2 badge rounded-pill text-white" style="background:#17a2b8;"><i class="bi bi-hourglass-split me-1"></i>' + when + '</span>';
            html += '</div>';
            html += '</a>';
          });
          $el.html(html);
          $cnt.text(list.length);
        }

        function flashBell(){
          var $b = $("#notif-badge");
          $b.addClass("anim-flash");
          setTimeout(function(){ $b.removeClass("anim-flash"); }, 1100);
        }

        // Sonidos de notificación (MP3) - uno por tipo de aviso
        var SOUNDS = {
          retraso: "./storage/sounds/aviso_de_retraso.mp3",        // pedido sin pago 30+ min
          tiempo:  "./storage/sounds/aviso_de_tiempo.mp3",         // hitos de pedido programado
          creado:  "./storage/sounds/pedido_programado_creado.mp3",// nuevo pedido PROGRAMADO
          nueva:   "./storage/sounds/nueva_orden.mp3"              // nuevo pedido NORMAL (no programado)
        };
        var _audioUnlocked = false;
        // Reproduce un sonido (se ignora si el navegador aún no permite audio)
        function playSound(key){
          var file = SOUNDS[key];
          if(!file){ return; }
          try{
            var a = new Audio(file);
            a.volume = 1;
            a.play().catch(function(){});
          }catch(e){}
        }
        // Reproduce cada tipo una vez, espaciados, para no saturar
        function playSounds(keys){
          var seen = {};
          var list = [];
          (keys || []).forEach(function(k){ if(!seen[k]){ seen[k]=true; list.push(k); } });
          list.forEach(function(k, i){
            setTimeout(function(){ playSound(k); }, i*450);
          });
        }
        // Desbloquea el audio en la interacción del usuario (política de autoplay).
        // IMPORTANTE: debe reproducirse un audio CON VOLUMEN dentro del gesto para que
        // el navegador autorice los siguientes play() con sonido. Un probe muteado/volumen 0
        // NO desbloquea el audio en Chrome. Se re-intenta en cada interacción hasta lograrlo.
        function unlockAudio(){
          if(_audioUnlocked){ return; }
          try{
            var probe = new Audio(SOUNDS.nueva);
            probe.volume = 0.05;
            var p = probe.play();
            if(p && p.then){
              p.then(function(){ _audioUnlocked = true; }).catch(function(){});
            } else {
              _audioUnlocked = true;
            }
          }catch(e){}
        }

        // Toasts en la esquina superior derecha
        function ensureToastWrap(){
          if(document.getElementById("notif-toast-wrap")){ return; }
          var w = document.createElement("div");
          w.id = "notif-toast-wrap";
          w.style.cssText = "position:fixed;top:16px;right:16px;z-index:100000;display:flex;flex-direction:column;gap:10px;max-width:360px;";
          document.body.appendChild(w);
        }
        function showToast(title, msg){
          ensureToastWrap();
          var t = document.createElement("div");
          t.style.cssText = "background:linear-gradient(135deg,#2a1c10,#1a140c);color:#f5e6c8;border:1px solid #e0a96d;border-left:5px solid #e0a96d;border-radius:10px;padding:12px 14px;box-shadow:0 10px 30px rgba(0,0,0,.5);display:flex;gap:10px;align-items:flex-start;animation:notifToastIn .3s ease;";
          t.innerHTML = '<i class="bi bi-calendar-check" style="font-size:1.3rem;color:#e0a96d;"></i><div style="flex:1;"><div style="font-weight:700;margin-bottom:2px;">'+title+'</div><div style="font-size:.85rem;opacity:.9;">'+msg+'</div></div>';
          var close = document.createElement("button");
          close.innerHTML = "&times;";
          close.style.cssText = "background:none;border:none;color:#e0a96d;font-size:1.1rem;cursor:pointer;padding:0 2px;";
          close.onclick = function(){ t.remove(); };
          t.appendChild(close);
          document.getElementById("notif-toast-wrap").appendChild(t);
          setTimeout(function(){ t.style.opacity="0"; t.style.transition="opacity .4s"; setTimeout(function(){ t.remove(); }, 400); }, 7500);
        }

        function loadNotifications(){
          $.getJSON("./?action=notifications&opt=json", function(res){
            if(!res || !res.ok){ return; }
            var pend = res.count || 0;
            var sch  = res.scheduled_count || 0;
            var total = pend + sch;
            if(total > 0){
              $("#notif-badge").removeClass("d-none").text(total > 99 ? "99+" : total);
            } else {
              $("#notif-badge").addClass("d-none").text("0");
            }
            // Avisos recién disparados -> sonido + toast + parpadeo
            var sounds = [];

            // NUEVO pedido (cualquier tipo): detectado por id no visto antes
            var justSch = []; // pedidos programados recién creados en ESTA consulta
            var maxIdThisPoll = _lastSeenId;
            if(res.recent && res.recent.length){
              res.recent.forEach(function(o){
                if(o.id > maxIdThisPoll){ maxIdThisPoll = o.id; }
                if(_seenOrders.indexOf(o.id) >= 0){ return; }
                _seenOrders.push(o.id);
                // En la PRIMERA consulta de la sesión, si no teníamos un id guardado
                // simplemente registramos el baseline sin sonar. Si sí lo teníamos,
                // cualquier pedido con id mayor al visto antes SÍ suena (aunque se
                // haya recargado la página entre tanto).
                if(_first && _lastSeenId === 0){ return; }
                if(_first && o.id <= _lastSeenId){ return; }
                if(o.scheduled){
                  sounds.push("creado"); // sonido de pedido programado
                  justSch.push(o.id);
                  flashBell();
                  showToast("🔔 Nuevo pedido programado - #" + o.id, "" + o.client + " &mdash; programado para " + o.scheduled_at);
                } else {
                  sounds.push("nueva"); // sonido de pedido normal
                  flashBell();
                  showToast("🔔 Nuevo pedido - #" + o.id, "" + o.client + " &mdash; " + o.paymethod + " $" + Number(o.total).toFixed(2));
                }
              });
            }
            // Actualiza el id máximo visto y lo persiste
            if(maxIdThisPoll > _lastSeenId){ _lastSeenId = maxIdThisPoll; trackLastSeenId(); }

            // Hitos de pedidos programados (24h, 6h, 1h, 15min). 'created' y los de un pedido recién creado se omiten para no repetir sonido
            if(res.new_alerts && res.new_alerts.length){
              res.new_alerts.forEach(function(a){
                if(a.hook === "created"){ return; }
                if(justSch.indexOf(a.id) >= 0){ return; } // ya sonó al crearse
                sounds.push("tiempo");
                flashBell();
                showToast("🔔 " + a.label + " - pedido #" + a.id, "" + a.client + " &mdash; programado para " + a.scheduled_at);
              });
            }

            // Pedidos sin pago 30+ min (nuevos)
            if(res.orders && res.orders.length){
              res.orders.forEach(function(o){
                if(_seenPend.indexOf(o.id) >= 0){ return; }
                _seenPend.push(o.id);
                if(_first){ return; }
                sounds.push("retraso");
                flashBell();
                showToast("🔔 Pedido sin pago 30+ min - #" + o.id, "" + o.client + " &mdash; " + o.paymethod + " $" + Number(o.total).toFixed(2));
              });
            } else if(total > 0 && _totalPrev >= 0 && total > _totalPrev){
              flashBell();
            }
            _first = false;
            if(sounds.length){ playSounds(sounds); }
            _totalPrev = total;
            renderOrders(res.orders || []);
            renderScheduled(res.scheduled || []);
          }).fail(function(){});
        }
        // Estado recordado entre consultas para detectar avisos nuevos.
        // Se persiste el último id de pedido visto en sessionStorage para que,
        // si el admin recarga la página dentro de la misma sesión de navegador,
        // los pedidos NUEVOS (con id mayor) sigan notificándose con sonido.
        var _seenOrders = [];
        var _seenPend = [];
        var _totalPrev = -1;
        var _first = true;
        var _lastSeenId = 0;
        try{
          _lastSeenId = parseInt(sessionStorage.getItem("blissfull_last_buy_id") || "0", 10) || 0;
        }catch(e){}
        function trackLastSeenId(){
          try{ sessionStorage.setItem("blissfull_last_buy_id", String(_lastSeenId)); }catch(e){}
        }

        // Desbloquea el audio en la primera interacción del usuario (política de autoplay).
        // Se re-intenta en cada interacción hasta que el navegador autorice el audio.
        document.addEventListener("click", unlockAudio);
        document.addEventListener("keydown", unlockAudio);

        loadNotifications();
        setInterval(loadNotifications, 20000);
      }
    </script>
    <!-- Guía interactiva del panel (tour con spotlight) -->
    <script src="assets/js/admin-tour.js?v=4"></script>
    <!-- Confirmación genérica de borrados (respaldos nativos si no hay Swal) -->
    <script src="assets/js/tt-confirm-delete.js"></script>
  </body>
</html>