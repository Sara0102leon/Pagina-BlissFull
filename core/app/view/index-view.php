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
foreach($sedes as $sd){
  $deliv_map = array();
  foreach(SedeDeliveryZoneData::getBySede($sd->id) as $sdzd){ $deliv_map[$sdzd->delivery_zone_id] = floatval($sdzd->price); }
  array_push($sede_json, array("id"=>$sd->id,"name"=>$sd->name,"address"=>$sd->address,"phone"=>$sd->phone,"delivery"=>$deliv_map));
}
$sede_json = json_encode($sede_json);
$base_url = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]!="off" ? "https" : "http")."://".$_SERVER["HTTP_HOST"];
$script_dir = str_replace("\\","/",dirname($_SERVER["SCRIPT_NAME"]));
if(strpos($script_dir,"/core")!==false){ $script_dir = substr($script_dir,0,strpos($script_dir,"/core")); }
else if(strpos($script_dir,"/admin")!==false){ $script_dir = substr($script_dir,0,strpos($script_dir,"/admin")); }
$base_url .= $script_dir;
$bcv_rate_js = $bcv_rate>0 ? $bcv_rate : 0;
$horario_display = isset($horario_display) ? $horario_display : "11:00 AM - 11:00 PM";
$tt_hours = array();
foreach(array("horario_lunes"=>"Lunes","horario_martes"=>"Martes","horario_miercoles"=>"Miércoles","horario_jueves"=>"Jueves","horario_viernes"=>"Viernes","horario_sabado"=>"Sábado","horario_domingo"=>"Domingo") as $hk=>$hl){
  $hr = ConfigurationData::getByPreffix($hk);
  $tt_hours[$hl] = ($hr && $hr->val!="") ? $hr->val : $horario_display;
}
$featured = ProductData::getFeatureds();
$hero_hand = ConfigurationData::getByPreffix("hero_hand")?ConfigurationData::getByPreffix("hero_hand")->val:"sabor casero que enamora";
$hero_title = ConfigurationData::getByPreffix("hero_title")?ConfigurationData::getByPreffix("hero_title")->val:"Alianza Blissfull";
$hero_sub = ConfigurationData::getByPreffix("hero_sub")?ConfigurationData::getByPreffix("hero_sub")->val:"pizzas y platillos caseros preparados al momento. Ordena desde tu celular y recíbelo caliente donde estés.";
$flotante_pid = ConfigurationData::getByPreffix("flotante_product_id")?ConfigurationData::getByPreffix("flotante_product_id")->val:"";
$flotante_pdata = null;
if($flotante_pid!=""){ $flotante_pdata = ProductData::getById($flotante_pid); }
$hero_img = $img_default;
if($flotante_pdata && $flotante_pdata->image!=""){
  $pimg = "admin/storage/products/".$flotante_pdata->image;
  if(file_exists($pimg)){ $hero_img = $pimg; }
}
if($hero_img==$img_default && count($featured)>0){ $hero_img = "admin/storage/products/".$featured[0]->image; if(!file_exists($hero_img)){ $hero_img=$img_default; } }
$flotante_extras_json = "[]";
$flotante_extras_json_js = "[]";
if($flotante_pdata){
  $extras_tmp = ProductExtraData::getByProductId($flotante_pdata->id);
  $extras_tmp2 = array();
  foreach($extras_tmp as $e){ $extras_tmp2[] = array("name"=>$e->name,"price"=>floatval($e->price)); }
  $flotante_extras_json = htmlspecialchars(json_encode($extras_tmp2), ENT_QUOTES);
  $flotante_extras_json_js = json_encode($extras_tmp2, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
}
$horarios = array();
$horario_keys = array("lunes","martes","miercoles","jueves","viernes","sabado","domingo");
foreach($horario_keys as $hk){
  $cfg = ConfigurationData::getByPreffix("horario_".$hk);
  $horarios[$hk] = $cfg && $cfg->val!="" ? $cfg->val : "10:00 - 22:00";
}
?>

<!-- Modal Selección de Sede -->
<div class="modal modal-blur fade" id="modal-sede" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header" style="background: linear-gradient(135deg,#e0a96d,#b87e38); color: #000000;">
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
      <div class="modal-header bg-primary">
        <h5 class="modal-title fw-bold"><i class="bi bi-cart-check-fill me-2"></i> Estás a un paso de tu pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         <button type="button" class="btn btn-light border w-100 mb-3 d-flex align-items-center justify-content-between text-start" id="btn_checkout_sede">
           <span><i class="bi bi-geo-alt-fill me-2 text-gold"></i><span class="fw-bold" id="checkout_sede_name">Elegir tu sede</span></span>
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
              <option value="<?php echo $z->id; ?>" data-price="<?php echo $z->price; ?>"><?php echo htmlspecialchars($z->name); ?></option>
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
              <span>TOTAL (US$)</span><span id="ck_total">$0.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center fw-bold text-gold">
              <span>TOTAL (Bs)</span><span id="ck_total_bs">Bs a confirmar</span>
            </div>
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
      <div class="modal-header bg-primary">
        <h5 class="modal-title fw-bold" id="extras_modal_title">Extras</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

  <!-- ============ HERO PRINCIPAL (TITO STYLE) ============ -->
  <section class="tt-hero">
    <!-- Hero Slider (banners dinámicos del Admin) como fondo -->
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

    <!-- Overlay de contenido: título, badges, imagen flotante y CTA -->
    <div class="tt-hero-content">
      <div class="container-xl">
        <div class="row align-items-center position-relative">
          <div class="col-lg-7 text-center text-lg-start">
            <div class="tt-hand tt-hand-big tt-neon mb-2"><?php echo htmlspecialchars($hero_hand); ?></div>
            <h1 class="tt-display tt-title mb-3"><?php echo htmlspecialchars($hero_title); ?></h1>
            <p class="tt-sub mx-auto mx-lg-0"><?php echo htmlspecialchars($hero_sub); ?></p>
            <div class="d-flex gap-3 justify-content-center justify-content-lg-start flex-wrap mt-4">
              <a href="#menu-anchor" id="btn-go-menu" class="btn btn-warning style-yellow-btn px-4 py-3 rounded-pill fw-bold text-nowrap">
                <i class="bi bi-basket3 me-2"></i> PEDIR AHORA
              </a>
              <a href="#horarios-section" class="btn tt-btn-ghost px-4 py-3 rounded-pill fw-bold text-nowrap">
                VER HORARIOS <i class="bi bi-arrow-down ms-1"></i>
              </a>
            </div>
            <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-3 mt-4 flex-wrap text-white-50 small">
              <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-truck text-gold"></i> Delivery a domicilio</span>
              <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-shop text-gold"></i> Recoge en sucursal</span>
              <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-whatsapp text-gold"></i> Pedidos por WhatsApp</span>
            </div>
          </div>
          <div class="col-lg-5 d-none d-lg-block">
            <?php if($flotante_pdata): ?>
            <a href="#" class="tt-float-link" onclick="openFlotante(); return false;" title="Agregar al pedido: <?php echo htmlspecialchars($flotante_pdata->name); ?>">
            <?php endif; ?>
              <img src="<?php echo $hero_img; ?>" class="tt-float-img" alt="Alianzas Blissful">
              <?php if($flotante_pdata): ?>
              <span class="tt-float-badge"><i class="bi bi-basket3 me-1"></i>PEDIR</span>
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ MENÚ SEMANAL (Carrusel Scroll/Swipe) ============ -->

  <div class="container-xl mt-5">
    <hr class="tt-divider">
    <div class="row g-4 mt-1">

      <!-- Menu Section (Full Width) -->
      <div class="col-md-12">

        <div class="tt-section-head text-center mb-4">
          <div class="tt-hand tt-hand-big">nuestros platillos</div>
          <h2 class="tt-display tt-section-title">MENÚ <span class="text-gold">PRINCIPAL</span></h2>
        </div>

        <!-- Category Dynamic Navigation -->
        <div class="mb-4 overflow-auto scroll-hide pb-2" id="menu-anchor">
          <div class="d-flex align-items-center gap-2 justify-content-center flex-wrap">
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
              <h2 class="h1 mb-0 fw-bold tt-display" id="grid-title" style="font-size: 1.8rem;">MENÚ PRINCIPAL</h2>
              <p class="text-muted mb-0">Explora nuestras especialidades y realiza tu pedido</p>
           </div>
           <!-- Dynamic Search Bar -->
           <div class="search-container">
              <div class="search-box">
                <i class="bi bi-search search-box-icon"></i>
                <input type="text" id="product_search" class="form-control search-box-input" placeholder="Buscar platillo...">
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

  <!-- ============ HORARIOS Y SUCURSALES ============ -->
  <section class="container-xl mt-5" id="horarios-section">
    <div class="tt-section-head text-center">
      <div class="tt-hand tt-hand-big">te esperamos</div>
      <h2 class="tt-display tt-section-title">HORARIOS <span class="text-gold">Y SUCURSALES</span></h2>
    </div>
    <div class="row g-4 mt-1">
      <div class="col-lg-5">
        <div class="tt-panel">
          <div class="tt-panel-title mb-3"><i class="bi bi-clock-fill text-gold me-2"></i>HORARIO DE ATENCIÓN</div>
          <?php $dias_fin = array("Sábado","Domingo"); foreach($tt_hours as $dlabel => $dhours): ?>
          <div class="tt-hours-row<?php echo in_array($dlabel,$dias_fin)?" tt-weekend":""; ?>">
            <span class="tt-day"><?php echo $dlabel; ?></span>
            <span class="tt-hours"><?php echo $dhours; ?></span>
          </div>
          <?php endforeach; ?>
          <div class="d-flex align-items-center gap-2 text-white-50 small mt-3 pt-3 border-top">
            <i class="bi bi-whatsapp text-gold"></i> Los pedidos por WhatsApp se atienden en el mismo horario.
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="row g-3">
          <?php foreach($sedes as $sd): ?>
          <div class="col-md-6">
            <div class="tt-map-card">
              <div class="tt-map-bg"></div>
              <div class="tt-map-road" style="top: 22%; left: -15%; width: 130%; height: 9px;"></div>
              <div class="tt-map-road" style="top: 66%; left: -15%; width: 130%; height: 7px;"></div>
              <div class="tt-map-pin"><i class="bi bi-geo-alt-fill"></i></div>
              <div class="tt-map-info">
                <div class="fw-bold"><i class="bi bi-shop me-1 text-gold"></i><?php echo htmlspecialchars($sd->name); ?></div>
                <div class="small text-white-50"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($sd->address); ?></div>
                <div class="small text-white-50"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($sd->phone); ?></div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if(count($sedes)==0): ?>
          <div class="col-12">
            <div class="tt-panel d-flex align-items-center justify-content-center text-white-50">
              <i class="bi bi-geo-alt-fill me-2 text-gold"></i> No hay sucursales registradas por ahora.
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
const COIN = "$";
const BS_SYMBOL = "Bs";
const SITE_BASE = "<?php echo $base_url; ?>";
const SEDES = <?php echo $sede_json; ?>;
const FLOTANTE_PID = <?php echo $flotante_pdata ? $flotante_pdata->id : 0; ?>;
const FLOTANTE_NAME = "<?php echo $flotante_pdata ? addslashes($flotante_pdata->name) : ''; ?>";
const FLOTANTE_EXTRAS = <?php echo $flotante_pdata ? $flotante_extras_json_js : "[]"; ?>;
let currentCatId = "";
let currentSearch = "";
let pendingExtrasPid = null;
let pendingExtrasName = "";
let pendingExtras = [];

function fmt(n){ return COIN + n.toFixed(2); }
function fmtBs(n){ return BS_SYMBOL + " " + n.toFixed(2); }
function fmtComma(n){ return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","); }

// ===== Flotante Hero =====
function openFlotante() {
  if (!FLOTANTE_PID) return;
  if (FLOTANTE_EXTRAS.length > 0) {
    openExtrasModal(FLOTANTE_PID, FLOTANTE_NAME, JSON.stringify(FLOTANTE_EXTRAS));
  } else {
    addToCart(FLOTANTE_PID, FLOTANTE_NAME, "[]");
  }
}

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

let sedeModalReturnToCheckout = false;

function openSedeModal() {
  if ($("#modal-checkout").hasClass("show")) {
    sedeModalReturnToCheckout = true;
    $("#modal-checkout").modal("hide");
  } else {
    sedeModalReturnToCheckout = false;
  }
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
  if (typeof showStoreClosedAlert === "function" && showStoreClosedAlert()) { return; }
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
  if (typeof showStoreClosedAlert === "function" && showStoreClosedAlert()) { return; }
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

function deliveryPriceFor(sede, zoneId) {
  if (!zoneId || zoneId === "0") return 0;
  const zoneOpt = $("#order_zone option[value='" + zoneId + "']");
  const zoneDefault = zoneOpt.length ? (parseFloat(zoneOpt.data("price")) || 0) : 0;
  if (sede && sede.delivery && sede.delivery[zoneId] !== undefined) {
    return parseFloat(sede.delivery[zoneId]) || 0;
  }
  return zoneDefault;
}

function updateCheckoutUI() {
  const isPickup = $("#order_pickup").is(":checked");
  const zoneSel = $("#order_zone").val();
  const delivery = !isPickup && zoneSel !== "0";
  const deliveryPrice = delivery ? deliveryPriceFor(getSelectedSede(), zoneSel) : 0;
  const t = computeTotals(getCartItems(), delivery, deliveryPrice);
  const paySel = $(".payment-method:checked");
  const isPM = paySel.data("pm") == 1;

  $("#ck_subtotal").text(fmt(t.subtotal));
  $("#ck_delivery").text(delivery ? fmt(t.delivery) : "Comer aquí / Recoger");
  $("#ck_total").text(fmt(t.total));
  $("#ck_total_bs").text(bcvRate > 0 ? fmtBs(t.total * bcvRate) : "Bs a confirmar");

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
  // Hero "PEDIR AHORA" smooth scroll
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
      Swal.fire({ icon: "warning", title: "Elige una sede", text: "Selecciona la sede que te queda más cerca para continuar.", confirmButtonColor: "#b87e38" });
      return;
    }
    localStorage.setItem("blissfull_sede_id", sel.data("id"));
    setSedeUI();
    $("#modal-sede").modal("hide");
    updateCheckoutUI();
    if (sedeModalReturnToCheckout) {
      sedeModalReturnToCheckout = false;
      setTimeout(function() { $("#modal-checkout").modal("show"); }, 350);
    }
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

  // Refresh totals when checkout opens
  $("#modal-checkout").on("shown.bs.modal", function() { updateCheckoutUI(); });

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
    if (typeof showStoreClosedAlert === "function" && showStoreClosedAlert()) { return; }
    const sede = getSelectedSede();
    if (!sede) {
      Swal.fire({ icon: "warning", title: "Elige tu sede", text: "Primero selecciona la sede que te queda más cerca, así tu pedido llega al WhatsApp correcto.", confirmButtonColor: "#b87e38" }).then(function(){ openSedeModal(); });
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
      Swal.fire({ icon: "warning", title: "Faltan datos", text: "Por favor completa tu nombre y teléfono.", confirmButtonColor: "#b87e38" });
      return;
    }
    if (isPickup) { address = "Recoger en sucursal"; }
    else if (address === "") {
      Swal.fire({ icon: "warning", title: "Faltan datos", text: "Por favor escribe tu dirección de entrega.", confirmButtonColor: "#b87e38" });
      return;
    }
    if (!isPickup && zoneSel === "0") {
      Swal.fire({ icon: "warning", title: "Zona de entrega", text: "Por favor selecciona tu zona de entrega (o marca que pasarás a recoger).", confirmButtonColor: "#b87e38" });
      return;
    }

    const delivery = !isPickup;
    const zoneOpt = delivery ? $("#order_zone option:selected") : null;
    const deliveryPrice = delivery ? deliveryPriceFor(sede, zoneSel) : 0;
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
        confirmButtonColor: "#e0a96d"
      }).then(function(){ location.reload(); });
    }).fail(function() {
      btn.prop("disabled", false).html('CONFIRMAR Y PEDIR POR WHATSAPP <i class="bi bi-whatsapp ms-2"></i>');
      Swal.fire({ icon: "error", title: "Error", text: "Ocurrió un error al registrar tu pedido. Intenta de nuevo.", confirmButtonColor: "#ff2a2a" });
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
  border: 1px solid rgba(255, 255, 255, 0.09);
  background: rgba(255, 255, 255, 0.04);
  padding: 0.55rem 1.3rem;
  border-radius: 2rem;
  font-weight: 700;
  color: #ffffff;
  transition: 0.2s;
}
.btn-category-ajax:hover { color: #ffffff; border-color: rgba(255, 183, 3, 0.55); }
.btn-category-ajax.active {
  background: linear-gradient(135deg, #e0a96d, #b87e38) !important;
  color: #000000 !important;
  border-color: transparent !important;
  box-shadow: 0 8px 20px rgba(255, 159, 28, 0.35);
}
.sede-option { cursor: pointer; }
.sede-card { transition: 0.2s; background: #0a0a0a; }
.sede-option:hover .sede-card { border-color: rgba(255, 183, 3, 0.45); }
.sede-check { color: rgba(255, 183, 3, 0.4); }
.sede-option.selected .sede-card { border-color: #e0a96d; background: rgba(255, 183, 3, 0.08); box-shadow: 0 8px 22px rgba(255, 159, 28, 0.18); }
.sede-option.selected .sede-check { color: #e0a96d; }
</style>