<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Nombre</th>

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
         * @var UserFilesHelper $row
         */
        $publicData = $row->toArray();
      ?>
        <tr class="align-middle">
          <td>
            <a class="d-flex align-items-center gap-2" target="_blank" href="<?= BASE_URL . "/src/assets/userfiles/{$row->getSlug()}"; ?>">
              <?php if ($row->getType() == "pdf") : ?>
                <div>
                  <div class="avatar" style="height: 2.5rem; width: 2.5rem;">
                    <div class="avatar-title bg-soft-danger text-danger rounded-pill">
                      <i class="fa fa-file-pdf font-24"></i>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ($row->getType() == "xls" || $row->getType() == "xlsx") : ?>
                <div>
                  <div class="avatar" style="height: 2.5rem; width: 2.5rem;">
                    <div class="avatar-title bg-soft-success text-success rounded-pill">
                      <i class="fa fa-file-excel font-24"></i>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

              <!-- folder -->
              <?php if ($row->getType() == "folder") : ?>
                <div>
                  <div class="avatar" style="height: 2.5rem; width: 2.5rem;">
                    <div class="avatar-title bg-soft-primary text-primary rounded-pill">
                      <i class="fa fa-folder font-24"></i>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

              <div class="fw-bold">
                <?= $row->getName(); ?>
              </div>
            </a>
          </td>

          <?php if ($haveActions) : ?>
            <td class="text-end">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <a class="dropdown-item" href="<?= BASE_URL . "/src/assets/userfiles/{$row->getSlug()}"; ?>" target="_blank">
                    <i class="fa fa-eye"></i> Ver archivo
                  </a>

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