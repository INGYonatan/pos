<?php

/** 
 * @var string $IS_ADMIN 
 * @var bool $statusProcessedOption
 * */

?>

<div class="d-flex flex-column align-items-center gap-3 flex-lg-row flex-1">
  <div class="flex-1">
    <div class="form-group m-0">
      <label class="form-label" for="filter-search">Buscar aquí</label>
      <input id="filter-search" class="form-control" name="search" placeholder="Folio, Sucursal, Cliente, Realizó, Total..." type="text">
    </div>
  </div>

  <div class="flex-1">
    <div class="form-group m-0">
      <label class="form-label" for="filter-fecha">Fechas</label>

      <div class="input-group">
        <input id="filter-fecha-inicio" class="form-control datepicker" name="fecha_inicio" value="" placeholder="Desde" type="text">
        <span class="input-group-text">
          <i class="fa fa-arrow-right"></i>
        </span>
        <input id="filter-fecha-fin" class="form-control datepicker" name="fecha_fin" value="" placeholder="Hasta" type="text">
      </div>
    </div>
  </div>

  <?php if ($IS_ADMIN) : ?>
    <div class="flex-1">
      <div class="form-group m-0">
        <label class="form-label" for="filter-id_sucursal_origen">Sucursal</label>

        <select id="filter-id_sucursal_origen" class="form-control form-select" name="id_sucursal">
          <?= getBranchOfficesCatalog('', '--Todas--', true); ?>
        </select>
      </div>
    </div>
  <?php endif; ?>

  <div class="flex-1">
    <div class="form-group m-0">
      <label class="form-label" for="filter-status">Estado</label>

      <select id="filter-status" class="form-control form-select" name="status">
        <option value="">--Todas--</option>
        <option value="vigente" selected>Vigente</option>
        <option value="expirado">Expirado</option>

        <?php if ($statusProcessedOption) : ?>
          <option value="procesado">Procesado</option>
        <?php endif; ?>
      </select>
    </div>
  </div>
</div>