<?php
require '../lib/settings.inc.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'cortes';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search           = cleanStr($_POST['search']);
    $id_sucursal      = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();
    $fecha_inicio     = cleanStr($_POST['fecha_inicio']);
    $fecha_fin        = cleanStr($_POST['fecha_fin']);

    $column_id        = "CC.id_corte_caja";
    $c_from           = "{$db_dti}_cortes_caja AS CC";
    $c_extra_clauses  = "ORDER BY CC.id_corte_caja DESC";

    $fields = [
      "CC.id_corte_caja",
      ["id_corte_caja", "uid"],
      "CC.folio",
      "CC.id_usuario",
      "CC.id_sucursal",
      "CC.total",
      "CC.fecha_desde",
      ["DATE_FORMAT(CC.fecha_desde, '%d-%m-%Y %h:%i %p')", "fecha_desde_formato"],
      "CC.fecha_hasta",
      ["DATE_FORMAT(CC.fecha_hasta, '%d-%m-%Y %h:%i %p')", "fecha_hasta_formato"],
      "S.nombre_sucursal",
      "U.nombre_completo"
    ];

    $c_join = "
      LEFT JOIN {$db_dti}_sucursales  AS S  ON (CC.id_sucursal = S.id_sucursal)
      LEFT JOIN {$db_ati}_usuarios    AS U  ON (CC.id_usuario  = U.id_usuario)
    ";

    $c_where = [];

    if (!empty($search))      array_push($c_where, ["CC.folio",  "%$search%", "LIKE"]);
    if (!empty($id_sucursal)) array_push($c_where, ["CC.id_sucursal",  "{$id_sucursal}"]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(CC.fecha_desde, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(CC.fecha_desde, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(CC.fecha_desde, '%d-%m-%Y'))", $fecha_fin]);
    // if (!empty($fecha))       array_push($c_where, ["(DATE_FORMAT(CC.fecha_creacion, '%d-%m-%Y'))",  "{$fecha}"]);

    $request = useDataTable([
      'column_id'     => $column_id,
      'from'          => $c_from,
      'where'         => $c_where,
      'fields'        => $fields,
      'join'          => $c_join,
      'extra_clauses' => $c_extra_clauses,
      'per_page'      => $per_page,
      'page'          => $page
    ]);

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';
    die;
    break;

  case 'action-cancelar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'cancelar')) :
      $id_corte_caja = cleanStr($_POST['uid']);

      $query = "UPDATE {$db_dti}_cortes_caja SET
          status = 'cancelado'
        WHERE
          id_corte_caja = {$id_corte_caja}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'La ajuste se canceló correctamente',
        'callback'      => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;

  case 'action-corte-caja-' . $identifier:
    if (checkModuleActionPermission($identifier, 'corte-caja')) :

    endif;
    break;
};

//$response['post'] = json_encode($_POST);

echo json_encode($response);
mysqli_close($mysqli);
die;
