<?php
require '../lib/settings.inc.php';
require '../lib/cart.php';
require '../lib/helpers/cotizaciones.helpers.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'editar-cotizacion';

$id_producto      = cleanStr($_POST['itemId']);
$carrito_ssid     = SESSION_CARRITO_EDITAR_COTIZACION;
$id_sucursal      = cleanStr($_POST['id_sucursal']);
$tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;
$cantidad         = $_POST['quantity'] ? doubleval(cleanStr($_POST['quantity'])) : 1;

$TIPO_PIEZA       = 'Pieza';
$TIPO_A_GRANEL    = 'A granel';
$mxn_symbol       = 'MXN';

$cart_data        = $_SESSION[$carrito_ssid] ?? new stdClass();

$cart = new Cart(
  $id_sucursal,
  $id_producto,
  $cart_data,
  false
);

switch ($action) {
  case 'cart-load-' . $identifier:
    $list = $cart_data->list;

    include $identifier . '_carrito_table.php';
    die;
    break;

  case "cart-add-item-{$identifier}":
    $list = $cart_data->list;

    if ($list[$id_producto])  $cart->increase_product_quantity($cantidad);
    if (!$list[$id_producto]) $cart->add_item($cantidad);

    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$carrito_ssid] = $cart->get_cart();
    break;

  case "cart-update-item-quantity-{$identifier}";
    $cart->update_product_quantity($cantidad ?? 1);

    $response = $cart->get_alert();

    if ($response->status === 'success') :
      $_SESSION[$carrito_ssid] = $cart->get_cart();

      $product    = $cart->get_product();
      $cart_data  = $cart->get_cart();

      $importe        = number_format($product->sale_amount, DECIMALS_CURRENCY);
      $precio_venta   = number_format($product->sale_without_iva, DECIMALS_CURRENCY);

      $subtotal       = number_format($cart_data->sale_subtotal, DECIMALS_CURRENCY);
      $total_iva      = number_format($cart_data->sale_iva, DECIMALS_CURRENCY);
      $total          = number_format($cart_data->sale_total, DECIMALS_CURRENCY);

      $response->id_producto   = $id_producto;
      $response->importe       = '$' . $importe       . ' ' . $mxn_symbol;
      $response->subtotal      = '$' . $subtotal      . ' ' . $mxn_symbol;
      $response->total_iva     = '$' . $total_iva     . ' ' . $mxn_symbol;
      $response->total         = '$' . $total         . ' ' . $mxn_symbol;
      $response->precio_venta  = '$' . $precio_venta  . ' ' . $mxn_symbol;
    endif;
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

  case "cart-update-rounding-{$identifier}":
    $rounding = $_POST['rounding'] ? doubleval(cleanStr($_POST['rounding'])) : 0;

    $cart->update_cart_rounding($rounding);

    $response = $cart->get_alert();

    if ($response->status === 'success') :
      $cart_data = $cart->get_cart();

      $_SESSION[$carrito_ssid] = $cart_data;

      $total            = number_format($cart_data->sale_total, DECIMALS_CURRENCY);
      $response->total  = '$' . $total . ' ' . $mxn_symbol;
    endif;
    break;

  case "cart-remove-item-{$identifier}":
    $cart->remove_item();
    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$carrito_ssid] = $cart->get_cart();
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
      $cart_data        = $cart->get_cart();
      $list             = $cart_data->list;

      // $id_usuario       = get_id_usuario();
      $id_cotizacion    = cleanStr($_POST['id_cotizacion']);
      $folio            = get_quote_folio($id_sucursal);
      $carrito          = $_SESSION[$carrito_ssid];
      $id_cliente       = cleanStr($_POST['id_cliente']);
      $observaciones    = cleanStr($_POST['observaciones']);

      $subtotal         = $cart_data->sale_subtotal;
      $iva              = $cart_data->sale_iva;
      $redondeo         = $cart_data->sale_rounding;
      $total            = $cart_data->sale_total;
      $fecha            = date('Y-m-d H:i:s');

      if (isEmptyArray($list)) $response['toastMessage'] = 'El carrito está vacío';

      if (!isEmptyArray($list)) :
        $query = "UPDATE {$db_dti}_cotizaciones SET
            id_sucursal     = ?,
            id_cliente      = ?,
            observaciones   = ?,
            subtotal        = ?,
            iva             = ?,
            redondeo        = ?,
            total           = ?,
            fecha_creacion  = ?
          WHERE
            id_cotizacion = ?
        ";

        $stmt = $mysqli->prepare($query);

        $stmt->bind_param(
          'iisddddsi',
          $id_sucursal,
          $id_cliente,
          $observaciones,
          $subtotal,
          $iva,
          $redondeo,
          $total,
          $fecha,
          $id_cotizacion
        );

        $query_result = $stmt->execute();

        if ($query_result) :
          $query  = "DELETE FROM {$db_dti}_cotizacion_productos WHERE id_cotizacion = {$id_cotizacion}";
          $query_result = mysqli_query($mysqli, $query);

          if ($query_result) :
            foreach ($list as $key => $product) :
              $id_producto      = $product->id;
              $nombre           = $product->name;
              $cantidad         = $product->quantity;
              $aplica_iva       = $product->have_iva;
              $precio_original  = $product->origin_sale_price;
              $precio_venta     = $product->sale_without_iva;
              $cantidad_mayoreo = $product->wholesale_quantity;
              $precio_mayoreo   = $product->wholesale_price;
              $subtotal         = $product->sale_amount;
              $iva              = $product->sale_total_iva;
              $total            = $product->sale_amount_with_iva;

              $query = "INSERT INTO {$db_dti}_cotizacion_productos (
                  id_cotizacion,
                  id_producto,
                  nombre_producto,
                  cantidad,
                  aplica_iva,
                  precio_original,
                  precio_venta,
                  cantidad_mayoreo,
                  precio_mayoreo,
                  subtotal,
                  iva,
                  total
                ) VALUES (
                  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
              ";

              $stmt = $mysqli->prepare($query);

              $stmt->bind_param(
                'iisdsddddddd',
                $id_cotizacion,
                $id_producto,
                $nombre,
                $cantidad,
                $aplica_iva,
                $precio_original,
                $precio_venta,
                $cantidad_mayoreo,
                $precio_mayoreo,
                $subtotal,
                $iva,
                $total
              );

              $query_result = $stmt->execute();
            endforeach;

            $_SESSION[$carrito_ssid] = 0;
            unset($_SESSION[$carrito_ssid]);

            increase_quote_edit_counter($id_cotizacion);

            $response = [
              'status'        => 'success',
              'toastMessage'  => 'La cotización se actualizó correctamente'
            ];

            $pdf = BASE_URL . '/pdf-cotizacion.php?uid=' . $id_cotizacion;
            $response['pdf'] = $pdf;
          endif;
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
