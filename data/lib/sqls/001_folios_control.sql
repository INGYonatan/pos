-- Tabla para control atomico de folios
-- Evita duplicados en concurrencia (reemplaza SELECT MAX(folio) + 1)
CREATE TABLE
  IF NOT EXISTS folios_control (
    id INT NOT NULL AUTO_INCREMENT,
    tipo_factura VARCHAR(50) NOT NULL,
    serie VARCHAR(15) COLLATE utf8mb4_bin NOT NULL,
    ultimo_folio INT NOT NULL DEFAULT 0,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_tipo_serie (tipo_factura, serie)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_bin;

-- Inicializacion de folios actuales por tabla
INSERT IGNORE INTO folios_control (tipo_factura, serie, ultimo_folio)
SELECT
  'ingreso',
  serie,
  COALESCE(MAX(folio), 0)
FROM
  paal_facturas
GROUP BY
  serie;

INSERT IGNORE INTO folios_control (tipo_factura, serie, ultimo_folio)
SELECT
  'pago',
  serie,
  COALESCE(MAX(folio), 0)
FROM
  paal_facturas_p
GROUP BY
  serie;

INSERT IGNORE INTO folios_control (tipo_factura, serie, ultimo_folio)
SELECT
  'pago_pago',
  serie,
  COALESCE(MAX(folio), 0)
FROM
  paal_facturas_p_pagos
GROUP BY
  serie;

INSERT IGNORE INTO folios_control (tipo_factura, serie, ultimo_folio)
SELECT
  'anticipo-de-compra',
  serie,
  COALESCE(MAX(folio), 0)
FROM
  paal_facturas_anticipo_compra
GROUP BY
  serie;

INSERT IGNORE INTO folios_control (tipo_factura, serie, ultimo_folio)
SELECT
  'nota-de-credito',
  serie,
  COALESCE(MAX(folio), 0)
FROM
  paal_facturas_nota_credito
GROUP BY
  serie;

INSERT IGNORE INTO folios_control (tipo_factura, serie, ultimo_folio)
SELECT
  'traslado',
  serie,
  COALESCE(MAX(folio), 0)
FROM
  paal_facturas_traslado
GROUP BY
  serie;