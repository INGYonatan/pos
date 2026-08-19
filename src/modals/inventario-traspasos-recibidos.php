<div class="modal fade" id="<?= $pageId; ?>-modal-ver-productos" tabindex="-1" role="dialog" aria-labelledby="<?= $pageId; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form id="<?= $pageId; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $pageId; ?>-modal-label">Productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="responsive-table">
          <table class="table table-sm table-hover table-striped">
            <thead class="bg-dark text-white">
              <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad enviada</th>
                <th>Cantidad recibida</th>
                <th>Números de serie</th>
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

<div id="<?= $pageId; ?>-modal-completar-traspaso" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form id="<?= $pageId; ?>-completar-traspaso-form" class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Completar traspaso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div id="completar-traspaso-productos-table" class="col-12"></div>
        </div>

        <?php /* 
        <div id="ajuste-inventario-container">
          <div class="row">
            <div class="col-12">
              <div class="alert alert-info" role="alert">
                <i class="fa fa-info-circle me-2"></i>
                <strong>Información:</strong> Las cantidades faltantes se registrarán automáticamente mediante un ajuste de inventario para garantizar la precisión de su stock.
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-lg-6">
              <div class="col-6">
                <label class="form-group">
                  <div class="form-label">Tipo de ajuste<span class="text-danger">*</span></div>

                  <select id="fdct-adjustmentType" class="form-control form-select with-reload" name="adjustmentType">
                    <option value="">--Seleccionar--</option>

                    <option value="merma">Merma</option>
                    <option value="perdida">Perdida</option>
                  </select>
                </label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label class="form-label" for="fdct-observaciones_ajuste">Observaciones ajuste de inventario</label>
                <textarea id="fdct-observaciones_ajuste" class="form-control" name="adjustmentObservations" rows="3" placeholder="Observaciones..."></textarea>
              </div>
            </div>
          </div>
        </div>
        */ ?>
      </div>

      <input name="uid" type="hidden">
      <input name="action" type="hidden">
      <input name="place" value="<?= $pageId; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>

        <button class="btn btn-primary" type="submit">
          <i class="fa fa-check me-1"></i>
          Completar traspaso
        </button>
      </div>
    </form>
  </div>
</div>

<div id="<?= $pageId; ?>-modal-completar-traspaso-numeros-serie-para-ajuste" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="background-color: rgba(0,0,0,0.3);">
  <div class="modal-dialog" role="document">
    <form id="completar-traspaso-numeros-serie-para-ajuste-form" class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Números de serie que se recibieron</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div>
          <div class="row">
            <div class="col-12">
              <div class="alert alert-info" role="alert">
                <i class="fa fa-info-circle me-2"></i>
                <strong>Información:</strong> Seleccione los números de serie de los productos que se recibieron. Solo marque aquellos que efectivamente llegaron a su inventario. Los números no seleccionados se excluirán del registro.
              </div>
            </div>
          </div>

          <div id="completar-traspaso-numeros-serie-para-ajuste-container" class="row"></div>
        </div>
      </div>

      <input id="completar-traspaso-producto-uid" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>

        <button class="btn btn-primary" type="submit">
          <i class="fa fa-check me-1"></i>
          Guardar números de serie
        </button>
      </div>
    </form>
  </div>
</div>