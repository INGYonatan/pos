<?php
require_once __DIR__ . "/modelresponse.model.php";

/* paal_solicitud_transferencia_productos (
    id_solicitud_transferencia_producto INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_solicitud_transferencia INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad_solicitada INT NOT NULL,
    cantidad_atendida INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_solicitud_transferencia) REFERENCES paal_solicitud_transferencias (id_solicitud_transferencia),
    FOREIGN KEY (id_producto) REFERENCES productos (id_producto)
  );
 */

class TransferRequestProductsModel
{
  private $table;

  private $id;
  private $transferRequestId;
  private $productId;
  private $requestedQuantity;
  private $attendedQuantity;

  public function __construct()
  {
    $this->table = "paal_solicitud_transferencia_productos";

    $this->id                 = 0;
    $this->transferRequestId  = 0;
    $this->productId          = 0;
    $this->requestedQuantity  = 0;
    $this->attendedQuantity   = 0;
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getTransferRequestId()
  {
    return $this->transferRequestId;
  }

  public function getProductId()
  {
    return $this->productId;
  }

  public function getRequestedQuantity()
  {
    return $this->requestedQuantity;
  }

  public function getAttendedQuantity()
  {
    return $this->attendedQuantity;
  }

  /**
   * Setters -
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setTransferRequestId($transferRequestId)
  {
    $this->transferRequestId = $transferRequestId;
  }

  public function setProductId($productId)
  {
    $this->productId = $productId;
  }

  public function setRequestedQuantity($requestedQuantity)
  {
    $this->requestedQuantity = $requestedQuantity;
  }

  public function setAttendedQuantity($attendedQuantity)
  {
    $this->attendedQuantity = $attendedQuantity;
  }

  public function from($data)
  {
    if (isset($data["id_solicitud_transferencia_producto"])) $this->setId($data["id_solicitud_transferencia_producto"]);
    if (isset($data["id_solicitud_transferencia"]))         $this->setTransferRequestId($data["id_solicitud_transferencia"]);
    if (isset($data["id_producto"]))                        $this->setProductId($data["id_producto"]);
    if (isset($data["cantidad_solicitada"]))                $this->setRequestedQuantity($data["cantidad_solicitada"]);
    if (isset($data["cantidad_atendida"]))                  $this->setAttendedQuantity($data["cantidad_atendida"]);
  }

  public function toArray()
  {
    return [
      "uid"                                 => $this->getId(),
      "id_solicitud_transferencia_producto" => $this->getId(),
      "id_solicitud_transferencia"          => $this->getTransferRequestId(),
      "id_producto"                         => $this->getProductId(),
      "cantidad_solicitada"                 => $this->getRequestedQuantity(),
      "cantidad_atendida"                   => $this->getAttendedQuantity()
    ];
  }

  /**
   * Another methods
   */
  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        id_solicitud_transferencia,
        id_producto,
        cantidad_solicitada,
        cantidad_atendida
      ) VALUES (
        ?,
        ?,
        ?,
        ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    $stmt->bind_param(
      "iiii",
      $this->transferRequestId,
      $this->productId,
      $this->requestedQuantity,
      $this->attendedQuantity
    );

    try {
      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Registro creado exitosamente.";
        $response->data     = $this->toArray();
      }
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUEST_PRODUCTS::CREATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function read($params = []): ModelResponse
  {
    global $mysqli;

    $response               = new ModelResponse();
    $response->data->rows   = [];

    $page                   = $params["page"]               ?? 1;
    $perPage                = $params["perPage"]            ?? 15;

    $transferRequestId      = $params["transferRequestId"]  ? mysqli_real_escape_string($mysqli, $params["transferRequestId"])  : null;
    $productId              = $params["productId"]          ? mysqli_real_escape_string($mysqli, $params["productId"])          : null;
    $requestedQuantity      = $params["requestedQuantity"]  ? mysqli_real_escape_string($mysqli, $params["requestedQuantity"])  : null;
    $attendedQuantity       = $params["attendedQuantity"]   ? mysqli_real_escape_string($mysqli, $params["attendedQuantity"])   : null;

    // Filtros de búsqueda
    $byTransferRequestId    = isset($transferRequestId) ? "id_solicitud_transferencia = {$transferRequestId}" : "1=1";
    $byProductId            = isset($productId)         ? "id_producto = {$productId}"                       : "1=1";
    $byRequestedQuantity    = isset($requestedQuantity) ? "cantidad_solicitada = {$requestedQuantity}"       : "1=1";
    $byAttendedQuantity     = isset($attendedQuantity)  ? "cantidad_atendida = {$attendedQuantity}"          : "1=1";

    // Cláusula FROM
    $cFrom = "FROM {$this->table}";

    // Cláusula JOIN
    $cJoin = "";

    // Cláusula WHERE
    $filters = [
      "($byTransferRequestId)",
      "($byProductId)",
      "($byRequestedQuantity)",
      "($byAttendedQuantity)"
    ];

    $filtersStr = implode(" AND ", $filters);
    $cWhere     = "WHERE {$filtersStr}";

    // Cláusula ORDER BY
    $cOrderBy   = "ORDER BY id_solicitud_transferencia_producto DESC";

    // Cláusula LIMIT
    $offset     = ($page - 1) * $perPage;
    $cLimit     = "LIMIT {$offset}, {$perPage}";

    // Consulta de datos
    $query = "SELECT COUNT(id_solicitud_transferencia_producto) AS total {$cFrom} {$cJoin} {$cWhere}";

    try {
      $result = mysqli_query($mysqli, $query);
      $data   = mysqli_fetch_assoc($result);

      $total  = $data["total"] ?? 0;
      $pages  = ceil($total / $perPage);

      $response->data->total = $total;
      $response->data->pages = $pages;
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUEST_PRODUCTS::READ_COUNT: {$e->getMessage()}");
      $total = 0;
    }

    if ($total == 0) return $response;

    $query = "SELECT * {$cFrom} {$cJoin} {$cWhere} {$cOrderBy} {$cLimit}";

    try {
      $result = mysqli_query($mysqli, $query);

      while ($data = mysqli_fetch_assoc($result)) {
        $item = new TransferRequestProductsModel();
        $item->from($data);

        $response->data->rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Registros obtenidos exitosamente.";
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUEST_PRODUCTS::READ: {$e->getMessage()}");
      return $response;
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    if ($this->getId() == 0) {
      $response->message = "El ID del producto de la solicitud de traspaso es requerido para actualizar.";
      return $response;
    }

    $query = "UPDATE {$this->table}
      SET
        id_solicitud_transferencia = ?,
        id_producto                = ?,
        cantidad_solicitada        = ?,
        cantidad_atendida          = ?
      WHERE
        id_solicitud_transferencia_producto = ?
    ";

    $stmt = $mysqli->prepare($query);

    $stmt->bind_param(
      "iiiii",
      $this->getTransferRequestId(),
      $this->getProductId(),
      $this->getRequestedQuantity(),
      $this->getAttendedQuantity(),
      $this->getId()
    );

    try {
      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Registro actualizado exitosamente.";
        $response->data     = $this->toArray();
      }
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUEST_PRODUCTS::UPDATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function getByTransferRequestId($transferRequestId): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();
    $response->data->rows = [];

    $query = "SELECT * FROM {$this->table} WHERE id_solicitud_transferencia = ? ORDER BY id_solicitud_transferencia_producto ASC";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $transferRequestId);

    try {
      $stmt->execute();
      $result = $stmt->get_result();

      while ($data = mysqli_fetch_assoc($result)) {
        $item = new TransferRequestProductsModel();
        $item->from($data);

        $response->data->rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Registros obtenidos exitosamente.";
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUEST_PRODUCTS::GET_BY_TRANSFER_REQUEST_ID: {$e->getMessage()}");
    }

    return $response;
  }
}
