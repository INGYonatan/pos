<?php
require_once __DIR__ . "/apiresponse.model.php";
//require_once __DIR__ . "/../../models/users.model.php";

class AccessApiController
{
  private $usersModel;

  public function __construct()
  {
    //$this->usersModel = new UsersModel();

    $httpAuthorization = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    if (!$httpAuthorization) {
      header("HTTP/1.1 401 Unauthorized");
      die;
    }

    $authHeader   = $_SERVER['HTTP_AUTHORIZATION'];
    $accessToken  = str_replace("Bearer ", "", $authHeader);

    //$this->usersModel->getByApiAccessToken($accessToken);

    // if (!$this->usersModel->getId()) {
    //   header("HTTP/1.1 401 Unauthorized");
    //   die;
    // }

    if ($accessToken != "POS_CHATBOTVELOZ_2025") {
      header("HTTP/1.1 401 Unauthorized");
      die;
    }
  }

  // public function getUsersModel()
  // {
  //   return $this->usersModel;
  // }

  // public function validateModuleActionPermission($module, $action)
  // {
  //   global $mysqli;

  //   $userId = $this->usersModel->getId();

  //   if ($userId == 1) return true;

  //   $query = "SELECT
  //       RMA.id_rol_modulo_accion,
  //       RMA.id_rol,
  //       RMA.id_modulo_accion,
  //       M.id_modulo
  //     FROM adm_rol_modulo_acciones AS RMA
  //       INNER JOIN adm_modulo_acciones AS MA ON (RMA.id_modulo_accion  = MA.id_modulo_accion)
  //       INNER JOIN adm_acciones        AS A  ON (MA.id_accion          = A.id_accion)
  //       INNER JOIN adm_modulos         AS M  ON (MA.id_modulo          = M.id_modulo)
  //       INNER JOIN adm_usuarios        AS U  ON (RMA.id_rol            = U.id_rol)
  //     WHERE
  //       U.id_usuario  = $userId   AND
  //       M.slug        = '$module' AND
  //       A.slug        = '$action'
  //     LIMIT 1
  //   ";

  //   $result   = mysqli_query($mysqli, $query);
  //   $numRows  = mysqli_num_rows($result);

  //   if ($numRows === 0) {
  //     header("HTTP/1.1 403 Forbidden");
  //     die;
  //   }
  // }

  // public function validateReadPermission($module)
  // {
  //   $this->validateModuleActionPermission($module, 'ver');
  // }

  // public function validateCreatePermission($module)
  // {
  //   $this->validateModuleActionPermission($module, 'crear');
  // }

  // public function validateUpdatePermission($module)
  // {
  //   $this->validateModuleActionPermission($module, 'editar');
  // }

  // public function validateDeletePermission($module)
  // {
  //   $this->validateModuleActionPermission($module, 'eliminar');
  // }
}
