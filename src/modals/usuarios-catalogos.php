<?php
$modal_tipos_id      = $modal_tipos_id      ? $modal_tipos_id      : $page_config['page_identifier'];
$modal_tipos_origin  = $modal_tipos_origin  ? $modal_tipos_origin  : '';
$modal_tipos_style   = $modal_tipos_origin == "productos" ? 'modal-secondary' : "";
?>

<div class="modal fade <?= $modal_tipos_style; ?>" id="<?= $modal_tipos_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_tipos_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $modal_tipos_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_tipos_id; ?>-modal-label">Nuevo archivo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdt-name">Nombre<span class="text-danger">*</span></label>
              <input id="fdt-name" class="form-control" name="name" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdt-file">Archivo<span class="text-danger">*</span></label>
              <input id="fdt-file" class="form-control" name="file" type="file" accept="application/pdf" required>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="userId" value="<?= $userId; ?>" type="hidden">
      <input name="action" value="add-<?= $modal_tipos_id; ?>" type="hidden">
      <input name="place" value="<?= $modal_tipos_id; ?>" type="hidden">
      <input name="origin" value="<?= $modal_tipos_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>