<?php
include '../../inc/settings.inc.php';

$action = $_POST['action'];

switch ($action) {
  case 'get_citys':
    $state_id = cleanStr($_POST['stateId']);
    $city_id  = cleanStr($_POST['cityId']);

    $query = "SELECT
       idMunicipio,
       Municipio
      FROM municipios AS EC
      WHERE idEstado = $state_id
    ";

    $query_result = mysqli_query($mysqli, $query);

    ob_start(); ?>
    <option value="">--Seleccionar--</option>
    <?php while ($row = mysqli_fetch_array($query_result)) :
      $selected_city = $city_id == $row['idMunicipio'] ? 'selected' : '';
    ?>
      <option <?= $selected_city; ?> value="<?= $row['idMunicipio']; ?>"><?= $row['Municipio']; ?></option>
    <?php endwhile; ?>

  <?php
    $content = base64_encode(ob_get_clean());

    $response = array(
      'status'  => 'success',
      'content' => $content
    );
    break;

  case 'get_neighborhoods':
    $city_id = cleanStr($_POST['cityId']);
    $neighborhood_id = cleanStr($_POST['neighborhoodId']);

    $query = "SELECT
        idColonia,
        Colonia
      FROM colonias
      WHERE idMunicipio = $city_id
      ORDER BY Colonia ASC
    ";

    $query_result = mysqli_query($mysqli, $query);

    ob_start(); ?>
    <option value="">--Seleccionar--</option>
    <?php while ($row = mysqli_fetch_array($query_result)) :
      $neighborhood_selected = $neighborhood_id == $row['idColonia'] ? 'selected' : '';
    ?>
      <option <?= $neighborhood_selected; ?> value="<?= $row['idColonia']; ?>"><?= $row['Colonia']; ?></option>
    <?php endwhile; ?>

  <?php
    $content = base64_encode(ob_get_clean());

    $response = array(
      'status'  => 'success',
      'content' => $content
    );
    break;

  case 'get_postal_code':
    $neighborhood_id = cleanStr($_POST['neighborhoodId']);

    $query = "SELECT
        idColonia,
        Colonia,
        CP
      FROM colonias
      WHERE idColonia = $neighborhood_id
      LIMIT 1
    ";

    $query_result = mysqli_query($mysqli, $query);
    $data         = mysqli_fetch_array($query_result);

    $postal_code = $data['CP'];

    $response = array(
      'status'      => 'success',
      'postalCode'  => $postal_code
    );
    break;

  case 'get_address':
    $postal_code = cleanStr($_POST['postalCode']);

    $query_neighborhoods = "SELECT
        idColonia,
        Colonia,
        CP
      FROM colonias
      WHERE
        CP = '$postal_code'
      GROUP BY Colonia
      ORDER BY Colonia
      ASC
    ";

    $query_neighborhoods_result = mysqli_query($mysqli, $query_neighborhoods);
    $num_rows = mysqli_num_rows($query_neighborhoods_result);

    if (!$num_rows) {
      $response = array('status' => 'empty');

      echo json_encode($response);
      mysqli_close($mysqli);
      exit();
    }

    ob_start(); ?>
    <option value="">-Seleccionar--</option>
    <?php
    $num_row_n = 0;
    while ($row = mysqli_fetch_array($query_neighborhoods_result)) : ?>
      <option <?= ($num_row_n == 0 && $postal_code == $row['CP']) ? 'selected' : ''; ?> value="<?= $row['idColonia']; ?>"><?= $row['Colonia']; ?></option>
    <?php
      $num_row_n++;
    endwhile;

    $neighborhoods = base64_encode(ob_get_clean());

    $query_citys = "SELECT
        M.idMunicipio,
        M.idEstado,
        M.Municipio,
        C.CP
      FROM municipios AS M
        LEFT JOIN colonias  AS C ON (M.idMunicipio = C.idMunicipio)
      WHERE
        CP = '$postal_code'
      GROUP BY M.idMunicipio
      LIMIT 1
    ";

    $query_citys_result = mysqli_query($mysqli, $query_citys);
    $citys_num_rows = mysqli_num_rows($query_citys_result);

    $state_id;

    ob_start(); ?>
    <option value="">--Seleccionar--</option>
    <?php
    $num_row_m = 0;
    while ($row = mysqli_fetch_array($query_citys_result)) :
      $state_id = $row['idEstado'];
    ?>
      <option <?= ($num_row_m == 0 && $postal_code == $row['CP']) ? 'selected' : ''; ?> value="<?= $row['idMunicipio']; ?>"><?= $row['Municipio']; ?></option>
<?php
      $num_row_m++;
    endwhile;

    $citys = base64_encode(ob_get_clean());

    $response = array(
      'status'        => 'success',
      'neighborhoods' => $neighborhoods,
      'citys'         => $citys,
      'state'         => $state_id
    );
    break;

  default:
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
exit();
