<?php

/**
 * Script para actualizar number_format, round y format_decimal_number
 * Reemplaza los valores hardcodeados (2 y 4) por la constante DECIMALS_CURRENCY
 * 
 * Uso: php update-decimals-to-constant.php
 */

// Colores para terminal
class Colors
{
  public static $RESET = "\033[0m";
  public static $RED = "\033[31m";
  public static $GREEN = "\033[32m";
  public static $YELLOW = "\033[33m";
  public static $BLUE = "\033[34m";
  public static $MAGENTA = "\033[35m";
  public static $CYAN = "\033[36m";
  public static $WHITE = "\033[37m";
  public static $BOLD = "\033[1m";
}

echo Colors::$CYAN . Colors::$BOLD . "\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  ACTUALIZACIÓN DE DECIMALES A CONSTANTE DECIMALS_CURRENCY       ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n";
echo Colors::$RESET . "\n";

$baseDir = dirname(__DIR__);
$backupDir = $baseDir . '/__backups/decimals-update-' . date('Y-m-d_H-i-s');
$logFile = $baseDir . '/__scripts/update-decimals-log-' . date('Y-m-d_H-i-s') . '.txt';
$rollbackScript = $baseDir . '/__scripts/rollback-decimals-' . date('Y-m-d_H-i-s') . '.sh';

// Estadísticas
$stats = [
  'files_processed' => 0,
  'files_modified' => 0,
  'total_replacements' => 0,
  'number_format_2' => 0,
  'number_format_4' => 0,
  'round_2' => 0,
  'round_4' => 0,
  'format_decimal_number_2' => 0,
  'format_decimal_number_4' => 0,
];

$modifiedFiles = [];

// Crear directorio de backup
if (!file_exists($backupDir)) {
  mkdir($backupDir, 0755, true);
  echo Colors::$GREEN . "✓ Directorio de backup creado: $backupDir\n" . Colors::$RESET;
}

// Iniciar log
file_put_contents($logFile, "=== UPDATE DECIMALS LOG ===\n");
file_put_contents($logFile, "Fecha: " . date('Y-m-d H:i:s') . "\n\n", FILE_APPEND);

/**
 * Buscar archivos PHP recursivamente
 */
function findPHPFiles($dir, $exclude = [])
{
  $files = [];
  $iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );

  foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
      $path = $file->getPathname();
      $skip = false;

      foreach ($exclude as $excludePattern) {
        if (strpos($path, $excludePattern) !== false) {
          $skip = true;
          break;
        }
      }

      if (!$skip) {
        $files[] = $path;
      }
    }
  }

  return $files;
}

/**
 * Procesar archivo
 */
function processFile($filePath, $backupDir, &$stats, &$modifiedFiles, $logFile)
{
  $stats['files_processed']++;

  $content = file_get_contents($filePath);
  $originalContent = $content;
  $replacements = 0;

  // Reemplazar number_format con 2 decimales
  $pattern1 = '/number_format\(([^,]+),\s*2\)/';
  $replacement1 = 'number_format($1, DECIMALS_CURRENCY)';
  $newContent = preg_replace($pattern1, $replacement1, $content, -1, $count1);
  if ($count1 > 0) {
    $content = $newContent;
    $replacements += $count1;
    $stats['number_format_2'] += $count1;
  }

  // Reemplazar number_format con 4 decimales
  $pattern2 = '/number_format\(([^,]+),\s*4\)/';
  $replacement2 = 'number_format($1, DECIMALS_CURRENCY)';
  $newContent = preg_replace($pattern2, $replacement2, $content, -1, $count2);
  if ($count2 > 0) {
    $content = $newContent;
    $replacements += $count2;
    $stats['number_format_4'] += $count2;
  }

  // Reemplazar round con 2 decimales
  $pattern3 = '/round\(([^,]+),\s*2\)/';
  $replacement3 = 'round($1, DECIMALS_CURRENCY)';
  $newContent = preg_replace($pattern3, $replacement3, $content, -1, $count3);
  if ($count3 > 0) {
    $content = $newContent;
    $replacements += $count3;
    $stats['round_2'] += $count3;
  }

  // Reemplazar round con 4 decimales
  $pattern4 = '/round\(([^,]+),\s*4\)/';
  $replacement4 = 'round($1, DECIMALS_CURRENCY)';
  $newContent = preg_replace($pattern4, $replacement4, $content, -1, $count4);
  if ($count4 > 0) {
    $content = $newContent;
    $replacements += $count4;
    $stats['round_4'] += $count4;
  }

  // Reemplazar format_decimal_number con 2 decimales
  $pattern5 = '/format_decimal_number\(([^,]+),\s*2\)/';
  $replacement5 = 'format_decimal_number($1, DECIMALS_CURRENCY)';
  $newContent = preg_replace($pattern5, $replacement5, $content, -1, $count5);
  if ($count5 > 0) {
    $content = $newContent;
    $replacements += $count5;
    $stats['format_decimal_number_2'] += $count5;
  }

  // Reemplazar format_decimal_number con 4 decimales
  $pattern6 = '/format_decimal_number\(([^,]+),\s*4\)/';
  $replacement6 = 'format_decimal_number($1, DECIMALS_CURRENCY)';
  $newContent = preg_replace($pattern6, $replacement6, $content, -1, $count6);
  if ($count6 > 0) {
    $content = $newContent;
    $replacements += $count6;
    $stats['format_decimal_number_4'] += $count6;
  }

  // Si hubo cambios, hacer backup y guardar
  if ($replacements > 0) {
    $stats['files_modified']++;
    $stats['total_replacements'] += $replacements;

    // Crear backup
    $relativePath = str_replace(dirname($backupDir) . '/', '', $filePath);
    $backupPath = $backupDir . '/' . $relativePath;
    $backupPathDir = dirname($backupPath);

    if (!file_exists($backupPathDir)) {
      mkdir($backupPathDir, 0755, true);
    }

    file_put_contents($backupPath, $originalContent);

    // Guardar archivo modificado
    file_put_contents($filePath, $content);

    // Registrar cambio
    $modifiedFiles[] = [
      'path' => $filePath,
      'backup' => $backupPath,
      'replacements' => $replacements,
    ];

    $logMsg = "✓ Modificado: $filePath ($replacements reemplazos)\n";
    echo Colors::$GREEN . $logMsg . Colors::$RESET;
    file_put_contents($logFile, $logMsg, FILE_APPEND);

    return true;
  }

  return false;
}

// Directorios a excluir
$excludeDirs = [
  'vendor',
  'node_modules',
  '__backups',
  'libs/tcpdf',
  'data/lib/tcpdf',
  'src/plugins',
];

echo Colors::$YELLOW . "Buscando archivos PHP...\n" . Colors::$RESET;
$files = findPHPFiles($baseDir, $excludeDirs);
echo Colors::$CYAN . "Encontrados: " . count($files) . " archivos\n\n" . Colors::$RESET;

echo Colors::$YELLOW . "Procesando archivos...\n" . Colors::$RESET;
$progressBar = 0;
$totalFiles = count($files);

foreach ($files as $index => $file) {
  processFile($file, $backupDir, $stats, $modifiedFiles, $logFile);

  // Barra de progreso
  $progress = (($index + 1) / $totalFiles) * 100;
  if (floor($progress / 10) > $progressBar) {
    $progressBar = floor($progress / 10);
    echo Colors::$BLUE . "Progreso: " . round($progress, 1) . "%\n" . Colors::$RESET;
  }
}

// Crear script de rollback
echo "\n" . Colors::$YELLOW . "Creando script de rollback...\n" . Colors::$RESET;
$rollbackContent = "#!/bin/bash\n";
$rollbackContent .= "# Script de rollback generado automáticamente\n";
$rollbackContent .= "# Fecha: " . date('Y-m-d H:i:s') . "\n\n";
$rollbackContent .= "echo \"═══════════════════════════════════════════════════════════════\"\n";
$rollbackContent .= "echo \"  ROLLBACK: Restaurando archivos desde backup\"\n";
$rollbackContent .= "echo \"═══════════════════════════════════════════════════════════════\"\n";
$rollbackContent .= "echo \"\"\n\n";

foreach ($modifiedFiles as $file) {
  $rollbackContent .= "echo \"Restaurando: {$file['path']}\"\n";
  $rollbackContent .= "cp \"{$file['backup']}\" \"{$file['path']}\"\n\n";
}

$rollbackContent .= "echo \"\"\n";
$rollbackContent .= "echo \"✓ Rollback completado. " . count($modifiedFiles) . " archivos restaurados.\"\n";
$rollbackContent .= "echo \"\"\n";

file_put_contents($rollbackScript, $rollbackContent);
chmod($rollbackScript, 0755);
echo Colors::$GREEN . "✓ Script de rollback creado: $rollbackScript\n" . Colors::$RESET;

// Resumen final
echo "\n";
echo Colors::$CYAN . Colors::$BOLD . "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║                      RESUMEN DE ACTUALIZACIÓN                    ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n" . Colors::$RESET;
echo "\n";
echo Colors::$WHITE . "Archivos procesados:     " . Colors::$CYAN . $stats['files_processed'] . "\n";
echo Colors::$WHITE . "Archivos modificados:    " . Colors::$GREEN . Colors::$BOLD . $stats['files_modified'] . "\n" . Colors::$RESET;
echo Colors::$WHITE . "Total de reemplazos:     " . Colors::$YELLOW . Colors::$BOLD . $stats['total_replacements'] . "\n" . Colors::$RESET;
echo "\n";
echo Colors::$WHITE . "Detalle de reemplazos:\n";
echo Colors::$WHITE . "  • number_format(..., DECIMALS_CURRENCY): " . Colors::$CYAN . $stats['number_format_2'] . "\n";
echo Colors::$WHITE . "  • number_format(..., DECIMALS_CURRENCY): " . Colors::$CYAN . $stats['number_format_4'] . "\n";
echo Colors::$WHITE . "  • round(..., DECIMALS_CURRENCY):         " . Colors::$CYAN . $stats['round_2'] . "\n";
echo Colors::$WHITE . "  • round(..., DECIMALS_CURRENCY):         " . Colors::$CYAN . $stats['round_4'] . "\n";
echo Colors::$WHITE . "  • format_decimal_number(..., DECIMALS_CURRENCY): " . Colors::$CYAN . $stats['format_decimal_number_2'] . "\n";
echo Colors::$WHITE . "  • format_decimal_number(..., DECIMALS_CURRENCY): " . Colors::$CYAN . $stats['format_decimal_number_4'] . "\n";
echo Colors::$RESET . "\n";
echo Colors::$GREEN . "✓ Backup guardado en:    " . Colors::$CYAN . $backupDir . "\n";
echo Colors::$GREEN . "✓ Log guardado en:       " . Colors::$CYAN . $logFile . "\n";
echo Colors::$GREEN . "✓ Script de rollback:    " . Colors::$CYAN . $rollbackScript . "\n";
echo Colors::$RESET . "\n";

// Guardar resumen en log
$summary = "\n=== RESUMEN ===\n";
$summary .= "Archivos procesados: {$stats['files_processed']}\n";
$summary .= "Archivos modificados: {$stats['files_modified']}\n";
$summary .= "Total reemplazos: {$stats['total_replacements']}\n";
$summary .= "\nDetalle:\n";
$summary .= "  number_format(..., DECIMALS_CURRENCY): {$stats['number_format_2']}\n";
$summary .= "  number_format(..., DECIMALS_CURRENCY): {$stats['number_format_4']}\n";
$summary .= "  round(..., DECIMALS_CURRENCY): {$stats['round_2']}\n";
$summary .= "  round(..., DECIMALS_CURRENCY): {$stats['round_4']}\n";
$summary .= "  format_decimal_number(..., DECIMALS_CURRENCY): {$stats['format_decimal_number_2']}\n";
$summary .= "  format_decimal_number(..., DECIMALS_CURRENCY): {$stats['format_decimal_number_4']}\n";
file_put_contents($logFile, $summary, FILE_APPEND);

echo Colors::$YELLOW . "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  IMPORTANTE: Para deshacer los cambios ejecuta:                 ║\n";
echo "║  bash " . basename($rollbackScript) . "                                  ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n" . Colors::$RESET;
echo "\n";
