<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}
$user= UserData::getById($_SESSION["user_id"]);
if($user==null){ Core::redir("./");}
?>
<?php if(isset($_GET["opt"]) && $_GET["opt"]=="all"):?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Productos</h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <a href="./?view=products&opt=new" class="btn btn-primary">
            <i class="bi bi-plus"></i> Nuevo Producto
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Productos</h3>
      </div>
      <div class="card-body">
    <?php
    $sede_filter = isset($_GET["sede"]) ? intval($_GET["sede"]) : 0;
    if($sede_filter>0){
      $products = ProductData::getBySede($sede_filter);
    } else {
      $products = ProductData::getAll();
    }
    $sedes_list = SedeData::getAll();
    if(count($products)>0):?>
      <div class="mb-3">
        <form method="get" class="row g-2 align-items-center">
          <input type="hidden" name="view" value="products">
          <input type="hidden" name="opt" value="all">
          <div class="col-auto">
            <label class="form-label mb-0 me-2">Sede:</label>
          </div>
          <div class="col-auto">
            <select name="sede" class="form-select" onchange="this.form.submit()">
              <option value="0" <?php if($sede_filter==0){ echo "selected"; } ?>>Todas las sedes</option>
              <?php foreach($sedes_list as $sd): ?>
              <option value="<?php echo $sd->id; ?>" <?php if($sede_filter==$sd->id){ echo "selected"; } ?>><?php echo htmlspecialchars($sd->name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-auto">
            <span class="text-muted small">Los productos de la vista "Todas las sedes" se muestran en el menÃº de todas las sucursales.</span>
          </div>
        </form>
      </div>
      <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Sede</th>
              <th>Visible</th>
              <th>Destacado</th>
              <th>Existencia</th>
              <th>Oferta</th>
              <th class="w-1"></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($products as $cat):
          $p_sede_name = "Todas las sedes";
          $p_sede_class = "bg-secondary";
          foreach($sedes_list as $sd){ if($sd->id==$cat->sede_id){ $p_sede_name = $sd->name; $p_sede_class = "bg-primary"; } }
          ?>
            <tr>
              <td><?php echo $cat->name; ?></td>
              <td><span class="badge <?php echo $p_sede_class; ?>"><i class="bi <?php echo $cat->sede_id? "bi-shop" : "bi-globe2"; ?> me-1"></i><?php echo htmlspecialchars($p_sede_name); ?></span></td>
              <td>
                <?php if($cat->is_public):?><i class="bi bi-check-lg text-success"></i><?php else: ?><i class="bi bi-x-lg text-danger"></i><?php endif; ?>
              </td>
              <td>
                <?php if($cat->is_featured):?><i class="bi bi-check-lg text-success"></i><?php else: ?><i class="bi bi-x-lg text-danger"></i><?php endif; ?>
              </td>
              <td>
                <?php if($cat->in_existence):?><i class="bi bi-check-lg text-success"></i><?php else: ?><i class="bi bi-x-lg text-danger"></i><?php endif; ?>
              </td>
              <td>
                <?php if($cat->is_offert):?><i class="bi bi-check-lg text-success"></i><?php else: ?><i class="bi bi-x-lg text-danger"></i><?php endif; ?>
              </td>
              <td>
                <div class="btn-list flex-nowrap">
                  <a href="../index.php?view=products&opt=open&id=<?php echo $cat->id; ?>" target="_blank" class="btn btn-default btn-sm"><i class="bi bi-link-45deg"></i></a> 
                  <a href="./?view=products&opt=edit&id=<?php echo $cat->id; ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a> 
                  <a href="./?action=products&opt=del&id=<?php echo $cat->id; ?>" class="btn btn-danger btn-sm btn-delete-product" data-name="<?php echo htmlspecialchars($cat->name); ?>"><i class="bi bi-trash"></i></a> 
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
    <?php else:?>
        <p class="alert alert-warning mb-0">No hay productos, puedes empezar agregando tu lista de productos.</p>
    <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
$(function(){
  $(".btn-delete-product").on("click", function(e){
    e.preventDefault();
    var $btn = $(this);
    var name = $btn.data("name");
    var href = $btn.attr("href");

    var challenge = Math.random() < 0.5 ? "text" : "math";
    var expected, title, placeholder;
    if(challenge === "text"){
      expected = "ELIMINAR";
      title = 'Para confirmar escribe la palabra <b>ELIMINAR</b>';
      placeholder = "ELIMINAR";
    }else{
      var a = Math.floor(Math.random()*9)+1;
      var b = Math.floor(Math.random()*9)+1;
      var add = Math.random() < 0.5;
      var x = Math.max(a,b), y = Math.min(a,b);
      var question = add ? (x + " + " + y) : (x + " - " + y);
      expected = add ? (x+y) : (x-y);
      title = 'Para confirmar responde: \u00bfCu\u00e1nto es <b>' + question + '</b>?';
      placeholder = "Resultado";
    }

    if(!window.Swal){
      if(confirm("\u00bfEliminar '" + name + "'?\n\nSe ocultar\u00e1 del men\u00fa y del panel.")){ window.location.href = href; }
      return;
    }
    Swal.fire({
      title: "\u00bfEliminar producto?",
      html: 'Se eliminar\u00e1 <b>' + name + '</b> del men\u00fa y del panel.<br><br>' + title,
      icon: "warning",
      input: "text",
      inputPlaceholder: placeholder,
      showCancelButton: true,
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#1a0004",
      cancelButtonColor: "#000000",
      inputValidator: function(value){
        if(!value) return "Debes responder la verificaci\u00f3n";
        if(challenge === "text" && value.trim().toUpperCase() !== expected) return "Palabra incorrecta, int\u00e9ntalo de nuevo";
        if(challenge === "math" && parseInt(value) !== expected) return "Resultado incorrecto, int\u00e9ntalo de nuevo";
        return null;
      }
    }).then(function(result){
      if(result.isConfirmed){ window.location.href = href; }
    });
  });
});
</script>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="new"):?>
<?php $coin = ConfigurationData::getByPreffix("general_coin")->val; ?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Nuevo Producto</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Nuevo Producto</h3>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="./?action=products&opt=add">
          <div class="row row-cards">
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" name="name" required placeholder="Nombre del producto">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <textarea class="form-control" placeholder="Descripcion" rows="4" name="description"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Precio</label>
            <div class="input-group">
              <span class="input-group-text"><?php echo $coin; ?></span>
              <input type="text" class="form-control" placeholder="Precio" required name="price">
            </div>
          </div>
          <div class="row row-cards">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Precio en oferta <span class="text-muted small">(opcional: se muestra en vez del precio y el anterior queda tachado)</span></label>
                <div class="input-group">
                  <span class="input-group-text"><?php echo $coin; ?></span>
                  <input type="text" class="form-control" placeholder="Precio de oferta" name="offer_price">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Fin de la oferta <span class="text-muted small">(opcional: despuÃ©s de esta fecha se cobra el precio normal)</span></label>
                <input type="date" class="form-control" name="offer_finish">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Precio para llevar (Delivery) <span class="text-muted small">(opcional, si es distinto al precio normal)</span></label>
            <div class="input-group">
              <span class="input-group-text"><?php echo $coin; ?></span>
              <input type="text" class="form-control" placeholder="Precio al llevar" name="price_llevar">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Ingredientes gratis <span class="text-muted small">(cuÃ¡ntos ingredientes van incluidos sin costo; el resto se cobra. Ej: 3 en la pizza familiar)</span></label>
            <input type="number" min="0" step="1" class="form-control" placeholder="0" name="free_ingredients" value="0">
          </div>
          <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" class="form-control" name="image">
          </div>
          <div class="mb-3">
            <div class="form-label">Opciones</div>
            <div class="d-flex gap-3">
              <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="is_public">
                <span class="form-check-label">Es Visible</span>
              </label>
              <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="in_existence">
                <span class="form-check-label">En Existencia</span>
              </label>
              <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="is_featured">
                <span class="form-check-label">Producto Destacado</span>
              </label>
              <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="is_offert">
                <span class="form-check-label">Producto en Oferta</span>
              </label>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Tipo de divisiÃ³n <span class="text-muted small">(solo pizzas: el cliente elige el sabor de cada fracciÃ³n; los ingredientes de la pizza son las opciones)</span></label>
            <select name="tipo_division" class="form-select">
              <option value="normal">Normal (el cliente arma su pizza con ingredientes)</option>
              <option value="2_estaciones">2 Estaciones (cliente elige 2 mitades)</option>
              <option value="4_estaciones">4 Estaciones (cliente elige 4 cuartos)</option>
            </select>
          </div>
          <div class="row row-cards">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Unidad</label>
                <?php $units = UnitData::getAll(); ?>
                <select name="unit_id" class="form-select" required>
                  <option value="">-- SELECCIONE UNIDAD --</option>
                  <?php foreach($units as $cat):?>
                  <option value="<?php echo $cat->id; ?>"><?php echo $cat->name; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Categoria</label>
                <?php $categories = CategoryData::getActives(); ?>
                <select name="category_id" class="form-select" required>
                  <option value="">-- SELECCIONE CATEGORIA --</option>
                  <?php foreach($categories as $cat):?>
                  <option value="<?php echo $cat->id; ?>"><?php echo $cat->name; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Sede <span class="text-muted small">(menÃº por sucursal)</span></label>
                <?php $sedes_form = SedeData::getAll(); ?>
                <select name="sede_id" class="form-select">
                  <option value="">-- TODAS LAS SEDES --</option>
                  <?php foreach($sedes_form as $sd): ?>
                  <option value="<?php echo $sd->id; ?>"><?php echo htmlspecialchars($sd->name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="form-footer">
            <button type="submit" class="btn btn-primary w-100">Agregar Producto</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="edit"):?>
<?php 
$product = ProductData::getById($_GET["id"]);
$url = "storage/products/$product->image";
$coin = ConfigurationData::getByPreffix("general_coin")->val; 
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Editar Producto: <?php echo $product->name; ?></h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-info"></div>
      <div class="card-header">
        <h3 class="card-title">Editar Producto</h3>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="./?action=products&opt=upd">
          <input type="hidden" name="id" value="<?php echo $product->id;?>">
          <div class="row row-cards">
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" name="name" value="<?php echo $product->name; ?>" required placeholder="Nombre del producto">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <textarea class="form-control" placeholder="Descripcion" rows="4" name="description"><?php echo $product->description; ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Precio</label>
            <div class="input-group">
              <span class="input-group-text"><?php echo $coin; ?></span>
              <input type="text" class="form-control" placeholder="Precio" value="<?php echo $product->price; ?>" required name="price">
            </div>
          </div>
          <div class="row row-cards">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Precio en oferta <span class="text-muted small">(opcional: se muestra en vez del precio y el anterior queda tachado)</span></label>
                <div class="input-group">
                  <span class="input-group-text"><?php echo $coin; ?></span>
                  <input type="text" class="form-control" placeholder="Precio de oferta" value="<?php echo $product->offer_price; ?>" name="offer_price">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Fin de la oferta <span class="text-muted small">(opcional: despuÃ©s de esta fecha se cobra el precio normal)</span></label>
                <input type="date" class="form-control" value="<?php echo $product->offer_finish; ?>" name="offer_finish">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Precio para llevar (Delivery) <span class="text-muted small">(opcional, si es distinto al precio normal)</span></label>
            <div class="input-group">
              <span class="input-group-text"><?php echo $coin; ?></span>
              <input type="text" class="form-control" placeholder="Precio al llevar" value="<?php echo $product->price_llevar; ?>" name="price_llevar">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Ingredientes gratis <span class="text-muted small">(cuÃ¡ntos ingredientes van incluidos sin costo; el resto se cobra. Ej: 3 en la pizza familiar)</span></label>
            <input type="number" min="0" step="1" class="form-control" placeholder="0" name="free_ingredients" value="<?php echo intval($product->free_ingredients); ?>">
          </div>
          <?php if( $product->image!="" && file_exists($url)):?>
          <div class="mb-3">
            <img src="<?php echo $url; ?>" class="img-fluid rounded border" style="max-height: 200px;">
          </div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" class="form-control" name="image">
          </div>
          <div class="mb-3">
            <div class="form-label">Opciones</div>
            <div class="d-flex gap-3">
              <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="is_public" <?php if($product->is_public){ echo "checked";} ?>>
                <span class="form-check-label">Es Visible</span>
              </label>
              <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="in_existence" <?php if($product->in_existence){ echo "checked";} ?>>
                <span class="form-check-label">En Existencia</span>
              </label>
              <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="is_featured" <?php if($product->is_featured){ echo "checked";} ?>>
                <span class="form-check-label">Producto Destacado</span>
              </label>
              <label class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="is_offert" <?php if($product->is_offert){ echo "checked";} ?>>
                <span class="form-check-label">Producto en Oferta</span>
              </label>
            </div>
          </div>
          <?php $td_val = isset($product->tipo_division) ? trim((string)$product->tipo_division) : "normal"; ?>
          <div class="mb-3">
            <label class="form-label">Tipo de divisiÃ³n <span class="text-muted small">(solo pizzas: el cliente elige el sabor de cada fracciÃ³n; los ingredientes de la pizza son las opciones)</span></label>
            <select name="tipo_division" class="form-select">
              <option value="normal" <?php if($td_val=="normal"){ echo "selected"; } ?>>Normal (el cliente arma su pizza con ingredientes)</option>
              <option value="2_estaciones" <?php if($td_val=="2_estaciones"){ echo "selected"; } ?>>2 Estaciones (cliente elige 2 mitades)</option>
              <option value="4_estaciones" <?php if($td_val=="4_estaciones"){ echo "selected"; } ?>>4 Estaciones (cliente elige 4 cuartos)</option>
            </select>
          </div>
          <div class="row row-cards">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Unidad</label>
                <?php $units = UnitData::getAll(); ?>
                <select name="unit_id" class="form-select" required>
                  <option value="">-- SELECCIONE UNIDAD --</option>
                  <?php foreach($units as $cat):?>
                  <option value="<?php echo $cat->id; ?>" <?php if($product->unit_id==$cat->id){ echo "selected";} ?>><?php echo $cat->name; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Categoria</label>
                <?php $categories = CategoryData::getActives(); ?>
                <select name="category_id" class="form-select" required>
                  <option value="">-- SELECCIONE CATEGORIA --</option>
                  <?php foreach($categories as $cat):?>
                  <option value="<?php echo $cat->id; ?>" <?php if($product->category_id==$cat->id){ echo "selected";} ?>><?php echo $cat->name; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Sede <span class="text-muted small">(menÃº por sucursal)</span></label>
                <?php $sedes_form = SedeData::getAll(); ?>
                <select name="sede_id" class="form-select">
                  <option value="">-- TODAS LAS SEDES --</option>
                  <?php foreach($sedes_form as $sd): ?>
                  <option value="<?php echo $sd->id; ?>" <?php if($product->sede_id==$sd->id){ echo "selected"; } ?>><?php echo htmlspecialchars($sd->name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="form-footer">
            <button type="submit" class="btn btn-success w-100">Actualizar Producto</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
