<?php
require '../lib/public-settings.inc.php';
require '../lib/php-mailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action = $_POST['action'];

switch ($action) {
  case 'login':
    $username = cleanStr($_POST['usernam']);
    $password = encrypt($_POST['password'], MYSQLI_PASSWORD_SECRET);

    $query = "SELECT id_usuario FROM {$db_ati}_usuarios WHERE
        username  = BINARY '{$username}' AND
        password  = BINARY '{$password}' AND
        status    = 'activo'
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if (!$num_rows) $response['toastMessage'] = '¡Tus datos de acceso son incorrectos!';

    if ($num_rows) :
      $user_data = mysqli_fetch_assoc($query_result);

      set_session_user_id($user_data['id_usuario']);

      $response = [
        'status'    => 'success',
        'callback'  => '{
          showPageLoading();
          location.reload();
        }'
      ];
    endif;
    break;

  case 'recuperar-credenciales':
    $correo = cleanStr($_POST['correo']);

    $query = "SELECT
        nombre_completo,
        correo,
        username,
        password
      FROM
        {$db_ati}_usuarios
      WHERE
        correo = '{$correo}'
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows == 0) $response['toastMessage'] = 'El correo ingresado no es válido';

    if ($num_rows > 0) :
      $data_usuario = mysqli_fetch_assoc($query_result);
      $password     = decrypt($data_usuario['password'], MYSQLI_PASSWORD_SECRET);

      $mensaje = "Hola {$data_usuario['nombre_completo']}, tus datos de acceso son:<br>";
      $mensaje .= "<b>Usuario:</b> {$data_usuario['username']}<br>";
      $mensaje .= "<b>Contraseña:</b> {$password}";

      ob_start();
      include '../lib/email-templates/default.php';
      $message = ob_get_clean();

      $config = [
        'mail'    => new PHPMailer(true),
        'from'  => [
          'name'      => ADM_NAME,
          'username'  => PHPMAILER_SUPPORT_EMAIL,
          'password'  => PHPMAILER_SUPPORT_PASSWORD
        ],
        'to'      => [[
          'name'  => $data_usuario['nombre_completo'],
          'email' => $data_usuario['correo']
        ]],
        'subject' => ADM_NAME . '| Datos de acceso',
        'message' => $message
      ];

      $request = sendEmail($config);

      if ($request['status'] === 'success') :
        $alert_message = "Le informamos que hemos enviado sus datos de acceso a la dirección de correo electrónico asociada a su cuenta. Por favor, revise su bandeja de entrada y también verifique la carpeta de correo no deseado si no encuentra nuestro mensaje en la bandeja de entrada principal.";

        $response = [
          'status'        => 'success',
          'title'         => 'Datos enviados',
          'alertMessage'  => $alert_message,
          'callback'      => 'location.href="' . BASE_URL . '/login"'
        ];
      endif;
    endif;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
exit;
