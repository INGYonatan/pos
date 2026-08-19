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
        ivaCurrency,
        haveIEPS,
        iepsPercentage,
        iepsCurrency,
        serialNumbers,
        comments
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

  _toFloat = value => {
    const number = parseFloat(value);
    return Number.isNaN(number) ? 0 : number;
  }

  _normalizeBoolean = value => {
    return value === true || value === 1 || value === '1' || value === 'si';
  }

  _fixDecimals = (number, decimals = 4) => {
    const parsedNumber = this._toFloat(number);
    //const newNumber = parseFloat(parseFloat(parsedNumber.toFixed(Math.max(0, DECIMALS_CURRENCY))).toFixed(DECIMALS_CURRENCY));
    const newNumber = parseFloat(parsedNumber.toFixed(decimals));
    return newNumber;
  }

  _getRowFiscalData = row => {
    const quantity = this._toFloat(row.quantity);
    const unitPrice = this._toFloat(row.unitPrice);
    const amountWithoutTaxes = this._fixDecimals(unitPrice * quantity);
    const discount = this._fixDecimals(row.discount);
    const taxableBase = this._fixDecimals(amountWithoutTaxes - discount);
    const haveIEPS = this._normalizeBoolean(row.haveIEPS);
    const iepsPercentage = haveIEPS ? this._toFloat(row.iepsPercentage) : 0;
    const haveIVA = this._normalizeBoolean(row.haveIVA);
    let iva = haveIVA ? 16 : 0;

    if (row.iva !== undefined && row.iva !== null && row.iva !== '') {
      iva = this._toFloat(row.iva);
    }

    let iepsCurrency = row.iepsCurrency;
    if (iepsCurrency !== undefined && iepsCurrency !== null && iepsCurrency !== '') {
      iepsCurrency = this._fixDecimals(iepsCurrency);
    } else {
      iepsCurrency = haveIEPS
        ? this._fixDecimals(taxableBase * (iepsPercentage / 100))
        : 0;
    }

    let ivaCurrency = row.ivaCurrency;
    if (ivaCurrency !== undefined && ivaCurrency !== null && ivaCurrency !== '') {
      ivaCurrency = this._fixDecimals(ivaCurrency);
    } else {
      ivaCurrency = haveIVA
        ? this._fixDecimals((taxableBase + iepsCurrency) * (iva / 100))
        : 0;
    }

    return {
      quantity,
      unitPrice: this._fixDecimals(unitPrice),
      amountWithoutIVA: amountWithoutTaxes,
      amountWithoutTaxes,
      discount,
      taxableBase,
      haveIEPS,
      iepsPercentage,
      iepsCurrency,
      haveIVA,
      iva,
      ivaCurrency,
      totalTaxes: this._fixDecimals(iepsCurrency + ivaCurrency),
      total: this._fixDecimals(taxableBase + iepsCurrency + ivaCurrency)
    };
  }

  _addRow = row => {
    const normalizedRow = {
      ...row,
      quantity: this._toFloat(row.quantity),
      unitPrice: this._toFloat(row.unitPrice),
      amount: this._fixDecimals(row.amount),
      discount: this._fixDecimals(row.discount),
      haveIVA: this._normalizeBoolean(row.haveIVA),
      haveIEPS: this._normalizeBoolean(row.haveIEPS),
      iepsPercentage: this._toFloat(row.iepsPercentage),
      ivaCurrency: row.ivaCurrency !== undefined && row.ivaCurrency !== null && row.ivaCurrency !== '' ? this._fixDecimals(row.ivaCurrency) : null,
      iepsCurrency: row.iepsCurrency !== undefined && row.iepsCurrency !== null && row.iepsCurrency !== '' ? this._fixDecimals(row.iepsCurrency) : null,
      serialNumbers: Array.isArray(row.serialNumbers) ? row.serialNumbers : []
    };

    const concatIDSaleFolio = normalizedRow.saleFolio ? `-${normalizedRow.saleFolio}` : '';
    const id = `${normalizedRow.productId}-${normalizedRow.unitMeasurementId}-${normalizedRow.keyProductServiceId}-${normalizedRow.unitPrice}-${normalizedRow.discount}-${normalizedRow.taxObject}-${normalizedRow.haveIVA}-${normalizedRow.haveIEPS}-${normalizedRow.iepsPercentage}${concatIDSaleFolio}`;

    if (this.state.rows[id]) {
      this.state.rows[id].quantity = this._toFloat(this.state.rows[id].quantity) + normalizedRow.quantity;
      this.state.rows[id].amount = this._fixDecimals(this._toFloat(this.state.rows[id].amount) + normalizedRow.amount);
      this.state.rows[id].discount = this._fixDecimals(this._toFloat(this.state.rows[id].discount) + normalizedRow.discount);

      if (this.state.rows[id].ivaCurrency !== null || normalizedRow.ivaCurrency !== null) {
        this.state.rows[id].ivaCurrency = this._fixDecimals(this._toFloat(this.state.rows[id].ivaCurrency) + this._toFloat(normalizedRow.ivaCurrency));
      }

      if (this.state.rows[id].iepsCurrency !== null || normalizedRow.iepsCurrency !== null) {
        this.state.rows[id].iepsCurrency = this._fixDecimals(this._toFloat(this.state.rows[id].iepsCurrency) + this._toFloat(normalizedRow.iepsCurrency));
      }

      this.state.rows[id].serialNumbers = [
        ...new Set([...(this.state.rows[id].serialNumbers ?? []), ...normalizedRow.serialNumbers])
      ];
    }

    if (!this.state.rows[id]) {
      normalizedRow.id = id;
      this.state.rows[id] = normalizedRow;
    }
  }

  __createCommentsPopup = (idO, comment) => {
    // encode como url
    const id = idO.replace(/\./g, '-');

    const element = `
      <div class="comments-popup dropdown dropstart">
        <a href="#" role="button" id="invoice-${id}-comments-popup" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa fa-comment"></i> ${!!comment ? comment : ""}
        </a>

        <div class="dropdown-menu p-3" aria-labelledby="invoice-${id}-comments-popup">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 header-title">Comentarios</h3>
            <button type="button" class="btn-close" data-bs-toggle="dropdown" aria-expanded="false"></button>
          </div>

          <div class="invoice-comments-form">
            <div class="form-group">
              <textarea id="invoice-${id}-comments" class="form-control" name="comments" rows="3" placeholder="Escribe tu comentario aquí...">${comment || ''}</textarea>
            </div>

            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="dropdown">
                <i class="fa fa-times me-1"></i>Cerrar
              </button>

              <button data-id="${idO}" type="button" class="btn btn-primary btn-sm flex-grow-1 btn-invoice-comments-save">
                <i class="fa fa-save me-1"></i>Guardar
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    return element;
  }

  _row = ({ row }) => {
    const saleFolioTD = row.saleFolio ? `<td class="text-center">${row.saleFolio}</td>` : ``;

    let productName = row.productName;
    let comments = row.comments;

    //if (comments) productName += `<br><small class="text-muted"><i class="fa fa-comment"></i> ${comments}</small>`;

    return `
    <tr data-uid="${row.id}">
      <td>${row.unitMeasurementName}/${row.keyProductServiceName}</td>
      ${saleFolioTD}
      <td>
        ${productName}
        ${this.__createCommentsPopup(row.id, row.comments)}
      </td>
      <td class="text-center">${row.quantity}</td>
      <td class="text-end">${formatNumberToCurrency(row.priceWithoutIVA)}</td>
      <td class="text-end">${formatNumberToCurrency(row.amountWithoutIVA)}</td>
      <td class="text-end">${formatNumberToCurrency(row.discount)}</td>
      <td class="text-end">${formatNumberToCurrency(row.iepsCurrency)}</td>
      <td class="text-end">${formatNumberToCurrency(row.ivaCurrency)}</td>
      <td class="text-end">${formatNumberToCurrency(row.total)}</td>
      <td class="text-end">
        <a class="invoice-btn-remove-item text-danger py-0 px-2 ${this.state.id}-btn-remove-item" href="javascript:void(0)">
          <i class="fas fa-trash-alt"></i>
        </a>
      </td>
    </tr>
  `};

  _render = () => {
    let rows = this.state.rows;
    let strRows = ``;
    let rowsArray = [];

    let subtotal = 0;
    let discount = 0;
    let totalIEPS = 0;
    let totalIVA = 0;
    let total = 0;
    let haveSaleFolio = false;

    for (const key in rows) {
      const row = rows[key];

      if (row.saleFolio) haveSaleFolio = true;

      const normalizedRow = {
        ...row,
        ...this._getRowFiscalData(row),
        priceWithoutIVA: this._fixDecimals(row.unitPrice)
      };

      subtotal += normalizedRow.amountWithoutIVA;
      discount += normalizedRow.discount;
      totalIEPS += normalizedRow.iepsCurrency;
      totalIVA += normalizedRow.ivaCurrency;
      rowsArray.push(normalizedRow);
    }

    for (const row of rowsArray) {
      strRows += this._row({ row });
    }

    subtotal = this._fixDecimals(subtotal, 2);
    discount = this._fixDecimals(discount);
    totalIEPS = this._fixDecimals(totalIEPS);
    totalIVA = this._fixDecimals(totalIVA, 2);
    total = this._fixDecimals(subtotal - discount + totalIEPS + totalIVA + this.state.rounded, 2);

    const saleFolioTH = haveSaleFolio ? `<th class="text-center">No. Identificación</th>` : ``;

    const table = `
      <div class="table-responsive">
        <table class="table">
          <thead class="table-light">
            <th>Unidad/Clave</th>
            ${saleFolioTH}
            <th>Concepto</th>
            <th class="text-center">Cantidad</th>
            <th class="text-end">Precio Unitario</th>
            <th class="text-end">Importe</th>
            <th class="text-end">Descuento</th>
            <th class="text-end">IEPS</th>
            <th class="text-end">IVA</th>
            <th class="text-end">Total</th>
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
                  <input name="subtotal" value="${subtotal}" type="hidden">
                </td>
              </tr>

              <tr>
                <td class="text-end fw-bold bg-primary text-dark text-uppercase">Descuento:</td>
                <td class="text-end">
                  ${formatNumberToCurrency(discount)}
                  <input name="totalDescuento" value="${discount}" type="hidden">
                </td>
              </tr>

              <tr>
                <td class="text-end fw-bold bg-primary text-dark text-uppercase">IEPS:</td>
                <td class="text-end">
                  ${formatNumberToCurrency(totalIEPS)}
                </td>
              </tr>

              <tr>
                <td class="text-end fw-bold bg-primary text-dark text-uppercase">IVA:</td>
                <td class="text-end">
                  ${formatNumberToCurrency(totalIVA)}
                  <input name="totalIVA" value="${totalIVA}" type="hidden">
                </td>
              </tr>

              <tr>
                <td class="text-end fw-bold bg-primary text-dark text-uppercase">Total:</td>
                <td class="text-end">
                  ${formatNumberToCurrency(total)}
                  <input name="total" value="${total}" type="hidden">
                </td>
              </tr>
            </tbody>
          </table>
          <input name="totalIEPS" value="${totalIEPS}" type="hidden">
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

    $(".btn-invoice-comments-save").on("click", (e) => {
      const id = $(e.currentTarget).data("id");
      const encodedId = id.replace(/\./g, '-');

      const comments = $(`#invoice-${encodedId}-comments`).val();

      if (this.state.rows[id]) {
        this.state.rows[id].comments = comments;
        this._render();
      }
    });

    this.onRender(rowsArray);
  };
}
