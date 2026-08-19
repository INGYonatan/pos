<?php
setlocale(LC_ALL, "es_MX");
date_default_timezone_set('America/Mexico_City');

$mysqli_host      = DB_HOST;
$mysqli_database  = DB_NAME;
$mysqli_user      = DB_USER;
$mysqli_password  = DB_PASSWORD;

$mysqli = new mysqli(
  $mysqli_host,
  $mysqli_user,
  $mysqli_password,
  $mysqli_database
);

if ($mysqli->connect_error) :
  $json = 'error';
  echo json_encode($json);
  die();
endif;

if (!$mysqli->connect_error) $mysqli->set_charset(DB_COLLATION);
