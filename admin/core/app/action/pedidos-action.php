<?php
/* ══════════════════════════════════════════════════════════════════
   pedidos-action.php — Paneles de Cocina y Repartidor (A3)
   Transiciones: Cocina (Pendiente|Pagado → Enviado) · Repartidor (Enviado → Finalizado)
   También sirve las filas HTML para el polling AJAX de los paneles.
   ══════════════════════════════════════════════════════════════════ */
if(!isset($_SESSION["user_id"])){ Core::redir("../login.php"); exit; }
$me = UserData::getById($_SESSION["user_id"]);
if(!$me){ Core::redir("../login.php"); exit; }
$my_role = ($me->role !== "") ? $me->role : "dueno";
$is_dueno      = ($my_role === "dueno" || $my_role === "superadmin");
$is_cocina     = ($is_dueno || $my_role === "cocina");
$is_repartidor = ($is_dueno || $my_role === "repartidor");

$opt = isset($_GET["opt"]) ? $_GET["opt"] : "";

// ── Cocina: marca el pedido como listo (Enviado) ──────────────────
if($opt === "preparar" && $is_cocina){
	$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
	if($id > 0){
		$buy = BuyData::getById($id);
		if($buy && intval($buy->tienda_id) === Tenant::id()){
			$cur = intval($buy->status_id);
			if($cur === 1 || $cur === 2){ // Pendiente / Pagado → Enviado
				$buy->status_id = 4;
				$buy->change_status();
				Core::alert("Pedido #".$buy->code." marcado como listo.");
			}else{
				Core::alert("Este pedido ya cambió de estado.");
			}
		}
	}
	Core::redir("./?view=cocina");
	exit;
}

// ── Repartidor: marca el pedido como entregado (Finalizado) ───────
if($opt === "entregar" && $is_repartidor){
	$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
	if($id > 0){
		$buy = BuyData::getById($id);
		if($buy && intval($buy->tienda_id) === Tenant::id()){
			$cur = intval($buy->status_id);
			if($cur === 4){ // Enviado → Finalizado
				$buy->status_id = 5;
				$buy->change_status();
				Core::alert("Pedido #".$buy->code." finalizado.");
			}else{
				Core::alert("Este pedido ya cambió de estado.");
			}
		}
	}
	Core::redir("./?view=repartidor");
	exit;
}

// ── Filas HTML para polling (cocina) ──────────────────────────────
if($opt === "cocina_rows" && $is_cocina){
	$queue = array();
	foreach(BuyData::getAll() as $b){
		$s = intval($b->status_id);
		if($s === 1 || $s === 2){ $queue[] = $b; }
	}
	$queue = array_reverse($queue); // más antiguos primero
	include "core/app/partials/cocina-list.php";
	exit;
}

// ── Filas HTML para polling (repartidor) ──────────────────────────
if($opt === "repartidor_rows" && $is_repartidor){
	$queue = array();
	foreach(BuyData::getAll() as $b){
		if(intval($b->status_id) === 4){ $queue[] = $b; }
	}
	include "core/app/partials/repartidor-list.php";
	exit;
}

Core::redir("./");