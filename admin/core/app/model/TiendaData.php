<?php
// TiendaData — cada cliente de la plataforma es una tienda (tenant) con su subdominio (A1)
class TiendaData {
	public static $tablename = "tienda";

	public $id, $subdomain, $nombre, $descripcion, $email, $whatsapp, $estatus, $zeno_mode, $created_at;

	public function __construct(){
		$this->id = null;
		$this->descripcion = "";
		$this->email = "";
		$this->whatsapp = "";
		$this->estatus = "pendiente";
		$this->zeno_mode = "demo";
		$this->created_at = "NOW()";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (subdomain,nombre,descripcion,email,whatsapp,estatus,zeno_mode,created_at) ";
		$sql .= "value (\"$this->subdomain\",\"$this->nombre\",\"$this->descripcion\",\"$this->email\",\"$this->whatsapp\",\"$this->estatus\",\"$this->zeno_mode\",$this->created_at)";
		return Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=".(int)$id." limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new TiendaData());
	}

	public static function getBySubdomain($subdomain){
		$sql = "select * from ".self::$tablename." where subdomain=\"$subdomain\" limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new TiendaData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." order by id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new TiendaData());
	}

	public static function subdomainFree($subdomain){
		$sql = "select id from ".self::$tablename." where subdomain=\"$subdomain\" limit 1";
		$query = Executor::doit($sql);
		return !($query[0] && $query[0]->num_rows > 0);
	}

	public function setEstatus($estatus){
		$sql = "update ".self::$tablename." set estatus=\"$estatus\" where id=".(int)$this->id;
		Executor::doit($sql);
		$this->estatus = $estatus;
	}

	// ── Visitas al menú (para "tiendas más visitadas") ────────────────
	// +1 día/tienda, con una visita por sesión y por día (así el número se
	// acerca más a "visitas reales" que a puros refreshs).
	// NUNCA debe romper el menú público: si la tabla no existe aún
	// (migracion_planes_ofertas.sql) o algo falla, simplemente no se cuenta.
	public static function registrarVisita($tienda_id){
		$tienda_id = (int)$tienda_id;
		if($tienda_id <= 0){ return; }

		$ua = isset($_SERVER["HTTP_USER_AGENT"]) ? trim((string)$_SERVER["HTTP_USER_AGENT"]) : "";
		if($ua === ""){ return; }
		// descarta bots / scrapers / previsualizadores
		if(preg_match('/bot\b|crawl|spider|slurp|curl\/|wget|python-requests|libwww|facebookexternalhit|preview|monitor/i', $ua)){ return; }

		$hoy = date("Y-m-d");
		$clave = "sazon_visita_dia";
		if(isset($_SESSION[$clave]) && $_SESSION[$clave] === $hoy."|".$tienda_id){ return; }

		$con = Database::getCon();
		if(!$con){ return; }
		try{
			$sql = "insert into tienda_visita (tienda_id,fecha,visitas) "
			     . "value ($tienda_id,'$hoy',1) "
			     . "on duplicate key update visitas=visitas+1";
			$r = $con->query($sql);
			if($r !== false){ $_SESSION[$clave] = $hoy."|".$tienda_id; }
		}catch(Throwable $e){
			// tabla pendiente de migrar / BD caída: se ignora a propósito
		}
	}
}
