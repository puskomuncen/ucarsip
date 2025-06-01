<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UnitsEdit = &$Page;
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
<form name="funitsedit" id="funitsedit" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { units: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var funitsedit;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("funitsedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["unit_id", [fields.unit_id.visible && fields.unit_id.required ? ew.Validators.required(fields.unit_id.caption) : null], fields.unit_id.isInvalid],
            ["nama_unit", [fields.nama_unit.visible && fields.nama_unit.required ? ew.Validators.required(fields.nama_unit.caption) : null], fields.nama_unit.isInvalid],
            ["kode_unit", [fields.kode_unit.visible && fields.kode_unit.required ? ew.Validators.required(fields.kode_unit.caption) : null], fields.kode_unit.isInvalid],
            ["created_at", [fields.created_at.visible && fields.created_at.required ? ew.Validators.required(fields.created_at.caption) : null, ew.Validators.datetime(fields.created_at.clientFormatPattern)], fields.created_at.isInvalid]
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
<input type="hidden" name="t" value="units">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->unit_id->Visible) { // unit_id ?>
    <div id="r_unit_id"<?= $Page->unit_id->rowAttributes() ?>>
        <label id="elh_units_unit_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->unit_id->caption() ?><?= $Page->unit_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->unit_id->cellAttributes() ?>>
<span id="el_units_unit_id">
<span<?= $Page->unit_id->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= HtmlEncode(RemoveHtml($Page->unit_id->getDisplayValue($Page->unit_id->getEditValue()))) ?>"></span>
<input type="hidden" data-table="units" data-field="x_unit_id" data-hidden="1" name="x_unit_id" id="x_unit_id" value="<?= HtmlEncode($Page->unit_id->CurrentValue) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->nama_unit->Visible) { // nama_unit ?>
    <div id="r_nama_unit"<?= $Page->nama_unit->rowAttributes() ?>>
        <label id="elh_units_nama_unit" for="x_nama_unit" class="<?= $Page->LeftColumnClass ?>"><?= $Page->nama_unit->caption() ?><?= $Page->nama_unit->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->nama_unit->cellAttributes() ?>>
<span id="el_units_nama_unit">
<input type="<?= $Page->nama_unit->getInputTextType() ?>" name="x_nama_unit" id="x_nama_unit" data-table="units" data-field="x_nama_unit" value="<?= $Page->nama_unit->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->nama_unit->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->nama_unit->formatPattern()) ?>"<?= $Page->nama_unit->editAttributes() ?> aria-describedby="x_nama_unit_help">
<?= $Page->nama_unit->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->nama_unit->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->kode_unit->Visible) { // kode_unit ?>
    <div id="r_kode_unit"<?= $Page->kode_unit->rowAttributes() ?>>
        <label id="elh_units_kode_unit" for="x_kode_unit" class="<?= $Page->LeftColumnClass ?>"><?= $Page->kode_unit->caption() ?><?= $Page->kode_unit->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->kode_unit->cellAttributes() ?>>
<span id="el_units_kode_unit">
<input type="<?= $Page->kode_unit->getInputTextType() ?>" name="x_kode_unit" id="x_kode_unit" data-table="units" data-field="x_kode_unit" value="<?= $Page->kode_unit->getEditValue() ?>" size="30" maxlength="20" placeholder="<?= HtmlEncode($Page->kode_unit->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->kode_unit->formatPattern()) ?>"<?= $Page->kode_unit->editAttributes() ?> aria-describedby="x_kode_unit_help">
<?= $Page->kode_unit->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->kode_unit->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->created_at->Visible) { // created_at ?>
    <div id="r_created_at"<?= $Page->created_at->rowAttributes() ?>>
        <label id="elh_units_created_at" for="x_created_at" class="<?= $Page->LeftColumnClass ?>"><?= $Page->created_at->caption() ?><?= $Page->created_at->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->created_at->cellAttributes() ?>>
<span id="el_units_created_at">
<input type="<?= $Page->created_at->getInputTextType() ?>" name="x_created_at" id="x_created_at" data-table="units" data-field="x_created_at" value="<?= $Page->created_at->getEditValue() ?>" placeholder="<?= HtmlEncode($Page->created_at->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->created_at->formatPattern()) ?>"<?= $Page->created_at->editAttributes() ?> aria-describedby="x_created_at_help">
<?= $Page->created_at->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->created_at->getErrorMessage() ?></div>
<?php if (!$Page->created_at->ReadOnly && !$Page->created_at->Disabled && !isset($Page->created_at->EditAttrs["readonly"]) && !isset($Page->created_at->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["funitsedit", "datetimepicker"], function () {
    let format = "<?= DateFormat(0) ?>",
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
        "funitsedit",
        "x_created_at",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['funitsedit', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("funitsedit", "x_created_at", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="funitsedit"><?= $Language->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="funitsedit" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(funitsedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#funitsedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("units");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#funitsedit:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
