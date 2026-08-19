<?php
$camopro_use_limit_qunatity = $carrito_modal_productos_use_limit_quantity ? $carrito_modal_productos_use_limit_quantity : "si";
?>

<div id="carrito-modal-productos-modal" class="modal fade" role="dialog" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 id="carrito-modal-productos-title" class="modal-title">Productos</h5>
        <button class="btn-close" data-bs-dismiss="modal" type="button" aria-label="Close"></button>
      </div>

      <div class="modal-body pt-0">
        <form id="carrito-modal-productos-filters-form" class="row" autocomplete="off">
          <div class="col-md-12 col-lg-12">
            <div class="card-header bg-white px-0">
              <div class="row">
                <div class="col-12">
                  <div class="row">
                    <div class="col-12 col-md-6 mb-2 mb-lg-0">
                      <label class="form-group m-0">
                        <div class="form-label" for="carrito-modal-productos-filter-search">Buscar producto</div>
                        <input id="carrito-modal-productos-filter-search" class="form-control" name="search" placeholder="Nombre, código..." type="text">
                      </label>
                    </div>

                    <input id="carrito-modal-productos-branchId" name="branchId" value="<?= $carrito_modal_productos_branch_id; ?>" type="hidden">
                    <input id="carrito-modal-productos-useLimitQuantity" name="useLimitQuantity" value="<?= $camopro_use_limit_qunatity; ?>" type="hidden">
                  </div>
                </div>
              </div>
            </div>

            <div id="carrito-modal-productos-table" class="card-body px-0 py-1"></div>

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