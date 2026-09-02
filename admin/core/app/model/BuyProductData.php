<?php
class BuyProductData {
	public static $tablename = "buy_product";

	public $id, $buy_id, $product_id, $q, $extras, $bebidas;

	public function __construct(){
		$this->id = null;
		$this->buy_id = "";
		$this->product_id = "";
		$this->q = "";
		$this->extras = "";
		$this->bebidas = "";
	}

	public function getProduct() { return ProductData::getById($this->product_id);}

	public function getExtrasArray(){
		if($this->extras && $this->extras!=""){
			$dec = json_decode($this->extras, true);
			if(is_array($dec)){ return $dec; }
		}
		return array();
	}

	public function getBebidasArray(){
		if($this->bebidas && $this->bebidas!=""){
			$dec = json_decode($this->bebidas, true);
			if(is_array($dec)){ return $dec; }
		}
		return array();
	}

	public function getExtrasTotal(){
		$total = 0;
		foreach($this->getExtrasArray() as $e){ $total += floatval($e["price"]); }
		foreach($this->getBebidasArray() as $b){ $total += floatval($b["price"]); }
		return $total;
	}

	public function add(){
		$extras_sql = "NULL";
		if($this->extras!=""){
			$esc = mysqli_real_escape_string(Database::getCon(), $this->extras);
			$extras_sql = "\"$esc\"";
		}
		$bebidas_sql = "NULL";
		if($this->bebidas!=""){
			$esc = mysqli_real_escape_string(Database::getCon(), $this->bebidas);
			$bebidas_sql = "\"$esc\"";
		}
		$sql = "insert into ".self::$tablename." (buy_id,product_id,q,extras,bebidas) ";
		$sql .= "value (\"$this->buy_id\",$this->product_id,$this->q,$extras_sql,$bebidas_sql)";
		return Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}
	public function del(){
		$sql = "delete from ".self::$tablename." where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new BuyProductData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename;
		$query = Executor::doit($sql);
		return Model::many($query[0],new BuyProductData());
	}

	public static function getAllByBuyId($id){
		$sql = "select * from ".self::$tablename." where buy_id=$id";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BuyProductData());
	}

}

?>
