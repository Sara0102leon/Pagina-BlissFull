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
          <a href="./?view=settings&opt=zones" class="btn btn-default">Zonas de Delivery</a>
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

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="zones"):?>
<?php $zones = DeliveryZoneData::getAll(); ?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Zonas de Delivery</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card mb-3">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Agregar Zona</h3>
      </div>
      <form method="post" action="./?action=settings&opt=addzone">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-8">
              <input type="text" name="name" class="form-control" placeholder="Nombre de la zona" required>
            </div>
            <div class="col-md-3">
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="text" name="price" class="form-control" placeholder="Precio delivery" required>
              </div>
            </div>
            <div class="col-md-1">
              <button type="submit" class="btn btn-success w-100">Agregar</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Zonas Registradas</h3>
      </div>
      <div class="card-body">
        <?php if(count($zones)>0):?>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>Zona</th>
                <th>Precio</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($zones as $z):?>
              <tr>
                <td><?php echo htmlspecialchars($z->name); ?></td>
                <td>$ <?php echo number_format($z->price,2,".",","); ?></td>
                <td class="text-end">
                  <a href="./?action=settings&opt=delzone&id=<?php echo $z->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar esta zona?');"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endforeach;?>
            </tbody>
          </table>
        </div>
        <?php else:?>
          <p class="alert alert-warning mb-0">No hay zonas registradas.</p>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="sedes"):?>
<?php $sedes = SedeData::getAll(); ?>
<?php $zones = DeliveryZoneData::getAll(); ?>
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
    <div class="card mb-3">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Agregar Sede</h3>
      </div>
      <form method="post" action="./?action=settings&opt=addsede">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-4">
              <input type="text" name="name" class="form-control" placeholder="Nombre de la sede" required>
            </div>
            <div class="col-md-4">
              <input type="text" name="address" class="form-control" placeholder="Dirección / Referencia">
            </div>
            <div class="col-md-3">
              <input type="text" name="phone" class="form-control" placeholder="WhatsApp (+58412...)" required>
            </div>
            <div class="col-md-1">
              <button type="submit" class="btn btn-success w-100">Agregar</button>
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
                  <form method="post" action="./?action=settings&opt=updsede" class="d-flex gap-2 align-items-center flex-wrap">
                    <input type="hidden" name="id" value="<?php echo $sd->id; ?>">
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($sd->name); ?>" required style="min-width:180px;">
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($sd->address); ?>" placeholder="Dirección" style="min-width:180px;">
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
    <div class="card">
      <div class="card-status-top bg-warning"></div>
      <div class="card-header">
        <h3 class="card-title">Precios de Delivery por Sede y Zona</h3>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Cada sede puede tener su propio precio por zona de entrega (útil cuando una sede queda lejos de una zona barata).</p>
        <?php if(count($sedes)==0):?>
          <p class="alert alert-warning mb-0">Primero agrega una sede.</p>
        <?php elseif(count($zones)==0):?>
          <p class="alert alert-warning mb-0">No hay zonas de entrega registradas.</p>
        <?php else:?>
          <?php foreach($sedes as $sd): ?>
          <?php $sd_prices = SedeDeliveryZoneData::getBySede($sd->id); $price_map = array(); foreach($sd_prices as $sp){ $price_map[$sp->delivery_zone_id] = $sp->price; } ?>
          <form method="post" action="./?action=settings&opt=updsedezones" class="border rounded-3 p-3 mb-3">
            <input type="hidden" name="sede_id" value="<?php echo $sd->id; ?>">
            <div class="fw-bold mb-2"><i class="bi bi-shop me-1"></i><?php echo htmlspecialchars($sd->name); ?></div>
            <div class="row g-2">
              <?php foreach($zones as $z): ?>
              <div class="col-md-4 col-lg-3">
                <label class="small text-muted"><?php echo htmlspecialchars($z->name); ?></label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" min="0" name="zone_price[<?php echo $z->id; ?>]" class="form-control"
                    value="<?php echo isset($price_map[$z->id]) ? number_format(floatval($price_map[$z->id]),2,".","") : number_format($z->price,2,".",""); ?>">
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-warning btn-sm mt-3"><i class="bi bi-check-lg"></i> Guardar precios de <?php echo htmlspecialchars($sd->name); ?></button>
          </form>
          <?php endforeach; ?>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="horarios"):?>
<?php
$days = array(
  "horario_lunes" => "Lunes",
  "horario_martes" => "Martes",
  "horario_miercoles" => "Miércoles",
  "horario_jueves" => "Jueves",
  "horario_viernes" => "Viernes",
  "horario_sabado" => "Sábado",
  "horario_domingo" => "Domingo"
);
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Horarios de Atención</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-info"></div>
      <div class="card-header">
        <h3 class="card-title">Horario de atención (sección "te esperamos")</h3>
        <p class="text-muted small mb-0 ms-3">Escribe cada día con formato de hora: 10:00 - 22:00</p>
      </div>
      <div class="card-body">
        <form method="post" action="./?action=settings&opt=updhorarios">
          <div class="row g-3">
            <?php foreach($days as $key => $label): ?>
            <?php $row = ConfigurationData::getByPreffix($key); ?>
            <div class="col-md-6">
              <label class="form-label"><?php echo $label; ?></label>
              <input type="text" class="form-control" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($row->val); ?>" placeholder="10:00 - 22:00">
            </div>
            <?php endforeach; ?>
          </div>
          <div class="form-footer mt-3">
            <button type="submit" class="btn btn-success w-100">Guardar Horarios</button>
          </div>
        </form>
      </div>
    </div>
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
          <p class="text-muted small mb-0 mt-2">Ej: Unidad, Media unidad, Pedazo, Docena, Kg...</p>
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
$extras = ProductExtraData::getAll();
$products = ProductData::getAll();
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Ingredientes</h2>
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
      <form method="post" action="./?action=settings&opt=addextra">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-3">
              <input type="text" name="name" class="form-control" placeholder="Nombre del ingrediente" required>
            </div>
            <div class="col-md-3">
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="text" name="price" class="form-control" placeholder="Precio" required>
              </div>
            </div>
            <div class="col-md-4">
              <select name="product_id" class="form-select">
                <option value="">-- Aplica a TODOS los productos --</option>
                <?php foreach($products as $pr):?>
                <option value="<?php echo $pr->id; ?>"><?php echo htmlspecialchars($pr->name); ?></option>
                <?php endforeach;?>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-success w-100">Agregar</button>
            </div>
          </div>
          <p class="text-muted small mb-0 mt-2">Ej: Jamón (3$), Extra de queso (5$). Si dejas "Aplica a TODOS" el ingrediente se ofrece en todos los productos.</p>
        </div>
      </form>
    </div>
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Ingredientes Registrados</h3>
      </div>
      <div class="card-body">
        <?php if(count($extras)>0):?>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>Ingrediente</th>
                <th>Precio</th>
                <th>Aplica a</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($extras as $e):?>
              <tr>
                <td>
                  <form method="post" action="./?action=settings&opt=updingredient" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="id" value="<?php echo $e->id; ?>">
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($e->name); ?>" required>
                </td>
                <td>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="text" name="price" class="form-control" value="<?php echo $e->price; ?>" required style="min-width:90px;">
                  </div>
                </td>
                <td>
                  <select name="product_id" class="form-select">
                    <option value="">-- TODOS --</option>
                    <?php foreach($products as $pr):?>
                    <option value="<?php echo $pr->id; ?>" <?php if($e->product_id==$pr->id){ echo "selected";} ?>><?php echo htmlspecialchars($pr->name); ?></option>
                    <?php endforeach;?>
                  </select>
                </td>
                <td class="text-end text-nowrap">
                  <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-check-lg"></i> Guardar</button>
                  </form>
                  <a href="./?action=settings&opt=delextra&id=<?php echo $e->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar este ingrediente?');"><i class="bi bi-trash"></i></a>
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
<?php endif; ?>