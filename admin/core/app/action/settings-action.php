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
		$z->price = floatval($_POST["price"]);
		$z->add();
	}
	Core::redir("./?view=settings&opt=zones");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="delzone"){
	DeliveryZoneData::delById($_GET["id"]);
	Core::redir("./?view=settings&opt=zones");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="addextra"){
	if(isset($_POST["name"]) && $_POST["name"]!=""){
		$e = new ProductExtraData();
		$e->name = $_POST["name"];
		$e->price = floatval($_POST["price"]);
		$e->product_id = isset($_POST["product_id"]) ? $_POST["product_id"] : "";
		$e->add();
	}
	Core::redir("./?view=settings&opt=ingredients");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="delextra"){
	ProductExtraData::delById($_GET["id"]);
	Core::redir("./?view=settings&opt=ingredients");
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
		$e->name = $_POST["name"];
		$e->price = floatval($_POST["price"]);
		$e->product_id = isset($_POST["product_id"]) ? $_POST["product_id"] : "";
		$e->update();
	}
	Core::redir("./?view=settings&opt=ingredients");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="addsede"){
	if(isset($_POST["name"]) && $_POST["name"]!="" && isset($_POST["phone"]) && $_POST["phone"]!=""){
		$s = new SedeData();
		$s->name = $_POST["name"];
		$s->address = isset($_POST["address"]) ? $_POST["address"] : "";
		$s->phone = $_POST["phone"];
		$s->is_active = isset($_POST["is_active"]) ? "1" : "0";
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
		$s->is_active = isset($_POST["is_active"]) ? "1" : "0";
		$s->update();
	}
	Core::redir("./?view=settings&opt=sedes");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="delsede"){
	SedeData::delById($_GET["id"]);
	Core::redir("./?view=settings&opt=sedes");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="updsedezones"){
	if(isset($_POST["sede_id"]) && $_POST["sede_id"]!="" && isset($_POST["zone_price"]) && is_array($_POST["zone_price"])){
		foreach($_POST["zone_price"] as $zid => $zprice){
			SedeDeliveryZoneData::save(intval($_POST["sede_id"]), intval($zid), floatval($zprice));
		}
		Core::redir("./?view=settings&opt=sedes");
	}
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
?>
