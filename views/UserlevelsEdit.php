<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UserlevelsEdit = &$Page;
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
<form name="fuserlevelsedit" id="fuserlevelsedit" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { userlevels: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var fuserlevelsedit;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fuserlevelsedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["ID", [fields.ID.visible && fields.ID.required ? ew.Validators.required(fields.ID.caption) : null, ew.Validators.userLevelId, ew.Validators.integer], fields.ID.isInvalid],
            ["Name", [fields.Name.visible && fields.Name.required ? ew.Validators.required(fields.Name.caption) : null, ew.Validators.userLevelName('ID')], fields.Name.isInvalid],
            ["Hierarchy", [fields.Hierarchy.visible && fields.Hierarchy.required ? ew.Validators.required(fields.Hierarchy.caption) : null], fields.Hierarchy.isInvalid],
            ["Level_Origin", [fields.Level_Origin.visible && fields.Level_Origin.required ? ew.Validators.required(fields.Level_Origin.caption) : null], fields.Level_Origin.isInvalid]
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
            "Hierarchy": <?= $Page->Hierarchy->toClientList($Page) ?>,
            "Level_Origin": <?= $Page->Level_Origin->toClientList($Page) ?>,
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
<input type="hidden" name="t" value="userlevels">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->ID->Visible) { // ID ?>
    <div id="r_ID"<?= $Page->ID->rowAttributes() ?>>
        <label id="elh_userlevels_ID" for="x_ID" class="<?= $Page->LeftColumnClass ?>"><?= $Page->ID->caption() ?><?= $Page->ID->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->ID->cellAttributes() ?>>
<span id="el_userlevels_ID">
<input type="<?= $Page->ID->getInputTextType() ?>" name="x_ID" id="x_ID" data-table="userlevels" data-field="x_ID" value="<?= $Page->ID->getEditValue() ?>" size="10" maxlength="5" placeholder="<?= HtmlEncode($Page->ID->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->ID->formatPattern()) ?>"<?= $Page->ID->editAttributes() ?> aria-describedby="x_ID_help">
<?= $Page->ID->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->ID->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(['fuserlevelsedit', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserlevelsedit", "x_ID", jQuery.extend(true, "", options));
});
</script>
<input type="hidden" data-table="userlevels" data-field="x_ID" data-hidden="1" data-old name="o_ID" id="o_ID" value="<?= HtmlEncode($Page->ID->OldValue ?? $Page->ID->CurrentValue) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Name->Visible) { // Name ?>
    <div id="r_Name"<?= $Page->Name->rowAttributes() ?>>
        <label id="elh_userlevels_Name" for="x_Name" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Name->caption() ?><?= $Page->Name->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Name->cellAttributes() ?>>
<span id="el_userlevels_Name">
<input type="<?= $Page->Name->getInputTextType() ?>" name="x_Name" id="x_Name" data-table="userlevels" data-field="x_Name" value="<?= $Page->Name->getEditValue() ?>" size="50" maxlength="255" placeholder="<?= HtmlEncode($Page->Name->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Name->formatPattern()) ?>"<?= $Page->Name->editAttributes() ?> aria-describedby="x_Name_help">
<?= $Page->Name->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Name->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Hierarchy->Visible) { // Hierarchy ?>
    <div id="r_Hierarchy"<?= $Page->Hierarchy->rowAttributes() ?>>
        <label id="elh_userlevels_Hierarchy" for="x_Hierarchy" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Hierarchy->caption() ?><?= $Page->Hierarchy->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Hierarchy->cellAttributes() ?>>
<span id="el_userlevels_Hierarchy">
    <select
        id="x_Hierarchy[]"
        name="x_Hierarchy[]"
        class="form-select ew-select<?= $Page->Hierarchy->isInvalidClass() ?>"
        <?php if (!$Page->Hierarchy->IsNativeSelect) { ?>
        data-select2-id="fuserlevelsedit_x_Hierarchy[]"
        <?php } ?>
        data-table="userlevels"
        data-field="x_Hierarchy"
        multiple
        size="1"
        data-value-separator="<?= $Page->Hierarchy->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->Hierarchy->getPlaceHolder()) ?>"
        <?= $Page->Hierarchy->editAttributes() ?>>
        <?= $Page->Hierarchy->selectOptionListHtml("x_Hierarchy[]") ?>
    </select>
    <?= $Page->Hierarchy->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->Hierarchy->getErrorMessage() ?></div>
<?= $Page->Hierarchy->Lookup->getParamTag($Page, "p_x_Hierarchy") ?>
<?php if (!$Page->Hierarchy->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fuserlevelsedit", function() {
    var options = { name: "x_Hierarchy[]", selectId: "fuserlevelsedit_x_Hierarchy[]" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.multiple = true;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fuserlevelsedit.lists.Hierarchy?.lookupOptions.length) {
        options.data = { id: "x_Hierarchy[]", form: "fuserlevelsedit" };
    } else {
        options.ajax = { id: "x_Hierarchy[]", form: "fuserlevelsedit", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.userlevels.fields.Hierarchy.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Level_Origin->Visible) { // Level_Origin ?>
    <div id="r_Level_Origin"<?= $Page->Level_Origin->rowAttributes() ?>>
        <label id="elh_userlevels_Level_Origin" for="x_Level_Origin" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Level_Origin->caption() ?><?= $Page->Level_Origin->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Level_Origin->cellAttributes() ?>>
<span id="el_userlevels_Level_Origin">
    <select
        id="x_Level_Origin"
        name="x_Level_Origin"
        class="form-select ew-select<?= $Page->Level_Origin->isInvalidClass() ?>"
        <?php if (!$Page->Level_Origin->IsNativeSelect) { ?>
        data-select2-id="fuserlevelsedit_x_Level_Origin"
        <?php } ?>
        data-table="userlevels"
        data-field="x_Level_Origin"
        data-value-separator="<?= $Page->Level_Origin->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->Level_Origin->getPlaceHolder()) ?>"
        <?= $Page->Level_Origin->editAttributes() ?>>
        <?= $Page->Level_Origin->selectOptionListHtml("x_Level_Origin") ?>
    </select>
    <?= $Page->Level_Origin->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->Level_Origin->getErrorMessage() ?></div>
<?= $Page->Level_Origin->Lookup->getParamTag($Page, "p_x_Level_Origin") ?>
<?php if (!$Page->Level_Origin->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fuserlevelsedit", function() {
    var options = { name: "x_Level_Origin", selectId: "fuserlevelsedit_x_Level_Origin" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fuserlevelsedit.lists.Level_Origin?.lookupOptions.length) {
        options.data = { id: "x_Level_Origin", form: "fuserlevelsedit" };
    } else {
        options.ajax = { id: "x_Level_Origin", form: "fuserlevelsedit", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.userlevels.fields.Level_Origin.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?php
    if (in_array("users", explode(",", $Page->getCurrentDetailTable())) && $users->DetailEdit) {
?>
<?php if ($Page->getCurrentDetailTable() != "") { ?>
<?php if (Container("users")->Count > 0) { // Begin of added by Masino Sinaga, September 16, 2023 ?>
<h4 class="ew-detail-caption"><?= $Language->tablePhrase("users", "TblCaption") ?></h4>
<?php } else { ?>
<h4 class="ew-detail-caption"><?= $Language->tablePhrase("users", "TblCaption") ?></h4>
<?php } // End of added by Masino Sinaga, September 16, 2023 ?>
<?php } ?>
<?php include_once "UsersGrid.php" ?>
<?php } ?>
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="fuserlevelsedit"><?= $Language->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fuserlevelsedit" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fuserlevelsedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fuserlevelsedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("userlevels");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#fuserlevelsedit:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
