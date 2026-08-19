<?php
include '../lib/settings.inc.php';

$term    = cleanStr($_GET['term']);
$value   = cleanStr($_GET['value']);

$catalog = new stdClass();
$catalog->results = [];

$catalog->pagination = new stdClass();
$catalog->pagination->more = false;

$id    = "";
$label = "--Seleccionar--";

$result       = new stdClass();
$result->id   = $id;
$result->text = $label;

$catalog->results[] = $result;
$catalog->selectedValue = $value;

$cWhere = !empty($value) ? "id_clave_producto_servicio = {$value}" : "
    descripcion LIKE _utf8'%{$term}%' collate utf8_unicode_ci OR
    clave       LIKE _utf8'%{$term}%' collate utf8_unicode_ci
";

//if (!empty($term)) :
$query = "SELECT
    id_clave_producto_servicio,
    clave,
    descripcion
  FROM {$db_dti}_clave_producto_servicios
  WHERE
    {$cWhere}
  LIMIT 10
";

$query_result = mysqli_query($mysqli, $query);
$num_rows     = mysqli_num_rows($query_result);

if ($num_rows > 0) :
  while ($row = mysqli_fetch_assoc($query_result)) :
    $result       = new stdClass();
    $result->id   = $row['id_clave_producto_servicio'];
    $result->text = $row['clave'] . ' - ' . $row['descripcion'];
    $result->clave = $row['clave'];
    $result->nombre = $row['descripcion'];

    $catalog->results[] = $result;
  endwhile;
endif;
//endif;

echo json_encode($catalog);
die;
