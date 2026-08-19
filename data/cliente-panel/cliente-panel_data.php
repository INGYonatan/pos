<?php
require '../lib/settings.inc.php';
require '../lib/helpers/quotes.helper.php';
require '../lib/helpers/sales.helper.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action                     = $_POST['action'];
$direcciones_tid            = 'cliente-direcciones';
$sucursales_tid             = 'cliente-sucursales';
$cotizaciones_abiertas_tid  = 'cotizaciones-abiertas';
$cotizaciones_cerradas_tid  = 'cotizaciones-cerradas';
$ventas_tid                 = 'ventas';
$fecha_hoy        = date('Y-m-d');

switch ($action) {
  case 'load-' . $cotizaciones_abiertas_tid:
    $have_actions     = haveActions($cotizaciones_abiertas_tid, 'tabla');
    $fecha_hoy        = date('Y-m-d');

    $per_page         = $_POST['perPage'] ? $_POST['perPage'] : 15;
    $page             = $_POST['page'];

    $search                 = cleanStr($_POST['search']);
    $id_sucursal            = cleanStr($_POST['id_sucursal']);
    $fecha                  = cleanStr($_POST['fecha']);
    $id_cliente             = cleanStr($_POST['id_cliente']);

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
      ['CTZ.status', 'cancelado', '!='],
      ["CTZ.tipo", "abierta"],
      ['CTZ.id_cliente', $id_cliente]
    ];

    if (!empty($search))      array_push($c_where, ["CTZ.folio",  "%$search%", "LIKE"]);
    if (!empty($id_sucursal)) array_push($c_where, ["CTZ.id_sucursal",  "{$id_sucursal}"]);
    if (!empty($fecha))       array_push($c_where, ["(DATE_FORMAT(CTZ.fecha_creacion, '%d-%m-%Y'))",  "{$fecha}"]);

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
    if ($request['status'] === 'success') include $cotizaciones_abiertas_tid . '_table.php';
    die;
    break;

  case 'action-imprimir-ticket-' . $cotizaciones_abiertas_tid:
    if (checkModuleActionPermission($cotizaciones_abiertas_tid, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/pdf-cotizacion.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;

  case 'action-cerrar-cotizacion-' . $cotizaciones_abiertas_tid:
    $uid        = $_POST['uid'];
    $date       = date('Y-m-d H:i:s');

    $query = "UPDATE {$db_dti}_cotizaciones SET
          tipo            = 'cerrada',
          fecha_creacion  = '{$date}'
        WHERE
          id_cotizacion = ?
      ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $uid);

    $result = $stmt->execute();

    if ($result) :
      $response = [
        'status'        => 'success',
        'toastMessage'  => "La cotización se cerró correctamente",
        'callback'      => 'load("' . $page . '", "' . $cotizaciones_abiertas_tid . '");'
      ];
    endif;
    break;

  case 'load-' . $cotizaciones_cerradas_tid:
    $have_actions     = haveActions($cotizaciones_cerradas_tid, 'tabla');
    $fecha_hoy        = date('Y-m-d');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = cleanStr($_POST['id_sucursal']);
    $fecha            = cleanStr($_POST['fecha']);
    $id_cliente       = cleanStr($_POST['id_cliente']);

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
      ['CTZ.status', 'cancelado', '!='],
      ['CTZ.id_cliente', $id_cliente]
    ];

    if (!empty($search))      array_push($c_where, ["CTZ.folio",  "%$search%", "LIKE"]);
    if (!empty($id_sucursal)) array_push($c_where, ["CTZ.id_sucursal",  "{$id_sucursal}"]);
    if (!empty($fecha))       array_push($c_where, ["(DATE_FORMAT(CTZ.fecha_creacion, '%d-%m-%Y'))",  "{$fecha}"]);

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
    if ($request['status'] === 'success') include $cotizaciones_cerradas_tid . '_table.php';
    die;
    break;

  /* case 'action-cancelar-' . $cotizaciones_cerradas_tid:
      if (checkModuleActionPermission($cotizaciones_cerradas_tid, 'cancelar')) :
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
            'callback'      => 'load("' . $page . '", "' . $cotizaciones_cerradas_tid . '");'
          ];
        endif;
      endif;
      break; */

  case "action-convertir-a-venta-{$cotizaciones_cerradas_tid}":
    $response['toastMessage'] = 'pl';
    if (checkModuleActionPermission($cotizaciones_cerradas_tid, 'convertir-a-venta')) :
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
                  load("' . $page . '", "' . $cotizaciones_cerradas_tid . '");
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

  case 'action-imprimir-ticket-' . $cotizaciones_cerradas_tid:
    if (checkModuleActionPermission($cotizaciones_cerradas_tid, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/pdf-cotizacion.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;

  case 'load-' . $ventas_tid:
    $have_actions     = haveActions($ventas_tid, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = cleanStr($_POST['id_sucursal']);
    $fecha            = cleanStr($_POST['fecha']);
    $id_cliente       = cleanStr($_POST['id_cliente']);

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
      "V.fecha_creacion",
      "V.tipo_productos",
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

    $c_where = [
      ["V.id_cliente", $id_cliente]
    ];

    if (!empty($search))      array_push($c_where, ["V.folio",  "%$search%", "LIKE"]);
    if (!empty($id_sucursal)) array_push($c_where, ["V.id_sucursal",  "{$id_sucursal}"]);
    if (!empty($fecha))       array_push($c_where, ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))",  "{$fecha}"]);

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
    if ($request['status'] === 'success') include $ventas_tid . '_table.php';
    die;
    break;

  case 'action-cancelar-' . $ventas_tid:
    if (checkModuleActionPermission($ventas_tid, 'cancelar')) :
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
          'callback'      => 'load("' . $page . '", "' . $ventas_tid . '");'
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

  case 'action-imprimir-ticket-' . $ventas_tid:
    if (checkModuleActionPermission($ventas_tid, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/ticket-venta.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;

  case 'load-' . $direcciones_tid:
    $have_actions     = haveActions($direcciones_tid, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];
    $customer_id      = $_POST['id_cliente'];

    $search           = cleanStr($_POST['search']);

    $column_id        = "id_cliente_direccion";
    $c_from           = "{$db_dti}_cliente_direcciones AS CD";
    $c_extra_clauses  = "ORDER BY id_cliente_direccion DESC";

    $fields = [
      "CD.id_cliente_direccion",
      ["CD.id_cliente_direccion", "uid"],
      "CD.id_cliente",
      "CD.id_estado",
      "CD.id_ciudad",
      "CD.id_colonia",
      "CD.nombre_comercial",
      "CD.codigo_postal",
      "CD.calle",
      "CD.n_exterior",
      "CD.n_interior",
      "CD.entre_calles",
      "CD.referencias",
      ["E.Estado", "estado"],
      ["M.Municipio", "ciudad"],
      ["CO.Colonia", "colonia"]
    ];

    $c_join = "
      LEFT JOIN
        estados AS E ON (E.idEstado = CD.id_estado)
      LEFT JOIN
        municipios AS M ON (M.idMunicipio = CD.id_ciudad)
      LEFT JOIN
        colonias AS CO ON (CO.idColonia = CD.id_colonia)
    ";

    $c_where = [
      ['CD.id_cliente', $customer_id],
      ['CD.status', 'activo']
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["CD.codigo_postal", "%$search%", "LIKE", "OR"],
        ["CD.calle", "%$search%", "LIKE", "OR"],
        ["E.estado", "%$search%", "LIKE", "OR"],
        ["M.Municipio", "%$search%", "LIKE", "OR"],
        ["CO.colionia", "%$search%", "LIKE", "OR"]
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

    //echo getEmptyTableMessage($request);
    //die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $direcciones_tid . '_table.php';
    die;
    break;

  case 'add-' . $direcciones_tid:
    if (checkModuleActionPermission($direcciones_tid, 'agregar')) :
      $request = useInsertByPost([
        'table_name' => "{$db_dti}_cliente_direcciones"
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'La dirección se agregó correctamente',
        'callback'      => 'load("' . $page . '", "' . $direcciones_tid . '");'
      ];
    endif;
    break;

  case 'edit-' . $direcciones_tid:
    if (checkModuleActionPermission($direcciones_tid, 'editar')) :
      $id_cliente_direccion = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_cliente_direcciones",
        'conditions' => [['id_cliente_direccion', $id_cliente_direccion]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'La dirección se actualizó correctamente',
        'callback'      => 'load("' . $page . '", "' . $direcciones_tid . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $direcciones_tid:
    if (checkModuleActionPermission($direcciones_tid, 'eliminar')) :
      $id_cliente_direccion = cleanStr($_POST['uid']);

      $query = "UPDATE {$db_dti}_cliente_direcciones SET
          status = 'eliminado'
        WHERE
          id_cliente_direccion = {$id_cliente_direccion}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La dirección se eliminó correctamente',
        'callback'      => 'load("' . $page . '", "' . $direcciones_tid . '");'
      ];
    endif;
    break;

  case 'load-' . $sucursales_tid:
    $have_actions     = haveActions($sucursales_tid, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];
    $customer_id      = $_POST['id_cliente'];

    $search           = cleanStr($_POST['search']);

    $column_id        = "id_cliente_sucursal";
    $c_from           = "{$db_dti}_cliente_sucursales";
    $c_extra_clauses  = "ORDER BY id_cliente_sucursal DESC";

    $fields = [
      "id_cliente_sucursal",
      ["id_cliente_sucursal", "uid"],
      "sucursal",
      "cp"
    ];

    $c_join = "";

    $c_where = [
      ['id_cliente', $customer_id],
      ['status', 'activo']
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["sucursal", "%$search%", "LIKE", "OR"],
        ["cp", "%$search%", "LIKE", "OR"]
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

    //echo getEmptyTableMessage($request);
    //die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $sucursales_tid . '_table.php';
    die;
    break;

  case 'add-' . $sucursales_tid:
    if (checkModuleActionPermission($sucursales_tid, 'agregar')) :
      $request = useInsertByPost([
        'table_name' => "{$db_dti}_cliente_sucursales"
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'La sucursal se agregó correctamente',
        'callback'      => 'load("' . $page . '", "' . $sucursales_tid . '");'
      ];
    endif;
    break;

  case 'edit-' . $sucursales_tid:
    if (checkModuleActionPermission($sucursales_tid, 'editar')) :
      $id_cliente_sucursal = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_cliente_sucursal",
        'conditions' => [['id_cliente_sucursal', $id_cliente_sucursal]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'La sucursal se actualizó correctamente',
        'callback'      => 'load("' . $page . '", "' . $sucursales_tid . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $sucursales_tid:
    if (checkModuleActionPermission($sucursales_tid, 'eliminar')) :
      $id_cliente_sucursal = cleanStr($_POST['uid']);

      $query = "UPDATE {$db_dti}_cliente_sucursales SET
          status = 'eliminado'
        WHERE
          id_cliente_sucursal = {$id_cliente_sucursal}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La sucursal se eliminó correctamente',
        'callback'      => 'load("' . $page . '", "' . $sucursales_tid . '");'
      ];
    endif;
    break;
};

//$response['post'] = json_encode($_POST);

echo json_encode($response);
mysqli_close($mysqli);
die;
