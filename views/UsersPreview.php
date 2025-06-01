<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UsersPreview = &$Page;
?>
<script<?= Nonce() ?>>ew.deepAssign(ew.vars, { tables: { users: <?= json_encode($Page->toClientVar()) ?> } });</script>
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
		<th style="text-align: right;"><span class="ew-table-header-caption">&nbsp;</span></th>
<?php // End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php if ($Page->_UserID->Visible) { // UserID ?>
    <?php if (!$Page->_UserID->Sortable || !$Page->sortUrl($Page->_UserID)) { ?>
        <th class="<?= $Page->_UserID->headerCellClass() ?>"><?= $Page->_UserID->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->_UserID->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->_UserID->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->_UserID->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->_UserID->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->_UserID->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
    <?php if (!$Page->_Username->Sortable || !$Page->sortUrl($Page->_Username)) { ?>
        <th class="<?= $Page->_Username->headerCellClass() ?>"><?= $Page->_Username->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->_Username->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->_Username->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->_Username->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->_Username->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->_Username->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
    <?php if (!$Page->UserLevel->Sortable || !$Page->sortUrl($Page->UserLevel)) { ?>
        <th class="<?= $Page->UserLevel->headerCellClass() ?>"><?= $Page->UserLevel->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->UserLevel->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->UserLevel->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->UserLevel->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->UserLevel->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->UserLevel->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
    <?php if (!$Page->CompleteName->Sortable || !$Page->sortUrl($Page->CompleteName)) { ?>
        <th class="<?= $Page->CompleteName->headerCellClass() ?>"><?= $Page->CompleteName->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->CompleteName->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->CompleteName->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->CompleteName->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->CompleteName->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->CompleteName->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
    <?php if (!$Page->Photo->Sortable || !$Page->sortUrl($Page->Photo)) { ?>
        <th class="<?= $Page->Photo->headerCellClass() ?>"><?= $Page->Photo->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Photo->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->Photo->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Photo->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Photo->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Photo->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
    <?php if (!$Page->Gender->Sortable || !$Page->sortUrl($Page->Gender)) { ?>
        <th class="<?= $Page->Gender->headerCellClass() ?>"><?= $Page->Gender->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Gender->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->Gender->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Gender->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Gender->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Gender->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
    <?php if (!$Page->_Email->Sortable || !$Page->sortUrl($Page->_Email)) { ?>
        <th class="<?= $Page->_Email->headerCellClass() ?>"><?= $Page->_Email->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->_Email->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->_Email->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->_Email->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->_Email->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->_Email->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
    <?php if (!$Page->Activated->Sortable || !$Page->sortUrl($Page->Activated)) { ?>
        <th class="<?= $Page->Activated->headerCellClass() ?>"><?= $Page->Activated->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Activated->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->Activated->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Activated->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Activated->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Activated->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
    <?php if (!$Page->ActiveStatus->Sortable || !$Page->sortUrl($Page->ActiveStatus)) { ?>
        <th class="<?= $Page->ActiveStatus->headerCellClass() ?>"><?= $Page->ActiveStatus->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->ActiveStatus->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->ActiveStatus->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->ActiveStatus->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->ActiveStatus->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->ActiveStatus->getSortIcon() ?></span>
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
$rowNumber = $Page->StartRecord;

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
<?php if (!empty($rowNumber)) { ?>
		<td style="text-align: right;"><?php echo FormatSequenceNumber($rowNumber); ?></td>
<?php } else { ?>
		<td style="text-align: right;"></td>
<?php } ?>
<?php // End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php if ($Page->_UserID->Visible) { // UserID ?>
        <!-- UserID -->
        <td<?= $Page->_UserID->cellAttributes() ?>>
<span<?= $Page->_UserID->viewAttributes() ?>>
<?= $Page->_UserID->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
        <!-- Username -->
        <td<?= $Page->_Username->cellAttributes() ?>>
<span<?= $Page->_Username->viewAttributes() ?>>
<?= $Page->_Username->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <!-- UserLevel -->
        <td<?= $Page->UserLevel->cellAttributes() ?>>
<span<?= $Page->UserLevel->viewAttributes() ?>>
<?= $Page->UserLevel->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <!-- CompleteName -->
        <td<?= $Page->CompleteName->cellAttributes() ?>>
<span<?= $Page->CompleteName->viewAttributes() ?>>
<?= $Page->CompleteName->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
        <!-- Photo -->
        <td<?= $Page->Photo->cellAttributes() ?>>
<span>
<?= GetFileViewTag($Page->Photo, $Page->Photo->getViewValue(), false) ?>
</span>
</td>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
        <!-- Gender -->
        <td<?= $Page->Gender->cellAttributes() ?>>
<span<?= $Page->Gender->viewAttributes() ?>>
<?= $Page->Gender->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
        <!-- Email -->
        <td<?= $Page->_Email->cellAttributes() ?>>
<span<?= $Page->_Email->viewAttributes() ?>>
<?= $Page->_Email->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
        <!-- Activated -->
        <td<?= $Page->Activated->cellAttributes() ?>>
<span<?= $Page->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Page->RowCount ?>"></label>
</div>
</span>
</td>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <!-- ActiveStatus -->
        <td<?= $Page->ActiveStatus->cellAttributes() ?>>
<span<?= $Page->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Page->RowCount ?>"></label>
</div>
</span>
</td>
<?php } ?>
<?php
// Render list options (body, right)
$Page->ListOptions->render("body", "right", $Page->RowCount);
?>
    </tr>
<?php
// Begin of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023
	 $rowNumber++;

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
		<th style="text-align: right;"><span class="ew-table-header-caption">&nbsp;</span></th>
		<th style="text-align: right;"><span class="ew-table-header-caption">&nbsp;</span></th>
<?php // End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php if ($Page->_UserID->Visible) { // UserID ?>
    <?php if (!$Page->_UserID->Sortable || !$Page->sortUrl($Page->_UserID)) { ?>
        <th class="<?= $Page->_UserID->headerCellClass() ?>"><?= $Page->_UserID->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->_UserID->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->_UserID->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->_UserID->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->_UserID->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->_UserID->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
    <?php if (!$Page->_Username->Sortable || !$Page->sortUrl($Page->_Username)) { ?>
        <th class="<?= $Page->_Username->headerCellClass() ?>"><?= $Page->_Username->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->_Username->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->_Username->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->_Username->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->_Username->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->_Username->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
    <?php if (!$Page->UserLevel->Sortable || !$Page->sortUrl($Page->UserLevel)) { ?>
        <th class="<?= $Page->UserLevel->headerCellClass() ?>"><?= $Page->UserLevel->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->UserLevel->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->UserLevel->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->UserLevel->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->UserLevel->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->UserLevel->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
    <?php if (!$Page->CompleteName->Sortable || !$Page->sortUrl($Page->CompleteName)) { ?>
        <th class="<?= $Page->CompleteName->headerCellClass() ?>"><?= $Page->CompleteName->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->CompleteName->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->CompleteName->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->CompleteName->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->CompleteName->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->CompleteName->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
    <?php if (!$Page->Photo->Sortable || !$Page->sortUrl($Page->Photo)) { ?>
        <th class="<?= $Page->Photo->headerCellClass() ?>"><?= $Page->Photo->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Photo->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->Photo->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Photo->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Photo->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Photo->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
    <?php if (!$Page->Gender->Sortable || !$Page->sortUrl($Page->Gender)) { ?>
        <th class="<?= $Page->Gender->headerCellClass() ?>"><?= $Page->Gender->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Gender->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->Gender->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Gender->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Gender->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Gender->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
    <?php if (!$Page->_Email->Sortable || !$Page->sortUrl($Page->_Email)) { ?>
        <th class="<?= $Page->_Email->headerCellClass() ?>"><?= $Page->_Email->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->_Email->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->_Email->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->_Email->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->_Email->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->_Email->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
    <?php if (!$Page->Activated->Sortable || !$Page->sortUrl($Page->Activated)) { ?>
        <th class="<?= $Page->Activated->headerCellClass() ?>"><?= $Page->Activated->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->Activated->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->Activated->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->Activated->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->Activated->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->Activated->getSortIcon() ?></span>
            </div>
        </th>
    <?php } ?>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
    <?php if (!$Page->ActiveStatus->Sortable || !$Page->sortUrl($Page->ActiveStatus)) { ?>
        <th class="<?= $Page->ActiveStatus->headerCellClass() ?>"><?= $Page->ActiveStatus->caption() ?></th>
    <?php } else { ?>
        <th class="<?= $Page->ActiveStatus->headerCellClass() ?>"><div role="button" data-table="users" data-sort="<?= HtmlEncode($Page->ActiveStatus->Name) ?>" data-sort-type="1" data-sort-order="<?= $Page->ActiveStatus->getNextSort() ?>">
            <div class="ew-table-header-btn">
                <span class="ew-table-header-caption"><?= $Page->ActiveStatus->caption() ?></span>
                <span class="ew-table-header-sort"><?= $Page->ActiveStatus->getSortIcon() ?></span>
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
<?php if (!empty($rowNumber)) { ?>
		<td style="text-align: right;"><?php echo FormatSequenceNumber($rowNumber); ?></td>
<?php } else { ?>
		<td style="text-align: right;"></td>
<?php } ?>
<?php // End of Sequence Number in Preview, Added by Masino Sinaga, September 16, 2023 ?>
<?php if ($Page->_UserID->Visible) { // UserID ?>
        <!-- UserID -->
        <td<?= $Page->_UserID->cellAttributes() ?>>
<span<?= $Page->_UserID->viewAttributes() ?>>
<?= $Page->_UserID->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
        <!-- Username -->
        <td<?= $Page->_Username->cellAttributes() ?>>
<span<?= $Page->_Username->viewAttributes() ?>>
<?= $Page->_Username->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <!-- UserLevel -->
        <td<?= $Page->UserLevel->cellAttributes() ?>>
<span<?= $Page->UserLevel->viewAttributes() ?>>
<?= $Page->UserLevel->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <!-- CompleteName -->
        <td<?= $Page->CompleteName->cellAttributes() ?>>
<span<?= $Page->CompleteName->viewAttributes() ?>>
<?= $Page->CompleteName->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
        <!-- Photo -->
        <td<?= $Page->Photo->cellAttributes() ?>>
<span>
<?= GetFileViewTag($Page->Photo, $Page->Photo->getViewValue(), false) ?>
</span>
</td>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
        <!-- Gender -->
        <td<?= $Page->Gender->cellAttributes() ?>>
<span<?= $Page->Gender->viewAttributes() ?>>
<?= $Page->Gender->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
        <!-- Email -->
        <td<?= $Page->_Email->cellAttributes() ?>>
<span<?= $Page->_Email->viewAttributes() ?>>
<?= $Page->_Email->getViewValue() ?></span>
</td>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
        <!-- Activated -->
        <td<?= $Page->Activated->cellAttributes() ?>>
<span<?= $Page->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Page->RowCount ?>"></label>
</div>
</span>
</td>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <!-- ActiveStatus -->
        <td<?= $Page->ActiveStatus->cellAttributes() ?>>
<span<?= $Page->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Page->RowCount ?>"></label>
</div>
</span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fusersadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fusersadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fusersedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fusersedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
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
