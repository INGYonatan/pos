<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Tipo</th>
        <th class="text-center">Folio</th>
        <th>Fecha</th>
        <th>Emisor</th>
        <th>Cliente</th>
        <th class="text-center">XML</th>
        <th class="text-center">PDf</th>
        <th class="text-center">F.Pago</th>
        <th class="text-end">Monto</th>
        <th>Enviada</th>
        <th class="text-center">Estatus</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
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
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            P
          </td>

          <td class="text-center">
            <?= $row['serie']; ?>-<?= $row['folio']; ?><br>
            <span class="badge bg-warning text-dark"><span class="fw-bold text-dark">Folio ingreso: </span><?= $row["folio_ingreso"]; ?></span>
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

          <td class="text-center">
            <a href="<?= BASE_URL; ?>/download?uid=<?= CARPETA_FACTURAS_PAGO_URL ?>/<?= $row['uuid']; ?>.xml">
              <i class="fa fa-file text-primary"></i> Archivo XML
            </a>
            <br>
            <?= $row["UUID"]; ?>
          </td>

          <td class="text-center">
            <a href="<?= BASE_URL; ?>/download?uid=<?= CARPETA_FACTURAS_PAGO_URL ?>/<?= $row['uuid']; ?>.pdf">
              <i class="fa fa-file text-primary"></i> Archivo PDF
            </a>
          </td>

          <td class="text-center">
            <?= $row["forma_pago"]; ?>
          </td>

          <td class="text-end">
            $<?= number_format($row["monto"], DECIMALS_CURRENCY_TICKET); ?>
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