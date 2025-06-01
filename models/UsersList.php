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
class UsersList extends Users
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "list";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "UsersList";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // Grid form hidden field names
    public string $FormName = "fuserslist";

    // CSS class/style
    public string $CurrentPageName = "userslist";

    // Page URLs
    public string $AddUrl = "";
    public string $EditUrl = "";
    public string $DeleteUrl = "";
    public string $ViewUrl = "";
    public string $CopyUrl = "";
    public string $ListUrl = "";

    // Update URLs
    public string $InlineAddUrl = "";
    public string $InlineCopyUrl = "";
    public string $InlineEditUrl = "";
    public string $GridAddUrl = "";
    public string $GridEditUrl = "";
    public string $MultiEditUrl = "";
    public string $MultiDeleteUrl = "";
    public string $MultiUpdateUrl = "";

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
        $this->_UserID->setVisibility();
        $this->_Username->setVisibility();
        $this->_Password->Visible = false;
        $this->UserLevel->setVisibility();
        $this->FirstName->Visible = false;
        $this->LastName->Visible = false;
        $this->CompleteName->setVisibility();
        $this->BirthDate->Visible = false;
        $this->HomePhone->Visible = false;
        $this->Photo->setVisibility();
        $this->Notes->Visible = false;
        $this->ReportsTo->Visible = false;
        $this->Gender->setVisibility();
        $this->_Email->setVisibility();
        $this->Activated->setVisibility();
        $this->_Profile->Visible = false;
        $this->Avatar->Visible = false;
        $this->ActiveStatus->setVisibility();
        $this->MessengerColor->Visible = false;
        $this->CreatedAt->Visible = false;
        $this->CreatedBy->Visible = false;
        $this->UpdatedAt->Visible = false;
        $this->UpdatedBy->Visible = false;
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'users';
        $this->TableName = 'users';

        // Table CSS class
        $this->TableClass = "table table-bordered table-hover table-sm ew-table";

        // CSS class name as context
        $this->ContextClass = CheckClassName($this->TableVar);
        AppendClass($this->TableGridClass, $this->ContextClass);

        // Fixed header table
        if (!$this->UseCustomTemplate) {
            $this->setFixedHeaderTable(Config("USE_FIXED_HEADER_TABLE"), Config("FIXED_HEADER_TABLE_HEIGHT"));
        }

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

        // Page URL
        $pageUrl = $this->pageUrl(false);

        // Initialize URLs
        $this->AddUrl = "usersadd";
        $this->InlineAddUrl = $pageUrl . "action=add";
        $this->GridAddUrl = $pageUrl . "action=gridadd";
        $this->GridEditUrl = $pageUrl . "action=gridedit";
        $this->MultiEditUrl = $pageUrl . "action=multiedit";
        $this->MultiDeleteUrl = "usersdelete";
        $this->MultiUpdateUrl = "usersupdate";

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'users');
        }

        // Open connection
        $GLOBALS["Conn"] ??= $this->getConnection();

        // List options
        $this->ListOptions = new ListOptions(Tag: "td", TableVar: $this->TableVar);

        // Export options
        $this->ExportOptions = new ListOptions(TagClassName: "ew-export-option");

        // Import options
        $this->ImportOptions = new ListOptions(TagClassName: "ew-import-option");

        // Other options
        $this->OtherOptions = new ListOptionsCollection();

        // Grid-Add/Edit
        $this->OtherOptions["addedit"] = new ListOptions(
            TagClassName: "ew-add-edit-option",
            UseDropDownButton: false,
            DropDownButtonPhrase: $this->language->phrase("ButtonAddEdit"),
            UseButtonGroup: true
        );

        // Detail tables
        $this->OtherOptions["detail"] = new ListOptions(TagClassName: "ew-detail-option");
        // Actions
        $this->OtherOptions["action"] = new ListOptions(TagClassName: "ew-action-option");

        // Column visibility
        $this->OtherOptions["column"] = new ListOptions(
            TableVar: $this->TableVar,
            TagClassName: "ew-column-option",
            ButtonGroupClass: "ew-column-dropdown",
            UseDropDownButton: true,
            DropDownButtonPhrase: $this->language->phrase("Columns"),
            DropDownAutoClose: "outside",
            UseButtonGroup: false
        );

        // Filter options
        $this->FilterOptions = new ListOptions(TagClassName: "ew-filter-option");

        // List actions
        $this->ListActions = new ListActions();
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
                if (!SameString($pageName, GetPageName($this->getListUrl()))) { // Not List page
                    $result["caption"] = $this->getModalCaption($pageName);
                    $result["view"] = SameString($pageName, "usersview"); // If View page, no primary button
                } else { // List page
                    $result["error"] = $this->getFailureMessage(); // List page should not be shown as modal => error
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
                        if ($fld->DataType == DataType::MEMO && $fld->MemoMaxLength > 0) {
                            $val = TruncateMemo($val, $fld->MemoMaxLength, $fld->TruncateMemoRemoveHtml);
                        }
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
        if ($this->isAddOrEdit()) {
            $this->UpdatedAt->Visible = false;
        }
        if ($this->isAddOrEdit()) {
            $this->UpdatedBy->Visible = false;
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

    // Class variables
    public ?ListOptions $ListOptions = null; // List options
    public ?ListOptions $ExportOptions = null; // Export options
    public ?ListOptions $SearchOptions = null; // Search options
    public ?ListOptionsCollection $OtherOptions = null; // Other options
    public ?ListOptions $HeaderOptions = null; // Header options
    public ?ListOptions $FooterOptions = null; // Footer options
    public ?ListOptions $FilterOptions = null; // Filter options
    public ?ListOptions $ImportOptions = null; // Import options
    public ?ListActions $ListActions = null; // List actions
    public int $SelectedCount = 0;
    public int $SelectedIndex = 0;
    public int $DisplayRecords = 20;
    public int $StartRecord = 0;
    public int $StopRecord = 0;
    public int $TotalRecords = 0;

    // Begin modification by Masino Sinaga, September 11, 2023
    // public $RecordRange = 10;
	public int $RecordRange = 10;

	// End modification by Masino Sinaga, September 11, 2023
    public string $PageSizes = "10,20,50,-1"; // Page sizes (comma separated)
    public string $DefaultSearchWhere = ""; // Default search WHERE clause
    public string $SearchWhere = ""; // Search WHERE clause
    public string $SearchPanelClass = "ew-search-panel collapse show"; // Search Panel class
    public int $SearchColumnCount = 0; // For extended search
    public int $SearchFieldsPerRow = 2; // For extended search
    public int $RecordCount = 0; // Record count
    public int $InlineRowCount = 0;
    public int $StartRowCount = 1;
    public array $Attrs = []; // Row attributes and cell attributes
    public int|string $RowIndex = 0; // Row index
    public int $KeyCount = 0; // Key count
    public string $MultiColumnGridClass = "row-cols-md";
    public string $MultiColumnEditClass = "col-12 w-100";
    public string $MultiColumnCardClass = "card h-100 ew-card";
    public string $MultiColumnListOptionsPosition = "bottom-start";
    public ?string $DbMasterFilter = ""; // Master filter
    public string $DbDetailFilter = ""; // Detail filter
    public bool $MasterRecordExists = false;
    public string $MultiSelectKey = "";
    public string $Command = "";
    public string $UserAction = ""; // User action
    public bool $RestoreSearch = false;
    public ?string $HashValue = null; // Hash value
    public ?SubPages $DetailPages = null;
    public string $TopContentClass = "ew-top";
    public string $MiddleContentClass = "ew-middle";
    public string $BottomContentClass = "ew-bottom";
    public string $PageAction = "";
    public array $RecKeys = [];
    public bool $IsModal = false;
    protected string $FilterForModalActions = "";
    private bool $UseInfiniteScroll = false;

    /**
     * Load result set from filter
     *
     * @return void
     */
    public function loadRecordsetFromFilter(string $filter): void
    {
        // Set up list options
        $this->setupListOptions();

        // Search options
        $this->setupSearchOptions();

        // Other options
        $this->setupOtherOptions();

        // Set visibility
        $this->setVisibility();

        // Load result set
        $this->TotalRecords = $this->loadRecordCount($filter);
        $this->StartRecord = 1;
        $this->StopRecord = $this->DisplayRecords;
        $this->CurrentFilter = $filter;
        $this->Result = $this->loadResult();

        // Set up pager
        $this->Pager = new PrevNextPager($this, $this->StartRecord, $this->DisplayRecords, $this->TotalRecords, $this->PageSizes, $this->RecordRange, $this->AutoHidePager, $this->AutoHidePageSizeSelector);
    }

    /**
     * Page run
     *
     * @return void
     */
    public function run(): void
    {
        global $ExportType, $DashboardReport;

        // Multi column button position
        $this->MultiColumnListOptionsPosition = Config("MULTI_COLUMN_LIST_OPTIONS_POSITION");
        $DashboardReport ??= Param(Config("PAGE_DASHBOARD"));

// Is modal
        $this->IsModal = IsModal();

        // Use layout
        $this->UseLayout = $this->UseLayout && ConvertToBool(Param(Config("PAGE_LAYOUT"), true));

        // View
        $this->View = Get(Config("VIEW"));

        // Get export parameters
        $custom = "";
        if (Param("export") !== null) {
            $this->Export = Param("export");
            $custom = Param("custom", "");
        } else {
            $this->setExportReturnUrl(CurrentUrl());
        }
        $ExportType = $this->Export; // Get export parameter, used in header
        if ($ExportType != "") {
            global $SkipHeaderFooter;
            $SkipHeaderFooter = true;
        }
        $this->CurrentAction = Param("action"); // Set up current action

        // Get grid add count
        $gridaddcnt = Get(Config("TABLE_GRID_ADD_ROW_COUNT"), "");
        if (is_numeric($gridaddcnt) && $gridaddcnt > 0) {
            $this->GridAddRowCount = $gridaddcnt;
        }

        // Set up list options
        $this->setupListOptions();
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

        // Set up master detail parameters
        $this->setupMasterParms();

        // Setup other options
        $this->setupOtherOptions();

        // Set up lookup cache
        $this->setupLookupOptions($this->UserLevel);
        $this->setupLookupOptions($this->Gender);
        $this->setupLookupOptions($this->Activated);
        $this->setupLookupOptions($this->ActiveStatus);
        $this->setupLookupOptions($this->CreatedBy);
        $this->setupLookupOptions($this->UpdatedBy);

        // Update form name to avoid conflict
        if ($this->IsModal) {
            $this->FormName = "fusersgrid";
        }

        // Set up page action
        $this->PageAction = CurrentPageUrl(false);

        // Set up infinite scroll
        $this->UseInfiniteScroll = ConvertToBool(Param("infinitescroll"));

        // Search filters
        $srchAdvanced = ""; // Advanced search filter
        $srchBasic = ""; // Basic search filter
        $query = ""; // Query builder

        // Set up Dashboard Filter
        if ($DashboardReport) {
            AddFilter($this->Filter, $this->getDashboardFilter($DashboardReport, $this->TableVar));
        }

        // Get command
        $this->Command = strtolower(Get("cmd", ""));

        // Process list action first
        if ($this->processListAction()) { // Ajax request
            $this->terminate();
            return;
        }

        // Set up records per page
        $this->setupDisplayRecords();

        // Handle reset command
        $this->resetCmd();

        // Set up Breadcrumb
        if (!$this->isExport()) {
            $this->setupBreadcrumb();
        }

        // Hide list options
        if ($this->isExport()) {
            $this->ListOptions->hideAllOptions(["sequence"]);
            $this->ListOptions->UseDropDownButton = false; // Disable drop down button
            $this->ListOptions->UseButtonGroup = false; // Disable button group
        } elseif ($this->isGridAdd() || $this->isGridEdit() || $this->isMultiEdit() || $this->isConfirm()) {
            $this->ListOptions->hideAllOptions();
            $this->ListOptions->UseDropDownButton = false; // Disable drop down button
            $this->ListOptions->UseButtonGroup = false; // Disable button group
        }

        // Hide options
        if ($this->isExport() || !(IsEmpty($this->CurrentAction) || $this->isSearch())) {
            $this->ExportOptions->hideAllOptions();
            $this->FilterOptions->hideAllOptions();
            $this->ImportOptions->hideAllOptions();
        }

        // Hide other options
        if ($this->isExport()) {
            $this->OtherOptions->hideAllOptions();
        }

        // Get default search criteria
        AddFilter($this->DefaultSearchWhere, $this->basicSearchWhere(true));
        AddFilter($this->DefaultSearchWhere, $this->advancedSearchWhere(true));

        // Get basic search values
        if ($this->loadBasicSearchValues()) {
            $this->setSessionRules(""); // Clear rules for QueryBuilder
        }

        // Get and validate search values for advanced search
        $isAdvancedSearch = false;
        if (IsEmpty($this->UserAction)) { // Skip if user action
            $isAdvancedSearch = $this->loadSearchValues();
        }

        // Process filter list
        if ($this->processFilterList()) {
            $this->terminate();
            return;
        }
        if ($this->validateSearch() && $isAdvancedSearch) {
            $this->setSessionRules(""); // Clear rules for QueryBuilder
        }

        // Restore search parms from Session if not searching / reset / export
        if (($this->isExport() || $this->Command != "search" && $this->Command != "reset" && $this->Command != "resetall") && $this->Command != "json" && $this->checkSearchParms()) {
            $this->restoreSearchParms();
        }

        // Call Records SearchValidated event
        $this->recordsSearchValidated();

        // Set up sorting order
        $this->setupSortOrder();

        // Get basic search criteria
        if (!$this->hasInvalidFields()) {
            $srchBasic = $this->basicSearchWhere();
        }

        // Get advanced search criteria
        if (!$this->hasInvalidFields()) {
            $srchAdvanced = $this->advancedSearchWhere();
        }

        // Get query builder criteria
        $query = $DashboardReport ? "" : $this->queryBuilderWhere();

        // Restore display records
        if ($this->Command != "json" && $this->getRecordsPerPage() != 0) {
            $this->DisplayRecords = $this->getRecordsPerPage(); // Restore from Session
        } else {
            $this->DisplayRecords = 20; // Load default
            $this->setRecordsPerPage($this->DisplayRecords); // Save default to Session
        }

        // Load search default if no existing search criteria
        if (!$this->checkSearchParms() && !$query) {
            // Load basic search from default
            $this->BasicSearch->loadDefault();
            if ($this->BasicSearch->Keyword != "") {
                $srchBasic = $this->basicSearchWhere(); // Save to session
            }

            // Load advanced search from default
            if ($this->loadAdvancedSearchDefault()) {
                $srchAdvanced = $this->advancedSearchWhere(); // Save to session
            }
        }

        // Restore search settings from Session
        if (!$this->hasInvalidFields()) {
            $this->loadAdvancedSearch();
        }

        // Build search criteria
        if ($query) {
            AddFilter($this->SearchWhere, $query);
        } else {
            AddFilter($this->SearchWhere, $srchAdvanced);
            AddFilter($this->SearchWhere, $srchBasic);
        }

        // Call Records_Searching event
        $this->recordsSearching($this->SearchWhere);

        // Save search criteria
        if ($this->Command == "search" && !$this->RestoreSearch) {
            $this->setSearchWhere($this->SearchWhere); // Save to Session
            $this->StartRecord = 1; // Reset start record counter
            $this->setStartRecordNumber($this->StartRecord);
        } elseif ($this->Command != "json" && !$query) {
            $this->SearchWhere = $this->getSearchWhere();
        }

        // Build filter
        if (!$this->security->canList()) {
            $this->Filter = "(0=1)"; // Filter all records
        }

        // Restore master/detail filter from session
        $this->DbMasterFilter = $this->getMasterFilterFromSession(); // Restore master filter from session
        $this->DbDetailFilter = $this->getDetailFilterFromSession(); // Restore detail filter from session
        AddFilter($this->Filter, $this->DbDetailFilter);
        AddFilter($this->Filter, $this->SearchWhere);

        // Load master record
        if ($this->CurrentMode != "add" && $this->DbMasterFilter != "" && $this->getCurrentMasterTable() == "userlevels") {
            $masterTbl = Container("userlevels");
            $masterRow = $masterTbl->loadRecords($this->DbMasterFilter)->fetchAssociative();
            $this->MasterRecordExists = $masterRow !== false;
            if (!$this->MasterRecordExists) {
                $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record found
                $this->terminate("userlevelslist"); // Return to master page
                return;
            } else {
                $masterTbl->loadListRowValues($masterRow);
                $masterTbl->RowType = RowType::MASTER; // Master row
                $masterTbl->renderListRow();
            }
        }

        // Set up filter
        if ($this->Command == "json") {
            $this->UseSessionForListSql = false; // Do not use session for ListSQL
            $this->CurrentFilter = $this->Filter;
        } else {
            $this->setSessionWhere($this->Filter);
            $this->CurrentFilter = "";
        }
        $this->Filter = $this->applyUserIDFilters($this->Filter);
        if ($this->isGridAdd()) {
            $this->CurrentFilter = "0=1";
            $this->StartRecord = 1;
            $this->DisplayRecords = $this->GridAddRowCount;
            $this->TotalRecords = $this->DisplayRecords;
            $this->StopRecord = $this->DisplayRecords;
        } elseif (($this->isEdit() || $this->isCopy() || $this->isInlineInserted() || $this->isInlineUpdated()) && $this->UseInfiniteScroll) { // Get current record only
            $this->CurrentFilter = $this->isInlineUpdated() ? $this->getRecordFilter() : $this->getFilterFromRecordKeys();
            $this->TotalRecords = $this->listRecordCount();
            $this->StartRecord = 1;
            $this->StopRecord = $this->DisplayRecords;
            $this->Result = $this->loadResult();
        } elseif (
            $this->UseInfiniteScroll && $this->isGridInserted()
            || $this->UseInfiniteScroll && ($this->isGridEdit() || $this->isGridUpdated())
            || $this->isMultiEdit()
            || $this->UseInfiniteScroll && $this->isMultiUpdated()
        ) { // Get current records only
            $this->CurrentFilter = $this->FilterForModalActions; // Restore filter
            $this->TotalRecords = $this->listRecordCount();
            $this->StartRecord = 1;
            $this->StopRecord = $this->DisplayRecords;
            $this->Result = $this->loadResult();
        } elseif (!(IsApi() && IsExport())) { // Skip loading records if export from API (to be done in exportData)
            $this->TotalRecords = $this->listRecordCount();
            $this->StartRecord = 1;
            if ($this->DisplayRecords <= 0 || ($this->isExport() && $this->ExportAll)) { // Display all records
                $this->DisplayRecords = $this->TotalRecords;
            }
            if (!($this->isExport() && $this->ExportAll)) {
                $this->setupStartRecord(); // Set up start record position
            }
            $this->Result = $this->loadResult($this->StartRecord - 1, $this->DisplayRecords);

            // Set no record found message
            if ((IsEmpty($this->CurrentAction) || $this->isSearch()) && $this->TotalRecords == 0) {
                if (!$this->security->canList()) {
                    $this->setWarningMessage(DeniedMessage());
                }
                if ($this->SearchWhere == "0=101") {
                    $this->setWarningMessage($this->language->phrase("EnterSearchCriteria"));
                } else {
                    $this->setWarningMessage($this->language->phrase("NoRecord"));
                }
            }
        }

        // Set up list action columns
        foreach ($this->ListActions as $listAction) {
            if ($listAction->getVisible()) {
                if ($listAction->Select == ActionType::MULTIPLE) { // Show checkbox column if multiple action
                    $this->ListOptions["checkbox"]->Visible = true;
                } elseif ($listAction->Select == ActionType::SINGLE) { // Show list action column
                    $this->ListOptions["listactions"]->Visible = true;
                }
            }
        }

        // Search options
        $this->setupSearchOptions();

        // Set up search panel class
        if ($this->SearchWhere != "") {
            if ($query) { // Hide search panel if using QueryBuilder
                RemoveClass($this->SearchPanelClass, "show");
            } else {
                AppendClass($this->SearchPanelClass, "show");
            }
        }

		// Begin of add Search Panel Status by Masino Sinaga, October 13, 2024
		if (ReadCookie('users_searchpanel') == 'notactive' || ReadCookie('users_searchpanel') == "") {
			RemoveClass($this->SearchPanelClass, "show");
			AppendClass($this->SearchPanelClass, "collapse");
		} elseif (ReadCookie('users_searchpanel') == 'active') {
			RemoveClass($this->SearchPanelClass, "collapse");
			AppendClass($this->SearchPanelClass, "show");
		} else {
			RemoveClass($this->SearchPanelClass, "show");
			AppendClass($this->SearchPanelClass, "collapse");
		}

		// End of add Search Panel Status by Masino Sinaga, October 13, 2024

        // API list action
        if (IsApi()) {
            if (Route(0) == Config("API_LIST_ACTION")) {
                if (!$this->isExport()) {
                    $rows = $this->getRecordsFromResult($this->Result);
                    $this->Result?->free();
                    WriteJson([
                        "success" => true,
                        "action" => Config("API_LIST_ACTION"),
                        $this->TableVar => $rows,
                        "totalRecordCount" => $this->TotalRecords
                    ]);
                    $this->terminate(true);
                }
                return;
            } elseif ($this->peekFailureMessage()) {
                WriteJson(["error" => $this->getFailureMessage()]);
                $this->terminate(true);
                return;
            }
        }

        // Render other options
        $this->renderOtherOptions();

        // Set up pager
        $this->Pager = new PrevNextPager($this, $this->StartRecord, $this->DisplayRecords, $this->TotalRecords, $this->PageSizes, $this->RecordRange, $this->AutoHidePager, $this->AutoHidePageSizeSelector);

		// Set up first record for Export Data purpose, by Masino Sinaga, September 11, 2023
		$first_rec = Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_START_REC")));
		$_SESSION["First_Record"] = $first_rec;

        // Set ReturnUrl in header if necessary
        if ($returnUrl = (FlashBag()->get("Return-Url") ?? "")) {
            AddHeader("Return-Url", GetUrl($returnUrl));
        }

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

    // Get page number
    public function getPageNumber(): int
    {
        return ($this->DisplayRecords > 0 && $this->StartRecord > 0) ? ceil($this->StartRecord / $this->DisplayRecords) : 1;
    }

    // Set up number of records displayed per page
    protected function setupDisplayRecords(): void
    {
        // Begin of modification Customize Navigation/Pager Panel, by Masino Sinaga, September 11, 2023
		global $Language;
        $wrk = Get(Config("TABLE_REC_PER_PAGE"), "");
        if ($wrk > MS_TABLE_MAXIMUM_SELECTED_RECORDS || strtolower($wrk) == "all") {
            $wrk = MS_TABLE_MAXIMUM_SELECTED_RECORDS;
			if ($wrk > 0)
				$this->setFailureMessage(str_replace("%t", MS_TABLE_MAXIMUM_SELECTED_RECORDS, $Language->Phrase("MaximumRecordsPerPage")));
        }

		// End of modification Customize Navigation/Pager Panel, by Masino Sinaga, September 11, 2023
        if ($wrk != "") {
            if (is_numeric($wrk)) {
                $this->DisplayRecords = (int)$wrk;
            } else {
                if (SameText($wrk, "all")) { // Display all records
                    $this->DisplayRecords = -1;
                } else {
                    $this->DisplayRecords = 20; // Non-numeric, load default
                }
            }
            $this->setRecordsPerPage($this->DisplayRecords); // Save to Session
            // Reset start position
            $this->StartRecord = 1;
            $this->setStartRecordNumber($this->StartRecord);
        }
    }

    // Build filter for all keys
    protected function buildKeyFilter(): string
    {
        $wrkFilter = "";

        // Update row index and get row key
        $rowindex = 1;
        $this->FormIndex = $rowindex;
        $thisKey = $this->getOldKey();
        while ($thisKey != "") {
            $this->setKey($thisKey);
            if ($this->OldKey != "") {
                $filter = $this->getRecordFilter();
                if ($wrkFilter != "") {
                    $wrkFilter .= " OR ";
                }
                $wrkFilter .= $filter;
            } else {
                $wrkFilter = "0=1";
                break;
            }

            // Update row index and get row key
            $rowindex++; // Next row
            $this->FormIndex = $rowindex;
            $thisKey = $this->getOldKey();
        }
        return $wrkFilter;
    }

    // Get list of filters
    public function getFilterList(): string
    {
        // Initialize
        $filterList = "";
        $savedFilterList = "";

        // Load server side filters
        if (Config("SEARCH_FILTER_OPTION") == "Server") {
            $savedFilterList = Profile()->getSearchFilters("fuserssrch");
        }
        $filterList = Concat($filterList, $this->_UserID->AdvancedSearch->toJson(), ","); // Field UserID
        $filterList = Concat($filterList, $this->_Username->AdvancedSearch->toJson(), ","); // Field Username
        $filterList = Concat($filterList, $this->UserLevel->AdvancedSearch->toJson(), ","); // Field UserLevel
        $filterList = Concat($filterList, $this->FirstName->AdvancedSearch->toJson(), ","); // Field FirstName
        $filterList = Concat($filterList, $this->LastName->AdvancedSearch->toJson(), ","); // Field LastName
        $filterList = Concat($filterList, $this->CompleteName->AdvancedSearch->toJson(), ","); // Field CompleteName
        $filterList = Concat($filterList, $this->BirthDate->AdvancedSearch->toJson(), ","); // Field BirthDate
        $filterList = Concat($filterList, $this->HomePhone->AdvancedSearch->toJson(), ","); // Field HomePhone
        $filterList = Concat($filterList, $this->Photo->AdvancedSearch->toJson(), ","); // Field Photo
        $filterList = Concat($filterList, $this->Notes->AdvancedSearch->toJson(), ","); // Field Notes
        $filterList = Concat($filterList, $this->ReportsTo->AdvancedSearch->toJson(), ","); // Field ReportsTo
        $filterList = Concat($filterList, $this->Gender->AdvancedSearch->toJson(), ","); // Field Gender
        $filterList = Concat($filterList, $this->_Email->AdvancedSearch->toJson(), ","); // Field Email
        $filterList = Concat($filterList, $this->Activated->AdvancedSearch->toJson(), ","); // Field Activated
        $filterList = Concat($filterList, $this->Avatar->AdvancedSearch->toJson(), ","); // Field Avatar
        $filterList = Concat($filterList, $this->ActiveStatus->AdvancedSearch->toJson(), ","); // Field ActiveStatus
        $filterList = Concat($filterList, $this->MessengerColor->AdvancedSearch->toJson(), ","); // Field MessengerColor
        $filterList = Concat($filterList, $this->CreatedAt->AdvancedSearch->toJson(), ","); // Field CreatedAt
        $filterList = Concat($filterList, $this->CreatedBy->AdvancedSearch->toJson(), ","); // Field CreatedBy
        $filterList = Concat($filterList, $this->UpdatedAt->AdvancedSearch->toJson(), ","); // Field UpdatedAt
        $filterList = Concat($filterList, $this->UpdatedBy->AdvancedSearch->toJson(), ","); // Field UpdatedBy
        if ($this->BasicSearch->Keyword != "") {
            $wrk = "\"" . Config("TABLE_BASIC_SEARCH") . "\":\"" . JsEncode($this->BasicSearch->Keyword) . "\",\"" . Config("TABLE_BASIC_SEARCH_TYPE") . "\":\"" . JsEncode($this->BasicSearch->Type) . "\"";
            $filterList = Concat($filterList, $wrk, ",");
        }

        // Return filter list in JSON
        if ($filterList != "") {
            $filterList = "\"data\":{" . $filterList . "}";
        }
        if ($savedFilterList != "") {
            $filterList = Concat($filterList, "\"filters\":" . $savedFilterList, ",");
        }
        return ($filterList != "") ? "{" . $filterList . "}" : "null";
    }

    // Process filter list
    protected function processFilterList(): bool
    {
        if (Post("ajax") == "savefilters") { // Save filter request (Ajax)
            $filters = Post("filters");
            Profile()->setSearchFilters("fuserssrch", $filters);
            WriteJson([["success" => true]]); // Success
            return true;
        } elseif (Post("cmd") == "resetfilter") {
            $this->restoreFilterList();
        }
        return false;
    }

    // Restore list of filters
    protected function restoreFilterList(): void
    {
        // Return if not reset filter
        if (Post("cmd") !== "resetfilter") {
            return;
        }
        $filter = json_decode(Post("filter"), true);
        $this->Command = "search";

        // Field UserID
        $this->_UserID->AdvancedSearch->SearchValue = $filter["x__UserID"] ?? "";
        $this->_UserID->AdvancedSearch->SearchOperator = $filter["z__UserID"] ?? "";
        $this->_UserID->AdvancedSearch->SearchCondition = $filter["v__UserID"] ?? "";
        $this->_UserID->AdvancedSearch->SearchValue2 = $filter["y__UserID"] ?? "";
        $this->_UserID->AdvancedSearch->SearchOperator2 = $filter["w__UserID"] ?? "";
        $this->_UserID->AdvancedSearch->save();

        // Field Username
        $this->_Username->AdvancedSearch->SearchValue = $filter["x__Username"] ?? "";
        $this->_Username->AdvancedSearch->SearchOperator = $filter["z__Username"] ?? "";
        $this->_Username->AdvancedSearch->SearchCondition = $filter["v__Username"] ?? "";
        $this->_Username->AdvancedSearch->SearchValue2 = $filter["y__Username"] ?? "";
        $this->_Username->AdvancedSearch->SearchOperator2 = $filter["w__Username"] ?? "";
        $this->_Username->AdvancedSearch->save();

        // Field UserLevel
        $this->UserLevel->AdvancedSearch->SearchValue = $filter["x_UserLevel"] ?? "";
        $this->UserLevel->AdvancedSearch->SearchOperator = $filter["z_UserLevel"] ?? "";
        $this->UserLevel->AdvancedSearch->SearchCondition = $filter["v_UserLevel"] ?? "";
        $this->UserLevel->AdvancedSearch->SearchValue2 = $filter["y_UserLevel"] ?? "";
        $this->UserLevel->AdvancedSearch->SearchOperator2 = $filter["w_UserLevel"] ?? "";
        $this->UserLevel->AdvancedSearch->save();

        // Field FirstName
        $this->FirstName->AdvancedSearch->SearchValue = $filter["x_FirstName"] ?? "";
        $this->FirstName->AdvancedSearch->SearchOperator = $filter["z_FirstName"] ?? "";
        $this->FirstName->AdvancedSearch->SearchCondition = $filter["v_FirstName"] ?? "";
        $this->FirstName->AdvancedSearch->SearchValue2 = $filter["y_FirstName"] ?? "";
        $this->FirstName->AdvancedSearch->SearchOperator2 = $filter["w_FirstName"] ?? "";
        $this->FirstName->AdvancedSearch->save();

        // Field LastName
        $this->LastName->AdvancedSearch->SearchValue = $filter["x_LastName"] ?? "";
        $this->LastName->AdvancedSearch->SearchOperator = $filter["z_LastName"] ?? "";
        $this->LastName->AdvancedSearch->SearchCondition = $filter["v_LastName"] ?? "";
        $this->LastName->AdvancedSearch->SearchValue2 = $filter["y_LastName"] ?? "";
        $this->LastName->AdvancedSearch->SearchOperator2 = $filter["w_LastName"] ?? "";
        $this->LastName->AdvancedSearch->save();

        // Field CompleteName
        $this->CompleteName->AdvancedSearch->SearchValue = $filter["x_CompleteName"] ?? "";
        $this->CompleteName->AdvancedSearch->SearchOperator = $filter["z_CompleteName"] ?? "";
        $this->CompleteName->AdvancedSearch->SearchCondition = $filter["v_CompleteName"] ?? "";
        $this->CompleteName->AdvancedSearch->SearchValue2 = $filter["y_CompleteName"] ?? "";
        $this->CompleteName->AdvancedSearch->SearchOperator2 = $filter["w_CompleteName"] ?? "";
        $this->CompleteName->AdvancedSearch->save();

        // Field BirthDate
        $this->BirthDate->AdvancedSearch->SearchValue = $filter["x_BirthDate"] ?? "";
        $this->BirthDate->AdvancedSearch->SearchOperator = $filter["z_BirthDate"] ?? "";
        $this->BirthDate->AdvancedSearch->SearchCondition = $filter["v_BirthDate"] ?? "";
        $this->BirthDate->AdvancedSearch->SearchValue2 = $filter["y_BirthDate"] ?? "";
        $this->BirthDate->AdvancedSearch->SearchOperator2 = $filter["w_BirthDate"] ?? "";
        $this->BirthDate->AdvancedSearch->save();

        // Field HomePhone
        $this->HomePhone->AdvancedSearch->SearchValue = $filter["x_HomePhone"] ?? "";
        $this->HomePhone->AdvancedSearch->SearchOperator = $filter["z_HomePhone"] ?? "";
        $this->HomePhone->AdvancedSearch->SearchCondition = $filter["v_HomePhone"] ?? "";
        $this->HomePhone->AdvancedSearch->SearchValue2 = $filter["y_HomePhone"] ?? "";
        $this->HomePhone->AdvancedSearch->SearchOperator2 = $filter["w_HomePhone"] ?? "";
        $this->HomePhone->AdvancedSearch->save();

        // Field Photo
        $this->Photo->AdvancedSearch->SearchValue = $filter["x_Photo"] ?? "";
        $this->Photo->AdvancedSearch->SearchOperator = $filter["z_Photo"] ?? "";
        $this->Photo->AdvancedSearch->SearchCondition = $filter["v_Photo"] ?? "";
        $this->Photo->AdvancedSearch->SearchValue2 = $filter["y_Photo"] ?? "";
        $this->Photo->AdvancedSearch->SearchOperator2 = $filter["w_Photo"] ?? "";
        $this->Photo->AdvancedSearch->save();

        // Field Notes
        $this->Notes->AdvancedSearch->SearchValue = $filter["x_Notes"] ?? "";
        $this->Notes->AdvancedSearch->SearchOperator = $filter["z_Notes"] ?? "";
        $this->Notes->AdvancedSearch->SearchCondition = $filter["v_Notes"] ?? "";
        $this->Notes->AdvancedSearch->SearchValue2 = $filter["y_Notes"] ?? "";
        $this->Notes->AdvancedSearch->SearchOperator2 = $filter["w_Notes"] ?? "";
        $this->Notes->AdvancedSearch->save();

        // Field ReportsTo
        $this->ReportsTo->AdvancedSearch->SearchValue = $filter["x_ReportsTo"] ?? "";
        $this->ReportsTo->AdvancedSearch->SearchOperator = $filter["z_ReportsTo"] ?? "";
        $this->ReportsTo->AdvancedSearch->SearchCondition = $filter["v_ReportsTo"] ?? "";
        $this->ReportsTo->AdvancedSearch->SearchValue2 = $filter["y_ReportsTo"] ?? "";
        $this->ReportsTo->AdvancedSearch->SearchOperator2 = $filter["w_ReportsTo"] ?? "";
        $this->ReportsTo->AdvancedSearch->save();

        // Field Gender
        $this->Gender->AdvancedSearch->SearchValue = $filter["x_Gender"] ?? "";
        $this->Gender->AdvancedSearch->SearchOperator = $filter["z_Gender"] ?? "";
        $this->Gender->AdvancedSearch->SearchCondition = $filter["v_Gender"] ?? "";
        $this->Gender->AdvancedSearch->SearchValue2 = $filter["y_Gender"] ?? "";
        $this->Gender->AdvancedSearch->SearchOperator2 = $filter["w_Gender"] ?? "";
        $this->Gender->AdvancedSearch->save();

        // Field Email
        $this->_Email->AdvancedSearch->SearchValue = $filter["x__Email"] ?? "";
        $this->_Email->AdvancedSearch->SearchOperator = $filter["z__Email"] ?? "";
        $this->_Email->AdvancedSearch->SearchCondition = $filter["v__Email"] ?? "";
        $this->_Email->AdvancedSearch->SearchValue2 = $filter["y__Email"] ?? "";
        $this->_Email->AdvancedSearch->SearchOperator2 = $filter["w__Email"] ?? "";
        $this->_Email->AdvancedSearch->save();

        // Field Activated
        $this->Activated->AdvancedSearch->SearchValue = $filter["x_Activated"] ?? "";
        $this->Activated->AdvancedSearch->SearchOperator = $filter["z_Activated"] ?? "";
        $this->Activated->AdvancedSearch->SearchCondition = $filter["v_Activated"] ?? "";
        $this->Activated->AdvancedSearch->SearchValue2 = $filter["y_Activated"] ?? "";
        $this->Activated->AdvancedSearch->SearchOperator2 = $filter["w_Activated"] ?? "";
        $this->Activated->AdvancedSearch->save();

        // Field Avatar
        $this->Avatar->AdvancedSearch->SearchValue = $filter["x_Avatar"] ?? "";
        $this->Avatar->AdvancedSearch->SearchOperator = $filter["z_Avatar"] ?? "";
        $this->Avatar->AdvancedSearch->SearchCondition = $filter["v_Avatar"] ?? "";
        $this->Avatar->AdvancedSearch->SearchValue2 = $filter["y_Avatar"] ?? "";
        $this->Avatar->AdvancedSearch->SearchOperator2 = $filter["w_Avatar"] ?? "";
        $this->Avatar->AdvancedSearch->save();

        // Field ActiveStatus
        $this->ActiveStatus->AdvancedSearch->SearchValue = $filter["x_ActiveStatus"] ?? "";
        $this->ActiveStatus->AdvancedSearch->SearchOperator = $filter["z_ActiveStatus"] ?? "";
        $this->ActiveStatus->AdvancedSearch->SearchCondition = $filter["v_ActiveStatus"] ?? "";
        $this->ActiveStatus->AdvancedSearch->SearchValue2 = $filter["y_ActiveStatus"] ?? "";
        $this->ActiveStatus->AdvancedSearch->SearchOperator2 = $filter["w_ActiveStatus"] ?? "";
        $this->ActiveStatus->AdvancedSearch->save();

        // Field MessengerColor
        $this->MessengerColor->AdvancedSearch->SearchValue = $filter["x_MessengerColor"] ?? "";
        $this->MessengerColor->AdvancedSearch->SearchOperator = $filter["z_MessengerColor"] ?? "";
        $this->MessengerColor->AdvancedSearch->SearchCondition = $filter["v_MessengerColor"] ?? "";
        $this->MessengerColor->AdvancedSearch->SearchValue2 = $filter["y_MessengerColor"] ?? "";
        $this->MessengerColor->AdvancedSearch->SearchOperator2 = $filter["w_MessengerColor"] ?? "";
        $this->MessengerColor->AdvancedSearch->save();

        // Field CreatedAt
        $this->CreatedAt->AdvancedSearch->SearchValue = $filter["x_CreatedAt"] ?? "";
        $this->CreatedAt->AdvancedSearch->SearchOperator = $filter["z_CreatedAt"] ?? "";
        $this->CreatedAt->AdvancedSearch->SearchCondition = $filter["v_CreatedAt"] ?? "";
        $this->CreatedAt->AdvancedSearch->SearchValue2 = $filter["y_CreatedAt"] ?? "";
        $this->CreatedAt->AdvancedSearch->SearchOperator2 = $filter["w_CreatedAt"] ?? "";
        $this->CreatedAt->AdvancedSearch->save();

        // Field CreatedBy
        $this->CreatedBy->AdvancedSearch->SearchValue = $filter["x_CreatedBy"] ?? "";
        $this->CreatedBy->AdvancedSearch->SearchOperator = $filter["z_CreatedBy"] ?? "";
        $this->CreatedBy->AdvancedSearch->SearchCondition = $filter["v_CreatedBy"] ?? "";
        $this->CreatedBy->AdvancedSearch->SearchValue2 = $filter["y_CreatedBy"] ?? "";
        $this->CreatedBy->AdvancedSearch->SearchOperator2 = $filter["w_CreatedBy"] ?? "";
        $this->CreatedBy->AdvancedSearch->save();

        // Field UpdatedAt
        $this->UpdatedAt->AdvancedSearch->SearchValue = $filter["x_UpdatedAt"] ?? "";
        $this->UpdatedAt->AdvancedSearch->SearchOperator = $filter["z_UpdatedAt"] ?? "";
        $this->UpdatedAt->AdvancedSearch->SearchCondition = $filter["v_UpdatedAt"] ?? "";
        $this->UpdatedAt->AdvancedSearch->SearchValue2 = $filter["y_UpdatedAt"] ?? "";
        $this->UpdatedAt->AdvancedSearch->SearchOperator2 = $filter["w_UpdatedAt"] ?? "";
        $this->UpdatedAt->AdvancedSearch->save();

        // Field UpdatedBy
        $this->UpdatedBy->AdvancedSearch->SearchValue = $filter["x_UpdatedBy"] ?? "";
        $this->UpdatedBy->AdvancedSearch->SearchOperator = $filter["z_UpdatedBy"] ?? "";
        $this->UpdatedBy->AdvancedSearch->SearchCondition = $filter["v_UpdatedBy"] ?? "";
        $this->UpdatedBy->AdvancedSearch->SearchValue2 = $filter["y_UpdatedBy"] ?? "";
        $this->UpdatedBy->AdvancedSearch->SearchOperator2 = $filter["w_UpdatedBy"] ?? "";
        $this->UpdatedBy->AdvancedSearch->save();
        $this->BasicSearch->setKeyword($filter[Config("TABLE_BASIC_SEARCH")] ?? "");
        $this->BasicSearch->setType($filter[Config("TABLE_BASIC_SEARCH_TYPE")] ?? "");
    }

    // Advanced search WHERE clause based on QueryString
    public function advancedSearchWhere(bool $default = false): string
    {
        $where = "";
        if (!$this->security->canSearch()) {
            return "";
        }
        $this->buildSearchSql($where, $this->_UserID, $default, false); // UserID
        $this->buildSearchSql($where, $this->_Username, $default, false); // Username
        $this->buildSearchSql($where, $this->UserLevel, $default, false); // UserLevel
        $this->buildSearchSql($where, $this->FirstName, $default, false); // FirstName
        $this->buildSearchSql($where, $this->LastName, $default, false); // LastName
        $this->buildSearchSql($where, $this->CompleteName, $default, false); // CompleteName
        $this->buildSearchSql($where, $this->BirthDate, $default, false); // BirthDate
        $this->buildSearchSql($where, $this->HomePhone, $default, false); // HomePhone
        $this->buildSearchSql($where, $this->Photo, $default, false); // Photo
        $this->buildSearchSql($where, $this->Notes, $default, false); // Notes
        $this->buildSearchSql($where, $this->ReportsTo, $default, false); // ReportsTo
        $this->buildSearchSql($where, $this->Gender, $default, false); // Gender
        $this->buildSearchSql($where, $this->_Email, $default, false); // Email
        $this->buildSearchSql($where, $this->Activated, $default, false); // Activated
        $this->buildSearchSql($where, $this->Avatar, $default, false); // Avatar
        $this->buildSearchSql($where, $this->ActiveStatus, $default, false); // ActiveStatus
        $this->buildSearchSql($where, $this->MessengerColor, $default, false); // MessengerColor
        $this->buildSearchSql($where, $this->CreatedAt, $default, false); // CreatedAt
        $this->buildSearchSql($where, $this->CreatedBy, $default, false); // CreatedBy
        $this->buildSearchSql($where, $this->UpdatedAt, $default, false); // UpdatedAt
        $this->buildSearchSql($where, $this->UpdatedBy, $default, false); // UpdatedBy

        // Set up search command
        if (!$default && $where != "" && in_array($this->Command, ["", "reset", "resetall"])) {
            $this->Command = "search";
        }
        if (!$default && $this->Command == "search") {
            $this->_UserID->AdvancedSearch->save(); // UserID
            $this->_Username->AdvancedSearch->save(); // Username
            $this->UserLevel->AdvancedSearch->save(); // UserLevel
            $this->FirstName->AdvancedSearch->save(); // FirstName
            $this->LastName->AdvancedSearch->save(); // LastName
            $this->CompleteName->AdvancedSearch->save(); // CompleteName
            $this->BirthDate->AdvancedSearch->save(); // BirthDate
            $this->HomePhone->AdvancedSearch->save(); // HomePhone
            $this->Photo->AdvancedSearch->save(); // Photo
            $this->Notes->AdvancedSearch->save(); // Notes
            $this->ReportsTo->AdvancedSearch->save(); // ReportsTo
            $this->Gender->AdvancedSearch->save(); // Gender
            $this->_Email->AdvancedSearch->save(); // Email
            $this->Activated->AdvancedSearch->save(); // Activated
            $this->Avatar->AdvancedSearch->save(); // Avatar
            $this->ActiveStatus->AdvancedSearch->save(); // ActiveStatus
            $this->MessengerColor->AdvancedSearch->save(); // MessengerColor
            $this->CreatedAt->AdvancedSearch->save(); // CreatedAt
            $this->CreatedBy->AdvancedSearch->save(); // CreatedBy
            $this->UpdatedAt->AdvancedSearch->save(); // UpdatedAt
            $this->UpdatedBy->AdvancedSearch->save(); // UpdatedBy
        }
        return $where;
    }

    // Query builder rules
    public function queryBuilderRules(): ?string
    {
        return Post("rules") ?? $this->getSessionRules();
    }

    // Quey builder WHERE clause
    public function queryBuilderWhere(string $fieldName = ""): string
    {
        if (!$this->security->canSearch()) {
            return "";
        }

        // Get rules by query builder
        $rules = $this->queryBuilderRules();

        // Decode and parse rules
        $where = $rules ? $this->parseRules(json_decode($rules, true), $fieldName) : "";

        // Clear other search and save rules to session
        if ($where && $fieldName == "") { // Skip if get query for specific field
            $this->resetSearchParms();
            $this->_UserID->AdvancedSearch->save(); // UserID
            $this->_Username->AdvancedSearch->save(); // Username
            $this->UserLevel->AdvancedSearch->save(); // UserLevel
            $this->FirstName->AdvancedSearch->save(); // FirstName
            $this->LastName->AdvancedSearch->save(); // LastName
            $this->CompleteName->AdvancedSearch->save(); // CompleteName
            $this->BirthDate->AdvancedSearch->save(); // BirthDate
            $this->HomePhone->AdvancedSearch->save(); // HomePhone
            $this->Photo->AdvancedSearch->save(); // Photo
            $this->Notes->AdvancedSearch->save(); // Notes
            $this->ReportsTo->AdvancedSearch->save(); // ReportsTo
            $this->Gender->AdvancedSearch->save(); // Gender
            $this->_Email->AdvancedSearch->save(); // Email
            $this->Activated->AdvancedSearch->save(); // Activated
            $this->Avatar->AdvancedSearch->save(); // Avatar
            $this->ActiveStatus->AdvancedSearch->save(); // ActiveStatus
            $this->MessengerColor->AdvancedSearch->save(); // MessengerColor
            $this->CreatedAt->AdvancedSearch->save(); // CreatedAt
            $this->CreatedBy->AdvancedSearch->save(); // CreatedBy
            $this->UpdatedAt->AdvancedSearch->save(); // UpdatedAt
            $this->UpdatedBy->AdvancedSearch->save(); // UpdatedBy
            $this->setSessionRules($rules);
        }

        // Return query
        return $where;
    }

    // Build search SQL
    protected function buildSearchSql(string &$where, DbField $fld, bool $default, bool $multiValue): void
    {
        $fldParm = $fld->Param;
        $fldVal = $default ? $fld->AdvancedSearch->SearchValueDefault : $fld->AdvancedSearch->SearchValue;
        $fldOpr = $default ? $fld->AdvancedSearch->SearchOperatorDefault : $fld->AdvancedSearch->SearchOperator;
        $fldCond = $default ? $fld->AdvancedSearch->SearchConditionDefault : $fld->AdvancedSearch->SearchCondition;
        $fldVal2 = $default ? $fld->AdvancedSearch->SearchValue2Default : $fld->AdvancedSearch->SearchValue2;
        $fldOpr2 = $default ? $fld->AdvancedSearch->SearchOperator2Default : $fld->AdvancedSearch->SearchOperator2;
        $fldVal = ConvertSearchValue($fldVal, $fldOpr, $fld);
        $fldVal2 = ConvertSearchValue($fldVal2, $fldOpr2, $fld);
        $fldOpr = ConvertSearchOperator($fldOpr, $fld, $fldVal);
        $fldOpr2 = ConvertSearchOperator($fldOpr2, $fld, $fldVal2);
        $wrk = "";
        $sep = $fld->UseFilter ? Config("FILTER_OPTION_SEPARATOR") : Config("MULTIPLE_OPTION_SEPARATOR");
        if (is_array($fldVal)) {
            $fldVal = implode($sep, $fldVal);
        }
        if (is_array($fldVal2)) {
            $fldVal2 = implode($sep, $fldVal2);
        }
        if (Config("SEARCH_MULTI_VALUE_OPTION") == 1 && !$fld->UseFilter || !IsMultiSearchOperator($fldOpr)) {
            $multiValue = false;
        }
        if ($multiValue) {
            $wrk = $fldVal != "" ? GetMultiSearchSql($fld, $fldOpr, $fldVal, $this->Dbid) : ""; // Field value 1
            $wrk2 = $fldVal2 != "" ? GetMultiSearchSql($fld, $fldOpr2, $fldVal2, $this->Dbid) : ""; // Field value 2
            AddFilter($wrk, $wrk2, $fldCond);
        } else {
            $wrk = GetSearchSql($fld, $fldVal, $fldOpr, $fldCond, $fldVal2, $fldOpr2, $this->Dbid);
        }
        if ($this->SearchOption == "AUTO" && in_array($this->BasicSearch->getType(), ["AND", "OR"])) {
            $cond = $this->BasicSearch->getType();
        } else {
            $cond = SameText($this->SearchOption, "OR") ? "OR" : "AND";
        }
        AddFilter($where, $wrk, $cond);
    }

    // Show list of filters
    public function showFilterList(): void
    {
        // Initialize
        $filterList = "";
        $captionClass = $this->isExport("email") ? "ew-filter-caption-email" : "ew-filter-caption";
        $captionSuffix = $this->isExport("email") ? ": " : "";

        // Field UserID
        $filter = $this->queryBuilderWhere("UserID");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->_UserID, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->_UserID->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }

        // Field Username
        $filter = $this->queryBuilderWhere("Username");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->_Username, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->_Username->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }

        // Field UserLevel
        $filter = $this->queryBuilderWhere("UserLevel");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->UserLevel, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->UserLevel->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }

        // Field CompleteName
        $filter = $this->queryBuilderWhere("CompleteName");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->CompleteName, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->CompleteName->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }

        // Field Photo
        $filter = $this->queryBuilderWhere("Photo");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->Photo, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->Photo->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }

        // Field Gender
        $filter = $this->queryBuilderWhere("Gender");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->Gender, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->Gender->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }

        // Field Email
        $filter = $this->queryBuilderWhere("Email");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->_Email, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->_Email->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }

        // Field Activated
        $filter = $this->queryBuilderWhere("Activated");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->Activated, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->Activated->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }

        // Field ActiveStatus
        $filter = $this->queryBuilderWhere("ActiveStatus");
        if (!$filter) {
            $this->buildSearchSql($filter, $this->ActiveStatus, false, false);
        }
        if ($filter != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->ActiveStatus->caption() . "</span>" . $captionSuffix . $filter . "</div>";
        }
        if ($this->BasicSearch->Keyword != "") {
            $filterList .= "<div><span class=\"" . $captionClass . "\">" . $this->language->phrase("BasicSearchKeyword") . "</span>" . $captionSuffix . $this->BasicSearch->Keyword . "</div>";
        }

        // Show Filters
        if ($filterList != "") {
            $message = "<div id=\"ew-filter-list\" class=\"callout callout-info d-table\"><div id=\"ew-current-filters\">" .
                $this->language->phrase("CurrentFilters") . "</div>" . $filterList . "</div>";
            $this->messageShowing($message, "");
            Write($message);
        } else { // Output empty tag
            Write("<div id=\"ew-filter-list\"></div>");
        }
    }

    // Return basic search WHERE clause based on search keyword and type
    public function basicSearchWhere(bool $default = false): string
    {
        $searchStr = "";
        if (!$this->security->canSearch()) {
            return "";
        }

        // Fields to search
        $searchFlds = [];
        $searchFlds[] = &$this->_Username;
        $searchFlds[] = &$this->FirstName;
        $searchFlds[] = &$this->LastName;
        $searchFlds[] = &$this->CompleteName;
        $searchFlds[] = &$this->HomePhone;
        $searchFlds[] = &$this->Photo;
        $searchFlds[] = &$this->Notes;
        $searchFlds[] = &$this->Gender;
        $searchFlds[] = &$this->_Email;
        $searchFlds[] = &$this->_Profile;
        $searchFlds[] = &$this->Avatar;
        $searchFlds[] = &$this->MessengerColor;
        $searchFlds[] = &$this->CreatedBy;
        $searchFlds[] = &$this->UpdatedBy;
        $searchKeyword = $default ? $this->BasicSearch->KeywordDefault : $this->BasicSearch->Keyword;
        $searchType = $default ? $this->BasicSearch->TypeDefault : $this->BasicSearch->Type;

        // Get search SQL
        if ($searchKeyword != "") {
            $ar = $this->BasicSearch->keywordList($default);
            $searchStr = GetQuickSearchFilter($searchFlds, $ar, $searchType, Config("BASIC_SEARCH_ANY_FIELDS"), $this->Dbid);
            if (!$default && in_array($this->Command, ["", "reset", "resetall"])) {
                $this->Command = "search";
            }
        }
        if (!$default && $this->Command == "search") {
            $this->BasicSearch->setKeyword($searchKeyword);
            $this->BasicSearch->setType($searchType);
        }
        return $searchStr;
    }

    // Check if search parm exists
    protected function checkSearchParms(): bool
    {
        // Check basic search
        if ($this->BasicSearch->issetSession()) {
            return true;
        }
        if ($this->_UserID->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->_Username->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->UserLevel->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->FirstName->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->LastName->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->CompleteName->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->BirthDate->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->HomePhone->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->Photo->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->Notes->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->ReportsTo->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->Gender->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->_Email->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->Activated->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->Avatar->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->ActiveStatus->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->MessengerColor->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->CreatedAt->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->CreatedBy->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->UpdatedAt->AdvancedSearch->issetSession()) {
            return true;
        }
        if ($this->UpdatedBy->AdvancedSearch->issetSession()) {
            return true;
        }
        return false;
    }

    // Clear all search parameters
    protected function resetSearchParms(): void
    {
        // Clear search WHERE clause
        $this->SearchWhere = "";
        $this->setSearchWhere($this->SearchWhere);

        // Clear basic search parameters
        $this->resetBasicSearchParms();

        // Clear advanced search parameters
        $this->resetAdvancedSearchParms();

        // Clear queryBuilder
        $this->setSessionRules("");
    }

    // Load advanced search default values
    protected function loadAdvancedSearchDefault(): bool
    {
        return false;
    }

    // Clear all basic search parameters
    protected function resetBasicSearchParms(): void
    {
        $this->BasicSearch->unsetSession();
    }

    // Clear all advanced search parameters
    protected function resetAdvancedSearchParms(): void
    {
        $this->_UserID->AdvancedSearch->unsetSession();
        $this->_Username->AdvancedSearch->unsetSession();
        $this->UserLevel->AdvancedSearch->unsetSession();
        $this->FirstName->AdvancedSearch->unsetSession();
        $this->LastName->AdvancedSearch->unsetSession();
        $this->CompleteName->AdvancedSearch->unsetSession();
        $this->BirthDate->AdvancedSearch->unsetSession();
        $this->HomePhone->AdvancedSearch->unsetSession();
        $this->Photo->AdvancedSearch->unsetSession();
        $this->Notes->AdvancedSearch->unsetSession();
        $this->ReportsTo->AdvancedSearch->unsetSession();
        $this->Gender->AdvancedSearch->unsetSession();
        $this->_Email->AdvancedSearch->unsetSession();
        $this->Activated->AdvancedSearch->unsetSession();
        $this->Avatar->AdvancedSearch->unsetSession();
        $this->ActiveStatus->AdvancedSearch->unsetSession();
        $this->MessengerColor->AdvancedSearch->unsetSession();
        $this->CreatedAt->AdvancedSearch->unsetSession();
        $this->CreatedBy->AdvancedSearch->unsetSession();
        $this->UpdatedAt->AdvancedSearch->unsetSession();
        $this->UpdatedBy->AdvancedSearch->unsetSession();
    }

    // Restore all search parameters
    protected function restoreSearchParms(): void
    {
        $this->RestoreSearch = true;

        // Restore basic search values
        $this->BasicSearch->load();

        // Restore advanced search values
        $this->_UserID->AdvancedSearch->load();
        $this->_Username->AdvancedSearch->load();
        $this->UserLevel->AdvancedSearch->load();
        $this->FirstName->AdvancedSearch->load();
        $this->LastName->AdvancedSearch->load();
        $this->CompleteName->AdvancedSearch->load();
        $this->BirthDate->AdvancedSearch->load();
        $this->HomePhone->AdvancedSearch->load();
        $this->Photo->AdvancedSearch->load();
        $this->Notes->AdvancedSearch->load();
        $this->ReportsTo->AdvancedSearch->load();
        $this->Gender->AdvancedSearch->load();
        $this->_Email->AdvancedSearch->load();
        $this->Activated->AdvancedSearch->load();
        $this->Avatar->AdvancedSearch->load();
        $this->ActiveStatus->AdvancedSearch->load();
        $this->MessengerColor->AdvancedSearch->load();
        $this->CreatedAt->AdvancedSearch->load();
        $this->CreatedBy->AdvancedSearch->load();
        $this->UpdatedAt->AdvancedSearch->load();
        $this->UpdatedBy->AdvancedSearch->load();
    }

    // Set up sort parameters
    protected function setupSortOrder(): void
    {
        // Load default Sorting Order
        if ($this->Command != "json") {
            $defaultSort = ""; // Set up default sort
            if ($this->getSessionOrderBy() == "" && $defaultSort != "") {
                $this->setSessionOrderBy($defaultSort);
            }
        }

        // Check for "order" parameter
        if (Get("order") !== null) {
            $this->CurrentOrder = Get("order");
            $this->CurrentOrderType = Get("ordertype", "");
            $this->updateSort($this->_UserID); // UserID
            $this->updateSort($this->_Username); // Username
            $this->updateSort($this->UserLevel); // UserLevel
            $this->updateSort($this->CompleteName); // CompleteName
            $this->updateSort($this->Photo); // Photo
            $this->updateSort($this->Gender); // Gender
            $this->updateSort($this->_Email); // Email
            $this->updateSort($this->Activated); // Activated
            $this->updateSort($this->ActiveStatus); // ActiveStatus
            $this->setStartRecordNumber(1); // Reset start position
        }

        // Update field sort
        $this->updateFieldSort();
    }

    // Reset command
    // - cmd=reset (Reset search parameters)
    // - cmd=resetall (Reset search and master/detail parameters)
    // - cmd=resetsort (Reset sort parameters)
    protected function resetCmd(): void
    {
        // Check if reset command
        if (StartsString("reset", $this->Command)) {
            // Reset search criteria
            if ($this->Command == "reset" || $this->Command == "resetall") {
                $this->resetSearchParms();
            }

            // Reset master/detail keys
            if ($this->Command == "resetall") {
                $this->setCurrentMasterTable(""); // Clear master table
                $this->DbMasterFilter = "";
                $this->DbDetailFilter = "";
                        $this->UserLevel->setSessionValue("");
            }

            // Reset (clear) sorting order
            if ($this->Command == "resetsort") {
                $orderBy = "";
                $this->setSessionOrderBy($orderBy);
                $this->_UserID->setSort("");
                $this->_Username->setSort("");
                $this->_Password->setSort("");
                $this->UserLevel->setSort("");
                $this->FirstName->setSort("");
                $this->LastName->setSort("");
                $this->CompleteName->setSort("");
                $this->BirthDate->setSort("");
                $this->HomePhone->setSort("");
                $this->Photo->setSort("");
                $this->Notes->setSort("");
                $this->ReportsTo->setSort("");
                $this->Gender->setSort("");
                $this->_Email->setSort("");
                $this->Activated->setSort("");
                $this->_Profile->setSort("");
                $this->Avatar->setSort("");
                $this->ActiveStatus->setSort("");
                $this->MessengerColor->setSort("");
                $this->CreatedAt->setSort("");
                $this->CreatedBy->setSort("");
                $this->UpdatedAt->setSort("");
                $this->UpdatedBy->setSort("");
            }

            // Reset start position
            $this->StartRecord = 1;
            $this->setStartRecordNumber($this->StartRecord);
        }
    }

    // Set up list options
    protected function setupListOptions(): void
    {
        // Add group option item ("button")
        $item = &$this->ListOptions->addGroupOption();
        $item->Body = "";
        $item->OnLeft = true;
        $item->Visible = false;

        // "view"
        $item = &$this->ListOptions->add("view");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->canView();
        $item->OnLeft = true;

        // "edit"
        $item = &$this->ListOptions->add("edit");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->canEdit();
        $item->OnLeft = true;

        // "copy"
        $item = &$this->ListOptions->add("copy");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->canAdd();
        $item->OnLeft = true;

        // "delete"
        $item = &$this->ListOptions->add("delete");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->canDelete();
        $item->OnLeft = true;

        // List actions
        $item = &$this->ListOptions->add("listactions");
        $item->CssClass = "text-nowrap";
        $item->OnLeft = true;
        $item->Visible = false;
        $item->ShowInButtonGroup = false;
        $item->ShowInDropDown = false;

        // "checkbox"
        $item = &$this->ListOptions->add("checkbox");
        $item->Visible = false;
        $item->OnLeft = true;
        $item->Header = "<div class=\"form-check\"><input type=\"checkbox\" name=\"key\" id=\"key\" class=\"form-check-input\" data-ew-action=\"select-all-keys\"></div>";
        if ($item->OnLeft) {
            $item->moveTo(0);
        }
        $item->ShowInDropDown = false;
        $item->ShowInButtonGroup = false;

        // "sequence"
        $item = &$this->ListOptions->add("sequence");
        $item->CssClass = "text-nowrap";
        $item->Visible = true;
        $item->OnLeft = true; // Always on left
        $item->ShowInDropDown = false;
        $item->ShowInButtonGroup = false;

        // Drop down button for ListOptions
        $this->ListOptions->UseDropDownButton = false;
        $this->ListOptions->DropDownButtonPhrase = $this->language->phrase("ButtonListOptions");
        $this->ListOptions->UseButtonGroup = true;
        if ($this->ListOptions->UseButtonGroup && IsMobile()) {
            $this->ListOptions->UseDropDownButton = true;
        }

        // $this->ListOptions->ButtonClass = ""; // Class for button group

            // Set up list options (to be implemented by extensions)

        // Preview extension
        $this->ListOptions->hideDetailItemsForDropDown(); // Hide detail items for dropdown if necessary

        // Call ListOptions_Load event
        $this->listOptionsLoad();
        $item = $this->ListOptions[$this->ListOptions->GroupOptionName];
        $item->Visible = $this->ListOptions->groupOptionVisible();
    }

    // Add "hash" parameter to URL
    public function urlAddHash(string $url, string $hash): string
    {
        return $this->UseAjaxActions ? $url : UrlAddQuery($url, "hash=" . $hash);
    }

    // Render list options
    public function renderListOptions(): void
    {
        $this->ListOptions->loadDefault();

        // Call ListOptions_Rendering event
        $this->listOptionsRendering();

        // "sequence"
        $opt = $this->ListOptions["sequence"];
        $opt->Body = FormatSequenceNumber($this->RecordCount);
        $pageUrl = $this->pageUrl(false);
        if ($this->CurrentMode == "view") {
            // "view"
            $opt = $this->ListOptions["view"];
            $viewcaption = HtmlTitle($this->language->phrase("ViewLink"));
            if ($this->security->canView() && $this->showOptionLink("view")) {
                if ($this->ModalView && !IsMobile()) {
                    $opt->Body = "<a class=\"ew-row-link ew-view\" title=\"" . $viewcaption . "\" data-table=\"users\" data-caption=\"" . $viewcaption . "\" data-ew-action=\"modal\" data-action=\"view\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->ViewUrl)) . "\" data-btn=\"null\">" . $this->language->phrase("ViewLink") . "</a>";
                } else {
                    $opt->Body = "<a class=\"ew-row-link ew-view\" title=\"" . $viewcaption . "\" data-caption=\"" . $viewcaption . "\" href=\"" . HtmlEncode(GetUrl($this->ViewUrl)) . "\">" . $this->language->phrase("ViewLink") . "</a>";
                }
            } else {
                $opt->Body = "";
            }

            // "edit"
            $opt = $this->ListOptions["edit"];
            $editcaption = HtmlTitle($this->language->phrase("EditLink"));
            if ($this->security->canEdit() && $this->showOptionLink("edit")) {
                if ($this->ModalEdit && !IsMobile()) {
					$opt->Body = "<a class=\"ew-row-link ew-edit\" title=\"" . $editcaption . "\" data-table=\"users\" data-caption=\"" . $editcaption . "\" data-ew-action=\"modal\" data-action=\"edit\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->EditUrl)) . "\" data-ask=\"1\" data-btn=\"SaveBtn\">" . $this->language->phrase("EditLink") . "</a>";
                } else {
                    $opt->Body = "<a class=\"ew-row-link ew-edit\" title=\"" . $editcaption . "\" data-caption=\"" . $editcaption . "\" href=\"" . HtmlEncode(GetUrl($this->EditUrl)) . "\">" . $this->language->phrase("EditLink") . "</a>";
                }
            } else {
                $opt->Body = "";
            }

            // "copy"
            $opt = $this->ListOptions["copy"];
            $copycaption = HtmlTitle($this->language->phrase("CopyLink"));
            if ($this->security->canAdd() && $this->showOptionLink("add")) {
                if ($this->ModalAdd && !IsMobile()) {
					$opt->Body = "<a class=\"ew-row-link ew-copy\" title=\"" . $copycaption . "\" data-table=\"users\" data-caption=\"" . $copycaption . "\" data-ew-action=\"modal\" data-action=\"add\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->CopyUrl)) . "\" data-ask=\"1\" data-btn=\"AddBtn\">" . $this->language->phrase("CopyLink") . "</a>";
                } else {
                    $opt->Body = "<a class=\"ew-row-link ew-copy\" title=\"" . $copycaption . "\" data-caption=\"" . $copycaption . "\" href=\"" . HtmlEncode(GetUrl($this->CopyUrl)) . "\">" . $this->language->phrase("CopyLink") . "</a>";
                }
            } else {
                $opt->Body = "";
            }

            // "delete"
            $opt = $this->ListOptions["delete"];
            if ($this->security->canDelete() && $this->showOptionLink("delete")) {
                $deleteCaption = $this->language->phrase("DeleteLink");
                $deleteTitle = HtmlTitle($deleteCaption);
                if ($this->UseAjaxActions) {
                    $opt->Body = "<a class=\"ew-row-link ew-delete\" data-ew-action=\"inline\" data-action=\"delete\" title=\"" . $deleteTitle . "\" data-caption=\"" . $deleteTitle . "\" data-key= \"" . HtmlEncode($this->getKey(true)) . "\" data-url=\"" . HtmlEncode(GetUrl($this->DeleteUrl)) . "\">" . $deleteCaption . "</a>";
                } else {
                    $opt->Body = "<a class=\"ew-row-link ew-delete\"" .
                        ($this->InlineDelete ? " data-ew-action=\"inline-delete\"" : "") .
                        " title=\"" . $deleteTitle . "\" data-caption=\"" . $deleteTitle . "\" href=\"" . HtmlEncode(GetUrl($this->DeleteUrl)) . "\">" . $deleteCaption . "</a>";
                }
            } else {
                $opt->Body = "";
            }
        } // End View mode

        // Render list action buttons (single selection)
        $opt = $this->ListOptions["listactions"];
        if ($opt && !$this->isExport() && !$this->CurrentAction) {
            $body = "";
            $links = [];
            foreach ($this->ListActions as $listAction) { // ActionType::SINGLE
                if (in_array($this->RowType, [RowType::VIEW, RowType::PREVIEW])) {
                    $listAction->setFields($this->Fields);
                }
                if ($listAction->Select == ActionType::SINGLE && $listAction->getVisible()) {
                    $caption = $listAction->getCaption();
                    $title = HtmlTitle($caption);
                    $icon = $listAction->Icon ? "<i class=\"" . HtmlEncode(str_replace(" ew-icon", "", $listAction->Icon)) . "\" data-caption=\"" . $title . "\"></i> " : "";
                    $link = "<li><button type=\"button\" class=\"dropdown-item ew-action ew-list-action" . ($listAction->getEnabled() ? "" : " disabled") .
                        "\" data-caption=\"" . $title . "\" data-ew-action=\"submit\" form=\"fuserslist\" data-key=\"" . $this->keyToJson(true) .
                        "\"" . $listAction->toDataAttributes() . ">" . $icon . " " . $caption . "</button></li>";
                    $links[] = $link;
                    if ($body == "") { // Setup first button
                        $body = "<button type=\"button\" class=\"btn btn-default ew-action ew-list-action" . ($listAction->getEnabled() ? "" : " disabled") .
                            "\" title=\"" . $title . "\" data-caption=\"" . $title . "\" data-ew-action=\"submit\" form=\"fuserslist\" data-key=\"" . $this->keyToJson(true) .
                            "\"" . $listAction->toDataAttributes() . ">" . $icon . " " . $caption . "</button>";
                    }
                }
            }
            if (count($links) > 1) { // More than one buttons, use dropdown
                $body = "<button type=\"button\" class=\"dropdown-toggle btn btn-default ew-actions\" title=\"" . HtmlTitle($this->language->phrase("ListActionButton")) . "\" data-bs-toggle=\"dropdown\">" . $this->language->phrase("ListActionButton") . "</button>";
                $content = implode(array_map(fn($link) => "<li>" . $link . "</li>", $links));
                $body .= "<ul class=\"dropdown-menu" . ($opt->OnLeft ? "" : " dropdown-menu-right") . "\">" . $content . "</ul>";
                $body = "<div class=\"btn-group btn-group-sm\">" . $body . "</div>";
            }
            if (count($links) > 0) {
                $opt->Body = $body;
            }
        }

        // "checkbox"
        $opt = $this->ListOptions["checkbox"];
        $opt->Body = "<div class=\"form-check\"><input type=\"checkbox\" id=\"key_m_" . $this->RowCount . "\" name=\"key_m[]\" class=\"form-check-input ew-multi-select\" value=\"" . HtmlEncode($this->_UserID->CurrentValue) . "\" data-ew-action=\"select-key\"></div>";

        // Render list options (to be implemented by extensions)

        // Call ListOptions_Rendered event
        $this->listOptionsRendered();
    }

    // Set up other options
    protected function setupOtherOptions(): void
    {
        $options = &$this->OtherOptions;
        $option = $options["addedit"];

        // Add
        $item = &$option->add("add");
        $addcaption = HtmlTitle($this->language->phrase("AddLink"));
        if ($this->ModalAdd && !IsMobile()) {
			$item->Body = "<a class=\"ew-add-edit ew-add\" title=\"" . $addcaption . "\" data-table=\"users\" data-caption=\"" . $addcaption . "\" data-ew-action=\"modal\" data-action=\"add\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->AddUrl)) . "\" data-ask=\"1\" data-btn=\"AddBtn\">" . $this->language->phrase("AddLink") . "</a>";
        } else {
            $item->Body = "<a class=\"ew-add-edit ew-add\" title=\"" . $addcaption . "\" data-caption=\"" . $addcaption . "\" href=\"" . HtmlEncode(GetUrl($this->AddUrl)) . "\">" . $this->language->phrase("AddLink") . "</a>";
        }
        $item->Visible = $this->AddUrl != "" && $this->security->canAdd();
        $option = $options["action"];

        // Show column list for column visibility
        if ($this->UseColumnVisibility) {
            $option = $this->OtherOptions["column"];
            $item = &$option->addGroupOption();
            $item->Body = "";
            $item->Visible = $this->UseColumnVisibility;
            $this->createColumnOption($option, "UserID");
            $this->createColumnOption($option, "Username");
            $this->createColumnOption($option, "UserLevel");
            $this->createColumnOption($option, "CompleteName");
            $this->createColumnOption($option, "Photo");
            $this->createColumnOption($option, "Gender");
            $this->createColumnOption($option, "Email");
            $this->createColumnOption($option, "Activated");
            $this->createColumnOption($option, "ActiveStatus");
        }
        $this->ListActions["resetconcurrentuser"] = new ResetConcurrentUserAction();
        $this->ListActions["resetloginretry"] = new ResetLoginRetryAction();
        $this->ListActions["setpasswordexpired"] = new SetPasswordExpiredAction();
        $this->ListActions["forcelogoutuser"] = new ForceLogoutUserAction();
        $this->ListActions["switchuser"] = new SwitchUserAction();
        $this->ListActions["sendloginlink"] = new SendLoginLinkAction();

        // Set up custom actions
        foreach ($this->CustomActions as $name => $action) {
            $this->ListActions[$name] = $action;
        }

        // Set up options default
        foreach ($options as $name => $option) {
            if ($name != "column") { // Always use dropdown for column
                $option->UseDropDownButton = false;
                $option->UseButtonGroup = true;
            }
            //$option->ButtonClass = ""; // Class for button group
            $item = &$option->addGroupOption();
            $item->Body = "";
            $item->Visible = false;
        }
        $options["addedit"]->DropDownButtonPhrase = $this->language->phrase("ButtonAddEdit");
        $options["detail"]->DropDownButtonPhrase = $this->language->phrase("ButtonDetails");
        $options["action"]->DropDownButtonPhrase = $this->language->phrase("ButtonActions");

        // Filter button
        $item = &$this->FilterOptions->add("savecurrentfilter");
        $item->Body = "<a class=\"ew-save-filter\" data-form=\"fuserssrch\" data-ew-action=\"none\">" . $this->language->phrase("SaveCurrentFilter") . "</a>";
        $item->Visible = true;
        $item = &$this->FilterOptions->add("deletefilter");
        $item->Body = "<a class=\"ew-delete-filter\" data-form=\"fuserssrch\" data-ew-action=\"none\">" . $this->language->phrase("DeleteFilter") . "</a>";
        $item->Visible = true;
        $this->FilterOptions->UseDropDownButton = true;
        $this->FilterOptions->UseButtonGroup = !$this->FilterOptions->UseDropDownButton;
        $this->FilterOptions->DropDownButtonPhrase = $this->language->phrase("Filters");

        // Add group option item
        $item = &$this->FilterOptions->addGroupOption();
        $item->Body = "";
        $item->Visible = false;

        // Page header/footer options
        $this->HeaderOptions = new ListOptions(TagClassName: "ew-header-option", UseDropDownButton: false, UseButtonGroup: false);
        $item = &$this->HeaderOptions->addGroupOption();
        $item->Body = "";
        $item->Visible = false;
        $this->FooterOptions = new ListOptions(TagClassName: "ew-footer-option", UseDropDownButton: false, UseButtonGroup: false);
        $item = &$this->FooterOptions->addGroupOption();
        $item->Body = "";
        $item->Visible = false;

        // Show active user count from SQL
        if (IsAdmin()) {
            $activeUserCount = $this->getConnection()->fetchOne("SELECT COUNT(*) FROM " . $this->getSqlFrom() . " WHERE " . $this->activeUserFilter());
            $showActiveUser = Param("activeuser", "");
            if ($showActiveUser != "") {
                Session(AddTabId(SESSION_ACTIVE_USERS), $showActiveUser);
            } elseif (Session(AddTabId(SESSION_ACTIVE_USERS)) != "") {
                $showActiveUser = Session(AddTabId(SESSION_ACTIVE_USERS));
            }
            if ($showActiveUser == "1" && $activeUserCount > 0) {
                AddFilter($this->Filter, $this->activeUserFilter());
            }
            $message = sprintf($this->language->phrase("ShowActiveUsers"), $activeUserCount);
            $item = &$this->HeaderOptions->add("activeuser");
            $checked = $showActiveUser == "1" ? " checked" : "";
            $item->Body = "<div class=\"form-check\"><input type=\"checkbox\" name=\"activeuser\" id=\"activeuser\" class=\"form-check-input\" data-ew-action=\"active-user\"{$checked}>" . $message . "</div>";
            $item->Visible = $activeUserCount > 0;
            $item->ShowInDropDown = false;
            $item->ShowInButtonGroup = false;
        }
    }

    // Active user filter
    // - Get active users by SQL (SELECT COUNT(*) FROM UserTable WHERE ProfileField LIKE '%"SessionID":%')
    protected function activeUserFilter(): string
    {
        if (UserProfile::$FORCE_LOGOUT_USER_ENABLED) {
            $userProfileField = $this->Fields[Config("USER_PROFILE_FIELD_NAME")];
            return $userProfileField->Expression . " LIKE '%\"" . UserProfile::$SESSION_ID . "\":%'";
        }
        return "0=1"; // No active users
    }

    // Create new column option
    protected function createColumnOption(ListOptions $options, string $name): void
    {
        $field = $this->Fields[$name] ?? null;
        if ($field?->Visible) {
            $item = $options->add($field->Name);
            $item->Body = '<button class="dropdown-item">' .
                '<div class="form-check ew-dropdown-checkbox">' .
                '<div class="form-check-input ew-dropdown-check-input" data-field="' . $field->Param . '"></div>' .
                '<label class="form-check-label ew-dropdown-check-label">' . $field->caption() . '</label></div></button>';
        }
    }

    // Render other options
    public function renderOtherOptions(): void
    {
        $options = &$this->OtherOptions;
        $option = $options["action"];
        // Render list action buttons
        foreach ($this->ListActions as $listAction) { // ActionType::MULTIPLE
            if ($listAction->Select == ActionType::MULTIPLE && $listAction->getVisible()) {
                $item = &$option->add("custom_" . $listAction->Action);
                $caption = $listAction->getCaption();
                $icon = $listAction->Icon ? '<i class="' . HtmlEncode($listAction->Icon) . '" data-caption="' . HtmlEncode($caption) . '"></i>' . $caption : $caption;
                $item->Body = '<button type="button" class="btn btn-default ew-action ew-list-action" title="' . HtmlEncode($caption) . '" data-caption="' . HtmlEncode($caption) . '" data-ew-action="submit" form="fuserslist"' . $listAction->toDataAttributes() . '>' . $icon . '</button>';
                $item->Visible = true;
            }
        }

        // Hide multi edit, grid edit and other options
        if ($this->TotalRecords <= 0) {
            $option = $options["addedit"];
            $item = $option["gridedit"];
            if ($item) {
                $item->Visible = false;
            }
            $option = $options["action"];
            $option->hideAllOptions();
        }
    }

    // Process list action
    protected function processListAction(): bool
    {
        $users = [];
        $user = "";
        $filter = $this->getFilterFromRecordKeys();
        $userAction = Post("action", "");
        if ($filter != "" && $userAction != "") {
            $conn = $this->getConnection();
            // Clear current action
            $this->CurrentAction = "";
            // Check permission first
            $caption = $userAction;
            $listAction = $this->ListActions[$userAction] ?? null;
            if ($listAction) {
                $this->UserAction = $userAction;
                $caption = $listAction->getCaption();
                if (!$listAction->Allowed) {
                    $errmsg = sprintf($this->language->phrase("CustomActionNotAllowed"), $caption);
                    if (Post("ajax") == $userAction) { // Ajax
                        echo "<p class=\"text-danger\">" . $errmsg . "</p>";
                        return true;
                    } else {
                        $this->setFailureMessage($errmsg);
                        return false;
                    }
                }
            } else {
                // Skip checking, handle by Row_CustomAction
            }
            $rows = $this->loadRecords($filter)->fetchAllAssociative();
            $this->SelectedCount = count($rows);
            $this->ActionValue = Post("actionvalue");

            // Call row action event
            if ($this->SelectedCount > 0) {
                if ($this->UseTransaction) {
                    $conn->beginTransaction();
                }
                $this->SelectedIndex = 0;
                foreach ($rows as $row) {
                    $this->SelectedIndex++;
                    if ($listAction) {
                        $processed = $listAction->handle($row, $this);
                        if (!$processed) {
                            break;
                        }
                    }
                    $processed = $this->rowCustomAction($userAction, $row);
                    if (!$processed) {
                        break;
                    }
                }
                if ($processed) {
                    if ($this->UseTransaction) { // Commit transaction
                        if ($conn->isTransactionActive()) {
                            $conn->commit();
                        }
                    }
                    if (!$this->peekSuccessMessage() && !IsEmpty($listAction?->SuccessMessage)) {
                        $userlist = implode(",", array_column($rows, Config("LOGIN_USERNAME_FIELD_NAME")));
                        $this->setSuccessMessage(sprintf($listAction->SuccessMessage, $userlist));
                    }
                    if (!$this->peekSuccessMessage()) {
                        $this->setSuccessMessage(sprintf($this->language->phrase("CustomActionCompleted"), $caption)); // Set up success message
                    }
                } else {
                    if ($this->UseTransaction) { // Rollback transaction
                        if ($conn->isTransactionActive()) {
                            $conn->rollback();
                        }
                    }
                    if (!$this->peekFailureMessage()) {
                        $user = $row[Config("LOGIN_USERNAME_FIELD_NAME")];
                        $this->setFailureMessage(sprintf($listAction->FailureMessage, $user));
                    }

                    // Set up error message
                    if ($this->peekSuccessMessage() || $this->peekFailureMessage()) {
                        // Use the message, do nothing
                    } elseif ($this->CancelMessage != "") {
                        $this->setFailureMessage($this->CancelMessage);
                        $this->CancelMessage = "";
                    } else {
                        $this->setFailureMessage(sprintf($this->language->phrase("CustomActionFailed"), $caption));
                    }
                }
            }
            if (Post("ajax") == $userAction) { // Ajax
                if (HasJsonResponse()) { // List action returns JSON
                    $this->clearMessages(); // Clear messages
                } else {
                    if ($this->peekSuccessMessage()) {
                        echo "<p class=\"text-success\">" . $this->getSuccessMessage() . "</p>";
                    }
                    if ($this->peekFailureMessage()) {
                        echo "<p class=\"text-danger\">" . $this->getFailureMessage() . "</p>";
                    }
                }
                return true;
            }
        }
        return false; // Not ajax request
    }

    // Set up Grid
    public function setupGrid(): void
    {
        if ($this->ExportAll && $this->isExport()) {
            $this->StopRecord = $this->TotalRecords;
        } else {
            // Set the last record to display
            if ($this->TotalRecords > $this->StartRecord + $this->DisplayRecords - 1) {
                $this->StopRecord = $this->StartRecord + $this->DisplayRecords - 1;
            } else {
                $this->StopRecord = $this->TotalRecords;
            }
        }
        $this->RecordCount = $this->StartRecord - 1;
        if ($this->CurrentRow !== false) {
            // Nothing to do
        } elseif ($this->isGridAdd() && !$this->AllowAddDeleteRow && $this->StopRecord == 0) { // Grid-Add with no records
            $this->StopRecord = $this->GridAddRowCount;
        } elseif ($this->isAdd() && $this->TotalRecords == 0) { // Inline-Add with no records
            $this->StopRecord = 1;
        }

        // Initialize aggregate
        $this->RowType = RowType::AGGREGATEINIT;
        $this->resetAttributes();
        $this->renderRow();
        if (($this->isGridAdd() || $this->isGridEdit())) { // Render template row first
            $this->RowIndex = '$rowindex$';
        }
    }

    // Set up Row
    public function setupRow(): void
    {
        if ($this->isGridAdd() || $this->isGridEdit()) {
            if ($this->RowIndex === '$rowindex$') { // Render template row first
                $this->loadRowValues();

                // Set row properties
                $this->resetAttributes();
                $this->RowAttrs->merge(["data-rowindex" => $this->RowIndex, "id" => "r0_users", "data-rowtype" => RowType::ADD]);
                $this->RowAttrs->appendClass("ew-template");
                // Render row
                $this->RowType = RowType::ADD;
                $this->renderRow();

                // Render list options
                $this->renderListOptions();

                // Reset record count for template row
                $this->RecordCount--;
                return;
            }
        }

        // Set up key count
        $this->KeyCount = $this->RowIndex;

        // Init row class and style
        $this->resetAttributes();
        $this->CssClass = "";
        if ($this->isCopy() && $this->InlineRowCount == 0 && !$this->loadRow()) { // Inline copy
            $this->CurrentAction = "add";
        }
        if ($this->isAdd() && $this->InlineRowCount == 0 || $this->isGridAdd()) {
            $this->loadRowValues(); // Load default values
            $this->OldKey = "";
            $this->setKey($this->OldKey);
        } elseif ($this->isInlineInserted() && $this->UseInfiniteScroll) {
            // Nothing to do, just use current values
        } elseif (!($this->isCopy() && $this->InlineRowCount == 0)) {
            $this->loadRowValues($this->CurrentRow); // Load row values
            if ($this->isGridEdit() || $this->isMultiEdit()) {
                $this->OldKey = $this->getKey(true); // Get from CurrentValue
                $this->setKey($this->OldKey);
            }
        }
        $this->RowType = RowType::VIEW; // Render view
        if (($this->isAdd() || $this->isCopy()) && $this->InlineRowCount == 0 || $this->isGridAdd()) { // Add
            $this->RowType = RowType::ADD; // Render add
        }

        // Inline Add/Copy row (row 0)
        if ($this->RowType == RowType::ADD && ($this->isAdd() || $this->isCopy())) {
            $this->InlineRowCount++;
            $this->RecordCount--; // Reset record count for inline add/copy row
            if ($this->TotalRecords == 0) { // Reset stop record if no records
                $this->StopRecord = 0;
            }
        } else {
            // Inline Edit row
            if ($this->RowType == RowType::EDIT && $this->isEdit()) {
                $this->InlineRowCount++;
            }
            $this->RowCount++; // Increment row count
        }

        // Set up row attributes
        $this->RowAttrs->merge([
            "data-rowindex" => $this->RowCount,
            "data-key" => $this->getKey(true),
            "id" => "r" . $this->RowCount . "_users",
            "data-rowtype" => $this->RowType,
            "data-inline" => ($this->isAdd() || $this->isCopy() || $this->isEdit()) ? "true" : "false", // Inline-Add/Copy/Edit
            "class" => ($this->RowCount % 2 != 1) ? "ew-table-alt-row" : "",
        ]);
        if ($this->isAdd() && $this->RowType == RowType::ADD || $this->isEdit() && $this->RowType == RowType::EDIT) { // Inline-Add/Edit row
            $this->RowAttrs->appendClass("table-active");
        }

        // Render row
        $this->renderRow();

        // Render list options
        $this->renderListOptions();
    }

// Load basic search values
    protected function loadBasicSearchValues(): bool
    {
        $keyword = Get(Config("TABLE_BASIC_SEARCH"));
        if ($keyword === null) {
            return false;
        } else {
            $this->BasicSearch->setKeyword($keyword, false);
            if ($this->BasicSearch->Keyword != "" && $this->Command == "") {
                $this->Command = "search";
            }
            $this->BasicSearch->setType(Get(Config("TABLE_BASIC_SEARCH_TYPE"), ""), false);
            return true;
        }
    }

    // Load search values for validation
    protected function loadSearchValues(): bool
    {
        // Load search values
        $hasValue = false;

        // Load query builder rules
        $rules = Post("rules");
        if ($rules && $this->Command == "") {
            $this->QueryRules = $rules;
            $this->Command = "search";
        }

        // UserID
        if ($this->_UserID->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->_UserID->AdvancedSearch->SearchValue != "" || $this->_UserID->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // Username
        if ($this->_Username->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->_Username->AdvancedSearch->SearchValue != "" || $this->_Username->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // UserLevel
        if ($this->UserLevel->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->UserLevel->AdvancedSearch->SearchValue != "" || $this->UserLevel->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // FirstName
        if ($this->FirstName->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->FirstName->AdvancedSearch->SearchValue != "" || $this->FirstName->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // LastName
        if ($this->LastName->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->LastName->AdvancedSearch->SearchValue != "" || $this->LastName->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // CompleteName
        if ($this->CompleteName->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->CompleteName->AdvancedSearch->SearchValue != "" || $this->CompleteName->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // BirthDate
        if ($this->BirthDate->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->BirthDate->AdvancedSearch->SearchValue != "" || $this->BirthDate->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // HomePhone
        if ($this->HomePhone->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->HomePhone->AdvancedSearch->SearchValue != "" || $this->HomePhone->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // Photo
        if ($this->Photo->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->Photo->AdvancedSearch->SearchValue != "" || $this->Photo->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // Notes
        if ($this->Notes->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->Notes->AdvancedSearch->SearchValue != "" || $this->Notes->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // ReportsTo
        if ($this->ReportsTo->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->ReportsTo->AdvancedSearch->SearchValue != "" || $this->ReportsTo->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // Gender
        if ($this->Gender->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->Gender->AdvancedSearch->SearchValue != "" || $this->Gender->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // Email
        if ($this->_Email->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->_Email->AdvancedSearch->SearchValue != "" || $this->_Email->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // Activated
        if ($this->Activated->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->Activated->AdvancedSearch->SearchValue != "" || $this->Activated->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }
        if (is_array($this->Activated->AdvancedSearch->SearchValue)) {
            $this->Activated->AdvancedSearch->SearchValue = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $this->Activated->AdvancedSearch->SearchValue);
        }
        if (is_array($this->Activated->AdvancedSearch->SearchValue2)) {
            $this->Activated->AdvancedSearch->SearchValue2 = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $this->Activated->AdvancedSearch->SearchValue2);
        }

        // Avatar
        if ($this->Avatar->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->Avatar->AdvancedSearch->SearchValue != "" || $this->Avatar->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // ActiveStatus
        if ($this->ActiveStatus->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->ActiveStatus->AdvancedSearch->SearchValue != "" || $this->ActiveStatus->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }
        if (is_array($this->ActiveStatus->AdvancedSearch->SearchValue)) {
            $this->ActiveStatus->AdvancedSearch->SearchValue = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $this->ActiveStatus->AdvancedSearch->SearchValue);
        }
        if (is_array($this->ActiveStatus->AdvancedSearch->SearchValue2)) {
            $this->ActiveStatus->AdvancedSearch->SearchValue2 = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $this->ActiveStatus->AdvancedSearch->SearchValue2);
        }

        // MessengerColor
        if ($this->MessengerColor->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->MessengerColor->AdvancedSearch->SearchValue != "" || $this->MessengerColor->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // CreatedAt
        if ($this->CreatedAt->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->CreatedAt->AdvancedSearch->SearchValue != "" || $this->CreatedAt->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // CreatedBy
        if ($this->CreatedBy->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->CreatedBy->AdvancedSearch->SearchValue != "" || $this->CreatedBy->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // UpdatedAt
        if ($this->UpdatedAt->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->UpdatedAt->AdvancedSearch->SearchValue != "" || $this->UpdatedAt->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }

        // UpdatedBy
        if ($this->UpdatedBy->AdvancedSearch->get()) {
            $hasValue = true;
            if (($this->UpdatedBy->AdvancedSearch->SearchValue != "" || $this->UpdatedBy->AdvancedSearch->SearchValue2 != "") && $this->Command == "") {
                $this->Command = "search";
            }
        }
        return $hasValue;
    }

    /**
     * Load result
     *
     * @param int $offset Offset
     * @param int $rowcnt Maximum number of rows
     * @return Result
     */
    public function loadResult(int $offset = -1, int $rowcnt = -1): Result
    {
        // Load List page SQL (QueryBuilder)
        $sql = $this->getListSql();

        // Load result set
        if ($offset > -1) {
            $sql->setFirstResult($offset);
        }
        if ($rowcnt > 0) {
            $sql->setMaxResults($rowcnt);
        }
        $result = $sql->executeQuery();
        if (property_exists($this, "TotalRecords") && $rowcnt < 0) {
            $this->TotalRecords = $result->rowCount();
            if ($this->TotalRecords <= 0) { // Handle database drivers that does not return rowCount()
                $this->TotalRecords = $this->getRecordCount($this->getListSql());
            }
        }

        // Call Records Selected event
        $this->recordsSelected($result);
        return $result;
    }

    /**
     * Load records as associative array
     *
     * @param int $offset Offset
     * @param int $rowcnt Maximum number of rows
     * @return array|bool
     */
    public function loadRows(int $offset = -1, int $rowcnt = -1): array|bool
    {
        // Load List page SQL (QueryBuilder)
        $sql = $this->getListSql();

        // Load result set
        if ($offset > -1) {
            $sql->setFirstResult($offset);
        }
        if ($rowcnt > 0) {
            $sql->setMaxResults($rowcnt);
        }
        $result = $sql->executeQuery();
        return $result->fetchAllAssociative();
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
        $this->ViewUrl = $this->getViewUrl();
        $this->EditUrl = $this->getEditUrl();
        $this->InlineEditUrl = $this->getInlineEditUrl();
        $this->CopyUrl = $this->getCopyUrl();
        $this->InlineCopyUrl = $this->getInlineCopyUrl();
        $this->DeleteUrl = $this->getDeleteUrl();

        // Call Row_Rendering event
        $this->rowRendering();

        // Common render codes for all row types

        // UserID

        // Username

        // Password

        // UserLevel

        // FirstName

        // LastName

        // CompleteName

        // BirthDate

        // HomePhone

        // Photo

        // Notes

        // ReportsTo

        // Gender

        // Email

        // Activated

        // Profile

        // Avatar

        // ActiveStatus

        // MessengerColor

        // CreatedAt

        // CreatedBy

        // UpdatedAt

        // UpdatedBy

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

            // UserID
            $this->_UserID->HrefValue = "";
            $this->_UserID->TooltipValue = "";

            // Username
            $this->_Username->HrefValue = "";
            $this->_Username->TooltipValue = "";

            // UserLevel
            $this->UserLevel->HrefValue = "";
            $this->UserLevel->TooltipValue = "";

            // CompleteName
            $this->CompleteName->HrefValue = "";
            $this->CompleteName->TooltipValue = "";

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
            $this->Photo->TooltipValue = "";
            if ($this->Photo->UseColorbox) {
                if (IsEmpty($this->Photo->TooltipValue)) {
                    $this->Photo->LinkAttrs["title"] = $this->language->phrase("ViewImageGallery");
                }
                $this->Photo->LinkAttrs["data-rel"] = "users_x" . $this->RowCount . "_Photo";
                $this->Photo->LinkAttrs->appendClass("ew-lightbox");
            }

            // Gender
            $this->Gender->HrefValue = "";
            $this->Gender->TooltipValue = "";

            // Email
            $this->_Email->HrefValue = "";
            $this->_Email->TooltipValue = "";

            // Activated
            $this->Activated->HrefValue = "";
            $this->Activated->TooltipValue = "";

            // ActiveStatus
            $this->ActiveStatus->HrefValue = "";
            $this->ActiveStatus->TooltipValue = "";
        } elseif ($this->RowType == RowType::SEARCH) {
            // UserID
            $this->_UserID->setupEditAttributes();
            $this->_UserID->EditValue = $this->_UserID->AdvancedSearch->SearchValue;
            $this->_UserID->PlaceHolder = RemoveHtml($this->_UserID->caption());

            // Username
            $this->_Username->setupEditAttributes();
            $this->_Username->EditValue = !$this->_Username->Raw ? HtmlDecode($this->_Username->AdvancedSearch->SearchValue) : $this->_Username->AdvancedSearch->SearchValue;
            $this->_Username->PlaceHolder = RemoveHtml($this->_Username->caption());

            // UserLevel
            $this->UserLevel->setupEditAttributes();
            if (!$this->security->canAdmin()) { // System admin
                $this->UserLevel->EditValue = $this->language->phrase("PasswordMask");
            } else {
                $curVal = trim(strval($this->UserLevel->AdvancedSearch->SearchValue));
                if ($curVal != "") {
                    $this->UserLevel->AdvancedSearch->ViewValue = $this->UserLevel->lookupCacheOption($curVal);
                } else {
                    $this->UserLevel->AdvancedSearch->ViewValue = $this->UserLevel->Lookup !== null && is_array($this->UserLevel->lookupOptions()) && count($this->UserLevel->lookupOptions()) > 0 ? $curVal : null;
                }
                if ($this->UserLevel->AdvancedSearch->ViewValue !== null) { // Load from cache
                    $this->UserLevel->EditValue = array_values($this->UserLevel->lookupOptions());
                } else { // Lookup from database
                    if ($curVal == "") {
                        $filterWrk = "0=1";
                    } else {
                        $filterWrk = SearchFilter($this->UserLevel->Lookup->getTable()->Fields["ID"]->searchExpression(), "=", $this->UserLevel->AdvancedSearch->SearchValue, $this->UserLevel->Lookup->getTable()->Fields["ID"]->searchDataType(), "DB");
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
                        $this->UserLevel->AdvancedSearch->ViewValue = $this->language->phrase("PleaseSelect");
                    }
                    $this->UserLevel->EditValue = $rows;
                }
                $this->UserLevel->PlaceHolder = RemoveHtml($this->UserLevel->caption());
            }

            // CompleteName
            $this->CompleteName->setupEditAttributes();
            $this->CompleteName->EditValue = !$this->CompleteName->Raw ? HtmlDecode($this->CompleteName->AdvancedSearch->SearchValue) : $this->CompleteName->AdvancedSearch->SearchValue;
            $this->CompleteName->PlaceHolder = RemoveHtml($this->CompleteName->caption());

            // Photo
            $this->Photo->setupEditAttributes();
            $this->Photo->EditValue = !$this->Photo->Raw ? HtmlDecode($this->Photo->AdvancedSearch->SearchValue) : $this->Photo->AdvancedSearch->SearchValue;
            $this->Photo->PlaceHolder = RemoveHtml($this->Photo->caption());

            // Gender
            $this->Gender->setupEditAttributes();
            $this->Gender->EditValue = $this->Gender->options(true);
            $this->Gender->PlaceHolder = RemoveHtml($this->Gender->caption());

            // Email
            $this->_Email->setupEditAttributes();
            $this->_Email->EditValue = !$this->_Email->Raw ? HtmlDecode($this->_Email->AdvancedSearch->SearchValue) : $this->_Email->AdvancedSearch->SearchValue;
            $this->_Email->PlaceHolder = RemoveHtml($this->_Email->caption());

            // Activated
            $this->Activated->EditValue = $this->Activated->options(false);
            $this->Activated->PlaceHolder = RemoveHtml($this->Activated->caption());

            // ActiveStatus
            $this->ActiveStatus->EditValue = $this->ActiveStatus->options(false);
            $this->ActiveStatus->PlaceHolder = RemoveHtml($this->ActiveStatus->caption());
        }
        if ($this->RowType == RowType::ADD || $this->RowType == RowType::EDIT || $this->RowType == RowType::SEARCH) { // Add/Edit/Search row
            $this->setupFieldTitles();
        }

        // Call Row Rendered event
        if ($this->RowType != RowType::AGGREGATEINIT) {
            $this->rowRendered();
        }
    }

    // Validate search
    protected function validateSearch(): bool
    {
        // Check if validation required
        if (!Config("SERVER_VALIDATE")) {
            return true;
        }

        // Return validate result
        $validateSearch = !$this->hasInvalidFields();

        // Call Form_CustomValidate event
        $formCustomError = "";
        $validateSearch = $validateSearch && $this->formCustomValidate($formCustomError);
        if ($formCustomError != "") {
            $this->setFailureMessage($formCustomError);
        }
        return $validateSearch;
    }

    // Load advanced search
    public function loadAdvancedSearch(): void
    {
        $this->_UserID->AdvancedSearch->load();
        $this->_Username->AdvancedSearch->load();
        $this->UserLevel->AdvancedSearch->load();
        $this->FirstName->AdvancedSearch->load();
        $this->LastName->AdvancedSearch->load();
        $this->CompleteName->AdvancedSearch->load();
        $this->BirthDate->AdvancedSearch->load();
        $this->HomePhone->AdvancedSearch->load();
        $this->Photo->AdvancedSearch->load();
        $this->Notes->AdvancedSearch->load();
        $this->ReportsTo->AdvancedSearch->load();
        $this->Gender->AdvancedSearch->load();
        $this->_Email->AdvancedSearch->load();
        $this->Activated->AdvancedSearch->load();
        $this->Avatar->AdvancedSearch->load();
        $this->ActiveStatus->AdvancedSearch->load();
        $this->MessengerColor->AdvancedSearch->load();
        $this->CreatedAt->AdvancedSearch->load();
        $this->CreatedBy->AdvancedSearch->load();
        $this->UpdatedAt->AdvancedSearch->load();
        $this->UpdatedBy->AdvancedSearch->load();
    }

    // Set up search options
    protected function setupSearchOptions(): void
    {	
		$pageUrl = $this->pageUrl(false);
        $this->SearchOptions = new ListOptions(TagClassName: "ew-search-option");

	// Begin of add Search Panel Status by Masino Sinaga, October 13, 2024

	    // Search button
        $item = &$this->SearchOptions->add("searchtoggle");
		if (ReadCookie('users_searchpanel') == 'notactive' || ReadCookie('users_searchpanel') == "") {
			$item->Body = "<a class=\"btn btn-default ew-search-toggle\" role=\"button\" title=\"" . $this->language->phrase("SearchPanel") . "\" data-caption=\"" . $this->language->phrase("SearchPanel") . "\" data-ew-action=\"search-toggle\" data-form=\"fuserssrch\" aria-pressed=\"false\">" . $this->language->phrase("SearchLink") . "</a>";
		} elseif (ReadCookie('users_searchpanel') == 'active') {
			$item->Body = "<a class=\"btn btn-default ew-search-toggle active\" role=\"button\" title=\"" . $this->language->phrase("SearchPanel") . "\" data-caption=\"" . $this->language->phrase("SearchPanel") . "\" data-ew-action=\"search-toggle\" data-form=\"fuserssrch\" aria-pressed=\"true\">" . $this->language->phrase("SearchLink") . "</a>";
		} else {
			$item->Body = "<a class=\"btn btn-default ew-search-toggle\" role=\"button\" title=\"" . $this->language->phrase("SearchPanel") . "\" data-caption=\"" . $this->language->phrase("SearchPanel") . "\" data-ew-action=\"search-toggle\" data-form=\"fuserssrch\" aria-pressed=\"false\">" . $this->language->phrase("SearchLink") . "</a>";
		}
        $item->Visible = true;

	// End of add Search Panel Status by Masino Sinaga, October 13, 2024

        // Show all button
        $item = &$this->SearchOptions->add("showall");
        if ($this->UseCustomTemplate || !$this->UseAjaxActions) {
            $item->Body = "<a class=\"btn btn-default ew-show-all\" role=\"button\" title=\"" . $this->language->phrase("ShowAll") . "\" data-caption=\"" . $this->language->phrase("ShowAll") . "\" href=\"" . $pageUrl . "cmd=reset\">" . $this->language->phrase("ShowAllBtn") . "</a>";
        } else {
            $item->Body = "<a class=\"btn btn-default ew-show-all\" role=\"button\" title=\"" . $this->language->phrase("ShowAll") . "\" data-caption=\"" . $this->language->phrase("ShowAll") . "\" data-ew-action=\"refresh\" data-url=\"" . $pageUrl . "cmd=reset\">" . $this->language->phrase("ShowAllBtn") . "</a>";
        }
        $item->Visible = ($this->SearchWhere != $this->DefaultSearchWhere && $this->SearchWhere != "0=101");

        // Advanced search button
        $item = &$this->SearchOptions->add("advancedsearch");
        if ($this->ModalSearch && !IsMobile()) {
            $item->Body = "<a class=\"btn btn-default ew-advanced-search\" title=\"" . $this->language->phrase("AdvancedSearch", true) . "\" data-table=\"users\" data-caption=\"" . $this->language->phrase("AdvancedSearch", true) . "\" data-ew-action=\"modal\" data-url=\"userssearch\" data-btn=\"SearchBtn\">" . $this->language->phrase("AdvancedSearch", false) . "</a>";
        } else {
            $item->Body = "<a class=\"btn btn-default ew-advanced-search\" title=\"" . $this->language->phrase("AdvancedSearch", true) . "\" data-caption=\"" . $this->language->phrase("AdvancedSearch", true) . "\" href=\"userssearch\">" . $this->language->phrase("AdvancedSearch", false) . "</a>";
        }
        $item->Visible = true;

        // Button group for search
        $this->SearchOptions->UseDropDownButton = false;
        $this->SearchOptions->UseButtonGroup = true;
        $this->SearchOptions->DropDownButtonPhrase = $this->language->phrase("ButtonSearch");

        // Add group option item
        $item = &$this->SearchOptions->addGroupOption();
        $item->Body = "";
        $item->Visible = false;

        // Hide search options
        if ($this->isExport() || $this->CurrentAction && $this->CurrentAction != "search") {
            $this->SearchOptions->hideAllOptions();
        }
        if (!$this->security->canSearch()) {
            $this->SearchOptions->hideAllOptions();
            $this->FilterOptions->hideAllOptions();
        }
    }

    // Check if any search fields
    public function hasSearchFields(): bool
    {
        return true;
    }

    // Render search options
    protected function renderSearchOptions(): void
    {
        if (!$this->hasSearchFields() && $this->SearchOptions["searchtoggle"]) {
            $this->SearchOptions["searchtoggle"]->Visible = false;
        }
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

            // Update URL
            $this->AddUrl = $this->addMasterUrl($this->AddUrl);
            $this->InlineAddUrl = $this->addMasterUrl($this->InlineAddUrl);
            $this->GridAddUrl = $this->addMasterUrl($this->GridAddUrl);
            $this->GridEditUrl = $this->addMasterUrl($this->GridEditUrl);
            $this->MultiEditUrl = $this->addMasterUrl($this->MultiEditUrl);

            // Set up Breadcrumb
            if (!$this->isExport()) {
                $this->setupBreadcrumb(); // Set up breadcrumb again for the master table
            }

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
        $url = preg_replace('/\?cmd=reset(all){0,1}$/i', '', $url); // Remove cmd=reset(all)
        $breadcrumb->add("list", $this->TableVar, $url, "", $this->TableVar, true);
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

    // Set up starting record parameters
    public function setupStartRecord(): void
    {
        $pagerTable = Get(Config("TABLE_PAGER_TABLE_NAME"));
        if ($this->DisplayRecords == 0 || $pagerTable && $pagerTable != $this->TableVar) { // Display all records / Check if paging for this table
            return;
        }
        $pageNo = Get(Config("TABLE_PAGE_NUMBER"));
        $startRec = Get(Config("TABLE_START_REC"));
        $infiniteScroll = ConvertToBool(Param("infinitescroll"));
        if ($pageNo !== null) { // Check for "pageno" parameter first
            $pageNo = ParseInteger($pageNo);
            if (is_numeric($pageNo)) {
                $this->StartRecord = ($pageNo - 1) * $this->DisplayRecords + 1;
                if ($this->StartRecord <= 0) {
                    $this->StartRecord = 1;
                } elseif ($this->StartRecord >= (int)(($this->TotalRecords - 1) / $this->DisplayRecords) * $this->DisplayRecords + 1) {
                    $this->StartRecord = (int)(($this->TotalRecords - 1) / $this->DisplayRecords) * $this->DisplayRecords + 1;
                }
            }
        } elseif ($startRec !== null && is_numeric($startRec)) { // Check for "start" parameter
            $this->StartRecord = $startRec;
        } elseif (!$infiniteScroll) {
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

    // Parse query builder rule
    protected function parseRules(array $group, string $fieldName = "", string $itemName = ""): string
    {
        $group["condition"] ??= "AND";
        if (!in_array($group["condition"], ["AND", "OR"])) {
            throw new Exception("Unable to build SQL query with condition '" . $group["condition"] . "'");
        }
        if (!is_array($group["rules"] ?? null)) {
            return "";
        }
        $parts = [];
        foreach ($group["rules"] as $rule) {
            if (is_array($rule["rules"] ?? null) && count($rule["rules"]) > 0) {
                $part = $this->parseRules($rule, $fieldName, $itemName);
                if ($part) {
                    $parts[] = "(" . " " . $part . " " . ")" . " ";
                }
            } else {
                $field = $rule["field"];
                $fld = $this->fieldByParam($field);
                $dbid = $this->Dbid;
                if ($fld instanceof ReportField && is_array($fld->DashboardSearchSourceFields)) {
                    $item = $fld->DashboardSearchSourceFields[$itemName] ?? null;
                    if ($item) {
                        $tbl = Container($item["table"]);
                        $dbid = $tbl->Dbid;
                        $fld = $tbl->Fields[$item["field"]];
                    } else {
                        $fld = null;
                    }
                }
                if ($fld && ($fieldName == "" || $fld->Name == $fieldName)) { // Field name not specified or matched field name
                    $fldOpr = array_search($rule["operator"], Config("CLIENT_SEARCH_OPERATORS"));
                    $ope = Config("QUERY_BUILDER_OPERATORS")[$rule["operator"]] ?? null;
                    if (!$ope || !$fldOpr) {
                        throw new Exception("Unknown SQL operation for operator '" . $rule["operator"] . "'");
                    }
                    if ($ope["nb_inputs"] > 0 && isset($rule["value"]) && !EmptyValue($rule["value"]) || IsNullOrEmptyOperator($fldOpr)) {
                        $fldVal = $rule["value"] ?? "";
                        if (is_array($fldVal)) {
                            $fldVal = $fld->isMultiSelect() ? implode(Config("MULTIPLE_OPTION_SEPARATOR"), $fldVal) : $fldVal[0];
                        }
                        $useFilter = $fld->UseFilter; // Query builder does not use filter
                        try {
                            if ($fld instanceof ReportField) { // Search report fields
                                if ($fld->SearchType == "dropdown") {
                                    if (is_array($fldVal)) {
                                        $sql = "";
                                        foreach ($fldVal as $val) {
                                            AddFilter($sql, DropDownFilter($fld, $val, $fldOpr, $dbid), "OR");
                                        }
                                        $parts[] = $sql;
                                    } else {
                                        $parts[] = DropDownFilter($fld, $fldVal, $fldOpr, $dbid);
                                    }
                                } else {
                                    $fld->AdvancedSearch->SearchOperator = $fldOpr;
                                    $fld->AdvancedSearch->SearchValue = $fldVal;
                                    $parts[] = GetReportFilter($fld, false, $dbid);
                                }
                            } else { // Search normal fields
                                if ($fld->isMultiSelect()) {
                                    $fld->AdvancedSearch->SearchValue = ConvertSearchValue($fldVal, $fldOpr, $fld);
                                    $parts[] = $fldVal != "" ? GetMultiSearchSql($fld, $fldOpr, $fld->AdvancedSearch->SearchValue, $this->Dbid) : "";
                                } else {
                                    $fldVal2 = ContainsString($fldOpr, "BETWEEN") ? $rule["value"][1] : ""; // BETWEEN
                                    if (is_array($fldVal2)) {
                                        $fldVal2 = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $fldVal2);
                                    }
                                    $fld->AdvancedSearch->SearchValue = ConvertSearchValue($fldVal, $fldOpr, $fld);
                                    $fld->AdvancedSearch->SearchValue2 = ConvertSearchValue($fldVal2, $fldOpr, $fld);
                                    $parts[] = GetSearchSql(
                                        $fld,
                                        $fld->AdvancedSearch->SearchValue, // SearchValue
                                        $fldOpr,
                                        "", // $fldCond not used
                                        $fld->AdvancedSearch->SearchValue2, // SearchValue2
                                        "", // $fldOpr2 not used
                                        $this->Dbid
                                    );
                                }
                            }
                        } finally {
                            $fld->UseFilter = $useFilter;
                        }
                    }
                }
            }
        }
        $where = "";
        foreach ($parts as $part) {
            AddFilter($where, $part, $group["condition"]);
        }
        if ($where && ($group["not"] ?? false)) {
            $where = "NOT (" . $where . ")";
        }
        return $where;
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

    // ListOptions Load event
    public function listOptionsLoad(): void
    {
        // Example:
        //$opt = &$this->ListOptions->add("new");
        //$opt->Header = "xxx";
        //$opt->OnLeft = true; // Link on left
        //$opt->moveTo(0); // Move to first column
    }

    // ListOptions Rendering event
    public function listOptionsRendering(): void
    {
        //Container("DetailTableGrid")->DetailAdd = (...condition...); // Set to true or false conditionally
        //Container("DetailTableGrid")->DetailEdit = (...condition...); // Set to true or false conditionally
        //Container("DetailTableGrid")->DetailView = (...condition...); // Set to true or false conditionally
    }

    // ListOptions Rendered event
    public function listOptionsRendered(): void
    {
        // Example:
        //$this->ListOptions["new"]->Body = "xxx";
    }

    // Row Custom Action event
    public function rowCustomAction(string $action, array $row): bool
    {
        // Return false to abort
        return true;
    }

    // Page Exporting event
    // $doc = export object
    public function pageExporting(object &$doc): bool
    {
        //$doc->Text = "my header"; // Export header
        //return false; // Return false to skip default export and use Row_Export event
        return true; // Return true to use default export and skip Row_Export event
    }

    // Row Export event
    // $doc = export document object
    public function rowExport(object $doc, array $row): void
    {
        //$doc->Text .= "my content"; // Build HTML with field value: $row["MyField"] or $this->MyField->ViewValue
    }

    // Page Exported event
    // $doc = export document object
    public function pageExported(object $doc): void
    {
        //$doc->Text .= "my footer"; // Export footer
        //Log($doc->Text);
    }

    // Page Importing event
    public function pageImporting(object &$builder, array &$options): bool
    {
        //var_dump($options); // Show all options for importing
        //$builder = fn($workflow) => $workflow->addStep($myStep);
        //return false; // Return false to skip import
        return true;
    }

    // Row Import event
    public function rowImport(array &$row, int $count): bool
    {
        //Log($count); // Import record count
        //var_dump($row); // Import row
        //return false; // Return false to skip import
        return true;
    }

    // Page Imported event
    public function pageImported(object $object, array $results): void
    {
        //var_dump($object); // Workflow result object
        //var_dump($results); // Import results
    }
}
