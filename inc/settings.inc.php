<?php
session_start();

const DECIMALS_CURRENCY = 6;
const DECIMALS_CURRENCY_STEP = 0.000001;

require_once __DIR__ . "/dotenv.inc.php";

error_reporting(E_ERROR); // Solo errores fatales, no warnings
ini_set("display_errors", 0); // Muestra errores fatales en pantalla
ini_set("display_startup_errors", 1);

ini_set("ignore_repeated_errors", TRUE);
ini_set("log_errors", TRUE); // Registra errores en el log
ini_set("error_log", __DIR__ . "/../logs/php-error.log");

require 'tb_constants.inc.php';

$db_ati = ATI;
$db_dti = DTI;
$crd_id = CRDI;

require 'config.inc.php';
require 'global-functions.inc.php';
require 'constants.inc.php';
require 'administrator-functions.inc.php';
require 'specific-functions.inc.php';

$admp_session_user_data = getUserData(get_id_usuario());
$page_config            = [];
