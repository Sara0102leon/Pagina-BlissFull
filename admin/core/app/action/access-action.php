<?php
// access-action.php
// este archivo sirve para procesar las opciones de login y logout

if(isset($_GET["opt"]) && $_GET["opt"]=="login"){

if(isset($_SESSION["user_id"])) {
	// Ya hay sesión → al panel
	header("Location: ./?view=home");
	exit;
}

$user_var     = isset($_POST["email"])    ? trim((string)$_POST["email"]) : "";
$password_var = isset($_POST["password"]) ? (string)$_POST["password"]   : "";

if($user_var === "" || $password_var === ""){
	header("Location: ../login.php?err=1");
	exit;
}

$base = new Database();
$con  = $base->connect();

$user = $con->real_escape_string($user_var);
$pass = sha1(md5($password_var)); // sha1(md5(...)) — mismo hash que login.php

$sql = "select id, role from user where (email= '$user' or username= '$user') and password= '$pass' and is_active=1 limit 1";
$query = $con->query($sql);
$row = ($query) ? $query->fetch_assoc() : null;

if($row) {
	$_SESSION['user_id'] = intval($row['id']);
	$dest = "./?view=home";
	if(isset($row['role'])){
		if($row['role']==="cocina"){ $dest = "./?view=cocina"; }
		elseif($row['role']==="repartidor"){ $dest = "./?view=repartidor"; }
	}
	header("Location: ".$dest);
	exit;
}else {
	// Credenciales incorrectas → de vuelta al login de la plataforma
	header("Location: ../login.php?err=1");
	exit;
}

}
if(isset($_GET["opt"]) && $_GET["opt"]=="logout"){
	$_SESSION = array();
	if(session_id()!==""){
		session_unset();
		session_destroy();
	}
	$cookieParams = session_get_cookie_params();
	setcookie(session_name(), "", time()-42000, $cookieParams["path"], $cookieParams["domain"], $cookieParams["secure"], $cookieParams["httponly"]);
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Pragma: no-cache");
	header("Location: ../login.php");
	exit;
}

?>