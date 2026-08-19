<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action       = $_POST['action'];
$identifier   = 'pos';
$carrito_ssid = SESSION_CARRITO_POS;

$id_cliente   = cleanStr($_POST['id_cliente']);
$id_vendedor  = cleanStr($_POST['id_vendedor']);
$id_sucursal  = getSessionBranchOfficeId();

$data_usuario = getUserData(get_id_usuario());

if ($data_usuario['IS_ADMIN'] === 'si') $id_sucursal = cleanStr($_POST['id_sucursal']);

switch ($action) {
  case 'load-' . $identifier:
    $per_page         = 9;
    $page             = $_POST['page'];
    $search           = cleanStr($_POST['search']);

    $column_id        = "I.id_inventario";
    $c_from           = "{$db_dti}_inventario AS I";
    $query_count      = "SELECT SUM(cantidad) FROM {$db_dti}_venta_productos AS VP WHERE VP.id_producto = I.id_producto";
    $c_extra_clauses  = "ORDER BY ({$query_count}) DESC";

    $fields = [
      "I.id_inventario",
      ["I.id_inventario", "uid"],
      "I.id_producto",
      "I.stock",
      "P.codigo",
      "P.nombre_producto",
      "P.contenido",
      "P.precio_venta",
      "P.fecha_creacion",
      "P.unidad"
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_productos AS P ON (I.id_producto = P.id_producto)
    ";

    $c_where = [
      ["I.id_sucursal", $id_sucursal]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["P.codigo",  "$search"],
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

    //echo getEmptyTableMessage($request);
    //die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';
    die;
    break;

  case 'cart-load-' . $identifier:
    $carrito = $_SESSION[$carrito_ssid];

    include $identifier . '_carrito_table.php';
    die;
    break;

  case 'cart-add-item-' . $identifier:
    $id_producto            = cleanStr($_POST['itemId']);
    $cantidad               = $_POST['quantity'] ? cleanStr($_POST['quantity']) : 1;

    $data_producto          = getBranchOfficeProductData($id_sucursal, $id_producto);
    $carrito                = $_SESSION[$carrito_ssid];

    if ($data_producto['stock'] == 0) :
      $response['toastMessage'] = 'No hay productos en stock';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    if ($carrito[$id_producto]) :
      $carrito_producto_data              = $carrito[$id_producto];
      $cantidad_anterior                  = $carrito_producto_data['cantidad'];
      $cantidad_nueva                     = $cantidad_anterior + intval($cantidad);

      if ($cantidad_nueva > $data_producto['stock']) :
        $response['toastMessage'] = 'No hay productos suficientes en stock';

        echo json_encode($response);
        mysqli_close($mysqli);
        die;
      endif;

      $carrito_producto_data['cantidad']  = $cantidad_nueva;
    endif;

    if (!$carrito[$id_producto]) :
      if ($cantidad > $data_producto['stock']) :
        $response['toastMessage'] = 'No hay productos suficientes en stock';

        echo json_encode($response);
        mysqli_close($mysqli);
        die;
      endif;

      $carrito_producto_data = [
        'id_producto'         => $data_producto['id_producto'],
        'codigo'              => $data_producto['codigo'],
        'nombre_producto'     => $data_producto['nombre_producto'],
        'contenido'           => $data_producto['contenido'],
        'stock'               => $data_producto['stock'],
        'cantidad'            => intval($cantidad),
        'precio_venta'        => $data_producto['precio_venta']
      ];
    endif;

    $carrito[$id_producto]    = $carrito_producto_data;
    $_SESSION[$carrito_ssid]  = $carrito;

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'El producto se agregó correctamente'
    ];
    break;

  case 'cart-update-item-quantity-' . $identifier:
    $id_producto            = cleanStr($_POST['itemId']);
    $cantidad               = $_POST['quantity'] ? intval(cleanStr($_POST['quantity'])) : 1;
    $carrito                = $_SESSION[$carrito_ssid];
    $carrito_producto_data  = $carrito[$id_producto];
    $data_producto          = getBranchOfficeProductData($id_sucursal, $id_producto);

    if ($cantidad > $data_producto['stock']) :
      $response['toastMessage'] = 'No hay productos suficientes en stock';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    $carrito_producto_data['cantidad']  = $cantidad;
    $carrito[$id_producto]              = $carrito_producto_data;
    $_SESSION[$carrito_ssid]            = $carrito;

    $carrito_total = 0;

    foreach ($carrito as $key => $row) :
      $producto_total = $row['cantidad'] * $row['precio_venta'];
      $carrito_total  = $carrito_total + $producto_total;
    endforeach;

    $response = [
      'status'                => 'success',
      'toastMessage'          => 'La cantidad se actualizó correctamente',
      'id_producto'           => $id_producto,
      'total'                 => '$' . number_format(($cantidad * $carrito_producto_data['precio_venta']), DECIMALS_CURRENCY),
      'carrito_total'         => 'Total: $' . number_format($carrito_total, DECIMALS_CURRENCY) . ' MXN',
      'carrito_total_number'  => $carrito_total
    ];
    break;

  case 'cart-remove-item-' . $identifier:
    $id_producto                      = cleanStr($_POST['itemId']);
    $carrito                          = $_SESSION[$carrito_ssid];
    unset($carrito[$id_producto]);
    $_SESSION[$carrito_ssid]          = $carrito;

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'El producto se removió correctamente'
    ];
    break;

  case 'cart-clean-cart-' . $identifier:
    unset($_SESSION[$carrito_ssid]);

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'El carrito se vació correctamente'
    ];
    break;

  case 'cart-action-verificar-producto-' . $identifier:
    $codigo_producto  = cleanStr($_POST['actionValue']);
    $id_producto      = getProductIdByCode($codigo_producto);

    if (empty($id_producto)) $response = [
      'status'        => 'error',
      'type'          => 'lector',
      'toastMessage'  => 'El producto no existe.'
    ];

    if (!empty($id_producto)) $response = [
      'status'      => 'success',
      'type'        => 'lector',
      'id_producto' => $id_producto
    ];
    break;

  case 'cart-save-cart-' . $identifier:
    $carrito          = $_SESSION[$carrito_ssid];
    $tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;
    $observaciones    = cleanStr($_POST['observaciones']);
    $pago_con         = cleanStr($_POST['pago_con']);
    $cambio           = cleanStr($_POST['cambio']);
    $folio            = get_sale_folio($id_sucursal);

    if (isEmptyArray($carrito)) $response['toastMessage'] = 'El carrito está vacío';

    if (!isEmptyArray($carrito)) :
      $total = 0;

      foreach ($carrito as $key => $row) :
        $total = $total + ($row['precio_venta'] * $row['cantidad']);
      endforeach;

      if ($pago_con < $total) $response['toastMessage'] = 'El pago no está completo';

      if ($pago_con >= $total) :
        $query = "INSERT INTO {$db_dti}_ventas (
            id_usuario,
            id_sucursal,
            id_cliente,
            folio,
            tipo,
            observaciones,
            total,
            pago_con,
            cambio
          ) VALUES (
            {$id_vendedor},
            {$id_sucursal},
            {$id_cliente},
            '{$folio}',
            '{$tipo_movimiento}',
            '{$observaciones}',
            {$total},
            {$pago_con},
            {$cambio}
          )
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) :
          $id_venta = mysqli_insert_id($mysqli);

          foreach ($carrito as $key => $row) :
            $total = $row['precio_venta'] * $row['cantidad'];

            $query_detalles = "INSERT INTO {$db_dti}_venta_productos (
                id_venta,
                id_producto,
                nombre_producto,
                cantidad,
                precio_venta,
                total
              ) VALUES (
                {$id_venta},
                {$row['id_producto']},
                '{$row['nombre_producto']}',
                {$row['cantidad']},
                {$row['precio_venta']},
                {$total}
              )
            ";

            $query_detalles_result = mysqli_query($mysqli, $query_detalles);

            $data_sucursal = getBranchOfficeData($id_sucursal);
            $data_vendedor = getUserData($id_vendedor);

            addLogInKardex(
              $id_sucursal,
              $row,
              ACCION_VENTA . " en sucursal {$data_sucursal['nombre_sucursal']} por {$data_vendedor['nombre_completo']}",
              $tipo_movimiento,
              'venta',
              $id_vendedor
            );
          endforeach;

          $_SESSION[$carrito_ssid] = 0;
          unset($_SESSION[$carrito_ssid]);

          $response = [
            'status'        => 'success',
            'toastMessage'  => 'La venta se realizó correctamente',
            'ticket'        => BASE_URL . '/ticket-venta.php?uid=' . $id_venta
          ];
        endif;
      endif;
    endif;
    break;

  case 'cart-action-corte-caja-' . $identifier:
    if (checkModuleActionPermission($identifier, 'corte-caja')) :
      $id_usuario = get_id_usuario();

      $query = "SELECT
          SUM(total) AS total
        FROM
          {$db_dti}_ventas
        WHERE
          id_sucursal = {$id_sucursal} AND
          corte       = 'no'
      ";

      $query_result = mysqli_query($mysqli, $query);
      $data_ventas  = mysqli_fetch_assoc($query_result);
      $total        = $data_ventas['total'];

      if ($total == 0) $response['toastMessage'] = 'No hay ventas realizadas.';

      if ($total > 0) :
        $folio        = get_cash_register_folio($id_sucursal);
        $fecha_desde  = get_last_date_cash_register($id_sucursal);

        $query_corte = "INSERT INTO {$db_dti}_cortes_caja (
            id_usuario,
            id_sucursal,
            folio,
            total,
            fecha_desde
          ) VALUES (
            {$id_usuario},
            {$id_sucursal},
            '{$folio}',
            {$total},
            '{$fecha_desde}'
          )
        ";

        $query_corte_result = mysqli_query($mysqli, $query_corte);

        if ($query_corte_result) :
          $id_corte_caja = mysqli_insert_id($mysqli);

          $query_update = "UPDATE {$db_dti}_ventas SET
              corte = 'si'
            WHERE
              id_sucursal = {$id_sucursal} AND
              corte       = 'no'
          ";

          $query_update_result = mysqli_query($mysqli, $query_update);

          if ($query_update_result) $response = [
            'status'        => 'success',
            'toastMessage'  => 'El corte del día se realizó correctamente.',
            'ticket'        => BASE_URL . '/ticket-corte.php?uid=' . $id_corte_caja
          ];
        endif;
      endif;
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
