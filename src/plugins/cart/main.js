class Cart {
  constructor({
    identifier,
    source,
    values = {},
    dynamicValues = () => null,

    onSuccessLoad = () => null,
    onErrorLoad = () => null,

    onSuccessAdd = () => null,
    onErrorAdd = () => null,

    onSuccessReduce = () => null,
    onErrorReduce = () => null,

    onSuccessRemove = () => null,
    onErrorRemove = () => null,

    onSuccessCleanCart = () => null,
    onErrorCleanCart = () => null,

    onSuccessUpdateItemQuantity = () => null,
    onErrorUpdateItemQuantity = () => null,

    onSuccessUpdateItemPrice = () => null,
    onErrorUpdateItemPrice = () => null,

    onSuccessUpdateRounding = () => null,
    onErrorUpdateRounding = () => null,

    onSuccessSaveCart = () => null,
    onErrorSaveCart = () => null,

    onSuccessCartAction = () => null,
    onErrorCartAction = () => null,

    onSuccessCartDispatch = () => null,
    onErrorCartDispatch = () => null,

    onSuccess = () => null,
    onError = () => null,

    loadingId = null,

    productsModalId = 'carrito-modal-productos',
    withSerialNumbersCatalog = false
  }) {
    this.state = {
      identifier,
      source,
      values,
      dynamicValues,
      loadingId: loadingId ? loadingId : identifier,
      currentSerialNumbersCount: 0,
      productsModalId,
      productsModal: {},
      withSerialNumbersCatalog: withSerialNumbersCatalog,
      currentSerialNumbersCatalog: []
    };

    this.onSuccessLoad = response => onSuccessLoad(response);
    this.onErrorLoad = response => onErrorLoad(response);

    this.onSuccessAdd = response => onSuccessAdd(response);
    this.onErrorAdd = response => onErrorAdd(response);

    this.onSuccessReduce = response => onSuccessReduce(response);
    this.onErrorReduce = response => onErrorReduce(response);

    this.onSuccessRemove = response => onSuccessRemove(response);
    this.onErrorRemove = response => onErrorRemove(response);

    this.onSuccessCleanCart = response => onSuccessCleanCart(response);
    this.onErrorCleanCart = response => onErrorCleanCart(response);

    this.onSuccessUpdateItemQuantity = response => onSuccessUpdateItemQuantity(response);
    this.onErrorUpdateItemQuantity = response => onErrorUpdateItemQuantity(response);

    this.onSuccessUpdateItemPrice = response => onSuccessUpdateItemPrice(response);
    this.onErrorUpdateItemPrice = response => onErrorUpdateItemPrice(response);

    this.onSuccessUpdateRounding = response => onSuccessUpdateRounding(response);
    this.onErrorUpdateRounding = response => onErrorUpdateRounding(response);

    this.onSuccessSaveCart = response => onSuccessSaveCart(response);
    this.onErrorSaveCart = response => onErrorSaveCart(response);

    this.onSuccessCartAction = response => onSuccessCartAction(response);
    this.onErrorCartAction = response => onErrorCartAction(response);

    this.onSuccessCartDispatch = response => onSuccessCartDispatch(response);
    this.onErrorCartDispatch = response => onErrorCartDispatch(response);

    this.onSuccess = response => onSuccess(response);
    this.onError = response => onError(response);
  }

  loadCart = () => {
    const parameters = new FormData();

    this._fetch({
      action: 'load',
      parameters,
      onSuccess: response => {
        this.onSuccessLoad(response);
        setTimeout(() => this.initCommentsPopup(), 300);
      },
      onError: response => this.onErrorLoad(response),
      typeLoad: true
    });
  }

  addItem = (itemId, quantity) => {
    if (quantity == 0 || !quantity) {
      showSweetToast({
        icon: 'warning',
        message: 'La cantidad no puede ser cero'
      });

      return;
    }

    this._fetch({
      action: 'add-item',
      parameters: {
        itemId,
        quantity
      },
      onSuccess: response => this.onSuccessAdd(response),
      onError: response => this.onErrorAdd(response)
    });
  }

  addItemWithCode = (code, quantity) => {
    if (quantity == 0 || !quantity) {
      showSweetToast({
        icon: 'warning',
        message: 'La cantidad no puede ser cero'
      });

      return;
    }

    this._fetch({
      action: 'add-item',
      parameters: {
        code,
        quantity
      },
      onSuccess: response => this.onSuccessAdd(response),
      onError: response => this.onErrorAdd(response)
    });
  }

  increaseItem = itemId => this.addItem(itemId, 1);

  reduceItem = itemId => this._fetch({
    action: 'reduce-item',
    parameters: {
      itemId
    },
    onSuccess: response => this.onSuccessReduce(response),
    onError: response => this.onErrorReduce(response)
  });

  updateRounding = rounding => this._fetch({
    action: 'update-rounding',
    parameters: {
      rounding
    },
    onSuccess: response => this.onSuccessUpdateRounding(response),
    onError: response => this.onErrorUpdateRounding(response),
    useGlobalOnSuccess: false
  });

  removeItem = async (itemId, title = 'producto') => {
    const alertResponse = await showSweetConfirm({
      title: '¡Cuidado!',
      message: `¿Realmente desea remover este ${title}?`
    });

    if (!alertResponse) return;

    this._fetch({
      action: 'remove-item',
      parameters: {
        itemId
      },
      onSuccess: response => this.onSuccessRemove(response),
      onError: response => this.onErrorRemove(response)
    });
  }

  updateItemQuantity = (itemId, quantity, useGlobalOnSuccess = true) => {
    if (!quantity) return;

    this._fetch({
      action: 'update-item-quantity',
      parameters: {
        itemId,
        quantity
      },
      useGlobalOnSuccess,
      rowLoading: itemId,
      onSuccess: response => this.onSuccessUpdateItemQuantity(response),
      onError: response => this.onErrorUpdateItemQuantity(response)
    });
  };

  updateItemPrice = (itemId, price) => {
    this._fetch({
      action: 'update-item-price',
      parameters: {
        itemId,
        price
      },
      useGlobalOnSuccess: false,
      rowLoading: itemId,
      onSuccess: response => this.onSuccessUpdateItemPrice(response),
      onError: response => this.onErrorUpdateItemPrice(response)
    });
  };

  cleanCart = async (useAlert = true, title = '¡Cuidado!', message = `¿Realmente desea vacíar el carrito?`) => {
    if (useAlert) {
      const alertResponse = await showSweetConfirm({
        title,
        message
      });

      if (!alertResponse) return;
    }

    this._fetch({
      action: 'clean-cart',
      parameters: {
        useAlert
      },
      onSuccess: response => this.onSuccessCleanCart(response),
      onError: response => this.onErrorCleanCart(response)
    });
  }

  saveCart = async (title = 'Guardar cambios', message = `¿Realmente desea guardar los cambios?`) => {
    const alertResponse = await showSweetConfirm({
      title,
      message
    });

    if (!alertResponse) return;

    this._fetch({
      action: 'save-cart',
      onSuccess: response => this.onSuccessSaveCart(response),
      onError: response => this.onErrorSaveCart(response)
    });
  }

  cartDispatch = action => {
    let parameters = this.state.values;

    if (this.state.dynamicValues()) {
      const dynamicValues = this.state.dynamicValues();

      for (const valueName in dynamicValues) {
        const value = dynamicValues[valueName];
        parameters[valueName] = value;
      }
    }

    this._fetch({
      action: `dispatch-${action}`,
      parameters,
      onError: response => this.onErrorCartDispatch(response),
      onSuccess: response => this.onSuccessCartDispatch(response)
    })
  }

  cartAction = (action, value, extraParams = {}) => {
    let params = {
      actionValue: value
    };

    // agregar extra params a params
    for (const key in extraParams) {
      params[key] = extraParams[key];
    }

    this._fetch({
      action: `action-${action}`,
      parameters: params,
      onError: response => this.onErrorCartAction(response),
      onSuccess: response => this.onSuccessCartAction(response)
    });
  }

  _fetch = ({
    action,
    parameters = {},
    onSuccess = data => console.log(data),
    onError = data => console.log(data),
    typeLoad = false,
    useGlobalOnSuccess = true,
    rowLoading = null
  }) => {
    const url = `${BASE_URL}/data/${this.state.source}/${this.state.source}_data.php`;

    const formData = new FormData();

    formData.append('action', `cart-${action}-${this.state.identifier}`);
    formData.append('identifier', this.state.identifier);

    for (const valueName in this.state.values) {
      const value = this.state.values[valueName];
      formData.append(valueName, value);
    }

    if (this.state.dynamicValues()) {
      const dynamicValues = this.state.dynamicValues();

      for (const valueName in dynamicValues) {
        const value = dynamicValues[valueName];
        formData.append(valueName, value);
      }
    }

    for (const valueName in parameters) {
      const value = parameters[valueName];
      formData.append(valueName, value);
    }

    if (!rowLoading) showCardLoading(this.state.loadingId);
    if (rowLoading) $(`#cart-actions-${this.state.identifier}-${rowLoading}`).addClass('loading');

    $.ajax({
      type: 'POST',
      enctype: 'multipart/form-data',
      url,
      data: formData,
      processData: false,
      contentType: false,
      cache: false,
      success: data => {
        if (!rowLoading) hideCardLoading(this.state.loadingId);
        if (rowLoading) $(`#cart-actions-${this.state.identifier}-${rowLoading}`).removeClass('loading');

        if (typeLoad) {
          onSuccess(data);
          return;
        }

        const response = JSON.parse(data);

        if (response.status === 'success') {
          onSuccess(response);
          if (useGlobalOnSuccess) this.onSuccess(response);
        } else {
          onError(response);
          this.onError(response);
        }
      },
      error: (e) => {
        console.log("ERROR : ", e);
        if (!rowLoading) hideCardLoading(this.state.loadingId);
        if (rowLoading) $(`#cart-actions-${this.state.identifier}-${rowLoading}`).removeClass('loading');
        onError({});
        this.onError({});
      }
    });
  }

  _serialNumberElement = (id, number, counter, catalog) => {
    const input = `
      <div id="${this.state.identifier}-serialNumberItem-${counter}" class="row">
        <div class="col-12">
          <div class="form-group">
            <div class="input-group">
              <input class="form-control" name="${this.state.identifier}-serialNumbers[]" placeholder="Número de serie" value="${number}" type="text" required>
              <input name="${this.state.identifier}-serialNumberIds[]" value="${id}" type="hidden" required>
      
              <a class="btn btn-danger ${this.state.identifier}-btn-remove-serial-number" data-target="#${this.state.identifier}-serialNumberItem-${counter}" href="javascript:void(0)">
                <i class="fa fa-times"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    `;

    let options = `<option value="">--Seleccionar--</option>`;

    for (const item of catalog) {
      options += `<option value="${item.numero_serie}" ${item.numero_serie == number ? 'selected' : ''}>${item.numero_serie}</option>`;
    }

    const select = `
      <div id="${this.state.identifier}-serialNumberItem-${counter}" class="row">
        <div class="col-12">
          <div class="form-group">
            <div class="input-group">
              <select class="form-control form-select" name="${this.state.identifier}-serialNumbers[]" required>
                ${options}
              </select>

              <input name="${this.state.identifier}-serialNumberIds[]" value="${id}" type="hidden" required>
      
              <a class="btn btn-danger ${this.state.identifier}-btn-remove-serial-number" data-target="#${this.state.identifier}-serialNumberItem-${counter}" href="javascript:void(0)">
                <i class="fa fa-times"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    `;

    if (this.state.withSerialNumbersCatalog) return select;

    return input;
  }

  _createSerialNumberInputs = (serialNumbers, catalog = []) => {
    let items = ``;
    let count = 0;

    this.state.currentSerialNumbersCatalog = catalog;

    serialNumbers.map(serialNumber => {
      const item = this._serialNumberElement(serialNumber.id, serialNumber.number, count, catalog);
      items += item;
      count++;
    });

    this.state.currentSerialNumbersCount = count;
    $(`#${this.state.identifier}-serialNumbers`).html(items);
    this._initRemoveSerialNumker();
  };

  _addSerialNumberInput = (serialNumber, catalog = []) => {
    let count = this.state.currentSerialNumbersCount;
    const item = this._serialNumberElement(serialNumber.id, serialNumber.number, count, catalog);
    count++;

    this.state.currentSerialNumbersCount = count;
    $(`#${this.state.identifier}-serialNumbers`).append(item);
    this._initRemoveSerialNumker();
  };

  _initAddSerialNumber = () => $(`#${this.state.identifier}-add-serial-number`).on('click', () => this._addSerialNumberInput({
    id: '',
    number: ''
  }, this.state.currentSerialNumbersCatalog));

  _initRemoveSerialNumker = () => $(`.${this.state.identifier}-btn-remove-serial-number`).on('click', function () {
    const target = $(this).attr('data-target');
    $(target).remove();
  });

  _initProductsModal = () => {
    const id = this.state.productsModalId;

    // DATA TABLE
    const datatable = new MultiDataTable({
      identifier: id
    });

    this.state.productsModal = datatable;

    datatable._initDataTable();

    $(`#btn-${id}`).on('click', () => datatable._load(id, 1));

    //const load = (page, identifier) => datatable._load(identifier, page);
  };

  _loadProductsModal = (page = 1) => this.state.productsModal._load(id, page);

  __createCommentsPopup = (element) => {
    const id = $(element).attr('data-id');
    const comment = $(element).attr('data-comment');

    // Remplazar el tag que tenga .{identifier}-comments-popup por un botón tipo anchor para evitar submits con un popup de comentarios, este es un formulario que se abre al dar click al botón
    const elementForReplace = `
      <div class="comments-popup dropdown dropstart">
        <a href="#" role="button" id="${this.state.identifier}-${id}-comments-popup" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa fa-comment"></i> ${!!comment ? comment : ""}
        </a>

        <div class="dropdown-menu p-3" aria-labelledby="${this.state.identifier}-${id}-comments-popup">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 header-title">Comentarios</h3>
            <button type="button" class="btn-close" data-bs-toggle="dropdown" aria-expanded="false"></button>
          </div>

          <form id="${this.state.identifier}-${id}-comments-form">
            <div class="form-group">
              <textarea class="form-control" name="comments" id="${this.state.identifier}-${id}-comments" rows="3" placeholder="Escribe tu comentario aquí...">${comment || ''}</textarea>
            </div>

            <input name="productId" value="${id}" type="hidden"/>

            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="dropdown">
                <i class="fa fa-times me-1"></i>Cerrar
              </button>

              <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                <i class="fa fa-save me-1"></i>Guardar
              </button>
            </div>
          </form>
        </div>
      </div>
    `;

    $(element).replaceWith(elementForReplace);

    // Agregarle eventos y despachar el evento de guardar comentarios al formulario
    $(`#${this.state.identifier}-${id}-comments-form`).on('submit', e => {
      e.preventDefault();

      const comments = $(`#${this.state.identifier}-${id}-comments`).val();

      if (!comments) showSweetToast({
        icon: 'warning',
        message: 'Debes ingresar un comentario'
      });

      if (comments) this.cartAction('comments', comments, {
        comment: comments,
        itemId: id
      });
    });
  }

  // Remplazar el tag que tenga .{identifier}-comments-popup por un botón tipo anchor para evitar submits con un popup de comentarios, este es un formulario que se abre al dar click al botón, cada tag tiene un data-id
  initCommentsPopup = () => {
    // Obtener tags
    const elements = $(`.${this.state.identifier}-comments-popup`);

    console.log('elements', elements);

    // Remplazar usando el método __createCommentsPopup
    elements.each((index, element) => {
      this.__createCommentsPopup(element);
    });
  }
};

function $cart_initDiscountformProps() {
  const itemId = $(this).attr('data-itemId');
  const branchId = $(this).attr('data-branchId');
  const price = $(this).attr('data-price');
  const netPrice = $(this).attr('data-netPrice');
  const discount = $(this).attr('data-discount');
  let discountLimit = $(this).attr('data-discountLimit');

  //discountLimit = 100;

  if (!discountLimit || discountLimit == 0) {
    // $('#fd-netPrice').prop('readonly', true);
    $('#fd-discount').prop('readonly', true);
    $('#discount-form').find('[type=submit]').prop('disabled', true);
  }

  if (discountLimit > 0) {
    // $('#fd-netPrice').prop('readonly', false);
    $('#fd-discount').prop('readonly', false);
    $('#discount-form').find('[type=submit]').prop('disabled', false);
  }

  $('#fd-branchId').val(branchId);
  $('#fd-itemId').val(itemId);
  $('#fd-price').val(price);
  $('#fd-netPrice').val(netPrice);
  $('#fd-discount').val(discount);
  $('#fd-discount').attr('max', discountLimit);
}

function $cart_updateItemQuantity(e) {
  e.preventDefault();

  const quantity = $(this).find('[name=quantity]').val();
  const itemId = $(this).find('[name=itemId]').val();

  storeCart.updateItemQuantity(itemId, quantity);
}

function $cart_updateRounding() {
  const rounding = $(this).val();
  const callback = () => storeCart.updateRounding(rounding);
  doSearch(callback);
}

function $cart_removeItem() {
  const itemId = $(this).attr('data-itemId');
  storeCart.removeItem(itemId);
}