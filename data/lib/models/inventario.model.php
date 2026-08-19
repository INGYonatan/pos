<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_inventario (
    id_inventario,
    id_sucursal,
    id_producto,
    stock,
  );
*/

class InventarioModel
{
  private $table = "paal_inventario";

  private int $id;
  private int $sucursalId;
  private int $productId;
  private float $stock;

  public function __construct()
  {
    $this->id            = 0;
    $this->sucursalId    = 0;
    $this->productId     = 0;
    $this->stock         = 0;
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getSucursalId(): int
  {
    return $this->sucursalId;
  }

  public function getProductId(): int
  {
    return $this->productId;
  }

  public function getStock(): float
  {
    return $this->stock;
  }

  /**
   * Setters
   */
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function setSucursalId(int $sucursalId): self
  {
    $this->sucursalId = $sucursalId;
    return $this;
  }

  public function setProductId(int $productId): self
  {
    $this->productId = $productId;
    return $this;
  }

  public function setStock(float $stock): self
  {
    $this->stock = $stock;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_inventario"])) $this->setId((int)$data["id_inventario"]);
    if (isset($data["id_sucursal"]))   $this->setSucursalId((int)$data["id_sucursal"]);
    if (isset($data["id_producto"]))   $this->setProductId((int)$data["id_producto"]);
    if (isset($data["stock"]))         $this->setStock((float)$data["stock"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        id_sucursal,
        id_producto,
        stock
      ) VALUES (
        ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $sucursalId    = $this->getSucursalId();
      $productId     = $this->getProductId();
      $stock         = $this->getStock();

      $stmt->bind_param(
        "iid",
        $sucursalId,
        $productId,
        $stock
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Inventario creado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_INVENTARIO_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear el inventario";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page          = $params["page"]        ?? 1;
    $perPage       = $params["perPage"]     ?? 10;
    $offset        = ($page - 1) * $perPage;

    $sucursalId    = $params["sucursalId"] ?? null;
    $sucursalId    = isset($sucursalId) ? (int) $sucursalId : null;

    $bySucursalId  = isset($sucursalId) ? "(id_sucursal = '{$sucursalId}')" : "1=1";

    $productId     = $params["productId"] ?? null;
    $productId     = isset($productId) ? (int) $productId : null;

    $byProductId   = isset($productId) ? "(id_producto = '{$productId}')" : "1=1";

    $stock         = $params["stock"] ?? null;
    $stock         = isset($stock) ? mysqli_real_escape_string($mysqli, $stock) : null;

    $byStock       = isset($stock) ? "(stock = '{$stock}')" : "1=1";

    $conditions = [
      $bySucursalId,
      $byProductId,
      $byStock,
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
        $response->message  = "No se encontraron inventarios";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_inventario DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new InventarioModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Inventarios obtenidos exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_INVENTARIO_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de inventarios";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        id_sucursal = ?,
        id_producto = ?,
        stock       = ?
      WHERE
        id_inventario = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $sucursalId    = $this->getSucursalId();
      $productId     = $this->getProductId();
      $stock         = $this->getStock();
      $id            = $this->getId();

      $stmt->bind_param(
        "iidi",
        $sucursalId,
        $productId,
        $stock,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Inventario actualizado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_INVENTARIO_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar el inventario";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_inventario = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Inventario eliminado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_INVENTARIO_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar el inventario";
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

    $query = "SELECT * FROM {$this->table} WHERE id_inventario = ?";

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
        $response->message  = "Inventario obtenido exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_INVENTARIO_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el inventario";
    }

    return $response;
  }

  public function getBySucursalIdAndProductId(int $sucursalId, int $productId): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "SELECT * FROM {$this->table} WHERE id_sucursal = ? AND id_producto = ? LIMIT 1";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("ii", $sucursalId, $productId);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows > 0) {
        $data = $result->fetch_assoc();
        $this->from($data);

        $response->status   = "success";
        $response->message  = "Inventario obtenido exitosamente";
      } else {
        $response->status   = "error";
        $response->message  = "Inventario no encontrado";
      }
    } catch (Exception $e) {
      error_log("ERROR_INVENTARIO_MODEL::GET_BY_SUCURSAL_ID_AND_PRODUCT_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el inventario";
    }

    return $response;
  }

}
