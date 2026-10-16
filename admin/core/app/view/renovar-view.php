<?php
/* ══════════════════════════════════════════════════════════════════
   renovar-view.php — Renovación / activación del plan de la tienda
   (?view=renovar)  ·  lo abre el banner de vencimiento del panel.

   Demo: activa al instante.  Live: crea el pago en Zenobank y redirige.
   ══════════════════════════════════════════════════════════════════ */
if(!isset($_SESSION["user_id"])){ Core::redir("../login.php"); exit; }
$_me = UserData::getById($_SESSION["user_id"]);
if(!$_me){ Core::redir("../login.php"); exit; }
$_role = ($_me->role !== "") ? $_me->role : "dueno";
if(!in_array($_role, array("dueno","superadmin"))){ Core::redir("./"); exit; }

$tienda = Tenant::current();
$plans  = PlanData::getAllActives();
$sus    = $tienda ? SuscripcionData::getLatestByTienda((int)$tienda->id) : null;
$alert  = $tienda ? SuscripcionData::tenantAlert((int)$tienda->id) : null;

$modo = ConfigurationData::getByPreffix("zeno_mode"); $modo = $modo ? $modo->val : "demo";
$aKey = ConfigurationData::getByPreffix("zeno_api_key"); $aKey = $aKey ? trim($aKey->val) : "";
if($modo !== "live" || $aKey === ""){ $modo = "demo"; }

$cur_plan = $sus ? (int)$sus->plan_id : (count($plans) ? (int)$plans[0]->id : 0);
$cur_plan_obj = $sus ? PlanData::getById((int)$sus->plan_id) : null;
$pago_exito = isset($_GET["pago"]) && $_GET["pago"] === "exito";
$err = isset($_GET["err"]) ? $_GET["err"] : "";
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Renovar plan</h2>
        <div class="text-secondary mt-1">Activa o renueva la suscripción de tu menú digital.</div>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">

    <?php if($pago_exito): ?>
    <div class="alert alert-success" role="alert">
      <i class="bi bi-check-circle me-1"></i>
      <?php if($modo==="demo"): ?>¡Listo! El pago se simuló (modo demo) y tu plan quedó activo.
      <?php else: ?>¡Gracias! Recibimos tu pago; tu plan se activa en unos segundos.<?php endif; ?>
      <?php if($sus && $sus->periodo_fin): ?> Vence el <b><?php echo htmlspecialchars(SuscripcionData::fechaFmt($sus->periodo_fin)); ?></b>.<?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if($err === "zeno"): ?>
    <div class="alert alert-danger" role="alert">No pudimos conectar con Zenobank. Inténtalo de nuevo en un momento.</div>
    <?php elseif($err === "sin-plan"): ?>
    <div class="alert alert-danger" role="alert">No hay planes activos configurados.</div>
    <?php elseif($err !== ""): ?>
    <div class="alert alert-danger" role="alert">No se pudo procesar la renovación (<?php echo htmlspecialchars($err); ?>).</div>
    <?php endif; ?>

    <?php if(!$tienda): ?>
      <div class="alert alert-warning mb-0">No hay una tienda activa en este contexto.</div>
    <?php else: ?>

    <div class="row row-cards">
      <div class="col-lg-5">
        <div class="card">
          <div class="card-status-top <?php echo $modo==="demo" ? "bg-yellow" : "bg-green"; ?>"></div>
          <div class="card-header">
            <h3 class="card-title">Estado actual</h3>
            <div class="card-actions">
              <span class="badge <?php echo strtolower($tienda->estatus)==="activa" ? "bg-green" : "bg-red"; ?>"><?php echo htmlspecialchars($tienda->estatus); ?></span>
            </div>
          </div>
          <div class="card-body">
            <div class="mb-2"><span class="text-secondary">Tienda:</span> <b><?php echo htmlspecialchars($tienda->nombre); ?></b></div>
            <div class="mb-2"><span class="text-secondary">Subdominio:</span> <?php echo htmlspecialchars($tienda->subdomain); ?></div>
            <div class="mb-2"><span class="text-secondary">Plan:</span> <?php echo $cur_plan_obj ? htmlspecialchars($cur_plan_obj->name) : "—"; ?></div>
            <div class="mb-2">
              <span class="text-secondary">Periodo:</span>
              <?php if($sus && $sus->periodo_fin): ?>
                <?php echo htmlspecialchars(SuscripcionData::fechaFmt($sus->periodo_inicio)); ?> →
                <b><?php echo htmlspecialchars(SuscripcionData::fechaFmt($sus->periodo_fin)); ?></b>
                <?php $d = SuscripcionData::diasRestantes($sus->periodo_fin); ?>
                <?php if($d !== null): ?>
                  <span class="ms-1 badge <?php echo $d<0 ? "bg-red" : ($d<=SuscripcionData::diasAviso() ? "bg-yellow" : "bg-green"); ?>">
                    <?php echo $d<0 ? "vencido hace ".abs($d)."d" : $d."d restantes"; ?>
                  </span>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-secondary">sin periodo registrado</span>
              <?php endif; ?>
            </div>
            <?php if($modo === "demo"): ?>
            <div class="alert alert-info mt-3 mb-0">
              <i class="bi bi-info-circle me-1"></i><b>Modo demo.</b> La renovación se activa al instante (sin cobro real).
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card">
          <div class="card-status-top bg-primary"></div>
          <div class="card-header"><h3 class="card-title">Elige tu plan</h3></div>
          <form method="post" action="./?action=renovar">
            <div class="card-body">
              <?php if(count($plans) == 0): ?>
                <div class="alert alert-warning mb-0">No hay planes activos.</div>
              <?php else: ?>
              <div class="row g-3">
                <?php foreach($plans as $pl): $of = PlanData::offerInfo($pl); $prec = $of ? $of["price"] : (float)$pl->price; ?>
                <div class="col-md-6">
                  <label class="form-selectgroup-item d-block">
                    <input type="radio" name="plan_id" value="<?php echo (int)$pl->id; ?>" class="form-selectgroup-input"
                      <?php if((int)$pl->id === $cur_plan){ echo "checked"; } ?>>
                    <span class="form-selectgroup-label d-block p-3">
                      <span class="d-flex align-items-center mb-1">
                        <span class="h3 mb-0 me-2"><?php echo htmlspecialchars($pl->name); ?></span>
                        <span class="ms-auto text-end">
                          <?php if($of): ?><s class="text-secondary me-1">$<?php echo number_format($of["regular"],2); ?></s><?php endif; ?>
                          <span class="h3 mb-0">$<?php echo number_format($prec,2); ?></span>
                        </span>
                      </span>
                      <?php if($of): ?>
                      <span class="badge bg-green mb-1"><?php echo htmlspecialchars($of["label"]); ?><?php if($of["to"] !== ""): ?> · hasta <?php echo htmlspecialchars(PlatformData::fechaFmt($of["to"])); ?><?php endif; ?></span>
                      <?php endif; ?>
                      <span class="text-secondary small d-block">
                        <?php if($pl->description){ echo htmlspecialchars($pl->description)." · "; } ?>
                        <?php if($pl->max_products===null || $pl->max_products===""){ echo "Productos ilimitados"; } else { echo (int)$pl->max_products." productos"; } ?>
                        · <?php if($pl->max_sedes===null || $pl->max_sedes===""){ echo "sedes ilimitadas"; } else { echo (int)$pl->max_sedes." sede(s)"; } ?>
                      </span>
                    </span>
                  </label>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="text-secondary small mt-3">El plan se activa por 30 días. Si aún tienes tiempo, se <b>suma</b> a tu periodo actual.</div>
              <?php endif; ?>
            </div>
            <?php if(count($plans) > 0): ?>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-credit-card me-1"></i><?php echo $modo==="demo" ? "Activar (demo)" : "Pagar con Zenobank"; ?>
              </button>
            </div>
            <?php endif; ?>
          </form>
        </div>
        <div class="text-secondary small mt-3">
          <i class="bi bi-shield-lock me-1"></i>Pago procesado por Zenobank. Al aprobarse, tu subdominio se reactiva automáticamente.
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
