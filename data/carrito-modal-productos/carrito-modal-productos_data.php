<?php
require '../lib/settings.inc.php';
require '../lib/shopping-cart.php';
require '../lib/helpers/sales.helper.php';

$response               = new stdClass();
$response->status       = 'error';
$response->toastMessage = '¡Error inesperado!, intentalo nuevamente';

$action           = $_POST['action'];
$identifier       = 'carrito-modal-productos';

$product_id       = cleanStr($_POST['itemId']);
$branch_id        = cleanStr($_POST['branchId']) ? cleanStr($_POST['branchId']) : getSessionBranchOfficeId();
$quantity         = $_POST['quantity'] ? doubleval(cleanStr($_POST['quantity'])) : 1;

switch ($action):
  case "load-{$identifier}":
    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];
    $useLimitQuantity = $_POST['useLimitQuantity'];

    $search           = cleanStr($_POST['search']);
    $branch_data      = getBranchOfficeData($branch_id);

    $column_id        = "I.id_inventario";
    $c_from           = "{$db_dti}_inventario AS I";
    $c_extra_clauses  = "ORDER BY I.id_inventario DESC";

    $fields = [
      "I.id_inventario",
      ["I.id_inventario", "uid"],
      "I.id_producto",
      "I.stock",
      "P.codigo",
      "P.nombre_producto",
      "P.control_inventario"
    ];

    $c_join = "
      LEFT JOIN
        {$db_dti}_productos AS P ON (P.id_producto = I.id_producto)
    ";

    $c_where = [
      ["P.status", "activo"],
      ["I.id_sucursal", $branch_id]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["P.codigo",  "%$search%", "LIKE"],
        ["P.nombre_producto",  "%$search%", "LIKE", "OR"]
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

    # echo getEmptyTableMessage($request['query']);
    # die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';

    echo "
      <script>
        $('#{$identifier}-title').html('Productos de {$branch_data['nombre_sucursal']}');
      </script>
    ";
    die;
    break;
endswitch;

echo json_encode($response);
mysqli_close($mysqli);
die;
