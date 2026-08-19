<?php

/* $cart = new stdClass();
$product = new stdClass;

//$cart->list = $product[];
$cart->subtotal;
$cart->iva;
$cart->rouding;

$product->id;
$product->code;
$product->name;
$product->content;
$product->stock;
$product->quantity;
$product->unit;
$product->have_iva;
$product->quantity_wholesale;
$product->price_wholesale;
$product->price;
$product->final_price; */

class Cart
{
  private $product_id;
  private $branch_id;
  private $use_sotck;
  private $product;
  private $cart;
  public $alert;

  public function __construct(
    $branch_id,
    $product_id,
    $cart,
    $use_sotck = true,
    $product = null
  ) {
    $this->branch_id  = $branch_id;
    $this->product_id = $product_id;
    $this->cart       = $cart;
    $this->use_sotck  = $use_sotck;
    $this->product    = $product;

    $this->alert      = new stdClass();
  }

  public function get_price_iva(
    $price
  ) {
    $price_without_iva  = $this->get_price_without_iva($price);
    $iva                = $price - $price_without_iva;

    return $iva;
  }

  public function get_price_without_iva(
    $price
  ) {
    $amount = $price / 1.16;

    return $amount;
  }

  public function parse_discount_percentage(
    $price,
    $discount
  ) {
    if ($price == 0) return 0;

    $discount = ($discount * $price) / 100;

    return $discount;
  }

  public function validate_item_quantity(
    $quantity
  ) {
    $product = $this->get_product();

    if ($product->unit === 'Pieza' && !(fmod($quantity, 1) == 0)) :
      $this->alert->status  = "error";
      $this->alert->toastMessage = "La cantidad para este producto no puede ser en decimales";
      return false;
    endif;

    if ($this->use_sotck && $product->stock == 0) :
      $this->alert->status  = "error";
      $this->alert->toastMessage = "No hay products en stock";
      return false;
    endif;

    if ($this->use_sotck && $quantity > $product->stock) :
      $this->alert->status  = "error";
      $this->alert->toastMessage = "No hay products suficientes en stock";
      return false;
    endif;

    return true;
  }

  public function add_item(
    $quantity = 1
  ) {
    $cart     = $this->cart;
    $list     = $cart->list;

    $is_valid_quantity  = $this->validate_item_quantity($quantity);

    if (!$is_valid_quantity) return false;

    $this->verify_wholesale_price($quantity);

    $product            = $this->get_product();

    $product->quantity  = $quantity;
    $list[$product->id] = $product;
    $cart->list         = $list;

    $this->product      = $product;
    $this->cart         = $cart;

    $this->define_product_totals();
    $this->define_cart_totals();

    $this->alert->status  = "success";
    $this->alert->toastMessage = "El producto se agregó correctamente";
  }

  public function increase_product_quantity(
    $quantity = 1
  ) {
    $product      = $this->get_product();
    $new_quantity = $product->quantity + $quantity;

    $this->update_product_quantity($new_quantity);
  }

  public function update_product_quantity(
    $quantity = 1
  ) {
    $product      = $this->get_product();
    $cart         = $this->cart;
    $list         = $cart->list;

    #$new_quantity = $product->quantity + $quantity;

    $is_valid_quantity  = $this->validate_item_quantity($quantity);

    if (!$is_valid_quantity) return false;

    $this->verify_wholesale_price($quantity);

    $product            = $this->get_product();

    $product->quantity  = $quantity;
    $list[$product->id] = $product;
    $cart->list         = $list;

    $this->product      = $product;
    $this->cart         = $cart;

    $this->define_product_totals();
    $this->define_cart_totals();

    $this->alert->status  = "success";

    return true;
    # $this->alert->toastMessage = "El producto se agregó correctamente";
  }

  public function update_product_cost_price(
    $price = 0
  ) {
    if ($price < 0) :
      $this->alert->toastMessage = 'El precio no puede ser menor a cero';
      return;
    endif;

    $product              = $this->get_product();
    $cart                 = $this->cart;
    $list                 = $cart->list;

    $product->cost_price  = $price;

    $list[$product->id] = $product;
    $cart->list         = $list;

    $this->product      = $product;
    $this->cart         = $cart;

    $this->define_product_totals();
    $this->define_cart_totals();

    $this->alert->status  = "success";
  }

  public function update_product_discount(
    $discount = 0
  ) {
    if ($discount < 0) :
      $this->alert->toastMessage = 'El precio no puede ser menor a cero';
      return;
    endif;

    if ($discount > 100) :
      $this->alert->toastMessage = 'El precio no puede ser mayor a cien';
      return;
    endif;

    $product              = $this->get_product();
    $cart                 = $this->cart;
    $list                 = $cart->list;

    $product->discount  = $discount;

    $list[$product->id] = $product;
    $cart->list         = $list;

    $this->product      = $product;
    $this->cart         = $cart;

    $this->define_product_totals();
    $this->define_cart_totals();

    $this->alert->status        = "success";
    $this->alert->toastMessage  = "El descuento se aplicó correctamente.";
  }

  public function update_cart_rounding(
    $rounding = 0
  ) {
    $cart = $this->get_cart();

    $cart->cost_rounding = $rounding;
    $cart->sale_rounding = $rounding;

    $this->cart = $cart;
    $this->define_cart_totals();

    $this->alert->status  = "success";
  }

  public function remove_item()
  {
    $product_id = $this->product_id;
    $cart       = $this->cart;
    $list       = $cart->list;

    unset($list[$product_id]);

    $cart->list = $list;
    $this->cart = $cart;

    $this->define_cart_totals();

    $this->alert->status  = "success";
    $this->alert->toastMessage = "El producto se removió correctamente";
  }

  private function define_product_totals()
  {
    $product = $this->get_product();

    $quantity = $product->quantity;
    $have_iva = $product->have_iva == 'si' ? true : false;

    /**
     * Definir el importe precio costo (cost_amount) en el producto
     */
    $product_cost                   = $have_iva ? number_format($this->get_price_without_iva($product->cost_price), DECIMALS_CURRENCY)  : number_format($product->cost_price, DECIMALS_CURRENCY);
    $product_cost_iva               = $have_iva ? number_format($this->get_price_iva($product->cost_price), DECIMALS_CURRENCY)          : 0;

    $product_cost_total_iva         = $product_cost_iva * $quantity;
    $product_cost_amount            = $product_cost     * $quantity;
    $product_cost_amount_with_iva   = $product_cost_amount + $product_cost_total_iva;

    $product->cost_without_iva      = number_format($product_cost, DECIMALS_CURRENCY);
    $product->cost_iva              = number_format($product_cost_iva, DECIMALS_CURRENCY);
    $product->cost_total_iva        = number_format($product_cost_total_iva, DECIMALS_CURRENCY);
    $product->cost_amount           = number_format($product_cost_amount, DECIMALS_CURRENCY);
    $product->cost_amount_with_iva  = number_format($product_cost_amount_with_iva, DECIMALS_CURRENCY);

    /**
     * Definir el importe precio venta (sale_amount) en el producto
     */
    $product_sale                   = number_format($product->sale_price, DECIMALS_CURRENCY);
    $discount_price                 = number_format($this->parse_discount_percentage($product_sale, $product->discount));
    $product_sale                   = $product_sale - $discount_price;
    $product_sale                   = $have_iva ? number_format($this->get_price_without_iva($product_sale), DECIMALS_CURRENCY) : number_format($product_sale, DECIMALS_CURRENCY);

    $product_sale_iva               = $have_iva ? number_format($this->get_price_iva($product_sale)) : 0;
    $product_sale_total_iva         = number_format(($product_sale_iva * $quantity), DECIMALS_CURRENCY);
    $product_sale_amount            = number_format(($product_sale * $quantity), DECIMALS_CURRENCY);
    $product_sale_amount_with_iva   = number_format(($product_sale_amount + $product_sale_total_iva), DECIMALS_CURRENCY);

    $product->sale_without_iva      = $product_sale;
    $product->sale_iva              = $product_sale_iva;
    $product->sale_total_iva        = $product_sale_total_iva;
    $product->sale_amount           = $product_sale_amount;
    $product->sale_amount_with_iva  = $product_sale_amount_with_iva;

    /**
     * Setear el producto
     */
    $this->product = $product;
    $this->cart->list[$product->id] = $this->product;
  }

  private function define_cart_totals()
  {
    $cart = $this->cart;
    $list = $cart->list;

    $cost_subtotal  = 0;
    $cost_iva       = 0;
    $cost_rounding  = $cart->cost_rounding ?? 0;
    $cost_total     = 0;

    $sale_subtotal  = 0;
    $sale_iva       = 0;
    $sale_rounding  = $cart->sale_rounding ?? 0;
    $sale_total     = 0;

    foreach ($list as $key => $product) :
      /**
       * Sumar totales para el precio costo
       */
      # $product_cost           = $product->cost_without_iva;
      $product_cost_amount    = $product->cost_amount;
      $product_cost_total_iva = $product->cost_total_iva;

      $cost_subtotal          = $cost_subtotal + $product_cost_amount;
      $cost_iva               = $cost_iva      + $product_cost_total_iva;
      # $cost_total             = $cost_total    + $product_cost_amount;

      /**
       * Sumar totales para el precio venta
       */
      # $product_sale           = $product->sale_without_iva;
      $product_sale_amount    = $product->sale_amount;
      $product_sale_total_iva = $product->sale_total_iva;

      $sale_subtotal        = $sale_subtotal + $product_sale_amount;
      $sale_iva             = $sale_iva      + $product_sale_total_iva;
    # $sale_total           = $sale_total    + $product_sale_amount;
    endforeach;

    /**
     * Definfir el total del precio costo
     */
    $cost_total = $cost_subtotal + $sale_iva + $cost_rounding;
    $sale_total = $sale_subtotal + $sale_iva + $sale_rounding;

    /**
     * Setear valores en el carrrito
     */
    $cart->cost_subtotal = $cost_subtotal;
    $cart->cost_iva      = $cost_iva;
    $cart->cost_rounding = $cost_rounding;
    $cart->cost_total    = $cost_total;

    $cart->sale_subtotal = $sale_subtotal;
    $cart->sale_iva      = $sale_iva;
    $cart->sale_rounding = $sale_rounding;
    $cart->sale_total    = $sale_total;

    /**
     * Setear el carrito
     */
    $this->cart = $cart;
  }

  private function get_product_data()
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
        P.nombre_producto,
        P.codigo,
        P.precio_venta,
        P.precio_costo,
        P.contenido,
        P.unidad,
        P.aplica_iva,
        P.cantidad_mayoreo,
        P.precio_mayoreo,
        P.en_dolares,
        P.precio_costo_original,
        P.precio_venta_original,
        P.precio_mayoreo_original
      FROM {$db_dti}_inventario AS I
        LEFT JOIN {$db_dti}_sucursales  AS S ON (I.id_sucursal = S.id_sucursal)
        LEFT JOIN {$db_dti}_productos   AS P ON (I.id_producto = P.id_producto)
      WHERE
        I.id_sucursal = {$branch_id}  AND
        I.id_producto = {$product_id} And
        S.status      = 'activo'
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows == 0) return false;

    $data     = mysqli_fetch_assoc($query_result);
    $product  = new stdClass();

    $product->id                  = $data['id_producto'];
    $product->code                = $data['codigo'];
    $product->name                = $data['nombre_producto'];
    $product->content             = $data['contenido'];
    $product->stock               = $data['stock'];
    $product->unit                = $data['unidad'];
    $product->unit_symbol         = $data['unidad'] == 'Pieza' ? 'pzs.' : 'kg.';
    $product->have_iva            = $data['aplica_iva'];
    $product->wholesale_quantity  = $data['cantidad_mayoreo'];
    $product->wholesale_price     = $data['precio_mayoreo'];
    $product->origin_sale_price   = $data['precio_venta'];
    $product->sale_price          = $data['precio_venta'];
    $product->cost_price          = $data['precio_costo'];

    $product->quantity            = 0;

    $product->cost_iva            = 0;
    $product->cost_total_iva      = 0;
    $product->cost_without_iva    = 0;
    $product->cost_amount         = 0;
    $product->cost_amount_with_iva = 0;

    $product->sale_iva            = 0;
    $product->sale_total_iva      = 0;
    $product->sale_without_iva    = 0;
    $product->sale_amount         = 0;
    $product->sale_amount_with_iva = 0;

    $product->discount            = 0;
    $product->discount_price      = 0;

    $product->net_price           = 0;

    return $product;
  }

  public function get_product()
  {
    $product_id = $this->product_id;
    $product    = $this->product;
    $list       = $this->cart->list;

    if (isset($list[$product_id]))  return $list[$product_id];
    if (isset($product))            return $product;

    $product = $this->get_product_data();

    $this->product = $product;

    return $product;
  }

  public function verify_wholesale_price(
    $quantity
  ) {
    $product            = $this->get_product();

    $price              = $product->origin_sale_price;
    $quantity           = doubleval($quantity);

    $wholesale_price    = $product->wholesale_price;
    $wholesale_quantity = $product->wholesale_quantity;

    if ($quantity >= $wholesale_quantity) $product->sale_price = $wholesale_price;
    if ($quantity < $wholesale_quantity)  $product->sale_price = $price;
    if ($wholesale_quantity == 0)         $product->sale_price = $price;

    $this->product = $product;

    return $product;
  }

  public function get_cart()
  {
    return $this->cart;
  }

  public function get_alert()
  {
    return $this->alert;
  }
}
