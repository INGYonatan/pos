<?php
require_once __DIR__ . "/helperresponse.model.php";

/* paal_usuario_archivos
  1	id_usuario_archivo Primaria	int(11)			No	Ninguna		AUTO_INCREMENT
  2	id_usuario	int(11)			No	Ninguna
  3	nombre	varchar(255)	utf8mb4_bin		Sí	NULL
  4	slug	varchar(255)	utf8mb4_bin		Sí	NULL
  5	tipo	enum('folder','pdf','excel','word','imagen','video','otro')		Sí	NULL
  6	status	enum('activo','eliminado')		No	activo
 */

class UserFilesHelper
{
  private $table = DTI . "_usuario_archivos";

  private $id;
  private $userId;
  private $name;
  private $slug;
  private $type;
  private $status;

  public function __construct()
  {
    $this->id     = 0;
    $this->userId = 0;
    $this->name   = "";
    $this->slug   = "";
    $this->type   = "pdf";
    $this->status = "activo";
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getUserId()
  {
    return $this->userId;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getSlug()
  {
    return $this->slug;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getStatus()
  {
    return $this->status;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setUserId($userId)
  {
    $this->userId = $userId;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setSlug($slug)
  {
    $this->slug = $slug;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  public function setStatus($status)
  {
    $this->status = $status;
  }

  /**
   * Another methods
   */
  public function from($data)
  {
    if (isset($data["id_usuario_archivo"])) $this->setId($data["id_usuario_archivo"]);
    if (isset($data["id_usuario"]))         $this->setUserId($data["id_usuario"]);
    if (isset($data["nombre"]))             $this->setName($data["nombre"]);
    if (isset($data["slug"]))               $this->setSlug($data["slug"]);
    if (isset($data["tipo"]))               $this->setType($data["tipo"]);
    if (isset($data["status"]))             $this->setStatus($data["status"]);
  }

  public function toArray()
  {
    return [
      'uid'                 => $this->getId(),
      'id_usuario_archivo'  => $this->getId(),
      'id_usuario'          => $this->getUserId(),
      'nombre'              => $this->getName(),
      'slug'                => $this->getSlug(),
      'tipo'                => $this->getType(),
      'status'              => $this->getStatus()
    ];
  }

  public function create(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $userId = $this->getUserId();
    $name   = $this->getName();
    $slug   = $this->getSlug();
    $type   = $this->getType();
    $status = $this->getStatus();

    $query  = "INSERT INTO {$this->table} (
        id_usuario,
        nombre,
        slug,
        tipo,
        status
      ) VALUES (
        ?, ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("issss", $userId, $name, $slug, $type, $status);
      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status  = "success";
        $response->message = "Archivo registrado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_USER_FILES_HELPER_CREATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function read($params = []): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    try {
      $page      = $params['page']    ?? 1;
      $perPage   = $params['perPage'] ?? 20;
      $offset    = ($page - 1) * $perPage;

      $userId    = $params["userId"] ?? null;
      $term      = $params["term"]   ?? null;
      $type      = $params["type"]   ?? null;
      $status    = $params["status"] ?? "activo";

      $byUserId  = $userId !== null ? "id_usuario = {$userId}" : "1=1";
      $byTerm    = $term ? "nombre LIKE '%{$term}%'" : "1=1";
      $byType    = $type ? "tipo = '{$type}'" : "1=1";
      $byStatus  = $status ? "status = '{$status}'" : "1=1";

      $cFrom     = "FROM {$this->table}";
      $cWhere    = "WHERE
          ({$byUserId}) AND
          ({$byTerm})   AND
          ({$byType})   AND
          ({$byStatus})
      ";

      $query = "SELECT COUNT(id_usuario_archivo) AS total {$cFrom} {$cWhere}";
      $stmt  = $mysqli->prepare($query);
      $stmt->execute();

      $result   = $stmt->get_result();
      $row      = $result->fetch_assoc();
      $total    = $row["total"];
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
          ORDER BY id_usuario_archivo DESC
          LIMIT {$offset}, {$perPage}
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->execute();

        $result  = $stmt->get_result();
        $numRows = $result->num_rows;

        if ($numRows) {
          $rows = [];

          while ($row = $result->fetch_assoc()) {
            $item = new UserFilesHelper();
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
      error_log("ERROR_USER_FILES_HELPER_READ: " . $e->getMessage());
    }

    return $response;
  }

  public function update(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id     = $this->getId();
    $userId = $this->getUserId();
    $name   = $this->getName();
    $slug   = $this->getSlug();
    $type   = $this->getType();
    $status = $this->getStatus();

    $query  = "UPDATE
        {$this->table}
      SET
        id_usuario = ?,
        nombre     = ?,
        slug       = ?,
        tipo       = ?,
        status     = ?
      WHERE
        id_usuario_archivo = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("issssi", $userId, $name, $slug, $type, $status, $id);
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Archivo actualizado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_USER_FILES_HELPER_UPDATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function delete(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id = $this->getId();

    $query  = "DELETE FROM {$this->table} WHERE id_usuario_archivo = ?";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Archivo eliminado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_USER_FILES_HELPER_DELETE: {$e->getMessage()}");
    }

    return $response;
  }

  public function getById($id): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $query  = "SELECT * FROM {$this->table} WHERE id_usuario_archivo = ? LIMIT 1";
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
        $response->message = "Archivo encontrado.";
        $response->data    = $this;
      } else {
        $response->status  = "error";
        $response->message = "No se encontró el archivo.";
      }
    } catch (Exception $e) {
      error_log("ERROR_USER_FILES_HELPER_GETBYID: {$e->getMessage()}");
    }

    return $response;
  }

  public function getByUserId($userId, $params = []): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();
    $response->data["rows"] = [];

    $sortBy    = $params['sortBy']    ?? 'nombre';
    $sortOrder = $params['sortOrder'] ?? 'ASC';
    $status    = $params['status']    ?? 'activo';

    $query  = "SELECT * FROM {$this->table} WHERE id_usuario = ? AND status = ? ORDER BY {$sortBy} {$sortOrder}";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("is", $userId, $status);
      $stmt->execute();

      $result  = $stmt->get_result();
      $numRows = $result->num_rows;

      if ($numRows) {
        $rows = [];

        while ($row = $result->fetch_assoc()) {
          $item = new UserFilesHelper();
          $item->from($row);
          $rows[] = $item;
        }

        $response->status       = "success";
        $response->message      = "Archivos encontrados.";
        $response->data["rows"] = $rows;
      } else {
        $response->status  = "error";
        $response->message = "No se encontraron archivos.";
      }
    } catch (Exception $e) {
      error_log("ERROR_USER_FILES_HELPER_GETBYUSERID: {$e->getMessage()}");
    }

    return $response;
  }
}
