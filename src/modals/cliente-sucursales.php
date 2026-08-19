<?php $modal_cliente_sucursales_id = $modal_cliente_sucursales_id ? $modal_cliente_sucursales_id : $page_config['page_identifier'];  ?>
<?php $modal_cliente_sucursales_place = $modal_cliente_sucursales_place ? $modal_cliente_sucursales_place : $modal_cliente_sucursales_id; ?>

<div class="modal fade" id="<?= $modal_cliente_sucursales_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_cliente_sucursales_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="<?= $modal_cliente_sucursales_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_cliente_sucursales_id; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="cls-sucursal">Sucursal<span class="text-danger">*</span></label>
              <input id="cls-sucursal" class="form-control" name="sucursal" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="cls-cp">Código postal<span class="text-danger">*</span></label>
              <input id="cls-cp" class="form-control" name="cp" type="text" required>
            </div>
          </div>
        </div>
      </div>

      <input name="id_cliente" value="<?= $customer->id; ?>" type="hidden">

      <input name="uid" type="hidden">
      <input name="action" type="hidden">
      <input name="place" value="<?= $modal_cliente_sucursales_place; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>