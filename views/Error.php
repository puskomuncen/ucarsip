<?php namespace PHPMaker2025\ucarsip; ?>
<div class="error-page">
    <?php if (@$Error["statusCode"] > 200) { ?>
    <h2 class="headline <?= @$Error["error"]["class"] ?>"><?= $Error["statusCode"] ?></h2>
    <?php } ?>
    <div class="error-content">
        <?php if (@$Error["error"]["type"]) { ?>
        <h3><i class="fa-solid fa-triangle-exclamation <?= @$Error["error"]["class"] ?>"></i> <?= @$Error["error"]["type"] ?></h3>
        <?php } ?>
        <p><?= HtmlEncode(@$Error["error"]["description"]) ?></p>
        <?php if (@$Error["error"]["trace"]) { ?>
        <div class="card card-danger ew-debug">
            <div class="card-header">
                <h3 class="card-title"><?= Language()->phrase("Debug") ?: "Debug" ?></h3>
            </div>
            <div class="card-body">
                <pre><?= $Error["error"]["trace"] ?></pre>
            </div>
        </div>
        <?php } ?>
    </div>
    <!-- /.error-content -->
</div>
<!-- /.error-page -->
<?php if (@$Error["error"]["trace"] === false) { // Show trace in debug bar ?>
<script<?= Nonce() ?>>loadjs.ready("load", () => { localStorage.setItem("phpdebugbar-tab", "exceptions"); ew.<?= DebugBar()->getVariableName() ?>.restore(); });</script>
<?php } ?>
