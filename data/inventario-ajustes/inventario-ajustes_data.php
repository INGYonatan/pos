<?php
require_once __DIR__ . "/../lib/settings.inc.php";
require_once __DIR__ . "/../lib/shopping-cart.php";

$response               = new stdClass();
$response->status       = 'error';
$response->toastMessage = '¡Error inesperado!, intentalo nuevamente';

$pageId       = "inventario-ajustes";
$action       = $_POST['action'];

$productId    = cleanStr($_POST['itemId']);
$quantity     = $_POST['quantity'] ? doubleval(cleanStr($_POST['quantity'])) : 1;
$branchId     = cleanStr($_POST["branchId"]);

$cartSSID     = SESSION_CARRITO_AJUSTES_INVENTARIO;
$cartSession  = $_SESSION[$cartSSID] ?? new stdClass();

$adjustment   = cleanStr($_POST["adjustment"]);

$cart = new ShoppingCart(
  $branchId,
  $productId,
  $cartSession,
  $adjustment == TIPO_MOVIMIENTO_DECREMENTO ? true : false
);

switch ($action):
  case "cart-load-{$pageId}":
    include "{$pageId}-cart.php";
    die;
    break;

  case "cart-add-item-{$pageId}":
    $list = $cartSession->list;

    if ($list[$productId])  $cart->increase_product_quantity($quantity);
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

    $verificationType = "";

    if ($adjustment == TIPO_MOVIMIENTO_INCREMENTO)  $verificationType = "for-increment";
    if ($adjustment == TIPO_MOVIMIENTO_DECREMENTO)  $verificationType = "for-decrement";

    $cart->update_serial_numbers($ps_serial_numbers, null, $verificationType);

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

    $userId       = get_id_usuario();
    $folio        = get_adjustment_folio();

    $date           = $_POST["date"];
    $branchId       = cleanStr($_POST["branchId"]);
    $adjustment     = cleanStr($_POST["adjustment"]);
    $adjustmentType = cleanStr($_POST["adjustmentType"]);
    $observations   = cleanStr($_POST["observations"]);

    # Validar los números de serie
    $sn_status = $cart->validate_serial_numbers();

    if ($sn_status->status == "error") :
      echo json_encode($sn_status);
      die;
    endif;

    $query = "INSERT INTO {$db_dti}_inventario_ajustes (
        id_usuario,
        id_sucursal,
        folio,
        observaciones,
        tipo,
        tipo_ajuste
      ) VALUES (
        ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    $stmt->bind_param(
      'iissss',
      $userId,
      $branchId,
      $folio,
      $observations,
      $adjustment,
      $adjustmentType
    );

    try {
      $result = $stmt->execute();

      if (!$result) break;

      $inventoryAdjustmentId  = $stmt->insert_id;
      $branchData             = getBranchOfficeData($branchId);
      $action                 = ACCION_INVENTARIO_AUMENTAR_STOCK . " en {$branchData['nombre_sucursal']}";

      if ($adjustmentType === TIPO_MOVIMIENTO_DECREMENTO) $action = ACCION_INVENTARIO_REDUCIR_STOCK . " en {$branchData['nombre_sucursal']}";

      foreach ($list as $product) :
        $productId      = $product->id;
        $productName    = $product->name;
        $quantity       = $product->cart_quantity;
        $serialNumbers  = $product->serial_numbers;
        $requiresSerialNumber = $product->requires_serial_number;
        $typeId         = $product->type_id;
        $type           = $product->type;

        $productData    = getBranchOfficeProductData($branchId, $productId);

        $queryProducts = "INSERT INTO {$db_dti}_inventario_ajuste_productos (
            id_inventario_ajuste,
            id_producto,
            cantidad,
            id_tipo,
            tipo
          ) VALUES (
            ?, ?, ?, ?, ?
          )
        ";

        $stmtProducts = $mysqli->prepare($queryProducts);

        $stmtProducts->bind_param(
          'iidis',
          $inventoryAdjustmentId,
          $productId,
          $quantity,
          $typeId,
          $type
        );

        $result = $stmtProducts->execute();

        $productInventoryId = $stmtProducts->insert_id;

        # AGREGAR LOS NÚMEROS DE SERIE CUANDO SEAN EQUIPOS E INCREMENTO
        if ($requiresSerialNumber) :
          $serialNumbersRowsToAdd = "";

          $serialNumbers          = $product->serial_numbers;
          $counter                = 0;

          $serialNumbersToAddInGeneralTable    = [];
          $serialNumbersToRemoveInGeneralTable = [];

          foreach ($serialNumbers as $serialNumber) :
            $concatAdd = $counter > 0 ? ", " : "";
            $serialNumbersRowsToAdd .= "{$concatAdd}({$productInventoryId}, {$inventoryAdjustmentId}, '{$serialNumber->number}')";

            $counter++;

            if ($adjustment == TIPO_MOVIMIENTO_INCREMENTO) :
              array_push($serialNumbersToAddInGeneralTable, "({$productId}, '{$serialNumber->number}', {$branchId})");
            endif;

            if ($adjustment == TIPO_MOVIMIENTO_DECREMENTO) :
              array_push($serialNumbersToRemoveInGeneralTable, "(
                id_producto   = '{$productId}'            AND
                numero_serie  = '{$serialNumber->number}' AND
                id_sucursal   = {$branchId}
              )");
            endif;
          endforeach;

          $serialNumbersInsert = "INSERT INTO {$db_dti}_inventario_ajuste_producto_numeros_serie (
              id_inventario_ajuste_producto,
              id_inventario_ajuste,
              numero_serie
            ) VALUES
              {$serialNumbersRowsToAdd}
          ";

          mysqli_query($mysqli, $serialNumbersInsert);

          if ($adjustment == TIPO_MOVIMIENTO_INCREMENTO) :
            $serialNumbersToAddInGeneralTable = implode(", ", $serialNumbersToAddInGeneralTable);

            // Primero obtendremos los números de serie que ya existen y que están con el status pendiente-de-ajuste para actualizarlos a disponibles
            $serialNumbersToSearch = array_map(fn($sn) => "'" . $sn->number . "'", $serialNumbers);
            $serialNumbersToSearch = implode(", ", $serialNumbersToSearch);
            $serialNumbersToUpdate = [];

            $query = "SELECT
                numero_serie
              FROM
                {$db_dti}_producto_numeros_serie
              WHERE
                id_sucursal   = {$branchId}                 AND
                numero_serie IN ({$serialNumbersToSearch})  AND
                status        = 'pendiente-de-ajuste'
            ";

            $result   = mysqli_query($mysqli, $query);
            $numRows  = mysqli_num_rows($result);

            if ($numRows > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
                array_push($serialNumbersToUpdate, $row['numero_serie']);
              }
            }

            // Agregamos los números de serie
            $query = "INSERT INTO {$db_dti}_producto_numeros_serie (
                id_producto,
                numero_serie,
                id_sucursal
              ) VALUES
                {$serialNumbersToAddInGeneralTable}
              ON DUPLICATE KEY UPDATE
                id_producto_numero_serie = id_producto_numero_serie
            ";

            mysqli_query($mysqli, $query);

            // Actualizar los números de serie encontrados a disponibles
            if (count($serialNumbersToUpdate) > 0) {
              $serialNumbersToUpdateStr = implode("', '", $serialNumbersToUpdate);

              $query = "UPDATE {$db_dti}_producto_numeros_serie SET
                  status = 'disponible'
                WHERE
                  id_sucursal   = {$branchId}                     AND
                  numero_serie IN ('{$serialNumbersToUpdateStr}')
              ";

              mysqli_query($mysqli, $query);
            }
          endif;

          if ($adjustment == TIPO_MOVIMIENTO_DECREMENTO) :
            $serialNumbersToRemoveInGeneralTable = implode(" OR ", $serialNumbersToRemoveInGeneralTable);

            $query = "DELETE FROM {$db_dti}_producto_numeros_serie WHERE {$serialNumbersToRemoveInGeneralTable}";

            mysqli_query($mysqli, $query);
          endif;
        endif;

        $kardekLog = $action;

        addKardexLog(
          $productId,
          $branchId,
          $quantity,
          $kardekLog
        );
      endforeach;

      unset($_SESSION[$cartSSID]);

      $response = [
        'status'        => 'success',
        'toastMessage'  => 'La ajuste se realizó correctamente'
      ];

      //if ($data_sucursal_destino['tipo'] === 'sucursal movil') :
      $ticket = BASE_URL . '/ticket-ajuste.php?uid=' . $inventoryAdjustmentId;
      $response['ticket'] = $ticket;
    } catch (Exception $e) {
      $response->toastMessage = $e->getMessage();
    }
    break;
endswitch;

echo json_encode($response);
mysqli_close($mysqli);
die;
