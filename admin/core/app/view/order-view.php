<?php
if(!isset($_SESSION["user_id"])){ Core::redir("./"); }
$user = UserData::getById($_SESSION["user_id"]);
if($user == null){ Core::redir("./"); }
$categories = CategoryData::getActives();
$total_prods = 0;
foreach($categories as $c){ $total_prods += count(ProductData::getActivesByCategory($c->id)); }
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Orden del menú</h2>
        <div class="text-secondary mt-1">
          Arrastra los elementos para ordenar el menú. Se guarda automáticamente.
          También puedes enfocar un elemento y usar las flechas ↑/↓ del teclado.
        </div>
      </div>
      <div class="col-auto">
        <span class="order-saved" id="orderSaved" role="status" aria-live="polite"></span>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <?php if(count($categories) == 0): ?>
      <div class="alert alert-warning mb-0">No hay categorías todavía. Crea una para empezar a ordenar el menú.</div>
    <?php else: ?>
    <div class="row g-4">
      <!-- Orden de categorías -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-status-top bg-primary"></div>
          <div class="card-header">
            <h3 class="card-title">Categorías</h3>
          </div>
          <div class="card-body">
            <ul class="order-list" id="orderCats" aria-label="Orden de las categorías">
              <?php foreach($categories as $cat): ?>
              <li class="order-item" data-id="<?php echo (int)$cat->id; ?>" tabindex="0">
                <span class="order-grip" title="Arrastrar"><i class="bi bi-grip-vertical"></i></span>
                <span class="order-name"><?php echo htmlspecialchars($cat->name); ?></span>
                <a class="order-link" href="./?view=products&opt=all" title="Ver productos"><i class="bi bi-box-seam"></i></a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <!-- Orden de productos por categoría -->
      <div class="col-lg-8">
        <div class="card mb-3">
          <div class="card-body py-2 text-secondary">
            <i class="bi bi-info-circle me-1"></i>
            El orden de las categorías y de los productos se refleja directamente en el menú público.
            <span class="ms-1">(<?php echo (int)$total_prods; ?> productos en total)</span>
          </div>
        </div>

        <?php foreach($categories as $cat): $prods = ProductData::getActivesByCategory($cat->id); ?>
        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title"><?php echo htmlspecialchars($cat->name); ?></h3>
            <div class="card-actions text-secondary small">
              <?php echo count($prods); ?> producto<?php echo count($prods) == 1 ? "" : "s"; ?>
            </div>
          </div>
          <div class="card-body">
            <?php if(count($prods) > 0): ?>
            <ul class="order-list order-prods" data-cat="<?php echo (int)$cat->id; ?>"
                aria-label="Orden de los productos de <?php echo htmlspecialchars($cat->name); ?>">
              <?php foreach($prods as $p): ?>
              <li class="order-item" data-id="<?php echo (int)$p->id; ?>" tabindex="0">
                <span class="order-grip" title="Arrastrar"><i class="bi bi-grip-vertical"></i></span>
                <span class="order-name">
                  <?php echo htmlspecialchars($p->name); ?>
                  <?php if(!$p->is_public): ?><span class="order-badge">oculto</span><?php endif; ?>
                </span>
              </li>
              <?php endforeach; ?>
            </ul>
            <?php else: ?>
              <p class="text-secondary mb-0">Sin productos activos en esta categoría.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="assets/js/admin-order.js?v=1"></script>
