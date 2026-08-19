<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Mi cuenta | ' . $admp_session_user_data['nombre_completo'],
  'page_identifier'   => 'mi-cuenta'
];

//checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/cs-filepicker/cs-filepicker.css">
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/whatsapp-editor/styles.css">
</head>

<body class="loading" data-sidebar-size="condensed">
  <!-- Begin page -->
  <div id="wrapper">
    <!-- HEADER -->
    <?php include 'src/components/header.php'; ?>

    <!-- SIDEBAR -->
    <?php include 'src/components/sidebar.php'; ?>

    <div class="content-page no-padding">
      <div class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="page-title-box">


                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= $page_config['page_title']; ?></li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-lg-3">
              <div class="card">
                <div class="card text-center">
                  <div class="card-body">
                    <div class="avatar-xl mx-auto mt-1">
                      <div class="avatar-title bg-light rounded-circle">
                        <i class="mdi mdi-account h1 m-0 text-body"></i>
                      </div>
                    </div>

                    <h4 class="mt-3 mb-0"><?= $admp_session_user_data['username']; ?></h4>
                    <!-- <p class="text-muted"><?= $admp_session_user_data['correo']; ?></p> -->

                    <div class="text-start mt-3">
                      <div class="table-responsive">
                        <table class="table table-borderless table-sm">
                          <tbody>
                            <tr>
                              <th scope="row">Nombre:</th>
                              <td class="text-muted"><?= $admp_session_user_data['nombre_completo']; ?></td>
                            </tr>

                            <?php if (!empty($admp_session_user_data['telefono'])) : ?>
                              <tr>
                                <th scope="row">Teléfono:</th>
                                <td class="text-muted"><?= formatPhoneNumber($admp_session_user_data['telefono']); ?></td>
                              </tr>
                            <?php endif; ?>

                            <tr>
                              <th scope="row">Correo:</th>
                              <td class="text-muted"><?= $admp_session_user_data['correo']; ?></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-9">
              <div class="card mb-0">
                <div class="card-body">
                  <ul class="nav nav-pills navtab-bg">
                    <li class="nav-item">
                      <a class="nav-link ms-0 active" data-bs-toggle="tab" aria-expanded="true" href="#datos-generales-tab">
                        <i class="mdi mdi-face-profile me-1"></i> Datos generales
                      </a>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="tab-content pt-1">
                <div id="datos-generales-tab" class="tab-pane card show active">
                  <form id="<?= $page_config['page_identifier']; ?>-form-data" class="card-body form-validate" autocomplete="off">
                    <div class="row">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label class="form-label" for="nombre_completo">Nombre completo<span class="text-danger">*</span></label>
                          <input id="nombre_completo" class="form-control" name="nombre_completo" value="<?= $admp_session_user_data['nombre_completo']; ?>" type="text" required>
                        </div>
                      </div>

                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label class="form-label" for="correo">Correo<span class="text-danger">*</span></label>
                          <input id="correo" class="form-control" name="correo" value="<?= $admp_session_user_data['correo']; ?>" type="email" required>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label class="form-label" for="telefono">Teléfono</label>
                          <input id="telefono" class="form-control number-input" name="telefono" value="<?= $admp_session_user_data['telefono']; ?>" type="text">
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-12">
                        <h5 class="header-title">Datos de cuenta</h5>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label class="form-label" for="username">Username<span class="text-danger">*</span></label>
                          <input id="username" class="form-control" name="username" value="<?= $admp_session_user_data['username']; ?>" type="text" required readonly>
                        </div>
                      </div>
                    </div>

                    <div id="change-password-container" class="row">
                      <div class="col-12">
                        <div class="form-group mb-0 mt-2">
                          <label class="custom-switch">
                            <input id="change_password" class="custom-switch-input" name="change_password" type="checkbox">
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Cambiar contraseña</span>
                          </label>
                        </div>
                      </div>
                    </div>

                    <div id="password-container" class="row" style="display: none;">
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label class="form-label" for="password">Contraseña<span class="text-danger">*</span></label>
                          <input id="password" class="form-control" name="password" type="password" required>
                        </div>
                      </div>

                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label class="form-label" for="confirm_password">Contraseña<span class="text-danger">*</span></label>
                          <input id="confirm_password" class="form-control" name="confirm_password" type="password" required>
                        </div>
                      </div>
                    </div>

                    <input name="uid" type="hidden">
                    <input name="action" value="edit-<?= $page_config['page_identifier']; ?>" type="hidden">
                    <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

                    <div class="row">
                      <div class="col-12 text-end">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

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

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    // JQUERY ACTIONS
    $('#change_password').on('click', function() {
      const isChecked = $(this).is(':checked');

      if (isChecked) $('#password-container').slideDown();
      if (!isChecked) $('#password-container').slideUp();
    });
  </script>
</body>

</html>