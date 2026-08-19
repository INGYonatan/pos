<?php
require '../lib/settings.inc.php';
require '../lib/helpers/purchases.helper.php';
require '../lib/helpers/purchase-orders.helper.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'ordenes-compra';

$per_page         = $_POST['perPage'];
$page             = $_POST['page'] ? $_POST['page'] : 1;

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();
    // $fecha            = cleanStr($_POST['fecha']);

    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $status           = cleanStr($_POST['status']);

    $column_id        = "OC.id_orden_compra";
    $c_from           = "{$db_dti}_ordenes_compra AS OC";
    $c_extra_clauses  = "ORDER BY OC.id_orden_compra DESC";

    $fields = [
      "OC.id_orden_compra",
      ["OC.id_orden_compra", "uid"],
      "OC.folio",
      "OC.id_usuario",
      "OC.id_sucursal",
      "OC.id_proveedor",
      "OC.status",
      "OC.tipo",
      "OC.observaciones",
      "OC.subtotal",
      "OC.iva",
      "OC.ieps",
      "OC.total",
      "OC.fecha_creacion",
      ["DATE_FORMAT(OC.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      "S.nombre_sucursal",
      "U.nombre_completo"
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_sucursales  AS S  ON (OC.id_sucursal = S.id_sucursal)
      LEFT JOIN {$db_ati}_usuarios    AS U  ON (OC.id_usuario  = U.id_usuario)
    ";

    $c_where = [];

    $filters = [
      ["OC.folio",  "%$search%", "LIKE"],
      ["U.nombre_completo",  "%$search%", "LIKE", "OR"],
      ["OC.total",  "%$search%", "LIKE", "OR"]
    ];

    if ($IS_ADMIN) $filters[] = ["S.nombre_sucursal",  "%$search%", "LIKE", "OR"];

    if (!empty($search))      array_push($c_where, [$filters]);
    if (!empty($id_sucursal)) array_push($c_where, ["OC.id_sucursal",  "{$id_sucursal}"]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(OC.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(OC.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(OC.fecha_creacion, '%d-%m-%Y'))", $fecha_fin]);

    if (!empty($status)) array_push($c_where, ["OC.status", $status]);

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

  case "modal-action-{$identifier}-convertir-a-compra":
    return;
    $id_orden_compra  = cleanStr($_POST['uid']);

    $folio_documento  = cleanStr($_POST['folio_documento']);
    $fecha_documento  = cleanStr($_POST['fecha_documento']);
    $fecha_documento  = date('Y-m-d', strtotime($fecha_documento));
    $metodo_pago      = cleanStr($_POST['metodo_pago']);
    $forma_pago       = cleanStr($_POST['forma_pago']);
    $observaciones    = cleanStr($_POST['observaciones']);

    $orden_compra     = purchase_order_get_by_id($id_orden_compra);

    $id_sucursal      = $orden_compra->branch_id;
    $id_usuario       = get_id_usuario();
    $folio            = get_purchase_folio($id_sucursal);
    $carrito          = purchase_order_create_cart_edit($id_orden_compra);
    $tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

    if ($orden_compra) :
      $total = 0;

      foreach ($orden_compra->list as $key => $row) :
        $producto_total = $row->quantity * $row->cost_price;

        if ($row->unit === $TIPO_A_GRANEL) $producto_total = parsePricePerBulk($producto_total);

        $total = $total + $producto_total;
      endforeach;

      $query = "INSERT INTO {$db_dti}_compras (
          id_usuario,
          id_sucursal,
          id_proveedor,
          folio,
          folio_orden_compra,
          folio_documento,
          fecha_documento,
          metodo_pago,
          forma_pago,
          observaciones,
          tipo,
          total
        ) VALUES (
          {$id_usuario},
          {$id_sucursal},
          {$orden_compra->supplier_id},
          '{$folio}',
          '{$orden_compra->folio}',
          '{$folio_documento}',
          '{$fecha_documento}',
          '{$metodo_pago}',
          '{$forma_pago}',
          '{$observaciones}',
          '{$tipo_movimiento}',
          {$total}
        )
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) :
        $id_compra      = mysqli_insert_id($mysqli);
        $data_sucursal  = getBranchOfficeData($id_sucursal);
        $accion         = ACCION_ORDEN_COMPRA_A_COMPRA . " en {$data_sucursal['nombre_sucursal']} por {$data_usuario['nombre_completo']}";

        foreach ($carrito as $key => $row) :
          $total = $row['precio_costo'] * $row['cantidad'];

          if ($row['unidad'] === $TIPO_A_GRANEL) $total = parsePricePerBulk($total);

          addLogInKardex(
            $id_sucursal,
            $row,
            $accion,
            $tipo_movimiento,
            'compra'
          );

          $query_detalles = "INSERT INTO {$db_dti}_compra_productos (
              id_compra,
              id_producto,
              nombre_producto,
              cantidad,
              precio_original,
              precio_costo,
              total
            ) VALUES (
              {$id_compra},
              {$row['id_producto']},
              '{$row['nombre_producto']}',
              {$row['cantidad']},
              {$row['precio_original']},
              {$row['precio_costo']},
              {$total}
            )
          ";

          $query_detalles_result = mysqli_query($mysqli, $query_detalles);
        endforeach;

        //$_SESSION[$carrito_ssid] = 0;
        //unset($_SESSION[$carrito_ssid]);

        $query = "UPDATE {$db_dti}_ordenes_compra SET status = 'comprado' WHERE id_orden_compra = {$id_orden_compra}";
        mysqli_query($mysqli, $query);

        $ticket = BASE_URL . '/ticket-compra.php?uid=' . $id_compra;

        $response = [
          'status'        => 'success',
          'toastMessage'  => 'La compra se realizó correctamente',
          'callback'      => '{
            window.open("' . $ticket . '", "_blank");
            load(' . $page . ', "' . $identifier . '");
          }'
        ];
      endif;
    endif;
    break;

  case 'action-cancelar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'cancelar')) :
      $id_orden_compra     = cleanStr($_POST['uid']);
      $id_usuario   = get_id_usuario();
      $data_usuario = getUserData($id_usuario);

      $query = "UPDATE {$db_dti}_ordenes_compra SET
          status = 'cancelado'
        WHERE
          id_orden_compra = {$id_orden_compra}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La orden de compra se canceló correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];

    // $query = "SELECT
    //     CP.id_orden_compra_producto,
    //     CP.id_orden_compra,
    //     CP.id_producto,
    //     CP.cantidad,
    //     CP.cancelado,
    //     OC.id_sucursal,
    //     I.stock,
    //     P.nombre_producto
    //   FROM
    //     {$db_dti}_compra_productos AS CP
    //   LEFT JOIN
    //     {$db_dti}_ordenes_compra AS C ON (CP.id_orden_compra = OC.id_orden_compra)
    //   LEFT JOIN
    //     {$db_dti}_inventario AS I ON (
    //       OC.id_sucursal  = I.id_sucursal AND
    //       CP.id_producto = I.id_producto
    //     )
    //   LEFT JOIN
    //     {$db_dti}_productos AS P ON (I.id_producto = P.id_producto)
    //   WHERE
    //     CP.id_orden_compra = ?
    // ";

    // $stmt = $mysqli->prepare($query);

    // $stmt->bind_param('i', $id_orden_compra);
    // $stmt->execute();

    // $query_result = $stmt->get_result();
    // $num_rows     = $query_result->num_rows;

    // if ($num_rows > 0) :
    //   // while ($row = mysqli_fetch_assoc($query_result)) :
    //   //   addLogInKardex(
    //   //     $row['id_sucursal'],
    //   //     $row,
    //   //     ACCION_CANCELAR_COMPRA . ' por ' . $data_usuario['nombre_completo'],
    //   //     TIPO_MOVIMIENTO_DECREMENTO,
    //   //     'compra'
    //   //   );
    //   // endwhile;


    // endif;
    endif;
    break;

    /* case 'action-imprimir-ticket-' . $identifier:
    if (checkModuleActionPermission($identifier, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/ticket-compra.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break; */
};

//$response['post'] = json_encode($_POST);

echo json_encode($response);
mysqli_close($mysqli);
die;
