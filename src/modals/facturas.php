<div class="modal fade" id="modal-cancelar-factura" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="cancelar-factura-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Cancelar factura</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fd-motivo">Motivo<span class="text-danger">*</span></label>
              <select id="fd-motivo" class="form-control form-select" name="motivo" required>
                <option value="">--Seleccionar--</option>
                <option value="01">Clave 01: Comprobante emitido con errores con relación.</option>
                <option value="02">Clave 02: Comprobante emitido con errores sin relación.</option>
                <option value="03">Clave 03: No se llevó a cabo la operación.</option>
                <option value="04">Clave 04: Operación nominativa relacionada en una factura global.</option>
              </select>
            </div>
          </div>
        </div>

        <div id="fd-folioSustitutoContainer" class="row" style="display: none;">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fd-folioSustituto">Folio fiscal que sustituye a la factura<span class="text-danger">*</span></label>
              <input id="fd-folioSustituto" class="form-control" name="folioSustituto" placeholder="8774D800-B447-11EC-BA96-A3A1C969C7D0" required>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" value="cancelar-factura" type="hidden">
      <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Descartar</button>
        <button class="btn btn-primary" type="submit">Cancelar factura</button>
      </div>
    </form>
  </div>
</div>