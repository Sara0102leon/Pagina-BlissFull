<?php
/* Parcial: pedidos listos para repartir (Repartidor).
   Espera $queue (BuyData[] con status 4). */
if(!isset($queue)){ $queue = array(); }
$count = count($queue);
?>
<div class="repartidor-stats d-flex align-items-center gap-2 mb-3 flex-wrap">
  <span class="badge bg-info-lt text-info" style="font-size:0.8rem;"><i class="bi bi-bicycle me-1"></i><?php echo $count; ?> listo<?php echo $count===1?"":"s"; ?> para repartir</span>
  <span class="small text-secondary">Actualizado <?php echo date("H:i:s"); ?></span>
</div>
<?php if($count === 0): ?>
  <div class="text-center py-5 text-secondary">
    <i class="bi bi-truck d-block" style="font-size:3rem;opacity:.35;"></i>
    <p class="mt-2 mb-0">No hay entregas pendientes en este momento.</p>
  </div>
<?php else: ?>
  <div class="row g-3">
  <?php foreach($queue as $b):
    $cl = ($b->client_name !== "") ? $b->client_name : ($b->getClient() ? $b->getClient()->name : "—");
    $ph = preg_replace('/[^0-9]/', '', $b->client_phone);
    $zone = $b->getDeliveryZone();
    $dir  = $b->maps ? htmlspecialchars($b->maps, ENT_QUOTES, "UTF-8") : "";
    $is_prog = ($b->scheduled_at && $b->scheduled_at != "" && $b->scheduled_at != "0000-00-00 00:00:00");
    $gmaps_query = ($b->lat && $b->lng) ? urlencode($b->lat.",".$b->lng) : urlencode($b->maps ? $b->maps : $cl);
  ?>
    <div class="col-md-6 col-xl-4">
      <div class="card shadow-sm h-100">
        <div class="card-status-top bg-info"></div>
        <div class="card-header d-flex align-items-center">
          <div class="me-auto">
            <span class="fw-bold h4 mb-0 text-info">#<?php echo htmlspecialchars($b->code, ENT_QUOTES, "UTF-8"); ?></span>
            <div class="small text-secondary">
              <?php echo date("H:i", strtotime($b->created_at)); ?>
              <?php if($is_prog): ?> · <span class="badge bg-info-lt text-info">Programado <?php echo date("H:i", strtotime($b->scheduled_at)); ?></span><?php endif; ?>
            </div>
          </div>
          <span class="badge bg-cyan-lt text-cyan">Enviado</span>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <i class="bi bi-person-circle text-secondary me-2"></i>
            <span class="fw-semibold"><?php echo htmlspecialchars($cl, ENT_QUOTES, "UTF-8"); ?></span>
            <?php if($ph): ?><a href="https://wa.me/<?php echo $ph; ?>" target="_blank" class="ms-auto small text-primary" title="Escribir por WhatsApp"><i class="bi bi-whatsapp"></i></a><?php endif; ?>
          </div>
          <div class="small text-secondary mb-1"><i class="bi bi-telephone me-2"></i><?php echo $ph ? $ph : "Sin teléfono"; ?></div>
          <div class="small text-secondary mb-1"><i class="bi bi-geo-alt me-2"></i><?php echo $zone ? htmlspecialchars($zone->name, ENT_QUOTES, "UTF-8") : "Recoger en local"; ?></div>
          <?php if($dir): ?><div class="small text-secondary mb-1"><i class="bi bi-signpost me-2"></i><?php echo $dir; ?></div><?php endif; ?>
          <?php if($b->distance_km): ?><div class="small text-secondary mb-1"><i class="bi bi-rulers me-2"></i>~<?php echo number_format(floatval($b->distance_km), 1); ?> km</div><?php endif; ?>
          <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="https://www.google.com/maps?q=<?php echo $gmaps_query; ?>" target="_blank" class="btn btn-outline-primary btn-sm flex-fill"><i class="bi bi-map me-1"></i>Abrir mapa</a>
            <?php if($ph): ?><a href="https://wa.me/<?php echo $ph; ?>" target="_blank" class="btn btn-outline-success btn-sm"><i class="bi bi-whatsapp"></i></a><?php endif; ?>
          </div>
          <?php if($b->note && $b->note != ""): ?>
          <div class="alert alert-warning py-2 px-2 mt-2 mb-0 small"><i class="bi bi-sticky me-1"></i><?php echo htmlspecialchars($b->note, ENT_QUOTES, "UTF-8"); ?></div>
          <?php endif; ?>
        </div>
        <div class="card-footer">
          <a href="./?action=pedidos&opt=entregar&id=<?php echo intval($b->id); ?>" class="btn btn-success btn-sm w-100">
            <i class="bi bi-check2-all me-1"></i>Entregado — finalizar pedido
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
<?php endif; ?>