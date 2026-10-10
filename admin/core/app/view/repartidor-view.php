<?php
if(!isset($_SESSION["user_id"])){ Core::redir("./"); }
$user = UserData::getById($_SESSION["user_id"]);
if($user == null){ Core::redir("./"); }
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title"><i class="bi bi-bicycle text-info me-1"></i> Repartidor</h2>
        <div class="text-secondary">Entregas listas para salir. Marca <strong>Entregado</strong> cuando completes el pedido.</div>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <button type="button" class="btn btn-outline-primary btn-sm" id="repo-refresh"><i class="bi bi-arrow-clockwise me-1"></i>Refrescar</button>
      </div>
    </div>
  </div>
</div>
<div class="page-body">
  <div class="container-xl">
    <div id="repoQueue">
      <?php
      $queue = array();
      foreach(BuyData::getAll() as $_b){
        if(intval($_b->status_id) === 4){ $queue[] = $_b; }
      }
      include "core/app/partials/repartidor-list.php";
      ?>
    </div>
  </div>
</div>
<script>
(function(){
  var wrap = document.getElementById("repoQueue");
  var lastCount = -1;
  var busy = false;

  function paint(html){
    if(!wrap){ return; }
    var tmp = document.createElement("div");
    tmp.innerHTML = html;
    var badge = tmp.querySelector(".repartidor-stats .badge");
    var count = badge ? parseInt((badge.textContent.match(/\d+/)) ? badge.textContent.match(/\d+/)[0] : "0", 10) : 0;
    if(lastCount >= 0 && count > lastCount){
      wrap.classList.remove("flash");
      void wrap.offsetWidth;
      wrap.classList.add("flash");
    }
    lastCount = count;
    wrap.innerHTML = html;
  }

  function poll(){
    if(busy){ return; }
    busy = true;
    fetch("./?action=pedidos&opt=repartidor_rows", {headers: {"X-Requested-With": "fetch"}})
      .then(function(r){ return r.text(); })
      .then(function(t){ paint(t); })
      .catch(function(){})
      .finally(function(){ busy = false; });
  }

  document.getElementById("repo-refresh").addEventListener("click", poll);
  setInterval(poll, 15000);
  var first = document.querySelector("#repoQueue .repartidor-stats .badge");
  lastCount = first ? parseInt((first.textContent.match(/\d+/)) ? first.textContent.match(/\d+/)[0] : "0", 10) : 0;
})();
</script>