<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UserlevelpermissionsAdd = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { userlevelpermissions: currentTable } });
var currentPageID = ew.PAGE_ID = "add";
var currentForm;
var fuserlevelpermissionsadd;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fuserlevelpermissionsadd")
        .setPageId("add")

        // Add fields
        .setFields([
            ["UserLevelID", [fields.UserLevelID.visible && fields.UserLevelID.required ? ew.Validators.required(fields.UserLevelID.caption) : null, ew.Validators.integer], fields.UserLevelID.isInvalid],
            ["_TableName", [fields._TableName.visible && fields._TableName.required ? ew.Validators.required(fields._TableName.caption) : null], fields._TableName.isInvalid],
            ["_Permission", [fields._Permission.visible && fields._Permission.required ? ew.Validators.required(fields._Permission.caption) : null, ew.Validators.integer], fields._Permission.isInvalid]
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
<form name="fuserlevelpermissionsadd" id="fuserlevelpermissionsadd" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="userlevelpermissions">
<input type="hidden" name="action" id="action" value="insert">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-add-div"><!-- page* -->
<?php if ($Page->UserLevelID->Visible) { // UserLevelID ?>
    <div id="r_UserLevelID"<?= $Page->UserLevelID->rowAttributes() ?>>
        <label id="elh_userlevelpermissions_UserLevelID" for="x_UserLevelID" class="<?= $Page->LeftColumnClass ?>"><?= $Page->UserLevelID->caption() ?><?= $Page->UserLevelID->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->UserLevelID->cellAttributes() ?>>
<span id="el_userlevelpermissions_UserLevelID">
<input type="<?= $Page->UserLevelID->getInputTextType() ?>" name="x_UserLevelID" id="x_UserLevelID" data-table="userlevelpermissions" data-field="x_UserLevelID" value="<?= $Page->UserLevelID->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->UserLevelID->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->UserLevelID->formatPattern()) ?>"<?= $Page->UserLevelID->editAttributes() ?> aria-describedby="x_UserLevelID_help">
<?= $Page->UserLevelID->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->UserLevelID->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(['fuserlevelpermissionsadd', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserlevelpermissionsadd", "x_UserLevelID", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->_TableName->Visible) { // TableName ?>
    <div id="r__TableName"<?= $Page->_TableName->rowAttributes() ?>>
        <label id="elh_userlevelpermissions__TableName" for="x__TableName" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_TableName->caption() ?><?= $Page->_TableName->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_TableName->cellAttributes() ?>>
<span id="el_userlevelpermissions__TableName">
<input type="<?= $Page->_TableName->getInputTextType() ?>" name="x__TableName" id="x__TableName" data-table="userlevelpermissions" data-field="x__TableName" value="<?= $Page->_TableName->getEditValue() ?>" size="30" maxlength="255" placeholder="<?= HtmlEncode($Page->_TableName->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->_TableName->formatPattern()) ?>"<?= $Page->_TableName->editAttributes() ?> aria-describedby="x__TableName_help">
<?= $Page->_TableName->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->_TableName->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->_Permission->Visible) { // Permission ?>
    <div id="r__Permission"<?= $Page->_Permission->rowAttributes() ?>>
        <label id="elh_userlevelpermissions__Permission" for="x__Permission" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_Permission->caption() ?><?= $Page->_Permission->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_Permission->cellAttributes() ?>>
<span id="el_userlevelpermissions__Permission">
<input type="<?= $Page->_Permission->getInputTextType() ?>" name="x__Permission" id="x__Permission" data-table="userlevelpermissions" data-field="x__Permission" value="<?= $Page->_Permission->getEditValue() ?>" size="30" placeholder="<?= HtmlEncode($Page->_Permission->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->_Permission->formatPattern()) ?>"<?= $Page->_Permission->editAttributes() ?> aria-describedby="x__Permission_help">
<?= $Page->_Permission->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->_Permission->getErrorMessage() ?></div>
<script<?= Nonce() ?>>
loadjs.ready(['fuserlevelpermissionsadd', 'jqueryinputmask'], function() {
	options = {
		'alias': 'numeric',
		'autoUnmask': true,
		'jitMasking': false,
		'groupSeparator': '<?php echo $GROUPING_SEPARATOR ?>',
		'digits': 0,
		'radixPoint': '<?php echo $DECIMAL_SEPARATOR ?>',
		'removeMaskOnSubmit': true
	};
	ew.createjQueryInputMask("fuserlevelpermissionsadd", "x__Permission", jQuery.extend(true, "", options));
});
</script>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="fuserlevelpermissionsadd"><?= $Language->phrase("AddBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fuserlevelpermissionsadd" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fuserlevelpermissionsadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fuserlevelpermissionsadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("userlevelpermissions");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#fuserlevelpermissionsadd:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
