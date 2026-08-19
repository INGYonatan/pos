<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_clave_producto_servicios (
    id_clave_producto_servicio,
    clave,
    descripcion,
  );
*/

class ProductServiceCodesModel
{
  private $table = "paal_clave_producto_servicios";

  private int $id;
  private string $code;
  private string $description;

  public function __construct()
  {
    $this->id             = 0;
    $this->code           = "";
    $this->description    = "";
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getCode(): string
  {
    return $this->code;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  /**
   * Setters
   */
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function setCode(string $code): self
  {
    $this->code = $code;
    return $this;
  }

  public function setDescription(string $description): self
  {
    $this->description = $description;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_clave_producto_servicio"])) $this->setId((int)$data["id_clave_producto_servicio"]);
    if (isset($data["clave"]))                      $this->setCode($data["clave"]);
    if (isset($data["descripcion"]))                $this->setDescription($data["descripcion"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        clave,
        descripcion
      ) VALUES (
        ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $code           = $this->getCode();
      $description    = $this->getDescription();

      $stmt->bind_param(
        "ss",
        $code,
        $description
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Clave de producto/servicio creadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCT_SERVICE_CODES_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear la clave de producto/servicio";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page           = $params["page"]        ?? 1;
    $perPage        = $params["perPage"]     ?? 10;
    $offset         = ($page - 1) * $perPage;

    $code           = $params["code"] ?? null;
    $code           = isset($code) ? mysqli_real_escape_string($mysqli, $code) : null;

    $byCode         = isset($code) ? "(clave = '{$code}')" : "1=1";

    $description    = $params["description"] ?? null;
    $description    = isset($description) ? mysqli_real_escape_string($mysqli, $description) : null;

    $byDescription  = isset($description) ? "(descripcion = '{$description}')" : "1=1";

    $conditions = [
      $byCode,
      $byDescription,
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
        $response->message  = "No se encontraron claves de producto/servicio";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_clave_producto_servicio DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new ProductServiceCodesModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Claves de producto/servicio obtenidosa exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_PRODUCT_SERVICE_CODES_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de claves de producto/servicio";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        clave       = ?,
        descripcion = ?
      WHERE
        id_clave_producto_servicio = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $code           = $this->getCode();
      $description    = $this->getDescription();
      $id             = $this->getId();

      $stmt->bind_param(
        "ssi",
        $code,
        $description,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Clave de producto/servicio actualizadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCT_SERVICE_CODES_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar la clave de producto/servicio";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_clave_producto_servicio = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Clave de producto/servicio eliminadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCT_SERVICE_CODES_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar la clave de producto/servicio";
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

    $query = "SELECT * FROM {$this->table} WHERE id_clave_producto_servicio = ?";

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
        $response->message  = "Clave de producto/servicio obtenidoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCT_SERVICE_CODES_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener la clave de producto/servicio";
    }

    return $response;
  }

  public function getByCode(string $code): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "SELECT * FROM {$this->table} WHERE clave = ? LIMIT 1";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("s", $code);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows > 0) {
        $data = $result->fetch_assoc();
        $this->from($data);

        $response->status   = "success";
        $response->message  = "Clave de producto/servicio obtenida exitosamente";
      } else {
        $response->status   = "error";
        $response->message  = "Clave de producto/servicio no encontrada";
      }
    } catch (Exception $e) {
      error_log("ERROR_PRODUCT_SERVICE_CODES_MODEL::GET_BY_CODE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener la clave de producto/servicio";
    }

    return $response;
  }

}
