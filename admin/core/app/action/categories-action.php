<?php
if(isset($_GET["opt"]) && $_GET["opt"]=="add"){
if(count($_POST)>0){
	$cat = new CategoryData();
	$cat->name = $_POST["name"];
	$cat->short_name = $_POST["short_name"];
	$cat->is_active = isset($_POST["is_active"])?1:0;
	$cat->position = CategoryData::nextPosition();
	if(isset($_FILES["image"]) && isset($_FILES["image"]["name"]) && $_FILES["image"]["name"]!=""){
		$handle = new Upload($_FILES["image"]);
		if($handle->uploaded){ $handle->Process("storage/categories/"); $cat->image = $handle->file_dst_name; }
	}
	$cat->add();
	//Core::alert("Categoria agregada!");
	Core::redir("./?view=categories&opt=all");
}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="upd"){
if(count($_POST)>0){
	$cat = CategoryData::getById($_POST["id"]);
	$cat->name = $_POST["name"];
	$cat->short_name = $_POST["short_name"];
	$cat->is_active = isset($_POST["is_active"])?1:0;
	$cat->position = isset($_POST["position"])?intval($_POST["position"]):$cat->position;
	if(isset($_POST["remove_image"]) && $_POST["remove_image"]=="1"){ $cat->image = ""; }
	if(isset($_FILES["image"]) && isset($_FILES["image"]["name"]) && $_FILES["image"]["name"]!=""){
		$handle = new Upload($_FILES["image"]);
		if($handle->uploaded){ $handle->Process("storage/categories/"); $cat->image = $handle->file_dst_name; }
	}
	$cat->update();
	//Core::alert("Categoria actualizada!");
	Core::redir("./?view=categories&opt=all");
}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="up"){
	$cat = CategoryData::getById($_GET["id"]);
	$prev = CategoryData::getPrev($cat->position);
	if($cat && $prev){
		$tmp = $cat->position;
		$cat->position = $prev->position;
		$prev->position = $tmp;
		$cat->update();
		$prev->update();
	}
	Core::redir("./?view=categories&opt=all");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="down"){
	$cat = CategoryData::getById($_GET["id"]);
	$next = CategoryData::getNext($cat->position);
	if($cat && $next){
		$tmp = $cat->position;
		$cat->position = $next->position;
		$next->position = $tmp;
		$cat->update();
		$next->update();
	}
	Core::redir("./?view=categories&opt=all");
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="del"){
	$cat = CategoryData::getById($_GET["id"]);
	$cat->del();
	//Core::alert("Categoria eliminada!");
	Core::redir("./?view=categories&opt=all");
}
?>