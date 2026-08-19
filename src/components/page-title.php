<?php

/**
 * @var string $pageTitle
 * @var string $pageDescription
 */
?>

<div class="d-flex flex-column mb-3">
  <?php if ($pageTitle) : ?>
    <h1 class="page-title">
      <?= $pageTitle; ?>
    </h1>
  <?php endif; ?>

  <?php if ($pageDescription) : ?>
    <p class="page-description">
      <?= $pageDescription; ?>
    </p>
  <?php endif; ?>
</div>