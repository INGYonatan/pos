<?php
require_once __DIR__ . '/../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'usuarios-permisos';
$userId     = cleanStr($_POST['uid']);

// validar si el usuario existe
$query    = "SELECT * FROM adm_usuarios WHERE MD5(id_usuario) = '{$userId}' LIMIT 1";
$result   = mysqli_query($mysqli, $query);
$numRows  = mysqli_num_rows($result);

if ($numRows == 0) {
  mysqli_close($mysqli);
  echo json_encode($response);
  die;
}

$userData = mysqli_fetch_assoc($result);
$userId   = $userData["id_usuario"];

switch ($action) {
  case "load-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'ver')) die;

    $perPage  = 150;
    $page     = $_POST['page'];
    $search   = cleanStr($_POST['search']);

    $columnId = "RMA.id_rol_modulo_accion";
    $cFrom    = "adm_rol_modulo_acciones AS RMA";

    $fields   = [
      "RMA.id_rol_modulo_accion",
      ["RMA.id_rol_modulo_accion", "uid"],
      "M.id_modulo",
      "M.modulo"
    ];

    $cJoin = "
      INNER JOIN
        adm_modulo_acciones AS MA ON MA.id_modulo_accion = RMA.id_modulo_accion
      INNER JOIN
        adm_modulos AS M ON M.id_modulo = MA.id_modulo
      INNER JOIN
        adm_roles AS R ON (R.id_rol = RMA.id_rol AND R.slug = 'vendedor')
    ";

    $cWhere = [
      // ["M.slug", "api-bots-rims", "!="],
      // ["M.slug", "api-bots-tires", "!="],
      // ["M.slug", "colaboradores", "!="],
      // ["M.slug", "colaboradores-permisos", "!="],
      // ["M.slug", "usuario-llanteras", "!="]
    ];

    if (!empty($search)) array_push($cWhere, ["M.modulo", "%$search%", "LIKE"]);

    $extraCluses = "
      GROUP BY
        M.id_modulo
      ORDER BY
        M.orden
      ASC
    ";

    $request = useDataTable([
      'column_id'     => $columnId,
      'from'          => $cFrom,
      'where'         => $cWhere,
      'fields'        => $fields,
      'join'          => $cJoin,
      'extra_clauses' => $extraCluses,
      'per_page'      => $perPage,
      'page'          => $page
    ]);

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include "{$identifier}_table.php";
    die;
    break;
  /*  */
  case "add-permission-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'agregar')) die;

    try {
      $moduleId       = $_POST["moduleId"];
      $moduleActionId = $_POST["moduleActionId"];

      $query = "INSERT INTO adm_usuario_permisos
        (
          id_usuario,
          id_modulo_accion
        ) VALUES (
          {$userId},
          {$moduleActionId}
        )
      ";

      $result = mysqli_query($mysqli, $query);

      if ($result) $response = [
        "status" => "success"
      ];

      if (!$result) $response['toastMessage'] = '¡Error al agregar el permiso!';
    } catch (Exception $e) {
      $response["toastMessage"] = "";
    }
    break;
  /*  */
  case "remove-permission-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'eliminar')) die;

    try {
      $moduleId       = $_POST["moduleId"];
      $moduleActionId = $_POST["moduleActionId"];

      $query = "DELETE FROM adm_usuario_permisos
        WHERE
          id_usuario        = {$userId} AND
          id_modulo_accion  = {$moduleActionId}
      ";

      $result = mysqli_query($mysqli, $query);

      if ($result) $response = [
        "status" => "success"
      ];

      if (!$result) $response['toastMessage'] = '¡Error al eliminar el permiso!';
    } catch (Exception $e) {
      $response["toastMessage"] = "";
    }
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
