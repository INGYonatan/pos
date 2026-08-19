<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_inventario_ajuste_productos (
    id_inventario_ajuste_producto,
    id_inventario_ajuste,
    id_producto,
    id_tipo,
    tipo,
    cantidad,
    cancelado,
  );
*/

class AjusteInventarioProductosModel
{
  private $table = "paal_inventario_ajuste_productos";

  private int $id;
  private int $adjustmentId;
  private int $productId;
  private int $typeId;
  private string $type;
  private float $quantity;
  private string $cancelled;

  public function __construct()
  {
    $this->id              = 0;
    $this->adjustmentId    = 0;
    $this->productId       = 0;
    $this->typeId          = 0;
    $this->type            = "";
    $this->quantity        = 0;
    $this->cancelled       = "no";
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getAdjustmentId(): int
  {
    return $this->adjustmentId;
  }

  public function getProductId(): int
  {
    return $this->productId;
  }

  public function getTypeId(): int
  {
    return $this->typeId;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getQuantity(): float
  {
    return $this->quantity;
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

  public function setAdjustmentId(int $adjustmentId): self
  {
    $this->adjustmentId = $adjustmentId;
    return $this;
  }

  public function setProductId(int $productId): self
  {
    $this->productId = $productId;
    return $this;
  }

  public function setTypeId(int $typeId): self
  {
    $this->typeId = $typeId;
    return $this;
  }

  public function setType(string $type): self
  {
    $this->type = $type;
    return $this;
  }

  public function setQuantity(float $quantity): self
  {
    $this->quantity = $quantity;
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
    if (isset($data["id_inventario_ajuste_producto"])) $this->setId((int)$data["id_inventario_ajuste_producto"]);
    if (isset($data["id_inventario_ajuste"]))          $this->setAdjustmentId((int)$data["id_inventario_ajuste"]);
    if (isset($data["id_producto"]))                   $this->setProductId((int)$data["id_producto"]);
    if (isset($data["id_tipo"]))                       $this->setTypeId((int)$data["id_tipo"]);
    if (isset($data["tipo"]))                          $this->setType($data["tipo"]);
    if (isset($data["cantidad"]))                      $this->setQuantity((float)$data["cantidad"]);
    if (isset($data["cancelado"]))                     $this->setCancelled($data["cancelado"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        id_inventario_ajuste,
        id_producto,
        id_tipo,
        tipo,
        cantidad,
        cancelado
      ) VALUES (
        ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $adjustmentId    = $this->getAdjustmentId();
      $productId       = $this->getProductId();
      $typeId          = $this->getTypeId();
      $type            = $this->getType();
      $quantity        = $this->getQuantity();
      $cancelled       = $this->getCancelled();

      $stmt->bind_param(
        "iiisds",
        $adjustmentId,
        $productId,
        $typeId,
        $type,
        $quantity,
        $cancelled
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Producto del ajuste creado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTOS_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear el producto del ajuste";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page            = $params["page"]        ?? 1;
    $perPage         = $params["perPage"]     ?? 10;
    $offset          = ($page - 1) * $perPage;

    $adjustmentId    = $params["adjustmentId"] ?? null;
    $adjustmentId    = isset($adjustmentId) ? (int) $adjustmentId : null;

    $byAdjustmentId  = isset($adjustmentId) ? "(id_inventario_ajuste = '{$adjustmentId}')" : "1=1";

    $productId       = $params["productId"] ?? null;
    $productId       = isset($productId) ? (int) $productId : null;

    $byProductId     = isset($productId) ? "(id_producto = '{$productId}')" : "1=1";

    $typeId          = $params["typeId"] ?? null;
    $typeId          = isset($typeId) ? (int) $typeId : null;

    $byTypeId        = isset($typeId) ? "(id_tipo = '{$typeId}')" : "1=1";

    $type            = $params["type"] ?? null;
    $type            = isset($type) ? mysqli_real_escape_string($mysqli, $type) : null;

    $byType          = isset($type) ? "(tipo = '{$type}')" : "1=1";

    $quantity        = $params["quantity"] ?? null;
    $quantity        = isset($quantity) ? mysqli_real_escape_string($mysqli, $quantity) : null;

    $byQuantity      = isset($quantity) ? "(cantidad = '{$quantity}')" : "1=1";

    $cancelled       = $params["cancelled"] ?? null;
    $cancelled       = isset($cancelled) ? mysqli_real_escape_string($mysqli, $cancelled) : null;

    $byCancelled     = isset($cancelled) ? "(cancelado = '{$cancelled}')" : "1=1";

    $conditions = [
      $byAdjustmentId,
      $byProductId,
      $byTypeId,
      $byType,
      $byQuantity,
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
        $response->message  = "No se encontraron productos del ajuste";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_inventario_ajuste_producto DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new AjusteInventarioProductosModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Productos del ajuste obtenidos exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTOS_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de productos del ajuste";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        id_inventario_ajuste = ?,
        id_producto          = ?,
        id_tipo              = ?,
        tipo                 = ?,
        cantidad             = ?,
        cancelado            = ?
      WHERE
        id_inventario_ajuste_producto = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $adjustmentId    = $this->getAdjustmentId();
      $productId       = $this->getProductId();
      $typeId          = $this->getTypeId();
      $type            = $this->getType();
      $quantity        = $this->getQuantity();
      $cancelled       = $this->getCancelled();
      $id              = $this->getId();

      $stmt->bind_param(
        "iiisdsi",
        $adjustmentId,
        $productId,
        $typeId,
        $type,
        $quantity,
        $cancelled,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Producto del ajuste actualizado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTOS_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar el producto del ajuste";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_inventario_ajuste_producto = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Producto del ajuste eliminado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTOS_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar el producto del ajuste";
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

    $query = "SELECT * FROM {$this->table} WHERE id_inventario_ajuste_producto = ?";

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
        $response->message  = "Producto del ajuste obtenido exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_AJUSTE_INVENTARIO_PRODUCTOS_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el producto del ajuste";
    }

    return $response;
  }

}
