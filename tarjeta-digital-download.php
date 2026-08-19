<?php
require_once __DIR__ . "/syspos/inc/settings.inc.php";

$userSlug = cleanStr($_GET["slug"]);

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
$filename = createSlug($userInfo["nombre_completo"]);

// Obtener los datos de la sucursal del usuario
$query = "SELECT * FROM paal_sucursales WHERE id_sucursal = ?";
$stmt   = $mysqli->prepare($query);

$stmt->bind_param("i", $userInfo["id_sucursal"]);
$stmt->execute();

$result     = $stmt->get_result();
$branchInfo = $result->fetch_assoc();

// 1. Forzar a Safari a reconocer el archivo como un contacto real
header('Content-Type: text/vcard; charset=utf-8');
header("Content-Disposition: attachment; filename=\"{$filename}.vcf\"");

// 2. Aquí pones los mismos datos que tenías en JavaScript (ajusta las variables PHP a tu proyecto)
echo "BEGIN:VCARD\n";
echo "VERSION:3.0\n";
echo "N:;" . $userInfo["nombre_completo"] . ";;;\n";
echo "FN:" . $userInfo["nombre_completo"] . "\n";
echo "ORG:{$branchInfo["nombre_comercial"]}\n";
echo "TITLE:Asesor de Ventas\n";
echo "TEL;TYPE=CELL,VOICE:" . $userInfo["telefono"] . "\n";
echo "EMAIL;TYPE=PREF,INTERNET:" . $userInfo["correo"] . "\n";
echo "ADR;TYPE=WORK:;;{$branchInfo["direccion"]}\n";
echo "END:VCARD";
exit;
