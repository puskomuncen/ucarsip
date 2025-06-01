<?php

namespace PHPMaker2025\ucarsip;

// Page object
$DispositionsDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { dispositions: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var fdispositionsdelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fdispositionsdelete")
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
<form name="fdispositionsdelete" id="fdispositionsdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="dispositions">
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
<?php if ($Page->disposition_id->Visible) { // disposition_id ?>
        <th class="<?= $Page->disposition_id->headerCellClass() ?>"><span id="elh_dispositions_disposition_id" class="dispositions_disposition_id"><?= $Page->disposition_id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->letter_id->Visible) { // letter_id ?>
        <th class="<?= $Page->letter_id->headerCellClass() ?>"><span id="elh_dispositions_letter_id" class="dispositions_letter_id"><?= $Page->letter_id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->dari_unit_id->Visible) { // dari_unit_id ?>
        <th class="<?= $Page->dari_unit_id->headerCellClass() ?>"><span id="elh_dispositions_dari_unit_id" class="dispositions_dari_unit_id"><?= $Page->dari_unit_id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->ke_unit_id->Visible) { // ke_unit_id ?>
        <th class="<?= $Page->ke_unit_id->headerCellClass() ?>"><span id="elh_dispositions_ke_unit_id" class="dispositions_ke_unit_id"><?= $Page->ke_unit_id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->catatan->Visible) { // catatan ?>
        <th class="<?= $Page->catatan->headerCellClass() ?>"><span id="elh_dispositions_catatan" class="dispositions_catatan"><?= $Page->catatan->caption() ?></span></th>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
        <th class="<?= $Page->status->headerCellClass() ?>"><span id="elh_dispositions_status" class="dispositions_status"><?= $Page->status->caption() ?></span></th>
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
<?php if ($Page->disposition_id->Visible) { // disposition_id ?>
        <td<?= $Page->disposition_id->cellAttributes() ?>>
<span id="">
<span<?= $Page->disposition_id->viewAttributes() ?>>
<?= $Page->disposition_id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->letter_id->Visible) { // letter_id ?>
        <td<?= $Page->letter_id->cellAttributes() ?>>
<span id="">
<span<?= $Page->letter_id->viewAttributes() ?>>
<?= $Page->letter_id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->dari_unit_id->Visible) { // dari_unit_id ?>
        <td<?= $Page->dari_unit_id->cellAttributes() ?>>
<span id="">
<span<?= $Page->dari_unit_id->viewAttributes() ?>>
<?= $Page->dari_unit_id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->ke_unit_id->Visible) { // ke_unit_id ?>
        <td<?= $Page->ke_unit_id->cellAttributes() ?>>
<span id="">
<span<?= $Page->ke_unit_id->viewAttributes() ?>>
<?= $Page->ke_unit_id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->catatan->Visible) { // catatan ?>
        <td<?= $Page->catatan->cellAttributes() ?>>
<span id="">
<span<?= $Page->catatan->viewAttributes() ?>>
<?= $Page->catatan->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
        <td<?= $Page->status->cellAttributes() ?>>
<span id="">
<span<?= $Page->status->viewAttributes() ?>>
<?= $Page->status->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fdispositionsdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fdispositionsdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
