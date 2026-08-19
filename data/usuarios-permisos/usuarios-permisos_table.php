<ul class="list-group">
  <?php while ($row = mysqli_fetch_array($request['query_result'])) :
    $colaboratorPermissions = getUserPermissionModuleActionIds($userId);
  ?>
    <li id="module-<?= $row['id_modulo']; ?>" data-uid="<?= $row["id_modulo"]; ?>" class="list-group-item d-flex align-items-center gap-2 py-3">
      <div>
        <i class="me-1 fa fa-shield-alt"></i>
      </div>

      <div>
        <h3 class="header-title d-flex align-items-center gap-2">
          <?= $row["modulo"]; ?>
        </h3>

        <?= getAvailableModuleActionsOfAdminBotsRoleSwitches($row["id_modulo"], $userData["id_rol"], $colaboratorPermissions); ?>
      </div>
    </li>
  <?php endwhile; ?>
</ul>

<!-- <?= paginate($page, $request['num_pages'], 1, 'load'); ?> -->

<script>
  $(".switch-permission").on("click", function() {
    const isChecked = $(this).is(":checked");
    const moduleActionId = $(this).val();
    const moduleId = $(this).closest("li").attr("data-uid");
    const parentAll = $(this).attr("data-parentAll");

    if (isChecked) addPermission(moduleId, moduleActionId, parentAll);
    if (!isChecked) removePermission(moduleId, moduleActionId, parentAll);
  });
</script>