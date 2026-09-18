-- Migración: ubicación del cliente + ruta Google Maps + seguimiento
-- Ejecutar:  mysql -uroot tacomenu < migracion_ubicacion.sql

-- 1) Coordenadas de sucursales (para la ruta A->B y la distancia)
ALTER TABLE sede
  ADD COLUMN lat DECIMAL(10,7) NULL DEFAULT NULL AFTER image,
  ADD COLUMN lng DECIMAL(10,7) NULL DEFAULT NULL AFTER lat;

-- Plaza Sevilla: extraída del enlace de Maps guardado (@10.0244691,-69.2549267)
UPDATE sede SET lat=10.0244691, lng=-69.2549267 WHERE id=1;
-- Cabudare y Metrópolis: geocodificadas con Nominatim (editables en el admin)
UPDATE sede SET lat=10.0314372, lng=-69.2555891 WHERE id=2;
UPDATE sede SET lat=10.0626940, lng=-69.3656443 WHERE id=4;

-- 2) Ubicación y distancia del cliente por pedido
ALTER TABLE buy
  ADD COLUMN lat DECIMAL(10,7) NULL DEFAULT NULL,
  ADD COLUMN lng DECIMAL(10,7) NULL DEFAULT NULL,
  ADD COLUMN maps VARCHAR(500) NULL DEFAULT NULL,
  ADD COLUMN distance_km DECIMAL(8,2) NULL DEFAULT NULL;