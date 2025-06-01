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
class UsersView extends Users
{
    use MessagesTrait;

    // Page ID
    public string $PageID = "view";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "UsersView";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "usersview";

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
        $this->_Profile->setVisibility();
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
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-view-table";

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

        // Set up record key
        if (($keyValue = Get("_UserID") ?? Route("_UserID")) !== null) {
            $this->RecKey["_UserID"] = $keyValue;
        }

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'users');
        }

        // Open connection
        $GLOBALS["Conn"] ??= $this->getConnection();

        // Export options
        $this->ExportOptions = new ListOptions(TagClassName: "ew-export-option");

        // Other options
        $this->OtherOptions = new ListOptionsCollection();

        // Detail tables
        $this->OtherOptions["detail"] = new ListOptions(TagClassName: "ew-detail-option");
        // Actions
        $this->OtherOptions["action"] = new ListOptions(TagClassName: "ew-action-option");
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
    public ?ListOptions $ExportOptions = null; // Export options
    public ?ListOptionsCollection $OtherOptions = null; // Other options
    public int $DisplayRecords = 1;
    public ?string $DbMasterFilter = ""; // Master filter
    public string $DbDetailFilter = "";
    public int $StartRecord = 0;
    public int $StopRecord = 0;
    public int $TotalRecords = 0;
    public int $RecordRange = 10;
    public array $RecKey = [];
    public bool $IsModal = false;

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

        // Check modal
        if ($this->IsModal) {
            $SkipHeaderFooter = true;
        }

        // Load current record
        $loadCurrentRecord = false;
        $returnUrl = "";
        $matchRecord = false;

        // Set up master/detail parameters
        $this->setupMasterParms();

        // Paging for view page (BUT not for detail grid)
        if (Get(Config("TABLE_PAGER_TABLE_NAME")) === null && (Get(Config("TABLE_START_REC")) !== null || Get(Config("TABLE_PAGE_NUMBER")) !== null)) {
            $loadCurrentRecord = true;
        }
        if (($keyValue = Get("_UserID") ?? Route("_UserID")) !== null) {
            $this->_UserID->setQueryStringValue($keyValue);
            $this->RecKey["_UserID"] = $this->_UserID->QueryStringValue;
        } elseif (Post("_UserID") !== null) {
            $this->_UserID->setFormValue(Post("_UserID"));
            $this->RecKey["_UserID"] = $this->_UserID->FormValue;
        } elseif (IsApi() && ($keyValue = Key(0) ?? Route(2)) !== null) {
            $this->_UserID->setQueryStringValue($keyValue);
            $this->RecKey["_UserID"] = $this->_UserID->QueryStringValue;
        } elseif (!$loadCurrentRecord) {
            $returnUrl = "userslist"; // Return to list
        }

        // Get action
        $this->CurrentAction = "show"; // Display
        switch ($this->CurrentAction) {
            case "show": // Get a record to display
                if (!$this->IsModal && !IsApi()) { // Normal view page
                    $this->StartRecord = 1; // Initialize start position
                    $this->Result = $this->loadResult(); // Load records
                    if ($this->TotalRecords <= 0) { // No record found
                        if (!$this->peekSuccessMessage() && !$this->peekFailureMessage()) {
                            $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
                        }
                        $this->terminate("userslist"); // Return to list page
                        return;
                    } elseif ($loadCurrentRecord) { // Load current record position
                        $this->setupStartRecord(); // Set up start record position
                        // Point to current record
                        if ($this->StartRecord <= $this->TotalRecords) {
                            $matchRecord = true;
                            $this->fetch($this->StartRecord);
                            // Redirect to correct record
                            $this->loadRowValues($this->CurrentRow);
                            $url = $this->getCurrentUrl();
                            $this->terminate($url);
                            return;
                        }
                    } else { // Match key values
                        while ($this->fetch()) {
                            if (SameString($this->_UserID->CurrentValue, $this->CurrentRow['UserID'])) {
                                $this->setStartRecordNumber($this->StartRecord); // Save record position
                                $matchRecord = true;
                                break;
                            } else {
                                $this->StartRecord++;
                            }
                        }
                    }
                    if (!$matchRecord) {
                        if (!$this->peekSuccessMessage() && !$this->peekFailureMessage()) {
                            $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
                        }
                        $returnUrl = "userslist"; // No matching record, return to list
                    } else {
                        $this->loadRowValues($this->CurrentRow); // Load row values
                    }
                } else {
                    // Load record based on key
                    if (IsApi()) {
                        $filter = $this->getRecordFilter();
                        $this->CurrentFilter = $filter;
                        $sql = $this->getCurrentSql();
                        $conn = $this->getConnection();
                        $res = ($this->Result = ExecuteQuery($sql, $conn));
                    } else {
                        $res = $this->loadRow();
                    }
                    if (!$res) { // Load record based on key
                        if (!$this->peekSuccessMessage() && !$this->peekFailureMessage()) {
                            $this->setFailureMessage($this->language->phrase("NoRecord")); // Set no record message
                        }
                        $returnUrl = "userslist"; // No matching record, return to list
                    }
                } // End modal checking
                break;
        }
        if ($returnUrl != "") {
            $this->terminate($returnUrl);
            return;
        }

        // Set up Breadcrumb
        if (!$this->isExport()) {
            $this->setupBreadcrumb();
        }

        // Render row
        $this->RowType = RowType::VIEW;
        $this->resetAttributes();
        $this->renderRow();

        // Normal return
        if (IsApi()) {
            if (!$this->isExport()) {
                $row = $this->getRecordsFromResult($this->Result, true); // Get current record only
                $this->Result?->free();
                WriteJson(["success" => true, "action" => Config("API_VIEW_ACTION"), $this->TableVar => $row]);
                $this->terminate(true);
            }
            return;
        }

        // Set up pager
        if (!$this->IsModal) { // Normal view page
            $this->Pager = new PrevNextPager($this, $this->StartRecord, $this->DisplayRecords, $this->TotalRecords, "", $this->RecordRange, $this->AutoHidePager, false, false);
            $this->Pager->PageNumberName = Config("TABLE_PAGE_NUMBER");
            $this->Pager->PagePhraseId = "Record"; // Show as record
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

    // Set up other options
    protected function setupOtherOptions(): void
    {
        // Disable Add/Edit/Copy/Delete for Modal and UseAjaxActions
        /*
        if ($this->IsModal && $this->UseAjaxActions) {
            $this->AddUrl = "";
            $this->EditUrl = "";
            $this->CopyUrl = "";
            $this->DeleteUrl = "";
        }
        */
        $options = &$this->OtherOptions;
        $option = $options["action"];

        // Add
        $item = &$option->add("add");
        $addcaption = HtmlTitle($this->language->phrase("ViewPageAddLink"));
        if ($this->IsModal) {
            $item->Body = "<a class=\"ew-action ew-add\" title=\"" . $addcaption . "\" data-caption=\"" . $addcaption . "\" data-ew-action=\"modal\" data-ask=\"1\" data-url=\"" . HtmlEncode(GetUrl($this->AddUrl)) . "\">" . $this->language->phrase("ViewPageAddLink") . "</a>";
        } else {
            $item->Body = "<a class=\"ew-action ew-add\" title=\"" . $addcaption . "\" data-caption=\"" . $addcaption . "\" href=\"" . HtmlEncode(GetUrl($this->AddUrl)) . "\">" . $this->language->phrase("ViewPageAddLink") . "</a>";
        }
        $item->Visible = $this->AddUrl != "" && $this->security->canAdd();

        // Edit
        $item = &$option->add("edit");
        $editcaption = HtmlTitle($this->language->phrase("ViewPageEditLink"));
        if ($this->IsModal) {
            $item->Body = "<a class=\"ew-action ew-edit\" title=\"" . $editcaption . "\" data-caption=\"" . $editcaption . "\" data-ew-action=\"modal\" data-ask=\"1\" data-url=\"" . HtmlEncode(GetUrl($this->EditUrl)) . "\">" . $this->language->phrase("ViewPageEditLink") . "</a>";
        } else {
            $item->Body = "<a class=\"ew-action ew-edit\" title=\"" . $editcaption . "\" data-caption=\"" . $editcaption . "\" href=\"" . HtmlEncode(GetUrl($this->EditUrl)) . "\">" . $this->language->phrase("ViewPageEditLink") . "</a>";
        }
        $item->Visible = $this->EditUrl != "" && $this->security->canEdit() && $this->showOptionLink("edit");

        // Copy
        $item = &$option->add("copy");
        $copycaption = HtmlTitle($this->language->phrase("ViewPageCopyLink"));
        if ($this->IsModal) {
            $item->Body = "<a class=\"ew-action ew-copy\" title=\"" . $copycaption . "\" data-caption=\"" . $copycaption . "\" data-ew-action=\"modal\" data-ask=\"1\" data-url=\"" . HtmlEncode(GetUrl($this->CopyUrl)) . "\" data-btn=\"AddBtn\">" . $this->language->phrase("ViewPageCopyLink") . "</a>";
        } else {
            $item->Body = "<a class=\"ew-action ew-copy\" title=\"" . $copycaption . "\" data-caption=\"" . $copycaption . "\" href=\"" . HtmlEncode(GetUrl($this->CopyUrl)) . "\">" . $this->language->phrase("ViewPageCopyLink") . "</a>";
        }
        $item->Visible = $this->CopyUrl != "" && $this->security->canAdd() && $this->showOptionLink("add");

        // Delete
        $item = &$option->add("delete");
        $url = GetUrl($this->DeleteUrl);
        $item->Body = "<a class=\"ew-action ew-delete\"" .
            ($this->InlineDelete || $this->IsModal ? " data-ew-action=\"inline-delete\"" : "") .
            " title=\"" . HtmlTitle($this->language->phrase("ViewPageDeleteLink")) . "\" data-caption=\"" . HtmlTitle($this->language->phrase("ViewPageDeleteLink")) .
            "\" href=\"" . HtmlEncode($url) . "\">" . $this->language->phrase("ViewPageDeleteLink") . "</a>";
        $item->Visible = $this->DeleteUrl != "" && $this->security->canDelete() && $this->showOptionLink("delete");

        // Set up action default
        $option = $options["action"];
        $option->DropDownButtonPhrase = $this->language->phrase("ButtonActions");
        $option->UseDropDownButton = !IsJsonResponse() && true;
        $option->UseButtonGroup = true;
        $item = &$option->addGroupOption();
        $item->Body = "";
        $item->Visible = false;
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

    // Render row values based on field settings
    public function renderRow(): void
    {
        global $CurrentLanguage;

        // Initialize URLs
        $this->AddUrl = $this->getAddUrl();
        $this->EditUrl = $this->getEditUrl();
        $this->CopyUrl = $this->getCopyUrl();
        $this->DeleteUrl = $this->getDeleteUrl();
        $this->ListUrl = $this->getListUrl();
        $this->setupOtherOptions();

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

            // Profile
            $this->_Profile->ViewValue = $this->_Profile->CurrentValue;

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

            // Profile
            $this->_Profile->HrefValue = "";
            $this->_Profile->TooltipValue = "";

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
        }

        // Call Row Rendered event
        if ($this->RowType != RowType::AGGREGATEINIT) {
            $this->rowRendered();
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
            $this->setSessionWhere($this->getDetailFilterFromSession());

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
        $pageId = "view";
        $breadcrumb->add("view", $pageId, $url);
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
}
