<?php
// Partial: filas <tbody> de la tabla de ventas (opción "all").
// Se usa tanto en sells-view.php (render server-side) como en sells-action.php (AJAX opt=rows).
// Espera en scope: $buys, $pending_map, $coin
if(!isset($buys) || !is_array($buys)){ $buys = BuyData::getAll(); }
if(!isset($pending_map)){ $pending_map = array(); }
if(!isset($coin)){ $coin = ConfigurationData::getByPreffix("general_coin")->val; }

foreach($buys as $b):
  $discount = 0;
  // $coupon handling skipped due to missing model
  ?>
  <tr>
    <td><a href="./?view=sells&opt=open&id=<?php echo $b->id; ?>" class="btn btn-sm btn-default">Detalles</a></td>
    <td>#<?php echo $b->id; ?>
      <?php if(intval($b->chatwoot_conversation_id)>0):?>
        <a href="<?php echo ChatwootData::baseUrl(); ?>/accounts/<?php echo ChatwootData::accountId(); ?>/conversations/<?php echo intval($b->chatwoot_conversation_id); ?>" target="_blank" rel="noopener" class="ms-1" title="Ver conversación en Chatwoot"><i class="bi bi-chat-dots text-success"></i></a>
      <?php endif; ?>
    </td>
    <td><?php echo $b->getClient()->getFullname(); ?></td>
    <td><?php $b_sede = $b->getSede(); echo $b_sede ? htmlspecialchars($b_sede->name) : '-'; ?></td>
    <td><?php echo $coin; ?> <?php echo number_format($b->getTotal()-$discount,2,".",","); ?></td>
    <td><?php echo $b->getPaymethod()->name; ?></td>
    <td>
      <?php if($b->status_id==1):?>
        <?php if(!empty($b->scheduled_at) && strtotime($b->scheduled_at) > time()):?>
          <span class="badge bg-info text-dark" title="Pedido programado"><i class="bi bi-calendar-check me-1"></i>Programado</span>
        <?php elseif(intval($pending_map[$b->id])>=30):?>
          <span class="badge bg-danger text-white" title="30+ minutos sin pago ni señales del cliente"><i class="bi bi-bell-fill me-1"></i>Sin señales de pago</span>
        <?php else:?>
          <span class="badge bg-warning text-dark">Pendiente</span>
        <?php endif;?>
      <?php else:?>
        <?php echo $b->getStatus()->name; ?>
      <?php endif;?>
    </td>
    <td><?php echo $b->created_at; ?></td>
    <td>
      <?php if(!empty($b->scheduled_at)): ?>
        <span class="badge bg-info-lt" title="Pedido programado"><i class="bi bi-clock me-1"></i><?php echo date("d/m/Y h:i A", strtotime($b->scheduled_at)); ?></span>
      <?php else: ?>
        <span class="text-muted">-</span>
      <?php endif; ?>
    </td>
    <td>
      <?php if($b->status_id==3):?>
        <span class="badge bg-danger text-white"><i class="bi bi-x-lg me-1"></i>Cancelado</span>
      <?php elseif($b->status_id!=5):?>
        <?php $is_pickup = ($b->delivery_zone_id=="" || strpos(strtolower($b->getClient()->address), "sucursal") !== false); ?>
        <?php $st = intval($b->status_id); ?>
        <div class="btn-list flex-nowrap">
          <button type="button" class="btn btn-info btn-sm btn-status-change <?php echo $st>=2?'disabled opacity-50':''; ?>" data-id="<?php echo $b->id;?>" data-status="2" title="Pagado" <?php echo $st>=2?'disabled':''; ?>><i class="bi bi-currency-dollar"></i></button>
          <?php if(!$is_pickup): ?>
          <button type="button" class="btn btn-success btn-sm btn-status-change <?php echo $st!=2?'disabled opacity-50':''; ?>" data-id="<?php echo $b->id;?>" data-status="4" title="Enviado (requiere marcarlo como pagado antes)" <?php echo $st!=2?'disabled':''; ?>><i class="bi bi-truck"></i></button>
          <?php endif; ?>
          <button type="button" class="btn btn-primary btn-sm btn-status-change <?php echo ($st!=2 && $st!=4)?'disabled opacity-50':''; ?>" data-id="<?php echo $b->id;?>" data-status="5" title="Finalizado (requiere pagado)" <?php echo ($st!=2 && $st!=4)?'disabled':''; ?>><i class="bi bi-check-lg"></i></button>
          <button type="button" class="btn btn-danger btn-sm btn-status-change <?php echo $st>=2?'disabled opacity-50':''; ?>" data-id="<?php echo $b->id;?>" data-status="3" title="Cancelar (solo si aún no ha pagado)" <?php echo $st>=2?'disabled':''; ?>><i class="bi bi-x-lg"></i></button>
        </div>
      <?php elseif($b->status_id==5):?>
        <span class="badge bg-success text-white"><i class="bi bi-check-lg me-1"></i>Finalizado</span>
      <?php else:?>
        <i class="bi bi-check-lg text-success"></i>
      <?php endif;?>
    </td>
  </tr>
<?php endforeach; ?>
