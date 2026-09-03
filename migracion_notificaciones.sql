-- Migración: avisos de pedidos programados en la campana de notificaciones
-- Añade la columna `notified` a la tabla `buy` para registrar los hitos ya notificados
-- (created, 24h, 6h, 1h, 15min) y evitar repetir la notificación en cada poll.
ALTER TABLE buy ADD COLUMN notified VARCHAR(100) NOT NULL DEFAULT '' AFTER scheduled_at;
