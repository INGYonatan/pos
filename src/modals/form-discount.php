<div id="modal-discount" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="discount-form" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Descuento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fd-price">Precio normal<span class="text-danger">*</span></label>
              <div class="input-group">
                <input id="fd-price" class="form-control" name="price" type="text" required readonly>

                <div class="input-group-text">$</div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fd-discount">Descuento<span class="text-danger">*</span></label>
              <div class="input-group">
                <input id="fd-discount" class="form-control" name="discount" min="0" max="100" type="number" required>
                <div class="input-group-text">%</div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fd-netPrice">Precio final<span class="text-danger">*</span></label>
              <div class="input-group">
                <input id="fd-netPrice" class="form-control" name="netPrice" type="number" readonly required>

                <div class="input-group-text">$</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <input id="fd-itemId" name="itemId" type="hidden">
      <input id="fd-branchId" name="branchId" type="hidden">

      <input name="uid" type="hidden">
      <input name="action" value="update-discount-<?= $page_config['page_identifier']; ?>" type="hidden">
      <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>