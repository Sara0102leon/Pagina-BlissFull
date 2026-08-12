<?php
class SedeData {
	public static $tablename = "sede";

	public $id, $name, $address, $phone, $is_active, $created_at;

	public function __construct(){
		$this->id = null;
		$this->name = "";
		$this->address = "";
		$this->phone = "";
		$this->is_active = "1";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (name,address,phone,is_active) ";
		$sql .= "value (\"$this->name\",\"$this->address\",\"$this->phone\",$this->is_active)";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set name=\"$this->name\",address=\"$this->address\",phone=\"$this->phone\",is_active=\"$this->is_active\" where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new SedeData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new SedeData());
	}

	public static function getActives(){
		$sql = "select * from ".self::$tablename." where is_active=1 order by name asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new SedeData());
	}

}
?>