<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente',
  'res' => json_encode($_POST)
];

$action                   = $_POST['action'];
$identifier               = 'inventario-transferir';

$carrito_ssid             = SESSION_CARRITO_TRANSFERIR_INVENTARIO;
$id_sucursal_origen_ssid  = SESSION_CARRITO_TRANSFERIR_INVENTARIO_SUCURSAL_ORIGEN;
$id_sucursal_destino_ssid = SESSION_CARRITO_TRANSFERIR_INVENTARIO_SUCURSAL_DESTINO;

$id_usuario               = get_id_usuario();
#$id_sucursal_origen      = getStoreId();
$id_sucursal_origen       = cleanStr($_POST['id_sucursal_origen']);
$id_sucursal_destino      = cleanStr($_POST['id_sucursal_destino']);

if (empty($id_sucursal_origen)) :
  $response['toastMessage'] = 'Selecciona la sucursal de origen';

  echo json_encode($response);
  mysqli_close($mysqli);
  die;
endif;

if (empty($id_sucursal_destino)) :
  $response['toastMessage'] = 'Selecciona la sucursal de destino';

  echo json_encode($response);
  mysqli_close($mysqli);
  die;
endif;

if ($id_sucursal_destino != $id_sucursal_origen) :
  $_SESSION[$id_sucursal_destino_ssid]  = $id_sucursal_destino;
  $_SESSION[$id_sucursal_origen_ssid]   = $id_sucursal_origen;
endif;

switch ($action) {
  case 'cart-load-' . $identifier:
    $carrito = $_SESSION[$carrito_ssid];

    include $identifier . '_carrito_table.php';
    die;
    break;

  case 'cart-add-item-' . $identifier:
    $id_producto            = cleanStr($_POST['itemId']);
    $cantidad               = $_POST['quantity'] ? cleanStr($_POST['quantity']) : 1;

    $data_producto          = getBranchOfficeProductData($id_sucursal_origen, $id_producto);
    $data_producto_destino  = getBranchOfficeProductData($id_sucursal_destino, $id_producto);
    $carrito                = $_SESSION[$carrito_ssid];

    if ($data_producto['unidad'] === 'Pieza' && !(fmod($cantidad, 1) == 0)) :
      $response['toastMessage'] = 'La cantidad para este producto no puede ser en decimales';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    if ($data_producto['stock'] == 0) :
      $response['toastMessage'] = 'No hay productos en stock';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    if ($cantidad > $data_producto['stock']) :
      $response['toastMessage'] = 'No hay productos suficientes en stock';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    if ($carrito[$id_producto]) :
      $carrito_producto_data              = $carrito[$id_producto];
      $cantidad_anterior                  = $carrito_producto_data['cantidad'];
      $cantidad_nueva                     = $cantidad_anterior + doubleval($cantidad);
      $carrito_producto_data['cantidad']  = $cantidad_nueva;
    endif;

    if (!$carrito[$id_producto]) :
      $carrito_producto_data = [
        'id_producto'         => $data_producto['id_producto'],
        'codigo'              => $data_producto['codigo'],
        'nombre_producto'     => $data_producto['nombre_producto'],
        'contenido'           => $data_producto['contenido'],
        'stock_origen'        => $data_producto['stock'],
        'stock_destino'       => $data_producto_destino['stock'],
        'cantidad'            => doubleval($cantidad),
        'precio_venta'        => $data_producto['precio_venta'],
        'unidad'              => $data_producto['unidad']
      ];
    endif;

    $carrito[$id_producto]    = $carrito_producto_data;
    $_SESSION[$carrito_ssid]  = $carrito;

    $response = [
      'status'        => 'success',
      'toastMessage'  => 'El producto se agregó correctamente'
    ];
    break;

  case 'cart-update-item-quantity-' . $identifier:
    $id_producto                        = cleanStr($_POST['itemId']);
    $cantidad                           = $_POST['quantity'] ? doubleval($_POST['quantity']) : 1;
    $carrito                            = $_SESSION[$carrito_ssid];
    $carrito_producto_data              = $carrito[$id_producto];

    $stock_origen                       = $carrito_producto_data['stock_origen'];
    $stock_destino                      = $carrito_producto_data['stock_destino'];

    $nuevo_stock_origen                 = $stock_origen - $cantidad;
    $nuevo_stock_destino                = $stock_destino + $cantidad;

    if ($nuevo_stock_origen < 0) :
      $response['toastMessage'] = 'Sin stock disponible';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    $carrito_producto_data['cantidad']  = $cantidad;

    $carrito[$id_producto]              = $carrito_producto_data;
    $_SESSION[$carrito_ssid]            = $carrito;

    $response = [
      'status'              => 'success',
      'toastMessage'        => 'La cantidad se actualizó correctamente',
      'id_producto'         => $id_producto,
      'stock_origen_final'  => "{$nuevo_stock_origen}",
      'stock_destino_final' => "{$nuevo_stock_destino}"
    ];
    break;

  case 'cart-remove-item-' . $identifier:
    $id_producto                      = cleanStr($_POST['itemId']);
    $carrito                          = $_SESSION[$carrito_ssid];
    unset($carrito[$id_producto]);
    $_SESSION[$carrito_ssid]          = $carrito;

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

  case 'cart-action-update-sucursal-origen-' . $identifier:
    if ($id_sucursal_origen === $id_sucursal_destino) :
      $response['toastMessage'] = 'La sucursal de origen no puede ser la misma que la sucursal de destino';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    $carrito = $_SESSION[$carrito_ssid];

    foreach ($carrito as $key => $row) :
      $id_producto                            = $row['id_producto'];

      $data_producto_carrito                  = $carrito[$id_producto];
      $data_producto                          = getBranchOfficeProductData($id_sucursal_origen, $id_producto);

      $data_producto_carrito['stock_origen']  = $data_producto['stock'];

      if ($data_producto_carrito['cantidad'] > $data_producto['stock']) :
        $data_producto_carrito['cantidad'] = $data_producto['stock'];
      endif;

      $carrito[$id_producto]                  = $data_producto_carrito;
    endforeach;

    $_SESSION[$carrito_ssid] = $carrito;

    $response = [
      'status' => 'success'
    ];
    break;

  case 'cart-action-update-sucursal-destino-' . $identifier:
    if ($id_sucursal_origen === $id_sucursal_destino) :
      $response['toastMessage'] = 'La sucursal de destino no puede ser la misma que la sucursal de origen';

      echo json_encode($response);
      mysqli_close($mysqli);
      die;
    endif;

    $carrito = $_SESSION[$carrito_ssid];

    foreach ($carrito as $key => $row) :
      $id_producto                            = $row['id_producto'];
      $data_producto_carrito                  = $carrito[$id_producto];
      $data_producto                          = getBranchOfficeProductData($id_sucursal_destino, $id_producto);
      $data_producto_carrito['stock_destino'] = $data_producto['stock'];
      $carrito[$id_producto]                  = $data_producto_carrito;
    endforeach;

    $_SESSION[$carrito_ssid] = $carrito;

    $response = [
      'status' => 'success'
    ];
    break;

  case 'cart-save-cart-' . $identifier:
    $id_usuario       = get_id_usuario();
    $carrito          = $_SESSION[$carrito_ssid];
    $observaciones    = cleanStr($_POST['observaciones']);

    $tipo_incremento  = TIPO_MOVIMIENTO_INCREMENTO;

    if (isEmptyArray($carrito)) $response['toastMessage'] = 'El carrito está vacío';

    if (!isEmptyArray($carrito)) :
      $folio = get_inventory_transfer_folio();

      $query = "INSERT INTO {$db_dti}_inventario_transferencias (
          id_usuario,
          id_sucursal_origen,
          id_sucursal_destino,
          folio,
          observaciones,
          tipo
        ) VALUES (
          {$id_usuario},
          {$id_sucursal_origen},
          {$id_sucursal_destino},
          '{$folio}',
          '{$observaciones}',
          '{$tipo_incremento}'
        )
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) :
        $id_inventario_transferencia  = mysqli_insert_id($mysqli);
        $data_sucursal_origen         = getBranchOfficeData($id_sucursal_origen);
        $data_sucursal_destino        = getBranchOfficeData($id_sucursal_destino);

        foreach ($carrito as $key => $row) :
          $query_detalles = "INSERT INTO {$db_dti}_inventario_transferencia_productos (
              id_inventario_transferencia,
              id_producto,
              cantidad
            ) VALUES (
              {$id_inventario_transferencia},
              {$row['id_producto']},
              {$row['cantidad']}
            )
          ";

          $query_detalles_result = mysqli_query($mysqli, $query_detalles);

          addLogInKardex(
            $id_sucursal_origen,
            $row,
            ACCION_INVENTARIO_TRANSFERIR . " hacia {$data_sucursal_destino['nombre_sucursal']}",
            $tipo_incremento,
            'transferencia-almacen'
          );

          addLogInKardex(
            $id_sucursal_destino,
            $row,
            ACCION_INVENTARIO_TRANSFERIR . " desde {$data_sucursal_origen['nombre_sucursal']}",
            $tipo_incremento,
            'transferencia-sucursal'
          );
        endforeach;

        $_SESSION[$carrito_ssid]              = null;
        $_SESSION[$id_sucursal_origen_ssid]   = null;
        $_SESSION[$id_sucursal_destino_ssid]  = null;

        unset($_SESSION[$carrito_ssid]);
        unset($_SESSION[$id_sucursal_origen_ssid]);
        unset($_SESSION[$id_sucursal_destino_ssid]);

        $response = [
          'status'        => 'success',
          'toastMessage'  => 'La transferencia se realizó correctamente'
        ];

        //if ($data_sucursal_destino['tipo'] === 'sucursal movil') :
        $ticket = BASE_URL . '/ticket-transferencia.php?uid=' . $id_inventario_transferencia;
        $response['ticket'] = $ticket;
      //endif;
      endif;
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
