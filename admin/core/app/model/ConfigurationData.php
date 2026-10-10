<?php
// ConfigurationData — ajustes del menú. Multi-tenant: cada tienda tiene sus propias
// filas (cfg_id = tienda_id). La configuración base vive en cfg_id=1 y sirve de
// herencia/plantilla para las tiendas que aún no han personalizado una clave.
//
// Columnas reales de la tabla: id, name, label, kind, val, cfg_id (UNIQUE name+cfg_id).
class ConfigurationData {
	public static $tablename = "configuration";

	public $id, $name, $label, $val, $kind, $cfg_id;

	public function __construct(){
		$this->id = null;
		$this->name = "";
		$this->label = "";
		$this->val = "";
		$this->kind = 1;
		$this->cfg_id = "1";
	}

	// Escapa un valor para un literal SQL (el valor guardado queda tal cual).
	private static function esc($s){
		$con = Database::getCon();
		if($con){ return $con->real_escape_string((string)$s); }
		return addslashes((string)$s);
	}

	// cfg_id de la configuración de la tienda activa (1 = base por defecto).
	public static function cfgId(){
		$t = class_exists("Tenant") ? (int)Tenant::id() : 0;
		return $t > 0 ? $t : 1;
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (name,label,kind,val,cfg_id) ";
		$sql .= "value (\"".self::esc($this->name)."\",\"".self::esc($this->label)."\",".intval($this->kind).",\"".self::esc($this->val)."\",".intval($this->cfg_id).")";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=".intval($id);
		Executor::doit($sql);
	}
	public function del(){
		$sql = "delete from ".self::$tablename." where id=".intval($this->id);
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set name=\"".self::esc($this->name)."\",label=\"".self::esc($this->label)."\",kind=".intval($this->kind).",val=\"".self::esc($this->val)."\" where id=".intval($this->id);
		Executor::doit($sql);
	}

	// Guarda el valor para la TIENDA ACTIVA. Si la tienda aún no tiene la clave,
	// crea la fila heredando label/kind de la configuración base (cfg_id=1).
	public static function updateValFromName($name,$val){
		$cfg = self::cfgId();
		$en = self::esc($name);
		$ev = self::esc($val);
		$found = self::rowByName($name, $cfg);
		if($found && intval($found->id) > 0){
			Executor::doit("update ".self::$tablename." set val=\"$ev\" where name=\"$en\" and cfg_id=$cfg");
			return;
		}
		$base = self::rowByName($name, 1);
		$label = $base ? self::esc($base->label) : "";
		$kind  = $base ? intval($base->kind) : 1;
		Executor::doit("insert into ".self::$tablename." (name,label,kind,val,cfg_id) values (\"$en\",\"$label\",$kind,\"$ev\",$cfg)");
	}

	// Fila cruda de una clave dentro de un cfg_id concreto (sin herencia).
	private static function rowByName($name, $cfg){
		$cfg = intval($cfg);
		$sql = "select * from ".self::$tablename." where name=\"".self::esc($name)."\" and cfg_id=$cfg limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new ConfigurationData());
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=".intval($id);
		$query = Executor::doit($sql);
		return Model::one($query[0],new ConfigurationData());
	}

	// Lee una clave para la tienda activa; si la tienda no la define, hereda de la base (cfg_id=1).
	public static function getByPreffix($id){
		$cfg = self::cfgId();
		$row = self::rowByName($id, $cfg);
		if($row===null && $cfg!==1){
			$row = self::rowByName($id, 1);
		}
		return $row;
	}

	// Config efectiva de la tienda: base (cfg_id=1) sobreescrita por la de la tienda.
	// La base se ordena primero (desc sobre cfg_id=1) para que las filas de la tienda ganen.
	private static function effectiveRows(){
		$cfg = self::cfgId();
		$sql = "select * from ".self::$tablename." where cfg_id=1 or cfg_id=$cfg order by (cfg_id=1) desc, id asc";
		$query = Executor::doit($sql);
		$rows = Model::many($query[0],new ConfigurationData());
		$out = array();
		foreach($rows as $r){ $out[$r->name] = $r; }
		return array_values($out);
	}

	public static function getAll(){
		return self::effectiveRows();
	}

	public static function getPublics(){
		return self::effectiveRows();
	}

	// Siembra toda la configuración base (cfg_id=1) en una tienda nueva (idempotente).
	public static function seedFromBase($cfg){
		$cfg = (int)$cfg;
		if($cfg<=0 || $cfg===1){ return; }
		$existing = array();
		$q2 = Executor::doit("select name from ".self::$tablename." where cfg_id=$cfg");
		if($q2[0]){ while($r=$q2[0]->fetch_array()){ $existing[$r["name"]] = true; } }
		$query = Executor::doit("select * from ".self::$tablename." where cfg_id=1");
		$base = Model::many($query[0],new ConfigurationData());
		foreach($base as $b){
			if(isset($existing[$b->name])){ continue; }
			Executor::doit("insert into ".self::$tablename." (name,label,kind,val,cfg_id) values (\"".self::esc($b->name)."\",\"".self::esc($b->label)."\",".intval($b->kind).",\"".self::esc($b->val)."\",$cfg)");
		}
	}

}
?>
