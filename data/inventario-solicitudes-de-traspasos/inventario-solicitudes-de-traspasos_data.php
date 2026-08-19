<?php
require_once __DIR__ . "/../lib/settings.inc.php";
require_once __DIR__ . "/../lib/models/solicitudes-traspasos.model.php";
require_once __DIR__ . "/../lib/models/solicitudes-traspasos-productos.model.php";

$response = [
  "status"        => "error",
  "toastMessage"  => "¡Error inesperado!, intentalo nuevamente"
];

$action       = $_POST["action"];
$pageId       = "inventario-solicitudes-de-traspasos";
$userBranchId = getSessionBranchOfficeId();
$IS_ADMIN     = $admp_session_user_data["IS_ADMIN"];

$model        = new TransferRequestsModel();

switch ($action) {
  /* LOAD */
  case "load-{$pageId}":
    $haveActions      = haveActions($pageId, "tabla");

    $page             = cleanStr($_POST["page"] ?? 1);
    $perPage          = cleanStr($_POST["perPage"] ?? 15);

    $search           = cleanStr($_POST["search"] ?? "");
    $date             = cleanStr($_POST["date"]   ?? "");

    if ($date) $date  = date('Y-m-d', strtotime($date));

    $type             = cleanStr($_POST["type"]   ?? "realizadas");

    $originBranchId   = cleanStr($_POST["originBranchId"]);
    $destinyBranchId  = cleanStr($_POST["destinyBranchId"]);

    if ($type == "realizadas")  $destinyBranchId  = $IS_ADMIN ? $destinyBranchId : $userBranchId;
    if ($type == "recibidas")   $originBranchId   = $IS_ADMIN ? $originBranchId : $userBranchId;

    $result = $model->read([
      "page"                => $page,
      "perPage"             => $perPage,
      "term"                => $search,
      "date"                => $date,
      "type"                => $type,
      "originBranchId"      => $originBranchId,
      "destinationBranchId" => $destinyBranchId
    ]);

    if ($result->status == "error") {
      echo getEmptyTableMessage();
      die;
    }

    $rows = $result->data->rows;

    include __DIR__ . "/{$pageId}_table.php";
    die;
    break;
  /*  */
  case "action-update-status-{$pageId}":
    if (!checkModuleActionPermission($pageId, 'editar')) break;

    $requestTransferId = cleanStr($_POST["uid"]);
    $newStatus         = cleanStr($_POST["actionValue"]);

    $model->getById($requestTransferId);

    if (!$model->getId()) {
      $response['toastMessage'] = "La solicitud de traspaso no existe";
      break;
    }

    $model->setStatus($newStatus);

    $result = $model->update();

    if ($result->status == "success") $response = [
      "status" => "success",
      "toastMessage" => "El estado de la solicitud de traspaso se actualizó correctamente",
      "callback" => "load('{$page}', '{$pageId}')"
    ];
    break;
  /*  */

  /* CANCELAR */
  case "action-cancelar-{$pageId}":
    if (!checkModuleActionPermission($pageId, 'cancelar')) break;

    $id_inventario_transferencia = cleanStr($_POST['uid']);

    $query = "SELECT
        ITP.id_inventario_transferencia_producto,
        ITP.id_inventario_transferencia,
        ITP.id_producto,
        ITP.cantidad,
        ITP.cancelado,
        ITP.completado,
        IT.id_sucursal_origen,
        IT.id_sucursal_destino,
        IO.stock AS stock_origen,
        ID.stock AS stock_destino,
        P.nombre_producto
      FROM
        {$db_dti}_inventario_transferencia_productos AS ITP
      LEFT JOIN
        {$db_dti}_inventario_transferencias AS IT ON (ITP.id_inventario_transferencia = IT.id_inventario_transferencia)
      LEFT JOIN
        {$db_dti}_inventario AS IO ON (
          IT.id_sucursal_origen = IO.id_sucursal AND
          ITP.id_producto       = IO.id_producto
        )
      LEFT JOIN
        {$db_dti}_inventario AS ID ON (
          IT.id_sucursal_destino  = ID.id_sucursal AND
          ITP.id_producto         = ID.id_producto
        )
      LEFT JOIN
        {$db_dti}_productos AS P ON (ITP.id_producto = P.id_producto)
      WHERE
        ITP.id_inventario_transferencia = ?
    ";

    $stmt = $mysqli->prepare($query);

    $stmt->bind_param('i', $id_inventario_transferencia);
    $stmt->execute();

    $query_result = $stmt->get_result();
    $num_rows     = $query_result->num_rows;

    if ($num_rows > 0) :
      while ($row = mysqli_fetch_assoc($query_result)) :
        if ($row['completado'] == "si") {
          $data_sucursal_origen   = getBranchOfficeData($row['id_sucursal_origen']);
          $data_sucursal_destino  = getBranchOfficeData($row['id_sucursal_destino']);

          $data_producto = [
            'id_producto'     => $row['id_producto'],
            'nombre_producto' => $row['nombre_producto'],
            'cantidad'        => doubleval($row['cantidad']),
            'stock_origen'    => doubleval($row['stock_origen']),
            'stock_destino'   => doubleval($row['stock_destino'])
          ];

          addLogInKardex(
            $row['id_sucursal_origen'],
            $data_producto,
            ACCION_CANCELAR_TRANSFERENCIA . ' que se realizó hacia ' . $data_sucursal_destino['nombre_sucursal'],
            TIPO_MOVIMIENTO_DECREMENTO,
            'transferencia-almacen'
          );

          addLogInKardex(
            $row['id_sucursal_destino'],
            $data_producto,
            ACCION_CANCELAR_TRANSFERENCIA . ' que realizó ' . $data_sucursal_origen['nombre_sucursal'],
            TIPO_MOVIMIENTO_DECREMENTO,
            'transferencia-sucursal'
          );
        }
      endwhile;

      $query = "UPDATE {$db_dti}_inventario_transferencias SET
          status = 'cancelado'
        WHERE
          id_inventario_transferencia = {$id_inventario_transferencia}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La transferencia se canceló correctamente',
        'callback'      => 'load("' . $page . '", "' . $pageId . '");'
      ];
    endif;
    break;

  /* IMPRIMIR TICKET */
  case 'action-imprimir-ticket-' . $pageId:
    if (checkModuleActionPermission($pageId, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/ticket-transferencia.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;

  /* CARRITO - CART LOAD */
  case "cart-load-{$pageId}":
    $cartSSID = SESSION_CARRITO_SOLICITUD_TRASPASO;
    $cartSession = $_SESSION[$cartSSID] ?? [];
    include "{$pageId}-cart.php";
    die;
    break;

  /* CARRITO - ADD ITEM */
  case "cart-add-item-{$pageId}":
    $cartSSID = SESSION_CARRITO_SOLICITUD_TRASPASO;
    $cartSession = $_SESSION[$cartSSID] ?? [];

    $itemId = cleanStr($_POST['itemId']);
    $quantity = $_POST['quantity'] ? doubleval(cleanStr($_POST['quantity'])) : 1;
    $originBranchId = cleanStr($_POST["originBranchId"]);
    $destinationBranchId = cleanStr($_POST["destinationBranchId"]);

    # Obtener información del producto desde la sucursal ORIGEN (donde vienen los productos)
    $originProductData = getBranchOfficeProductData($originBranchId, $itemId);

    if (!$originProductData) :
      $response = ["status" => "error", "toastMessage" => "El producto no existe en la sucursal origen"];
      break;
    endif;

    $originStock = $originProductData['stock'];

    # Validar que la cantidad no exceda el stock disponible en el origen
    if ($originStock < $quantity) :
      $response = ["status" => "error", "toastMessage" => "No hay suficiente stock en la sucursal origen"];
      break;
    endif;

    # Obtener información del producto desde la sucursal DESTINO (para mostrar stock actual)
    $destinationProductData = getBranchOfficeProductData($destinationBranchId, $itemId);
    $destinationStock = $destinationProductData['stock'] ?? 0;

    # Verificar si el producto ya está en el carrito
    if (isset($cartSession[$itemId])) :
      $cartSession[$itemId]['cantidad_solicitada'] += $quantity;
    else :
      $cartSession[$itemId] = [
        'id_producto' => $itemId,
        'nombre_producto' => $originProductData['nombre_producto'],
        'codigo' => $originProductData['codigo'],
        'unidad' => $originProductData['unidad'] ?? '',
        'stock' => $originStock,
        'stock_destino' => $destinationStock,
        'cantidad_solicitada' => $quantity
      ];
    endif;

    $_SESSION[$cartSSID] = $cartSession;
    $response = ["status" => "success", "toastMessage" => "Producto agregado al carrito"];
    break;

  /* CARRITO - UPDATE ITEM QUANTITY */
  case "cart-update-item-quantity-{$pageId}":
    $cartSSID = SESSION_CARRITO_SOLICITUD_TRASPASO;
    $cartSession = $_SESSION[$cartSSID] ?? [];

    $itemId = cleanStr($_POST['itemId']);
    $quantity = $_POST['quantity'] ? doubleval(cleanStr($_POST['quantity'])) : 1;

    if (!isset($cartSession[$itemId])) :
      $response = ["status" => "error", "toastMessage" => "El producto no está en el carrito"];
      break;
    endif;

    if ($quantity <= 0) :
      unset($cartSession[$itemId]);
    else :
      $cartSession[$itemId]['cantidad_solicitada'] = $quantity;
    endif;

    $_SESSION[$cartSSID] = $cartSession;
    $response = ["status" => "success", "toastMessage" => "Cantidad actualizada"];
    break;

  /* CARRITO - REMOVE ITEM */
  case "cart-remove-item-{$pageId}":
    $cartSSID = SESSION_CARRITO_SOLICITUD_TRASPASO;
    $cartSession = $_SESSION[$cartSSID] ?? [];

    $itemId = cleanStr($_POST['itemId']);

    if (isset($cartSession[$itemId])) :
      unset($cartSession[$itemId]);
      $_SESSION[$cartSSID] = $cartSession;
      $response = ["status" => "success", "toastMessage" => "Producto eliminado"];
    else :
      $response = ["status" => "error", "toastMessage" => "El producto no está en el carrito"];
    endif;
    break;

  /* CARRITO - CLEAN CART */
  case "cart-clean-cart-{$pageId}":
    $cartSSID = SESSION_CARRITO_SOLICITUD_TRASPASO;
    unset($_SESSION[$cartSSID]);
    $response = ["status" => "success", "toastMessage" => "El carrito se vació correctamente"];
    break;

  /* CARRITO - SAVE CART */
  case "cart-save-cart-{$pageId}":
    $cartSSID = SESSION_CARRITO_SOLICITUD_TRASPASO;
    $cartSession = $_SESSION[$cartSSID] ?? [];

    if (isEmptyArray($cartSession)) :
      $response = ["status" => "error", "toastMessage" => "El carrito está vacío"];
      break;
    endif;

    try {
      $userId = get_id_usuario();
      $originBranchId = cleanStr($_POST["originBranchId"]);
      $destinationBranchId = cleanStr($_POST["destinationBranchId"]);
      $date = $_POST["date"] ?? date('Y-m-d');
      $observations = $_POST["observations"] != "undefined" ? cleanStr($_POST["observations"]) : "";

      # Crear la solicitud de traspaso
      $transferRequest = new TransferRequestsModel();
      $transferRequest->setUserId($userId);
      $transferRequest->setOriginBranchId($originBranchId);
      $transferRequest->setDestinationBranchId($destinationBranchId);
      $transferRequest->setFolio(get_transfer_request_folio());
      $transferRequest->setNotes($observations);
      $transferRequest->setStatus('pendiente');

      $createResult = $transferRequest->create();

      if ($createResult->status !== 'success') :
        $response = ["status" => "error", "toastMessage" => $createResult->message];
        break;
      endif;

      $transferRequestId = $transferRequest->getId();

      # Crear los productos de la solicitud
      foreach ($cartSession as $productData) :
        $productId = $productData['id_producto'];
        $quantityRequested = $productData['cantidad_solicitada'];

        $transferRequestProduct = new TransferRequestProductsModel();
        $transferRequestProduct->setTransferRequestId($transferRequestId);
        $transferRequestProduct->setProductId($productId);
        $transferRequestProduct->setRequestedQuantity($quantityRequested);
        $transferRequestProduct->setAttendedQuantity(0);

        $createProductResult = $transferRequestProduct->create();

        if ($createProductResult->status !== 'success') :
          $transferRequest->deleteById($transferRequestId);
          throw new Exception("Error al crear el producto: " . $createProductResult->message);
        endif;
      endforeach;

      # Limpiar el carrito
      unset($_SESSION[$cartSSID]);

      $response = [
        "status" => "success",
        "toastMessage" => "La solicitud de traspaso se creó correctamente",
        "uid" => $transferRequestId,
        "folio" => $transferRequest->getFolio(),
        //"ticket" => BASE_URL . '/ticket-solicitud-traspaso.php?uid=' . $transferRequestId
      ];
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUEST_SAVE: {$e->getMessage()}");
      $response = ["status" => "error", "toastMessage" => $e->getMessage()];
    }
    break;
}

mysqli_close($mysqli);
echo json_encode($response);
exit;
