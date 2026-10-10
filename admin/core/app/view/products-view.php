<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}
$user= UserData::getById($_SESSION["user_id"]);
if($user==null){ Core::redir("./");}
$tt_cat_guides = array(
  "pizza"=>"<b>Pizza:</b> completa sus datos de pizza: cuántos ingredientes trae gratis, qué ingredientes incluye y si el cliente elige mitades/cuartos (tipo de división).",
  "pasta"=>"<b>Pasta:</b> solo datos básicos (nombre, descripción, precios, imagen y sede). No lleva ingredientes extra ni división: el cliente lo agrega directo al carrito.",
  "focaccia"=>"<b>Focaccia:</b> igual que la pasta, solo datos básicos. No lleva ingredientes ni división.",
  "bebida"=>"<b>Bebida/Refresco:</b> solo datos básicos. El sabor y el tamaño van en el Nombre (ej. \"Coca-Cola 2 L\"). Se agrega directo, sin ingredientes.",
  "general"=>"<b>Categoría genérica:</b> se usa únicamente con los datos básicos. Si va a ser una pizza, usa una categoría cuyo nombre lo indique."
);
$tt_cat_guide_map = array();
foreach(CategoryData::getActives() as $gc){
  $gcn = mb_strtolower(trim($gc->name));
  $gtype = "general";
  if(strpos($gcn,"pizza")!==false){ $gtype = "pizza"; }
  else if(strpos($gcn,"pasta")!==false){ $gtype = "pasta"; }
  else if(strpos($gcn,"focaccia")!==false){ $gtype = "focaccia"; }
  else if(strpos($gcn,"bebida")!==false || strpos($gcn,"refresco")!==false || strpos($gcn,"jugo")!==false){ $gtype = "bebida"; }
  $tt_cat_guide_map[$gc->id] = $gtype;
}
$tt_cat_guides_json = json_encode($tt_cat_guides);
$tt_cat_guide_map_json = json_encode($tt_cat_guide_map);
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
    <?php if(isset($_SESSION["product_img_error"]) && trim($_SESSION["product_img_error"])!=""): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION["product_img_error"]); ?></div>
    <?php unset($_SESSION["product_img_error"]); endif; ?>
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
            <span class="text-muted small">Los productos de la vista "Todas las sedes" se muestran en el menú de todas las sucursales.</span>
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
          $p_sede_icon = "bi-globe2";
          if(intval($cat->sede_id) === 0){
            $p_sede_name = "Varias sedes";
            $p_sede_icon = "bi-shop";
          } else {
            foreach($sedes_list as $sd){ if($sd->id==$cat->sede_id){ $p_sede_name = $sd->name; $p_sede_icon = "bi-shop"; } }
          }
          ?>
            <tr>
              <td><?php echo $cat->name; ?></td>
              <td><span class="badge" style="background:#1a3a5c!important;color:#fff!important;"><i class="bi <?php echo $p_sede_icon; ?> me-1"></i><?php echo htmlspecialchars($p_sede_name); ?></span></td>
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
                <?php if(ProductData::offerActive($cat)):?><i class="bi bi-check-lg text-success"></i><?php else: ?><i class="bi bi-x-lg text-danger"></i><?php endif; ?>
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

<?php elseif(isset($_GET["opt"]) && ($_GET["opt"]=="new" || $_GET["opt"]=="edit")):?>
<?php
// Asistente de producto (nuevo/editar): misma acción de backend (products&opt=add|upd),
// mismos nombre= de campos. Sólo cambia la experiencia: pasos + vista previa en vivo.
$pw_mode = ($_GET["opt"]=="edit") ? "edit" : "new";
$product = null;
$url_exists = false;
$pv_img_src = "";
$ps_sedes_all = true;
$ps_multi = array();
$house_current = array();
$td_val = "normal";
if($pw_mode=="edit"){
  $product = ProductData::getById(intval($_GET["id"]));
  if(!$product){ Core::redir("./?view=products&opt=all"); }
  // Seguridad multi-tenant: no editar productos de otra tienda
  if(Tenant::id()>0 && intval($product->tienda_id)!==Tenant::id()){ Core::redir("./?view=products&opt=all"); }
  $url = "storage/products/".$product->image;
  $url_exists = ($product->image!="" && file_exists($url));
  if($url_exists){ $pv_img_src = $url."?v=".filemtime($url); }
  $ps_sedes_all = ($product->sede_id === "" || $product->sede_id === null);
  if(!$ps_sedes_all){
    if(intval($product->sede_id) === 0){
      $ps_multi = $product->getSedes();
      if(count($ps_multi) === 0){ $ps_sedes_all = true; }
    } else {
      $ps_multi = array(intval($product->sede_id));
    }
  }
  if(trim((string)$product->house_ingredients)!=""){
    foreach(explode(",", $product->house_ingredients) as $hc){
      $house_current[] = strtr(mb_strtolower(trim($hc)), array("á"=>"a","é"=>"e","í"=>"i","ó"=>"o","ú"=>"u","ñ"=>"n"));
    }
  }
  $td_val = isset($product->tipo_division) ? trim((string)$product->tipo_division) : "normal";
  if(!in_array($td_val, array("normal","2_estaciones","4_estaciones"))){ $td_val = "normal"; }
}
$coin_row = ConfigurationData::getByPreffix("general_coin");
$coin = $coin_row ? $coin_row->val : "$";
$sedes_form = SedeData::getAll();
$categories = CategoryData::getActives();
$house_catalog = array();
foreach(ProductExtraData::getAll() as $pe){
  if(intval($pe->is_ingredient)==1 && trim($pe->name)!=""){
    $house_catalog[strtr(mb_strtolower(trim($pe->name)), array("á"=>"a","é"=>"e","í"=>"i","ó"=>"o","ú"=>"u","ñ"=>"n"))] = $pe->name;
  }
}
ksort($house_catalog);
$pw_v = function($prop) use ($product){ return $product ? htmlspecialchars((string)$product->$prop, ENT_QUOTES) : ""; };
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title"><?php echo $pw_mode=="edit" ? "Editar producto" : "Nuevo producto"; ?></h2>
        <div class="text-muted"><?php echo $pw_mode=="edit" ? htmlspecialchars($product->name) : "Se guarda en 4 pasos sencillos."; ?></div>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <a href="./?view=products&opt=all" class="btn btn-ghost-secondary"><i class="bi bi-arrow-left"></i> Volver a la lista</a>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <form method="post" enctype="multipart/form-data" action="./?action=products&opt=<?php echo $pw_mode=="edit" ? "upd" : "add"; ?>" id="pw_form">
      <?php if($pw_mode=="edit"): ?><input type="hidden" name="id" value="<?php echo intval($product->id); ?>"><?php endif; ?>
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="card">
            <div class="card-status-top bg-primary"></div>
            <div class="card-body">
              <ul class="pw-steps" id="pw_steps">
                <li class="pw-step active" data-goto="1"><span class="pw-dot">1</span><span class="pw-label">Básicos</span></li>
                <li class="pw-step" data-goto="2"><span class="pw-dot">2</span><span class="pw-label">Precio</span></li>
                <li class="pw-step" data-goto="3"><span class="pw-dot">3</span><span class="pw-label">En el menú</span></li>
                <li class="pw-step" data-goto="4"><span class="pw-dot">4</span><span class="pw-label">Publicar</span></li>
              </ul>

              <!-- Paso 1: datos básicos -->
              <section class="pw-pane" data-pane="1">
                <div class="pw-field">
                  <label class="form-label">Foto del producto</label>
                  <div class="pw-dropzone" id="pw_drop">
                    <i class="bi bi-cloud-arrow-up h1 d-block mb-1"></i>
                    <div class="fw-bold">Haz clic o arrastra una imagen aquí</div>
                    <div class="text-muted small">JPG o PNG. Se mostrará en grande en tu menú.</div>
                  </div>
                  <input type="file" name="image" id="pw_image" accept="image/*" class="d-none">
                  <div class="text-muted small mt-1" id="pw_file_name"><?php echo ($url_exists ? "Imagen actual cargada" : "Sin imagen (opcional)"); ?></div>
                </div>
                <div class="pw-field">
                  <label class="form-label required">Nombre</label>
                  <input type="text" class="form-control form-control-lg" name="name" id="pw_name" placeholder="Ej: Pizza Gigante Pepperoni" value="<?php echo $pw_v("name"); ?>" required>
                </div>
                <div class="pw-field">
                  <label class="form-label">Descripción <span class="text-muted small">(opcional, se ve debajo del nombre en el modal)</span></label>
                  <textarea class="form-control" name="description" id="pw_desc" rows="3" placeholder="Ej: Salsa de tomate, mozzarella y pepperoni"><?php echo $pw_v("description"); ?></textarea>
                </div>
              </section>

              <!-- Paso 2: precio -->
              <section class="pw-pane d-none" data-pane="2">
                <div class="pw-field">
                  <label class="form-label required">Precio</label>
                  <div class="input-group input-group-lg">
                    <span class="input-group-text"><?php echo $coin; ?></span>
                    <input type="text" inputmode="decimal" class="form-control" name="price" id="pw_price" placeholder="0.00" value="<?php echo $pw_v("price"); ?>" required>
                  </div>
                </div>
                <div class="pw-field">
                  <label class="form-label">Precio para llevar (Delivery) <span class="text-muted small">(opcional)</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><?php echo $coin; ?></span>
                    <input type="text" inputmode="decimal" class="form-control" name="price_llevar" id="pw_price_llevar" placeholder="Igual al precio normal" value="<?php echo $pw_v("price_llevar"); ?>">
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="pw-field">
                      <label class="form-label">Precio en oferta <span class="text-muted small">(opcional)</span></label>
                      <div class="input-group">
                        <span class="input-group-text"><?php echo $coin; ?></span>
                        <input type="text" inputmode="decimal" class="form-control" name="offer_price" id="pw_offer_price" placeholder="Precio rebajado" value="<?php echo $pw_v("offer_price"); ?>">
                      </div>
                      <div class="text-muted small mt-1">Al colocarlo, el producto queda en oferta automáticamente.</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="pw-field">
                      <label class="form-label">Fin de la oferta <span class="text-muted small">(opcional)</span></label>
                      <input type="date" class="form-control" name="offer_finish" id="pw_offer_finish" value="<?php echo $pw_v("offer_finish"); ?>">
                      <div class="text-muted small mt-1">Después de esta fecha vuelve al precio normal.</div>
                    </div>
                  </div>
                </div>
              </section>

              <!-- Paso 3: en el menú -->
              <section class="pw-pane d-none" data-pane="3">
                <div class="pw-field">
                  <label class="form-label required">Categoría</label>
                  <select id="pw_cat" name="category_id" class="form-select form-select-lg" required>
                    <option value="">-- Elige la categoría --</option>
                    <?php foreach($categories as $cat):?>
                    <option value="<?php echo $cat->id; ?>" <?php if($product && $product->category_id==$cat->id){ echo "selected"; } ?>><?php echo htmlspecialchars($cat->name); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="alert alert-info mt-3 mb-0 d-none" id="pw_cat_guide"></div>
                </div>
                <div class="pw-field">
                  <label class="form-label">Disponible en</label>
                  <label class="form-check">
                    <input class="form-check-input pw-sedes-all" type="checkbox" name="sedes_all" <?php echo $ps_sedes_all ? "checked" : ""; ?>>
                    <span class="form-check-label">Todas las sedes</span>
                  </label>
                  <div class="pw-sedes-list <?php echo $ps_sedes_all ? "pw-disabled" : ""; ?>">
                    <?php foreach($sedes_form as $sd): ?>
                    <label class="form-check form-check-inline mb-1">
                      <input class="form-check-input pw-sede-chk" type="checkbox" name="sedes[]" value="<?php echo intval($sd->id); ?>" <?php echo in_array(intval($sd->id), $ps_multi) ? "checked" : ""; ?>>
                      <span class="form-check-label"><?php echo htmlspecialchars($sd->name); ?></span>
                    </label>
                    <?php endforeach; ?>
                    <div class="text-muted small mt-1">Desmarca "Todas las sedes" para elegir en cuáles aparece.</div>
                  </div>
                </div>
                <div id="pw_pizza_fields" class="d-none">
                  <hr>
                  <div class="h4 mb-3"><i class="bi bi-pie-chart-fill text-gold me-1"></i> Datos de pizza</div>
                  <div class="pw-field">
                    <label class="form-label">Ingredientes gratis <span class="text-muted small">(cuántos van incluidos sin costo)</span></label>
                    <input type="number" min="0" step="1" class="form-control" name="free_ingredients" value="<?php echo $product ? intval($product->free_ingredients) : 0; ?>">
                  </div>
                  <div class="pw-field">
                    <label class="form-label">Ingredientes que trae <span class="text-muted small">(se premarcan como incluidos en el menú; si no marcas ninguno se detectan desde la descripción)</span></label>
                    <div class="row g-2">
                      <?php foreach($house_catalog as $hkey => $hname): ?>
                      <div class="col-6 col-md-4">
                        <label class="form-check">
                          <input class="form-check-input" type="checkbox" name="house_ingredients[]" value="<?php echo htmlspecialchars($hname); ?>" <?php if(in_array($hkey, $house_current)){ echo "checked"; } ?>>
                          <span class="form-check-label"><?php echo htmlspecialchars($hname); ?></span>
                        </label>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="pw-field">
                    <label class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" name="has_extras">
                      <span class="form-check-label">¿Tiene ingredientes extra? <span class="text-muted small">(al guardar se agrega a todos los grupos de ingredientes)</span></span>
                    </label>
                  </div>
                  <div class="pw-field">
                    <label class="form-label">Tipo de división <span class="text-muted small">(el cliente elige el sabor de cada fracción)</span></label>
                    <select name="tipo_division" class="form-select">
                      <option value="normal" <?php if($td_val=="normal"){ echo "selected"; } ?>>Normal (arma su pizza con ingredientes)</option>
                      <option value="2_estaciones" <?php if($td_val=="2_estaciones"){ echo "selected"; } ?>>2 Estaciones (elige 2 mitades)</option>
                      <option value="4_estaciones" <?php if($td_val=="4_estaciones"){ echo "selected"; } ?>>4 Estaciones (elige 4 cuartos)</option>
                    </select>
                  </div>
                </div>
              </section>

              <!-- Paso 4: publicar -->
              <section class="pw-pane d-none" data-pane="4">
                <div class="pw-review mb-4">
                  <div class="pw-review-img"><img id="rv_img" alt=""></div>
                  <div class="pw-review-txt">
                    <div class="pw-review-name" id="rv_name">—</div>
                    <div class="text-muted small" id="rv_cat">—</div>
                    <div class="pw-review-price" id="rv_price">—</div>
                  </div>
                </div>
                <label class="form-label">Estado en el menú</label>
                <div class="pw-toggles">
                  <label class="pw-toggle">
                    <input class="form-check-input" type="checkbox" name="is_public" <?php if($pw_mode=="new" || ($product && $product->is_public)){ echo "checked"; } ?>>
                    <span><b>Visible</b><br><span class="text-muted small">Aparece en el menú de los clientes</span></span>
                  </label>
                  <label class="pw-toggle">
                    <input class="form-check-input" type="checkbox" name="in_existence" <?php if($pw_mode=="new" || ($product && $product->in_existence)){ echo "checked"; } ?>>
                    <span><b>En existencia</b><br><span class="text-muted small">Se puede pedir ahora mismo</span></span>
                  </label>
                  <label class="pw-toggle">
                    <input class="form-check-input" type="checkbox" name="is_featured" <?php if($product && $product->is_featured){ echo "checked"; } ?>>
                    <span><b>Destacado</b><br><span class="text-muted small">Sale primero en "Destacados"</span></span>
                  </label>
                </div>
                <div class="alert alert-warning mt-3 mb-0 d-none" id="pw_inactive_warn">
                  <i class="bi bi-exclamation-triangle me-1"></i>Este producto quedará <b>oculto</b> para los clientes.
                </div>
              </section>
            </div>
            <div class="card-footer d-flex align-items-center">
              <button type="button" class="btn btn-ghost-secondary d-none" id="pw_prev"><i class="bi bi-arrow-left"></i> Anterior</button>
              <div class="ms-auto">
                <button type="button" class="btn btn-primary" id="pw_next">Siguiente <i class="bi bi-arrow-right"></i></button>
                <button type="submit" class="btn btn-success d-none" id="pw_submit"><i class="bi bi-check-lg"></i> <?php echo $pw_mode=="edit" ? "Guardar cambios" : "Publicar producto"; ?></button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="pw-preview-wrap">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="bi bi-eye me-1"></i> Vista previa</h3>
                <div class="card-actions text-muted small">Así se verá en tu menú</div>
              </div>
              <div class="card-body text-center">
                <div class="tt-product-card pw-live">
                  <div class="pc-img-wrap">
                    <img id="pv_img" class="pc-img" alt="Vista previa" src="<?php echo $pv_img_src; ?>" <?php echo $url_exists ? "" : "style=\"display:none\""; ?>>
                    <div class="pc-img-ph" id="pv_img_ph" <?php echo $url_exists ? "style=\"display:none\"" : ""; ?>><i class="bi bi-image"></i></div>
                    <span class="pc-feat-badge" id="pv_feat" <?php if(!($product && $product->is_featured)){ echo "style=\"display:none\""; } ?>><i class="bi bi-star-fill me-1"></i>Destacado</span>
                  </div>
                  <div class="pc-body">
                    <h3 class="pc-name" id="pv_name"><?php echo $product ? htmlspecialchars($product->name) : "Nombre del producto"; ?></h3>
                    <div class="pc-meta">
                      <span class="pc-price-pill" id="pv_price"><?php echo $coin; ?>0.00</span>
                      <span class="pc-price-old" id="pv_old" style="display:none"></span>
                      <span class="pc-price-dine" id="pv_llevar" style="display:none"></span>
                    </div>
                  </div>
                  <div class="pc-footer">
                    <button type="button" class="pc-add-btn" tabindex="-1" aria-hidden="true"><i class="bi bi-plus-lg"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
  .pw-steps{ list-style:none; display:flex; gap:.25rem; padding:0; margin:0 0 1.5rem; }
  .pw-step{ flex:1; text-align:center; cursor:pointer; color:#6c757d; }
  .pw-step .pw-dot{ display:inline-flex; width:30px; height:30px; border-radius:50%; background:#e9ecef; color:#6c757d; align-items:center; justify-content:center; font-weight:700; transition:.15s; }
  .pw-step .pw-label{ display:block; font-size:.78rem; margin-top:.25rem; }
  .pw-step.active{ color:var(--tblr-primary,#206bc4); }
  .pw-step.active .pw-dot{ background:var(--tblr-primary,#206bc4); color:#fff; transform:scale(1.08); }
  .pw-step.done{ color:#2fb344; }
  .pw-step.done .pw-dot{ background:#2fb344; color:#fff; }
  .pw-field{ margin-bottom:1.1rem; }
  .pw-dropzone{ border:2px dashed #ced4da; border-radius:12px; padding:1.5rem 1rem; text-align:center; cursor:pointer; color:#6c757d; background:#f8f9fa; transition:.15s; }
  .pw-dropzone:hover,.pw-dropzone.pw-drag{ border-color:var(--tblr-primary,#206bc4); background:#eef4fb; color:var(--tblr-primary,#206bc4); }
  .pw-disabled{ opacity:.45; }
  .pw-preview-wrap{ position:sticky; top:1rem; }
  .pw-live{ max-width:300px; margin:0 auto; background:#0c0c0c; border:1px solid #262626; border-radius:16px; overflow:hidden; color:#fff; text-align:left; }
  .pw-live .pc-img-wrap{ position:relative; height:170px; background:#161616; display:flex; align-items:center; justify-content:center; }
  .pw-live .pc-img{ width:100%; height:100%; object-fit:cover; }
  .pw-live .pc-img-ph{ font-size:2.5rem; color:#3a3a3a; }
  .pw-live .pc-feat-badge{ position:absolute; top:.5rem; left:.5rem; background:#ffb703; color:#111; font-size:.7rem; font-weight:700; padding:.15rem .5rem; border-radius:999px; }
  .pw-live .pc-body{ padding:.85rem .95rem .4rem; }
  .pw-live .pc-name{ font-size:1.25rem; font-weight:700; margin:0 0 .5rem; line-height:1.15; }
  .pw-live .pc-price-pill{ display:inline-block; background:#ffb703; color:#111; font-weight:700; padding:.2rem .7rem; border-radius:999px; font-size:.95rem; }
  .pw-live .pc-price-old{ text-decoration:line-through; color:#8a8a8a; font-size:.82rem; margin-left:.4rem; }
  .pw-live .pc-price-dine{ display:block; color:#b5b5b5; font-size:.78rem; margin-top:.3rem; }
  .pw-live .pc-footer{ padding:.7rem .95rem; display:flex; justify-content:flex-end; }
  .pw-live .pc-add-btn{ width:34px; height:34px; border-radius:50%; border:0; background:#ffb703; color:#111; font-size:1.1rem; }
  .pw-review{ display:flex; gap:1rem; align-items:center; padding:1rem; border:1px solid #dee2e6; border-radius:12px; background:#f8f9fa; }
  .pw-review-img{ width:64px; height:64px; border-radius:10px; background:#e9ecef center/cover no-repeat; flex:0 0 64px; }
  .pw-review-name{ font-weight:700; font-size:1.05rem; }
  .pw-review-price{ color:#0a3622; font-weight:700; margin-top:.15rem; }
  .pw-toggles{ display:grid; gap:.6rem; }
  .pw-toggle{ display:flex; gap:.65rem; align-items:flex-start; padding:.75rem .9rem; border:1px solid #dee2e6; border-radius:10px; cursor:pointer; }
  .pw-toggle:hover{ border-color:var(--tblr-primary,#206bc4); }
</style>

<script>
$(function(){
  var PW_COIN = <?php echo json_encode($coin); ?>;
  var TT_GUIDES = <?php echo $tt_cat_guides_json; ?>;
  var CAT_TYPES = <?php echo $tt_cat_guide_map_json; ?>;
  var PW_STEP = 1;
  var PW_LAST = 4;

  function pwParse(v){
    var s = String(v == null ? "" : v).replace(/\s/g, "");
    if(s === ""){ return 0; }
    if(s.indexOf(",") >= 0 && s.indexOf(".") >= 0){ s = s.replace(/\./g, "").replace(",", "."); }
    else if(s.indexOf(",") >= 0){ s = s.replace(",", "."); }
    var n = parseFloat(s);
    return isNaN(n) ? 0 : n;
  }
  function pwMoney(v){ return PW_COIN + pwParse(v).toFixed(2); }
  function pwNum(v){ return pwParse(v); }
  function pwAlert(msg){
    if(window.Swal){ Swal.fire({ icon:"info", title:"Falta un dato", text:msg, confirmButtonColor:"#1a0004" }); }
    else { alert(msg); }
  }

  function validateStep(n){
    if(n === 1 && !$.trim($("#pw_name").val())){ pwAlert("Escribe el nombre del producto."); return false; }
    if(n === 2 && !$.trim($("#pw_price").val())){ pwAlert("Escribe el precio del producto."); return false; }
    if(n === 3 && !$("#pw_cat").val()){ pwAlert("Elige la categoría del producto."); return false; }
    return true;
  }

  function goToStep(n){
    if(n < 1) n = 1;
    if(n > PW_LAST) n = PW_LAST;
    if(n > PW_STEP && !validateStep(PW_STEP)){ return; }
    PW_STEP = n;
    $(".pw-pane").addClass("d-none").filter("[data-pane='" + n + "']").removeClass("d-none");
    $(".pw-step").each(function(i){
      var s = i + 1;
      $(this).removeClass("active done");
      if(s < n) $(this).addClass("done");
      if(s === n) $(this).addClass("active");
    });
    $("#pw_prev").toggleClass("d-none", n === 1);
    $("#pw_next").toggleClass("d-none", n === PW_LAST);
    $("#pw_submit").toggleClass("d-none", n !== PW_LAST);
    if(n === PW_LAST){ renderReview(); }
    $("html, body").animate({ scrollTop: 0 }, 150);
  }
  $("#pw_next").on("click", function(){ goToStep(PW_STEP + 1); });
  $("#pw_prev").on("click", function(){ goToStep(PW_STEP - 1); });
  $(".pw-step").on("click", function(){ goToStep(parseInt($(this).data("goto"), 10)); });

  // ---- Guía según categoría y campos de pizza ----
  function applyCatFields(cid){
    var t = CAT_TYPES[cid] || "general";
    var isPizza = (t === "pizza");
    if(cid === "" || cid === null || cid === undefined){
      $("#pw_cat_guide").addClass("d-none");
      $("#pw_pizza_fields").addClass("d-none");
      return;
    }
    $("#pw_cat_guide").html(TT_GUIDES[t] || TT_GUIDES.general).removeClass("d-none");
    $("#pw_pizza_fields").toggleClass("d-none", !isPizza);
  }
  $("#pw_cat").on("change", function(){ applyCatFields($(this).val()); });

  // ---- Sedes ----
  function pwSyncSedes(){
    var all = $(".pw-sedes-all").is(":checked");
    $(".pw-sedes-list").toggleClass("pw-disabled", all);
    $(".pw-sede-chk").prop("disabled", all);
    if(!all && $(".pw-sede-chk:checked").length === 0){ $(".pw-sede-chk").first().prop("checked", true); }
  }
  $(".pw-sedes-all").on("change", pwSyncSedes);

  // ---- Vista previa en vivo ----
  function pvRender(){
    var name = $.trim($("#pw_name").val()) || "Nombre del producto";
    $("#pv_name").text(name);
    var base = pwNum($("#pw_price").val());
    var offer = pwNum($("#pw_offer_price").val());
    var llevar = pwNum($("#pw_price_llevar").val());
    var show = (offer > 0) ? offer : base;
    $("#pv_price").text(pwMoney(show));
    if(offer > 0 && base > 0){
      $("#pv_old").text("antes " + pwMoney(base)).show();
    } else { $("#pv_old").hide(); }
    if(llevar > 0 && Math.abs(llevar - show) > 0.0001){
      $("#pv_llevar").text("para llevar " + pwMoney(llevar)).show();
    } else { $("#pv_llevar").hide(); }
    $("#pv_feat").toggle($("input[name='is_featured']").is(":checked"));
  }
  $("#pw_name, #pw_price, #pw_offer_price, #pw_price_llevar").on("input", pvRender);
  $("input[name='is_featured']").on("change", pvRender);

  // ---- Imagen (drag & drop + preview) ----
  function setPreviewImage(src){
    $("#pv_img").attr("src", src).show();
    $("#pv_img_ph").hide();
    $("#rv_img").css("background-image", "url('" + src + "')");
  }
  function readFile(file){
    if(!file){ return; }
    if(!/^image\//.test(file.type)){ pwAlert("El archivo debe ser una imagen (JPG o PNG)."); return; }
    var r = new FileReader();
    r.onload = function(e){ setPreviewImage(e.target.result); };
    r.readAsDataURL(file);
    $("#pw_file_name").text(file.name);
  }
  $("#pw_drop").on("click", function(){ $("#pw_image").trigger("click"); });
  $("#pw_image").on("change", function(){ if(this.files && this.files[0]){ readFile(this.files[0]); } });
  var dz = $("#pw_drop")[0];
  if(dz){
    ["dragenter","dragover"].forEach(function(ev){ dz.addEventListener(ev, function(e){ e.preventDefault(); $("#pw_drop").addClass("pw-drag"); }); });
    ["dragleave","drop"].forEach(function(ev){ dz.addEventListener(ev, function(e){ e.preventDefault(); $("#pw_drop").removeClass("pw-drag"); }); });
    dz.addEventListener("drop", function(e){
      if(e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]){
        var f = e.dataTransfer.files[0];
        var dt = new DataTransfer(); dt.items.add(f);
        $("#pw_image")[0].files = dt.files;
        readFile(f);
      }
    });
  }

  // ---- Resumen del paso 4 ----
  function renderReview(){
    $("#rv_name").text($.trim($("#pw_name").val()) || "—");
    var catTxt = $("#pw_cat option:selected").text();
    $("#rv_cat").text(catTxt && catTxt.indexOf("--") !== 0 ? catTxt : "Sin categoría");
    var base = pwNum($("#pw_price").val());
    var offer = pwNum($("#pw_offer_price").val());
    $("#rv_price").text(offer > 0 ? (pwMoney(offer) + "  (oferta)") : pwMoney(base));
  }

  // ---- Aviso si queda oculto + confirmación al guardar ----
  function pwRefreshWarn(){
    var vis = $("input[name='is_public']").is(":checked");
    var exi = $("input[name='in_existence']").is(":checked");
    $("#pw_inactive_warn").toggleClass("d-none", vis && exi);
  }
  $("input[name='is_public'], input[name='in_existence']").on("change", pwRefreshWarn);

  $("#pw_form").on("submit", function(e){
    if(PW_STEP !== PW_LAST){
      e.preventDefault();
      goToStep(PW_LAST);
      return;
    }
    var vis = $("input[name='is_public']").is(":checked");
    var exi = $("input[name='in_existence']").is(":checked");
    if(vis && exi){ return; }
    e.preventDefault();
    var msgs = [];
    if(!vis) msgs.push("no est\u00e1 visible en el men\u00fa");
    if(!exi) msgs.push("no est\u00e1 en existencia");
    var txt = msgs.join(" y ");
    var self = this;
    var go = function(){ self.submit(); };
    if(!window.Swal){ if(confirm("\u00bfEl producto " + txt + ".\n\n\u00bfDeseas guardarlo de todas formas?")){ go(); } return; }
    Swal.fire({
      title: "\u00bfProducto inactivo?",
      html: "El producto <b>" + txt + "</b>.<br>No ser\u00e1 visible para los clientes.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Guardar de todas formas",
      cancelButtonText: "Cancelar y revisar",
      confirmButtonColor: "#1a0004",
      cancelButtonColor: "#000000"
    }).then(function(r){ if(r.isConfirmed){ go(); } });
  });

  // ---- Arranque ----
  goToStep(1);
  applyCatFields($("#pw_cat").val());
  pwSyncSedes();
  pwRefreshWarn();
  pvRender();
  <?php if($url_exists): ?>setPreviewImage(<?php echo json_encode($pv_img_src); ?>);<?php endif; ?>
});
</script>
<?php endif; ?>
