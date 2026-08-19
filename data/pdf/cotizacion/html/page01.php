<?php $DECIMAL_PDF = 2; ?>

<style>
  .description {
    font-size: 7px;
    font-weight: 600;
    text-align: left;
    vertical-align: middle;
    margin: 0;
    color: #000;
  }

  .table-title {
    font-size: 5.5px;
    font-weight: 600;
    vertical-align: middle;
    margin: 0;
    color: #000;
  }

  .description-sm {
    font-size: 7px;
    font-weight: 600;
    text-align: left;
    vertical-align: middle;
    margin: 0;
    color: #000;
  }

  .description-sm-italic {
    font-size: 6px;
    font-weight: bold;
    text-align: justify;
    vertical-align: middle;
    margin: 0;
    color: #000;
    font-family: 'Courier New', Courier, monospace;
  }
</style>

<table cellspacing="0" cellpadding="1" border="0">
  <tbody>
    <tr>
      <td rowspan="4" style="width: 20%;">
        <img src="<?= $company->logo; ?>" alt="<?= $company->name; ?>" width="<?= PDF_LOGO_WIDTH; ?>" height="<?= PDF_LOGO_HEIGHT; ?>" style="object-fit: contain;">
      </td>

      <td class="description" rowspan="4" style="width: 56%;"><b><?= $company->name; ?></b> SUCURSAL <?= $company->branch; ?><br><?= $company->address; ?><br><b>Teléfono:</b> <?= $company->phone; ?> <b>WhatsApp:</b> <?= $company->whatsapp; ?><br><b>Correo:</b> <?= $company->email; ?></td>
      <td class="description" style="width: 12%; text-align: right;">COTIZACIÓN</td>
      <td class="description-sm" style="width: 12%; text-align: right;"><?= $quote->folio; ?></td>
    </tr>

    <tr>
      <td class="description" style="background-color: #444; text-align: right; color: #fff;"><span style="text-transform: uppercase;"><?= $quote->type; ?></span></td>
    </tr>
  </tbody>
</table>

<br><br><br><br>

<table cellspacing="0" cellpadding="1" border="0">
  <tbody>
    <tr>
      <td class="description" style="background-color: #444; color: #fff;">No. Asesor</td>
      <td class="description"><?= $seller->number; ?></td>
      <td class="description" style="background-color: #444; color: #fff;">Email</td>
      <td class="description" colspan="2"><?= $seller->email; ?></td>
      <td class="description" style="background-color: #444; color: #fff;">Fecha de expedición</td>
      <td class="description" style="text-align: right;"><?= $quote->expeditionDate; ?></td>
    </tr>

    <tr>
      <td class="description" style="background-color: #444; color: #fff;">Vendedor</td>
      <td class="description" colspan="4"><?= $seller->name; ?></td>
      <td class="description" style="background-color: #444; color: #fff;">Vigencia</td>
      <td class="description" style="text-align: right;"><?= $quote->expirationDate; ?></td>
    </tr>
  </tbody>
</table>

<br><br>

<table cellspacing="0" cellpadding="1" border="0">
  <thead>
    <tr>
      <th style="background-color: #444;"></th>
      <th class="table-title" colspan="3" style="background-color: #444; text-align: center; color: #fff;"><b>DATOS DEL CLIENTE</b></th>
      <th class="table-title" colspan="2" style="background-color: #444; text-align: center; color: #fff;"><b>OBSERVACIONES</b></th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td class="table-title" style="background-color: #444; color: #fff;"><b>Empresa</b></td>
      <td class="description" colspan="2"><?= $customer->company; ?></td>
      <td></td>
      <td class="description" colspan="2" rowspan="4"><?= $customer->observations; ?></td>
    </tr>

    <tr>
      <td class="table-title" style="background-color: #444; color: #fff;"><b>Solicitante</b></td>
      <td class="description" colspan="2"><?= $customer->name; ?></td>
      <td></td>
    </tr>

    <tr>
      <td class="table-title" style="background-color: #444; color: #fff;"><b>Dirección</b></td>
      <td class="description-sm" colspan="1" style="width: 23%;"><?= $customer->address; ?></td>
      <td class="table-title" style="background-color: #444; color: #fff; width: 10%;">Lugar</td>
      <td class="description"><?= $customer->city; ?></td>
    </tr>

    <tr>
      <td class="table-title" style="background-color: #444; color: #fff;"><b>E-mail</b></td>
      <td class="description" colspan="1" style="width: 23%;"><?= $customer->email; ?></td>
      <td class="table-title" style="background-color: #444; color: #fff">Teléfono</td>
      <td class="description"><?= $customer->phone; ?></td>
    </tr>
  </tbody>
</table>

<br><br><br>

<table cellspacing="0" cellpadding="6" border="0" style="border-collapse: collapse;">
  <thead>
    <tr style="background-color: #444;">
      <th class="table-title" style="color: #fff; font-weight: bold; text-align: center;">CANTIDAD</th>
      <th class="table-title" style="color: #fff; font-weight: bold; text-align: left;">PRODUCTO</th>
      <th class="table-title" style="color: #fff; font-weight: bold; text-align: right;">PRECIO UNITARIO</th>
      <!--th class="table-title" style="color: #fff; font-weight: bold; text-align: center;">DESCUENTO</th-->
      <th class="table-title" style="color: #fff; font-weight: bold; text-align: right;">PRECIO NETO</th>
      <th class="table-title" style="color: #fff; font-weight: bold; text-align: center;">IEPS</th>
      <th class="table-title" style="color: #fff; font-weight: bold; text-align: center;">IVA</th>
      <th class="table-title" style="color: #fff; font-weight: bold; text-align: right;">TOTAL CON IVA</th>
    </tr>
  </thead>

  <tbody>
    <?php foreach ($products->list as $key => $product) :
      $quantity   = $product->cart_quantity;
      $name       = "{$product->code} {$product->name}";
      $price      = $product->cart_sale_price;
      $discount   = $product->cart_sale_discount;
      $netPrice   = (((100 - $discount) * $price) / 100) * $quantity;
      $ieps_rate  = ($product->ieps_percentage ?? 0);
      $ieps_moneda = ($product->cart_sale_ieps ?? 0);
      $iva        = $product->cart_sale_iva > 0 ? 16 : 0;
      $iva_moneda = $product->cart_sale_iva;
      $total      = $product->cart_sale_amount;
      $comments   = $product->comments ?? '';

      if ($comments) $name .= "<br><small style='font-size: 6px; font-style: italic;'>{$comments}</small>";
    ?>
      <tr>
        <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: center;"><?= format_decimal_number($quantity, 3); ?></td>
        <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: left;"><?= $name; ?></td>
        <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: right;">$<?= number_format($price, $DECIMAL_PDF); ?></td>
        <!--td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: center;"><?= format_decimal_number($discount, $DECIMAL_PDF); ?>%</td-->
        <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: right;">$<?= number_format(($netPrice), $DECIMAL_PDF); ?></td>
        <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: center;"><?= format_decimal_number($ieps_rate, $DECIMAL_PDF); ?>% | $<?= number_format(($ieps_moneda), $DECIMAL_PDF); ?></td>
        <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: center;"><?= format_decimal_number($iva, $DECIMAL_PDF); ?>% | $<?= number_format(($iva_moneda), $DECIMAL_PDF); ?></td>
        <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: right;">$<?= number_format($total, $DECIMAL_PDF); ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($quote->type === "cerrada") : ?>
  <br><br>

  <table cellspacing="0" cellpadding="4" border="0">
    <tbody>
      <tr>
        <td class="description" rowspan="5" style="border-bottom: 0.5px solid #EAEAEA; text-align: center;"><b>Firma</b></td>
        <td class="description" rowspan="5" colspan="4" style="border-bottom: 0.5px solid #EAEAEA; text-align: center;"><?= $quote->signature; ?></td>
        <td class="description" style="text-align: right;">SUBTOTAL</td>
        <td class="description" style="text-align: right;">$<?= number_format($products->subtotal, $DECIMAL_PDF); ?></td>
      </tr>

      <tr>
        <td class="description" style="text-align: right;">ENVÍO</td>
        <td class="description" style="text-align: right;">$<?= number_format($products->shipment, $DECIMAL_PDF); ?></td>
      </tr>

      <tr>
        <td class="description" style="text-align: right;">IEPS</td>
        <td class="description" style="text-align: right;">$<?= number_format($products->ieps ?? 0, $DECIMAL_PDF); ?></td>
      </tr>

      <tr>
        <td class="description" style="text-align: right;">IVA</td>
        <td class="description" style="text-align: right;">$<?= number_format($products->iva, $DECIMAL_PDF); ?></td>
      </tr>

      <tr>
        <td class="description" style="text-align: right; border-bottom: 0.5px solid #EAEAEA; color: red;">TOTAL</td>
        <td class="description" style="text-align: right; border-bottom: 0.5px solid #EAEAEA; color: red;">$<?= number_format($products->total + $products->shipment, $DECIMAL_PDF); ?></td>
      </tr>
    </tbody>
  </table>
<?php endif; ?>

<br><br>

<table cellspacing="0" cellpadding="6" border="0">
  <tbody>
    <tr>
      <td class="description-sm-italic" style="border-bottom: 0.5px solid #EAEAEA;"><?= $quote->page01Note; ?></td>
    </tr>
  </tbody>
</table>

<br><br>

<!-- CONTENT PAGE 02 -->
<table cellspacing="0" cellpadding="6" border="0">
  <tbody>
    <tr>
      <td class="description-sm-italic" style="border-bottom: 0.5px solid #EAEAEA; border-top: 0.5px solid #EAEAEA;"><?= $quote->page02Note; ?></td>
    </tr>
  </tbody>
</table>