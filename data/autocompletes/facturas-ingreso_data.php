<?php
include '../lib/settings.inc.php';

$term    = cleanStr($_GET['term']);
$value   = cleanStr($_GET['value']);

$catalog = new stdClass();
$catalog->results = [];

$catalog->pagination = new stdClass();
$catalog->pagination->more = false;

$id    = "";
$label = "--Seleccionar--";

$result       = new stdClass();
$result->id   = $id;
$result->text = $label;

$catalog->results[] = $result;
$catalog->selectedValue = $value;

$cWhere = !empty($value) ? "UUID = {$value}" : "
    UUID  LIKE _utf8'%{$term}%' collate utf8_unicode_ci OR
    Folio LIKE _utf8'%{$term}%' collate utf8_unicode_ci
";

//if (!empty($term)) :
$query = "SELECT
    id_factura,
    uuid,
    serie,
    folio,
    total
  FROM {$db_dti}_facturas
  WHERE
    cancelado = 0 AND
    ({$cWhere}) AND
    metodo_pago = 'PPD'
  LIMIT 10
";

$query_result = mysqli_query($mysqli, $query);
$num_rows     = mysqli_num_rows($query_result);

if ($num_rows > 0) :
  while ($row = mysqli_fetch_assoc($query_result)) :
    $result         = new stdClass();
    $result->id     = $row['uuid'];
    $result->text   = $row['serie'] . "-" . $row['folio'] . ' | ' . $row['uuid'];
    $result->folio  = $row['folio'];
    $result->amount = $row['total'];

    $queryPayments = "SELECT
        FPP.num_parcialidad,
        FPP.importe_saldo_anterior,
        FPP.importe_pagado,
        FPP.importe_saldo_insoluto
      FROM
        {$db_dti}_facturas_p_pagos AS FPP
      INNER JOIN
        {$db_dti}_facturas_p AS FP ON FPP.id_factura = FP.id_factura
      WHERE
        FP.id_factura_ingreso = '{$row['id_factura']}'
      ORDER BY
        FPP.num_parcialidad
      DESC
    ";

    $queryResult = mysqli_query($mysqli, $queryPayments);
    $numRows     = mysqli_num_rows($queryResult);

    if ($numRows == 0):
      $result->num_parcialidad           = 1;
      $result->importe_saldo_anterior    = $row['total'];
      $result->importe_pagado            = 0;
    endif;

    if ($numRows > 0) :
      $rowPayment = mysqli_fetch_assoc($queryResult);

      $result->num_parcialidad           = $rowPayment['num_parcialidad'] + 1;
      $result->importe_saldo_anterior    = $rowPayment['importe_saldo_insoluto'];
      //$result->importe_pagado            = $rowPayment['importe_pagado'];
      $result->importe_pagado            = 0;
    endif;

    $catalog->results[] = $result;
  endwhile;
endif;
//endif;

echo json_encode($catalog);
die;
