const useInventoryTransfersReceived = () => {
  let products = [];
  let useAdjustInventory = false;

  const tableContainer = $("#completar-traspaso-productos-table");
  const ajusteInventarioContainer = $("#ajuste-inventario-container");
  const serialNumbersContainer = $("#completar-traspaso-numeros-serie-para-ajuste-container");

  const renderProductsTable = (productsToComplete) => {
    products = [];

    const tableRows = productsToComplete.map(product => {
      const inventoryTransferProductId = product.inventoryTransferProductId;
      const productId = product.productId;
      const code = product.code;
      const name = product.name;
      const quantity = parseFloat(product.quantity);
      const requiresSerialNumbers = product.requiresSerialNumbers || false;
      const serialNumbers = product.serialNumbers || [];

      products[productId] = {
        inventoryTransferProductId,
        productId,
        code,
        name,
        quantity,
        receivedQuantity: quantity,
        remainingQuantity: 0,
        requiresSerialNumbers,
        serialNumbers,
        serialNumbersToDiscard: []
      };

      const addSerialNumbersButton = requiresSerialNumbers ? `
        <a id="btn-completar-traspaso-manage-serial-numbers-${productId}" style="display: none"
          class="btn btn-sm btn-secondary btn-manage-serial-numbers btn-xs mt-2"
          data-bs-toggle="modal"
          data-bs-target="#inventario-traspasos-recibidos-modal-completar-traspaso-numeros-serie-para-ajuste"
          data-uid="${productId}">
          Gestionar números de serie
        </a>
      ` : ``;

      return `
        <tr class="align-middle">
          <td>
            ${code}
          </td>

          <td>
            ${name}
          </td>

          <td class="text-center">
            ${quantity}
          </td>

          <td class="text-center">
            <input style="max-width: 6rem;"
              class="form-control completar-traspaso-cantidad mx-auto text-center"
              min="0"
              max="${quantity}"
              value="${quantity}"
              data-uid="${productId}"
              type="number"
              required>

            ${addSerialNumbersButton}
          </td>
        </tr>
    `}).join("");

    const tableHTML = `
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr class="align-middle">
              <th class="text-center" style="width: 15%;">Código</th>
              <th class="text-start" style="width: 45%;">Producto</th>
              <th class="text-center" style="width: 20%;">Cantidad enviada</th>
              <th class="text-center" style="width: 20%;">Cantidad recibida</th>
            </tr>
          </thead>

          <tbody>
            ${tableRows}
          </tbody>
        </table>
      </div>
    `;

    tableContainer.html(tableHTML);
    initializeEventListeners();
    verifyIfNeedsAdjustInventory();
  }

  const initializeEventListeners = () => {
    onKeyUpQuantity();
    onClickManageSerialNumbers();
  }

  const onKeyUpQuantity = () => $(".completar-traspaso-cantidad").on("keyup change", function () {
    const uid = $(this).data("uid");
    const max = $(this).attr("max");

    let newQuantity = parseFloat($(this).val());

    if (isNaN(newQuantity) || newQuantity < 0) return;

    if (newQuantity > max) newQuantity = max;

    const product = products[uid];
    product.receivedQuantity = newQuantity;
    product.remainingQuantity = product.quantity - newQuantity;

    $(this).val(newQuantity);

    products[uid] = product;

    if (product.remainingQuantity && product.requiresSerialNumbers) $(`#btn-completar-traspaso-manage-serial-numbers-${uid}`).fadeIn();
    else $(`#btn-completar-traspaso-manage-serial-numbers-${uid}`).fadeOut();

    product.serialNumbersToDiscard = [];

    products[uid] = product;

    verifyIfNeedsAdjustInventory();
  });

  const onClickManageSerialNumbers = () => $(".btn-manage-serial-numbers").on("click", function () {
    const uid = $(this).data("uid");
    const product = products[uid];

    const serialNumbersSelectHTML = getSerialNumbersSelect(product.serialNumbers, product.serialNumbersToDiscard);

    serialNumbersContainer.html(serialNumbersSelectHTML);

    // Inicializar el select2
    setTimeout(() => {
      $(".completar-traspaso-ajuste-numeros-serie-select").select2({
        width: '100%',
        height: '200px',
        placeholder: '--Seleccionar--',
        allowClear: true,
        dropdownParent: $("#inventario-traspasos-recibidos-modal-completar-traspaso-numeros-serie-para-ajuste"),
        maximumSelectionLength: product.remainingQuantity,
        language: {
          // personaliza el mensaje (opcional)
          maximumSelected: function (args) {
            return 'Solo puedes seleccionar ' + args.maximum + ' números de serie.';
          }
        }
      });
    }, 100);

    //completar-traspaso-producto-uid
    $("#completar-traspaso-producto-uid").val(uid);
  });

  const verifyIfNeedsAdjustInventory = () => {
    useAdjustInventory = products.some(product => product.remainingQuantity > 0);

    if (useAdjustInventory) ajusteInventarioContainer.slideDown();
    if (!useAdjustInventory) ajusteInventarioContainer.slideUp();
  }

  const getSerialNumbersSelect = (serialNumbers, selectedSerialNumbers) => {
    const options = serialNumbers.map(sn => `<option value="${sn}" ${selectedSerialNumbers.includes(sn) ? 'selected' : ''}>${sn}</option>`).join("");

    return `
      <div class="col-12">
        <div class="form-group">
          <select id="completar-traspaso-ajuste-numeros-serie-select" class="form-control form-select completar-traspaso-ajuste-numeros-serie-select select2" multiple required>
            ${options}
          </select>
        </div>
      </div>
    `;
  };

  const getData = () => ({
    products,
    useAdjustInventory
  });

  // Initialize forms
  $("#completar-traspaso-numeros-serie-para-ajuste-form").on("submit", function (e) {
    e.preventDefault();

    const serialNumbersToDiscard = $(".completar-traspaso-ajuste-numeros-serie-select").val() || [];
    const totalSelected = serialNumbersToDiscard.length;

    const uid = $("#completar-traspaso-producto-uid").val();
    const product = products[uid];

    if (totalSelected < product.remainingQuantity) {
      showSweetToast({
        icon: "warning",
        message: `Debes seleccionar al menos ${product.remainingQuantity} números de serie para descartar.`
      });

      return;
    }

    product.serialNumbersToDiscard = serialNumbersToDiscard;
    products[uid] = product;

    $(`#inventario-traspasos-recibidos-modal-completar-traspaso-numeros-serie-para-ajuste`).modal('hide');
  });

  const validateAllSerialNumbers = () => {
    for (const uid in products) {
      const product = products[uid];

      if (product.remainingQuantity && product.requiresSerialNumbers) {
        if (product.serialNumbersToDiscard.length < product.remainingQuantity) {
          showSweetToast({
            icon: "warning",
            message: `Debes seleccionar al menos ${product.remainingQuantity} números de serie para descartar del producto ${product.name}.`
          });

          return false;
        }
      }
    }

    return true;
  }

  return {
    renderProductsTable,
    validateAllSerialNumbers,
    getData
  };
};

const inventoryTransfersReceived = useInventoryTransfersReceived();

$(document).on("click", ".btn-completar-traspaso", function () {
  const data = JSON.parse($(this).attr("data-row"));
  const productsToComplete = data.productsToComplete;

  inventoryTransfersReceived.renderProductsTable(productsToComplete);
});

$("#inventario-traspasos-recibidos-completar-traspaso-form").on("submit", function (e) {
  e.preventDefault();

  const {
    products
  } = inventoryTransfersReceived.getData();

  const transferId = $(this).find("[name='uid']").val();
  const action = $(this).find("[name='action']").val();

  if (!inventoryTransfersReceived.validateAllSerialNumbers()) return;

  const productsToComplete = [];

  products.forEach(product => productsToComplete.push(product));

  const payload = {
    transferId,
    products: JSON.stringify(productsToComplete),
    action
  };

  callEndpoint({
    place: "inventario-traspasos-recibidos",
    parameters: payload
  }).then(response => {
    if (!response) {
      showSweetToast({
        icon: "error",
        message: "Error en la respuesta del servidor."
      });

      return;
    }

    if (response.toastMessage) showSweetToast({
      icon: response.status,
      message: response.toastMessage
    });

    if (response.status === "success") load(1, "inventario-traspasos-recibidos");
  });
});