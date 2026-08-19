<?php $modal_cliente_direcciones_id = $modal_cliente_direcciones_id ? $modal_cliente_direcciones_id : $page_config['page_identifier'];  ?>
<?php $modal_cliente_direcciones_origin = $modal_cliente_direcciones_origin ? $modal_cliente_direcciones_origin : $modal_cliente_direcciones_id; ?>
<?php $modal_cliente_direcciones_place = $modal_cliente_direcciones_place ? $modal_cliente_direcciones_place : $modal_cliente_direcciones_id; ?>

<div class="modal fade" id="<?= $modal_cliente_direcciones_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_cliente_direcciones_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $modal_cliente_direcciones_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_cliente_direcciones_id; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="fdcd-nombre_comercial">Nombre comercial</label>
              <input id="fdcd-nombre_comercial" class="form-control" name="nombre_comercial" type="text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="postalCode">Código postal<span class="text-danger">*</span></label>
              <input id="postalCode" class="form-control" name="codigo_postal" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="state">Estado<span class="text-danger">*</span></label>
              <select id="state" class="form-control form-select" name="id_estado" required>
                <?= catalog_get_state(); ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="city">Ciudad/Municipio<span class="text-danger">*</span></label>
              <select id="city" class="form-control form-select" name="id_ciudad" required></select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="neighborhood">Barrio<span class="text-danger">*</span></label>
              <select id="neighborhood" class="form-control form-select" name="id_colonia" required></select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="calle">Calle<span class="text-danger">*</span></label>
              <input id="calle" class="form-control" name="calle" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="n_exterior">Número exterior<span class="text-danger">*</span></label>
              <input id="n_exterior" class="form-control" name="n_exterior" type="text" required>
            </div>
          </div>

          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="n_interior">Número interior</label>
              <input id="n_interior" class="form-control" name="n_interior" type="text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="entre_calles">Entre que calles</label>
              <input id="entre_calles" class="form-control" name="entre_calles" type="text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="referencias">Referencias</label>
              <input id="referencias" class="form-control" name="referencias" type="text">
            </div>
          </div>
        </div>
      </div>

      <input name="id_cliente" value="<?= $customer->id; ?>" type="hidden">

      <input name="uid" type="hidden">
      <input name="action" value="add-<?= $modal_cliente_direcciones_id; ?>" type="hidden">
      <input name="place" value="<?= $modal_cliente_direcciones_place; ?>" type="hidden">
      <input name="origin" value="<?= $modal_cliente_direcciones_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>