<?php
require_once __DIR__ . "/modelresponse.model.php";

/* paal_solicitud_transferencias (
    id_solicitud_transferencia INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_sucursal_origen INT NOT NULL,
    id_sucursal_destino INT NOT NULL,
    folio VARCHAR(20) NOT NULL,
    notas TEXT,
    status ENUM (
      'pendiente',
      'aprobado',
      'completado',
      'rechazado'
    ) NOT NULL DEFAULT 'pendiente',
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES adm_usuarios (id_usuario),
    FOREIGN KEY (id_sucursal_origen) REFERENCES paal_sucursales (id_sucursal),
    FOREIGN KEY (id_sucursal_destino) REFERENCES paal_sucursales (id_sucursal)
  );
 */

class TransferRequestsModel
{
  private $table;

  private $id;
  private $userId;
  private $originBranchId;
  private $destinationBranchId;
  private $folio;
  private $notes;
  private $status;
  private $createdAt;
  private $updatedAt;

  public function __construct()
  {
    $this->table = "paal_solicitud_transferencias";

    $this->id                   = 0;
    $this->userId               = 0;
    $this->originBranchId       = 0;
    $this->destinationBranchId  = 0;
    $this->folio                = "";
    $this->notes                = "";
    $this->status               = "pendiente";
    $this->createdAt            = date("Y-m-d H:i:s");
    $this->updatedAt            = date("Y-m-d H:i:s");
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getUserId()
  {
    return $this->userId;
  }

  public function getOriginBranchId()
  {
    return $this->originBranchId;
  }

  public function getDestinationBranchId()
  {
    return $this->destinationBranchId;
  }

  public function getFolio()
  {
    return $this->folio;
  }

  public function getNotes()
  {
    return $this->notes;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }

  /**
   * Setters -
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setUserId($userId)
  {
    $this->userId = $userId;
  }

  public function setOriginBranchId($originBranchId)
  {
    $this->originBranchId = $originBranchId;
  }

  public function setDestinationBranchId($destinationBranchId)
  {
    $this->destinationBranchId = $destinationBranchId;
  }

  public function setFolio($folio)
  {
    $this->folio = $folio;
  }

  public function setNotes($notes)
  {
    $this->notes = $notes;
  }

  public function setStatus($status)
  {
    $this->status = $status;
  }

  public function setCreatedAt($createdAt)
  {
    $this->createdAt = $createdAt;
  }

  public function setUpdatedAt($updatedAt)
  {
    $this->updatedAt = $updatedAt;
  }

  public function from($data)
  {
    if (isset($data["id_solicitud_transferencia"])) $this->setId($data["id_solicitud_transferencia"]);
    if (isset($data["id_usuario"]))                 $this->setUserId($data["id_usuario"]);
    if (isset($data["id_sucursal_origen"]))         $this->setOriginBranchId($data["id_sucursal_origen"]);
    if (isset($data["id_sucursal_destino"]))        $this->setDestinationBranchId($data["id_sucursal_destino"]);
    if (isset($data["folio"]))                      $this->setFolio($data["folio"]);
    if (isset($data["notas"]))                      $this->setNotes($data["notas"]);
    if (isset($data["status"]))                     $this->setStatus($data["status"]);
    if (isset($data["creado_en"]))                  $this->setCreatedAt($data["creado_en"]);
    if (isset($data["actualizado_en"]))             $this->setUpdatedAt($data["actualizado_en"]);
  }

  public function toArray()
  {
    return [
      "uid"                         => $this->getId(),
      "id_solicitud_transferencia"  => $this->getId(),
      "id_usuario"                  => $this->getUserId(),
      "id_sucursal_origen"          => $this->getOriginBranchId(),
      "id_sucursal_destino"         => $this->getDestinationBranchId(),
      "folio"                       => $this->getFolio(),
      "notas"                       => $this->getNotes(),
      "status"                      => $this->getStatus(),
      "creado_en"                   => $this->getCreatedAt(),
      "actualizado_en"              => $this->getUpdatedAt()
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
        id_usuario,
        id_sucursal_origen,
        id_sucursal_destino,
        folio,
        notas,
        status,
        creado_en,
        actualizado_en
      ) VALUES (
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
      "iiisssss",
      $this->userId,
      $this->originBranchId,
      $this->destinationBranchId,
      $this->folio,
      $this->notes,
      $this->status,
      $this->createdAt,
      $this->updatedAt
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
      error_log("ERROR_TRANSFER_REQUESTS::CREATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function read($params = []): ModelResponse
  {
    global $mysqli;

    $response               = new ModelResponse();
    $response->data->rows   = [];

    $page                   = $params["page"]                 ?? 1;
    $perPage                = $params["perPage"]              ?? 15;

    $term                   = $params["term"]                 ? mysqli_real_escape_string($mysqli, $params["term"])                 : null;
    $userId                 = $params["userId"]               ? mysqli_real_escape_string($mysqli, $params["userId"])               : null;
    $originBranchId         = $params["originBranchId"]       ? mysqli_real_escape_string($mysqli, $params["originBranchId"])       : null;
    $destinationBranchId    = $params["destinationBranchId"]  ? mysqli_real_escape_string($mysqli, $params["destinationBranchId"])  : null;
    $folio                  = $params["folio"]                ? mysqli_real_escape_string($mysqli, $params["folio"])                : null;
    $status                 = $params["status"]               ? mysqli_real_escape_string($mysqli, $params["status"])               : null;
    $date                   = $params["date"]                 ? mysqli_real_escape_string($mysqli, $params["date"])                 : null;

    // Filtros de búsqueda
    $byTerm                 = isset($term)                ? "(folio LIKE _utf8 '%{$term}%' collate utf8_unicode_ci OR notas LIKE _utf8 '%{$term}%' collate utf8_unicode_ci)" : "1=1";
    $byUserId               = isset($userId)              ? "id_usuario = {$userId}"                                : "1=1";
    $byOriginBranchId       = isset($originBranchId)      ? "id_sucursal_origen = {$originBranchId}"                : "1=1";
    $byDestinationBranchId  = isset($destinationBranchId) ? "id_sucursal_destino = {$destinationBranchId}"          : "1=1";
    $byFolio                = isset($folio)               ? "folio LIKE _utf8 '%{$folio}%' collate utf8_unicode_ci" : "1=1";
    $byStatus               = isset($status)              ? "status = '{$status}'"                                  : "1=1";
    $byDate                 = isset($date)                ? "DATE(creado_en) = '{$date}'"                           : "1=1";

    // Cláusula FROM
    $cFrom = "FROM {$this->table}";

    // Cláusula JOIN
    $cJoin = "";

    // Cláusula WHERE
    $filters = [
      "({$byTerm})",
      "({$byUserId})",
      "({$byOriginBranchId})",
      "({$byDestinationBranchId})",
      "({$byFolio})",
      "({$byStatus})",
      "({$byDate})"
    ];

    $filtersStr = implode(" AND ", $filters);
    $cWhere     = "WHERE {$filtersStr}";

    // Cláusula ORDER BY
    $cOrderBy   = "ORDER BY id_solicitud_transferencia DESC";

    // Cláusula LIMIT
    $offset     = ($page - 1) * $perPage;
    $cLimit     = "LIMIT {$offset}, {$perPage}";

    // Consulta de datos
    $query = "SELECT COUNT(id_solicitud_transferencia) AS total {$cFrom} {$cJoin} {$cWhere}";

    try {
      $result = mysqli_query($mysqli, $query);
      $data   = mysqli_fetch_assoc($result);

      $total  = $data["total"] ?? 0;
      $pages  = ceil($total / $perPage);

      $response->data->total = $total;
      $response->data->pages = $pages;
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUESTS::READ_COUNT: {$e->getMessage()}");
      $total = 0;
    }

    if ($total == 0) return $response;

    $query = "SELECT * {$cFrom} {$cJoin} {$cWhere} {$cOrderBy} {$cLimit}";

    try {
      $result = mysqli_query($mysqli, $query);

      while ($data = mysqli_fetch_assoc($result)) {
        $item = new TransferRequestsModel();
        $item->from($data);

        $response->data->rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Registros obtenidos exitosamente.";
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUESTS::READ: {$e->getMessage()}");
      return $response;
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    if ($this->getId() == 0) {
      $response->message = "El ID de la solicitud de traspaso es requerido para actualizar.";
      return $response;
    }

    $query = "UPDATE {$this->table}
      SET
        id_usuario          = ?,
        id_sucursal_origen  = ?,
        id_sucursal_destino = ?,
        folio               = ?,
        notas               = ?,
        status              = ?,
        creado_en           = ?,
        actualizado_en      = ?
      WHERE
        id_solicitud_transferencia = ?
    ";

    $stmt = $mysqli->prepare($query);

    $stmt->bind_param(
      "iiisssssi",
      $this->getUserId(),
      $this->getOriginBranchId(),
      $this->getDestinationBranchId(),
      $this->getFolio(),
      $this->getNotes(),
      $this->getStatus(),
      $this->getCreatedAt(),
      $this->getUpdatedAt(),
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
      error_log("ERROR_TRANSFER_REQUESTS::UPDATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function deleteById($id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    if (!$id) {
      $response->message = "El ID de la solicitud de traspaso es requerido para eliminar.";
      return $response;
    }

    $query = "DELETE FROM {$this->table} WHERE id_solicitud_transferencia = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);

    try {
      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Registro eliminado exitosamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUESTS::DELETE: {$e->getMessage()}");
    }

    return $response;
  }

  public function getById($id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    if (!$id) {
      $response->message = "El ID de la solicitud de traspaso es requerido para obtener el registro.";
      return $response;
    }

    $query = "SELECT * FROM {$this->table} WHERE id_solicitud_transferencia = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);

    try {
      $stmt->execute();
      $result = $stmt->get_result();
      $data   = $result->fetch_assoc();

      if ($data) {
        $this->from($data);

        $response->status   = "success";
        $response->message  = "Registro obtenido exitosamente.";
        $response->data     = $this->toArray();
      } else {
        $response->message = "No se encontró una solicitud de traspaso con el ID proporcionado.";
      }
    } catch (Exception $e) {
      error_log("ERROR_TRANSFER_REQUESTS::GET_BY_ID: {$e->getMessage()}");
    }

    return $response;
  }
}
