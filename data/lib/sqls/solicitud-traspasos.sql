DROP TABLE IF EXISTS paal_solicitud_transferencias;

CREATE TABLE
  IF NOT EXISTS paal_solicitud_transferencias (
    id_solicitud_transferencia INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_sucursal_origen INT NOT NULL,
    id_sucursal_destino INT NOT NULL,
    folio VARCHAR(20) NOT NULL,
    notas TEXT,
    status ENUM (
      'pendiente',
      'aprobado',
      'completado',
      'rechazado',
      'cancelado'
    ) NOT NULL DEFAULT 'pendiente',
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES adm_usuarios (id_usuario),
    FOREIGN KEY (id_sucursal_origen) REFERENCES paal_sucursales (id_sucursal),
    FOREIGN KEY (id_sucursal_destino) REFERENCES paal_sucursales (id_sucursal)
  );

DROP TABLE IF EXISTS paal_solicitud_transferencia_productos;

CREATE TABLE
  IF NOT EXISTS paal_solicitud_transferencia_productos (
    id_solicitud_transferencia_producto INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_solicitud_transferencia INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad_solicitada INT NOT NULL,
    cantidad_atendida INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_solicitud_transferencia) REFERENCES paal_solicitud_transferencias (id_solicitud_transferencia),
    FOREIGN KEY (id_producto) REFERENCES paal_productos (id_producto)
  );

ALTER TABLE paal_inventario_transferencias
ADD COLUMN id_solicitud_transferencia INT NULL