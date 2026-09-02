<?php
class BebidaData {
	public static $tablename = "bebida";

	public $id, $sabor, $medida, $sabor_options, $precio, $es_gratis, $is_active;

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
}
?>
