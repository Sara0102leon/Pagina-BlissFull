<?php
class UserData extends Extra{
	public static $tablename = "user";
	public $extra_fields;
	public $extra_fields_strings;

	public $id, $name, $lastname, $username, $email, $password, $kind, $status, $created_at, $image, $is_active, $is_admin, $tienda_id, $role;

	public function __construct(){
		$this->extra_fields = array();
		$this->extra_fields_strings = array();
		$this->name = "";
		$this->lastname = "";
		$this->username = "";
		$this->email = "";
		$this->password = "";
		$this->created_at = "NOW()";
	}


	public function register(){

		$sql = "insert into user (".$this->getExtraFieldNames().",username,email,password,created_at) ";
		$sql .= "value (".$this->getExtraFieldValues().",\"$this->username\",\"$this->email\",\"$this->password\",$this->created_at)";
		return Executor::doit($sql);
	}


	public function add(){
		$role  = ($this->role !== "") ? $this->role : "dueno";
		$tienda = (intval($this->tienda_id) > 0) ? intval($this->tienda_id) : 0;
		$sql = "insert into user (name,lastname,email,password,role,tienda_id,created_at) ";
		$sql .= "value (\"$this->name\",\"$this->lastname\",\"$this->email\",\"$this->password\",\"$role\",$tienda,$this->created_at)";
		Executor::doit($sql);
	}

	public function del(){
		$sql = "delete from ".self::$tablename." where id=$this->id";
		Executor::doit($sql);
	}

	public static function delBy($k,$v){
		$sql = "delete from ".self::$tablename." where $k=\"$v\"";
		Executor::doit($sql);
	}

	public function update(){
		$role  = ($this->role !== "") ? $this->role : "dueno";
		$sql = "update ".self::$tablename." set name=\"$this->name\",lastname=\"$this->lastname\",email=\"$this->email\",username=\"$this->username\",is_active=\"$this->is_active\",is_admin=\"$this->is_admin\",role=\"$role\" where id=$this->id";
		Executor::doit($sql);
	}

	public function update_passwd(){
		$sql = "update ".self::$tablename." set password=\"$this->password\" where id=$this->id";
		Executor::doit($sql);
	}

	public function updateById($k,$v){
		$sql = "update ".self::$tablename." set $k=\"$v\" where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		 $sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new UserData());
	}

	public static function getBy($k,$v){
		$sql = "select * from ".self::$tablename." where $k=\"$v\"";
		$query = Executor::doit($sql);
		return Model::one($query[0],new UserData());
	}

	public static function getAll(){
		 $sql = "select * from ".self::$tablename;
		$query = Executor::doit($sql);
		return Model::many($query[0],new UserData());
	}

	public static function getAllBy($k,$v){
		 $sql = "select * from ".self::$tablename." where $k=\"$v\"";
		$query = Executor::doit($sql);
		return Model::many($query[0],new UserData());
	}


	public static function getLike($q){
		$sql = "select * from ".self::$tablename." where name like '%$q%'";
		$query = Executor::doit($sql);
		return Model::many($query[0],new UserData());
	}


}

?>