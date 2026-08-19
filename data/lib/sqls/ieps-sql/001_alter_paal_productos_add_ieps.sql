-- Agrega soporte de IEPS a productos (compatible con IVA actual)
ALTER TABLE `paal_productos`
ADD COLUMN `aplica_ieps` enum ('si', 'no') COLLATE utf8mb4_bin NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` decimal(6, 2) NOT NULL DEFAULT '8.00' AFTER `aplica_ieps`;

-- Garantiza compatibilidad con productos existentes
UPDATE `paal_productos`
SET
  `aplica_ieps` = IFNULL (`aplica_ieps`, 'no'),
  `ieps_porcentaje` = IFNULL (`ieps_porcentaje`, 8.00);