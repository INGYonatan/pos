<?php
function purchase_order_get_folio(
  $id_sucursal
) {
  global $mysqli;
  global $db_dti;

  $mark = "OC{$id_sucursal}-";

  $today_date = date('Ymd');
  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_ordenes_compra
    WHERE
      id_sucursal           = {$id_sucursal} AND
      YEAR(fecha_creacion)  = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  if (!$query_get_folio_num_rows) return $mark . '00001-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;
    $num_folio_length = strlen($new_num);
    $new_num_folio = '';

    if ($num_folio_length === 1) $new_num_folio = '0000' . $new_num;
    if ($num_folio_length === 2) $new_num_folio = '000' . $new_num;
    if ($num_folio_length === 3) $new_num_folio = '00' . $new_num;
    if ($num_folio_length === 4) $new_num_folio = '0' . $new_num;
    if ($num_folio_length === 5) $new_num_folio = $new_num;

    $new_folio = $mark . $new_num_folio . '-' . $today_year;

    return $new_folio;
  }
}

function purchase_order_get_by_id(
  $purchase_id
) {
  global $mysqli;
  global $db_dti;
  global $db_ati;

  if (!$purchase_id) return false;

  $query = "SELECT
      OC.id_orden_compra,
      OC.id_usuario,
      OC.id_sucursal,
      OC.id_proveedor,
      OC.folio,
      OC.folio_documento,
      OC.fecha_documento,
      DATE_FORMAT(OC.fecha_documento, '%d-%m-%Y') AS fecha_documento_formato,
      OC.metodo_pago,
      OC.forma_pago,
      OC.observaciones,
      OC.tipo,
      OC.subtotal,
      OC.iva,
      OC.ieps,
      OC.total,
      OC.fecha_creacion,
      DATE_FORMAT(OC.fecha_creacion, '%d-%m-%Y') AS fecha_creacion_formato,
      U.nombre_completo AS vendedor_nombre,
      U.correo          AS vendedor_correo
    FROM
      {$db_dti}_ordenes_compra AS OC
    LEFT JOIN
      {$db_ati}_usuarios AS U ON (U.id_usuario = OC.id_usuario)
    WHERE
      OC.id_orden_compra = ? AND
      OC.status          != 'cancelado'
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $purchase_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return false;

  $data     = mysqli_fetch_assoc($query_result);
  $purchase = new stdClass();

  $purchase->id                   = $data['id_orden_compra'];
  $purchase->user_id              = $data['id_usuario'];
  $purchase->branch_id            = $data['id_sucursal'];
  $purchase->supplier_id          = $data['id_proveedor'];
  $purchase->folio                = $data['folio'];
  $purchase->document_folio       = $data['folio_documento'];
  $purchase->document_date        = $data['fecha_documento'];
  $purchase->document_date_format = $data['fecha_documento_formato'];
  $purchase->created_date         = $data["fecha_creacion"];
  $purchase->created_date_format  = $data["fecha_creacion_formato"];
  $purchase->payment_method       = $data['metodo_pago'];
  $purchase->payment_form         = $data['forma_pago'];
  $purchase->observations         = $data['observaciones'];
  $purchase->type                 = $data['tipo'];
  $purchase->subtotal             = $data['subtotal'];
  $purchase->iva                  = $data['iva'];
  $purchase->ieps                 = $data['ieps'] ?? 0;
  $purchase->total                = $data['total'];
  $purchase->list                 = purchase_order_get_products($purchase->id, $purchase->branch_id);

  $purchase->seller               = new stdClass();
  $purchase->seller->id           = $data['id_usuario'];
  $purchase->seller->name         = $data['vendedor_nombre'];
  $purchase->seller->email        = $data['vendedor_correo'];

  return $purchase;
}

function purchase_order_get_products(
  $purchase_id,
  $branch_id
) {
  global $mysqli;
  global $db_dti;

  if (!$purchase_id) return false;

  $query = "SELECT
      CP.id_orden_compra_producto,
      CP.id_orden_compra,
      CP.id_producto,
      CP.nombre_producto,
      CP.cantidad,
      CP.precio_original,
      CP.precio_costo,
      CP.aplica_iva,
      CP.aplica_ieps,
      CP.ieps_porcentaje,
      CP.descuento,
      CP.subtotal,
      CP.iva,
      CP.ieps,
      CP.total,
      CP.cancelado,
      P.codigo,
      P.unidad,
      P.unidad_entrada,
      P.contenido,
      I.stock,
      P.tipo,
      P.unidad_entrada,
      P.numero_piezas,
      CF.limite_descuento
    FROM
      {$db_dti}_orden_compra_productos AS CP
    LEFT JOIN
      {$db_dti}_productos AS P ON (P.id_producto = CP.id_producto)
    LEFT JOIN
      {$db_dti}_categoria_familias AS CF ON (CF.id_categoria_familia = P.id_categoria_familia)
    LEFT JOIN
      {$db_dti}_ordenes_compra AS C ON (C.id_orden_compra = CP.id_orden_compra)
    LEFT JOIN
      {$db_dti}_inventario AS I ON (
        I.id_producto = CP.id_producto AND
        I.id_sucursal = C.id_sucursal
      )
    WHERE
      CP.id_orden_compra = ?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $purchase_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return [];

  $products = [];

  while ($data = mysqli_fetch_assoc($query_result)) :
    $product = new stdClass();

    $product->id              = $data['id_producto'];
    $product->code            = $data['codigo'];
    $product->type            = $data['tipo'];
    $product->name            = $data['nombre_producto'];
    $product->content         = $data['contenido'];
    $product->stock           = $data['stock'];
    $product->quantity        = $data['cantidad'];
    $product->cancelled       = $data['cancelado'] === 'si' ? true : false;
    $product->unit            = $data['unidad'];
    $product->unit_symbol     = $data['unidad']/*  == 'Pieza' ? 'pzs.' : 'kg.' */;
    $product->inputUnit       = $data["unidad_entrada"];
    $product->original_price  = $data['precio_original'];
    $product->cost_price      = $data['precio_costo'];
    $product->inputUnit       = $data["unidad_entrada"];
    $product->piecesNumber    = $data["numero_piezas"];
    $product->limitDiscount   = $data['limite_descuento'];

    $product->price           = $data['precio_costo'];
    $product->discount        = $data['descuento'];
    $product->have_iva        = $data['aplica_iva'] === 'si' ? true : false;
    $product->have_ieps       = $data['aplica_ieps'] === 'si' ? true : false;
    $product->ieps_percentage = $data['ieps_porcentaje'] ?? 0;
    $product->subtotal        = $data['subtotal'];
    $product->iva             = $data['iva'];
    $product->ieps            = $data['ieps'] ?? 0;
    $product->total           = $data['total'];

    // $serial_numbers           = purchase_order_get_serial_numbers($data['id_orden_compra_producto']);

    // if (count($serial_numbers) == 0) :
    //   $product->noSerial = true;

    //   for ($i = 1; $i <= $product->quantity; $i++) :
    //     $serial_number          = new stdClass();
    //     $serial_number->id      = "";
    //     $serial_number->number  = "";

    //     array_push($serial_numbers, $serial_number);
    //   endfor;
    // endif;

    // $product->serial_numbers  = $serial_numbers;

    $products[$product->id]   = $product;
  endwhile;

  return $products;
}

function purchase_order_get_serial_numbers(
  $product_purchase_order_id
) {
  global $mysqli;
  global $db_dti;

  $serial_numbers = [];

  $query = "SELECT
      id_orden_compra_producto_numero_serie,
      id_orden_compra_producto,
      numero_serie
    FROM
      {$db_dti}_orden_compra_producto_numeros_serie
    WHERE
      id_orden_compra_producto = ?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $product_purchase_order_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $serial_number          = new stdClass();
      $serial_number->id      = $row['id_orden_compra_producto_numero_serie'];
      $serial_number->number  = $row['numero_serie'];

      array_push($serial_numbers, $serial_number);
    endwhile;
  endif;

  return $serial_numbers;
}

function purchase_order_details_table(
  $purchase_order_id
) {
  global $mysqli;
  global $db_dti;

  $productos = '';

  $query = "SELECT
      C.id_orden_compra_producto,
      C.id_orden_compra,
      C.id_producto,
      C.nombre_producto,
      C.cantidad,
      C.precio_costo,
      C.total,
      C.cancelado,
      P.unidad,
      P.codigo,
      P.unidad_entrada
    FROM
      {$db_dti}_orden_compra_productos AS C
    LEFT JOIN
      {$db_dti}_productos AS P ON (C.id_producto = P.id_producto)
    WHERE
      C.id_orden_compra = {$purchase_order_id}
  ";

  $query_result   = mysqli_query($mysqli, $query);
  $num_productos  = mysqli_num_rows($query_result);

  if ($num_productos > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : 'pzs.';
      // $unit_type = $row['unidad_entrada'] === 'caja' ? 'caj.' : $unit_type;

      $productos .= '
        <tr>
          <td>' . $row['codigo'] . '</td>
          <td>' . $row['nombre_producto'] . '</td>
          <td>' . (int) $row['cantidad'] . ' ' . $unit_type . '</td>
        </tr>
      ';
    endwhile;
  endif;

  return $productos;
}

function purchase_order_create_cart_edit(
  $purchase_id
) {
  if (!$purchase_id) return false;

  $purchase = purchase_order_get_by_id($purchase_id);

  if (!$purchase) return false;

  $cart = [];

  foreach ($purchase->list as $product) :
    $id             = $product->id;
    $code           = $product->code;
    $name           = $product->name;
    $stock          = $product->stock;
    $quantity       = $product->quantity;
    $have_iva       = $product->have_iva;
    $have_ieps      = $product->have_ieps ?? false;
    $iva_percentaje = $have_iva ? 16 : 0;
    $ieps_percentage = $have_ieps ? ($product->ieps_percentage ?? 0) : 0;
    $discount       = $product->discount;
    $discount_limit = $product->limitDiscount;
    $price          = $product->price;
    $entry_unit     = $product->inputUnit /* === 'caja' ? 'caj.' : 'pzs.' */;
    $pieces_number  = $product->piecesNumber;
    $type           = $product->type;
    $serial_numbers = $product->serial_numbers;

    $data = [
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

    $cart[$product->id] = $data;
  endforeach;

  return $cart;
}

function purchase_order_update(
  $parameters
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

  $branchId         = $parameters->branchId;
  $purchaseOrderId  = $parameters->purchaseOrderId;
  $supplierId       = $parameters->supplierId;
  $documentFolio    = $parameters->documentFolio;
  $documentDate     = $parameters->documentDate;
  $paymentMethod    = $parameters->paymentMethod;
  $paymentForm      = $parameters->paymentForm;
  $observations     = $parameters->observations;
  $cart             = $parameters->cart;

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
    if ($validateSerialNumbers->type === 'greater') :
      $response->toastMessage = $validateSerialNumbers->toastMessage;

      return $response;
    endif;
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
   * Actualizar la orden de compra
   */
  $query = "UPDATE {$db_dti}_ordenes_compra SET
      id_sucursal     = $branchId,
      id_proveedor    = $supplierId,
      folio_documento = '{$documentFolio}',
      fecha_documento = '{$documentDate}',
      metodo_pago     = '{$paymentMethod}',
      forma_pago      = '{$paymentForm}',
      observaciones   = '{$observations}',
      subtotal        = {$subtotal},
      iva             = {$totalIva},
      ieps            = {$totalIeps},
      total           = {$total}
    WHERE
      id_orden_compra = {$purchaseOrderId}
  ";

  $queryResult = mysqli_query($mysqli, $query);

  if ($queryResult) :
    /**
     * Eliminar los productos para agregarlos nuevamente con la nueva lista
     */
    $query = "DELETE FROM {$db_dti}_orden_compra_productos WHERE id_orden_compra = {$purchaseOrderId}";
    mysqli_query($mysqli, $query);

    /**
     * Eliminar los números de serie para agregarlos nuevamente
     */
    // $query = "DELETE FROM {$db_dti}_orden_compra_producto_numeros_serie WHERE id_orden_compra = {$purchaseOrderId}";
    // mysqli_query($mysqli, $query);

    /**
     * Agregar los productos de la orden de compra
     */
    foreach ($cart as $product) :
      $productId      = $product['id_producto'];
      $productName    = $product['nombre_producto'];
      $originalPrice  = $product['precio_original'];
      $type           = $product['tipo'];

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

      $haveIvaStr    = $haveIva ? 'si' : 'no';
      $haveIepsStr   = $haveIeps ? 'si' : 'no';

      $query = "INSERT INTO {$db_dti}_orden_compra_productos (
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
          {$purchaseOrderId},
          {$productId},
          '{$productName}',
          {$quantity},
          {$originalPrice},
          {$price},
          '{$haveIvaStr}',
          '{$haveIepsStr}',
          {$iepsPercentage},
          {$unitSubtotal},
          {$unitIva},
          {$unitIeps},
          {$discount},
          {$unitTotal}
        )
      ";

      mysqli_query($mysqli, $query);

      $productPurchaseOrderId = mysqli_insert_id($mysqli);

    // if ($type == 'equipo') :
    //   $serialNumbersRows  = "";
    //   $serialNumbers      = $product['serial_numbers'];
    //   $counter            = 0;

    //   foreach ($serialNumbers as $serialNumber) :
    //     $concat = $counter > 0 ? ", " : "";
    //     $serialNumbersRows .= "{$concat}({$productPurchaseOrderId}, {$purchaseOrderId}, '{$serialNumber->number}')";
    //     $counter++;
    //   endforeach;

    //   $serialNumbersQuery = "INSERT INTO {$db_dti}_orden_compra_producto_numeros_serie (
    //       id_orden_compra_producto,
    //       id_orden_compra,
    //       numero_serie
    //     ) VALUES 
    //     {$serialNumbersRows}
    //   ";

    //   mysqli_query($mysqli, $serialNumbersQuery);
    // endif;
    endforeach;
  endif;

  $response->status       = "success";
  $response->toastMessage = "La orden de compra se actualizó correctamente";

  return $response;
}
