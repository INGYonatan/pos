<?php
include __DIR__ . "/styles.php";

$DECIMALS_CURRENCY = 2;
?>

<table cellspacing="0" cellpadding="2" border="0">
  <tr>
    <td class="cell"></td>
    <td class="cell"></td>
    <td class="cell">
      <table>
        <tbody>
          <tr>
            <td class="cell text-bold">NOTA DE ENTREGA:</td>
            <td class="cell text-bold text-danger">
              <?= $this->getEntryNote(); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </td>
  </tr>

  <tr>
    <td class="cell"><span class="text-bold">Concepto: <?= $this->getConcept(); ?></span></td>
    <td class="cell"><span class="text-bold"><?= $this->getType(); ?></span></td>
    <td class="cell">
      <table>
        <tbody>
          <tr>
            <td class="cell text-bold">Fecha:</td>
            <td class="cell text-bold">
              <?= $this->getDate(); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </td>
  </tr>

  <tr>
    <td class="cell"></td>
    <td class="cell"></td>
    <td class="cell">
      <table>
        <tbody>
          <tr>
            <td class="cell text-bold">Almacén:</td>
            <td class="cell text-bold">
              <?= $this->getWarehouse(); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </td>
  </tr>
</table>

<?php if (sizeof($this->getProducts()) > 0) : ?>
  <div></div>

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

      foreach ($this->getProducts() as $item) :
        /**
         * @var PDFTemplateDefaultListItemModel $item
         */

        $amount       = $item->getPrice() * $item->getQuantity();
        $totalAmount += $amount;
      ?> <tr>
          <td class="cell text-center" width="15%"><?= number_format($item->getQuantity(), $DECIMALS_CURRENCY); ?></td>

          <td class="cell" width="15%"><?= $item->getId(); ?></td>

          <td class="cell" width="40%"><?= $item->getName(); ?></td>

          <td class="cell text-end" width="15%">$<?= number_format($item->getPrice(), $DECIMALS_CURRENCY); ?></td>

          <td class="cell text-end" width="15%">$<?= number_format(($amount), $DECIMALS_CURRENCY); ?></td>
        </tr>
      <?php endforeach; ?>

      <tr>
        <td class="cell text-end" colspan="4"><span class="text-bold">Importe total:</span></td>
        <td class="cell text-end" style="border-top: 1px solid #333333;">$<?= number_format($totalAmount, $DECIMALS_CURRENCY); ?></td>
      </tr>
    </tbody>
  </table>
<?php endif; ?>