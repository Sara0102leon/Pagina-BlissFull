<?php
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$total = 0;
?>
<div class="card border-0 shadow-sm overflow-hidden mb-4">
  <div class="card-header bg-primary text-white py-3">
    <h3 class="card-title fw-bold mb-0"><i class="bi bi-cart3 me-2"></i> Mi Carrito</h3>
  </div>
  <div class="card-body p-0">
    <?php if(isset($_SESSION["cart"]) && count($_SESSION["cart"])>0):?>
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
      <table class="table table-vcenter table-mobile-md card-table">
        <tbody>
          <?php 
          $items_text = "";
          foreach($_SESSION["cart"] as $s):
          $p = ProductData::getById($s["product_id"]); 
          $subtotal = $p->price*$s["q"];
          $total += $subtotal;
          $items_text .= "- ".$s["q"]." x ".htmlspecialchars($p->name)." (".$coin_symbol.number_format($p->price,2).") = ".$coin_symbol.number_format($subtotal,2)."%0A";
          ?>
          <tr>
            <td class="px-3 py-2">
              <div class="fw-bold small"><?php echo htmlspecialchars($p->name); ?></div>
              <div class="text-muted extra-small"><?php echo $s["q"]; ?> x <?php echo $coin_symbol.number_format($p->price,2); ?></div>
            </td>
            <td class="text-end px-3 py-2">
               <div class="fw-bold"><?php echo $coin_symbol.number_format($subtotal,2); ?></div>
               <button type="button" class="btn btn-ghost-danger btn-icon btn-sm border-0" onclick="removeFromCart(<?php echo $p->id; ?>)">
                 <i class="bi bi-x-circle"></i>
               </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Hidden data for WhatsApp -->
    <input type="hidden" id="whatsapp_items_text" value="<?php echo $items_text; ?>">
    <input type="hidden" id="whatsapp_total_text" value="<?php echo $coin_symbol.number_format($total,2); ?>">
    <input type="hidden" id="cart_total_count" value="<?php echo count($_SESSION["cart"]); ?>">

    <div class="p-3 bg-light border-top">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="h4 mb-0 fw-bold">TOTAL:</span>
        <span class="h3 mb-0 fw-bold text-primary"><?php echo $coin_symbol.number_format($total,2); ?></span>
      </div>
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

<style>
.extra-small { font-size: 0.75rem; }
</style>
