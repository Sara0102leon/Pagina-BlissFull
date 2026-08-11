<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}

if(isset($_GET["opt"]) && $_GET["opt"]=="status"){
	$buy = BuyData::getById($_GET["id"]);
	if(!$buy){ Core::redir("./?view=sells&opt=all"); }
	$cur = intval($buy->status_id);
	$target = intval($_GET["status"]);
	if($cur==3){
		Core::alert("Este pedido ya fue cancelado y no puede cambiar de estado.");
	}else if($target<=$cur){
		Core::alert("No se puede retroceder el estado del pedido.");
	}else if($target==4 && $cur<2){
		Core::alert("No puedes marcar como ENVIADO sin haber marcado PAGADO antes.");
	}else if($target==5 && $cur<2){
		Core::alert("No puedes marcar como FINALIZADO sin haber marcado PAGADO antes.");
	}else if($target==3 && $cur>=2){
		Core::alert("No puedes CANCELAR un pedido que ya fue pagado.");
	}else{
		$buy->status_id = $target;
		$buy->change_status();
	}
	Core::redir("./?view=sells&opt=all");
}
?>
