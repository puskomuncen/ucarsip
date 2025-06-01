<?php

namespace PHPMaker2025\ucarsip;

// Set up and run Grid object
$Grid = Container("HelpGrid");
$Grid->run();
?>
<?php if (!$Grid->isExport()) { ?>
<script<?= Nonce() ?>>
var fhelpgrid;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let currentTable = <?= json_encode($Grid->toClientVar()) ?>;
    ew.deepAssign(ew.vars, { tables: { help: currentTable } });
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fhelpgrid")
        .setPageId("grid")
        .setFormKeyCountName("<?= $Grid->getFormKeyCountName() ?>")

        // Add fields
        .setFields([
            ["_Language", [fields._Language.visible && fields._Language.required ? ew.Validators.required(fields._Language.caption) : null], fields._Language.isInvalid],
            ["Topic", [fields.Topic.visible && fields.Topic.required ? ew.Validators.required(fields.Topic.caption) : null], fields.Topic.isInvalid],
            ["Description", [fields.Description.visible && fields.Description.required ? ew.Validators.required(fields.Description.caption) : null], fields.Description.isInvalid],
            ["Category", [fields.Category.visible && fields.Category.required ? ew.Validators.required(fields.Category.caption) : null], fields.Category.isInvalid]
        ])

        // Check empty row
        .setEmptyRow(
            function (rowIndex) {
                let fobj = this.getForm(),
                    fields = [["_Language",false],["Topic",false],["Description",false],["Category",false]];
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
            "_Language": <?= $Grid->_Language->toClientList($Grid) ?>,
            "Category": <?= $Grid->Category->toClientList($Grid) ?>,
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
<div id="fhelpgrid" class="ew-form ew-list-form">
<div id="gmp_help" class="card-body ew-grid-middle-panel <?= $Grid->TableContainerClass ?>" style="<?= $Grid->TableContainerStyle ?>">
<table id="tbl_helpgrid" class="<?= $Grid->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Grid->_Language->Visible) { // Language ?>
        <th data-name="_Language" class="<?= $Grid->_Language->headerCellClass() ?>"><div id="elh_help__Language" class="help__Language"><?= $Grid->renderFieldHeader($Grid->_Language) ?></div></th>
<?php } ?>
<?php if ($Grid->Topic->Visible) { // Topic ?>
        <th data-name="Topic" class="<?= $Grid->Topic->headerCellClass() ?>"><div id="elh_help_Topic" class="help_Topic"><?= $Grid->renderFieldHeader($Grid->Topic) ?></div></th>
<?php } ?>
<?php if ($Grid->Description->Visible) { // Description ?>
        <th data-name="Description" class="<?= $Grid->Description->headerCellClass() ?>"><div id="elh_help_Description" class="help_Description"><?= $Grid->renderFieldHeader($Grid->Description) ?></div></th>
<?php } ?>
<?php if ($Grid->Category->Visible) { // Category ?>
        <th data-name="Category" class="<?= $Grid->Category->headerCellClass() ?>"><div id="elh_help_Category" class="help_Category"><?= $Grid->renderFieldHeader($Grid->Category) ?></div></th>
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
    <?php if ($Grid->_Language->Visible) { // Language ?>
        <td data-name="_Language"<?= $Grid->_Language->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help__Language" class="el_help__Language">
    <select
        id="x<?= $Grid->RowIndex ?>__Language"
        name="x<?= $Grid->RowIndex ?>__Language"
        class="form-select ew-select<?= $Grid->_Language->isInvalidClass() ?>"
        <?php if (!$Grid->_Language->IsNativeSelect) { ?>
        data-select2-id="fhelpgrid_x<?= $Grid->RowIndex ?>__Language"
        <?php } ?>
        data-table="help"
        data-field="x__Language"
        data-value-separator="<?= $Grid->_Language->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->_Language->getPlaceHolder()) ?>"
        <?= $Grid->_Language->editAttributes() ?>>
        <?= $Grid->_Language->selectOptionListHtml("x{$Grid->RowIndex}__Language") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->_Language->getErrorMessage() ?></div>
<?= $Grid->_Language->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "__Language") ?>
<?php if (!$Grid->_Language->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelpgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>__Language", selectId: "fhelpgrid_x<?= $Grid->RowIndex ?>__Language" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelpgrid.lists._Language?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>__Language", form: "fhelpgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>__Language", form: "fhelpgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help.fields._Language.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<input type="hidden" data-table="help" data-field="x__Language" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>__Language" id="o<?= $Grid->RowIndex ?>__Language" value="<?= HtmlEncode($Grid->_Language->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help__Language" class="el_help__Language">
    <select
        id="x<?= $Grid->RowIndex ?>__Language"
        name="x<?= $Grid->RowIndex ?>__Language"
        class="form-select ew-select<?= $Grid->_Language->isInvalidClass() ?>"
        <?php if (!$Grid->_Language->IsNativeSelect) { ?>
        data-select2-id="fhelpgrid_x<?= $Grid->RowIndex ?>__Language"
        <?php } ?>
        data-table="help"
        data-field="x__Language"
        data-value-separator="<?= $Grid->_Language->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->_Language->getPlaceHolder()) ?>"
        <?= $Grid->_Language->editAttributes() ?>>
        <?= $Grid->_Language->selectOptionListHtml("x{$Grid->RowIndex}__Language") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->_Language->getErrorMessage() ?></div>
<?= $Grid->_Language->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "__Language") ?>
<?php if (!$Grid->_Language->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelpgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>__Language", selectId: "fhelpgrid_x<?= $Grid->RowIndex ?>__Language" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelpgrid.lists._Language?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>__Language", form: "fhelpgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>__Language", form: "fhelpgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help.fields._Language.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help__Language" class="el_help__Language">
<span<?= $Grid->_Language->viewAttributes() ?>>
<?= $Grid->_Language->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="help" data-field="x__Language" data-hidden="1" name="fhelpgrid$x<?= $Grid->RowIndex ?>__Language" id="fhelpgrid$x<?= $Grid->RowIndex ?>__Language" value="<?= HtmlEncode($Grid->_Language->FormValue) ?>">
<input type="hidden" data-table="help" data-field="x__Language" data-hidden="1" data-old name="fhelpgrid$o<?= $Grid->RowIndex ?>__Language" id="fhelpgrid$o<?= $Grid->RowIndex ?>__Language" value="<?= HtmlEncode($Grid->_Language->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Topic->Visible) { // Topic ?>
        <td data-name="Topic"<?= $Grid->Topic->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Topic" class="el_help_Topic">
<input type="<?= $Grid->Topic->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>_Topic" id="x<?= $Grid->RowIndex ?>_Topic" data-table="help" data-field="x_Topic" value="<?= $Grid->Topic->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Grid->Topic->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->Topic->formatPattern()) ?>"<?= $Grid->Topic->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->Topic->getErrorMessage() ?></div>
</span>
<input type="hidden" data-table="help" data-field="x_Topic" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Topic" id="o<?= $Grid->RowIndex ?>_Topic" value="<?= HtmlEncode($Grid->Topic->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Topic" class="el_help_Topic">
<input type="<?= $Grid->Topic->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>_Topic" id="x<?= $Grid->RowIndex ?>_Topic" data-table="help" data-field="x_Topic" value="<?= $Grid->Topic->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Grid->Topic->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->Topic->formatPattern()) ?>"<?= $Grid->Topic->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->Topic->getErrorMessage() ?></div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Topic" class="el_help_Topic">
<span<?= $Grid->Topic->viewAttributes() ?>>
<?= $Grid->Topic->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="help" data-field="x_Topic" data-hidden="1" name="fhelpgrid$x<?= $Grid->RowIndex ?>_Topic" id="fhelpgrid$x<?= $Grid->RowIndex ?>_Topic" value="<?= HtmlEncode($Grid->Topic->FormValue) ?>">
<input type="hidden" data-table="help" data-field="x_Topic" data-hidden="1" data-old name="fhelpgrid$o<?= $Grid->RowIndex ?>_Topic" id="fhelpgrid$o<?= $Grid->RowIndex ?>_Topic" value="<?= HtmlEncode($Grid->Topic->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Description->Visible) { // Description ?>
        <td data-name="Description"<?= $Grid->Description->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Description" class="el_help_Description">
<?php $Grid->Description->EditAttrs->appendClass("editor"); ?>
<textarea data-table="help" data-field="x_Description" name="x<?= $Grid->RowIndex ?>_Description" id="x<?= $Grid->RowIndex ?>_Description" cols="50" rows="5" placeholder="<?= HtmlEncode($Grid->Description->getPlaceHolder()) ?>"<?= $Grid->Description->editAttributes() ?>><?= $Grid->Description->getEditValue() ?></textarea>
<div class="invalid-feedback"><?= $Grid->Description->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(["fhelpgrid", "editor"], function() {
    ew.createEditor("fhelpgrid", "x<?= $Grid->RowIndex ?>_Description", 50, 5, <?= $Grid->Description->ReadOnly || false ? "true" : "false" ?>);
});
</script>
</span>
<input type="hidden" data-table="help" data-field="x_Description" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Description" id="o<?= $Grid->RowIndex ?>_Description" value="<?= HtmlEncode($Grid->Description->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Description" class="el_help_Description">
<?php $Grid->Description->EditAttrs->appendClass("editor"); ?>
<textarea data-table="help" data-field="x_Description" name="x<?= $Grid->RowIndex ?>_Description" id="x<?= $Grid->RowIndex ?>_Description" cols="50" rows="5" placeholder="<?= HtmlEncode($Grid->Description->getPlaceHolder()) ?>"<?= $Grid->Description->editAttributes() ?>><?= $Grid->Description->getEditValue() ?></textarea>
<div class="invalid-feedback"><?= $Grid->Description->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(["fhelpgrid", "editor"], function() {
    ew.createEditor("fhelpgrid", "x<?= $Grid->RowIndex ?>_Description", 50, 5, <?= $Grid->Description->ReadOnly || false ? "true" : "false" ?>);
});
</script>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Description" class="el_help_Description">
<span<?= $Grid->Description->viewAttributes() ?>>
<?= $Grid->Description->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="help" data-field="x_Description" data-hidden="1" name="fhelpgrid$x<?= $Grid->RowIndex ?>_Description" id="fhelpgrid$x<?= $Grid->RowIndex ?>_Description" value="<?= HtmlEncode($Grid->Description->FormValue) ?>">
<input type="hidden" data-table="help" data-field="x_Description" data-hidden="1" data-old name="fhelpgrid$o<?= $Grid->RowIndex ?>_Description" id="fhelpgrid$o<?= $Grid->RowIndex ?>_Description" value="<?= HtmlEncode($Grid->Description->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Category->Visible) { // Category ?>
        <td data-name="Category"<?= $Grid->Category->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<?php if ($Grid->Category->getSessionValue() != "") { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
<span<?= $Grid->Category->viewAttributes() ?>>
<span class="form-control-plaintext"><?= $Grid->Category->getDisplayValue($Grid->Category->ViewValue) ?></span></span>
<input type="hidden" id="x<?= $Grid->RowIndex ?>_Category" name="x<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->CurrentValue) ?>" data-hidden="1">
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
    <select
        id="x<?= $Grid->RowIndex ?>_Category"
        name="x<?= $Grid->RowIndex ?>_Category"
        class="form-select ew-select<?= $Grid->Category->isInvalidClass() ?>"
        <?php if (!$Grid->Category->IsNativeSelect) { ?>
        data-select2-id="fhelpgrid_x<?= $Grid->RowIndex ?>_Category"
        <?php } ?>
        data-table="help"
        data-field="x_Category"
        data-value-separator="<?= $Grid->Category->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->Category->getPlaceHolder()) ?>"
        <?= $Grid->Category->editAttributes() ?>>
        <?= $Grid->Category->selectOptionListHtml("x{$Grid->RowIndex}_Category") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->Category->getErrorMessage() ?></div>
<?= $Grid->Category->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "_Category") ?>
<?php if (!$Grid->Category->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelpgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_Category", selectId: "fhelpgrid_x<?= $Grid->RowIndex ?>_Category" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelpgrid.lists.Category?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_Category", form: "fhelpgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_Category", form: "fhelpgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help.fields.Category.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<input type="hidden" data-table="help" data-field="x_Category" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Category" id="o<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<?php if ($Grid->Category->getSessionValue() != "") { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
<span<?= $Grid->Category->viewAttributes() ?>>
<span class="form-control-plaintext"><?= $Grid->Category->getDisplayValue($Grid->Category->ViewValue) ?></span></span>
<input type="hidden" id="x<?= $Grid->RowIndex ?>_Category" name="x<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->CurrentValue) ?>" data-hidden="1">
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
    <select
        id="x<?= $Grid->RowIndex ?>_Category"
        name="x<?= $Grid->RowIndex ?>_Category"
        class="form-select ew-select<?= $Grid->Category->isInvalidClass() ?>"
        <?php if (!$Grid->Category->IsNativeSelect) { ?>
        data-select2-id="fhelpgrid_x<?= $Grid->RowIndex ?>_Category"
        <?php } ?>
        data-table="help"
        data-field="x_Category"
        data-value-separator="<?= $Grid->Category->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->Category->getPlaceHolder()) ?>"
        <?= $Grid->Category->editAttributes() ?>>
        <?= $Grid->Category->selectOptionListHtml("x{$Grid->RowIndex}_Category") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->Category->getErrorMessage() ?></div>
<?= $Grid->Category->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "_Category") ?>
<?php if (!$Grid->Category->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelpgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_Category", selectId: "fhelpgrid_x<?= $Grid->RowIndex ?>_Category" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelpgrid.lists.Category?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_Category", form: "fhelpgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_Category", form: "fhelpgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help.fields.Category.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
<span<?= $Grid->Category->viewAttributes() ?>>
<?= $Grid->Category->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="help" data-field="x_Category" data-hidden="1" name="fhelpgrid$x<?= $Grid->RowIndex ?>_Category" id="fhelpgrid$x<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->FormValue) ?>">
<input type="hidden" data-table="help" data-field="x_Category" data-hidden="1" data-old name="fhelpgrid$o<?= $Grid->RowIndex ?>_Category" id="fhelpgrid$o<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->OldValue) ?>">
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
loadjs.ready(["fhelpgrid","load"], () => fhelpgrid.updateLists(<?= $Grid->RowIndex ?><?= $Grid->isAdd() || $Grid->isEdit() || $Grid->isCopy() || $Grid->RowIndex === '$rowindex$' ? ", true" : "" ?>));
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
<input type="hidden" name="detailpage" value="fhelpgrid">
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
<div id="fhelpgrid" class="ew-form ew-list-form">
<div id="gmp_help" class="card-body ew-grid-middle-panel <?= $Grid->TableContainerClass ?>" style="<?= $Grid->TableContainerStyle ?>">
<table id="tbl_helpgrid" class="<?= $Grid->TableClass ?>"><!-- .ew-table -->
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
<?php if ($Grid->_Language->Visible) { // Language ?>
        <th data-name="_Language" class="<?= $Grid->_Language->headerCellClass() ?>"><div id="elh_help__Language" class="help__Language"><?= $Grid->renderFieldHeader($Grid->_Language) ?></div></th>
<?php } ?>
<?php if ($Grid->Topic->Visible) { // Topic ?>
        <th data-name="Topic" class="<?= $Grid->Topic->headerCellClass() ?>"><div id="elh_help_Topic" class="help_Topic"><?= $Grid->renderFieldHeader($Grid->Topic) ?></div></th>
<?php } ?>
<?php if ($Grid->Description->Visible) { // Description ?>
        <th data-name="Description" class="<?= $Grid->Description->headerCellClass() ?>"><div id="elh_help_Description" class="help_Description"><?= $Grid->renderFieldHeader($Grid->Description) ?></div></th>
<?php } ?>
<?php if ($Grid->Category->Visible) { // Category ?>
        <th data-name="Category" class="<?= $Grid->Category->headerCellClass() ?>"><div id="elh_help_Category" class="help_Category"><?= $Grid->renderFieldHeader($Grid->Category) ?></div></th>
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
    <?php if ($Grid->_Language->Visible) { // Language ?>
        <td data-name="_Language"<?= $Grid->_Language->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help__Language" class="el_help__Language">
    <select
        id="x<?= $Grid->RowIndex ?>__Language"
        name="x<?= $Grid->RowIndex ?>__Language"
        class="form-select ew-select<?= $Grid->_Language->isInvalidClass() ?>"
        <?php if (!$Grid->_Language->IsNativeSelect) { ?>
        data-select2-id="fhelpgrid_x<?= $Grid->RowIndex ?>__Language"
        <?php } ?>
        data-table="help"
        data-field="x__Language"
        data-value-separator="<?= $Grid->_Language->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->_Language->getPlaceHolder()) ?>"
        <?= $Grid->_Language->editAttributes() ?>>
        <?= $Grid->_Language->selectOptionListHtml("x{$Grid->RowIndex}__Language") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->_Language->getErrorMessage() ?></div>
<?= $Grid->_Language->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "__Language") ?>
<?php if (!$Grid->_Language->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelpgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>__Language", selectId: "fhelpgrid_x<?= $Grid->RowIndex ?>__Language" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelpgrid.lists._Language?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>__Language", form: "fhelpgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>__Language", form: "fhelpgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help.fields._Language.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<input type="hidden" data-table="help" data-field="x__Language" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>__Language" id="o<?= $Grid->RowIndex ?>__Language" value="<?= HtmlEncode($Grid->_Language->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help__Language" class="el_help__Language">
    <select
        id="x<?= $Grid->RowIndex ?>__Language"
        name="x<?= $Grid->RowIndex ?>__Language"
        class="form-select ew-select<?= $Grid->_Language->isInvalidClass() ?>"
        <?php if (!$Grid->_Language->IsNativeSelect) { ?>
        data-select2-id="fhelpgrid_x<?= $Grid->RowIndex ?>__Language"
        <?php } ?>
        data-table="help"
        data-field="x__Language"
        data-value-separator="<?= $Grid->_Language->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->_Language->getPlaceHolder()) ?>"
        <?= $Grid->_Language->editAttributes() ?>>
        <?= $Grid->_Language->selectOptionListHtml("x{$Grid->RowIndex}__Language") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->_Language->getErrorMessage() ?></div>
<?= $Grid->_Language->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "__Language") ?>
<?php if (!$Grid->_Language->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelpgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>__Language", selectId: "fhelpgrid_x<?= $Grid->RowIndex ?>__Language" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelpgrid.lists._Language?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>__Language", form: "fhelpgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>__Language", form: "fhelpgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help.fields._Language.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help__Language" class="el_help__Language">
<span<?= $Grid->_Language->viewAttributes() ?>>
<?= $Grid->_Language->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="help" data-field="x__Language" data-hidden="1" name="fhelpgrid$x<?= $Grid->RowIndex ?>__Language" id="fhelpgrid$x<?= $Grid->RowIndex ?>__Language" value="<?= HtmlEncode($Grid->_Language->FormValue) ?>">
<input type="hidden" data-table="help" data-field="x__Language" data-hidden="1" data-old name="fhelpgrid$o<?= $Grid->RowIndex ?>__Language" id="fhelpgrid$o<?= $Grid->RowIndex ?>__Language" value="<?= HtmlEncode($Grid->_Language->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Topic->Visible) { // Topic ?>
        <td data-name="Topic"<?= $Grid->Topic->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Topic" class="el_help_Topic">
<input type="<?= $Grid->Topic->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>_Topic" id="x<?= $Grid->RowIndex ?>_Topic" data-table="help" data-field="x_Topic" value="<?= $Grid->Topic->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Grid->Topic->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->Topic->formatPattern()) ?>"<?= $Grid->Topic->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->Topic->getErrorMessage() ?></div>
</span>
<input type="hidden" data-table="help" data-field="x_Topic" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Topic" id="o<?= $Grid->RowIndex ?>_Topic" value="<?= HtmlEncode($Grid->Topic->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Topic" class="el_help_Topic">
<input type="<?= $Grid->Topic->getInputTextType() ?>" name="x<?= $Grid->RowIndex ?>_Topic" id="x<?= $Grid->RowIndex ?>_Topic" data-table="help" data-field="x_Topic" value="<?= $Grid->Topic->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Grid->Topic->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Grid->Topic->formatPattern()) ?>"<?= $Grid->Topic->editAttributes() ?>>
<div class="invalid-feedback"><?= $Grid->Topic->getErrorMessage() ?></div>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Topic" class="el_help_Topic">
<span<?= $Grid->Topic->viewAttributes() ?>>
<?= $Grid->Topic->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="help" data-field="x_Topic" data-hidden="1" name="fhelpgrid$x<?= $Grid->RowIndex ?>_Topic" id="fhelpgrid$x<?= $Grid->RowIndex ?>_Topic" value="<?= HtmlEncode($Grid->Topic->FormValue) ?>">
<input type="hidden" data-table="help" data-field="x_Topic" data-hidden="1" data-old name="fhelpgrid$o<?= $Grid->RowIndex ?>_Topic" id="fhelpgrid$o<?= $Grid->RowIndex ?>_Topic" value="<?= HtmlEncode($Grid->Topic->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Description->Visible) { // Description ?>
        <td data-name="Description"<?= $Grid->Description->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Description" class="el_help_Description">
<?php $Grid->Description->EditAttrs->appendClass("editor"); ?>
<textarea data-table="help" data-field="x_Description" name="x<?= $Grid->RowIndex ?>_Description" id="x<?= $Grid->RowIndex ?>_Description" cols="50" rows="5" placeholder="<?= HtmlEncode($Grid->Description->getPlaceHolder()) ?>"<?= $Grid->Description->editAttributes() ?>><?= $Grid->Description->getEditValue() ?></textarea>
<div class="invalid-feedback"><?= $Grid->Description->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(["fhelpgrid", "editor"], function() {
    ew.createEditor("fhelpgrid", "x<?= $Grid->RowIndex ?>_Description", 50, 5, <?= $Grid->Description->ReadOnly || false ? "true" : "false" ?>);
});
</script>
</span>
<input type="hidden" data-table="help" data-field="x_Description" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Description" id="o<?= $Grid->RowIndex ?>_Description" value="<?= HtmlEncode($Grid->Description->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Description" class="el_help_Description">
<?php $Grid->Description->EditAttrs->appendClass("editor"); ?>
<textarea data-table="help" data-field="x_Description" name="x<?= $Grid->RowIndex ?>_Description" id="x<?= $Grid->RowIndex ?>_Description" cols="50" rows="5" placeholder="<?= HtmlEncode($Grid->Description->getPlaceHolder()) ?>"<?= $Grid->Description->editAttributes() ?>><?= $Grid->Description->getEditValue() ?></textarea>
<div class="invalid-feedback"><?= $Grid->Description->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(["fhelpgrid", "editor"], function() {
    ew.createEditor("fhelpgrid", "x<?= $Grid->RowIndex ?>_Description", 50, 5, <?= $Grid->Description->ReadOnly || false ? "true" : "false" ?>);
});
</script>
</span>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Description" class="el_help_Description">
<span<?= $Grid->Description->viewAttributes() ?>>
<?= $Grid->Description->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="help" data-field="x_Description" data-hidden="1" name="fhelpgrid$x<?= $Grid->RowIndex ?>_Description" id="fhelpgrid$x<?= $Grid->RowIndex ?>_Description" value="<?= HtmlEncode($Grid->Description->FormValue) ?>">
<input type="hidden" data-table="help" data-field="x_Description" data-hidden="1" data-old name="fhelpgrid$o<?= $Grid->RowIndex ?>_Description" id="fhelpgrid$o<?= $Grid->RowIndex ?>_Description" value="<?= HtmlEncode($Grid->Description->OldValue) ?>">
<?php } ?>
<?php } ?>
</td>
    <?php } ?>
    <?php if ($Grid->Category->Visible) { // Category ?>
        <td data-name="Category"<?= $Grid->Category->cellAttributes() ?>>
<?php if ($Grid->RowType == RowType::ADD) { // Add record ?>
<?php if ($Grid->Category->getSessionValue() != "") { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
<span<?= $Grid->Category->viewAttributes() ?>>
<span class="form-control-plaintext"><?= $Grid->Category->getDisplayValue($Grid->Category->ViewValue) ?></span></span>
<input type="hidden" id="x<?= $Grid->RowIndex ?>_Category" name="x<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->CurrentValue) ?>" data-hidden="1">
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
    <select
        id="x<?= $Grid->RowIndex ?>_Category"
        name="x<?= $Grid->RowIndex ?>_Category"
        class="form-select ew-select<?= $Grid->Category->isInvalidClass() ?>"
        <?php if (!$Grid->Category->IsNativeSelect) { ?>
        data-select2-id="fhelpgrid_x<?= $Grid->RowIndex ?>_Category"
        <?php } ?>
        data-table="help"
        data-field="x_Category"
        data-value-separator="<?= $Grid->Category->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->Category->getPlaceHolder()) ?>"
        <?= $Grid->Category->editAttributes() ?>>
        <?= $Grid->Category->selectOptionListHtml("x{$Grid->RowIndex}_Category") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->Category->getErrorMessage() ?></div>
<?= $Grid->Category->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "_Category") ?>
<?php if (!$Grid->Category->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelpgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_Category", selectId: "fhelpgrid_x<?= $Grid->RowIndex ?>_Category" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelpgrid.lists.Category?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_Category", form: "fhelpgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_Category", form: "fhelpgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help.fields.Category.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<input type="hidden" data-table="help" data-field="x_Category" data-hidden="1" data-old name="o<?= $Grid->RowIndex ?>_Category" id="o<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->OldValue) ?>">
<?php } ?>
<?php if ($Grid->RowType == RowType::EDIT) { // Edit record ?>
<?php if ($Grid->Category->getSessionValue() != "") { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
<span<?= $Grid->Category->viewAttributes() ?>>
<span class="form-control-plaintext"><?= $Grid->Category->getDisplayValue($Grid->Category->ViewValue) ?></span></span>
<input type="hidden" id="x<?= $Grid->RowIndex ?>_Category" name="x<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->CurrentValue) ?>" data-hidden="1">
</span>
<?php } else { ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
    <select
        id="x<?= $Grid->RowIndex ?>_Category"
        name="x<?= $Grid->RowIndex ?>_Category"
        class="form-select ew-select<?= $Grid->Category->isInvalidClass() ?>"
        <?php if (!$Grid->Category->IsNativeSelect) { ?>
        data-select2-id="fhelpgrid_x<?= $Grid->RowIndex ?>_Category"
        <?php } ?>
        data-table="help"
        data-field="x_Category"
        data-value-separator="<?= $Grid->Category->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Grid->Category->getPlaceHolder()) ?>"
        <?= $Grid->Category->editAttributes() ?>>
        <?= $Grid->Category->selectOptionListHtml("x{$Grid->RowIndex}_Category") ?>
    </select>
    <div class="invalid-feedback"><?= $Grid->Category->getErrorMessage() ?></div>
<?= $Grid->Category->Lookup->getParamTag($Grid, "p_x" . $Grid->RowIndex . "_Category") ?>
<?php if (!$Grid->Category->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelpgrid", function() {
    var options = { name: "x<?= $Grid->RowIndex ?>_Category", selectId: "fhelpgrid_x<?= $Grid->RowIndex ?>_Category" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelpgrid.lists.Category?.lookupOptions.length) {
        options.data = { id: "x<?= $Grid->RowIndex ?>_Category", form: "fhelpgrid" };
    } else {
        options.ajax = { id: "x<?= $Grid->RowIndex ?>_Category", form: "fhelpgrid", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help.fields.Category.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
<?php } ?>
<?php } ?>
<?php if ($Grid->RowType == RowType::VIEW) { // View record ?>
<span id="el<?= $Grid->RowIndex == '$rowindex$' ? '$rowindex$' : $Grid->RowCount ?>_help_Category" class="el_help_Category">
<span<?= $Grid->Category->viewAttributes() ?>>
<?= $Grid->Category->getViewValue() ?></span>
</span>
<?php if ($Grid->isConfirm()) { ?>
<input type="hidden" data-table="help" data-field="x_Category" data-hidden="1" name="fhelpgrid$x<?= $Grid->RowIndex ?>_Category" id="fhelpgrid$x<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->FormValue) ?>">
<input type="hidden" data-table="help" data-field="x_Category" data-hidden="1" data-old name="fhelpgrid$o<?= $Grid->RowIndex ?>_Category" id="fhelpgrid$o<?= $Grid->RowIndex ?>_Category" value="<?= HtmlEncode($Grid->Category->OldValue) ?>">
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
loadjs.ready(["fhelpgrid","load"], () => fhelpgrid.updateLists(<?= $Grid->RowIndex ?><?= $Grid->isAdd() || $Grid->isEdit() || $Grid->isCopy() || $Grid->RowIndex === '$rowindex$' ? ", true" : "" ?>));
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
<input type="hidden" name="detailpage" value="fhelpgrid">
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
    ew.addEventHandlers("help");
});
</script>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
