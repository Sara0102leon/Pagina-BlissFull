<?php
/* ══════════════════════════════════════════════════════════════════
   platform-view.php — Panel de PLATAFORMA (super-admin) · Fase 6
   Ver y gestionar TODAS las tiendas, suscripciones y el movimiento.

   Acceso: rol 'superadmin' (los demás son redirigidos).
   Sub-secciones por ?opt=   all (default) · movimiento · planes
   ══════════════════════════════════════════════════════════════════ */
if(!isset($_SESSION["user_id"])){ Core::redir("../login.php"); exit; }
$_me = UserData::getById($_SESSION["user_id"]);
if(!$_me || $_me->role !== "superadmin"){ Core::redir("./"); exit; }

$pf_opt   = isset($_GET["opt"]) ? $_GET["opt"] : "all";
$plans    = PlanData::getAllActives();
$dias_aviso = SuscripcionData::diasAviso();
$baseDomain = PlatformData::cfg("saas_base_domain", "sazon.app");

// Aviso de acciones
$pf_msg = isset($_GET["msg"]) ? $_GET["msg"] : "";
$pf_ok  = isset($_GET["ok"]) ? $_GET["ok"] : "";
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title"><i class="bi bi-diagram-3 me-2"></i>Plataforma</h2>
        <div class="text-secondary mt-1">Todas las tiendas, sus planes y el movimiento de sazón.</div>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <a href="./?view=platform&opt=all" class="btn <?php echo $pf_opt=="all"?"btn-primary":"btn-default"; ?>">Tiendas</a>
          <a href="./?view=platform&opt=movimiento" class="btn <?php echo $pf_opt=="movimiento"?"btn-primary":"btn-default"; ?>">Movimiento</a>
          <a href="./?view=platform&opt=planes" class="btn <?php echo $pf_opt=="planes"?"btn-primary":"btn-default"; ?>">Planes</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if($pf_msg !== ""): ?>
<div class="container-xl pt-3">
  <div class="alert alert-<?php echo $pf_ok==="1" ? "success" : "warning"; ?> alert-dismissible" role="alert">
    <?php echo htmlspecialchars($pf_msg); ?>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></a>
  </div>
</div>
<?php endif; ?>

<?php if($pf_opt == "all"): ?>
<?php
  $st = PlatformData::stats();
  $tiendas = PlatformData::tiendas();
?>
<div class="page-body">
  <div class="container-xl">

    <!-- KPIs -->
    <div class="row row-cards mb-2">
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-primary text-white avatar"><i class="bi bi-shop"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo (int)$st["tiendas_total"]; ?></div>
                <div class="text-secondary">Tiendas · <?php echo (int)$st["tiendas_activas"]; ?> activas</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-green text-white avatar"><i class="bi bi-cash-coin"></i></span></div>
              <div class="col">
                <div class="h2 mb-0">$<?php echo number_format($st["ingresos_mes"],2); ?></div>
                <div class="text-secondary">Pagos con periodo que inicia este mes</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-yellow text-white avatar"><i class="bi bi-clock-history"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo (int)$st["por_vencer"]; ?> / <?php echo (int)$st["vencidas"]; ?></div>
                <div class="text-secondary">Por vencer / Vencidas</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-red text-white avatar"><i class="bi bi-receipt"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo (int)$st["pedidos_mes"]; ?></div>
                <div class="text-secondary">Pedidos este mes (<?php echo (int)$st["pedidos_total"]; ?> total)</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row row-cards">
      <!-- Alta de tienda + configuración -->
      <div class="col-lg-4">
        <div class="card mb-3">
          <div class="card-status-top bg-primary"></div>
          <div class="card-header"><h3 class="card-title">Nueva tienda (alta manual)</h3></div>
          <form method="post" action="./?action=platform&opt=crear">
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label">Nombre del local</label>
                <input type="text" name="nombre" class="form-control" placeholder="Don Pepe" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Subdominio</label>
                <div class="input-group">
                  <input type="text" name="subdominio" id="pf_sub" class="form-control" placeholder="donpepe">
                  <span class="input-group-text">.<?php echo htmlspecialchars($baseDomain); ?></span>
                </div>
                <small class="form-hint">Se puede dejar vacío: se genera del nombre.</small>
              </div>
              <div class="row">
                <div class="col-7 mb-3">
                  <label class="form-label">Plan</label>
                  <select name="plan_id" class="form-select">
                    <?php foreach($plans as $pl): ?>
                    <option value="<?php echo (int)$pl->id; ?>"><?php echo htmlspecialchars($pl->name); ?> · $<?php echo number_format((float)$pl->price,2); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-5 mb-3">
                  <label class="form-label">Días</label>
                  <input type="number" name="dias" class="form-control" value="30" min="1" max="3650">
                </div>
              </div>
              <label class="form-check">
                <input class="form-check-input" type="checkbox" name="con_dueno" value="1">
                <span class="form-check-label">Crear usuario dueño</span>
              </label>
              <div class="row mt-2">
                <div class="col-6 mb-2"><input type="email" name="email" class="form-control" placeholder="email@local.com" autocomplete="off"></div>
                <div class="col-6 mb-2"><input type="password" name="password" class="form-control" placeholder="contraseña (mín 6)" autocomplete="new-password"></div>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Crear tienda</button>
            </div>
          </form>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="card-title">Aviso de vencimiento</h3></div>
          <form method="post" action="./?action=platform&opt=cfg">
            <div class="card-body">
              <label class="form-label">Días de antelación del aviso</label>
              <input type="number" name="saas_dias_aviso" class="form-control" value="<?php echo (int)$dias_aviso; ?>" min="1" max="90">
              <small class="form-hint">A partir de cuántos días antes del vencimiento se marca la tienda como "por vencer".</small>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-default">Guardar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Listado de tiendas -->
      <div class="col-lg-8">
        <?php if(count($tiendas) == 0): ?>
          <div class="alert alert-warning mb-0">Todavía no hay tiendas registradas.</div>
        <?php endif; ?>

        <?php foreach($tiendas as $t): ?>
        <?php
          $estatus = strtolower(trim($t["estatus"]));
          $badge   = $estatus=="activa" ? "bg-green" : ($estatus=="suspendida" ? "bg-red" : "bg-yellow");
          $dias    = $t["dias"];
          $diasTxt = $dias===null ? "sin periodo" : ($dias < 0 ? "venció hace ".abs($dias)."d" : $dias."d restantes");
          $diasCls = $dias===null ? "text-secondary" : ($dias < 0 ? "text-danger" : ($dias <= $dias_aviso ? "text-warning" : "text-success"));
        ?>
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex align-items-start flex-wrap gap-2">
              <div class="flex-fill min-w-0">
                <div class="d-flex align-items-center gap-2">
                  <span class="h3 mb-0"><?php echo htmlspecialchars($t["nombre"]); ?></span>
                  <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($estatus); ?></span>
                  <?php if($t["sus_id"] && $t["sus_status"]==="OPEN"): ?><span class="badge bg-info">pago pendiente</span><?php endif; ?>
                </div>
                <div class="text-secondary small mt-1">
                  <a href="//<?php echo htmlspecialchars($t["subdomain"].".".$baseDomain); ?>" target="_blank" rel="noopener" class="text-reset">
                    <i class="bi bi-box-arrow-up-right me-1"></i><?php echo htmlspecialchars($t["subdomain"]); ?>.<?php echo htmlspecialchars($baseDomain); ?>
                  </a>
                  · #<?php echo (int)$t["id"]; ?> · creada <?php echo htmlspecialchars(PlatformData::fechaFmt($t["created_at"])); ?>
                </div>
              </div>
              <div class="text-end">
                <div class="<?php echo $diasCls; ?> fw-bold"><?php echo htmlspecialchars($diasTxt); ?></div>
                <div class="text-secondary small">
                  <?php echo htmlspecialchars($t["plan_name"]); ?><?php if($t["periodo_fin"]){ ?> · vence <?php echo htmlspecialchars(PlatformData::fechaFmt($t["periodo_fin"])); ?><?php } ?>
                </div>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-3 mt-3 text-secondary small">
              <span><i class="bi bi-box-seam me-1"></i><?php echo (int)$t["prods"]; ?> productos</span>
              <span><i class="bi bi-tags me-1"></i><?php echo (int)$t["cats"]; ?> categorías</span>
              <span><i class="bi bi-receipt me-1"></i><?php echo (int)$t["pedidos"]; ?> pedidos</span>
              <?php if($t["whatsapp"]): ?><span><i class="bi bi-whatsapp me-1"></i><?php echo htmlspecialchars($t["whatsapp"]); ?></span><?php endif; ?>
              <?php if($t["recordatorio_at"]): ?><span><i class="bi bi-bell me-1"></i>Aviso enviado <?php echo htmlspecialchars(PlatformData::fechaHoraFmt($t["recordatorio_at"])); ?></span><?php endif; ?>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
              <!-- Entrar como -->
              <a href="./?action=platform&opt=entrar&id=<?php echo (int)$t["id"]; ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-in-right me-1"></i>Entrar como
              </a>
              <!-- Estatus -->
              <?php if($estatus !== "activa"): ?>
              <a href="./?action=platform&opt=estatus&id=<?php echo (int)$t["id"]; ?>&to=activa" class="btn btn-sm btn-success">Activar</a>
              <?php endif; ?>
              <?php if($estatus !== "suspendida"): ?>
              <a href="./?action=platform&opt=estatus&id=<?php echo (int)$t["id"]; ?>&to=suspendida" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Suspender <?php echo htmlspecialchars(addslashes($t["nombre"])); ?>? Su menú dejará de mostrarse.');">Suspender</a>
              <?php endif; ?>
              <!-- Recordar por WhatsApp -->
              <?php if($t["whatsapp"]): ?>
              <a href="./?action=platform&opt=recordar&id=<?php echo (int)$t["id"]; ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                <i class="bi bi-whatsapp me-1"></i>Recordar
              </a>
              <?php else: ?>
              <a href="./?action=platform&opt=recordar&id=<?php echo (int)$t["id"]; ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Sin WhatsApp registrado">
                <i class="bi bi-bell me-1"></i>Marcar aviso
              </a>
              <?php endif; ?>

              <!-- Extender días -->
              <form method="post" action="./?action=platform&opt=extender" class="d-flex align-items-center gap-1 ms-auto">
                <input type="hidden" name="id" value="<?php echo (int)$t["id"]; ?>">
                <label class="text-secondary small mb-0">Extender</label>
                <input type="number" name="dias" class="form-control form-control-sm" style="width:78px" value="30" min="1" max="3650">
                <button class="btn btn-sm btn-outline-secondary" type="submit">días</button>
              </form>
              <!-- Cambiar plan -->
              <form method="post" action="./?action=platform&opt=plan" class="d-flex align-items-center gap-1">
                <input type="hidden" name="id" value="<?php echo (int)$t["id"]; ?>">
                <select name="plan_id" class="form-select form-select-sm" style="width:auto">
                  <?php foreach($plans as $pl): ?>
                  <option value="<?php echo (int)$pl->id; ?>" <?php if((int)$pl->id === (int)$t["plan_id"]){ echo "selected"; } ?>><?php echo htmlspecialchars($pl->name); ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-outline-secondary" type="submit">Plan</button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script>
// Autogenera el subdominio desde el nombre
(function(){
  var n = document.querySelector('input[name="nombre"]');
  var s = document.getElementById('pf_sub');
  if(!n || !s){ return; }
  var touched = false;
  s.addEventListener('input', function(){ touched = true; });
  n.addEventListener('input', function(){
    if(touched){ return; }
    s.value = n.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')
      .replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').slice(0,63);
  });
})();
</script>

<?php elseif($pf_opt == "movimiento"): ?>
<div class="page-body">
  <div class="container-xl">
    <div class="row row-cards">
      <div class="col-12">
        <div class="card mb-3">
          <div class="card-status-top bg-primary"></div>
          <div class="card-header"><h3 class="card-title">Suscripciones y pagos (recientes)</h3></div>
          <div class="table-responsive">
            <table class="table table-vcenter card-table">
              <thead><tr><th>Tienda</th><th>Plan</th><th>Estado</th><th>Monto</th><th>Periodo</th><th>Actualizado</th></tr></thead>
              <tbody>
              <?php $sus_list = PlatformData::suscripciones(20); ?>
              <?php if(count($sus_list) == 0): ?>
                <tr><td colspan="6" class="text-secondary">Todavía no hay pagos registrados.</td></tr>
              <?php endif; ?>
              <?php foreach($sus_list as $sx): $sst = strtoupper(trim($sx["zeno_status"]));
                    $sb = $sst=="COMPLETED" ? "bg-green" : ($sst=="OPEN" ? "bg-info" : "bg-red"); ?>
              <tr>
                <td>
                  <div class="fw-bold"><?php echo htmlspecialchars($sx["tienda"] ? $sx["tienda"] : "(sin tienda)"); ?></div>
                  <div class="text-secondary small"><?php echo htmlspecialchars($sx["subdomain"]); ?></div>
                </td>
                <td><?php echo htmlspecialchars($sx["plan_name"] ? $sx["plan_name"] : "—"); ?></td>
                <td><span class="badge <?php echo $sb; ?>"><?php echo htmlspecialchars($sst); ?></span></td>
                <td><?php echo $sx["paid_amount"]!==null ? "$".number_format((float)$sx["paid_amount"],2) : "—"; ?></td>
                <td class="text-secondary small">
                  <?php echo htmlspecialchars(PlatformData::fechaFmt($sx["periodo_inicio"])); ?> → <?php echo htmlspecialchars(PlatformData::fechaFmt($sx["periodo_fin"])); ?>
                </td>
                <td class="text-secondary small"><?php echo htmlspecialchars(PlatformData::fechaHoraFmt($sx["updated_at"] ? $sx["updated_at"] : $sx["created_at"])); ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-status-top bg-green"></div>
          <div class="card-header"><h3 class="card-title">Pedidos recientes (todas las tiendas)</h3></div>
          <div class="table-responsive">
            <table class="table table-vcenter card-table">
              <thead><tr><th>#</th><th>Tienda</th><th>Cliente</th><th>Estado</th><th>Fecha</th></tr></thead>
              <tbody>
              <?php $ped_list = PlatformData::pedidos(15); ?>
              <?php if(count($ped_list) == 0): ?>
                <tr><td colspan="5" class="text-secondary">Todavía no hay pedidos.</td></tr>
              <?php endif; ?>
              <?php foreach($ped_list as $px): ?>
              <tr>
                <td class="fw-bold">#<?php echo (int)$px["id"]; ?></td>
                <td><?php echo htmlspecialchars($px["tienda"] ? $px["tienda"] : "(sin tienda)"); ?></td>
                <td><?php echo htmlspecialchars($px["cliente"] ? $px["cliente"] : "—"); ?></td>
                <td><?php echo htmlspecialchars($px["estado"] ? $px["estado"] : "—"); ?></td>
                <td class="text-secondary small"><?php echo htmlspecialchars(PlatformData::fechaHoraFmt($px["created_at"])); ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php else: /* planes */ ?>
<div class="page-body">
  <div class="container-xl">
    <div class="card">
      <div class="card-status-top bg-primary"></div>
      <div class="card-header"><h3 class="card-title">Planes</h3></div>
      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead><tr><th>Plan</th><th>Código</th><th>Precio</th><th>Productos</th><th>Sedes</th><th>KDS</th><th>Drag&amp;drop</th><th>Tema</th><th>Reportes</th></tr></thead>
          <tbody>
          <?php foreach($plans as $pl): ?>
            <tr>
              <td class="fw-bold"><?php echo htmlspecialchars($pl->name); ?></td>
              <td><code><?php echo htmlspecialchars($pl->code); ?></code></td>
              <td>$<?php echo number_format((float)$pl->price,2); ?></td>
              <td><?php echo $pl->max_products===null || $pl->max_products==="" ? "∞" : (int)$pl->max_products; ?></td>
              <td><?php echo $pl->max_sedes===null || $pl->max_sedes==="" ? "∞" : (int)$pl->max_sedes; ?></td>
              <td><?php echo $pl->kds?"✓":"—"; ?></td>
              <td><?php echo $pl->drag_drop?"✓":"—"; ?></td>
              <td><?php echo $pl->theme?"✓":"—"; ?></td>
              <td><?php echo $pl->reports?"✓":"—"; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer text-secondary small">
        Nota: los límites de cada plan están definidos en la tabla <code>plan</code>; hoy son informativos (el cobro/límite automático por plan está pendiente).
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
