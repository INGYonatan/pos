<?php
include __DIR__ . "/styles.php";

/**
 * @var PDFTemplateInventarioModel $this
 */
?>

<table cellspacing="3" cellpadding="5" border="0">
  <tbody>
    <?php
    $totalProducts  = count($this->getProducts());
    $totalStock     = 0;
    $totalAmount    = 0;
    foreach ($this->getProducts() as $product) :
      /**
       * @var PDFTemplateInventarioListItemModel $product
       */
      $totalStock   += $product->getStock();
      $totalAmount  += $product->getTotalPurchasePrice();
    ?>
      <tr>
        <td class="cellbody" width="12%"><?= $product->getId(); ?></td>
        <td class="cellbody" width="25%"><?= $product->getName(); ?></td>
        <td class="cellbody" width="18%"><?= $product->getBrand(); ?></td>
        <td class="cellbody" width="12%"><?= $product->getCategory(); ?></td>
        <td class="cellbody text-end" width="10%">$<?= $product->getLastPurchasePrice() ? number_format($product->getLastPurchasePrice(), 2) : "0.00"; ?></td>
        <td class="cellbody text-center" width="10%"><?= number_format($product->getStock()); ?></td>
        <td class="cellbody text-end" width="13%">$<?= $product->getTotalPurchasePrice() ? number_format($product->getTotalPurchasePrice(), 2) : "0.00"; ?></td>
      </tr>
    <?php endforeach; ?>

    <tr>
      <td class="cellbody" colspan="4">Total de registros impresos: <?= $totalProducts; ?></td>
      <td class="cellbody text-bold text-end">Total:</td>
      <td class="cellbody text-bold text-center" style="border-top: 1px solid #333;"><?= $totalStock; ?></td>
      <td class="cellbody text-bold text-end" style="border-top: 1px solid #333;">$<?= number_format($totalAmount, 2); ?></td>
    </tr>
  </tbody>
</table>