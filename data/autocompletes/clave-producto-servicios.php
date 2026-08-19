<?php
include '../lib/settings.inc.php';

$term       = cleanStr($_GET['term']);
$response   = [];

if (!empty($term)) :
  $query = "SELECT
      id_clave_producto_servicio,
      clave,
      descripcion
    FROM {$db_dti}_clave_producto_servicios
    WHERE
      descripcion LIKE _utf8'%$term%' collate utf8_unicode_ci OR
      clave       LIKE _utf8'%$term%' collate utf8_unicode_ci
    LIMIT 20
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $label = "{$row['clave']} - {$row['descripcion']}";

      array_push($response, [
        'value'       => $row['descripcion'],
        'label'       => $label,
        'uid'         => $row['id_clave_producto_servicio']
      ]);
    endwhile;
  endif;
endif;

echo json_encode($response);
mysqli_close($mysqli);
die();
