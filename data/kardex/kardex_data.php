<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'kardex';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $id_producto      = cleanStr($_POST['id_producto']);
    $id_sucursal      = cleanStr($_POST['id_sucursal']);

    $column_id        = "K.id_kardex";
    $c_from           = "{$db_dti}_kardex AS K";
    $c_extra_clauses  = "ORDER BY K.id_kardex DESC";

    $fields = [
      "K.id_kardex",
      ["K.id_kardex", "uid"],
      "K.id_usuario",
      "K.id_sucursal",
      "K.id_producto",
      "K.nombre_producto",
      "K.cantidad",
      "K.accion",
      "K.existencia",
      "K.fecha_creacion",
      ["DATE_FORMAT(K.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      "U.nombre_completo",
      "S.nombre_sucursal",
      "P.unidad",
      "P.control_inventario"
    ];

    $c_join = "
      LEFT JOIN {$db_ati}_usuarios    AS U ON (K.id_usuario   = U.id_usuario)
      LEFT JOIN {$db_dti}_sucursales  AS S ON (K.id_sucursal  = S.id_sucursal)
      LEFT JOIN {$db_dti}_productos   AS P ON (K.id_producto  = P.id_producto)
    ";

    $c_where = [
      ["K.id_producto", $id_producto]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["K.nombre_producto",  "%$search%", "LIKE"],
        ["K.accion",  "%$search%", "LIKE", "OR"]
      ]
    ]);

    if (!empty($id_sucursal)) array_push($c_where, ["K.id_sucursal", $id_sucursal]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(K.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(K.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(K.fecha_creacion, '%d-%m-%Y'))", $fecha_fin]);

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
