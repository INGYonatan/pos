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