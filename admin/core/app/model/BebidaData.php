<?php
class BebidaData {
	public static $tablename = "bebida";

	public $id, $sabor, $medida, $sabor_options, $precio, $es_gratis, $is_active, $agotado_sedes = array();

	public function __construct(){
		$this->id = null;
		$this->sabor = "";
		$this->medida = "";
		$this->sabor_options = "";
		$this->precio = "0";
		$this->es_gratis = "0";
		$this->is_active = "1";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (sabor,medida,sabor_options,precio,es_gratis,is_active) ";
		$sql .= "value (\"".$this->sabor."\",\"".$this->medida."\",\"".$this->sabor_options."\",\"".floatval($this->precio)."\"," . intval($this->es_gratis) . "," . intval($this->is_active) . ")";
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set sabor=\"$this->sabor\",medida=\"$this->medida\",sabor_options=\"$this->sabor_options\",precio=\"".floatval($this->precio)."\",es_gratis=" . intval($this->es_gratis) . ",is_active=" . intval($this->is_active) . " where id=$this->id";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new BebidaData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by es_gratis desc, sabor asc, medida asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BebidaData());
	}

	public static function getActive(){
		$sql = "select * from ".self::$tablename." where is_active=1 order by es_gratis desc, sabor asc, medida asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BebidaData());
	}

	// Bebidas activas + sedes donde está agotada (pivot bebida_sede)
	public static function getActiveWithSedes(){
		$rows = self::getActive();
		$map = self::agotadoMap();
		foreach($rows as $b){
			$b->agotado_sedes = isset($map[intval($b->id)]) ? $map[intval($b->id)] : array();
		}
		return $rows;
	}

	// bebida_id => array(sede_id)
	public static function agotadoMap(){
		$map = array();
		try {
			$q = Executor::doit("select bebida_id, sede_id from bebida_sede where agotado=1");
			$r = $q[0];
			if($r){
				while($row = $r->fetch_assoc()){
					$map[intval($row["bebida_id"])][] = intval($row["sede_id"]);
				}
			}
		} catch(\Throwable $e){}
		return $map;
	}

	public static function esAgotadaEn($bebida_id, $sede_id){
		if(!$bebida_id || !$sede_id){ return false; }
		try {
			$q = Executor::doit("select agotado from bebida_sede where bebida_id=".intval($bebida_id)." and sede_id=".intval($sede_id));
			$r = $q[0];
			if($r && ($row = $r->fetch_assoc()) && intval($row["agotado"])==1){ return true; }
		} catch(\Throwable $e){}
		return false;
	}

	public static function setAgotado($bebida_id, $sede_id, $agotado){
		$bebida_id = intval($bebida_id);
		$sede_id = intval($sede_id);
		$agotado = $agotado ? 1 : 0;
		$sql = "insert into bebida_sede (bebida_id, sede_id, agotado) values ($bebida_id, $sede_id, $agotado) ";
		$sql .= "on duplicate key update agotado=$agotado";
		return Executor::doit($sql);
	}
}
?>
