<?php
function purchase_get_by_id(
  $purchase_id
) {
  global $mysqli;
  global $db_dti;

  if (!$purchase_id) return false;

  $query = "SELECT
      id_compra,
      id_usuario,
      id_sucursal,
      id_proveedor,
      folio,
      folio_documento,
      fecha_documento,
      DATE_FORMAT(fecha_documento, '%d-%m-%Y') AS fecha_documento_formato,
      metodo_pago,
      forma_pago,
      observaciones,
      tipo,
      subtotal,
      iva,
      ieps,
      total
    FROM
      {$db_dti}_compras
    WHERE
      id_compra = ? AND
      status    = 'activo'
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $purchase_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return false;

  $data     = mysqli_fetch_assoc($query_result);
  $purchase = new stdClass();

  $purchase->id                   = $data['id_compra'];
  $purchase->user_id              = $data['id_usuario'];
  $purchase->branch_id            = $data['id_sucursal'];
  $purchase->supplier_id          = $data['id_proveedor'];
  $purchase->folio                = $data['folio'];
  $purchase->document_folio       = $data['folio_documento'];
  $purchase->document_date        = $data['fecha_documento'];
  $purchase->document_date_format = $data['fecha_documento_formato'];
  $purchase->payment_method       = $data['metodo_pago'];
  $purchase->payment_form         = $data['forma_pago'];
  $purchase->observations         = $data['observaciones'];
  $purchase->type                 = $data['tipo'];
  $purchase->subtotal             = $data['subtotal'] ?? 0;
  $purchase->iva                  = $data['iva'] ?? 0;
  $purchase->ieps                 = $data['ieps'] ?? 0;
  $purchase->total                = $data['total'];
  $purchase->list                 = purchase_get_products($purchase->id);

  return $purchase;
}

function purchase_get_products(
  $purchase_id
) {
  global $mysqli;
  global $db_dti;

  if (!$purchase_id) return false;

  $query = "SELECT
      CP.id_compra_producto,
      CP.id_compra,
      CP.id_producto,
      CP.nombre_producto,
      CP.cantidad,
      CP.precio_original,
      CP.precio_costo,
      CP.aplica_iva,
      CP.aplica_ieps,
      CP.ieps_porcentaje,
      CP.subtotal,
      CP.iva,
      CP.ieps,
      CP.descuento,
      CP.total,
      CP.cancelado,
      P.codigo,
      P.unidad,
      P.contenido,
      I.stock
    FROM
      {$db_dti}_compra_productos AS CP
    LEFT JOIN
      {$db_dti}_productos AS P ON (P.id_producto = CP.id_producto)
    LEFT JOIN
      {$db_dti}_compras AS C ON (C.id_compra = CP.id_compra)
    LEFT JOIN
      {$db_dti}_inventario AS I ON (
        I.id_producto = CP.id_producto AND
        I.id_sucursal = C.id_sucursal
      )
    WHERE
      CP.id_compra = ?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $purchase_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return [];

  $list = [];

  while ($data = mysqli_fetch_assoc($query_result)) :
    $product = new stdClass();

    $product->id              = $data['id_producto'];
    $product->code            = $data['codigo'];
    $product->name            = $data['nombre_producto'];
    $product->content         = $data['contenido'];
    $product->stock           = $data['stock'];
    $product->quantity        = $data['cantidad'];
    $product->cancelled       = $data['cancelado'] === 'si' ? true : false;
    $product->unit            = $data['unidad'];
    $product->unit_symbol     = $data['unidad'] == 'Pieza' ? 'pzs.' : 'kg.';
    $product->original_price  = $data['precio_original'];
    $product->cost_price      = $data['precio_costo'];
    $product->have_iva        = $data['aplica_iva'] === 'si' ? true : false;
    $product->have_ieps       = $data['aplica_ieps'] === 'si' ? true : false;
    $product->ieps_percentage = $data['ieps_porcentaje'] ?? 0;
    $product->subtotal        = $data['subtotal'] ?? 0;
    $product->iva             = $data['iva'] ?? 0;
    $product->ieps            = $data['ieps'] ?? 0;
    $product->discount        = $data['descuento'] ?? 0;

    $products[$product->id]   = $product;
  endwhile;

  return $products;
}

function purchase_create_cart_edit(
  $purchase_id
) {
  if (!$purchase_id) return false;

  $purchase = purchase_get_by_id($purchase_id);

  if (!$purchase) return false;

  $cart = [];

  foreach ($purchase->list as $product) :
    $data = [
      'id_producto'     => $product->id,
      'codigo'          => $product->code,
      'nombre_producto' => $product->name,
      'contenido'       => $product->content,
      'stock'           => $product->stock,
      'cantidad'        => $product->quantity,
      'precio_original' => $product->original_price,
      'precio_costo'    => $product->cost_price,
      'unidad'          => $product->unit
    ];

    $cart[$product->id] = $data;
  endforeach;

  return $cart;
}

function purchase_validate_serial_numbers(
  $cart
) {
  $response               = new stdClass();
  $response->status       = "error";
  $response->type         = "";
  $response->toastMessage = "";

  foreach ($cart as $product) :
    // Solo los productos tipo "equipo" manejan números de serie
    $is_equipment = $product['requiere_numero_serie'];

    if ($is_equipment) :
      $quantity                 = $product['cantidad'];
      $serial_numbers           = $product['serial_numbers'];
      $is_empty_serial_numbers  = isEmptyArray($serial_numbers);

      if ($is_empty_serial_numbers) :
        $response->toastMessage = "No hay números de serie en el producto {$product['nombre_producto']}";
        $response->type = "empty";

        return $response;
      endif;

      if (count($serial_numbers) < $quantity) :
        $response->toastMessage = "Faltan números de serie en el producto {$product['nombre_producto']}";
        $response->type = "smaller";

        return $response;
      endif;

      if (count($serial_numbers) > $quantity) :
        $response->toastMessage = "Los números de serie en el producto {$product['nombre_producto']} superan la cantidad";
        $response->type = "greater";
        return $response;
      endif;

      foreach ($serial_numbers as $serial_number) :
        if (!$serial_number->number) :
          $response->toastMessage = "Hay números de serie vacíos en el producto {$product['nombre_producto']}";
          $response->type = "void";
          return $response;
        endif;
      endforeach;

      # Verificar que los datos en el array no se repitan
      $verify_serial_numbers = array_unique($serial_numbers, SORT_REGULAR);

      if (count($verify_serial_numbers) !== count($serial_numbers)) :
        $response->toastMessage = "Hay números de serie duplicados en el producto {$product['nombre_producto']}";
        $response->type = "duplicate";
        return $response;
      endif;
    endif;
  endforeach;

  $response->status       = "success";
  $response->toastMessage = "Los números de serie están completos";

  return $response;
}

function purchase_add(
  $parameters,
  $udpateCostPrices = false
) {
  global $mysqli;
  global $db_dti;

  $response               = new stdClass();
  $response->status       = "error";
  $response->toastMessage = "";

  $calculate_product_totals = function ($product) {
    $quantity        = doubleval($product['cantidad'] ?? 0);
    $haveIvaValue    = $product['aplica_iva'] ?? false;
    $haveIepsValue   = $product['aplica_ieps'] ?? false;
    $haveIva         = ($haveIvaValue === true || $haveIvaValue === 1 || $haveIvaValue === '1' || $haveIvaValue === 'si');
    $haveIeps        = ($haveIepsValue === true || $haveIepsValue === 1 || $haveIepsValue === '1' || $haveIepsValue === 'si');
    $ivaPercentage   = doubleval($product['iva_porcentaje'] ?? 16);
    $iepsPercentage  = doubleval($product['ieps_porcentaje'] ?? 0);
    $price           = doubleval($product['precio_costo'] ?? 0);
    $discount        = doubleval($product['descuento'] ?? 0);

    $priceWithDiscount = $price - ($price * ($discount / 100));
    $ieps              = $haveIeps ? ($priceWithDiscount * ($iepsPercentage / 100)) : 0;
    $ivaBase           = $priceWithDiscount + $ieps;
    $iva               = $haveIva ? ($ivaBase * ($ivaPercentage / 100)) : 0;

    $unitSubtotal      = $priceWithDiscount * $quantity;
    $unitIeps          = $ieps * $quantity;
    $unitIva           = $iva * $quantity;
    $unitTotal         = ($priceWithDiscount + $ieps + $iva) * $quantity;

    return [
      'subtotal' => $unitSubtotal,
      'ieps'     => $unitIeps,
      'iva'      => $unitIva,
      'total'    => $unitTotal
    ];
  };

  $branchId           = $parameters->branchId;
  $purchaseOrderFolio = $parameters->purchaseOrderFolio;
  $supplierId         = $parameters->supplierId;
  $documentFolio      = $parameters->documentFolio;
  $documentDate       = $parameters->documentDate;
  $paymentMethod      = $parameters->paymentMethod;
  $paymentForm        = $parameters->paymentForm;
  $observations       = $parameters->observations;
  $cart               = $parameters->cart;

  $userId             = get_id_usuario();
  $folio              = get_purchase_folio($branchId);
  $movement           = TIPO_MOVIMIENTO_INCREMENTO;

  /**
   * Verificar si el carrito está vacío
   */
  $isListEmpty = isEmptyArray($cart);

  if ($isListEmpty) :
    $response->toastMessage = "El carrito está vacío";
    return $response;
  endif;

  /**
   * Validar los números de serie
   */
  $validateSerialNumbers = purchase_validate_serial_numbers($cart);

  if ($validateSerialNumbers->status === 'error') :
    $response->toastMessage = $validateSerialNumbers->toastMessage;
    return $response;
  endif;

  /**
   * Obtener los totales
   */
  $subtotal = 0;
  $totalIeps = 0;
  $totalIva = 0;
  $total    = 0;

  foreach ($cart as $product) :
    $productTotals = $calculate_product_totals($product);

    $subtotal     += $productTotals['subtotal'];
    $totalIeps    += $productTotals['ieps'];
    $totalIva     += $productTotals['iva'];
    $total        += $productTotals['total'];
  endforeach;

  /**
   * Agregar la orden de compra
   */
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
      subtotal,
      iva,
      ieps,
      total
    ) VALUES (
      {$userId},
      {$branchId},
      {$supplierId},
      '{$folio}',
      '{$purchaseOrderFolio}',
      '{$documentFolio}',
      '{$documentDate}',
      '{$paymentMethod}',
      '{$paymentForm}',
      '{$observations}',
      '{$movement}',
      {$subtotal},
      {$totalIva},
      {$totalIeps},
      {$total}
    )
  ";

  $queryResult = mysqli_query($mysqli, $query);

  if ($queryResult) :
    $purchaseId = mysqli_insert_id($mysqli);
    $branchData = getBranchOfficeData($branchId);
    $action     = ACCION_NUEVA_COMPRA . " en {$branchData['nombre_sucursal']}";

    /**
     * Agregar los productos de la orden de compra
     */
    foreach ($cart as $product) :
      $productId      = $product['id_producto'];
      $productName    = $product['nombre_producto'];
      $quantity       = $product['cantidad'];
      $originalPrice  = $product['precio_original'];
      $costPrice      = $product['precio_costo'];
      $typeId         = $product['id_tipo'];
      $type           = $product['tipo'];
      $productTotal   = $product['cantidad'] * $product['precio_costo'];
      $inputUnit      = $product["unidad_entrada"];
      $piecesNumber   = $product["numero_piezas"];
      $requiresSerialNumber = $product['requiere_numero_serie'];

      if ($inputUnit == 'caja') :
        $quantity = $quantity * $piecesNumber;
        $product["cantidad"] = $quantity;

        $product["precio_costo"]  = round($costPrice / $piecesNumber, DECIMALS_CURRENCY);
        $originalPrice            = round($originalPrice / $piecesNumber, DECIMALS_CURRENCY);
      endif;

      $quantity       = $product['cantidad'];
      $haveIvaValue   = $product['aplica_iva'] ?? false;
      $haveIepsValue  = $product['aplica_ieps'] ?? false;
      $haveIva        = ($haveIvaValue === true || $haveIvaValue === 1 || $haveIvaValue === '1' || $haveIvaValue === 'si');
      $haveIeps       = ($haveIepsValue === true || $haveIepsValue === 1 || $haveIepsValue === '1' || $haveIepsValue === 'si');
      $iepsPercentage = $product['ieps_porcentaje'] ?? 0;
      $price          = $product['precio_costo'];
      $discount       = $product['descuento'];

      $productTotals  = $calculate_product_totals($product);
      $unitSubtotal   = $productTotals['subtotal'];
      $unitIeps       = $productTotals['ieps'];
      $unitIva        = $productTotals['iva'];
      $unitTotal      = $productTotals['total'];

      $haveIvaStr     = $haveIva ? 'si' : 'no';
      $haveIepsStr    = $haveIeps ? 'si' : 'no';

      addLogInKardex(
        $branchId,
        $product,
        $action,
        $movement,
        'compra'
      );

      $productsQuery = "INSERT INTO {$db_dti}_compra_productos (
          id_compra,
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
          total,
          id_tipo,
          tipo
        ) VALUES (
          '{$purchaseId}',
          '{$productId}',
          '{$productName}',
          '{$quantity}',
          '{$originalPrice}',
          '{$price}',
          '{$haveIvaStr}',
          '{$haveIepsStr}',
          '{$iepsPercentage}',
          '{$unitSubtotal}',
          '{$unitIva}',
          '{$unitIeps}',
          '{$discount}',
          '{$unitTotal}',
          '{$typeId}',
          '{$type}'
        )
      ";

      $productsQueryResult = mysqli_query($mysqli, $productsQuery);

      if ($productsQueryResult && $requiresSerialNumber) :
        $serialNumbersRows  = "";
        $serialNumbers      = $product['serial_numbers'];
        $counter            = 0;

        foreach ($serialNumbers as $serialNumber) :
          $concat = $counter > 0 ? ", " : "";
          $serialNumbersRows .= "{$concat}({$branchId}, {$productId}, '{$folio}', '{$serialNumber->number}')";
          $counter++;
        endforeach;

        $serialNumbersQuery = "INSERT INTO {$db_dti}_producto_numeros_serie (
            id_sucursal,
            id_producto,
            folio_compra,
            numero_serie
          ) VALUES
          {$serialNumbersRows}
        ";

        mysqli_query($mysqli, $serialNumbersQuery);
      endif;
    endforeach;

    $response->status       = "success";
    $response->toastMessage = "La compra se realizó correctamente";
    $response->purchaseId   = $purchaseId;
    $response->ticket       = BASE_URL . "/pdf-compra.php?uid={$purchaseId}";
  endif;

  if ($udpateCostPrices) {
    foreach ($cart as $product) {
      $productId   = $product['id_producto'];
      $costPrice   = floatval($product['precio_costo'] ?? 0);
      $haveIvaValue  = $product['aplica_iva'] ?? false;
      $haveIepsValue = $product['aplica_ieps'] ?? false;
      $haveIva       = ($haveIvaValue === true || $haveIvaValue === 1 || $haveIvaValue === '1' || $haveIvaValue === 'si');
      $haveIeps      = ($haveIepsValue === true || $haveIepsValue === 1 || $haveIepsValue === '1' || $haveIepsValue === 'si');
      $ivaRate     = $haveIva ? (doubleval($product['iva_porcentaje'] ?? 16) / 100) : 0;
      $iepsRate    = $haveIeps ? (doubleval($product['ieps_porcentaje'] ?? 0) / 100) : 0;

      $costPriceWithIEPS = $costPrice + ($costPrice * $iepsRate);
      $costPriceWithTaxes = $costPriceWithIEPS + ($costPriceWithIEPS * $ivaRate);

      $updateQuery = "UPDATE
          {$db_dti}_productos 
        SET
          precio_costo_original = {$costPrice},
          precio_costo          = {$costPriceWithTaxes}
        WHERE
          id_producto = {$productId}
      ";

      mysqli_query($mysqli, $updateQuery);
    }
  }

  return $response;
}
