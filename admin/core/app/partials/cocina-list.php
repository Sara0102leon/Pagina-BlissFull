<?php
/* Parcial: cola de pedidos por preparar (Cocina).
   Espera $queue (BuyData[] con status 1|2, ordenados más antiguos primero). */
if(!isset($queue)){ $queue = array(); }
$empty_text = "La cocina está al día. Cuando entre un pedido nuevo lo verás aquí.";
$count = count($queue);
?>
<div class="cocina-stats d-flex align-items-center gap-2 mb-3 flex-wrap">
  <span class="badge bg-primary-lt text-primary" style="font-size:0.8rem;"><i class="bi bi-fire me-1"></i><?php echo $count; ?> por preparar</span>
  <span class="small text-secondary">Actualizado <?php echo date("H:i:s"); ?></span>
</div>
<?php if($count === 0): ?>
  <div class="text-center py-5 text-secondary">
    <i class="bi bi-check2-all d-block" style="font-size:3rem;opacity:.35;"></i>
    <p class="mt-2 mb-0"><?php echo $empty_text; ?></p>
  </div>
<?php else: ?>
  <div class="row g-3">
  <?php foreach($queue as $b):
    $st_items = BuyProductData::getAllByBuyId($b->id);
    $cl = ($b->client_name !== "") ? $b->client_name : ($b->getClient() ? $b->getClient()->name : "—");
    $ph = ($b->client_phone !== "") ? $b->client_phone : "";
    $zone = $b->getDeliveryZone();
    $sede = $b->getSede();
    $is_prog = ($b->scheduled_at && $b->scheduled_at != "" && $b->scheduled_at != "0000-00-00 00:00:00");
  ?>
    <div class="col-md-6 col-xl-4">
      <div class="card shadow-sm h-100 cocina-card">
        <div class="card-status-top bg-primary"></div>
        <div class="card-header d-flex align-items-center">
          <div class="me-auto">
            <span class="fw-bold h4 mb-0 text-primary">#<?php echo htmlspecialchars($b->code, ENT_QUOTES, "UTF-8"); ?></span>
            <div class="small text-secondary">
              <?php echo date("H:i", strtotime($b->created_at)); ?> ·
              <?php echo $is_prog
                ? '<span class="badge bg-info-lt text-info"><i class="bi bi-calendar-check me-1"></i>Programado '.date("H:i", strtotime($b->scheduled_at)).'</span>'
                : "Ahora"; ?>
            </div>
          </div>
          <span class="badge <?php echo intval($b->status_id)===1 ? "bg-warning-lt text-warning" : "bg-success-lt text-success"; ?>">
            <?php echo intval($b->status_id)===1 ? "Pendiente" : "Pagado"; ?>
          </span>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-person-circle text-secondary me-2"></i>
            <span class="fw-semibold"><?php echo htmlspecialchars($cl, ENT_QUOTES, "UTF-8"); ?></span>
            <?php if($ph): ?><a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $ph); ?>" target="_blank" class="ms-auto small text-primary" title="Escribir por WhatsApp"><i class="bi bi-whatsapp"></i></a><?php endif; ?>
          </div>
          <div class="d-flex align-items-center mb-2 small text-secondary">
            <i class="bi bi-geo-alt me-2"></i>
            <?php if($zone): ?><?php echo htmlspecialchars($zone->name, ENT_QUOTES, "UTF-8"); ?><?php else: ?>Recoger en local<?php endif; ?>
            <?php if($sede): ?> · <?php echo htmlspecialchars($sede->name, ENT_QUOTES, "UTF-8"); ?><?php endif; ?>
          </div>
          <hr class="my-2 opacity-10">
          <ul class="list-unstyled mb-0">
          <?php foreach($st_items as $bp):
            $p = $bp->getProduct();
            $pname = $p ? $p->name : "Producto #".$bp->product_id;
            $extra_txt = array();
            foreach($bp->getExtrasArray() as $e){ $extra_txt[] = $e["name"] . (isset($e["price"]) && floatval($e["price"])>0 ? " +$".number_format(floatval($e["price"]),2) : ""); }
            foreach($bp->getBebidasArray() as $e){ $extra_txt[] = $e["name"] . (isset($e["price"]) && floatval($e["price"])>0 ? " +$".number_format(floatval($e["price"]),2) : ""); }
          ?>
            <li class="d-flex gap-2">
              <span class="badge bg-primary-lt text-primary align-self-start" style="min-width:2rem;"><?php echo intval($bp->q); ?></span>
              <span>
                <?php echo htmlspecialchars($pname, ENT_QUOTES, "UTF-8"); ?>
                <?php if($extra_txt): ?><span class="d-block small text-secondary"><?php echo htmlspecialchars(implode(" · ", $extra_txt), ENT_QUOTES, "UTF-8"); ?></span><?php endif; ?>
              </span>
            </li>
          <?php endforeach; ?>
          </ul>
          <?php if($b->note && $b->note != ""): ?>
          <div class="alert alert-warning py-2 px-2 mt-2 mb-0 small"><i class="bi bi-sticky me-1"></i><?php echo htmlspecialchars($b->note, ENT_QUOTES, "UTF-8"); ?></div>
          <?php endif; ?>
        </div>
        <div class="card-footer d-flex align-items-center">
          <span class="text-secondary small">Total: <span class="fw-bold"><?php echo "$" . number_format($b->getTotal(), 2); ?></span></span>
          <a href="./?action=pedidos&opt=preparar&id=<?php echo intval($b->id); ?>" class="btn btn-primary btn-sm ms-auto">
            <i class="bi bi-check-lg me-1"></i>Listo para reparto
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
<?php endif; ?>