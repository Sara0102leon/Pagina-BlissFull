<?php 
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$img_default = ConfigurationData::getByPreffix("general_img_default")?ConfigurationData::getByPreffix("general_img_default")->val:"assets/img/default.png";

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
<div class="row g-2">
  <?php foreach($products as $p):
  $img = "admin/storage/products/".$p->image;
  if($p->image=="" || !file_exists($img)){ $img=$img_default; }
  ?>
  <div class="col-6 col-sm-4 col-md-3 col-xl-custom-8">
    <div class="card card-stacked shadow-sm h-100 overflow-hidden border-0 product-card">
       <div class="position-relative">
          <img src="<?php echo $img; ?>" class="card-img-top" style="height: 140px; object-fit: cover;">
       </div>
       <div class="card-body p-2 d-flex flex-column">
          <h3 class="card-title h4 mb-1 fw-bold text-truncate"><?php echo htmlspecialchars($p->name); ?></h3>
          <p class="text-muted extra-small mb-2 text-truncate-2" style="height: 2.8em;"><?php echo substr(strip_tags($p->description),0,40); ?></p>
          
          <div class="mt-auto pt-2">
            <div class="h4 fw-bold text-primary mb-2 text-center"><?php echo $coin_symbol.number_format($p->price,2); ?></div>
            <button type="button" class="btn btn-primary w-100 py-2 rounded-0 shadow-sm fw-bold border-0" onclick="addToCart(<?php echo $p->id; ?>)">
              AGREGAR <i class="bi bi-plus-lg ms-1"></i>
            </button>
          </div>
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
