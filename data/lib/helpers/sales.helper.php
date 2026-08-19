<?php
// function get_sale_folio(
//   $id_sucursal
// ) {
//   global $mysqli;
//   global $db_dti;

//   $mark = "V{$id_sucursal}-";

//   $today_date = date('Ymd');
//   $today_year = date('Y');

//   $query_get_folio = "SELECT
//       folio
//     FROM
//       {$db_dti}_ventas
//     WHERE
//       id_sucursal           = {$id_sucursal}  AND
//       YEAR(fecha_creacion)  = {$today_year}
//     ORDER BY folio
//     DESC
//     LIMIT 1
//   ";

//   $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
//   $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

//   if (!$query_get_folio_num_rows) return $mark . '0000001-' . $today_year;

//   if ($query_get_folio_num_rows) {
//     $quote_data = mysqli_fetch_assoc($query_get_folio_result);
//     $folio = $quote_data['folio'];

//     $folio = str_replace($mark, '', $folio);
//     $folio = str_replace('-' . $today_year, '', $folio);
//     $folio = ltrim($folio, '0');

//     $new_num = intval($folio) + 1;
//     $num_folio_length = strlen($new_num);
//     $new_num_folio = '';

//     if ($num_folio_length === 1) $new_num_folio = '000000' . $new_num;
//     if ($num_folio_length === 2) $new_num_folio = '00000' . $new_num;
//     if ($num_folio_length === 3) $new_num_folio = '0000' . $new_num;
//     if ($num_folio_length === 4) $new_num_folio = '000' . $new_num;
//     if ($num_folio_length === 5) $new_num_folio = '00' . $new_num;
//     if ($num_folio_length === 6) $new_num_folio = '0' . $new_num;
//     if ($num_folio_length >= 7) $new_num_folio = $new_num;

//     $new_folio = $mark . $new_num_folio . '-' . $today_year;

//     return $new_folio;
//   }
// }

function get_sale_folio(
  $id_sucursal
) {
  global $mysqli;
  global $db_dti;

  $mark = "V{$id_sucursal}-";

  $today_date = date('Ymd');
  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_ventas
    WHERE
      id_sucursal           = {$id_sucursal}  AND
      YEAR(fecha_creacion)  = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  if (!$query_get_folio_num_rows) return $mark . '0000001-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;
    $num_folio_length = strlen($new_num);
    $new_num_folio = '';

    // Pad the new number with leading zeros to ensure it is always 7 digits
    $new_num_folio = str_pad($new_num, 7, '0', STR_PAD_LEFT);

    $new_folio = $mark . $new_num_folio . '-' . $today_year;

    return $new_folio;
  }
}

function get_sale_data_by_folio(
  $folio
) {
  global $mysqli;
  global $db_ati;
  global $db_dti;

  if (!$folio) return false;

  $query = "SELECT id_venta FROM {$db_dti}_ventas WHERE folio = ? LIMIT 1";

  $stmt = $mysqli->prepare($query);

  $stmt->bind_param('s', $folio);
  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return false;

  $data = mysqli_fetch_assoc($query_result);

  $saleId = $data['id_venta'];

  $sale = get_sale_data($saleId);

  return $sale;
}

function get_sale_data(
  $sale_id,
  $branch_id = null
) {
  global $mysqli;
  global $db_ati;
  global $db_dti;

  if (!$sale_id) return false;

  $query = "SELECT
      V.id_venta,
      V.id_usuario,
      V.id_sucursal,
      V.id_cliente,
      V.id_cfdi,
      V.folio,
      V.observaciones,
      V.tipo,
      V.subtotal,
      V.iva,
      V.ieps,
      V.redondeo,
      V.total,
      V.pago_con,
      V.cambio,
      V.status,
      V.fecha_creacion,
      V.forma_pago,
      V.pagado,
      V.efectivo,
      V.cheque,
      V.transferencia,
      V.tarjeta_credito,
      V.tarjeta_debito,
      V.tipo_transaccion,
      DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y') AS fecha_creacion_formato,
      DATE_FORMAT(V.fecha_creacion, '%h:%i %p') AS hora_creacion_formato,
      U.nombre_completo,
      U.correo,
      C.nombre_completo   AS cliente_nombre,
      C.correo            AS cliente_correo,
      C.telefono          AS cliente_telefono,

      C.razon_social  AS cliente_nombre_comercial,
      C.rfc               AS cliente_rfc,
      C.id_regimen_fiscal AS cliente_id_regimen_fiscal,
      C.domicilio_fiscal  AS cliente_domicilio_fiscal,

      S.nombre_sucursal   AS sucursal_nombre,
      S.direccion         AS sucursal_direccion,
      S.numero_serie      AS sucursal_numero_serie
    FROM
      {$db_dti}_ventas AS V
    LEFT JOIN
      {$db_dti}_sucursales AS S ON (S.id_sucursal = V.id_sucursal)
    LEFT JOIN
      {$db_ati}_usuarios AS U ON (U.id_usuario = V.id_usuario)
    LEFT JOIN
      {$db_dti}_clientes AS C ON (C.id_cliente = V.id_cliente)
    WHERE
      V.id_venta = ?
    LIMIT 1
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $sale_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return false;

  $data   = mysqli_fetch_assoc($query_result);
  $sale  = new stdClass();

  $sale->id                     = $data['id_venta'];
  $sale->user_id                = $data['id_usuario'];
  $sale->branch_id              = $data['id_sucursal'];
  $sale->customer_id            = $data['id_cliente'];
  $sale->cfdi_id                = $data['id_cfdi'];
  $sale->folio                  = $data['folio'];
  $sale->observations           = $data['observaciones'];
  $sale->type                   = $data['tipo'];
  $sale->subtotal               = $data['subtotal'];
  $sale->iva                    = $data['iva'];
  $sale->rounding               = $data['redondeado'];
  $sale->status                 = $data['status'];
  $sale->date                   = $data['fecha_creacion'];
  $sale->date_format            = $data['fecha_creacion_formato'];
  $sale->time_format            = $data['hora_creacion_formato'];
  $sale->payWith                = $data['pago_con'];
  $sale->exchange               = $data['cambio'];
  $sale->paid                   = $data['pagado'];
  $sale->payment_form           = $data['forma_pago'];
  $sale->efectivo                = $data['efectivo'];
  $sale->cheque                 = $data['cheque'];
  $sale->transferencia          = $data['transferencia'];
  $sale->tarjeta_credito        = $data['tarjeta_credito'];
  $sale->tarjeta_debito         = $data['tarjeta_debito'];
  $sale->transaction_type       = $data['tipo_transaccion'];

  $sale->branch                 = new stdClass();
  $sale->branch->name           = $data['sucursal_nombre'];
  $sale->branch->address        = $data['sucursal_direccion'];
  $sale->branch->serial_number  = $data['sucursal_numero_serie'];

  $sale->seller                 = new stdClass();
  $sale->seller->id             = $data['id_usuario'];
  $sale->seller->name           = $data['nombre_completo'];
  $sale->seller->email          = $data['correo'];

  $sale->customer               = new stdClass();
  $sale->customer->id           = $data['id_cliente'];
  $sale->customer->name         = $data['cliente_nombre'];
  $sale->customer->email        = $data['cliente_correo'];
  $sale->customer->phone        = $data['cliente_telefono'];

  $sale->customer->business_name  = $data['cliente_nombre_comercial'];
  $sale->customer->rfc            = $data['cliente_rfc'];
  $sale->customer->fiscal_address = $data['cliente_domicilio_fiscal'];
  $sale->customer->fiscal_regime  = $data['cliente_id_regimen_fiscal'];

  $sale->cost_subtotal          = 0;
  $sale->cost_iva               = 0;
  $sale->cost_rounding          = 0;
  $sale->cost_total             = 0;

  $sale->sale_subtotal          = $data['subtotal'];
  $sale->sale_iva               = $data['iva'];
  $sale->sale_ieps              = $data['ieps'] ?? 0;
  $sale->sale_rounding          = $data['redondeo'];
  $sale->sale_total             = $data['total'];

  $sale->list                   = get_sale_products($sale_id, $branch_id);

  return $sale;
}

function get_sale_products(
  $sale_id,
  $branch_id = null
) {
  global $mysqli;
  global $db_dti;

  if (!$sale_id) return [];

  $join_branch = "V.id_sucursal";

  if ($branch_id) $join_branch = $branch_id;

  $query = "SELECT
      VP.id_venta_producto,
      VP.id_venta,
      VP.id_producto,
      VP.nombre_producto,
      VP.precio_venta,
      VP.cantidad_mayoreo,
      VP.precio_mayoreo,
      VP.aplica_iva,
      VP.aplica_ieps,
      VP.ieps_porcentaje,
      VP.precio_venta_base,
      VP.cantidad,
      VP.precio,
      VP.iva,
      VP.ieps,
      VP.descuento,
      VP.precio_neto,
      VP.subtotal,
      VP.total,
      VP.cancelado,
      VP.comentarios,
      P.codigo,
      P.unidad,
      P.contenido,
      P.id_clave_unidad,
      P.id_clave_producto_servicio,
      CU.nombre       AS clave_unidad_nombre,
      CPS.descripcion AS clave_producto_servicio_descripcion,
      I.stock
    FROM
      {$db_dti}_venta_productos AS VP
    LEFT JOIN
      {$db_dti}_ventas AS V ON (V.id_venta = VP.id_venta)
    LEFT JOIN
      {$db_dti}_productos AS P ON (P.id_producto = VP.id_producto)
    LEFT JOIN
      {$db_dti}_clave_unidades AS CU ON (CU.id_clave_unidad = P.id_clave_unidad)
    LEFT JOIN
      {$db_dti}_clave_producto_servicios AS CPS ON (CPS.id_clave_producto_servicio = P.id_clave_producto_servicio)
    LEFT JOIN
      {$db_dti}_inventario AS I ON (
        I.id_producto = VP.id_producto AND
        I.id_sucursal = {$join_branch}
      )
    WHERE
        VP.id_venta = ?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $sale_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return [];

  $list = [];

  while ($data = mysqli_fetch_assoc($query_result)) :
    $product = new stdClass();

    $product->id                            = $data['id_producto'];
    $product->unitMeasurementId             = $data['id_clave_unidad'];
    $product->keyProductServiceId           = $data['id_clave_producto_servicio'];
    $product->unitMeasurementName           = $data['clave_unidad_nombre'];
    $product->keyProductServiceName         = $data['clave_producto_servicio_descripcion'];

    $product->code                          = $data['codigo'];
    $product->name                          = $data['nombre_producto'];
    $product->content                       = $data['contenido'];
    $product->stock                         = $data['stock'];
    $product->cancelled                     = $data['cancelado'] === 'si' ? true : false;
    $product->unit                          = $data['unidad'];
    $product->unit_symbol                   = $data['unidad'] == 'Pieza' ? 'pzs.' : 'kg.';
    $product->have_iva                      = $data['aplica_iva'];
    $product->wholesale_quantity            = $data['cantidad_mayoreo'];
    $product->wholesale_price               = $data['precio_mayoreo'];
    $product->sale_price                    = $data['precio_venta'];
    $product->cost_price                    = 0;

    $product->cart_quantity                 = removeTrailingZeros($data['cantidad']);

    $product->cart_base_sale_price          = $data['precio_venta_base'];
    $product->cart_sale_price               = $data['precio'];
    $product->cart_sale_iva                 = $data['iva'];
    $product->cart_sale_ieps                = $data['ieps'] ?? 0;

    // // ✅ CORRECCIÓN: Recalcular precio sin IVA correctamente desde el total con IVA
    // if ($data['aplica_iva'] == 'si' && $data['total'] > 0 && $data['cantidad'] > 0) {
    //   $precioConIvaPorUnidad = $data['total'] / $data['cantidad'];
    //   $product->cart_sale_price = $precioConIvaPorUnidad / 1.16;
    //   // También actualizar el precio base
    //   $product->cart_base_sale_price = $product->cart_sale_price;
    //   // Recalcular el IVA por unidad
    //   $product->cart_sale_iva = $product->cart_sale_price * 0.16;
    // }

    $product->cart_sale_price_with_iva      = ($data['precio'] + $data['iva']);

    $product->cart_sale_price_with_iva      = ($product->cart_sale_price + $product->cart_sale_iva);
    $product->cart_sale_discount            = $data['descuento'];
    $product->cart_sale_net_price           = $data['precio_neto'];
    $product->cart_sale_amount              = $data['total'];
    $product->cart_sale_amount_without_iva  = $data['subtotal'];
    $product->cart_sale_total_iva           = $data['iva'] * $data['cantidad'];
    $product->cart_sale_total_ieps          = ($data['ieps'] ?? 0) * $data['cantidad'];
    $product->have_ieps                     = $data['aplica_ieps'] ?? 'no';
    $product->ieps_percentage               = $data['ieps_porcentaje'] ?? 0;

    $product->comments                        = $data['comentarios'] ?? '';

    $product->cart_base_cost_price          = 0;
    $product->cart_cost_price               = 0;
    $product->cart_cost_iva                 = 0;
    $product->cart_cost_price_with_iva      = 0;
    $product->cart_cost_discount            = 0;
    $product->cart_cost_net_price           = 0;
    $product->cart_cost_amount              = 0;
    $product->cart_cost_amount_without_iva  = 0;
    $product->cart_cost_total_iva           = 0;

    // Obtener números de serie del producto
    $product->serial_numbers = get_sale_product_serial_numbers($data['id_producto'], $sale_id);

    $products[$data['id_producto']]         = $product;
  endwhile;

  return $products;
}

function get_sale_product_serial_numbers(
  $product_id,
  $sale_id
) {
  global $mysqli;
  global $db_dti;

  if (!$product_id || !$sale_id) return [];

  $query = "SELECT
      PNS.numero_serie
    FROM
      {$db_dti}_producto_numeros_serie AS PNS
     JOIN
      {$db_dti}_ventas AS V ON (V.folio = PNS.folio_venta)
    WHERE
      PNS.id_producto = ? AND
      V.id_venta      = ?
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('ii', $product_id, $sale_id);

  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return [];

  $serial_numbers = [];

  while ($data = mysqli_fetch_assoc($query_result)) :
    $serial_numbers[] = $data['numero_serie'];
  endwhile;

  return $serial_numbers;
}

function get_sale_details_table(
  $sale_id
) {

  if (!$sale_id) return false;

  $quote    = get_sale_data($sale_id);
  $rows     = '';
  $colspan  = 8;

  foreach ($quote->list as $key => $product) :
    $id             = $product->id;
    $code           = $product->code;
    $name           = $product->name;
    $stock          = $product->stock;
    $quantity       = $product->cart_quantity;
    $price          = $product->cart_sale_price;
    $iva            = $product->cart_sale_iva;
    $ieps           = $product->cart_sale_ieps ?? 0;
    $discount       = $product->cart_sale_discount;
    $net_price      = $product->cart_sale_net_price;
    $amount         = $product->cart_sale_amount_without_iva;
    $unit_symbol    = $product->unit_symbol;
    $cancelled      = $product->cancelled;
    $comments       = $product->comments ?? '';

    if ($comments) $name .= '<br><small class="text-muted">' . $comments . '</small>';

    $rows .= '
      <tr class="pos-tr">
        <td>' . $code . '</td>
        <td>' . $name . '</td>
        <td>' . format_decimal_number($quantity, DECIMALS_CURRENCY_TICKET) . ' ' . $unit_symbol . '</td>
        <td class="text-end">$' . number_format($price, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . number_format($ieps, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . number_format($iva, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . format_decimal_number($discount, DECIMALS_CURRENCY_TICKET) . '%</td>
        <td class="text-end">$' . format_decimal_number($net_price, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-end">$' . format_decimal_number($amount, DECIMALS_CURRENCY_TICKET) . '</td>
        <td class="text-center">' . ($cancelled ? '<span class="text-danger">Devuelto</span>' : '<span class="text-success">Activo</span>') . '</td>
        ' . ($cancelled ? '' : '
          <td id="cart-actions-ventas-' . $id . '" class="pos-actions-td">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

            <a class="pos-btn btn-remove-item" data-itemId="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Devolver producto" href="javascript:void(0)">
              <i class="fa fa-times-circle text-danger"></i>
            </a>
          </td>
        ') . '
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
          <th class="text-center">Estatus</th>
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
          <td class="text-end">$' . number_format($quote->sale_ieps, DECIMALS_CURRENCY_TICKET) . '</td>
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

    <script>
      $(".btn-remove-item").on("click", function() {
        const itemId = $(this).attr("data-itemId");

        cancelProduct(' . $sale_id . ',itemId);
      });
    </script>
  ';

  return $table;
}

function sale_get_active_products_quantity(
  $sale_id
) {
  global $mysqli;
  global $db_dti;


  $query = "SELECT
      COUNT(id_venta_producto) AS total
    FROM
      {$db_dti}_venta_productos
    WHERE
      id_venta  = {$sale_id} AND
      cancelado = 'no'
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  return $total;
}

function sale_get_product_type(
  $sale_id,
  $type
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      COUNT(VP.id_producto) AS total
    FROM
      {$db_dti}_venta_productos AS VP
    LEFT JOIN
      {$db_dti}_productos AS P ON (P.id_producto = VP.id_producto)
    WHERE
      VP.id_venta = {$sale_id} AND
      P.tipo      = '{$type}'
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  return $total;
}

function sale_get_product_types_for_sale(
  $sale_id
) {
  global $mysqli, $db_dti;

  // Obtener todos los tipos de productos únicos en esta venta
  $query = "SELECT DISTINCT P.tipo 
            FROM {$db_dti}_venta_productos VP 
            LEFT JOIN {$db_dti}_productos P ON VP.id_producto = P.id_producto 
            WHERE VP.id_venta = ? AND VP.cancelado = 0";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $sale_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $types = [];
  while ($row = $result->fetch_assoc()) {
    if (!empty($row['tipo'])) {
      $types[] = $row['tipo'];
    }
  }

  if (count($types) == 0) return 'Sin tipo';
  if (count($types) == 1) return ucfirst($types[0]);
  if (count($types) > 1)  return 'Mixto';

  return 'Sin tipo';
}

function getTotalAmountOfYeat(
  $year
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      SUM(total) AS total
    FROM
      {$db_dti}_ventas
    WHERE
      YEAR(fecha_creacion) = {$year}
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  return $total;
}

function getTotalSalesOfYear(
  $year
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      COUNT(id_venta) AS total
    FROM
      {$db_dti}_ventas
    WHERE
      YEAR(fecha_creacion) = {$year}
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  return $total;
}

function getTotalAmountOfMonth(
  $month,
  $year
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      SUM(total) AS total
    FROM
      {$db_dti}_ventas
    WHERE
      MONTH(fecha_creacion) = {$month} AND
      YEAR(fecha_creacion)  = {$year}
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  return $total;
}

/* function getAverageOfMonth(
  $month,
  $year
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      AVG(total) AS total
    FROM
      {$db_dti}_ventas
    WHERE
      MONTH(fecha_creacion) = {$month} AND
      YEAR(fecha_creacion)  = {$year}
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  return $total;
} */

function gettotalSalesOfMonth(
  $month,
  $year
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      COUNT(id_venta) AS total
    FROM
      {$db_dti}_ventas
    WHERE
      MONTH(fecha_creacion) = {$month} AND
      YEAR(fecha_creacion)  = {$year}
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  return $total;
}

// En getTopFiveSales ordenar de mayor a menor los clientes que más han comprado en dinero me refiero del mes
function getTopFiveSales(
  $month,
  $year
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      C.id_cliente,
      C.nombre_completo,
      C.correo,
      C.telefono,
      V.folio,
      SUM(V.total) AS total
    FROM
      {$db_dti}_ventas AS V
    LEFT JOIN
      {$db_dti}_clientes AS C ON (C.id_cliente = V.id_cliente)
    WHERE
      MONTH(V.fecha_creacion) = {$month} AND
      YEAR(V.fecha_creacion)  = {$year}
    GROUP BY
      V.id_cliente
    ORDER BY
      total DESC
    LIMIT 5
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return [];

  $list = [];

  while ($data = mysqli_fetch_assoc($query_result)) :
    $customer = new stdClass();

    $customer->id           = $data['id_cliente'];
    $customer->name         = $data['nombre_completo'];
    $customer->email        = $data['correo'];
    $customer->phone        = $data['telefono'];
    $customer->saleFolio    = $data['folio'];
    $customer->totalAmount  = $data['total'];

    $list[] = $customer;
  endwhile;

  return $list;
}

// Obtener los productos mas vendidos del mes, ordenar de mayor a menor, obtener nombre, cantidadTotalVendida, precioTotalVendido
function getTopFiveMostSelledProducts(
  $month,
  $year
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      VP.id_producto,
      P.nombre_producto,
      SUM(VP.cantidad) AS cantidad_total,
      SUM(VP.total)    AS total
    FROM
      {$db_dti}_venta_productos AS VP
    LEFT JOIN
      {$db_dti}_productos AS P ON (P.id_producto = VP.id_producto)
    LEFT JOIN
      {$db_dti}_ventas AS V ON (V.id_venta = VP.id_venta)
    WHERE
      MONTH(V.fecha_creacion) = {$month} AND
      YEAR(V.fecha_creacion)  = {$year}
    GROUP BY
      VP.id_producto
    ORDER BY
      cantidad_total DESC
    LIMIT 5
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return [];

  $list = [];

  while ($data = mysqli_fetch_assoc($query_result)) :
    $product = new stdClass();

    $product->id           = $data['id_producto'];
    $product->name         = $data['nombre_producto'];
    $product->totalSold    = $data['cantidad_total'];
    $product->totalAmount  = $data['total'];

    $list[] = $product;
  endwhile;

  return $list;
}

// Incluir fecha completa
function getTotalAmountByType(
  $date,
  $type
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      SUM({$type}) AS total
    FROM
      {$db_dti}_ventas
    WHERE
      DATE_FORMAT(fecha_creacion, '%d-%m-%Y') = '{$date}'
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  return $total;
}

function getTotalAmountInCash(
  $date
) {
  return getTotalAmountByType($date, 'efectivo');
}

function getTotalAmountInDebitCard(
  $date
) {
  return getTotalAmountByType($date, 'tarjeta_debito');
}

function getTotalAmountInCreditCard(
  $date
) {
  return getTotalAmountByType($date, 'tarjeta_credito');
}

function getTotalAmountInTransfer(
  $date
) {
  return getTotalAmountByType($date, 'transferencia');
}

function getTotalAmountInMoneyCheck(
  $date
) {
  return getTotalAmountByType($date, 'cheque');
}

/* 
Promedio (Cards): Total mensual / días trabajados (Todos los días del mes menos los domingos)
Promedio (tabla): Total del año / total de meses transcurridos en el año
*/
function getWorkingDaysOfMonth(
  $month,
  $year
) {
  $days = (int)date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
  $sundays = 0;

  for ($i = 1; $i <= $days; $i++) {
    $date = date("Y-m-d", strtotime("{$year}-{$month}-{$i}"));

    if (date('N', strtotime($date)) == 7) $sundays++;
  }

  return $days - $sundays;
}

function getAverageOfMonthCards(
  $month,
  $year
) {
  $total = getTotalAmountOfMonth($month, $year);
  $days  = getWorkingDaysOfMonth($month, $year);

  return $total / $days;
}

function getAverageAmountOfMonthTable(
  $year
) {
  $total = getTotalAmountOfYeat($year);
  $months = date('m');

  return $total / $months;
}

function getAverageSalesOfMonthTable(
  $year
) {
  $total = getTotalSalesOfYear($year);
  $months = date('m');

  return $total / $months;
}
