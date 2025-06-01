<?php

namespace PHPMaker2025\ucarsip;

// Page object
$TheuserprofileAdd = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { theuserprofile: currentTable } });
var currentPageID = ew.PAGE_ID = "add";
var currentForm;
var ftheuserprofileadd;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("ftheuserprofileadd")
        .setPageId("add")

        // Add fields
        .setFields([
            ["_Username", [fields._Username.visible && fields._Username.required ? ew.Validators.required(fields._Username.caption) : null], fields._Username.isInvalid],
            ["_Password", [fields._Password.visible && fields._Password.required ? ew.Validators.required(fields._Password.caption) : null, ew.Validators.passwordStrength], fields._Password.isInvalid],
            ["UserLevel", [fields.UserLevel.visible && fields.UserLevel.required ? ew.Validators.required(fields.UserLevel.caption) : null], fields.UserLevel.isInvalid],
            ["FirstName", [fields.FirstName.visible && fields.FirstName.required ? ew.Validators.required(fields.FirstName.caption) : null], fields.FirstName.isInvalid],
            ["LastName", [fields.LastName.visible && fields.LastName.required ? ew.Validators.required(fields.LastName.caption) : null], fields.LastName.isInvalid],
            ["CompleteName", [fields.CompleteName.visible && fields.CompleteName.required ? ew.Validators.required(fields.CompleteName.caption) : null], fields.CompleteName.isInvalid],
            ["BirthDate", [fields.BirthDate.visible && fields.BirthDate.required ? ew.Validators.required(fields.BirthDate.caption) : null, ew.Validators.datetime(fields.BirthDate.clientFormatPattern)], fields.BirthDate.isInvalid],
            ["HomePhone", [fields.HomePhone.visible && fields.HomePhone.required ? ew.Validators.required(fields.HomePhone.caption) : null], fields.HomePhone.isInvalid],
            ["Photo", [fields.Photo.visible && fields.Photo.required ? ew.Validators.fileRequired(fields.Photo.caption) : null], fields.Photo.isInvalid],
            ["Notes", [fields.Notes.visible && fields.Notes.required ? ew.Validators.required(fields.Notes.caption) : null], fields.Notes.isInvalid],
            ["ReportsTo", [fields.ReportsTo.visible && fields.ReportsTo.required ? ew.Validators.required(fields.ReportsTo.caption) : null, ew.Validators.integer], fields.ReportsTo.isInvalid],
            ["Gender", [fields.Gender.visible && fields.Gender.required ? ew.Validators.required(fields.Gender.caption) : null], fields.Gender.isInvalid],
            ["_Email", [fields._Email.visible && fields._Email.required ? ew.Validators.required(fields._Email.caption) : null, ew.Validators.email], fields._Email.isInvalid],
            ["Activated", [fields.Activated.visible && fields.Activated.required ? ew.Validators.required(fields.Activated.caption) : null], fields.Activated.isInvalid],
            ["_Profile", [fields._Profile.visible && fields._Profile.required ? ew.Validators.required(fields._Profile.caption) : null], fields._Profile.isInvalid],
            ["Avatar", [fields.Avatar.visible && fields.Avatar.required ? ew.Validators.fileRequired(fields.Avatar.caption) : null], fields.Avatar.isInvalid],
            ["ActiveStatus", [fields.ActiveStatus.visible && fields.ActiveStatus.required ? ew.Validators.required(fields.ActiveStatus.caption) : null], fields.ActiveStatus.isInvalid],
            ["MessengerColor", [fields.MessengerColor.visible && fields.MessengerColor.required ? ew.Validators.required(fields.MessengerColor.caption) : null], fields.MessengerColor.isInvalid],
            ["CreatedAt", [fields.CreatedAt.visible && fields.CreatedAt.required ? ew.Validators.required(fields.CreatedAt.caption) : null, ew.Validators.datetime(fields.CreatedAt.clientFormatPattern)], fields.CreatedAt.isInvalid],
            ["CreatedBy", [fields.CreatedBy.visible && fields.CreatedBy.required ? ew.Validators.required(fields.CreatedBy.caption) : null], fields.CreatedBy.isInvalid],
            ["UpdatedAt", [fields.UpdatedAt.visible && fields.UpdatedAt.required ? ew.Validators.required(fields.UpdatedAt.caption) : null], fields.UpdatedAt.isInvalid],
            ["UpdatedBy", [fields.UpdatedBy.visible && fields.UpdatedBy.required ? ew.Validators.required(fields.UpdatedBy.caption) : null], fields.UpdatedBy.isInvalid]
        ])

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
    currentForm = form;
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
	  <h4 class="card-title"><?php echo Language()->phrase("AddCaption"); ?></h4>
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
<form name="ftheuserprofileadd" id="ftheuserprofileadd" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="theuserprofile">
<input type="hidden" name="action" id="action" value="insert">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-add-div"><!-- page* -->
<?php if ($Page->_Username->Visible) { // Username ?>
    <div id="r__Username"<?= $Page->_Username->rowAttributes() ?>>
        <label id="elh_theuserprofile__Username" for="x__Username" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_Username->caption() ?><?= $Page->_Username->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_Username->cellAttributes() ?>>
<span id="el_theuserprofile__Username">
<input type="<?= $Page->_Username->getInputTextType() ?>" name="x__Username" id="x__Username" data-table="theuserprofile" data-field="x__Username" value="<?= $Page->_Username->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->_Username->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->_Username->formatPattern()) ?>"<?= $Page->_Username->editAttributes() ?> aria-describedby="x__Username_help">
<?= $Page->_Username->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->_Username->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->_Password->Visible) { // Password ?>
    <div id="r__Password"<?= $Page->_Password->rowAttributes() ?>>
        <label id="elh_theuserprofile__Password" for="x__Password" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_Password->caption() ?><?= $Page->_Password->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_Password->cellAttributes() ?>>
<span id="el_theuserprofile__Password">
<div class="input-group" id="ig__Password">
    <input type="password" autocomplete="new-password" data-password-strength="pst__Password" data-table="theuserprofile" data-field="x__Password" name="x__Password" id="x__Password" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->_Password->getPlaceHolder()) ?>"<?= $Page->_Password->editAttributes() ?> aria-describedby="x__Password_help">
    <button type="button" class="btn btn-default ew-toggle-password" data-ew-action="password"><i class="fa-solid fa-eye"></i></button>
    <button type="button" class="btn btn-default ew-password-generator rounded-end" title="<?= HtmlTitle($Language->phrase("GeneratePassword")) ?>" data-password-field="x__Password" data-password-confirm="c__Password" data-password-strength="pst__Password"><?= $Language->phrase("GeneratePassword") ?></button>
</div>
<div class="progress ew-password-strength-bar form-text mt-1 d-none" id="pst__Password" role="progressbar">
    <div class="progress-bar"></div>
</div>
<?= $Page->_Password->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->_Password->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
    <div id="r_UserLevel"<?= $Page->UserLevel->rowAttributes() ?>>
        <label id="elh_theuserprofile_UserLevel" for="x_UserLevel" class="<?= $Page->LeftColumnClass ?>"><?= $Page->UserLevel->caption() ?><?= $Page->UserLevel->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->UserLevel->cellAttributes() ?>>
<span id="el_theuserprofile_UserLevel">
    <select
        id="x_UserLevel"
        name="x_UserLevel"
        class="form-select ew-select<?= $Page->UserLevel->isInvalidClass() ?>"
        <?php if (!$Page->UserLevel->IsNativeSelect) { ?>
        data-select2-id="ftheuserprofileadd_x_UserLevel"
        <?php } ?>
        data-table="theuserprofile"
        data-field="x_UserLevel"
        data-value-separator="<?= $Page->UserLevel->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->UserLevel->getPlaceHolder()) ?>"
        <?= $Page->UserLevel->editAttributes() ?>>
        <?= $Page->UserLevel->selectOptionListHtml("x_UserLevel") ?>
    </select>
    <?= $Page->UserLevel->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->UserLevel->getErrorMessage() ?></div>
<?= $Page->UserLevel->Lookup->getParamTag($Page, "p_x_UserLevel") ?>
<?php if (!$Page->UserLevel->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("ftheuserprofileadd", function() {
    var options = { name: "x_UserLevel", selectId: "ftheuserprofileadd_x_UserLevel" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (ftheuserprofileadd.lists.UserLevel?.lookupOptions.length) {
        options.data = { id: "x_UserLevel", form: "ftheuserprofileadd" };
    } else {
        options.ajax = { id: "x_UserLevel", form: "ftheuserprofileadd", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.theuserprofile.fields.UserLevel.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->FirstName->Visible) { // FirstName ?>
    <div id="r_FirstName"<?= $Page->FirstName->rowAttributes() ?>>
        <label id="elh_theuserprofile_FirstName" for="x_FirstName" class="<?= $Page->LeftColumnClass ?>"><?= $Page->FirstName->caption() ?><?= $Page->FirstName->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->FirstName->cellAttributes() ?>>
<span id="el_theuserprofile_FirstName">
<input type="<?= $Page->FirstName->getInputTextType() ?>" name="x_FirstName" id="x_FirstName" data-table="theuserprofile" data-field="x_FirstName" value="<?= $Page->FirstName->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->FirstName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->FirstName->formatPattern()) ?>"<?= $Page->FirstName->editAttributes() ?> aria-describedby="x_FirstName_help">
<?= $Page->FirstName->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->FirstName->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->LastName->Visible) { // LastName ?>
    <div id="r_LastName"<?= $Page->LastName->rowAttributes() ?>>
        <label id="elh_theuserprofile_LastName" for="x_LastName" class="<?= $Page->LeftColumnClass ?>"><?= $Page->LastName->caption() ?><?= $Page->LastName->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->LastName->cellAttributes() ?>>
<span id="el_theuserprofile_LastName">
<input type="<?= $Page->LastName->getInputTextType() ?>" name="x_LastName" id="x_LastName" data-table="theuserprofile" data-field="x_LastName" value="<?= $Page->LastName->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->LastName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->LastName->formatPattern()) ?>"<?= $Page->LastName->editAttributes() ?> aria-describedby="x_LastName_help">
<?= $Page->LastName->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->LastName->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
    <div id="r_CompleteName"<?= $Page->CompleteName->rowAttributes() ?>>
        <label id="elh_theuserprofile_CompleteName" for="x_CompleteName" class="<?= $Page->LeftColumnClass ?>"><?= $Page->CompleteName->caption() ?><?= $Page->CompleteName->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->CompleteName->cellAttributes() ?>>
<span id="el_theuserprofile_CompleteName">
<input type="<?= $Page->CompleteName->getInputTextType() ?>" name="x_CompleteName" id="x_CompleteName" data-table="theuserprofile" data-field="x_CompleteName" value="<?= $Page->CompleteName->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->CompleteName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->CompleteName->formatPattern()) ?>"<?= $Page->CompleteName->editAttributes() ?> aria-describedby="x_CompleteName_help">
<?= $Page->CompleteName->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->CompleteName->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->BirthDate->Visible) { // BirthDate ?>
    <div id="r_BirthDate"<?= $Page->BirthDate->rowAttributes() ?>>
        <label id="elh_theuserprofile_BirthDate" for="x_BirthDate" class="<?= $Page->LeftColumnClass ?>"><?= $Page->BirthDate->caption() ?><?= $Page->BirthDate->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->BirthDate->cellAttributes() ?>>
<span id="el_theuserprofile_BirthDate">
<input type="<?= $Page->BirthDate->getInputTextType() ?>" name="x_BirthDate" id="x_BirthDate" data-table="theuserprofile" data-field="x_BirthDate" value="<?= $Page->BirthDate->getEditValue() ?>" size="12" maxlength="10" placeholder="<?= HtmlEncode($Page->BirthDate->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->BirthDate->formatPattern()) ?>"<?= $Page->BirthDate->editAttributes() ?> aria-describedby="x_BirthDate_help">
<?= $Page->BirthDate->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->BirthDate->getErrorMessage() ?></div>
<?php if (!$Page->BirthDate->ReadOnly && !$Page->BirthDate->Disabled && !isset($Page->BirthDate->EditAttrs["readonly"]) && !isset($Page->BirthDate->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["ftheuserprofileadd", "datetimepicker"], function () {
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
        "ftheuserprofileadd",
        "x_BirthDate",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['ftheuserprofileadd', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("ftheuserprofileadd", "x_BirthDate", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->HomePhone->Visible) { // HomePhone ?>
    <div id="r_HomePhone"<?= $Page->HomePhone->rowAttributes() ?>>
        <label id="elh_theuserprofile_HomePhone" for="x_HomePhone" class="<?= $Page->LeftColumnClass ?>"><?= $Page->HomePhone->caption() ?><?= $Page->HomePhone->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->HomePhone->cellAttributes() ?>>
<span id="el_theuserprofile_HomePhone">
<input type="<?= $Page->HomePhone->getInputTextType() ?>" name="x_HomePhone" id="x_HomePhone" data-table="theuserprofile" data-field="x_HomePhone" value="<?= $Page->HomePhone->getEditValue() ?>" size="30" maxlength="24" placeholder="<?= HtmlEncode($Page->HomePhone->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->HomePhone->formatPattern()) ?>"<?= $Page->HomePhone->editAttributes() ?> aria-describedby="x_HomePhone_help">
<?= $Page->HomePhone->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->HomePhone->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
    <div id="r_Photo"<?= $Page->Photo->rowAttributes() ?>>
        <label id="elh_theuserprofile_Photo" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Photo->caption() ?><?= $Page->Photo->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Photo->cellAttributes() ?>>
<span id="el_theuserprofile_Photo">
<div id="fd_x_Photo" class="fileinput-button ew-file-drop-zone">
    <input
        type="file"
        id="x_Photo"
        name="x_Photo"
        class="form-control ew-file-input"
        title="<?= $Page->Photo->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="theuserprofile"
        data-field="x_Photo"
        data-size="50"
        data-accept-file-types="<?= $Page->Photo->acceptFileTypes() ?>"
        data-max-file-size="<?= $Page->Photo->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Page->Photo->ImageCropper ? 0 : 1 ?>"
        aria-describedby="x_Photo_help"
        <?= ($Page->Photo->ReadOnly || $Page->Photo->Disabled) ? " disabled" : "" ?>
        <?= $Page->Photo->editAttributes() ?>
    >
    <div class="text-body-secondary ew-file-text"><?= $Language->phrase("ChooseFile") ?></div>
    <?= $Page->Photo->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->Photo->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x_Photo" id= "fn_x_Photo" value="<?= $Page->Photo->Upload->FileName ?>">
<input type="hidden" name="fa_x_Photo" id= "fa_x_Photo" value="0">
<table id="ft_x_Photo" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Notes->Visible) { // Notes ?>
    <div id="r_Notes"<?= $Page->Notes->rowAttributes() ?>>
        <label id="elh_theuserprofile_Notes" for="x_Notes" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Notes->caption() ?><?= $Page->Notes->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Notes->cellAttributes() ?>>
<span id="el_theuserprofile_Notes">
<textarea data-table="theuserprofile" data-field="x_Notes" name="x_Notes" id="x_Notes" cols="50" rows="5" placeholder="<?= HtmlEncode($Page->Notes->getPlaceHolder()) ?>"<?= $Page->Notes->editAttributes() ?> aria-describedby="x_Notes_help"><?= $Page->Notes->getEditValue() ?></textarea>
<?= $Page->Notes->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Notes->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->ReportsTo->Visible) { // ReportsTo ?>
    <div id="r_ReportsTo"<?= $Page->ReportsTo->rowAttributes() ?>>
        <label id="elh_theuserprofile_ReportsTo" for="x_ReportsTo" class="<?= $Page->LeftColumnClass ?>"><?= $Page->ReportsTo->caption() ?><?= $Page->ReportsTo->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->ReportsTo->cellAttributes() ?>>
<span id="el_theuserprofile_ReportsTo">
<input type="<?= $Page->ReportsTo->getInputTextType() ?>" name="x_ReportsTo" id="x_ReportsTo" data-table="theuserprofile" data-field="x_ReportsTo" value="<?= $Page->ReportsTo->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->ReportsTo->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->ReportsTo->formatPattern()) ?>"<?= $Page->ReportsTo->editAttributes() ?> aria-describedby="x_ReportsTo_help">
<?= $Page->ReportsTo->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->ReportsTo->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(['ftheuserprofileadd', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("ftheuserprofileadd", "x_ReportsTo", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
    <div id="r_Gender"<?= $Page->Gender->rowAttributes() ?>>
        <label id="elh_theuserprofile_Gender" for="x_Gender" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Gender->caption() ?><?= $Page->Gender->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Gender->cellAttributes() ?>>
<span id="el_theuserprofile_Gender">
    <select
        id="x_Gender"
        name="x_Gender"
        class="form-select ew-select<?= $Page->Gender->isInvalidClass() ?>"
        <?php if (!$Page->Gender->IsNativeSelect) { ?>
        data-select2-id="ftheuserprofileadd_x_Gender"
        <?php } ?>
        data-table="theuserprofile"
        data-field="x_Gender"
        data-value-separator="<?= $Page->Gender->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->Gender->getPlaceHolder()) ?>"
        <?= $Page->Gender->editAttributes() ?>>
        <?= $Page->Gender->selectOptionListHtml("x_Gender") ?>
    </select>
    <?= $Page->Gender->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->Gender->getErrorMessage() ?></div>
<?php if (!$Page->Gender->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("ftheuserprofileadd", function() {
    var options = { name: "x_Gender", selectId: "ftheuserprofileadd_x_Gender" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (ftheuserprofileadd.lists.Gender?.lookupOptions.length) {
        options.data = { id: "x_Gender", form: "ftheuserprofileadd" };
    } else {
        options.ajax = { id: "x_Gender", form: "ftheuserprofileadd", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumResultsForSearch = Infinity;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.theuserprofile.fields.Gender.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
    <div id="r__Email"<?= $Page->_Email->rowAttributes() ?>>
        <label id="elh_theuserprofile__Email" for="x__Email" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_Email->caption() ?><?= $Page->_Email->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_Email->cellAttributes() ?>>
<span id="el_theuserprofile__Email">
<input type="<?= $Page->_Email->getInputTextType() ?>" name="x__Email" id="x__Email" data-table="theuserprofile" data-field="x__Email" value="<?= $Page->_Email->getEditValue() ?>" size="50" maxlength="255" placeholder="<?= HtmlEncode($Page->_Email->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->_Email->formatPattern()) ?>"<?= $Page->_Email->editAttributes() ?> aria-describedby="x__Email_help">
<?= $Page->_Email->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->_Email->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
    <div id="r_Activated"<?= $Page->Activated->rowAttributes() ?>>
        <label id="elh_theuserprofile_Activated" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Activated->caption() ?><?= $Page->Activated->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Activated->cellAttributes() ?>>
<span id="el_theuserprofile_Activated">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Activated->isInvalidClass() ?>" data-table="theuserprofile" data-field="x_Activated" data-boolean name="x_Activated" id="x_Activated" value="1"<?= ConvertToBool($Page->Activated->CurrentValue) ? " checked" : "" ?><?= $Page->Activated->editAttributes() ?> aria-describedby="x_Activated_help">
    <div class="invalid-feedback"><?= $Page->Activated->getErrorMessage() ?></div>
</div>
<?= $Page->Activated->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->_Profile->Visible) { // Profile ?>
    <div id="r__Profile"<?= $Page->_Profile->rowAttributes() ?>>
        <label id="elh_theuserprofile__Profile" for="x__Profile" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_Profile->caption() ?><?= $Page->_Profile->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_Profile->cellAttributes() ?>>
<span id="el_theuserprofile__Profile">
<textarea data-table="theuserprofile" data-field="x__Profile" name="x__Profile" id="x__Profile" cols="35" rows="4" placeholder="<?= HtmlEncode($Page->_Profile->getPlaceHolder()) ?>"<?= $Page->_Profile->editAttributes() ?> aria-describedby="x__Profile_help"><?= $Page->_Profile->getEditValue() ?></textarea>
<?= $Page->_Profile->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->_Profile->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Avatar->Visible) { // Avatar ?>
    <div id="r_Avatar"<?= $Page->Avatar->rowAttributes() ?>>
        <label id="elh_theuserprofile_Avatar" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Avatar->caption() ?><?= $Page->Avatar->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Avatar->cellAttributes() ?>>
<span id="el_theuserprofile_Avatar">
<div id="fd_x_Avatar" class="fileinput-button ew-file-drop-zone">
    <input
        type="file"
        id="x_Avatar"
        name="x_Avatar"
        class="form-control ew-file-input"
        title="<?= $Page->Avatar->title() ?>"
        lang="<?= CurrentLanguageID() ?>"
        data-table="theuserprofile"
        data-field="x_Avatar"
        data-size="255"
        data-accept-file-types="<?= $Page->Avatar->acceptFileTypes() ?>"
        data-max-file-size="<?= $Page->Avatar->UploadMaxFileSize ?>"
        data-max-number-of-files="null"
        data-disable-image-crop="<?= $Page->Avatar->ImageCropper ? 0 : 1 ?>"
        aria-describedby="x_Avatar_help"
        <?= ($Page->Avatar->ReadOnly || $Page->Avatar->Disabled) ? " disabled" : "" ?>
        <?= $Page->Avatar->editAttributes() ?>
    >
    <div class="text-body-secondary ew-file-text"><?= $Language->phrase("ChooseFile") ?></div>
    <?= $Page->Avatar->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->Avatar->getErrorMessage() ?></div>
</div>
<input type="hidden" name="fn_x_Avatar" id= "fn_x_Avatar" value="<?= $Page->Avatar->Upload->FileName ?>">
<input type="hidden" name="fa_x_Avatar" id= "fa_x_Avatar" value="0">
<table id="ft_x_Avatar" class="table table-sm float-start ew-upload-table"><tbody class="files"></tbody></table>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
    <div id="r_ActiveStatus"<?= $Page->ActiveStatus->rowAttributes() ?>>
        <label id="elh_theuserprofile_ActiveStatus" class="<?= $Page->LeftColumnClass ?>"><?= $Page->ActiveStatus->caption() ?><?= $Page->ActiveStatus->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->ActiveStatus->cellAttributes() ?>>
<span id="el_theuserprofile_ActiveStatus">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->ActiveStatus->isInvalidClass() ?>" data-table="theuserprofile" data-field="x_ActiveStatus" data-boolean name="x_ActiveStatus" id="x_ActiveStatus" value="1"<?= ConvertToBool($Page->ActiveStatus->CurrentValue) ? " checked" : "" ?><?= $Page->ActiveStatus->editAttributes() ?> aria-describedby="x_ActiveStatus_help">
    <div class="invalid-feedback"><?= $Page->ActiveStatus->getErrorMessage() ?></div>
</div>
<?= $Page->ActiveStatus->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->MessengerColor->Visible) { // MessengerColor ?>
    <div id="r_MessengerColor"<?= $Page->MessengerColor->rowAttributes() ?>>
        <label id="elh_theuserprofile_MessengerColor" for="x_MessengerColor" class="<?= $Page->LeftColumnClass ?>"><?= $Page->MessengerColor->caption() ?><?= $Page->MessengerColor->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->MessengerColor->cellAttributes() ?>>
<span id="el_theuserprofile_MessengerColor">
<input type="<?= $Page->MessengerColor->getInputTextType() ?>" name="x_MessengerColor" id="x_MessengerColor" data-table="theuserprofile" data-field="x_MessengerColor" value="<?= $Page->MessengerColor->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->MessengerColor->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->MessengerColor->formatPattern()) ?>"<?= $Page->MessengerColor->editAttributes() ?> aria-describedby="x_MessengerColor_help">
<?= $Page->MessengerColor->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->MessengerColor->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready("head", function() {
jQuery("#x_MessengerColor:not(.ew-template #x_MessengerColor)").colorpicker(ew.colorPickerOptions);
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->CreatedAt->Visible) { // CreatedAt ?>
    <div id="r_CreatedAt"<?= $Page->CreatedAt->rowAttributes() ?>>
        <label id="elh_theuserprofile_CreatedAt" for="x_CreatedAt" class="<?= $Page->LeftColumnClass ?>"><?= $Page->CreatedAt->caption() ?><?= $Page->CreatedAt->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->CreatedAt->cellAttributes() ?>>
<span id="el_theuserprofile_CreatedAt">
<input type="<?= $Page->CreatedAt->getInputTextType() ?>" name="x_CreatedAt" id="x_CreatedAt" data-table="theuserprofile" data-field="x_CreatedAt" value="<?= $Page->CreatedAt->getEditValue() ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->CreatedAt->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->CreatedAt->formatPattern()) ?>"<?= $Page->CreatedAt->editAttributes() ?> aria-describedby="x_CreatedAt_help">
<?= $Page->CreatedAt->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->CreatedAt->getErrorMessage() ?></div>
<?php if (!$Page->CreatedAt->ReadOnly && !$Page->CreatedAt->Disabled && !isset($Page->CreatedAt->EditAttrs["readonly"]) && !isset($Page->CreatedAt->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["ftheuserprofileadd", "datetimepicker"], function () {
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
        "ftheuserprofileadd",
        "x_CreatedAt",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['ftheuserprofileadd', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("ftheuserprofileadd", "x_CreatedAt", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->CreatedBy->Visible) { // CreatedBy ?>
    <div id="r_CreatedBy"<?= $Page->CreatedBy->rowAttributes() ?>>
        <label id="elh_theuserprofile_CreatedBy" for="x_CreatedBy" class="<?= $Page->LeftColumnClass ?>"><?= $Page->CreatedBy->caption() ?><?= $Page->CreatedBy->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->CreatedBy->cellAttributes() ?>>
<span id="el_theuserprofile_CreatedBy">
    <select
        id="x_CreatedBy"
        name="x_CreatedBy"
        class="form-select ew-select<?= $Page->CreatedBy->isInvalidClass() ?>"
        <?php if (!$Page->CreatedBy->IsNativeSelect) { ?>
        data-select2-id="ftheuserprofileadd_x_CreatedBy"
        <?php } ?>
        data-table="theuserprofile"
        data-field="x_CreatedBy"
        data-value-separator="<?= $Page->CreatedBy->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->CreatedBy->getPlaceHolder()) ?>"
        <?= $Page->CreatedBy->editAttributes() ?>>
        <?= $Page->CreatedBy->selectOptionListHtml("x_CreatedBy") ?>
    </select>
    <?= $Page->CreatedBy->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->CreatedBy->getErrorMessage() ?></div>
<?= $Page->CreatedBy->Lookup->getParamTag($Page, "p_x_CreatedBy") ?>
<?php if (!$Page->CreatedBy->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("ftheuserprofileadd", function() {
    var options = { name: "x_CreatedBy", selectId: "ftheuserprofileadd_x_CreatedBy" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (ftheuserprofileadd.lists.CreatedBy?.lookupOptions.length) {
        options.data = { id: "x_CreatedBy", form: "ftheuserprofileadd" };
    } else {
        options.ajax = { id: "x_CreatedBy", form: "ftheuserprofileadd", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.theuserprofile.fields.CreatedBy.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="ftheuserprofileadd"><?= $Language->phrase("AddBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="ftheuserprofileadd" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
<?php // End of Card view by Masino Sinaga, September 10, 2023 ?>
<?php
$Page->showPageFooter();
?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(ftheuserprofileadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#ftheuserprofileadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("theuserprofile");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#ftheuserprofileadd:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
