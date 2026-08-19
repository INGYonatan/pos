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
$identifier       = 'facturas-anticipo-compra';
$invoiceTable     = "{$db_dti}_facturas_anticipo_compra";

$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');


const FCS_CARPETA_URL   = CARPETA_FACTURAS_INGRESO_URL;
const FCS_CARPETA_PATH  = CARPETA_FACTURAS_INGRESO_PATH;

function cancelarFactura(
  $idFactura,
  $UUID,
  $rfcEmisor,
  $motivo,
  $folioSustituto
) {
  global $mysqli;
  global $invoiceTable;

  $debug = 1;

  //pruebas
  //$rfcEmisor = "ESI920427886";

  $url_timbrado   = FACTURAS_URL_TIMBRADO;
  $user_id        = FACTURAS_ID_USUARIO;
  $user_password  = FACTURAS_PASSWORD;

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
    mysqli_query($mysqli, "UPDATE {$invoiceTable} SET cancelado = 1 WHERE id_factura= '" . $idFactura . "'");

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

    $search           = cleanStr($_POST["search"]);
    $issuerId         = cleanStr($_POST["id_emisor"]);
    $branchId         = $IS_ADMIN ? cleanStr($_POST["id_sucursal"]) : getSessionBranchOfficeId();
    // $customerId       = cleanStr($_POST["id_cliente"]);

    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);
    $status           = cleanStr($_POST['status']);

    $column_id        = "F.id_factura";
    $c_from           = "{$invoiceTable} AS F";
    $c_extra_clauses  = "ORDER BY F.id_factura DESC";

    $fields = [
      "F.id_factura",
      ["F.id_factura", "uid"],
      "F.id_emisor",
      "F.id_cliente",
      "F.id_uso_cfdi",
      "F.id_forma_pago",
      "F.id_venta",
      "F.serie",
      "F.folio",
      "F.uuid",
      "F.metodo_pago",
      "F.moneda",
      "F.total",
      "F.comentarios",
      "F.enviado",
      "F.cancelado",
      "F.pagado",
      "F.fecha",
      ["(DATE_FORMAT(F.fecha, '%d-%m-%Y %h:%i %p'))", "fecha_formato"],
      ["E.nombre_razon_social", "Emisor"],
      ["C.nombre_completo", "nombre_cliente"],
      ["V.folio", "folio_venta"],
      ["FP.descripcion", "forma_pago"]
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_emisores AS E ON (E.id_emisor = F.id_emisor)
      LEFT JOIN {$db_dti}_clientes AS C ON (C.id_cliente = F.id_cliente)
      LEFT JOIN {$db_dti}_ventas AS V ON (V.id_venta = F.id_venta)
      LEFT JOIN {$db_dti}_formas_pago AS FP ON (FP.id = F.id_forma_pago)
      LEFT JOIN {$db_dti}_sucursales AS S ON (S.id_sucursal = F.id_sucursal)
    ";

    $c_where = [];

    $filtersSearch = [
      ["F.uuid",  "%$search%", "LIKE"],
      ["F.serie",  "%$search%", "LIKE", "OR"],
      ["F.folio",  "%$search%", "LIKE", "OR"],
      ["CONCAT(F.serie, '-', F.folio)", "%$search%", "LIKE", "OR"],
      ["V.folio",  "%$search%", "LIKE", "OR"],
      ["C.nombre_completo",  "%$search%", "LIKE", "OR"],
      ["E.nombre_razon_social",  "%$search%", "LIKE", "OR"],
      ["F.total",  "%$search%", "LIKE", "OR"]
    ];

    if ($IS_ADMIN) $filtersSearch[] = ["S.nombre_sucursal",  "%$search%", "LIKE", "OR"];

    if (!empty($search)) array_push($c_where, [$filtersSearch]);

    if (!empty($issuerId)) array_push($c_where, ["F.id_emisor", $issuerId]);
    if (!empty($branchId)) array_push($c_where, ["F.id_sucursal", $branchId]);
    // if (!empty($customerId)) array_push($c_where, ["F.id_cliente", $customerId]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(F.fecha, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(F.fecha, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(F.fecha, '%d-%m-%Y'))", $fecha_fin]);

    if ($status === "activo")    array_push($c_where, ["cancelado", "0"]);
    if ($status === "cancelado") array_push($c_where, ["cancelado", "1"]);

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
          {$invoiceTable} AS F
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
        {$invoiceTable} AS F
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
      $pdf          = FCS_CARPETA_PATH . $uuid . ".pdf";
      $xml          = FCS_CARPETA_PATH . $uuid . ".xml";
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
          mysqli_query($mysqli, "UPDATE {$invoiceTable} SET enviado = 1 WHERE id_factura = {$id_factura}");

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
