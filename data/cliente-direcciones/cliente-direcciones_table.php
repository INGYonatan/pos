<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Dirección</th>

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
            <?php if ($row["nombre_comercial"]) : ?>
              <span class="fw-bold"><?= $row["nombre_comercial"]; ?></span><br>
            <?php endif; ?>

            <?= $row['calle']; ?> <?= $row['numero_exterior']; ?> Barrio <?= $row['colonia']; ?> <?= $row['codigo_postal']; ?> <?= $row['ciudad']; ?>, <?= $row['estado']; ?>.<br>
            <span class="fw-bold">Entre calles:</span> <?= $row['entre_calles']; ?><br>
            <span class="fw-bold">Referencias:</span> <?= $row['referencias']; ?>
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

<script>
  $('.btn-edit').on('click', function() {
    const data = JSON.parse($(this).attr('data-row'));

    getCitys({
      id: data.id_estado,
      cityId: data.id_ciudad
    });

    getNeighborhood({
      id: data.id_ciudad,
      neighborhoodId: data.id_colonia
    });
  });
</script>