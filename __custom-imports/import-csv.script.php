<?php
require_once __DIR__ . "/../inc/settings.inc.php";

try {
  // Leer el archivo Berfran CSV
  $file = fopen(__DIR__ . "/inventario.csv", 'r');

  if ($file === false) {
    die('Error al abrir el archivo CSV.');
  }

  $header           = fgetcsv($file); // Leer la primera línea como encabezados
  $skuPosition              = 0;
  $namePosition             = 1;
  $typePosition             = 2;
  $brandPosition            = 3;
  $brandLinePosition        = 4;
  $brandLineFamilyPosition  = 5;
  $supplierPosition         = 6;
  $entryUnitPosition        = 7;
  $entryNumPiecesPosition   = 8;
  $exitUnitPosition         = 9;
  $unitKeyPosition          = 10;
  $satKeyPosition           = 11;
  $applyIvaPosition         = 12;
  $inDollarPosition         = 13;
  $costPricePosition        = 14;
  $salePricePosition        = 15;

  $branch_TGZ_position      = 18; // Tuxtla Gutiérrez
  $branch_MID_position      = 19; // Merida
  $branch_VHS_position      = 20; // Villahermosa
  $branch_CUN_position      = 21;  // Cancún
  $branch_DIG_position      = 22; // Digital

  $count = 1;

  while (($row = fgetcsv($file)) !== false) {
    echo "procesando registro {$count}<br>";
    $count++;

    $sku              = $row[$skuPosition];
    $name             = $row[$namePosition];
    $type             = $row[$typePosition];
    $brand            = $row[$brandPosition];
    $brandLine        = $row[$brandLinePosition];
    $brandLineFamily  = $row[$brandLineFamilyPosition];
    $supplier         = $row[$supplierPosition];
    $entryUnit        = $row[$entryUnitPosition];
    $entryNumPieces   = $row[$entryNumPiecesPosition];
    $exitUnit         = $row[$exitUnitPosition];
    $unitKey          = $row[$unitKeyPosition];
    $satKey           = $row[$satKeyPosition];
    $applyIva         = strtolower($row[$applyIvaPosition]);
    $inDollar         = strtolower($row[$inDollarPosition]);
    $costPrice        = $row[$costPricePosition];
    $salePrice        = $row[$salePricePosition];

    if ($entryUnit == "PZ") $entryUnit = "unidad";
    if ($exitUnit == "PZ")  $exitUnit  = "unidad";

    // validar el Tipo
    $typeId = verifyProductType($type);
    if (!$typeId) continue;

    // Validar Marca
    $brandId = verifyBrand($brand);
    if (!$brandId) continue;

    // Validar Línea de Marca
    $brandLineId = verifyBrandLine($brandId, $brandLine);
    if (!$brandLineId) continue;

    // Validar Familia de Línea de Marca
    $brandLineFamilyId  = verifyBrandLineFamily($brandLineId, $brandLineFamily);
    if (!$brandLineFamilyId) continue;

    // Validar Proveedor
    $supplierId = verifySupplier($supplier);
    if (!$supplierId) continue;

    // Validar unidad de medida
    $unitKeyId = getUnitKeyIdByKey($unitKey);

    // validar clave de producto SAT
    $satKeyId = getSatKeyIdByKey($satKey);

    if ($inDollar == "si") {
      $exchange   = getTipoCambio();

      $costPrice  = $costPrice * $exchange;
      $salePrice  = $salePrice * $exchange;
    }

    $originalCostPrice = $costPrice;
    $originalSalePrice = $salePrice;

    if ($applyIva == "si") {
      $costPrice = $costPrice * 1.16;
      $salePrice = $salePrice * 1.16;
    }

    $stocks = [
      "TGZ" => $row[$branch_TGZ_position],
      "MID" => $row[$branch_MID_position],
      "VHS" => $row[$branch_VHS_position],
      "DIG" => $row[$branch_DIG_position],
      "CUN" => $row[$branch_CUN_position],
    ];

    $brandId            = mysqli_real_escape_string($mysqli, $brandId);
    $brandLineId        = mysqli_real_escape_string($mysqli, $brandLineId);
    $brandLineFamilyId  = mysqli_real_escape_string($mysqli, $brandLineFamilyId);
    $supplierId         = mysqli_real_escape_string($mysqli, $supplierId);
    $unitKeyId          = mysqli_real_escape_string($mysqli, $unitKeyId);
    $satKeyId           = mysqli_real_escape_string($mysqli, $satKeyId);
    $sku                = mysqli_real_escape_string($mysqli, $sku);
    $name               = mysqli_real_escape_string($mysqli, $name);
    $typeId             = mysqli_real_escape_string($mysqli, $typeId);
    $entryUnit          = mysqli_real_escape_string($mysqli, $entryUnit);
    $entryNumPieces     = mysqli_real_escape_string($mysqli, $entryNumPieces);
    $exitUnit           = mysqli_real_escape_string($mysqli, $exitUnit);
    $applyIva           = mysqli_real_escape_string($mysqli, $applyIva);
    $inDollar           = mysqli_real_escape_string($mysqli, $inDollar);
    $originalCostPrice  = mysqli_real_escape_string($mysqli, $originalCostPrice);
    $originalSalePrice  = mysqli_real_escape_string($mysqli, $originalSalePrice);
    $costPrice          = mysqli_real_escape_string($mysqli, $costPrice);
    $salePrice          = mysqli_real_escape_string($mysqli, $salePrice);

    $query = "INSERT INTO {$db_dti}_productos (
        id_marca,
        id_categoria,
        id_categoria_familia,
        id_proveedor,
        id_clave_unidad,
        id_clave_producto_servicio,
        codigo,
        nombre_producto,
        id_tipo,
        unidad_entrada,
        numero_piezas,
        unidad_salida,
        aplica_iva,
        en_dolares,
        precio_costo_original,
        precio_venta_original,
        precio_costo,
        precio_venta
      ) VALUES (
        '{$brandId}',
        '{$brandLineId}',
        '{$brandLineFamilyId}',
        '{$supplierId}',
        '{$unitKeyId}',
        '{$satKeyId}',
        '{$sku}',
        '{$name}',
        '{$typeId}',
        '{$entryUnit}',
        '{$entryNumPieces}',
        '{$exitUnit}',
        '{$applyIva}',
        '{$inDollar}',
        '{$originalCostPrice}',
        '{$originalSalePrice}',
        '{$costPrice}',
        '{$salePrice}'
      ) ON DUPLICATE KEY UPDATE
        nombre_producto        = '{$name}',
        id_marca               = '{$brandId}',
        id_categoria           = '{$brandLineId}',
        id_categoria_familia   = '{$brandLineFamilyId}',
        id_proveedor           = '{$supplierId}',
        id_clave_unidad        = '{$unitKeyId}',
        id_clave_producto_servicio = '{$satKeyId}',
        id_tipo                   = '{$typeId}',
        unidad_entrada         = '{$entryUnit}',
        numero_piezas          = '{$entryNumPieces}',
        unidad_salida          = '{$exitUnit}',
        aplica_iva             = '{$applyIva}',
        en_dolares             = '{$inDollar}',
        precio_costo_original  = '{$originalCostPrice}',
        precio_venta_original  = '{$originalSalePrice}',
        precio_costo           = '{$costPrice}',
        precio_venta           = '{$salePrice}'
    ";

    $result = mysqli_query($mysqli, $query);

    if ($result) {
      $productId = mysqli_insert_id($mysqli);

      // foreach ($stocks as $branchCode => $stock) {
      //   $stock = (int)$stock ?? 0;

      //   addProductOnInventory(
      //     $productId,
      //     $name,
      //     $stock,
      //     $branchCode
      //   );
      // }
    }
  }

  fclose($file);
} catch (Exception $e) {
  echo 'Error: ' . $e->getMessage();
  echo "<br>";
  echo $query;
}
