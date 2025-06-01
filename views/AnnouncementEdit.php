<?php

namespace PHPMaker2025\ucarsip;

// Page object
$AnnouncementEdit = &$Page;
?>
<?php $Page->showPageHeader(); ?>
<?php
$Page->showMessage();
?>
<main class="edit">
<?php // Begin of Card view by Masino Sinaga, September 10, 2023 ?>
<?php if (!$Page->IsModal) { ?>
<div class="col-md-12">
  <div class="card shadow-sm">
    <div class="card-header">
	  <h4 class="card-title"><?php echo Language()->phrase("EditCaption"); ?></h4>
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
<form name="fannouncementedit" id="fannouncementedit" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { announcement: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var fannouncementedit;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fannouncementedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["Announcement_ID", [fields.Announcement_ID.visible && fields.Announcement_ID.required ? ew.Validators.required(fields.Announcement_ID.caption) : null], fields.Announcement_ID.isInvalid],
            ["Is_Active", [fields.Is_Active.visible && fields.Is_Active.required ? ew.Validators.required(fields.Is_Active.caption) : null], fields.Is_Active.isInvalid],
            ["Topic", [fields.Topic.visible && fields.Topic.required ? ew.Validators.required(fields.Topic.caption) : null], fields.Topic.isInvalid],
            ["Message", [fields.Message.visible && fields.Message.required ? ew.Validators.required(fields.Message.caption) : null], fields.Message.isInvalid],
            ["Date_LastUpdate", [fields.Date_LastUpdate.visible && fields.Date_LastUpdate.required ? ew.Validators.required(fields.Date_LastUpdate.caption) : null, ew.Validators.datetime(fields.Date_LastUpdate.clientFormatPattern)], fields.Date_LastUpdate.isInvalid],
            ["_Language", [fields._Language.visible && fields._Language.required ? ew.Validators.required(fields._Language.caption) : null], fields._Language.isInvalid],
            ["Auto_Publish", [fields.Auto_Publish.visible && fields.Auto_Publish.required ? ew.Validators.required(fields.Auto_Publish.caption) : null], fields.Auto_Publish.isInvalid],
            ["Date_Start", [fields.Date_Start.visible && fields.Date_Start.required ? ew.Validators.required(fields.Date_Start.caption) : null, ew.Validators.datetime(fields.Date_Start.clientFormatPattern)], fields.Date_Start.isInvalid],
            ["Date_End", [fields.Date_End.visible && fields.Date_End.required ? ew.Validators.required(fields.Date_End.caption) : null, ew.Validators.datetime(fields.Date_End.clientFormatPattern)], fields.Date_End.isInvalid],
            ["Date_Created", [fields.Date_Created.visible && fields.Date_Created.required ? ew.Validators.required(fields.Date_Created.caption) : null, ew.Validators.datetime(fields.Date_Created.clientFormatPattern)], fields.Date_Created.isInvalid],
            ["Created_By", [fields.Created_By.visible && fields.Created_By.required ? ew.Validators.required(fields.Created_By.caption) : null], fields.Created_By.isInvalid],
            ["Translated_ID", [fields.Translated_ID.visible && fields.Translated_ID.required ? ew.Validators.required(fields.Translated_ID.caption) : null], fields.Translated_ID.isInvalid]
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
            "Is_Active": <?= $Page->Is_Active->toClientList($Page) ?>,
            "_Language": <?= $Page->_Language->toClientList($Page) ?>,
            "Auto_Publish": <?= $Page->Auto_Publish->toClientList($Page) ?>,
            "Created_By": <?= $Page->Created_By->toClientList($Page) ?>,
            "Translated_ID": <?= $Page->Translated_ID->toClientList($Page) ?>,
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
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="announcement">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->Announcement_ID->Visible) { // Announcement_ID ?>
    <div id="r_Announcement_ID"<?= $Page->Announcement_ID->rowAttributes() ?>>
        <label id="elh_announcement_Announcement_ID" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Announcement_ID->caption() ?><?= $Page->Announcement_ID->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Announcement_ID->cellAttributes() ?>>
<span id="el_announcement_Announcement_ID">
<span<?= $Page->Announcement_ID->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= HtmlEncode(RemoveHtml($Page->Announcement_ID->getDisplayValue($Page->Announcement_ID->getEditValue()))) ?>"></span>
<input type="hidden" data-table="announcement" data-field="x_Announcement_ID" data-hidden="1" name="x_Announcement_ID" id="x_Announcement_ID" value="<?= HtmlEncode($Page->Announcement_ID->CurrentValue) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Is_Active->Visible) { // Is_Active ?>
    <div id="r_Is_Active"<?= $Page->Is_Active->rowAttributes() ?>>
        <label id="elh_announcement_Is_Active" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Is_Active->caption() ?><?= $Page->Is_Active->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Is_Active->cellAttributes() ?>>
<span id="el_announcement_Is_Active">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Is_Active->isInvalidClass() ?>" data-table="announcement" data-field="x_Is_Active" data-boolean name="x_Is_Active" id="x_Is_Active" value="1"<?= ConvertToBool($Page->Is_Active->CurrentValue) ? " checked" : "" ?><?= $Page->Is_Active->editAttributes() ?> aria-describedby="x_Is_Active_help">
    <div class="invalid-feedback"><?= $Page->Is_Active->getErrorMessage() ?></div>
</div>
<?= $Page->Is_Active->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
    <div id="r_Topic"<?= $Page->Topic->rowAttributes() ?>>
        <label id="elh_announcement_Topic" for="x_Topic" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Topic->caption() ?><?= $Page->Topic->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Topic->cellAttributes() ?>>
<span id="el_announcement_Topic">
<input type="<?= $Page->Topic->getInputTextType() ?>" name="x_Topic" id="x_Topic" data-table="announcement" data-field="x_Topic" value="<?= $Page->Topic->getEditValue() ?>" size="50" maxlength="50" placeholder="<?= HtmlEncode($Page->Topic->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Topic->formatPattern()) ?>"<?= $Page->Topic->editAttributes() ?> aria-describedby="x_Topic_help">
<?= $Page->Topic->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Topic->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Message->Visible) { // Message ?>
    <div id="r_Message"<?= $Page->Message->rowAttributes() ?>>
        <label id="elh_announcement_Message" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Message->caption() ?><?= $Page->Message->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Message->cellAttributes() ?>>
<span id="el_announcement_Message">
<?php $Page->Message->EditAttrs->appendClass("editor"); ?>
<textarea data-table="announcement" data-field="x_Message" name="x_Message" id="x_Message" cols="50" rows="5" placeholder="<?= HtmlEncode($Page->Message->getPlaceHolder()) ?>"<?= $Page->Message->editAttributes() ?> aria-describedby="x_Message_help"><?= $Page->Message->getEditValue() ?></textarea>
<?= $Page->Message->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Message->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(["fannouncementedit", "editor"], function() {
    ew.createEditor("fannouncementedit", "x_Message", 50, 5, <?= $Page->Message->ReadOnly || false ? "true" : "false" ?>);
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Date_LastUpdate->Visible) { // Date_LastUpdate ?>
    <div id="r_Date_LastUpdate"<?= $Page->Date_LastUpdate->rowAttributes() ?>>
        <label id="elh_announcement_Date_LastUpdate" for="x_Date_LastUpdate" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Date_LastUpdate->caption() ?><?= $Page->Date_LastUpdate->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Date_LastUpdate->cellAttributes() ?>>
<span id="el_announcement_Date_LastUpdate">
<input type="<?= $Page->Date_LastUpdate->getInputTextType() ?>" name="x_Date_LastUpdate" id="x_Date_LastUpdate" data-table="announcement" data-field="x_Date_LastUpdate" value="<?= $Page->Date_LastUpdate->getEditValue() ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->Date_LastUpdate->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Date_LastUpdate->formatPattern()) ?>"<?= $Page->Date_LastUpdate->editAttributes() ?> aria-describedby="x_Date_LastUpdate_help">
<?= $Page->Date_LastUpdate->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Date_LastUpdate->getErrorMessage() ?></div>
<?php if (!$Page->Date_LastUpdate->ReadOnly && !$Page->Date_LastUpdate->Disabled && !isset($Page->Date_LastUpdate->EditAttrs["readonly"]) && !isset($Page->Date_LastUpdate->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fannouncementedit", "datetimepicker"], function () {
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
        "fannouncementedit",
        "x_Date_LastUpdate",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fannouncementedit', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fannouncementedit", "x_Date_LastUpdate", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->_Language->Visible) { // Language ?>
    <div id="r__Language"<?= $Page->_Language->rowAttributes() ?>>
        <label id="elh_announcement__Language" for="x__Language" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_Language->caption() ?><?= $Page->_Language->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_Language->cellAttributes() ?>>
<span id="el_announcement__Language">
    <select
        id="x__Language"
        name="x__Language"
        class="form-select ew-select<?= $Page->_Language->isInvalidClass() ?>"
        <?php if (!$Page->_Language->IsNativeSelect) { ?>
        data-select2-id="fannouncementedit_x__Language"
        <?php } ?>
        data-table="announcement"
        data-field="x__Language"
        data-value-separator="<?= $Page->_Language->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->_Language->getPlaceHolder()) ?>"
        <?= $Page->_Language->editAttributes() ?>>
        <?= $Page->_Language->selectOptionListHtml("x__Language") ?>
    </select>
    <?= $Page->_Language->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->_Language->getErrorMessage() ?></div>
<?= $Page->_Language->Lookup->getParamTag($Page, "p_x__Language") ?>
<?php if (!$Page->_Language->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fannouncementedit", function() {
    var options = { name: "x__Language", selectId: "fannouncementedit_x__Language" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fannouncementedit.lists._Language?.lookupOptions.length) {
        options.data = { id: "x__Language", form: "fannouncementedit" };
    } else {
        options.ajax = { id: "x__Language", form: "fannouncementedit", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.announcement.fields._Language.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Auto_Publish->Visible) { // Auto_Publish ?>
    <div id="r_Auto_Publish"<?= $Page->Auto_Publish->rowAttributes() ?>>
        <label id="elh_announcement_Auto_Publish" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Auto_Publish->caption() ?><?= $Page->Auto_Publish->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Auto_Publish->cellAttributes() ?>>
<span id="el_announcement_Auto_Publish">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Auto_Publish->isInvalidClass() ?>" data-table="announcement" data-field="x_Auto_Publish" data-boolean name="x_Auto_Publish" id="x_Auto_Publish" value="1"<?= ConvertToBool($Page->Auto_Publish->CurrentValue) ? " checked" : "" ?><?= $Page->Auto_Publish->editAttributes() ?> aria-describedby="x_Auto_Publish_help">
    <div class="invalid-feedback"><?= $Page->Auto_Publish->getErrorMessage() ?></div>
</div>
<?= $Page->Auto_Publish->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Date_Start->Visible) { // Date_Start ?>
    <div id="r_Date_Start"<?= $Page->Date_Start->rowAttributes() ?>>
        <label id="elh_announcement_Date_Start" for="x_Date_Start" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Date_Start->caption() ?><?= $Page->Date_Start->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Date_Start->cellAttributes() ?>>
<span id="el_announcement_Date_Start">
<input type="<?= $Page->Date_Start->getInputTextType() ?>" name="x_Date_Start" id="x_Date_Start" data-table="announcement" data-field="x_Date_Start" value="<?= $Page->Date_Start->getEditValue() ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->Date_Start->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Date_Start->formatPattern()) ?>"<?= $Page->Date_Start->editAttributes() ?> aria-describedby="x_Date_Start_help">
<?= $Page->Date_Start->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Date_Start->getErrorMessage() ?></div>
<?php if (!$Page->Date_Start->ReadOnly && !$Page->Date_Start->Disabled && !isset($Page->Date_Start->EditAttrs["readonly"]) && !isset($Page->Date_Start->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fannouncementedit", "datetimepicker"], function () {
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
        "fannouncementedit",
        "x_Date_Start",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fannouncementedit', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fannouncementedit", "x_Date_Start", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Date_End->Visible) { // Date_End ?>
    <div id="r_Date_End"<?= $Page->Date_End->rowAttributes() ?>>
        <label id="elh_announcement_Date_End" for="x_Date_End" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Date_End->caption() ?><?= $Page->Date_End->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Date_End->cellAttributes() ?>>
<span id="el_announcement_Date_End">
<input type="<?= $Page->Date_End->getInputTextType() ?>" name="x_Date_End" id="x_Date_End" data-table="announcement" data-field="x_Date_End" value="<?= $Page->Date_End->getEditValue() ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->Date_End->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Date_End->formatPattern()) ?>"<?= $Page->Date_End->editAttributes() ?> aria-describedby="x_Date_End_help">
<?= $Page->Date_End->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Date_End->getErrorMessage() ?></div>
<?php if (!$Page->Date_End->ReadOnly && !$Page->Date_End->Disabled && !isset($Page->Date_End->EditAttrs["readonly"]) && !isset($Page->Date_End->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fannouncementedit", "datetimepicker"], function () {
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
        "fannouncementedit",
        "x_Date_End",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fannouncementedit', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fannouncementedit", "x_Date_End", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Date_Created->Visible) { // Date_Created ?>
    <div id="r_Date_Created"<?= $Page->Date_Created->rowAttributes() ?>>
        <label id="elh_announcement_Date_Created" for="x_Date_Created" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Date_Created->caption() ?><?= $Page->Date_Created->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Date_Created->cellAttributes() ?>>
<span id="el_announcement_Date_Created">
<input type="<?= $Page->Date_Created->getInputTextType() ?>" name="x_Date_Created" id="x_Date_Created" data-table="announcement" data-field="x_Date_Created" value="<?= $Page->Date_Created->getEditValue() ?>" size="17" maxlength="19" placeholder="<?= HtmlEncode($Page->Date_Created->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Date_Created->formatPattern()) ?>"<?= $Page->Date_Created->editAttributes() ?> aria-describedby="x_Date_Created_help">
<?= $Page->Date_Created->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Date_Created->getErrorMessage() ?></div>
<?php if (!$Page->Date_Created->ReadOnly && !$Page->Date_Created->Disabled && !isset($Page->Date_Created->EditAttrs["readonly"]) && !isset($Page->Date_Created->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fannouncementedit", "datetimepicker"], function () {
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
        "fannouncementedit",
        "x_Date_Created",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fannouncementedit', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fannouncementedit", "x_Date_Created", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Created_By->Visible) { // Created_By ?>
    <div id="r_Created_By"<?= $Page->Created_By->rowAttributes() ?>>
        <label id="elh_announcement_Created_By" for="x_Created_By" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Created_By->caption() ?><?= $Page->Created_By->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Created_By->cellAttributes() ?>>
<span id="el_announcement_Created_By">
    <select
        id="x_Created_By"
        name="x_Created_By"
        class="form-select ew-select<?= $Page->Created_By->isInvalidClass() ?>"
        <?php if (!$Page->Created_By->IsNativeSelect) { ?>
        data-select2-id="fannouncementedit_x_Created_By"
        <?php } ?>
        data-table="announcement"
        data-field="x_Created_By"
        data-value-separator="<?= $Page->Created_By->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->Created_By->getPlaceHolder()) ?>"
        <?= $Page->Created_By->editAttributes() ?>>
        <?= $Page->Created_By->selectOptionListHtml("x_Created_By") ?>
    </select>
    <?= $Page->Created_By->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->Created_By->getErrorMessage() ?></div>
<?= $Page->Created_By->Lookup->getParamTag($Page, "p_x_Created_By") ?>
<?php if (!$Page->Created_By->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fannouncementedit", function() {
    var options = { name: "x_Created_By", selectId: "fannouncementedit_x_Created_By" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fannouncementedit.lists.Created_By?.lookupOptions.length) {
        options.data = { id: "x_Created_By", form: "fannouncementedit" };
    } else {
        options.ajax = { id: "x_Created_By", form: "fannouncementedit", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.announcement.fields.Created_By.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Translated_ID->Visible) { // Translated_ID ?>
    <div id="r_Translated_ID"<?= $Page->Translated_ID->rowAttributes() ?>>
        <label id="elh_announcement_Translated_ID" for="x_Translated_ID" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Translated_ID->caption() ?><?= $Page->Translated_ID->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Translated_ID->cellAttributes() ?>>
<span id="el_announcement_Translated_ID">
    <select
        id="x_Translated_ID"
        name="x_Translated_ID"
        class="form-select ew-select<?= $Page->Translated_ID->isInvalidClass() ?>"
        <?php if (!$Page->Translated_ID->IsNativeSelect) { ?>
        data-select2-id="fannouncementedit_x_Translated_ID"
        <?php } ?>
        data-table="announcement"
        data-field="x_Translated_ID"
        data-value-separator="<?= $Page->Translated_ID->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->Translated_ID->getPlaceHolder()) ?>"
        <?= $Page->Translated_ID->editAttributes() ?>>
        <?= $Page->Translated_ID->selectOptionListHtml("x_Translated_ID") ?>
    </select>
    <?= $Page->Translated_ID->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->Translated_ID->getErrorMessage() ?></div>
<?= $Page->Translated_ID->Lookup->getParamTag($Page, "p_x_Translated_ID") ?>
<?php if (!$Page->Translated_ID->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fannouncementedit", function() {
    var options = { name: "x_Translated_ID", selectId: "fannouncementedit_x_Translated_ID" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fannouncementedit.lists.Translated_ID?.lookupOptions.length) {
        options.data = { id: "x_Translated_ID", form: "fannouncementedit" };
    } else {
        options.ajax = { id: "x_Translated_ID", form: "fannouncementedit", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.announcement.fields.Translated_ID.selectOptions);
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
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="fannouncementedit"><?= $Language->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fannouncementedit" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
</main>
<?php
$Page->showPageFooter();
?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fannouncementedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fannouncementedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("announcement");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#fannouncementedit:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
