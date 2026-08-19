<?php
class GCatalogsHelpers
{
  function getTaxRegime(
    $value = ""
  ) {
    global $mysqli;
    global $db_dti;

    $catalog = "";

    $query = "SELECT
        id_regimen_fiscal  AS id,
        concat(id_regimen_fiscal,' - ',regimen_fiscal)     AS label
      FROM
        regimen_fiscal
      ORDER BY
        id_regimen_fiscal
    ";

    $queryResult  = mysqli_query($mysqli, $query);
    $numRows      = mysqli_num_rows($queryResult);

    if ($numRows) :
      while ($row = mysqli_fetch_assoc($queryResult)) :
        $id       = $row['id'];
        $label    = $row['label'];
        $selected = $id == $value ? "selected" : "";

        $catalog .= "<option value='{$id}' {$selected}>{$label}</option>";
      endwhile;
    endif;

    return $catalog;
  }

  function getCFDICatalog(
    $value = "",
    $disabled = false
  ) {
    global $mysqli;
    global $db_dti;

    $catalog = "";

    $query = "SELECT
        id                                  AS id,
        CONCAT(uso_cfdi, ' ', descripcion)  AS label
      FROM
        uso_cfdi
      ORDER BY
        CONCAT(uso_cfdi, ' ', descripcion)
    ";

    $queryResult  = mysqli_query($mysqli, $query);
    $numRows      = mysqli_num_rows($queryResult);

    if ($numRows) :
      while ($row = mysqli_fetch_assoc($queryResult)) :
        $id       = $row['id'];
        $label    = $row['label'];
        $selected = $id == $value ? "selected" : "";
        $disabledOption = ($disabled && !$selected) ? "disabled" : "";

        $catalog .= "<option value='{$id}' {$selected} {$disabledOption}>{$label}</option>";
      endwhile;
    endif;

    return $catalog;
  }

  function getConcepts(
    $value = ""
  ) {
    global $mysqli;
    global $db_dti;

    $catalog = "";

    $query = "SELECT
        id_concepto  AS id,
        concepto     AS label
      FROM
        {$db_dti}_conceptos
      ORDER BY
        concepto
    ";

    $queryResult  = mysqli_query($mysqli, $query);
    $numRows      = mysqli_num_rows($queryResult);

    if ($numRows) :
      while ($row = mysqli_fetch_assoc($queryResult)) :
        $id       = $row['id'];
        $label    = $row['label'];
        $selected = $id == $value ? "selected" : "";

        $catalog .= "<option value='{$id}' {$selected}>{$label}</option>";
      endwhile;
    endif;

    return $catalog;
  }

  public function getPaymentMethods(
    $value = ""
  ) {
    global $mysqli;
    global $db_dti;

    $catalog = "";

    $query = "SELECT
        id AS id,
        CONCAT(forma_pago,'- ',descripcion) AS label
      FROM
        {$db_dti}_formas_pago
      ORDER BY
        forma_pago
    ";

    $queryResult  = mysqli_query($mysqli, $query);
    $numRows      = mysqli_num_rows($queryResult);

    if ($numRows) :
      while ($row = mysqli_fetch_assoc($queryResult)) :
        $id       = $row['id'];
        $label    = $row['label'];
        $selected = $id == $value ? "selected" : "";

        $catalog .= "<option value='{$id}' {$selected}>{$label}</option>";
      endwhile;
    endif;

    return $catalog;
  }
}
