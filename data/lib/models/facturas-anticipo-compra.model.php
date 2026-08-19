<?php
require_once __DIR__ . "/modelresponse.model.php";

class FacturasAnticipoCompraModel
{
  private $id;
  private $issuerId;
  private $customerId;
  private $useCfdiId;
  private $paymentFormId;
  private $saleId;
  private $serie;
  private $folio;
  private $uuid;
  private $paymentMethod;
  private $currency;
  private $total;
  private $comments;
  private $sent;
  private $cancelled;
  private $paid;
  private $date;
  private $relationType;
  private $cfdiRelated;
  private $branchId;

  public function __construct()
  {
    $this->id             = 0;
    $this->issuerId       = 0;
    $this->customerId     = 0;
    $this->useCfdiId      = 0;
    $this->paymentFormId  = 0;
    $this->saleId         = 0;
    $this->serie          = "";
    $this->folio          = 0;
    $this->uuid           = "";
    $this->paymentMethod  = "";
    $this->currency       = "";
    $this->total          = 0;
    $this->comments       = "";
    $this->sent           = 0;
    $this->cancelled      = 0;
    $this->paid           = 0;
    $this->date           = date("Y-m-d");
    $this->relationType   = "";
    $this->cfdiRelated    = "";
    $this->branchId       = 0;
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getIssuerId()
  {
    return $this->issuerId;
  }

  public function getCustomerId()
  {
    return $this->customerId;
  }

  public function getUseCfdiId()
  {
    return $this->useCfdiId;
  }

  public function getPaymentFormId()
  {
    return $this->paymentFormId;
  }

  public function getSaleId()
  {
    return $this->saleId;
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

  public function getPaymentMethod()
  {
    return $this->paymentMethod;
  }

  public function getCurrency()
  {
    return $this->currency;
  }

  public function getTotal()
  {
    return $this->total;
  }

  public function getComments()
  {
    return $this->comments;
  }

  public function getSent()
  {
    return $this->sent;
  }

  public function getCancelled()
  {
    return $this->cancelled;
  }

  public function getPaid()
  {
    return $this->paid;
  }

  public function getDate()
  {
    return $this->date;
  }

  public function getRelationType()
  {
    return $this->relationType;
  }

  public function getCfdiRelated()
  {
    return $this->cfdiRelated;
  }

  public function getBranchId()
  {
    return $this->branchId;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setIssuerId($issuerId)
  {
    $this->issuerId = $issuerId;
  }

  public function setCustomerId($customerId)
  {
    $this->customerId = $customerId;
  }

  public function setUseCfdiId($useCfdiId)
  {
    $this->useCfdiId = $useCfdiId;
  }

  public function setPaymentFormId($paymentFormId)
  {
    $this->paymentFormId = $paymentFormId;
  }

  public function setSaleId($saleId)
  {
    $this->saleId = $saleId;
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

  public function setPaymentMethod($paymentMethod)
  {
    $this->paymentMethod = $paymentMethod;
  }

  public function setCurrency($currency)
  {
    $this->currency = $currency;
  }

  public function setTotal($total)
  {
    $this->total = $total;
  }

  public function setComments($comments)
  {
    $this->comments = $comments;
  }

  public function setSent($sent)
  {
    $this->sent = $sent;
  }

  public function setCancelled($cancelled)
  {
    $this->cancelled = $cancelled;
  }

  public function setPaid($paid)
  {
    $this->paid = $paid;
  }

  public function setDate($date)
  {
    $this->date = $date;
  }

  public function setRelationType($relationType)
  {
    $this->relationType = $relationType;
  }

  public function setCfdiRelated($cfdiRelated)
  {
    $this->cfdiRelated = $cfdiRelated;
  }

  public function setBranchId($branchId)
  {
    $this->branchId = $branchId;
  }

  /**
   * Another methods
   */
  public function from($data)
  {
    $this->setId($data["id_factura"]);
    $this->setIssuerId($data["id_emisor"]);
    $this->setCustomerId($data["id_cliente"]);
    $this->setUseCfdiId($data["id_uso_cfdi"]);
    $this->setPaymentFormId($data["id_forma_pago"]);
    $this->setSaleId($data["id_venta"]);
    $this->setSerie($data["serie"]);
    $this->setFolio($data["folio"]);
    $this->setUuid($data["uuid"]);
    $this->setPaymentMethod($data["metodo_pago"]);
    $this->setCurrency($data["moneda"]);
    $this->setTotal($data["total"]);
    $this->setComments($data["comentarios"]);
    $this->setSent($data["enviado"]);
    $this->setCancelled($data["cancelado"]);
    $this->setPaid($data["pagado"]);
    $this->setDate($data["fecha"]);
    $this->setRelationType($data["tipo_relacion"]);
    $this->setCfdiRelated($data["cfdi_relacionado"]);
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    try {
      $query = "INSERT INTO paal_facturas_anticipo_compra (
          id_emisor,
          id_cliente,
          id_uso_cfdi,
          id_forma_pago,
          id_venta,
          serie,
          folio,
          uuid,
          metodo_pago,
          moneda,
          total,
          comentarios,
          enviado,
          cancelado,
          pagado,
          fecha,
          tipo_relacion,
          cfdi_relacionado,
          id_sucursal
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
        "iiiiisssssssssssssi",
        $this->getIssuerId(),
        $this->getCustomerId(),
        $this->getUseCfdiId(),
        $this->getPaymentFormId(),
        $this->getSaleId(),
        $this->getSerie(),
        $this->getFolio(),
        $this->getUuid(),
        $this->getPaymentMethod(),
        $this->getCurrency(),
        $this->getTotal(),
        $this->getComments(),
        $this->getSent(),
        $this->getCancelled(),
        $this->getPaid(),
        $this->getDate(),
        $this->getRelationType(),
        $this->getCfdiRelated(),
        $this->getBranchId()
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
      $query = "UPDATE paal_facturas_anticipo_compra SET
          id_emisor         = ?,
          id_cliente        = ?,
          id_uso_cfdi       = ?,
          id_forma_pago     = ?,
          id_venta          = ?,
          serie             = ?,
          folio             = ?,
          uuid              = ?,
          metodo_pago       = ?,
          moneda            = ?,
          total             = ?,
          comentarios       = ?,
          enviado           = ?,
          cancelado         = ?,
          pagado            = ?,
          fecha             = ?,
          tipo_relacion     = ?,
          cfdi_relacionado  = ?
        WHERE
          id_factura = ?
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param(
        "iiiiisssssdsiiisssi",
        $this->getIssuerId(),
        $this->getCustomerId(),
        $this->getUseCfdiId(),
        $this->getPaymentFormId(),
        $this->getSaleId(),
        $this->getSerie(),
        $this->getFolio(),
        $this->getUuid(),
        $this->getPaymentMethod(),
        $this->getCurrency(),
        $this->getTotal(),
        $this->getComments(),
        $this->getSent(),
        $this->getCancelled(),
        $this->getPaid(),
        $this->getDate(),
        $this->getRelationType(),
        $this->getCfdiRelated(),
        $this->getId(),
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
