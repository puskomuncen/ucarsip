<?php

namespace PHPMaker2025\ucarsip;

// Page object
$TracksEdit = &$Page;
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
<form name="ftracksedit" id="ftracksedit" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { tracks: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var ftracksedit;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("ftracksedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["track_id", [fields.track_id.visible && fields.track_id.required ? ew.Validators.required(fields.track_id.caption) : null], fields.track_id.isInvalid],
            ["letter_id", [fields.letter_id.visible && fields.letter_id.required ? ew.Validators.required(fields.letter_id.caption) : null], fields.letter_id.isInvalid],
            ["user_id", [fields.user_id.visible && fields.user_id.required ? ew.Validators.required(fields.user_id.caption) : null, ew.Validators.integer], fields.user_id.isInvalid],
            ["_action", [fields._action.visible && fields._action.required ? ew.Validators.required(fields._action.caption) : null], fields._action.isInvalid],
            ["keterangan", [fields.keterangan.visible && fields.keterangan.required ? ew.Validators.required(fields.keterangan.caption) : null], fields.keterangan.isInvalid],
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
            "letter_id": <?= $Page->letter_id->toClientList($Page) ?>,
            "_action": <?= $Page->_action->toClientList($Page) ?>,
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
<input type="hidden" name="t" value="tracks">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->track_id->Visible) { // track_id ?>
    <div id="r_track_id"<?= $Page->track_id->rowAttributes() ?>>
        <label id="elh_tracks_track_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->track_id->caption() ?><?= $Page->track_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->track_id->cellAttributes() ?>>
<span id="el_tracks_track_id">
<span<?= $Page->track_id->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= HtmlEncode(RemoveHtml($Page->track_id->getDisplayValue($Page->track_id->getEditValue()))) ?>"></span>
<input type="hidden" data-table="tracks" data-field="x_track_id" data-hidden="1" name="x_track_id" id="x_track_id" value="<?= HtmlEncode($Page->track_id->CurrentValue) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->letter_id->Visible) { // letter_id ?>
    <div id="r_letter_id"<?= $Page->letter_id->rowAttributes() ?>>
        <label id="elh_tracks_letter_id" for="x_letter_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->letter_id->caption() ?><?= $Page->letter_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->letter_id->cellAttributes() ?>>
<span id="el_tracks_letter_id">
    <select
        id="x_letter_id"
        name="x_letter_id"
        class="form-select ew-select<?= $Page->letter_id->isInvalidClass() ?>"
        <?php if (!$Page->letter_id->IsNativeSelect) { ?>
        data-select2-id="ftracksedit_x_letter_id"
        <?php } ?>
        data-table="tracks"
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
loadjs.ready("ftracksedit", function() {
    var options = { name: "x_letter_id", selectId: "ftracksedit_x_letter_id" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (ftracksedit.lists.letter_id?.lookupOptions.length) {
        options.data = { id: "x_letter_id", form: "ftracksedit" };
    } else {
        options.ajax = { id: "x_letter_id", form: "ftracksedit", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumResultsForSearch = Infinity;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.tracks.fields.letter_id.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->user_id->Visible) { // user_id ?>
    <div id="r_user_id"<?= $Page->user_id->rowAttributes() ?>>
        <label id="elh_tracks_user_id" for="x_user_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->user_id->caption() ?><?= $Page->user_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->user_id->cellAttributes() ?>>
<span id="el_tracks_user_id">
<input type="<?= $Page->user_id->getInputTextType() ?>" name="x_user_id" id="x_user_id" data-table="tracks" data-field="x_user_id" value="<?= $Page->user_id->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->user_id->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->user_id->formatPattern()) ?>"<?= $Page->user_id->editAttributes() ?> aria-describedby="x_user_id_help">
<?= $Page->user_id->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->user_id->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(['ftracksedit', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("ftracksedit", "x_user_id", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->_action->Visible) { // action ?>
    <div id="r__action"<?= $Page->_action->rowAttributes() ?>>
        <label id="elh_tracks__action" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_action->caption() ?><?= $Page->_action->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_action->cellAttributes() ?>>
<span id="el_tracks__action">
<template id="tp_x__action">
    <div class="form-check">
        <input type="radio" class="form-check-input" data-table="tracks" data-field="x__action" name="x__action" id="x__action"<?= $Page->_action->editAttributes() ?>>
        <label class="form-check-label"></label>
    </div>
</template>
<div id="dsl_x__action" class="ew-item-list"></div>
<selection-list hidden
    id="x__action"
    name="x__action"
    value="<?= HtmlEncode($Page->_action->CurrentValue) ?>"
    data-type="select-one"
    data-template="tp_x__action"
    data-target="dsl_x__action"
    data-repeatcolumn="5"
    class="form-control<?= $Page->_action->isInvalidClass() ?>"
    data-table="tracks"
    data-field="x__action"
    data-value-separator="<?= $Page->_action->displayValueSeparatorAttribute() ?>"
    <?= $Page->_action->editAttributes() ?>></selection-list>
<?= $Page->_action->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->_action->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->keterangan->Visible) { // keterangan ?>
    <div id="r_keterangan"<?= $Page->keterangan->rowAttributes() ?>>
        <label id="elh_tracks_keterangan" for="x_keterangan" class="<?= $Page->LeftColumnClass ?>"><?= $Page->keterangan->caption() ?><?= $Page->keterangan->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->keterangan->cellAttributes() ?>>
<span id="el_tracks_keterangan">
<input type="<?= $Page->keterangan->getInputTextType() ?>" name="x_keterangan" id="x_keterangan" data-table="tracks" data-field="x_keterangan" value="<?= $Page->keterangan->getEditValue() ?>" size="30" maxlength="65535" placeholder="<?= HtmlEncode($Page->keterangan->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->keterangan->formatPattern()) ?>"<?= $Page->keterangan->editAttributes() ?> aria-describedby="x_keterangan_help">
<?= $Page->keterangan->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->keterangan->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->created_at->Visible) { // created_at ?>
    <div id="r_created_at"<?= $Page->created_at->rowAttributes() ?>>
        <label id="elh_tracks_created_at" for="x_created_at" class="<?= $Page->LeftColumnClass ?>"><?= $Page->created_at->caption() ?><?= $Page->created_at->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->created_at->cellAttributes() ?>>
<span id="el_tracks_created_at">
<input type="<?= $Page->created_at->getInputTextType() ?>" name="x_created_at" id="x_created_at" data-table="tracks" data-field="x_created_at" value="<?= $Page->created_at->getEditValue() ?>" placeholder="<?= HtmlEncode($Page->created_at->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->created_at->formatPattern()) ?>"<?= $Page->created_at->editAttributes() ?> aria-describedby="x_created_at_help">
<?= $Page->created_at->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->created_at->getErrorMessage() ?></div>
<?php if (!$Page->created_at->ReadOnly && !$Page->created_at->Disabled && !isset($Page->created_at->EditAttrs["readonly"]) && !isset($Page->created_at->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["ftracksedit", "datetimepicker"], function () {
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
        "ftracksedit",
        "x_created_at",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['ftracksedit', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("ftracksedit", "x_created_at", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="ftracksedit"><?= $Language->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="ftracksedit" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(ftracksedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#ftracksedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("tracks");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#ftracksedit:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
