<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr>
        <th>#</th>
        <th>Producto</th>
        <th>Sucursal</th>
        <th>Serie</th>
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
        /**
         * @var SerialNumbersHelper $row
         * @var ProductHelper $productsModel
         */
        $productsModel = new ProductHelper();
        $productsModel->get($row->getProductId());

        $publicData = $row->toArray();

        // Obtener la sucursal
        $queryBranch  = "SELECT nombre_sucursal FROM {$db_dti}_sucursales WHERE id_sucursal = '{$row->getBranchId()}' LIMIT 1";
        $resultBranch = mysqli_query($mysqli, $queryBranch);
        $branchData   = mysqli_fetch_assoc($resultBranch);
      ?>
        <tr>
          <td>
            <?= $productsModel->getCode(); ?>
          </td>

          <td>
            <?= $productsModel->getName(); ?>
          </td>

          <td>
            <?= $branchData['nombre_sucursal']; ?>
          </td>

          <td>
            <?= $row->getSerialNumber(); ?>
          </td>

          <td>
            <?= dateToSpanishStructure($row->getCreatedAt()); ?>
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

<?= paginate($page, $result->data["numPages"], 2, 'load'); ?>