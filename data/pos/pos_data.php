<?php
require '../lib/settings.inc.php';
require '../lib/shopping-cart.php';
require '../lib/helpers/sales.helper.php';
include '../lib/helpers/quotes.helper.php';
require_once __DIR__ . "/../lib/helpers/products.helper.php";


require_once __DIR__ . "/../lib/php-mailer/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$response               = new stdClass();
$response->status       = 'error';
$response->toastMessage = '¡Error inesperado!, intentalo nuevamente';

$user_data        = getUserData(get_id_usuario());
$IS_ADMIN         = $user_data['IS_ADMIN'] == 'si' ? true : false;

$action           = $_POST['action'];
$identifier       = 'pos';
$inventory_id     = 'pos-inventario';
$products_id      = 'pos-productos';
$today_sales_id   = 'ventas-del-dia';

$carrito_ssid     = SESSION_CARRITO_POS;

$product_id       = cleanStr($_POST['itemId']);
$branch_id        = $IS_ADMIN ? cleanStr($_POST['branchId']) : getSessionBranchOfficeId();
$code             = $_POST['code'] ? cleanStr($_POST['code']) : '';
$tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;

$quantity         = $_POST['quantity'] ? doubleval(cleanStr($_POST['quantity'])) : 1;
$discount         = $_POST['discount'] ? doubleval(cleanStr($_POST['discount'])) : 1;
$rounding         = $_POST['rounding'] ? doubleval(cleanStr($_POST['rounding'])) : 0;

$mxn_symbol       = 'MXN';

$cart_session     = $_SESSION[$carrito_ssid] ?? new stdClass();

if ($code) :
  $product_id = getProductIdByCode($code);

  if (!$product_id) :
    $response->toastMessage = "El producto no existe";

    echo json_encode($response);
    die;
  endif;
endif;

$cart = new ShoppingCart(
  $branch_id,
  $product_id,
  $cart_session,
  true,
  true
);

$productsModel = new ProductHelper();

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

      $cart_total               = number_format($cart_data->sale_total, DECIMALS_CURRENCY_TICKET);
      $response->cartTotal      = $cart_total;
    endif;
    break;

  case "cart-remove-item-{$identifier}":
    $cart->remove_product();
    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$carrito_ssid] = $cart->get_cart();
    break;

  case "cart-clean-cart-{$identifier}":
    unset($_SESSION[$carrito_ssid]);

    $response->status = 'success';

    if ($_POST['useAlert'] != 'false') $response->toastMessage = 'El carrito se vació correctamente';
    if ($_POST['useAlert'] == 'false') $response->toastMessage = '';
    break;

  case "update-serialNumbers-{$identifier}":
    $ps_serial_numbers_ids  = $_POST["{$identifier}-serialNumberIds"];
    $ps_serial_numbers      = $_POST["{$identifier}-serialNumbers"];
    $is_empty_array         = isEmptyArray($ps_serial_numbers);

    if ($is_empty_array) :
      $response->toastMessage = 'No hay números de serie';
      echo json_encode($response);
      die;
    endif;

    $cart->update_serial_numbers($ps_serial_numbers);

    $response = $cart->get_alert();

    if ($response->status === 'success') :
      $_SESSION[$carrito_ssid]  = $cart->get_cart();
      $response->callback       = "storeCart.loadCart();";
    endif;
    break;

  case "cart-save-cart-{$identifier}":
    $cart_data  = $cart->get_cart();
    $list       = $cart_data->list;

    if (isEmptyArray($list)) $response->toastMessage = 'El carrito está vacío';

    if (!isEmptyArray($list)) :
      $create_invoice         = $_POST['createInvoice'] == 'si' ? true : false;
      $user_id                = cleanStr($_POST['sellerId']);
      $folio                  = get_sale_folio($branch_id);
      $type                   = $tipo_movimiento;
      $customer_id            = cleanStr($_POST['customerId']);
      $cfdi_id                = cleanStr($_POST['cfdi']);
      $address_id             = cleanStr($_POST['addressId']) ?? "0";
      $payment_form           = cleanStr($_POST['paymentForm']);
      $comments               = cleanStr($_POST['comments']);

      $cash                   = $_POST['cash'] ? cleanStr($_POST['cash']) : 0;

      $check                  = $_POST['check'] ? cleanStr($_POST['check']) : 0;
      $check_reference        = $check ? cleanStr($_POST['checkReference']) : 'NULL';

      $transfer               = $_POST['transfer'] ? cleanStr($_POST['transfer']) : 0;
      $transfer_reference     = $transfer ? cleanStr($_POST['transferReference']) : 'NULL';

      $credit_card            = $_POST['creditCard'] ? cleanStr($_POST['creditCard']) : 0;
      $credit_card_reference  = $credit_card ? cleanStr($_POST['creditCardReference']) : 'NULL';
      $credit_card_number     = $credit_card ? cleanStr($_POST['creditCardNumber']) : 'NULL';

      $debit_card             = $_POST['debitCard'] ? cleanStr($_POST['debitCard']) : 0;
      $debit_card_reference   = $debit_card ? cleanStr($_POST['debitCardReference']) : 'NULL';
      $debit_card_number      = $debit_card ? cleanStr($_POST['debitCardNumber']) : 'NULL';
      $quoteId                = isset($_POST['quoteId']) ? cleanStr($_POST['quoteId']) : null;
      $quoteFolio             = "";

      $pay_with               = $cash + $check + $transfer + $credit_card + $debit_card;

      $exchange               = $_POST['exchange'] ?? 0;
      $observations           = $comments;

      $cash = $cash - $exchange;

      $isSerialNumbersSended = false;

      // $response->toastMessage = "El total en efectivo es: {$cash} y el cambio es: {$exchange}";
      // break;

      $subtotal               = $cart_data->sale_subtotal;
      $ieps                   = $cart_data->sale_ieps ?? 0;
      $iva                    = $cart_data->sale_iva;
      $rounding               = $cart_data->sale_rounding;
      $total                  = $cart_data->sale_total;
      $product_type           = $cart_data->product_type;

      $total = round($total, 2);

      $tipoTransaccion       = "venta";

      // Obtener la cotización si es que existe
      if ($quoteId) {
        $quoteData  = get_quote_data($quoteId);
        $quoteFolio = $quoteData->folio;

        // Obtener el producto tipo nota de crédito
        $productsModel->getProductTypeCreditNote();

        // Verificar si es necesario crear la nota de crédito
        $saleCreditNoteTotals = getSaleCreditNoteTotals([
          "quoteData" => $quoteData
        ]);

        if ($saleCreditNoteTotals && !$productsModel->getId()) {
          $response->status        = "¡Atención!";
          $response->alertMessage  = 'No se ha creado un producto para notas de crédito, para crearlo ve a <a class="fw-bold" target="_blank" href="' . BASE_URL . '/productos">productos</a> y crea un producto con el tipo "Nota de Crédito"';
          break;
        }
      }

      // Validar el crédito del cliente
      if ($payment_form == "credito") {
        $canBuyWithCredit = canBuyWithCredit($customer_id, $total);

        if ($canBuyWithCredit["status"] == "error") {
          $response->toastMessage = $canBuyWithCredit["message"];
          echo json_encode($response);
          die;
        }
      }

      if (($pay_with < $total) && $payment_form == "contado") :
        $response->toastMessage = "El pago no está completo {$pay_with} :::: {$total}";
        echo json_encode($response);
        die;
      endif;

      if ($cash <= 0) :
        $symbol       = "$";
        $total_format = number_format($total, DECIMALS_CURRENCY);

        if ($pay_with > $total) :
          $response->toastMessage = "El pago tiene que ser la cantidad exacta o menos de {$symbol}{$total_format}";
          echo json_encode($response);
          die;
        endif;
      endif;

      // Definir si ya está pagado o no
      $is_paid = $pay_with >= $total ? "si" : "no";

      /**
       * Validar los números de serie
       */
      $sn_status = $cart->validate_serial_numbers();

      if ($sn_status->status == "error") :
        echo json_encode($sn_status);
        die;
      endif;

      $query = "INSERT INTO {$db_dti}_ventas (
          id_usuario,
          id_sucursal,
          id_cliente,
          id_cfdi,
          id_direccion,
          folio,
          folio_cotizacion,
          tipo,
          observaciones,
          subtotal,
          iva,
          ieps,
          redondeo,
          total,
          efectivo,
          cheque,
          cheque_referencia,
          transferencia,
          transferencia_referencia,
          tarjeta_credito,
          tarjeta_credito_referencia,
          tarjeta_credito_numero,
          tarjeta_debito,
          tarjeta_debito_referencia,
          tarjeta_debito_numero,
          pago_con,
          cambio,
          tipo_productos,
          forma_pago,
          pagado
        ) values (
          ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param(
        'iiiiissssdddddddsdsdssdssddsss',
        $user_id,
        $branch_id,
        $customer_id,
        $cfdi_id,
        $address_id,
        $folio,
        $quoteFolio,
        $type,
        $observations,
        $subtotal,
        $iva,
        $ieps,
        $rounding,
        $total,
        $cash,
        $check,
        $check_reference,
        $transfer,
        $transfer_reference,
        $credit_card,
        $credit_card_reference,
        $credit_card_number,
        $debit_card,
        $debit_card_reference,
        $debit_card_number,
        $pay_with,
        $exchange,
        $product_type,
        $payment_form,
        $is_paid
      );

      $query_result = $stmt->execute();

      if ($query_result) :
        $sale_id      = $stmt->insert_id;
        $branch_data  = getBranchOfficeData($branch_id);
        $seller_data  = getUserData($user_id);

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
          $type               = $product->type;
          $type_id            = $product->type_id;
          $comments           = $product->comments ?? '';

          $query = "INSERT INTO {$db_dti}_venta_productos (
              id_venta,
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
              iva,
              ieps,
              descuento,
              precio_neto,
              subtotal,
              total,
              id_tipo,
              tipo,
              comentarios
            ) VALUES (
              ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
          ";

          $stmt = $mysqli->prepare($query);

          $stmt->bind_param(
            'iisdddssddddddddddiss',
            $sale_id,
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
            $iva,
            $ieps,
            $discount,
            $net_price,
            $subtotal,
            $total,
            $type_id,
            $type,
            $comments
          );

          $query_result = $stmt->execute();

          if ($query_result) :
            $product_sale_id  = $stmt->insert_id;
            $product_data     = getBranchOfficeProductData($branch_id, $product_id);

            $data_kardex = [
              'id_producto'     => $product_id,
              'nombre_producto' => $name,
              'stock'           => $product_data['stock'],
              'cantidad'        => $quantity
            ];

            if (!$quoteId) addLogInKardex(
              $branch_id,
              $data_kardex,
              ACCION_VENTA . " en sucursal {$branch_data['nombre_sucursal']} por {$seller_data['nombre_completo']}",
              $type,
              'venta',
              $user_id
            );

            if ($quoteId) addLogInKardex(
              $branch_id,
              $data_kardex,
              ACCION_COTIZACION_A_VENTA . " en sucursal {$branch_data['nombre_sucursal']} por {$seller_data['nombre_completo']}",
              $type,
              'venta',
              $user_id
            );

            /**
             * Actualizar los números de serie
             */
            if ($product->requires_serial_number) :
              $serialNumbersCondition = "";
              $serialNumbers          = $product->serial_numbers;
              $counter                = 0;

              foreach ($serialNumbers as $serialNumber) :
                $isSerialNumbersSended = true;

                $concat = $counter > 0 ? "OR " : "";
                $serialNumbersCondition .= "{$concat}(
                  numero_serie  = '{$serialNumber->number}' AND
                  id_producto   = {$product_id}
                )";
                $counter++;
              endforeach;

              $serialNumbersQuery = "UPDATE {$db_dti}_producto_numeros_serie SET
                  folio_venta = '{$folio}',
                  status      = 'vendido'
                WHERE
                  {$serialNumbersCondition}
              ";

              mysqli_query($mysqli, $serialNumbersQuery);
            endif;
          endif;
        endforeach;

        if ($quoteId) {
          // Crear nota de crédito
          createCreditNoteFromQuote([
            "userId"     => $user_id,
            "branchId"   => $branch_id,
            "saleId"     => $sale_id,
            "quoteData"  => $quoteData
          ]);

          $query = "UPDATE {$db_dti}_cotizaciones SET
              status = 'procesado'
            WHERE
              id_cotizacion = {$quoteId}
          ";

          mysqli_query($mysqli, $query);
        }

        if ($payment_form == "credito") {
          $today_date   = date('Y-m-d H:i:s');
          $total_amount = $pay_with;

          if ($total_amount > 0) {
            $query = "INSERT INTO {$db_dti}_venta_pagos (
                id_venta,
                efectivo_monto,
                cheque_monto,
                cheque_referencia,
                transferencia_monto,
                transferencia_referencia,
                tarjeta_credito_monto,
                tarjeta_credito_numero,
                tarjeta_debito_monto,
                tarjeta_debito_numero,
                monto_total,
                fecha_hora,
                notas
              ) VALUES (
                '$sale_id',
                '$cash_amount',
                '$check_amount',
                '$check_reference',
                '$transfer_amount',
                '$transfer_reference',
                '$credit_card_amount',
                '$credit_card_number',
                '$debit_card_amount',
                '$debit_card_number',
                '$total_amount',
                '$today_date',
                'Abono inicial'
              )
            ";

            $result = mysqli_query($mysqli, $query);
          }
        }

        unset($_SESSION[$carrito_ssid]);

        $ticket = BASE_URL . '/ticket-venta.php?uid=' . $sale_id;

        $response->status       = 'success';
        $response->toastMessage = 'La venta se realizó correctamente';
        $response->ticket          = $ticket;

        if ($create_invoice) :
          $response->folio = $folio;
        endif;

        $sendTicketByEmail = $_POST['sendTicketByEmail'] == 'si' ? true : false;

        if ($sendTicketByEmail) {
          $customerData = getCustomerById($customer_id);
          $email        = $customerData['correo'];

          if ($email) {
            $ticket_venta_id_venta = $sale_id;

            ob_start();
            include __DIR__ . "/../../ticket-venta.php";
            $message = ob_get_clean();

            $config = [
              'mail'  => new PHPMailer(),
              'from'  => [
                'name'      => ADM_NAME,
                'username'  => PHPMAILER_SALES_EMAIL,
                'password'  => PHPMAILER_SALES_PASSWORD
              ],
              'to'      => [[
                'name'  => $customerData['nombre_completo'],
                'email' => $customerData['correo']
              ]],
              'subject' => ADM_NAME . '| Ticket de Compra',
              'message' => $message
            ];

            $request = sendEmail($config);
          }
        }

      // if ($isSerialNumbersSended) {
      //   // Enviar el ticket a EMAIL_SEND_WHEN_SALE_EQUIPMENT
      //   $ticket_venta_id_venta  = $sale_id;
      //   $todayDate              = date("d/m/y");
      //   $subject                = "VENTA EQUIPO {$todayDate} SUC {$branch_data["nombre_sucursal"]}";

      //   ob_start();
      //   include __DIR__ . "/../../ticket-venta.php";
      //   $message = ob_get_clean();

      //   $config = [
      //     "mail" => new PHPMailer(),
      //     "from" => [
      //       "name" => ADM_NAME,
      //       "username" => PHPMAILER_SALES_EMAIL,
      //       "password" => PHPMAILER_SALES_PASSWORD
      //     ],
      //     "to" => [[
      //       "name" => SALE_EQUIPMENT_NAME,
      //       "email" => SALE_EQUIPMENT_EMAIL
      //     ]],
      //     "subject" => $subject,
      //     "message" => $message
      //   ];

      //   $request = sendEmail($config);
      // }
      endif;
    endif;
    break;

  case "load-{$inventory_id}":
    $have_actions     = haveActions($inventory_id, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $branch_data      = getBranchOfficeData($branch_id);

    $column_id        = "id_producto";
    $c_from           = "{$db_dti}_productos";
    $c_extra_clauses  = "ORDER BY id_producto DESC";

    $by_branch_id     = "id_sucursal = {$branch_id}";

    $query_existence = "SELECT
        SUM(I.stock)
      FROM
        {$db_dti}_inventario AS I
      WHERE
        (I.id_producto = uid) AND
        ({$by_branch_id})
    ";

    $fields = [
      "id_producto",
      ["id_producto", "uid"],
      "codigo",
      "nombre_producto",
      "contenido",
      "precio_venta",
      "unidad",
      ["({$query_existence})", "existencia"]
    ];

    $c_join = "";

    $c_where = [
      ["status", "activo"]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["codigo",  "%$search%", "LIKE"],
        ["nombre_producto",  "%$search%", "LIKE", "OR"]
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

    # echo getEmptyTableMessage($request['query']);
    # die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $inventory_id . '_table.php';

    echo "
      <script>
        $('#modal-inventory-title').html('Inventario de {$branch_data['nombre_sucursal']}');
      </script>
    ";
    die;
    break;

  case "load-{$today_sales_id}":
    $have_actions     = haveActions($today_sales_id, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $branch_data      = getBranchOfficeData($branch_id);
    $date             = date('d-m-Y');

    $column_id        = "V.id_venta";
    $c_from           = "{$db_dti}_ventas AS V";
    $c_extra_clauses  = "ORDER BY V.id_venta DESC";

    $fields = [
      "V.id_venta",
      ["V.id_venta", "uid"],
      "V.folio",
      "V.id_usuario",
      "V.id_sucursal",
      "V.id_cliente",
      "V.status",
      "V.tipo",
      "V.observaciones",
      "V.total",
      "V.pago_con",
      "V.cambio",
      "V.fecha_creacion",
      ["DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      "S.nombre_sucursal",
      "U.nombre_completo",
      ["C.nombre_completo", "nombre_cliente"]
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_sucursales  AS S  ON (V.id_sucursal = S.id_sucursal)
      LEFT JOIN {$db_ati}_usuarios    AS U  ON (V.id_usuario  = U.id_usuario)
      LEFT JOIN {$db_dti}_clientes    AS C  ON (V.id_cliente  = C.id_cliente)
    ";

    $c_where = [];

    if (!empty($search))    array_push($c_where, ["V.folio",  "%$search%", "LIKE"]);
    if (!empty($branch_id)) array_push($c_where, ["V.id_sucursal",  "{$branch_id}"]);
    if (!empty($date))      array_push($c_where, ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))",  "{$date}"]);

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
    if ($request['status'] === 'success') include $today_sales_id . '_table.php';

    echo "
      <script>
        $('#modal-today-sales-title').html('Ventas del día de {$branch_data['nombre_sucursal']}');
      </script>
    ";
    die;
    break;

  case "load-{$products_id}":
    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $branch_data      = getBranchOfficeData($branch_id);

    $column_id        = "I.id_inventario";
    $c_from           = "{$db_dti}_inventario AS I";
    $c_extra_clauses  = "ORDER BY I.id_inventario DESC";

    $fields = [
      "I.id_inventario",
      ["I.id_inventario", "uid"],
      "I.id_producto",
      "I.stock",
      "P.codigo",
      "P.nombre_producto"
    ];

    $c_join = "
      LEFT JOIN
        {$db_dti}_productos AS P ON (P.id_producto = I.id_producto)
    ";

    $c_where = [
      ["P.status", "activo"],
      ["I.id_sucursal", $branch_id]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["P.codigo",  "%$search%", "LIKE"],
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

    # echo getEmptyTableMessage($request['query']);
    # die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $products_id . '_table.php';

    echo "
      <script>
        $('#modal-products-title').html('Productos de {$branch_data['nombre_sucursal']}');
      </script>
    ";
    die;
    break;

  case "action-imprimir-ticket-{$today_sales_id}":
    if (checkModuleActionPermission($today_sales_id, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/ticket-venta.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;

  case "cart-action-corte-caja-{$identifier}":
    if (checkModuleActionPermission($identifier, 'corte-caja')) :
      $user_id = get_id_usuario();

      $query = "SELECT
          SUM(total) AS total,
          SUM(redondeo) AS redondeo
        FROM
          {$db_dti}_ventas
        WHERE
          id_sucursal = {$branch_id} AND
          corte       = 'no' AND
          status      = 'activo'
      ";

      $query_result = mysqli_query($mysqli, $query);
      $data_ventas  = mysqli_fetch_assoc($query_result);
      //$total        = $data_ventas['total'];

      // Obtener el total de las ventas pero desde los productos y no de las ventas
      $query = "SELECT
          SUM(VP.total) AS total,
          SUM(V.redondeo) AS redondeo
        FROM
          {$db_dti}_venta_productos AS VP
        LEFT JOIN
          {$db_dti}_ventas AS V ON (VP.id_venta = V.id_venta)
        WHERE
          VP.cancelado = 'no' AND
          V.id_sucursal = {$branch_id} AND
          V.corte       = 'no' AND
          V.status      = 'activo'
      ";

      $query_result = mysqli_query($mysqli, $query);
      $total        = 0;
      $redondeo     = 0;

      $data_sales = mysqli_fetch_assoc($query_result);

      $total    = $data_sales['total'];
      $redondeo = $data_sales['redondeo'];

      $total = $total + $redondeo;

      if ($total == 0) $response->toastMessage = 'No hay ventas realizadas.';

      if ($total > 0) :
        $folio        = get_cash_register_folio($branch_id);
        $date_from  = get_last_date_cash_register($branch_id);

        $cash_cut_query = "INSERT INTO {$db_dti}_cortes_caja (
            id_usuario,
            id_sucursal,
            folio,
            total,
            fecha_desde
          ) VALUES (
            {$user_id},
            {$branch_id},
            '{$folio}',
            {$total},
            '{$date_from}'
          )
        ";

        $cash_cut_query_result = mysqli_query($mysqli, $cash_cut_query);

        if ($cash_cut_query_result) :
          $cash_cut_id = mysqli_insert_id($mysqli);

          $update_query = "UPDATE {$db_dti}_ventas SET
              id_corte_caja = {$cash_cut_id},
              corte         = 'si'
            WHERE
              id_sucursal = {$branch_id} AND
              corte       = 'no'
          ";

          $update_query_result = mysqli_query($mysqli, $update_query);

          $response->status       = 'success';
          $response->toastMessage = 'El corte del día se realizó correctamente.';
          $response->ticket       = BASE_URL . '/ticket-corte.php?uid=' . $cash_cut_id;
        endif;
      endif;
    endif;
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
