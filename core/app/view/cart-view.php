<?php
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$iva = ConfigurationData::getByPreffix("general_iva")?ConfigurationData::getByPreffix("general_iva")->val:0;
$ivatxt = ConfigurationData::getByPreffix("general_iva_txt")?ConfigurationData::getByPreffix("general_iva_txt")->val:"IVA";
$whatsapp_number = ConfigurationData::getByPreffix("general_whatsapp")?ConfigurationData::getByPreffix("general_whatsapp")->val:"5555555555";

if(isset($_GET["opt"]) && $_GET["opt"]=="all"):
$total = 0;
?>
<div class="page-body">
  <div class="container-xl">
    <div class="row g-4 justify-content-center">
      <div class="col-md-10">
        <div class="d-flex align-items-center mb-4">
           <h2 class="h1 mb-0 fw-bold"><i class="bi bi-bag-heart text-primary me-2"></i> Mi Orden</h2>
           <div class="ms-auto text-muted small">Lista de tus platillos favoritos</div>
        </div>

        <?php if(isset($_SESSION["cart"]) && count($_SESSION["cart"])>0):?>
        <div class="card border-0 shadow-sm overflow-hidden mb-4">
          <div class="table-responsive">
            <table class="table table-vcenter table-mobile-md card-table">
              <thead>
                <tr class="bg-light">
                  <th class="py-3 px-4">Platillo</th>
                  <th class="text-center py-3">Cantidad</th>
                  <th class="text-end py-3">Precio</th>
                  <th class="text-end py-3">Total</th>
                  <th class="w-1 py-3 px-4"></th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $items_text = "";
                foreach($_SESSION["cart"] as $s):
                $p = ProductData::getById($s["product_id"]); 
                $img = "admin/storage/products/".$p->image;
                if($p->image=="" || !file_exists($img)){ $img=$img_default; }
                $subtotal = $p->price*$s["q"];
                $total += $subtotal;
                $items_text .= "- ".$s["q"]." x ".htmlspecialchars($p->name)." (".$coin_symbol.number_format($p->price,2).") = ".$coin_symbol.number_format($subtotal,2)."%0A";
                ?>
                <tr>
                  <td class="px-4">
                    <div class="d-flex py-2 align-items-center">
                       <span class="avatar avatar-md me-3 rounded shadow-sm" style="background-image: url(<?php echo $img; ?>); background-size: cover;"></span>
                       <div class="flex-fill">
                         <div class="fw-bold h4 mb-0"><?php echo htmlspecialchars($p->name); ?></div>
                       </div>
                    </div>
                  </td>
                  <td class="text-center" data-label="Cantidad">
                    <form action="./?action=cart&opt=edit" method="post" class="d-inline-block">
                      <input type="hidden" name="product_id" value="<?php echo $p->id; ?>">
                      <div class="input-group input-group-sm rounded-pill border overflow-hidden" style="width: 100px;">
                        <input type="number" name="q" value="<?php echo $s["q"]; ?>" class="form-control border-0 text-center fw-bold" onchange="this.form.submit()" min="1">
                      </div>
                    </form>
                  </td>
                  <td class="text-end text-muted" data-label="Precio"><?php echo $coin_symbol." ".number_format($p->price,2); ?></td>
                  <td class="text-end fw-bold text-dark" data-label="Subtotal"><?php echo $coin_symbol." ".number_format($subtotal,2); ?></td>
                  <td class="px-4 text-end">
                    <a href="./?action=cart&opt=del&product_id=<?php echo $p->id; ?>" class="btn btn-ghost-danger btn-icon rounded-circle" title="Eliminar"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-7">
             <div class="card border-0 shadow-sm p-4 h-100">
                <h4 class="fw-bold mb-4 text-primary"><i class="bi bi-person-lines-fill me-2"></i> Datos de Entrega</h4>
                <div class="row g-3 mb-3">
                   <div class="col-md-7">
                      <label class="form-label fw-bold">Nombre Completo</label>
                      <input type="text" id="order_name" class="form-control form-control-lg border-primary-lt" placeholder="¿A nombre de quién?" required>
                   </div>
                   <div class="col-md-5">
                      <label class="form-label fw-bold">Teléfono / WhatsApp</label>
                      <input type="tel" id="order_phone" class="form-control form-control-lg border-primary-lt" placeholder="Tu celular" required>
                   </div>
                </div>
                <div class="mb-0">
                   <label class="form-label fw-bold">Dirección de Entrega</label>
                   <textarea id="order_address" class="form-control form-control-lg border-primary-lt" rows="3" placeholder="Calle, número, colonia..." required></textarea>
                </div>
                <p class="small text-muted mt-3 mb-0">Al confirmar, registraremos tu pedido y se abrirá WhatsApp.</p>
             </div>
          </div>
          <div class="col-md-5">
            <div class="card shadow-sm border-0 bg-white">
              <div class="card-body p-4">
                <h3 class="fw-bold mb-4">Resumen de Orden</h3>
                <div class="d-flex mb-3 justify-content-between">
                  <span class="text-muted">Total Productos</span>
                  <span class="fw-bold text-dark h4 mb-0"><?php echo $coin_symbol." ".number_format($total,2); ?></span>
                </div>
                <hr class="my-3">
                <div class="d-flex mb-4 justify-content-between align-items-center">
                  <span class="h2 fw-bold mb-0">TOTAL A PAGAR</span>
                  <span class="h1 fw-bold text-primary mb-0"><?php echo $coin_symbol." ".number_format($total,2); ?></span>
                </div>

                <div class="d-grid">
                  <button id="btn_whatsapp" class="btn btn-success btn-lg py-3 rounded-pill shadow-sm fw-bold border-0" style="background: #25D366;">
                    <i class="bi bi-whatsapp me-2 h3 mb-0"></i> 
                    <span class="h3 mb-0">CONFIRMAR Y PEDIR</span>
                  </button>
                </div>
                
                <div class="mt-4 text-center">
                   <a href="./?action=cart&opt=clear" class="btn btn-link link-danger btn-sm"><i class="bi bi-trash3 me-1"></i> Cancelar y Limpiar Orden</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <script>
        $(document).ready(function() {
          $("#btn_whatsapp").click(function() {
            const name = $("#order_name").val().trim();
            const phone = $("#order_phone").val().trim();
            const address = $("#order_address").val().trim();

            if (name === "" || address === "" || phone === "") {
              alert("Por favor, completa tus datos para continuar.");
              return;
            }

            const btn = $(this);
            btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span> Procesando...');

            // Step 1: Register in database via AJAX
            $.post("./?action=cart&opt=buy", {
              name: name,
              phone: phone,
              address: address
            }, function(response) {
              // Step 2: Open WhatsApp
              const whatsapp_num = "<?php echo $whatsapp_number; ?>";
              const coin = "<?php echo $coin_symbol; ?>";
              const total = "<?php echo number_format($total, 2); ?>";
              
              let message = "*🍽️ NUEVA ORDEN REGISTRADA DE TACO MENU*%0A%0A";
              message += "*👤 Cliente:* " + name + "%0A";
              message += "*📞 Teléfono:* " + phone + "%0A";
              message += "*📍 Dirección:* " + address + "%0A%0A";
              message += "*🌮 Productos:*%0A";
              message += "<?php echo $items_text; ?>";
              message += "%0A*------------------------------*%0A";
              message += "*💰 TOTAL: " + coin + total + "*%0A";
              message += "*------------------------------*%0A%0A";
              message += "_Enviado desde el Menú Digital_";

              const url = "https://api.whatsapp.com/send?phone=" + whatsapp_num + "&text=" + message;
              window.open(url, '_blank');
              
              // Clear cart and redirect to success or home
              window.location.href = "./?view=products&opt=all&msg=order_success";
            });
          });
        });
        </script>

        <?php else: ?>
        <div class="card border-0 shadow-sm py-5">
          <div class="card-body text-center py-5">
            <div class="mb-4">
              <span class="avatar avatar-xl bg-light text-muted rounded-circle">
                 <i class="bi bi-bag-plus h1 mb-0"></i>
              </span>
            </div>
            <h2 class="fw-bold">No has seleccionado nada aún</h2>
            <p class="text-muted fs-4">Tu orden está vacía. ¡Explora nuestros deliciosos platillos!</p>
            <a href="./?view=products&opt=all" class="btn btn-primary btn-lg rounded-pill px-5 mt-3 shadow-sm">
               <i class="bi bi-grid-fill me-2"></i> Ver Menú
            </a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
