<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UserlevelpermissionsView = &$Page;
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
<form name="fuserlevelpermissionsview" id="fuserlevelpermissionsview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { userlevelpermissions: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var fuserlevelpermissionsview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fuserlevelpermissionsview")
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
<input type="hidden" name="t" value="userlevelpermissions">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->UserLevelID->Visible) { // UserLevelID ?>
    <tr id="r_UserLevelID"<?= $Page->UserLevelID->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_userlevelpermissions_UserLevelID"><?= $Page->UserLevelID->caption() ?></span></td>
        <td data-name="UserLevelID"<?= $Page->UserLevelID->cellAttributes() ?>>
<span id="el_userlevelpermissions_UserLevelID">
<span<?= $Page->UserLevelID->viewAttributes() ?>>
<?= $Page->UserLevelID->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->_TableName->Visible) { // TableName ?>
    <tr id="r__TableName"<?= $Page->_TableName->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_userlevelpermissions__TableName"><?= $Page->_TableName->caption() ?></span></td>
        <td data-name="_TableName"<?= $Page->_TableName->cellAttributes() ?>>
<span id="el_userlevelpermissions__TableName">
<span<?= $Page->_TableName->viewAttributes() ?>>
<?= $Page->_TableName->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->_Permission->Visible) { // Permission ?>
    <tr id="r__Permission"<?= $Page->_Permission->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_userlevelpermissions__Permission"><?= $Page->_Permission->caption() ?></span></td>
        <td data-name="_Permission"<?= $Page->_Permission->cellAttributes() ?>>
<span id="el_userlevelpermissions__Permission">
<span<?= $Page->_Permission->viewAttributes() ?>>
<?= $Page->_Permission->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fuserlevelpermissionsadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fuserlevelpermissionsadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fuserlevelpermissionsedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fuserlevelpermissionsedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
