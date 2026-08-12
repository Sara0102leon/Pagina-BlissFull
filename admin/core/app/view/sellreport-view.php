<?php
if(!isset($_SESSION["user_id"])){ Core::redir("./");}

if(isset($_GET["opt"]) && $_GET["opt"]=="excel"){
    $buys = array();
    if(isset($_GET["start_at"]) && isset($_GET["finish_at"]) && $_GET["start_at"]!="" && $_GET["finish_at"]!=""){
        $buys = BuyData::getByRange($_GET["start_at"]." 00:00:00", $_GET["finish_at"]." 23:59:59");
    } else {
        $buys = BuyData::getAll();
    }
    $coin = ConfigurationData::getByPreffix("general_coin")->val;
    $sum_total = 0;
    $rows = "";
    foreach($buys as $b){
        $total = $b->getTotal();
        $sum_total += $total;
        $rows .= "<tr>";
        $rows .= "<td>#".$b->id."</td>";
        $rows .= "<td>".htmlspecialchars($b->getClient()->getFullname())."</td>";
        $b_sede_x = $b->getSede();
        $rows .= "<td>".($b_sede_x ? htmlspecialchars($b_sede_x->name) : "-")."</td>";
        $rows .= "<td>".number_format($total,2)."</td>";
        $rows .= "<td>".number_format(0,2)."</td>";
        $rows .= "<td>".number_format($total,2)."</td>";
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
</head>
<body>
<table border="1">
  <tr>
    <td colspan="9" style="font-weight:bold; font-size:14px;">REPORTE DE VENTAS - <?php echo $range_txt; ?></td>
  </tr>
  <tr>
    <th>ID</th>
    <th>Cliente</th>
    <th>Sede</th>
    <th>SubTotal</th>
    <th>Descuento</th>
    <th>Total</th>
    <th>Metodo</th>
    <th>Estado</th>
    <th>Fecha</th>
  </tr>
  <?php echo $rows; ?>
  <tr style="font-weight:bold; background-color:#DDEBF7;">
    <td colspan="4">TOTAL (<?php echo count($buys); ?> ventas)</td>
    <td><?php echo number_format($sum_total,2); ?></td>
    <td colspan="3"></td>
  </tr>
</table>
</body>
</html>
<?php
    exit;
}

$buys = array();
if(isset($_GET["start_at"]) && isset($_GET["finish_at"]) && $_GET["start_at"]!="" && $_GET["finish_at"]!=""){
    $buys = BuyData::getByRange($_GET["start_at"]." 00:00:00", $_GET["finish_at"]." 23:59:59");
} else {
    $buys = BuyData::getAll();
}
$coin = ConfigurationData::getByPreffix("general_coin")->val;
$sum_total = 0;
foreach($buys as $b){ $sum_total += $b->getTotal(); }
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
          <input type="hidden" name="view" value="sellreport">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Desde</label>
              <input type="date" name="start_at" class="form-control" value="<?php echo isset($_GET["start_at"])?$_GET["start_at"]:""; ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Hasta</label>
              <input type="date" name="finish_at" class="form-control" value="<?php echo isset($_GET["finish_at"])?$_GET["finish_at"]:""; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel me-1"></i> Generar Reporte
              </button>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <a href="./?view=sellreport&opt=excel&start_at=<?php echo isset($_GET["start_at"])?$_GET["start_at"]:""; ?>&finish_at=<?php echo isset($_GET["finish_at"])?$_GET["finish_at"]:""; ?>" class="btn btn-success w-100">
                <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <?php if(isset($_GET["start_at"]) && isset($_GET["finish_at"]) && $_GET["start_at"]!="" && $_GET["finish_at"]!=""): 
        $start_at = strtotime($_GET["start_at"]);
        $finish_at = strtotime($_GET["finish_at"]);
        $labels = [];
        $values = [];
        for($i=$start_at; $i<=$finish_at; $i+=(60*60*24)){
            $date = date("Y-m-d", $i);
            $labels[] = $date;
            $operations = BuyData::getAllByDate($date);
            $total_day = 0;
            foreach ($operations as $buy) {
                $total_day += $buy->getTotal();
            }
            $values[] = $total_day;
        }
    ?>
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="card-title">Tendencia de Ventas (<?php echo $coin; ?>)</h3>
        <div id="chart-report" class="position-relative chart-lg"></div>
      </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        window.ApexCharts &&
          new ApexCharts(document.getElementById("chart-report"), {
            chart: {
              type: "area",
              fontFamily: "inherit",
              height: 300,
              parentHeightOffset: 0,
              toolbar: { show: false },
              animations: { enabled: true },
            },
            fill: { opacity: .1, type: 'solid' },
            stroke: { width: 3, lineCap: "round", curve: "smooth" },
            series: [{
              name: "Ventas",
              data: <?php echo json_encode($values); ?>
            }],
            tooltip: { theme: 'dark', x: { format: 'dd MMM' } },
            grid: { strokeDashArray: 4, padding: { top: -20, right: 0, left: -4, bottom: -4 } },
            xaxis: {
              categories: <?php echo json_encode($labels); ?>,
              type: 'datetime',
            },
            colors: ["var(--tblr-primary)"],
          }).render();
      });
    </script>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Resultados</h3>
      </div>
      <div class="card-body">
      <div class="table-responsive">
        <?php if(count($buys)>0): ?>
        <table class="table table-vcenter table-mobile-md card-table datatable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Sede</th>
              <th>SubTotal</th>
              <th>Descuento</th>
              <th>Total</th>
              <th>Método</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th class="w-1"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($buys as $b): 
                $client = $b->getClient();
                $paymethod = $b->getPaymethod();
                $status = $b->getStatus();
                $discount = 0;
                if($b->coupon_id != null){
                    //$coupon = CouponData::getById($b->coupon_id);
                    $discount =0; // $coupon->val;
                }
                $total = $b->getTotal();
            ?>
            <tr>
              <td data-label="ID">#<?php echo $b->id; ?></td>
              <td data-label="Cliente"><?php echo htmlspecialchars($client->getFullname()); ?></td>
              <td data-label="Sede"><?php $b_sede = $b->getSede(); echo $b_sede ? htmlspecialchars($b_sede->name) : "-"; ?></td>
              <td data-label="SubTotal"><?php echo $coin; ?> <?php echo number_format($total, 2); ?></td>
              <td data-label="Descuento"><?php echo $coin; ?> <?php echo number_format($discount, 2); ?></td>
              <td data-label="Total" class="fw-bold"><?php echo $coin; ?> <?php echo number_format($total - $discount, 2); ?></td>
              <td data-label="Método"><?php echo $paymethod->name; ?></td>
              <td data-label="Estado">
                <?php 
                  $badge = "secondary";
                  if($b->status_id == 1) $badge = "warning";
                  if($b->status_id == 2) $badge = "success";
                  if($b->status_id == 3) $badge = "danger";
                ?>
                <span class="badge bg-<?php echo $badge; ?>-lt"><?php echo $status->name; ?></span>
              </td>
              <td data-label="Fecha"><?php echo $b->created_at; ?></td>
              <td>
                <a href="./?view=sells&opt=open&id=<?php echo $b->id; ?>" class="btn btn-sm btn-ghost-primary">Detalles</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="fw-bold table-active">
              <td>TOTAL (<?php echo count($buys); ?> ventas)</td>
              <td></td>
              <td></td>
              <td><?php echo $coin; ?> <?php echo number_format($sum_total, 2); ?></td>
              <td></td>
              <td><?php echo $coin; ?> <?php echo number_format($sum_total, 2); ?></td>
              <td colspan="4"></td>
            </tr>
          </tfoot>
        </table>
        <?php else: ?>
        <div class="empty py-5">
          <div class="empty-icon"><i class="bi bi-search h1"></i></div>
          <p class="empty-title">No hay operaciones</p>
          <p class="empty-subtitle text-muted">Intenta cambiar el rango de fechas para encontrar resultados.</p>
        </div>
        <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</div>
