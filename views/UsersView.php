<?php

namespace PHPMaker2025\ucarsip;

// Page object
$UsersView = &$Page;
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
<form name="fusersview" id="fusersview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { users: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var fusersview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fusersview")
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
<input type="hidden" name="t" value="users">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->_UserID->Visible) { // UserID ?>
    <tr id="r__UserID"<?= $Page->_UserID->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users__UserID"><?= $Page->_UserID->caption() ?></span></td>
        <td data-name="_UserID"<?= $Page->_UserID->cellAttributes() ?>>
<span id="el_users__UserID">
<span<?= $Page->_UserID->viewAttributes() ?>>
<?= $Page->_UserID->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->_Username->Visible) { // Username ?>
    <tr id="r__Username"<?= $Page->_Username->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users__Username"><?= $Page->_Username->caption() ?></span></td>
        <td data-name="_Username"<?= $Page->_Username->cellAttributes() ?>>
<span id="el_users__Username">
<span<?= $Page->_Username->viewAttributes() ?>>
<?= $Page->_Username->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->UserLevel->Visible) { // UserLevel ?>
    <tr id="r_UserLevel"<?= $Page->UserLevel->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_UserLevel"><?= $Page->UserLevel->caption() ?></span></td>
        <td data-name="UserLevel"<?= $Page->UserLevel->cellAttributes() ?>>
<span id="el_users_UserLevel">
<span<?= $Page->UserLevel->viewAttributes() ?>>
<?= $Page->UserLevel->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->FirstName->Visible) { // FirstName ?>
    <tr id="r_FirstName"<?= $Page->FirstName->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_FirstName"><?= $Page->FirstName->caption() ?></span></td>
        <td data-name="FirstName"<?= $Page->FirstName->cellAttributes() ?>>
<span id="el_users_FirstName">
<span<?= $Page->FirstName->viewAttributes() ?>>
<?= $Page->FirstName->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->LastName->Visible) { // LastName ?>
    <tr id="r_LastName"<?= $Page->LastName->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_LastName"><?= $Page->LastName->caption() ?></span></td>
        <td data-name="LastName"<?= $Page->LastName->cellAttributes() ?>>
<span id="el_users_LastName">
<span<?= $Page->LastName->viewAttributes() ?>>
<?= $Page->LastName->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->CompleteName->Visible) { // CompleteName ?>
    <tr id="r_CompleteName"<?= $Page->CompleteName->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_CompleteName"><?= $Page->CompleteName->caption() ?></span></td>
        <td data-name="CompleteName"<?= $Page->CompleteName->cellAttributes() ?>>
<span id="el_users_CompleteName">
<span<?= $Page->CompleteName->viewAttributes() ?>>
<?= $Page->CompleteName->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->BirthDate->Visible) { // BirthDate ?>
    <tr id="r_BirthDate"<?= $Page->BirthDate->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_BirthDate"><?= $Page->BirthDate->caption() ?></span></td>
        <td data-name="BirthDate"<?= $Page->BirthDate->cellAttributes() ?>>
<span id="el_users_BirthDate">
<span<?= $Page->BirthDate->viewAttributes() ?>>
<?= $Page->BirthDate->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->HomePhone->Visible) { // HomePhone ?>
    <tr id="r_HomePhone"<?= $Page->HomePhone->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_HomePhone"><?= $Page->HomePhone->caption() ?></span></td>
        <td data-name="HomePhone"<?= $Page->HomePhone->cellAttributes() ?>>
<span id="el_users_HomePhone">
<span<?= $Page->HomePhone->viewAttributes() ?>>
<?= $Page->HomePhone->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Photo->Visible) { // Photo ?>
    <tr id="r_Photo"<?= $Page->Photo->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_Photo"><?= $Page->Photo->caption() ?></span></td>
        <td data-name="Photo"<?= $Page->Photo->cellAttributes() ?>>
<span id="el_users_Photo">
<span>
<?= GetFileViewTag($Page->Photo, $Page->Photo->getViewValue(), false) ?>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Notes->Visible) { // Notes ?>
    <tr id="r_Notes"<?= $Page->Notes->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_Notes"><?= $Page->Notes->caption() ?></span></td>
        <td data-name="Notes"<?= $Page->Notes->cellAttributes() ?>>
<span id="el_users_Notes">
<span<?= $Page->Notes->viewAttributes() ?>>
<?= $Page->Notes->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->ReportsTo->Visible) { // ReportsTo ?>
    <tr id="r_ReportsTo"<?= $Page->ReportsTo->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_ReportsTo"><?= $Page->ReportsTo->caption() ?></span></td>
        <td data-name="ReportsTo"<?= $Page->ReportsTo->cellAttributes() ?>>
<span id="el_users_ReportsTo">
<span<?= $Page->ReportsTo->viewAttributes() ?>>
<?= $Page->ReportsTo->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Gender->Visible) { // Gender ?>
    <tr id="r_Gender"<?= $Page->Gender->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_Gender"><?= $Page->Gender->caption() ?></span></td>
        <td data-name="Gender"<?= $Page->Gender->cellAttributes() ?>>
<span id="el_users_Gender">
<span<?= $Page->Gender->viewAttributes() ?>>
<?= $Page->Gender->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->_Email->Visible) { // Email ?>
    <tr id="r__Email"<?= $Page->_Email->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users__Email"><?= $Page->_Email->caption() ?></span></td>
        <td data-name="_Email"<?= $Page->_Email->cellAttributes() ?>>
<span id="el_users__Email">
<span<?= $Page->_Email->viewAttributes() ?>>
<?= $Page->_Email->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Activated->Visible) { // Activated ?>
    <tr id="r_Activated"<?= $Page->Activated->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_Activated"><?= $Page->Activated->caption() ?></span></td>
        <td data-name="Activated"<?= $Page->Activated->cellAttributes() ?>>
<span id="el_users_Activated">
<span<?= $Page->Activated->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_Activated_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->Activated->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->Activated->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_Activated_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->_Profile->Visible) { // Profile ?>
    <tr id="r__Profile"<?= $Page->_Profile->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users__Profile"><?= $Page->_Profile->caption() ?></span></td>
        <td data-name="_Profile"<?= $Page->_Profile->cellAttributes() ?>>
<span id="el_users__Profile">
<span<?= $Page->_Profile->viewAttributes() ?>>
<?= $Page->_Profile->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->Avatar->Visible) { // Avatar ?>
    <tr id="r_Avatar"<?= $Page->Avatar->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_Avatar"><?= $Page->Avatar->caption() ?></span></td>
        <td data-name="Avatar"<?= $Page->Avatar->cellAttributes() ?>>
<span id="el_users_Avatar">
<span>
<?= GetFileViewTag($Page->Avatar, $Page->Avatar->getViewValue(), false) ?>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->ActiveStatus->Visible) { // ActiveStatus ?>
    <tr id="r_ActiveStatus"<?= $Page->ActiveStatus->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_ActiveStatus"><?= $Page->ActiveStatus->caption() ?></span></td>
        <td data-name="ActiveStatus"<?= $Page->ActiveStatus->cellAttributes() ?>>
<span id="el_users_ActiveStatus">
<span<?= $Page->ActiveStatus->viewAttributes() ?>>
<div class="form-check form-switch d-inline-block">
    <input type="checkbox" id="x_ActiveStatus_<?= $Page->RowCount ?>" class="form-check-input" value="<?= $Page->ActiveStatus->getViewValue() ?>" disabled<?php if (ConvertToBool($Page->ActiveStatus->CurrentValue)) { ?> checked<?php } ?>>
    <label class="form-check-label" for="x_ActiveStatus_<?= $Page->RowCount ?>"></label>
</div>
</span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->MessengerColor->Visible) { // MessengerColor ?>
    <tr id="r_MessengerColor"<?= $Page->MessengerColor->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_MessengerColor"><?= $Page->MessengerColor->caption() ?></span></td>
        <td data-name="MessengerColor"<?= $Page->MessengerColor->cellAttributes() ?>>
<span id="el_users_MessengerColor">
<span<?= $Page->MessengerColor->viewAttributes() ?>>
<?= $Page->MessengerColor->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->CreatedAt->Visible) { // CreatedAt ?>
    <tr id="r_CreatedAt"<?= $Page->CreatedAt->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_CreatedAt"><?= $Page->CreatedAt->caption() ?></span></td>
        <td data-name="CreatedAt"<?= $Page->CreatedAt->cellAttributes() ?>>
<span id="el_users_CreatedAt">
<span<?= $Page->CreatedAt->viewAttributes() ?>>
<?= $Page->CreatedAt->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->CreatedBy->Visible) { // CreatedBy ?>
    <tr id="r_CreatedBy"<?= $Page->CreatedBy->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_CreatedBy"><?= $Page->CreatedBy->caption() ?></span></td>
        <td data-name="CreatedBy"<?= $Page->CreatedBy->cellAttributes() ?>>
<span id="el_users_CreatedBy">
<span<?= $Page->CreatedBy->viewAttributes() ?>>
<?= $Page->CreatedBy->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->UpdatedAt->Visible) { // UpdatedAt ?>
    <tr id="r_UpdatedAt"<?= $Page->UpdatedAt->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_UpdatedAt"><?= $Page->UpdatedAt->caption() ?></span></td>
        <td data-name="UpdatedAt"<?= $Page->UpdatedAt->cellAttributes() ?>>
<span id="el_users_UpdatedAt">
<span<?= $Page->UpdatedAt->viewAttributes() ?>>
<?= $Page->UpdatedAt->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->UpdatedBy->Visible) { // UpdatedBy ?>
    <tr id="r_UpdatedBy"<?= $Page->UpdatedBy->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_users_UpdatedBy"><?= $Page->UpdatedBy->caption() ?></span></td>
        <td data-name="UpdatedBy"<?= $Page->UpdatedBy->cellAttributes() ?>>
<span id="el_users_UpdatedBy">
<span<?= $Page->UpdatedBy->viewAttributes() ?>>
<?= $Page->UpdatedBy->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fusersadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fusersadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fusersedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fusersedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
