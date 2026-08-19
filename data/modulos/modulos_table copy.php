<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Módulo</th>
        <th>Identificador</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $modulo_action_ids          = getModuloActionIds($row['id_modulo']);
        $rol_modulo_actions_ids     = getRolModuleActionIds($row['id_modulo']);
        $row['acciones']            = $modulo_action_ids;
        $row['modulo_rol_acciones'] = getRolModuleActionsCheckboxes($row['id_modulo'], $rol_modulo_actions_ids);
      ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <?= $row['modulo']; ?>
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

<script>
  $('.btn-permisos').on('click', function() {
    const data = JSON.parse($(this).attr('data-row'));
    $('#<?= $identifier; ?>-permisos-container').html(data.modulo_rol_acciones);
  });
</script>