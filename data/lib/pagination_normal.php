<?php
function paginate_normal($page, $tpages, $limit, $funcion)
{
  //if ($tpages == 1) return;

  $out = '<nav class="d-flex justify-content-end p-1">';
  $out .= '<ul class="pagination mb-0">';

  if ($page == 1) {
    $out .= '<li class="page-item disabled text-primary">';
    $out .= '<button type="button" class="page-link disabled text-primary">&laquo; Ant</button>';
    $out .= '</li>';
  } else {
    $out .= '<li class="page-item">';
    $out .= '<button type="button" class="page-link text-primary" onclick="' . $funcion . '(' . ($page - 1) . ')">&laquo; Ant</button>';
    $out .= '</li>';
  }

  // first label
  if ($page > ($limit + 1)) {
    $out .= '<li class="page-item">';
    $out .= '<button type="button" class="page-link text-primary" onclick="' . $funcion . '(1)">1</button>';
    $out .= '</li>';
  }

  // interval
  if ($page > ($limit + 2)) {
    $out .= '<li class="page-item">';
    $out .= '<button type="button" class="page-link text-primary">...</button>';
    $out .= '</li>';
  }

  $pmin = ($page > $limit) ? ($page - $limit) : 1;
  $pmax = ($page < ($tpages - $limit)) ? ($page + $limit) : $tpages;

  for ($i = $pmin; $i <= $pmax; $i++) {
    if ($i == $page) {
      $out .= '<li class="page-item text-primary active">';
      $out .= '<button type="button" class="page-link text-white">';
      $out .= $i;
      $out .= '</button>';
      $out .= '</li>';
    } else if ($i == 1) {
      $out .= '<li class="page-item">';
      $out .= '<button type="button" class="page-link text-primary" onclick="' . $funcion . '(1)">';
      $out .= $i;
      $out .= '</button>';
      $out .= '</li>';
    } else {
      $out .= '<li class="page-item">';
      $out .= '<button type="button" class="page-link text-primary" onclick="' . $funcion . '(' . $i . ')">';
      $out .= $i;
      $out .= '</button>';
      $out .= '</li>';
    }
  }

  // Interval
  if ($page < ($tpages - $limit - 1)) {
    $out .= '<li class="page-item">';
    $out .= '<button type="button" class="page-link text-primary">...</button>';
    $out .= '</li>';
  }

  // last
  if ($page < ($tpages - $limit)) {
    $out .= '<li class="page-item">';
    $out .= '<button type="button" class="page-link text-primary" onclick="' . $funcion . '(' . $tpages . ')">';
    $out .= $tpages;
    $out .= '</button>';
    $out .= '</li>';
  }

  // next
  if ($page < $tpages) {
    $out .= '<li class="page-item">';
    $out .= '<button type="button" class="page-link text-primary" onclick="' . $funcion . '(' . ($page + 1) . ')">Sig &raquo;</button>';
    $out .= '</li>';
  } else {
    $out .= '<li class="page-item disabled text-primary">';
    $out .= '<button type="button" class="page-link disabled text-primary">Sig &raquo;</button>';
    $out .= '</li>';
  }


  $out .= '</ul>';
  $out .= '</nav>';

  return $out;
}
