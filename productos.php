<?php
include 'inc/session.inc.php';
require 'data/lib/helpers/catalogs.helper.php';

$page_config = [
  'page_title'        => 'Productos',
  'page_identifier'   => 'productos',
  'modal_title_add'   => 'Agregar producto',
  'modal_title_edit'  => 'Editar producto'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$tipo_cambio = getTipoCambio();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <!-- JQUERY UI -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.css">

  <!-- CS FILEPICKER -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/cs-filepicker/cs-filepicker.css">

  <style>
    .modal-secondary {
      background-color: rgba(0, 0, 0, 0.3);
    }
  </style>
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
            "pageTitle"       => "Productos",
            "pageDescription" => "Aquí podrás administrar los productos de tu empresa.",
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'], [], [
              "upload-csv" => [
                "type"        => "customLink",
                "customLink"  => BASE_URL . "/productos/importar",
              ]
            ])),
            "filters" => [
              "principal" => [
                [
                  "name"        => "search",
                  "label"       => "Buscar aquí",
                  "type"        => "input",
                  "placeholder" => "Código, producto",
                ],
                [
                  "field"         => "select",
                  "name"          => "brandId",
                  "label"         => "Marca",
                  "optionsRender" => renderToString(getBrandsCatalog("", "--Todas--")),
                ],
                [
                  "field"         => "select",
                  "name"          => "supplierId",
                  "label"         => "Proveedor",
                  "optionsRender" => renderToString(getSuppliersCatalog("", "--Todas--")),
                ],
                [
                  "field" => "select",
                  "name" => "type",
                  "label" => "Tipo",
                  "optionsRender" => renderToString(getTypesCatalog("", "--Todos--")),
                ],
                [
                  "field" => "select",
                  "name"  => "status",
                  "label" => "Estatus",
                  "selectOptions" => [
                    ["value" => "", "label" => "--Todos--"],
                    ["value" => "activo", "label" => "Activo", "selected" => true],
                    ["value" => "eliminado", "label" => "Eliminado"]
                  ]
                ]
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

  <!-- JQUERY UI -->
  <script src="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.js"></script>

  <!-- CS FILEPICKER -->
  <script src="<?= BASE_URL; ?>/src/plugins/cs-filepicker/cs-filepicker.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/cs-filepicker/cs-filepicker.init.js"></script>

  <!-- AUTOCOMPLETEO -->
  <script src="<?= BASE_URL; ?>/src/plugins/autocomplete/main.js"></script>

  <script>
    $('#unidad').on('change', function() {
      const PER_PIEZE = 'Pieza';
      const PER_BULK = 'A granel';

      const unit = $(this).val();
      const label = $('#contenido-label');

      if (unit === PER_PIEZE) label.html('Contenido (gramos)');
      if (unit === PER_BULK) label.html('Contenido (kg)');
    });

    const unidadMedidaAutocomplete = new Autocomplete({
      identifier: 'id_clave_unidad-label',
      source: `${BASE_URL}/data/autocompletes/clave-unidades.php`,
      minLength: 2,
      onSelect: data => $('#id_clave_unidad').val(data.uid)
    });

    const claveSATAutocomplete = new Autocomplete({
      identifier: 'id_clave_producto_servicio-label',
      source: `${BASE_URL}/data/autocompletes/clave-producto-servicios.php`,
      minLength: 2,
      onSelect: data => $('#id_clave_producto_servicio').val(data.uid)
    });

    unidadMedidaAutocomplete.initAutocomplete();
    claveSATAutocomplete.initAutocomplete();

    const parseProductPrice = price => {
      if (!price || isNaN(price)) return 0;

      const aplica_iva = $('#aplica_iva').val();
      const aplica_ieps = $('#aplica_ieps').val();
      const ieps_porcentaje = parseFloat($('#ieps_porcentaje').val()) || 0;
      const en_dolares = $('#en_dolares').val();

      const tipo_cambio = <?= $tipo_cambio; ?>;

      let finalPrice = parseFloat(price);

      if (aplica_ieps == 'si' && ieps_porcentaje > 0) finalPrice = finalPrice * (1 + (ieps_porcentaje / 100));
      if (aplica_iva == 'si') finalPrice = finalPrice * 1.16;
      if (en_dolares == 'si') finalPrice = finalPrice * tipo_cambio;

      return finalPrice.toFixed(DECIMALS_CURRENCY);
    };

    const getBrandCategories = (brandId, categoryId) => getCatalog({
      catalogSelector: '#id_categoria',
      parameters: {
        value: brandId,
        selectedValue: categoryId,
        action: 'get-brand-categories',
        resetCatalog: true
      }
    });

    const getFamilies = (categoryId, familyId) => getCatalog({
      catalogSelector: '#id_categoria_familia',
      parameters: {
        value: categoryId,
        selectedValue: familyId,
        action: 'get-category-families',
        resetCatalog: true
      }
    });

    // $('#precio_costo_original').on('keyup', function() {
    //   const costPrice = parseProductPrice($(this).val());
    //   $('#precio_costo').val(costPrice);
    // });

    // $('#precio_venta_original').on('keyup', function() {
    //   const salePrice = parseProductPrice($(this).val());
    //   $('#precio_venta').val(salePrice);
    //   $('#fd-precio_venta2').val(salePrice);
    //   $('#fd-precio_venta3').val(salePrice);
    // });

    // $('#precio_mayoreo_original').on('keyup', function() {
    //   const mayoreoPrice = parseProductPrice($(this).val());
    //   $('#precio_mayoreo').val(mayoreoPrice);
    // });

    // $('#aplica_iva').on('change', () => {
    //   const costPrice = parseProductPrice($('#precio_costo_original').val());
    //   $('#precio_costo').val(costPrice);

    //   const salePrice = parseProductPrice($('#precio_venta_original').val());
    //   $('#precio_venta').val(salePrice);
    //   $('#fd-precio_venta2').val(salePrice);
    //   $('#fd-precio_venta3').val(salePrice);

    //   const mayoreoPrice = parseProductPrice($('#precio_mayoreo_original').val());
    //   $('#precio_mayoreo').val(mayoreoPrice);
    // });

    // $('#en_dolares').on('change', () => {
    //   const costPrice = parseProductPrice($('#precio_costo_original').val());
    //   $('#precio_costo').val(costPrice);

    //   const salePrice = parseProductPrice($('#precio_venta_original').val());
    //   $('#precio_venta').val(salePrice);
    //   $('#fd-precio_venta2').val(salePrice);
    //   $('#fd-precio_venta3').val(salePrice);

    //   const mayoreoPrice = parseProductPrice($('#precio_mayoreo_original').val());
    //   $('#precio_mayoreo').val(mayoreoPrice);
    // });

    /* $('#id_categoria').on('change', function() {
      const categoryId = $(this).val();

      getFamilies(categoryId);
    }); */

    const handleToggleInputUnit = inputUnit => {
      if (inputUnit === 'caja') $('#unidad-entrada-caja-container').slideDown();
      else $('#unidad-entrada-caja-container').slideUp();
    };

    $('#pr_unidad_entrada').on('change', function() {
      const inputUnit = $(this).val();
      handleToggleInputUnit(inputUnit);
    })
  </script>

  <script>
    $("#id_categoria").on("change", function() {
      const categoryId = $(this).val();

      /*if (categoryId != 12) {
        $(".not-service").show();
        $("#precio_costo_original").val("").prop("readonly", false);
        $("#precio_costo").val("").prop("readonly", false);
      }

      if (categoryId == 12) {
        $(".not-service").hide();
        $("#id_categoria_familia").val("");
        $("#pr_unidad_entrada").val("");
        $("#pr_unidad_salida").val("");
        $("#precio_costo_original").val("0.00").prop("readonly", true);
        $("#precio_costo").val("0.00").prop("readonly", true);
      }*/
    });
  </script>

  <script>
    let csvOptions = `<option value="">--Seleccionar--</option>`;

    $cs_file_pickers_out_callbacks['csvFile'] = csvData => {
      const columns = csvData.split('\n')[0];
      console.log('columns:', columns);
      const columnsMap = columns.split(',');

      csvOptions = `<option value="">--Seleccionar--</option>`;

      columnsMap.map((item, index) => {
        csvOptions += `<option value="${index}">${item}</option>`;
      });

      $('.csv-match').html(csvOptions);
      $('#csv-match').show();
    };

    $('.btn-upload-csv').on('click', () => {
      $('.csv-match').html('');
      $('#csv-match').hide();
      $cs_file_pickers['csvFile'].createFilePicker();
    });

    $("#id_marca").on("change", function() {
      const value = $(this).val();

      if (!value) {
        $("#btn-add-line-container").hide();
        $("#btn-add-family-container").hide();
      }

      if (value) {
        $("#btn-add-line-container").show();
        $("[name='id_marca']").val(value);
        $("#btn-add-family-container").hide();
      }

      $("#id_categoria_familia").html('<option value="">--Seleccionar--</option>');
    });

    $("#id_categoria").on("change", function() {
      const value = $(this).val();

      if (!value) $("#btn-add-family-container").hide();
      if (value) {
        $("#btn-add-family-container").show();
        $("[name='id_categoria']").val(value);
      }
    });
  </script>

  <script>
    function handleIepsVisibility() {
      const haveIeps = $("#aplica_ieps").val() === "si";
      const currentIepsPercentage = parseFloat($("#ieps_porcentaje").val()) || 0;

      if (haveIeps) {
        $("#ieps-porcentaje-container").show();

        if (currentIepsPercentage <= 0) {
          $("#ieps_porcentaje").val("8.00");
        }
      } else {
        $("#ieps-porcentaje-container").hide();
        $("#ieps_porcentaje").val("0.00");
      }
    }

    function handleChangeInputWithComplement() {
      let value = parseFloat($(this).val());

      const mode = $(this).data("mode") ?? "add-tax"; // "add-tax" or "remove-tax"
      const complementSelector = $(this).data("complement");

      const haveIva = $("#aplica_iva").val() === "si";
      const haveIeps = $("#aplica_ieps").val() === "si";
      const iepsPercentage = parseFloat($("#ieps_porcentaje").val()) || 0;
      const inDollars = $("#en_dolares").val() === "si";
      const exchangeRate = <?= $tipo_cambio; ?>;
      const haveTax = haveIva || haveIeps;

      let finalValue = value;

      if (haveTax) $(".hidding-price-inputs").show();
      else $(".hidding-price-inputs").hide();

      if (isNaN(value) || value == null) return;

      if (mode === "add-tax") {
        if (haveIeps && iepsPercentage > 0) finalValue = finalValue * (1 + (iepsPercentage / 100));
        if (haveIva) finalValue = finalValue * 1.16;
      }

      if (mode === "remove-tax") {
        if (haveIva) finalValue = finalValue / 1.16;
        if (haveIeps && iepsPercentage > 0) finalValue = finalValue / (1 + (iepsPercentage / 100));
      }

      if (inDollars) finalValue = finalValue * exchangeRate;

      $(complementSelector).val(finalValue.toFixed(DECIMALS_CURRENCY));
    }

    $(".input-price-with-complement").on("keyup", handleChangeInputWithComplement);
    $("#aplica_iva, #aplica_ieps, #ieps_porcentaje, #en_dolares").on("change keyup", () => {
      handleIepsVisibility();
      $(".input-price-with-complement").trigger("keyup");
    });

    handleIepsVisibility();
  </script>

  <script>
    $("#id_tipo").on("change", function() {
      const tangible = $('option:selected', this).data('tangible');

      if (tangible == 0) {
        $("#id_marca").removeAttr("required").val("");
        $("#marca-span-required").hide();
      }

      if (tangible == 1) {
        $("#id_marca").attr("required", "true");
        $("#marca-span-required").show();
      }
    });

    $(document).on("click", ".btn-edit", () => setTimeout(() => {
      $("#id_tipo").trigger("change");
      $("#aplica_iva").trigger("change");
      $("#aplica_ieps").trigger("change");
    }, 200));
  </script>

  <script>
    $(".btn-add").on("click", () => {
      setTimeout(() => {
        $("#fdap-control_inventario").prop("checked", true);
      }, 200);
    });
  </script>
</body>

</html>