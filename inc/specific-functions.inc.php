<?php
function getUserData(
  $user_id = 0,
  $md5 = false
) {
  global $mysqli;
  global $db_ati;
  global $db_dti;

  if (!$user_id) return false;

  $byId = "U.id_usuario = {$user_id}";

  if ($md5) $byId = "MD5(U.id_usuario) = '$user_id'";

  $query = "SELECT
      U.*,
      R.rol,
      R.slug AS rol_slug,
      S.nombre_sucursal
    FROM {$db_ati}_usuarios AS U
      LEFT JOIN {$db_ati}_roles AS R ON (U.id_rol = R.id_rol)
      LEFT JOIN {$db_dti}_sucursales AS S ON (U.id_sucursal = S.id_sucursal)
    WHERE
      {$byId}
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  if ($num_rows > 0) :
    $user_data = mysqli_fetch_assoc($query_result);

    if ($user_data['rol_slug'] === 'administrador' || $user_data['rol_slug'] === 'root') :
      $user_data['IS_ADMIN'] = 'si';
    endif;

    return $user_data;
  endif;
}

function obtenerFolioSeguro(
  $tipo_factura,
  $serie,
  $mysqli = null
) {
  $db = $mysqli ?: $GLOBALS['mysqli'];

  if (!$db) return 0;

  // Asignacion atomica de folio en una sola operacion
  $sql = "INSERT INTO folios_control (tipo_factura, serie, ultimo_folio)
          VALUES (?, ?, LAST_INSERT_ID(1))
          ON DUPLICATE KEY UPDATE ultimo_folio = LAST_INSERT_ID(ultimo_folio + 1)";

  $stmt = $db->prepare($sql);
  if (!$stmt) {
    error_log("Error preparando obtenerFolioSeguro: " . $db->error);
    return 0;
  }

  $stmt->bind_param("ss", $tipo_factura, $serie);
  if (!$stmt->execute()) {
    error_log("Error ejecutando obtenerFolioSeguro: " . $stmt->error);
    return 0;
  }

  $result = $db->query("SELECT LAST_INSERT_ID() AS folio");
  if (!$result) {
    error_log("Error consultando obtenerFolioSeguro: " . $db->error);
    return 0;
  }

  $row = $result->fetch_assoc();
  return isset($row['folio']) ? (int)$row['folio'] : 0;
}

function checkIfExistsUserByEmailAndUsername(
  $email,
  $username,
  $user_id = null
) {
  global $mysqli;
  global $db_ati;

  $ignore_user = "";

  if ($user_id) $ignore_user = "AND id_usuario != $user_id";

  $query = "SELECT
      id_usuario,
      correo,
      username
    FROM {$db_ati}_usuarios
    WHERE
      (
        correo    = '$email'    OR
        username  = '$username' OR
        username  = '$email'
      )
      $ignore_user
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows === 0)  return false;

  $user_data = mysqli_fetch_assoc($query_result);

  if ($user_data['correo'] === $email && $user_data['username'] === $username) return 'El correo y el nombre de usuario ya están en uso.';
  else if ($user_data['correo']   === $email) return 'El correo ya está en uso.';
  else if ($user_data['username'] === $username) return 'El nombre de usuario ya está en uso.';
  else return 'El correo o el nombre de usuario ya están en uso';
}

function createUserReference(
  $id_usuario
) {
  global $mysqli;
  global $db_ati;

  $query = "SELECT nombre_completo FROM {$db_ati}_usuarios WHERE id_usuario = $id_usuario LIMIT 1";

  $query_result = mysqli_query($mysqli, $query);
  $data_usuario = mysqli_fetch_assoc($query_result);

  $nombre_completo  = $data_usuario['nombre_completo'];
  $primer_caracter  = $nombre_completo[0];
  $segundo_caracter = $nombre_completo[1];
  $ultimo_caracter  = $nombre_completo[strlen($nombre_completo) - 1];

  $referencia       = $primer_caracter . $segundo_caracter . $ultimo_caracter . $id_usuario[0];

  return $referencia;
}

/* function getTireWidthsCatalog(
  $id_llantera,
  $placeholder = '--Seleccionar--'
) {
  global $mysqli;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      width AS id,
      width AS label
    FROM
      {$db_dti}_tires AS T
    WHERE
      id_llantera = $id_llantera
    GROUP BY
      width
    ORDER BY
      width
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id     = $row['id'];
      $label  = $row['label'];

      $response .= '<option value="' . $id . '">' . $label . '</option>';
    endwhile;
  endif;

  return $response;
} */

function format_decimal_number(
  $number,
  $limit_decimals = 0
) {
  $number_format  = number_format($number, $limit_decimals);
  $number_explode = explode('.', $number_format);
  $decimals       = $number_explode[1];

  if ($decimals == '00' || $decimals == '0000') :
    $number_explode = explode('.', $number);
    $number_to_format = floatval($number_explode[0]);
    return number_format($number_to_format);
  endif;

  return number_format($number, $limit_decimals);
}

/* function getCategoriesCatalog(
  $value = ''
) {
  global $mysqli;

  $query = "SELECT
      id_categoria  AS id,
      categoria     AS label
    FROM up_categorias
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return '';

  if ($num_rows > 0) :
    $catalog = '';

    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $catalog .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;

    return $catalog;
  endif;
} */

function getBranchOfficesCatalog(
  $value = '',
  $label = '--Seleccionar--',
  $mostrar_almacen = true,
  $initial_option = true,
  $optionHide = null,
  $onlyShowSelected = false
) {
  global $mysqli;
  global $db_dti;

  $condicion_almacen = "tipo != 'almacen'";
  $optionHideCondition = "1=1";

  if ($mostrar_almacen) $condicion_almacen = "1=1";
  if ($optionHide)      $optionHideCondition = "id_sucursal != $optionHide";

  $query = "SELECT
      id_sucursal     AS id,
      nombre_sucursal AS label,
      cp
    FROM
      {$db_dti}_sucursales
    WHERE
      (status = 'activo')     AND
      ({$condicion_almacen})  AND
      ({$optionHideCondition})
    ORDER BY nombre_sucursal
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return '';

  if ($num_rows > 0) :
    $catalog = '';
    if ($initial_option) $catalog .= '<option value="">' . $label . '</option>';

    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      if ($onlyShowSelected && !$selected) continue;

      $cp = $row['cp'];

      $catalog .= '<option value="' . $id . '" ' . $selected . ' data-cp="' . $cp . '">' . $label . '</option>';
    endwhile;

    return $catalog;
  endif;
}

function getProductTypesCatalog(
  $value = '',
  $label = '--Seleccionar--',
  $initial_option = true
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      id_tipo AS id,
      nombre  AS label
    FROM
      {$db_dti}_tipos
    ORDER BY
      nombre
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return '';

  if ($num_rows > 0) :
    $catalog = '';
    if ($initial_option) $catalog .= '<option value="">' . $label . '</option>';

    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $catalog .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;

    return $catalog;
  endif;
}

function addProductOnInventory(
  $id_producto,
  $nombre_producto,
  $stock = 0,
  $branchCode = null
) {
  global $mysqli;
  global $db_dti;

  $byBranchCode = "1=1";

  if ($branchCode) $byBranchCode = "numero_serie = '{$branchCode}'";

  $query_sucursal = "SELECT
      id_sucursal
    FROM
      {$db_dti}_sucursales
    WHERE
      status = 'activo' AND
      ({$byBranchCode})
    ORDER BY
      id_sucursal
    ASC
  ";

  $query_sucursal_result  = mysqli_query($mysqli, $query_sucursal);
  $num_rows               = mysqli_num_rows($query_sucursal_result);

  if ($num_rows > 0) :
    $values = "";
    $count  = 0;

    while ($row = mysqli_fetch_assoc($query_sucursal_result)) :
      $id_sucursal  = $row['id_sucursal'];
      $concat       = $count > 0 ? "," : "";
      $values       .= "{$concat}({$id_sucursal}, {$id_producto}, {$stock})";

      addLogInKardex(
        $id_sucursal,
        [
          'id_producto' => $id_producto,
          'cantidad'    => 0,
          'stock'       => $stock,
          'nombre_producto' => $nombre_producto
        ],
        ACCION_NUEVO_PRODUCTO,
        'incremento',
        'ajuste'
      );

      $count++;
    endwhile;

    $query_inventario = "INSERT INTO {$db_dti}_inventario (
        id_sucursal,
        id_producto,
        stock
      ) VALUES
        {$values}
      ON DUPLICATE KEY UPDATE
        stock = VALUES(stock)
    ";

    $query_inventario_result = mysqli_query($mysqli, $query_inventario);

    return $query_inventario_result;
  endif;
}

function addBranchOfficeOnInventory(
  $id_sucursal
) {
  global $mysqli;
  global $db_dti;

  $query_productos = "SELECT
      id_producto
    FROM {$db_dti}_productos
    ORDER BY
      id_producto
    ASC
  ";

  $query_productos_result  = mysqli_query($mysqli, $query_productos);
  $num_rows               = mysqli_num_rows($query_productos_result);

  if ($num_rows > 0) :
    $values = "";
    $count  = 0;

    while ($row = mysqli_fetch_assoc($query_productos_result)) :
      $id_producto  = $row['id_producto'];
      $concat       = $count > 0 ? "," : "";
      $values       .= "{$concat}({$id_sucursal}, {$id_producto})";

      $count++;
    endwhile;

    $query_inventario = "INSERT INTO {$db_dti}_inventario (
        id_sucursal,
        id_producto
      ) VALUES
        {$values};
    ";

    $query_inventario_result = mysqli_query($mysqli, $query_inventario);

    return $query_inventario_result;
  endif;
}

function getStoreId()
{
  global $mysqli;
  global $db_dti;

  $query = "SELECT id_sucursal FROM {$db_dti}_sucursales WHERE tipo = 'almacen' LIMIT 1";
  $query_result = mysqli_query($mysqli, $query);
  $data = mysqli_fetch_assoc($query_result);

  return $data['id_sucursal'];
}

function getBranchOfficeData(
  $id_sucursal
) {
  global $mysqli;
  global $db_dti;

  if (empty($id_sucursal)) return false;

  $query = "SELECT
      id_sucursal,
      nombre_sucursal,
      telefono,
      direccion,
      tipo,
      numero_serie,
      correo,
      rfc,
      cp
    FROM {$db_dti}_sucursales
    WHERE
      id_sucursal = $id_sucursal AND
      status      = 'activo'
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data_sucursal = mysqli_fetch_assoc($query_result);

  return $data_sucursal;
}

function getBranchOfficeProductData(
  $id_sucursal,
  $id_producto
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      I.id_inventario,
      I.id_sucursal,
      I.id_producto,
      I.stock,
      S.nombre_sucursal,
      P.id_tipo,
      P.nombre_producto,
      P.unidad_entrada,
      P.codigo,
      P.precio_venta,
      P.precio_costo,
      P.contenido,
      P.unidad,
      P.aplica_iva,
      P.aplica_ieps,
      P.ieps_porcentaje,
      P.cantidad_mayoreo,
      P.precio_mayoreo,
      P.en_dolares,
      P.precio_costo_original,
      P.precio_venta_original,
      P.precio_mayoreo_original,
      P.numero_piezas,
      CF.limite_descuento,
      P.id_tipo,
      T.nombre AS tipo,
      T.requiere_numero_serie
    FROM {$db_dti}_inventario AS I
      LEFT JOIN {$db_dti}_sucursales  AS S ON (I.id_sucursal = S.id_sucursal)
      LEFT JOIN {$db_dti}_productos   AS P ON (I.id_producto = P.id_producto)
      LEFT JOIN {$db_dti}_categoria_familias  AS CF ON (P.id_categoria_familia  = CF.id_categoria_familia)
      LEFT JOIN {$db_dti}_tipos         AS T ON (P.id_tipo             = T.id_tipo)
    WHERE
      I.id_sucursal = {$id_sucursal} AND
      I.id_producto = {$id_producto} And
      S.status      = 'activo'
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data_producto = mysqli_fetch_assoc($query_result);

  return $data_producto;
}

function addLogInKardex(
  $id_sucursal,
  $data_producto,
  $accion,
  $tipo_movimiento = 'incremento',
  $tipo_configuracion,
  $uid = null
) {
  global $mysqli;
  global $db_dti;

  $id_usuario = get_id_usuario();
  $existencia = 0;

  if ($uid) $id_usuario = $uid;

  if ($tipo_configuracion === 'compra'                  && $tipo_movimiento === TIPO_MOVIMIENTO_INCREMENTO) $existencia = ($data_producto['stock']         + $data_producto['cantidad']);
  if ($tipo_configuracion === 'ajuste'                  && $tipo_movimiento === TIPO_MOVIMIENTO_INCREMENTO) $existencia = ($data_producto['stock']         + $data_producto['cantidad']);
  if ($tipo_configuracion === 'transferencia-almacen'   && $tipo_movimiento === TIPO_MOVIMIENTO_INCREMENTO) $existencia = ($data_producto['stock_origen']  - $data_producto['cantidad']);
  if ($tipo_configuracion === 'transferencia-sucursal'  && $tipo_movimiento === TIPO_MOVIMIENTO_INCREMENTO) $existencia = ($data_producto['stock_destino'] + $data_producto['cantidad']);
  if ($tipo_configuracion === 'venta'                   && $tipo_movimiento === TIPO_MOVIMIENTO_INCREMENTO) $existencia = ($data_producto['stock']         - $data_producto['cantidad']);

  if ($tipo_configuracion === 'compra'                  && $tipo_movimiento === TIPO_MOVIMIENTO_DECREMENTO) $existencia = ($data_producto['stock']         - $data_producto['cantidad']);
  if ($tipo_configuracion === 'ajuste'                  && $tipo_movimiento === TIPO_MOVIMIENTO_DECREMENTO) $existencia = ($data_producto['stock']         - $data_producto['cantidad']);
  if ($tipo_configuracion === 'transferencia-almacen'   && $tipo_movimiento === TIPO_MOVIMIENTO_DECREMENTO) $existencia = ($data_producto['stock_origen']  + $data_producto['cantidad']);
  if ($tipo_configuracion === 'transferencia-sucursal'  && $tipo_movimiento === TIPO_MOVIMIENTO_DECREMENTO) $existencia = ($data_producto['stock_destino'] - $data_producto['cantidad']);
  if ($tipo_configuracion === 'venta'                   && $tipo_movimiento === TIPO_MOVIMIENTO_DECREMENTO) $existencia = ($data_producto['stock']         + $data_producto['cantidad']);

  if ($tipo_configuracion === 'ajuste' && $tipo_movimiento === TIPO_MOVIMIENTO_DECREMENTO && $data_producto['tipo'] == TIPO_MOVIMIENTO_DECREMENTO) :
    $existencia = ($data_producto['stock'] + $data_producto['cantidad']);
  endif;

  if ($tipo_configuracion === 'compra' && $tipo_movimiento === TIPO_MOVIMIENTO_DECREMENTO && $data_producto['tipo'] == TIPO_MOVIMIENTO_DECREMENTO) :
    $existencia = ($data_producto['stock'] + $data_producto['cantidad']);
  endif;

  $query = "INSERT INTO {$db_dti}_kardex (
      id_usuario,
      id_sucursal,
      id_producto,
      nombre_producto,
      cantidad,
      accion,
      existencia
    ) VALUES (
      {$id_usuario},
      {$id_sucursal},
      {$data_producto['id_producto']},
      '{$data_producto['nombre_producto']}',
      {$data_producto['cantidad']},
      '{$accion}',
      {$existencia}
    )
  ";

  $query_result = mysqli_query($mysqli, $query);

  return $query_result;
}

function getStoreSettingsProductsTable(
  $id_inventario_ajuste
) {
  global $mysqli;
  global $db_dti;

  $productos = '';

  $query = "SELECT
      AAP.id_inventario_ajuste_producto,
      AAP.id_inventario_ajuste,
      AAP.id_producto,
      AAP.cantidad,
      P.codigo,
      P.nombre_producto,
      P.unidad
    FROM {$db_dti}_inventario_ajuste_productos AS AAP
      LEFT JOIN {$db_dti}_productos AS P ON (AAP.id_producto = P.id_producto)
    WHERE
      AAP.id_inventario_ajuste = {$id_inventario_ajuste}
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : 'pzs.';

      $productos .= '
        <tr>
          <td>' . $row['codigo'] . '</td>
          <td>' . $row['nombre_producto'] . '</td>
          <td>' . $row['cantidad'] . ' ' . $unit_type . '</td>
        </tr>
      ';
    endwhile;
  endif;

  return $productos;
}

function getStoreTransferProductsTable(
  $id_inventario_transferencia
) {
  global $mysqli;
  global $db_dti;

  $productos = '';

  $query = "SELECT
      AAP.id_inventario_transferencia_producto,
      AAP.id_inventario_transferencia,
      AAP.id_producto,
      AAP.cantidad,
      AAP.completado,
      AAP.cancelado,
      AAP.recibido,
      P.codigo,
      P.nombre_producto,
      P.unidad
    FROM {$db_dti}_inventario_transferencia_productos AS AAP
      LEFT JOIN {$db_dti}_productos AS P ON (AAP.id_producto = P.id_producto)
    WHERE
      AAP.id_inventario_transferencia = {$id_inventario_transferencia}
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : 'pzs.';

      $recibidoLabel  = $row["completado"] == "si" ? $row["recibido"] . " " . $unit_type : "<span class='badge bg-warning text-dark'>Pendiente</span>";
      $NSLabel        = "--";

      $queryNS = "SELECT
          numero_serie,
          cancelado,
          completado,
          recibido
        FROM
          {$db_dti}_inventario_transferencia_producto_numeros_serie
        WHERE
          id_inventario_transferencia_producto = {$row['id_inventario_transferencia_producto']}
      ";

      $resultNS = mysqli_query($mysqli, $queryNS);
      $numNS    = mysqli_num_rows($resultNS);

      if ($numNS > 0) {
        $serialNumbers = [];

        while ($rowNS = mysqli_fetch_assoc($resultNS)) :
          $serialNumber = $rowNS["numero_serie"];
          $cancelled    = $rowNS["cancelado"];
          $completed    = $rowNS["completado"];
          $received     = $rowNS["recibido"];

          $iconReceived = '<i class="fa fa-check me-1 text-success"></i>';
          $iconNotReceived = '<i class="fa fa-times me-1 text-danger"></i>';
          $icon = $received == 'si' ? $iconReceived : $iconNotReceived;

          if ($completed == "no") $icon = '<i class="fa fa-clock me-1 text-info"></i>';
          if ($cancelled == "si") $icon = '<i class="fa fa-ban me-1 text-danger"></i>';

          $serialNumbers[] = "{$icon} {$serialNumber}";
        endwhile;

        $NSLabel = implode("<br>", $serialNumbers);
      }

      $productos .= '
        <tr>
          <td>' . $row['codigo'] . '</td>
          <td>' . $row['nombre_producto'] . '</td>
          <td>' . (int)$row['cantidad'] . ' ' . $unit_type . '</td>
          <td>' . $recibidoLabel . '</td>
          <td>' . $NSLabel . '</td>
        </tr>
      ';
    endwhile;
  endif;

  return $productos;
}

function getSessionBranchOfficeId()
{
  $id_usuario = get_id_usuario();
  $id_sucursal = getBranchOfficeIdByUserId($id_usuario);

  return $id_sucursal;
}

function getBranchOfficeIdByUserId(
  $id_usuario
) {
  $data_usuario = getUserData($id_usuario);

  return $data_usuario['id_sucursal'];
}

function getUsersByBranchOfficeId(
  $id_sucursal
) {
  global $mysqli;
  global $db_ati;

  $usuarios = [];

  $query = "SELECT
      id_usuario,
      nombre_completo
    FROM {$db_ati}_usuarios
    WHERE
      id_sucursal = {$id_sucursal} AND
      status      = 'activo'       AND
      id_rol      != 1
    ORDER BY
      nombre_completo
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      array_push($usuarios, [
        'id_usuario'      => $row['id_usuario'],
        'nombre_completo' => $row['nombre_completo']
      ]);
    endwhile;
  endif;

  return $usuarios;
}

function getBranchOfficesData()
{
  global $mysqli;
  global $db_dti;

  $sucursales = [];

  $query = "SELECT
      id_sucursal,
      nombre_sucursal
    FROM
      {$db_dti}_sucursales
    WHERE
      status = 'activo'
    ORDER BY
      nombre_sucursal
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      array_push($sucursales, [
        'id_sucursal'     => $row['id_sucursal'],
        'nombre_sucursal' => $row['nombre_sucursal']
      ]);
    endwhile;
  endif;

  return $sucursales;
}

function getProductIdByCode(
  $codigo_producto
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      id_producto
    FROM
      {$db_dti}_productos
    WHERE
      codigo = '{$codigo_producto}'
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data_producto  = mysqli_fetch_assoc($query_result);
  $id_producto    = $data_producto['id_producto'];

  return $id_producto;
}

function getProductDataById(
  $id_producto
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      id_producto,
      codigo,
      nombre_producto,
      contenido,
      precio_venta,
      fecha_creacion,
      precio_venta,
      precio_venta2,
      precio_venta3
    FROM
      {$db_dti}_productos
    WHERE
      id_producto = {$id_producto}
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data_producto = mysqli_fetch_assoc($query_result);

  return $data_producto;
}

function getSaleData(
  $id_venta
) {
  global $mysqli;
  global $db_dti;
  global $db_ati;

  $query = "SELECT
      V.id_venta,
      V.id_usuario,
      V.id_sucursal,
      V.id_cliente,
      V.folio,
      V.tipo,
      V.observaciones,
      V.total,
      V.pago_con,
      V.cambio,
      V.status,
      V.fecha_creacion,
      U.nombre_completo AS usuario,
      S.nombre_sucursal AS sucursal,
      S.telefono        AS sucursal_telefono,
      S.direccion       AS sucursal_direccion,
      S.numero_serie,
      C.nombre_completo AS cliente,
      DATE_FORMAT(V.fecha_creacion, '%d-%m-%Y') AS ticket_fecha,
      DATE_FORMAT(V.fecha_creacion, '%h:%i %p') AS ticket_hora
    FROM {$db_dti}_ventas AS V
      LEFT JOIN {$db_ati}_usuarios    AS U ON (V.id_usuario   = U.id_usuario)
      LEFT JOIN {$db_dti}_sucursales  AS S ON (V.id_sucursal  = S.id_sucursal)
      LEFT JOIN {$db_dti}_clientes    AS C ON (V.id_cliente   = C.id_cliente)
    WHERE
      V.id_venta = {$id_venta}
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  if ($num_rows > 0) :
    $data_venta = mysqli_fetch_assoc($query_result);
    $productos  = [];

    $query_productos = "SELECT
        V.id_venta_producto,
        V.id_venta,
        V.id_producto,
        V.nombre_producto,
        V.cantidad,
        V.precio_neto AS precio_venta,
        V.total,
        V.cancelado,
        P.unidad,
        P.codigo
      FROM
        {$db_dti}_venta_productos AS V
      LEFT JOIN
        {$db_dti}_productos AS P ON (V.id_producto = P.id_producto)
      WHERE
        V.id_venta = {$id_venta}
    ";

    $query_productos_result = mysqli_query($mysqli, $query_productos);
    $num_productos          = mysqli_num_rows($query_productos_result);

    if ($num_productos > 0) :
      while ($producto = mysqli_fetch_assoc($query_productos_result)) :
        array_push($productos, $producto);
      endwhile;
    endif;

    $data_venta['productos'] = $productos;

    return $data_venta;
  endif;
}

function getSaleProducts(
  $sale_id
) {
  global $mysqli;
  global $db_dti;

  $productos = '';

  $query = "SELECT
      V.id_venta_producto,
      V.id_venta,
      V.id_producto,
      V.nombre_producto,
      V.cantidad,
      V.precio_venta,
      V.total,
      V.cancelado,
      P.unidad,
      P.codigo
    FROM
      {$db_dti}_venta_productos AS V
    LEFT JOIN
      {$db_dti}_productos AS P ON (V.id_producto = P.id_producto)
    WHERE
      V.id_venta = {$sale_id}
  ";

  $query_result   = mysqli_query($mysqli, $query);
  $num_productos  = mysqli_num_rows($query_result);

  if ($num_productos > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : 'pzs.';

      $productos .= '
        <tr>
          <td>' . $row['codigo'] . '</td>
          <td>' . $row['nombre_producto'] . '</td>
          <td>' . $row['cantidad'] . ' ' . $unit_type . '</td>
        </tr>
      ';
    endwhile;
  endif;

  return $productos;
}

function numtoletras($xcifra)
{
  $xarray = array(
    0 => "Cero",
    1 => "UN",
    "DOS",
    "TRES",
    "CUATRO",
    "CINCO",
    "SEIS",
    "SIETE",
    "OCHO",
    "NUEVE",
    "DIEZ",
    "ONCE",
    "DOCE",
    "TRECE",
    "CATORCE",
    "QUINCE",
    "DIECISEIS",
    "DIECISIETE",
    "DIECIOCHO",
    "DIECINUEVE",
    "VEINTI",
    30 => "TREINTA",
    40 => "CUARENTA",
    50 => "CINCUENTA",
    60 => "SESENTA",
    70 => "SETENTA",
    80 => "OCHENTA",
    90 => "NOVENTA",
    100 => "CIENTO",
    200 => "DOSCIENTOS",
    300 => "TRESCIENTOS",
    400 => "CUATROCIENTOS",
    500 => "QUINIENTOS",
    600 => "SEISCIENTOS",
    700 => "SETECIENTOS",
    800 => "OCHOCIENTOS",
    900 => "NOVECIENTOS"
  );
  //
  $xcifra = trim($xcifra);
  $xlength = strlen($xcifra);
  $xpos_punto = strpos($xcifra, ".");
  $xaux_int = $xcifra;
  $xdecimales = "00";
  if (!($xpos_punto === false)) {
    if ($xpos_punto == 0) {
      $xcifra = "0" . $xcifra;
      $xpos_punto = strpos($xcifra, ".");
    }
    $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
    $xdecimales = substr($xcifra . "00", $xpos_punto + 1, 2); // obtengo los valores decimales
  }

  $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
  $xcadena = "";
  for ($xz = 0; $xz < 3; $xz++) {
    $xaux = substr($XAUX, $xz * 6, 6);
    $xi = 0;
    $xlimite = 6; // inicializo el contador de centenas xi y establezco el límite a 6 dígitos en la parte entera
    $xexit = true; // bandera para controlar el ciclo del While
    while ($xexit) {
      if ($xi == $xlimite) { // si ya llegó al límite máximo de enteros
        break; // termina el ciclo
      }

      $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
      $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres dígitos)
      for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
        switch ($xy) {
          case 1: // checa las centenas
            if (substr($xaux, 0, 3) < 100) { // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas

            } else {
              $key = (int) substr($xaux, 0, 3);
              if (TRUE === array_key_exists($key, $xarray)) {  // busco si la centena es número redondo (100, 200, 300, 400, etc..)
                $xseek = $xarray[$key];
                $xsub = subfijo($xaux); // devuelve el subfijo correspondiente (Millón, Millones, Mil o nada)
                if (substr($xaux, 0, 3) == 100)
                  $xcadena = " " . $xcadena . " CIEN " . $xsub;
                else
                  $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
              } else { // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                $key = (int) substr($xaux, 0, 1) * 100;
                $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                $xcadena = " " . $xcadena . " " . $xseek;
              } // ENDIF ($xseek)
            } // ENDIF (substr($xaux, 0, 3) < 100)
            break;
          case 2: // checa las decenas (con la misma lógica que las centenas)
            if (substr($xaux, 1, 2) < 10) {
            } else {
              $key = (int) substr($xaux, 1, 2);
              if (TRUE === array_key_exists($key, $xarray)) {
                $xseek = $xarray[$key];
                $xsub = subfijo($xaux);
                if (substr($xaux, 1, 2) == 20)
                  $xcadena = " " . $xcadena . " VEINTE " . $xsub;
                else
                  $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                $xy = 3;
              } else {
                $key = (int) substr($xaux, 1, 1) * 10;
                $xseek = $xarray[$key];
                if (20 == substr($xaux, 1, 1) * 10)
                  $xcadena = " " . $xcadena . " " . $xseek;
                else
                  $xcadena = " " . $xcadena . " " . $xseek . " Y ";
              } // ENDIF ($xseek)
            } // ENDIF (substr($xaux, 1, 2) < 10)
            break;
          case 3: // checa las unidades
            if (substr($xaux, 2, 1) < 1) { // si la unidad es cero, ya no hace nada

            } else {
              $key = (int) substr($xaux, 2, 1);
              $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
              $xsub = subfijo($xaux);
              $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
            } // ENDIF (substr($xaux, 2, 1) < 1)
            break;
        } // END SWITCH
      } // END FOR
      $xi = $xi + 3;
    } // ENDDO

    if (substr(trim($xcadena), -5, 5) == "ILLON") // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
      $xcadena .= " DE";

    if (substr(trim($xcadena), -7, 7) == "ILLONES") // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
      $xcadena .= " DE";

    // ----------- esta línea la puedes cambiar de acuerdo a tus necesidades o a tu país -------
    if (trim($xaux) != "") {
      switch ($xz) {
        case 0:
          if (trim(substr($XAUX, $xz * 6, 6)) == "1")
            $xcadena .= "UN BILLON ";
          else
            $xcadena .= " BILLONES ";
          break;
        case 1:
          if (trim(substr($XAUX, $xz * 6, 6)) == "1")
            $xcadena .= "UN MILLON ";
          else
            $xcadena .= " MILLONES ";
          break;
        case 2:
          if ($xcifra < 1) {
            $xcadena = "CERO PESOS $xdecimales/100 M.N.";
          }
          if ($xcifra >= 1 && $xcifra < 2) {
            $xcadena = "UN PESO $xdecimales/100 M.N. ";
          }
          if ($xcifra >= 2) {
            $xcadena .= " PESOS $xdecimales/100 M.N. "; //
          }
          break;
      } // endswitch ($xz)
    } // ENDIF (trim($xaux) != "")
    // ------------------      en este caso, para México se usa esta leyenda     ----------------
    $xcadena = str_replace("VEINTI ", "VEINTI", $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
    $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
    $xcadena = str_replace("UN UN", "UN", $xcadena); // quito la duplicidad
    $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
    $xcadena = str_replace("BILLON DE MILLONES", "BILLON DE", $xcadena); // corrigo la leyenda
    $xcadena = str_replace("BILLONES DE MILLONES", "BILLONES DE", $xcadena); // corrigo la leyenda
    $xcadena = str_replace("DE UN", "UN", $xcadena); // corrigo la leyenda
  } // ENDFOR ($xz)
  return trim($xcadena);
}
function subfijo($xx)
{ // esta función regresa un subfijo para la cifra
  $xx = trim($xx);
  $xstrlen = strlen($xx);
  if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3)
    $xsub = "";
  //
  if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6)
    $xsub = "MIL";
  //
  return $xsub;
}

function get_cash_register_folio(
  $id_sucursal
) {
  global $mysqli;
  global $db_dti;

  $mark = "CC{$id_sucursal}-";

  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_cortes_caja
    WHERE
      id_sucursal       = {$id_sucursal} AND
      YEAR(fecha_hasta) = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  if (!$query_get_folio_num_rows) return $mark . '0001-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;
    $num_folio_length = strlen($new_num);
    $new_num_folio = '';

    if ($num_folio_length === 1) $new_num_folio = '000' . $new_num;
    if ($num_folio_length === 2) $new_num_folio = '00' . $new_num;
    if ($num_folio_length === 3) $new_num_folio = '0' . $new_num;
    if ($num_folio_length === 4) $new_num_folio = $new_num;

    $new_folio = $mark . $new_num_folio . '-' . $today_year;

    return $new_folio;
  }
}

function get_last_date_cash_register(
  $id_sucursal
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      fecha_hasta
    FROM
      {$db_dti}_cortes_caja
    WHERE
      id_sucursal = {$id_sucursal}
    ORDER BY
      fecha_hasta
    DESC
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    $data_corte   = mysqli_fetch_assoc($query_result);
    $fecha_corte  = $data_corte['fecha_hasta'];

    return $fecha_corte;
  endif;

  if ($num_rows == 0) :
    $query = "SELECT
        fecha_creacion
      FROM
        {$db_dti}_ventas
      WHERE
        id_sucursal = {$id_sucursal} AND
        corte       = 'no'
      ORDER BY
        fecha_creacion
      ASC
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows == 0) return false;

    if ($num_rows > 0) :
      $data_venta   = mysqli_fetch_assoc($query_result);
      $fecha_corte  = $data_venta['fecha_creacion'];

      return $fecha_corte;
    endif;
  endif;
}

/* function getCashRegisterData(
  $id_corte_caja
) {
  global $mysqli;
  global $db_dti;
  global $db_ati;

  $query = "SELECT
      CC.id_corte_caja,
      CC.id_usuario,
      CC.id_sucursal,
      CC.folio,
      CC.total,
      CC.fecha_desde,
      CC.fecha_hasta,
      S.nombre_sucursal,
      U.nombre_completo
    FROM
      {$db_dti}_cortes_caja AS CC
    LEFT JOIN
      {$db_dti}_sucursales AS S ON (CC.id_sucursal = S.id_sucursal)
    LEFT JOIN
      {$db_ati}_usuarios AS U ON (CC.id_usuario = U.id_usuario)
    WHERE
      CC.id_corte_caja = $id_corte_caja
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data_corte = mysqli_fetch_assoc($query_result);

  $query = "SELECT
      id_venta
    FROM
      {$db_dti}_ventas
    WHERE
      id_sucursal = {$data_corte['id_sucursal']} AND
      (fecha_creacion BETWEEN '{$data_corte['fecha_desde']}' AND '{$data_corte['fecha_hasta']}')
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    $productos = [];

    while ($venta = mysqli_fetch_assoc($query_result)) :
      $id_venta   = $venta['id_venta'];
      $venta_data = getSaleData($id_venta);

      $new_productos  = array_merge($productos, $venta_data['productos']);
      $productos      = $new_productos;
    endwhile;

    $data_corte['productos'] = $productos;
  endif;

  return $data_corte;
} */
function getCashRegisterData(
  $id_corte_caja
) {
  global $mysqli;
  global $db_dti;
  global $db_ati;

  $query = "SELECT
      CC.id_corte_caja,
      CC.id_usuario,
      CC.id_sucursal,
      CC.folio,
      CC.total,
      CC.fecha_desde,
      CC.fecha_hasta,
      S.nombre_sucursal,
      U.nombre_completo,
      DATE_FORMAT(CC.fecha_hasta, '%h:%i %p') AS ticket_hora,
      DATE_FORMAT(CC.fecha_hasta, '%d-%m-%Y') AS ticket_fecha
    FROM
      {$db_dti}_cortes_caja AS CC
    LEFT JOIN
      {$db_dti}_sucursales AS S ON (CC.id_sucursal = S.id_sucursal)
    LEFT JOIN
      {$db_ati}_usuarios AS U ON (CC.id_usuario = U.id_usuario)
    WHERE
      CC.id_corte_caja = $id_corte_caja
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data_corte = mysqli_fetch_assoc($query_result);

  $query = "SELECT
      id_venta
    FROM
      {$db_dti}_ventas
    WHERE
      id_sucursal = {$data_corte['id_sucursal']} AND
      (fecha_creacion BETWEEN '{$data_corte['fecha_desde']}' AND '{$data_corte['fecha_hasta']}') AND
      status = 'activo'
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    $productos          = [];
    $divisor_productos  = [];

    while ($venta = mysqli_fetch_assoc($query_result)) :
      $id_venta   = $venta['id_venta'];
      $venta_data = getSaleData($id_venta);

      # $new_productos  = array_merge($productos, $venta_data['productos']);
      # $productos      = $new_productos;

      $venta_productos = $venta_data['productos'];

      foreach ($venta_productos as $key => $producto) :
        $id_producto  = $producto['id_producto'];
        $in_list      = $productos[$id_producto];

        if ($producto['cancelado'] === 'si') continue;

        if (!$in_list) :
          $productos[$id_producto]          = $producto;
          $divisor_productos[$id_producto]  = 1;
        endif;

        if ($in_list) :
          $data_producto    = $productos[$id_producto];

          $old_precio_venta = $data_producto['precio_venta'];
          $new_precio_venta = $old_precio_venta + $producto['precio_venta'];

          $old_cantidad     = $data_producto['cantidad'];
          $new_cantidad     = $old_cantidad + $producto['cantidad'];

          $old_total        = $data_producto['total'];
          $new_total        = $old_total + $producto['total'];

          $data_producto['precio_venta']  = $new_precio_venta;
          $data_producto['cantidad']      = $new_cantidad;
          $data_producto['total']         = $new_total;

          $productos[$id_producto]        = $data_producto;
          $divisor_productos[$id_producto]++;
        endif;
      endforeach;
    endwhile;

    foreach ($productos as $key => $producto) :
      $id_producto      = $producto['id_producto'];
      $data_producto    = $producto;

      $divisor          = $divisor_productos[$producto['id_producto']];

      $old_precio_venta = $data_producto['precio_venta'];
      $new_precio_venta = $old_precio_venta / $divisor;

      $data_producto['precio_venta']  = $new_precio_venta;
      $productos[$id_producto]        = $data_producto;
    endforeach;

    $data_corte['productos'] = $productos;
  endif;

  return $data_corte;
}

function getInventoryTransferData(
  $id_inventario_transferencia
) {
  global $mysqli;
  global $db_dti;
  global $db_ati;

  $query = "SELECT
      IT.id_inventario_transferencia,
      IT.id_usuario,
      IT.id_sucursal_origen,
      IT.id_sucursal_destino,
      IT.folio,
      IT.tipo,
      IT.observaciones,
      IT.status,
      IT.fecha_creacion,
      DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y') AS ticket_fecha,
      DATE_FORMAT(IT.fecha_creacion, '%h:%i %p') AS ticket_hora,
      SO.nombre_sucursal AS sucursal_origen,
      SD.nombre_sucursal AS sucursal_destino,
      U.nombre_completo
    FROM
      {$db_dti}_inventario_transferencias AS IT
    LEFT JOIN
      {$db_dti}_sucursales  AS SO ON (IT.id_sucursal_origen   = SO.id_sucursal)
    LEFT JOIN
      {$db_dti}_sucursales  AS SD ON (IT.id_sucursal_destino  = SD.id_sucursal)
    LEFT JOIN
      {$db_ati}_usuarios    AS U  ON (IT.id_usuario           = U.id_usuario)
    WHERE
      IT.id_inventario_transferencia = {$id_inventario_transferencia}
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data_inventario_transferencia = mysqli_fetch_assoc($query_result);

  $query_productos = "SELECT
      ITP.id_inventario_transferencia_producto,
      ITP.id_inventario_transferencia,
      ITP.id_producto,
      ITP.cantidad,
      ITP.cancelado,
      P.codigo,
      P.precio_venta,
      P.nombre_producto,
      P.unidad,
      P.tipo,
      CU.clave AS clave_unidad,
      CPS.clave AS clave_producto_servicio
    FROM
      {$db_dti}_inventario_transferencia_productos AS ITP
    LEFT JOIN
      {$db_dti}_productos AS P ON (ITP.id_producto = P.id_producto)
    LEFT JOIN
      {$db_dti}_clave_unidades AS CU ON (CU.id_clave_unidad = P.id_clave_unidad)
    LEFT JOIN
      {$db_dti}_clave_producto_servicios AS CPS ON (CPS.id_clave_producto_servicio = P.id_clave_producto_servicio)
    WHERE
      ITP.id_inventario_transferencia = {$id_inventario_transferencia}
  ";

  $query_productos_result = mysqli_query($mysqli, $query_productos);
  $num_productos          = mysqli_num_rows($query_productos_result);
  $productos              = [];

  if ($num_productos) :
    while ($producto = mysqli_fetch_assoc($query_productos_result)) :
      // Obtener los números de serie
      // Solo los productos tipo "equipo" manejan números de serie
      if ($producto['tipo'] === 'equipo') :
        $producto['serial_numbers'] = [];

        $query_producto_numeros_serie = "SELECT
            numero_serie
          FROM
            {$db_dti}_inventario_transferencia_producto_numeros_serie
          WHERE
            id_inventario_transferencia_producto = {$producto['id_inventario_transferencia_producto']}
        ";

        $query_producto_numeros_serie_result = mysqli_query($mysqli, $query_producto_numeros_serie);
        $num_producto_numeros_serie          = mysqli_num_rows($query_producto_numeros_serie_result);

        if ($num_producto_numeros_serie > 0) :
          while ($producto_numeros_serie = mysqli_fetch_assoc($query_producto_numeros_serie_result)) :
            array_push($producto['serial_numbers'], $producto_numeros_serie["numero_serie"]);
          endwhile;
        endif;
      endif;

      array_push($productos, $producto);
    endwhile;
  endif;

  $data_inventario_transferencia['productos'] = $productos;

  return $data_inventario_transferencia;
}

function getInventoryAdjustmentData(
  $id_inventario_ajuste
) {
  global $mysqli;
  global $db_dti;
  global $db_ati;

  $query = "SELECT
      IA.id_inventario_ajuste,
      IA.id_usuario,
      IA.id_sucursal,
      IA.folio,
      IA.tipo,
      IA.observaciones,
      IA.status,
      IA.fecha_creacion,
      DATE_FORMAT(IA.fecha_creacion, '%h:%i %p') AS ticket_hora,
      DATE_FORMAT(IA.fecha_creacion, '%d-%m-%Y') AS ticket_fecha,
      S.nombre_sucursal,
      U.nombre_completo
    FROM
      {$db_dti}_inventario_ajustes AS IA
    LEFT JOIN
      {$db_dti}_sucursales  AS S ON (IA.id_sucursal = S.id_sucursal)
    LEFT JOIN
      {$db_ati}_usuarios    AS U  ON (IA.id_usuario = U.id_usuario)
    WHERE
      IA.id_inventario_ajuste = {$id_inventario_ajuste}
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  $data_inventario_ajuste = mysqli_fetch_assoc($query_result);

  $query_productos = "SELECT
      IAP.id_inventario_ajuste_producto,
      IAP.id_inventario_ajuste,
      IAP.id_producto,
      IAP.cantidad,
      IAP.cancelado,
      P.codigo,
      P.precio_venta,
      P.precio_costo_original,
      P.nombre_producto,
      P.unidad,
      P.unidad_entrada,
      P.unidad_salida,
      P.numero_piezas
    FROM
      {$db_dti}_inventario_ajuste_productos AS IAP
    LEFT JOIN
      {$db_dti}_productos AS P ON (IAP.id_producto = P.id_producto)
    WHERE
      IAP.id_inventario_ajuste = {$id_inventario_ajuste}
  ";

  $query_productos_result = mysqli_query($mysqli, $query_productos);
  $num_productos          = mysqli_num_rows($query_productos_result);
  $productos              = [];

  if ($num_productos) :
    while ($producto = mysqli_fetch_assoc($query_productos_result)) :
      array_push($productos, $producto);
    endwhile;
  endif;

  $data_inventario_ajuste['productos'] = $productos;

  return $data_inventario_ajuste;
}

function get_inventory_transfer_folio()
{
  global $mysqli;
  global $db_dti;

  $mark = 'T-';

  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_inventario_transferencias
    WHERE
      YEAR(fecha_creacion)  = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  if (!$query_get_folio_num_rows) return $mark . '00001-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;
    $num_folio_length = strlen($new_num);
    $new_num_folio = '';

    if ($num_folio_length === 1) $new_num_folio = '0000' . $new_num;
    if ($num_folio_length === 2) $new_num_folio = '000' . $new_num;
    if ($num_folio_length === 3) $new_num_folio = '00' . $new_num;
    if ($num_folio_length === 4) $new_num_folio = '0' . $new_num;
    if ($num_folio_length === 5) $new_num_folio = $new_num;

    $new_folio = $mark . $new_num_folio . '-' . $today_year;

    return $new_folio;
  }
}

function get_transfer_request_folio()
{
  global $mysqli;
  global $db_dti;

  $mark = 'ST-';
  $length = 5;

  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_solicitud_transferencias
    WHERE
      YEAR(creado_en)  = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  $new_folio = $mark . str_pad(1, $length, '0', STR_PAD_LEFT) . '-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;

    $new_folio = $mark . str_pad($new_num, $length, '0', STR_PAD_LEFT) . '-' . $today_year;
  }

  return $new_folio;
}

function get_user_cookie_session()
{
  global $_COOKIE;

  $nombre_cookie  = COOKIE_SESSION_COOKIE_NAME;
  $cookie         = $_COOKIE[$nombre_cookie];

  #if (!empty($cookie)) :
  $cookie = decrypt($cookie, MYSQLI_PASSWORD_SECRET);
  $cookie = json_decode($cookie, true);
  #endif;

  //if (empty($cookie)) $cookie = [];

  return $cookie;
}

function set_user_cookie_session(
  $cookie
) {
  $cookie             = encrypt(json_encode($cookie), MYSQLI_PASSWORD_SECRET);
  $nombre_cookie      = COOKIE_SESSION_COOKIE_NAME;
  $tiempo_expiracion  = time() + (60 * 60 * 24 * 365);

  setcookie($nombre_cookie, $cookie, $tiempo_expiracion, BASE_URL);
}

function set_session_user_id(
  $id_usuario
) {
  global $_SESSION;

  $cookie                   = get_user_cookie_session();
  $nombre_session           = COOKIE_SESSION_USER_COOKIE_NAME;

  $fecha                    = date('YmdHis');
  $id_usuario_fecha         = $fecha . '-' . $id_usuario;

  $data_usuario_cookie      = encrypt($id_usuario_fecha, MYSQLI_PASSWORD_SECRET);
  $cookie[$nombre_session]  = $data_usuario_cookie;

  set_user_cookie_session($cookie);

  $_SESSION[$nombre_session] = $data_usuario_cookie;
}

function get_session_user_id_in_cookie()
{
  global $_SESSION;

  $cookie         = get_user_cookie_session();
  $nombre_session = COOKIE_SESSION_USER_COOKIE_NAME;

  $data_cookie    = $cookie[$nombre_session];
  $data_session   = $_SESSION[$nombre_session];

  $need_cookie    = false;

  if (empty($data_session) && empty($data_cookie)) return false;

  if (empty($data_session) && !empty($data_cookie)) :
    $fecha_id_usuario = decrypt($data_cookie, MYSQLI_PASSWORD_SECRET);
  endif;

  if (!empty($data_session) && empty($data_cookie)) :
    $need_cookie      = true;
    $fecha_id_usuario = decrypt($data_session, MYSQLI_PASSWORD_SECRET);
  endif;

  if (!empty($data_session) && !empty($data_cookie)) :
    $fecha_id_usuario = decrypt($data_cookie, MYSQLI_PASSWORD_SECRET);
  endif;

  $data_id_usario     = explode('-', $fecha_id_usuario);
  $id_usuario         = $data_id_usario[1];

  if ($need_cookie) set_session_user_id($id_usuario);

  return $id_usuario;
}

function calculatePricePerBulk(
  $content,
  $price_per_content,
  $quantity
) {
  $price = ($quantity * $price_per_content) / $content;

  return $price;
}

function parsePricePerBulk(
  $price
) {
  return ceil($price);
}

function isMatrizType(
  $branch_office_id
) {
  global $mysqli;
  global $db_dti;

  $matriz_type = 'almacen';

  $query = "SELECT
      id_sucursal
    FROM
      {$db_dti}_sucursales
    WHERE
      id_sucursal = ? AND
      tipo        = ?
    LIMIT 1
  ";

  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('is', $branch_office_id, $matriz_type);
  $stmt->execute();

  $result   = $stmt->get_result();
  $num_rows = $result->num_rows;

  return $num_rows;

  if ($num_rows > 0) return true;

  return false;
}

function getInventoryData(
  $branch_office_id = null
) {
  global $mysqli;
  global $db_dti;

  $productos = [];

  $by_branch_office_id = empty($branch_office_id) ? "1=1" : "I.id_sucursal = {$branch_office_id}";

  $query = "SELECT
      P.codigo,
      P.nombre_producto,
      P.precio_venta,
      (
        SELECT
          SUM(I.stock)
        FROM
          {$db_dti}_inventario AS I
        WHERE
          (I.id_producto = P.id_producto) AND
          ({$by_branch_office_id})
      ) AS existencia
    FROM
      {$db_dti}_productos AS P
    WHERE
      P.status = 'activo'
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($producto = mysqli_fetch_assoc($query_result)) :
      array_push($productos, $producto);
    endwhile;
  endif;

  return $productos;
}

function getSupplierCatalog(
  $value = '',
  $placeholder = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      id_proveedor      AS id,
      nombre_proveedor  AS label
    FROM
      {$db_dti}_proveedores
    ORDER BY
      nombre_proveedor
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function getCFDICatalog(
  $value = '',
  $placeholder = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      id,
      CONCAT(uso_cfdi, ' - ', descripcion) as label
    FROM
      {$db_dti}_uso_cfdi
    ORDER BY
      uso_cfdi
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function get_adjustment_folio()
{
  global $mysqli;
  global $db_dti;

  $mark       = "AJ-";
  $today_date = date('Ymd');
  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_inventario_ajustes
    WHERE
      YEAR(fecha_creacion) = {$today_year}
    ORDER BY
      folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  if (!$query_get_folio_num_rows) return $mark . '00001-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;
    $num_folio_length = strlen($new_num);
    $new_num_folio = '';

    if ($num_folio_length === 1) $new_num_folio = '0000' . $new_num;
    if ($num_folio_length === 2) $new_num_folio = '000' . $new_num;
    if ($num_folio_length === 3) $new_num_folio = '00' . $new_num;
    if ($num_folio_length === 4) $new_num_folio = '0' . $new_num;
    if ($num_folio_length === 5) $new_num_folio = $new_num;

    $new_folio = $mark . $new_num_folio . '-' . $today_year;

    return $new_folio;
  }
}

function obtener_totales_carrito(
  $carrito = [],
  $id_precio,
  $id_cantidad = 'cantidad'
) {
  $subtotal   = 0;
  $total_iva  = 0;
  $total      = 0;

  foreach ($carrito as $key => $row) :
    $data_producto  = parseCartProduct($row, $id_precio, $id_cantidad);
    $importe        = $data_producto->importe;
    $iva            = $data_producto->iva;

    $subtotal       = $subtotal   + $importe;
    $total_iva      = $total_iva  + $iva;
  endforeach;

  $total = $subtotal + $total_iva;

  $totales = new stdClass();

  $totales->subtotal  = $subtotal;
  $totales->total_iva = $total_iva;
  $totales->total     = $total;

  return $totales;
}

function getTotalInCart(
  $cart = [],
  $price_id,
  $quantity_id
) {
  $total = 0;

  foreach ($cart as $key => $item) :
    $price    = $item[$price_id];
    $quantity = $item[$quantity_id];
    $subtotal = $price * $quantity;

    $total = $total + $subtotal;
  endforeach;

  return $total;
}

function get_purchase_folio(
  $id_sucursal
) {
  global $mysqli;
  global $db_dti;

  $mark = "C{$id_sucursal}-";

  $today_date = date('Ymd');
  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_compras
    WHERE
      id_sucursal           = {$id_sucursal} AND
      YEAR(fecha_creacion)  = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  if (!$query_get_folio_num_rows) return $mark . '00001-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;
    $num_folio_length = strlen($new_num);
    $new_num_folio = '';

    if ($num_folio_length === 1) $new_num_folio = '0000' . $new_num;
    if ($num_folio_length === 2) $new_num_folio = '000' . $new_num;
    if ($num_folio_length === 3) $new_num_folio = '00' . $new_num;
    if ($num_folio_length === 4) $new_num_folio = '0' . $new_num;
    if ($num_folio_length === 5) $new_num_folio = $new_num;

    $new_folio = $mark . $new_num_folio . '-' . $today_year;

    return $new_folio;
  }
}

function getPurchaseData(
  $id_compra
) {
  global $mysqli;
  global $db_dti;
  global $db_ati;

  $query = "SELECT
      C.id_compra,
      C.id_usuario,
      C.id_sucursal,
      C.id_proveedor,
      C.folio,
      C.tipo,
      C.observaciones,
      C.iva,
      C.ieps,
      C.subtotal,
      C.total,
      C.status,
      C.fecha_creacion,
      U.nombre_completo AS usuario,
      S.nombre_sucursal AS sucursal,
      S.telefono        AS sucursal_telefono,
      S.direccion       AS sucursal_direccion,
      S.numero_serie,
      DATE_FORMAT(C.fecha_creacion, '%d-%m-%Y') AS ticket_fecha,
      DATE_FORMAT(C.fecha_creacion, '%h:%i %p') AS ticket_hora
    FROM {$db_dti}_compras AS C
      LEFT JOIN {$db_ati}_usuarios    AS U ON (C.id_usuario   = U.id_usuario)
      LEFT JOIN {$db_dti}_sucursales  AS S ON (C.id_sucursal  = S.id_sucursal)
    WHERE
      C.id_compra = {$id_compra}
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  if ($num_rows > 0) :
    $data_compra = mysqli_fetch_assoc($query_result);
    $productos  = [];

    $query_productos = "SELECT
        CP.id_compra_producto,
        CP.id_compra,
        CP.id_producto,
        CP.nombre_producto,
        CP.cantidad,
        CP.precio_costo,
        CP.ieps,
        CP.total,
        CP.cancelado,
        P.unidad,
        P.codigo
      FROM
        {$db_dti}_compra_productos AS CP
      LEFT JOIN
        {$db_dti}_productos AS P ON (CP.id_producto = P.id_producto)
      WHERE
        CP.id_compra = {$id_compra}
    ";

    $query_productos_result = mysqli_query($mysqli, $query_productos);
    $num_productos          = mysqli_num_rows($query_productos_result);

    if ($num_productos > 0) :
      while ($producto = mysqli_fetch_assoc($query_productos_result)) :
        array_push($productos, $producto);
      endwhile;
    endif;

    $data_compra['productos'] = $productos;

    return $data_compra;
  endif;
}

function getPurchaseProductos(
  $purchase_id
) {
  global $mysqli;
  global $db_dti;

  $productos = '';

  $query = "SELECT
      C.id_compra_producto,
      C.id_compra,
      C.id_producto,
      C.nombre_producto,
      C.cantidad,
      C.precio_costo,
      C.total,
      C.cancelado,
      P.unidad,
      P.codigo
    FROM
      {$db_dti}_compra_productos AS C
    LEFT JOIN
      {$db_dti}_productos AS P ON (C.id_producto = P.id_producto)
    WHERE
      C.id_compra = {$purchase_id}
  ";

  $query_result   = mysqli_query($mysqli, $query);
  $num_productos  = mysqli_num_rows($query_result);

  if ($num_productos > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : 'pzs.';

      $productos .= '
        <tr>
          <td>' . $row['codigo'] . '</td>
          <td>' . $row['nombre_producto'] . '</td>
          <td>' . (int) $row['cantidad'] . ' ' . $unit_type . '</td>
        </tr>
      ';
    endwhile;
  endif;

  return $productos;
}


function getCategoriesCatalog(
  $value = '',
  $placeholder = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      id_categoria  AS id,
      categoria     AS label
    FROM
      {$db_dti}_categorias
    ORDER BY
      categoria
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function getBrandsCatalog(
  $value = '',
  $placeholder = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      id_marca  AS id,
      marca     AS label
    FROM
      {$db_dti}_marcas
    ORDER BY
      marca
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function getSuppliersCatalog(
  $value = '',
  $placeholder = '--Seleccionar--'
) {
  global $mysqli;
  global $db_dti;

  $response = '<option value="">' . $placeholder . '</option>';

  $query = "SELECT
      id_proveedor      AS id,
      nombre_proveedor  AS label
    FROM
      {$db_dti}_proveedores
    ORDER BY
      nombre_proveedor
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $id       = $row['id'];
      $label    = $row['label'];
      $selected = $id == $value ? 'selected' : '';

      $response .= '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
    endwhile;
  endif;

  return $response;
}

function parseProductPrice(
  $data_producto,
  $cantidad,
  $id_precio
) {
  $precio           = $data_producto['precio_original'] ?? $data_producto[$id_precio];
  $cantidad         = doubleval($cantidad);

  $cantidad_moyoreo = $data_producto['cantidad_mayoreo'];
  $precio_moyoreo   = $data_producto['precio_mayoreo'];

  if ($cantidad >= $cantidad_moyoreo) $data_producto['price_to_use'] = $precio_moyoreo;
  if ($cantidad < $cantidad_moyoreo)  $data_producto['price_to_use'] = $precio;

  return $data_producto;
}

function getTipoCambio()
{
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      id_configuracion,
      configuracion,
      slug,
      valor,
      tipo
    FROM
      {$db_dti}_configuraciones
    WHERE
      slug = 'tipo_cambio'
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $setting      = mysqli_fetch_assoc($query_result);

  return doubleval($setting['valor']);
}

function parseCartProduct(
  $data_producto,
  $id_precio,
  $id_cantidad = 'cantidad'
) {
  $id_producto    = $data_producto['id_producto'];
  $codigo         = $data_producto['codigo'];
  $producto       = $data_producto['nombre_producto'];
  $cantidad       = $data_producto[$id_cantidad];
  $aplica_iva     = $data_producto['aplica_iva'];
  $precio         = $aplica_iva === 'si' ? ($data_producto[$id_precio] * 0.84) : $data_producto[$id_precio];
  $importe        = $cantidad * $precio;
  $unidad         = $data_producto['unidad'] === 'A granel' ? 'kg.' : 'pzs.';
  $iva            = $aplica_iva === 'si' ? (($data_producto['precio_venta'] * 0.16) * $cantidad) : 0;

  $data_producto = new stdClass();
  $data_producto->id_producto = $id_producto;
  $data_producto->codigo = $codigo;
  $data_producto->producto = $producto;
  $data_producto->cantidad = $cantidad;
  $data_producto->aplica_iva = $aplica_iva;
  $data_producto->precio = $precio;
  $data_producto->importe = $importe;
  $data_producto->unidad = $unidad;
  $data_producto->iva = $iva;

  return $data_producto;
}

function get_id_usuario()
{
  $id_usuario = get_session_user_id_in_cookie();
  return $id_usuario;
}

function getPriceWithoutIVA(
  $price
) {
  return $price / 1.16;
}

function getPriceIVA(
  $price
) {
  $price = (16 * $price / 100);
  return $price;
}

function createInvoiceOfReceiptFolio()
{
  global $mysqli;
  global $db_dti;

  $mark = "";

  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_facturas_ingreso
    WHERE
      YEAR(fecha_emision) = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  if (!$query_get_folio_num_rows) return $mark . '0001-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;
    $num_folio_length = strlen($new_num);
    $new_num_folio = '';

    if ($num_folio_length === 1) $new_num_folio = '000' . $new_num;
    if ($num_folio_length === 2) $new_num_folio = '00' . $new_num;
    if ($num_folio_length === 3) $new_num_folio = '0' . $new_num;
    if ($num_folio_length === 4) $new_num_folio = $new_num;

    $new_folio = $mark . $new_num_folio . '-' . $today_year;

    return $new_folio;
  }
}

function createPaymentInvoiceFolio()
{
  global $mysqli;
  global $db_dti;

  $mark   = "";
  $length = 6;

  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_facturas_p_pagos
    WHERE
      YEAR(fecha) = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  //if (!$query_get_folio_num_rows) return $mark . '0001-' . $today_year;

  $new_folio = $mark . str_pad(1, $length, '0', STR_PAD_LEFT) . '-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;

    $new_folio = $mark . str_pad($new_num, $length, '0', STR_PAD_LEFT) . '-' . $today_year;
  }

  return $new_folio;
}

function createInvoicePaymentTypeFolio()
{
  global $mysqli;
  global $db_dti;

  $mark   = "";
  $length = 6;

  $today_year = date('Y');

  $query_get_folio = "SELECT
      folio
    FROM
      {$db_dti}_facturas_p
    WHERE
      YEAR(fecha) = {$today_year}
    ORDER BY folio
    DESC
    LIMIT 1
  ";

  $query_get_folio_result = mysqli_query($mysqli, $query_get_folio);
  $query_get_folio_num_rows = mysqli_num_rows($query_get_folio_result);

  //if (!$query_get_folio_num_rows) return $mark . '0001-' . $today_year;

  $new_folio = $mark . str_pad(1, $length, '0', STR_PAD_LEFT) . '-' . $today_year;

  if ($query_get_folio_num_rows) {
    $quote_data = mysqli_fetch_assoc($query_get_folio_result);
    $folio = $quote_data['folio'];

    $folio = str_replace($mark, '', $folio);
    $folio = str_replace('-' . $today_year, '', $folio);
    $folio = ltrim($folio, '0');

    $new_num = intval($folio) + 1;

    $new_folio = $mark . str_pad($new_num, $length, '0', STR_PAD_LEFT) . '-' . $today_year;
  }

  return $new_folio;
}

function sendInvoice(
  $mail,
  $sender,
  $customer,
  $xml,
  $pdf
) {
  error_log("MLFROMINDEX: " . json_encode($_REQUEST));
  $mensaje = "<h4>{$customer->name}</h4>";
  $mensaje .= "PRESENTE<br /><br />";
  $mensaje .= "Por medio de la presente le informamos que {$sender->name} le ha enviado un nuevo Comprobante Fiscal Digital.<br/><br/><br/>";
  $mensaje .= "<strong>Atentamente</strong><br/>";
  $mensaje .= "{$sender->name}<br/>";
  //$mensaje .= "facturacion@cocinaspaal.com<br/>";
  $mensaje .= ADM_EMAIL . "<br/>";

  ob_start();
  require BASE_PATH . "/data/lib/email-templates/default.php";
  $message = ob_get_clean();

  $config = [
    'mail' => $mail,
    'from' => [
      'username' => PHPMAILER_SALES_EMAIL,
      'password' => PHPMAILER_SALES_PASSWORD,
      'name' => ADM_NAME
    ],
    'to' => $customer->emails,
    'subject' => "Comprobante Fiscal Digital {$customer->rfc}",
    'message' => $message,
    'attachment' => [$xml, $pdf]
  ];

  $request = sendEmail($config);

  return $request;
}

function insertKardexData($data = [
  "userId"      => "",
  "branchId"    => "",
  "productId"   => "",
  "productName" => "",
  "quantity"    => "",
  "log"         => "",
  "existence"   => "",
])
{
  global $mysqli;
  global $db_dti;

  $query = "INSERT INTO {$db_dti}_kardex (
      id_usuario,
      id_sucursal,
      id_producto,
      nombre_producto,
      cantidad,
      accion,
      existencia
    ) VALUES (
      ?, ?, ?, ?, ?, ?, ?
    )
  ";

  $stmt = $mysqli->prepare($query);

  $stmt->bind_param(
    "iiissss",
    $data["userId"],
    $data["branchId"],
    $data["productId"],
    $data["productName"],
    $data["quantity"],
    $data["log"],
    $data["existence"]
  );

  try {
    $result = $stmt->execute();
    $stmt->close();

    if (!$result) return false;
    if ($result)  return true;
  } catch (Exception $e) {
    return false;
  }
}

function addKardexLog(
  $productId,
  $branchId,
  $quantity,
  $log
) {
  $userId       = get_id_usuario();
  $productData  = getBranchOfficeProductData($branchId, $productId);

  $kardexData = [
    "userId"      => $userId,
    "branchId"    => $branchId,
    "productId"   => $productId,
    "productName" => $productData["nombre_producto"],
    "quantity"    => $quantity,
    "log"         => $log,
    "existence"   => $productData["stock"],
  ];

  $result = insertKardexData($kardexData);

  return $result;
}

function parseStrCurrency(
  $money
) {
  $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
  $onlyNumbersString = preg_replace('/([^0-9.])/i', '', $money);

  return (float) $onlyNumbersString;

  $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

  $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
  $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

  return (float) str_replace(',', '.', $removedThousandSeparator);
}

function getProductTypeByName($name)
{
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $slug = createSlug($name);

  $query = "SELECT
      *
    FROM
      {$db_dti}_tipos
    WHERE
      slug = '{$slug}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $type = mysqli_fetch_assoc($result);

  return $type;
}

function addProductType($name)
{
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);
  $slug = createSlug($name);

  $query = "INSERT INTO {$db_dti}_tipos (nombre, slug) VALUES ('{$name}', '{$slug}')";

  $result = mysqli_query($mysqli, $query);

  if (!$result) return false;

  return mysqli_insert_id($mysqli);
}

function verifyProductType($name)
{
  $typeData = getProductTypeByName($name);

  if ($typeData) return $typeData["id_tipo"];

  if (!$typeData) {
    $typeId = addProductType($name);

    return $typeId;
  }
}

function getBrandByName($name)
{
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);

  $query = "SELECT
      *
    FROM
      {$db_dti}_marcas
    WHERE
      marca = '{$name}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $brand = mysqli_fetch_assoc($result);

  return $brand;
}

function addBrand($name)
{
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);

  $query = "INSERT INTO {$db_dti}_marcas (marca) VALUES ('{$name}')";

  $result = mysqli_query($mysqli, $query);

  if (!$result) return false;

  return mysqli_insert_id($mysqli);
}

function verifyBrand($name)
{
  $brandData = getBrandByName($name);

  if ($brandData) return $brandData["id_marca"];

  if (!$brandData) {
    $brandId = addBrand($name);

    return $brandId;
  }
}

function getBrandLineByName($brandId, $name)
{
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);

  $query = "SELECT
      *
    FROM
      {$db_dti}_categorias
    WHERE
      id_marca  = {$brandId} AND
      categoria = '{$name}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $brandLine = mysqli_fetch_assoc($result);

  return $brandLine;
}

function addBrandLine($brandId, $name)
{
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);

  $query = "INSERT INTO {$db_dti}_categorias (id_marca, categoria) VALUES ({$brandId}, '{$name}')";

  $result = mysqli_query($mysqli, $query);

  if (!$result) return false;

  return mysqli_insert_id($mysqli);
}

function verifyBrandLine($brandId, $name)
{
  $brandLineData = getBrandLineByName($brandId, $name);

  if ($brandLineData) return $brandLineData["id_categoria"];

  if (!$brandLineData) {
    $brandLineId = addBrandLine($brandId, $name);

    return $brandLineId;
  }
}

function getBrandLineFamilyByName($brandLineId, $name)
{
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);

  $query = "SELECT
      *
    FROM
      {$db_dti}_categoria_familias
    WHERE
      id_categoria = {$brandLineId} AND
      familia      = '{$name}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $brandFamily = mysqli_fetch_assoc($result);

  return $brandFamily;
}

function addBrandLineFamily($brandLineId, $name)
{
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);

  $query = "INSERT INTO {$db_dti}_categoria_familias (id_categoria, familia) VALUES ({$brandLineId}, '{$name}')";

  $result = mysqli_query($mysqli, $query);

  if (!$result) return false;

  return mysqli_insert_id($mysqli);
}

function verifyBrandLineFamily($brandLineId, $name)
{
  $brandFamilyData = getBrandLineFamilyByName($brandLineId, $name);

  if ($brandFamilyData) return $brandFamilyData["id_categoria_familia"];

  if (!$brandFamilyData) {
    $brandFamilyId = addBrandLineFamily($brandLineId, $name);

    return $brandFamilyId;
  }
}

function getSupplierByName(
  $name
) {
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);

  $query = "SELECT
      *
    FROM
      {$db_dti}_proveedores
    WHERE
      nombre_proveedor  = '{$name}' OR
      nombre_comercial  = '{$name}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $supplier = mysqli_fetch_assoc($result);

  return $supplier;
}

function addSupplier(
  $name
) {
  global $mysqli;
  global $db_dti;

  $name = trim($name);
  $name = strtoupper($name);

  $query = "INSERT INTO {$db_dti}_proveedores (nombre_proveedor, nombre_comercial) VALUES ('{$name}', '{$name}')";

  $result = mysqli_query($mysqli, $query);

  if (!$result) return false;

  return mysqli_insert_id($mysqli);
}

function verifySupplier(
  $name
) {
  $supplierData = getSupplierByName($name);

  if ($supplierData) return $supplierData["id_proveedor"];

  if (!$supplierData) {
    $supplierId = addSupplier($name);

    return $supplierId;
  }
}

function getUnitKeyIdByKey(
  $key
) {
  global $mysqli;
  global $db_dti;

  $key = trim($key);
  $key = strtoupper($key);

  $query = "SELECT
      id_clave_unidad
    FROM
      {$db_dti}_clave_unidades
    WHERE
      clave = '{$key}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $unitData = mysqli_fetch_assoc($result);

  return $unitData["id_clave_unidad"];
}

function getSatKeyIdByKey(
  $key
) {
  global $mysqli;
  global $db_dti;

  $key = trim($key);
  $key = strtoupper($key);

  $query = "SELECT
      id_clave_producto_servicio
    FROM
      {$db_dti}_clave_producto_servicios
    WHERE
      clave = '{$key}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $satData = mysqli_fetch_assoc($result);

  return $satData["id_clave_producto_servicio"];
}

function getProductBySku(
  $key
) {
  global $mysqli;
  global $db_dti;

  $key = trim($key);
  $key = strtoupper($key);

  $query = "SELECT
      *
    FROM
      {$db_dti}_productos
    WHERE
      codigo = '{$key}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $productData = mysqli_fetch_assoc($result);

  return $productData;
}

function getProductSerialNumbers($productId, $branchId, $status = ["disponible"])
{
  global $mysqli;
  global $db_dti;

  // {$db_dti}_producto_numeros_serie
  /* 
  	1	id_producto_numero_serie Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_producto Índice	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	folio_compra	varchar(40)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	4	folio_venta	varchar(40)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	5	numero_serie Índice	text	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	6	status	enum('disponible', 'vendido')	utf8mb4_bin		No	disponible			Cambiar Cambiar	Eliminar Eliminar	
	7	fecha_creacion	datetime			No	current_timestamp()			Cambiar Cambiar	Eliminar Eliminar	
	8	id_sucursal	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	

  */

  $byStatus = "1=1";
  $byStatus = "status IN ('" . implode("','", $status) . "')";

  // if ($status == "disponible")                    $byStatus = "status = 'disponible'";
  // if ($status == "vendido")                       $byStatus = "status = 'vendido'";
  // if ($status == "reservado-para-transferencia")  $byStatus = "status = 'reservado-para-transferencia'";
  // if ($status == "pendiente-de-ajuste")           $byStatus = "status = 'pendiente-de-ajuste'";
  // if ($status == "all")                           $byStatus = "1=1";

  $serialNumbers = [];

  $query = "SELECT
      id_producto_numero_serie,
      id_producto,
      folio_compra,
      folio_venta,
      numero_serie,
      status,
      fecha_creacion,
      id_sucursal
    FROM
      {$db_dti}_producto_numeros_serie
    WHERE
      id_producto = {$productId} AND
      id_sucursal = {$branchId}  AND
      {$byStatus}
    ORDER BY
      fecha_creacion DESC
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return $serialNumbers;

  while ($row = mysqli_fetch_assoc($result)) {
    array_push($serialNumbers, $row);
  }

  return $serialNumbers;
}

function getProductSerialNumbersForCatalog($productId, $branchId, $value = "")
{
  $serialNumbers = getProductSerialNumbers($productId, $branchId);

  $response = '<option value="">--Seleccionar--</option>';

  foreach ($serialNumbers as $serialNumberData) {
    $serialNumber = $serialNumberData['numero_serie'];
    $selected     = $serialNumber == $value ? 'selected' : '';

    $response .= "<option value='{$serialNumber}' {$selected}>{$serialNumber}</option>";
  }

  return $response;
}

function getSaleInvoiceBySaleIdAndType($saleId, $type)
{
  global $mysqli;
  global $db_dti;

  $table  = "";
  $join   = "";

  if ($type == "ingreso")       $join = "LEFT JOIN {$db_dti}_facturas                 AS TF ON (VF.id_factura = TF.id_factura)";
  if ($type == "anticipo")      $join = "LEFT JOIN {$db_dti}_facturas_anticipo_compra AS TF ON (VF.id_factura = TF.id_factura)";
  if ($type == "nota_credito")  $join = "LEFT JOIN {$db_dti}_facturas_nota_credito    AS TF ON (VF.id_factura = TF.id_factura)";

  $query    = "SELECT TF.* FROM {$db_dti}_venta_facturas AS VF {$join} WHERE VF.id_venta = {$saleId} AND cancelado = 0 LIMIT 1";
  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $invoiceData = mysqli_fetch_assoc($result);

  return $invoiceData;
}

function getIncomeInvoiceBySaleId($saleId)
{
  global $mysqli;
  global $db_dti;

  $query    = "SELECT * FROM {$db_dti}_facturas WHERE id_venta = {$saleId} AND cancelado = 0 LIMIT 1";
  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $invoiceData = mysqli_fetch_assoc($result);

  return $invoiceData;
}

function getAdvanceInvoiceBySaleId($saleId)
{
  global $mysqli;
  global $db_dti;

  $query    = "SELECT * FROM {$db_dti}_facturas_anticipo_compra WHERE id_venta = {$saleId} AND cancelado = 0 LIMIT 1";
  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $invoiceData = mysqli_fetch_assoc($result);

  return $invoiceData;
}

function getCreditNoteInvoiceBySaleId($saleId)
{
  global $mysqli;
  global $db_dti;

  $query    = "SELECT * FROM {$db_dti}_facturas_nota_credito WHERE id_venta = {$saleId} AND cancelado = 0 LIMIT 1";
  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $invoiceData = mysqli_fetch_assoc($result);

  return $invoiceData;
}

function updateSalePayedStatus($saleId)
{
  global $mysqli;
  global $db_dti;
  global $_POST;

  $totalAmount = getSaleTotalById($saleId);

  $query      = "SELECT SUM(monto_total) AS monto_total FROM {$db_dti}_venta_pagos WHERE id_venta = {$saleId}";
  $result     = mysqli_query($mysqli, $query);
  $data       = mysqli_fetch_assoc($result);
  $totalPaid  = $data['monto_total'] ?? 0;

  if ($totalPaid >= $totalAmount) {
    $_POST = [
      'pagado'  => 'si'
    ];

    useUpdateByPost([
      'table_name' => "{$db_dti}_ventas",
      'conditions' => [['id_venta', $saleId]]
    ]);
  }

  if ($totalPaid < $totalAmount) {
    $_POST = [
      'pagado'  => 'no'
    ];

    useUpdateByPost([
      'table_name' => "{$db_dti}_ventas",
      'conditions' => [['id_venta', $saleId]]
    ]);
  }
}

function parseCurrency(
  $money
) {
  $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
  $onlyNumbersString = preg_replace('/([^0-9.])/i', '', $money);

  return (float) $onlyNumbersString;

  $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

  $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
  $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

  return (float) str_replace(',', '.', $removedThousandSeparator);
}

function getCustomerById(
  $customerId
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      *
    FROM
      {$db_dti}_clientes
    WHERE
      id_cliente = {$customerId}
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $customerData = mysqli_fetch_assoc($result);

  return $customerData;
}

function getSaleTotalById($saleId)
{
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      SUM(total) AS total
    FROM
      {$db_dti}_venta_productos
    WHERE
      id_venta  = {$saleId} AND
      cancelado = 'no'
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);
  $total  = $data['total'] ?? 0;

  // Obtner el redondeo de la venta
  $query = "SELECT
      SUM(V.redondeo) AS redondeo
    FROM
      {$db_dti}_ventas AS V
    WHERE
      V.forma_pago  = 'credito'     AND
      V.pagado      = 'no'          AND
      V.status      = 'activo'      AND
      V.id_venta    = {$saleId}
  ";

  $result   = mysqli_query($mysqli, $query);
  $data     = mysqli_fetch_array($result);
  $redondeo = $data['redondeo'] ?? 0;

  $total += $redondeo;


  return doubleval($total);
}

function getSaleTotalPaidById($saleId)
{
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      SUM(monto_total) AS total_paid
    FROM
      {$db_dti}_venta_pagos
    WHERE
      id_venta = {$saleId}
  ";

  $result     = mysqli_query($mysqli, $query);
  $data       = mysqli_fetch_assoc($result);
  $totalPaid  = $data['total_paid'] ?? 0;

  return doubleval($totalPaid);
}

function getCreditSaleTotalByCustomerId(
  $customerId,
  $branchId = 0
) {
  global $mysqli;
  global $db_dti;

  $byranchId = $branchId ? "V.id_sucursal = {$branchId}" : "1=1";

  $query = "SELECT
      SUM(VPR.total) AS balance
    FROM
      {$db_dti}_venta_productos AS VPR
    LEFT JOIN
      {$db_dti}_ventas AS V ON (VPR.id_venta = V.id_venta)
    WHERE
      V.id_cliente  = {$customerId} AND
      V.forma_pago  = 'credito'     AND
      V.pagado      = 'no'          AND
      V.status      = 'activo'      AND
      VPR.cancelado = 'no' AND
      {$byranchId}
  ";

  $result   = mysqli_query($mysqli, $query);
  $data     = mysqli_fetch_array($result);
  $balance  = $data['balance'] ?? 0;

  // Sumar el redondeo de la venta
  $query = "SELECT
      SUM(redondeo) AS redondeo
    FROM
      {$db_dti}_ventas AS V
    WHERE
      V.id_cliente  = {$customerId} AND
      V.forma_pago  = 'credito'     AND
      V.pagado      = 'no'          AND
      V.status      = 'activo'     AND
      {$byranchId}
  ";

  $result   = mysqli_query($mysqli, $query);
  $data     = mysqli_fetch_array($result);
  $redondeo = $data['redondeo'] ?? 0;

  $balance += $redondeo;

  return doubleval($balance);
}

function getTotalBalancePaidByCustomerId(
  $customerId,
  $branchId = 0
) {
  global $mysqli;
  global $db_dti;

  $byranchId = $branchId ? "V.id_sucursal = {$branchId}" : "1=1";

  $query = "SELECT
      SUM(VP.monto_total) AS total_paid
    FROM
      {$db_dti}_venta_pagos AS VP
    LEFT JOIN
      {$db_dti}_ventas AS V ON (VP.id_venta = V.id_venta)
    WHERE
      V.id_cliente  = {$customerId} AND
      V.forma_pago  = 'credito'     AND
      V.pagado      = 'no'          AND
      V.status      = 'activo'      AND
      {$byranchId}
  ";

  $result     = mysqli_query($mysqli, $query);
  $data       = mysqli_fetch_array($result);
  $totalPaid  = $data['total_paid'] ?? 0;

  return doubleval($totalPaid);
}

// nombre de función para obtener la primera venta a crédito sin pagar
function getFirstUnpaidCreditSaleByCustomerId(
  $customerId
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      *
    FROM
      {$db_dti}_ventas
    WHERE
      id_cliente  = {$customerId} AND
      forma_pago  = 'credito'     AND
      pagado      = 'no'          AND
      status      = 'activo'
    ORDER BY
      id_venta
    ASC
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $saleData = mysqli_fetch_assoc($result);

  return $saleData;
}

function canBuyWithCredit(
  $customerId,
  $newSaleTotal = 0
) {
  $response = [
    "status" => "error",
    "message" => "No se pudo validar el crédito del cliente"
  ];

  /**
   * Condiciones para invalidar el crédito
   * 1.- El cliente excede su límite de crédito
   * 2.- El cliente tiene un plazo de crédito y aún no ha pagado la venta que cumple con ese plazo (El plazo se mide en días)
   */

  // Obtener los datos del cliente
  $customerData = getCustomerById($customerId);

  if (!$customerData) return $response;

  // Obtener el total de las ventas a crédito activas y no pagadas
  $creditSaleTotal = getCreditSaleTotalByCustomerId($customerId);

  // Obtener el total pagado
  $totalPaid = getTotalBalancePaidByCustomerId($customerId);

  // Obtener el saldo pendiente
  $balance = $creditSaleTotal - $totalPaid;

  // Validar que el cliente tiene crédito disponible
  $creditLimit = doubleval($customerData['limite_credito'] ?? 0);

  if ($creditLimit <= 0) {
    $response['message'] = "El cliente no tiene límite de crédito asignado";
    return $response;
  }

  if ($balance >= $creditLimit) {
    $response['message'] = "El cliente no tiene crédito disponible";
    return $response;
  }

  if (($balance + $newSaleTotal) > $creditLimit) {
    $response['message'] = "El cliente excede su límite de crédito, su crédito disponible es de $" . number_format($creditLimit - $balance, DECIMALS_CURRENCY);
    return $response;
  }

  // Obtener la primera venta a crédito sin pagar (fecha_creacion)
  $firstUnpaidCreditSale = getFirstUnpaidCreditSaleByCustomerId($customerId);

  if ($firstUnpaidCreditSale) {
    // Validar que el cliente no tenga ventas vencidas
    $creditDays = intval($customerData['limite_credito_plazo'] ?? 0);
    $dateNow    = new DateTime();
    $dateSale   = new DateTime($firstUnpaidCreditSale['fecha_creacion'] ?? '0000-00-00');
    $dateSale->modify("+{$creditDays} days");

    if ($dateNow >= $dateSale) {
      $response['message'] = "El cliente tiene ventas vencidas, no puede realizar una nueva venta a crédito";
      return $response;
    }
  }

  $response['status']  = "success";
  $response['message'] = "El cliente puede realizar la venta a crédito";

  return $response;
}

function getSalePaymentByMd5Id($paymentId)
{
  global $mysqli;
  global $db_dti;

  $paymentId = mysqli_real_escape_string($mysqli, $paymentId);

  /* 
  1	id_venta_pago Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_venta	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	efectivo_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	4	cheque_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	5	cheque_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	6	transferencia_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	7	transferencia_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	8	tarjeta_credito_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	9	tarjeta_credito_numero	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	10	tarjeta_debito_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	11	tarjeta_debito_numero	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	12	monto_total	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	13	fecha_hora	datetime			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	14	notas	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
  15 folio  varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar
  16	id_usuario	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	17	id_sucursal	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
  */

  $query = "SELECT
      *
    FROM
      {$db_dti}_venta_pagos
    WHERE
      MD5(id_venta_pago) = '{$paymentId}'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $paymentData = mysqli_fetch_assoc($result);

  return $paymentData;
}

function getSaleById($saleId)
{
  global $mysqli;
  global $db_dti;

  /* 
  	1	id_venta Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_usuario	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	id_sucursal	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	4	id_cliente	int(11)			No	1			Cambiar Cambiar	Eliminar Eliminar	
	5	id_cfdi	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	6	id_direccion	int(11)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	7	id_corte_caja	int(11)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	8	folio	varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	9	tipo	enum('incremento', 'decremento')	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	10	observaciones	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	11	folio_cotizacion	varchar(100)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	12	subtotal	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	13	iva	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	14	redondeo	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	15	total	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	16	pago_con	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	17	efectivo	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	18	cheque	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	19	cheque_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	20	transferencia	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	21	transferencia_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	22	tarjeta_credito	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	23	tarjeta_credito_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	24	tarjeta_credito_numero	varchar(100)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	25	tarjeta_debito	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	26	tarjeta_debito_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	27	tarjeta_debito_numero	varchar(100)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	28	cambio	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	29	corte	enum('si', 'no')	utf8mb4_bin		No	no			Cambiar Cambiar	Eliminar Eliminar	
	30	status	enum('activo', 'cancelado')	utf8mb4_bin		No	activo			Cambiar Cambiar	Eliminar Eliminar	
	31	fecha_creacion	datetime			No	current_timestamp()			Cambiar Cambiar	Eliminar Eliminar	
	32	tipo_productos	enum('equipo', 'llantas', 'rines', 'refacciones', ...	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	33	forma_pago	enum('contado', 'credito')	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	34	pagado	enum('si', 'no')	utf8mb4_bin		No	si			Cambiar Cambiar	Eliminar Eliminar	
  */

  $query = "SELECT
      *
    FROM
      {$db_dti}_ventas
    WHERE
      id_venta = {$saleId}
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $saleData = mysqli_fetch_assoc($result);

  return $saleData;
}

function createSalePaymentFolio($saleId)
{
  global $mysqli;
  global $db_dti;

  $currentYear = date('Y');

  // Create a short hash from the sale ID (3 characters)
  $saleHash = substr(md5($saleId), 0, 3);
  $saleHash = strtoupper($saleHash);

  $query = "SELECT
      folio
    FROM
      {$db_dti}_venta_pagos
    WHERE
      id_venta = {$saleId} AND
      folio LIKE 'P{$saleHash}-%{$currentYear}'
    ORDER BY
      folio DESC
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  $nextNumber = 1;

  if ($numRows > 0) {
    $data = mysqli_fetch_assoc($result);
    $lastFolio = $data['folio'];

    // Extract the number part from the folio (between P{hash}- and -{currentYear})
    $pattern = "/P{$saleHash}-(\d+)-{$currentYear}/";
    if (preg_match($pattern, $lastFolio, $matches)) {
      $nextNumber = intval($matches[1]) + 1;
    }
  }

  // Format the number with leading zeros (5 digits)
  $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

  // Create new folio: P{saleHash}-{number}-{currentYear}
  $newFolio = "P{$saleHash}-{$formattedNumber}-{$currentYear}";

  return $newFolio;
}

function getSaleTotalsBySaleId($saleId)
{
  global $mysqli;
  global $db_dti;

  $saleData = getSaleById($saleId);

  $query = "SELECT
      SUM(subtotal)           AS subtotal,
      SUM((iva * cantidad))   AS iva,
      SUM((ieps * cantidad))  AS ieps,
      SUM(total)              AS totalProducts
    FROM
      {$db_dti}_venta_productos
    WHERE
      id_venta  = {$saleId} AND
      cancelado = 'no'
  ";

  $result = mysqli_query($mysqli, $query);
  $data = mysqli_fetch_assoc($result);

  $iva            = doubleval($data['iva'] ?? 0);
  $ieps           = doubleval($data['ieps'] ?? 0);
  $subtotal       = doubleval($data['subtotal'] ?? 0);
  $totalProducts  = $data['totalProducts'];

  if ($totalProducts > 0) $totalProducts = doubleval($data['totalProducts'] ?? 0) + doubleval($saleData['redondeo'] ?? 0);

  return [
    "ieps"      => round($ieps, DECIMALS_CURRENCY),
    "iva"       => round($iva, DECIMALS_CURRENCY),
    "subtotal"  => round($subtotal, DECIMALS_CURRENCY),
    "total"     => round($totalProducts, DECIMALS_CURRENCY)
  ];
}

function format_number(
  $number,
  $limit_decimals = 2
) {
  // Si el número tiene decimales muy pequeños, usar solo 1 decimal por defecto
  if ($limit_decimals == 0) {
    $limit_decimals = 1;
  }

  // Redondear a la cantidad de decimales especificada
  $rounded_number = round($number, $limit_decimals);

  // Si el resultado es un entero (ej: 1.0), mostrar sin decimales
  if ($rounded_number == intval($rounded_number)) {
    return number_format(intval($rounded_number));
  }

  // Formatear con los decimales especificados, eliminando ceros innecesarios
  return rtrim(rtrim(number_format($rounded_number, $limit_decimals), '0'), '.');
}

function parseDateToSpanish(
  $date,
  $withTime = false
) {
  setlocale(LC_TIME, 'es_ES.UTF-8');
  date_default_timezone_set('America/Mexico_City');

  $timestamp = strtotime($date);

  if ($withTime) {
    return strftime('%d de %B de %Y, %H:%M:%S', $timestamp);
  } else {
    return strftime('%d de %B de %Y', $timestamp);
  }
}

function getProductSalePricesArray(
  $productId
) {
  $prices = [];

  $data = getProductDataById($productId);

  if ($data) $prices = [$data['precio_venta'], $data['precio_venta2'], $data['precio_venta3']];

  return $prices;
}

function getTypesCatalog($value = "", $label = "--Seleccionar--")
{
  require_once __DIR__ . "/../data/lib/helpers/types.helper.php";

  $typesModel   = new TypesHelper();
  $typesResult  = $typesModel->getAll();
  $types        = $typesResult->data["rows"];

  $response = "<option value=''>{$label}</option>";

  foreach ($types as $type) {
    /**
     * @var TypesHelper $type
     */
    $selected = $type->getId() == $value ? 'selected' : '';

    $response .= "<option data-tangible='{$type->getTangible()}' data-isAdvance='{$type->getIsAdvance()}' data-isCreditNote='{$type->getIsCreditNote()}' value='{$type->getId()}' {$selected}>{$type->getName()}</option>";
  }

  return $response;
}

function getCreditoSaleTotalsByBranchIdAndDate(
  $branchId,
  $date,
  $extraParams = [
    "dateMode" => "%Y-%m"
  ]
) {
  global $mysqli;
  global $db_dti;

  $defaultExtraParams = [
    "dateMode" => "%Y-%m"
  ];

  $extraParams = array_merge($defaultExtraParams, $extraParams);

  $query = "SELECT
      SUM(VP.efectivo_monto) AS efectivo,
      SUM(VP.cheque_monto) AS cheque,
      SUM(VP.transferencia_monto) AS transferencia,
      SUM(VP.tarjeta_credito_monto) AS tarjeta_credito,
      SUM(VP.tarjeta_debito_monto) AS tarjeta_debito,
      SUM(VP.monto_total) AS total
    FROM
      {$db_dti}_venta_pagos AS VP
    INNER JOIN
      {$db_dti}_ventas AS V ON (V.id_venta = VP.id_venta)
    WHERE
      VP.id_sucursal = {$branchId} AND
      DATE_FORMAT(VP.fecha_hora, '{$extraParams["dateMode"]}') = '{$date}' AND
      V.forma_pago = 'credito' AND
      V.status = 'activo'
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);

  $efectivo         = doubleval($data['efectivo']         ?? 0);
  $cheque           = doubleval($data['cheque']           ?? 0);
  $transferencia    = doubleval($data['transferencia']    ?? 0);
  $tarjeta_credito  = doubleval($data['tarjeta_credito']  ?? 0);
  $tarjeta_debito   = doubleval($data['tarjeta_debito']   ?? 0);
  $total            = doubleval($data['total']            ?? 0);

  return [
    "efectivo"         => $efectivo,
    "cheque"           => $cheque,
    "transferencia"    => $transferencia,
    "tarjeta_credito"  => $tarjeta_credito,
    "tarjeta_debito"   => $tarjeta_debito,
    "total"            => $total
  ];
}

function getContadoSaleTotalsByBranchIdAndDate(
  $branchId,
  $date,
  $extraParams = [
    "dateMode" => "%Y-%m"
  ]
) {
  global $mysqli;
  global $db_dti;

  $defaultExtraParams = [
    "dateMode" => "%Y-%m"
  ];

  $extraParams = array_merge($defaultExtraParams, $extraParams);

  $query = "SELECT
      SUM(efectivo) AS efectivo,
      SUM(cheque) AS cheque,
      SUM(transferencia) AS transferencia,
      SUM(tarjeta_credito) AS tarjeta_credito,
      SUM(tarjeta_debito) AS tarjeta_debito,
      SUM(iva) AS iva,
      SUM(ieps) AS ieps,
      SUM(subtotal) AS subtotal,
      SUM(redondeo) AS redondeo,
      SUM(total) AS total
    FROM
      {$db_dti}_ventas
    WHERE
      id_sucursal           = {$branchId} AND
      DATE_FORMAT(fecha_creacion, '{$extraParams["dateMode"]}') = '{$date}'   AND
      forma_pago            = 'contado'   AND
      status                = 'activo'
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);

  $efectivo         = doubleval($data['efectivo']         ?? 0);
  $cheque           = doubleval($data['cheque']           ?? 0);
  $transferencia    = doubleval($data['transferencia']    ?? 0);
  $tarjeta_credito  = doubleval($data['tarjeta_credito']  ?? 0);
  $tarjeta_debito   = doubleval($data['tarjeta_debito']   ?? 0);
  $iva              = doubleval($data['iva']              ?? 0);
  $ieps             = doubleval($data['ieps']             ?? 0);
  $subtotal         = doubleval($data['subtotal']         ?? 0);
  $redondeo         = doubleval($data['redondeo']         ?? 0);
  $total            = doubleval($data['total']            ?? 0);

  return [
    "efectivo"         => $efectivo,
    "cheque"           => $cheque,
    "transferencia"    => $transferencia,
    "tarjeta_credito"  => $tarjeta_credito,
    "tarjeta_debito"   => $tarjeta_debito,
    "iva"              => $iva,
    "ieps"             => $ieps,
    "subtotal"         => $subtotal,
    "redondeo"         => $redondeo,
    "total"            => $total
  ];
}

function getContadoSalePorDepositar(
  $branchId,
  $date,
  $extraParams = [
    "dateMode" => "%Y-%m"
  ]
) {
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      SUM(efectivo) AS efectivo
    FROM
      {$db_dti}_ventas
    WHERE
      id_sucursal           = {$branchId} AND
      DATE_FORMAT(fecha_creacion, '{$extraParams["dateMode"]}') = '{$date}'   AND
      forma_pago            = 'contado'   AND
      status                = 'activo'    AND
      (
        efectivo_referencia IS NULL OR
        efectivo_referencia = ''
      )
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);

  $efectivo         = doubleval($data['efectivo']         ?? 0);

  return $efectivo;
}

function getTotalProductSaleByBranchIdAndDate(
  $branchId,
  $date,
  $extraParams = [
    "dateMode" => "%Y-%m"
  ]
) {
  global $mysqli;
  global $db_dti;

  $defaultExtraParams = [
    "dateMode" => "%Y-%m"
  ];

  $extraParams = array_merge($defaultExtraParams, $extraParams);

  $typeId = $extraParams["typeId"];
  $paymentForm = $extraParams["paymentForm"];

  $byTypeId = $typeId ? "VPR.id_tipo = {$typeId}" : "1=1";
  $byPaymentForm = $paymentForm ? "V.forma_pago = '{$paymentForm}'" : "1=1";

  $query = "SELECT
      SUM(VPR.total) AS totalProducts
    FROM
      {$db_dti}_venta_productos AS VPR
    LEFT JOIN
      {$db_dti}_ventas AS V ON (VPR.id_venta = V.id_venta)
    WHERE
      V.id_sucursal           = {$branchId} AND
      DATE_FORMAT(V.fecha_creacion, '{$extraParams["dateMode"]}') = '{$date}'   AND
      V.status                = 'activo'    AND
      VPR.cancelado           = 'no'        AND
      {$byTypeId} AND
      {$byPaymentForm}
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);

  $totalProducts  = doubleval($data['totalProducts'] ?? 0);

  return $totalProducts;
}

function getCountProductSaleByBranchIdAndDate(
  $branchId,
  $date,
  $extraParams = [
    "dateMode" => "%Y-%m"
  ]
) {
  global $mysqli;
  global $db_dti;

  $defaultExtraParams = [
    "dateMode" => "%Y-%m"
  ];

  $extraParams = array_merge($defaultExtraParams, $extraParams);

  $typeId = $extraParams["typeId"];
  $paymentForm = $extraParams["paymentForm"];

  $byTypeId = $typeId ? "VPR.id_tipo = {$typeId}" : "1=1";
  $byPaymentForm = $paymentForm ? "V.forma_pago = '{$paymentForm}'" : "1=1";

  $query = "SELECT
      COUNT(DISTINCT VPR.id_producto) AS countProducts
    FROM
      {$db_dti}_venta_productos AS VPR
    LEFT JOIN
      {$db_dti}_ventas AS V ON (VPR.id_venta = V.id_venta)
    WHERE
      V.id_sucursal           = {$branchId} AND
      DATE_FORMAT(V.fecha_creacion, '{$extraParams["dateMode"]}') = '{$date}'   AND
      V.status                = 'activo'    AND
      VPR.cancelado           = 'no'        AND
      {$byTypeId} AND
      {$byPaymentForm}
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);

  $countProducts  = intval($data['countProducts'] ?? 0);

  return $countProducts;
}

// función que obtiene el total de días que huvo venta filtrado por sucursal y fecha y extra params
function getCountDaysWithSalesByBranchIdAndDate(
  $branchId,
  $date,
  $extraParams = [
    "dateMode" => "%Y-%m"
  ]
) {
  global $mysqli;
  global $db_dti;

  $defaultExtraParams = [
    "dateMode" => "%Y-%m"
  ];

  $extraParams = array_merge($defaultExtraParams, $extraParams);

  $query = "SELECT
      COUNT(DISTINCT DATE_FORMAT(fecha_creacion, '%Y-%m-%d')) AS countDays
    FROM
      {$db_dti}_ventas
    WHERE
      id_sucursal           = {$branchId} AND
      DATE_FORMAT(fecha_creacion, '{$extraParams["dateMode"]}') = '{$date}'   AND
      status                = 'activo'  AND
      forma_pago           = 'contado'
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);

  $countDays  = intval($data['countDays'] ?? 0);

  return $countDays;
}

function getQuoteAdvancePaymentsTotal($quoteFolio)
{
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      SUM(total) AS total
    FROM
      {$db_dti}_ventas
    WHERE
      folio_cotizacion  = '{$quoteFolio}' AND
      status            = 'activo'        AND
      tipo_transaccion  = 'anticipo'
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);
  $total  = doubleval($data['total'] ?? 0);

  return $total;
}

function getSaleCreditNoteTotals($params = [
  "notSaleId"  => 0,
  "quoteFolio" => 0
])
{
  global $mysqli;
  global $db_dti;

  $notSaleId  = $params["notSaleId"];
  $quoteFolio = $params["quoteFolio"];

  $byNotSaleId = $notSaleId ? "id_venta != {$notSaleId}" : "1=1";

  $query = "SELECT
      SUM(efectivo)         AS efectivo,
      SUM(cheque)           AS cheque,
      SUM(transferencia)    AS transferencia,
      SUM(tarjeta_credito)  AS tarjeta_credito,
      SUM(tarjeta_debito)   AS tarjeta_debito,
      SUM(subtotal)         AS subtotal,
      SUM(iva)              AS iva,
      SUM(redondeo)         AS redondeo,
      SUM(total)            AS total
    FROM
      {$db_dti}_ventas
    WHERE
      folio_cotizacion  = '{$quoteFolio}' AND
      status            = 'activo'        AND
      ({$byNotSaleId})
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if (!$numRows) return;

  $data = mysqli_fetch_assoc($result);

  $total = $data["total"] ?? 0;

  if (!$total) return;

  return $data;
}

function createCreditNoteFromQuote($params = [
  "userId"      => 0,
  "branchId"    => 0,
  "saleId"      => 0,
  "quoteData"   => null
])
{
  require_once __DIR__ . "/../data/lib/helpers/products.helper.php";

  global $mysqli;
  global $db_dti;

  $userId     = $params["userId"];
  $branchId   = $params["branchId"];
  $saleId     = $params["saleId"];
  $quoteData  = $params["quoteData"] ?? new stdClass();
  $quoteFolio = $quoteData->folio;

  // Obtener el producto para la nota de crédito
  $productsModel = new ProductHelper();
  $productsModel->getProductTypeCreditNote();

  if (!$productsModel->getId()) return;

  // Obtener los totales para la nota de crédito sin incluir la venta actual
  $data = getSaleCreditNoteTotals([
    "notSaleId"  => $saleId,
    "quoteFolio" => $quoteFolio
  ]);

  if (!$data) return;

  $userId           = $userId;
  $branchId         = $branchId;
  $customerId       = $quoteData->customer_id;
  $folio            = get_sale_folio($branchId);
  $quoteFolio       = $quoteFolio;
  $transactionType  = "nota-credito";
  $observations     = "Nota de crédito generada desde la cotización {$quoteFolio}";
  $efectivo         = (doubleval($data['efectivo']         ?? 0) * -1);
  $cheque           = (doubleval($data['cheque']           ?? 0) * -1);
  $transferencia    = (doubleval($data['transferencia']    ?? 0) * -1);
  $tarjeta_debito   = (doubleval($data['tarjeta_debito']   ?? 0) * -1);
  $tarjeta_credito  = (doubleval($data['tarjeta_credito']  ?? 0) * -1);
  $subtotal         = (doubleval($data['subtotal']         ?? 0) * -1);
  $iva              = (doubleval($data['iva']              ?? 0) * -1);
  $redondeo         = (doubleval($data['redondeo']         ?? 0) * -1);
  $total            = (doubleval($data['total']            ?? 0) * -1);
  $creationDate     = date('Y-m-d H:i:s');
  $paymentForm      = 'contado';

  // Crear la nota de crédito
  $query = "INSERT INTO {$db_dti}_ventas (
      id_usuario,
      id_sucursal,
      id_cliente,
      folio,
      folio_cotizacion,
      tipo_transaccion,
      observaciones,
      efectivo,
      cheque,
      transferencia,
      tarjeta_debito,
      tarjeta_credito,
      subtotal,
      iva,
      redondeo,
      total,
      fecha_creacion,
      forma_pago
    ) VALUES (
      '{$userId}',
      '{$branchId}',
      '{$customerId}',
      '{$folio}',
      '{$quoteFolio}',
      '{$transactionType}',
      '{$observations}',
      '{$efectivo}',
      '{$cheque}',
      '{$transferencia}',
      '{$tarjeta_debito}',
      '{$tarjeta_credito}',
      '{$subtotal}',
      '{$iva}',
      '{$redondeo}',
      '{$total}',
      '{$creationDate}',
      '{$paymentForm}'
    )
  ";

  $result = mysqli_query($mysqli, $query);

  if (!$result) return;

  $saleId = mysqli_insert_id($mysqli);

  // Agregar el producto de la nota de crédito
  $productId      = $productsModel->getId();
  $typeId         = $productsModel->getTypeId();
  $name           = $productsModel->getName();
  $salePrice      = $total;       // Precio con iva
  $haveIva        = "si";
  $salePriceBase  = $total;       // Precio con iva
  $quantity       = 1;
  $price          = $subtotal;    // Precio sin iva
  $iva            = $iva;         // IVA
  $priceNet       = $total;       // Precio con iva
  $subtotal       = $subtotal;    // Precio sin iva
  $total          = $total;       // Precio con iva

  $query = "INSERT INTO {$db_dti}_venta_productos (
      id_venta,
      id_producto,
      id_tipo,
      nombre_producto,
      precio_venta,
      aplica_iva,
      precio_venta_base,
      cantidad,
      precio,
      iva,
      precio_neto,
      subtotal,
      total
    ) VALUES (
      '{$saleId}',
      '{$productId}',
      '{$typeId}',
      '{$name}',
      '{$salePrice}',
      '{$haveIva}',
      '{$salePriceBase}',
      '{$quantity}',
      '{$price}',
      '{$iva}',
      '{$priceNet}',
      '{$subtotal}',
      '{$total}'
    )
  ";

  mysqli_query($mysqli, $query);

  return $saleId;
}

function getUserPermissionModuleActionIds(
  $userId
) {
  global $mysqli;
  /* adm_usuario_permisos
  	1	id Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	  2	id_usuario Índice	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	  3	id_modulo_accion Índice	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
  */

  $moduleActionIds = [];

  $query = "SELECT
      id_modulo_accion AS id
    FROM
      adm_usuario_permisos
    WHERE
      id_usuario = {$userId}
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $moduleActionIds[] = $row['id'];
    }
  }

  return $moduleActionIds;
}

function getAvailableModuleActionsOfAdminBotsRole($moduleId, $roleId)
{
  global $mysqli;

  $actions = [];

  $query = "SELECT
      RMA.id_rol_modulo_accion,
      RMA.id_rol,
      RMA.id_modulo_accion  AS id,
      A.accion              AS label
    FROM
      adm_rol_modulo_acciones AS RMA
    INNER JOIN
      adm_modulo_acciones AS MA ON (MA.id_modulo_accion = RMA.id_modulo_accion)
    INNER JOIN
      adm_acciones AS A ON (A.id_accion = MA.id_accion)
    INNER JOIN
      adm_roles AS R ON (R.id_rol = RMA.id_rol AND R.id_rol = {$roleId})
    WHERE
      MA.id_modulo = {$moduleId}
    ORDER BY
      A.accion
    DESC
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $actions[] = [
        "id"    => $row["id"],
        "label" => $row["label"]
      ];
    }
  }

  return $actions;
}

function getAvailableModuleActionsOfAdminBotsRoleSwitches(
  $moduleId,
  $roleId,
  $value = []
) {
  $actions = getAvailableModuleActionsOfAdminBotsRole($moduleId, $roleId);
  $switches = "";

  $counterCheckeds = 0;

  // switch para activar todos
  $switches .= "<div class='m-0 form-check form-switch'>
    <input id='toggle-all-{$moduleId}'
      class='form-check-input toggle-all-permissions'
      data-switches='switch-permission-{$moduleId}'
      type='checkbox'
    >
      <label class='form-check-label fw-bold' for='toggle-all-{$moduleId}'>Todos</label>
    </div>  <span class='vr mx-1'></span>";

  foreach ($actions as $action) {
    $checked = in_array($action['id'], $value) ? "checked" : "";
    $counterCheckeds += $checked ? 1 : 0;

    $switches .= "<div class='m-0 form-check form-switch'>
      <input id='action_{$action['id']}'
        class='form-check-input switch-permission switch-permission-{$moduleId}'
        name='actions-{$moduleId}[]'
        value='{$action['id']}'
        type='checkbox'
        data-parentAll='toggle-all-{$moduleId}'
        {$checked}
      >
      <label class='form-check-label fw-normal' for='action_{$action['id']}'>{$action['label']}</label>
    </div>";
  }

  $html = "
    <div class='d-flex flex-wrap align-items-center gap-2'>{$switches}</div>
  ";

  $allChecked = count($actions) > 0 && $counterCheckeds === count($actions);

  if ($allChecked) {
    $html = str_replace(
      "id='toggle-all-{$moduleId}'",
      "id='toggle-all-{$moduleId}' checked",
      $html
    );
  }

  return $html;
}

// return DD-MM-YYYY (TIME optional)
function dateToSpanishStructure(
  $date,
  $withTime = false
) {
  setlocale(LC_TIME, 'es_ES.UTF-8');
  date_default_timezone_set('America/Mexico_City');

  $timestamp = strtotime($date);

  if ($withTime) {
    return strftime('%d-%m-%Y %H:%M:%S', $timestamp);
  } else {
    return strftime('%d-%m-%Y', $timestamp);
  }
}

function getSaleCashTotalToDeposit($date = null)
{
  global $mysqli;
  global $db_dti;

  $byDate = "1=1";

  if ($date) $byDate = "DATE(F.fecha) = '{$date}'";

  $query = "SELECT
      SUM(V.efectivo) AS totalCashToDeposit
    FROM
      {$db_dti}_ventas AS V
    INNER JOIN
      {$db_dti}_facturas AS F ON (F.id_venta = V.id_venta AND F.cancelado = 0)
    WHERE
      V.status                = 'activo'    AND
      (
        V.efectivo_referencia IS NULL OR
        V.efectivo_referencia = ''
      ) AND
      ({$byDate})
  ";

  $result = mysqli_query($mysqli, $query);
  $data   = mysqli_fetch_assoc($result);

  $totalCashToDeposit  = doubleval($data['totalCashToDeposit'] ?? 0);

  return $totalCashToDeposit;
}

function truncateDecimals($number, $decimals = 2)
{
  //return round($number, $decimals, PHP_ROUND_HALF_DOWN);

  $factor = pow(10, $decimals);
  // El truco es usar intval para truncar después de multiplicar
  return intval($number * $factor) / $factor;
}

function truncateDecimalsV2($number, $decimals = 2)
{
  $factor = pow(10, $decimals);
  // El truco es usar intval para truncar después de multiplicar
  return intval($number * $factor) / $factor;
}

function getSaleSerialNumbersBySaleFolioAndProductId($saleFolio, $productId)
{
  global $mysqli;
  global $db_dti;

  $serialNumbers = [];

  $query = "SELECT
      numero_serie
    FROM
      {$db_dti}_producto_numeros_serie
    WHERE
      folio_venta = '{$saleFolio}'
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $serialNumbers[] = $row['numero_serie'];
    }
  }

  return $serialNumbers;
}

function getSaleTotalsForInvoicingBySaleId($saleId)
{
  global $mysqli;
  global $db_dti;

  $query = "SELECT
      folio,
      redondeo,
      efectivo,
      cheque,
      transferencia,
      tarjeta_credito,
      tarjeta_debito,
      tipo_transaccion,
      status
    FROM
      {$db_dti}_ventas
    WHERE
      id_venta = {$saleId} AND
      status != 'cancelado'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $saleData = mysqli_fetch_assoc($result);

  if ($saleData["status"] == "cancelado") return false;

  $query = "SELECT
      SUM(precio * cantidad) AS total_precio,
      SUM(iva * cantidad)    AS total_iva,
      SUM(ieps * cantidad)   AS total_ieps
    FROM
      {$db_dti}_venta_productos
    WHERE
      id_venta  = {$saleId} AND
      cancelado = 'no'
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return false;

  $totalsData = mysqli_fetch_assoc($result);

  $data = [
    "status"          => $saleData["status"],
    "folio"           => $saleData["folio"],
    "rounded"         => $saleData["redondeo"],
    "price"           => $totalsData["total_precio"] ?? 0,
    "iva"             => $totalsData["total_iva"] ?? 0,
    "ieps"            => $totalsData["total_ieps"] ?? 0,
    "cash"            => $saleData["efectivo"],
    "cheque"          => $saleData["cheque"],
    "transfer"        => $saleData["transferencia"],
    "credito"         => $saleData["tarjeta_credito"],
    "debito"          => $saleData["tarjeta_debito"],
    "transactionType" => $saleData["tipo_transaccion"]
  ];

  return $data;
}

function getSalesForInvoicingBySaleIds($saleIds = [])
{
  global $mysqli;
  global $db_dti;

  if (!is_array($saleIds)) $saleIds = [$saleIds];

  $normalizedSaleIds = [];

  foreach ($saleIds as $saleId) {
    $saleId = trim((string)$saleId);

    if ($saleId === "" || $saleId === "0" || strtoupper($saleId) === "NULL") continue;
    if (!ctype_digit($saleId)) continue;

    $normalizedSaleIds[] = (int)$saleId;
  }

  $normalizedSaleIds = array_values(array_unique($normalizedSaleIds));
  sort($normalizedSaleIds, SORT_NUMERIC);

  if (sizeof($normalizedSaleIds) == 0) return [
    "status"  => "error",
    "message" => "No se recibieron ventas válidas para facturar"
  ];

  // Obtener unidad y clave del producto para facturar múltiples ventas
  $query = "SELECT
      P.id_producto,
      P.codigo,
      P.nombre_producto,
      P.id_clave_unidad,
      P.id_clave_producto_servicio,
      CU.nombre       AS clave_unidad_nombre,
      CPS.descripcion AS clave_producto_servicio_descripcion
    FROM
      {$db_dti}_productos AS P
    LEFT JOIN
      {$db_dti}_clave_unidades AS CU ON (CU.id_clave_unidad = P.id_clave_unidad)
    LEFT JOIN
      {$db_dti}_clave_producto_servicios AS CPS ON (CPS.id_clave_producto_servicio = P.id_clave_producto_servicio)
    WHERE
      codigo = 'SERV-FACT'
    LIMIT 1
  ";

  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows == 0) return [
    "status"  => "error",
    "message" => "No se ha creado el producto con el código \"SERV-FACT\" para facturar ventas múltiples"
  ];

  $productData = mysqli_fetch_assoc($result);

  $salesForInvoicing = [];
  $mixedTotals = [
    "totalCash"       => 0,
    "totalCheque"     => 0,
    "totalTransfer"   => 0,
    "totalCreditCard" => 0,
    "totalDebitCard"  => 0,
  ];

  foreach ($normalizedSaleIds as $saleId) {
    $saleTotalsData = getSaleTotalsForInvoicingBySaleId($saleId);

    if (!$saleTotalsData) return [
      "status"  => "error",
      "message" => "Una de las ventas a facturar no existe o está cancelada"
    ];

    // Verificar si tienen folio de facturación
    $transactionType = $saleTotalsData["transactionType"];
    $invoiceData     = null;

    // if ($transactionType == "nota-credito") return [
    //   "status"  => "error",
    //   "message" => "Una de las ventas a facturar es una <b>Nota de crédito</b>, ¡No es válido para la factura global!"
    // ];

    if ($transactionType == "nota-credito" || $transactionType == "anticipo") return [
      "status"  => "error",
      "message" => "No se pueden incluir ventas con tipo de transacción <b>Nota de crédito</b> o <b>Anticipo</b> en la factura global"
    ];

    //if ($transactionType == "anticipo")     $invoiceData = getSaleInvoiceBySaleIdAndType($saleId, "anticipo");
    /* if ($transactionType == "venta")         */
    $invoiceData = getSaleInvoiceBySaleIdAndType($saleId, "ingreso");

    if ($invoiceData) return [
      "status"  => "error",
      "message" => "Una de las ventas a facturar ya tiene una factura generada con el folio {$invoiceData["serie"]}-{$invoiceData["folio"]}"
    ];

    $saleTotalsData["saleFolio"]                = $saleTotalsData["folio"];
    $saleTotalsData["productId"]                = $productData["id_producto"];
    $saleTotalsData["productSku"]               = $productData["codigo"];
    $saleTotalsData["productName"]              = $productData["nombre_producto"];
    $saleTotalsData["satUnitKeyId"]             = $productData["id_clave_unidad"];
    $saleTotalsData["satProductServiceKeyId"]   = $productData["id_clave_producto_servicio"];
    $saleTotalsData["satUnitKeyName"]           = $productData["clave_unidad_nombre"];
    $saleTotalsData["satProductServiceKeyName"] = $productData["clave_producto_servicio_descripcion"];

    $mixedTotals["totalCash"]       += $saleTotalsData["cash"];
    $mixedTotals["totalCheque"]     += $saleTotalsData["cheque"];
    $mixedTotals["totalTransfer"]   += $saleTotalsData["transfer"];
    $mixedTotals["totalCreditCard"] += $saleTotalsData["credito"];
    $mixedTotals["totalDebitCard"]  += $saleTotalsData["debito"];

    $salesForInvoicing[] = $saleTotalsData;
  }

  $data = [
    "status"      => "success",
    "sales"       => $salesForInvoicing,
    "mixedTotals" => $mixedTotals
  ];

  return $data;
}

function getSalePaymentInvoiceBySalePaymentId(int $salePaymentId): array
{
  global
    $mysqli,
    $db_dti;

  $invoice = [];

  $query = "SELECT
      VPF.id_venta_pago_factura,
      VPF.id_venta_pago,
      VPF.id_factura,
      FP.uuid
    FROM
      {$db_dti}_venta_pago_facturas AS VPF
    INNER JOIN
      {$db_dti}_facturas_p AS FP ON (FP.id_factura = VPF.id_factura)
    WHERE
      VPF.id_venta_pago = ? AND
      FP.cancelado      = 0
    LIMIT 1
  ";

  $stmt = $mysqli->prepare($query);

  try {
    $stmt->bind_param("i", $salePaymentId);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows > 0) $invoice = $result->fetch_assoc();
  } catch (Exception $e) {
    error_log("ERROR_GET_SALE_PAYMENT_INVOICE_BY_SALE_PAYMENT_ID: {$e->getMessage()}");
  }

  return $invoice;
}

function getSalePaymentInvoicePaymetData(int $invoiceId): array
{
  global
    $mysqli,
    $db_dti;

  $invoicePaymentData = [];

  $query = "SELECT
      FPP.num_parcialidad,
      FPP.importe_saldo_anterior,
      FPP.importe_pagado,
      FPP.importe_saldo_insoluto
    FROM
      {$db_dti}_facturas_p_pagos AS FPP
    INNER JOIN
      {$db_dti}_facturas_p AS FP ON FPP.id_factura = FP.id_factura
    WHERE
      FP.id_factura_ingreso = ?
    ORDER BY
      FPP.num_parcialidad
    DESC
    LIMIT 1
  ";

  $stmt = $mysqli->prepare($query);

  try {
    $stmt->bind_param("i", $invoiceId);
    $stmt->execute();

    $result   = $stmt->get_result();
    $numRows  = $result->num_rows;

    if ($numRows == 0) $invoicePaymentData = [
      "num_parcialidad"         => 1,
      "importe_saldo_anterior"  => 0,
      "importe_saldo_insoluto"  => 0,
      "importe_pagado"          => 0
    ];

    if ($numRows > 0) {
      $data = $result->fetch_assoc();

      $invoicePaymentData = [
        "num_parcialidad"         => $data['num_parcialidad'] + 1,
        "importe_saldo_anterior"  => $data['importe_saldo_anterior'],
        "importe_saldo_insoluto"  => $data['importe_saldo_insoluto'],
        "importe_pagado"          => $data["importe_pagado"]
      ];
    }
  } catch (Exception $e) {
    error_log("ERROR_GET_SALE_PAYMENT_INVOICE_PAYMENT_DATA: {$e->getMessage()}");
  }

  return $invoicePaymentData;
}
