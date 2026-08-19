<?php $page_config['page_identifier'] = "cierre-caja"; ?>

<div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Nuevo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="row" bis_skin_checked="1">
                    <div class="col-12" bis_skin_checked="1">
                        <div class="table-responsive" bis_skin_checked="1">
                            <div style="max-height:350px; overflow-y:auto;">
                                <table class="table mt-4 table-bordered" style="margin-bottom:0;">
                                <tbody>
                                    <!-- Efectivo -->
                                    <tr class="fw-bold" style="height:32px;">
                                        <td>Efectivo</td>
                                        <td class="text-end">$ 1,035.67</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4">Apertura</td>
                                        <td class="text-end">$ 1,000.00</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4">Pagos en Efectivo</td>
                                        <td class="text-end">$ 35.67</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4">Entrada/Salida de efectivo</td>
                                        <td class="text-end">$ 0.00</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4 text-danger">Diferencia</td>
                                        <td class="text-end text-danger">$ -1,035.67</td>
                                    </tr>
                                    <!-- Tarjeta -->
                                    <tr class="fw-bold" style="height:32px;">
                                        <td>Tarjeta</td>
                                        <td class="text-end">$ 0.00</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4">Contado</td>
                                        <td class="text-end">$ 0.00</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4 text-danger">Diferencia</td>
                                        <td class="text-end text-danger">$ 0.00</td>
                                    </tr>
                                    <!-- Cuenta de cliente -->
                                    <tr class="fw-bold" style="height:32px;">
                                        <td>Cuenta de cliente</td>
                                        <td class="text-end">$ 0.00</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4">Contado</td>
                                        <td class="text-end">$ 0.00</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4 text-danger">Diferencia</td>
                                        <td class="text-end text-danger">$ 0.00</td>
                                    </tr>
                                    <!-- Tarjeta de Crédito -->
                                    <tr class="fw-bold" style="height:32px;">
                                        <td>Tarjeta de Crédito</td>
                                        <td class="text-end">$ 0.00</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4">Contado</td>
                                        <td class="text-end">$ 0.00</td>
                                    </tr>
                                    <tr style="height:26px;">
                                        <td class="ps-4 text-danger">Diferencia</td>
                                        <td class="text-end text-danger">$ 0.00</td>
                                    </tr>
                                </table>
                            </div>
                                </tbody>
                            </table>
                        </div> <!-- end table-responsive -->
                    </div> <!-- end col -->
                </div>


                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="form-group">
                            <label class="form-label" for="nombre_sucursal">Conteo de efectivo<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="conteo_efectivo" class="form-control" name="conteo_efectivo" type="text" required>
                                <button class="btn btn-outline-secondary" type="button" id="btn-clear-efectivo" title="Limpiar"><span>&#10006;</span></button>
                                <button class="btn btn-outline-secondary" type="button" id="btn-money-efectivo" title="Dinero"><span>&#128176;</span></button>
                                <button class="btn btn-outline-secondary" type="button" id="btn-copy-efectivo" title="Copiar"><span>&#128203;</span></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label" for="direccion">Nota de apertura</label>
                            <textarea id="nota_apertura" class="form-control" name="nota_apertura" rows="6"></textarea>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label" for="direccion">Nota de cierre</label>
                            <textarea id="nota_cierre" class="form-control" name="nota_cierre" rows="6"></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <input name="uid" type="hidden">
            <input name="action" type="hidden">
            <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
                <button class="btn btn-primary" type="submit">Cerrar caja</button>
                <script>
                // Limpiar campo
                document.getElementById('btn-clear-efectivo').onclick = function() {
                    document.getElementById('conteo_efectivo').value = '';
                };
                // Icono dinero (puedes agregar funcionalidad si lo necesitas)
                document.getElementById('btn-money-efectivo').onclick = function() {
                    document.getElementById('conteo_efectivo').focus();
                };
                // Copiar valor de efectivo
                document.getElementById('btn-copy-efectivo').onclick = function() {
                    // Busca el primer td con texto "Efectivo" en la fila anterior y copia el valor del siguiente td
                    var rows = document.querySelectorAll('table tr');
                    for (var i = 0; i < rows.length; i++) {
                        var tds = rows[i].querySelectorAll('td');
                        if (tds.length > 1 && tds[0].textContent.trim() === 'Efectivo') {
                            var valor = tds[1].textContent.replace(/[^\d.,-]/g, '');
                            document.getElementById('conteo_efectivo').value = valor;
                            break;
                        }
                    }
                };
                </script>
            </div>
        </form>
    </div>
</div>