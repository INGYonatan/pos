class DataTable {
  constructor({
    identifier,
    modalTitleNew = '',
    modalTitleEdit = '',
    onPressEdit = () => null,
    onPressAdd = () => null,
    customLoad = null
  }) {
    this.state = {
      identifier,
      modalTitleNew,
      modalTitleEdit,
      page: 1,
      customLoad
    };

    this._setPage = page => this.state.page = page;
    this._onPressEdit = data => onPressEdit(data);
    this._onPressAdd = data => onPressAdd(data);
    this._customLoad = page => customLoad(page);
  };

  _load = (page = 1) => !!this.state.customLoad ? this._customLoad(page) : this._originalLoad(page);

  _originalLoad = (page = 1) => {
    showCardLoading(this.state.identifier);

    this._setPage(page);

    const url = `data/${this.state.identifier}/${this.state.identifier}_data.php`;
    const parameters = new FormData($(`#${this.state.identifier}-filters-form`)[0]);
    parameters.append('action', `load-${this.state.identifier}`);
    parameters.append('page', page);

    $.ajax({
      type: 'POST',
      enctype: 'multipart/form-data',
      url,
      data: parameters,
      processData: false,
      contentType: false,
      cache: false,
      success: response => {
        hideCardLoading(this.state.identifier);
        console.log(response);
        $(`#${this.state.identifier}-table`).html(response);
      },
      error: function (e) {
        console.log("ERROR : ", e);
        hideCardLoading(this.state.identifier);
      }
    });
  }

  _searchTimeOut = false;

  _initDataTable = () => {
    const form = `#${this.state.identifier}-filters-form`;
    const load = page => this._load(page);
    const handlePressAdd = data => this._handlePressAdd(data);
    const handlePressEdit = data => this._handlePressEdit(data);
    const handlePressModalAction = (data, modal) => this._handlePressModalAction(data, modal);
    const handlePressAction = (data, action) => this._handlePressAction(data, action);
    const handlePressDelete = (data, title, message) => this._handlePressDelete(data, title, message);

    $(`${form} .per-page`).on('click', function () {
      const perPage = $(this).attr('data-perPage');

      $(`${form} input[name=perPage]`).val(perPage);

      $(`${form} .per-page`).removeClass('bg-primary text-white');
      $(this).addClass('bg-primary text-white');

      load(1);
    });

    $(form).on('submit', (e) => {
      e.preventDefault();
      load(1);
    });

    $(`${form} select`).on('change', (e) => {
      e.preventDefault();
      load(1);
    });

    $(form).on('keyup', () => {
      if (this._searchTimeOut != false) {
        window.clearTimeout(this._searchTimeOut);
      }

      this._searchTimeOut = window.setTimeout(() => {
        load(1);
      }, 500);
    });

    $(document).on('click', `${form} .pagination .page`, function () {
      const page = $(this).attr('data-page');
      load(page);
    });

    $(`${form} .btn-add`).on('click', function () {
      handlePressAdd($(this));
    });

    $(document).on('click', `${form} .btn-edit`, function () {
      const data = JSON.parse($(this).attr('data-row'));
      handlePressEdit(data);
    });

    $(document).on('click', `${form} .btn-modal`, function () {
      const data = JSON.parse($(this).attr('data-row'));
      const modal = $(this).attr('data-bs-target');

      handlePressModalAction(data, modal);
    });

    $(document).on('click', `${form} .btn-delete`, function () {
      const data = JSON.parse($(this).attr('data-row'));
      const title = $(this).attr('data-title') != undefined ? $(this).attr('data-title') : '¡Cuidado!';
      const message = $(this).attr('data-message') != undefined ? $(this).attr('data-message') : '¿Realmente desea ejecutar esta acción?';

      handlePressDelete(data, title, message);
    });

    $(document).on('click', `${form} .btn-action`, function () {
      const data = JSON.parse($(this).attr('data-row'));
      const action = $(this).attr('data-action') != undefined ? JSON.parse($(this).attr('data-action')) : null;

      handlePressAction(data, action);
    });

    $(document).on('click', `${form} .btn-reset-form`, () => {
      $(form).trigger('reset');
    });

    this._load(1)
  }

  _handlePressAdd = data => {
    $(`#${this.state.identifier}-form-data`).trigger('reset');

    $(`#${this.state.identifier}-form-data [type="submit"]`).html(`
      Guardar
    `);

    $(`#${this.state.identifier}-form-data [name="action"]`).val(`add-${this.state.identifier}`);

    $(`#${this.state.identifier}-form-data .form-control`).removeClass('error');
    $(`#${this.state.identifier}-form-data .custom-control-input`).removeClass('error');
    $(`#${this.state.identifier}-form-data .form-group label.error`).remove();

    if (this.state.modalTitleEdit) $(`#${this.state.identifier}-modal .modal-title`).html(this.state.modalTitleNew);
    else $(`#${this.state.identifier}-modal .modal-title`).html(`Nuevo`);

    this._onPressAdd(data);
  }

  _handlePressEdit = data => {
    $(`#${this.state.identifier}-form-data`).trigger('reset');

    $(`#${this.state.identifier}-form-data [type="submit"]`).html(`
      Guardar cambios
    `);

    for (const valueName in data) {
      if (isNaN(valueName)) {
        const inputElement = $(`#${this.state.identifier}-form-data [name="${valueName}"]:not([type="radio"],[type="checkbox"])`).attr('type');
        const checkboxElement = $(`#${this.state.identifier}-form-data [type="checkbox"][name="${valueName}"]`).attr('type');
        const arrayCheckboxElement = $(`#${this.state.identifier}-form-data [type="checkbox"][name="${valueName}[]"]`).attr('type');
        const radioElement = $(`#${this.state.identifier}-form-data [type="radio"][name="${valueName}"]`).attr('type');

        console.log('INPUT TYPE', inputElement);

        if (inputElement !== undefined && inputElement != 'file') $(`#${this.state.identifier}-form-data [name="${valueName}"]`).val(data[valueName]);
        else if (checkboxElement) $(`#${this.state.identifier}-form-data [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        else if (arrayCheckboxElement) (!!data[valueName] && data[valueName].length > 0) && data[valueName].map(checkboxValue => $(`#${this.state.identifier}-form-data [name="${valueName}[]"][value="${checkboxValue}"]`).prop('checked', true))
        else if (radioElement) $(`#${this.state.identifier}-form-data [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        else if (inputElement === 'file') console.log('INPUT FILE TYPE');
        else $(`#${this.state.identifier}-form-data [name="${valueName}"]`).val(data[valueName]);

        console.log(valueName, ' - ', data[valueName]);
      }
    }

    $(`#${this.state.identifier}-form-data [name="action"]`).val(`edit-${this.state.identifier}`);

    $(`#${this.state.identifier}-form-data .form-control`).removeClass('error');
    $(`#${this.state.identifier}-form-data .custom-control-input`).removeClass('error');
    $(`#${this.state.identifier}-form-data .form-group label.error`).remove();

    if (this.state.modalTitleEdit) $(`#${this.state.identifier}-modal .modal-title`).html(this.state.modalTitleEdit);
    else $(`#${this.state.identifier}-modal .modal-title`).html(`Editar`);

    this._onPressEdit(data);
  }

  _handlePressModalAction = (data, modal) => {
    $(`${modal} form`).trigger('reset');

    for (const valueName in data) {
      if (isNaN(valueName)) {
        const inputElement = $(`${modal} [name="${valueName}"]:not([type="radio"],[type="checkbox"])`).attr('type');
        const checkboxElement = $(`${modal} [type="checkbox"][name="${valueName}"]`).attr('type');
        const arrayCheckboxElement = $(`${modal} [type="checkbox"][name="${valueName}[]"]`).attr('type');
        const radioElement = $(`${modal} [type="radio"][name="${valueName}"]`).attr('type');

        if (inputElement !== undefined && inputElement != 'file') $(`${modal} [name="${valueName}"]`).val(data[valueName]);
        else if (checkboxElement) $(`${modal} [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        else if (arrayCheckboxElement) (!!data[valueName] && data[valueName].length > 0) && data[valueName].map(checkboxValue => $(`${modal} [name="${valueName}[]"][value="${checkboxValue}"]`).prop('checked', true))
        else if (radioElement) $(`${modal} [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        else if (inputElement === 'file') console.log('INPUT FILE TYPE');
        else $(`${modal} [name="${valueName}"]`).val(data[valueName]);

        console.log(valueName, ' - ', data[valueName]);
      }
    }

    $(`${modal} .form-control`).removeClass('error');
    $(`${modal} .custom-control-input`).removeClass('error');
    $(`${modal} .form-group label.error`).remove();

    //this._onPressEdit(data);
  }

  /* _usePressButtonToForm = ({
    data,
    modalTitle,

  }) => {
    $(`#${this.state.identifier}-form-data`).trigger('reset');

    $(`#${this.state.identifier}-form-data [type="submit"]`).html(`
      Guardar cambios
    `);

    for (const valueName in data) {
      if (isNaN(valueName)) {
        const inputElement = $(`#${this.state.identifier}-form-data [name="${valueName}"]:not([type="radio"],[type="checkbox"]`).attr('type');
        const checkboxElement = $(`#${this.state.identifier}-form-data [type="checkbox"][name="${valueName}"]`).attr('type');
        const arrayCheckboxElement = $(`#${this.state.identifier}-form-data [type="checkbox"][name="${valueName}[]"]`).attr('type');
        const radioElement = $(`#${this.state.identifier}-form-data [type="radio"][name="${valueName}"]`).attr('type');

        if (inputElement !== undefined) $(`#${this.state.identifier}-form-data [name="${valueName}"]`).val(data[valueName]);
        if (checkboxElement) $(`#${this.state.identifier}-form-data [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        if (arrayCheckboxElement) (!!data[valueName] && data[valueName].length > 0) && data[valueName].map(checkboxValue => $(`#${this.state.identifier}-form-data [name="${valueName}[]"][value="${checkboxValue}"]`).prop('checked', true))
        if (radioElement) $(`#${this.state.identifier}-form-data [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);

        console.log(valueName, ' - ', data[valueName]);
      }
    }

    $(`#${this.state.identifier}-form-data [name="action"]`).val(`edit-${this.state.identifier}`);

    $(`#${this.state.identifier}-form-data .form-control`).removeClass('error');
    $(`#${this.state.identifier}-form-data .form-group label.error`).remove();

    if (this.state.modalTitleEdit) $(`#${this.state.identifier}-modal .modal-title`).html(this.state.modalTitleEdit);
    else $(`#${this.state.identifier}-modal .modal-title`).html(`Editar`);

    this._onPressEdit(data);
  } */

  _handlePressDelete = async (data, title, message) => {
    const alertResponse = await showSweetConfirm({
      title,
      message
    });

    if (!alertResponse) return;

    callEndpoint({
      place: this.state.identifier,
      parameters: {
        action: `delete-${this.state.identifier}`,
        uid: data.uid
      }
    }).then(response => {
      if (response.toastMessage) showSweetToast({
        icon: response.status,
        message: response.toastMessage
      });

      if (response.status === 'success') this._load(1);
    });
  }

  _handlePressAction = async (data, action) => {
    if (!action) {
      alert('¡Debe de definir el atributo acción en el botón');
      return;
    }

    const alertResponse = await showSweetConfirm({
      icon: 'info',
      title: action?.title ? action?.title : '¡Cuidado!',
      message: action?.message ? action?.message : '¿Realmente desea ejecutar esta acción?'
    });

    if (!alertResponse) return;

    callEndpoint({
      place: this.state.identifier,
      parameters: {
        action: `action-${action.action}-${this.state.identifier}`,
        uid: data.uid
      }
    }).then(response => {
      if (response.toastMessage) showSweetToast({
        icon: response.status,
        message: response.toastMessage
      });

      if (response.status === 'success') this._load(this.state.page);
    });
  }
}