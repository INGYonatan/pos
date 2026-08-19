<?php
require_once __DIR__ . "/helperresponse.model.php";

class SerialNumbersHelper
{
  private $table = DTI . "_producto_numeros_serie";

  private $id;
  private $productId;
  private $purchaseInvoice;
  private $saleInvoice;
  private $serialNumber;
  private $status;
  private $createdAt;
  private $branchId;

  public function __construct()
  {
    $this->id              = 0;
    $this->productId       = 0;
    $this->purchaseInvoice = null;
    $this->saleInvoice     = null;
    $this->serialNumber    = "";
    $this->status          = "disponible";
    $this->createdAt       = date("Y-m-d H:i:s");
    $this->branchId        = 0;
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getProductId()
  {
    return $this->productId;
  }

  public function getPurchaseInvoice()
  {
    return $this->purchaseInvoice;
  }

  public function getSaleInvoice()
  {
    return $this->saleInvoice;
  }

  public function getSerialNumber()
  {
    return $this->serialNumber;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function getCreatedAt()
  {
    return $this->createdAt;
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

  public function setProductId($productId)
  {
    $this->productId = $productId;
  }

  public function setPurchaseInvoice($purchaseInvoice)
  {
    $this->purchaseInvoice = $purchaseInvoice;
  }

  public function setSaleInvoice($saleInvoice)
  {
    $this->saleInvoice = $saleInvoice;
  }

  public function setSerialNumber($serialNumber)
  {
    $this->serialNumber = $serialNumber;
  }

  public function setStatus($status)
  {
    $this->status = $status;
  }

  public function setCreatedAt($createdAt)
  {
    $this->createdAt = $createdAt;
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
    if (isset($data["id_producto_numero_serie"])) $this->setId($data["id_producto_numero_serie"]);
    if (isset($data["id_producto"]))              $this->setProductId($data["id_producto"]);
    if (isset($data["folio_compra"]))             $this->setPurchaseInvoice($data["folio_compra"]);
    if (isset($data["folio_venta"]))              $this->setSaleInvoice($data["folio_venta"]);
    if (isset($data["numero_serie"]))             $this->setSerialNumber($data["numero_serie"]);
    if (isset($data["status"]))                   $this->setStatus($data["status"]);
    if (isset($data["fecha_creacion"]))           $this->setCreatedAt($data["fecha_creacion"]);
    if (isset($data["id_sucursal"]))              $this->setBranchId($data["id_sucursal"]);
  }

  public function toArray()
  {
    return [
      'uid'             => $this->getId(),
      'productId'       => $this->getProductId(),
      'purchaseInvoice' => $this->getPurchaseInvoice(),
      'saleInvoice'     => $this->getSaleInvoice(),
      'serialNumber'    => $this->getSerialNumber(),
      'status'          => $this->getStatus(),
      'createdAt'       => $this->getCreatedAt(),
      'branchId'        => $this->getBranchId()
    ];
  }

  public function create(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $productId       = $this->getProductId();
    $purchaseInvoice = $this->getPurchaseInvoice();
    $saleInvoice     = $this->getSaleInvoice();
    $serialNumber    = $this->getSerialNumber();
    $status          = $this->getStatus();
    $createdAt       = $this->getCreatedAt();
    $branchId        = $this->getBranchId();

    $query  = "INSERT INTO {$this->table} (
        id_producto,
        folio_compra,
        folio_venta,
        numero_serie,
        status,
        fecha_creacion,
        id_sucursal
      ) VALUES (
        ?, ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("isssssi", $productId, $purchaseInvoice, $saleInvoice, $serialNumber, $status, $createdAt, $branchId);
      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status  = "success";
        $response->message = "Registro creado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_SERIAL_NUMBERS_HELPER_CREATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function read($params = []): HelperResponseModel
  {
    global $mysqli;
    global $db_dti;

    $response = new HelperResponseModel();

    try {
      $page         = $params['page']    ?? 1;
      $perPage      = $params['perPage'] ?? 20;
      $offset       = ($page - 1) * $perPage;

      $term         = $params["term"] ?? null;
      $productId    = $params["productId"] ?? null;
      $status       = $params["status"] ?? null;
      $branchId     = $params["branchId"] ?? null;
      $productCode  = $params["productCode"] ?? null;

      $byTerm      = $term ? "
        (PSN.numero_serie LIKE '%{$term}%') OR
        (P.codigo LIKE '%{$term}%') OR
        (P.nombre_producto LIKE _utf8 '%{$term}%' collate utf8_unicode_ci)
      " : "1=1";
      $byProductId = $productId !== null ? "PSN.id_producto = {$productId}" : "1=1";
      $byStatus    = $status ? "PSN.status = '{$status}'" : "1=1";
      $byBranchId  = $branchId !== null ? "PSN.id_sucursal = {$branchId}" : "1=1";
      $byProductCode = $productCode ? "P.codigo = '{$productCode}'" : "1=1";

      $cFrom    = "FROM {$this->table} AS PSN";

      $cJoin = "LEFT JOIN {$db_dti}_productos AS P ON PSN.id_producto = P.id_producto";

      $cWhere   = "WHERE
          ({$byTerm})       AND
          ({$byProductId})  AND
          ({$byStatus})     AND
          ({$byBranchId})   AND
          ({$byProductCode})
      ";

      $query    = "SELECT COUNT(PSN.id_producto_numero_serie) AS total {$cFrom} {$cJoin} {$cWhere}";

      $stmt = $mysqli->prepare($query);
      $stmt->execute();

      $result = $stmt->get_result();
      $row    = $result->fetch_assoc();
      $total  = $row["total"];
      $numPages = ceil($total / $perPage);

      $response->data["numPages"] = $numPages;

      if ($total == 0) {
        $response->status  = "success";
        $response->message = "No hay registros disponibles";
        $response->data["total"] = $total;
        $response->data["rows"]  = [];
      }

      if ($total > 0) {
        $query = "SELECT * {$cFrom}
          {$cJoin}
          {$cWhere}
          ORDER BY
            PSN.id_producto_numero_serie
          DESC
          LIMIT {$offset}, {$perPage}
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->execute();

        $result  = $stmt->get_result();
        $numRows = $result->num_rows;

        if ($numRows) {
          $rows = [];

          while ($row = $result->fetch_assoc()) {
            $item = new SerialNumbersHelper();
            $item->from($row);
            $rows[] = $item;
          }

          $response->status  = "success";
          $response->message = "Registros encontrados";
          $response->data["total"] = $total;
          $response->data["rows"]  = $rows;
        }
      }
    } catch (Exception $e) {
      error_log("ERROR_SERIAL_NUMBERS_HELPER_READ: " . $e->getMessage());
    }

    return $response;
  }

  public function update(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id              = $this->getId();
    $productId       = $this->getProductId();
    $purchaseInvoice = $this->getPurchaseInvoice();
    $saleInvoice     = $this->getSaleInvoice();
    $serialNumber    = $this->getSerialNumber();
    $status          = $this->getStatus();
    $branchId        = $this->getBranchId();

    $query  = "UPDATE
        {$this->table}
      SET
        id_producto  = ?,
        folio_compra = ?,
        folio_venta  = ?,
        numero_serie = ?,
        status       = ?,
        id_sucursal  = ?
      WHERE
        id_producto_numero_serie = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param(
        "issssii",
        $productId,
        $purchaseInvoice,
        $saleInvoice,
        $serialNumber,
        $status,
        $branchId,
        $id
      );
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Registro actualizado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_SERIAL_NUMBERS_HELPER_UPDATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function delete(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id = $this->getId();

    $query  = "DELETE FROM {$this->table} WHERE id_producto_numero_serie = ?";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Registro eliminado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_SERIAL_NUMBERS_HELPER_DELETE: {$e->getMessage()}");
    }

    return $response;
  }

  public function getById($id): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $query  = "SELECT * FROM {$this->table} WHERE id_producto_numero_serie = ? LIMIT 1";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $stmt->execute();

      $result  = $stmt->get_result();
      $numRows = $result->num_rows;

      if ($numRows) {
        $row = $result->fetch_assoc();

        $this->from($row);

        $response->status  = "success";
        $response->message = "Registro encontrado.";
        $response->data    = $this;
      } else {
        $response->status  = "error";
        $response->message = "No se encontró el registro.";
      }
    } catch (Exception $e) {
      error_log("ERROR_SERIAL_NUMBERS_HELPER_GETBYID: {$e->getMessage()}");
    }

    return $response;
  }

  public function getAll($params = []): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();
    $response->data["rows"] = [];

    $sortBy    = $params['sortBy']    ?? 'numero_serie';
    $sortOrder = $params['sortOrder'] ?? 'ASC';

    $query  = "SELECT * FROM {$this->table} ORDER BY {$sortBy} {$sortOrder}";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->execute();

      $result  = $stmt->get_result();
      $numRows = $result->num_rows;

      if ($numRows) {
        $rows = [];

        while ($row = $result->fetch_assoc()) {
          $item = new SerialNumbersHelper();
          $item->from($row);
          $rows[] = $item;
        }

        $response->status       = "success";
        $response->message      = "Registros encontrados.";
        $response->data["rows"] = $rows;
      } else {
        $response->status  = "error";
        $response->message = "No se encontraron registros.";
      }
    } catch (Exception $e) {
      error_log("ERROR_SERIAL_NUMBERS_HELPER_GETALL: {$e->getMessage()}");
    }

    return $response;
  }
}
