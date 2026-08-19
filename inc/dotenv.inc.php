<?php
require_once __DIR__ . "/../vendor/dotenv/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

define("SUBDOMAIN", $_ENV["SUBDOMAIN"]);

define("DB_HOST", $_ENV["DB_HOST"]);
define("DB_NAME", $_ENV["DB_NAME"]);
define("DB_USER", $_ENV["DB_USER"]);
define("DB_PASSWORD", $_ENV["DB_PASSWORD"]);
define("DB_COLLATION", $_ENV["DB_COLLATION"]);
