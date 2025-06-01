<?php

namespace PHPMaker2025\ucarsip;

// Page object
$LanguagesEdit = &$Page;
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
<form name="flanguagesedit" id="flanguagesedit" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { languages: currentTable } });
var currentPageID = ew.PAGE_ID = "edit";
var currentForm;
var flanguagesedit;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("flanguagesedit")
        .setPageId("edit")

        // Add fields
        .setFields([
            ["Language_Code", [fields.Language_Code.visible && fields.Language_Code.required ? ew.Validators.required(fields.Language_Code.caption) : null], fields.Language_Code.isInvalid],
            ["Language_Name", [fields.Language_Name.visible && fields.Language_Name.required ? ew.Validators.required(fields.Language_Name.caption) : null], fields.Language_Name.isInvalid],
            ["Default", [fields.Default.visible && fields.Default.required ? ew.Validators.required(fields.Default.caption) : null], fields.Default.isInvalid],
            ["Site_Logo", [fields.Site_Logo.visible && fields.Site_Logo.required ? ew.Validators.required(fields.Site_Logo.caption) : null], fields.Site_Logo.isInvalid],
            ["Site_Title", [fields.Site_Title.visible && fields.Site_Title.required ? ew.Validators.required(fields.Site_Title.caption) : null], fields.Site_Title.isInvalid],
            ["Default_Thousands_Separator", [fields.Default_Thousands_Separator.visible && fields.Default_Thousands_Separator.required ? ew.Validators.required(fields.Default_Thousands_Separator.caption) : null], fields.Default_Thousands_Separator.isInvalid],
            ["Default_Decimal_Point", [fields.Default_Decimal_Point.visible && fields.Default_Decimal_Point.required ? ew.Validators.required(fields.Default_Decimal_Point.caption) : null], fields.Default_Decimal_Point.isInvalid],
            ["Default_Currency_Symbol", [fields.Default_Currency_Symbol.visible && fields.Default_Currency_Symbol.required ? ew.Validators.required(fields.Default_Currency_Symbol.caption) : null], fields.Default_Currency_Symbol.isInvalid],
            ["Default_Money_Thousands_Separator", [fields.Default_Money_Thousands_Separator.visible && fields.Default_Money_Thousands_Separator.required ? ew.Validators.required(fields.Default_Money_Thousands_Separator.caption) : null], fields.Default_Money_Thousands_Separator.isInvalid],
            ["Default_Money_Decimal_Point", [fields.Default_Money_Decimal_Point.visible && fields.Default_Money_Decimal_Point.required ? ew.Validators.required(fields.Default_Money_Decimal_Point.caption) : null], fields.Default_Money_Decimal_Point.isInvalid],
            ["Terms_And_Condition_Text", [fields.Terms_And_Condition_Text.visible && fields.Terms_And_Condition_Text.required ? ew.Validators.required(fields.Terms_And_Condition_Text.caption) : null], fields.Terms_And_Condition_Text.isInvalid],
            ["Announcement_Text", [fields.Announcement_Text.visible && fields.Announcement_Text.required ? ew.Validators.required(fields.Announcement_Text.caption) : null], fields.Announcement_Text.isInvalid],
            ["About_Text", [fields.About_Text.visible && fields.About_Text.required ? ew.Validators.required(fields.About_Text.caption) : null], fields.About_Text.isInvalid]
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
            "Default": <?= $Page->Default->toClientList($Page) ?>,
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
<input type="hidden" name="t" value="languages">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-edit-div"><!-- page* -->
<?php if ($Page->Language_Code->Visible) { // Language_Code ?>
    <div id="r_Language_Code"<?= $Page->Language_Code->rowAttributes() ?>>
        <label id="elh_languages_Language_Code" for="x_Language_Code" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Language_Code->caption() ?><?= $Page->Language_Code->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Language_Code->cellAttributes() ?>>
<span id="el_languages_Language_Code">
<input type="<?= $Page->Language_Code->getInputTextType() ?>" name="x_Language_Code" id="x_Language_Code" data-table="languages" data-field="x_Language_Code" value="<?= $Page->Language_Code->getEditValue() ?>" size="30" maxlength="5" placeholder="<?= HtmlEncode($Page->Language_Code->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Language_Code->formatPattern()) ?>"<?= $Page->Language_Code->editAttributes() ?> aria-describedby="x_Language_Code_help">
<?= $Page->Language_Code->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Language_Code->getErrorMessage() ?></div>
<input type="hidden" data-table="languages" data-field="x_Language_Code" data-hidden="1" data-old name="o_Language_Code" id="o_Language_Code" value="<?= HtmlEncode($Page->Language_Code->OldValue ?? $Page->Language_Code->CurrentValue) ?>">
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Language_Name->Visible) { // Language_Name ?>
    <div id="r_Language_Name"<?= $Page->Language_Name->rowAttributes() ?>>
        <label id="elh_languages_Language_Name" for="x_Language_Name" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Language_Name->caption() ?><?= $Page->Language_Name->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Language_Name->cellAttributes() ?>>
<span id="el_languages_Language_Name">
<input type="<?= $Page->Language_Name->getInputTextType() ?>" name="x_Language_Name" id="x_Language_Name" data-table="languages" data-field="x_Language_Name" value="<?= $Page->Language_Name->getEditValue() ?>" size="30" maxlength="20" placeholder="<?= HtmlEncode($Page->Language_Name->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Language_Name->formatPattern()) ?>"<?= $Page->Language_Name->editAttributes() ?> aria-describedby="x_Language_Name_help">
<?= $Page->Language_Name->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Language_Name->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Default->Visible) { // Default ?>
    <div id="r_Default"<?= $Page->Default->rowAttributes() ?>>
        <label id="elh_languages_Default" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Default->caption() ?><?= $Page->Default->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Default->cellAttributes() ?>>
<span id="el_languages_Default">
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" class="form-check-input<?= $Page->Default->isInvalidClass() ?>" data-table="languages" data-field="x_Default" data-boolean name="x_Default" id="x_Default" value="1"<?= ConvertToBool($Page->Default->CurrentValue) ? " checked" : "" ?><?= $Page->Default->editAttributes() ?> aria-describedby="x_Default_help">
    <div class="invalid-feedback"><?= $Page->Default->getErrorMessage() ?></div>
</div>
<?= $Page->Default->getCustomMessage() ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Site_Logo->Visible) { // Site_Logo ?>
    <div id="r_Site_Logo"<?= $Page->Site_Logo->rowAttributes() ?>>
        <label id="elh_languages_Site_Logo" for="x_Site_Logo" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Site_Logo->caption() ?><?= $Page->Site_Logo->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Site_Logo->cellAttributes() ?>>
<span id="el_languages_Site_Logo">
<input type="<?= $Page->Site_Logo->getInputTextType() ?>" name="x_Site_Logo" id="x_Site_Logo" data-table="languages" data-field="x_Site_Logo" value="<?= $Page->Site_Logo->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->Site_Logo->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Site_Logo->formatPattern()) ?>"<?= $Page->Site_Logo->editAttributes() ?> aria-describedby="x_Site_Logo_help">
<?= $Page->Site_Logo->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Site_Logo->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Site_Title->Visible) { // Site_Title ?>
    <div id="r_Site_Title"<?= $Page->Site_Title->rowAttributes() ?>>
        <label id="elh_languages_Site_Title" for="x_Site_Title" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Site_Title->caption() ?><?= $Page->Site_Title->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Site_Title->cellAttributes() ?>>
<span id="el_languages_Site_Title">
<input type="<?= $Page->Site_Title->getInputTextType() ?>" name="x_Site_Title" id="x_Site_Title" data-table="languages" data-field="x_Site_Title" value="<?= $Page->Site_Title->getEditValue() ?>" size="30" maxlength="100" placeholder="<?= HtmlEncode($Page->Site_Title->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Site_Title->formatPattern()) ?>"<?= $Page->Site_Title->editAttributes() ?> aria-describedby="x_Site_Title_help">
<?= $Page->Site_Title->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Site_Title->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Default_Thousands_Separator->Visible) { // Default_Thousands_Separator ?>
    <div id="r_Default_Thousands_Separator"<?= $Page->Default_Thousands_Separator->rowAttributes() ?>>
        <label id="elh_languages_Default_Thousands_Separator" for="x_Default_Thousands_Separator" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Default_Thousands_Separator->caption() ?><?= $Page->Default_Thousands_Separator->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Default_Thousands_Separator->cellAttributes() ?>>
<span id="el_languages_Default_Thousands_Separator">
<input type="<?= $Page->Default_Thousands_Separator->getInputTextType() ?>" name="x_Default_Thousands_Separator" id="x_Default_Thousands_Separator" data-table="languages" data-field="x_Default_Thousands_Separator" value="<?= $Page->Default_Thousands_Separator->getEditValue() ?>" size="30" maxlength="5" placeholder="<?= HtmlEncode($Page->Default_Thousands_Separator->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Default_Thousands_Separator->formatPattern()) ?>"<?= $Page->Default_Thousands_Separator->editAttributes() ?> aria-describedby="x_Default_Thousands_Separator_help">
<?= $Page->Default_Thousands_Separator->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Default_Thousands_Separator->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Default_Decimal_Point->Visible) { // Default_Decimal_Point ?>
    <div id="r_Default_Decimal_Point"<?= $Page->Default_Decimal_Point->rowAttributes() ?>>
        <label id="elh_languages_Default_Decimal_Point" for="x_Default_Decimal_Point" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Default_Decimal_Point->caption() ?><?= $Page->Default_Decimal_Point->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Default_Decimal_Point->cellAttributes() ?>>
<span id="el_languages_Default_Decimal_Point">
<input type="<?= $Page->Default_Decimal_Point->getInputTextType() ?>" name="x_Default_Decimal_Point" id="x_Default_Decimal_Point" data-table="languages" data-field="x_Default_Decimal_Point" value="<?= $Page->Default_Decimal_Point->getEditValue() ?>" size="30" maxlength="5" placeholder="<?= HtmlEncode($Page->Default_Decimal_Point->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Default_Decimal_Point->formatPattern()) ?>"<?= $Page->Default_Decimal_Point->editAttributes() ?> aria-describedby="x_Default_Decimal_Point_help">
<?= $Page->Default_Decimal_Point->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Default_Decimal_Point->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Default_Currency_Symbol->Visible) { // Default_Currency_Symbol ?>
    <div id="r_Default_Currency_Symbol"<?= $Page->Default_Currency_Symbol->rowAttributes() ?>>
        <label id="elh_languages_Default_Currency_Symbol" for="x_Default_Currency_Symbol" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Default_Currency_Symbol->caption() ?><?= $Page->Default_Currency_Symbol->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Default_Currency_Symbol->cellAttributes() ?>>
<span id="el_languages_Default_Currency_Symbol">
<input type="<?= $Page->Default_Currency_Symbol->getInputTextType() ?>" name="x_Default_Currency_Symbol" id="x_Default_Currency_Symbol" data-table="languages" data-field="x_Default_Currency_Symbol" value="<?= $Page->Default_Currency_Symbol->getEditValue() ?>" size="30" maxlength="10" placeholder="<?= HtmlEncode($Page->Default_Currency_Symbol->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Default_Currency_Symbol->formatPattern()) ?>"<?= $Page->Default_Currency_Symbol->editAttributes() ?> aria-describedby="x_Default_Currency_Symbol_help">
<?= $Page->Default_Currency_Symbol->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Default_Currency_Symbol->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Default_Money_Thousands_Separator->Visible) { // Default_Money_Thousands_Separator ?>
    <div id="r_Default_Money_Thousands_Separator"<?= $Page->Default_Money_Thousands_Separator->rowAttributes() ?>>
        <label id="elh_languages_Default_Money_Thousands_Separator" for="x_Default_Money_Thousands_Separator" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Default_Money_Thousands_Separator->caption() ?><?= $Page->Default_Money_Thousands_Separator->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Default_Money_Thousands_Separator->cellAttributes() ?>>
<span id="el_languages_Default_Money_Thousands_Separator">
<input type="<?= $Page->Default_Money_Thousands_Separator->getInputTextType() ?>" name="x_Default_Money_Thousands_Separator" id="x_Default_Money_Thousands_Separator" data-table="languages" data-field="x_Default_Money_Thousands_Separator" value="<?= $Page->Default_Money_Thousands_Separator->getEditValue() ?>" size="30" maxlength="5" placeholder="<?= HtmlEncode($Page->Default_Money_Thousands_Separator->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Default_Money_Thousands_Separator->formatPattern()) ?>"<?= $Page->Default_Money_Thousands_Separator->editAttributes() ?> aria-describedby="x_Default_Money_Thousands_Separator_help">
<?= $Page->Default_Money_Thousands_Separator->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Default_Money_Thousands_Separator->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Default_Money_Decimal_Point->Visible) { // Default_Money_Decimal_Point ?>
    <div id="r_Default_Money_Decimal_Point"<?= $Page->Default_Money_Decimal_Point->rowAttributes() ?>>
        <label id="elh_languages_Default_Money_Decimal_Point" for="x_Default_Money_Decimal_Point" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Default_Money_Decimal_Point->caption() ?><?= $Page->Default_Money_Decimal_Point->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Default_Money_Decimal_Point->cellAttributes() ?>>
<span id="el_languages_Default_Money_Decimal_Point">
<input type="<?= $Page->Default_Money_Decimal_Point->getInputTextType() ?>" name="x_Default_Money_Decimal_Point" id="x_Default_Money_Decimal_Point" data-table="languages" data-field="x_Default_Money_Decimal_Point" value="<?= $Page->Default_Money_Decimal_Point->getEditValue() ?>" size="30" maxlength="5" placeholder="<?= HtmlEncode($Page->Default_Money_Decimal_Point->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Default_Money_Decimal_Point->formatPattern()) ?>"<?= $Page->Default_Money_Decimal_Point->editAttributes() ?> aria-describedby="x_Default_Money_Decimal_Point_help">
<?= $Page->Default_Money_Decimal_Point->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Default_Money_Decimal_Point->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Terms_And_Condition_Text->Visible) { // Terms_And_Condition_Text ?>
    <div id="r_Terms_And_Condition_Text"<?= $Page->Terms_And_Condition_Text->rowAttributes() ?>>
        <label id="elh_languages_Terms_And_Condition_Text" for="x_Terms_And_Condition_Text" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Terms_And_Condition_Text->caption() ?><?= $Page->Terms_And_Condition_Text->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Terms_And_Condition_Text->cellAttributes() ?>>
<span id="el_languages_Terms_And_Condition_Text">
<textarea data-table="languages" data-field="x_Terms_And_Condition_Text" name="x_Terms_And_Condition_Text" id="x_Terms_And_Condition_Text" cols="50" rows="5" placeholder="<?= HtmlEncode($Page->Terms_And_Condition_Text->getPlaceHolder()) ?>"<?= $Page->Terms_And_Condition_Text->editAttributes() ?> aria-describedby="x_Terms_And_Condition_Text_help"><?= $Page->Terms_And_Condition_Text->getEditValue() ?></textarea>
<?= $Page->Terms_And_Condition_Text->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Terms_And_Condition_Text->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Announcement_Text->Visible) { // Announcement_Text ?>
    <div id="r_Announcement_Text"<?= $Page->Announcement_Text->rowAttributes() ?>>
        <label id="elh_languages_Announcement_Text" for="x_Announcement_Text" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Announcement_Text->caption() ?><?= $Page->Announcement_Text->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Announcement_Text->cellAttributes() ?>>
<span id="el_languages_Announcement_Text">
<textarea data-table="languages" data-field="x_Announcement_Text" name="x_Announcement_Text" id="x_Announcement_Text" cols="50" rows="5" placeholder="<?= HtmlEncode($Page->Announcement_Text->getPlaceHolder()) ?>"<?= $Page->Announcement_Text->editAttributes() ?> aria-describedby="x_Announcement_Text_help"><?= $Page->Announcement_Text->getEditValue() ?></textarea>
<?= $Page->Announcement_Text->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Announcement_Text->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->About_Text->Visible) { // About_Text ?>
    <div id="r_About_Text"<?= $Page->About_Text->rowAttributes() ?>>
        <label id="elh_languages_About_Text" for="x_About_Text" class="<?= $Page->LeftColumnClass ?>"><?= $Page->About_Text->caption() ?><?= $Page->About_Text->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->About_Text->cellAttributes() ?>>
<span id="el_languages_About_Text">
<textarea data-table="languages" data-field="x_About_Text" name="x_About_Text" id="x_About_Text" cols="50" rows="5" placeholder="<?= HtmlEncode($Page->About_Text->getPlaceHolder()) ?>"<?= $Page->About_Text->editAttributes() ?> aria-describedby="x_About_Text_help"><?= $Page->About_Text->getEditValue() ?></textarea>
<?= $Page->About_Text->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->About_Text->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="flanguagesedit"><?= $Language->phrase("SaveBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="flanguagesedit" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flanguagesedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#flanguagesedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("languages");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#flanguagesedit:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
