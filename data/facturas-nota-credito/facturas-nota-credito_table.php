<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-hover table-sm">
    <thead>
      <tr class="table-dark">
        <th>#</th>
        <th>Folio</th>
        <th>Fecha</th>
        <th>Emisor</th>
        <th>Cliente</th>
        <th>Archivos</th>
        <th class="text-center">F.Pago</th>
        <th class="text-end">Monto</th>
        <th class="text-center">MetodoP.</th>
        <th class="text-center">Enviada</th>
        <th class="text-center">Estatus</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $dataRow = htmlentities(json_encode([
          "uid" => $row["uid"]
        ]));

        $sendInvoiceActionData = htmlentities(json_encode([
          "action"  => "send-invoice",
          "alert"   => "si",
          "title"   => "¡Enviar Factura!",
          "message" => "¿Realmente desea enviar la factura?"
        ]));
      ?>
        <tr>
          <td style="width: 8px;"><?= $table_row_number; ?></td>

          <td>
            <?= $row['serie']; ?>-<?= $row['folio']; ?>

            <?php if ($row['folio_venta']) : ?>
              <br>
              <small class="text-muted">
                <span class="badge bg-dark">
                  Venta: <?= $row['folio_venta']; ?>
                </span>
              </small>
            <?php endif; ?>
          </td>

          <td>
            <?= $row["fecha_formato"]; ?>
          </td>

          <td>
            <?= $row['Emisor']; ?>
          </td>

          <td>
            <?= $row['nombre_cliente']; ?>
          </td>

          <td>
            <a href="<?= BASE_URL; ?>/download?uid=<?= CARPETA_FACTURAS_NOTA_DE_CREDITO_URL ?>/<?= $row['uuid']; ?>.xml">
              <i class="fa fa-file text-primary"></i> Archivo XML
            </a>
            <br>
            <a href="<?= BASE_URL; ?>/download?uid=<?= CARPETA_FACTURAS_NOTA_DE_CREDITO_URL ?>/<?= $row['uuid']; ?>.pdf">
              <i class="fa fa-file text-primary"></i> Archivo PDF
            </a>
            <br>
            <?= $row["uuid"]; ?>
          </td>

          <td class="text-center">
            <?= $row["forma_pago"]; ?>
          </td>

          <td class="text-end">
            $<?= number_format($row["total"], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-center">
            <?= $row["metodo_pago"]; ?>

            <?php if ($row["metodo_pago"] == "PPD"): ?>
              <a class="btn btn-light btn-sm" href="<?= BASE_URL; ?>/facturas/<?= md5($row["uid"]); ?>/pagos">Pagos</a>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if (!$row["enviado"]) : ?>
              <span class="badge bg-danger">No enviada</span><br>
              <a class="btn btn-light btn-sm btn-action" data-action="<?= $sendInvoiceActionData; ?>" data-row=<?= htmlentities(json_encode(["uid" => $row["id_factura"]])); ?> href="javascript:void(0)">Enviar</a>
            <?php endif; ?>

            <?php if ($row["enviado"]) : ?>
              <span class="badge bg-success">Enviada</span><br>
              <a class="btn btn-light btn-sm btn-action" data-action="<?= $sendInvoiceActionData; ?>" data-row=<?= htmlentities(json_encode(["uid" => $row["id_factura"]])); ?> href="javascript:void(0)">Reenviar</a>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if (!$row["cancelado"]) : ?>
              <span class="badge bg-success">Activa</span><br>
              <a class="btn btn-light btn-sm btn-modal" data-bs-toggle="modal" data-bs-target="#modal-cancelar-factura" data-row="<?= $dataRow; ?>" href="javascript:void(0)">Cancelar</a>
            <?php endif; ?>

            <?php if ($row["cancelado"]) : ?>
              <span class="badge bg-danger">Cancelado</span>
            <?php endif; ?>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?= getTableActions($identifier, $row); ?>
                </div>
              </div>
            </td>
          <?php endif; ?>
        </tr>

        <?php $table_row_number++; ?>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php renderComponent("table-pagination", [
  "page"      => $page,
  "perPage"   => $per_page,
  "end"       => $table_row_number,
  "numPages"  => $request['num_pages'],
  "total"     => $request['total']
]); ?>