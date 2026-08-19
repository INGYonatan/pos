<?php
require_once __DIR__ . "/../inc/settings.inc.php";

/**
 * Script de Importación de Números de Serie desde CSV
 * 
 * Este script lee un archivo CSV con columnas: sku, numero_serie, sucursal
 * y realiza la importación a la tabla paal_producto_numeros_serie.
 * 
 * Uso:
 *   php import_numeros_serie.php [ruta/al/archivo.csv] [--dry-run]
 * 
 * Ejemplo:
 *   php import_numeros_serie.php SAE_numeros_serie_sucursales.csv
 */

// ========================== CONFIGURACIÓN ==========================
class Config
{
  const DB_HOST = DB_HOST;
  const DB_NAME = DB_NAME;
  const DB_USER = DB_USER;
  const DB_PASS = DB_PASSWORD;
  const DB_CHARSET = DB_COLLATION;
  const COMMIT_EVERY = 1000;
  const DEFAULT_CSV_FILE = 'SAE_numeros_serie_sucursales.csv';
}

// ========================== IMPORTADOR ==========================
class ImportadorNumerosSerie
{
  private $pdo;
  private $dryRun;
  private $stats;

  private $stmtGetProduct;
  private $stmtGetSucursal;
  private $stmtCheckExist;
  private $stmtInsert;

  public function __construct($dryRun = false)
  {
    $this->dryRun = $dryRun;
    $this->stats = [
      'procesadas'   => 0,
      'insertadas'   => 0,
      'sinProducto'  => 0,
      'sinSucursal'  => 0,
      'duplicadas'   => 0,
      'invalidas'    => 0,
      'errores'      => 0
    ];
  }

  public function conectarDB()
  {
    $dsn = sprintf(
      "mysql:host=%s;dbname=%s;charset=%s",
      Config::DB_HOST,
      Config::DB_NAME,
      Config::DB_CHARSET
    );
    $opts = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    $this->pdo = new PDO($dsn, Config::DB_USER, Config::DB_PASS, $opts);
  }

  private function prepararConsultas()
  {
    $this->stmtGetProduct = $this->pdo->prepare("
            SELECT id_producto FROM paal_productos 
            WHERE codigo = :codigo AND status = 'activo' LIMIT 1
        ");
    $this->stmtGetSucursal = $this->pdo->prepare("
            SELECT id_sucursal FROM paal_sucursales 
            WHERE numero_serie = :numero_serie AND status = 'activo' LIMIT 1
        ");
    $this->stmtCheckExist = $this->pdo->prepare("
            SELECT id_producto_numero_serie FROM paal_producto_numeros_serie
            WHERE id_producto = :id_producto AND id_sucursal = :id_sucursal AND numero_serie = :numero_serie LIMIT 1
        ");
    $this->stmtInsert = $this->pdo->prepare("
            INSERT INTO paal_producto_numeros_serie
            (id_producto, folio_compra, folio_venta, numero_serie, status, fecha_creacion, id_sucursal)
            VALUES (:id_producto, NULL, NULL, :numero_serie, 'disponible', NOW(), :id_sucursal)
        ");
  }

  public function procesarCSV($csvFile)
  {
    if (!file_exists($csvFile)) {
      throw new Exception("Archivo CSV no encontrado: $csvFile");
    }
    $handle = fopen($csvFile, 'r');
    if (!$handle) {
      throw new Exception("No se pudo abrir el archivo CSV");
    }
    $this->prepararConsultas();
    $lineNumber = 0;
    $pendingInserts = 0;
    $this->pdo->beginTransaction();
    while (($row = fgetcsv($handle)) !== false) {
      $lineNumber++;
      if (count($row) === 0) continue;
      if ($lineNumber === 1) {
        if ($this->esEncabezado($row)) continue; // Salta encabezado
      }
      $this->stats['procesadas']++;
      if (!$this->procesarFila($row, $lineNumber, $pendingInserts)) {
        continue;
      }
      $pendingInserts++;
      if ($pendingInserts >= Config::COMMIT_EVERY) {
        $this->pdo->commit();
        $this->pdo->beginTransaction();
        $pendingInserts = 0;
      }
    }
    if ($this->pdo->inTransaction()) $this->pdo->commit();
    fclose($handle);
    $this->mostrarResumen();
  }

  private function procesarFila($row, $lineNumber, $pendingInserts)
  {
    if (count($row) < 3) {
      $this->stats['invalidas']++;
      return false;
    }
    $sku = trim($row[0]);
    $numeroSerie = trim($row[1]);
    $sucursal = trim($row[2]);
    if ($sku === '' || $numeroSerie === '' || $sucursal === '') {
      $this->stats['invalidas']++;
      return false;
    }
    $idProducto = $this->buscarProducto($sku);
    if (!$idProducto) {
      $this->stats['sinProducto']++;
      return false;
    }
    $idSucursal = $this->buscarSucursal($sucursal);
    if (!$idSucursal) {
      $this->stats['sinSucursal']++;
      return false;
    }
    if ($this->existeRegistro($idProducto, $idSucursal, $numeroSerie)) {
      $this->stats['duplicadas']++;
      return false;
    }
    if (!$this->dryRun) {
      try {
        $this->stmtInsert->execute([
          ':id_producto' => $idProducto,
          ':numero_serie' => $numeroSerie,
          ':id_sucursal' => $idSucursal
        ]);
      } catch (PDOException $e) {
        $this->stats['errores']++;
        return false;
      }
    }
    $this->stats['insertadas']++;
    return true;
  }

  private function buscarProducto($codigo)
  {
    $this->stmtGetProduct->execute([':codigo' => $codigo]);
    $r = $this->stmtGetProduct->fetch();
    return $r ? $r['id_producto'] : null;
  }
  private function buscarSucursal($numeroSerie)
  {
    $this->stmtGetSucursal->execute([':numero_serie' => $numeroSerie]);
    $r = $this->stmtGetSucursal->fetch();
    return $r ? $r['id_sucursal'] : null;
  }
  private function existeRegistro($idProducto, $idSucursal, $numeroSerie)
  {
    $this->stmtCheckExist->execute([
      ':id_producto' => $idProducto,
      ':id_sucursal' => $idSucursal,
      ':numero_serie' => $numeroSerie
    ]);
    return $this->stmtCheckExist->fetch() !== false;
  }
  private function esEncabezado($row)
  {
    $first = strtolower(trim($row[0]));
    return in_array($first, ['sku', 'codigo', 'producto']);
  }
  private function mostrarResumen()
  {
    echo "\n========================================\n";
    echo "RESUMEN DE IMPORTACIÓN\n";
    echo "========================================\n";
    echo "Líneas procesadas:           {$this->stats['procesadas']}\n";
    echo "Registros insertados:        {$this->stats['insertadas']}\n";
    echo "Omitidos (sin producto):     {$this->stats['sinProducto']}\n";
    echo "Omitidos (sin sucursal):     {$this->stats['sinSucursal']}\n";
    echo "Omitidos (duplicados):       {$this->stats['duplicadas']}\n";
    echo "Omitidos (datos inválidos):  {$this->stats['invalidas']}\n";
    echo "Errores:                     {$this->stats['errores']}\n";
    echo "========================================\n";
    if ($this->dryRun) {
      echo "\n*** MODO DRY-RUN: No se realizaron cambios en la base de datos ***\n";
    }
  }
}

// ========================== MAIN/EJECUCIÓN ==========================
function mostrarAyuda()
{
  echo "\n";
  echo "Script de Importación de Números de Serie\n";
  echo "==========================================\n\n";
  echo "Uso:\n";
  echo "  php import_numeros_serie.php [archivo.csv] [--dry-run]\n\n";
  echo "Opciones:\n";
  echo "  --dry-run    Ejecuta sin realizar cambios en la base de datos\n";
  echo "  --help       Muestra esta ayuda\n\n";
  echo "Ejemplo:\n";
  echo "  php import_numeros_serie.php datos.csv\n";
  echo "  php import_numeros_serie.php datos.csv --dry-run\n\n";
}

$csvFile = null;
$dryRun = false;
foreach ($argv as $idx => $arg) {
  if ($idx === 0) continue;
  elseif ($arg === '--help' || $arg === '-h') {
    mostrarAyuda();
    exit(0);
  } elseif ($arg === '--dry-run') {
    $dryRun = true;
  } elseif (strpos($arg, '--') !== 0) {
    $csvFile = $arg;
  }
}
if ($csvFile === null) $csvFile = Config::DEFAULT_CSV_FILE;
try {
  echo "\nIMPORTADOR DE NÚMEROS DE SERIE\n==========================================\n";
  $importador = new ImportadorNumerosSerie($dryRun);
  $importador->conectarDB();
  $importador->procesarCSV($csvFile);
  echo "\n✓ Importación completada exitosamente.\n\n";
  exit(0);
} catch (Exception $e) {
  echo "\n✗ ERROR: " . $e->getMessage() . "\n\n";
  exit(1);
}
