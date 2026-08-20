<?php
/**
 * Helper de extras/ingredientes/sabores del público.
 * - tt_build_extras_payload: pizza normal -> detecta los ingredientes "de la casa"
 *   en la descripción (ej: "Salsa de la casa, queso mozzarella, tocineta, jamón y maíz"
 *   -> tocineta, jamón y maíz), los ordena primero (principal al frente),
 *   preseleccionados en "sel" y el principal bloqueado en "main".
 * - tt_build_sabores: para las estaciones (2/4) -> lista de sabores de pizza
 *   gigante (Básica, Tocineta, Pepperoni, Vegetariana, Hawaiana, Anchoa), cada
 *   uno con sus ingredientes incluidos, para elegir el sabor de cada fracción.
 */

function tt_norm($s){
  $s = mb_strtolower(trim((string)$s));
  $s = strtr($s, array("á"=>"a","é"=>"e","í"=>"i","ó"=>"o","ú"=>"u","ñ"=>"n","ü"=>"u"));
  $s = preg_replace('/[^a-z0-9 ]/', ' ', $s);
  $s = preg_replace('/\s+/', ' ', $s);
  return trim($s);
}

function tt_house_ingredients($desc, $catalog){
  $base = array(
    "salsa de la casa", "queso mozzarella", "salsa", "queso",
    "elige ingredientes de tu preferencia", "de tu preferencia",
    "comer aqui", "para llevar", "sabores de pizza", "a tu preferencia",
    "precio", "extra"
  );
  $parts = preg_split('/\s+y\s+|\s*[,;]\s+/', (string)$desc, -1, PREG_SPLIT_NO_EMPTY);
  $found = array();
  foreach($parts as $part){
    $p = tt_norm($part);
    if($p=="" || in_array($p, $base)){ continue; }
    foreach($catalog as $ci => $cat){
      $cn = $cat["_norm"];
      if($cn=="" ){ continue; }
      if($p===$cn || (strlen($p)>=3 && (strpos($cn,$p)!==false || strpos($p,$cn)!==false))){
        if(!in_array($ci, $found)){ $found[] = $ci; }
        break;
      }
    }
  }
  return $found;
}

function tt_build_extras_payload($desc, $free, $rows, $house_csv=""){
  $payload = array("desc"=>trim((string)$desc),"free"=>intval($free),"ingredients"=>array(),"extras"=>array(),"sel"=>array(),"main"=>-1);
  if(!is_array($rows)){ return $payload; }

  $ing = array();
  $extras_rows = array();
  foreach($rows as $e){
    $item = array("name"=>$e->name,"price"=>floatval($e->price));
    if(intval($e->is_ingredient)==1){ $ing[] = $item; }
    else { $extras_rows[] = $item; }
  }
  if(count($ing)==0){
    $payload["extras"] = $extras_rows;
    return $payload;
  }

  $catalog = array();
  foreach($ing as $i => $it){ $catalog[$i] = array("name"=>$it["name"],"price"=>$it["price"],"_norm"=>tt_norm($it["name"])); }

  $house_idx = array();
  $house_names = array();
  if(trim((string)$house_csv)!=""){
    foreach(explode(",", $house_csv) as $h){ $h = trim($h); if($h!=""){ $house_names[] = $h; } }
  }
  foreach($house_names as $hn){
    $hnn = tt_norm($hn);
    if($hnn==""){ continue; }
    foreach($catalog as $ci => $cat){
      if($cat["_norm"]==$hnn || (strlen($hnn)>=3 && (strpos($cat["_norm"],$hnn)!==false || strpos($hnn,$cat["_norm"])!==false))){
        if(!in_array($ci, $house_idx)){ $house_idx[] = $ci; }
        break;
      }
    }
  }
  if(count($house_idx)==0){ $house_idx = tt_house_ingredients($desc, $catalog); }

  $ordered = array();
  $house_set = array();
  foreach($house_idx as $ci){
    $ordered[] = array("name"=>$catalog[$ci]["name"],"price"=>$catalog[$ci]["price"]);
    $house_set[] = $catalog[$ci]["_norm"];
  }
  foreach($ing as $i => $it){
    if(!in_array($catalog[$i]["_norm"], $house_set)){
      $ordered[] = array("name"=>$it["name"],"price"=>$it["price"]);
    }
  }
  $payload["ingredients"] = $ordered;

  if(count($house_idx)>0){
    $payload["sel"] = range(0, count($house_idx)-1);
    $payload["main"] = 0;
    $payload["free"] = count($house_idx);
  }

  $payload["extras"] = $extras_rows;
  return $payload;
}

/**
 * Sabores disponibles para las estaciones: los productos de la categoría
 * "Pizzas Gigantes" que tengan ingredientes de la casa (las estaciones y la
 * genérica "a tu preferencia" quedan fuera solas).
 */
function tt_build_sabores($sede_id){
  $sabores = array();
  $prods = ProductData::getPublicsByCategoryId(2, intval($sede_id));
  if(!is_array($prods)){ return $sabores; }
  foreach($prods as $p){
    if(trim((string)$p->tipo_division)!=""){ continue; }
    $extras = ProductExtraData::getByProductId($p->id);
    $pay = tt_build_extras_payload($p->description, $p->free_ingredients, $extras, $p->house_ingredients);
    $sel = $pay["sel"];
    if(count($sel)==0){ continue; }
    $ing = array();
    foreach($sel as $i){
      $ing[] = array("name"=>$pay["ingredients"][$i]["name"],"price"=>$pay["ingredients"][$i]["price"]);
    }
    $sabores[] = array("id"=>intval($p->id),"name"=>trim((string)$p->name),"ingredients"=>$ing);
  }
  return $sabores;
}