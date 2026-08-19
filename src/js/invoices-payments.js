class InvoicesPayments {
  constructor({
    selector,
    paymentMethodsCatalog
  }) {
    this.state = {
      selector,
      invoiceCount: 0,
      paymentsCount: 0,
      paymentMethodsCatalog
    };
  }

  _init = () => {
    $(`#${this.state.selector}-btn-add-row`).on("click", this._addInvoiceRow);
  }

  _invoiceRow = ({
    index
  }) => `
    <div class="card">
      <div class="card-body card-body-with-remove">
        <div class="row">
          <div class="col-12 col-md-8 col-lg-6">
            <div class="form-group">
              <label class="form-label" for="fd-cfdi_relacionado">CFDI Relacionado<span class="text-danger">*</span> <span class="fw-light">CFDI (Formato: 17CD1DDC-FF52-4D68-A3C8-AC336FBF7FD0)</span></label>
              <input id="${this.state.selector}-${index}-fd-cfdi_relacionado" class="form-control" name="cfdi_relacionado[]" type="text" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div id="${this.state.selector}-${index}-payments" class="col-12"></div>
        </div>

        <div class="row">
          <div class="col-12 text-center">
            <button class="btn btn-secondary ${this.state.selector}-btn-add-payment" data-index="${index}" data-invoice="#${this.state.selector}-${index}-fd-cfdi_relacionado" type="button">
              <i class="fa fa-dollar-sign me-1"></i>Agregar Pago
            </button>
          </div>
        </div>

        <a class="text-danger btn-remove ${this.state.selector}-remove" href="javascript:void(0)">
          <i class="fas fa-trash-alt"></i>
        </a>
      </div>
    </div>
  `;

  _paymentRow = ({ invoice, index }) => `
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="fdf-metodo_pago">Método de pago<span class="text-danger">*</span></label>

              <select id="fdf-metodo_pago" class="form-control form-select" name="metodo_pago" required>
                <option value="">--Seleccionar--</option>
                <option value="PUE">Pago de una sola exibición</option>
                <option value="PPD">Pago en parciales o diferido</option>
              </select>
            </div>
          </div>

          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="fdf-id_forma_pago">Forma de pago<span class="text-danger">*</span></label>

              <select id="fdf-id_forma_pago" class="form-control form-select" name="id_forma_pago" required>
                <option value="">--Seleccionar--</option>

                ${this.state.paymentMethodsCatalog}
              </select>
            </div>
          </div>

          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group">
              <label class="form-label" for="fdf-moneda">Moneda<span class="text-danger">*</span></label>

              <input id="fdf-moneda" class="form-control datepicker" name="moneda" value="Peso Mexicano" type="text" readonly required>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  _addInvoiceRow = () => {
    $(`#${this.state.selector}-container`).append(this._invoiceRow({
      index: this.state.invoiceCount
    }));

    this.state.invoiceCount++;
    this._initBtnRemove();
    this._initBtnAddPayment();
  }

  _initBtnRemove = () => {
    $(`.${this.state.selector}-remove`).on("click", function () {
      showSweetConfirm({
        icon: "warning",
        title: "¡Atención!",
        message: "¿Estás seguro de quitar este registro?",
      }).then((result) => {
        if (!result) return;

        $(this).parent().parent().remove();
      });
    });
  }

  _initBtnAddPayment = () => {
    const searchInvoice = this._searchInvoice;

    $(`.${this.state.selector}-btn-add-payment`).on("click", function () {
      const invoiceSelector = $(this).data("invoice");
      const index = $(this).data("index");
      const invoice = $(invoiceSelector).val();

      if (!invoice) {
        showSweetToast({
          icon: "warning",
          message: "Debes de agregar un CFDI relacionado.",
        });

        return;
      }

      searchInvoice(invoice, index);
    });
  }

  _searchInvoice = invoice => callEndpoint({
    place: "facturas-nueva",
    parameters: {
      action: "search-invoice",
      invoice
    }
  }).then(response => {
    if (response.toastMessage) showSweetToast({
      icon: response.status,
      message: response.toastMessage
    });

    if (response.status === "success") {
      $(`#${this.state.selector}-${index}-payments`).append(this._paymentRow({
        invoice: invoice,
        data: response.data,
        index: this.state.paymentsCount
      }));

      this.state.paymentsCount++;
    }
  });
}