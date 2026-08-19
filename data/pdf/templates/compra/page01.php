<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compra</title>

  <style>
    .description {
      font-size: 7.5px;
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

    .text-right {
      text-align: right;
    }

    .text-bold {
      font-weight: bold;
    }
  </style>
</head>

<body>
  <table cellspacing="0" cellpadding="1" border="0">
    <tbody>
      <tr>
        <td rowspan="4" style="width: 20%;">
          <img src="<?= $company->logo; ?>" alt="<?= $company->name; ?>" width="80px" height="50px" style="object-fit: contain;">
        </td>

        <td class="description" rowspan="4" style="width: 56%;"><b><?= $company->name; ?></b> SUCURSAL <?= $company->branch; ?><br><?= $company->address; ?><br>Teléfono: <?= $company->phone; ?><br>Whatsapp: <?= $company->whatsapp; ?><br>Correo: <?= $company->email; ?></td>
        <td class="description" style="width: 12%; text-align: right;">COMPRA</td>
        <td class="description-sm" style="width: 12%; text-align: right;"><?= $compra->folio; ?></td>
      </tr>

      <tr>
        <td class="description" style="background-color: #444; text-align: right; color: #fff;"><span style="text-transform: uppercase;">No Documento</span></td>
        <td class="description-sm" style="text-align: right;"><?= $compra->document_folio; ?></td>
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
        <td class="description" style="text-align: right;"><?= $compra->created_date_format; ?></td>
      </tr>

      <tr>
        <td class="description" style="background-color: #444; color: #fff;">Vendedor</td>
        <td class="description" colspan="4"><?= $seller->name; ?></td>
        <td class="description" style="background-color: #444; color: #fff;">Vigencia</td>
        <td class="description" style="text-align: right;">--</td>
      </tr>
    </tbody>
  </table>

  <br><br><br>

  <?php
  $subtotal       = 0;
  $total_discount = 0;
  $total_ieps     = 0;
  $total_iva      = 0;
  $total          = 0;
  ?>

  <table cellspacing="0" cellpadding="6" border="0" style="border-collapse: collapse;">
    <thead>
      <tr style="background-color: #444;">
        <th class="table-title" style="color: #fff; font-weight: bold; text-align: center; width: 10%;">CANTIDAD</th>
        <th class="table-title" style="color: #fff; font-weight: bold; text-align: left; width: 20%;">PRODUCTO</th>
        <th class="table-title" style="color: #fff; font-weight: bold; text-align: right; width: 15%;">PRECIO UNITARIO</th>
        <th class="table-title" style="color: #fff; font-weight: bold; text-align: center; width: 10%;">DESCUENTO</th>
        <th class="table-title" style="color: #fff; font-weight: bold; text-align: right; width: 15%;">PRECIO NETO</th>
        <th class="table-title" style="color: #fff; font-weight: bold; text-align: center; width: 10%;">IEPS</th>
        <th class="table-title" style="color: #fff; font-weight: bold; text-align: center; width: 10%;">IVA</th>
        <th class="table-title" style="color: #fff; font-weight: bold; text-align: right; width: 10%;">TOTAL CON IVA</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($products->list as $key => $product) :
        $quantity   = $product->quantity;
        $name       = "{$product->code} {$product->name}";
        $price      = $product->price;
        $discount   = $product->discount;
        $netPrice   = $product->subtotal;
        $ieps       = $product->have_ieps ? ($product->ieps_percentage ?? 0) : 0;
        $ieps_moneda = $product->ieps ?? 0;
        $iva        = $product->have_iva ? 16 : 0;
        $iva_moneda = $product->iva;
        $unit_total = $product->total;

        $subtotal       += $price * $quantity;
        $total_discount += ((($discount * $price) / 100) * $quantity);
        $total_ieps     += $ieps_moneda;
        $total_iva      += $iva_moneda;
        $total          += $unit_total;

        $serialNumbers    = $product->serial_numbers;
        $serialNumbersStr = "";

        if (!$product->noSerial) :
          $serialRows = "";

          foreach ($serialNumbers as $serial) :
            $serialRows  .= "<br>{$concat}<b>No. de serie</b>: {$serial->number}";
          endforeach;

          $serialNumbersStr = "{$serialRows}";
        endif;
      ?>
        <tr>
          <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: center; width: 10%;"><?= format_decimal_number($quantity, 3); ?></td>
          <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: left; width: 20%;"><?= $name; ?><?= $serialNumbersStr; ?></td>
          <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: right; width: 15%;">$<?= number_format($price, DECIMALS_CURRENCY_TICKET); ?></td>
          <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: center; width: 10%;"><?= format_decimal_number($discount, DECIMALS_CURRENCY_TICKET); ?>%</td>
          <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: right; width: 15%;">$<?= number_format(($netPrice), DECIMALS_CURRENCY_TICKET); ?></td>
          <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: center; width: 10%;"><?= format_decimal_number($ieps, DECIMALS_CURRENCY_TICKET); ?>% | $<?= number_format(($ieps_moneda), DECIMALS_CURRENCY_TICKET); ?></td>
          <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: center; width: 10%;"><?= format_decimal_number($iva, DECIMALS_CURRENCY_TICKET); ?>% | $<?= number_format(($iva_moneda), DECIMALS_CURRENCY_TICKET); ?></td>
          <td class="description-sm" style="border-bottom: 0.5px solid #EAEAEA; text-align: right; width: 10%;">$<?= number_format($unit_total, DECIMALS_CURRENCY_TICKET); ?></td>
        </tr>
      <?php endforeach; ?>

      <tr>
        <td colspan="7"></td>
      </tr>

      <tr>
        <td class="description text-right text-bold" colspan="7">Subtotal</td>
        <td class="description text-right">$<?= number_format($subtotal, DECIMALS_CURRENCY_TICKET); ?></td>
      </tr>

      <tr>
        <td class="description text-right text-bold" colspan="7">Descuento</td>
        <td class="description text-right">$<?= number_format($total_discount, DECIMALS_CURRENCY_TICKET); ?></td>
      </tr>

      <!-- <tr>
        <td class="description text-right text-bold" colspan="6">Desc. Fin</td>
        <td class="description text-right">$0</td>
      </tr> -->

      <tr>
        <td class="description text-right text-bold" colspan="7">I.E.P.S</td>
        <td class="description text-right">$<?= number_format($total_ieps, DECIMALS_CURRENCY_TICKET); ?></td>
      </tr>

      <!-- <tr>
        <td class="description text-right text-bold" colspan="6">Ret. ISR</td>
        <td class="description text-right">$0</td>
      </tr> -->

      <tr>
        <td class="description text-right text-bold" colspan="7">IVA</td>
        <td class="description text-right">$<?= number_format($total_iva, DECIMALS_CURRENCY_TICKET); ?></td>
      </tr>

      <!-- <tr>
        <td colspan="7"></td>
      </tr> -->

      <tr>
        <td class="description text-right text-bold" colspan="7">Total</td>
        <td class="description text-right">$<?= number_format($total, DECIMALS_CURRENCY_TICKET); ?></td>
      </tr>

      <tr>
        <td class="description text-right text-bold" colspan="7"><?= numtoletras($total); ?></td>
        <td class="description text-right"></td>
      </tr>
    </tbody>
  </table>
</body>

</html>