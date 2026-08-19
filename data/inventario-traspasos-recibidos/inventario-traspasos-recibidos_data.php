<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . "/../lib/settings.inc.php";
require_once __DIR__ . "/../lib/php-mailer/vendor/autoload.php";

$response = [
  "status"        => "error",
  "toastMessage"  => "¡Error inesperado!, intentalo nuevamente"
];

$action       = $_POST["action"];
$pageId       = "inventario-traspasos-recibidos";
$userBranchId = getSessionBranchOfficeId();
$userId       = get_id_usuario();
$IS_ADMIN     = $admp_session_user_data["IS_ADMIN"];

switch ($action) {
  /* LOAD */
  case "load-{$pageId}":
    $haveActions      = haveActions($pageId, "tabla");

    $page             = cleanStr($_POST["page"] ?? 1);
    $perPage          = cleanStr($_POST["perPage"] ?? 15);

    $search           = cleanStr($_POST["search"] ?? "");
    $fecha_inicio     = cleanStr($_POST["fecha_inicio"] ?? "");
    $fecha_fin        = cleanStr($_POST["fecha_fin"]   ?? "");

    $originBranchId   = cleanStr($_POST["originBranchId"]);
    $destinyBranchId  = $IS_ADMIN ? cleanStr($_POST["destinyBranchId"]) : $userBranchId;

    $columnId         = "IT.id_inventario_transferencia";
    $cFrom            = "{$db_dti}_inventario_transferencias AS IT";
    $cExtraClauses    = "ORDER BY IT.id_inventario_transferencia DESC";

    $fields = [
      "IT.id_inventario_transferencia",
      ["IT.id_inventario_transferencia", "uid"],
      "IT.id_usuario",
      "IT.id_usuario_recibe",
      "IT.id_sucursal_origen",
      "IT.id_sucursal_destino",
      "IT.observaciones",
      "IT.status",
      "IT.tipo",
      "IT.fecha_creacion",
      "IT.folio",
      "IT.facturado",
      ["DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      ["OS.nombre_sucursal", "nombre_sucursal_origen"],
      ["DS.nombre_sucursal", "nombre_sucursal_destino"],
      "U.nombre_completo",
      ["UR.nombre_completo", "recibio"]
    ];

    $cJoin = "
      LEFT JOIN {$db_dti}_sucursales  AS OS ON (IT.id_sucursal_origen   = OS.id_sucursal)
      LEFT JOIN {$db_dti}_sucursales  AS DS ON (IT.id_sucursal_destino  = DS.id_sucursal)
      LEFT JOIN {$db_ati}_usuarios    AS U  ON (IT.id_usuario           = U.id_usuario)
      LEFT JOIN {$db_ati}_usuarios    AS UR ON (IT.id_usuario_recibe    = UR.id_usuario)
    ";

    $cWhere = [];

    if ($search)          $cWhere[] = ["IT.observaciones", "%{$search}%", "LIKE"];
    if ($originBranchId)  $cWhere[] = ["IT.id_sucursal_origen", $originBranchId];
    if ($destinyBranchId) $cWhere[] = ["IT.id_sucursal_destino", $destinyBranchId];

    if ($fecha_inicio && $fecha_fin) $cWhere[] = ["(DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"];
    if ($fecha_inicio && !$fecha_fin) $cWhere[] = ["(DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio];
    if (!$fecha_inicio && $fecha_fin) $cWhere[] = ["(DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y'))", $fecha_fin];

    $request = useDataTable([
      "column_id"     => $columnId,
      "from"          => $cFrom,
      "where"         => $cWhere,
      "fields"        => $fields,
      "join"          => $cJoin,
      "extra_clauses" => $cExtraClauses,
      "per_page"      => $perPage,
      "page"          => $page
    ]);

    if ($request["status"] == "error") {
      echo getEmptyTableMessage();
      die;
    }

    include __DIR__ . "/{$pageId}_table.php";
    die;
    break;

  /* COMPLETAR TRASPASO */
  case "modal-action-{$pageId}-completar-traspaso":
    $transferId           = cleanStr($_POST["transferId"]);
    $products             = json_decode($_POST["products"], true);
    $haveDiscardProducts  = false;

    //-- Start - Actualizar el traspaso, los productos del traspaso y los números de serie de los productos
    foreach ($products as $product) {
      $inventoryTransferProductId = $product["inventoryTransferProductId"];
      $productId                  = $product["productId"];
      $code                       = $product["code"];
      $name                       = $product["name"];
      $quantity                   = $product["quantity"];
      $requiresSerialNumbers      = $product["requiresSerialNumbers"];
      $serialNumbers              = $product["serialNumbers"];
      $receivedQuantity           = $product["receivedQuantity"];
      $receivedSerialNumbers      = $product["receivedSerialNumbers"];

      if ($receivedQuantity < $quantity) $haveDiscardProducts = true;

      // Actualizar el producto
      $query = "UPDATE {$db_dti}_inventario_transferencia_productos SET
          recibido = '{$receivedQuantity}'
        WHERE
          id_inventario_transferencia_producto = {$inventoryTransferProductId}
      ";

      mysqli_query($mysqli, $query);

      // Marcar los números de serie como aceptados
      if ($requiresSerialNumbers && count($receivedSerialNumbers) > 0) {
        foreach ($receivedSerialNumbers as $serialNumber) {
          $query = "UPDATE {$db_dti}_inventario_transferencia_producto_numeros_serie SET
              recibido = 'si'
            WHERE
              id_inventario_transferencia_producto  = {$inventoryTransferProductId} AND
              numero_serie                          = '{$serialNumber}'
          ";

          mysqli_query($mysqli, $query);
        }
      }
    }
    //-- End - Actualizar el traspaso, los productos del traspaso y los números de serie de los productos

    $estado = $haveDiscardProducts ? 'procesado-con-diferencias' : 'procesado-correctamente';

    $query = "UPDATE {$db_dti}_inventario_transferencias SET
        id_usuario_recibe = {$userId},
        status            = '{$estado}'
      WHERE
        id_inventario_transferencia = {$transferId}
    ";

    $query_result = mysqli_query($mysqli, $query);

    if (!$query_result) {
      $response['toastMessage'] = 'Error al completar el traspaso, intentalo nuevamente';
      break;
    }

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

    $stmt->bind_param('i', $transferId);
    $stmt->execute();

    $query_result = $stmt->get_result();
    $num_rows     = $query_result->num_rows;

    if ($num_rows > 0) :
      while ($row = mysqli_fetch_assoc($query_result)) :
        $data_sucursal_origen   = getBranchOfficeData($row['id_sucursal_origen']);
        $data_sucursal_destino  = getBranchOfficeData($row['id_sucursal_destino']);

        $data_producto = [
          'id_producto'     => $row['id_producto'],
          'nombre_producto' => $row['nombre_producto'],
          'cantidad'        => doubleval($row['cantidad']),
          'stock_origen'    => doubleval($row['stock_origen']),
          'stock_destino'   => doubleval($row['stock_destino'])
        ];

        addKardexLog(
          $row["id_producto"],
          $row['id_sucursal_origen'],
          $row['cantidad'],
          ACCION_INVENTARIO_TRANSFERIR . " hacia {$data_sucursal_destino['nombre_sucursal']}"
        );

        addKardexLog(
          $row["id_producto"],
          $row['id_sucursal_destino'],
          $row['cantidad'],
          ACCION_INVENTARIO_TRANSFERIR . " desde {$data_sucursal_origen['nombre_sucursal']}"
        );
      endwhile;
    endif;

    // pageId-modal-completar-traspaso-numeros-serie-para-ajuste
    $response = [
      'status'        => 'success',
      'toastMessage'  => 'La transferencia se procesó correctamente',
      'callback'      => '{
        load("' . $page . '", "' . $pageId . '");
        $("#' . $pageId . '-modal-completar-traspaso-numeros-serie-para-ajuste").modal("hide");
      }'
    ];
    break;

  /* COMPLETAR TRASPASO OLD */
  case "action-completar-traspaso-{$pageId}--old":
    if (!checkModuleActionPermission($pageId, "completar-traspaso")) break;

    $id_inventario_transferencia = cleanStr($_POST['uid']);

    $query = "UPDATE {$db_dti}_inventario_transferencias SET
        id_usuario_recibe = {$userId},
        status = 'completado'
      WHERE
        id_inventario_transferencia = {$id_inventario_transferencia}
    ";

    $query_result = mysqli_query($mysqli, $query);

    if (!$query_result) {
      $response['toastMessage'] = 'Error al completar el traspaso, intentalo nuevamente';
      break;
    }

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
        $data_sucursal_origen   = getBranchOfficeData($row['id_sucursal_origen']);
        $data_sucursal_destino  = getBranchOfficeData($row['id_sucursal_destino']);

        $data_producto = [
          'id_producto'     => $row['id_producto'],
          'nombre_producto' => $row['nombre_producto'],
          'cantidad'        => doubleval($row['cantidad']),
          'stock_origen'    => doubleval($row['stock_origen']),
          'stock_destino'   => doubleval($row['stock_destino'])
        ];

        addKardexLog(
          $row["id_producto"],
          $row['id_sucursal_origen'],
          $row['cantidad'],
          ACCION_INVENTARIO_TRANSFERIR . " hacia {$data_sucursal_destino['nombre_sucursal']}"
        );

        addKardexLog(
          $row["id_producto"],
          $row['id_sucursal_destino'],
          $row['cantidad'],
          ACCION_INVENTARIO_TRANSFERIR . " desde {$data_sucursal_origen['nombre_sucursal']}"
        );
      endwhile;

      $response = [
        'status'        => 'success',
        'toastMessage'  => 'La transferencia se completó correctamente',
        'callback'      => 'load("' . $page . '", "' . $pageId . '");'
      ];
    endif;
    break;
}

mysqli_close($mysqli);
echo json_encode($response);
exit;
