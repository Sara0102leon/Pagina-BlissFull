<?php 
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

<?php if(count($products)>0):?>
<div class="row g-4">
  <?php foreach($products as $p):
  $img = "admin/storage/products/".$p->image;
  if($p->image=="" || !file_exists($img)){ $img=$img_default; }
  $pizza_edit_json = array("desc"=>trim((string)$p->description),"free"=>intval($p->free_ingredients),"ingredients"=>array(),"extras"=>array());
  $no_edit_cats = array(5,6); // Pastas y Focaccia: por ahora sin extras/ingredientes
  if(!in_array(intval($p->category_id), $no_edit_cats)){
    $extras = ProductExtraData::getByProductId($p->id);
    foreach($extras as $e){
      $item = array("name"=>$e->name,"price"=>floatval($e->price));
      if(intval($e->is_ingredient)==1){ $pizza_edit_json["ingredients"][] = $item; }
      else { $pizza_edit_json["extras"][] = $item; }
    }
  }
  $has_edit = count($pizza_edit_json["ingredients"])>0 || count($pizza_edit_json["extras"])>0;
  $pizza_edit_json_str = htmlspecialchars(json_encode($pizza_edit_json), ENT_QUOTES);
  ?>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="tt-product-card">
      <div class="pc-img-wrap">
        <img src="<?php echo $img; ?>" class="pc-img" alt="<?php echo htmlspecialchars($p->name); ?>" loading="lazy">
      </div>
      <div class="pc-body">
        <h3 class="pc-name" title="<?php echo htmlspecialchars($p->name); ?>"><?php echo htmlspecialchars($p->name); ?></h3>
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
<div class="text-center py-5">
   <i class="bi bi-search h1 text-muted"></i>
   <p class="h3">No encontramos productos.</p>
</div>
<?php endif; ?>