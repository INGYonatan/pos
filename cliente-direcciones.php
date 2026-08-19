<?php
include 'inc/session.inc.php';
include 'data/lib/helpers/customers.helper.php';
include 'data/lib/helpers/catalogs.helper.php';

$page_config = [
  'page_title'        => 'Direcciones',
  'page_identifier'   => 'cliente-direcciones',
  'modal_title_add'   => 'Agregar dirección',
  'modal_title_edit'  => 'Editar dirección'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$customer_id  = cleanStr($_GET['uid']);
$customer     = customer_get_by_id($customer_id);

if (!$customer) :
  closeSession();
  die;
endif;

$page_config['page_title'] = "Direcciones | {$customer->name}";
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>
</head>

<body class="loading">
  <!-- Begin page -->
  <div id="wrapper">
    <!-- HEADER -->
    <?php include 'src/components/header.php'; ?>

    <!-- SIDEBAR -->
    <?php include 'src/components/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">
          <?php renderComponent("crudtable", [
            "pageId"          => $page_config['page_identifier'],
            "pageTitle"       => $page_config['page_title'],
            "pageDescription" => "Administra las direcciones de este cliente, agrega puntos de entrega y mantén actualizados sus datos fiscales",
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
            "extraHtmlInFilters" => '<input name="id_cliente" value="' . $customer->id . '" type="hidden">',
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Calle, C.P., Estado, Ciudad...",
              ]
            ]
          ]); ?>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/' . $page_config['page_identifier'] . '.php'; ?>

      <!-- FOOTER -->
      <?php include 'src/components/footer.php'; ?>
    </div>
  </div>
  <!-- END wrapper -->

  <!-- PAGE LOADINGS -->
  <?php include 'src/components/page-loadings.php'; ?>

  <!-- REQUIRED SCRIPTS -->
  <?php include 'src/components/required-scripts.php'; ?>

  <!-- APP JS -->
  <script src="<?= BASE_URL; ?>/src/js/app.min.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script src="<?= BASE_URL; ?>/src/main/address-autocomplete.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>
