<?php
// ZenoBank.php — cliente del gateway de pagos Zenobank (https://docs.zenobank.io)
// - Crear checkout: POST /api/v1/checkouts  (auth X-API-Key: sk_...)
// - Estado público: GET  /api/v1/public/checkouts/{id}
// - Webhooks con firma Svix (svix-id, svix-timestamp, svix-signature)
class ZenoBank {

	const API_BASE = "https://api.zenobank.io/api/v1";

	public $apiKey;

	function __construct($apiKey){
		$this->apiKey = $apiKey;
	}

	// POST /checkouts — genera un link de pago para el cliente.
	// Devuelve el JSON de Zenobank con ok/_http añadidos: {id, checkoutUrl, status, ...}
	public function createCheckout($orderId, $amount, $currency, $successRedirectUrl = ""){
		$body = array(
			"orderId"       => $orderId,
			"priceAmount"   => number_format((float)$amount, 2, ".", ""),
			"priceCurrency" => $currency,
		);
		if($successRedirectUrl !== ""){
			$body["successRedirectUrl"] = $successRedirectUrl;
		}
		return $this->request("POST", "/checkouts", json_encode($body), true);
	}

	// GET /public/checkouts/{id} — estado del checkout (endpoint público, sin auth)
	public function getCheckout($checkoutId){
		return $this->request("GET", "/public/checkouts/" . rawurlencode($checkoutId), "", false);
	}

	// ---------- Webhooks (firma Svix) ----------
	// El secret tiene el formato whsec_<base64>. El mensaje firmado es:
	//   "{svix-id}.{svix-timestamp}.{rawBody}"
	// con HMAC-SHA256 y clave = base64_decode(secret sin el prefijo whsec_).
	// El header svix-signature trae una o más entradas "v1,<base64>".
	public static function verifyWebhook($rawBody, $svixId, $svixTimestamp, $svixSignature, $secret){
		if($secret === "" || (string)$svixId === "" || (string)$svixTimestamp === "" || (string)$svixSignature === ""){
			return false;
		}
		if(substr($secret, 0, 6) === "whsec_"){ $secret = substr($secret, 6); }
		$key = base64_decode($secret, true);
		if($key === false || $key === ""){ return false; }

		// tolerancia: la firma no puede tener más de 5 minutos
		$now = time();
		if(!is_numeric($svixTimestamp) || abs($now - (int)$svixTimestamp) > 300){ return false; }

		$signed = $svixId . "." . $svixTimestamp . "." . $rawBody;
		$expected = base64_encode(hash_hmac("sha256", $signed, $key, true));

		$entries = preg_split('/\s+/', trim($svixSignature));
		foreach((array)$entries as $entry){
			if($entry === ""){ continue; }
			$parts = explode(",", $entry, 2);
			if(count($parts) !== 2 || $parts[0] !== "v1"){ continue; }
			if(hash_equals($expected, $parts[1])){ return true; }
		}
		return false;
	}

	// ---------- helper HTTP ----------
	private function request($method, $path, $payload = "", $auth = true){
		$url = self::API_BASE . $path;
		$ch = curl_init($url);
		$headers = array("Accept: application/json", "Content-Type: application/json");
		if($auth){ $headers[] = "X-API-Key: " . $this->apiKey; }

		$opts = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_TIMEOUT        => 25,
			CURLOPT_SSL_VERIFYPEER => true,
		);
		if($method === "POST"){
			$opts[CURLOPT_POST] = true;
			$opts[CURLOPT_POSTFIELDS] = $payload;
		}else{
			$opts[CURLOPT_HTTPGET] = true;
		}
		curl_setopt_array($ch, $opts);

		$raw = curl_exec($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err = curl_error($ch);
		curl_close($ch);

		if($raw === false || $err !== ""){
			return array("ok" => false, "error" => "zenobank_http", "detail" => $err);
		}
		$json = json_decode($raw, true);
		if(!is_array($json)){
			return array("ok" => false, "error" => "zenobank_parse", "http" => $code, "raw" => substr($raw, 0, 200));
		}
		$json["_http"] = $code;
		$json["ok"] = ($code >= 200 && $code < 300);
		return $json;
	}
}