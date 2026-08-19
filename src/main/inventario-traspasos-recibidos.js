const useInventoryTransfersReceived = () => {
  let products = [];
  let useAdjustInventory = false;

  const tableContainer = $("#completar-traspaso-productos-table");
  const ajusteInventarioContainer = $("#ajuste-inventario-container");
  const serialNumbersContainer = $("#completar-traspaso-numeros-serie-para-ajuste-container");

  const initializeEventListeners = () => {
    onKeyUpQuantity();
    onClickManageSerialNumbers();
  }

  const renderProductsTable = (productsToComplete) => {
    products = [];

    const tableRows = productsToComplete.map(product => {
      const parsedProduct = {
        inventoryTransferProductId: product.inventoryTransferProductId,
        productId: product.productId,
        code: product.code,
        name: product.name,
        quantity: parseFloat(product.quantity),
        requiresSerialNumbers: product.requiresSerialNumbers || false,
        serialNumbers: product.serialNumbers || [],
        receivedQuantity: parseFloat(product.quantity),
        receivedSerialNumbers: product.serialNumbers || []
      };

      products[product.productId] = parsedProduct;

      const addSerialNumbersButton = parsedProduct.requiresSerialNumbers ? `
        <a id="btn-completar-traspaso-manage-serial-numbers-${parsedProduct.productId}"
          class="btn btn-sm btn-secondary btn-manage-serial-numbers btn-xs mt-2"
          data-bs-toggle="modal"
          data-bs-target="#inventario-traspasos-recibidos-modal-completar-traspaso-numeros-serie-para-ajuste"
          data-uid="${parsedProduct.productId}">
          Gestionar números de serie
        </a>
      ` : ``;

      return `
        <tr class="align-middle">
          <td>
            ${parsedProduct.code}
          </td>

          <td>
            ${parsedProduct.name}
          </td>

          <td class="text-center">
            ${parsedProduct.quantity}
          </td>

          <td class="text-center">
            <input style="max-width: 6rem;"
              class="form-control completar-traspaso-cantidad mx-auto text-center"
              min="0"
              max="${parsedProduct.quantity}"
              value="${parsedProduct.quantity}"
              data-uid="${parsedProduct.productId}"
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
  }

  const onKeyUpQuantity = () => $(".completar-traspaso-cantidad").on("keyup change", function () {
    const uid = $(this).data("uid");
    const max = $(this).attr("max");

    let newQuantity = parseFloat($(this).val());

    if (isNaN(newQuantity) || newQuantity < 0) return;

    if (newQuantity > max) newQuantity = max;

    const product = products[uid];
    product.receivedQuantity = newQuantity;

    $(this).val(newQuantity);

    products[uid] = product;

    if (newQuantity == 0) {
      $(`#btn-completar-traspaso-manage-serial-numbers-${uid}`).fadeOut();
      product.receivedSerialNumbers = [];
    }

    if (newQuantity == product.quantity) {
      $(`#btn-completar-traspaso-manage-serial-numbers-${uid}`).fadeIn();
      product.receivedSerialNumbers = product.serialNumbers;
    }

    if (newQuantity < product.quantity && newQuantity > 0) {
      $(`#btn-completar-traspaso-manage-serial-numbers-${uid}`).fadeIn();
      product.receivedSerialNumbers = product.serialNumbers;
    }

    products[uid] = product;
  });

  const onClickManageSerialNumbers = () => $(".btn-manage-serial-numbers").on("click", function () {
    const uid = $(this).data("uid");
    const product = products[uid];

    const serialNumbersSelectHTML = getSerialNumbersSelect(product.serialNumbers, product.receivedSerialNumbers);

    serialNumbersContainer.html(serialNumbersSelectHTML);

    // Inicializar el select2
    setTimeout(() => {
      $(".completar-traspaso-ajuste-numeros-serie-select").select2({
        width: '100%',
        height: '200px',
        placeholder: '--Seleccionar--',
        allowClear: true,
        dropdownParent: $("#inventario-traspasos-recibidos-modal-completar-traspaso-numeros-serie-para-ajuste"),
        maximumSelectionLength: product.receivedQuantity,
        language: {
          // personaliza el mensaje (opcional)
          maximumSelected: function (args) {
            return 'Solo puedes seleccionar ' + args.maximum + ' número(s) de serie para recibir.';
          }
        }
      });
    }, 100);

    $("#completar-traspaso-producto-uid").val(uid);
  });

  const getSerialNumbersSelect = (serialNumbers, selectedSerialNumbers) => {
    const options = serialNumbers.map(sn => `<option value="${sn}" ${selectedSerialNumbers.includes(sn) ? 'selected' : ''}>${sn}</option>`).join("");

    return `
      <div class="col-12">
        <div class="form-group">
          <select id="completar-traspaso-ajuste-numeros-serie-select" class="form-control form-select completar-traspaso-ajuste-numeros-serie-select select2" multiple>
            ${options}
          </select>
        </div>
      </div>
    `;
  };

  const getData = () => ({
    products
  });

  // Initialize forms
  $("#completar-traspaso-numeros-serie-para-ajuste-form").on("submit", function (e) {
    e.preventDefault();

    const receivedSerialNumbers = $(".completar-traspaso-ajuste-numeros-serie-select").val() || [];
    const totalSelected = receivedSerialNumbers.length;

    const uid = $("#completar-traspaso-producto-uid").val();
    const product = products[uid];
    const receivedQuantity = product.receivedQuantity;

    if (totalSelected < receivedQuantity) {
      showSweetToast({
        icon: "warning",
        message: `Debes seleccionar ${receivedQuantity} número(s) de serie para recibir.`
      });

      return;
    }

    product.receivedSerialNumbers = receivedSerialNumbers;
    products[uid] = product;

    $(`#inventario-traspasos-recibidos-modal-completar-traspaso-numeros-serie-para-ajuste`).modal('hide');
  });

  const validateAllSerialNumbers = () => {
    for (const uid in products) {
      const product = products[uid];

      const receivedQuantity = product.receivedQuantity;
      const receivedSerialNumbersLength = product.receivedSerialNumbers.length;

      if (!product.requiresSerialNumbers) continue;

      if (receivedQuantity < receivedSerialNumbersLength) {
        showSweetAlert({
          icon: "warning",
          title: "Cantidad de números de serie excedida",
          message: `En el producto ${product.name}, solo puedes seleccionar ${receivedQuantity} número(s) de serie para recibir.`
        });

        return false;
      }

      if (receivedQuantity > receivedSerialNumbersLength) {
        showSweetAlert({
          icon: "warning",
          title: "Cantidad insuficiente de números de serie",
          message: `En el producto ${product.name}, debes seleccionar al menos ${receivedQuantity} números de serie para recibir.`
        });

        return false;
      }

      if (receivedQuantity != receivedSerialNumbersLength) {
        showSweetAlert({
          icon: "warning",
          title: "Cantidad incorrecta de números de serie",
          message: `En el producto ${product.name}, debes seleccionar ${receivedQuantity} números de serie para recibir.`
        });

        return false;
      }
    }

    return true;
  }

  return {
    renderProductsTable,
    validateAllSerialNumbers,
    getData
  }
}

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

    if (response.status == "success") {
      $("#inventario-traspasos-recibidos-modal-completar-traspaso").modal('hide');
      load(1, "inventario-traspasos-recibidos");
    }
  });
});