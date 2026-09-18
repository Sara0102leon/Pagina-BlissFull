<?php
$coin_symbol = ConfigurationData::getByPreffix("general_coin")?ConfigurationData::getByPreffix("general_coin")->val:"$";
$raw_code = isset($_GET["code"]) ? trim((string)$_GET["code"]) : "";
$code = preg_replace("/[^A-Za-z0-9_-]/","", $raw_code);
$buy = ($code!=="") ? BuyData::getByCode($code) : null;
$fmt = function($n){ return number_format(floatval($n), 2, ".", ","); };
?>
<section class="tt-section py-4">
  <div class="container-xl">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="text-center mb-4">
          <div class="tt-hand tt-hand-lg mb-1">Seguimiento</div>
          <h1 class="tt-title-underline mb-3">Seguir mi pedido</h1>
          <p class="text-muted mb-0">Ingresa el código de tu pedido para ver su estado y la ruta de entrega.</p>
        </div>

        <?php if($buy==null): ?>
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <form method="get" action="./">
                <input type="hidden" name="view" value="order-status">
                <label class="form-label fw-bold" for="os_code">Código del pedido</label>
                <input type="text" name="code" id="os_code" class="form-control text-center fw-bold" placeholder="Ej: aB3xY9_kLm1" value="<?php echo htmlspecialchars($code, ENT_QUOTES); ?>" maxlength="30">
                <button type="submit" class="btn btn-warning rounded-pill w-100 mt-3 fw-bold"><i class="bi bi-search me-1"></i> Ver estado</button>
              </form>
              <?php if($code!==""): ?>
                <div class="alert alert-warning small mt-3 mb-0"><i class="bi bi-exclamation-triangle me-1"></i> No encontramos ningún pedido con ese código. Revisa que esté bien escrito.</div>
              <?php endif; ?>
              <p class="small text-muted mt-3 mb-0 text-center">¿Recibiste un pedido y no tienes el código? Consulta en el chat de la conversación de tu pedido.</p>
            </div>
          </div>
        <?php else: ?>
          <?php $st = intval($buy->status_id); ?>
          <?php $sede = $buy->getSede(); ?>
          <?php $client = $buy->getClient(); ?>
          <?php $paymethod = $buy->getPaymethod(); ?>
          <?php $zone = $buy->getDeliveryZone(); ?>
          <?php $delivery = ($zone != null); ?>

          <?php if($st==3): ?>
            <div class="alert alert-danger d-flex align-items-center gap-3 rounded-4">
              <i class="bi bi-x-octagon-fill fs-3"></i>
              <div>
                <div class="fw-bold">Pedido cancelado</div>
                <div class="small">Tu pedido <b>#<?php echo htmlspecialchars($buy->code); ?></b> fue cancelado. Si fue un error, escríbenos por WhatsApp.</div>
              </div>
            </div>
          <?php endif; ?>

          <!-- Timeline -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Pedido #<?php echo htmlspecialchars($buy->code); ?></h2>
                <span class="badge rounded-pill text-bg-<?php echo $st==3?"danger":($st>=4?"success":"primary"); ?>">
                  <?php
                    if($st==1){ echo "En revisión"; }
                    elseif($st==2){ echo "Pago recibido"; }
                    elseif($st>=4){ echo "Enviado"; }
                    else { echo "Cancelado"; }
                  ?>
                </span>
              </div>
              <?php
                $steps = array("Revisión del pedido","Pago recibido","Enviado");
                $cur = $st==3 ? -1 : ($st==1 ? 0 : ($st==2 ? 1 : 2));
              ?>
              <div class="tt-timeline">
                <?php foreach($steps as $i=>$label): ?>
                  <div class="tt-timeline-step <?php echo ($cur==-1||$cur>=2)?"done":($i<$cur?"done":($i==$cur?"active":"")); ?>">
                    <div class="tt-timeline-dot">
                      <i class="bi <?php echo ($cur==-1||(($i<$cur)||($cur==2&&$i==2)))? "bi-check-lg" : ($i==$cur?"bi-circle-fill":"bi-circle"); ?>"></i>
                    </div>
                    <div class="tt-timeline-content">
                      <span class="tt-timeline-label"><?php echo $label; ?></span>
                      <?php if($i==2 && $cur==2): ?>
                        <span class="tt-timeline-sub"><?php echo $st==5?"Entregado":"Pedido en camino"; ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Ruta de entrega -->
          <?php if($delivery): ?>
          <?php
            $has_sede = ($sede && $sede->lat!="" && $sede->lng!="");
            $has_cli  = ($buy->lat!="" && $buy->lng!="");
            $embed_url = "";
            $open_url  = ($buy->maps!="" ? $buy->maps : "");
            if($has_cli){
              if($has_sede){
                $embed_url = "https://maps.google.com/maps?saddr=".trim($sede->lat).",".trim($sede->lng)."&daddr=".trim($buy->lat).",".trim($buy->lng)."&output=embed";
                if($open_url==""){ $open_url = "https://www.google.com/maps/dir/?api=1&origin=".trim($sede->lat).",".trim($sede->lng)."&destination=".trim($buy->lat).",".trim($buy->lng)."&travelmode=driving"; }
              }else{
                $embed_url = "https://maps.google.com/maps?q=".trim($buy->lat).",".trim($buy->lng)."&z=15&output=embed";
                if($open_url==""){ $open_url = "https://www.google.com/maps/search/?api=1&query=".trim($buy->lat).",".trim($buy->lng); }
              }
            }
          ?>
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
              <h2 class="h5 mb-1"><i class="bi bi-truck text-gold me-1"></i> Entrega a domicilio</h2>
              <p class="small text-muted mb-3"><?php echo htmlspecialchars($client ? $client->address : ""); ?></p>
              <?php if($embed_url!=""): ?>
                <div class="ratio ratio-4x3 rounded-4 overflow-hidden border shadow-sm mb-3">
                  <iframe src="<?php echo htmlspecialchars($embed_url, ENT_QUOTES); ?>" width="100%" height="100%" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Mapa de entrega"></iframe>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                  <a class="btn btn-warning rounded-pill px-4 fw-bold" href="<?php echo htmlspecialchars($open_url, ENT_QUOTES); ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i> Abrir en Google Maps</a>
                  <?php if(!empty($buy->distance_km)): ?>
                    <span class="small text-muted"><i class="bi bi-signpost-2 me-1"></i> Aproximadamente <?php echo $fmt($buy->distance_km); ?> km de distancia</span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <p class="small text-muted mb-0"><i class="bi bi-info-circle me-1"></i> El mapa se activa cuando tu pedido sea confirmado.</p>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Resumen del pedido -->
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h2 class="h5 mb-3">Resumen del pedido</h2>
              <dl class="row small mb-0">
                <dt class="col-5 text-muted fw-normal">Sucursal</dt>
                <dd class="col-7 text-end mb-2"><?php echo htmlspecialchars($sede?$sede->name:"-"); ?></dd>
                <dt class="col-5 text-muted fw-normal">Nombre</dt>
                <dd class="col-7 text-end mb-2"><?php echo htmlspecialchars($client?trim($client->name." ".$client->lastname):"-"); ?></dd>
                <dt class="col-5 text-muted fw-normal">Entrega</dt>
                <dd class="col-7 text-end mb-2"><?php echo $delivery ? ("A domicilio" . ($zone?", ".htmlspecialchars($zone->name):"")) : "En sucursal / para llevar"; ?></dd>
                <dt class="col-5 text-muted fw-normal">Método de pago</dt>
                <dd class="col-7 text-end mb-2"><?php echo htmlspecialchars($paymethod?$paymethod->name:"-"); ?></dd>
                <?php if(!empty($buy->scheduled_at)): ?>
                <dt class="col-5 text-muted fw-normal">Programado</dt>
                <dd class="col-7 text-end mb-2"><?php echo date("d/m/Y h:i A", strtotime($buy->scheduled_at)); ?></dd>
                <?php endif; ?>
                <dt class="col-5 text-muted fw-normal">Fecha</dt>
                <dd class="col-7 text-end mb-2"><?php echo $buy->created_at; ?></dd>
              </dl>
              <hr class="my-3">
              <ul class="list-unstyled small mb-3">
                <?php foreach(BuyProductData::getAllByBuyId($buy->id) as $bp): $p=$bp->getProduct(); if(!$p){continue;} ?>
                  <li class="d-flex justify-content-between py-1">
                    <span><b><?php echo intval($bp->q); ?>×</b> <?php echo htmlspecialchars($p->name); ?>
                      <?php $extras = $bp->getExtrasArray(); if(count($extras)>0): ?>
                        <span class="text-muted d-block ms-2"><?php foreach($extras as $e){ echo "· ".htmlspecialchars($e["name"])." "; } ?></span>
                      <?php endif; ?>
                      <?php $bebs = $bp->getBebidasArray(); if(count($bebs)>0): ?>
                        <span class="text-muted d-block ms-2"><?php foreach($bebs as $be){ echo "· ".htmlspecialchars(isset($be["sabor"])?$be["sabor"]:"")." ".htmlspecialchars($be["medida"])." ".htmlspecialchars(isset($be["sabor_elegido"])?$be["sabor_elegido"]:"")." "; } ?></span>
                      <?php endif; ?>
                    </span>
                  </li>
                <?php endforeach; ?>
              </ul>
              <div class="d-flex justify-content-between fw-bold border-top pt-2">
                <span>Total</span>
                <span class="text-gold"><?php echo $coin_symbol." ".$fmt($buy->getTotal()); ?></span>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<style>
  .tt-timeline{ display:flex; align-items:flex-start; }
  .tt-timeline-step{ flex:1; text-align:center; position:relative; }
  .tt-timeline-step:not(:last-child)::after{
    content:""; position:absolute; top:16px; left:calc(50% + 22px); right:calc(-50% + 22px);
    height:2px; background:#e3d5bc;
  }
  .tt-timeline-step.done:not(:last-child)::after{ background:#e0a96d; }
  .tt-timeline-dot{
    width:32px; height:32px; margin:0 auto 8px; border-radius:50%;
    background:#fff; border:2px solid #e3d5bc; color:#b2a88f;
    display:flex; align-items:center; justify-content:center; font-size:15px;
  }
  .tt-timeline-step.done .tt-timeline-dot{ border-color:#e0a96d; color:#fff; background:#e0a96d; }
  .tt-timeline-step.active .tt-timeline-dot{ border-color:#e0a96d; color:#e0a96d; }
  .tt-timeline-label{ display:block; font-size:13px; font-weight:600; color:#555; }
  .tt-timeline-sub{ display:block; font-size:11px; color:#999; }
</style>