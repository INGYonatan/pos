<?php
require_once __DIR__ . "/modelresponse.model.php";

// 1	id_sucursal Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
// 2	nombre_sucursal	varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
// 3	telefono	varchar(15)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
// 4	direccion	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
// 5	numero_serie	text	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
// 6	tipo	enum('sucursal', 'almacen', 'sucursal movil')	utf8mb4_bin		No	sucursal			Cambiar Cambiar	Eliminar Eliminar	
// 7	status	enum('activo', 'eliminado')	utf8mb4_bin		No	activo			Cambiar Cambiar	Eliminar Eliminar	
// 8	correo	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
// 9	rfc	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
// 10	cp	varchar(250)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
// 11	nombre_comercial	varchar(255)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
// 12	logo	text	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
// 13	display_orden	int(11)			No	1			Cambiar Cambiar	Eliminar Eliminar	

class SucursalesModel
{
  private $table = "paal_sucursales";

  private int $id;
  private string $name;
  private string $phone;
  private string $address;
  private string $serialNumber;
  private string $type;
  private string $status;
  private string $email;
  private string $rfc;
  private string $postalCode;
  private string $commercialName;
  private string $logo;
  private int $displayOrder;

  public function __construct()
  {
    $this->id              = 0;
    $this->name            = "";
    $this->phone           = "";
    $this->address         = "";
    $this->serialNumber    = "";
    $this->type            = "sucursal";
    $this->status          = "activo";
    $this->email           = "";
    $this->rfc             = "";
    $this->postalCode      = "";
    $this->commercialName  = "";
    $this->logo            = "";
    $this->displayOrder    = 1;
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

  public function getPhone(): string
  {
    return $this->phone;
  }

  public function getAddress(): string
  {
    return $this->address;
  }

  public function getSerialNumber(): string
  {
    return $this->serialNumber;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getRfc(): string
  {
    return $this->rfc;
  }

  public function getPostalCode(): string
  {
    return $this->postalCode;
  }

  public function getCommercialName(): string
  {
    return $this->commercialName;
  }

  public function getLogo(): string
  {
    return $this->logo;
  }

  public function getDisplayOrder(): int
  {
    return $this->displayOrder;
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

  public function setPhone(string $phone): self
  {
    $this->phone = $phone;
    return $this;
  }

  public function setAddress(string $address): self
  {
    $this->address = $address;
    return $this;
  }

  public function setSerialNumber(string $serialNumber): self
  {
    $this->serialNumber = $serialNumber;
    return $this;
  }

  public function setType(string $type): self
  {
    $this->type = $type;
    return $this;
  }

  public function setStatus(string $status): self
  {
    $this->status = $status;
    return $this;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;
    return $this;
  }

  public function setRfc(string $rfc): self
  {
    $this->rfc = $rfc;
    return $this;
  }

  public function setPostalCode(string $postalCode): self
  {
    $this->postalCode = $postalCode;
    return $this;
  }

  public function setCommercialName(string $commercialName): self
  {
    $this->commercialName = $commercialName;
    return $this;
  }

  public function setLogo(string $logo): self
  {
    $this->logo = $logo;
    return $this;
  }

  public function setDisplayOrder(int $displayOrder): self
  {
    $this->displayOrder = $displayOrder;
    return $this;
  }

  /**
   * Another methods
   */
  public function from(array $data): self
  {
    if (isset($data["id_sucursal"]))      $this->setId((int)$data["id_sucursal"]);
    if (isset($data["nombre_sucursal"]))  $this->setName($data["nombre_sucursal"]);
    if (isset($data["telefono"]))         $this->setPhone($data["telefono"]);
    if (isset($data["direccion"]))        $this->setAddress($data["direccion"]);
    if (isset($data["numero_serie"]))     $this->setSerialNumber($data["numero_serie"]);
    if (isset($data["tipo"]))             $this->setType($data["tipo"]);
    if (isset($data["status"]))           $this->setStatus($data["status"]);
    if (isset($data["correo"]))           $this->setEmail($data["correo"]);
    if (isset($data["rfc"]))              $this->setRfc($data["rfc"]);
    if (isset($data["cp"]))               $this->setPostalCode($data["cp"]);
    if (isset($data["nombre_comercial"])) $this->setCommercialName($data["nombre_comercial"]);
    if (isset($data["logo"]))             $this->setLogo($data["logo"]);
    if (isset($data["display_orden"]))    $this->setDisplayOrder((int)$data["display_orden"]);

    return $this;
  }

  public function getAll(): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();
    $response->data->rows = [];

    try {
      $query  = "SELECT * FROM {$this->table} WHERE status = 'activo' ORDER BY display_orden ASC";
      $result = mysqli_query($mysqli, $query);

      if (!$result) {
        $response->message = "Error al obtener las sucursales: " . mysqli_error($mysqli);
        return $response;
      }

      $numRows = mysqli_num_rows($result);

      if ($numRows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $item = new self();
          $item->from($row);

          $response->data->rows[] = $item;
        }
      }

      $response->status  = "success";
      $response->message = "Sucursales obtenidas correctamente";

      return $response;
    } catch (Exception $te) {
      error_log("ERROR_SUCURSALES_MODEL::GET_ALL: {$te->getMessage()}");
      return $response;
    }
  }

  public function getById(int $id): ModelResponse
  {
    global $mysqli;

    $response = new ModelResponse();

    $query  = "SELECT * FROM {$this->table} WHERE id_sucursal = ? AND status = 'activo' LIMIT 1";
    $stmt   = $mysqli->prepare($query);

    if (!$stmt) {
      $response->message = "Error inesperado, intentalo nuevamente";
      return $response;
    }

    try {
      $stmt->bind_param("i", $id);
      $stmt->execute();

      $result   = $stmt->get_result();
      $numRows  = $result->num_rows;

      if ($numRows === 0) throw new Exception("Sucursal no encontrada");

      $data = $result->fetch_assoc();
      $this->from($data);

      $response->status  = "success";
      $response->message = "Sucursal obtenida correctamente";

      return $response;
    } catch (Exception $te) {
      error_log("ERROR_SUCURSALES_MODEL::GET_BY_ID: {$te->getMessage()}");

      $response->status  = "error";
      $response->message = "Error al obtener la sucursal";

      return $response;
    }
  }
}
