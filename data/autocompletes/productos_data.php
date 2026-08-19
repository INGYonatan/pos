<?php
require_once __DIR__ . "/../lib/settings.inc.php";
require_once __DIR__ . "/../lib/helpers/products.helper.php";

$term = cleanStr($_GET['term']);

$productHelper    = new ProductHelper();
$productsCatalog  = $productHelper->getForSelect2($term);

$catalog = new stdClass();
$catalog->results = [];

$catalog->pagination = new stdClass();
$catalog->pagination->more = false;

$id    = "";
$label = "--Seleccionar--";

$result       = new stdClass();
$result->id   = $id;
$result->text = $label;

$catalog->results[] = $result;

/* private $id; */
/* private $categoryId; */
/* private $familyId; */
/* private $unitId; */
/* private $productServiceId; */
/* private $code; */
/* private $name; */
/* private $unit; */
/* private $content; */
/* private $costPrice; */
/* private $salePrice; */
/* private $wholesaleAmount; */
/* private $wholesalePrice; */
/* private $originalCostPrice; */
/* private $originalSalePrice; */
/* private $originalWholesalePrice; */
/* private $applyIva; */
/* private $applyIeps; */
/* private $iepsPercentage; */
/* private $inDollars; */
/* private $inputUnit; */
/* private $outputUnit; */
/* private $type; */
/* private $pieces; */
/* private $category; */
/* private $family; */
/* private $unitName; */
/* private $productServiceDescription; */

foreach ($productsCatalog as $item) :
  $result                     = new stdClass();
  $result->id                 = $item->getId();
  $result->text               = $item->getName();
  $result->categoryId         = $item->getCategoryId();
  $result->familyId           = $item->getFamilyId();
  $result->unitId             = $item->getUnitId();
  $result->productServiceId   = $item->getProductServiceId();
  $result->code               = $item->getCode();
  $result->name               = $item->getName();
  $result->unit               = $item->getUnit();
  $result->content            = $item->getContent();
  $result->costPrice          = $item->getCostPrice();
  $result->salePrice          = $item->getSalePrice();
  $result->wholesaleAmount    = $item->getWholesaleAmount();
  $result->wholesalePrice     = $item->getWholesalePrice();
  $result->originalCostPrice  = $item->getOriginalCostPrice();
  $result->originalSalePrice  = $item->getOriginalSalePrice();
  $result->originalWholesalePrice = $item->getOriginalWholesalePrice();
  $result->applyIva           = $item->getApplyIva();
  $result->applyIeps          = $item->getApplyIeps();
  $result->iepsPercentage     = $item->getIepsPercentage();
  $result->inDollars          = $item->getInDollars();
  $result->inputUnit          = $item->getInputUnit();
  $result->outputUnit         = $item->getOutputUnit();
  $result->type               = $item->getType();
  $result->pieces             = $item->getPieces();
  $result->category           = $item->getCategory();
  $result->family             = $item->getFamily();
  $result->unitName           = $item->getUnitName();
  $result->productServiceDescription = $item->getProductServiceDescription();

  $catalog->results[] = $result;
endforeach;

echo json_encode($catalog);
die;
