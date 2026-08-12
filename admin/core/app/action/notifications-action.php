<?php
if(!isset($_SESSION["user_id"])){ http_response_code(403); echo json_encode(array("ok"=>false,"error"=>"Sin sesion")); exit; }

if(isset($_GET["opt"]) && $_GET["opt"]=="json"){
	$sql = "select b.*, c.name as client_name, c.phone as client_phone from buy b left join client c on c.id=b.client_id where b.status_id=1 order by b.created_at asc";
	$query = Executor::doit($sql);
	$rows = Model::many($query[0], new BuyData());
	$now = time();
	$data = array();
	foreach($rows as $b){
		$elapsed = $now - strtotime($b->created_at);
		if($elapsed < 0){ $elapsed = 0; }
		if($elapsed <= 2400){
			continue; // solo se notifican pedidos con 40+ minutos sin pagar
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
	header("Content-Type: application/json");
	echo json_encode(array("ok"=>true,"count"=>count($data),"orders"=>$data));
	exit;
}
?>