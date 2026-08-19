<?php
function getWebSiteProtocol()
{
  global $_SERVER;

  $protocol = 'http://';

  if (
    isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
  ) {
    $protocol = 'https://';
  } else {
    $protocol = 'http://';
  }

  return $protocol;
}

function getServerName(
  $subdomain = ''
) {
  global $_SERVER;

  // Usamos $_SERVER['HTTP_HOST'] para capturar tanto 'localhost' como ':8000'
  // Es la forma más fiable en entornos con puertos no estándar.
  $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

  // Si se pasa un subdominio, se concatena.
  $server_name = $host . $subdomain;

  return $server_name;
}

function getBasePath(
  $subdomain = ''
) {
  global $_SERVER;

  $base_path = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
  return $base_path;
}

function clearSpecialSpaceStr($str)
{
  $str = str_replace('&nbsp;', ' ', $str);
  $str = str_replace('&nbsp', ' ', $str);

  return $str;
}

function cleanStr(
  $str,
  $priority = 'high'
) {
  if ($str == 'null' || $str == null) return null;

  if ($priority === 'none') return $str;

  if ($priority === 'high' || $priority === 'number') :
    $bad_string = array('select', 'drop', ';', '--', 'insert', 'delete', 'xp_', '%20union%20', '/', '/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', '<iframe', '<applet', '<meta', '<style', '<form', '<img', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'http://', 'https://', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=', '<', '>', 'href=');
  endif;

  if ($priority === 'medium') :
    $bad_string = array('select', 'drop', 'insert', 'delete', 'xp_', '%20union%20', '/', '/union/*', '+union+', 'load_file', 'outfile', 'document.cookie', 'onmouse', '<script', '<iframe', '<applet', '<meta', '<style', '<form', '<img', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'http://', 'https://', 'ftp://', 'smb://', 'onmouseover=', 'onmouseout=');
  endif;

  if ($priority === 'low') :
    $bad_string = array('<script', '<iframe', '<applet', '<', '>', 'href=', 'select', 'drop', 'insert', 'delete');
  endif;

  if ($priority === 'html') :
    $bad_string = array('<script', '<applet', 'select', 'drop', 'insert', 'delete');
  endif;

  if ($priority === 'url') :
    $bad_string = array('<script', '<iframe', '<applet');
  endif;

  $bad_string_size  = count($bad_string);
  $count            = 0;

  while ($count <= $bad_string_size) {
    $str = str_replace($bad_string[$count], '/', $str);
    $count++;
  }

  $str = trim($str);

  return $str;
}

function encrypt(
  $string,
  $key
) {
  $result = '';
  for ($i = 0; $i < strlen($string); $i++) {
    $char = substr($string, $i, 1);
    $keychar = substr($key, ($i % strlen($key)) - 1, 1);
    $char = chr(ord($char) + ord($keychar));
    $result .= $char;
  }
  return base64_encode($result);
}

function decrypt(
  $string,
  $key
) {
  $result = '';
  $string = base64_decode($string);
  for ($i = 0; $i < strlen($string); $i++) {
    $char = substr($string, $i, 1);
    $keychar = substr($key, ($i % strlen($key)) - 1, 1);
    $char = chr(ord($char) - ord($keychar));
    $result .= $char;
  }
  return $result;
}

function isEmptyArray(
  $array = []
) {
  $array = (array)$array;

  $is_array = is_array($array);

  if (!$is_array) return true;

  $count = count($array);

  if ($count === 0) return true;

  return false;
}

function useGetFieldPriority(
  $value_to_find,
  $array
) {
  $is_empty = isEmptyArray($array);

  if ($is_empty) return false;

  foreach ($array as $field_name => $priority) :
    if ($field_name === $value_to_find) return $priority;
  endforeach;

  return false;
}

function useInsertByPost(
  $new_config = [
    'table_name'      => "",
    'extra_fields'    => [],
    'excluded_fields' => ['action', 'place', 'uid'],
    'clean_priority'  => [],
    'required'        => []
  ]
) {
  global $mysqli;
  global $_POST;

  if (!$_POST) return false;

  $config = [
    'table_name'      => "",
    'excluded_fields' => ['action', 'place', 'uid'],
    'extra_fields'    => [],
    'clean_priority'  => [],
    'required'        => []
  ];

  if ($new_config['table_name'])                      $config['table_name']       = $new_config['table_name'];
  if (!isEmptyArray($new_config['excluded_fields']))  $config['excluded_fields']  = array_merge($config['excluded_fields'], $new_config['excluded_fields']);
  if (!isEmptyArray($new_config['extra_fields']))     $config['extra_fields']     = $new_config['extra_fields'];
  if (!isEmptyArray($new_config['clean_priority']))   $config['clean_priority']   = $new_config['clean_priority'];
  if (!isEmptyArray($new_config['required']))         $config['required']         = $new_config['required'];

  # VERIFY REQUIRED VALUES
  foreach ($config['required'] as $key => $value) :
    $is_array         = is_array($value);
    $required_name    = '';
    $required_message = '¡Completa los campos requeridos!';

    if (!$is_array) $required_name = $value;

    if ($is_array) :
      $required_name    = $value['name'];
      $required_message = $value['message'];
    endif;

    $field_value = $_POST[$required_name];

    if (empty($field_value)) return [
      'status'  => 'error',
      'message' => $required_message
    ];
  endforeach;

  # PREPARE POST VALUES
  $values = [];

  foreach ($_POST as $field_name => $value) :
    if (!in_array($field_name, $config['excluded_fields'])) :
      $clean_priority     = 'high';
      $new_clean_priority = useGetFieldPriority($field_name, $config['clean_priority']);

      if ($new_clean_priority) $clean_priority = $new_clean_priority;

      array_push($values, [
        'field_name' => $field_name,
        'value'      => cleanStr($value, $clean_priority)
      ]);
    endif;
  endforeach;

  # ADD EXTRA FIELDS TO VALUES V2
  foreach ($config['extra_fields'] as $field_name => $value) :
    $clean_priority     = 'high';
    $new_clean_priority = useGetFieldPriority($field_name, $config['clean_priority']);

    if ($new_clean_priority) $clean_priority = $new_clean_priority;

    array_push($values, [
      'field_name' => $field_name,
      'value'      => cleanStr($value, $clean_priority)
    ]);
  endforeach;

  # BUILD QUERY
  $counter      = 1;
  $total_fields = count($values);

  $field_names  = "";
  $field_values = "";

  foreach ($values as $key => $fields) :
    $separator = $counter === $total_fields ? "" : ",";
    $field_value = mysqli_real_escape_string($mysqli, $fields['value']);

    $field_names  .= "$fields[field_name]"  . $separator;
    $field_values .= "'$field_value'"       . $separator;

    $counter++;
  endforeach;

  $query = "INSERT INTO $config[table_name] ($field_names) VALUES ($field_values)";

  $query_result = mysqli_query($mysqli, $query);

  if ($query_result)  return [
    'status'  => 'success',
    'id'      => mysqli_insert_id($mysqli)
  ];

  if (!$query_result) return [
    'status'  => 'error',
    'message' => mysqli_error($mysqli)
  ];
};

function useUpdateByPost(
  $new_config = [
    'table_name'      => "",
    'extra_fields'    => [],
    'excluded_fields' => ['action', 'place', 'uid'],
    'clean_priority'  => [],
    "conditions"      => [],
    'required'        => []
  ]
) {
  global $mysqli;
  global $_POST;

  if (!$_POST) return false;

  $config = [
    'table_name'      => "",
    'excluded_fields' => ['action', 'place', 'uid'],
    'extra_fields'    => [],
    'clean_priority'  => [],
    'conditions'      => [],
    'required'        => []
  ];

  if ($new_config['table_name'])                      $config['table_name']       = $new_config['table_name'];
  if (!isEmptyArray($new_config['excluded_fields']))  $config['excluded_fields']  = array_merge($config['excluded_fields'], $new_config['excluded_fields']);
  if (!isEmptyArray($new_config['extra_fields']))     $config['extra_fields']     = $new_config['extra_fields'];
  if (!isEmptyArray($new_config['clean_priority']))   $config['clean_priority']   = $new_config['clean_priority'];
  if ($new_config['conditions'])                      $config['conditions']       = $new_config['conditions'];
  if (!isEmptyArray($new_config['required']))         $config['required']         = $new_config['required'];

  # VERIFY REQUIRED VALUES
  foreach ($config['required'] as $key => $value) :
    $is_array       = is_array($value);
    $required_name  = '';
    $message        = '¡Completa los campos requeridos!';

    if (!$is_array) $required_name = $value;

    if ($is_array) :
      $required_name    = $value[0];
      $required_message = $value[1];

      if (!empty($required_message)) $message = $required_message;
    endif;

    $field_value = $_POST[$required_name];

    if (empty($field_value)) return [
      'status'  => 'error',
      'message' => $message
    ];
  endforeach;

  # PREPARE POST VALUES
  $values = [];

  foreach ($_POST as $field_name => $value) :
    if (!in_array($field_name, $config['excluded_fields'])) :
      $clean_priority     = 'high';
      $new_clean_priority = useGetFieldPriority($field_name, $config['clean_priority']);

      if ($new_clean_priority) $clean_priority = $new_clean_priority;

      array_push($values, [
        'field_name' => $field_name,
        'value'      => cleanStr($value, $clean_priority)
      ]);
    endif;
  endforeach;

  # ADD EXTRA FIELDS TO VALUES
  foreach ($config['extra_fields'] as $field_name => $value) :
    $clean_priority     = 'high';
    $new_clean_priority = useGetFieldPriority($field_name, $config['clean_priority']);

    if ($new_clean_priority) $clean_priority = $new_clean_priority;

    array_push($values, [
      'field_name' => $field_name,
      'value'      => cleanStr($value, $clean_priority)
    ]);
  endforeach;

  # BUILD QUERY
  $counter      = 1;
  $total_fields = count($values);

  $fields = "";

  foreach ($values as $key => $field) :
    $separator    = $counter === $total_fields ? "" : ", ";
    $field_value  = mysqli_real_escape_string($mysqli, $field['value']);

    $fields .= "$field[field_name] = '$field_value'" . $separator;

    $counter++;
  endforeach;

  # CONDITIONS
  # WHERE
  $c_where        = "WHERE ";
  $total_clauses  = count($config['conditions']);

  if (count($config['conditions']) > 0) :
    foreach ($config['conditions'] as $key => $value) :
      $field_name   = $value[0];
      $field_value  = $value[1];
      $field_rule   = $value[2] ? $value[2] : "=";
      $field_concat = $value[3] ? $value[3] : "AND";
      $concat       = (($total_clauses > 1) && ($key + 1 <= $total_clauses) && ($key != 0)) ? $field_concat : "";

      if ($field_rule === "=")        $c_where .= $concat . " ($field_name =        '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
      if ($field_rule === "!=")       $c_where .= $concat . " ($field_name !=       '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
      if ($field_rule === 'LIKE')     $c_where .= $concat . " ($field_name LIKE     '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
      if ($field_rule === "IN")       $c_where .= $concat . " ($field_name IN       (" . mysqli_real_escape_string($mysqli, $field_value) . ")" . ") ";
      if ($field_rule === 'BETWEEN')  $c_where .= $concat . " ($field_name BETWEEN  '" . mysqli_real_escape_string($mysqli, $field_value[0]) . "' AND '" . mysqli_real_escape_string($mysqli, $field_value[1]) . "'" . ") ";
    endforeach;
  endif;

  $query = "UPDATE $config[table_name] SET $fields $c_where";

  $query_result = mysqli_query($mysqli, $query);

  if ($query_result)  return [
    'status' => 'success',
    'query'  => $query
  ];

  if (!$query_result) return [
    'status'  => 'error',
    'message' => mysqli_error($mysqli),
    'query'   => $query
  ];
};

function getEmptyTableMessage(
  $message = '¡No hay registros disponibles!'
) {
  return '
    <div class="col-xs-12 text-center" style="margin: 2rem;">
      ' . $message . '
    </div>
  ';
}

function useDataTable(
  $new_config = [
    'column_id'     => "",
    'from'          => "",
    'where'         => [],
    'fields'        => [],
    'join'          => "",
    'extra_clauses' => "",
    'per_page'      => 15,
    'page'          => 1
  ]
) {
  global $mysqli;

  $config = [
    'column_id'     => "",
    'from'          => "",
    'where'         => [],
    'fields'        => [],
    'join'          => "",
    'extra_clauses' => "",
    'per_page'      => 15,
    'page'          => 1
  ];

  if ($new_config['column_id'])     $config['column_id']      = $new_config['column_id'];
  if ($new_config['from'])          $config['from']           = $new_config['from'];
  if ($new_config['where'])         $config['where']          = $new_config['where'];
  if ($new_config['fields'])        $config['fields']         = $new_config['fields'];
  if ($new_config['join'])          $config['join']           = $new_config['join'];
  if ($new_config['extra_clauses']) $config['extra_clauses']  = $new_config['extra_clauses'];
  if ($new_config['per_page'])      $config['per_page']       = $new_config['per_page'];
  if ($new_config['page'])          $config['page']           = $new_config['page'];

  $column_id    = $config['column_id'];
  $per_page     = $config['per_page'];
  $page         = $config['page'];

  $fields       = $config['fields'];
  $where        = $config['where'];

  $c_from       = "FROM " . $config['from'];
  $c_join       = $config['join'];
  $c_extra_clauses      = $config['extra_clauses'];

  # WHERE
  $c_where        = "";
  $total_clauses  = count($where);

  if (count($where) > 0) :
    $c_where = "WHERE ";

    foreach ($where as $key => $value) :
      $is_empty_array = isEmptyArray($value);

      if (!$is_empty_array) :
        if (!is_array($value[0])) :
          $field_name   = $value[0];
          $field_value  = $value[1];
          $field_rule   = $value[2] ? $value[2] : "=";
          $field_concat = $value[3] ? $value[3] : "AND";
          $concat       = (($total_clauses > 1) && ($key + 1 <= $total_clauses) && ($key != 0)) ? $field_concat : "";

          if ($field_rule === "=")            $c_where .= $concat . " ($field_name =        '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
          if ($field_rule === ">")            $c_where .= $concat . " ($field_name >        '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
          if ($field_rule === "<")            $c_where .= $concat . " ($field_name <        '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
          if ($field_rule === ">=")           $c_where .= $concat . " ($field_name >=       '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
          if ($field_rule === "<=")           $c_where .= $concat . " ($field_name <=       '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
          if ($field_rule === "!=")           $c_where .= $concat . " ($field_name !=       '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
          if ($field_rule === 'LIKE')         $c_where .= $concat . " ($field_name LIKE     _utf8'" . mysqli_real_escape_string($mysqli, $field_value) . "' collate utf8_unicode_ci" . ") ";
          if ($field_rule === "IN")           $c_where .= $concat . " ($field_name IN       (" . mysqli_real_escape_string($mysqli, $field_value) . ")" . ") ";
          if ($field_rule === "NOT IN")       $c_where .= $concat . " ($field_name NOT IN   (" . mysqli_real_escape_string($mysqli, $field_value) . ")" . ") ";
          if ($field_rule === 'BETWEEN')      $c_where .= $concat . " ($field_name BETWEEN  '" . mysqli_real_escape_string($mysqli, $field_value[0]) . "' AND '" . mysqli_real_escape_string($mysqli, $field_value[1]) . "'" . ") ";

          if ($field_rule === "IS NULL")      $c_where .= $concat . " ($field_name IS NULL) ";
          if ($field_rule === "IS NOT NULL")  $c_where .= $concat . " ($field_name IS NOT NULL) ";
        endif;

        if (is_array($value[0])) :
          $total_clauses_2  = count($value[0]);
          $second_concat    = !empty($value[1]) ? $value[1] : "AND";
          if ($key === 0) $second_concat = "";
          $c_where          .= $second_concat . "(";

          foreach ($value[0] as $key_2 => $second_value) :
            $is_empty_array = isEmptyArray($second_value);

            if (!$is_empty_array) :
              $field_name   = $second_value[0];
              $field_value  = $second_value[1];
              $field_rule   = $second_value[2] ? $second_value[2] : "=";
              $field_concat = $second_value[3] ? $second_value[3] : "AND";
              $concat       = (($total_clauses_2 > 1) && ($key_2 + 1 <= $total_clauses_2) && ($key_2 != 0)) ? $field_concat : "";

              if ($field_rule === "=")        $c_where .= $concat . " ($field_name =        '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
              if ($field_rule === ">")        $c_where .= $concat . " ($field_name >        '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
              if ($field_rule === "<")        $c_where .= $concat . " ($field_name <        '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
              if ($field_rule === ">=")       $c_where .= $concat . " ($field_name >=       '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
              if ($field_rule === "<=")       $c_where .= $concat . " ($field_name <=       '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
              if ($field_rule === "!=")       $c_where .= $concat . " ($field_name !=       '" . mysqli_real_escape_string($mysqli, $field_value) . "'" . ") ";
              if ($field_rule === 'LIKE')     $c_where .= $concat . " ($field_name LIKE     _utf8'" . mysqli_real_escape_string($mysqli, $field_value) . "' collate utf8_unicode_ci" . ") ";
              if ($field_rule === "IN")       $c_where .= $concat . " ($field_name IN       (" . mysqli_real_escape_string($mysqli, $field_value) . ")" . ") ";
              if ($field_rule === "NOT IN")   $c_where .= $concat . " ($field_name NOT IN   (" . mysqli_real_escape_string($mysqli, $field_value) . ")" . ") ";
              if ($field_rule === 'BETWEEN')  $c_where .= $concat . " ($field_name BETWEEN  '" . mysqli_real_escape_string($mysqli, $field_value[0]) . "' AND '" . mysqli_real_escape_string($mysqli, $field_value[1]) . "'" . ") ";

              if ($field_rule === "IS NULL")      $c_where .= $concat . " ($field_name IS NULL) ";
              if ($field_rule === "IS NOT NULL")  $c_where .= $concat . " ($field_name IS NOT NULL) ";
            endif;
          endforeach;

          $c_where .= ")";
        endif;
      endif;
    endforeach;
  endif;

  $start_rows   = ($page - 1) * $per_page;
  $stop_rows    = $per_page;

  $c_limit_rows = "LIMIT $start_rows, $stop_rows";

  $query        = "SELECT $column_id $c_from $c_join $c_where $c_extra_clauses";

  $query_result = mysqli_query($mysqli, $query);
  $num_rows     = mysqli_num_rows($query_result);

  if ($num_rows == 0) return [
    'status'  => 'error',
    'query'   => $query
  ];

  $num_pages    = ceil($num_rows / $stop_rows);

  # FIELDS
  $c_fields     = "";
  $total_fields = count($fields);

  foreach ($fields as $key => $field) :
    $is_array = is_array($field);

    if (!$is_array) $c_fields .= $field;

    if ($is_array) :
      $field_name   = $field[0];
      $field_rename = $field[1];
      $c_fields    .= "$field_name AS $field_rename";
    endif;

    if (($key + 1) < $total_fields) $c_fields .= ",";
  endforeach;

  $query        = "SELECT $c_fields $c_from $c_join $c_where $c_extra_clauses $c_limit_rows";
  $query_result = mysqli_query($mysqli, $query);

  return [
    'status'        => 'success',
    'query'         => $query,
    'query_result'  => $query_result,
    'num_pages'     => $num_pages,
    "total"         => $num_rows
  ];
}

/* function closeSession()
{
  global $_SESSION;

  $_SESSION = array();

  if (ini_get("session.use_cookies")) :
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params["path"],
      $params["domain"],
      $params["secure"],
      $params["httponly"]
    );
  endif;

  session_destroy();

  header('location:' . BASE_URL . '/login');
  die;
} */

function closeSession()
{
  global $_SESSION;
  global $_COOKIE;

  $_SESSION = array();

  if (ini_get("session.use_cookies")) :
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params["path"],
      $params["domain"],
      $params["secure"],
      $params["httponly"]
    );
  endif;

  /* unset($_COOKIE[COOKIE_USER_SESSION_COOKIE_NAME]);
  unset($_COOKIE[COOKIE_BOT_SESSION_COOKIE_NAME]);

  setcookie(COOKIE_USER_SESSION_COOKIE_NAME, null, -1, BASE_URL);
  setcookie(COOKIE_BOT_SESSION_COOKIE_NAME, null, -1, BASE_URL); */

  unset($_COOKIE[COOKIE_SESSION_COOKIE_NAME]);
  setcookie(COOKIE_SESSION_COOKIE_NAME, "", -1, BASE_URL);

  session_destroy();

  header('location:' . BASE_URL . '/login');
  die;
}

function formatPhoneNumber($phoneNumber)
{
  $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

  if (strlen($phoneNumber) > 10) {
    $countryCode = substr($phoneNumber, 0, strlen($phoneNumber) - 10);
    $areaCode = substr($phoneNumber, -10, 3);
    $nextThree = substr($phoneNumber, -7, 3);
    $lastFour = substr($phoneNumber, -4, 4);

    $phoneNumber = '+' . $countryCode . ' (' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
  } else if (strlen($phoneNumber) == 10) {
    $areaCode = substr($phoneNumber, 0, 3);
    $nextThree = substr($phoneNumber, 3, 3);
    $lastFour = substr($phoneNumber, 6, 4);

    $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
  } else if (strlen($phoneNumber) == 7) {
    $nextThree = substr($phoneNumber, 0, 3);
    $lastFour = substr($phoneNumber, 3, 4);

    $phoneNumber = $nextThree . '-' . $lastFour;
  }

  return $phoneNumber;
}

function removeAllCharactersExceptNumbers(
  $str
) {
  $str = preg_replace('/[^0-9.]+/', '', $str);

  return $str;
}

function processFile(
  $file,
  $extensions,
  $folder,
  $name = 'image',
  $full_name = null
) {
  $today_date = date('dmYHis');

  $file_name = $file['name'];
  $file_tmp_name = $file['tmp_name'];

  $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
  $file_name_without_extension = pathinfo($file_name, PATHINFO_FILENAME);

  //$new_file_name = $file_name_without_extension . '-' . $today_date . '.' . $file_extension;

  //$new_file_name = "manteleslargos$name-$today_date.$file_extension";
  $new_file_name = 'file_' . $name . '_' . $today_date . '.' . $file_extension;

  if ($full_name) $new_file_name = $full_name;

  $file_with_folder = $folder . $new_file_name;

  if (!in_array($file_extension, $extensions)) {
    return 'no-valid';
  }

  $move_file = move_uploaded_file($file_tmp_name, $file_with_folder);

  if (!$move_file) {
    return 'no-move';
  }

  return $new_file_name;
}

function createSlug(
  $str,
  $max = 100
) {
  $str = trim($str);

  $out = str_replace('año', 'anio', $str);
  $out = iconv('UTF-8', 'ASCII//TRANSLIT', $out);
  $out = substr(preg_replace('/[^-\/+|\w ]/', '', $out), 0, $max);
  $out = strtolower(trim($out, '-'));
  $out = preg_replace('/[\/_| -]+/', '-', $out);
  $out = str_replace('+', 'mas', $out);

  return $out;
}

function deleteFile(
  $file_location
) {
  $file_exist = file_exists($file_location);

  if (!$file_exist) {
    return 'deleted';
  }

  if ($file_exist) {
    $file_unlink = unlink($file_location);

    if ($file_unlink) {
      return 'deleted';
    }

    if (!$file_unlink) {
      return 'not-deleted';
    }
  }
}

function getMonthNameByMonthNumber(
  $numero_mes
) {
  $numero = intval($numero_mes) - 1;
  $meses  = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
  $mes    = $meses[$numero];

  return $mes;
}

function getNumDaysOfMonth(
  $mes,
  $anio
) {
  $nuemro_dias = (int)date('t', mktime(0, 0, 0, (int)$mes, 1, (int)$anio));
  return $nuemro_dias;
}

function getNumDaysArrayOfMonth(
  $mes,
  $anio
) {
  $numero_dias  = getNumDaysOfMonth($mes, $anio);
  $dias         = [];

  for ($i = 1; $i <= $numero_dias; $i++) :
    $dias[] = $i;
  endfor;

  return $dias;
}

function sendEmail(
  $config = [
    'access' => [
      'name'     => null,
      'username' => null,
      'password' => null,
    ],
    'mail'              => null,
    'to'                => [],
    'subject'           => '',
    'message'           => '',
    'replyTo'           => null,
    'string_attachment' => null,
    'attachment'        => []
  ]
) {
  try {
    //$mail = new PHPMailer(true);
    $response = [
      "status"  => "error",
      "message" => "Error inesperado, intentelo nuevamente"
    ];

    $mail = $config['mail'] ? $config['mail'] : new stdClass();

    $mail->SMTPDebug = 0;
    //$mail->isSMTP();
    $mail->Host       = PHPMAILER_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['from']['username'];
    $mail->Password   = $config['from']['password'];
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 465;

    $mail->CharSet = "UTF-8";

    if (!empty($config['replyTo'])) $mail->addReplyTo($config['replyTo']);

    if ($config['string_attachment']) :
      $attachment = $config['string_attachment']['attachment'];
      $filename   = $config['string_attachment']['filename'];
      $type       = $config['string_attachment']['type'];
      $extension  = $config['string_attachment']['extension'];

      $mail->AddStringAttachment($attachment, $filename, $type, $extension);
    endif;

    if ($config["attachment"]) :
      foreach ($config["attachment"] as $attachment) :
        $mail->addAttachment($attachment);
      endforeach;
    endif;

    $mail->setFrom($config['from']['username'], $config['from']['name']);

    foreach ($config['to'] as $row) :
      $mail->addAddress($row['email'], $row['name']);
    endforeach;

    $mail->isHTML(true);
    $mail->Subject = $config['subject'];
    $mail->Body    = $config['message'];

    error_log(json_encode($mail));
    $send = $mail->send();


    if ($send) $response = [
      'status'  => 'success',
      'message' => 'Mensaje enviado correctamente'
    ];
  } catch (Exception $e) {
    $response = [
      'status'  => 'error',
      'message' => $e->getMessage()
    ];
  }

  return $response;
}

function removeTrailingZeros($number)
{
  // Convertir el número a una cadena para tratarlo como texto
  $numberStr = (string) $number;

  // Usar rtrim para eliminar los ceros al final del número
  $trimmedNumber = rtrim($numberStr, '0');

  // Verificar si el último caracter es un punto y eliminarlo si es necesario
  if (substr($trimmedNumber, -1) === '.') {
    $trimmedNumber = substr($trimmedNumber, 0, -1);
  }

  // Convertir de regreso a un número (entero o decimal)
  return strpos($trimmedNumber, '.') !== false ? (float) $trimmedNumber : (int) $trimmedNumber;
}

// ej: ["date" => "dd/mm/yyyy", "time] => "hh:mm AM/PM"]
function parseDateTimeToSpanishParts($datetime)
{
  $date = date('d/m/Y', strtotime($datetime));
  $time = date('h:i A', strtotime($datetime));

  return [
    "date" => $date,
    "time" => $time
  ];
}

function renderComponent(
  $name,
  $data = []
) {
  $basePath = BASE_PATH;

  extract($data);

  $componentPath = $basePath . "/src/components/$name.php";

  if (file_exists($componentPath)) :
    include $componentPath;
  else :
    echo "Component not found: $name";
  endif;
}

function getComponent(
  $name,
  $data = []
) {
  ob_start();
  renderComponent($name, $data);
  $output = ob_get_clean();
  return $output;
}

function renderToString($render)
{
  ob_start();
  echo $render;
  return ob_get_clean();
}
