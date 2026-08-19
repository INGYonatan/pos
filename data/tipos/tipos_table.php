<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Nombre</th>
        <th class="text-center">Requiere número de serie</th>
        <th class="text-center">Tangible</th>
        <th class="text-center">Es anticipo</th>
        <th class="text-center">Es nota de crédito</th>

        <?php if ($haveActions) : ?>
          <th class="text-end">
            Acciones
          </th>
        <?php endif; ?>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($rows as $row) :
        /**
         * @var TypesHelper $row
         */
        $publicData = $row->toArray();
      ?>
        <tr>
          <td>
            <?= $row->getName(); ?>
          </td>

          <td class="text-center">
            <?= $row->getRequiresSerialNumber() ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'; ?>
          </td>

          <td class="text-center">
            <?= $row->getTangible() ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'; ?>
          </td>

          <td class="text-center">
            <?= $row->getIsAdvance() ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'; ?>
          </td>

          <td class="text-center">
            <?= $row->getIsCreditNote() ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'; ?>
          </td>

          <?php if ($haveActions) : ?>
            <td class="text-end">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?= getTableActions($identifier, $publicData); ?>
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