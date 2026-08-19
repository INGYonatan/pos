<?php
class ShoppingCart
{
  private $product_id;
  private $branch_id;
  private $use_sotck;
  private $use_ieps;
  private $product;
  private $cart;
  public $alert;

  public function __construct(
    $branch_id,
    $product_id,
    $cart,
    $use_sotck = true,
    $use_ieps = false
  ) {
    $this->branch_id  = $branch_id;
    $this->product_id = $product_id;
    $this->cart       = $cart;
    $this->use_sotck  = $use_sotck;
    $this->use_ieps   = $use_ieps;
    $this->product    = null;
    $this->alert      = new stdClass();
  }

  public function get_alert()
  {
    return $this->alert;
  }
  public function get_cart()
  {
    return $this->cart;
  }

  public function get_product()
  {
    $product_id = $this->product_id;
    $product    = $this->product;
    $list       = $this->cart->list;

    if (isset($list[$product_id])) {
      $productUpdate = $this->get_product_data();
      $list[$product_id]->wholesale_quantity = $productUpdate->wholesale_quantity;
      $list[$product_id]->wholesale_price    = $productUpdate->wholesale_price;
      return $list[$product_id];
    }
    if (isset($product)) return $product;
    $product = $this->get_product_data();
    $this->product = $product;
    return $product;
  }

  public function get_product_data()
  {
    global $mysqli;
    global $db_dti;

    $product_id = $this->product_id;
    $branch_id  = $this->branch_id;

    $query = "SELECT
        I.id_inventario,
        I.id_sucursal,
        I.id_producto,
        I.stock,
        S.nombre_sucursal,
        P.id_categoria_familia,
        P.nombre_producto,
        P.codigo,
        P.precio_venta,
        P.precio_venta2,
        P.precio_venta3,
        P.precio_costo,
        P.contenido,
        P.unidad,
        P.aplica_iva,
        P.aplica_ieps,
        P.ieps_porcentaje,
        P.cantidad_mayoreo,
        P.precio_mayoreo,
        P.en_dolares,
        P.precio_costo_original,
        P.precio_venta_original,
        P.precio_venta2_original,
        P.precio_venta3_original,
        P.precio_mayoreo_original,
        CF.limite_descuento,
        P.id_tipo,
        T.nombre AS tipo,
        T.requiere_numero_serie,
        P.control_inventario
      FROM {$db_dti}_inventario AS I
        LEFT JOIN {$db_dti}_sucursales          AS S  ON (I.id_sucursal           = S.id_sucursal)
        LEFT JOIN {$db_dti}_productos           AS P  ON (I.id_producto           = P.id_producto)
        LEFT JOIN {$db_dti}_categoria_familias  AS CF ON (P.id_categoria_familia  = CF.id_categoria_familia)
        LEFT JOIN {$db_dti}_tipos               AS T  ON (P.id_tipo               = T.id_tipo)
      WHERE
        I.id_sucursal = {$branch_id}  AND
        I.id_producto = {$product_id} AND
        S.status      = 'activo'
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);
    if ($num_rows == 0) return false;

    $data     = mysqli_fetch_assoc($query_result);
    $product  = new stdClass();

    $product->id                            = $data['id_producto'];
    $product->code                          = $data['codigo'];
    $product->name                          = $data['nombre_producto'];
    $product->content                       = $data['contenido'];
    $product->stock                         = $data['stock'];
    $product->unit                          = $data['unidad'];
    $product->unit_symbol                   = $data['unidad'] == 'Pieza' ? 'pzs.' : 'kg.';
    $product->have_iva                      = $data['aplica_iva'];
    $product->have_ieps                     = $data['aplica_ieps'] ?? 'no';
    $product->ieps_percentage               = (float)($data['ieps_porcentaje'] ?? 0);
    $product->wholesale_quantity            = $data['cantidad_mayoreo'];
    $product->wholesale_price               = $data['precio_mayoreo'];
    $product->requires_serial_number        = $data['requiere_numero_serie'];
    $product->inventory_control              = $data['control_inventario'];

    $product->sale_price                    = $data['precio_venta'];

    $product->sale_price1                   = $data['precio_venta'];
    $product->sale_price2                   = $data['precio_venta2'];
    $product->sale_price3                   = $data['precio_venta3'];

    $product->sale_price1_original          = $data['precio_venta_original'];
    $product->sale_price2_original          = $data['precio_venta2_original'];
    $product->sale_price3_original          = $data['precio_venta3_original'];

    $product->cost_price                    = $data['precio_costo'];
    $product->discount_limit                = $data['limite_descuento'];
    $product->category_family_id            = $data['id_categoria_familia'];
    $product->type                          = $data['tipo'];
    $product->type_id                       = $data['id_tipo'];

    $product->cart_quantity                 = 0;
    $product->serial_numbers                = [];

    $product->cart_base_sale_price          = 0;
    $product->cart_sale_price               = 0;
    $product->cart_sale_ieps                = 0;
    $product->cart_sale_iva                 = 0;
    $product->cart_sale_price_with_iva      = 0;
    $product->cart_sale_discount            = 0;
    $product->cart_sale_net_price           = 0;
    $product->cart_sale_amount              = 0;
    $product->cart_sale_amount_without_iva  = 0;
    $product->cart_sale_total_ieps          = 0;
    $product->cart_sale_total_iva           = 0;

    $product->cart_base_cost_price          = 0;
    $product->cart_cost_price               = 0;
    $product->cart_cost_ieps                = 0;
    $product->cart_cost_iva                 = 0;
    $product->cart_cost_price_with_iva      = 0;
    $product->cart_cost_discount            = 0;
    $product->cart_cost_net_price           = 0;
    $product->cart_cost_amount              = 0;
    $product->cart_cost_amount_without_iva  = 0;
    $product->cart_cost_total_ieps          = 0;
    $product->cart_cost_total_iva           = 0;

    // Para corrección de redondeo
    $product->cart_sale_amount_without_iva_unrounded = 0;
    $product->cart_sale_total_ieps_unrounded = 0;
    $product->cart_sale_total_iva_unrounded = 0;
    $product->cart_cost_amount_without_iva_unrounded = 0;
    $product->cart_cost_total_ieps_unrounded = 0;
    $product->cart_cost_total_iva_unrounded = 0;

    $product->comments = "";

    return $product;
  }

  // Esta función cambiará $product->>sale_price y actualizará los precios relacionados
  public function change_base_sale_price($new_base_price)
  {
    if ($new_base_price < 0) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "El precio no puede ser menor a cero";
      return;
    }

    $product                      = $this->get_product();

    // Validar que el nuevo precio venta no sea menor al precio venta menor permitido
    $salePrice1 = $product->sale_price1;
    $salePrice2 = $product->sale_price2;
    $salePrice3 = $product->sale_price3;

    //$haveIva = $product->have_iva === 'si' ? true : false;

    $prices = [$salePrice1, $salePrice2, $salePrice3];

    // ordenar prices del menor al mayor
    sort($prices);

    $allowed_min_price = $prices[0];

    if ($allowed_min_price !== null && $new_base_price < $allowed_min_price) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "El precio no puede ser menor al precio de venta menor permitido ($" . number_format($allowed_min_price, DECIMALS_CURRENCY) . ")";
      return;
    }

    // Asignarle a $product->sale_price el nuevo precio base
    $product->sale_price          = $new_base_price;

    $this->product                = $product;

    $this->define_product_base_price();
    $this->define_serial_numbers();
    $this->define_product_prices_and_totals();

    $this->alert->status        = "success";
    $this->alert->toastMessage  = "El precio se actualizó correctamente";
  }

  public function validate_product_quantity($quantity)
  {
    $product = $this->get_product();

    $inventory_control = $product->inventory_control == "si" ? $this->use_sotck : false;

    if ($product->unit === 'Pieza' && !(fmod($quantity, 1) == 0)) {
      $this->alert->status  = "error";
      $this->alert->toastMessage = "La cantidad para este producto no puede ser en decimales";
      return;
    }
    if ($inventory_control && $product->stock == 0) {
      $this->alert->status  = "error";
      $this->alert->toastMessage = "No hay productos en stock";
      return;
    }
    if ($inventory_control && $quantity > $product->stock) {
      $this->alert->status  = "error";
      $this->alert->toastMessage = "No hay productos suficientes en stock";
      return;
    }
    $product->cart_quantity = doubleval($quantity);
    $this->product = $product;
    return $product;
  }

  public function verify_family_quantity($coming_quantity = 0)
  {
    $cart               = $this->get_cart();
    $product            = $this->get_product();
    $category_family_id = $product->category_family_id;
    $list               = $cart->list;
    $quantity           = $coming_quantity;

    foreach ($list as $product) {
      if ($product->category_family_id && $product->category_family_id === $category_family_id) {
        $quantity = $quantity + $product->cart_quantity;
      }
    }

    return $quantity;
  }

  public function define_product_base_price($coming_quantity = null)
  {
    $cart               = $this->get_cart();
    $product            = $this->get_product();
    $list               = $cart->list;
    $category_family_id = $product->category_family_id;
    $wholesale_price    = $product->wholesale_price;
    $wholesale_quantity = $product->wholesale_quantity;

    $list[$product->id] = $product;
    $quantity           = $this->verify_family_quantity($coming_quantity);

    //if ($coming_quantity) $quantity = $quantity + $coming_quantity;

    $is_wholesale       = false;
    if ($quantity >= $wholesale_quantity) $is_wholesale = true;
    if ($wholesale_quantity == 0)         $is_wholesale = false;

    foreach ($list as $product) {
      if ($product->category_family_id === $category_family_id) {
        $sale_price = $product->sale_price;
        $cost_price = $product->cost_price;

        if ($is_wholesale)  $product->cart_base_sale_price = $wholesale_price;
        if (!$is_wholesale) $product->cart_base_sale_price = $sale_price;

        $product->cart_base_cost_price = $cost_price;
      }
    }

    $cart->list = $list;
    $this->cart = $cart;

    $product = $this->get_product();
    $product = $list[$product->id];
    $this->product = $product;
    return $product;
  }

  private function format_price($price)
  {
    $price = number_format($price, DECIMALS_CURRENCY - 2, '.', '');
    $price = number_format($price, DECIMALS_CURRENCY, '.', '');
    return $price;
  }

  public function get_price_without_iva($price)
  {
    return $price / 1.16;
  }
  public function get_price_with_discount($price, $discount)
  {
    return $price - (($discount * $price) / 100);
  }

  private function define_product_prices_and_totals($default_product = null)
  {
    $product  = $default_product ? $default_product : $this->get_product();
    $cart     = $this->get_cart();
    $list     = $cart->list;

    $quantity = $product->cart_quantity;
    $have_iva = $product->have_iva == 'si' ? true : false;
    $have_ieps = $this->use_ieps && ($product->have_ieps == 'si') ? true : false;

    $ieps_percentage = (float)($product->ieps_percentage ?? 0);
    $ieps_rate = ($have_ieps && $ieps_percentage > 0) ? ($ieps_percentage / 100) : 0;
    $iva_rate = $have_iva ? 0.16 : 0;

    $sale_discount_factor = 1 - (($product->cart_sale_discount ?? 0) / 100);
    $cost_discount_factor = 1 - (($product->cart_cost_discount ?? 0) / 100);

    if ($sale_discount_factor < 0) $sale_discount_factor = 0;
    if ($cost_discount_factor < 0) $cost_discount_factor = 0;

    $sale_tax_factor = (1 + $ieps_rate) * (1 + $iva_rate);
    $cost_tax_factor = $sale_tax_factor;

    // --- Precio venta ---
    $cart_base_sale_price = $product->cart_base_sale_price;
    $cart_sale_price_without_taxes = $sale_tax_factor > 0 ? ($cart_base_sale_price / $sale_tax_factor) : $cart_base_sale_price;
    $cart_sale_ieps_before_discount = $ieps_rate > 0 ? ($cart_sale_price_without_taxes * $ieps_rate) : 0;
    $cart_sale_iva_before_discount = $iva_rate > 0 ? (($cart_sale_price_without_taxes + $cart_sale_ieps_before_discount) * $iva_rate) : 0;

    $cart_sale_price      = $cart_sale_price_without_taxes * $sale_discount_factor;
    $cart_sale_ieps       = $cart_sale_ieps_before_discount * $sale_discount_factor;
    $cart_sale_iva        = $cart_sale_iva_before_discount * $sale_discount_factor;
    $cart_sale_price_with_iva = $cart_base_sale_price;
    $cart_sale_discount   = $product->cart_sale_discount;
    $cart_sale_net_price  = $this->get_price_with_discount($cart_sale_price_with_iva, $cart_sale_discount);
    $cart_sale_amount     = $cart_sale_net_price * $quantity;
    $cart_sale_amount_without_iva = $cart_sale_price * $quantity;
    $cart_sale_total_ieps  = $cart_sale_ieps * $quantity;
    $cart_sale_total_iva  = $cart_sale_iva * $quantity;

    // Sin redondear para sumas globales
    $product->cart_sale_amount_without_iva_unrounded = $cart_sale_amount_without_iva;
    $product->cart_sale_total_ieps_unrounded = $cart_sale_total_ieps;
    $product->cart_sale_total_iva_unrounded = $cart_sale_total_iva;

    // Redondeado para mostrar
    $product->cart_sale_price               = $this->format_price($cart_sale_price);
    $product->cart_sale_ieps                = $this->format_price($cart_sale_ieps);
    $product->cart_sale_iva                 = $this->format_price($cart_sale_iva);
    $product->cart_sale_price_with_iva      = $this->format_price($cart_sale_price_with_iva);
    $product->cart_sale_discount            = $this->format_price($cart_sale_discount);
    $product->cart_sale_net_price           = $this->format_price($cart_sale_net_price);
    $product->cart_sale_amount              = $this->format_price($cart_sale_amount);
    $product->cart_sale_amount_without_iva  = $this->format_price($cart_sale_amount_without_iva);
    $product->cart_sale_total_ieps          = $this->format_price($cart_sale_total_ieps);
    $product->cart_sale_total_iva           = $this->format_price($cart_sale_total_iva);

    // --- Precio costo ---
    $cart_base_cost_price = $product->cart_base_cost_price;
    $cart_cost_price_without_taxes = $cost_tax_factor > 0 ? ($cart_base_cost_price / $cost_tax_factor) : $cart_base_cost_price;
    $cart_cost_ieps_before_discount = $ieps_rate > 0 ? ($cart_cost_price_without_taxes * $ieps_rate) : 0;
    $cart_cost_iva_before_discount = $iva_rate > 0 ? (($cart_cost_price_without_taxes + $cart_cost_ieps_before_discount) * $iva_rate) : 0;

    $cart_cost_price      = $cart_cost_price_without_taxes * $cost_discount_factor;
    $cart_cost_ieps       = $cart_cost_ieps_before_discount * $cost_discount_factor;
    $cart_cost_iva        = $cart_cost_iva_before_discount * $cost_discount_factor;
    $cart_cost_price_with_iva = $cart_base_cost_price;
    $cart_cost_discount   = $product->cart_cost_discount;
    $cart_cost_net_price  = $this->get_price_with_discount($cart_cost_price_with_iva, $cart_cost_discount);
    $cart_cost_amount     = $cart_cost_net_price * $quantity;
    $cart_cost_amount_without_iva = $cart_cost_price * $quantity;
    $cart_cost_total_ieps  = $cart_cost_ieps * $quantity;
    $cart_cost_total_iva  = $cart_cost_iva * $quantity;

    $product->cart_cost_amount_without_iva_unrounded = $cart_cost_amount_without_iva;
    $product->cart_cost_total_ieps_unrounded = $cart_cost_total_ieps;
    $product->cart_cost_total_iva_unrounded = $cart_cost_total_iva;

    $product->cart_cost_price               = $this->format_price($cart_cost_price);
    $product->cart_cost_ieps                = $this->format_price($cart_cost_ieps);
    $product->cart_cost_iva                 = $this->format_price($cart_cost_iva);
    $product->cart_cost_price_with_iva      = $this->format_price($cart_cost_price_with_iva);
    $product->cart_cost_discount            = $this->format_price($cart_cost_discount);
    $product->cart_cost_net_price           = $this->format_price($cart_cost_net_price);
    $product->cart_cost_amount              = $this->format_price($cart_cost_amount);
    $product->cart_cost_amount_without_iva  = $this->format_price($cart_cost_amount_without_iva);
    $product->cart_cost_total_ieps          = $this->format_price($cart_cost_total_ieps);
    $product->cart_cost_total_iva           = $this->format_price($cart_cost_total_iva);

    $this->product      = $product;
    $list[$product->id] = $product;
    $cart->list         = $list;
    $this->cart         = $cart;

    if (!$default_product) $this->define_products_base_prices();
    $this->define_cart_totals();
  }

  private function define_products_base_prices()
  {
    $cart = $this->cart;
    $list = $cart->list;
    foreach ($list as $product) {
      $this->define_product_prices_and_totals($product);
    }
  }

  // --- Corrección de redondeo ---
  private function define_cart_totals()
  {
    $cart = $this->cart;
    $list = $cart->list;

    $sale_subtotal  = 0;
    $sale_ieps      = 0;
    $sale_iva       = 0;
    $sale_rounding  = $cart->sale_rounding ?? 0;
    $sale_total     = 0;
    $cost_subtotal  = 0;
    $cost_ieps      = 0;
    $cost_iva       = 0;
    $cost_rounding  = $cart->cost_rounding ?? 0;
    $cost_total     = 0;
    $product_types  = [];

    foreach ($list as $product) {
      $sale_subtotal += (float)$product->cart_sale_amount_without_iva;
      $sale_ieps     += (float)($product->cart_sale_total_ieps ?? 0);
      $sale_iva      += (float)$product->cart_sale_total_iva;
      $cost_subtotal += (float)$product->cart_cost_amount_without_iva;
      $cost_ieps     += (float)($product->cart_cost_total_ieps ?? 0);
      $cost_iva      += (float)$product->cart_cost_total_iva;
      $product_type = $product->type;

      if (!in_array($product_type, $product_types)) array_push($product_types, $product_type);
    }

    if (count($product_types) == 1) $cart->product_type = $product_types[0];
    if (count($product_types) > 1)  $cart->product_type = 'mixto';

    $cart->sale_subtotal  = $this->format_price($sale_subtotal);
    $cart->sale_ieps      = $this->format_price($sale_ieps);
    $cart->sale_iva       = $this->format_price($sale_iva);
    $cart->sale_rounding  = $this->format_price($sale_rounding);
    $cart->sale_total     = $this->format_price($sale_subtotal + $sale_ieps + $sale_iva + $sale_rounding);

    $cart->cost_subtotal  = $this->format_price($cost_subtotal);
    $cart->cost_ieps      = $this->format_price($cost_ieps);
    $cart->cost_iva       = $this->format_price($cost_iva);
    $cart->cost_rounding  = $this->format_price($cost_rounding);
    $cart->cost_total     = $this->format_price($cost_subtotal + $cost_ieps + $cost_iva + $cost_rounding);

    $this->cart = $cart;
  }

  // Métodos para agregar, actualizar, incrementar producto
  public function add_product($quantity = 1)
  {
    $is_valid_quantity  = $this->validate_product_quantity($quantity);
    if (!$is_valid_quantity) return;
    $this->define_product_base_price($quantity);
    $this->define_serial_numbers();
    $this->define_product_prices_and_totals();
    $this->alert->status = 'success';
    $this->alert->toastMessage  = "El producto se agregó correctamente";
  }

  private function define_serial_numbers()
  {
    $product  = $this->get_product();
    $quantity = $product->cart_quantity;
    $serial_numbers = [];
    if ($product->requires_serial_number) {
      for ($i = 1; $i <= $quantity; $i++) {
        $serial_number = new stdClass();
        $serial_number->id      = "";
        $serial_number->number  = "";
        array_push($serial_numbers, $serial_number);
      }
    }
    $product->serial_numbers  = $serial_numbers;
    $this->product            = $product;
  }

  public function update_product_quantity($quantity = 1)
  {
    $is_valid_quantity  = $this->validate_product_quantity($quantity);
    if (!$is_valid_quantity) return;
    $this->define_product_base_price();
    $this->define_product_prices_and_totals();
    $this->alert->status        = "success";
    $this->alert->toastMessage  = "La cantidad se actualizó correctamente";
  }

  public function increase_product_quantity($quantity = 1)
  {
    $product      = $this->get_product();
    $new_quantity = $product->cart_quantity + $quantity;
    $this->update_product_quantity($new_quantity);
  }

  public function update_product_discount($discount = 0)
  {
    if ($discount < 0) {
      $this->alert->toastMessage = 'El precio no puede ser menor a cero';
      return;
    }
    if ($discount > 100) {
      $this->alert->toastMessage = 'El precio no puede ser mayor a cien';
      return;
    }
    $product                      = $this->get_product();
    $product->cart_sale_discount  = $discount;
    $product->cart_cost_discount  = $discount;
    $this->product                = $product;
    $this->define_product_prices_and_totals();
    $this->alert->status  = "success";
    $this->alert->toastMessage = "El descuento se aplicó correctamente";
  }

  public function update_product_comments($comments = "")
  {
    $product                      = $this->get_product();
    $product->comments            = $comments;
    $this->product                = $product;

    $this->cart->list[$product->id] = $product;

    $this->alert->status  = "success";
    $this->alert->toastMessage = "Los comentarios se actualizaron correctamente";
  }

  public function remove_product()
  {
    $product_id = $this->product_id;
    $cart       = $this->get_cart();
    $list       = $cart->list;
    $this->update_product_quantity(0);
    unset($list[$product_id]);
    $cart->list = $list;
    $this->cart = $cart;
    $this->define_cart_totals();
    $this->alert->status        = "success";
    $this->alert->toastMessage  = "El producto se removió correctamente";
  }

  public function update_cart_rounding($rounding = 0)
  {
    $cart = $this->get_cart();
    $cart->cost_rounding = $rounding;
    $cart->sale_rounding = $rounding;
    $this->cart = $cart;
    $this->define_cart_totals();
    $this->alert->status  = "success";
  }

  // Métodos de seriales y validaciones igual que tu código original...
  public function validate_serial_numbers()
  {
    $cart = $this->get_cart();
    $list = $cart->list;

    $response               = new stdClass();
    $response->status       = "error";
    $response->toastMessage = "";

    foreach ($list as $product) {
      $is_equipment = $product->requires_serial_number;

      if ($is_equipment) {
        $quantity                 = $product->cart_quantity;
        $serial_numbers           = $product->serial_numbers;
        $is_empty_serial_numbers  = isEmptyArray($serial_numbers);

        if ($is_empty_serial_numbers) {
          $response->toastMessage = "No hay números de serie en el producto {$product->name}";
          return $response;
        }
        if (count($serial_numbers) < $quantity) {
          $response->toastMessage = "Faltan números de serie en el producto {$product->name}";
          return $response;
        }
        foreach ($serial_numbers as $serial_number) {
          if (!$serial_number->number) {
            $response->toastMessage = "Hay números de serie vacíos en el producto {$product->name}";
            return $response;
          }
        }
      }
    }

    $response->status       = "success";
    $response->toastMessage = "Los números de serie están completos";
    return $response;
  }

  public function validate_available_serial_number(
    $serial_number,
    $originBranchId = null
  ) {
    global $mysqli;
    global $db_dti;

    $branch_id = $originBranchId ? $originBranchId : $this->branch_id;

    if (!$serial_number->number) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "El número de serie no puede quedar vacío";
      return false;
    }

    $product = $this->get_product();
    $number = $serial_number->number;

    $query = "SELECT
        id_producto_numero_serie
      FROM
        {$db_dti}_producto_numeros_serie
      WHERE
        id_producto   = {$product->id}  AND
        numero_serie  = '{$number}'     AND
        status        = 'disponible'    AND
        id_sucursal   = {$branch_id}
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows == 0) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "El número de serie {$number} no está disponible para su uso o no existe";
      return false;
    }

    $this->alert->status        = "success";
    $this->alert->toastMessage  = "";
    return true;
  }

  public function validate_for_increment_serial_number(
    $serial_number,
    $originBranchId = null
  ) {
    global $mysqli;
    global $db_dti;

    $branch_id = $originBranchId ? $originBranchId : $this->branch_id;

    if (!$serial_number->number) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "El número de serie no puede quedar vacío";
      return false;
    }

    $product = $this->get_product();
    $number = $serial_number->number;

    $query = "SELECT
        id_producto_numero_serie
      FROM
        {$db_dti}_producto_numeros_serie
      WHERE
        id_producto   = {$product->id}  AND
        numero_serie  = '{$number}'     AND
        id_sucursal   = {$branch_id}    AND
        status        != 'pendiente-de-ajuste'
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows > 0) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "El número de serie {$number} ya existe en el inventario o está en proceso de traspaso";
      return false;
    }

    $this->alert->status        = "success";
    $this->alert->toastMessage  = "";
    return true;
  }

  public function validate_for_decrement_serial_number(
    $serial_number,
    $originBranchId = null
  ) {
    global $mysqli;
    global $db_dti;

    $branch_id = $originBranchId ? $originBranchId : $this->branch_id;

    if (!$serial_number->number) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "El número de serie no puede quedar vacío";
      return false;
    }

    $product = $this->get_product();
    $number = $serial_number->number;

    $query = "SELECT
        id_producto_numero_serie
      FROM
        {$db_dti}_producto_numeros_serie
      WHERE
        id_producto   = {$product->id}  AND
        numero_serie  = '{$number}'     AND
        status        = 'disponible'    AND
        id_sucursal   = {$branch_id}
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows == 0) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "El número de serie {$number} no está disponible para su decremento o no existe";
      return false;
    }

    $this->alert->status        = "success";
    $this->alert->toastMessage  = "";
    return true;
  }

  public function update_serial_numbers(
    $ps_serial_numbers,
    $originBranchId = null,
    $type = "availables"
  ) {
    $branch_id = $originBranchId ? $originBranchId : $this->branch_id;

    $product    = $this->get_product();
    $cart       = $this->get_cart();
    $list       = $cart->list;

    $quantity       = $product->cart_quantity;
    $serial_numbers = [];
    $numbers        = [];

    if (count($ps_serial_numbers) < $quantity) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "Faltan números de serie";
      return false;
    }

    if (count($ps_serial_numbers) > $quantity) {
      $this->alert->status        = "error";
      $this->alert->toastMessage  = "Los números de serie sobrepasa la cantidad de productos";
      return false;
    }

    foreach ($ps_serial_numbers as $number) {
      $serial_number = new stdClass();
      $serial_number->id      = "";
      $serial_number->number  = $number;

      $in_array = in_array($number, $numbers);

      if ($in_array) {
        $this->alert->status        = "error";
        $this->alert->toastMessage  = "El número de serie {$number} está repetido";
        return false;
      }

      $is_valid = false;

      if ($type === "availables")     $is_valid = $this->validate_available_serial_number($serial_number, $branch_id);
      if ($type === "for-increment")  $is_valid = $this->validate_for_increment_serial_number($serial_number, $branch_id);
      if ($type === "for-decrement")  $is_valid = $this->validate_for_decrement_serial_number($serial_number, $branch_id);

      if (!$is_valid) return false;

      array_push($serial_numbers, $serial_number);
      array_push($numbers, $number);
    }

    $product->serial_numbers  = $serial_numbers;
    $this->product            = $product;
    $list[$product->id]       = $product;
    $cart->list               = $list;
    $this->cart               = $cart;

    $this->alert->toastMessage = "Los números de serie se actualizaron correctamente";
  }
}
