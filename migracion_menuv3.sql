-- Migración Menú (lote v3)
-- Ejecutar:  mysql -uroot tacomenu < migracion_menuv3.sql

DELIMITER $$

-- 1) Tabla pivote: bebida agotada por sede
CREATE TABLE IF NOT EXISTS bebida_sede (
  bebida_id INT NOT NULL,
  sede_id INT NOT NULL,
  agotado TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (bebida_id, sede_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4$$

-- 2) Precios de extras por tamaño
--    Pequeña: ingredientes $1, extra queso $3
--    Familiar: ingredientes $2, extra queso $4
--    Gigante: ingredientes $3, extra queso $3 (sin cambio)
UPDATE product_extra pe JOIN product p ON p.id=pe.product_id SET pe.price=3.00 WHERE p.category_id=4 AND pe.group_key='g30'$$
UPDATE product_extra pe JOIN product p ON p.id=pe.product_id SET pe.price=4.00 WHERE p.category_id=3 AND pe.group_key='g30'$$
UPDATE product_extra pe JOIN product p ON p.id=pe.product_id SET pe.price=2.00 WHERE p.category_id=3 AND pe.is_ingredient=1 AND pe.group_key!='g30'$$
UPDATE product_extra pe JOIN product p ON p.id=pe.product_id SET pe.price=1.00 WHERE p.category_id=4 AND pe.is_ingredient=1 AND pe.group_key!='g30'$$

-- 3) Golden: un refresco por sabor (se desactiva el combinado)
UPDATE bebida SET is_active=0 WHERE id=4$$
INSERT IGNORE INTO bebida (sabor,medida,sabor_options,precio,es_gratis,is_active) VALUES
('Golden Uva','1.5 Litros','',1.00,1,1),
('Golden Piña','1.5 Litros','',1.00,1,1),
('Golden Kolita','1.5 Litros','',1.00,1,1),
('Golden Manzanita','1.5 Litros','',1.00,1,1),
('Golden Naranja','1.5 Litros','',1.00,1,1)$$

-- 4) Pastas disponibles en todas las sedes
UPDATE product SET sede_id=NULL WHERE id IN (40,42,43)$$

-- 5) Configuraciones del CRM (mensajes editables + URL del front)
INSERT INTO configuration(name,label,kind,val) VALUE ("general_chatwoot_front_url","Chatwoot URL (Ventana Chat)",1,"https://chat.alianzablissful.com/app")
  ON DUPLICATE KEY UPDATE val=VALUES(val)$$
INSERT INTO configuration(name,label,kind,val) VALUE ("general_chatwoot_msg_enviado","Mensaje al cliente (Enviado)",1,"Tu pedido #CODIGO ha sido enviado. ¡Gracias por tu compra!")
  ON DUPLICATE KEY UPDATE val=VALUES(val)$$
INSERT INTO configuration(name,label,kind,val) VALUE ("general_chatwoot_msg_cancelado","Mensaje al cliente (Cancelado)",1,"Tu pedido #CODIGO ha sido cancelado.")
  ON DUPLICATE KEY UPDATE val=VALUES(val)$$

DELIMITER ;