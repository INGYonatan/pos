-- ============================================================
-- 995 - Rollback 005: quitar IEPS de órdenes de compra
-- ============================================================
ALTER TABLE paal_orden_compra_productos
DROP COLUMN `ieps`,
DROP COLUMN `ieps_porcentaje`,
DROP COLUMN `aplica_ieps`;

ALTER TABLE paal_ordenes_compra
DROP COLUMN `ieps`;