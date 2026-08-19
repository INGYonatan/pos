<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'clientes';

$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);

    $column_id        = "C.id_cliente";
    $c_from           = "{$db_dti}_clientes AS C";
    $c_extra_clauses  = "ORDER BY C.id_cliente DESC";

    $fields = [
      "C.id_cliente",
      ["C.id_cliente", "uid"],
      "C.id_regimen_fiscal",
      "C.nombre_completo",
      "C.nombre_comercial",
      "C.razon_social",
      "C.rfc",
      "C.domicilio_fiscal",
      "C.tipo",
      "C.correo",
      "C.telefono",
      "C.requiere_factura",
      "C.limite_credito",
      "C.limite_credito_plazo",
      "RF.regimen_fiscal"
    ];

    $c_join = "
        LEFT JOIN regimen_fiscal AS RF ON (RF.id_regimen_fiscal = C.id_regimen_fiscal)
    ";

    $c_where = [
      ["C.status", "activo"]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["C.nombre_completo", "%$search%", "LIKE", "OR"],
        ["C.correo",          "%$search%", "LIKE", "OR"],
        ["C.telefono",        "%$search%", "LIKE", "OR"]
      ]
    ]);

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
      $_POST['requiere_factura'] = $_POST['requiere_factura'] === 'si' ? 'si' : 'no';

      $request = useInsertByPost([
        'table_name' => "{$db_dti}_clientes",
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') {
        $customerId = $request['id'];
        $name       = cleanStr($_POST['nombre_completo']);

        $origin     = $_POST["origin"];
        $callback   = 'load("' . $page . '", "' . $identifier . '");';

        if ($origin == "pos") $callback = "{
            $('#atc-customerId').val('{$customerId}');
            $('#atc-customerId_label').val('{$name}');
        }";

        if ($origin == "facturas-nueva") {
          $optionStr = "<option value='{$customerId}' selected>{$name}</option>";

          $callback = "{
            $('#fdf-id_cliente').append(`{$optionStr}`).val(`{$customerId}`).trigger('change');
          }";
        }

        $response = [
          'status'        => 'success',
          'toastMessage'  => 'El cliente se agregó correctamente',
          'callback'      => $callback
        ];
      }
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_cliente = cleanStr($_POST['uid']);

      $_POST['requiere_factura'] = $_POST['requiere_factura'] === 'si' ? 'si' : 'no';

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_clientes",
        'conditions' => [['id_cliente', $id_cliente]],
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'El cliente se actualizó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_cliente = cleanStr($_POST['uid']);

      $query = "UPDATE {$db_dti}_clientes SET
            status = 'eliminado'
          WHERE
            id_cliente = {$id_cliente}
        ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El cliente se eliminó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
