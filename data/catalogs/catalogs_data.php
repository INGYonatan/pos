<?php
require '../lib/settings.inc.php';
require '../lib/helpers/catalogs.helper.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action = $_POST['action'];

switch ($action) {
  case 'get-customer-addresses':
    $customerId = cleanStr($_POST['customerId']);
    $catalog    = catalog_get_customer_addresses($customerId);

    $response = [
      'status'  => 'success',
      'catalog' => $catalog
    ];
    break;

  case 'get-brands':
    $selctedValue = cleanStr($_POST['selectedValue']);
    $catalog      = getBrandsCatalog($selctedValue);

    $response = [
      'status'  => 'success',
      'catalog' => $catalog
    ];
    break;

  case 'get-brand-categories':
    $brandId        = cleanStr($_POST['value']);
    $selectedValue  = cleanStr($_POST['selectedValue']);
    $catalog        = catalog_get_brand_categories($brandId, $selectedValue);

    $response = [
      'status'  => 'success',
      'catalog' => $catalog
    ];
    break;

  case 'get-category-families':
    $categoryId     = cleanStr($_POST['value']);
    $selectedValue  = cleanStr($_POST['selectedValue']);
    $catalog        = catalog_get_families($categoryId, $selectedValue);

    $response = [
      'status'  => 'success',
      'catalog' => $catalog
    ];
    break;

  case 'get-suppliers':
    $selectedValue  = cleanStr($_POST['selectedValue']);
    $catalog        = getSupplierCatalog($selectedValue);

    $response = [
      'status'  => 'success',
      'catalog' => $catalog
    ];
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
