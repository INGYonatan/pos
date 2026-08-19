class CSFilePicker {
  constructor({
    id,
    required = false,
    requiredMessage = '',
    name = 'cs-image',
    title = 'Adjuntar imagen',
    subtitle = '',
    inputName = 'filepicker-input',
    //supportedImages = ['image/jpeg', 'image/png', 'image/gif'],
    //supportedFiles = ['text/csv', 'application/vnd.ms-excel'],
    supportedImages = ['image/jpeg', 'image/png', 'image/gif'],
    supportedFiles = ['text/csv'],
    quality = 0.9,
    fileCallbackData = () => null,
    errorNoFormat = 'El tipo de archivo que intenta subir no es válido',
    onlyFiles = false,
    onlyImages = false
  }) {
    this.state = {
      id,
      name,
      title,
      subtitle,
      inputName,
      supportedImages,
      supportedFiles,
      imgFile: null,
      quality,
      required,
      requiredMessage,
      fileCallbackData,
      errorNoFormat,
      onlyFiles,
      onlyImages,
      result: null
    };

    this.setResult = result => this.state.result = result;
  }

  createFilePicker = () => {
    const { id, inputName, title, subtitle, name, required, requiredMessage, errorNoFormat, onlyFiles, onlyImages, supportedFiles, supportedImages } = this.state;
    this.state.imgFile = null

    const elementToReplace = document.getElementById(id);

    const extensionFiles = onlyImages ? [] : supportedFiles;
    const extensionImages = onlyFiles ? [] : supportedImages;

    const extensions = [...extensionFiles, ...extensionImages];

    /* const extensiones = extensions.map(tipo => {
      const partes = tipo.split('/');
      return `.${partes[1]}`;
    }); */
    const extensiones = extensions.map(item => item);

    const acceptValue = `${extensiones.join(',')}`;

    const filePicker = `
        <div id="${id}"
          class="cs-filepicker"
          data-title="${title}"
          data-subtitle="${subtitle}"
          data-name="${name}"
          data-required="${required}"
          data-requiredMessage="${requiredMessage}"
          data-errorNoFormat="${errorNoFormat}"
          data-onlyFiles="${onlyFiles}"
          data-onlyImages="${onlyImages}"
        >
          <div id="cs-filepicker-container-${id}">
            <div id="cs-filepicker-icon-${id}" class="cs-filepicker-icon"></div>

            <div id="cs-filepicker-preview-${id}" class="cs-filepicker-preview"></div>

            <h3 id="cs-filepicker-title-${id}" class="cs-filepicker-title">${title}</h3>

            <button id="cs-filepicker-btn-${id}" class="cs-filepicker-button" type="button">
              Buscar archivo
            </button>

            ${!!subtitle ? `<p id="cs-filepicker-subtitle-${id}" class="cs-filepicker-subtitle">${subtitle}</p>` : ''}
          </div>

          <input id="cs-filepicker-input-${id}"
            type="file" name="${inputName}"
            accept="${acceptValue}"
            style="display: none;"
          >
        </div>
    `;

    elementToReplace.outerHTML = filePicker;
    this.initListeners();
  };

  obtenerExtension = nombreArchivo => {
    // Obtener la última posición del punto en el nombre del archivo
    const ultimaPosicionPunto = nombreArchivo.lastIndexOf('.');

    // Verificar si se encuentra un punto en el nombre del archivo
    if (ultimaPosicionPunto !== -1) {
      // Obtener la extensión a partir de la última posición del punto
      const extension = nombreArchivo.slice(ultimaPosicionPunto + 1);
      return extension;
    } else {
      // Si no se encuentra un punto, no hay extensión
      return false;
    }
  }

  createImagePreview = imageData => {
    const container = document.getElementById(`cs-filepicker-preview-${this.state.id}`);
    const img = `<img class="cs-filepicker-img-preview" src="${imageData.imageSrc}" alt="${imageData.imageName}">`;
    container.innerHTML = img;
    document.getElementById(`cs-filepicker-icon-${this.state.id}`).style.display = 'none';
    this._setImage(imageData);
  }

  createFilePreview = fileData => {
    const container = document.getElementById(`cs-filepicker-icon-${this.state.id}`);
    const extensión = this.obtenerExtension(fileData.fiileName);
    const titleContainer = document.getElementById(`cs-filepicker-title-${this.state.id}`);

    let icon = '';

    if (extensión === 'csv') icon = 'xls';
    if (extensión === 'pdf') icon = 'pdf';
    if (extensión === 'doc' || extensión === 'docx') icon = 'doc';

    const iconTag = `<div id="cs-filepicker-icon-${this.state.id}" class="cs-filepicker-icon ${icon}"></div>`;
    container.outerHTML = iconTag;
    titleContainer.innerHTML = fileData.fiileName;

    this._setImage(fileData);
  };

  _setImage = img => this.state.imgFile = img;

  initListeners = () => {
    const { id, supportedImages, supportedFiles, quality, imgFile, fileCallbackData, errorNoFormat, onlyFiles, onlyImages } = this.state;
    const setResult = result => this.setResult(result);

    var inputTag = document.getElementById(`cs-filepicker-input-${id}`);
    const buttonTag = document.getElementById(`cs-filepicker-btn-${id}`);

    const clickButton = (e) => {
      e.stopPropagation();

      inputTag.click();
      return false;
    }

    const setImage = img => this._setImage(img);

    const createImagePreview = img => this.createImagePreview(img);

    const createFilePreview = file => this.createFilePreview(file);

    function readableBytes(bytes) {
      const i = Math.floor(Math.log(bytes) / Math.log(1024)),
        sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

      return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
    }

    function displayInfo(label, file) {
      console.log(`${label} - ${readableBytes(file.size)}`);
      return file.size;
    }

    buttonTag.addEventListener('click', clickButton);

    inputTag.addEventListener('change', function (event) {
      let notSupported = false;

      for (let i = 0; i < this.files.length; i++) {
        const element = this.files[i];

        console.log(element.type);

        if ((!onlyFiles && !onlyImages) && (supportedImages.indexOf(element.type) === -1 && supportedFiles.indexOf(element.type) === -1)) notSupported = true;

        if (onlyFiles && supportedFiles.indexOf(element.type) === -1) notSupported = true;
        if (onlyImages && supportedImages.indexOf(element.type) === -1) notSupported = true;

        if (!onlyFiles) {
          if (supportedImages.indexOf(element.type) != -1) {
            const imageSrc = URL.createObjectURL(element);
            const img = new Image;

            img.onload = function () {
              //let height = this.height;
              //let width = this.width;

              const max_size = 720
              let height = this.height;
              let width = this.width;

              if (width > height) {
                if (width > max_size) {
                  height *= max_size / width;
                  width = max_size;
                }
              } else {
                if (height > max_size) {
                  width *= max_size / height;
                  height = max_size;
                }
              }

              console.log(height, '-', width);

              const canvas = document.createElement("canvas");
              canvas.width = width;
              canvas.height = height;

              const ctx = canvas.getContext("2d");

              ctx.drawImage(img, 0, 0, width, height);
              const originalSize = displayInfo('Original file', element);
              const dataUrl = canvas.toDataURL(element.type);

              canvas.toBlob((blob) => {
                // Handle the compressed image. es. upload or save in local state
                displayInfo('Compressed file', blob);

                const imageData = {
                  imageSrc: dataUrl,
                  imageName: element.name,
                  name: element.name,
                  type: element.type,
                  blob: new Blob([blob])
                };

                //setImage(imageData);
                createImagePreview(imageData);
              },
                'image/jpeg',
                quality
              );
            }

            img.src = imageSrc;
          }
        }

        //if (supportedFiles.indexOf(element.type) === -1 && supportedImages.indexOf(element.type) != -1) notSupported = true;

        if (!onlyImages) {
          if (supportedFiles.indexOf(element.type) != -1) {
            notSupported = false;

            const reader = new FileReader();

            reader.addEventListener('load', function (event) {
              //console.log(event.target.result);
              const blob = new Blob([event.target.result], { type: element.type });

              console.log('BLOB', blob);

              const fileData = {
                fiileName: element.name,
                name: element.name,
                type: element.type,
                blob: new Blob([blob])
                //result: event.target.result
              };

              setResult(event.target.result);

              createFilePreview(fileData);

              fileCallbackData(event.target.result);
            });

            reader.readAsText(element);
          }
        }
      }

      if (notSupported) alert(errorNoFormat);

      document.getElementById(`cs-filepicker-input-${id}`).value = "";
    });
  }

  getFile = () => this.state.imgFile;

  getPickerId = () => this.state.id;

  getPickerData = () => this.state;

  getIsRequired = () => this.state.required;

  getResult = () => this.state.result;

  clearPicker = () => {
    this.state.imgFile = null;
    this.createFilePicker();
  }
}

let $cs_file_pickers_out_callbacks = [];