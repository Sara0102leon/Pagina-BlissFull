-- ============================================================
-- Migración: Integración Ventas <-> Chatwoot CRM
-- Ejecutar:  mysql -uroot tacomenu < migracion_chatwoot.sql
-- ============================================================

-- ------------------------------------------------------------
-- 1) buy: vínculo con la conversación/contacto de Chatwoot
--    + columna scheduled_at faltante (referenciada por BuyData::add)
--    (exists-check para que sea re-ejecutable)
-- ------------------------------------------------------------
SET @sched := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA='tacomenu' AND TABLE_NAME='buy' AND COLUMN_NAME='scheduled_at');
SET @sql := IF(@sched=0,
  'ALTER TABLE buy ADD COLUMN scheduled_at datetime NULL AFTER note',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @cwci := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA='tacomenu' AND TABLE_NAME='buy' AND COLUMN_NAME='chatwoot_conversation_id');
SET @sql := IF(@cwci=0,
  'ALTER TABLE buy ADD COLUMN chatwoot_conversation_id bigint NULL AFTER scheduled_at',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @cwct := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA='tacomenu' AND TABLE_NAME='buy' AND COLUMN_NAME='chatwoot_contact_id');
SET @sql := IF(@cwct=0,
  'ALTER TABLE buy ADD COLUMN chatwoot_contact_id bigint NULL AFTER chatwoot_conversation_id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 2) sede: conversación "grupo" (equipo de delivery) por sede
-- ------------------------------------------------------------
SET @cwg := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA='tacomenu' AND TABLE_NAME='sede' AND COLUMN_NAME='chatwoot_group_conversation_id');
SET @sql := IF(@cwg=0,
  'ALTER TABLE sede ADD COLUMN chatwoot_group_conversation_id bigint NULL AFTER maps',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 3) Configuración de Chatwoot (se autolista en Settings via general_*)
-- ------------------------------------------------------------
INSERT IGNORE INTO configuration (name,label,kind,val) VALUES
("general_chatwoot_base_url","Chatwoot URL Base",1,"https://chat.alianzablissful.com"),
("general_chatwoot_account_id","Chatwoot Account ID",1,"1"),
("general_chatwoot_access_token","Chatwoot Access Token",1,""),
("general_chatwoot_webhook_secret","Chatwoot Webhook Secret",1,""),
("general_chatwoot_app_token","Chatwoot API Token (Dashboard App)",1,"");
