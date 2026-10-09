-- =====================================================================
-- Recuperación de esquema (aditiva, idempotente) — octubre 2026
-- La BD viva tacomenu es más vieja que lo que el código de main espera.
-- Estas tablas/columnas las usa el código en producción (per-sede, bebidas,
-- horario semanal, notificaciones, chatwoot, geo) y faltaban -> fatales.
-- NADA se borra; solo se crea lo que no existe.
-- =====================================================================

-- 1) product_sede: productos disponibles en varias sedes (pívote)
CREATE TABLE IF NOT EXISTS product_sede (
  product_id int not null,
  sede_id int not null,
  primary key (product_id, sede_id),
  key idx_ps_sede (sede_id)
) engine=InnoDB default charset=utf8mb4;

-- 2) bebida: bebidas por sabor/medida (sección "bebidas")
CREATE TABLE IF NOT EXISTS bebida (
  id int not null auto_increment primary key,
  sabor varchar(120) not null,
  medida varchar(80) not null default '',
  sabor_options varchar(500) null,
  precio decimal(10,2) not null default 0.00,
  es_gratis tinyint(1) not null default 0,
  is_active tinyint not null default 1,
  unique key uq_bebida (sabor, medida)
) engine=InnoDB default charset=utf8mb4;

-- 3) sede_horario: horario semanal por sede (‹Hoy: …›, cerrado)
CREATE TABLE IF NOT EXISTS sede_horario (
  id int not null auto_increment primary key,
  sede_id int not null,
  dia varchar(20) not null,
  hora_open varchar(5) default null,
  hora_close varchar(5) default null,
  unique key uq_sede_dia (sede_id, dia)
) engine=InnoDB default charset=utf8mb4;

-- 4) buy: columnas nuevas (notificaciones / chatwoot / geo)
ALTER TABLE buy ADD COLUMN IF NOT EXISTS notified varchar(100) NOT NULL DEFAULT '';
ALTER TABLE buy ADD COLUMN IF NOT EXISTS chatwoot_conversation_id bigint NULL;
ALTER TABLE buy ADD COLUMN IF NOT EXISTS chatwoot_contact_id bigint NULL;
ALTER TABLE buy ADD COLUMN IF NOT EXISTS lat decimal(10,7) NULL;
ALTER TABLE buy ADD COLUMN IF NOT EXISTS lng decimal(10,7) NULL;
ALTER TABLE buy ADD COLUMN IF NOT EXISTS maps varchar(500) NULL;
ALTER TABLE buy ADD COLUMN IF NOT EXISTS distance_km decimal(8,2) NULL;

-- 5) config bebida_base (seed del schema original)
INSERT IGNORE INTO configuration (name, label, kind, val, cfg_id) VALUES
('bebida_base', 'Valor base del refresco gratis (USD)', 0, '1', 1);