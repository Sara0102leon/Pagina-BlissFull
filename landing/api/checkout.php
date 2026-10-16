<?php
// checkout.php — POST: registra la tienda + suscripción y crea el pago en Zenobank
// Demo: activa al instante. Live: redirige al checkoutUrl de Zenobank.
require_once __DIR__ . "/_bootstrap.php";

if($_SERVER["REQUEST_METHOD"] !== "POST"){
	saz_json(array("ok" => false, "error" => "Método no permitido"), 405);
}

$raw = file_get_contents("php://input");
$in = json_decode($raw, true);
if(!is_array($in)){
	saz_json(array("ok" => false, "error" => "JSON inválido"), 400);
}

$planCode  = isset($in["plan"])       ? trim((string)$in["plan"]) : "";
$nombre    = isset($in["nombre"])     ? trim((string)$in["nombre"]) : "";
$subdomain = isset($in["subdominio"]) ? strtolower(trim((string)$in["subdominio"])) : "";
$email     = isset($in["email"])      ? trim((string)$in["email"]) : "";
$password  = isset($in["password"])   ? (string)$in["password"] : "";

// ---------- validaciones ----------
if($planCode === ""){
	saz_json(array("ok" => false, "error" => "Selecciona un plan", "field" => "plan"), 400);
}
$plan = PlanData::getByCode($planCode);
if(!$plan){
	saz_json(array("ok" => false, "error" => "El plan no existe", "field" => "plan"), 400);
}

if(strlen($nombre) < 3){
	saz_json(array("ok" => false, "error" => "Escribe el nombre de tu local (mínimo 3 letras)", "field" => "nombre"), 400);
}

if($subdomain === ""){ $subdomain = saz_slugify($nombre); }
if($subdomain === "" || !preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $subdomain)){
	saz_json(array("ok" => false, "error" => "El subdominio solo admite letras, números y guiones", "field" => "subdominio"), 400);
}

// subdominio único: si está tomado se agrega -2, -3...
$base = $subdomain;
$i = 1;
while(!TiendaData::subdomainFree($subdomain)){
	$i++;
	$subdomain = substr($base, 0, 60) . "-" . $i;
	if($i > 50){
		saz_json(array("ok" => false, "error" => "Subdominio no disponible, elige otro", "field" => "subdominio"), 409);
	}
}

if($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)){
	saz_json(array("ok" => false, "error" => "Email no válido", "field" => "email"), 400);
}
if($password !== "" && strlen($password) < 6){
	saz_json(array("ok" => false, "error" => "La contraseña debe tener al menos 6 caracteres", "field" => "password"), 400);
}

// ---------- modo demo / live ----------
$apiKey = saz_config("zeno_api_key");
$mode   = saz_config("zeno_mode", "demo");
if($mode !== "live" || $apiKey === ""){ $mode = "demo"; }

// ---------- crear la tienda ----------
$tienda = new TiendaData();
$tienda->subdomain = saz_q($subdomain);
$tienda->nombre    = saz_q($nombre);
$tienda->email     = saz_q($email);
$tienda->estatus   = ($mode === "demo") ? "activa" : "pendiente";
$tienda->zeno_mode = $mode;
$r = $tienda->add();
if(!$r[0]){
	saz_json(array("ok" => false, "error" => "No se pudo crear tu tienda, inténtalo de nuevo"), 500);
}
$tiendaId = (int)$r[1];

// ---------- usuario dueño (solo si email + contraseña) ----------
if($email !== "" && $password !== ""){
	$pass = sha1(md5($password)); // mismo hash que el login admin
	$sql = "insert into user (name,username,email,password,is_active,is_admin,tienda_id,role,created_at) ";
	$sql .= "value (\"".saz_q($nombre)."\",\"".saz_q($email)."\",\"".saz_q($email)."\",\"$pass\",1,0,$tiendaId,'dueno',NOW())";
	Executor::doit($sql); // si el email ya existe fallará el unique; no bloquea la tienda
}

// ---------- suscripción ----------
$sus = new SuscripcionData();
$sus->tienda_id = $tiendaId;
$sus->plan_id   = (int)$plan->id;
$r3 = $sus->add();
if(!$r3[0]){
	saz_json(array("ok" => false, "error" => "No se pudo crear la suscripción"), 500);
}
$susId = (int)$r3[1];

$baseDomain     = saz_config("saas_base_domain", "sazon.app");
$subdominioText = $subdomain . "." . $baseDomain;

// ---------- DEMO: activación inmediata ----------
$precio = PlanData::effectivePrice($plan); // oferta vigente si la hay
if($mode === "demo"){
	$checkoutId = "demo_" . bin2hex(random_bytes(6));
	$sus->id = $susId;
	$sus->setCheckoutId($checkoutId);
	$sus->markCompleted($precio);
	saz_json(array(
		"ok"         => true,
		"demo"       => true,
		"tienda"     => $subdomain,
		"subdominio" => $subdominioText,
	));
}

// ---------- LIVE: crear checkout en Zenobank ----------
$currency = saz_config("zeno_price_currency", "USD");
$landing  = rtrim(saz_config("saas_landing_url", ""), "/");
if($landing === ""){
	// fallback: construir desde el host actual (sirve igual en producción)
	$proto = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
	$dir   = dirname($_SERVER["SCRIPT_NAME"]); // .../landing/api
	$landing = $proto . "://" . $_SERVER["HTTP_HOST"] . rtrim(substr($dir, 0, -4), "/");
}
$successUrl = $landing . "/?pago=exito&tienda=" . rawurlencode($subdomain);

$orderId = "saz-" . $tiendaId . "-" . $susId;
$zeno = new ZenoBank($apiKey);
$checkout = $zeno->createCheckout($orderId, $precio, $currency, $successUrl);

if(!$checkout || empty($checkout["ok"]) || empty($checkout["id"]) || empty($checkout["checkoutUrl"])){
	error_log("[saz] zenobank create falló: " . json_encode($checkout));
	saz_json(array("ok" => false, "error" => "No pudimos conectar con Zenobank. Inténtalo en un momento."), 502);
}

$sus->id = $susId;
$sus->setCheckoutId($checkout["id"]);

saz_json(array(
	"ok"          => true,
	"live"        => true,
	"checkoutId"  => $checkout["id"],
	"checkoutUrl" => $checkout["checkoutUrl"],
	"tienda"      => $subdomain,
	"subdominio"  => $subdominioText,
));