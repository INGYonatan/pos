<?php
require_once __DIR__ . '/settings.inc.php';

if (!$admp_session_user_data) :
  // Verificar si se está navegando en un navegador o se está haciendo una petición desde fuera como api, no tengo funciones
  $httpSecFetchMode = $_SERVER["HTTP_SEC_FETCH_MODE"] ?? null;

  if ($httpSecFetchMode == 'navigate') {
    header('location:' . BASE_URL . '/login');
    die;
  }

  if ($httpSecFetchMode != 'navigate') :
    $response = [
      'status'        => 'error',
      'toastMessage'  => '¡Error inesperado, intentalo nuevamente!.'
    ];

    echo json_encode($response);
    die;
  endif;
endif;
