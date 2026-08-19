<?php
class PDFTemplateInventarioListItemModel
{
  private $id;
  private $name;
  private $brand;
  private $category;
  private $lastPurchasePrice;
  private $stock;
  private $totalPurchasePrice;

  public function __construct()
  {
    $this->id                 = "";
    $this->name               = "";
    $this->brand              = "";
    $this->category           = "";
    $this->lastPurchasePrice  = 0.0;
    $this->stock              = 0;
    $this->totalPurchasePrice = 0.0;
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getBrand()
  {
    return $this->brand;
  }

  public function getCategory()
  {
    return $this->category;
  }

  public function getLastPurchasePrice()
  {
    return $this->lastPurchasePrice;
  }

  public function getStock()
  {
    return $this->stock;
  }

  public function getTotalPurchasePrice()
  {
    return $this->totalPurchasePrice;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setBrand($brand)
  {
    $this->brand = $brand;
  }

  public function setCategory($category)
  {
    $this->category = $category;
  }

  public function setLastPurchasePrice($lastPurchasePrice)
  {
    $this->lastPurchasePrice = $lastPurchasePrice;
  }

  public function setStock($stock)
  {
    $this->stock = $stock;
  }

  public function setTotalPurchasePrice($totalPurchasePrice)
  {
    $this->totalPurchasePrice = $totalPurchasePrice;
  }
}
