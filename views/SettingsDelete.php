<?php

namespace PHPMaker2025\ucarsip;

// Page object
$SettingsDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { settings: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var fsettingsdelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fsettingsdelete")
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
<form name="fsettingsdelete" id="fsettingsdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="settings">
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
<?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <th class="<?= $Page->Option_ID->headerCellClass() ?>"><span id="elh_settings_Option_ID" class="settings_Option_ID"><?= $Page->Option_ID->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <th class="<?= $Page->Option_Default->headerCellClass() ?>"><span id="elh_settings_Option_Default" class="settings_Option_Default"><?= $Page->Option_Default->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <th class="<?= $Page->Show_Announcement->headerCellClass() ?>"><span id="elh_settings_Show_Announcement" class="settings_Show_Announcement"><?= $Page->Show_Announcement->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <th class="<?= $Page->Use_Announcement_Table->headerCellClass() ?>"><span id="elh_settings_Use_Announcement_Table" class="settings_Use_Announcement_Table"><?= $Page->Use_Announcement_Table->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <th class="<?= $Page->Maintenance_Mode->headerCellClass() ?>"><span id="elh_settings_Maintenance_Mode" class="settings_Maintenance_Mode"><?= $Page->Maintenance_Mode->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <th class="<?= $Page->Maintenance_Finish_DateTime->headerCellClass() ?>"><span id="elh_settings_Maintenance_Finish_DateTime" class="settings_Maintenance_Finish_DateTime"><?= $Page->Maintenance_Finish_DateTime->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <th class="<?= $Page->Auto_Normal_After_Maintenance->headerCellClass() ?>"><span id="elh_settings_Auto_Normal_After_Maintenance" class="settings_Auto_Normal_After_Maintenance"><?= $Page->Auto_Normal_After_Maintenance->caption() ?></span></th>
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
<?php if ($Page->Option_ID->Visible) { // Option_ID ?>
        <td<?= $Page->Option_ID->cellAttributes() ?>>
<span id="">
<span<?= $Page->Option_ID->viewAttributes() ?>>
<?= $Page->Option_ID->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Option_Default->Visible) { // Option_Default ?>
        <td<?= $Page->Option_Default->cellAttributes() ?>>
<span id="">
<span<?= $Page->Option_Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Option_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Option_Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Option_Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Option_Default_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
        <td<?= $Page->Show_Announcement->cellAttributes() ?>>
<span id="">
<span<?= $Page->Show_Announcement->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Show_Announcement_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Show_Announcement->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Show_Announcement->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Show_Announcement_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
        <td<?= $Page->Use_Announcement_Table->cellAttributes() ?>>
<span id="">
<span<?= $Page->Use_Announcement_Table->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Use_Announcement_Table_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Use_Announcement_Table->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Use_Announcement_Table->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Use_Announcement_Table_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
        <td<?= $Page->Maintenance_Mode->cellAttributes() ?>>
<span id="">
<span<?= $Page->Maintenance_Mode->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Maintenance_Mode_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Maintenance_Mode->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Maintenance_Mode->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Maintenance_Mode_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
        <td<?= $Page->Maintenance_Finish_DateTime->cellAttributes() ?>>
<span id="">
<span<?= $Page->Maintenance_Finish_DateTime->viewAttributes() ?>>
<?= $Page->Maintenance_Finish_DateTime->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
        <td<?= $Page->Auto_Normal_After_Maintenance->cellAttributes() ?>>
<span id="">
<span<?= $Page->Auto_Normal_After_Maintenance->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Auto_Normal_After_Maintenance->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Auto_Normal_After_Maintenance->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>"></label>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fsettingsdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fsettingsdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
