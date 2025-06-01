<?php

namespace PHPMaker2025\ucarsip;

// Page object
$DispositionsAdd = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { dispositions: currentTable } });
var currentPageID = ew.PAGE_ID = "add";
var currentForm;
var fdispositionsadd;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fdispositionsadd")
        .setPageId("add")

        // Add fields
        .setFields([
            ["letter_id", [fields.letter_id.visible && fields.letter_id.required ? ew.Validators.required(fields.letter_id.caption) : null], fields.letter_id.isInvalid],
            ["dari_unit_id", [fields.dari_unit_id.visible && fields.dari_unit_id.required ? ew.Validators.required(fields.dari_unit_id.caption) : null, ew.Validators.integer], fields.dari_unit_id.isInvalid],
            ["ke_unit_id", [fields.ke_unit_id.visible && fields.ke_unit_id.required ? ew.Validators.required(fields.ke_unit_id.caption) : null, ew.Validators.integer], fields.ke_unit_id.isInvalid],
            ["catatan", [fields.catatan.visible && fields.catatan.required ? ew.Validators.required(fields.catatan.caption) : null], fields.catatan.isInvalid],
            ["status", [fields.status.visible && fields.status.required ? ew.Validators.required(fields.status.caption) : null], fields.status.isInvalid],
            ["created_at", [fields.created_at.visible && fields.created_at.required ? ew.Validators.required(fields.created_at.caption) : null], fields.created_at.isInvalid]
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
            "letter_id": <?= $Page->letter_id->toClientList($Page) ?>,
            "dari_unit_id": <?= $Page->dari_unit_id->toClientList($Page) ?>,
            "ke_unit_id": <?= $Page->ke_unit_id->toClientList($Page) ?>,
            "status": <?= $Page->status->toClientList($Page) ?>,
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
<form name="fdispositionsadd" id="fdispositionsadd" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="dispositions">
<input type="hidden" name="action" id="action" value="insert">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-add-div"><!-- page* -->
<?php if ($Page->letter_id->Visible) { // letter_id ?>
    <div id="r_letter_id"<?= $Page->letter_id->rowAttributes() ?>>
        <label id="elh_dispositions_letter_id" for="x_letter_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->letter_id->caption() ?><?= $Page->letter_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->letter_id->cellAttributes() ?>>
<span id="el_dispositions_letter_id">
    <select
        id="x_letter_id"
        name="x_letter_id"
        class="form-select ew-select<?= $Page->letter_id->isInvalidClass() ?>"
        <?php if (!$Page->letter_id->IsNativeSelect) { ?>
        data-select2-id="fdispositionsadd_x_letter_id"
        <?php } ?>
        data-table="dispositions"
        data-field="x_letter_id"
        data-value-separator="<?= $Page->letter_id->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->letter_id->getPlaceHolder()) ?>"
        <?= $Page->letter_id->editAttributes() ?>>
        <?= $Page->letter_id->selectOptionListHtml("x_letter_id") ?>
    </select>
    <?= $Page->letter_id->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->letter_id->getErrorMessage() ?></div>
<?= $Page->letter_id->Lookup->getParamTag($Page, "p_x_letter_id") ?>
<?php if (!$Page->letter_id->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fdispositionsadd", function() {
    var options = { name: "x_letter_id", selectId: "fdispositionsadd_x_letter_id" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fdispositionsadd.lists.letter_id?.lookupOptions.length) {
        options.data = { id: "x_letter_id", form: "fdispositionsadd" };
    } else {
        options.ajax = { id: "x_letter_id", form: "fdispositionsadd", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumResultsForSearch = Infinity;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.dispositions.fields.letter_id.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->dari_unit_id->Visible) { // dari_unit_id ?>
    <div id="r_dari_unit_id"<?= $Page->dari_unit_id->rowAttributes() ?>>
        <label id="elh_dispositions_dari_unit_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->dari_unit_id->caption() ?><?= $Page->dari_unit_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->dari_unit_id->cellAttributes() ?>>
<span id="el_dispositions_dari_unit_id">
<?php
if (IsRTL()) {
    $Page->dari_unit_id->EditAttrs["dir"] = "rtl";
}
?>
<span id="as_x_dari_unit_id" class="ew-auto-suggest">
    <input type="<?= $Page->dari_unit_id->getInputTextType() ?>" class="form-control" name="sv_x_dari_unit_id" id="sv_x_dari_unit_id" value="<?= $Page->dari_unit_id->getEditValue() ?>" autocomplete="off" size="30" placeholder="<?= HtmlEncode($Page->dari_unit_id->getPlaceHolder()) ?>" data-placeholder="<?= HtmlEncode($Page->dari_unit_id->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->dari_unit_id->formatPattern()) ?>"<?= $Page->dari_unit_id->editAttributes() ?> aria-describedby="x_dari_unit_id_help">
</span>
<selection-list hidden class="form-control" data-table="dispositions" data-field="x_dari_unit_id" data-input="sv_x_dari_unit_id" data-value-separator="<?= $Page->dari_unit_id->displayValueSeparatorAttribute() ?>" name="x_dari_unit_id" id="x_dari_unit_id" value="<?= HtmlEncode($Page->dari_unit_id->CurrentValue) ?>"></selection-list>
<?= $Page->dari_unit_id->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->dari_unit_id->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready("fdispositionsadd", function() {
    fdispositionsadd.createAutoSuggest(Object.assign({"id":"x_dari_unit_id","forceSelect":false}, { lookupAllDisplayFields: <?= $Page->dari_unit_id->Lookup->LookupAllDisplayFields ? "true" : "false" ?> }, ew.vars.tables.dispositions.fields.dari_unit_id.autoSuggestOptions));
});
</script>
<?= $Page->dari_unit_id->Lookup->getParamTag($Page, "p_x_dari_unit_id") ?>
<script<?= Nonce() ?>>
loadjs.ready(['fdispositionsadd', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fdispositionsadd", "x_dari_unit_id", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->ke_unit_id->Visible) { // ke_unit_id ?>
    <div id="r_ke_unit_id"<?= $Page->ke_unit_id->rowAttributes() ?>>
        <label id="elh_dispositions_ke_unit_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->ke_unit_id->caption() ?><?= $Page->ke_unit_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->ke_unit_id->cellAttributes() ?>>
<span id="el_dispositions_ke_unit_id">
<?php
if (IsRTL()) {
    $Page->ke_unit_id->EditAttrs["dir"] = "rtl";
}
?>
<span id="as_x_ke_unit_id" class="ew-auto-suggest">
    <input type="<?= $Page->ke_unit_id->getInputTextType() ?>" class="form-control" name="sv_x_ke_unit_id" id="sv_x_ke_unit_id" value="<?= $Page->ke_unit_id->getEditValue() ?>" autocomplete="off" size="30" placeholder="<?= HtmlEncode($Page->ke_unit_id->getPlaceHolder()) ?>" data-placeholder="<?= HtmlEncode($Page->ke_unit_id->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->ke_unit_id->formatPattern()) ?>"<?= $Page->ke_unit_id->editAttributes() ?> aria-describedby="x_ke_unit_id_help">
</span>
<selection-list hidden class="form-control" data-table="dispositions" data-field="x_ke_unit_id" data-input="sv_x_ke_unit_id" data-value-separator="<?= $Page->ke_unit_id->displayValueSeparatorAttribute() ?>" name="x_ke_unit_id" id="x_ke_unit_id" value="<?= HtmlEncode($Page->ke_unit_id->CurrentValue) ?>"></selection-list>
<?= $Page->ke_unit_id->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->ke_unit_id->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready("fdispositionsadd", function() {
    fdispositionsadd.createAutoSuggest(Object.assign({"id":"x_ke_unit_id","forceSelect":false}, { lookupAllDisplayFields: <?= $Page->ke_unit_id->Lookup->LookupAllDisplayFields ? "true" : "false" ?> }, ew.vars.tables.dispositions.fields.ke_unit_id.autoSuggestOptions));
});
</script>
<?= $Page->ke_unit_id->Lookup->getParamTag($Page, "p_x_ke_unit_id") ?>
<script<?= Nonce() ?>>
loadjs.ready(['fdispositionsadd', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fdispositionsadd", "x_ke_unit_id", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->catatan->Visible) { // catatan ?>
    <div id="r_catatan"<?= $Page->catatan->rowAttributes() ?>>
        <label id="elh_dispositions_catatan" for="x_catatan" class="<?= $Page->LeftColumnClass ?>"><?= $Page->catatan->caption() ?><?= $Page->catatan->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->catatan->cellAttributes() ?>>
<span id="el_dispositions_catatan">
<input type="<?= $Page->catatan->getInputTextType() ?>" name="x_catatan" id="x_catatan" data-table="dispositions" data-field="x_catatan" value="<?= $Page->catatan->getEditValue() ?>" size="30" maxlength="65535" placeholder="<?= HtmlEncode($Page->catatan->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->catatan->formatPattern()) ?>"<?= $Page->catatan->editAttributes() ?> aria-describedby="x_catatan_help">
<?= $Page->catatan->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->catatan->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
    <div id="r_status"<?= $Page->status->rowAttributes() ?>>
        <label id="elh_dispositions_status" class="<?= $Page->LeftColumnClass ?>"><?= $Page->status->caption() ?><?= $Page->status->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->status->cellAttributes() ?>>
<span id="el_dispositions_status">
<template id="tp_x_status">
    <div class="form-check">
        <input type="radio" class="form-check-input" data-table="dispositions" data-field="x_status" name="x_status" id="x_status"<?= $Page->status->editAttributes() ?>>
        <label class="form-check-label"></label>
    </div>
</template>
<div id="dsl_x_status" class="ew-item-list"></div>
<selection-list hidden
    id="x_status"
    name="x_status"
    value="<?= HtmlEncode($Page->status->CurrentValue) ?>"
    data-type="select-one"
    data-template="tp_x_status"
    data-target="dsl_x_status"
    data-repeatcolumn="5"
    class="form-control<?= $Page->status->isInvalidClass() ?>"
    data-table="dispositions"
    data-field="x_status"
    data-value-separator="<?= $Page->status->displayValueSeparatorAttribute() ?>"
    <?= $Page->status->editAttributes() ?>></selection-list>
<?= $Page->status->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->status->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="fdispositionsadd"><?= $Language->phrase("AddBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fdispositionsadd" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fdispositionsadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fdispositionsadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("dispositions");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#fdispositionsadd:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
