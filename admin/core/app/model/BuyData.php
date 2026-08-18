<?php
class BuyData {
	public static $tablename = "buy";

	public $id, $k, $code, $coupon_id, $client_id, $created_at, $paymethod_id, $delivery_zone_id, $sede_id, $capture, $note, $status_id, $name, $c;

	public function __construct(){
		$this->id = null;
		$this->k = "";
		$this->code = "";
		$this->coupon_id = "null";
		$this->client_id = "";
		$this->created_at = "NOW()";
		$this->paymethod_id = "";
		$this->delivery_zone_id = "";
		$this->sede_id = "";
		$this->capture = "";
		$this->note = "";
		$this->status_id = "";
	}

	public function getStatus(){ return StatusData::getById($this->status_id);}
	public function getClient(){ return ClientData::getById($this->client_id);}
	public function getPaymethod(){ return PaymethodData::getById($this->paymethod_id);}
	public function getDeliveryZone(){ return $this->delivery_zone_id ? DeliveryZoneData::getById($this->delivery_zone_id) : null; }
	public function getSede(){ return $this->sede_id ? SedeData::getById($this->sede_id) : null; }

	public function add(){
		$zone_sql = $this->delivery_zone_id!="" ? $this->delivery_zone_id : "NULL";
		$sede_sql = $this->sede_id!="" ? $this->sede_id : "NULL";
		$sql = "insert into ".self::$tablename." (k,code,coupon_id,client_id,created_at,paymethod_id,delivery_zone_id,sede_id,capture,note,status_id) ";
		$sql .= "value (\"$this->k\",\"$this->code\",$this->coupon_id,\"$this->client_id\",$this->created_at,$this->paymethod_id,$zone_sql,$sede_sql," . ($this->capture!="" ? "\"$this->capture\"" : "NULL") . "," . ($this->note!="" ? "\"$this->note\"" : "NULL") . ",$this->status_id)";
		return Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "delete from ".self::$tablename." where id=$id";
		Executor::doit($sql);
	}
	public function del(){
		$sql = "delete from ".self::$tablename." where id=$this->id";
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set name=\"$this->name\" where id=$this->id";
		Executor::doit($sql);
	}

	public function cancel(){
		$sql = "update ".self::$tablename." set status_id=3 where id=$this->id";
		Executor::doit($sql);
	}

	public function change_status(){
		$sql = "update ".self::$tablename." set status_id=\"$this->status_id\" where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new BuyData());
	}

	public static function countByStatusId($id){
		$sql = "select count(*) as c from ".self::$tablename." where status_id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new BuyData());
	}

	public static function getByCode($id){
		$sql = "select * from ".self::$tablename." where code=\"$id\"";
		$query = Executor::doit($sql);
		return Model::one($query[0],new BuyData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BuyData());
	}

	public static function getAllByDate($date){
		$sql = "select * from ".self::$tablename." where date(created_at)=\"$date\"";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BuyData());
	}

	public static function getByRange($start,$end){
		$sql = "select * from ".self::$tablename." where (created_at>=\"$start\" and created_at<=\"$end\") order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BuyData());
	}

	public  function getTotal(){
		$products = BuyProductData::getAllByBuyId($this->id);
		$total=0;
		foreach ($products as $px) {
			$p = ProductData::getById($px->product_id);
			$total+=($p->price + $px->getExtrasTotal())*$px->q;
		}
		$zone = $this->getDeliveryZone();
		if($zone){ $total += floatval($zone->price); }
		return $total;
	}

	public static function getAllByClientId($id){
		$sql = "select * from ".self::$tablename." where client_id=$id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BuyData());
	}

}

?>
