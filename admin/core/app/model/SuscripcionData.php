<?php
// SuscripcionData — vínculo tienda <-> plan, con el estado del checkout Zenobank (A1)
// + renovación / recordatorio (Fase 6).
//
// Una tienda tiene UNA suscripción vigente (la de id mayor). Al renovar se REUSA
// esa fila: se vuelve a poner zeno_status='OPEN' con un checkout nuevo y, al
// aprobarse el pago, markCompleted() EXTIENDE el periodo en vez de reiniciarlo.
class SuscripcionData {
	public static $tablename = "suscripcion";

	public $id, $tienda_id, $plan_id, $zeno_checkout_id, $zeno_status,
	       $paid_amount, $periodo_inicio, $periodo_fin, $recordatorio_at,
	       $created_at, $updated_at;

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

	// Suscripción vigente de una tienda (la más reciente)
	public static function getLatestByTienda($tienda_id){
		$sql = "select * from ".self::$tablename." where tienda_id=".(int)$tienda_id." order by id desc limit 1";
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

	public function setPlan($plan_id){
		$sql = "update ".self::$tablename." set plan_id=".(int)$plan_id.", updated_at=NOW() where id=".(int)$this->id;
		Executor::doit($sql);
		$this->plan_id = (int)$plan_id;
	}

	// Prepara la fila para un nuevo pago (renovación): plan + checkout + OPEN.
	public function openRenewal($plan_id, $checkoutId){
		$sql = "update ".self::$tablename." set plan_id=".(int)$plan_id.", zeno_status='OPEN', ";
		$sql .= "zeno_checkout_id=\"$checkoutId\", updated_at=NOW() where id=".(int)$this->id;
		Executor::doit($sql);
		$this->plan_id = (int)$plan_id;
		$this->zeno_status = "OPEN";
		$this->zeno_checkout_id = $checkoutId;
	}

	// Marca que hoy se envió el recordatorio de renovación.
	public function markRecordatorio(){
		Executor::doit("update ".self::$tablename." set recordatorio_at=NOW(), updated_at=NOW() where id=".(int)$this->id);
		$this->recordatorio_at = date("Y-m-d H:i:s");
	}

	// Pago aprobado. Si hay periodo vigente lo EXTIENDE 30 días (renovación);
	// si no, arranca hoy (alta nueva).
	public function markCompleted($paidAmount){
		$base = self::basePeriodo($this->periodo_fin);
		$fin  = date("Y-m-d", strtotime($base . " +30 days"));
		$amount = $paidAmount === null ? "NULL" : (float)$paidAmount;
		$sql = "update ".self::$tablename." set zeno_status='COMPLETED', paid_amount=$amount, ";
		$sql .= "periodo_inicio='$base', periodo_fin='$fin', updated_at=NOW() where id=".(int)$this->id;
		Executor::doit($sql);
		$this->zeno_status = "COMPLETED";
		$this->periodo_inicio = $base;
		$this->periodo_fin = $fin;
	}

	// Extensión manual del super-admin: suma N días al fin vigente (o a hoy).
	public function extend($dias){
		$dias = max(1, (int)$dias);
		$base = self::basePeriodo($this->periodo_fin);
		$fin  = date("Y-m-d", strtotime($base . " +$dias days"));
		Executor::doit("update ".self::$tablename." set zeno_status='COMPLETED', periodo_inicio='$base', periodo_fin='$fin', updated_at=NOW() where id=".(int)$this->id);
		$this->zeno_status = "COMPLETED";
		$this->periodo_inicio = $base;
		$this->periodo_fin = $fin;
	}

	// Base del próximo periodo: el fin vigente si aún no pasó, si no hoy.
	private static function basePeriodo($periodo_fin){
		$hoy = date("Y-m-d");
		if($periodo_fin && substr($periodo_fin,0,10) !== "0000-00-00" && substr($periodo_fin,0,10) >= $hoy){
			return substr($periodo_fin,0,10);
		}
		return $hoy;
	}

	// Días restantes hasta una fecha (negativo si ya venció; null si no hay fecha)
	public static function diasRestantes($fecha){
		if(!$fecha || substr($fecha,0,10) === "0000-00-00"){ return null; }
		$hoy = strtotime(date("Y-m-d"));
		$fin = strtotime(substr($fecha,0,10));
		return (int)floor(($fin - $hoy) / 86400);
	}

	public static function diasAviso(){
		$q = Executor::doit("select val from configuration where name='saas_dias_aviso' and cfg_id=1 limit 1");
		if($q[0] && ($row = $q[0]->fetch_assoc())){ $n = (int)$row["val"]; if($n > 0){ return $n; } }
		return 7;
	}

	public static function fechaFmt($fecha){
		if(!$fecha || substr($fecha,0,10) === "0000-00-00"){ return "—"; }
		$ts = strtotime(substr($fecha,0,10));
		return $ts ? date("d/m/Y", $ts) : $fecha;
	}

	// Estado de renovación de una tienda -> array(level,title,msg,cta) o null.
	// Lo usa el banner del panel (dueño) y la vista de renovación.
	public static function tenantAlert($tienda_id){
		$tienda = TiendaData::getById($tienda_id);
		if(!$tienda){ return null; }
		$est = strtolower(trim($tienda->estatus));
		if($est === "suspendida"){
			return array("level"=>"danger","title"=>"Tu tienda está suspendida",
				"msg"=>"Regulariza tu pago para volver a publicar tu menú.","cta"=>"Renovar plan");
		}
		$sus = self::getLatestByTienda($tienda_id);
		if(!$sus){
			if($est === "pendiente"){
				return array("level"=>"warning","title"=>"Tu plan está pendiente de pago",
					"msg"=>"Activa tu plan para publicar tu menú.","cta"=>"Activar plan");
			}
			return null; // cliente migrado sin suscripción -> sin aviso
		}
		$st   = strtoupper(trim($sus->zeno_status));
		$dias = self::diasRestantes($sus->periodo_fin);
		if($st === "OPEN"){
			return array("level"=>"info","title"=>"Tienes un pago pendiente",
				"msg"=>"Completa el pago para activar o renovar tu plan.","cta"=>"Completar pago");
		}
		if($dias === null){
			return array("level"=>"warning","title"=>"Sin periodo de pago registrado",
				"msg"=>"Renueva tu plan para mantener tu menú activo.","cta"=>"Renovar plan");
		}
		if($dias < 0){
			return array("level"=>"danger","title"=>"Tu plan venció",
				"msg"=>"Venció el ".self::fechaFmt($sus->periodo_fin)." (hace ".abs($dias)." día".(abs($dias)==1?"":"s")."). Tu menú puede dejar de estar disponible.",
				"cta"=>"Renovar plan");
		}
		if($dias <= self::diasAviso()){
			return array("level"=>"warning","title"=>"Tu plan está por vencer",
				"msg"=>"Vence en $dias día".($dias==1?"":"s")." (".self::fechaFmt($sus->periodo_fin)."). Renuévalo para no perder tu menú.",
				"cta"=>"Renovar plan");
		}
		if($st === "EXPIRED" || $st === "PARTIALLY_PAID"){
			return array("level"=>"danger","title"=>"Pago incompleto",
				"msg"=>"Tu último pago no se completó. Inténtalo de nuevo.","cta"=>"Renovar plan");
		}
		return null;
	}
}
