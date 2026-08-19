<?php
require_once __DIR__ . "/syspos/inc/settings.inc.php";

$userSlug = cleanStr($_GET["slug"]);
$download = cleanStr($_GET["download"] ?? "");

// Current url sin variables GET para compartir
$currentURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$currentURL = strtok($currentURL, '?'); // Eliminar cualquier variable GET para compartir solo la URL base de la tarjeta digital
$downloadURL = "{$currentURL}/download";

if ($download) {
  $filename = basename($download);

  // 2. Validación de extensiones permitidas
  $allowedExtensions = ["pdf"];
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

  if (!in_array($ext, $allowedExtensions)) {
    header("location:https://cocinaspaal.com/tarjeta/digital/{$userSlug}");
    die;
  }

  /**
   * LÓGICA UNIVERSAL (DOCKER / XAMPP)
   * En lugar de hacer una petición HTTP a localhost (que falla en Docker),
   * convertimos la URL en una ruta de archivo física usando BASE_PATH.
   */

  // Extraer la ruta relativa de la URL (ej: /src/assets/...)
  $parsedUrlPath = parse_url($download, PHP_URL_PATH);

  // Construir la ruta absoluta en el disco duro del servidor
  // Aseguramos que no se dupliquen las diagonales entre BASE_PATH y la ruta
  $full_path = rtrim(BASE_PATH, '/') . '/' . ltrim($parsedUrlPath, '/');

  $contenido = false;

  // Intentar leer el archivo directamente del disco (Más rápido y seguro en Docker)
  if (file_exists($full_path) && is_readable($full_path)) {
    $contenido = file_get_contents($full_path);
  } else {
    // Si por alguna razón el archivo no se encuentra físicamente, intentamos CURL como respaldo
    $ch = curl_init($download);
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
    header("location:https://cocinaspaal.com/tarjeta/digital/{$userSlug}");
    die;
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
    // Fallback si es PDF
    $mime = ($ext == 'pdf') ? 'application/pdf' : 'application/octet-stream';
    header('Content-Type: ' . $mime);
  }

  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Content-Length: ' . strlen($contenido));
  header('Cache-Control: private, max-age=0, must-revalidate');
  header('Pragma: public');

  // 6. Enviar el contenido del archivo
  echo $contenido;
  exit;
}

$query  = "SELECT * FROM adm_usuarios WHERE slug = ? AND mostrar_tarjeta = 'si' LIMIT 1";
$stmt   = $mysqli->prepare($query);

$stmt->bind_param("s", $userSlug);
$stmt->execute();

$result   = $stmt->get_result();
$numRows  = $result->num_rows;

if ($numRows == 0) {
  echo "not found - 404";
  die;
}

$userInfo = $result->fetch_assoc();
$userId   = $userInfo['id_usuario'];

// Obtener los archivos del usuario
$query  = "SELECT * FROM paal_usuario_archivos WHERE id_usuario = ? AND status = 'activo'";
$stmt   = $mysqli->prepare($query);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result   = $stmt->get_result();
$numRows  = $result->num_rows;

$files = [];

if ($numRows > 0) {
  while ($row = $result->fetch_assoc()) {
    $files[] = $row;
  }
}

// Obtener los datos de la sucursal del usuario
$query = "SELECT * FROM paal_sucursales WHERE id_sucursal = ?";
$stmt   = $mysqli->prepare($query);

$stmt->bind_param("i", $userInfo["id_sucursal"]);
$stmt->execute();

$result     = $stmt->get_result();
$branchInfo = $result->fetch_assoc();


$logo_url = "https://cocinaspaal.com/tarjeta-digital-logo.png";

if ($userInfo["avatar"]) $logo_url = BASE_URL . "/src/assets/images/usuarios/{$userInfo["avatar"]}";

$branchLogo = $branchInfo["logo"] ? BASE_URL . "/src/assets/images/sucursales/{$branchInfo["logo"]}" : null;
?>
<!DOCTYPE html>

<html class="" lang="es">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&amp;display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />

  <link rel="apple-touch-icon" sizes="180x180" href="<?= $logo_url; ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= $logo_url; ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= $logo_url; ?>">
  <link rel="mask-icon" href="<?= $logo_url; ?>" color="#ff9900">
  <meta name="msapplication-TileColor" content="#ff9900">
  <meta name="theme-color" content="#ff9900">

  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#ff9900",
            "background-light": "#f8f7f5",
            "background-dark": "#231b0f",
          },
          fontFamily: {
            "display": ["Manrope", "sans-serif"]
          },
          borderRadius: {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
        },
      },
    }
  </script>
  <style>
    body {
      font-family: 'Manrope', sans-serif;
    }

    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
  </style>
</head>

<body class="bg-background-light text-slate-900 min-h-screen">
  <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
    <div class="layout-container flex h-full grow flex-col">
      <!-- Main Content Wrapper -->
      <div class="flex flex-1 justify-center sm:px-10 md:px-40">
        <div class="layout-content-container flex flex-col max-w-[480px] w-full flex-1 bg-white dark:bg-background-dark shadow-2xl sm:rounded-xl overflow-hidden">
          <!-- Header/Top Profile Section -->
          <div style="background-image: url(<?= $branchLogo; ?>); background-position: center; object-fit: cover; background-size: cover;">
            <div style="<?= $branchLogo ? "background-color: rgba(0,0,0,0.5);" : ""; ?>">
              <header class="relative flex flex-col items-center pt-12 pb-8 px-6 bg-gradient-to-b from-primary/20 to-transparent dark:from-primary/10">
                <div class="absolute top-4 right-4 flex gap-2">
                  <button class="flex items-center justify-center rounded-full w-10 h-10 bg-slate-100 text-slate-600 hover:bg-primary hover:text-white transition-colors" onclick="compartirPerfil()">
                    <span class="material-symbols-outlined">share</span>
                  </button>
                  <!-- <button class="flex items-center justify-center rounded-full w-10 h-10 bg-slate-100 text-slate-600 hover:bg-primary hover:text-white transition-colors" onclick="descargarVCard()">
                <span class="material-symbols-outlined">person_add</span>
              </button> -->
                  <a class="flex items-center justify-center rounded-full w-10 h-10 bg-slate-100 text-slate-600 hover:bg-primary hover:text-white transition-colors" href="<?= $downloadURL; ?>" target="_blank">
                    <span class="material-symbols-outlined">person_add</span>
                  </a>
                </div>
                <div class="relative group">
                  <div class="absolute -inset-1 bg-gradient-to-r from-primary to-orange-600 rounded-full blur opacity-20 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                  <div class="relative size-32 rounded-full border-4 border-white bg-center bg-no-repeat bg-cover overflow-hidden" data-alt="Professional portrait of a male sales agent in a suit" style='background-image: url("<?= $logo_url; ?>");'>
                  </div>
                </div>
                <div class="mt-6 text-center">
                  <h1 class="text-2xl font-extrabold tracking-tight text-<?= $branchLogo ? "slate-100" : "slate-900"; ?>"><?= $userInfo["nombre_completo"]; ?></h1>
                  <p class="text-primary font-bold text-sm uppercase tracking-widest mt-1">Asesor de Ventas</p>

                  <p class="text-<?= $branchLogo ? "slate-300" : "slate-600"; ?> text-sm">
                    <b><?= $branchInfo["nombre_comercial"]; ?></b>
                  </p>

                  <p class="text-<?= $branchLogo ? "slate-300" : "slate-600"; ?> text-sm">
                    <span class="material-symbols-outlined text-sm">location_on</span>
                    <span><?= $branchInfo["direccion"]; ?></span>
                  </p>
                </div>
              </header>
            </div>
          </div>
          <!-- Contact Actions Grid -->
          <div class="px-6 py-4">
            <div class="w-100 gap-4" style="flex-direction: row; display: flex;">
              <a class="flex flex-col items-center gap-2 group" href="https://wa.me/521<?= $userInfo["telefono"]; ?>" target="_blank" style="flex: 1;">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                  <span class="material-symbols-outlined text-2xl">chat</span>
                </div>
                <span class="text-xs font-semibold text-slate-700">WhatsApp</span>
              </a>

              <a class="flex flex-col items-center gap-2 group" href="tel:<?= $userInfo["telefono"]; ?>" target="_blank" style="flex: 1;">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                  <span class="material-symbols-outlined text-2xl">call</span>
                </div>
                <span class="text-xs font-semibold text-slate-700">Llamar</span>
              </a>

              <a class="flex flex-col items-center gap-2 group" href="mailto:<?= $userInfo["correo"]; ?>" target="_blank" style="flex: 1;">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                  <span class="material-symbols-outlined text-2xl">mail</span>
                </div>
                <span class="text-xs font-semibold text-slate-700">Correo</span>
              </a>
              <!-- <a class="flex flex-col items-center gap-2 group" href="#">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-sm">
                  <span class="material-symbols-outlined text-2xl">groups</span>
                </div>
                <span class="text-xs font-semibold text-slate-700">LinkedIn</span>
              </a> -->
            </div>
          </div>
          <!-- Company Logo/Branding Divider -->
          <!-- <div class="px-6 py-6">
            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 rounded-xl border border-slate-200">
              <div class="size-8 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">restaurant</span>
              </div>
              <div class="flex-1">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">CocinasPAAL</h2>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">Equipamiento para profesionales</p>
              </div>
              <span class="material-symbols-outlined text-slate-300 dark:text-slate-600">verified</span>
            </div>
          </div> -->
          <!-- Product Catalogs Section -->

          <div class="px-6 pb-12">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">Catálogos de Productos</h3>
              <!-- <span class="text-primary text-xs font-semibold px-2 py-1 bg-primary/10 rounded uppercase">2024</span> -->
            </div>
            <div class="space-y-3"><!-- Catalog Item 1 -->
              <?php foreach ($files as $file) :
                $fileName       = $file["nombre"];
                $fileExtension  = pathinfo($file["slug"], PATHINFO_EXTENSION);
                $fileUrl        = BASE_URL . "/src/assets/userfiles/{$file["slug"]}";
                $fileSize       = 0;

                // calcular el tamaño del archivo en MB
                $filePath = BASE_PATH . "/src/assets/userfiles/{$file["slug"]}";

                if (file_exists($filePath)) {
                  $fileSize = filesize($filePath) / (1024 * 1024);
                  $fileSize = number_format($fileSize, 1); // Formatear a 1 decimal
                }
              ?>
                <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-xl border border-slate-200 hover:border-primary/50 hover:bg-white transition-all cursor-pointer group shadow-sm">
                  <div class="flex items-center justify-center rounded-lg bg-primary/10 text-primary shrink-0 size-12 group-hover:bg-primary group-hover:text-white transition-all">
                    <span class="material-symbols-outlined">menu_book</span>
                  </div>

                  <div class="flex flex-col justify-center flex-1">
                    <p class="text-slate-900 text-sm font-bold line-clamp-1"><?= $fileName; ?></p>
                    <p class="text-slate-500 text-xs font-medium"><?= strtoupper($fileExtension); ?> • <?= $fileSize; ?> MB</p>
                  </div>

                  <div class="shrink-0">
                    <a href="<?= $fileUrl; ?>" class="flex items-center justify-center rounded-full size-8 bg-white text-slate-600 border border-slate-200 hover:bg-primary hover:text-white hover:border-primary transition-colors" download target="_blank">
                      <span class="material-symbols-outlined text-sm">download</span>
                    </a>

                    <!-- <a href="<?= $currentURL; ?>?slug=<?= $userSlug; ?>&download=<?= urlencode($fileUrl); ?>" class="flex items-center justify-center rounded-full size-8 bg-white text-slate-600 border border-slate-200 hover:bg-primary hover:text-white hover:border-primary transition-colors">
                      <span class="material-symbols-outlined text-sm">download</span>
                    </a> -->
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <!-- Footer -->
          <footer class="mt-auto py-6 text-center border-t border-slate-100">
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-medium"><a target="_blank" href="https://www.cocinaspaal.com">www.cocinaspaal.com</a></p>
            <div class="mt-3 flex justify-center gap-4 opacity-60 grayscale hover:grayscale-0 hover:opacity-100 transition-all" style="display: flex; align-items: center;">
              <a class="text-lg text-slate-700" target="_blank" href="https://www.facebook.com/cocinasPAAL">
                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="23" viewBox="0 0 24 24" width="24">
                  <rect fill="none" height="24" width="24" />
                  <path d="M22,12c0-5.52-4.48-10-10-10S2,6.48,2,12c0,4.84,3.44,8.87,8,9.8V15H8v-3h2V9.5C10,7.57,11.57,6,13.5,6H16v3h-2 c-0.55,0-1,0.45-1,1v2h3v3h-3v6.95C18.05,21.45,22,17.19,22,12z" />
                </svg>
              </a>

              <a class="text-lg text-slate-700" target="_blank" href="https://www.instagram.com/cocinaspaal">
                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="19" viewBox="0 0 24 24" width="24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                </svg>
              </a>

              <a class="text-lg text-slate-700" target="_blank" href="https://www.youtube.com/channel/UCa4rb-wRzeDQnKbxFTkZeJQ">
                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24" viewBox="0 0 24 24" width="24">
                  <path d="M21.583 6.643C21.36 5.827 20.724 5.19 19.909 4.966C18.414 4.562 12.001 4.562 12.001 4.562C12.001 4.562 5.587 4.562 4.093 4.966C3.277 5.19 2.642 5.827 2.418 6.643C2.016 8.139 2.016 11.275 2.016 11.275C2.016 11.275 2.016 14.411 2.418 15.907C2.642 16.723 3.277 17.36 4.093 17.583C5.587 17.989 12.001 17.989 12.001 17.989C12.001 17.989 18.414 17.989 19.909 17.583C20.724 17.36 21.36 16.723 21.583 15.907C21.986 14.411 21.986 11.275 21.986 11.275C21.986 11.275 21.986 8.139 21.583 6.643Z" fill="#FF0000" />
                  <path d="M9.75 14.062V8.488L14.881 11.275L9.75 14.062Z" fill="#FFFFFF" />
                </svg>
              </a>
            </div>
          </footer>
        </div>
      </div>
    </div>
  </div>

  <script>
    function descargarVCard() {
      // Los datos se inyectan directamente desde tu backend PHP
      const vcard = `BEGIN:VCARD
          VERSION:3.0
          N:;<?= $userInfo["nombre_completo"]; ?>;;;
          FN:<?= $userInfo["nombre_completo"]; ?>\n
          ORG:CocinasPAAL
          TITLE:Asesor de Ventas
          TEL;TYPE=CELL,VOICE:<?= $userInfo["telefono"]; ?>\n
          EMAIL;TYPE=PREF,INTERNET:<?= $userInfo["correo"]; ?>\n
          ADR;TYPE=WORK:;;<?= $branchInfo["direccion"]; ?>
        END:VCARD
      `;

      // Crear el archivo en memoria con codificación UTF-8
      const blob = new Blob([vcard], {
        type: 'text/vcard;charset=utf-8;'
      });

      const url = URL.createObjectURL(blob);

      // Crear enlace de descarga automático
      const link = document.createElement('a');
      link.href = url;

      // El archivo se descargará con el nombre del asesor
      link.download = '<?= str_replace(" ", "_", $userInfo["nombre_completo"]); ?>.vcf';

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    }

    async function compartirPerfil(event) {
      if (event) event.preventDefault();

      // const enlaceTarjeta = window.location.href;
      const enlaceTarjeta = `<?= $currentURL; ?>`;

      //alert(enlaceTarjeta); // Solo para verificar que la URL se genera correctamente antes de compartir

      // Mensaje personalizado con los datos dinámicos de tu PHP
      const mensajeTexto = 'Te comparto el contacto de <?= $userInfo["nombre_completo"]; ?>, Asesor de Ventas en CocinasPAAL:\n\n' + enlaceTarjeta;

      // 1. Intento Nativo: Funcionará en Android y en iPhone (Con HTTPS activo)
      if (navigator.share) {
        try {
          await navigator.share({
            // Pasamos el texto y el link juntos en el campo 'text'. 
            // Es la forma más compatible para que Safari y Android no fallen.
            text: mensajeTexto
          });
          return;
        } catch (error) {
          if (error.name === 'AbortError') return;
        }
      }

      // 2. Plan B Exclusivo para Safari/iOS local o fallas
      if (navigator.clipboard && navigator.clipboard.writeText) {
        try {
          await navigator.clipboard.writeText(enlaceTarjeta);
          mostrarAvisoWeb('¡Enlace de tarjeta copiado!');
          return;
        } catch (_) {}
      }

      // 3. Plan C: Respaldo manual extremo
      const input = document.createElement('input');
      input.value = enlaceTarjeta;
      input.style.position = 'absolute';
      input.style.left = '-9999px';
      document.body.appendChild(input);
      input.select();
      input.setSelectionRange(0, 99999);
      document.execCommand('copy');
      document.body.removeChild(input);
      mostrarAvisoWeb('¡Enlace copiado!');
    }

    // Mensaje flotante elegante (Se mantiene igual)
    function mostrarAvisoWeb(mensaje) {
      const aviso = document.createElement('div');
      aviso.textContent = mensaje;
      aviso.style.cssText = 'position:fixed; bottom:80px; left:50%; transform:translateX(-50%); background:#0f172a; color:#ffffff; padding:12px 24px; border-radius:12px; font-size:14px; font-weight:bold; z-index:99999; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:opacity 0.3s;';
      document.body.appendChild(aviso);
      setTimeout(() => {
        aviso.style.opacity = '0';
        setTimeout(() => aviso.remove(), 300);
      }, 2000);
    }
  </script>
</body>

</html>