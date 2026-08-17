<?php
class SedeDeliveryZoneData {
	public static $tablename = "sede_delivery_zone";

	public $id, $sede_id, $delivery_zone_id, $price;

	public function __construct(){
		$this->id = null;
		$this->sede_id = null;
		$this->delivery_zone_id = null;
		$this->price = "0";
	}

	public static function getBySede($sede_id){
		$sql = "select * from ".self::$tablename." where sede_id=$sede_id";
		$query = Executor::doit($sql);
		return Model::many($query[0],new SedeDeliveryZoneData());
	}

	public static function getPrice($sede_id, $delivery_zone_id){
		$sql = "select * from ".self::$tablename." where sede_id=$sede_id and delivery_zone_id=$delivery_zone_id";
		$query = Executor::doit($sql);
		$row = Model::one($query[0],new SedeDeliveryZoneData());
		return ($row && $row->id) ? floatval($row->price) : null;
	}

	public static function save($sede_id, $delivery_zone_id, $price){
		$price = floatval($price);
		$sql = "insert into ".self::$tablename." (sede_id, delivery_zone_id, price) ";
		$sql .= "value ($sede_id, $delivery_zone_id, $price) ";
		$sql .= "on duplicate key update price=$price";
		Executor::doit($sql);
	}

}
?>