<?php

/**
 * @var string $label
 * @var string|null $fecha_inicio
 * @var string|null $fecha_fin
 */

if (!$label)  $label = "Fechas";
?>

<div class="form-group m-0">
  <label class="form-label" for="filter-fecha"><?= $label; ?></label>

  <div class="input-group">
    <input id="filter-fecha-inicio" class="form-control datepicker" name="fecha_inicio" value="<?= $fecha_inicio; ?>" placeholder="Desde" type="text">
    <!-- <span class="input-group-text">
      <i class="fa fa-arrow-right"></i>
    </span> -->
    <input id="filter-fecha-fin" class="form-control datepicker" name="fecha_fin" value="<?= $fecha_fin; ?>" placeholder="Hasta" type="text">
  </div>
</div>