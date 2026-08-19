<?php

/**
 * @phpstan-type FilterStructure array{
 *    field: string, // "select" | "input" | "render"
 *    name: string,
 *    label: string,
 *    placeholder: string,
 *    colSizes: string,
 *    attributes: array,
 *    render: string,
 *    selectOptions: array,
 *    optionsRender: string,
 *    visible: bool
 * }
 */

/**
 * @var string $pageId
 * 
 * @var array{
 *     principal: FilterStructure,
 *     hidden: FilterStructure
 * } $filters
 */

$filtersLength = count($filters ?? []);

if ($filtersLength === 0) return;
?>

<!-- <div class="d-flex flex-column align-items-center gap-3 flex-lg-row flex-1"> -->
<div class="crudtable-filters-container">
  <?php foreach ($filters as $filter) :
    $fieldField         = $filter["field"] ?? "input";
    $fieldName          = $filter["name"];
    $fieldLabel         = $filter["label"];
    $fieldPlaceholder   = $filter["placeholder"];
    $fieldColSizes      = $filter["colSizes"] ?? "col-12";
    $fieldAttributes    = $filter["attributes"] ?? [];
    $fieldSelectOptions = $filter["selectOptions"] ?? [];
    $fieldOptionsRender = $filter["optionsRender"] ?? null;
    $fieldVisible       = $filter["visible"] ?? true;
    $fieldRender        = $filter["render"] ?? null;

    if (!$fieldVisible) continue;

    $attrs = [];

    foreach ($fieldAttributes as $attrName => $attrValue) :
      $attrs[] = "$attrName=\"$attrValue\"";
    endforeach;

    $attrs = implode(" ", $attrs);
  ?>
    <!-- <div class="<?= $fieldColSizes; ?>"> -->
    <div class="flex-1 w-100">
      <?php if ($fieldField !== "render") : ?>
        <div class="form-group m-0">
          <label class="form-label" for="filter-<?= $pageId; ?>-<?= $fieldName; ?>"><?= $fieldLabel; ?></label>

          <?php if ($fieldField === "select") : ?>
            <select id="filter-<?= $pageId; ?>-<?= $fieldName; ?>" class="form-control form-select" name="<?= $fieldName; ?>" <?= $attrs; ?>>
              <?php if ($fieldOptionsRender) : ?>
                <?= $fieldOptionsRender; ?>
              <?php endif; ?>

              <?php foreach ($fieldSelectOptions as $option) : ?>
                <option value="<?= $option["value"]; ?>" <?= $option["selected"] ? "selected" : ""; ?>><?= $option["label"]; ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>

          <?php if ($fieldField === "input") : ?>
            <input id="filter-<?= $fieldName; ?>" class="form-control" name="<?= $fieldName; ?>" placeholder="<?= $fieldPlaceholder; ?>" type="text">
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($fieldField === "render") : ?>
        <?= $fieldRender; ?>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>