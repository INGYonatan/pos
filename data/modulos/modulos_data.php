<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action      = $_POST['action'];
$identifier  = 'modulos';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions = haveActions($identifier, 'tabla');

    $initial_query = "SELECT
        *,
        id_modulo AS uid
      FROM
        {$db_ati}_modulos
    ";

    $query = $initial_query . "
      WHERE
        id_padre = 0
      ORDER BY
        orden
      ASC
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows === 0) echo getEmptyTableMessage();
    if ($num_rows > 0) include $identifier . '_table.php';

    die;

    $have_actions = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $rol              = cleanStr($_POST['rol']);

    $column_id        = "id_modulo";
    $c_from           = "{$db_ati}_modulos";
    $c_extra_clauses  = "ORDER BY id_modulo DESC";

    $fields = [
      "id_modulo",
      ["id_modulo", "uid"],
      "modulo",
      "slug"
    ];

    $c_join   = "";
    $c_where  = [
      [[
        ["slug", "roles",     "!="],
        ["slug", "acciones",  "!="],
        ["slug", "menu",      "!="],
        ["slug", "modulos",   "!="]
      ]]
    ];

    if (!empty($search)) array_push($c_where, ["modulo", "%$search%", "LIKE"]);

    $request = useDataTable([
      'column_id'     => $column_id,
      'from'          => $c_from,
      'where'         => $c_where,
      'fields'        => $fields,
      'join'          => $c_join,
      'extra_clauses' => $c_extra_clauses,
      'per_page'      => $per_page,
      'page'          => $page
    ]);

    //echo getEmptyTableMessage($request['query']);
    //die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';
    die;
    break;

  case 'add-' . $identifier:
    if (checkModuleActionPermission($identifier, 'agregar')) :
      $acciones = $_POST['acciones'];

      $request = useInsertByPost([
        'table_name'      => "{$db_ati}_modulos",
        'excluded_fields' => ['acciones']
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') :
        $id_modulo  = $request['id'];

        foreach ($acciones as $key => $id) :
          $id_accion = cleanStr($id);

          $query = "INSERT INTO {$db_ati}_modulo_acciones (
                id_modulo,
                id_accion
              ) VALUES (
                $id_modulo,
                $id_accion
              )
            ";

          mysqli_query($mysqli, $query);
        endforeach;

        $response = [
          'status'        => 'success',
          'toastMessage'  => '¡El modulo se agregó correctamente!',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_modulo  = cleanStr($_POST['uid']);
      $acciones   = $_POST['acciones'];

      $request = useUpdateByPost([
        'table_name'      => "{$db_ati}_modulos",
        'excluded_fields' => ['acciones'],
        "conditions"      => [['id_modulo', $id_modulo]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') :
        updateModuleActionIds(
          $id_modulo,
          $acciones
        );

        $response = [
          'status'        => 'success',
          'toastMessage'  => '¡El modulo se actualizó correctamente!',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_modulo = cleanStr($_POST['uid']);

      $query        = "DELETE FROM {$db_ati}_modulos WHERE id_modulo = $id_modulo";
      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El modulo se eliminó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'edit-rol-module-actions-' . $identifier:
    if (checkModuleActionPermission($identifier, 'permisos')) :
      $id_modulo  = cleanStr($_POST['uid']);
      $rol_ids    = $_POST['rol_ids'];


      foreach ($rol_ids as $key => $id) :
        $id_rol   = cleanStr($id);
        $acciones = $_POST["acciones-$id_rol"];

        updateRolModuleActionIds(
          $id_modulo,
          $id_rol,
          $acciones
        );
      endforeach;

      $response = [
        'status'        => 'success',
        'toastMessage'  => 'Los permisos del modulo se actualizaron correctamente.',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case "sort-modulos-items":
    if (!checkModuleActionPermission($identifier, 'editar')) break;

    try {
      $items = $_POST["items"];
      $items = json_decode($items, true);

      foreach ($items as $item) {
        $id       = $item["id"];
        $order    = $item["order"];
        $parentId = $item["parentId"] ?? 0;

        $query = "UPDATE {$db_ati}_modulos SET
            orden       = {$order},
            id_padre = '{$parentId}'
          WHERE
            id_modulo = {$id}
        ";

        mysqli_query($mysqli, $query);
      }

      $response = [
        'status'        => 'success',
        'toastMessage'  => 'El orden de los módulos se actualizaron correctamente'
      ];
    } catch (Exception $e) {
      error_log("ERROR_SORT_MENU_ITEMS: " . $e->getMessage());
    }
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
