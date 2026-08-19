<?php
require_once __DIR__ . "/modelresponse.model.php";

/*
  paal_proveedores (
    id_proveedor,
    nombre_proveedor,
    nombre_comercial,
    correo,
    telefono,
    status,
    fecha_creacion,
  );
*/

class SuppliersModel
{
  private $table = "paal_proveedores";

  private int $id;
  private string $name;
  private string $commercialName;
  private string $email;
  private string $phone;
  private string $status;
  private string $createdAt;

  public function __construct()
  {
    $this->id                = 0;
    $this->name              = "";
    $this->commercialName    = "";
    $this->email             = "";
    $this->phone             = "";
    $this->status            = "activo";
    $this->createdAt         = date("Y-m-d H:i:s");
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

  public function getCommercialName(): string
  {
    return $this->commercialName;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getPhone(): string
  {
    return $this->phone;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function getCreatedAt(): string
  {
    return $this->createdAt;
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

  public function setCommercialName(string $commercialName): self
  {
    $this->commercialName = $commercialName;
    return $this;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;
    return $this;
  }

  public function setPhone(string $phone): self
  {
    $this->phone = $phone;
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

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_proveedor"]))     $this->setId((int)$data["id_proveedor"]);
    if (isset($data["nombre_proveedor"])) $this->setName($data["nombre_proveedor"]);
    if (isset($data["nombre_comercial"])) $this->setCommercialName($data["nombre_comercial"]);
    if (isset($data["correo"]))           $this->setEmail($data["correo"]);
    if (isset($data["telefono"]))         $this->setPhone($data["telefono"]);
    if (isset($data["status"]))           $this->setStatus($data["status"]);
    if (isset($data["fecha_creacion"]))   $this->setCreatedAt($data["fecha_creacion"]);

    return $this;
  }

  public function create(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "INSERT INTO {$this->table} (
        nombre_proveedor,
        nombre_comercial,
        correo,
        telefono,
        status,
        fecha_creacion
      ) VALUES (
        ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $name              = $this->getName();
      $commercialName    = $this->getCommercialName();
      $email             = $this->getEmail();
      $phone             = $this->getPhone();
      $status            = $this->getStatus();
      $createdAt         = $this->getCreatedAt();

      $stmt->bind_param(
        "ssssss",
        $name,
        $commercialName,
        $email,
        $phone,
        $status,
        $createdAt
      );

      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status   = "success";
        $response->message  = "Proveedor creado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_SUPPLIERS_MODEL::CREATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al crear el proveedor";
    }

    return $response;
  }

  public function read(array $params = []): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $page              = $params["page"]        ?? 1;
    $perPage           = $params["perPage"]     ?? 10;
    $offset            = ($page - 1) * $perPage;

    $name              = $params["name"] ?? null;
    $name              = isset($name) ? mysqli_real_escape_string($mysqli, $name) : null;

    $byName            = isset($name) ? "(nombre_proveedor = '{$name}')" : "1=1";

    $commercialName    = $params["commercialName"] ?? null;
    $commercialName    = isset($commercialName) ? mysqli_real_escape_string($mysqli, $commercialName) : null;

    $byCommercialName  = isset($commercialName) ? "(nombre_comercial = '{$commercialName}')" : "1=1";

    $email             = $params["email"] ?? null;
    $email             = isset($email) ? mysqli_real_escape_string($mysqli, $email) : null;

    $byEmail           = isset($email) ? "(correo = '{$email}')" : "1=1";

    $phone             = $params["phone"] ?? null;
    $phone             = isset($phone) ? mysqli_real_escape_string($mysqli, $phone) : null;

    $byPhone           = isset($phone) ? "(telefono = '{$phone}')" : "1=1";

    $status            = $params["status"] ?? null;
    $status            = isset($status) ? mysqli_real_escape_string($mysqli, $status) : null;

    $byStatus          = isset($status) ? "(status = '{$status}')" : "1=1";

    $createdAt         = $params["createdAt"] ?? null;
    $createdAt         = isset($createdAt) ? mysqli_real_escape_string($mysqli, $createdAt) : null;

    $byCreatedAt       = isset($createdAt) ? "(fecha_creacion = '{$createdAt}')" : "1=1";

    $conditions = [
      $byName,
      $byCommercialName,
      $byEmail,
      $byPhone,
      $byStatus,
      $byCreatedAt,
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
        $response->message  = "No se encontraron proveedores";

        $response->data = (object) [
          "rows"  => [],
          "total" => 0,
          "pages" => 0
        ];

        return $response;
      }

      // Query data
      $query  = "SELECT * {$cFrom} {$cWhere} ORDER BY id_proveedor DESC {$cLimit}";
      $result = mysqli_query($mysqli, $query);
      $rows   = [];

      while ($row = mysqli_fetch_assoc($result)) {
        $item = new SuppliersModel();
        $item->from($row);

        $rows[] = $item;
      }

      $response->status   = "success";
      $response->message  = "Proveedores obtenidos exitosamente";

      $response->data = (object) [
        "rows"  => $rows,
        "total" => $total,
        "pages" => $pages
      ];
    } catch (Exception $e) {
      error_log("ERROR_SUPPLIERS_MODEL::READ_TOTAL: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el total de proveedores";
    }

    return $response;
  }

  public function update(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "UPDATE {$this->table}
      SET
        nombre_proveedor = ?,
        nombre_comercial = ?,
        correo           = ?,
        telefono         = ?,
        status           = ?,
        fecha_creacion   = ?
      WHERE
        id_proveedor = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $name              = $this->getName();
      $commercialName    = $this->getCommercialName();
      $email             = $this->getEmail();
      $phone             = $this->getPhone();
      $status            = $this->getStatus();
      $createdAt         = $this->getCreatedAt();
      $id                = $this->getId();

      $stmt->bind_param(
        "ssssssi",
        $name,
        $commercialName,
        $email,
        $phone,
        $status,
        $createdAt,
        $id
      );

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Proveedor actualizado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_SUPPLIERS_MODEL::UPDATE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al actualizar el proveedor";
    }

    return $response;
  }

  public function deleteById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query = "DELETE FROM {$this->table} WHERE id_proveedor = ?";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);

      $result = $stmt->execute();

      if ($result) {
        $response->status   = "success";
        $response->message  = "Proveedor eliminado exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_SUPPLIERS_MODEL::DELETE: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al eliminar el proveedor";
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

    $query = "SELECT * FROM {$this->table} WHERE id_proveedor = ?";

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
        $response->message  = "Proveedor obtenido exitosamente";
      }
    } catch (Exception $e) {
      error_log("ERROR_SUPPLIERS_MODEL::GET_BY_ID: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el proveedor";
    }

    return $response;
  }

  public function getByName(string $name): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $name = strtoupper($name);

    $query = "SELECT * FROM {$this->table} WHERE nombre_proveedor = ? LIMIT 1";

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
        $response->message  = "Proveedor obtenido exitosamente";
      } else {
        $response->status   = "error";
        $response->message  = "Proveedor no encontrado";
      }
    } catch (Exception $e) {
      error_log("ERROR_SUPPLIERS_MODEL::GET_BY_NAME: {$e->getMessage()}");

      $response->status   = "error";
      $response->message  = "Error al obtener el proveedor";
    }

    return $response;
  }

}
