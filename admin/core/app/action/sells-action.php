<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}

if(isset($_GET["opt"]) && $_GET["opt"]=="status"){
	$buy =  BuyData::getById($_GET["id"]);
	if($buy && $buy->status_id != 3){
		$buy->status_id = $_GET["status"];
		$buy->change_status();
	}else{
		Core::alert("Este pedido fue cancelado y no puede cambiar de estado.");
	}
	Core::redir("./?view=sells&opt=all");
}
?>
