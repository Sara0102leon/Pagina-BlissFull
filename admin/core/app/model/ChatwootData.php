<?php
class ChatwootData {

	// ------------------------------------------------------------
	// Configuración
	// ------------------------------------------------------------
	public static function getConfig($name, $default=""){
		$cfg = ConfigurationData::getByPreffix($name);
		if($cfg && $cfg->val!==null && trim($cfg->val)!== ""){
			return trim($cfg->val);
		}
		return $default;
	}

	public static function baseUrl(){
		return rtrim(self::getConfig("general_chatwoot_base_url","https://chat.alianzablissful.com"),"/");
	}

	public static function accountId(){
		return intval(self::getConfig("general_chatwoot_account_id","1"));
	}

	public static function accessToken(){
		return self::getConfig("general_chatwoot_access_token","");
	}

	public static function webhookSecret(){
		return self::getConfig("general_chatwoot_webhook_secret","");
	}

	public static function appToken(){
		return self::getConfig("general_chatwoot_app_token","");
	}

	// ------------------------------------------------------------
	// Seguridad del webhook (firma HMAC X-Chatwoot-Signature)
	// Formato recibido: sha256=<hex>
	// ------------------------------------------------------------
	public static function verifySignature($payload, $timestamp, $signature){
		$secret = self::webhookSecret();
		if($secret===""){ return false; }
		if(!preg_match('/^sha256=([0-9a-f]{64})$/i', trim($signature), $m)){ return false; }
		$expected = hash_hmac("sha256", $timestamp.".".$payload, $secret);
		return hash_equals($expected, strtolower($m[1]));
	}

	// ------------------------------------------------------------
	// Parseo de keywords en mensajes salientes del operador
	// ------------------------------------------------------------
	public static function parseKeyword($content){
		$text = strtolower(trim($content));
		$text = preg_replace('/[áà]/u','a',$text);
		$text = preg_replace('/[éè]/u','e',$text);
		$text = preg_replace('/[íì]/u','i',$text);
		$text = preg_replace('/[óò]/u','o',$text);
		$text = preg_replace('/[úùü]/u','u',$text);
		$text = preg_replace('/[^a-z0-9 ]/',' ',$text);
		$text = preg_replace('/\s+/',' ',trim($text));

		if(strpos($text,"pago recibido")!==false || strpos($text,"pago confirmado")!==false
			|| strpos($text,"pagado")!==false && strpos($text,"pago")!==false){
			return "pago_recibido";
		}
		if(strpos($text,"pedido enviado")!==false || strpos($text,"enviado")!==false){
			return "enviado";
		}
		if(strpos($text,"finalizado")!==false || strpos($text,"vendido")!==false || strpos($text,"entregado")!==false){
			return "finalizado";
		}
		if(strpos($text,"cancelado")!==false || strpos($text,"cancelar")!==false){
			return "cancelado";
		}
		return null;
	}

	// ------------------------------------------------------------
	// Detección de #CÓDIGO dentro de un mensaje
	// ------------------------------------------------------------
	public static function extractCode($content){
		if(preg_match('/#([A-Za-z0-9_-]{11})/', $content, $m)){
			return $m[1];
		}
		return null;
	}

	// ------------------------------------------------------------
	// Construcción del resumen que se envía al grupo (delivery)
	// ------------------------------------------------------------
	public static function buildGroupMessage($buy){
		$sede = $buy->getSede();
		$client = $buy->getClient();
		$paymethod = $buy->getPaymethod();
		$coin = ConfigurationData::getByPreffix("general_coin")->val;

		$lines = array();
		$lines[] = "PEDIDO PAGADO - ALIANZAS BLISSFUL";
		$lines[] = "------------------------------------";
		$lines[] = "Sede: ".($sede?$sede->name:"-");
		$lines[] = "Cliente: ".($client?trim($client->name." ".$client->lastname):"-");
		$lines[] = "Teléfono: ".($client && $client->phone?$client->phone:"-");
		if($buy->delivery_zone_id && $buy->getDeliveryZone()){
			$lines[] = "Zona: ".$buy->getDeliveryZone()->name;
		}
		if(!empty($client->address)){
			$lines[] = "Dirección: ".$client->address;
		}
		if($sede && !empty($sede->maps)){
			$lines[] = "Maps: ".$sede->maps;
		}
		if(!empty($buy->scheduled_at)){
			$lines[] = "Programado: ".date("d/m/Y h:i A", strtotime($buy->scheduled_at));
		}
		$lines[] = "Pago: ".($paymethod?$paymethod->name:"-");
		$lines[] = "Total: ".$coin." ".number_format($buy->getTotal(),2,".",",");
		if(!empty($buy->note)){
			$lines[] = "Nota: ".$buy->note;
		}
		return implode("\n", $lines);
	}

	// ------------------------------------------------------------
	// Aplica una transición de estado a un buy según keyword/acción.
	// Retorna: array(status, changed, group_sent)
	// ------------------------------------------------------------
	public static function applyTransition($buy, $keyword){
		$cur = intval($buy->status_id);
		$changed = false;
		$group_sent = false;

		switch($keyword){
			case "pago_recibido":
				if($cur === 1){
					$buy->status_id = 2;
					$buy->change_status();
					$changed = true;
					// Resumen de entrega al grupo de delivery de la sede
					$sede = $buy->getSede();
					$group_id = $sede ? $sede->chatwoot_group_conversation_id : null;
					if($group_id){
						$group_sent = self::sendMessage($group_id, self::buildGroupMessage($buy));
					}
				}
				break;

			case "enviado":
				if($cur >= 2 && $cur !== 5){
					$buy->cascadeToFinal();
					$changed = true;
				}
				break;

			case "finalizado":
				if($cur >= 2 && $cur !== 5){
					$buy->status_id = 5;
					$buy->change_status();
					$changed = true;
				}
				break;

			case "cancelado":
				if($cur === 1){
					$buy->status_id = 3;
					$buy->change_status();
					$changed = true;
				}
				break;
		}

		return array("status"=>intval($buy->status_id), "changed"=>$changed, "group_sent"=>$group_sent);
	}

	// ------------------------------------------------------------
	// Envío de mensaje a una conversación via API de Chatwoot
	// ------------------------------------------------------------
	public static function sendMessage($conversationId, $content){
		$token = self::accessToken();
		if($token==="" || !$conversationId){ return false; }
		$url = self::baseUrl()."/api/v1/accounts/".self::accountId()."/conversations/".$conversationId."/messages";
		$data = json_encode(array("content"=>$content));

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"api_access_token: ".$token
		));
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		$resp = curl_exec($ch);
		$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return ($http>=200 && $http<300);
	}

	// ------------------------------------------------------------
	// Resumen de venta para la Dashboard App
	// ------------------------------------------------------------
	public static function saleInfo($buy){
		$sede = $buy->getSede();
		$client = $buy->getClient();
		$paymethod = $buy->getPaymethod();
		$coin = ConfigurationData::getByPreffix("general_coin")->val;
		$status = StatusData::getById($buy->status_id);

		return array(
			"id" => $buy->id,
			"code" => $buy->code,
			"status" => intval($buy->status_id),
			"status_name" => ($status?$status->name:-1),
			"client" => ($client?trim($client->name." ".$client->lastname):""),
			"phone" => ($client?$client->phone:""),
			"address" => ($client?$client->address:""),
			"sede" => ($sede?$sede->name:""),
			"sede_maps" => ($sede?$sede->maps:""),
			"zona" => ($buy->delivery_zone_id && $buy->getDeliveryZone()?$buy->getDeliveryZone()->name:""),
			"scheduled_at" => ($buy->scheduled_at?date("d/m/Y h:i A", strtotime($buy->scheduled_at)):""),
			"note" => $buy->note,
			"paymethod" => ($paymethod?$paymethod->name:""),
			"total" => $coin." ".number_format($buy->getTotal(),2,".",","),
			"created_at" => $buy->created_at,
			"chatwoot_group_conversation_id" => ($sede?$sede->chatwoot_group_conversation_id:null)
		);
	}
}
