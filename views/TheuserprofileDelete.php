<?php

namespace PHPMaker2025\ucarsip;

// Page object
$TheuserprofileDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { theuserprofile: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var ftheuserprofiledelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("ftheuserprofiledelete")
        .setPageId("delete")
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
<form name="ftheuserprofiledelete" id="ftheuserprofiledelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="theuserprofile">
<input type="hidden" name="action" id="action" value="delete">
<?php foreach ($Page->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode(Config("COMPOSITE_KEY_SEPARATOR"), $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?= HtmlEncode($keyvalue) ?>">
<?php } ?>
<div class="card ew-card ew-grid <?= $Page->TableGridClass ?>">
<div class="card-body ew-grid-middle-panel <?= $Page->TableContainerClass ?>" style="<?= $Page->TableContainerStyle ?>">
<table class="<?= $Page->TableClass ?>">
    <thead>
    <tr class="ew-table-header">
<?php if ($Page->_UserID->Visible) { // UserID ?>
        <th class="<?= $Page->_UserID->headerCellClass() ?>"><span id="elh_theuserprofile__UserID" class="theuserprofile__UserID"><?= $Page->_UserID->caption() ?></span></th>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
        <th class="<?= $Page->_Username->headerCellClass() ?>"><span id="elh_theuserprofile__Username" class="theuserprofile__Username"><?= $Page->_Username->caption() ?></span></th>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <th class="<?= $Page->UserLevel->headerCellClass() ?>"><span id="elh_theuserprofile_UserLevel" class="theuserprofile_UserLevel"><?= $Page->UserLevel->caption() ?></span></th>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <th class="<?= $Page->CompleteName->headerCellClass() ?>"><span id="elh_theuserprofile_CompleteName" class="theuserprofile_CompleteName"><?= $Page->CompleteName->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
        <th class="<?= $Page->Photo->headerCellClass() ?>"><span id="elh_theuserprofile_Photo" class="theuserprofile_Photo"><?= $Page->Photo->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
        <th class="<?= $Page->Gender->headerCellClass() ?>"><span id="elh_theuserprofile_Gender" class="theuserprofile_Gender"><?= $Page->Gender->caption() ?></span></th>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
        <th class="<?= $Page->_Email->headerCellClass() ?>"><span id="elh_theuserprofile__Email" class="theuserprofile__Email"><?= $Page->_Email->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
        <th class="<?= $Page->Activated->headerCellClass() ?>"><span id="elh_theuserprofile_Activated" class="theuserprofile_Activated"><?= $Page->Activated->caption() ?></span></th>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <th class="<?= $Page->ActiveStatus->headerCellClass() ?>"><span id="elh_theuserprofile_ActiveStatus" class="theuserprofile_ActiveStatus"><?= $Page->ActiveStatus->caption() ?></span></th>
<?php } ?>
    </tr>
    </thead>
    <tbody>
<?php
$Page->RecordCount = 0;
$i = 0;
while ($Page->fetch()) {
    $Page->RecordCount++;
    $Page->RowCount++;

    // Set row properties
    $Page->resetAttributes();
    $Page->RowType = RowType::VIEW; // View

    // Get the field contents
    $Page->loadRowValues($Page->CurrentRow);

    // Render row
    $Page->renderRow();
?>
    <tr <?= $Page->rowAttributes() ?>>
<?php if ($Page->_UserID->Visible) { // UserID ?>
        <td<?= $Page->_UserID->cellAttributes() ?>>
<span id="">
<span<?= $Page->_UserID->viewAttributes() ?>>
<?= $Page->_UserID->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
        <td<?= $Page->_Username->cellAttributes() ?>>
<span id="">
<span<?= $Page->_Username->viewAttributes() ?>>
<?= $Page->_Username->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
        <td<?= $Page->UserLevel->cellAttributes() ?>>
<span id="">
<span<?= $Page->UserLevel->viewAttributes() ?>>
<?= $Page->UserLevel->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
        <td<?= $Page->CompleteName->cellAttributes() ?>>
<span id="">
<span<?= $Page->CompleteName->viewAttributes() ?>>
<?= $Page->CompleteName->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
        <td<?= $Page->Photo->cellAttributes() ?>>
<span id="">
<span>
<?= GetFileViewTag($Page->Photo, $Page->Photo->getViewValue(), false) ?>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
        <td<?= $Page->Gender->cellAttributes() ?>>
<span id="">
<span<?= $Page->Gender->viewAttributes() ?>>
<?= $Page->Gender->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
        <td<?= $Page->_Email->cellAttributes() ?>>
<span id="">
<span<?= $Page->_Email->viewAttributes() ?>>
<?= $Page->_Email->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
        <td<?= $Page->Activated->cellAttributes() ?>>
<span id="">
<span<?= $Page->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
        <td<?= $Page->ActiveStatus->cellAttributes() ?>>
<span id="">
<span<?= $Page->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
<?php } ?>
    </tr>
<?php
}
$Page->Result?->free();
?>
</tbody>
</table>
</div>
</div>
<div class="ew-buttons ew-desktop-buttons">
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit"><?= $Language->phrase("DeleteBtn") ?></button>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
</div>
</form>
<?php
$Page->showPageFooter();
?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(ftheuserprofiledelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#ftheuserprofiledelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
