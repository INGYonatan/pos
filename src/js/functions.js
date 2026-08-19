const showPageProgress = () => {
  $('[type="submit"]').prop('disabled', true);
  setTimeout(() => $('#page-progress').show(), 200);
}

const hidePageProgress = () => {
  clearTimeout(showPageProgress());
  $('[type="submit"]').prop('disabled', false);
  setTimeout(() => $('#page-progress').hide(), 200);
}

const updatePageProgress = progress => {
  $('#page-progress .progress-bar').html(`${progress}%`);
  $('#page-progress .progress-bar').width(`${progress}%`);
  $('#page-progress .progress-bar').attr('aria-valuenow', progress);
}

const showPageLoading = () => {
  $('[type="submit"]').prop('disabled', true);
  setTimeout(() => $('#page-loading').show(), 200);
}

const hidePageLoading = () => {
  clearTimeout(showPageLoading());
  $('[type="submit"]').prop('disabled', false);
  setTimeout(() => $('#page-loading').hide(), 200);
}

const showCardLoading = id => setTimeout(() => $(`#${id}-loading`).show(), 200);

const hideCardLoading = id => {
  clearTimeout(showCardLoading());
  setTimeout(() => $(`#${id}-loading`).hide(), 200);
}

const useSendFormData = ({
  place,
  parameters
}) => new Promise((resolve, reject) => {
  // Request
  const request = new XMLHttpRequest();

  try {
    const url = `${BASE_URL}/data/${place}/${place}_data.php`;

    // Progress
    request.upload.addEventListener('progress', event => {
      const percent = Math.round((event.loaded / event.total) * 100);

      showPageProgress();
      updatePageProgress(percent);
    });

    request.addEventListener('load', () => {
      hidePageProgress();
      updatePageProgress(0);
    });

    request.addEventListener('loadend', event => {
      hidePageProgress();

      try {
        const response = JSON.parse(event.target.response);
        resolve(response);
      } catch (error) {
        showSweetAlert({
          title: '¡Error inesperado!',
          message: error
        });

        resolve([]);
      }
    });

    // Enviar datos
    request.open('post', url);
    request.send(parameters);
  } catch (error) {
    showSweetAlert({
      title: '¡Error inesperado!',
      message: error
    });

    request.abort();

    hidePageProgress();
    updatePageProgress(0);

    resolve([]);
  }
});

const callEndpoint = ({
  place,
  customURL,
  parameters,
  showLoading = true
}) => new Promise((resolve, reject) => {
  if (showLoading) showPageLoading();

  const defaultURL = `data/${place}/${place}_data.php`;
  const url = BASE_URL + '/' + (customURL ? customURL : defaultURL);

  const data = new FormData();

  for (const valueName in parameters) {
    const value = parameters[valueName];
    data.append(`${valueName}`, value);
  }

  fetch(url, {
    method: 'post',
    body: data
  }).then(res => res.json()).then(response => {
    hidePageLoading();
    resolve(response);
  }).catch(error => {
    hidePageLoading();
    alert(error);
    resolve({});
  });
});

$('[data-load-select]').on('change', function () {
  console.log($(this).attr('data-place'));
  const place = $(this).attr('data-place') != undefined ? $(this).attr('data-place') : 'selects';

  const name = $(this).attr('name');
  const value = $(this).val();
  const container = $(this).attr('data-container');

  let parameters = {};

  parameters[name] = value;

  console.log($(this).attr('data-parameters'));

  const moreParameters = $(this).attr('data-parameters') != undefined ? JSON.parse($(this).attr('data-parameters')) : {};

  console.log('moreParameters', moreParameters);

  for (const valueName in moreParameters) {
    const value = moreParameters[valueName];
    parameters[valueName] = value;
  }

  const parametersItems = $(this).attr('data-parametersItems') != undefined ? JSON.parse($(this).attr('data-parametersItems')) : [];

  parametersItems.map(item => {
    const name = $(item).attr('name');
    const value = $(item).val();

    parameters[name] = value;
  });

  console.log(parameters);

  callEndpoint({
    place,
    parameters
  }).then(response => {
    if (response.status === 'success') $(container).html(response.data);
  });
});

$('input[name="username"]').on('keyup', function () {
  const id = $(this).attr('id');
  const specialCharacters = /\W+/;
  const value = $(this).val();
  const form = $(this).closest('form');

  if (id === 'login-modal-username' || id === 'supplier-username') return;

  if (value.match(specialCharacters)) {
    $(this).closest('form').find('[type="submit"]').attr('disabled', true);
    if ($(this).parent().find('p.alert-message').length == 0) $(this).parent().append(
      `<p class="alert-message" style="
        font-size: 0.8rem;
        color: red;
        margin: 0;
      ">Lo sentimos, solo se permiten letras (a-z) y números (0-9).</p>`
    );
  };

  if (!value.match(specialCharacters)) {
    $(this).closest('form').find('[type="submit"]').removeAttr('disabled');
    $(this).parent().children('p.alert-message').remove();
  }
});

$(function () {
  initNumberInput();
  initDecimalInput();
});

const initNumberInput = () => $('.number-input').on('keyup', function () {
  this.value = (this.value + '').replace(/[^0-9*\.0-9]/g, '');
});

const initDecimalInput = () => $('.decimal-input').on('keyup', function () {
  const inputValue = $(this).val();
  const numbersOnly = inputValue.replace(/[^0-9.]/g, '');
  $(this).val(numbersOnly);
});

const calculatePriceWithPercentajeDiscount = ({
  price,
  newPrice,
  discount
}) => {
  if (!newPrice || !discount) return false;

  let initialState = {
    price: parseFloat(price),
    newPrice: parseFloat(newPrice),
    discount: parseFloat(discount)
  };

  if (initialState.discount > 100) initialState.discount = 100;

  const priceWithDiscount = (initialState.price - (initialState.discount * initialState.price) / 100);

  initialState.newPrice = priceWithDiscount.toFixed(DECIMALS_CURRENCY);

  return initialState;
}

const calculatePriceWithNewPrice = ({
  price,
  newPrice,
  discount
}) => {
  if (!newPrice || !discount) return false;

  let initialState = {
    price: parseFloat(price),
    newPrice: parseFloat(newPrice),
    discount: parseFloat(discount)
  };

  if (initialState.newPrice > initialState.price) initialState.newPrice = price;

  const newDiscount = (100 - (initialState.newPrice * 100) / initialState.price);

  initialState.discount = newDiscount.toFixed(DECIMALS_CURRENCY);

  return initialState;
}

let doSearchDelayTimer;

const doSearch = callback => {
  clearTimeout(doSearchDelayTimer);

  doSearchDelayTimer = setTimeout(() => {
    !!callback && callback();
  }, 500);
}

function capitalizeWords(str) {
  return str.toLowerCase().replace(/(^|\s)\S/g, (match) => match.toUpperCase());
}

function strToNumber(numberString) {
  return parseFloat(numberString.replace(/,/g, ''));
}

async function fetchData({
  place,
  customURL,
  data,
  showError = true
}) {
  try {
    const defaultURL = `${BASE_URL}/data/${place}/${place}_data.php`;
    const url = customURL ? `${BASE_URL}/${customURL}` : defaultURL;

    console.log(url);

    // const timeout = 60000;
    // const controller = new AbortController();
    // const timeoutId = setTimeout(() => controller.abort(), timeout);

    const resData = await fetch(url, {
      method: 'POST',
      body: data
      //signal: controller.signal
    });

    //clearTimeout(timeoutId);

    const response = await resData.json();

    if (!response) {
      showSweetAlert({ icon: 'error', title: '¡Error!', message: 'Error del servidor, intentelo nuevamente.' });
    }

    if (response) return response;

  } catch (error) {
    if (showError) {
      if (error.name === 'AbortError') {
        showSweetAlert({ icon: 'error', title: '¡Error!', message: 'Verifique su conexión a internet e intentelo nuevamente.' });
      }

      if (error.name !== 'AbortError') {
        showSweetAlert({ icon: 'error', title: '¡Error!', message: error });
      }
    }

    return false;
  }
}

const getCatalog = ({
  catalogSelector,
  parameters = {},
  place = 'catalogs',
  resetCatalog,
  onSuccess = () => { }
}) => callEndpoint({
  place,
  parameters
}).then(response => {
  if (resetCatalog) $(catalogSelector).val('');
  if (response.status === 'success') {
    $(catalogSelector).html(response.catalog);
    onSuccess(response);
  }
});

let $validate_callbacks = [];

$('[data-numberSwitcher]').on('keyup', function () {
  let value = $(this).val();
  const content = $(this).attr('data-content');

  if (!value) value = 0;
  if (value <= 0) $(content).removeClass('active');
  if (value > 0) $(content).addClass('active');
});

$('[catalog-onChange]').each(function () {
  const catalogSelector = $(this).attr('catalog-onChange');
  const parameters = $(this).attr('data-parameters') ? JSON.parse($(this).attr('data-parameters')) : {};
  const resetCatalog = $(this).attr('data-resetCatalog') == 'true' ? true : false;

  $(this).on('change', function () {
    const value = $(this).val();

    if (!value) {
      $(catalogSelector).html('<option value="">--Seleccionar--</option>');
      return;
    }

    parameters.value = value;

    getCatalog({
      catalogSelector,
      parameters,
      resetCatalog
    });
  });
});

/* $('[data-target-catalog-on-change]').each(function () {
  const catalogSelector = $(this).attr('data-selector') ? $(this).attr('data-selector') : `#${$(this).attr('id')}`;
  const target = $(this).attr('data-target-catalog-on-change');
  const parameters = $(this).attr('data-parameters') ? JSON.parse($(this).attr('data-parameters')) : {};
  const resetCatalog = $(this).attr('data-resetCatalog') == 'true' ? true : false;

  $(target).on('change', function () {
    const value = $(this).val();

    parameters.value = value;

    getCatalog({
      catalogSelector,
      parameters,
      resetCatalog
    });
  });
}); */

// FUnción para poder formatear un número a moneda en pesos mexicanos con 2 decimales
function formatNumberToCurrency(number) {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 2,
    maximumFractionDigits: DECIMALS_CURRENCY
  }).format(number);
}

// La misma función de arriba, solo que si no tiene decimales no los muestra pero quitando el signo de pesos
function formatNumberToCurrencyWithoutSymbol(number) {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    minimumFractionDigits: 0,
    maximumFractionDigits: DECIMALS_CURRENCY
  }).format(number).split('$')[1];
}

const updateUrlParams = (paramName, value) => {
  const url = new URL(window.location.href);

  if (!value) {
    const params = new URLSearchParams(url.search);
    params.delete(paramName);
    url.search = params.toString();
    window.history.replaceState({}, "", url);
  }

  if (value) {
    url.searchParams.set(paramName, value);
    window.history.replaceState({}, "", url);
  }
};
