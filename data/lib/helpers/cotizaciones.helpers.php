<?php
function get_quote_data(
  $quote_id
) {
  global $mysqli;
  global $db_dti;

  if (!$quote_id) return false;

  $query = "SELECT
      id_cotizacion,
      id_usuario,
      id_sucursal,
      id_cliente,
      folio,
      observaciones,
      tipo,
      subtotal,
      iva,
      redondeo,
      total,
      status,
      fecha_creacion
    FROM
      {$db_dti}_cotizaciones
    WHERE
      id_cotizacion = ?
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

  $quote->id            = $data['id_cotizacion'];
  $quote->user_id       = $data['id_usuario'];
  $quote->branch_id     = $data['id_sucursal'];
  $quote->customer_id   = $data['id_cliente'];
  $quote->folio         = $data['folio'];
  $quote->observations  = $data['observaciones'];
  $quote->type          = $data['tipo'];
  $quote->subtotal      = $data['subtotal'];
  $quote->iva           = $data['iva'];
  $quote->rounding      = $data['redondeado'];
  $quote->status        = $data['status'];
  $quote->date          = $data['fecha_creacion'];

  $quote->cost_subtotal = 0;
  $quote->cost_iva      = 0;
  $quote->cost_rounding = 0;
  $quote->cost_total    = 0;

  $quote->sale_subtotal = $data['subtotal'];
  $quote->sale_iva      = $data['iva'];
  $quote->sale_rounding = $data['redondeo'];
  $quote->sale_total    = $data['total'];

  $quote->list          = get_quote_products($quote_id);

  return $quote;
}

function get_quote_products(
  $quote_id
) {
  global $mysqli;
  global $db_dti;

  if (!$quote_id) return [];

  $query = "SELECT
      CP.id_cotizacion_producto,
      CP.id_cotizacion,
      CP.id_producto,
      CP.nombre_producto,
      CP.cantidad,
      CP.aplica_iva,
      CP.precio_original,
      CP.precio_venta,
      CP.descuento,
      CP.cantidad_mayoreo,
      CP.precio_mayoreo,
      CP.iva,
      CP.subtotal,
      CP.total,
      CP.comentarios,
      P.codigo,
      P.unidad,
      P.contenido
    FROM
      {$db_dti}_cotizacion_productos AS CP
    LEFT JOIN
      {$db_dti}_productos AS P ON (P.id_producto = CP.id_producto)
    WHERE
      CP.id_cotizacion = ?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $quote_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return [];

  $products = [];

  while ($row = mysqli_fetch_assoc($query_result)) :
    $product = new stdClass();

    $product->product_quote_id    = $row['id_cotizacion_producto'];
    $product->id                  = $row['id_producto'];
    $product->code                = $row['codigo'];
    $product->name                = $row['nombre_producto'];
    $product->content             = $row['contenido'];
    $product->stock               = $row['cantidad'];
    $product->unit                = $row['unidad'];
    $product->unit_symbol         = $row['unidad'] == 'Pieza' ? 'pzs.' : 'kg.';
    $product->have_iva            = $row['aplica_iva'];
    $product->wholesale_quantity  = $row['cantidad_mayoreo'];
    $product->wholesale_price     = $row['precio_mayoreo'];
    $product->origin_sale_price   = $row['precio_original'];
    $product->sale_price          = $row['precio_venta'];
    $product->cost_price          = 0;

    $product->quantity            = format_decimal_number($row['cantidad'], 3);

    $product->cost_iva              = 0;
    $product->cost_total_iva        = 0;
    $product->cost_without_iva      = 0;
    $product->cost_amount           = 0;
    $product->cost_amount_with_iva  = 0;

    $product->sale_iva              = $row['iva'] / $row['cantidad'];
    $product->sale_total_iva        = $row['iva'];
    $product->sale_without_iva      = $row['precio_venta'];
    $product->sale_amount           = $row['subtotal'];
    $product->sale_amount_with_iva  = $row['total'];

    $product->discount              = $row['descuento'];
    $product->net_price             = $row['total'] / $row['cantidad'];
    $product->comments              = $row['comentarios'] ?? '';

    #array_push($products, $product);
    $products[$row['id_producto']] = $product;
  endwhile;

  return $products;
}

function get_quote_details_table(
  $quote_id
) {

  if (!$quote_id) return false;

  $quote  = get_quote_data($quote_id);
  $rows   = '';

  foreach ($quote->list as $key => $product) :
    $name = $product->name;
    $comments = $product->comments ?? '';

    if ($comments) $name .= '<br><small class="text-muted">' . $comments . '</small>';

    $rows .= '
      <tr>
      <td>' . $product->code . '</td>
      <td>' . $name . '</td>
      <td>' . format_decimal_number($product->quantity, DECIMALS_CURRENCY_TICKET) . '</td>
        <td>$' . format_decimal_number($product->sale_without_iva, DECIMALS_CURRENCY_TICKET) . '</td>
        <td>' . format_decimal_number($product->discount, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . format_decimal_number($product->sale_total_iva, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . format_decimal_number($product->sale_amount, DECIMALS_CURRENCY_TICKET) . '</td>
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
          <th>Precio</th>
          <th>Descuento</th>
          <th class="text-end">IVA</th>
          <th class="text-end">Importe (Sin IVA)</th>
        </tr>
      </thead>

      <tbody>
        ' . $rows . '

        <tr>
          <td class="text-end fw-bold" colspan="6">Subtotal:</td>
          <td class="text-end">$' . number_format($quote->sale_subtotal, DECIMALS_CURRENCY_TICKET) . '</td>
        </tr>

        <tr>
          <td class="text-end fw-bold" colspan="6">IVA:</td>
          <td class="text-end">$' . number_format($quote->sale_iva, DECIMALS_CURRENCY_TICKET) . '</td>
        </tr>

        <tr>
          <td class="text-end fw-bold" colspan="6">Redondeo:</td>
          <td class="text-end">$' . number_format($quote->sale_rounding, DECIMALS_CURRENCY_TICKET) . '</td>
        </tr>

        <tr>
          <td class="text-end text-success fw-bold" colspan="6">Total:</td>
          <td class="text-end text-success fw-bold"><u>$' . number_format($quote->sale_total, DECIMALS_CURRENCY_TICKET) . '</u></td>
        </tr>
      </tbody>
    </table>
  ';

  return $table;
}

function create_quote_cart(
  $quote_id
) {
  if (!$quote_id) return false;

  $quote  = get_quote_data($quote_id);
  $cart   = new stdClass();
  $cart   = $quote;

  return $cart;
};

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
