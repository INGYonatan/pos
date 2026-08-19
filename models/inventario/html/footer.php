<?php
include __DIR__ . "/styles.php";

/**
 * @var PDFTemplateInventarioModel $this
 */
?>

<table cellspacing="0" cellpadding="5" border="0">
  <tbody>
    <tr>
      <td class="cellfooter"><span class="text-bold">Usuario:</span> <?= $this->getUser(); ?></td>

      <td class="cellfooter text-center" align="center"><span class="text-bold">Fecha y Hora:</span> <?= $this->getDate(); ?></td>

      <td class="cellfooter text-end"><span class="text-bold">Pag.</span> <?= $this->getAliasNumPage(); ?></td>
    </tr>
  </tbody>
</table>