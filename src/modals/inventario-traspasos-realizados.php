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