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
class HelpAdd extends Help
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "add";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "HelpAdd";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "helpadd";

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
        $this->Help_ID->Visible = false;
        $this->_Language->setVisibility();
        $this->Topic->setVisibility();
        $this->Description->setVisibility();
        $this->Category->setVisibility();
        $this->Order->setVisibility();
        $this->Display_in_Page->setVisibility();
        $this->Updated_By->setVisibility();
        $this->Last_Updated->setVisibility();
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'help';
        $this->TableName = 'help';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-add-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (help)
        if (!isset($GLOBALS["help"]) || $GLOBALS["help"]::class == PROJECT_NAMESPACE . "help") {
            $GLOBALS["help"] = &$this;
        }

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'help');
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
                        $result["view"] = SameString($pageName, "helpview"); // If View page, no primary button
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
            $key .= @$ar['Help_ID'];
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
            $this->Help_ID->Visible = false;
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
        $this->setupLookupOptions($this->_Language);
        $this->setupLookupOptions($this->Category);
        $this->setupLookupOptions($this->Updated_By);

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
            if (($keyValue = Get("Help_ID") ?? Route("Help_ID")) !== null) {
                $this->Help_ID->setQueryStringValue($keyValue);
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

        // Set up master/detail parameters
        // NOTE: Must be after loadOldRecord to prevent master key values being overwritten
        $this->setupMasterParms();

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
                    $this->terminate("helplist"); // No matching record, return to list
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
                    if (GetPageName($returnUrl) == "helplist") {
                        $returnUrl = $this->addMasterUrl($returnUrl); // List page, return to List page with correct master key if necessary
                    } elseif (GetPageName($returnUrl) == "helpview") {
                        $returnUrl = $this->getViewUrl(); // View page, return to View page with keyurl directly
                    }

                    // Handle UseAjaxActions
                    if ($this->IsModal && $this->UseAjaxActions && !$this->getCurrentMasterTable()) {
                        $this->IsModal = false;
                        if (GetPageName($returnUrl) != "helplist") {
                            FlashBag()->add("Return-Url", $returnUrl); // Save return URL
                            $returnUrl = "helplist"; // Return list page content
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
    }

    // Load form values
    protected function loadFormValues(): void
    {
        $validate = !Config("SERVER_VALIDATE");

        // Check field name 'Language' before field var 'x__Language'
        $val = $this->getFormValue("Language", null) ?? $this->getFormValue("x__Language", null);
        if (!$this->_Language->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->_Language->Visible = false; // Disable update for API request
            } else {
                $this->_Language->setFormValue($val);
            }
        }

        // Check field name 'Topic' before field var 'x_Topic'
        $val = $this->getFormValue("Topic", null) ?? $this->getFormValue("x_Topic", null);
        if (!$this->Topic->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Topic->Visible = false; // Disable update for API request
            } else {
                $this->Topic->setFormValue($val);
            }
        }

        // Check field name 'Description' before field var 'x_Description'
        $val = $this->getFormValue("Description", null) ?? $this->getFormValue("x_Description", null);
        if (!$this->Description->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Description->Visible = false; // Disable update for API request
            } else {
                $this->Description->setFormValue($val);
            }
        }

        // Check field name 'Category' before field var 'x_Category'
        $val = $this->getFormValue("Category", null) ?? $this->getFormValue("x_Category", null);
        if (!$this->Category->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Category->Visible = false; // Disable update for API request
            } else {
                $this->Category->setFormValue($val);
            }
        }

        // Check field name 'Order' before field var 'x_Order'
        $val = $this->getFormValue("Order", null) ?? $this->getFormValue("x_Order", null);
        if (!$this->Order->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Order->Visible = false; // Disable update for API request
            } else {
                $this->Order->setFormValue($val, true, $validate);
            }
        }

        // Check field name 'Display_in_Page' before field var 'x_Display_in_Page'
        $val = $this->getFormValue("Display_in_Page", null) ?? $this->getFormValue("x_Display_in_Page", null);
        if (!$this->Display_in_Page->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Display_in_Page->Visible = false; // Disable update for API request
            } else {
                $this->Display_in_Page->setFormValue($val);
            }
        }

        // Check field name 'Updated_By' before field var 'x_Updated_By'
        $val = $this->getFormValue("Updated_By", null) ?? $this->getFormValue("x_Updated_By", null);
        if (!$this->Updated_By->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Updated_By->Visible = false; // Disable update for API request
            } else {
                $this->Updated_By->setFormValue($val);
            }
        }

        // Check field name 'Last_Updated' before field var 'x_Last_Updated'
        $val = $this->getFormValue("Last_Updated", null) ?? $this->getFormValue("x_Last_Updated", null);
        if (!$this->Last_Updated->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Last_Updated->Visible = false; // Disable update for API request
            } else {
                $this->Last_Updated->setFormValue($val, true, $validate);
            }
            $this->Last_Updated->CurrentValue = UnformatDateTime($this->Last_Updated->CurrentValue, $this->Last_Updated->formatPattern());
        }

        // Check field name 'Help_ID' first before field var 'x_Help_ID'
        $val = $this->hasFormValue("Help_ID") ? $this->getFormValue("Help_ID") : $this->getFormValue("x_Help_ID");
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        $this->_Language->CurrentValue = $this->_Language->FormValue;
        $this->Topic->CurrentValue = $this->Topic->FormValue;
        $this->Description->CurrentValue = $this->Description->FormValue;
        $this->Category->CurrentValue = $this->Category->FormValue;
        $this->Order->CurrentValue = $this->Order->FormValue;
        $this->Display_in_Page->CurrentValue = $this->Display_in_Page->FormValue;
        $this->Updated_By->CurrentValue = $this->Updated_By->FormValue;
        $this->Last_Updated->CurrentValue = $this->Last_Updated->FormValue;
        $this->Last_Updated->CurrentValue = UnformatDateTime($this->Last_Updated->CurrentValue, $this->Last_Updated->formatPattern());
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
        $this->Help_ID->setDbValue($row['Help_ID']);
        $this->_Language->setDbValue($row['Language']);
        $this->Topic->setDbValue($row['Topic']);
        $this->Description->setDbValue($row['Description']);
        $this->Category->setDbValue($row['Category']);
        $this->Order->setDbValue($row['Order']);
        $this->Display_in_Page->setDbValue($row['Display_in_Page']);
        $this->Updated_By->setDbValue($row['Updated_By']);
        $this->Last_Updated->setDbValue($row['Last_Updated']);
    }

    // Return a row with default values
    protected function newRow(): array
    {
        $row = [];
        $row['Help_ID'] = $this->Help_ID->DefaultValue;
        $row['Language'] = $this->_Language->DefaultValue;
        $row['Topic'] = $this->Topic->DefaultValue;
        $row['Description'] = $this->Description->DefaultValue;
        $row['Category'] = $this->Category->DefaultValue;
        $row['Order'] = $this->Order->DefaultValue;
        $row['Display_in_Page'] = $this->Display_in_Page->DefaultValue;
        $row['Updated_By'] = $this->Updated_By->DefaultValue;
        $row['Last_Updated'] = $this->Last_Updated->DefaultValue;
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

        // Help_ID
        $this->Help_ID->RowCssClass = "row";

        // Language
        $this->_Language->RowCssClass = "row";

        // Topic
        $this->Topic->RowCssClass = "row";

        // Description
        $this->Description->RowCssClass = "row";

        // Category
        $this->Category->RowCssClass = "row";

        // Order
        $this->Order->RowCssClass = "row";

        // Display_in_Page
        $this->Display_in_Page->RowCssClass = "row";

        // Updated_By
        $this->Updated_By->RowCssClass = "row";

        // Last_Updated
        $this->Last_Updated->RowCssClass = "row";

        // View row
        if ($this->RowType == RowType::VIEW) {
            // Help_ID
            $this->Help_ID->ViewValue = $this->Help_ID->CurrentValue;

            // Language
            $curVal = strval($this->_Language->CurrentValue);
            if ($curVal != "") {
                $this->_Language->ViewValue = $this->_Language->lookupCacheOption($curVal);
                if ($this->_Language->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->_Language->Lookup->getTable()->Fields["Language_Code"]->searchExpression(), "=", $curVal, $this->_Language->Lookup->getTable()->Fields["Language_Code"]->searchDataType(), "DB");
                    $sqlWrk = $this->_Language->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->_Language->Lookup->renderViewRow($row);
                        }
                        $this->_Language->ViewValue = $this->_Language->displayValue($rows[0]);
                    } else {
                        $this->_Language->ViewValue = $this->_Language->CurrentValue;
                    }
                }
            } else {
                $this->_Language->ViewValue = null;
            }

            // Topic
            $this->Topic->ViewValue = $this->Topic->CurrentValue;

            // Description
            $this->Description->ViewValue = $this->Description->CurrentValue;

            // Category
            $curVal = strval($this->Category->CurrentValue);
            if ($curVal != "") {
                $this->Category->ViewValue = $this->Category->lookupCacheOption($curVal);
                if ($this->Category->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->Category->Lookup->getTable()->Fields["Category_ID"]->searchExpression(), "=", $curVal, $this->Category->Lookup->getTable()->Fields["Category_ID"]->searchDataType(), "DB");
                    $sqlWrk = $this->Category->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->Category->Lookup->renderViewRow($row);
                        }
                        $this->Category->ViewValue = $this->Category->displayValue($rows[0]);
                    } else {
                        $this->Category->ViewValue = FormatNumber($this->Category->CurrentValue, $this->Category->formatPattern());
                    }
                }
            } else {
                $this->Category->ViewValue = null;
            }

            // Order
            $this->Order->ViewValue = $this->Order->CurrentValue;
            $this->Order->ViewValue = FormatNumber($this->Order->ViewValue, $this->Order->formatPattern());

            // Display_in_Page
            $this->Display_in_Page->ViewValue = $this->Display_in_Page->CurrentValue;

            // Updated_By
            $curVal = strval($this->Updated_By->CurrentValue);
            if ($curVal != "") {
                $this->Updated_By->ViewValue = $this->Updated_By->lookupCacheOption($curVal);
                if ($this->Updated_By->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->Updated_By->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $curVal, $this->Updated_By->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
                    $sqlWrk = $this->Updated_By->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->Updated_By->Lookup->renderViewRow($row);
                        }
                        $this->Updated_By->ViewValue = $this->Updated_By->displayValue($rows[0]);
                    } else {
                        $this->Updated_By->ViewValue = $this->Updated_By->CurrentValue;
                    }
                }
            } else {
                $this->Updated_By->ViewValue = null;
            }

            // Last_Updated
            $this->Last_Updated->ViewValue = $this->Last_Updated->CurrentValue;
            $this->Last_Updated->ViewValue = FormatDateTime($this->Last_Updated->ViewValue, $this->Last_Updated->formatPattern());

            // Language
            $this->_Language->HrefValue = "";

            // Topic
            $this->Topic->HrefValue = "";

            // Description
            $this->Description->HrefValue = "";

            // Category
            $this->Category->HrefValue = "";

            // Order
            $this->Order->HrefValue = "";

            // Display_in_Page
            $this->Display_in_Page->HrefValue = "";

            // Updated_By
            $this->Updated_By->HrefValue = "";

            // Last_Updated
            $this->Last_Updated->HrefValue = "";
        } elseif ($this->RowType == RowType::ADD) {
            // Language
            $this->_Language->setupEditAttributes();
            $curVal = trim(strval($this->_Language->CurrentValue));
            if ($curVal != "") {
                $this->_Language->ViewValue = $this->_Language->lookupCacheOption($curVal);
            } else {
                $this->_Language->ViewValue = $this->_Language->Lookup !== null && is_array($this->_Language->lookupOptions()) && count($this->_Language->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->_Language->ViewValue !== null) { // Load from cache
                $this->_Language->EditValue = array_values($this->_Language->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->_Language->Lookup->getTable()->Fields["Language_Code"]->searchExpression(), "=", $this->_Language->CurrentValue, $this->_Language->Lookup->getTable()->Fields["Language_Code"]->searchDataType(), "DB");
                }
                $sqlWrk = $this->_Language->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                $conn = Conn();
                $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                $ari = count($rswrk);
                $rows = [];
                if ($ari > 0) { // Lookup values found
                    foreach ($rswrk as $row) {
                        $rows[] = $this->_Language->Lookup->renderViewRow($row);
                    }
                } else {
                    $this->_Language->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->_Language->EditValue = $rows;
            }
            $this->_Language->PlaceHolder = RemoveHtml($this->_Language->caption());

            // Topic
            $this->Topic->setupEditAttributes();
            $this->Topic->EditValue = !$this->Topic->Raw ? HtmlDecode($this->Topic->CurrentValue) : $this->Topic->CurrentValue;
            $this->Topic->PlaceHolder = RemoveHtml($this->Topic->caption());

            // Description
            $this->Description->setupEditAttributes();
            $this->Description->EditValue = $this->Description->CurrentValue;
            $this->Description->PlaceHolder = RemoveHtml($this->Description->caption());

            // Category
            $this->Category->setupEditAttributes();
            if ($this->Category->getSessionValue() != "") {
                $this->Category->CurrentValue = GetForeignKeyValue($this->Category->getSessionValue());
                $curVal = strval($this->Category->CurrentValue);
                if ($curVal != "") {
                    $this->Category->ViewValue = $this->Category->lookupCacheOption($curVal);
                    if ($this->Category->ViewValue === null) { // Lookup from database
                        $filterWrk = SearchFilter($this->Category->Lookup->getTable()->Fields["Category_ID"]->searchExpression(), "=", $curVal, $this->Category->Lookup->getTable()->Fields["Category_ID"]->searchDataType(), "DB");
                        $sqlWrk = $this->Category->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                        $conn = Conn();
                        $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                        $ari = count($rswrk);
                        if ($ari > 0) { // Lookup values found
                            $rows = [];
                            foreach ($rswrk as $row) {
                                $rows[] = $this->Category->Lookup->renderViewRow($row);
                            }
                            $this->Category->ViewValue = $this->Category->displayValue($rows[0]);
                        } else {
                            $this->Category->ViewValue = FormatNumber($this->Category->CurrentValue, $this->Category->formatPattern());
                        }
                    }
                } else {
                    $this->Category->ViewValue = null;
                }
            } else {
                $curVal = trim(strval($this->Category->CurrentValue));
                if ($curVal != "") {
                    $this->Category->ViewValue = $this->Category->lookupCacheOption($curVal);
                } else {
                    $this->Category->ViewValue = $this->Category->Lookup !== null && is_array($this->Category->lookupOptions()) && count($this->Category->lookupOptions()) > 0 ? $curVal : null;
                }
                if ($this->Category->ViewValue !== null) { // Load from cache
                    $this->Category->EditValue = array_values($this->Category->lookupOptions());
                } else { // Lookup from database
                    if ($curVal == "") {
                        $filterWrk = "0=1";
                    } else {
                        $filterWrk = SearchFilter($this->Category->Lookup->getTable()->Fields["Category_ID"]->searchExpression(), "=", $this->Category->CurrentValue, $this->Category->Lookup->getTable()->Fields["Category_ID"]->searchDataType(), "DB");
                    }
                    $sqlWrk = $this->Category->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    $rows = [];
                    if ($ari > 0) { // Lookup values found
                        foreach ($rswrk as $row) {
                            $rows[] = $this->Category->Lookup->renderViewRow($row);
                        }
                    } else {
                        $this->Category->ViewValue = $this->language->phrase("PleaseSelect");
                    }
                    $this->Category->EditValue = $rows;
                }
                $this->Category->PlaceHolder = RemoveHtml($this->Category->caption());
            }

            // Order
            $this->Order->setupEditAttributes();
            $this->Order->EditValue = $this->Order->CurrentValue;
            $this->Order->PlaceHolder = RemoveHtml($this->Order->caption());
            if (strval($this->Order->EditValue) != "" && is_numeric($this->Order->EditValue)) {
                $this->Order->EditValue = FormatNumber($this->Order->EditValue, null);
            }

            // Display_in_Page
            $this->Display_in_Page->setupEditAttributes();
            $this->Display_in_Page->EditValue = !$this->Display_in_Page->Raw ? HtmlDecode($this->Display_in_Page->CurrentValue) : $this->Display_in_Page->CurrentValue;
            $this->Display_in_Page->PlaceHolder = RemoveHtml($this->Display_in_Page->caption());

            // Updated_By
            $this->Updated_By->setupEditAttributes();
            $curVal = trim(strval($this->Updated_By->CurrentValue));
            if ($curVal != "") {
                $this->Updated_By->ViewValue = $this->Updated_By->lookupCacheOption($curVal);
            } else {
                $this->Updated_By->ViewValue = $this->Updated_By->Lookup !== null && is_array($this->Updated_By->lookupOptions()) && count($this->Updated_By->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->Updated_By->ViewValue !== null) { // Load from cache
                $this->Updated_By->EditValue = array_values($this->Updated_By->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->Updated_By->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $this->Updated_By->CurrentValue, $this->Updated_By->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
                }
                $sqlWrk = $this->Updated_By->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                $conn = Conn();
                $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                $ari = count($rswrk);
                $rows = [];
                if ($ari > 0) { // Lookup values found
                    foreach ($rswrk as $row) {
                        $rows[] = $this->Updated_By->Lookup->renderViewRow($row);
                    }
                } else {
                    $this->Updated_By->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->Updated_By->EditValue = $rows;
            }
            $this->Updated_By->PlaceHolder = RemoveHtml($this->Updated_By->caption());

            // Last_Updated
            $this->Last_Updated->setupEditAttributes();
            $this->Last_Updated->EditValue = FormatDateTime($this->Last_Updated->CurrentValue, $this->Last_Updated->formatPattern());
            $this->Last_Updated->PlaceHolder = RemoveHtml($this->Last_Updated->caption());

            // Add refer script

            // Language
            $this->_Language->HrefValue = "";

            // Topic
            $this->Topic->HrefValue = "";

            // Description
            $this->Description->HrefValue = "";

            // Category
            $this->Category->HrefValue = "";

            // Order
            $this->Order->HrefValue = "";

            // Display_in_Page
            $this->Display_in_Page->HrefValue = "";

            // Updated_By
            $this->Updated_By->HrefValue = "";

            // Last_Updated
            $this->Last_Updated->HrefValue = "";
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
            if ($this->_Language->Visible && $this->_Language->Required) {
                if (!$this->_Language->IsDetailKey && IsEmpty($this->_Language->FormValue)) {
                    $this->_Language->addErrorMessage(str_replace("%s", $this->_Language->caption(), $this->_Language->RequiredErrorMessage));
                }
            }
            if ($this->Topic->Visible && $this->Topic->Required) {
                if (!$this->Topic->IsDetailKey && IsEmpty($this->Topic->FormValue)) {
                    $this->Topic->addErrorMessage(str_replace("%s", $this->Topic->caption(), $this->Topic->RequiredErrorMessage));
                }
            }
            if ($this->Description->Visible && $this->Description->Required) {
                if (!$this->Description->IsDetailKey && IsEmpty($this->Description->FormValue)) {
                    $this->Description->addErrorMessage(str_replace("%s", $this->Description->caption(), $this->Description->RequiredErrorMessage));
                }
            }
            if ($this->Category->Visible && $this->Category->Required) {
                if (!$this->Category->IsDetailKey && IsEmpty($this->Category->FormValue)) {
                    $this->Category->addErrorMessage(str_replace("%s", $this->Category->caption(), $this->Category->RequiredErrorMessage));
                }
            }
            if ($this->Order->Visible && $this->Order->Required) {
                if (!$this->Order->IsDetailKey && IsEmpty($this->Order->FormValue)) {
                    $this->Order->addErrorMessage(str_replace("%s", $this->Order->caption(), $this->Order->RequiredErrorMessage));
                }
            }
            if (!CheckInteger($this->Order->FormValue)) {
                $this->Order->addErrorMessage($this->Order->getErrorMessage(false));
            }
            if ($this->Display_in_Page->Visible && $this->Display_in_Page->Required) {
                if (!$this->Display_in_Page->IsDetailKey && IsEmpty($this->Display_in_Page->FormValue)) {
                    $this->Display_in_Page->addErrorMessage(str_replace("%s", $this->Display_in_Page->caption(), $this->Display_in_Page->RequiredErrorMessage));
                }
            }
            if ($this->Updated_By->Visible && $this->Updated_By->Required) {
                if (!$this->Updated_By->IsDetailKey && IsEmpty($this->Updated_By->FormValue)) {
                    $this->Updated_By->addErrorMessage(str_replace("%s", $this->Updated_By->caption(), $this->Updated_By->RequiredErrorMessage));
                }
            }
            if ($this->Last_Updated->Visible && $this->Last_Updated->Required) {
                if (!$this->Last_Updated->IsDetailKey && IsEmpty($this->Last_Updated->FormValue)) {
                    $this->Last_Updated->addErrorMessage(str_replace("%s", $this->Last_Updated->caption(), $this->Last_Updated->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->Last_Updated->FormValue, $this->Last_Updated->formatPattern())) {
                $this->Last_Updated->addErrorMessage($this->Last_Updated->getErrorMessage(false));
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

        // Language
        $this->_Language->setDbValueDef($newRow, $this->_Language->CurrentValue, false);

        // Topic
        $this->Topic->setDbValueDef($newRow, $this->Topic->CurrentValue, false);

        // Description
        $this->Description->setDbValueDef($newRow, $this->Description->CurrentValue, false);

        // Category
        $this->Category->setDbValueDef($newRow, $this->Category->CurrentValue, false);

        // Order
        $this->Order->setDbValueDef($newRow, $this->Order->CurrentValue, false);

        // Display_in_Page
        $this->Display_in_Page->setDbValueDef($newRow, $this->Display_in_Page->CurrentValue, false);

        // Updated_By
        $this->Updated_By->setDbValueDef($newRow, $this->Updated_By->CurrentValue, false);

        // Last_Updated
        $this->Last_Updated->setDbValueDef($newRow, UnFormatDateTime($this->Last_Updated->CurrentValue, $this->Last_Updated->formatPattern()), false);
        return $newRow;
    }

    // Set up master/detail based on QueryString
    protected function setupMasterParms(): void
    {
        $validMaster = false;
        $foreignKeys = [];
        // Get the keys for master table
        if (($master = Get(Config("TABLE_SHOW_MASTER"), Get(Config("TABLE_MASTER")))) !== null) {
            $masterTblVar = $master;
            if ($masterTblVar == "") {
                $validMaster = true;
                $this->DbMasterFilter = "";
                $this->DbDetailFilter = "";
            }
            if ($masterTblVar == "help_categories") {
                $validMaster = true;
                $masterTbl = Container("help_categories");
                if (($parm = Get("fk_Category_ID", Get("Category"))) !== null) {
                    $masterTbl->Category_ID->setQueryStringValue($parm);
                    $this->Category->QueryStringValue = $masterTbl->Category_ID->QueryStringValue; // DO NOT change, master/detail key data type can be different
                    $this->Category->setSessionValue($this->Category->QueryStringValue);
                    $foreignKeys["Category"] = $this->Category->QueryStringValue;
                    if (!is_numeric($masterTbl->Category_ID->QueryStringValue)) {
                        $validMaster = false;
                    }
                } else {
                    $validMaster = false;
                }
            }
        } elseif (($master = Post(Config("TABLE_SHOW_MASTER"), Post(Config("TABLE_MASTER")))) !== null) {
            $masterTblVar = $master;
            if ($masterTblVar == "") {
                    $validMaster = true;
                    $this->DbMasterFilter = "";
                    $this->DbDetailFilter = "";
            }
            if ($masterTblVar == "help_categories") {
                $validMaster = true;
                $masterTbl = Container("help_categories");
                if (($parm = Post("fk_Category_ID", Post("Category"))) !== null) {
                    $masterTbl->Category_ID->setFormValue($parm);
                    $this->Category->FormValue = $masterTbl->Category_ID->FormValue;
                    $this->Category->setSessionValue($this->Category->FormValue);
                    $foreignKeys["Category"] = $this->Category->FormValue;
                    if (!is_numeric($masterTbl->Category_ID->FormValue)) {
                        $validMaster = false;
                    }
                } else {
                    $validMaster = false;
                }
            }
        }
        if ($validMaster) {
            // Save current master table
            $this->setCurrentMasterTable($masterTblVar);

            // Reset start record counter (new master key)
            if (!$this->isAddOrEdit() && !$this->isGridUpdate()) {
                $this->StartRecord = 1;
                $this->setStartRecordNumber($this->StartRecord);
            }

            // Clear previous master key from Session
            if ($masterTblVar != "help_categories") {
                if (!array_key_exists("Category", $foreignKeys)) { // Not current foreign key
                    $this->Category->setSessionValue("");
                }
            }
        }
        $this->DbMasterFilter = $this->getMasterFilterFromSession(); // Get master filter from session
        $this->DbDetailFilter = $this->getDetailFilterFromSession(); // Get detail filter from session
    }

    // Set up Breadcrumb
    protected function setupBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb();
        $url = CurrentUrl();
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("helplist"), "", $this->TableVar, true);
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
                case "x__Language":
                    break;
                case "x_Category":
                    break;
                case "x_Updated_By":
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
        //Log("Page Load");
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
