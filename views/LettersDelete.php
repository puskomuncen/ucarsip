<?php

namespace PHPMaker2025\ucarsip;

// Page object
$LettersDelete = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { letters: currentTable } });
var currentPageID = ew.PAGE_ID = "delete";
var currentForm;
var flettersdelete;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("flettersdelete")
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
<form name="flettersdelete" id="flettersdelete" class="ew-form ew-delete-form" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="letters">
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
<?php if ($Page->letter_id->Visible) { // letter_id ?>
        <th class="<?= $Page->letter_id->headerCellClass() ?>"><span id="elh_letters_letter_id" class="letters_letter_id"><?= $Page->letter_id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->nomor_surat->Visible) { // nomor_surat ?>
        <th class="<?= $Page->nomor_surat->headerCellClass() ?>"><span id="elh_letters_nomor_surat" class="letters_nomor_surat"><?= $Page->nomor_surat->caption() ?></span></th>
<?php } ?>
<?php if ($Page->perihal->Visible) { // perihal ?>
        <th class="<?= $Page->perihal->headerCellClass() ?>"><span id="elh_letters_perihal" class="letters_perihal"><?= $Page->perihal->caption() ?></span></th>
<?php } ?>
<?php if ($Page->tanggal_surat->Visible) { // tanggal_surat ?>
        <th class="<?= $Page->tanggal_surat->headerCellClass() ?>"><span id="elh_letters_tanggal_surat" class="letters_tanggal_surat"><?= $Page->tanggal_surat->caption() ?></span></th>
<?php } ?>
<?php if ($Page->tanggal_terima->Visible) { // tanggal_terima ?>
        <th class="<?= $Page->tanggal_terima->headerCellClass() ?>"><span id="elh_letters_tanggal_terima" class="letters_tanggal_terima"><?= $Page->tanggal_terima->caption() ?></span></th>
<?php } ?>
<?php if ($Page->jenis->Visible) { // jenis ?>
        <th class="<?= $Page->jenis->headerCellClass() ?>"><span id="elh_letters_jenis" class="letters_jenis"><?= $Page->jenis->caption() ?></span></th>
<?php } ?>
<?php if ($Page->klasifikasi->Visible) { // klasifikasi ?>
        <th class="<?= $Page->klasifikasi->headerCellClass() ?>"><span id="elh_letters_klasifikasi" class="letters_klasifikasi"><?= $Page->klasifikasi->caption() ?></span></th>
<?php } ?>
<?php if ($Page->pengirim->Visible) { // pengirim ?>
        <th class="<?= $Page->pengirim->headerCellClass() ?>"><span id="elh_letters_pengirim" class="letters_pengirim"><?= $Page->pengirim->caption() ?></span></th>
<?php } ?>
<?php if ($Page->penerima_unit_id->Visible) { // penerima_unit_id ?>
        <th class="<?= $Page->penerima_unit_id->headerCellClass() ?>"><span id="elh_letters_penerima_unit_id" class="letters_penerima_unit_id"><?= $Page->penerima_unit_id->caption() ?></span></th>
<?php } ?>
<?php if ($Page->file_url->Visible) { // file_url ?>
        <th class="<?= $Page->file_url->headerCellClass() ?>"><span id="elh_letters_file_url" class="letters_file_url"><?= $Page->file_url->caption() ?></span></th>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
        <th class="<?= $Page->status->headerCellClass() ?>"><span id="elh_letters_status" class="letters_status"><?= $Page->status->caption() ?></span></th>
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
<?php if ($Page->letter_id->Visible) { // letter_id ?>
        <td<?= $Page->letter_id->cellAttributes() ?>>
<span id="">
<span<?= $Page->letter_id->viewAttributes() ?>>
<?= $Page->letter_id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->nomor_surat->Visible) { // nomor_surat ?>
        <td<?= $Page->nomor_surat->cellAttributes() ?>>
<span id="">
<span<?= $Page->nomor_surat->viewAttributes() ?>>
<?= $Page->nomor_surat->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->perihal->Visible) { // perihal ?>
        <td<?= $Page->perihal->cellAttributes() ?>>
<span id="">
<span<?= $Page->perihal->viewAttributes() ?>>
<?= $Page->perihal->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->tanggal_surat->Visible) { // tanggal_surat ?>
        <td<?= $Page->tanggal_surat->cellAttributes() ?>>
<span id="">
<span<?= $Page->tanggal_surat->viewAttributes() ?>>
<?= $Page->tanggal_surat->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->tanggal_terima->Visible) { // tanggal_terima ?>
        <td<?= $Page->tanggal_terima->cellAttributes() ?>>
<span id="">
<span<?= $Page->tanggal_terima->viewAttributes() ?>>
<?= $Page->tanggal_terima->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->jenis->Visible) { // jenis ?>
        <td<?= $Page->jenis->cellAttributes() ?>>
<span id="">
<span<?= $Page->jenis->viewAttributes() ?>>
<?= $Page->jenis->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->klasifikasi->Visible) { // klasifikasi ?>
        <td<?= $Page->klasifikasi->cellAttributes() ?>>
<span id="">
<span<?= $Page->klasifikasi->viewAttributes() ?>>
<?= $Page->klasifikasi->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->pengirim->Visible) { // pengirim ?>
        <td<?= $Page->pengirim->cellAttributes() ?>>
<span id="">
<span<?= $Page->pengirim->viewAttributes() ?>>
<?= $Page->pengirim->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->penerima_unit_id->Visible) { // penerima_unit_id ?>
        <td<?= $Page->penerima_unit_id->cellAttributes() ?>>
<span id="">
<span<?= $Page->penerima_unit_id->viewAttributes() ?>>
<?= $Page->penerima_unit_id->getViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Page->file_url->Visible) { // file_url ?>
        <td<?= $Page->file_url->cellAttributes() ?>>
<span id="">
<span<?= $Page->file_url->viewAttributes() ?>>
<?= GetFileViewTag($Page->file_url, $Page->file_url->getViewValue(), false) ?>
</span>
</span>
</td>
<?php } ?>
<?php if ($Page->status->Visible) { // status ?>
        <td<?= $Page->status->cellAttributes() ?>>
<span id="">
<span<?= $Page->status->viewAttributes() ?>>
<?= $Page->status->getViewValue() ?></span>
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
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(flettersdelete.validateFields()){ew.prompt({title: ew.language.phrase("MessageDeleteConfirm"),icon:'question',showCancelButton:true},result=>{if(result) $("#flettersdelete").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
