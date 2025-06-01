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
class HelpGrid extends Help
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "grid";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "HelpGrid";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // Grid form hidden field names
    public string $FormName = "fhelpgrid";

    // CSS class/style
    public string $CurrentPageName = "helpgrid";

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
        $this->Help_ID->Visible = false;
        $this->_Language->setVisibility();
        $this->Topic->setVisibility();
        $this->Description->setVisibility();
        $this->Category->setVisibility();
        $this->Order->Visible = false;
        $this->Display_in_Page->Visible = false;
        $this->Updated_By->Visible = false;
        $this->Last_Updated->Visible = false;
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'help';
        $this->TableName = 'help';

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

        // Table object (help)
        if (!isset($GLOBALS["help"]) || $GLOBALS["help"]::class == PROJECT_NAMESPACE . "help") {
            $GLOBALS["help"] = &$this;
        }
        $this->AddUrl = "helpadd";

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'help');
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
    public int $SearchFieldsPerRow = 1; // For extended search
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
        $this->setupLookupOptions($this->_Language);
        $this->setupLookupOptions($this->Category);
        $this->setupLookupOptions($this->Updated_By);

        // Load default values for add
        $this->loadDefaultValues();

        // Update form name to avoid conflict
        if ($this->IsModal) {
            $this->FormName = "fhelpgrid";
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
        if ($this->CurrentMode != "add" && $this->DbMasterFilter != "" && $this->getCurrentMasterTable() == "help_categories") {
            $masterTbl = Container("help_categories");
            $masterRow = $masterTbl->loadRecords($this->DbMasterFilter)->fetchAssociative();
            $this->MasterRecordExists = $masterRow !== false;
            if (!$this->MasterRecordExists) {
                $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record found
                $this->terminate("helpcategorieslist"); // Return to master page
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
            $this->hasFormValue("x__Language")
            && $this->hasFormValue("o__Language")
            && $this->_Language->CurrentValue != $this->_Language->DefaultValue
            && !($this->_Language->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->_Language->CurrentValue == $this->_Language->getSessionValue())
        ) {
            return false;
        }
        if (
            $this->hasFormValue("x_Topic")
            && $this->hasFormValue("o_Topic")
            && $this->Topic->CurrentValue != $this->Topic->DefaultValue
            && !($this->Topic->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->Topic->CurrentValue == $this->Topic->getSessionValue())
        ) {
            return false;
        }
        if (
            $this->hasFormValue("x_Description")
            && $this->hasFormValue("o_Description")
            && $this->Description->CurrentValue != $this->Description->DefaultValue
            && !($this->Description->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->Description->CurrentValue == $this->Description->getSessionValue())
        ) {
            return false;
        }
        if (
            $this->hasFormValue("x_Category")
            && $this->hasFormValue("o_Category")
            && $this->Category->CurrentValue != $this->Category->DefaultValue
            && !($this->Category->IsForeignKey && $this->getCurrentMasterTable() != "" && $this->Category->CurrentValue == $this->Category->getSessionValue())
        ) {
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
                        $this->Category->setSessionValue("");
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
        $item->Visible = $this->security->allowView(CurrentProjectID() . 'help');
        $item->OnLeft = true;

        // "edit"
        $item = &$this->ListOptions->add("edit");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->allowEdit(CurrentProjectID() . 'help');
        $item->OnLeft = true;

        // "copy"
        $item = &$this->ListOptions->add("copy");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->allowAdd(CurrentProjectID() . 'help');
        $item->OnLeft = true;

        // "delete"
        $item = &$this->ListOptions->add("delete");
        $item->CssClass = "text-nowrap";
        $item->Visible = $this->security->allowDelete(CurrentProjectID() . 'help');
        $item->OnLeft = true;

        // Drop down button for ListOptions
        $this->ListOptions->UseDropDownButton = false;
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
        if ($this->CurrentMode == "view") {
            // "view"
            $opt = $this->ListOptions["view"];
            $viewcaption = HtmlTitle($this->language->phrase("ViewLink"));
            if ($this->security->allowView(CurrentProjectID() . 'help')) {
                if ($this->ModalView && !IsMobile()) {
                    $opt->Body = "<a class=\"ew-row-link ew-view\" title=\"" . $viewcaption . "\" data-table=\"help\" data-caption=\"" . $viewcaption . "\" data-ew-action=\"modal\" data-action=\"view\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->ViewUrl)) . "\" data-btn=\"null\">" . $this->language->phrase("ViewLink") . "</a>";
                } else {
                    $opt->Body = "<a class=\"ew-row-link ew-view\" title=\"" . $viewcaption . "\" data-caption=\"" . $viewcaption . "\" href=\"" . HtmlEncode(GetUrl($this->ViewUrl)) . "\">" . $this->language->phrase("ViewLink") . "</a>";
                }
            } else {
                $opt->Body = "";
            }

            // "edit"
            $opt = $this->ListOptions["edit"];
            $editcaption = HtmlTitle($this->language->phrase("EditLink"));
            if ($this->security->allowEdit(CurrentProjectID() . 'help')) {
                if ($this->ModalEdit && !IsMobile()) {
					$opt->Body = "<a class=\"ew-row-link ew-edit\" title=\"" . $editcaption . "\" data-table=\"help\" data-caption=\"" . $editcaption . "\" data-ew-action=\"modal\" data-action=\"edit\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->EditUrl)) . "\" data-ask=\"1\" data-btn=\"SaveBtn\">" . $this->language->phrase("EditLink") . "</a>";
                } else {
                    $opt->Body = "<a class=\"ew-row-link ew-edit\" title=\"" . $editcaption . "\" data-caption=\"" . $editcaption . "\" href=\"" . HtmlEncode(GetUrl($this->EditUrl)) . "\">" . $this->language->phrase("EditLink") . "</a>";
                }
            } else {
                $opt->Body = "";
            }

            // "copy"
            $opt = $this->ListOptions["copy"];
            $copycaption = HtmlTitle($this->language->phrase("CopyLink"));
            if ($this->security->allowAdd(CurrentProjectID() . 'help')) {
                if ($this->ModalAdd && !IsMobile()) {
					$opt->Body = "<a class=\"ew-row-link ew-copy\" title=\"" . $copycaption . "\" data-table=\"help\" data-caption=\"" . $copycaption . "\" data-ew-action=\"modal\" data-action=\"add\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->CopyUrl)) . "\" data-ask=\"1\" data-btn=\"AddBtn\">" . $this->language->phrase("CopyLink") . "</a>";
                } else {
                    $opt->Body = "<a class=\"ew-row-link ew-copy\" title=\"" . $copycaption . "\" data-caption=\"" . $copycaption . "\" href=\"" . HtmlEncode(GetUrl($this->CopyUrl)) . "\">" . $this->language->phrase("CopyLink") . "</a>";
                }
            } else {
                $opt->Body = "";
            }

            // "delete"
            $opt = $this->ListOptions["delete"];
            if ($this->security->allowDelete(CurrentProjectID() . 'help')) {
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
				$item->Body = "<a class=\"ew-add-edit ew-add\" title=\"" . $addcaption . "\" data-table=\"help\" data-caption=\"" . $addcaption . "\" data-ew-action=\"modal\" data-action=\"add\" data-ajax=\"" . ($this->UseAjaxActions ? "true" : "false") . "\" data-url=\"" . HtmlEncode(GetUrl($this->AddUrl)) . "\" data-ask=\"1\" data-btn=\"AddBtn\">" . $this->language->phrase("AddLink") . "</a>";
            } else {
                $item->Body = "<a class=\"ew-add-edit ew-add\" title=\"" . $addcaption . "\" data-caption=\"" . $addcaption . "\" href=\"" . HtmlEncode(GetUrl($this->AddUrl)) . "\">" . $this->language->phrase("AddLink") . "</a>";
            }
            $item->Visible = $this->AddUrl != "" && $this->security->allowAdd(CurrentProjectID() . 'help');
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
                    $item->Visible = $this->security->allowAdd(CurrentProjectID() . 'help');
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
                $this->RowAttrs->merge(["data-rowindex" => $this->RowIndex, "id" => "r0_help", "data-rowtype" => RowType::ADD]);
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
            "id" => "r" . $this->RowCount . "_help",
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
        if ($this->hasFormValue("o__Language")) {
            $this->_Language->setOldValue($this->getFormValue("o__Language"));
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
        if ($this->hasFormValue("o_Topic")) {
            $this->Topic->setOldValue($this->getFormValue("o_Topic"));
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
        if ($this->hasFormValue("o_Description")) {
            $this->Description->setOldValue($this->getFormValue("o_Description"));
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
        if ($this->hasFormValue("o_Category")) {
            $this->Category->setOldValue($this->getFormValue("o_Category"));
        }

        // Check field name 'Help_ID' first before field var 'x_Help_ID'
        $val = $this->hasFormValue("Help_ID") ? $this->getFormValue("Help_ID") : $this->getFormValue("x_Help_ID");
        if (!$this->Help_ID->IsDetailKey && !$this->isGridAdd() && !$this->isAdd()) {
            $this->Help_ID->setFormValue($val);
        }
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        if (!$this->isGridAdd() && !$this->isAdd()) {
            $this->Help_ID->CurrentValue = $this->Help_ID->FormValue;
        }
        $this->_Language->CurrentValue = $this->_Language->FormValue;
        $this->Topic->CurrentValue = $this->Topic->FormValue;
        $this->Description->CurrentValue = $this->Description->FormValue;
        $this->Category->CurrentValue = $this->Category->FormValue;
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
        $this->ViewUrl = $this->getViewUrl();
        $this->EditUrl = $this->getEditUrl();
        $this->CopyUrl = $this->getCopyUrl();
        $this->DeleteUrl = $this->getDeleteUrl();

        // Call Row_Rendering event
        $this->rowRendering();

        // Common render codes for all row types

        // Help_ID

        // Language

        // Topic

        // Description

        // Category

        // Order

        // Display_in_Page

        // Updated_By

        // Last_Updated

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
            $this->_Language->TooltipValue = "";

            // Topic
            $this->Topic->HrefValue = "";
            $this->Topic->TooltipValue = "";

            // Description
            $this->Description->HrefValue = "";
            $this->Description->TooltipValue = "";

            // Category
            $this->Category->HrefValue = "";
            $this->Category->TooltipValue = "";
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
                $this->Category->OldValue = $this->Category->CurrentValue;
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

            // Add refer script

            // Language
            $this->_Language->HrefValue = "";

            // Topic
            $this->Topic->HrefValue = "";

            // Description
            $this->Description->HrefValue = "";

            // Category
            $this->Category->HrefValue = "";
        } elseif ($this->RowType == RowType::EDIT) {
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
                $this->Category->OldValue = $this->Category->CurrentValue;
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

            // Edit refer script

            // Language
            $this->_Language->HrefValue = "";

            // Topic
            $this->Topic->HrefValue = "";

            // Description
            $this->Description->HrefValue = "";

            // Category
            $this->Category->HrefValue = "";
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
        $newRow = [];

        // Language
        $this->_Language->setDbValueDef($newRow, $this->_Language->CurrentValue, $this->_Language->ReadOnly);

        // Topic
        $this->Topic->setDbValueDef($newRow, $this->Topic->CurrentValue, $this->Topic->ReadOnly);

        // Description
        $this->Description->setDbValueDef($newRow, $this->Description->CurrentValue, $this->Description->ReadOnly);

        // Category
        if ($this->Category->getSessionValue() != "") {
            $this->Category->ReadOnly = true;
        }
        $this->Category->setDbValueDef($newRow, $this->Category->CurrentValue, $this->Category->ReadOnly);
        return $newRow;
    }

    /**
     * Restore edit form from row
     * @param array $row Row
     */
    protected function restoreEditFormFromRow(array $row): void
    {
        if (isset($row['Language'])) { // Language
            $this->_Language->CurrentValue = $row['Language'];
        }
        if (isset($row['Topic'])) { // Topic
            $this->Topic->CurrentValue = $row['Topic'];
        }
        if (isset($row['Description'])) { // Description
            $this->Description->CurrentValue = $row['Description'];
        }
        if (isset($row['Category'])) { // Category
            $this->Category->CurrentValue = $row['Category'];
        }
    }

    // Add record
    protected function addRow(?array $oldRow = null): bool
    {
        // Set up foreign key field value from Session
        if ($this->getCurrentMasterTable() == "help_categories") {
            $this->Category->Visible = true; // Need to insert foreign key
            $this->Category->CurrentValue = $this->Category->getSessionValue();
        }

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
        return $newRow;
    }

    // Set up master/detail based on QueryString
    protected function setupMasterParms(): void
    {
        // Hide foreign keys
        $masterTblVar = $this->getCurrentMasterTable();
        if ($masterTblVar == "help_categories") {
            $masterTbl = Container("help_categories");
            $this->Category->Visible = false;
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
