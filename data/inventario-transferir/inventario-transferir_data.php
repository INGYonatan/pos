<?php
require_once __DIR__ . "/../lib/settings.inc.php";
require_once __DIR__ . "/../lib/shopping-cart.php";

$response               = new stdClass();
$response->status       = 'error';
$response->toastMessage = '¡Error inesperado!, intentalo nuevamente';

$pageId               = "inventario-transferir";
$action               = $_POST['action'];

$productId            = cleanStr($_POST['itemId']);
$quantity             = $_POST['quantity'] ? doubleval(cleanStr($_POST['quantity'])) : 1;

$originBranchId       = cleanStr($_POST["originBranchId"]);
$destinationBranchId  = $_POST["branchId"] ? cleanStr($_POST["branchId"]) : cleanStr($_POST["destinationBranchId"]);

$cartSSID             = SESSION_CARRITO_TRANSFERIR_INVENTARIO;
$cartSession          = $_SESSION[$cartSSID] ?? new stdClass();

$cart = new ShoppingCart(
  $destinationBranchId,
  $productId,
  $cartSession,
  false
);

switch ($action):
  case "cart-load-{$pageId}":
    include "{$pageId}-cart.php";
    die;
    break;

  case "cart-add-item-{$pageId}":
    # Verificar si el producto tiene stock en la sucursal de origen
    $originProductData  = getBranchOfficeProductData($originBranchId, $productId);
    $originStock        = $originProductData["stock"];

    if ($originStock < $quantity) :
      $response->toastMessage = "El producto no tiene suficiente stock en la sucursal de origen";
      break;
    endif;

    $list = $cartSession->list;

    if ($list[$productId]) :
      # Verificar que la nueva cantidad mas la existente sea menor o igual a la existente
      $product      = $cartSession->list[$productId];
      $newQuantity  = $product->cart_quantity + $quantity;

      if ($originStock < $newQuantity) :
        $response->toastMessage = "El producto no tiene suficiente stock en la sucursal de origen";
        break;
      endif;

      $cart->increase_product_quantity($quantity);
    endif;

    if (!$list[$productId]) $cart->add_product($quantity);

    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$cartSSID] = $cart->get_cart();
    break;

  case "cart-update-item-quantity-{$pageId}":
    $cart->update_product_quantity($quantity ?? 1);

    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$cartSSID] = $cart->get_cart();
    break;

  case "cart-remove-item-{$pageId}":
    $cart->remove_product();
    $response = $cart->get_alert();

    if ($response->status === 'success') $_SESSION[$cartSSID] = $cart->get_cart();
    break;

  case "cart-clean-cart-{$pageId}":
    unset($_SESSION[$cartSSID]);

    $response->status = 'success';

    if ($_POST['useAlert'] != 'false') $response->toastMessage = 'El carrito se vació correctamente';
    if ($_POST['useAlert'] == 'false') $response->toastMessage = '';
    break;

  case "update-serialNumbers-{$pageId}":
    $ps_serial_numbers_ids  = $_POST["{$pageId}-serialNumberIds"];
    $ps_serial_numbers      = $_POST["{$pageId}-serialNumbers"];
    $is_empty_array         = isEmptyArray($ps_serial_numbers);

    if ($is_empty_array) :
      $response->toastMessage = 'No hay números de serie';
      echo json_encode($response);
      die;
    endif;

    $cart->update_serial_numbers($ps_serial_numbers, $originBranchId);

    $response = $cart->get_alert();

    if ($response->status === 'success') :
      $_SESSION[$cartSSID]  = $cart->get_cart();
      $response->callback   = "storeCart.loadCart();";
    endif;
    break;

  case "cart-save-cart-{$pageId}":
    $list = $cart->get_cart()->list;

    if (isEmptyArray($list)) :
      $response->toastMessage = 'El carrito está vacío ' . json_encode($cartData);
      break;
    endif;

    $userId             = get_id_usuario();
    $folio              = get_inventory_transfer_folio();
    $date               = $_POST["date"];
    $observations       = $_POST["observations"] != "undefined" ? cleanStr($_POST["observations"]) : "";
    $type               = TIPO_MOVIMIENTO_INCREMENTO;
    $transferRequestId  = $_POST["transferRequestId"] ?? null;

    # Validar los números de serie
    $sn_status = $cart->validate_serial_numbers();

    if ($sn_status->status == "error") :
      echo json_encode($sn_status);
      die;
    endif;

    $query = "INSERT INTO {$db_dti}_inventario_transferencias (
        id_usuario,
        id_sucursal_origen,
        id_sucursal_destino,
        folio,
        observaciones,
        tipo
      ) VALUES (
        ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    $stmt->bind_param(
      'iiisss',
      $userId,
      $originBranchId,
      $destinationBranchId,
      $folio,
      $observations,
      $type
    );

    try {
      $result = $stmt->execute();

      if (!$result) break;

      $inventoryTransferId    = $stmt->insert_id;
      $originBranchData       = getBranchOfficeData($originBranchId);
      $destinationBranchData  = getBranchOfficeData($destinationBranchId);

      foreach ($list as $product) :
        $productId      = $product->id;
        $productName    = $product->name;
        $quantity       = $product->cart_quantity;
        $serialNumbers  = $product->serial_numbers;
        $requiresSerialNumber = $product->requires_serial_number;
        $typeId         = $product->type_id;
        $type           = $product->type;

        $queryDetails = "INSERT INTO {$db_dti}_inventario_transferencia_productos (
            id_inventario_transferencia,
            id_producto,
            cantidad,
            id_tipo,
            tipo
          ) VALUES (
            ?, ?, ?, ?, ?
          )
        ";

        $stmtProducts = $mysqli->prepare($queryDetails);

        $stmtProducts->bind_param(
          'iidis',
          $inventoryTransferId,
          $productId,
          $quantity,
          $typeId,
          $type
        );

        $result = $stmtProducts->execute();

        $productInventoryId = $stmtProducts->insert_id;

        $productData = getBranchOfficeProductData($originBranchId, $productId);

        $kardexData = [
          'id_producto'     => $productId,
          'nombre_producto' => $productName,
          'stock'           => $productData['stock'],
          'cantidad'        => $quantity
        ];

        # Acutalizar los números de serie
        if ($requiresSerialNumber) :
          $serialNumbersCondition = "";
          $serialNumbersRowsToAdd = "";

          $serialNumbers          = $product->serial_numbers;
          $counter                = 0;

          foreach ($serialNumbers as $serialNumber) :
            $concat = $counter > 0 ? "OR " : "";

            $serialNumbersCondition .= "{$concat}(
              numero_serie  = '{$serialNumber->number}' AND
              id_producto   = {$productId}
            )";

            $concatAdd = $counter > 0 ? ", " : "";
            $serialNumbersRowsToAdd .= "{$concatAdd}({$productInventoryId}, {$inventoryTransferId}, '{$serialNumber->number}')";

            $counter++;
          endforeach;

          // $serialNumbersUpdate = "UPDATE {$db_dti}_producto_numeros_serie SET
          //     id_sucursal   = {$destinationBranchId}
          //   WHERE
          //     {$serialNumbersCondition}
          // ";

          // mysqli_query($mysqli, $serialNumbersUpdate);

          $serialNumbersInsert = "INSERT INTO {$db_dti}_inventario_transferencia_producto_numeros_serie (
              id_inventario_transferencia_producto,
              id_inventario_transferencia,
              numero_serie
            ) VALUES
            {$serialNumbersRowsToAdd}
          ";

          mysqli_query($mysqli, $serialNumbersInsert);
        endif;

        /**
         * Start add logs
         */
        # Agregar al kardex la info de la sucursal origen
        // $kardekLog = ACCION_INVENTARIO_TRANSFERIR . " hacia {$destinationBranchData['nombre_sucursal']}";

        // addKardexLog(
        //   $productId,
        //   $originBranchId,
        //   $quantity,
        //   $kardekLog
        // );

        // # Agregar al kardex la info de la sucursal destino
        // $kardekLog = ACCION_INVENTARIO_TRANSFERIR . " desde {$originBranchData['nombre_sucursal']}";

        // addKardexLog(
        //   $productId,
        //   $destinationBranchId,
        //   $quantity,
        //   $kardekLog
        // );
        /**
         * End add logs
         */
      endforeach;

      $response = [
        'status'        => 'success',
        'toastMessage'  => 'La transferencia se realizó correctamente'
      ];

      if ($response["status"] == "success" && $transferRequestId) {
        // Actualizar el estatus de la solicitud de traspaso a "completado" y los productos poner en cantidad_atendida la cantidad que se procesó en el traspaso
        try {
          $updateTransferRequestQuery = "UPDATE {$db_dti}_solicitud_transferencias SET
              status = 'completado'
            WHERE
              id_solicitud_transferencia = {$transferRequestId}
          ";

          mysqli_query($mysqli, $updateTransferRequestQuery);

          foreach ($list as $product) {
            $productId = $product->id;
            $quantity   = $product->cart_quantity;

            $updateTransferRequestProductsQuery = "UPDATE {$db_dti}_solicitud_transferencia_productos SET
                cantidad_atendida = {$quantity}
              WHERE
                id_solicitud_transferencia = {$transferRequestId} AND
                id_producto           = {$productId}
            ";

            mysqli_query($mysqli, $updateTransferRequestProductsQuery);
          }

          // Actualizar el traspaso para relacionarlo con la solicitud de traspaso
          $updateInventoryTransferQuery = "UPDATE {$db_dti}_inventario_transferencias SET id_solicitud_transferencia = {$transferRequestId} WHERE id_inventario_transferencia = {$inventoryTransferId}";
          mysqli_query($mysqli, $updateInventoryTransferQuery);
        } catch (Exception $e) {
          error_log("ERROR_INVENTORY_TRANSFER::UPDATE_TRANSFER_REQUEST: {$e->getMessage()}");
        }
      }

      unset($_SESSION[$cartSSID]);

      //if ($data_sucursal_destino['tipo'] === 'sucursal movil') :
      $ticket = BASE_URL . '/ticket-transferencia.php?uid=' . $inventoryTransferId;
      $response['ticket'] = $ticket;
    } catch (Exception $e) {
      error_log("ERROR_INVENTORY_TRANSFER::SAVE_CART: {$e->getMessage()}");
      $response->toastMessage = /* $e->getMessage(); */ "Error inesperado al guardar la transferencia, intentalo nuevamente";
    }
    break;
endswitch;

echo json_encode($response);
mysqli_close($mysqli);
die;
