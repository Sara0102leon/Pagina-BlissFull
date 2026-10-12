<?php 
if(!isset($_SESSION["user_id"])){ Core::redir("./");}

// Solo se copian a $product las propiedades declaradas en ProductData
$PRODUCT_FIELDS = array_flip(array("id","short_name","code","name","description","image","link","price","price_llevar","offer_price","offer_finish","offer_from","offer_to","badge","badge_color","free_ingredients","house_ingredients","allow_halves","tipo_division","category_id","unit_id","sede_id","is_public","in_existence","is_featured","is_active","created_at","order_at","offer_txt","meta_title","meta_description","meta_keywords","is_offert"));

// Normaliza la hora (HH:MM[:SS]) al formato de MySQL o "" si no es válida
if(!function_exists("tt_norm_time")){
	function tt_norm_time($t){
		$t = trim((string)$t);
		if($t===""){ return ""; }
		if(preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $t, $m)){
			$h = intval($m[1]); $i = intval($m[2]); $s = isset($m[3]) ? intval($m[3]) : 0;
			if($h>23 || $i>59 || $s>59){ return ""; }
			return sprintf("%02d:%02d:%02d", $h, $i, $s);
		}
		return "";
	}
}
// Etiqueta/color de etiqueta + franja horaria de la oferta
if(!function_exists("tt_norm_product_extras")){
	function tt_norm_product_extras($product){
		$badge = trim((string)$product->badge);
		$badge = (function_exists("mb_substr") ? mb_substr($badge, 0, 24) : substr($badge, 0, 24));
		$product->badge = $badge;
		$allowed = array("accent","gold","red","dark","outline");
		$bc = strtolower(trim((string)$product->badge_color));
		$product->badge_color = in_array($bc, $allowed) ? $bc : "";
		$product->offer_from = tt_norm_time($product->offer_from);
		$product->offer_to = tt_norm_time($product->offer_to);
	}
}

if(isset($_GET["opt"]) && $_GET["opt"]=="add"){
	if(count($_POST)>0){
		$product =  new ProductData();
		foreach ($_POST as $k => $v) {
			if(isset($PRODUCT_FIELDS[$k])){ $product->$k = $v; }
		}
		tt_norm_product_extras($product);
		$alphabeth ="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890_-";
		$code = "";
		for($i=0;$i<11;$i++){
		    $code .= $alphabeth[rand(0,strlen($alphabeth)-1)];
		}
		$product->short_name= $code;

		if(isset($_FILES["image"])){
    		$handle = new Upload($_FILES['image']);
        	if ($handle->uploaded) {
        		$url="storage/products/";
            	$handle->Process($url);
                $product->image = $handle->file_dst_name;
                if(!$handle->processed){ $_SESSION["product_img_error"] = "No se pudo procesar la imagen: ".$handle->error; }
    		} else if(isset($_FILES["image"]["error"]) && intval($_FILES["image"]["error"])!==UPLOAD_ERR_NO_FILE){
    			$_SESSION["product_img_error"] = "No se pudo subir la imagen (código ".intval($_FILES["image"]["error"])."). Verifica el peso y formato.";
    		}
		}

		$sedes_all = !empty($_POST["sedes_all"]);
		$p_sedes = array_values(array_filter(array_map("intval", isset($_POST["sedes"]) && is_array($_POST["sedes"]) ? $_POST["sedes"] : array()), function($s){ return $s>0; }));
		$product->sede_id = $sedes_all ? "" : 0;

		if(isset($_POST["is_public"])) { $product->is_public=1; }else{ $product->is_public=0; }
		if(isset($_POST["in_existence"])) { $product->in_existence=1; }else{ $product->in_existence=0; }
		if(isset($_POST["is_featured"])) { $product->is_featured=1; }else{ $product->is_featured=0; }
		// La oferta se activa automáticamente al colocar un precio de oferta
		$product->is_offert = ($product->offer_price!=="" && floatval($product->offer_price)>0) ? 1 : 0;
		$is_pizza_cat = false;
		$pcat = isset($_POST["category_id"]) ? CategoryData::getById(intval($_POST["category_id"])) : null;
		if($pcat && strpos(mb_strtolower(trim($pcat->name)), "pizza")!==false){ $is_pizza_cat = true; }
		$td = ($is_pizza_cat && isset($_POST["tipo_division"])) ? trim($_POST["tipo_division"]) : "normal";
		if(!in_array($td, array("normal","2_estaciones","4_estaciones"))){ $td = "normal"; }
		$product->tipo_division = $td;
		$product->allow_halves = ($td=="normal" ? 0 : 1);
		$product->free_ingredients = ($is_pizza_cat && isset($_POST["free_ingredients"])) ? intval($_POST["free_ingredients"]) : 0;
		$hi = ($is_pizza_cat && isset($_POST["house_ingredients"]) && is_array($_POST["house_ingredients"])) ? array_map("trim", $_POST["house_ingredients"]) : array();
		$product->house_ingredients = implode(", ", array_filter($hi));

		$res = $product->add();
		if($res){ ProductData::saveSedes(intval($res[1]), $sedes_all ? array() : $p_sedes); }
		if(isset($_POST["has_extras"]) && $res && $is_pizza_cat){
			$new_id = intval($res[1]);
			if($new_id>0){
				ProductExtraData::addProductToAllGroups($new_id);
			}
		}
		Core::redir("./?view=products&opt=all");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="upd"){
	if(count($_POST)>0){
		$product = ProductData::getById($_POST["id"]);
		// Seguridad multi-tenant: solo se puede editar un producto de la tienda activa
		if(!$product || (Tenant::id()>0 && intval($product->tienda_id)!==Tenant::id())){ Core::redir("./?view=products&opt=all"); }
		foreach ($_POST as $k => $v) {
			if(isset($PRODUCT_FIELDS[$k])){ $product->$k = $v; }
		}
		tt_norm_product_extras($product);

		if(isset($_FILES["image"])){
			$handle = new Upload($_FILES['image']);
			if ($handle->uploaded) {
				$url="storage/products/";
				$handle->Process($url);
			    $product->image = $handle->file_dst_name;
			    $product->update_image();
			    if(!$handle->processed){ $_SESSION["product_img_error"] = "No se pudo procesar la imagen: ".$handle->error; }
			} else if(isset($_FILES["image"]["error"]) && intval($_FILES["image"]["error"])!==UPLOAD_ERR_NO_FILE){
				$_SESSION["product_img_error"] = "No se pudo subir la imagen (código ".intval($_FILES["image"]["error"])."). Verifica el peso y formato.";
			}
		}

		if(isset($_POST["is_public"])) { $product->is_public=1; }else{ $product->is_public=0; }
		if(isset($_POST["in_existence"])) { $product->in_existence=1; }else{ $product->in_existence=0; }
		if(isset($_POST["is_featured"])) { $product->is_featured=1; }else{ $product->is_featured=0; }
		// La oferta se activa automáticamente al colocar un precio de oferta
		$product->is_offert = ($product->offer_price!=="" && floatval($product->offer_price)>0) ? 1 : 0;
		$is_pizza_cat = false;
		$pcat = isset($_POST["category_id"]) ? CategoryData::getById(intval($_POST["category_id"])) : null;
		if($pcat && strpos(mb_strtolower(trim($pcat->name)), "pizza")!==false){ $is_pizza_cat = true; }
		$td = ($is_pizza_cat && isset($_POST["tipo_division"])) ? trim($_POST["tipo_division"]) : "normal";
		if(!in_array($td, array("normal","2_estaciones","4_estaciones"))){ $td = "normal"; }
		$product->tipo_division = $td;
		$product->allow_halves = ($td=="normal" ? 0 : 1);
		$product->free_ingredients = ($is_pizza_cat && isset($_POST["free_ingredients"])) ? intval($_POST["free_ingredients"]) : 0;
		$hi = ($is_pizza_cat && isset($_POST["house_ingredients"]) && is_array($_POST["house_ingredients"])) ? array_map("trim", $_POST["house_ingredients"]) : array();
		$product->house_ingredients = implode(", ", array_filter($hi));

		$sedes_all2 = !empty($_POST["sedes_all"]);
		$p_sedes2 = array_values(array_filter(array_map("intval", isset($_POST["sedes"]) && is_array($_POST["sedes"]) ? $_POST["sedes"] : array()), function($s){ return $s>0; }));
		$product->sede_id = $sedes_all2 ? "" : 0;

		$product->update();
		ProductData::saveSedes(intval($product->id), $sedes_all2 ? array() : $p_sedes2);
		$_SESSION["product_updated"]= 1;
		Core::redir("./?view=products&opt=all");
	}
}
else if(isset($_GET["opt"]) && $_GET["opt"]=="del"){
	$product = ProductData::getById($_GET["id"]);
	// Seguridad multi-tenant: solo se puede borrar un producto de la tienda activa
	if(!$product || (Tenant::id()>0 && intval($product->tienda_id)!==Tenant::id())){ Core::redir("./?view=products&opt=all"); }
	$product->del();
	Core::redir("./?view=products&opt=all");
}
?>
