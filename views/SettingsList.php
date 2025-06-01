<?php

namespace PHPMaker2025\ucarsip;

// Page object
$SettingsList = &$Page;
?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { settings: currentTable } });
var currentPageID = ew.PAGE_ID = "list";
var currentForm;
var <?= $Page->FormName ?>;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("<?= $Page->FormName ?>")
        .setPageId("list")
        .setSubmitWithFetch(<?= $Page->UseAjaxActions ? "true" : "false" ?>)
        .setFormKeyCountName("<?= $Page->getFormKeyCountName() ?>")
        .build();
    window[form.id] = form;
    currentForm = form;
    loadjs.done(form.id);
});
</script>
<script<?= Nonce() ?>>
ew.PREVIEW_SELECTOR ??= ".ew-preview-btn";
ew.PREVIEW_TYPE ??= "row";
ew.PREVIEW_NAV_STYLE ??= "tabs"; // tabs/pills/underline
ew.PREVIEW_MODAL_CLASS ??= "modal modal-fullscreen-sm-down";
ew.PREVIEW_ROW ??= true;
ew.PREVIEW_SINGLE_ROW ??= false;
ew.PREVIEW || ew.ready("head", ew.PATH_BASE + "js/preview.min.js?v=25.10.0", "preview");
</script>
<script<?= Nonce() ?>>
loadjs.ready("head", function () {
    // Write your table-specific client script here, no need to add script tags.
});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<div class="btn-toolbar ew-toolbar">
<?php if ($Page->TotalRecords > 0 && $Page->ExportOptions->visible()) { ?>
<?php $Page->ExportOptions->render("body") ?>
<?php } ?>
<?php if ($Page->ImportOptions->visible()) { ?>
<?php $Page->ImportOptions->render("body") ?>
<?php } ?>
</div>
<?php } ?>
<?php if (!$Page->IsModal) { ?>
<?php } ?>
<?php $Page->showPageHeader(); ?>
<?php
$Page->showMessage();
?>
<?php if (MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE == TRUE) { ?>
<main class="list<?= ($Page->TotalRecords == 0 && !$Page->isAdd()) ? "" : "" ?>">
<?php } else { ?>
<main class="list<?= ($Page->TotalRecords == 0 && !$Page->isAdd()) ? " ew-no-record" : "" ?>">
<?php } ?>
<div id="ew-header-options">
<?php $Page->HeaderOptions?->render("body") ?>
</div>
<div id="ew-list">
<?php if ($Page->TotalRecords > 0 || $Page->CurrentAction) { ?>
<div class="card ew-card ew-grid<?= $Page->isAddOrEdit() ? " ew-grid-add-edit" : "" ?> <?= $Page->TableGridClass ?>">
<?php if (!$Page->isExport()) { ?>
<div class="card-header ew-grid-upper-panel">
<?php if (!$Page->isGridAdd() && !($Page->isGridEdit() && $Page->ModalGridEdit) && !$Page->isMultiEdit()) { ?>
<?= $Page->Pager->render() ?>
<?php } ?>
<div class="ew-list-other-options">
<?php $Page->OtherOptions->render("body") ?>
</div>
</div>
<?php } ?>
<form name="<?= $Page->FormName ?>" id="<?= $Page->FormName ?>" class="ew-form ew-list-form" action="<?= $Page->PageAction ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="settings">
<?php if ($Page->IsModal) { ?>
<input type="hidden" name="modal" value="1">
<?php } ?>
<div id="gmp_settings" class="card-body ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>">
<?php if ($Page->TotalRecords > 0 || $Page->isGridEdit() || $Page->isMultiEdit()) { ?>
<table id="tbl_settingslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
<thead>
    <tr class="ew-table-header">
<?php
// Header row
$Page->RowType = RowType::HEADER;

// Render list options
$Page->renderListOptions();

// Render list options (header, left)
$Page->ListOptions->render("header", "left");
?>
<?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <th data-name="Option_ID" class="<?= $Page->Option_ID->headerCellClass() ?>"><div id="elh_settings_Option_ID" class="settings_Option_ID"><?= $Page->renderFieldHeader($Page->Option_ID) ?></div></th>
<?php } ?>
<?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <th data-name="Option_Default" class="<?= $Page->Option_Default->headerCellClass() ?>"><div id="elh_settings_Option_Default" class="settings_Option_Default"><?= $Page->renderFieldHeader($Page->Option_Default) ?></div></th>
<?php } ?>
<?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <th data-name="Show_Announcement" class="<?= $Page->Show_Announcement->headerCellClass() ?>"><div id="elh_settings_Show_Announcement" class="settings_Show_Announcement"><?= $Page->renderFieldHeader($Page->Show_Announcement) ?></div></th>
<?php } ?>
<?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <th data-name="Use_Announcement_Table" class="<?= $Page->Use_Announcement_Table->headerCellClass() ?>"><div id="elh_settings_Use_Announcement_Table" class="settings_Use_Announcement_Table"><?= $Page->renderFieldHeader($Page->Use_Announcement_Table) ?></div></th>
<?php } ?>
<?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <th data-name="Maintenance_Mode" class="<?= $Page->Maintenance_Mode->headerCellClass() ?>"><div id="elh_settings_Maintenance_Mode" class="settings_Maintenance_Mode"><?= $Page->renderFieldHeader($Page->Maintenance_Mode) ?></div></th>
<?php } ?>
<?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <th data-name="Maintenance_Finish_DateTime" class="<?= $Page->Maintenance_Finish_DateTime->headerCellClass() ?>"><div id="elh_settings_Maintenance_Finish_DateTime" class="settings_Maintenance_Finish_DateTime"><?= $Page->renderFieldHeader($Page->Maintenance_Finish_DateTime) ?></div></th>
<?php } ?>
<?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <th data-name="Auto_Normal_After_Maintenance" class="<?= $Page->Auto_Normal_After_Maintenance->headerCellClass() ?>"><div id="elh_settings_Auto_Normal_After_Maintenance" class="settings_Auto_Normal_After_Maintenance"><?= $Page->renderFieldHeader($Page->Auto_Normal_After_Maintenance) ?></div></th>
<?php } ?>
<?php
// Render list options (header, right)
$Page->ListOptions->render("header", "right");
?>
    </tr>
</thead>
<tbody data-page="<?= $Page->getPageNumber() ?>">
<?php
$Page->setupGrid();
$isInlineAddOrCopy = ($Page->isCopy() || $Page->isAdd());
while ($Page->RecordCount < $Page->StopRecord || $Page->RowIndex === '$rowindex$' || $isInlineAddOrCopy && $Page->RowIndex == 0) {
    if (
        $Page->CurrentRow !== false
        && $Page->RowIndex !== '$rowindex$'
        && (!$Page->isGridAdd() || $Page->CurrentMode == "copy")
        && (!($isInlineAddOrCopy && $Page->RowIndex == 0))
    ) {
        $Page->fetch();
    }
    $Page->RecordCount++;
    if ($Page->RecordCount >= $Page->StartRecord) {
        $Page->setupRow();
?>
    <tr <?= $Page->rowAttributes() ?>>
<?php
// Render list options (body, left)
$Page->ListOptions->render("body", "left", $Page->RowCount);
?>
    <?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <td data-name="Option_ID"<?= $Page->Option_ID->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Option_ID" class="el_settings_Option_ID">
<span<?= $Page->Option_ID->viewAttributes() ?>>
<?= $Page->Option_ID->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <td data-name="Option_Default"<?= $Page->Option_Default->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Option_Default" class="el_settings_Option_Default">
<span<?= $Page->Option_Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Option_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Option_Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Option_Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Option_Default_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <td data-name="Show_Announcement"<?= $Page->Show_Announcement->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Show_Announcement" class="el_settings_Show_Announcement">
<span<?= $Page->Show_Announcement->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Show_Announcement_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Show_Announcement->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Show_Announcement->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Show_Announcement_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <td data-name="Use_Announcement_Table"<?= $Page->Use_Announcement_Table->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Use_Announcement_Table" class="el_settings_Use_Announcement_Table">
<span<?= $Page->Use_Announcement_Table->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Use_Announcement_Table_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Use_Announcement_Table->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Use_Announcement_Table->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Use_Announcement_Table_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <td data-name="Maintenance_Mode"<?= $Page->Maintenance_Mode->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Maintenance_Mode" class="el_settings_Maintenance_Mode">
<span<?= $Page->Maintenance_Mode->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Maintenance_Mode_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Maintenance_Mode->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Maintenance_Mode->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Maintenance_Mode_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <td data-name="Maintenance_Finish_DateTime"<?= $Page->Maintenance_Finish_DateTime->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Maintenance_Finish_DateTime" class="el_settings_Maintenance_Finish_DateTime">
<span<?= $Page->Maintenance_Finish_DateTime->viewAttributes() ?>>
<?= $Page->Maintenance_Finish_DateTime->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <td data-name="Auto_Normal_After_Maintenance"<?= $Page->Auto_Normal_After_Maintenance->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Auto_Normal_After_Maintenance" class="el_settings_Auto_Normal_After_Maintenance">
<span<?= $Page->Auto_Normal_After_Maintenance->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Auto_Normal_After_Maintenance->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Auto_Normal_After_Maintenance->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
<?php
// Render list options (body, right)
$Page->ListOptions->render("body", "right", $Page->RowCount);
?>
    </tr>
<?php
    }

    // Reset for template row
    if ($Page->RowIndex === '$rowindex$') {
        $Page->RowIndex = 0;
    }
    // Reset inline add/copy row
    if (($Page->isCopy() || $Page->isAdd()) && $Page->RowIndex == 0) {
        $Page->RowIndex = 1;
    }
}
?>
</tbody>
</table><!-- /.ew-table -->
<?php // Begin of Empty Table by Masino Sinaga, September 10, 2023 ?>
<?php } else { ?>
<?php if (MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE == TRUE) { // --- Begin of if MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE ?>
<table id="tbl_settingslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
<thead>
    <tr class="ew-table-header">
<?php
// Header row
$Page->RowType = RowType::HEADER;

// Render list options
// $Page->renderListOptions(); // do not display for empty table, by Masino Sinaga, September 10, 2023

// Render list options (header, left)
$Page->ListOptions->render("header", "left");
?>
<?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <th data-name="Option_ID" class="<?= $Page->Option_ID->headerCellClass() ?>"><div id="elh_settings_Option_ID" class="settings_Option_ID"><?= $Page->renderFieldHeader($Page->Option_ID) ?></div></th>
<?php } ?>
<?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <th data-name="Option_Default" class="<?= $Page->Option_Default->headerCellClass() ?>"><div id="elh_settings_Option_Default" class="settings_Option_Default"><?= $Page->renderFieldHeader($Page->Option_Default) ?></div></th>
<?php } ?>
<?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <th data-name="Show_Announcement" class="<?= $Page->Show_Announcement->headerCellClass() ?>"><div id="elh_settings_Show_Announcement" class="settings_Show_Announcement"><?= $Page->renderFieldHeader($Page->Show_Announcement) ?></div></th>
<?php } ?>
<?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <th data-name="Use_Announcement_Table" class="<?= $Page->Use_Announcement_Table->headerCellClass() ?>"><div id="elh_settings_Use_Announcement_Table" class="settings_Use_Announcement_Table"><?= $Page->renderFieldHeader($Page->Use_Announcement_Table) ?></div></th>
<?php } ?>
<?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <th data-name="Maintenance_Mode" class="<?= $Page->Maintenance_Mode->headerCellClass() ?>"><div id="elh_settings_Maintenance_Mode" class="settings_Maintenance_Mode"><?= $Page->renderFieldHeader($Page->Maintenance_Mode) ?></div></th>
<?php } ?>
<?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <th data-name="Maintenance_Finish_DateTime" class="<?= $Page->Maintenance_Finish_DateTime->headerCellClass() ?>"><div id="elh_settings_Maintenance_Finish_DateTime" class="settings_Maintenance_Finish_DateTime"><?= $Page->renderFieldHeader($Page->Maintenance_Finish_DateTime) ?></div></th>
<?php } ?>
<?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <th data-name="Auto_Normal_After_Maintenance" class="<?= $Page->Auto_Normal_After_Maintenance->headerCellClass() ?>"><div id="elh_settings_Auto_Normal_After_Maintenance" class="settings_Auto_Normal_After_Maintenance"><?= $Page->renderFieldHeader($Page->Auto_Normal_After_Maintenance) ?></div></th>
<?php } ?>
<?php
// Render list options (header, right)
$Page->ListOptions->render("header", "right");
?>
    </tr>
</thead>
<tbody data-page="<?= $Page->getPageNumber() ?>">
    <tr class="border-bottom-0" style="height:36px;">
<?php
// Render list options (body, left)
$Page->ListOptions->render("body", "left", $Page->RowCount);
?>
    <?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <td data-name="Option_ID"<?= $Page->Option_ID->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Option_ID" class="el_settings_Option_ID">
<span<?= $Page->Option_ID->viewAttributes() ?>>
<?= $Page->Option_ID->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <td data-name="Option_Default"<?= $Page->Option_Default->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Option_Default" class="el_settings_Option_Default">
<span<?= $Page->Option_Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Option_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Option_Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Option_Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Option_Default_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <td data-name="Show_Announcement"<?= $Page->Show_Announcement->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Show_Announcement" class="el_settings_Show_Announcement">
<span<?= $Page->Show_Announcement->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Show_Announcement_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Show_Announcement->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Show_Announcement->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Show_Announcement_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <td data-name="Use_Announcement_Table"<?= $Page->Use_Announcement_Table->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Use_Announcement_Table" class="el_settings_Use_Announcement_Table">
<span<?= $Page->Use_Announcement_Table->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Use_Announcement_Table_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Use_Announcement_Table->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Use_Announcement_Table->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Use_Announcement_Table_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <td data-name="Maintenance_Mode"<?= $Page->Maintenance_Mode->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Maintenance_Mode" class="el_settings_Maintenance_Mode">
<span<?= $Page->Maintenance_Mode->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Maintenance_Mode_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Maintenance_Mode->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Maintenance_Mode->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Maintenance_Mode_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <td data-name="Maintenance_Finish_DateTime"<?= $Page->Maintenance_Finish_DateTime->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Maintenance_Finish_DateTime" class="el_settings_Maintenance_Finish_DateTime">
<span<?= $Page->Maintenance_Finish_DateTime->viewAttributes() ?>>
<?= $Page->Maintenance_Finish_DateTime->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <td data-name="Auto_Normal_After_Maintenance"<?= $Page->Auto_Normal_After_Maintenance->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Auto_Normal_After_Maintenance" class="el_settings_Auto_Normal_After_Maintenance">
<span<?= $Page->Auto_Normal_After_Maintenance->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Auto_Normal_After_Maintenance->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Auto_Normal_After_Maintenance->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
<?php
// Render list options (body, right)
$Page->ListOptions->render("body", "right", $Page->RowCount);
?>
    </tr>
</tbody>
</table><!-- /.ew-table -->
<?php } // --- End of if MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE ?>
<?php // End of Empty Table by Masino Sinaga, September 10, 2023 ?>
<?php } ?>
</div><!-- /.ew-grid-middle-panel -->
<?php if (!$Page->CurrentAction && !$Page->UseAjaxActions) { ?>
<input type="hidden" name="action" id="action" value="">
<?php } ?>
</form><!-- /.ew-list-form -->
<?php
// Close result set
$Page->Result?->free();
?>
<?php if (!$Page->isExport()) { ?>
<div class="card-footer ew-grid-lower-panel">
<?php if (!$Page->isGridAdd() && !($Page->isGridEdit() && $Page->ModalGridEdit) && !$Page->isMultiEdit()) { ?>
<?= $Page->Pager->render() ?>
<?php } ?>
<div class="ew-list-other-options">
<?php $Page->OtherOptions->render("body", "bottom") ?>
</div>
</div>
<?php } ?>
</div><!-- /.ew-grid -->
<?php } ?>
<?php if ($Page->TotalRecords == 0 && !$Page->CurrentAction) { // Show other options ?>
<?php // Begin of Empty Table by Masino Sinaga, September 30, 2020 ?>
<?php if (MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE == TRUE) { ?>
<div class="card ew-card ew-grid<?= $Page->isAddOrEdit() ? " ew-grid-add-edit" : "" ?> <?= $Page->TableGridClass ?>">
<?php if (!$Page->isExport()) { ?>
<div class="card-header ew-grid-upper-panel">
<?php if (!$Page->isGridAdd() && !($Page->isGridEdit() && $Page->ModalGridEdit) && !$Page->isMultiEdit()) { ?>
<?= $Page->Pager->render() ?>
<?php } ?>
<div class="ew-list-other-options">
<?php $Page->OtherOptions->render("body") ?>
</div>
</div>
<?php } ?>
<form name="<?= $Page->FormName ?>" id="<?= $Page->FormName ?>" class="ew-form ew-list-form" action="<?= $Page->PageAction ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="settings">
<?php if ($Page->IsModal) { ?>
<input type="hidden" name="modal" value="1">
<?php } ?>
<div id="gmp_settings" class="card-body ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>">
<?php if ($Page->TotalRecords > 0 || $Page->isGridEdit() || $Page->isMultiEdit()) { ?>
<table id="tbl_settingslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
<thead>
    <tr class="ew-table-header">
<?php
// Header row
$Page->RowType = RowType::HEADER;

// Render list options
$Page->renderListOptions();

// Render list options (header, left)
$Page->ListOptions->render("header", "left");
?>
<?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <th data-name="Option_ID" class="<?= $Page->Option_ID->headerCellClass() ?>"><div id="elh_settings_Option_ID" class="settings_Option_ID"><?= $Page->renderFieldHeader($Page->Option_ID) ?></div></th>
<?php } ?>
<?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <th data-name="Option_Default" class="<?= $Page->Option_Default->headerCellClass() ?>"><div id="elh_settings_Option_Default" class="settings_Option_Default"><?= $Page->renderFieldHeader($Page->Option_Default) ?></div></th>
<?php } ?>
<?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <th data-name="Show_Announcement" class="<?= $Page->Show_Announcement->headerCellClass() ?>"><div id="elh_settings_Show_Announcement" class="settings_Show_Announcement"><?= $Page->renderFieldHeader($Page->Show_Announcement) ?></div></th>
<?php } ?>
<?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <th data-name="Use_Announcement_Table" class="<?= $Page->Use_Announcement_Table->headerCellClass() ?>"><div id="elh_settings_Use_Announcement_Table" class="settings_Use_Announcement_Table"><?= $Page->renderFieldHeader($Page->Use_Announcement_Table) ?></div></th>
<?php } ?>
<?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <th data-name="Maintenance_Mode" class="<?= $Page->Maintenance_Mode->headerCellClass() ?>"><div id="elh_settings_Maintenance_Mode" class="settings_Maintenance_Mode"><?= $Page->renderFieldHeader($Page->Maintenance_Mode) ?></div></th>
<?php } ?>
<?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <th data-name="Maintenance_Finish_DateTime" class="<?= $Page->Maintenance_Finish_DateTime->headerCellClass() ?>"><div id="elh_settings_Maintenance_Finish_DateTime" class="settings_Maintenance_Finish_DateTime"><?= $Page->renderFieldHeader($Page->Maintenance_Finish_DateTime) ?></div></th>
<?php } ?>
<?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <th data-name="Auto_Normal_After_Maintenance" class="<?= $Page->Auto_Normal_After_Maintenance->headerCellClass() ?>"><div id="elh_settings_Auto_Normal_After_Maintenance" class="settings_Auto_Normal_After_Maintenance"><?= $Page->renderFieldHeader($Page->Auto_Normal_After_Maintenance) ?></div></th>
<?php } ?>
<?php
// Render list options (header, right)
$Page->ListOptions->render("header", "right");
?>
    </tr>
</thead>
<tbody data-page="<?= $Page->getPageNumber() ?>">
<?php
$Page->setupGrid();
$isInlineAddOrCopy = ($Page->isCopy() || $Page->isAdd());
while ($Page->RecordCount < $Page->StopRecord || $Page->RowIndex === '$rowindex$' || $isInlineAddOrCopy && $Page->RowIndex == 0) {
    if (
        $Page->CurrentRow !== false
        && $Page->RowIndex !== '$rowindex$'
        && (!$Page->isGridAdd() || $Page->CurrentMode == "copy")
        && (!($isInlineAddOrCopy && $Page->RowIndex == 0))
    ) {
        $Page->fetch();
    }
    $Page->RecordCount++;
    if ($Page->RecordCount >= $Page->StartRecord) {
        $Page->setupRow();
?>
    <tr <?= $Page->rowAttributes() ?>>
<?php
// Render list options (body, left)
$Page->ListOptions->render("body", "left", $Page->RowCount);
?>
    <?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <td data-name="Option_ID"<?= $Page->Option_ID->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Option_ID" class="el_settings_Option_ID">
<span<?= $Page->Option_ID->viewAttributes() ?>>
<?= $Page->Option_ID->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <td data-name="Option_Default"<?= $Page->Option_Default->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Option_Default" class="el_settings_Option_Default">
<span<?= $Page->Option_Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Option_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Option_Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Option_Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Option_Default_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <td data-name="Show_Announcement"<?= $Page->Show_Announcement->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Show_Announcement" class="el_settings_Show_Announcement">
<span<?= $Page->Show_Announcement->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Show_Announcement_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Show_Announcement->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Show_Announcement->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Show_Announcement_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <td data-name="Use_Announcement_Table"<?= $Page->Use_Announcement_Table->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Use_Announcement_Table" class="el_settings_Use_Announcement_Table">
<span<?= $Page->Use_Announcement_Table->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Use_Announcement_Table_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Use_Announcement_Table->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Use_Announcement_Table->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Use_Announcement_Table_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <td data-name="Maintenance_Mode"<?= $Page->Maintenance_Mode->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Maintenance_Mode" class="el_settings_Maintenance_Mode">
<span<?= $Page->Maintenance_Mode->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Maintenance_Mode_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Maintenance_Mode->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Maintenance_Mode->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Maintenance_Mode_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <td data-name="Maintenance_Finish_DateTime"<?= $Page->Maintenance_Finish_DateTime->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Maintenance_Finish_DateTime" class="el_settings_Maintenance_Finish_DateTime">
<span<?= $Page->Maintenance_Finish_DateTime->viewAttributes() ?>>
<?= $Page->Maintenance_Finish_DateTime->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <td data-name="Auto_Normal_After_Maintenance"<?= $Page->Auto_Normal_After_Maintenance->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Auto_Normal_After_Maintenance" class="el_settings_Auto_Normal_After_Maintenance">
<span<?= $Page->Auto_Normal_After_Maintenance->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Auto_Normal_After_Maintenance->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Auto_Normal_After_Maintenance->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
<?php
// Render list options (body, right)
$Page->ListOptions->render("body", "right", $Page->RowCount);
?>
    </tr>
<?php
    }

    // Reset for template row
    if ($Page->RowIndex === '$rowindex$') {
        $Page->RowIndex = 0;
    }
    // Reset inline add/copy row
    if (($Page->isCopy() || $Page->isAdd()) && $Page->RowIndex == 0) {
        $Page->RowIndex = 1;
    }
}
?>
</tbody>
</table><!-- /.ew-table -->
<?php // Begin of Empty Table by Masino Sinaga, September 10, 2023 ?>
<?php } else { ?>
<?php if (MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE == TRUE) { // --- Begin of if MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE ?>
<table id="tbl_settingslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
<thead>
    <tr class="ew-table-header">
<?php
// Header row
$Page->RowType = RowType::HEADER;

// Render list options
// $Page->renderListOptions(); // do not display for empty table, by Masino Sinaga, September 10, 2023

// Render list options (header, left)
$Page->ListOptions->render("header", "left");
?>
<?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <th data-name="Option_ID" class="<?= $Page->Option_ID->headerCellClass() ?>"><div id="elh_settings_Option_ID" class="settings_Option_ID"><?= $Page->renderFieldHeader($Page->Option_ID) ?></div></th>
<?php } ?>
<?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <th data-name="Option_Default" class="<?= $Page->Option_Default->headerCellClass() ?>"><div id="elh_settings_Option_Default" class="settings_Option_Default"><?= $Page->renderFieldHeader($Page->Option_Default) ?></div></th>
<?php } ?>
<?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <th data-name="Show_Announcement" class="<?= $Page->Show_Announcement->headerCellClass() ?>"><div id="elh_settings_Show_Announcement" class="settings_Show_Announcement"><?= $Page->renderFieldHeader($Page->Show_Announcement) ?></div></th>
<?php } ?>
<?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <th data-name="Use_Announcement_Table" class="<?= $Page->Use_Announcement_Table->headerCellClass() ?>"><div id="elh_settings_Use_Announcement_Table" class="settings_Use_Announcement_Table"><?= $Page->renderFieldHeader($Page->Use_Announcement_Table) ?></div></th>
<?php } ?>
<?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <th data-name="Maintenance_Mode" class="<?= $Page->Maintenance_Mode->headerCellClass() ?>"><div id="elh_settings_Maintenance_Mode" class="settings_Maintenance_Mode"><?= $Page->renderFieldHeader($Page->Maintenance_Mode) ?></div></th>
<?php } ?>
<?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <th data-name="Maintenance_Finish_DateTime" class="<?= $Page->Maintenance_Finish_DateTime->headerCellClass() ?>"><div id="elh_settings_Maintenance_Finish_DateTime" class="settings_Maintenance_Finish_DateTime"><?= $Page->renderFieldHeader($Page->Maintenance_Finish_DateTime) ?></div></th>
<?php } ?>
<?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <th data-name="Auto_Normal_After_Maintenance" class="<?= $Page->Auto_Normal_After_Maintenance->headerCellClass() ?>"><div id="elh_settings_Auto_Normal_After_Maintenance" class="settings_Auto_Normal_After_Maintenance"><?= $Page->renderFieldHeader($Page->Auto_Normal_After_Maintenance) ?></div></th>
<?php } ?>
<?php
// Render list options (header, right)
$Page->ListOptions->render("header", "right");
?>
    </tr>
</thead>
<tbody data-page="<?= $Page->getPageNumber() ?>">
    <tr class="border-bottom-0" style="height:36px;">
<?php
// Render list options (body, left)
$Page->ListOptions->render("body", "left", $Page->RowCount);
?>
    <?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <td data-name="Option_ID"<?= $Page->Option_ID->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Option_ID" class="el_settings_Option_ID">
<span<?= $Page->Option_ID->viewAttributes() ?>>
<?= $Page->Option_ID->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <td data-name="Option_Default"<?= $Page->Option_Default->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Option_Default" class="el_settings_Option_Default">
<span<?= $Page->Option_Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Option_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Option_Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Option_Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Option_Default_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <td data-name="Show_Announcement"<?= $Page->Show_Announcement->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Show_Announcement" class="el_settings_Show_Announcement">
<span<?= $Page->Show_Announcement->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Show_Announcement_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Show_Announcement->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Show_Announcement->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Show_Announcement_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <td data-name="Use_Announcement_Table"<?= $Page->Use_Announcement_Table->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Use_Announcement_Table" class="el_settings_Use_Announcement_Table">
<span<?= $Page->Use_Announcement_Table->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Use_Announcement_Table_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Use_Announcement_Table->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Use_Announcement_Table->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Use_Announcement_Table_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <td data-name="Maintenance_Mode"<?= $Page->Maintenance_Mode->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Maintenance_Mode" class="el_settings_Maintenance_Mode">
<span<?= $Page->Maintenance_Mode->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Maintenance_Mode_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Maintenance_Mode->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Maintenance_Mode->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Maintenance_Mode_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <td data-name="Maintenance_Finish_DateTime"<?= $Page->Maintenance_Finish_DateTime->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Maintenance_Finish_DateTime" class="el_settings_Maintenance_Finish_DateTime">
<span<?= $Page->Maintenance_Finish_DateTime->viewAttributes() ?>>
<?= $Page->Maintenance_Finish_DateTime->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <td data-name="Auto_Normal_After_Maintenance"<?= $Page->Auto_Normal_After_Maintenance->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_settings_Auto_Normal_After_Maintenance" class="el_settings_Auto_Normal_After_Maintenance">
<span<?= $Page->Auto_Normal_After_Maintenance->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Auto_Normal_After_Maintenance->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Auto_Normal_After_Maintenance->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
<?php
// Render list options (body, right)
$Page->ListOptions->render("body", "right", $Page->RowCount);
?>
    </tr>
</tbody>
</table><!-- /.ew-table -->
<?php } // --- End of if MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE ?>
<?php // End of Empty Table by Masino Sinaga, September 10, 2023 ?>
<?php } ?>
</div><!-- /.ew-grid-middle-panel -->
<?php if (!$Page->CurrentAction && !$Page->UseAjaxActions) { ?>
<input type="hidden" name="action" id="action" value="">
<?php } ?>
</form><!-- /.ew-list-form -->
<?php
// Close result set
$Page->Result?->free();
?>
<?php if (!$Page->isExport()) { ?>
<div class="card-footer ew-grid-lower-panel">
<?php if (!$Page->isGridAdd() && !($Page->isGridEdit() && $Page->ModalGridEdit) && !$Page->isMultiEdit()) { ?>
<?= $Page->Pager->render() ?>
<?php } ?>
<div class="ew-list-other-options">
<?php $Page->OtherOptions->render("body", "bottom") ?>
</div>
</div>
<?php } ?>
</div><!-- /.ew-grid -->
<?php } else { ?>
<div class="ew-list-other-options">
<?php $Page->OtherOptions->render("body") ?>
</div>
<div class="clearfix"></div>
<?php } // end of Empty Table by Masino Sinaga, September 30, 2020 ?>
<?php } ?>
</div>
<div id="ew-footer-options">
<?php $Page->FooterOptions?->render("body") ?>
</div>
</main>
<?php
$Page->showPageFooter();
?>
<?php if (!$Page->isExport()) { ?>
<script>
loadjs.ready("head", function() {
	$(".ew-grid").css("width", "100%");
	$(".sidebar, .main-sidebar, .main-header, .header-navbar, .main-menu").on("mouseenter", function(event) {
		$(".ew-grid").css("width", "100%");
	});
	$(".sidebar, .main-sidebar, .main-header, .header-navbar, .main-menu").on("mouseover", function(event) {
		$(".ew-grid").css("width", "100%");
	});
	var cssTransitionEnd = 'webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend';
	$('.main-header').on(cssTransitionEnd, function(event) {
		$(".ew-grid").css("width", "100%");
	});
	$(document).on('resize', function() {
		if ($('.ew-grid').length > 0) {
			$(".ew-grid").css("width", "100%");
		}
	});
	$(".nav-item.d-block").on("click", function(event) {
		$(".ew-grid").css("width", "100%");
	});
});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fsettingsadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fsettingsadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fsettingsedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fsettingsedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fsettingsupdate.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fsettingsupdate").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fsettingsdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fsettingsdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport() && CurrentPageID()=="list") { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-grid-save, .ew-grid-insert').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fsettingslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-inline-update').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fsettingslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-inline-insert').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fsettingslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){var gridchange=false;$('[data-table="settings"]').change(function(){	gridchange=true;});$('.ew-grid-cancel,.ew-inline-cancel').click(function(){if (gridchange==true){ew.prompt({title: ew.language.phrase("ConfirmCancel"),icon:'question',showCancelButton:true},result=>{if(result) window.location = "<?php echo str_replace('_', '', 'settingslist'); ?>";});return false;}});});
</script>
<?php } ?>
<?php if (!$settings->isExport()) { ?>
<script>
loadjs.ready("jscookie", function() {
	var expires = new Date(new Date().getTime() + 525600 * 60 * 1000); // expire in 525600 
	var SearchToggle = $('.ew-search-toggle');
	SearchToggle.on('click', function(event) { 
		event.preventDefault(); 
		if (SearchToggle.hasClass('active')) { 
			ew.Cookies.set(ew.PROJECT_NAME + "_settings_searchpanel", "notactive", {
			  sameSite: ew.COOKIE_SAMESITE,
			  secure: ew.COOKIE_SECURE
			}); 
		} else { 
			ew.Cookies.set(ew.PROJECT_NAME + "_settings_searchpanel", "active", {
			  sameSite: ew.COOKIE_SAMESITE,
			  secure: ew.COOKIE_SECURE
			}); 
		} 
	});
});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("settings");
});
</script>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
