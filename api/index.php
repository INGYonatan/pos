<?php
require_once __DIR__ . "/config/settings.php";
require_once __DIR__ . "/config/functions.php";
require_once __DIR__ . "/config/access.api.controller.php";

class ApiAPP
{
  public function __construct()
  {
    $url = $_GET["url"] ?? "";
    $url = rtrim($url, "/");

    $segments = explode("/", $url);
    $method = array_pop($segments) ?: "index"; // método por defecto: index

    // El método en la url puede venir con guiones, poner en camelCase
    $method = lcfirst(str_replace('-', '', ucwords($method, '-')));

    $fileBase = implode("-", $segments);
    $fileName = "{$fileBase}.api.php";

    $fileExists = file_exists(__DIR__ . "/{$fileName}");

    error_log("method: {$method}");

    if (!$fileExists) {
      header("HTTP/1.0 404 Not Found");
      die;
    }

    require_once __DIR__ . "/{$fileName}";

    $className = $this->fileToClassName($fileName);

    $api = new $className();

    if (!method_exists($api, $method)) {
      header("HTTP/1.0 404 Not Found");
      die;
    }

    $api->$method();
  }

  public function fileToClassName($apiFile)
  {
    // Remove .api.php, split by '-', capitalize each part, and append 'Api'
    $base = str_replace('.api.php', '', $apiFile);
    $parts = explode('-', $base);
    $className = '';
    foreach ($parts as $part) {
      $className .= ucfirst($part);
    }
    $className .= 'Api';
    return $className;
  }
}

$apiApp = new ApiAPP();
