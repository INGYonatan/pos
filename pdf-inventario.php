<?php
require_once __DIR__ . "/inc/session.inc.php";
require_once __DIR__ . "/models/inventario/pdf.template.inventario.model.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$isExcel          = cleanStr($_GET["isExcel"]) ?? "no";

if ($isExcel == "si") {
  $fecha_hoy        = date('Ym');
  $titulo_excel     = 'Inventario - ' . $fecha_hoy;
  $table_row_number = (($page - 1) * $per_page) + 1;

  header('Content-Type: text/html; charset=utf-8');
  header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
  header('Content-Disposition: attachment; filename=' . $titulo_excel . '.xls');
}

// Obtener todos los productos
$month            = cleanStr($_GET["month"]);
$branchOfficeId   = $IS_ADMIN ? cleanStr($_GET["sid"]) : getSessionBranchOfficeId();
$brandId          = cleanStr($_GET["brandId"]);
$categoryId       = cleanStr($_GET["categoryId"]);
$familyId         = cleanStr($_GET["familyId"]);
$typeIds          = cleanStr($_GET["typeIds"]);
$hideStockZero    = cleanStr($_GET["hideStockZero"]) ?? "no";

if (!$IS_ADMIN) $branchOfficeId = getSessionBranchOfficeId();

$brandName        = null;
$categoryName     = null;
$familyName       = null;

$branchOfficeData = $branchOfficeId ? getBranchOfficeData($branchOfficeId) : [];
$byBranchOfficeId = $branchOfficeId ? "id_sucursal = {$branchOfficeId}" : "1=1";

$products = [];

$byMonth      = $month      ? "DATE_FORMAT(P.fecha_creacion, '%Y-%m') = '{$month}'" : "1=1";
$byBrandId    = $brandId    ? "P.id_marca = {$brandId}"               : "1=1";
$byCategoryId = $categoryId ? "P.id_categoria = {$categoryId}"        : "1=1";
$byFamilyId   = $familyId   ? "P.id_categoria_familia = {$familyId}"  : "1=1";
$byTypeIds    = $typeIds    ? "P.id_tipo IN ({$typeIds})"             : "1=1";

$orders = [];
$orders[] = "M.marca ASC";
$orders[] = "P.id_categoria ASC";
$orders[] = "P.id_categoria_familia ASC";

$orders = implode(", ", $orders);

// Obtener el nombre de la marca
// if ($brandId) {
//   $query = "SELECT marca FROM {$db_dti}_marcas WHERE id_marca = {$brandId} LIMIT 1";
//   $result = mysqli_query($mysqli, $query);
//   $data = mysqli_fetch_assoc($result);
//   $brandName = $data['marca'];
// }

// // Obtener el nombre de la categoría
// if ($categoryId) {
//   $query = "SELECT categoria FROM {$db_dti}_categorias WHERE id_categoria = {$categoryId} LIMIT 1";
//   $result = mysqli_query($mysqli, $query);
//   $data = mysqli_fetch_assoc($result);
//   $categoryName = $data['categoria'];
// }

// // Obtener el nombre de la familia
// if ($familyId) {
//   $query = "SELECT familia FROM {$db_dti}_categoria_familias WHERE id_categoria_familia = {$familyId} LIMIT 1";
//   $result = mysqli_query($mysqli, $query);
//   $data = mysqli_fetch_assoc($result);
//   $familyName = $data['familia'];
// }

$query = "SELECT
    P.id_producto,
    P.codigo,
    P.nombre_producto,
    P.precio_costo,
    P.precio_costo_original,
    P.unidad_entrada,
    P.unidad_salida,
    P.numero_piezas,
    M.marca,
    C.categoria
  FROM
    {$db_dti}_productos AS P
  LEFT JOIN
    {$db_dti}_marcas AS M ON P.id_marca = M.id_marca
  LEFT JOIN
    {$db_dti}_categorias AS C ON P.id_categoria = C.id_categoria
  WHERE
    P.status = 'activo' AND
    ({$byMonth}) AND
    ({$byBrandId}) AND
    ({$byCategoryId}) AND
    ({$byFamilyId}) AND
    ({$byTypeIds})
  ORDER BY
    {$orders}
";

$result = mysqli_query($mysqli, $query);

while ($row = mysqli_fetch_assoc($result)) {
  $productId            = $row["id_producto"];
  $productCode          = $row["codigo"];
  $productName          = $row["nombre_producto"];
  $productBrand         = $row["marca"];
  $productCategory      = $row["categoria"];
  $productCostPrice     = floatval($row["precio_costo_original"]);
  $productUnitEntry     = $row["unidad_entrada"];
  $productPiecesPerUnit = $row["numero_piezas"] > 0 ? intval($row["numero_piezas"]) : 1;

  if ($productUnitEntry == "caja") $productCostPrice = $productCostPrice / $productPiecesPerUnit;


  // Obtener la existencia del producto
  $queryStock = "SELECT SUM(stock) AS total FROM {$db_dti}_inventario WHERE id_producto = {$productId} AND ({$byBranchOfficeId})";
  $dataStock  = mysqli_fetch_assoc(mysqli_query($mysqli, $queryStock));
  $totalStock = $dataStock["total"];

  if ($hideStockZero == "si" && $totalStock <= 0) continue;

  $totalPurchasePrice = $totalStock * $productCostPrice;

  $item = new PDFTemplateInventarioListItemModel();
  $item->setId($productCode);
  $item->setName($productName);
  $item->setBrand($productBrand);
  $item->setCategory($productCategory);
  $item->setLastPurchasePrice($productCostPrice);
  $item->setStock($totalStock);
  $item->setTotalPurchasePrice($totalPurchasePrice);

  $products[] = $item;
}

if ($isExcel == "no") {
  $fileName = "inventario-" . date("YmdHis");

  $pdf = new PDFTemplateInventarioModel();

  $pdf->setPdfTitle($fileName);
  $pdf->setTypeProducts("Todos");
  $pdf->setTypeSuppliers($branchOfficeId ? $branchOfficeData["nombre_sucursal"] : "Todos");
  $pdf->setLine("--");
  $pdf->setDate(date("d/m/Y h:i a"));
  $pdf->setUser($admp_session_user_data["nombre_completo"]);
  $pdf->setProducts($products);

  $pdf->createPDF();
  $pdf->showPDF();
}
?>

<?php if ($isExcel == "si") : ?>
  <table cellspacing="3" cellpadding="5" border="0">
    <thead>
      <tr>
        <th class="cellheader text-bold" width="12%">Clave</th>
        <th class="cellheader text-bold" width="25%">Descripción</th>
        <th class="cellheader text-bold" width="18%">Marca</th>
        <th class="cellheader text-bold" width="12%">Línea</th>
        <th class="cellheader text-bold text-end" width="10%">Ult. Costo</th>
        <th class="cellheader text-bold text-center" width="10%">Existencia</th>
        <th class="cellheader text-bold text-end" width="13%">Costo total</th>
      </tr>
    </thead>

    <tbody>
      <?php
      $totalProducts  = count($products);
      $totalStock     = 0;
      $totalAmount    = 0;
      foreach ($products as $product) :
        /**
         * @var PDFTemplateInventarioListItemModel $product
         */
        $totalStock   += $product->getStock();
        $totalAmount  += $product->getTotalPurchasePrice();
      ?>
        <tr>
          <td class="cellbody" width="12%"><?= $product->getId(); ?></td>
          <td class="cellbody" width="25%"><?= $product->getName(); ?></td>
          <td class="cellbody" width="18%"><?= $product->getBrand(); ?></td>
          <td class="cellbody" width="12%"><?= $product->getCategory(); ?></td>
          <td class="cellbody text-end" width="10%">$<?= $product->getLastPurchasePrice() ? number_format($product->getLastPurchasePrice(), 2) : "0.00"; ?></td>
          <td class="cellbody text-center" width="10%"><?= number_format($product->getStock()); ?></td>
          <td class="cellbody text-end" width="13%">$<?= $product->getTotalPurchasePrice() ? number_format($product->getTotalPurchasePrice(), 2) : "0.00"; ?></td>
        </tr>
      <?php endforeach; ?>

      <tr>
        <td class="cellbody" colspan="4">Total de registros impresos: <?= $totalProducts; ?></td>
        <td class="cellbody text-bold text-end">Total:</td>
        <td class="cellbody text-bold text-center" style="border-top: 1px solid #333;"><?= $totalStock; ?></td>
        <td class="cellbody text-bold text-end" style="border-top: 1px solid #333;">$<?= number_format($totalAmount, 2); ?></td>
      </tr>
    </tbody>
  </table>
<?php endif; ?>