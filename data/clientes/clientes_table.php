<?php
$table_row_number = (($page - 1) * $per_page) + 1;

$customers = [];

while ($row = mysqli_fetch_assoc($request['query_result'])) {
  $customers[] = $row;
}
?>

<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Cliente</th>
        <th>Nombre comercial</th>
        <th>R.F.C.</th>
        <th class="text-end">Límite de crédito</th>
        <th class="text-center">Plazo de crédito (días)</th>
        <?php if ($have_actions) : ?>
          <th class="text-end">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($customers as $row) : ?>
        <tr>
          <th scope="row">
            <?= $table_row_number++; ?>
          </th>

          <td>
            <a class="text-primary" href="<?= BASE_URL; ?>/cliente/<?= $row['id_cliente']; ?>/datos-generales"><?= $row['nombre_completo']; ?></a>

            <?php if ($row["correo"]) : ?>
              <br>
              <?= $row["correo"]; ?>
            <?php endif; ?>

            <?php if ($row["telefono"]) : ?>
              <br>
              <?= formatPhoneNumber($row["telefono"]); ?>
            <?php endif; ?>
          </td>

          <td>
            <?php if ($row["nombre_comercial"]) : ?>
              <?= $row["nombre_comercial"]; ?>
            <?php endif; ?>

            <?php if (!$row["nombre_comercial"]) : ?>
              --
            <?php endif; ?>
          </td>

          <td>
            <?php if ($row["rfc"]) : ?>
              <code><?= $row["rfc"]; ?></code>
            <?php endif; ?>

            <?php if ($row["id_regimen_fiscal"]) : ?>
              <br>
              <i><?= $row["id_regimen_fiscal"]; ?> - <?= $row["regimen_fiscal"]; ?></i>
            <?php endif; ?>
          </td>

          <td class="text-end">
            $<?= number_format($row["limite_credito"], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-center">
            <?= $row["limite_credito_plazo"]; ?> días
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-end">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <a class="dropdown-item btn-edit" data-row="<?= htmlspecialchars(json_encode($data_row)); ?>" href="<?= BASE_URL; ?>/cliente/<?= $row['uid']; ?>/direcciones">
                    <i class="fa fa-map-marker"></i> Direcciones
                  </a>

                  <?= getTableActions($identifier, $row); ?>
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
  "end"       => $table_row_number,
  "numPages"  => $request['num_pages'],
  "total"     => $request['total']
]); ?>