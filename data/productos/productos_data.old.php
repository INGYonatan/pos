<?php
require '../lib/settings.inc.php';
require '../lib/helpers/category-families.helper.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action           = $_POST['action'];
$identifier       = 'productos';

$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');
$carpeta_imagenes       = '../../../src/assets/images/blogs/';

$LIMIT_PRODUCTS_CSV     = 250;

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $brandId          = cleanStr($_POST['brandId']);
    $categoryId       = cleanStr($_POST['categoryId']);
    $categoryFamilyId = cleanStr($_POST['categoryFamilyId']);
    $supplierId       = cleanStr($_POST['supplierId']);
    $type             = cleanStr($_POST['type']);
    $status           = cleanStr($_POST['status']);

    $column_id        = "P.id_producto";
    $c_from           = "{$db_dti}_productos AS P";
    $c_extra_clauses  = "ORDER BY P.id_producto DESC";

    $fields = [
      "P.id_producto",
      ["P.id_producto", "uid"],
      "P.id_marca",
      "P.id_categoria",
      "P.id_categoria_familia",
      "P.id_proveedor",
      "P.id_clave_unidad",
      "P.id_clave_producto_servicio",
      "P.codigo",
      "P.nombre_producto",
      "P.unidad",
      "P.contenido",
      "P.precio_costo",
      "P.precio_venta",
      "P.cantidad_mayoreo",
      "P.precio_mayoreo",
      "P.precio_costo_original",
      "P.precio_venta_original",
      "P.precio_mayoreo_original",
      "P.aplica_iva",
      "P.en_dolares",
      "P.unidad_entrada",
      "P.unidad_salida",
      "P.tipo",
      "P.numero_piezas",
      "C.categoria",
      "CF.familia",
      ["CU.nombre", "nombre_clave_unidad"],
      ["CPS.descripcion", "descripcion_clave_producto_servicio"],
      "M.marca",
      ["PR.nombre_proveedor", "proveedor"],
      "P.status"
    ];

    $c_join = "
        LEFT JOIN
          {$db_dti}_clave_unidades AS CU ON (CU.id_clave_unidad = P.id_clave_unidad)
        LEFT JOIN
          {$db_dti}_clave_producto_servicios AS CPS ON (CPS.id_clave_producto_servicio = P.id_clave_producto_servicio)
        LEFT JOIN
          {$db_dti}_categorias AS C ON (C.id_categoria = P.id_categoria)
        LEFT JOIN
          {$db_dti}_categoria_familias AS CF ON (CF.id_categoria_familia = P.id_categoria_familia)
        LEFT JOIN
          {$db_dti}_marcas AS M ON (M.id_marca = P.id_marca)
        LEFT JOIN
          {$db_dti}_proveedores AS PR ON (PR.id_proveedor = P.id_proveedor)
    ";

    $c_where = [];

    if (!empty($search)) array_push($c_where, [
      [
        ["P.nombre_producto",  "%$search%", "LIKE", "OR"],
        ["P.codigo",  "%$search%", "LIKE", "OR"]
      ]
    ]);

    if (!empty($brandId))           array_push($c_where, ["P.id_marca", $brandId]);
    if (!empty($categoryId))        array_push($c_where, ["P.id_categoria", $categoryId]);
    if (!empty($categoryFamilyId))  array_push($c_where, ["P.id_categoria_familia", $categoryFamilyId]);
    if (!empty($supplierId))        array_push($c_where, ["P.id_proveedor", $supplierId]);
    if (!empty($type))              array_push($c_where, ["P.tipo", $type]);
    if (!empty($status))            array_push($c_where, ["P.status", $status]);

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

    //echo getEmptyTableMessage($request);
    //die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';
    die;
    break;

  case 'add-' . $identifier:
    if (checkModuleActionPermission($identifier, 'agregar')) :
      try {
        //$category_family_id = cleanStr($_POST['id_categoria_familia']);
        //$category_family    = category_families_get_by_id($category_family_id);

        $category_family = new stdClass();
        $category_family->wholesale_quantity = "0.00";
        $category_family->wholesale_price    = "0.00";

        $_POST["numero_piezas"] = $_POST["unidad_entrada"] === 'caja' ? $_POST["numero_piezas"] : "0";

        if ($category_family) :
          $request = useInsertByPost([
            'table_name'      => "{$db_dti}_productos",
            "excluded_fields" => ['nombre_clave_unidad', 'descripcion_clave_producto_servicio'],
            'extra_fields'    => [
              'cantidad_mayoreo'        => $category_family->wholesale_quantity,
              'precio_mayoreo'          => $category_family->wholesale_price,
              'precio_mayoreo_original' => $category_family->wholesale_price,
            ]
          ]);

          if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

          if ($request['status'] === 'success') :
            addProductOnInventory(
              $request['id'],
              cleanStr($_POST['nombre_producto'])
            );

            $response = [
              'status'        => 'success',
              'toastMessage'  => 'El producto se agregó correctamente',
              'callback'      => 'load("' . $page . '", "' . $identifier . '");'
            ];
          endif;
        endif;
      } catch (Exception $e) {
        $response['toastMessage'] = $e->getMessage();
      }
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_producto = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name' => "{$db_dti}_productos",
        "excluded_fields" => ['nombre_clave_unidad', 'descripcion_clave_producto_servicio'],
        'conditions' => [['id_producto', $id_producto]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'toastMessage'  => 'El producto se actualizó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_producto = cleanStr($_POST['uid']);

      /* $query = "DELETE FROM {$db_dti}_productos WHERE
          id_producto = $id_producto
        "; */

      $query = "UPDATE {$db_dti}_productos SET
          status = 'eliminado'
        WHERE
          id_producto = {$id_producto}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El producto se eliminó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-activar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'activar')) :
      $id_producto = cleanStr($_POST['uid']);

      $query = "UPDATE {$db_dti}_productos SET
          status = 'activo'
        WHERE
          id_producto = {$id_producto}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El producto se activó correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case "modal-action-{$identifier}-upload-csv":
    $skuPosition              = $_POST["skuPosition"];
    $namePosition             = $_POST["namePosition"];
    $typePosition             = $_POST["typePosition"];
    $brandPosition            = $_POST["brandPosition"];
    $brandLinePosition        = $_POST["brandLinePosition"];
    $brandLineFamilyPosition  = $_POST["brandLineFamilyPosition"];
    $supplierPosition         = $_POST["supplierPosition"];
    $entryUnitPosition        = $_POST["entryUnitPosition"];
    $entryNumPiecesPosition   = $_POST["entryNumPiecesPosition"];
    $exitUnitPosition         = $_POST["exitUnitPosition"];
    $unitKeyPosition          = $_POST["unitKeyPosition"];
    $satKeyPosition           = $_POST["satKeyPosition"];
    $applyIvaPosition         = $_POST["applyIvaPosition"];
    $inDollarPosition         = $_POST["inDollarsPosition"];
    $costPricePosition        = $_POST["costPricePosition"];
    $salePricePosition        = $_POST["salePricePosition"];

    $scvFile                  = $_FILES['csvFile'];
    $csvTmpName               = $scvFile['tmp_name'];

    /**
     * Start - Read CSV file
     */ {
      $csvFileHandler = fopen($csvTmpName, "r");

      if (!$csvFileHandler) break;

      $productsToAdd  = [];
      $counter        = 0;

      while (($row = fgetcsv($csvFileHandler)) !== false) {
        if ($counter === 0) {
          $counter++;
          continue;
        }

        $sku = $row[$skuPosition];

        $productData = getProductBySku($sku);

        if (!$productData) {
          $name            = $row[$namePosition];
          $type            = $row[$typePosition];
          $brand           = $row[$brandPosition];
          $brandLine       = $row[$brandLinePosition];
          $brandLineFamily = $row[$brandLineFamilyPosition];
          $supplier        = $row[$supplierPosition];
          $entryUnit       = $row[$entryUnitPosition];
          $entryNumPieces  = $row[$entryNumPiecesPosition];
          $exitUnit        = $row[$exitUnitPosition];
          $unitKey         = $row[$unitKeyPosition];
          $satKey          = $row[$satKeyPosition];
          $applyIva        = $row[$applyIvaPosition];
          $inDollar        = $row[$inDollarPosition];
          $costPrice       = $row[$costPricePosition];
          $salePrice       = $row[$salePricePosition];

          // Validar Marca
          $brandId = getBrandByName($brand);
          if (!$brand) continue;

          // Validar Línea de Marca
          $brandLineId = getBrandLineByName($brandId, $brandLine);
          if (!$brandLineId) continue;

          // Validar Familia de Línea de Marca
          $brandLineFamilyId  = getBrandLineFamilyByName($brandLineId, $brandLineFamily);
          if (!$brandLineFamilyId) continue;

          // Validar Proveedor
          $supplierId = getSupplierByName($supplier);
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

          $query = "INSERT INTO {$db_dti}_productos (
              id_marca,
              id_categoria,
              id_categoria_familia,
              id_proveedor,
              id_clave_unidad,
              id_clave_producto_servicio,
              codigo,
              nombre_producto,
              tipo,
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
              '{$type}',
              '{$entryUnit}',
              '{$entryNumPieces}',
              '{$exitUnit}',
              '{$applyIva}',
              '{$inDollar}',
              '{$originalCostPrice}',
              '{$originalSalePrice}',
              '{$costPrice}',
              '{$salePrice}'
            )
          ";

          $result = mysqli_query($mysqli, $query);

          if ($result) {
            $productId = mysqli_insert_id($mysqli);

            addProductOnInventory(
              $productId,
              $name
            );
          }
        }

        $counter++;
      }

      fclose($csvFileHandler);
    }

    $response = [
      "status"        => "success",
      "toastMessage"  => "Los productos se han importado correctamente",
      "callback"      => "load('{$page}', '{$identifier}');"
    ];
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
