<?php
function catalog_get_tax_regime(
  $value        = '',
  $placeholder  = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      id_regimen_fiscal  AS id,
      regimen_fiscal AS label
    FROM
      regimen_fiscal
    ORDER BY
      regimen_fiscal
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function catalog_get_state(
  $value        = '',
  $placeholder  = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      idEstado  AS id,
      Estado    AS label
    FROM
      estados
    ORDER BY
      Estado
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function catalog_get_customer_addresses(
  $customer_id,
  $value        = '',
  $placeholder  = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query        = "SELECT COUNT(id_cliente_direccion) AS total FROM {$db_dti}_cliente_direcciones WHERE id_cliente = {$customer_id}";
  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);
  $total        = $data['total'];

  if ($total == 0) return $response;

  $per_page         = $total;
  $page             = 1;

  $column_id        = "id_cliente_direccion";
  $c_from           = "{$db_dti}_cliente_direcciones AS CD";
  $c_extra_clauses  = "ORDER BY id_cliente_direccion DESC";

  $fields = [
    "CD.id_cliente_direccion",
    ["CD.id_cliente_direccion", "uid"],
    "CD.id_cliente",
    "CD.nombre_comercial",
    "CD.id_estado",
    "CD.id_ciudad",
    "CD.id_colonia",
    "CD.codigo_postal",
    "CD.calle",
    "CD.n_exterior",
    "CD.n_interior",
    "CD.entre_calles",
    "CD.referencias",
    ["E.Estado", "estado"],
    ["M.Municipio", "ciudad"],
    ["CO.Colonia", "colonia"]
  ];

  $c_join = "
    LEFT JOIN
      estados AS E ON (E.idEstado = CD.id_estado)
    LEFT JOIN
      municipios AS M ON (M.idMunicipio = CD.id_ciudad)
    LEFT JOIN
      colonias AS CO ON (CO.idColonia = CD.id_colonia)
  ";

  $c_where = [
    ['CD.id_cliente', $customer_id],
    ['CD.status', 'activo']
  ];

  if (!empty($search)) array_push($c_where, [
    [
      ["CD.codigo_postal", "%$search%", "LIKE", "OR"],
      ["CD.calle", "%$search%", "LIKE", "OR"],
      ["E.estado", "%$search%", "LIKE", "OR"],
      ["M.Municipio", "%$search%", "LIKE", "OR"],
      ["CO.colionia", "%$search%", "LIKE", "OR"]
    ]
  ]);

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

  if ($request['status'] === 'success') :
    while ($row = mysqli_fetch_assoc($request['query_result'])) :
      $id       = $row['id_cliente_direccion'];
      $label    = "{$row['nombre_comercial']} | {$row['calle']} {$row['numero_exterior']} Barrio {$row['colonia']} {$row['codigo_postal']} {$row['ciudad']}, {$row['estado']}";
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function catalog_get_brand_categories(
  $brandId,
  $value = '',
  $placeholder = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      id_categoria  AS id,
      categoria               AS label
    FROM
      {$db_dti}_categorias
    WHERE
      id_marca = {$brandId}
    ORDER BY
      categoria
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function catalog_get_families(
  $category_id,
  $value = '',
  $placeholder = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      id_categoria_familia  AS id,
      familia               AS label
    FROM
      {$db_dti}_categoria_familias
    WHERE
      id_categoria = {$category_id}
    ORDER BY
      familia
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}
