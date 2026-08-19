<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_productos (
    id_producto,
    id_tipo,
    id_marca,
    id_categoria,
    id_categoria_familia,
    id_proveedor,
    id_clave_unidad,
    id_clave_producto_servicio,
    codigo,
    nombre_producto,
    unidad,
    contenido,
    precio_costo_original,
    precio_costo,
    precio_venta_original,
    precio_venta,
    precio_venta2_original,
    precio_venta2,
    precio_venta3_original,
    precio_venta3,
    cantidad_mayoreo,
    precio_mayoreo_original,
    en_dolares,
    tipo,
    aplica_iva,
    aplica_ieps,
    ieps_porcentaje,
    fecha_creacion,
    status,
    precio_mayoreo,
    unidad_entrada,
    unidad_salida,
    numero_piezas,
    control_inventario,
  );
*/

class ProductosModel
{
  private $table = "paal_productos";

  private int $id;
  private int $typeId;
  private int $brandId;
  private int $categoryId;
  private int $categoryFamilyId;
  private int $supplierId;
  private int $unitCodeId;
  private int $productServiceCodeId;
  private string $code;
  private string $name;
  private string $unit;
  private float $content;
  private float $costPriceOriginal;
  private float $costPrice;
  private float $salePriceOriginal;
  private float $salePrice;
  private float $salePrice2Original;
  private float $salePrice2;
  private float $salePrice3Original;
  private float $salePrice3;
  private float $wholesaleQuantity;
  private float $wholesalePriceOriginal;
  private string $inDollars;
  private string $type;
  private string $appliesVat;
  private string $appliesIeps;
  private float $iepsPercentage;
  private string $createdAt;
  private string $status;
  private float $wholesalePrice;
  private string $inputUnit;
  private string $outputUnit;
  private int $piecesNumber;
  private string $inventoryControl;

  public function __construct()
  {
    $this->id                        = 0;
    $this->typeId                    = 1;
    $this->brandId                   = 0;
    $this->categoryId                = 0;
    $this->categoryFamilyId          = 0;
    $this->supplierId                = 0;
    $this->unitCodeId                = 0;
    $this->productServiceCodeId      = 0;
    $this->code                      = "";
    $this->name                      = "";
    $this->unit                      = "Pieza";
    $this->content                   = 0;
    $this->costPriceOriginal         = 0;
    $this->costPrice                 = 0;
    $this->salePriceOriginal         = 0;
    $this->salePrice                 = 0;
    $this->salePrice2Original        = 0;
    $this->salePrice2                = 0;
    $this->salePrice3Original        = 0;
    $this->salePrice3                = 0;
    $this->wholesaleQuantity         = 0;
    $this->wholesalePriceOriginal    = 0;
    $this->inDollars                 = "no";
    $this->type                      = "";
    $this->appliesVat                = "no";
    $this->appliesIeps               = "no";
    $this->iepsPercentage            = 8;
    $this->createdAt                 = date("Y-m-d H:i:s");
    $this->status                    = "activo";
    $this->wholesalePrice            = 0;
    $this->inputUnit                 = "unidad";
    $this->outputUnit                = "unidad";
    $this->piecesNumber              = 0;
    $this->inventoryControl          = "si";
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getTypeId(): int
  {
    return $this->typeId;
  }

  public function getBrandId(): int
  {
    return $this->brandId;
  }

  public function getCategoryId(): int
  {
    return $this->categoryId;
  }

  public function getCategoryFamilyId(): int
  {
    return $this->categoryFamilyId;
  }

  public function getSupplierId(): int
  {
    return $this->supplierId;
  }

  public function getUnitCodeId(): int
  {
    return $this->unitCodeId;
  }

  public function getProductServiceCodeId(): int
  {
    return $this->productServiceCodeId;
  }

  public function getCode(): string
  {
    return $this->code;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getUnit(): string
  {
    return $this->unit;
  }

  public function getContent(): float
  {
    return $this->content;
  }

  public function getCostPriceOriginal(): float
  {
    return $this->costPriceOriginal;
  }

  public function getCostPrice(): float
  {
    return $this->costPrice;
  }

  public function getSalePriceOriginal(): float
  {
    return $this->salePriceOriginal;
  }

  public function getSalePrice(): float
  {
    return $this->salePrice;
  }

  public function getSalePrice2Original(): float
  {
    return $this->salePrice2Original;
  }

  public function getSalePrice2(): float
  {
    return $this->salePrice2;
  }

  public function getSalePrice3Original(): float
  {
    return $this->salePrice3Original;
  }

  public function getSalePrice3(): float
  {
    return $this->salePrice3;
  }

  public function getWholesaleQuantity(): float
  {
    return $this->wholesaleQuantity;
  }

  public function getWholesalePriceOriginal(): float
  {
    return $this->wholesalePriceOriginal;
  }

  public function getInDollars(): string
  {
    return $this->inDollars;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getAppliesVat(): string
  {
    return $this->appliesVat;
  }

  public function getAppliesIeps(): string
  {
    return $this->appliesIeps;
  }

  public function getIepsPercentage(): float
  {
    return $this->iepsPercentage;
  }

  public function getCreatedAt(): string
  {
    return $this->createdAt;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function getWholesalePrice(): float
  {
    return $this->wholesalePrice;
  }

  public function getInputUnit(): string
  {
    return $this->inputUnit;
  }

  public function getOutputUnit(): string
  {
    return $this->outputUnit;
  }

  public function getPiecesNumber(): int
  {
    return $this->piecesNumber;
  }

  public function getInventoryControl(): string
  {
    return $this->inventoryControl;
  }

  /**
   * Setters
   */
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function setTypeId(int $typeId): self
  {
    $this->typeId = $typeId;
    return $this;
  }

  public function setBrandId(int $brandId): self
  {
    $this->brandId = $brandId;
    return $this;
  }

  public function setCategoryId(int $categoryId): self
  {
    $this->categoryId = $categoryId;
    return $this;
  }

  public function setCategoryFamilyId(int $categoryFamilyId): self
  {
    $this->categoryFamilyId = $categoryFamilyId;
    return $this;
  }

  public function setSupplierId(int $supplierId): self
  {
    $this->supplierId = $supplierId;
    return $this;
  }

  public function setUnitCodeId(int $unitCodeId): self
  {
    $this->unitCodeId = $unitCodeId;
    return $this;
  }

  public function setProductServiceCodeId(int $productServiceCodeId): self
  {
    $this->productServiceCodeId = $productServiceCodeId;
    return $this;
  }

  public function setCode(string $code): self
  {
    $this->code = $code;
    return $this;
  }

  public function setName(string $name): self
  {
    $this->name = $name;
    return $this;
  }

  public function setUnit(string $unit): self
  {
    $this->unit = $unit;
    return $this;
  }

  public function setContent(float $content): self
  {
    $this->content = $content;
    return $this;
  }

  public function setCostPriceOriginal(float $costPriceOriginal): self
  {
    $this->costPriceOriginal = $costPriceOriginal;
    return $this;
  }

  public function setCostPrice(float $costPrice): self
  {
    $this->costPrice = $costPrice;
    return $this;
  }

  public function setSalePriceOriginal(float $salePriceOriginal): self
  {
    $this->salePriceOriginal = $salePriceOriginal;
    return $this;
  }

  public function setSalePrice(float $salePrice): self
  {
    $this->salePrice = $salePrice;
    return $this;
  }

  public function setSalePrice2Original(float $salePrice2Original): self
  {
    $this->salePrice2Original = $salePrice2Original;
    return $this;
  }

  public function setSalePrice2(float $salePrice2): self
  {
    $this->salePrice2 = $salePrice2;
    return $this;
  }

  public function setSalePrice3Original(float $salePrice3Original): self
  {
    $this->salePrice3Original = $salePrice3Original;
    return $this;
  }

  public function setSalePrice3(float $salePrice3): self
  {
    $this->salePrice3 = $salePrice3;
    return $this;
  }

  public function setWholesaleQuantity(float $wholesaleQuantity): self
  {
    $this->wholesaleQuantity = $wholesaleQuantity;
    return $this;
  }

  public function setWholesalePriceOriginal(float $wholesalePriceOriginal): self
  {
    $this->wholesalePriceOriginal = $wholesalePriceOriginal;
    return $this;
  }

  public function setInDollars(string $inDollars): self
  {
    $this->inDollars = $inDollars;
    return $this;
  }

  public function setType(string $type): self
  {
    $this->type = $type;
    return $this;
  }

  public function setAppliesVat(string $appliesVat): self
  {
    $this->appliesVat = $appliesVat;
    return $this;
  }

  public function setAppliesIeps(string $appliesIeps): self
  {
    $this->appliesIeps = $appliesIeps;
    return $this;
  }

  public function setIepsPercentage(float $iepsPercentage): self
  {
    $this->iepsPercentage = $iepsPercentage;
    return $this;
  }

  public function setCreatedAt(string $createdAt): self
  {
    $this->createdAt = $createdAt;
    return $this;
  }

  public function setStatus(string $status): self
  {
    $this->status = $status;
    return $this;
  }

  public function setWholesalePrice(float $wholesalePrice): self
  {
    $this->wholesalePrice = $wholesalePrice;
    return $this;
  }

  public function setInputUnit(string $inputUnit): self
  {
    $this->inputUnit = $inputUnit;
    return $this;
  }

  public function setOutputUnit(string $outputUnit): self
  {
    $this->outputUnit = $outputUnit;
    return $this;
  }

  public function setPiecesNumber(int $piecesNumber): self
  {
    $this->piecesNumber = $piecesNumber;
    return $this;
  }

  public function setInventoryControl(string $inventoryControl): self
  {
    $this->inventoryControl = $inventoryControl;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_producto"]))                $this->setId((int)$data["id_producto"]);
    if (isset($data["id_tipo"]))                    $this->setTypeId((int)$data["id_tipo"]);
    if (isset($data["id_marca"]))                   $this->setBrandId((int)$data["id_marca"]);
    if (isset($data["id_categoria"]))               $this->setCategoryId((int)$data["id_categoria"]);
    if (isset($data["id_categoria_familia"]))       $this->setCategoryFamilyId((int)$data["id_categoria_familia"]);
    if (isset($data["id_proveedor"]))               $this->setSupplierId((int)$data["id_proveedor"]);
    if (isset($data["id_clave_unidad"]))            $this->setUnitCodeId((int)$data["id_clave_unidad"]);
    if (isset($data["id_clave_producto_servicio"])) $this->setProductServiceCodeId((int)$data["id_clave_producto_servicio"]);
    if (isset($data["codigo"]))                     $this->setCode($data["codigo"]);
    if (isset($data["nombre_producto"]))            $this->setName($data["nombre_producto"]);
    if (isset($data["unidad"]))                     $this->setUnit($data["unidad"]);
    if (isset($data["contenido"]))                  $this->setContent((float)$data["contenido"]);
    if (isset($data["precio_costo_original"]))      $this->setCostPriceOriginal((float)$data["precio_costo_original"]);
    if (isset($data["precio_costo"]))               $this->setCostPrice((float)$data["precio_costo"]);
    if (isset($data["precio_venta_original"]))      $this->setSalePriceOriginal((float)$data["precio_venta_original"]);
    if (isset($data["precio_venta"]))               $this->setSalePrice((float)$data["precio_venta"]);
    if (isset($data["precio_venta2_original"]))     $this->setSalePrice2Original((float)$data["precio_venta2_original"]);
    if (isset($data["precio_venta2"]))              $this->setSalePrice2((float)$data["precio_venta2"]);
    if (isset($data["precio_venta3_original"]))     $this->setSalePrice3Original((float)$data["precio_venta3_original"]);
    if (isset($data["precio_venta3"]))              $this->setSalePrice3((float)$data["precio_venta3"]);
    if (isset($data["cantidad_mayoreo"]))           $this->setWholesaleQuantity((float)$data["cantidad_mayoreo"]);
    if (isset($data["precio_mayoreo_original"]))    $this->setWholesalePriceOriginal((float)$data["precio_mayoreo_original"]);
    if (isset($data["en_dolares"]))                 $this->setInDollars($data["en_dolares"]);
    if (isset($data["tipo"]))                       $this->setType($data["tipo"]);
    if (isset($data["aplica_iva"]))                 $this->setAppliesVat($data["aplica_iva"]);
    if (isset($data["aplica_ieps"]))                $this->setAppliesIeps($data["aplica_ieps"]);
    if (isset($data["ieps_porcentaje"]))            $this->setIepsPercentage((float)$data["ieps_porcentaje"]);
    if (isset($data["fecha_creacion"]))             $this->setCreatedAt($data["fecha_creacion"]);
    if (isset($data["status"]))                     $this->setStatus($data["status"]);
    if (isset($data["precio_mayoreo"]))             $this->setWholesalePrice((float)$data["precio_mayoreo"]);
    if (isset($data["unidad_entrada"]))             $this->setInputUnit($data["unidad_entrada"]);
    if (isset($data["unidad_salida"]))              $this->setOutputUnit($data["unidad_salida"]);
    if (isset($data["numero_piezas"]))              $this->setPiecesNumber((int)$data["numero_piezas"]);
    if (isset($data["control_inventario"]))         $this->setInventoryControl($data["control_inventario"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        id_tipo,
        id_marca,
        id_categoria,
        id_categoria_familia,
        id_proveedor,
        id_clave_unidad,
        id_clave_producto_servicio,
        codigo,
        nombre_producto,
        unidad,
        contenido,
        precio_costo_original,
        precio_costo,
        precio_venta_original,
        precio_venta,
        precio_venta2_original,
        precio_venta2,
        precio_venta3_original,
        precio_venta3,
        cantidad_mayoreo,
        precio_mayoreo_original,
        en_dolares,
        tipo,
        aplica_iva,
        aplica_ieps,
        ieps_porcentaje,
        fecha_creacion,
        status,
        precio_mayoreo,
        unidad_entrada,
        unidad_salida,
        numero_piezas,
        control_inventario
      ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $typeId                    = $this->getTypeId();
      $brandId                   = $this->getBrandId();
      $categoryId                = $this->getCategoryId();
      $categoryFamilyId          = $this->getCategoryFamilyId();
      $supplierId                = $this->getSupplierId();
      $unitCodeId                = $this->getUnitCodeId();
      $productServiceCodeId      = $this->getProductServiceCodeId();
      $code                      = $this->getCode();
      $name                      = $this->getName();
      $unit                      = $this->getUnit();
      $content                   = $this->getContent();
      $costPriceOriginal         = $this->getCostPriceOriginal();
      $costPrice                 = $this->getCostPrice();
      $salePriceOriginal         = $this->getSalePriceOriginal();
      $salePrice                 = $this->getSalePrice();
      $salePrice2Original        = $this->getSalePrice2Original();
      $salePrice2                = $this->getSalePrice2();
      $salePrice3Original        = $this->getSalePrice3Original();
      $salePrice3                = $this->getSalePrice3();
      $wholesaleQuantity         = $this->getWholesaleQuantity();
      $wholesalePriceOriginal    = $this->getWholesalePriceOriginal();
      $inDollars                 = $this->getInDollars();
      $type                      = $this->getType();
      $appliesVat                = $this->getAppliesVat();
      $appliesIeps               = $this->getAppliesIeps();
      $iepsPercentage            = $this->getIepsPercentage();
      $createdAt                 = $this->getCreatedAt();
      $status                    = $this->getStatus();
      $wholesalePrice            = $this->getWholesalePrice();
      $inputUnit                 = $this->getInputUnit();
      $outputUnit                = $this->getOutputUnit();
      $piecesNumber              = $this->getPiecesNumber();
      $inventoryControl          = $this->getInventoryControl();

      $stmt->bind_param(
        "iiiiiiisssdddddddddddssssdssdssis",
        $typeId,
        $brandId,
        $categoryId,
        $categoryFamilyId,
        $supplierId,
        $unitCodeId,
        $productServiceCodeId,
        $code,
        $name,
        $unit,
        $content,
        $costPriceOriginal,
        $costPrice,
        $salePriceOriginal,
        $salePrice,
        $salePrice2Original,
        $salePrice2,
        $salePrice3Original,
        $salePrice3,
        $wholesaleQuantity,
        $wholesalePriceOriginal,
        $inDollars,
        $type,
        $appliesVat,
        $appliesIeps,
        $iepsPercentage,
        $createdAt,
        $status,
        $wholesalePrice,
        $inputUnit,
        $outputUnit,
        $piecesNumber,
        $inventoryControl
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Producto creado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTOS_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear el producto";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page                      = $params["page"]        ?? 1;
    $perPage                   = $params["perPage"]     ?? 10;
    $offset                    = ($page - 1) * $perPage;

    $typeId                    = $params["typeId"] ?? null;
    $typeId                    = isset($typeId) ? (int) $typeId : null;

    $byTypeId                  = isset($typeId) ? "(id_tipo = '{$typeId}')" : "1=1";

    $brandId                   = $params["brandId"] ?? null;
    $brandId                   = isset($brandId) ? (int) $brandId : null;

    $byBrandId                 = isset($brandId) ? "(id_marca = '{$brandId}')" : "1=1";

    $categoryId                = $params["categoryId"] ?? null;
    $categoryId                = isset($categoryId) ? (int) $categoryId : null;

    $byCategoryId              = isset($categoryId) ? "(id_categoria = '{$categoryId}')" : "1=1";

    $categoryFamilyId          = $params["categoryFamilyId"] ?? null;
    $categoryFamilyId          = isset($categoryFamilyId) ? (int) $categoryFamilyId : null;

    $byCategoryFamilyId        = isset($categoryFamilyId) ? "(id_categoria_familia = '{$categoryFamilyId}')" : "1=1";

    $supplierId                = $params["supplierId"] ?? null;
    $supplierId                = isset($supplierId) ? (int) $supplierId : null;

    $bySupplierId              = isset($supplierId) ? "(id_proveedor = '{$supplierId}')" : "1=1";

    $unitCodeId                = $params["unitCodeId"] ?? null;
    $unitCodeId                = isset($unitCodeId) ? (int) $unitCodeId : null;

    $byUnitCodeId              = isset($unitCodeId) ? "(id_clave_unidad = '{$unitCodeId}')" : "1=1";

    $productServiceCodeId      = $params["productServiceCodeId"] ?? null;
    $productServiceCodeId      = isset($productServiceCodeId) ? (int) $productServiceCodeId : null;

    $byProductServiceCodeId    = isset($productServiceCodeId) ? "(id_clave_producto_servicio = '{$productServiceCodeId}')" : "1=1";

    $code                      = $params["code"] ?? null;
    $code                      = isset($code) ? mysqli_real_escape_string($mysqli, $code) : null;

    $byCode                    = isset($code) ? "(codigo = '{$code}')" : "1=1";

    $name                      = $params["name"] ?? null;
    $name                      = isset($name) ? mysqli_real_escape_string($mysqli, $name) : null;

    $byName                    = isset($name) ? "(nombre_producto = '{$name}')" : "1=1";

    $unit                      = $params["unit"] ?? null;
    $unit                      = isset($unit) ? mysqli_real_escape_string($mysqli, $unit) : null;

    $byUnit                    = isset($unit) ? "(unidad = '{$unit}')" : "1=1";

    $content                   = $params["content"] ?? null;
    $content                   = isset($content) ? mysqli_real_escape_string($mysqli, $content) : null;

    $byContent                 = isset($content) ? "(contenido = '{$content}')" : "1=1";

    $costPriceOriginal         = $params["costPriceOriginal"] ?? null;
    $costPriceOriginal         = isset($costPriceOriginal) ? mysqli_real_escape_string($mysqli, $costPriceOriginal) : null;

    $byCostPriceOriginal       = isset($costPriceOriginal) ? "(precio_costo_original = '{$costPriceOriginal}')" : "1=1";

    $costPrice                 = $params["costPrice"] ?? null;
    $costPrice                 = isset($costPrice) ? mysqli_real_escape_string($mysqli, $costPrice) : null;

    $byCostPrice               = isset($costPrice) ? "(precio_costo = '{$costPrice}')" : "1=1";

    $salePriceOriginal         = $params["salePriceOriginal"] ?? null;
    $salePriceOriginal         = isset($salePriceOriginal) ? mysqli_real_escape_string($mysqli, $salePriceOriginal) : null;

    $bySalePriceOriginal       = isset($salePriceOriginal) ? "(precio_venta_original = '{$salePriceOriginal}')" : "1=1";

    $salePrice                 = $params["salePrice"] ?? null;
    $salePrice                 = isset($salePrice) ? mysqli_real_escape_string($mysqli, $salePrice) : null;

    $bySalePrice               = isset($salePrice) ? "(precio_venta = '{$salePrice}')" : "1=1";

    $salePrice2Original        = $params["salePrice2Original"] ?? null;
    $salePrice2Original        = isset($salePrice2Original) ? mysqli_real_escape_string($mysqli, $salePrice2Original) : null;

    $bySalePrice2Original      = isset($salePrice2Original) ? "(precio_venta2_original = '{$salePrice2Original}')" : "1=1";

    $salePrice2                = $params["salePrice2"] ?? null;
    $salePrice2                = isset($salePrice2) ? mysqli_real_escape_string($mysqli, $salePrice2) : null;

    $bySalePrice2              = isset($salePrice2) ? "(precio_venta2 = '{$salePrice2}')" : "1=1";

    $salePrice3Original        = $params["salePrice3Original"] ?? null;
    $salePrice3Original        = isset($salePrice3Original) ? mysqli_real_escape_string($mysqli, $salePrice3Original) : null;

    $bySalePrice3Original      = isset($salePrice3Original) ? "(precio_venta3_original = '{$salePrice3Original}')" : "1=1";

    $salePrice3                = $params["salePrice3"] ?? null;
    $salePrice3                = isset($salePrice3) ? mysqli_real_escape_string($mysqli, $salePrice3) : null;

    $bySalePrice3              = isset($salePrice3) ? "(precio_venta3 = '{$salePrice3}')" : "1=1";

    $wholesaleQuantity         = $params["wholesaleQuantity"] ?? null;
    $wholesaleQuantity         = isset($wholesaleQuantity) ? mysqli_real_escape_string($mysqli, $wholesaleQuantity) : null;

    $byWholesaleQuantity       = isset($wholesaleQuantity) ? "(cantidad_mayoreo = '{$wholesaleQuantity}')" : "1=1";

    $wholesalePriceOriginal    = $params["wholesalePriceOriginal"] ?? null;
    $wholesalePriceOriginal    = isset($wholesalePriceOriginal) ? mysqli_real_escape_string($mysqli, $wholesalePriceOriginal) : null;

    $byWholesalePriceOriginal  = isset($wholesalePriceOriginal) ? "(precio_mayoreo_original = '{$wholesalePriceOriginal}')" : "1=1";

    $inDollars                 = $params["inDollars"] ?? null;
    $inDollars                 = isset($inDollars) ? mysqli_real_escape_string($mysqli, $inDollars) : null;

    $byInDollars               = isset($inDollars) ? "(en_dolares = '{$inDollars}')" : "1=1";

    $type                      = $params["type"] ?? null;
    $type                      = isset($type) ? mysqli_real_escape_string($mysqli, $type) : null;

    $byType                    = isset($type) ? "(tipo = '{$type}')" : "1=1";

    $appliesVat                = $params["appliesVat"] ?? null;
    $appliesVat                = isset($appliesVat) ? mysqli_real_escape_string($mysqli, $appliesVat) : null;

    $byAppliesVat              = isset($appliesVat) ? "(aplica_iva = '{$appliesVat}')" : "1=1";

    $appliesIeps               = $params["appliesIeps"] ?? null;
    $appliesIeps               = isset($appliesIeps) ? mysqli_real_escape_string($mysqli, $appliesIeps) : null;

    $byAppliesIeps             = isset($appliesIeps) ? "(aplica_ieps = '{$appliesIeps}')" : "1=1";

    $iepsPercentage            = $params["iepsPercentage"] ?? null;
    $iepsPercentage            = isset($iepsPercentage) ? mysqli_real_escape_string($mysqli, $iepsPercentage) : null;

    $byIepsPercentage          = isset($iepsPercentage) ? "(ieps_porcentaje = '{$iepsPercentage}')" : "1=1";

    $createdAt                 = $params["createdAt"] ?? null;
    $createdAt                 = isset($createdAt) ? mysqli_real_escape_string($mysqli, $createdAt) : null;

    $byCreatedAt               = isset($createdAt) ? "(fecha_creacion = '{$createdAt}')" : "1=1";

    $status                    = $params["status"] ?? null;
    $status                    = isset($status) ? mysqli_real_escape_string($mysqli, $status) : null;

    $byStatus                  = isset($status) ? "(status = '{$status}')" : "1=1";

    $wholesalePrice            = $params["wholesalePrice"] ?? null;
    $wholesalePrice            = isset($wholesalePrice) ? mysqli_real_escape_string($mysqli, $wholesalePrice) : null;

    $byWholesalePrice          = isset($wholesalePrice) ? "(precio_mayoreo = '{$wholesalePrice}')" : "1=1";

    $inputUnit                 = $params["inputUnit"] ?? null;
    $inputUnit                 = isset($inputUnit) ? mysqli_real_escape_string($mysqli, $inputUnit) : null;

    $byInputUnit               = isset($inputUnit) ? "(unidad_entrada = '{$inputUnit}')" : "1=1";

    $outputUnit                = $params["outputUnit"] ?? null;
    $outputUnit                = isset($outputUnit) ? mysqli_real_escape_string($mysqli, $outputUnit) : null;

    $byOutputUnit              = isset($outputUnit) ? "(unidad_salida = '{$outputUnit}')" : "1=1";

    $piecesNumber              = $params["piecesNumber"] ?? null;
    $piecesNumber              = isset($piecesNumber) ? (int) $piecesNumber : null;

    $byPiecesNumber            = isset($piecesNumber) ? "(numero_piezas = '{$piecesNumber}')" : "1=1";

    $inventoryControl          = $params["inventoryControl"] ?? null;
    $inventoryControl          = isset($inventoryControl) ? mysqli_real_escape_string($mysqli, $inventoryControl) : null;

    $byInventoryControl        = isset($inventoryControl) ? "(control_inventario = '{$inventoryControl}')" : "1=1";

    $conditions = [
      $byTypeId,
      $byBrandId,
      $byCategoryId,
      $byCategoryFamilyId,
      $bySupplierId,
      $byUnitCodeId,
      $byProductServiceCodeId,
      $byCode,
      $byName,
      $byUnit,
      $byContent,
      $byCostPriceOriginal,
      $byCostPrice,
      $bySalePriceOriginal,
      $bySalePrice,
      $bySalePrice2Original,
      $bySalePrice2,
      $bySalePrice3Original,
      $bySalePrice3,
      $byWholesaleQuantity,
      $byWholesalePriceOriginal,
      $byInDollars,
      $byType,
      $byAppliesVat,
      $byAppliesIeps,
      $byIepsPercentage,
      $byCreatedAt,
      $byStatus,
      $byWholesalePrice,
      $byInputUnit,
      $byOutputUnit,
      $byPiecesNumber,
      $byInventoryControl,
    ];

    $conditions = implode(" AND ", $conditions);

    // Clausulas
    $cFrom  = "FROM {$this->table}";
    $cWhere = "WHERE {$conditions}";
    $cLimit = "LIMIT {$offset}, {$perPage}";

    // Query total
    try {
      $query  = "SELECT COUNT(*) AS total {$cFrom} {$cWhere}";
      $result = mysqli_query($mysqli, $query);
      $data   = mysqli_fetch_assoc($result);
      $total  = $data["total"] ?? 0;

      $pages = ceil($total / $perPage);

      if ($pages == 0) {
        $response->status   = "success";
        $response->message  = "No se encontraron productos";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_producto DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new ProductosModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Productos obtenidos exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTOS_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de productos";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        id_tipo                    = ?,
        id_marca                   = ?,
        id_categoria               = ?,
        id_categoria_familia       = ?,
        id_proveedor               = ?,
        id_clave_unidad            = ?,
        id_clave_producto_servicio = ?,
        codigo                     = ?,
        nombre_producto            = ?,
        unidad                     = ?,
        contenido                  = ?,
        precio_costo_original      = ?,
        precio_costo               = ?,
        precio_venta_original      = ?,
        precio_venta               = ?,
        precio_venta2_original     = ?,
        precio_venta2              = ?,
        precio_venta3_original     = ?,
        precio_venta3              = ?,
        cantidad_mayoreo           = ?,
        precio_mayoreo_original    = ?,
        en_dolares                 = ?,
        tipo                       = ?,
        aplica_iva                 = ?,
        aplica_ieps                = ?,
        ieps_porcentaje            = ?,
        fecha_creacion             = ?,
        status                     = ?,
        precio_mayoreo             = ?,
        unidad_entrada             = ?,
        unidad_salida              = ?,
        numero_piezas              = ?,
        control_inventario         = ?
      WHERE
        id_producto = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $typeId                    = $this->getTypeId();
      $brandId                   = $this->getBrandId();
      $categoryId                = $this->getCategoryId();
      $categoryFamilyId          = $this->getCategoryFamilyId();
      $supplierId                = $this->getSupplierId();
      $unitCodeId                = $this->getUnitCodeId();
      $productServiceCodeId      = $this->getProductServiceCodeId();
      $code                      = $this->getCode();
      $name                      = $this->getName();
      $unit                      = $this->getUnit();
      $content                   = $this->getContent();
      $costPriceOriginal         = $this->getCostPriceOriginal();
      $costPrice                 = $this->getCostPrice();
      $salePriceOriginal         = $this->getSalePriceOriginal();
      $salePrice                 = $this->getSalePrice();
      $salePrice2Original        = $this->getSalePrice2Original();
      $salePrice2                = $this->getSalePrice2();
      $salePrice3Original        = $this->getSalePrice3Original();
      $salePrice3                = $this->getSalePrice3();
      $wholesaleQuantity         = $this->getWholesaleQuantity();
      $wholesalePriceOriginal    = $this->getWholesalePriceOriginal();
      $inDollars                 = $this->getInDollars();
      $type                      = $this->getType();
      $appliesVat                = $this->getAppliesVat();
      $appliesIeps               = $this->getAppliesIeps();
      $iepsPercentage            = $this->getIepsPercentage();
      $createdAt                 = $this->getCreatedAt();
      $status                    = $this->getStatus();
      $wholesalePrice            = $this->getWholesalePrice();
      $inputUnit                 = $this->getInputUnit();
      $outputUnit                = $this->getOutputUnit();
      $piecesNumber              = $this->getPiecesNumber();
      $inventoryControl          = $this->getInventoryControl();
      $id                        = $this->getId();

      $stmt->bind_param(
        "iiiiiiisssdddddddddddssssdssdssisi",
        $typeId,
        $brandId,
        $categoryId,
        $categoryFamilyId,
        $supplierId,
        $unitCodeId,
        $productServiceCodeId,
        $code,
        $name,
        $unit,
        $content,
        $costPriceOriginal,
        $costPrice,
        $salePriceOriginal,
        $salePrice,
        $salePrice2Original,
        $salePrice2,
        $salePrice3Original,
        $salePrice3,
        $wholesaleQuantity,
        $wholesalePriceOriginal,
        $inDollars,
        $type,
        $appliesVat,
        $appliesIeps,
        $iepsPercentage,
        $createdAt,
        $status,
        $wholesalePrice,
        $inputUnit,
        $outputUnit,
        $piecesNumber,
        $inventoryControl,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Producto actualizado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTOS_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar el producto";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_producto = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Producto eliminado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTOS_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar el producto";
    }

    return $response;
  }

  public function delete(): ModelResponse
  {
    return $this->deleteById($this->getId());
  }

  public function getById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "SELECT * FROM {$this->table} WHERE id_producto = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows > 0) {
        $data = $result->fetch_assoc();
        $this->from($data);

        $response->status   = "success";
        $response->message  = "Producto obtenido exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTOS_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el producto";
    }

    return $response;
  }

  public function getByCode(string $code): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "SELECT * FROM {$this->table} WHERE codigo = ? LIMIT 1";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("s", $code);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows > 0) {
        $data = $result->fetch_assoc();
        $this->from($data);

        $response->status   = "success";
        $response->message  = "Producto obtenido exitosamente";
      } else {
        $response->status   = "error";
        $response->message  = "Producto no encontrado";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTOS_MODEL::GET_BY_CODE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el producto";
    }

    return $response;
  }

}
