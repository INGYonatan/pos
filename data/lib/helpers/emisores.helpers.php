<?php
/* 
id_emisor*
tipo (fisica, moral)*
rfc*
nombre_razon_social*
regimen_fiscal*
archivo_key
archivo_cer
dirección (Toda la dirección del domicilio fiscal)
no_certificado
*/
class EmisoresHelper
{
  private $id;
  private $type;
  private $rfc;
  private $businessName;
  private $fiscalRegimeId;
  private $keyFile;
  private $cerFile;
  private $address;
  private $certificateNumber;
  private $fiscalRegimeText;
  private $postalCode;

  public function __construct()
  {
    $this->id                 = 0;
    $this->type               = "fisica";
    $this->rfc                = "";
    $this->businessName       = "";
    $this->fiscalRegimeId     = 0;
    $this->keyFile            = "";
    $this->cerFile            = "";
    $this->address            = "";
    $this->certificateNumber  = "";
    $this->fiscalRegimeText   = "";
    $this->postalCode         = "";
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getRfc()
  {
    return $this->rfc;
  }

  public function getBusinessName()
  {
    return $this->businessName;
  }

  public function getFiscalRegimeId()
  {
    return $this->fiscalRegimeId;
  }

  public function getKeyFile()
  {
    return $this->keyFile;
  }

  public function getCerFile()
  {
    return $this->cerFile;
  }

  public function getAddress()
  {
    return $this->address;
  }

  public function getCertificateNumber()
  {
    return $this->certificateNumber;
  }

  public function getFiscalRegimeText()
  {
    return $this->fiscalRegimeText;
  }

  public function getPostalCode()
  {
    return $this->postalCode;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  public function setRfc($rfc)
  {
    $this->rfc = $rfc;
  }

  public function setBusinessName($businessName)
  {
    $this->businessName = $businessName;
  }

  public function setFiscalRegimeId($fiscalRegimeId)
  {
    $this->fiscalRegimeId = $fiscalRegimeId;
  }

  public function setKeyFile($keyFile)
  {
    $this->keyFile = $keyFile;
  }

  public function setCerFile($cerFile)
  {
    $this->cerFile = $cerFile;
  }

  public function setAddress($address)
  {
    $this->address = $address;
  }

  public function setCertificateNumber($certificateNumber)
  {
    $this->certificateNumber = $certificateNumber;
  }

  public function setFiscalRegimeText($fiscalRegimeText)
  {
    $this->fiscalRegimeText = $fiscalRegimeText;
  }

  public function setPostalCode($postalCode)
  {
    $this->postalCode = $postalCode;
  }

  /**
   * Methods
   */
  public function from($data)
  {
    $this->id                 = $data['id_emisor'];
    $this->type               = $data['tipo'];
    $this->rfc                = $data['rfc'];
    $this->businessName       = $data['nombre_razon_social'];
    $this->fiscalRegimeId     = $data['regimen_fiscal'];
    $this->keyFile            = $data['archivo_key_pem'];
    $this->cerFile            = $data['archivo_cer'];
    $this->address            = $data['direccion'];
    $this->certificateNumber  = $data['no_certificado'];
    $this->fiscalRegimeText   = $data['regimen_fiscal_texto'];
    $this->postalCode         = $data['cp'];
  }

  public function get($id)
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        E.id_emisor,
        E.tipo,
        E.rfc,
        E.nombre_razon_social,
        E.regimen_fiscal,
        E.archivo_key_pem,
        E.archivo_cer,
        E.direccion,
        E.no_certificado,
        E.cp,
        R.regimen_fiscal AS regimen_fiscal_texto
      FROM
        {$db_dti}_emisores AS E
      LEFT JOIN
        regimen_fiscal AS R ON (R.id_regimen_fiscal = E.regimen_fiscal)
      WHERE
        E.id_emisor = {$id}
      LIMIT 1
    ";

    $queryResult  = mysqli_query($mysqli, $query);
    $numRows      = mysqli_num_rows($queryResult);

    if ($numRows > 0) :
      $data = mysqli_fetch_assoc($queryResult);
      $this->from($data);
    endif;
  }

  public function getCatalog($value = "")
  {
    global $mysqli;
    global $db_dti;

    $catalog = "";

    $query = "SELECT
        id_emisor           AS id,
        nombre_razon_social AS label
      FROM
        {$db_dti}_emisores
      WHERE
        status = 'activo'
      ORDER BY
        nombre_razon_social
      ASC
    ";

    $queryResult = mysqli_query($mysqli, $query);

    while ($data = mysqli_fetch_assoc($queryResult)) {
      $selected = ($value == $data['id']) ? "selected" : "";
      $catalog .= "<option value='{$data['id']}' {$selected}>{$data['label']}</option>";
    }

    return $catalog;
  }
}
