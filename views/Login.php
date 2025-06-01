<?php

namespace PHPMaker2025\ucarsip;

// Page object
$Login = &$Page;
?>
<script<?= Nonce() ?>>
loadjs.ready("head", function () {
    // Write your client script here, no need to add script tags.
});
</script>
<?php $Page->showPageHeader(); ?>
<div class="ew-login-box">
    <div class="login-logo"></div>
    <div class="card ew-login-card">
        <div class="card-body">
<?php
$Page->showMessage();
?>
<script<?= Nonce() ?>>
// Script inside .card-body
var flogin,
    $overlay,
    showLoading = () => $(".ew-login-card").append($overlay ??= $(ew.overlayTemplate())),
    hideLoading = () => $(".ew-login-card").find($overlay).detach();
loadjs.ready(["wrapper", "head"], function() {
    let $ = jQuery;
    ew.PAGE_ID ||= "login";
    window.currentPageID ||= "login";
    let form = new ew.FormBuilder()
        .setId("flogin")
        // Add fields
        .addFields([
            ["username", ew.Validators.required(ew.language.phrase("UserName")), <?= $Page->Username->IsInvalid ? "true" : "false" ?>],
            ["password", ew.Validators.required(ew.language.phrase("Password")), <?= $Page->Password->IsInvalid ? "true" : "false" ?>]
        ])

        // Captcha
        <?= Captcha()->getScript() ?>

        // Validate
        .setValidate(
            async function() {
                if (!this.validateRequired)
                    return true; // Ignore validation
                let fobj = this.getForm();

                // Validate fields
                if (!this.validateFields())
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
<?php
$formAction = Config("USE_TWO_FACTOR_AUTHENTICATION") || Config("USE_PHPCAPTCHA_FOR_LOGIN") ? UrlFor("login1fa") : UrlFor("login");
?>
<form name="flogin" id="flogin" class="ew-form ew-login-form" action="<?= $formAction ?>" method="post" novalidate autocomplete="off">
    <p class="login-box-msg"><?= $Language->phrase("LoginMsg") ?></p>
    <div class="row gx-0">
        <input type="text" name="username" id="username" autocomplete="username" value="<?= HtmlEncode($Page->Username->CurrentValue) ?>" placeholder="<?= HtmlEncode($Page->Username->PlaceHolder) ?>"<?= $Page->Username->editAttributes() ?>>
        <div class="invalid-feedback"><?= $Page->Username->getErrorMessage() ?></div>
    </div>
    <?php if (!Config("OTP_ONLY")) { // Disable password checking ?>
    <div class="row gx-0">
        <div class="input-group px-0">
            <input type="password" name="password" id="password" autocomplete="current-password" placeholder="<?= HtmlEncode($Page->Password->PlaceHolder) ?>"<?= $Page->Password->editAttributes() ?>>
            <button type="button" class="btn btn-default ew-toggle-password rounded-end" data-ew-action="password"><i class="fa-solid fa-eye"></i></button>
        </div>
        <div class="valid-feedback"><?= $Language->phrase("LoginSucceeded") ?></div>
        <div class="invalid-feedback"><?= $Page->Password->getErrorMessage() ?></div>
    </div>
    <?php } ?>
    <div class="row gx-0">
        <div class="form-check">
            <input type="checkbox" name="<?= $Page->RememberMe->FieldVar ?>" id="<?= $Page->RememberMe->FieldVar ?>" class="form-check-input" value="1"<?php if (IsRememberMe()) { ?> checked<?php } ?>>
            <label class="form-check-label" for="<?= $Page->RememberMe->FieldVar ?>"><?= $Language->phrase("RememberMe") ?></label>
        </div>
    </div>
    <div class="d-grid">
        <button class="btn btn-primary ew-btn disabled enable-on-init" name="btn-submit" id="btn-submit" type="submit" formaction="<?= $formAction ?>"><?= $Language->phrase("Login", true) ?></button>
    </div>
<?php
// Social login
$providers = array_filter(Config("AUTH_CONFIG.providers"), fn($provider) => $provider["enabled"]);
if (count($providers) > 0) {
?>
    <div class="social-auth-links text-center mt-3 d-grid gap-2">
        <p><?= $Language->phrase("LoginOr") ?></p>
        <?php foreach ($providers as $id => $provider) { ?>
            <a href="<?= CurrentPageUrl(false) ?>/<?= $id ?>" class="btn btn-outline-<?= strtolower($provider["color"]) ?>"><?= $Language->phrase("Login" . $id, null) ?></a>
        <?php } ?>
    </div>
<?php
}
?>
<div class="login-page-links text-center mt-3"></div>
<script type="text/html" class="ew-js-template"<?php if (!$Page->IsModal) { ?> data-name="login-page" data-seq="10"<?php } ?> data-data="login" data-target=".login-page-links">
{{if canResetPassword && resetPassword}}
<a class="card-link me-2"{{props resetPassword}} data-{{:key}}="{{>prop}}"{{/props}}>{{:resetPasswordText}}</a>
{{/if}}
{{if canRegister && register}}
<a class="card-link me-2"{{props register}} data-{{:key}}="{{>prop}}"{{/props}}>{{:registerText}}</a>
{{/if}}
</script>
</form>
        </div><!-- ./card-body -->
    </div><!-- ./card -->
</div><!-- ./ew-login-box -->
<?php
$Page->showPageFooter();
?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your startup script here, no need to add script tags.
});
</script>
