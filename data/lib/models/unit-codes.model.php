<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_clave_unidades (
    id_clave_unidad,
    clave,
    nombre,
  );
*/

class UnitCodesModel
{
  private $table = "paal_clave_unidades";

  private int $id;
  private string $code;
  private string $name;

  public function __construct()
  {
    $this->id      = 0;
    $this->code    = "";
    $this->name    = "";
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

  public function setCode(string $code): self
  {
    $this->code = $code;
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
    if (isset($data["id_clave_unidad"])) $this->setId((int)$data["id_clave_unidad"]);
    if (isset($data["clave"]))           $this->setCode($data["clave"]);
    if (isset($data["nombre"]))          $this->setName($data["nombre"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        clave,
        nombre
      ) VALUES (
        ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $code    = $this->getCode();
      $name    = $this->getName();

      $stmt->bind_param(
        "ss",
        $code,
        $name
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Clave de unidad creadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_UNIT_CODES_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear la clave de unidad";
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

    $code     = $params["code"] ?? null;
    $code     = isset($code) ? mysqli_real_escape_string($mysqli, $code) : null;

    $byCode   = isset($code) ? "(clave = '{$code}')" : "1=1";

    $name     = $params["name"] ?? null;
    $name     = isset($name) ? mysqli_real_escape_string($mysqli, $name) : null;

    $byName   = isset($name) ? "(nombre = '{$name}')" : "1=1";

    $conditions = [
      $byCode,
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
        $response->message  = "No se encontraron claves de unidad";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_clave_unidad DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new UnitCodesModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Claves de unidad obtenidosa exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_UNIT_CODES_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de claves de unidad";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        clave  = ?,
        nombre = ?
      WHERE
        id_clave_unidad = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $code    = $this->getCode();
      $name    = $this->getName();
      $id      = $this->getId();

      $stmt->bind_param(
        "ssi",
        $code,
        $name,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Clave de unidad actualizadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_UNIT_CODES_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar la clave de unidad";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_clave_unidad = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Clave de unidad eliminadoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_UNIT_CODES_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar la clave de unidad";
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

    $query = "SELECT * FROM {$this->table} WHERE id_clave_unidad = ?";

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
        $response->message  = "Clave de unidad obtenidoa exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_UNIT_CODES_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener la clave de unidad";
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
        $response->message  = "Clave de unidad obtenida exitosamente";
      } else {
        $response->status   = "error";
        $response->message  = "Clave de unidad no encontrada";
      }
    } catch (Exception $e) {
      error_log("ERROR_UNIT_CODES_MODEL::GET_BY_CODE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener la clave de unidad";
    }

    return $response;
  }

}
