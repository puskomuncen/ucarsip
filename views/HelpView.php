<?php

namespace PHPMaker2025\ucarsip;

// Page object
$HelpView = &$Page;
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
<form name="fhelpview" id="fhelpview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { help: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var fhelpview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fhelpview")
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
<input type="hidden" name="t" value="help">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->Help_ID->Visible) { // Help_ID ?>
    <tr id="r_Help_ID"<?= $Page->Help_ID->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help_Help_ID"><?= $Page->Help_ID->caption() ?></span></td>
        <td data-name="Help_ID"<?= $Page->Help_ID->cellAttributes() ?>>
<span id="el_help_Help_ID">
<span<?= $Page->Help_ID->viewAttributes() ?>>
<?= $Page->Help_ID->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->_Language->Visible) { // Language ?>
    <tr id="r__Language"<?= $Page->_Language->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help__Language"><?= $Page->_Language->caption() ?></span></td>
        <td data-name="_Language"<?= $Page->_Language->cellAttributes() ?>>
<span id="el_help__Language">
<span<?= $Page->_Language->viewAttributes() ?>>
<?= $Page->_Language->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
    <tr id="r_Topic"<?= $Page->Topic->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help_Topic"><?= $Page->Topic->caption() ?></span></td>
        <td data-name="Topic"<?= $Page->Topic->cellAttributes() ?>>
<span id="el_help_Topic">
<span<?= $Page->Topic->viewAttributes() ?>>
<?= $Page->Topic->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Description->Visible) { // Description ?>
    <tr id="r_Description"<?= $Page->Description->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help_Description"><?= $Page->Description->caption() ?></span></td>
        <td data-name="Description"<?= $Page->Description->cellAttributes() ?>>
<span id="el_help_Description">
<span<?= $Page->Description->viewAttributes() ?>>
<?= $Page->Description->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Category->Visible) { // Category ?>
    <tr id="r_Category"<?= $Page->Category->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help_Category"><?= $Page->Category->caption() ?></span></td>
        <td data-name="Category"<?= $Page->Category->cellAttributes() ?>>
<span id="el_help_Category">
<span<?= $Page->Category->viewAttributes() ?>>
<?= $Page->Category->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Order->Visible) { // Order ?>
    <tr id="r_Order"<?= $Page->Order->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help_Order"><?= $Page->Order->caption() ?></span></td>
        <td data-name="Order"<?= $Page->Order->cellAttributes() ?>>
<span id="el_help_Order">
<span<?= $Page->Order->viewAttributes() ?>>
<?= $Page->Order->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Display_in_Page->Visible) { // Display_in_Page ?>
    <tr id="r_Display_in_Page"<?= $Page->Display_in_Page->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help_Display_in_Page"><?= $Page->Display_in_Page->caption() ?></span></td>
        <td data-name="Display_in_Page"<?= $Page->Display_in_Page->cellAttributes() ?>>
<span id="el_help_Display_in_Page">
<span<?= $Page->Display_in_Page->viewAttributes() ?>>
<?= $Page->Display_in_Page->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Updated_By->Visible) { // Updated_By ?>
    <tr id="r_Updated_By"<?= $Page->Updated_By->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help_Updated_By"><?= $Page->Updated_By->caption() ?></span></td>
        <td data-name="Updated_By"<?= $Page->Updated_By->cellAttributes() ?>>
<span id="el_help_Updated_By">
<span<?= $Page->Updated_By->viewAttributes() ?>>
<?= $Page->Updated_By->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Last_Updated->Visible) { // Last_Updated ?>
    <tr id="r_Last_Updated"<?= $Page->Last_Updated->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_help_Last_Updated"><?= $Page->Last_Updated->caption() ?></span></td>
        <td data-name="Last_Updated"<?= $Page->Last_Updated->cellAttributes() ?>>
<span id="el_help_Last_Updated">
<span<?= $Page->Last_Updated->viewAttributes() ?>>
<?= $Page->Last_Updated->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fhelpadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fhelpadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fhelpedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fhelpedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
