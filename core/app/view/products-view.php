<?php 
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$img_default = ConfigurationData::getByPreffix("general_img_default")?ConfigurationData::getByPreffix("general_img_default")->val:"assets/img/default.png";

if(isset($_GET["opt"]) && $_GET["opt"]=="all"):
  $products = [];
  $title = "Productos";
  if(isset($_GET["cat"])){
    $cat = CategoryData::getByPreffix($_GET["cat"]);
    if($cat){
      $products = ProductData::getPublicsByCategoryId($cat->id);
      $title = "Categoría: ".$cat->name;
    }
  }else if(isset($_GET["q"])){
    $products = ProductData::getLike($_GET["q"]);
    $title = "Búsqueda: ".$_GET["q"];
  }else if(isset($_GET["filter"])){
    if($_GET["filter"]=="news") { $products = ProductData::getNews(); $title = "Novedades"; }
    else if($_GET["filter"]=="offers") { $products = ProductData::getOffers(); $title = "Ofertas"; }
  }else{
    $products = ProductData::getAll(); // O getPublics si existe
  }
?>
<div class="page-body">
  <div class="container-xl">
    <div class="row g-4">
      <div class="col-md-3 d-none d-md-block">
        <div class="card shadow-sm border-0 bg-white sticky-top" style="top: 100px;">
          <div class="card-header border-0 bg-white"><h3 class="card-title fw-bold">Categorías</h3></div>
          <div class="list-group list-group-flush">
            <a href="./?view=products&opt=all" class="list-group-item list-group-item-action d-flex align-items-center border-0 px-4 py-3 <?php echo !isset($_GET['cat'])?'bg-primary-lt text-primary fw-bold':''; ?>">
              <i class="bi bi-grid-fill me-2"></i> Todos los productos
            </a>
            <?php foreach(CategoryData::getPublics() as $cat): ?>
            <a href="./?view=products&opt=all&cat=<?php echo $cat->short_name; ?>" 
               class="list-group-item list-group-item-action d-flex align-items-center border-0 px-4 py-3 <?php echo (isset($_GET['cat']) && $_GET['cat']==$cat->short_name)?'bg-primary-lt text-primary fw-bold':''; ?>">
              <i class="bi bi-tag-fill me-2"></i> <?php echo htmlspecialchars($cat->name); ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="col-md-9">
        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $title; ?></li>
          </ol>
        </nav>
        <h2 class="h1 mb-4 fw-bold"><?php echo $title; ?></h2>
        
        <?php if(count($products)>0):?>
        <div class="row g-3">
          <?php foreach($products as $p):
          $img = "admin/storage/products/".$p->image;
          if($p->image=="" || !file_exists($img)){ $img=$img_default; }
          $in_cart=false;
          if(isset($_SESSION["cart"])){
            foreach ($_SESSION["cart"] as $pc) {
              if($pc["product_id"]==$p->id){ $in_cart=true;  }
            }
          }
          ?>
          <div class="col-6 col-sm-6 col-md-4 col-lg-4">
            <div class="card card-stacked shadow-sm h-100 overflow-hidden border-0">
               <div class="position-relative">
                  <a href="./?view=products&opt=open&id=<?php echo $p->id; ?>">
                    <img src="<?php echo $img; ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                  </a>
                  <?php if(ProductData::offerActive($p)): ?>
                  <span class="badge bg-danger position-absolute top-0 end-0 m-2 rounded-pill px-2 py-1 small">OFERTA</span>
                  <?php endif; ?>
               </div>
               <div class="card-body p-3">
                  <h3 class="card-title h4 mb-2 fw-bold text-truncate"><?php echo htmlspecialchars($p->name); ?></h3>
                  <p class="text-muted small mb-3 text-truncate-2"><?php echo substr(strip_tags($p->description),0,60); ?>...</p>
                  
                  <?php if(ProductData::offerActive($p)): ?>
                    <div class="d-flex align-items-center justify-content-between mt-auto">
                      <div class="h3 mb-0">
                        <span class="price-badge"><?php echo $coin_symbol." ".number_format(ProductData::getEffectivePrice($p),2,".",","); ?></span>
                        <small class="text-muted text-decoration-line-through ms-2"><?php echo $coin_symbol." ".number_format($p->price,2,".",","); ?></small>
                      </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-between mt-auto">
                      <div class="price-badge h3 mb-0"><?php echo $coin_symbol." ".number_format($p->price,2,".",","); ?></div>
                    <?php endif; ?>
                    <div class="d-flex gap-1">
                      <?php if($p->in_existence):?>
                        <?php if(!$in_cart):?>
                          <a href="./?action=cart&opt=add&product_id=<?php echo $p->id; ?>&href=cat&cat=<?php echo isset($_GET['cat'])?$_GET['cat']:''; ?>" class="btn btn-primary btn-icon rounded-circle shadow-sm">
                            <i class="bi bi-plus-lg"></i>
                          </a>
                        <?php else:?>
                          <button class="btn btn-success btn-icon rounded-circle shadow-sm disabled">
                            <i class="bi bi-check-lg"></i>
                          </button>
                        <?php endif; ?>
                      <?php else:?>
                         <span class="text-danger small fw-bold">Agotado</span>
                      <?php endif; ?>
                    </div>
                  </div>
               </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else:?>
        <div class="card border-0 bg-light py-5">
          <div class="card-body text-center">
            <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
            <h3 class="mt-3">No se encontraron productos</h3>
            <p class="text-muted">Prueba con otra categoría o término de búsqueda.</p>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="open"):
$p = ProductData::getById($_GET["id"]);
?>
<div class="page-body">
  <div class="container-xl">
    <div class="row g-4 justify-content-center">
      <div class="col-md-10">
        <?php if($p):
        $img = "admin/storage/products/".$p->image;
        if($p->image=="" || !file_exists($img)){ $img=$img_default; }
        $in_cart=false;
        if(isset($_SESSION["cart"])){
          foreach ($_SESSION["cart"] as $pc) {
            if($pc["product_id"]==$p->id){ $in_cart=true;  }
          }
        }
        ?>
        <div class="card border-0 shadow-lg overflow-hidden">
          <div class="row g-0">
            <div class="col-md-6 bg-light d-flex align-items-center justify-content-center p-4">
              <img src="<?php echo $img; ?>" class="img-fluid rounded shadow-sm" style="max-height: 450px; object-fit: contain;">
            </div>
            <div class="col-md-6 p-4 p-md-5 d-flex flex-column">
              <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="./">Inicio</a></li>
                  <li class="breadcrumb-item"><a href="./?view=products&opt=all">Menú</a></li>
                  <li class="breadcrumb-item active"><?php echo htmlspecialchars($p->name); ?></li>
                </ol>
              </nav>
              
              <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($p->name); ?></h1>
              <div class="price-badge h1 mb-4"><?php echo $coin_symbol." ".number_format($p->price,2,".",","); ?></div>
              
              <div class="mb-4">
                <?php if($p->in_existence):?>
                <div class="d-flex align-items-center text-success fw-bold">
                   <span class="avatar avatar-xs bg-success text-white rounded-circle me-2"><i class="bi bi-check"></i></span>
                   Disponible para ordenar
                </div>
                <?php else:?>
                <div class="d-flex align-items-center text-warning fw-bold">
                   <span class="avatar avatar-xs bg-warning text-white rounded-circle me-2"><i class="bi bi-dash"></i></span>
                   Temporalmente agotado
                </div>
                <?php endif; ?>
              </div>

              <div class="mb-5">
                <h4 class="fw-bold mb-3 text-uppercase small tracking-widest text-muted">Descripción</h4>
                <p class="text-muted fs-4 leading-relaxed">
                  <?php echo nl2br(htmlspecialchars($p->description)); ?>
                </p>
              </div>

              <div class="mt-auto d-grid gap-3">
                <?php if(!$p->in_existence):?>
                  <button class="btn btn-warning btn-pill btn-lg disabled py-3 fw-bold">No Disponible</button>
                <?php elseif(!$in_cart):?>
                  <a href="./?action=cart&opt=add&product_id=<?php echo $p->id; ?>&href=open&id=<?php echo $p->id;?>" class="btn btn-primary btn-pill btn-lg py-3 fw-bold shadow-sm">
                    <i class="bi bi-plus-circle me-2"></i> Agregar a mi Orden
                  </a>
                <?php else:?>
                  <div class="d-flex flex-column gap-2">
                    <button class="btn btn-success btn-pill btn-lg py-3 fw-bold disabled w-100">
                      <i class="bi bi-check2-all me-2"></i> Agregado correctamente
                    </button>
                    <a href="./?view=cart&opt=all" class="btn btn-outline-primary btn-pill btn-lg py-3 fw-bold">Ver mi Orden</a>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="mt-4 pt-4 border-top">
                <div class="row g-2">
                  <div class="col-6">
                    <div class="small text-muted">SKU: <?php echo $p->code; ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Related / Featured at bottom -->
        <div class="mt-5">
           <h3 class="fw-bold mb-4 h1 text-center">Te podría gustar también</h3>
           <div class="row g-3">
              <?php foreach(ProductData::getFeatureds() as $index => $rf): if($index > 3 || $rf->id == $p->id) continue; 
                $rimg = "admin/storage/products/".$rf->image;
                if($rf->image=="" || !file_exists($rimg)){ $rimg=$img_default; }
              ?>
              <div class="col-6 col-md-3">
                 <div class="card border-0 shadow-sm overflow-hidden h-100">
                    <a href="./?view=products&opt=open&id=<?php echo $rf->id; ?>">
                       <img src="<?php echo $rimg; ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                    </a>
                    <div class="card-body p-2 text-center">
                       <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($rf->name); ?></h5>
                       <div class="text-primary small fw-bold"><?php echo $coin_symbol." ".number_format($rf->price,2); ?></div>
                    </div>
                 </div>
              </div>
              <?php endforeach; ?>
           </div>
        </div>
        
        <?php else: ?>
        <div class="alert alert-important alert-danger">
          <div class="d-flex">
            <div><i class="bi bi-alert-triangle me-2"></i></div>
            <div>Lo sentimos, este platillo no está disponible actualmente.</div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

</div>
<?php endif; ?>
