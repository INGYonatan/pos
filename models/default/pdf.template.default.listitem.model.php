<?php
class PDFTemplateDefaultListItemModel
{
  private $id;
  private $name;
  private $stock;
  private $quantity;
  private $price;

  public function __construct()
  {
    $this->id       = "";
    $this->name     = "";
    $this->stock    = 0;
    $this->quantity = 0;
    $this->price    = 0;
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

  public function getStock()
  {
    return $this->stock;
  }

  public function getQuantity()
  {
    return $this->quantity;
  }

  public function getPrice()
  {
    return $this->price;
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

  public function setStock($stock)
  {
    $this->stock = $stock;
  }

  public function setQuantity($quantity)
  {
    $this->quantity = $quantity;
  }

  public function setPrice($price)
  {
    $this->price = $price;
  }
}
