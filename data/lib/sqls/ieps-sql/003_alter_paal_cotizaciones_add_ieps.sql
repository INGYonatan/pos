-- ============================================================
-- 003 - IEPS en cotizaciones
-- Agrega columna ieps a paal_cotizaciones y
-- columnas aplica_ieps / ieps_porcentaje / ieps a paal_cotizacion_productos
-- ============================================================
-- Encabezado
ALTER TABLE paal_cotizaciones
ADD COLUMN `ieps` DECIMAL(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`;

UPDATE paal_cotizaciones
SET
  ieps = 0
WHERE
  ieps IS NULL;

-- Detalle
ALTER TABLE paal_cotizacion_productos
ADD COLUMN `aplica_ieps` ENUM ('si', 'no') NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` DECIMAL(5, 2) NOT NULL DEFAULT '0.00' AFTER `aplica_ieps`,
ADD COLUMN `ieps` DECIMAL(22, 6) NOT NULL DEFAULT '0.000000' AFTER `iva`;

UPDATE paal_cotizacion_productos
SET
  aplica_ieps = 'no',
  ieps_porcentaje = 0,
  ieps = 0
WHERE
  ieps IS NULL;