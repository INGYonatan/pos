<?php
include '../lib/settings.inc.php';

$term       = cleanStr($_GET['term']);
$response   = [];

if (!empty($term)) :
  $query = "SELECT
      id_cliente,
      nombre_completo,
      limite_credito
    FROM {$db_dti}_clientes
    WHERE
      status = 'activo' AND
      nombre_completo LIKE _utf8'%$term%' collate utf8_unicode_ci
    LIMIT 10
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $label        = $row['nombre_completo'];
      $customerId   = $row['id_cliente'];

      // Obtener el total de las ventas a crédito activas y no pagadas
      $creditSaleTotal = getCreditSaleTotalByCustomerId($customerId);

      // Obtener el total pagado
      $totalPaid = getTotalBalancePaidByCustomerId($customerId);

      $creditLimit      = $row['limite_credito'];
      $creditBalance    = $creditSaleTotal - $totalPaid;
      $remainingCredit  = $creditLimit - $creditBalance;

      array_push($response, [
        'value'           => $row['nombre_completo'],
        'label'           => $label,
        'id_cliente'      => $row['id_cliente'],
        "creditLimit"     => "$" . number_format($creditLimit, DECIMALS_CURRENCY),
        "creditBalance"   => "$" . number_format($creditBalance, DECIMALS_CURRENCY),
        "remainingCredit" => "$" . number_format($remainingCredit, DECIMALS_CURRENCY),
      ]);
    endwhile;
  endif;
endif;

echo json_encode($response);
mysqli_close($mysqli);
die();
