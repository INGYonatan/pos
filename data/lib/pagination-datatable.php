<?php
function paginate(
  $page,
  $tpages,
  $limit
) {
  $out = '<nav class="d-flex justify-content-end p-1">';
  $out .= '<ul class="pagination mb-0 justify-content-center justify-content-lg-end" style="flex-wrap: wrap;">';

  if ($page == 1) $out .= '
    <li class="page-item">
      <a class="page-link disabled text-muted"
        href="javascript:void(0)"
      >
        &laquo; Ant
      </a>
    </li>
  ';

  if ($page > 1) $out .= '
    <li class="page-item">
      <a class="page-link text-primary"
        data-page="' . ($page - 1) . '"
        href="javascript:void(0)"
      >
        &laquo; Ant
      </a>
    </li>
  ';

  // first label
  if ($page > ($limit + 1)) $out .= '
    <li class="page-item">
      <a class="page-link text-primary"
        data-page="1"
        href="javascript:void(0)"
      >
        1
      </a>
    </li>
  ';

  // interval
  if ($page > ($limit + 2)) $out .= '
    <li class="page-item">
      <a class="page-link text-muted"
        href="javascript:void(0)"
      >
        ...
      </a>
    </li>
  ';

  $pmin = ($page > $limit) ? ($page - $limit) : 1;
  $pmax = ($page < ($tpages - $limit)) ? ($page + $limit) : $tpages;

  for ($i = $pmin; $i <= $pmax; $i++) {
    if ($i == $page) {
      $out .= '
        <li class="page-item active">
          <a class="page-link text-white"
            href="javascript:void(0)"
          >
            ' . $i . '
          </a>
        </li>
      ';
    } else if ($i == 1) {
      $out .= '
        <li class="page-item">
          <a class="page-link text-primary"
            data-page="' . $i . '"
            href="javascript:void(0)"
          >
            ' . $i . '
          </a>
        </li>
      ';
    } else {
      $out .= '
        <li class="page-item">
          <a class="page-link text-primary"
            data-page="' . $i . '"
            href="javascript:void(0)"
          >
            ' . $i . '
          </a>
        </li>
      ';
    }
  }

  // Interval
  if ($page < ($tpages - $limit - 1)) $out .= '
    <li class="page-item">
      <a class="page-link text-muted"
        href="javascript:void(0)"
      >
        ...
      </a>
    </li>
  ';

  // last
  if ($page < ($tpages - $limit)) $out .= '
    <li class="page-item">
      <a class="page-link text-primary"
        data-page="' . $tpages . '"
        href="javascript:void(0)"
      >
        ' . $tpages . '
      </a>
    </li>
  ';

  // next
  if ($page < $tpages) {
    $out .= '
      <li class="page-item">
        <a class="page-link text-primary"
          data-page="' . ($page + 1) . '"
          href="javascript:void(0)"
        >
          Sig &raquo;
        </a>
      </li>
    ';
  } else {
    $out .= '
      <li class="page-item">
        <a class="page-link disabled text-muted"
          href="javascript:void(0)"
        >
          Sig &raquo;
        </a>
      </li>
    ';
  }

  $out .= '</ul>';
  $out .= '</nav>';

  return $out;
}
