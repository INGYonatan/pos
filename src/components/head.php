<meta charset="utf-8" />



<title><?= ADM_NAME; ?> <?= !empty($page_config['page_title']) ? ':: ' . $page_config['page_title'] : ''; ?></title>



<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />

<meta content="Coderthemes" name="author" />

<meta http-equiv="X-UA-Compatible" content="IE=edge" />



<!-- FAVICON -->

<link rel="shortcut icon" href="<?= ADM_FAVICON; ?>">



<!-- FONTS -->

<!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap"> -->



<!-- APP CSS -->

<link href="<?= BASE_URL; ?>/src/css/default/bootstrap.min.css" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />

<link href="<?= BASE_URL; ?>/src/css/default/app.min.css" rel="stylesheet" type="text/css" id="app-default-stylesheet" />



<!-- ICONS -->

<link href="<?= BASE_URL; ?>/src/css/icons.min.css" rel="stylesheet" type="text/css" />



<!-- SWEETALERTS -->

<link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/sweetalert/material-ui.css">



<!-- CUSTOM CSS -->

<link rel="stylesheet" href="<?= BASE_URL; ?>/src/css/custom.css">

<style>
  :root {
    --bs-on-primary: #000000;
  }

  .cs-datalist {
    position: relative;
  }

  .cs-datalist-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    display: none;
    max-height: 200px;
    overflow-y: auto;
    background-color: white;
    border: 1px solid #ccc;
    border-top: none;
    list-style: none;
    padding: 0;
  }

  .cs-datalist-options.active {
    display: block;
  }

  .cs-datalist.active .cs-datalist-options {
    display: block;
  }

  .cs-datalist-option {
    padding: 8px 12px;
    cursor: pointer;
  }

  .cs-datalist-option:hover {
    background-color: #f1f1f1;
  }

  .ui-state-highlight {
    min-height: 3rem;
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    border: 0.1rem dashed var(--bs-primary) !important;
    border-radius: 0.25rem;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: var(--bs-primary) !important;
    color: var(--bs-on-primary) !important;
    border: none;
  }

  .select2-container .select2-selection--multiple .select2-selection__choice {
    color: var(--bs-on-primary) !important;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: var(--bs-on-primary) !important;
  }
</style>

<style>
  .page-title-container {
    padding-bottom: 1.2rem;
    padding-top: .2rem;
  }

  .page-title {
    font-size: 1.8rem;
    margin: 0;
    margin-bottom: .2rem;
    font-weight: bold;
  }

  .page-description {
    font-size: 1rem;
    margin: 0;
  }

  .table thead,
  .table thead th {
    background-color: #f7f7f7 !important;
    color: #333 !important;
  }

  .crudtable-filters-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  @media (min-width: 576px) {
    .crudtable-filters-container {
      grid-template-columns: 1fr 1fr;
    }
  }

  @media (min-width: 768px) {
    .crudtable-filters-container {
      grid-template-columns: 1fr 1fr 1fr;
    }
  }

  @media (min-width: 992px) {
    .crudtable-filters-container {
      grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
    }
  }
</style>