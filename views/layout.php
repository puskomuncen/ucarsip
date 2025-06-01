<?php

namespace PHPMaker2025\ucarsip;

// Base path
$basePath = BasePath(true);
?>
<!DOCTYPE html>
<html lang="<?= CurrentLanguageID() ?>"<?= IsRTL() ? 'dir="rtl"' : '' ?> data-bs-theme="light">
<head>
<title>
<?php 
	// === Begin of modification Page Title variable, by Masino Sinaga, September 12, 2023 == \\
	global $sCurrentPageTitle, $CurrentPageTitle, $sLogoFilename;
	$sCurrentPageTitle = "";
	$sCurrentPageTitle = (getCurrentPageTitle(CurrentPageName()) != "") ? getCurrentPageTitle(CurrentPageName()) :  "";
	if (empty($sCurrentPageTitle)) {
		echo $Language->ProjectPhrase("BodyTitle");
	} else {
		echo strip_tags($sCurrentPageTitle) . " &laquo; " . $Language->ProjectPhrase("BodyTitle");
	}
	// === End of modification Page Title variable, by Masino Sinaga, September 12, 2023 == \\
?>
</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?= $basePath ?>css/select2.min.css?v=25.10.0">
<link rel="stylesheet" href="<?= $basePath ?>css/select2-bootstrap5.min.css?v=25.10.0">
<link rel="stylesheet" href="<?= $basePath ?><?= Config("FONT_AWESOME_STYLESHEET") ?>?v=25.10.0">
<link rel="stylesheet" href="<?= $basePath ?>css/jquery.overlayScrollbars.css?v=25.10.0">
<?php // Begin of modification by Masino Sinaga, September 7, 2023 ?>
<link rel="stylesheet" href="<?= $basePath ?><?= AutoVersion("adminlte32/css/" . CssFile("adminlte.css") . ""); ?>">
<?php // End of modification by Masino Sinaga, September 7, 2023 ?>
<link rel="stylesheet" href="<?= $basePath ?><?= CssFile(Config("PROJECT_STYLESHEET_FILENAME")) ?>?v=25.10.0">
<?php // === Begin modification of Alertify Notification, by Masino Sinaga, September 9, 2023  === \\ ?>
<?php if($Language->phrase("dir")=="rtl") { ?>
<link rel="stylesheet" href="<?= $basePath ?>plugins/alertifyjs/css/alertify.rtl.min.css?v=1694586875" id="alertify_style">
<link rel="stylesheet" href="<?= $basePath ?>plugins/alertifyjs/css/themes/bootstrap-flat.rtl.min.css?v=1694586875" id="alertify_theme">
<?php } else { ?>
<link rel="stylesheet" href="<?= $basePath ?>plugins/alertifyjs/css/alertify.min.css?v=1694586875" id="alertify_style">
<link rel="stylesheet" href="<?= $basePath ?>plugins/alertifyjs/css/themes/bootstrap-flat.min.css?v=1694586875" id="alertify_theme">
<?php } ?>
<?php // === End modification of Alertify Notification, by Masino Sinaga, September 9, 2023 === \\ ?>
<link rel="stylesheet" href="<?= $basePath ?><?= AutoVersion("adminlte32/css/custom-style.css"); ?>">
<link rel="stylesheet" href="<?= $basePath ?><?= AutoVersion("adminlte32/css/font-montserrat.css"); ?>">
<?php // === Begin modification by Masino Sinaga, September 9, 2023 === \\ ?>
<?php if (Language()->Type == "DROPDOWN" || Language()->Type == "LI") { ?>
<link rel="stylesheet" href="<?= $basePath ?>plugins/language-selector-dropdown/flags.css?v=1694586875">
<?php } elseif (Language()->Type == "SELECT") { ?>
<?php if($Language->phrase("dir")=="rtl") { ?>
<link rel="stylesheet" href="<?= $basePath ?>plugins/language-selector-combobox/css/msdropdown/dd-rtl.css?v=1694586875">
<link rel="stylesheet" href="<?= $basePath ?>plugins/language-selector-dropdown/flags.css?v=1694586875">
<?php } else { ?>
<link rel="stylesheet" href="<?= $basePath ?>plugins/language-selector-combobox/css/msdropdown/dd.css?v=1694586875">
<link rel="stylesheet" href="<?= $basePath ?>plugins/language-selector-dropdown/flags.css?v=1694586875">
<?php } ?>
<link rel="stylesheet" href="<?= $basePath ?>plugins/language-selector-combobox/css/msdropdown/flags.css?v=1694586875">
<?php } ?>
<?php // === End modification by Masino Sinaga, September 9, 2023 ==- \\ ?>
<script<?= Nonce() ?> data-pace-options='<?= json_encode(Config("PACE_OPTIONS")) ?>' src="<?= $basePath ?>js/pace.js?v=25.10.0"></script><!-- Single quotes for data-pace-options -->
<?php // === Begin modification by Masino Sinaga, September 9, 2023 ==- \\ ?>
<script<?= Nonce() ?> src="<?= $basePath ?><?= AutoVersion("js/masinoewcore.js"); ?>"></script>
<?php // === End modification by Masino Sinaga, September 9, 2023 ==- \\ ?>
<script<?= Nonce() ?>>
var document_title = document.title;
var $rowindex$ = null;
Object.assign(ew, <?= json_encode(ConfigClientVars()) ?>, <?= json_encode(GlobalClientVars()) ?>);
loadjs.done("clientvars"); // Write tab ID cookie
loadjs(ew.PATH_BASE + "jquery/jquery.min.js?v=25.10.0", "jquery");
loadjs(ew.PATH_BASE + "js/popper.min.js?v=25.10.0", "popper");
loadjs(ew.PATH_BASE + "js/luxon.min.js?v=25.10.0", "luxon");
loadjs([
    ew.PATH_BASE + "js/ua-parser.min.js?v=25.10.0",
    ew.PATH_BASE + "js/purify.min.js?v=25.10.0",
    ew.PATH_BASE + "js/cropper.min.js?v=25.10.0",
    ew.PATH_BASE + "jquery/load-image.all.min.js?v=25.10.0"
], "others");
loadjs([ew.PATH_BASE + "js/sweetalert2.min.js?v=25.10.0", ew.PATH_BASE + "css/sweetalert2.min.css?v=25.10.0"], "swal");
<?= $Language->toJson() ?>
ew.vars = <?= json_encode(GetClientVar()) ?>;
ew.ready(["wrapper", "jquery"], ew.PATH_BASE + "jquery/jsrender.min.js?v=25.10.0", "jsrender", ew.renderJsTemplates);
ew.ready("jsrender", ew.PATH_BASE + "jquery/jquery.overlayScrollbars.min.js?v=25.10.0", "scrollbars"); // Init sidebar scrollbars after rendering menu
ew.ready("jquery", ew.PATH_BASE + "jquery/jquery-ui.min.js?v=25.10.0", "widget");
<?php // === Begin modification of Language Selector and Alertify Javascript, by Masino Sinaga, September 11, 2020  === \\ ?>
ew.ready("jquery", ew.PATH_BASE + "plugins/language-selector-combobox/js/msdropdown/jquery.dd.js?v=1694586875", "msdropdown");
ew.ready("jquery", ew.PATH_BASE + "plugins/alertifyjs/alertify.min.js?v=1694586875", "alertifyjs");
<?php // === End modification of Language Selector and Alertify Javascript, by Masino Sinaga, September 11, 2020  === \\ ?>
</script>
<?php include_once "views/menu.php"; ?>
<script<?= Nonce() ?>>
var cssfiles = [
    ew.PATH_BASE + "css/jquery.fileupload.css?v=25.10.0",
    ew.PATH_BASE + "css/jquery.fileupload-ui.css?v=25.10.0",
    ew.PATH_BASE + "css/cropper.min.css?v=25.10.0"
];
cssfiles.push(ew.PATH_BASE + "colorbox/colorbox.css?v=25.10.0");
loadjs(cssfiles, "css");
var cssjs = [];
<?php foreach (array_merge(Config("STYLESHEET_FILES"), Config("JAVASCRIPT_FILES")) as $file) { // External Stylesheets and JavaScripts ?>
cssjs.push("<?= (IsRemote($file) ? "" : BasePath(true)) . $file ?>?v=25.10.0");
<?php } ?>
var jqueryjs = [
    ew.PATH_BASE + "jquery/select2.full.min.js?v=25.10.0",
    ew.PATH_BASE + "jquery/jqueryfileupload.min.js?v=25.10.0",
    ew.PATH_BASE + "jquery/typeahead.jquery.min.js?v=25.10.0"
];
jqueryjs.push(ew.PATH_BASE + "jquery/pStrength.jquery.min.js?v=25.10.0");
jqueryjs.push(ew.PATH_BASE + "jquery/pGenerator.jquery.min.js?v=25.10.0");
jqueryjs.push(ew.PATH_BASE + "colorbox/jquery.colorbox.min.js?v=25.10.0");
jqueryjs.push(ew.PATH_BASE + "js/pdfobject.min.js?v=25.10.0");
ew.ready(["jquery", "dom", "popper"], ew.PATH_BASE + "bootstrap5/js/bootstrap.min.js?v=25.10.0", "bootstrap"); // Bootstrap
ew.ready("popper", [ew.PATH_BASE + "js/tippy.umd.min.js?v=25.10.0", ew.PATH_BASE + "css/tippy.css?v=25.10.0",], "tippy"); // Tippy
ew.ready("bootstrap", ew.PATH_BASE + "<?= AutoVersion("adminlte32/js/adminlte.js"); ?>", "adminlte"); // AdminLTE (After Bootstrap)
ew.ready(["jquery", "widget", "msdropdown"], [jqueryjs], "jqueryjs");
ew.ready(["bootstrap", "adminlte", "jqueryjs", "luxon", "others"], ew.PATH_BASE + "<?= AutoVersion("js/masinoew.js"); ?>", "makerjs");
ew.ready("makerjs", [
    cssjs,
    ew.PATH_BASE + "<?= AutoVersion("js/userfn.js"); ?>",
    ew.PATH_BASE + "<?= AutoVersion("js/userevent.js"); ?>"
], "head");
</script>
<script<?= Nonce() ?>>
loadjs(ew.PATH_BASE + "css/<?= CssFile("tempus-dominus.css") ?>?v=25.10.0");
ew.ready("head", [
    ew.PATH_BASE + "js/tempus-dominus.min.js?v=25.10.0",
    ew.PATH_BASE + "js/ewdatetimepicker.min.js?v=25.10.0"
], "datetimepicker");
</script>
<script>
ew.ready("head", [
	ew.PATH_BASE + "jquery/jquery.inputmask.min.js",
	ew.PATH_BASE + "jquery/msjqueryinputmask.js"
], "jqueryinputmask");
</script>
<script<?= Nonce() ?>>
loadjs.ready(["wrapper", "jquery"], () => $(document).find("tr[data-rowindex][data-widget='expandable-table']").on("expand.lte.expandableTable", (e) => !Pace?.running || e.preventDefault())); // Add "expand" handler to rows
</script>
<script>
ew.ready("head", [
	ew.PATH_BASE + "plugins/js-cookie/src/js.cookie.js?v=1725851613"
], "jscookie");
</script>
<script<?= Nonce() ?>>
ew.TINYMCE_VERSION = 6;
</script>
<script<?= Nonce() ?>>
ew.ready("head", [ew.PATH_BASE + "tinymce/tinymce.min.js?v=25.10.0", ew.PATH_BASE + "js/eweditor.js?v=25.10.0"], "editor");
</script>
<style<?= Nonce() ?>>
.tox-promotion, .tox-statusbar__branding {
    display: none;
}
</style>
<script<?= Nonce() ?>>
// Load color picker (requires "jquery" and "bootstrap", see layout.php for the ids)
ew.ready(["jquery", "bootstrap"], [[
    ew.PATH_BASE + "plugins/colorpicker/css/bootstrap-colorpicker.min.css?v=25.10.0",
    ew.PATH_BASE + "plugins/colorpicker/js/bootstrap-colorpicker.min.js?v=25.10.0"
]], "colorpicker");
// Color picker options
ew.colorPickerOptions = { format: 'hex', autoHexInputFallback: true };
</script>
<?php // === Begin modification of Alertifyjs by Masino Sinaga, September 10, 2023 === \\ ?>
<script<?= Nonce() ?>>
loadjs.ready("alertifyjs", function () {
	alertify.defaults.pinnable = true;alertify.defaults.modal = false;alertify.defaults.transition = "pulse";alertify.defaults.theme.ok = "btn btn-primary";alertify.defaults.theme.cancel = "btn btn-danger";alertify.defaults.theme.input = "form-control";
});
</script>
<?php // === End modification of Alertifyjs by Masino Sinaga, September 10, 2023 === \\ ?>
<!-- Navbar -->
<script type="text/html" id="navbar-menu-items" class="ew-js-template" data-name="navbar" data-seq="10" data-data="navbar" data-method="appendTo" data-target="#ew-navbar">
{{if items}}
    {{for items}}
        <li id="{{:id}}" data-name="{{:name}}" class="{{if parentId == -1}}nav-item ew-navbar-item{{/if}}{{if isHeader && parentId > -1}}dropdown-header{{/if}}{{if items}} dropdown{{/if}}{{if items && parentId != -1}} dropdown-submenu{{/if}}{{if items && level == 1}} dropdown-hover{{/if}} d-none d-sm-block">
            {{if isHeader && parentId > -1}}
                {{if icon}}<i class="{{:icon}}"></i>{{/if}}
                <span>{{:text}}</span>
            {{else}}
            <a href="{{:href}}"{{if target}} target="{{:target}}"{{/if}} class="{{if parentId == -1}}nav-link{{else}}dropdown-item{{/if}}{{if active}} active{{/if}}{{if items}} dropdown-toggle ew-dropdown {{if open}} active {{/if}}{{/if}}"{{if items}} role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"{{/if}}{{if attrs}}{{:attrs}}{{/if}} data-ew-action="redirect" data-url="{{:href}}">
                {{if icon}}<i class="{{:icon}}"></i>{{/if}}
                <span>{{:text}}</span>
            </a>
            {{/if}}
            {{if items}}
            <ul class="dropdown-menu">
                {{include tmpl="#navbar-menu-items"/}}
            </ul>
            {{/if}}
        </li>
    {{/for}}
{{/if}}
</script>
<!-- Sidebar -->
<script type="text/html" class="ew-js-template" data-name="menu" data-seq="10" data-data="menu" data-target="#ew-menu">
{{if items}}
    <ul class="nav nav-sidebar nav-child-indent nav-legacy flex-column{{if compact}} nav-compact{{/if}}" data-widget="treeview" role="menu" data-accordion="{{:accordion}}">
    {{include tmpl="#menu-items"/}}
    </ul>
{{/if}}
</script>
<script type="text/html" id="menu-items">
{{if items}}
    {{for items}}
        <li id="{{:id}}" data-name="{{:name}}" class="{{if isHeader}}nav-header{{else}}nav-item{{if items}} has-treeview{{/if}}{{if active}} active current{{/if}}{{if active && items || open}} menu-open{{/if}}{{/if}}{{if isNavbarItem}} d-block d-sm-none{{/if}}">
            {{if isHeader}}
                {{if icon}}<i class="{{:icon}}"></i>{{/if}}
                <span>{{:text}}</span>
                {{if label}}
                <span class="right">
                    {{:label}}
                </span>
                {{/if}}
            {{else}}
			{{if href == "javascript:void(0);"}}
			<a href="{{:href}}" class="nav-link{{if (active || open)}} active{{/if}}"{{if target}} target="{{:target}}"{{/if}}{{if attrs}}{{:attrs}}{{/if}}>
			{{else}}
            <a href="{{:href}}"{{if target}} target="{{:target}}"{{/if}}{{if attrs}}{{:attrs}}{{/if}} data-ew-action="redirect" data-url="{{:href}}">
			{{/if}}
                {{if icon}}<i class="nav-icon {{:icon}}"></i>{{/if}}
                <p>{{:text}}
                    {{if items}}
                        <i class="right fa-solid fa-angle-left"></i>
                        {{if label}}
                            <span class="right">
                                {{:label}}
                            </span>
                        {{/if}}
                    {{else}}
                        {{if label}}
                            <span class="right">
                                {{:label}}
                            </span>
                        {{/if}}
                    {{/if}}
                </p>
            </a>
            {{/if}}
            {{if items}}
            <ul class="nav nav-treeview"{{if open}} style="display: block;"{{/if}}>
                {{include tmpl="#menu-items"/}}
            </ul>
            {{/if}}
        </li>
    {{/for}}
{{/if}}
</script>
<script type="text/html" class="ew-js-template" data-name="languages" data-seq="10" data-data="languages" data-method="<?= $Language->Method ?>" data-target="<?= HtmlEncode($Language->Target) ?>">
<?= $Language->getTemplate() ?>
</script>
<script type="text/html" class="ew-js-template" data-name="colormodes" data-seq="10" data-method="appendTo" data-target="#ew-navbar-end" data-callback="initThemes">
<li class="nav-item dropdown">
    <button class="btn btn-link nav-link py-2 px-0 px-lg-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="true" data-bs-toggle="dropdown" data-bs-display="static" aria-label="<?= HtmlEncode($Language->phrase("ToggleTheme", true)) ?> (<?= HtmlEncode($Language->phrase("ThemeLight")) ?>)">
        <i class="<?= $Language->phraseClass("ThemeLight") ?> my-1 theme-icon-active"></i>
        <span class="d-none ms-2" id="bd-theme-text"><?= $Language->phrase("ToggleTheme") ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme-text" data-bs-popper="static">
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="light" aria-pressed="true">
                <i class="<?= $Language->phraseClass("ThemeLight") ?> me-2 opacity-50 theme-icon"></i>
                <span class="fs-6"><?= $Language->phrase("ThemeLight", true) ?></span>
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                <i class="<?= $Language->phraseClass("ThemeDark") ?> me-2 opacity-50 theme-icon"></i>
                <span class="fs-6"><?= $Language->phrase("ThemeDark", true) ?></span>
            </button>
        </li>
    </ul>
</li>
</script>
<script type="text/html" class="ew-js-template" data-name="login" data-seq="10" data-data="login" data-method="appendTo" data-target=".navbar-nav.ms-auto">
{{if canSubscribe}}
<li class="nav-item"><a id="subscribe-notification" class="nav-link disabled">{{:subscribeText}}</a></li>
{{/if}}
{{if (isLoggedIn || isAuthenticated)}}
<li class="nav-item dropdown text-body">
    <a id="ew-nav-link-user" class="nav-link ew-user" data-bs-toggle="dropdown" href="#">
		<?php if (CurrentUserImageBase64()) { ?>
			<div class="user-panel d-flex">
                <div class="user-image">
                    <?= Config("MS_USER_CARD_USER_NAME"); ?>&nbsp;<img src="data:image/png;base64,<?= CurrentUserImageBase64() ?>" class="img-circle ew-user-image" alt="">
                </div>
			</div>
        <?php } else { ?>
			<div class="user-image">
		    <?= Config("MS_USER_CARD_USER_NAME"); ?>&nbsp;<i class="fas fa-user"></i>
			</div>
		<?php } ?>
    </a>
    <div class="dropdown-menu dropdown-menu-end ew-user-dropdown" aria-labelledby="ew-nav-link-user">
        <div class="ms-user-card" style="background-image: url(<?= $basePath ?>assets/media/misc/head_bg_sm.jpg); top: -8px;">
			<div class="ms-user-card-wrapper">
				<div class="ms-user-card-pic">
					<img src="data:image/png;base64,<?= CurrentUserImageBase64() ?>" class="img-circle ew-user-image" alt="">
				</div>
				<div class="ms-user-card-details">
					<div class="ms-user-card-name"><?php echo Config("MS_USER_CARD_COMPLETE_NAME"); ?></div>
					<div class="ms-user-card-position"><?php echo Config("MS_USER_CARD_POSITION"); ?></div>
				</div>
			</div>
		</div>
    {{if isLoggedIn}}
        <div class="dropdown-header">
			<i class="fa-solid fa-user me-2"></i>{{:currentUserName}}
		</div>
		<a class="dropdown-item" id="dropdown-user-profile" data-ew-action="redirect" data-url="<?php echo $basePath; ?>theuserprofilelist"><?php echo Language()->phrase("UserProfileTitle"); ?></a>
		<div class="dropdown-divider"></div>
        {{if hasPersonalData}}
        <a class="dropdown-item" id="personal-data"{{props personalData}} data-{{:key}}="{{>prop}}"{{/props}}>{{:personalDataText}}</a>
        {{/if}}
        {{if canChangePassword}}
        <a class="dropdown-item" id="change-password"{{props changePassword}} data-{{:key}}="{{>prop}}"{{/props}}>{{:changePasswordText}}</a>
        {{/if}}
        {{if can2fa}}
        <a class="dropdown-item" id="config-2fa" data-ew-action="redirect" data-url="{{url:twofaUrl}}">{{:twofaText}}</a>
        {{/if}}
        {{if canChat}}
        <a class="dropdown-item{{if !enableChat}} d-none{{/if}}" id="enable-chat" data-ew-action="toggle-chat" data-value="1">{{:enableChatText}}</a>
        <a class="dropdown-item{{if !disableChat}} d-none{{/if}}" id="disable-chat" data-ew-action="toggle-chat" data-value="0">{{:disableChatText}}</a>
        {{/if}}
    {{/if}}
        {{if canLogout}}
        <div class="dropdown-divider"></div>
        <div class="dropdown-footer text-end py-0">
            <a class="btn btn-default"{{props logout}} data-{{:key}}="{{>prop}}"{{/props}}>{{:logoutText}}</a>
        </div>
        {{/if}}
    </div>
</li>
{{else}}
    {{if canLogin}}
<li class="nav-item"><a class="nav-link ew-tooltip" title="{{:loginTitle}}"{{props login}} data-{{:key}}="{{>prop}}"{{/props}}>{{:loginText}}</a></li>
    {{/if}}
    {{if canLogout}}
<li class="nav-item"><a class="nav-link ew-tooltip"{{props logout}} data-{{:key}}="{{>prop}}"{{/props}}>{{:logoutText}}</a></li>
    {{/if}}
{{/if}}
</script>
<link rel="shortcut icon" type="image/x-icon" href="<?= BasePath() ?>/favicon.ico">
<link rel="icon" type="image/x-icon" href="<?= BasePath() ?>/favicon.ico">
<?= DebugBarRenderer()?->renderHead() ?>
<meta name="generator" content="PHPMaker 2025.10.0">
</head>
<body class="<?= Config("BODY_CLASS") ?>" style="<?= Config("BODY_STYLE") ?>">
<?php if (@!$SkipHeaderFooter) { ?>
<div class="wrapper ew-layout">
    <!-- Main Header -->
    <!-- Navbar -->
    <nav class="<?= Config("NAVBAR_CLASS") ?>">
        <div class="container-fluid">
            <!-- Left navbar links -->
            <ul id="ew-navbar" class="navbar-nav">
                <li class="nav-item d-block">
                    <a class="nav-link" data-widget="pushmenu" data-enable-remember="true" data-ew-action="none"><i class="fa-solid fa-bars ew-icon"></i></a>
                </li>
                <a class="navbar-brand d-none" href="#" data-ew-action="none">
                    <span class="brand-text">ARSIP UNCEN</span>
                </a>
            </ul>
            <!-- Right navbar links -->
            <ul id="ew-navbar-end" class="navbar-nav ms-auto"></ul>
        </div>
    </nav>
    <!-- /.navbar -->
    <!-- Main Sidebar Container -->
    <aside class="<?= Config("SIDEBAR_CLASS") ?>">
        <div class="brand-container">
            <!-- Brand Logo //** Note: Only licensed users are allowed to change the logo ** -->
            <a href="#" class="brand-link">
                <span class="brand-text">ARSIP UNCEN</span>
            </a>
        </div>
        <!-- Sidebar -->
        <div id="my-sidebar" class="sidebar">
            <!-- Sidebar user panel -->
            <!-- Sidebar Menu -->
            <nav id="ew-menu" class="mt-2"></nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
    <?php if (Config("PAGE_TITLE_STYLE") != "None") { ?>
            <div class="container-fluid">
                <div class="row">
                <?php if (MS_SHOW_PHPMAKER_BREADCRUMBLINKS == false && MS_SHOW_MASINO_BREADCRUMBLINKS == false) { ?>
				<div class="col-sm-12">
				<?php } else { ?>
                <div class="col-sm-6">
				<?php } ?>
                    <h1 class="m-0 text-dark"><?= CurrentPageHeading() ?> 
					<?php if (MS_SHOW_HELP_ONLINE) { ?> 
					&nbsp;<a href='javascript:void(0);' id='helponline' onclick='getHelp("<?php echo CurrentPageName(); ?>");' title='<?php echo $Language->phrase("Help"); ?>'><span class='fa fa-question-circle ew-icon'></span></a> 
					 <?php } ?>
					</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
				<?php if (MS_SHOW_PHPMAKER_BREADCRUMBLINKS == true) { ?>
                    <?= Breadcrumb()->render() ?>
				<?php } ?>
					<?php if (MS_SHOW_MASINO_BREADCRUMBLINKS == true) { ?>
						<?php echo MasinoBreadcrumbLinks(); ?>
					<?php } ?>
                </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
    <?php } ?>
        </div>
        <!-- /.content-header -->
        <!-- Main content -->
        <section class="content">
        <div class="container-fluid">
		<?php // Begin of modification Announcement in All Pages, by Masino Sinaga, May 12, 2012 \\ ?>
		<?php if (Config("MS_SHOW_ANNOUNCEMENT")) { ?>
		<?php   if (Config("MS_ANNOUNCEMENT_TEXT") != "") { ?>
		<div class="alert alert-info shadow">
		  <h5><i class="icon fas fa-info"></i>Info</h5>
			<?php echo Config("MS_ANNOUNCEMENT_TEXT"); ?>
		  <!-- /.card-body -->
		</div>
		<!-- /.card -->
		<?php   } ?>
		<?php } ?>
		<?php // End of modification Announcement in All Pages, by Masino Sinaga, May 12, 2012 \\ ?>
		<?php // Begin of modification Maintenance Mode, by Masino Sinaga, May 12, 2012 \\ ?>
		<?php $date_now = date("Y-m-d H:i:s"); ?>
		<?php if ( (Config("MS_MAINTENANCE_MODE") == TRUE &&	
				   strtotime(Config("MS_MAINTENANCE_END_DATETIME")) > strtotime($date_now) && 
				   (Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE") ==  FALSE || Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE") ==  TRUE)) ||
				   (Config("MS_MAINTENANCE_MODE") == TRUE && 
				   strtotime(Config("MS_MAINTENANCE_END_DATETIME")) < strtotime($date_now) && 
				   Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE") ==  FALSE) || 
				   (Config("MS_MAINTENANCE_MODE") == TRUE && Config("MS_MAINTENANCE_END_DATETIME") == "" && 
				   (Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE") ==  FALSE || Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE") ==  TRUE))
				   ) { ?>
		<?php   if (Config("MS_MAINTENANCE_TEXT")!="") { ?>
		<div class="alert alert-warning shadow">
		  <h5><i class="icon fas fa-exclamation-triangle"></i>Alert</h5>
			<?php echo Config("MS_MAINTENANCE_TEXT"); ?>
		  </div>
		  <!-- /.card-body -->
		</div>
		<!-- /.card -->
		<?php   } ?>
		<?php } ?>
		<?php // End of modification Maintenance Mode, by Masino Sinaga, May 12, 2012 \\ ?>
<?php } ?>
<?= $content ?>
<?php if (@!$SkipHeaderFooter) { ?>
        </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- ** Note: Only licensed users are allowed to change the copyright statement. ** -->
        <div class="ew-footer-text"><?= $Language->projectPhrase("FooterText") ?>&nbsp;|&nbsp;<span class="msTimer"></span>
		<div class="float-end d-none d-sm-inline">
		<?php if (MS_SHOW_ABOUT_US_ON_FOOTER) { ?> 
		&nbsp;|&nbsp;<a href='javascript:void(0);' class="kt-footer__menu-link kt-link" id='aboutus' onclick='getAboutUs();' title='<?php echo $Language->phrase("AboutUsTitle"); ?>'><?php echo $Language->phrase("AboutUsTitle"); ?></a> 
		<?php } ?>
		<?php if (MS_SHOW_TERMS_CONDITIONS_ON_FOOTER) { ?> 
		&nbsp;|&nbsp;<a href='javascript:void(0);' class="kt-footer__menu-link kt-link" id='termsandcond' onclick='getTermsConditions();' title='<?php echo $Language->phrase("TermsConditionsTitle"); ?>'><?php echo $Language->phrase("TermsConditionsTitle"); ?></a> 
		<?php } ?>
		</div>
		</div>
        <div class="float-end d-none d-sm-inline"></div>
    </footer>
	<span class="scrollTop">
	  <svg height="40" width="40" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
		<path id="scrolltop-bg" d="M0 0h48v48h-48z"></path>
		<path id="scrolltop-arrow" d="M14.83 30.83l9.17-9.17 9.17 9.17 2.83-2.83-12-12-12 12z"></path>
	  </svg>
	</span>
    <!-- Page Foot -->
</div>
<!-- ./wrapper -->
<?php } ?>
<script<?= Nonce() ?>>
loadjs.done("wrapper");
</script>
<!-- template upload (for file upload) -->
<script id="template-upload" type="text/html">
{{for files}}
    <tr class="template-upload">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{{:name}}</p>
            <p class="error"></p>
        </td>
        <td>
            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar text-bg-success" style="width: 0%;"></div></div>
        </td>
        <td>
            {{if !#index && !~root.options.autoUpload}}
            <button type="button" class="btn btn-default btn-sm start" disabled><?= $Language->phrase("UploadStart") ?></button>
            {{/if}}
            {{if !#index}}
            <button type="button" class="btn btn-default btn-sm cancel"><?= $Language->phrase("UploadCancel") ?></button>
            {{/if}}
        </td>
    </tr>
{{/for}}
</script>
<!-- template download (for file upload) -->
<script id="template-download" type="text/html">
{{for files}}
    <tr class="template-download">
        <td>
            <span class="preview">
                {{if !exists}}
                <span class="error"><?= $Language->phrase("FileNotFound") ?></span>
                {{else url && extension == "pdf"}}
                <div class="ew-pdfobject" data-url="{{>url}}" style="width: <?= Config("UPLOAD_THUMBNAIL_WIDTH") ?>px;"></div>
                {{else url && extension == "mp3"}}
                <audio controls><source type="audio/mpeg" src="{{>url}}"></audio>
                {{else url && extension == "mp4"}}
                <video controls><source type="video/mp4" src="{{>url}}"></video>
                {{else thumbnailUrl}}
                <a href="{{>url}}" title="{{>name}}" download="{{>name}}" class="ew-lightbox"><img class="ew-lazy" loading="lazy" src="{{>thumbnailUrl}}"></a>
                {{/if}}
            </span>
        </td>
        <td>
            <p class="name">
                {{if !exists}}
                <span class="text-body-secondary">{{:name}}</span>
                {{else url && (extension == "pdf" || thumbnailUrl) && extension != "mp3" && extension != "mp4"}}
                <a href="{{>url}}" title="{{>name}}" data-extension="{{>extension}}" target="_blank">{{:name}}</a>
                {{else url}}
                <a href="{{>url}}" title="{{>name}}" data-extension="{{>extension}}" download="{{>name}}">{{:name}}</a>
                {{else}}
                <span>{{:name}}</span>
                {{/if}}
            </p>
            {{if error}}
            <div><span class="error">{{:error}}</span></div>
            {{/if}}
        </td>
        <td>
            <span class="size">{{:~root.formatFileSize(size)}}</span>
        </td>
        <td>
            {{if !~root.options.readonly && deleteUrl}}
            <button type="button" class="btn btn-default btn-sm delete" data-type="{{>deleteType}}" data-url="{{>deleteUrl}}"><?= $Language->phrase("UploadDelete") ?></button>
            {{else !~root.options.readonly}}
            <button type="button" class="btn btn-default btn-sm cancel"><?= $Language->phrase("UploadCancel") ?></button>
            {{/if}}
        </td>
    </tr>
{{/for}}
</script>
<!-- modal dialog -->
<div id="ew-modal-dialog" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ew-modal-dialog-title" aria-hidden="true"><div class="modal-dialog modal-fullscreen-sm-down"><div class="modal-content"><div class="modal-header"><h5 id="ew-modal-dialog-title" class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $Language->phrase("CloseBtn", true) ?>"></button></div><div class="modal-body"></div><div class="modal-footer"></div></div></div></div>
<!-- image cropper dialog -->
<div id="ew-cropper-dialog" class="modal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="ew-cropper-dialog-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="ew-cropper-dialog-title" class="modal-title"><?= $Language->phrase("Crop") ?></h5>
            </div>
            <div class="modal-body">
                <div id="ew-crop-image-container"><img id="ew-crop-image" src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary ew-crop-btn"><?= $Language->phrase("Crop") ?></button>
                <button type="button" class="btn btn-default ew-skip-btn" data-bs-dismiss="modal"><?= $Language->phrase("Skip") ?></button>
            </div>
        </div>
    </div>
</div>
<!-- tooltip -->
<div id="ew-tooltip"></div>
<!-- drill down -->
<div id="ew-drilldown-panel"></div>
<script<?= Nonce() ?>>

function getHelp(strPage){$.ajax({url: ew.PATH_BASE + "loadhelponline",type: "GET",data: "page="+strPage,success: function (responseText) {var arrResponseText = responseText.split("~~~", 2); alertify.alert(arrResponseText[1]).set('title',arrResponseText[0]).set('modal',true).setting({'resizable':true,'maximizable':true}).resizeTo(340,500).set('onok',function(closeEvent){alertify.alert().destroy();});}});}function getAboutUs(){$.ajax({url: ew.PATH_BASE + "loadaboutus",type: "GET",success: function (responseText) {alertify.alert(responseText).set('title','<?php echo $Language->phrase("AboutUsTitle") ?>').set('modal',true).setting({'resizable':true,'maximizable':true}).resizeTo(340,480).set('onok',function(closeEvent){alertify.alert().destroy();});}});}function getTermsConditions(){$.ajax({url: ew.PATH_BASE + "loadtermsconditions",type: "GET",success: function (responseText) {alertify.alert(responseText).set('title','<?php echo $Language->phrase("TermsConditionsTitle") ?>').set('modal',true).setting({'resizable':true,'maximizable':true}).resizeTo(340,500).set('onok',function(closeEvent){alertify.alert().destroy();});}});}
</script>
<script<?= Nonce() ?>>
loadjs.done("wrapper");
loadjs.ready(ew.bundleIds, function() {
  if (!loadjs.isDefined("foot")) loadjs.done("foot");	
  if (window.innerWidth <= 990 || $(window).width() <= 990)
     $('[data-widget="pushmenu"]').PushMenu("collapse");
  window.onresize = SidebarHandling;

  function SidebarHandling() {
    if (window.innerWidth <= 990 || $(window).width() <= 990)
      $('[data-widget="pushmenu"]').PushMenu("collapse");
  }
  <?php if (Language()->Type == "SELECT") { ?>
  loadjs.ready("msdropdown", function(){ $("#ew-language").msDropdown();});
  <?php } ?>
  <?php 
  global $SkipHeaderFooter;
  if ($SkipHeaderFooter == false) {
  ?>
  loadjs.ready("jscookie", function() {
  const currentTheme = ew.Cookies.get('theme');
  if (currentTheme == "dark") {
	  var osInstance = OverlayScrollbars(document.getElementById("my-sidebar"), {className:"os-theme-light", callbacks: {	onScrollStop: function(e) {const scrollInfo = osInstance.scroll();	const topPosition = e.target.scrollTop;	var expires = new Date(new Date().getTime() + 525600 * 60 * 1000); ew.Cookies.set(ew.PROJECT_NAME + '_menuTopPosition', topPosition, { path: '/', expires: expires });	}}}); var menuTopPosition = ew.Cookies.get(ew.PROJECT_NAME + '_menuTopPosition') > 0 ? ew.Cookies.get(ew.PROJECT_NAME + '_menuTopPosition') : "0"; osInstance.scroll({ y: menuTopPosition }, 900); document.addEventListener("scroll", handleScroll);var scrollTop = document.querySelector(".scrollTop");
  } else {
	  var osInstance = OverlayScrollbars(document.getElementById("my-sidebar"), {className:"os-theme-dark", callbacks: {	onScrollStop: function(e) {const scrollInfo = osInstance.scroll();	const topPosition = e.target.scrollTop;	var expires = new Date(new Date().getTime() + 525600 * 60 * 1000); ew.Cookies.set(ew.PROJECT_NAME + '_menuTopPosition', topPosition, { path: '/', expires: expires });	}}}); var menuTopPosition = ew.Cookies.get(ew.PROJECT_NAME + '_menuTopPosition') > 0 ? ew.Cookies.get(ew.PROJECT_NAME + '_menuTopPosition') : "0"; osInstance.scroll({ y: menuTopPosition }, 900); document.addEventListener("scroll", handleScroll);var scrollTop = document.querySelector(".scrollTop");
  }

  function handleScroll() { var scrollableHeight = document.documentElement.scrollHeight -document.documentElement.clientHeight; var golden_ratio = 0.4; if ((document.documentElement.scrollTop / scrollableHeight ) > golden_ratio) { scrollTop.style.display = "block"; } else { scrollTop.style.display = "none"; }}scrollTop.addEventListener("click", scrollToTop); function scrollToTop() { window.scrollTo({ top: 0, behavior: "smooth"});}
  const toggleTheme = document.querySelector('.theme-switch input[type="checkbox"]');
  });
  <?php
  }
  ?>
});
</script>
<?= DebugBarRenderer()?->render() ?>
</body>
</html>
