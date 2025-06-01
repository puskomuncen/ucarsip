<?php

namespace PHPMaker2025\ucarsip;

// Page object
$LettersEdit = &$Page;
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
<form name="flettersedit" id="flettersedit" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { letters: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var flettersedit;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("flettersedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["letter_id", [fields.letter_id.visible && fields.letter_id.required ? ew.Validators.required(fields.letter_id.caption) : null], fields.letter_id.isInvalid],
            ["nomor_surat", [fields.nomor_surat.visible && fields.nomor_surat.required ? ew.Validators.required(fields.nomor_surat.caption) : null], fields.nomor_surat.isInvalid],
            ["perihal", [fields.perihal.visible && fields.perihal.required ? ew.Validators.required(fields.perihal.caption) : null], fields.perihal.isInvalid],
            ["tanggal_surat", [fields.tanggal_surat.visible && fields.tanggal_surat.required ? ew.Validators.required(fields.tanggal_surat.caption) : null, ew.Validators.datetime(fields.tanggal_surat.clientFormatPattern)], fields.tanggal_surat.isInvalid],
            ["tanggal_terima", [fields.tanggal_terima.visible && fields.tanggal_terima.required ? ew.Validators.required(fields.tanggal_terima.caption) : null, ew.Validators.datetime(fields.tanggal_terima.clientFormatPattern)], fields.tanggal_terima.isInvalid],
            ["jenis", [fields.jenis.visible && fields.jenis.required ? ew.Validators.required(fields.jenis.caption) : null], fields.jenis.isInvalid],
            ["klasifikasi", [fields.klasifikasi.visible && fields.klasifikasi.required ? ew.Validators.required(fields.klasifikasi.caption) : null], fields.klasifikasi.isInvalid],
            ["pengirim", [fields.pengirim.visible && fields.pengirim.required ? ew.Validators.required(fields.pengirim.caption) : null], fields.pengirim.isInvalid],
            ["penerima_unit_id", [fields.penerima_unit_id.visible && fields.penerima_unit_id.required ? ew.Validators.required(fields.penerima_unit_id.caption) : null, ew.Validators.integer], fields.penerima_unit_id.isInvalid],
            ["file_url", [fields.file_url.visible && fields.file_url.required ? ew.Validators.required(fields.file_url.caption) : null], fields.file_url.isInvalid],
            ["status", [fields.status.visible && fields.status.required ? ew.Validators.required(fields.status.caption) : null], fields.status.isInvalid],
            ["created_by", [fields.created_by.visible && fields.created_by.required ? ew.Validators.required(fields.created_by.caption) : null], fields.created_by.isInvalid],
            ["created_at", [fields.created_at.visible && fields.created_at.required ? ew.Validators.required(fields.created_at.caption) : null], fields.created_at.isInvalid],
            ["updated_at", [fields.updated_at.visible && fields.updated_at.required ? ew.Validators.required(fields.updated_at.caption) : null], fields.updated_at.isInvalid]
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
            "jenis": <?= $Page->jenis->toClientList($Page) ?>,
            "klasifikasi": <?= $Page->klasifikasi->toClientList($Page) ?>,
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
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="letters">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->letter_id->Visible) { // letter_id ?>
    <div id="r_letter_id"<?= $Page->letter_id->rowAttributes() ?>>
        <label id="elh_letters_letter_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->letter_id->caption() ?><?= $Page->letter_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->letter_id->cellAttributes() ?>>
<span id="el_letters_letter_id">
<span<?= $Page->letter_id->viewAttributes() ?>>
<input type="text" readonly class="form-control-plaintext" value="<?= HtmlEncode(RemoveHtml($Page->letter_id->getDisplayValue($Page->letter_id->getEditValue()))) ?>"></span>
<input type="hidden" data-table="letters" data-field="x_letter_id" data-hidden="1" name="x_letter_id" id="x_letter_id" value="<?= HtmlEncode($Page->letter_id->CurrentValue) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->nomor_surat->Visible) { // nomor_surat ?>
    <div id="r_nomor_surat"<?= $Page->nomor_surat->rowAttributes() ?>>
        <label id="elh_letters_nomor_surat" for="x_nomor_surat" class="<?= $Page->LeftColumnClass ?>"><?= $Page->nomor_surat->caption() ?><?= $Page->nomor_surat->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->nomor_surat->cellAttributes() ?>>
<span id="el_letters_nomor_surat">
<input type="<?= $Page->nomor_surat->getInputTextType() ?>" name="x_nomor_surat" id="x_nomor_surat" data-table="letters" data-field="x_nomor_surat" value="<?= $Page->nomor_surat->getEditValue() ?>" size="30" maxlength="50" placeholder="<?= HtmlEncode($Page->nomor_surat->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->nomor_surat->formatPattern()) ?>"<?= $Page->nomor_surat->editAttributes() ?> aria-describedby="x_nomor_surat_help">
<?= $Page->nomor_surat->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->nomor_surat->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->perihal->Visible) { // perihal ?>
    <div id="r_perihal"<?= $Page->perihal->rowAttributes() ?>>
        <label id="elh_letters_perihal" for="x_perihal" class="<?= $Page->LeftColumnClass ?>"><?= $Page->perihal->caption() ?><?= $Page->perihal->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->perihal->cellAttributes() ?>>
<span id="el_letters_perihal">
<input type="<?= $Page->perihal->getInputTextType() ?>" name="x_perihal" id="x_perihal" data-table="letters" data-field="x_perihal" value="<?= $Page->perihal->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->perihal->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->perihal->formatPattern()) ?>"<?= $Page->perihal->editAttributes() ?> aria-describedby="x_perihal_help">
<?= $Page->perihal->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->perihal->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->tanggal_surat->Visible) { // tanggal_surat ?>
    <div id="r_tanggal_surat"<?= $Page->tanggal_surat->rowAttributes() ?>>
        <label id="elh_letters_tanggal_surat" for="x_tanggal_surat" class="<?= $Page->LeftColumnClass ?>"><?= $Page->tanggal_surat->caption() ?><?= $Page->tanggal_surat->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->tanggal_surat->cellAttributes() ?>>
<span id="el_letters_tanggal_surat">
<input type="<?= $Page->tanggal_surat->getInputTextType() ?>" name="x_tanggal_surat" id="x_tanggal_surat" data-table="letters" data-field="x_tanggal_surat" value="<?= $Page->tanggal_surat->getEditValue() ?>" placeholder="<?= HtmlEncode($Page->tanggal_surat->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->tanggal_surat->formatPattern()) ?>"<?= $Page->tanggal_surat->editAttributes() ?> aria-describedby="x_tanggal_surat_help">
<?= $Page->tanggal_surat->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->tanggal_surat->getErrorMessage() ?></div>
<?php if (!$Page->tanggal_surat->ReadOnly && !$Page->tanggal_surat->Disabled && !isset($Page->tanggal_surat->EditAttrs["readonly"]) && !isset($Page->tanggal_surat->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["flettersedit", "datetimepicker"], function () {
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
        "flettersedit",
        "x_tanggal_surat",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['flettersedit', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("flettersedit", "x_tanggal_surat", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->tanggal_terima->Visible) { // tanggal_terima ?>
    <div id="r_tanggal_terima"<?= $Page->tanggal_terima->rowAttributes() ?>>
        <label id="elh_letters_tanggal_terima" for="x_tanggal_terima" class="<?= $Page->LeftColumnClass ?>"><?= $Page->tanggal_terima->caption() ?><?= $Page->tanggal_terima->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->tanggal_terima->cellAttributes() ?>>
<span id="el_letters_tanggal_terima">
<input type="<?= $Page->tanggal_terima->getInputTextType() ?>" name="x_tanggal_terima" id="x_tanggal_terima" data-table="letters" data-field="x_tanggal_terima" value="<?= $Page->tanggal_terima->getEditValue() ?>" placeholder="<?= HtmlEncode($Page->tanggal_terima->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->tanggal_terima->formatPattern()) ?>"<?= $Page->tanggal_terima->editAttributes() ?> aria-describedby="x_tanggal_terima_help">
<?= $Page->tanggal_terima->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->tanggal_terima->getErrorMessage() ?></div>
<?php if (!$Page->tanggal_terima->ReadOnly && !$Page->tanggal_terima->Disabled && !isset($Page->tanggal_terima->EditAttrs["readonly"]) && !isset($Page->tanggal_terima->EditAttrs["disabled"])) { ?>
<script<?= Nonce() ?>>
loadjs.ready(["flettersedit", "datetimepicker"], function () {
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
        "flettersedit",
        "x_tanggal_terima",
        ew.deepAssign({"useCurrent":false,"display":{"sideBySide":false}}, options),
        {"inputGroup":true}
    );
});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready(['flettersedit', 'jqueryinputmask'], function() {
	options = {
		'jitMasking': false,
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("flettersedit", "x_tanggal_terima", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->jenis->Visible) { // jenis ?>
    <div id="r_jenis"<?= $Page->jenis->rowAttributes() ?>>
        <label id="elh_letters_jenis" class="<?= $Page->LeftColumnClass ?>"><?= $Page->jenis->caption() ?><?= $Page->jenis->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->jenis->cellAttributes() ?>>
<span id="el_letters_jenis">
<template id="tp_x_jenis">
    <div class="form-check">
        <input type="radio" class="form-check-input" data-table="letters" data-field="x_jenis" name="x_jenis" id="x_jenis"<?= $Page->jenis->editAttributes() ?>>
        <label class="form-check-label"></label>
    </div>
</template>
<div id="dsl_x_jenis" class="ew-item-list"></div>
<selection-list hidden
    id="x_jenis"
    name="x_jenis"
    value="<?= HtmlEncode($Page->jenis->CurrentValue) ?>"
    data-type="select-one"
    data-template="tp_x_jenis"
    data-target="dsl_x_jenis"
    data-repeatcolumn="5"
    class="form-control<?= $Page->jenis->isInvalidClass() ?>"
    data-table="letters"
    data-field="x_jenis"
    data-value-separator="<?= $Page->jenis->displayValueSeparatorAttribute() ?>"
    <?= $Page->jenis->editAttributes() ?>></selection-list>
<?= $Page->jenis->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->jenis->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->klasifikasi->Visible) { // klasifikasi ?>
    <div id="r_klasifikasi"<?= $Page->klasifikasi->rowAttributes() ?>>
        <label id="elh_letters_klasifikasi" class="<?= $Page->LeftColumnClass ?>"><?= $Page->klasifikasi->caption() ?><?= $Page->klasifikasi->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->klasifikasi->cellAttributes() ?>>
<span id="el_letters_klasifikasi">
<template id="tp_x_klasifikasi">
    <div class="form-check">
        <input type="radio" class="form-check-input" data-table="letters" data-field="x_klasifikasi" name="x_klasifikasi" id="x_klasifikasi"<?= $Page->klasifikasi->editAttributes() ?>>
        <label class="form-check-label"></label>
    </div>
</template>
<div id="dsl_x_klasifikasi" class="ew-item-list"></div>
<selection-list hidden
    id="x_klasifikasi"
    name="x_klasifikasi"
    value="<?= HtmlEncode($Page->klasifikasi->CurrentValue) ?>"
    data-type="select-one"
    data-template="tp_x_klasifikasi"
    data-target="dsl_x_klasifikasi"
    data-repeatcolumn="5"
    class="form-control<?= $Page->klasifikasi->isInvalidClass() ?>"
    data-table="letters"
    data-field="x_klasifikasi"
    data-value-separator="<?= $Page->klasifikasi->displayValueSeparatorAttribute() ?>"
    <?= $Page->klasifikasi->editAttributes() ?>></selection-list>
<?= $Page->klasifikasi->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->klasifikasi->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->pengirim->Visible) { // pengirim ?>
    <div id="r_pengirim"<?= $Page->pengirim->rowAttributes() ?>>
        <label id="elh_letters_pengirim" for="x_pengirim" class="<?= $Page->LeftColumnClass ?>"><?= $Page->pengirim->caption() ?><?= $Page->pengirim->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->pengirim->cellAttributes() ?>>
<span id="el_letters_pengirim">
<input type="<?= $Page->pengirim->getInputTextType() ?>" name="x_pengirim" id="x_pengirim" data-table="letters" data-field="x_pengirim" value="<?= $Page->pengirim->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->pengirim->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->pengirim->formatPattern()) ?>"<?= $Page->pengirim->editAttributes() ?> aria-describedby="x_pengirim_help">
<?= $Page->pengirim->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->pengirim->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->penerima_unit_id->Visible) { // penerima_unit_id ?>
    <div id="r_penerima_unit_id"<?= $Page->penerima_unit_id->rowAttributes() ?>>
        <label id="elh_letters_penerima_unit_id" for="x_penerima_unit_id" class="<?= $Page->LeftColumnClass ?>"><?= $Page->penerima_unit_id->caption() ?><?= $Page->penerima_unit_id->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->penerima_unit_id->cellAttributes() ?>>
<span id="el_letters_penerima_unit_id">
<input type="<?= $Page->penerima_unit_id->getInputTextType() ?>" name="x_penerima_unit_id" id="x_penerima_unit_id" data-table="letters" data-field="x_penerima_unit_id" value="<?= $Page->penerima_unit_id->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->penerima_unit_id->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->penerima_unit_id->formatPattern()) ?>"<?= $Page->penerima_unit_id->editAttributes() ?> aria-describedby="x_penerima_unit_id_help">
<?= $Page->penerima_unit_id->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->penerima_unit_id->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(['flettersedit', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("flettersedit", "x_penerima_unit_id", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->file_url->Visible) { // file_url ?>
    <div id="r_file_url"<?= $Page->file_url->rowAttributes() ?>>
        <label id="elh_letters_file_url" for="x_file_url" class="<?= $Page->LeftColumnClass ?>"><?= $Page->file_url->caption() ?><?= $Page->file_url->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->file_url->cellAttributes() ?>>
<span id="el_letters_file_url">
<input type="<?= $Page->file_url->getInputTextType() ?>" name="x_file_url" id="x_file_url" data-table="letters" data-field="x_file_url" value="<?= $Page->file_url->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->file_url->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->file_url->formatPattern()) ?>"<?= $Page->file_url->editAttributes() ?> aria-describedby="x_file_url_help">
<?= $Page->file_url->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->file_url->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
    <div id="r_status"<?= $Page->status->rowAttributes() ?>>
        <label id="elh_letters_status" class="<?= $Page->LeftColumnClass ?>"><?= $Page->status->caption() ?><?= $Page->status->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->status->cellAttributes() ?>>
<span id="el_letters_status">
<template id="tp_x_status">
    <div class="form-check">
        <input type="radio" class="form-check-input" data-table="letters" data-field="x_status" name="x_status" id="x_status"<?= $Page->status->editAttributes() ?>>
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
    data-table="letters"
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
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="flettersedit"><?= $Language->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="flettersedit" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flettersedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#flettersedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("letters");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#flettersedit:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
