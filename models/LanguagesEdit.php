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
class LanguagesEdit extends Languages
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "edit";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "LanguagesEdit";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "languagesedit";

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
        $this->Language_Code->setVisibility();
        $this->Language_Name->setVisibility();
        $this->Default->setVisibility();
        $this->Site_Logo->setVisibility();
        $this->Site_Title->setVisibility();
        $this->Default_Thousands_Separator->setVisibility();
        $this->Default_Decimal_Point->setVisibility();
        $this->Default_Currency_Symbol->setVisibility();
        $this->Default_Money_Thousands_Separator->setVisibility();
        $this->Default_Money_Decimal_Point->setVisibility();
        $this->Terms_And_Condition_Text->setVisibility();
        $this->Announcement_Text->setVisibility();
        $this->About_Text->setVisibility();
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'languages';
        $this->TableName = 'languages';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-edit-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (languages)
        if (!isset($GLOBALS["languages"]) || $GLOBALS["languages"]::class == PROJECT_NAMESPACE . "languages") {
            $GLOBALS["languages"] = &$this;
        }

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'languages');
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
                        $result["view"] = SameString($pageName, "languagesview"); // If View page, no primary button
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
            $key .= @$ar['Language_Code'];
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

    // Properties
    public string $FormClassName = "ew-form ew-edit-form overlay-wrapper";
    public bool $IsModal = false;
    public bool $IsMobileOrModal = false;
    public ?string $DbMasterFilter = "";
    public string $DbDetailFilter = "";
    public ?string $HashValue = null; // Hash Value
    public int $DisplayRecords = 1;
    public int $StartRecord = 0;
    public int $StopRecord = 0;
    public int $TotalRecords = 0;
    public int $RecordRange = 10;
    public int $RecordCount = 0;

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
        $this->setupLookupOptions($this->Default);

        // Check modal
        if ($this->IsModal) {
            $SkipHeaderFooter = true;
        }
        $this->IsMobileOrModal = IsMobile() || $this->IsModal;
        $loaded = false;
        $postBack = false;

        // Set up current action and primary key
        if (IsApi()) {
            // Load key values
            $loaded = true;
            if (($keyValue = Get("Language_Code") ?? Key(0) ?? Route(2)) !== null) {
                $this->Language_Code->setQueryStringValue($keyValue);
                $this->Language_Code->setOldValue($this->Language_Code->QueryStringValue);
            } elseif (Post("Language_Code") !== null) {
                $this->Language_Code->setFormValue(Post("Language_Code"));
                $this->Language_Code->setOldValue($this->Language_Code->FormValue);
            } else {
                $loaded = false; // Unable to load key
            }

            // Load record
            if ($loaded) {
                $loaded = $this->loadRow();
            }
            if (!$loaded) {
                $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
                $this->terminate();
                return;
            }
            $this->CurrentAction = "update"; // Update record directly
            $this->OldKey = $this->getKey(true); // Get from CurrentValue
            $postBack = true;
        } else {
            if (Post("action", "") !== "") {
                $this->CurrentAction = Post("action"); // Get action code
                if (!$this->isShow()) { // Not reload record, handle as postback
                    $postBack = true;
                }

                // Get key from Form
                $this->setKey($this->getOldKey(), $this->isShow());
            } else {
                $this->CurrentAction = "show"; // Default action is display

                // Load key from QueryString
                $loadByQuery = false;
                if (($keyValue = Get("Language_Code") ?? Route("Language_Code")) !== null) {
                    $this->Language_Code->setQueryStringValue($keyValue);
                    $loadByQuery = true;
                } else {
                    $this->Language_Code->CurrentValue = null;
                }
            }

            // Load result set
            if ($this->isShow()) {
                    // Load current record
                    $loaded = $this->loadRow();
                $this->OldKey = $loaded ? $this->getKey(true) : ""; // Get from CurrentValue
            }
        }

        // Process form if post back
        if ($postBack) {
            $this->loadFormValues(); // Get form values
        }

        // Validate form if post back
        if ($postBack) {
            if (!$this->validateForm()) {
                $this->EventCancelled = true; // Event cancelled
                $this->restoreFormValues();
                if (IsApi()) {
                    $this->terminate();
                    return;
                } else {
                    $this->CurrentAction = ""; // Form error, reset action
                }
            }
        }

        // Perform current action
        switch ($this->CurrentAction) {
            case "show": // Get a record to display
                    if (!$loaded) { // Load record based on key
                        if (!$this->peekFailureMessage()) {
                            $this->setFailureMessage($this->language->phrase("NoRecord")); // No record found
                        }
                        $this->terminate("languageslist"); // No matching record, return to list
                        return;
                    }
                break;
            case "update": // Update
                $returnUrl = $this->getReturnUrl();
                if (GetPageName($returnUrl) == "languageslist") {
                    $returnUrl = $this->addMasterUrl($returnUrl); // List page, return to List page with correct master key if necessary
                }
                if ($this->editRow()) { // Update record based on key
                    CleanUploadTempPaths(SessionId());
                    if (!$this->peekSuccessMessage()) {
                        $this->setSuccessMessage($this->language->phrase("UpdateSuccess")); // Update success
                    }

                    // Handle UseAjaxActions with return page
                    if ($this->IsModal && $this->UseAjaxActions) {
                        $this->IsModal = false;
                        if (GetPageName($returnUrl) != "languageslist") {
                            FlashBag()->add("Return-Url", $returnUrl); // Save return URL
                            $returnUrl = "languageslist"; // Return list page content
                        }
                    }
                    if (IsJsonResponse()) {
                        $this->terminate(true);
                        return;
                    } else {
                        $this->terminate($returnUrl); // Return to caller
                        return;
                    }
                } elseif (IsApi()) { // API request, return
                    $this->terminate();
                    return;
                } elseif ($this->IsModal && $this->UseAjaxActions) { // Return JSON error message
                    WriteJson(["success" => false, "validation" => $this->getValidationErrors(), "error" => $this->getFailureMessage()]);
                    $this->terminate();
                    return;
                } elseif (($this->peekFailureMessage()[0] ?? "") == $this->language->phrase("NoRecord")) {
                    $this->terminate($returnUrl); // Return to caller
                    return;
                } else {
                    $this->EventCancelled = true; // Event cancelled
                    $this->restoreFormValues(); // Restore form values if update failed
                }
        }

        // Set up Breadcrumb
        $this->setupBreadcrumb();

        // Render the record
        $this->RowType = RowType::EDIT; // Render as Edit
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

    // Load form values
    protected function loadFormValues(): void
    {
        $validate = !Config("SERVER_VALIDATE");

        // Check field name 'Language_Code' before field var 'x_Language_Code'
        $val = $this->getFormValue("Language_Code", null) ?? $this->getFormValue("x_Language_Code", null);
        if (!$this->Language_Code->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Language_Code->Visible = false; // Disable update for API request
            } else {
                $this->Language_Code->setFormValue($val);
            }
        }
        if ($this->hasFormValue("o_Language_Code")) {
            $this->Language_Code->setOldValue($this->getFormValue("o_Language_Code"));
        }

        // Check field name 'Language_Name' before field var 'x_Language_Name'
        $val = $this->getFormValue("Language_Name", null) ?? $this->getFormValue("x_Language_Name", null);
        if (!$this->Language_Name->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Language_Name->Visible = false; // Disable update for API request
            } else {
                $this->Language_Name->setFormValue($val);
            }
        }

        // Check field name 'Default' before field var 'x_Default'
        $val = $this->getFormValue("Default", null) ?? $this->getFormValue("x_Default", null);
        if (!$this->Default->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Default->Visible = false; // Disable update for API request
            } else {
                $this->Default->setFormValue($val);
            }
        }

        // Check field name 'Site_Logo' before field var 'x_Site_Logo'
        $val = $this->getFormValue("Site_Logo", null) ?? $this->getFormValue("x_Site_Logo", null);
        if (!$this->Site_Logo->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Site_Logo->Visible = false; // Disable update for API request
            } else {
                $this->Site_Logo->setFormValue($val);
            }
        }

        // Check field name 'Site_Title' before field var 'x_Site_Title'
        $val = $this->getFormValue("Site_Title", null) ?? $this->getFormValue("x_Site_Title", null);
        if (!$this->Site_Title->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Site_Title->Visible = false; // Disable update for API request
            } else {
                $this->Site_Title->setFormValue($val);
            }
        }

        // Check field name 'Default_Thousands_Separator' before field var 'x_Default_Thousands_Separator'
        $val = $this->getFormValue("Default_Thousands_Separator", null) ?? $this->getFormValue("x_Default_Thousands_Separator", null);
        if (!$this->Default_Thousands_Separator->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Default_Thousands_Separator->Visible = false; // Disable update for API request
            } else {
                $this->Default_Thousands_Separator->setFormValue($val);
            }
        }

        // Check field name 'Default_Decimal_Point' before field var 'x_Default_Decimal_Point'
        $val = $this->getFormValue("Default_Decimal_Point", null) ?? $this->getFormValue("x_Default_Decimal_Point", null);
        if (!$this->Default_Decimal_Point->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Default_Decimal_Point->Visible = false; // Disable update for API request
            } else {
                $this->Default_Decimal_Point->setFormValue($val);
            }
        }

        // Check field name 'Default_Currency_Symbol' before field var 'x_Default_Currency_Symbol'
        $val = $this->getFormValue("Default_Currency_Symbol", null) ?? $this->getFormValue("x_Default_Currency_Symbol", null);
        if (!$this->Default_Currency_Symbol->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Default_Currency_Symbol->Visible = false; // Disable update for API request
            } else {
                $this->Default_Currency_Symbol->setFormValue($val);
            }
        }

        // Check field name 'Default_Money_Thousands_Separator' before field var 'x_Default_Money_Thousands_Separator'
        $val = $this->getFormValue("Default_Money_Thousands_Separator", null) ?? $this->getFormValue("x_Default_Money_Thousands_Separator", null);
        if (!$this->Default_Money_Thousands_Separator->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Default_Money_Thousands_Separator->Visible = false; // Disable update for API request
            } else {
                $this->Default_Money_Thousands_Separator->setFormValue($val);
            }
        }

        // Check field name 'Default_Money_Decimal_Point' before field var 'x_Default_Money_Decimal_Point'
        $val = $this->getFormValue("Default_Money_Decimal_Point", null) ?? $this->getFormValue("x_Default_Money_Decimal_Point", null);
        if (!$this->Default_Money_Decimal_Point->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Default_Money_Decimal_Point->Visible = false; // Disable update for API request
            } else {
                $this->Default_Money_Decimal_Point->setFormValue($val);
            }
        }

        // Check field name 'Terms_And_Condition_Text' before field var 'x_Terms_And_Condition_Text'
        $val = $this->getFormValue("Terms_And_Condition_Text", null) ?? $this->getFormValue("x_Terms_And_Condition_Text", null);
        if (!$this->Terms_And_Condition_Text->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Terms_And_Condition_Text->Visible = false; // Disable update for API request
            } else {
                $this->Terms_And_Condition_Text->setFormValue($val);
            }
        }

        // Check field name 'Announcement_Text' before field var 'x_Announcement_Text'
        $val = $this->getFormValue("Announcement_Text", null) ?? $this->getFormValue("x_Announcement_Text", null);
        if (!$this->Announcement_Text->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Announcement_Text->Visible = false; // Disable update for API request
            } else {
                $this->Announcement_Text->setFormValue($val);
            }
        }

        // Check field name 'About_Text' before field var 'x_About_Text'
        $val = $this->getFormValue("About_Text", null) ?? $this->getFormValue("x_About_Text", null);
        if (!$this->About_Text->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->About_Text->Visible = false; // Disable update for API request
            } else {
                $this->About_Text->setFormValue($val);
            }
        }
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        $this->Language_Code->CurrentValue = $this->Language_Code->FormValue;
        $this->Language_Name->CurrentValue = $this->Language_Name->FormValue;
        $this->Default->CurrentValue = $this->Default->FormValue;
        $this->Site_Logo->CurrentValue = $this->Site_Logo->FormValue;
        $this->Site_Title->CurrentValue = $this->Site_Title->FormValue;
        $this->Default_Thousands_Separator->CurrentValue = $this->Default_Thousands_Separator->FormValue;
        $this->Default_Decimal_Point->CurrentValue = $this->Default_Decimal_Point->FormValue;
        $this->Default_Currency_Symbol->CurrentValue = $this->Default_Currency_Symbol->FormValue;
        $this->Default_Money_Thousands_Separator->CurrentValue = $this->Default_Money_Thousands_Separator->FormValue;
        $this->Default_Money_Decimal_Point->CurrentValue = $this->Default_Money_Decimal_Point->FormValue;
        $this->Terms_And_Condition_Text->CurrentValue = $this->Terms_And_Condition_Text->FormValue;
        $this->Announcement_Text->CurrentValue = $this->Announcement_Text->FormValue;
        $this->About_Text->CurrentValue = $this->About_Text->FormValue;
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
        $this->Language_Code->setDbValue($row['Language_Code']);
        $this->Language_Name->setDbValue($row['Language_Name']);
        $this->Default->setDbValue($row['Default']);
        $this->Site_Logo->setDbValue($row['Site_Logo']);
        $this->Site_Title->setDbValue($row['Site_Title']);
        $this->Default_Thousands_Separator->setDbValue($row['Default_Thousands_Separator']);
        $this->Default_Decimal_Point->setDbValue($row['Default_Decimal_Point']);
        $this->Default_Currency_Symbol->setDbValue($row['Default_Currency_Symbol']);
        $this->Default_Money_Thousands_Separator->setDbValue($row['Default_Money_Thousands_Separator']);
        $this->Default_Money_Decimal_Point->setDbValue($row['Default_Money_Decimal_Point']);
        $this->Terms_And_Condition_Text->setDbValue($row['Terms_And_Condition_Text']);
        $this->Announcement_Text->setDbValue($row['Announcement_Text']);
        $this->About_Text->setDbValue($row['About_Text']);
    }

    // Return a row with default values
    protected function newRow(): array
    {
        $row = [];
        $row['Language_Code'] = $this->Language_Code->DefaultValue;
        $row['Language_Name'] = $this->Language_Name->DefaultValue;
        $row['Default'] = $this->Default->DefaultValue;
        $row['Site_Logo'] = $this->Site_Logo->DefaultValue;
        $row['Site_Title'] = $this->Site_Title->DefaultValue;
        $row['Default_Thousands_Separator'] = $this->Default_Thousands_Separator->DefaultValue;
        $row['Default_Decimal_Point'] = $this->Default_Decimal_Point->DefaultValue;
        $row['Default_Currency_Symbol'] = $this->Default_Currency_Symbol->DefaultValue;
        $row['Default_Money_Thousands_Separator'] = $this->Default_Money_Thousands_Separator->DefaultValue;
        $row['Default_Money_Decimal_Point'] = $this->Default_Money_Decimal_Point->DefaultValue;
        $row['Terms_And_Condition_Text'] = $this->Terms_And_Condition_Text->DefaultValue;
        $row['Announcement_Text'] = $this->Announcement_Text->DefaultValue;
        $row['About_Text'] = $this->About_Text->DefaultValue;
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

        // Language_Code
        $this->Language_Code->RowCssClass = "row";

        // Language_Name
        $this->Language_Name->RowCssClass = "row";

        // Default
        $this->Default->RowCssClass = "row";

        // Site_Logo
        $this->Site_Logo->RowCssClass = "row";

        // Site_Title
        $this->Site_Title->RowCssClass = "row";

        // Default_Thousands_Separator
        $this->Default_Thousands_Separator->RowCssClass = "row";

        // Default_Decimal_Point
        $this->Default_Decimal_Point->RowCssClass = "row";

        // Default_Currency_Symbol
        $this->Default_Currency_Symbol->RowCssClass = "row";

        // Default_Money_Thousands_Separator
        $this->Default_Money_Thousands_Separator->RowCssClass = "row";

        // Default_Money_Decimal_Point
        $this->Default_Money_Decimal_Point->RowCssClass = "row";

        // Terms_And_Condition_Text
        $this->Terms_And_Condition_Text->RowCssClass = "row";

        // Announcement_Text
        $this->Announcement_Text->RowCssClass = "row";

        // About_Text
        $this->About_Text->RowCssClass = "row";

        // View row
        if ($this->RowType == RowType::VIEW) {
            // Language_Code
            $this->Language_Code->ViewValue = $this->Language_Code->CurrentValue;

            // Language_Name
            $this->Language_Name->ViewValue = $this->Language_Name->CurrentValue;

            // Default
            if (ConvertToBool($this->Default->CurrentValue)) {
                $this->Default->ViewValue = $this->Default->tagCaption(1) != "" ? $this->Default->tagCaption(1) : "Yes";
            } else {
                $this->Default->ViewValue = $this->Default->tagCaption(2) != "" ? $this->Default->tagCaption(2) : "No";
            }

            // Site_Logo
            $this->Site_Logo->ViewValue = $this->Site_Logo->CurrentValue;

            // Site_Title
            $this->Site_Title->ViewValue = $this->Site_Title->CurrentValue;

            // Default_Thousands_Separator
            $this->Default_Thousands_Separator->ViewValue = $this->Default_Thousands_Separator->CurrentValue;

            // Default_Decimal_Point
            $this->Default_Decimal_Point->ViewValue = $this->Default_Decimal_Point->CurrentValue;

            // Default_Currency_Symbol
            $this->Default_Currency_Symbol->ViewValue = $this->Default_Currency_Symbol->CurrentValue;

            // Default_Money_Thousands_Separator
            $this->Default_Money_Thousands_Separator->ViewValue = $this->Default_Money_Thousands_Separator->CurrentValue;

            // Default_Money_Decimal_Point
            $this->Default_Money_Decimal_Point->ViewValue = $this->Default_Money_Decimal_Point->CurrentValue;

            // Terms_And_Condition_Text
            $this->Terms_And_Condition_Text->ViewValue = $this->Terms_And_Condition_Text->CurrentValue;

            // Announcement_Text
            $this->Announcement_Text->ViewValue = $this->Announcement_Text->CurrentValue;

            // About_Text
            $this->About_Text->ViewValue = $this->About_Text->CurrentValue;

            // Language_Code
            $this->Language_Code->HrefValue = "";

            // Language_Name
            $this->Language_Name->HrefValue = "";

            // Default
            $this->Default->HrefValue = "";

            // Site_Logo
            $this->Site_Logo->HrefValue = "";

            // Site_Title
            $this->Site_Title->HrefValue = "";

            // Default_Thousands_Separator
            $this->Default_Thousands_Separator->HrefValue = "";

            // Default_Decimal_Point
            $this->Default_Decimal_Point->HrefValue = "";

            // Default_Currency_Symbol
            $this->Default_Currency_Symbol->HrefValue = "";

            // Default_Money_Thousands_Separator
            $this->Default_Money_Thousands_Separator->HrefValue = "";

            // Default_Money_Decimal_Point
            $this->Default_Money_Decimal_Point->HrefValue = "";

            // Terms_And_Condition_Text
            $this->Terms_And_Condition_Text->HrefValue = "";

            // Announcement_Text
            $this->Announcement_Text->HrefValue = "";

            // About_Text
            $this->About_Text->HrefValue = "";
        } elseif ($this->RowType == RowType::EDIT) {
            // Language_Code
            $this->Language_Code->setupEditAttributes();
            $this->Language_Code->EditValue = !$this->Language_Code->Raw ? HtmlDecode($this->Language_Code->CurrentValue) : $this->Language_Code->CurrentValue;
            $this->Language_Code->PlaceHolder = RemoveHtml($this->Language_Code->caption());

            // Language_Name
            $this->Language_Name->setupEditAttributes();
            $this->Language_Name->EditValue = !$this->Language_Name->Raw ? HtmlDecode($this->Language_Name->CurrentValue) : $this->Language_Name->CurrentValue;
            $this->Language_Name->PlaceHolder = RemoveHtml($this->Language_Name->caption());

            // Default
            $this->Default->EditValue = $this->Default->options(false);
            $this->Default->PlaceHolder = RemoveHtml($this->Default->caption());

            // Site_Logo
            $this->Site_Logo->setupEditAttributes();
            $this->Site_Logo->EditValue = !$this->Site_Logo->Raw ? HtmlDecode($this->Site_Logo->CurrentValue) : $this->Site_Logo->CurrentValue;
            $this->Site_Logo->PlaceHolder = RemoveHtml($this->Site_Logo->caption());

            // Site_Title
            $this->Site_Title->setupEditAttributes();
            $this->Site_Title->EditValue = !$this->Site_Title->Raw ? HtmlDecode($this->Site_Title->CurrentValue) : $this->Site_Title->CurrentValue;
            $this->Site_Title->PlaceHolder = RemoveHtml($this->Site_Title->caption());

            // Default_Thousands_Separator
            $this->Default_Thousands_Separator->setupEditAttributes();
            $this->Default_Thousands_Separator->EditValue = !$this->Default_Thousands_Separator->Raw ? HtmlDecode($this->Default_Thousands_Separator->CurrentValue) : $this->Default_Thousands_Separator->CurrentValue;
            $this->Default_Thousands_Separator->PlaceHolder = RemoveHtml($this->Default_Thousands_Separator->caption());

            // Default_Decimal_Point
            $this->Default_Decimal_Point->setupEditAttributes();
            $this->Default_Decimal_Point->EditValue = !$this->Default_Decimal_Point->Raw ? HtmlDecode($this->Default_Decimal_Point->CurrentValue) : $this->Default_Decimal_Point->CurrentValue;
            $this->Default_Decimal_Point->PlaceHolder = RemoveHtml($this->Default_Decimal_Point->caption());

            // Default_Currency_Symbol
            $this->Default_Currency_Symbol->setupEditAttributes();
            $this->Default_Currency_Symbol->EditValue = !$this->Default_Currency_Symbol->Raw ? HtmlDecode($this->Default_Currency_Symbol->CurrentValue) : $this->Default_Currency_Symbol->CurrentValue;
            $this->Default_Currency_Symbol->PlaceHolder = RemoveHtml($this->Default_Currency_Symbol->caption());

            // Default_Money_Thousands_Separator
            $this->Default_Money_Thousands_Separator->setupEditAttributes();
            $this->Default_Money_Thousands_Separator->EditValue = !$this->Default_Money_Thousands_Separator->Raw ? HtmlDecode($this->Default_Money_Thousands_Separator->CurrentValue) : $this->Default_Money_Thousands_Separator->CurrentValue;
            $this->Default_Money_Thousands_Separator->PlaceHolder = RemoveHtml($this->Default_Money_Thousands_Separator->caption());

            // Default_Money_Decimal_Point
            $this->Default_Money_Decimal_Point->setupEditAttributes();
            $this->Default_Money_Decimal_Point->EditValue = !$this->Default_Money_Decimal_Point->Raw ? HtmlDecode($this->Default_Money_Decimal_Point->CurrentValue) : $this->Default_Money_Decimal_Point->CurrentValue;
            $this->Default_Money_Decimal_Point->PlaceHolder = RemoveHtml($this->Default_Money_Decimal_Point->caption());

            // Terms_And_Condition_Text
            $this->Terms_And_Condition_Text->setupEditAttributes();
            $this->Terms_And_Condition_Text->EditValue = $this->Terms_And_Condition_Text->CurrentValue;
            $this->Terms_And_Condition_Text->PlaceHolder = RemoveHtml($this->Terms_And_Condition_Text->caption());

            // Announcement_Text
            $this->Announcement_Text->setupEditAttributes();
            $this->Announcement_Text->EditValue = $this->Announcement_Text->CurrentValue;
            $this->Announcement_Text->PlaceHolder = RemoveHtml($this->Announcement_Text->caption());

            // About_Text
            $this->About_Text->setupEditAttributes();
            $this->About_Text->EditValue = $this->About_Text->CurrentValue;
            $this->About_Text->PlaceHolder = RemoveHtml($this->About_Text->caption());

            // Edit refer script

            // Language_Code
            $this->Language_Code->HrefValue = "";

            // Language_Name
            $this->Language_Name->HrefValue = "";

            // Default
            $this->Default->HrefValue = "";

            // Site_Logo
            $this->Site_Logo->HrefValue = "";

            // Site_Title
            $this->Site_Title->HrefValue = "";

            // Default_Thousands_Separator
            $this->Default_Thousands_Separator->HrefValue = "";

            // Default_Decimal_Point
            $this->Default_Decimal_Point->HrefValue = "";

            // Default_Currency_Symbol
            $this->Default_Currency_Symbol->HrefValue = "";

            // Default_Money_Thousands_Separator
            $this->Default_Money_Thousands_Separator->HrefValue = "";

            // Default_Money_Decimal_Point
            $this->Default_Money_Decimal_Point->HrefValue = "";

            // Terms_And_Condition_Text
            $this->Terms_And_Condition_Text->HrefValue = "";

            // Announcement_Text
            $this->Announcement_Text->HrefValue = "";

            // About_Text
            $this->About_Text->HrefValue = "";
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
            if ($this->Language_Code->Visible && $this->Language_Code->Required) {
                if (!$this->Language_Code->IsDetailKey && IsEmpty($this->Language_Code->FormValue)) {
                    $this->Language_Code->addErrorMessage(str_replace("%s", $this->Language_Code->caption(), $this->Language_Code->RequiredErrorMessage));
                }
            }
            if ($this->Language_Name->Visible && $this->Language_Name->Required) {
                if (!$this->Language_Name->IsDetailKey && IsEmpty($this->Language_Name->FormValue)) {
                    $this->Language_Name->addErrorMessage(str_replace("%s", $this->Language_Name->caption(), $this->Language_Name->RequiredErrorMessage));
                }
            }
            if ($this->Default->Visible && $this->Default->Required) {
                if ($this->Default->FormValue == "") {
                    $this->Default->addErrorMessage(str_replace("%s", $this->Default->caption(), $this->Default->RequiredErrorMessage));
                }
            }
            if ($this->Site_Logo->Visible && $this->Site_Logo->Required) {
                if (!$this->Site_Logo->IsDetailKey && IsEmpty($this->Site_Logo->FormValue)) {
                    $this->Site_Logo->addErrorMessage(str_replace("%s", $this->Site_Logo->caption(), $this->Site_Logo->RequiredErrorMessage));
                }
            }
            if ($this->Site_Title->Visible && $this->Site_Title->Required) {
                if (!$this->Site_Title->IsDetailKey && IsEmpty($this->Site_Title->FormValue)) {
                    $this->Site_Title->addErrorMessage(str_replace("%s", $this->Site_Title->caption(), $this->Site_Title->RequiredErrorMessage));
                }
            }
            if ($this->Default_Thousands_Separator->Visible && $this->Default_Thousands_Separator->Required) {
                if (!$this->Default_Thousands_Separator->IsDetailKey && IsEmpty($this->Default_Thousands_Separator->FormValue)) {
                    $this->Default_Thousands_Separator->addErrorMessage(str_replace("%s", $this->Default_Thousands_Separator->caption(), $this->Default_Thousands_Separator->RequiredErrorMessage));
                }
            }
            if ($this->Default_Decimal_Point->Visible && $this->Default_Decimal_Point->Required) {
                if (!$this->Default_Decimal_Point->IsDetailKey && IsEmpty($this->Default_Decimal_Point->FormValue)) {
                    $this->Default_Decimal_Point->addErrorMessage(str_replace("%s", $this->Default_Decimal_Point->caption(), $this->Default_Decimal_Point->RequiredErrorMessage));
                }
            }
            if ($this->Default_Currency_Symbol->Visible && $this->Default_Currency_Symbol->Required) {
                if (!$this->Default_Currency_Symbol->IsDetailKey && IsEmpty($this->Default_Currency_Symbol->FormValue)) {
                    $this->Default_Currency_Symbol->addErrorMessage(str_replace("%s", $this->Default_Currency_Symbol->caption(), $this->Default_Currency_Symbol->RequiredErrorMessage));
                }
            }
            if ($this->Default_Money_Thousands_Separator->Visible && $this->Default_Money_Thousands_Separator->Required) {
                if (!$this->Default_Money_Thousands_Separator->IsDetailKey && IsEmpty($this->Default_Money_Thousands_Separator->FormValue)) {
                    $this->Default_Money_Thousands_Separator->addErrorMessage(str_replace("%s", $this->Default_Money_Thousands_Separator->caption(), $this->Default_Money_Thousands_Separator->RequiredErrorMessage));
                }
            }
            if ($this->Default_Money_Decimal_Point->Visible && $this->Default_Money_Decimal_Point->Required) {
                if (!$this->Default_Money_Decimal_Point->IsDetailKey && IsEmpty($this->Default_Money_Decimal_Point->FormValue)) {
                    $this->Default_Money_Decimal_Point->addErrorMessage(str_replace("%s", $this->Default_Money_Decimal_Point->caption(), $this->Default_Money_Decimal_Point->RequiredErrorMessage));
                }
            }
            if ($this->Terms_And_Condition_Text->Visible && $this->Terms_And_Condition_Text->Required) {
                if (!$this->Terms_And_Condition_Text->IsDetailKey && IsEmpty($this->Terms_And_Condition_Text->FormValue)) {
                    $this->Terms_And_Condition_Text->addErrorMessage(str_replace("%s", $this->Terms_And_Condition_Text->caption(), $this->Terms_And_Condition_Text->RequiredErrorMessage));
                }
            }
            if ($this->Announcement_Text->Visible && $this->Announcement_Text->Required) {
                if (!$this->Announcement_Text->IsDetailKey && IsEmpty($this->Announcement_Text->FormValue)) {
                    $this->Announcement_Text->addErrorMessage(str_replace("%s", $this->Announcement_Text->caption(), $this->Announcement_Text->RequiredErrorMessage));
                }
            }
            if ($this->About_Text->Visible && $this->About_Text->Required) {
                if (!$this->About_Text->IsDetailKey && IsEmpty($this->About_Text->FormValue)) {
                    $this->About_Text->addErrorMessage(str_replace("%s", $this->About_Text->caption(), $this->About_Text->RequiredErrorMessage));
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

    // Update record based on key values
    protected function editRow(): bool
    {
        $oldKeyFilter = $this->getRecordFilter();
        $filter = $this->applyUserIDFilters($oldKeyFilter);
        $conn = $this->getConnection();

        // Load old row
        $this->CurrentFilter = $filter;
        $sql = $this->getCurrentSql();
        $oldRow = $conn->fetchAssociative($sql);
        if (!$oldRow) {
            $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
            return false; // Update Failed
        } else {
            // Load old values
            $this->loadDbValues($oldRow);
        }

        // Get new row
        $newRow = $this->getEditRow($oldRow);

        // Update current values
        $this->Fields->setCurrentValues($newRow);

        // Check field with unique index (Language_Code)
        if ($this->Language_Code->CurrentValue != "") {
            $filterChk = "(`Language_Code` = '" . AdjustSql($this->Language_Code->CurrentValue) . "')";
            $filterChk .= " AND NOT (" . $filter . ")";
            $this->CurrentFilter = $filterChk;
            $sqlChk = $this->getCurrentSql();
            $rsChk = $conn->executeQuery($sqlChk);
            if (!$rsChk) {
                return false;
            }
            if ($rsChk->fetchAssociative()) {
                $idxErrMsg = sprintf($this->language->phrase("DuplicateIndex"), $this->Language_Code->CurrentValue, $this->Language_Code->caption());
                $this->setFailureMessage($idxErrMsg);
                return false;
            }
        }

        // Call Row Updating event
        $updateRow = $this->rowUpdating($oldRow, $newRow);

        // Check for duplicate key when key changed
        if ($updateRow) {
            $newKeyFilter = $this->getRecordFilter($newRow);
            if ($newKeyFilter != $oldKeyFilter) {
                $rsChk = $this->loadRecords($newKeyFilter)->fetchAssociative();
                if ($rsChk !== false) {
                    $keyErrMsg = sprintf($this->language->phrase("DuplicateKey"), $newKeyFilter);
                    $this->setFailureMessage($keyErrMsg);
                    $updateRow = false;
                }
            }
        }
        if ($updateRow) {
            if (count($newRow) > 0) {
                $this->CurrentFilter = $filter; // Set up current filter
                $editRow = $this->update($newRow, "", $oldRow);
                if (!$editRow && !IsEmpty($this->DbErrorMessage)) { // Show database error
                    $this->setFailureMessage($this->DbErrorMessage);
                }
            } else {
                $editRow = true; // No field to update
            }
            if ($editRow) {
            }
        } else {
            if ($this->peekSuccessMessage() || $this->peekFailureMessage()) {
                // Use the message, do nothing
            } elseif ($this->CancelMessage != "") {
                $this->setFailureMessage($this->CancelMessage);
                $this->CancelMessage = "";
            } else {
                $this->setFailureMessage($this->language->phrase("UpdateCancelled"));
            }
            $editRow = $updateRow;
        }

        // Call Row_Updated event
        if ($editRow) {
            $this->rowUpdated($oldRow, $newRow);
        }

        // Write JSON response
        if (IsJsonResponse() && $editRow) {
            $row = $this->getRecordsFromResult([$newRow], true);
            $table = $this->TableVar;
            WriteJson(["success" => true, "action" => Config("API_EDIT_ACTION"), $table => $row]);
        }
        return $editRow;
    }

    /**
     * Get edit row
     *
     * @return array
     */
    protected function getEditRow(array $oldRow): array
    {
        $newRow = [];

        // Language_Code
        $this->Language_Code->setDbValueDef($newRow, $this->Language_Code->CurrentValue, $this->Language_Code->ReadOnly);

        // Language_Name
        $this->Language_Name->setDbValueDef($newRow, $this->Language_Name->CurrentValue, $this->Language_Name->ReadOnly);

        // Default
        $tmpBool = $this->Default->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Default->setDbValueDef($newRow, $tmpBool, $this->Default->ReadOnly);

        // Site_Logo
        $this->Site_Logo->setDbValueDef($newRow, $this->Site_Logo->CurrentValue, $this->Site_Logo->ReadOnly);

        // Site_Title
        $this->Site_Title->setDbValueDef($newRow, $this->Site_Title->CurrentValue, $this->Site_Title->ReadOnly);

        // Default_Thousands_Separator
        $this->Default_Thousands_Separator->setDbValueDef($newRow, $this->Default_Thousands_Separator->CurrentValue, $this->Default_Thousands_Separator->ReadOnly);

        // Default_Decimal_Point
        $this->Default_Decimal_Point->setDbValueDef($newRow, $this->Default_Decimal_Point->CurrentValue, $this->Default_Decimal_Point->ReadOnly);

        // Default_Currency_Symbol
        $this->Default_Currency_Symbol->setDbValueDef($newRow, $this->Default_Currency_Symbol->CurrentValue, $this->Default_Currency_Symbol->ReadOnly);

        // Default_Money_Thousands_Separator
        $this->Default_Money_Thousands_Separator->setDbValueDef($newRow, $this->Default_Money_Thousands_Separator->CurrentValue, $this->Default_Money_Thousands_Separator->ReadOnly);

        // Default_Money_Decimal_Point
        $this->Default_Money_Decimal_Point->setDbValueDef($newRow, $this->Default_Money_Decimal_Point->CurrentValue, $this->Default_Money_Decimal_Point->ReadOnly);

        // Terms_And_Condition_Text
        $this->Terms_And_Condition_Text->setDbValueDef($newRow, $this->Terms_And_Condition_Text->CurrentValue, $this->Terms_And_Condition_Text->ReadOnly);

        // Announcement_Text
        $this->Announcement_Text->setDbValueDef($newRow, $this->Announcement_Text->CurrentValue, $this->Announcement_Text->ReadOnly);

        // About_Text
        $this->About_Text->setDbValueDef($newRow, $this->About_Text->CurrentValue, $this->About_Text->ReadOnly);
        return $newRow;
    }

    /**
     * Restore edit form from row
     * @param array $row Row
     */
    protected function restoreEditFormFromRow(array $row): void
    {
        if (isset($row['Language_Code'])) { // Language_Code
            $this->Language_Code->CurrentValue = $row['Language_Code'];
        }
        if (isset($row['Language_Name'])) { // Language_Name
            $this->Language_Name->CurrentValue = $row['Language_Name'];
        }
        if (isset($row['Default'])) { // Default
            $this->Default->CurrentValue = $row['Default'];
        }
        if (isset($row['Site_Logo'])) { // Site_Logo
            $this->Site_Logo->CurrentValue = $row['Site_Logo'];
        }
        if (isset($row['Site_Title'])) { // Site_Title
            $this->Site_Title->CurrentValue = $row['Site_Title'];
        }
        if (isset($row['Default_Thousands_Separator'])) { // Default_Thousands_Separator
            $this->Default_Thousands_Separator->CurrentValue = $row['Default_Thousands_Separator'];
        }
        if (isset($row['Default_Decimal_Point'])) { // Default_Decimal_Point
            $this->Default_Decimal_Point->CurrentValue = $row['Default_Decimal_Point'];
        }
        if (isset($row['Default_Currency_Symbol'])) { // Default_Currency_Symbol
            $this->Default_Currency_Symbol->CurrentValue = $row['Default_Currency_Symbol'];
        }
        if (isset($row['Default_Money_Thousands_Separator'])) { // Default_Money_Thousands_Separator
            $this->Default_Money_Thousands_Separator->CurrentValue = $row['Default_Money_Thousands_Separator'];
        }
        if (isset($row['Default_Money_Decimal_Point'])) { // Default_Money_Decimal_Point
            $this->Default_Money_Decimal_Point->CurrentValue = $row['Default_Money_Decimal_Point'];
        }
        if (isset($row['Terms_And_Condition_Text'])) { // Terms_And_Condition_Text
            $this->Terms_And_Condition_Text->CurrentValue = $row['Terms_And_Condition_Text'];
        }
        if (isset($row['Announcement_Text'])) { // Announcement_Text
            $this->Announcement_Text->CurrentValue = $row['Announcement_Text'];
        }
        if (isset($row['About_Text'])) { // About_Text
            $this->About_Text->CurrentValue = $row['About_Text'];
        }
    }

    // Set up Breadcrumb
    protected function setupBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb();
        $url = CurrentUrl();
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("languageslist"), "", $this->TableVar, true);
        $pageId = "edit";
        $breadcrumb->add("edit", $pageId, $url);
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
                case "x_Default":
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

    // Set up starting record parameters
    public function setupStartRecord(): void
    {
        $pagerTable = Get(Config("TABLE_PAGER_TABLE_NAME"));
        if ($this->DisplayRecords == 0 || $pagerTable && $pagerTable != $this->TableVar) { // Display all records / Check if paging for this table
            return;
        }
        $pageNo = Get(Config("TABLE_PAGE_NUMBER"));
        $startRec = Get(Config("TABLE_START_REC"));
        $infiniteScroll = false;
        $recordNo = $pageNo ?? $startRec; // Record number = page number or start record
        if ($recordNo !== null && is_numeric($recordNo)) {
            $this->StartRecord = $recordNo;
        } else {
            $this->StartRecord = $this->getStartRecordNumber();
        }

        // Check if correct start record counter
        if (!is_numeric($this->StartRecord) || intval($this->StartRecord) <= 0) { // Avoid invalid start record counter
            $this->StartRecord = 1; // Reset start record counter
        } elseif ($this->StartRecord > $this->TotalRecords) { // Avoid starting record > total records
            $this->StartRecord = (int)(($this->TotalRecords - 1) / $this->DisplayRecords) * $this->DisplayRecords + 1; // Point to last page first record
        } elseif (($this->StartRecord - 1) % $this->DisplayRecords != 0) {
            $this->StartRecord = (int)(($this->StartRecord - 1) / $this->DisplayRecords) * $this->DisplayRecords + 1; // Point to page boundary
        }
        if (!$infiniteScroll) {
            $this->setStartRecordNumber($this->StartRecord);
        }
    }

    // Get page count
    public function pageCount(): int
    {
        return ceil($this->TotalRecords / $this->DisplayRecords);
    }

    // Page Load event
    public function pageLoad(): void
    {
        $this->Terms_And_Condition_Text->Raw = true;
        $this->Announcement_Text->Raw = true;
        $this->About_Text->Raw = true;
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
