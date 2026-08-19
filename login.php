<?php
require 'inc/settings.inc.php';

if ($admp_session_user_data) :
  // if ($admp_session_user_data['rol_slug'] === 'vendedor') :
  //   header('location:' . BASE_URL . '/pos');
  //   die;
  // endif;

  header('location:' . BASE_URL . '/panel-de-control');
  die;
endif;
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>
</head>

<body class="loading">
  <!-- Begin page -->
  <div class="account-pages mt-5 mb-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-4">
          <div class="card">
            <div class="card-body p-4">
              <div class="text-center w-75 m-auto">
                <a href="<?= BASE_URL; ?>" class="logo text-center">
                  <img src="<?= ADM_LOGO; ?>" alt="<?= ADM_NAME; ?>" height="80" width="150" style="object-fit: contain;">
                </a>

                <p class="text-muted mb-4 mt-3">Ingresa tus datos de acceso para continuar.</p>
              </div>

              <form id="login-form" class="form-validate" autocomplete="off">
                <div class="row">
                  <div class="col-12 mb-2">
                    <div class="form-group">
                      <label class="form-label" for="username">Nombre de usuario</label>
                      <input id="username" class="form-control" name="usernam" type="text" required>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-2">
                    <div class="form-group">
                      <label class="form-label" for="password">Contraseña</label>

                      <div class="input-group cs-input-group-append">
                        <input id="password" class="form-control" name="password" type="password" required>

                        <div class="input-group-text" data-password="false">
                          <span class="password-eye"></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkbox-signin" checked>
                    <label class="form-check-label" for="checkbox-signin">
                      Recordarme
                    </label>
                  </div>
                </div>

                <input name="action" type="hidden" value="login">
                <input name="place" type="hidden" value="authentication">

                <div class="d-grid mb-0 text-center">
                  <button class="btn btn-primary" type="submit"> Iniciar sesión </button>
                </div>
              </form>

              <!-- <div class="text-center">
                <h5 class="mt-3 text-muted">Sign in with</h5>
                <ul class="social-list list-inline mt-3 mb-0">
                  <li class="list-inline-item">
                    <a href="javascript:void(0);" class="social-list-item border-purple text-purple"><i class="mdi mdi-facebook"></i></a>
                  </li>
                  <li class="list-inline-item">
                    <a href="javascript:void(0);" class="social-list-item border-danger text-danger"><i class="mdi mdi-google"></i></a>
                  </li>
                  <li class="list-inline-item">
                    <a href="javascript:void(0);" class="social-list-item border-info text-info"><i class="mdi mdi-twitter"></i></a>
                  </li>
                  <li class="list-inline-item">
                    <a href="javascript:void(0);" class="social-list-item border-secondary text-secondary"><i class="mdi mdi-github"></i></a>
                  </li>
                </ul>
              </div> -->

            </div> <!-- end card-body -->
          </div>
          <!-- end card -->

          <div class="row mt-3">
            <div class="col-12 text-center">
              <p> <a href="recuperar-credenciales" class="text-muted ms-1">¿Olvidaste tu contraseña?</a></p>
            </div>
          </div>
          <!-- end row -->

        </div> <!-- end col -->
      </div>
      <!-- end row -->
    </div>
  </div>
  <!-- END wrapper -->

  <!-- FOOTER -->
  <footer class="footer footer-alt">
    <script>
      document.write(new Date().getFullYear())
    </script> <a href="<?= ADM_WEBPAGE; ?>" target="_blank" class="text-dark">&copy;<?= ADM_WEBPAGE; ?></a>
  </footer>

  <!-- PAGE LOADINGS -->
  <?php include 'src/components/page-loadings.php'; ?>

  <!-- REQUIRED SCRIPTS -->
  <?php include 'src/components/required-scripts.php'; ?>

  <!-- VALIDATE -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <!-- APP JS -->
  <script src="<?= BASE_URL; ?>/src/js/app.min.js"></script>
</body>

</html>