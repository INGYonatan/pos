<?php
require_once __DIR__ . "/../inc/session.inc.php";

// Actualizar todas las transferencias activas a procesado-correctamente
$query = "UPDATE {$db_dti}_inventario_transferencias SET
    status = 'procesado-correctamente'
  WHERE
    status = 'activo'
";

mysqli_query($mysqli, $query);

// Actualizar los productos de las trasnferencias completadas y que no han sido cancelados
$query = "UPDATE {$db_dti}_inventario_transferencia_productos SET
    completado = 'si',
    recibido = cantidad
  WHERE
    cancelado = 'no' AND
    id_inventario_transferencia IN (
      SELECT
        id_inventario_transferencia
      FROM
        {$db_dti}_inventario_transferencias
      WHERE
        status = 'procesado-correctamente'
    )
";

mysqli_query($mysqli, $query);

// Actualizar los números de serie de las transferencias completadas y que no han sido cancelados
$query = "UPDATE {$db_dti}_inventario_transferencia_producto_numeros_serie SET
    completado = 'si',
    recibido = 'si'
  WHERE
    cancelado = 'no' AND
    id_inventario_transferencia IN (
      SELECT
        id_inventario_transferencia
      FROM
        {$db_dti}_inventario_transferencias
      WHERE
        status = 'procesado-correctamente'
    )
";

mysqli_query($mysqli, $query);
