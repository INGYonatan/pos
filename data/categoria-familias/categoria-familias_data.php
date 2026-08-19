<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action       = $_POST['action'];
$identifier   = 'categoria-familias';
$id_categoria = cleanStr($_POST['id_categoria']);

switch ($action) {
  case 'load-' . $identifier:
    $have_actions = haveActions($identifier, 'tabla');

    $per_page   = $_POST['perPage'];
    $page       = $_POST['page'];

    $search     = cleanStr($_POST['search']);

    $column_id  = "CF.id_categoria_familia";
    $c_from     = "{$db_dti}_categoria_familias CF";
    $c_extra_clauses = "ORDER BY CF.id_categoria_familia DESC";

    $fields = [
      "CF.id_categoria_familia",
      ["CF.id_categoria_familia", "uid"],
      "CF.id_categoria",
      "CF.familia",
      "CF.limite_descuento",
      "CF.cantidad_mayoreo",
      "CF.precio_mayoreo",
      "C.categoria",
      "M.marca"
    ];

    $c_join     = "
        LEFT JOIN
          {$db_dti}_categorias AS C ON CF.id_categoria = C.id_categoria
        LEFT JOIN
          {$db_dti}_marcas AS M ON C.id_marca = M.id_marca
    ";

    $c_where    = [];

    if (!empty($search)) array_push($c_where, ["CF.familia", "%$search%", "LIKE"]);
    if (!empty($id_categoria)) array_push($c_where, ["CF.id_categoria", $id_categoria]);

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
        'table_name' => "{$db_dti}_categoria_familias",
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') {
        $origin               = $_POST['origin'];
        $id_categoria         = cleanStr($_POST['id_categoria']);
        $id_categoria_familia = $request['id'];

        $response = [
          'status'        => 'success',
          'toastMessage'  => '¡La familia se agregó correctamente!',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];

        if ($origin == "productos") {
          $response["callback"] = "{
            getCatalog({
              catalogSelector: '#id_categoria_familia',
              parameters: {
                action: 'get-category-families',
                value: {$id_categoria},
                selectedValue: {$id_categoria_familia},
              }
            });
          }";

          $response["modalSelector"] = "#categoria-familias-modal";
        }
      }
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_categoria_familia = cleanStr($_POST['uid']);
      $cantidad_mayoreo     = cleanStr($_POST['cantidad_mayoreo']);
      $precio_mayoreo       = cleanStr($_POST['precio_mayoreo']);

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_categoria_familias",
        'conditions' => [['id_categoria_familia', $id_categoria_familia]],
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') :
        $query = "UPDATE {$db_dti}_productos SET
            cantidad_mayoreo        = {$cantidad_mayoreo},
            precio_mayoreo          = {$precio_mayoreo},
            precio_mayoreo_original = {$precio_mayoreo}
          WHERE
            id_categoria_familia = {$id_categoria_familia}
        ";

        mysqli_query($mysqli, $query);

        $response = [
          'status'        => 'success',
          'toastMessage'  => '¡La familia se actualizó correctamente!',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_categoria_familia = cleanStr($_POST['uid']);

      $query        = "DELETE FROM {$db_dti}_categoria_familias WHERE id_categoria_familia = $id_categoria_familia";
      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La familia se eliminó correctamente',
        'callback'      => 'load("1", "' . $identifier . '");'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
