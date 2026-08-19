<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'cliente-direcciones';

$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];
    $customer_id      = $_POST['id_cliente'];

    $search           = cleanStr($_POST['search']);

    $column_id        = "id_cliente_direccion";
    $c_from           = "{$db_dti}_cliente_direcciones AS CD";
    $c_extra_clauses  = "ORDER BY id_cliente_direccion DESC";

    $fields = [
      "CD.id_cliente_direccion",
      ["CD.id_cliente_direccion", "uid"],
      "CD.id_cliente",
      "CD.id_estado",
      "CD.id_ciudad",
      "CD.id_colonia",
      "CD.nombre_comercial",
      "CD.codigo_postal",
      "CD.calle",
      "CD.n_exterior",
      "CD.n_interior",
      "CD.entre_calles",
      "CD.referencias",
      ["E.Estado", "estado"],
      ["M.Municipio", "ciudad"],
      ["CO.Colonia", "colonia"]
    ];

    $c_join = "
      LEFT JOIN
        estados AS E ON (E.idEstado = CD.id_estado)
      LEFT JOIN
        municipios AS M ON (M.idMunicipio = CD.id_ciudad)
      LEFT JOIN
        colonias AS CO ON (CO.idColonia = CD.id_colonia)
    ";

    $c_where = [
      ['CD.id_cliente', $customer_id],
      ['CD.status', 'activo']
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["CD.codigo_postal", "%$search%", "LIKE", "OR"],
        ["CD.calle", "%$search%", "LIKE", "OR"],
        ["E.estado", "%$search%", "LIKE", "OR"],
        ["M.Municipio", "%$search%", "LIKE", "OR"],
        ["CO.colionia", "%$search%", "LIKE", "OR"]
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
      $request = useInsertByPost([
        'table_name' => "{$db_dti}_cliente_direcciones",
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') {
        $customerAddressId = $request['id'];
        $name       = cleanStr($_POST['nombre_comercial']);

        $origin     = $_POST["origin"];
        $callback   = 'load("' . $page . '", "' . $identifier . '");';

        if ($origin == "pos") {
          $optionStr = "<option value='{$customerAddressId}' selected>{$name}</option>";

          $callback = "{
            $('#atc-addressId').append(`{$optionStr}`);
            $('#atc-addressId').val(`{$customerAddressId}`).trigger('change');
          };";
        }

        $response = [
          'status'        => 'success',
          'toastMessage'  => 'La dirección se agregó correctamente',
          'callback'      => $callback
        ];
      }
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_cliente_direccion = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_cliente_direcciones",
        'conditions' => [['id_cliente_direccion', $id_cliente_direccion]],
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'La dirección se actualizó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_cliente_direccion = cleanStr($_POST['uid']);

      $query = "UPDATE {$db_dti}_cliente_direcciones SET
            status = 'eliminado'
          WHERE
            id_cliente_direccion = {$id_cliente_direccion}
        ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La dirección se eliminó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
