<?php 
require_once __DIR__."/../helpers/product-extras-helper.php";
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$img_default = ConfigurationData::getByPreffix("general_img_default")?ConfigurationData::getByPreffix("general_img_default")->val:"assets/img/default.png";
$bcv_rate = 0;
$bcv_row = ConfigurationData::getByPreffix("bcv_rate");
if($bcv_row && $bcv_row->val){ $bcv_rate = floatval($bcv_row->val); }
$bs_symbol = "Bs";

$cat_id = 0;
if(isset($_POST["cat_id"]) && $_POST["cat_id"]!=""){
  $cat_id = intval($_POST["cat_id"]);
}

$query = "";
if(isset($_POST["q"])){ $query = $_POST["q"]; }

$sede_id = 0;
if(isset($_POST["sede_id"]) && $_POST["sede_id"]!=""){ $sede_id = intval($_POST["sede_id"]); }

if($cat_id>0){
  $products = ProductData::getPublicsByCategoryId($cat_id,$sede_id);
} else if($query!=""){
  $products = ProductData::getLike($query,$sede_id);
} else {
  $products = ProductData::getFeatureds($sede_id);
}
?>

<?php
$aviso_sede = null;
$aviso_cat_nombre = "";
if($cat_id>0 && $sede_id>0 && in_array($cat_id, array(5,6))){
  $q2 = Executor::doit("select sede_id from product where category_id=$cat_id and is_public=1 and is_active=1 and sede_id is not null limit 1");
  $r2 = Model::one($q2[0], new ProductData());
  if($r2 && intval($r2->sede_id)>0 && $r2->sede_id!=$sede_id){
    $aviso_sede = SedeData::getById($r2->sede_id);
    $aviso_cat_nombre = ($cat_id==5) ? "Pastas" : "Focaccias";
  }
}
?>

<?php if(count($products)>0):?>
<div class="row g-4">
  <?php foreach($products as $p):
  $img = "admin/storage/products/".$p->image;
  if($p->image=="" || !file_exists($img)){ $img=$img_default; }
  $pizza_edit_json = array("desc"=>trim((string)$p->description),"free"=>intval($p->free_ingredients),"division"=>trim((string)$p->tipo_division),"sabores"=>array(),"ingredients"=>array(),"extras"=>array(),"sel"=>array(),"main"=>-1);
  $no_edit_cats = array(5,6); // Pastas y Focaccia: por ahora sin extras/ingredientes
  if(!in_array(intval($p->category_id), $no_edit_cats)){
    $extras = ProductExtraData::getByProductId($p->id);
    if(trim((string)$p->tipo_division)!=""){
      // Estaciones: modal de sabores completos de pizza gigante
      $pizza_edit_json["sabores"] = tt_build_sabores($sede_id);
    } else {
      // Pizza normal: ingredientes de la casa detectados por la descripción
      $pizza_edit_json = tt_build_extras_payload($p->description, $p->free_ingredients, $extras, $p->house_ingredients);
      $pizza_edit_json["division"] = trim((string)$p->tipo_division);
    }
  }
  $has_edit = count($pizza_edit_json["ingredients"])>0 || count($pizza_edit_json["extras"])>0 || count($pizza_edit_json["sabores"])>0;
  $pizza_edit_json_str = htmlspecialchars(json_encode($pizza_edit_json), ENT_QUOTES);
  ?>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="tt-product-card">
      <div class="pc-img-wrap">
        <img src="<?php echo $img; ?>" class="pc-img" alt="<?php echo htmlspecialchars($p->name); ?>" loading="lazy">
      </div>
      <div class="pc-body">
        <h3 class="pc-name" title="<?php echo htmlspecialchars($p->name); ?>"><?php echo htmlspecialchars($p->name); ?></h3>
        <?php if(intval($p->sede_id)>0):
        $p_sede = SedeData::getById($p->sede_id); ?>
        <div class="pc-sede-note"><i class="bi bi-geo-alt-fill me-1"></i>Solo disponible en <?php echo htmlspecialchars($p_sede?$p_sede->name:"la sede"); ?></div>
        <?php endif; ?>
        <div class="pc-meta">
          <?php $tt_offer = ProductData::offerActive($p); ?>
          <?php if($tt_offer): ?>
          <?php $show_price = floatval($p->offer_price); $tt_old_price = ($p->price_llevar!="" && floatval($p->price_llevar)>0) ? floatval($p->price_llevar) : floatval($p->price); ?>
          <span class="pc-price-pill"><?php echo $coin_symbol.number_format($show_price,2,".",","); ?> <i class="bi bi-fire text-danger"></i></span>
          <span class="pc-price-old">antes <?php echo $coin_symbol.number_format($tt_old_price,2,".",","); ?></span>
          <?php else: ?>
          <?php $show_price = ($p->price_llevar!="" && floatval($p->price_llevar)>0) ? floatval($p->price_llevar) : floatval($p->price); ?>
          <span class="pc-price-pill"><?php echo $coin_symbol.number_format($show_price,2,".",","); ?></span>
          <?php if($p->price_llevar!="" && floatval($p->price_llevar)>0): ?>
          <span class="pc-price-dine">comer en la sede <?php echo $coin_symbol.number_format(floatval($p->price),2,".",","); ?></span>
          <?php endif; ?>
          <?php endif; ?>
          <?php if($bcv_rate>0): ?>
          <span class="pc-price-bs">≈ <?php echo $bs_symbol.number_format($show_price*$bcv_rate,2,".",","); ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="pc-footer">
        <?php if($has_edit): ?>
        <button type="button" class="pc-add-btn" onclick="openExtrasModal(<?php echo $p->id; ?>, '<?php echo addslashes($p->name); ?>', '<?php echo $pizza_edit_json_str; ?>')" title="Agregar al carrito" aria-label="Agregar al carrito">
          <i class="bi bi-plus-lg"></i>
        </button>
        <?php else: ?>
        <button type="button" class="pc-add-btn" onclick="addToCart(<?php echo $p->id; ?>, '<?php echo addslashes($p->name); ?>', '[]')" title="Agregar al carrito" aria-label="Agregar al carrito">
          <i class="bi bi-plus-lg"></i>
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php else:?>
<?php if($aviso_sede): ?>
<div class="tt-sede-aviso text-center px-3 py-5">
  <div class="tt-sede-aviso-icon mb-2"><i class="bi bi-shop"></i></div>
  <h3 class="h2 tt-display mb-2">SOLO EN <span class="text-gold"><?php echo htmlspecialchars($aviso_sede->name); ?></span></h3>
  <p class="text-muted mb-2">Las <?php echo htmlspecialchars(mb_strtolower($aviso_cat_nombre)); ?> se preparan únicamente en la sede <b><?php echo htmlspecialchars($aviso_sede->name); ?></b>. Para pedirlas, cambia tu sede:</p>
  <button type="button" class="btn btn-warning rounded-pill fw-bold px-4 py-2" onclick="switchSedeNow(<?php echo intval($aviso_sede->id); ?>)">CAMBIAR A <?php echo htmlspecialchars(mb_strtoupper($aviso_sede->name)); ?></button>
</div>
<?php else: ?>
<div class="text-center py-5">
   <i class="bi bi-search h1 text-muted"></i>
   <p class="h3">No encontramos productos.</p>
</div>
<?php endif; ?>
<?php endif; ?>