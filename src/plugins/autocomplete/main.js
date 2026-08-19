class Autocomplete {
  constructor({
    identifier,
    source,
    minLength = 2,
    useCleanOnSelect = false,

    onSelect = () => null,
    onEnter = data => data
  }) {
    this.state = {
      identifier,
      source,
      minLength,

      useCleanOnSelect
    };

    this.onSelect = data => onSelect(data);
    this.onEnter = data => onEnter(data)
  }

  initAutocomplete = () => {
    const identifier = this.state.identifier;
    const onEnter = data => this.onEnter(data);

    $(`#${identifier}`).autocomplete({
      source: this.state.source,
      minLength: this.state.minLength,
      autoFocus: true,
      select: (event, ui) => {
        this.onSelect(ui.item)
        if (this.state.useCleanOnSelect) setTimeout(() => {
          $(`#${this.state.identifier}`).val('');
        }, 200);
      }
    }).data("ui-autocomplete")._renderItem = function (ul, item) {
      const newText = String(item.label).replace(
        new RegExp(this.term, "gi"),
        "<span style='font-weight:bold;'>$&</span>"
      );

      /* const supplierType = String(item.supplierType).replace(
        new RegExp(this.term, "gi"),
        "<span style='font-weight:bold;'>$&</span>"
      ); */

      /* const business = item.business;
      const image = item.image; */

      return $("<li></li>")
        .data("item.autocomplete", item)
        .append(`
        <div data-item="${JSON.stringify(item)}" class="cs-autocomplete-item cs-autocomplete-item-${identifier}" style="
          display: flex;
          align-items: center;
          width: 100%;
          padding-top: 0.6rem;
          padding-bottom: 0rem;
          padding-left: 0.6rem;
          padding-right: 0.6rem;
          font-size: 1rem;
          cursor: pointer;
          color: #000;
          font-weight: 500;  
        ">
          <p>
            ${newText}
          </p>
        </div>
      `).appendTo(ul);
    };

    $(`#${identifier}`).keypress(function (e) {
      if (e.which === 13) {
        onEnter($(this).val());
        $(this).val('');
      }
    });
  }
}