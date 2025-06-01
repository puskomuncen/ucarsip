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
class UsersGrid extends Users
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "grid";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "UsersGrid";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // Grid form hidden field names
    public string $FormName = "fusersgrid";

    // CSS class/style
    public string $CurrentPageName = "usersgrid";

    // Page URLs
    public string $AddUrl = "";
    public string $EditUrl = "";
    public string $DeleteUrl = "";
    public string $ViewUrl = "";
    public string $CopyUrl = "";
    public string $ListUrl = "";

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
        $GLOBALS["Grid"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (users)
        if (!isset($GLOBALS["users"]) || $GLOBALS["users"]::class == PROJECT_NAMESPACE . "users") {
            $GLOBALS["users"] = &$this;
        }
        $this->AddUrl = "usersadd";

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'users');
        }

        // Open connection
        $GLOBALS["Conn"] ??= $this->getConnection();

        // List options
        $this->ListOptions = new ListOptions(Tag: "td", TableVar: $this->TableVar);

        // Other options
        $this->OtherOptions = new ListOptionsCollection();

        // Grid-Add/Edit
        $this->OtherOptions["addedit"] = new ListOptions(
            TagClassName: "ew-add-edit-option",
            UseDropDownButton: false,
            DropDownButtonPhrase: $this->language->phrase("ButtonAddEdit"),
            UseButtonGroup: true
        );
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
        unset($GLOBALS["Grid"]);
        if ($url === "") {
            return;
        }
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
            Redirect(GetUrl($url));
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
    public bool $ShowOtherOptions = false;
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

// Use layout
        $this->UseLayout = $this->UseLayout && ConvertToBool(Param(Config("PAGE_LAYOUT"), true));

        // View
        $this->View = Get(Config("VIEW"));
        if (Param("export") !== null) {
            $this->Export = Param("export");
        }

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

        // Load default values for add
        $this->loadDefaultValues();

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

        // Set up records per page
        $this->setupDisplayRecords();

        // Handle reset command
        $this->resetCmd();

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

        // Hide other options
        if ($this->isExport()) {
            $this->OtherOptions->hideAllOptions();
        }

        // Show grid delete link for grid add / grid edit
        if ($this->AllowAddDeleteRow) {
            if ($this->isGridAdd() || $this->isGridEdit()) {
                $item = $this->ListOptions["griddelete"];
                if ($item) {
                    $item->Visible = $this->security->allowDelete(CurrentProjectID() . $this->TableName);
                }
            }
        }

        // Set up sorting order
        $this->setupSortOrder();

        // Restore display records
        if ($this->Command != "json" && $this->getRecordsPerPage() != 0) {
            $this->DisplayRecords = $this->getRecordsPerPage(); // Restore from Session
        } else {
            $this->DisplayRecords = 20; // Load default
            $this->setRecordsPerPage($this->DisplayRecords); // Save default to Session
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
            if ($this->CurrentMode == "copy") {
                $this->TotalRecords = $this->listRecordCount();
                $this->StartRecord = 1;
                $this->DisplayRecords = $this->TotalRecords;
                $this->Result = $this->loadResult($this->StartRecord - 1, $this->DisplayRecords);
            } else {
                $this->CurrentFilter = "0=1";
                $this->StartRecord = 1;
                $this->DisplayRecords = $this->GridAddRowCount;
            }
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
            if ($this->CurrentMode == "view" && $this->DetailViewPaging && !$this->isExport()) { // Set up start record position for view mode
                $this->setupStartRecord();
            } else { // Display all records if not view mode
                $this->DisplayRecords = $this->TotalRecords;
            }
            $this->Result = $this->loadResult($this->StartRecord - 1, $this->DisplayRecords);
        }

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

        // Set up pager (always use PrevNextPager for grid page)
        $url = CurrentPageUrl() . "?" . Config("TABLE_PAGER_TABLE_NAME") . "=" . $this->TableVar; // Add detail table parameter
        $this->Pager = new PrevNextPager($this, $this->StartRecord, $this->DisplayRecords, $this->TotalRecords, "", $this->RecordRange, $this->AutoHidePager, null, null, false, true, $url);

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

    // Exit inline mode
    protected function clearInlineMode(): void
    {
        $this->LastAction = $this->CurrentAction; // Save last action
        $this->CurrentAction = ""; // Clear action
        Session()->remove(AddTabId(SESSION_INLINE_MODE)); // Clear inline mode
    }

    // Switch to grid add mode
    protected function gridAddMode(): void
    {
        $this->CurrentAction = "gridadd";
        Session(AddTabId(SESSION_INLINE_MODE), "gridadd");
        $this->hideFieldsForAddEdit();
    }

    // Switch to grid edit mode
    protected function gridEditMode(): void
    {
        $this->CurrentAction = "gridedit";
        Session(AddTabId(SESSION_INLINE_MODE), "gridedit");
        $this->hideFieldsForAddEdit();
    }

    // Perform update to grid
    public function gridUpdate(): bool
    {
        $gridUpdate = true;

        // Get old result set
        $this->CurrentFilter = $this->buildKeyFilter();
        if ($this->CurrentFilter == "") {
            $this->CurrentFilter = "0=1";
        }
        $sql = $this->getCurrentSql();
        $conn = $this->getConnection();
        if ($result = $conn->executeQuery($sql)) {
            $oldRows = $result->fetchAllAssociative();
        }

        // Call Grid Updating event
        if (!$this->gridUpdating($oldRows)) {
            if (!$this->peekFailureMessage()) {
                $this->setFailureMessage($this->language->phrase("GridEditCancelled")); // Set grid edit cancelled message
            }
            $this->EventCancelled = true;
            return false;
        }
        $this->loadDefaultValues();
        $wrkfilter = "";
        $successKeys = [];
        $skipRecords = [];

        // Get row count
        $rowcnt = $this->getKeyCount();

        // Update all rows based on key
        for ($rowindex = 1; $rowindex <= $rowcnt; $rowindex++) {
            $this->FormIndex = $rowindex;
            $this->setKey($this->getOldKey());
            $rowaction = $this->getRowAction();

            // Load all values and keys
            if ($rowaction != "insertdelete" && $rowaction != "hide") { // Skip insert then deleted rows / hidden rows for grid edit
                $this->loadFormValues(); // Get form values
                if ($rowaction == "" || $rowaction == "edit" || $rowaction == "delete") {
                    $gridUpdate = $this->OldKey != ""; // Key must not be empty
                } else {
                    $gridUpdate = true;
                }

                // Skip empty row
                if ($rowaction == "insert" && $this->emptyRow()) {
                // Validate form and insert/update/delete record
                } elseif ($gridUpdate) {
                    if ($rowaction == "delete") {
                        $this->CurrentFilter = $this->getRecordFilter();
                        $gridUpdate = $this->deleteRows(); // Delete this row
                        if ($gridUpdate === null) { // Record skipped, get filter for this record as well
                            AddFilter($wrkfilter, $this->getRecordFilter(), "OR");
                        }
                    } else {
                        if ($rowaction == "insert") {
                            $gridUpdate = $this->addRow(); // Insert this row
                        } else {
                            if ($this->OldKey != "") {
                                $this->SendEmail = false; // Do not send email on update success
                                $gridUpdate = $this->editRow(); // Update this row
                            }
                        } // End update
                        if ($gridUpdate) { // Get inserted or updated filter
                            AddFilter($wrkfilter, $this->getRecordFilter(), "OR");
                        }
                    }
                }
                if ($gridUpdate === null) { // Record skipped
                    $key = $this->getKey();
                    $skipRecords[] = $rowindex . (!IsEmpty($key) ? ": " . $key : ""); // Record count and key if exists
                    $gridUpdate = true; // Skip this record and continue to next record
                } elseif ($gridUpdate === false) {
                    $this->EventCancelled = true;
                    break;
                } elseif ($gridUpdate) {
                    $successKeys[] = $this->getKey();
                }
            }
        }
        if ($gridUpdate) {
            $this->FilterForModalActions = $wrkfilter;

            // Get new records
            $newRows = $conn->fetchAllAssociative($sql);

            // Call Grid_Updated event
            $this->gridUpdated($oldRows, $newRows);

            // Set warning message if some records skipped
            if (count($skipRecords) > 0) {
                $this->setWarningMessage(sprintf($this->language->phrase("RecordsSkipped"), count($skipRecords)));
                Log("Records skipped", $skipRecords);
            }
            $this->clearInlineMode(); // Clear inline edit mode
        } else {
            if (!$this->peekFailureMessage()) {
                $this->setFailureMessage($this->language->phrase("UpdateFailed")); // Set update failed message
            }
        }
        return $gridUpdate;
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

    // Perform grid add
    public function gridInsert(): bool
    {
        $rowindex = 1;
        $conn = $this->getConnection();

        // Call Grid Inserting event
        if (!$this->gridInserting()) {
            if (!$this->peekFailureMessage()) {
                $this->setFailureMessage($this->language->phrase("GridAddCancelled")); // Set grid add cancelled message
            }
            $this->EventCancelled = true;
            return false;
        }
		$gridInsert = true;
        $this->loadDefaultValues();

        // Init key filter
        $wrkfilter = "";
        $addcnt = 0;
        $successKeys = [];
        $skipRecords = [];

        // Get row count
        $rowcnt = $this->getKeyCount();

        // Insert all rows
        for ($rowindex = 1; $rowindex <= $rowcnt; $rowindex++) {
            // Load current row values
            $this->FormIndex = $rowindex;
            $rowaction = $this->getRowAction();
            if ($rowaction != "" && $rowaction != "insert") {
                continue; // Skip
            }
            $oldRow = null;
            if ($rowaction == "insert") {
                $this->OldKey = $this->getOldKey();
                $oldRow = $this->loadOldRecord(); // Load old record
            }
            $this->loadFormValues(); // Get form values
            if (!$this->emptyRow()) {
                $this->SendEmail = false; // Do not send email on insert success
                $gridInsert = $this->addRow($oldRow); // Insert row (already validated by validateGridForm())
                if ($gridInsert === null) { // Record skipped
                    $key = $this->getKey(true);
                    $skipRecords[] = $rowindex . (!IsEmpty($key) ? ": " . $key : ""); // Record count and key if exists
                    $gridInsert = true; // Skip this record and continue to next record
                } elseif ($gridInsert === true) { // Record inserted
                    $addcnt++;
                    $successKeys[] = $this->getKey(true);

                    // Add filter for this record
                    AddFilter($wrkfilter, $this->getRecordFilter(), "OR");
                } elseif ($gridInsert === false) { // Record not inserted
                    $this->EventCancelled = true;
                    break;
                }
            }
        }
        if ($addcnt == 0) { // No record inserted
            return $gridInsert;
		}
        if ($gridInsert) {
            // Get new records
            $this->CurrentFilter = $wrkfilter;
            $this->FilterForModalActions = $wrkfilter;
            $sql = $this->getCurrentSql();
            $newRows = $conn->fetchAllAssociative($sql);

            // Call Grid_Inserted event
            $this->gridInserted($newRows);

            // Set warning message if some records skipped
            if (count($skipRecords) > 0) {
                $this->setWarningMessage(sprintf($this->language->phrase("RecordsSkipped"), count($skipRecords)));
                Log("Records skipped", $skipRecords);
            }
            $this->clearInlineMode(); // Clear grid add mode
        } else {
            if (!$this->peekFailureMessage()) {
                $this->setFailureMessage($this->language->phrase("InsertFailed")); // Set insert failed message
            }
        }
        return $gridInsert;
    }

    // Check if empty row
    public function emptyRow(): bool
    {
        if (
            $this->hasFormValue("x__Username")
            && $this->hasFormValue("o__Username")
            && $this->_Username->CurrentValue != $this->_Username->DefaultValue
            && !($this->_Username->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->_Username->CurrentValue == $this->_Username->getSessionValue())
        ) {
            return false;
        }
        if (
            $this->hasFormValue("x_UserLevel")
            && $this->hasFormValue("o_UserLevel")
            && $this->UserLevel->CurrentValue != $this->UserLevel->DefaultValue
            && !($this->UserLevel->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->UserLevel->CurrentValue == $this->UserLevel->getSessionValue())
        ) {
            return false;
        }
        if (
            $this->hasFormValue("x_CompleteName")
            && $this->hasFormValue("o_CompleteName")
            && $this->CompleteName->CurrentValue != $this->CompleteName->DefaultValue
            && !($this->CompleteName->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->CompleteName->CurrentValue == $this->CompleteName->getSessionValue())
        ) {
            return false;
        }
        if (!IsEmpty($this->Photo->Upload->Value)) {
            return false;
        }
        if (
            $this->hasFormValue("x_Gender")
            && $this->hasFormValue("o_Gender")
            && $this->Gender->CurrentValue != $this->Gender->DefaultValue
            && !($this->Gender->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->Gender->CurrentValue == $this->Gender->getSessionValue())
        ) {
            return false;
        }
        if (
            $this->hasFormValue("x__Email")
            && $this->hasFormValue("o__Email")
            && $this->_Email->CurrentValue != $this->_Email->DefaultValue
            && !($this->_Email->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->_Email->CurrentValue == $this->_Email->getSessionValue())
        ) {
            return false;
        }
        if ($this->hasFormValue("x_Activated") && $this->hasFormValue("o_Activated") && ConvertToBool($this->Activated->CurrentValue) != ConvertToBool($this->Activated->DefaultValue)) {
            return false;
        }
        if ($this->hasFormValue("x_ActiveStatus") && $this->hasFormValue("o_ActiveStatus") && ConvertToBool($this->ActiveStatus->CurrentValue) != ConvertToBool($this->ActiveStatus->DefaultValue)) {
            return false;
        }
        return true;
    }

    // Validate grid form
    public function validateGridForm(): bool
    {
        // Get row count
        $rowcnt = $this->getKeyCount();

        // Load default values for emptyRow checking
        $this->loadDefaultValues();

        // Validate all records
        for ($rowindex = 1; $rowindex <= $rowcnt; $rowindex++) {
            // Load current row values
            $this->FormIndex = $rowindex;
            $rowaction = $this->getRowAction();
            if ($rowaction != "delete" && $rowaction != "insertdelete" && $rowaction != "hide") {
                $this->loadFormValues(); // Get form values
                if ($rowaction == "insert" && $this->emptyRow()) {
                    // Ignore
                } elseif (!$this->validateForm()) {
                    $this->ValidationErrors[$rowindex] = $this->getValidationErrors();
                    $this->EventCancelled = true;
                    return false;
                }
            }
        }
        return true;
    }

    // Get all form values of the grid
    public function getGridFormValues(): array
    {
        // Get row count
        $rowcnt = $this->getKeyCount();
        $rows = [];

        // Loop through all records
        for ($rowindex = 1; $rowindex <= $rowcnt; $rowindex++) {
            // Load current row values
            $this->FormIndex = $rowindex;
            $rowaction = $this->getRowAction();
            if ($rowaction != "delete" && $rowaction != "insertdelete") {
                $this->loadFormValues(); // Get form values
                if ($rowaction == "insert" && $this->emptyRow()) {
                    // Ignore
                } else {
                    $rows[] = $this->Fields->getFormValues(); // Return row as array
                }
            }
        }
        return $rows; // Return as array of array
    }

    // Restore form values for current row
    public function restoreCurrentRowFormValues(int $rowindex): void
    {
        // Get row based on current index
        $this->FormIndex = $rowindex;
        $rowaction = $this->getRowAction();
        $this->loadFormValues(); // Load form values
        // Set up invalid status correctly
        $this->resetFormError();
        if ($rowaction == "insert" && $this->emptyRow()) {
            // Ignore
        } else {
            $this->validateForm();
        }
    }

    // Reset form status
    public function resetFormError(): void
    {
        foreach ($this->Fields as $field) {
            $field->clearErrorMessage();
        }
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
            }

            // Reset start position
            $this->StartRecord = 1;
            $this->setStartRecordNumber($this->StartRecord);
        }
    }

    // Set up list options
    protected function setupListOptions(): void
    {
        // "griddelete"
        if ($this->AllowAddDeleteRow) {
            $item = &$this->ListOptions->add("griddelete");
            $item->CssClass = "text-nowrap";
            $item->OnLeft = true;
            $item->Visible = false; // Default hidden
        }

        // Add group option item ("button")
        $item = &$this->ListOptions->addGroupOption();
        $item->Body = "";
        $item->OnLeft = true;
        $item->Visible = false;

        // "view"
        $item = &$this->ListOptions->add("view");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->allowView(CurrentProjectID() . 'users');
        $item->OnLeft = true;

        // "edit"
        $item = &$this->ListOptions->add("edit");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->allowEdit(CurrentProjectID() . 'users');
        $item->OnLeft = true;

        // "copy"
        $item = &$this->ListOptions->add("copy");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->allowAdd(CurrentProjectID() . 'users');
        $item->OnLeft = true;

        // "delete"
        $item = &$this->ListOptions->add("delete");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->allowDelete(CurrentProjectID() . 'users');
        $item->OnLeft = true;

        // "sequence"
        $item = &$this->ListOptions->add("sequence");
        $item->CssClass = "text-nowrap";
        $item->Visible = true;
        $item->OnLeft = true; // Always on left
        $item->ShowInDropDown = false;
        $item->ShowInButtonGroup = false;

        // Drop down button for ListOptions
        $this->ListOptions->UseDropDownButton = true;
        $this->ListOptions->DropDownButtonPhrase = $this->language->phrase("ButtonListOptions");
        $this->ListOptions->UseButtonGroup = true;
        if ($this->ListOptions->UseButtonGroup && IsMobile()) {
            $this->ListOptions->UseDropDownButton = true;
        }

        // $this->ListOptions->ButtonClass = ""; // Class for button group

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

        // Set up row action and key
        if (is_numeric($this->RowIndex) && $this->RowType != "view") {
            $this->FormIndex = $this->RowIndex;
            $actionName = $this->getFormRowActionName(true);
            $oldKeyName = $this->getFormOldKeyName(true);
            $blankRowName = $this->getFormBlankRowName(true);
            if ($this->RowAction != "") {
                $this->MultiSelectKey .= "<input type=\"hidden\" name=\"" . $actionName . "\" id=\"" . $actionName . "\" value=\"" . $this->RowAction . "\">";
            }
            $oldKey = $this->getKey(false); // Get from OldValue
            if ($oldKeyName != "" && $oldKey != "") {
                $this->MultiSelectKey .= "<input type=\"hidden\" name=\"" . $oldKeyName . "\" id=\"" . $oldKeyName . "\" value=\"" . HtmlEncode($oldKey) . "\">";
            }
            if ($this->RowAction == "insert" && $this->isConfirm() && $this->emptyRow()) {
                $this->MultiSelectKey .= "<input type=\"hidden\" name=\"" . $blankRowName . "\" id=\"" . $blankRowName . "\" value=\"1\">";
            }
        }

        // "delete"
        if ($this->AllowAddDeleteRow) {
            if ($this->CurrentMode == "add" || $this->CurrentMode == "copy" || $this->CurrentMode == "edit") {
                $options = &$this->ListOptions;
                $options->UseButtonGroup = true; // Use button group for grid delete button
                $opt = $options["griddelete"];
                if (!$this->security->allowDelete(CurrentProjectID() . $this->TableName) && is_numeric($this->RowIndex) && ($this->RowAction == "" || $this->RowAction == "edit")) { // Do not allow delete existing record
                    $opt->Body = "&nbsp;";
                } else {
                    $opt->Body = "<a class=\"ew-grid-link ew-grid-delete\" title=\"" . HtmlTitle($this->language->phrase("DeleteLink")) . "\" data-caption=\"" . HtmlTitle($this->language->phrase("DeleteLink")) . "\" data-ew-action=\"delete-grid-row\" data-rowindex=\"" . $this->RowIndex . "\">" . $this->language->phrase("DeleteLink") . "</a>";
                }
            }
        }

        // "sequence"
        $opt = $this->ListOptions["sequence"];
        $opt->Body = FormatSequenceNumber($this->RecordCount);
        if ($this->CurrentMode == "view") {
            // "view"
            $opt = $this->ListOptions["view"];
            $viewcaption = HtmlTitle($this->language->phrase("ViewLink"));
            if ($this->security->allowView(CurrentProjectID() . 'users') && $this->showOptionLink("view")) {
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
            if ($this->security->allowEdit(CurrentProjectID() . 'users') && $this->showOptionLink("edit")) {
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
            if ($this->security->allowAdd(CurrentProjectID() . 'users') && $this->showOptionLink("add")) {
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
            if ($this->security->allowDelete(CurrentProjectID() . 'users') && $this->showOptionLink("delete")) {
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

        // Render list options (to be implemented by extensions)

        // Call ListOptions_Rendered event
        $this->listOptionsRendered();
    }

    // Set up other options
    protected function setupOtherOptions(): void
    {
        $option = $this->OtherOptions["addedit"];
        $item = &$option->addGroupOption();
        $item->Body = "";
        $item->Visible = false;

        // Add
        if ($this->CurrentMode == "view") { // Check view mode
            $item = &$option->add("add");
            $addcaption = HtmlTitle($this->language->phrase("AddLink"));
            $this->AddUrl = $this->getAddUrl();
            if ($this->ModalAdd && !IsMobile()) {
				$item->Body = "<a class=\"ew-add-edit ew-add\" title=\"" . $addcaption . "\" data-table=\"users\" data-caption=\"" . $addcaption . "\" data-ew-action=\"modal\" data-action=\"add\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->AddUrl)) . "\" data-ask=\"1\" data-btn=\"AddBtn\">" . $this->language->phrase("AddLink") . "</a>";
            } else {
                $item->Body = "<a class=\"ew-add-edit ew-add\" title=\"" . $addcaption . "\" data-caption=\"" . $addcaption . "\" href=\"" . HtmlEncode(GetUrl($this->AddUrl)) . "\">" . $this->language->phrase("AddLink") . "</a>";
            }
            $item->Visible = $this->AddUrl != "" && $this->security->allowAdd(CurrentProjectID() . 'users');
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
            if (in_array($this->CurrentMode, ["add", "copy", "edit"]) && !$this->isConfirm()) { // Check add/copy/edit mode
                if ($this->AllowAddDeleteRow) {
                    $option = $options["addedit"];
                    $option->UseDropDownButton = false;
                    $item = &$option->add("addblankrow");
                    $item->Body = "<a class=\"ew-add-edit ew-add-blank-row\" title=\"" . HtmlTitle($this->language->phrase("AddBlankRow")) . "\" data-caption=\"" . HtmlTitle($this->language->phrase("AddBlankRow")) . "\" data-ew-action=\"add-grid-row\">" . $this->language->phrase("AddBlankRow") . "</a>";
                    $item->Visible = $this->security->allowAdd(CurrentProjectID() . 'users');
                    $this->ShowOtherOptions = $item->Visible;
                }
            }
            if ($this->CurrentMode == "view") { // Check view mode
                $option = $options["addedit"];
                $item = $option["add"];
                $this->ShowOtherOptions = $item?->Visible ?? false;
            }
    }

    // Set up Grid
    public function setupGrid(): void
    {
        if ($this->CurrentMode == "view" && $this->DetailViewPaging && !$this->isExport()) { // Allow paging in View mode
            // Set the last record to display
            if ($this->TotalRecords > $this->StartRecord + $this->DisplayRecords - 1) {
                $this->StopRecord = $this->StartRecord + $this->DisplayRecords - 1;
            } else {
                $this->StopRecord = $this->TotalRecords;
            }
        } else { // Show all records if not paging in View mode
            $this->StartRecord = 1;
            $this->StopRecord = $this->TotalRecords;
        }

        // Restore number of post back records
        if ($this->isConfirm() || $this->EventCancelled) {
            if ($this->hasKeyCount() && ($this->isGridAdd() || $this->isGridEdit() || $this->isConfirm())) {
                $this->KeyCount = $this->getKeyCount();
                $this->StopRecord = $this->StartRecord + $this->KeyCount - 1;
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
        if ($this->isGridAdd() || $this->isGridEdit() || $this->isConfirm() || $this->isMultiEdit()) {
            $this->RowIndex++;
            $this->FormIndex = $this->RowIndex;
            if ($this->hasRowAction() && ($this->isConfirm() || $this->EventCancelled)) {
                $this->RowAction = $this->getRowAction();
            } elseif ($this->isGridAdd()) {
                $this->RowAction = "insert";
            } else {
                $this->RowAction = "";
            }
        }

        // Set up key count
        $this->KeyCount = $this->RowIndex;

        // Init row class and style
        $this->resetAttributes();
        $this->CssClass = "";
        if ($this->isGridAdd()) {
            if ($this->CurrentMode == "copy") {
                $this->loadRowValues($this->CurrentRow); // Load row values
                $this->OldKey = $this->getKey(true); // Get from CurrentValue
            } else {
                $this->loadRowValues(); // Load default values
                $this->OldKey = "";
            }
        } else {
            $this->loadRowValues($this->CurrentRow); // Load row values
            $this->OldKey = $this->getKey(true); // Get from CurrentValue
        }
        $this->setKey($this->OldKey);
        $this->RowType = RowType::VIEW; // Render view
        if (($this->isAdd() || $this->isCopy()) && $this->InlineRowCount == 0 || $this->isGridAdd()) { // Add
            $this->RowType = RowType::ADD; // Render add
        }
        if ($this->isGridAdd() && $this->EventCancelled && !$this->hasBlankRow()) { // Insert failed
            $this->restoreCurrentRowFormValues($this->RowIndex); // Restore form values
        }
        if ($this->isGridEdit()) { // Grid edit
            if ($this->EventCancelled) {
                $this->restoreCurrentRowFormValues($this->RowIndex); // Restore form values
            }
            if ($this->RowAction == "insert") {
                $this->RowType = RowType::ADD; // Render add
            } else {
                $this->RowType = RowType::EDIT; // Render edit
            }
        }
        if ($this->isGridEdit() && ($this->RowType == RowType::EDIT || $this->RowType == RowType::ADD) && $this->EventCancelled) { // Update failed
            $this->restoreCurrentRowFormValues($this->RowIndex); // Restore form values
        }
        if ($this->isConfirm()) { // Confirm row
            $this->restoreCurrentRowFormValues($this->RowIndex); // Restore form values
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

// Get upload files
    protected function getUploadFiles(): void
    {
        $this->Photo->Upload->Index = $this->FormIndex;
        $this->Photo->Upload->uploadFile();
        $this->Photo->CurrentValue = $this->Photo->Upload->FileName;
    }

    // Load default values
    protected function loadDefaultValues(): void
    {
        $this->UserLevel->DefaultValue = $this->UserLevel->getDefault(); // PHP
        $this->UserLevel->OldValue = $this->UserLevel->DefaultValue;
        $this->Photo->Upload->Index = $this->RowIndex;
        $this->Avatar->Upload->Index = $this->RowIndex;
        $this->CreatedAt->DefaultValue = $this->CreatedAt->getDefault(); // PHP
        $this->CreatedAt->OldValue = $this->CreatedAt->DefaultValue;
        $this->CreatedBy->DefaultValue = $this->CreatedBy->getDefault(); // PHP
        $this->CreatedBy->OldValue = $this->CreatedBy->DefaultValue;
    }

    // Load form values
    protected function loadFormValues(): void
    {
        $validate = !Config("SERVER_VALIDATE");

        // Check field name 'UserID' before field var 'x__UserID'
        $val = $this->getFormValue("UserID", null) ?? $this->getFormValue("x__UserID", null);
        if (!$this->_UserID->IsDetailKey && !$this->isGridAdd() && !$this->isAdd()) {
            $this->_UserID->setFormValue($val);
        }

        // Check field name 'Username' before field var 'x__Username'
        $val = $this->getFormValue("Username", null) ?? $this->getFormValue("x__Username", null);
        if (!$this->_Username->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->_Username->Visible = false; // Disable update for API request
            } else {
                $this->_Username->setFormValue($val);
            }
        }
        if ($this->hasFormValue("o__Username")) {
            $this->_Username->setOldValue($this->getFormValue("o__Username"));
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
        if ($this->hasFormValue("o_UserLevel")) {
            $this->UserLevel->setOldValue($this->getFormValue("o_UserLevel"));
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
        if ($this->hasFormValue("o_CompleteName")) {
            $this->CompleteName->setOldValue($this->getFormValue("o_CompleteName"));
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
        if ($this->hasFormValue("o_Gender")) {
            $this->Gender->setOldValue($this->getFormValue("o_Gender"));
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
        if ($this->hasFormValue("o__Email")) {
            $this->_Email->setOldValue($this->getFormValue("o__Email"));
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
        if ($this->hasFormValue("o_Activated")) {
            $this->Activated->setOldValue($this->getFormValue("o_Activated"));
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
        if ($this->hasFormValue("o_ActiveStatus")) {
            $this->ActiveStatus->setOldValue($this->getFormValue("o_ActiveStatus"));
        }
		$this->Photo->OldUploadPath = $this->Photo->getUploadPath(); // PHP
		$this->Photo->UploadPath = $this->Photo->OldUploadPath;
        $this->getUploadFiles(); // Get upload files
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        if (!$this->isGridAdd() && !$this->isAdd()) {
            $this->_UserID->CurrentValue = $this->_UserID->FormValue;
        }
        $this->_Username->CurrentValue = $this->_Username->FormValue;
        $this->UserLevel->CurrentValue = $this->UserLevel->FormValue;
        $this->CompleteName->CurrentValue = $this->CompleteName->FormValue;
        $this->Gender->CurrentValue = $this->Gender->FormValue;
        $this->_Email->CurrentValue = $this->_Email->FormValue;
        $this->Activated->CurrentValue = $this->Activated->FormValue;
        $this->ActiveStatus->CurrentValue = $this->ActiveStatus->FormValue;
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
        $this->Photo->Upload->Index = $this->RowIndex;
        $this->Notes->setDbValue($row['Notes']);
        $this->ReportsTo->setDbValue($row['ReportsTo']);
        $this->Gender->setDbValue($row['Gender']);
        $this->_Email->setDbValue($row['Email']);
        $this->Activated->setDbValue($row['Activated']);
        $this->_Profile->setDbValue($row['Profile']);
        $this->Avatar->Upload->DbValue = $row['Avatar'];
        $this->Avatar->setDbValue($this->Avatar->Upload->DbValue);
        $this->Avatar->Upload->Index = $this->RowIndex;
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
        $this->CopyUrl = $this->getCopyUrl();
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
        } elseif ($this->RowType == RowType::ADD) {
            // UserID
            $this->_UserID->EditValue = $this->_UserID->CurrentValue;

            // Username
            $this->_Username->setupEditAttributes();
            $this->_Username->EditValue = !$this->_Username->Raw ? HtmlDecode($this->_Username->CurrentValue) : $this->_Username->CurrentValue;
            $this->_Username->PlaceHolder = RemoveHtml($this->_Username->caption());

            // UserLevel
            $this->UserLevel->setupEditAttributes();
            if ($this->UserLevel->getSessionValue() != "") {
                $this->UserLevel->CurrentValue = GetForeignKeyValue($this->UserLevel->getSessionValue());
                $this->UserLevel->OldValue = $this->UserLevel->CurrentValue;
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

            // CompleteName
            $this->CompleteName->setupEditAttributes();
            $this->CompleteName->EditValue = !$this->CompleteName->Raw ? HtmlDecode($this->CompleteName->CurrentValue) : $this->CompleteName->CurrentValue;
            $this->CompleteName->PlaceHolder = RemoveHtml($this->CompleteName->caption());

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
                if ($this->RowIndex == '$rowindex$') {
                    $this->Photo->Upload->FileName = "";
                } else {
                    $this->Photo->Upload->FileName = $this->Photo->CurrentValue;
                }
            }
            if (!Config("CREATE_UPLOAD_FILE_ON_COPY")) {
                $this->Photo->Upload->DbValue = null;
            }
            if (is_numeric($this->RowIndex)) {
                $this->Photo->Upload->setupTempDirectory($this->RowIndex);
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

            // ActiveStatus
            $this->ActiveStatus->EditValue = $this->ActiveStatus->options(false);
            $this->ActiveStatus->PlaceHolder = RemoveHtml($this->ActiveStatus->caption());

            // Add refer script

            // UserID
            $this->_UserID->HrefValue = "";

            // Username
            $this->_Username->HrefValue = "";

            // UserLevel
            $this->UserLevel->HrefValue = "";

            // CompleteName
            $this->CompleteName->HrefValue = "";

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

            // Gender
            $this->Gender->HrefValue = "";

            // Email
            $this->_Email->HrefValue = "";

            // Activated
            $this->Activated->HrefValue = "";

            // ActiveStatus
            $this->ActiveStatus->HrefValue = "";
        } elseif ($this->RowType == RowType::EDIT) {
            // UserID
            $this->_UserID->setupEditAttributes();
            $this->_UserID->EditValue = $this->_UserID->CurrentValue;

            // Username
            $this->_Username->setupEditAttributes();
            $this->_Username->EditValue = !$this->_Username->Raw ? HtmlDecode($this->_Username->CurrentValue) : $this->_Username->CurrentValue;
            $this->_Username->PlaceHolder = RemoveHtml($this->_Username->caption());

            // UserLevel
            $this->UserLevel->setupEditAttributes();
            if ($this->UserLevel->getSessionValue() != "") {
                $this->UserLevel->CurrentValue = GetForeignKeyValue($this->UserLevel->getSessionValue());
                $this->UserLevel->OldValue = $this->UserLevel->CurrentValue;
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

            // CompleteName
            $this->CompleteName->setupEditAttributes();
            $this->CompleteName->EditValue = !$this->CompleteName->Raw ? HtmlDecode($this->CompleteName->CurrentValue) : $this->CompleteName->CurrentValue;
            $this->CompleteName->PlaceHolder = RemoveHtml($this->CompleteName->caption());

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
                if ($this->RowIndex == '$rowindex$') {
                    $this->Photo->Upload->FileName = "";
                } else {
                    $this->Photo->Upload->FileName = $this->Photo->CurrentValue;
                }
            }
            if (is_numeric($this->RowIndex)) {
                $this->Photo->Upload->setupTempDirectory($this->RowIndex);
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

            // ActiveStatus
            $this->ActiveStatus->EditValue = $this->ActiveStatus->options(false);
            $this->ActiveStatus->PlaceHolder = RemoveHtml($this->ActiveStatus->caption());

            // Edit refer script

            // UserID
            $this->_UserID->HrefValue = "";

            // Username
            $this->_Username->HrefValue = "";

            // UserLevel
            $this->UserLevel->HrefValue = "";

            // CompleteName
            $this->CompleteName->HrefValue = "";

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

            // Gender
            $this->Gender->HrefValue = "";

            // Email
            $this->_Email->HrefValue = "";

            // Activated
            $this->Activated->HrefValue = "";

            // ActiveStatus
            $this->ActiveStatus->HrefValue = "";
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
            if ($this->_UserID->Visible && $this->_UserID->Required) {
                if (!$this->_UserID->IsDetailKey && IsEmpty($this->_UserID->FormValue)) {
                    $this->_UserID->addErrorMessage(str_replace("%s", $this->_UserID->caption(), $this->_UserID->RequiredErrorMessage));
                }
            }
            if ($this->_Username->Visible && $this->_Username->Required) {
                if (!$this->_Username->IsDetailKey && IsEmpty($this->_Username->FormValue)) {
                    $this->_Username->addErrorMessage(str_replace("%s", $this->_Username->caption(), $this->_Username->RequiredErrorMessage));
                }
            }
            if (!$this->_Username->Raw && Config("REMOVE_XSS") && CheckUsername($this->_Username->FormValue)) {
                $this->_Username->addErrorMessage($this->language->phrase("InvalidUsernameChars"));
            }
            if ($this->UserLevel->Visible && $this->UserLevel->Required) {
                if ($this->security->canAdmin() && !$this->UserLevel->IsDetailKey && IsEmpty($this->UserLevel->FormValue)) {
                    $this->UserLevel->addErrorMessage(str_replace("%s", $this->UserLevel->caption(), $this->UserLevel->RequiredErrorMessage));
                }
            }
            if ($this->CompleteName->Visible && $this->CompleteName->Required) {
                if (!$this->CompleteName->IsDetailKey && IsEmpty($this->CompleteName->FormValue)) {
                    $this->CompleteName->addErrorMessage(str_replace("%s", $this->CompleteName->caption(), $this->CompleteName->RequiredErrorMessage));
                }
            }
            if ($this->Photo->Visible && $this->Photo->Required) {
                if ($this->Photo->Upload->FileName == "" && !$this->Photo->Upload->KeepFile) {
                    $this->Photo->addErrorMessage(str_replace("%s", $this->Photo->caption(), $this->Photo->RequiredErrorMessage));
                }
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
            if ($this->ActiveStatus->Visible && $this->ActiveStatus->Required) {
                if ($this->ActiveStatus->FormValue == "") {
                    $this->ActiveStatus->addErrorMessage(str_replace("%s", $this->ActiveStatus->caption(), $this->ActiveStatus->RequiredErrorMessage));
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

    // Delete records based on current filter
    protected function deleteRows(): ?bool
    {
        if (!$this->security->canDelete()) {
            $this->setFailureMessage($this->language->phrase("NoDeletePermission")); // No delete permission
            return false;
        }
        $sql = $this->getCurrentSql(true);
        $conn = $this->getConnection();
        $rows = $conn->fetchAllAssociative($sql);
        if (count($rows) == 0) {
            $this->setFailureMessage($this->language->phrase("NoRecord")); // No record found
            return false;
        }

        // Clone old rows
        $oldRows = $rows;
        $successKeys = [];
        $failKeys = [];
        $skipRecords = [];
        $rowindex = 0;
        foreach ($oldRows as $row) {
            $rowindex++;
            $thisKey = $this->getKeyFromRecord($row);

            // Call row deleting event
            $deleteRow = $this->rowDeleting($row);
            if ($deleteRow) { // Delete
                $deleteRow = $this->delete($row);
                if ($deleteRow === false && !IsEmpty($this->DbErrorMessage)) { // Show database error
                    $this->setFailureMessage($this->DbErrorMessage);
                }
            }
            if ($deleteRow === null) { // Row skipped
                $skipRecords[] = $rowindex . (!IsEmpty($thisKey) ? ": " . $thisKey : ""); // Record count and key if exists
            } elseif ($deleteRow === false) { // Row not deleted
                if ($this->UseTransaction) {
                    $successKeys = []; // Reset success keys
                    break;
                }
                $failKeys[] = $thisKey;
            } elseif ($deleteRow) { // Row deleted
                if (Config("DELETE_UPLOADED_FILES")) { // Delete old files
                    $this->deleteUploadedFiles($row);
                }

                // Call Row Deleted event
                $this->rowDeleted($row);
                $successKeys[] = $thisKey;
            }
        }

        // Any records deleted
        $deleteRows = count($successKeys) > 0;
        if (!$deleteRows && count($skipRecords) > 0) { // Record skipped
            return null;
        }
        if (!$deleteRows) {
            // Set up error message
            if ($this->peekSuccessMessage() || $this->peekFailureMessage()) {
                // Use the message, do nothing
            } elseif ($this->CancelMessage != "") {
                $this->setFailureMessage($this->CancelMessage);
                $this->CancelMessage = "";
            } else {
                $this->setFailureMessage($this->language->phrase("DeleteCancelled"));
            }
        }
        return $deleteRows;
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
        if ($this->Photo->Visible && !$this->Photo->Upload->KeepFile) {
            $this->Photo->UploadPath = $this->Photo->getUploadPath();
            if (!IsEmpty($this->Photo->Upload->FileName)) {
                FixUploadFileNames($this->Photo);
                $this->Photo->setDbValueDef($newRow, $this->Photo->Upload->FileName, $this->Photo->ReadOnly);
            }
        }

        // Call Row Updating event
        $updateRow = $this->rowUpdating($oldRow, $newRow);
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
                if ($this->Photo->Visible && !$this->Photo->Upload->KeepFile) {
                    if (!SaveUploadFiles($this->Photo, $newRow['Photo'], false)) {
                        $this->setFailureMessage($this->language->phrase("UploadError7"));
                        return false;
                    }
                }
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
        return $editRow;
    }

    /**
     * Get edit row
     *
     * @return array
     */
    protected function getEditRow(array $oldRow): array
    {
        $this->Photo->OldUploadPath = $this->Photo->getUploadPath(); // PHP
        $this->Photo->UploadPath = $this->Photo->OldUploadPath;
        $newRow = [];

        // Username
        $this->_Username->setDbValueDef($newRow, $this->_Username->CurrentValue, $this->_Username->ReadOnly);

        // UserLevel
        if ($this->security->canAdmin()) { // System admin
            if ($this->UserLevel->getSessionValue() != "") {
                $this->UserLevel->ReadOnly = true;
            }
            $this->UserLevel->setDbValueDef($newRow, $this->UserLevel->CurrentValue, $this->UserLevel->ReadOnly);
        }

        // CompleteName
        $this->CompleteName->setDbValueDef($newRow, $this->CompleteName->CurrentValue, $this->CompleteName->ReadOnly);

        // Photo
        if ($this->Photo->Visible && !$this->Photo->ReadOnly && !$this->Photo->Upload->KeepFile) {
            if ($this->Photo->Upload->FileName == "") {
                $newRow['Photo'] = null;
            } else {
                FixUploadTempFileNames($this->Photo);
                $newRow['Photo'] = $this->Photo->Upload->FileName;
            }
        }

        // Gender
        $this->Gender->setDbValueDef($newRow, $this->Gender->CurrentValue, $this->Gender->ReadOnly);

        // Email
        $this->_Email->setDbValueDef($newRow, $this->_Email->CurrentValue, $this->_Email->ReadOnly);

        // Activated
        $tmpBool = $this->Activated->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Activated->setDbValueDef($newRow, $tmpBool, $this->Activated->ReadOnly);

        // ActiveStatus
        $tmpBool = $this->ActiveStatus->CurrentValue;
        if ($tmpBool != "1" && $tmpBool != "0") {
            $tmpBool = !empty($tmpBool) ? "1" : "0";
        }
        $this->ActiveStatus->setDbValueDef($newRow, $tmpBool, $this->ActiveStatus->ReadOnly);
        return $newRow;
    }

    /**
     * Restore edit form from row
     * @param array $row Row
     */
    protected function restoreEditFormFromRow(array $row): void
    {
        if (isset($row['Username'])) { // Username
            $this->_Username->CurrentValue = $row['Username'];
        }
        if (isset($row['UserLevel'])) { // UserLevel
            $this->UserLevel->CurrentValue = $row['UserLevel'];
        }
        if (isset($row['CompleteName'])) { // CompleteName
            $this->CompleteName->CurrentValue = $row['CompleteName'];
        }
        if (isset($row['Photo'])) { // Photo
            $this->Photo->CurrentValue = $row['Photo'];
        }
        if (isset($row['Gender'])) { // Gender
            $this->Gender->CurrentValue = $row['Gender'];
        }
        if (isset($row['Email'])) { // Email
            $this->_Email->CurrentValue = $row['Email'];
        }
        if (isset($row['Activated'])) { // Activated
            $this->Activated->CurrentValue = $row['Activated'];
        }
        if (isset($row['ActiveStatus'])) { // ActiveStatus
            $this->ActiveStatus->CurrentValue = $row['ActiveStatus'];
        }
    }

    // Add record
    protected function addRow(?array $oldRow = null): bool
    {
        // Set up foreign key field value from Session
        if ($this->getCurrentMasterTable() == "userlevels") {
            $this->UserLevel->Visible = true; // Need to insert foreign key
            $this->UserLevel->CurrentValue = $this->UserLevel->getSessionValue();
        }

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

        // UserLevel
        if ($this->security->canAdmin()) { // System admin
            $this->UserLevel->setDbValueDef($newRow, $this->UserLevel->CurrentValue, strval($this->UserLevel->CurrentValue) == "");
        }

        // CompleteName
        $this->CompleteName->setDbValueDef($newRow, $this->CompleteName->CurrentValue, false);

        // Photo
        if ($this->Photo->Visible && !$this->Photo->Upload->KeepFile) {
            if ($this->Photo->Upload->FileName == "") {
                $newRow['Photo'] = null;
            } else {
                FixUploadTempFileNames($this->Photo);
                $newRow['Photo'] = $this->Photo->Upload->FileName;
            }
        }

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

        // ActiveStatus
        $tmpBool = $this->ActiveStatus->CurrentValue;
        if ($tmpBool != "1" && $tmpBool != "0") {
            $tmpBool = !empty($tmpBool) ? "1" : "0";
        }
        $this->ActiveStatus->setDbValueDef($newRow, $tmpBool, false);

        // ReportsTo
        if (!$this->security->canAccess() && $this->security->isLoggedIn()) { // No access permission
            $newRow['ReportsTo'] = CurrentUserID();
        }
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
        // Hide foreign keys
        $masterTblVar = $this->getCurrentMasterTable();
        if ($masterTblVar == "userlevels") {
            $masterTbl = Container("userlevels");
            $this->UserLevel->Visible = false;
            if ($masterTbl->EventCancelled) {
                $this->EventCancelled = true;
            }
        }
        $this->DbMasterFilter = $this->getMasterFilterFromSession(); // Get master filter from session
        $this->DbDetailFilter = $this->getDetailFilterFromSession(); // Get detail filter from session
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
}
