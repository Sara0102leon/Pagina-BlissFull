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

if($cat_id>0){
  $products = ProductData::getPublicsByCategoryId($cat_id);
} else if($query!=""){
  $products = ProductData::getLike($query);
} else {
  $products = ProductData::getFeatureds();
}
?>

<?php if(count($products)>0):?>
<div class="row g-4">
  <?php foreach($products as $p):
  $img = "admin/storage/products/".$p->image;
  if($p->image=="" || !file_exists($img)){ $img=$img_default; }
  $extras = ProductExtraData::getByProductId($p->id);
  $extras_json = array();
  foreach($extras as $e){ $extras_json[] = array("name"=>$e->name,"price"=>floatval($e->price)); }
  $extras_json_str = htmlspecialchars(json_encode($extras_json), ENT_QUOTES);
  ?>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="tt-product-card">
      <div class="pc-img-wrap">
        <img src="<?php echo $img; ?>" class="pc-img" alt="<?php echo htmlspecialchars($p->name); ?>" loading="lazy">
      </div>
      <div class="pc-body">
        <h3 class="pc-name" title="<?php echo htmlspecialchars($p->name); ?>"><?php echo htmlspecialchars($p->name); ?></h3>
        <div class="pc-meta">
          <span class="pc-price-pill"><?php echo $coin_symbol.number_format($p->price,2,".",","); ?></span>
          <?php if($bcv_rate>0): ?>
          <span class="pc-price-bs">≈ <?php echo $bs_symbol.number_format($p->price*$bcv_rate,2,".",","); ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="pc-footer">
        <?php if(count($extras_json)>0): ?>
        <button type="button" class="pc-add-btn" onclick="openExtrasModal(<?php echo $p->id; ?>, '<?php echo addslashes($p->name); ?>', '<?php echo $extras_json_str; ?>')" title="Agregar al carrito" aria-label="Agregar al carrito">
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