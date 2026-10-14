<?php
// TiendaData — cada cliente de la plataforma es una tienda (tenant) con su subdominio (A1)
class TiendaData {
	public static $tablename = "tienda";

	public $id, $subdomain, $nombre, $descripcion, $email, $whatsapp, $estatus, $zeno_mode, $created_at;

	public function __construct(){
		$this->id = null;
		$this->descripcion = "";
		$this->email = "";
		$this->whatsapp = "";
		$this->estatus = "pendiente";
		$this->zeno_mode = "demo";
		$this->created_at = "NOW()";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (subdomain,nombre,descripcion,email,whatsapp,estatus,zeno_mode,created_at) ";
		$sql .= "value (\"$this->subdomain\",\"$this->nombre\",\"$this->descripcion\",\"$this->email\",\"$this->whatsapp\",\"$this->estatus\",\"$this->zeno_mode\",$this->created_at)";
		return Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=".(int)$id." limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new TiendaData());
	}

	public static function getBySubdomain($subdomain){
		$sql = "select * from ".self::$tablename." where subdomain=\"$subdomain\" limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new TiendaData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new TiendaData());
	}

	public static function subdomainFree($subdomain){
		$sql = "select id from ".self::$tablename." where subdomain=\"$subdomain\" limit 1";
		$query = Executor::doit($sql);
		return !($query[0] && $query[0]->num_rows > 0);
	}

	public function setEstatus($estatus){
		$sql = "update ".self::$tablename." set estatus=\"$estatus\" where id=".(int)$this->id;
		Executor::doit($sql);
		$this->estatus = $estatus;
	}

	// Datos editables de la ficha de la tienda (nombre de contacto, etc.)
	public function update(){
		$sql = "update ".self::$tablename." set nombre=\"$this->nombre\", descripcion=\"$this->descripcion\", ";
		$sql .= "email=\"$this->email\", whatsapp=\"$this->whatsapp\" where id=".(int)$this->id;
		Executor::doit($sql);
	}
}
