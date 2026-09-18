<?php
class SedeData {
	public static $tablename = "sede";

	public $id, $name, $address, $phone, $image, $lat, $lng, $maps, $is_active, $created_at, $chatwoot_group_conversation_id;

	public function __construct(){
		$this->id = null;
		$this->name = "";
		$this->address = "";
		$this->phone = "";
		$this->image = "";
		$this->lat = "";
		$this->lng = "";
		$this->maps = "";
		$this->is_active = "1";
	}

	public function add(){
		$lat_sql = ($this->lat!="" && is_numeric($this->lat)) ? $this->lat : "NULL";
		$lng_sql = ($this->lng!="" && is_numeric($this->lng)) ? $this->lng : "NULL";
		$sql = "insert into ".self::$tablename." (name,address,phone,image,lat,lng,maps,is_active) ";
		$sql .= "value (\"$this->name\",\"$this->address\",\"$this->phone\",\"$this->image\",$lat_sql,$lng_sql,\"$this->maps\",$this->is_active)";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}

	public function update(){
		$lat_sql = ($this->lat!="" && is_numeric($this->lat)) ? $this->lat : "NULL";
		$lng_sql = ($this->lng!="" && is_numeric($this->lng)) ? $this->lng : "NULL";
		$sql = "update ".self::$tablename." set name=\"$this->name\",address=\"$this->address\",phone=\"$this->phone\",image=\"$this->image\",lat=$lat_sql,lng=$lng_sql,maps=\"$this->maps\",is_active=\"$this->is_active\" where id=$this->id";
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