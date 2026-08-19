<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action      = $_POST['action'];
$identifier  = 'usuarios';

$extensiones_permitidas = ['jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG'];
$carpeta_avatar = BASE_PATH . '/src/assets/images/usuarios/';

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');
    $session_user_id  = get_id_usuario();

    $per_page         = $_POST['perPage'];
    $page             = intval($_POST['page']) ?? 1;

    $search           = cleanStr($_POST['search']);
    $id_rol           = cleanStr($_POST['id_rol']);
    $id_sucursal      = cleanStr($_POST['id_sucursal']);

    $column_id        = "U.id_usuario";
    $c_from           = "{$db_ati}_usuarios AS U";
    $c_extra_clauses  = "ORDER BY U.id_usuario DESC";

    $fields = [
      "U.id_usuario",
      ["U.id_usuario", "uid"],
      "U.id_rol",
      "U.id_sucursal",
      "U.nombre_completo",
      "U.correo",
      "U.telefono",
      "U.username",
      "R.rol",
      ["R.slug", "rol_slug"],
      "S.nombre_sucursal",
      "U.mostrar_tarjeta",
      "U.slug",
      "U.avatar"
    ];

    $c_join     = "
        LEFT JOIN {$db_ati}_roles       AS R ON (U.id_rol       = R.id_rol)
        LEFT JOIN {$db_dti}_sucursales  AS S ON (U.id_sucursal  = S.id_sucursal)
      ";

    $c_where    = [
      ["U.id_usuario",  "$session_user_id", "!="],
      ["U.id_usuario",  "1",                "!="],
      ["U.id_rol",      "1",                "!="],
      ["U.username", "admin@windsoftti.com", "!="],
      ["U.status",      "activo"]
    ];

    if (!empty($search))      array_push($c_where, ["U.nombre_completo",  "%$search%", "LIKE"]);
    if (!empty($id_rol))      array_push($c_where, ["U.id_rol",           "$id_rol"]);
    if (!empty($id_sucursal)) array_push($c_where, ["U.id_sucursal",      "$id_sucursal"]);

    $request = useDataTable([
      'column_id'     => $column_id,
      'from'          => $c_from,
      'where'         => $c_where,
      'fields'        => $fields,
      'join'          => $c_join,
      'extra_clauses' => $c_extra_clauses,
      'per_page'      => $per_page,
      'page'          => $page
    ]);

    //echo getEmptyTableMessage($request);
    //die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';
    die;
    break;

  case 'add-' . $identifier:
    if (checkModuleActionPermission($identifier, 'agregar')) :
      if ($_POST['id_rol'] != 1) :
        $password = encrypt($_POST['password'], MYSQLI_PASSWORD_SECRET);

        $user_exists = checkIfExistsUserByEmailAndUsername(
          cleanStr($_POST['correo']),
          cleanStr($_POST['username'])
        );

        if ($user_exists) $response['toastMessage'] = $user_exists;

        if (!$user_exists) :

          $file = $_FILES['avatar'] ?? null;

          if (!empty($file['name'])) :
            $$extra_fields_add = ['password' => $password];

            $file_result = processFile($file, $extensiones_permitidas, $carpeta_avatar);

            if ($file_result === 'no-valid') :
              $response['toastMessage'] = '¡Archivo no permitido! Solo se permiten imágenes JPG y PNG.';
              break;
            endif;

            if ($file_result === 'no-move') :
              $response['toastMessage'] = '¡Error al subir el avatar!, inténtalo nuevamente.';
              break;
            endif;

            $extra_fields_add['avatar'] = $file_result;
          endif;

          $request = useInsertByPost([
            'table_name'      => "{$db_ati}_usuarios",
            'excluded_fields' => ['change_password', 'password', 'confirm_password'],
            'extra_fields'    => $extra_fields_add,
            'clean_priority'  => ['password' => 'none']
          ]);

          if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

          if ($request['status'] === 'success') $response = [
            'status'        => 'success',
            'toastMessage'  => '¡El usuario se agregó correctamente!',
            'callback'      => 'load("' . $page . '", "' . $identifier . '");'
          ];
        endif;
      endif;
    endif;
    break;

  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      if ($_POST['id_rol'] != 1) :
        $id_usuario       = cleanStr($_POST['uid']);
        $change_password  = $_POST['change_password'];
        $password         = encrypt($_POST['password'], MYSQLI_PASSWORD_SECRET);

        $user_exists      = checkIfExistsUserByEmailAndUsername(
          cleanStr($_POST['correo']),
          cleanStr($_POST['username']),
          $id_usuario
        );

        if ($user_exists) $response['toastMessage'] = $user_exists;

        if (!$user_exists) :
          $extra_fields = [];

          if ($change_password) $extra_fields = ['password' => $password];

          $file = $_FILES['avatar'] ?? null;

          if (!empty($file['name'])) :
            $file_result = processFile($file, $extensiones_permitidas, $carpeta_avatar);

            if ($file_result === 'no-valid') :
              $response['toastMessage'] = '¡Archivo no permitido! Solo se permiten imágenes JPG y PNG.';
              break;
            endif;

            if ($file_result === 'no-move') :
              $response['toastMessage'] = '¡Error al subir el avatar!, inténtalo nuevamente.';
              break;
            endif;

            // Eliminar file anterior si existe
            $query_file_actual = mysqli_query($mysqli, "SELECT avatar FROM {$db_ati}_usuarios WHERE id_usuario = {$id_usuario} LIMIT 1");
            $row_file          = mysqli_fetch_assoc($query_file_actual);

            if (!empty($row_file['avatar'])) :
              deleteFile($carpeta_avatar . $row_file['avatar']);
            endif;

            $extra_fields['avatar'] = $file_result;
          else :
            // No viene archivo, excluir el campo para no sobreescribir con vacío
            $excluded_fields_edit[] = 'avatar';
          endif;

          $request = useUpdateByPost([
            'table_name'      => "{$db_ati}_usuarios",
            'excluded_fields' => ['change_password', 'password', 'confirm_password', 'username'],
            'extra_fields'    => $extra_fields,
            'clean_priority'  => ['password' => 'none'],
            "conditions"      => [['id_usuario', $id_usuario]]
          ]);

          if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

          if ($request['status'] === 'success') $response = [
            'status'        => 'success',
            'toastMessage'  => '¡El usuario se actualizó correctamente!',
            'callback'      => 'load("' . $page . '", "' . $identifier . '");'
          ];
        endif;
      endif;
    endif;
    break;

  case 'action-eliminar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'eliminar')) :
      $id_usuario = cleanStr($_POST['uid']);

      #$query        = "DELETE FROM {$db_ati}_usuarios WHERE id_usuario = $id_usuario";
      $query = "UPDATE {$db_ati}_usuarios SET
          status = 'eliminado'
        WHERE
          id_usuario = {$id_usuario}
      ";

      $query_result = mysqli_query($mysqli, $query);

      if ($query_result) $response = [
        'status'        => 'success',
        'toastMessage'  => 'El usuario se eliminó correctamente'
      ];
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
