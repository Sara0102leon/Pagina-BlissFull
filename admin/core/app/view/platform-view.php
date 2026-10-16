<?php
/* ══════════════════════════════════════════════════════════════════
   platform-view.php — Panel de PLATAFORMA (super-admin) · Fase 6
   Visión CONTABLE de sazón: cuántos clientes compraron, cuántos usuarios
   hay, qué tiendas se visitan más, ingresos, planes y sus ofertas.

   Acceso: rol 'superadmin' (los demás son redirigidos).
   Sub-secciones por ?opt=   resumen (default) · tiendas · planes · movimiento
   (?opt=all se acepta como alias de tiendas, por compatibilidad)
   ══════════════════════════════════════════════════════════════════ */
if(!isset($_SESSION["user_id"])){ Core::redir("../login.php"); exit; }
$_me = UserData::getById($_SESSION["user_id"]);
if(!$_me || $_me->role !== "superadmin"){ Core::redir("./"); exit; }

$pf_opt = isset($_GET["opt"]) ? $_GET["opt"] : "resumen";
if($pf_opt === "all"){ $pf_opt = "tiendas"; }
if(!in_array($pf_opt, array("resumen","tiendas","planes","movimiento"))){ $pf_opt = "resumen"; }

$plans      = PlanData::getAllActives();
$allPlans   = ($pf_opt === "planes") ? PlanData::getAll() : array();
$dias_aviso = SuscripcionData::diasAviso();
$baseDomain = PlatformData::cfg("saas_base_domain", "sazon.app");

// Aviso de acciones
$pf_msg = isset($_GET["msg"]) ? $_GET["msg"] : "";
$pf_ok  = isset($_GET["ok"]) ? $_GET["ok"] : "";

// Plan en edición (pestaña Planes): ?opt=planes&edit=ID
$editId   = isset($_GET["edit"]) ? (int)$_GET["edit"] : 0;
$editPlan = $editId > 0 ? PlanData::getById($editId) : null;

// ¿Hay migración de visitas? (tabla `tienda_visita`)
$hasVisitas = ($pf_opt === "resumen") ? PlatformData::visitasDisponibles() : false;
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title"><i class="bi bi-diagram-3 me-2"></i>Plataforma</h2>
        <div class="text-secondary mt-1">El movimiento de sazón: clientes, usuarios, visitas, planes y cobros.</div>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <a href="./?view=platform&opt=resumen" class="btn <?php echo $pf_opt=="resumen"?"btn-primary":"btn-default"; ?>"><i class="bi bi-graph-up-arrow me-1"></i>Resumen</a>
          <a href="./?view=platform&opt=tiendas" class="btn <?php echo $pf_opt=="tiendas"?"btn-primary":"btn-default"; ?>"><i class="bi bi-shop me-1"></i>Tiendas</a>
          <a href="./?view=platform&opt=planes" class="btn <?php echo $pf_opt=="planes"?"btn-primary":"btn-default"; ?>"><i class="bi bi-tags me-1"></i>Planes</a>
          <a href="./?view=platform&opt=movimiento" class="btn <?php echo $pf_opt=="movimiento"?"btn-primary":"btn-default"; ?>"><i class="bi bi-receipt me-1"></i>Movimiento</a>
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

<?php if($pf_opt == "resumen"): ?>
<?php
  $st       = PlatformData::stats();
  $rank     = PlatformData::tiendasMasVisitadas(30, 8);
  $serie    = PlatformData::visitasSerie(14);
  $serieMax = 1; foreach($serie as $v){ if($v > $serieMax){ $serieMax = $v; } }
  $ofertas  = array();
  foreach(PlanData::getAll() as $pl){ $of = PlanData::offerInfo($pl); if($of){ $ofertas[] = array("plan"=>$pl, "of"=>$of); } }
?>
<style>
  .pf-spark{display:flex;align-items:flex-end;gap:4px;height:96px}
  .pf-spark__col{flex:1;display:flex;flex-direction:column;justify-content:flex-end;height:100%}
  .pf-spark__bar{background:var(--tblr-primary,#206bc4);border-radius:3px 3px 0 0;min-height:3px;transition:height .2s}
  .pf-spark__col:hover .pf-spark__bar{background:var(--tblr-green,#2fb344)}
  .pf-spark__lbl{font-size:.62rem;color:var(--tblr-secondary,#667382);text-align:center;margin-top:4px;white-space:nowrap}
</style>
<div class="page-body">
  <div class="container-xl">

    <!-- KPIs contables -->
    <div class="row row-cards mb-2">
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-green text-white avatar"><i class="bi bi-cash-coin"></i></span></div>
              <div class="col">
                <div class="h2 mb-0">$<?php echo number_format($st["ingresos_total"],2); ?></div>
                <div class="text-secondary">Ingresos cobrados (total)</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-teal text-white avatar"><i class="bi bi-calendar-check"></i></span></div>
              <div class="col">
                <div class="h2 mb-0">$<?php echo number_format($st["ingresos_mes"],2); ?></div>
                <div class="text-secondary">Cobrado este mes · MRR $<?php echo number_format($st["mrr"],0); ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-primary text-white avatar"><i class="bi bi-people"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo (int)$st["clientes_compradores"]; ?></div>
                <div class="text-secondary">Clientes que compraron · de <?php echo (int)$st["clientes_total"]; ?> registrados</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-purple text-white avatar"><i class="bi bi-person-badge"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo (int)$st["usuarios_total"]; ?></div>
                <div class="text-secondary">Usuarios · <?php echo (int)$st["usuarios_activos"]; ?> activos · <?php echo (int)$st["usuarios_duenos"]; ?> dueños</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row row-cards mb-3">
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-cyan text-white avatar"><i class="bi bi-eye"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo number_format($st["visitas_total"]); ?></div>
                <div class="text-secondary">Visitas al menú · mes <?php echo number_format($st["visitas_mes"]); ?> · hoy <?php echo number_format($st["visitas_hoy"]); ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-primary text-white avatar"><i class="bi bi-shop"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo (int)$st["tiendas_activas"]; ?> <span class="text-secondary fs-4">/ <?php echo (int)$st["tiendas_total"]; ?></span></div>
                <div class="text-secondary">Tiendas activas · <?php echo (int)$st["por_vencer"]; ?> por vencer · <?php echo (int)$st["vencidas"]; ?> vencidas</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-red text-white avatar"><i class="bi bi-basket"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo number_format($st["pedidos_total"]); ?></div>
                <div class="text-secondary">Pedidos de los menús · <?php echo number_format($st["pedidos_mes"]); ?> este mes</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto"><span class="bg-yellow text-white avatar"><i class="bi bi-tags"></i></span></div>
              <div class="col">
                <div class="h2 mb-0"><?php echo (int)$st["planes_activos"]; ?> <span class="text-secondary fs-4">/ <?php echo (int)$st["planes_total"]; ?></span></div>
                <div class="text-secondary">Planes activos · <?php echo (int)$st["planes_en_oferta"]; ?> con oferta vigente</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row row-cards">
      <!-- Tiendas más visitadas -->
      <div class="col-lg-7">
        <div class="card">
          <div class="card-status-top bg-primary"></div>
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-trophy me-1"></i>Tiendas más visitadas</h3>
            <div class="card-actions text-secondary small">Ranking por visitas al menú</div>
          </div>
          <div class="table-responsive">
            <table class="table table-vcenter card-table">
              <thead><tr><th>#</th><th>Tienda</th><th class="text-end">Últimos 30 d</th><th class="text-end">Total</th><th class="text-end">Pedidos</th><th class="text-end">Cobrado</th></tr></thead>
              <tbody>
              <?php if(count($rank) == 0): ?>
                <tr><td colspan="6" class="text-secondary">Todavía no hay tiendas.</td></tr>
              <?php endif; ?>
              <?php $i = 0; foreach($rank as $t): $i++;
                    $eb = strtolower(trim($t["estatus"]))=="activa" ? "bg-green" : (strtolower(trim($t["estatus"]))=="suspendida" ? "bg-red" : "bg-yellow"); ?>
              <tr>
                <td class="text-secondary"><?php echo $i; ?></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold"><?php echo htmlspecialchars($t["nombre"]); ?></span>
                    <span class="badge <?php echo $eb; ?>"><?php echo htmlspecialchars($t["estatus"]); ?></span>
                  </div>
                  <div class="text-secondary small"><?php echo htmlspecialchars($t["subdomain"]); ?>.<?php echo htmlspecialchars($baseDomain); ?> · <?php echo (int)$t["usuarios"]; ?> usuario(s)</div>
                </td>
                <td class="text-end fw-bold"><?php echo number_format($t["visitas_dias"]); ?></td>
                <td class="text-end"><?php echo number_format($t["visitas_total"]); ?></td>
                <td class="text-end"><?php echo number_format($t["pedidos"]); ?></td>
                <td class="text-end">$<?php echo number_format($t["cobrado"],2); ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if(!$hasVisitas): ?>
          <div class="card-footer text-secondary small">
            <i class="bi bi-info-circle me-1"></i>Aplicá <code>migracion_planes_ofertas.sql</code> para empezar a contar visitas.
          </div>
          <?php elseif($st["visitas_total"] === 0): ?>
          <div class="card-footer text-secondary small">
            <i class="bi bi-info-circle me-1"></i>Todavía no hay visitas registradas (se cuentan al abrir el menú público, una por sesión y por día).
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Visitas por día + ofertas vigentes -->
      <div class="col-lg-5">
        <div class="card mb-3">
          <div class="card-header"><h3 class="card-title"><i class="bi bi-bar-chart me-1"></i>Visitas (14 días)</h3></div>
          <div class="card-body">
            <div class="pf-spark">
              <?php foreach($serie as $f => $v): $h = (int)round($v / $serieMax * 100); ?>
              <div class="pf-spark__col" title="<?php echo date("d/m", strtotime($f)); ?>: <?php echo (int)$v; ?> visita(s)">
                <div class="pf-spark__bar" style="height:<?php echo max(3, $h); ?>%"></div>
                <div class="pf-spark__lbl"><?php echo date("d", strtotime($f)); ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="text-secondary small mt-2">Máximo del período: <b><?php echo number_format($serieMax); ?></b> visitas/día.</div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="card-title"><i class="bi bi-megaphone me-1"></i>Ofertas vigentes</h3></div>
          <div class="card-body">
            <?php if(count($ofertas) == 0): ?>
              <div class="text-secondary">No hay ninguna oferta activa en este momento.</div>
            <?php else: ?>
              <?php foreach($ofertas as $o): $pl = $o["plan"]; $of = $o["of"]; ?>
              <div class="d-flex align-items-center mb-2">
                <div class="flex-fill">
                  <div class="fw-bold"><?php echo htmlspecialchars($pl->name); ?></div>
                  <div class="text-secondary small">
                    $<?php echo number_format($of["price"],2); ?>
                    <s class="text-secondary">$<?php echo number_format($of["regular"],2); ?></s>
                    · <?php echo htmlspecialchars($of["label"]); ?>
                  </div>
                </div>
                <span class="badge bg-green">
                  <?php echo $of["dias"]===null ? "sin límite" : ($of["dias"]." día".($of["dias"]==1?"":"s")); ?>
                </span>
              </div>
              <div class="text-secondary small mb-2">
                <?php if($of["from"] !== "" && $of["to"] !== ""): ?>Del <?php echo htmlspecialchars(PlatformData::fechaFmt($of["from"])); ?> al <?php echo htmlspecialchars(PlatformData::fechaFmt($of["to"])); ?>
                <?php elseif($of["to"] !== ""): ?>Vence el <?php echo htmlspecialchars(PlatformData::fechaFmt($of["to"])); ?>
                <?php elseif($of["from"] !== ""): ?>Desde el <?php echo htmlspecialchars(PlatformData::fechaFmt($of["from"])); ?>
                <?php else: ?>Sin fecha límite<?php endif; ?>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div class="card-footer text-end">
            <a href="./?view=platform&opt=planes" class="btn btn-sm btn-default">Gestionar planes</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php elseif($pf_opt == "tiendas"): ?>
<?php
  $tiendas = PlatformData::tiendas();
?>
<div class="page-body">
  <div class="container-xl">

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

<?php elseif($pf_opt == "planes"): ?>
<?php
  // Valores del formulario (edición) o por defecto (nuevo)
  $ep = $editPlan;
  $f_code  = $ep ? $ep->code : "";
  $f_name  = $ep ? $ep->name : "";
  $f_desc  = $ep ? (string)$ep->description : "";
  $f_price = $ep ? $ep->price : "9.00";
  $f_cur   = $ep && $ep->currency ? $ep->currency : "USD";
  $f_maxp  = ($ep && $ep->max_products !== null && $ep->max_products !== "") ? $ep->max_products : "";
  $f_maxs  = $ep ? (int)$ep->max_sedes : 1;
  $f_pos   = $ep ? (int)$ep->position : PlanData::nextPosition();
  $f_on    = $ep ? ((int)$ep->is_active === 1) : true;
  $f_ofp   = ($ep && $ep->offer_price !== null && (float)$ep->offer_price > 0) ? $ep->offer_price : "";
  $f_offr  = ($ep && $ep->offer_from && substr((string)$ep->offer_from,0,10) !== "0000-00-00") ? substr($ep->offer_from,0,10) : "";
  $f_ofto  = ($ep && $ep->offer_to   && substr((string)$ep->offer_to,0,10)   !== "0000-00-00") ? substr($ep->offer_to,0,10)   : "";
  $f_oflab = $ep ? (string)$ep->offer_label : "";
  $f_ostate = $ep ? PlanData::offerStateLabel($ep) : "";
?>
<div class="page-body">
  <div class="container-xl">
    <div class="row row-cards">
      <!-- Listado de planes -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-status-top bg-primary"></div>
          <div class="card-header">
            <h3 class="card-title"><i class="bi bi-tags me-1"></i>Planes del menú</h3>
            <div class="card-actions">
              <a href="./?view=platform&opt=planes#pf-planform" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo plan</a>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-vcenter card-table">
              <thead><tr><th>Plan</th><th>Precio</th><th>Límites</th><th>Funciones</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
              <tbody>
              <?php if(count($allPlans) == 0): ?>
                <tr><td colspan="6" class="text-secondary">No hay planes. Creá el primero con el formulario de la derecha.</td></tr>
              <?php endif; ?>
              <?php foreach($allPlans as $pl): $os = PlanData::offerState($pl); $ol = PlanData::offerStateLabel($pl); ?>
              <tr>
                <td>
                  <div class="fw-bold"><?php echo htmlspecialchars($pl->name); ?></div>
                  <div class="text-secondary small">
                    <code><?php echo htmlspecialchars($pl->code); ?></code> · pos. <?php echo (int)$pl->position; ?>
                    <?php if($pl->description): ?><br><?php echo htmlspecialchars($pl->description); ?><?php endif; ?>
                  </div>
                </td>
                <td>
                  <div class="fw-bold">$<?php echo number_format((float)$pl->price,2); ?> <span class="text-secondary small"><?php echo htmlspecialchars($pl->currency); ?></span></div>
                  <?php if($os !== PlanData::OFF_NONE): ?>
                  <div class="small mt-1">
                    <span class="badge <?php echo $os==PlanData::OFF_ACT?"bg-green":($os==PlanData::OFF_PROG?"bg-blue":"bg-secondary"); ?>">oferta <?php echo htmlspecialchars($ol); ?></span>
                    <div class="text-secondary">
                      $<?php echo number_format((float)$pl->offer_price,2); ?>
                      <?php if($pl->offer_label): ?><br><?php echo htmlspecialchars($pl->offer_label); ?><?php endif; ?>
                      <?php if($pl->offer_from || $pl->offer_to): ?>
                      <br><i class="bi bi-calendar3 me-1"></i><?php echo htmlspecialchars(PlatformData::fechaFmt($pl->offer_from)); ?> → <?php echo htmlspecialchars(PlatformData::fechaFmt($pl->offer_to)); ?>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php endif; ?>
                </td>
                <td class="small text-secondary">
                  <?php echo ($pl->max_products===null || $pl->max_products==="") ? "∞ productos" : (int)$pl->max_products." productos"; ?><br>
                  <?php echo (int)$pl->max_sedes; ?> sede(s)
                </td>
                <td class="small">
                  <?php
                    $feat = array();
                    if($pl->kds){ $feat[] = "KDS"; }
                    if($pl->drag_drop){ $feat[] = "Orden"; }
                    if($pl->theme){ $feat[] = "Tema"; }
                    if($pl->reports){ $feat[] = "Reportes"; }
                    if($pl->priority_support){ $feat[] = "Soporte"; }
                    echo count($feat) ? htmlspecialchars(implode(" · ", $feat)) : '<span class="text-secondary">—</span>';
                  ?>
                </td>
                <td>
                  <?php if((int)$pl->is_active === 1): ?><span class="badge bg-green">activo</span>
                  <?php else: ?><span class="badge bg-secondary">inactivo</span><?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="btn-list justify-content-end flex-nowrap">
                    <a href="./?view=platform&opt=planes&edit=<?php echo (int)$pl->id; ?>#pf-planform" class="btn btn-sm btn-outline-primary">Editar</a>
                    <form method="post" action="./?action=platform&opt=plan_toggle" class="d-inline">
                      <input type="hidden" name="id" value="<?php echo (int)$pl->id; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-secondary"><?php echo ((int)$pl->is_active===1) ? "Desactivar" : "Activar"; ?></button>
                    </form>
                    <form method="post" action="./?action=platform&opt=plan_del" class="d-inline"
                          onsubmit="return confirm('¿Borrar el plan <?php echo htmlspecialchars(addslashes($pl->name)); ?>?');">
                      <input type="hidden" name="id" value="<?php echo (int)$pl->id; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Borrar</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="card-footer text-secondary small">
            El <b>precio con oferta vigente</b> es el que se cobra al renovar y el que muestra la landing.
            Los demás límites (productos, sedes, funciones) hoy son informativos.
          </div>
        </div>
      </div>

      <!-- Alta / edición de plan -->
      <div class="col-lg-4">
        <div class="card" id="pf-planform">
          <div class="card-status-top <?php echo $ep ? "bg-yellow" : "bg-green"; ?>"></div>
          <div class="card-header">
            <h3 class="card-title"><?php echo $ep ? "Editar plan" : "Nuevo plan"; ?><?php if($ep): ?> · <code><?php echo htmlspecialchars($ep->code); ?></code><?php endif; ?></h3>
            <?php if($ep): ?><div class="card-actions"><a href="./?view=platform&opt=planes" class="btn btn-sm btn-default">Cancelar</a></div><?php endif; ?>
          </div>
          <form method="post" action="./?action=platform&opt=plan_save">
            <input type="hidden" name="id" value="<?php echo $ep ? (int)$ep->id : 0; ?>">
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label required">Nombre</label>
                <input type="text" name="name" class="form-control" maxlength="120" value="<?php echo htmlspecialchars($f_name); ?>" placeholder="Restaurante" required>
              </div>
              <div class="mb-3">
                <label class="form-label required">Código (interno)</label>
                <input type="text" name="code" class="form-control" maxlength="40" value="<?php echo htmlspecialchars($f_code); ?>" placeholder="restaurante" required>
                <small class="form-hint">Minúsculas, números, guion o guion bajo. No se repite. Se usa en los enlaces de pago.</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Descripción</label>
                <input type="text" name="description" class="form-control" maxlength="160" value="<?php echo htmlspecialchars($f_desc); ?>" placeholder="El panel completo para tu negocio.">
              </div>

              <div class="row">
                <div class="col-8 mb-3">
                  <label class="form-label required">Precio / mes</label>
                  <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars((string)$f_price); ?>" required>
                </div>
                <div class="col-4 mb-3">
                  <label class="form-label">Moneda</label>
                  <input type="text" name="currency" class="form-control" maxlength="3" value="<?php echo htmlspecialchars($f_cur); ?>">
                </div>
              </div>

              <div class="row">
                <div class="col-6 mb-3">
                  <label class="form-label">Productos máx.</label>
                  <input type="number" name="max_products" class="form-control" min="1" value="<?php echo htmlspecialchars((string)$f_maxp); ?>" placeholder="∞">
                  <small class="form-hint">Vacío = ilimitado.</small>
                </div>
                <div class="col-6 mb-3">
                  <label class="form-label">Sedes máx.</label>
                  <input type="number" name="max_sedes" class="form-control" min="1" value="<?php echo (int)$f_maxs; ?>">
                </div>
              </div>

              <div class="mb-2"><label class="form-label">Funciones incluidas</label></div>
              <div class="row g-2 mb-2">
                <div class="col-6"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="kds" value="1" <?php if($ep && $ep->kds){ echo "checked"; } ?>><span class="form-check-label">Pantalla cocina (KDS)</span></label></div>
                <div class="col-6"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="drag_drop" value="1" <?php if($ep && $ep->drag_drop){ echo "checked"; } ?>><span class="form-check-label">Orden del menú</span></label></div>
                <div class="col-6"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="theme" value="1" <?php if($ep && $ep->theme){ echo "checked"; } ?>><span class="form-check-label">Apariencia propia</span></label></div>
                <div class="col-6"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="reports" value="1" <?php if($ep && $ep->reports){ echo "checked"; } ?>><span class="form-check-label">Reportes</span></label></div>
                <div class="col-6"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="priority_support" value="1" <?php if($ep && $ep->priority_support){ echo "checked"; } ?>><span class="form-check-label">Soporte prioritario</span></label></div>
                <div class="col-6"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?php if($f_on){ echo "checked"; } ?>><span class="form-check-label">Plan activo</span></label></div>
              </div>

              <hr>

              <div class="d-flex align-items-center mb-2">
                <label class="form-label mb-0 me-2">Oferta con vigencia</label>
                <?php if($f_ostate !== ""): ?>
                  <span class="badge ms-auto <?php echo $f_ostate=="vigente"?"bg-green":($f_ostate=="programada"?"bg-blue":"bg-secondary"); ?>"><?php echo htmlspecialchars($f_ostate); ?></span>
                <?php endif; ?>
              </div>
              <div class="row">
                <div class="col-6 mb-3">
                  <label class="form-label">Precio de oferta</label>
                  <input type="number" name="offer_price" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars((string)$f_ofp); ?>" placeholder="—">
                  <small class="form-hint">Vacío = sin oferta.</small>
                </div>
                <div class="col-6 mb-3">
                  <label class="form-label">Etiqueta</label>
                  <input type="text" name="offer_label" class="form-control" maxlength="80" value="<?php echo htmlspecialchars($f_oflab); ?>" placeholder="Lanzamiento">
                </div>
              </div>
              <div class="row">
                <div class="col-6 mb-3">
                  <label class="form-label">Desde</label>
                  <input type="date" name="offer_from" class="form-control" value="<?php echo htmlspecialchars($f_offr); ?>">
                </div>
                <div class="col-6 mb-3">
                  <label class="form-label">Hasta</label>
                  <input type="date" name="offer_to" class="form-control" value="<?php echo htmlspecialchars($f_ofto); ?>">
                </div>
              </div>
              <small class="form-hint d-block mb-2">Dejá las fechas vacías para una oferta sin límite. El precio de oferta debe ser menor que la tarifa normal.</small>

              <div class="mb-0">
                <label class="form-label">Posición (orden en la lista)</label>
                <input type="number" name="position" class="form-control" value="<?php echo (int)$f_pos; ?>">
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?php echo $ep ? "Guardar cambios" : "Crear plan"; ?></button>
            </div>
          </form>
        </div>
        <div class="text-secondary small mt-3">
          <i class="bi bi-lightbulb me-1"></i>Tip: para una promo de lanzamiento poné un precio de oferta y una fecha "Hasta".
          Al vencer, el cobro y la landing vuelven solos a la tarifa normal.
        </div>
      </div>
    </div>
  </div>
</div>

<?php else: /* movimiento */ ?>
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
<?php endif; ?>
