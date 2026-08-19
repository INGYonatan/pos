<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Sucursal</th>
        <th>Nombre comercial</th>
        <th>Tipo</th>
        <th>Serie</th>
        <th>Teléfono</th>
        <th>Correo</th>
        <th>Dirección</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) : ?>
        <tr class="align-middle">
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="">
                <img class="avatar-img rounded" height="40" width="70" src="<?= $row['logo'] ? BASE_URL . "/src/assets/images/sucursales/" . $row['logo'] : "https://placehold.co/700x400"; ?>" alt="Logo de la sucursal">
                <?php unset($row['logo']); ?>
              </div>

              <div>
                <?= $row['nombre_sucursal']; ?>
              </div>
            </div>
          </td>

          <td>
            <?= $row['nombre_comercial'] ?: '-'; ?>
          </td>

          <td style="text-transform: uppercase;">
            <?= $row['tipo']; ?>
          </td>

          <td>
            <?= $row['numero_serie']; ?>
          </td>

          <td>
            <?= formatPhoneNumber($row['telefono']); ?>
          </td>

          <td>
            <?= $row['correo'] ?: '-'; ?>
          </td>

          <td>
            <?= $row['direccion']; ?>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?= getTableActions($identifier, $row, [
                    "eliminar" => [
                      "condition" => $row['tipo'] !== 'almacen',
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