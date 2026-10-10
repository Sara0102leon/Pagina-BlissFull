<?php
class CategoryData {
	public static $tablename = "category";

	public $id, $name, $short_name, $is_active, $in_home, $in_menu, $position, $tienda_id;

	public function __construct(){
		$this->name = "";
		$this->short_name = "";
		$this->is_active = "1";
		$this->position = 0;
	}

	public function add(){
		$pos = ($this->position===""||$this->position===null) ? 0 : intval($this->position);
		$sql = "insert into ".self::$tablename." (name,short_name,is_active,position,tienda_id) ";
		$sql .= "value (\"$this->name\",\"$this->short_name\",$this->is_active,$pos,".Tenant::idSql().")";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "update ".self::$tablename." set is_active=0 where id=$id";
		Executor::doit($sql);
	}
	public function del(){
		$sql = "update ".self::$tablename." set is_active=0 where id=$this->id";
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set name=\"$this->name\",short_name=\"$this->short_name\",is_active=\"$this->is_active\",position=".intval($this->position)." where id=$this->id";
		Executor::doit($sql);
	}

	// --- Orden del menú ---
	public static function nextPosition(){
		$sql = "select coalesce(max(position),0)+1 as np from ".self::$tablename." where ".Tenant::filter();
		$query = Executor::doit($sql);
		$one = Model::one($query[0],new CategoryData());
		return intval($one->np);
	}
	public static function getPrev($pos){
		$sql = "select * from ".self::$tablename." where is_active=1 and ".Tenant::filter()." and position<$pos order by position desc, id desc limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0],new CategoryData());
	}
	public static function getNext($pos){
		$sql = "select * from ".self::$tablename." where is_active=1 and ".Tenant::filter()." and position>$pos order by position asc, id asc limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0],new CategoryData());
	}

	// Reasigna posiciones (0..n-1) a las categorías indicadas, validando tienda.
	public static function reorder($ids){
		if(!is_array($ids)){ return 0; }
		$pos = 0; $n = 0;
		foreach($ids as $id){
			$id = intval($id);
			if($id<=0){ continue; }
			$chk = Executor::doit("select id from ".self::$tablename." where id=$id and ".Tenant::filter()." limit 1");
			if($chk[0] && $chk[0]->fetch_assoc()){
				Executor::doit("update ".self::$tablename." set position=".$pos." where id=$id");
				$pos++; $n++;
			}
		}
		return $n;
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new CategoryData());
	}

	public static function getActives(){
		$sql = "select * from ".self::$tablename." where is_active=1 and ".Tenant::filter()." order by position asc, id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new CategoryData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." where ".Tenant::filter();
		$query = Executor::doit($sql);
		return Model::many($query[0],new CategoryData());
	}
	
	public static function getLike($q){
		$sql = "select * from ".self::$tablename." where name like '%$q%' and ".Tenant::filter();
		$query = Executor::doit($sql);
		return Model::many($query[0],new CategoryData());
	}
	public static function getPublics(){
		$sql = "select * from ".self::$tablename." where is_active=1 and ".Tenant::filter()." order by position asc, id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new CategoryData());
	}
	public static function getByPreffix($id){
		$sql = "select * from ".self::$tablename." where short_name=\"$id\" and ".Tenant::filter();
		$query = Executor::doit($sql);
		return Model::one($query[0],new CategoryData());
	}
	public static function getByShortName($id){ return self::getByPreffix($id); }


}
?>