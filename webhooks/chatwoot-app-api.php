<?php
// API interna para la Dashboard App "Blissfull"
// Autenticación: ?token=<general_chatwoot_app_token>
// Endpoints:
//   ?action=info&id=<conversation_display_id>     -> info de venta vinculada
//   ?action=status&id=<conversation_display_id>&status=<keyword>
//         keyword: pago_recibido | enviado | finalizado | cancelado
//   ?action=config                                -> mensaje editable actual
//   ?action=save_config  (POST msg=...)           -> guardar mensaje editable

require_once __DIR__ . "/_bootstrap.php";

header("Content-Type: application/json; charset=utf-8");

$token = isset($_GET["token"]) ? $_GET["token"] : "";
if(ChatwootData::appToken()==="" || $token !== ChatwootData::appToken()){
	http_response_code(403);
	echo json_encode(array("ok"=>false,"error"=>"unauthorized"));
	exit;
}

$action = isset($_GET["action"]) ? $_GET["action"] : "";

// ---------- CONFIG (mensaje editable) ----------
if($action === "config"){
	echo json_encode(array(
		"ok"=>true,
		"msg_enviado"=>ChatwootData::getConfig("general_chatwoot_msg_enviado",
			"Tu pedido #CODIGO ha sido enviado. ¡Gracias por tu compra!")
	));
	exit;
}

if($action === "save_config"){
	$msg = isset($_POST["msg"]) ? $_POST["msg"] : (isset($_GET["msg"]) ? $_GET["msg"] : null);
	if($msg === null){
		echo json_encode(array("ok"=>false,"error"=>"no msg"));
		exit;
	}
	ConfigurationData::updateValFromName("general_chatwoot_msg_enviado", trim($msg));
	echo json_encode(array("ok"=>true,"msg_enviado"=>trim($msg)));
	exit;
}

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
	$res = ChatwootData::applyTransition($buy, $keyword, $id);
	$res["sale"] = ChatwootData::saleInfo($buy);
	$labels = array("pago_recibido"=>"Marcado como Pagado","enviado"=>"Marcado como Enviado","finalizado"=>"Marcado como Finalizado","cancelado"=>"Pedido cancelado");
	echo json_encode(array_merge(array("ok"=>true,"label"=>$labels[$keyword]), $res));
	exit;
}

echo json_encode(array("ok"=>false,"error"=>"invalid action"));
exit;
