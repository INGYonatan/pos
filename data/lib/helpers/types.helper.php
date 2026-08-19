<?php
require_once __DIR__ . "/helperresponse.model.php";

/* {prefix}_tipos
  1	id_tipo Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	nombre	varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	slug Índice	varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	4	requiere_numero_serie	tinyint(1)			No	0			Cambiar Cambiar	Eliminar Eliminar	
	5	tangible	tinyint(1)			No	1			Cambiar Cambiar	Eliminar Eliminar	
  6	es_anticipo	tinyint(1)			No	0			Cambiar Cambiar	Eliminar Eliminar	
	7	es_nota_credito	tinyint(1)			No	0			Cambiar Cambiar	Eliminar Eliminar	
 */

class TypesHelper
{
  private $table = DTI . "_tipos";

  private $id;
  private $name;
  private $slug;
  private $requiresSerialNumber;
  private $tangible;
  private $isAdvance;
  private $isCreditNote;

  public function __construct()
  {
    $this->id                   = 0;
    $this->name                 = "";
    $this->slug                 = "";
    $this->requiresSerialNumber = 0;
    $this->tangible             = 1;
    $this->isAdvance            = 0;
    $this->isCreditNote         = 0;
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getSlug()
  {
    return $this->slug;
  }

  public function getRequiresSerialNumber()
  {
    return $this->requiresSerialNumber;
  }

  public function getTangible()
  {
    return $this->tangible;
  }

  public function getIsAdvance()
  {
    return $this->isAdvance;
  }

  public function getIsCreditNote()
  {
    return $this->isCreditNote;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setSlug($slug)
  {
    $this->slug = $slug;
  }

  public function setRequiresSerialNumber($requiresSerialNumber)
  {
    $this->requiresSerialNumber = $requiresSerialNumber;
  }

  public function setTangible($tangible)
  {
    $this->tangible = $tangible;
  }

  public function setIsAdvance($isAdvance)
  {
    $this->isAdvance = $isAdvance;
  }

  public function setIsCreditNote($isCreditNote)
  {
    $this->isCreditNote = $isCreditNote;
  }

  /**
   * Another methods
   */
  public function from($data)
  {
    if (isset($data["id_tipo"]))                $this->setId($data["id_tipo"]);
    if (isset($data["nombre"]))                 $this->setName($data["nombre"]);
    if (isset($data["slug"]))                   $this->setSlug($data["slug"]);
    if (isset($data["requiere_numero_serie"]))  $this->setRequiresSerialNumber($data["requiere_numero_serie"]);
    if (isset($data["tangible"]))               $this->setTangible($data["tangible"]);
    if (isset($data["es_anticipo"]))            $this->setIsAdvance($data["es_anticipo"]);
    if (isset($data["es_nota_credito"]))        $this->setIsCreditNote($data["es_nota_credito"]);
  }

  public function toArray()
  {
    return [
      'uid'                   => $this->getId(),
      'name'                  => $this->getName(),
      'slug'                  => $this->getSlug(),
      'requiresSerialNumber'  => $this->getRequiresSerialNumber(),
      'tangible'              => $this->getTangible(),
      'isAdvance'             => $this->getIsAdvance(),
      'isCreditNote'          => $this->getIsCreditNote()
    ];
  }

  public function create(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $name                 = $this->getName();
    $slug                 = $this->getSlug();
    $requiresSerialNumber = $this->getRequiresSerialNumber();
    $tangible             = $this->getTangible();
    $isAdvance            = $this->getIsAdvance();
    $isCreditNote         = $this->getIsCreditNote();

    if ($isAdvance && $this->existsAdvanceType()) {
      $response->status  = "error";
      $response->message = "Ya existe un tipo de anticipo registrado.";

      return $response;
    }

    if ($isCreditNote && $this->existsCreditNoteType()) {
      $response->status  = "error";
      $response->message = "Ya existe un tipo de nota de crédito registrado.";

      return $response;
    }

    $query  = "INSERT INTO {$this->table} (
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

    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("ssiiii", $name, $slug, $requiresSerialNumber, $tangible, $isAdvance, $isCreditNote);
      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status  = "success";
        $response->message = "Registro creado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_HELPER_CREATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function read($params = []): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    try {
      $page           = $params['page']    ?? 1;
      $perPage        = $params['perPage'] ?? 20;
      $offset         = ($page - 1) * $perPage;

      $term           = $params["term"] ?? null;
      $tangible       = $params["tangible"] ?? null;
      $isAdvance      = $params["isAdvance"] ?? null;
      $isCreditNote   = $params["isCreditNote"] ?? null;

      $byTerm         = $term ? "nombre LIKE '%{$term}%'" : "1=1";
      $byTangible     = $tangible !== null ? "tangible = {$tangible}" : "1=1";
      $byIsAdvance    = $isAdvance !== null ? "es_anticipo = {$isAdvance}" : "1=1";
      $byIsCreditNote = $isCreditNote !== null ? "es_nota_credito = {$isCreditNote}" : "1=1";

      $cFrom    = "FROM {$this->table}";

      $cWhere   = "WHERE
          ({$byTerm})       AND
          ({$byTangible})   AND
          ({$byIsAdvance})  AND
          ({$byIsCreditNote})
      ";

      $query    = "SELECT COUNT(id_tipo) AS total {$cFrom} {$cWhere}";

      $stmt = $mysqli->prepare($query);
      $stmt->execute();

      $result = $stmt->get_result();
      $row    = $result->fetch_assoc();
      $total  = $row["total"];
      $numPages = ceil($total / $perPage);

      $response->data["numPages"] = $numPages;

      if ($total == 0) {
        $response->status  = "success";
        $response->message = "No hay registros disponibles";
        $response->data["total"] = $total;
        $response->data["rows"]  = [];
      }

      if ($total > 0) {
        $query = "SELECT * {$cFrom}
          {$cWhere}
          ORDER BY id_tipo DESC
          LIMIT {$offset}, {$perPage}
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->execute();

        $result  = $stmt->get_result();
        $numRows = $result->num_rows;

        if ($numRows) {
          $rows = [];

          while ($row = $result->fetch_assoc()) {
            $item = new TypesHelper();
            $item->from($row);
            $rows[] = $item;
          }

          $response->status  = "success";
          $response->message = "Registros encontrados";
          $response->data["total"] = $total;
          $response->data["rows"]  = $rows;
        }
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_HELPER_READ: " . $e->getMessage());
    }

    return $response;
  }

  public function update(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id                   = $this->getId();
    $name                 = $this->getName();
    $slug                 = $this->getSlug();
    $requiresSerialNumber = $this->getRequiresSerialNumber();
    $tangible             = $this->getTangible();
    $isAdvance            = $this->getIsAdvance();
    $isCreditNote         = $this->getIsCreditNote();

    if ($isAdvance && $this->existsAdvanceType($id)) {
      $response->status  = "error";
      $response->message = "Ya existe un tipo de anticipo registrado.";

      return $response;
    }

    if ($isCreditNote && $this->existsCreditNoteType($id)) {
      $response->status  = "error";
      $response->message = "Ya existe un tipo de nota de crédito registrado.";

      return $response;
    }

    $query  = "UPDATE
        {$this->table}
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
        $response->status  = "success";
        $response->message = "Registro actualizado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_HELPER_UPDATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function delete(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id = $this->getId();

    $query  = "DELETE FROM {$this->table} WHERE id_tipo = ?";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Registro eliminado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_HELPER_DELETE: {$e->getMessage()}");
    }

    return $response;
  }

  public function getById($id): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $query  = "SELECT * FROM {$this->table} WHERE id_tipo = ? LIMIT 1";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $stmt->execute();

      $result  = $stmt->get_result();
      $numRows = $result->num_rows;

      if ($numRows) {
        $row = $result->fetch_assoc();

        $this->from($row);

        $response->status  = "success";
        $response->message = "Registro encontrado.";
        $response->data    = $this;
      } else {
        $response->status  = "error";
        $response->message = "No se encontró el registro.";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_HELPER_GETBYID: {$e->getMessage()}");
    }

    return $response;
  }

  public function getAll($params = []): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();
    $response->data["rows"] = [];

    $sortBy    = $params['sortBy']    ?? 'nombre';
    $sortOrder = $params['sortOrder'] ?? 'ASC';

    $query  = "SELECT * FROM {$this->table} ORDER BY {$sortBy} {$sortOrder}";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->execute();

      $result  = $stmt->get_result();
      $numRows = $result->num_rows;

      if ($numRows) {
        $rows = [];

        while ($row = $result->fetch_assoc()) {
          $item = new TypesHelper();
          $item->from($row);
          $rows[] = $item;
        }

        $response->status       = "success";
        $response->message      = "Registros encontrados.";
        $response->data["rows"] = $rows;
      } else {
        $response->status  = "error";
        $response->message = "No se encontraron registros.";
      }
    } catch (Exception $e) {
      error_log("ERROR_TYPES_HELPER_GETALL: {$e->getMessage()}");
    }

    return $response;
  }

  public function existsAdvanceType($ignoreId = null): bool
  {
    global $mysqli;

    $exists = false;

    $ignoreCondition  = $ignoreId ? "AND id_tipo != ?" : "";
    $query            = "SELECT COUNT(id_tipo) AS total FROM {$this->table} WHERE es_anticipo = 1 {$ignoreCondition}";
    $stmt             = $mysqli->prepare($query);

    try {
      if ($ignoreId) {
        $stmt->bind_param("i", $ignoreId);
      }

      $stmt->execute();

      $result = $stmt->get_result();
      $row    = $result->fetch_assoc();
      $total  = $row["total"];

      if ($total > 0) $exists = true;
    } catch (Exception $e) {
      error_log("ERROR_TYPES_HELPER_EXISTS_ADVANCE_TYPE: {$e->getMessage()}");
    }

    return $exists;
  }

  public function existsCreditNoteType($ignoreId = null): bool
  {
    global $mysqli;

    $exists = false;

    $ignoreCondition  = $ignoreId ? "AND id_tipo != ?" : "";
    $query            = "SELECT COUNT(id_tipo) AS total FROM {$this->table} WHERE es_nota_credito = 1 {$ignoreCondition}";
    $stmt             = $mysqli->prepare($query);

    try {
      if ($ignoreId) {
        $stmt->bind_param("i", $ignoreId);
      }

      $stmt->execute();

      $result = $stmt->get_result();
      $row    = $result->fetch_assoc();
      $total  = $row["total"];

      if ($total > 0) $exists = true;
    } catch (Exception $e) {
      error_log("ERROR_TYPES_HELPER_EXISTS_CREDIT_NOTE_TYPE: {$e->getMessage()}");
    }

    return $exists;
  }
}
