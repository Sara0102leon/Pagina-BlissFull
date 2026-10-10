<?php
// _bootstrap.php — Bootstrap mínimo para los endpoints JSON de la landing (A1: registro + pago)
// Expone: Database, Executor, Model, autoload de modelos (admin/core/app/model) y ZenoBank.

error_reporting(E_ALL);
ini_set("display_errors", 0); // JSON limpio; los errores quedan en el log
date_default_timezone_set("America/Caracas");

$BOOT_ROOT = realpath(__DIR__ . "/../..");
if(!$BOOT_ROOT){
	http_response_code(500);
	header("Content-Type: application/json; charset=utf-8");
	exit('{"ok":false,"error":"root not found"}');
}

chdir($BOOT_ROOT);
include $BOOT_ROOT . "/core/autoload.php";       // Database real + Executor + Model (modelos -> admin/core/app/model)
include $BOOT_ROOT . "/core/app/autoload.php";   // autoloader SPL de modelos
require_once $BOOT_ROOT . "/admin/core/controller/ZenoBank.php";

// Config SAAS/Zenobank con caché estática (claves zeno_* / saas_* en configuration)
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

function saz_json($data, $code = 200){
	http_response_code($code);
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit;
}

// Escape para SQL (MySQLi). Los modelos del proyecto no escapan; los endpoints
// públicos sí deben hacerlo en lo que entra por HTTP.
function saz_q($v){
	return Database::getCon()->real_escape_string((string)$v);
}

// "Hamburguesa 2x" -> "hamburguesa-2x" (para subdominios)
function saz_slugify($text){
	$text = strtolower(trim($text));
	$text = strtr($text, array(
		"á"=>"a","é"=>"e","í"=>"i","ó"=>"o","ú"=>"u","ü"=>"u","ñ"=>"n",
		"Á"=>"a","É"=>"e","Í"=>"i","Ó"=>"o","Ú"=>"u","Ü"=>"u","Ñ"=>"n",
	));
	$text = preg_replace('/[^a-z0-9]+/', "-", $text);
	$text = trim($text, "-");
	$text = substr($text, 0, 63);
	return $text;
}