<?php namespace PHPMaker2025\ucarsip; ?>
<!-- email dialog -->
<div id="ew-email-dialog" class="modal" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
            </div>
            <div class="modal-body">
                <form id="ew-email-form" class="ew-form" action="<?= CurrentPageUrl(false) ?>"  novalidate autocomplete="off">
                    <?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
                    <input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
                    <input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
                    <?php } ?>
                    <input type="hidden" name="tblvar" id="tblvar" value="<?= isset($GLOBALS["Table"]) ? $GLOBALS["Table"]->TableVar : "" ?>">
                    <input type="hidden" name="export" id="export" value="email">
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label ew-label" for="sender"><?= $Language->phrase("EmailFormSender") ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control ew-form-control" name="sender" id="sender">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label ew-label" for="recipient"><?= $Language->phrase("EmailFormRecipient") ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control ew-form-control" name="recipient"
                                id="recipient">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label ew-label" for="cc"><?= $Language->phrase("EmailFormCc") ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control ew-form-control" name="cc" id="cc">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label ew-label" for="bcc"><?= $Language->phrase("EmailFormBcc") ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control ew-form-control" name="bcc" id="bcc">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label ew-label" for="subject"><?= $Language->phrase("EmailFormSubject") ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control ew-form-control" name="subject" id="subject">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label ew-label" for="message"><?= $Language->phrase("EmailFormMessage") ?></label>
                        <div class="col-sm-8">
                            <textarea class="form-control ew-form-control" rows="6" name="message" id="message"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary ew-btn"><?= $Language->phrase("SendEmailBtn") ?></button>
                <button type="button" class="btn btn-default ew-btn" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
            </div>
        </div>
    </div>
</div>
