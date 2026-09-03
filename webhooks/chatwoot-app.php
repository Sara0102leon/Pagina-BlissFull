<?php
// Dashboard App "Blissfull" - panel dentro de la conversación de Chatwoot
// Recibe el token via query string (configurado en la Dashboard App URL)
require_once __DIR__ . "/_bootstrap.php";
$token = isset($_GET["token"]) ? htmlspecialchars($_GET["token"]) : "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Blissfull Ventas</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#f5f1EA;color:#1a0004;font-size:13px}
.wrap{padding:12px}
h1{font-size:14px;margin:0 0 10px;color:#b87e38;text-transform:uppercase;letter-spacing:.5px}
.card{background:#fff;border:1px solid #e4dfd5;border-radius:8px;padding:12px;margin-bottom:10px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.row{display:flex;justify-content:space-between;padding:3px 0}
.row .k{color:#8a7f6d}
.row .v{font-weight:600;text-align:right;max-width:62%}
.badge{display:inline-block;padding:3px 9px;border-radius:999px;color:#fff;font-weight:600;font-size:12px}
.b-1{background:#f0ad4e}.b-2{background:#e0a96d}.b-3{background:#d9534f}.b-4{background:#0275d8}.b-5{background:#5cb85c}
.empty{color:#8a7f6d;font-style:italic;text-align:center;padding:14px 0}
.buttons{display:flex;flex-direction:column;gap:6px}
.btn{display:block;width:100%;padding:9px;border:none;border-radius:6px;color:#fff;font-weight:700;font-size:13px;cursor:pointer}
.btn:disabled{opacity:.45;cursor:not-allowed}
.btn:hover:not(:disabled){filter:brightness(.94)}
.btn-paid{background:#5cb85c}
.btn-ship{background:#0275d8}
.btn-cancel{background:#d9534f}
.note{font-size:11px;color:#8a7f6d;text-align:center;margin-top:8px}
.spin{display:inline-block;width:14px;height:14px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<div class="wrap">
  <h1>Blissfull · Ventas</h1>
  <div id="content"><div class="empty"><span class="spin"></span> Cargando…</div></div>
</div>
<script>
var API = "./chatwoot-app-api.php?token=" + encodeURIComponent(<?php echo json_encode($token); ?>);
var conversationId = null;

function statusBadge(s){
  var map = {1:"Pendiente",2:"Pagado",3:"Cancelado",4:"Enviado",5:"Finalizado"};
  return '<span class="badge b-' + s + '">' + (map[s]||s) + '</span>';
}

function field(k,v){
  if(!v){ return ''; }
  return '<div class="row"><span class="k">' + k + '</span><span class="v">' + v + '</span></div>';
}

function renderSale(s){
  var linkedButtons = '';
  var isDelivery = s.zona || s.address;
  if(s.status === 1){
    linkedButtons =
      '<button class="btn btn-paid" data-kw="pago_recibido">✓ Marcar Pagado</button>' +
      '<button class="btn btn-cancel" data-kw="cancelado">✕ Cancelar</button>';
  } else if(s.status === 2){
    linkedButtons =
      '<button class="btn btn-ship" data-kw="enviado">🚚 Marcar Enviado</button>' +
      '<button class="btn btn-paid" data-kw="finalizado">✓ Finalizar</button>';
  }
  var maps = s.sede_maps
    ? '<a href="'+s.sede_maps+'" target="_blank" rel="noopener">Ver en Google Maps ↗</a>'
    : '';

  document.getElementById('content').innerHTML =
    '<div class="card">' +
      '<div class="row"><span class="k">Operación</span><span class="v">#'+s.id+' '+statusBadge(s.status)+'</span></div>' +
      field('Código','<code>#'+s.code+'</code>') +
      field('Sede',s.sede) +
      field('Cliente',s.client) +
      field('Teléfono',s.phone) +
      field('Dirección',s.address) +
      field('Zona',s.zona) +
      (maps ? '<div class="row"><span class="k">Maps</span><span class="v">'+maps+'</span></div>' : '') +
      field('Programado',s.scheduled_at) +
      field('Pago',s.paymethod) +
      field('Total','<b>'+s.total+'</b>') +
      field('Nota',s.note) +
    '</div>' +
    (linkedButtons
      ? '<div class="card"><div class="buttons">'+linkedButtons+'</div></div>'
      : '<div class="empty">Venta finalizada o sin acciones disponibles.</div>') +
    '<div class="note">Pulsa un botón para confirmar en la venta.</div>';

  document.querySelectorAll('.btn[data-kw]').forEach(function(b){
    b.addEventListener('click', function(){ doStatus(b.dataset.kw, b); });
  });
}

function doStatus(kw, btn){
  var original = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spin"></span> Procesando…';
  fetch(API + '&action=status&id=' + conversationId + '&status=' + kw)
    .then(function(r){ return r.json(); })
    .then(function(j){
      if(j && j.ok && j.sale){ renderSale(j.sale); }
      else {
        btn.disabled = false;
        btn.innerHTML = original;
        alert('No se pudo actualizar. ' + (j && j.error ? j.error : ''));
      }
    })
    .catch(function(){
      btn.disabled = false;
      btn.innerHTML = original;
      alert('Error de red. Intenta de nuevo.');
    });
}

function loadInfo(){
  if(!conversationId){ document.getElementById('content').innerHTML='<div class="empty">Sin conversación.</div>'; return; }
  fetch(API + '&action=info&id=' + conversationId)
    .then(function(r){ return r.json(); })
    .then(function(j){
      if(j && j.linked && j.sale){ renderSale(j.sale); }
      else { document.getElementById('content').innerHTML = '<div class="empty">' + (j.message||'Sin venta vinculada para esta conversación.') + '</div>'; }
    })
    .catch(function(){ document.getElementById('content').innerHTML='<div class="empty">Error al consultar.</div>'; });
}

// Handshake con Chatwoot: pedimos el appContext
function handleMessage(event){
  var raw = event.data;
  if(typeof raw !== 'string'){ return; }
  var msg;
  try { msg = JSON.parse(raw); } catch(e){ return; }
  if(msg && msg.event === 'appContext' && msg.data){
    var conv = msg.data.conversation;
    if(conv && conv.id){
      conversationId = conv.id;
      loadInfo();
    }
  }
}
window.addEventListener('message', handleMessage);
window.parent.postMessage('chatwoot-dashboard-app:fetch-info', '*');
</script>
</body>
</html>
