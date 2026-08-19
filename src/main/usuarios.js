// DATA TABLE
const datatable = new DataTable({
  identifier: PAGE_CONFIG.page_identifier,
  modalTitleNew: PAGE_CONFIG.modal_title_add,
  modalTitleEdit: PAGE_CONFIG.modal_title_edit,
  onPressAdd: () => {
    $('#username').prop('readonly', false);
    //$('#correo').prop('readonly', false);
    $('#change-password-container').hide();
    $('#password-container').show();
    checkBranchOffice();
  },
  onPressEdit: () => {
    $('#username').prop('readonly', true);
    //$('#correo').prop('readonly', true);
    $('#change-password-container').show();
    $('#password-container').hide();
    $('#change_password').prop('checked', false);
    checkBranchOffice();
  }
});

datatable._initDataTable();

const load = (page = 1) => datatable._load(page);

// JQUERY ACTIONS
$('#change_password').on('click', function () {
  const isChecked = $(this).is(':checked');

  if (isChecked) $('#password-container').slideDown();
  if (!isChecked) $('#password-container').slideUp();
});

const checkBranchOffice = () => {
  const rolId = $('#id_rol').val();
  const rolSelected = $(`#id_rol option[value="${rolId}"]`).attr('data-slug');
  const branchOfficeSelect = $('#id_sucursal');

  const permittedRols = ["administrador"];

  if (!permittedRols.includes(rolSelected)) branchOfficeSelect.removeAttr('disabled');
  if (permittedRols.includes(rolSelected)) branchOfficeSelect.attr('disabled', true).val('');
};

$('#id_rol').on('change', () => checkBranchOffice());