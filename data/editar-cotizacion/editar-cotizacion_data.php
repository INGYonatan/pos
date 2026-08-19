<?php
require '../lib/settings.inc.php';
require '../lib/shopping-cart.php';
require '../lib/helpers/quotes.helper.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response               = new stdClass();
$response->status       = 'error';
$response->toastMessage = '¡Error inesperado!, intentalo nuevamente';

$action           = $_POST['action'];
$identifier       = 'editar-cotizacion';

$carrito_ssid     = SESSION_CARRITO_EDITAR_COTIZACION;

$quote_id         = cleanStr($_POST['quoteId']);
$product_id       = cleanStr($_POST['itemId']);
$branch_id        = $IS_ADMIN ? cleanStr($_POST['branchId']) : getSessionBranchOfficeId();
$tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

$quantity         = $_POST['quantity'] ? doubleval(cleanStr($_POST['quantity'])) : 1;
$discount         = $_POST['discount'] ? doubleval(cleanStr($_POST['discount'])) : 0;
$rounding         = $_POST['rounding'] ? doubleval(cleanStr($_POST['rounding'])) : 0;

$mxn_symbol       = 'MXN';

$cart_session     = $_SESSION[$carrito_ssid] ?? new stdClass();

$cart = new ShoppingCart(
  $branch_id,
  $product_id,
  $cart_session,
  false,
  true
);

switch ($action):
  case "cart-load-{$identifier}":
    $list = $cart_session->list;

    include $identifier . '_carrito_table.php';
    die;
    break;

  case "cart-add-item-{$identifier}":
    $list = $cart_session->list;

    if ($list[$product_id])  $cart->increase_product_quantity($quantity);
    if (!$list[$product_id]) $cart->add_product($quantity);

    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$carrito_ssid] = $cart->get_cart();
    break;

  case "cart-update-item-price-{$identifier}":
    $price = $_POST['price'] ? doubleval(cleanStr($_POST['price'])) : 0;
    $cart->change_base_sale_price($price ?? 0);

    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$carrito_ssid] = $cart->get_cart();
    break;

  case "cart-update-item-quantity-{$identifier}":
    $cart->update_product_quantity($quantity ?? 1);

    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$carrito_ssid] = $cart->get_cart();
    break;

  case "update-discount-{$identifier}":
    $cart->update_product_discount($discount ?? 0);

    $response = $cart->get_alert();

    if ($response->status === 'success') :
      $cart_data                = $cart->get_cart();
      $_SESSION[$carrito_ssid]  = $cart_data;
      $response->callback       = "storeCart.loadCart();";
    endif;
    break;

  case "cart-update-rounding-{$identifier}":
    $cart->update_cart_rounding($rounding);

    $response = $cart->get_alert();

    if ($response->status === 'success') :
      $cart_data                = $cart->get_cart();
      $_SESSION[$carrito_ssid]  = $cart_data;

      $cart_total               = number_format($cart_data->sale_total, DECIMALS_CURRENCY);
      $response->cartTotal      = $cart_total;
    endif;
    break;

  case "cart-remove-item-{$identifier}":
    $cart->remove_product();
    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$carrito_ssid] = $cart->get_cart();
    break;

  case "cart-clean-cart-{$identifier}":
    $cart = get_quote_data($quote_id);

    $_SESSION[$carrito_ssid] = $cart;

    $response->status       = 'success';
    $response->toastMessage = 'El carrito se reinició correctamente';
    break;

  case "cart-save-cart-{$identifier}":
    try {
      $cart_data  = $cart->get_cart();
      $list       = $cart_data->list;

      if (isEmptyArray($list)) $response->toastMessage = 'El carrito está vacío';

      if (!isEmptyArray($list)) :
        $user_id      = get_id_usuario();
        $type         = cleanStr($_POST['type']);
        $customer_id  = cleanStr($_POST['customerId']);
        $observations = cleanStr($_POST['observations']);

        $subtotal     = $cart_data->sale_subtotal;
        $ieps         = $cart_data->sale_ieps;
        $iva          = $cart_data->sale_iva;
        $rounding     = $cart_data->sale_rounding;
        $total        = $cart_data->sale_total;
        $date         = date('Y-m-d H:i:s');

        $query = "UPDATE {$db_dti}_cotizaciones SET
            id_usuario      = ?,
            id_sucursal     = ?,
            id_cliente      = ?,
            observaciones   = ?,
            tipo            = ?,
            subtotal        = ?,
            ieps            = ?,
            iva             = ?,
            redondeo        = ?,
            total           = ?,
            fecha_creacion  = ?
          WHERE
            id_cotizacion = ?
        ";

        $stmt = $mysqli->prepare($query);

        $stmt->bind_param(
          'iiissdddddsi',
          $user_id,
          $branch_id,
          $customer_id,
          $observations,
          $type,
          $subtotal,
          $ieps,
          $iva,
          $rounding,
          $total,
          $date,
          $quote_id
        );

        $query_result = $stmt->execute();

        if ($query_result) :
          $query  = "DELETE FROM {$db_dti}_cotizacion_productos WHERE id_cotizacion = {$quote_id}";
          $query_result = mysqli_query($mysqli, $query);

          foreach ($list as $product) :
            $product_id         = $product->id;
            $name               = $product->name;
            $sale_price         = $product->sale_price;
            $sale_price_base    = $product->cart_base_sale_price;
            $wholesale_quantity = $product->wholesale_quantity;
            $wholesale_price    = $product->wholesale_price;
            $have_iva           = $product->have_iva;
            $have_ieps          = $product->have_ieps ?? 'no';
            $ieps_percentage    = $product->ieps_percentage ?? 0;
            $quantity           = $product->cart_quantity;
            $price              = $product->cart_sale_price;
            $ieps               = $product->cart_sale_ieps ?? 0;
            $iva                = $product->cart_sale_iva;
            $discount           = $product->cart_sale_discount;
            $net_price          = $product->cart_sale_net_price;
            $subtotal           = $product->cart_sale_amount_without_iva;
            $total              = $product->cart_sale_amount;
            $comments           = $product->comments ?? '';

            $query = "INSERT INTO {$db_dti}_cotizacion_productos (
                id_cotizacion,
                id_producto,
                nombre_producto,
                precio_venta,
                cantidad_mayoreo,
                precio_mayoreo,
                aplica_iva,
                aplica_ieps,
                ieps_porcentaje,
                precio_venta_base,
                cantidad,
                precio,
                ieps,
                iva,
                descuento,
                precio_neto,
                subtotal,
                total,
                comentarios
              ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
              )
            ";

            $stmt = $mysqli->prepare($query);

            $stmt->bind_param(
              'iisdddssdddddddddds',
              $quote_id,
              $product_id,
              $name,
              $sale_price,
              $wholesale_quantity,
              $wholesale_price,
              $have_iva,
              $have_ieps,
              $ieps_percentage,
              $sale_price_base,
              $quantity,
              $price,
              $ieps,
              $iva,
              $discount,
              $net_price,
              $subtotal,
              $total,
              $comments
            );

            $stmt->execute();
          endforeach;

          unset($_SESSION[$carrito_ssid]);
          increase_quote_edit_counter($quote_id);

          $pdf = BASE_URL . '/pdf-cotizacion.php?uid=' . $quote_id;

          $response->status       = 'success';
          $response->toastMessage = 'La cotización se actualizó correctamente';
          $response->pdf          = $pdf;
        endif;
      endif;
    } catch (Exception $e) {
      $response->toastMessage = $e->getMessage();
    }
    break;

  case "cart-action-comments-{$identifier}":
    $comment = cleanStr($_POST['comment']);

    $cart->update_product_comments($comment);

    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$carrito_ssid] = $cart->get_cart();
    break;
endswitch;

echo json_encode($response);
mysqli_close($mysqli);
die;
