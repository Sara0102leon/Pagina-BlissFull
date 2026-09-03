<?php
if(isset($_GET["opt"]) && $_GET["opt"]=="add"){
	$extras = array();
	if(isset($_POST["extras"]) && $_POST["extras"]!=""){
		$dec = json_decode($_POST["extras"], true);
		if(is_array($dec)){ $extras = $dec; }
	}
	$bebidas = array();
	if(isset($_POST["bebidas"]) && $_POST["bebidas"]!=""){
		$dec = json_decode($_POST["bebidas"], true);
		if(is_array($dec)){ $bebidas = $dec; }
	}
	$key = $_POST["product_id"].":".md5(json_encode($extras)).":".md5(json_encode($bebidas));
	if(!isset($_SESSION["cart"])){
		$_SESSION["cart"] = array( array("key"=>$key,"product_id"=>$_POST["product_id"],"q"=>1,"extras"=>$extras,"bebidas"=>$bebidas) );
	}else{
		$products = $_SESSION["cart"];
		$found = false;
		foreach($products as $index => $p){
			if($p["key"] == $key){
				$products[$index]["q"]++;
				$found = true;
				break;
			}
		}
		if(!$found){
			array_push($products, array("key"=>$key,"product_id"=>$_POST["product_id"],"q"=>1,"extras"=>$extras,"bebidas"=>$bebidas));
		}
		$_SESSION["cart"]=$products;
	}
	if(isset($_GET["ajax"])){
		View::load("cart-side");
	}else{
		Core::redir("./");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="dec"){
	if(isset($_SESSION["cart"])){
		$cart = $_SESSION["cart"];
		$newcart = array();
		foreach($cart as $c){
			if($c["key"]==$_REQUEST["key"]){
				$c["q"]--;
				if($c["q"] > 0){
					array_push($newcart, $c);
				}
			}else{
				array_push($newcart, $c);
			}
		}
		$_SESSION["cart"] = $newcart;
	}
	if(isset($_GET["ajax"])){
		View::load("cart-side");
	}else{
		Core::redir("./?view=cart&opt=all");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="inc"){
	if(isset($_SESSION["cart"])){
		$cart = $_SESSION["cart"];
		foreach($cart as $index => $c){
			if($c["key"]==$_REQUEST["key"]){
				$cart[$index]["q"]++;
				break;
			}
		}
		$_SESSION["cart"] = $cart;
	}
	if(isset($_GET["ajax"])){
		View::load("cart-side");
	}else{
		Core::redir("./?view=cart&opt=all");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="del"){
	if(isset($_SESSION["cart"])){
		$cart = $_SESSION["cart"];
		$newcart = array();
		foreach($cart as $c){
			if($c["key"]!=$_REQUEST["key"]){
				array_push($newcart, $c);
			}
		}
		$_SESSION["cart"] = $newcart;
	}
	if(isset($_GET["ajax"])){
		View::load("cart-side");
	}else{
		Core::redir("./");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="edit"){
	if(isset($_SESSION["cart"])){
		$cart = $_SESSION["cart"];
		foreach($cart as $index => $c){
			if($c["key"]==$_REQUEST["key"]){
				$cart[$index]["q"] = $_REQUEST["q"];
				break;
			}
		}
		$_SESSION["cart"] = $cart;
	}
	if(isset($_GET["ajax"])){
		View::load("cart-side");
	}else{
		Core::redir("./?view=cart&opt=all");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="clear"){
	unset($_SESSION["cart"]);
	if(isset($_GET["ajax"])){
		View::load("cart-side");
	}else{
		Core::redir("./?view=cart&opt=all");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="buy"){
	if(!empty($_POST) && isset($_SESSION["cart"]) && count($_SESSION["cart"])>0){
		$code = "";
		try {
			$client = null;
			$phone = trim($_POST["phone"]);
			$name = trim($_POST["name"]);

			// Un cliente se identifica SOLO con la combinación nombre + teléfono.
			// Si coincide la misma combinación, se reutiliza y se acumulan sus pedidos
			// (el conteo hacia "cliente frecuente"). Si solo coincide el teléfono o
			// solo el nombre, se trata como un cliente DIFERENTE.
			$sql = "select * from client where phone=\"$phone\" and lower(trim(name))=lower(trim(\"$name\")) order by id desc limit 1";
			$query = Executor::doit($sql);
			$client = Model::one($query[0],new ClientData());

			if($client==null){
				$client = new ClientData();
				$client->name = $name;
				$client->phone = $phone;
				$client->address = $_POST["address"];
				$client->email = "";
				$client->password = "";
				$client->add();
				
				$sql_find = "select * from client where phone=\"$phone\" and lower(trim(name))=lower(trim(\"$name\")) order by id desc limit 1";
				$query_find = Executor::doit($sql_find);
				$client = Model::one($query_find[0],new ClientData());
			}else{
				$q_ped = Executor::doit("select count(*) as c from buy where client_id=".intval($client->id)." and status_id<>3");
				$pedidos = Model::one($q_ped[0],new BuyData());
				// solo se renombra el cliente si nunca ha completado un pedido (evita que datos de prueba arruinen el nombre)
				if(!$pedidos || (trim($client->name)!=="" && strcasecmp(trim($client->name),$name)!=0 && intval($pedidos->c)==0)){
					$client->name = $name;
				}
				$client->address = $_POST["address"];
				$client->update();
			}

			$buy = new BuyData();
			$alphabeth ="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890_-";
			$code = "";
			for($i=0;$i<11;$i++){ $code .= $alphabeth[rand(0,strlen($alphabeth)-1)]; }
			$buy->code = $code;
			$buy->client_id = $client->id;
			$buy->paymethod_id= isset($_POST["paymethod_id"])?$_POST["paymethod_id"]:1;
			$buy->delivery_zone_id = isset($_POST["delivery_zone_id"])?$_POST["delivery_zone_id"]:"";
			$buy->sede_id = isset($_POST["sede_id"])?$_POST["sede_id"]:"";
			$buy->capture = isset($_POST["capture"])?$_POST["capture"]:"";
			$buy->note = isset($_POST["note"])?trim($_POST["note"]):"";
			$buy->scheduled_at = isset($_POST["scheduled_at"])?trim($_POST["scheduled_at"]):"";
			// Validación servidor: los pedidos programados exigen mínimo 3 horas de anticipación
			if($buy->scheduled_at!=""){
				$ts = strtotime($buy->scheduled_at);
				$min_lead = 3*60*60; // 3 horas
				$min_ts = time() + $min_lead;
				if(!$ts || $ts <= time() || $ts < $min_ts){
					$buy->scheduled_at = "";
				}
			}
			$buy->status_id= 1;
			$b = $buy->add();

			foreach ($_SESSION["cart"] as $c) {
				$p = new BuyProductData();
				$p->buy_id = $b[1];
				$p->product_id = $c["product_id"];
				$p->q = $c["q"];
				if(isset($c["extras"]) && count($c["extras"])>0){
					$clean = array();
					foreach($c["extras"] as $e){
						$clean[] = array("name"=>$e["name"],"price"=>floatval($e["price"]));
					}
					$p->extras = json_encode($clean);
				}
				if(isset($c["bebidas"]) && count($c["bebidas"])>0){
					$cleanb = array();
					foreach($c["bebidas"] as $beb){
						$cleanb[] = array("sabor"=>$beb["sabor"],"medida"=>$beb["medida"],"sabor_elegido"=>isset($beb["sabor_elegido"]) ? $beb["sabor_elegido"] : "","price"=>floatval($beb["precio"]));
					}
					$p->bebidas = json_encode($cleanb);
				}
				$p->add();
			}
		} catch(\Throwable $e){
			if($code==""){
				$alphabeth ="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890_-";
				for($i=0;$i<11;$i++){ $code .= $alphabeth[rand(0,strlen($alphabeth)-1)]; }
			}
		}
		unset($_SESSION["cart"]);
		echo $code;
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="check"){
	header("Content-Type: application/json");
	$phone = trim(isset($_POST["phone"])?$_POST["phone"]:"");
	$frequent = false;
	$orders = 0;
	if($phone!=""){
		$sql = "select cl.id, count(b.id) as c from client cl left join buy b on b.client_id=cl.id and b.status_id<>3 where cl.phone=\"$phone\" group by cl.id order by c desc limit 1";
		$query = Executor::doit($sql);
		$row = Model::one($query[0],new BuyData());
		if($row){ $orders = intval($row->c); $frequent = $orders>=8; }
	}
	echo json_encode(array("frequent"=>$frequent,"orders"=>$orders));
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="search"){
	View::load("product-grid");
}
?>
