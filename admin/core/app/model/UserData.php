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
		// username incluido: el login del panel acepta email o username, y el
		// formulario lo pide (antes se descartaba en silencio).
		$sql = "insert into user (name,lastname,username,email,password,role,tienda_id,created_at) ";
		$sql .= "value (\"$this->name\",\"$this->lastname\",\"$this->username\",\"$this->email\",\"$this->password\",\"$role\",$tienda,$this->created_at)";
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

	/* ¿Puede $me editar o borrar a $obj?  (Fase 6 · auditoría #1)
	   - El super-admin sólo lo toca otro super-admin (nadie lo degrada/borra).
	   - Un dueño sólo toca usuarios de SU tienda; el super-admin, de todas.
	   - Los usuarios sin tienda (legados previos a A0) los edita cualquier
	     dueño: antes del multi-tenant eran el único equipo del panel.      */
	public static function puedeTocar($me, $obj){
		if(!$me || !$obj){ return false; }
		if($obj->role === "superadmin"){ return $me->role === "superadmin"; }
		if($me->role === "superadmin"){ return true; }
		$mia  = (int)Tenant::id();
		$suya = (int)$obj->tienda_id;
		if($mia > 0 && $suya > 0 && $mia !== $suya){ return false; }
		return true;
	}

	// Usuarios visibles para $me (misma regla que puedeTocar) — tabla del panel.
	public static function getAllVisibles($me){
		$vis = array();
		foreach(self::getAll() as $u){
			if(self::puedeTocar($me, $u)){ $vis[] = $u; }
		}
		return $vis;
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