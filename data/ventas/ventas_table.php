<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">
          <div class="d-flex align-items-center gap-1">
            <div class="form-check m-0">
              <?php
              $ischeckedAll = false;
              $num_total_rows = mysqli_num_rows($request['query_result']) * $page;

              if (count($selectedSales) >= $num_total_rows && $num_total_rows > 0) {
                $ischeckedAll = true;
              }
              ?>
              <input id="ventas-check-all" class="form-check-input cursor-pointer" type="checkbox" <?= $ischeckedAll ? 'checked' : ''; ?>>
            </div>

            <div id="ventas-ms-menu" <?= count($selectedSales) > 0 ? '' : 'style="display: none;"'; ?>>
              <div class="dropdown dropend">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <!-- <a id="btn-masivo-referencia-pago" class="dropdown-item btn-modal" data-bs-toggle="modal" data-bs-target="#modal-ventas-ms-referencia-pago" href="javascript:void(0)">
                    <i class="fa fa-plus-circle"></i> Referencia de pago
                  </a> -->

                  <!-- Timbrar ventas seleccionadas -->
                  <a id="btn-timbrar-ventas-seleccionadas" class="dropdown-item" data-href="<?= BASE_URL; ?>/facturas/nueva?tipo_factura=ingreso" href="javascript:void(0)">
                    <i class="fa fa-file"></i> Generar factura global
                  </a>
                </div>
              </div>
            </div>
          </div>
        </th>
        <th>Folio</th>
        <th>Fecha</th>
        <th>Sucursal</th>
        <th>Cliente</th>
        <th class="text-center">Tipo</th>
        <th class="text-center">Forma de pago</th>
        <th class="text-right">Subtotal</th>
        <th class="text-right">IEPS</th>
        <th class="text-right">IVA</th>
        <th class="text-right">Total</th>
        <th class="text-center">Timbrado</th>
        <th>Realizó</th>
        <th class="text-center">Estatus</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $type       = "";
        $type_color = 'light text-danger';

        $metodoPago = "-";

        if ($row["efectivo"] > 0) {
          $referencia = "";

          if ($row["efectivo_referencia"]) {
            $referencia = " - Ref: {$row['efectivo_referencia']}";
          }

          $metodoPago .= "<p class='m-0'>* Efectivo{$referencia}</p>";
        }

        if ($row["cheque"] > 0)           $metodoPago .= "<p class='m-0'>* Cheque</p>";
        if ($row["transferencia"] > 0)    $metodoPago .= "<p class='m-0'>* Transferencia</p>";
        if ($row["tarjeta_credito"] > 0)  $metodoPago .= "<p class='m-0'>* TC</p>";
        if ($row["tarjeta_debito"] > 0)   $metodoPago .= "<p class='m-0'>* TD</p>";

        $productos        = get_sale_details_table($row['id_venta']);

        $tableProductos = "<h4>Método de pago:</h4>";
        $tableProductos .= $metodoPago;
        $tableProductos .= $productos;
        $row['productos'] = $tableProductos;

        $invoice = null;

        if ($row["tipo_transaccion"] == "venta")        $invoice = getSaleInvoiceBySaleIdAndType($row['id_venta'], "ingreso");
        if ($row["tipo_transaccion"] == "anticipo")     $invoice = getSaleInvoiceBySaleIdAndType($row['id_venta'], "anticipo");
        if ($row["tipo_transaccion"] == "nota-credito") $invoice = getSaleInvoiceBySaleIdAndType($row['id_venta'], "nota_credito");

        $saleTotals = getSaleTotalsBySaleId($row['id_venta']);

        $isChecked = in_array($row["uid"], $selectedSales) ? 'checked' : '';

        // Obtener los tipos de productos de la venta
        $detailsQuery = "SELECT
            T.nombre AS tipo_producto
          FROM
            paal_venta_productos AS VP
          INNER JOIN
            paal_productos AS P ON (VP.id_producto = P.id_producto)
          INNER JOIN
            paal_tipos AS T ON (P.id_tipo = T.id_tipo)
          WHERE
            VP.id_venta = {$row['id_venta']}
        ";

        $detailsResult  = mysqli_query($mysqli, $detailsQuery);
        $numDetails     = mysqli_num_rows($detailsResult);
        $productTypes   = [];

        while ($detailRow = mysqli_fetch_assoc($detailsResult)) {
          $productTypes[] = $detailRow['tipo_producto'];
        }

        $productTypes = implode(", ", array_unique($productTypes));
      ?>
        <tr class="align-middle">
          <th scope="row">
            <div class="form-check m-0">
              <input id="<?= $row["uid"]; ?>-ventas-check" class="form-check-input cursor-pointer ventas-table-check" value="<?= $row["uid"]; ?>" type="checkbox" <?= $isChecked; ?>>
            </div>
          </th>

          <td>
            <?php if ($row["tipo_transaccion"] == "venta") : ?>
              <span class="badge bg-primary">Venta</span>
            <?php endif; ?>

            <?php if ($row["tipo_transaccion"] == "anticipo") : ?>
              <span class="badge bg-danger">Anticipo</span>
            <?php endif; ?>

            <?php if ($row["tipo_transaccion"] == "nota-credito") : ?>
              <span class="badge bg-danger">Nota de crédito</span>
            <?php endif; ?>

            <br>

            <?= $row['folio']; ?>

            <?php if ($row['folio_cotizacion']) : ?>
              <br>
              <span class="badge bg-info text-dark" title="Folio de cotización"><?= $row['folio_cotizacion']; ?></span>
            <?php endif; ?>

            <?php if ($row["folio_sae"]) : ?>
              <br>
              <span class="badge bg-danger" title="Folio SAE"><?= $row['folio_sae']; ?></span>
            <?php endif; ?>

            <?php if ($invoice) : ?>
              <br>
              <span class="badge bg-dark"><?= $invoice["serie"] ?>-<?= $invoice["folio"]; ?></span>
            <?php endif; ?>
          </td>

          <td>
            <?= $row['fecha_creacion_formato']; ?>
          </td>

          <td>
            <?= $row['nombre_sucursal']; ?>
          </td>

          <td>
            <span class="fw-bold text-dark" href="<?= BASE_URL; ?>/cliente/<?= $row['id_cliente']; ?>/ventas"><?= $row['nombre_cliente']; ?></span>
            <!-- <br>
            <span class="badge bg-light text-danger"><?= $row["tipo_productos"]; ?></span> -->
          </td>

          <td class="text-center">
            <?= $productTypes; ?>
          </td>

          <td class="text-center">
            <?= $row['forma_pago']; ?>
          </td>

          <td class="text-right">
            $<?= number_format($saleTotals["subtotal"], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-right">
            $<?= number_format($saleTotals["ieps"] ?? 0, DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-right">
            $<?= number_format($saleTotals["iva"], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-right">
            <b>$<?= number_format($saleTotals["total"], DECIMALS_CURRENCY_TICKET); ?></b>
            <?= $metodoPago; ?>
          </td>

          <td class="text-center">
            <?php if (!$invoice) : ?>
              <?php if ($row['status'] === 'activo') : ?>
                <span class="badge bg-warning text-dark mb-1">No</span><br>
                <?php if ($row["tipo_transaccion"] == "venta") : ?>
                  <a class="btn btn-xs btn-secondary" target="_blank" href="<?= BASE_URL; ?>/facturas/nueva?tipo_factura=ingreso&folio=<?= $row['folio']; ?>">Timbrar</a>
                <?php endif; ?>

                <?php if ($row["tipo_transaccion"] == "nota-credito") : ?>
                  <a class="btn btn-xs btn-secondary" target="_blank" href="<?= BASE_URL; ?>/facturas/nueva?tipo_factura=nota-de-credito&folio=<?= $row['folio']; ?>">Timbrar</a>
                <?php endif; ?>

                <?php if ($row["tipo_transaccion"] == "anticipo") : ?>
                  <a class="btn btn-xs btn-secondary" target="_blank" href="<?= BASE_URL; ?>/facturas/nueva?tipo_factura=anticipo-de-compra&folio=<?= $row['folio']; ?>">Timbrar</a>
                <?php endif; ?>
              <?php endif; ?>

              <?php if ($row['status'] === 'cancelado') : ?>
                --
              <?php endif; ?>
            <?php endif; ?>

            <?php if ($invoice) : ?>
              <span class="badge bg-success mb-1">Sí</span><br>

              <?php
              $invoiceFolderUrl = "";

              if ($row["tipo_transaccion"] == "venta")        $invoiceFolderUrl = CARPETA_FACTURAS_INGRESO_URL;
              if ($row["tipo_transaccion"] == "anticipo")     $invoiceFolderUrl = CARPETA_FACTURAS_ANTICIPO_DE_COMPRA_URL;
              if ($row["tipo_transaccion"] == "nota-credito") $invoiceFolderUrl = CARPETA_FACTURAS_NOTA_DE_CREDITO_URL;
              ?>

              <a href="<?= $invoiceFolderUrl; ?><?= $invoice['uuid']; ?>.xml" target="_blank">
                <i class="fa fa-file text-primary"></i> Archivo XML
              </a>
              <br>
              <a href="<?= $invoiceFolderUrl; ?><?= $invoice['uuid']; ?>.pdf" target="_blank">
                <i class="fa fa-file text-primary"></i> Archivo PDF
              </a>
            <?php endif; ?>
          </td>

          <td>
            <?= $row['nombre_completo']; ?>
          </td>

          <td class="text-center">
            <?php if ($row['status'] === 'activo') : ?>
              <i class="fa fa-check-circle text-success"></i> Activo
            <?php endif; ?>

            <?php if ($row['status'] === 'cancelado') : ?>
              <i class="fa fa-check-circle text-danger"></i> Cancelado
            <?php endif; ?>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?php /* if (!$row["folio_sae"]) : ?>
                    <a class="dropdown-item btn-modal" data-bs-toggle="modal" data-bs-target="#modal-agregar-folio-sae" data-row="<?= htmlentities(json_encode($row)); ?>" href="javascript:void(0)">
                      <i class="fa fa-barcode"></i> Agregar folio SAE
                    </a>
                  <?php endif; */ ?>

                  <?php /*if ($row["forma_pago"] === "contado" && $row["efectivo"] > 0) : ?>
                    <a class="dropdown-item btn-modal btn-payment-reference" data-bs-toggle="modal" data-bs-target="#modal-agregar-referencia-efectivo" data-row="<?= htmlentities(json_encode($row)); ?>" href="javascript:void(0)">
                      <i class="fa fa-plus-circle"></i> Referencia de pago
                    </a>
                  <?php endif;*/ ?>

                  <!-- <hr class="my-1"> -->

                  <?= getTableActions($identifier, $row, [
                    'cancelar' => [
                      'condition' => $row['status'] === 'activo'
                    ],
                    "estado-de-cuenta" => [
                      "condition" => $row["forma_pago"] == "credito",
                      "type" => "customLink",
                      "customLink" => BASE_URL . "/ventas/{$row['folio']}/pagos"
                    ]
                  ]); ?>
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

<script>
  $(".ventas-table-check").on("click", function() {
    // #selected-sales
    let selectedSales = $("#selected-sales").val() ? JSON.parse($("#selected-sales").val()) : [];

    const saleId = $(this).val();
    const isChecked = $(this).is(":checked");

    if (!isChecked) {
      // Eliminar el ID de la venta si está desmarcada
      selectedSales = selectedSales.filter(id => id !== saleId);
    } else {
      // Agregar el ID de la venta si está marcada
      selectedSales.push(saleId);
    }

    // Mostrar u ocultar el menú de acciones múltiples
    if (selectedSales.length > 0) {
      $('#ventas-ms-menu').show();
    } else {
      $('#ventas-ms-menu').hide();
    }

    // hacer check al checkbox "check all" si todos los checkboxes están seleccionados
    const totalCheckboxes = $(".ventas-table-check").length;
    const checkedCheckboxes = $(".ventas-table-check:checked").length;

    if (totalCheckboxes === checkedCheckboxes) {
      $("#ventas-check-all").prop("checked", true);
    } else {
      $("#ventas-check-all").prop("checked", false);
    }

    // Actualizar el valor del campo oculto
    $("#selected-sales").val(JSON.stringify(selectedSales));
  });

  $("#ventas-check-all").on("click", function() {
    const isChecked = $(this).is(":checked");

    $(".ventas-table-check").prop("checked", !isChecked).trigger("click");
  });

  $("#btn-masivo-referencia-pago").on("click", function() {
    const selectedSales = $("#selected-sales").val();
    $("#masivo-agregar-referencia-efectivo-form-data input[name='uids']").val(selectedSales);
  });

  $("#btn-timbrar-ventas-seleccionadas").on("click", function() {
    const selectedSales = $("#selected-sales").val();

    // Construir la URL con los UIDs seleccionados separados por comas y quitar las llaves y comillas
    const selectedSalesArray = JSON.parse(selectedSales);
    const uids = selectedSalesArray.map(uid => uid.replace(/[{}"]/g, '')).join(',');

    const url = $(this).data("href") + `&uids=${encodeURIComponent(uids)}`;

    window.open(url, '_blank');
  });
</script>