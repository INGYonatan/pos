<?php
require_once __DIR__ . "/../../data/lib/models/sucursales.model.php";

/**
 * @var array $data
 */

$pageId     = "productos";
$elementId  = "importp-{$pageId}";

// Obtener los bots que hay por usuario en sesión
$branchesModel  = new SucursalesModel();
$branches       = $branchesModel->getAll()->data->rows;
?>

<!-- ---------------------------------------------------------------- -->

<!-- CS FILEPICKER -->
<link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/cs-filepicker/cs-filepicker.css">

<!-- ---------------------------------------------------------------- -->

<form id="form-<?= $elementId; ?>">
  <div class="row">
    <div class="col-12 col-lg-6 mx-auto mb-3">
      <div class="card card-body h-100 m-0">
        <div class="mb-4">
          <h3 class="card-title">
            <i class="fa fa-file-csv text-primary me-1"></i>
            Adjunta tu archivo CSV para importar tus productos
          </h3>

          <p class="card-description">Verifica que tu archivo tenga el formato correcto y los campos correctos para que la importación se realice con éxito.</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="<?= $elementId; ?>-file">Archivo CSV<span class="text-danger">*</span></label>
          <div
            id="<?= $elementId; ?>-file"
            class="cs-filepicker"
            data-name="csvFile"
            data-title="Adjuntar .CSV"
            data-subtitle="
            Asegúrate de incluir todas las columnas requeridas: 'Código/SKU', 'Nombre', 'Tipo', 'Requiere número de serie', 'Números de serie', 'Marca', 'Proveedor', 'Unidad de entrada', 'Unidad de medida (SAT)', 'Clave del SAT', 'Precio costo', 'Precio venta 1', 'Precio venta 2', 'Precio venta 3', 'Aplica IVA', 'Aplica IEPS', 'IEPS %', 'En dólares'<br>
            <span class='text-danger'>Los registros que no contengan estos datos no serán agregados.</span><br>
            <a class='fw-bold text-dark pulse' href='<?= BASE_URL; ?>/productos-prueba-importar.csv' download class='text-primary'>DESCARGAR EJEMPLO</a>
          "
            data-errorNoFormat="El tipo de archivo no es el formato indicado, adjunta un archivo .csv"
            data-onlyFiles="true"
            data-required="true"
            data-requiredMessage="¡Debes de adjuntar el archivo csv!"></div>
        </div>
      </div>
    </div>
  </div>

  <div id="<?= $elementId; ?>-csv-match" class="row" style="display: none;">
    <div class="col-12 col-lg-6 mb-3">
      <div class="card card-body h-100 m-0">
        <div class="mb-3">
          <h3 class="card-title">
            <i class="fa fa-columns text-primary me-1"></i>
            Asignar campos compartidos
          </h3>

          <p class="card-description">Asigna los campos compartidos entre todas tus sucursales/agentes</p>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <label class="form-group m-0">
              <div class="d-flex gap-2 form-check form-switch">
                <input id="<?= $elementId; ?>-prices_with_iva" class="form-check-input" type="checkbox" value="si" name="prices_with_iva" checked>
                <span class="form-check-label">
                  Precios con IVA
                  <br>
                  <span class="fw-light">Habilita esta opción si tus precios ya vienen con IVA</span>
                </span>
              </div>
            </label>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <label class="form-group m-0">
              <div class="d-flex gap-2 form-check form-switch">
                <input id="<?= $elementId; ?>-prices_with_ieps" class="form-check-input" type="checkbox" value="si" name="prices_with_ieps" checked>
                <span class="form-check-label">
                  Precios con IEPS
                  <br>
                  <span class="fw-light">Habilita esta opción si tus precios ya vienen con IEPS</span>
                </span>
              </div>
            </label>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Nombre de la columna</th>
                  <th>Asignar al campo</th>
                </tr>
              </thead>

              <tbody id="csv-match-body">
                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-sku">Código/SKU<span class="text-danger">*</span></label>

                    <small>
                      <p class="text-muted">Ejemplo: 1657014MIECO307</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-sku" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_sku" required></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-name">Nombre del producto<span class="text-danger">*</span></label>

                    <small>
                      <p class="text-muted">Ejemplo: 165/70R14 BRIDGSTONE</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-name" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_name" required></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-type">Tipo<span class="text-danger">*</span></label>

                    <small>
                      <p class="text-muted">Ejemplo: Producto, Servicio</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-type" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_type" required></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-unit">Unidad de entrada<span class="text-danger">*</span></label>

                    <small>
                      <p class="text-muted">Ejemplo: Pieza, Kilo, Litro</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-unit" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_unit" required></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-requires_serial">¿Requiere número de serie? (1/0)</label>

                    <small>
                      <p class="text-muted">Ejemplo: 1 = sí, 0 = no, valor por defecto (0)</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-requires_serial" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_requires_serial"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-brand">Marca</label>

                    <small>
                      <p class="text-muted">Ejemplo: BRIDGESTONE</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-brand" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_brand"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-supplier">Proveedor</label>

                    <small>
                      <p class="text-muted">Ejemplo: PROVEEDOR PRINCIPAL</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-supplier" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_supplier"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-unit_code">Unidad de medida (SAT)</label>

                    <small>
                      <p class="text-muted">Ejemplo: H87 (pieza), KGM (kilogramo)</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-unit_code" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_unit_code"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-service_code">Clave del SAT</label>

                    <small>
                      <p class="text-muted">Ejemplo: 84111506 (Llantas nuevas)</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-service_code" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_service_code"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-description">Descripción (Opcional)</label>

                    <small>
                      <p class="text-muted">Describe el producto</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-description" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_description"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-cost_price">Precio costo <span class="<?= $elementId; ?>-vat-label">(Con impuestos)</span> <span class="<?= $elementId; ?>-ieps-label">(Con IEPS)</span><span class="text-danger">*</span></label>

                    <small>
                      <p class="text-muted">Ejemplo: 100</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-cost_price" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_cost_price" required></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-sale_price">Precio venta 1 <span class="<?= $elementId; ?>-vat-label">(Con impuestos)</span> <span class="<?= $elementId; ?>-ieps-label">(Con IEPS)</span><span class="text-danger">*</span></label>

                    <small>
                      <p class="text-muted">Ejemplo: 150</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-sale_price" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_sale_price" required></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-sale_price_2">Precio venta 2 <span class="<?= $elementId; ?>-vat-label">(Con impuestos)</span> <span class="<?= $elementId; ?>-ieps-label">(Con IEPS)</span></label>

                    <small>
                      <p class="text-muted">Ejemplo: 160</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-sale_price_2" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_sale_price_2"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-sale_price_3">Precio venta 3 <span class="<?= $elementId; ?>-vat-label">(Con impuestos)</span> <span class="<?= $elementId; ?>-ieps-label">(Con IEPS)</span></label>

                    <small>
                      <p class="text-muted">Ejemplo: 170</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-sale_price_3" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_sale_price_3"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle <?= $elementId; ?>-vat-row" style="display: none;">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-apply_iva">¿Aplica IVA? (opcional)</label>

                    <small>
                      <p class="text-muted">Ejemplo: si/no, valor por defecto (si)</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-apply_iva" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_apply_iva"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle <?= $elementId; ?>-ieps-row" style="display: none;">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-apply_ieps">¿Aplica IEPS? (opcional)</label>

                    <small>
                      <p class="text-muted">Ejemplo: si/no, valor por defecto (si)</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-apply_ieps" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_apply_ieps"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-ieps">IEPS (%)</label>

                    <small>
                      <p class="text-muted">Valor por defecto: 8%</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-ieps" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_ieps"></select>
                    </div>
                  </td>
                </tr>

                <tr class="align-middle">
                  <td>
                    <label class="form-label mb-0" for="<?= $elementId; ?>-usd">¿En dólares?</label>

                    <small>
                      <p class="text-muted">Ejemplo: si/no, valor por defecto (no)</p>
                    </small>
                  </td>

                  <td>
                    <div class="form-group m-0">
                      <select id="<?= $elementId; ?>-usd" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_usd"></select>
                    </div>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <div class="card card-body h-100 m-0">
        <div class="mb-3">
          <h3 class="card-title">
            <i class="fa fa-columns text-primary me-1"></i>
            Asignar campos de Stock y Números de Serie
          </h3>

          <p class="card-description">Asigna los campos de stock y de números de serie para cada sucursal/agente</p>
        </div>

        <div class="row">
          <?php foreach ($branches as $branch) :
            /**
             * @var SucursalesModel $branch
             */
          ?>
            <div class="col-12 mb-3">
              <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title">
                <i class="fa fa-store me-1"></i>
                <?= $branch->getName(); ?>
              </h5>

              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Nombre de la columna</th>
                    <th>Asignar al campo</th>
                  </tr>
                </thead>

                <tbody>
                  <tr class="align-middle">
                    <td>
                      <label class="form-label mb-0" for="<?= $elementId; ?>-stock-<?= md5($branch->getId()); ?>">Stock</label>

                      <small>
                        <p class="text-muted">Ejemplo: 100, (No seleccionar si el stock inicial quedará en 0)</p>
                      </small>
                    </td>

                    <td>
                      <div class="form-group m-0">
                        <select id="<?= $elementId; ?>-stock-<?= md5($branch->getId()); ?>" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_stock_<?= md5($branch->getId()); ?>"></select>
                      </div>
                    </td>
                  </tr>

                  <tr class="align-middle">
                    <td>
                      <label class="form-label mb-0" for="<?= $elementId; ?>-serial_number-<?= md5($branch->getId()); ?>">Números de serie</label>

                      <small>
                        <p class="text-muted">Ejemplo: ABC123, DEF456, GHI789 (separados por comas)</p>
                      </small>
                    </td>

                    <td>
                      <div class="form-group m-0">
                        <select id="<?= $elementId; ?>-serial_number-<?= md5($branch->getId()); ?>" class="form-control form-select <?= $elementId; ?>-csv-match" name="position_serial_number_<?= md5($branch->getId()); ?>"></select>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div id="<?= $elementId; ?>-loading" style="display: none;">
    <div class="d-flex vh-100 vw-100 align-items-center justify-content-center position-fixed top-0 start-0 end-0 bottom-0" style="z-index: 9999; background-color: rgba(0,0,0,0.2);">
      <div class="card card-body text-center" style="max-width: 30rem;">
        <div class="mb-3">
          <div class="spinner-border text-primary" role="status"></div>
        </div>

        <h3 class="card-title">Importando productos</h3>
        <p class="card-description">Este proceso puede tardar varios minutos, no cierres la ventana</p>

        <div class="card card-body shadow-none border">
          <p id="<?= $elementId; ?>-loading-label" class="card-description m-0"></p>
        </div>
      </div>
    </div>
  </div>

  <div id="<?= $elementId; ?>-csv-match-button" class="col-12 mb-3 text-end" style="display: none;">
    <button class="btn btn-primary" type="submit">
      <i class="fa fa-upload me-2"></i>
      Importar productos
    </button>
  </div>
</form>

<!-- ---------------------------------------------------------------- -->

<!-- CS FILEPICKER -->
<script src="<?= BASE_URL; ?>/src/plugins/cs-filepicker/cs-filepicker.js"></script>

<script>
  const showImportLoading = () => $(`#<?= $elementId;  ?>-loading`).show();
  const hideImportLoading = () => $(`#<?= $elementId; ?>-loading`).hide();
</script>

<script>
  let csvOptions = `<option value="">--Seleccionar--</option>`;
  let csvRows = [];

  $cs_file_pickers_out_callbacks['csvFile'] = csvData => {
    const rows = csvData.split('\n');
    const rowsWithoutHeader = rows.slice(1);

    csvRows = rowsWithoutHeader.filter(row => row.trim() !== ""); // Filtrar filas vacías

    // console.log('csvRows:', csvRows);

    const columns = rows[0];

    // console.log('columns:', columns);
    const columnsMap = columns.split(',');

    csvOptions = `<option value="">--Seleccionar--</option>`;

    columnsMap.map((item, index) => {
      csvOptions += `<option value="${index}">${item}</option>`;
    });

    $('.<?= $elementId; ?>-csv-match').html(csvOptions);
    $('#<?= $elementId; ?>-csv-match').show();
    $('#<?= $elementId; ?>-csv-match-button').show();
  };
</script>

<!-- CS FILEPICKER INIT -->
<script src="<?= BASE_URL; ?>/src/plugins/cs-filepicker/cs-filepicker.init.js"></script>

<script>
  $("#<?= $elementId; ?>-prices_with_iva").on("click", function() {
    const isChecked = $(this).is(":checked");
    const vatLabel = $(".<?= $elementId; ?>-vat-label");
    const vatRow = $(".<?= $elementId; ?>-vat-row");

    if (isChecked) {
      vatLabel.html("(Con IVA)"));
    vatRow.hide();
  }

  if (!isChecked) {
    vatLabel.html("(Sin IVA)")); vatRow.show();
  }
  });

  $("#<?= $elementId; ?>-prices_with_ieps").on("click", function() {
    const isChecked = $(this).is(":checked");
    const iepsLabel = $(".<?= $elementId; ?>-ieps-label");
    const iepsRow = $(".<?= $elementId; ?>-ieps-row");

    if (isChecked) {
      iepsLabel.html("(Con IEPS)");
      iepsRow.hide();
    }

    if (!isChecked) {
      iepsLabel.html("(Sin IEPS)");
      iepsRow.show();
    }
  });
</script>

<script>
  const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

  // CORRECCIÓN: Ahora recibe el objeto 'currentAdjustIds' como segundo parámetro
  const _importProducts = async (products = [], currentAdjustIds = {}) => {
    const form = document.getElementById(`form-<?= $elementId; ?>`);
    const params = Object.fromEntries(new FormData(form).entries());

    params.products = JSON.stringify(products);
    // CORRECCIÓN: Enviamos los IDs actuales convertidos a JSON String para el PHP
    params.adjust_ids = JSON.stringify(currentAdjustIds);
    params.action = "importar-productos";

    try {
      const response = await callEndpoint({
        place: "productos",
        parameters: params,
        showLoading: false
      });

      if (!response) throw new Error("No se recibió respuesta del servidor");
      if (response.status != "success") return false;

      // CORRECCIÓN: Retornamos la respuesta completa del servidor para extraer los IDs en el ciclo
      return response;
    } catch (error) {
      console.error("Error al importar productos:", error);
      return false;
    }
  };

  $("#form-<?= $elementId; ?>").on("submit", async (e) => {
    e.preventDefault();

    // Mandar un alerta para que el usuario pueda verificar los campos que seleccionó y confirmar que es correcto o no
    const fieldMessages = [];

    $(".<?= $elementId; ?>-csv-match").each(function() {
      const id = $(this).attr("id");
      const label = $(`[for='${id}']`).html();
      const optionText = $(this).find("option:selected").text();
      const isHidden = $(this).closest("tr").is(":hidden");

      if (isHidden) return true;

      // fieldMessages.push(`<b>${label}</b>: [${optionText}]`);
      fieldMessages.push(`
        <tr>
          <td><b>${label}</b></td>

          <td>${optionText}</td>
        </tr>
      `);
    });

    const table = `
      <table class="table">
        <thead>
          <tr>
            <th>Campo del sistema</th>
            <th>Columna de tu archivo</th>
          </tr>
        </thead>

        <tbody>
         ${fieldMessages.join("")}
        </tbody>
      </table>
    `;

    //const fieldMessagesString = fieldMessages.join("<br>");

    const aResponse = await showSweetConfirm({
      title: "¿Todo listo para importar?",
      message: `Revisa que las columnas de tu archivo coincidan correctamente con los campos del sistema<br><br>${table}<br><br>Si la información es correcta, presiona Continuar. Si detectas algún error, haz clic en Cancelar para corregirlo`,
    });

    if (!aResponse) return;

    const abResponse = await showSweetConfirm({
      title: "⚠️ ¡Atención! Acción irreversible",
      message: "Solo puedes realizar <b>una importación masiva</b> por cuenta.<br><br>" +
        "Antes de dar el paso final, asegúrate de que cada producto tenga un <b>SKU único y correcto</b>. " +
        "Si notas algún error, estás a tiempo de <b>cancelar y corregir</b> tus datos ahora mismo.",
    });

    if (!abResponse) return;

    const rows = csvRows;
    const rowsLength = rows.length;
    const batchSize = 50;
    const batches = [];
    const delayTime = 2000;

    for (let i = 0; i < rows.length; i += batchSize) {
      batches.push(rows.slice(i, i + batchSize));
    }

    let totalProcesados = 0;
    let lote = 1;
    // CORRECCIÓN: Variable para mantener el rastro de los IDs generados entre lotes
    let activeAdjustIds = {};

    showImportLoading();

    for (const batch of batches) {
      const batchLength = batch.length;
      const messageForLoading = `
        <strong>Importando lote ${lote} de ${batches.length}...</strong><br>
        <span>Se han procesado <strong>${totalProcesados}</strong> de <strong>${rowsLength}</strong> productos</span>
      `;

      $("#<?= $elementId; ?>-loading-label").html(messageForLoading);

      // CORRECCIÓN: Pasamos 'activeAdjustIds' a la función de importación
      const importRes = await _importProducts(batch, activeAdjustIds);

      // CORRECCIÓN: Validamos contra false o nulo
      if (!importRes) {
        showSweetAlert({
          icon: "error",
          title: "Error al importar productos",
          message: `Ocurrió un error al importar el lote ${lote}, verifica que tu archivo tenga el formato correcto e intenta de nuevo.`
        });
        break;
      }

      // CORRECCIÓN: Guardamos los IDs que devolvió el backend para usarlos en el siguiente ciclo
      if (importRes.data && importRes.data.adjustIds) activeAdjustIds = importRes.data.adjustIds;

      totalProcesados += batchLength;
      lote++;

      $("#<?= $elementId; ?>-loading-label").html(`
        <strong>Lote ${lote - 1} de ${batches.length} completado</strong><br>
        <span>Se han procesado <strong>${totalProcesados}</strong> de <strong>${rowsLength}</strong> productos</span>
      `);

      if (totalProcesados < rowsLength) {
        // console.log("Esperando 2 segundos para el siguiente lote...");
        await delay(delayTime);
      }
    }

    hideImportLoading();

    if (totalProcesados === rowsLength) {
      $("#<?= $elementId; ?>-loading-label").html("");
      // console.log("🎉 ¡Todos los lotes han sido procesados!");

      showSweetAlert({
        icon: "success",
        title: "Importación completa",
        message: `Se importaron correctamente ${totalProcesados} productos.`
      }).then(() => {
        window.location.href = `${BASE_URL}/productos`;
      });
    }
  });
</script>


<!-- ---------------------------------------------------------------- -->