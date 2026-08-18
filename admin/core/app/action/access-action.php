<?php
// access-action.php
// este archivo sirve para procesar las opciones de login y logout

if(isset($_GET["opt"]) && $_GET["opt"]=="login"){

if(!isset($_SESSION["user_id"])) {

$user_var = htmlentities($_POST["email"]);
$password_var = htmlentities($_POST['password']);


$user = $user_var;
$pass = sha1(md5($password_var));
$base = new Database();
$con = $base->connect();

$sql = "select * from user where (email= \"".$user."\" or username= \"".$user."\" ) and password= \"".$pass."\" and is_active=1";
//print $sql;
$query = $con->query($sql);
$found = false;
$userid = null;
while($r = $query->fetch_array()){
	$found = true ;
	$userid = $r['id'];
}

if($found==true) {
	$_SESSION['user_id']=$userid ;
	// Si todo sale bien
	print "Cargando ... $user";
	Core::redir("./?view=home");
}else {
	// Si la contrase~a es incorrecta
	Core::redir("./?view=login");
}
}else{
	// si ya esta logeado
	Core::redir("./?view=home");	
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
	header("Location: ./?view=login");
	exit;
}

?>