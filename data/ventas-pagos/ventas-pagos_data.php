<?php
require '../lib/settings.inc.php';
require '../lib/helpers/sales.helper.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'ventas-pagos';
//$id_sucursal = getSessionBranchOfficeId();
$id_venta = cleanStr($_POST['id_venta']);

$branchId = getSessionBranchOfficeId();
$userId   = get_id_usuario();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = cleanStr($_POST['id_sucursal']);
    $fecha            = cleanStr($_POST['fecha']);
    $tipo_productos   = cleanStr($_POST['tipo_productos']);

    $column_id        = "id_venta";
    $c_from           = "{$db_dti}_venta_pagos";
    $c_extra_clauses  = "ORDER BY id_venta_pago DESC";
    $sale_id          = cleanStr($_POST['sale_id']);

    $sale_data = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT id_venta FROM {$db_dti}_ventas WHERE MD5(id_venta) = '{$sale_id}' LIMIT 1"));

    $invoice        = getSaleInvoiceBySaleIdAndType($sale_data["id_venta"], "ingreso");
    $hasPPDInvoice  = $invoice["metodo_pago"] === "PPD";

    $fields = [
      "*",
      ["id_venta_pago", "uid"],
      ["(DATE_FORMAT(fecha_hora, '%d/%m/%Y'))", "fecha_hora_formato"]
    ];

    $c_join = "";

    $c_where = [
      ["(MD5(id_venta))", $sale_id]
    ];

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
  /*  */
  case 'add-' . $identifier:
    if (checkModuleActionPermission($identifier, 'agregar')) :
      $saleId = cleanStr($_POST['id_venta']);
      $folio  = createSalePaymentFolio($saleId);

      $_POST["folio"]       = $folio;
      $_POST["id_usuario"]  = $userId;
      $_POST["id_sucursal"] = $branchId;

      $_POST["fecha_hora"]  = date("Y-m-d H:i:s", strtotime($_POST["fecha_hora"] . date(" H:i:s")));
      $_POST["monto_total"] = cleanStr($_POST["efectivo_monto"]) + cleanStr($_POST["cheque_monto"]) + cleanStr($_POST["transferencia_monto"]) + cleanStr($_POST["tarjeta_credito_monto"]) + cleanStr($_POST["tarjeta_debito_monto"]);

      if ($_POST["monto_total"] <= 0) {
        $response["toastMessage"] = "El monto total debe ser mayor a $0.00";
        break;
      }

      $request = useInsertByPost([
        'table_name' => "{$db_dti}_venta_pagos",
        "excluded_fields" => ["origin", "sale_totalToPay", "sale_balance", "sale_newBalance"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') {
        updateSalePayedStatus(cleanStr($_POST["id_venta"]));

        $response = [
          'status'        => 'success',
          "title"         => "Success",
          'alertMessage'  => '¡El pago se agregó correctamente!',
          'callback'      => 'location.reload()'
        ];
      }
    endif;
    break;
  /*  */
  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_venta_pago             = cleanStr($_POST['uid']);
      $_POST["fecha_hora"]  = date("Y-m-d H:i:s", strtotime($_POST["fecha_hora"] . date(" H:i:s")));
      $_POST["monto_total"] = cleanStr($_POST["efectivo_monto"]) + cleanStr($_POST["cheque_monto"]) + cleanStr($_POST["transferencia_monto"]) + cleanStr($_POST["tarjeta_credito_monto"]) + cleanStr($_POST["tarjeta_debito_monto"]);

      if ($_POST["monto_total"] <= 0) {
        $response["toastMessage"] = "El monto total debe ser mayor a $0.00";
        break;
      }

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_venta_pagos",
        'conditions' => [['id_venta_pago', $id_venta_pago]],
        "excluded_fields" => ["origin", "sale_totalToPay", "sale_balance", "sale_newBalance"]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') {
        updateSalePayedStatus(cleanStr($_POST["id_venta"]));

        $response = [
          'status'        => 'success',
          "title"         => "Success",
          'alertMessage'  => '¡El pago se actualizó correctamente!',
          'callback'      => 'location.reload()'
        ];
      }
    endif;
    break;
  /*  */
  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_venta_pago = cleanStr($_POST['uid']);

      $query = "SELECT id_venta FROM {$db_dti}_venta_pagos WHERE id_venta_pago = {$id_venta_pago} LIMIT 1";
      $result = mysqli_query($mysqli, $query);
      $data = mysqli_fetch_assoc($result);
      $id_venta = $data['id_venta'];

      $query        = "DELETE FROM {$db_dti}_venta_pagos WHERE id_venta_pago = $id_venta_pago";
      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) {
        updateSalePayedStatus($id_venta);

        $response = [
          'status'        => 'success',
          "title"         => "Success",
          'alertMessage'  => 'El pago se eliminó correctamente',
          'callback'      => 'location.reload()'
        ];
      }
    endif;
    break;
};

//$response['post'] = json_encode($_POST);

echo json_encode($response);
mysqli_close($mysqli);
die;
