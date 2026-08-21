<?php

/**
 * @var string $db_dti
 */

require_once __DIR__ . '/../lib/settings.inc.php';
require_once __DIR__ . "/../lib/helpers/customers.helper.php";
require_once __DIR__ . "/../lib/helpers/sales.helper.php";

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$user_data   = getUserData(get_id_usuario());

$action      = $_POST['action'];
$identifier  = "cuentas-por-cobrar-estado-de-cuenta";

switch ($action) {
  case "load-{$identifier}":
    if (!checkModuleActionPermission($identifier, "ver")) break;

    $customerMd5Id  = cleanStr($_POST["customerId"]);
    $page           = $_POST["page"]    ?? 1;
    $perPage        = $_POST["perPage"] ?? 15;
    $term           = cleanStr($_POST["search"]);

    $fecha_inicio   = cleanStr($_POST['fecha_inicio']);
    $fecha_fin      = cleanStr($_POST['fecha_fin']);

    $customersModel = new CustomerHelper();
    $customersModel->getByMd5Id($customerMd5Id);

    if (!$customersModel->getId()) break;

    $columnId       = "V.id_venta";
    $cFrom          = "{$db_dti}_ventas AS V";
    $cExtraClauses  = "ORDER BY V.id_venta DESC";

    $cJoin = "
      INNER JOIN {$db_dti}_sucursales AS S ON (V.id_sucursal = S.id_sucursal)
      INNER JOIN {$db_dti}_clientes AS C ON (V.id_cliente = C.id_cliente)
    ";

    $fields = [
      "V.*",
      ["DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      "S.nombre_sucursal"
    ];

    $cWhere = [
      ["V.id_cliente", "{$customersModel->getId()}"],
      ["V.forma_pago", "credito"],
      ["V.status", "activo"]
    ];

    $filtersSearch = [
      ["V.folio",  "%{$term}%", "LIKE"]
    ];

    if ($term) $cWhere[] = [$filtersSearch];

    if ($fecha_inicio && $fecha_fin) $cWhere[] = ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"];
    if ($fecha_inicio && !$fecha_fin) $cWhere[] = ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio];
    if (!$fecha_inicio && $fecha_fin) $cWhere[] = ["(DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y'))", $fecha_fin];

    $result = useDataTable([
      "column_id"     => $columnId,
      "from"          => $cFrom,
      "where"         => $cWhere,
      "fields"        => $fields,
      "join"          => $cJoin,
      "extra_clauses" => $cExtraClauses,
      "per_page"      => $perPage,
      "page"          => $page
    ]);

    // Obtener el total de todas las ventas
    $totalAmount = getSalesTotalByCustomerId($customersModel->getId());

    // Obtener el total abonado de todas las ventas
    $totalPaid = getSalesTotalPaidByCustomerId($customersModel->getId());

    // Saldo pendiente
    $balance = $totalAmount - $totalPaid;

    if ($result["status"] === "error")   echo getEmptyTableMessage();
    if ($result["status"] === "success") include "{$identifier}_table.php";

    $totalAmountFormat = number_format($totalAmount, DECIMALS_CURRENCY_TICKET);
    $totalPaidFormat   = number_format($totalPaid, DECIMALS_CURRENCY_TICKET);
    $balanceFormat     = number_format($balance, DECIMALS_CURRENCY_TICKET);
?>
    <script>
      $("#totalAmount").text("$<?= $totalAmountFormat; ?>");
      $("#totalPaid").text("$<?= $totalPaidFormat; ?>");
      $("#balance").text("$<?= $balanceFormat; ?>");
    </script>
<?php
    die;
}

mysqli_close($mysqli);
echo json_encode($response);
exit;
