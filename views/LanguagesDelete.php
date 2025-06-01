<?php

namespace PHPMaker2025\ucarsip;

// Page object
$LanguagesDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { languages: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var flanguagesdelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("flanguagesdelete")
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
<form name="flanguagesdelete" id="flanguagesdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="languages">
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
<?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <th class="<?= $Page->Language_Code->headerCellClass() ?>"><span id="elh_languages_Language_Code" class="languages_Language_Code"><?= $Page->Language_Code->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <th class="<?= $Page->Language_Name->headerCellClass() ?>"><span id="elh_languages_Language_Name" class="languages_Language_Name"><?= $Page->Language_Name->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Default->Visible) { // Default ?>
        <th class="<?= $Page->Default->headerCellClass() ?>"><span id="elh_languages_Default" class="languages_Default"><?= $Page->Default->caption() ?></span></th>
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
<?php if ($Page->Language_Code->Visible) { // Language_Code ?>
        <td<?= $Page->Language_Code->cellAttributes() ?>>
<span id="">
<span<?= $Page->Language_Code->viewAttributes() ?>>
<?= $Page->Language_Code->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Language_Name->Visible) { // Language_Name ?>
        <td<?= $Page->Language_Name->cellAttributes() ?>>
<span id="">
<span<?= $Page->Language_Name->viewAttributes() ?>>
<?= $Page->Language_Name->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->Default->Visible) { // Default ?>
        <td<?= $Page->Default->cellAttributes() ?>>
<span id="">
<span<?= $Page->Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Default_<?= $Page->RowCount ?>"></label>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flanguagesdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#flanguagesdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
