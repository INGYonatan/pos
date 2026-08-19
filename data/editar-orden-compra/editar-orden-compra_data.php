<?php
require '../lib/settings.inc.php';
require '../lib/helpers/purchase-orders.helper.php';
require '../lib/helpers/purchases.helper.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'editar-orden-compra';

$carrito_ssid     = SESSION_CARRITO_EDITAR_ORDEN_COMPRA;
$id_sucursal      = $_POST['id_sucursal'];
$tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

$TIPO_PIEZA     = 'Pieza';
$TIPO_A_GRANEL  = 'A granel';

function getPurchaseOrderCartTotalsWithIEPSForEdit($cart)
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

      $serial_numbers         = $carrito_producto_data['serial_numbers'];

      for ($i = 1; $i <= $cantidad; $i++) :
        $serial_number = new stdClass();
        $serial_number->id      = "";
        $serial_number->number  = "";

        array_push($serial_numbers, $serial_number);
      endfor;

      $carrito_producto_data['serial_numbers'] = $serial_numbers;
    endif;

    if (!$carrito[$id_producto]) :
      $serial_numbers = [];

      for ($i = 1; $i <= $cantidad; $i++) :
        $serial_number = new stdClass();
        $serial_number->id      = "";
        $serial_number->number  = "";

        array_push($serial_numbers, $serial_number);
      endfor;

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
      $entry_unit     = $data_producto['unidad_entrada']/*  === 'caja' ? 'caj.' : 'pzs.' */;
      $pieces_number  = $data_producto["numero_piezas"];
      $type           = $data_producto['tipo'];
      $serial_numbers = $serial_numbers;

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
    $cartTotals         = getPurchaseOrderCartTotalsWithIEPSForEdit($carrito);
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
    $cartTotals         = getPurchaseOrderCartTotalsWithIEPSForEdit($carrito);
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

  case 'cart-dispatch-save-changes-' . $identifier:
    try {
      $parameters                   = new stdClass();
      $parameters->branchId         = $id_sucursal;
      $parameters->purchaseOrderId  = cleanStr($_POST['id_orden_compra']);
      $parameters->supplierId       = cleanStr($_POST['id_proveedor']);
      $parameters->documentFolio    = cleanStr($_POST['folio_documento']);
      $parameters->documentDate     = date('Y-m-d', strtotime($_POST['fecha_documento']));
      $parameters->paymentMethod    = cleanStr($_POST['metodo_pago']);
      $parameters->paymentForm      = cleanStr($_POST['forma_pago']);
      $parameters->observations     = cleanStr($_POST['observaciones']);
      $parameters->cart             = $_SESSION[$carrito_ssid];

      $request  = purchase_order_update($parameters);
      $response = $request;
    } catch (Exception $e) {
      $response['toastMessage'] = $e->getMessage();
    }
    break;

  case 'cart-save-cart-' . $identifier:
    $purchaseOrder     = purchase_order_get_by_id(cleanStr($_POST['id_orden_compra']));

    $parameters                     = new stdClass();
    $parameters->branchId           = $id_sucursal;
    $parameters->purchaseOrderId    = cleanStr($_POST['id_orden_compra']);
    $parameters->purchaseOrderFolio = $purchaseOrder->folio;
    $parameters->supplierId         = cleanStr($_POST['id_proveedor']);
    $parameters->documentFolio      = cleanStr($_POST['folio_documento']);
    $parameters->documentDate       = date('Y-m-d', strtotime($_POST['fecha_documento']));
    $parameters->paymentMethod      = cleanStr($_POST['metodo_pago']);
    $parameters->paymentForm        = cleanStr($_POST['forma_pago']);
    $parameters->observations       = cleanStr($_POST['observaciones']);
    $parameters->cart               = $_SESSION[$carrito_ssid];

    $purchaseOrderRequest = purchase_order_update($parameters);
    $purchaseRequest      = purchase_add($parameters);
    $response             = $purchaseRequest;

    if ($purchaseOrderRequest->status === 'success' && $purchaseRequest->status === 'success') :
      $query = "UPDATE {$db_dti}_ordenes_compra SET status = 'comprado' WHERE id_orden_compra = {$parameters->purchaseOrderId}";
      mysqli_query($mysqli, $query);

      $response->toastMessage = "La orden de compra se convirtío en compra";
    endif;
    break;

  case "update-serialNumbers-{$identifier}":
    $product_id             = cleanStr($_POST['itemId']);
    $branch_id              = $id_sucursal;
    $cart                   = $_SESSION[$carrito_ssid];
    $ps_serial_numbers_ids  = $_POST["{$identifier}-serialNumberIds"];
    $ps_serial_numbers      = $_POST["{$identifier}-serialNumbers"];
    $is_empty_array         = isEmptyArray($ps_serial_numbers);

    if ($is_empty_array) :
      $response['toastMessage'] = 'No hay números de serie';
      echo json_encode($response);
      die;
    endif;

    // Validar que los números de serie sean únicos
    if (count(array_unique($ps_serial_numbers)) !== count($ps_serial_numbers)) :
      $response["toastMessage"] = "Los números no deben de repetirse";
      echo json_encode($response);
      die;
    endif;

    $serial_numbers = [];

    foreach ($ps_serial_numbers as $number) :
      $serial_number = new stdClass();
      $serial_number->id      = "";
      $serial_number->number  = $number;

      array_push($serial_numbers, $serial_number);
    endforeach;

    $carrito                                  = $_SESSION[$carrito_ssid];
    $carrito_producto_data                    = $carrito[$product_id];
    $carrito_producto_data['serial_numbers']  = $serial_numbers;

    $carrito[$product_id]    = $carrito_producto_data;
    $_SESSION[$carrito_ssid]  = $carrito;

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'Los números de serie se actualizaron correctamente',
      'callback'      => "storeCart.loadCart();"
    ];
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
