-- Rollback IEPS en productos
ALTER TABLE `paal_productos`
DROP COLUMN `ieps_porcentaje`,
DROP COLUMN `aplica_ieps`;