// DATA TABLE
const datatable = new DataTable({
  identifier: PAGE_CONFIG.page_identifier,
  modalTitleNew: PAGE_CONFIG.modal_title_add,
  modalTitleEdit: PAGE_CONFIG.modal_title_edit
});

datatable._initDataTable();

const load = () => datatable._load();