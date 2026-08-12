<?php 
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$img_default = ConfigurationData::getByPreffix("general_img_default")?ConfigurationData::getByPreffix("general_img_default")->val:"assets/img/default.png";
$whatsapp_number = ConfigurationData::getByPreffix("general_whatsapp")?ConfigurationData::getByPreffix("general_whatsapp")->val:"+5215574506232";
$slides = SlideData::getPublics();
$categories = CategoryData::getPublics();
$paymethods = PaymethodData::getActives();
$bcv_rate = 0;
$bcv_rate_row = ConfigurationData::getByPreffix("bcv_rate");
if($bcv_rate_row && $bcv_rate_row->val){ $bcv_rate = floatval($bcv_rate_row->val); }
$pm_bank = ConfigurationData::getByPreffix("pago_movil_bank")?ConfigurationData::getByPreffix("pago_movil_bank")->val:"";
$pm_ci = ConfigurationData::getByPreffix("pago_movil_ci")?ConfigurationData::getByPreffix("pago_movil_ci")->val:"";
$pm_phone = ConfigurationData::getByPreffix("pago_movil_phone")?ConfigurationData::getByPreffix("pago_movil_phone")->val:"";
$pm_titular = ConfigurationData::getByPreffix("pago_movil_titular")?ConfigurationData::getByPreffix("pago_movil_titular")->val:"";
$zelle_contact = ConfigurationData::getByPreffix("zelle_contact")?ConfigurationData::getByPreffix("zelle_contact")->val:"";
$binance_contact = ConfigurationData::getByPreffix("binance_contact")?ConfigurationData::getByPreffix("binance_contact")->val:"";
$zones = DeliveryZoneData::getAll();
$sedes = SedeData::getActives();
$sede_json = array();
foreach($sedes as $sd){ array_push($sede_json, array("id"=>$sd->id,"name"=>$sd->name,"address"=>$sd->address,"phone"=>$sd->phone)); }
$sede_json = json_encode($sede_json);
$base_url = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]!="off" ? "https" : "http")."://".$_SERVER["HTTP_HOST"];
$script_dir = str_replace("\\","/",dirname($_SERVER["SCRIPT_NAME"]));
if(strpos($script_dir,"/core")!==false){ $script_dir = substr($script_dir,0,strpos($script_dir,"/core")); }
else if(strpos($script_dir,"/admin")!==false){ $script_dir = substr($script_dir,0,strpos($script_dir,"/admin")); }
$base_url .= $script_dir;
$bcv_rate_js = $bcv_rate>0 ? $bcv_rate : 0;
?>

<!-- Modal Selección de Sede -->
<div class="modal modal-blur fade" id="modal-sede" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header" style="background: linear-gradient(135deg,#922235,#51111f); color: white;">
        <div>
          <h5 class="modal-title fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>¿Cuál sede te queda más cerca?</h5>
          <div class="small opacity-75">Elige tu sucursal y tu pedido se enviará directo a su WhatsApp</div>
        </div>
      </div>
      <div class="modal-body p-3">
        <?php if(count($sedes)>0): ?>
        <div class="form-hint mb-3">Toca la sede que te queda más cerca y continúa:</div>
        <div id="sede-list">
          <?php foreach($sedes as $sd): ?>
          <label class="sede-option d-block mb-2" data-id="<?php echo $sd->id; ?>" data-name="<?php echo htmlspecialchars($sd->name); ?>" data-phone="<?php echo preg_replace('/\D/','',$sd->phone); ?>">
            <div class="d-flex align-items-center gap-3 border rounded-3 p-3 shadow-sm sede-card">
              <div class="sede-check"><i class="bi bi-check-circle-fill h4 mb-0"></i></div>
              <div class="flex-fill">
                <div class="fw-bold h6 mb-0"><?php echo htmlspecialchars($sd->name); ?></div>
                <div class="text-muted small"><?php echo htmlspecialchars($sd->address); ?></div>
              </div>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <p class="alert alert-warning mb-0">No hay sedes disponibles por ahora.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer d-flex flex-column gap-2 border-0">
        <button type="button" id="btn_confirm_sede" class="btn btn-primary w-100 py-3 rounded-pill fw-bold" <?php echo count($sedes)==0?'disabled':''; ?>>
          CONTINUAR A ESTA SEDE <i class="bi bi-arrow-right ms-2"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Checkout -->
<div class="modal modal-blur fade" id="modal-checkout" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-cart-check-fill me-2"></i> Estás a un paso de tu pizza</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         <button type="button" class="btn btn-light border w-100 mb-3 d-flex align-items-center justify-content-between text-start" id="btn_checkout_sede">
           <span><i class="bi bi-geo-alt-fill me-2 text-success"></i><span class="fw-bold" id="checkout_sede_name">Elegir tu sede</span></span>
           <span class="small text-muted">Cambiar ▾</span>
         </button>
         <div class="mb-3">
            <label class="form-label fw-bold">Tu Nombre</label>
            <input type="text" id="order_name" class="form-control" placeholder="¿Cómo te llamamos?">
         </div>
         <div class="mb-3">
            <label class="form-label fw-bold">Tu Teléfono (WhatsApp)</label>
            <input type="tel" id="order_phone" class="form-control" placeholder="Ej: 5215512345678">
         </div>
         <div class="mb-3">
            <label class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" id="order_pickup">
              <span class="form-check-label fw-bold">PASARÉ A RECOGER EN LA SUCURSAL</span>
            </label>
         </div>
         <div class="mb-3">
            <label class="form-label fw-bold">Zona de entrega</label>
            <select id="order_zone" class="form-select">
              <option value="0" data-price="0">Comer aquí / Recoger en sucursal</option>
              <?php foreach($zones as $z): ?>
              <option value="<?php echo $z->id; ?>" data-price="<?php echo $z->price; ?>"><?php echo htmlspecialchars($z->name); ?> (+ $<?php echo number_format($z->price,2,".",","); ?>)</option>
              <?php endforeach; ?>
            </select>
         </div>
         <div class="mb-0" id="address_container">
            <label class="form-label fw-bold">Dirección de Entrega</label>
            <textarea id="order_address" class="form-control" rows="2" placeholder="Calle, número, cruzamientos..."></textarea>
         </div>
         <hr>
         <div class="mb-2">
            <label class="form-label fw-bold">¿Cómo vas a pagar?</label>
            <?php foreach($paymethods as $pm):
              $pm_name = strtolower($pm->name);
              $is_pm = strpos($pm_name,"pago movil")!==false || strpos($pm_name,"pago_movil")!==false;
            ?>
            <label class="form-check mb-1">
              <input class="form-check-input payment-method" type="radio" name="order_paymethod" value="<?php echo $pm->id; ?>" data-name="<?php echo htmlspecialchars($pm->name); ?>" data-pm="<?php echo $is_pm?1:0; ?>" <?php echo $pm->id==1?'checked':''; ?>>
              <span class="form-check-label"><?php echo htmlspecialchars($pm->name); ?></span>
            </label>
            <?php endforeach; ?>
         </div>
         <div id="pm_box" class="alert alert-success py-2 small d-none">
            <strong><i class="bi bi-credit-card-fill me-1"></i> PAGO MÓVIL</strong><br>
            Banco: <span id="pm_bank"><?php echo htmlspecialchars($pm_bank); ?></span> | Cédula: <span id="pm_ci"><?php echo htmlspecialchars($pm_ci); ?></span><br>
            Teléfono: <span id="pm_phone"><?php echo htmlspecialchars($pm_phone); ?></span><br>
            Titular: <span id="pm_titular"><?php echo htmlspecialchars($pm_titular); ?></span><br>
            <strong><i class="bi bi-cash-coin me-1"></i> Monto a pagar: <span id="pm_amount">$0.00</span></strong>
         </div>
         <div class="mb-0 bg-light rounded-3 p-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Subtotal</span><span id="ck_subtotal">$0.00</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Delivery</span><span id="ck_delivery">$0.00</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between fw-bold h5 mb-1">
              <span>TOTAL</span><span id="ck_total">$0.00</span>
            </div>
            <div class="text-end text-muted small" id="ck_total_bs"></div>
         </div>
         <div class="alert alert-warning py-2 small mt-3">
            <i class="bi bi-exclamation-triangle me-1"></i> El negocio confirmará tu pedido por WhatsApp antes de prepararlo.
         </div>
      </div>
      <div class="modal-footer d-flex flex-column gap-2 border-0">
        <button type="button" id="btn_confirm_order" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">
           CONFIRMAR Y PEDIR POR WHATSAPP <i class="bi bi-whatsapp ms-2"></i>
        </button>
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Seguir pidiendo</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Extras -->
<div class="modal modal-blur fade" id="modal-extras" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="extras_modal_title">Extras</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-hint mb-3">Elige los ingredientes adicionales para tu producto:</div>
        <div id="extras_list" class="mb-2"></div>
        <div class="bg-light rounded-3 p-3 mb-2">
          <div class="d-flex justify-content-between fw-bold">
            <span>Total del producto:</span><span id="extras_total">$0.00</span>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex flex-column gap-2 border-0">
        <button type="button" id="btn_confirm_extras" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
          AGREGAR AL CARRITO <i class="bi bi-cart-plus ms-2"></i>
        </button>
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<div class="page-body">

  <!-- Hero Slider Full-Bleed (Edge-to-Edge) - banners dinámicos del Admin -->
  <section class="hero-slider-fullwidth">
    <div id="heroSlider" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3500">

      <div class="carousel-inner">
        <?php if(count($slides)>0): ?>
          <?php foreach($slides as $idx => $s): ?>
          <div class="carousel-item hero-slide-item <?php echo $idx==0?'active':''; ?>">
            <img src="admin/storage/slides/<?php echo $s->image; ?>" class="hero-slide-img" alt="<?php echo htmlspecialchars($s->title); ?>">
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="carousel-item hero-slide-item active">
            <div class="hero-slide-fallback"></div>
          </div>
        <?php endif; ?>
      </div>

      <?php if(count($slides)>1): ?>
      <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
      </button>
      <?php endif; ?>

    </div>
  </section>

  <div class="container-xl">
    <div class="text-center menu-cta">
      <a href="#menu-anchor" id="btn-go-menu" class="btn btn-warning rounded-pill px-5 py-3 fw-bold style-yellow-btn">
        VER MENÚ <i class="bi bi-arrow-down-circle-fill ms-1"></i>
      </a>
    </div>
    <div class="row g-4 mt-2">
      
      <!-- Menu Section (Full Width) -->
      <div class="col-md-12">

        <!-- Category Dynamic Navigation -->
        <div class="mb-4 overflow-auto scroll-hide pb-2" id="menu-anchor">
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-category-ajax active" data-cat=""><i class="bi bi-star-fill me-1"></i> Destacados</button>
            <?php foreach($categories as $cat): ?>
            <button type="button" class="btn-category-ajax text-nowrap" data-cat="<?php echo $cat->id; ?>">
              <?php echo htmlspecialchars($cat->name); ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
           <div>
              <h2 class="h1 mb-0 fw-bold" id="grid-title">Menú Principal</h2>
              <p class="text-muted mb-0">Explora nuestras especialidades y realiza tu pedido</p>
           </div>
           <!-- Dynamic Search Bar -->
           <div class="search-container">
              <div class="search-box">
                <i class="bi bi-search search-box-icon"></i>
                <input type="text" id="product_search" class="form-control search-box-input" placeholder="Buscar platillo..." style="min-width: 280px;">
              </div>
           </div>
        </div>

        <div id="product-grid-container">
           <?php 
            $_POST["q"] = "";
            $_POST["cat_id"] = "";
            View::load("product-grid"); 
           ?>
        </div>

      </div>


    </div>
  </div>
</div>

<script>
const COIN = "$";
const BS_SYMBOL = "Bs";
const SITE_BASE = "<?php echo $base_url; ?>";
const SEDES = <?php echo $sede_json; ?>;
let currentCatId = "";
let currentSearch = "";
let pendingExtrasPid = null;
let pendingExtrasName = "";
let pendingExtras = [];

function fmt(n){ return COIN + n.toFixed(2); }
function fmtBs(n){ return BS_SYMBOL + " " + n.toFixed(2); }
function fmtComma(n){ return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","); }

// ===== Sede Selection =====
function getSelectedSede() {
  const sid = localStorage.getItem("blissfull_sede_id");
  if (!sid) return null;
  let found = null;
  SEDES.forEach(function(s){ if (String(s.id) === String(sid)) { found = s; } });
  return found;
}

function setSedeUI() {
  const sede = getSelectedSede();
  const headerEl = document.getElementById("header-sede-name");
  const checkoutEl = document.getElementById("checkout_sede_name");
  const label = sede ? sede.name : "Elegir tu sede";
  if (headerEl) { headerEl.textContent = label; }
  if (checkoutEl) { checkoutEl.textContent = label; }
}

function selectSedeCard(id) {
  $(".sede-option").removeClass("selected");
  $(".sede-option[data-id='" + id + "']").addClass("selected");
}

function openSedeModal() {
  const sede = getSelectedSede();
  if (sede) { selectSedeCard(sede.id); }
  $("#modal-sede").modal("show");
}

function updateGrid() {
  $.post("./?action=cart&opt=search", { q: currentSearch, cat_id: currentCatId }, function(data) {
    $("#product-grid-container").html(data);
  });
}

function updateUI() {
  const total = $("#whatsapp_total_text").val();
  const count = $("#cart_total_count").val();
  
  // Updates Header
  $("#header-total").text(total);
  if(parseInt(count) > 0) {
    if($("#global-cart-badge").length) { $("#global-cart-badge").text(count); }
    else { $(".bi-cart3").parent().append('<span class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle-x" id="global-cart-badge">'+count+'</span>'); }
    $(".sticky-bottom-bar").fadeIn();
  } else {
    $("#global-cart-badge").remove();
    $(".sticky-bottom-bar").fadeOut();
  }
  
  // Updates Mobile Bar
  $("#mobile-total-display").text(total);
}

function addToCart(pid, pname, extrasJson) {
  $.post("./?action=cart&opt=add&ajax=1", { product_id: pid, extras: extrasJson || "[]" }, function(data) {
     $("#cart-container").html(data);
     $("#offcanvas-cart-container").html(data);
     updateUI();
     if(typeof showCartToast === "function") { showCartToast("Se agregó: " + pname); }
  });
}

function incCart(key, pname) {
  $.post("./?action=cart&opt=inc&ajax=1", { key: key }, function(data) {
     $("#cart-container").html(data);
     $("#offcanvas-cart-container").html(data);
     updateUI();
     if(typeof showCartToast === "function") { showCartToast("Se incrementó: " + pname); }
  });
}

function decCart(key, pname) {
  $.post("./?action=cart&opt=dec&ajax=1", { key: key }, function(data) {
     $("#cart-container").html(data);
     $("#offcanvas-cart-container").html(data);
     updateUI();
     if(typeof showCartToast === "function") { showCartToast("Se disminuyó: " + pname); }
  });
}

function removeFromCart(key, pname) {
  $.post("./?action=cart&opt=del&ajax=1", { key: key }, function(data) {
     $("#cart-container").html(data);
     $("#offcanvas-cart-container").html(data);
     updateUI();
     if(typeof showCartToast === "function") { showCartToast("Se eliminó: " + pname); }
  });
}

function clearCart() {
  $.post("./?action=cart&opt=clear&ajax=1", {}, function(data) {
     $("#cart-container").html(data);
     $("#offcanvas-cart-container").html(data);
     updateUI();
  });
}

// ===== Extras Modal =====
function openExtrasModal(pid, pname, extrasJson) {
  pendingExtrasPid = pid;
  pendingExtrasName = pname;
  try { pendingExtras = JSON.parse(extrasJson || "[]"); } catch(e) { pendingExtras = []; }
  $("#extras_modal_title").text("Extras - " + pname);
  let html = "";
  pendingExtras.forEach(function(e, i) {
    html += '<label class="form-check mb-2 extra-opt">';
    html += '<input class="form-check-input" type="checkbox" data-price="' + e.price + '" data-idx="' + i + '">';
    html += '<span class="form-check-label">' + e.name + ' <span class="text-primary fw-bold">(+$' + e.price.toFixed(2) + ')</span></span>';
    html += '</label>';
  });
  $("#extras_list").html(html);
  updateExtrasTotal();
  $("#modal-extras").modal("show");
}

function updateExtrasTotal() {
  let total = 0;
  $(".extra-opt input:checked").each(function(){ total += parseFloat($(this).data("price")); });
  $("#extras_total").text(fmt(total));
}

function confirmExtras() {
  const sel = [];
  $(".extra-opt input:checked").each(function(){
    const e = pendingExtras[parseInt($(this).data("idx"))];
    sel.push({ name: e.name, price: parseFloat(e.price) });
  });
  $("#modal-extras").modal("hide");
  addToCart(pendingExtrasPid, pendingExtrasName, JSON.stringify(sel));
}

// ===== Checkout Totals =====
function getCartItems() {
  try { return JSON.parse($("#cart_items_json").val() || "[]"); } catch(e) { return []; }
}

function computeTotals(items, delivery, deliveryPrice) {
  let subtotal = 0;
  items.forEach(function(it){
    let unit = it.price;
    if(delivery && it.price_llevar > 0) { unit = it.price_llevar; }
    (it.extras || []).forEach(function(e){ unit += parseFloat(e.price); });
    subtotal += unit * it.q;
  });
  const deliveryCost = delivery ? deliveryPrice : 0;
  return { subtotal: subtotal, delivery: deliveryCost, total: subtotal + deliveryCost };
}

function updateCheckoutUI() {
  const isPickup = $("#order_pickup").is(":checked");
  const zoneSel = $("#order_zone").val();
  const delivery = !isPickup && zoneSel !== "0";
  const zoneOpt = zoneSel !== "0" ? $("#order_zone option:selected") : null;
  const deliveryPrice = delivery ? parseFloat(zoneOpt.data("price")) : 0;
  const t = computeTotals(getCartItems(), delivery, deliveryPrice);
  const paySel = $(".payment-method:checked");
  const isPM = paySel.data("pm") == 1;

  $("#ck_subtotal").text(fmt(t.subtotal));
  $("#ck_delivery").text(delivery ? fmt(t.delivery) : "Comer aquí / Recoger");
  $("#ck_total").text(fmt(t.total));
  $("#ck_total_bs").text(bcvRate > 0 ? "≈ " + fmtBs(t.total * bcvRate) : "");

  if(isPM){
    $("#pm_amount").text(fmt(t.total) + " (" + (bcvRate>0 ? fmtBs(t.total*bcvRate) : "Bs a confirmar") + ")");
    $("#pm_box").removeClass("d-none");
  } else {
    $("#pm_box").addClass("d-none");
  }
}

function itemsWhatsAppText(items, delivery) {
  const lines = [];
  items.forEach(function(it){
    let unit = it.price;
    if(delivery && it.price_llevar > 0) { unit = it.price_llevar; }
    let extrasTxt = "";
    (it.extras || []).forEach(function(e){ unit += parseFloat(e.price); extrasTxt += " + " + e.name; });
    lines.push("- " + it.q + " x " + it.name + extrasTxt + " (" + fmt(unit) + ") = " + fmt(unit * it.q) + "%0A");
  });
  return lines.join("");
}

$(document).ready(function() {
  // Hero "VER MENÚ" smooth scroll
  $("#btn-go-menu").on("click", function(e) {
    e.preventDefault();
    const target = document.getElementById("menu-anchor");
    if (target) { $("html,body").animate({ scrollTop: target.offsetTop - 90 }, 500); }
  });

  // Sede Selection Events
  $(".sede-option").click(function() {
    selectSedeCard($(this).data("id"));
  });
  $("#btn_confirm_sede").click(function() {
    const sel = $(".sede-option.selected");
    if (sel.length === 0) {
      Swal.fire({ icon: "warning", title: "Elige una sede", text: "Selecciona la sede que te queda más cerca para continuar.", confirmButtonColor: "#c67718" });
      return;
    }
    localStorage.setItem("blissfull_sede_id", sel.data("id"));
    setSedeUI();
    $("#modal-sede").modal("hide");
  });
  $("#btn_checkout_sede").click(function() { openSedeModal(); });

  setSedeUI();
  if (!getSelectedSede()) {
    setTimeout(function() { openSedeModal(); }, 600);
  }

  // Category AJAX Toggle
  $(".btn-category-ajax").click(function() {
    $(".btn-category-ajax").removeClass("active");
    $(this).addClass("active");
    currentCatId = $(this).data("cat");
    const name = $(this).text().trim();
    $("#grid-title").html(currentCatId === "" ? '<i class="bi bi-star-fill me-1"></i> Destacados' : '<i class="bi bi-folder2-open me-1"></i> ' + name);
    updateGrid();
  });

  // Dynamic Search
  let searchTimer;
  $("#product_search").on("keyup", function() {
    clearTimeout(searchTimer);
    currentSearch = $(this).val().trim();
    
    searchTimer = setTimeout(function() {
      if(currentSearch !== "") {
        $("#grid-title").html('<i class="bi bi-search me-1"></i> Buscando: ' + currentSearch);
      } else {
        const activeName = $(".btn-category-ajax.active").text().trim();
        $("#grid-title").html(currentCatId === "" ? '<i class="bi bi-star-fill me-1"></i> Destacados' : '<i class="bi bi-folder2-open me-1"></i> ' + activeName);
      }
      updateGrid();
    }, 400); 
  });

  // Extras Modal Events
  $("#extras_list").on("change", ".extra-opt input", updateExtrasTotal);
  $("#btn_confirm_extras").click(confirmExtras);

  // Pickup Toggle
  $("#order_pickup").change(function() {
    if($(this).is(":checked")) {
      $("#address_container").fadeOut();
      $("#order_zone").val("0").prop("disabled", true);
    } else {
      $("#address_container").fadeIn();
      $("#order_zone").prop("disabled", false);
    }
    updateCheckoutUI();
  });

  // Zone / Payment Method
  $("#order_zone").change(updateCheckoutUI);
  $(".payment-method").change(updateCheckoutUI);

  // BCV Rate
  let bcvRate = <?php echo $bcv_rate_js; ?>;
  function bcvLoadRate(showSpinner) {
    $.get("./?action=bcv&opt=get", function(data) {
      try {
        const res = typeof data === "string" ? JSON.parse(data) : data;
        if (res.rate && res.rate > 0) {
          bcvRate = res.rate;
          $("#bcv_rate_badge").text(res.rate.toFixed(2) + " Bs");
          updateCheckoutUI();
        }
      } catch(e) {}
    });
  }

  // Confirm Order
  $("#btn_confirm_order").click(async function() {
    const sede = getSelectedSede();
    if (!sede) {
      Swal.fire({ icon: "warning", title: "Elige tu sede", text: "Primero selecciona la sede que te queda más cerca, así tu pedido llega al WhatsApp correcto.", confirmButtonColor: "#c67718" }).then(function(){ openSedeModal(); });
      return;
    }
    const name = $("#order_name").val().trim();
    const phone = $("#order_phone").val().trim();
    let address = $("#order_address").val().trim();
    const isPickup = $("#order_pickup").is(":checked");
    const zoneSel = $("#order_zone").val();
    const paymethodId = $(".payment-method:checked").val();
    const paymethodName = $(".payment-method:checked").data("name");
    const isPM = $(".payment-method:checked").data("pm") == 1;

    if (name === "" || phone === "") {
      Swal.fire({ icon: "warning", title: "Faltan datos", text: "Por favor completa tu nombre y teléfono.", confirmButtonColor: "#c67718" });
      return;
    }
    if (isPickup) { address = "Recoger en sucursal"; }
    else if (address === "") {
      Swal.fire({ icon: "warning", title: "Faltan datos", text: "Por favor escribe tu dirección de entrega.", confirmButtonColor: "#c67718" });
      return;
    }
    if (!isPickup && zoneSel === "0") {
      Swal.fire({ icon: "warning", title: "Zona de entrega", text: "Por favor selecciona tu zona de entrega (o marca que pasarás a recoger).", confirmButtonColor: "#c67718" });
      return;
    }

    const delivery = !isPickup;
    const zoneOpt = delivery ? $("#order_zone option:selected") : null;
    const deliveryPrice = delivery ? parseFloat(zoneOpt.data("price")) : 0;
    const zoneName = delivery ? zoneOpt.text() : "Recoger en sucursal";
    const items = getCartItems();
    const t = computeTotals(items, delivery, deliveryPrice);

    const btn = $(this);
    btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span> Enviando...');

    $.post("./?action=cart&opt=buy", {
      name: name,
      phone: phone,
      address: address,
      sede_id: sede.id,
      paymethod_id: paymethodId,
      delivery_zone_id: delivery ? zoneSel : ""
    }, function(res) {
      clearCart();

      const whatsappNum = sede.phone ? String(sede.phone).replace(/\D/g, "") : "<?php echo $whatsapp_number; ?>";
      let msg = "*NUEVA ORDEN - ALIANZAS BLISSFUL*%0A%0A";
      msg += "*Sede:* " + sede.name + "%0A";
      msg += "*Cliente:* " + name + "%0A";
      msg += "*Teléfono:* " + phone + "%0A";
      if(delivery){
        msg += "*Dirección:* " + address + "%0A";
        msg += "*Zona (Delivery):* " + zoneName + "%0A";
        msg += "*Delivery:* " + fmt(t.delivery) + "%0A";
      } else {
        msg += "*Entrega:* Recoger en sucursal%0A";
      }
      msg += "*Pago:* " + paymethodName + "%0A%0A";
      msg += "*Productos:*%0A" + itemsWhatsAppText(items, delivery);
      msg += "%0A*------------------------------*%0A";
      msg += "*SUBTOTAL (US$): " + fmt(t.subtotal) + "*%0A";
      msg += "*TOTAL (US$): " + fmt(t.total) + "*%0A";
      msg += "*TOTAL (Bs): " + (bcvRate > 0 ? fmtBs(t.total * bcvRate) : "a confirmar") + "*%0A";
      msg += "*------------------------------*%0A";
      msg += isPM ? "_El cliente pagó/pagará el monto indicado. Solicita el capture de pago por este chat antes de confirmar._" : "_El cliente confirmará el pago._";

      window.open("https://api.whatsapp.com/send?phone=" + whatsappNum + "&text=" + msg, '_blank');
      $("#modal-checkout").modal("hide");
      Swal.fire({
        icon: "success",
        title: "¡Pedido enviado!",
        html: "Tu pedido ha sido enviado por WhatsApp. Te confirmaremos pronto.",
        confirmButtonText: "¡Genial!",
        confirmButtonColor: "#ffde64"
      }).then(function(){ location.reload(); });
    }).fail(function() {
      btn.prop("disabled", false).html('CONFIRMAR Y PEDIR POR WHATSAPP <i class="bi bi-whatsapp ms-2"></i>');
      Swal.fire({ icon: "error", title: "Error", text: "Ocurrió un error al registrar tu pedido. Intenta de nuevo.", confirmButtonColor: "#922235" });
    });
  });

  // Auto-refresh BCV rate every 10 minutes
  bcvLoadRate(false);
  setInterval(function() { bcvLoadRate(false); }, 600000);
});
</script>

<style>
.product-card { transition: 0.3s; cursor: default; }
.product-card:hover { transform: translateY(-5px); }
.btn-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
.scroll-hide::-webkit-scrollbar { display: none; }
.scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
.btn-category-ajax {
  border: 1px solid rgba(146, 34, 53, 0.6);
  background: transparent;
  padding: 0.5rem 1.25rem;
  border-radius: 2rem;
  font-weight: 600;
  color: #a0a0a0;
  transition: 0.2s;
}
.btn-category-ajax:hover { color: #ffffff; border-color: rgba(255, 222, 100, 0.5); }
.btn-category-ajax.active {
  background: rgba(255, 222, 100, 0.08);
  color: #ffde64;
  border-color: #ffde64;
  box-shadow: 0 0 0 1px rgba(255, 222, 100, 0.25);
}
.sede-option { cursor: pointer; }
.sede-card { transition: 0.2s; background: #270a16; }
.sede-option:hover .sede-card { border-color: rgba(255, 222, 100, 0.45); }
.sede-check { color: rgba(255, 222, 100, 0.4); }
.sede-option.selected .sede-card { border-color: #ffde64; background: rgba(255, 222, 100, 0.07); box-shadow: 0 4px 12px rgba(255, 222, 100, 0.15); }
.sede-option.selected .sede-check { color: #ffde64; }
</style>