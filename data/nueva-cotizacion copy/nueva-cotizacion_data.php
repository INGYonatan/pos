<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'nueva-cotizacion';

$carrito_ssid     = SESSION_CARRITO_NUEVA_COTIZACION;
$id_sucursal      = cleanStr($_POST['id_sucursal']);
$tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

$TIPO_PIEZA     = 'Pieza';
$TIPO_A_GRANEL  = 'A granel';
$mxn_symbol     = 'MXN';

switch ($action) {
  case 'cart-load-' . $identifier:
    $carrito = $_SESSION[$carrito_ssid];

    include $identifier . '_carrito_table.php';
    die;
    break;

  case 'cart-add-item-' . $identifier:
    $id_producto    = cleanStr($_POST['itemId']);
    $cantidad       = $_POST['quantity'] ? cleanStr($_POST['quantity']) : 0;

    $carrito        = $_SESSION[$carrito_ssid];
    $data_producto  = getBranchOfficeProductData($id_sucursal, $id_producto);

    if ($data_producto['unidad'] === 'Pieza' && !(fmod($cantidad, 1) == 0)) :
      $response['toastMessage'] = 'La cantidad para este producto no puede ser en decimales';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    /* if ($data_producto['stock'] == 0) :
      $response['toastMessage'] = 'No hay productos en stock';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif; */

    if ($carrito[$id_producto]) :
      $carrito_producto_data                  = $carrito[$id_producto];

      $cantidad_anterior                      = $carrito_producto_data['cantidad'];
      $cantidad_nueva                         = $cantidad_anterior + doubleval($cantidad);

      $carrito_producto_data['cantidad']      = $cantidad_nueva;

      $carrito_producto_data                  = parseProductPrice($carrito_producto_data, $cantidad_nueva, 'precio_venta');
      $carrito_producto_data['precio_venta']  = $carrito_producto_data['price_to_use'];
    endif;

    if (!$carrito[$id_producto]) :
      $data_producto = parseProductPrice($data_producto, $cantidad, 'precio_venta');

      $carrito_producto_data = [
        'id_producto'       => $data_producto['id_producto'],
        'codigo'            => $data_producto['codigo'],
        'nombre_producto'   => $data_producto['nombre_producto'],
        'contenido'         => $data_producto['contenido'],
        'stock'             => $data_producto['stock'],
        'cantidad'          => doubleval($cantidad),
        'unidad'            => $data_producto['unidad'],
        'precio_original'   => $data_producto['precio_venta'],
        'precio_venta'      => $data_producto['price_to_use'],
        'aplica_iva'        => $data_producto['aplica_iva'],
        'cantidad_mayoreo'  => $data_producto['cantidad_mayoreo'],
        'precio_mayoreo'    => $data_producto['precio_mayoreo']
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
    $id_producto                        = cleanStr($_POST['itemId']);
    $cantidad                           = $_POST['quantity'] ? cleanStr($_POST['quantity']) : 0;
    $carrito                            = $_SESSION[$carrito_ssid];
    $carrito_producto_data              = $carrito[$id_producto];

    if ($carrito_producto_data['unidad'] === 'Pieza' && !(fmod($cantidad, 1) == 0)) :
      $response['toastMessage'] = 'La cantidad para este producto no puede ser en decimales';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    $carrito_producto_data                  = parseProductPrice($carrito_producto_data, $cantidad, 'precio_venta');
    $carrito_producto_data['precio_venta']  = $carrito_producto_data['price_to_use'];

    $cantidad_nueva                     = doubleval($cantidad);
    $carrito_producto_data['cantidad']  = $cantidad_nueva;

    $carrito[$id_producto]              = $carrito_producto_data;
    $_SESSION[$carrito_ssid]            = $carrito;

    $totales        = obtener_totales_carrito($carrito, 'precio_venta');
    $data_producto  = parseCartProduct($carrito_producto_data, 'precio_venta');

    $importe        = number_format($data_producto->importe, DECIMALS_CURRENCY);
    $precio_venta   = number_format($data_producto->precio, DECIMALS_CURRENCY);

    $subtotal       = number_format($totales->subtotal, DECIMALS_CURRENCY);
    $total_iva      = number_format($totales->total_iva, DECIMALS_CURRENCY);
    $total          = number_format($totales->total, DECIMALS_CURRENCY);

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'La cantidad se actualizó correctamente',
      'id_producto'   => $id_producto,
      'importe'       => '$' . $importe . ' ' . $mxn_symbol,
      'subtotal'      => '$' . $subtotal . ' ' . $mxn_symbol,
      'total_iva'     => '$' . $total_iva . ' ' . $mxn_symbol,
      'total'         => '$' . $total . ' ' . $mxn_symbol,
      'precio_venta'  => '$' . $precio_venta . ' ' . $mxn_symbol
    ];
    break;

    /* case 'cart-update-item-price-' . $identifier:
    $id_producto                            = cleanStr($_POST['itemId']);
    $precio_venta                           = $_POST['price'] ? cleanStr($_POST['price']) : 0;

    $carrito                                = $_SESSION[$carrito_ssid];
    $carrito_producto_data                  = $carrito[$id_producto];

    $precio_venta_nuevo                     = doubleval($precio_venta);
    $carrito_producto_data['precio_venta']  = $precio_venta_nuevo;

    $carrito[$id_producto]                  = $carrito_producto_data;
    $_SESSION[$carrito_ssid]                = $carrito;

    $precio_venta_final = ($precio_venta_nuevo * $carrito_producto_data['cantidad']);
    $total              = getTotalInCart($carrito, 'precio_venta', 'cantidad');

    $response = [
      'status'              => 'success',
      'toastMessage'        => 'El precio costo se actualizó correctamente',
      'id_producto'         => $id_producto,
      'precio_venta_final'  => '$' . number_format($precio_venta_final, DECIMALS_CURRENCY),
      'total'               => 'Total: $' . number_format($total, DECIMALS_CURRENCY) . ' MXN'
    ];
    break; */

  case 'cart-remove-item-' . $identifier:
    $id_producto                      = cleanStr($_POST['itemId']);
    $carrito                          = $_SESSION[$carrito_ssid];
    unset($carrito[$id_producto]);
    $_SESSION[$carrito_ssid]  = $carrito;

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

  case 'cart-save-cart-' . $identifier:
    try {
      $id_usuario       = get_id_usuario();
      $folio            = get_quote_folio($id_sucursal);
      $carrito          = $_SESSION[$carrito_ssid];
      $tipo             = cleanStr($_POST['tipo']);
      $id_cliente       = cleanStr($_POST['id_cliente']);
      $observaciones    = cleanStr($_POST['observaciones']);

      if (isEmptyArray($carrito)) $response['toastMessage'] = 'El carrito está vacío';

      if (!isEmptyArray($carrito)) :
        foreach ($carrito as $key => $row) :
          $producto_total = $row['cantidad'] * $row['precio_venta'];

          if ($row['unidad'] === $TIPO_A_GRANEL) $producto_total = parsePricePerBulk($producto_total);

          $total = $total + $producto_total;
        endforeach;

        $query = "INSERT INTO {$db_dti}_cotizaciones (
            id_usuario,
            id_sucursal,
            id_cliente,
            folio,
            observaciones,
            tipo,
            total
          ) VALUES (
            {$id_usuario},
            {$id_sucursal},
            {$id_cliente},
            '{$folio}',
            '{$observaciones}',
            '{$tipo}',
            {$total}
          )
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) :
          $id_cotizacion  = mysqli_insert_id($mysqli);
          $data_sucursal  = getBranchOfficeData($id_sucursal);
          $accion         = SESSION_CARRITO_NUEVA_COTIZACION . " en {$data_sucursal['nombre_sucursal']}";

          foreach ($carrito as $key => $row) :
            $total = $row['precio_venta'] * $row['cantidad'];

            if ($row['unidad'] === $TIPO_A_GRANEL) $total = parsePricePerBulk($total);

            $query_detalles = "INSERT INTO {$db_dti}_cotizacion_productos (
                id_cotizacion,
                id_producto,
                nombre_producto,
                cantidad,
                precio_original,
                precio_venta,
                total
              ) VALUES (
                {$id_cotizacion},
                {$row['id_producto']},
                '{$row['nombre_producto']}',
                {$row['cantidad']},
                {$row['precio_original']},
                {$row['precio_venta']},
                {$total}
              )
            ";

            $query_detalles_result = mysqli_query($mysqli, $query_detalles);
          endforeach;

          $_SESSION[$carrito_ssid] = 0;
          unset($_SESSION[$carrito_ssid]);

          $response = [
            'status'        => 'success',
            'toastMessage'  => 'La cotización se realizó correctamente'
          ];

          $pdf = BASE_URL . '/pdf-cotizacion.php?uid=' . $id_cotizacion;
          $response['pdf'] = $pdf;
        endif;
      endif;
    } catch (Exception $e) {
      $response['toastMessage'] = $e->getMessage();
    }
    break;

  case 'cart-action-change-branch-office-' . $identifier:
    unset($_SESSION[$carrito_ssid]);

    $response = [
      'status' => 'success',
      //'toastMessage'  => 'La sucursal se cambió correctamente'
    ];
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
