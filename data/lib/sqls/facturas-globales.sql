CREATE TABLE
  IF NOT EXISTS paal_venta_facturas (
    id_venta_factura INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_factura INT NOT NULL,
    tipo enum ("ingreso", "anticipo", "nota_credito") NOT NULL DEFAULT "ingreso"
  );

-- Queries para agregar todas las facturas de ventas existentes a esta nueva tabla, incluyendo los tipos de facutras en las 3 tablas paal_facturas, paal_facturas_anticipo_compra, paal_facturas_nota_credito, todos cuentan con id_venta e id_factura, solo hay que agregar el tipo de factura correspondiente a cada una de las tablas, hay que tener en cuenta que no todos tiene un id de venta relacionado, está vacío o nulo
INSERT INTO
  paal_venta_facturas (id_venta, id_factura, tipo)
SELECT
  id_venta,
  id_factura,
  "ingreso"
FROM
  paal_facturas
WHERE
  id_venta IS NOT NULL
  AND id_venta != 0;

INSERT INTO
  paal_venta_facturas (id_venta, id_factura, tipo)
SELECT
  id_venta,
  id_factura,
  "anticipo"
FROM
  paal_facturas_anticipo_compra
WHERE
  id_venta IS NOT NULL
  AND id_venta != 0;

INSERT INTO
  paal_venta_facturas (id_venta, id_factura, tipo)
SELECT
  id_venta,
  id_factura,
  "nota_credito"
FROM
  paal_facturas_nota_credito
WHERE
  id_venta IS NOT NULL
  AND id_venta != 0;