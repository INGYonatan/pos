<?php
require_once __DIR__ . "/../../inc/settings.inc.php";

/* 
CLAVE	MARCA 	LINEA 	PROVEEDOR	TIPO	PRODUCTO	ULTIMA  COMPRA	 ULTIMO COSTO 	 COSTO PROMEDIO 	PZAS	 COSTO TOTAL 	IVA	 PRECIO  	 PRECIO MAYOREO 	PZAS MAYOREO	DCTO
*/

// Incluye el simbolo de porcentaje
function removeIvaChars($ivaStr)
{
  return str_replace("%", "", $ivaStr);
}

function addIvaToPrice($price, $iva = 16)
{
  return $price * (1 + ($iva / 100));
}

// Read CSV file
$fileLocation = __DIR__ . "/products.csv";
$file         = fopen($fileLocation, "r");
$counter      = 0;
$productsStr  = [];

$types = [
  "EQUIPO" => "equipo",
  "VARIOS" => "varios"
];

while (($data = fgetcsv($file)) !== false) :
  if ($counter > 0) :
    $sku          = $data[0];
    $brand        = $data[1];
    $line         = $data[2];
    $provider     = $data[3];
    $type         = $types[$data[4]];
    $product      = $data[5];
    $last_buy     = $data[6];
    $last_cost    = parseStrCurrency($data[7]);
    $average_cost = parseStrCurrency($data[8]);
    $pieces       = $data[9];
    $total_cost   = parseStrCurrency($data[10]);
    $iva          = doubleval(removeIvaChars($data[11]));
    $price        = parseStrCurrency($data[12]);
    $mayor_price  = parseStrCurrency($data[13]);
    $mayor_pieces = $data[14];
    $discount     = $data[15];

    $brandId              = 1;
    $categoryId           = 1;
    $familyId             = 1;
    $unitId               = 1;
    $supplierId           = 1;
    $entryUnit            = "unidad";
    $exitUnit             = "unidad";
    $unitKeyId            = 1;
    $productServiceKeyId  = 1;
    $inDollars            = "no";
    $applyIva             = $iva > 0 ? "si" : "no";

    $originalCostPrice    = $last_cost;
    $costPrice            = $iva > 0 ? addIvaToPrice($last_cost) : $last_cost;

    $originalSellPrice    = $price;
    $sellPrice            = $iva > 0 ? addIvaToPrice($price) : $price;

    $originalMayoreoPrice = $mayor_price;
    $mayoreoPrice         = $iva > 0 ? addIvaToPrice($mayor_price) : $mayor_price;

    $productsToInsert = "(
        '{$sku}',
        '{$product}',
        '{$brandId}',
        '{$categoryId}',
        '{$familyId}',
        '{$type}',
        '{$supplierId}',
        '{$entryUnit}',
        '{$exitUnit}',
        '{$unitKeyId}',
        '{$productServiceKeyId}',
        '{$applyIva}',
        '{$inDollars}',
        '{$originalCostPrice}',
        '{$costPrice}',
        '{$originalSellPrice}',
        '{$sellPrice}',
        '{$originalMayoreoPrice}',
        '{$mayoreoPrice}',
        '{$mayor_pieces}'
      )
    ";

    $query = "INSERT INTO {$db_dti}_productos (
        codigo,
        nombre_producto,
        id_marca,
        id_categoria,
        id_categoria_familia,
        tipo,
        id_proveedor,
        unidad_entrada,
        unidad_salida,
        id_clave_unidad,
        id_clave_producto_servicio,
        aplica_iva,
        en_dolares,
        precio_costo_original,
        precio_costo,
        precio_venta_original,
        precio_venta,
        precio_mayoreo_original,
        precio_mayoreo,
        cantidad_mayoreo
      ) VALUES
        {$productsToInsert};
    ";

    try {
      $result = mysqli_query($mysqli, $query);

      if ($result) :
        $productId = mysqli_insert_id($mysqli);

        // Agregar productos al inventario
        addProductOnInventory(
          $productId,
          $product
        );

        // Agregar stock al inventario de almacen (1)
        $queryStock = "UPDATE {$db_dti}_inventario
          SET
            stock = {$pieces}
          WHERE
            id_sucursal = 1 AND
            id_producto = $productId
        ";

        $stockResult = mysqli_query($mysqli, $queryStock);

        if ($stockResult) addKardexLog(
          $productId,
          1,
          $pieces,
          "Stock inicial desde CSV"
        );

        echo "Productos insertados con exito <br>";
      endif;

      if (!$result):
        echo "Error al insertar productos <br>";
      endif;
    } catch (Exception $e) {
      echo "Error al insertar producto {$sku} - {$product}: {$e->getMessage()}<br>";
    }
  endif;

  $counter++;
endwhile;

fclose($file);
mysqli_close($mysqli);

//addProductOnInventory

/**
 * codigo
 * nombre_producto
 * id_marca (1)
 * id_categoria (1)
 * id_categoria_familia (1)
 * tipo (equipo/varios)
 * id_proveedor (1)
 * unidad_entrada (caja/unidad)
 * unidad_salida (caja/unidad)
 * id_clave_unidad (1)
 * id_clave_producto_servicio (1)
 * aplica_iva (si/no)
 * en_dolares (si/no)
 * precio_costo_original
 * precio_costo
 * precio_venta_original
 * precio_venta
 * precio_mayoreo_original
 * precio_mayoreo
 * cantidad_mayoreo
 */
