<?php

namespace PHPMaker2025\ucarsip;

// Page object
$AnnouncementView = &$Page;
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
<form name="fannouncementview" id="fannouncementview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { announcement: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var fannouncementview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fannouncementview")
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
<input type="hidden" name="t" value="announcement">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->Announcement_ID->Visible) { // Announcement_ID ?>
    <tr id="r_Announcement_ID"<?= $Page->Announcement_ID->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Announcement_ID"><?= $Page->Announcement_ID->caption() ?></span></td>
        <td data-name="Announcement_ID"<?= $Page->Announcement_ID->cellAttributes() ?>>
<span id="el_announcement_Announcement_ID">
<span<?= $Page->Announcement_ID->viewAttributes() ?>>
<?= $Page->Announcement_ID->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Is_Active->Visible) { // Is_Active ?>
    <tr id="r_Is_Active"<?= $Page->Is_Active->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Is_Active"><?= $Page->Is_Active->caption() ?></span></td>
        <td data-name="Is_Active"<?= $Page->Is_Active->cellAttributes() ?>>
<span id="el_announcement_Is_Active">
<span<?= $Page->Is_Active->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Is_Active_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Is_Active->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Is_Active->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Is_Active_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Topic->Visible) { // Topic ?>
    <tr id="r_Topic"<?= $Page->Topic->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Topic"><?= $Page->Topic->caption() ?></span></td>
        <td data-name="Topic"<?= $Page->Topic->cellAttributes() ?>>
<span id="el_announcement_Topic">
<span<?= $Page->Topic->viewAttributes() ?>>
<?= $Page->Topic->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Message->Visible) { // Message ?>
    <tr id="r_Message"<?= $Page->Message->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Message"><?= $Page->Message->caption() ?></span></td>
        <td data-name="Message"<?= $Page->Message->cellAttributes() ?>>
<span id="el_announcement_Message">
<span<?= $Page->Message->viewAttributes() ?>>
<?= $Page->Message->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Date_LastUpdate->Visible) { // Date_LastUpdate ?>
    <tr id="r_Date_LastUpdate"<?= $Page->Date_LastUpdate->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Date_LastUpdate"><?= $Page->Date_LastUpdate->caption() ?></span></td>
        <td data-name="Date_LastUpdate"<?= $Page->Date_LastUpdate->cellAttributes() ?>>
<span id="el_announcement_Date_LastUpdate">
<span<?= $Page->Date_LastUpdate->viewAttributes() ?>>
<?= $Page->Date_LastUpdate->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->_Language->Visible) { // Language ?>
    <tr id="r__Language"<?= $Page->_Language->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement__Language"><?= $Page->_Language->caption() ?></span></td>
        <td data-name="_Language"<?= $Page->_Language->cellAttributes() ?>>
<span id="el_announcement__Language">
<span<?= $Page->_Language->viewAttributes() ?>>
<?= $Page->_Language->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Auto_Publish->Visible) { // Auto_Publish ?>
    <tr id="r_Auto_Publish"<?= $Page->Auto_Publish->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Auto_Publish"><?= $Page->Auto_Publish->caption() ?></span></td>
        <td data-name="Auto_Publish"<?= $Page->Auto_Publish->cellAttributes() ?>>
<span id="el_announcement_Auto_Publish">
<span<?= $Page->Auto_Publish->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Auto_Publish_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Auto_Publish->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Auto_Publish->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Auto_Publish_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Date_Start->Visible) { // Date_Start ?>
    <tr id="r_Date_Start"<?= $Page->Date_Start->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Date_Start"><?= $Page->Date_Start->caption() ?></span></td>
        <td data-name="Date_Start"<?= $Page->Date_Start->cellAttributes() ?>>
<span id="el_announcement_Date_Start">
<span<?= $Page->Date_Start->viewAttributes() ?>>
<?= $Page->Date_Start->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Date_End->Visible) { // Date_End ?>
    <tr id="r_Date_End"<?= $Page->Date_End->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Date_End"><?= $Page->Date_End->caption() ?></span></td>
        <td data-name="Date_End"<?= $Page->Date_End->cellAttributes() ?>>
<span id="el_announcement_Date_End">
<span<?= $Page->Date_End->viewAttributes() ?>>
<?= $Page->Date_End->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Date_Created->Visible) { // Date_Created ?>
    <tr id="r_Date_Created"<?= $Page->Date_Created->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Date_Created"><?= $Page->Date_Created->caption() ?></span></td>
        <td data-name="Date_Created"<?= $Page->Date_Created->cellAttributes() ?>>
<span id="el_announcement_Date_Created">
<span<?= $Page->Date_Created->viewAttributes() ?>>
<?= $Page->Date_Created->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Created_By->Visible) { // Created_By ?>
    <tr id="r_Created_By"<?= $Page->Created_By->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Created_By"><?= $Page->Created_By->caption() ?></span></td>
        <td data-name="Created_By"<?= $Page->Created_By->cellAttributes() ?>>
<span id="el_announcement_Created_By">
<span<?= $Page->Created_By->viewAttributes() ?>>
<?= $Page->Created_By->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Translated_ID->Visible) { // Translated_ID ?>
    <tr id="r_Translated_ID"<?= $Page->Translated_ID->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_announcement_Translated_ID"><?= $Page->Translated_ID->caption() ?></span></td>
        <td data-name="Translated_ID"<?= $Page->Translated_ID->cellAttributes() ?>>
<span id="el_announcement_Translated_ID">
<span<?= $Page->Translated_ID->viewAttributes() ?>>
<?= $Page->Translated_ID->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fannouncementadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fannouncementadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fannouncementedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fannouncementedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
