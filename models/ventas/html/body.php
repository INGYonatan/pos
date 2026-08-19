<?php
include __DIR__ . "/styles.php";

/**
 * @var PDFTemplateVentasModel $this
 */
?>

<table cellspacing="3" cellpadding="5" border="0">
  <tbody>
    <?php
    $totalProducts  = count($this->getSales());
    $totalQuantity  = 0;
    $totalCost      = 0;
    $totalSale      = 0;

    foreach ($this->getSales() as $sale) :
      /**
       * @var PDFTemplateVentasListItemModel $sale
       */
      $totalQuantity += $sale->getQuantity();
      $totalCost     += $sale->getTotalCost();
      $totalSale     += $sale->getTotalSale();
    ?>
      <tr>
        <td class="cellbody" width="10%"><?= $sale->getDate(); ?></td>
        <td class="cellbody" width="10%"><?= $sale->getBrand(); ?></td>
        <td class="cellbody" width="10%"><?= $sale->getLine(); ?></td>
        <td class="cellbody" width="8%"><?= $sale->getCode(); ?></td>
        <td class="cellbody" width="24%"><?= $sale->getName(); ?></td>
        <td class="cellbody text-center" width="8%"><?= number_format($sale->getQuantity()); ?></td>
        <td class="cellbody text-end" width="10%">$<?= number_format($sale->getTotalCost(), 2); ?></td>
        <td class="cellbody text-end" width="10%">$<?= number_format($sale->getTotalSale(), 2); ?></td>
        <td class="cellbody" width="10%"><?= $sale->getBranch(); ?></td>
      </tr>
    <?php endforeach; ?>

    <tr>
      <td class="cellbody" colspan="5">Total de registros impresos: <?= $totalProducts; ?></td>
      <td class="cellbody text-bold text-center" style="border-top: 1px solid #333;"><?= number_format($totalQuantity); ?></td>
      <td class="cellbody text-bold text-end" style="border-top: 1px solid #333;">$<?= number_format($totalCost, 2); ?></td>
      <td class="cellbody text-bold text-end" style="border-top: 1px solid #333;">$<?= number_format($totalSale, 2); ?></td>
      <td class="cellbody"></td>
    </tr>
  </tbody>
</table>
