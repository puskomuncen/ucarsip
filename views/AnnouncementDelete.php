<?php

namespace PHPMaker2025\ucarsip;

// Page object
$AnnouncementDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { announcement: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var fannouncementdelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fannouncementdelete")
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
<form name="fannouncementdelete" id="fannouncementdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="announcement">
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
<?php if ($Page->Announcement_ID->Visible) { // Announcement_ID ?>
        <th class="<?= $Page->Announcement_ID->headerCellClass() ?>"><span id="elh_announcement_Announcement_ID" class="announcement_Announcement_ID"><?= $Page->Announcement_ID->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Is_Active->Visible) { // Is_Active ?>
        <th class="<?= $Page->Is_Active->headerCellClass() ?>"><span id="elh_announcement_Is_Active" class="announcement_Is_Active"><?= $Page->Is_Active->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
        <th class="<?= $Page->Topic->headerCellClass() ?>"><span id="elh_announcement_Topic" class="announcement_Topic"><?= $Page->Topic->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Date_LastUpdate->Visible) { // Date_LastUpdate ?>
        <th class="<?= $Page->Date_LastUpdate->headerCellClass() ?>"><span id="elh_announcement_Date_LastUpdate" class="announcement_Date_LastUpdate"><?= $Page->Date_LastUpdate->caption() ?></span></th>
<?php } ?>
<?php if ($Page->_Language->Visible) { // Language ?>
        <th class="<?= $Page->_Language->headerCellClass() ?>"><span id="elh_announcement__Language" class="announcement__Language"><?= $Page->_Language->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Auto_Publish->Visible) { // Auto_Publish ?>
        <th class="<?= $Page->Auto_Publish->headerCellClass() ?>"><span id="elh_announcement_Auto_Publish" class="announcement_Auto_Publish"><?= $Page->Auto_Publish->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Date_Start->Visible) { // Date_Start ?>
        <th class="<?= $Page->Date_Start->headerCellClass() ?>"><span id="elh_announcement_Date_Start" class="announcement_Date_Start"><?= $Page->Date_Start->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Date_End->Visible) { // Date_End ?>
        <th class="<?= $Page->Date_End->headerCellClass() ?>"><span id="elh_announcement_Date_End" class="announcement_Date_End"><?= $Page->Date_End->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Date_Created->Visible) { // Date_Created ?>
        <th class="<?= $Page->Date_Created->headerCellClass() ?>"><span id="elh_announcement_Date_Created" class="announcement_Date_Created"><?= $Page->Date_Created->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Created_By->Visible) { // Created_By ?>
        <th class="<?= $Page->Created_By->headerCellClass() ?>"><span id="elh_announcement_Created_By" class="announcement_Created_By"><?= $Page->Created_By->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Translated_ID->Visible) { // Translated_ID ?>
        <th class="<?= $Page->Translated_ID->headerCellClass() ?>"><span id="elh_announcement_Translated_ID" class="announcement_Translated_ID"><?= $Page->Translated_ID->caption() ?></span></th>
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
<?php if ($Page->Announcement_ID->Visible) { // Announcement_ID ?>
        <td<?= $Page->Announcement_ID->cellAttributes() ?>>
<span id="">
<span<?= $Page->Announcement_ID->viewAttributes() ?>>
<?= $Page->Announcement_ID->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Is_Active->Visible) { // Is_Active ?>
        <td<?= $Page->Is_Active->cellAttributes() ?>>
<span id="">
<span<?= $Page->Is_Active->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Is_Active_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Is_Active->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Is_Active->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Is_Active_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
        <td<?= $Page->Topic->cellAttributes() ?>>
<span id="">
<span<?= $Page->Topic->viewAttributes() ?>>
<?= $Page->Topic->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Date_LastUpdate->Visible) { // Date_LastUpdate ?>
        <td<?= $Page->Date_LastUpdate->cellAttributes() ?>>
<span id="">
<span<?= $Page->Date_LastUpdate->viewAttributes() ?>>
<?= $Page->Date_LastUpdate->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->_Language->Visible) { // Language ?>
        <td<?= $Page->_Language->cellAttributes() ?>>
<span id="">
<span<?= $Page->_Language->viewAttributes() ?>>
<?= $Page->_Language->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Auto_Publish->Visible) { // Auto_Publish ?>
        <td<?= $Page->Auto_Publish->cellAttributes() ?>>
<span id="">
<span<?= $Page->Auto_Publish->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Auto_Publish_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Auto_Publish->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Auto_Publish->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Auto_Publish_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->Date_Start->Visible) { // Date_Start ?>
        <td<?= $Page->Date_Start->cellAttributes() ?>>
<span id="">
<span<?= $Page->Date_Start->viewAttributes() ?>>
<?= $Page->Date_Start->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Date_End->Visible) { // Date_End ?>
        <td<?= $Page->Date_End->cellAttributes() ?>>
<span id="">
<span<?= $Page->Date_End->viewAttributes() ?>>
<?= $Page->Date_End->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Date_Created->Visible) { // Date_Created ?>
        <td<?= $Page->Date_Created->cellAttributes() ?>>
<span id="">
<span<?= $Page->Date_Created->viewAttributes() ?>>
<?= $Page->Date_Created->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Created_By->Visible) { // Created_By ?>
        <td<?= $Page->Created_By->cellAttributes() ?>>
<span id="">
<span<?= $Page->Created_By->viewAttributes() ?>>
<?= $Page->Created_By->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Translated_ID->Visible) { // Translated_ID ?>
        <td<?= $Page->Translated_ID->cellAttributes() ?>>
<span id="">
<span<?= $Page->Translated_ID->viewAttributes() ?>>
<?= $Page->Translated_ID->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fannouncementdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fannouncementdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
