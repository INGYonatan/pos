<script>
  const BASE_URL = `<?= BASE_URL; ?>`;

  const DECIMALS_CURRENCY = <?= DECIMALS_CURRENCY; ?>;

  const PAGE_CONFIG = {
    page_title: '<?= $page_config['page_title']; ?>',
    page_identifier: '<?= $page_config['page_identifier']; ?>'.split('/').join('-'),
    modal_title_add: '<?= $page_config['modal_title_add']; ?>',
    modal_title_edit: '<?= $page_config['modal_title_edit']; ?>',
    tables_config: <?= json_encode($page_config['tables_config']); ?>
  };
</script>

<!-- VENDOR JS -->
<script src="<?= BASE_URL; ?>/src/js/vendor.min.js"></script>

<!-- JQUERY VALIDATOR -->
<script src="<?= BASE_URL; ?>/src/plugins/jquery-validator/jquery.validate.js"></script>
<script src="<?= BASE_URL; ?>/src/plugins/jquery-validator/additional-methods.js"></script>

<!-- SWEETALERT2 -->
<script src="<?= BASE_URL; ?>/src/plugins/sweetalert/material-ui.js"></script>
<script src="<?= BASE_URL; ?>/src/plugins/sweetalert/sweetalert-functions.js"></script>

<!-- FUNCTIONS JS -->
<script src="<?= BASE_URL; ?>/src/js/functions.js"></script>

<script>
  $(document).on('click', '.custom-popover', e => e.stopPropagation());

  $(document).on('click', '[data-toggle="custom-popover"]', function(e) {
    e.stopPropagation();
    const popover = $(this).attr('data-popover');

    const isActive = $(popover).hasClass('active');

    if (isActive) $(popover).removeClass('active');

    if (!isActive) {
      $('.custom-popover').removeClass('active');
      $(popover).addClass('active');
    }
  });

  $(document).on('click', '[data-toggle="close-custom-popover"]', function() {
    $('.custom-popover').removeClass('active');
    $('.price-with-discount').html('');
    $(this).closest('form').trigger('reset');
  });

  function $close_all_popovers() {
    $('.custom-popover').removeClass('active');
  }
</script>

<script>
  $(function() {
    $(document).on("focus", ".cs-datalist input", function() {
      // remover active de todos los datalists
      $(".cs-datalist-options").removeClass("active");

      $(this).closest(".cs-datalist").find(".cs-datalist-options").addClass("active");
    });

    $(document).on("blur", ".cs-datalist input", function() {
      setTimeout(() => {
        $(this).closest(".cs-datalist").find(".cs-datalist-options").removeClass("active");
      }, 200);
    });

    $(document).on("click", ".cs-datalist-option", function() {
      const value = $(this).data("value");
      const input = $(this).closest(".cs-datalist").find(".cs-datalist-price");
      input.val(value).trigger("change");
      input.blur();

      $(this).closest(".cs-datalist-options").removeClass("active");
      $(this).closest(".cs-datalist").trigger("submit");
    })
  });
</script>