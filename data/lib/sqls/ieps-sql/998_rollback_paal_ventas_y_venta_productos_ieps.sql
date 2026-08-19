-- Rollback IEPS en ventas POS (encabezado + detalle)
ALTER TABLE `paal_venta_productos`
DROP COLUMN `ieps`,
DROP COLUMN `ieps_porcentaje`,
DROP COLUMN `aplica_ieps`;

ALTER TABLE `paal_ventas`
DROP COLUMN `ieps`;