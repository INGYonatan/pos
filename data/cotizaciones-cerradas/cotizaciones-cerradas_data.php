<?php
require '../lib/settings.inc.php';
require '../lib/helpers/quotes.helper.php';
require '../lib/helpers/sales.helper.php';
require_once __DIR__ . "/../lib/helpers/products.helper.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'cotizaciones-cerradas';
//$id_sucursal = getSessionBranchOfficeId();

$productsModel = new ProductHelper();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');
    $fecha_hoy        = date('Y-m-d');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();
    // $fecha            = cleanStr($_POST['fecha']);

    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $status           = cleanStr($_POST['status']);

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
      ["CTZ.tipo", "cerrada"],
      // ['CTZ.status', 'cancelado', '!=']
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
    if ($status === "expirado") array_push($c_where, [
      [
        ["CTZ.status", "procesado", "!="],
        ["(DATE_ADD(CTZ.fecha_creacion, INTERVAL {$quote_days_to_expired} DAY))", $fecha_hoy, "<"]
      ]
    ]);

    if ($status === "vigente")    array_push($c_where, [
      [
        ["CTZ.status", "procesado", "!="],
        ["(DATE_ADD(CTZ.fecha_creacion, INTERVAL {$quote_days_to_expired} DAY))", $fecha_hoy, ">"]
      ]
    ]);

    if ($status === "procesado")  array_push($c_where, ["CTZ.status", "procesado"]);

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

  case "action-convertir-a-venta-{$identifier}":
    $response['toastMessage'] = 'pl';
    if (checkModuleActionPermission($identifier, 'convertir-a-venta')) :
      try {
        $quote_id = cleanStr($_POST['uid']);
        $quote    = get_quote_data($quote_id);

        if ($quote) :
          $tipo_movimiento  = TIPO_MOVIMIENTO_INCREMENTO;
          $observaciones    = 'Cotización convertida en venta';
          $pago_con         = $quote->sale_total;
          $cambio           = 0;
          $folio            = get_sale_folio($quote->branch_id);

          $query = "INSERT INTO {$db_dti}_ventas (
              id_usuario,
              id_sucursal,
              id_cliente,
              folio,
              folio_cotizacion,
              tipo,
              observaciones,
              subtotal,
              iva,
              redondeo,
              total,
              pago_con,
              cambio
            ) VALUES (
              ?,?,?,?,?,?,?,?,?,?,?,?,?
            )
          ";

          $stmt = $mysqli->prepare($query);

          $stmt->bind_param(
            'iiissssdddddd',
            $quote->user_id,
            $quote->branch_id,
            $quote->customer_id,
            $folio,
            $quote->folio,
            $tipo_movimiento,
            $observaciones,
            $quote->sale_subtotal,
            $quote->sale_iva,
            $quote->sale_rounding,
            $quote->sale_total,
            $pago_con,
            $cambio
          );

          $query_result = $stmt->execute();

          if ($query_result) :
            $sale_id        = $stmt->insert_id;
            $list           = $quote->list;
            $data_sucursal  = getBranchOfficeData($quote->branch_id);
            $data_vendedor  = getUserData($quote->user_id);

            foreach ($list as $key => $product) :
              $query = "INSERT INTO {$db_dti}_venta_productos (
                  id_venta,
                  id_producto,
                  nombre_producto,
                  cantidad,
                  aplica_iva,
                  precio_original,
                  precio_venta,
                  descuento,
                  cantidad_mayoreo,
                  precio_mayoreo,
                  iva,
                  subtotal,
                  total
                ) VALUES (
                  ?,?,?,?,?,?,?,?,?,?,?,?,?
                )
              ";

              $stmt = $mysqli->prepare($query);

              $stmt->bind_param(
                'iisdsdddddddd',
                $sale_id,
                $product->id,
                $product->name,
                $product->quantity,
                $product->have_iva,
                $product->origin_sale_price,
                $product->sale_price,
                $product->discount,
                $product->wholesale_quantity,
                $product->wholesale_price,
                $product->sale_total_iva,
                $product->sale_amount,
                $product->sale_amount_with_iva
              );

              $stmt->execute();

              $product_data = getBranchOfficeProductData($quote->branch_id, $product->id);

              $data_kardex = [
                'id_producto'     => $product->id,
                'nombre_producto' => $product->name,
                'stock'           => $product_data['stock'],
                'cantidad'        => $product->quantity
              ];

              addLogInKardex(
                $quote->branch_id,
                $data_kardex,
                ACCION_COTIZACION_A_VENTA . " en sucursal {$data_sucursal['nombre_sucursal']} por {$data_vendedor['nombre_completo']}",
                $tipo_movimiento,
                'venta',
                $quote->user_id
              );
            endforeach;

            $query = "UPDATE {$db_dti}_cotizaciones SET
                status = 'procesado'
              WHERE
                id_cotizacion = {$quote->id}
            ";

            mysqli_query($mysqli, $query);

            $ticket_url = BASE_URL . '/ticket-venta.php?uid=' . $sale_id;

            $response = [
              'status'        => 'success',
              'toastMessage'  => 'La venta se realizó correctamente',
              'callback'      => '{
                load("' . $page . '", "' . $identifier . '");
                window.open("' . $ticket_url . '", "_blank");
              }'
            ];
          endif;
        endif;
      } catch (Exception $e) {
        $response['toastMessage'] = $e->getMessage();
      }
    endif;
    break;

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

  /*  */
  case "agregar-anticipo":
    if (!checkModuleActionPermission("cotizaciones-agregar-anticipo", 'agregar')) break;
    $quoteId              = cleanStr($_POST["uid"]);
    $userId               = get_id_usuario();
    $branchId             = getSessionBranchOfficeId();
    $folio                = get_sale_folio($branchId);
    $transactionType      = "anticipo";

    $productsModel->getProductTypeAdvance();

    if (!$productsModel->getId()) {
      $response["title"]        = "¡Atención!";
      $response['alertMessage'] = 'No se ha creado un producto para anticipos, para crearlo ve a <a class="fw-bold" target="_blank" href="' . BASE_URL . '/productos">productos</a> y crea un producto con el tipo "Anticipo"';
      break;
    }

    /**
     * Ventas
     * subtotal
     * iva
     * total
     */
    $date                 = date('Y-m-d', strtotime($_POST["fecha_hora"])) . ' ' . date('H:i:s');
    $notes                = cleanStr($_POST["notas"]);
    $cashAmount           = floatval(cleanStr($_POST["efectivo_monto"]));
    $cashReference        = cleanStr($_POST["efectivo_referencia"]);
    $checkAmount          = floatval(cleanStr($_POST["cheque_monto"]));
    $checkReference       = cleanStr($_POST["cheque_referencia"]);
    $transferAmount       = floatval(cleanStr($_POST["transferencia_monto"]));
    $transferReference    = cleanStr($_POST["transferencia_referencia"]);
    $debitCardAmount      = floatval(cleanStr($_POST["tarjeta_debito_monto"]));
    $debitCardReference   = cleanStr($_POST["tarjeta_debito_numero"]);
    $creditCardAmount     = floatval(cleanStr($_POST["tarjeta_credito_monto"]));
    $creditCardReference  = cleanStr($_POST["tarjeta_credito_numero"]);
    $totalAmount          = $cashAmount + $checkAmount + $transferAmount + $debitCardAmount + $creditCardAmount;
    $subtotal             = $totalAmount / 1.16;
    $iva                  = $totalAmount - $subtotal;
    $paymentForm          = "contado";

    $quoteData = get_quote_data($quoteId);

    if (!$quoteData) {
      $response['toastMessage'] = 'La cotización no existe';
      break;
    }

    if ($totalAmount <= 0) {
      $response['toastMessage'] = 'El anticipo debe ser mayor a $0.00';
      break;
    }

    // Agregar a ventas
    $uqery = "INSERT INTO {$db_dti}_ventas (
        id_usuario,
        id_sucursal,
        id_cliente,
        folio,
        folio_cotizacion,
        tipo_transaccion,
        observaciones,
        efectivo,
        efectivo_referencia,
        cheque,
        cheque_referencia,
        transferencia,
        transferencia_referencia,
        tarjeta_debito,
        tarjeta_debito_numero,
        tarjeta_credito,
        tarjeta_credito_numero,
        subtotal,
        iva,
        total,
        pago_con,
        fecha_creacion,
        forma_pago
      ) VALUES (
          ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
      )
    ";

    $stmt = $mysqli->prepare($uqery);

    try {
      $stmt->bind_param(
        "iiissssdsdsdsdsdsddddss",
        $userId,
        $branchId,
        $quoteData->customer_id,
        $folio,
        $quoteData->folio,
        $transactionType,
        $notes,
        $cashAmount,
        $cashReference,
        $checkAmount,
        $checkReference,
        $transferAmount,
        $transferReference,
        $debitCardAmount,
        $debitCardReference,
        $creditCardAmount,
        $creditCardReference,
        $subtotal,
        $iva,
        $totalAmount,
        $totalAmount,
        $date,
        $paymentForm
      );

      $result = $stmt->execute();

      if ($result) {
        $saleId = $stmt->insert_id;

        // Agregar a venta productos
        $productId      = $productsModel->getId();
        $typeId         = $productsModel->getTypeId();
        $name           = $productsModel->getName();
        $salePrice      = $totalAmount; // Precio con iva
        $haveIva        = "si";
        $salePriceBase  = $totalAmount; // Precio con iva
        $quantity       = 1;
        $price          = $subtotal;    // Precio sin iva
        $iva            = $iva;         // IVA
        $priceNet       = $totalAmount; // Precio con iva
        $subtotal       = $subtotal;    // Precio sin iva
        $total          = $totalAmount; // Precio con iva

        $query = "INSERT INTO {$db_dti}_venta_productos (
            id_venta,
            id_producto,
            id_tipo,
            nombre_producto,
            precio_venta,
            aplica_iva,
            precio_venta_base,
            cantidad,
            precio,
            iva,
            precio_neto,
            subtotal,
            total
          ) VALUES (
            '{$saleId}',
            '{$productId}',
            '{$typeId}',
            '{$name}',
            '{$salePrice}',
            '{$haveIva}',
            '{$salePriceBase}',
            '{$quantity}',
            '{$price}',
            '{$iva}',
            '{$priceNet}',
            '{$subtotal}',
            '{$total}'
          )
        ";

        mysqli_query($mysqli, $query);

        $response = [
          'status'        => 'success',
          'toastMessage'  => 'El anticipo se agregó correctamente',
          'callback'      => "{
            load('1', '{$identifier}');
            window.open('" . BASE_URL . "/ticket-venta.php?uid={$saleId}', '_blank');
          }"
        ];
      }
    } catch (Exception $e) {
      error_log("ERROR_COTIZACIONES_CERRADAS::AGREGAR_ANTICIPO: {$e->getMessage()}");
    }
    break;
};

// $response['post'] = json_encode($_POST);

echo json_encode($response);
mysqli_close($mysqli);
die;
