<?php
include 'inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/types.helper.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$page_config = [
  'page_title'        => 'Exportar reportes',
  'page_identifier'   => 'exportar-reportes',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

$typesHelper  = new TypesHelper();
$resTypes     = $typesHelper->getAll();
$types        = $resTypes->data["rows"];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600&display=swap');

    :root {
      --report-bg-start: #0f172a;
      --report-bg-mid: #1e293b;
      --report-bg-end: #334155;
      --report-accent: #f59e0b;
      --report-accent-2: #22c55e;
      --report-ink: #0b1120;
      --report-surface: rgba(255, 255, 255, 0.96);
      --report-border: rgba(15, 23, 42, 0.12);
      --report-muted: #64748b;
    }

    .report-shell {
      position: relative;
      background: radial-gradient(1200px 600px at 10% -10%, rgba(245, 158, 11, 0.25), transparent 60%),
        radial-gradient(900px 500px at 90% -20%, rgba(34, 197, 94, 0.2), transparent 60%),
        linear-gradient(135deg, var(--report-bg-start), var(--report-bg-mid) 55%, var(--report-bg-end));
      border-radius: 20px;
      padding: 22px;
      overflow: hidden;
    }

    .report-shell::before {
      content: "";
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(248, 250, 252, 0.08) 1px, transparent 1px);
      background-size: 26px 26px;
      opacity: 0.25;
      pointer-events: none;
    }

    .report-shell::after {
      content: "";
      position: absolute;
      inset: 18px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      pointer-events: none;
    }

    .report-hero {
      color: #f8fafc;
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 16px 10px 6px;
      font-family: "Fraunces", "Georgia", serif;
    }

    .report-hero h1 {
      font-size: clamp(26px, 3vw, 34px);
      margin: 0;
      letter-spacing: 0.4px;
    }

    .report-hero p {
      margin: 0;
      color: #e2e8f0;
      font-family: "Work Sans", "Segoe UI", sans-serif;
      font-size: 15px;
      max-width: 680px;
    }

    .report-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      border-radius: 999px;
      background: rgba(248, 250, 252, 0.16);
      color: #f8fafc;
      font-family: "Work Sans", "Segoe UI", sans-serif;
      font-size: 12px;
      letter-spacing: 0.6px;
      text-transform: uppercase;
      width: fit-content;
    }

    .report-card {
      background: var(--report-surface);
      border-radius: 18px;
      box-shadow: 0 22px 50px rgba(15, 23, 42, 0.2);
      padding: 20px;
      margin-top: 16px;
      font-family: "Work Sans", "Segoe UI", sans-serif;
    }

    .report-card-header {
      display: flex;
      flex-wrap: wrap;
      gap: 12px 20px;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
    }

    .report-card-header h3 {
      margin: 0 0 4px;
      font-size: 18px;
      color: #0f172a;
    }

    .report-card-header p {
      margin: 0;
      color: var(--report-muted);
      font-size: 13px;
    }

    .report-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      border-radius: 999px;
      background: rgba(15, 23, 42, 0.08);
      color: #0f172a;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.3px;
    }

    .filter-grid {
      margin-bottom: 8px;
    }

    .report-card .form-label {
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 6px;
    }

    .report-card .form-control,
    .report-card .form-select {
      border-radius: 12px;
      border-color: #cbd5f5;
      background-color: #f8fafc;
      box-shadow: none;
    }

    .report-card .form-control:focus,
    .report-card .form-select:focus {
      border-color: rgba(245, 158, 11, 0.6);
      box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.18);
    }

    .report-helper {
      font-size: 12px;
      color: var(--report-muted);
      margin-top: 6px;
    }

    .report-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 14px;
      margin-top: 14px;
    }

    .btn-report {
      border: none;
      border-radius: 14px;
      padding: 14px 18px;
      color: #0b1120;
      font-weight: 600;
      letter-spacing: 0.3px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);
    }

    .btn-report:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 26px rgba(15, 23, 42, 0.18);
    }

    .btn-report span {
      font-size: 15px;
    }

    .btn-report small {
      font-size: 12px;
      opacity: 0.8;
      display: block;
    }

    .btn-report .report-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: rgba(15, 23, 42, 0.15);
      font-size: 18px;
    }

    .btn-report-inventory {
      background: linear-gradient(120deg, rgba(245, 158, 11, 0.95), rgba(250, 204, 21, 0.9));
    }

    .btn-report-sales {
      background: linear-gradient(120deg, rgba(34, 197, 94, 0.95), rgba(74, 222, 128, 0.9));
    }

    .report-note {
      margin-top: 14px;
      font-size: 13px;
      color: #475569;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(15, 23, 42, 0.06);
    }

    @media (max-width: 768px) {
      .report-shell {
        padding: 16px;
      }

      .report-hero {
        padding: 10px 6px 4px;
      }
    }
  </style>
</head>

<body class="loading">
  <!-- Begin page -->
  <div id="wrapper">
    <!-- HEADER -->
    <?php include 'src/components/header.php'; ?>

    <!-- SIDEBAR -->
    <?php include 'src/components/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">
          <form id="<?= $page_config['page_identifier']; ?>-filters-form" class="row" autocomplete="off">
            <div class="col-12">
              <div class="report-shell">
                <div class="report-hero">
                  <span class="report-badge">Centro de exportacion</span>
                  <h1 class="text-white">Exportar reportes</h1>
                  <p>Genera reportes con los mismos filtros del inventario. Selecciona el periodo, limita los catalogos y exporta en segundos.</p>
                </div>

                <div class="report-card">
                  <div class="report-card-header">
                    <div>
                      <h3>Filtros de reporte</h3>
                      <p>Aplica los filtros que necesitas para un reporte preciso.</p>
                    </div>
                    <span class="report-chip"><i class="fa fa-bolt"></i> Listo para exportar</span>
                  </div>

                  <div class="row g-3 filter-grid">
                    <div class="col-12 col-lg-4">
                      <div class="form-group">
                        <label class="form-label" for="filter-month">Mes y año</label>
                        <input id="filter-month" class="form-control" name="month" placeholder="YYYY-MM" type="month">
                        <div class="report-helper">Formato requerido: YYYY-MM</div>
                      </div>
                    </div>

                    <?php if ($IS_ADMIN) : ?>
                      <div class="col-12 col-lg-4">
                        <div class="form-group">
                          <label class="form-label" for="filter-id_sucursal">Sucursal</label>
                          <select id="filter-id_sucursal" class="form-control form-select" name="id_sucursal">
                            <?= getBranchOfficesCatalog('', '--Todas--', true) ?>
                          </select>
                        </div>
                      </div>
                    <?php endif; ?>

                    <div class="col-12 col-lg-4">
                      <div class="form-group">
                        <label class="form-label" for="filter-brandId">Marca</label>
                        <select id="filter-brandId" class="form-control form-select" name="brandId" catalog-onChange="#filter-categoryId" data-parameters="<?= htmlentities(json_encode(['action' => 'get-brand-categories'])) ?>" data-resetCatalog="true">
                          <?= getBrandsCatalog("", "--Todas--"); ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-12 col-lg-4">
                      <div class="form-group">
                        <label class="form-label" for="filter-categoryId">Linea</label>
                        <select id="filter-categoryId" class="form-control form-select" name="categoryId" catalog-onChange="#filter-familyId" data-parameters="<?= htmlentities(json_encode(['action' => 'get-category-families'])) ?>" data-resetCatalog="true">
                          <option value="">--Todas--</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-12 col-lg-4">
                      <div class="form-group">
                        <label class="form-label" for="filter-familyId">Familia</label>
                        <select id="filter-familyId" class="form-control form-select" name="familyId">
                          <option value="">--Todas--</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label">Tipo de producto</label>

                        <?php foreach ($types as $type) :
                          /**
                           * @var TypesHelper $type
                           */
                        ?>
                          <div class="form-check">
                            <input class="form-check-input check-typeId" type="checkbox" value="<?= $type->getId(); ?>" id="check<?= $type->getId() ?>" name="typeIds[]">
                            <label class="form-check-label" for="check<?= $type->getId() ?>">
                              <?= $type->getName(); ?>
                            </label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <div class="col-12">
                      <div class="report-actions">
                        <a id="btn-report-inventory" class="btn-report btn-report-inventory" href="#" target="_blank" rel="noopener" data-report-url="<?= BASE_URL; ?>/pdf-inventario.php">
                          <div>
                            <span>Reporte de inventario</span>
                            <small>PDF con filtros actuales</small>
                          </div>
                          <div class="report-icon"><i class="fa fa-file-pdf"></i></div>
                        </a>

                        <a id="btn-report-inventory" class="btn-report btn-report-inventory" href="#" target="_blank" rel="noopener" data-report-url="<?= BASE_URL; ?>/pdf-inventario.php?isExcel=si">
                          <div>
                            <span>Reporte de inventario</span>
                            <small>Excel con filtros actuales</small>
                          </div>
                          <div class="report-icon"><i class="fa fa-file-excel"></i></div>
                        </a>

                        <a id="btn-report-sales" class="btn-report btn-report-sales" href="#" target="_blank" rel="noopener" data-report-url="<?= BASE_URL; ?>/exports/venta-productos-excel">
                          <div>
                            <span>Reporte de ventas</span>
                            <small>Excel con filtros actuales</small>
                          </div>
                          <div class="report-icon"><i class="fa fa-file-excel"></i></div>
                        </a>

                        <a id="btn-report-sales-pdf" class="btn-report btn-report-sales" href="#" target="_blank" rel="noopener" data-report-url="<?= BASE_URL; ?>/exports/venta-productos-pdf.php">
                          <div>
                            <span>Reporte de ventas</span>
                            <small>PDF con filtros actuales</small>
                          </div>
                          <div class="report-icon"><i class="fa fa-file-pdf"></i></div>
                        </a>
                      </div>
                    </div>
                  </div>

                  <div class="report-note"><i class="fa fa-info-circle"></i> Consejo: puedes dejar filtros vacios para un reporte completo.</div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- FOOTER -->
      <?php include 'src/components/footer.php'; ?>
    </div>
  </div>
  <!-- END wrapper -->

  <!-- PAGE LOADINGS -->
  <?php include 'src/components/page-loadings.php'; ?>

  <!-- REQUIRED SCRIPTS -->
  <?php include 'src/components/required-scripts.php'; ?>

  <!-- APP JS -->
  <script src="<?= BASE_URL; ?>/src/js/app.min.js"></script>

  <script>
    const buildReportUrl = (baseUrl, concat = "?") => {
      const month = $('#filter-month').val();
      const idSucursal = $('#filter-id_sucursal').val();
      const brandId = $('#filter-brandId').val();
      const categoryId = $('#filter-categoryId').val();
      const familyId = $('#filter-familyId').val();
      const typeIds = $('.check-typeId:checked').map(function() {
        return this.value;
      }).get();

      const params = new URLSearchParams();

      if (month) params.append('month', month);
      if (idSucursal) params.append('sid', idSucursal);
      if (brandId) params.append('brandId', brandId);
      if (categoryId) params.append('categoryId', categoryId);
      if (familyId) params.append('familyId', familyId);
      if (typeIds.length) params.append('typeIds', typeIds.join(','));
      let url = baseUrl;

      if ([...params].length) url += `${concat}${params.toString()}`;

      return url;
    };

    const updateReportLinks = () => {
      $('[data-report-url]').each(function() {
        const baseUrl = $(this).data('report-url');

        let concat = baseUrl.includes('?') ? '&' : '?';

        const url = buildReportUrl(baseUrl, concat);
        $(this).attr('href', url);
      });
    };

    updateReportLinks();
    $('#filter-month, #filter-id_sucursal, #filter-brandId, #filter-categoryId, #filter-familyId, .check-typeId')
      .on('change input', () => updateReportLinks());
  </script>
</body>

</html>