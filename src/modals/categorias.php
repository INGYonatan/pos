<?php
$modal_categorias_id      = $modal_categorias_id      ? $modal_categorias_id      : $page_config['page_identifier'];
$modal_categorias_origin  = $modal_categorias_origin  ? $modal_categorias_origin  : '';
$modal_categorias_style   = $modal_categorias_origin == "productos" ? "modal-secondary" : "";
?>

<div class="modal fade <?= $modal_categorias_style; ?>" id="<?= $modal_categorias_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_categorias_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="<?= $modal_categorias_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_categorias_id; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdmc-id_marca">Marca<span class="text-danger">*</span></label>
              <select id="fdmc-id_marca" class="form-control form-select" name="id_marca" required <?php $brandId ? 'style="pointer-events: none;"' : "" ?>>
                <?= getBrandsCatalog($brandId ?? ""); ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdmc-categoria">Línea<span class="text-danger">*</span></label>
              <input id="fdmc-categoria" class="form-control" name="categoria" type="text" required>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" value="add-<?= $modal_categorias_id; ?>" type="hidden">
      <input name="place" value="<?= $modal_categorias_id; ?>" type="hidden">
      <input name="origin" value="<?= $modal_categorias_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>