<?php
/* ══════════════════════════════════════════════════════════════════
   order-action.php — Orden manual del menú (A4)
   Persiste el orden por arrastre de categorías y de productos.

   Rutas:
     ?action=order&opt=cats    body JSON {ids:[...]}               -> category.position
     ?action=order&opt=prods   body JSON {category_id, ids:[...]}  -> product.position

   Devuelve siempre JSON {ok, n} (o {ok:false, error}).
   ══════════════════════════════════════════════════════════════════ */
header("Content-Type: application/json; charset=utf-8");

function order_out($arr, $code = 200){
	http_response_code($code);
	echo json_encode($arr);
	exit;
}

// ── Guard: solo el dueño reordena el menú ─────────────────────────
if(!isset($_SESSION["user_id"])){ order_out(array("ok"=>false,"error"=>"no-session"), 401); }
$me = UserData::getById($_SESSION["user_id"]);
if(!$me){ order_out(array("ok"=>false,"error"=>"no-user"), 401); }
$my_role = ($me->role !== "") ? $me->role : "dueno";
if($my_role !== "dueno" && $my_role !== "superadmin"){ order_out(array("ok"=>false,"error"=>"forbidden"), 403); }

// ── Entrada: JSON (fetch) o form-encoded (fallback) ───────────────
$raw = file_get_contents("php://input");
$in = json_decode($raw, true);
if(!is_array($in)){ $in = $_POST; }
$ids = (isset($in["ids"]) && is_array($in["ids"])) ? $in["ids"] : array();

$opt = isset($_GET["opt"]) ? $_GET["opt"] : "";

if($opt === "cats"){
	$n = CategoryData::reorder($ids);
	order_out(array("ok"=>true, "n"=>$n));
}

if($opt === "prods"){
	$cat = isset($in["category_id"]) ? intval($in["category_id"]) : 0;
	if($cat <= 0){ order_out(array("ok"=>false,"error"=>"missing-category"), 400); }
	// Verifica que la categoría pertenezca a la tienda activa
	$chk = Executor::doit("select id from category where id=$cat and ".Tenant::filter()." limit 1");
	if(!($chk[0] && $chk[0]->fetch_assoc())){ order_out(array("ok"=>false,"error"=>"bad-category"), 403); }
	$n = ProductData::reorderInCategory($cat, $ids);
	order_out(array("ok"=>true, "n"=>$n));
}

order_out(array("ok"=>false,"error"=>"bad-opt"), 400);
