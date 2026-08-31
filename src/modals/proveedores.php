<?php
$proveedores_page_config_id = $proveedores_page_config_id ?? $page_config['page_identifier'];
$modal_proveedores_origin = $proveedores_page_config_origin ? $proveedores_page_config_origin : '';
$modal_proveedores_style = $proveedores_page_config_origin == "productos" ? "modal-secondary" : "";
?>

<div class="modal fade <?= $modal_proveedores_style; ?>" id="<?= $proveedores_page_config_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $proveedores_page_config_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $proveedores_page_config_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $proveedores_page_config_id; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <h3 class="header-title">Datos del proveedor</h3>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="nombre_proveedor">Nombre completo<span class="text-danger">*</span></label>
              <input id="nombre_proveedor" class="form-control" name="nombre_proveedor" data-validateFieldKeyUp="true" data-validateFieldKeyUp-place="proveedores" type="text" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="nombre_comercial">Nombre comercial<span class="text-danger">*</span></label>
              <input id="nombre_comercial" class="form-control" name="nombre_comercial" data-validateFieldKeyUp="true" data-validateFieldKeyUp-place="proveedores" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <h3 class="header-title">Datos de contacto</h3>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="correo">Correo<span class="text-danger">*</span></label>
              <input id="correo" class="form-control" name="correo" type="email" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="telefono">Teléfono</label>
              <input id="telefono" class="form-control" name="telefono" type="tel">
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" value="add-<?= $proveedores_page_config_id; ?>" type="hidden">
      <input name="place" value="<?= $proveedores_page_config_id; ?>" type="hidden">
      <input name="origin" value="<?= $modal_proveedores_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>