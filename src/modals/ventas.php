<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal-ver-productos" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div id="tabla-ver-productos" class="modal-body"></div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modal-agregar-referencia-efectivo" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="agregar-referencia-efectivo-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Referencia de pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdare-referencia_efectivo">Referencia<span class="text-danger">*</span></label>
              <input id="fdare-referencia" class="form-control" name="efectivo_referencia" type="text" required>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="place" value="ventas" type="hidden">
      <input name="action" value="agregar-referencia-efectivo" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modal-agregar-folio-sae" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="agregar-folio-sae-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Agregar Folio SAE</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdafs-folio_sae">Folio SAE<span class="text-danger">*</span></label>

              <div class="input-group">
                <div class="input-group-text">
                  <i class="fa fa-barcode"></i>
                </div>

                <input id="fdafs-folio_sae" class="form-control" name="folio_sae" type="text" required>
              </div>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="place" value="ventas" type="hidden">
      <input name="action" value="agregar-folio-sae" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modal-ventas-ms-referencia-pago" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="masivo-agregar-referencia-efectivo-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Referencia de pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="alert alert-info" role="alert">
              La referencia de pago se agregará a todas las ventas seleccionadas.
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdares-referencia_efectivo">Referencia<span class="text-danger">*</span></label>
              <input id="fdares-referencia_efectivo" class="form-control" name="efectivo_referencia" type="text" required>
            </div>
          </div>
        </div>
      </div>

      <input name="uids" type="hidden">
      <input name="place" value="ventas" type="hidden">
      <input name="action" value="masivo-agregar-referencia-efectivo" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>