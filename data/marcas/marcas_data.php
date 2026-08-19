<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action      = $_POST['action'];
$identifier  = 'marcas';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions = haveActions($identifier, 'tabla');

    $per_page   = $_POST['perPage'];
    $page       = $_POST['page'];

    $search     = cleanStr($_POST['search']);

    $column_id        = "id_marca";
    $c_from           = "{$db_dti}_marcas";
    $c_extra_clauses  = "ORDER BY id_marca DESC";

    $fields = [
      "id_marca",
      ["id_marca", "uid"],
      "marca"
    ];

    $c_join     = "";

    $c_where    = [];

    if (!empty($search)) array_push($c_where, [[
      ["marca", "%$search%", "LIKE"]
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
        'table_name' => "{$db_dti}_marcas",
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') {
        $id_marca = $request["id"];
        $origin = $_POST['origin'];

        $response = [
          'status'        => 'success',
          'toastMessage'  => '¡La marca se agregó correctamente!',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];

        if ($origin == "productos") {
          $response["callback"] = "{
            getCatalog({
              catalogSelector: '[name=\"id_marca\"]',
              parameters: {
                action: 'get-brands',
                selectedValue: {$id_marca}
              },
              onSuccess: () => {
                $('#btn-add-line-container').show();
                $('#id_categoria').html('<option value=\"\">--Seleccionar--</option>');
                $('#id_categoria_familia').html('<option value=\"\">--Seleccionar--</option>');
                $('#btn-add-family-container').hide();
              }
            });
          }";
          $response["modalSelector"] = "#marcas-modal";
        }
      }
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_marca = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_marcas",
        'conditions' => [['id_marca', $id_marca]],
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => '¡La marca se actualizó correctamente!',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_marca = cleanStr($_POST['uid']);

      $query        = "DELETE FROM {$db_dti}_marcas WHERE id_marca = $id_marca";
      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La marca se eliminó correctamente',
        'callback'      => 'load("1", "' . $identifier . '");'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
