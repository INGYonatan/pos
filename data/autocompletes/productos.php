<?php
include '../lib/settings.inc.php';

$term       = cleanStr($_GET['term']);
$response   = [];

//-- Start - Bloque para buscar por termino parseado y partido en palabras
$byParts    = "";
$termParts  = explode(' ', $term);

if (count($termParts) > 1) {
  $byParts  = "";
  $parts    = [];

  foreach ($termParts as $part) {
    $part = trim($part);

    if (!empty($part)) {
      $parts[] = "(codigo LIKE _utf8'%$part%' collate utf8_unicode_ci OR nombre_producto LIKE _utf8'%$part%' collate utf8_unicode_ci)";
    }
  }

  $byParts = 'OR (' . implode(' AND ', $parts) . ')';
}

if (!empty($term)) :
  $query = "SELECT
      id_producto,
      codigo,
      nombre_producto,
      contenido,
      precio_venta
    FROM {$db_dti}_productos
    WHERE
      status = 'activo' AND
      (
        codigo      LIKE _utf8'%$term%' collate utf8_unicode_ci OR
        nombre_producto LIKE _utf8'%$term%' collate utf8_unicode_ci
      )
      {$byParts}
    ORDER BY
      codigo ASC
    LIMIT 20
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $label = "{$row['codigo']} - {$row['nombre_producto']}";

      array_push($response, [
        'value'       => $row['nombre_producto'],
        'label'       => $label,
        'id_producto' => $row['id_producto'],
        'uid'         => $row['id_producto']
      ]);
    endwhile;
  endif;
endif;

echo json_encode($response);
mysqli_close($mysqli);
die();
