<?php

namespace PHPMaker2025\ucarsip;

// Page object
$HelpCategoriesDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { help_categories: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var fhelp_categoriesdelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fhelp_categoriesdelete")
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
<form name="fhelp_categoriesdelete" id="fhelp_categoriesdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="help_categories">
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
<?php if ($Page->Category_ID->Visible) { // Category_ID ?>
        <th class="<?= $Page->Category_ID->headerCellClass() ?>"><span id="elh_help_categories_Category_ID" class="help_categories_Category_ID"><?= $Page->Category_ID->caption() ?></span></th>
<?php } ?>
<?php if ($Page->_Language->Visible) { // Language ?>
        <th class="<?= $Page->_Language->headerCellClass() ?>"><span id="elh_help_categories__Language" class="help_categories__Language"><?= $Page->_Language->caption() ?></span></th>
<?php } ?>
<?php if ($Page->Category_Description->Visible) { // Category_Description ?>
        <th class="<?= $Page->Category_Description->headerCellClass() ?>"><span id="elh_help_categories_Category_Description" class="help_categories_Category_Description"><?= $Page->Category_Description->caption() ?></span></th>
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
<?php if ($Page->Category_ID->Visible) { // Category_ID ?>
        <td<?= $Page->Category_ID->cellAttributes() ?>>
<span id="">
<span<?= $Page->Category_ID->viewAttributes() ?>>
<?= $Page->Category_ID->getViewValue() ?></span>
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
<?php if ($Page->Category_Description->Visible) { // Category_Description ?>
        <td<?= $Page->Category_Description->cellAttributes() ?>>
<span id="">
<span<?= $Page->Category_Description->viewAttributes() ?>>
<?= $Page->Category_Description->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fhelp_categoriesdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#fhelp_categoriesdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
