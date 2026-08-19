<?php
require_once __DIR__ . "/helperresponse.model.php";
/* {prefix}_gasto_conceptos
	1	id_gasto_concepto Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	concepto	varchar(250)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	slug Índice	varchar(250)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
 */

class ExpenseConceptsHelper
{
  private $table = DTI . "_gasto_conceptos";

  private $id;
  private $concept;
  private $slug;

  public function __construct()
  {
    $this->id      = 0;
    $this->concept = "";
    $this->slug    = "";
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getConcept()
  {
    return $this->concept;
  }

  public function getSlug()
  {
    return $this->slug;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setConcept($concept)
  {
    $this->concept = $concept;
  }

  public function setSlug($slug)
  {
    $this->slug = $slug;
  }

  /**
   * Another methods
   */
  public function from($data)
  {
    if (isset($data['id_gasto_concepto']))  $this->setId($data['id_gasto_concepto']);
    if (isset($data['concepto']))           $this->setConcept($data['concepto']);
    if (isset($data['slug']))               $this->setSlug($data['slug']);
  }

  public function toArray()
  {
    return [
      'id'      => $this->getId(),
      'concept' => $this->getConcept(),
      'slug'    => $this->getSlug()
    ];
  }

  public function create(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $concept = $this->getConcept();
    $slug    = $this->getSlug();

    $query  = "INSERT INTO {$this->table} (concepto, slug) VALUES (?, ?)";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("ss", $concept, $slug);
      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status  = "success";
        $response->message = "Registro creado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_EXPENSE_CONCEPTS_HELPER_CREATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function read($params = []): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    try {
      $page     = $params['page']    ?? 1;
      $perPage  = $params['perPage'] ?? 20;
      $offset   = ($page - 1) * $perPage;

      $term     = $params["term"] ? mysqli_real_escape_string($mysqli, $params["term"]) : null;

      $byTerm   = $term ? "concepto LIKE '%{$term}%'" : "1=1";

      $cFrom    = "FROM {$this->table}";
      $cWhere   = "WHERE ({$byTerm})";

      $query    = "SELECT COUNT(id_gasto_concepto) AS total {$cFrom} {$cWhere}";

      $stmt = $mysqli->prepare($query);
      $stmt->execute();

      $result = $stmt->get_result();
      $row    = $result->fetch_assoc();
      $total  = $row["total"];

      if ($total == 0) {
        $response->status  = "success";
        $response->message = "No hay registros disponibles";
        $response->data["total"] = $total;
        $response->data["rows"]  = [];
      }

      if ($total > 0) {
        $query = "SELECT * {$cFrom}
          {$cWhere}
          ORDER BY concepto ASC
          LIMIT {$offset}, {$perPage}
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->execute();

        $result  = $stmt->get_result();
        $numRows = $result->num_rows;

        if ($numRows) {
          $rows = [];

          while ($row = $result->fetch_assoc()) {
            $item = new ExpenseConceptsHelper();
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
      error_log("ERROR_EXPENSE_CONCEPTS_HELPER_READ: " . $e->getMessage());
    }

    return $response;
  }

  public function update(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id      = $this->getId();
    $concept = $this->getConcept();
    $slug    = $this->getSlug();

    $query  = "UPDATE {$this->table} SET concepto = ?, slug = ? WHERE id_gasto_concepto = ?";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("ssi", $concept, $slug, $id);
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Registro actualizado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_EXPENSE_CONCEPTS_HELPER_UPDATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function delete(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id = $this->getId();

    $query  = "DELETE FROM {$this->table} WHERE id_gasto_concepto = ?";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Registro eliminado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_EXPENSE_CONCEPTS_HELPER_DELETE: {$e->getMessage()}");
    }

    return $response;
  }

  public function getAll(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();
    $response->data["rows"] = [];

    $query = "SELECT * FROM {$this->table} ORDER BY concepto ASC";

    try {
      $result   = mysqli_query($mysqli, $query);
      $numRows  = mysqli_num_rows($result);

      if ($numRows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $item = new ExpenseConceptsHelper();
          $item->from($row);
          $response->data["rows"][] = $item;
        }
      }

      $response->status  = "success";
      $response->message = "{$numRows} registros encontrados.";
    } catch (Exception $e) {
      error_log("ERROR_EXPENSE_CONCEPTS_HELPER_GET_ALL: {$e->getMessage()}");
    }

    return $response;
  }

  public function getById($id): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $query = "SELECT * FROM {$this->table} WHERE id_gasto_concepto = ? LIMIT 1";

    try {
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param("i", $id);
      $stmt->execute();

      $result = $stmt->get_result();
      $numRows = $result->num_rows;

      if ($numRows > 0) {
        $row = $result->fetch_assoc();

        $this->from($row);
      }

      $response->status  = "success";
      $response->message = "{$numRows} registros encontrados.";
    } catch (Exception $e) {
      error_log("ERROR_EXPENSE_CONCEPTS_HELPER_GET_BY_ID: {$e->getMessage()}");
    }

    return $response;
  }
}
