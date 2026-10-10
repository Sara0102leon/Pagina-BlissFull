<?php
// config.php — GET: modo de pago, dominio base y planes activos (para el modal de la landing)
require_once __DIR__ . "/_bootstrap.php";

$apiKey = saz_config("zeno_api_key");
$mode   = saz_config("zeno_mode", "demo");
if($mode !== "live" || $apiKey === ""){ $mode = "demo"; }

$plans = PlanData::getAllActives();
$out = array();
foreach($plans as $p){
	$out[] = array(
		"code"     => $p->code,
		"name"     => $p->name,
		"price"    => $p->price,
		"currency" => $p->currency,
	);
}

saz_json(array(
	"ok"         => true,
	"mode"       => $mode,
	"currency"   => saz_config("zeno_price_currency", "USD"),
	"symbol"     => saz_config("zeno_currency_symbol", "$"),
	"baseDomain" => saz_config("saas_base_domain", "sazon.app"),
	"plans"      => $out,
));