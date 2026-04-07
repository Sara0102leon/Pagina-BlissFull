<?php
if(isset($_GET["opt"]) && $_GET["opt"]=="add"){
	if(!isset($_SESSION["cart"])){
		$_SESSION["cart"] = array( array("product_id"=>$_POST["product_id"],"q"=>1 ));
	}else{
		$products = $_SESSION["cart"];
		$found = false;
		foreach($products as $index => $p){
			if($p["product_id"] == $_POST["product_id"]){
				$products[$index]["q"]++;
				$found = true;
				break;
			}
		}
		if(!$found){
			array_push($products, array("product_id"=>$_POST["product_id"],"q"=>1));
		}
		$_SESSION["cart"]=$products;
	}
	// Return updated cart view if it was an AJAX call
	if(isset($_GET["ajax"])){
		View::load("cart-side");
	}else{
		Core::redir("./");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="del"){
	if(isset($_SESSION["cart"])){
		$cart = $_SESSION["cart"];
		$newcart = array();
		foreach($cart as $c){
			if($c["product_id"]!=$_POST["product_id"]){
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
			if($c["product_id"]==$_POST["product_id"]){
				$cart[$index]["q"] = $_POST["q"];
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
		$client = null;
		$phone = $_POST["phone"];
		$sql = "select * from client where phone=\"$phone\" limit 1";
		$query = Executor::doit($sql);
		$client = Model::one($query[0],new ClientData());

		if($client==null){
			$client = new ClientData();
			$client->name = $_POST["name"];
			$client->phone = $_POST["phone"];
			$client->address = $_POST["address"];
			$client->email = "";
			$client->password = "";
			$client->add();
			
			$sql_find = "select * from client where phone=\"$phone\" order by created_at desc limit 1";
			$query_find = Executor::doit($sql_find);
			$client = Model::one($query_find[0],new ClientData());
		}

		$buy = new BuyData();
		$alphabeth ="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890_-";
		$code = "";
		for($i=0;$i<11;$i++){ $code .= $alphabeth[rand(0,strlen($alphabeth)-1)]; }
		$buy->code = $code;
		$buy->client_id = $client->id;
		$buy->paymethod_id= 1;
		$buy->status_id= 1;
		$b = $buy->add();

		foreach ($_SESSION["cart"] as $c) {
			$p = new BuyProductData();
			$p->buy_id = $b[1];
			$p->product_id = $c["product_id"];
			$p->q = $c["q"];
			$p->add();
		}
		unset($_SESSION["cart"]);
		echo "ok";
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="search"){
	View::load("product-grid");
}
?>
