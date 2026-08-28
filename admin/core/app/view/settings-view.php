<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}
$user= UserData::getById($_SESSION["user_id"]);
if($user==null){ Core::redir("./");}
?>
<?php if(isset($_GET["opt"]) && $_GET["opt"]=="all"):?>
<?php $settings = ConfigurationData::getAll(); ?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Ajustes Generales</h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <a href="./?view=settings&opt=payment" class="btn btn-default">Metodos de Pago</a>
          <a href="./?view=settings&opt=sedes" class="btn btn-default">Sedes</a>
          <a href="./?view=settings&opt=horarios" class="btn btn-default">Horarios</a>
          <a href="./?view=settings&opt=units" class="btn btn-default">Unidades</a>
          <a href="./?view=settings&opt=ingredients" class="btn btn-default">Ingredientes</a>
          <a href="./?view=settings&opt=password" class="btn btn-default">Cambiar Contraseña</a>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <form method="post" action="./?action=settings&opt=update">
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>Parametro</th>
                <th>Valor</th>
              </tr>
            </thead>
            <tbody>
            <?php if(count($settings)>0):?>
            <?php foreach($settings as $cat):?>
              <?php if(substr($cat->name, 0,8)=="general_" || $cat->name=="bcv_rate"):?>
              <tr>
                <td class="w-50"><?php echo $cat->label; ?></td>
                <td>
                  <input type="text" name="<?php echo $cat->name; ?>" class="form-control" value="<?php echo htmlspecialchars($cat->val);?>">
                </td>
              </tr>
              <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card-footer text-end">
          <button type="submit" class="btn btn-success">Actualizar Ajustes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="payment"):?>
<?php 
$paymethods = PaymethodData::getAll(); 
$settings = ConfigurationData::getAll();
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Metodos de Pago</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card mb-3">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Metodos de Pago</h3>
      </div>
      <div class="table-responsive">
        <table class="table card-table table-vcenter">
          <tbody>
          <?php foreach($paymethods as $pay):?>
            <tr>
              <td><?php echo $pay->name;?></td>
              <td class="text-end">
                <a href="./?action=settings&opt=switchpm&id=<?php echo $pay->id; ?>" class="btn btn-sm <?php echo $pay->is_active?'btn-warning':'btn-primary'; ?>">
                  <?php echo $pay->is_active?'Desactivar':'Activar'; ?>
                </a>
              </td>
            </tr>
          <?php endforeach;?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if(PaymethodData::getByName("bank") && PaymethodData::getByName("bank")->is_active):?>
    <div class="card">
      <div class="card-status-top bg-info"></div>
      <div class="card-header">
        <h3 class="card-title">Deposito Bancario</h3>
      </div>
      <form method="post" action="./?action=settings&opt=updatepm">
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <tbody>
            <?php foreach($settings as $cat):?>
              <?php if(substr($cat->name, 0,5)=="bank_"):?>
              <tr>
                <td class="w-50"><?php echo $cat->label; ?></td>
                <td>
                  <input type="text" name="<?php echo $cat->name; ?>" class="form-control" value="<?php echo htmlspecialchars($cat->val);?>">
                </td>
              </tr>
              <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="card-footer text-end">
          <button type="submit" class="btn btn-success">Actualizar Datos</button>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Datos de Pago (Pago Móvil, Zelle, Binance)</h3>
        <p class="text-muted small mb-0 ms-3">Estos datos se envían al cliente junto al pedido por WhatsApp.</p>
      </div>
      <form method="post" action="./?action=settings&opt=updatepm">
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <tbody>
            <?php foreach($settings as $cat):?>
              <?php if(substr($cat->name, 0,10)=="pago_movil" || substr($cat->name, 0,5)=="zelle" || substr($cat->name, 0,7)=="binance"):?>
              <tr>
                <td class="w-50"><?php echo $cat->label; ?></td>
                <td>
                  <input type="text" name="<?php echo $cat->name; ?>" class="form-control" value="<?php echo htmlspecialchars($cat->val);?>">
                </td>
              </tr>
              <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="card-footer text-end">
          <button type="submit" class="btn btn-success">Actualizar Datos</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="password"):?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Cambiar Contraseña</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-info"></div>
      <div class="card-body">
        <form method="post" action="./?action=settings&opt=changepass">
          <div class="mb-3">
            <label class="form-label">Contraseña Actual</label>
            <input type="password" name="password" class="form-control" required placeholder="Contraseña Actual">
          </div>
          <div class="mb-3">
            <label class="form-label">Nueva Contraseña</label>
            <input type="password" name="newpassword" class="form-control" required placeholder="Nueva Contraseña">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password" name="confirmnewpassword" class="form-control" required placeholder="Confirmar Nueva Contraseña">
          </div>
          <div class="form-footer">
            <button type="submit" class="btn btn-success w-100">Cambiar Contraseña</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="sedes"):?>
<?php $sedes = SedeData::getAll(); ?>
<?php $zones = DeliveryZoneData::getAll(); ?>
<?php $tab = isset($_GET["tab"]) ? $_GET["tab"] : "sedes"; ?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Sedes</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
      <li class="nav-item">
        <a class="nav-link <?php echo $tab==='sedes'?'active':''; ?>" href="./?view=settings&opt=sedes&tab=sedes" role="tab">
          <i class="bi bi-shop me-1"></i> Sedes
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $tab==='zonas'?'active':''; ?>" href="./?view=settings&opt=sedes&tab=zonas" role="tab">
          <i class="bi bi-geo-alt me-1"></i> Zonas de Delivery
        </a>
      </li>
    </ul>

    <?php if($tab === "sedes"): ?>
    <!-- TAB SEDES -->
    <div class="card mb-3">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Agregar Sede</h3>
      </div>
      <form method="post" action="./?action=settings&opt=addsede" enctype="multipart/form-data">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-4">
              <input type="text" name="name" class="form-control" placeholder="Nombre de la sede" required>
            </div>
            <div class="col-md-4">
              <input type="text" name="address" class="form-control" placeholder="Dirección / Referencia">
            </div>
            <div class="col-md-3">
              <label class="form-label small text-muted mb-0">WhatsApp de la sede</label>
              <input type="text" name="phone" class="form-control" placeholder="WhatsApp (+58412...)" required>
            </div>
            <div class="col-md-1">
              <button type="submit" class="btn btn-success w-100">Agregar</button>
            </div>
            <div class="col-md-4">
              <input type="text" name="maps" class="form-control" placeholder="Google Maps: dirección, coordenadas o enlace completo (https://www.google.com/maps/place/...)">
            </div>
            <div class="col-md-4">
              <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="col-md-4 d-flex align-items-center text-muted small">
              Foto de la sede (se ve al girar la tarjeta en la página).
            </div>
          </div>
          <p class="text-muted small mb-0 mt-2">El WhatsApp de la sede se usa en el envío del pedido. Ej: +584121234567</p>
        </div>
      </form>
    </div>
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Sedes Registradas</h3>
      </div>
      <div class="card-body">
        <?php if(count($sedes)>0):?>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>Sede</th>
                <th>WhatsApp</th>
                <th>Activa</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($sedes as $sd):?>
              <tr>
                <td>
                  <form method="post" action="./?action=settings&opt=updsede" class="d-flex gap-2 align-items-center flex-wrap" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $sd->id; ?>">
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($sd->name); ?>" required style="min-width:180px;">
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($sd->address); ?>" placeholder="Dirección" style="min-width:180px;">
                    <?php if($sd->image!="" && file_exists("storage/sedes/".$sd->image)): ?>
                    <img src="storage/sedes/<?php echo $sd->image; ?>" alt="foto sede" style="width:52px; height:52px; object-fit:cover; border-radius:10px;" class="border">
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*" style="max-width:220px;">
                    <input type="text" name="maps" class="form-control" value="<?php echo htmlspecialchars($sd->maps); ?>" placeholder="Google Maps (query de dirección)" style="min-width:220px;">
                </td>
                <td>
                  <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($sd->phone); ?>" required style="min-width:140px;">
                </td>
                <td>
                  <label class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="is_active" <?php if($sd->is_active){ echo "checked";} ?>>
                  </label>
                </td>
                <td class="text-end text-nowrap">
                  <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-check-lg"></i> Guardar</button>
                  </form>
                  <a href="./?action=settings&opt=delsede&id=<?php echo $sd->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar esta sede?');"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endforeach;?>
            </tbody>
          </table>
        </div>
        <?php else:?>
          <p class="alert alert-warning mb-0">No hay sedes registradas.</p>
        <?php endif;?>
      </div>
    </div>
    <?php else: ?>
    <!-- TAB ZONAS -->
    <div class="card mb-3">
      <div class="card-status-top bg-warning"></div>
      <div class="card-header">
        <h3 class="card-title">Agregar Zona</h3>
      </div>
      <form method="post" action="./?action=settings&opt=addzone" class="card-body row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Nombre de la zona</label>
          <input type="text" name="name" class="form-control" placeholder="Ej: Zona Centro, Zona Norte" required>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-warning w-100"><i class="bi bi-plus-lg"></i> Agregar zona</button>
        </div>
        <div class="col-12">
          <p class="text-muted small mb-0">La zona se crea con precio de $1.00; luego configúralo por sede en la tabla de abajo.</p>
        </div>
      </form>
    </div>
    <div class="card">
      <div class="card-status-top bg-info"></div>
      <div class="card-header">
        <h3 class="card-title">Precios por Sede y Zona</h3>
      </div>
      <div class="card-body">
        <?php if(count($sedes)==0):?>
          <p class="alert alert-warning mb-0">Primero agrega una sede.</p>
        <?php elseif(count($zones)==0):?>
          <p class="alert alert-warning mb-0">No hay zonas de entrega registradas.</p>
        <?php else:?>
          <div class="table-responsive">
            <table class="table card-table table-vcenter">
              <thead>
                <tr>
                  <th style="min-width:180px;">Zona de Delivery</th>
                  <?php foreach($sedes as $sd): ?>
                  <th class="text-center" style="min-width:170px;">
                    <div class="fw-bold mb-1"><?php echo htmlspecialchars($sd->name); ?></div>
                    <button type="button" class="btn btn-warning btn-sm btn-guardar-sede" data-sede="<?php echo $sd->id; ?>"><i class="bi bi-check-lg"></i> Guardar</button>
                  </th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach($zones as $z): ?>
                <tr>
                  <td class="align-middle"><?php echo htmlspecialchars($z->name); ?></td>
                  <?php foreach($sedes as $sd): ?>
                  <?php $sp = SedeDeliveryZoneData::getPrice($sd->id, $z->id); $hasPrice = $sp !== null; $val = $hasPrice ? number_format(floatval($sp),2,".","") : "1.00"; ?>
                  <td class="text-center align-middle">
                    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                      <div class="form-check form-switch me-2 mb-0">
                        <input class="form-check-input sede-zone-toggle" type="checkbox" <?php echo $hasPrice ? "checked" : ""; ?> data-sede="<?php echo $sd->id; ?>" data-zone="<?php echo $z->id; ?>">
                        <label class="form-check-label small text-muted delivery-label"><?php echo $hasPrice ? "Con delivery" : "Sin delivery"; ?></label>
                      </div>
                      <input type="number" step="0.01" min="1" class="form-control form-control-sm text-center sede-price-input" value="<?php echo $val; ?>" style="max-width:90px;" <?php echo !$hasPrice ? "disabled" : ""; ?>>
                    </div>
                  </td>
                  <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif;?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<script>
$(function(){
  // Switch Con delivery / Sin delivery: cambia la etiqueta al instante
  $(document).on("change", ".sede-zone-toggle", function() {
    var $checkbox = $(this);
    var $container = $checkbox.closest(".d-flex");
    var $label = $container.find(".delivery-label");
    var $priceInput = $container.find(".sede-price-input");
    if ($checkbox.is(":checked")) {
      $label.text("Con delivery");
      $priceInput.prop("disabled", false);
      if ($priceInput.val() === "" || parseFloat($priceInput.val()) < 1) {
        $priceInput.val("1.00");
      }
    } else {
      $label.text("Sin delivery");
      $priceInput.prop("disabled", true);
    }
  });

  // Guardar por sede: solo los toggles de esa sede
  $(document).on("click", ".btn-guardar-sede", function() {
    var $btn = $(this);
    var sedeId = String($btn.data("sede"));
    $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');
    var promises = [];
    $(".sede-zone-toggle").each(function() {
      var $checkbox = $(this);
      if (String($checkbox.data("sede")) !== sedeId) { return; }
      var $container = $checkbox.closest(".d-flex");
      var $form = $("<form>", { method: "post", action: "./?action=settings&opt=updsedezone" });
      $form.append($("<input>", { type: "hidden", name: "enabled", value: $checkbox.is(":checked") ? "1" : "" }));
      $form.append($("<input>", { type: "hidden", name: "sede_id", value: sedeId }));
      $form.append($("<input>", { type: "hidden", name: "zone_id", value: $checkbox.data("zone") }));
      var $priceInput = $container.find(".sede-price-input");
      if ($checkbox.is(":checked")) {
        var price = parseFloat($priceInput.val()) || 1;
        $form.append($("<input>", { type: "hidden", name: "price", value: price }));
      }
      $("body").append($form);
      promises.push($.post($form.attr("action"), $form.serialize()));
      $form.remove();
    });
    Promise.all(promises).then(function() {
      Swal.fire({ icon: "success", title: "Guardado", text: "Precios de la sede guardados", timer: 1500, showConfirmButton: false });
      location.reload();
    }).catch(function() {
      Swal.fire({ icon: "error", title: "Error", text: "Hubo un error al guardar" });
      $btn.prop("disabled", false).html('<i class="bi bi-check-lg"></i> Guardar');
    });
  });
});
</script>
<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="horarios"):?>
<?php
$sedes_h = SedeData::getAll();
$dias = array(
  "lunes"=>"Lunes",
  "martes"=>"Martes",
  "miercoles"=>"Miércoles",
  "jueves"=>"Jueves",
  "viernes"=>"Viernes",
  "sabado"=>"Sábado",
  "domingo"=>"Domingo"
);
$active_sede = isset($_GET["sede"]) ? intval($_GET["sede"]) : (count($sedes_h)>0 ? intval($sedes_h[0]->id) : 0);
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Horarios por Sede</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <?php if(count($sedes_h)==0): ?>
      <div class="alert alert-warning">No hay sedes registradas. Primero crea una sede en la pestaña <b>Sedes</b>.</div>
    <?php else: ?>
    <ul class="nav nav-pills mb-3" role="tablist">
      <?php foreach($sedes_h as $sh): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo intval($sh->id)==$active_sede ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#horsede-<?php echo $sh->id; ?>" type="button" role="tab">
          <i class="bi bi-shop me-1"></i> <?php echo htmlspecialchars($sh->name); ?>
        </button>
      </li>
      <?php endforeach; ?>
    </ul>
    <div class="tab-content">
      <?php foreach($sedes_h as $sh):
      $map = SedeHorarioData::mapForSede($sh->id); ?>
      <div class="tab-pane fade <?php echo intval($sh->id)==$active_sede ? 'show active' : ''; ?>" id="horsede-<?php echo $sh->id; ?>" role="tabpanel">
        <div class="card">
          <div class="card-status-top bg-info"></div>
          <div class="card-header">
            <h3 class="card-title">Horario de atención — <?php echo htmlspecialchars($sh->name); ?></h3>
            <p class="text-muted small mb-0 ms-3">Horario por día de la semana. Deja vacío un día si esa sede no atiende ese día.</p>
          </div>
          <form method="post" action="./?action=settings&opt=updhorariosede">
            <input type="hidden" name="sede_id" value="<?php echo $sh->id; ?>">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-vcenter">
                  <thead>
                    <tr>
                      <th>Día</th>
                      <th class="w-25">Apertura</th>
                      <th class="w-25">Cierre</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($dias as $dk => $dl):
                    $ro = $map[$dk]["open"];
                    $rc = $map[$dk]["close"]; ?>
                    <tr>
                      <td class="fw-bold"><?php echo $dl; ?></td>
                      <td><input type="time" class="form-control" name="open[<?php echo $dk; ?>]" value="<?php echo htmlspecialchars($ro!==null?substr($ro,0,5):""); ?>"></td>
                      <td><input type="time" class="form-control" name="close[<?php echo $dk; ?>]" value="<?php echo htmlspecialchars($rc!==null?substr($rc,0,5):""); ?>"></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Guardar Horarios</button>
            </div>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="units"):?>
<?php $units = UnitData::getAll(); ?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Unidades</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card mb-3">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Agregar Unidad</h3>
      </div>
      <form method="post" action="./?action=settings&opt=addunit">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-8">
              <input type="text" name="name" class="form-control" placeholder="Nombre de la unidad" required>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-success w-100">Agregar</button>
            </div>
          </div>
          <p class="text-muted small mb-0 mt-2">Ej: Pizza Gigante, Pizza Familiar 40 cm, Pizza Pequeña 25 cm, Extra, Litro y medio...</p>
        </div>
      </form>
    </div>
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Unidades Registradas</h3>
      </div>
      <div class="card-body">
        <?php if(count($units)>0):?>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>Unidad</th>
                <th class="w-1"></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($units as $u):?>
              <tr>
                <td>
                  <form method="post" action="./?action=settings&opt=updunit" class="d-flex gap-2">
                    <input type="hidden" name="id" value="<?php echo $u->id; ?>">
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($u->name); ?>" required>
                    <button type="submit" class="btn btn-warning btn-sm text-nowrap"><i class="bi bi-check-lg"></i> Guardar</button>
                  </form>
                </td>
                <td class="text-end">
                  <a href="./?action=settings&opt=delunit&id=<?php echo $u->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar esta unidad?');"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endforeach;?>
            </tbody>
          </table>
        </div>
        <?php else:?>
          <p class="alert alert-warning mb-0">No hay unidades registradas.</p>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="ingredients" || isset($_GET["opt"]) && $_GET["opt"]=="extras"):?>
<?php
$extras_groups = ProductExtraData::getGroups();
$products = ProductData::getAll();
$active_ids = array();
foreach($products as $pr){ $active_ids[intval($pr->id)] = intval($pr->id); }
$groups_info = array();
foreach($extras_groups as $g){
  $rows = ProductExtraData::getByGroup($g->group_key);
  $global = ProductExtraData::groupHasGlobal($rows);
  $pids = ProductExtraData::productIdsFromGroup($rows);
  $pids = array_values(array_intersect($pids, $active_ids));
  $groups_info[$g->group_key] = array("g"=>$g,"global"=>$global,"pids"=>$pids);
}
$extra_products_json = array();
foreach($products as $pr){
  $img = "storage/products/".$pr->image;
  $extra_products_json[] = array("id"=>intval($pr->id),"name"=>$pr->name,"img"=>$pr->image!="" && file_exists($img)?$img:"");
}
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Ingredientes / Extras</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card mb-3">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Agregar Ingrediente</h3>
      </div>
      <div class="card-body">
        <form method="post" action="./?action=settings&opt=addextra" id="form-add-extra" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Nombre del ingrediente</label>
            <input type="text" name="name" class="form-control" placeholder="Ej: Jamón" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Precio</label>
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input type="text" name="price" class="form-control" placeholder="3.00" required>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Productos donde aplica</label>
            <button type="button" class="btn btn-outline-primary w-100 btn-pick-products" data-form="#form-add-extra">
              <i class="bi bi-check2-square me-1"></i>Seleccionar productos (<span class="picker-label">TODOS</span>)
            </button>
            <input type="hidden" name="products" value="">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">Agregar</button>
          </div>
          <div class="col-12">
            <label class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" name="is_ingredient" checked>
              <span class="form-check-label">Es ingrediente (el cliente lo elige: los que van gratis se configuran en cada producto)</span>
            </label>
          </div>
          <p class="text-muted small mb-0 mt-1 col-12">Ej: Jamón (3$), Extra de queso (5$). Marca con un check los productos que ofrecerán este ingrediente; los que queden sin marcar no podrán pedirlo.</p>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Ingredientes Registrados</h3>
      </div>
      <div class="card-body">
        <?php if(count($groups_info)>0):?>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>Ingrediente</th>
                <th>Precio</th>
                <th>Productos donde aplica</th>
                <th>Es ingrediente</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($groups_info as $gk => $info):?>
              <?php $g = $info["g"]; $label = $info["global"] ? "TODOS los productos" : (count($info["pids"])>0 ? count($info["pids"])." producto".(count($info["pids"])>1?"s":"") : "Ninguno"); $pids_csv = implode(",", $info["pids"]); ?>
              <tr>
                <td><input type="text" class="form-control" value="<?php echo htmlspecialchars($g->name); ?>" data-row-name="<?php echo htmlspecialchars($gk); ?>" required></td>
                <td>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="text" class="form-control" value="<?php echo $g->price; ?>" data-row-price="<?php echo htmlspecialchars($gk); ?>" required style="min-width:90px;">
                  </div>
                </td>
                <td>
                  <button type="button" class="btn btn-outline-primary btn-sm btn-pick-products" data-form="<?php echo htmlspecialchars($gk); ?>" data-global="<?php echo $info["global"] ? "1" : "0"; ?>" data-pids="<?php echo htmlspecialchars($pids_csv); ?>">
                    <i class="bi bi-check2-square me-1"></i><span class="picker-label"><?php echo $label; ?></span>
                  </button>
                </td>
                <td>
                  <label class="form-check form-switch mb-0" title="Marcado = el cliente lo elige como ingrediente (gratis hasta la cantidad configurada en el producto); sin marcar = extra de pago">
                    <input class="form-check-input row-is-ingredient" type="checkbox" data-form="<?php echo htmlspecialchars($gk); ?>" <?php if(intval($g->is_ingredient)==1){ echo "checked"; } ?>>
                  </label>
                </td>
                <td class="text-end text-nowrap">
                  <button type="button" class="btn btn-warning btn-sm btn-extra-save" data-form="<?php echo htmlspecialchars($gk); ?>"><i class="bi bi-check-lg"></i> Guardar</button>
                  <a href="./?action=settings&opt=delextra&group_key=<?php echo htmlspecialchars($gk); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar este ingrediente de todos los productos?');"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endforeach;?>
            </tbody>
          </table>
        </div>
        <?php else:?>
          <p class="alert alert-warning mb-0">No hay ingredientes registrados.</p>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>

<!-- Modal selector de productos -->
<div class="modal fade" id="productsPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-check2-square me-1"></i>Productos donde aplica el ingrediente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Marca los productos que ofrecerán este ingrediente. Sin marcar = el producto no puede pedirlo.</p>
        <div class="d-flex gap-2 mb-3">
          <button type="button" class="btn btn-sm btn-outline-primary" id="products-check-all"><i class="bi bi-check-all me-1"></i>Marcar todos</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="products-uncheck-all"><i class="bi bi-x-circle me-1"></i>Desmarcar todos</button>
        </div>
        <div class="row g-1" id="products-picker-list"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="products-picker-save"><i class="bi bi-check-lg me-1"></i>Guardar selección</button>
      </div>
    </div>
  </div>
</div>

<script>
var EXTRA_PRODUCTS = <?php echo json_encode($extra_products_json); ?>;
var EXTRA_PICKER_KEY = null;

function openProductsPicker(key, checkedIds){
  EXTRA_PICKER_KEY = key;
  var html = "";
  EXTRA_PRODUCTS.forEach(function(p){
    var checked = checkedIds.indexOf(p.id) !== -1;
    html += '<div class="col-6 col-md-6">';
    html += '<label class="form-check d-flex align-items-center gap-2 border rounded p-2 mb-1">';
    html += '<input class="form-check-input mt-0 pick-item" type="checkbox" value="' + p.id + '"' + (checked ? " checked" : "") + '>';
    if(p.img){ html += '<img src="./' + p.img + '" style="width:38px;height:38px;object-fit:cover;border-radius:6px;">'; }
    else { html += '<span class="d-inline-flex align-items-center justify-content-center text-muted bg-light rounded" style="width:38px;height:38px;"><i class="bi bi-image"></i></span>'; }
    html += '<span class="small text-truncate" style="max-width:170px;">' + p.name + '</span>';
    html += '</label></div>';
  });
  $("#products-picker-list").html(html);
  bootstrap.Modal.getOrCreateInstance(document.getElementById("productsPickerModal")).show();
}

function rowPids($btn){
  var ids = [];
  var pids = String($btn.data("pids") || "");
  if(pids !== ""){
    pids.split(",").forEach(function(x){ if(x !== ""){ ids.push(parseInt(x)); } });
  } else if(String($btn.data("global")) === "1"){
    EXTRA_PRODUCTS.forEach(function(p){ ids.push(p.id); });
  }
  return ids;
}

function saveExtraProducts(key, ids, $lbl){
  var nm = $('input[data-row-name="' + key + '"]').val();
  var pc = $('input[data-row-price="' + key + '"]').val();
  var ig = $('.row-is-ingredient[data-form="' + key + '"]').is(":checked") ? 1 : 0;
  $.post("./?action=settings&opt=updextraprods", { group_key: key, name: nm, price: pc, products: ids.join(","), is_ingredient: ig }, function(){
    var lbl = ids.length === EXTRA_PRODUCTS.length ? "TODOS los productos" : (ids.length > 0 ? ids.length + " producto" + (ids.length > 1 ? "s" : "") : "Ninguno");
    if($lbl){ $lbl.text(lbl); }
    var $btn = $('[data-form="' + key + '"]');
    $btn.attr("data-pids", ids.join(",")).attr("data-global", ids.length === EXTRA_PRODUCTS.length ? "1" : "0");
  }).fail(function(){ alert("No se pudo guardar la selección de productos."); });
}

$(function(){
  $(".btn-pick-products").on("click", function(){
    var key = String($(this).data("form"));
    if(key === "#form-add-extra"){
      var hid = $("#form-add-extra input[name='products']").val();
      var checkedIds = [];
      if(hid !== ""){
        hid.split(",").forEach(function(x){ if(x !== ""){ checkedIds.push(parseInt(x)); } });
      } else {
        EXTRA_PRODUCTS.forEach(function(p){ checkedIds.push(p.id); });
      }
      openProductsPicker(key, checkedIds);
    } else {
      openProductsPicker(key, rowPids($(this)));
    }
  });

  $("#products-check-all").click(function(){ $("#products-picker-list .pick-item").prop("checked", true); });
  $("#products-uncheck-all").click(function(){ $("#products-picker-list .pick-item").prop("checked", false); });

  $("#products-picker-save").click(function(){
    var ids = [];
    $("#products-picker-list .pick-item:checked").each(function(){ ids.push($(this).val()); });
    if(!EXTRA_PICKER_KEY){ bootstrap.Modal.getOrCreateInstance(document.getElementById("productsPickerModal")).hide(); return; }
    if(EXTRA_PICKER_KEY === "#form-add-extra"){
      $("#form-add-extra input[name='products']").val(ids.join(","));
      var lblNew = ids.length === EXTRA_PRODUCTS.length ? "TODOS los productos" : (ids.length > 0 ? ids.length + " producto" + (ids.length > 1 ? "s" : "") : "Ninguno");
      $("#form-add-extra .picker-label").text(lblNew);
    } else {
      saveExtraProducts(EXTRA_PICKER_KEY, ids, $('[data-form="' + EXTRA_PICKER_KEY + '"]').find(".picker-label"));
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById("productsPickerModal")).hide();
  });

  $(".btn-extra-save").click(function(){
    var key = String($(this).data("form"));
    var $btn = $('[data-form="' + key + '"]');
    saveExtraProducts(key, rowPids($btn), $btn.find(".picker-label"));
    Swal.fire({ icon:"success", title:"Guardado", text:"Ingrediente actualizado", timer:1200, showConfirmButton:false });
  });

  // Sedes/Zonas: toggle Con delivery / Sin delivery
  $(document).on("change", ".sede-zone-toggle", function() {
    var $checkbox = $(this);
    var $container = $checkbox.closest(".d-flex");
    var $label = $container.find(".delivery-label");
    var $priceInput = $container.find(".sede-price-input");
    if ($checkbox.is(":checked")) {
      $label.text("Con delivery");
      $priceInput.prop("disabled", false);
      if ($priceInput.val() === "" || parseFloat($priceInput.val()) < 1) {
        $priceInput.val("1.00");
      }
    } else {
      $label.text("Sin delivery");
      $priceInput.prop("disabled", true);
    }
  });

  // Guardar todo: submit all sede-zone forms
  $("#btn-guardar-todo").click(function() {
    var $btn = $(this);
    $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');
    var promises = [];
    $(".sede-zone-toggle").each(function() {
      var $checkbox = $(this);
      var $container = $checkbox.closest(".d-flex");
      var $form = $("<form>", { method: "post", action: "./?action=settings&opt=updsedezone" });
      $form.append($("<input>", { type: "hidden", name: "enabled", value: $checkbox.is(":checked") ? "1" : "" }));
      $form.append($("<input>", { type: "hidden", name: "sede_id", value: $checkbox.data("sede") }));
      $form.append($("<input>", { type: "hidden", name: "zone_id", value: $checkbox.data("zone") }));
      var $priceInput = $container.find(".sede-price-input");
      if ($checkbox.is(":checked")) {
        var price = parseFloat($priceInput.val()) || 1;
        $form.append($("<input>", { type: "hidden", name: "price", value: price }));
      }
      $("body").append($form);
      promises.push($.post($form.attr("action"), $form.serialize()));
      $form.remove();
    });
    Promise.all(promises).then(function() {
      Swal.fire({ icon: "success", title: "Guardado", text: "Todos los cambios guardados", timer: 1500, showConfirmButton: false });
      location.reload();
    }).catch(function() {
      Swal.fire({ icon: "error", title: "Error", text: "Hubo un error al guardar" });
      $btn.prop("disabled", false).html('<i class="bi bi-save-fill me-1"></i> Guardar todos los cambios');
    });
  });
});
</script>
<?php endif; ?>