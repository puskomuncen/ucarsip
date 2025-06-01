<?php

namespace PHPMaker2025\ucarsip;

// Page object
$SettingsView = &$Page;
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
<form name="fsettingsview" id="fsettingsview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { settings: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var fsettingsview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fsettingsview")
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
<input type="hidden" name="t" value="settings">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->Option_ID->Visible) { // Option_ID ?>
    <tr id="r_Option_ID"<?= $Page->Option_ID->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_settings_Option_ID"><?= $Page->Option_ID->caption() ?></span></td>
        <td data-name="Option_ID"<?= $Page->Option_ID->cellAttributes() ?>>
<span id="el_settings_Option_ID">
<span<?= $Page->Option_ID->viewAttributes() ?>>
<?= $Page->Option_ID->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Option_Default->Visible) { // Option_Default ?>
    <tr id="r_Option_Default"<?= $Page->Option_Default->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_settings_Option_Default"><?= $Page->Option_Default->caption() ?></span></td>
        <td data-name="Option_Default"<?= $Page->Option_Default->cellAttributes() ?>>
<span id="el_settings_Option_Default">
<span<?= $Page->Option_Default->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Option_Default_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Option_Default->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Option_Default->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Option_Default_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Show_Announcement->Visible) { // Show_Announcement ?>
    <tr id="r_Show_Announcement"<?= $Page->Show_Announcement->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_settings_Show_Announcement"><?= $Page->Show_Announcement->caption() ?></span></td>
        <td data-name="Show_Announcement"<?= $Page->Show_Announcement->cellAttributes() ?>>
<span id="el_settings_Show_Announcement">
<span<?= $Page->Show_Announcement->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Show_Announcement_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Show_Announcement->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Show_Announcement->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Show_Announcement_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Use_Announcement_Table->Visible) { // Use_Announcement_Table ?>
    <tr id="r_Use_Announcement_Table"<?= $Page->Use_Announcement_Table->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_settings_Use_Announcement_Table"><?= $Page->Use_Announcement_Table->caption() ?></span></td>
        <td data-name="Use_Announcement_Table"<?= $Page->Use_Announcement_Table->cellAttributes() ?>>
<span id="el_settings_Use_Announcement_Table">
<span<?= $Page->Use_Announcement_Table->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Use_Announcement_Table_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Use_Announcement_Table->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Use_Announcement_Table->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Use_Announcement_Table_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Maintenance_Mode->Visible) { // Maintenance_Mode ?>
    <tr id="r_Maintenance_Mode"<?= $Page->Maintenance_Mode->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_settings_Maintenance_Mode"><?= $Page->Maintenance_Mode->caption() ?></span></td>
        <td data-name="Maintenance_Mode"<?= $Page->Maintenance_Mode->cellAttributes() ?>>
<span id="el_settings_Maintenance_Mode">
<span<?= $Page->Maintenance_Mode->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Maintenance_Mode_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Maintenance_Mode->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Maintenance_Mode->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Maintenance_Mode_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Maintenance_Finish_DateTime->Visible) { // Maintenance_Finish_DateTime ?>
    <tr id="r_Maintenance_Finish_DateTime"<?= $Page->Maintenance_Finish_DateTime->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_settings_Maintenance_Finish_DateTime"><?= $Page->Maintenance_Finish_DateTime->caption() ?></span></td>
        <td data-name="Maintenance_Finish_DateTime"<?= $Page->Maintenance_Finish_DateTime->cellAttributes() ?>>
<span id="el_settings_Maintenance_Finish_DateTime">
<span<?= $Page->Maintenance_Finish_DateTime->viewAttributes() ?>>
<?= $Page->Maintenance_Finish_DateTime->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Auto_Normal_After_Maintenance->Visible) { // Auto_Normal_After_Maintenance ?>
    <tr id="r_Auto_Normal_After_Maintenance"<?= $Page->Auto_Normal_After_Maintenance->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_settings_Auto_Normal_After_Maintenance"><?= $Page->Auto_Normal_After_Maintenance->caption() ?></span></td>
        <td data-name="Auto_Normal_After_Maintenance"<?= $Page->Auto_Normal_After_Maintenance->cellAttributes() ?>>
<span id="el_settings_Auto_Normal_After_Maintenance">
<span<?= $Page->Auto_Normal_After_Maintenance->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Auto_Normal_After_Maintenance->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Auto_Normal_After_Maintenance->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Auto_Normal_After_Maintenance_<?= $Page->RowCount ?>"></label>
</div>
</span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fsettingsadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fsettingsadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fsettingsedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fsettingsedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
