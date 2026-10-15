<?php
// zenobank.php — Webhook receptor de Zenobank (firma Svix)
// URL a registrar en dashboard.zenobank.io → Developers: https://<tu-dominio>/webhooks/zenobank.php
// Eventos: checkout.completed, checkout.expired, checkout.partially_paid
// Al aprobarse el pago: activa la tienda (subdominio) y marca la suscripción COMPLETED.

require_once __DIR__ . "/_bootstrap.php";

// _bootstrap.php (webhooks) no trae los helpers SAAS ni ZenoBank
require_once $BOOT_ROOT . "/admin/core/controller/ZenoBank.php";
if(!function_exists("saz_config")){
	function saz_config($name, $def = ""){
		static $cache = null;
		if($cache === null){
			$cache = array();
			$query = Executor::doit("select name,val from configuration where name like 'zeno_%' or name like 'saas_%'");
			if($query[0]){
				while($row = $query[0]->fetch_assoc()){
					$cache[$row["name"]] = $row["val"];
				}
			}
		}
		return (isset($cache[$name]) && $cache[$name] !== "") ? $cache[$name] : $def;
	}
}

http_response_code(200);
header("Content-Type: application/json");

$rawBody = file_get_contents("php://input");
if($rawBody === false || $rawBody === ""){
	echo json_encode(array("ok" => false, "error" => "empty body"));
	exit;
}

$secret = saz_config("zeno_webhook_secret", "");
if($secret === ""){
	http_response_code(501);
	echo json_encode(array("ok" => false, "error" => "webhook secret not configured"));
	exit;
}

$svixId  = isset($_SERVER["HTTP_SVIX_ID"])        ? $_SERVER["HTTP_SVIX_ID"]        : "";
$svixTs  = isset($_SERVER["HTTP_SVIX_TIMESTAMP"]) ? $_SERVER["HTTP_SVIX_TIMESTAMP"] : "";
$svixSig = isset($_SERVER["HTTP_SVIX_SIGNATURE"]) ? $_SERVER["HTTP_SVIX_SIGNATURE"] : "";

if(!ZenoBank::verifyWebhook($rawBody, $svixId, $svixTs, $svixSig, $secret)){
	http_response_code(403);
	echo json_encode(array("ok" => false, "error" => "invalid signature"));
	exit;
}

$event = json_decode($rawBody, true);
if(!is_array($event)){
	echo json_encode(array("ok" => false, "error" => "bad json"));
	exit;
}

$type = isset($event["type"]) ? (string)$event["type"] : "";
$data = (isset($event["data"]) && is_array($event["data"])) ? $event["data"] : array();

$checkoutId = isset($data["id"]) ? (string)$data["id"] : "";
$status     = isset($data["status"]) ? strtoupper((string)$data["status"]) : "";

if($checkoutId === ""){
	echo json_encode(array("ok" => true, "ignored" => "empty checkout id"));
	exit;
}

$sus = SuscripcionData::getByCheckout($checkoutId);
if(!$sus){
	// Fallback: si el dueño generó un segundo checkout, el primero quedó
	// huérfano en `zeno_checkout_id`. El orderId viaja en el payload y lleva
	// el id de la fila ("saz-<tienda>-<sus>" / "renov-<tienda>-<sus>-<ts>").
	if(preg_match('/\b(?:saz|renov)-\d+-(\d+)(?:-\d+)?\b/', $rawBody, $m)){
		$sus = SuscripcionData::getById((int)$m[1]);
	}
}
if(!$sus){
	echo json_encode(array("ok" => true, "ignored" => "no subscription for checkout"));
	exit;
}

$newStatus = "OPEN";
if($status === "COMPLETED" || $type === "checkout.completed"){          $newStatus = "COMPLETED"; }
elseif($status === "EXPIRED" || $type === "checkout.expired"){          $newStatus = "EXPIRED"; }
elseif($status === "PARTIALLY_PAID" || $type === "checkout.partially_paid"){ $newStatus = "PARTIALLY_PAID"; }

if($newStatus === "COMPLETED" && $sus->zeno_status !== "COMPLETED"){
	// pago aprobado -> activa la tienda y la suscripción
	$paid = isset($data["paidAmount"]) ? $data["paidAmount"] : 0;
	$sus->markCompleted($paid);
	$tienda = TiendaData::getById($sus->tienda_id);
	if($tienda){ $tienda->setEstatus("activa"); }
	echo json_encode(array("ok" => true, "status" => $newStatus, "checkout" => $checkoutId));
	exit;
}

if($newStatus !== $sus->zeno_status){
	$sus->setStatus($newStatus);
}

echo json_encode(array("ok" => true, "status" => $newStatus, "checkout" => $checkoutId));