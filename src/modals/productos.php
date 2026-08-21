<?php $productos_page_config_id = $productos_page_config_id ?? $page_config['page_identifier']; ?>

<div class="modal fade" id="<?= $productos_page_config_id; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $productos_page_config_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="<?= $productos_page_config_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $productos_page_config_id; ?>-modal-label">Nuevo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="codigo">Código<span class="text-danger">*</span></label>
              <input id="codigo" class="form-control" name="codigo" type="text" required>
            </div>
          </div>

          <div class="col-12 col-lg-8">
            <div class="form-group">
              <label class="form-label" for="nombre_producto">Nombre del producto<span class="text-danger">*</span></label>
              <input id="nombre_producto" class="form-control" name="nombre_producto" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="id_tipo">Tipo<span class="text-danger">*</span></label>
              <select id="id_tipo" class="form-control form-select" name="id_tipo" required>
                <!-- <option value="">--Seleccionar--</option>
                <option value="equipo">Equipo</option>
                <option value="llantas">Llantas</option>
                <option value="rines">Rines</option>
                <option value="refacciones">Refacciones</option>
                <option value="servicios">Servicios</option>
                <option value="otros">Otros</option> -->

                <?= getTypesCatalog(); ?>
              </select>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="id_marca">Marca<span class="text-danger" id="marca-span-required">*</span></label>

              <div class="input-group">
                <select id="id_marca" class="form-control form-select" name="id_marca" catalog-onChange="#id_categoria" data-parameters="<?= htmlentities(json_encode(['action' => 'get-brand-categories'])) ?>" data-resetCatalog="true">
                  <?= getBrandsCatalog(); ?>
                </select>

                <div class="input-group-text p-0 overflow-hidden" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar nueva marca">
                  <button class="btn btn-secondary rounded-0" type="button" data-bs-toggle="modal" data-bs-target="#marcas-modal">+</button>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="id_categoria">Línea</label>

              <div class="input-group">
                <select id="id_categoria" class="form-control form-select" name="id_categoria" catalog-onChange="#id_categoria_familia" data-parameters="<?= htmlentities(json_encode(['action' => 'get-category-families'])) ?>" data-resetCatalog="true">
                  <option value="">--</option>
                </select>

                <div id="btn-add-line-container" class="input-group-text p-0 overflow-hidden" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar nueva línea" style="display: none;">
                  <button class="btn btn-secondary rounded-0" type="button" data-bs-toggle="modal" data-bs-target="#categorias-modal">+</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-4 not-service">
            <div class="form-group">
              <label class="form-label" for="id_categoria_familia">Familia</label>

              <div class="input-group">
                <select id="id_categoria_familia" class="form-control form-select" name="id_categoria_familia">
                  <option value="">--</option>
                </select>

                <div id="btn-add-family-container" class="input-group-text p-0 overflow-hidden" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar nueva familia" style="display: none;">
                  <button class="btn btn-secondary rounded-0" type="button" data-bs-toggle="modal" data-bs-target="#categoria-familias-modal">+</button>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="tipo">Proveedor<span class="text-danger">*</span></label>

              <div class="input-group">
                <select id="id_proveedor" class="form-control form-select" name="id_proveedor" required>
                  <?= getSuppliersCatalog(); ?>
                </select>

                <div class="input-group-text p-0 overflow-hidden" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar nuevo proveedor">
                  <button class="btn btn-secondary rounded-0" type="button" data-bs-toggle="modal" data-bs-target="#proveedores-modal">+</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-4" style="display: none;">
            <div class="form-group">
              <label class="form-label" for="unidad">Se vende<span class="text-danger">*</span></label>
              <select class="form-control form-select" name="unidad" id="unidad" required>
                <option value="Pieza" selected>Por Unidad/Pza</option>
                <option value="A granel">A Granel(usa decimales)</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-lg-4" style="display: none;">
            <div class="form-group">
              <label class="form-label" for="contenido"><span id="contenido-label">Contenido (gramos)</span><span class="text-danger">*</span></label>
              <input id="contenido" class="form-control decimal-input" name="contenido" min="0" value="0" type="text" required>
            </div>
          </div>
        </div>

        <div class="row not-service">
          <div class="col-12 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="pr_unidad_entrada">Unidad de entrada<span class="text-danger">*</span></label>
              <select id="pr_unidad_entrada" class="form-control form-select" name="unidad_entrada" required>
                <option value="">--Seleccionar--</option>
                <option value="caja">Caja</option>
                <option value="unidad">Unidad</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="pr_unidad_salida">Unidad de salida<span class="text-danger">*</span></label>
              <select id="pr_unidad_salida" class="form-control form-select" name="unidad_salida" required>
                <option value="">--Seleccionar--</option>
                <!-- <option value="caja">Caja</option> -->
                <option value="unidad" selected>Unidad</option>
              </select>
            </div>
          </div>
        </div>

        <div id="unidad-entrada-caja-container" class="row" style="display: none;">
          <div class="col-12 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="numero_piezas">Número de piezas<span class="text-danger">*</span></label>
              <input id="numero_piezas" class="form-control" name="numero_piezas" min="1" step="1" type="number" required>
            </div>
          </div>
        </div>

        <hr class="my-2">

        <div class="card card-body shadow-none border">
          <h3 class="card-title">Filtros para Productos tipo Llanta</h3>
          <p class="page-description">Si tu producto es una llanta, define las dimensiones correspondientes.</p>

          <div class="row">
            <div class="col-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="ancho">Ancho</label>
                <input id="ancho" class="form-control input-decimal" name="ancho" type="text" value="0.00">
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="alto">Alto</label>
                <input id="alto" class="form-control input-decimal" name="alto" type="text" value="0.00">
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="form-group">
                <label class="form-label" for="rin">Diámetro</label>
                <input id="rin" class="form-control input-decimal" name="rin" type="text" value="0.00">
              </div>
            </div>
          </div>
        </div>

        <hr class="my-2">

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="id_clave_unidad-label">Unidad de medida (SAT)<span class="text-danger">*</span></label>
              <input id="id_clave_unidad-label" class="form-control" name="nombre_clave_unidad" type="text" required>
              <input id="id_clave_unidad" name="id_clave_unidad" type="hidden" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="id_clave_producto_servicio-label">Clave del sat<span class="text-danger">*</span></label>
              <input id="id_clave_producto_servicio-label" class="form-control" name="descripcion_clave_producto_servicio" type="text" required>
              <input id="id_clave_producto_servicio" name="id_clave_producto_servicio" type="hidden" required>
            </div>
          </div>
        </div>

        <hr class="my-2">

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group mb-0">
              <label class="form-label">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" role="switch" id="fdap-control_inventario" name="control_inventario" value="si">
                  <label class="form-check-label" for="fdap-control_inventario">
                    Control de inventario
                  </label>
                </div>
                <small class="form-text text-muted">Activa esta opción para llevar el control de inventario del producto</small>
            </div>
          </div>
        </div>

        <hr class="my-2 mb-3">

        <div class="row">
          <div class="col-6 col-lg-4 col-xl-3">
            <div class="form-group">
              <label class="form-label" for="aplica_iva">¿Aplica IVA?<span class="text-danger">*</span></label>
              <select class="form-control form-select" name="aplica_iva" id="aplica_iva" required>
                <option value="si">Si</option>
                <option value="no" selected>No</option>
              </select>
            </div>
          </div>

          <div class="col-6 col-lg-4 col-xl-3">
            <div class="form-group">
              <label class="form-label" for="aplica_ieps">¿Aplica IEPS?<span class="text-danger">*</span></label>
              <select class="form-control form-select" name="aplica_ieps" id="aplica_ieps" required>
                <option value="si">Si</option>
                <option value="no" selected>No</option>
              </select>
            </div>
          </div>

          <div id="ieps-porcentaje-container" class="col-6 col-lg-4 col-xl-3" style="display: none;">
            <div class="form-group">
              <label class="form-label" for="ieps_porcentaje">% IEPS<span class="text-danger">*</span></label>
              <input id="ieps_porcentaje" class="form-control number-input" name="ieps_porcentaje" type="text" value="0.00" required>
            </div>
          </div>

          <div class="col-6 col-lg-4 col-xl-3">
            <div class="form-group">
              <label class="form-label" for="en_dolares">¿En dolares?<span class="text-danger">*</span></label>
              <select class="form-control form-select" name="en_dolares" id="en_dolares" required>
                <option value="si">Si</option>
                <option value="no" selected>No</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="precio_costo_original">Precio costo<span class="text-danger">*</span></label>
              <div class="row g-2">
                <div class="col">
                  <label class="form-label" for="precio_costo_original">Sin Impuesto</label>
                  <input id="precio_costo_original" class="form-control number-input input-price-with-complement" data-complement="#precio_costo" name="precio_costo_original" type="text" required>
                </div>
                <div class="col hidding-price-inputs" style="display: none;">
                  <label class="form-label" for="precio_costo">Con Impuesto</label>
                  <input id="precio_costo" class="form-control number-input input-price-with-complement" data-complement="#precio_costo_original" data-mode="remove-tax" name="precio_costo" type="text">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="precio_venta_original">Precio venta<span class="text-danger">*</span></label>
              <div class="row g-2">
                <div class="col">
                  <label class="form-label" for="precio_venta_original">Sin Impuesto</label>
                  <input id="precio_venta_original" class="form-control number-input input-price-with-complement" data-complement="#precio_venta" name="precio_venta_original" type="text" required>
                </div>
                <div class="col hidding-price-inputs" style="display: none;">
                  <label class="form-label" for="precio_venta">Con Impuesto</label>
                  <input id="precio_venta" class="form-control number-input input-price-with-complement" data-complement="#precio_venta_original" data-mode="remove-tax" name="precio_venta" type="text">
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="precio_venta2_original">Precio venta 2<span class="text-danger">*</span></label>
              <div class="row g-2">
                <div class="col">
                  <label class="form-label" for="precio_venta2_original">Sin Impuesto</label>
                  <input id="precio_venta2_original" class="form-control number-input input-price-with-complement" data-complement="#precio_venta2" name="precio_venta2_original" type="text" required>
                </div>
                <div class="col hidding-price-inputs" style="display: none;">
                  <label class="form-label" for="precio_venta2">Con Impuesto</label>
                  <input id="precio_venta2" class="form-control number-input input-price-with-complement" data-complement="#precio_venta2_original" data-mode="remove-tax" name="precio_venta2" type="text" required>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="precio_venta3_original">Precio venta 3<span class="text-danger">*</span></label>
              <div class="row g-2">
                <div class="col">
                  <label class="form-label" for="precio_venta3_original">Sin Impuesto</label>
                  <input id="precio_venta3_original" class="form-control number-input input-price-with-complement" data-complement="#precio_venta3" name="precio_venta3_original" type="text" required>
                </div>
                <div class="col hidding-price-inputs" style="display: none;">
                  <label class="form-label" for="precio_venta3">Con Impuesto</label>
                  <input id="precio_venta3" class="form-control number-input input-price-with-complement" data-complement="#precio_venta3_original" data-mode="remove-tax" name="precio_venta3" type="text" required>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- <div class="row">
          <div class="col-12 col-lg-5">
            <div class="form-group">
              <label class="form-label" for="cantidad_mayoreo">Cantidad mayoreo (dejar en 0 si no aplica)<span class="text-danger">*</span></label>
              <input id="cantidad_mayoreo" class="form-control decimal-input" name="cantidad_mayoreo" value="0" type="text" required>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="precio_mayoreo_original">Precio mayoreo (dejar en 0 si no aplica)<span class="text-danger">*</span></label>

              <div class="input-group">
                <input id="precio_mayoreo_original" class="form-control number-input" name="precio_mayoreo_original" value="0" type="text" required>
                <input id="precio_mayoreo" class="form-control number-input" name="precio_mayoreo" value="0" type="text" required readonly>
              </div>
            </div>
          </div>
        </div> -->
      </div>

      <input name="uid" type="hidden">
      <input name="action" type="hidden">
      <input name="place" value="<?= $productos_page_config_id; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal-upload-csv" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="<?= $page_config['page_identifier']; ?>-form-data-upload-csv" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Importar productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label" for="csvFile">Archivo CSV<span class="text-danger">*</span></label>
              <div id="csvFile" class="cs-filepicker" data-name="csvFile" data-title="Adjuntar .CSV" data-subtitle="Recuerda tener todas las columnas necesarias" data-errorNoFormat="El tipo de archivo no es el formato indicado, adjunta un archivo .csv" data-onlyFiles="true" data-required="true" data-requiredMessage="¡Debes de adjuntar el archivo csv!"></div>
            </div>
          </div>
        </div>

        <div id="csv-match" class="row" style="display: none;">
          <div class="col-12">
            <h5>Asignar campos CSV a las llantas</h5>
            <p>Selecciona los campos de tu archivo CSV para asignarlos a los campos de llanta</p>
          </div>

          <div class="col-12">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Nombre de la columna</th>
                  <th>Asignar al campo</th>
                </tr>
              </thead>

              <tbody id="csv-match-body">
                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="skuPosition">Clave/Sku<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="skuPosition" class="form-control form-select csv-match" name="skuPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="namePosition">Nombre del producto<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="namePosition" class="form-control form-select csv-match" name="namePosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="typePosition">Tipo<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="typePosition" class="form-control form-select csv-match" name="typePosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="brandPosition">Marca<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="brandPosition" class="form-control form-select csv-match" name="brandPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="brandLinePosition">Línea<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="brandLinePosition" class="form-control form-select csv-match" name="brandLinePosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="brandLineFamilyPosition">Familia<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="brandLineFamilyPosition" class="form-control form-select csv-match" name="brandLineFamilyPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="supplierPosition">Proveedor<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="supplierPosition" class="form-control form-select csv-match" name="supplierPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="entryUnitPosition">Unidad de entrada<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="entryUnitPosition" class="form-control form-select csv-match" name="entryUnitPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="entryNumPiecesPosition">Número de piezas<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="entryNumPiecesPosition" class="form-control form-select csv-match" name="entryNumPiecesPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="exitUnitPosition">Unidad de salida<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="exitUnitPosition" class="form-control form-select csv-match" name="exitUnitPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="unitKeyPosition">Unidad de medida<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="unitKeyPosition" class="form-control form-select csv-match" name="unitKeyPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="satKeyPosition">Clave del sat<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="satKeyPosition" class="form-control form-select csv-match" name="satKeyPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="applyIvaPosition">¿Aplica IVA?<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="applyIvaPosition" class="form-control form-select csv-match" name="applyIvaPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="applyIepsPosition">¿Aplica IEPS?</label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="applyIepsPosition" class="form-control form-select csv-match" name="applyIepsPosition"></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="iepsPercentagePosition">% IEPS</label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="iepsPercentagePosition" class="form-control form-select csv-match" name="iepsPercentagePosition"></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="inDollarsPosition">¿En dolares?<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="inDollarsPosition" class="form-control form-select csv-match" name="inDollarsPosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="costPricePosition">Precio costo<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="costPricePosition" class="form-control form-select csv-match" name="costPricePosition" required></select>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td class="align-middle">
                    <label class="form-label mb-0" for="salePricePosition">Precio venta<span class="text-danger">*</span></label>
                  </td>

                  <td class="align-middle">
                    <div class="form-group mb-0">
                      <select id="salePricePosition" class="form-control form-select csv-match" name="salePricePosition" required></select>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <input name="uid" type="hidden">
      <input name="action" type="hidden">
      <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Procesar</button>
      </div>
    </form>
  </div>
</div>

<?php
$modal_marcas_id      = "marcas";
$modal_marcas_origin  = "productos";
include_once __DIR__ . "/marcas.php";

$modal_categorias_id      = "categorias";
$modal_categorias_origin  = "productos";
include_once __DIR__ . "/categorias.php";

$modal_categoria_familias_id      = "categoria-familias";
$modal_categoria_familias_origin  = "productos";
include_once __DIR__ . "/categoria-familias.php";

$proveedores_page_config_id = "proveedores";
$proveedores_page_config_origin = "productos";
include_once __DIR__ . "/proveedores.php";
?>