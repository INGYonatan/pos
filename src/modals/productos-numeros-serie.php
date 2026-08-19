<?php
$modal_screen_id      = $modal_screen_id      ? $modal_screen_id      : $page_config['page_identifier'];
$modal_screen_origin  = $modal_screen_origin  ? $modal_screen_origin  : '';
$modal_screen_style   = $modal_screen_origin == "productos" ? 'modal-secondary' : "";
?>

<div class="modal fade <?= $modal_screen_style; ?>" id="<?= $modal_screen_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_screen_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $modal_screen_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_screen_id; ?>-modal-label">Nueva marca</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdt-serialNumber">Número de serie<span class="text-danger">*</span></label>
              <input id="fdt-serialNumber" class="form-control" name="serialNumber" type="text" required>
            </div>
          </div>
        </div>
      </div>

      <input id="fdt-uid" name="uid" type="hidden">
      <input name="action" value="edit-<?= $modal_screen_id; ?>" type="hidden">
      <input name="place" value="<?= $modal_screen_id; ?>" type="hidden">
      <input name="origin" value="<?= $modal_screen_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>