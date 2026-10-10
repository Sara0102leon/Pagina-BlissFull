<?php
// SuscripcionData — vínculo tienda <-> plan, con el estado del checkout Zenobank (A1)
class SuscripcionData {
	public static $tablename = "suscripcion";

	public $id, $tienda_id, $plan_id, $zeno_checkout_id, $zeno_status,
	       $paid_amount, $periodo_inicio, $periodo_fin, $created_at, $updated_at;

	public function __construct(){
		$this->id = null;
		$this->zeno_checkout_id = "";
		$this->zeno_status = "OPEN";
		$this->created_at = "NOW()";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (tienda_id,plan_id,zeno_checkout_id,zeno_status,created_at) ";
		$sql .= "value (".(int)$this->tienda_id.",".(int)$this->plan_id.",\"$this->zeno_checkout_id\",\"$this->zeno_status\",$this->created_at)";
		return Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=".(int)$id." limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new SuscripcionData());
	}

	public static function getByCheckout($checkoutId){
		$sql = "select * from ".self::$tablename." where zeno_checkout_id=\"$checkoutId\" limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new SuscripcionData());
	}

	public function setCheckoutId($checkoutId){
		$sql = "update ".self::$tablename." set zeno_checkout_id=\"$checkoutId\", updated_at=NOW() where id=".(int)$this->id;
		Executor::doit($sql);
	}

	public function setStatus($status){
		$sql = "update ".self::$tablename." set zeno_status=\"$status\", updated_at=NOW() where id=".(int)$this->id;
		Executor::doit($sql);
	}

	public function markCompleted($paidAmount){
		$hoy = date("Y-m-d");
		$fin = date("Y-m-d", strtotime("+30 days"));
		$amount = $paidAmount === null ? "NULL" : (float)$paidAmount;
		$sql = "update ".self::$tablename." set zeno_status='COMPLETED', paid_amount=$amount, ";
		$sql .= "periodo_inicio='$hoy', periodo_fin='$fin', updated_at=NOW() where id=".(int)$this->id;
		Executor::doit($sql);
	}
}