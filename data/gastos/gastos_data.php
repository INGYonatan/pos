<?php
require_once __DIR__ . '/../lib/settings.inc.php';
require_once __DIR__ . "/../lib/helpers/expenses.helper.php";
require_once __DIR__ . "/../lib/helpers/expense-concepts.helper.php";

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];
$user_data   = getUserData(get_id_usuario());

$action      = $_POST['action'];
$identifier  = 'gastos';
$IS_ADMIN    = $user_data['IS_ADMIN'] == 'si' ? true : false;

$model = new ExpensesHelper();

switch ($action) {
  case "load-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'ver')) break;

    $haveActions = haveActions($identifier, 'tabla');

    $page             = $_POST['page'];
    $per_page         = $_POST['perPage'];

    $term             = cleanStr($_POST['search']);
    $branchId         = $IS_ADMIN ? cleanStr($_POST['branchId']) : getSessionBranchOfficeId();
    $expenseConceptId = cleanStr($_POST['expenseConceptId']);
    $paymentForm      = cleanStr($_POST['paymentForm']);

    $result = $model->read([
      "term"              => $term,
      "branchId"          => $branchId,
      "expenseConceptId"  => $expenseConceptId,
      "paymentForm"       => $paymentForm,
      "page"              => $page,
      "perPage"           => $per_page
    ]);

    if ($result->status == "error") {
      echo getEmptyTableMessage();
      exit;
    }

    $rows       = $result->data['rows'];
    $totalRows  = $result->data['total'];

    if (count($rows) == 0) {
      echo getEmptyTableMessage();
      exit;
    }

    include __DIR__ . "/{$identifier}_table.php";

    exit;
    break;

  case "add-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'agregar')) break;

    $userId           = get_id_usuario();
    $date             = date("Y-m-d H:i:s", strtotime(cleanStr($_POST['date'])));
    $branchId         = $IS_ADMIN ? cleanStr($_POST['branchId']) : getSessionBranchOfficeId();
    $expenseConceptId = cleanStr($_POST['expenseConceptId']);
    $amount           = floatval(cleanStr($_POST['amount']));
    $paymentForm      = cleanStr($_POST['paymentForm']);
    $comments         = cleanStr($_POST['comments']);

    $model->setUserId($userId);
    $model->setBranchId($branchId);
    $model->setExpenseConceptId($expenseConceptId);
    $model->setDateTime($date);
    $model->setAmount($amount);
    $model->setPaymentForm($paymentForm);
    $model->setComments($comments);

    $result = $model->create();

    if ($result->status == "success") $response = [
      "status"        => "success",
      "toastMessage"  => "¡Gasto agregado exitosamente!",
      "callback"      => 'load("' . $page . '", "' . $identifier . '");'
    ];
    break;

  case "edit-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'editar')) break;

    $id               = cleanStr($_POST['uid']);
    $userId           = get_id_usuario();
    $date             = date("Y-m-d H:i:s", strtotime(cleanStr($_POST['date'])));
    $branchId         = $IS_ADMIN ? cleanStr($_POST['branchId']) : getSessionBranchOfficeId();
    $expenseConceptId = cleanStr($_POST['expenseConceptId']);
    $amount           = floatval(cleanStr($_POST['amount']));
    $paymentForm      = cleanStr($_POST['paymentForm']);
    $comments         = cleanStr($_POST['comments']);

    $model->getById($id);

    if (!$model->getId()) break;

    $model->setId($id);
    $model->setUserId($userId);
    $model->setBranchId($branchId);
    $model->setExpenseConceptId($expenseConceptId);
    $model->setDateTime($date);
    $model->setAmount($amount);
    $model->setPaymentForm($paymentForm);
    $model->setComments($comments);

    $result = $model->update();

    if ($result->status == "success") $response = [
      "status"        => "success",
      "toastMessage"  => "¡Gasto editado exitosamente!",
      "callback"      => 'load("' . $page . '", "' . $identifier . '");'
    ];
    break;

  case "action-eliminar-{$identifier}":
    if (!checkModuleActionPermission($identifier, 'eliminar')) break;

    $id = cleanStr($_POST['uid']);

    $model->getById($id);

    if (!$model->getId()) break;

    $result = $model->delete();

    if ($result->status == "success") $response = [
      "status"        => "success",
      "toastMessage"  => "¡Gasto eliminado exitosamente!",
      "callback"      => 'load("' . $page . '", "' . $identifier . '");'
    ];
    break;
}

mysqli_close($mysqli);
echo json_encode($response);
exit;
