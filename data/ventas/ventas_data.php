<?php

// use function PHPSTORM_META\type;

require '../lib/settings.inc.php';
require '../lib/helpers/sales.helper.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'ventas';
//$id_sucursal = getSessionBranchOfficeId();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();
    // $fecha            = cleanStr($_POST['fecha']);
    $tipo_productos   = cleanStr($_POST['tipo_productos']);
    $forma_pago       = cleanStr($_POST['forma_pago']);
    $metodo_pago      = cleanStr($_POST['metodo_pago']);
    $id_cliente       = cleanStr($_POST['id_cliente']);
    $selectedSales  = $_POST['selectedSales'] ? json_decode($_POST['selectedSales']) : [];
    $id_quien_realizo = cleanStr($_POST['id_quien_realizo']);

    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $status           = cleanStr($_POST['status']);

    $column_id        = "V.id_venta";
    $c_from           = "{$db_dti}_ventas AS V";
    $c_extra_clauses  = "GROUP BY V.id_venta ORDER BY V.id_venta DESC";

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
      "V.ieps",
      "V.total",
      "V.pago_con",
      "V.cambio",
      "V.efectivo",
      "V.efectivo_referencia",
      "V.cheque",
      "V.transferencia",
      "V.tarjeta_credito",
      "V.tarjeta_debito",
      "V.fecha_creacion",
      "V.tipo_productos",
      "V.forma_pago",
      "V.pagado",
      "V.tipo_transaccion",
      "V.folio_sae",
      ["DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      "S.nombre_sucursal",
      "U.nombre_completo",
      ["C.nombre_completo", "nombre_cliente"]
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_sucursales  AS S  ON (V.id_sucursal = S.id_sucursal)
      LEFT JOIN {$db_ati}_usuarios    AS U  ON (V.id_usuario  = U.id_usuario)
      LEFT JOIN {$db_dti}_clientes    AS C  ON (V.id_cliente  = C.id_cliente)
      LEFT JOIN {$db_dti}_cotizaciones AS CT ON (V.folio_cotizacion = CT.folio)
    ";

    if ($tipo_productos) {
      $c_join .= "
        INNER JOIN {$db_dti}_venta_productos AS VP ON (V.id_venta = VP.id_venta AND VP.id_tipo = '{$tipo_productos}')
      ";
    }

    $c_where = [];

    $filtersSearch = [
      ["V.folio",  "%$search%", "LIKE"],
      ["V.folio_cotizacion",  "%$search%", "LIKE", "OR"],
      ["U.nombre_completo",  "%$search%", "LIKE", "OR"],
      ["C.nombre_completo",  "%$search%", "LIKE", "OR"],
      ["V.total",  "%$search%", "LIKE", "OR"]
    ];

    if ($IS_ADMIN) $filtersSearch[] = ["S.nombre_sucursal",  "%$search%", "LIKE", "OR"];

    if (!empty($search))          array_push($c_where, [$filtersSearch]);

    if (!empty($id_sucursal))     array_push($c_where, ["V.id_sucursal",  "{$id_sucursal}"]);
    if (!empty($fecha))           array_push($c_where, ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))",  "{$fecha}"]);
    //if (!empty($tipo_productos))  array_push($c_where, ["V.tipo_productos",  "{$tipo_productos}"]);
    if (!empty($forma_pago))      array_push($c_where, ["V.forma_pago",  "{$forma_pago}"]);

    if ($metodo_pago == "efectivo")          array_push($c_where, ["V.efectivo",  "0", ">"]);
    if ($metodo_pago == "cheque")            array_push($c_where, ["V.cheque",  "0", ">"]);
    if ($metodo_pago == "transferencia")     array_push($c_where, ["V.transferencia",  "0", ">"]);
    if ($metodo_pago == "tarjeta_credito")   array_push($c_where, ["V.tarjeta_credito",  "0", ">"]);
    if ($metodo_pago == "tarjeta_debito")    array_push($c_where, ["V.tarjeta_debito",  "0", ">"]);

    if (!empty($id_cliente))      array_push($c_where, ["V.id_cliente",  "{$id_cliente}"]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))", $fecha_fin]);

    if (!empty($status)) array_push($c_where, ["V.status", $status]);

    if (!empty($id_quien_realizo)) array_push($c_where, ["V.id_usuario", $id_quien_realizo]);

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

  case "agregar-referencia-efectivo":
    //if (!checkModuleActionPermission($identifier, "editar")) break;

    $saleId     = cleanStr($_POST["uid"]);
    $reference  = cleanStr($_POST["efectivo_referencia"]);

    $query = "UPDATE {$db_dti}_ventas SET
        efectivo_referencia = ?
      WHERE
        id_venta = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("si", $reference, $saleId);

      $result = $stmt->execute();

      if ($result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La referencia se agregó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    } catch (Exception $e) {
      error_log("ERROR_VENTAS_DATA::AGREGAR_REFERENCIA_EFECTIVO: {$e->getMessage()}");
    }
    break;

  case "masivo-agregar-referencia-efectivo":
    //if (!checkModuleActionPermission($identifier, "editar")) break;

    $saleIds    = json_decode($_POST["uids"], true);
    $reference  = cleanStr($_POST["efectivo_referencia"]);

    $saleIdsString = implode(',', $saleIds);

    $query = "UPDATE {$db_dti}_ventas SET
        efectivo_referencia = '{$reference}'
      WHERE
        id_venta IN ({$saleIdsString}) AND
        efectivo > 0
    ";

    try {
      $result = mysqli_query($mysqli, $query);

      if ($result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La referencia se actualizó correctamente en las ventas seleccionadas',
        'callback'      => '{$("#selected-sales").val(""); load("' . $page . '", "' . $identifier . '");}'
      ];
    } catch (Exception $e) {
      error_log("ERROR_VENTAS_DATA::AGREGAR_REFERENCIA_EFECTIVO: {$e->getMessage()}");
    }
    break;

  case "agregar-folio-sae":
    //if (!checkModuleActionPermission($identifier, "editar")) break;

    $saleId     = cleanStr($_POST["uid"]);
    $folioSae   = cleanStr($_POST["folio_sae"]);

    $query = "UPDATE {$db_dti}_ventas SET
        folio_sae = ?
      WHERE
        id_venta = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("si", $folioSae, $saleId);

      $result = $stmt->execute();

      if ($result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El folio SAE se agregó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    } catch (Exception $e) {
      error_log("ERROR_VENTAS_DATA::AGREGAR_FOLIO_SAE: {$e->getMessage()}");
    }
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
