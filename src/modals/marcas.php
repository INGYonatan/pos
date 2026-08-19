<?php
$modal_marcas_id      = $modal_marcas_id      ? $modal_marcas_id      : $page_config['page_identifier'];
$modal_marcas_origin  = $modal_marcas_origin  ? $modal_marcas_origin  : '';
$modal_marcas_style   = $modal_marcas_origin == "productos" ? 'modal-secondary' : "";
?>

<div class="modal fade <?= $modal_marcas_style; ?>" id="<?= $modal_marcas_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_marcas_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="<?= $modal_marcas_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_marcas_id; ?>-modal-label">Nueva marca</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdm-marca">Marca<span class="text-danger">*</span></label>
              <input id="fdm-marca" class="form-control" name="marca" type="text" required>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" value="add-<?= $modal_marcas_id; ?>" type="hidden">
      <input name="place" value="<?= $modal_marcas_id; ?>" type="hidden">
      <input name="origin" value="<?= $modal_marcas_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>