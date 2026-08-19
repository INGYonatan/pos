<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action      = $_POST['action'];
$identifier  = 'roles';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions = haveActions($identifier, 'tabla');

    $per_page   = $_POST['perPage'];
    $page       = $_POST['page'];

    $search     = cleanStr($_POST['search']);

    $column_id  = "id_rol";
    $c_from     = "{$db_ati}_roles";
    $c_extra_clauses    = "ORDER BY id_rol DESC";

    $fields = [
      "id_rol",
      ["id_rol", "uid"],
      "rol",
      "slug"
    ];

    $c_join     = "";

    $c_where    = [
      ["id_rol", "1", "!="]
    ];

    if (!empty($search)) array_push($c_where, ["rol", "%$search%", "LIKE"]);

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

    //echo getEmptyTableMessage($request);
    //die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';

    die;
    break;

  case 'add-' . $identifier:
    if (checkModuleActionPermission($identifier, 'agregar')) :
      $request = useInsertByPost([
        'table_name' => "{$db_ati}_roles"
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => '¡El rol se agregó correctamente!',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_rol = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "{$db_ati}_roles",
        'conditions' => [['id_rol', $id_rol]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => '¡El rol se actualizó correctamente!',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_rol = cleanStr($_POST['uid']);

      $query        = "DELETE FROM {$db_ati}_roles WHERE id_rol = $id_rol";
      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El rol se eliminó correctamente'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
