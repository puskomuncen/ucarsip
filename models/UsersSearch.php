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
class UsersSearch extends Users
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "search";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "UsersSearch";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "userssearch";

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
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-search-table";

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
    public string $FormClassName = "ew-form ew-search-form";
    public bool $IsModal = false;
    public bool $IsMobileOrModal = false;

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

        // Set up Breadcrumb
        $this->setupBreadcrumb();

        // Check modal
        if ($this->IsModal) {
            $SkipHeaderFooter = true;
        }
        $this->IsMobileOrModal = IsMobile() || $this->IsModal;

        // Get action
        $this->CurrentAction = Post("action");
        if ($this->isSearch()) {
            // Build search string for advanced search, remove blank field
            $this->loadSearchValues(); // Get search values
            $srchStr = $this->validateSearch() ? $this->buildAdvancedSearch() : "";
            if ($srchStr != "") {
                $srchStr = "userslist" . "?" . $srchStr;
                // Do not return Json for UseAjaxActions
                if ($this->IsModal && $this->UseAjaxActions) {
                    $this->IsModal = false;
                }
                $this->terminate($srchStr); // Go to list page
                return;
            }
        }

        // Restore search settings from Session
        if (!$this->hasInvalidFields()) {
            $this->loadAdvancedSearch();
        }

        // Render row for search
        $this->RowType = RowType::SEARCH;
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

    // Build advanced search
    protected function buildAdvancedSearch(): string
    {
        $srchUrl = "";
        $this->buildSearchUrl($srchUrl, $this->_UserID); // UserID
        $this->buildSearchUrl($srchUrl, $this->_Username); // Username
        $this->buildSearchUrl($srchUrl, $this->UserLevel); // UserLevel
        $this->buildSearchUrl($srchUrl, $this->FirstName); // FirstName
        $this->buildSearchUrl($srchUrl, $this->LastName); // LastName
        $this->buildSearchUrl($srchUrl, $this->CompleteName); // CompleteName
        $this->buildSearchUrl($srchUrl, $this->BirthDate); // BirthDate
        $this->buildSearchUrl($srchUrl, $this->HomePhone); // HomePhone
        $this->buildSearchUrl($srchUrl, $this->Photo); // Photo
        $this->buildSearchUrl($srchUrl, $this->Notes); // Notes
        $this->buildSearchUrl($srchUrl, $this->ReportsTo); // ReportsTo
        $this->buildSearchUrl($srchUrl, $this->Gender); // Gender
        $this->buildSearchUrl($srchUrl, $this->_Email); // Email
        $this->buildSearchUrl($srchUrl, $this->Activated, true); // Activated
        $this->buildSearchUrl($srchUrl, $this->Avatar); // Avatar
        $this->buildSearchUrl($srchUrl, $this->ActiveStatus, true); // ActiveStatus
        $this->buildSearchUrl($srchUrl, $this->MessengerColor); // MessengerColor
        $this->buildSearchUrl($srchUrl, $this->CreatedAt); // CreatedAt
        $this->buildSearchUrl($srchUrl, $this->CreatedBy); // CreatedBy
        $this->buildSearchUrl($srchUrl, $this->UpdatedAt); // UpdatedAt
        $this->buildSearchUrl($srchUrl, $this->UpdatedBy); // UpdatedBy
        if ($srchUrl != "") {
            $srchUrl .= "&";
        }
        $srchUrl .= "cmd=search";
        return $srchUrl;
    }

    // Build search URL
    protected function buildSearchUrl(string &$url, DbField $fld, bool $oprOnly = false): void
    {
        $wrk = "";
        $fldParm = $fld->Param;
        [
            "value" => $fldVal,
            "operator" => $fldOpr,
            "condition" => $fldCond,
            "value2" => $fldVal2,
            "operator2" => $fldOpr2
        ] = $this->getSearchValues($fldParm);
        if (is_array($fldVal)) {
            $fldVal = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $fldVal);
        }
        if (is_array($fldVal2)) {
            $fldVal2 = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $fldVal2);
        }
        $fldDataType = $fld->DataType;
        $value = ConvertSearchValue($fldVal, $fldOpr, $fld); // For testing if numeric only
        $value2 = ConvertSearchValue($fldVal2, $fldOpr2, $fld); // For testing if numeric only
        $fldOpr = ConvertSearchOperator($fldOpr, $fld, $value);
        $fldOpr2 = ConvertSearchOperator($fldOpr2, $fld, $value2);
        if (in_array($fldOpr, ["BETWEEN", "NOT BETWEEN"])) {
            $isValidValue = $fldDataType != DataType::NUMBER || $fld->VirtualSearch || IsNumericSearchValue($value, $fldOpr, $fld) && IsNumericSearchValue($value2, $fldOpr2, $fld);
            if ($fldVal != "" && $fldVal2 != "" && $isValidValue) {
                $wrk = "x_" . $fldParm . "=" . urlencode($fldVal) . "&y_" . $fldParm . "=" . urlencode($fldVal2) . "&z_" . $fldParm . "=" . urlencode($fldOpr);
            }
        } else {
            $isValidValue = $fldDataType != DataType::NUMBER || $fld->VirtualSearch || IsNumericSearchValue($value, $fldOpr, $fld);
            if ($fldVal != "" && $isValidValue && IsValidOperator($fldOpr)) {
                $wrk = "x_" . $fldParm . "=" . urlencode($fldVal) . "&z_" . $fldParm . "=" . urlencode($fldOpr);
            } elseif (in_array($fldOpr, ["IS NULL", "IS NOT NULL", "IS EMPTY", "IS NOT EMPTY"]) || ($fldOpr != "" && $oprOnly && IsValidOperator($fldOpr))) {
                $wrk = "z_" . $fldParm . "=" . urlencode($fldOpr);
            }
            $isValidValue = $fldDataType != DataType::NUMBER || $fld->VirtualSearch || IsNumericSearchValue($value2, $fldOpr2, $fld);
            if ($fldVal2 != "" && $isValidValue && IsValidOperator($fldOpr2)) {
                if ($wrk != "") {
                    $wrk .= "&v_" . $fldParm . "=" . urlencode($fldCond) . "&";
                }
                $wrk .= "y_" . $fldParm . "=" . urlencode($fldVal2) . "&w_" . $fldParm . "=" . urlencode($fldOpr2);
            } elseif (in_array($fldOpr2, ["IS NULL", "IS NOT NULL", "IS EMPTY", "IS NOT EMPTY"]) || ($fldOpr2 != "" && $oprOnly && IsValidOperator($fldOpr2))) {
                if ($wrk != "") {
                    $wrk .= "&v_" . $fldParm . "=" . urlencode($fldCond) . "&";
                }
                $wrk .= "w_" . $fldParm . "=" . urlencode($fldOpr2);
            }
        }
        if ($wrk != "") {
            if ($url != "") {
                $url .= "&";
            }
            $url .= $wrk;
        }
    }

// Load search values for validation
    protected function loadSearchValues(): bool
    {
        // Load search values
        $hasValue = false;

        // UserID
        if ($this->_UserID->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // Username
        if ($this->_Username->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // UserLevel
        if ($this->UserLevel->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // FirstName
        if ($this->FirstName->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // LastName
        if ($this->LastName->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // CompleteName
        if ($this->CompleteName->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // BirthDate
        if ($this->BirthDate->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // HomePhone
        if ($this->HomePhone->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // Photo
        if ($this->Photo->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // Notes
        if ($this->Notes->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // ReportsTo
        if ($this->ReportsTo->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // Gender
        if ($this->Gender->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // Email
        if ($this->_Email->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // Activated
        if ($this->Activated->AdvancedSearch->get()) {
            $hasValue = true;
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
        }

        // ActiveStatus
        if ($this->ActiveStatus->AdvancedSearch->get()) {
            $hasValue = true;
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
        }

        // CreatedAt
        if ($this->CreatedAt->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // CreatedBy
        if ($this->CreatedBy->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // UpdatedAt
        if ($this->UpdatedAt->AdvancedSearch->get()) {
            $hasValue = true;
        }

        // UpdatedBy
        if ($this->UpdatedBy->AdvancedSearch->get()) {
            $hasValue = true;
        }
        return $hasValue;
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

            // UserID
            $this->_UserID->HrefValue = "";
            $this->_UserID->TooltipValue = "";

            // Username
            $this->_Username->HrefValue = "";
            $this->_Username->TooltipValue = "";

            // UserLevel
            $this->UserLevel->HrefValue = "";
            $this->UserLevel->TooltipValue = "";

            // FirstName
            $this->FirstName->HrefValue = "";
            $this->FirstName->TooltipValue = "";

            // LastName
            $this->LastName->HrefValue = "";
            $this->LastName->TooltipValue = "";

            // CompleteName
            $this->CompleteName->HrefValue = "";
            $this->CompleteName->TooltipValue = "";

            // BirthDate
            $this->BirthDate->HrefValue = "";
            $this->BirthDate->TooltipValue = "";

            // HomePhone
            $this->HomePhone->HrefValue = "";
            $this->HomePhone->TooltipValue = "";

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
                $this->Photo->LinkAttrs["data-rel"] = "users_x_Photo";
                $this->Photo->LinkAttrs->appendClass("ew-lightbox");
            }

            // Notes
            $this->Notes->HrefValue = "";
            $this->Notes->TooltipValue = "";

            // ReportsTo
            $this->ReportsTo->HrefValue = "";
            $this->ReportsTo->TooltipValue = "";

            // Gender
            $this->Gender->HrefValue = "";
            $this->Gender->TooltipValue = "";

            // Email
            $this->_Email->HrefValue = "";
            $this->_Email->TooltipValue = "";

            // Activated
            $this->Activated->HrefValue = "";
            $this->Activated->TooltipValue = "";

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
            $this->Avatar->TooltipValue = "";
            if ($this->Avatar->UseColorbox) {
                if (IsEmpty($this->Avatar->TooltipValue)) {
                    $this->Avatar->LinkAttrs["title"] = $this->language->phrase("ViewImageGallery");
                }
                $this->Avatar->LinkAttrs["data-rel"] = "users_x_Avatar";
                $this->Avatar->LinkAttrs->appendClass("ew-lightbox");
            }

            // ActiveStatus
            $this->ActiveStatus->HrefValue = "";
            $this->ActiveStatus->TooltipValue = "";

            // MessengerColor
            $this->MessengerColor->HrefValue = "";
            $this->MessengerColor->TooltipValue = "";

            // CreatedAt
            $this->CreatedAt->HrefValue = "";
            $this->CreatedAt->TooltipValue = "";

            // CreatedBy
            $this->CreatedBy->HrefValue = "";
            $this->CreatedBy->TooltipValue = "";

            // UpdatedAt
            $this->UpdatedAt->HrefValue = "";
            $this->UpdatedAt->TooltipValue = "";

            // UpdatedBy
            $this->UpdatedBy->HrefValue = "";
            $this->UpdatedBy->TooltipValue = "";
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

            // FirstName
            $this->FirstName->setupEditAttributes();
            $this->FirstName->EditValue = !$this->FirstName->Raw ? HtmlDecode($this->FirstName->AdvancedSearch->SearchValue) : $this->FirstName->AdvancedSearch->SearchValue;
            $this->FirstName->PlaceHolder = RemoveHtml($this->FirstName->caption());

            // LastName
            $this->LastName->setupEditAttributes();
            $this->LastName->EditValue = !$this->LastName->Raw ? HtmlDecode($this->LastName->AdvancedSearch->SearchValue) : $this->LastName->AdvancedSearch->SearchValue;
            $this->LastName->PlaceHolder = RemoveHtml($this->LastName->caption());

            // CompleteName
            $this->CompleteName->setupEditAttributes();
            $this->CompleteName->EditValue = !$this->CompleteName->Raw ? HtmlDecode($this->CompleteName->AdvancedSearch->SearchValue) : $this->CompleteName->AdvancedSearch->SearchValue;
            $this->CompleteName->PlaceHolder = RemoveHtml($this->CompleteName->caption());

            // BirthDate
            $this->BirthDate->setupEditAttributes();
            $this->BirthDate->EditValue = FormatDateTime(UnFormatDateTime($this->BirthDate->AdvancedSearch->SearchValue, $this->BirthDate->formatPattern()), $this->BirthDate->formatPattern());
            $this->BirthDate->PlaceHolder = RemoveHtml($this->BirthDate->caption());
            $this->BirthDate->setupEditAttributes();
            $this->BirthDate->EditValue2 = FormatDateTime(UnFormatDateTime($this->BirthDate->AdvancedSearch->SearchValue2, $this->BirthDate->formatPattern()), $this->BirthDate->formatPattern());
            $this->BirthDate->PlaceHolder = RemoveHtml($this->BirthDate->caption());

            // HomePhone
            $this->HomePhone->setupEditAttributes();
            $this->HomePhone->EditValue = !$this->HomePhone->Raw ? HtmlDecode($this->HomePhone->AdvancedSearch->SearchValue) : $this->HomePhone->AdvancedSearch->SearchValue;
            $this->HomePhone->PlaceHolder = RemoveHtml($this->HomePhone->caption());

            // Photo
            $this->Photo->setupEditAttributes();
            $this->Photo->EditValue = !$this->Photo->Raw ? HtmlDecode($this->Photo->AdvancedSearch->SearchValue) : $this->Photo->AdvancedSearch->SearchValue;
            $this->Photo->PlaceHolder = RemoveHtml($this->Photo->caption());

            // Notes
            $this->Notes->setupEditAttributes();
            $this->Notes->EditValue = $this->Notes->AdvancedSearch->SearchValue;
            $this->Notes->PlaceHolder = RemoveHtml($this->Notes->caption());

            // ReportsTo
            $this->ReportsTo->setupEditAttributes();
            $this->ReportsTo->EditValue = $this->ReportsTo->AdvancedSearch->SearchValue;
            $this->ReportsTo->PlaceHolder = RemoveHtml($this->ReportsTo->caption());

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

            // Avatar
            $this->Avatar->setupEditAttributes();
            $this->Avatar->EditValue = !$this->Avatar->Raw ? HtmlDecode($this->Avatar->AdvancedSearch->SearchValue) : $this->Avatar->AdvancedSearch->SearchValue;
            $this->Avatar->PlaceHolder = RemoveHtml($this->Avatar->caption());

            // ActiveStatus
            $this->ActiveStatus->EditValue = $this->ActiveStatus->options(false);
            $this->ActiveStatus->PlaceHolder = RemoveHtml($this->ActiveStatus->caption());

            // MessengerColor
            $this->MessengerColor->setupEditAttributes();
            $this->MessengerColor->EditValue = !$this->MessengerColor->Raw ? HtmlDecode($this->MessengerColor->AdvancedSearch->SearchValue) : $this->MessengerColor->AdvancedSearch->SearchValue;
            $this->MessengerColor->PlaceHolder = RemoveHtml($this->MessengerColor->caption());

            // CreatedAt
            $this->CreatedAt->setupEditAttributes();
            $this->CreatedAt->EditValue = FormatDateTime(UnFormatDateTime($this->CreatedAt->AdvancedSearch->SearchValue, $this->CreatedAt->formatPattern()), $this->CreatedAt->formatPattern());
            $this->CreatedAt->PlaceHolder = RemoveHtml($this->CreatedAt->caption());
            $this->CreatedAt->setupEditAttributes();
            $this->CreatedAt->EditValue2 = FormatDateTime(UnFormatDateTime($this->CreatedAt->AdvancedSearch->SearchValue2, $this->CreatedAt->formatPattern()), $this->CreatedAt->formatPattern());
            $this->CreatedAt->PlaceHolder = RemoveHtml($this->CreatedAt->caption());

            // CreatedBy
            $this->CreatedBy->setupEditAttributes();
            $curVal = trim(strval($this->CreatedBy->AdvancedSearch->SearchValue));
            if ($curVal != "") {
                $this->CreatedBy->AdvancedSearch->ViewValue = $this->CreatedBy->lookupCacheOption($curVal);
            } else {
                $this->CreatedBy->AdvancedSearch->ViewValue = $this->CreatedBy->Lookup !== null && is_array($this->CreatedBy->lookupOptions()) && count($this->CreatedBy->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->CreatedBy->AdvancedSearch->ViewValue !== null) { // Load from cache
                $this->CreatedBy->EditValue = array_values($this->CreatedBy->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->CreatedBy->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $this->CreatedBy->AdvancedSearch->SearchValue, $this->CreatedBy->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
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
                    $this->CreatedBy->AdvancedSearch->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->CreatedBy->EditValue = $rows;
            }
            $this->CreatedBy->PlaceHolder = RemoveHtml($this->CreatedBy->caption());

            // UpdatedAt
            $this->UpdatedAt->setupEditAttributes();
            $this->UpdatedAt->EditValue = FormatDateTime(UnFormatDateTime($this->UpdatedAt->AdvancedSearch->SearchValue, $this->UpdatedAt->formatPattern()), $this->UpdatedAt->formatPattern());
            $this->UpdatedAt->PlaceHolder = RemoveHtml($this->UpdatedAt->caption());
            $this->UpdatedAt->setupEditAttributes();
            $this->UpdatedAt->EditValue2 = FormatDateTime(UnFormatDateTime($this->UpdatedAt->AdvancedSearch->SearchValue2, $this->UpdatedAt->formatPattern()), $this->UpdatedAt->formatPattern());
            $this->UpdatedAt->PlaceHolder = RemoveHtml($this->UpdatedAt->caption());

            // UpdatedBy
            $this->UpdatedBy->setupEditAttributes();
            $curVal = trim(strval($this->UpdatedBy->AdvancedSearch->SearchValue));
            if ($curVal != "") {
                $this->UpdatedBy->AdvancedSearch->ViewValue = $this->UpdatedBy->lookupCacheOption($curVal);
            } else {
                $this->UpdatedBy->AdvancedSearch->ViewValue = $this->UpdatedBy->Lookup !== null && is_array($this->UpdatedBy->lookupOptions()) && count($this->UpdatedBy->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->UpdatedBy->AdvancedSearch->ViewValue !== null) { // Load from cache
                $this->UpdatedBy->EditValue = array_values($this->UpdatedBy->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->UpdatedBy->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $this->UpdatedBy->AdvancedSearch->SearchValue, $this->UpdatedBy->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
                }
                $sqlWrk = $this->UpdatedBy->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                $conn = Conn();
                $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                $ari = count($rswrk);
                $rows = [];
                if ($ari > 0) { // Lookup values found
                    foreach ($rswrk as $row) {
                        $rows[] = $this->UpdatedBy->Lookup->renderViewRow($row);
                    }
                } else {
                    $this->UpdatedBy->AdvancedSearch->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->UpdatedBy->EditValue = $rows;
            }
            $this->UpdatedBy->PlaceHolder = RemoveHtml($this->UpdatedBy->caption());
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
        if (!CheckInteger($this->_UserID->AdvancedSearch->SearchValue)) {
            $this->_UserID->addErrorMessage($this->_UserID->getErrorMessage(false));
        }
        if (!CheckDate($this->BirthDate->AdvancedSearch->SearchValue, $this->BirthDate->formatPattern())) {
            $this->BirthDate->addErrorMessage($this->BirthDate->getErrorMessage(false));
        }
        if (!CheckDate($this->BirthDate->AdvancedSearch->SearchValue2, $this->BirthDate->formatPattern())) {
            $this->BirthDate->addErrorMessage($this->BirthDate->getErrorMessage(false));
        }
        if (!CheckInteger($this->ReportsTo->AdvancedSearch->SearchValue)) {
            $this->ReportsTo->addErrorMessage($this->ReportsTo->getErrorMessage(false));
        }
        if (!CheckDate($this->CreatedAt->AdvancedSearch->SearchValue, $this->CreatedAt->formatPattern())) {
            $this->CreatedAt->addErrorMessage($this->CreatedAt->getErrorMessage(false));
        }
        if (!CheckDate($this->CreatedAt->AdvancedSearch->SearchValue2, $this->CreatedAt->formatPattern())) {
            $this->CreatedAt->addErrorMessage($this->CreatedAt->getErrorMessage(false));
        }
        if (!CheckDate($this->UpdatedAt->AdvancedSearch->SearchValue, $this->UpdatedAt->formatPattern())) {
            $this->UpdatedAt->addErrorMessage($this->UpdatedAt->getErrorMessage(false));
        }
        if (!CheckDate($this->UpdatedAt->AdvancedSearch->SearchValue2, $this->UpdatedAt->formatPattern())) {
            $this->UpdatedAt->addErrorMessage($this->UpdatedAt->getErrorMessage(false));
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

    // Set up Breadcrumb
    protected function setupBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb();
        $url = CurrentUrl();
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("userslist"), "", $this->TableVar, true);
        $pageId = "search";
        $breadcrumb->add("search", $pageId, $url);
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
