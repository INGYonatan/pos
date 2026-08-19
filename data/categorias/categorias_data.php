<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action      = $_POST['action'];
$identifier  = 'categorias';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions = haveActions($identifier, 'tabla');

    $per_page   = $_POST['perPage'];
    $page       = $_POST['page'];

    $search     = cleanStr($_POST['search']);
    $id_marca   = cleanStr($_POST['id_marca']);

    $column_id        = "C.id_categoria";
    $c_from           = "{$db_dti}_categorias AS C";
    $c_extra_clauses  = "ORDER BY C.id_categoria DESC";

    $fields = [
      "C.id_categoria",
      ["C.id_categoria", "uid"],
      "C.id_marca",
      "C.categoria",
      "M.marca"
    ];

    $c_join     = "
        LEFT JOIN {$db_dti}_marcas AS M ON (C.id_marca = M.id_marca)
    ";

    $c_where    = [];

    if (!empty($id_marca)) array_push($c_where, ["C.id_marca", $id_marca]);

    if (!empty($search)) array_push($c_where, [[
      ["C.categoria", "%$search%", "LIKE"],
      ["M.marca", "%$search%", "LIKE", "OR"]
    ]]);

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
        'table_name' => "{$db_dti}_categorias",
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') {
        $origin       = $_POST['origin'];
        $id_categoria = $request["id"];
        $id_marca     = cleanStr($_POST['id_marca']);

        $response = [
          'status'        => 'success',
          'toastMessage'  => '¡La categoría se agregó correctamente!',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];

        if ($origin == "productos") {
          $response["callback"] = "{
            getCatalog({
              catalogSelector: '#id_categoria',
              parameters: {
                action: 'get-brand-categories',
                value: {$id_marca},
                selectedValue: {$id_categoria}
              },
              onSuccess: () => {
                $('#fdmcf-id_categoria').val({$id_categoria});
                $('#btn-add-family-container').show();
                $('#id_categoria_familia').html('<option value=\"\">--Seleccionar--</option>');
              }
            });
          }";

          $response["modalSelector"] = "#categorias-modal";
        }
      }
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_categoria = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_categorias",
        'conditions' => [['id_categoria', $id_categoria]],
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => '¡La categoría se actualizó correctamente!',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_categoria = cleanStr($_POST['uid']);

      $query        = "DELETE FROM {$db_dti}_categorias WHERE id_categoria = $id_categoria";
      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La categoría se eliminó correctamente',
        'callback'      => 'load("1", "' . $identifier . '");'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
