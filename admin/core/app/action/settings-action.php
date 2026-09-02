<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}

if(isset($_GET["opt"]) && $_GET["opt"]=="update"){
	if(count($_POST)>0){
		foreach($_POST as $p => $v){
			ConfigurationData::updateValFromName($p,$v);
		}
		Core::redir("./?view=settings&opt=all");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updatepm"){
	if(count($_POST)>0){
		foreach($_POST as $p => $v){
			ConfigurationData::updateValFromName($p,$v);
		}
		Core::redir("./?view=settings&opt=payment");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="switchpm"){
	$pay = PaymethodData::getById($_GET["id"]);
	$pay->is_active = $pay->is_active?0:1;
	$pay->update();
	Core::redir("./?view=settings&opt=payment");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="addzone"){
	if(isset($_POST["name"]) && $_POST["name"]!=""){
		$z = new DeliveryZoneData();
		$z->name = $_POST["name"];
		$z->price = 1;
		$z->add();
	}
	Core::redir("./?view=settings&opt=sedes&tab=zonas");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="addextra"){
	if(isset($_POST["name"]) && $_POST["name"]!=""){
		$products = array();
		if(isset($_POST["products"])){
			if(is_array($_POST["products"])){ $products = $_POST["products"]; }
			else if(trim($_POST["products"])!=""){ $products = explode(",", $_POST["products"]); }
		}
		$all_ids = array();
		foreach(ProductData::getAll() as $ap){ $all_ids[] = intval($ap->id); }
		$group_key = "g".uniqid(mt_rand());
		$is_ingredient = isset($_POST["is_ingredient"]) ? 1 : 0;
		ProductExtraData::setGroup($group_key, trim($_POST["name"]), floatval($_POST["price"]), $products, $all_ids, $is_ingredient);
	}
	Core::redir("./?view=settings&opt=ingredients");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updextraprods"){
	if(isset($_POST["group_key"]) && $_POST["group_key"]!="" && isset($_POST["name"]) && $_POST["name"]!=""){
		$products = array();
		if(isset($_POST["products"])){
			if(is_array($_POST["products"])){ $products = $_POST["products"]; }
			else if(trim($_POST["products"])!=""){ $products = explode(",", $_POST["products"]); }
		}
		$all_ids = array();
		foreach(ProductData::getAll() as $ap){ $all_ids[] = intval($ap->id); }
		$is_ingredient = isset($_POST["is_ingredient"]) ? 1 : 0;
		ProductExtraData::setGroup($_POST["group_key"], trim($_POST["name"]), floatval($_POST["price"]), $products, $all_ids, $is_ingredient);
	}
	echo "ok";
	exit;
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="delextra"){
	if(isset($_GET["group_key"]) && $_GET["group_key"]!=""){
		ProductExtraData::delGroup($_GET["group_key"]);
	}else if(isset($_GET["id"]) && $_GET["id"]!=""){
		$e = ProductExtraData::getById($_GET["id"]);
		if($e && $e->group_key!=""){ ProductExtraData::delGroup($e->group_key); }
		else if($e){ $e->delById($e->id); }
	}
	Core::redir("./?view=settings&opt=ingredients");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="addbebida"){
	if(isset($_POST["sabor"]) && trim($_POST["sabor"])!=""){
		$b = new BebidaData();
		$b->sabor = trim($_POST["sabor"]);
		$b->medida = isset($_POST["medida"]) ? trim($_POST["medida"]) : "";
		$b->sabor_options = isset($_POST["sabor_options"]) ? trim($_POST["sabor_options"]) : "";
		$b->precio = isset($_POST["precio"]) ? floatval($_POST["precio"]) : 0;
		$b->es_gratis = !empty($_POST["es_gratis"]) ? "1" : "0";
		$b->is_active = !empty($_POST["is_active"]) ? "1" : "0";
		$b->add();
	}
	Core::redir("./?view=settings&opt=bebidas");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updbebida"){
	if(isset($_POST["id"]) && isset($_POST["sabor"]) && trim($_POST["sabor"])!=""){
		$b = BebidaData::getById($_POST["id"]);
		$b->sabor = trim($_POST["sabor"]);
		$b->medida = isset($_POST["medida"]) ? trim($_POST["medida"]) : "";
		$b->sabor_options = isset($_POST["sabor_options"]) ? trim($_POST["sabor_options"]) : "";
		$b->precio = isset($_POST["precio"]) ? floatval($_POST["precio"]) : 0;
		$b->es_gratis = !empty($_POST["es_gratis"]) ? "1" : "0";
		$b->is_active = !empty($_POST["is_active"]) ? "1" : "0";
		$b->update();
	}
	echo "ok";
	exit;
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updbebidabase"){
	if(isset($_POST["bebida_base"]) && $_POST["bebida_base"]!=""){
		ConfigurationData::updateValFromName("bebida_base", floatval($_POST["bebida_base"]));
	}
	echo "ok";
	exit;
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="delbebida"){
	if(isset($_GET["id"]) && $_GET["id"]!=""){
		BebidaData::delById($_GET["id"]);
	}
	echo "ok";
	exit;
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="addunit"){
	if(isset($_POST["name"]) && $_POST["name"]!=""){
		$u = new UnitData();
		$u->name = $_POST["name"];
		$u->add();
	}
	Core::redir("./?view=settings&opt=units");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updunit"){
	if(isset($_POST["id"]) && isset($_POST["name"]) && $_POST["name"]!=""){
		$u = UnitData::getById($_POST["id"]);
		$u->name = $_POST["name"];
		$u->update();
	}
	Core::redir("./?view=settings&opt=units");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="delunit"){
	UnitData::delById($_GET["id"]);
	Core::redir("./?view=settings&opt=units");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updingredient"){
	if(isset($_POST["id"]) && isset($_POST["name"]) && $_POST["name"]!=""){
		$e = ProductExtraData::getById($_POST["id"]);
		if($e){
			$group_key = isset($_POST["group_key"]) && $_POST["group_key"]!="" ? $_POST["group_key"] : $e->group_key;
			if($group_key=="" && $e->group_key==""){ $group_key = "g".uniqid(mt_rand()); }
			$products = array();
			if(isset($_POST["products"])){
				if(is_array($_POST["products"])){ $products = $_POST["products"]; }
				else if(trim($_POST["products"])!=""){ $products = explode(",", $_POST["products"]); }
			}
			$all_ids = array();
			foreach(ProductData::getAll() as $ap){ $all_ids[] = intval($ap->id); }
			$is_ingredient = isset($_POST["is_ingredient"]) ? 1 : 0;
			ProductExtraData::setGroup($group_key, trim($_POST["name"]), floatval($_POST["price"]), $products, $all_ids, $is_ingredient);
		}
	}
	Core::redir("./?view=settings&opt=ingredients");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="addsede"){
	if(isset($_POST["name"]) && $_POST["name"]!="" && isset($_POST["phone"]) && $_POST["phone"]!=""){
		$s = new SedeData();
		$s->name = $_POST["name"];
		$s->address = isset($_POST["address"]) ? $_POST["address"] : "";
		$s->phone = $_POST["phone"];
		$s->maps = isset($_POST["maps"]) ? $_POST["maps"] : "";
		$s->is_active = isset($_POST["is_active"]) ? "1" : "0";
		if(isset($_FILES["image"])){
			$handle = new Upload($_FILES["image"]);
			if($handle->uploaded){
				$handle->Process("storage/sedes/");
				$s->image = $handle->file_dst_name;
			}
		}
		$s->add();
	}
	Core::redir("./?view=settings&opt=sedes");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updsede"){
	if(isset($_POST["id"]) && isset($_POST["name"]) && $_POST["name"]!="" && isset($_POST["phone"]) && $_POST["phone"]!=""){
		$s = SedeData::getById($_POST["id"]);
		$s->name = $_POST["name"];
		$s->address = isset($_POST["address"]) ? $_POST["address"] : "";
		$s->phone = $_POST["phone"];
		$s->maps = isset($_POST["maps"]) ? $_POST["maps"] : "";
		$s->is_active = isset($_POST["is_active"]) ? "1" : "0";
		if(isset($_FILES["image"]) && $_FILES["image"]["name"]!=""){
			$handle = new Upload($_FILES["image"]);
			if($handle->uploaded){
				$handle->Process("storage/sedes/");
				$s->image = $handle->file_dst_name;
			}
		}
		$s->update();
	}
	Core::redir("./?view=settings&opt=sedes");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="delsede"){
	SedeData::delById($_GET["id"]);
	Core::redir("./?view=settings&opt=sedes");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updhorarios"){
	if(count($_POST)>0){
		foreach(array("horario_lunes","horario_martes","horario_miercoles","horario_jueves","horario_viernes","horario_sabado","horario_domingo") as $k){
			if(isset($_POST[$k])){ ConfigurationData::updateValFromName($k,$_POST[$k]); }
		}
		Core::redir("./?view=settings&opt=horarios");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updhorario"){
	if(count($_POST)>0){
		foreach(array("horario_open","horario_close") as $k){
			if(isset($_POST[$k])){ ConfigurationData::updateValFromName($k,$_POST[$k]); }
		}
		Core::redir("./?view=settings&opt=horarios");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="changepass"){
	if(count($_POST)>0){
		$user = UserData::getById($_SESSION["user_id"]);
		if(sha1(md5($_POST["password"])) == $user->password){
			if($_POST["newpassword"] == $_POST["confirmnewpassword"]){
				$user->password = sha1(md5($_POST["newpassword"]));
				$user->update_passwd();
				Core::alert("Contraseña actualizada exitosamente!");
			}else{
				Core::alert("Las contraseñas no coinciden.");
			}
		}else{
			Core::alert("La contraseña actual es incorrecta.");
		}
		Core::redir("./?view=settings&opt=password");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updsedezone"){
	if(isset($_POST["sede_id"]) && isset($_POST["zone_id"])){
		$sede_id = intval($_POST["sede_id"]);
		$zone_id = intval($_POST["zone_id"]);
		$enabled = isset($_POST["enabled"]) && $_POST["enabled"] !== "";
		if(!$enabled){
			// Delete the record = no delivery for this sede+zone
			Executor::doit("DELETE FROM sede_delivery_zone WHERE sede_id=$sede_id AND delivery_zone_id=$zone_id");
		}else{
			$price = isset($_POST["price"]) ? floatval($_POST["price"]) : 1;
			SedeDeliveryZoneData::save($sede_id, $zone_id, $price);
		}
	}
	Core::redir("./?view=settings&opt=sedes&tab=zonas");
}
?>
