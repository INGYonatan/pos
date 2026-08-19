<?php
require '../lib/settings.inc.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'compras';
//$id_sucursal = getSessionBranchOfficeId();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();
    // $fecha            = cleanStr($_POST['fecha']);

    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $status           = cleanStr($_POST['status']);

    $column_id        = "C.id_compra";
    $c_from           = "{$db_dti}_compras AS C";
    $c_extra_clauses  = "ORDER BY C.id_compra DESC";

    $fields = [
      "C.id_compra",
      ["C.id_compra", "uid"],
      "C.folio",
      "C.id_usuario",
      "C.id_proveedor",
      "C.id_sucursal",
      "C.folio_documento",
      "C.folio_orden_compra",
      "C.fecha_documento",
      "C.metodo_pago",
      "C.forma_pago",
      "C.status",
      "C.tipo",
      "C.observaciones",
      "C.subtotal",
      "C.iva",
      "C.ieps",
      "C.total",
      "C.fecha_creacion",
      ["DATE_FORMAT(C.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      "S.nombre_sucursal",
      "U.nombre_completo"
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_sucursales  AS S  ON (C.id_sucursal = S.id_sucursal)
      LEFT JOIN {$db_ati}_usuarios    AS U  ON (C.id_usuario  = U.id_usuario)
    ";

    $c_where = [];

    $filtersSearch = [
      ["C.folio",  "%$search%", "LIKE"],
      ["C.folio_documento",  "%$search%", "LIKE", "OR"],
      ["C.folio_orden_compra",  "%$search%", "LIKE", "OR"],
      ["U.nombre_completo",  "%$search%", "LIKE", "OR"],
      ["C.total",  "%$search%", "LIKE", "OR"]
    ];

    if ($IS_ADMIN) $filtersSearch[] = ["S.nombre_sucursal",  "%$search%", "LIKE", "OR"];

    if (!empty($search))      array_push($c_where, [$filtersSearch]);
    if (!empty($id_sucursal)) array_push($c_where, ["C.id_sucursal",  "{$id_sucursal}"]);

    // if (!empty($fecha))       array_push($c_where, ["(DATE_FORMAT(C.fecha_creacion, '%d-%m-%Y'))",  "{$fecha}"]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(C.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(C.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(C.fecha_creacion, '%d-%m-%Y'))", $fecha_fin]);

    if (!empty($status)) array_push($c_where, ["C.status", $status]);

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

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';
    die;
    break;

  case 'action-cancelar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'cancelar')) :
      $id_compra     = cleanStr($_POST['uid']);
      $id_usuario   = get_id_usuario();
      $data_usuario = getUserData($id_usuario);

      $query = "SELECT
          CP.id_compra_producto,
          CP.id_compra,
          CP.id_producto,
          CP.cantidad,
          CP.cancelado,
          C.id_sucursal,
          I.stock,
          P.nombre_producto
        FROM
          {$db_dti}_compra_productos AS CP
        LEFT JOIN
          {$db_dti}_compras AS C ON (CP.id_compra = C.id_compra)
        LEFT JOIN
          {$db_dti}_inventario AS I ON (
            C.id_sucursal  = I.id_sucursal AND
            CP.id_producto = I.id_producto
          )
        LEFT JOIN
          {$db_dti}_productos AS P ON (I.id_producto = P.id_producto)
        WHERE
          CP.id_compra = ?
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param('i', $id_compra);
      $stmt->execute();

      $query_result = $stmt->get_result();
      $num_rows     = $query_result->num_rows;

      if ($num_rows > 0) :
        while ($row = mysqli_fetch_assoc($query_result)) :
          addLogInKardex(
            $row['id_sucursal'],
            $row,
            ACCION_CANCELAR_COMPRA . ' por ' . $data_usuario['nombre_completo'],
            TIPO_MOVIMIENTO_DECREMENTO,
            'compra'
          );
        endwhile;

        $query = "UPDATE {$db_dti}_compras SET
            status = 'cancelado'
          WHERE
            id_compra = {$id_compra}
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) $response = [
          'status'        => 'success',
          'toastMessage'  => 'La compra se canceló correctamente',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_compra = cleanStr($_POST['uid']);

      $fecha_documento  = cleanStr($_POST['fecha_documento']);
      $fecha_documento  = date('Y-m-d', strtotime($fecha_documento));

      $request = useUpdateByPost([
        'table_name'      => "{$db_dti}_compras",
        'excluded_fields' => ['fecha_documento'],
        'extra_fields'    => ['fecha_documento' => $fecha_documento],
        'conditions'      => [['id_compra', $id_compra]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => '!La compra se actualizó correctamente!',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-imprimir-ticket-' . $identifier:
    if (checkModuleActionPermission($identifier, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/ticket-compra.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;
};

//$response['post'] = json_encode($_POST);

echo json_encode($response);
mysqli_close($mysqli);
die;
