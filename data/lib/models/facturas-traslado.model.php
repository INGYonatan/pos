<?php
require_once __DIR__ . "/modelresponse.model.php";

/* 
id_factura
id_emisor
id_receptor
id_uso_cfdi
id_inventario_transferencia
serie
folio
uuid
moneda
subtotal
exportacion
total
comentarios
enviado
cancelado
pagado
fecha
tipo_relacion
cfdi_relacionado
id_sucursal
*/

class FacturasTrasladoModel
{
  private $id;
  private $issuerId;
  private $receiverId;
  private $useCfdiId;
  private $inventoryTransferId;
  private $serie;
  private $folio;
  private $uuid;
  private $currency;
  private $subtotal;
  private $export;
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
    $this->receiverId     = 0;
    $this->useCfdiId      = 0;
    $this->inventoryTransferId  = 0;
    $this->serie          = "";
    $this->folio          = 0;
    $this->uuid           = "";
    $this->currency       = "XXX";
    $this->subtotal       = 0;
    $this->export         = "02";
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

  public function getReceiverId()
  {
    return $this->receiverId;
  }

  public function getUseCfdiId()
  {
    return $this->useCfdiId;
  }

  public function getInventoryTransferId()
  {
    return $this->inventoryTransferId;
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

  public function getCurrency()
  {
    return $this->currency;
  }

  public function getSubtotal()
  {
    return $this->subtotal;
  }

  public function getExport()
  {
    return $this->export;
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
    $this->id             = $id;
  }

  public function setIssuerId($issuerId)
  {
    $this->issuerId       = $issuerId;
  }

  public function setReceiverId($receiverId)
  {
    $this->receiverId     = $receiverId;
  }

  public function setUseCfdiId($useCfdiId)
  {
    $this->useCfdiId      = $useCfdiId;
  }

  public function setInventoryTransferId($inventoryTransferId)
  {
    $this->inventoryTransferId  = $inventoryTransferId;
  }

  public function setSerie($serie)
  {
    $this->serie          = $serie;
  }

  public function setFolio($folio)
  {
    $this->folio          = $folio;
  }

  public function setUuid($uuid)
  {
    $this->uuid           = $uuid;
  }

  public function setCurrency($currency)
  {
    $this->currency       = $currency;
  }

  public function setSubtotal($subtotal)
  {
    $this->subtotal       = $subtotal;
  }

  public function setExport($export)
  {
    $this->export         = $export;
  }

  public function setTotal($total)
  {
    $this->total          = $total;
  }

  public function setComments($comments)
  {
    $this->comments       = $comments;
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
    $this->paid           = $paid;
  }

  public function setDate($date)
  {
    $this->date = $date;
  }

  public function setRelationType($relationType)
  {
    $this->relationType   = $relationType;
  }

  public function setCfdiRelated($cfdiRelated)
  {
    $this->cfdiRelated    = $cfdiRelated;
  }

  public function setBranchId($branchId)
  {
    $this->branchId       = $branchId;
  }

  /**
   * Another methods
   */
  public function from($data)
  {
    $this->setId($data["id_factura"]);
    $this->setIssuerId($data["id_emisor"]);
    $this->setReceiverId($data["id_receptor"]);
    $this->setUseCfdiId($data["id_uso_cfdi"]);
    $this->setInventoryTransferId($data["id_inventario_transferencia"]);
    $this->setSerie($data["serie"]);
    $this->setFolio($data["folio"]);
    $this->setUuid($data["uuid"]);
    $this->setCurrency($data["moneda"]);
    $this->setSubtotal($data["subtotal"]);
    $this->setExport($data["exportacion"]);
    $this->setTotal($data["total"]);
    $this->setComments($data["comentarios"]);
    $this->setSent($data["enviado"]);
    $this->setCancelled($data["cancelado"]);
    $this->setPaid($data["pagado"]);
    $this->setDate($data["fecha"]);
    $this->setRelationType($data["tipo_relacion"]);
    $this->setCfdiRelated($data["cfdi_relacionado"]);
    $this->setBranchId($data["id_sucursal"]);
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    try {
      $query = "INSERT INTO paal_facturas_traslado (
          id_emisor,
          id_receptor,
          id_uso_cfdi,
          id_inventario_transferencia,
          serie,
          folio,
          uuid,
          moneda,
          subtotal,
          exportacion,
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
        "iiiissssssssssssssi",
        $this->getIssuerId(),
        $this->getReceiverId(),
        $this->getUseCfdiId(),
        $this->getInventoryTransferId(),
        $this->getSerie(),
        $this->getFolio(),
        $this->getUuid(),
        $this->getCurrency(),
        $this->getSubtotal(),
        $this->getExport(),
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
      $query = "UPDATE paal_facturas_traslado SET
          id_emisor         = ?,
          id_receptor       = ?,
          id_uso_cfdi       = ?,
          id_inventario_transferencia = ?,
          serie             = ?,
          folio             = ?,
          uuid              = ?,
          moneda            = ?,
          subtotal          = ?,
          exportacion       = ?,
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
        "iiiissssdsdsiiisssi",
        $this->getIssuerId(),
        $this->getReceiverId(),
        $this->getUseCfdiId(),
        $this->getInventoryTransferId(),
        $this->getSerie(),
        $this->getFolio(),
        $this->getUuid(),
        $this->getCurrency(),
        $this->getSubtotal(),
        $this->getExport(),
        $this->getTotal(),
        $this->getComments(),
        $this->getSent(),
        $this->getCancelled(),
        $this->getPaid(),
        $this->getDate(),
        $this->getRelationType(),
        $this->getCfdiRelated(),
        $this->getId()
      );

      $result = $stmt->execute();

      if ($result) :
        $response->status   = "success";
        $response->message  = "La factura se actualizó correctamente";
        $response->data     = $this;
      endif;
    } catch (Exception $e) {
      error_log("FacturasTrasladoModel->update: {$e->getMessage()}");
    }

    return $response;
  }
}
