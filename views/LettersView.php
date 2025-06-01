<?php

namespace PHPMaker2025\ucarsip;

// Page object
$LettersView = &$Page;
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
<form name="flettersview" id="flettersview" class="ew-form ew-view-form overlay-wrapper" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { letters: currentTable } });
var currentPageID = ew.PAGE_ID = "view";
var currentForm;
var flettersview;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("flettersview")
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
<input type="hidden" name="t" value="letters">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<table class="<?= $Page->TableClass ?>">
<?php if ($Page->letter_id->Visible) { // letter_id ?>
    <tr id="r_letter_id"<?= $Page->letter_id->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_letter_id"><?= $Page->letter_id->caption() ?></span></td>
        <td data-name="letter_id"<?= $Page->letter_id->cellAttributes() ?>>
<span id="el_letters_letter_id">
<span<?= $Page->letter_id->viewAttributes() ?>>
<?= $Page->letter_id->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->nomor_surat->Visible) { // nomor_surat ?>
    <tr id="r_nomor_surat"<?= $Page->nomor_surat->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_nomor_surat"><?= $Page->nomor_surat->caption() ?></span></td>
        <td data-name="nomor_surat"<?= $Page->nomor_surat->cellAttributes() ?>>
<span id="el_letters_nomor_surat">
<span<?= $Page->nomor_surat->viewAttributes() ?>>
<?= $Page->nomor_surat->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->perihal->Visible) { // perihal ?>
    <tr id="r_perihal"<?= $Page->perihal->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_perihal"><?= $Page->perihal->caption() ?></span></td>
        <td data-name="perihal"<?= $Page->perihal->cellAttributes() ?>>
<span id="el_letters_perihal">
<span<?= $Page->perihal->viewAttributes() ?>>
<?= $Page->perihal->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->tanggal_surat->Visible) { // tanggal_surat ?>
    <tr id="r_tanggal_surat"<?= $Page->tanggal_surat->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_tanggal_surat"><?= $Page->tanggal_surat->caption() ?></span></td>
        <td data-name="tanggal_surat"<?= $Page->tanggal_surat->cellAttributes() ?>>
<span id="el_letters_tanggal_surat">
<span<?= $Page->tanggal_surat->viewAttributes() ?>>
<?= $Page->tanggal_surat->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->tanggal_terima->Visible) { // tanggal_terima ?>
    <tr id="r_tanggal_terima"<?= $Page->tanggal_terima->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_tanggal_terima"><?= $Page->tanggal_terima->caption() ?></span></td>
        <td data-name="tanggal_terima"<?= $Page->tanggal_terima->cellAttributes() ?>>
<span id="el_letters_tanggal_terima">
<span<?= $Page->tanggal_terima->viewAttributes() ?>>
<?= $Page->tanggal_terima->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->jenis->Visible) { // jenis ?>
    <tr id="r_jenis"<?= $Page->jenis->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_jenis"><?= $Page->jenis->caption() ?></span></td>
        <td data-name="jenis"<?= $Page->jenis->cellAttributes() ?>>
<span id="el_letters_jenis">
<span<?= $Page->jenis->viewAttributes() ?>>
<?= $Page->jenis->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->klasifikasi->Visible) { // klasifikasi ?>
    <tr id="r_klasifikasi"<?= $Page->klasifikasi->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_klasifikasi"><?= $Page->klasifikasi->caption() ?></span></td>
        <td data-name="klasifikasi"<?= $Page->klasifikasi->cellAttributes() ?>>
<span id="el_letters_klasifikasi">
<span<?= $Page->klasifikasi->viewAttributes() ?>>
<?= $Page->klasifikasi->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->pengirim->Visible) { // pengirim ?>
    <tr id="r_pengirim"<?= $Page->pengirim->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_pengirim"><?= $Page->pengirim->caption() ?></span></td>
        <td data-name="pengirim"<?= $Page->pengirim->cellAttributes() ?>>
<span id="el_letters_pengirim">
<span<?= $Page->pengirim->viewAttributes() ?>>
<?= $Page->pengirim->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->penerima_unit_id->Visible) { // penerima_unit_id ?>
    <tr id="r_penerima_unit_id"<?= $Page->penerima_unit_id->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_penerima_unit_id"><?= $Page->penerima_unit_id->caption() ?></span></td>
        <td data-name="penerima_unit_id"<?= $Page->penerima_unit_id->cellAttributes() ?>>
<span id="el_letters_penerima_unit_id">
<span<?= $Page->penerima_unit_id->viewAttributes() ?>>
<?= $Page->penerima_unit_id->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->file_url->Visible) { // file_url ?>
    <tr id="r_file_url"<?= $Page->file_url->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_file_url"><?= $Page->file_url->caption() ?></span></td>
        <td data-name="file_url"<?= $Page->file_url->cellAttributes() ?>>
<span id="el_letters_file_url">
<span<?= $Page->file_url->viewAttributes() ?>>
<?= $Page->file_url->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
    <tr id="r_status"<?= $Page->status->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_status"><?= $Page->status->caption() ?></span></td>
        <td data-name="status"<?= $Page->status->cellAttributes() ?>>
<span id="el_letters_status">
<span<?= $Page->status->viewAttributes() ?>>
<?= $Page->status->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->created_by->Visible) { // created_by ?>
    <tr id="r_created_by"<?= $Page->created_by->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_created_by"><?= $Page->created_by->caption() ?></span></td>
        <td data-name="created_by"<?= $Page->created_by->cellAttributes() ?>>
<span id="el_letters_created_by">
<span<?= $Page->created_by->viewAttributes() ?>>
<?= $Page->created_by->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->created_at->Visible) { // created_at ?>
    <tr id="r_created_at"<?= $Page->created_at->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_created_at"><?= $Page->created_at->caption() ?></span></td>
        <td data-name="created_at"<?= $Page->created_at->cellAttributes() ?>>
<span id="el_letters_created_at">
<span<?= $Page->created_at->viewAttributes() ?>>
<?= $Page->created_at->getViewValue() ?></span>
</span>
</td>
    </tr>
<?php } ?>
<?php if ($Page->updated_at->Visible) { // updated_at ?>
    <tr id="r_updated_at"<?= $Page->updated_at->rowAttributes() ?>>
        <td class="<?= $Page->TableLeftColumnClass ?>"><span id="elh_letters_updated_at"><?= $Page->updated_at->caption() ?></span></td>
        <td data-name="updated_at"<?= $Page->updated_at->cellAttributes() ?>>
<span id="el_letters_updated_at">
<span<?= $Page->updated_at->viewAttributes() ?>>
<?= $Page->updated_at->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flettersadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#flettersadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flettersedit.validateFields()){ew.prompt({title: ew.language.phrase("MessageEditConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#flettersedit").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<?php if (!$Page->isExport()) { ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
<?php } ?>
