<?php $modal_cotizaciones_id = $modal_cotizaciones_id ? $modal_cotizaciones_id : $page_config['page_identifier'];  ?>

<div class="modal fade" id="<?= $modal_cotizaciones_id; ?>-modal-ver-productos" tabindex="-1" role="dialog" aria-labelledby="<?= $modal_cotizaciones_id; ?>-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form id="<?= $modal_cotizaciones_id; ?>-form-data" class="modal-content form-validate" autocomplete="off">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="<?= $modal_cotizaciones_id; ?>-modal-label">Productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div id="<?= $modal_cotizaciones_id; ?>-tabla-ver-productos" class="modal-body"></div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
      </div>
    </form>
  </div>
</div>