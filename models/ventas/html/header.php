<?php
include __DIR__ . "/styles.php";

/**
 * @var PDFTemplateVentasModel $this
 */
?>

<table cellspacing="0" cellpadding="5" border="0">
  <tbody>
    <tr>
      <td class="cell" width="30%"><img src="<?= $this->getLeftLogo(); ?>" alt="Logo" height="65" /></td>
      <td class="cell" align="center" width="40%"><img src="<?= $this->getCenterLogo(); ?>" alt="Logo" height="55" /></td>
      <td class="cell text-end" width="30%"><span class="text-bold"></span></td>
    </tr>
  </tbody>
</table>

<table cellspacing="0" cellpadding="5" border="0">
  <thead>
    <tr>
      <td class="cell header-title" align="center">Reporte de ventas</td>
    </tr>
  </thead>
</table>

<table cellspacing="0" cellpadding="2" border="0">
  <tbody>
    <tr>
      <td class="cell"><span class="text-bold">Sucursal:</span> <?= $this->getTypeProducts(); ?></td>
    </tr>
    <!--tr>
      <td class="cell"><span class="text-bold">Productos:</span> <?= $this->getTypeSuppliers(); ?></td>
    </tr-->
  </tbody>
</table>

<div></div>

<table cellspacing="0" cellpadding="5" border="0">
  <tbody>
    <tr>
      <td class="cell"><span class="text-bold">Periodo:</span> <?= $this->getLine(); ?></td>
      <td class="cell"></td>
      <td class="cell text-end"><span class="text-bold">Moneda:</span> Pesos</td>
    </tr>
  </tbody>
</table>

<table cellspacing="0" cellpadding="5" border="0">
  <thead>
    <tr>
      <th class="cellheader text-bold" width="10%">Fecha</th>
      <th class="cellheader text-bold" width="10%">Marca</th>
      <th class="cellheader text-bold" width="10%">Línea</th>
      <th class="cellheader text-bold" width="8%">Clave</th>
      <th class="cellheader text-bold" width="24%">Nombre</th>
      <th class="cellheader text-bold text-center" width="8%">Cant.</th>
      <th class="cellheader text-bold text-end" width="10%">Costo total</th>
      <th class="cellheader text-bold text-end" width="10%">Precio total</th>
      <th class="cellheader text-bold" width="10%">Sucursal</th>
    </tr>
  </thead>
</table>
