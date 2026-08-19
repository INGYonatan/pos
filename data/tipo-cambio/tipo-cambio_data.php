<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'tipo-cambio';

switch ($action) {
  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_configuracion = cleanStr($_POST['uid']);
      $tipo_cambio      = cleanStr($_POST['tipo_cambio']);

      $query = "UPDATE {$db_dti}_configuraciones SET
          valor = '{$tipo_cambio}'
        WHERE
          id_configuracion = {$id_configuracion}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El tipo de cambio se actualizó correctamente'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
