<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UserlevelsView = &$Page;
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
<form name="fuserlevelsview" id="fuserlevelsview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { userlevels: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var fuserlevelsview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fuserlevelsview")
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
<input type="hidden" name="t" value="userlevels">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->ID->Visible) { // ID ?>
    <tr id="r_ID"<?= $Page->ID->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_userlevels_ID"><?= $Page->ID->caption() ?></span></td>
        <td data-name="ID"<?= $Page->ID->cellAttributes() ?>>
<span id="el_userlevels_ID">
<span<?= $Page->ID->viewAttributes() ?>>
<?= $Page->ID->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Name->Visible) { // Name ?>
    <tr id="r_Name"<?= $Page->Name->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_userlevels_Name"><?= $Page->Name->caption() ?></span></td>
        <td data-name="Name"<?= $Page->Name->cellAttributes() ?>>
<span id="el_userlevels_Name">
<span<?= $Page->Name->viewAttributes() ?>>
<?= $Page->Name->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Hierarchy->Visible) { // Hierarchy ?>
    <tr id="r_Hierarchy"<?= $Page->Hierarchy->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_userlevels_Hierarchy"><?= $Page->Hierarchy->caption() ?></span></td>
        <td data-name="Hierarchy"<?= $Page->Hierarchy->cellAttributes() ?>>
<span id="el_userlevels_Hierarchy">
<span<?= $Page->Hierarchy->viewAttributes() ?>>
<?= $Page->Hierarchy->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Level_Origin->Visible) { // Level_Origin ?>
    <tr id="r_Level_Origin"<?= $Page->Level_Origin->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_userlevels_Level_Origin"><?= $Page->Level_Origin->caption() ?></span></td>
        <td data-name="Level_Origin"<?= $Page->Level_Origin->cellAttributes() ?>>
<span id="el_userlevels_Level_Origin">
<span<?= $Page->Level_Origin->viewAttributes() ?>>
<?= $Page->Level_Origin->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
</table>
<?php
    if (in_array("users", explode(",", $Page->getCurrentDetailTable())) && $users->DetailView) {
?>
<?php if ($Page->getCurrentDetailTable() != "") { ?>
<?php if (Container("users")->Count > 0) { // Begin of added by Masino Sinaga, September 16, 2023 ?>
<h4 class="ew-detail-caption"><?= $Language->tablePhrase("users", "TblCaption") ?>&nbsp;<?= sprintf($Language->phrase("DetailCount"), "navy", Container("users")->Count) ?></h4>
<?php } else { ?>
<h4 class="ew-detail-caption"><?= $Language->tablePhrase("users", "TblCaption") ?></h4>
<?php } // End of added by Masino Sinaga, September 16, 2023 ?>
<?php } ?>
<?php include_once "UsersGrid.php" ?>
<?php } ?>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fuserlevelsadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fuserlevelsadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fuserlevelsedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fuserlevelsedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
