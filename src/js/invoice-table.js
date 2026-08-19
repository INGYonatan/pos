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
    onRender = data => null
  }) {
    this.state = {
      id,
      rows: []
    };

    this.onRender = onRender;
  }

  _setRows = (rows) => this.state.rows = rows;

  _getRows = () => this.state.rows;

  _addRow = (row) => {
    //const ivaCurrency = (row.unitPrice * row.iva) / 100;
    let iva = row.iva;
    let ivaCurrency = 0;
    let unitPrice = row.unitPrice;

    if (iva > 0) {
      iva = 16;
      unitPrice = parseFloat((row.unitPrice / 1.16).toFixed(DECIMALS_CURRENCY)); // Aquí de 9280 me está dando 8000.000000000001 ¿Por qué?, eso esta mal //Corrigelo por favor
      ivaCurrency = row.unitPrice - unitPrice;
    }

    const newPrice = unitPrice;

    row.ivaCurrency = (ivaCurrency) * row.quantity;
    row.unitPrice = newPrice;

    this.state.rows.push(row);
  }

  _removeRow = (index) => this.state.rows.splice(index, 1);

  _row = ({
    productId,
    productName,
    unitMeasurementName,
    keyProductServiceName,
    quantity,
    unitPrice,
    discount,
    ivaCurrency
  }) => `
    <tr data-uid="${productId}">
      <td>${unitMeasurementName}/${keyProductServiceName}</td>
      <td>${productName}</td>
      <td class="text-end">${formatNumberToCurrency(unitPrice)}</td>
      <td class="text-center">${quantity}</td>
      <!-- <td class="text-end">${formatNumberToCurrency(discount)}</td> -->
      <td class="text-end">${formatNumberToCurrency((unitPrice * quantity))}</td>
      <td class="text-end">${formatNumberToCurrency(ivaCurrency)}</td>
      <td class="text-end">
        <button class="btn btn-danger btn-sm ${this.state.id}-btn-remove-item" type="button">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
  `;

  _render = () => {
    const rows = this._getRows();

    let strRows = ``;

    let subtotal = 0;
    let discount = 0;
    let iva = 0;
    let total = 0;

    for (const key in rows) {
      let row = rows[key];

      subtotal += row.unitPrice * row.quantity;
      discount += parseFloat(row.discount);
      iva += row.ivaCurrency;

      strRows += this._row(row);
    }

    total = subtotal - discount + iva;

    const rowOfTotals = `
      <tr>
        <td colspan="5" class="text-end fw-bold">Subtotal:</td>
        <td class="text-end">
          ${formatNumberToCurrency(subtotal)}
          <input name="subtotal" value="${subtotal}" type="hidden" />
        </td>
        <td colspan="2"></td>
      </tr>

      <tr>
        <td colspan="5" class="text-end fw-bold">Descuento:</td>
        <td class="text-end">
          ${formatNumberToCurrency(discount)}
          <input name="totalDescuento" value="${discount}" type="hidden" />
        </td>
        <td colspan="2"></td>
      </tr>

      <tr>
        <td colspan="5" class="text-end fw-bold">IVA:</td>
        <td class="text-end">
          ${formatNumberToCurrency(iva)}
          <input name="totalIVA" value="${iva}" type="hidden" />
        </td>
        <td colspan="2"></td>
      </tr>

      <tr>
        <td colspan="5" class="text-end fw-bold">Total:</td>
        <td class="text-end">
          ${formatNumberToCurrency(total)}
          <input name="total" value="${total}" type="hidden" />
        </td>
        <td colspan="2"></td>
      </tr>
    `;

    const table = `
      <table class="table table-sm table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>Unidad/Clave</th>
            <th>Concepto</th>
            <th class="text-end">Precio</th>
            <th class="text-center">Cantidad</th>
            <!-- <th class="text-end">Descuento</th> -->
            <th class="text-end">Importe</th>
            <th class="text-end">IVA</th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          ${strRows}
          ${rowOfTotals}
        </tbody>
      </table>
    `;

    $(`#${this.state.id}-table`).html(table);

    $(`.${this.state.id}-btn-remove-item`).on('click', (e) => showSweetConfirm({
      title: 'Eliminar producto',
      message: '¿Estás seguro de eliminar este producto?',
    }).then(result => {
      if (!result) return;

      const uid = $(e.currentTarget).closest('tr').data('uid');
      const index = this.state.rows.findIndex(row => row.productId == uid);

      this._removeRow(index);
      this._render();
    }));

    this.onRender(rows);
  };
}
