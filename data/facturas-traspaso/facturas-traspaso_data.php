<?php
require '../lib/settings.inc.php';

require_once __DIR__ . "/../lib/php-mailer/vendor/autoload.php";
require_once __DIR__ . "/../lib/facturacion/facturacion-moderna/facturacion-moderna.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'facturas-traspaso';

$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');

function cancelarFactura(
  $idFactura,
  $UUID,
  $rfcEmisor,
  $motivo,
  $folioSustituto
) {
  global $mysqli;
  global $db_dti;

  $debug = 1;

  //pruebas
  //$rfcEmisor = "ESI920427886";

  $url_timbrado = "https://wsdemo.dinvbox.mx/timbrado/wsdl";
  $user_id = "UsuarioPruebasWS";
  $user_password = "b9ec2afa3361a59af4b4d102d3f704eabdf097d4";

  //producci贸n
  //$rfc_emisor = "HIMY840518KQ6";
  //
  //$url_timbrado = "https://t2.facturacionmoderna.com/timbrado/wsdl";
  //$user_id = "HIMY840518KQ6";
  //$user_password = "68958d52976d8075fc79966e9ef5a274509409e5";


  $parametros = array('emisorRFC' => $rfcEmisor, 'UserID' => $user_id, 'UserPass' => $user_password);
  $cliente = new FacturacionModerna($url_timbrado, $parametros, $debug);


  /* Nuevos Parametros Cancelacion 2022 */
  if ($motivo == '01') {
    $opciones = array('Motivo' => $motivo, 'FolioSustitucion' => $folioSustituto);
  } else {
    $opciones = array('Motivo' => $motivo);
  }

  if ($cliente->cancelar($UUID, $opciones)) {

    $res =  "Cancelación exitosa\n";
    mysqli_query($mysqli, "UPDATE {$db_dti}_facturas_traslado SET cancelado = 1 WHERE id_factura= '" . $idFactura . "'");

    return [
      "status"  => "success",
      "message" => $res
    ];
  } else {
    return [
      "status"  => "error",
      "message" => "[" . $cliente->ultimoCodigoError . "] - " . $cliente->ultimoError
    ];
  }
}

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $inventoryTransferId           = cleanStr($_POST['inventoryTransferId']);
    $branchId         = $IS_ADMIN ? cleanStr($_POST["id_sucursal"]) : getSessionBranchOfficeId();

    $issuerId         = cleanStr($_POST["id_emisor"]);

    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $status           = cleanStr($_POST['status']);

    $column_id        = "F.id_factura";
    $c_from           = "{$db_dti}_facturas_traslado AS F";
    $c_extra_clauses  = "ORDER BY F.id_factura DESC";

    /* 
    https://www.youtube.com/watch?v=AclxHKf3x30&ab_channel=ONEMediaEspa%C3%B1ol
    */

    $fields = [
      "F.id_factura",
      ["F.id_factura", "uid"],
      "F.id_emisor",
      "F.id_receptor",
      "F.id_uso_cfdi",
      "F.id_inventario_transferencia",
      "F.serie",
      "F.folio",
      "F.uuid",
      "F.moneda",
      "F.subtotal",
      "F.exportacion",
      "F.total",
      "F.comentarios",
      "F.enviado",
      "F.cancelado",
      "F.pagado",
      "F.fecha",
      ["(DATE_FORMAT(F.fecha, '%d-%m-%Y %h:%i %p'))", "fecha_formato"],
      "F.tipo_relacion",
      "F.cfdi_relacionado",
      "F.id_sucursal",
      ["E.nombre_razon_social", "Emisor"],
      ["S.nombre_sucursal", "nombre_sucursal"]
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_emisores AS E   ON (E.id_emisor   = F.id_emisor)
      LEFT JOIN {$db_dti}_sucursales AS S ON (S.id_sucursal = F.id_sucursal)
    ";

    $c_where = [];

    $filtersSearch = [
      ["F.uuid",  "%$search%", "LIKE"],
      ["F.serie",  "%$search%", "LIKE", "OR"],
      ["F.folio",  "%$search%", "LIKE", "OR"],
      ["CONCAT(F.serie, '-', F.folio)", "%$search%", "LIKE", "OR"],
      // ["V.folio",  "%$search%", "LIKE", "OR"],
      // ["C.nombre_completo",  "%$search%", "LIKE", "OR"],
      ["E.nombre_razon_social",  "%$search%", "LIKE", "OR"],
      // ["F.total",  "%$search%", "LIKE", "OR"]
    ];

    // if ($IS_ADMIN) $filtersSearch[] = ["S.nombre_sucursal",  "%$search%", "LIKE", "OR"];

    if (!empty($search)) array_push($c_where, [$filtersSearch]);

    if (!empty($inventoryTransferId)) array_push($c_where, ["(MD5(F.id_inventario_transferencia))", $inventoryTransferId]);

    if (!empty($branchId)) array_push($c_where, ["F.id_sucursal", $branchId]);
    if (!empty($issuerId)) array_push($c_where, ["F.id_emisor", $issuerId]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(F.fecha, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(F.fecha, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(F.fecha, '%d-%m-%Y'))", $fecha_fin]);

    if ($status === "activo")    array_push($c_where, ["F.cancelado", "0"]);
    if ($status === "cancelado") array_push($c_where, ["F.cancelado", "1"]);

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

    #echo getEmptyTableMessage($request["query"]);
    #die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';
    die;
    break;

  case "cancelar-factura":
    $idFactura      = $_POST['uid'];
    $motivo         = $_POST['motivo'];
    $folioSustituto = $_POST['folioSustituto'];

    if ($idFactura) :
      $query = "SELECT
          F.uuid,
          E.rfc
        FROM
          {$db_dti}_facturas_traslado AS F
        LEFT JOIN
          {$db_dti}_emisores AS E ON (E.id_emisor = F.id_emisor)
        WHERE
          id_factura = '$idFactura'
        LIMIT 1
      ";

      $queryResult = mysqli_query($mysqli, $query);
      $numRows     = mysqli_num_rows($queryResult);

      if ($numRows > 0) :
        $data       = mysqli_fetch_assoc($queryResult);
        $UUID       = $data['uuid'];
        $rfcEmisor  = $data['rfc'];

        $result = cancelarFactura($idFactura, $UUID, $rfcEmisor, $motivo, $folioSustituto);

        if ($result["status"] == "error") $result["toastMessage"] = $result["message"];

        if ($result["status"] == "success") $response = [
          'status'        => 'success',
          'toastMessage'  => '¡Factura cancelada con éxito!',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;

  case "action-send-invoice-{$identifier}":
    $id_factura = $_POST["uid"];

    $query = "SELECT
        F.id_cliente,
        F.uuid,
        C.nombre_completo,
        C.rfc,
        E.nombre_razon_social,
        C.correo
      FROM
        {$db_dti}_facturas_p AS F
      LEFT JOIN
        {$db_dti}_clientes AS C ON (C.id_cliente = F.id_cliente)
      LEFT JOIN
        {$db_dti}_emisores AS E ON (E.id_emisor = F.id_emisor)
      WHERE
        F.id_factura = {$id_factura}
      LIMIT 1
    ";

    $result   = mysqli_query($mysqli, $query);
    $numRows  = mysqli_num_rows($result);

    if ($numRows > 0) :
      $data = mysqli_fetch_assoc($result);

      $customerId   = $data["id_cliente"];
      $customerName = $data["nombre_completo"];
      $customerRFC  = $data["rfc"];
      $senderName   = $data["nombre_razon_social"];
      $uuid         = $data["uuid"];
      $pdf          = BASE_PATH . '/src/assets/facturacion/comprobantes/' . $uuid . ".pdf";
      $xml          = BASE_PATH . '/src/assets/facturacion/comprobantes/' . $uuid . ".xml";
      $emails       = [];

      if ($data["correo"]) array_push($emails, [
        "name"  => $customerName,
        "email" => $data["correo"],
      ]);

      if (sizeof($emails) == 0) $response["toastMessage"] = "No hay correos electrónicos configurados";

      if (sizeof($emails) > 0) :
        $mail = new PHPMailer(true);

        $sender           = new stdClass();
        $sender->name     = $senderName;

        $customer         = new stdClass();
        $customer->rfc    = $customerRFC;
        $customer->name   = $customerName;
        $customer->emails = $emails;

        $sendResponse = sendInvoice(
          $mail,
          $sender,
          $customer,
          $xml,
          $pdf
        );

        if ($sendResponse["status"] == "error") $response["toastMessage"] = $sendResponse["message"];

        if ($sendResponse["status"] == "success") :
          mysqli_query($mysqli, "UPDATE paal_facturas_p SET enviado = 1 WHERE id_factura = {$id_factura}");

          $response = [
            "status"        => "success",
            "toastMessage"  => "Los archivos se enviaron correctamente",
            'callback'      => 'load("' . $page . '", "' . $identifier . '");'
          ];
        endif;
      endif;
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
