<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente',
  'action' => $_POST['action']
];

$action                       = $_POST['action'];
$identifier                   = 'mi-cuenta';
$usuario_llanteras_identifier = 'usuario-llanteras';
$extensiones_permitidas = array('jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG');
$carpeta_imagenes       = '../../src/assets/images/llanteras/';

switch ($action) {
  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_usuario       = get_id_usuario();
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

        $request = useUpdateByPost([
          'table_name'      => "{$db_ati}_usuarios",
          'excluded_fields' => ['change_password', 'password', 'confirm_password', 'username', 'id_rol'],
          'extra_fields'    => $extra_fields,
          'clean_priority'  => ['password' => 'none'],
          "conditions"      => [['id_usuario', $id_usuario]]
        ]);

        if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

        if ($request['status'] === 'success') $response = [
          'status'        => 'success',
          'title'         => '¡Datos guardados!',
          'alertMessage'  => '¡Tus datos se actualizaron correctamente!',
          'callback'      => 'showPageLoading;location.reload();'
        ];
      endif;
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
