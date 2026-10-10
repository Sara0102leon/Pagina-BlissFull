<?php
// checkout-status.php — GET: estado del checkout (para la pantalla "verificando" tras volver de Zenobank)
// demo_* -> lee la BD. ch_* -> consulta Zenobank y reconcilia la BD (refuerzo del webhook).
require_once __DIR__ . "/_bootstrap.php";

$checkoutId = isset($_GET["checkout"]) ? trim((string)$_GET["checkout"]) : "";
if($checkoutId === ""){
	saz_json(array("ok" => false, "error" => "checkout requerido"), 400);
}

$res = array("ok" => true, "status" => "OPEN", "checkout" => $checkoutId);
$tieneDatos = false;

$sus = SuscripcionData::getByCheckout($checkoutId);

// ---------- demo: estado desde la BD ----------
if(strpos($checkoutId, "demo_") === 0){
	if(!$sus){ saz_json(array("ok" => false, "error" => "checkout no encontrado"), 404); }
	$res["status"] = $sus->zeno_status;
	$tieneDatos = true;

// ---------- live: consultar a Zenobank ----------
}else{
	$apiKey = saz_config("zeno_api_key");
	if($apiKey === ""){ saz_json(array("ok" => false, "error" => "pago no configurado"), 503); }

	$zeno = new ZenoBank($apiKey);
	$chk = $zeno->getCheckout($checkoutId);
	if(!$chk || empty($chk["ok"])){
		saz_json(array("ok" => false, "error" => "No pudimos consultar el estado"), 502);
	}
	$status = strtoupper((string)(isset($chk["status"]) ? $chk["status"] : "OPEN"));
	$res["status"] = $status;

	// si Zenobank ya lo ve pagado y la BD no, lo marcamos aquí (refuerzo del webhook)
	if($sus && $status === "COMPLETED" && $sus->zeno_status !== "COMPLETED"){
		$paid = isset($chk["paidAmount"]) ? $chk["paidAmount"] : 0;
		$sus->markCompleted($paid);
		$tienda = TiendaData::getById($sus->tienda_id);
		if($tienda){ $tienda->setEstatus("activa"); }
	}
	$tieneDatos = true;
}

// datos de la tienda para el mensaje de éxito
if($tieneDatos && $sus && $sus->tienda_id){
	$tienda = TiendaData::getById($sus->tienda_id);
	if($tienda){
		$res["subdominio"] = $tienda->subdomain . "." . saz_config("saas_base_domain", "sazon.app");
		$res["nombre"]     = $tienda->nombre;
	}
}

saz_json($res);