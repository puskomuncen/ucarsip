<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UserlevelpermissionsDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { userlevelpermissions: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var fuserlevelpermissionsdelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fuserlevelpermissionsdelete")
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
<form name="fuserlevelpermissionsdelete" id="fuserlevelpermissionsdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="userlevelpermissions">
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
<?php if ($Page->UserLevelID->Visible) { // UserLevelID ?>
        <th class="<?= $Page->UserLevelID->headerCellClass() ?>"><span id="elh_userlevelpermissions_UserLevelID" class="userlevelpermissions_UserLevelID"><?= $Page->UserLevelID->caption() ?></span></th>
<?php } ?>
<?php if ($Page->_TableName->Visible) { // TableName ?>
        <th class="<?= $Page->_TableName->headerCellClass() ?>"><span id="elh_userlevelpermissions__TableName" class="userlevelpermissions__TableName"><?= $Page->_TableName->caption() ?></span></th>
<?php } ?>
<?php if ($Page->_Permission->Visible) { // Permission ?>
        <th class="<?= $Page->_Permission->headerCellClass() ?>"><span id="elh_userlevelpermissions__Permission" class="userlevelpermissions__Permission"><?= $Page->_Permission->caption() ?></span></th>
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
<?php if ($Page->UserLevelID->Visible) { // UserLevelID ?>
        <td<?= $Page->UserLevelID->cellAttributes() ?>>
<span id="">
<span<?= $Page->UserLevelID->viewAttributes() ?>>
<?= $Page->UserLevelID->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->_TableName->Visible) { // TableName ?>
        <td<?= $Page->_TableName->cellAttributes() ?>>
<span id="">
<span<?= $Page->_TableName->viewAttributes() ?>>
<?= $Page->_TableName->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->_Permission->Visible) { // Permission ?>
        <td<?= $Page->_Permission->cellAttributes() ?>>
<span id="">
<span<?= $Page->_Permission->viewAttributes() ?>>
<?= $Page->_Permission->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fuserlevelpermissionsdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fuserlevelpermissionsdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
