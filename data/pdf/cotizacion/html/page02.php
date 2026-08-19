<style>
  .description {
    font-size: 8px;
    font-weight: 600;
    text-align: left;
    vertical-align: middle;
    margin: 0;
    color: #000;
  }

  .description-sm {
    font-size: 7px;
    font-weight: 600;
    text-align: left;
    vertical-align: middle;
    margin: 0;
    color: #000;
  }

  .description-sm-italic {
    font-size: 6px;
    font-weight: bold;
    text-align: justify;
    vertical-align: middle;
    margin: 0;
    color: #000;
    font-family: 'Courier New', Courier, monospace;
  }
</style>

<table cellspacing="0" cellpadding="6" border="0">
  <tbody>
    <tr>
      <td class="description-sm-italic" style="border-bottom: 0.5px solid #EAEAEA; border-top: 0.5px solid #EAEAEA;"><?= $quote->page02Note; ?></td>
    </tr>
  </tbody>
</table>

<br>

<table cellspacing="0" cellpadding="5" border="0">
  <tbody>
    <tr>
      <td>
        <img src="<?= $bank->logo; ?>" alt="<?= $company->name; ?>" height="50px">
      </td>
      <td class="description" colspan="2"><?= $company->socialReason; ?><br><?= $company->rfc; ?></td>
      <td class="description">NO. DE CUENTA: <?= $bank->accountNumber; ?><br>CLABE: <?= $bank->clabe; ?></td>
    </tr>
  </tbody>
</table>