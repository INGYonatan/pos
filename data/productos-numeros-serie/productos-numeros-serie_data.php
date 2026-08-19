<?php
require_once __DIR__ . '/../lib/settings.inc.php';
require_once __DIR__ . "/../lib/helpers/serial-numbers.helper.php";
require_once __DIR__ . "/../lib/helpers/products.helper.php";

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$user_data   = getUserData(get_id_usuario());

$action      = $_POST['action'];
$identifier  = 'productos-numeros-serie';

$model = new SerialNumbersHelper();

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

switch ($action) {
  /* Caso múltiple */
  case "load-{$identifier}-disponibles":
  case "load-{$identifier}-vendidos":
    if (!checkModuleActionPermission($identifier, 'ver')) break;

    $haveActions = haveActions($identifier, 'tabla');

    $page     = $_POST['page'];
    $per_page = $_POST['perPage'];
    $term     = cleanStr($_POST['search']);
    $status   = $action == "load-{$identifier}-disponibles" ? 'disponible' : 'vendido';
    $branchId = $IS_ADMIN && isset($_POST['id_sucursal']) ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();

    if ($status == "vendido") $haveActions = false;

    $result = $model->read([
      "term"    => $term,
      "page"    => $page,
      "perPage" => $per_page,
      "status"  => $status,
      "branchId" => $branchId
    ]);

    if ($result->status == "error") {
      echo getEmptyTableMessage();
      exit;
    }

    $rows       = $result->data['rows'];
    $totalRows  = $result->data['total'];

    if (count($rows) == 0) {
      echo getEmptyTableMessage();
      exit;
    }

    include __DIR__ . "/{$identifier}_table.php";

    exit;
    break;

  case "edit-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'editar')) break;

    $id           = cleanStr($_POST['uid']);
    $serialNumber = cleanStr($_POST['serialNumber']);

    $model->getById($id);

    if (!$model->getId()) break;

    $model->setSerialNumber($serialNumber);

    $result = $model->update();

    if ($result->status == "error") $response['toastMessage'] = $result->message;

    if ($result->status == "success") $response = [
      "status"        => "success",
      "toastMessage"  => "¡Registro actualizado exitosamente!",
      "callback"      => '{
        load("' . $page . '", "' . $identifier . '-disponibles");
        load("' . $page . '", "' . $identifier . '-vendidos");
      }'
    ];
    break;
}

mysqli_close($mysqli);
echo json_encode($response);
exit;
