<?php
$__me_raw = isset($_SESSION["user_id"]) ? UserData::getById($_SESSION["user_id"]) : null;
$__is_dueno = ($__me_raw && ($__me_raw->role === "dueno" || $__me_raw->role === "superadmin"));
$__roles_ok = array("dueno","cocina","repartidor");

// Permiso de edición/borrado (UserData::puedeTocar) + respuesta común.
function u_denegado(){
	Core::alert("No tienes permiso para modificar ese usuario.");
	Core::redir("./?view=users&opt=all");
	exit;
}

if(isset($_GET["opt"]) && $_GET["opt"]=="register"){
if(count($_POST)>0){



	$user = new UserData();

	$user->addExtraFieldString("name", htmlentities($_POST["name"]));
	$user->addExtraFieldString("lastname", htmlentities($_POST["lastname"]));
	$user->addExtraFieldString("username", htmlentities($_POST["username"]));

	$user->email = htmlentities($_POST["email"]);
	$user->password = sha1(md5(htmlentities($_POST["password"])));
	$user->register();
	Core::alert("Usuario agregado!");
	Core::redir("./?view=login");
}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="add"){
if(count($_POST)>0){
	if(!$__is_dueno){ Core::redir("./"); exit; }
	$user = new UserData();
	$user->name = $_POST["name"];
	$user->lastname = $_POST["lastname"];
	$user->username = $_POST["username"];
	$user->email = $_POST["email"];
	$user->password = sha1(md5($_POST["password"]));
	$user->role = (isset($_POST["role"]) && in_array($_POST["role"], $__roles_ok)) ? $_POST["role"] : "dueno";
	$user->tienda_id = Tenant::id();
	$user->add();
	Core::alert("Usuario agregado!");
	Core::redir("./?view=users&opt=all");
}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="upd"){
if(count($_POST)>0){
	if(!$__is_dueno){ Core::redir("./"); exit; }
	$__obj = UserData::getById(isset($_POST["user_id"]) ? (int)$_POST["user_id"] : 0);
	if(!UserData::puedeTocar($__me_raw, $__obj)){ u_denegado(); }
	$user = $__obj;
	$user->name = $_POST["name"];
	$user->lastname = $_POST["lastname"];
	$user->email = $_POST["email"];
	// Nunca se pierde el rol super-admin por un form que no lo ofrece:
	// si el rol enviado no está permitido, se conserva el actual.
	$user->role = (isset($_POST["role"]) && in_array($_POST["role"], $__roles_ok)) ? $_POST["role"] : $user->role;
	$user->update();

	if($_POST["password"]!=""){
		$user->password = sha1(md5($_POST["password"]));
		$user->update_passwd();
		Core::alert("Se ha actualizado el password!");
	}
	Core::alert("Usuario actualizado!");
	Core::redir("./?view=users&opt=all");
}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="del"){
	if(!$__is_dueno){ Core::redir("./"); exit; }
	$__obj = UserData::getById(isset($_GET["id"]) ? (int)$_GET["id"] : 0);
	// mismo alcance que en upd: ni el super-admin ni usuarios de otra tienda
	if(!UserData::puedeTocar($__me_raw, $__obj)){ u_denegado(); }
	$user = $__obj;
	if($user->id!=$_SESSION["user_id"]){
		$user->del();
	}
	Core::alert("Usuario eliminado!");
	Core::redir("./?view=users&opt=all");
}



?>