<?php
class FacturasPagoModel
{
  /* 
  id_factura
  id_factura_ingreso
  id_emisor
  id_cliente
  serie
  folio
  uuid
  enviado
  cancelado
  comentarios
  fecha
  */

  private $id;
  private $incomeInvoiceId;
  private $issuerId;
  private $customerId;
  private $serie;
  private $folio;
  private $uuid;
  private $sent;
  private $cancelled;
  private $comments;
  private $date;

  public function __construct()
  {
    $this->id               = 0;
    $this->incomeInvoiceId  = 0;
    $this->issuerId         = 0;
    $this->customerId       = 0;
    $this->serie            = "";
    $this->folio            = 0;
    $this->uuid             = "";
    $this->sent             = 0;
    $this->cancelled        = 0;
    $this->comments         = "";
    $this->date             = date("Y-m-d");
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getIncomeInvoiceId()
  {
    return $this->incomeInvoiceId;
  }

  public function getIssuerId()
  {
    return $this->issuerId;
  }

  public function getCustomerId()
  {
    return $this->customerId;
  }

  public function getSerie()
  {
    return $this->serie;
  }

  public function getFolio()
  {
    return $this->folio;
  }

  public function getUuid()
  {
    return $this->uuid;
  }

  public function getSent()
  {
    return $this->sent;
  }

  public function getCancelled()
  {
    return $this->cancelled;
  }

  public function getComments()
  {
    return $this->comments;
  }

  public function getDate()
  {
    return $this->date;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setIncomeInvoiceId($id)
  {
    $this->incomeInvoiceId = $id;
  }

  public function setIssuerId($id)
  {
    $this->issuerId = $id;
  }

  public function setCustomerId($id)
  {
    $this->customerId = $id;
  }

  public function setSerie($serie)
  {
    $this->serie = $serie;
  }

  public function setFolio($folio)
  {
    $this->folio = $folio;
  }

  public function setUuid($uuid)
  {
    $this->uuid = $uuid;
  }

  public function setSent($sent)
  {
    $this->sent = $sent;
  }

  public function setCancelled($cancelled)
  {
    $this->cancelled = $cancelled;
  }

  public function setComments($comments)
  {
    $this->comments = $comments;
  }

  public function setDate($date)
  {
    $this->date = $date;
  }

  /**
   * Another methods
   */
  public function from($data)
  {
    $this->setId($data["id_factura_pago"]);
    $this->setIncomeInvoiceId($data["id_factura_ingreso"]);
    $this->setIssuerId($data["id_emisor"]);
    $this->setCustomerId($data["id_cliente"]);
    $this->setSerie($data["serie"]);
    $this->setFolio($data["folio"]);
    $this->setUuid($data["uuid"]);
    $this->setSent($data["enviado"]);
    $this->setCancelled($data["cancelado"]);
    $this->setComments($data["comentarios"]);
    $this->setDate($data["fecha"]);
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    try {
      $query = "INSERT INTO paal_facturas_p (
          id_factura,
          id_factura_ingreso,
          id_emisor,
          id_cliente,
          serie,
          folio,
          uuid,
          enviado,
          cancelado,
          comentarios,
          fecha
        ) VALUES (
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?,
          ?
        )
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param(
        "iiiiissssss",
        $this->getId(),
        $this->getIncomeInvoiceId(),
        $this->getIssuerId(),
        $this->getCustomerId(),
        $this->getSerie(),
        $this->getFolio(),
        $this->getUuid(),
        $this->getSent(),
        $this->getCancelled(),
        $this->getComments(),
        $this->getDate()
      );

      $result = $stmt->execute();

      if ($result) :
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "La factura se guardó correctamente";
        $response->data     = $this;
      endif;
    } catch (Exception $e) {
      error_log("FacturasModel->create: {$e->getMessage()}");
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    try {
      $query = "UPDATE paal_facturas_p SET
          id_factura_ingreso  = ?,
          id_emisor           = ?,
          id_cliente          = ?,
          serie               = ?,
          folio               = ?,
          uuid                = ?,
          enviado             = ?,
          cancelado           = ?,
          comentarios         = ?,
          fecha               = ?
        WHERE
          id_factura_pago = ?
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param(
        "iiisssiissi",
        $this->getIncomeInvoiceId(),
        $this->getIssuerId(),
        $this->getCustomerId(),
        $this->getSerie(),
        $this->getFolio(),
        $this->getUuid(),
        $this->getSent(),
        $this->getCancelled(),
        $this->getComments(),
        $this->getDate(),
        $this->getId()
      );

      $result = $stmt->execute();

      if ($result) :
        $response->status   = "success";
        $response->message  = "La factura se actualizó correctamente";
        $response->data     = $this;
      endif;
    } catch (Exception $e) {
      error_log("FacturasModel->update: {$e->getMessage()}");
    }

    return $response;
  }
}
