/*
const row = {
        productId,
        productName,
        unitMeasurementId,
        unitMeasurementName,
        keyProductServiceId,
        keyProductServiceName,
        quantity,
        unitPrice,
        amount,
        discount,
        taxObject,
        iva,
        ivaCurrency
      };
*/

class InvoiceTable {
  constructor({
    id,
    onRender = data => null,
    rounded = 0
  }) {
    this.state = {
      id,
      rows: [],
      rounded
    };

    this.onRender = onRender;
  }

  _addRow = row => {
    const id = `${row.productId}-${row.unitMeasurementId}-${row.keyProductServiceId}-${row.unitPrice}-${row.discount}-${row.taxObject}-${row.haveIVA}`;

    if (this.state.rows[id]) this.state.rows[id].quantity += row.quantity;

    if (!this.state.rows[id]) {
      row.id = id;
      this.state.rows[id] = row;
    }
  }

  _row = row => `
    <tr data-uid="${row.id}">
      <td>${row.unitMeasurementName}/${row.keyProductServiceName}</td>
      <td>${row.productName}</td>
      <td class="text-center">${row.quantity}</td>
      <td class="text-end">${formatNumberToCurrency(row.priceWithoutIVA)}</td>
      <td class="text-end">${formatNumberToCurrency(row.amountWithoutIVA)}</td>
      <td class="text-end">${formatNumberToCurrency(row.ivaCurrency)}</td>
      <td class="text-end">
        <a class="invoice-btn-remove-item text-danger py-0 px-2 ${this.state.id}-btn-remove-item" href="javascript:void(0)">
          <i class="fas fa-trash-alt"></i>
        </a>
      </td>
    </tr>
  `;

  _render = () => {
    let rows = this.state.rows;
    let strRows = ``;
    let rowsArray = [];

    let subtotal = 0;
    let discount = 0;
    let totalIVA = 0;
    let total = 0;

    for (const key in rows) {
      let row = rows[key];
      let iva = 0;
      let ivaCurrency = 0;
      let priceWithoutIVA = row.unitPrice;

      if (row.haveIVA) {
        iva = 16;
        priceWithoutIVA = parseFloat((row.unitPrice / 1.16).toFixed(4));
        ivaCurrency = (row.unitPrice - priceWithoutIVA) * row.quantity;
      }

      row.iva = iva;
      row.ivaCurrency = ivaCurrency;
      row.priceWithoutIVA = priceWithoutIVA;
      row.amountWithoutIVA = priceWithoutIVA * row.quantity;

      subtotal += priceWithoutIVA * row.quantity;
      discount += parseFloat(row.discount);
      totalIVA += row.ivaCurrency;

      strRows += this._row(row);
      rowsArray.push(row);
    }

    total = subtotal - discount + totalIVA + this.state.rounded;

    const table = `
      <div class="table-responsive">
        <table class="table">
          <thead class="table-light">
            <th>Unidad/Clave</th>
            <th>Concepto</th>
            <th class="text-center">Cantidad</th>
            <th class="text-end">Precio</th>
            <th class="text-end">Importe</th>
            <th class="text-end">IVA</th>
            <th></th>
          </thead>

          <tbody>
            ${strRows}
          </tbody>
        </table>

        <div class="ms-auto" style="max-width: 18rem;">
          <table class="table table-sm table-bordered text-dark">
            <tbody>
              <tr>
                <td class="text-end fw-bold bg-primary text-dark text-uppercase">Subtotal:</td>
                <td class="text-end">
                  ${formatNumberToCurrency(subtotal)}
                  <input name="subtotal" value="${subtotal.toFixed(DECIMALS_CURRENCY)}" type="hidden">
                </td>
              </tr>

              <tr>
                <td class="text-end fw-bold bg-primary text-dark text-uppercase">Descuento:</td>
                <td class="text-end">
                  ${formatNumberToCurrency(discount)}
                  <input name="totalDescuento" value="${discount.toFixed(DECIMALS_CURRENCY)}" type="hidden">
                </td>
              </tr>

              <tr>
                <td class="text-end fw-bold bg-primary text-dark text-uppercase">IVA:</td>
                <td class="text-end">
                  ${formatNumberToCurrency(totalIVA)}
                  <input name="totalIVA" value="${totalIVA.toFixed(DECIMALS_CURRENCY)}" type="hidden">
                </td>
              </tr>

              <tr>
                <td class="text-end fw-bold bg-primary text-dark text-uppercase">Total:</td>
                <td class="text-end">
                  ${formatNumberToCurrency(total)}
                  <input name="total" value="${total.toFixed(DECIMALS_CURRENCY)}" type="hidden">
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `;

    $(`#${this.state.id}-table`).html(table);

    $(`.${this.state.id}-btn-remove-item`).on('click', (e) => showSweetConfirm({
      title: 'Eliminar producto',
      message: '¿Estás seguro de eliminar este producto?',
    }).then(result => {
      if (!result) return;

      const uid = $(e.currentTarget).closest('tr').data('uid');
      this.state.rows[uid] = null;
      delete this.state.rows[uid];
      this._render();
    }));

    this.onRender(rowsArray);
  };
}
