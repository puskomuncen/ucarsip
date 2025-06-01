<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UsersSearch = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { users: currentTable } });
var currentPageID = ew.PAGE_ID = "search";
var currentForm;
var fuserssearch, currentSearchForm, currentAdvancedSearchForm;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery,
        fields = currentTable.fields;

    // Form object for search
    let form = new ew.FormBuilder()
        .setId("fuserssearch")
        .setPageId("search")
<?php if ($Page->IsModal && $Page->UseAjaxActions) { ?>
        .setSubmitWithFetch(true)
<?php } ?>

        // Add fields
        .addFields([
            ["_UserID", [ew.Validators.integer], fields._UserID.isInvalid],
            ["_Username", [], fields._Username.isInvalid],
            ["UserLevel", [], fields.UserLevel.isInvalid],
            ["FirstName", [], fields.FirstName.isInvalid],
            ["LastName", [], fields.LastName.isInvalid],
            ["CompleteName", [], fields.CompleteName.isInvalid],
            ["BirthDate", [ew.Validators.datetime(fields.BirthDate.clientFormatPattern)], fields.BirthDate.isInvalid],
            ["y_BirthDate", [ew.Validators.between], false],
            ["HomePhone", [], fields.HomePhone.isInvalid],
            ["Photo", [], fields.Photo.isInvalid],
            ["Notes", [], fields.Notes.isInvalid],
            ["ReportsTo", [ew.Validators.integer], fields.ReportsTo.isInvalid],
            ["Gender", [], fields.Gender.isInvalid],
            ["_Email", [], fields._Email.isInvalid],
            ["Activated", [], fields.Activated.isInvalid],
            ["Avatar", [], fields.Avatar.isInvalid],
            ["ActiveStatus", [], fields.ActiveStatus.isInvalid],
            ["MessengerColor", [], fields.MessengerColor.isInvalid],
            ["CreatedAt", [ew.Validators.datetime(fields.CreatedAt.clientFormatPattern)], fields.CreatedAt.isInvalid],
            ["y_CreatedAt", [ew.Validators.between], false],
            ["CreatedBy", [], fields.CreatedBy.isInvalid],
            ["UpdatedAt", [ew.Validators.datetime(fields.UpdatedAt.clientFormatPattern)], fields.UpdatedAt.isInvalid],
            ["y_UpdatedAt", [ew.Validators.between], false],
            ["UpdatedBy", [], fields.UpdatedBy.isInvalid]
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
            "Gender": <?= $Page->Gender->toClientList($Page) ?>,
            "Activated": <?= $Page->Activated->toClientList($Page) ?>,
            "ActiveStatus": <?= $Page->ActiveStatus->toClientList($Page) ?>,
            "CreatedBy": <?= $Page->CreatedBy->toClientList($Page) ?>,
            "UpdatedBy": <?= $Page->UpdatedBy->toClientList($Page) ?>,
        })
        .build();
    window[form.id] = form;
<?php if ($Page->IsModal) { ?>
    currentAdvancedSearchForm = form;
<?php } else { ?>
    currentForm = form;
<?php } ?>
    loadjs.done(form.id);
});
</script>
<script<?= Nonce() ?>>
loadjs.ready("head", function () {
    // Write your table-specific client script here, no need to add script tags.
});
</script>
<?php $Page->showPageHeader(); ?>
<?php
$Page->showMessage();
?>
<?php // Begin of Card view by Masino Sinaga, September 10, 2023 ?>
<?php if (!$Page->IsModal) { ?>
<div class="col-md-12">
  <div class="card shadow-sm">
    <div class="card-header">
	  <h4 class="card-title"><?php echo Language()->phrase("SearchCaption"); ?></h4>
	  <div class="card-tools">
	  <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
	  </button>
	  </div>
	  <!-- /.card-tools -->
    </div>
    <!-- /.card-header -->
    <div class="card-body">
<?php } ?>
<?php // End of Card view by Masino Sinaga, September 10, 2023 ?>
<form name="fuserssearch" id="fuserssearch" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="users">
<input type="hidden" name="action" id="action" value="search">
<?php if ($Page->IsModal) { ?>
<input type="hidden" name="modal" value="1">
<?php } ?>
<div class="ew-search-div"><!-- page* -->
<?php if ($Page->_UserID->Visible) { // UserID ?>
    <div id="r__UserID" class="row"<?= $Page->_UserID->rowAttributes() ?>>
        <label for="x__UserID" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users__UserID"><?= $Page->_UserID->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z__UserID" id="z__UserID" value="=">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->_UserID->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users__UserID" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->_UserID->getInputTextType() ?>" name="x__UserID" id="x__UserID" data-table="users" data-field="x__UserID" value="<?= $Page->_UserID->getEditValue() ?>" placeholder="<?= HtmlEncode($Page->_UserID->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->_UserID->formatPattern()) ?>"<?= $Page->_UserID->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->_UserID->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
    <div id="r__Username" class="row"<?= $Page->_Username->rowAttributes() ?>>
        <label for="x__Username" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users__Username"><?= $Page->_Username->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z__Username" id="z__Username" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->_Username->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users__Username" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->_Username->getInputTextType() ?>" name="x__Username" id="x__Username" data-table="users" data-field="x__Username" value="<?= $Page->_Username->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->_Username->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->_Username->formatPattern()) ?>"<?= $Page->_Username->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->_Username->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
    <div id="r_UserLevel" class="row"<?= $Page->UserLevel->rowAttributes() ?>>
        <label for="x_UserLevel" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_UserLevel"><?= $Page->UserLevel->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_UserLevel" id="z_UserLevel" value="=">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->UserLevel->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_UserLevel" class="ew-search-field ew-search-field-single">
<?php if (!$Security->canAccess() && $Security->isLoggedIn()) { // No access permission ?>
<span class="form-control-plaintext"><?= $Page->UserLevel->getDisplayValue($Page->UserLevel->getEditValue()) ?></span>
<?php } else { ?>
    <select
        id="x_UserLevel"
        name="x_UserLevel"
        class="form-select ew-select<?= $Page->UserLevel->isInvalidClass() ?>"
        <?php if (!$Page->UserLevel->IsNativeSelect) { ?>
        data-select2-id="fuserssearch_x_UserLevel"
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
loadjs.ready("fuserssearch", function() {
    var options = { name: "x_UserLevel", selectId: "fuserssearch_x_UserLevel" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fuserssearch.lists.UserLevel?.lookupOptions.length) {
        options.data = { id: "x_UserLevel", form: "fuserssearch" };
    } else {
        options.ajax = { id: "x_UserLevel", form: "fuserssearch", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.UserLevel.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
<?php } ?>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->FirstName->Visible) { // FirstName ?>
    <div id="r_FirstName" class="row"<?= $Page->FirstName->rowAttributes() ?>>
        <label for="x_FirstName" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_FirstName"><?= $Page->FirstName->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z_FirstName" id="z_FirstName" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->FirstName->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_FirstName" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->FirstName->getInputTextType() ?>" name="x_FirstName" id="x_FirstName" data-table="users" data-field="x_FirstName" value="<?= $Page->FirstName->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->FirstName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->FirstName->formatPattern()) ?>"<?= $Page->FirstName->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->FirstName->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->LastName->Visible) { // LastName ?>
    <div id="r_LastName" class="row"<?= $Page->LastName->rowAttributes() ?>>
        <label for="x_LastName" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_LastName"><?= $Page->LastName->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z_LastName" id="z_LastName" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->LastName->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_LastName" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->LastName->getInputTextType() ?>" name="x_LastName" id="x_LastName" data-table="users" data-field="x_LastName" value="<?= $Page->LastName->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->LastName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->LastName->formatPattern()) ?>"<?= $Page->LastName->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->LastName->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
    <div id="r_CompleteName" class="row"<?= $Page->CompleteName->rowAttributes() ?>>
        <label for="x_CompleteName" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_CompleteName"><?= $Page->CompleteName->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z_CompleteName" id="z_CompleteName" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->CompleteName->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_CompleteName" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->CompleteName->getInputTextType() ?>" name="x_CompleteName" id="x_CompleteName" data-table="users" data-field="x_CompleteName" value="<?= $Page->CompleteName->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->CompleteName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->CompleteName->formatPattern()) ?>"<?= $Page->CompleteName->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->CompleteName->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->BirthDate->Visible) { // BirthDate ?>
    <div id="r_BirthDate" class="row"<?= $Page->BirthDate->rowAttributes() ?>>
        <label for="x_BirthDate" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_BirthDate"><?= $Page->BirthDate->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("BETWEEN") ?>
<input type="hidden" name="z_BirthDate" id="z_BirthDate" value="BETWEEN">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->BirthDate->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_BirthDate" class="ew-search-field">
<input type="<?= $Page->BirthDate->getInputTextType() ?>" name="x_BirthDate" id="x_BirthDate" data-table="users" data-field="x_BirthDate" value="<?= $Page->BirthDate->getEditValue() ?>" size="12" maxlength="10" placeholder="<?= HtmlEncode($Page->BirthDate->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->BirthDate->formatPattern()) ?>"<?= $Page->BirthDate->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->BirthDate->getErrorMessage(false) ?></div>
<?php if (!$Page->BirthDate->ReadOnly && !$Page->BirthDate->Disabled && !isset($Page->BirthDate->EditAttrs["readonly"]) && !isset($Page->BirthDate->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fuserssearch", "datetimepicker"], function () {
    let format = "<?= DateFormat(2) ?>",
        options = {
            localization: {
                locale: ew.LANGUAGE_ID + "-u-nu-" + ew.getNumberingSystem(),
                hourCycle: format.match(/H/) ? "h24" : "h12",
                format,
                ...ew.language.phrase("datetimepicker")
            },
            display: {
                icons: {
                    previous: ew.IS_RTL ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left",
                    next: ew.IS_RTL ? "fa-solid fa-chevron-left" : "fa-solid fa-chevron-right"
                },
                components: {
                    clock: !!format.match(/h/i) || !!format.match(/m/) || !!format.match(/s/i),
                    hours: !!format.match(/h/i),
                    minutes: !!format.match(/m/),
                    seconds: !!format.match(/s/i)
                },
                theme: ew.getPreferredTheme()
            }
        };
    ew.createDateTimePicker(
        "fuserssearch",
        "x_BirthDate",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fuserssearch', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserssearch", "x_BirthDate", jQuery.extend(true, "", options));
});
</script>
</span>
                    <span class="ew-search-and"><label><?= $Language->phrase("AND") ?></label></span>
                    <span id="el2_users_BirthDate" class="ew-search-field2">
<input type="<?= $Page->BirthDate->getInputTextType() ?>" name="y_BirthDate" id="y_BirthDate" data-table="users" data-field="x_BirthDate" value="<?= $Page->BirthDate->EditValue2 ?>" size="12" maxlength="10" placeholder="<?= HtmlEncode($Page->BirthDate->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->BirthDate->formatPattern()) ?>"<?= $Page->BirthDate->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->BirthDate->getErrorMessage(false) ?></div>
<?php if (!$Page->BirthDate->ReadOnly && !$Page->BirthDate->Disabled && !isset($Page->BirthDate->EditAttrs["readonly"]) && !isset($Page->BirthDate->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fuserssearch", "datetimepicker"], function () {
    let format = "<?= DateFormat(2) ?>",
        options = {
            localization: {
                locale: ew.LANGUAGE_ID + "-u-nu-" + ew.getNumberingSystem(),
                hourCycle: format.match(/H/) ? "h24" : "h12",
                format,
                ...ew.language.phrase("datetimepicker")
            },
            display: {
                icons: {
                    previous: ew.IS_RTL ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left",
                    next: ew.IS_RTL ? "fa-solid fa-chevron-left" : "fa-solid fa-chevron-right"
                },
                components: {
                    clock: !!format.match(/h/i) || !!format.match(/m/) || !!format.match(/s/i),
                    hours: !!format.match(/h/i),
                    minutes: !!format.match(/m/),
                    seconds: !!format.match(/s/i)
                },
                theme: ew.getPreferredTheme()
            }
        };
    ew.createDateTimePicker(
        "fuserssearch",
        "y_BirthDate",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fuserssearch', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserssearch", "y_BirthDate", jQuery.extend(true, "", options));
});
</script>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->HomePhone->Visible) { // HomePhone ?>
    <div id="r_HomePhone" class="row"<?= $Page->HomePhone->rowAttributes() ?>>
        <label for="x_HomePhone" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_HomePhone"><?= $Page->HomePhone->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z_HomePhone" id="z_HomePhone" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->HomePhone->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_HomePhone" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->HomePhone->getInputTextType() ?>" name="x_HomePhone" id="x_HomePhone" data-table="users" data-field="x_HomePhone" value="<?= $Page->HomePhone->getEditValue() ?>" size="30" maxlength="24" placeholder="<?= HtmlEncode($Page->HomePhone->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->HomePhone->formatPattern()) ?>"<?= $Page->HomePhone->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->HomePhone->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
    <div id="r_Photo" class="row"<?= $Page->Photo->rowAttributes() ?>>
        <label class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_Photo"><?= $Page->Photo->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z_Photo" id="z_Photo" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->Photo->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_Photo" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->Photo->getInputTextType() ?>" name="x_Photo" id="x_Photo" data-table="users" data-field="x_Photo" value="<?= $Page->Photo->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->Photo->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Photo->formatPattern()) ?>"<?= $Page->Photo->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->Photo->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->Notes->Visible) { // Notes ?>
    <div id="r_Notes" class="row"<?= $Page->Notes->rowAttributes() ?>>
        <label for="x_Notes" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_Notes"><?= $Page->Notes->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z_Notes" id="z_Notes" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->Notes->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_Notes" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->Notes->getInputTextType() ?>" name="x_Notes" id="x_Notes" data-table="users" data-field="x_Notes" value="<?= $Page->Notes->getEditValue() ?>" size="50" placeholder="<?= HtmlEncode($Page->Notes->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Notes->formatPattern()) ?>"<?= $Page->Notes->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->Notes->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->ReportsTo->Visible) { // ReportsTo ?>
    <div id="r_ReportsTo" class="row"<?= $Page->ReportsTo->rowAttributes() ?>>
        <label for="x_ReportsTo" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_ReportsTo"><?= $Page->ReportsTo->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_ReportsTo" id="z_ReportsTo" value="=">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->ReportsTo->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_ReportsTo" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->ReportsTo->getInputTextType() ?>" name="x_ReportsTo" id="x_ReportsTo" data-table="users" data-field="x_ReportsTo" value="<?= $Page->ReportsTo->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->ReportsTo->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->ReportsTo->formatPattern()) ?>"<?= $Page->ReportsTo->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->ReportsTo->getErrorMessage(false) ?></div>
<script<?= Nonce() ?>>
loadjs.ready(['fuserssearch', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserssearch", "x_ReportsTo", jQuery.extend(true, "", options));
});
</script>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
    <div id="r_Gender" class="row"<?= $Page->Gender->rowAttributes() ?>>
        <label for="x_Gender" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_Gender"><?= $Page->Gender->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_Gender" id="z_Gender" value="=">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->Gender->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_Gender" class="ew-search-field ew-search-field-single">
    <select
        id="x_Gender"
        name="x_Gender"
        class="form-select ew-select<?= $Page->Gender->isInvalidClass() ?>"
        <?php if (!$Page->Gender->IsNativeSelect) { ?>
        data-select2-id="fuserssearch_x_Gender"
        <?php } ?>
        data-table="users"
        data-field="x_Gender"
        data-value-separator="<?= $Page->Gender->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->Gender->getPlaceHolder()) ?>"
        <?= $Page->Gender->editAttributes() ?>>
        <?= $Page->Gender->selectOptionListHtml("x_Gender") ?>
    </select>
    <div class="invalid-feedback"><?= $Page->Gender->getErrorMessage(false) ?></div>
<?php if (!$Page->Gender->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fuserssearch", function() {
    var options = { name: "x_Gender", selectId: "fuserssearch_x_Gender" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fuserssearch.lists.Gender?.lookupOptions.length) {
        options.data = { id: "x_Gender", form: "fuserssearch" };
    } else {
        options.ajax = { id: "x_Gender", form: "fuserssearch", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumResultsForSearch = Infinity;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.Gender.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
    <div id="r__Email" class="row"<?= $Page->_Email->rowAttributes() ?>>
        <label for="x__Email" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users__Email"><?= $Page->_Email->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z__Email" id="z__Email" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->_Email->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users__Email" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->_Email->getInputTextType() ?>" name="x__Email" id="x__Email" data-table="users" data-field="x__Email" value="<?= $Page->_Email->getEditValue() ?>" size="50" maxlength="255" placeholder="<?= HtmlEncode($Page->_Email->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->_Email->formatPattern()) ?>"<?= $Page->_Email->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->_Email->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
    <div id="r_Activated" class="row"<?= $Page->Activated->rowAttributes() ?>>
        <label class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_Activated"><?= $Page->Activated->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_Activated" id="z_Activated" value="=">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->Activated->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_Activated" class="ew-search-field ew-search-field-single">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Activated->isInvalidClass() ?>" data-table="users" data-field="x_Activated" data-boolean name="x_Activated" id="x_Activated" value="1"<?= ConvertToBool($Page->Activated->AdvancedSearch->SearchValue) ? " checked" : "" ?><?= $Page->Activated->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Page->Activated->getErrorMessage(false) ?></div>
</div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->Avatar->Visible) { // Avatar ?>
    <div id="r_Avatar" class="row"<?= $Page->Avatar->rowAttributes() ?>>
        <label class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_Avatar"><?= $Page->Avatar->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z_Avatar" id="z_Avatar" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->Avatar->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_Avatar" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->Avatar->getInputTextType() ?>" name="x_Avatar" id="x_Avatar" data-table="users" data-field="x_Avatar" value="<?= $Page->Avatar->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->Avatar->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Avatar->formatPattern()) ?>"<?= $Page->Avatar->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->Avatar->getErrorMessage(false) ?></div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
    <div id="r_ActiveStatus" class="row"<?= $Page->ActiveStatus->rowAttributes() ?>>
        <label class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_ActiveStatus"><?= $Page->ActiveStatus->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_ActiveStatus" id="z_ActiveStatus" value="=">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->ActiveStatus->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_ActiveStatus" class="ew-search-field ew-search-field-single">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->ActiveStatus->isInvalidClass() ?>" data-table="users" data-field="x_ActiveStatus" data-boolean name="x_ActiveStatus" id="x_ActiveStatus" value="1"<?= ConvertToBool($Page->ActiveStatus->AdvancedSearch->SearchValue) ? " checked" : "" ?><?= $Page->ActiveStatus->editAttributes() ?>>
    <div class="invalid-feedback"><?= $Page->ActiveStatus->getErrorMessage(false) ?></div>
</div>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->MessengerColor->Visible) { // MessengerColor ?>
    <div id="r_MessengerColor" class="row"<?= $Page->MessengerColor->rowAttributes() ?>>
        <label for="x_MessengerColor" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_MessengerColor"><?= $Page->MessengerColor->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("LIKE") ?>
<input type="hidden" name="z_MessengerColor" id="z_MessengerColor" value="LIKE">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->MessengerColor->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_MessengerColor" class="ew-search-field ew-search-field-single">
<input type="<?= $Page->MessengerColor->getInputTextType() ?>" name="x_MessengerColor" id="x_MessengerColor" data-table="users" data-field="x_MessengerColor" value="<?= $Page->MessengerColor->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->MessengerColor->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->MessengerColor->formatPattern()) ?>"<?= $Page->MessengerColor->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->MessengerColor->getErrorMessage(false) ?></div>
<script<?= Nonce() ?>>
loadjs.ready("head", function() {
jQuery("#x_MessengerColor:not(.ew-template #x_MessengerColor)").colorpicker(ew.colorPickerOptions);
});
</script>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->CreatedAt->Visible) { // CreatedAt ?>
    <div id="r_CreatedAt" class="row"<?= $Page->CreatedAt->rowAttributes() ?>>
        <label for="x_CreatedAt" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_CreatedAt"><?= $Page->CreatedAt->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("BETWEEN") ?>
<input type="hidden" name="z_CreatedAt" id="z_CreatedAt" value="BETWEEN">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->CreatedAt->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_CreatedAt" class="ew-search-field">
<input type="<?= $Page->CreatedAt->getInputTextType() ?>" name="x_CreatedAt" id="x_CreatedAt" data-table="users" data-field="x_CreatedAt" value="<?= $Page->CreatedAt->getEditValue() ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->CreatedAt->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->CreatedAt->formatPattern()) ?>"<?= $Page->CreatedAt->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->CreatedAt->getErrorMessage(false) ?></div>
<?php if (!$Page->CreatedAt->ReadOnly && !$Page->CreatedAt->Disabled && !isset($Page->CreatedAt->EditAttrs["readonly"]) && !isset($Page->CreatedAt->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fuserssearch", "datetimepicker"], function () {
    let format = "<?= DateFormat(1) ?>",
        options = {
            localization: {
                locale: ew.LANGUAGE_ID + "-u-nu-" + ew.getNumberingSystem(),
                hourCycle: format.match(/H/) ? "h24" : "h12",
                format,
                ...ew.language.phrase("datetimepicker")
            },
            display: {
                icons: {
                    previous: ew.IS_RTL ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left",
                    next: ew.IS_RTL ? "fa-solid fa-chevron-left" : "fa-solid fa-chevron-right"
                },
                components: {
                    clock: !!format.match(/h/i) || !!format.match(/m/) || !!format.match(/s/i),
                    hours: !!format.match(/h/i),
                    minutes: !!format.match(/m/),
                    seconds: !!format.match(/s/i)
                },
                theme: ew.getPreferredTheme()
            }
        };
    ew.createDateTimePicker(
        "fuserssearch",
        "x_CreatedAt",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fuserssearch', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserssearch", "x_CreatedAt", jQuery.extend(true, "", options));
});
</script>
</span>
                    <span class="ew-search-and"><label><?= $Language->phrase("AND") ?></label></span>
                    <span id="el2_users_CreatedAt" class="ew-search-field2">
<input type="<?= $Page->CreatedAt->getInputTextType() ?>" name="y_CreatedAt" id="y_CreatedAt" data-table="users" data-field="x_CreatedAt" value="<?= $Page->CreatedAt->EditValue2 ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->CreatedAt->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->CreatedAt->formatPattern()) ?>"<?= $Page->CreatedAt->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->CreatedAt->getErrorMessage(false) ?></div>
<?php if (!$Page->CreatedAt->ReadOnly && !$Page->CreatedAt->Disabled && !isset($Page->CreatedAt->EditAttrs["readonly"]) && !isset($Page->CreatedAt->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fuserssearch", "datetimepicker"], function () {
    let format = "<?= DateFormat(1) ?>",
        options = {
            localization: {
                locale: ew.LANGUAGE_ID + "-u-nu-" + ew.getNumberingSystem(),
                hourCycle: format.match(/H/) ? "h24" : "h12",
                format,
                ...ew.language.phrase("datetimepicker")
            },
            display: {
                icons: {
                    previous: ew.IS_RTL ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left",
                    next: ew.IS_RTL ? "fa-solid fa-chevron-left" : "fa-solid fa-chevron-right"
                },
                components: {
                    clock: !!format.match(/h/i) || !!format.match(/m/) || !!format.match(/s/i),
                    hours: !!format.match(/h/i),
                    minutes: !!format.match(/m/),
                    seconds: !!format.match(/s/i)
                },
                theme: ew.getPreferredTheme()
            }
        };
    ew.createDateTimePicker(
        "fuserssearch",
        "y_CreatedAt",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fuserssearch', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserssearch", "y_CreatedAt", jQuery.extend(true, "", options));
});
</script>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->CreatedBy->Visible) { // CreatedBy ?>
    <div id="r_CreatedBy" class="row"<?= $Page->CreatedBy->rowAttributes() ?>>
        <label for="x_CreatedBy" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_CreatedBy"><?= $Page->CreatedBy->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_CreatedBy" id="z_CreatedBy" value="=">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->CreatedBy->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_CreatedBy" class="ew-search-field ew-search-field-single">
    <select
        id="x_CreatedBy"
        name="x_CreatedBy"
        class="form-select ew-select<?= $Page->CreatedBy->isInvalidClass() ?>"
        <?php if (!$Page->CreatedBy->IsNativeSelect) { ?>
        data-select2-id="fuserssearch_x_CreatedBy"
        <?php } ?>
        data-table="users"
        data-field="x_CreatedBy"
        data-value-separator="<?= $Page->CreatedBy->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->CreatedBy->getPlaceHolder()) ?>"
        <?= $Page->CreatedBy->editAttributes() ?>>
        <?= $Page->CreatedBy->selectOptionListHtml("x_CreatedBy") ?>
    </select>
    <div class="invalid-feedback"><?= $Page->CreatedBy->getErrorMessage(false) ?></div>
<?= $Page->CreatedBy->Lookup->getParamTag($Page, "p_x_CreatedBy") ?>
<?php if (!$Page->CreatedBy->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fuserssearch", function() {
    var options = { name: "x_CreatedBy", selectId: "fuserssearch_x_CreatedBy" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fuserssearch.lists.CreatedBy?.lookupOptions.length) {
        options.data = { id: "x_CreatedBy", form: "fuserssearch" };
    } else {
        options.ajax = { id: "x_CreatedBy", form: "fuserssearch", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.CreatedBy.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->UpdatedAt->Visible) { // UpdatedAt ?>
    <div id="r_UpdatedAt" class="row"<?= $Page->UpdatedAt->rowAttributes() ?>>
        <label for="x_UpdatedAt" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_UpdatedAt"><?= $Page->UpdatedAt->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("BETWEEN") ?>
<input type="hidden" name="z_UpdatedAt" id="z_UpdatedAt" value="BETWEEN">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->UpdatedAt->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_UpdatedAt" class="ew-search-field">
<input type="<?= $Page->UpdatedAt->getInputTextType() ?>" name="x_UpdatedAt" id="x_UpdatedAt" data-table="users" data-field="x_UpdatedAt" value="<?= $Page->UpdatedAt->getEditValue() ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->UpdatedAt->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->UpdatedAt->formatPattern()) ?>"<?= $Page->UpdatedAt->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->UpdatedAt->getErrorMessage(false) ?></div>
<?php if (!$Page->UpdatedAt->ReadOnly && !$Page->UpdatedAt->Disabled && !isset($Page->UpdatedAt->EditAttrs["readonly"]) && !isset($Page->UpdatedAt->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fuserssearch", "datetimepicker"], function () {
    let format = "<?= DateFormat(1) ?>",
        options = {
            localization: {
                locale: ew.LANGUAGE_ID + "-u-nu-" + ew.getNumberingSystem(),
                hourCycle: format.match(/H/) ? "h24" : "h12",
                format,
                ...ew.language.phrase("datetimepicker")
            },
            display: {
                icons: {
                    previous: ew.IS_RTL ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left",
                    next: ew.IS_RTL ? "fa-solid fa-chevron-left" : "fa-solid fa-chevron-right"
                },
                components: {
                    clock: !!format.match(/h/i) || !!format.match(/m/) || !!format.match(/s/i),
                    hours: !!format.match(/h/i),
                    minutes: !!format.match(/m/),
                    seconds: !!format.match(/s/i)
                },
                theme: ew.getPreferredTheme()
            }
        };
    ew.createDateTimePicker(
        "fuserssearch",
        "x_UpdatedAt",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true,"minDateField":null,"maxDateField":null}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fuserssearch', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserssearch", "x_UpdatedAt", jQuery.extend(true, "", options));
});
</script>
</span>
                    <span class="ew-search-and"><label><?= $Language->phrase("AND") ?></label></span>
                    <span id="el2_users_UpdatedAt" class="ew-search-field2">
<input type="<?= $Page->UpdatedAt->getInputTextType() ?>" name="y_UpdatedAt" id="y_UpdatedAt" data-table="users" data-field="x_UpdatedAt" value="<?= $Page->UpdatedAt->EditValue2 ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->UpdatedAt->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->UpdatedAt->formatPattern()) ?>"<?= $Page->UpdatedAt->editAttributes() ?>>
<div class="invalid-feedback"><?= $Page->UpdatedAt->getErrorMessage(false) ?></div>
<?php if (!$Page->UpdatedAt->ReadOnly && !$Page->UpdatedAt->Disabled && !isset($Page->UpdatedAt->EditAttrs["readonly"]) && !isset($Page->UpdatedAt->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fuserssearch", "datetimepicker"], function () {
    let format = "<?= DateFormat(1) ?>",
        options = {
            localization: {
                locale: ew.LANGUAGE_ID + "-u-nu-" + ew.getNumberingSystem(),
                hourCycle: format.match(/H/) ? "h24" : "h12",
                format,
                ...ew.language.phrase("datetimepicker")
            },
            display: {
                icons: {
                    previous: ew.IS_RTL ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left",
                    next: ew.IS_RTL ? "fa-solid fa-chevron-left" : "fa-solid fa-chevron-right"
                },
                components: {
                    clock: !!format.match(/h/i) || !!format.match(/m/) || !!format.match(/s/i),
                    hours: !!format.match(/h/i),
                    minutes: !!format.match(/m/),
                    seconds: !!format.match(/s/i)
                },
                theme: ew.getPreferredTheme()
            }
        };
    ew.createDateTimePicker(
        "fuserssearch",
        "y_UpdatedAt",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true,"minDateField":null,"maxDateField":null}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fuserssearch', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserssearch", "y_UpdatedAt", jQuery.extend(true, "", options));
});
</script>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($Page->UpdatedBy->Visible) { // UpdatedBy ?>
    <div id="r_UpdatedBy" class="row"<?= $Page->UpdatedBy->rowAttributes() ?>>
        <label for="x_UpdatedBy" class="<?= $Page->LeftColumnClass ?>"><span id="elh_users_UpdatedBy"><?= $Page->UpdatedBy->caption() ?></span>
        <span class="ew-search-operator">
<?= $Language->phrase("=") ?>
<input type="hidden" name="z_UpdatedBy" id="z_UpdatedBy" value="=">
</span>
        </label>
        <div class="<?= $Page->RightColumnClass ?>">
            <div<?= $Page->UpdatedBy->cellAttributes() ?>>
                <div class="d-flex align-items-start">
                <span id="el_users_UpdatedBy" class="ew-search-field ew-search-field-single">
    <select
        id="x_UpdatedBy"
        name="x_UpdatedBy"
        class="form-select ew-select<?= $Page->UpdatedBy->isInvalidClass() ?>"
        <?php if (!$Page->UpdatedBy->IsNativeSelect) { ?>
        data-select2-id="fuserssearch_x_UpdatedBy"
        <?php } ?>
        data-table="users"
        data-field="x_UpdatedBy"
        data-value-separator="<?= $Page->UpdatedBy->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->UpdatedBy->getPlaceHolder()) ?>"
        <?= $Page->UpdatedBy->editAttributes() ?>>
        <?= $Page->UpdatedBy->selectOptionListHtml("x_UpdatedBy") ?>
    </select>
    <div class="invalid-feedback"><?= $Page->UpdatedBy->getErrorMessage(false) ?></div>
<?= $Page->UpdatedBy->Lookup->getParamTag($Page, "p_x_UpdatedBy") ?>
<?php if (!$Page->UpdatedBy->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fuserssearch", function() {
    var options = { name: "x_UpdatedBy", selectId: "fuserssearch_x_UpdatedBy" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fuserssearch.lists.UpdatedBy?.lookupOptions.length) {
        options.data = { id: "x_UpdatedBy", form: "fuserssearch" };
    } else {
        options.ajax = { id: "x_UpdatedBy", form: "fuserssearch", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.users.fields.UpdatedBy.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
        <button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="fuserssearch"><?= $Language->phrase("Search") ?></button>
        <?php if ($Page->IsModal) { ?>
        <button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fuserssearch"><?= $Language->phrase("CancelBtn") ?></button>
        <?php } else { ?>
        <button class="btn btn-secondary ew-btn" name="btn-reset" id="btn-reset" type="button" form="fuserssearch" data-ew-action="reload"><?= $Language->phrase("Reset") ?></button>
        <?php } ?>
    </div><!-- /buttons offset -->
<?= $Page->IsModal ? "</template>" : "</div>" ?><!-- /buttons .row -->
</form>
<?php // Begin of Card view by Masino Sinaga, September 10, 2023 ?>
<?php if (!$Page->IsModal) { ?>
		</div>
     <!-- /.card-body -->
     </div>
  <!-- /.card -->
</div>
<?php } ?>
<?php
$Page->showPageFooter();
?>
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
