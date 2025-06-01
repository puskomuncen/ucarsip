<?php

namespace PHPMaker2025\ucarsip;

// Page object
$LanguagesList = &$Page;
?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { languages: currentTable } });
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
<?php if ($Page->SearchOptions->visible()) { ?>
<?php $Page->SearchOptions->render("body") ?>
<?php } ?>
<?php if ($Page->FilterOptions->visible()) { ?>
<?php $Page->FilterOptions->render("body") ?>
<?php } ?>
</div>
<?php } ?>
<?php if (!$Page->IsModal) { ?>
<form name="flanguagessrch" id="flanguagessrch" class="ew-form ew-ext-search-form" action="<?= CurrentPageUrl(false) ?>" novalidate autocomplete="off">
<div id="flanguagessrch_search_panel" class="mb-2 mb-sm-0 <?= $Page->SearchPanelClass ?>"><!-- .ew-search-panel -->
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { languages: currentTable } });
var currentForm;
var flanguagessrch, currentSearchForm, currentAdvancedSearchForm;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery,
        fields = currentTable.fields;

    // Form object for search
    let form = new ew.FormBuilder()
        .setId("flanguagessrch")
        .setPageId("list")
<?php if ($Page->UseAjaxActions) { ?>
        .setSubmitWithFetch(true)
<?php } ?>

        // Dynamic selection lists
        .setLists({
        })

        // Filters
        .setFilterList(<?= $Page->getFilterList() ?>)
        .build();
    window[form.id] = form;
    currentSearchForm = form;
    loadjs.done(form.id);
});
</script>
<input type="hidden" name="cmd" value="search">
<?php if ($Security->canSearch()) { ?>
<?php if (!$Page->isExport() && !($Page->CurrentAction && $Page->CurrentAction != "search") && $Page->hasSearchFields()) { ?>
<div class="ew-extended-search container-fluid ps-2">
<div class="card shadow-sm" style="width: 100%">
<div class="card-header"><h4 class="card-title"><?php echo Language()->phrase("SearchPanel"); ?></h4></div>
<div class="card-body" style="margin-left: 20px !important;">
<div class="row mb-0">
    <div class="col-sm-auto px-0 pe-sm-2">
        <div class="ew-basic-search input-group">
            <input type="search" name="<?= Config("TABLE_BASIC_SEARCH") ?>" id="<?= Config("TABLE_BASIC_SEARCH") ?>" class="form-control ew-basic-search-keyword" value="<?= HtmlEncode($Page->BasicSearch->getKeyword()) ?>" placeholder="<?= HtmlEncode($Language->phrase("Search")) ?>" aria-label="<?= HtmlEncode($Language->phrase("Search")) ?>">
            <input type="hidden" name="<?= Config("TABLE_BASIC_SEARCH_TYPE") ?>" id="<?= Config("TABLE_BASIC_SEARCH_TYPE") ?>" class="ew-basic-search-type" value="<?= HtmlEncode($Page->BasicSearch->getType()) ?>">
            <button type="button" data-bs-toggle="dropdown" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" aria-haspopup="true" aria-expanded="false">
                <span id="searchtype"><?= $Page->BasicSearch->getTypeNameShort() ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <button type="button" class="dropdown-item<?= $Page->BasicSearch->getType() == "" ? " active" : "" ?>" form="flanguagessrch" data-ew-action="search-type"><?= $Language->phrase("QuickSearchAuto") ?></button>
                <button type="button" class="dropdown-item<?= $Page->BasicSearch->getType() == "=" ? " active" : "" ?>" form="flanguagessrch" data-ew-action="search-type" data-search-type="="><?= $Language->phrase("QuickSearchExact") ?></button>
                <button type="button" class="dropdown-item<?= $Page->BasicSearch->getType() == "AND" ? " active" : "" ?>" form="flanguagessrch" data-ew-action="search-type" data-search-type="AND"><?= $Language->phrase("QuickSearchAll") ?></button>
                <button type="button" class="dropdown-item<?= $Page->BasicSearch->getType() == "OR" ? " active" : "" ?>" form="flanguagessrch" data-ew-action="search-type" data-search-type="OR"><?= $Language->phrase("QuickSearchAny") ?></button>
            </div>
        </div>
    </div>
    <div class="col-sm-auto mb-3">
        <button class="btn btn-primary" name="btn-submit" id="btn-submit" type="submit"><?= $Language->phrase("SearchBtn") ?></button>
    </div>
</div>
</div><!-- /.ew-extended-search -->
</div></div>
<?php } ?>
<?php } ?>
</div><!-- /.ew-search-panel -->
</form>
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
<input type="hidden" name="t" value="languages">
<?php if ($Page->IsModal) { ?>
<input type="hidden" name="modal" value="1">
<?php } ?>
<div id="gmp_languages" class="card-body ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>">
<?php if ($Page->TotalRecords > 0 || $Page->isGridEdit() || $Page->isMultiEdit()) { ?>
<table id="tbl_languageslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <th data-name="Language_Code" class="<?= $Page->Language_Code->headerCellClass() ?>"><div id="elh_languages_Language_Code" class="languages_Language_Code"><?= $Page->renderFieldHeader($Page->Language_Code) ?></div></th>
<?php } ?>
<?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <th data-name="Language_Name" class="<?= $Page->Language_Name->headerCellClass() ?>"><div id="elh_languages_Language_Name" class="languages_Language_Name"><?= $Page->renderFieldHeader($Page->Language_Name) ?></div></th>
<?php } ?>
<?php if ($Page->Default->Visible) { // Default ?>
        <th data-name="Default" class="<?= $Page->Default->headerCellClass() ?>"><div id="elh_languages_Default" class="languages_Default"><?= $Page->renderFieldHeader($Page->Default) ?></div></th>
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
    <?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <td data-name="Language_Code"<?= $Page->Language_Code->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Language_Code" class="el_languages_Language_Code">
<span<?= $Page->Language_Code->viewAttributes() ?>>
<?= $Page->Language_Code->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <td data-name="Language_Name"<?= $Page->Language_Name->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Language_Name" class="el_languages_Language_Name">
<span<?= $Page->Language_Name->viewAttributes() ?>>
<?= $Page->Language_Name->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Default->Visible) { // Default ?>
        <td data-name="Default"<?= $Page->Default->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Default" class="el_languages_Default">
<span<?= $Page->Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Default_<?= $Page->RowCount ?>"></label>
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
<table id="tbl_languageslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <th data-name="Language_Code" class="<?= $Page->Language_Code->headerCellClass() ?>"><div id="elh_languages_Language_Code" class="languages_Language_Code"><?= $Page->renderFieldHeader($Page->Language_Code) ?></div></th>
<?php } ?>
<?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <th data-name="Language_Name" class="<?= $Page->Language_Name->headerCellClass() ?>"><div id="elh_languages_Language_Name" class="languages_Language_Name"><?= $Page->renderFieldHeader($Page->Language_Name) ?></div></th>
<?php } ?>
<?php if ($Page->Default->Visible) { // Default ?>
        <th data-name="Default" class="<?= $Page->Default->headerCellClass() ?>"><div id="elh_languages_Default" class="languages_Default"><?= $Page->renderFieldHeader($Page->Default) ?></div></th>
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
    <?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <td data-name="Language_Code"<?= $Page->Language_Code->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Language_Code" class="el_languages_Language_Code">
<span<?= $Page->Language_Code->viewAttributes() ?>>
<?= $Page->Language_Code->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <td data-name="Language_Name"<?= $Page->Language_Name->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Language_Name" class="el_languages_Language_Name">
<span<?= $Page->Language_Name->viewAttributes() ?>>
<?= $Page->Language_Name->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Default->Visible) { // Default ?>
        <td data-name="Default"<?= $Page->Default->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Default" class="el_languages_Default">
<span<?= $Page->Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Default_<?= $Page->RowCount ?>"></label>
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
<input type="hidden" name="t" value="languages">
<?php if ($Page->IsModal) { ?>
<input type="hidden" name="modal" value="1">
<?php } ?>
<div id="gmp_languages" class="card-body ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>">
<?php if ($Page->TotalRecords > 0 || $Page->isGridEdit() || $Page->isMultiEdit()) { ?>
<table id="tbl_languageslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <th data-name="Language_Code" class="<?= $Page->Language_Code->headerCellClass() ?>"><div id="elh_languages_Language_Code" class="languages_Language_Code"><?= $Page->renderFieldHeader($Page->Language_Code) ?></div></th>
<?php } ?>
<?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <th data-name="Language_Name" class="<?= $Page->Language_Name->headerCellClass() ?>"><div id="elh_languages_Language_Name" class="languages_Language_Name"><?= $Page->renderFieldHeader($Page->Language_Name) ?></div></th>
<?php } ?>
<?php if ($Page->Default->Visible) { // Default ?>
        <th data-name="Default" class="<?= $Page->Default->headerCellClass() ?>"><div id="elh_languages_Default" class="languages_Default"><?= $Page->renderFieldHeader($Page->Default) ?></div></th>
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
    <?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <td data-name="Language_Code"<?= $Page->Language_Code->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Language_Code" class="el_languages_Language_Code">
<span<?= $Page->Language_Code->viewAttributes() ?>>
<?= $Page->Language_Code->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <td data-name="Language_Name"<?= $Page->Language_Name->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Language_Name" class="el_languages_Language_Name">
<span<?= $Page->Language_Name->viewAttributes() ?>>
<?= $Page->Language_Name->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Default->Visible) { // Default ?>
        <td data-name="Default"<?= $Page->Default->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Default" class="el_languages_Default">
<span<?= $Page->Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Default_<?= $Page->RowCount ?>"></label>
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
<table id="tbl_languageslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <th data-name="Language_Code" class="<?= $Page->Language_Code->headerCellClass() ?>"><div id="elh_languages_Language_Code" class="languages_Language_Code"><?= $Page->renderFieldHeader($Page->Language_Code) ?></div></th>
<?php } ?>
<?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <th data-name="Language_Name" class="<?= $Page->Language_Name->headerCellClass() ?>"><div id="elh_languages_Language_Name" class="languages_Language_Name"><?= $Page->renderFieldHeader($Page->Language_Name) ?></div></th>
<?php } ?>
<?php if ($Page->Default->Visible) { // Default ?>
        <th data-name="Default" class="<?= $Page->Default->headerCellClass() ?>"><div id="elh_languages_Default" class="languages_Default"><?= $Page->renderFieldHeader($Page->Default) ?></div></th>
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
    <?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <td data-name="Language_Code"<?= $Page->Language_Code->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Language_Code" class="el_languages_Language_Code">
<span<?= $Page->Language_Code->viewAttributes() ?>>
<?= $Page->Language_Code->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <td data-name="Language_Name"<?= $Page->Language_Name->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Language_Name" class="el_languages_Language_Name">
<span<?= $Page->Language_Name->viewAttributes() ?>>
<?= $Page->Language_Name->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Default->Visible) { // Default ?>
        <td data-name="Default"<?= $Page->Default->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_languages_Default" class="el_languages_Default">
<span<?= $Page->Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Default_<?= $Page->RowCount ?>"></label>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flanguagesadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#flanguagesadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flanguagesedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#flanguagesedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flanguagesupdate.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#flanguagesupdate").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flanguagesdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#flanguagesdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport() && CurrentPageID()=="list") { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-grid-save, .ew-grid-insert').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#flanguageslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-inline-update').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#flanguageslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-inline-insert').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#flanguageslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){var gridchange=false;$('[data-table="languages"]').change(function(){	gridchange=true;});$('.ew-grid-cancel,.ew-inline-cancel').click(function(){if (gridchange==true){ew.prompt({title: ew.language.phrase("ConfirmCancel"),icon:'question',showCancelButton:true},result=>{if(result) window.location = "<?php echo str_replace('_', '', 'languageslist'); ?>";});return false;}});});
</script>
<?php } ?>
<?php if (!$languages->isExport()) { ?>
<script>
loadjs.ready("jscookie", function() {
	var expires = new Date(new Date().getTime() + 525600 * 60 * 1000); // expire in 525600 
	var SearchToggle = $('.ew-search-toggle');
	SearchToggle.on('click', function(event) { 
		event.preventDefault(); 
		if (SearchToggle.hasClass('active')) { 
			ew.Cookies.set(ew.PROJECT_NAME + "_languages_searchpanel", "notactive", {
			  sameSite: ew.COOKIE_SAMESITE,
			  secure: ew.COOKIE_SECURE
			}); 
		} else { 
			ew.Cookies.set(ew.PROJECT_NAME + "_languages_searchpanel", "active", {
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
    ew.addEventHandlers("languages");
});
</script>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
