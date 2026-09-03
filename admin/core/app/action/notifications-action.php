<?php
if(!isset($_SESSION["user_id"])){ http_response_code(403); echo json_encode(array("ok"=>false,"error"=>"Sin sesión")); exit; }

if(isset($_GET["opt"]) && $_GET["opt"]=="json"){
	// ------------------------------------------------------------------
	// 1) Pedidos sin pago (lógica original, sin cambios)
	// ------------------------------------------------------------------
	$sql = "select b.*, c.name as client_name, c.phone as client_phone, TIMESTAMPDIFF(SECOND, b.created_at, NOW()) as elapsed_sec from buy b left join client c on c.id=b.client_id where b.status_id=1 order by b.created_at asc";
	$query = Executor::doit($sql);
	$rows = Model::many($query[0], new BuyData());
	$data = array();
	foreach($rows as $b){
		// Los pedidos programados a futuro no se notifican como "sin pago" todavia
		if(!empty($b->scheduled_at) && strtotime($b->scheduled_at) > time()){
			continue;
		}
		$elapsed = intval($b->elapsed_sec);
		if($elapsed < 0){ $elapsed = 0; }
		if($elapsed <= 1800){
			continue; // solo se notifican pedidos con 30+ minutos sin pagar
		}else if($elapsed <= 3600){
			$level = "risk";
		}else{
			$level = "critical";
		}
		$client = $b->getClient();
		$pm = $b->getPaymethod();
		$zone = $b->getDeliveryZone();
		$data[] = array(
			"id"			=> intval($b->id),
			"created_at"	=> date("Y-m-d H:i:s", strtotime($b->created_at)),
			"scheduled_at"	=> $b->scheduled_at ? date("Y-m-d H:i", strtotime($b->scheduled_at)) : "",
			"elapsed"		=> $elapsed,
			"level"			=> $level,
			"client"		=> $client ? $client->getFullname() : "Sin nombre",
			"phone"			=> $client ? $client->phone : "",
			"paymethod"		=> $pm ? $pm->name : "Sin metodo",
			"zone"			=> $zone ? $zone->name : "",
			"pickup"		=> $zone ? 0 : 1,
			"total"			=> $b->getTotal()
		);
	}

	// ------------------------------------------------------------------
	// 2) Pedidos programados a futuro con avisos por hitos (una vez por hito)
	// ------------------------------------------------------------------
	// Umbrales: avisar al crearse y luego a 24h, 6h, 1h y 15 min antes.
	$HOOKS = array(
		"15min" => 900,
		"1h"    => 3600,
		"6h"    => 21600,
		"24h"   => 86400,
	);
	$now = time();
	$newAlerts = array();   // notificaciones recién disparadas en esta consulta
	$scheduled = array();   // todos los pedidos programados pendientes visibles en la campana

	$sql2 = "select * from buy where status_id=1 and scheduled_at is not null and scheduled_at <> '' and scheduled_at > NOW() order by scheduled_at asc";
	$q2 = Executor::doit($sql2);
	$schRows = Model::many($q2[0], new BuyData());
	foreach($schRows as $b){
		$ts = strtotime($b->scheduled_at);
		$diff = intval($ts) - $now;
		if($diff < 0){ continue; }

		$notified = array();
		if(!empty($b->notified)){
			foreach(explode(",", $b->notified) as $tok){ $tok=trim($tok); if($tok!=""){ $notified[$tok]=true; } }
		}

		$fired = array();

		// Al crearse (primera vez que lo vemos)
		if(empty($notified) && !isset($notified["created"])){
			$fired[] = "created";
			$newAlerts[] = array(
				"id"=>intval($b->id),
				"type"=>"scheduled",
				"hook"=>"created",
				"scheduled_at"=>date("Y-m-d H:i", $ts),
				"client"=>$b->getClient() ? $b->getClient()->getFullname() : "Sin nombre",
				"label"=>"Nuevo pedido programado"
			);
		}

		// Cruzar umbrales de tiempo (solo los aún no notificados)
		foreach($HOOKS as $key => $secs){
			if($diff <= $secs && !isset($notified[$key])){
				$fired[] = $key;
				$hookLabel = array(
					"15min"=>"Falta menos de 15 minutos",
					"1h"   =>"Falta 1 hora",
					"6h"   =>"Faltan 6 horas",
					"24h"  =>"Falta 1 día (24h)",
				);
				$newAlerts[] = array(
					"id"=>intval($b->id),
					"type"=>"scheduled",
					"hook"=>$key,
					"scheduled_at"=>date("Y-m-d H:i", $ts),
					"client"=>$b->getClient() ? $b->getClient()->getFullname() : "Sin nombre",
					"label"=>$hookLabel[$key]
				);
			}
		}

		if(!empty($fired)){
			foreach($fired as $f){ $notified[$f] = true; }
			$newset = implode(",", array_keys($notified));
			Executor::doit("update buy set notified=\"".addslashes($newset)."\" where id=".intval($b->id));
		}

		$scheduled[] = array(
			"id"			=> intval($b->id),
			"scheduled_at"	=> date("Y-m-d H:i", $ts),
			"scheduled_ts"	=> $ts,
			"diff"			=> $diff,
			"client"		=> $b->getClient() ? $b->getClient()->getFullname() : "Sin nombre",
			"phone"			=> $b->getClient() ? $b->getClient()->phone : "",
			"paymethod"		=> $b->getPaymethod() ? $b->getPaymethod()->name : "Sin metodo",
			"total"			=> $b->getTotal(),
			"notified"		=> implode(",", array_keys($notified))
		);
	}

	// ------------------------------------------------------------------
	// 3) Pedidos recientes (cualquier estado) para detectar pedidos NUEVOS
	// ------------------------------------------------------------------
	$sql3 = "select * from buy order by id desc limit 10";
	$q3 = Executor::doit($sql3);
	$recent = array();
	foreach(Model::many($q3[0], new BuyData()) as $r){
		$recent[] = array(
			"id"			=> intval($r->id),
			"scheduled"		=> (!empty($r->scheduled_at) && strtotime($r->scheduled_at) > time()) ? 1 : 0,
			"scheduled_at"	=> $r->scheduled_at ? date("Y-m-d H:i", strtotime($r->scheduled_at)) : "",
			"created_at"	=> $r->created_at ? date("Y-m-d H:i", strtotime($r->created_at)) : "",
			"client"		=> $r->getClient() ? $r->getClient()->getFullname() : "Sin nombre",
			"phone"			=> $r->getClient() ? $r->getClient()->phone : "",
			"paymethod"		=> $r->getPaymethod() ? $r->getPaymethod()->name : "Sin metodo",
			"total"			=> $r->getTotal(),
			"status_id"		=> intval($r->status_id)
		);
	}

	header("Content-Type: application/json");
	echo json_encode(array(
		"ok"=>true,
		"count"=>count($data),
		"orders"=>$data,
		"scheduled_count"=>count($scheduled),
		"scheduled"=>$scheduled,
		"new_alerts"=>$newAlerts,
		"recent"=>$recent
	));
	exit;
}
?>
