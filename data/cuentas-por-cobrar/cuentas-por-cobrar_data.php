<?php
require '../lib/settings.inc.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action      = $_POST['action'];
$identifier  = 'cuentas-por-cobrar';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions = haveActions($identifier, 'tabla');

    $per_page   = $_POST['perPage'];
    $page       = $_POST['page'];

    $search     = cleanStr($_POST['search']);
    $branchId   = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();

    $column_id        = "V.id_venta";
    $c_from           = "{$db_dti}_ventas AS V";
    $c_extra_clauses  = "
      GROUP BY
        C.id_cliente
      ORDER BY
        V.id_venta
      DESC
    ";

    $fields = [
      ["V.id_venta", "uid"],
      "V.*",
      ["C.nombre_completo", "cliente_nombre"],
      ["C.telefono", "cliente_telefono"]
    ];

    $c_join     = "
      LEFT JOIN {$db_dti}_clientes AS C ON C.id_cliente = V.id_cliente
    ";

    $c_where    = [
      ["V.forma_pago", "credito"],
      ["V.pagado", "no"],
      ["V.status", "activo"]
    ];

    if ($branchId) array_push($c_where, ["V.id_sucursal", $branchId]);

    if (!empty($search)) array_push($c_where, [[
      ["C.nombre_completo", "%$search%", "LIKE"]
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
}

echo json_encode($response);
mysqli_close($mysqli);
die;
