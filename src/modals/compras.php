<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal-ver-productos" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="responsive-table">
          <table class="table table-sm table-hover">
            <thead class="bg-light">
              <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
              </tr>
            </thead>

            <tbody id="tabla-ver-productos"></tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="id_proveedor">Proveedor<span class="text-danger">*</span></label>
              <select id="id_proveedor" class="form-control form-select supplier-catalog" name="id_proveedor" required>
                <?= getSupplierCatalog(); ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="folio_documento">Folio del documento<span class="text-danger">*</span></label>
              <input id="folio_documento" class="form-control" name="folio_documento" type="text" required>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="fecha_documento">Fecha del documento<span class="text-danger">*</span></label>
              <div class="input-group">
                <input id="fecha_documento" class="form-control datepicker" name="fecha_documento" value="<?= date('d-m-Y'); ?>" type="text" required>

                <div class="input-group-text">
                  <i class="fa fa-calendar"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="metodo_pago">Método de pago<span class="text-danger">*</span></label>
              <select id="metodo_pago" class="form-control form-select" name="metodo_pago" required>
                <option value="De contado" selected>De contado</option>
                <option value="Credito">Credito</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="form-group">
              <label class="form-label" for="forma_pago">Forma de pago<span class="text-danger">*</span></label>
              <select id="forma_pago" class="form-control form-select" name="forma_pago" required>
                <option value="Efectivo">Efectivo</option>
                <option value="Cheque">Cheque</option>
                <option value="Transferencia">Transferencia</option>
                <option value="Tarjeta de débito">Tarjeta de débito</option>
                <option value="Tarjeta de crédito">Tarjeta de crédito</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="observaciones">Observaciones</label>
              <textarea id="observaciones" class="form-control" name="observaciones" rows="4"></textarea>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" value="" type="hidden">
      <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>