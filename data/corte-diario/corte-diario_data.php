<?php
require '../lib/settings.inc.php';
require '../lib/helpers/sales.helper.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'corte-diario';
//$id_sucursal = getSessionBranchOfficeId();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = cleanStr($_POST['id_sucursal']);

    $filterDay        = cleanStr($_POST['filterDay']);
    $filterMonth      = cleanStr($_POST['filterMonth']);
    $filterYear       = cleanStr($_POST['filterYear']);

    $fecha            = date("d-m-Y", strtotime("{$filterYear}-{$filterMonth}-{$filterDay}"));
    $payment_method   = cleanStr($_POST['paymentMethod']);

    $column_id        = "V.id_venta";
    $c_from           = "{$db_dti}_ventas AS V";
    $c_extra_clauses  = "ORDER BY V.id_venta DESC";

    $fields = [
      "V.id_venta",
      ["V.id_venta", "uid"],
      "V.folio",
      "V.folio_cotizacion",
      "V.id_usuario",
      "V.id_sucursal",
      "V.id_cliente",
      "V.status",
      "V.tipo",
      "V.observaciones",
      "V.subtotal",
      "V.iva",
      "V.total",
      "V.pago_con",
      "V.cambio",
      "V.efectivo",
      "V.cheque",
      "V.transferencia",
      "V.tarjeta_credito",
      "V.tarjeta_debito",
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

    if (!empty($search))      array_push($c_where, ["V.folio",  "%$search%", "LIKE"]);
    #if (!empty($id_sucursal)) array_push($c_where, ["V.id_sucursal",  "{$id_sucursal}"]);
    if (!empty($fecha))       array_push($c_where, ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))",  "{$fecha}"]);

    if ($payment_method == "efectivo") array_push($c_where, ["V.efectivo",  "0", ">"]);
    if ($payment_method == "cheque")   array_push($c_where, ["V.cheque",    "0", ">"]);
    if ($payment_method == "transferencia") array_push($c_where, ["V.transferencia",  "0", ">"]);
    if ($payment_method == "tarjeta_credito") array_push($c_where, ["V.tarjeta_credito",  "0", ">"]);
    if ($payment_method == "tarjeta_debito") array_push($c_where, ["V.tarjeta_debito",  "0", ">"]);

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

    include $identifier . '_table.php';
    die;
    break;

  case 'action-cancelar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'cancelar')) :
      $id_venta     = cleanStr($_POST['uid']);
      $id_usuario   = get_id_usuario();
      $data_usuario = getUserData($id_usuario);

      $query = "SELECT
          VP.id_venta_producto,
          VP.id_venta,
          VP.id_producto,
          VP.cantidad,
          VP.cancelado,
          V.id_sucursal,
          I.stock,
          P.nombre_producto
        FROM
          {$db_dti}_venta_productos AS VP
        LEFT JOIN
          {$db_dti}_ventas AS V ON (VP.id_venta = V.id_venta)
        LEFT JOIN
          {$db_dti}_inventario AS I ON (
            V.id_sucursal  = I.id_sucursal AND
            VP.id_producto = I.id_producto
          )
        LEFT JOIN
          {$db_dti}_productos AS P ON (I.id_producto = P.id_producto)
        WHERE
          VP.id_venta = ?
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param('i', $id_venta);
      $stmt->execute();

      $query_result = $stmt->get_result();
      $num_rows     = $query_result->num_rows;

      if ($num_rows > 0) :
        while ($row = mysqli_fetch_assoc($query_result)) :
          addLogInKardex(
            $data_usuario['id_sucursal'],
            $row,
            ACCION_CANCELAR_VENTA . ' por ' . $data_usuario['nombre_completo'],
            TIPO_MOVIMIENTO_DECREMENTO,
            'venta'
          );
        endwhile;

        $query = "UPDATE {$db_dti}_ventas SET
            status = 'cancelado'
          WHERE
            id_venta = {$id_venta}
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) $response = [
          'status'        => 'success',
          'toastMessage'  => 'La venta se canceló correctamente',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;

  case "cancel-product":
    $sale_id                  = cleanStr($_POST['saleId']);
    $product_id               = cleanStr($_POST['productId']);
    $user_id                  = get_id_usuario();
    $user_data                = getUserData($user_id);
    $active_products_quantity = sale_get_active_products_quantity($sale_id);

    $query = "SELECT
        VP.id_venta_producto,
        VP.id_venta,
        VP.id_producto,
        VP.cantidad,
        VP.cancelado,
        V.id_sucursal,
        I.stock,
        P.nombre_producto
      FROM
        {$db_dti}_venta_productos AS VP
      LEFT JOIN
        {$db_dti}_ventas AS V ON (VP.id_venta = V.id_venta)
      LEFT JOIN
        {$db_dti}_inventario AS I ON (
          V.id_sucursal  = I.id_sucursal AND
          VP.id_producto = I.id_producto
        )
      LEFT JOIN
        {$db_dti}_productos AS P ON (I.id_producto = P.id_producto)
      WHERE
        VP.id_venta     = ? AND
        VP.id_producto  = ?
      LIMIT 1
    ";

    $stmt = $mysqli->prepare($query);

    $stmt->bind_param('ii', $sale_id, $product_id);
    $stmt->execute();

    $query_result = $stmt->get_result();
    $num_rows     = $query_result->num_rows;

    if ($num_rows > 0) :
      $product = mysqli_fetch_assoc($query_result);

      addLogInKardex(
        $user_data['id_sucursal'],
        $product,
        $active_products_quantity == 1 ? ACCION_CANCELAR_VENTA . ' por ' . $user_data['nombre_completo'] : "Producto devuelto por {$user_data['nombre_completo']}",
        TIPO_MOVIMIENTO_DECREMENTO,
        'venta'
      );

      $query = "";

      if ($active_products_quantity == 1) $query = "UPDATE {$db_dti}_ventas SET
          status = 'cancelado'
        WHERE
          id_venta = {$sale_id}
      ";

      if ($active_products_quantity > 1) $query = "UPDATE {$db_dti}_venta_productos SET
          cancelado = 'si'
        WHERE
          id_venta_producto = {$product['id_venta_producto']}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => $active_products_quantity == 1 ? 'La venta se canceló correctamente' : 'El producto se canceló correctamente',
        'reload'        => $active_products_quantity == 1 ? true : false,
        'products'      => get_sale_details_table($sale_id)
      ];
    endif;
    break;

  case 'action-imprimir-ticket-' . $identifier:
    if (checkModuleActionPermission($identifier, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/ticket-venta.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;
};

//$response['post'] = json_encode($_POST);

echo json_encode($response);
mysqli_close($mysqli);
die;
