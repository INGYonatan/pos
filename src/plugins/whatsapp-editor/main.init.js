class WhatsappEditor {
  constructor({
    id,
    name,
    required = false,
    requiredMessage = ''
  }) {
    this.state = {
      id,
      name,
      required,
      requiredMessage,
      editor: null
    };

    this._setEditor = editorData => this.state.editor = editorData;
  };

  _whatsappMessageToHTML = message => message.replace(/\n/g, "<br>").replace(/(?:\*)(?:(?!\s))((?:(?!\*|\n).)+)(?:\*)/g, '<b>$1</b>')
    .replace(/(?:_)(?:(?!\s))((?:(?!\n|_).)+)(?:_)/g, '<i>$1</i>')
    .replace(/(?:~)(?:(?!\s))((?:(?!\n|~).)+)(?:~)/g, '<s>$1</s>')
    .replace(/(?:--)(?:(?!\s))((?:(?!\n|--).)+)(?:--)/g, '<u>$1</u>')
    .replace(/(?:```)(?:(?!\s))((?:(?!\n|```).)+)(?:```)/g, '<tt>$1</tt>');

  //_whatsappMessageToHTML = message => `<p>${message}</p>`;

  createWhatsappEditor = (InitialMessage = '') => {
    $(`#${this.state.id} *`).remove();
    this._setEditor($(`#${this.state.id}`).whatsappEditor({ content: InitialMessage }));
  }

  getWhatsappMessage = () => this.state.editor.getFormattedContent();

  getWhatsappEditordata = () => this.state;

  setEditorMessage = message => this.createWhatsappEditor(this._whatsappMessageToHTML(message));

  resetEditor = () => this.createWhatsappEditor();
}

let $cs_whatsapp_editors = [];

const $init_whatsapp_editors = () => $('[data-whatsapp="true"]').each(function () {
  const id = $(this).attr('id');
  const name = $(this).attr('data-name') != undefined ? $(this).attr('data-name') : `${id}-mensaje_whatsapp`;
  const required = $(this).attr('data-required') == 'true' ? true : false;
  const requiredMessage = $(this).attr('data-requiredMessage') != undefined ? $(this).attr('data-requiredMessage') : 'Debe de escribir el mensaje de whatsapp';
  const message = $(this).attr('data-message') != undefined ? $(this).attr('data-message') : '';

  const whatsappEditor = new WhatsappEditor({
    id,
    name,
    required,
    requiredMessage
  });

  whatsappEditor.createWhatsappEditor(whatsappEditor._whatsappMessageToHTML(message));

  $cs_whatsapp_editors[name] = whatsappEditor;
});

$init_whatsapp_editors();