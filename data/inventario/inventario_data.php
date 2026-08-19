<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'inventario';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = cleanStr($_POST['id_sucursal']);

    $brandId          = cleanStr($_POST['brandId']);
    $categoryId       = cleanStr($_POST['categoryId']);
    $familyId         = cleanStr($_POST['familyId']);
    $existenceMode    = cleanStr($_POST['existenceMode']);

    $column_id        = "P.id_producto";
    $c_from           = "{$db_dti}_productos AS P";
    $having_existence = "";
    if ($existenceMode == 'con-existencia') $having_existence = "HAVING COALESCE(SUM(I.stock), 0) > 0";
    if ($existenceMode == 'sin-existencia') $having_existence = "HAVING COALESCE(SUM(I.stock), 0) <= 0";

    $c_extra_clauses  = "GROUP BY P.id_producto {$having_existence} ORDER BY P.id_producto DESC";

    $by_id_sucursal = empty($id_sucursal) ? "1=1" : "id_sucursal = {$id_sucursal}";

    $query_existencia = "SELECT
        SUM(I.stock)
      FROM
        {$db_dti}_inventario AS I
      WHERE
        (I.id_producto = uid) AND
        ({$by_id_sucursal})
    ";

    $fields = [
      "P.id_producto",
      ["P.id_producto", "uid"],
      "P.codigo",
      "P.nombre_producto",
      "P.contenido",
      "P.precio_venta",
      "P.unidad",
      "P.control_inventario",
      ["({$query_existencia})", "existencia"]
    ];

    $c_join = "
      INNER JOIN {$db_dti}_inventario AS I ON I.id_producto = P.id_producto" .
      (!empty($id_sucursal) ? " AND I.id_sucursal = {$id_sucursal}" : "") . "
    ";

    $c_where = [
      ["P.status", "activo"]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["P.codigo",  "%$search%", "LIKE"],
        ["P.nombre_producto",  "%$search%", "LIKE", "OR"]
      ]
    ]);

    if ($brandId)     array_push($c_where, ["P.id_marca", $brandId]);
    if ($categoryId)  array_push($c_where, ["P.id_categoria", $categoryId]);
    if ($familyId)    array_push($c_where, ["P.id_categoria_familia", $familyId]);

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
    die;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
