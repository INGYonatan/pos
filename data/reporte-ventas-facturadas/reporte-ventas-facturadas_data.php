<?php
require_once __DIR__ . '/../lib/settings.inc.php';
require_once __DIR__ . "/../lib/helpers/serial-numbers.helper.php";
require_once __DIR__ . "/../lib/helpers/products.helper.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$user_data   = getUserData(get_id_usuario());

$action      = $_POST['action'];
$identifier  = 'reporte-ventas-facturadas';

$model = new SerialNumbersHelper();

switch ($action) {
  case "load-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'ver')) break;

    $haveActions = haveActions($identifier, 'tabla');

    $page           = $_POST['page'];
    $per_page       = $_POST['perPage'];
    $term           = cleanStr($_POST['search']);
    $date           = $_POST["date"] ? cleanStr($_POST['date']) : date('Y-m-d');
    $id_sucursal    = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();

    $columnId       = "V.id_venta";
    $cFrom          = "{$db_dti}_ventas AS V";
    $cExtraClauses  = "ORDER BY V.id_venta DESC";

    $cJoin          = "
      INNER JOIN {$db_dti}_facturas AS F ON (F.id_venta = V.id_venta AND F.cancelado = 0)
      LEFT JOIN {$db_dti}_sucursales AS S ON S.id_sucursal = V.id_sucursal
      LEFT JOIN {$db_dti}_clientes AS C ON C.id_cliente = V.id_cliente
    ";

    $fields = [
      "V.*",
      ["V.id_venta", "uid"],
      ["S.nombre_sucursal", "nombre_sucursal"],
      ["C.nombre_completo", "nombre_cliente"],
      ["(CONCAT(F.serie, '-', F.folio))", "folio_factura"]
    ];

    $cWhere = [];

    $filtersSearch = [
      ["V.folio",  "%$term%", "LIKE", "OR"],
      ["C.nombre_completo",  "%$term%", "LIKE", "OR"],
      ["(CONCAT(F.serie, '-', F.folio))", "%$term%", "LIKE", "OR"],
    ];

    if ($IS_ADMIN) $filtersSearch[] = ["S.nombre_sucursal",  "%$term%", "LIKE", "OR"];

    if (!empty($term)) $cWhere[] = [$filtersSearch];

    if (!empty($date)) $cWhere[] = ["(DATE(F.fecha))", $date];

    if (!empty($id_sucursal)) $cWhere[] = ["V.id_sucursal", $id_sucursal];

    $result = useDataTable([
      "column_id"     => $columnId,
      "from"          => $cFrom,
      "join"          => $cJoin,
      "fields"        => $fields,
      "where"         => $cWhere,
      "extra_clauses" => $cExtraClauses,
      "page"          => $page,
      "per_page"      => $per_page
    ]);

    // if ($result["status"] == "error")   echo getEmptyTableMessage();
    // if ($result["status"] == "success") 
    include __DIR__ . "/{$identifier}_table.php";
    exit;
    break;

  case "edit-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'editar')) break;

    $id           = cleanStr($_POST['uid']);
    $serialNumber = cleanStr($_POST['serialNumber']);

    $model->getById($id);

    if (!$model->getId()) break;

    $model->setSerialNumber($serialNumber);

    $result = $model->update();

    if ($result->status == "error") $response['toastMessage'] = $result->message;

    if ($result->status == "success") $response = [
      "status"        => "success",
      "toastMessage"  => "¡Registro actualizado exitosamente!",
      "callback"      => '{
        load("' . $page . '", "' . $identifier . '-disponibles");
        load("' . $page . '", "' . $identifier . '-vendidos");
      }'
    ];
    break;
}

mysqli_close($mysqli);
echo json_encode($response);
exit;
