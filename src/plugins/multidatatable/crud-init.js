// DATA TABLE
const datatable = new MultiDataTable({
  identifier: PAGE_CONFIG.page_identifier,
  modalTitleNew: PAGE_CONFIG.modal_title_add,
  modalTitleEdit: PAGE_CONFIG.modal_title_edit,
  tablesConfig: PAGE_CONFIG?.tables_config,
  location: 'crud'
});

datatable._initDataTable();

const load = (page, identifier) => datatable._load(identifier, page);