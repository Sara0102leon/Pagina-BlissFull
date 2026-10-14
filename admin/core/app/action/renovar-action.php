<?php
/* ══════════════════════════════════════════════════════════════════
   renovar-action.php — Renovación / activación del plan (Fase 6)
   (?action=renovar)  ·  Lo envía el formulario de ?view=renovar.

   Demo: activa/extiende al instante y vuelve a la vista con pago=exito.
   Live: crea el checkout en Zenobank y redirige a su página de pago.
   Al aprobarse el pago, el webhook (webhooks/zenobank.php) extiende el
   periodo y reactiva la tienda.
   ══════════════════════════════════════════════════════════════════ */
if(!isset($_SESSION["user_id"])){ Core::redir("../login.php"); exit; }
$_rv_me = UserData::getById($_SESSION["user_id"]);
if(!$_rv_me){ Core::redir("../login.php"); exit; }
$_rv_role = ($_rv_me->role !== "") ? $_rv_me->role : "dueno";
if(!in_array($_rv_role, array("dueno","superadmin"))){ Core::redir("./"); exit; }

if($_SERVER["REQUEST_METHOD"] !== "POST"){ Core::redir("./?view=renovar"); exit; }

// Base URL del panel admin (para el retorno desde Zenobank)
function rv_admin_url(){
	$proto = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
	$host  = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost";
	$dir   = str_replace("\\", "/", dirname(isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : "/admin/index.php"));
	$dir   = rtrim($dir, "/");
	if($dir === "." ){ $dir = ""; }
	return $proto . "://" . $host . $dir;
}

$tienda = Tenant::current();
if(!$tienda){ Core::redir("./?view=renovar&err=sin-tienda"); exit; }
$tienda_id = (int)$tienda->id;

$plan_id = isset($_POST["plan_id"]) ? (int)$_POST["plan_id"] : 0;
$plan = $plan_id > 0 ? PlanData::getById($plan_id) : null;
if(!$plan || !$plan->is_active){ Core::redir("./?view=renovar&err=sin-plan"); exit; }

// Modo de cobro (config de plataforma cfg_id=1)
$modo = PlatformData::cfg("zeno_mode", "demo");
$apiKey = trim(PlatformData::cfg("zeno_api_key", ""));
if($modo !== "live" || $apiKey === ""){ $modo = "demo"; }

// Suscripción vigente de la tienda (o se crea)
$sus = SuscripcionData::getLatestByTienda($tienda_id);
if(!$sus){
	$sus = new SuscripcionData();
	$sus->tienda_id = $tienda_id;
	$sus->plan_id   = (int)$plan->id;
	$r = $sus->add();
	if(!$r[0]){ Core::redir("./?view=renovar&err=db"); exit; }
	$sus->id = (int)$r[1];
}

// ── DEMO: activación inmediata ─────────────────────────────────────
if($modo === "demo"){
	$sus->openRenewal((int)$plan->id, "demo_" . bin2hex(random_bytes(6)));
	$sus->markCompleted($plan->price);
	$tienda->setEstatus("activa");
	Core::redir("./?view=renovar&pago=exito");
	exit;
}

// ── LIVE: crear checkout en Zenobank ───────────────────────────────
require_once dirname(__DIR__, 2) . "/controller/ZenoBank.php";

$currency  = PlatformData::cfg("zeno_price_currency", "USD");
$successUrl = rv_admin_url() . "/?view=renovar&pago=exito";
$orderId    = "renov-" . $tienda_id . "-" . (int)$sus->id . "-" . time();

$zeno = new ZenoBank($apiKey);
$checkout = $zeno->createCheckout($orderId, $plan->price, $currency, $successUrl);
if(!$checkout || empty($checkout["ok"]) || empty($checkout["id"]) || empty($checkout["checkoutUrl"])){
	error_log("[saz] renovar: zenobank create falló: " . json_encode($checkout));
	Core::redir("./?view=renovar&err=zeno");
	exit;
}

$sus->openRenewal((int)$plan->id, $checkout["id"]);
header("Location: " . $checkout["checkoutUrl"]);
exit;
