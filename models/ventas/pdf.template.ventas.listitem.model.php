<?php
class PDFTemplateVentasListItemModel
{
  private $date;
  private $brand;
  private $line;
  private $code;
  private $name;
  private $quantity;
  private $totalCost;
  private $totalSale;
  private $branch;

  public function __construct()
  {
    $this->date       = "";
    $this->brand      = "";
    $this->line       = "";
    $this->code       = "";
    $this->name       = "";
    $this->quantity   = 0;
    $this->totalCost  = 0.0;
    $this->totalSale  = 0.0;
    $this->branch     = "";
  }

  public function getDate()
  {
    return $this->date;
  }

  public function getBrand()
  {
    return $this->brand;
  }

  public function getLine()
  {
    return $this->line;
  }

  public function getCode()
  {
    return $this->code;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getQuantity()
  {
    return $this->quantity;
  }

  public function getTotalCost()
  {
    return $this->totalCost;
  }

  public function getTotalSale()
  {
    return $this->totalSale;
  }

  public function getBranch()
  {
    return $this->branch;
  }

  public function setDate($date)
  {
    $this->date = $date;
  }

  public function setBrand($brand)
  {
    $this->brand = $brand;
  }

  public function setLine($line)
  {
    $this->line = $line;
  }

  public function setCode($code)
  {
    $this->code = $code;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setQuantity($quantity)
  {
    $this->quantity = $quantity;
  }

  public function setTotalCost($totalCost)
  {
    $this->totalCost = $totalCost;
  }

  public function setTotalSale($totalSale)
  {
    $this->totalSale = $totalSale;
  }

  public function setBranch($branch)
  {
    $this->branch = $branch;
  }
}
