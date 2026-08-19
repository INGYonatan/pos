<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_producto_numeros_serie (
    id_producto_numero_serie,
    id_producto,
    folio_compra,
    folio_venta,
    numero_serie,
    status,
    fecha_creacion,
    id_sucursal,
  );
*/

class ProductoNumerosSerieModel
{
  private $table = "paal_producto_numeros_serie";

  private int $id;
  private int $productId;
  private string $purchaseFolio;
  private string $saleFolio;
  private string $serialNumber;
  private string $status;
  private string $createdAt;
  private int $sucursalId;

  public function __construct()
  {
    $this->id               = 0;
    $this->productId        = 0;
    $this->purchaseFolio    = "";
    $this->saleFolio        = "";
    $this->serialNumber     = "";
    $this->status           = "disponible";
    $this->createdAt        = date("Y-m-d H:i:s");
    $this->sucursalId       = 0;
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getProductId(): int
  {
    return $this->productId;
  }

  public function getPurchaseFolio(): string
  {
    return $this->purchaseFolio;
  }

  public function getSaleFolio(): string
  {
    return $this->saleFolio;
  }

  public function getSerialNumber(): string
  {
    return $this->serialNumber;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function getCreatedAt(): string
  {
    return $this->createdAt;
  }

  public function getSucursalId(): int
  {
    return $this->sucursalId;
  }

  /**
   * Setters
   */
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function setProductId(int $productId): self
  {
    $this->productId = $productId;
    return $this;
  }

  public function setPurchaseFolio(string $purchaseFolio): self
  {
    $this->purchaseFolio = $purchaseFolio;
    return $this;
  }

  public function setSaleFolio(string $saleFolio): self
  {
    $this->saleFolio = $saleFolio;
    return $this;
  }

  public function setSerialNumber(string $serialNumber): self
  {
    $this->serialNumber = $serialNumber;
    return $this;
  }

  public function setStatus(string $status): self
  {
    $this->status = $status;
    return $this;
  }

  public function setCreatedAt(string $createdAt): self
  {
    $this->createdAt = $createdAt;
    return $this;
  }

  public function setSucursalId(int $sucursalId): self
  {
    $this->sucursalId = $sucursalId;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_producto_numero_serie"])) $this->setId((int)$data["id_producto_numero_serie"]);
    if (isset($data["id_producto"]))              $this->setProductId((int)$data["id_producto"]);
    if (isset($data["folio_compra"]))             $this->setPurchaseFolio($data["folio_compra"]);
    if (isset($data["folio_venta"]))              $this->setSaleFolio($data["folio_venta"]);
    if (isset($data["numero_serie"]))             $this->setSerialNumber($data["numero_serie"]);
    if (isset($data["status"]))                   $this->setStatus($data["status"]);
    if (isset($data["fecha_creacion"]))           $this->setCreatedAt($data["fecha_creacion"]);
    if (isset($data["id_sucursal"]))              $this->setSucursalId((int)$data["id_sucursal"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
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

    $stmt = $mysqli->prepare($query);

    try {
      $productId        = $this->getProductId();
      $purchaseFolio    = $this->getPurchaseFolio();
      $saleFolio        = $this->getSaleFolio();
      $serialNumber     = $this->getSerialNumber();
      $status           = $this->getStatus();
      $createdAt        = $this->getCreatedAt();
      $sucursalId       = $this->getSucursalId();

      $stmt->bind_param(
        "isssssi",
        $productId,
        $purchaseFolio,
        $saleFolio,
        $serialNumber,
        $status,
        $createdAt,
        $sucursalId
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Número de serie creado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTO_NUMEROS_SERIE_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear el número de serie";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page             = $params["page"]        ?? 1;
    $perPage          = $params["perPage"]     ?? 10;
    $offset           = ($page - 1) * $perPage;

    $productId        = $params["productId"] ?? null;
    $productId        = isset($productId) ? (int) $productId : null;

    $byProductId      = isset($productId) ? "(id_producto = '{$productId}')" : "1=1";

    $purchaseFolio    = $params["purchaseFolio"] ?? null;
    $purchaseFolio    = isset($purchaseFolio) ? mysqli_real_escape_string($mysqli, $purchaseFolio) : null;

    $byPurchaseFolio  = isset($purchaseFolio) ? "(folio_compra = '{$purchaseFolio}')" : "1=1";

    $saleFolio        = $params["saleFolio"] ?? null;
    $saleFolio        = isset($saleFolio) ? mysqli_real_escape_string($mysqli, $saleFolio) : null;

    $bySaleFolio      = isset($saleFolio) ? "(folio_venta = '{$saleFolio}')" : "1=1";

    $serialNumber     = $params["serialNumber"] ?? null;
    $serialNumber     = isset($serialNumber) ? mysqli_real_escape_string($mysqli, $serialNumber) : null;

    $bySerialNumber   = isset($serialNumber) ? "(numero_serie = '{$serialNumber}')" : "1=1";

    $status           = $params["status"] ?? null;
    $status           = isset($status) ? mysqli_real_escape_string($mysqli, $status) : null;

    $byStatus         = isset($status) ? "(status = '{$status}')" : "1=1";

    $createdAt        = $params["createdAt"] ?? null;
    $createdAt        = isset($createdAt) ? mysqli_real_escape_string($mysqli, $createdAt) : null;

    $byCreatedAt      = isset($createdAt) ? "(fecha_creacion = '{$createdAt}')" : "1=1";

    $sucursalId       = $params["sucursalId"] ?? null;
    $sucursalId       = isset($sucursalId) ? (int) $sucursalId : null;

    $bySucursalId     = isset($sucursalId) ? "(id_sucursal = '{$sucursalId}')" : "1=1";

    $conditions = [
      $byProductId,
      $byPurchaseFolio,
      $bySaleFolio,
      $bySerialNumber,
      $byStatus,
      $byCreatedAt,
      $bySucursalId,
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
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_producto_numero_serie DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new ProductoNumerosSerieModel();
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
      error_log("ERROR_PRODUCTO_NUMEROS_SERIE_MODEL::READ_TOTAL: {$e->getMessage()}");

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
        id_producto    = ?,
        folio_compra   = ?,
        folio_venta    = ?,
        numero_serie   = ?,
        status         = ?,
        fecha_creacion = ?,
        id_sucursal    = ?
      WHERE
        id_producto_numero_serie = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $productId        = $this->getProductId();
      $purchaseFolio    = $this->getPurchaseFolio();
      $saleFolio        = $this->getSaleFolio();
      $serialNumber     = $this->getSerialNumber();
      $status           = $this->getStatus();
      $createdAt        = $this->getCreatedAt();
      $sucursalId       = $this->getSucursalId();
      $id               = $this->getId();

      $stmt->bind_param(
        "isssssii",
        $productId,
        $purchaseFolio,
        $saleFolio,
        $serialNumber,
        $status,
        $createdAt,
        $sucursalId,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Número de serie actualizado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTO_NUMEROS_SERIE_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar el número de serie";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_producto_numero_serie = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Número de serie eliminado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTO_NUMEROS_SERIE_MODEL::DELETE: {$e->getMessage()}");

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

    $query = "SELECT * FROM {$this->table} WHERE id_producto_numero_serie = ?";

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
      error_log("ERROR_PRODUCTO_NUMEROS_SERIE_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el número de serie";
    }

    return $response;
  }

  public function getAllByProductIdAndSucursalId(int $productId, int $sucursalId): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();
    $response->data->rows = [];

    $query = "SELECT * FROM {$this->table} WHERE id_producto = ? AND id_sucursal = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("ii", $productId, $sucursalId);
      $stmt->execute();

      $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()) {
        $item = new ProductoNumerosSerieModel();
        $item->from($row);

        $response->data->rows[] = $item;
      }

      $response->status  = "success";
      $response->message = "Números de serie obtenidos exitosamente";
    } catch (Exception $e) {
      error_log("ERROR_PRODUCTO_NUMEROS_SERIE_MODEL::GET_ALL_BY_PRODUCT_ID_AND_SUCURSAL_ID: {$e->getMessage()}");

      $response->status  = "error";
      $response->message = "Error al obtener los números de serie";
    }

    return $response;
  }

}
