<?php
require '../lib/settings.inc.php';
require '../lib/helpers/quotes.helper.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'cotizaciones-abiertas';
//$id_sucursal = getSessionBranchOfficeId();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');
    $fecha_hoy        = date('Y-m-d');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();

    //$fecha            = cleanStr($_POST['fecha']);

    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $status           = cleanStr($_POST['status']);

    // $is_expired        = $fecha_hoy > $row['fecha_expiracion'];

    $column_id              = "CTZ.id_cotizacion";
    $c_from                 = "{$db_dti}_cotizaciones AS CTZ";
    $c_extra_clauses        = "ORDER BY CTZ.id_cotizacion DESC";
    $quote_days_to_expired  = QUOTE_DAYS_TO_EXPIRED;

    $fields = [
      "CTZ.id_cotizacion",
      ["CTZ.id_cotizacion", "uid"],
      "CTZ.folio",
      "CTZ.id_usuario",
      "CTZ.id_sucursal",
      "CTZ.id_cliente",
      "CTZ.status",
      "CTZ.tipo",
      "CTZ.observaciones",
      "CTZ.subtotal",
      "CTZ.iva",
      "CTZ.ieps",
      "CTZ.redondeo",
      "CTZ.total",
      "CTZ.fecha_creacion",
      "CTZ.ediciones",
      ["DATE_FORMAT(CTZ.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      ["(DATE_ADD(CTZ.fecha_creacion, INTERVAL {$quote_days_to_expired} DAY))", "fecha_expiracion"],
      ["(DATE_FORMAT(DATE_ADD(CTZ.fecha_creacion, INTERVAL {$quote_days_to_expired} DAY), '%d-%m-%Y'))", "fecha_expiracion_format"],
      "S.nombre_sucursal",
      "U.nombre_completo",
      ["C.nombre_completo", "nombre_cliente"]
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_sucursales  AS S  ON (CTZ.id_sucursal = S.id_sucursal)
      LEFT JOIN {$db_ati}_usuarios    AS U  ON (CTZ.id_usuario  = U.id_usuario)
      LEFT JOIN {$db_dti}_clientes    AS C  ON (CTZ.id_cliente  = C.id_cliente)
    ";

    $c_where = [
      ["CTZ.tipo", "abierta"],
      ['CTZ.status', 'cancelado', '!=']
    ];

    if (!empty($search))      array_push($c_where, [
      [
        ["CTZ.folio",  "%$search%", "LIKE"],
        ["S.nombre_sucursal",  "%$search%", "LIKE", "OR"],
        ["U.nombre_completo",  "%$search%", "LIKE", "OR"],
        ["C.nombre_completo",  "%$search%", "LIKE", "OR"],
        ["CTZ.total",  "%$search%", "LIKE", "OR"],
      ]
    ]);

    if (!empty($id_sucursal)) array_push($c_where, ["CTZ.id_sucursal",  "{$id_sucursal}"]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(CTZ.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(CTZ.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(CTZ.fecha_creacion, '%d-%m-%Y'))", $fecha_fin]);

    // Calcular la fecha de vigencia con la fecha de hoy
    if ($status === "expirado") array_push($c_where, [$fecha_hoy, "(DATE_ADD(CTZ.fecha_creacion, INTERVAL {$quote_days_to_expired} DAY))", ">"]);
    if ($status === "vigente")  array_push($c_where, ["(DATE_ADD(CTZ.fecha_creacion, INTERVAL {$quote_days_to_expired} DAY))", $fecha_hoy, ">"]);

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

  /* case 'action-cancelar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'cancelar')) :
      $id_cotizacion     = cleanStr($_POST['uid']);
      $id_usuario   = get_id_usuario();
      $data_usuario = getUserData($id_usuario);

      $query = "SELECT
          VP.id_cotizacion_producto,
          VP.id_cotizacion,
          VP.id_producto,
          VP.cantidad,
          VP.cancelado,
          CTZ.id_sucursal,
          I.stock,
          P.nombre_producto
        FROM
          {$db_dti}_cotizacion_productos AS CTZP
        LEFT JOIN
          {$db_dti}_cotizaciones AS CTZ ON (VP.id_cotizacion = CTZ.id_cotizacion)
        LEFT JOIN
          {$db_dti}_inventario AS I ON (
            CTZ.id_sucursal  = I.id_sucursal AND
            VP.id_producto = I.id_producto
          )
        LEFT JOIN
          {$db_dti}_productos AS P ON (I.id_producto = P.id_producto)
        WHERE
          VP.id_cotizacion = ?
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param('i', $id_cotizacion);
      $stmt->execute();

      $query_result = $stmt->get_result();
      $num_rows     = $query_result->num_rows;

      if ($num_rows > 0) :
        while ($row = mysqli_fetch_assoc($query_result)) :
          addLogInKardex(
            $row['id_sucursal'],
            $row,
            ACCION_CANCELAR_VENTA . ' por ' . $data_usuario['nombre_completo'],
            TIPO_MOVIMIENTO_DECREMENTO,
            'venta'
          );
        endwhile;

        $query = "UPDATE {$db_dti}_cotizaciones SET
            status = 'cancelado'
          WHERE
            id_cotizacion = {$id_cotizacion}
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) $response = [
          'status'        => 'success',
          'toastMessage'  => 'La venta se canceló correctamente',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break; */

  case 'action-imprimir-ticket-' . $identifier:
    if (checkModuleActionPermission($identifier, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/pdf-cotizacion.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;

  case 'action-cerrar-cotizacion-' . $identifier:
    $uid        = $_POST['uid'];
    $date       = date('Y-m-d H:i:s');

    $query = "UPDATE {$db_dti}_cotizaciones SET
        tipo            = 'cerrada',
        fecha_creacion  = '{$date}'
      WHERE
        id_cotizacion = ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $uid);

    $result = $stmt->execute();

    if ($result) :
      $response = [
        'status'        => 'success',
        'toastMessage'  => "La cotización se cerró correctamente",
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;
};

//$response['post'] = json_encode($_POST);

echo json_encode($response);
mysqli_close($mysqli);
die;
