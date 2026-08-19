-- ============================================================
-- 997 - Rollback 003: quitar IEPS de cotizaciones
-- ============================================================
ALTER TABLE paal_cotizacion_productos
DROP COLUMN `ieps`,
DROP COLUMN `ieps_porcentaje`,
DROP COLUMN `aplica_ieps`;

ALTER TABLE paal_cotizaciones
DROP COLUMN `ieps`;