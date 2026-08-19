let $cs_file_pickers = [];
let $cs_file_pickers_callbacks = $cs_file_pickers_out_callbacks;

const $init_file_picker = () => $('.cs-filepicker').each(function () {
  const id = $(this).attr('id');
  const name = $(this).attr('data-name') != undefined ? $(this).attr('data-name') : `${id}-image`;
  const title = $(this).attr('data-title') != undefined ? $(this).attr('data-title') : 'Adjuntar imagen';
  const subtitle = $(this).attr('data-subtitle') != undefined ? $(this).attr('data-subtitle') : 'Para un mejor rendimiento adjunta imagenes optimizadas';
  const required = $(this).attr('data-required') == 'true' ? true : false;
  const requiredMessage = $(this).attr('data-requiredMessage') != undefined ? $(this).attr('data-requiredMessage') : 'Debe de adjuntar la imagen';

  //console.log('REQUIRED', $(this).attr('data-required'));
  const image = $(this).attr('data-image') != undefined ? $(this).attr('data-image') : false;

  const errorNoFormat = $(this).attr('data-errorNoFormat') != undefined ? $(this).attr('data-errorNoFormat') : 'El tipo de archivo que intenta subir no es válido';
  const onlyFiles = $(this).attr('data-onlyFiles') != undefined ? true : false;
  const onlyImages = $(this).attr('data-onlyImages') != undefined ? true : false;

  //const fileCallbackData = $(this).attr('data-fileCallbackData') == 'true' ? true : false;

  if (typeof $cs_file_pickers_callbacks[name] === undefined) $cs_file_pickers_callbacks[name] = () => null

  console.log('FUNCIÓN:', typeof $cs_file_pickers_callbacks[name]);

  const filePicker = new CSFilePicker({
    id,
    name,
    title,
    subtitle,
    required,
    requiredMessage,
    errorNoFormat,
    onlyFiles,
    onlyImages,
    fileCallbackData: fileData => $cs_file_pickers_callbacks[name](fileData)
  });

  filePicker.createFilePicker();
  if (image) filePicker.createImagePreview({
    imageSrc: image,
    imageName: 'default-image'
  });

  $cs_file_pickers[name] = filePicker;
});

$init_file_picker();