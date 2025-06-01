<?php

namespace PHPMaker2025\ucarsip;

use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\App;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemException;
use Closure;
use DateTime;
use DateTimeImmutable;
use DateInterval;
use Exception;
use InvalidArgumentException;

/**
 * Page class
 */
class SettingsAdd extends Settings
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "add";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "SettingsAdd";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "settingsadd";

    // Page headings
    public string $Heading = "";
    public string $Subheading = "";
    public string $PageHeader = "";
    public string $PageFooter = "";

    // Page layout
    public bool $UseLayout = true;

    // Page terminated
    private bool $terminated = false;

    // Page heading
    public function pageHeading(): string
    {
        if ($this->Heading != "") {
            return $this->Heading;
        }
        if (method_exists($this, "tableCaption")) {
            return $this->tableCaption();
        }
        return "";
    }

    // Page subheading
    public function pageSubheading(): string
    {
        if ($this->Subheading != "") {
            return $this->Subheading;
        }
        if ($this->TableName) {
            return Language()->phrase($this->PageID);
        }
        return "";
    }

    // Page name
    public function pageName(): string
    {
        return CurrentPageName();
    }

    // Page URL
    public function pageUrl(bool $withArgs = true): string
    {
        $route = GetRoute();
        $args = RemoveXss($route->getArguments());
        if (!$withArgs) {
            foreach ($args as $key => &$val) {
                $val = "";
            }
            unset($val);
        }
        return rtrim(UrlFor($route->getName(), $args), "/") . "?";
    }

    // Show Page Header
    public function showPageHeader(): void
    {
        $header = $this->PageHeader;
        $this->pageDataRendering($header);
        if ($header != "") { // Header exists, display
            echo '<div id="ew-page-header">' . $header . '</div>';
        }
    }

    // Show Page Footer
    public function showPageFooter(): void
    {
        $footer = $this->PageFooter;
        $this->pageDataRendered($footer);
        if ($footer != "") { // Footer exists, display
            echo '<div id="ew-page-footer">' . $footer . '</div>';
        }
    }

    // Set field visibility
    public function setVisibility(): void
    {
        $this->Option_ID->Visible = false;
        $this->Option_Default->setVisibility();
        $this->Show_Announcement->setVisibility();
        $this->Use_Announcement_Table->setVisibility();
        $this->Maintenance_Mode->setVisibility();
        $this->Maintenance_Finish_DateTime->setVisibility();
        $this->Auto_Normal_After_Maintenance->setVisibility();
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'settings';
        $this->TableName = 'settings';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-add-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (settings)
        if (!isset($GLOBALS["settings"]) || $GLOBALS["settings"]::class == PROJECT_NAMESPACE . "settings") {
            $GLOBALS["settings"] = &$this;
        }

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'settings');
        }

        // Open connection
        $GLOBALS["Conn"] ??= $this->getConnection();
    }

    // Is lookup
    public function isLookup(): bool
    {
        return SameText(Route(0), Config("API_LOOKUP_ACTION"));
    }

    // Is AutoFill
    public function isAutoFill(): bool
    {
        return $this->isLookup() && SameText(Post("ajax"), "autofill");
    }

    // Is AutoSuggest
    public function isAutoSuggest(): bool
    {
        return $this->isLookup() && SameText(Post("ajax"), "autosuggest");
    }

    // Is modal lookup
    public function isModalLookup(): bool
    {
        return $this->isLookup() && SameText(Post("ajax"), "modal");
    }

    // Is terminated
    public function isTerminated(): bool
    {
        return $this->terminated;
    }

    /**
     * Terminate page
     *
     * @param string|bool $url URL for direction, true => show response for API
     * @return void
     */
    public function terminate(string|bool $url = ""): void
    {
        if ($this->terminated) {
            return;
        }
        global $TempImages, $DashboardReport, $Response;

        // Page is terminated
        $this->terminated = true;

        // Page Unload event
        if (method_exists($this, "pageUnload")) {
            $this->pageUnload();
        }
        DispatchEvent(new PageUnloadedEvent($this), PageUnloadedEvent::NAME);
        if (!IsApi() && method_exists($this, "pageRedirecting")) {
            $this->pageRedirecting($url);
        }

        // Return for API
        if (IsApi()) {
            $res = $url === true;
            if (!$res) { // Show response for API
                $ar = array_merge($this->getMessages(), $url ? ["url" => GetUrl($url)] : []);
                WriteJson($ar);
            }
            $this->clearMessages(); // Clear messages for API request
            return;
        } else { // Check if response is JSON
            if (HasJsonResponse()) { // Has JSON response
                $this->clearMessages();
                return;
            }
        }

        // Go to URL if specified
        if ($url != "") {
            if (!IsDebug() && ob_get_length()) {
                ob_end_clean();
            }

            // Handle modal response
            if ($this->IsModal) { // Show as modal
                $pageName = GetPageName($url);
                $result = ["url" => GetUrl($url), "modal" => "1"];  // Assume return to modal for simplicity
                if (
                    SameString($pageName, GetPageName($this->getListUrl()))
                    || SameString($pageName, GetPageName($this->getViewUrl()))
                    || SameString($pageName, GetPageName(CurrentMasterTable()?->getViewUrl() ?? ""))
                ) { // List / View / Master View page
                    if (!SameString($pageName, GetPageName($this->getListUrl()))) { // Not List page
                        $result["caption"] = $this->getModalCaption($pageName);
                        $result["view"] = SameString($pageName, "settingsview"); // If View page, no primary button
                    } else { // List page
                        $result["error"] = $this->getFailureMessage(); // List page should not be shown as modal => error
                    }
                } else { // Other pages (add messages and then clear messages)
                    $result = array_merge($this->getMessages(), ["modal" => "1"]);
                    $this->clearMessages();
                }
                WriteJson($result);
            } else {
                Redirect(GetUrl($url));
            }
        }
        return; // Return to controller
    }

    // Get records from result set
    protected function getRecordsFromResult(Result|array $result, bool $current = false): array
    {
        $rows = [];
        if ($result instanceof Result) { // Result
            while ($row = $result->fetchAssociative()) {
                $this->loadRowValues($row); // Set up DbValue/CurrentValue
                $row = $this->getRecordFromArray($row);
                if ($current) {
                    return $row;
                } else {
                    $rows[] = $row;
                }
            }
        } elseif (is_array($result)) {
            foreach ($result as $ar) {
                $row = $this->getRecordFromArray($ar);
                if ($current) {
                    return $row;
                } else {
                    $rows[] = $row;
                }
            }
        }
        return $rows;
    }

    // Get record from array
    protected function getRecordFromArray(array $ar): array
    {
        $row = [];
        if (is_array($ar)) {
            foreach ($ar as $fldname => $val) {
                if (isset($this->Fields[$fldname]) && ($this->Fields[$fldname]->Visible || $this->Fields[$fldname]->IsPrimaryKey)) { // Primary key or Visible
                    $fld = &$this->Fields[$fldname];
                    if ($fld->HtmlTag == "FILE") { // Upload field
                        if (IsEmpty($val)) {
                            $row[$fldname] = null;
                        } else {
                            if ($fld->DataType == DataType::BLOB) {
                                $url = FullUrl(GetApiUrl(Config("API_FILE_ACTION") .
                                    "/" . $fld->TableVar . "/" . $fld->Param . "/" . rawurlencode($this->getRecordKeyValue($ar))));
                                $row[$fldname] = ["type" => ContentType($val), "url" => $url, "name" => $fld->Param . ContentExtension($val)];
                            } elseif (!$fld->UploadMultiple || !ContainsString($val, Config("MULTIPLE_UPLOAD_SEPARATOR"))) { // Single file
                                $url = FullUrl(GetApiUrl(Config("API_FILE_ACTION") .
                                    "/" . $fld->TableVar . "/" . Encrypt($fld->uploadPath() . $val)));
                                $row[$fldname] = ["type" => MimeContentType($val), "url" => $url, "name" => $val];
                            } else { // Multiple files
                                $files = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $val);
                                $ar = [];
                                foreach ($files as $file) {
                                    $url = FullUrl(GetApiUrl(Config("API_FILE_ACTION") .
                                        "/" . $fld->TableVar . "/" . Encrypt($fld->uploadPath() . $file)));
                                    if (!IsEmpty($file)) {
                                        $ar[] = ["type" => MimeContentType($file), "url" => $url, "name" => $file];
                                    }
                                }
                                $row[$fldname] = $ar;
                            }
                        }
                    } else {
                        $row[$fldname] = $val;
                    }
                }
            }
        }
        return $row;
    }

    // Get record key value from array
    protected function getRecordKeyValue(array $ar): string
    {
        $key = "";
        if (is_array($ar)) {
            $key .= @$ar['Option_ID'];
        }
        return $key;
    }

    /**
     * Hide fields for add/edit
     *
     * @return void
     */
    protected function hideFieldsForAddEdit(): void
    {
        if ($this->isAdd() || $this->isCopy() || $this->isGridAdd()) {
            $this->Option_ID->Visible = false;
        }
    }

    // Lookup data
    public function lookup(array $req = [], bool $response = true): array|bool
    {
        // Get lookup object
        $fieldName = $req["field"] ?? null;
        if (!$fieldName) {
            return [];
        }
        $fld = $this->Fields[$fieldName];
        $lookup = $fld->Lookup;
        $name = $req["name"] ?? "";
        if (ContainsString($name, "query_builder_rule")) {
            $lookup->FilterFields = []; // Skip parent fields if any
        }

        // Get lookup parameters
        $lookupType = $req["ajax"] ?? "unknown";
        $pageSize = -1;
        $offset = -1;
        $searchValue = "";
        if (SameText($lookupType, "modal") || SameText($lookupType, "filter")) {
            $searchValue = $req["q"] ?? $req["sv"] ?? "";
            $pageSize = $req["n"] ?? $req["recperpage"] ?? 10;
        } elseif (SameText($lookupType, "autosuggest")) {
            $searchValue = $req["q"] ?? "";
            $pageSize = $req["n"] ?? -1;
            $pageSize = is_numeric($pageSize) ? (int)$pageSize : -1;
            if ($pageSize <= 0) {
                $pageSize = Config("AUTO_SUGGEST_MAX_ENTRIES");
            }
        }
        $start = $req["start"] ?? -1;
        $start = is_numeric($start) ? (int)$start : -1;
        $page = $req["page"] ?? -1;
        $page = is_numeric($page) ? (int)$page : -1;
        $offset = $start >= 0 ? $start : ($page > 0 && $pageSize > 0 ? ($page - 1) * $pageSize : 0);
        $userSelect = Decrypt($req["s"] ?? "");
        $userFilter = Decrypt($req["f"] ?? "");
        $userOrderBy = Decrypt($req["o"] ?? "");
        $keys = $req["keys"] ?? null;
        $lookup->LookupType = $lookupType; // Lookup type
        $lookup->FilterValues = []; // Clear filter values first
        if ($keys !== null) { // Selected records from modal
            if (is_array($keys)) {
                $keys = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $keys);
            }
            $lookup->FilterFields = []; // Skip parent fields if any
            $lookup->FilterValues[] = $keys; // Lookup values
            $pageSize = -1; // Show all records
        } else { // Lookup values
            $lookup->FilterValues[] = $req["v0"] ?? $req["lookupValue"] ?? "";
        }
        $cnt = is_array($lookup->FilterFields) ? count($lookup->FilterFields) : 0;
        for ($i = 1; $i <= $cnt; $i++) {
            $lookup->FilterValues[] = $req["v" . $i] ?? "";
        }
        $lookup->SearchValue = $searchValue;
        $lookup->PageSize = $pageSize;
        $lookup->Offset = $offset;
        if ($userSelect != "") {
            $lookup->UserSelect = $userSelect;
        }
        if ($userFilter != "") {
            $lookup->UserFilter = $userFilter;
        }
        if ($userOrderBy != "") {
            $lookup->UserOrderBy = $userOrderBy;
        }
        return $lookup->toJson($this, $response); // Use settings from current page
    }
    public string $FormClassName = "ew-form ew-add-form";
    public bool $IsModal = false;
    public bool $IsMobileOrModal = false;
    public ?string $DbMasterFilter = "";
    public string $DbDetailFilter = "";
    public int $StartRecord = 0;
    public int $Priv = 0;
    public bool $CopyRecord = false;

    /**
     * Page run
     *
     * @return void
     */
    public function run(): void
    {
        global $ExportType, $SkipHeaderFooter;

// Is modal
        $this->IsModal = IsModal();
        $this->UseLayout = $this->UseLayout && !$this->IsModal;

        // Use layout
        $this->UseLayout = $this->UseLayout && ConvertToBool(Param(Config("PAGE_LAYOUT"), true));

        // View
        $this->View = Get(Config("VIEW"));
        $this->CurrentAction = Param("action"); // Set up current action
        $this->setVisibility();

        // Set lookup cache
        if (!in_array($this->PageID, Config("LOOKUP_CACHE_PAGE_IDS"))) {
            $this->setUseLookupCache(false);
        }

		// Call this new function from userfn*.php file
		My_Global_Check(); // Modified by Masino Sinaga, September 10, 2023

        // Global Page Loading event (in userfn*.php)
        DispatchEvent(new PageLoadingEvent($this), PageLoadingEvent::NAME);

        // Page Load event
        if (method_exists($this, "pageLoad")) {
            $this->pageLoad();
        }

        // Hide fields for add/edit
        if (!$this->UseAjaxActions) {
            $this->hideFieldsForAddEdit();
        }
        // Use inline delete
        if ($this->UseAjaxActions) {
            $this->InlineDelete = true;
        }

		// Begin of Compare Root URL by Masino Sinaga, September 10, 2023
		if (MS_ALWAYS_COMPARE_ROOT_URL == TRUE) {
			if (isset($_SESSION['ucarsip_Root_URL'])) {
				if ($_SESSION['ucarsip_Root_URL'] == MS_OTHER_COMPARED_ROOT_URL && $_SESSION['ucarsip_Root_URL'] <> "") {
					$this->setFailureMessage(str_replace("%s", MS_OTHER_COMPARED_ROOT_URL, Container("language")->phrase("NoPermission")));
					header("Location: " . $_SESSION['ucarsip_Root_URL']);
				}
			}
		}
		// End of Compare Root URL by Masino Sinaga, September 10, 2023

        // Set up lookup cache
        $this->setupLookupOptions($this->Option_Default);
        $this->setupLookupOptions($this->Show_Announcement);
        $this->setupLookupOptions($this->Use_Announcement_Table);
        $this->setupLookupOptions($this->Maintenance_Mode);
        $this->setupLookupOptions($this->Auto_Normal_After_Maintenance);

        // Load default values for add
        $this->loadDefaultValues();

        // Check modal
        if ($this->IsModal) {
            $SkipHeaderFooter = true;
        }
        $this->IsMobileOrModal = IsMobile() || $this->IsModal;
        $postBack = false;

        // Set up current action
        if (IsApi()) {
            $this->CurrentAction = "insert"; // Add record directly
            $postBack = true;
        } elseif (Post("action", "") !== "") {
            $this->CurrentAction = Post("action"); // Get form action
            $this->setKey($this->getOldKey());
            $postBack = true;
        } else {
            // Load key values from QueryString
            if (($keyValue = Get("Option_ID") ?? Route("Option_ID")) !== null) {
                $this->Option_ID->setQueryStringValue($keyValue);
            }
            $this->OldKey = $this->getKey(true); // Get from CurrentValue
            $this->CopyRecord = !IsEmpty($this->OldKey);
            if ($this->CopyRecord) {
                $this->CurrentAction = "copy"; // Copy record
                $this->setKey($this->OldKey); // Set up record key
            } else {
                $this->CurrentAction = "show"; // Display blank record
            }
        }

        // Load old record or default values
        $oldRow = $this->loadOldRecord();

        // Load form values
        if ($postBack) {
            $this->loadFormValues(); // Load form values
        }

        // Validate form if post back
        if ($postBack) {
            if (!$this->validateForm()) {
                $this->EventCancelled = true; // Event cancelled
                $this->restoreFormValues(); // Restore form values
                if (IsApi()) {
                    $this->terminate();
                    return;
                } else {
                    $this->CurrentAction = "show"; // Form error, reset action
                }
            }
        }

        // Perform current action
        switch ($this->CurrentAction) {
            case "copy": // Copy an existing record
                if (!$oldRow) { // Record not loaded
                    if (!$this->peekFailureMessage()) {
                        $this->setFailureMessage($this->language->phrase("NoRecord")); // No record found
                    }
                    $this->terminate("settingslist"); // No matching record, return to list
                    return;
                }
                break;
            case "insert": // Add new record
                if ($this->addRow($oldRow)) { // Add successful
                    CleanUploadTempPaths(SessionId());
                    if (!$this->peekSuccessMessage() && Post("addopt") != "1") { // Skip success message for addopt (done in JavaScript)
                        $this->setSuccessMessage($this->language->phrase("AddSuccess")); // Set up success message
                    }
                    $returnUrl = $this->getReturnUrl();
                    if (GetPageName($returnUrl) == "settingslist") {
                        $returnUrl = $this->addMasterUrl($returnUrl); // List page, return to List page with correct master key if necessary
                    } elseif (GetPageName($returnUrl) == "settingsview") {
                        $returnUrl = $this->getViewUrl(); // View page, return to View page with keyurl directly
                    }

                    // Handle UseAjaxActions
                    if ($this->IsModal && $this->UseAjaxActions) {
                        $this->IsModal = false;
                        if (GetPageName($returnUrl) != "settingslist") {
                            FlashBag()->add("Return-Url", $returnUrl); // Save return URL
                            $returnUrl = "settingslist"; // Return list page content
                        }
                    }
                    if (IsJsonResponse()) { // Return to caller
                        $this->terminate(true);
                        return;
                    } else {
                        $this->terminate($returnUrl);
                        return;
                    }
                } elseif (IsApi()) { // API request, return
                    $this->terminate();
                    return;
                } elseif ($this->IsModal && $this->UseAjaxActions) { // Return JSON error message
                    WriteJson(["success" => false, "validation" => $this->getValidationErrors(), "error" => $this->getFailureMessage()]);
                    $this->terminate();
                    return;
                } else {
                    $this->EventCancelled = true; // Event cancelled
                    $this->restoreFormValues(); // Add failed, restore form values
                }
        }

        // Set up Breadcrumb
        $this->setupBreadcrumb();

        // Render row based on row type
        $this->RowType = RowType::ADD; // Render add type

        // Render row
        $this->resetAttributes();
        $this->renderRow();

        // Set LoginStatus / Page_Rendering / Page_Render
        if (!IsApi() && !$this->isTerminated()) {
            // Setup login status
            SetupLoginStatus();

            // Pass login status to client side
            SetClientVar("login", LoginStatus());

            // Global Page Rendering event (in userfn*.php)
            DispatchEvent(new PageRenderingEvent($this), PageRenderingEvent::NAME);

            // Page Render event
            if (method_exists($this, "pageRender")) {
                $this->pageRender();
            }

            // Render search option
            if (method_exists($this, "renderSearchOptions")) {
                $this->renderSearchOptions();
            }
        }
    }

// Get upload files
    protected function getUploadFiles(): void
    {
    }

    // Load default values
    protected function loadDefaultValues(): void
    {
        $this->Option_Default->DefaultValue = $this->Option_Default->getDefault(); // PHP
        $this->Option_Default->OldValue = $this->Option_Default->DefaultValue;
        $this->Show_Announcement->DefaultValue = $this->Show_Announcement->getDefault(); // PHP
        $this->Show_Announcement->OldValue = $this->Show_Announcement->DefaultValue;
        $this->Use_Announcement_Table->DefaultValue = $this->Use_Announcement_Table->getDefault(); // PHP
        $this->Use_Announcement_Table->OldValue = $this->Use_Announcement_Table->DefaultValue;
        $this->Maintenance_Mode->DefaultValue = $this->Maintenance_Mode->getDefault(); // PHP
        $this->Maintenance_Mode->OldValue = $this->Maintenance_Mode->DefaultValue;
        $this->Auto_Normal_After_Maintenance->DefaultValue = $this->Auto_Normal_After_Maintenance->getDefault(); // PHP
        $this->Auto_Normal_After_Maintenance->OldValue = $this->Auto_Normal_After_Maintenance->DefaultValue;
    }

    // Load form values
    protected function loadFormValues(): void
    {
        $validate = !Config("SERVER_VALIDATE");

        // Check field name 'Option_Default' before field var 'x_Option_Default'
        $val = $this->getFormValue("Option_Default", null) ?? $this->getFormValue("x_Option_Default", null);
        if (!$this->Option_Default->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Option_Default->Visible = false; // Disable update for API request
            } else {
                $this->Option_Default->setFormValue($val);
            }
        }

        // Check field name 'Show_Announcement' before field var 'x_Show_Announcement'
        $val = $this->getFormValue("Show_Announcement", null) ?? $this->getFormValue("x_Show_Announcement", null);
        if (!$this->Show_Announcement->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Show_Announcement->Visible = false; // Disable update for API request
            } else {
                $this->Show_Announcement->setFormValue($val);
            }
        }

        // Check field name 'Use_Announcement_Table' before field var 'x_Use_Announcement_Table'
        $val = $this->getFormValue("Use_Announcement_Table", null) ?? $this->getFormValue("x_Use_Announcement_Table", null);
        if (!$this->Use_Announcement_Table->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Use_Announcement_Table->Visible = false; // Disable update for API request
            } else {
                $this->Use_Announcement_Table->setFormValue($val);
            }
        }

        // Check field name 'Maintenance_Mode' before field var 'x_Maintenance_Mode'
        $val = $this->getFormValue("Maintenance_Mode", null) ?? $this->getFormValue("x_Maintenance_Mode", null);
        if (!$this->Maintenance_Mode->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Maintenance_Mode->Visible = false; // Disable update for API request
            } else {
                $this->Maintenance_Mode->setFormValue($val);
            }
        }

        // Check field name 'Maintenance_Finish_DateTime' before field var 'x_Maintenance_Finish_DateTime'
        $val = $this->getFormValue("Maintenance_Finish_DateTime", null) ?? $this->getFormValue("x_Maintenance_Finish_DateTime", null);
        if (!$this->Maintenance_Finish_DateTime->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Maintenance_Finish_DateTime->Visible = false; // Disable update for API request
            } else {
                $this->Maintenance_Finish_DateTime->setFormValue($val, true, $validate);
            }
            $this->Maintenance_Finish_DateTime->CurrentValue = UnformatDateTime($this->Maintenance_Finish_DateTime->CurrentValue, $this->Maintenance_Finish_DateTime->formatPattern());
        }

        // Check field name 'Auto_Normal_After_Maintenance' before field var 'x_Auto_Normal_After_Maintenance'
        $val = $this->getFormValue("Auto_Normal_After_Maintenance", null) ?? $this->getFormValue("x_Auto_Normal_After_Maintenance", null);
        if (!$this->Auto_Normal_After_Maintenance->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Auto_Normal_After_Maintenance->Visible = false; // Disable update for API request
            } else {
                $this->Auto_Normal_After_Maintenance->setFormValue($val);
            }
        }

        // Check field name 'Option_ID' first before field var 'x_Option_ID'
        $val = $this->hasFormValue("Option_ID") ? $this->getFormValue("Option_ID") : $this->getFormValue("x_Option_ID");
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        $this->Option_Default->CurrentValue = $this->Option_Default->FormValue;
        $this->Show_Announcement->CurrentValue = $this->Show_Announcement->FormValue;
        $this->Use_Announcement_Table->CurrentValue = $this->Use_Announcement_Table->FormValue;
        $this->Maintenance_Mode->CurrentValue = $this->Maintenance_Mode->FormValue;
        $this->Maintenance_Finish_DateTime->CurrentValue = $this->Maintenance_Finish_DateTime->FormValue;
        $this->Maintenance_Finish_DateTime->CurrentValue = UnformatDateTime($this->Maintenance_Finish_DateTime->CurrentValue, $this->Maintenance_Finish_DateTime->formatPattern());
        $this->Auto_Normal_After_Maintenance->CurrentValue = $this->Auto_Normal_After_Maintenance->FormValue;
    }

    /**
     * Load row based on key values
     *
     * @return bool
     */
    public function loadRow(): bool
    {
        $filter = $this->getRecordFilter();

        // Call Row Selecting event
        $this->rowSelecting($filter);

        // Load SQL based on filter
        $this->CurrentFilter = $filter;
        $sql = $this->getCurrentSql();
        $conn = $this->getConnection();
        $res = false;
        $row = $conn->fetchAssociative($sql);
        if ($row) {
            $res = true;
            $this->loadRowValues($row); // Load row values
        }
        return $res;
    }

    /**
     * Load row values from result set or record
     *
     * @param array|bool|null $row Record
     * @return void
     */
    public function loadRowValues(array|bool|null $row = null): void
    {
        $row = is_array($row) ? $row : $this->newRow();

        // Call Row Selected event
        $this->rowSelected($row);
        $this->Option_ID->setDbValue($row['Option_ID']);
        $this->Option_Default->setDbValue($row['Option_Default']);
        $this->Show_Announcement->setDbValue($row['Show_Announcement']);
        $this->Use_Announcement_Table->setDbValue($row['Use_Announcement_Table']);
        $this->Maintenance_Mode->setDbValue($row['Maintenance_Mode']);
        $this->Maintenance_Finish_DateTime->setDbValue($row['Maintenance_Finish_DateTime']);
        $this->Auto_Normal_After_Maintenance->setDbValue($row['Auto_Normal_After_Maintenance']);
    }

    // Return a row with default values
    protected function newRow(): array
    {
        $row = [];
        $row['Option_ID'] = $this->Option_ID->DefaultValue;
        $row['Option_Default'] = $this->Option_Default->DefaultValue;
        $row['Show_Announcement'] = $this->Show_Announcement->DefaultValue;
        $row['Use_Announcement_Table'] = $this->Use_Announcement_Table->DefaultValue;
        $row['Maintenance_Mode'] = $this->Maintenance_Mode->DefaultValue;
        $row['Maintenance_Finish_DateTime'] = $this->Maintenance_Finish_DateTime->DefaultValue;
        $row['Auto_Normal_After_Maintenance'] = $this->Auto_Normal_After_Maintenance->DefaultValue;
        return $row;
    }

    // Load old record
    protected function loadOldRecord(): ?array
    {
        // Load old record
        if ($this->OldKey != "") {
            $this->setKey($this->OldKey);
            $this->CurrentFilter = $this->getRecordFilter();
            $sql = $this->getCurrentSql();
            $conn = $this->getConnection();
            $result = ExecuteQuery($sql, $conn);
            if ($row = $result->fetchAssociative()) {
                $this->loadRowValues($row); // Load row values
                return $row;
            }
        }
        $this->loadRowValues(); // Load default row values
        return null;
    }

    // Render row values based on field settings
    public function renderRow(): void
    {
        global $CurrentLanguage;

        // Initialize URLs

        // Call Row_Rendering event
        $this->rowRendering();

        // Common render codes for all row types

        // Option_ID
        $this->Option_ID->RowCssClass = "row";

        // Option_Default
        $this->Option_Default->RowCssClass = "row";

        // Show_Announcement
        $this->Show_Announcement->RowCssClass = "row";

        // Use_Announcement_Table
        $this->Use_Announcement_Table->RowCssClass = "row";

        // Maintenance_Mode
        $this->Maintenance_Mode->RowCssClass = "row";

        // Maintenance_Finish_DateTime
        $this->Maintenance_Finish_DateTime->RowCssClass = "row";

        // Auto_Normal_After_Maintenance
        $this->Auto_Normal_After_Maintenance->RowCssClass = "row";

        // View row
        if ($this->RowType == RowType::VIEW) {
            // Option_ID
            $this->Option_ID->ViewValue = $this->Option_ID->CurrentValue;

            // Option_Default
            if (ConvertToBool($this->Option_Default->CurrentValue)) {
                $this->Option_Default->ViewValue = $this->Option_Default->tagCaption(1) != "" ? $this->Option_Default->tagCaption(1) : "Yes";
            } else {
                $this->Option_Default->ViewValue = $this->Option_Default->tagCaption(2) != "" ? $this->Option_Default->tagCaption(2) : "No";
            }

            // Show_Announcement
            if (ConvertToBool($this->Show_Announcement->CurrentValue)) {
                $this->Show_Announcement->ViewValue = $this->Show_Announcement->tagCaption(1) != "" ? $this->Show_Announcement->tagCaption(1) : "Yes";
            } else {
                $this->Show_Announcement->ViewValue = $this->Show_Announcement->tagCaption(2) != "" ? $this->Show_Announcement->tagCaption(2) : "No";
            }

            // Use_Announcement_Table
            if (ConvertToBool($this->Use_Announcement_Table->CurrentValue)) {
                $this->Use_Announcement_Table->ViewValue = $this->Use_Announcement_Table->tagCaption(2) != "" ? $this->Use_Announcement_Table->tagCaption(2) : "Yes";
            } else {
                $this->Use_Announcement_Table->ViewValue = $this->Use_Announcement_Table->tagCaption(1) != "" ? $this->Use_Announcement_Table->tagCaption(1) : "No";
            }

            // Maintenance_Mode
            if (ConvertToBool($this->Maintenance_Mode->CurrentValue)) {
                $this->Maintenance_Mode->ViewValue = $this->Maintenance_Mode->tagCaption(2) != "" ? $this->Maintenance_Mode->tagCaption(2) : "Yes";
            } else {
                $this->Maintenance_Mode->ViewValue = $this->Maintenance_Mode->tagCaption(1) != "" ? $this->Maintenance_Mode->tagCaption(1) : "No";
            }

            // Maintenance_Finish_DateTime
            $this->Maintenance_Finish_DateTime->ViewValue = $this->Maintenance_Finish_DateTime->CurrentValue;
            $this->Maintenance_Finish_DateTime->ViewValue = FormatDateTime($this->Maintenance_Finish_DateTime->ViewValue, $this->Maintenance_Finish_DateTime->formatPattern());

            // Auto_Normal_After_Maintenance
            if (ConvertToBool($this->Auto_Normal_After_Maintenance->CurrentValue)) {
                $this->Auto_Normal_After_Maintenance->ViewValue = $this->Auto_Normal_After_Maintenance->tagCaption(1) != "" ? $this->Auto_Normal_After_Maintenance->tagCaption(1) : "Yes";
            } else {
                $this->Auto_Normal_After_Maintenance->ViewValue = $this->Auto_Normal_After_Maintenance->tagCaption(2) != "" ? $this->Auto_Normal_After_Maintenance->tagCaption(2) : "No";
            }

            // Option_Default
            $this->Option_Default->HrefValue = "";

            // Show_Announcement
            $this->Show_Announcement->HrefValue = "";

            // Use_Announcement_Table
            $this->Use_Announcement_Table->HrefValue = "";

            // Maintenance_Mode
            $this->Maintenance_Mode->HrefValue = "";

            // Maintenance_Finish_DateTime
            $this->Maintenance_Finish_DateTime->HrefValue = "";

            // Auto_Normal_After_Maintenance
            $this->Auto_Normal_After_Maintenance->HrefValue = "";
        } elseif ($this->RowType == RowType::ADD) {
            // Option_Default
            $this->Option_Default->EditValue = $this->Option_Default->options(false);
            $this->Option_Default->PlaceHolder = RemoveHtml($this->Option_Default->caption());

            // Show_Announcement
            $this->Show_Announcement->EditValue = $this->Show_Announcement->options(false);
            $this->Show_Announcement->PlaceHolder = RemoveHtml($this->Show_Announcement->caption());

            // Use_Announcement_Table
            $this->Use_Announcement_Table->EditValue = $this->Use_Announcement_Table->options(false);
            $this->Use_Announcement_Table->PlaceHolder = RemoveHtml($this->Use_Announcement_Table->caption());

            // Maintenance_Mode
            $this->Maintenance_Mode->EditValue = $this->Maintenance_Mode->options(false);
            $this->Maintenance_Mode->PlaceHolder = RemoveHtml($this->Maintenance_Mode->caption());

            // Maintenance_Finish_DateTime
            $this->Maintenance_Finish_DateTime->setupEditAttributes();
            $this->Maintenance_Finish_DateTime->EditValue = FormatDateTime($this->Maintenance_Finish_DateTime->CurrentValue, $this->Maintenance_Finish_DateTime->formatPattern());
            $this->Maintenance_Finish_DateTime->PlaceHolder = RemoveHtml($this->Maintenance_Finish_DateTime->caption());

            // Auto_Normal_After_Maintenance
            $this->Auto_Normal_After_Maintenance->EditValue = $this->Auto_Normal_After_Maintenance->options(false);
            $this->Auto_Normal_After_Maintenance->PlaceHolder = RemoveHtml($this->Auto_Normal_After_Maintenance->caption());

            // Add refer script

            // Option_Default
            $this->Option_Default->HrefValue = "";

            // Show_Announcement
            $this->Show_Announcement->HrefValue = "";

            // Use_Announcement_Table
            $this->Use_Announcement_Table->HrefValue = "";

            // Maintenance_Mode
            $this->Maintenance_Mode->HrefValue = "";

            // Maintenance_Finish_DateTime
            $this->Maintenance_Finish_DateTime->HrefValue = "";

            // Auto_Normal_After_Maintenance
            $this->Auto_Normal_After_Maintenance->HrefValue = "";
        }
        if ($this->RowType == RowType::ADD || $this->RowType == RowType::EDIT || $this->RowType == RowType::SEARCH) { // Add/Edit/Search row
            $this->setupFieldTitles();
        }

        // Call Row Rendered event
        if ($this->RowType != RowType::AGGREGATEINIT) {
            $this->rowRendered();
        }
    }

    // Validate form
    protected function validateForm(): bool
    {
        // Check if validation required
        if (!Config("SERVER_VALIDATE")) {
            return true;
        }
        $validateForm = true;
            if ($this->Option_Default->Visible && $this->Option_Default->Required) {
                if ($this->Option_Default->FormValue == "") {
                    $this->Option_Default->addErrorMessage(str_replace("%s", $this->Option_Default->caption(), $this->Option_Default->RequiredErrorMessage));
                }
            }
            if ($this->Show_Announcement->Visible && $this->Show_Announcement->Required) {
                if ($this->Show_Announcement->FormValue == "") {
                    $this->Show_Announcement->addErrorMessage(str_replace("%s", $this->Show_Announcement->caption(), $this->Show_Announcement->RequiredErrorMessage));
                }
            }
            if ($this->Use_Announcement_Table->Visible && $this->Use_Announcement_Table->Required) {
                if ($this->Use_Announcement_Table->FormValue == "") {
                    $this->Use_Announcement_Table->addErrorMessage(str_replace("%s", $this->Use_Announcement_Table->caption(), $this->Use_Announcement_Table->RequiredErrorMessage));
                }
            }
            if ($this->Maintenance_Mode->Visible && $this->Maintenance_Mode->Required) {
                if ($this->Maintenance_Mode->FormValue == "") {
                    $this->Maintenance_Mode->addErrorMessage(str_replace("%s", $this->Maintenance_Mode->caption(), $this->Maintenance_Mode->RequiredErrorMessage));
                }
            }
            if ($this->Maintenance_Finish_DateTime->Visible && $this->Maintenance_Finish_DateTime->Required) {
                if (!$this->Maintenance_Finish_DateTime->IsDetailKey && IsEmpty($this->Maintenance_Finish_DateTime->FormValue)) {
                    $this->Maintenance_Finish_DateTime->addErrorMessage(str_replace("%s", $this->Maintenance_Finish_DateTime->caption(), $this->Maintenance_Finish_DateTime->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->Maintenance_Finish_DateTime->FormValue, $this->Maintenance_Finish_DateTime->formatPattern())) {
                $this->Maintenance_Finish_DateTime->addErrorMessage($this->Maintenance_Finish_DateTime->getErrorMessage(false));
            }
            if ($this->Auto_Normal_After_Maintenance->Visible && $this->Auto_Normal_After_Maintenance->Required) {
                if ($this->Auto_Normal_After_Maintenance->FormValue == "") {
                    $this->Auto_Normal_After_Maintenance->addErrorMessage(str_replace("%s", $this->Auto_Normal_After_Maintenance->caption(), $this->Auto_Normal_After_Maintenance->RequiredErrorMessage));
                }
            }

        // Return validate result
        $validateForm = $validateForm && !$this->hasInvalidFields();

        // Call Form_CustomValidate event
        $formCustomError = "";
        $validateForm = $validateForm && $this->formCustomValidate($formCustomError);
        if ($formCustomError != "") {
            $this->setFailureMessage($formCustomError);
        }
        return $validateForm;
    }

    // Add record
    protected function addRow(?array $oldRow = null): bool
    {
        // Get new row
        $newRow = $this->getAddRow();

        // Update current values
        $this->Fields->setCurrentValues($newRow);
        $conn = $this->getConnection();

        // Load db values from old row
        $this->loadDbValues($oldRow);

        // Call Row Inserting event
        $insertRow = $this->rowInserting($oldRow, $newRow);
        if ($insertRow) {
            $addRow = $this->insert($newRow);
            if ($addRow) {
            } elseif (!IsEmpty($this->DbErrorMessage)) { // Show database error
                $this->setFailureMessage($this->DbErrorMessage);
            }
        } else {
            if ($this->peekSuccessMessage() || $this->peekFailureMessage()) {
                // Use the message, do nothing
            } elseif ($this->CancelMessage != "") {
                $this->setFailureMessage($this->CancelMessage);
                $this->CancelMessage = "";
            } else {
                $this->setFailureMessage($this->language->phrase("InsertCancelled"));
            }
            $addRow = $insertRow;
        }
        if ($addRow) {
            // Call Row Inserted event
            $this->rowInserted($oldRow, $newRow);
        }

        // Write JSON response
        if (IsJsonResponse() && $addRow) {
            $row = $this->getRecordsFromResult([$newRow], true);
            $table = $this->TableVar;
            WriteJson(["success" => true, "action" => Config("API_ADD_ACTION"), $table => $row]);
        }
        return $addRow;
    }

    /**
     * Get add row
     *
     * @return array
     */
    protected function getAddRow(): array
    {
        $newRow = [];

        // Option_Default
        $tmpBool = $this->Option_Default->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Option_Default->setDbValueDef($newRow, $tmpBool, strval($this->Option_Default->CurrentValue) == "");

        // Show_Announcement
        $tmpBool = $this->Show_Announcement->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Show_Announcement->setDbValueDef($newRow, $tmpBool, strval($this->Show_Announcement->CurrentValue) == "");

        // Use_Announcement_Table
        $tmpBool = $this->Use_Announcement_Table->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Use_Announcement_Table->setDbValueDef($newRow, $tmpBool, strval($this->Use_Announcement_Table->CurrentValue) == "");

        // Maintenance_Mode
        $tmpBool = $this->Maintenance_Mode->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Maintenance_Mode->setDbValueDef($newRow, $tmpBool, strval($this->Maintenance_Mode->CurrentValue) == "");

        // Maintenance_Finish_DateTime
        $this->Maintenance_Finish_DateTime->setDbValueDef($newRow, UnFormatDateTime($this->Maintenance_Finish_DateTime->CurrentValue, $this->Maintenance_Finish_DateTime->formatPattern()), false);

        // Auto_Normal_After_Maintenance
        $tmpBool = $this->Auto_Normal_After_Maintenance->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Auto_Normal_After_Maintenance->setDbValueDef($newRow, $tmpBool, strval($this->Auto_Normal_After_Maintenance->CurrentValue) == "");
        return $newRow;
    }

    // Set up Breadcrumb
    protected function setupBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb();
        $url = CurrentUrl();
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("settingslist"), "", $this->TableVar, true);
        $pageId = ($this->isCopy()) ? "Copy" : "Add";
        $breadcrumb->add("add", $pageId, $url);
    }

    // Setup lookup options
    public function setupLookupOptions(DbField $fld): void
    {
        if ($fld->Lookup && $fld->Lookup->Options === null) {
            // Get default connection and filter
            $conn = $this->getConnection();
            $lookupFilter = "";

            // No need to check any more
            $fld->Lookup->Options = [];

            // Set up lookup SQL and connection
            switch ($fld->FieldVar) {
                case "x_Option_Default":
                    break;
                case "x_Show_Announcement":
                    break;
                case "x_Use_Announcement_Table":
                    break;
                case "x_Maintenance_Mode":
                    break;
                case "x_Auto_Normal_After_Maintenance":
                    break;
                default:
                    $lookupFilter = "";
                    break;
            }

            // Always call to Lookup->getSql so that user can setup Lookup->Options in Lookup_Selecting server event
            $qb = $fld->Lookup->getSqlAsQueryBuilder(false, "", $lookupFilter, $this);

            // Set up lookup cache
            if (!$fld->hasLookupOptions() && $fld->UseLookupCache && $qb != null && count($fld->Lookup->Options) == 0 && count($fld->Lookup->FilterFields) == 0) {
                $totalCnt = $this->getRecordCount($qb, $conn);
                if ($totalCnt > $fld->LookupCacheCount) { // Total count > cache count, do not cache
                    return;
                }
                // Get lookup cache Id
                $sql = $qb->getSQL();
                $lookupCacheKey = "lookup.cache." . Container($fld->Lookup->LinkTable)->TableVar . ".";
                $cacheId = $lookupCacheKey . hash("xxh128", $sql); // Hash value of SQL as cache id
                // Prune stale data first
                Container("result.cache")->prune();
                // Use result cache
                $cacheProfile = new QueryCacheProfile(0, $cacheId, Container("result.cache"));
                $rows = $conn->executeCacheQuery($sql, [], [], $cacheProfile)->fetchAllAssociative();
                $ar = [];
                foreach ($rows as $row) {
                    $row = $fld->Lookup->renderViewRow($row);
                    $key = $row["lf"];
                    if (IsFloatType($fld->Type)) { // Handle float field
                        $key = (float)$key;
                    }
                    $ar[strval($key)] = $row;
                }
                $fld->Lookup->Options = $ar;
            }
        }
    }

    // Page Load event
    public function pageLoad(): void
    {
        Language()->setPhrase("AddCaption", "Please fill in the following form ...");
    }

    // Page Unload event
    public function pageUnload(): void
    {
        //Log("Page Unload");
    }

    // Page Redirecting event
    public function pageRedirecting(string &$url): void
    {
        // Example:
        //$url = "your URL";
    }

    // Message Showing event
    // $type = ''|'success'|'failure'|'warning'
    public function messageShowing(string &$message, string $type): void
    {
        if ($type == "success") {
            //$message = "your success message";
        } elseif ($type == "failure") {
            //$message = "your failure message";
        } elseif ($type == "warning") {
            //$message = "your warning message";
        } else {
            //$message = "your message";
        }
    }

    // Page Render event
    public function pageRender(): void
    {
        //Log("Page Render");
    }

    // Page Data Rendering event
    public function pageDataRendering(string &$header): void
    {
        // Example:
        //$header = "your header";
    }

    // Page Data Rendered event
    public function pageDataRendered(string &$footer): void
    {
        // Example:
        //$footer = "your footer";
    }

    // Page Breaking event
    public function pageBreaking(bool &$break, string &$content): void
    {
        // Example:
        //$break = false; // Skip page break, or
        //$content = "<div style=\"break-after:page;\"></div>"; // Modify page break content
    }

    // Form Custom Validate event
    public function formCustomValidate(string &$customError): bool
    {
        // Return error message in $customError
        return true;
    }
}
