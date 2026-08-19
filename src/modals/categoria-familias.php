<?php
$modal_categoria_familias_id      = $modal_categoria_familias_id      ? $modal_categoria_familias_id      : $page_config['page_identifier'];
$modal_categoria_familias_origin  = $modal_categoria_familias_origin  ? $modal_categoria_familias_origin  : '';
$modal_categoria_familias_style   = $modal_categoria_familias_origin == "productos" ? "modal-secondary" : "";
?>

<div class="modal fade <?= $modal_categoria_familias_style; ?>" id="<?= $modal_categoria_familias_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_categoria_familias_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $modal_categoria_familias_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_categoria_familias_id; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdmcf-id_categoria">Líneas<span class="text-danger">*</span></label>
              <select id="fdmcf-id_categoria" class="form-control form-select" name="id_categoria" required>
                <?= getCategoriesCatalog($category_id ?? ""); ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdmcf-familia">Familia<span class="text-danger">*</span></label>
              <input id="fdmcf-familia" class="form-control" name="familia" type="text" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdmcf-limite_descuento">Límite de descuento<span class="text-danger">*</span></label>
              <input id="fdmcf-limite_descuento" class="form-control" name="limite_descuento" type="number" step="<?= DECIMALS_CURRENCY_STEP; ?>" max="100" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdmcf-cantidad_mayoreo">Cantidad mayoreo (dejar en 0 si no aplica)<span class="text-danger">*</span></label>
              <input id="fdmcf-cantidad_mayoreo" class="form-control" name="cantidad_mayoreo" type="number" min="0" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdmcf-precio_mayoreo">Precio mayoreo (dejar en 0 si no aplica)<span class="text-danger">*</span></label>
              <input id="fdmcf-precio_mayoreo" class="form-control" name="precio_mayoreo" type="number" step="<?= DECIMALS_CURRENCY_STEP; ?>" required>
            </div>
          </div>
        </div>
      </div>

      <?php /* <input id="fdmcf-id_categoria" name="id_categoria" value="<?= $category_id; ?>" type="hidden"> */ ?>

      <input name="uid" type="hidden">
      <input name="action" value="add-<?= $modal_categoria_familias_id; ?>" type="hidden">
      <input name="place" value="<?= $modal_categoria_familias_id; ?>" type="hidden">
      <input name="origin" value="<?= $modal_categoria_familias_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>