<?php $table_row_number = (($page - 1) * $perPage) + 1; ?>

<div class="table-responsive">
  <table class="table table-hover">
    <thead class="table-dark">
      <tr>
        <th>#</th>

        <th>Folio</th>

        <?php if ($type == "realizadas") : ?>
          <th>Solicitud enviada a</th>
        <?php endif; ?>

        <?php if ($type == "recibidas") : ?>
          <th>Sucursal que solicita</th>
        <?php endif; ?>

        <th>Observaciones</th>

        <th class="text-center">Estado</th>

        <?php if ($haveActions) : ?>
          <th class="text-right"></th>
        <?php endif; ?>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($rows as $row) :
        /**
         * @var TransferRequestsModel $row
         */

        // Obtener el nombre de las sucursales
        $originBranchData   = getBranchOfficeData($row->getOriginBranchId());
        $destinyBranchData  = getBranchOfficeData($row->getDestinationBranchId());

        $productsTable = getComponent("solicitudes-de-traspasos-products", [
          "transferRequestId"     => $row->getId(),
          "transferRequestStatus" => $row->getStatus()
        ]);
      ?>
        <tr>
          <td>
            <b><?= $table_row_number++; ?></b>
          </td>

          <td>
            <?= $row->getFolio(); ?>
          </td>

          <?php if ($type == "realizadas") : ?>
            <td>
              <?= $originBranchData["nombre_sucursal"]; ?>
            </td>
          <?php endif; ?>

          <?php if ($type == "recibidas") : ?>
            <td>
              <?= $destinyBranchData["nombre_sucursal"]; ?>
            </td>
          <?php endif; ?>

          <td>
            <?php if ($row->getNotes()) : ?>
              <?= $row->getNotes(); ?>
            <?php endif; ?>

            <?php if (!$row->getNotes()) : ?>
              --
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if ($row->getStatus() === "cancelado") : ?>
              <span class="badge bg-danger">Cancelada</span>
            <?php endif; ?>

            <?php if ($row->getStatus() === "pendiente") : ?>
              <span class="badge bg-warning text-dark">Pendiente</span>
            <?php endif; ?>

            <?php if ($row->getStatus() === "rechazado") : ?>
              <span class="badge bg-danger opacity-75">Rechazada</span>
            <?php endif; ?>

            <?php if ($row->getStatus() === "aprobado") : ?>
              <span class="badge bg-info">Aprobada</span>
            <?php endif; ?>

            <?php if ($row->getStatus() === "completado") : ?>
              <span class="badge bg-success">Completada</span>
            <?php endif; ?>
          </td>

          <?php if ($haveActions) :
            $btnStatusAction = [
              "action"  => "update-status",
              "alert"   => "si",
              "title"   => "Cambiar estado",
              "message" => "¿Deseas cambiar el estado de esta solicitud de traspaso?",
            ];

            $dataRow = [
              "uid" => $row->getId()
            ];
          ?>
            <td class="text-end">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <a class="dropdown-item btn-view-products" data-bs-toggle="modal" data-bs-target="#modal-show-products" data-table="<?= htmlentities($productsTable); ?>" href="javascript:void(0)">
                    <i class="fa fa-eye" style="width: 1.3rem;"></i>
                    Ver productos
                  </a>

                  <?php if ($type == "realizadas" && $row->getStatus() == "pendiente") :
                    $btnStatusAction["title"]       = "Cancelar solicitud";
                    $btnStatusAction["message"]     = "¿Deseas cancelar esta solicitud de traspaso?";
                    $btnStatusAction["actionValue"] = "cancelado";
                  ?>
                    <a class="dropdown-item btn-action" data-action="<?= htmlentities(json_encode($btnStatusAction)); ?>" data-row="<?= htmlentities(json_encode($dataRow)); ?>" href="javascript:void(0)">
                      <i class="fa fa-times text-danger" style="width: 1.3rem;"></i>
                      Cancelar
                    </a>
                  <?php endif; ?>

                  <?php if ($type == "recibidas") : ?>
                    <?php if ($row->getStatus() == "pendiente") :
                      $btnStatusAction["title"]       = "Aprobar solicitud";
                      $btnStatusAction["message"]     = "¿Deseas aprobar esta solicitud de traspaso?";
                      $btnStatusAction["actionValue"] = "aprobado";
                    ?>
                      <a class="dropdown-item btn-action" data-action="<?= htmlentities(json_encode($btnStatusAction)); ?>" data-row="<?= htmlentities(json_encode($dataRow)); ?>" href="javascript:void(0)">
                        <i class="fa fa-check text-success" style="width: 1.3rem;"></i>
                        Aprobar
                      </a>
                    <?php endif; ?>

                    <?php if ($row->getStatus() == "pendiente" || $row->getStatus() == "aprobado") : ?>
                      <a class="dropdown-item btn-action" href="<?= BASE_URL; ?>/inventario-transferir?suid=<?= md5($row->getId()); ?>&destination=<?= $row->getDestinationBranchId(); ?>&redirect=inventario-solicitudes-de-traspasos">
                        <i class="fa fa-check-double text-primary" style="width: 1.3rem;"></i>
                        <?php if ($row->getStatus() == "pendiente") : ?>
                          Aprobar y convertir a traspaso
                        <?php endif; ?>
                        <?php if ($row->getStatus() == "aprobado") : ?>
                          Convertir a traspaso
                        <?php endif; ?>
                      </a>
                    <?php endif; ?>

                    <?php if ($row->getStatus() == "pendiente") :
                      $btnStatusAction["title"]       = "Rechazar solicitud";
                      $btnStatusAction["message"]     = "¿Deseas rechazar esta solicitud de traspaso?";
                      $btnStatusAction["actionValue"] = "rechazado";
                    ?>
                      <hr class="my-1">

                      <a class="dropdown-item btn-action" data-action="<?= htmlentities(json_encode($btnStatusAction)); ?>" data-row="<?= htmlentities(json_encode($dataRow)); ?>" href="javascript:void(0)">
                        <i class="fa fa-times text-danger" style="width: 1.3rem;"></i>
                        Rechazar
                      </a>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?= paginate($page, $result->data->pages, 2, 'load'); ?>

<script>
  $(".btn-view-products").on("click", function() {
    const table = $(this).attr("data-table");
    $("#modal-show-products-table").html(table);
  });
</script>