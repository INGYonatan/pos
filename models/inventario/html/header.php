<?php
include __DIR__ . "/styles.php";

/**
 * @var PDFTemplateInventarioModel $this
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
      <td class="cell header-title" align="center">Existencias y costos actuales</td>
    </tr>
  </thead>
</table>

<table cellspacing="0" cellpadding="2" border="0">
  <tbody>
    <tr>
      <td class="cell"><span class="text-bold"> Productos:</span> <?= $this->getTypeProducts(); ?></td>
    </tr>

    <tr>
      <td class="cell"><span class="text-bold"> Proveedores:</span> <?= $this->getTypeSuppliers(); ?></td>
    </tr>
  </tbody>
</table>

<div></div>

<table cellspacing="0" cellpadding="5" border="0">
  <tbody>
    <tr>
      <td class="cell"><span class="text-bold">Total basado en:</span> Costo promedio</td>

      <td class="cell"></td>

      <td class="cell text-end"><span class="text-bold">Moneda:</span> Todos</td>
    </tr>
  </tbody>
</table>

<table cellspacing="0" cellpadding="5" border="0">
  <thead>
    <tr>
      <th class="cellheader text-bold" width="12%">Clave</th>
      <th class="cellheader text-bold" width="25%">Descripción</th>
      <th class="cellheader text-bold" width="18%">Marca</th>
      <th class="cellheader text-bold" width="12%">Línea</th>
      <th class="cellheader text-bold text-end" width="10%">Ult. Costo</th>
      <th class="cellheader text-bold text-center" width="10%">Existencia</th>
      <th class="cellheader text-bold text-end" width="13%">Costo total</th>
    </tr>
  </thead>
</table>