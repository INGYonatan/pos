<?php
function category_families_get_by_id(
  $category_family_id
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      id_categoria_familia,
      id_categoria,
      familia,
      limite_descuento,
      cantidad_mayoreo,
      precio_mayoreo
    FROM
      {$db_dti}_categoria_familias
    where
      id_categoria_familia = ?
  ";

  $stmt = $mysqli->prepare($query);

  $stmt->bind_param('i', $category_family_id);
  $stmt->execute();

  $query_result = $stmt->get_result();
  $num_rows     = $query_result->num_rows;

  if ($num_rows == 0) return false;

  $category_family = new stdClass();

  $data = mysqli_fetch_assoc($query_result);

  $category_family->id                  = $data['id_categoria_familia'];
  $category_family->category_id         = $data['id_categoria'];
  $category_family->name                = $data['familia'];
  $category_family->discount_limit      = $data['limite_descuento'];
  $category_family->wholesale_quantity  = $data['cantidad_mayoreo'];
  $category_family->wholesale_price     = $data['precio_mayoreo'];

  return $category_family;
}
