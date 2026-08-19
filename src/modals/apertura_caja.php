<?php $page_config['page_identifier'] = "apertura-caja"; ?>

<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-lg-12">
            <div class="form-group">
              <label class="form-label" for="nombre_sucursal">Efectivo de apertura<span class="text-danger">*</span></label>
              <input id="nombre_sucursal" class="form-control" name="nombre_sucursal" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="direccion">Nota de apertura</label>
              <textarea id="nota_apertura" class="form-control" name="nota_apertura" rows="6"></textarea>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" type="hidden">
      <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Aperturar caja</button>
      </div>
    </form>
  </div>
</div>