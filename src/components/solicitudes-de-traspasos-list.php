<?php
$pageId   = $data["pageId"];
$IS_ADMIN = $data["IS_ADMIN"];
$type     = $data["type"] ?? "realizadas";
?>

<form id="<?= $pageId; ?>-filters-form" class="row" autocomplete="off">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-transparent">
        <div class="row">
          <div class="col-12 col-lg-8">
            <div class="row">
              <div class="col-12 col-md-6 col-lg-3 col-xl-3 mb-2 mb-lg-0">
                <div class="form-group">
                  <label class="form-label" for="filter-search">Buscar aquí</label>
                  <input id="filter-search" class="form-control" name="search" placeholder="Observaciones..." type="text">
                </div>
              </div>

              <div class="col-12 col-md-6 col-lg-3 col-xl-3 mb-2 mb-lg-0">
                <div class="form-group">
                  <label class="form-label" for="filter-fecha">Fecha</label>
                  <input id="filter-fecha" class="form-control datepicker" name="date" value="" type="text">
                </div>
              </div>

              <?php if ($type == "realizadas") : ?>
                <div class="col-12 col-md-6 col-lg-3 col-xl-3 mb-2 mb-lg-0">
                  <div class="form-group">
                    <label class="form-label" for="filter-id_sucursal_origen">Solicitud enviada a</label>
                    <select id="filter-id_sucursal_origen" class="form-control form-select" name="originBranchId">
                      <?= getBranchOfficesCatalog('', '--Todas--', true); ?>
                    </select>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ($type == "recibidas") : ?>
                <div class="col-12 col-md-6 col-lg-3 col-xl-3 mb-2 mb-lg-0">
                  <div class="form-group">
                    <label class="form-label" for="filter-id_sucursal_destino">Sucursal que solicita</label>
                    <select id="filter-id_sucursal_destino" class="form-control form-select" name="destinyBranchId">
                      <?= getBranchOfficesCatalog('', '--Todas--'); ?>
                    </select>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="col-12 col-lg-4 text-center text-lg-end">
            <div class="dropdown">
              <div class="btn-group">
                <?php /* getFilterActions($page_config['page_identifier']); */ ?>
                <?php if (checkModuleActionPermission($pageId, 'agregar') && $type == "realizadas") : ?>
                  <a class="btn btn-primary" href="<?= BASE_URL; ?>/inventario-solicitudes-de-traspasos/crear">
                    <i class="fa fa-plus me-1"></i> Nueva
                  </a>
                <?php endif; ?>

                <?php include 'src/components/per-page.php'; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <input name="type" value="<?= $type; ?>" type="hidden">

      <div class="card-body">
        <div id="<?= $pageId; ?>-table"></div>
      </div>

      <!-- CARD LOADING -->
      <?php include 'src/components/card-loading.php'; ?>
    </div>
  </div>
</form>