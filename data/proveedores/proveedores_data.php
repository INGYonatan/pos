<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'proveedores';

$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');
$carpeta_imagenes       = '../../../src/assets/images/blogs/';
$id_almacen             = getStoreId();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);

    $column_id        = "id_proveedor";
    $c_from           = "{$db_dti}_proveedores";
    $c_extra_clauses  = "ORDER BY id_proveedor DESC";

    $fields = [
      "id_proveedor",
      ["id_proveedor", "uid"],
      "nombre_proveedor",
      "nombre_comercial",
      "correo",
      "telefono"
    ];

    $c_join = "";

    $c_where = [
      ["status", "activo"]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["nombre_proveedor",  "%$search%", "LIKE", "OR"],
        ["nombre_comercial",  "%$search%", "LIKE", "OR"],
        ["telefono",  "%$search%", "LIKE", "OR"],
        ["correo",  "%$search%", "LIKE", "OR"]
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
        'table_name' => "{$db_dti}_proveedores",
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') :
        //$query_invetariopn = addBranchOfficeOnInventory($request['id']);
        $id_proveedor = $request['id'];
        $origin = $_POST['origin'];
        $proveedores = getSupplierCatalog();

        $response = [
          'status'        => 'success',
          'toastMessage'  => 'El proveedor se agregó correctamente',
          'callback'      => '{
            load("' . $page . '", "' . $identifier . '");
            $(".supplier-catalog").html(`' . $proveedores . '`);
          }'
        ];

        if ($origin == "productos") {
          $response["callback"] = "{
            getCatalog({
              catalogSelector: '#id_proveedor',
              parameters: {
                action: 'get-suppliers',
                selectedValue: {$id_proveedor}
              }
            });
          }";

          $response["modalSelector"] = "#proveedores-modal";
        }
      endif;
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_proveedor = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_proveedores",
        'conditions' => [['id_proveedor', $id_proveedor]],
        "excluded_fields" => ["origin"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'El proveedor se actualizó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_proveedor = cleanStr($_POST['uid']);

      $query = "UPDATE {$db_dti}_proveedores SET
            status = 'eliminado'
          WHERE
            id_proveedor = {$id_proveedor}
        ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El proveedor se eliminó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
