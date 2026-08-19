<?php
/* 
"P.id_producto",
      ["P.id_producto", "uid"],
      "P.id_categoria",
      "P.id_categoria_familia",
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
      ["CPS.descripcion", "descripcion_clave_producto_servicio"]
*/

/* 
$c_join = "
        LEFT JOIN
          {$db_dti}_clave_unidades AS CU ON (CU.id_clave_unidad = P.id_clave_unidad)
        LEFT JOIN
          {$db_dti}_clave_producto_servicios AS CPS ON (CPS.id_clave_producto_servicio = P.id_clave_producto_servicio)
        LEFT JOIN
          {$db_dti}_categorias AS C ON (C.id_categoria = P.id_categoria)
        LEFT JOIN
          {$db_dti}_categoria_familias AS CF ON (CF.id_categoria_familia = P.id_categoria_familia)
    ";

    $c_from           = "{$db_dti}_productos AS P";
*/
class ProductHelper
{
  private $id;
  private $categoryId;
  private $familyId;
  private $unitId;
  private $productServiceId;
  private $typeId;
  private $code;
  private $name;
  private $unit;
  private $content;
  private $costPrice;
  private $salePrice;
  private $salePrice2;
  private $salePrice3;
  private $wholesaleAmount;
  private $wholesalePrice;
  private $originalCostPrice;
  private $originalSalePrice;
  private $originalSalePrice2;
  private $originalSalePrice3;
  private $originalWholesalePrice;
  private $applyIva;
  private $applyIeps;
  private $iepsPercentage;
  private $inDollars;
  private $inputUnit;
  private $outputUnit;
  private $type;
  private $pieces;
  private $category;
  private $family;
  private $unitName;
  private $productServiceDescription;

  private $globalStock;

  public function __construct()
  {
    $this->id                         = 0;
    $this->categoryId                 = 0;
    $this->familyId                   = 0;
    $this->unitId                     = 0;
    $this->productServiceId           = 0;
    $this->typeId                     = 0;
    $this->code                       = "";
    $this->name                       = "";
    $this->unit                       = "";
    $this->content                    = "";
    $this->costPrice                  = 0;
    $this->salePrice                  = 0;
    $this->salePrice2                 = 0;
    $this->salePrice3                 = 0;
    $this->wholesaleAmount            = 0;
    $this->wholesalePrice             = 0;
    $this->originalCostPrice          = 0;
    $this->originalSalePrice          = 0;
    $this->originalSalePrice2         = 0;
    $this->originalSalePrice3         = 0;
    $this->originalWholesalePrice     = 0;
    $this->applyIva                   = 0;
    $this->applyIeps                  = 'no';
    $this->iepsPercentage             = 0;
    $this->inDollars                  = 0;
    $this->inputUnit                  = "";
    $this->outputUnit                 = "";
    $this->type                       = "";
    $this->pieces                     = 0;
    $this->category                   = "";
    $this->family                     = "";
    $this->unitName                   = "";
    $this->productServiceDescription  = "";

    $this->globalStock                = 0;
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getCategoryId()
  {
    return $this->categoryId;
  }

  public function getFamilyId()
  {
    return $this->familyId;
  }

  public function getUnitId()
  {
    return $this->unitId;
  }

  public function getProductServiceId()
  {
    return $this->productServiceId;
  }

  public function getTypeId()
  {
    return $this->typeId;
  }

  public function getCode()
  {
    return $this->code;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getUnit()
  {
    return $this->unit;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function getCostPrice()
  {
    return $this->costPrice;
  }

  public function getSalePrice()
  {
    return $this->salePrice;
  }

  public function getSalePrice2()
  {
    return $this->salePrice2;
  }

  public function getSalePrice3()
  {
    return $this->salePrice3;
  }

  public function getWholesaleAmount()
  {
    return $this->wholesaleAmount;
  }

  public function getWholesalePrice()
  {
    return $this->wholesalePrice;
  }

  public function getOriginalCostPrice()
  {
    return $this->originalCostPrice;
  }

  public function getOriginalSalePrice()
  {
    return $this->originalSalePrice;
  }

  public function getOriginalWholesalePrice()
  {
    return $this->originalWholesalePrice;
  }

  public function getApplyIva()
  {
    return $this->applyIva;
  }

  public function getApplyIeps()
  {
    return $this->applyIeps;
  }

  public function getIepsPercentage()
  {
    return $this->iepsPercentage;
  }

  public function getInDollars()
  {
    return $this->inDollars;
  }

  public function getInputUnit()
  {
    return $this->inputUnit;
  }

  public function getOutputUnit()
  {
    return $this->outputUnit;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getPieces()
  {
    return $this->pieces;
  }

  public function getCategory()
  {
    return $this->category;
  }

  public function getFamily()
  {
    return $this->family;
  }

  public function getUnitName()
  {
    return $this->unitName;
  }

  public function getProductServiceDescription()
  {
    return $this->productServiceDescription;
  }

  public function getGlobalStock()
  {
    return $this->globalStock;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setCategoryId($categoryId)
  {
    $this->categoryId = $categoryId;
  }

  public function setFamilyId($familyId)
  {
    $this->familyId = $familyId;
  }

  public function setUnitId($unitId)
  {
    $this->unitId = $unitId;
  }

  public function setProductServiceId($productServiceId)
  {
    $this->productServiceId = $productServiceId;
  }

  public function setTypeId($typeId)
  {
    $this->typeId = $typeId;
  }

  public function setCode($code)
  {
    $this->code = $code;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setUnit($unit)
  {
    $this->unit = $unit;
  }

  public function setContent($content)
  {
    $this->content = $content;
  }

  public function setCostPrice($costPrice)
  {
    $this->costPrice = $costPrice;
  }

  public function setSalePrice($salePrice)
  {
    $this->salePrice = $salePrice;
  }

  public function setSalePrice2($salePrice2)
  {
    $this->salePrice2 = $salePrice2;
  }

  public function setSalePrice3($salePrice3)
  {
    $this->salePrice3 = $salePrice3;
  }

  public function setWholesaleAmount($wholesaleAmount)
  {
    $this->wholesaleAmount = $wholesaleAmount;
  }

  public function setWholesalePrice($wholesalePrice)
  {
    $this->wholesalePrice = $wholesalePrice;
  }

  public function setOriginalCostPrice($originalCostPrice)
  {
    $this->originalCostPrice = $originalCostPrice;
  }

  public function setOriginalSalePrice($originalSalePrice)
  {
    $this->originalSalePrice = $originalSalePrice;
  }

  public function setOriginalWholesalePrice($originalWholesalePrice)
  {
    $this->originalWholesalePrice = $originalWholesalePrice;
  }

  public function setApplyIva($applyIva)
  {
    $this->applyIva = $applyIva;
  }

  public function setApplyIeps($applyIeps)
  {
    $this->applyIeps = $applyIeps;
  }

  public function setIepsPercentage($iepsPercentage)
  {
    $this->iepsPercentage = $iepsPercentage;
  }

  public function setInDollars($inDollars)
  {
    $this->inDollars = $inDollars;
  }

  public function setInputUnit($inputUnit)
  {
    $this->inputUnit = $inputUnit;
  }

  public function setOutputUnit($outputUnit)
  {
    $this->outputUnit = $outputUnit;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  public function setPieces($pieces)
  {
    $this->pieces = $pieces;
  }

  public function setCategory($category)
  {
    $this->category = $category;
  }

  public function setFamily($family)
  {
    $this->family = $family;
  }

  public function setUnitName($unitName)
  {
    $this->unitName = $unitName;
  }

  public function setProductServiceDescription($productServiceDescription)
  {
    $this->productServiceDescription = $productServiceDescription;
  }

  public function setGlobalStock($globalStock)
  {
    $this->globalStock = $globalStock;
  }

  /**
   * Methods
   */
  public function from($data)
  {
    $this->id                         = $data['id_producto'];
    $this->categoryId                 = $data['id_categoria'];
    $this->familyId                   = $data['id_categoria_familia'];
    $this->unitId                     = $data['id_clave_unidad'];
    $this->productServiceId           = $data['id_clave_producto_servicio'];
    $this->typeId                     = $data['id_tipo'];
    $this->code                       = $data['codigo'];
    $this->name                       = $data['nombre_producto'];
    $this->unit                       = $data['unidad'];
    $this->content                    = $data['contenido'];
    $this->costPrice                  = $data['precio_costo'];
    $this->salePrice                  = $data['precio_venta'];
    $this->salePrice2                 = $data['precio_venta2'] ?? 0;
    $this->salePrice3                 = $data['precio_venta3'] ?? 0;
    $this->wholesaleAmount            = $data['cantidad_mayoreo'];
    $this->wholesalePrice             = $data['precio_mayoreo'];
    $this->originalCostPrice          = $data['precio_costo_original'];
    $this->originalSalePrice          = $data['precio_venta_original'];
    $this->originalSalePrice2         = $data['precio_venta2_original'] ?? 0;
    $this->originalSalePrice3         = $data['precio_venta3_original'] ?? 0;
    $this->originalWholesalePrice     = $data['precio_mayoreo_original'];
    $this->applyIva                   = $data['aplica_iva'];
    $this->applyIeps                  = $data['aplica_ieps'] ?? 'no';
    $this->iepsPercentage             = $data['ieps_porcentaje'] ?? 0;
    $this->inDollars                  = $data['en_dolares'];
    $this->inputUnit                  = $data['unidad_entrada'];
    $this->outputUnit                 = $data['unidad_salida'];
    $this->type                       = $data['tipo'];
    $this->pieces                     = $data['numero_piezas'];
    $this->category                   = $data['categoria'];
    $this->family                     = $data['familia'];
    $this->unitName                   = $data['nombre_clave_unidad'];
    $this->productServiceDescription  = $data['descripcion_clave_producto_servicio'];

    $this->globalStock                = $data['globalStock'] ?? 0;
  }

  public function get($id)
  {
    global $mysqli;
    global $db_dti;

    try {
      $query = "SELECT
          P.id_producto,
          P.id_categoria,
          P.id_categoria_familia,
          P.id_clave_unidad,
          P.id_clave_producto_servicio,
          P.codigo,
          P.nombre_producto,
          P.unidad,
          P.contenido,
          P.precio_costo,
          P.precio_venta,
          P.precio_venta2,
          P.precio_venta3,
          P.cantidad_mayoreo,
          P.precio_mayoreo,
          P.precio_costo_original,
          P.precio_venta_original,
          P.precio_venta2_original,
          P.precio_venta3_original,
          P.precio_mayoreo_original,
          P.aplica_iva,
          P.aplica_ieps,
          P.ieps_porcentaje,
          P.en_dolares,
          P.unidad_entrada,
          P.unidad_salida,
          P.tipo,
          P.numero_piezas,
          C.categoria,
          CF.familia,
          CU.nombre AS nombre_clave_unidad,
          CPS.descripcion AS descripcion_clave_producto_servicio
        FROM
          {$db_dti}_productos AS P
        LEFT JOIN
          {$db_dti}_clave_unidades AS CU ON (CU.id_clave_unidad = P.id_clave_unidad)
        LEFT JOIN
          {$db_dti}_clave_producto_servicios AS CPS ON (CPS.id_clave_producto_servicio = P.id_clave_producto_servicio)
        LEFT JOIN
          {$db_dti}_categorias AS C ON (C.id_categoria = P.id_categoria)
        LEFT JOIN
          {$db_dti}_categoria_familias AS CF ON (CF.id_categoria_familia = P.id_categoria_familia)
        WHERE
          P.id_producto = ?
      ";

      $stmt = $mysqli->prepare($query);
      $stmt->bind_param('i', $id);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows > 0) :
        $data = $result->fetch_assoc();
        $this->from($data);
      endif;
    } catch (Exception $e) {
      //throw $th;
    }
  }

  /**
   * Obtiene un array de objetos ProductHelper para Select2.
   *
   * @param string $term El término de búsqueda.
   * @param string $value Un valor opcional adicional.
   * @return ProductHelper[] Un array de objetos ProductHelper.
   */
  public function getForSelect2($term, $value = ""): array
  {
    global $mysqli;
    global $db_dti;

    $catalog = [];

    $query = "SELECT
        P.id_producto,
        P.id_categoria,
        P.id_categoria_familia,
        P.id_clave_unidad,
        P.id_clave_producto_servicio,
        P.codigo,
        P.nombre_producto,
        P.unidad,
        P.contenido,
        P.precio_costo,
        P.precio_venta,
        P.precio_venta2,
        P.precio_venta3,
        P.cantidad_mayoreo,
        P.precio_mayoreo,
        P.precio_costo_original,
        P.precio_venta_original,
        P.precio_venta2_original,
        P.precio_venta3_original,
        P.precio_mayoreo_original,
        P.aplica_iva,
        P.aplica_ieps,
        P.ieps_porcentaje,
        P.en_dolares,
        P.unidad_entrada,
        P.unidad_salida,
        P.tipo,
        P.numero_piezas,
        C.categoria,
        CF.familia,
        CU.nombre AS nombre_clave_unidad,
        CPS.descripcion AS descripcion_clave_producto_servicio
      FROM
        {$db_dti}_productos AS P
      LEFT JOIN
        {$db_dti}_clave_unidades AS CU ON (CU.id_clave_unidad = P.id_clave_unidad)
      LEFT JOIN
        {$db_dti}_clave_producto_servicios AS CPS ON (CPS.id_clave_producto_servicio = P.id_clave_producto_servicio)
      LEFT JOIN
        {$db_dti}_categorias AS C ON (C.id_categoria = P.id_categoria)
      LEFT JOIN
        {$db_dti}_categoria_familias AS CF ON (CF.id_categoria_familia = P.id_categoria_familia)
      WHERE
        (P.nombre_producto LIKE _utf8 '%{$term}%' collate utf8_unicode_ci) OR
        (P.codigo LIKE _utf8 '%{$term}%' collate utf8_unicode_ci)
      ORDER BY
        P.nombre_producto
      ASC
      LIMIT 25
    ";

    $result   = mysqli_query($mysqli, $query);
    $numRows  = mysqli_num_rows($result);

    if ($numRows > 0) :
      while ($data = mysqli_fetch_assoc($result)) :
        $item = new ProductHelper();
        $item->from($data);

        array_push($catalog, $item);
      endwhile;
    endif;

    return $catalog;
  }

  public function getAllWithBranchStocksSum($params = [])
  {
    global $mysqli;
    global $db_dti;

    $typeId = $params['typeId'] ?? null;

    $byTypeId = $typeId ? "P.id_tipo = '{$typeId}'" : "1=1";

    $query = "SELECT
        P.*,
        SUM(I.stock) AS globalStock
      FROM
        {$db_dti}_productos AS P
      LEFT JOIN
        {$db_dti}_inventario AS I ON (I.id_producto = P.id_producto)
      WHERE
        ({$byTypeId})
      GROUP BY
        P.id_producto
    ";

    $result   = mysqli_query($mysqli, $query);
    $numRows  = mysqli_num_rows($result);

    $products = [];

    if ($numRows > 0) :
      while ($data = mysqli_fetch_assoc($result)) :
        $item = new ProductHelper();
        $item->from($data);

        array_push($products, $item);
      endwhile;
    endif;

    return $products;
  }

  public function getProductTypeAdvance()
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        *
      FROM
        {$db_dti}_productos AS P
      INNER JOIN
        {$db_dti}_tipos AS T ON (T.id_tipo = P.id_tipo)
      WHERE
        T.es_anticipo = 1
      LIMIT 1
    ";

    try {
      $result   = mysqli_query($mysqli, $query);
      $numRows  = mysqli_num_rows($result);

      if ($numRows > 0) {
        $data = mysqli_fetch_assoc($result);
        $this->from($data);
      }
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }

  public function getProductTypeCreditNote()
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        *
      FROM
        {$db_dti}_productos AS P
      INNER JOIN
        {$db_dti}_tipos AS T ON (T.id_tipo = P.id_tipo)
      WHERE
        T.es_nota_credito = 1
      LIMIT 1
    ";

    try {
      $result   = mysqli_query($mysqli, $query);
      $numRows  = mysqli_num_rows($result);

      if ($numRows > 0) {
        $data = mysqli_fetch_assoc($result);
        $this->from($data);
      }
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }
}
