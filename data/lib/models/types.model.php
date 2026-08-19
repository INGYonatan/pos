<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_tipos (
    id_tipo,
    nombre,
    slug,
    requiere_numero_serie,
    tangible,
    es_anticipo,
    es_nota_credito,
  );
*/

class TypesModel
{
  private $table = "paal_tipos";

  private int $id;
  private string $name;
  private string $slug;
  private int $requiresSerialNumber;
  private int $tangible;
  private int $isAdvance;
  private int $isCreditNote;

  public function __construct()
  {
    $this->id                      = 0;
    $this->name                    = "";
    $this->slug                    = "";
    $this->requiresSerialNumber    = 0;
    $this->tangible                = 1;
    $this->isAdvance               = 0;
    $this->isCreditNote            = 0;
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

  public function getSlug(): string
  {
    return $this->slug;
  }

  public function getRequiresSerialNumber(): int
  {
    return $this->requiresSerialNumber;
  }

  public function getTangible(): int
  {
    return $this->tangible;
  }

  public function getIsAdvance(): int
  {
    return $this->isAdvance;
  }

  public function getIsCreditNote(): int
  {
    return $this->isCreditNote;
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

  public function setSlug(string $slug): self
  {
    $this->slug = $slug;
    return $this;
  }

  public function setRequiresSerialNumber(int $requiresSerialNumber): self
  {
    $this->requiresSerialNumber = $requiresSerialNumber;
    return $this;
  }

  public function setTangible(int $tangible): self
  {
    $this->tangible = $tangible;
    return $this;
  }

  public function setIsAdvance(int $isAdvance): self
  {
    $this->isAdvance = $isAdvance;
    return $this;
  }

  public function setIsCreditNote(int $isCreditNote): self
  {
    $this->isCreditNote = $isCreditNote;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_tipo"]))               $this->setId((int)$data["id_tipo"]);
    if (isset($data["nombre"]))                $this->setName($data["nombre"]);
    if (isset($data["slug"]))                  $this->setSlug($data["slug"]);
    if (isset($data["requiere_numero_serie"])) $this->setRequiresSerialNumber((int)$data["requiere_numero_serie"]);
    if (isset($data["tangible"]))              $this->setTangible((int)$data["tangible"]);
    if (isset($data["es_anticipo"]))           $this->setIsAdvance((int)$data["es_anticipo"]);
    if (isset($data["es_nota_credito"]))       $this->setIsCreditNote((int)$data["es_nota_credito"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        nombre,
        slug,
        requiere_numero_serie,
        tangible,
        es_anticipo,
        es_nota_credito
      ) VALUES (
        ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $name                    = $this->getName();
      $slug                    = $this->getSlug();
      $requiresSerialNumber    = $this->getRequiresSerialNumber();
      $tangible                = $this->getTangible();
      $isAdvance               = $this->getIsAdvance();
      $isCreditNote            = $this->getIsCreditNote();

      $stmt->bind_param(
        "ssiiii",
        $name,
        $slug,
        $requiresSerialNumber,
        $tangible,
        $isAdvance,
        $isCreditNote
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Tipo creado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear el tipo";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page                    = $params["page"]        ?? 1;
    $perPage                 = $params["perPage"]     ?? 10;
    $offset                  = ($page - 1) * $perPage;

    $name                    = $params["name"] ?? null;
    $name                    = isset($name) ? mysqli_real_escape_string($mysqli, $name) : null;

    $byName                  = isset($name) ? "(nombre = '{$name}')" : "1=1";

    $slug                    = $params["slug"] ?? null;
    $slug                    = isset($slug) ? mysqli_real_escape_string($mysqli, $slug) : null;

    $bySlug                  = isset($slug) ? "(slug = '{$slug}')" : "1=1";

    $requiresSerialNumber    = $params["requiresSerialNumber"] ?? null;
    $requiresSerialNumber    = isset($requiresSerialNumber) ? (int) $requiresSerialNumber : null;

    $byRequiresSerialNumber  = isset($requiresSerialNumber) ? "(requiere_numero_serie = '{$requiresSerialNumber}')" : "1=1";

    $tangible                = $params["tangible"] ?? null;
    $tangible                = isset($tangible) ? (int) $tangible : null;

    $byTangible              = isset($tangible) ? "(tangible = '{$tangible}')" : "1=1";

    $isAdvance               = $params["isAdvance"] ?? null;
    $isAdvance               = isset($isAdvance) ? (int) $isAdvance : null;

    $byIsAdvance             = isset($isAdvance) ? "(es_anticipo = '{$isAdvance}')" : "1=1";

    $isCreditNote            = $params["isCreditNote"] ?? null;
    $isCreditNote            = isset($isCreditNote) ? (int) $isCreditNote : null;

    $byIsCreditNote          = isset($isCreditNote) ? "(es_nota_credito = '{$isCreditNote}')" : "1=1";

    $conditions = [
      $byName,
      $bySlug,
      $byRequiresSerialNumber,
      $byTangible,
      $byIsAdvance,
      $byIsCreditNote,
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
        $response->message  = "No se encontraron tipos";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_tipo DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new TypesModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Tipos obtenidos exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_TYPES_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de tipos";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        nombre                = ?,
        slug                  = ?,
        requiere_numero_serie = ?,
        tangible              = ?,
        es_anticipo           = ?,
        es_nota_credito       = ?
      WHERE
        id_tipo = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $name                    = $this->getName();
      $slug                    = $this->getSlug();
      $requiresSerialNumber    = $this->getRequiresSerialNumber();
      $tangible                = $this->getTangible();
      $isAdvance               = $this->getIsAdvance();
      $isCreditNote            = $this->getIsCreditNote();
      $id                      = $this->getId();

      $stmt->bind_param(
        "ssiiiii",
        $name,
        $slug,
        $requiresSerialNumber,
        $tangible,
        $isAdvance,
        $isCreditNote,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Tipo actualizado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar el tipo";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_tipo = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Tipo eliminado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar el tipo";
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

    $query = "SELECT * FROM {$this->table} WHERE id_tipo = ?";

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
        $response->message  = "Tipo obtenido exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el tipo";
    }

    return $response;
  }

  public function getBySlug(string $slug): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "SELECT * FROM {$this->table} WHERE slug = ? LIMIT 1";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("s", $slug);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows > 0) {
        $data = $result->fetch_assoc();
        $this->from($data);

        $response->status   = "success";
        $response->message  = "Tipo obtenido exitosamente";
      } else {
        $response->status   = "error";
        $response->message  = "Tipo no encontrado";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_MODEL::GET_BY_SLUG: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el tipo";
    }

    return $response;
  }

}
