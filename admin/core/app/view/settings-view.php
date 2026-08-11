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
          <a href="./?view=settings&opt=extras" class="btn btn-default">Extras</a>
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

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="extras"):?>
<?php
$extras = ProductExtraData::getAll();
$products = ProductData::getAll();
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Extras de Productos</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card mb-3">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Agregar Extra</h3>
      </div>
      <form method="post" action="./?action=settings&opt=addextra">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-3">
              <input type="text" name="name" class="form-control" placeholder="Nombre del extra" required>
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
          <p class="text-muted small mb-0 mt-2">Ej: Jamón (3$), Extra de queso (5$). Si dejas "Aplica a TODOS" el extra se ofrece en todos los productos.</p>
        </div>
      </form>
    </div>
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Extras Registrados</h3>
      </div>
      <div class="card-body">
        <?php if(count($extras)>0):?>
        <div class="table-responsive">
          <table class="table card-table table-vcenter">
            <thead>
              <tr>
                <th>Extra</th>
                <th>Precio</th>
                <th>Aplica a</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($extras as $e):?>
              <tr>
                <td><?php echo htmlspecialchars($e->name); ?></td>
                <td>$ <?php echo number_format($e->price,2,".",","); ?></td>
                <td><?php echo $e->product_id ? htmlspecialchars(ProductData::getById($e->product_id)->name) : "Todos los productos"; ?></td>
                <td class="text-end">
                  <a href="./?action=settings&opt=delextra&id=<?php echo $e->id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Eliminar este extra?');"><i class="bi bi-trash"></i></a>
                </td>
              </tr>
            <?php endforeach;?>
            </tbody>
          </table>
        </div>
        <?php else:?>
          <p class="alert alert-warning mb-0">No hay extras registrados.</p>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>