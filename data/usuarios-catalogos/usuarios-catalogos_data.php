<?php
require_once __DIR__ . '/../lib/settings.inc.php';
require_once __DIR__ . "/../lib/helpers/user-files.helper.php";

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$user_data   = getUserData(get_id_usuario());

$action      = $_POST['action'];
$identifier  = 'usuarios-catalogos';

$model = new UserFilesHelper();

$filePath           = BASE_PATH . "/src/assets/userfiles/";
$extensionsAllowed  = ['pdf'];

switch ($action) {
  case "load-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'ver')) break;

    $haveActions = haveActions($identifier, 'tabla');

    $userId   = $_POST['userId'];
    $page     = $_POST['page'];
    $per_page = $_POST['perPage'];
    $term     = cleanStr($_POST['search']);

    $result = $model->read([
      "term"    => $term,
      "page"    => $page,
      "perPage" => $per_page,
      "userId"  => $userId,
      "status"  => "activo"
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
  /*  */
  case "add-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'agregar')) break;

    $userId = $_POST['userId'];
    $name   = cleanStr($_POST['name']);
    $slug   = createSlug($name);
    $file   = $_FILES['file'];

    if (!$file["name"]) {
      $response['toastMessage'] = "¡Selecciona un archivo para continuar!";
      break;
    }

    $fileResult = processFile($file, $extensionsAllowed, $filePath, $slug);

    if ($fileResult == "no-valid") {
      $response['toastMessage'] = "¡Archivo no permitido!, solo se permiten los siguientes formatos: " . implode(", ", $extensionsAllowed);
      break;
    }

    if ($fileResult == "no-move") {
      $response['toastMessage'] = "¡Error al subir el archivo!, intentalo nuevamente";
      break;
    }

    $slug = $fileResult;

    $model->setUserId($userId);
    $model->setName($name);
    $model->setSlug($slug);
    $model->setType(pathinfo($slug, PATHINFO_EXTENSION));

    $result = $model->create();

    if ($result->status == "error") $response['toastMessage'] = $result->message;

    if ($result->status == "success") $response = [
      "status"        => "success",
      "toastMessage"  => "¡Registro agregado exitosamente!",
      "callback"      => 'load("' . $page . '", "' . $identifier . '");'
    ];
    break;
  // /*  */
  // case "edit-{$identifier}":
  //   if (!checkModuleActionPermission($identifier, 'editar')) break;

  //   $id                   = cleanStr($_POST['uid']);
  //   $name                 = cleanStr($_POST['name']);
  //   $slug                 = createSlug($name);
  //   $requiresSerialNumber = isset($_POST['requiresSerialNumber']) ? 1 : 0;
  //   $tangible             = isset($_POST['tangible']) ? 1 : 0;
  //   $isAdvance            = isset($_POST['isAdvance']) ? 1 : 0;
  //   $isCreditNote         = isset($_POST['isCreditNote']) ? 1 : 0;

  //   $model->getById($id);

  //   if (!$model->getId()) break;

  //   $model->setId($id);
  //   $model->setName($name);
  //   $model->setSlug($slug);
  //   $model->setRequiresSerialNumber($requiresSerialNumber);
  //   $model->setTangible($tangible);
  //   $model->setIsAdvance($isAdvance);
  //   $model->setIsCreditNote($isCreditNote);

  //   $result = $model->update();

  //   if ($result->status == "error") $response['toastMessage'] = $result->message;

  //   if ($result->status == "success") $response = [
  //     "status"        => "success",
  //     "toastMessage"  => "¡Registro editado exitosamente!",
  //     "callback"      => 'load("' . $page . '", "' . $identifier . '");'
  //   ];
  //   break;
  /*  */
  case "action-eliminar-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'eliminar')) break;

    $id = cleanStr($_POST['uid']);

    $model->getById($id);

    if (!$model->getId()) break;

    $model->setStatus("eliminado");

    $result = $model->update();

    if ($result->status == "success") $response = [
      "status"        => "success",
      "toastMessage"  => "¡Registro eliminado exitosamente!",
      "callback"      => 'load("' . $page . '", "' . $identifier . '");'
    ];
    break;
}

mysqli_close($mysqli);
echo json_encode($response);
exit;
