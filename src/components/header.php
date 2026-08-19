<div class="navbar-custom">
  <div class="container-fluid">

    <ul class="list-unstyled topnav-menu float-end mb-0">
      <li class="dropdown d-none d-lg-inline-block">
        <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-toggle="fullscreen" href="#">
          <i class="fe-maximize noti-icon"></i>
        </a>
      </li>

      <li class="dropdown notification-list topbar-dropdown">
        <a class="nav-link dropdown-toggle nav-user me-0 waves-effect waves-light d-lg-flex align-items-lg-center" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
          <i class="fa fa-user-circle fa-2x rounded-circle"></i>

          <span class="pro-user-name ms-1">
            <?= $admp_session_user_data['username']; ?> <i class="mdi mdi-chevron-down"></i>
          </span>
        </a>
        <div class="dropdown-menu dropdown-menu-end profile-dropdown ">

          <div class="dropdown-header noti-title">
            <h6 class="text-overflow m-0">¡Bienvenido!</h6>
          </div>


          <a href="<?= BASE_URL; ?>/mi-cuenta" class="dropdown-item notify-item">
            <i class="ri-account-circle-line"></i>
            <span>Mi cuenta</span>
          </a>

          <?php if (checkModuleActionPermission("usuarios-catalogos", 'ver')) : ?>
            <a href="<?= BASE_URL; ?>/usuarios/<?= md5($admp_session_user_data["id_usuario"]);  ?>/archivos" class="dropdown-item notify-item">
              <i class="ri-file-list-3-line"></i>
              <span>Catálogos</span>
            </a>
          <?php endif; ?>

          <?php if ($admp_session_user_data["mostrar_tarjeta"] == "si") : ?>
            <a href="<?= ADM_WEBPAGE ?>/tarjeta/digital/<?= $admp_session_user_data['slug']; ?>" target="_blank" class="dropdown-item notify-item">
              <i class="fa fa-list"></i>
              <span>Mi tarjeta</span>
            </a>
          <?php endif; ?>

          <div class="dropdown-divider"></div>

          <a href="<?= BASE_URL ?>/cerrar-sesion" class="dropdown-item notify-item">
            <i class="ri-logout-box-line"></i>
            <span>Cerrar sesión</span>
          </a>
        </div>
      </li>
    </ul>

    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
      <li>
        <button class="button-menu-mobile waves-effect waves-light">
          <i class="fe-menu"></i>
        </button>
      </li>

      <li>
        <a class="navbar-toggle nav-link" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
          <div class="lines">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </a>
      </li>

      <li>
        <a class="nav-link header-title fw-bold" href="javascript:void(0)">
          <?php if (!$admp_session_user_data["IS_ADMIN"]) :
            $headerSessionBranchId = getSessionBranchOfficeId();
            $headerBranchData = getBranchOfficeData($headerSessionBranchId);
          ?>
            <!-- <i class="fa fa-user-circle text-warning"></i> -->
            <?= $headerBranchData["nombre_sucursal"]; ?>
          <?php endif; ?>
        </a>
      </li>
    </ul>
    <div class="clearfix"></div>
  </div>
</div>