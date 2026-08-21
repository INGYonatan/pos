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
      "P.id_tipo",
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
      "P.precio_venta2",
      "P.precio_venta3",
      "P.cantidad_mayoreo",
      "P.precio_mayoreo",
      "P.precio_costo_original",
      "P.precio_venta_original",
      "P.precio_venta2_original",
      "P.precio_venta3_original",
      "P.precio_mayoreo_original",
      "P.aplica_iva",
      "P.aplica_ieps",
      "P.ieps_porcentaje",
      "P.en_dolares",
      "P.unidad_entrada",
      "P.unidad_salida",
      "P.numero_piezas",
      "C.categoria",
      "CF.familia",
      ["CU.nombre", "nombre_clave_unidad"],
      ["CPS.descripcion", "descripcion_clave_producto_servicio"],
      "M.marca",
      ["PR.nombre_proveedor", "proveedor"],
      "P.status",
      ["T.nombre", "tipo"],
      "P.control_inventario",
      "P.ancho",
      "P.alto",
      "P.rin",
      "P.anio_fabricacion"
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
        LEFT JOIN
          {$db_dti}_tipos AS T ON (T.id_tipo = P.id_tipo)
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
    if (!empty($type))              array_push($c_where, ["P.id_tipo", $type]);
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
        $category_family_id = cleanStr($_POST['id_categoria_familia']);
        $category_family    = category_families_get_by_id($category_family_id);

        if (!$category_family) {
          $category_family = new stdClass();
          $category_family->wholesale_quantity = "0.00";
          $category_family->wholesale_price    = "0.00";
        }

        $_POST["numero_piezas"] = $_POST["unidad_entrada"] === 'caja' ? $_POST["numero_piezas"] : "0";
        $_POST["control_inventario"] = isset($_POST["control_inventario"]) ? "si" : "no";
        $_POST["aplica_ieps"] = isset($_POST["aplica_ieps"]) ? cleanStr($_POST["aplica_ieps"]) : "no";
        $_POST["ieps_porcentaje"] = $_POST["aplica_ieps"] === "si" ? cleanStr($_POST["ieps_porcentaje"] ?? "0") : "0";

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

      $_POST["control_inventario"] = isset($_POST["control_inventario"]) ? "si" : "no";
      $_POST["aplica_ieps"] = isset($_POST["aplica_ieps"]) ? cleanStr($_POST["aplica_ieps"]) : "no";
      $_POST["ieps_porcentaje"] = $_POST["aplica_ieps"] === "si" ? cleanStr($_POST["ieps_porcentaje"] ?? "0") : "0";

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
    $applyIepsPosition        = $_POST["applyIepsPosition"] ?? "";
    $iepsPercentagePosition   = $_POST["iepsPercentagePosition"] ?? "";
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
          $applyIva        = strtolower($row[$applyIvaPosition]);
          $applyIeps       = !empty($applyIepsPosition) ? strtolower($row[$applyIepsPosition]) : "no";
          $iepsPercentage  = !empty($iepsPercentagePosition) ? $row[$iepsPercentagePosition] : 0;
          $inDollar        = strtolower($row[$inDollarPosition]);
          $costPrice       = $row[$costPricePosition];
          $salePrice       = $row[$salePricePosition];

          if ($applyIeps !== "si") {
            $applyIeps = "no";
            $iepsPercentage = 0;
          }

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

          if ($applyIeps == "si") {
            $iepsPercentage = (float)$iepsPercentage;
            if ($iepsPercentage > 0) {
              $costPrice = $costPrice * (1 + ($iepsPercentage / 100));
              $salePrice = $salePrice * (1 + ($iepsPercentage / 100));
            }
          }

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
          $applyIeps          = mysqli_real_escape_string($mysqli, $applyIeps);
          $iepsPercentage     = mysqli_real_escape_string($mysqli, $iepsPercentage);
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
              aplica_ieps,
              ieps_porcentaje,
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
              '{$applyIeps}',
              '{$iepsPercentage}',
              '{$inDollar}',
              '{$originalCostPrice}',
              '{$originalSalePrice}',
              '{$costPrice}',
              '{$salePrice}'
            ) ON DUPLICATE KEY UPDATE
              nombre_producto             = '{$name}',
              id_marca                    = '{$brandId}',
              id_categoria                = '{$brandLineId}',
              id_categoria_familia        = '{$brandLineFamilyId}',
              id_proveedor                = '{$supplierId}',
              id_clave_unidad             = '{$unitKeyId}',
              id_clave_producto_servicio  = '{$satKeyId}',
              id_tipo                     = '{$typeId}',
              unidad_entrada              = '{$entryUnit}',
              numero_piezas               = '{$entryNumPieces}',
              unidad_salida               = '{$exitUnit}',
              aplica_iva                  = '{$applyIva}',
              aplica_ieps                 = '{$applyIeps}',
              ieps_porcentaje             = '{$iepsPercentage}',
              en_dolares                  = '{$inDollar}',
              precio_costo_original       = '{$originalCostPrice}',
              precio_venta_original       = '{$originalSalePrice}',
              precio_costo                = '{$costPrice}',
              precio_venta                = '{$salePrice}'
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
  /* */
  case "importar-productos":
    //if (!checkModuleActionPermission("productos", "add")) break;

    require_once __DIR__ . "/../lib/models/sucursales.model.php";
    require_once __DIR__ . "/../lib/models/types.model.php";
    require_once __DIR__ . "/../lib/models/brands.model.php";
    require_once __DIR__ . "/../lib/models/suppliers.model.php";
    require_once __DIR__ . "/../lib/models/unit-codes.model.php";
    require_once __DIR__ . "/../lib/models/product-service-codes.model.php";
    require_once __DIR__ . "/../lib/models/productos.model.php";
    require_once __DIR__ . "/../lib/models/inventario.model.php";
    require_once __DIR__ . "/../lib/models/ajustes-inventario.model.php";
    require_once __DIR__ . "/../lib/models/ajuste-inventario-productos.model.php";
    require_once __DIR__ . "/../lib/models/ajuste-inventario-producto-numeros-serie.model.php";
    require_once __DIR__ . "/../lib/models/producto-numeros-serie.model.php";

    $productsSession        = [];

    $existingTypes          = [];
    $existingBrands         = [];
    $existingSuppliers      = [];
    $existingUnitCodes      = [];
    $existingServiceCodes   = [];

    $updatedCreatedProducts = [];
    $currentUserId          = $admp_session_user_data["id_usuario"] ?? 0;

    $pricesWithIva          = ($_POST["prices_with_iva"] ?? "") === "si";
    $pricesWithIeps         = ($_POST["prices_with_ieps"] ?? "") === "si";
    $products               = $_POST["products"] ? json_decode($_POST["products"], true) : [];

    $positionSku            = $_POST["position_sku"];
    $positionName           = $_POST["position_name"];
    $positionType           = $_POST["position_type"];
    $positionRequiresSerial = $_POST["position_requires_serial"];
    $positionBrand          = $_POST["position_brand"];
    $positionSupplier       = $_POST["position_supplier"];
    $positionUnit           = $_POST["position_unit"];
    $positionUnitCode       = $_POST["position_unit_code"];
    $positionServiceCode    = $_POST["position_service_code"];
    $positionDescription    = $_POST["position_description"];
    $positionCostPrice      = $_POST["position_cost_price"];
    $positionSalePrice      = $_POST["position_sale_price"];
    $positionSalePrice2     = $_POST["position_sale_price_2"];
    $positionSalePrice3     = $_POST["position_sale_price_3"];
    $positionApplyIva       = $_POST["position_apply_iva"];
    $positionApplyIeps      = $_POST["position_apply_ieps"];
    $positionIeps           = $_POST["position_ieps"];
    $positionUsd            = $_POST["position_usd"];
    $adjustIds              = isset($_POST["adjust_ids"]) ? json_decode($_POST["adjust_ids"], true) : [];

    // Sucursales del sistema
    $sucursalesModel    = new SucursalesModel();
    $sucursales         = $sucursalesModel->getAll()->data->rows ?? [];
    $sucursalesLength   = count($sucursales);

    if ($sucursalesLength === 0) {
      $response['toastMessage'] = 'No tienes ninguna sucursal creada, crea una sucursal para poder importar los productos.';
      break;
    }

    foreach ($products as $product) {
      $product      = str_getcsv($product);

      $sku          = cleanStr($product[$positionSku] ?? "");

      if (!$sku) continue;

      $name              = cleanStr($product[$positionName] ?? "") ?? "";
      $typeName          = cleanStr($product[$positionType] ?? "Producto") ?? "Producto";
      $requiresSerial    = intval($product[$positionRequiresSerial] ?? 0) > 0 ? 1 : 0;
      $brandName         = cleanStr($product[$positionBrand] ?? "") ?? "";
      $supplierName      = cleanStr($product[$positionSupplier] ?? "") ?? "";
      $unitName          = cleanStr($product[$positionUnit] ?? "") ?? "";
      $unitCode          = cleanStr($product[$positionUnitCode] ?? "") ?? "";
      $serviceCode       = cleanStr($product[$positionServiceCode] ?? "") ?? "";
      $description       = cleanStr($product[$positionDescription] ?? "") ?? "";
      $costPrice         = floatval(str_replace(",", "", $product[$positionCostPrice] ?? 0));
      $salePrice         = floatval(str_replace(",", "", $product[$positionSalePrice] ?? 0));
      $salePrice2        = floatval(str_replace(",", "", $product[$positionSalePrice2] ?? 0));
      $salePrice3        = floatval(str_replace(",", "", $product[$positionSalePrice3] ?? 0));
      $applyIva          = strtolower($product[$positionApplyIva] ?? "si");
      $applyIeps         = strtolower($product[$positionApplyIeps] ?? "no");
      $iepsPercentage    = floatval(str_replace(",", "", $product[$positionIeps] ?? 8));
      $inDollars         = strtolower($product[$positionUsd] ?? "no");

      // Si el precio viene en dólares, convertirlo a pesos mexicanos (MXN) con la tasa de cambio
      if ($inDollars === "si") {
        $exchange   = getTipoCambio();

        $costPrice  = $costPrice * $exchange;
        $salePrice  = $salePrice * $exchange;
        $salePrice2 = $salePrice2 * $exchange;
        $salePrice3 = $salePrice3 * $exchange;
      }

      // Los originales se guardan sin impuestos, los finales con impuestos aplicados
      $costPriceOriginal    = $costPrice;
      $salePriceOriginal    = $salePrice;
      $salePrice2Original   = $salePrice2;
      $salePrice3Original   = $salePrice3;

      if ($pricesWithIva && $applyIva === "si") {
        $costPriceOriginal    = $costPrice / 1.16;
        $salePriceOriginal    = $salePrice / 1.16;
        $salePrice2Original   = $salePrice2 > 0 ? $salePrice2 / 1.16 : 0;
        $salePrice3Original   = $salePrice3 > 0 ? $salePrice3 / 1.16 : 0;
      }

      if ($pricesWithIeps && $applyIeps === "si" && $iepsPercentage > 0) {
        $iepsFactor           = 1 + ($iepsPercentage / 100);

        $costPriceOriginal    = $costPriceOriginal / $iepsFactor;
        $salePriceOriginal    = $salePriceOriginal / $iepsFactor;
        $salePrice2Original   = $salePrice2Original > 0 ? $salePrice2Original / $iepsFactor : 0;
        $salePrice3Original   = $salePrice3Original > 0 ? $salePrice3Original / $iepsFactor : 0;
      }

      // Validar el tipo (buscar por slug, si no existe crearlo)
      $typeSlug = createSlug($typeName);

      if (!isset($existingTypes[$typeSlug])) {
        $typesModel = new TypesModel();
        $typesModel->getBySlug($typeSlug);

        if (!$typesModel->getId()) {
          $typesModel->setName($typeName);
          $typesModel->setSlug($typeSlug);
          $typesModel->setRequiresSerialNumber($requiresSerial);
          $typesModel->setTangible(1);

          $createTypeResult = $typesModel->create();

          if ($createTypeResult->status === "error") continue;
        }

        if ($typesModel->getId()) $existingTypes[$typeSlug] = $typesModel;
      }

      /**
       * @var TypesModel $rowTypeModel
       */
      $rowTypeModel = $existingTypes[$typeSlug] ?? new TypesModel();

      if (!$rowTypeModel->getId()) continue;

      // Validar la marca (buscar por nombre en mayúsculas, si no existe crearla)
      $brandId = 0;

      if ($brandName !== "") {
        $brandKey = strtoupper($brandName);

        if (!isset($existingBrands[$brandKey])) {
          $brandsModel = new BrandsModel();
          $brandsModel->getByName($brandKey);

          if (!$brandsModel->getId()) {
            $brandsModel->setName($brandKey);

            $createBrandResult = $brandsModel->create();

            if ($createBrandResult->status === "error") continue;
          }

          if ($brandsModel->getId()) $existingBrands[$brandKey] = $brandsModel;
        }

        if (isset($existingBrands[$brandKey])) $brandId = $existingBrands[$brandKey]->getId();
      }

      // Validar el proveedor (buscar por nombre en mayúsculas, si no existe crearlo)
      $supplierId = 0;

      if ($supplierName !== "") {
        $supplierKey = strtoupper($supplierName);

        if (!isset($existingSuppliers[$supplierKey])) {
          $suppliersModel = new SuppliersModel();
          $suppliersModel->getByName($supplierKey);

          if (!$suppliersModel->getId()) {
            $suppliersModel->setName($supplierKey);

            $createSupplierResult = $suppliersModel->create();

            if ($createSupplierResult->status === "error") continue;
          }

          if ($suppliersModel->getId()) $existingSuppliers[$supplierKey] = $suppliersModel;
        }

        if (isset($existingSuppliers[$supplierKey])) $supplierId = $existingSuppliers[$supplierKey]->getId();
      }

      // Validar la unidad de medida SAT (buscar por clave)
      $unitCodeId = 0;

      if ($unitCode !== "") {
        if (!isset($existingUnitCodes[$unitCode])) {
          $unitCodesModel = new UnitCodesModel();
          $unitCodesModel->getByCode($unitCode);

          if ($unitCodesModel->getId()) $existingUnitCodes[$unitCode] = $unitCodesModel;
        }

        if (isset($existingUnitCodes[$unitCode])) $unitCodeId = $existingUnitCodes[$unitCode]->getId();
      }

      // Validar la clave SAT (buscar por clave)
      $serviceCodeId = 0;

      if ($serviceCode !== "") {
        if (!isset($existingServiceCodes[$serviceCode])) {
          $productServiceCodesModel = new ProductServiceCodesModel();
          $productServiceCodesModel->getByCode($serviceCode);

          if ($productServiceCodesModel->getId()) $existingServiceCodes[$serviceCode] = $productServiceCodesModel;
        }

        if (isset($existingServiceCodes[$serviceCode])) $serviceCodeId = $existingServiceCodes[$serviceCode]->getId();
      }

      // Validar si el producto existe o no por su código, si existe actualizarlo, si no existe crearlo
      $productsModel = new ProductosModel();
      $productsModel->getByCode($sku);

      $productsModel->setTypeId($rowTypeModel->getId());
      $productsModel->setBrandId($brandId);
      $productsModel->setSupplierId($supplierId);
      $productsModel->setUnitCodeId($unitCodeId);
      $productsModel->setProductServiceCodeId($serviceCodeId);
      $productsModel->setCode($sku);
      $productsModel->setName($name);
      $productsModel->setInputUnit($unitName !== "" ? $unitName : "unidad");
      $productsModel->setOutputUnit($unitName !== "" ? $unitName : "unidad");
      $productsModel->setCostPriceOriginal($costPriceOriginal);
      $productsModel->setCostPrice($costPrice);
      $productsModel->setSalePriceOriginal($salePriceOriginal);
      $productsModel->setSalePrice($salePrice);
      $productsModel->setSalePrice2Original($salePrice2Original);
      $productsModel->setSalePrice2($salePrice2);
      $productsModel->setSalePrice3Original($salePrice3Original);
      $productsModel->setSalePrice3($salePrice3);
      $productsModel->setInDollars($inDollars === "si" ? "si" : "no");
      $productsModel->setAppliesVat($applyIva === "si" ? "si" : "no");
      $productsModel->setAppliesIeps($applyIeps === "si" ? "si" : "no");
      $productsModel->setIepsPercentage($iepsPercentage > 0 ? $iepsPercentage : 8);
      $productsModel->setStatus("activo");

      if ($productsModel->getId()) $productsModel->update();
      else $productsModel->create();

      if (!$productsModel->getId()) continue;

      // Agregar el producto en la lista de productos agregados o actualizados
      $updatedCreatedProducts[$productsModel->getCode()] = [
        "product"        => $productsModel,
        "csvRow"         => $product,
        "typeName"       => $typeName,
        "requiresSerial" => $rowTypeModel->getRequiresSerialNumber()
      ];
    }

    $updatedCreatedProductsLength = count($updatedCreatedProducts);

    foreach ($sucursales as $sucursal) {
      /**
       * @var SucursalesModel $sucursal
       */
      $inventoryAdjustmentsModel  = new AjustesInventarioModel();
      $md5SucursalId              = md5($sucursal->getId());
      $stockPosition              = $_POST["position_stock_{$md5SucursalId}"];
      $serialNumberPosition       = $_POST["position_serial_number_{$md5SucursalId}"] ?? "";
      $adjustId                   = $adjustIds["adjust_{$md5SucursalId}"] ?? null;

      // Filtrar solo los productos que si obtuvieron un ajuste de stock
      $productsForAdjustment = [];

      foreach ($updatedCreatedProducts as $key => $data) {
        // Definir si es entrada o salida mediante el stock actual del producto
        /**
         * @var ProductosModel $productsModel
         */
        $productsModel  = $data["product"];
        $csvRow         = $data["csvRow"];
        $serialNumbers  = cleanStr($csvRow[$serialNumberPosition] ?? "") ?? "";
        $typeName       = $data["typeName"];
        $requiresSerial = $data["requiresSerial"];

        $inventoryModel = new InventarioModel();

        $inventoryModel->getBySucursalIdAndProductId($sucursal->getId(), $productsModel->getId());

        $currentStock   = $inventoryModel->getStock();
        $newStock       = intval($csvRow[$stockPosition] ?? 0);

        if ($currentStock == $newStock) continue;

        $movementType   = $currentStock < $newStock ? "entrada" : "salida";
        $quantity       = $movementType == "entrada" ? ($newStock - $currentStock) : ($currentStock - $newStock);

        $productsForAdjustment[] = [
          "id"            => $productsModel->getId(),
          "sku"           => $productsModel->getCode(),
          "name"          => $productsModel->getName(),
          "movementType"  => $movementType,
          "quantity"      => $quantity,
          "previousStock" => $currentStock,
          "newStock"      => $newStock,
          "typeId"         => $productsModel->getTypeId(),
          "typeName"       => $typeName,
          "serialNumbers"  => $serialNumbers,
          "requiresSerial" => $requiresSerial
        ];
      }

      $productsForAdjustmentLength = count($productsForAdjustment);

      if ($productsForAdjustmentLength == 0) continue;

      if (!$adjustId) {
        $inventoryAdjustmentsModel->setUserId($currentUserId);
        $inventoryAdjustmentsModel->setSucursalId($sucursal->getId());
        $inventoryAdjustmentsModel->setFolio("AJ" . date("ymdHis") . strtoupper(substr($productsForAdjustment[0]["movementType"], 0, 1)));
        $inventoryAdjustmentsModel->setObservations("Ajuste masivo de productos");
        $inventoryAdjustmentsModel->setStatus("activo");
        $inventoryAdjustmentsModel->setType($productsForAdjustment[0]["movementType"] == "entrada" ? "incremento" : "decremento");
        $inventoryAdjustmentsModel->setAdjustmentReason("Ajuste masivo de productos");
        $inventoryAdjustmentsModel->setAdjustmentType("ajuste");
        $inventoryAdjustmentsModel->create();
      }

      if ($adjustId) $inventoryAdjustmentsModel->getById($adjustId);

      $adjustId = $inventoryAdjustmentsModel->getId();
      $adjustIds["adjust_{$md5SucursalId}"] = $adjustId;

      // if ($currentStock > $newStock) $adjustType = "conteo_fisico";

      if ($inventoryAdjustmentsModel->getId()) {
        foreach ($productsForAdjustment as $data) {
          $ajusteProductoModel = new AjusteInventarioProductosModel();
          $ajusteProductoModel->setAdjustmentId($adjustId);
          $ajusteProductoModel->setProductId($data["id"]);
          $ajusteProductoModel->setTypeId($data["typeId"]);
          $ajusteProductoModel->setType($data["typeName"]);
          $ajusteProductoModel->setQuantity($data["quantity"]);
          $ajusteProductoModel->create();

          // Actualizar el stock real del producto en el inventario para que
          // re-importar el mismo archivo no vuelva a generar un ajuste
          $ajusteInventoryModel = new InventarioModel();
          $ajusteInventoryModel->getBySucursalIdAndProductId($sucursal->getId(), $data["id"]);

          $ajusteInventoryModel->setSucursalId($sucursal->getId());
          $ajusteInventoryModel->setProductId($data["id"]);
          $ajusteInventoryModel->setStock($data["newStock"]);

          if ($ajusteInventoryModel->getId()) $ajusteInventoryModel->update();
          else $ajusteInventoryModel->create();

          // Registrar en el kardex el movimiento del ajuste
          $kardexLog = ACCION_INVENTARIO_AUMENTAR_STOCK . " en {$sucursal->getName()}";

          if ($data["movementType"] == "salida") {
            $kardexLog = ACCION_INVENTARIO_REDUCIR_STOCK . " en {$sucursal->getName()}";
          }

          addKardexLog(
            $data["id"],
            $sucursal->getId(),
            $data["quantity"],
            $kardexLog
          );

          // Números de serie (pueden venir varios separados por comas)
          $serialList = array_filter(array_map("trim", explode(",", $data["serialNumbers"])));

          // Solo se gestionan seriales cuando el tipo del producto lo requiere
          if ($ajusteProductoModel->getId() && $data["requiresSerial"]) {
            $productoNumerosSerieModel = new ProductoNumerosSerieModel();

            // Seriales actuales del producto en la sucursal
            $serialesExistentes = [];
            $getSerialesResult = $productoNumerosSerieModel->getAllByProductIdAndSucursalId($data["id"], $sucursal->getId());

            foreach ($getSerialesResult->data->rows as $serialModel) {
              $serialesExistentes[$serialModel->getSerialNumber()] = $serialModel;
            }

            if ($data["movementType"] == "entrada") {
              // Serials únicos en todo el sistema: solo se crean y registran
              // los que no existan en ninguna sucursal
              foreach ($serialList as $serial) {
                $serialCheck      = new ProductoNumerosSerieModel();
                $serialCheckResult = $serialCheck->read(["serialNumber" => $serial, "perPage" => 1]);

                if (($serialCheckResult->data->total ?? 0) === 0) {
                  $nuevoSerialModel = new ProductoNumerosSerieModel();
                  $nuevoSerialModel->setProductId($data["id"]);
                  $nuevoSerialModel->setSerialNumber($serial);
                  $nuevoSerialModel->setStatus("disponible");
                  $nuevoSerialModel->setSucursalId($sucursal->getId());
                  $nuevoSerialModel->create();

                  $numeroSerieModel = new AjusteInventarioProductoNumerosSerieModel();
                  $numeroSerieModel->setAdjustmentProductId($ajusteProductoModel->getId());
                  $numeroSerieModel->setAdjustmentId($adjustId);
                  $numeroSerieModel->setSerialNumber($serial);
                  $numeroSerieModel->setCancelled("no");
                  $numeroSerieModel->create();
                }
              }
            } else {
              // Dar de baja los seriales existentes que no vengan en el CSV
              foreach ($serialesExistentes as $serial => $serialModel) {
                if (!in_array($serial, $serialList)) {
                  $serialModel->delete();

                  $numeroSerieModel = new AjusteInventarioProductoNumerosSerieModel();
                  $numeroSerieModel->setAdjustmentProductId($ajusteProductoModel->getId());
                  $numeroSerieModel->setAdjustmentId($adjustId);
                  $numeroSerieModel->setSerialNumber($serial);
                  $numeroSerieModel->setCancelled("no");
                  $numeroSerieModel->create();
                }
              }
            }
          }
        }
      }
    }

    $response = [
      "status"  => "success",
      "data"    => [
        "adjustIds" => $adjustIds
      ]
    ];
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
