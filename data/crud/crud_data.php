<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'categorias';

$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');
$carpeta_imagenes       = '../../../src/assets/images/blogs/';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);

    $column_id        = "id_categoria";
    $c_from           = "up_categorias";
    $c_extra_clauses  = "ORDER BY id_categoria DESC";

    $fields = [
      "id_categoria",
      ["id_categoria", "uid"],
      "categoria"
    ];

    $c_join = "";

    $c_where = [];

    if (!empty($search)) array_push($c_where, ["categoria",  "%$search%", "LIKE"]);

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
      $slug = createSlug($_POST['categoria'], 150);

      $extra_fields               = [];
      $extra_fields['slug']       = $slug;

      $request = useInsertByPost([
        'table_name'      => "up_categorias",
        'extra_fields'    => $extra_fields
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'La categoría se agregó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_categoria = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "up_categorias",
        'conditions' => [['id_categoria', $id_categoria]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'La categoría se actualizó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_categoria = cleanStr($_POST['uid']);

      $query = "DELETE FROM up_categorias WHERE
          id_categoria = $id_categoria
        ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La categoría se eliminó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
