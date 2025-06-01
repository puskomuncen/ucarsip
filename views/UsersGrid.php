<?php

namespace PHPMaker2025\ucarsip;

// Set up and run Grid object
$Grid = Container("UsersGrid");
$Grid->run();
?>
<?php if (!$Grid->isExport()) { ?>
<script<?= Nonce() ?>>
var fusersgrid;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let currentTable = <?= json_encode($Grid->toClientVar()) ?>;
    ew.deepAssign(ew.vars, { tables: { users: currentTable } });
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fusersgrid")
        .setPageId("grid")
        .setFormKeyCountName("<?= $Grid->getFormKeyCountName() ?>")

        // Add fields
        .setFields([
            ["_UserID", [fields._UserID.visible && fields._UserID.required ? ew.Validators.required(fields._UserID.caption) : null], fields._UserID.isInvalid],
            ["_Username", [fields._Username.visible && fields._Username.required ? ew.Validators.required(fields._Username.caption) : null], fields._Username.isInvalid],
            ["UserLevel", [fields.UserLevel.visible && fields.UserLevel.required ? ew.Validators.required(fields.UserLevel.caption) : null], fields.UserLevel.isInvalid],
            ["CompleteName", [fields.CompleteName.visible && fields.CompleteName.required ? ew.Validators.required(fields.CompleteName.caption) : null], fields.CompleteName.isInvalid],
            ["Photo", [fields.Photo.visible && fields.Photo.required ? ew.Validators.fileRequired(fields.Photo.caption) : null], fields.Photo.isInvalid],
            ["Gender", [fields.Gender.visible && fields.Gender.required ? ew.Validators.required(fields.Gender.caption) : null], fields.Gender.isInvalid],
            ["_Email", [fields._Email.visible && fields._Email.required ? ew.Validators.required(fields._Email.caption) : null, ew.Validators.email], fields._Email.isInvalid],
            ["Activated", [fields.Activated.visible && fields.Activated.required ? ew.Validators.required(fields.Activated.caption) : null], fields.Activated.isInvalid],
            ["ActiveStatus", [fields.ActiveStatus.visible && fields.ActiveStatus.required ? ew.Validators.required(fields.ActiveStatus.caption) : null], fields.ActiveStatus.isInvalid]
        ])

        // Check empty row
        .setEmptyRow(
            function (rowIndex) {
                let fobj = this.getForm(),
                    fields = [["_Username",false],["UserLevel",false],["CompleteName",false],["Photo",false],["Gender",false],["_Email",false],["Activated",true],["ActiveStatus",true]];
                if (fields.some(field => ew.valueChanged(fobj, rowIndex, ...field)))
                    return false;
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
            "UserLevel": <?= $Grid->UserLevel->toClientList($Grid) ?>,
            "Gender": <?= $Grid->Gender->toClientList($Grid) ?>,
            "Activated": <?= $Grid->Activated->toClientList($Grid) ?>,
            "ActiveStatus": <?= $Grid->ActiveStatus->toClientList($Grid) ?>,
        })
        .build();
    window[form.id] = form;
    loadjs.done(form.id);
});
</script>
<?php } ?>
<?php if (MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE == TRUE) { ?>
<main class="list">
<?php } else { ?>
<main class="list">
<?php } ?>
<div id="ew-header-options">
<?php $Grid->HeaderOptions?->render("body") ?>
</div>
<div id="ew-list">
<?php if ($Grid->TotalRecords > 0 || $Grid->CurrentAction) { ?>
<div class="card ew-card ew-grid<?= $Grid->isAddOrEdit() ? " ew-grid-add-edit" : "" ?> <?= $Grid->TableGridClass ?>">
<?php if (!$Grid->isExport()) { ?>
<div class="card-header ew-grid-upper-panel">
<?php if ($Grid->CurrentMode == "view" && $Grid->DetailViewPaging) { ?>
<?= $Grid->Pager->render() ?>
<?php } ?>
<div class="ew-list-other-options">
<?php $Grid->OtherOptions->render("body") ?>
</div>
</div>
<?php } ?>
<div id="fusersgrid" class="ew-form ew-list-form">
<div id="gmp_users" class="card-body ew-grid-middle-panel <?= $Grid->TableContainerClass ?>" style="<?= $Grid->TableContainerStyle ?>">
<table id="tbl_usersgrid" class="<?= $Grid->TableClass ?>"><!-- .ew-table -->
<thead>
    <tr class="ew-table-header">
<?php
// Header row
$Grid->RowType = RowType::HEADER;

// Render list options
$Grid->renderListOptions();

// Render list options (header, left)
$Grid->ListOptions->render("header", "left");
?>
<?php if ($Grid->_UserID->Visible) { // UserID ?>
        <th data-name="_UserID" class="<?= $Grid->_UserID->headerCellClass() ?>"><div id="elh_users__UserID" class="users__UserID"><?= $Grid->renderFieldHeader($Grid->_UserID) ?></div></th>
<?php } ?>
<?php if ($Grid->_Username->Visible) { // Username ?>
        <th data-name="_Username" class="<?= $Grid->_Username->headerCellClass() ?>"><div id="elh_users__Username" class="users__Username"><?= $Grid->renderFieldHeader($Grid->_Username) ?></div></th>
<?php } ?>
<?php if ($Grid->UserLevel->Visible) { // UserLevel ?>
        <th data-name="UserLevel" class="<?= $Grid->UserLevel->headerCellClass() ?>"><div id="elh_users_UserLevel" class="users_UserLevel"><?= $Grid->renderFieldHeader($Grid->UserLevel) ?></div></th>
<?php } ?>
<?php if ($Grid->CompleteName->Visible) { // CompleteName ?>
        <th data-name="CompleteName" class="<?= $Grid->CompleteName->headerCellClass() ?>"><div id="elh_users_CompleteName" class="users_CompleteName"><?= $Grid->renderFieldHeader($Grid->CompleteName) ?></div></th>
<?php } ?>
<?php if ($Grid->Photo->Visible) { // Photo ?>
        <th data-name="Photo" class="<?= $Grid->Photo->headerCellClass() ?>"><div id="elh_users_Photo" class="users_Photo"><?= $Grid->renderFieldHeader($Grid->Photo) ?></div></th>
<?php } ?>
<?php if ($Grid->Gender->Visible) { // Gender ?>
        <th data-name="Gender" class="<?= $Grid->Gender->headerCellClass() ?>"><div id="elh_users_Gender" class="users_Gender"><?= $Grid->renderFieldHeader($Grid->Gender) ?></div></th>
<?php } ?>
<?php if ($Grid->_Email->Visible) { // Email ?>
        <th data-name="_Email" class="<?= $Grid->_Email->headerCellClass() ?>"><div id="elh_users__Email" class="users__Email"><?= $Grid->renderFieldHeader($Grid->_Email) ?></div></th>
<?php } ?>
<?php if ($Grid->Activated->Visible) { // Activated ?>
        <th data-name="Activated" class="<?= $Grid->Activated->headerCellClass() ?>"><div id="elh_users_Activated" class="users_Activated"><?= $Grid->renderFieldHeader($Grid->Activated) ?></div></th>
<?php } ?>
<?php if ($Grid->ActiveStatus->Visible) { // ActiveStatus ?>
        <th data-name="ActiveStatus" class="<?= $Grid->ActiveStatus->headerCellClass() ?>"><div id="elh_users_ActiveStatus" class="users_ActiveStatus"><?= $Grid->renderFieldHeader($Grid->ActiveStatus) ?></div></th>
<?php } ?>
<?php
// Render list options (header, right)
$Grid->ListOptions->render("header", "right");
?>
    </tr>
</thead>
<tbody data-page="<?= $Grid->getPageNumber() ?>">
<?php
$Grid->setupGrid();
$isInlineAddOrCopy = ($Grid->isCopy() || $Grid->isAdd());
while ($Grid->RecordCount < $Grid->StopRecord || $Grid->RowIndex === '$rowindex$' || $isInlineAddOrCopy && $Grid->RowIndex == 0) {
    if (
        $Grid->CurrentRow !== false
        && $Grid->RowIndex !== '$rowindex$'
        && (!$Grid->isGridAdd() || $Grid->CurrentMode == "copy")
        && (!($isInlineAddOrCopy && $Grid->RowIndex == 0))
    ) {
        $Grid->fetch();
    }
    $Grid->RecordCount++;
    if ($Grid->RecordCount >= $Grid->StartRecord) {
        $Grid->setupRow();

        // Skip 1) delete row / empty row for confirm page, 2) hidden row
        if (
            $Grid->RowAction != "delete"
            && $Grid->RowAction != "insertdelete"
            && !($Grid->RowAction == "insert" && $Grid->isConfirm() && $Grid->emptyRow())
            && $Grid->RowAction != "hide"
        ) {
?>
    <tr <?= $Grid->rowAttributes() ?>>
<?php
// Render list options (body, left)
$Grid->ListOptions->render("body", "left", $Grid->RowCount);
?>
    <?php if ($Grid->_UserID->Visible) { // UserID ?>
        <td data-name="_UserID"<?= $Grid->_UserID->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__UserID" class="el_users__UserID"></span>
<input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>__UserID" id="o<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__UserID" class="el_users__UserID">
<span<?= $Grid->_UserID->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= HtmlEncode(RemoveHtml($Grid->_UserID->getDisplayValue($Grid->_UserID->getEditValue()))) ?>"></span>
<input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" name="x<?= $Grid->RowIndex ?>__UserID" id="x<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->CurrentValue) ?>">
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__UserID" class="el_users__UserID">
<span<?= $Grid->_UserID->viewAttributes() ?>>
<?= $Grid->_UserID->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>__UserID" id="fusersgrid$x<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>__UserID" id="fusersgrid$o<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } else { ?>
            <input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" name="x<?= $Grid->RowIndex ?>__UserID" id="x<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->CurrentValue) ?>">
    <?php } ?>
    <?php if ($Grid->_Username->Visible) { // Username ?>
        <td data-name="_Username"<?= $Grid->_Username->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Username" class="el_users__Username">
<input type="<?= $Grid->_Username->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>__Username" id="x<?= $Grid->RowIndex ?>__Username" data-table="users" data-field="x__Username" value="<?= $Grid->_Username->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Grid->_Username->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->_Username->formatPattern()) ?>"<?= $Grid->_Username->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->_Username->getErrorMessage() ?></div>
</span>
<input type="hidden" data-table="users" data-field="x__Username" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>__Username" id="o<?= $Grid->RowIndex ?>__Username" value="<?= HtmlEncode($Grid->_Username->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Username" class="el_users__Username">
<input type="<?= $Grid->_Username->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>__Username" id="x<?= $Grid->RowIndex ?>__Username" data-table="users" data-field="x__Username" value="<?= $Grid->_Username->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Grid->_Username->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->_Username->formatPattern()) ?>"<?= $Grid->_Username->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->_Username->getErrorMessage() ?></div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Username" class="el_users__Username">
<span<?= $Grid->_Username->viewAttributes() ?>>
<?= $Grid->_Username->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x__Username" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>__Username" id="fusersgrid$x<?= $Grid->RowIndex ?>__Username" value="<?= HtmlEncode($Grid->_Username->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x__Username" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>__Username" id="fusersgrid$o<?= $Grid->RowIndex ?>__Username" value="<?= HtmlEncode($Grid->_Username->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->UserLevel->Visible) { // UserLevel ?>
        <td data-name="UserLevel"<?= $Grid->UserLevel->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<?php if ($Grid->UserLevel->getSessionValue() != "") { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Grid->UserLevel->viewAttributes() ?>>
<span class="form-control-plaintext"><?= $Grid->UserLevel->getDisplayValue($Grid->UserLevel->ViewValue) ?></span></span>
<input type="hidden" id="x<?= $Grid->RowIndex ?>_UserLevel" name="x<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->CurrentValue) ?>" data-hidden="1">
</span>
<?php } elseif (!$Security->canAccess() && $Security->isLoggedIn()) { // No access permission ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span class="form-control-plaintext"><?= $Grid->UserLevel->getDisplayValue($Grid->UserLevel->getEditValue()) ?></span>
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
    <select
        id="x<?= $Grid->RowIndex ?>_UserLevel"
        name="x<?= $Grid->RowIndex ?>_UserLevel"
        class="form-select ew-select<?= $Grid->UserLevel->isInvalidClass() ?>"
        <?php if (!$Grid->UserLevel->IsNativeSelect) { ?>
        data-select2-id="fusersgrid_x<?= $Grid->RowIndex ?>_UserLevel"
        <?php } ?>
        data-table="users"
        data-field="x_UserLevel"
        data-value-separator="<?= $Grid->UserLevel->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->UserLevel->getPlaceHolder()) ?>"
        <?= $Grid->UserLevel->editAttributes() ?>>
        <?= $Grid->UserLevel->selectOptionListHtml("x{$Grid->RowIndex}_UserLevel") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->UserLevel->getErrorMessage() ?></div>
<?= $Grid->UserLevel->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "_UserLevel") ?>
<?php if (!$Grid->UserLevel->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fusersgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_UserLevel", selectId: "fusersgrid_x<?= $Grid->RowIndex ?>_UserLevel" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fusersgrid.lists.UserLevel?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_UserLevel", form: "fusersgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_UserLevel", form: "fusersgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.UserLevel.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<input type="hidden" data-table="users" data-field="x_UserLevel" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_UserLevel" id="o<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<?php if ($Grid->UserLevel->getSessionValue() != "") { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Grid->UserLevel->viewAttributes() ?>>
<span class="form-control-plaintext"><?= $Grid->UserLevel->getDisplayValue($Grid->UserLevel->ViewValue) ?></span></span>
<input type="hidden" id="x<?= $Grid->RowIndex ?>_UserLevel" name="x<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->CurrentValue) ?>" data-hidden="1">
</span>
<?php } elseif (!$Security->canAccess() && $Security->isLoggedIn()) { // No access permission ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span class="form-control-plaintext"><?= $Grid->UserLevel->getDisplayValue($Grid->UserLevel->getEditValue()) ?></span>
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
    <select
        id="x<?= $Grid->RowIndex ?>_UserLevel"
        name="x<?= $Grid->RowIndex ?>_UserLevel"
        class="form-select ew-select<?= $Grid->UserLevel->isInvalidClass() ?>"
        <?php if (!$Grid->UserLevel->IsNativeSelect) { ?>
        data-select2-id="fusersgrid_x<?= $Grid->RowIndex ?>_UserLevel"
        <?php } ?>
        data-table="users"
        data-field="x_UserLevel"
        data-value-separator="<?= $Grid->UserLevel->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->UserLevel->getPlaceHolder()) ?>"
        <?= $Grid->UserLevel->editAttributes() ?>>
        <?= $Grid->UserLevel->selectOptionListHtml("x{$Grid->RowIndex}_UserLevel") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->UserLevel->getErrorMessage() ?></div>
<?= $Grid->UserLevel->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "_UserLevel") ?>
<?php if (!$Grid->UserLevel->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fusersgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_UserLevel", selectId: "fusersgrid_x<?= $Grid->RowIndex ?>_UserLevel" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fusersgrid.lists.UserLevel?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_UserLevel", form: "fusersgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_UserLevel", form: "fusersgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.UserLevel.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Grid->UserLevel->viewAttributes() ?>>
<?= $Grid->UserLevel->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_UserLevel" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_UserLevel" id="fusersgrid$x<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_UserLevel" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_UserLevel" id="fusersgrid$o<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->CompleteName->Visible) { // CompleteName ?>
        <td data-name="CompleteName"<?= $Grid->CompleteName->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<input type="<?= $Grid->CompleteName->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>_CompleteName" id="x<?= $Grid->RowIndex ?>_CompleteName" data-table="users" data-field="x_CompleteName" value="<?= $Grid->CompleteName->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Grid->CompleteName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->CompleteName->formatPattern()) ?>"<?= $Grid->CompleteName->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->CompleteName->getErrorMessage() ?></div>
</span>
<input type="hidden" data-table="users" data-field="x_CompleteName" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_CompleteName" id="o<?= $Grid->RowIndex ?>_CompleteName" value="<?= HtmlEncode($Grid->CompleteName->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<input type="<?= $Grid->CompleteName->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>_CompleteName" id="x<?= $Grid->RowIndex ?>_CompleteName" data-table="users" data-field="x_CompleteName" value="<?= $Grid->CompleteName->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Grid->CompleteName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->CompleteName->formatPattern()) ?>"<?= $Grid->CompleteName->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->CompleteName->getErrorMessage() ?></div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<span<?= $Grid->CompleteName->viewAttributes() ?>>
<?= $Grid->CompleteName->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_CompleteName" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_CompleteName" id="fusersgrid$x<?= $Grid->RowIndex ?>_CompleteName" value="<?= HtmlEncode($Grid->CompleteName->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_CompleteName" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_CompleteName" id="fusersgrid$o<?= $Grid->RowIndex ?>_CompleteName" value="<?= HtmlEncode($Grid->CompleteName->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Photo->Visible) { // Photo ?>
        <td data-name="Photo"<?= $Grid->Photo->cellAttributes() ?>>
<?php if ($Grid->RowAction == "insert") { // Add record ?>
<?php if (!$Grid->isConfirm()) { ?>
<span id="el<?= $Grid->RowIndex ?>_users_Photo" class="el_users_Photo">
<div id="fd_x<?= $Grid->RowIndex ?>_Photo" class="fileinput-button ew-file-drop-zone">
    <input
        type="file"
        id="x<?= $Grid->RowIndex ?>_Photo"
        name="x<?= $Grid->RowIndex ?>_Photo"
        class="form-control ew-file-input"
        title="<?= $Grid->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="users"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Grid->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Grid->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Grid->Photo->ImageCropper ? 0 : 1 ?>"
        <?= ($Grid->Photo->ReadOnly || $Grid->Photo->Disabled) ? " disabled" : "" ?>
        <?= $Grid->Photo->editAttributes() ?>
    >
    <div class="text-body-secondary ew-file-text"><?= $Language->phrase("ChooseFile") ?></div>
    <div class="invalid-feedback"><?= $Grid->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x<?= $Grid->RowIndex ?>_Photo" id= "fn_x<?= $Grid->RowIndex ?>_Photo" value="<?= $Grid->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x<?= $Grid->RowIndex ?>_Photo" id= "fa_x<?= $Grid->RowIndex ?>_Photo" value="0">
<table id="ft_x<?= $Grid->RowIndex ?>_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex ?>_users_Photo" class="el_users_Photo">
<div id="fd_x<?= $Grid->RowIndex ?>_Photo">
    <input
        type="file"
        id="x<?= $Grid->RowIndex ?>_Photo"
        name="x<?= $Grid->RowIndex ?>_Photo"
        class="form-control ew-file-input d-none"
        title="<?= $Grid->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="users"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Grid->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Grid->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Grid->Photo->ImageCropper ? 0 : 1 ?>"
        <?= $Grid->Photo->editAttributes() ?>
    >
    <div class="invalid-feedback"><?= $Grid->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x<?= $Grid->RowIndex ?>_Photo" id= "fn_x<?= $Grid->RowIndex ?>_Photo" value="<?= $Grid->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x<?= $Grid->RowIndex ?>_Photo" id= "fa_x<?= $Grid->RowIndex ?>_Photo" value="0">
<table id="ft_x<?= $Grid->RowIndex ?>_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
<?php } ?>
<input type="hidden" data-table="users" data-field="x_Photo" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Photo" id="o<?= $Grid->RowIndex ?>_Photo" value="<?= HtmlEncode($Grid->Photo->OldValue) ?>">
<?php } elseif ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Photo" class="el_users_Photo">
<span>
<?= GetFileViewTag($Grid->Photo, $Grid->Photo->getViewValue(), false) ?>
</span>
</span>
<?php } else  { // Edit record ?>
<?php if (!$Grid->isConfirm()) { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Photo" class="el_users_Photo">
<div id="fd_x<?= $Grid->RowIndex ?>_Photo" class="fileinput-button ew-file-drop-zone">
    <input
        type="file"
        id="x<?= $Grid->RowIndex ?>_Photo"
        name="x<?= $Grid->RowIndex ?>_Photo"
        class="form-control ew-file-input"
        title="<?= $Grid->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="users"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Grid->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Grid->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Grid->Photo->ImageCropper ? 0 : 1 ?>"
        <?= ($Grid->Photo->ReadOnly || $Grid->Photo->Disabled) ? " disabled" : "" ?>
        <?= $Grid->Photo->editAttributes() ?>
    >
    <div class="text-body-secondary ew-file-text"><?= $Language->phrase("ChooseFile") ?></div>
    <div class="invalid-feedback"><?= $Grid->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x<?= $Grid->RowIndex ?>_Photo" id= "fn_x<?= $Grid->RowIndex ?>_Photo" value="<?= $Grid->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x<?= $Grid->RowIndex ?>_Photo" id= "fa_x<?= $Grid->RowIndex ?>_Photo" value="<?= (Post("fa_x<?= $Grid->RowIndex ?>_Photo") == "0") ? "0" : "1" ?>">
<table id="ft_x<?= $Grid->RowIndex ?>_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Photo" class="el_users_Photo">
<div id="fd_x<?= $Grid->RowIndex ?>_Photo">
    <input
        type="file"
        id="x<?= $Grid->RowIndex ?>_Photo"
        name="x<?= $Grid->RowIndex ?>_Photo"
        class="form-control ew-file-input d-none"
        title="<?= $Grid->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="users"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Grid->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Grid->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Grid->Photo->ImageCropper ? 0 : 1 ?>"
        <?= $Grid->Photo->editAttributes() ?>
    >
    <div class="invalid-feedback"><?= $Grid->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x<?= $Grid->RowIndex ?>_Photo" id= "fn_x<?= $Grid->RowIndex ?>_Photo" value="<?= $Grid->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x<?= $Grid->RowIndex ?>_Photo" id= "fa_x<?= $Grid->RowIndex ?>_Photo" value="<?= (Post("fa_x<?= $Grid->RowIndex ?>_Photo") == "0") ? "0" : "1" ?>">
<table id="ft_x<?= $Grid->RowIndex ?>_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Gender->Visible) { // Gender ?>
        <td data-name="Gender"<?= $Grid->Gender->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Gender" class="el_users_Gender">
    <select
        id="x<?= $Grid->RowIndex ?>_Gender"
        name="x<?= $Grid->RowIndex ?>_Gender"
        class="form-select ew-select<?= $Grid->Gender->isInvalidClass() ?>"
        <?php if (!$Grid->Gender->IsNativeSelect) { ?>
        data-select2-id="fusersgrid_x<?= $Grid->RowIndex ?>_Gender"
        <?php } ?>
        data-table="users"
        data-field="x_Gender"
        data-value-separator="<?= $Grid->Gender->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->Gender->getPlaceHolder()) ?>"
        <?= $Grid->Gender->editAttributes() ?>>
        <?= $Grid->Gender->selectOptionListHtml("x{$Grid->RowIndex}_Gender") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->Gender->getErrorMessage() ?></div>
<?php if (!$Grid->Gender->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fusersgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_Gender", selectId: "fusersgrid_x<?= $Grid->RowIndex ?>_Gender" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fusersgrid.lists.Gender?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_Gender", form: "fusersgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_Gender", form: "fusersgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumResultsForSearch = Infinity;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.Gender.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<input type="hidden" data-table="users" data-field="x_Gender" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Gender" id="o<?= $Grid->RowIndex ?>_Gender" value="<?= HtmlEncode($Grid->Gender->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Gender" class="el_users_Gender">
    <select
        id="x<?= $Grid->RowIndex ?>_Gender"
        name="x<?= $Grid->RowIndex ?>_Gender"
        class="form-select ew-select<?= $Grid->Gender->isInvalidClass() ?>"
        <?php if (!$Grid->Gender->IsNativeSelect) { ?>
        data-select2-id="fusersgrid_x<?= $Grid->RowIndex ?>_Gender"
        <?php } ?>
        data-table="users"
        data-field="x_Gender"
        data-value-separator="<?= $Grid->Gender->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->Gender->getPlaceHolder()) ?>"
        <?= $Grid->Gender->editAttributes() ?>>
        <?= $Grid->Gender->selectOptionListHtml("x{$Grid->RowIndex}_Gender") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->Gender->getErrorMessage() ?></div>
<?php if (!$Grid->Gender->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fusersgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_Gender", selectId: "fusersgrid_x<?= $Grid->RowIndex ?>_Gender" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fusersgrid.lists.Gender?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_Gender", form: "fusersgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_Gender", form: "fusersgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumResultsForSearch = Infinity;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.Gender.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Gender" class="el_users_Gender">
<span<?= $Grid->Gender->viewAttributes() ?>>
<?= $Grid->Gender->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_Gender" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_Gender" id="fusersgrid$x<?= $Grid->RowIndex ?>_Gender" value="<?= HtmlEncode($Grid->Gender->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_Gender" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_Gender" id="fusersgrid$o<?= $Grid->RowIndex ?>_Gender" value="<?= HtmlEncode($Grid->Gender->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->_Email->Visible) { // Email ?>
        <td data-name="_Email"<?= $Grid->_Email->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Email" class="el_users__Email">
<input type="<?= $Grid->_Email->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>__Email" id="x<?= $Grid->RowIndex ?>__Email" data-table="users" data-field="x__Email" value="<?= $Grid->_Email->getEditValue() ?>" size="50" maxlength="255" placeholder="<?= HtmlEncode($Grid->_Email->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->_Email->formatPattern()) ?>"<?= $Grid->_Email->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->_Email->getErrorMessage() ?></div>
</span>
<input type="hidden" data-table="users" data-field="x__Email" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>__Email" id="o<?= $Grid->RowIndex ?>__Email" value="<?= HtmlEncode($Grid->_Email->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Email" class="el_users__Email">
<input type="<?= $Grid->_Email->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>__Email" id="x<?= $Grid->RowIndex ?>__Email" data-table="users" data-field="x__Email" value="<?= $Grid->_Email->getEditValue() ?>" size="50" maxlength="255" placeholder="<?= HtmlEncode($Grid->_Email->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->_Email->formatPattern()) ?>"<?= $Grid->_Email->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->_Email->getErrorMessage() ?></div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Email" class="el_users__Email">
<span<?= $Grid->_Email->viewAttributes() ?>>
<?= $Grid->_Email->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x__Email" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>__Email" id="fusersgrid$x<?= $Grid->RowIndex ?>__Email" value="<?= HtmlEncode($Grid->_Email->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x__Email" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>__Email" id="fusersgrid$o<?= $Grid->RowIndex ?>__Email" value="<?= HtmlEncode($Grid->_Email->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Activated->Visible) { // Activated ?>
        <td data-name="Activated"<?= $Grid->Activated->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Activated" class="el_users_Activated">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Grid->Activated->isInvalidClass() ?>" data-table="users" data-field="x_Activated" data-boolean name="x<?= $Grid->RowIndex ?>_Activated" id="x<?= $Grid->RowIndex ?>_Activated" value="1"<?= ConvertToBool($Grid->Activated->CurrentValue) ? " checked" : "" ?><?= $Grid->Activated->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Grid->Activated->getErrorMessage() ?></div>
</div>
</span>
<input type="hidden" data-table="users" data-field="x_Activated" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Activated" id="o<?= $Grid->RowIndex ?>_Activated" value="<?= HtmlEncode($Grid->Activated->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Activated" class="el_users_Activated">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Grid->Activated->isInvalidClass() ?>" data-table="users" data-field="x_Activated" data-boolean name="x<?= $Grid->RowIndex ?>_Activated" id="x<?= $Grid->RowIndex ?>_Activated" value="1"<?= ConvertToBool($Grid->Activated->CurrentValue) ? " checked" : "" ?><?= $Grid->Activated->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Grid->Activated->getErrorMessage() ?></div>
</div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Activated" class="el_users_Activated">
<span<?= $Grid->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Grid->RowCount ?>" class="form-check-input" value="<?= $Grid->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Grid->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Grid->RowCount ?>"></label>
</div>
</span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_Activated" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_Activated" id="fusersgrid$x<?= $Grid->RowIndex ?>_Activated" value="<?= HtmlEncode($Grid->Activated->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_Activated" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_Activated" id="fusersgrid$o<?= $Grid->RowIndex ?>_Activated" value="<?= HtmlEncode($Grid->Activated->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->ActiveStatus->Visible) { // ActiveStatus ?>
        <td data-name="ActiveStatus"<?= $Grid->ActiveStatus->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Grid->ActiveStatus->isInvalidClass() ?>" data-table="users" data-field="x_ActiveStatus" data-boolean name="x<?= $Grid->RowIndex ?>_ActiveStatus" id="x<?= $Grid->RowIndex ?>_ActiveStatus" value="1"<?= ConvertToBool($Grid->ActiveStatus->CurrentValue) ? " checked" : "" ?><?= $Grid->ActiveStatus->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Grid->ActiveStatus->getErrorMessage() ?></div>
</div>
</span>
<input type="hidden" data-table="users" data-field="x_ActiveStatus" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_ActiveStatus" id="o<?= $Grid->RowIndex ?>_ActiveStatus" value="<?= HtmlEncode($Grid->ActiveStatus->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Grid->ActiveStatus->isInvalidClass() ?>" data-table="users" data-field="x_ActiveStatus" data-boolean name="x<?= $Grid->RowIndex ?>_ActiveStatus" id="x<?= $Grid->RowIndex ?>_ActiveStatus" value="1"<?= ConvertToBool($Grid->ActiveStatus->CurrentValue) ? " checked" : "" ?><?= $Grid->ActiveStatus->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Grid->ActiveStatus->getErrorMessage() ?></div>
</div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<span<?= $Grid->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Grid->RowCount ?>" class="form-check-input" value="<?= $Grid->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Grid->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Grid->RowCount ?>"></label>
</div>
</span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_ActiveStatus" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_ActiveStatus" id="fusersgrid$x<?= $Grid->RowIndex ?>_ActiveStatus" value="<?= HtmlEncode($Grid->ActiveStatus->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_ActiveStatus" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_ActiveStatus" id="fusersgrid$o<?= $Grid->RowIndex ?>_ActiveStatus" value="<?= HtmlEncode($Grid->ActiveStatus->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
<?php
// Render list options (body, right)
$Grid->ListOptions->render("body", "right", $Grid->RowCount);
?>
    </tr>
<?php if ($Grid->RowType == RowType::ADD || $Grid->RowType == RowType::EDIT) { ?>
<script<?= Nonce() ?> data-rowindex="<?= $Grid->RowIndex ?>">
loadjs.ready(["fusersgrid","load"], () => fusersgrid.updateLists(<?= $Grid->RowIndex ?><?= $Grid->isAdd() || $Grid->isEdit() || $Grid->isCopy() || $Grid->RowIndex === '$rowindex$' ? ", true" : "" ?>));
</script>
<?php } ?>
<?php
    }
    } // End delete row checking

    // Reset for template row
    if ($Grid->RowIndex === '$rowindex$') {
        $Grid->RowIndex = 0;
    }
    // Reset inline add/copy row
    if (($Grid->isCopy() || $Grid->isAdd()) && $Grid->RowIndex == 0) {
        $Grid->RowIndex = 1;
    }
}
?>
</tbody>
</table><!-- /.ew-table -->
<?php if ($Grid->CurrentMode == "add" || $Grid->CurrentMode == "copy") { ?>
<input type="hidden" name="<?= $Grid->getFormKeyCountName() ?>" id="<?= $Grid->getFormKeyCountName() ?>" value="<?= $Grid->KeyCount ?>">
<?= $Grid->MultiSelectKey ?>
<?php } ?>
<?php if ($Grid->CurrentMode == "edit") { ?>
<input type="hidden" name="<?= $Grid->getFormKeyCountName() ?>" id="<?= $Grid->getFormKeyCountName() ?>" value="<?= $Grid->KeyCount ?>">
<?= $Grid->MultiSelectKey ?>
<?php } ?>
</div><!-- /.ew-grid-middle-panel -->
<?php if ($Grid->CurrentMode == "") { ?>
<input type="hidden" name="action" id="action" value="">
<?php } ?>
<input type="hidden" name="detailpage" value="fusersgrid">
</div><!-- /.ew-list-form -->
<?php
// Close result set
$Grid->Result?->free();
?>
<?php if (!$Grid->isExport()) { ?>
<div class="card-footer ew-grid-lower-panel">
<?php if ($Grid->CurrentMode == "view" && $Grid->DetailViewPaging) { ?>
<?= $Grid->Pager->render() ?>
<?php } ?>
<div class="ew-list-other-options">
<?php $Grid->OtherOptions->render("body", "bottom") ?>
</div>
</div>
<?php } ?>
</div><!-- /.ew-grid -->
<?php } ?>
<?php if ($Grid->TotalRecords == 0 && !$Grid->CurrentAction) { // Show other options ?>
<?php // Begin of Empty Table by Masino Sinaga, September 30, 2020 ?>
<?php if (MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE == TRUE) { ?>
<div class="card ew-card ew-grid<?= $Grid->isAddOrEdit() ? " ew-grid-add-edit" : "" ?> <?= $Grid->TableGridClass ?>">
<?php if (!$Grid->isExport()) { ?>
<div class="card-header ew-grid-upper-panel">
<?php if ($Grid->CurrentMode == "view" && $Grid->DetailViewPaging) { ?>
<?= $Grid->Pager->render() ?>
<?php } ?>
<div class="ew-list-other-options">
<?php $Grid->OtherOptions->render("body") ?>
</div>
</div>
<?php } ?>
<div id="fusersgrid" class="ew-form ew-list-form">
<div id="gmp_users" class="card-body ew-grid-middle-panel <?= $Grid->TableContainerClass ?>" style="<?= $Grid->TableContainerStyle ?>">
<table id="tbl_usersgrid" class="<?= $Grid->TableClass ?>"><!-- .ew-table -->
<thead>
    <tr class="ew-table-header">
<?php
// Header row
$Grid->RowType = RowType::HEADER;

// Render list options
$Grid->renderListOptions();

// Render list options (header, left)
$Grid->ListOptions->render("header", "left");
?>
<?php if ($Grid->_UserID->Visible) { // UserID ?>
        <th data-name="_UserID" class="<?= $Grid->_UserID->headerCellClass() ?>"><div id="elh_users__UserID" class="users__UserID"><?= $Grid->renderFieldHeader($Grid->_UserID) ?></div></th>
<?php } ?>
<?php if ($Grid->_Username->Visible) { // Username ?>
        <th data-name="_Username" class="<?= $Grid->_Username->headerCellClass() ?>"><div id="elh_users__Username" class="users__Username"><?= $Grid->renderFieldHeader($Grid->_Username) ?></div></th>
<?php } ?>
<?php if ($Grid->UserLevel->Visible) { // UserLevel ?>
        <th data-name="UserLevel" class="<?= $Grid->UserLevel->headerCellClass() ?>"><div id="elh_users_UserLevel" class="users_UserLevel"><?= $Grid->renderFieldHeader($Grid->UserLevel) ?></div></th>
<?php } ?>
<?php if ($Grid->CompleteName->Visible) { // CompleteName ?>
        <th data-name="CompleteName" class="<?= $Grid->CompleteName->headerCellClass() ?>"><div id="elh_users_CompleteName" class="users_CompleteName"><?= $Grid->renderFieldHeader($Grid->CompleteName) ?></div></th>
<?php } ?>
<?php if ($Grid->Photo->Visible) { // Photo ?>
        <th data-name="Photo" class="<?= $Grid->Photo->headerCellClass() ?>"><div id="elh_users_Photo" class="users_Photo"><?= $Grid->renderFieldHeader($Grid->Photo) ?></div></th>
<?php } ?>
<?php if ($Grid->Gender->Visible) { // Gender ?>
        <th data-name="Gender" class="<?= $Grid->Gender->headerCellClass() ?>"><div id="elh_users_Gender" class="users_Gender"><?= $Grid->renderFieldHeader($Grid->Gender) ?></div></th>
<?php } ?>
<?php if ($Grid->_Email->Visible) { // Email ?>
        <th data-name="_Email" class="<?= $Grid->_Email->headerCellClass() ?>"><div id="elh_users__Email" class="users__Email"><?= $Grid->renderFieldHeader($Grid->_Email) ?></div></th>
<?php } ?>
<?php if ($Grid->Activated->Visible) { // Activated ?>
        <th data-name="Activated" class="<?= $Grid->Activated->headerCellClass() ?>"><div id="elh_users_Activated" class="users_Activated"><?= $Grid->renderFieldHeader($Grid->Activated) ?></div></th>
<?php } ?>
<?php if ($Grid->ActiveStatus->Visible) { // ActiveStatus ?>
        <th data-name="ActiveStatus" class="<?= $Grid->ActiveStatus->headerCellClass() ?>"><div id="elh_users_ActiveStatus" class="users_ActiveStatus"><?= $Grid->renderFieldHeader($Grid->ActiveStatus) ?></div></th>
<?php } ?>
<?php
// Render list options (header, right)
$Grid->ListOptions->render("header", "right");
?>
    </tr>
</thead>
<tbody data-page="<?= $Grid->getPageNumber() ?>">
<?php
$Grid->setupGrid();
$isInlineAddOrCopy = ($Grid->isCopy() || $Grid->isAdd());
while ($Grid->RecordCount < $Grid->StopRecord || $Grid->RowIndex === '$rowindex$' || $isInlineAddOrCopy && $Grid->RowIndex == 0) {
    if (
        $Grid->CurrentRow !== false
        && $Grid->RowIndex !== '$rowindex$'
        && (!$Grid->isGridAdd() || $Grid->CurrentMode == "copy")
        && (!($isInlineAddOrCopy && $Grid->RowIndex == 0))
    ) {
        $Grid->fetch();
    }
    $Grid->RecordCount++;
    if ($Grid->RecordCount >= $Grid->StartRecord) {
        $Grid->setupRow();

        // Skip 1) delete row / empty row for confirm page, 2) hidden row
        if (
            $Grid->RowAction != "delete"
            && $Grid->RowAction != "insertdelete"
            && !($Grid->RowAction == "insert" && $Grid->isConfirm() && $Grid->emptyRow())
            && $Grid->RowAction != "hide"
        ) {
?>
    <tr <?= $Grid->rowAttributes() ?>>
<?php
// Render list options (body, left)
$Grid->ListOptions->render("body", "left", $Grid->RowCount);
?>
    <?php if ($Grid->_UserID->Visible) { // UserID ?>
        <td data-name="_UserID"<?= $Grid->_UserID->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__UserID" class="el_users__UserID"></span>
<input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>__UserID" id="o<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__UserID" class="el_users__UserID">
<span<?= $Grid->_UserID->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= HtmlEncode(RemoveHtml($Grid->_UserID->getDisplayValue($Grid->_UserID->getEditValue()))) ?>"></span>
<input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" name="x<?= $Grid->RowIndex ?>__UserID" id="x<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->CurrentValue) ?>">
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__UserID" class="el_users__UserID">
<span<?= $Grid->_UserID->viewAttributes() ?>>
<?= $Grid->_UserID->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>__UserID" id="fusersgrid$x<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>__UserID" id="fusersgrid$o<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } else { ?>
            <input type="hidden" data-table="users" data-field="x__UserID" data-hidden="1" name="x<?= $Grid->RowIndex ?>__UserID" id="x<?= $Grid->RowIndex ?>__UserID" value="<?= HtmlEncode($Grid->_UserID->CurrentValue) ?>">
    <?php } ?>
    <?php if ($Grid->_Username->Visible) { // Username ?>
        <td data-name="_Username"<?= $Grid->_Username->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Username" class="el_users__Username">
<input type="<?= $Grid->_Username->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>__Username" id="x<?= $Grid->RowIndex ?>__Username" data-table="users" data-field="x__Username" value="<?= $Grid->_Username->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Grid->_Username->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->_Username->formatPattern()) ?>"<?= $Grid->_Username->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->_Username->getErrorMessage() ?></div>
</span>
<input type="hidden" data-table="users" data-field="x__Username" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>__Username" id="o<?= $Grid->RowIndex ?>__Username" value="<?= HtmlEncode($Grid->_Username->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Username" class="el_users__Username">
<input type="<?= $Grid->_Username->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>__Username" id="x<?= $Grid->RowIndex ?>__Username" data-table="users" data-field="x__Username" value="<?= $Grid->_Username->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Grid->_Username->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->_Username->formatPattern()) ?>"<?= $Grid->_Username->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->_Username->getErrorMessage() ?></div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Username" class="el_users__Username">
<span<?= $Grid->_Username->viewAttributes() ?>>
<?= $Grid->_Username->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x__Username" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>__Username" id="fusersgrid$x<?= $Grid->RowIndex ?>__Username" value="<?= HtmlEncode($Grid->_Username->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x__Username" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>__Username" id="fusersgrid$o<?= $Grid->RowIndex ?>__Username" value="<?= HtmlEncode($Grid->_Username->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->UserLevel->Visible) { // UserLevel ?>
        <td data-name="UserLevel"<?= $Grid->UserLevel->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<?php if ($Grid->UserLevel->getSessionValue() != "") { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Grid->UserLevel->viewAttributes() ?>>
<span class="form-control-plaintext"><?= $Grid->UserLevel->getDisplayValue($Grid->UserLevel->ViewValue) ?></span></span>
<input type="hidden" id="x<?= $Grid->RowIndex ?>_UserLevel" name="x<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->CurrentValue) ?>" data-hidden="1">
</span>
<?php } elseif (!$Security->canAccess() && $Security->isLoggedIn()) { // No access permission ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span class="form-control-plaintext"><?= $Grid->UserLevel->getDisplayValue($Grid->UserLevel->getEditValue()) ?></span>
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
    <select
        id="x<?= $Grid->RowIndex ?>_UserLevel"
        name="x<?= $Grid->RowIndex ?>_UserLevel"
        class="form-select ew-select<?= $Grid->UserLevel->isInvalidClass() ?>"
        <?php if (!$Grid->UserLevel->IsNativeSelect) { ?>
        data-select2-id="fusersgrid_x<?= $Grid->RowIndex ?>_UserLevel"
        <?php } ?>
        data-table="users"
        data-field="x_UserLevel"
        data-value-separator="<?= $Grid->UserLevel->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->UserLevel->getPlaceHolder()) ?>"
        <?= $Grid->UserLevel->editAttributes() ?>>
        <?= $Grid->UserLevel->selectOptionListHtml("x{$Grid->RowIndex}_UserLevel") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->UserLevel->getErrorMessage() ?></div>
<?= $Grid->UserLevel->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "_UserLevel") ?>
<?php if (!$Grid->UserLevel->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fusersgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_UserLevel", selectId: "fusersgrid_x<?= $Grid->RowIndex ?>_UserLevel" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fusersgrid.lists.UserLevel?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_UserLevel", form: "fusersgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_UserLevel", form: "fusersgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.UserLevel.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<input type="hidden" data-table="users" data-field="x_UserLevel" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_UserLevel" id="o<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<?php if ($Grid->UserLevel->getSessionValue() != "") { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Grid->UserLevel->viewAttributes() ?>>
<span class="form-control-plaintext"><?= $Grid->UserLevel->getDisplayValue($Grid->UserLevel->ViewValue) ?></span></span>
<input type="hidden" id="x<?= $Grid->RowIndex ?>_UserLevel" name="x<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->CurrentValue) ?>" data-hidden="1">
</span>
<?php } elseif (!$Security->canAccess() && $Security->isLoggedIn()) { // No access permission ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span class="form-control-plaintext"><?= $Grid->UserLevel->getDisplayValue($Grid->UserLevel->getEditValue()) ?></span>
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
    <select
        id="x<?= $Grid->RowIndex ?>_UserLevel"
        name="x<?= $Grid->RowIndex ?>_UserLevel"
        class="form-select ew-select<?= $Grid->UserLevel->isInvalidClass() ?>"
        <?php if (!$Grid->UserLevel->IsNativeSelect) { ?>
        data-select2-id="fusersgrid_x<?= $Grid->RowIndex ?>_UserLevel"
        <?php } ?>
        data-table="users"
        data-field="x_UserLevel"
        data-value-separator="<?= $Grid->UserLevel->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->UserLevel->getPlaceHolder()) ?>"
        <?= $Grid->UserLevel->editAttributes() ?>>
        <?= $Grid->UserLevel->selectOptionListHtml("x{$Grid->RowIndex}_UserLevel") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->UserLevel->getErrorMessage() ?></div>
<?= $Grid->UserLevel->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "_UserLevel") ?>
<?php if (!$Grid->UserLevel->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fusersgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_UserLevel", selectId: "fusersgrid_x<?= $Grid->RowIndex ?>_UserLevel" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fusersgrid.lists.UserLevel?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_UserLevel", form: "fusersgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_UserLevel", form: "fusersgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.UserLevel.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_UserLevel" class="el_users_UserLevel">
<span<?= $Grid->UserLevel->viewAttributes() ?>>
<?= $Grid->UserLevel->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_UserLevel" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_UserLevel" id="fusersgrid$x<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_UserLevel" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_UserLevel" id="fusersgrid$o<?= $Grid->RowIndex ?>_UserLevel" value="<?= HtmlEncode($Grid->UserLevel->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->CompleteName->Visible) { // CompleteName ?>
        <td data-name="CompleteName"<?= $Grid->CompleteName->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<input type="<?= $Grid->CompleteName->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>_CompleteName" id="x<?= $Grid->RowIndex ?>_CompleteName" data-table="users" data-field="x_CompleteName" value="<?= $Grid->CompleteName->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Grid->CompleteName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->CompleteName->formatPattern()) ?>"<?= $Grid->CompleteName->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->CompleteName->getErrorMessage() ?></div>
</span>
<input type="hidden" data-table="users" data-field="x_CompleteName" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_CompleteName" id="o<?= $Grid->RowIndex ?>_CompleteName" value="<?= HtmlEncode($Grid->CompleteName->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<input type="<?= $Grid->CompleteName->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>_CompleteName" id="x<?= $Grid->RowIndex ?>_CompleteName" data-table="users" data-field="x_CompleteName" value="<?= $Grid->CompleteName->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Grid->CompleteName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->CompleteName->formatPattern()) ?>"<?= $Grid->CompleteName->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->CompleteName->getErrorMessage() ?></div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_CompleteName" class="el_users_CompleteName">
<span<?= $Grid->CompleteName->viewAttributes() ?>>
<?= $Grid->CompleteName->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_CompleteName" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_CompleteName" id="fusersgrid$x<?= $Grid->RowIndex ?>_CompleteName" value="<?= HtmlEncode($Grid->CompleteName->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_CompleteName" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_CompleteName" id="fusersgrid$o<?= $Grid->RowIndex ?>_CompleteName" value="<?= HtmlEncode($Grid->CompleteName->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Photo->Visible) { // Photo ?>
        <td data-name="Photo"<?= $Grid->Photo->cellAttributes() ?>>
<?php if ($Grid->RowAction == "insert") { // Add record ?>
<?php if (!$Grid->isConfirm()) { ?>
<span id="el<?= $Grid->RowIndex ?>_users_Photo" class="el_users_Photo">
<div id="fd_x<?= $Grid->RowIndex ?>_Photo" class="fileinput-button ew-file-drop-zone">
    <input
        type="file"
        id="x<?= $Grid->RowIndex ?>_Photo"
        name="x<?= $Grid->RowIndex ?>_Photo"
        class="form-control ew-file-input"
        title="<?= $Grid->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="users"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Grid->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Grid->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Grid->Photo->ImageCropper ? 0 : 1 ?>"
        <?= ($Grid->Photo->ReadOnly || $Grid->Photo->Disabled) ? " disabled" : "" ?>
        <?= $Grid->Photo->editAttributes() ?>
    >
    <div class="text-body-secondary ew-file-text"><?= $Language->phrase("ChooseFile") ?></div>
    <div class="invalid-feedback"><?= $Grid->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x<?= $Grid->RowIndex ?>_Photo" id= "fn_x<?= $Grid->RowIndex ?>_Photo" value="<?= $Grid->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x<?= $Grid->RowIndex ?>_Photo" id= "fa_x<?= $Grid->RowIndex ?>_Photo" value="0">
<table id="ft_x<?= $Grid->RowIndex ?>_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex ?>_users_Photo" class="el_users_Photo">
<div id="fd_x<?= $Grid->RowIndex ?>_Photo">
    <input
        type="file"
        id="x<?= $Grid->RowIndex ?>_Photo"
        name="x<?= $Grid->RowIndex ?>_Photo"
        class="form-control ew-file-input d-none"
        title="<?= $Grid->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="users"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Grid->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Grid->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Grid->Photo->ImageCropper ? 0 : 1 ?>"
        <?= $Grid->Photo->editAttributes() ?>
    >
    <div class="invalid-feedback"><?= $Grid->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x<?= $Grid->RowIndex ?>_Photo" id= "fn_x<?= $Grid->RowIndex ?>_Photo" value="<?= $Grid->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x<?= $Grid->RowIndex ?>_Photo" id= "fa_x<?= $Grid->RowIndex ?>_Photo" value="0">
<table id="ft_x<?= $Grid->RowIndex ?>_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
<?php } ?>
<input type="hidden" data-table="users" data-field="x_Photo" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Photo" id="o<?= $Grid->RowIndex ?>_Photo" value="<?= HtmlEncode($Grid->Photo->OldValue) ?>">
<?php } elseif ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Photo" class="el_users_Photo">
<span>
<?= GetFileViewTag($Grid->Photo, $Grid->Photo->getViewValue(), false) ?>
</span>
</span>
<?php } else  { // Edit record ?>
<?php if (!$Grid->isConfirm()) { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Photo" class="el_users_Photo">
<div id="fd_x<?= $Grid->RowIndex ?>_Photo" class="fileinput-button ew-file-drop-zone">
    <input
        type="file"
        id="x<?= $Grid->RowIndex ?>_Photo"
        name="x<?= $Grid->RowIndex ?>_Photo"
        class="form-control ew-file-input"
        title="<?= $Grid->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="users"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Grid->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Grid->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Grid->Photo->ImageCropper ? 0 : 1 ?>"
        <?= ($Grid->Photo->ReadOnly || $Grid->Photo->Disabled) ? " disabled" : "" ?>
        <?= $Grid->Photo->editAttributes() ?>
    >
    <div class="text-body-secondary ew-file-text"><?= $Language->phrase("ChooseFile") ?></div>
    <div class="invalid-feedback"><?= $Grid->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x<?= $Grid->RowIndex ?>_Photo" id= "fn_x<?= $Grid->RowIndex ?>_Photo" value="<?= $Grid->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x<?= $Grid->RowIndex ?>_Photo" id= "fa_x<?= $Grid->RowIndex ?>_Photo" value="<?= (Post("fa_x<?= $Grid->RowIndex ?>_Photo") == "0") ? "0" : "1" ?>">
<table id="ft_x<?= $Grid->RowIndex ?>_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Photo" class="el_users_Photo">
<div id="fd_x<?= $Grid->RowIndex ?>_Photo">
    <input
        type="file"
        id="x<?= $Grid->RowIndex ?>_Photo"
        name="x<?= $Grid->RowIndex ?>_Photo"
        class="form-control ew-file-input d-none"
        title="<?= $Grid->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="users"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Grid->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Grid->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Grid->Photo->ImageCropper ? 0 : 1 ?>"
        <?= $Grid->Photo->editAttributes() ?>
    >
    <div class="invalid-feedback"><?= $Grid->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x<?= $Grid->RowIndex ?>_Photo" id= "fn_x<?= $Grid->RowIndex ?>_Photo" value="<?= $Grid->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x<?= $Grid->RowIndex ?>_Photo" id= "fa_x<?= $Grid->RowIndex ?>_Photo" value="<?= (Post("fa_x<?= $Grid->RowIndex ?>_Photo") == "0") ? "0" : "1" ?>">
<table id="ft_x<?= $Grid->RowIndex ?>_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Gender->Visible) { // Gender ?>
        <td data-name="Gender"<?= $Grid->Gender->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Gender" class="el_users_Gender">
    <select
        id="x<?= $Grid->RowIndex ?>_Gender"
        name="x<?= $Grid->RowIndex ?>_Gender"
        class="form-select ew-select<?= $Grid->Gender->isInvalidClass() ?>"
        <?php if (!$Grid->Gender->IsNativeSelect) { ?>
        data-select2-id="fusersgrid_x<?= $Grid->RowIndex ?>_Gender"
        <?php } ?>
        data-table="users"
        data-field="x_Gender"
        data-value-separator="<?= $Grid->Gender->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->Gender->getPlaceHolder()) ?>"
        <?= $Grid->Gender->editAttributes() ?>>
        <?= $Grid->Gender->selectOptionListHtml("x{$Grid->RowIndex}_Gender") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->Gender->getErrorMessage() ?></div>
<?php if (!$Grid->Gender->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fusersgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_Gender", selectId: "fusersgrid_x<?= $Grid->RowIndex ?>_Gender" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fusersgrid.lists.Gender?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_Gender", form: "fusersgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_Gender", form: "fusersgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumResultsForSearch = Infinity;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.Gender.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<input type="hidden" data-table="users" data-field="x_Gender" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Gender" id="o<?= $Grid->RowIndex ?>_Gender" value="<?= HtmlEncode($Grid->Gender->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Gender" class="el_users_Gender">
    <select
        id="x<?= $Grid->RowIndex ?>_Gender"
        name="x<?= $Grid->RowIndex ?>_Gender"
        class="form-select ew-select<?= $Grid->Gender->isInvalidClass() ?>"
        <?php if (!$Grid->Gender->IsNativeSelect) { ?>
        data-select2-id="fusersgrid_x<?= $Grid->RowIndex ?>_Gender"
        <?php } ?>
        data-table="users"
        data-field="x_Gender"
        data-value-separator="<?= $Grid->Gender->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->Gender->getPlaceHolder()) ?>"
        <?= $Grid->Gender->editAttributes() ?>>
        <?= $Grid->Gender->selectOptionListHtml("x{$Grid->RowIndex}_Gender") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->Gender->getErrorMessage() ?></div>
<?php if (!$Grid->Gender->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fusersgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_Gender", selectId: "fusersgrid_x<?= $Grid->RowIndex ?>_Gender" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fusersgrid.lists.Gender?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_Gender", form: "fusersgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_Gender", form: "fusersgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumResultsForSearch = Infinity;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.Gender.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Gender" class="el_users_Gender">
<span<?= $Grid->Gender->viewAttributes() ?>>
<?= $Grid->Gender->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_Gender" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_Gender" id="fusersgrid$x<?= $Grid->RowIndex ?>_Gender" value="<?= HtmlEncode($Grid->Gender->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_Gender" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_Gender" id="fusersgrid$o<?= $Grid->RowIndex ?>_Gender" value="<?= HtmlEncode($Grid->Gender->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->_Email->Visible) { // Email ?>
        <td data-name="_Email"<?= $Grid->_Email->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Email" class="el_users__Email">
<input type="<?= $Grid->_Email->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>__Email" id="x<?= $Grid->RowIndex ?>__Email" data-table="users" data-field="x__Email" value="<?= $Grid->_Email->getEditValue() ?>" size="50" maxlength="255" placeholder="<?= HtmlEncode($Grid->_Email->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->_Email->formatPattern()) ?>"<?= $Grid->_Email->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->_Email->getErrorMessage() ?></div>
</span>
<input type="hidden" data-table="users" data-field="x__Email" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>__Email" id="o<?= $Grid->RowIndex ?>__Email" value="<?= HtmlEncode($Grid->_Email->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Email" class="el_users__Email">
<input type="<?= $Grid->_Email->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>__Email" id="x<?= $Grid->RowIndex ?>__Email" data-table="users" data-field="x__Email" value="<?= $Grid->_Email->getEditValue() ?>" size="50" maxlength="255" placeholder="<?= HtmlEncode($Grid->_Email->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->_Email->formatPattern()) ?>"<?= $Grid->_Email->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->_Email->getErrorMessage() ?></div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users__Email" class="el_users__Email">
<span<?= $Grid->_Email->viewAttributes() ?>>
<?= $Grid->_Email->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x__Email" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>__Email" id="fusersgrid$x<?= $Grid->RowIndex ?>__Email" value="<?= HtmlEncode($Grid->_Email->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x__Email" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>__Email" id="fusersgrid$o<?= $Grid->RowIndex ?>__Email" value="<?= HtmlEncode($Grid->_Email->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Activated->Visible) { // Activated ?>
        <td data-name="Activated"<?= $Grid->Activated->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Activated" class="el_users_Activated">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Grid->Activated->isInvalidClass() ?>" data-table="users" data-field="x_Activated" data-boolean name="x<?= $Grid->RowIndex ?>_Activated" id="x<?= $Grid->RowIndex ?>_Activated" value="1"<?= ConvertToBool($Grid->Activated->CurrentValue) ? " checked" : "" ?><?= $Grid->Activated->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Grid->Activated->getErrorMessage() ?></div>
</div>
</span>
<input type="hidden" data-table="users" data-field="x_Activated" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Activated" id="o<?= $Grid->RowIndex ?>_Activated" value="<?= HtmlEncode($Grid->Activated->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Activated" class="el_users_Activated">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Grid->Activated->isInvalidClass() ?>" data-table="users" data-field="x_Activated" data-boolean name="x<?= $Grid->RowIndex ?>_Activated" id="x<?= $Grid->RowIndex ?>_Activated" value="1"<?= ConvertToBool($Grid->Activated->CurrentValue) ? " checked" : "" ?><?= $Grid->Activated->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Grid->Activated->getErrorMessage() ?></div>
</div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_Activated" class="el_users_Activated">
<span<?= $Grid->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Grid->RowCount ?>" class="form-check-input" value="<?= $Grid->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Grid->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Grid->RowCount ?>"></label>
</div>
</span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_Activated" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_Activated" id="fusersgrid$x<?= $Grid->RowIndex ?>_Activated" value="<?= HtmlEncode($Grid->Activated->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_Activated" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_Activated" id="fusersgrid$o<?= $Grid->RowIndex ?>_Activated" value="<?= HtmlEncode($Grid->Activated->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->ActiveStatus->Visible) { // ActiveStatus ?>
        <td data-name="ActiveStatus"<?= $Grid->ActiveStatus->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Grid->ActiveStatus->isInvalidClass() ?>" data-table="users" data-field="x_ActiveStatus" data-boolean name="x<?= $Grid->RowIndex ?>_ActiveStatus" id="x<?= $Grid->RowIndex ?>_ActiveStatus" value="1"<?= ConvertToBool($Grid->ActiveStatus->CurrentValue) ? " checked" : "" ?><?= $Grid->ActiveStatus->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Grid->ActiveStatus->getErrorMessage() ?></div>
</div>
</span>
<input type="hidden" data-table="users" data-field="x_ActiveStatus" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_ActiveStatus" id="o<?= $Grid->RowIndex ?>_ActiveStatus" value="<?= HtmlEncode($Grid->ActiveStatus->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Grid->ActiveStatus->isInvalidClass() ?>" data-table="users" data-field="x_ActiveStatus" data-boolean name="x<?= $Grid->RowIndex ?>_ActiveStatus" id="x<?= $Grid->RowIndex ?>_ActiveStatus" value="1"<?= ConvertToBool($Grid->ActiveStatus->CurrentValue) ? " checked" : "" ?><?= $Grid->ActiveStatus->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Grid->ActiveStatus->getErrorMessage() ?></div>
</div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_users_ActiveStatus" class="el_users_ActiveStatus">
<span<?= $Grid->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Grid->RowCount ?>" class="form-check-input" value="<?= $Grid->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Grid->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Grid->RowCount ?>"></label>
</div>
</span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="users" data-field="x_ActiveStatus" data-hidden="1" name="fusersgrid$x<?= $Grid->RowIndex ?>_ActiveStatus" id="fusersgrid$x<?= $Grid->RowIndex ?>_ActiveStatus" value="<?= HtmlEncode($Grid->ActiveStatus->FormValue) ?>">
<input type="hidden" data-table="users" data-field="x_ActiveStatus" data-hidden="1" data-old name="fusersgrid$o<?= $Grid->RowIndex ?>_ActiveStatus" id="fusersgrid$o<?= $Grid->RowIndex ?>_ActiveStatus" value="<?= HtmlEncode($Grid->ActiveStatus->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
<?php
// Render list options (body, right)
$Grid->ListOptions->render("body", "right", $Grid->RowCount);
?>
    </tr>
<?php if ($Grid->RowType == RowType::ADD || $Grid->RowType == RowType::EDIT) { ?>
<script<?= Nonce() ?> data-rowindex="<?= $Grid->RowIndex ?>">
loadjs.ready(["fusersgrid","load"], () => fusersgrid.updateLists(<?= $Grid->RowIndex ?><?= $Grid->isAdd() || $Grid->isEdit() || $Grid->isCopy() || $Grid->RowIndex === '$rowindex$' ? ", true" : "" ?>));
</script>
<?php } ?>
<?php
    }
    } // End delete row checking

    // Reset for template row
    if ($Grid->RowIndex === '$rowindex$') {
        $Grid->RowIndex = 0;
    }
    // Reset inline add/copy row
    if (($Grid->isCopy() || $Grid->isAdd()) && $Grid->RowIndex == 0) {
        $Grid->RowIndex = 1;
    }
}
?>
</tbody>
</table><!-- /.ew-table -->
<?php if ($Grid->CurrentMode == "add" || $Grid->CurrentMode == "copy") { ?>
<input type="hidden" name="<?= $Grid->getFormKeyCountName() ?>" id="<?= $Grid->getFormKeyCountName() ?>" value="<?= $Grid->KeyCount ?>">
<?= $Grid->MultiSelectKey ?>
<?php } ?>
<?php if ($Grid->CurrentMode == "edit") { ?>
<input type="hidden" name="<?= $Grid->getFormKeyCountName() ?>" id="<?= $Grid->getFormKeyCountName() ?>" value="<?= $Grid->KeyCount ?>">
<?= $Grid->MultiSelectKey ?>
<?php } ?>
</div><!-- /.ew-grid-middle-panel -->
<?php if ($Grid->CurrentMode == "") { ?>
<input type="hidden" name="action" id="action" value="">
<?php } ?>
<input type="hidden" name="detailpage" value="fusersgrid">
</div><!-- /.ew-list-form -->
<?php
// Close result set
$Grid->Result?->free();
?>
<?php if (!$Grid->isExport()) { ?>
<div class="card-footer ew-grid-lower-panel">
<?php if ($Grid->CurrentMode == "view" && $Grid->DetailViewPaging) { ?>
<?= $Grid->Pager->render() ?>
<?php } ?>
<div class="ew-list-other-options">
<?php $Grid->OtherOptions->render("body", "bottom") ?>
</div>
</div>
<?php } ?>
</div><!-- /.ew-grid -->
<?php } else { ?>
<div class="ew-list-other-options">
<?php $Grid->OtherOptions->render("body") ?>
</div>
<div class="clearfix"></div>
<?php } // end of Empty Table by Masino Sinaga, September 30, 2020 ?>
<?php } ?>
</div>
<div id="ew-footer-options">
<?php $Grid->FooterOptions?->render("body") ?>
</div>
</main>
<?php if (!$Grid->isExport()) { ?>
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
