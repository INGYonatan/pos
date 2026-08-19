<?php
$clientes_modal_page_id = $clientes_modal_page_id ? $clientes_modal_page_id : $page_config['page_identifier'];
$clientes_modal_origin  = $clientes_modal_origin  ? $clientes_modal_origin  : $page_config['page_identifier'];
?>

<div class="modal fade" id="<?= $clientes_modal_page_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $clientes_modal_page_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $clientes_modal_page_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $clientes_modal_page_id; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <h3 class="header-title">Datos de cliente</h3>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="nombre_completo">Nombre completo<span class="text-danger">*</span></label>
              <input id="nombre_completo" class="form-control" name="nombre_completo" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="nombre_comercial">Nombre comercial</label>
              <input id="nombre_comercial" class="form-control" name="nombre_comercial" type="text">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fd-limite_credito">Límite de crédito<span class="text-danger">*</span></label>
              <input id="fd-limite_credito" class="form-control number-input" name="limite_credito" value="0" type="text" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fd-limite_credito_plazo">Plazo de crédito (días)<span class="text-danger">*</span></label>
              <input id="fd-limite_credito_plazo" class="form-control number-input" name="limite_credito_plazo" value="0" step="1" type="number" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <h3 class="header-title">Datos de contacto</h3>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="correo">Correo</label>
              <input id="correo" class="form-control" name="correo" type="email">
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="telefono">Teléfono</label>
              <input id="telefono" class="form-control number-input" size="10" name="telefono" type="number">
            </div>
          </div>
        </div>

        <div class="row mb-2">
          <div class="col-12">
            <input id="requiere_factura" class="check-with-content" name="requiere_factura" value="si" type="checkbox">
            <label for="requiere_factura" class="form-label label-check fw-bold">¿Requiere factura?</label>

            <div class="content-check">
              <div class="row">
                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label class="form-label" for="razon_social">Razón social</label>
                    <input id="razon_social" class="form-control" name="razon_social" type="text" required>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label class="form-label" for="id_regimen_fiscal">Tipo</label>
                    <select id="tipo" class="form-control form-select" name="tipo" required>
                      <option value="">--Seleccionar--</option>
                      <option value="fisica">Física</option>
                      <option value="moral">Moral</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label class="form-label" for="rfc">RFC</label>
                    <input id="rfc" class="form-control" name="rfc" type="text" required>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label class="form-label" for="id_regimen_fiscal">Regimen fiscal</label>
                    <select id="id_regimen_fiscal" class="form-control form-select" name="id_regimen_fiscal" required>
                      <?= catalog_get_tax_regime(); ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label class="form-label" for="domicilio_fiscal">Domicilio fiscal (CP)</label>
                    <input id="domicilio_fiscal" class="form-control" name="domicilio_fiscal" type="text" required>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" value="add-<?= $clientes_modal_page_id; ?>" type="hidden">
      <input name="place" value="<?= $clientes_modal_page_id; ?>" type="hidden">
      <input name="origin" value="<?= $clientes_modal_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>