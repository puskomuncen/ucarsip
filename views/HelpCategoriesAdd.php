<?php

namespace PHPMaker2025\ucarsip;

// Page object
$HelpCategoriesAdd = &$Page;
?>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { help_categories: currentTable } });
var currentPageID = ew.PAGE_ID = "add";
var currentForm;
var fhelp_categoriesadd;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fhelp_categoriesadd")
        .setPageId("add")

        // Add fields
        .setFields([
            ["_Language", [fields._Language.visible && fields._Language.required ? ew.Validators.required(fields._Language.caption) : null], fields._Language.isInvalid],
            ["Category_Description", [fields.Category_Description.visible && fields.Category_Description.required ? ew.Validators.required(fields.Category_Description.caption) : null], fields.Category_Description.isInvalid]
        ])

        // Form_CustomValidate
        .setCustomValidate(
            function (fobj) { // DO NOT CHANGE THIS LINE! (except for adding "async" keyword)
                    // Your custom validation code in JAVASCRIPT here, return false if invalid.
                    return true;
                }
        )

        // Use JavaScript validation or not
        .setValidateRequired(ew.CLIENT_VALIDATE)

        // Dynamic selection lists
        .setLists({
            "_Language": <?= $Page->_Language->toClientList($Page) ?>,
        })
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
<?php // Begin of Card view by Masino Sinaga, September 10, 2023 ?>
<?php if (!$Page->IsModal) { ?>
<div class="col-md-12">
  <div class="card shadow-sm">
    <div class="card-header">
	  <h4 class="card-title"><?php echo Language()->phrase("AddCaption"); ?></h4>
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
<form name="fhelp_categoriesadd" id="fhelp_categoriesadd" class="<?= $Page->FormClassName ?>" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="help_categories">
<input type="hidden" name="action" id="action" value="insert">
<input type="hidden" name="modal" value="<?= (int)$Page->IsModal ?>">
<?php if (IsJsonResponse()) { ?>
<input type="hidden" name="json" value="1">
<?php } ?>
<input type="hidden" name="<?= $Page->getFormOldKeyName() ?>" value="<?= $Page->OldKey ?>">
<div class="ew-add-div"><!-- page* -->
<?php if ($Page->_Language->Visible) { // Language ?>
    <div id="r__Language"<?= $Page->_Language->rowAttributes() ?>>
        <label id="elh_help_categories__Language" for="x__Language" class="<?= $Page->LeftColumnClass ?>"><?= $Page->_Language->caption() ?><?= $Page->_Language->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->_Language->cellAttributes() ?>>
<span id="el_help_categories__Language">
    <select
        id="x__Language"
        name="x__Language"
        class="form-select ew-select<?= $Page->_Language->isInvalidClass() ?>"
        <?php if (!$Page->_Language->IsNativeSelect) { ?>
        data-select2-id="fhelp_categoriesadd_x__Language"
        <?php } ?>
        data-table="help_categories"
        data-field="x__Language"
        data-value-separator="<?= $Page->_Language->displayValueSeparatorAttribute() ?>"
        data-placeholder="<?= HtmlEncode($Page->_Language->getPlaceHolder()) ?>"
        <?= $Page->_Language->editAttributes() ?>>
        <?= $Page->_Language->selectOptionListHtml("x__Language") ?>
    </select>
    <?= $Page->_Language->getCustomMessage() ?>
    <div class="invalid-feedback"><?= $Page->_Language->getErrorMessage() ?></div>
<?= $Page->_Language->Lookup->getParamTag($Page, "p_x__Language") ?>
<?php if (!$Page->_Language->IsNativeSelect) { ?>
<script<?= Nonce() ?>>
loadjs.ready("fhelp_categoriesadd", function() {
    var options = { name: "x__Language", selectId: "fhelp_categoriesadd_x__Language" },
        el = document.querySelector("select[data-select2-id='" + options.selectId + "']");
    if (!el)
        return;
    options.closeOnSelect = !options.multiple;
    options.dropdownParent = el.closest("#ew-modal-dialog, #ew-add-opt-dialog");
    if (fhelp_categoriesadd.lists._Language?.lookupOptions.length) {
        options.data = { id: "x__Language", form: "fhelp_categoriesadd" };
    } else {
        options.ajax = { id: "x__Language", form: "fhelp_categoriesadd", limit: ew.LOOKUP_PAGE_SIZE };
    }
    options.minimumInputLength = ew.selectMinimumInputLength;
    options = Object.assign({}, ew.selectOptions, options, ew.vars.tables.help_categories.fields._Language.selectOptions);
    ew.createSelect(options);
});
</script>
<?php } ?>
</span>
</div></div>
    </div>
<?php } ?>
<?php if ($Page->Category_Description->Visible) { // Category_Description ?>
    <div id="r_Category_Description"<?= $Page->Category_Description->rowAttributes() ?>>
        <label id="elh_help_categories_Category_Description" for="x_Category_Description" class="<?= $Page->LeftColumnClass ?>"><?= $Page->Category_Description->caption() ?><?= $Page->Category_Description->Required ? $Language->phrase("FieldRequiredIndicator") : "" ?></label>
        <div class="<?= $Page->RightColumnClass ?>"><div<?= $Page->Category_Description->cellAttributes() ?>>
<span id="el_help_categories_Category_Description">
<input type="<?= $Page->Category_Description->getInputTextType() ?>" name="x_Category_Description" id="x_Category_Description" data-table="help_categories" data-field="x_Category_Description" value="<?= $Page->Category_Description->getEditValue() ?>" size="50" maxlength="100" placeholder="<?= HtmlEncode($Page->Category_Description->getPlaceHolder()) ?>" data-format-pattern="<?= HtmlEncode($Page->Category_Description->formatPattern()) ?>"<?= $Page->Category_Description->editAttributes() ?> aria-describedby="x_Category_Description_help">
<?= $Page->Category_Description->getCustomMessage() ?>
<div class="invalid-feedback"><?= $Page->Category_Description->getErrorMessage() ?></div>
</span>
</div></div>
    </div>
<?php } ?>
</div><!-- /page* -->
<?php
    if (in_array("help", explode(",", $Page->getCurrentDetailTable())) && $help->DetailAdd) {
?>
<?php if ($Page->getCurrentDetailTable() != "") { ?>
<?php if (Container("help")->Count > 0) { // Begin of added by Masino Sinaga, September 16, 2023 ?>
<h4 class="ew-detail-caption"><?= $Language->tablePhrase("help", "TblCaption") ?></h4>
<?php } else { ?>
<h4 class="ew-detail-caption"><?= $Language->tablePhrase("help", "TblCaption") ?></h4>
<?php } // End of added by Masino Sinaga, September 16, 2023 ?>
<?php } ?>
<?php include_once "HelpGrid.php" ?>
<?php } ?>
<?= $Page->IsModal ? '<template class="ew-modal-buttons">' : '<div class="row ew-buttons">' ?><!-- buttons .row -->
    <div class="<?= $Page->OffsetColumnClass ?>"><!-- buttons offset -->
<button class="btn btn-primary ew-btn" name="btn-action" id="btn-action" type="submit" form="fhelp_categoriesadd"><?= $Language->phrase("AddBtn") ?></button>
<?php if (IsJsonResponse()) { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-bs-dismiss="modal"><?= $Language->phrase("CancelBtn") ?></button>
<?php } else { ?>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" form="fhelp_categoriesadd" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
<?php } ?>
    </div><!-- /buttons offset -->
<?= $Page->IsModal ? "</template>" : "</div>" ?><!-- /buttons .row -->
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
<?php
$Page->showPageFooter();
?>
<?php if (!$Page->IsModal && !$Page->isExport()) { ?>
<script>
loadjs.ready(["wrapper", "head", "swal"],function(){$('#btn-action').on('click',function(){if(fhelp_categoriesadd.validateFields()){ew.prompt({title: ew.language.phrase("MessageAddConfirm"),icon:'question',showCancelButton:true},result=>{if(result)$("#fhelp_categoriesadd").submit();});return false;} else { ew.prompt({title: ew.language.phrase("MessageInvalidForm"), icon: 'warning', showCancelButton:false}); }});});
</script>
<?php } ?>
<script<?= Nonce() ?>>
// Field event handlers
loadjs.ready("head", function() {
    ew.addEventHandlers("help_categories");
});
</script>
<?php if (Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD")) { ?>
<script>
loadjs.ready("head", function() { $("#fhelp_categoriesadd:first *:input[type!=hidden]:first").focus(),$("input").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("select").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()}),$("radio").keydown(function(i){if(13==i.which){var e=$(this).closest("form").find(":input:visible:enabled"),n=e.index(this);n==e.length-1||(e.eq(e.index(this)+1).focus(),i.preventDefault())}else 113==i.which&&$("#btn-action").click()})});
</script>
<?php } ?>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your table-specific startup script here, no need to add script tags.
});
</script>
