<?php
function getNavbarModules(
  $parent_id = 0
) {
  global $mysqli;
  global $_SESSION;
  global $db_ati;

  $user_id  = get_id_usuario();

  // Verificar si el rol del usuario es vendedor o no
  $admp_session_user_data = getUserData($user_id);
  $role_slug              = $admp_session_user_data['rol_slug'];
  $IS_ADMIN               = $admp_session_user_data["IS_ADMIN"];

  $joinPermissions = "";

  if (!$IS_ADMIN) {
    $joinPermissions = "
      LEFT JOIN {$db_ati}_usuario_permisos AS UP ON (UP.id_modulo_accion = MA.id_modulo_accion)
    ";
  }

  $menu     = '';

  $c_where = "
    ME.pertenece_a = $parent_id AND
    (
      U.id_usuario = $user_id OR
      ME.id_modulo = 0
    )
  ";

  if (!$IS_ADMIN) $c_where .= " AND (UP.id_usuario = $user_id OR ME.id_modulo = 0) AND (A.slug = 'ver' OR A.slug IS NULL) ";

  //if ($user_id == 1) $c_where = "ME.pertenece_a = $parent_id";

  $query = "SELECT
      ME.id,
      ME.id_modulo,
      ME.titulo,
      ME.icono,
      ME.orden,
      ME.slug,
      ME.pertenece_a,
      ME._blank,
      M.slug
    FROM {$db_ati}_menu AS ME
      LEFT JOIN {$db_ati}_modulos              AS M    ON (ME.id_modulo        = M.id_modulo)
      LEFT JOIN {$db_ati}_modulo_acciones      AS MA   ON (ME.id_modulo        = MA.id_modulo)
      LEFT JOIN {$db_ati}_rol_modulo_acciones  AS RMA  ON (MA.id_modulo_accion = RMA.id_modulo_accion)
      LEFT JOIN {$db_ati}_usuarios             AS U    ON (RMA.id_rol          = U.id_rol)
      LEFT JOIN {$db_ati}_acciones             AS A    ON (MA.id_accion        = A.id_accion)
      {$joinPermissions}
    WHERE
      $c_where
    GROUP BY ME.id
    ORDER BY ME.orden
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $menu_id = $row['id'];
      $have_submenu = haveSubmenu($menu_id);
      $submenu      = getNavbarModules($menu_id);
      $_blank       = $row['_blank'] === 'si' ? 'target="_blank"' : '';

      if ($submenu && $have_submenu) :
        $menu .= '
          <li>
            <a href="#sidebar-menu-' . $row['id'] . '" data-bs-toggle="collapse" aria-expanded="false" aria-controls="sidebar-menu-' . $row['slug'] . '">
              <i class="' . $row['icono'] . '"></i>
              <span> ' . $row['titulo'] . ' </span>
              <span class="menu-arrow"></span>
            </a>

            <div class="collapse" id="sidebar-menu-' . $row['id'] . '">
              <ul class="nav-second-level">
              ' . $submenu . '
              </ul>
            </div>
          </li>
        ';
      endif;

      if (!$submenu && !$have_submenu) :
        $menu .= '
          <li>
            <a href="' . BASE_URL . '/' . $row['slug'] . '" ' . $_blank . '>
              <i class="' . $row['icono'] . '"></i>
              <span> ' . $row['titulo'] . ' </span>
            </a>
          </li>
        ';
      endif;
    endwhile;
  endif;

  if ($parent_id === 0) $menu .= '
    <li>
      <a href="' . BASE_URL . '/cerrar-sesion">
        <i class="ri-logout-box-line"></i>
        <span> Cerrar sesión </span>
      </a>
    </li>
  ';

  return $menu;
}

function haveSubmenu(
  $parent_id
) {
  global $mysqli;
  global $db_ati;

  $query        = "SELECT id FROM {$db_ati}_menu WHERE pertenece_a = $parent_id";
  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows === 0)  return false;
  if ($num_rows > 0)    return true;
}

function checkModuleActionPermission(
  $module,
  $action,
  $use_close_sesion = false
) {
  global $mysqli;
  global $db_ati;
  global $_SESSION;

  $user_id = get_id_usuario();

  $moduleV2 = str_replace("-", "/", $module);
  $moduleV3 = str_replace("/", "-", $module);

  $query = "SELECT
      RMA.id_rol_modulo_accion,
      RMA.id_rol,
      RMA.id_modulo_accion,
      M.modulo,
      R.slug AS rolSlug
    FROM {$db_ati}_rol_modulo_acciones AS RMA
      INNER JOIN adm_modulo_acciones AS MA ON (RMA.id_modulo_accion  = MA.id_modulo_accion)
      INNER JOIN adm_acciones        AS A  ON (MA.id_accion          = A.id_accion)
      INNER JOIN adm_modulos         AS M  ON (MA.id_modulo          = M.id_modulo)
      INNER JOIN adm_usuarios        AS U  ON (RMA.id_rol            = U.id_rol)
      INNER JOIN adm_roles           AS R  ON (RMA.id_rol            = R.id_rol)
    WHERE
      U.id_usuario  = $user_id  AND
      (
        M.slug = '$module' OR
        M.slug = '$moduleV2' OR
        M.slug = '$moduleV3'
      ) AND
      A.slug        = '$action'
    LIMIT 1
  ";

  /* if ($user_id == 1) $query = "SELECT
      id_modulo,
      modulo
    FROM
      {$db_ati}_modulos
    WHERE
      slug = '$module'
    LIMIT 1
  "; */

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows === 0 && $use_close_sesion)   closeSession();
  if ($num_rows === 0 && !$use_close_sesion)  return false;

  if ($num_rows > 0) {
    $data     = mysqli_fetch_assoc($query_result);
    $rolSlug  = $data["rolSlug"];

    if ($rolSlug == "administrador" || $rolSlug == "root") {
      $data = mysqli_fetch_assoc($query_result);

      $module = new stdClass();
      $module->name = $data['modulo'];

      return $module;
    }

    if ($rolSlug != "administrador" && $rolSlug != "root") return admVerifySupplierPermissions($module, $action, $use_close_sesion);
  }
}

function admVerifySupplierPermissions($module, $action, $use_close_sesion)
{
  global $mysqli;

  /* adm_usuario_permisos
  	1	id Primaria	int(11)			        No	Ninguna AUTO_INCREMENT  Cambiar Cambiar	Eliminar Eliminar
	  2	id_usuario Índice	int(11)			  No	Ninguna			            Cambiar Cambiar	Eliminar Eliminar
	  3	id_modulo_accion Índice	int(11)	No	Ninguna			            Cambiar Cambiar	Eliminar Eliminar
  */

  $moduleV2 = str_replace("-", "/", $module);
  $moduleV3 = str_replace("/", "-", $module);

  $userId = get_id_usuario();

  $query = "SELECT
      UP.id,
      UP.id_usuario,
      UP.id_modulo_accion,
      M.modulo
    FROM adm_usuario_permisos AS UP
      INNER JOIN adm_modulo_acciones AS MA ON (UP.id_modulo_accion  = MA.id_modulo_accion)
      INNER JOIN adm_acciones        AS A  ON (MA.id_accion         = A.id_accion)
      INNER JOIN adm_modulos         AS M  ON (MA.id_modulo         = M.id_modulo)
    WHERE
      UP.id_usuario = {$userId}   AND
      (
        M.slug = '$module' OR
        M.slug = '$moduleV2' OR
        M.slug = '$moduleV3'
      ) AND
      A.slug        = '{$action}'
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows === 0 && $use_close_sesion)   closeSession();
  if ($num_rows === 0 && !$use_close_sesion)  return false;
  if ($num_rows > 0) {
    $data = mysqli_fetch_assoc($query_result);

    $module = new stdClass();
    $module->name = $data['modulo'];

    return $module;
  }
}

function haveActions(
  $module_slug,
  $location
) {
  global $mysqli;

  $query = getPageModuleActionsQuery($module_slug, $location);

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0)    return true;
  if ($num_rows === 0)  return false;
}

function getTableActions(
  $module_slug,
  $data_row,
  $settings = []
) {
  global $mysqli;

  $response = '';

  $query = getPageModuleActionsQuery($module_slug, 'tabla');

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      $attributes = '';

      $have_settings = $settings[$row['slug']];
      $cumple_condicion = true;

      if ($settings[$row["slug"]]) {
        $actionssSettings = $settings[$row["slug"]];

        if ($actionssSettings["type"]) $row['tipo'] = $actionssSettings["type"];

        if ($actionssSettings["type"] == "customLink") {
          $row['customLink']        = $actionssSettings["customLink"];
          $row['customTargetBlank'] = $actionssSettings["customTargetBlank"];
        }
      }

      if ($have_settings) $cumple_condicion = $have_settings['condition'];

      if ($row['_blank'] === 'si') $attributes .= ' target="_blank"';

      if ($row['clase_html'] != 'btn-edit' && $row['clase_html'] != 'btn-add' && $row['tipo'] == 'modal') $attributes = 'data-bs-toggle="modal" data-bs-target="#' . $module_slug . '-modal-' . $row['slug'] . '"';
      if ($row['clase_html'] == 'btn-edit' || $row['clase_html'] == 'btn-add' && $row['tipo'] == 'modal') $attributes = 'data-bs-toggle="modal" data-bs-target="#' . $module_slug . '-modal"';

      if ($row['tipo'] === 'modal' && ($row['clase_html'] !== 'btn-edit' && $row['clase_html'] !== 'btn-add')) :
        $row['clase_html'] = $row['clase_html'] . ' btn-modal';
        $attributes .= ' data-modal-action="modal-action-' . $module_slug . '-' . $row['slug'] . '"';
      endif;

      if ($row['tipo'] === 'accion') :
        //$attributes .= 'data-action="action-' . $row['slug'] . '-' . $module_slug . '"';

        $data_action = [
          'action' => $row['slug'],
          'alert' => $row['alerta'],
          'title' => parseAlertMessage($row['alerta_titulo'], $row),
          'message' => parseAlertMessage($row['alerta_mensaje'], $row)
        ];

        $row['clase_html'] = $row['clase_html'] . ' btn-action';
        $attributes .= ' data-action="' . htmlspecialchars(json_encode($data_action)) . '"';
      endif;

      if ($row["tipo"] != "customLink") {
        if ($cumple_condicion) $response .= '
          <a class="dropdown-item ' . $row['clase_html'] . '" ' . $attributes . ' data-row="' . htmlspecialchars(json_encode($data_row)) . '" href="javascript:void(0)">
            <i class="' . $row['icono'] . '"></i> ' . $row['accion'] . '
          </a>
        ';
      }

      if ($row["tipo"] == "customLink") {
        $customLink         = $row["customLink"] ? "href='" . $row["customLink"] . "'" : 'href="javascript:void(0)"';
        $customTargetBlank  = $row["customTargetBlank"] === "si" ? 'target="_blank"' : '';

        if ($cumple_condicion) $response .= '
          <a class="dropdown-item ' . $row['clase_html'] . '" ' . $customTargetBlank . ' ' . $customLink . '>
            <i class="' . $row['icono'] . '"></i> ' . $row['accion'] . '
          </a>
        ';
      }
    endwhile;
  endif;

  return $response;
}

function getPageModuleActionsQuery(
  $module_slug,
  $location
) {
  global $_SESSION;
  global $db_ati;

  $admp_session_user_data  = getUserData(get_id_usuario());
  $id_rol                 = $admp_session_user_data['id_rol'];
  $IS_ADMIN               = $admp_session_user_data["IS_ADMIN"];

  $query = "SELECT
      RMA.id_rol_modulo_accion,
      RMA.id_rol,
      RMA.id_modulo_accion,
      MA.id_modulo,
      MA.id_accion,
      A.accion,
      A.tipo,
      A.ubicacion,
      A.icono,
      A.clase_html,
      A.slug,
      A.alerta,
      A.alerta_titulo,
      A.alerta_mensaje,
      M.modulo,
      A._blank
    FROM {$db_ati}_rol_modulo_acciones AS RMA
      INNER JOIN {$db_ati}_modulo_acciones AS MA ON (RMA.id_modulo_accion  = MA.id_modulo_accion)
      INNER JOIN {$db_ati}_modulos         AS M  ON (MA.id_modulo          = M.id_modulo)
      INNER JOIN {$db_ati}_acciones        AS A  ON (MA.id_accion          = A.id_accion)
    WHERE
      RMA.id_rol  = $id_rol         AND
      M.slug      = '$module_slug'  AND
      A.ubicacion = '$location'
    ORDER BY A.orden
  ";

  if (!$IS_ADMIN) $query = "SELECT
      RMA.id_rol_modulo_accion,
      RMA.id_rol,
      RMA.id_modulo_accion,
      MA.id_modulo,
      MA.id_accion,
      A.accion,
      A.tipo,
      A.ubicacion,
      A.icono,
      A.clase_html,
      A.slug,
      A.alerta,
      A.alerta_titulo,
      A.alerta_mensaje,
      M.modulo,
      A._blank
    FROM {$db_ati}_rol_modulo_acciones AS RMA
      INNER JOIN {$db_ati}_modulo_acciones AS MA ON (RMA.id_modulo_accion  = MA.id_modulo_accion)
      INNER JOIN {$db_ati}_modulos         AS M  ON (MA.id_modulo          = M.id_modulo)
      INNER JOIN {$db_ati}_acciones        AS A  ON (MA.id_accion          = A.id_accion)
      INNER JOIN {$db_ati}_usuario_permisos AS UP ON (UP.id_modulo_accion  = RMA.id_modulo_accion)
    WHERE
      RMA.id_rol  = $id_rol         AND
      M.slug      = '$module_slug'  AND
      A.ubicacion = '$location'     AND
      UP.id_usuario = {$admp_session_user_data["id_usuario"]}
    ORDER BY A.orden
  ";

  if ($id_rol == 1) $query = "SELECT
      MA.id_modulo_accion,
      MA.id_modulo,
      MA.id_accion,
      A.accion,
      A.tipo,
      A.ubicacion,
      A.icono,
      A.clase_html,
      A.slug,
      A.alerta,
      A.alerta_titulo,
      A.alerta_mensaje
    FROM {$db_ati}_modulo_acciones AS MA
      INNER JOIN {$db_ati}_modulos   AS M ON (MA.id_modulo = M.id_modulo)
      INNER JOIN {$db_ati}_acciones  AS A ON (MA.id_accion = A.id_accion)
    WHERE
      M.slug      = '$module_slug' AND
      A.ubicacion = '$location'
    ORDER BY A.orden
  ";

  return $query;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- START CATALOGS --//
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
function getRolesCatalog(
  $value = '',
  $label = '--Seleccionar--'
) {
  global $mysqli;
  global $db_ati;

  $response = '';

  $query = "SELECT
      id_rol  AS id_item,
      rol     AS item,
      slug
    FROM {$db_ati}_roles
    WHERE id_rol != 1
    ORDER BY rol
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);

  $response .= '<option value="">' . $label . '</option>';

  while ($row = mysqli_fetch_assoc($query_result)) :
    $selected = $value == $row['id_item'] ? 'selected' : '';

    $response .= '<option ' . $selected . ' data-slug="' . $row['slug'] . '" value="' . $row['id_item'] . '">' . $row['item'] . '</option>';
  endwhile;

  return $response;
}

function getModulesCatalog(
  $value = '',
  $label = '--Seleccionar--',
  $use_cero = false
) {
  global $mysqli;
  global $db_ati;

  $response = '';

  $query = "SELECT
      id_modulo AS id_item,
      modulo    AS item
    FROM {$db_ati}_modulos
    ORDER BY modulo
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);

  $response .= '<option value="' . ($use_cero ? '0' : '') . '">' . $label . '</option>';

  while ($row = mysqli_fetch_assoc($query_result)) :
    $selected = $value == $row['id_item'] ? 'selected' : '';

    $response .= '<option ' . $selected . ' value="' . $row['id_item'] . '">' . $row['item'] . '</option>';
  endwhile;

  return $response;
}

function getMenuCatalog(
  $value = '',
  $label = '--Seleccionar--',
  $use_cero = false
) {
  global $mysqli;
  global $db_ati;

  $response = '';

  $query = "SELECT
      id      AS id_item,
      titulo  AS item
    FROM {$db_ati}_menu
    WHERE pertenece_a = 0
    ORDER BY titulo
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);

  $response .= '<option value="' . ($use_cero ? '0' : '') . '">' . $label . '</option>';

  while ($row = mysqli_fetch_assoc($query_result)) :
    $selected = $value == $row['id_item'] ? 'selected' : '';

    $response .= '<option ' . $selected . ' value="' . $row['id_item'] . '">' . $row['item'] . '</option>';
  endwhile;

  return $response;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- END CATALOGS --//
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */


/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- START CHECKBOXES --//
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
function getActionsCheckboxes(
  $value = [],
  $required = true,
  $identifier = '',
  $disabled = false
) {
  global $mysqli;
  global $db_ati;

  $acciones = '';

  $query = "SELECT
      id_accion,
      accion,
      slug
    FROM {$db_ati}_acciones
    ORDER BY accion
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    $acciones .= '<div class="custom-controls-stacked">';

    while ($row = mysqli_fetch_assoc($query_result)) :
      $selected = in_array($row['id_accion'], $value) ? 'checked' : '';

      $acciones .= '
        <label class="custom-control custom-checkbox">
          <input
            class="custom-control-input"
            name="acciones' . $identifier . '[]"
            value="' . $row['id_accion'] . '"
            type="checkbox"
            ' . $selected . '
            ' . ($required ? 'required' : '') . '
            ' . ($disabled ? 'disabled' : '') . '
          >

          <span class="custom-control-label">' . $row['accion'] . '</span>
        </label>
      ';
    endwhile;

    $acciones .= '</div>';
  endif;

  return $acciones;
}

function getModuleActionsCheckboxes(
  $module_id,
  $rol_id,
  $value = [],
  $required = true,
  $disabled = false
) {
  global $mysqli;
  global $db_ati;

  $acciones = '';

  $query = "SELECT
      MA.id_modulo_accion,
      MA.id_modulo,
      MA.id_accion,
      A.accion
    FROM {$db_ati}_modulo_acciones AS MA
      INNER JOIN {$db_ati}_acciones AS A ON (MA.id_accion = A.id_accion)
    WHERE
      MA.id_modulo = $module_id
    ORDER BY A.accion
    ASC
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    $acciones .= '<div class="custom-controls-stacked">';

    while ($row = mysqli_fetch_assoc($query_result)) :
      $currentValue = $value["acciones-$rol_id"] ? $value["acciones-$rol_id"] : [];
      $selected = in_array($row['id_modulo_accion'], $currentValue) ? 'checked' : '';

      $acciones .= '
        <label class="custom-control custom-checkbox">
          <input
            class="custom-control-input"
            name="acciones-' . $rol_id . '[]"
            value="' . $row['id_modulo_accion'] . '"
            type="checkbox"
            ' . $selected . '
            ' . ($required ? 'required' : '') . '
            ' . ($disabled ? 'disabled' : '') . '
          >

          <span class="custom-control-label">' . $row['accion'] . '</span>
        </label>
      ';
    endwhile;

    $acciones .= '</div>';
  endif;

  return $acciones;
}

function getRolModuleActionsCheckboxes(
  $module_id,
  $value = [],
  $disabled = false
) {
  global $mysqli;
  global $db_ati;

  $rol_module_actions = '';

  $query = "SELECT
      id_rol,
      rol
    FROM {$db_ati}_roles
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    $rol_module_actions .= '<div class="row">';

    while ($row = mysqli_fetch_assoc($query_result)) :
      $rol_id = $row['id_rol'];
      $rol    = $row['rol'];

      $rol_module_actions .= '<div class="col-12 col-lg-6">';
      $rol_module_actions .= '<label class="form-label mb-3">Permisos para rol "' . $rol . '"</label>';
      $rol_module_actions .= getModuleActionsCheckboxes($module_id, $rol_id, $value, false, $disabled);

      $rol_module_actions .= '<input name="rol_ids[]" value="' . $rol_id . '" type="hidden">';
      $rol_module_actions .= '</div>';
    endwhile;

    $rol_module_actions .= '</div>';
  endif;

  return $rol_module_actions;
}

/* function getRolActionsCheckboxes(
  $value    = [],
  $disabled = false
) {
  global $mysqli;

  $rol_operations = "";

  $query = "SELECT
      id_rol,
      rol
    FROM {$db_ati}_roles
    WHERE rol != 'root'
    ORDER BY rol
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    $rol_operations .= '<div class="row">';

    while ($row = mysqli_fetch_assoc($query_result)) :
      $rol_id = $row['id_rol'];
      $rol    = $row['rol'];

      $rol_operations .= '<div class="col-12 col-lg-6">';
      $rol_operations .= '<label class="form-label mb-3">Permisos para rol "' . $rol . '"</label>';
      $rol_operations .= getActionsCheckboxes([], false, '-' . $rol_id, $disabled);

      $rol_operations .= '<input name="rol_ids[]" value="' . $rol_id . '" type="hidden">';
      $rol_operations .= '</div>';
    endwhile;

    $rol_operations .= '</div>';
  endif;

  return $rol_operations;
} */
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- END CHECKBOXES --//
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- START IDS --//
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
function getModuloActionIds(
  $module_id
) {
  global $mysqli;
  global $db_ati;

  $modulo_actions_ids = [];

  $query = "SELECT
      id_modulo_accion,
      id_modulo,
      id_accion
    FROM {$db_ati}_modulo_acciones
    WHERE id_modulo = $module_id
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      array_push($modulo_actions_ids, $row['id_accion']);
    endwhile;
  endif;

  return $modulo_actions_ids;
}

function getRolModuleActionIds(
  $module_id
) {
  global $mysqli;
  global $db_ati;

  $rol_module_actions = [];

  $query_roles = "SELECT
      id_rol,
      rol
    FROM {$db_ati}_roles
  ";

  $query_roles_result = mysqli_query($mysqli, $query_roles);

  while ($rol = mysqli_fetch_assoc($query_roles_result)) :
    $rol_module_actions["acciones-$rol[id_rol]"] = getRolActionIds($module_id, $rol['id_rol']);
  endwhile;

  return $rol_module_actions;
}

function getRolActionIds(
  $module_id,
  $rol_id
) {
  global $mysqli;
  global $db_ati;

  $actions = [];

  $query = "SELECT
      RMA.id_rol_modulo_accion,
      RMA.id_rol,
      RMA.id_modulo_accion,
      MA.id_accion
    FROM {$db_ati}_rol_modulo_acciones AS RMA
      INNER JOIN {$db_ati}_modulo_acciones AS MA ON (RMA.id_modulo_accion = MA.id_modulo_accion)
    WHERE
      MA.id_modulo  = $module_id AND
      RMA.id_rol    = $rol_id
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($data = mysqli_fetch_assoc($query_result)) :
      array_push($actions, $data['id_modulo_accion']);
    endwhile;
  endif;

  return $actions;
}

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- END IDS --//
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

function updateModuleActionIds(
  $module_id,
  $new_actions = []
) {
  global $mysqli;
  global $db_ati;

  $old_actions = getModuloActionIds($module_id);

  $actions_to_delete = [];
  if (!isEmptyArray($new_actions)) $actions_to_delete = array_diff($old_actions, $new_actions);
  if (isEmptyArray($new_actions))  $actions_to_delete = $old_actions;

  $actions_to_insert = [];
  if (!isEmptyArray($new_actions))  $actions_to_insert = array_diff($new_actions, $old_actions);
  if (count($old_actions) === 0)    $actions_to_insert = $new_actions;

  foreach ($actions_to_delete as $key => $action_id) :
    $query = "DELETE FROM {$db_ati}_modulo_acciones WHERE
      id_modulo = $module_id AND
      id_accion = $action_id
    ";

    mysqli_query($mysqli, $query);
  endforeach;

  foreach ($actions_to_insert as $key => $action_id) :
    $query = "INSERT INTO {$db_ati}_modulo_acciones (
        id_modulo,
        id_accion
      ) VALUES (
        $module_id,
        $action_id
      )
    ";

    mysqli_query($mysqli, $query);
  endforeach;

  return true;
}

function updateRolModuleActionIds(
  $module_id,
  $rol_id,
  $new_actions = []
) {
  global $mysqli;
  global $db_ati;

  $old_actions = getRolActionIds(
    $module_id,
    $rol_id
  );

  $actions_to_delete = [];
  if (!isEmptyArray($new_actions)) $actions_to_delete = array_diff($old_actions, $new_actions);
  if (isEmptyArray($new_actions))  $actions_to_delete = $old_actions;

  $actions_to_insert = [];
  if (!isEmptyArray($new_actions))  $actions_to_insert = array_diff($new_actions, $old_actions);
  if (count($old_actions) === 0)    $actions_to_insert = $new_actions;

  foreach ($actions_to_delete as $key => $action_id) :
    $query = "DELETE FROM {$db_ati}_rol_modulo_acciones WHERE
      id_rol            = $rol_id     AND
      id_modulo_accion  = $action_id
    ";

    mysqli_query($mysqli, $query);
  endforeach;

  foreach ($actions_to_insert as $key => $action_id) :
    $query = "INSERT INTO {$db_ati}_rol_modulo_acciones (
        id_rol,
        id_modulo_accion
      ) VALUES (
        $rol_id,
        $action_id
      )
    ";

    mysqli_query($mysqli, $query);
  endforeach;

  return true;
}

function parseAlertMessage(
  $str,
  $data
) {
  $str = str_replace('[page_title]', $data['modulo'], $str);
  $str = strtolower($str);

  $str = preg_replace_callback('/\b(\w)/u', function ($matches) {
    return ucfirst($matches[0]);
  }, $str);

  return $str;
}

function getFilterActions(
  $module_slug,
  $data = [],
  $settings = []
) {
  global $mysqli;

  $response = '';

  $query = getPageModuleActionsQuery($module_slug, 'filtros');

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows > 0) :
    while ($row = mysqli_fetch_assoc($query_result)) :
      if ($settings[$row['slug']]) {
        $actionssSettings = $settings[$row['slug']];

        if ($actionssSettings["type"]) $row['tipo'] = $actionssSettings["type"];

        if ($actionssSettings["type"] == "customLink") {
          $row['customLink']        = $actionssSettings["customLink"];
          $row['customTargetBlank'] = $actionssSettings["customTargetBlank"];
        }
      }

      $attributes = '';

      if ($row['_blank'] === 'si') $attributes .= ' target="_blank"';

      if ($row['clase_html'] != 'btn-edit' && $row['clase_html'] != 'btn-add' && $row['tipo'] == 'modal') $attributes = 'data-bs-toggle="modal" data-bs-target="#' . $module_slug . '-modal-' . $row['slug'] . '"';
      if ($row['clase_html'] == 'btn-edit' || $row['clase_html'] == 'btn-add' && $row['tipo'] == 'modal') $attributes = 'data-bs-toggle="modal" data-bs-target="#' . $module_slug . '-modal"';

      if ($row['tipo'] === 'modal' && ($row['clase_html'] !== 'btn-edit' && $row['clase_html'] !== 'btn-add')) :
        $row['clase_html'] = $row['clase_html'] . ' btn-modal';
        $attributes .= ' data-modal-action="modal-action-' . $module_slug . '-' . $row['slug'] . '"';
      endif;

      if ($row['tipo'] === 'accion') :
        //$attributes .= 'data-action="action-' . $row['slug'] . '-' . $module_slug . '"';

        $data_action = [
          'action' => $row['slug'],
          'alert' => $row['alerta'],
          'title' => parseAlertMessage($row['alerta_titulo'], $row),
          'message' => parseAlertMessage($row['alerta_mensaje'], $row)
        ];

        $row['clase_html'] = $row['clase_html'] . ' btn-action';
        $attributes .= ' data-action="' . htmlspecialchars(json_encode($data_action)) . '"';
      endif;

      $extra_class = $row['clase_html'] == 'btn-add' ? 'btn-primary' : '';

      if ($row["tipo"] != "customLink") $response .= '
        <button class="btn ' . $row['clase_html'] . ' ' . $extra_class . '" ' . $attributes . ' data-row="' . htmlspecialchars(json_encode($data)) . '" type="button">
          <i class="' . $row['icono'] . '"></i> ' . $row['accion'] . '
        </button>
      ';

      if ($row["tipo"] == "customLink") $response .= '
        <a class="btn ' . $row['clase_html'] . ' ' . $extra_class . '" ' . $attributes . ' data-row="' . htmlspecialchars(json_encode($data)) . '" ' . ($row['customTargetBlank'] === 'si' ? 'target="_blank"' : '') . ' href="' . $row['customLink'] . '">
          <i class="' . $row['icono'] . '"></i> ' . $row['accion'] . '
        </a>
      ';

    /* $response .= '
        <a class="dropdown-item ' . $row['clase_html'] . '" ' . $attributes . ' data-row="' . htmlspecialchars(json_encode($data)) . '" href="javascript:void(0)">
          <i class="' . $row['icono'] . '"></i> ' . $row['accion'] . '
        </a>
      '; */
    endwhile;
  endif;

  return $response;
}

function getModuleDataBySlug(
  $module_slug
) {
  global $mysqli;
  global $db_ati;

  $query = "SELECT
      *
    FROM
      {$db_ati}_modulos
    WHERE
      slug = BINARY '{$module_slug}'
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return false;

  if ($num_rows > 0) :
    $data_modulo = mysqli_fetch_assoc($query_result);

    return $data_modulo;
  endif;
}

function getNewModuleOrder()
{
  global $mysqli;
  global $db_ati;

  $query = "SELECT
      MAX(orden) AS max_order
    FROM {$db_ati}_modulos
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);

  $new_order = $data['max_order'] + 1;

  return $new_order;
}
