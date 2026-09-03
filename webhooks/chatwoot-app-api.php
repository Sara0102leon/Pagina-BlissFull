<?php
// API interna para la Dashboard App "Blissfull"
// Autenticación: ?token=<general_chatwoot_app_token>
// Endpoints:
//   ?action=info&id=<conversation_display_id>     -> info de venta vinculada
//   ?action=status&id=<conversation_display_id>&status=<keyword>
//         keyword: pago_recibido | enviado | finalizado | cancelado

require_once __DIR__ . "/_bootstrap.php";

header("Content-Type: application/json; charset=utf-8");

$token = isset($_GET["token"]) ? $_GET["token"] : "";
if(ChatwootData::appToken()==="" || $token !== ChatwootData::appToken()){
	http_response_code(403);
	echo json_encode(array("ok"=>false,"error"=>"unauthorized"));
	exit;
}

$action = isset($_GET["action"]) ? $_GET["action"] : "";
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if(!$id){
	echo json_encode(array("ok"=>false,"error"=>"no id"));
	exit;
}

// ---------- INFO ----------
if($action === "info"){
	$buy = BuyData::getByConversationId($id);
	if(!$buy){
		echo json_encode(array("ok"=>true,"linked"=>false,"message"=>"No hay una venta vinculada a esta conversación."));
		exit;
	}
	echo json_encode(array("ok"=>true,"linked"=>true,"sale"=>ChatwootData::saleInfo($buy)));
	exit;
}

// ---------- STATUS ----------
if($action === "status"){
	$buy = BuyData::getByConversationId($id);
	if(!$buy){
		echo json_encode(array("ok"=>false,"error"=>"no_linked_buy"));
		exit;
	}
	$keyword = isset($_GET["status"]) ? $_GET["status"] : "";
	if(!in_array($keyword, array("pago_recibido","enviado","finalizado","cancelado"))){
		echo json_encode(array("ok"=>false,"error"=>"invalid status"));
		exit;
	}
	$res = ChatwootData::applyTransition($buy, $keyword);
	$res["sale"] = ChatwootData::saleInfo($buy);
	echo json_encode(array_merge(array("ok"=>true), $res));
	exit;
}

echo json_encode(array("ok"=>false,"error"=>"invalid action"));
exit;
