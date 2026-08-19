<?php
require_once __DIR__ . "/../../data/lib/helpers/emisores.helpers.php";
$emisoresHelper   = new EmisoresHelper();

/** 
 * @var string $IS_ADMIN 
 * @var string $pageId
 * @var string $searchPlaceholder
 * */

$searchPlaceholder = $searchPlaceholder ? $searchPlaceholder : "Folio, emisor, Cliente," . ($IS_ADMIN ? " Sucursal," : "") . " F. Pago, Monto...";

?>

<div class="d-flex flex-column flex-lg-row gap-3 w-100 align-items-center align-items-lg-start">
  <div class="d-flex flex-column align-items-center gap-3 flex-lg-row flex-1">
    <div class="flex-1">
      <div class="form-group m-0">
        <label class="form-label" for="filter-search">Buscar aquí</label>
        <input id="filter-search" class="form-control" name="search" placeholder="<?= $searchPlaceholder; ?>" type="text">
      </div>
    </div>

    <div class="flex-1">
      <?php renderComponent("field-fechas-desde-hasta"); ?>
    </div>

    <div class="flex-1">
      <div class="form-group m-0">
        <label class="form-label" for="filter-id_emisor">Emisor<span class="text-danger">*</span></label>

        <select id="filter-id_emisor" class="form-control form-select" name="id_emisor" required>
          <option value="">--Todos--</option>
          <?= $emisoresHelper->getCatalog(); ?>
        </select>
      </div>
    </div>

    <?php if ($IS_ADMIN) : ?>
      <div class="flex-1">
        <div class="form-group m-0">
          <label class="form-label" for="filter-id_sucursal_origen">Sucursal</label>
          <select id="filter-id_sucursal_origen" class="form-control form-select" name="id_sucursal">
            <?= getBranchOfficesCatalog('', '--Todas--'); ?>
          </select>
        </div>
      </div>
    <?php endif; ?>

    <div class="flex-1">
      <div class="form-group m-0">
        <label class="form-label" for="filter-status">Estado</label>

        <select id="filter-status" class="form-control form-select" name="status">
          <option value="">--Todas--</option>
          <option value="activo" selected>Activo</option>
          <option value="cancelado">Cancelado</option>
        </select>
      </div>
    </div>
  </div>

  <div>
    <div class="dropdown">
      <div class="btn-group">
        <?= getFilterActions($pageId); ?>

        <?php include 'src/components/per-page.php'; ?>
      </div>
    </div>
  </div>
</div>