<?php
function get_cashcut_data(
  $cashcut_id
) {
  global $mysqli;
  global $db_ati;
  global $db_dti;

  $query = "SELECT
      CC.id_corte_caja,
      CC.id_usuario,
      CC.id_sucursal,
      CC.folio,
      CC.total,
      CC.fecha_desde,
      CC.fecha_hasta,
      S.nombre_sucursal,
      U.nombre_completo,
      DATE_FORMAT(CC.fecha_hasta, '%h:%i %p') AS ticket_hora,
      DATE_FORMAT(CC.fecha_hasta, '%d-%m-%Y') AS ticket_fecha
    FROM
      {$db_dti}_cortes_caja AS CC
    LEFT JOIN
      {$db_dti}_sucursales AS S ON (CC.id_sucursal = S.id_sucursal)
    LEFT JOIN
      {$db_ati}_usuarios AS U ON (CC.id_usuario = U.id_usuario)
    WHERE
      CC.id_corte_caja = $cashcut_id
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $cashcut_data = mysqli_fetch_assoc($query_result);

  $query = "SELECT
      id_venta
    FROM
      {$db_dti}_ventas
    WHERE
      id_corte_caja = {$cashcut_id}
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    $products         = [];
    $products_divisor = [];

    while ($row = mysqli_fetch_assoc($query_result)) :
      $sale_id  = $row['id_venta'];
      $list     = get_sale_products($sale_id);

      foreach ($list as $product) :
        $id       = $product->id;
        $in_list  = $products[$id];

        if (!$in_list) :
          $products[$id]         = $product;
          $products_divisor[$id] = 1;
        endif;

        if ($in_list) :
          $data = $products[$id];

          $old_price            = $data->cart_sale_net_price;
          $new_price            = $product->cart_sale_net_price;
          $final_price          = $old_price + $new_price;

          $old_quantity         = $data->cart_quantity;
          $new_quantity         = $product->cart_quantity;
          $final_quantity       = $old_quantity + $new_quantity;

          $old_total            = $data->cart_sale_amount;
          $new_total            = $product->cart_sale_amount;
          $final_total          = $old_total + $new_total;

          $data->final_price    = $final_price;
          $data->final_quantity = $final_quantity;
          $data->final_total    = $final_total;

          $products[$id]        = $data;
          $products_divisor[$id]++;
        endif;
      endforeach;
    endwhile;
  endif;
}
