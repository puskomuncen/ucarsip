<?php

namespace PHPMaker2025\ucarsip;

// Page object
$HelpPreview = &$Page;
?>
<script<?= Nonce() ?>>ew.deepAssign(ew.vars, { tables: { help: <?= json_encode($Page->toClientVar()) ?> } });</script>
<script<?= Nonce() ?>>
loadjs.ready("head", function () {
    // Write your table-specific client script here, no need to add script tags.
});
</script>
<?php $Page->showPageHeader(); ?>
<?php if ($Page->TotalRecords > 0) { ?>
<?php // Begin of modification by Masino Sinaga, October 14, 2024 ?>
<div class="card ew-grid <?= $Page->TableGridClass ?>" style="width: 100%;"><!-- .card -->
<?php // End of modification by Masino Sinaga, October 14, 2024 ?>
<div class="card-header ew-grid-upper-panel ew-preview-upper-panel"><!-- .card-header -->
<?= $Page->Pager->render() ?>
<?php if ($Page->OtherOptions->visible()) { ?>
<div class="ew-preview-other-options">
<?php
    foreach ($Page->OtherOptions as $option) {
        $option->render("body");
    }
?>
</div>
<?php } ?>
</div><!-- /.card-header -->
<div class="card-body ew-preview-middle-panel ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>"><!-- .card-body -->
<table class="<?= $Page->TableClass ?>"><!-- .table -->
    <thead><!-- Table header -->
        <tr class="ew-table-header">
<?php
// Render list options
$Page->renderListOptions();

// Render list options (header, left)
$Page->ListOptions->render("header", "left");
?>
<?php // Begin of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php // End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php if ($Page->_Language->Visible) { // Language ?>
    <?php if (!$Page->_Language->Sortable || !$Page->sortUrl($Page->_Language)) { ?>
        <th class="<?= $Page->_Language->headerCellClass() ?>"><?= $Page->_Language->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->_Language->headerCellClass() ?>"><div role="button" data-table="help" data-sort="<?= HtmlEncode($Page->_Language->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->_Language->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->_Language->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->_Language->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
    <?php if (!$Page->Topic->Sortable || !$Page->sortUrl($Page->Topic)) { ?>
        <th class="<?= $Page->Topic->headerCellClass() ?>"><?= $Page->Topic->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Topic->headerCellClass() ?>"><div role="button" data-table="help" data-sort="<?= HtmlEncode($Page->Topic->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Topic->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Topic->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Topic->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Description->Visible) { // Description ?>
    <?php if (!$Page->Description->Sortable || !$Page->sortUrl($Page->Description)) { ?>
        <th class="<?= $Page->Description->headerCellClass() ?>"><?= $Page->Description->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Description->headerCellClass() ?>"><div role="button" data-table="help" data-sort="<?= HtmlEncode($Page->Description->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Description->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Description->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Description->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Category->Visible) { // Category ?>
    <?php if (!$Page->Category->Sortable || !$Page->sortUrl($Page->Category)) { ?>
        <th class="<?= $Page->Category->headerCellClass() ?>"><?= $Page->Category->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Category->headerCellClass() ?>"><div role="button" data-table="help" data-sort="<?= HtmlEncode($Page->Category->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Category->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Category->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Category->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php
// Render list options (header, right)
$Page->ListOptions->render("header", "right");
?>
        </tr>
    </thead>
    <tbody><!-- Table body -->
<?php
$Page->RecordCount = 0;
$Page->RowCount = 0;
// Begin of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023

// End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023
while ($Page->fetch()) {
    // Init row class and style
    $Page->RecordCount++;
    $Page->RowCount++;
    $Page->CssStyle = "";
    $Page->loadListRowValues($Page->CurrentRow);

    // Render row
    $Page->RowType = RowType::PREVIEW; // Preview record
    $Page->resetAttributes();
    $Page->renderListRow();

    // Set up row attributes
    $Page->RowAttrs->merge([
        "data-rowindex" => $Page->RowCount,
        "class" => ($Page->RowCount % 2 != 1) ? "ew-table-alt-row" : "",

        // Add row attributes for expandable row
        "data-widget" => "expandable-table",
        "aria-expanded" => "false",
    ]);

    // Render list options
    $Page->renderListOptions();
?>
    <tr <?= $Page->rowAttributes() ?>>
<?php
// Render list options (body, left)
$Page->ListOptions->render("body", "left", $Page->RowCount);
?>
<?php // Begin of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php // End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php if ($Page->_Language->Visible) { // Language ?>
        <!-- Language -->
        <td<?= $Page->_Language->cellAttributes() ?>>
<span<?= $Page->_Language->viewAttributes() ?>>
<?= $Page->_Language->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
        <!-- Topic -->
        <td<?= $Page->Topic->cellAttributes() ?>>
<span<?= $Page->Topic->viewAttributes() ?>>
<?= $Page->Topic->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Description->Visible) { // Description ?>
        <!-- Description -->
        <td<?= $Page->Description->cellAttributes() ?>>
<span<?= $Page->Description->viewAttributes() ?>>
<?= $Page->Description->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Category->Visible) { // Category ?>
        <!-- Category -->
        <td<?= $Page->Category->cellAttributes() ?>>
<span<?= $Page->Category->viewAttributes() ?>>
<?= $Page->Category->getViewValue() ?></span>
</td>
<?php } ?>
<?php
// Render list options (body, right)
$Page->ListOptions->render("body", "right", $Page->RowCount);
?>
    </tr>
<?php
// Begin of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023

// End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023
} // while
?>
    </tbody>
</table><!-- /.table -->
</div><!-- /.card-body -->
<div class="card-footer ew-grid-lower-panel ew-preview-lower-panel"><!-- .card-footer -->
<?= $Page->Pager->render() ?>
<?php if ($Page->OtherOptions->visible()) { ?>
<div class="ew-preview-other-options">
<?php
    foreach ($Page->OtherOptions as $option) {
        $option->render("body");
    }
?>
</div>
<?php } ?>
</div><!-- /.card-footer -->
</div><!-- /.card -->
<?php } else { // No record ?>
<?php /////////// Begin of Empty Table in Preview Page by Masino Sinaga, September 15, 2023 ////////// ?>
<?php if (MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE == TRUE) { ?>
<?php // BEGIN OF EMPTY TABLE CODE ?>
<?php // Begin of modification by Masino Sinaga, October 14, 2024 ?>
<div class="card ew-grid <?= $Page->TableGridClass ?>" style="width: 100%;"><!-- .card -->
<?php // End of modification by Masino Sinaga, October 14, 2024 ?>
<div class="card-header ew-grid-upper-panel ew-preview-upper-panel"><!-- .card-header -->
<?= $Page->Pager->render() ?>
<?php if ($Page->OtherOptions->visible()) { ?>
<div class="ew-preview-other-options">
<?php
    foreach ($Page->OtherOptions as $option) {
        $option->render("body");
    }
?>
</div>
<?php } ?>
</div><!-- /.card-header -->
<div class="card-body ew-preview-middle-panel ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>"><!-- .card-body -->
<table class="<?= $Page->TableClass ?>"><!-- .table -->
    <thead><!-- Table header -->
        <tr class="ew-table-header">
<?php
// Render list options
// $Page->renderListOptions();

// Render list options (header, left)
// $Page->ListOptions->render("header", "left");
?>
<?php // Begin of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php // End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php if ($Page->_Language->Visible) { // Language ?>
    <?php if (!$Page->_Language->Sortable || !$Page->sortUrl($Page->_Language)) { ?>
        <th class="<?= $Page->_Language->headerCellClass() ?>"><?= $Page->_Language->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->_Language->headerCellClass() ?>"><div role="button" data-table="help" data-sort="<?= HtmlEncode($Page->_Language->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->_Language->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->_Language->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->_Language->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
    <?php if (!$Page->Topic->Sortable || !$Page->sortUrl($Page->Topic)) { ?>
        <th class="<?= $Page->Topic->headerCellClass() ?>"><?= $Page->Topic->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Topic->headerCellClass() ?>"><div role="button" data-table="help" data-sort="<?= HtmlEncode($Page->Topic->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Topic->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Topic->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Topic->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Description->Visible) { // Description ?>
    <?php if (!$Page->Description->Sortable || !$Page->sortUrl($Page->Description)) { ?>
        <th class="<?= $Page->Description->headerCellClass() ?>"><?= $Page->Description->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Description->headerCellClass() ?>"><div role="button" data-table="help" data-sort="<?= HtmlEncode($Page->Description->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Description->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Description->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Description->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Category->Visible) { // Category ?>
    <?php if (!$Page->Category->Sortable || !$Page->sortUrl($Page->Category)) { ?>
        <th class="<?= $Page->Category->headerCellClass() ?>"><?= $Page->Category->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Category->headerCellClass() ?>"><div role="button" data-table="help" data-sort="<?= HtmlEncode($Page->Category->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Category->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Category->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Category->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php
// Render list options (header, right)
$Page->ListOptions->render("header", "right");
?>
        </tr>
    </thead>
    <tbody><!-- Table body -->
	<tr class="border-bottom-0" style="height:36px;">
<?php
// Render list options (body, left)
$Page->ListOptions->render("body", "left", $Page->RowCount);
?>
<?php // Begin of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php // End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php if ($Page->_Language->Visible) { // Language ?>
        <!-- Language -->
        <td<?= $Page->_Language->cellAttributes() ?>>
<span<?= $Page->_Language->viewAttributes() ?>>
<?= $Page->_Language->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
        <!-- Topic -->
        <td<?= $Page->Topic->cellAttributes() ?>>
<span<?= $Page->Topic->viewAttributes() ?>>
<?= $Page->Topic->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Description->Visible) { // Description ?>
        <!-- Description -->
        <td<?= $Page->Description->cellAttributes() ?>>
<span<?= $Page->Description->viewAttributes() ?>>
<?= $Page->Description->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Category->Visible) { // Category ?>
        <!-- Category -->
        <td<?= $Page->Category->cellAttributes() ?>>
<span<?= $Page->Category->viewAttributes() ?>>
<?= $Page->Category->getViewValue() ?></span>
</td>
<?php } ?>
<?php
// Render list options (body, right)
$Page->ListOptions->render("body", "right", $Page->RowCount);
?>
    </tr>
    </tbody>
</table><!-- /.table -->
</div><!-- /.card-body -->
<div class="card-footer ew-grid-lower-panel ew-preview-lower-panel"><!-- .card-footer -->
<?= $Page->Pager->render() ?>
<?php if ($Page->OtherOptions->visible()) { ?>
<div class="ew-preview-other-options">
<?php
    foreach ($Page->OtherOptions as $option) {
        $option->render("body");
    }
?>
</div>
<?php } ?>
</div><!-- /.card-footer -->
</div><!-- /.card -->
<?php // END OF EMPTY TABLE CODE ?>
<?php } else { // Else of MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE ?>
<div class="card border-0"><!-- .card -->
<div class="ew-detail-count"><?= $Language->phrase("NoRecord") ?></div>
<?php if ($Page->OtherOptions->visible()) { ?>
<div class="ew-preview-other-options">
<?php
    foreach ($Page->OtherOptions as $option) {
        $option->render("body");
    }
?>
</div>
<?php } ?>
</div><!-- /.card -->
<?php } ///////// End of Empty Table in Preview Page by Masino Sinaga, September 15, 2023  ?>
<?php } ?>
<?php
foreach ($Page->DetailCounts as $detailTblVar => $detailCount) {
?>
<div class="ew-detail-count d-none" data-table="<?= $detailTblVar ?>" data-count="<?= $detailCount ?>"><?= FormatInteger($detailCount) ?></div>
<?php
}
?>
<?php
$Page->showPageFooter();
?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fhelpadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fhelpadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fhelpedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fhelpedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php
$Page->Result?->free();
?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
