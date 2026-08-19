<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_marcas (
    id_marca,
    marca,
  );
*/

class BrandsModel
{
  private $table = "paal_marcas";

  private int $id;
  private string $name;

  public function __construct()
  {
    $this->id      = 0;
    $this->name    = "";
  }

  /**
   * Getters
   */
  public function getId(): int
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Setters
   */
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function setName(string $name): self
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_marca"])) $this->setId((int)$data["id_marca"]);
    if (isset($data["marca"]))    $this->setName($data["marca"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        marca
      ) VALUES (
        ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $name    = $this->getName();

      $stmt->bind_param(
        "s",
        $name
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Marca creadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_BRANDS_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear la marca";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page     = $params["page"]        ?? 1;
    $perPage  = $params["perPage"]     ?? 10;
    $offset   = ($page - 1) * $perPage;

    $name     = $params["name"] ?? null;
    $name     = isset($name) ? mysqli_real_escape_string($mysqli, $name) : null;

    $byName   = isset($name) ? "(marca = '{$name}')" : "1=1";

    $conditions = [
      $byName,
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
        $response->message  = "No se encontraron marcas";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_marca DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new BrandsModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Marcas obtenidosa exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_BRANDS_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de marcas";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        marca = ?
      WHERE
        id_marca = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $name    = $this->getName();
      $id      = $this->getId();

      $stmt->bind_param(
        "si",
        $name,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Marca actualizadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_BRANDS_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar la marca";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_marca = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Marca eliminadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_BRANDS_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar la marca";
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

    $query = "SELECT * FROM {$this->table} WHERE id_marca = ?";

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
        $response->message  = "Marca obtenidoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_BRANDS_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener la marca";
    }

    return $response;
  }

  public function getByName(string $name): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $name = strtoupper($name);

    $query = "SELECT * FROM {$this->table} WHERE marca = ? LIMIT 1";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("s", $name);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows > 0) {
        $data = $result->fetch_assoc();
        $this->from($data);

        $response->status   = "success";
        $response->message  = "Marca obtenidoa exitosamente";
      } else {
        $response->status   = "error";
        $response->message  = "Marca no encontrada";
      }
    } catch (Exception $e) {
      error_log("ERROR_BRANDS_MODEL::GET_BY_NAME: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener la marca";
    }

    return $response;
  }

}
