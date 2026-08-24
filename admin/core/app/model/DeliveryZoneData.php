<?php
class DeliveryZoneData {
	public static $tablename = "delivery_zone";

	public $id, $name, $price;

	public function __construct(){
		$this->id = null;
		$this->name = "";
		$this->price = "";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (name,price) ";
		$sql .= "value (\"$this->name\",\"$this->price\")";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new DeliveryZoneData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by price asc, name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new DeliveryZoneData());
	}

	public function update(){
		$sql = "update ".self::$tablename." set name=\"$this->name\",price=\"$this->price\" where id=$this->id";
		Executor::doit($sql);
	}

}
?>
