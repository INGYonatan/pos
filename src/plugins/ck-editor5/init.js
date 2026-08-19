let $cs_ckeditors = [];

const $init_ckeditors = () => $('.cs-ckeditor5').each(function () {
  const id = $(this).attr('id');
  const name = $(this).attr('data-name') != undefined ? $(this).attr('data-name') : `${id}-image`;
  const required = $(this).attr('data-required') == 'true' ? true : false;
  const requiredMessage = $(this).attr('data-requiredMessage') != undefined ? $(this).attr('data-requiredMessage') : 'Completa los campos requeridos';

  $cs_ckeditors[name] = {
    editor: null,
    id: null,
    name: null,
    required: null,
    requiredMessage: null
  };

  ClassicEditor.create(document.querySelector(`#${id}`), {
    mediaEmbed: {
      previewsInData: true
    },
  }).then(editor => {
    $cs_ckeditors[name].editor = editor;
    $cs_ckeditors[name].id = id;
    $cs_ckeditors[name].name = name;
    $cs_ckeditors[name].required = required;
    $cs_ckeditors[name].requiredMessage = requiredMessage;
  });
});

$init_ckeditors();