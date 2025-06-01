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
class UsersAdd extends Users
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "add";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "UsersAdd";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "usersadd";

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
        $this->_UserID->Visible = false;
        $this->_Username->setVisibility();
        $this->_Password->setVisibility();
        $this->UserLevel->setVisibility();
        $this->FirstName->setVisibility();
        $this->LastName->setVisibility();
        $this->CompleteName->setVisibility();
        $this->BirthDate->setVisibility();
        $this->HomePhone->setVisibility();
        $this->Photo->setVisibility();
        $this->Notes->setVisibility();
        $this->ReportsTo->setVisibility();
        $this->Gender->setVisibility();
        $this->_Email->setVisibility();
        $this->Activated->setVisibility();
        $this->_Profile->Visible = false;
        $this->Avatar->setVisibility();
        $this->ActiveStatus->setVisibility();
        $this->MessengerColor->setVisibility();
        $this->CreatedAt->setVisibility();
        $this->CreatedBy->setVisibility();
        $this->UpdatedAt->setVisibility();
        $this->UpdatedBy->setVisibility();
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'users';
        $this->TableName = 'users';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-add-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (users)
        if (!isset($GLOBALS["users"]) || $GLOBALS["users"]::class == PROJECT_NAMESPACE . "users") {
            $GLOBALS["users"] = &$this;
        }

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'users');
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
                        $result["view"] = SameString($pageName, "usersview"); // If View page, no primary button
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
                $this->Photo->OldUploadPath = $this->Photo->getUploadPath(); // PHP
                $this->Photo->UploadPath = $this->Photo->OldUploadPath;
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
            $key .= @$ar['UserID'];
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
            $this->_UserID->Visible = false;
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
        $this->setupLookupOptions($this->UserLevel);
        $this->setupLookupOptions($this->Gender);
        $this->setupLookupOptions($this->Activated);
        $this->setupLookupOptions($this->ActiveStatus);
        $this->setupLookupOptions($this->CreatedBy);
        $this->setupLookupOptions($this->UpdatedBy);

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
            if (($keyValue = Get("_UserID") ?? Route("_UserID")) !== null) {
                $this->_UserID->setQueryStringValue($keyValue);
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
                    $this->terminate("userslist"); // No matching record, return to list
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
                    if (GetPageName($returnUrl) == "userslist") {
                        $returnUrl = $this->addMasterUrl($returnUrl); // List page, return to List page with correct master key if necessary
                    } elseif (GetPageName($returnUrl) == "usersview") {
                        $returnUrl = $this->getViewUrl(); // View page, return to View page with keyurl directly
                    }

                    // Handle UseAjaxActions
                    if ($this->IsModal && $this->UseAjaxActions && !$this->getCurrentMasterTable()) {
                        $this->IsModal = false;
                        if (GetPageName($returnUrl) != "userslist") {
                            FlashBag()->add("Return-Url", $returnUrl); // Save return URL
                            $returnUrl = "userslist"; // Return list page content
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
        $this->Photo->Upload->Index = $this->FormIndex;
        $this->Photo->Upload->uploadFile();
        $this->Photo->CurrentValue = $this->Photo->Upload->FileName;
        $this->Avatar->Upload->Index = $this->FormIndex;
        $this->Avatar->Upload->uploadFile();
        $this->Avatar->CurrentValue = $this->Avatar->Upload->FileName;
    }

    // Load default values
    protected function loadDefaultValues(): void
    {
        $this->UserLevel->DefaultValue = $this->UserLevel->getDefault(); // PHP
        $this->UserLevel->OldValue = $this->UserLevel->DefaultValue;
        $this->CreatedAt->DefaultValue = $this->CreatedAt->getDefault(); // PHP
        $this->CreatedAt->OldValue = $this->CreatedAt->DefaultValue;
        $this->CreatedBy->DefaultValue = $this->CreatedBy->getDefault(); // PHP
        $this->CreatedBy->OldValue = $this->CreatedBy->DefaultValue;
    }

    // Load form values
    protected function loadFormValues(): void
    {
        $validate = !Config("SERVER_VALIDATE");

        // Check field name 'Username' before field var 'x__Username'
        $val = $this->getFormValue("Username", null) ?? $this->getFormValue("x__Username", null);
        if (!$this->_Username->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->_Username->Visible = false; // Disable update for API request
            } else {
                $this->_Username->setFormValue($val);
            }
        }

        // Check field name 'Password' before field var 'x__Password'
        $val = $this->getFormValue("Password", null) ?? $this->getFormValue("x__Password", null);
        if (!$this->_Password->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->_Password->Visible = false; // Disable update for API request
            } else {
                $this->_Password->setFormValue($val);
            }
        }

        // Check field name 'UserLevel' before field var 'x_UserLevel'
        $val = $this->getFormValue("UserLevel", null) ?? $this->getFormValue("x_UserLevel", null);
        if (!$this->UserLevel->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->UserLevel->Visible = false; // Disable update for API request
            } else {
                $this->UserLevel->setFormValue($val);
            }
        }

        // Check field name 'FirstName' before field var 'x_FirstName'
        $val = $this->getFormValue("FirstName", null) ?? $this->getFormValue("x_FirstName", null);
        if (!$this->FirstName->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->FirstName->Visible = false; // Disable update for API request
            } else {
                $this->FirstName->setFormValue($val);
            }
        }

        // Check field name 'LastName' before field var 'x_LastName'
        $val = $this->getFormValue("LastName", null) ?? $this->getFormValue("x_LastName", null);
        if (!$this->LastName->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->LastName->Visible = false; // Disable update for API request
            } else {
                $this->LastName->setFormValue($val);
            }
        }

        // Check field name 'CompleteName' before field var 'x_CompleteName'
        $val = $this->getFormValue("CompleteName", null) ?? $this->getFormValue("x_CompleteName", null);
        if (!$this->CompleteName->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->CompleteName->Visible = false; // Disable update for API request
            } else {
                $this->CompleteName->setFormValue($val);
            }
        }

        // Check field name 'BirthDate' before field var 'x_BirthDate'
        $val = $this->getFormValue("BirthDate", null) ?? $this->getFormValue("x_BirthDate", null);
        if (!$this->BirthDate->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->BirthDate->Visible = false; // Disable update for API request
            } else {
                $this->BirthDate->setFormValue($val, true, $validate);
            }
            $this->BirthDate->CurrentValue = UnformatDateTime($this->BirthDate->CurrentValue, $this->BirthDate->formatPattern());
        }

        // Check field name 'HomePhone' before field var 'x_HomePhone'
        $val = $this->getFormValue("HomePhone", null) ?? $this->getFormValue("x_HomePhone", null);
        if (!$this->HomePhone->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->HomePhone->Visible = false; // Disable update for API request
            } else {
                $this->HomePhone->setFormValue($val);
            }
        }

        // Check field name 'Notes' before field var 'x_Notes'
        $val = $this->getFormValue("Notes", null) ?? $this->getFormValue("x_Notes", null);
        if (!$this->Notes->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Notes->Visible = false; // Disable update for API request
            } else {
                $this->Notes->setFormValue($val);
            }
        }

        // Check field name 'ReportsTo' before field var 'x_ReportsTo'
        $val = $this->getFormValue("ReportsTo", null) ?? $this->getFormValue("x_ReportsTo", null);
        if (!$this->ReportsTo->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->ReportsTo->Visible = false; // Disable update for API request
            } else {
                $this->ReportsTo->setFormValue($val, true, $validate);
            }
        }

        // Check field name 'Gender' before field var 'x_Gender'
        $val = $this->getFormValue("Gender", null) ?? $this->getFormValue("x_Gender", null);
        if (!$this->Gender->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Gender->Visible = false; // Disable update for API request
            } else {
                $this->Gender->setFormValue($val);
            }
        }

        // Check field name 'Email' before field var 'x__Email'
        $val = $this->getFormValue("Email", null) ?? $this->getFormValue("x__Email", null);
        if (!$this->_Email->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->_Email->Visible = false; // Disable update for API request
            } else {
                $this->_Email->setFormValue($val, true, $validate);
            }
        }

        // Check field name 'Activated' before field var 'x_Activated'
        $val = $this->getFormValue("Activated", null) ?? $this->getFormValue("x_Activated", null);
        if (!$this->Activated->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Activated->Visible = false; // Disable update for API request
            } else {
                $this->Activated->setFormValue($val);
            }
        }

        // Check field name 'ActiveStatus' before field var 'x_ActiveStatus'
        $val = $this->getFormValue("ActiveStatus", null) ?? $this->getFormValue("x_ActiveStatus", null);
        if (!$this->ActiveStatus->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->ActiveStatus->Visible = false; // Disable update for API request
            } else {
                $this->ActiveStatus->setFormValue($val);
            }
        }

        // Check field name 'MessengerColor' before field var 'x_MessengerColor'
        $val = $this->getFormValue("MessengerColor", null) ?? $this->getFormValue("x_MessengerColor", null);
        if (!$this->MessengerColor->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->MessengerColor->Visible = false; // Disable update for API request
            } else {
                $this->MessengerColor->setFormValue($val);
            }
        }

        // Check field name 'CreatedAt' before field var 'x_CreatedAt'
        $val = $this->getFormValue("CreatedAt", null) ?? $this->getFormValue("x_CreatedAt", null);
        if (!$this->CreatedAt->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->CreatedAt->Visible = false; // Disable update for API request
            } else {
                $this->CreatedAt->setFormValue($val, true, $validate);
            }
            $this->CreatedAt->CurrentValue = UnformatDateTime($this->CreatedAt->CurrentValue, $this->CreatedAt->formatPattern());
        }

        // Check field name 'CreatedBy' before field var 'x_CreatedBy'
        $val = $this->getFormValue("CreatedBy", null) ?? $this->getFormValue("x_CreatedBy", null);
        if (!$this->CreatedBy->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->CreatedBy->Visible = false; // Disable update for API request
            } else {
                $this->CreatedBy->setFormValue($val);
            }
        }

        // Check field name 'UpdatedAt' before field var 'x_UpdatedAt'
        $val = $this->getFormValue("UpdatedAt", null) ?? $this->getFormValue("x_UpdatedAt", null);
        if (!$this->UpdatedAt->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->UpdatedAt->Visible = false; // Disable update for API request
            } else {
                $this->UpdatedAt->setFormValue($val);
            }
            $this->UpdatedAt->CurrentValue = UnformatDateTime($this->UpdatedAt->CurrentValue, $this->UpdatedAt->formatPattern());
        }

        // Check field name 'UpdatedBy' before field var 'x_UpdatedBy'
        $val = $this->getFormValue("UpdatedBy", null) ?? $this->getFormValue("x_UpdatedBy", null);
        if (!$this->UpdatedBy->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->UpdatedBy->Visible = false; // Disable update for API request
            } else {
                $this->UpdatedBy->setFormValue($val);
            }
        }

        // Check field name 'UserID' first before field var 'x__UserID'
        $val = $this->hasFormValue("UserID") ? $this->getFormValue("UserID") : $this->getFormValue("x__UserID");
		$this->Photo->OldUploadPath = $this->Photo->getUploadPath(); // PHP
		$this->Photo->UploadPath = $this->Photo->OldUploadPath;
        $this->getUploadFiles(); // Get upload files
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        $this->_Username->CurrentValue = $this->_Username->FormValue;
        $this->_Password->CurrentValue = $this->_Password->FormValue;
        $this->UserLevel->CurrentValue = $this->UserLevel->FormValue;
        $this->FirstName->CurrentValue = $this->FirstName->FormValue;
        $this->LastName->CurrentValue = $this->LastName->FormValue;
        $this->CompleteName->CurrentValue = $this->CompleteName->FormValue;
        $this->BirthDate->CurrentValue = $this->BirthDate->FormValue;
        $this->BirthDate->CurrentValue = UnformatDateTime($this->BirthDate->CurrentValue, $this->BirthDate->formatPattern());
        $this->HomePhone->CurrentValue = $this->HomePhone->FormValue;
        $this->Notes->CurrentValue = $this->Notes->FormValue;
        $this->ReportsTo->CurrentValue = $this->ReportsTo->FormValue;
        $this->Gender->CurrentValue = $this->Gender->FormValue;
        $this->_Email->CurrentValue = $this->_Email->FormValue;
        $this->Activated->CurrentValue = $this->Activated->FormValue;
        $this->ActiveStatus->CurrentValue = $this->ActiveStatus->FormValue;
        $this->MessengerColor->CurrentValue = $this->MessengerColor->FormValue;
        $this->CreatedAt->CurrentValue = $this->CreatedAt->FormValue;
        $this->CreatedAt->CurrentValue = UnformatDateTime($this->CreatedAt->CurrentValue, $this->CreatedAt->formatPattern());
        $this->CreatedBy->CurrentValue = $this->CreatedBy->FormValue;
        $this->UpdatedAt->CurrentValue = $this->UpdatedAt->FormValue;
        $this->UpdatedAt->CurrentValue = UnformatDateTime($this->UpdatedAt->CurrentValue, $this->UpdatedAt->formatPattern());
        $this->UpdatedBy->CurrentValue = $this->UpdatedBy->FormValue;
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

        // Check if valid User ID
        if ($res) {
            $res = $this->showOptionLink("add");
            if (!$res) {
                $userIdMsg = DeniedMessage();
                $this->setFailureMessage($userIdMsg);
            }
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
        $this->_UserID->setDbValue($row['UserID']);
        $this->_Username->setDbValue($row['Username']);
        $this->_Password->setDbValue($row['Password']);
        $this->UserLevel->setDbValue($row['UserLevel']);
        $this->FirstName->setDbValue($row['FirstName']);
        $this->LastName->setDbValue($row['LastName']);
        $this->CompleteName->setDbValue($row['CompleteName']);
        $this->BirthDate->setDbValue($row['BirthDate']);
        $this->HomePhone->setDbValue($row['HomePhone']);
        $this->Photo->Upload->DbValue = $row['Photo'];
        $this->Photo->setDbValue($this->Photo->Upload->DbValue);
        $this->Notes->setDbValue($row['Notes']);
        $this->ReportsTo->setDbValue($row['ReportsTo']);
        $this->Gender->setDbValue($row['Gender']);
        $this->_Email->setDbValue($row['Email']);
        $this->Activated->setDbValue($row['Activated']);
        $this->_Profile->setDbValue($row['Profile']);
        $this->Avatar->Upload->DbValue = $row['Avatar'];
        $this->Avatar->setDbValue($this->Avatar->Upload->DbValue);
        $this->ActiveStatus->setDbValue($row['ActiveStatus']);
        $this->MessengerColor->setDbValue($row['MessengerColor']);
        $this->CreatedAt->setDbValue($row['CreatedAt']);
        $this->CreatedBy->setDbValue($row['CreatedBy']);
        $this->UpdatedAt->setDbValue($row['UpdatedAt']);
        $this->UpdatedBy->setDbValue($row['UpdatedBy']);
    }

    // Return a row with default values
    protected function newRow(): array
    {
        $row = [];
        $row['UserID'] = $this->_UserID->DefaultValue;
        $row['Username'] = $this->_Username->DefaultValue;
        $row['Password'] = $this->_Password->DefaultValue;
        $row['UserLevel'] = $this->UserLevel->DefaultValue;
        $row['FirstName'] = $this->FirstName->DefaultValue;
        $row['LastName'] = $this->LastName->DefaultValue;
        $row['CompleteName'] = $this->CompleteName->DefaultValue;
        $row['BirthDate'] = $this->BirthDate->DefaultValue;
        $row['HomePhone'] = $this->HomePhone->DefaultValue;
        $row['Photo'] = $this->Photo->DefaultValue;
        $row['Notes'] = $this->Notes->DefaultValue;
        $row['ReportsTo'] = $this->ReportsTo->DefaultValue;
        $row['Gender'] = $this->Gender->DefaultValue;
        $row['Email'] = $this->_Email->DefaultValue;
        $row['Activated'] = $this->Activated->DefaultValue;
        $row['Profile'] = $this->_Profile->DefaultValue;
        $row['Avatar'] = $this->Avatar->DefaultValue;
        $row['ActiveStatus'] = $this->ActiveStatus->DefaultValue;
        $row['MessengerColor'] = $this->MessengerColor->DefaultValue;
        $row['CreatedAt'] = $this->CreatedAt->DefaultValue;
        $row['CreatedBy'] = $this->CreatedBy->DefaultValue;
        $row['UpdatedAt'] = $this->UpdatedAt->DefaultValue;
        $row['UpdatedBy'] = $this->UpdatedBy->DefaultValue;
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

        // UserID
        $this->_UserID->RowCssClass = "row";

        // Username
        $this->_Username->RowCssClass = "row";

        // Password
        $this->_Password->RowCssClass = "row";

        // UserLevel
        $this->UserLevel->RowCssClass = "row";

        // FirstName
        $this->FirstName->RowCssClass = "row";

        // LastName
        $this->LastName->RowCssClass = "row";

        // CompleteName
        $this->CompleteName->RowCssClass = "row";

        // BirthDate
        $this->BirthDate->RowCssClass = "row";

        // HomePhone
        $this->HomePhone->RowCssClass = "row";

        // Photo
        $this->Photo->RowCssClass = "row";

        // Notes
        $this->Notes->RowCssClass = "row";

        // ReportsTo
        $this->ReportsTo->RowCssClass = "row";

        // Gender
        $this->Gender->RowCssClass = "row";

        // Email
        $this->_Email->RowCssClass = "row";

        // Activated
        $this->Activated->RowCssClass = "row";

        // Profile
        $this->_Profile->RowCssClass = "row";

        // Avatar
        $this->Avatar->RowCssClass = "row";

        // ActiveStatus
        $this->ActiveStatus->RowCssClass = "row";

        // MessengerColor
        $this->MessengerColor->RowCssClass = "row";

        // CreatedAt
        $this->CreatedAt->RowCssClass = "row";

        // CreatedBy
        $this->CreatedBy->RowCssClass = "row";

        // UpdatedAt
        $this->UpdatedAt->RowCssClass = "row";

        // UpdatedBy
        $this->UpdatedBy->RowCssClass = "row";

        // View row
        if ($this->RowType == RowType::VIEW) {
            // UserID
            $this->_UserID->ViewValue = $this->_UserID->CurrentValue;

            // Username
            $this->_Username->ViewValue = $this->_Username->CurrentValue;

            // Password
            $this->_Password->ViewValue = $this->language->phrase("PasswordMask");

            // UserLevel
            if ($this->security->canAdmin()) { // System admin
                $curVal = strval($this->UserLevel->CurrentValue);
                if ($curVal != "") {
                    $this->UserLevel->ViewValue = $this->UserLevel->lookupCacheOption($curVal);
                    if ($this->UserLevel->ViewValue === null) { // Lookup from database
                        $filterWrk = SearchFilter($this->UserLevel->Lookup->getTable()->Fields["ID"]->searchExpression(), "=", $curVal, $this->UserLevel->Lookup->getTable()->Fields["ID"]->searchDataType(), "DB");
                        $sqlWrk = $this->UserLevel->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                        $conn = Conn();
                        $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                        $ari = count($rswrk);
                        if ($ari > 0) { // Lookup values found
                            $rows = [];
                            foreach ($rswrk as $row) {
                                $rows[] = $this->UserLevel->Lookup->renderViewRow($row);
                            }
                            $this->UserLevel->ViewValue = $this->UserLevel->displayValue($rows[0]);
                        } else {
                            $this->UserLevel->ViewValue = FormatNumber($this->UserLevel->CurrentValue, $this->UserLevel->formatPattern());
                        }
                    }
                } else {
                    $this->UserLevel->ViewValue = null;
                }
            } else {
                $this->UserLevel->ViewValue = $this->language->phrase("PasswordMask");
            }

            // FirstName
            $this->FirstName->ViewValue = $this->FirstName->CurrentValue;

            // LastName
            $this->LastName->ViewValue = $this->LastName->CurrentValue;

            // CompleteName
            $this->CompleteName->ViewValue = $this->CompleteName->CurrentValue;

            // BirthDate
            $this->BirthDate->ViewValue = $this->BirthDate->CurrentValue;
            $this->BirthDate->ViewValue = FormatDateTime($this->BirthDate->ViewValue, $this->BirthDate->formatPattern());

            // HomePhone
            $this->HomePhone->ViewValue = $this->HomePhone->CurrentValue;

            // Photo
            $this->Photo->UploadPath = $this->Photo->getUploadPath(); // PHP
            if (!IsEmpty($this->Photo->Upload->DbValue)) {
                $this->Photo->ImageWidth = 0;
                $this->Photo->ImageHeight = 70;
                $this->Photo->ImageAlt = $this->Photo->alt();
                $this->Photo->ImageCssClass = "ew-image";
                $this->Photo->ViewValue = $this->Photo->Upload->DbValue;
            } else {
                $this->Photo->ViewValue = "";
            }

            // Notes
            $this->Notes->ViewValue = $this->Notes->CurrentValue;

            // ReportsTo
            $this->ReportsTo->ViewValue = $this->ReportsTo->CurrentValue;
            $this->ReportsTo->ViewValue = FormatNumber($this->ReportsTo->ViewValue, $this->ReportsTo->formatPattern());

            // Gender
            if (strval($this->Gender->CurrentValue) != "") {
                $this->Gender->ViewValue = $this->Gender->optionCaption($this->Gender->CurrentValue);
            } else {
                $this->Gender->ViewValue = null;
            }

            // Email
            $this->_Email->ViewValue = $this->_Email->CurrentValue;

            // Activated
            if (ConvertToBool($this->Activated->CurrentValue)) {
                $this->Activated->ViewValue = $this->Activated->tagCaption(1) != "" ? $this->Activated->tagCaption(1) : "Yes";
            } else {
                $this->Activated->ViewValue = $this->Activated->tagCaption(2) != "" ? $this->Activated->tagCaption(2) : "No";
            }

            // Avatar
            if (!IsEmpty($this->Avatar->Upload->DbValue)) {
                $this->Avatar->ImageAlt = $this->Avatar->alt();
                $this->Avatar->ImageCssClass = "ew-image";
                $this->Avatar->ViewValue = $this->Avatar->Upload->DbValue;
            } else {
                $this->Avatar->ViewValue = "";
            }

            // ActiveStatus
            if (ConvertToBool($this->ActiveStatus->CurrentValue)) {
                $this->ActiveStatus->ViewValue = $this->ActiveStatus->tagCaption(1) != "" ? $this->ActiveStatus->tagCaption(1) : "Yes";
            } else {
                $this->ActiveStatus->ViewValue = $this->ActiveStatus->tagCaption(2) != "" ? $this->ActiveStatus->tagCaption(2) : "No";
            }

            // MessengerColor
            $this->MessengerColor->ViewValue = $this->MessengerColor->CurrentValue;

            // CreatedAt
            $this->CreatedAt->ViewValue = $this->CreatedAt->CurrentValue;
            $this->CreatedAt->ViewValue = FormatDateTime($this->CreatedAt->ViewValue, $this->CreatedAt->formatPattern());

            // CreatedBy
            $curVal = strval($this->CreatedBy->CurrentValue);
            if ($curVal != "") {
                $this->CreatedBy->ViewValue = $this->CreatedBy->lookupCacheOption($curVal);
                if ($this->CreatedBy->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->CreatedBy->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $curVal, $this->CreatedBy->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
                    $sqlWrk = $this->CreatedBy->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->CreatedBy->Lookup->renderViewRow($row);
                        }
                        $this->CreatedBy->ViewValue = $this->CreatedBy->displayValue($rows[0]);
                    } else {
                        $this->CreatedBy->ViewValue = $this->CreatedBy->CurrentValue;
                    }
                }
            } else {
                $this->CreatedBy->ViewValue = null;
            }

            // UpdatedAt
            $this->UpdatedAt->ViewValue = $this->UpdatedAt->CurrentValue;
            $this->UpdatedAt->ViewValue = FormatDateTime($this->UpdatedAt->ViewValue, $this->UpdatedAt->formatPattern());

            // UpdatedBy
            $curVal = strval($this->UpdatedBy->CurrentValue);
            if ($curVal != "") {
                $this->UpdatedBy->ViewValue = $this->UpdatedBy->lookupCacheOption($curVal);
                if ($this->UpdatedBy->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->UpdatedBy->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $curVal, $this->UpdatedBy->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
                    $sqlWrk = $this->UpdatedBy->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->UpdatedBy->Lookup->renderViewRow($row);
                        }
                        $this->UpdatedBy->ViewValue = $this->UpdatedBy->displayValue($rows[0]);
                    } else {
                        $this->UpdatedBy->ViewValue = $this->UpdatedBy->CurrentValue;
                    }
                }
            } else {
                $this->UpdatedBy->ViewValue = null;
            }

            // Username
            $this->_Username->HrefValue = "";

            // Password
            $this->_Password->HrefValue = "";

            // UserLevel
            $this->UserLevel->HrefValue = "";

            // FirstName
            $this->FirstName->HrefValue = "";

            // LastName
            $this->LastName->HrefValue = "";

            // CompleteName
            $this->CompleteName->HrefValue = "";

            // BirthDate
            $this->BirthDate->HrefValue = "";

            // HomePhone
            $this->HomePhone->HrefValue = "";

            // Photo
            $this->Photo->UploadPath = $this->Photo->getUploadPath(); // PHP
            if (!IsEmpty($this->Photo->Upload->DbValue)) {
                $this->Photo->HrefValue = GetFileUploadUrl($this->Photo, $this->Photo->htmlDecode($this->Photo->Upload->DbValue)); // Add prefix/suffix
                $this->Photo->LinkAttrs["target"] = ""; // Add target
                if ($this->isExport()) {
                    $this->Photo->HrefValue = FullUrl($this->Photo->HrefValue, "href");
                }
            } else {
                $this->Photo->HrefValue = "";
            }
            $this->Photo->ExportHrefValue = $this->Photo->UploadPath . $this->Photo->Upload->DbValue;

            // Notes
            $this->Notes->HrefValue = "";

            // ReportsTo
            $this->ReportsTo->HrefValue = "";

            // Gender
            $this->Gender->HrefValue = "";

            // Email
            $this->_Email->HrefValue = "";

            // Activated
            $this->Activated->HrefValue = "";

            // Avatar
            if (!IsEmpty($this->Avatar->Upload->DbValue)) {
                $this->Avatar->HrefValue = GetFileUploadUrl($this->Avatar, $this->Avatar->htmlDecode($this->Avatar->Upload->DbValue)); // Add prefix/suffix
                $this->Avatar->LinkAttrs["target"] = ""; // Add target
                if ($this->isExport()) {
                    $this->Avatar->HrefValue = FullUrl($this->Avatar->HrefValue, "href");
                }
            } else {
                $this->Avatar->HrefValue = "";
            }
            $this->Avatar->ExportHrefValue = $this->Avatar->UploadPath . $this->Avatar->Upload->DbValue;

            // ActiveStatus
            $this->ActiveStatus->HrefValue = "";

            // MessengerColor
            $this->MessengerColor->HrefValue = "";

            // CreatedAt
            $this->CreatedAt->HrefValue = "";
            $this->CreatedAt->TooltipValue = "";

            // CreatedBy
            $this->CreatedBy->HrefValue = "";
            $this->CreatedBy->TooltipValue = "";

            // UpdatedAt
            $this->UpdatedAt->HrefValue = "";

            // UpdatedBy
            $this->UpdatedBy->HrefValue = "";
        } elseif ($this->RowType == RowType::ADD) {
            // Username
            $this->_Username->setupEditAttributes();
            $this->_Username->EditValue = !$this->_Username->Raw ? HtmlDecode($this->_Username->CurrentValue) : $this->_Username->CurrentValue;
            $this->_Username->PlaceHolder = RemoveHtml($this->_Username->caption());

            // Password
            $this->_Password->setupEditAttributes(["class" => "ew-password-strength"]);
            $this->_Password->PlaceHolder = RemoveHtml($this->_Password->caption());

            // UserLevel
            $this->UserLevel->setupEditAttributes();
            if ($this->UserLevel->getSessionValue() != "") {
                $this->UserLevel->CurrentValue = GetForeignKeyValue($this->UserLevel->getSessionValue());
                if ($this->security->canAdmin()) { // System admin
                    $curVal = strval($this->UserLevel->CurrentValue);
                    if ($curVal != "") {
                        $this->UserLevel->ViewValue = $this->UserLevel->lookupCacheOption($curVal);
                        if ($this->UserLevel->ViewValue === null) { // Lookup from database
                            $filterWrk = SearchFilter($this->UserLevel->Lookup->getTable()->Fields["ID"]->searchExpression(), "=", $curVal, $this->UserLevel->Lookup->getTable()->Fields["ID"]->searchDataType(), "DB");
                            $sqlWrk = $this->UserLevel->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                            $conn = Conn();
                            $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                            $ari = count($rswrk);
                            if ($ari > 0) { // Lookup values found
                                $rows = [];
                                foreach ($rswrk as $row) {
                                    $rows[] = $this->UserLevel->Lookup->renderViewRow($row);
                                }
                                $this->UserLevel->ViewValue = $this->UserLevel->displayValue($rows[0]);
                            } else {
                                $this->UserLevel->ViewValue = FormatNumber($this->UserLevel->CurrentValue, $this->UserLevel->formatPattern());
                            }
                        }
                    } else {
                        $this->UserLevel->ViewValue = null;
                    }
                } else {
                    $this->UserLevel->ViewValue = $this->language->phrase("PasswordMask");
                }
            } elseif (!$this->security->canAdmin()) { // System admin
                $this->UserLevel->EditValue = $this->language->phrase("PasswordMask");
            } else {
                $curVal = trim(strval($this->UserLevel->CurrentValue));
                if ($curVal != "") {
                    $this->UserLevel->ViewValue = $this->UserLevel->lookupCacheOption($curVal);
                } else {
                    $this->UserLevel->ViewValue = $this->UserLevel->Lookup !== null && is_array($this->UserLevel->lookupOptions()) && count($this->UserLevel->lookupOptions()) > 0 ? $curVal : null;
                }
                if ($this->UserLevel->ViewValue !== null) { // Load from cache
                    $this->UserLevel->EditValue = array_values($this->UserLevel->lookupOptions());
                } else { // Lookup from database
                    if ($curVal == "") {
                        $filterWrk = "0=1";
                    } else {
                        $filterWrk = SearchFilter($this->UserLevel->Lookup->getTable()->Fields["ID"]->searchExpression(), "=", $this->UserLevel->CurrentValue, $this->UserLevel->Lookup->getTable()->Fields["ID"]->searchDataType(), "DB");
                    }
                    $sqlWrk = $this->UserLevel->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    $rows = [];
                    if ($ari > 0) { // Lookup values found
                        foreach ($rswrk as $row) {
                            $rows[] = $this->UserLevel->Lookup->renderViewRow($row);
                        }
                    } else {
                        $this->UserLevel->ViewValue = $this->language->phrase("PleaseSelect");
                    }
                    $this->UserLevel->EditValue = $rows;
                }
                $this->UserLevel->PlaceHolder = RemoveHtml($this->UserLevel->caption());
            }

            // FirstName
            $this->FirstName->setupEditAttributes();
            $this->FirstName->EditValue = !$this->FirstName->Raw ? HtmlDecode($this->FirstName->CurrentValue) : $this->FirstName->CurrentValue;
            $this->FirstName->PlaceHolder = RemoveHtml($this->FirstName->caption());

            // LastName
            $this->LastName->setupEditAttributes();
            $this->LastName->EditValue = !$this->LastName->Raw ? HtmlDecode($this->LastName->CurrentValue) : $this->LastName->CurrentValue;
            $this->LastName->PlaceHolder = RemoveHtml($this->LastName->caption());

            // CompleteName
            $this->CompleteName->setupEditAttributes();
            $this->CompleteName->EditValue = !$this->CompleteName->Raw ? HtmlDecode($this->CompleteName->CurrentValue) : $this->CompleteName->CurrentValue;
            $this->CompleteName->PlaceHolder = RemoveHtml($this->CompleteName->caption());

            // BirthDate
            $this->BirthDate->setupEditAttributes();
            $this->BirthDate->EditValue = FormatDateTime($this->BirthDate->CurrentValue, $this->BirthDate->formatPattern());
            $this->BirthDate->PlaceHolder = RemoveHtml($this->BirthDate->caption());

            // HomePhone
            $this->HomePhone->setupEditAttributes();
            $this->HomePhone->EditValue = !$this->HomePhone->Raw ? HtmlDecode($this->HomePhone->CurrentValue) : $this->HomePhone->CurrentValue;
            $this->HomePhone->PlaceHolder = RemoveHtml($this->HomePhone->caption());

            // Photo
            $this->Photo->setupEditAttributes();
            $this->Photo->UploadPath = $this->Photo->getUploadPath(); // PHP
            if (!IsEmpty($this->Photo->Upload->DbValue)) {
                $this->Photo->ImageWidth = 0;
                $this->Photo->ImageHeight = 70;
                $this->Photo->ImageAlt = $this->Photo->alt();
                $this->Photo->ImageCssClass = "ew-image";
                $this->Photo->EditValue = $this->Photo->Upload->DbValue;
            } else {
                $this->Photo->EditValue = "";
            }
            if (!IsEmpty($this->Photo->CurrentValue)) {
                $this->Photo->Upload->FileName = $this->Photo->CurrentValue;
            }
            if (!Config("CREATE_UPLOAD_FILE_ON_COPY")) {
                $this->Photo->Upload->DbValue = null;
            }
            if ($this->isShow() || $this->isCopy()) {
                $this->Photo->Upload->setupTempDirectory();
            }

            // Notes
            $this->Notes->setupEditAttributes();
            $this->Notes->EditValue = $this->Notes->CurrentValue;
            $this->Notes->PlaceHolder = RemoveHtml($this->Notes->caption());

            // ReportsTo
            $this->ReportsTo->setupEditAttributes();
            if (!$this->security->canAccess() && $this->security->isLoggedIn()) { // No access permission
            } else {
                $this->ReportsTo->EditValue = $this->ReportsTo->CurrentValue;
                $this->ReportsTo->PlaceHolder = RemoveHtml($this->ReportsTo->caption());
                if (strval($this->ReportsTo->EditValue) != "" && is_numeric($this->ReportsTo->EditValue)) {
                    $this->ReportsTo->EditValue = FormatNumber($this->ReportsTo->EditValue, null);
                }
            }

            // Gender
            $this->Gender->setupEditAttributes();
            $this->Gender->EditValue = $this->Gender->options(true);
            $this->Gender->PlaceHolder = RemoveHtml($this->Gender->caption());

            // Email
            $this->_Email->setupEditAttributes();
            $this->_Email->EditValue = !$this->_Email->Raw ? HtmlDecode($this->_Email->CurrentValue) : $this->_Email->CurrentValue;
            $this->_Email->PlaceHolder = RemoveHtml($this->_Email->caption());

            // Activated
            $this->Activated->EditValue = $this->Activated->options(false);
            $this->Activated->PlaceHolder = RemoveHtml($this->Activated->caption());

            // Avatar
            $this->Avatar->setupEditAttributes();
            if (!IsEmpty($this->Avatar->Upload->DbValue)) {
                $this->Avatar->ImageAlt = $this->Avatar->alt();
                $this->Avatar->ImageCssClass = "ew-image";
                $this->Avatar->EditValue = $this->Avatar->Upload->DbValue;
            } else {
                $this->Avatar->EditValue = "";
            }
            if (!IsEmpty($this->Avatar->CurrentValue)) {
                $this->Avatar->Upload->FileName = $this->Avatar->CurrentValue;
            }
            if (!Config("CREATE_UPLOAD_FILE_ON_COPY")) {
                $this->Avatar->Upload->DbValue = null;
            }
            if ($this->isShow() || $this->isCopy()) {
                $this->Avatar->Upload->setupTempDirectory();
            }

            // ActiveStatus
            $this->ActiveStatus->EditValue = $this->ActiveStatus->options(false);
            $this->ActiveStatus->PlaceHolder = RemoveHtml($this->ActiveStatus->caption());

            // MessengerColor
            $this->MessengerColor->setupEditAttributes();
            $this->MessengerColor->EditValue = !$this->MessengerColor->Raw ? HtmlDecode($this->MessengerColor->CurrentValue) : $this->MessengerColor->CurrentValue;
            $this->MessengerColor->PlaceHolder = RemoveHtml($this->MessengerColor->caption());

            // CreatedAt
            $this->CreatedAt->setupEditAttributes();
            $this->CreatedAt->EditValue = FormatDateTime($this->CreatedAt->CurrentValue, $this->CreatedAt->formatPattern());
            $this->CreatedAt->PlaceHolder = RemoveHtml($this->CreatedAt->caption());

            // CreatedBy
            $this->CreatedBy->setupEditAttributes();
            $curVal = trim(strval($this->CreatedBy->CurrentValue));
            if ($curVal != "") {
                $this->CreatedBy->ViewValue = $this->CreatedBy->lookupCacheOption($curVal);
            } else {
                $this->CreatedBy->ViewValue = $this->CreatedBy->Lookup !== null && is_array($this->CreatedBy->lookupOptions()) && count($this->CreatedBy->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->CreatedBy->ViewValue !== null) { // Load from cache
                $this->CreatedBy->EditValue = array_values($this->CreatedBy->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->CreatedBy->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $this->CreatedBy->CurrentValue, $this->CreatedBy->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
                }
                $sqlWrk = $this->CreatedBy->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                $conn = Conn();
                $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                $ari = count($rswrk);
                $rows = [];
                if ($ari > 0) { // Lookup values found
                    foreach ($rswrk as $row) {
                        $rows[] = $this->CreatedBy->Lookup->renderViewRow($row);
                    }
                } else {
                    $this->CreatedBy->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->CreatedBy->EditValue = $rows;
            }
            $this->CreatedBy->PlaceHolder = RemoveHtml($this->CreatedBy->caption());

            // UpdatedAt

            // UpdatedBy

            // Add refer script

            // Username
            $this->_Username->HrefValue = "";

            // Password
            $this->_Password->HrefValue = "";

            // UserLevel
            $this->UserLevel->HrefValue = "";

            // FirstName
            $this->FirstName->HrefValue = "";

            // LastName
            $this->LastName->HrefValue = "";

            // CompleteName
            $this->CompleteName->HrefValue = "";

            // BirthDate
            $this->BirthDate->HrefValue = "";

            // HomePhone
            $this->HomePhone->HrefValue = "";

            // Photo
            $this->Photo->UploadPath = $this->Photo->getUploadPath(); // PHP
            if (!IsEmpty($this->Photo->Upload->DbValue)) {
                $this->Photo->HrefValue = GetFileUploadUrl($this->Photo, $this->Photo->htmlDecode($this->Photo->Upload->DbValue)); // Add prefix/suffix
                $this->Photo->LinkAttrs["target"] = ""; // Add target
                if ($this->isExport()) {
                    $this->Photo->HrefValue = FullUrl($this->Photo->HrefValue, "href");
                }
            } else {
                $this->Photo->HrefValue = "";
            }
            $this->Photo->ExportHrefValue = $this->Photo->UploadPath . $this->Photo->Upload->DbValue;

            // Notes
            $this->Notes->HrefValue = "";

            // ReportsTo
            $this->ReportsTo->HrefValue = "";

            // Gender
            $this->Gender->HrefValue = "";

            // Email
            $this->_Email->HrefValue = "";

            // Activated
            $this->Activated->HrefValue = "";

            // Avatar
            if (!IsEmpty($this->Avatar->Upload->DbValue)) {
                $this->Avatar->HrefValue = GetFileUploadUrl($this->Avatar, $this->Avatar->htmlDecode($this->Avatar->Upload->DbValue)); // Add prefix/suffix
                $this->Avatar->LinkAttrs["target"] = ""; // Add target
                if ($this->isExport()) {
                    $this->Avatar->HrefValue = FullUrl($this->Avatar->HrefValue, "href");
                }
            } else {
                $this->Avatar->HrefValue = "";
            }
            $this->Avatar->ExportHrefValue = $this->Avatar->UploadPath . $this->Avatar->Upload->DbValue;

            // ActiveStatus
            $this->ActiveStatus->HrefValue = "";

            // MessengerColor
            $this->MessengerColor->HrefValue = "";

            // CreatedAt
            $this->CreatedAt->HrefValue = "";

            // CreatedBy
            $this->CreatedBy->HrefValue = "";

            // UpdatedAt
            $this->UpdatedAt->HrefValue = "";

            // UpdatedBy
            $this->UpdatedBy->HrefValue = "";
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
            if ($this->_Username->Visible && $this->_Username->Required) {
                if (!$this->_Username->IsDetailKey && IsEmpty($this->_Username->FormValue)) {
                    $this->_Username->addErrorMessage(str_replace("%s", $this->_Username->caption(), $this->_Username->RequiredErrorMessage));
                }
            }
            if (!$this->_Username->Raw && Config("REMOVE_XSS") && CheckUsername($this->_Username->FormValue)) {
                $this->_Username->addErrorMessage($this->language->phrase("InvalidUsernameChars"));
            }
            if ($this->_Password->Visible && $this->_Password->Required) {
                if (!$this->_Password->IsDetailKey && IsEmpty($this->_Password->FormValue)) {
                    $this->_Password->addErrorMessage(str_replace("%s", $this->_Password->caption(), $this->_Password->RequiredErrorMessage));
                }
            }
            if (!$this->_Password->Raw && Config("REMOVE_XSS") && CheckPassword($this->_Password->FormValue)) {
                $this->_Password->addErrorMessage($this->language->phrase("InvalidPasswordChars"));
            }
            if ($this->UserLevel->Visible && $this->UserLevel->Required) {
                if ($this->security->canAdmin() && !$this->UserLevel->IsDetailKey && IsEmpty($this->UserLevel->FormValue)) {
                    $this->UserLevel->addErrorMessage(str_replace("%s", $this->UserLevel->caption(), $this->UserLevel->RequiredErrorMessage));
                }
            }
            if ($this->FirstName->Visible && $this->FirstName->Required) {
                if (!$this->FirstName->IsDetailKey && IsEmpty($this->FirstName->FormValue)) {
                    $this->FirstName->addErrorMessage(str_replace("%s", $this->FirstName->caption(), $this->FirstName->RequiredErrorMessage));
                }
            }
            if ($this->LastName->Visible && $this->LastName->Required) {
                if (!$this->LastName->IsDetailKey && IsEmpty($this->LastName->FormValue)) {
                    $this->LastName->addErrorMessage(str_replace("%s", $this->LastName->caption(), $this->LastName->RequiredErrorMessage));
                }
            }
            if ($this->CompleteName->Visible && $this->CompleteName->Required) {
                if (!$this->CompleteName->IsDetailKey && IsEmpty($this->CompleteName->FormValue)) {
                    $this->CompleteName->addErrorMessage(str_replace("%s", $this->CompleteName->caption(), $this->CompleteName->RequiredErrorMessage));
                }
            }
            if ($this->BirthDate->Visible && $this->BirthDate->Required) {
                if (!$this->BirthDate->IsDetailKey && IsEmpty($this->BirthDate->FormValue)) {
                    $this->BirthDate->addErrorMessage(str_replace("%s", $this->BirthDate->caption(), $this->BirthDate->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->BirthDate->FormValue, $this->BirthDate->formatPattern())) {
                $this->BirthDate->addErrorMessage($this->BirthDate->getErrorMessage(false));
            }
            if ($this->HomePhone->Visible && $this->HomePhone->Required) {
                if (!$this->HomePhone->IsDetailKey && IsEmpty($this->HomePhone->FormValue)) {
                    $this->HomePhone->addErrorMessage(str_replace("%s", $this->HomePhone->caption(), $this->HomePhone->RequiredErrorMessage));
                }
            }
            if ($this->Photo->Visible && $this->Photo->Required) {
                if ($this->Photo->Upload->FileName == "" && !$this->Photo->Upload->KeepFile) {
                    $this->Photo->addErrorMessage(str_replace("%s", $this->Photo->caption(), $this->Photo->RequiredErrorMessage));
                }
            }
            if ($this->Notes->Visible && $this->Notes->Required) {
                if (!$this->Notes->IsDetailKey && IsEmpty($this->Notes->FormValue)) {
                    $this->Notes->addErrorMessage(str_replace("%s", $this->Notes->caption(), $this->Notes->RequiredErrorMessage));
                }
            }
            if ($this->ReportsTo->Visible && $this->ReportsTo->Required) {
                if (!$this->ReportsTo->IsDetailKey && IsEmpty($this->ReportsTo->FormValue)) {
                    $this->ReportsTo->addErrorMessage(str_replace("%s", $this->ReportsTo->caption(), $this->ReportsTo->RequiredErrorMessage));
                }
            }
            if (!CheckInteger($this->ReportsTo->FormValue)) {
                $this->ReportsTo->addErrorMessage($this->ReportsTo->getErrorMessage(false));
            }
            if ($this->Gender->Visible && $this->Gender->Required) {
                if (!$this->Gender->IsDetailKey && IsEmpty($this->Gender->FormValue)) {
                    $this->Gender->addErrorMessage(str_replace("%s", $this->Gender->caption(), $this->Gender->RequiredErrorMessage));
                }
            }
            if ($this->_Email->Visible && $this->_Email->Required) {
                if (!$this->_Email->IsDetailKey && IsEmpty($this->_Email->FormValue)) {
                    $this->_Email->addErrorMessage(str_replace("%s", $this->_Email->caption(), $this->_Email->RequiredErrorMessage));
                }
            }
            if (!CheckEmail($this->_Email->FormValue)) {
                $this->_Email->addErrorMessage($this->_Email->getErrorMessage(false));
            }
            if ($this->Activated->Visible && $this->Activated->Required) {
                if ($this->Activated->FormValue == "") {
                    $this->Activated->addErrorMessage(str_replace("%s", $this->Activated->caption(), $this->Activated->RequiredErrorMessage));
                }
            }
            if ($this->Avatar->Visible && $this->Avatar->Required) {
                if ($this->Avatar->Upload->FileName == "" && !$this->Avatar->Upload->KeepFile) {
                    $this->Avatar->addErrorMessage(str_replace("%s", $this->Avatar->caption(), $this->Avatar->RequiredErrorMessage));
                }
            }
            if ($this->ActiveStatus->Visible && $this->ActiveStatus->Required) {
                if ($this->ActiveStatus->FormValue == "") {
                    $this->ActiveStatus->addErrorMessage(str_replace("%s", $this->ActiveStatus->caption(), $this->ActiveStatus->RequiredErrorMessage));
                }
            }
            if ($this->MessengerColor->Visible && $this->MessengerColor->Required) {
                if (!$this->MessengerColor->IsDetailKey && IsEmpty($this->MessengerColor->FormValue)) {
                    $this->MessengerColor->addErrorMessage(str_replace("%s", $this->MessengerColor->caption(), $this->MessengerColor->RequiredErrorMessage));
                }
            }
            if ($this->CreatedAt->Visible && $this->CreatedAt->Required) {
                if (!$this->CreatedAt->IsDetailKey && IsEmpty($this->CreatedAt->FormValue)) {
                    $this->CreatedAt->addErrorMessage(str_replace("%s", $this->CreatedAt->caption(), $this->CreatedAt->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->CreatedAt->FormValue, $this->CreatedAt->formatPattern())) {
                $this->CreatedAt->addErrorMessage($this->CreatedAt->getErrorMessage(false));
            }
            if ($this->CreatedBy->Visible && $this->CreatedBy->Required) {
                if (!$this->CreatedBy->IsDetailKey && IsEmpty($this->CreatedBy->FormValue)) {
                    $this->CreatedBy->addErrorMessage(str_replace("%s", $this->CreatedBy->caption(), $this->CreatedBy->RequiredErrorMessage));
                }
            }
            if ($this->UpdatedAt->Visible && $this->UpdatedAt->Required) {
                if (!$this->UpdatedAt->IsDetailKey && IsEmpty($this->UpdatedAt->FormValue)) {
                    $this->UpdatedAt->addErrorMessage(str_replace("%s", $this->UpdatedAt->caption(), $this->UpdatedAt->RequiredErrorMessage));
                }
            }
            if ($this->UpdatedBy->Visible && $this->UpdatedBy->Required) {
                if (!$this->UpdatedBy->IsDetailKey && IsEmpty($this->UpdatedBy->FormValue)) {
                    $this->UpdatedBy->addErrorMessage(str_replace("%s", $this->UpdatedBy->caption(), $this->UpdatedBy->RequiredErrorMessage));
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
        if ($this->Photo->Visible && !$this->Photo->Upload->KeepFile) {
            $this->Photo->UploadPath = $this->Photo->getUploadPath();
            if (!IsEmpty($this->Photo->Upload->FileName)) {
                $this->Photo->Upload->DbValue = null;
                FixUploadFileNames($this->Photo);
                $this->Photo->setDbValueDef($newRow, $this->Photo->Upload->FileName, false);
            }
        }
        if ($this->Avatar->Visible && !$this->Avatar->Upload->KeepFile) {
            if (!IsEmpty($this->Avatar->Upload->FileName)) {
                $this->Avatar->Upload->DbValue = null;
                FixUploadFileNames($this->Avatar);
                $this->Avatar->setDbValueDef($newRow, $this->Avatar->Upload->FileName, false);
            }
        }

        // Update current values
        $this->Fields->setCurrentValues($newRow);

        // Check if valid User ID
        if (
            !IsEmpty($this->security->currentUserID())
            && !$this->security->canAccess() // No access permission
            && !$this->security->isValidUserID($this->_UserID->CurrentValue)
        ) {
            $userIdMsg = sprintf($this->language->phrase("UnauthorizedUserID"), CurrentUserID(), strval($this->_UserID->CurrentValue));
            $this->setFailureMessage($userIdMsg);
            return false;
        }

        // Check if valid Parent User ID
        if (
            !IsEmpty($this->security->currentUserID())
            && !IsEmpty($this->ReportsTo->CurrentValue) // Allow empty value
            && !$this->security->canAccess() // No access permission
            && !$this->security->isValidUserID($this->ReportsTo->CurrentValue)
        ) {
            $parentUserIdMsg = sprintf($this->language->phrase("UnauthorizedParentUserID"), CurrentUserID(), strval($this->ReportsTo->CurrentValue));
            $this->setFailureMessage($parentUserIdMsg);
            return false;
        }
        $conn = $this->getConnection();

        // Load db values from old row
        $this->loadDbValues($oldRow);
        $this->Photo->OldUploadPath = $this->Photo->getUploadPath(); // PHP
        $this->Photo->UploadPath = $this->Photo->OldUploadPath;

        // Call Row Inserting event
        $insertRow = $this->rowInserting($oldRow, $newRow);
        if ($insertRow) {
            $addRow = $this->insert($newRow);
            if ($addRow) {
                if ($this->Photo->Visible && !$this->Photo->Upload->KeepFile) {
                    $this->Photo->Upload->DbValue = null;
                    if (!SaveUploadFiles($this->Photo, $newRow['Photo'], false)) {
                        $this->setFailureMessage($this->language->phrase("UploadError7"));
                        return false;
                    }
                }
                if ($this->Avatar->Visible && !$this->Avatar->Upload->KeepFile) {
                    $this->Avatar->Upload->DbValue = null;
                    if (!SaveUploadFiles($this->Avatar, $newRow['Avatar'], false)) {
                        $this->setFailureMessage($this->language->phrase("UploadError7"));
                        return false;
                    }
                }
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

        // Username
        $this->_Username->setDbValueDef($newRow, $this->_Username->CurrentValue, false);

        // Password
        if (!IsMaskedPassword($this->_Password->CurrentValue)) {
            $this->_Password->setDbValueDef($newRow, $this->_Password->CurrentValue, false);
        }

        // UserLevel
        if ($this->security->canAdmin()) { // System admin
            $this->UserLevel->setDbValueDef($newRow, $this->UserLevel->CurrentValue, strval($this->UserLevel->CurrentValue) == "");
        }

        // FirstName
        $this->FirstName->setDbValueDef($newRow, $this->FirstName->CurrentValue, false);

        // LastName
        $this->LastName->setDbValueDef($newRow, $this->LastName->CurrentValue, false);

        // CompleteName
        $this->CompleteName->setDbValueDef($newRow, $this->CompleteName->CurrentValue, false);

        // BirthDate
        $this->BirthDate->setDbValueDef($newRow, UnFormatDateTime($this->BirthDate->CurrentValue, $this->BirthDate->formatPattern()), false);

        // HomePhone
        $this->HomePhone->setDbValueDef($newRow, $this->HomePhone->CurrentValue, false);

        // Photo
        if ($this->Photo->Visible && !$this->Photo->Upload->KeepFile) {
            if ($this->Photo->Upload->FileName == "") {
                $newRow['Photo'] = null;
            } else {
                FixUploadTempFileNames($this->Photo);
                $newRow['Photo'] = $this->Photo->Upload->FileName;
            }
        }

        // Notes
        $this->Notes->setDbValueDef($newRow, $this->Notes->CurrentValue, false);

        // ReportsTo
        $this->ReportsTo->setDbValueDef($newRow, $this->ReportsTo->CurrentValue, false);

        // Gender
        $this->Gender->setDbValueDef($newRow, $this->Gender->CurrentValue, false);

        // Email
        $this->_Email->setDbValueDef($newRow, $this->_Email->CurrentValue, false);

        // Activated
        $tmpBool = $this->Activated->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Activated->setDbValueDef($newRow, $tmpBool, false);

        // Avatar
        if ($this->Avatar->Visible && !$this->Avatar->Upload->KeepFile) {
            if ($this->Avatar->Upload->FileName == "") {
                $newRow['Avatar'] = null;
            } else {
                FixUploadTempFileNames($this->Avatar);
                $newRow['Avatar'] = $this->Avatar->Upload->FileName;
            }
        }

        // ActiveStatus
        $tmpBool = $this->ActiveStatus->CurrentValue;
        if ($tmpBool != "1" && $tmpBool != "0") {
            $tmpBool = !empty($tmpBool) ? "1" : "0";
        }
        $this->ActiveStatus->setDbValueDef($newRow, $tmpBool, false);

        // MessengerColor
        $this->MessengerColor->setDbValueDef($newRow, $this->MessengerColor->CurrentValue, false);

        // CreatedAt
        $this->CreatedAt->setDbValueDef($newRow, UnFormatDateTime($this->CreatedAt->CurrentValue, $this->CreatedAt->formatPattern()), false);

        // CreatedBy
        $this->CreatedBy->setDbValueDef($newRow, $this->CreatedBy->CurrentValue, false);

        // UpdatedAt
        $this->UpdatedAt->CurrentValue = $this->UpdatedAt->getAutoUpdateValue(); // PHP
        $this->UpdatedAt->setDbValueDef($newRow, UnFormatDateTime($this->UpdatedAt->CurrentValue, $this->UpdatedAt->formatPattern()), false);

        // UpdatedBy
        $this->UpdatedBy->CurrentValue = $this->UpdatedBy->getAutoUpdateValue(); // PHP
        $this->UpdatedBy->setDbValueDef($newRow, $this->UpdatedBy->CurrentValue, false);

        // UserID
        return $newRow;
    }

    // Show link optionally based on User ID
    protected function showOptionLink(string $id = ""): bool
    {
        if ($this->security->isLoggedIn() && !$this->security->canAccess() && !$this->userIDAllow($id)) { // No access permission
            return $this->security->isValidUserID($this->_UserID->CurrentValue);
        }
        return true;
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
            if ($masterTblVar == "userlevels") {
                $validMaster = true;
                $masterTbl = Container("userlevels");
                if (($parm = Get("fk_ID", Get("UserLevel"))) !== null) {
                    $masterTbl->ID->setQueryStringValue($parm);
                    $this->UserLevel->QueryStringValue = $masterTbl->ID->QueryStringValue; // DO NOT change, master/detail key data type can be different
                    $this->UserLevel->setSessionValue($this->UserLevel->QueryStringValue);
                    $foreignKeys["UserLevel"] = $this->UserLevel->QueryStringValue;
                    if (!is_numeric($masterTbl->ID->QueryStringValue)) {
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
            if ($masterTblVar == "userlevels") {
                $validMaster = true;
                $masterTbl = Container("userlevels");
                if (($parm = Post("fk_ID", Post("UserLevel"))) !== null) {
                    $masterTbl->ID->setFormValue($parm);
                    $this->UserLevel->FormValue = $masterTbl->ID->FormValue;
                    $this->UserLevel->setSessionValue($this->UserLevel->FormValue);
                    $foreignKeys["UserLevel"] = $this->UserLevel->FormValue;
                    if (!is_numeric($masterTbl->ID->FormValue)) {
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
            if ($masterTblVar != "userlevels") {
                if (!array_key_exists("UserLevel", $foreignKeys)) { // Not current foreign key
                    $this->UserLevel->setSessionValue("");
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
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("userslist"), "", $this->TableVar, true);
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
                case "x_UserLevel":
                    break;
                case "x_Gender":
                    break;
                case "x_Activated":
                    break;
                case "x_ActiveStatus":
                    break;
                case "x_CreatedBy":
                    break;
                case "x_UpdatedBy":
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
        Language()->setPhrase("AddCaption", "Please fill in the following form to add a new user ...");
        $this->CreatedAt->Visible = false;
        $this->CreatedBy->Visible = false;
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
