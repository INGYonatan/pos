const $init_table_editable = tableId => {
  $(tableId).Tabledit({
    buttons: {
      edit: {
        class: 'btn btn-success',
        html: '<span class="mdi mdi-pencil"></span>',
        action: 'edit'
      }
    },
    inputClass: 'form-control form-control-sm',
    deleteButton: true,
    saveButton: true,
    autoFocus: false,
    columns: {
      identifier: [0, "id"],
      editable: [
        [1, "col1"],
        [2, "col2"],
        [3, "col3"],
        [4, "col4"],
        [6, "col6"]
      ]
    }
  });
}