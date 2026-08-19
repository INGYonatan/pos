<?php
function customer_get_by_id(
  $customer_id
) {
  global $mysqli;
  global $db_dti;

  if (!$customer_id) return false;

  $query = "SELECT
      id_cliente,
      id_regimen_fiscal,
      nombre_completo,
      nombre_comercial,
      requiere_factura,
      tipo,
      razon_social,
      rfc,
      domicilio_fiscal,
      correo,
      telefono,
      fecha_creacion,
      DATE_FORMAT(fecha_creacion, '%d-%m-%Y %h:%i %p') AS fecha_creacion_formato,
      status,
      limite_credito,
      limite_credito_plazo
    FROM
      {$db_dti}_clientes
    WHERE
      id_cliente = ?
  ";

  $stmt = $mysqli->prepare($query);

  $stmt->bind_param('i', $customer_id);
  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return false;

  $customer = new stdClass();

  $data = mysqli_fetch_assoc($query_result);

  $customer->id                 = $data['id_cliente'];
  $customer->taxRegimeId        = $data['id_regimen_fiscal'];
  $customer->name               = $data['nombre_completo'];
  $customer->commercialName     = $data['nombre_comercial'];
  $customer->requireInvoice     = $data['requiere_factura'] === 'si' ? true : false;
  $customer->type               = $data['tipo'];
  $customer->businessName       = $data['razon_social'];
  $customer->rfc                = $data['rfc'];
  $customer->taxResidence       = $data['domicilio_fiscal'];
  $customer->email              = $data['correo'];
  $customer->phone              = $data['telefono'];
  $customer->creationDate       = $data['fecha_creacion'];
  $customer->creationDateFormat = $data['fecha_creacion_formato'];
  $customer->status             = $data['status'];
  $customer->creditLimit        = $data['limite_credito'];
  $customer->creditLimitTerm    = $data['limite_credito_plazo'];

  return $customer;
}

class CustomerHelper
{
  private $id;
  private $taxRegimeId;
  private $name;
  private $commercialName;
  private $requireInvoice;
  private $type;
  private $businessName;
  private $rfc;
  private $taxResidence;
  private $email;
  private $phone;
  private $creditLimit;
  private $creditLimitTerm;
  private $creationDate;
  private $creationDateFormat;
  private $status;

  public function __construct()
  {
    $this->id                 = 0;
    $this->taxRegimeId        = 0;
    $this->name               = "";
    $this->commercialName     = "";
    $this->requireInvoice     = "";
    $this->type               = "";
    $this->businessName       = "";
    $this->rfc                = "";
    $this->taxResidence       = "";
    $this->email              = "";
    $this->phone              = "";
    $this->creditLimit        = 0.0;
    $this->creditLimitTerm    = 0;
    $this->creationDate       = "";
    $this->creationDateFormat = "";
    $this->status             = "";
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getTaxRegimeId()
  {
    return $this->taxRegimeId;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getCommercialName()
  {
    return $this->commercialName;
  }

  public function getRequireInvoice()
  {
    return $this->requireInvoice;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getBusinessName()
  {
    return $this->businessName;
  }

  public function getRfc()
  {
    return $this->rfc;
  }

  public function getTaxResidence()
  {
    return $this->taxResidence;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function getPhone()
  {
    return $this->phone;
  }

  public function getCreditLimit()
  {
    return $this->creditLimit;
  }

  public function getCreditLimitTerm()
  {
    return $this->creditLimitTerm;
  }

  public function getCreationDate()
  {
    return $this->creationDate;
  }

  public function getCreationDateFormat()
  {
    return $this->creationDateFormat;
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

  public function setTaxRegimeId($taxRegimeId)
  {
    $this->taxRegimeId = $taxRegimeId;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setCommercialName($commercialName)
  {
    $this->commercialName = $commercialName;
  }

  public function setRequireInvoice($requireInvoice)
  {
    $this->requireInvoice = $requireInvoice;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  public function setBusinessName($businessName)
  {
    $this->businessName = $businessName;
  }

  public function setRfc($rfc)
  {
    $this->rfc = $rfc;
  }

  public function setTaxResidence($taxResidence)
  {
    $this->taxResidence = $taxResidence;
  }

  public function setEmail($email)
  {
    $this->email = $email;
  }

  public function setPhone($phone)
  {
    $this->phone = $phone;
  }

  public function setCreditLimit($creditLimit)
  {
    $this->creditLimit = $creditLimit;
  }

  public function setCreditLimitTerm($creditLimitTerm)
  {
    $this->creditLimitTerm = $creditLimitTerm;
  }

  public function setCreationDate($creationDate)
  {
    $this->creationDate = $creationDate;
  }

  public function setCreationDateFormat($creationDateFormat)
  {
    $this->creationDateFormat = $creationDateFormat;
  }

  public function setStatus($status)
  {
    $this->status = $status;
  }

  /**
   * Methods
   */
  public function from($data)
  {
    $this->setId($data['id_cliente']);
    $this->setTaxRegimeId($data['id_regimen_fiscal']);
    $this->setName($data['nombre_completo']);
    $this->setCommercialName($data['nombre_comercial']);
    $this->setRequireInvoice($data['requiere_factura']);
    $this->setType($data['tipo']);
    $this->setBusinessName($data['razon_social']);
    $this->setRfc($data['rfc']);
    $this->setTaxResidence($data['domicilio_fiscal']);
    $this->setEmail($data['correo']);
    $this->setPhone($data['telefono']);
    $this->setCreditLimit($data['limite_credito']);
    $this->setCreditLimitTerm($data['limite_credito_plazo']);
    $this->setCreationDate($data['fecha_creacion']);
    $this->setCreationDateFormat($data['fecha_creacion_formato']);
    $this->setStatus($data['status']);
  }

  public function get($customerId)
  {
    global $mysqli;
    global $db_dti;

    if (!$customerId) return false;

    $query = "SELECT
        id_cliente,
        id_regimen_fiscal,
        nombre_completo,
        nombre_comercial,
        requiere_factura,
        tipo,
        razon_social,
        rfc,
        domicilio_fiscal,
        correo,
        telefono,
        limite_credito,
        limite_credito_plazo,
        fecha_creacion,
        DATE_FORMAT(fecha_creacion, '%d-%m-%Y %h:%i %p') AS fecha_creacion_formato,
        status
      FROM
        {$db_dti}_clientes
      WHERE
        id_cliente = ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $customerId);
    $stmt->execute();

    $result = $stmt->get_result();
    $numRows = $result->num_rows;

    if ($numRows == 0) return false;

    $data = mysqli_fetch_assoc($result);

    $this->from($data);
  }

  /**
   * Obtiene un array de objetos CustomerHelper para Select2.
   *
   * @param string $term El término de búsqueda.
   * @param string $value Un valor opcional adicional.
   * @return CustomerHelper[] Un array de objetos CustomerHelper.
   */

  public function getForSelect2($term, $value = ""): array
  {
    global $mysqli;
    global $db_dti;

    $catalog = [];

    $query = "SELECT
        id_cliente,
        id_regimen_fiscal,
        nombre_completo,
        nombre_comercial,
        requiere_factura,
        tipo,
        razon_social,
        rfc,
        domicilio_fiscal,
        correo,
        telefono,
        fecha_creacion,
        DATE_FORMAT(fecha_creacion, '%d-%m-%Y %h:%i %p') AS fecha_creacion_formato,
        limite_credito,
        limite_credito_plazo,
        status
      FROM
        {$db_dti}_clientes
      WHERE
        status = 'activo' AND
        nombre_completo LIKE _utf8 '%{$term}%' collate utf8_unicode_ci
      ORDER BY
        nombre_completo
    ";

    $result   = mysqli_query($mysqli, $query);
    $numRows  = mysqli_num_rows($result);

    if ($numRows > 0) :
      while ($data = mysqli_fetch_assoc($result)) :
        $item = new CustomerHelper();
        $item->from($data);

        array_push($catalog, $item);
      endwhile;
    endif;

    return $catalog;
  }

  public function getByMd5Id($md5Id)
  {
    global $mysqli;
    global $db_dti;

    if (!$md5Id) return false;

    $query = "SELECT
        id_cliente,
        id_regimen_fiscal,
        nombre_completo,
        nombre_comercial,
        requiere_factura,
        tipo,
        razon_social,
        rfc,
        domicilio_fiscal,
        correo,
        telefono,
        limite_credito,
        limite_credito_plazo,
        fecha_creacion,
        DATE_FORMAT(fecha_creacion, '%d-%m-%Y %h:%i %p') AS fecha_creacion_formato,
        status
      FROM
        {$db_dti}_clientes
      WHERE
        MD5(id_cliente) = ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $md5Id);
    $stmt->execute();

    $result = $stmt->get_result();
    $numRows = $result->num_rows;

    if ($numRows == 0) return false;

    $data = mysqli_fetch_assoc($result);

    $this->from($data);
  }
}
