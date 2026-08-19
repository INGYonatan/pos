<div id="modal-cotizaciones-agregar-anticipo" class="modal fade" role="dialog" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="form-cotizaciones-agregar-anticipo" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">Agregar anticipo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-12 col-lg-6">
            <div class="form-group" style="pointer-events: none;">
              <label class="form-label" for="fdacaa-monto">Monto total</label>
              <div class="input-group">
                <div class="input-group-text">
                  $
                </div>

                <input id="fdacaa-monto" class="form-control" name="quote_totalToPay" type="text">
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="form-group" style="pointer-events: none;">
              <label class="form-label" for="fdacaa-saldo">Saldo pendiente</label>
              <div class="input-group">
                <div class="input-group-text">
                  $
                </div>

                <input id="fdacaa-saldo" class="form-control" name="quote_balance" type="text">
              </div>
            </div>
          </div>
        </div>

        <hr>

        <table class="table">
          <tbody>
            <tr>
              <td class="align-middle">
                <label class="form-label m-0" for="atc-fecha_hora">Fecha</label>
              </td>

              <td class="align-middle" colspan="3">
                <div class="form-group m-0">
                  <input id="atc-fecha_hora" class="form-control form-control-sm datepicker" name="fecha_hora" value="<?= date("d-m-Y"); ?>" type="text" required>
                </div>
              </td>
            </tr>

            <tr>
              <td class="align-middle">
                <label class="form-label m-0" for="atc-efectivo_monto">Efectivo</label>
              </td>

              <td class="align-middle" colspan="3">
                <div class="form-group m-0">
                  <input id="atc-efectivo_monto" class="form-control form-control-sm payWith" name="efectivo_monto" data-content="#atc-efectivo_monto-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                </div>

                <div id="atc-efectivo_monto-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                  <div class="flex-1">
                    <div class="form-group m-0">
                      <label class="form-label" for="atc-efectivo_referencia"><small>Referencia</small></label>
                      <input id="atc-efectivo_referencia" class="form-control form-control-sm" name="efectivo_referencia" type="text">
                    </div>
                  </div>
                </div>
              </td>
            </tr>

            <tr>
              <td class="align-middle">
                <label class="form-label m-0" for="atc-cheque_monto">Cheque</label>
              </td>

              <td class="align-middle" colspan="3">
                <div class="form-group m-0">
                  <input id="atc-cheque_monto" class="form-control form-control-sm payWith" name="cheque_monto" data-content="#atc-cheque_monto-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                </div>

                <div id="atc-cheque_monto-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                  <div class="flex-1">
                    <div class="form-group m-0">
                      <label class="form-label" for="atc-cheque_referencia"><small>Referencia</small><span class="text-danger">*</span></label>
                      <input id="atc-cheque_referencia" class="form-control form-control-sm" name="cheque_referencia" type="text" required>
                    </div>
                  </div>
                </div>
              </td>
            </tr>

            <tr>
              <td class="align-middle">
                <label class="form-label m-0" for="atc-transferencia_monto">Transferecia</label>
              </td>

              <td class="align-middle" colspan="3">
                <div class="form-group m-0">
                  <input id="atc-transferencia_monto" class="form-control form-control-sm payWith" name="transferencia_monto" data-content="#atc-transferencia_monto-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                </div>

                <div id="atc-transferencia_monto-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                  <div class="flex-1">
                    <div class="form-group m-0">
                      <label class="form-label" for="atc-transferencia_referencia"><small>Referencia<span class="text-danger">*</span></small></label>
                      <input id="atc-transferencia_referencia" class="form-control form-control-sm" name="transferencia_referencia" type="text" required>
                    </div>
                  </div>
                </div>
              </td>
            </tr>

            <tr>
              <td class="align-middle">
                <label class="form-label m-0" for="atc-tarjeta_credito_monto">Tarjeta de crédito</label>
              </td>

              <td class="align-middle" colspan="3">
                <div class="form-group m-0">
                  <input id="atc-tarjeta_credito_monto" class="form-control form-control-sm payWith" name="tarjeta_credito_monto" data-content="#atc-tarjeta_credito_monto-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                </div>

                <div id="atc-tarjeta_credito_monto-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                  <div class="flex-1">
                    <div class="form-group m-0">
                      <label class="form-label" for="atc-tarjeta_credito_numero"><small>Número de tarjeta<span class="text-danger">*</span></small></label>
                      <input id="atc-tarjeta_credito_numero" class="form-control form-control-sm" name="tarjeta_credito_numero" type="text" required>
                    </div>
                  </div>
                </div>
              </td>
            </tr>

            <tr>
              <td class="align-middle">
                <label class="form-label m-0" for="atc-tarjeta_debito_monto">Tarjeta de débito</label>
              </td>

              <td class="align-middle" colspan="3">
                <div class="form-group m-0">
                  <input id="atc-tarjeta_debito_monto" class="form-control form-control-sm payWith" name="tarjeta_debito_monto" data-content="#atc-tarjeta_debito_monto-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                </div>

                <div id="atc-tarjeta_debito_monto-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                  <div class="flex-1">
                    <div class="form-group m-0">
                      <label class="form-label" for="atc-tarjeta_debito_numero"><small>Número de tarjeta<span class="text-danger">*</span></small></label>
                      <input id="atc-tarjeta_debito_numero" class="form-control form-control-sm" name="tarjeta_debito_numero" type="text" required>
                    </div>
                  </div>
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="2">
                <div id="fdacaa-alert-balance-container" class="row mb-1">
                  <div class="col-12">
                    <div id="fdacaa-alert-balance"></div>
                  </div>
                </div>

                <div class="form-group" style="pointer-events: none;">
                  <label class="form-label" for="fdacaa-nuevo-saldo">Nuevo saldo</label>
                  <div class="input-group">
                    <div class="input-group-text">
                      $
                    </div>

                    <input id="fdacaa-nuevo-saldo" class="form-control" name="quote_newBalance" value="0" type="text">
                  </div>
                </div>
              </td>
            </tr>

            <tr>
              <td colspan="2">
                <div class="form-group">
                  <label class="form-label" for="atc-notas">Notas</label>
                  <textarea id="atc-notas" class="form-control form-control-sm" name="notas" rows="2"></textarea>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <input name="uid" type="hidden">
      <input name="place" value="cotizaciones-cerradas" type="hidden">
      <input name="action" value="agregar-anticipo" type="hidden">

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-primary" type="submit">Agregar anticipo</button>
      </div>
    </form>
  </div>
</div>

<script>
  const $cotizacionesAnticipos_calculateNewBalance = () => {
    let totalPay = 0;
    const alertBalanceContainer = document.querySelector('#fdacaa-alert-balance-container');
    const alertBalance = document.querySelector('#fdacaa-alert-balance');

    document.querySelectorAll('#modal-cotizaciones-agregar-anticipo .payWith').forEach($el => {
      const val = parseFloat($el.value) || 0;
      totalPay += val;
    });

    const currentBalance = parseFloat(document.querySelector('#fdacaa-saldo').value) || 0;
    const newBalance = currentBalance - totalPay;
    let balanceToShow = newBalance;

    if (newBalance < 0) {
      balanceToShow = 0;

      alertBalanceContainer.style.display = 'block';
      alertBalance.innerHTML = `
        <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
          <strong>Atención!</strong> El monto a pagar es mayor al saldo pendiente. El nuevo saldo se ajustará a $0.00.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;
    } else {
      alertBalanceContainer.style.display = 'none';
      alertBalance.innerHTML = '';
    }

    document.querySelector('#fdacaa-nuevo-saldo').value = balanceToShow.toFixed(DECIMALS_CURRENCY);
  };

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#modal-cotizaciones-agregar-anticipo .payWith').forEach($el => {
      $el.addEventListener('input', $cotizacionesAnticipos_calculateNewBalance);
    });

    $(document).on("click", ".btn-edit", () => setTimeout(() => {
      $(".payWith").trigger("keyup");
    }, 200))

    // $(".btn-add").on("click", function() {
    //   /* const modal = new bootstrap.Modal(document.getElementById('<?= $modal_ventas_pagos_id; ?>-modal'), {
    //     keyboard: false
    //   }); */

    //   /* document.querySelector('#<?= $modal_ventas_pagos_id; ?>-form-data').reset(); */
    //   document.querySelector('#fdacaa-alert-balance-container').style.display = 'none';
    //   document.querySelector('#fdacaa-alert-balance').innerHTML = '';
    //   //document.querySelector('#fdacaa-nuevo-saldo').value = document.querySelector('#fdacaa-saldo').value;
    // })
  });
</script>