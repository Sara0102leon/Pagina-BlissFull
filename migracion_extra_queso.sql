-- Migración: precios y comportamiento del extra queso
-- Ejecutar:  mysql -uroot tacomenu < migracion_extra_queso.sql

-- 1) Extra queso de la Pizza Gigante: vale $5.00
UPDATE product_extra pe JOIN product p ON p.id=pe.product_id
SET pe.price=5.00 WHERE pe.group_key='g30' AND p.category_id=2$$

-- 2) El extra queso nunca es gratis (ni cuenta como ingrediente del cupo):
--    pasa a la sección EXTRAS (siempre se cobra su precio).
--    Gigante $5 | Familiar $4 | Pequeña $3
UPDATE product_extra SET is_ingredient=0 WHERE group_key='g30'$$