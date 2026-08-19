<?php
require_once __DIR__ . "/../../data/lib/helpers/expense-concepts.helper.php";

$modal_gastos_id      = $modal_gastos_id      ? $modal_gastos_id      : $page_config['page_identifier'];
$modal_gastos_origin  = $modal_gastos_origin  ? $modal_gastos_origin  : '';
$modal_gastos_style   = $modal_gastos_origin == "productos" ? 'modal-secondary' : "";

$modal_gastos_expenseConceptsHelper = new ExpenseConceptsHelper();
$modal_gastos_expenseConcepts = $modal_gastos_expenseConceptsHelper->getAll()->data['rows'];
?>

<div class="modal fade <?= $modal_gastos_style; ?>" id="<?= $modal_gastos_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_gastos_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $modal_gastos_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_gastos_id; ?>-modal-label">Nueva marca</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdg-date">Fecha<span class="text-danger">*</span></label>
              <input id="fdg-date" class="form-control datepicker" name="date" value="<?= date('Y-m-d'); ?>" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <?php if ($IS_ADMIN) : ?>
            <div class="col-12 col-lg-6">
              <div class="form-group">
                <label class="form-label" for="fdg-branchId">Sucursal<span class="text-danger">*</span></label>
                <select id="fdg-branchId" class="form-control form-select" name="branchId" required>
                  <?= getBranchOfficesCatalog("", "--Seleccionar--", true); ?>
                </select>
              </div>
            </div>
          <?PHP endif; ?>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdg-expenseConceptId">Conceptos<span class="text-danger">*</span></label>
              <select id="fdg-expenseConceptId" class="form-control form-select" name="expenseConceptId" required>
                <option value="">--Seleccionar--</option>

                <?php foreach ($modal_gastos_expenseConcepts as $concept) :
                  /**
                   * @var ExpenseConceptsHelper $concept
                   */
                ?>
                  <option value="<?= $concept->getId(); ?>"><?= $concept->getConcept(); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdg-paymentForm">Forma de pago<span class="text-danger">*</span></label>
              <select id="fdg-paymentForm" class="form-control form-select" name="paymentForm" required>
                <option value="">--Seleccionar--</option>
                <option value="efectivo">Efectivo</option>
                <option value="cheque">Cheque</option>
                <option value="transferencia">Transferencia</option>
                <option value="tarjeta-credito">Tarjeta de Crédito</option>
                <option value="tarjeta-debito">Tarjeta de Débito</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fdg-amount">Monto<span class="text-danger">*</span></label>
              <input id="fdg-amount" class="form-control" name="amount" placeholder="0.00" type="number" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="fdg-comments">Comentarios</label>
              <textarea id="fdg-comments" class="form-control" name="comments" rows="3" placeholder="Comentarios (opcional)"></textarea>
            </div>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" value="add-<?= $modal_gastos_id; ?>" type="hidden">
      <input name="place" value="<?= $modal_gastos_id; ?>" type="hidden">
      <input name="origin" value="<?= $modal_gastos_origin; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>