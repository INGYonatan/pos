CREATE TABLE
  IF NOT EXISTS paal_usuario_archivos (
    id_usuario_archivo int NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_usuario int NOT NULL,
    nombre varchar(255) DEFAULT NULL,
    slug varchar(255) DEFAULT NULL,
    tipo VARCHAR(255) DEFAULT 'pdf',
    status enum ('activo', 'eliminado') DEFAULT 'activo'
  );