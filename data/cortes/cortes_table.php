<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Folio</th>
        <th>Desde</th>
        <th>Hasta</th>
        <th>Sucursal</th>
        <th class="text-right">Total</th>
        <th class="text-right">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        //$productos = getStoreTransferProductsTable($row['id_inventario_transferencia']);
        //$row['productos'] = $productos;
      ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <?= $row['folio']; ?>
          </td>

          <td>
            <?= $row['fecha_desde_formato']; ?>
          </td>

          <td>
            <?= $row['fecha_hasta_formato']; ?>
          </td>

          <td>
            <?= $row['nombre_sucursal']; ?>
          </td>

          <td class="text-right">
            $<?= number_format($row['total'], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-right">
            <div class="btn-group btn-group-sm">
              <a class="btn btn-primary" target="_blank" href="ticket-corte?uid=<?= $row['id_corte_caja']; ?>" title="Imprimir ticket">
                <i class="fa fa-print"></i>
              </a>
            </div>
          </td>
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