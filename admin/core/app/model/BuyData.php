<?php
class BuyData {
	public static $tablename = "buy";

	public $id, $k, $code, $coupon_id, $client_id, $created_at, $paymethod_id, $delivery_zone_id, $sede_id, $capture, $note, $scheduled_at, $status_id, $name, $c, $m, $chatwoot_conversation_id, $chatwoot_contact_id, $notified;

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
		$this->scheduled_at = "";
		$this->status_id = "";
	}

	public function getStatus(){ return StatusData::getById($this->status_id);}
	public function getClient(){ return ClientData::getById($this->client_id);}
	public function getPaymethod(){ return PaymethodData::getById($this->paymethod_id);}
	public function getDeliveryZone(){ return $this->delivery_zone_id ? DeliveryZoneData::getById($this->delivery_zone_id) : null; }
	public function getSede(){ return $this->sede_id ? SedeData::getById($this->sede_id) : null; }

	public function add(){
		$cols = $this->existingColumns();
		$zone_sql = $this->delivery_zone_id!="" ? $this->delivery_zone_id : "NULL";
		$sede_sql = $this->sede_id!="" ? $this->sede_id : "NULL";
		$sched_sql = ($this->scheduled_at!="" && isset($cols["scheduled_at"])) ? "\"$this->scheduled_at\"" : "NULL";
		$fields = array("k","code","coupon_id","client_id","created_at","paymethod_id","delivery_zone_id","sede_id","capture","note","scheduled_at","status_id");
		$names = array();
		$vals = array();
		foreach($fields as $f){
			if(!isset($cols[$f])){ continue; }
			$names[] = $f;
			switch($f){
				case "coupon_id": $vals[] = $this->coupon_id; break;
				case "created_at": $vals[] = $this->created_at; break;
				case "delivery_zone_id": $vals[] = $zone_sql; break;
				case "sede_id": $vals[] = $sede_sql; break;
				case "capture": $vals[] = ($this->capture!="" ? "\"$this->capture\"" : "NULL"); break;
				case "note": $vals[] = ($this->note!="" ? "\"$this->note\"" : "NULL"); break;
				case "scheduled_at": $vals[] = $sched_sql; break;
				default: $vals[] = "\"".$this->{$f}."\"";
			}
		}
		$sql = "insert into ".self::$tablename." (".implode(",",$names).") ";
		$sql .= "value (".implode(",",$vals).")";
		return Executor::doit($sql);
	}

	private function existingColumns(){
		$cols = array();
		try {
			$r = Executor::doit("SHOW COLUMNS FROM ".self::$tablename);
			if($r[0] !== false){
				while($row = $r[0]->fetch_assoc()){ $cols[strtolower($row["Field"])] = true; }
			}
		} catch(\Throwable $e){}
		return $cols;
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

	public static function getByRangeDelivered($start,$end){
		$sql = "select * from ".self::$tablename." where status_id=5 and (created_at>=\"$start\" and created_at<=\"$end\") order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BuyData());
	}

	public static function getAllDelivered(){
		$sql = "select * from ".self::$tablename." where status_id=5 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new BuyData());
	}

	public static function getByConversationId($cw_conversation_id){
		$sql = "select * from ".self::$tablename." where chatwoot_conversation_id=$cw_conversation_id order by id desc limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0],new BuyData());
	}

	public function linkConversation($cw_conversation_id,$cw_contact_id){
		$sql = "update ".self::$tablename." set chatwoot_conversation_id=$cw_conversation_id, chatwoot_contact_id=".($cw_contact_id!=""?$cw_contact_id:"NULL")." where id=$this->id";
		Executor::doit($sql);
		$this->chatwoot_conversation_id = $cw_conversation_id;
		$this->chatwoot_contact_id = $cw_contact_id;
	}

	public function cascadeToFinal(){
		$sql = "update ".self::$tablename." set status_id=5 where id=$this->id";
		Executor::doit($sql);
		$this->status_id = 5;
	}

	public  function getTotal(){
		$products = BuyProductData::getAllByBuyId($this->id);
		$total=0;
		foreach ($products as $px) {
			$p = ProductData::getById($px->product_id);
			$total+=(ProductData::getEffectivePrice($p) + $px->getExtrasTotal())*$px->q;
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
