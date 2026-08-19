<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="accion">Acción<span class="text-danger">*</span></label>
              <input id="accion" class="form-control" name="accion" type="text" required>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="tipo">Tipo<span class="text-danger">*</span></label>
              <select id="tipo" class="form-select" name="tipo" required>
                <option value="">--Seleccionar--</option>
                <option value="vista">Vista</option>
                <option value="accion">Acción</option>
                <option value="modal">Modal</option>
              </select>
            </div>
          </div>
        </div>

        <div id="tipo-acciones-container" class="card" style="display: none;">
          <div class="card-body">
            <input id="alerta" class="check-with-content" name="alerta" value="si" type="checkbox">
            <label for="alerta" class="form-label label-check">Usar alerta de confirmación</label>

            <div class="content-check">
              <div class="row pt-2">
                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label" for="alerta_titulo">Titulo alerta <span class="text-mute">(Opcional)</span></label>
                    <input id="alerta_titulo" class="form-control" name="alerta_titulo" type="text">
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label" for="alerta_mensaje">Mensaje alerta <span class="text-mute">(Opcional)</span></label>
                    <textarea id="alerta_mensaje" class="form-control" name="alerta_mensaje" rows="3"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="ubicacion">Ubicación<span class="text-danger">*</span></label>
              <select id="ubicacion" class="form-select" name="ubicacion" required>
                <option value="">--Seleccionar--</option>
                <option value="vista">Vista</option>
                <option value="filtros">Filtros</option>
                <option value="tabla">Tabla</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="icono">Icono</label>
              <input id="icono" class="form-control" name="icono" placeholder="fa fa-plus" type="text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="orden">Orden<span class="text-danger">*</span></label>
              <input id="orden" class="form-control number-input" name="orden" min="0" value="0" type="number" required>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="slug">Slug (identificador)<span class="text-danger">*</span></label>
              <input id="slug" class="form-control" name="slug" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="_blank">_blank<span class="text-danger">*</span></label>
              <select id="_blank" class="form-control form-select" name="_blank" required>
                <option value="">--Seleccionar--</option>
                <option value="si">si</option>
                <option value="no" selected>no</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="clase_html">Clase html</label>
              <input id="clase_html" class="form-control" name="clase_html" placeholder="nombre-clase" type="text">
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