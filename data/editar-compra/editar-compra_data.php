<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'editar-compra';

$carrito_ssid     = SESSION_CARRITO_EDITAR_COMPRA;
$id_almacen       = getStoreId();
$tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

$TIPO_PIEZA     = 'Pieza';
$TIPO_A_GRANEL  = 'A granel';

switch ($action) {
  case 'cart-load-' . $identifier:
    $carrito = $_SESSION[$carrito_ssid];

    include $identifier . '_carrito_table.php';
    die;
    break;

  case 'cart-add-item-' . $identifier:
    $id_producto    = cleanStr($_POST['itemId']);
    $cantidad       = $_POST['quantity'] ? cleanStr($_POST['quantity']) : 0;

    $data_producto  = getBranchOfficeProductData($id_almacen, $id_producto);
    $carrito        = $_SESSION[$carrito_ssid];

    if ($data_producto['unidad'] === 'Pieza' && !(fmod($cantidad, 1) == 0)) :
      $response['toastMessage'] = 'La cantidad para este producto no puede ser en decimales';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    if ($carrito[$id_producto]) :
      $carrito_producto_data  = $carrito[$id_producto];

      $cantidad_anterior      = $carrito_producto_data['cantidad'];
      $cantidad_nueva         = $cantidad_anterior + doubleval($cantidad);

      $carrito_producto_data['cantidad'] = $cantidad_nueva;
    endif;

    if (!$carrito[$id_producto]) :
      $carrito_producto_data = [
        'id_producto'     => $data_producto['id_producto'],
        'codigo'          => $data_producto['codigo'],
        'nombre_producto' => $data_producto['nombre_producto'],
        'contenido'       => $data_producto['contenido'],
        'stock'           => $data_producto['stock'],
        'cantidad'        => doubleval($cantidad),
        'precio_original' => $data_producto['precio_costo'],
        'precio_costo'    => $data_producto['precio_costo'],
        'unidad'          => $data_producto['unidad']
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

    $cantidad_nueva                     = doubleval($cantidad);
    $carrito_producto_data['cantidad']  = $cantidad_nueva;

    $carrito[$id_producto]              = $carrito_producto_data;
    $_SESSION[$carrito_ssid]            = $carrito;

    $precio_costo_final = ($cantidad_nueva * $carrito_producto_data['precio_costo']);
    $total              = getTotalInCart($carrito, 'precio_costo', 'cantidad');

    $response = [
      'status'              => 'success',
      'toastMessage'        => 'La cantidad se actualizó correctamente',
      'id_producto'         => $id_producto,
      'precio_costo_final'  => '$' . number_format($precio_costo_final, DECIMALS_CURRENCY),
      'total'               => 'Total: $' . number_format($total, DECIMALS_CURRENCY) . ' MXN'
    ];
    break;

  case 'cart-update-item-price-' . $identifier:
    $id_producto                            = cleanStr($_POST['itemId']);
    $precio_costo                           = $_POST['price'] ? cleanStr($_POST['price']) : 0;

    $carrito                                = $_SESSION[$carrito_ssid];
    $carrito_producto_data                  = $carrito[$id_producto];

    $precio_costo_nuevo                     = doubleval($precio_costo);
    $carrito_producto_data['precio_costo']  = $precio_costo_nuevo;

    $carrito[$id_producto]                  = $carrito_producto_data;
    $_SESSION[$carrito_ssid]                = $carrito;

    $precio_costo_final = ($precio_costo_nuevo * $carrito_producto_data['cantidad']);
    $total              = getTotalInCart($carrito, 'precio_costo', 'cantidad');

    $response = [
      'status'              => 'success',
      'toastMessage'        => 'El precio costo se actualizó correctamente',
      'id_producto'         => $id_producto,
      'precio_costo_final'  => '$' . number_format($precio_costo_final, DECIMALS_CURRENCY),
      'total'               => 'Total: $' . number_format($total, DECIMALS_CURRENCY) . ' MXN'
    ];
    break;

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
      $id_compra        = cleanStr($_POST['id_compra']);
      $folio            = get_purchase_folio($id_almacen);
      $carrito          = $_SESSION[$carrito_ssid];
      $tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

      $folio_documento  = cleanStr($_POST['folio_documento']);

      $fecha_documento  = cleanStr($_POST['fecha_documento']);
      $fecha_documento  = date('Y-m-d', strtotime($fecha_documento));

      $metodo_pago      = cleanStr($_POST['metodo_pago']);
      $forma_pago       = cleanStr($_POST['forma_pago']);
      $id_proveedor     = cleanStr($_POST['id_proveedor']);
      $observaciones    = cleanStr($_POST['observaciones']);

      if (isEmptyArray($carrito)) $response['toastMessage'] = 'El carrito está vacío';

      if (!isEmptyArray($carrito)) :
        foreach ($carrito as $key => $row) :
          $producto_total = $row['cantidad'] * $row['precio_costo'];

          if ($row['unidad'] === $TIPO_A_GRANEL) $producto_total = parsePricePerBulk($producto_total);

          $total = $total + $producto_total;
        endforeach;

        $query = "UPDATE {$db_dti}_compras SET
            status = 'cancelado'
          WHERE
            id_compra = {$id_compra}
        ";

        $query_result = mysqli_query($mysqli, $query);

        if (!$query_result) :
          echo json_encode($response);
          mysqli_close($mysqli);
          die;
        endif;

        $query = "UPDATE {$db_dti}_compras SET
            id_usuario      = {$id_usuario},
            id_sucursal     = {$id_almacen},
            id_proveedor    = {$id_proveedor},
            folio_documento = '{$folio_documento}',
            fecha_documento = '{$fecha_documento}',
            metodo_pago     = '{$metodo_pago}',
            forma_pago      = '{$forma_pago}',
            observaciones   = '{$observaciones}',
            tipo            = '{$tipo_movimiento}',
            total           = {$total}
          WHERE
            id_compra = {$id_compra}
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) :
          $data_sucursal  = getBranchOfficeData($id_almacen);
          $accion         = ACCION_NUEVA_COMPRA . " en {$data_sucursal['nombre_sucursal']}";

          foreach ($carrito as $key => $row) :
            $total = $row['precio_costo'] * $row['cantidad'];

            if ($row['unidad'] === $TIPO_A_GRANEL) $total = parsePricePerBulk($total);

            addLogInKardex(
              $id_almacen,
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

          $_SESSION[$carrito_ssid] = 0;
          unset($_SESSION[$carrito_ssid]);

          $response = [
            'status'        => 'success',
            'toastMessage'  => 'La compra se realizó correctamente'
          ];

          $ticket = BASE_URL . '/ticket-compra.php?uid=' . $id_compra;
          $response['ticket'] = $ticket;
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
