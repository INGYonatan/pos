<?php
require_once __DIR__ . "/modelresponse.model.php";

/* paal_venta_facturas 
  id_venta_factura INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL,
  id_factura INT NOT NULL,
  tipo enum ("ingreso", "anticipo", "nota_credito") NOT NULL DEFAULT "ingreso"
*/

class SaleInvoicesModel
{
  private $id;
  private $saleId;
  private $invoiceId;
  private $type;

  public function __construct()
  {
    $this->id         = 0;
    $this->saleId     = 0;
    $this->invoiceId  = 0;
    $this->type       = "ingreso";
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getSaleId()
  {
    return $this->saleId;
  }

  public function getInvoiceId()
  {
    return $this->invoiceId;
  }

  public function getType()
  {
    return $this->type;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setSaleId($saleId)
  {
    $this->saleId = $saleId;
  }

  public function setInvoiceId($invoiceId)
  {
    $this->invoiceId = $invoiceId;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * Another methods
   */
  public function from($data)
  {
    if ($data["id_venta_factura"]) $this->setId($data["id_venta_factura"]);
    if ($data["id_venta"]) $this->setSaleId($data["id_venta"]);
    if ($data["id_factura"]) $this->setInvoiceId($data["id_factura"]);
    if ($data["tipo"]) $this->setType($data["tipo"]);
  }

  public function toArray()
  {
    return [
      "id_venta_factura" => $this->getId(),
      "id_venta" => $this->getSaleId(),
      "id_factura" => $this->getInvoiceId(),
      "tipo" => $this->getType()
    ];
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    try {
      $query = "INSERT INTO paal_venta_facturas (
          id_venta,
          id_factura,
          tipo
        ) VALUES (
          ?,
          ?,
          ?
        )
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param(
        "iis",
        $this->getSaleId(),
        $this->getInvoiceId(),
        $this->getType()
      );

      $result = $stmt->execute();

      if ($result) :
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "La relación venta-factura se guardó correctamente";
        $response->data     = $this;
      endif;
    } catch (Exception $e) {
      error_log("SaleInvoicesModel->create: {$e->getMessage()}");
    }

    return $response;
  }
}
