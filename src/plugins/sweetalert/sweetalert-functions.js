const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
});

function showSweetToast({
  icon,
  message
}) {
  Toast.fire({
    icon,
    title: message
  });
}

function showSweetAlert({
  title,
  message
}) {
  return new Promise((resolve, reject) => Swal.fire({
    title,
    html: message,
    confirmButtonText: 'Aceptar'
  }).then(() => resolve(true)));
}

function showSweetConfirm({
  title = '¡Cuidado!',
  message
}) {
  return new Promise((resolve, reject) => {
    Swal.fire({
      title,
      html: message,
      showCancelButton: true,
      confirmButtonText: 'Si, Continuar',
      cancelButtonText: 'No, Cancelar',
    }).then((result) => {
      if (result.isConfirmed) resolve(true);
      if (result.isDenied) resolve(false);
    })
  });
}