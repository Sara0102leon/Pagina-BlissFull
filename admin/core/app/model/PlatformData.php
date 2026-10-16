<?php
// PlatformData — Vista de PLATAFORMA (Fase 6). Agrega datos de TODAS las tiendas.
//
// IMPORTANTE: este modelo trabaja a propósito **sin** Tenant::filter(): el
// super-admin necesita ver y gestionar la plataforma completa. Sólo debe
// invocarse desde el panel de plataforma / acciones con guard de super-admin.
//
// Para evitar sorpresas con la herencia de configuración, las consultas a
// `configuration` se hacen siempre sobre cfg_id=1 (configuración base/plataforma).
class PlatformData {

	// ── Config de plataforma (cfg_id=1) ────────────────────────────────
	public static function cfg($name, $def = ""){
		$n = self::esc($name);
		$q = Executor::doit("select val from configuration where name=\"$n\" and cfg_id=1 limit 1");
		if($q[0] && ($row = $q[0]->fetch_assoc()) && $row["val"] !== ""){ return $row["val"]; }
		return $def;
	}

	public static function setCfg($name, $val){
		$n = self::esc($name);
		$v = self::esc($val);
		$q = Executor::doit("select id,label,kind from configuration where name=\"$n\" and cfg_id=1 limit 1");
		if($q[0] && ($row = $q[0]->fetch_assoc())){
			Executor::doit("update configuration set val=\"$v\" where id=".intval($row["id"]));
			return;
		}
		Executor::doit("insert into configuration (name,label,kind,val,cfg_id) values (\"$n\",\"$n\",2,\"$v\",1)");
	}

	private static function esc($s){
		$con = Database::getCon();
		return $con ? $con->real_escape_string((string)$s) : addslashes((string)$s);
	}

	// ── KPIs de la plataforma ──────────────────────────────────────────
	public static function stats(){
		$out = array(
			"tiendas_total"=>0, "tiendas_activas"=>0, "tiendas_pendientes"=>0, "tiendas_suspendidas"=>0,
			"susc_completed"=>0, "susc_open"=>0, "susc_otras"=>0,
			"por_vencer"=>0, "vencidas"=>0, "sin_suscripcion"=>0,
			"ingresos_total"=>0.0, "ingresos_mes"=>0.0, "ticket_promedio"=>0.0, "mrr"=>0.0,
			"pedidos_total"=>0, "pedidos_mes"=>0, "productos_total"=>0,
			// ── Contable / audiencia (lo que mira el super-admin) ────────
			"clientes_total"=>0, "clientes_compradores"=>0, "clientes_nuevos_mes"=>0,
			"usuarios_total"=>0, "usuarios_activos"=>0, "usuarios_duenos"=>0,
			"visitas_total"=>0, "visitas_mes"=>0, "visitas_hoy"=>0,
			"planes_total"=>0, "planes_activos"=>0, "planes_en_oferta"=>0,
			"dias_aviso"=>(int)self::cfg("saas_dias_aviso", 7),
			"aviso_cfg"=>self::cfg("saas_dias_aviso", "7"),
		);

		// Tiendas por estatus
		$q = Executor::doit("select estatus, count(*) c from tienda group by estatus");
		if($q[0]){
			while($r = $q[0]->fetch_assoc()){
				$out["tiendas_total"] += (int)$r["c"];
				$e = strtolower(trim($r["estatus"]));
				if($e === "activa"){ $out["tiendas_activas"] += (int)$r["c"]; }
				elseif($e === "pendiente"){ $out["tiendas_pendientes"] += (int)$r["c"]; }
				elseif($e === "suspendida"){ $out["tiendas_suspendidas"] += (int)$r["c"]; }
			}
		}

		// Suscripciones por estado
		$q = Executor::doit("select zeno_status, count(*) c from suscripcion group by zeno_status");
		if($q[0]){
			while($r = $q[0]->fetch_assoc()){
				$st = strtoupper(trim($r["zeno_status"]));
				if($st === "COMPLETED"){ $out["susc_completed"] += (int)$r["c"]; }
				elseif($st === "OPEN"){ $out["susc_open"] += (int)$r["c"]; }
				else { $out["susc_otras"] += (int)$r["c"]; }
			}
		}

		// Ingresos SaaS
		$q = Executor::doit("select coalesce(sum(paid_amount),0) s from suscripcion where zeno_status='COMPLETED'");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["ingresos_total"] = (float)$r["s"]; }
		$q = Executor::doit("select coalesce(sum(paid_amount),0) s from suscripcion where zeno_status='COMPLETED' and periodo_inicio >= date_format(curdate(),'%Y-%m-01')");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["ingresos_mes"] = (float)$r["s"]; }

		// Actividad (pedidos / productos de todas las tiendas)
		$q = Executor::doit("select count(*) c from buy");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["pedidos_total"] = (int)$r["c"]; }
		$q = Executor::doit("select count(*) c from buy where created_at >= date_format(curdate(),'%Y-%m-01')");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["pedidos_mes"] = (int)$r["c"]; }
		$q = Executor::doit("select count(*) c from product");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["productos_total"] = (int)$r["c"]; }

		// Ticket promedio de los pagos
		if($out["susc_completed"] > 0){
			$out["ticket_promedio"] = $out["ingresos_total"] / $out["susc_completed"];
		}

		// ── Usuarios ───────────────────────────────────────────────────
		$q = Executor::doit("select count(*) total,
			coalesce(sum(case when is_active=1 then 1 else 0 end),0) activos,
			coalesce(sum(case when role='dueno' then 1 else 0 end),0) duenos
			from user");
		if($q[0] && ($r = $q[0]->fetch_assoc())){
			$out["usuarios_total"]   = (int)$r["total"];
			$out["usuarios_activos"] = (int)$r["activos"];
			$out["usuarios_duenos"]  = (int)$r["duenos"];
		}

		// ── Clientes (del menú) ────────────────────────────────────────
		$q = Executor::doit("select count(*) c from client where is_active is null or is_active=1");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["clientes_total"] = (int)$r["c"]; }
		$q = Executor::doit("select count(distinct client_id) c from buy where client_id is not null");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["clientes_compradores"] = (int)$r["c"]; }
		$q = Executor::doit("select count(*) c from client where created_at >= date_format(curdate(),'%Y-%m-01')");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["clientes_nuevos_mes"] = (int)$r["c"]; }

		// ── Visitas al menú de cada tienda ─────────────────────────────
		// (la tabla la crea migracion_planes_ofertas.sql; si aún no existe
		//  el panel sigue funcionando en ceros)
		$out = array_merge($out, self::visitasGlobal());

		// ── Planes ─────────────────────────────────────────────────────
		$q = Executor::doit("select count(*) total,
			coalesce(sum(case when is_active=1 then 1 else 0 end),0) activos
			from plan");
		if($q[0] && ($r = $q[0]->fetch_assoc())){
			$out["planes_total"]    = (int)$r["total"];
			$out["planes_activos"]  = (int)$r["activos"];
		}
		foreach(PlanData::getAll() as $pl){
			if(PlanData::offerState($pl) === PlanData::OFF_ACT){ $out["planes_en_oferta"]++; }
		}

		// ── Ingresos recurrentes (MRR): plan de las tiendas activas ────
		// con su pago aprobado y periodo vigente (usa la tarifa normal).
		$q = Executor::doit("select coalesce(sum(p.price),0) s
			from tienda t
			join suscripcion s on s.id=(select max(s2.id) from suscripcion s2 where s2.tienda_id=t.id)
			join plan p on p.id=s.plan_id
			where t.estatus='activa' and s.zeno_status='COMPLETED'
			  and (s.periodo_fin is null or s.periodo_fin >= curdate())");
		if($q[0] && ($r = $q[0]->fetch_assoc())){ $out["mrr"] = (float)$r["s"]; }

		// Vencimientos (sobre la suscripción más reciente de cada tienda)
		foreach(self::tiendas() as $t){
			if(!$t["sus_id"]){ $out["sin_suscripcion"]++; continue; }
			$d = $t["dias"];
			if($d === null){ continue; }
			if($d < 0){ $out["vencidas"]++; }
			elseif($d <= $out["dias_aviso"]){ $out["por_vencer"]++; }
		}

		return $out;
	}

	// ── Visitas al menú (tiendas más visitadas) ──────────────────────
	// Ojo: estas consultas tocan `tienda_visita`, que crea
	// migracion_planes_ofertas.sql. Si aún no se aplicó, la consulta falla y
	// (por los mysqli de PHP 8.1+) lanzaría excepción — por eso se pasan por
	// tryDoit(): el panel tiene que seguir mostrándose en ceros.
	private static function tryDoit($sql){
		try { return Executor::doit($sql); }
		catch(Throwable $e){ return array(null, 0); }
	}

	// ¿Existe ya la tabla de visitas? (para avisar en el panel si falta migrar)
	public static function visitasDisponibles(){
		$q = self::tryDoit("select 1 from tienda_visita limit 1");
		return ($q[0] !== false && $q[0] !== null);
	}

	public static function visitasGlobal(){
		$out = array("visitas_total"=>0, "visitas_mes"=>0, "visitas_hoy"=>0);		$q = self::tryDoit("select
			coalesce(sum(visitas),0) total,
			coalesce(sum(case when fecha >= date_format(curdate(),'%Y-%m-01') then visitas else 0 end),0) mes,
			coalesce(sum(case when fecha = curdate() then visitas else 0 end),0) hoy
			from tienda_visita");
		if($q[0] && ($r = $q[0]->fetch_assoc())){
			$out["visitas_total"] = (int)$r["total"];
			$out["visitas_mes"]   = (int)$r["mes"];
			$out["visitas_hoy"]   = (int)$r["hoy"];
		}
		return $out;
	}

	// Ranking: visitas (ventana + total), pedidos y cobrado por tienda.
	public static function tiendasMasVisitadas($dias = 30, $limit = 10){
		$dias  = max(1, (int)$dias);
		$limit = max(1, (int)$limit);
		$desde = date("Y-m-d", strtotime("-".($dias - 1)." days"));
		$rows = array();
		$q = self::tryDoit("select t.id, t.nombre, t.subdomain, t.estatus,
			(select coalesce(sum(v.visitas),0) from tienda_visita v
			  where v.tienda_id=t.id and v.fecha>='$desde') visitas_dias,
			(select coalesce(sum(v.visitas),0) from tienda_visita v
			  where v.tienda_id=t.id) visitas_total,
			(select count(*) from buy b where b.tienda_id=t.id) pedidos,
			(select coalesce(sum(s.paid_amount),0) from suscripcion s
			  where s.tienda_id=t.id and s.zeno_status='COMPLETED') cobrado,
			(select count(*) from user u where u.tienda_id=t.id) usuarios
			from tienda t
			order by visitas_total desc, visitas_dias desc, t.id asc
			limit $limit");
		if($q[0]){
			while($r = $q[0]->fetch_assoc()){
				$rows[] = array(
					"id"           => (int)$r["id"],
					"nombre"       => $r["nombre"],
					"subdomain"    => $r["subdomain"],
					"estatus"      => $r["estatus"],
					"visitas_dias" => (int)$r["visitas_dias"],
					"visitas_total"=> (int)$r["visitas_total"],
					"pedidos"      => (int)$r["pedidos"],
					"cobrado"      => (float)$r["cobrado"],
					"usuarios"     => (int)$r["usuarios"],
				);
			}
		}
		return $rows;
	}

	// Serie de visitas por día (para la barrita del panel). Devuelve los $dias
	// últimos días, rellenando en 0 los que no tuvieron visitas.
	public static function visitasSerie($dias = 14){
		$dias = max(2, (int)$dias);
		$serie = array();
		for($i = $dias - 1; $i >= 0; $i--){
			$fecha = date("Y-m-d", strtotime("-".$i." days"));
			$serie[$fecha] = 0;
		}
		$desde = key($serie);
		$q = self::tryDoit("select fecha, sum(visitas) v from tienda_visita
			where fecha >= '$desde' group by fecha");
		if($q[0]){
			while($r = $q[0]->fetch_assoc()){
				$f = substr((string)$r["fecha"], 0, 10);
				if(array_key_exists($f, $serie)){ $serie[$f] = (int)$r["v"]; }
			}
		}
		return $serie; // [fecha => visitas]
	}

	// ── Listado de tiendas con su suscripción/plan vigente ─────────────
	// Devuelve un array de arrays asociativos (modelo de presentación).
	public static function tiendas(){
		$rows = array();
		$q = Executor::doit("select t.*,
			(select count(*) from product p where p.tienda_id=t.id) prods,
			(select count(*) from category c where c.tienda_id=t.id) cats,
			(select count(*) from buy b where b.tienda_id=t.id) pedidos
			from tienda t order by t.id desc");
		if(!$q[0]){ return $rows; }
		while($r = $q[0]->fetch_assoc()){
			$r["id"] = (int)$r["id"];
			$r["prods"] = (int)$r["prods"];
			$r["cats"] = (int)$r["cats"];
			$r["pedidos"] = (int)$r["pedidos"];
			$rows[$r["id"]] = $r;
		}

		// Suscripción vigente (la de id mayor por tienda) + plan
		$q = Executor::doit("select s.*, p.name plan_name, p.code plan_code, p.price plan_price
			from suscripcion s left join plan p on p.id=s.plan_id order by s.tienda_id asc, s.id desc");
		if($q[0]){
			while($r = $q[0]->fetch_assoc()){
				$tid = (int)$r["tienda_id"];
				if(!isset($rows[$tid]) || !empty($rows[$tid]["sus_id"])){ continue; } // ya tiene la más reciente
				$rows[$tid]["sus_id"] = (int)$r["id"];
				$rows[$tid]["sus_status"] = strtoupper(trim($r["zeno_status"]));
				$rows[$tid]["plan_id"] = (int)$r["plan_id"];
				$rows[$tid]["plan_name"] = $r["plan_name"] ? $r["plan_name"] : "—";
				$rows[$tid]["plan_code"] = $r["plan_code"] ? $r["plan_code"] : "";
				$rows[$tid]["plan_price"] = $r["plan_price"];
				$rows[$tid]["paid_amount"] = $r["paid_amount"];
				$rows[$tid]["periodo_fin"] = $r["periodo_fin"];
				$rows[$tid]["recordatorio_at"] = $r["recordatorio_at"];
				$rows[$tid]["dias"] = SuscripcionData::diasRestantes($r["periodo_fin"]);
			}
		}
		// Rellena los que no tienen suscripción (evita Undefined array key en la vista)
		foreach($rows as $id=>$t){
			if(!isset($t["sus_id"])){
				$rows[$id]["sus_id"] = 0;
				$rows[$id]["sus_status"] = "";
				$rows[$id]["plan_id"] = 0;
				$rows[$id]["plan_name"] = "—";
				$rows[$id]["plan_code"] = "";
				$rows[$id]["plan_price"] = null;
				$rows[$id]["paid_amount"] = null;
				$rows[$id]["periodo_fin"] = "";
				$rows[$id]["dias"] = null;
				$rows[$id]["recordatorio_at"] = "";
			}
		}
		return array_values($rows);
	}

	// ── Movimiento: suscripciones/pagos recientes ─────────────────────
	public static function suscripciones($limit = 20){
		$limit = max(1, (int)$limit);
		$out = array();
		$q = Executor::doit("select s.*, t.nombre tienda, t.subdomain, p.name plan_name
			from suscripcion s
			left join tienda t on t.id=s.tienda_id
			left join plan p on p.id=s.plan_id
			order by s.updated_at desc, s.id desc limit $limit");
		if($q[0]){ while($r = $q[0]->fetch_assoc()){ $out[] = $r; } }
		return $out;
	}

	// ── Movimiento: pedidos recientes de todas las tiendas ────────────
	public static function pedidos($limit = 15){
		$limit = max(1, (int)$limit);
		$out = array();
		$q = Executor::doit("select b.id, b.code, b.created_at, b.status_id,
			t.nombre tienda, t.subdomain, c.name cliente, st.name estado
			from buy b
			left join tienda t on t.id=b.tienda_id
			left join client c on c.id=b.client_id
			left join status st on st.id=b.status_id
			order by b.created_at desc, b.id desc limit $limit");
		if($q[0]){ while($r = $q[0]->fetch_assoc()){ $out[] = $r; } }
		return $out;
	}

	// ── Helpers ────────────────────────────────────────────────────────
	// "Don Pepe 2" -> "don-pepe-2"
	public static function slugify($text){
		$text = strtolower(trim((string)$text));
		$text = strtr($text, array(
			"á"=>"a","é"=>"e","í"=>"i","ó"=>"o","ú"=>"u","ü"=>"u","ñ"=>"n",
			"Á"=>"a","É"=>"e","Í"=>"i","Ó"=>"o","Ú"=>"u","Ü"=>"u","Ñ"=>"n",
		));
		$text = preg_replace('/[^a-z0-9]+/', "-", $text);
		$text = trim($text, "-");
		return substr($text, 0, 63);
	}

	public static function fechaFmt($fecha){
		if(!$fecha || $fecha === "0000-00-00" || $fecha === "0000-00-00 00:00:00"){ return "—"; }
		$ts = strtotime($fecha);
		return $ts ? date("d/m/Y", $ts) : $fecha;
	}

	public static function fechaHoraFmt($fecha){
		if(!$fecha || substr($fecha,0,10) === "0000-00-00"){ return "—"; }
		$ts = strtotime($fecha);
		return $ts ? date("d/m/Y H:i", $ts) : $fecha;
	}
}
