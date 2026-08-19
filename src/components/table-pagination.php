<?php

/**
 * @var string $page
 * @var string $perPage
 * @var string $end
 * @var string $numPages
 * @var string $total
 */

$start = ($page - 1) * $perPage + 1;
?>

<div class="d-flex flex-column align-items-center gap-3 flex-lg-row justify-content-lg-between w-100 mb-2 ps-3 pe-2">
  <div>
    Mostrando <?= $start; ?> a <?= ($end - 1); ?> de <?= $total; ?> registros
  </div>

  <div>
    <?= paginate($page, $numPages, 2, 'load'); ?>
  </div>
</div>