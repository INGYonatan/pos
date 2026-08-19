<?php
class FacturacionHelpers
{
  public function getUsoCFDIKey($id)
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        uso_cfdi AS clave
      FROM
        {$db_dti}_uso_cfdi
      WHERE
        id = ?
      LIMIT 1
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows) :
      $data = $result->fetch_assoc();
      return $data['clave'];
    endif;
  }

  public function getFormaPagoKey($id)
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        forma_pago AS clave
      FROM
        {$db_dti}_formas_pago
      WHERE
        id = ?
      LIMIT 1
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows) :
      $data = $result->fetch_assoc();
      return $data['clave'];
    endif;
  }

  public function getFormaPagoLabel($id)
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        descripcion AS label
      FROM
        {$db_dti}_formas_pago
      WHERE
        id = ?
      LIMIT 1
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows) :
      $data = $result->fetch_assoc();
      return $data['label'];
    endif;
  }

  public function getkeyProductoServicio($id)
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        clave AS clave
      FROM
        {$db_dti}_clave_producto_servicios
      WHERE
        id_clave_producto_servicio = ?
      LIMIT 1
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows) :
      $data = $result->fetch_assoc();
      return $data['clave'];
    endif;
  }

  public function getKeyUnidad($id)
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        clave AS clave
      FROM
        {$db_dti}_clave_unidades
      WHERE
        id_clave_unidad = ?
      LIMIT 1
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows) :
      $data = $result->fetch_assoc();
      return $data['clave'];
    endif;
  }

  public function getNameProductoServicio($id)
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        descripcion AS label
      FROM
        {$db_dti}_clave_producto_servicios
      WHERE
        id_clave_producto_servicio = ?
      LIMIT 1
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows) :
      $data = $result->fetch_assoc();
      return $data['label'];
    endif;
  }

  public function getNameUnidad($id)
  {
    global $mysqli;
    global $db_dti;

    $query = "SELECT
        nombre AS label
      FROM
        {$db_dti}_clave_unidades
      WHERE
        id_clave_unidad = ?
      LIMIT 1
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows) :
      $data = $result->fetch_assoc();
      return $data['label'];
    endif;
  }
}
