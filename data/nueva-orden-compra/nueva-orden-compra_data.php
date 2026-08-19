<?php
require '../lib/settings.inc.php';
require '../lib/helpers/purchase-orders.helper.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'nueva-orden-compra';

$carrito_ssid     = SESSION_CARRITO_NUEVA_ORDEN_COMPRA;
$id_sucursal      = $_POST['id_sucursal'];
$tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

$TIPO_PIEZA     = 'Pieza';
$TIPO_A_GRANEL  = 'A granel';

function getPurchaseOrderCartTotalsWithIEPS($cart)
{
  $subtotal  = 0;
  $totalIeps = 0;
  $totalIva  = 0;
  $total     = 0;

  if (isEmptyArray($cart)) {
    return [
      'subtotal' => 0,
      'ieps'     => 0,
      'iva'      => 0,
      'total'    => 0
    ];
  }

  foreach ($cart as $row) {
    $quantity        = doubleval($row['cantidad'] ?? 0);
    $price           = doubleval($row['precio_costo'] ?? 0);
    $discount        = doubleval($row['descuento'] ?? 0);
    $haveIvaValue    = $row['aplica_iva'] ?? false;
    $haveIepsValue   = $row['aplica_ieps'] ?? false;
    $haveIva         = ($haveIvaValue === true || $haveIvaValue === 1 || $haveIvaValue === '1' || $haveIvaValue === 'si');
    $haveIeps        = ($haveIepsValue === true || $haveIepsValue === 1 || $haveIepsValue === '1' || $haveIepsValue === 'si');
    $ivaPercentage   = doubleval($row['iva_porcentaje'] ?? 16);
    $iepsPercentage  = doubleval($row['ieps_porcentaje'] ?? 0);

    $priceWithDiscount = $price - ($price * ($discount / 100));
    $iepsUnit          = $haveIeps ? ($priceWithDiscount * ($iepsPercentage / 100)) : 0;
    $ivaBaseUnit       = $priceWithDiscount + $iepsUnit;
    $ivaUnit           = $haveIva ? ($ivaBaseUnit * ($ivaPercentage / 100)) : 0;

    $subtotal  += $priceWithDiscount * $quantity;
    $totalIeps += $iepsUnit * $quantity;
    $totalIva  += $ivaUnit * $quantity;
    $total     += ($priceWithDiscount + $iepsUnit + $ivaUnit) * $quantity;
  }

  return [
    'subtotal' => $subtotal,
    'ieps'     => $totalIeps,
    'iva'      => $totalIva,
    'total'    => $total
  ];
}

switch ($action) {
  case 'cart-load-' . $identifier:
    $carrito = $_SESSION[$carrito_ssid];

    include $identifier . '_carrito_table.php';
    die;
    break;

  case 'cart-add-item-' . $identifier:
    $id_producto    = cleanStr($_POST['itemId']);
    $cantidad       = $_POST['quantity'] ? cleanStr($_POST['quantity']) : 0;

    $data_producto  = getBranchOfficeProductData($id_sucursal, $id_producto);
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
      $id             = $data_producto['id_producto'];
      $code           = $data_producto['codigo'];
      $name           = $data_producto['nombre_producto'];
      $stock          = $data_producto['stock'];
      $quantity       = doubleval($cantidad);
      $have_iva       = $data_producto['aplica_iva'] == 'si' ? true : false;
      $have_ieps      = ($data_producto['aplica_ieps'] ?? 'no') == 'si' ? true : false;
      $iva_percentaje = $have_iva ? 16 : 0;
      $ieps_percentage = $have_ieps ? doubleval($data_producto['ieps_porcentaje'] ?? 0) : 0;
      $discount       = 0;
      $discount_limit = $data_producto['limite_descuento'];
      $price          = doubleval($data_producto['precio_costo_original'] ?? 0);
      if ($price <= 0) {
        $price = $have_iva ? getPriceWithoutIVA($data_producto['precio_costo']) : doubleval($data_producto['precio_costo']);
      }
      $entry_unit     = $data_producto['unidad_entrada'] === 'caja' ? 'caj.' : 'pzs.';
      $pieces_number  = $data_producto["numero_piezas"];
      $type           = $data_producto['tipo'];
      $serial_numbers = [];

      $carrito_producto_data = [
        'id_producto'       => $id,
        'codigo'            => $code,
        'nombre_producto'   => $name,
        'stock'             => $stock,
        'cantidad'          => $quantity,
        'aplica_iva'        => $have_iva,
        'aplica_ieps'       => $have_ieps,
        'iva_porcentaje'    => $iva_percentaje,
        'ieps_porcentaje'   => $ieps_percentage,
        'descuento'         => $discount,
        'limite_descuento'  => $discount_limit,
        'precio_original'   => $price,
        'precio_costo'      => $price,
        'unidad_entrada'    => $entry_unit,
        'numero_piezas'     => $pieces_number,
        'tipo'              => $type,
        'serial_numbers'    => $serial_numbers
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
    $cartTotals         = getPurchaseOrderCartTotalsWithIEPS($carrito);
    $total              = $cartTotals['total'];

    $response = [
      'status'              => 'success',
      'toastMessage'        => '',
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
    $cartTotals         = getPurchaseOrderCartTotalsWithIEPS($carrito);
    $total              = $cartTotals['total'];

    $response = [
      'status'              => 'success',
      'toastMessage'        => 'El precio costo se actualizó correctamente',
      'id_producto'         => $id_producto,
      'precio_costo_final'  => '$' . number_format($precio_costo_final, DECIMALS_CURRENCY),
      'total'               => 'Total: $' . number_format($total, DECIMALS_CURRENCY) . ' MXN'
    ];
    break;

  case 'cart-update-row':
    $id       = cleanStr($_POST['id']);
    $quantity = cleanStr($_POST['quantity']);
    $price    = cleanStr($_POST['price']);
    $discount = cleanStr($_POST['discount']);

    $cart     = $_SESSION[$carrito_ssid];
    $product  = $cart[$id];

    $product['cantidad']      = $quantity;
    $product['precio_costo']  = $price;
    $product['descuento']     = $discount;

    $cart[$id] = $product;
    $_SESSION[$carrito_ssid] = $cart;

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'El producto se actualizó correctamente'
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
      $folio            = purchase_order_get_folio($id_sucursal);
      $carrito          = $_SESSION[$carrito_ssid];
      $tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

      $id_proveedor     = cleanStr($_POST['id_proveedor']);
      $observaciones    = cleanStr($_POST['observaciones']);

      if (isEmptyArray($carrito)) $response['toastMessage'] = 'El carrito está vacío';

      if (!isEmptyArray($carrito)) :
        $cartTotals = getPurchaseOrderCartTotalsWithIEPS($carrito);
        $subtotal   = $cartTotals['subtotal'];
        $total_ieps = $cartTotals['ieps'];
        $total_iva  = $cartTotals['iva'];
        $total      = $cartTotals['total'];

        $query = "INSERT INTO {$db_dti}_ordenes_compra (
            id_usuario,
            id_sucursal,
            id_proveedor,
            folio,
            observaciones,
            tipo,
            subtotal,
            iva,
            ieps,
            total
          ) VALUES (
            {$id_usuario},
            {$id_sucursal},
            {$id_proveedor},
            '{$folio}',
            '{$observaciones}',
            '{$tipo_movimiento}',
            {$subtotal},
            {$total_iva},
            {$total_ieps},
            {$total}
          )
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) :
          $id_orden_compra  = mysqli_insert_id($mysqli);
          $data_sucursal    = getBranchOfficeData($id_sucursal);
          $accion           = ACCION_NUEVA_ORDEN_COMPRA . " en {$data_sucursal['nombre_sucursal']}";

          foreach ($carrito as $key => $row) :
            $quantity       = $row['cantidad'];
            $have_iva_value = $row['aplica_iva'] ?? false;
            $have_ieps_value = $row['aplica_ieps'] ?? false;
            $have_iva       = ($have_iva_value === true || $have_iva_value === 1 || $have_iva_value === '1' || $have_iva_value === 'si');
            $have_ieps      = ($have_ieps_value === true || $have_ieps_value === 1 || $have_ieps_value === '1' || $have_ieps_value === 'si');
            $ieps_percentage = doubleval($row['ieps_porcentaje'] ?? 0);
            $price          = $row['precio_costo'];
            $discount       = $row['descuento'];

            # Calcular el precio con descuento
            $price_with_discount = $price - ($price * ($discount / 100));
            $ieps               = $have_ieps ? ($price_with_discount * ($ieps_percentage / 100)) : 0;
            $iva_base           = $price_with_discount + $ieps;
            $iva                = $have_iva ? ($iva_base * (doubleval($row['iva_porcentaje'] ?? 16) / 100)) : 0;

            $unit_ieps      = $ieps * $quantity;
            $unit_iva       = $iva * $quantity;
            $unit_subtotal  = $price_with_discount * $quantity;
            $unit_total     = ($price_with_discount + $ieps + $iva) * $quantity;

            $aplica_iva    = $have_iva ? 'si' : 'no';
            $aplica_ieps   = $have_ieps ? 'si' : 'no';

            $query_detalles = "INSERT INTO {$db_dti}_orden_compra_productos (
                id_orden_compra,
                id_producto,
                nombre_producto,
                cantidad,
                precio_original,
                precio_costo,
                aplica_iva,
                aplica_ieps,
                ieps_porcentaje,
                subtotal,
                iva,
                ieps,
                descuento,
                total
              ) VALUES (
                {$id_orden_compra},
                {$row['id_producto']},
                '{$row['nombre_producto']}',
                {$row['cantidad']},
                {$row['precio_original']},
                {$row['precio_costo']},
                '{$aplica_iva}',
                '{$aplica_ieps}',
                {$ieps_percentage},
                {$unit_subtotal},
                {$unit_iva},
                {$unit_ieps},
                {$discount},
                {$unit_total}
              )
            ";

            $query_detalles_result = mysqli_query($mysqli, $query_detalles);
          endforeach;

          $_SESSION[$carrito_ssid] = 0;
          unset($_SESSION[$carrito_ssid]);

          $response = [
            'status'        => 'success',
            'toastMessage'  => 'La orden de compra se creó correctamente'
          ];

          /* $ticket = BASE_URL . '/ticket-compra.php?uid=' . $id_orden_compra;
          $response['ticket'] = $ticket; */

          $ticket = BASE_URL . '/pdf-orden-compra.php?uid=' . $id_orden_compra;
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
