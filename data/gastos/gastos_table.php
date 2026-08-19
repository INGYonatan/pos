<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Sucursal</th>
        <th>Concepto</th>
        <th>Forma de pago</th>
        <th class="text-end">Monto</th>
        <th>Comentario</th>
        <th>Fecha</th>

        <?php if ($haveActions) : ?>
          <th class="text-end">
            Acciones
          </th>
        <?php endif; ?>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($rows as $row) :
        $expenseConcepts  = new ExpenseConceptsHelper();
        $expenseConcepts->getById($row->getExpenseConceptId());

        $branchData       = getBranchOfficeData($row->getBranchId());

        /**
         * @var ExpensesHelper $row
         */
        $publicData = $row->toArray();
      ?>
        <tr>
          <td>
            <?= $branchData['nombre_sucursal']; ?>
          </td>

          <td>
            <?= $expenseConcepts->getConcept(); ?>
          </td>

          <td class="text-capitalize">
            <?= $row->getPaymentForm(); ?>
          </td>

          <td class="text-end">
            $<?= number_format($row->getAmount(), DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td>
            <?= $row->getComments() ? $row->getComments() : "--"; ?>
          </td>

          <td>
            <?= parseDateToSpanish($row->getDateTime()); ?>
          </td>

          <?php if ($haveActions) : ?>
            <td class="text-end">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?= getTableActions($identifier, $row->toArray()); ?>
                </div>
              </div>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php renderComponent("table-pagination", [
  "page"      => $page,
  "perPage"   => $per_page,
  "end"       => (($page - 1) * $per_page) + 1 + count($rows),
  "numPages"  => $result->data["numPages"],
  "total"     => $result->data["total"]
]); ?>