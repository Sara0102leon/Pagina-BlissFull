-- ============================================================================
-- migracion_plataforma.sql — Fase 6: super-admin de plataforma + recordatorio
-- de renovación (oct 2026)
--
-- Aditiva e idempotente. Se puede re-ejecutar sin perder datos:
--   * columnas  -> ADD COLUMN IF NOT EXISTS
--   * claves    -> INSERT ... ON DUPLICATE KEY UPDATE (NO pisa `val`)
--   * usuario   -> INSERT ... WHERE NOT EXISTS
--
-- Aplicar:  mariadb -u root tacomenu < migracion_plataforma.sql
-- ============================================================================

-- 1) Último aviso de renovación enviado a la tienda (marca del super-admin)
ALTER TABLE suscripcion ADD COLUMN IF NOT EXISTS recordatorio_at DATETIME NULL;

-- 2) Clave de configuración (base cfg_id=1): días de antelación del aviso
INSERT INTO configuration (name,label,kind,val,cfg_id) VALUES
  ('saas_dias_aviso','Días de aviso antes del vencimiento',2,'7',1)
ON DUPLICATE KEY UPDATE label=VALUES(label), kind=VALUES(kind);

-- 3) Usuario super-admin de la plataforma (role='superadmin').
--    Acceso total + panel "Plataforma" (ver/ gestionar todas las tiendas).
--    Credenciales iniciales:  superadmin / superadmin   (CAMBIAR al desplegar)
INSERT INTO user (name,username,email,password,is_active,is_admin,tienda_id,role,created_at)
SELECT 'Super Admin','superadmin','superadmin@sazon.local',SHA1(MD5('superadmin')),1,0,1,'superadmin',NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM user WHERE role='superadmin' OR username='superadmin');
