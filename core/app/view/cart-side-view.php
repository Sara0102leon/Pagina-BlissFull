<?php
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$bs_symbol = "Bs";
$bcv_rate = 0;
$bcv_row = ConfigurationData::getByPreffix("bcv_rate");
if($bcv_row && $bcv_row->val){ $bcv_rate = floatval($bcv_row->val); }
$total = 0;
$total_count = 0;
$items_json = array();
if(isset($_SESSION["cart"])){
  foreach($_SESSION["cart"] as $s){ $total_count += $s["q"]; }
}
?>
<div class="card border-0 shadow-sm overflow-hidden mb-4 cart-side-card">
  <div class="card-header bg-primary text-white py-3">
    <h3 class="card-title fw-bold mb-0"><i class="bi bi-cart3 me-2"></i> Mi Carrito</h3>
  </div>
  <div class="card-body p-0">
    <?php if(isset($_SESSION["cart"]) && count($_SESSION["cart"])>0):?>
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
      <table class="table table-vcenter table-mobile-md card-table">
        <tbody>
          <?php 
          foreach($_SESSION["cart"] as $s):
          $p = ProductData::getById($s["product_id"]); 
          $extras_sum = 0;
          $extras_txt = array();
          if(isset($s["extras"]) && count($s["extras"])>0){
            foreach($s["extras"] as $e){ $extras_sum += floatval($e["price"]); if(floatval($e["price"])>0 || (isset($e["div"]) && intval($e["div"])==1)){ $extras_txt[] = $e["name"]; } }
          }
          $unit = ProductData::getEffectivePrice($p) + $extras_sum;
          $subtotal = $unit*$s["q"];
          $total += $subtotal;
          $items_json[] = array(
            "id"=>intval($p->id),
            "name"=>$p->name,
            "q"=>intval($s["q"]),
            "price"=>ProductData::getEffectivePrice($p),
            "price_llevar"=>ProductData::offerActive($p) ? ProductData::getEffectivePrice($p) : floatval($p->price_llevar),
            "extras"=>isset($s["extras"])?$s["extras"]:array()
          );
          ?>
          <tr>
            <td class="px-3 py-2">
              <div class="fw-bold small"><?php echo htmlspecialchars($p->name); ?></div>
              <?php if(count($extras_txt)>0): ?>
              <div class="text-muted extra-small">+ <?php echo htmlspecialchars(implode(", ", $extras_txt)); ?></div>
              <?php endif; ?>
              <div class="text-muted extra-small"><?php echo $coin_symbol.number_format($unit,2,".",","); ?> c/u<?php if($bcv_rate>0): ?> ≈ <?php echo $bs_symbol.number_format($unit*$bcv_rate,2,".",","); ?><?php endif; ?></div>
            </td>
            <td class="px-3 py-2">
              <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-outline-secondary btn-icon btn-sm border-0 p-0" onclick="decCart('<?php echo $s["key"]; ?>', '<?php echo addslashes($p->name); ?>')" title="Restar uno">
                  <i class="bi bi-dash-circle"></i>
                </button>
                <span class="fw-bold small mx-1"><?php echo $s["q"]; ?></span>
                <button type="button" class="btn btn-outline-secondary btn-icon btn-sm border-0 p-0" onclick="incCart('<?php echo $s["key"]; ?>', '<?php echo addslashes($p->name); ?>')" title="Agregar uno más">
                  <i class="bi bi-plus-circle"></i>
                </button>
              </div>
            </td>
            <td class="text-end px-3 py-2">
               <div class="fw-bold"><?php echo $coin_symbol.number_format($subtotal,2,".",","); ?></div>
               <?php if($bcv_rate>0): ?><div class="text-muted extra-small"><?php echo $bs_symbol.number_format($subtotal*$bcv_rate,2,".",","); ?></div><?php endif; ?>
               <button type="button" class="btn btn-ghost-danger btn-icon btn-sm border-0" onclick="removeFromCart('<?php echo $s["key"]; ?>', '<?php echo addslashes($p->name); ?>')" title="Eliminar del carrito">
                 <i class="bi bi-x-circle"></i>
               </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <div class="p-3 bg-light border-top">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="h4 mb-0 fw-bold">TOTAL:</span>
        <span class="h3 mb-0 fw-bold text-primary"><?php echo $coin_symbol.number_format($total,2,".",","); ?></span>
      </div>
      <?php if($bcv_rate>0): ?>
      <div class="d-flex justify-content-end mb-3">
        <span class="text-muted fw-bold">≈ <?php echo $bs_symbol.number_format($total*$bcv_rate,2,".",","); ?></span>
      </div>
      <?php endif; ?>
      <button type="button" class="btn btn-primary w-100 py-2 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#modal-checkout">
        CONFIRMAR Y PEDIR
      </button>
      <div class="text-center mt-2">
         <button type="button" class="btn btn-link link-danger btn-sm p-0" onclick="clearCart()">Vaciar Carrito</button>
      </div>
    </div>

    <?php else: ?>
    <div class="text-center py-5">
      <i class="bi bi-bag-plus h1 text-muted mb-3 d-block"></i>
      <p class="text-muted px-4">Tu carrito está vacío. ¡Presiona el <i class="bi bi-plus-lg text-primary"></i> en los platillos para agregar!</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Hidden data for WhatsApp/UI (always present so JS can reset values) -->
<input type="hidden" id="cart_items_json" value='<?php echo json_encode($items_json); ?>'>
<input type="hidden" id="whatsapp_total_text" value="<?php echo $coin_symbol.number_format($total,2,".",","); ?>">
<input type="hidden" id="whatsapp_total_bs_text" value="<?php echo $bs_symbol." ".number_format($bcv_rate>0?$total*$bcv_rate:0,2,".",","); ?>">
<input type="hidden" id="cart_total_count" value="<?php echo $total_count; ?>">

<style>
.extra-small { font-size: 0.75rem; }
</style>
