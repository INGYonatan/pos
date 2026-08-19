<?php
function get_quote_folio(
  $id_sucursal
) {
  global $mysqli;
  global $db_dti;

  $mark = "CT{$id_sucursal}-";

  $today_date = date('Ymd');
  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_cotizaciones
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

function get_quote_data(
  $quote_id,
  $branch_id = null
) {
  global $mysqli;
  global $db_ati;
  global $db_dti;

  if (!$quote_id) return false;

  $days_to_expired = QUOTE_DAYS_TO_EXPIRED;

  $query = "SELECT
      CT.id_cotizacion,
      CT.id_usuario,
      CT.id_sucursal,
      CT.id_cliente,
      CT.folio,
      CT.observaciones,
      CT.tipo,
      CT.subtotal,
      CT.iva,
      CT.ieps,
      CT.redondeo,
      CT.total,
      CT.ediciones,
      CT.status,
      CT.fecha_creacion,
      CT.status AS status_cotizacion,
      DATE_FORMAT(CT.fecha_creacion, '%d-%m-%Y')                                            AS fecha_creacion_formato,
      DATE_ADD(CT.fecha_creacion, INTERVAL {$days_to_expired} DAY)                          AS fecha_expiracion,
      DATE_FORMAT(DATE_ADD(CT.fecha_creacion, INTERVAL {$days_to_expired} DAY), '%d-%m-%Y') AS fecha_expiracion_formato,
      U.nombre_completo,
      U.correo,
      C.nombre_completo AS cliente_nombre,
      C.correo          AS cliente_correo,
      C.telefono        AS cliente_telefono
    FROM
      {$db_dti}_cotizaciones AS CT
    LEFT JOIN
      {$db_ati}_usuarios AS U ON (U.id_usuario = CT.id_usuario)
    LEFT JOIN
      {$db_dti}_clientes AS C ON (C.id_cliente = CT.id_cliente)
    WHERE
      CT.id_cotizacion = ?
    LIMIT 1
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $quote_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return false;

  $data   = mysqli_fetch_assoc($query_result);
  $quote  = new stdClass();

  $quote->id                  = $data['id_cotizacion'];
  $quote->user_id             = $data['id_usuario'];
  $quote->branch_id           = $data['id_sucursal'];
  $quote->customer_id         = $data['id_cliente'];
  $quote->folio               = $data['folio'];
  $quote->observations        = $data['observaciones'];
  $quote->type                = $data['tipo'];
  $quote->subtotal            = $data['subtotal'];
  $quote->iva                 = $data['iva'];
  $quote->ieps                = $data['ieps'] ?? 0;
  $quote->rounding            = $data['redondeado'];
  $quote->status              = $data['status'];
  $quote->date                = $data['fecha_creacion'];
  $quote->date_format         = $data['fecha_creacion_formato'];
  $quote->date_expired        = $data['fecha_expiracion'];
  $quote->date_expired_format = $data['fecha_expiracion_formato'];
  $quote->quoteStatus         = $data['status_cotizacion'];

  $quote->seller              = new stdClass();
  $quote->seller->id          = $data['id_usuario'];
  $quote->seller->name        = $data['nombre_completo'];
  $quote->seller->email       = $data['correo'];

  $quote->customer            = new stdClass();
  $quote->customer->id        = $data['id_cliente'];
  $quote->customer->name      = $data['cliente_nombre'];
  $quote->customer->email     = $data['cliente_correo'];
  $quote->customer->phone     = $data['cliente_telefono'];

  $quote->cost_subtotal       = 0;
  $quote->cost_iva            = 0;
  $quote->cost_rounding       = 0;
  $quote->cost_total          = 0;

  $quote->sale_subtotal       = $data['subtotal'];
  $quote->sale_iva            = $data['iva'];
  $quote->sale_ieps           = $data['ieps'] ?? 0;
  $quote->sale_rounding       = $data['redondeo'];
  $quote->sale_total          = $data['total'];

  $quote->list                = get_quote_products($quote_id, $branch_id);

  return $quote;
}

function get_quote_products(
  $quote_id,
  $branch_id = null
) {
  global $mysqli;
  global $db_dti;

  if (!$quote_id) return [];

  $join_branch = "CT.id_sucursal";

  if ($branch_id) $join_branch = $branch_id;

  $query = "SELECT
      CTP.id_cotizacion_producto,
      CTP.id_cotizacion,
      CTP.id_producto,
      CTP.nombre_producto,
      CTP.precio_venta,
      CTP.cantidad_mayoreo,
      CTP.precio_mayoreo,
      CTP.aplica_iva,
      CTP.aplica_ieps,
      CTP.ieps_porcentaje,
      CTP.precio_venta_base,
      CTP.cantidad,
      CTP.precio,
      CTP.iva,
      CTP.ieps,
      CTP.descuento,
      CTP.precio_neto,
      CTP.subtotal,
      CTP.total,
      CTP.cancelado,
      CTP.comentarios,
      P.id_categoria_familia,
      P.codigo,
      P.unidad,
      P.contenido,
      P.tipo,
      I.stock,
      P.control_inventario,
      CF.limite_descuento,

      P.precio_venta AS sale_price1,
      P.precio_venta2 AS sale_price2,
      P.precio_venta3 AS sale_price3,

      P.precio_venta_original AS sale_price1_original,
      P.precio_venta2_original AS sale_price2_original,
      P.precio_venta3_original AS sale_price3_original,
      
      P.id_tipo,
      T.nombre AS tipo,
      T.requiere_numero_serie
    FROM
      {$db_dti}_cotizacion_productos AS CTP
    LEFT JOIN
      {$db_dti}_cotizaciones AS CT ON (CT.id_cotizacion = CTP.id_cotizacion)
    LEFT JOIN
      {$db_dti}_productos AS P ON (P.id_producto = CTP.id_producto)
    LEFT JOIN
      {$db_dti}_categoria_familias AS CF ON (P.id_categoria_familia  = CF.id_categoria_familia)
    LEFT JOIN
      {$db_dti}_inventario AS I ON (
        I.id_producto = CTP.id_producto AND
        I.id_sucursal = {$join_branch}
      )
    LEFT JOIN
      {$db_dti}_tipos AS T ON (P.id_tipo = T.id_tipo)
    WHERE
        CTP.id_cotizacion = ?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $quote_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return [];

  $list = [];

  while ($data = mysqli_fetch_assoc($query_result)) :
    $product = new stdClass();

    $product->id                            = $data['id_producto'];
    $product->code                          = $data['codigo'];
    $product->name                          = $data['nombre_producto'];
    $product->content                       = $data['contenido'];
    $product->stock                         = $data['stock'];
    $product->unit                          = $data['unidad'];
    $product->unit_symbol                   = $data['unidad'] == 'Pieza' ? 'pzs.' : 'kg.';
    $product->have_iva                      = $data['aplica_iva'];
    $product->have_ieps                     = $data['aplica_ieps'] ?? 'no';
    $product->ieps_percentage               = $data['ieps_porcentaje'] ?? 0;
    $product->wholesale_quantity            = $data['cantidad_mayoreo'];
    $product->wholesale_price               = $data['precio_mayoreo'];
    $product->sale_price                    = $data['precio_venta'];
    $product->cost_price                    = 0;
    $product->discount_limit                = $data['limite_descuento'];
    $product->category_family_id            = $data['id_categoria_familia'];
    $product->type                          = $data['tipo'];
    $product->type_id                       = $data['id_tipo'];
    $product->serial_numbers                = [];
    $product->requires_serial_number        = $data['requiere_numero_serie'];
    $product->inventory_control             = $data['control_inventario'];

    $product->sale_price1                   = $data['sale_price1'];
    $product->sale_price2                   = $data['sale_price2'];
    $product->sale_price3                   = $data['sale_price3'];

    $product->sale_price1_original          = $data['sale_price1_original'];
    $product->sale_price2_original          = $data['sale_price2_original'];
    $product->sale_price3_original          = $data['sale_price3_original'];

    $product->cart_quantity                 = removeTrailingZeros($data['cantidad']);

    $product->cart_base_sale_price          = $data['precio_venta_base'];
    $product->cart_sale_price               = $data['precio'];
    $product->cart_sale_iva                 = $data['iva'];
    $product->cart_sale_ieps                = $data['ieps'] ?? 0;
    $product->cart_sale_price_with_iva      = ($data['precio'] + $data['iva']);
    $product->cart_sale_discount            = $data['descuento'];
    $product->cart_sale_net_price           = $data['precio_neto'];
    $product->cart_sale_amount              = $data['total'];
    $product->cart_sale_amount_without_iva  = $data['subtotal'];
    $product->cart_sale_total_iva           = $data['iva'] * $data['cantidad'];
    $product->cart_sale_total_ieps          = ($data['ieps'] ?? 0) * $data['cantidad'];
    $product->comments                          = $data['comentarios'] ?? '';

    $product->cart_base_cost_price          = 0;
    $product->cart_cost_price               = 0;
    $product->cart_cost_iva                 = 0;
    $product->cart_cost_price_with_iva      = 0;
    $product->cart_cost_discount            = 0;
    $product->cart_cost_net_price           = 0;
    $product->cart_cost_amount              = 0;
    $product->cart_cost_amount_without_iva  = 0;
    $product->cart_cost_total_iva           = 0;

    $products[$data['id_producto']]         = $product;
  endwhile;

  return $products;
}

function get_quote_edit_counter(
  $quote_id
) {
  global $mysqli;
  global $db_dti;

  if (!$quote_id) return false;

  $query = "SELECT
      ediciones
    FROM
      {$db_dti}_cotizaciones
    WHERE
      id_cotizacion = ?
  ";

  $stmt = $mysqli->prepare($query);

  $stmt->bind_param('i', $quote_id);
  $stmt->execute();

  $result   = $stmt->get_result();
  $num_rows = $result->num_rows;

  if ($num_rows == 0) return false;

  $data         = $result->fetch_assoc();
  $edit_counter = $data['ediciones'];

  return $edit_counter;
}

function increase_quote_edit_counter(
  $quote_id
) {
  global $mysqli;
  global $db_dti;

  if (!$quote_id) return false;

  $edit_counter = get_quote_edit_counter($quote_id);
  $new_counter  = $edit_counter + 1;

  $query = "UPDATE {$db_dti}_cotizaciones SET
      ediciones = ?
    WHERE
      id_cotizacion = ?
  ";

  $stmt = $mysqli->prepare($query);

  $stmt->bind_param('ii', $new_counter, $quote_id);
  $result = $stmt->execute();

  return $result;
}

function get_quote_details_table(
  $quote_id
) {

  if (!$quote_id) return false;

  $quote    = get_quote_data($quote_id);
  $rows     = '';
  $colspan  = 8;

  foreach ($quote->list as $key => $product) :
    $code           = $product->code;
    $name           = $product->name;
    $stock          = $product->stock;
    $quantity       = $product->cart_quantity;
    $price          = $product->cart_sale_price;
    $ieps           = $product->cart_sale_ieps ?? 0;
    $iva            = $product->cart_sale_iva;
    $discount       = $product->cart_sale_discount;
    $net_price      = $product->cart_sale_net_price;
    $amount         = $product->cart_sale_amount_without_iva;
    $unit_symbol    = $product->unit_symbol;
    $comments       = $product->comments ?? '';

    if ($comments) $name .= '<br><small class="text-muted">' . $comments . '</small>';

    $rows .= '
      <tr>
        <td>' . $code . '</td>
        <td>' . $name . '</td>
        <td>' . format_decimal_number($quantity, DECIMALS_CURRENCY_TICKET) . ' ' . $unit_symbol . '</td>
        <td class="text-end">$' . number_format($price, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . number_format($ieps, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . number_format($iva, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . format_decimal_number($discount, DECIMALS_CURRENCY_TICKET) . '%</td>
        <td class="text-end">$' . format_decimal_number($net_price, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . format_decimal_number($amount, DECIMALS_CURRENCY_TICKET) . '</td>
      </tr>
    ';
  endforeach;

  $table = '
    <table class="table table-sm table-bordered table-hover">
      <thead>
        <tr class="table-dark">
          <th>#</th>
          <th>Producto</th>
          <th>Cant</th>
          <th class="text-end">Precio</th>
          <th class="text-end">IEPS</th>
          <th class="text-end">IVA</th>
          <th class="text-end">Descuento</th>
          <th class="text-end">Precio Neto</th>
          <th class="text-end">Importe (Sin Impuesto)</th>
        </tr>
      </thead>

      <tbody>
        ' . $rows . '

        <tr>
          <td class="text-end fw-bold" colspan="' . $colspan . '">Subtotal:</td>
          <td class="text-end">$' . number_format($quote->sale_subtotal, DECIMALS_CURRENCY_TICKET) . '</td>
        </tr>

        <tr>
          <td class="text-end fw-bold" colspan="' . $colspan . '">IEPS:</td>
          <td class="text-end">$' . number_format($quote->sale_ieps ?? 0, DECIMALS_CURRENCY_TICKET) . '</td>
        </tr>

        <tr>
          <td class="text-end fw-bold" colspan="' . $colspan . '">IVA:</td>
          <td class="text-end">$' . number_format($quote->sale_iva, DECIMALS_CURRENCY_TICKET) . '</td>
        </tr>

        <tr>
          <td class="text-end fw-bold" colspan="' . $colspan . '">Redondeo:</td>
          <td class="text-end">$' . number_format($quote->sale_rounding, DECIMALS_CURRENCY_TICKET) . '</td>
        </tr>

        <tr>
          <td class="text-end text-success fw-bold" colspan="' . $colspan . '">Total:</td>
          <td class="text-end text-success fw-bold"><u>$' . number_format($quote->sale_total, DECIMALS_CURRENCY_TICKET) . '</u></td>
        </tr>
      </tbody>
    </table>
  ';

  return $table;
}
