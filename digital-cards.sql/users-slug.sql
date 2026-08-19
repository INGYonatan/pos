ALTER TABLE `adm_usuarios`
  ADD COLUMN `slug` VARCHAR(255) NULL;

 -- Convertir nombre_completo a slug, tomando en cuenta que los nombres pueden contener caracteres especiales, acentos y espacios. Se reemplazarán los espacios por guiones y se eliminarán los acentos.
UPDATE `adm_usuarios`
  SET `slug` = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nombre_completo, ' ', '-'), 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'));

-- Agregar un índice único a la columna slug para evitar duplicados
ALTER TABLE `adm_usuarios`
  ADD UNIQUE KEY `slug` (`slug`);

-- Trigger para crear el slug automáticamente al insertar un nuevo usuario
DELIMITER $$
CREATE TRIGGER `before_insert_usuario_add_slug` BEFORE INSERT ON `adm_usuarios`
FOR EACH ROW
BEGIN
  SET NEW.slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(NEW.nombre_completo, ' ', '-'), 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'));
END$$
DELIMITER ;

-- Trigger para actualizar el slug automáticamente al actualizar el nombre_completo de un usuario
DELIMITER $$
CREATE TRIGGER `before_update_usuario_update_slug` BEFORE UPDATE ON `adm_usuarios`
FOR EACH ROW
BEGIN
  IF NEW.nombre_completo <> OLD.nombre_completo THEN
    SET NEW.slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(NEW.nombre_completo, ' ', '-'), 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'));
  END IF;
END$$
DELIMITER ;

ALTER TABLE `adm_usuarios`
  MODIFY COLUMN `slug` VARCHAR(255) NOT NULL,
  ADD COLUMN `mostrar_tarjeta` ENUM('si', 'no') NOT NULL DEFAULT 'no';