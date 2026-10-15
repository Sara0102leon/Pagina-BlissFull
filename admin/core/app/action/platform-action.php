<?php
/* ══════════════════════════════════════════════════════════════════
   platform-action.php — Acciones del panel de PLATAFORMA (Fase 6)
   Guard: sólo rol 'superadmin'.

   Rutas (?action=platform&opt=…):
     estatus   id, to=activa|pendiente|suspendida   -> cambia el estatus
     extender  id, dias                              -> extiende el periodo
     plan      id, plan_id                           -> cambia el plan
     recordar  id                                    -> marca aviso + abre WhatsApp
     entrar    id                                    -> "entrar como" (impersonar)
     salir                                           -> deja de impersonar
     crear     nombre, subdominio, plan_id, dias[, con_dueno, email, password]
     cfg       saas_dias_aviso                       -> configura el aviso
   ══════════════════════════════════════════════════════════════════ */
if(!isset($_SESSION["user_id"])){ Core::redir("../login.php"); exit; }
$_pf_me = UserData::getById($_SESSION["user_id"]);
if(!$_pf_me || $_pf_me->role !== "superadmin"){ Core::redir("./"); exit; }

function pf_esc($v){
	$con = Database::getCon();
	return $con ? $con->real_escape_string((string)$v) : addslashes((string)$v);
}
function pf_go($url){
	if(!headers_sent()){ header("Location: ".$url); } else { echo "<script>window.location='".$url."';</script>"; }
	exit;
}
function pf_back($opt, $msg, $ok = 1){
	Core::redir("./?view=platform".($opt ? "&opt=".$opt : "")."&ok=".(int)$ok."&msg=".rawurlencode($msg));
	exit;
}
function pf_base_url(){
	$proto = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
	return $proto . "://" . (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "localhost");
}
// URL de la página de acceso (login.php) de la plataforma
function pf_login_url(){
	$d = dirname(isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : "/admin/index.php"); // /menu/admin
	$d = dirname($d);                                                                              // /menu
	if($d === "/" || $d === "\\" || $d === "."){ $d = ""; }
	return pf_base_url() . rtrim($d, "/\\") . "/login.php";
}

$opt = isset($_GET["opt"]) ? $_GET["opt"] : "";

// ── Cambiar estatus de una tienda ──────────────────────────────────
if($opt === "estatus"){
	$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
	$to = isset($_GET["to"]) ? $_GET["to"] : "";
	if($id > 0 && in_array($to, array("activa","pendiente","suspendida"))){
		$t = TiendaData::getById($id);
		if($t){
			// Nunca dejar la plataforma sin ninguna tienda activa: sin fallback
			// el menú público queda en 404 global (auditoría Fase 6 #3).
			if($to !== "activa" && strtolower(trim($t->estatus)) === "activa"){
				$q = Executor::doit("select count(*) c from tienda where estatus='activa'");
				$activas = ($q[0] && ($row = $q[0]->fetch_assoc())) ? (int)$row["c"] : 0;
				if($activas <= 1){
					pf_back("all", "No se puede suspender: es la última tienda activa. Activa otra primero.", 0);
				}
			}
			$t->setEstatus($to);
			pf_back("all", "Tienda \"".$t->nombre."\" marcada como $to.", 1);
		}
	}
	pf_back("all", "No se pudo actualizar el estatus.", 0);
}

// ── Extender el periodo ────────────────────────────────────────────
if($opt === "extender"){
	$id   = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
	$dias = isset($_POST["dias"]) ? (int)$_POST["dias"] : 30;
	if($dias <= 0){ $dias = 30; }
	$t = $id > 0 ? TiendaData::getById($id) : null;
	if(!$t){ pf_back("all", "Tienda no encontrada.", 0); }
	$sus = SuscripcionData::getLatestByTienda($id);
	if(!$sus){
		$plans = PlanData::getAllActives();
		$plan  = count($plans) ? $plans[0] : null;
		if(!$plan){ pf_back("all", "No hay planes activos.", 0); }
		$sus = new SuscripcionData();
		$sus->tienda_id = $id;
		$sus->plan_id   = (int)$plan->id;
		$r = $sus->add();
		$sus->id = (int)$r[1];
	}
	$sus->extend($dias);
	$t->setEstatus("activa");
	pf_back("all", "Periodo de \"".$t->nombre."\" extendido ".$dias." días.", 1);
}

// ── Cambiar de plan ────────────────────────────────────────────────
if($opt === "plan"){
	$id      = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
	$plan_id = isset($_POST["plan_id"]) ? (int)$_POST["plan_id"] : 0;
	$t = $id > 0 ? TiendaData::getById($id) : null;
	$plan = $plan_id > 0 ? PlanData::getById($plan_id) : null;
	if(!$t || !$plan){ pf_back("all", "Datos inválidos para cambiar el plan.", 0); }
	$sus = SuscripcionData::getLatestByTienda($id);
	if(!$sus){
		$sus = new SuscripcionData();
		$sus->tienda_id = $id;
		$sus->plan_id   = $plan_id;
		$r = $sus->add();
		$sus->id = (int)$r[1];
	}else{
		$sus->setPlan($plan_id);
	}
	pf_back("all", "Plan de \"".$t->nombre."\" cambiado a ".$plan->name.".", 1);
}

// ── Recordatorio de renovación (WhatsApp) ──────────────────────────
if($opt === "recordar"){
	$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
	$t = $id > 0 ? TiendaData::getById($id) : null;
	if(!$t){ pf_back("all", "Tienda no encontrada.", 0); }
	$sus = SuscripcionData::getLatestByTienda($id);
	if($sus){ $sus->markRecordatorio(); }

	$tel = preg_replace('/\D+/', '', (string)$t->whatsapp);
	if($tel === ""){ pf_back("all", "Aviso registrado para \"".$t->nombre."\" (sin WhatsApp configurado).", 1); }

	$fecha  = $sus ? SuscripcionData::fechaFmt($sus->periodo_fin) : "";
	$dias   = $sus ? SuscripcionData::diasRestantes($sus->periodo_fin) : null;
	$cuando = "está por vencer";
	if($dias !== null && $fecha !== "—"){
		$cuando = $dias < 0 ? "venció el $fecha" : "vence el $fecha (en $dias día".($dias==1?"":"s").")";
	}
	$msg = "Hola ".$t->nombre.", te saludamos de sazón. Tu menú digital ".$cuando.". "
	     . "Renueva tu plan aquí para no perderlo: ".pf_login_url();
	pf_go("https://wa.me/".$tel."?text=".rawurlencode($msg));
}

// ── Entrar como (impersonar) / salir ───────────────────────────────
if($opt === "entrar"){
	$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
	$t = $id > 0 ? TiendaData::getById($id) : null;
	if(!$t){ pf_back("all", "Tienda no encontrada.", 0); }
	$_SESSION["impersonate_tienda"] = $id;
	pf_go("./?view=home");
}
if($opt === "salir"){
	unset($_SESSION["impersonate_tienda"]);
	pf_go("./?view=platform");
}

// ── Alta manual de una tienda ──────────────────────────────────────
if($opt === "crear"){
	$nombre = isset($_POST["nombre"]) ? trim($_POST["nombre"]) : "";
	$sub    = isset($_POST["subdominio"]) ? strtolower(trim($_POST["subdominio"])) : "";
	$plan_id = isset($_POST["plan_id"]) ? (int)$_POST["plan_id"] : 0;
	$dias   = isset($_POST["dias"]) ? (int)$_POST["dias"] : 30;
	if($dias <= 0){ $dias = 30; }

	if(strlen($nombre) < 3){ pf_back("all", "El nombre del local debe tener al menos 3 letras.", 0); }
	if($sub === ""){ $sub = PlatformData::slugify($nombre); }
	if($sub === "" || !preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $sub)){
		pf_back("all", "El subdominio sólo admite letras, números y guiones.", 0);
	}
	$plan = $plan_id > 0 ? PlanData::getById($plan_id) : null;
	if(!$plan){ pf_back("all", "Selecciona un plan válido.", 0); }

	// Dueño opcional: se valida ANTES de crear nada (si no, el alta quedaría
	// a medias y sin usuario, en silencio) — auditoría Fase 6 #6.
	$con_dueno = isset($_POST["con_dueno"]);
	$email     = isset($_POST["email"]) ? trim($_POST["email"]) : "";
	$password  = isset($_POST["password"]) ? (string)$_POST["password"] : "";
	if($con_dueno){
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ pf_back("all", "El email del dueño no es válido.", 0); }
		if(strlen($password) < 6){ pf_back("all", "La contraseña del dueño debe tener al menos 6 caracteres.", 0); }
	}

	// Subdominio único
	$base = $sub; $i = 1;
	while(!TiendaData::subdomainFree($sub)){
		$i++;
		$sub = substr($base, 0, 60)."-".$i;
		if($i > 50){ pf_back("all", "Subdominio no disponible, prueba con otro.", 0); }
	}

	$t = new TiendaData();
	$t->subdomain = pf_esc($sub);
	$t->nombre    = pf_esc($nombre);
	$t->estatus   = "activa";
	// En modo live la tienda recién creada queda como "demo" hasta que haya
	// un checkout real; el estatus lo decide el alta manual (activa, N días).
	$t->zeno_mode = (PlatformData::cfg("zeno_mode", "demo") === "live") ? "live" : "demo";
	$r = $t->add();
	if(!$r[0]){ pf_back("all", "No se pudo crear la tienda.", 0); }
	$tiendaId = (int)$r[1];

	$sus = new SuscripcionData();
	$sus->tienda_id = $tiendaId;
	$sus->plan_id   = (int)$plan->id;
	$r2 = $sus->add();
	$sus->id = (int)$r2[1];
	$sus->extend($dias);

	// Usuario dueño (opcional; datos ya validados arriba)
	if($con_dueno){
		$pass = sha1(md5($password)); // mismo hash que el login del panel
		$sql  = "insert into user (name,username,email,password,is_active,is_admin,tienda_id,role,created_at) ";
		$sql .= "value (\"".pf_esc($nombre)."\",\"".pf_esc($email)."\",\"".pf_esc($email)."\",\"$pass\",1,0,$tiendaId,'dueno',NOW())";
		Executor::doit($sql);
	}

	pf_back("all", "Tienda \"".$nombre."\" creada (".$sub.").", 1);
}

// ── Configuración del aviso ────────────────────────────────────────
if($opt === "cfg"){
	$v = isset($_POST["saas_dias_aviso"]) ? (int)$_POST["saas_dias_aviso"] : 7;
	if($v < 1){ $v = 7; }
	if($v > 365){ $v = 365; }
	PlatformData::setCfg("saas_dias_aviso", (string)$v);
	pf_back("all", "Aviso de vencimiento configurado a $v días.", 1);
}

// Sin opt válido
pf_back("all", "Acción no reconocida.", 0);
