<?php
require_once __DIR__ . "/helperresponse.model.php";

/* {prefix}_gastos
  1	id_gasto Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
  2	id_usuario	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
  3	id_sucursal	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
  4	id_gasto_concepto	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
  5 forma_pago  enum('efectivo','cheque','transferencia','tarjeta-credito', 'tarjeta-debito')  No  'efectivo'      Cambiar Cambiar  Eliminar Eliminar
  6	monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
  7	comentarios	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
  8	fecha_hora	datetime			No	current_timestamp()			Cambiar Cambiar	Eliminar Eliminar	
 */

class ExpensesHelper
{
  private $table = DTI . "_gastos";

  private $id;
  private $userId;
  private $branchId;
  private $expenseConceptId;
  private $paymentForm;
  private $amount;
  private $comments;
  private $dateTime;

  public function __construct()
  {
    $this->id               = 0;
    $this->userId           = 0;
    $this->branchId         = 0;
    $this->expenseConceptId = 0;
    $this->paymentForm      = 'efectivo';
    $this->amount           = 0.00;
    $this->comments         = "";
    $this->dateTime         = "";
  }

  /**
   * Getters
   */
  public function getId()
  {
    return $this->id;
  }

  public function getUserId()
  {
    return $this->userId;
  }

  public function getBranchId()
  {
    return $this->branchId;
  }

  public function getExpenseConceptId()
  {
    return $this->expenseConceptId;
  }

  public function getPaymentForm()
  {
    return $this->paymentForm;
  }

  public function getAmount()
  {
    return $this->amount;
  }

  public function getComments()
  {
    return $this->comments;
  }

  public function getDateTime()
  {
    return $this->dateTime;
  }

  /**
   * Setters
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  public function setUserId($userId)
  {
    $this->userId = $userId;
  }

  public function setBranchId($branchId)
  {
    $this->branchId = $branchId;
  }

  public function setExpenseConceptId($expenseConceptId)
  {
    $this->expenseConceptId = $expenseConceptId;
  }

  public function setPaymentForm($paymentForm)
  {
    $this->paymentForm = $paymentForm;
  }

  public function setAmount($amount)
  {
    $this->amount = $amount;
  }

  public function setComments($comments)
  {
    $this->comments = $comments;
  }

  public function setDateTime($dateTime)
  {
    $this->dateTime = $dateTime;
  }

  /**
   * Another methods
   */
  public function from($data)
  {
    if (isset($data['id_gasto']))          $this->setId($data['id_gasto']);
    if (isset($data['id_usuario']))        $this->setUserId($data['id_usuario']);
    if (isset($data['id_sucursal']))       $this->setBranchId($data['id_sucursal']);
    if (isset($data['id_gasto_concepto'])) $this->setExpenseConceptId($data['id_gasto_concepto']);
    if (isset($data['forma_pago']))        $this->setPaymentForm($data['forma_pago']);
    if (isset($data['monto']))             $this->setAmount($data['monto']);
    if (isset($data['comentarios']))       $this->setComments($data['comentarios']);
    if (isset($data['fecha_hora']))        $this->setDateTime($data['fecha_hora']);
  }

  public function toArray()
  {
    return [
      'uid'              => $this->getId(),
      'userId'           => $this->getUserId(),
      'branchId'         => $this->getBranchId(),
      'expenseConceptId' => $this->getExpenseConceptId(),
      'paymentForm'      => $this->getPaymentForm(),
      'amount'           => $this->getAmount(),
      'comments'         => $this->getComments(),
      'dateTime'         => $this->getDateTime()
    ];
  }

  public function create(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $userId           = $this->getUserId();
    $branchId         = $this->getBranchId();
    $expenseConceptId = $this->getExpenseConceptId();
    $paymentForm      = $this->getPaymentForm();
    $amount           = $this->getAmount();
    $comments         = $this->getComments();
    $dateTime         = $this->getDateTime();

    $query  = "INSERT INTO {$this->table} (
        id_usuario,
        id_sucursal,
        id_gasto_concepto,
        forma_pago,
        monto,
        comentarios,
        fecha_hora
      ) VALUES (
        ?, ?, ?, ?, ?, ?, ?
      )
    ";

    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("iiisdss", $userId, $branchId, $expenseConceptId, $paymentForm, $amount, $comments, $dateTime);
      $result = $stmt->execute();

      if ($result) {
        $this->setId($stmt->insert_id);

        $response->status  = "success";
        $response->message = "Registro creado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_EXPENSES_HELPER_CREATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function read($params = []): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    try {
      $page     = $params['page']    ?? 1;
      $perPage  = $params['perPage'] ?? 20;
      $offset   = ($page - 1) * $perPage;

      $term         = $params["term"] ?? null;
      $userId       = $params["userId"] ?? null;
      $branchId     = $params["branchId"] ?? null;
      $paymentForm  = $params["paymentForm"] ?? null;

      $byTerm   = $term ? "comentarios LIKE '%{$term}%'" : "1=1";
      $byUser   = $userId ? "id_usuario = {$userId}" : "1=1";
      $byBranch = $branchId ? "id_sucursal = {$branchId}" : "1=1";
      $byPaymentForm = $paymentForm ? "forma_pago = '{$paymentForm}'" : "1=1";

      $cFrom    = "FROM {$this->table}";
      $cWhere   = "WHERE
          ({$byTerm}) AND
          ({$byUser}) AND
          ({$byBranch}) AND
          ({$byPaymentForm})
      ";

      $query    = "SELECT COUNT(id_gasto) AS total {$cFrom} {$cWhere}";

      $stmt = $mysqli->prepare($query);
      $stmt->execute();

      $result = $stmt->get_result();
      $row    = $result->fetch_assoc();
      $total  = $row["total"];
      $numPages = ceil($total / $perPage);

      $response->data["numPages"] = $numPages;

      if ($total == 0) {
        $response->status  = "success";
        $response->message = "No hay registros disponibles";
        $response->data["total"] = $total;
        $response->data["rows"]  = [];
      }

      if ($total > 0) {
        $query = "SELECT * {$cFrom}
          {$cWhere}
          ORDER BY id_gasto DESC
          LIMIT {$offset}, {$perPage}
        ";

        $stmt = $mysqli->prepare($query);
        $stmt->execute();

        $result  = $stmt->get_result();
        $numRows = $result->num_rows;

        if ($numRows) {
          $rows = [];

          while ($row = $result->fetch_assoc()) {
            $item = new ExpensesHelper();
            $item->from($row);
            $rows[] = $item;
          }

          $response->status  = "success";
          $response->message = "Registros encontrados";
          $response->data["total"] = $total;
          $response->data["rows"]  = $rows;
        }
      }
    } catch (Exception $e) {
      error_log("ERROR_EXPENSES_HELPER_READ: " . $e->getMessage());
    }

    return $response;
  }

  public function update(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id               = $this->getId();
    $userId           = $this->getUserId();
    $branchId         = $this->getBranchId();
    $expenseConceptId = $this->getExpenseConceptId();
    $paymentForm      = $this->getPaymentForm();
    $amount           = $this->getAmount();
    $comments         = $this->getComments();
    $dateTime         = $this->getDateTime();

    $query  = "UPDATE
        {$this->table}
      SET
        id_usuario        = ?,
        id_sucursal       = ?,
        id_gasto_concepto = ?,
        forma_pago        = ?,
        monto             = ?,
        comentarios       = ?,
        fecha_hora        = ?
      WHERE
        id_gasto = ?
    ";

    $stmt = $mysqli->prepare($query);

    try {
      $stmt->bind_param(
        "iiisdssi",
        $userId,
        $branchId,
        $expenseConceptId,
        $paymentForm,
        $amount,
        $comments,
        $dateTime,
        $id
      );
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Registro actualizado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_EXPENSES_HELPER_UPDATE: {$e->getMessage()}");
    }

    return $response;
  }

  public function delete(): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $id = $this->getId();

    $query  = "DELETE FROM {$this->table} WHERE id_gasto = ?";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $result = $stmt->execute();

      if ($result) {
        $response->status  = "success";
        $response->message = "Registro eliminado correctamente.";
      }
    } catch (Exception $e) {
      error_log("ERROR_EXPENSES_HELPER_DELETE: {$e->getMessage()}");
    }

    return $response;
  }

  public function getById($id): HelperResponseModel
  {
    global $mysqli;

    $response = new HelperResponseModel();

    $query  = "SELECT * FROM {$this->table} WHERE id_gasto = ? LIMIT 1";
    $stmt   = $mysqli->prepare($query);

    try {
      $stmt->bind_param("i", $id);
      $stmt->execute();

      $result  = $stmt->get_result();
      $numRows = $result->num_rows;

      if ($numRows) {
        $row = $result->fetch_assoc();

        $this->from($row);

        $response->status  = "success";
        $response->message = "Registro encontrado.";
        $response->data    = $this;
      } else {
        $response->status  = "error";
        $response->message = "No se encontró el registro.";
      }
    } catch (Exception $e) {
      error_log("ERROR_EXPENSES_HELPER_GETBYID: {$e->getMessage()}");
    }

    return $response;
  }
}
