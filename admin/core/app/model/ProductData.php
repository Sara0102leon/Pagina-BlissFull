<?php
class ProductData {
	public static $tablename = "product";

	public $id, $short_name, $code, $name, $description, $image, $price, $price_llevar, $offer_price, $offer_finish, $free_ingredients, $house_ingredients, $allow_halves, $tipo_division, $link, $category_id, $unit_id, $sede_id, $is_public, $in_existence, $is_featured, $is_active, $created_at, $tienda_id, $position;
	public $offer_txt, $order_at, $meta_title, $meta_description, $meta_keywords, $is_offert;
	public $badge, $badge_color, $offer_from, $offer_to;

	public static function generateCode(){
		$alphabeth = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890_-";
		$code = "";
		for($i=0;$i<11;$i++){ $code .= $alphabeth[rand(0,strlen($alphabeth)-1)]; }
		return $code;
	}

	public static function offerActive($p){
		if(!$p){ return false; }
		if($p->offer_price=="" || $p->offer_price===null || floatval($p->offer_price)<=0){ return false; }
		// Fecha de fin (inclusive)
		if($p->offer_finish!="" && $p->offer_finish!==null){
			if(date("Y-m-d") > date("Y-m-d", strtotime($p->offer_finish))){ return false; }
		}
		// Franja horaria opcional (offer_from / offer_to, formato HH:MM:SS).
		// Si solo hay una de las dos, se interpreta como "desde" o "hasta" abierto.
		$from = isset($p->offer_from) ? trim((string)$p->offer_from) : "";
		$to   = isset($p->offer_to)   ? trim((string)$p->offer_to)   : "";
		if($from!=="" || $to!==""){
			$now = date("H:i:s");
			if($from!=="" && $to!==""){
				if($from <= $to){
					if($now < $from || $now > $to){ return false; }
				}else{
					// franja que cruza medianoche (ej. 20:00 - 02:00)
					if($now < $from && $now > $to){ return false; }
				}
			}else if($from!==""){
				if($now < $from){ return false; }
			}else{
				if($now > $to){ return false; }
			}
		}
		return true;
	}

	// Producto agotado: no se puede pedir. Reutiliza la columna in_existence.
	public static function isSoldOut($p){
		if(!$p){ return false; }
		return intval($p->in_existence)===0;
	}

	// Texto de la etiqueta del producto (vacío = sin etiqueta)
	public static function badgeText($p){
		if(!$p || !isset($p->badge)){ return ""; }
		$b = trim((string)$p->badge);
		if($b===""){ return ""; }
		return (function_exists("mb_substr") ? mb_substr($b,0,24) : substr($b,0,24));
	}

	// Clave de color de la etiqueta (se resuelve a una clase CSS; ver custom-dark.css)
	public static function badgeColor($p){
		$allowed = array("accent","gold","red","dark","outline");
		$c = ($p && isset($p->badge_color)) ? strtolower(trim((string)$p->badge_color)) : "";
		return in_array($c, $allowed) ? $c : "accent";
	}

	public static function getEffectivePrice($p){
		return self::offerActive($p) ? floatval($p->offer_price) : floatval($p->price);
	}

	public function __construct(){
		$this->short_name = "";
		$this->code = "";
		$this->name = "";
		$this->description = "";
		$this->image = "";
		$this->link = "";
		$this->category_id = "";
		$this->unit_id = "";
		$this->is_public = "0";
		$this->in_existence = "0";
		$this->is_featured = "0";
		$this->is_active = "1";
		$this->created_at = "NOW()";
		$this->position = 0;
		$this->offer_from = "";
		$this->offer_to = "";
		$this->badge = "";
		$this->badge_color = "";
	}

	public function getUnit(){ return UnitData::getById($this->unit_id);}

	// Normaliza un precio al formato numérico de MySQL (coma->punto) o NULL si vacío
	private static function num($v){
		if($v===null || $v===""){ return null; }
		$s = trim((string)$v);
		if($s===""){ return null; }
		if(strpos($s, ",")!==false){ $s = str_replace(".", "", $s); $s = str_replace(",", ".", $s); }
		return (is_numeric($s) ? floatval($s) : null);
	}

	public function add(){
		if($this->code=="" || $this->code===null){ $this->code = self::generateCode(); }
		$price = self::num($this->price);
		$pl    = self::num($this->price_llevar);
		$op    = self::num($this->offer_price);
		$pos   = self::nextPosition($this->category_id); // los productos nuevos van al final de su categoría
		$sql = "insert into ".self::$tablename." (short_name,code,name,description,image,price,price_llevar,offer_price,offer_finish,offer_from,offer_to,badge,badge_color,free_ingredients,house_ingredients,allow_halves,tipo_division,link,category_id,unit_id,sede_id,is_public,in_existence,is_featured,is_offert,tienda_id,position,created_at) ";
		$sql .= "value (\"$this->short_name\",\"$this->code\",\"$this->name\",\"$this->description\",\"$this->image\"," . ($price!==null ? "\"$price\"" : "NULL") . "," . ($pl!==null ? "\"$pl\"" : "NULL") . "," . ($op!==null ? "\"$op\"" : "NULL") . "," . ($this->offer_finish!="" ? "\"$this->offer_finish\"" : "NULL") . "," . ($this->offer_from!="" ? "\"$this->offer_from\"" : "NULL") . "," . ($this->offer_to!="" ? "\"$this->offer_to\"" : "NULL") . "," . ($this->badge!="" ? "\"$this->badge\"" : "NULL") . "," . ($this->badge_color!="" ? "\"$this->badge_color\"" : "NULL") . "," . intval($this->free_ingredients) . "," . ($this->house_ingredients!="" ? "\"$this->house_ingredients\"" : "NULL") . "," . intval($this->allow_halves) . ",\"" . ($this->tipo_division!="" ? $this->tipo_division : "normal") . "\",\"$this->link\",$this->category_id," . ($this->unit_id!="" ? "$this->unit_id" : "NULL") . "," . ($this->sede_id!="" ? "$this->sede_id" : "NULL") . ",$this->is_public,$this->in_existence,$this->is_featured,$this->is_offert,".Tenant::idSql().",".intval($pos).",$this->created_at)";
		return Executor::doit($sql);
	}

	// Siguiente posición libre dentro de una categoría (para que los nuevos vayan al final)
	public static function nextPosition($category_id){
		$cat = intval($category_id);
		$sql = "select coalesce(max(position),0)+1 as np from ".self::$tablename." where category_id=$cat and ".Tenant::filter();
		$query = Executor::doit($sql);
		$one = Model::one($query[0],new ProductData());
		return intval($one->np);
	}

	// Reasigna posiciones (0..n-1) a los productos indicados, validando categoría y tienda.
	public static function reorderInCategory($category_id, $ids){
		$cat = intval($category_id);
		if($cat<=0 || !is_array($ids)){ return 0; }
		$pos = 0; $n = 0;
		foreach($ids as $id){
			$id = intval($id);
			if($id<=0){ continue; }
			$chk = Executor::doit("select id from ".self::$tablename." where id=$id and category_id=$cat and ".Tenant::filter()." limit 1");
			if($chk[0] && $chk[0]->fetch_assoc()){
				Executor::doit("update ".self::$tablename." set position=".$pos." where id=$id");
				$pos++; $n++;
			}
		}
		return $n;
	}

	public static function delById($id){
		$sql = "update ".self::$tablename." set is_active=0 where id=$id";
		Executor::doit($sql);
	}

	public function del(){
		$sql = "update ".self::$tablename." set is_active=0 where id=$this->id";
		Executor::doit($sql);
	}

	public function update(){
		$price = self::num($this->price);
		$pl    = self::num($this->price_llevar);
		$op    = self::num($this->offer_price);
		$sql = "update ".self::$tablename." set code=\"$this->code\",name=\"$this->name\",description=\"$this->description\",link=\"$this->link\",price=" . ($price!==null ? "\"$price\"" : "NULL") . ",price_llevar=" . ($pl!==null ? "\"$pl\"" : "NULL") . ",offer_price=" . ($op!==null ? "\"$op\"" : "NULL") . ",offer_finish=" . ($this->offer_finish!="" ? "\"$this->offer_finish\"" : "NULL") . ",offer_from=" . ($this->offer_from!="" ? "\"$this->offer_from\"" : "NULL") . ",offer_to=" . ($this->offer_to!="" ? "\"$this->offer_to\"" : "NULL") . ",badge=" . ($this->badge!="" ? "\"$this->badge\"" : "NULL") . ",badge_color=" . ($this->badge_color!="" ? "\"$this->badge_color\"" : "NULL") . ",free_ingredients=" . intval($this->free_ingredients) . ",house_ingredients=" . ($this->house_ingredients!="" ? "\"$this->house_ingredients\"" : "NULL") . ",allow_halves=" . intval($this->allow_halves) . ",tipo_division=\"" . ($this->tipo_division!="" ? $this->tipo_division : "normal") . "\",in_existence=\"$this->in_existence\",is_public=\"$this->is_public\",is_featured=\"$this->is_featured\",unit_id=" . ($this->unit_id!="" ? "$this->unit_id" : "NULL") . ",category_id=\"$this->category_id\",sede_id=" . ($this->sede_id!="" ? "$this->sede_id" : "NULL") . ",is_offert=\"$this->is_offert\" where id=$this->id";
		Executor::doit($sql);
	}

	public function update_image(){
		$sql = "update ".self::$tablename." set image=\"$this->image\" where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new ProductData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." where is_active=1 and ".Tenant::filter()." order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	// Filtro de sede para las consultas públicas:
	//   sede_id NULL  -> todas las sedes
	//   sede_id > 0   -> solo esa sede (legacy)
	//   sede_id = 0   -> solo las sedes del pivote product_sede
	// Se agrega el producto si está en el pivote o aplica a la sede consultada.
	public static function sedeFilter($sede_id){
		$s = intval($sede_id);
		$t = self::$tablename;
		if($s>0){
			return " (".$t.".sede_id is null or ".$t.".sede_id=".$s
				." or exists (select 1 from product_sede ps where ps.product_id=".$t.".id and ps.sede_id=".$s.")) ";
		}
		return " (".$t.".sede_id is null or exists (select 1 from product_sede ps where ps.product_id=".$t.".id)) ";
	}

	// Sedes específicas (pivote) de un producto
	public function getSedes(){
		$out = array();
		$q = Executor::doit("select sede_id from product_sede where product_id=".intval($this->id));
		$r = $q[0];
		if($r){ while($row = $r->fetch_assoc()){ $out[] = intval($row["sede_id"]); } }
		return $out;
	}

	public static function saveSedes($product_id, $sedes){
		$pid = intval($product_id);
		Executor::doit("delete from product_sede where product_id=$pid");
		if(is_array($sedes) && $pid>0){
			$seen = array();
			foreach($sedes as $sid){
				$sid = intval($sid);
				if($sid>0 && !isset($seen[$sid])){ $seen[$sid]=1; Executor::doit("insert ignore into product_sede (product_id,sede_id) values ($pid,$sid)"); }
			}
		}
	}

	public static function getBySede($sede_id){
		$sql = "select * from ".self::$tablename." where is_active=1 and ".Tenant::filter()." and ".self::sedeFilter($sede_id)." order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getPublicsByCategoryId($id,$sede_id=0){
		$sql = "select * from ".self::$tablename." where category_id=$id and is_public=1 and is_active=1 and ".Tenant::filter()." and ".self::sedeFilter($sede_id);
		$sql .= " order by position asc, created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	// Todos los productos activos de una categoría (para el orden manual del admin)
	public static function getActivesByCategory($category_id){
		$cat = intval($category_id);
		$sql = "select * from ".self::$tablename." where category_id=$cat and is_active=1 and ".Tenant::filter()." order by position asc, created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	// Carta "todas las sedes": sin filtro de sede (incluye productos fijados a una sede)
	public static function getPublicsByCategoryIdAll($id){
		$sql = "select * from ".self::$tablename." where category_id=$id and is_public=1 and is_active=1 and ".Tenant::filter();
		$sql .= " order by position asc, name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getLike($q,$sede_id=0){
		$sql = "select * from ".self::$tablename." where is_active=1 and (name like '%$q%' or description like '%$q%') and ".Tenant::filter()." and ".self::sedeFilter($sede_id);
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getFeatureds($sede_id=0){
		$sql = "select * from ".self::$tablename." where is_featured=1 and is_active=1 and ".Tenant::filter()." and ".self::sedeFilter($sede_id);
		$sql .= " order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getNews(){
		$sql = "select * from ".self::$tablename." where is_active=1 and ".Tenant::filter()." order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getOffers($sede_id=0){
		$sql = "select * from ".self::$tablename." where offer_price is not null and offer_price<>'' and offer_price>0 and is_active=1 and ".Tenant::filter()." and ".self::sedeFilter($sede_id);
		$sql .= " order by created_at desc";
		$query = Executor::doit($sql);
		$rows = Model::many($query[0],new ProductData());
		// Solo las ofertas vigentes (fecha de fin y franja horaria)
		$out = array();
		foreach($rows as $p){ if(self::offerActive($p)){ $out[] = $p; } }
		return $out;
	}

}

?>
