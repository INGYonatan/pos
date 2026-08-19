<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_inventario_ajustes (
    id_inventario_ajuste,
    id_usuario,
    id_sucursal,
    folio,
    observaciones,
    status,
    tipo,
    motivo_ajuste,
    fecha_creacion,
    tipo_ajuste,
  );
*/

class AjustesInventarioModel
{
  private $table = "paal_inventario_ajustes";

  private int $id;
  private int $userId;
  private int $sucursalId;
  private string $folio;
  private string $observations;
  private string $status;
  private string $type;
  private string $adjustmentReason;
  private string $createdAt;
  private string $adjustmentType;

  public function __construct()
  {
    $this->id                  = 0;
    $this->userId              = 0;
    $this->sucursalId          = 0;
    $this->folio               = "";
    $this->observations        = "";
    $this->status              = "activo";
    $this->type                = "";
    $this->adjustmentReason    = "";
    $this->createdAt           = date("Y-m-d H:i:s");
    $this->adjustmentType      = "ajuste";
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getUserId(): int
  {
    return $this->userId;
  }

  public function getSucursalId(): int
  {
    return $this->sucursalId;
  }

  public function getFolio(): string
  {
    return $this->folio;
  }

  public function getObservations(): string
  {
    return $this->observations;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getAdjustmentReason(): string
  {
    return $this->adjustmentReason;
  }

  public function getCreatedAt(): string
  {
    return $this->createdAt;
  }

  public function getAdjustmentType(): string
  {
    return $this->adjustmentType;
  }

  /**
   * Setters
   */
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function setUserId(int $userId): self
  {
    $this->userId = $userId;
    return $this;
  }

  public function setSucursalId(int $sucursalId): self
  {
    $this->sucursalId = $sucursalId;
    return $this;
  }

  public function setFolio(string $folio): self
  {
    $this->folio = $folio;
    return $this;
  }

  public function setObservations(string $observations): self
  {
    $this->observations = $observations;
    return $this;
  }

  public function setStatus(string $status): self
  {
    $this->status = $status;
    return $this;
  }

  public function setType(string $type): self
  {
    $this->type = $type;
    return $this;
  }

  public function setAdjustmentReason(string $adjustmentReason): self
  {
    $this->adjustmentReason = $adjustmentReason;
    return $this;
  }

  public function setCreatedAt(string $createdAt): self
  {
    $this->createdAt = $createdAt;
    return $this;
  }

  public function setAdjustmentType(string $adjustmentType): self
  {
    $this->adjustmentType = $adjustmentType;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_inventario_ajuste"])) $this->setId((int)$data["id_inventario_ajuste"]);
    if (isset($data["id_usuario"]))           $this->setUserId((int)$data["id_usuario"]);
    if (isset($data["id_sucursal"]))          $this->setSucursalId((int)$data["id_sucursal"]);
    if (isset($data["folio"]))                $this->setFolio($data["folio"]);
    if (isset($data["observaciones"]))        $this->setObservations($data["observaciones"]);
    if (isset($data["status"]))               $this->setStatus($data["status"]);
    if (isset($data["tipo"]))                 $this->setType($data["tipo"]);
    if (isset($data["motivo_ajuste"]))        $this->setAdjustmentReason($data["motivo_ajuste"]);
    if (isset($data["fecha_creacion"]))       $this->setCreatedAt($data["fecha_creacion"]);
    if (isset($data["tipo_ajuste"]))          $this->setAdjustmentType($data["tipo_ajuste"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        id_usuario,
        id_sucursal,
        folio,
        observaciones,
        status,
        tipo,
        motivo_ajuste,
        fecha_creacion,
        tipo_ajuste
      ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $userId              = $this->getUserId();
      $sucursalId          = $this->getSucursalId();
      $folio               = $this->getFolio();
      $observations        = $this->getObservations();
      $status              = $this->getStatus();
      $type                = $this->getType();
      $adjustmentReason    = $this->getAdjustmentReason();
      $createdAt           = $this->getCreatedAt();
      $adjustmentType      = $this->getAdjustmentType();

      $stmt->bind_param(
        "iisssssss",
        $userId,
        $sucursalId,
        $folio,
        $observations,
        $status,
        $type,
        $adjustmentReason,
        $createdAt,
        $adjustmentType
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Ajuste de inventario creado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTES_INVENTARIO_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear el ajuste de inventario";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page                = $params["page"]        ?? 1;
    $perPage             = $params["perPage"]     ?? 10;
    $offset              = ($page - 1) * $perPage;

    $userId              = $params["userId"] ?? null;
    $userId              = isset($userId) ? (int) $userId : null;

    $byUserId            = isset($userId) ? "(id_usuario = '{$userId}')" : "1=1";

    $sucursalId          = $params["sucursalId"] ?? null;
    $sucursalId          = isset($sucursalId) ? (int) $sucursalId : null;

    $bySucursalId        = isset($sucursalId) ? "(id_sucursal = '{$sucursalId}')" : "1=1";

    $folio               = $params["folio"] ?? null;
    $folio               = isset($folio) ? mysqli_real_escape_string($mysqli, $folio) : null;

    $byFolio             = isset($folio) ? "(folio = '{$folio}')" : "1=1";

    $observations        = $params["observations"] ?? null;
    $observations        = isset($observations) ? mysqli_real_escape_string($mysqli, $observations) : null;

    $byObservations      = isset($observations) ? "(observaciones = '{$observations}')" : "1=1";

    $status              = $params["status"] ?? null;
    $status              = isset($status) ? mysqli_real_escape_string($mysqli, $status) : null;

    $byStatus            = isset($status) ? "(status = '{$status}')" : "1=1";

    $type                = $params["type"] ?? null;
    $type                = isset($type) ? mysqli_real_escape_string($mysqli, $type) : null;

    $byType              = isset($type) ? "(tipo = '{$type}')" : "1=1";

    $adjustmentReason    = $params["adjustmentReason"] ?? null;
    $adjustmentReason    = isset($adjustmentReason) ? mysqli_real_escape_string($mysqli, $adjustmentReason) : null;

    $byAdjustmentReason  = isset($adjustmentReason) ? "(motivo_ajuste = '{$adjustmentReason}')" : "1=1";

    $createdAt           = $params["createdAt"] ?? null;
    $createdAt           = isset($createdAt) ? mysqli_real_escape_string($mysqli, $createdAt) : null;

    $byCreatedAt         = isset($createdAt) ? "(fecha_creacion = '{$createdAt}')" : "1=1";

    $adjustmentType      = $params["adjustmentType"] ?? null;
    $adjustmentType      = isset($adjustmentType) ? mysqli_real_escape_string($mysqli, $adjustmentType) : null;

    $byAdjustmentType    = isset($adjustmentType) ? "(tipo_ajuste = '{$adjustmentType}')" : "1=1";

    $conditions = [
      $byUserId,
      $bySucursalId,
      $byFolio,
      $byObservations,
      $byStatus,
      $byType,
      $byAdjustmentReason,
      $byCreatedAt,
      $byAdjustmentType,
    ];

    $conditions = implode(" AND ", $conditions);

    // Clausulas
    $cFrom  = "FROM {$this->table}";
    $cWhere = "WHERE {$conditions}";
    $cLimit = "LIMIT {$offset}, {$perPage}";

    // Query total
    try {
      $query  = "SELECT COUNT(*) AS total {$cFrom} {$cWhere}";
      $result = mysqli_query($mysqli, $query);
      $data   = mysqli_fetch_assoc($result);
      $total  = $data["total"] ?? 0;

      $pages = ceil($total / $perPage);

      if ($pages == 0) {
        $response->status   = "success";
        $response->message  = "No se encontraron ajustes de inventario";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_inventario_ajuste DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new AjustesInventarioModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Ajustes de inventario obtenidos exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_AJUSTES_INVENTARIO_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de ajustes de inventario";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        id_usuario     = ?,
        id_sucursal    = ?,
        folio          = ?,
        observaciones  = ?,
        status         = ?,
        tipo           = ?,
        motivo_ajuste  = ?,
        fecha_creacion = ?,
        tipo_ajuste    = ?
      WHERE
        id_inventario_ajuste = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $userId              = $this->getUserId();
      $sucursalId          = $this->getSucursalId();
      $folio               = $this->getFolio();
      $observations        = $this->getObservations();
      $status              = $this->getStatus();
      $type                = $this->getType();
      $adjustmentReason    = $this->getAdjustmentReason();
      $createdAt           = $this->getCreatedAt();
      $adjustmentType      = $this->getAdjustmentType();
      $id                  = $this->getId();

      $stmt->bind_param(
        "iisssssssi",
        $userId,
        $sucursalId,
        $folio,
        $observations,
        $status,
        $type,
        $adjustmentReason,
        $createdAt,
        $adjustmentType,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Ajuste de inventario actualizado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTES_INVENTARIO_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar el ajuste de inventario";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_inventario_ajuste = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Ajuste de inventario eliminado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTES_INVENTARIO_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar el ajuste de inventario";
    }

    return $response;
  }

  public function delete(): ModelResponse
  {
    return $this->deleteById($this->getId());
  }

  public function getById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "SELECT * FROM {$this->table} WHERE id_inventario_ajuste = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows > 0) {
        $data = $result->fetch_assoc();
        $this->from($data);

        $response->status   = "success";
        $response->message  = "Ajuste de inventario obtenido exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTES_INVENTARIO_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el ajuste de inventario";
    }

    return $response;
  }

}
