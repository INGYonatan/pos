<div id="modal-today-sales" class="modal fade" role="dialog" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 id="ventas-del-dia-title" class="modal-title">Ventas del día</h5>
        <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="ventas-del-dia-filters-form" class="row" autocomplete="off">
          <div class="col-md-12 col-lg-12">
            <div class="card-header bg-white">
              <div class="row">
                <div class="col-12">
                  <div class="row">
                    <div class="col-12 col-md-6 mb-2 mb-lg-0">
                      <div class="form-group">
                        <label class="form-label" for="filter-search">Buscar aquí</label>
                        <input id="filter-search" class="form-control" name="search" placeholder="Folio..." type="text">
                      </div>
                    </div>

                    <?php if ($data_usuario['IS_ADMIN'] === 'si') : ?>
                      <input name="id_sucursal" type="hidden">
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <div id="ventas-del-dia-table" class="card-body px-0 py-1"></div>

            <!-- CARD LOADING -->
            <?php include 'src/components/card-loading.php'; ?>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="ventas-del-dia-modal-ver-productos" tabindex="-1" role="dialog" aria-labelledby="ventas-del-dia-modal-label" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
  <div class="modal-dialog modal-lg" role="document">
    <form id="ventas-del-dia-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="ventas-del-dia-modal-label">Productos</h5>
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

<div id="modal-inventory" class="modal fade" role="dialog" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 id="pos-inventario-title" class="modal-title">Inventario</h5>
        <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="pos-inventario-filters-form" class="row" autocomplete="off">
          <div class="col-md-12 col-lg-12">
            <div class="card-header bg-white">
              <div class="row">
                <div class="col-12">
                  <div class="row">
                    <div class="col-12 col-md-6 mb-2 mb-lg-0">
                      <div class="form-group">
                        <label class="form-label" for="filter-search">Buscar aquí</label>
                        <input id="filter-search" class="form-control" name="search" placeholder="Código, Producto..." type="text">
                      </div>
                    </div>

                    <?php if ($data_usuario['IS_ADMIN'] === 'si') : ?>
                      <input name="id_sucursal" type="hidden">
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <div id="pos-inventario-table" class="card-body px-0 py-1"></div>

            <!-- CARD LOADING -->
            <?php include 'src/components/card-loading.php'; ?>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
      </div>
    </div>
  </div>
</div>