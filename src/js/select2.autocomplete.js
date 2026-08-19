class Select2Autocomplete {
  constructor({
    selector,
    url,
    onSelect = () => null
  }) {
    this.state = {
      selector,
      url,
      items: [],
    };

    this.onSelect = onSelect;
    this._init();
  }

  _init = (value = null) => {
    $(this.state.selector).select2({
      ajax: {
        url: `${this.state.url}?value=${value}`,
        dataType: 'json',
        delay: 250,
        processResults: (data, params) => {
          params.page = params.page || 1;

          this.state.items = data.results;

          return {
            results: data.results,
            pagination: { more: false }
          };
        }
      }
    });

    $(this.state.selector).on('select2:select', (e) => {
      const selectedItem = this.state.items.find(item => item.id == e.params.data.id);
      if (selectedItem) this.onSelect(selectedItem);
    });
  }

  //_setValue = (value) => $(this.state.selector).val(value).trigger('change', value);

  _setValue = (value, text) => {
    const id = value; // Cambia esto según sea necesario
    const existingData = $(this.state.selector).select2('data');

    // Verificar si el valor ya está en la lista
    const isExisting = existingData.some(item => item.id === id);

    console.log("isExisting", isExisting);

    if (!isExisting) {
      // Si no existe, agrega el nuevo valor a la lista de datos
      const newOption = {
        id: id,
        text // Puedes cambiar esto si necesitas otro texto
      };
      $(this.state.selector).append(new Option(newOption.text, newOption.id, false, true)).trigger('change');
    } else {
      // Si ya existe, simplemente selecciona el valor
      $(this.state.selector).val(id).trigger('change');
    }
  }
}