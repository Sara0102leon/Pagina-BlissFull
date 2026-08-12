<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}
$user= UserData::getById($_SESSION["user_id"]);
if($user==null){ Core::redir("./");}
$coin = ConfigurationData::getByPreffix("general_coin")->val;
?>
<?php if(isset($_GET["opt"]) && $_GET["opt"]=="all"):?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Ventas</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Historial de Ventas</h3>
      </div>
      <div class="card-body">
    <?php
    $buys = BuyData::getAll();
    if(count($buys)>0):?>
      <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
          <thead>
            <tr>
              <th></th>
              <th>Operacion</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Metodo de pago</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($buys as $b):
            $discount = 0;
            // $coupon handling skipped due to missing model
            ?>
            <tr>
              <td><a href="./?view=sells&opt=open&id=<?php echo $b->id; ?>" class="btn btn-sm btn-default">Detalles</a></td>
              <td>#<?php echo $b->id; ?></td>
              <td><?php echo $b->getClient()->getFullname(); ?></td>
              <td><?php echo $coin; ?> <?php echo number_format($b->getTotal()-$discount,2,".",","); ?></td>
              <td><?php echo $b->getPaymethod()->name; ?></td>
              <td>
                <?php if($b->status_id==1):?>
                  <span class="badge text-white order-elapsed" data-created="<?php echo date("Y-m-d H:i:s", strtotime($b->created_at)); ?>" style="background:#f59f00;">--:--</span>
                  <div class="small text-muted">Pendiente de pago</div>
                <?php else:?>
                  <?php echo $b->getStatus()->name; ?>
                <?php endif;?>
              </td>
              <td><?php echo $b->created_at; ?></td>
              <td>
                <?php if($b->status_id==3):?>
                  <span class="badge bg-danger"><i class="bi bi-x-lg me-1"></i>Cancelado</span>
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
                <?php else:?>
                  <i class="bi bi-check-lg text-success"></i>
                <?php endif;?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
    <?php else:?>
        <p class="alert alert-warning mb-0">No hay operaciones.</p>
    <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
$(function(){
  var EL_LV = [
    { max: 2400,    color: "#2fb344", txt: "Esperando pago" },
    { max: 3600,    color: "#fd7e14", txt: "Riesgo de no pagar" },
    { max: 9999999, color: "#d63939", txt: "Posible pedido falso" }
  ];
  var serverMs = Date.parse("<?php echo date('c'); ?>");
  var startClient = Date.now();

  function fmtDur(sec){
    sec = Math.max(0, sec);
    var h = Math.floor(sec/3600), m = Math.floor((sec%3600)/60), s = sec%60;
    if(h > 0){ return h + "h " + String(m).padStart(2,"0") + "m"; }
    return String(m).padStart(2,"0") + ":" + String(s).padStart(2,"0");
  }

  function tick(){
    var currentMs = serverMs + (Date.now() - startClient);
    $(".order-elapsed").each(function(){
      var created = Date.parse($(this).data("created").replace(" ", "T"));
      var sec = Math.floor((currentMs - created) / 1000);
      if(sec < 0){ sec = 0; }
      var lv = EL_LV[0];
      for(var i = 0; i < EL_LV.length; i++){ if(sec <= EL_LV[i].max){ lv = EL_LV[i]; break; } }
      $(this).css("background", lv.color).text(fmtDur(sec)).attr("title", lv.txt + " \u00b7 " + sec + " segundos");
    });
  }

  setInterval(tick, 1000);
  tick();
});
</script>
<script>
$(function(){
  var STATUS_CONF = {
    2: { title: "¿Marcar como PAGADO?", icon: "success", color: "#2f9e44", confirmText: "Sí, marcar pagado", doneText: "Pedido marcado como pagado" },
    4: { title: "¿Marcar como ENVIADO?", icon: "question", color: "#2f9e44", confirmText: "Sí, marcar enviado", doneText: "Pedido marcado como enviado" },
    5: { title: "¿Marcar como FINALIZADO?", icon: "success", color: "#2f9e44", confirmText: "Sí, finalizar", doneText: "Pedido finalizado" },
    3: { title: "¿CANCELAR este pedido?", icon: "warning", color: "#d63939", confirmText: "Sí, cancelar", doneText: "Pedido cancelado" }
  };
  $(document).on("click", ".btn-status-change", function(){
    var $btn = $(this);
    var id = $btn.data("id");
    var status = $btn.data("status");
    var cfg = STATUS_CONF[status] || { title: "¿Cambiar estado?", icon: "question", color: "#2f9e44", confirmText: "Sí", doneText: "Estado actualizado" };
    Swal.fire({
      title: cfg.title,
      html: status == 3
        ? "El pedido <b>#"+id+"</b> quedará <b>cancelado</b> y <b>NO podrás cambiar su estado después</b>."
        : "Se actualizará el estado del pedido <b>#"+id+"</b>.",
      icon: cfg.icon,
      showCancelButton: true,
      confirmButtonText: cfg.confirmText,
      cancelButtonText: "Cancelar",
      confirmButtonColor: cfg.color,
      cancelButtonColor: "#2c3b41",
      reverseButtons: true
    }).then(function(result){
      if(!result.isConfirmed) return;
      $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span>');
      $.get("./?action=sells&opt=status&id=" + id + "&status=" + status)
        .done(function(){
          Swal.fire({ icon: "success", title: cfg.doneText, toast: true, position: "top-end", timer: 2500, showConfirmButton: false });
          setTimeout(function(){ location.reload(); }, 900);
        })
        .fail(function(){
          $btn.prop("disabled", false).html('<i class="bi bi-exclamation-triangle"></i>');
          Swal.fire({ icon: "error", title: "Error", text: "No se pudo cambiar el estado. Intenta de nuevo.", confirmButtonColor: "#d63939" });
        });
    });
  });
});
</script>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="open"):?>
<?php
$buy = BuyData::getById($_GET["id"]);
$products = BuyProductData::getAllByBuyId($_GET["id"]);
$client = ClientData::getById($buy->client_id);
$paymethod = $buy->getPaymethod();
$iva = ConfigurationData::getByPreffix("general_iva")->val;
$ivatxt = ConfigurationData::getByPreffix("general_iva_txt")->val;
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Venta #<?php echo $buy->id; ?> [<?php echo $buy->getStatus()->name; ?>]</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-info"></div>
      <div class="card-header">
        <h3 class="card-title">Detalles de la Venta</h3>
      </div>
      <div class="card-body">
        <p><strong>Cliente:</strong> <?php echo $client->getFullname(); ?><br>
        <strong>Teléfono:</strong> <?php echo $client->phone; ?><br>
        <strong>Dirección:</strong> <?php echo $client->address ? $client->address : "Recoger en sucursal"; ?><br>
        <strong>Metodo de pago:</strong> <?php echo $paymethod->name; ?><br>
        <?php $zone = $buy->getDeliveryZone(); if($zone): ?>
        <strong>Zona de Delivery:</strong> <?php echo htmlspecialchars($zone->name); ?> ($ <?php echo number_format($zone->price,2,".",","); ?>)<br>
        <?php endif; ?>
        <?php if($buy->capture): ?>
        <strong>Capture de pago:</strong> <a href="../core/uploads/captures/<?php echo $buy->capture; ?>" target="_blank">Ver capture</a><br>
        <?php endif; ?></p>

        <?php if(count($products)>0):?>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th></th>
                <th>Codigo</th>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Extras</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($products as $p):
                $px = $p->getProduct();
                $line_extras = $p->getExtrasArray();
                $line_extra_txt = "";
                if(count($line_extras)>0){
                  $parts = array();
                  foreach($line_extras as $e){ $parts[] = $e["name"]." (+$".number_format($e["price"],2,".",",").")"; }
                  $line_extra_txt = implode(", ", $parts);
                }
              ?>
              <tr>
                <td><a target="_blank" href="../index.php?view=producto&product_id=<?php echo $px->id; ?>">Ver</a></td>
                <td><?php echo $px->code; ?></td>
                <td><?php echo $px->name; ?></td>
                <td><?php echo $p->q; ?></td>
                <td><?php echo $line_extra_txt!="" ? $line_extra_txt : "-"; ?></td>
                <td><?php echo $coin; ?> <?php echo number_format(($px->price+$p->getExtrasTotal())*$p->q,2,".",","); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="row mt-4">
          <div class="col-md-4 ms-auto">
            <table class="table table-sm table-bordered">
              <tr>
                <td>Subtotal</td><td><?php echo $coin; ?> <?php echo number_format($buy->getTotal()-($buy->getTotal()*($iva/100)),2,".",","); ?></td>
              </tr>
              <?php if($zone): ?>
              <tr>
                <td>Delivery (<?php echo htmlspecialchars($zone->name); ?>)</td><td><?php echo $coin; ?> <?php echo number_format($zone->price,2,".",","); ?></td>
              </tr>
              <?php endif; ?>
              <tr>
                <td><?php echo $ivatxt; ?></td><td><?php echo $coin; ?> <?php echo number_format($buy->getTotal()*($iva/100),2,".",","); ?></td>
              </tr>
              <tr class="fw-bold">
                <td>Total</td><td><?php echo $coin; ?> <?php echo number_format($buy->getTotal(),2,".",","); ?></td>
              </tr>
            </table>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="report"):?>
<?php
$buys = array();
if(isset($_GET["start_at"]) && isset($_GET["finish_at"]) && $_GET["start_at"]!="" && $_GET["finish_at"]!=""){
  $buys = BuyData::getByRange($_GET["start_at"],$_GET["finish_at"]);
}else{
  $buys = BuyData::getAll();
}

// group by month and sum totals with daily BCV rate
function bcv_rate_for_date($date){
  $sql = "select rate from bcv_history where rate_date='$date' limit 1";
  $q = Executor::doit($sql);
  $r = Model::one($q[0], new StatusData());
  if($r && $r->rate){ return floatval($r->rate); }
  return 0;
}

$monthly = array();
$grand_bs = 0;
$grand_usd = 0;
foreach($buys as $b){
  $d = date("Y-m", strtotime($b->created_at));
  if(!isset($monthly[$d])){ $monthly[$d] = array("bs"=>0,"usd"=>0,"count"=>0); }
  $total = $b->getTotal();
  $rate = bcv_rate_for_date(date("Y-m-d", strtotime($b->created_at)));
  $monthly[$d]["usd"] += $total;
  $monthly[$d]["count"]++;
  $grand_usd += $total;
  if($rate>0){ $monthly[$d]["bs"] += $total*$rate; $grand_bs += $total*$rate; }
}
krsort($monthly);
$coin = ConfigurationData::getByPreffix("general_coin")->val;
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Reporte de Ventas</h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div class="card mb-3">
      <div class="card-body">
        <form method="get">
          <input type="hidden" name="view" value="sells">
          <input type="hidden" name="opt" value="report">
          <div class="row g-2">
            <div class="col-md-5">
              <input type="date" name="start_at" class="form-control" value="<?php echo @$_GET["start_at"];?>">
            </div>
            <div class="col-md-5">
              <input type="date" name="finish_at" class="form-control" value="<?php echo @$_GET["finish_at"];?>">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">Generar</button>
            </div>
            <div class="col-md-3">
              <a href="./?view=sells&opt=excel&start_at=<?php echo @$_GET["start_at"];?>&finish_at=<?php echo @$_GET["finish_at"];?>" class="btn btn-success w-100"><i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel</a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-status-top bg-success"></div>
      <div class="card-header">
        <h3 class="card-title">Resumen Mensual (con tasa BCV del día de la venta)</h3>
      </div>
      <div class="card-body">
        <?php if(count($monthly)>0):?>
        <div class="table-responsive">
          <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
              <tr>
                <th>Mes</th>
                <th>Pedidos</th>
                <th>Total US$</th>
                <th>Total Bs</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($monthly as $m => $data): ?>
              <tr>
                <td class="fw-bold"><?php echo date("F Y", strtotime($m."-01")); ?></td>
                <td><?php echo $data["count"]; ?></td>
                <td><?php echo "$ ".number_format($data["usd"],2,".",","); ?></td>
                <td><?php echo $data["bs"]>0 ? "Bs ".number_format($data["bs"],2,".",",") : "-"; ?></td>
              </tr>
            <?php endforeach; ?>
              <tr class="fw-bold table-active">
                <td>TOTAL</td>
                <td><?php echo count($buys); ?></td>
                <td><?php echo "$ ".number_format($grand_usd,2,".",","); ?></td>
                <td><?php echo $grand_bs>0 ? "Bs ".number_format($grand_bs,2,".",",") : "-"; ?></td>
              </tr>
            </tbody>
          </table>
        </div>
        <?php else:?>
          <p class="alert alert-warning mb-0">No hay operaciones en este rango.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header">
        <h3 class="card-title">Resultados</h3>
      </div>
      <div class="card-body">
        <?php if(count($buys)>0):?>
        <div class="table-responsive">
          <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
              <tr>
                <th>Operacion</th>
                <th>Cliente</th>
                <th>Total US$</th>
                <th>Total Bs</th>
                <th>Tasa BCV</th>
                <th>Metodo de pago</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
            <?php 
            $sum_usd = 0; $sum_bs = 0;
            foreach($buys as $b):
              $discount = 0;
              $rate_b = bcv_rate_for_date(date("Y-m-d", strtotime($b->created_at)));
              $total_usd_row = $b->getTotal()-$discount;
              $total_bs_row = $rate_b>0 ? $total_usd_row*$rate_b : 0;
              $sum_usd += $total_usd_row;
              $sum_bs += $total_bs_row;
              ?>
              <tr>
                <td>#<?php echo $b->id; ?></td>
                <td><?php echo $b->getClient()->getFullname(); ?></td>
                <td><?php echo "$ ".number_format($total_usd_row,2,".",","); ?></td>
                <td><?php echo $rate_b>0 ? "Bs ".number_format($total_bs_row,2,".",",") : "-"; ?></td>
                <td><?php echo $rate_b>0 ? number_format($rate_b,2,".",",") : "-"; ?></td>
                <td><?php echo $b->getPaymethod()->name; ?></td>
                <td><?php echo $b->getStatus()->name; ?></td>
                <td><?php echo $b->created_at; ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="fw-bold table-active">
                <td>TOTAL (<?php echo count($buys); ?> ventas)</td>
                <td></td>
                <td><?php echo "$ ".number_format($sum_usd,2,".",","); ?></td>
                <td><?php echo $sum_bs>0 ? "Bs ".number_format($sum_bs,2,".",",") : "-"; ?></td>
                <td colspan="4"></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <?php else:?>
          <p class="alert alert-warning mb-0">No hay operaciones en este rango.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php elseif(isset($_GET["opt"]) && $_GET["opt"]=="excel"):?>
<?php
function bcv_rate_for_date_excel($date){
  $sql = "select rate from bcv_history where rate_date='$date' limit 1";
  $q = Executor::doit($sql);
  $r = Model::one($q[0], new StatusData());
  if($r && $r->rate){ return floatval($r->rate); }
  return 0;
}
$buys = array();
if(isset($_GET["start_at"]) && isset($_GET["finish_at"]) && $_GET["start_at"]!="" && $_GET["finish_at"]!=""){
  $buys = BuyData::getByRange($_GET["start_at"],$_GET["finish_at"]);
}else{
  $buys = BuyData::getAll();
}
$sum_usd = 0;
$sum_bs = 0;
$count = count($buys);
$rows = "";
foreach($buys as $b){
  $rate = bcv_rate_for_date_excel(date("Y-m-d", strtotime($b->created_at)));
  $tu = $b->getTotal();
  $tbs = $rate>0 ? $tu*$rate : 0;
  $sum_usd += $tu;
  $sum_bs += $tbs;
  $rows .= "<tr>";
  $rows .= "<td>#".$b->id."</td>";
  $rows .= "<td>".htmlspecialchars($b->getClient()->getFullname())."</td>";
  $rows .= "<td>".number_format($tu,2)."</td>";
  $rows .= "<td>".number_format($tbs,2)."</td>";
  $rows .= "<td>".($rate>0 ? number_format($rate,2) : "-")."</td>";
  $rows .= "<td>".htmlspecialchars($b->getPaymethod()->name)."</td>";
  $rows .= "<td>".htmlspecialchars($b->getStatus()->name)."</td>";
  $rows .= "<td>".$b->created_at."</td>";
  $rows .= "</tr>";
}
$range_txt = (@$_GET["start_at"]!="" && @$_GET["finish_at"]!="") ? $_GET["start_at"]." a ".$_GET["finish_at"] : "TODO EL HISTORIAL";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_ventas_".date("Ymd_His").".xls");
echo "\xEF\xBB\xBF";
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="utf-8">
<title>Reporte de Ventas</title>
<style>
td, th { mso-number-format: "@"; }
</style>
</head>
<body>
<table border="1">
  <tr>
    <td colspan="8" style="font-weight:bold; font-size:14px;">REPORTE DE VENTAS - <?php echo $range_txt; ?></td>
  </tr>
  <tr>
    <th>Operacion</th>
    <th>Cliente</th>
    <th>Total US$</th>
    <th>Total Bs</th>
    <th>Tasa BCV</th>
    <th>Metodo de pago</th>
    <th>Estado</th>
    <th>Fecha</th>
  </tr>
  <?php echo $rows; ?>
  <tr style="font-weight:bold; background-color:#DDEBF7;">
    <td colspan="2">TOTAL (<?php echo $count; ?> ventas)</td>
    <td><?php echo number_format($sum_usd,2); ?></td>
    <td><?php echo number_format($sum_bs,2); ?></td>
    <td colspan="4"></td>
  </tr>
</table>
</body>
</html>
<?php endif; ?>
