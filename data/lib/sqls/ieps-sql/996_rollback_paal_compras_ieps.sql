-- ============================================================
-- 996 - Rollback 004: quitar IEPS de compras
-- ============================================================
ALTER TABLE paal_compra_productos
DROP COLUMN `ieps`,
DROP COLUMN `ieps_porcentaje`,
DROP COLUMN `aplica_ieps`;

ALTER TABLE paal_compras
DROP COLUMN `ieps`;