<?php

namespace PHPMaker2025\ucarsip;

// Page object
$SettingsAdd = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { settings: currentTable } });
var currentPageID = ew.PAGE_ID = "add";
var currentForm;
var fsettingsadd;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fsettingsadd")
        .setPageId("add")

        // Add fields
        .setFields([
            ["Option_Default", [fields.Option_Default.visible && fields.Option_Default.required ? ew.Validators.required(fields.Option_Default.caption) : null], fields.Option_Default.isInvalid],
            ["Show_Announcement", [fields.Show_Announcement.visible && fields.Show_Announcement.required ? ew.Validators.required(fields.Show_Announcement.caption) : null], fields.Show_Announcement.isInvalid],
            ["Use_Announcement_Table", [fields.Use_Announcement_Table.visible && fields.Use_Announcement_Table.required ? ew.Validators.required(fields.Use_Announcement_Table.caption) : null], fields.Use_Announcement_Table.isInvalid],
            ["Maintenance_Mode", [fields.Maintenance_Mode.visible && fields.Maintenance_Mode.required ? ew.Validators.required(fields.Maintenance_Mode.caption) : null], fields.Maintenance_Mode.isInvalid],
            ["Maintenance_Finish_DateTime", [fields.Maintenance_Finish_DateTime.visible && fields.Maintenance_Finish_DateTime.required ? ew.Validators.required(fields.Maintenance_Finish_DateTime.caption) : null, ew.Validators.datetime(fields.Maintenance_Finish_DateTime.clientFormatPattern)], fields.Maintenance_Finish_DateTime.isInvalid],
            ["Auto_Normal_After_Maintenance", [fields.Auto_Normal_After_Maintenance.visible && fields.Auto_Normal_After_Maintenance.required ? ew.Validators.required(fields.Auto_Normal_After_Maintenance.caption) : null], fields.Auto_Normal_After_Maintenance.isInvalid]
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
            "Option_Default": <?= $Page->Option_Default->toClientList($Page) ?>,
            "Show_Announcement": <?= $Page->Show_Announcement->toClientList($Page) ?>,
            "Use_Announcement_Table": <?= $Page->Use_Announcement_Table->toClientList($Page) ?>,
            "Maintenance_Mode": <?= $Page->Maintenance_Mode->toClientList($Page) ?>,
            "Auto_Normal_After_Maintenance": <?= $Page->Auto_Normal_After_Maintenance->toClientList($Page) ?>,
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
<form name="fsettingsadd" id="fsettingsadd" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="settings">
<input type="hidden" name="action" id="action" value="insert">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-add-div"><!-- page* -->
<?php if ($Page->Option_Default->Visible) { // Option_Default ?>
    <div id="r_Option_Default"<?= $Page->Option_Default->rowAttributes() ?>>
        <label id="elh_settings_Option_Default" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Option_Default->caption() ?><?= $Page->Option_Default->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Option_Default->cellAttributes() ?>>
<span id="el_settings_Option_Default">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Option_Default->isInvalidClass() ?>" data-table="settings" data-field="x_Option_Default" data-boolean name="x_Option_Default" id="x_Option_Default" value="1"<?= ConvertToBool($Page->Option_Default->CurrentValue) ? " checked" : "" ?><?= $Page->Option_Default->editAttributes() ?> aria-describedby="x_Option_Default_help">
    <div class="invalid-feedback"><?= $Page->Option_Default->getErrorMessage() ?></div>
</div>
<?= $Page->Option_Default->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
    <div id="r_Show_Announcement"<?= $Page->Show_Announcement->rowAttributes() ?>>
        <label id="elh_settings_Show_Announcement" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Show_Announcement->caption() ?><?= $Page->Show_Announcement->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Show_Announcement->cellAttributes() ?>>
<span id="el_settings_Show_Announcement">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Show_Announcement->isInvalidClass() ?>" data-table="settings" data-field="x_Show_Announcement" data-boolean name="x_Show_Announcement" id="x_Show_Announcement" value="1"<?= ConvertToBool($Page->Show_Announcement->CurrentValue) ? " checked" : "" ?><?= $Page->Show_Announcement->editAttributes() ?> aria-describedby="x_Show_Announcement_help">
    <div class="invalid-feedback"><?= $Page->Show_Announcement->getErrorMessage() ?></div>
</div>
<?= $Page->Show_Announcement->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
    <div id="r_Use_Announcement_Table"<?= $Page->Use_Announcement_Table->rowAttributes() ?>>
        <label id="elh_settings_Use_Announcement_Table" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Use_Announcement_Table->caption() ?><?= $Page->Use_Announcement_Table->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Use_Announcement_Table->cellAttributes() ?>>
<span id="el_settings_Use_Announcement_Table">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Use_Announcement_Table->isInvalidClass() ?>" data-table="settings" data-field="x_Use_Announcement_Table" data-boolean name="x_Use_Announcement_Table" id="x_Use_Announcement_Table" value="1"<?= ConvertToBool($Page->Use_Announcement_Table->CurrentValue) ? " checked" : "" ?><?= $Page->Use_Announcement_Table->editAttributes() ?> aria-describedby="x_Use_Announcement_Table_help">
    <div class="invalid-feedback"><?= $Page->Use_Announcement_Table->getErrorMessage() ?></div>
</div>
<?= $Page->Use_Announcement_Table->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
    <div id="r_Maintenance_Mode"<?= $Page->Maintenance_Mode->rowAttributes() ?>>
        <label id="elh_settings_Maintenance_Mode" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Maintenance_Mode->caption() ?><?= $Page->Maintenance_Mode->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Maintenance_Mode->cellAttributes() ?>>
<span id="el_settings_Maintenance_Mode">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Maintenance_Mode->isInvalidClass() ?>" data-table="settings" data-field="x_Maintenance_Mode" data-boolean name="x_Maintenance_Mode" id="x_Maintenance_Mode" value="1"<?= ConvertToBool($Page->Maintenance_Mode->CurrentValue) ? " checked" : "" ?><?= $Page->Maintenance_Mode->editAttributes() ?> aria-describedby="x_Maintenance_Mode_help">
    <div class="invalid-feedback"><?= $Page->Maintenance_Mode->getErrorMessage() ?></div>
</div>
<?= $Page->Maintenance_Mode->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
    <div id="r_Maintenance_Finish_DateTime"<?= $Page->Maintenance_Finish_DateTime->rowAttributes() ?>>
        <label id="elh_settings_Maintenance_Finish_DateTime" for="x_Maintenance_Finish_DateTime" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Maintenance_Finish_DateTime->caption() ?><?= $Page->Maintenance_Finish_DateTime->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Maintenance_Finish_DateTime->cellAttributes() ?>>
<span id="el_settings_Maintenance_Finish_DateTime">
<input type="<?= $Page->Maintenance_Finish_DateTime->getInputTextType() ?>" name="x_Maintenance_Finish_DateTime" id="x_Maintenance_Finish_DateTime" data-table="settings" data-field="x_Maintenance_Finish_DateTime" value="<?= $Page->Maintenance_Finish_DateTime->getEditValue() ?>" placeholder="<?= HtmlEncode($Page->Maintenance_Finish_DateTime->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Maintenance_Finish_DateTime->formatPattern()) ?>"<?= $Page->Maintenance_Finish_DateTime->editAttributes() ?> aria-describedby="x_Maintenance_Finish_DateTime_help">
<?= $Page->Maintenance_Finish_DateTime->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Maintenance_Finish_DateTime->getErrorMessage() ?></div>
<?php if (!$Page->Maintenance_Finish_DateTime->ReadOnly && !$Page->Maintenance_Finish_DateTime->Disabled && !isset($Page->Maintenance_Finish_DateTime->EditAttrs["readonly"]) && !isset($Page->Maintenance_Finish_DateTime->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["fsettingsadd", "datetimepicker"], function () {
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
        "fsettingsadd",
        "x_Maintenance_Finish_DateTime",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['fsettingsadd', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fsettingsadd", "x_Maintenance_Finish_DateTime", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
    <div id="r_Auto_Normal_After_Maintenance"<?= $Page->Auto_Normal_After_Maintenance->rowAttributes() ?>>
        <label id="elh_settings_Auto_Normal_After_Maintenance" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Auto_Normal_After_Maintenance->caption() ?><?= $Page->Auto_Normal_After_Maintenance->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Auto_Normal_After_Maintenance->cellAttributes() ?>>
<span id="el_settings_Auto_Normal_After_Maintenance">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Auto_Normal_After_Maintenance->isInvalidClass() ?>" data-table="settings" data-field="x_Auto_Normal_After_Maintenance" data-boolean name="x_Auto_Normal_After_Maintenance" id="x_Auto_Normal_After_Maintenance" value="1"<?= ConvertToBool($Page->Auto_Normal_After_Maintenance->CurrentValue) ? " checked" : "" ?><?= $Page->Auto_Normal_After_Maintenance->editAttributes() ?> aria-describedby="x_Auto_Normal_After_Maintenance_help">
    <div class="invalid-feedback"><?= $Page->Auto_Normal_After_Maintenance->getErrorMessage() ?></div>
</div>
<?= $Page->Auto_Normal_After_Maintenance->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="fsettingsadd"><?= $Language->phrase("AddBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fsettingsadd" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fsettingsadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fsettingsadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("settings");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#fsettingsadd:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
