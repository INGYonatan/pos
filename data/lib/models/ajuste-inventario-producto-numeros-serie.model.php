<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_inventario_ajuste_producto_numeros_serie (
    id_inventario_ajuste_producto_numero_serie,
    id_inventario_ajuste_producto,
    id_inventario_ajuste,
    numero_serie,
    cancelado,
  );
*/

class AjusteInventarioProductoNumerosSerieModel
{
  private $table = "paal_inventario_ajuste_producto_numeros_serie";

  private int $id;
  private int $adjustmentProductId;
  private int $adjustmentId;
  private string $serialNumber;
  private string $cancelled;

  public function __construct()
  {
    $this->id                     = 0;
    $this->adjustmentProductId    = 0;
    $this->adjustmentId           = 0;
    $this->serialNumber           = "";
    $this->cancelled              = "no";
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getAdjustmentProductId(): int
  {
    return $this->adjustmentProductId;
  }

  public function getAdjustmentId(): int
  {
    return $this->adjustmentId;
  }

  public function getSerialNumber(): string
  {
    return $this->serialNumber;
  }

  public function getCancelled(): string
  {
    return $this->cancelled;
  }

  /**
   * Setters
   */
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function setAdjustmentProductId(int $adjustmentProductId): self
  {
    $this->adjustmentProductId = $adjustmentProductId;
    return $this;
  }

  public function setAdjustmentId(int $adjustmentId): self
  {
    $this->adjustmentId = $adjustmentId;
    return $this;
  }

  public function setSerialNumber(string $serialNumber): self
  {
    $this->serialNumber = $serialNumber;
    return $this;
  }

  public function setCancelled(string $cancelled): self
  {
    $this->cancelled = $cancelled;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_inventario_ajuste_producto_numero_serie"])) $this->setId((int)$data["id_inventario_ajuste_producto_numero_serie"]);
    if (isset($data["id_inventario_ajuste_producto"]))              $this->setAdjustmentProductId((int)$data["id_inventario_ajuste_producto"]);
    if (isset($data["id_inventario_ajuste"]))                       $this->setAdjustmentId((int)$data["id_inventario_ajuste"]);
    if (isset($data["numero_serie"]))                               $this->setSerialNumber($data["numero_serie"]);
    if (isset($data["cancelado"]))                                  $this->setCancelled($data["cancelado"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        id_inventario_ajuste_producto,
        id_inventario_ajuste,
        numero_serie,
        cancelado
      ) VALUES (
        ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $adjustmentProductId    = $this->getAdjustmentProductId();
      $adjustmentId           = $this->getAdjustmentId();
      $serialNumber           = $this->getSerialNumber();
      $cancelled              = $this->getCancelled();

      $stmt->bind_param(
        "iiss",
        $adjustmentProductId,
        $adjustmentId,
        $serialNumber,
        $cancelled
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Número de serie creado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTO_NUMEROS_SERIE_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear el número de serie";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page                   = $params["page"]        ?? 1;
    $perPage                = $params["perPage"]     ?? 10;
    $offset                 = ($page - 1) * $perPage;

    $adjustmentProductId    = $params["adjustmentProductId"] ?? null;
    $adjustmentProductId    = isset($adjustmentProductId) ? (int) $adjustmentProductId : null;

    $byAdjustmentProductId  = isset($adjustmentProductId) ? "(id_inventario_ajuste_producto = '{$adjustmentProductId}')" : "1=1";

    $adjustmentId           = $params["adjustmentId"] ?? null;
    $adjustmentId           = isset($adjustmentId) ? (int) $adjustmentId : null;

    $byAdjustmentId         = isset($adjustmentId) ? "(id_inventario_ajuste = '{$adjustmentId}')" : "1=1";

    $serialNumber           = $params["serialNumber"] ?? null;
    $serialNumber           = isset($serialNumber) ? mysqli_real_escape_string($mysqli, $serialNumber) : null;

    $bySerialNumber         = isset($serialNumber) ? "(numero_serie = '{$serialNumber}')" : "1=1";

    $cancelled              = $params["cancelled"] ?? null;
    $cancelled              = isset($cancelled) ? mysqli_real_escape_string($mysqli, $cancelled) : null;

    $byCancelled            = isset($cancelled) ? "(cancelado = '{$cancelled}')" : "1=1";

    $conditions = [
      $byAdjustmentProductId,
      $byAdjustmentId,
      $bySerialNumber,
      $byCancelled,
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
        $response->message  = "No se encontraron números de serie";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_inventario_ajuste_producto_numero_serie DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new AjusteInventarioProductoNumerosSerieModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Números de serie obtenidos exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTO_NUMEROS_SERIE_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de números de serie";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        id_inventario_ajuste_producto = ?,
        id_inventario_ajuste          = ?,
        numero_serie                  = ?,
        cancelado                     = ?
      WHERE
        id_inventario_ajuste_producto_numero_serie = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $adjustmentProductId    = $this->getAdjustmentProductId();
      $adjustmentId           = $this->getAdjustmentId();
      $serialNumber           = $this->getSerialNumber();
      $cancelled              = $this->getCancelled();
      $id                     = $this->getId();

      $stmt->bind_param(
        "iissi",
        $adjustmentProductId,
        $adjustmentId,
        $serialNumber,
        $cancelled,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Número de serie actualizado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTO_NUMEROS_SERIE_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar el número de serie";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_inventario_ajuste_producto_numero_serie = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Número de serie eliminado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTO_NUMEROS_SERIE_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar el número de serie";
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

    $query = "SELECT * FROM {$this->table} WHERE id_inventario_ajuste_producto_numero_serie = ?";

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
        $response->message  = "Número de serie obtenido exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTO_NUMEROS_SERIE_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el número de serie";
    }

    return $response;
  }

}
