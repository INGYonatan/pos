async function getCitys({
  id = null,
  cityId = null
}) {
  const stateId = id ? id : $(this).val();

  if (!stateId) return;

  const dataChanged = $('#state').attr('data-changed');

  if (dataChanged == 'cp') {
    $('#state').attr('data-changed', 'searched');
    return;
  }

  showPageLoading();

  const parameters = new FormData();

  parameters.append('stateId', stateId);
  parameters.append('cityId', cityId);
  parameters.append('action', 'get_citys');

  const response = await fetchData({
    place: 'autocomplete_address',
    data: parameters
  });

  hidePageLoading();

  if (response.message) showSweetAlert({
    icon: response.status,
    title: response.message
  });

  if (response.content) {
    const content = decodeURIComponent(escape(atob(response.content)))
    $(`#city`).html(content);
  }
}

async function getNeighborhood({
  id = null,
  neighborhoodId = null
}) {
  const cityId = !!id ? id : $(this).val();

  if (!cityId) return;

  showPageLoading();

  const parameters = new FormData();

  parameters.append('cityId', cityId);
  parameters.append('neighborhoodId', neighborhoodId);
  parameters.append('action', 'get_neighborhoods');

  const response = await fetchData({
    place: 'autocomplete_address',
    data: parameters
  });

  hidePageLoading();

  if (response.message) showSweetAlert({
    icon: response.status,
    title: response.message
  });

  if (response.content) {
    const content = decodeURIComponent(escape(atob(response.content)))
    $(`#neighborhood`).html(content);
  }
}

async function getPostalCode({
  id
}) {
  const neighborhoodId = !!id ? id : $(this).val();
  //const neighborhoodId = $(this).val();

  if (!neighborhoodId) return;

  showPageLoading();

  const parameters = new FormData();

  parameters.append('neighborhoodId', neighborhoodId);
  parameters.append('action', 'get_postal_code');

  const response = await fetchData({
    place: 'autocomplete_address',
    data: parameters
  });

  hidePageLoading();

  if (response.message) showSweetAlert({
    icon: response.status,
    title: response.message
  });

  if (response.postalCode) {
    $(`#postalCode`).val(response.postalCode);
  }
}

async function getAddress() {
  const postalCode = $(this).val();

  if (!postalCode) return;

  const parameters = new FormData();

  parameters.append('postalCode', postalCode);
  parameters.append('action', 'get_address');

  const response = await fetchData({
    place: 'autocomplete_address',
    data: parameters
  });

  //if (response.state) await $("#state").select2("val", response.state);
  $('#state').attr('data-changed', 'cp');
  $('#state').val(response.state).trigger('change');

  if (response.neighborhoods) {
    const content = decodeURIComponent(escape(atob(response.neighborhoods)))
    $(`#neighborhood`).html(content);
  }

  if (response.citys) {
    const content = decodeURIComponent(escape(atob(response.citys)))
    $(`#city`).html(content);
  }
}

$('#state').on('change', getCitys);
$('#city').on('change', getNeighborhood);
$('#neighborhood').on('change', getPostalCode);
$('#postalCode').on('keyup', getAddress);