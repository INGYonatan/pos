<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'inventario-ajustes-historial';

$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');
$carpeta_imagenes       = '../../../src/assets/images/blogs/';
$id_almacen            = getStoreId();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    // $fecha            = cleanStr($_POST['fecha']);
    $id_sucursal      = cleanStr($_POST['id_sucursal']);

    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $status           = cleanStr($_POST['status']);

    $column_id        = "IA.id_inventario_ajuste";
    $c_from           = "{$db_dti}_inventario_ajustes AS IA";
    $c_extra_clauses  = "ORDER BY IA.id_inventario_ajuste DESC";

    $fields = [
      "IA.id_inventario_ajuste",
      ["IA.id_inventario_ajuste", "uid"],
      "IA.id_sucursal",
      "IA.observaciones",
      "IA.status",
      "IA.tipo",
      "IA.fecha_creacion",
      "IA.folio",
      "IA.tipo_ajuste",
      ["DATE_FORMAT(IA.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      "S.nombre_sucursal"
    ];

    $c_join = "
        LEFT JOIN {$db_dti}_sucursales AS S ON (IA.id_sucursal = S.id_sucursal)
    ";

    $c_where = [
      /* ["id_sucursal", $id_almacen] */];

    if (!empty($search)) array_push($c_where, [[
      ["IA.observaciones",  "%$search%", "LIKE"],
      ["IA.folio",  "%$search%", "LIKE", "OR"],
    ]]);
    //if (!empty($fecha)) array_push($c_where, ["(DATE_FORMAT(IA.fecha_creacion, '%d-%m-%Y'))",  $fecha]);
    if (!empty($id_sucursal)) array_push($c_where, ["IA.id_sucursal", $id_sucursal]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(IA.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(IA.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(IA.fecha_creacion, '%d-%m-%Y'))", $fecha_fin]);

    if (isset($status)) array_push($c_where, ["IA.status", $status]);

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
        'table_name' => "{$db_dti}_inventario_ajustes"
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') :
        $query_invetariopn = addBranchOfficeOnInventory($request['id']);

        $response = [
          'status'        => 'success',
          'toastMessage'  => 'La sucursal se agregó correctamente',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;

  case 'action-cancelar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'cancelar')) :
      $id_usuario           = get_id_usuario();
      $data_usuario         = getUserData($id_usuario);
      $id_inventario_ajuste = cleanStr($_POST['uid']);

      $query = "SELECT
          IAP.id_inventario_ajuste_producto,
          IAP.id_inventario_ajuste,
          IAP.id_producto,
          IAP.cantidad,
          IAP.cancelado,
          IA.id_sucursal,
          IA.tipo,
          I.stock,
          P.nombre_producto
        FROM
          {$db_dti}_inventario_ajuste_productos AS IAP
        LEFT JOIN
          {$db_dti}_inventario_ajustes AS IA ON (IAP.id_inventario_ajuste = IA.id_inventario_ajuste)
        LEFT JOIN
          {$db_dti}_inventario AS I ON (
            IA.id_sucursal  = I.id_sucursal AND
            IAP.id_producto = I.id_producto
          )
        LEFT JOIN
          {$db_dti}_productos AS P ON (I.id_producto = P.id_producto)
        WHERE
          IAP.id_inventario_ajuste = ?
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param('i', $id_inventario_ajuste);
      $stmt->execute();

      $query_result = $stmt->get_result();
      $num_rows     = $query_result->num_rows;

      if ($num_rows > 0) :
        while ($row = mysqli_fetch_assoc($query_result)) :
          addLogInKardex(
            $row['id_sucursal'],
            $row,
            ACCION_CANCELAR_AJUSTE . ' por ' . $data_usuario['nombre_completo'],
            TIPO_MOVIMIENTO_DECREMENTO,
            'ajuste'
          );
        endwhile;

        $query = "UPDATE {$db_dti}_inventario_ajustes SET
            status = 'cancelado'
          WHERE
            id_inventario_ajuste = {$id_inventario_ajuste}
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) :
          $response = [
            'status'        => 'success',
            'toastMessage'  => 'El ajuste se canceló correctamente',
            'callback'      => 'load("' . $page . '", "' . $identifier . '");'
          ];
        endif;
      endif;
    endif;
    break;

  case 'action-imprimir-ticket-' . $identifier:
    if (checkModuleActionPermission($identifier, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/ticket-ajuste.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
