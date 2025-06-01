<?php

namespace PHPMaker2025\ucarsip;

// Page object
$ChangePassword = &$Page;
?>
<script<?= Nonce() ?>>
loadjs.ready("head", function () {
    // Write your client script here, no need to add script tags.
});
</script>
<?php $Page->showPageHeader(); ?>
<div class="ew-change-pwd-box">
<div class="card">
<div class="card-body">
<?php
$Page->showMessage();
?>
<script<?= Nonce() ?>>
var fchange_password;
loadjs.ready(["wrapper", "head"], function() {
    let $ = jQuery;
    ew.PAGE_ID ||= "change_password";
    window.currentPageID ||= "change_password";
    let form = new ew.FormBuilder()
        .setId("fchange_password")
        // Add fields
        .addFields([
        <?php if (!IsPasswordReset()) { ?>
            ["opwd", ew.Validators.required(ew.language.phrase("OldPassword")), <?= $Page->OldPassword->IsInvalid ? "true" : "false" ?>],
        <?php } ?>
            ["npwd", [ew.Validators.required(ew.language.phrase("NewPassword")), ew.Validators.password(<?= $Page->NewPassword->Raw ? "true" : "false" ?>), ew.Validators.passwordStrength], <?= $Page->NewPassword->IsInvalid ? "true" : "false" ?>],
            ["cpwd", [ew.Validators.required(ew.language.phrase("ConfirmPassword")), ew.Validators.mismatchPassword], <?= $Page->ConfirmPassword->IsInvalid ? "true" : "false" ?>]
        ])

        // Validate
        .setValidate(
            async function() {
                if (!this.validateRequired)
                    return true; // Ignore validation
                let $ = jQuery,
                    fobj = this.getForm();
					$npwd = $(fobj).find("#npwd"); // added by Masino Sinaga, September 17, 2023

                // Validate fields
                if (!this.validateFields())
					//$('#btn-submit').attr('disabled', 'disabled'); // added by Masino Sinaga, September 17, 2023
					//$('#chkterms').prop('checked', false); // added by Masino Sinaga, September 17, 2023
                    return false;

                // Call Form_CustomValidate event
                if (!(await this.customValidate?.(fobj) ?? true)) {
                    this.focus();
                    return false;
                }
                return true;
            }
        )

        // Form_CustomValidate
        .setCustomValidate(
            function (fobj) { // DO NOT CHANGE THIS LINE! (except for adding "async" keyword)
                    // Your custom validation code in JAVASCRIPT here, return false if invalid.
                    return true;
                }
        )

        // Use JavaScript validation
        .setValidateRequired(ew.CLIENT_VALIDATE)
        .build();
    window[form.id] = form;
    window.currentForm ||= form;
    loadjs.done(form.id);
});
</script>
<form name="fchange_password" id="fchange_password" class="ew-form ew-change-pwd-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
    <?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
    <input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
    <input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
    <?php } ?>
    <input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
    <p class="login-box-msg"><?= $Language->phrase("ChangePasswordMessage") ?></p>
<?php if (!IsPasswordReset()) { ?>
    <div class="row">
        <div class="input-group">
            <input type="password" name="<?= $Page->OldPassword->FieldVar ?>" id="<?= $Page->OldPassword->FieldVar ?>" autocomplete="current-password" placeholder="<?= HtmlEncode($Language->phrase("OldPassword", true)) ?>"<?= $Page->OldPassword->editAttributes() ?>>
            <button type="button" class="btn btn-default ew-toggle-password rounded-end" data-ew-action="password"><i class="fa-solid fa-eye"></i></button>
        </div>
        <div class="invalid-feedback"><?= $Page->OldPassword->getErrorMessage() ?></div>
    </div>
<?php } ?>
    <div class="row gx-0">
        <div class="input-group px-0">
            <input type="password" name="<?= $Page->NewPassword->FieldVar ?>" id="<?= $Page->NewPassword->FieldVar ?>" autocomplete="new-password" placeholder="<?= HtmlEncode($Language->phrase("NewPassword", true)) ?>" data-password-strength="pst_npwd"<?= $Page->NewPassword->editAttributes() ?>>
            <button type="button" class="btn btn-default ew-toggle-password" data-ew-action="password"><i class="fa-solid fa-eye"></i></button>
            <button type="button" class="btn btn-default ew-password-generator rounded-end" title="<?= HtmlTitle($Language->phrase("GeneratePassword")) ?>" data-password-field="<?= $Page->NewPassword->FieldVar ?>" data-password-confirm="<?= $Page->ConfirmPassword->FieldVar ?>" data-password-strength="pst_npwd"><?= $Language->phrase("GeneratePassword") ?></button>
        </div>
        <div class="invalid-feedback"><?= $Page->NewPassword->getErrorMessage() ?></div>
        <div class="progress ew-password-strength-bar form-text mt-1 d-none" id="pst_<?= $Page->NewPassword->FieldVar ?>" role="progressbar">
            <div class="progress-bar"></div>
        </div>
    </div>
    <div class="row gx-0">
        <div class="input-group px-0">
            <input type="password" name="<?= $Page->ConfirmPassword->FieldVar ?>" id="<?= $Page->ConfirmPassword->FieldVar ?>" autocomplete="new-password" placeholder="<?= HtmlEncode($Language->phrase("ConfirmPassword", true)) ?>"<?= $Page->ConfirmPassword->editAttributes() ?>>
            <button type="button" class="btn btn-default ew-toggle-password rounded-end" data-ew-action="password"><i class="fa-solid fa-eye"></i></button>
        </div>
        <div class="invalid-feedback"><?= $Page->ConfirmPassword->getErrorMessage() ?></div>
    </div>
	<?php if (MS_TERMS_AND_CONDITION_CHECKBOX_ON_CHANGEPWD_PAGE == TRUE) { ?>
	<div class="form-group" id="r_ChkTerms">
		<div class="col-sm-12">
			<label>
				<span class="kt-switch">
					<?php $selwrk = (@isset($_POST["chkterms"])) ? " checked='checked'" : ""; ?>
					<div class="form-check form-switch d-inline-block" style="vertical-align: middle;">
					<input type="checkbox" class="form-check-input" name="chkterms" id="chkterms" value="<?php echo @$_POST["chkterms"]; ?>" <?php echo $selwrk; ?>>
					</div>
					<label class="col-form-label" for="chkterms">
					&nbsp;<?php echo $Language->phrase("IAgreeWith"); ?>&nbsp;<a href="javascript:void(0);" id="tac" onclick="getTermsConditions();return false;"><?php echo $Language->phrase("TermsConditionsTitle"); ?></a>&nbsp;|&nbsp;<a href="printtermsconditions" title="<?php echo $Language->phrase("Print"); ?>&nbsp;<?php echo $Language->phrase("TermsConditionsTitle"); ?>"><?php echo Language()->phrase("Print"); ?></a>
					</label>
				</span>
			</label>
		</div>
	</div>
	<?php } ?>
<div class="d-grid mb-3">
    <button class="btn btn-primary ew-btn disabled enable-on-init" name="btn-submit" id="btn-submit" type="submit" formaction="<?= CurrentPageUrl(false) ?>"><?= $Language->phrase("ChangePasswordBtn") ?></button>
</div>
</form>
</div>
</div>
</div>
<?php
$Page->showPageFooter();
?>
<script type="text/javascript">
loadjs.ready("load", function(){
  setTimeout(function (){
    $('#opwd').focus();
  }, 500);
<?php if (!$Page->IsModal) { ?>
  $("div.ew-change-pwd-box").css({"width":"380px"});
<?php } else { ?>
  $("div.ew-change-pwd-box").css({"width":"auto"});
<?php } ?>
<?php if (MS_TERMS_AND_CONDITION_CHECKBOX_ON_CHANGEPWD_PAGE == TRUE) { ?>
  <?php if (!CurrentPage()->IsModal) { ?>
  if ($('#chkterms').attr('checked')) {
	$('#btn-submit').removeAttr('disabled');
  } else {
	$('#btn-submit').attr('disabled', 'disabled');
  }
  $("#chkterms").click(function() {
    var checked_status = this.checked;
    if (checked_status == true) {
	  $('#btn-submit').removeAttr('disabled');
	} else {
	  $('#btn-submit').attr('disabled', 'disabled');
	}
  });
  <?php } else { ?>
  if ($('#ew-modal-dialog #chkterms').attr('checked')) {
	$('#ew-modal-dialog .ew-submit').removeAttr('disabled');
  } else {
	$('#ew-modal-dialog .ew-submit').attr('disabled', 'disabled');
  }
  $("#ew-modal-dialog #chkterms").click(function() {
    var checked_status = this.checked;
    if (checked_status == true) {
	  $('#ew-modal-dialog .ew-submit').removeAttr('disabled');
	} else {
	  $('#ew-modal-dialog .ew-submit').attr('disabled', 'disabled');
	}
  });
  <?php } ?>
<?php } ?>
});
</script>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-submit').on('click',function(){var $ = jQuery; if(fchange_password.validateFields()==true){ ew.prompt({html: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fchange_password").submit();});return false;});});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#fchange_password:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-submit").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-submit").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-submit").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your startup script here, no need to add script tags.
});
</script>
