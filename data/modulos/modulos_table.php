<style>
  .sortable-parent .sortable-child .sortable-child {
    display: none;
  }

  /* .sortable-parent:has(>li>.sortable-child>.list-group-item:not(.list-group-helper))>li>.sortable-child>.list-group-helper {
    display: none;
  } */
</style>

<ul class="list-group sortable-list bg-transparent mb-5 sortable-parent">
  <?php while ($row = mysqli_fetch_assoc($query_result)) :
    $query_submenu = $initial_query . "
      WHERE
        id_padre = {$row['id_modulo']}
      ORDER BY
        orden
      ASC
    ";

    $query_submenu_result = mysqli_query($mysqli, $query_submenu);
    $num_items            = mysqli_num_rows($query_submenu_result);
    $count_sub_items      = 0;

    $modulo_action_ids          = getModuloActionIds($row['id_modulo']);
    $rol_modulo_actions_ids     = getRolModuleActionIds($row['id_modulo']);
    $row['acciones']            = $modulo_action_ids;
    $row['modulo_rol_acciones'] = getRolModuleActionsCheckboxes($row['id_modulo'], $rol_modulo_actions_ids);
  ?>
    <li class="list-group-item bg-transparent border-0 p-0" data-id="<?= $row['uid']; ?>">
      <div class="card card-body m-0 p-2">
        <div class="d-flex justify-content-between align-items-center">
          <div class="gap-2 d-flex align-items-center">
            <a class="d-flex align-items-center justify-content-center border rounded btn-sorter text-dark" style="height: 2.1rem; width: 2.1rem;" href="javascript:void(0)">
              <i class="fa fa-bars"></i>
            </a>

            <span><i class="fa fa-file"></i> <?= $row['modulo']; ?></span>
          </div>

          <?php if ($have_actions) : ?>
            <div class="btn-group dropstart">
              <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                <i class="fa fa-ellipsis-v"></i>
              </a>

              <div class="dropdown-menu">
                <?= getTableActions($identifier, $row); ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </li>
  <?php endwhile; ?>
</ul>

<div id="sort-modulos-buttons" class="position-fixed" style="bottom: 1rem; right: 1rem; display: none;">
  <div class="btn-group">
    <button id="btn-cancel-sort-modulos" class="btn btn-secondary" type="button">
      <i class="fa fa-times me-1"></i> Cancelar
    </button>

    <button id="btn-save-sort-modulos" class="btn btn-primary" type="button">
      <i class="fa fa-save me-1"></i>
      Actualizar Orden
    </button>
  </div>
</div>

<script>
  // $(sortableListSelector).sortable({
  //   handle: ".cs-listItem-iconCircle",
  //   connectWith: sortableListSelector,
  //   placeholder: "ui-state-highlight",
  //   stop: () => {
  //     const orders = getItemsOrder();
  //     sortItems(orders);
  //   }
  // })

  $(".sortable-list").sortable({
    handle: ".btn-sorter",
    connectWith: ".sortable-list",
    placeholder: "ui-state-highlight",
    stop: () => {
      $("#sort-modulos-buttons").fadeIn();
    }
  });

  $("#btn-cancel-sort-modulos").on("click", () => {
    showPageLoading();
    location.reload();
  });

  $("#btn-save-sort-modulos").on("click", async () => {
    const alertResponse = await showSweetConfirm({
      title: "¿Estas seguro?",
      message: "Se actualizará el orden del menú de navegación"
    });

    if (!alertResponse) return;

    const orders = [];

    $(".sortable-list").each(function() {
      const ul = $(this);
      const parentId = ul.attr("data-parentid") || null;

      ul.children("li").each(function(idx) {
        const id = $(this).attr("data-id");

        if (id) orders.push({
          id,
          parentId,
          order: idx + 1
        });
      });
    });

    callEndpoint({
      place: "modulos",
      parameters: {
        action: "sort-modulos-items",
        items: JSON.stringify(orders)
      }
    }).then(response => {
      if (response.toastMessage) showSweetToast({
        icon: response.status,
        message: response.toastMessage
      });

      if (response.status == "success") load();
    });
  });

  $('.btn-permisos').on('click', function() {
    const data = JSON.parse($(this).attr('data-row'));
    $('#<?= $identifier; ?>-permisos-container').html(data.modulo_rol_acciones);
  });
</script>