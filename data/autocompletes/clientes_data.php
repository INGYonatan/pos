<?php
require_once __DIR__ . "/../lib/settings.inc.php";
require_once __DIR__ . "/../lib/helpers/customers.helper.php";

$term = cleanStr($_GET['term']);

$customersHelper  = new CustomerHelper();
$customersCatalog = $customersHelper->getForSelect2($term);

$catalog = new stdClass();
$catalog->results = [];

$catalog->pagination = new stdClass();
$catalog->pagination->more = false;

$id    = "";
$label = "--Seleccionar--";

$result       = new stdClass();
$result->id   = $id;
$result->text = $label;

$catalog->results[] = $result;

/* private $taxRegimeId; */
/* private $name; */
/* private $commercialName; */
/* private $requireInvoice; */
/* private $type; */
/* private $businessName; */
/* private $rfc; */
/* private $taxResidence; */
/* private $email; */
/* private $phone; */
/* private $creationDate; */
/* private $creationDateFormat; */
/* private $status; */

foreach ($customersCatalog as $item) :
  $result                     = new stdClass();
  $result->id                 = $item->getId();
  $result->text               = $item->getName();
  $result->taxRegimeId        = $item->getTaxRegimeId();
  $result->name               = $item->getName();
  $result->commercialName     = $item->getCommercialName();
  $result->requireInvoice     = $item->getRequireInvoice();
  $result->type               = $item->getType();
  $result->businessName       = $item->getBusinessName();
  $result->rfc                = $item->getRfc();
  $result->taxResidence       = $item->getTaxResidence();
  $result->email              = $item->getEmail();
  $result->phone              = $item->getPhone();
  $result->creationDate       = $item->getCreationDate();
  $result->creationDateFormat = $item->getCreationDateFormat();
  $result->status             = $item->getStatus();

  $catalog->results[] = $result;
endforeach;

echo json_encode($catalog);
die;
