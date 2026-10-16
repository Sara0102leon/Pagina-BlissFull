<?php
// PlanData — catálogo de planes de suscripción (A1)
// + CRUD y OFERTAS con vigencia (oct 2026).
//
// Precio: `price` es la tarifa normal. `offer_price` + `offer_from`/`offer_to`
// son una promoción opcional con ventana de fechas (los extremos pueden quedar
// vacíos = abiertos). Mientras la oferta esté VIGENTE, el precio que se cobra
// es `effectivePrice()` — lo usan el panel de renovación, el checkout de la
// landing y el webhook.
//
// Estados de la oferta (offerState):
//   OFF_NONE sin oferta · OFF_PROG programada (no empezó) ·
//   OFF_ACT  vigente     · OFF_END vencida
class PlanData {
	public static $tablename = "plan";

	public $id, $code, $name, $price, $currency, $max_products, $max_sedes,
	       $kds, $drag_drop, $theme, $reports, $priority_support, $position, $is_active,
	       $description, $offer_price, $offer_from, $offer_to, $offer_label;

	const OFF_NONE = 0;
	const OFF_PROG = 1;
	const OFF_ACT  = 2;
	const OFF_END  = 3;

	public function __construct(){
		$this->id = null;
		$this->max_products = null;
		$this->description = "";
		$this->offer_price = null;
		$this->offer_from  = null;
		$this->offer_to    = null;
		$this->offer_label = "";
	}

	// ── Lecturas ──────────────────────────────────────────────────────
	public static function getByCode($code){
		$sql = "select * from ".self::$tablename." where code=\"".self::esc($code)."\" and is_active=1 limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new PlanData());
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=".(int)$id." limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new PlanData());
	}

	public static function getAllActives(){
		$sql = "select * from ".self::$tablename." where is_active=1 order by position asc, id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new PlanData());
	}

	// Todos los planes (incluidos desactivados) — panel de plataforma.
	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by position asc, id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new PlanData());
	}

	public static function codeFree($code, $exceptId = 0){
		$sql = "select id from ".self::$tablename." where code=\"".self::esc($code)."\" and id<>".(int)$exceptId." limit 1";
		$query = Executor::doit($sql);
		return !($query[0] && $query[0]->num_rows > 0);
	}

	public static function nextPosition(){
		$q = Executor::doit("select coalesce(max(position),0)+1 p from ".self::$tablename);
		if($q[0] && ($r = $q[0]->fetch_assoc())){ return (int)$r["p"]; }
		return 1;
	}

	// ¿Hay tiendas pagando este plan? (no se puede borrar)
	public static function tieneSuscripciones($id){
		$q = Executor::doit("select id from suscripcion where plan_id=".(int)$id." limit 1");
		return ($q[0] && $q[0]->num_rows > 0);
	}

	// ── Escritura ─────────────────────────────────────────────────────
	public function add(){
		$sql = "insert into ".self::$tablename." (".$this->cols().") value (".$this->vals().")";
		return Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set ".$this->assigns()." where id=".(int)$this->id;
		return Executor::doit($sql);
	}

	public function save($id = 0){
		$this->id = (int)$id;
		if($this->id > 0){ return $this->update(); }
		return $this->add();
	}

	public static function setActive($id, $on){
		$on = $on ? 1 : 0;
		return Executor::doit("update ".self::$tablename." set is_active=$on where id=".(int)$id);
	}

	public static function del($id){
		return Executor::doit("delete from ".self::$tablename." where id=".(int)$id);
	}

	// ── Oferta con vigencia ───────────────────────────────────────────
	public static function offerState($p){
		if(!$p || !isset($p->offer_price)){ return self::OFF_NONE; }
		if((float)$p->offer_price <= 0){ return self::OFF_NONE; }
		$hoy  = strtotime(date("Y-m-d"));
		$from = self::dateTs($p->offer_from);
		$to   = self::dateTs($p->offer_to);
		if($from !== null && $hoy < $from){ return self::OFF_PROG; }
		if($to   !== null && $hoy > $to){ return self::OFF_END; }
		return self::OFF_ACT;
	}

	// Devuelve los datos de la oferta SÓLO si está vigente (null si no).
	public static function offerInfo($p){
		if(self::offerState($p) !== self::OFF_ACT){ return null; }
		$hoy  = strtotime(date("Y-m-d"));
		$to   = self::dateTs($p->offer_to);
		$from = self::dateTs($p->offer_from);
		$orig = (float)$p->price;
		$nuev = (float)$p->offer_price;
		$pct  = $orig > 0 ? (int)round(100 - ($nuev / $orig * 100)) : 0;
		$def  = ($pct > 0) ? "Oferta -".$pct."%" : "Oferta especial";
		$dias = $to !== null ? (int)floor(($to - $hoy) / 86400) + 1 : null;
		return array(
			"price"   => $nuev,
			"regular" => $orig,
			"pct"     => $pct,
			"label"   => ($p->offer_label && trim($p->offer_label) !== "") ? $p->offer_label : $def,
			"from"    => $from !== null ? date("Y-m-d", $from) : "",
			"to"      => $to   !== null ? date("Y-m-d", $to)   : "",
			"dias"    => $dias,
		);
	}

	// Precio a cobrar HOY (oferta vigente si existe).
	public static function effectivePrice($p){
		if(!$p){ return 0.0; }
		$of = self::offerInfo($p);
		return $of ? $of["price"] : (float)$p->price;
	}

	// Texto del estado para el panel: "" · "programada" · "vigente" · "vencida"
	public static function offerStateLabel($p){
		switch(self::offerState($p)){
			case self::OFF_PROG: return "programada";
			case self::OFF_ACT:  return "vigente";
			case self::OFF_END:  return "vencida";
		}
		return "";
	}

	// ── Helpers internos (armado de SQL) ──────────────────────────────
	// Una sola lista de [columna, valor]; cols/vals/assigns se derivan de ella
	// así el INSERT y el UPDATE no se pueden desincronizar.
	private function cols(){
		$c = array();
		foreach($this->assignParts() as $pair){ $c[] = $pair[0]; }
		return implode(",", $c);
	}
	private function vals(){
		$v = array();
		foreach($this->assignParts() as $pair){ $v[] = $pair[1]; }
		return implode(",", $v);
	}
	private function assigns(){
		$a = array();
		foreach($this->assignParts() as $pair){ $a[] = $pair[0]."=".$pair[1]; }
		return implode(",", $a);
	}

	// [columna => valor SQL] — mismo orden que cols()
	private function assignParts(){
		$cur = strtoupper(substr((string)$this->currency, 0, 3));
		if($cur === ""){ $cur = "USD"; }
		$cmp = array();
		$cmp[] = array("code",        self::sqlStr($this->code));
		$cmp[] = array("name",        self::sqlStr($this->name));
		$cmp[] = array("price",       self::sqlNum($this->price));
		$cmp[] = array("currency",    self::sqlStr($cur));
		$cmp[] = array("max_products", self::sqlInt($this->max_products));
		$cmp[] = array("max_sedes",   self::sqlInt($this->max_sedes, 1));
		$cmp[] = array("kds",         self::sqlBool($this->kds));
		$cmp[] = array("drag_drop",   self::sqlBool($this->drag_drop));
		$cmp[] = array("theme",       self::sqlBool($this->theme));
		$cmp[] = array("reports",     self::sqlBool($this->reports));
		$cmp[] = array("priority_support", self::sqlBool($this->priority_support));
		$cmp[] = array("position",    (string)(int)$this->position);
		$cmp[] = array("is_active",   self::sqlBool($this->is_active));
		$cmp[] = array("description", self::sqlStr($this->description));
		$cmp[] = array("offer_price", self::sqlMoney($this->offer_price));
		$cmp[] = array("offer_from",  self::sqlDate($this->offer_from));
		$cmp[] = array("offer_to",    self::sqlDate($this->offer_to));
		$cmp[] = array("offer_label", self::sqlStr($this->offer_label));
		return $cmp;
	}

	private static function esc($s){
		$con = Database::getCon();
		return $con ? $con->real_escape_string((string)$s) : addslashes((string)$s);
	}
	private static function sqlStr($v){ return "\"".self::esc((string)$v)."\""; }
	private static function sqlNum($v){ return sprintf("%.2f", max(0, (float)$v)); }
	// "" / null -> NULL (se acepta "∞"), si no entero
	private static function sqlInt($v, $def = null){
		if($v === "" || $v === null){ return $def === null ? "null" : (string)(int)$def; }
		return (string)(int)$v;
	}
	// "" / null / <=0 -> NULL (sin oferta)
	private static function sqlMoney($v){
		if($v === "" || $v === null){ return "null"; }
		$f = (float)$v;
		if($f <= 0){ return "null"; }
		return sprintf("%.2f", $f);
	}
	// "" / fecha inválida -> NULL, si no 'YYYY-MM-DD'
	private static function sqlDate($v){
		if($v === "" || $v === null){ return "null"; }
		$ts = strtotime((string)$v);
		return $ts ? "'".date("Y-m-d", $ts)."'" : "null";
	}
	private static function sqlBool($v){ return $v ? "1" : "0"; }

	private static function dateTs($v){
		if(!$v || substr((string)$v, 0, 10) === "0000-00-00"){ return null; }
		$ts = strtotime((string)$v);
		return $ts ? $ts : null;
	}
}
