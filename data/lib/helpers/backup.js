let customers = [];
let products = [];
let invoiceRows = [];

const getInvoiceNotes = (customerId) => {
  $.ajax({
    url: `${BASE_URL}/data/${PAGE_CONFIG.page_identifier}/${PAGE_CONFIG.page_identifier}_data.php`,
    type: "POST",
    data: {
      action: "get-invoice-notes",
      customerId
    },
    success: function (response) {
      $("#customer-notes").html(response);
    }
  });
};

const proccessNote = (orderId, note) => new Promise((resolve, reject) => callEndpoint({
  place: PAGE_CONFIG.page_identifier,
  parameters: {
    action: "add-invoice-item",
    orderId,
    note
  }
}).then(response => {
  if (response.toastMessage) showSweetToast({
    icon: response.status,
    message: response.toastMessage
  });

  if (response.status !== "success") resolve(false);

  if (response.status === "success") {
    invoiceRows[`${orderId}-${note}-journey`] = response.data.journey;

    if (response.data.freight?.quantity) invoiceRows[`${orderId}-${note}-freight`] = response.data.freight;

    renderInvoiceTable();
  }
}));

const invoiceTableRow = ({
  id,
  quantity,
  unit,
  concept,
  price,
  iva,
  ivaCurrency,
  ivaRetention,
  ivaRetentionCurrency,
  amount
}) => `
      <tr>
        <td class="text-center">${quantity}</td>
        <td class="text-center">${unit}</td>
        <td>${concept}</td>
        <td class="text-end">${formatNumberToCurrency(price)}</td>
        <td class="text-center">${formatNumberToCurrencyWithoutSymbol(iva)}% | ${formatNumberToCurrency(ivaCurrency)}</td>
        <td class="text-end">${formatNumberToCurrencyWithoutSymbol(ivaRetention)}% | ${formatNumberToCurrency(ivaRetentionCurrency)}</td>
        <td class="text-end">${formatNumberToCurrency(amount)}</td>
        <td class="text-end">
          <button class="btn btn-danger btn-sm" type="button" onclick="removeInvoiceItem('${id}')">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
    `;

const removeInvoiceItem = (id) => {
  delete invoiceRows[id];
  renderInvoiceTable();

  let rowId = id.replace("-journey", "");
  rowId = rowId.replace("-freight", "");

  if (!invoiceRows[rowId + "-journey"] && !invoiceRows[rowId + "-freight"]) {
    const orderId = rowId.split("-")[0];
    const note = rowId.split("-")[1];

    console.log(orderId, note);

    $(`.fdf-note[data-orderId='${orderId}'][data-note='${note}']`).prop("checked", false);
  }
};

const renderInvoiceTable = () => {
  let rows = ``;

  let subtotal = 0;
  let totaliva = 0;
  let totalivaRetention = 0;
  let total = 0;

  for (const key in invoiceRows) {
    const invoiceRow = invoiceRows[key];

    subtotal += invoiceRow.price;
    totaliva += invoiceRow.ivaCurrency;
    totalivaRetention += invoiceRow.ivaRetentionCurrency;

    rows += invoiceTableRow(invoiceRow);
  }

  total = subtotal + totaliva - totalivaRetention;

  const totalsRows = `
        <tr>
          <td colspan="6" class="text-end fw-bold">Subtotal:</td>
          <td class="text-end">${formatNumberToCurrency(subtotal)}</td>
          <td colspan="2"></td>
        </tr>

        <tr>
          <td colspan="6" class="text-end fw-bold">IVA:</td>
          <td class="text-end">${formatNumberToCurrency(totaliva)}</td>
          <td colspan="2"></td>
        </tr>

        <tr>
          <td colspan="6" class="text-end fw-bold">Retención IVA:</td>
          <td class="text-end">${formatNumberToCurrency(totalivaRetention)}</td>
          <td colspan="2"></td>
        </tr>

        <tr>
          <td colspan="6" class="text-end fw-bold">Total:</td>
          <td class="text-end">${formatNumberToCurrency(total)}</td>
          <td colspan="2"></td>
        </tr>
      `;

  const table = `
        <table class="table table-sm table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th class="text-center">Cantidad</th>
              <th class="text-center">Unidad</th>
              <th>Concepto</th>
              <th class="text-end">Precio</th>
              <th class="text-center">IVA</th>
              <th class="text-end">Retención IVA</th>
              <th class="text-end">Importe</th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            ${rows}
            ${totalsRows}
          </tbody>
        </table>
      `;

  $("#invoice-table").html(table);
}

$(function () {
  $("#fdf-id_cliente").select2({
    theme: "bootstrap",
    ajax: {
      url: "<?= BASE_URL; ?>/data/autocompletes/clientes_data.php",
      dataType: 'json',
      delay: 250,
      processResults: function (data, params) {
        params.page = params.page || 1;

        console.log(data);

        customers = data.results;

        return {
          results: data.results,
          pagination: {
            more: false
          }
        };
      }
    }
  });

  $("#fdf-id_cliente").on('select2:select', function (e) {
    let selectedCustomer = customers.find(customer => customer.id == e.params.data.id);

    if (selectedCustomer) {
      $("#fdf-razon_social").val(selectedCustomer.businessName);
      $("#fdf-cliente_rfc").val(selectedCustomer.rfc);
      $("#fdf-id_regimen_fiscal").val(selectedCustomer.taxRegimeId).trigger('change');
      $("#fdf-cliente_domicilio_fiscal").val(selectedCustomer.taxResidence);
      $("#fdf-correo").val(selectedCustomer.email);
    }
  });

  $("#fdf-id_producto").select2({
    theme: "bootstrap",
    ajax: {
      url: "<?= BASE_URL; ?>/data/autocompletes/productos_data.php",
      dataType: 'json',
      delay: 250,
      processResults: function (data, params) {
        params.page = params.page || 1;

        console.log(data);

        customers = data.results;

        return {
          results: data.results,
          pagination: {
            more: false
          }
        };
      }
    }
  });


  $("#fdf-id_producto").on('select2:select', function (e) {
    let selectedProduct = products.find(products => products.id == e.params.data.id);

    if (selectedProduct) {
      $("#fdf-id_clave_unidad").val(selectedProduct.unitId).trigger('change');
      $("#fdf-id_clave_producto_servicio").val(selectedProduct.productServiceId).trigger('change');
      $("#fdf-cantidad").val(selectedProduct.quantity);
      $("#fdf-precio_unitario").val(selectedProduct.price);
      $("#fdf-importe").val(selectedProduct.amount);
      $("#fdf-descuento").val(selectedProduct.discount);
      $("#fdf-objeto_impuesto").val(selectedProduct.taxObject).trigger('change');
      $("#fdf-iva").val(selectedProduct.iva);
    }
  });

  $(document).on("click", ".fdf-note", async function () {
    const orderId = $(this).attr("data-orderId");
    const note = $(this).attr("data-note");

    const isChecked = $(this).prop("checked");

    if (!isChecked) {
      delete invoiceRows[`${orderId}-${note}-journey`];
      delete invoiceRows[`${orderId}-${note}-freight`];

      renderInvoiceTable();
    }

    if (isChecked) {
      const isValid = await proccessNote(orderId, note);

      if (!isValid) $(this).prop("checked", false);
    }
  });
});