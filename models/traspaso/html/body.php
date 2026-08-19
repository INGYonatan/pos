<?php
include __DIR__ . "/styles.php";

$DECIMALS_CURRENCY = 2;
?>

<table cellspacing="0" cellpadding="0" border="0">
  <tbody>
    <tr>
      <td width="10%" class="cell"><img src="<?= $this->getLeftLogo(); ?>" alt="Logo" width="55" /></td>

      <td width="45%">
        <table cellspacing="0" cellpadding="3" border="0">
          <tbody>
            <tr>
              <td class="cell text-bold">Almacén de origen:</td>
              <td class="cell text-bold"><?= $this->getOriginWarehouse(); ?></td>
            </tr>

            <tr>
              <td class="cell text-bold">Almacén de destino:</td>
              <td class="cell text-bold"><?= $this->getDestinationWarehouse(); ?></td>
            </tr>

            <tr>
              <td class="cell text-bold">Fecha:</td>
              <td class="cell text-bold"><?= $this->getDate(); ?></td>
            </tr>
          </tbody>
        </table>
      </td>

      <td width="50%">
        <table cellspacing="0" cellpadding="3" border="0">
          <tbody>
            <tr>
              <td class="cell text-bold">Salida x Traspaso</td>
            </tr>

            <tr>
              <td class="cell text-bold">Entrada x Traspaso</td>
            </tr>

            <tr>
              <td class="cell text-bold">Folio: <?= $this->getFolio(); ?></td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>

<?php if (sizeof($this->getProducts()) > 0) : ?>
  <table cellspacing="0" cellpadding="5" border="0">
    <thead>
      <tr>
        <th class="cellheader text-bold text-center" width="15%">Cantidad</th>

        <th class="cellheader text-bold" width="15%">Clave</th>

        <th class="cellheader text-bold" width="40%">Descripción</th>

        <th class="cellheader text-bold text-end" width="15%">Costo</th>

        <th class="cellheader text-bold text-end" width="15%">Importe</th>
      </tr>
    </thead>

    <tbody>
      <?php
      $totalAmount = 0;
      $totalQuantity = 0;

      foreach ($this->getProducts() as $item) :
        /**
         * @var PDFTemplateDefaultListItemModel $item
         */

        $amount       = $item->getPrice() * $item->getQuantity();
        $totalAmount += $amount;
        $totalQuantity += $item->getQuantity();
      ?> <tr>
          <td class="cell text-center" width="15%"><?= number_format($item->getQuantity(), 0); ?></td>

          <td class="cell" width="15%"><?= $item->getId(); ?></td>

          <td class="cell" width="40%"><?= $item->getName(); ?></td>

          <td class="cell text-end" width="15%">$<?= number_format($item->getPrice(), $DECIMALS_CURRENCY); ?></td>

          <td class="cell text-end" width="15%">$<?= number_format(($amount), $DECIMALS_CURRENCY); ?></td>
        </tr>
      <?php endforeach; ?>

      <tr>
        <td class="cell text-start" style="border-top: 1px solid #333333;">Total de art: <?= $totalQuantity; ?></td>
        <td class="cell text-end" colspan="3"><span class="text-bold">Importe total:</span></td>
        <td class="cell text-end" style="border-top: 1px solid #333333;">$<?= number_format($totalAmount, $DECIMALS_CURRENCY); ?></td>
      </tr>
    </tbody>
  </table>
<?php endif; ?>