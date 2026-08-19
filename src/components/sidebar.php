<div class="left-side-menu">
  <!-- LOGO -->
  <div class="logo-box">
    <a href="<?= BASE_URL; ?>" class="logo logo-dark text-center">
      <span class="logo-sm">
        <img src="<?= ADM_LOGO_WHITE; ?>" alt="<?= ADM_NAME; ?>" height="15">
        <!-- <span class="logo-lg-text-light">Minton</span> -->
      </span>
      <span class="logo-lg">
        <img src="<?= ADM_LOGO_WHITE; ?>" alt="<?= ADM_NAME; ?>" height="30">
        <!-- <span class="logo-lg-text-light">M</span> -->
      </span>
    </a>

    <a href="<?= BASE_URL; ?>" class="logo logo-light text-center">
      <span class="logo-sm">
        <img src="<?= ADM_LOGO_WHITE; ?>" alt="<?= ADM_NAME; ?>" height="15">
      </span>
      <span class="logo-lg">
        <img src="<?= ADM_LOGO_WHITE; ?>" alt="<?= ADM_NAME; ?>" height="30">
      </span>
    </a>
  </div>

  <div class="h-100" data-simplebar>
    <!--- Sidemenu -->
    <div id="sidebar-menu">
      <ul id="side-menu">
        <li class="menu-title">Panel</li>

        <!--li>
          <a href="<?= BASE_URL ?>/dashboard">
            <i class="ri-dashboard-line"></i>
            <span> Dashboard </span>
          </a>
        </li-->

        <?= getNavbarModules(); ?>
      </ul>

    </div>
    <!-- End Sidebar -->

    <div class="clearfix"></div>

  </div>
  <!-- Sidebar -left -->

</div>