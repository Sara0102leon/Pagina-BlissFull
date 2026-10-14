<?php

date_default_timezone_set("America/Caracas");

if(isset($_SESSION["user_id"])){
	Core::$user = UserData::getById($_SESSION["user_id"]);
}

/* ── Multi-tenant (público) ───────────────────────────────────────────
   1) Subdominio de tienda inexistente -> 404 (sin caer al fallback).  */
$tenant = Tenant::current();
if($tenant === null && Tenant::$hostIsStore){
	http_response_code(404);
	header("Content-Type: text/html; charset=utf-8");
	header("X-Robots-Tag: noindex, nofollow");
	$host = isset($_SERVER["HTTP_HOST"]) ? htmlspecialchars($_SERVER["HTTP_HOST"], ENT_QUOTES, "UTF-8") : "";
	echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tienda no encontrada · sazón</title><style>html,body{margin:0;min-height:100vh}body{background:#0f0c09;color:#f5eee2;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;padding:24px}main{max-width:480px;text-align:center}.mark{display:inline-flex;align-items:center;gap:10px;margin-bottom:28px}h1{font-size:2.2rem;margin:0 0 10px;color:#e9b94a}p{color:#b3a795;line-height:1.6;margin:0 0 24px}code{color:#e0a96d;word-break:break-all}.btn{display:inline-block;background:#e9b94a;color:#1a130a;font-weight:700;text-decoration:none;padding:10px 22px;border-radius:8px}</style></head><body><main><div class="mark"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 56 56" role="img" aria-label="sazón"><rect x="4" y="4" width="48" height="48" rx="13" fill="#e9b94a" transform="rotate(-8 28 28)"/><text x="28" y="40" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="bold" text-anchor="middle" fill="#1a130a">s</text></svg><b style="font-size:1.5rem;letter-spacing:.06em">sazón</b></div><h1>Tienda no encontrada</h1><p>El subdominio <code>'.$host.'</code> no corresponde a ninguna tienda activa. Verificá la dirección o consultá con el negocio.</p><a class="btn" href="https://sazon.app">Ir a sazón</a></main></body></html>';
	exit;
}

/* 1b) Tienda existente pero NO disponible (suspendida / pendiente de pago):
       en un subdominio de tienda se publica una página "no disponible".     */
if($tenant !== null && Tenant::$hostIsStore
   && strtolower(trim((string)$tenant->estatus)) !== "activa"){
	http_response_code(503);
	header("Content-Type: text/html; charset=utf-8");
	header("X-Robots-Tag: noindex, nofollow");
	header("Retry-After: 86400");
	$nombre = htmlspecialchars($tenant->nombre, ENT_QUOTES, "UTF-8");
	echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tienda no disponible · sazón</title><style>html,body{margin:0;min-height:100vh}body{background:#0f0c09;color:#f5eee2;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;padding:24px}main{max-width:480px;text-align:center}.mark{display:inline-flex;align-items:center;gap:10px;margin-bottom:28px}h1{font-size:2.2rem;margin:0 0 10px;color:#e9b94a}p{color:#b3a795;line-height:1.6;margin:0 0 24px}.btn{display:inline-block;background:#e9b94a;color:#1a130a;font-weight:700;text-decoration:none;padding:10px 22px;border-radius:8px}</style></head><body><main><div class="mark"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 56 56" role="img" aria-label="sazón"><rect x="4" y="4" width="48" height="48" rx="13" fill="#e9b94a" transform="rotate(-8 28 28)"/><text x="28" y="40" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="bold" text-anchor="middle" fill="#1a130a">s</text></svg><b style="font-size:1.5rem;letter-spacing:.06em">sazón</b></div><h1>Menú no disponible</h1><p>El menú de <b>'.$nombre.'</b> no está disponible en este momento. Si eres el dueño, inicia sesión en tu panel para reactivar tu plan.</p><a class="btn" href="https://sazon.app">Ir a sazón</a></main></body></html>';
	exit;
}

/* 2) Carrito por tienda: si el carrito pertenece a otra tienda se descarta
      (se conserva el identificador de contrato $_SESSION["cart"]).       */
$curTenant = (int)Tenant::id();
if(isset($_SESSION["cart"])){
	$cartTenant = isset($_SESSION["cart_tienda"]) ? (int)$_SESSION["cart_tienda"] : 0;
	if($cartTenant !== $curTenant){
		unset($_SESSION["cart"]);
	}
}
$_SESSION["cart_tienda"] = $curTenant;

if(!isset($_GET["action"])){
//	Bootload::load("default");
	Module::loadLayout("index");
}else{
	Action::load($_GET["action"]);
}

?>