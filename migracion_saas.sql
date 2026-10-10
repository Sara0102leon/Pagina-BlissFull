-- =====================================================================
-- SaaS A1: registro + planes + suscripciones + pago Zenobank
-- Migración ADITIVA e idempotente — octubre 2026
-- Nada se borra; solo se crea lo que no existe y se hace seed.
-- =====================================================================

-- 1) plan: catálogo de planes vendidos en la landing
CREATE TABLE IF NOT EXISTS plan (
  id int not null auto_increment primary key,
  code varchar(40) not null,
  name varchar(120) not null,
  price decimal(10,2) not null default 0.00,
  currency char(3) not null default 'USD',
  max_products int null,
  max_sedes int not null default 1,
  kds tinyint(1) not null default 0,
  drag_drop tinyint(1) not null default 0,
  theme tinyint(1) not null default 0,
  reports tinyint(1) not null default 0,
  priority_support tinyint(1) not null default 0,
  position int not null default 0,
  is_active tinyint(1) not null default 1,
  unique key uq_plan_code (code)
) engine=InnoDB default charset=utf8mb4;

INSERT IGNORE INTO plan (code,name,price,currency,max_products,max_sedes,kds,drag_drop,theme,reports,priority_support,position) VALUES
('emprendedor','Emprendedor',9.00,'USD',30,1,0,0,0,0,0,1),
('restaurante','Restaurante',19.00,'USD',NULL,1,1,1,1,0,0,2),
('cadena','Cadena',39.00,'USD',NULL,5,1,1,1,1,1,3);

-- 2) tienda: cada cliente = una tienda (tenant) con su subdominio
CREATE TABLE IF NOT EXISTS tienda (
  id int not null auto_increment primary key,
  subdomain varchar(100) not null,
  nombre varchar(150) not null,
  descripcion text null,
  email varchar(255) null,
  whatsapp varchar(30) null,
  estatus varchar(20) not null default 'pendiente',
  zeno_mode varchar(10) not null default 'demo',
  created_at datetime not null default current_timestamp,
  unique key uq_tienda_subdomain (subdomain),
  key idx_tienda_estatus (estatus)
) engine=InnoDB default charset=utf8mb4;

-- 3) suscripcion: vínculo tienda <-> plan con el estado del checkout Zenobank
CREATE TABLE IF NOT EXISTS suscripcion (
  id int not null auto_increment primary key,
  tienda_id int not null,
  plan_id int not null,
  zeno_checkout_id varchar(64) null,
  zeno_status varchar(20) not null default 'OPEN',
  paid_amount decimal(10,2) null,
  periodo_inicio date null,
  periodo_fin date null,
  created_at datetime not null default current_timestamp,
  updated_at datetime null,
  unique key uq_sus_zeno (zeno_checkout_id),
  key idx_sus_tienda (tienda_id),
  key idx_sus_plan (plan_id)
) engine=InnoDB default charset=utf8mb4;

-- 4) user: dueño de la tienda (multi-tenant + roles A2/A3)
ALTER TABLE user ADD COLUMN IF NOT EXISTS tienda_id int NULL;
ALTER TABLE user ADD COLUMN IF NOT EXISTS role varchar(20) NOT NULL DEFAULT 'dueno';

-- 5) claves de configuración para el pago Zenobank (editar en admin o SQL)
INSERT IGNORE INTO configuration (name,label,kind,val) VALUES
('zeno_api_key','Zenobank API Key (sk_...)',1,''),
('zeno_webhook_secret','Zenobank Webhook Secret (whsec_...)',1,''),
('zeno_mode','Modo pago Zenobank (demo|live)',1,'demo'),
('zeno_price_currency','Moneda de cobro (ISO 4217)',1,'USD'),
('zeno_currency_symbol','Símbolo de moneda',1,'$'),
('saas_base_domain','Dominio raíz de tiendas',1,'sazon.app'),
('saas_landing_url','URL de la landing (para redirigir tras pagar)',1,'');