<?php $cart_serlal_numbers_id = $cart_serlal_numbers_id ? $cart_serlal_numbers_id : $page_config['page_identifier']; ?>

<div id="modal-serialNumbers" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <form id="serialNumbers-form" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Números de serie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="<?= $cart_serlal_numbers_id; ?>-serialNumbers"></div>

        <div class="d-flex align-items-center justify-content-center">
          <button id="<?= $cart_serlal_numbers_id; ?>-add-serial-number" class="btn btn-secondary" type="button">
            <i class="fa fa-plus-circle"></i> Número de serie
          </button>
        </div>
      </div>

      <input id="<?= $cart_serlal_numbers_id; ?>-itemId" name="itemId" type="hidden">
      <input id="<?= $cart_serlal_numbers_id; ?>-adjustment" name="adjustment" type="hidden">
      <input id="<?= $cart_serlal_numbers_id; ?>-branchId" name="branchId" type="hidden">
      <input id="<?= $cart_serlal_numbers_id; ?>-originBranchId" name="originBranchId" type="hidden">

      <input name="uid" type="hidden">
      <input name="action" value="update-serialNumbers-<?= $cart_serlal_numbers_id; ?>" type="hidden">
      <input name="place" value="<?= $cart_serlal_numbers_id; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>