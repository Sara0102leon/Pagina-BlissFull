<?php
// Tenant — contexto multi-tenant activo en la petición actual.
// Resuelve la tienda actual:
//   1) admin: el usuario logueado pertenece a una tienda (user.tienda_id)
//   2) público: el subdominio del Host header (p.ej. donapepita.localhost / donapepita.sazon.app)
//   3) fallback: primera tienda activa (default) — antes de A2 no hay tiendas y no se escopa nada
// Una vez resuelta, los modelos usan Tenant::filter() para acotar sus consultas por tienda_id.
class Tenant {
	public static $current = null;   // TiendaData|null
	public static $resolved = false;
	public static $hostIsStore = false; // Host tipo "<sub>.<base>" (subdominio de tienda, exista o no)

	public static function resolve(){
		if(self::$resolved){ return self::$current; }
		self::$resolved = true;
		$id = 0;

		// 1) sesión (admin): el dueño/equipo pertenece a una tienda
		if(isset($_SESSION["user_id"]) && intval($_SESSION["user_id"])>0){
			$u = UserData::getById($_SESSION["user_id"]);
			if($u && $u->tienda_id && intval($u->tienda_id)>0){ $id = (int)$u->tienda_id; }
			// Super-admin "entrando como" otra tienda: la impersonada manda
			// (sólo si el usuario logueado es realmente superadmin).
			if($u && $u->role === "superadmin" && self::impersonating() > 0){
				$id = self::impersonating();
			}
		}

		// 2) Host header: subdominio de una tienda (menú público)
		if(!$id){
			$host = isset($_SERVER["HTTP_HOST"]) ? strtolower(trim((string)$_SERVER["HTTP_HOST"])) : "";
			$host = preg_replace('/:\d+$/', "", $host);
			$sub = self::subdomainFromHost($host);
			self::$hostIsStore = self::isStoreStyleHost($host);
			if($sub !== ""){
				$t = TiendaData::getBySubdomain($sub);
				if($t){ $id = (int)$t->id; }
			}
			// Subdominio de tienda inexistente → sin fallback (el front publicará 404)
			if(!$id && self::$hostIsStore){
				self::$current = null;
				return null;
			}
		}

		// 3) fallback: primera tienda activa (por defecto -> la pizzería tras A2)
		if(!$id){
			$q = Executor::doit("select id from tienda where estatus='activa' order by id asc limit 1");
			if($q[0] && $row = $q[0]->fetch_assoc()){ $id = (int)$row["id"]; }
		}

		if($id>0){ self::$current = TiendaData::getById($id); }
		return self::$current;
	}

	public static function current(){
		return self::resolve();
	}

	// Id de la tienda que un super-admin está "viendo como" (0 = ninguna).
	public static function impersonating(){
		if(empty($_SESSION["impersonate_tienda"])){ return 0; }
		return (int)$_SESSION["impersonate_tienda"];
	}

	// id de tienda activa (0 = sin tenant)
	public static function id(){
		self::resolve();
		return self::$current ? (int)self::$current->id : 0;
	}

	// Valor SQL del id (para INSERTs): el entero o NULL si no hay tenant
	public static function idSql(){
		$id = self::id();
		return $id > 0 ? (string)$id : "NULL";
	}

	// Fragmento SQL listo para concatenar en un WHERE:
	//   con tenant -> "tienda_id=1"  ·  sin tenant -> "1=1" (mismo comportamiento que antes)
	public static function filter($alias = ""){
		$id = self::id();
		if(!$id){ return "1=1"; }
		$p = ($alias !== "") ? $alias . "." : "";
		return $p . "tienda_id=" . $id;
	}

	// Extrae el subdominio de una tienda del Host, o "" si es dominio raíz/desconocido.
	public static function subdomainFromHost($host){
		if($host === ""){ return ""; }
		foreach(self::baseDomains() as $b){
			if($host === $b){ return ""; } // dominio raíz -> sin subdominio
			if(substr($host, -strlen($b) - 1) === "." . $b){
				$prefix = substr($host, 0, -strlen($b) - 1);
				if($prefix !== "" && strpos($prefix, ".") === false){ return $prefix; }
			}
		}
		return "";
	}

	// true si el Host tiene forma "<sub>.<base>" con <sub> de una sola etiqueta
	// (subdominio de tienda, exista o no). false para raíz y hosts ajenos.
	public static function isStoreStyleHost($host){
		if($host === ""){ return false; }
		foreach(self::baseDomains() as $b){
			if($host === $b){ return false; }
			if(substr($host, -strlen($b) - 1) === "." . $b){
				$prefix = substr($host, 0, -strlen($b) - 1);
				if($prefix !== "" && strpos($prefix, ".") === false){ return true; }
			}
		}
		return false;
	}

	// Dominios base reconocidos (raíz): localhost, saas_base_domain y saas_alt_domains.
	private static function baseDomains(){
		$bases = array("localhost");
		$row = ConfigurationData::getByPreffix("saas_base_domain");
		$base = ($row && trim($row->val) !== "") ? strtolower(trim($row->val)) : "sazon.app";
		if($base !== ""){ $bases[] = $base; }
		$row2 = ConfigurationData::getByPreffix("saas_alt_domains");
		if($row2 && trim($row2->val) !== ""){
			foreach(explode(" ", trim($row2->val)) as $d){
				$d = strtolower(trim($d));
				if($d !== ""){ $bases[] = $d; }
			}
		}
		return $bases;
	}
}