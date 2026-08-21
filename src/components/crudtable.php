<?php

/**
 * @phpstan-type FilterStructure array{
 *    type: string, // "select" | "input"
 *    name: string,
 *    label: string,
 *    placeholder: string,
 *    colSize: string,
 *    attributes: array
 * }
 */

/**
 * @var string $pageId
 * 
 * @var array{
 *     principal: FilterStructure,
 *     hidden: FilterStructure
 * } $filters
 * 
 * @var string $pageTitle
 * @var string $pageDescription
 * @var string $actions
 * @var string $renderedActions
 * @var string $extraHtmlInFilters
 */

$filters            = $filters              ?? [];
$principalFilters   = $filters["principal"] ?? [];
$hideFilters        = $filters["hidden"]    ?? [];

if (count($filters) > 0 && count($principalFilters) === 0 && count($hideFilters) === 0) $principalFilters = $filters;

// Limitar los filtros visibles en la barra de filtros y los excedentes agregarlos a hidden
$limitVisibleFilters = 5;

if (count($principalFilters) > $limitVisibleFilters) {
  $hideFilters = array_slice($principalFilters, $limitVisibleFilters);
  $principalFilters = array_slice($principalFilters, 0, $limitVisibleFilters);
}

$haveHiddenFilters  = count($hideFilters) > 0;
?>

<form id="<?= $pageId; ?>-filters-form" class="row crudtable-component mt-0" autocomplete="off">
  <?php if ($pageTitle || $pageDescription || $actions || $renderedActions) : ?>
    <div class="page-title-container d-flex flex-column gap-2 flex-lg-row justify-content-lg-between align-items-lg-center">
      <div>
        <?php if ($pageTitle || $pageDescription) : ?>
          <div class="d-flex flex-column">
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
        <?php endif; ?>
      </div>

      <?php if ($actions || $renderedActions) : ?>
        <div class="btn-actions-group">
          <?= $actions; ?>

          <?php if ($renderedActions) : ?>
            <?= $renderedActions; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div id="<?= $pageId; ?>-crudtable-filters" class="crudtable-filters">
          <div class="d-flex flex-column flex-lg-row gap-3 w-100 align-items-center align-items-lg-start">
            <div class="flex-1 w-100">
              <?php renderComponent("crudtable-fields", [
                "pageId"  => $pageId,
                "filters" => $principalFilters
              ]); ?>
            </div>

            <?php if ($haveHiddenFilters) : ?>
              <div>
                <label class="form-label" for="filter-search" style="opacity: 0;">-</label>

                <br>

                <button id="<?= $pageId; ?>-show-filters" for="<?= $pageId; ?>-crudtable-filters" class="form-control" type="button">
                  <i class="fa fa-filter me-1"></i> Mas filtros
                </button>
              </div>
            <?php endif; ?>
          </div>

          <?php if ($haveHiddenFilters) : ?>
            <div class="crudtable-hidden-filters mt-2" style="display: none;">
              <?php renderComponent("crudtable-fields", [
                "pageId"  => $pageId,
                "filters" => $hideFilters
              ]); ?>
            </div>
          <?php endif; ?>

          <?php if ($extraHtmlInFilters) : ?>
            <?= $extraHtmlInFilters; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-footer bg-transparent py-1">
        <div class="d-flex flex-column w-100 align-items-center flex-lg-row justify-content-lg-end">
          <div>
            <button id="<?= $pageId; ?>-clear-filters" class="btn px-0" type="button">
              <i class="fa fa-trash-alt me-1"></i>
              Limpiar filtros
            </button>
          </div>

          <div>
            <hr class="hr d-lg-none my-2">
            <hr class="vr d-none d-lg-block mx-3">
          </div>

          <div>
            <?php renderComponent("perPage"); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body p-0">
        <div id="<?= $pageId; ?>-table"></div>
      </div>

      <?php renderComponent("cardLoading", [
        "pageId" => $pageId
      ]); ?>
    </div>
  </div>
</form>

<?php if ($haveHiddenFilters) : ?>
  <script>
    document.getElementById("<?= $pageId; ?>-show-filters").addEventListener("click", function() {
      const filters = document.querySelector("#<?= $pageId; ?>-crudtable-filters .crudtable-hidden-filters");
      const isOpen = filters.style.display === "none";

      if (isOpen) filters.style.display = "block";
      else filters.style.display = "none";
    });
  </script>
<?php endif; ?>

<script>
  // reiniciar todos los filtros del formulario
  document.getElementById("<?= $pageId; ?>-clear-filters").addEventListener("click", function() {
    const form = document.querySelector("#<?= $pageId; ?>-filters-form");

    form.reset();
    form.dispatchEvent(new Event("submit"));

    // Cerrar los filtros ocultos
    const filters = document.querySelector("#<?= $pageId; ?>-crudtable-filters .crudtable-hidden-filters");
    filters.style.display = "none";
  });
</script>