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
class DispositionsEdit extends Dispositions
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "edit";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "DispositionsEdit";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "dispositionsedit";

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
        $this->disposition_id->setVisibility();
        $this->letter_id->setVisibility();
        $this->dari_unit_id->setVisibility();
        $this->ke_unit_id->setVisibility();
        $this->catatan->setVisibility();
        $this->status->setVisibility();
        $this->created_at->setVisibility();
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'dispositions';
        $this->TableName = 'dispositions';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-edit-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (dispositions)
        if (!isset($GLOBALS["dispositions"]) || $GLOBALS["dispositions"]::class == PROJECT_NAMESPACE . "dispositions") {
            $GLOBALS["dispositions"] = &$this;
        }

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'dispositions');
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
                        $result["view"] = SameString($pageName, "dispositionsview"); // If View page, no primary button
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
            $key .= @$ar['disposition_id'];
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
            $this->disposition_id->Visible = false;
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
        $this->setupLookupOptions($this->letter_id);
        $this->setupLookupOptions($this->dari_unit_id);
        $this->setupLookupOptions($this->ke_unit_id);
        $this->setupLookupOptions($this->status);

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
            if (($keyValue = Get("disposition_id") ?? Key(0) ?? Route(2)) !== null) {
                $this->disposition_id->setQueryStringValue($keyValue);
                $this->disposition_id->setOldValue($this->disposition_id->QueryStringValue);
            } elseif (Post("disposition_id") !== null) {
                $this->disposition_id->setFormValue(Post("disposition_id"));
                $this->disposition_id->setOldValue($this->disposition_id->FormValue);
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
                if (($keyValue = Get("disposition_id") ?? Route("disposition_id")) !== null) {
                    $this->disposition_id->setQueryStringValue($keyValue);
                    $loadByQuery = true;
                } else {
                    $this->disposition_id->CurrentValue = null;
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
                        $this->terminate("dispositionslist"); // No matching record, return to list
                        return;
                    }
                break;
            case "update": // Update
                $returnUrl = $this->getReturnUrl();
                if (GetPageName($returnUrl) == "dispositionslist") {
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
                        if (GetPageName($returnUrl) != "dispositionslist") {
                            FlashBag()->add("Return-Url", $returnUrl); // Save return URL
                            $returnUrl = "dispositionslist"; // Return list page content
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

        // Check field name 'disposition_id' before field var 'x_disposition_id'
        $val = $this->getFormValue("disposition_id", null) ?? $this->getFormValue("x_disposition_id", null);
        if (!$this->disposition_id->IsDetailKey) {
            $this->disposition_id->setFormValue($val);
        }

        // Check field name 'letter_id' before field var 'x_letter_id'
        $val = $this->getFormValue("letter_id", null) ?? $this->getFormValue("x_letter_id", null);
        if (!$this->letter_id->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->letter_id->Visible = false; // Disable update for API request
            } else {
                $this->letter_id->setFormValue($val);
            }
        }

        // Check field name 'dari_unit_id' before field var 'x_dari_unit_id'
        $val = $this->getFormValue("dari_unit_id", null) ?? $this->getFormValue("x_dari_unit_id", null);
        if (!$this->dari_unit_id->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->dari_unit_id->Visible = false; // Disable update for API request
            } else {
                $this->dari_unit_id->setFormValue($val, true, $validate);
            }
        }

        // Check field name 'ke_unit_id' before field var 'x_ke_unit_id'
        $val = $this->getFormValue("ke_unit_id", null) ?? $this->getFormValue("x_ke_unit_id", null);
        if (!$this->ke_unit_id->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->ke_unit_id->Visible = false; // Disable update for API request
            } else {
                $this->ke_unit_id->setFormValue($val, true, $validate);
            }
        }

        // Check field name 'catatan' before field var 'x_catatan'
        $val = $this->getFormValue("catatan", null) ?? $this->getFormValue("x_catatan", null);
        if (!$this->catatan->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->catatan->Visible = false; // Disable update for API request
            } else {
                $this->catatan->setFormValue($val);
            }
        }

        // Check field name 'status' before field var 'x_status'
        $val = $this->getFormValue("status", null) ?? $this->getFormValue("x_status", null);
        if (!$this->status->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->status->Visible = false; // Disable update for API request
            } else {
                $this->status->setFormValue($val);
            }
        }

        // Check field name 'created_at' before field var 'x_created_at'
        $val = $this->getFormValue("created_at", null) ?? $this->getFormValue("x_created_at", null);
        if (!$this->created_at->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->created_at->Visible = false; // Disable update for API request
            } else {
                $this->created_at->setFormValue($val);
            }
            $this->created_at->CurrentValue = UnformatDateTime($this->created_at->CurrentValue, $this->created_at->formatPattern());
        }
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        $this->disposition_id->CurrentValue = $this->disposition_id->FormValue;
        $this->letter_id->CurrentValue = $this->letter_id->FormValue;
        $this->dari_unit_id->CurrentValue = $this->dari_unit_id->FormValue;
        $this->ke_unit_id->CurrentValue = $this->ke_unit_id->FormValue;
        $this->catatan->CurrentValue = $this->catatan->FormValue;
        $this->status->CurrentValue = $this->status->FormValue;
        $this->created_at->CurrentValue = $this->created_at->FormValue;
        $this->created_at->CurrentValue = UnformatDateTime($this->created_at->CurrentValue, $this->created_at->formatPattern());
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
        $this->disposition_id->setDbValue($row['disposition_id']);
        $this->letter_id->setDbValue($row['letter_id']);
        $this->dari_unit_id->setDbValue($row['dari_unit_id']);
        $this->ke_unit_id->setDbValue($row['ke_unit_id']);
        $this->catatan->setDbValue($row['catatan']);
        $this->status->setDbValue($row['status']);
        $this->created_at->setDbValue($row['created_at']);
    }

    // Return a row with default values
    protected function newRow(): array
    {
        $row = [];
        $row['disposition_id'] = $this->disposition_id->DefaultValue;
        $row['letter_id'] = $this->letter_id->DefaultValue;
        $row['dari_unit_id'] = $this->dari_unit_id->DefaultValue;
        $row['ke_unit_id'] = $this->ke_unit_id->DefaultValue;
        $row['catatan'] = $this->catatan->DefaultValue;
        $row['status'] = $this->status->DefaultValue;
        $row['created_at'] = $this->created_at->DefaultValue;
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

        // disposition_id
        $this->disposition_id->RowCssClass = "row";

        // letter_id
        $this->letter_id->RowCssClass = "row";

        // dari_unit_id
        $this->dari_unit_id->RowCssClass = "row";

        // ke_unit_id
        $this->ke_unit_id->RowCssClass = "row";

        // catatan
        $this->catatan->RowCssClass = "row";

        // status
        $this->status->RowCssClass = "row";

        // created_at
        $this->created_at->RowCssClass = "row";

        // View row
        if ($this->RowType == RowType::VIEW) {
            // disposition_id
            $this->disposition_id->ViewValue = $this->disposition_id->CurrentValue;

            // letter_id
            $curVal = strval($this->letter_id->CurrentValue);
            if ($curVal != "") {
                $this->letter_id->ViewValue = $this->letter_id->lookupCacheOption($curVal);
                if ($this->letter_id->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->letter_id->Lookup->getTable()->Fields["letter_id"]->searchExpression(), "=", $curVal, $this->letter_id->Lookup->getTable()->Fields["letter_id"]->searchDataType(), "DB");
                    $sqlWrk = $this->letter_id->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->letter_id->Lookup->renderViewRow($row);
                        }
                        $this->letter_id->ViewValue = $this->letter_id->displayValue($rows[0]);
                    } else {
                        $this->letter_id->ViewValue = FormatNumber($this->letter_id->CurrentValue, $this->letter_id->formatPattern());
                    }
                }
            } else {
                $this->letter_id->ViewValue = null;
            }

            // dari_unit_id
            $this->dari_unit_id->ViewValue = $this->dari_unit_id->CurrentValue;
            $curVal = strval($this->dari_unit_id->CurrentValue);
            if ($curVal != "") {
                $this->dari_unit_id->ViewValue = $this->dari_unit_id->lookupCacheOption($curVal);
                if ($this->dari_unit_id->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->dari_unit_id->Lookup->getTable()->Fields["unit_id"]->searchExpression(), "=", $curVal, $this->dari_unit_id->Lookup->getTable()->Fields["unit_id"]->searchDataType(), "DB");
                    $sqlWrk = $this->dari_unit_id->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->dari_unit_id->Lookup->renderViewRow($row);
                        }
                        $this->dari_unit_id->ViewValue = $this->dari_unit_id->displayValue($rows[0]);
                    } else {
                        $this->dari_unit_id->ViewValue = FormatNumber($this->dari_unit_id->CurrentValue, $this->dari_unit_id->formatPattern());
                    }
                }
            } else {
                $this->dari_unit_id->ViewValue = null;
            }

            // ke_unit_id
            $this->ke_unit_id->ViewValue = $this->ke_unit_id->CurrentValue;
            $curVal = strval($this->ke_unit_id->CurrentValue);
            if ($curVal != "") {
                $this->ke_unit_id->ViewValue = $this->ke_unit_id->lookupCacheOption($curVal);
                if ($this->ke_unit_id->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->ke_unit_id->Lookup->getTable()->Fields["unit_id"]->searchExpression(), "=", $curVal, $this->ke_unit_id->Lookup->getTable()->Fields["unit_id"]->searchDataType(), "DB");
                    $sqlWrk = $this->ke_unit_id->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->ke_unit_id->Lookup->renderViewRow($row);
                        }
                        $this->ke_unit_id->ViewValue = $this->ke_unit_id->displayValue($rows[0]);
                    } else {
                        $this->ke_unit_id->ViewValue = FormatNumber($this->ke_unit_id->CurrentValue, $this->ke_unit_id->formatPattern());
                    }
                }
            } else {
                $this->ke_unit_id->ViewValue = null;
            }

            // catatan
            $this->catatan->ViewValue = $this->catatan->CurrentValue;

            // status
            if (strval($this->status->CurrentValue) != "") {
                $this->status->ViewValue = $this->status->optionCaption($this->status->CurrentValue);
            } else {
                $this->status->ViewValue = null;
            }

            // created_at
            $this->created_at->ViewValue = $this->created_at->CurrentValue;
            $this->created_at->ViewValue = FormatDateTime($this->created_at->ViewValue, $this->created_at->formatPattern());

            // disposition_id
            $this->disposition_id->HrefValue = "";

            // letter_id
            $this->letter_id->HrefValue = "";

            // dari_unit_id
            $this->dari_unit_id->HrefValue = "";

            // ke_unit_id
            $this->ke_unit_id->HrefValue = "";

            // catatan
            $this->catatan->HrefValue = "";

            // status
            $this->status->HrefValue = "";

            // created_at
            $this->created_at->HrefValue = "";
        } elseif ($this->RowType == RowType::EDIT) {
            // disposition_id
            $this->disposition_id->setupEditAttributes();
            $this->disposition_id->EditValue = $this->disposition_id->CurrentValue;

            // letter_id
            $this->letter_id->setupEditAttributes();
            $curVal = trim(strval($this->letter_id->CurrentValue));
            if ($curVal != "") {
                $this->letter_id->ViewValue = $this->letter_id->lookupCacheOption($curVal);
            } else {
                $this->letter_id->ViewValue = $this->letter_id->Lookup !== null && is_array($this->letter_id->lookupOptions()) && count($this->letter_id->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->letter_id->ViewValue !== null) { // Load from cache
                $this->letter_id->EditValue = array_values($this->letter_id->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->letter_id->Lookup->getTable()->Fields["letter_id"]->searchExpression(), "=", $this->letter_id->CurrentValue, $this->letter_id->Lookup->getTable()->Fields["letter_id"]->searchDataType(), "DB");
                }
                $sqlWrk = $this->letter_id->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                $conn = Conn();
                $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                $ari = count($rswrk);
                $rows = [];
                if ($ari > 0) { // Lookup values found
                    foreach ($rswrk as $row) {
                        $rows[] = $this->letter_id->Lookup->renderViewRow($row);
                    }
                } else {
                    $this->letter_id->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->letter_id->EditValue = $rows;
            }
            $this->letter_id->PlaceHolder = RemoveHtml($this->letter_id->caption());

            // dari_unit_id
            $this->dari_unit_id->setupEditAttributes();
            $this->dari_unit_id->EditValue = $this->dari_unit_id->CurrentValue;
            $this->dari_unit_id->EditValue = RemoveHtml($this->dari_unit_id->EditValue);
            $curVal = strval($this->dari_unit_id->CurrentValue);
            if ($curVal != "") {
                $this->dari_unit_id->EditValue = $this->dari_unit_id->lookupCacheOption($curVal);
                if ($this->dari_unit_id->EditValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->dari_unit_id->Lookup->getTable()->Fields["unit_id"]->searchExpression(), "=", $curVal, $this->dari_unit_id->Lookup->getTable()->Fields["unit_id"]->searchDataType(), "DB");
                    $sqlWrk = $this->dari_unit_id->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->dari_unit_id->Lookup->renderViewRow($row);
                        }
                        $this->dari_unit_id->EditValue = $this->dari_unit_id->displayValue($rows[0]);
                    } else {
                        $this->dari_unit_id->EditValue = FormatNumber($this->dari_unit_id->CurrentValue, $this->dari_unit_id->formatPattern());
                    }
                }
            } else {
                $this->dari_unit_id->EditValue = null;
            }
            $this->dari_unit_id->PlaceHolder = RemoveHtml($this->dari_unit_id->caption());

            // ke_unit_id
            $this->ke_unit_id->setupEditAttributes();
            $this->ke_unit_id->EditValue = $this->ke_unit_id->CurrentValue;
            $this->ke_unit_id->EditValue = RemoveHtml($this->ke_unit_id->EditValue);
            $curVal = strval($this->ke_unit_id->CurrentValue);
            if ($curVal != "") {
                $this->ke_unit_id->EditValue = $this->ke_unit_id->lookupCacheOption($curVal);
                if ($this->ke_unit_id->EditValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->ke_unit_id->Lookup->getTable()->Fields["unit_id"]->searchExpression(), "=", $curVal, $this->ke_unit_id->Lookup->getTable()->Fields["unit_id"]->searchDataType(), "DB");
                    $sqlWrk = $this->ke_unit_id->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->ke_unit_id->Lookup->renderViewRow($row);
                        }
                        $this->ke_unit_id->EditValue = $this->ke_unit_id->displayValue($rows[0]);
                    } else {
                        $this->ke_unit_id->EditValue = FormatNumber($this->ke_unit_id->CurrentValue, $this->ke_unit_id->formatPattern());
                    }
                }
            } else {
                $this->ke_unit_id->EditValue = null;
            }
            $this->ke_unit_id->PlaceHolder = RemoveHtml($this->ke_unit_id->caption());

            // catatan
            $this->catatan->setupEditAttributes();
            $this->catatan->EditValue = !$this->catatan->Raw ? HtmlDecode($this->catatan->CurrentValue) : $this->catatan->CurrentValue;
            $this->catatan->PlaceHolder = RemoveHtml($this->catatan->caption());

            // status
            $this->status->EditValue = $this->status->options(false);
            $this->status->PlaceHolder = RemoveHtml($this->status->caption());

            // created_at

            // Edit refer script

            // disposition_id
            $this->disposition_id->HrefValue = "";

            // letter_id
            $this->letter_id->HrefValue = "";

            // dari_unit_id
            $this->dari_unit_id->HrefValue = "";

            // ke_unit_id
            $this->ke_unit_id->HrefValue = "";

            // catatan
            $this->catatan->HrefValue = "";

            // status
            $this->status->HrefValue = "";

            // created_at
            $this->created_at->HrefValue = "";
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
            if ($this->disposition_id->Visible && $this->disposition_id->Required) {
                if (!$this->disposition_id->IsDetailKey && IsEmpty($this->disposition_id->FormValue)) {
                    $this->disposition_id->addErrorMessage(str_replace("%s", $this->disposition_id->caption(), $this->disposition_id->RequiredErrorMessage));
                }
            }
            if ($this->letter_id->Visible && $this->letter_id->Required) {
                if (!$this->letter_id->IsDetailKey && IsEmpty($this->letter_id->FormValue)) {
                    $this->letter_id->addErrorMessage(str_replace("%s", $this->letter_id->caption(), $this->letter_id->RequiredErrorMessage));
                }
            }
            if ($this->dari_unit_id->Visible && $this->dari_unit_id->Required) {
                if (!$this->dari_unit_id->IsDetailKey && IsEmpty($this->dari_unit_id->FormValue)) {
                    $this->dari_unit_id->addErrorMessage(str_replace("%s", $this->dari_unit_id->caption(), $this->dari_unit_id->RequiredErrorMessage));
                }
            }
            if (!CheckInteger($this->dari_unit_id->FormValue)) {
                $this->dari_unit_id->addErrorMessage($this->dari_unit_id->getErrorMessage(false));
            }
            if ($this->ke_unit_id->Visible && $this->ke_unit_id->Required) {
                if (!$this->ke_unit_id->IsDetailKey && IsEmpty($this->ke_unit_id->FormValue)) {
                    $this->ke_unit_id->addErrorMessage(str_replace("%s", $this->ke_unit_id->caption(), $this->ke_unit_id->RequiredErrorMessage));
                }
            }
            if (!CheckInteger($this->ke_unit_id->FormValue)) {
                $this->ke_unit_id->addErrorMessage($this->ke_unit_id->getErrorMessage(false));
            }
            if ($this->catatan->Visible && $this->catatan->Required) {
                if (!$this->catatan->IsDetailKey && IsEmpty($this->catatan->FormValue)) {
                    $this->catatan->addErrorMessage(str_replace("%s", $this->catatan->caption(), $this->catatan->RequiredErrorMessage));
                }
            }
            if ($this->status->Visible && $this->status->Required) {
                if ($this->status->FormValue == "") {
                    $this->status->addErrorMessage(str_replace("%s", $this->status->caption(), $this->status->RequiredErrorMessage));
                }
            }
            if ($this->created_at->Visible && $this->created_at->Required) {
                if (!$this->created_at->IsDetailKey && IsEmpty($this->created_at->FormValue)) {
                    $this->created_at->addErrorMessage(str_replace("%s", $this->created_at->caption(), $this->created_at->RequiredErrorMessage));
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

        // letter_id
        $this->letter_id->setDbValueDef($newRow, $this->letter_id->CurrentValue, $this->letter_id->ReadOnly);

        // dari_unit_id
        $this->dari_unit_id->setDbValueDef($newRow, $this->dari_unit_id->CurrentValue, $this->dari_unit_id->ReadOnly);

        // ke_unit_id
        $this->ke_unit_id->setDbValueDef($newRow, $this->ke_unit_id->CurrentValue, $this->ke_unit_id->ReadOnly);

        // catatan
        $this->catatan->setDbValueDef($newRow, $this->catatan->CurrentValue, $this->catatan->ReadOnly);

        // status
        $this->status->setDbValueDef($newRow, $this->status->CurrentValue, $this->status->ReadOnly);

        // created_at
        $this->created_at->CurrentValue = $this->created_at->getAutoUpdateValue(); // PHP
        $this->created_at->setDbValueDef($newRow, UnFormatDateTime($this->created_at->CurrentValue, $this->created_at->formatPattern()), $this->created_at->ReadOnly);
        return $newRow;
    }

    /**
     * Restore edit form from row
     * @param array $row Row
     */
    protected function restoreEditFormFromRow(array $row): void
    {
        if (isset($row['letter_id'])) { // letter_id
            $this->letter_id->CurrentValue = $row['letter_id'];
        }
        if (isset($row['dari_unit_id'])) { // dari_unit_id
            $this->dari_unit_id->CurrentValue = $row['dari_unit_id'];
        }
        if (isset($row['ke_unit_id'])) { // ke_unit_id
            $this->ke_unit_id->CurrentValue = $row['ke_unit_id'];
        }
        if (isset($row['catatan'])) { // catatan
            $this->catatan->CurrentValue = $row['catatan'];
        }
        if (isset($row['status'])) { // status
            $this->status->CurrentValue = $row['status'];
        }
        if (isset($row['created_at'])) { // created_at
            $this->created_at->CurrentValue = $row['created_at'];
        }
    }

    // Set up Breadcrumb
    protected function setupBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb();
        $url = CurrentUrl();
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("dispositionslist"), "", $this->TableVar, true);
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
                case "x_letter_id":
                    break;
                case "x_dari_unit_id":
                    break;
                case "x_ke_unit_id":
                    break;
                case "x_status":
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

    // Form Custom Validate event
    public function formCustomValidate(string &$customError): bool
    {
        // Return error message in $customError
        return true;
    }
}
