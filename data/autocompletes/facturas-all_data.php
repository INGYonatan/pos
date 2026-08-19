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

$byUUID = !empty($value) ? "UUID = {$value}" : "1=1";;

$byTerm = !empty($term) ? "
    uuid  LIKE _utf8'%{$term}%' collate utf8_unicode_ci OR
    folio LIKE _utf8'%{$term}%' collate utf8_unicode_ci OR
    serie LIKE _utf8'%{$term}%' collate utf8_unicode_ci
" : "1=1";

$cWhere = "
  cancelado = 0 AND
  ( 
    ({$byUUID}) AND
    ({$byTerm})
  )
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
    ({$cWhere})
  ORDER BY
    id_factura DESC
  LIMIT 10
";

error_log($query);

$query_result = mysqli_query($mysqli, $query);
$num_rows     = mysqli_num_rows($query_result);

if ($num_rows > 0) :
  while ($row = mysqli_fetch_assoc($query_result)) :
    $result         = new stdClass();
    $result->id     = $row['uuid'];
    $result->text   = "(Ingreso) ::: " . $row['serie'] . "-" . $row['folio'] . ' | ' . $row['uuid'];
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

// Hacer la búsqueda de facturas pero ahora con los de tipo anticipo, es practicamente el mismo código, pero cambiando la tabla
$query = "SELECT
    id_factura,
    uuid,
    serie,
    folio,
    total
  FROM {$db_dti}_facturas_anticipo_compra
  WHERE
    ({$cWhere})
  ORDER BY
    id_factura DESC
  LIMIT 10
";

$query_result = mysqli_query($mysqli, $query);
$num_rows     = mysqli_num_rows($query_result);

if ($num_rows > 0) :
  while ($row = mysqli_fetch_assoc($query_result)) :
    $result         = new stdClass();
    $result->id     = $row['uuid'];
    $result->text   = "(Anticipo) ::: " . $row['serie'] . "-" . $row['folio'] . ' | ' . $row['uuid'];
    $result->folio  = $row['folio'];
    $result->amount = $row['total'];

    $catalog->results[] = $result;
  endwhile;
endif;

// Ahora la búsqueda con las facturas de notas de crédito
$query = "SELECT
    id_factura,
    uuid,
    serie,
    folio,
    total
  FROM {$db_dti}_facturas_nota_credito
  WHERE
    ({$cWhere})
  ORDER BY
    id_factura DESC
  LIMIT 10
";

$query_result = mysqli_query($mysqli, $query);
$num_rows     = mysqli_num_rows($query_result);

if ($num_rows > 0) :
  while ($row = mysqli_fetch_assoc($query_result)) :
    $result         = new stdClass();
    $result->id     = $row['uuid'];
    $result->text   = "(Nota de Crédito) ::: " . $row['serie'] . "-" . $row['folio'] . ' | ' . $row['uuid'];
    $result->folio  = $row['folio'];
    $result->amount = $row['total'];

    $catalog->results[] = $result;
  endwhile;
endif;

// Ahora la búsqueda con las facturas de pago
$query = "SELECT
    id_factura,
    uuid,
    serie,
    folio
  FROM {$db_dti}_facturas_p
  WHERE
    ({$cWhere})
  ORDER BY
    id_factura DESC
  LIMIT 10
";

$query_result = mysqli_query($mysqli, $query);
$num_rows     = mysqli_num_rows($query_result);

if ($num_rows > 0) :
  while ($row = mysqli_fetch_assoc($query_result)) :
    $result         = new stdClass();
    $result->id     = $row['uuid'];
    $result->text   = "(Pago) ::: " . $row['serie'] . "-" . $row['folio'] . ' | ' . $row['uuid'];
    $result->folio  = $row['folio'];

    $catalog->results[] = $result;
  endwhile;
endif;

echo json_encode($catalog);
die;
