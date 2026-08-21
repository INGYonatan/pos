SET
    SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

SET
    NAMES utf8mb4;

-- ============================================================
-- 1. TABLAS QUE NO EXISTEN EN LA BD DESTINO
-- ============================================================
-- ------------------------------------------------------------
-- folios_control
-- ------------------------------------------------------------
CREATE TABLE
    IF NOT EXISTS `folios_control` (
        `id` int NOT NULL,
        `tipo_factura` varchar(50) COLLATE utf8mb4_bin NOT NULL,
        `serie` varchar(15) CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `ultimo_folio` int NOT NULL DEFAULT '0',
            `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_tipo_serie` (`tipo_factura`, `serie`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin;

-- ------------------------------------------------------------
-- paal__usuarios_catalogos
-- ------------------------------------------------------------
CREATE TABLE
    IF NOT EXISTS `paal__usuarios_catalogos` (
        `id_catalogo` int NOT NULL,
        `id_usuario` int NOT NULL,
        `nombre_catalogo` varchar(255) CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `descripcion` text CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_bin,
            `nombre_archivo` varchar(255) CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `archivo_ruta` varchar(500) CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `estado` enum ('activo', 'inactivo') CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `fecha_carga` datetime DEFAULT NULL,
            `uid` varchar(255) CHARACTER
        SET
            utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin;

-- ------------------------------------------------------------
-- paal_solicitud_transferencias
-- ------------------------------------------------------------
CREATE TABLE
    IF NOT EXISTS `paal_solicitud_transferencias` (
        `id_solicitud_transferencia` int NOT NULL AUTO_INCREMENT,
        `id_usuario` int NOT NULL,
        `id_sucursal_origen` int NOT NULL,
        `id_sucursal_destino` int NOT NULL,
        `folio` varchar(20) COLLATE utf8mb4_bin NOT NULL,
        `notas` text COLLATE utf8mb4_bin,
        `status` enum (
            'pendiente',
            'aprobado',
            'completado',
            'rechazado',
            'cancelado'
        ) COLLATE utf8mb4_bin NOT NULL DEFAULT 'pendiente',
        `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_solicitud_transferencia`),
        KEY `id_usuario` (`id_usuario`),
        KEY `id_sucursal_origen` (`id_sucursal_origen`),
        KEY `id_sucursal_destino` (`id_sucursal_destino`),
        CONSTRAINT `paal_solicitud_transferencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `adm_usuarios` (`id_usuario`),
        CONSTRAINT `paal_solicitud_transferencias_ibfk_2` FOREIGN KEY (`id_sucursal_origen`) REFERENCES `paal_sucursales` (`id_sucursal`),
        CONSTRAINT `paal_solicitud_transferencias_ibfk_3` FOREIGN KEY (`id_sucursal_destino`) REFERENCES `paal_sucursales` (`id_sucursal`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin;

-- ------------------------------------------------------------
-- paal_solicitud_transferencia_productos
-- ------------------------------------------------------------
CREATE TABLE
    IF NOT EXISTS `paal_solicitud_transferencia_productos` (
        `id_solicitud_transferencia_producto` int NOT NULL AUTO_INCREMENT,
        `id_solicitud_transferencia` int NOT NULL,
        `id_producto` int NOT NULL,
        `cantidad_solicitada` int NOT NULL,
        `cantidad_atendida` int NOT NULL DEFAULT '0',
        PRIMARY KEY (`id_solicitud_transferencia_producto`),
        KEY `id_solicitud_transferencia` (`id_solicitud_transferencia`),
        KEY `id_producto` (`id_producto`),
        CONSTRAINT `paal_solicitud_transferencia_productos_ibfk_1` FOREIGN KEY (`id_solicitud_transferencia`) REFERENCES `paal_solicitud_transferencias` (`id_solicitud_transferencia`),
        CONSTRAINT `paal_solicitud_transferencia_productos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `paal_productos` (`id_producto`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin;

-- ------------------------------------------------------------
-- paal_usuario_archivos
-- ------------------------------------------------------------
CREATE TABLE
    IF NOT EXISTS `paal_usuario_archivos` (
        `id_usuario_archivo` int NOT NULL AUTO_INCREMENT,
        `id_usuario` int NOT NULL,
        `nombre` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
        `slug` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
        `tipo` varchar(255) COLLATE utf8mb4_bin DEFAULT 'pdf',
        `status` enum ('activo', 'eliminado') COLLATE utf8mb4_bin DEFAULT 'activo',
        PRIMARY KEY (`id_usuario_archivo`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin;

-- ------------------------------------------------------------
-- paal_venta_facturas
-- ------------------------------------------------------------
CREATE TABLE
    IF NOT EXISTS `paal_venta_facturas` (
        `id_venta_factura` int NOT NULL AUTO_INCREMENT,
        `id_venta` int NOT NULL,
        `id_factura` int NOT NULL,
        `tipo` enum ('ingreso', 'anticipo', 'nota_credito') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ingreso',
        PRIMARY KEY (`id_venta_factura`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin;

-- ------------------------------------------------------------
-- paal_venta_pago_facturas
-- ------------------------------------------------------------
CREATE TABLE
    IF NOT EXISTS `paal_venta_pago_facturas` (
        `id_venta_pago_factura` int NOT NULL AUTO_INCREMENT,
        `id_venta_pago` int NOT NULL,
        `id_factura` int NOT NULL,
        PRIMARY KEY (`id_venta_pago_factura`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin COMMENT = 'Tambla relacional para las facturas de pago y los pagos de las ventas';

-- ------------------------------------------------------------
-- paal_venta_producto_numeros_serie
-- ------------------------------------------------------------
CREATE TABLE
    IF NOT EXISTS `paal_venta_producto_numeros_serie` (
        `id_venta_producto_numero_serie` int NOT NULL AUTO_INCREMENT,
        `id_venta_producto` int NOT NULL,
        `id_venta` int NOT NULL,
        `numero_serie` text COLLATE utf8mb4_bin NOT NULL,
        PRIMARY KEY (`id_venta_producto_numero_serie`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin;

-- ============================================================
-- 2. COLUMNAS FALTANTES
-- ============================================================
-- ------------------------------------------------------------
-- adm_usuarios
-- ------------------------------------------------------------
ALTER TABLE `adm_usuarios`
ADD COLUMN `slug` varchar(255) COLLATE utf8mb4_bin NOT NULL AFTER `status`,
ADD COLUMN `mostrar_tarjeta` enum ('si', 'no') COLLATE utf8mb4_bin NOT NULL DEFAULT 'no' AFTER `slug`,
ADD COLUMN `avatar` text COLLATE utf8mb4_bin AFTER `mostrar_tarjeta`;

-- ------------------------------------------------------------
-- paal_compra_productos
-- ------------------------------------------------------------
ALTER TABLE `paal_compra_productos`
ADD COLUMN `aplica_ieps` enum ('si', 'no') COLLATE utf8mb4_bin NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` decimal(5, 2) NOT NULL DEFAULT '0.00' AFTER `aplica_ieps`,
ADD COLUMN `ieps` decimal(22, 6) NOT NULL DEFAULT '0.000000' AFTER `iva`;

-- ------------------------------------------------------------
-- paal_compras
-- ------------------------------------------------------------
ALTER TABLE `paal_compras`
ADD COLUMN `ieps` decimal(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`;

-- ------------------------------------------------------------
-- paal_cotizacion_productos
-- ------------------------------------------------------------
ALTER TABLE `paal_cotizacion_productos`
ADD COLUMN `aplica_ieps` enum ('si', 'no') COLLATE utf8mb4_bin NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` decimal(5, 2) NOT NULL DEFAULT '0.00' AFTER `aplica_ieps`,
ADD COLUMN `ieps` decimal(22, 6) NOT NULL DEFAULT '0.000000' AFTER `iva`,
ADD COLUMN `comentarios` text COLLATE utf8mb4_bin AFTER `cancelado`;

-- ------------------------------------------------------------
-- paal_cotizaciones
-- ------------------------------------------------------------
ALTER TABLE `paal_cotizaciones`
ADD COLUMN `ieps` decimal(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`;

-- ------------------------------------------------------------
-- paal_inventario_transferencias
-- ------------------------------------------------------------
ALTER TABLE `paal_inventario_transferencias`
ADD COLUMN `id_solicitud_transferencia` int DEFAULT NULL AFTER `facturado`;

-- ------------------------------------------------------------
-- paal_orden_compra_productos
-- ------------------------------------------------------------
ALTER TABLE `paal_orden_compra_productos`
ADD COLUMN `aplica_ieps` enum ('si', 'no') COLLATE utf8mb4_bin NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` decimal(5, 2) NOT NULL DEFAULT '0.00' AFTER `aplica_ieps`,
ADD COLUMN `ieps` decimal(22, 6) NOT NULL DEFAULT '0.000000' AFTER `iva`;

-- ------------------------------------------------------------
-- paal_ordenes_compra
-- ------------------------------------------------------------
ALTER TABLE `paal_ordenes_compra`
ADD COLUMN `ieps` decimal(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`;

-- ------------------------------------------------------------
-- paal_productos
-- ------------------------------------------------------------
ALTER TABLE `paal_productos`
ADD COLUMN `aplica_ieps` enum ('si', 'no') CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` decimal(6, 2) NOT NULL DEFAULT '8.00' AFTER `aplica_ieps`;

-- ------------------------------------------------------------
-- paal_sucursales
-- ------------------------------------------------------------
ALTER TABLE `paal_sucursales`
ADD COLUMN `cp` varchar(250) COLLATE utf8mb4_bin NOT NULL AFTER `rfc`,
ADD COLUMN `nombre_comercial` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL AFTER `cp`,
ADD COLUMN `logo` text COLLATE utf8mb4_bin AFTER `nombre_comercial`,
ADD COLUMN `display_orden` int NOT NULL DEFAULT '1' AFTER `logo`;

-- ------------------------------------------------------------
-- paal_venta_productos
-- ------------------------------------------------------------
ALTER TABLE `paal_venta_productos`
ADD COLUMN `aplica_ieps` enum ('si', 'no') CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'no' AFTER `aplica_iva`,
ADD COLUMN `ieps_porcentaje` decimal(6, 2) NOT NULL DEFAULT '0.00' AFTER `aplica_ieps`,
ADD COLUMN `ieps` decimal(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`,
ADD COLUMN `comentarios` text COLLATE utf8mb4_bin AFTER `cancelado`;

-- ------------------------------------------------------------
-- paal_ventas
-- ------------------------------------------------------------
ALTER TABLE `paal_ventas`
ADD COLUMN `ieps` decimal(22, 2) NOT NULL DEFAULT '0.00' AFTER `iva`;

-- ============================================================
-- 3. CORRECCIONES DE PRECISIÓN NUMÉRICA
-- ============================================================
--
-- Se usa DECIMAL(22,6), tal como acordamos.
-- Estos cambios amplían precisión decimal y no eliminan
-- información existente.
-- ============================================================
ALTER TABLE `adm_menu` MODIFY COLUMN `orden` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_categoria_familias` MODIFY COLUMN `limite_descuento` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cantidad_mayoreo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_mayoreo` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_clientes` MODIFY COLUMN `limite_credito` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_compra_productos` MODIFY COLUMN `cantidad` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_original` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_costo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `iva` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `descuento` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_compras` MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `iva` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_cortes_caja` MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_cotizacion_productos` MODIFY COLUMN `precio_venta` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cantidad_mayoreo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_mayoreo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_venta_base` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cantidad` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `iva` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `descuento` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_neto` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_cotizaciones` MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `iva` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `redondeo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_facturas` MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cfdi_relacionado` longtext CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_bin;

ALTER TABLE `paal_facturas_anticipo_compra` MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cfdi_relacionado` longtext CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_bin;

ALTER TABLE `paal_facturas_nota_credito` MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cfdi_relacionado` longtext CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_bin;

ALTER TABLE `paal_facturas_p_pagos` MODIFY COLUMN `monto` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `importe_saldo_anterior` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `importe_pagado` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `importe_saldo_insoluto` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_facturas_traslado` MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_gastos` MODIFY COLUMN `monto` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_inventario` MODIFY COLUMN `stock` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_inventario_ajuste_productos` MODIFY COLUMN `cantidad` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_inventario_transferencia_productos` MODIFY COLUMN `cantidad` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_kardex` MODIFY COLUMN `cantidad` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `existencia` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_orden_compra_productos` MODIFY COLUMN `cantidad` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_original` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_costo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `iva` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `descuento` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_ordenes_compra` MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `iva` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

-- ------------------------------------------------------------
-- paal_productos
-- Aquí estaban los DOUBLE.
-- ------------------------------------------------------------
ALTER TABLE `paal_productos` MODIFY COLUMN `contenido` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_costo_original` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_costo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_venta_original` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_venta` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_venta2_original` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_venta2` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_venta3_original` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_venta3` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cantidad_mayoreo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_mayoreo_original` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_mayoreo` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_venta_pagos` MODIFY COLUMN `efectivo_monto` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cheque_monto` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `transferencia_monto` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `tarjeta_credito_monto` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `tarjeta_debito_monto` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `monto_total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_venta_productos` MODIFY COLUMN `precio_venta` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cantidad_mayoreo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_mayoreo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_venta_base` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cantidad` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `iva` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `descuento` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `precio_neto` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL;

ALTER TABLE `paal_ventas` MODIFY COLUMN `subtotal` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `iva` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `redondeo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `total` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `pago_con` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `efectivo` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cheque` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `transferencia` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `tarjeta_credito` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `tarjeta_debito` decimal(22, 6) DEFAULT NULL,
MODIFY COLUMN `cambio` decimal(22, 6) DEFAULT NULL;

-- ------------------------------------------------------------
-- En la BD destino era TEXT, en la BD completa es MEDIUMTEXT.
-- Esto amplía capacidad, no la reduce.
-- ------------------------------------------------------------
ALTER TABLE `regimen_fiscal` MODIFY COLUMN `regimen_fiscal` mediumtext COLLATE utf8mb4_bin;

-- ------------------------------------------------------------
-- paal_productos
-- Se agrega el año de fabricación
-- ------------------------------------------------------------
ALTER TABLE `paal_productos`
ADD COLUMN `anio_fabricacion` INT (11) NULL DEFAULT NULL;

-- ============================================================
-- FIN DE LA SINCRONIZACIÓN DE ESTRUCTURA
-- ============================================================