class MultiDataTable {
  constructor({
    identifier,
    modalTitleNew = '',
    modalTitleEdit = '',
    customLoad = null,
    tablesConfig,
    onPressEdit = () => null,
    onPressAdd = () => null,
    location = null
  }) {
    this.state = {
      identifier,
      modalTitleNew,
      modalTitleEdit,
      customLoad,
      page: [],
      tablesConfig,
      location
    };

    this._customLoad = (identifier, page) => customLoad(identifier, page);
    this._setPage = (tableIdentifier, page) => this.state.page[tableIdentifier] = page;
    this._onPressEdit = (identifier, data) => onPressEdit(identifier, data);
    this._onPressAdd = (identifier, data) => onPressAdd(identifier, data);
  }

  _load = (identifier, page) => !!this.state.customLoad ? this._customLoad(identifier, page) : this._loadDataTable(identifier, page);

  //loadDataTable = () => { }

  _loadDataTable = (tableIdentifier, tablePage) => {
    showCardLoading(tableIdentifier);

    const location = this.state.location ? this.state.location : this.state.identifier;

    let page = !!tablePage ? tablePage : 1;

    this._setPage(tableIdentifier, page);

    console.log('PAGE SELECTED', page);

    const url = `${BASE_URL}/data/${location}/${location}_data.php`;
    const parameters = new FormData($(`#${tableIdentifier}-filters-form`)[0]);
    parameters.append('action', `load-${tableIdentifier}`);
    parameters.append('page', page);
    parameters.append('identifier', this.state.identifier);

    console.log(url);

    $.ajax({
      type: 'POST',
      enctype: 'multipart/form-data',
      url,
      data: parameters,
      processData: false,
      contentType: false,
      cache: false,
      success: response => {
        hideCardLoading(tableIdentifier);
        console.log('PAGE', this.state.page[tableIdentifier])
        console.log(response);
        $(`#${tableIdentifier}-table`).html(response);
      },
      error: function (e) {
        console.log("ERROR : ", e);
        hideCardLoading(tableIdentifier);
      }
    });
  }

  _searchTimeOut = false;

  _initDataTable = () => {
    if (!this.state.tablesConfig) this.startConfigTable(this.state.identifier);

    if (this.state.tablesConfig) {
      const tablesConfig = this.state.tablesConfig;

      for (const key in tablesConfig) {
        const config = tablesConfig[key];
        const identifier = config.identifier;

        console.log(identifier);

        this.startConfigTable(identifier);
      }
    }
  }

  startConfigTable = identifier => {
    const form = `#${identifier}-filters-form`;
    const load = page => this._load(identifier, page);
    const handlePressAdd = data => this._handlePressAdd(identifier, data);
    const handlePressEdit = data => this._handlePressEdit(identifier, data);
    const handlePressModalAction = (data, modal) => this._handlePressModalAction(identifier, data, modal);
    const handlePressAction = (data, action) => this._handlePressAction(identifier, data, action);
    const handlePressDelete = (data, title, message) => this._handlePressDelete(identifier, data, title, message);

    $(`${form} .per-page`).on('click', function () {
      const perPage = $(this).attr('data-perPage');

      $(`${form} input[name=perPage]`).val(perPage);

      $(`${form} .per-page`).removeClass('bg-primary text-white');
      $(this).addClass('bg-primary text-white');

      load(1);
    });

    $(form).on('submit', (e) => {
      e.preventDefault();
      console.log('filter changed');
      load(1);
    });

    $(`${form} select`).on('change', (e) => {
      e.preventDefault();
      console.log('filter changed');
      load(1);
    });

    $(`${form} input[type="checkbox"]`).on('click', (e) => {
      e.preventDefault();
      console.log('filter changed');
      load(1);
    });

    $(`${form} input[type="radio"]`).on('click', (e) => {
      e.preventDefault();
      console.log('filter changed');
      load(1);
    });

    $(`${form} input[type="text"]`).on('keyup', () => {
      if (this._searchTimeOut != false) {
        window.clearTimeout(this._searchTimeOut);
      }

      this._searchTimeOut = window.setTimeout(() => {
        load(1);
      }, 500);
    });

    $(document).on('click', `${form} .pagination .page-link`, function () {
      const page = $(this).attr('data-page');
      if (page == undefined) return;

      console.log('filter page', page);
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
      const action = $(this).attr('data-modal-action') != undefined ? $(this).attr('data-modal-action') : null;

      if (action) $(`${modal} form input[name="action"]`).val(action);

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

    this._load(identifier, 1);
  }

  /* _handlePressAdd = (identifier, data) => {
    $(`#${identifier}-form-data`).trigger('reset');

    $(`#${identifier}-form-data [type="submit"]`).html(`
      Guardar
    `);

    $(`#${identifier}-form-data [name="action"]`).val(`add-${identifier}`);

    $(`#${identifier}-form-data .form-control`).removeClass('error');
    $(`#${identifier}-form-data .custom-control-input`).removeClass('error');
    $(`#${identifier}-form-data .form-group label.error`).remove();

    if (this.state.modalTitleNew) $(`#${identifier}-modal .modal-title`).html(this.state.modalTitleNew);
    else $(`#${identifier}-modal .modal-title`).html(`Nuevo`);

    this._onPressAdd(identifier, data);

    $(`#${identifier}-form-data .cs-filepicker`).each(function () {
      const filePickerId = $(this).attr('id');
      $cs_file_pickers[filePickerId].clearPicker();
    });
  } */

  _handlePressAdd = (identifier, data) => {
    $(`#${identifier}-form-data`).trigger('reset');

    $(`#${identifier}-form-data [type="submit"]`).html(`
      Guardar
    `);

    $(`#${identifier}-form-data [name="action"]`).val(`add-${identifier}`);

    $(`#${identifier}-form-data .form-control`).removeClass('error');
    $(`#${identifier}-form-data .custom-control-input`).removeClass('error');
    $(`#${identifier}-form-data .form-group label.error`).remove();

    if (this.state.modalTitleNew) $(`#${identifier}-modal .modal-title`).html(this.state.modalTitleNew);
    else $(`#${identifier}-modal .modal-title`).html(`Nuevo`);

    $(`#${identifier}-form-data .cs-filepicker`).each(function () {
      const filePickerName = $(this).attr('data-name');
      $cs_file_pickers[filePickerName].clearPicker();
    });

    $(`#${identifier}-form-data [data-whatsapp="true"]`).each(function () {
      const whatsappEditorName = $(this).attr('data-name');
      $cs_whatsapp_editors[whatsappEditorName].resetEditor();
    });

    $(`#${identifier}-form-data .cs-ckeditor5`).each(function () {
      const name = $(this).attr('data-name');
      $cs_ckeditors[name].editor.setData('');
    });

    this._onPressAdd(identifier, data);
  }

  _handlePressEdit = (identifier, data) => {
    $(`#${identifier}-form-data`).trigger('reset');

    $(`#${identifier}-form-data [type="submit"]`).html(`
      Guardar cambios
    `);

    $(`#${identifier}-form-data .cs-filepicker`).each(function () {
      const filePickerId = $(this).attr('id');
      $cs_file_pickers[filePickerId].clearPicker();
    });

    $(`#${identifier}-form-data [data-whatsapp="true"]`).each(function () {
      const whatsappEditorName = $(this).attr('data-name');
      $cs_whatsapp_editors[whatsappEditorName].resetEditor();
    });

    $(`#${identifier}-form-data .cs-ckeditor5`).each(function () {
      const name = $(this).attr('data-name');
      $cs_ckeditors[name].editor.setData('');
    });

    for (const valueName in data) {
      if (isNaN(valueName)) {
        const inputElement = $(`#${identifier}-form-data [name="${valueName}"]:not([type="radio"],[type="checkbox"])`).attr('type');
        const checkboxElement = $(`#${identifier}-form-data [type="checkbox"][name="${valueName}"]`).attr('type');
        const arrayCheckboxElement = $(`#${identifier}-form-data [type="checkbox"][name="${valueName}[]"]`).attr('type');
        const radioElement = $(`#${identifier}-form-data [type="radio"][name="${valueName}"]`).attr('type');

        if (inputElement !== undefined) $(`#${identifier}-form-data [name="${valueName}"]`).val(data[valueName]);
        else if (checkboxElement) $(`#${identifier}-form-data [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        else if (arrayCheckboxElement) (!!data[valueName] && data[valueName].length > 0) && data[valueName].map(checkboxValue => $(`#${identifier}-form-data [name="${valueName}[]"][value="${checkboxValue}"]`).prop('checked', true))
        else if (radioElement) $(`#${identifier}-form-data [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        else $(`#${identifier}-form-data [name="${valueName}"]`).val(data[valueName]);

        $(`#${identifier}-form-data .cs-filepicker`).each(function () {
          const filePickerName = $(this).attr('data-name');
          const filePickerId = $(this).attr('id');

          if (filePickerName == valueName) $cs_file_pickers[filePickerId].createImagePreview({
            imageSrc: data[valueName]
          });
        });

        const whatsAppEditor = $(`#${identifier}-form-data [data-whatsapp="true"][data-name="${valueName}"]`);

        if (whatsAppEditor.length) $cs_whatsapp_editors[valueName].setEditorMessage(data[valueName]);

        const ckEditors5 = $(`#${identifier}-form-data .cs-ckeditor5`);

        if (ckEditors5.length) {
          const ckEditor5 = $cs_ckeditors[valueName];

          if (ckEditor5) ckEditor5.editor.setData(data[valueName]);
        }

        console.log(valueName, ' - ', data[valueName]);
      }
    }

    $(`#${identifier}-form-data [name="action"]`).val(`edit-${identifier}`);

    $(`#${identifier}-form-data .form-control`).removeClass('error');
    $(`#${identifier}-form-data .custom-control-input`).removeClass('error');
    $(`#${identifier}-form-data .form-group label.error`).remove();

    if (this.state.modalTitleEdit) $(`#${identifier}-modal .modal-title`).html(this.state.modalTitleEdit);
    else $(`#${identifier}-modal .modal-title`).html(`Editar`);

    this._onPressEdit(identifier, data);
  }

  _handlePressModalAction = (identifier, data, modal) => {
    $(`${modal} form`).trigger('reset');

    for (const valueName in data) {
      if (isNaN(valueName)) {
        const inputElement = $(`${modal} [name="${valueName}"]:not([type="radio"],[type="checkbox"])`).attr('type');
        const checkboxElement = $(`${modal} [type="checkbox"][name="${valueName}"]`).attr('type');
        const arrayCheckboxElement = $(`${modal} [type="checkbox"][name="${valueName}[]"]`).attr('type');
        const radioElement = $(`${modal} [type="radio"][name="${valueName}"]`).attr('type');

        if (inputElement !== undefined) $(`${modal} [name="${valueName}"]`).val(data[valueName]);
        else if (checkboxElement) $(`${modal} [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        else if (arrayCheckboxElement) (!!data[valueName] && data[valueName].length > 0) && data[valueName].map(checkboxValue => $(`${modal} [name="${valueName}[]"][value="${checkboxValue}"]`).prop('checked', true))
        else if (radioElement) $(`${modal} [name="${valueName}"][value="${data[valueName]}"]`).prop('checked', true);
        else $(`${modal} [name="${valueName}"]`).val(data[valueName]);

        console.log(valueName, ' - ', data[valueName]);
      }
    }

    $(`${modal} .form-control`).removeClass('error');
    $(`${modal} .custom-control-input`).removeClass('error');
    $(`${modal} .form-group label.error`).remove();

    //this._onPressEdit(data);
  }

  _handlePressDelete = async (identifier, data, title, message) => {
    const alertResponse = await showSweetConfirm({
      title,
      message
    });

    if (!alertResponse) return;

    callEndpoint({
      place: this.state.identifier,
      parameters: {
        action: `delete-${identifier}`,
        uid: data.uid
      }
    }).then(res => {
      console.log(res);

      if (res?.toastMessage && !res.callback) showSweetToast({
        icon: res.status,
        message: res.toastMessage
      });

      if (res.toastMessage && res.callback) {
        showSweetToast({
          icon: res.status,
          message: res.toastMessage
        });

        const callback = new Function(res.callback);
        callback();
      }

      if (res.alertMessage && !res.callback) showSweetAlert({
        title: res.title,
        message: res.alertMessage
      });

      if (res.alertMessage && res.callback) showSweetAlert({
        title: res.title,
        message: res.alertMessage
      }).then(() => {
        const callback = new Function(res.callback);
        callback();
      });

      if (!res.toastMessage && !res.alertMessage && res.callback) {
        const callback = new Function(res.callback);
        callback();
      }

      if (res.status === 'success' && !response.callback) this._load(this.state.page, this.state.identifier);

      /* if (response.toastMessage) showSweetToast({
        icon: response.status,
        message: response.toastMessage
      });

      if (response.alertMessage && !response.callback) showSweetAlert({
        title: response.title,
        message: response.message
      });

      if (response.status === 'success' && !response.callback) this._load(1);

      if (response.alertMessage && response.callback) showSweetAlert({
        title: response.title,
        message: response.message
      }).then(() => {

      }); */
    });
  }

  _handlePressAction = async (identifier, data, action) => {
    if (!action) {
      alert('¡Debe de definir el atributo acción en el botón');
      return;
    }

    if (action.alert == 'si') {
      const alertResponse = await showSweetConfirm({
        icon: 'info',
        title: action?.title ? action?.title : '¡Cuidado!',
        message: action?.message ? action?.message : '¿Realmente desea ejecutar esta acción?'
      });

      if (!alertResponse) return;
    }

    callEndpoint({
      place: this.state.identifier,
      parameters: {
        action: `action-${action.action}-${identifier}`,
        uid: data.uid
      }
    }).then(res => {
      console.log(res);

      if (res?.toastMessage && !res.callback) showSweetToast({
        icon: res.status,
        message: res.toastMessage
      });

      if (res.toastMessage && res.callback) {
        showSweetToast({
          icon: res.status,
          message: res.toastMessage
        });

        const callback = new Function(res.callback);
        callback();
      }

      if (res.alertMessage && !res.callback) showSweetAlert({
        title: res.title,
        message: res.alertMessage
      });

      if (res.alertMessage && res.callback) showSweetAlert({
        title: res.title,
        message: res.alertMessage
      }).then(() => {
        const callback = new Function(res.callback);
        callback();
      });

      if (!res.toastMessage && !res.alertMessage && res.callback) {
        const callback = new Function(res.callback);
        callback();
      }

      if (res.status === 'success' && !res.callback) this._load(this.state.page, this.state.identifier);
    });
  }
}