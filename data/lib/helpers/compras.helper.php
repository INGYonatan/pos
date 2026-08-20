<?php
function compra_get_by_id(
  $compra_id
) {
  global $mysqli;
  global $db_dti;
  global $db_ati;

  if (!$compra_id) return false;

  $query = "SELECT
        C.id_compra,
        C.id_usuario,
        C.id_sucursal,
        C.id_proveedor,
        C.folio,
        C.folio_documento,
        C.fecha_documento,
        DATE_FORMAT(C.fecha_documento, '%d-%m-%Y') AS fecha_documento_formato,
        C.metodo_pago,
        C.forma_pago,
        C.observaciones,
        C.tipo,
        C.subtotal,
        C.iva,
        C.ieps,
        C.total,
        C.fecha_creacion,
        DATE_FORMAT(C.fecha_creacion, '%d-%m-%Y') AS fecha_creacion_formato,
        U.nombre_completo AS vendedor_nombre,
        U.correo          AS vendedor_correo,
        P.nombre_comercial AS proveedor_nombre
      FROM
        {$db_dti}_compras AS C
      LEFT JOIN
        {$db_ati}_usuarios AS U ON (U.id_usuario = C.id_usuario)
      LEFT JOIN
        {$db_dti}_proveedores AS P ON (P.id_proveedor = C.id_proveedor)
      WHERE
        C.id_compra = ? AND
        C.status    != 'cancelado'
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $compra_id);

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
  $purchase->list                 = compra_get_products($purchase->id, $purchase->branch_id);

  $purchase->seller               = new stdClass();
  $purchase->seller->id           = $data['id_usuario'];
  $purchase->seller->name         = $data['vendedor_nombre'];
  $purchase->seller->email        = $data['vendedor_correo'];

  $purchase->supplier              = new stdClass();
  $purchase->supplier->id          = $data['id_proveedor'];
  $purchase->supplier->name        = $data['proveedor_nombre'];

  return $purchase;
}

function compra_get_products(
  $purchase_id,
  $branch_id
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
      CF.limite_descuento,
      C.folio
    FROM
      {$db_dti}_compra_productos AS CP
    LEFT JOIN
      {$db_dti}_productos AS P ON (P.id_producto = CP.id_producto)
    LEFT JOIN
      {$db_dti}_categoria_familias AS CF ON (CF.id_categoria_familia = P.id_categoria_familia)
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
    $product->unit_symbol     = $data['unidad'] == 'Pieza' ? 'pzs.' : 'kg.';
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

    $serial_numbers           = compra_get_serial_numbers($data['id_producto'], $data["folio"]);

    if (count($serial_numbers) == 0) :
      $product->noSerial = true;

      for ($i = 1; $i <= $product->quantity; $i++) :
        $serial_number          = new stdClass();
        $serial_number->id      = "";
        $serial_number->number  = "";

        array_push($serial_numbers, $serial_number);
      endfor;
    endif;

    $product->serial_numbers  = $serial_numbers;

    $products[$product->id]   = $product;
  endwhile;

  return $products;
}

function compra_get_serial_numbers(
  $id_producto,
  $folio_compra
) {
  global $mysqli;
  global $db_dti;

  $serial_numbers = [];

  $query = "SELECT
      id_producto_numero_serie,
      id_producto,
      numero_serie
    FROM
      {$db_dti}_producto_numeros_serie
    WHERE
      id_producto   = ? AND
      folio_compra  = ?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('is', $id_producto, $folio_compra);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $serial_number          = new stdClass();
      $serial_number->id      = $row['id_producto_numero_serie'];
      $serial_number->number  = $row['numero_serie'];

      array_push($serial_numbers, $serial_number);
    endwhile;
  endif;

  return $serial_numbers;
}
