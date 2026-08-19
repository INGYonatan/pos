-- ============================================================
-- 004 - IEPS en compras
-- Agrega columna ieps a paal_compras y
-- columnas aplica_ieps / ieps_porcentaje / ieps a paal_compra_productos
-- ============================================================
-- Encabezado
ALTER TABLE paal_compras
ADD COLUMN `ieps` DECIMAL(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`;

UPDATE paal_compras
SET
  ieps = 0
WHERE
  ieps IS NULL;

-- Detalle
ALTER TABLE paal_compra_productos
ADD COLUMN `aplica_ieps` ENUM ('si', 'no') NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` DECIMAL(5, 2) NOT NULL DEFAULT '0.00' AFTER `aplica_ieps`,
ADD COLUMN `ieps` DECIMAL(22, 6) NOT NULL DEFAULT '0.000000' AFTER `iva`;

UPDATE paal_compra_productos
SET
  aplica_ieps = 'no',
  ieps_porcentaje = 0,
  ieps = 0
WHERE
  ieps IS NULL;