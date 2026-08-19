<div id="modal-inventory" class="modal fade" role="dialog" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 id="modal-inventory-title" class="modal-title">Inventario</h5>
        <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="pos-inventario-filters-form" class="row" autocomplete="off">
          <div class="col-md-12 col-lg-12">
            <div class="card-header bg-white px-0">
              <div class="row">
                <div class="col-12">
                  <div class="row">
                    <div class="col-12 col-md-6 mb-2 mb-lg-0">
                      <div class="form-group form-group-sm">
                        <label class="form-label" for="filter-inv-search">Buscar aquí</label>
                        <input id="filter-inv-search" class="form-control" name="search" placeholder="Código, Producto..." type="text">
                      </div>
                    </div>

                    <input id="inv-branchId" name="branchId" value="<?= $user_data['id_sucursal']; ?>" type="hidden">
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

<div id="modal-today-sales" class="modal fade" role="dialog" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 id="modal-today-sales-title" class="modal-title">Ventas del día</h5>
        <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="ventas-del-dia-filters-form" class="row" autocomplete="off">
          <div class="col-md-12 col-lg-12">
            <div class="card-header bg-white px-0">
              <div class="row">
                <div class="col-12">
                  <div class="row">
                    <div class="col-12 col-md-6 mb-2 mb-lg-0">
                      <div class="form-group form-group-sm">
                        <label class="form-label" for="filter-ts-search">Buscar aquí</label>
                        <input id="filter-ts-search" class="form-control" name="search" placeholder="Código, Producto..." type="text">
                      </div>
                    </div>

                    <input id="ts-branchId" name="branchId" value="<?= $user_data['id_sucursal']; ?>" type="hidden">
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

<div class="modal fade" id="ventas-del-dia-modal-ver-productos" tabindex="-1" role="dialog" aria-labelledby="ventas-del-dia-modal-label" aria-hidden="true" style="background-color: rgba(0,0,0,0.6);">
  <div class="modal-dialog modal-lg" role="document">
    <form id="ventas-del-dia-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="ventas-del-dia-modal-label">Productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div id="tabla-ver-productos" class="modal-body"></div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
      </div>
    </form>
  </div>
</div>