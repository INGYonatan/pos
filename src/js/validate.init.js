const $init_form_validates = () => $('.form-validate').each(function () {
  $(this).validate({
    submitHandler: function (form) {
      const formId = $(form).attr('id');

      const place = $(`#${formId} input[name="place"]`).val();
      const action = $(`#${formId} input[name="action"]`).val();

      console.log(`${action} -- ${place}`);

      const parameters = new FormData(form);

      // Verificar si hay filePickers
      let filePickerRequiredError = false;

      $(`#${formId} .cs-filepicker`).each(function () {
        const filePickerId = $(this).attr('id');
        const filePickerName = $(this).attr('data-name');

        const file = $cs_file_pickers[filePickerName].getFile();
        const fileData = $cs_file_pickers[filePickerName].getPickerData();
        const isRequired = fileData.required;

        if (isRequired && !file) {
          filePickerRequiredError = true;

          showSweetToast({
            icon: 'warning',
            message: fileData.requiredMessage
          });

          return false;
        }

        if (isRequired && file && file?.blob) parameters.append(fileData.name, file.blob, file.name);
      });

      if (filePickerRequiredError) return;

      let whatsappMessageRequiredError = false;

      $(`#${formId} [data-whatsapp="true"]`).each(function () {
        const whatsappEditorName = $(this).attr('id');

        const whatsappEditorData = $cs_whatsapp_editors[whatsappEditorName].getWhatsappEditordata();
        const whatsappMessage = $cs_whatsapp_editors[whatsappEditorName].getWhatsappMessage();
        const isRequired = whatsappEditorData.required;

        if (isRequired && !whatsappMessage) {
          whatsappMessageRequiredError = true;

          showSweetToast({
            icon: 'warning',
            message: whatsappEditorData.requiredMessage
          });

          return false;
        }

        if (isRequired && whatsappMessage) parameters.append(whatsappEditorData.name, whatsappMessage);
      });

      if (whatsappMessageRequiredError) return;

      let csCKEditorRequiredError = false;

      $(`#${formId} .cs-ckeditor5`).each(function () {
        const id = $(this).attr('id');
        const name = $(this).attr('data-name');

        const editor = $cs_ckeditors[name].editor;
        const required = $cs_ckeditors[name].required;
        const requiredMessage = $cs_ckeditors[name].requiredMessage;

        const data = editor.getData();

        console.log('data:', data);

        if (required && !data) {
          csCKEditorRequiredError = true;

          showSweetToast({
            icon: 'warning',
            message: requiredMessage
          });

          return false;
        }

        if (required && data) parameters.append(name, data);
      });

      if (csCKEditorRequiredError) return;

      const validateCallback = $(`#${formId}`).attr('data-validateCallback');

      if (validateCallback != undefined) {
        $validate_callbacks[validateCallback]();
        return;
      }

      useSendFormData({
        place,
        parameters
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

        if (res.status === 'success') {
          if (!res.modalSelector) $('.modal').modal('hide');
          if (res.modalSelector) $(`${res.modalSelector}`).modal('hide');
        }
      });
    },

    rules: {
      confirm_password: {
        equalTo: '[name="password"]'
      }
    },

    errorPlacement: function (error, element) {
      const container = $(element).closest('.form-group');
      if (!$(element).closest('.form-group').children('.label-validate').length) container.append(`<span class="label-validate text-danger">${error.text()}</span>`);
    },

    highlight: function (element, errorClass) {
      $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
    },

    unhighlight: function (element) {
      $(element).closest('.form-group').removeClass('has-error').addClass('has-success').children('.label-validate').remove();
    },

    ignore: ":hidden"
  });
});

$init_form_validates();
