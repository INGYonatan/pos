<ul class="list-group sortable-list bg-transparent mb-5">
  <?php while ($row = mysqli_fetch_assoc($query_result)) :
    $query_submenu = $initial_query . "
      WHERE pertenece_a = $row[id]
      ORDER BY orden
      ASC
    ";

    $query_submenu_result = mysqli_query($mysqli, $query_submenu);
    $num_items            = mysqli_num_rows($query_submenu_result);
    $count_sub_items      = 0;
  ?>
    <li class="list-group-item bg-transparent border-0 p-0" data-id="<?= $row['uid']; ?>">
      <div class="card card-body m-0 p-1">
        <div class="d-flex justify-content-between align-items-center">
          <div class="gap-2 d-flex align-items-center">
            <a class="d-flex align-items-center justify-content-center border rounded btn-sorter text-dark" style="height: 2.1rem; width: 2.1rem;" href="javascript:void(0)">
              <i class="fa fa-bars"></i>
            </a>

            <span><i class="<?= $row['icono'] ?>"></i> <?= $row['titulo']; ?></span>
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

      <?php if ($num_items) : ?>
        <ul class="list-group sortable-list bg-transparent ps-3" data-parentId="<?= $row['uid']; ?>">
          <?php while ($item = mysqli_fetch_assoc($query_submenu_result)) : ?>
            <li class="list-group-item bg-transparent border-0 p-0" data-id="<?= $item['uid']; ?>">
              <div class="card card-body p-1 m-0 <?= $count_sub_items === 0 ? 'border-top-0' : '' ?>">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="gap-2 d-flex align-items-center">
                    <a class="d-flex align-items-center justify-content-center border rounded btn-sorter text-dark" style="height: 2.1rem; width: 2.1rem;" href="javascript:void(0)">
                      <i class="fa fa-bars"></i>
                    </a>

                    <i class="<?= $item['icono'] ? $item['icono'] : 'fa fa-file'; ?>"></i> <?= $item['titulo']; ?>
                  </div>

                  <?php if ($have_actions) : ?>
                    <div class="btn-group dropstart">
                      <a class="btn btn-sm btn-soft-warning px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                        <i class="fa fa-ellipsis-v"></i>
                      </a>

                      <div class="dropdown-menu">
                        <?= getTableActions($identifier, $item); ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </li>

            <?php $count_sub_items++; ?>
          <?php endwhile; ?>
        </ul>
      <?php endif; ?>
    </li>

    <?php /* 
    <li class="list-group-item <?= $num_items ? 'border-bottom' : '' ?>">
      <div class="d-flex justify-content-between align-items-center <?= $num_items ? "border-bottom pb-2" : ""; ?>">
        <div class="gap-2 d-flex align-items-center">
          <a class="d-flex align-items-center justify-content-center border rounded btn-sorter text-dark" style="height: 2.5rem; width: 2.5rem;" href="javascript:void(0)">
            <i class="fa fa-bars"></i>
          </a>

          <span><i class="<?= $row['icono'] ?>"></i> <?= $row['titulo']; ?></span>
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

      <?php if ($num_items) : ?>
        <ul class="list-group sortable-list bg-transparent">
          <?php while ($item = mysqli_fetch_assoc($query_submenu_result)) : ?>
            <li class="list-group-item d-flex justify-content-between align-items-center <?= $count_sub_items === 0 ? 'border-top-0' : '' ?>">
              <div class="gap-2 d-flex align-items-center">
                <a class="d-flex align-items-center justify-content-center border rounded btn-sorter text-dark" style="height: 2.5rem; width: 2.5rem;" href="javascript:void(0)">
                  <i class="fa fa-bars"></i>
                </a>

                <i class="<?= $item['icono'] ? $item['icono'] : 'fa fa-file'; ?>"></i> <?= $item['titulo']; ?>
              </div>

              <?php if ($have_actions) : ?>
                <div class="btn-group dropstart">
                  <a class="btn btn-sm btn-soft-warning px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                    <i class="fa fa-ellipsis-v"></i>
                  </a>

                  <div class="dropdown-menu">
                    <?= getTableActions($identifier, $item); ?>
                  </div>
                </div>
              <?php endif; ?>
            </li>

            <?php $count_sub_items++; ?>
          <?php endwhile; ?>
        </ul>
      <?php endif; ?>
    </li>
    */ ?>
  <?php endwhile; ?>
</ul>

<div id="sort-menu-buttons" class="position-fixed" style="bottom: 1rem; right: 1rem; display: none;">
  <div class="btn-group">
    <button id="btn-cancel-sort-menu" class="btn btn-secondary" type="button">
      <i class="fa fa-times me-1"></i> Cancelar
    </button>

    <button id="btn-save-sort-menu" class="btn btn-primary" type="button">
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
      $("#sort-menu-buttons").fadeIn();
    }
  });

  $("#btn-cancel-sort-menu").on("click", () => {
    showPageLoading();
    location.reload();
  });

  $("#btn-save-sort-menu").on("click", async () => {
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
      place: "menu",
      parameters: {
        action: "sort-menu-items",
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
</script>