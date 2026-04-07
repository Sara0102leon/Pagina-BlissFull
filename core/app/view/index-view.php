<?php 
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$img_default = ConfigurationData::getByPreffix("general_img_default")?ConfigurationData::getByPreffix("general_img_default")->val:"assets/img/default.png";
$whatsapp_number = ConfigurationData::getByPreffix("general_whatsapp")?ConfigurationData::getByPreffix("general_whatsapp")->val:"+5215574506232";
$slides = SlideData::getPublics();
$categories = CategoryData::getPublics();
?>

<!-- Modal Checkout -->
<div class="modal modal-blur fade" id="modal-checkout" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">🛒 Estás a un paso de tus tacos</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
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
         <div class="mb-0" id="address_container">
            <label class="form-label fw-bold">Dirección de Entrega</label>
            <textarea id="order_address" class="form-control" rows="3" placeholder="Calle, número, cruzamientos..."></textarea>
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

<div class="page-body">
  <div class="container-xl">
    <div class="row g-4 mt-2">
      
      <!-- Menu Section (Full Width) -->
      <div class="col-md-12">
        
        <!-- Slider Section -->
        <?php if(count($slides)>0): ?>
        <div id="carousel-hero" class="carousel slide shadow-sm rounded-4 overflow-hidden mb-5" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php foreach($slides as $idx => $s): ?>
            <div class="carousel-item <?php echo $idx==0?'active':''; ?>">
              <img src="admin/storage/slides/<?php echo $s->image; ?>" class="d-block w-100" style="height: 200px; object-fit: cover;">
              <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded-3 p-2">
                <h2 class="fw-bold h2 mb-0"><?php echo htmlspecialchars($s->title); ?></h2>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Category Dynamic Navigation -->
        <div class="mb-4 overflow-auto scroll-hide pb-2">
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-category-ajax active" data-cat="">🔥 Destacados</button>
            <?php foreach($categories as $cat): ?>
            <button type="button" class="btn-category-ajax text-nowrap" data-cat="<?php echo $cat->id; ?>">
              <?php echo htmlspecialchars($cat->name); ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
           <div>
              <h2 class="h1 mb-0 fw-bold" id="grid-title">🔥 El Menú que Amas</h2>
              <p class="text-muted mb-0">Selecciona tus favoritos y arma tu orden</p>
           </div>
           <!-- Dynamic Search Bar -->
           <div class="search-container">
              <div class="input-icon">
                <span class="input-icon-addon"><i class="bi bi-search"></i></span>
                <input type="text" id="product_search" class="form-control form-control-rounded shadow-sm" placeholder="Buscar platillo..." style="min-width: 280px;">
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
let currentCatId = "";
let currentSearch = "";

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

function addToCart(pid) {
  $.post("./?action=cart&opt=add&ajax=1", { product_id: pid }, function(data) {
     $("#cart-container").html(data);
     $("#offcanvas-cart-container").html(data);
     updateUI();
  });
}

function removeFromCart(pid) {
  $.post("./?action=cart&opt=del&ajax=1", { product_id: pid }, function(data) {
     $("#cart-container").html(data);
     $("#offcanvas-cart-container").html(data);
     updateUI();
  });
}

function clearCart() {
  $.post("./?action=cart&opt=clear&ajax=1", {}, function(data) {
     $("#cart-container").html(data);
     $("#offcanvas-cart-container").html(data);
     updateUI();
  });
}

$(document).ready(function() {
  // Category AJAX Toggle
  $(".btn-category-ajax").click(function() {
    $(".btn-category-ajax").removeClass("active");
    $(this).addClass("active");
    currentCatId = $(this).data("cat");
    const name = $(this).text().trim();
    $("#grid-title").html(currentCatId === "" ? "🔥 Destacados" : "📁 " + name);
    updateGrid();
  });

  // Dynamic Search
  let searchTimer;
  $("#product_search").on("keyup", function() {
    clearTimeout(searchTimer);
    currentSearch = $(this).val().trim();
    
    searchTimer = setTimeout(function() {
      if(currentSearch !== "") {
        $("#grid-title").html('🔍 Buscando: ' + currentSearch);
      } else {
        const activeName = $(".btn-category-ajax.active").text().trim();
        $("#grid-title").html(currentCatId === "" ? "🔥 Destacados" : "📁 " + activeName);
      }
      updateGrid();
    }, 400); 
  });

  // Pickup Toggle
  $("#order_pickup").change(function() {
    if($(this).is(":checked")) {
      $("#address_container").fadeOut();
    } else {
      $("#address_container").fadeIn();
    }
  });

  // Confirm Order
  $("#btn_confirm_order").click(function() {
    const name = $("#order_name").val().trim();
    const phone = $("#order_phone").val().trim();
    let address = $("#order_address").val().trim();
    const isPickup = $("#order_pickup").is(":checked");

    if (isPickup) { address = "Recoger en sucursal"; }

    if (name === "" || phone === "" || address === "") {
      alert("Por favor completa los datos para recibir tu orden.");
      return;
    }

    const btn = $(this);
    btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span> Registrando...');

    $.post("./?action=cart&opt=buy", {
      name: name,
      phone: phone,
      address: address
    }, function(res) {
       const itemsText = $("#whatsapp_items_text").val();
       const totalText = $("#whatsapp_total_text").val();
       clearCart();
       
       const whatsappNum = "<?php echo $whatsapp_number; ?>";
       let msg = "*🍽️ NUEVA ORDEN REGISTRADA - TACO MENU*%0A%0A";
       msg += "*👤 Cliente:* " + name + "%0A";
       msg += "*📞 Teléfono:* " + phone + "%0A";
       msg += "*📍 Entrega:* " + address + "%0A%0A";
       msg += "*🌮 Productos:*%0A" + itemsText;
       msg += "%0A*------------------------------*%0A";
       msg += "*💰 TOTAL: " + totalText + "*%0A";
       msg += "*------------------------------*%0A%0A";
       msg += "_¡Súper! Tu pedido ha sido recibido y registrado._";

       window.open("https://api.whatsapp.com/send?phone=" + whatsappNum + "&text=" + msg, '_blank');
       $("#modal-checkout").modal("hide");
       alert("¡Excelente! Tu pedido ha sido enviado por WhatsApp.");
       location.reload();
    });
  });
});
</script>

<style>
.product-card { transition: 0.3s; cursor: default; }
.product-card:hover { transform: translateY(-5px); }
.text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.btn-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
.scroll-hide::-webkit-scrollbar { display: none; }
.scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
.btn-category-ajax {
  border: 1px solid #dee2e6;
  background: white;
  padding: 0.5rem 1.25rem;
  border-radius: 2rem;
  font-weight: 600;
  color: #495057;
  transition: 0.2s;
}
.btn-category-ajax.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
  box-shadow: 0 4px 10px rgba(230, 126, 34, 0.3);
}
</style>