<?php
require 'inc/session.inc.php';

// 1. Obtener y limpiar la URL recibida
$url = $_GET['uid'];
$filename = basename($url);

// 2. Validación de extensiones permitidas
$allowedExtensions = ["pdf", "xml"];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
  closeSession();
  die("Extensión no permitida.");
}

/**
 * LÓGICA UNIVERSAL (DOCKER / XAMPP)
 * En lugar de hacer una petición HTTP a localhost (que falla en Docker),
 * convertimos la URL en una ruta de archivo física usando BASE_PATH.
 */

// Extraer la ruta relativa de la URL (ej: /src/assets/...)
$parsedUrlPath = parse_url($url, PHP_URL_PATH);

// Construir la ruta absoluta en el disco duro del servidor
// Aseguramos que no se dupliquen las diagonales entre BASE_PATH y la ruta
$full_path = rtrim(BASE_PATH, '/') . '/' . ltrim($parsedUrlPath, '/');

$contenido = false;

// Intentar leer el archivo directamente del disco (Más rápido y seguro en Docker)
if (file_exists($full_path) && is_readable($full_path)) {
  $contenido = file_get_contents($full_path);
} else {
  // Si por alguna razón el archivo no se encuentra físicamente, intentamos CURL como respaldo
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Tiempo límite para no trabar el servidor

  $contenido = curl_exec($ch);

  if (curl_errno($ch)) {
    error_log("Error CURL: " . curl_error($ch));
  }
  curl_close($ch);
}

// 3. Verificar si logramos obtener el contenido
if (!$contenido) {
  echo "Error: No se pudo obtener el archivo";
  exit;
}

// 4. Limpiar buffers de salida para evitar caracteres extra en el archivo
while (ob_get_level()) {
  ob_end_clean();
}

// 5. Establecer cabeceras HTTP para la descarga
// Usamos el archivo físico para detectar el MIME de forma más precisa
if (file_exists($full_path)) {
  header('Content-Type: ' . mime_content_type($full_path));
} else {
  // Fallback si es PDF o XML
  $mime = ($ext == 'pdf') ? 'application/pdf' : 'application/xml';
  header('Content-Type: ' . $mime);
}

header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($contenido));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// 6. Enviar el contenido del archivo
echo $contenido;
exit;
