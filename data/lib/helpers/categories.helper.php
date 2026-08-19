<?php
function categories_get_by_id(
  $category_id
) {
  global $mysqli;
  global $db_dti;

  if (!$category_id) return false;

  $query = "SELECT
      id_categoria,
      categoria
    FROM
      {$db_dti}_categorias
    WHERE
      id_categoria = {$category_id}
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data           = mysqli_fetch_assoc($query_result);
  $category       = new stdClass();
  $category->id   = $data['id_categoria'];
  $category->name = $data['categoria'];

  return $category;
}
