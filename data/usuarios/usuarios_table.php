<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Nombre completo</th>
        <th>Rol</th>
        <th>Sucursal</th>
        <th>Correo</th>
        <th>Teléfono</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) : ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-sm">
                <img class="avatar-img rounded" height="40" width="40" src="<?= $row['avatar'] ? BASE_URL . "/src/assets/images/usuarios/" . $row['avatar'] : "https://placehold.co/40x40"; ?>" alt="Avatar del usuario">
                <?php unset($row['avatar']); ?>
              </div>

              <div>
                <?= $row['nombre_completo']; ?>
              </div>
            </div>
          </td>

          <td>
            <?= $row['rol']; ?>
          </td>

          <td>
            <?= $row['nombre_sucursal']; ?>
          </td>

          <td>
            <?= $row['correo']; ?>
          </td>

          <td>
            <?php if ($row['telefono']) : ?>
              <?= formatPhoneNumber($row['telefono']); ?>
            <?php endif; ?>

            <?php if (!$row['telefono']) : ?>
              <span class="badge bg-danger">Sin teléfono</span>
            <?php endif; ?>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?php if ($row["mostrar_tarjeta"] == "si") : ?>
                    <a class="dropdown-item" href="<?= ADM_WEBPAGE ?>/tarjeta/digital/<?= $row['slug']; ?>" target="_blank">
                      <i class="me-1 fa fa-id-card"></i>
                      Tarjeta digital
                    </a>

                    <div class="hr my-1"></div>
                  <?php endif; ?>

                  <?php if ($row["rol_slug"] != "administrador" && $row["rol_slug"] != "root") : ?>
                    <a class="dropdown-item" href="<?= BASE_URL; ?>/usuarios/<?= md5($row['uid']); ?>/permisos">
                      <i class="me-1 fa fa-shield-alt"></i>
                      Permisos
                    </a>

                    <div class="hr my-1"></div>
                  <?php endif; ?>

                  <a class="dropdown-item" href="<?= BASE_URL; ?>/usuarios/<?= md5($row['uid']); ?>/archivos">
                    <i class="me-1 fa fa-file"></i>
                    Catálogos
                  </a>

                  <?= getTableActions($identifier, $row); ?>
                </div>
              </div>
            </td>
          <?php endif; ?>
        </tr>

        <?php $table_row_number++; ?>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?= paginate_normal($page, $request['num_pages'], 2, 'load'); ?>