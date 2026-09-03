<?php
class SedeHorarioData {
	public static $tablename = "sede_horario";

	public $id, $sede_id, $dia, $hora_open, $hora_close;

	public function __construct(){
		$this->id = null;
		$this->sede_id = null;
		$this->dia = "";
		$this->hora_open = "";
		$this->hora_close = "";
	}

	public static function getBySede($sede_id){
		$sql = "select * from ".self::$tablename." where sede_id=".intval($sede_id)." order by field(dia,'lunes','martes','miercoles','jueves','viernes','sabado','domingo')";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SedeHorarioData());
	}

	public static function mapForSede($sede_id){
		$map = array();
		foreach(array("lunes","martes","miercoles","jueves","viernes","sabado","domingo") as $d){
			$map[$d] = array("open"=>"", "close"=>"");
		}
		foreach(self::getBySede($sede_id) as $h){
			$map[$h->dia]["open"] = $h->hora_open;
			$map[$h->dia]["close"] = $h->hora_close;
		}
		return $map;
	}

	public static function save($sede_id, $dia, $open, $close){
		$sede_id = intval($sede_id);
		$dia = trim($dia);
		$open = trim((string)$open) !== "" ? trim($open) : null;
		$close = trim((string)$close) !== "" ? trim($close) : null;
		$open_sql = $open !== null ? "'".$open."'" : "NULL";
		$close_sql = $close !== null ? "'".$close."'" : "NULL";
		$sql = "insert into ".self::$tablename." (sede_id,dia,hora_open,hora_close) value ($sede_id,'$dia',$open_sql,$close_sql) on duplicate key update hora_open=$open_sql, hora_close=$close_sql";
		Executor::doit($sql);
	}

	public static function deleteBySede($sede_id){
		Executor::doit("delete from ".self::$tablename." where sede_id=".intval($sede_id));
	}
}
?>
