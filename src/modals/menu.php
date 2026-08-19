<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="id_modulo">Módulo (Opcional)</label>
              <select id="id_modulo" class="form-select" name="id_modulo">
                <?= getModulesCatalog('', '--Seleccionar--', true); ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="titulo">Título<span class="text-danger">*</span></label>
              <input id="titulo" class="form-control" name="titulo" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="icono">Icono</label>
              <input id="icono" class="form-control" name="icono" type="text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="pertenece_a">Pertenece a (Opcional)</label>
              <select id="pertenece_a" class="form-select" name="pertenece_a">
                <?= getMenuCatalog('', '--Seleccionar--', true); ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="orden">Orden<span class="text-danger">*</span></label>
              <input id="orden" class="form-control" name="orden" type="number" min="1" required>
            </div>
          </div>
        </div>

        <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="_blank">_blank<span class="text-danger">*</span></label>
              <select id="_blank" class="form-control form-select" name="_blank" required>
                <option value="">--Seleccionar--</option>
                <option value="si">si</option>
                <option value="no" selected>no</option>
              </select>
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