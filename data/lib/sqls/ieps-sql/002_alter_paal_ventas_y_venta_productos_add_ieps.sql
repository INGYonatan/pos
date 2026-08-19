-- Agrega soporte de IEPS en ventas POS (encabezado + detalle)
ALTER TABLE `paal_ventas`
ADD COLUMN `ieps` decimal(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`;

ALTER TABLE `paal_venta_productos`
ADD COLUMN `aplica_ieps` enum ('si', 'no') COLLATE utf8mb4_bin NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` decimal(6, 2) NOT NULL DEFAULT '0.00' AFTER `aplica_ieps`,
ADD COLUMN `ieps` decimal(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`;

-- Compatibilidad con datos existentes
UPDATE `paal_ventas`
SET
  `ieps` = IFNULL (`ieps`, 0.00);

UPDATE `paal_venta_productos`
SET
  `aplica_ieps` = IFNULL (`aplica_ieps`, 'no'),
  `ieps_porcentaje` = IFNULL (`ieps_porcentaje`, 0.00),
  `ieps` = IFNULL (`ieps`, 0.00);