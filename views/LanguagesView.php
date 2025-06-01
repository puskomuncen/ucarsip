<?php

namespace PHPMaker2025\ucarsip;

// Page object
$LanguagesView = &$Page;
?>
<?php if (!$Page->isExport()) { ?>
<div class="btn-toolbar ew-toolbar">
<?php $Page->ExportOptions->render("body") ?>
<?php $Page->OtherOptions->render("body") ?>
</div>
<?php } ?>
<?php $Page->showPageHeader(); ?>
<?php
$Page->showMessage();
?>
<main class="view">
<?php if (!$Page->IsModal) { ?>
<?php if (!$Page->isExport()) { ?>
<?= $Page->Pager->render() ?>
<?php } ?>
<?php } ?>
<?php // Begin of Card view by Masino Sinaga, September 10, 2023 ?>
<?php if (!$Page->IsModal) { ?>
<div class="col-md-12">
  <div class="card shadow-sm">
    <div class="card-header">
	  <h4 class="card-title"><?php echo Language()->phrase("ViewCaption"); ?></h4>
	  <div class="card-tools">
	  <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
	  </button>
	  </div>
	  <!-- /.card-tools -->
    </div>
    <!-- /.card-header -->
    <div class="card-body">
<?php } ?>
<?php // End of Card view by Masino Sinaga, September 10, 2023 ?>
<form name="flanguagesview" id="flanguagesview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { languages: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var flanguagesview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("flanguagesview")
        .setPageId("view")
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
<?php } ?>
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="languages">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->Language_Code->Visible) { // Language_Code ?>
    <tr id="r_Language_Code"<?= $Page->Language_Code->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Language_Code"><?= $Page->Language_Code->caption() ?></span></td>
        <td data-name="Language_Code"<?= $Page->Language_Code->cellAttributes() ?>>
<span id="el_languages_Language_Code">
<span<?= $Page->Language_Code->viewAttributes() ?>>
<?= $Page->Language_Code->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Language_Name->Visible) { // Language_Name ?>
    <tr id="r_Language_Name"<?= $Page->Language_Name->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Language_Name"><?= $Page->Language_Name->caption() ?></span></td>
        <td data-name="Language_Name"<?= $Page->Language_Name->cellAttributes() ?>>
<span id="el_languages_Language_Name">
<span<?= $Page->Language_Name->viewAttributes() ?>>
<?= $Page->Language_Name->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Default->Visible) { // Default ?>
    <tr id="r_Default"<?= $Page->Default->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Default"><?= $Page->Default->caption() ?></span></td>
        <td data-name="Default"<?= $Page->Default->cellAttributes() ?>>
<span id="el_languages_Default">
<span<?= $Page->Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Default_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Site_Logo->Visible) { // Site_Logo ?>
    <tr id="r_Site_Logo"<?= $Page->Site_Logo->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Site_Logo"><?= $Page->Site_Logo->caption() ?></span></td>
        <td data-name="Site_Logo"<?= $Page->Site_Logo->cellAttributes() ?>>
<span id="el_languages_Site_Logo">
<span<?= $Page->Site_Logo->viewAttributes() ?>>
<?= $Page->Site_Logo->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Site_Title->Visible) { // Site_Title ?>
    <tr id="r_Site_Title"<?= $Page->Site_Title->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Site_Title"><?= $Page->Site_Title->caption() ?></span></td>
        <td data-name="Site_Title"<?= $Page->Site_Title->cellAttributes() ?>>
<span id="el_languages_Site_Title">
<span<?= $Page->Site_Title->viewAttributes() ?>>
<?= $Page->Site_Title->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Default_Thousands_Separator->Visible) { // Default_Thousands_Separator ?>
    <tr id="r_Default_Thousands_Separator"<?= $Page->Default_Thousands_Separator->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Default_Thousands_Separator"><?= $Page->Default_Thousands_Separator->caption() ?></span></td>
        <td data-name="Default_Thousands_Separator"<?= $Page->Default_Thousands_Separator->cellAttributes() ?>>
<span id="el_languages_Default_Thousands_Separator">
<span<?= $Page->Default_Thousands_Separator->viewAttributes() ?>>
<?= $Page->Default_Thousands_Separator->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Default_Decimal_Point->Visible) { // Default_Decimal_Point ?>
    <tr id="r_Default_Decimal_Point"<?= $Page->Default_Decimal_Point->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Default_Decimal_Point"><?= $Page->Default_Decimal_Point->caption() ?></span></td>
        <td data-name="Default_Decimal_Point"<?= $Page->Default_Decimal_Point->cellAttributes() ?>>
<span id="el_languages_Default_Decimal_Point">
<span<?= $Page->Default_Decimal_Point->viewAttributes() ?>>
<?= $Page->Default_Decimal_Point->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Default_Currency_Symbol->Visible) { // Default_Currency_Symbol ?>
    <tr id="r_Default_Currency_Symbol"<?= $Page->Default_Currency_Symbol->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Default_Currency_Symbol"><?= $Page->Default_Currency_Symbol->caption() ?></span></td>
        <td data-name="Default_Currency_Symbol"<?= $Page->Default_Currency_Symbol->cellAttributes() ?>>
<span id="el_languages_Default_Currency_Symbol">
<span<?= $Page->Default_Currency_Symbol->viewAttributes() ?>>
<?= $Page->Default_Currency_Symbol->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Default_Money_Thousands_Separator->Visible) { // Default_Money_Thousands_Separator ?>
    <tr id="r_Default_Money_Thousands_Separator"<?= $Page->Default_Money_Thousands_Separator->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Default_Money_Thousands_Separator"><?= $Page->Default_Money_Thousands_Separator->caption() ?></span></td>
        <td data-name="Default_Money_Thousands_Separator"<?= $Page->Default_Money_Thousands_Separator->cellAttributes() ?>>
<span id="el_languages_Default_Money_Thousands_Separator">
<span<?= $Page->Default_Money_Thousands_Separator->viewAttributes() ?>>
<?= $Page->Default_Money_Thousands_Separator->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Default_Money_Decimal_Point->Visible) { // Default_Money_Decimal_Point ?>
    <tr id="r_Default_Money_Decimal_Point"<?= $Page->Default_Money_Decimal_Point->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Default_Money_Decimal_Point"><?= $Page->Default_Money_Decimal_Point->caption() ?></span></td>
        <td data-name="Default_Money_Decimal_Point"<?= $Page->Default_Money_Decimal_Point->cellAttributes() ?>>
<span id="el_languages_Default_Money_Decimal_Point">
<span<?= $Page->Default_Money_Decimal_Point->viewAttributes() ?>>
<?= $Page->Default_Money_Decimal_Point->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Terms_And_Condition_Text->Visible) { // Terms_And_Condition_Text ?>
    <tr id="r_Terms_And_Condition_Text"<?= $Page->Terms_And_Condition_Text->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Terms_And_Condition_Text"><?= $Page->Terms_And_Condition_Text->caption() ?></span></td>
        <td data-name="Terms_And_Condition_Text"<?= $Page->Terms_And_Condition_Text->cellAttributes() ?>>
<span id="el_languages_Terms_And_Condition_Text">
<span<?= $Page->Terms_And_Condition_Text->viewAttributes() ?>>
<?= $Page->Terms_And_Condition_Text->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Announcement_Text->Visible) { // Announcement_Text ?>
    <tr id="r_Announcement_Text"<?= $Page->Announcement_Text->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_Announcement_Text"><?= $Page->Announcement_Text->caption() ?></span></td>
        <td data-name="Announcement_Text"<?= $Page->Announcement_Text->cellAttributes() ?>>
<span id="el_languages_Announcement_Text">
<span<?= $Page->Announcement_Text->viewAttributes() ?>>
<?= $Page->Announcement_Text->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->About_Text->Visible) { // About_Text ?>
    <tr id="r_About_Text"<?= $Page->About_Text->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_languages_About_Text"><?= $Page->About_Text->caption() ?></span></td>
        <td data-name="About_Text"<?= $Page->About_Text->cellAttributes() ?>>
<span id="el_languages_About_Text">
<span<?= $Page->About_Text->viewAttributes() ?>>
<?= $Page->About_Text->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
</table>
</form>
<?php // Begin of Card view by Masino Sinaga, September 10, 2023 ?>
<?php if (!$Page->IsModal) { ?>
		</div>
     <!-- /.card-body -->
     </div>
  <!-- /.card -->
</div>
<?php } ?>
<?php // End of Card view by Masino Sinaga, September 10, 2023 ?>
<?php if (!$Page->IsModal) { ?>
<?php if (!$Page->isExport()) { ?>
<?= $Page->Pager->render() ?>
<?php } ?>
<?php } ?>
</main>
<?php
$Page->showPageFooter();
?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flanguagesadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#flanguagesadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flanguagesedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#flanguagesedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
