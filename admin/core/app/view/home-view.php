<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}
$user= UserData::getById($_SESSION["user_id"]);
if($user==null){ Core::redir("./");}

$num_categories = count(CategoryData::getActives());
$num_products = count(ProductData::getAll());
$num_clients = count(ClientData::getAll());
$num_users = count(UserData::getAll());

$status = StatusData::getAll();
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">
          Dashboard
        </h2>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    
    <!-- Status Counters -->
    <div class="position-relative mb-4">
      <button type="button" class="btn btn-icon btn-sm tt-scroll-arrow tt-scroll-left" data-target="status-scroll" aria-label="Anterior" style="position:absolute;left:-8px;top:50%;transform:translateY(-50%);z-index:5;display:none;">
        <i class="bi bi-chevron-left"></i>
      </button>
      <div id="status-scroll" class="tt-scroll-container">
        <div class="tt-scroll-track">
          <?php foreach($status as $s): 
            $count = BuyData::countByStatusId($s->id)->c;
            $color = "primary";
            $icon = "bi-cart";
            if($s->id == 1) { $color = "warning"; $icon = "bi-clock"; }
            if($s->id == 2) { $color = "success"; $icon = "bi-check-circle"; }
            if($s->id == 3) { $color = "danger"; $icon = "bi-x-circle"; }
          ?>
          <div class="tt-scroll-item">
            <div class="card card-sm shadow-sm border-0 border-start border-<?php echo $color; ?> border-3 h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-<?php echo $color; ?>-lt text-<?php echo $color; ?> avatar">
                      <i class="bi <?php echo $icon; ?>"></i>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium h2 mb-0">
                      <?php echo $count; ?>
                    </div>
                    <div class="text-secondary small font-weight-bold text-uppercase">
                      <?php echo $s->name; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <button type="button" class="btn btn-icon btn-sm tt-scroll-arrow tt-scroll-right" data-target="status-scroll" aria-label="Siguiente" style="position:absolute;right:-8px;top:50%;transform:translateY(-50%);z-index:5;display:none;">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <!-- General Stats -->
    <div class="position-relative mb-4">
      <button type="button" class="btn btn-icon btn-sm tt-scroll-arrow tt-scroll-left" data-target="stats-scroll" aria-label="Anterior" style="position:absolute;left:-8px;top:50%;transform:translateY(-50%);z-index:5;display:none;">
        <i class="bi bi-chevron-left"></i>
      </button>
      <div id="stats-scroll" class="tt-scroll-container">
        <div class="tt-scroll-track">
          <div class="tt-scroll-item tt-scroll-item-lg">
            <div class="card card-sm h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-blue text-white avatar">
                      <i class="bi bi-box-seam"></i>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium"><?php echo $num_products; ?> Productos</div>
                    <div class="text-secondary small">En inventario</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tt-scroll-item tt-scroll-item-lg">
            <div class="card card-sm h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-green text-white avatar">
                      <i class="bi bi-tags"></i>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium"><?php echo $num_categories; ?> Categorías</div>
                    <div class="text-secondary small">Organizadas</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tt-scroll-item tt-scroll-item-lg">
            <div class="card card-sm h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-yellow text-white avatar">
                      <i class="bi bi-people"></i>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium"><?php echo $num_clients; ?> Clientes</div>
                    <div class="text-secondary small">Registrados</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tt-scroll-item tt-scroll-item-lg">
            <div class="card card-sm h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-red text-white avatar">
                       <i class="bi bi-person-badge"></i>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium"><?php echo $num_users; ?> Usuarios</div>
                    <div class="text-secondary small">Administradores</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-icon btn-sm tt-scroll-arrow tt-scroll-right" data-target="stats-scroll" aria-label="Siguiente" style="position:absolute;right:-8px;top:50%;transform:translateY(-50%);z-index:5;display:none;">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <div class="row mt-4">
      <div class="col-lg-12">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <h3 class="card-title mb-0">Resumen de Ventas (Últimos 30 días)</h3>
                <div class="ms-auto">
                    <span class="badge bg-primary-lt">Total de Pedidos</span>
                </div>
            </div>
            <div id="chart-buys" class="position-relative chart-lg"></div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php
// Lógica para la gráfica de los últimos 30 días
$start_date = date("Y-m-d", strtotime("-30 days"));
$end_date = date("Y-m-d");
$buys_chart = BuyData::getByRange($start_date . " 00:00:00", $end_date . " 23:59:59");

$data_points = [];
// Inicializar los 30 días con 0
for($i=30; $i>=0; $i--){
    $d = date("Y-m-d", strtotime("-$i days"));
    $data_points[$d] = 0;
}

// Llenar con datos reales (conteo de ventas por día)
foreach($buys_chart as $b){
    $day = date("Y-m-d", strtotime($b->created_at));
    if(isset($data_points[$day])){
        $data_points[$day]++;
    }
}

$labels = array_keys($data_points);
$values = array_values($data_points);
?>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    window.ApexCharts &&
      new ApexCharts(document.getElementById("chart-buys"), {
        chart: {
          type: "area",
          fontFamily: "inherit",
          height: 300,
          parentHeightOffset: 0,
          toolbar: {
            show: false,
          },
          animations: {
            enabled: true,
          },
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.15,
            opacityTo: 0.02,
            stops: [0, 90, 100]
          }
        },
        stroke: {
          width: 3,
          lineCap: "round",
          curve: "smooth",
        },
        dataLabels: {
          enabled: false,
        },
        markers: {
          size: 0,
          strokeWidth: 0,
        },
        series: [{
          name: "Ventas",
          data: <?php echo json_encode($values); ?>
        }],
        tooltip: {
          theme: 'dark',
          x: {
            format: 'dd MMM'
          }
        },
        grid: {
          strokeDashArray: 4,
          padding: {
            top: -20,
            right: 0,
            left: -4,
            bottom: -4,
          },
        },
        xaxis: {
          categories: <?php echo json_encode($labels); ?>,
          labels: {
            padding: 0,
          },
          tooltip: {
            enabled: false,
          },
          axisBorder: {
            show: false,
          },
          type: 'datetime',
        },
        yaxis: {
          labels: {
            padding: 4,
          },
        },
        colors: ["#8b1538"],
        legend: {
          show: false,
        },
      }).render();
  });
</script>

<style>
  .tt-scroll-container {
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    -ms-overflow-style: none;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    padding: 4px 0;
  }
  .tt-scroll-container::-webkit-scrollbar { display: none; }
  .tt-scroll-track {
    display: flex;
    gap: 12px;
    min-width: min-content;
  }
  .tt-scroll-item {
    flex: 0 0 calc(50% - 6px);
    min-width: 200px;
  }
  .tt-scroll-item-lg {
    flex: 0 0 calc(25% - 9px);
    min-width: 220px;
  }
  @media (max-width: 575.98px) {
    .tt-scroll-item { flex: 0 0 calc(80% - 6px); min-width: 180px; }
    .tt-scroll-item-lg { flex: 0 0 calc(80% - 6px); min-width: 200px; }
  }
  .tt-scroll-arrow {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.92);
    border: 1px solid #ddd;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    color: #333;
    display: flex; align-items: center; justify-content: center;
    transition: opacity 0.2s, background 0.2s;
  }
  .tt-scroll-arrow:hover { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.18); }
  [data-theme="dark"] .tt-scroll-arrow,
  .admin-jobie .tt-scroll-arrow {
    background: rgba(30,24,18,0.92);
    border-color: rgba(184,126,56,0.3);
    color: #e0a96d;
  }
  [data-theme="dark"] .tt-scroll-arrow:hover,
  .admin-jobie .tt-scroll-arrow:hover {
    background: rgba(40,32,22,0.98);
    border-color: rgba(184,126,56,0.5);
  }
</style>
<script>
document.addEventListener("DOMContentLoaded", function(){
  var isMobile = window.matchMedia("(max-width: 575.98px)").matches;

  document.querySelectorAll(".tt-scroll-container").forEach(function(sc){
    var wrapper = sc.closest(".position-relative");
    if(!wrapper) return;
    var leftBtn = wrapper.querySelector(".tt-scroll-left");
    var rightBtn = wrapper.querySelector(".tt-scroll-right");

    function updateArrows(){
      if(!isMobile){ leftBtn.style.display="none"; rightBtn.style.display="none"; return; }
      var atStart = sc.scrollLeft <= 4;
      var atEnd = sc.scrollLeft + sc.clientWidth >= sc.scrollWidth - 4;
      leftBtn.style.display = atStart ? "none" : "flex";
      rightBtn.style.display = atEnd ? "none" : "flex";
    }

    sc.addEventListener("scroll", updateArrows);
    updateArrows();

    if(leftBtn){
      leftBtn.addEventListener("click", function(){
        sc.scrollBy({ left: -sc.clientWidth * 0.7, behavior: "smooth" });
      });
    }
    if(rightBtn){
      rightBtn.addEventListener("click", function(){
        sc.scrollBy({ left: sc.clientWidth * 0.7, behavior: "smooth" });
      });
    }
  });
});
</script>