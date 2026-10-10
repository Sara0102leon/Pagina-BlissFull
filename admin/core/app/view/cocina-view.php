<?php
if(!isset($_SESSION["user_id"])){ Core::redir("./"); }
$user = UserData::getById($_SESSION["user_id"]);
if($user == null){ Core::redir("./"); }
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title"><i class="bi bi-fire text-primary me-1"></i> Cocina</h2>
        <div class="text-secondary">Pedidos por preparar. Marca cada orden como <strong>lista</strong> cuando salga a reparto.</div>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <button type="button" class="btn btn-outline-primary btn-sm" id="cocina-refresh"><i class="bi bi-arrow-clockwise me-1"></i>Refrescar</button>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div id="cocinaQueue">
      <?php
      $queue = array();
      foreach(BuyData::getAll() as $_b){
        $__s = intval($_b->status_id);
        if($__s === 1 || $__s === 2){ $queue[] = $_b; }
      }
      $queue = array_reverse($queue);
      include "core/app/partials/cocina-list.php";
      ?>
    </div>
  </div>
</div>
<script>
(function(){
  var wrap = document.getElementById("cocinaQueue");
  var lastCount = -1;
  var busy = false;

  function paint(html){
    if(!wrap){ return; }
    var tmp = document.createElement("div");
    tmp.innerHTML = html;
    var badge = tmp.querySelector(".cocina-stats .badge");
    var count = badge ? parseInt((badge.textContent.match(/\d+/)) ? badge.textContent.match(/\d+/)[0] : "0", 10) : 0;
    if(lastCount >= 0 && count > lastCount){
      wrap.classList.remove("flash");
      void wrap.offsetWidth;
      wrap.classList.add("flash");
      try{
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        var o = ctx.createOscillator(), g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.frequency.value = 660; g.gain.value = 0.12;
        o.start();
        setTimeout(function(){ o.stop(); ctx.close(); }, 220);
      }catch(e){}
    }
    lastCount = count;
    wrap.innerHTML = html;
  }

  function poll(){
    if(busy){ return; }
    busy = true;
    fetch("./?action=pedidos&opt=cocina_rows", {headers: {"X-Requested-With": "fetch"}})
      .then(function(r){ return r.text(); })
      .then(function(t){ paint(t); })
      .catch(function(){})
      .finally(function(){ busy = false; });
  }

  document.getElementById("cocina-refresh").addEventListener("click", poll);
  setInterval(poll, 15000);
  var first = document.querySelector("#cocinaQueue .cocina-stats .badge");
  lastCount = first ? parseInt((first.textContent.match(/\d+/)) ? first.textContent.match(/\d+/)[0] : "0", 10) : 0;
})();
</script>