<?php
class ProductExtraData {
	public static $tablename = "product_extra";

	public $id, $product_id, $name, $price;

	public function __construct(){
		$this->id = null;
		$this->product_id = "";
		$this->name = "";
		$this->price = "";
	}

	public function add(){
		$product_id = ($this->product_id === "" || $this->product_id === null) ? "NULL" : intval($this->product_id);
		$sql = "insert into ".self::$tablename." (product_id,name,price) ";
		$sql .= "value ($product_id,\"$this->name\",\"$this->price\")";
		Executor::doit($sql);
	}

	public function update(){
		$product_id = ($this->product_id === "" || $this->product_id === null) ? "NULL" : intval($this->product_id);
		$sql = "update ".self::$tablename." set name=\"$this->name\",price=\"$this->price\",product_id=$product_id where id=$this->id";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new ProductExtraData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by product_id asc, price desc, name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductExtraData());
	}

	public static function getByProductId($id){
		$sql = "select * from ".self::$tablename." where product_id is null or product_id=$id order by price desc, name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ProductExtraData());
	}

}
?>
