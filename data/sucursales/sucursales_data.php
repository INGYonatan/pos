<?php

require '../lib/settings.inc.php';



$response = [

  'status'        => 'error',

  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'

];



$action           = $_POST['action'];

$identifier       = 'sucursales';



$extensiones_permitidas = ['jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG'];

$carpeta_logo_sucursales = BASE_PATH . '/src/assets/images/sucursales/';

$id_almacen              = getStoreId();



switch ($action) {

  case 'load-' . $identifier:

    $have_actions     = haveActions($identifier, 'tabla');



    $per_page         = $_POST['perPage'];

    $page             = $_POST['page'];



    $search           = cleanStr($_POST['search']);



    $column_id        = "id_sucursal";

    $c_from           = "{$db_dti}_sucursales";

    $c_extra_clauses  = "ORDER BY nombre_sucursal ASC";



    $fields = [

      "id_sucursal",

      ["id_sucursal", "uid"],

      "nombre_sucursal",
      "nombre_comercial",
      "logo",

      "correo",
      "telefono",

      "direccion",

      "numero_serie",

      "tipo",

      "cp"

    ];



    $c_join = "";



    $c_where = [

      //["tipo", "almacen", "!="],

      ["status", "activo"]

    ];



    if (!empty($search)) array_push($c_where, [

      [

        ["nombre_sucursal",  "%$search%", "LIKE", "OR"],

        ["telefono",  "%$search%", "LIKE", "OR"],

        ["direccion",  "%$search%", "LIKE", "OR"]

      ]

    ]);



    $request = useDataTable([

      'column_id'     => $column_id,

      'from'          => $c_from,

      'where'         => $c_where,

      'fields'        => $fields,

      'join'          => $c_join,

      'extra_clauses' => $c_extra_clauses,

      'per_page'      => $per_page,

      'page'          => $page

    ]);



    //echo getEmptyTableMessage($request);

    //die;



    if ($request['status'] === 'error')   echo getEmptyTableMessage();

    if ($request['status'] === 'success') include $identifier . '_table.php';

    die;

    break;



  case 'add-' . $identifier:
    if (checkModuleActionPermission($identifier, 'agregar')) :

      $extra_fields_add = [];

      // Procesar logo si viene en la petición
      $logo_file = $_FILES['logo'] ?? null;

      if (!empty($logo_file['name'])) :
        $logo_result = processFile($logo_file, $extensiones_permitidas, $carpeta_logo_sucursales);

        if ($logo_result === 'no-valid') :
          $response['toastMessage'] = '¡Archivo no permitido! Solo se permiten imágenes JPG y PNG.';
          break;
        endif;

        if ($logo_result === 'no-move') :
          $response['toastMessage'] = '¡Error al subir el logo!, inténtalo nuevamente.';
          break;
        endif;

        $extra_fields_add['logo'] = $logo_result;
      endif;

      $request = useInsertByPost([
        'table_name'   => "{$db_dti}_sucursales",
        'extra_fields' => $extra_fields_add
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') :
        $query_invetariopn = addBranchOfficeOnInventory($request['id']);

        $response = [
          'status'       => 'success',
          'toastMessage' => 'La sucursal se agregó correctamente',
          'callback'     => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;



  case 'edit-' . $identifier:
    if (checkModuleActionPermission($identifier, 'editar')) :
      $id_sucursal = cleanStr($_POST['uid']);

      $extra_fields_edit    = [];
      $excluded_fields_edit = [];

      // Procesar logo si viene en la petición
      $logo_file = $_FILES['logo'] ?? null;

      if (!empty($logo_file['name'])) :
        $logo_result = processFile($logo_file, $extensiones_permitidas, $carpeta_logo_sucursales);

        if ($logo_result === 'no-valid') :
          $response['toastMessage'] = '¡Archivo no permitido! Solo se permiten imágenes JPG y PNG.';
          break;
        endif;

        if ($logo_result === 'no-move') :
          $response['toastMessage'] = '¡Error al subir el logo!, inténtalo nuevamente.';
          break;
        endif;

        // Eliminar logo anterior si existe
        $query_logo_actual = mysqli_query($mysqli, "SELECT logo FROM {$db_dti}_sucursales WHERE id_sucursal = {$id_sucursal} LIMIT 1");
        $row_logo          = mysqli_fetch_assoc($query_logo_actual);

        if (!empty($row_logo['logo'])) :
          deleteFile($carpeta_logo_sucursales . $row_logo['logo']);
        endif;

        $extra_fields_edit['logo'] = $logo_result;
      else :
        // No viene archivo, excluir el campo para no sobreescribir con vacío
        $excluded_fields_edit[] = 'logo';
      endif;

      $request = useUpdateByPost([
        'table_name'      => "{$db_dti}_sucursales",
        'conditions'      => [['id_sucursal', $id_sucursal]],
        'extra_fields'    => $extra_fields_edit,
        'excluded_fields' => $excluded_fields_edit
      ]);

      if ($request['status'] === 'error') $response['toastMessage'] = $request['message'];

      if ($request['status'] === 'success') $response = [
        'status'       => 'success',
        'toastMessage' => 'La sucursal se actualizó correctamente',
        'callback'     => 'load("' . $page . '", "' . $identifier . '");'
      ];
    endif;
    break;



  case 'action-eliminar-' . $identifier:

    if (checkModuleActionPermission($identifier, 'eliminar')) :

      $id_sucursal = cleanStr($_POST['uid']);



      $query = "UPDATE {$db_dti}_sucursales SET

            status = 'eliminado'

          WHERE

            id_sucursal = {$id_sucursal}

        ";



      $query_result = mysqli_query($mysqli, $query);



      if ($query_result) $response = [

        'status'        => 'success',

        'toastMessage'  => 'La sucursal se eliminó correctamente',

        'callback'      => 'load("' . $page . '", "' . $identifier . '");'

      ];

    endif;

    break;
}



echo json_encode($response);

mysqli_close($mysqli);

die;
