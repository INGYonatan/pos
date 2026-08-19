<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action      = $_POST['action'];
$identifier  = 'menu';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions = haveActions($identifier, 'tabla');

    $initial_query = "SELECT
        id,
        id AS uid,
        titulo,
        id_modulo,
        icono,
        orden,
        pertenece_a,
        _blank
      FROM {$db_ati}_menu
    ";

    $query = $initial_query . "
      WHERE pertenece_a = 0
      ORDER BY orden
      ASC
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows === 0) echo getEmptyTableMessage();
    if ($num_rows > 0) include $identifier . '_table.php';

    die;
    break;

  case 'add-' . $identifier:
    if (checkModuleActionPermission($identifier, 'agregar')) :
      $request = useInsertByPost([
        'table_name' => "{$db_ati}_menu"
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'title'         => '¡Datos guardados!',
        'alertMessage'  => '¡El item del menú se agregó correctamente!',
        'callback'      => 'location.reload();'
      ];
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id = cleanStr($_POST['uid']);

      $request = useUpdateByPost([
        'table_name'      => "{$db_ati}_menu",
        "conditions"      => [['id', $id]]
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'        => 'success',
        'title'         => '¡Datos guardados!',
        'alertMessage'  => '¡El item del menú se actualizó correctamente!',
        'callback'      => 'location.reload();'
      ];
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id = cleanStr($_POST['uid']);

      $query        = "DELETE FROM {$db_ati}_menu WHERE id = $id";
      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El item se eliminó correctamente'
      ];
    endif;
    break;

  case "sort-menu-items":
    if (!checkModuleActionPermission($identifier, 'editar')) break;

    try {
      $items = $_POST["items"];
      $items = json_decode($items, true);

      foreach ($items as $item) {
        $id       = $item["id"];
        $order    = $item["order"];
        $parentId = $item["parentId"] ?? null;

        $query = "UPDATE {$db_ati}_menu SET
            orden       = {$order},
            pertenece_a = '{$parentId}'
          WHERE
            id = {$id}
        ";

        mysqli_query($mysqli, $query);
      }

      $response = [
        'status'        => 'success',
        'toastMessage'  => 'El orden del menú se actualizó correctamente'
      ];
    } catch (Exception $e) {
      error_log("ERROR_SORT_MENU_ITEMS: " . $e->getMessage());
    }
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
