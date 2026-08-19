<?php
require_once __DIR__ . "/config/apiresponse.model.php";
require_once __DIR__ . "/../data/lib/helpers/products.helper.php";

class ProductsApi extends AccessApiController
{
  private $model;

  public $id;
  public $categoryId;
  public $familyId;
  public $unitId;
  public $productServiceId;
  public $code;
  public $name;
  public $unit;
  public $content;
  public $costPrice;
  public $salePrice;
  public $wholesaleAmount;
  public $wholesalePrice;
  public $originalCostPrice;
  public $originalSalePrice;
  public $originalWholesalePrice;
  public $onlinePrice;
  public $applyIva;
  public $inDollars;
  public $inputUnit;
  public $outputUnit;
  public $type;
  public $pieces;
  public $category;
  public $family;
  public $unitName;
  public $productServiceDescription;
  public $globalStock;

  public $sku;
  public $price;
  public $stock;

  public $salePrice1;
  public $salePrice2;
  public $salePrice3;

  public function __construct()
  {
    parent::__construct();

    $this->model = new ProductHelper();
  }

  public function from(ProductHelper $data)
  {
    // $this->id       = md5($data->getId());
    // $this->botId    = md5($data->getBotId());
    // $this->sku      = $data->getSku();
    // $this->name     = $data->getDescription();
    // $this->width    = $data->getWidth();
    // $this->height   = $data->getHeight();
    // $this->diameter = $data->getDiameter();
    // $this->price    = $data->getMxnPrice();
    // $this->stock    = $data->getStock();

    $this->id                           = md5($data->getId());
    $this->categoryId                   = md5($data->getCategoryId());
    $this->familyId                     = md5($data->getFamilyId());
    $this->unitId                       = md5($data->getUnitId());
    $this->productServiceId             = md5($data->getProductServiceId());
    $this->code                         = $data->getCode();
    $this->name                         = $data->getName();
    $this->unit                         = $data->getUnit();
    $this->content                      = $data->getContent();
    $this->costPrice                    = $data->getCostPrice();
    $this->salePrice                    = $data->getSalePrice();
    $this->wholesaleAmount              = $data->getWholesaleAmount();
    $this->wholesalePrice               = $data->getWholesalePrice();
    $this->originalCostPrice            = $data->getOriginalCostPrice();
    $this->originalSalePrice            = $data->getOriginalSalePrice();
    $this->originalWholesalePrice       = $data->getOriginalWholesalePrice();
    $this->applyIva                     = $data->getApplyIva();
    $this->inDollars                    = $data->getInDollars();
    $this->inputUnit                    = $data->getInputUnit();
    $this->outputUnit                   = $data->getOutputUnit();
    $this->type                         = $data->getType();
    $this->pieces                       = $data->getPieces();
    $this->category                     = $data->getCategory();
    $this->family                       = $data->getFamily();
    $this->unitName                     = $data->getUnitName();
    $this->productServiceDescription    = $data->getProductServiceDescription();
    $this->globalStock                  = $data->getGlobalStock();

    $this->sku                          = $data->getCode();
    $this->price                        = $data->getSalePrice3();
    $this->stock                        = $data->getGlobalStock();

    $this->salePrice1                   = $data->getSalePrice();
    $this->salePrice2                   = $data->getSalePrice2();
    $this->salePrice3                   = $data->getSalePrice3();
  }

  public function getAllWithGlobalStock()
  {
    $response = new ApiResponseModel();

    $typeId = $_GET["typeId"] ?? null;

    $params = [
      "typeId" => $typeId
    ];

    /**
     * @var ProductHelper[] $rows
     */
    $rows = $this->model->getAllWithBranchStocksSum($params);

    if (count($rows) == 0) sendApiResponse($response, 404, "No se encontraron resultados");

    $products = [];

    foreach ($rows as $row) {
      $item = new ProductsApi();
      $item->from($row);

      $products[] = $item;
    }

    sendApiResponse($response, 200, "Productos encontrados", [
      "rows" => $products
    ]);
  }
}
