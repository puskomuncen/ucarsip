<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UsersList = &$Page;
?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { users: currentTable } });
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
<?php if (!$Page->isExport() || Config("EXPORT_MASTER_RECORD") && $Page->isExport("print")) { ?>
<?php
if ($Page->DbMasterFilter != "" && $Page->getCurrentMasterTable() == "userlevels") {
    if ($Page->MasterRecordExists) {
        include_once "views/UserlevelsMaster.php";
    }
}
?>
<?php } ?>
<?php if ($Page->ShowCurrentFilter) { ?>
<?php $Page->showFilterList() ?>
<?php } ?>
<?php if (!$Page->IsModal) { ?>
<form name="fuserssrch" id="fuserssrch" class="ew-form ew-ext-search-form" action="<?= CurrentPageUrl(false) ?>" novalidate autocomplete="off">
<div id="fuserssrch_search_panel" class="mb-2 mb-sm-0 <?= $Page->SearchPanelClass ?>"><!-- .ew-search-panel -->
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { users: currentTable } });
var currentForm;
var fuserssrch, currentSearchForm, currentAdvancedSearchForm;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery,
        fields = currentTable.fields;

    // Form object for search
    let form = new ew.FormBuilder()
        .setId("fuserssrch")
        .setPageId("list")
<?php if ($Page->UseAjaxActions) { ?>
        .setSubmitWithFetch(true)
<?php } ?>

        // Add fields
        .addFields([
            ["_Username", [], fields._Username.isInvalid],
            ["UserLevel", [], fields.UserLevel.isInvalid],
            ["Activated", [], fields.Activated.isInvalid],
            ["ActiveStatus", [], fields.ActiveStatus.isInvalid]
        ])
        // Validate form
        .setValidate(
            async function () {
                if (!this.validateRequired)
                    return true; // Ignore validation
                let fobj = this.getForm();

                // Validate fields
                if (!this.validateFields())
                    return false;

                // Call Form_CustomValidate event
                if (!(await this.customValidate?.(fobj) ?? true)) {
                    this.focus();
                    return false;
                }
                return true;
            }
        )

        // Form_CustomValidate
        .setCustomValidate(
            function (fobj) { // DO NOT CHANGE THIS LINE! (except for adding "async" keyword)
                    // Your custom validation code in JAVASCRIPT here, return false if invalid.
                    return true;
                }
        )

        // Use JavaScript validation or not
        .setValidateRequired(ew.CLIENT_VALIDATE)

        // Dynamic selection lists
        .setLists({
            "UserLevel": <?= $Page->UserLevel->toClientList($Page) ?>,
            "Activated": <?= $Page->Activated->toClientList($Page) ?>,
            "ActiveStatus": <?= $Page->ActiveStatus->toClientList($Page) ?>,
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
<div class="row mb-0<?= ($Page->SearchFieldsPerRow > 0) ? " row-cols-sm-" . $Page->SearchFieldsPerRow : "" ?>">
<?php
// Render search row
$Page->RowType = RowType::SEARCH;
$Page->resetAttributes();
$Page->renderRow();
?>
<?php if ($Page->_Username->Visible) { // Username ?>
<?php
if (!$Page->_Username->UseFilter) {
    $Page->SearchColumnCount++;
}
?>
    <div id="xs__Username" class="col-sm-auto d-sm-flex align-items-start mb-3 px-0 pe-sm-2<?= $Page->_Username->UseFilter ? " ew-filter-field" : "" ?>">
        <div class="d-flex my-1 my-sm-0">
            <label for="x__Username" class="ew-search-caption ew-label"><?= $Page->_Username->caption() ?></label>
            <div class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z__Username" id="z__Username" value="LIKE">
</div>
        </div>
        <div id="el_users__Username" class="ew-search-field">
<input type="<?= $Page->_Username->getInputTextType() ?>" name="x__Username" id="x__Username" data-table="users" data-field="x__Username" value="<?= $Page->_Username->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->_Username->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->_Username->formatPattern()) ?>"<?= $Page->_Username->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->_Username->getErrorMessage(false) ?></div>
</div>
        <div class="d-flex my-1 my-sm-0">
        </div><!-- /.ew-search-field -->
    </div><!-- /.col-sm-auto -->
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
<?php
if (!$Page->UserLevel->UseFilter) {
    $Page->SearchColumnCount++;
}
?>
    <div id="xs_UserLevel" class="col-sm-auto d-sm-flex align-items-start mb-3 px-0 pe-sm-2<?= $Page->UserLevel->UseFilter ? " ew-filter-field" : "" ?>">
        <div class="d-flex my-1 my-sm-0">
            <label for="x_UserLevel" class="ew-search-caption ew-label"><?= $Page->UserLevel->caption() ?></label>
            <div class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_UserLevel" id="z_UserLevel" value="=">
</div>
        </div>
        <div id="el_users_UserLevel" class="ew-search-field">
<?php if (!$Security->canAccess() && $Security->isLoggedIn()) { // No access permission ?>
<span class="form-control-plaintext"><?= $Page->UserLevel->getDisplayValue($Page->UserLevel->getEditValue()) ?></span>
<?php } else { ?>
    <select
        id="x_UserLevel"
        name="x_UserLevel"
        class="form-select ew-select<?= $Page->UserLevel->isInvalidClass() ?>"
        <?php if (!$Page->UserLevel->IsNativeSelect) { ?>
        data-select2-id="fuserssrch_x_UserLevel"
        <?php } ?>
        data-table="users"
        data-field="x_UserLevel"
        data-value-separator="<?= $Page->UserLevel->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->UserLevel->getPlaceHolder()) ?>"
        <?= $Page->UserLevel->editAttributes() ?>>
        <?= $Page->UserLevel->selectOptionListHtml("x_UserLevel") ?>
    </select>
    <div class="invalid-feedback"><?= $Page->UserLevel->getErrorMessage(false) ?></div>
<?= $Page->UserLevel->Lookup->getParamTag($Page, "p_x_UserLevel") ?>
<?php if (!$Page->UserLevel->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fuserssrch", function() {
    var options = { name: "x_UserLevel", selectId: "fuserssrch_x_UserLevel" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fuserssrch.lists.UserLevel?.lookupOptions.length) {
        options.data = { id: "x_UserLevel", form: "fuserssrch" };
    } else {
        options.ajax = { id: "x_UserLevel", form: "fuserssrch", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.UserLevel.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
<?php } ?>
</div>
        <div class="d-flex my-1 my-sm-0">
        </div><!-- /.ew-search-field -->
    </div><!-- /.col-sm-auto -->
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
<?php
if (!$Page->Activated->UseFilter) {
    $Page->SearchColumnCount++;
}
?>
    <div id="xs_Activated" class="col-sm-auto d-sm-flex align-items-start mb-3 px-0 pe-sm-2<?= $Page->Activated->UseFilter ? " ew-filter-field" : "" ?>">
        <div class="d-flex my-1 my-sm-0">
            <label class="ew-search-caption ew-label"><?= $Page->Activated->caption() ?></label>
            <div class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_Activated" id="z_Activated" value="=">
</div>
        </div>
        <div id="el_users_Activated" class="ew-search-field">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Activated->isInvalidClass() ?>" data-table="users" data-field="x_Activated" data-boolean name="x_Activated" id="x_Activated" value="1"<?= ConvertToBool($Page->Activated->AdvancedSearch->SearchValue) ? " checked" : "" ?><?= $Page->Activated->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Page->Activated->getErrorMessage(false) ?></div>
</div>
</div>
        <div class="d-flex my-1 my-sm-0">
        </div><!-- /.ew-search-field -->
    </div><!-- /.col-sm-auto -->
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
<?php
if (!$Page->ActiveStatus->UseFilter) {
    $Page->SearchColumnCount++;
}
?>
    <div id="xs_ActiveStatus" class="col-sm-auto d-sm-flex align-items-start mb-3 px-0 pe-sm-2<?= $Page->ActiveStatus->UseFilter ? " ew-filter-field" : "" ?>">
        <div class="d-flex my-1 my-sm-0">
            <label class="ew-search-caption ew-label"><?= $Page->ActiveStatus->caption() ?></label>
            <div class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_ActiveStatus" id="z_ActiveStatus" value="=">
</div>
        </div>
        <div id="el_users_ActiveStatus" class="ew-search-field">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->ActiveStatus->isInvalidClass() ?>" data-table="users" data-field="x_ActiveStatus" data-boolean name="x_ActiveStatus" id="x_ActiveStatus" value="1"<?= ConvertToBool($Page->ActiveStatus->AdvancedSearch->SearchValue) ? " checked" : "" ?><?= $Page->ActiveStatus->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Page->ActiveStatus->getErrorMessage(false) ?></div>
</div>
</div>
        <div class="d-flex my-1 my-sm-0">
        </div><!-- /.ew-search-field -->
    </div><!-- /.col-sm-auto -->
<?php } ?>
</div><!-- /.row -->
<div class="row mb-0">
    <div class="col-sm-auto px-0 pe-sm-2">
        <div class="ew-basic-search input-group">
            <input type="search" name="<?= Config("TABLE_BASIC_SEARCH") ?>" id="<?= Config("TABLE_BASIC_SEARCH") ?>" class="form-control ew-basic-search-keyword" value="<?= HtmlEncode($Page->BasicSearch->getKeyword()) ?>" placeholder="<?= HtmlEncode($Language->phrase("Search")) ?>" aria-label="<?= HtmlEncode($Language->phrase("Search")) ?>">
            <input type="hidden" name="<?= Config("TABLE_BASIC_SEARCH_TYPE") ?>" id="<?= Config("TABLE_BASIC_SEARCH_TYPE") ?>" class="ew-basic-search-type" value="<?= HtmlEncode($Page->BasicSearch->getType()) ?>">
            <button type="button" data-bs-toggle="dropdown" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" aria-haspopup="true" aria-expanded="false">
                <span id="searchtype"><?= $Page->BasicSearch->getTypeNameShort() ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <button type="button" class="dropdown-item<?= $Page->BasicSearch->getType() == "" ? " active" : "" ?>" form="fuserssrch" data-ew-action="search-type"><?= $Language->phrase("QuickSearchAuto") ?></button>
                <button type="button" class="dropdown-item<?= $Page->BasicSearch->getType() == "=" ? " active" : "" ?>" form="fuserssrch" data-ew-action="search-type" data-search-type="="><?= $Language->phrase("QuickSearchExact") ?></button>
                <button type="button" class="dropdown-item<?= $Page->BasicSearch->getType() == "AND" ? " active" : "" ?>" form="fuserssrch" data-ew-action="search-type" data-search-type="AND"><?= $Language->phrase("QuickSearchAll") ?></button>
                <button type="button" class="dropdown-item<?= $Page->BasicSearch->getType() == "OR" ? " active" : "" ?>" form="fuserssrch" data-ew-action="search-type" data-search-type="OR"><?= $Language->phrase("QuickSearchAny") ?></button>
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
<input type="hidden" name="t" value="users">
<?php if ($Page->IsModal) { ?>
<input type="hidden" name="modal" value="1">
<?php } ?>
<?php if ($Page->getCurrentMasterTable() == "userlevels" && $Page->CurrentAction) { ?>
<input type="hidden" name="<?= Config("TABLE_SHOW_MASTER") ?>" value="userlevels">
<input type="hidden" name="fk_ID" value="<?= HtmlEncode($Page->UserLevel->getSessionValue()) ?>">
<?php } ?>
<div id="gmp_users" class="card-body ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>">
<?php if ($Page->TotalRecords > 0 || $Page->isGridEdit() || $Page->isMultiEdit()) { ?>
<table id="tbl_userslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Page->_UserID->Visible) { // UserID ?>
        <th data-name="_UserID" class="<?= $Page->_UserID->headerCellClass() ?>"><div id="elh_users__UserID" class="users__UserID"><?= $Page->renderFieldHeader($Page->_UserID) ?></div></th>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
        <th data-name="_Username" class="<?= $Page->_Username->headerCellClass() ?>"><div id="elh_users__Username" class="users__Username"><?= $Page->renderFieldHeader($Page->_Username) ?></div></th>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <th data-name="UserLevel" class="<?= $Page->UserLevel->headerCellClass() ?>"><div id="elh_users_UserLevel" class="users_UserLevel"><?= $Page->renderFieldHeader($Page->UserLevel) ?></div></th>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <th data-name="CompleteName" class="<?= $Page->CompleteName->headerCellClass() ?>"><div id="elh_users_CompleteName" class="users_CompleteName"><?= $Page->renderFieldHeader($Page->CompleteName) ?></div></th>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
        <th data-name="Photo" class="<?= $Page->Photo->headerCellClass() ?>"><div id="elh_users_Photo" class="users_Photo"><?= $Page->renderFieldHeader($Page->Photo) ?></div></th>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
        <th data-name="Gender" class="<?= $Page->Gender->headerCellClass() ?>"><div id="elh_users_Gender" class="users_Gender"><?= $Page->renderFieldHeader($Page->Gender) ?></div></th>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
        <th data-name="_Email" class="<?= $Page->_Email->headerCellClass() ?>"><div id="elh_users__Email" class="users__Email"><?= $Page->renderFieldHeader($Page->_Email) ?></div></th>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
        <th data-name="Activated" class="<?= $Page->Activated->headerCellClass() ?>"><div id="elh_users_Activated" class="users_Activated"><?= $Page->renderFieldHeader($Page->Activated) ?></div></th>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <th data-name="ActiveStatus" class="<?= $Page->ActiveStatus->headerCellClass() ?>"><div id="elh_users_ActiveStatus" class="users_ActiveStatus"><?= $Page->renderFieldHeader($Page->ActiveStatus) ?></div></th>
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
    <?php if ($Page->_UserID->Visible) { // UserID ?>
        <td data-name="_UserID"<?= $Page->_UserID->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__UserID" class="el_users__UserID">
<span<?= $Page->_UserID->viewAttributes() ?>>
<?= $Page->_UserID->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->_Username->Visible) { // Username ?>
        <td data-name="_Username"<?= $Page->_Username->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__Username" class="el_users__Username">
<span<?= $Page->_Username->viewAttributes() ?>>
<?= $Page->_Username->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <td data-name="UserLevel"<?= $Page->UserLevel->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Page->UserLevel->viewAttributes() ?>>
<?= $Page->UserLevel->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <td data-name="CompleteName"<?= $Page->CompleteName->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<span<?= $Page->CompleteName->viewAttributes() ?>>
<?= $Page->CompleteName->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Photo->Visible) { // Photo ?>
        <td data-name="Photo"<?= $Page->Photo->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Photo" class="el_users_Photo">
<span>
<?= GetFileViewTag($Page->Photo, $Page->Photo->getViewValue(), false) ?>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Gender->Visible) { // Gender ?>
        <td data-name="Gender"<?= $Page->Gender->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Gender" class="el_users_Gender">
<span<?= $Page->Gender->viewAttributes() ?>>
<?= $Page->Gender->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->_Email->Visible) { // Email ?>
        <td data-name="_Email"<?= $Page->_Email->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__Email" class="el_users__Email">
<span<?= $Page->_Email->viewAttributes() ?>>
<?= $Page->_Email->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Activated->Visible) { // Activated ?>
        <td data-name="Activated"<?= $Page->Activated->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Activated" class="el_users_Activated">
<span<?= $Page->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <td data-name="ActiveStatus"<?= $Page->ActiveStatus->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<span<?= $Page->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Page->RowCount ?>"></label>
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
<table id="tbl_userslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Page->_UserID->Visible) { // UserID ?>
        <th data-name="_UserID" class="<?= $Page->_UserID->headerCellClass() ?>"><div id="elh_users__UserID" class="users__UserID"><?= $Page->renderFieldHeader($Page->_UserID) ?></div></th>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
        <th data-name="_Username" class="<?= $Page->_Username->headerCellClass() ?>"><div id="elh_users__Username" class="users__Username"><?= $Page->renderFieldHeader($Page->_Username) ?></div></th>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <th data-name="UserLevel" class="<?= $Page->UserLevel->headerCellClass() ?>"><div id="elh_users_UserLevel" class="users_UserLevel"><?= $Page->renderFieldHeader($Page->UserLevel) ?></div></th>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <th data-name="CompleteName" class="<?= $Page->CompleteName->headerCellClass() ?>"><div id="elh_users_CompleteName" class="users_CompleteName"><?= $Page->renderFieldHeader($Page->CompleteName) ?></div></th>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
        <th data-name="Photo" class="<?= $Page->Photo->headerCellClass() ?>"><div id="elh_users_Photo" class="users_Photo"><?= $Page->renderFieldHeader($Page->Photo) ?></div></th>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
        <th data-name="Gender" class="<?= $Page->Gender->headerCellClass() ?>"><div id="elh_users_Gender" class="users_Gender"><?= $Page->renderFieldHeader($Page->Gender) ?></div></th>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
        <th data-name="_Email" class="<?= $Page->_Email->headerCellClass() ?>"><div id="elh_users__Email" class="users__Email"><?= $Page->renderFieldHeader($Page->_Email) ?></div></th>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
        <th data-name="Activated" class="<?= $Page->Activated->headerCellClass() ?>"><div id="elh_users_Activated" class="users_Activated"><?= $Page->renderFieldHeader($Page->Activated) ?></div></th>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <th data-name="ActiveStatus" class="<?= $Page->ActiveStatus->headerCellClass() ?>"><div id="elh_users_ActiveStatus" class="users_ActiveStatus"><?= $Page->renderFieldHeader($Page->ActiveStatus) ?></div></th>
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
    <?php if ($Page->_UserID->Visible) { // UserID ?>
        <td data-name="_UserID"<?= $Page->_UserID->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__UserID" class="el_users__UserID">
<span<?= $Page->_UserID->viewAttributes() ?>>
<?= $Page->_UserID->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->_Username->Visible) { // Username ?>
        <td data-name="_Username"<?= $Page->_Username->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__Username" class="el_users__Username">
<span<?= $Page->_Username->viewAttributes() ?>>
<?= $Page->_Username->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <td data-name="UserLevel"<?= $Page->UserLevel->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Page->UserLevel->viewAttributes() ?>>
<?= $Page->UserLevel->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <td data-name="CompleteName"<?= $Page->CompleteName->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<span<?= $Page->CompleteName->viewAttributes() ?>>
<?= $Page->CompleteName->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Photo->Visible) { // Photo ?>
        <td data-name="Photo"<?= $Page->Photo->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Photo" class="el_users_Photo">
<span>
<?= GetFileViewTag($Page->Photo, $Page->Photo->getViewValue(), false) ?>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Gender->Visible) { // Gender ?>
        <td data-name="Gender"<?= $Page->Gender->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Gender" class="el_users_Gender">
<span<?= $Page->Gender->viewAttributes() ?>>
<?= $Page->Gender->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->_Email->Visible) { // Email ?>
        <td data-name="_Email"<?= $Page->_Email->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__Email" class="el_users__Email">
<span<?= $Page->_Email->viewAttributes() ?>>
<?= $Page->_Email->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Activated->Visible) { // Activated ?>
        <td data-name="Activated"<?= $Page->Activated->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Activated" class="el_users_Activated">
<span<?= $Page->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <td data-name="ActiveStatus"<?= $Page->ActiveStatus->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<span<?= $Page->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Page->RowCount ?>"></label>
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
<input type="hidden" name="t" value="users">
<?php if ($Page->IsModal) { ?>
<input type="hidden" name="modal" value="1">
<?php } ?>
<?php if ($Page->getCurrentMasterTable() == "userlevels" && $Page->CurrentAction) { ?>
<input type="hidden" name="<?= Config("TABLE_SHOW_MASTER") ?>" value="userlevels">
<input type="hidden" name="fk_ID" value="<?= HtmlEncode($Page->UserLevel->getSessionValue()) ?>">
<?php } ?>
<div id="gmp_users" class="card-body ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>">
<?php if ($Page->TotalRecords > 0 || $Page->isGridEdit() || $Page->isMultiEdit()) { ?>
<table id="tbl_userslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Page->_UserID->Visible) { // UserID ?>
        <th data-name="_UserID" class="<?= $Page->_UserID->headerCellClass() ?>"><div id="elh_users__UserID" class="users__UserID"><?= $Page->renderFieldHeader($Page->_UserID) ?></div></th>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
        <th data-name="_Username" class="<?= $Page->_Username->headerCellClass() ?>"><div id="elh_users__Username" class="users__Username"><?= $Page->renderFieldHeader($Page->_Username) ?></div></th>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <th data-name="UserLevel" class="<?= $Page->UserLevel->headerCellClass() ?>"><div id="elh_users_UserLevel" class="users_UserLevel"><?= $Page->renderFieldHeader($Page->UserLevel) ?></div></th>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <th data-name="CompleteName" class="<?= $Page->CompleteName->headerCellClass() ?>"><div id="elh_users_CompleteName" class="users_CompleteName"><?= $Page->renderFieldHeader($Page->CompleteName) ?></div></th>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
        <th data-name="Photo" class="<?= $Page->Photo->headerCellClass() ?>"><div id="elh_users_Photo" class="users_Photo"><?= $Page->renderFieldHeader($Page->Photo) ?></div></th>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
        <th data-name="Gender" class="<?= $Page->Gender->headerCellClass() ?>"><div id="elh_users_Gender" class="users_Gender"><?= $Page->renderFieldHeader($Page->Gender) ?></div></th>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
        <th data-name="_Email" class="<?= $Page->_Email->headerCellClass() ?>"><div id="elh_users__Email" class="users__Email"><?= $Page->renderFieldHeader($Page->_Email) ?></div></th>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
        <th data-name="Activated" class="<?= $Page->Activated->headerCellClass() ?>"><div id="elh_users_Activated" class="users_Activated"><?= $Page->renderFieldHeader($Page->Activated) ?></div></th>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <th data-name="ActiveStatus" class="<?= $Page->ActiveStatus->headerCellClass() ?>"><div id="elh_users_ActiveStatus" class="users_ActiveStatus"><?= $Page->renderFieldHeader($Page->ActiveStatus) ?></div></th>
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
    <?php if ($Page->_UserID->Visible) { // UserID ?>
        <td data-name="_UserID"<?= $Page->_UserID->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__UserID" class="el_users__UserID">
<span<?= $Page->_UserID->viewAttributes() ?>>
<?= $Page->_UserID->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->_Username->Visible) { // Username ?>
        <td data-name="_Username"<?= $Page->_Username->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__Username" class="el_users__Username">
<span<?= $Page->_Username->viewAttributes() ?>>
<?= $Page->_Username->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <td data-name="UserLevel"<?= $Page->UserLevel->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Page->UserLevel->viewAttributes() ?>>
<?= $Page->UserLevel->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <td data-name="CompleteName"<?= $Page->CompleteName->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<span<?= $Page->CompleteName->viewAttributes() ?>>
<?= $Page->CompleteName->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Photo->Visible) { // Photo ?>
        <td data-name="Photo"<?= $Page->Photo->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Photo" class="el_users_Photo">
<span>
<?= GetFileViewTag($Page->Photo, $Page->Photo->getViewValue(), false) ?>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Gender->Visible) { // Gender ?>
        <td data-name="Gender"<?= $Page->Gender->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Gender" class="el_users_Gender">
<span<?= $Page->Gender->viewAttributes() ?>>
<?= $Page->Gender->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->_Email->Visible) { // Email ?>
        <td data-name="_Email"<?= $Page->_Email->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__Email" class="el_users__Email">
<span<?= $Page->_Email->viewAttributes() ?>>
<?= $Page->_Email->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Activated->Visible) { // Activated ?>
        <td data-name="Activated"<?= $Page->Activated->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Activated" class="el_users_Activated">
<span<?= $Page->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <td data-name="ActiveStatus"<?= $Page->ActiveStatus->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<span<?= $Page->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Page->RowCount ?>"></label>
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
<table id="tbl_userslist" class="<?= $Page->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Page->_UserID->Visible) { // UserID ?>
        <th data-name="_UserID" class="<?= $Page->_UserID->headerCellClass() ?>"><div id="elh_users__UserID" class="users__UserID"><?= $Page->renderFieldHeader($Page->_UserID) ?></div></th>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
        <th data-name="_Username" class="<?= $Page->_Username->headerCellClass() ?>"><div id="elh_users__Username" class="users__Username"><?= $Page->renderFieldHeader($Page->_Username) ?></div></th>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <th data-name="UserLevel" class="<?= $Page->UserLevel->headerCellClass() ?>"><div id="elh_users_UserLevel" class="users_UserLevel"><?= $Page->renderFieldHeader($Page->UserLevel) ?></div></th>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <th data-name="CompleteName" class="<?= $Page->CompleteName->headerCellClass() ?>"><div id="elh_users_CompleteName" class="users_CompleteName"><?= $Page->renderFieldHeader($Page->CompleteName) ?></div></th>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
        <th data-name="Photo" class="<?= $Page->Photo->headerCellClass() ?>"><div id="elh_users_Photo" class="users_Photo"><?= $Page->renderFieldHeader($Page->Photo) ?></div></th>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
        <th data-name="Gender" class="<?= $Page->Gender->headerCellClass() ?>"><div id="elh_users_Gender" class="users_Gender"><?= $Page->renderFieldHeader($Page->Gender) ?></div></th>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
        <th data-name="_Email" class="<?= $Page->_Email->headerCellClass() ?>"><div id="elh_users__Email" class="users__Email"><?= $Page->renderFieldHeader($Page->_Email) ?></div></th>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
        <th data-name="Activated" class="<?= $Page->Activated->headerCellClass() ?>"><div id="elh_users_Activated" class="users_Activated"><?= $Page->renderFieldHeader($Page->Activated) ?></div></th>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <th data-name="ActiveStatus" class="<?= $Page->ActiveStatus->headerCellClass() ?>"><div id="elh_users_ActiveStatus" class="users_ActiveStatus"><?= $Page->renderFieldHeader($Page->ActiveStatus) ?></div></th>
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
    <?php if ($Page->_UserID->Visible) { // UserID ?>
        <td data-name="_UserID"<?= $Page->_UserID->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__UserID" class="el_users__UserID">
<span<?= $Page->_UserID->viewAttributes() ?>>
<?= $Page->_UserID->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->_Username->Visible) { // Username ?>
        <td data-name="_Username"<?= $Page->_Username->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__Username" class="el_users__Username">
<span<?= $Page->_Username->viewAttributes() ?>>
<?= $Page->_Username->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <td data-name="UserLevel"<?= $Page->UserLevel->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Page->UserLevel->viewAttributes() ?>>
<?= $Page->UserLevel->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <td data-name="CompleteName"<?= $Page->CompleteName->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<span<?= $Page->CompleteName->viewAttributes() ?>>
<?= $Page->CompleteName->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Photo->Visible) { // Photo ?>
        <td data-name="Photo"<?= $Page->Photo->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Photo" class="el_users_Photo">
<span>
<?= GetFileViewTag($Page->Photo, $Page->Photo->getViewValue(), false) ?>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Gender->Visible) { // Gender ?>
        <td data-name="Gender"<?= $Page->Gender->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Gender" class="el_users_Gender">
<span<?= $Page->Gender->viewAttributes() ?>>
<?= $Page->Gender->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->_Email->Visible) { // Email ?>
        <td data-name="_Email"<?= $Page->_Email->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users__Email" class="el_users__Email">
<span<?= $Page->_Email->viewAttributes() ?>>
<?= $Page->_Email->getViewValue() ?></span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->Activated->Visible) { // Activated ?>
        <td data-name="Activated"<?= $Page->Activated->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_Activated" class="el_users_Activated">
<span<?= $Page->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    <?php } ?>
    <?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <td data-name="ActiveStatus"<?= $Page->ActiveStatus->cellAttributes() ?>>
<span id="el<?= $Page->RowIndex == '$rowindex$' ? '$rowindex$' : $Page->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<span<?= $Page->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Page->RowCount ?>"></label>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fusersadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fusersadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fusersedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fusersedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fusersupdate.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fusersupdate").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fusersdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fusersdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport() && CurrentPageID()=="list") { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-grid-save, .ew-grid-insert').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fuserslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-inline-update').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fuserslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('.ew-inline-insert').on('click',function(){ew.prompt({title: ew.language.phrase("MessageSaveGridConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fuserslist").submit();});return false;});});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){var gridchange=false;$('[data-table="users"]').change(function(){	gridchange=true;});$('.ew-grid-cancel,.ew-inline-cancel').click(function(){if (gridchange==true){ew.prompt({title: ew.language.phrase("ConfirmCancel"),icon:'question',showCancelButton:true},result=>{if(result) window.location = "<?php echo str_replace('_', '', 'userslist'); ?>";});return false;}});});
</script>
<?php } ?>
<?php if (!$users->isExport()) { ?>
<script>
loadjs.ready("jscookie", function() {
	var expires = new Date(new Date().getTime() + 525600 * 60 * 1000); // expire in 525600 
	var SearchToggle = $('.ew-search-toggle');
	SearchToggle.on('click', function(event) { 
		event.preventDefault(); 
		if (SearchToggle.hasClass('active')) { 
			ew.Cookies.set(ew.PROJECT_NAME + "_users_searchpanel", "notactive", {
			  sameSite: ew.COOKIE_SAMESITE,
			  secure: ew.COOKIE_SECURE
			}); 
		} else { 
			ew.Cookies.set(ew.PROJECT_NAME + "_users_searchpanel", "active", {
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
    ew.addEventHandlers("users");
});
</script>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
