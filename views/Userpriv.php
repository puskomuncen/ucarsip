<?php

namespace PHPMaker2025\ucarsip;

// Page object
$Userpriv = &$Page;
?>
<style<?= Nonce() ?>>
main .tooltip {
    --bs-tooltip-max-width: 500px;
}
[data-bs-theme=light] th.jtable-column-header.jtable-column-header-sortable {
  z-index:5 !important;
  position: fixed;
  width: 300px !important;
  cursor: pointer;
}
[data-bs-theme=light] th:not(.jtable-column-header.jtable-column-header-sortable) {
  width: 80px !important;
  z-index: 4 !important;
}
[data-bs-theme=light] td:first-child,
[data-bs-theme=light] th:first-child {
  width: 300px !important;
  position:sticky;
  left: -2px;
  z-index: 4 !important;
  background-color:#c4c4c4;
}
table {
  table-layout: fixed;
  width:100%;
}
[data-bs-theme=light] thead tr th {
  background-color:#c4c4c4 !important;
  position:sticky;
  top:0;
}
[data-bs-theme=light] tr.jtable-data-row {
  background-color: #e4e4e4;
}
[data-bs-theme=light] tr.jtable-data-row.jtable-row-even {
  background-color: #f4f4f4;
}
[data-bs-theme=dark] th.jtable-column-header.jtable-column-header-sortable {
  z-index: 5 !important;
  position: fixed;
  width: 300px !important;
}
[data-bs-theme=dark] th:not(.jtable-column-header.jtable-column-header-sortable) {
  width: 80px !important;
  z-index: 4 !important;
}
[data-bs-theme=dark] td:first-child,
[data-bs-theme=dark] th:first-child {
  width: 300px !important;
  position:sticky;
  left: -2px;
  z-index: 4 !important;
  background-color:#212529;
}
[data-bs-theme=dark] thead tr th {
  background-color:#212529 !important;
  border-color: #6c757d;
  position: sticky;
}
[data-bs-theme=dark] tr.jtable-data-row {
  background-color: #495057;
}
[data-bs-theme=dark] tr.jtable-data-row.jtable-row-even {
  background-color: #343a40;
}
</style>
<script<?= Nonce() ?>>
var currentTable = <?= json_encode($Page->toClientVar()) ?>;
ew.deepAssign(ew.vars, { tables: { userlevels: currentTable } });
var currentPageID = ew.PAGE_ID = "userpriv";
var currentForm;
var fuserpriv;
loadjs.ready(["wrapper", "head"], function () {
    let $ = jQuery;
    let fields = currentTable.fields;

    // Form object
    let form = new ew.FormBuilder()
        .setId("fuserpriv")
        .setPageId("userpriv")
        .build();
    window[form.id] = form;
    currentForm = form;
    loadjs.done(form.id);
});
</script>
<script<?= Nonce() ?>>
var headerSortTristate = false,
    tableOptions = {
        locale: ew.LANGUAGE_ID,
        langs: {
            [ew.LANGUAGE_ID]: {
                "data": {
                    "loading": ew.language.phrase("Loading"),
                    "error": ew.language.phrase("Error")
                }
            }
        }
    },
    priv = <?= json_encode($Page->Privileges) ?>;
</script>
<script<?= Nonce() ?>>
loadjs.ready("head", function () {
    // Write your client script here, no need to add script tags.
});
</script>
<?php
$Page->showMessage();
?>
<main>
<form name="fuserpriv" id="fuserpriv" class="ew-form ew-user-priv-form w-100" action="<?= CurrentPageUrl(false) ?>" method="post" novalidate autocomplete="off">
<?php if (Config("CSRF_PROTECTION") && Csrf()->isEnabled()) { ?>
<input type="hidden" name="<?= $TokenNameKey ?>" id="<?= $TokenNameKey ?>" value="<?= $TokenName ?>"><!-- CSRF token name -->
<input type="hidden" name="<?= $TokenValueKey ?>" id="<?= $TokenValueKey ?>" value="<?= $TokenValue ?>"><!-- CSRF token value -->
<?php } ?>
<input type="hidden" name="t" value="userlevels">
<input type="hidden" name="action" id="action" value="update">
<input type="hidden" name="x_ID" id="x_ID" value="<?= $Page->ID->CurrentValue ?>">
<div class="ew-desktop">
<div class="card ew-card ew-user-priv">
<div class="card-header">
    <h3 class="card-title"><?= $Language->phrase("UserLevel") ?><?= $Security->getUserLevelName((int)$Page->ID->CurrentValue) ?> (<?= $Page->ID->CurrentValue ?>)</h3>
    <div class="card-tools float-none float-sm-end">
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" name="table-name" id="table-name" class="form-control form-control-sm" placeholder="<?= HtmlEncode($Language->phrase("Search", true)) ?>">
        </div>
    </div>
</div>
<div class="card-body ew-card-body p-0 <?= $Page->ResponsiveTableClass ?>"></div>
</div>
<div class="ew-buttons ew-desktop-buttons">
<button class="btn btn-primary ew-btn" name="btn-submit" id="btn-submit" type="submit"<?= $Page->Disabled ?>><?= $Language->phrase("Update") ?></button>
<button class="btn btn-default ew-btn" name="btn-cancel" id="btn-cancel" type="button" data-href="<?= HtmlEncode(GetUrl($Page->getReturnUrl())) ?>"><?= $Language->phrase("CancelBtn") ?></button>
</div>
</div>
</form>
</main>
<script<?= Nonce() ?>>
var useFixedHeaderTable = true,
    tableHeight = "400px",
    priv = <?= JsonEncode($Page->Privileges) ?>;
ew.ready("makerjs", [
    ew.PATH_BASE + "jquery/jquery.jtable.min.js",
<?php if (MS_ENABLE_PERMISSION_FOR_EXPORT_DATA == TRUE) { ?>
    ew.PATH_BASE + "js/userprivmod.js?v=1731330577"
<?php } else { ?>
	ew.PATH_BASE + "js/userprivori.js?v=1731330577"
<?php } ?>
]);
</script>
<script<?= Nonce() ?>>
loadjs.ready("load", function () {
    // Write your startup script here, no need to add script tags.
});
</script>
<script<?= Nonce() ?>>
ew.ready(["load", "tabulator"], ew.PATH_BASE + "js/userpriv.min.js?v=25.10.0");
</script>
