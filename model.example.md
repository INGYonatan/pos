<?php
require_once __DIR__ . "/modelresponse.model.php";

/* 
  pos_inventario (
    id_inventario INT (11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_llantera INT (11) NOT NULL,
    id_producto INT (11) NOT NULL,
    stock INT (11) NOT NULL DEFAULT 0,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_llantera) REFERENCES bot_llanteras (id_llantera),
    FOREIGN KEY (id_producto) REFERENCES pos_productos (id_producto),
    UNIQUE KEY (id_llantera, id_producto)
  ) COMMENT 'Tabla que mantiene el inventario de productos en cada llantera, actualizada automáticamente con cada venta y reposición';
*/

class InventarioModel
{
  private string $table = "pos_inventario";

  private int $id;
  private int $botId;
  private int $productId;
  private int $stock;
  private string $updatedAt;

  public function __construct()
  {
    $this->id           = 0;
    $this->botId        = 0;
    $this->productId    = 0;
    $this->stock        = 0;
    $this->updatedAt    = date("Y-m-d H:i:s");
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getBotId(): int
  {
    return $this->botId;
  }

  public function getProductId(): int
  {
    return $this->productId;
  }

  public function getStock(): int
  {
    return $this->stock;
  }

  public function getUpdatedAt(): string
  {
    return $this->updatedAt;
  }

  /**
   * Setters
   */
  public function setId(int $id)
  {
    $this->id = $id;
  }

  public function setBotId(int $botId)
  {
    $this->botId = $botId;
  }

  public function setProductId(int $productId)
  {
    $this->productId = $productId;
  }

  public function setStock(int $stock)
  {
    $this->stock = $stock;
  }

  public function setUpdatedAt(string $updatedAt)
  {
    $this->updatedAt = $updatedAt;
  }

  /**
   * Another methods
   */
  public function from(array $data)
  {
    if (isset($data["id_inventario"]))         $this->setId($data["id_inventario"]);
    if (isset($data["id_llantera"]))           $this->setBotId($data["id_llantera"]);
    if (isset($data["id_producto"]))           $this->setProductId($data["id_producto"]);
    if (isset($data["stock"]))                 $this->setStock($data["stock"]);
    if (isset($data["fecha_actualizacion"]))   $this->setUpdatedAt($data["fecha_actualizacion"]);
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        id_llantera,
        id_producto,
        stock,
        fecha_actualizacion
      ) VALUES (
        ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param(
        "iiis",
        $this->getBotId(),
        $this->getProductId(),
        $this->getStock(),
        $this->getUpdatedAt()
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

    $page         = $params["page"]       ?? 1;
    $perPage      = $params["perPage"]    ?? 10;
    $offset       = ($page - 1) * $perPage;

    $term         = $params["term"] ?? "";
    $term         = mysqli_real_escape_string($mysqli, $term);

    $botId        = $params["botId"] ?? ($params["tireShopId"] ?? null);
    $botId        = isset($botId) ? (int) $botId : null;

    $productId    = $params["productId"] ?? null;
    $productId    = isset($productId) ? (int) $productId : null;

    $userId       = $params["userId"] ?? null;
    $userId       = isset($userId) ? (int) $userId : null;

    $byTerm       = isset($term) && $term !== "" ? "(
      P.sku             LIKE _utf8 '%{$term}%' collate utf8_unicode_ci OR
      P.nombre          LIKE _utf8 '%{$term}%' collate utf8_unicode_ci OR
      B.nombre_comercial LIKE _utf8 '%{$term}%' collate utf8_unicode_ci
    )" : "1=1";

    $byBotId      = isset($botId) ? "(I.id_llantera = '{$botId}')" : "1=1";
    $byProductId  = isset($productId) ? "(I.id_producto = '{$productId}')" : "1=1";
    $byUserId     = isset($userId) ? "(B.id_usuario = '{$userId}')" : "1=1";

    $conditions = [
      $byTerm,
      $byBotId,
      $byProductId,
      $byUserId
    ];

    $conditions = implode(" AND ", $conditions);

    // Clausulas
    $cFrom  = "FROM {$this->table} AS I
      INNER JOIN pos_productos AS P ON P.id_producto = I.id_producto
      INNER JOIN bot_llanteras AS B ON B.id_llantera = I.id_llantera";
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
      $query  = "SELECT I.* {$cFrom} {$cWhere} ORDER BY I.id_inventario DESC {$cLimit}";
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
        id_llantera         = ?,
        id_producto         = ?,
        stock               = ?,
        fecha_actualizacion = ?
      WHERE
        id_inventario = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param(
        "iiisi",
        $this->getBotId(),
        $this->getProductId(),
        $this->getStock(),
        $this->getUpdatedAt(),
        $this->getId()
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

  public function getByBotIdAndProductId(int $botId, int $productId): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "SELECT * FROM {$this->table} WHERE id_llantera = ? AND id_producto = ? LIMIT 1";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("ii", $botId, $productId);
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
      error_log("ERROR_INVENTARIO_MODEL::GET_BY_BOT_ID_AND_PRODUCT_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el inventario";
    }

    return $response;
  }
}
