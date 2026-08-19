<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off" enctype="multipart/form-data">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="nombre_sucursal">Nombre sucursal<span class="text-danger">*</span></label>
              <input id="nombre_sucursal" class="form-control" name="nombre_sucursal" type="text" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="numero_serie">Serie<span class="text-danger">*</span></label>
              <input id="numero_serie" class="form-control" name="numero_serie" type="text" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="nombre_comercial">Nombre comercial</label>
              <input id="nombre_comercial" class="form-control" name="nombre_comercial" type="text">
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="correo">Correo</label>
              <input id="correo" class="form-control" name="correo" type="email">
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="telefono">Teléfono<span class="text-danger">*</span></label>
              <input id="telefono" class="form-control number-input" name="telefono" type="tel" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="cp">Código postal</label>
              <input id="cp" class="form-control" name="cp" type="text">
            </div>
          </div>

          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="direccion">Dirección</label>
              <textarea id="direccion" class="form-control" name="direccion" rows="3"></textarea>
            </div>
          </div>

          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="logo">Logo <small class="text-muted">(JPG o PNG)</small></label>
              <input id="logo" class="form-control" name="logo" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            </div>
          </div>

        </div>

        <div class="row d-none">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="tipo">Tipo<span class="text-danger">*</span></label>
              <select id="tipo" class="form-control form-select" name="tipo" required>
                <option value="sucursal" selected>Sucursal</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" type="hidden">
      <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>