<?php
require_once __DIR__ . "/inc/session.inc.php";
require_once __DIR__ . "/data/lib/php-mailer/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = "Mensaje de prueba";

$config = [
  'mail'  => new PHPMailer(),
  'from'  => [
    'name'      => ADM_NAME,
    'username'  => PHPMAILER_SALES_EMAIL,
    'password'  => PHPMAILER_SALES_PASSWORD
  ],
  'to'      => [[
    'name'  => "Yonatan Salazar López",
    'email' => "yonatan021297@gmail.com"
  ]],
  'subject' => ADM_NAME . '| Datos de acceso',
  'message' => $message
];

$request = sendEmail($config);

echo json_encode($request);
