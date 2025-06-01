<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UnitsDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { units: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var funitsdelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("funitsdelete")
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
<form name="funitsdelete" id="funitsdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="units">
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
<?php if ($Page->unit_id->Visible) { // unit_id ?>
        <th class="<?= $Page->unit_id->headerCellClass() ?>"><span id="elh_units_unit_id" class="units_unit_id"><?= $Page->unit_id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->nama_unit->Visible) { // nama_unit ?>
        <th class="<?= $Page->nama_unit->headerCellClass() ?>"><span id="elh_units_nama_unit" class="units_nama_unit"><?= $Page->nama_unit->caption() ?></span></th>
<?php } ?>
<?php if ($Page->kode_unit->Visible) { // kode_unit ?>
        <th class="<?= $Page->kode_unit->headerCellClass() ?>"><span id="elh_units_kode_unit" class="units_kode_unit"><?= $Page->kode_unit->caption() ?></span></th>
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
<?php if ($Page->unit_id->Visible) { // unit_id ?>
        <td<?= $Page->unit_id->cellAttributes() ?>>
<span id="">
<span<?= $Page->unit_id->viewAttributes() ?>>
<?= $Page->unit_id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->nama_unit->Visible) { // nama_unit ?>
        <td<?= $Page->nama_unit->cellAttributes() ?>>
<span id="">
<span<?= $Page->nama_unit->viewAttributes() ?>>
<?= $Page->nama_unit->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->kode_unit->Visible) { // kode_unit ?>
        <td<?= $Page->kode_unit->cellAttributes() ?>>
<span id="">
<span<?= $Page->kode_unit->viewAttributes() ?>>
<?= $Page->kode_unit->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(funitsdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#funitsdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
