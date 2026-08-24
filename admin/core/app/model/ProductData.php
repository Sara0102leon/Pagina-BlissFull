<?php
class ProductData {
	public static $tablename = "product";

	public $id, $short_name, $code, $name, $description, $image, $price, $price_llevar, $offer_price, $offer_finish, $free_ingredients, $house_ingredients, $allow_halves, $tipo_division, $link, $category_id, $unit_id, $sede_id, $is_public, $in_existence, $is_featured, $is_active, $created_at;
	public $offer_txt, $order_at, $meta_title, $meta_description, $meta_keywords, $is_offert;

	public static function generateCode(){
		$alphabeth = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYZ1234567890_-";
		$code = "";
		for($i=0;$i<11;$i++){ $code .= $alphabeth[rand(0,strlen($alphabeth)-1)]; }
		return $code;
	}

	public static function offerActive($p){
		if(!$p || intval($p->is_offert)!=1){ return false; }
		if($p->offer_price=="" || $p->offer_price===null || floatval($p->offer_price)<=0){ return false; }
		if($p->offer_finish=="" || $p->offer_finish===null){ return true; }
		return date("Y-m-d") <= date("Y-m-d", strtotime($p->offer_finish));
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
	}

	public function getUnit(){ return UnitData::getById($this->unit_id);}

	public function add(){
		if($this->code=="" || $this->code===null){ $this->code = self::generateCode(); }
		$sql = "insert into ".self::$tablename." (short_name,code,name,description,image,price,price_llevar,offer_price,offer_finish,free_ingredients,house_ingredients,allow_halves,tipo_division,link,category_id,unit_id,sede_id,is_public,in_existence,is_featured,is_offert,created_at) ";
		$sql .= "value (\"$this->short_name\",\"$this->code\",\"$this->name\",\"$this->description\",\"$this->image\",\"$this->price\"," . ($this->price_llevar!="" ? "\"$this->price_llevar\"" : "NULL") . "," . ($this->offer_price!=""  ? "\"$this->offer_price\"" : "NULL") . "," . ($this->offer_finish!="" ? "\"$this->offer_finish\"" : "NULL") . "," . intval($this->free_ingredients) . "," . ($this->house_ingredients!="" ? "\"$this->house_ingredients\"" : "NULL") . "," . intval($this->allow_halves) . ",\"" . ($this->tipo_division!="" ? $this->tipo_division : "normal") . "\",\"$this->link\",$this->category_id,$this->unit_id," . ($this->sede_id!="" ? "$this->sede_id" : "NULL") . ",$this->is_public,$this->in_existence,$this->is_featured,$this->is_offert,$this->created_at)";
		return Executor::doit($sql);
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
		$sql = "update ".self::$tablename." set code=\"$this->code\",name=\"$this->name\",description=\"$this->description\",link=\"$this->link\",price=\"$this->price\",price_llevar=" . ($this->price_llevar!="" ? "\"$this->price_llevar\"" : "NULL") . ",offer_price=" . ($this->offer_price!="" ? "\"$this->offer_price\"" : "NULL") . ",offer_finish=" . ($this->offer_finish!="" ? "\"$this->offer_finish\"" : "NULL") . ",free_ingredients=" . intval($this->free_ingredients) . ",house_ingredients=" . ($this->house_ingredients!="" ? "\"$this->house_ingredients\"" : "NULL") . ",allow_halves=" . intval($this->allow_halves) . ",tipo_division=\"" . ($this->tipo_division!="" ? $this->tipo_division : "normal") . "\",in_existence=\"$this->in_existence\",is_public=\"$this->is_public\",is_featured=\"$this->is_featured\",unit_id=\"$this->unit_id\",category_id=\"$this->category_id\",sede_id=" . ($this->sede_id!="" ? "$this->sede_id" : "NULL") . ",is_offert=\"$this->is_offert\" where id=$this->id";
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
		$sql = "select * from ".self::$tablename." where is_active=1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getBySede($sede_id){
		$sede = intval($sede_id);
		$sql = "select * from ".self::$tablename." where is_active=1 and (sede_id is null or sede_id=$sede) order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getPublicsByCategoryId($id,$sede_id=0){
		$sql = "select * from ".self::$tablename." where category_id=$id and is_public=1 and is_active=1";
		if(intval($sede_id)>0){ $sql .= " and (sede_id is null or sede_id=".intval($sede_id).")"; }
		else { $sql .= " and sede_id is null"; }
		$sql .= " order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getLike($q,$sede_id=0){
		$sql = "select * from ".self::$tablename." where is_active=1 and (name like '%$q%' or description like '%$q%')";
		if(intval($sede_id)>0){ $sql .= " and (sede_id is null or sede_id=".intval($sede_id).")"; }
		else { $sql .= " and sede_id is null"; }
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getFeatureds($sede_id=0){
		$sql = "select * from ".self::$tablename." where is_featured=1 and is_active=1";
		if(intval($sede_id)>0){ $sql .= " and (sede_id is null or sede_id=".intval($sede_id).")"; }
		else { $sql .= " and sede_id is null"; }
		$sql .= " order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getNews(){
		$sql = "select * from ".self::$tablename." where is_active=1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

	public static function getOffers(){
		$sql = "select * from ".self::$tablename." where is_offert=1 and is_active=1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductData());
	}

}

?>
