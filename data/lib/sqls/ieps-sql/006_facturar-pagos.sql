CREATE TABLE `paal_venta_pago_facturas` (
  `id_venta_pago_factura` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_venta_pago` INT NOT NULL,
  `id_factura` INT NOT NULL
) COMMENT = "Tambla relacional para las facturas de pago y los pagos de las ventas";