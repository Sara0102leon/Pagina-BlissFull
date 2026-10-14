<?php
// PlanData — catálogo de planes de suscripción (A1)
class PlanData {
	public static $tablename = "plan";

	public $id, $code, $name, $price, $currency, $max_products, $max_sedes,
	       $kds, $drag_drop, $theme, $reports, $priority_support, $position, $is_active;

	public function __construct(){
		$this->id = null;
		$this->max_products = null;
	}

	public static function getByCode($code){
		$sql = "select * from ".self::$tablename." where code=\"$code\" and is_active=1 limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new PlanData());
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=".(int)$id." limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new PlanData());
	}

	public static function getAllActives(){
		$sql = "select * from ".self::$tablename." where is_active=1 order by position asc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new PlanData());
	}
}