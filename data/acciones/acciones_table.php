<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Acción</th>
        <th>Tipo</th>
        <th>Ubicación</th>
        <th>Ícono</th>
        <th>Clase HTML</th>
        <th>Orden</th>
        <th>Identificador</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) : ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <?= $row['accion']; ?>
          </td>

          <td>
            <?= $row['tipo']; ?>
          </td>

          <td>
            <?php if ($row['ubicacion'] === 'vista') : ?>
              <span class="badge bg-info">
                <i class="fa fa-eye"></i> Vista
              </span>
            <?php endif; ?>

            <?php if ($row['ubicacion'] === 'filtros') : ?>
              <span class="badge bg-primary">
                <i class="fa fa-filter"></i> Filtros
              </span>
            <?php endif; ?>

            <?php if ($row['ubicacion'] === 'tabla') : ?>
              <span class="badge bg-success">
                <i class="fa fa-table"></i> Tabla
              </span>
            <?php endif; ?>
          </td>

          <td>
            <?php if (empty($row['icono'])) : ?>
              <span class="badge bg-warning">Sin ícono</span>
            <?php endif; ?>

            <?php if (!empty($row['icono'])) : ?>
              <i class="<?= $row['icono']; ?>"></i>
            <?php endif; ?>
          </td>

          <td>
            <?php if (empty($row['clase_html'])) : ?>
              <span class="badge bg-warning">Sin clase</span>
            <?php endif; ?>

            <?php if (!empty($row['clase_html'])) : ?>
              <?= $row['clase_html']; ?>
            <?php endif; ?>
          </td>

          <td>
            <?= $row['orden']; ?>
          </td>

          <td>
            <?= $row['slug']; ?>
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

<?= paginate($page, $request['num_pages'], 2, 'load'); ?>