<?php

namespace PHPMaker2025\ucarsip;

// Page object
$DispositionsView = &$Page;
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
<form name="fdispositionsview" id="fdispositionsview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { dispositions: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var fdispositionsview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fdispositionsview")
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
<input type="hidden" name="t" value="dispositions">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->disposition_id->Visible) { // disposition_id ?>
    <tr id="r_disposition_id"<?= $Page->disposition_id->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_dispositions_disposition_id"><?= $Page->disposition_id->caption() ?></span></td>
        <td data-name="disposition_id"<?= $Page->disposition_id->cellAttributes() ?>>
<span id="el_dispositions_disposition_id">
<span<?= $Page->disposition_id->viewAttributes() ?>>
<?= $Page->disposition_id->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->letter_id->Visible) { // letter_id ?>
    <tr id="r_letter_id"<?= $Page->letter_id->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_dispositions_letter_id"><?= $Page->letter_id->caption() ?></span></td>
        <td data-name="letter_id"<?= $Page->letter_id->cellAttributes() ?>>
<span id="el_dispositions_letter_id">
<span<?= $Page->letter_id->viewAttributes() ?>>
<?= $Page->letter_id->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->dari_unit_id->Visible) { // dari_unit_id ?>
    <tr id="r_dari_unit_id"<?= $Page->dari_unit_id->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_dispositions_dari_unit_id"><?= $Page->dari_unit_id->caption() ?></span></td>
        <td data-name="dari_unit_id"<?= $Page->dari_unit_id->cellAttributes() ?>>
<span id="el_dispositions_dari_unit_id">
<span<?= $Page->dari_unit_id->viewAttributes() ?>>
<?= $Page->dari_unit_id->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->ke_unit_id->Visible) { // ke_unit_id ?>
    <tr id="r_ke_unit_id"<?= $Page->ke_unit_id->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_dispositions_ke_unit_id"><?= $Page->ke_unit_id->caption() ?></span></td>
        <td data-name="ke_unit_id"<?= $Page->ke_unit_id->cellAttributes() ?>>
<span id="el_dispositions_ke_unit_id">
<span<?= $Page->ke_unit_id->viewAttributes() ?>>
<?= $Page->ke_unit_id->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->catatan->Visible) { // catatan ?>
    <tr id="r_catatan"<?= $Page->catatan->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_dispositions_catatan"><?= $Page->catatan->caption() ?></span></td>
        <td data-name="catatan"<?= $Page->catatan->cellAttributes() ?>>
<span id="el_dispositions_catatan">
<span<?= $Page->catatan->viewAttributes() ?>>
<?= $Page->catatan->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
    <tr id="r_status"<?= $Page->status->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_dispositions_status"><?= $Page->status->caption() ?></span></td>
        <td data-name="status"<?= $Page->status->cellAttributes() ?>>
<span id="el_dispositions_status">
<span<?= $Page->status->viewAttributes() ?>>
<?= $Page->status->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->created_at->Visible) { // created_at ?>
    <tr id="r_created_at"<?= $Page->created_at->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_dispositions_created_at"><?= $Page->created_at->caption() ?></span></td>
        <td data-name="created_at"<?= $Page->created_at->cellAttributes() ?>>
<span id="el_dispositions_created_at">
<span<?= $Page->created_at->viewAttributes() ?>>
<?= $Page->created_at->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fdispositionsadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fdispositionsadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fdispositionsedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fdispositionsedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
