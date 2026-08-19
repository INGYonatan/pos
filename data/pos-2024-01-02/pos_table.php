<div class="row">
  <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
    $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : 'gr.';
  ?>
    <div class="col-12 col-md-6 col-lg-4">
      <a id="producto-item-<?= $row['id_producto']; ?>" data-itemId="<?= $row['id_producto']; ?>" class="card bg-primary autocomplete-item text-dark" href="javascript:void(0)">
        <div class="card-body" style="padding: .5rem .5rem;">
          <h4 class="text-center mb-1"><?= $row['nombre_producto']; ?> <?= $row['contenido']; ?> <?= $unit_type; ?></h4>

          <hr class="text-dark">

          <div class="row">
            <div class="col-6 text-center">
              <h5>Precio</h5>
              <p>$<?= number_format($row['precio_venta'], DECIMALS_CURRENCY); ?></p>
            </div>

            <div class="col-6 text-center">
              <h5>Existencia</h5>
              <p><?= $row['stock']; ?></p>
            </div>
          </div>
        </div>
      </a>
    </div>
  <?php endwhile; ?>
</div>