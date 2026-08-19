<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action       = $_POST['action'];
$identifier   = 'acciones';
$per_page     = $_POST['perPage'];
$page         = $_POST['page'];

switch ($action) {
  case 'load-' . $identifier:
    $have_actions = haveActions($identifier, 'tabla');

    $search     = cleanStr($_POST['search']);
    $tipo       = cleanStr($_POST['tipo']);

    $column_id  = "id_accion";
    $c_from     = "{$db_ati}_acciones";
    $c_extra_cluses = "ORDER BY orden ASC";

    $fields = [
      "id_accion",
      ["id_accion", "uid"],
      "accion",
      "tipo",
      "ubicacion",
      "clase_html",
      "icono",
      "alerta",
      "alerta_titulo",
      "alerta_mensaje",
      "orden",
      "slug",
      "_blank"
    ];

    $c_join     = "";
    $c_where    = [];

    if (!empty($search))  array_push($c_where, ["accion", "%$search%", "LIKE"]);
    if (!empty($tipo))    array_push($c_where, ["tipo",   "$tipo"]);

    $request = useDataTable([
      'column_id'     => $column_id,
      'from'          => $c_from,
      'where'         => $c_where,
      'fields'        => $fields,
      'join'          => $c_join,
      'extra_clauses' => $c_extra_cluses,
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
    if (checkModuleActionPermission($identifier, 'editar')) :
      $request = useInsertByPost([
        'table_name'      => "{$db_ati}_acciones",
        'clean_priority'  => ['clase_html' => 'none']
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => '¡La acción se agregó correctamente!',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_accion = cleanStr($_POST['uid']);

      $_POST['alerta'] = empty($_POST['alerta']) ? 'no' : 'si';

      $request = useUpdateByPost([
        'table_name'      => "{$db_ati}_acciones",
        'clean_priority'  => ['clase_html' => 'none'],
        "conditions"      => [['id_accion', $id_accion]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => '¡La acción se actualizó correctamente!',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_accion = cleanStr($_POST['uid']);

      $query        = "DELETE FROM {$db_ati}_acciones WHERE id_accion = $id_accion";
      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La acción se eliminó correctamente'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
