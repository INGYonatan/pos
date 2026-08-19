<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <h5 class="header-title">Datos generales</h5>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="nombre_completo">Nombre completo<span class="text-danger">*</span></label>
              <input id="nombre_completo" class="form-control" name="nombre_completo" type="text" required>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="correo">Correo<span class="text-danger">*</span></label>
              <input id="correo" class="form-control" name="correo" type="email" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="telefono">Teléfono</label>
              <input id="telefono" class="form-control number-input" name="telefono" type="text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="avatar">Avatar <small class="text-muted">(JPG o PNG)</small></label>
              <input id="avatar" class="form-control" name="avatar" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <h5 class="header-title">Datos de cuenta</h5>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="username">Username<span class="text-danger">*</span></label>
              <input id="username" class="form-control" name="username" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="id_rol">Rol<span class="text-danger">*</span></label>
              <select id="id_rol" class="form-select" name="id_rol" required>
                <?= getRolesCatalog(); ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="id_sucursal">Sucursal<span class="text-danger">*</span></label>
              <select id="id_sucursal" class="form-select" name="id_sucursal" required disabled>
                <?= getBranchOfficesCatalog("", "--Seleccionar--", true); ?>
              </select>
            </div>
          </div>
        </div>

        <div id="change-password-container" class="row">
          <div class="col-12">
            <div class="form-group mb-0 mt-2">
              <label class="custom-switch">
                <input id="change_password" class="custom-switch-input" name="change_password" type="checkbox">
                <span class="custom-switch-indicator"></span>
                <span class="custom-switch-description">Cambiar contraseña</span>
              </label>
            </div>
          </div>
        </div>

        <div id="password-container" class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="password">Contraseña<span class="text-danger">*</span></label>
              <input id="password" class="form-control" name="password" type="password" required>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="confirm_password">Contraseña<span class="text-danger">*</span></label>
              <input id="confirm_password" class="form-control" name="confirm_password" type="password" required>
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