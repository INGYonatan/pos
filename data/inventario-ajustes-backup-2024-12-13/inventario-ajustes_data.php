<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action                 = $_POST['action'];
$identifier             = 'inventario-ajustes';
$identifier_inventario  = 'inventario';

$carrito_ssid           = SESSION_CARRITO_AJUSTES_INVENTARIO;
#$id_almacen             = getStoreId();
$id_almacen             = cleanStr($_POST['id_sucursal']);
$tipo_movimiento        = cleanStr($_POST['tipo']);
$tipo_ajuste            = cleanStr($_POST['tipo_ajuste']);

switch ($action) {
  case 'load-' . $identifier_inventario:
    $have_actions     = haveActions($identifier_inventario, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);

    $column_id        = "I.id_inventario";
    $c_from           = "{$db_dti}_inventario AS I";
    $c_extra_clauses  = "ORDER BY I.id_inventario DESC";

    $fields = [
      "I.id_inventario",
      ["I.id_inventario", "uid"],
      "I.id_sucursal",
      "I.id_producto",
      "I.stock",
      "S.nombre_sucursal",
      "P.nombre_producto",
      "P.codigo",
      "P.unidad"
    ];

    $c_join = "
        LEFT JOIN {$db_dti}_sucursales  AS S ON (I.id_sucursal = S.id_sucursal)
        LEFT JOIN {$db_dti}_productos   AS P ON (I.id_producto = P.id_producto)
    ";

    $c_where = [
      ["I.id_sucursal", $id_almacen],
      ["P.status", "activo"]
    ];

    if (!empty($search)) array_push($c_where, [
      [
        ["S.nombre_sucursal",  "%$search%", "LIKE", "OR"],
        ["P.nombre_producto",  "%$search%", "LIKE", "OR"],
        ["P.codigo",  "%$search%", "LIKE", "OR"]
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
    if ($request['status'] === 'success') include $identifier_inventario . '_table.php';
    die;
    break;

  case 'cart-load-' . $identifier:
    $carrito = $_SESSION[$carrito_ssid];

    include $identifier . '_carrito_table.php';
    die;
    break;

  case 'cart-add-item-' . $identifier:
    $id_producto    = cleanStr($_POST['itemId']);
    $cantidad       = $_POST['quantity'] ? cleanStr($_POST['quantity']) : 0;

    $data_producto  = getBranchOfficeProductData($id_almacen, $id_producto);
    $carrito        = $_SESSION[$carrito_ssid];

    if ($data_producto['unidad'] === 'Pieza' && !(fmod($cantidad, 1) == 0)) :
      $response['toastMessage'] = 'La cantidad para este producto no puede ser en decimales';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    if ($carrito[$id_producto]) :
      $carrito_producto_data  = $carrito[$id_producto];

      $cantidad_anterior      = $carrito_producto_data['cantidad'];
      $cantidad_nueva         = $cantidad_anterior + doubleval($cantidad);

      $carrito_producto_data['cantidad'] = $cantidad_nueva;
    endif;

    if (!$carrito[$id_producto]) :
      $carrito_producto_data = [
        'id_producto'     => $data_producto['id_producto'],
        'codigo'          => $data_producto['codigo'],
        'nombre_producto' => $data_producto['nombre_producto'],
        'contenido'       => $data_producto['contenido'],
        'stock'           => $data_producto['stock'],
        'cantidad'        => doubleval($cantidad),
        'precio_venta'    => $data_producto['precio_venta'],
        'unidad'          => $data_producto['unidad']
      ];
    endif;

    $carrito[$id_producto]            = $carrito_producto_data;
    $_SESSION[$carrito_ssid]  = $carrito;

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'El producto se agregó correctamente'
    ];
    break;

  case 'cart-update-item-quantity-' . $identifier:
    $id_producto                        = cleanStr($_POST['itemId']);
    $cantidad                           = $_POST['quantity'] ? cleanStr($_POST['quantity']) : 0;
    $carrito                            = $_SESSION[$carrito_ssid];
    $carrito_producto_data              = $carrito[$id_producto];

    if ($carrito_producto_data['unidad'] === 'Pieza' && !(fmod($cantidad, 1) == 0)) :
      $response['toastMessage'] = 'La cantidad para este producto no puede ser en decimales';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    $cantidad_nueva                     = doubleval($cantidad);
    $carrito_producto_data['cantidad']  = $cantidad_nueva;

    $carrito[$id_producto]              = $carrito_producto_data;
    $_SESSION[$carrito_ssid]            = $carrito;

    $stock_final = ($cantidad_nueva + $carrito_producto_data['stock']);

    if ($tipo_movimiento == TIPO_MOVIMIENTO_DECREMENTO) $stock_final = ($carrito_producto_data['stock'] - $cantidad_nueva);

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'La cantidad se actualizó correctamente',
      'id_producto'   => $id_producto,
      'stock_final'   => $stock_final
    ];
    break;

  case 'cart-remove-item-' . $identifier:
    $id_producto                      = cleanStr($_POST['itemId']);
    $carrito                          = $_SESSION[$carrito_ssid];
    unset($carrito[$id_producto]);
    $_SESSION[$carrito_ssid]  = $carrito;

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'El producto se removió correctamente'
    ];
    break;

  case 'cart-clean-cart-' . $identifier:
    unset($_SESSION[$carrito_ssid]);

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'El carrito se vació correctamente'
    ];
    break;

  case 'cart-save-cart-' . $identifier:
    $id_usuario       = get_id_usuario();
    $folio            = get_adjustment_folio($id_almacen);

    $carrito          = $_SESSION[$carrito_ssid];
    $observaciones    = $tipo_ajuste == "ajuste" ?  cleanStr($_POST['observaciones']) : "";

    if (isEmptyArray($carrito)) $response['toastMessage'] = 'El carrito está vacío';

    if (!isEmptyArray($carrito)) :
      $query = "INSERT INTO {$db_dti}_inventario_ajustes (
          id_usuario,
          id_sucursal,
          folio,
          observaciones,
          tipo,
          tipo_ajuste
        ) VALUES (
          {$id_usuario},
          {$id_almacen},
          '{$folio}',
          '{$observaciones}',
          '{$tipo_movimiento}',
          '{$tipo_ajuste}'
        )
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) :
        $id_inventario_ajuste = mysqli_insert_id($mysqli);
        $data_sucursal        = getBranchOfficeData($id_almacen);

        $accion = ACCION_INVENTARIO_AUMENTAR_STOCK . " en {$data_sucursal['nombre_sucursal']}";

        if ($tipo_movimiento == TIPO_MOVIMIENTO_DECREMENTO) $accion = ACCION_INVENTARIO_REDUCIR_STOCK . " en {$data_sucursal['nombre_sucursal']}";

        foreach ($carrito as $key => $row) :
          $query_detalles = "INSERT INTO {$db_dti}_inventario_ajuste_productos (
              id_inventario_ajuste,
              id_producto,
              cantidad
            ) VALUES (
              {$id_inventario_ajuste},
              {$row['id_producto']},
              {$row['cantidad']}
            )
          ";

          $query_detalles_result = mysqli_query($mysqli, $query_detalles);

          addLogInKardex(
            $id_almacen,
            $row,
            $accion,
            $tipo_movimiento,
            'ajuste'
          );
        endforeach;

        $_SESSION[$carrito_ssid] = 0;
        unset($_SESSION[$carrito_ssid]);

        $response = [
          'status'        => 'success',
          'toastMessage'  => 'El almacén se actualizó correctamente'
        ];

        $ticket = BASE_URL . '/ticket-ajuste.php?uid=' . $id_inventario_ajuste;
        $response['ticket'] = $ticket;
      endif;
    endif;
    break;

  case 'cart-action-change-branch-office-' . $identifier:
    unset($_SESSION[$carrito_ssid]);

    $response = [
      'status' => 'success',
      //'toastMessage'  => 'La sucursal se cambió correctamente'
    ];
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
