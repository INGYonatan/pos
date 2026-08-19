<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="row">
  <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
    $row['imagen_bienvenida'] = BASE_URL . '/src/assets/images/llanteras/' . $row['imagen_bienvenida'];
    $id_llantera = $row['id_llantera'];

    $query = "SELECT mensaje, slug FROM {$db_dti}_llantera_mensajes WHERE id_llantera = $id_llantera";

    $query_result = mysqli_query($mysqli, $query);
    $num_rows     = mysqli_num_rows($query_result);

    if ($num_rows) :
      while ($mensaje = mysqli_fetch_assoc($query_result)) :
        $row['mensaje_' . $mensaje['slug']] = $mensaje['mensaje'];
      endwhile;
    endif;
  ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="text-center card">
        <div class="card-body pt-3">
          <div class="dropdown float-end position-absolute" style="top: 1rem; right: 1rem;">
            <a class="text-body dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="mdi mdi-dots-vertical font-20"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-end">
              <?= getTableActions($usuario_llanteras_identifier, $row); ?>

              <a class="dropdown-item" href="<?= BASE_URL; ?>/llantera-llantas?uid=<?= $row['slug']; ?>">
                <i class="mdi mdi-face-profile me-1"></i> Administrar llantas
              </a>
            </div>
          </div>

          <img src="<?= $row['imagen_bienvenida']; ?>" class="rounded-circle img-thumbnail avatar-xl mt-1" alt="<?= $row['nombre_comercial']; ?>">

          <h4 class="mt-3 mb-1"><a href="contacts-profile.html" class="text-dark"><?= $row['nombre_comercial']; ?></a></h4>
          <p class="text-muted"><?= formatPhoneNumber($row['telefono']); ?><span> <!-- | </span> <span> <a href="#" class="text-pink">websitename.com</a> </span> --></p>
          <?php if ($row['facebook'] || $row['instagram'] || $row['twitter']) : ?>
            <ul class="social-list list-inline mt-4 mb-2">
              <?php if ($row['facebook']) : ?>
                <li class="list-inline-item">
                  <a class="social-list-item border-purple text-purple" target="_blank" href="<?= $row['facebook']; ?>">
                    <i class="mdi mdi-facebook"></i>
                  </a>
                </li>

                <li class="list-inline-item">
                  <a class="social-list-item border-purple text-purple" target="_blank" href="<?= $row['instagram']; ?>">
                    <i class="mdi mdi-instagram"></i>
                  </a>
                </li>

                <li class="list-inline-item">
                  <a class="social-list-item border-purple text-purple" target="_blank" href="<?= $row['twitter']; ?>">
                    <i class="mdi mdi-twitter"></i>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php $table_row_number++; ?>
  <?php endwhile; ?>
</div>