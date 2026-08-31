<?php
// Webhook receptor de Chatwoot (event: message_created)
// URL registrada en Chatwoot: https://prueba.alianzablissful.com/webhooks/chatwoot.php
// Autenticación: ?token=<general_chatwoot_app_token> O firma HMAC verifica validada

require_once __DIR__ . "/_bootstrap.php";

http_response_code(200);
header("Content-Type: application/json");

$payload = file_get_contents("php://input");
if($payload===false || $payload===""){
	echo json_encode(array("ok"=>false,"error"=>"empty body"));
	exit;
}

// ---------- Autenticación ----------
$token = isset($_GET["token"]) ? $_GET["token"] : "";
$appToken = ChatwootData::appToken();
$sig  = isset($_SERVER["HTTP_X_CHATWOOT_SIGNATURE"]) ? $_SERVER["HTTP_X_CHATWOOT_SIGNATURE"] : "";
$ts   = isset($_SERVER["HTTP_X_CHATWOOT_TIMESTAMP"]) ? $_SERVER["HTTP_X_CHATWOOT_TIMESTAMP"] : "";

$authorized = false;
if($appToken!=="" && $token===$appToken){ $authorized = true; }
elseif(ChatwootData::verifySignature($payload, $ts, $sig)){ $authorized = true; }

if(!$authorized){
	http_response_code(403);
	echo json_encode(array("ok"=>false,"error"=>"unauthorized"));
	exit;
}

// ---------- Parseo ----------
$data = json_decode($payload, true);
if(!is_array($data)){
	echo json_encode(array("ok"=>false,"error"=>"invalid json"));
	exit;
}
$event = isset($data["event"]) ? $data["event"] : "";
if($event !== "message_created"){
	echo json_encode(array("ok"=>true,"ignored"=>"event: ".$event));
	exit;
}

$conversation_id = isset($data["conversation"]["id"]) ? intval($data["conversation"]["id"]) : 0;
$message_type    = isset($data["message_type"]) ? intval($data["message_type"]) : -1;
$content         = isset($data["content"]) ? strval($data["content"]) : "";
$contact_id      = isset($data["sender"]["id"]) ? intval($data["sender"]["id"]) : "";

if(!$conversation_id){
	echo json_encode(array("ok"=>false,"error"=>"no conversation"));
	exit;
}

// ---------- 1) Mensaje ENTRANTE (cliente): enlazar por #CÓDIGO ----------
if($message_type === 0){
	$code = ChatwootData::extractCode($content);
	if($code){
		$buy = BuyData::getByCode($code);
		if($buy && intval($buy->chatwoot_conversation_id)===0){
			$buy->linkConversation($conversation_id, $contact_id);
		}
	}
	echo json_encode(array("ok"=>true,"action"=>"inbound"));
	exit;
}

// ---------- 2) Mensaje SALIENTE (operador): keywords ----------
if($message_type !== 1){
	echo json_encode(array("ok"=>true,"ignored"=>"message_type ".$message_type));
	exit;
}

$buy = BuyData::getByConversationId($conversation_id);
if(!$buy){
	echo json_encode(array("ok"=>true,"action"=>"no_linked_buy"));
	exit;
}
$keyword = ChatwootData::parseKeyword($content);
if(!$keyword){
	echo json_encode(array("ok"=>true,"action"=>"no_keyword"));
	exit;
}

$res = ChatwootData::applyTransition($buy, $keyword);
echo json_encode(array_merge(array("ok"=>true,"keyword"=>$keyword), $res));
exit;
