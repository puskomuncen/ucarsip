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
class AnnouncementEdit extends Announcement
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "edit";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "AnnouncementEdit";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "announcementedit";

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
        $this->Announcement_ID->setVisibility();
        $this->Is_Active->setVisibility();
        $this->Topic->setVisibility();
        $this->Message->setVisibility();
        $this->Date_LastUpdate->setVisibility();
        $this->_Language->setVisibility();
        $this->Auto_Publish->setVisibility();
        $this->Date_Start->setVisibility();
        $this->Date_End->setVisibility();
        $this->Date_Created->setVisibility();
        $this->Created_By->setVisibility();
        $this->Translated_ID->setVisibility();
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'announcement';
        $this->TableName = 'announcement';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-edit-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (announcement)
        if (!isset($GLOBALS["announcement"]) || $GLOBALS["announcement"]::class == PROJECT_NAMESPACE . "announcement") {
            $GLOBALS["announcement"] = &$this;
        }

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'announcement');
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
                        $result["view"] = SameString($pageName, "announcementview"); // If View page, no primary button
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
            $key .= @$ar['Announcement_ID'];
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
            $this->Announcement_ID->Visible = false;
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
        $this->setupLookupOptions($this->Is_Active);
        $this->setupLookupOptions($this->_Language);
        $this->setupLookupOptions($this->Auto_Publish);
        $this->setupLookupOptions($this->Created_By);
        $this->setupLookupOptions($this->Translated_ID);

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
            if (($keyValue = Get("Announcement_ID") ?? Key(0) ?? Route(2)) !== null) {
                $this->Announcement_ID->setQueryStringValue($keyValue);
                $this->Announcement_ID->setOldValue($this->Announcement_ID->QueryStringValue);
            } elseif (Post("Announcement_ID") !== null) {
                $this->Announcement_ID->setFormValue(Post("Announcement_ID"));
                $this->Announcement_ID->setOldValue($this->Announcement_ID->FormValue);
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
                if (($keyValue = Get("Announcement_ID") ?? Route("Announcement_ID")) !== null) {
                    $this->Announcement_ID->setQueryStringValue($keyValue);
                    $loadByQuery = true;
                } else {
                    $this->Announcement_ID->CurrentValue = null;
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
                        $this->terminate("announcementlist"); // No matching record, return to list
                        return;
                    }
                break;
            case "update": // Update
                $returnUrl = $this->getReturnUrl();
                if (GetPageName($returnUrl) == "announcementlist") {
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
                        if (GetPageName($returnUrl) != "announcementlist") {
                            FlashBag()->add("Return-Url", $returnUrl); // Save return URL
                            $returnUrl = "announcementlist"; // Return list page content
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

        // Check field name 'Announcement_ID' before field var 'x_Announcement_ID'
        $val = $this->getFormValue("Announcement_ID", null) ?? $this->getFormValue("x_Announcement_ID", null);
        if (!$this->Announcement_ID->IsDetailKey) {
            $this->Announcement_ID->setFormValue($val);
        }

        // Check field name 'Is_Active' before field var 'x_Is_Active'
        $val = $this->getFormValue("Is_Active", null) ?? $this->getFormValue("x_Is_Active", null);
        if (!$this->Is_Active->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Is_Active->Visible = false; // Disable update for API request
            } else {
                $this->Is_Active->setFormValue($val);
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

        // Check field name 'Message' before field var 'x_Message'
        $val = $this->getFormValue("Message", null) ?? $this->getFormValue("x_Message", null);
        if (!$this->Message->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Message->Visible = false; // Disable update for API request
            } else {
                $this->Message->setFormValue($val);
            }
        }

        // Check field name 'Date_LastUpdate' before field var 'x_Date_LastUpdate'
        $val = $this->getFormValue("Date_LastUpdate", null) ?? $this->getFormValue("x_Date_LastUpdate", null);
        if (!$this->Date_LastUpdate->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Date_LastUpdate->Visible = false; // Disable update for API request
            } else {
                $this->Date_LastUpdate->setFormValue($val, true, $validate);
            }
            $this->Date_LastUpdate->CurrentValue = UnformatDateTime($this->Date_LastUpdate->CurrentValue, $this->Date_LastUpdate->formatPattern());
        }

        // Check field name 'Language' before field var 'x__Language'
        $val = $this->getFormValue("Language", null) ?? $this->getFormValue("x__Language", null);
        if (!$this->_Language->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->_Language->Visible = false; // Disable update for API request
            } else {
                $this->_Language->setFormValue($val);
            }
        }

        // Check field name 'Auto_Publish' before field var 'x_Auto_Publish'
        $val = $this->getFormValue("Auto_Publish", null) ?? $this->getFormValue("x_Auto_Publish", null);
        if (!$this->Auto_Publish->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Auto_Publish->Visible = false; // Disable update for API request
            } else {
                $this->Auto_Publish->setFormValue($val);
            }
        }

        // Check field name 'Date_Start' before field var 'x_Date_Start'
        $val = $this->getFormValue("Date_Start", null) ?? $this->getFormValue("x_Date_Start", null);
        if (!$this->Date_Start->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Date_Start->Visible = false; // Disable update for API request
            } else {
                $this->Date_Start->setFormValue($val, true, $validate);
            }
            $this->Date_Start->CurrentValue = UnformatDateTime($this->Date_Start->CurrentValue, $this->Date_Start->formatPattern());
        }

        // Check field name 'Date_End' before field var 'x_Date_End'
        $val = $this->getFormValue("Date_End", null) ?? $this->getFormValue("x_Date_End", null);
        if (!$this->Date_End->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Date_End->Visible = false; // Disable update for API request
            } else {
                $this->Date_End->setFormValue($val, true, $validate);
            }
            $this->Date_End->CurrentValue = UnformatDateTime($this->Date_End->CurrentValue, $this->Date_End->formatPattern());
        }

        // Check field name 'Date_Created' before field var 'x_Date_Created'
        $val = $this->getFormValue("Date_Created", null) ?? $this->getFormValue("x_Date_Created", null);
        if (!$this->Date_Created->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Date_Created->Visible = false; // Disable update for API request
            } else {
                $this->Date_Created->setFormValue($val, true, $validate);
            }
            $this->Date_Created->CurrentValue = UnformatDateTime($this->Date_Created->CurrentValue, $this->Date_Created->formatPattern());
        }

        // Check field name 'Created_By' before field var 'x_Created_By'
        $val = $this->getFormValue("Created_By", null) ?? $this->getFormValue("x_Created_By", null);
        if (!$this->Created_By->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Created_By->Visible = false; // Disable update for API request
            } else {
                $this->Created_By->setFormValue($val);
            }
        }

        // Check field name 'Translated_ID' before field var 'x_Translated_ID'
        $val = $this->getFormValue("Translated_ID", null) ?? $this->getFormValue("x_Translated_ID", null);
        if (!$this->Translated_ID->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->Translated_ID->Visible = false; // Disable update for API request
            } else {
                $this->Translated_ID->setFormValue($val);
            }
        }
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        $this->Announcement_ID->CurrentValue = $this->Announcement_ID->FormValue;
        $this->Is_Active->CurrentValue = $this->Is_Active->FormValue;
        $this->Topic->CurrentValue = $this->Topic->FormValue;
        $this->Message->CurrentValue = $this->Message->FormValue;
        $this->Date_LastUpdate->CurrentValue = $this->Date_LastUpdate->FormValue;
        $this->Date_LastUpdate->CurrentValue = UnformatDateTime($this->Date_LastUpdate->CurrentValue, $this->Date_LastUpdate->formatPattern());
        $this->_Language->CurrentValue = $this->_Language->FormValue;
        $this->Auto_Publish->CurrentValue = $this->Auto_Publish->FormValue;
        $this->Date_Start->CurrentValue = $this->Date_Start->FormValue;
        $this->Date_Start->CurrentValue = UnformatDateTime($this->Date_Start->CurrentValue, $this->Date_Start->formatPattern());
        $this->Date_End->CurrentValue = $this->Date_End->FormValue;
        $this->Date_End->CurrentValue = UnformatDateTime($this->Date_End->CurrentValue, $this->Date_End->formatPattern());
        $this->Date_Created->CurrentValue = $this->Date_Created->FormValue;
        $this->Date_Created->CurrentValue = UnformatDateTime($this->Date_Created->CurrentValue, $this->Date_Created->formatPattern());
        $this->Created_By->CurrentValue = $this->Created_By->FormValue;
        $this->Translated_ID->CurrentValue = $this->Translated_ID->FormValue;
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
        $this->Announcement_ID->setDbValue($row['Announcement_ID']);
        $this->Is_Active->setDbValue($row['Is_Active']);
        $this->Topic->setDbValue($row['Topic']);
        $this->Message->setDbValue($row['Message']);
        $this->Date_LastUpdate->setDbValue($row['Date_LastUpdate']);
        $this->_Language->setDbValue($row['Language']);
        $this->Auto_Publish->setDbValue($row['Auto_Publish']);
        $this->Date_Start->setDbValue($row['Date_Start']);
        $this->Date_End->setDbValue($row['Date_End']);
        $this->Date_Created->setDbValue($row['Date_Created']);
        $this->Created_By->setDbValue($row['Created_By']);
        $this->Translated_ID->setDbValue($row['Translated_ID']);
    }

    // Return a row with default values
    protected function newRow(): array
    {
        $row = [];
        $row['Announcement_ID'] = $this->Announcement_ID->DefaultValue;
        $row['Is_Active'] = $this->Is_Active->DefaultValue;
        $row['Topic'] = $this->Topic->DefaultValue;
        $row['Message'] = $this->Message->DefaultValue;
        $row['Date_LastUpdate'] = $this->Date_LastUpdate->DefaultValue;
        $row['Language'] = $this->_Language->DefaultValue;
        $row['Auto_Publish'] = $this->Auto_Publish->DefaultValue;
        $row['Date_Start'] = $this->Date_Start->DefaultValue;
        $row['Date_End'] = $this->Date_End->DefaultValue;
        $row['Date_Created'] = $this->Date_Created->DefaultValue;
        $row['Created_By'] = $this->Created_By->DefaultValue;
        $row['Translated_ID'] = $this->Translated_ID->DefaultValue;
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

        // Announcement_ID
        $this->Announcement_ID->RowCssClass = "row";

        // Is_Active
        $this->Is_Active->RowCssClass = "row";

        // Topic
        $this->Topic->RowCssClass = "row";

        // Message
        $this->Message->RowCssClass = "row";

        // Date_LastUpdate
        $this->Date_LastUpdate->RowCssClass = "row";

        // Language
        $this->_Language->RowCssClass = "row";

        // Auto_Publish
        $this->Auto_Publish->RowCssClass = "row";

        // Date_Start
        $this->Date_Start->RowCssClass = "row";

        // Date_End
        $this->Date_End->RowCssClass = "row";

        // Date_Created
        $this->Date_Created->RowCssClass = "row";

        // Created_By
        $this->Created_By->RowCssClass = "row";

        // Translated_ID
        $this->Translated_ID->RowCssClass = "row";

        // View row
        if ($this->RowType == RowType::VIEW) {
            // Announcement_ID
            $this->Announcement_ID->ViewValue = $this->Announcement_ID->CurrentValue;

            // Is_Active
            if (ConvertToBool($this->Is_Active->CurrentValue)) {
                $this->Is_Active->ViewValue = $this->Is_Active->tagCaption(2) != "" ? $this->Is_Active->tagCaption(2) : "Yes";
            } else {
                $this->Is_Active->ViewValue = $this->Is_Active->tagCaption(1) != "" ? $this->Is_Active->tagCaption(1) : "No";
            }

            // Topic
            $this->Topic->ViewValue = $this->Topic->CurrentValue;

            // Message
            $this->Message->ViewValue = $this->Message->CurrentValue;

            // Date_LastUpdate
            $this->Date_LastUpdate->ViewValue = $this->Date_LastUpdate->CurrentValue;
            $this->Date_LastUpdate->ViewValue = FormatDateTime($this->Date_LastUpdate->ViewValue, $this->Date_LastUpdate->formatPattern());

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

            // Auto_Publish
            if (ConvertToBool($this->Auto_Publish->CurrentValue)) {
                $this->Auto_Publish->ViewValue = $this->Auto_Publish->tagCaption(1) != "" ? $this->Auto_Publish->tagCaption(1) : "Yes";
            } else {
                $this->Auto_Publish->ViewValue = $this->Auto_Publish->tagCaption(2) != "" ? $this->Auto_Publish->tagCaption(2) : "No";
            }

            // Date_Start
            $this->Date_Start->ViewValue = $this->Date_Start->CurrentValue;
            $this->Date_Start->ViewValue = FormatDateTime($this->Date_Start->ViewValue, $this->Date_Start->formatPattern());

            // Date_End
            $this->Date_End->ViewValue = $this->Date_End->CurrentValue;
            $this->Date_End->ViewValue = FormatDateTime($this->Date_End->ViewValue, $this->Date_End->formatPattern());

            // Date_Created
            $this->Date_Created->ViewValue = $this->Date_Created->CurrentValue;
            $this->Date_Created->ViewValue = FormatDateTime($this->Date_Created->ViewValue, $this->Date_Created->formatPattern());

            // Created_By
            $curVal = strval($this->Created_By->CurrentValue);
            if ($curVal != "") {
                $this->Created_By->ViewValue = $this->Created_By->lookupCacheOption($curVal);
                if ($this->Created_By->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->Created_By->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $curVal, $this->Created_By->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
                    $sqlWrk = $this->Created_By->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->Created_By->Lookup->renderViewRow($row);
                        }
                        $this->Created_By->ViewValue = $this->Created_By->displayValue($rows[0]);
                    } else {
                        $this->Created_By->ViewValue = $this->Created_By->CurrentValue;
                    }
                }
            } else {
                $this->Created_By->ViewValue = null;
            }

            // Translated_ID
            $curVal = strval($this->Translated_ID->CurrentValue);
            if ($curVal != "") {
                $this->Translated_ID->ViewValue = $this->Translated_ID->lookupCacheOption($curVal);
                if ($this->Translated_ID->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->Translated_ID->Lookup->getTable()->Fields["Announcement_ID"]->searchExpression(), "=", $curVal, $this->Translated_ID->Lookup->getTable()->Fields["Announcement_ID"]->searchDataType(), "DB");
                    $sqlWrk = $this->Translated_ID->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->Translated_ID->Lookup->renderViewRow($row);
                        }
                        $this->Translated_ID->ViewValue = $this->Translated_ID->displayValue($rows[0]);
                    } else {
                        $this->Translated_ID->ViewValue = FormatNumber($this->Translated_ID->CurrentValue, $this->Translated_ID->formatPattern());
                    }
                }
            } else {
                $this->Translated_ID->ViewValue = null;
            }

            // Announcement_ID
            $this->Announcement_ID->HrefValue = "";

            // Is_Active
            $this->Is_Active->HrefValue = "";

            // Topic
            $this->Topic->HrefValue = "";

            // Message
            $this->Message->HrefValue = "";

            // Date_LastUpdate
            $this->Date_LastUpdate->HrefValue = "";

            // Language
            $this->_Language->HrefValue = "";

            // Auto_Publish
            $this->Auto_Publish->HrefValue = "";

            // Date_Start
            $this->Date_Start->HrefValue = "";

            // Date_End
            $this->Date_End->HrefValue = "";

            // Date_Created
            $this->Date_Created->HrefValue = "";

            // Created_By
            $this->Created_By->HrefValue = "";

            // Translated_ID
            $this->Translated_ID->HrefValue = "";
        } elseif ($this->RowType == RowType::EDIT) {
            // Announcement_ID
            $this->Announcement_ID->setupEditAttributes();
            $this->Announcement_ID->EditValue = $this->Announcement_ID->CurrentValue;

            // Is_Active
            $this->Is_Active->EditValue = $this->Is_Active->options(false);
            $this->Is_Active->PlaceHolder = RemoveHtml($this->Is_Active->caption());

            // Topic
            $this->Topic->setupEditAttributes();
            $this->Topic->EditValue = !$this->Topic->Raw ? HtmlDecode($this->Topic->CurrentValue) : $this->Topic->CurrentValue;
            $this->Topic->PlaceHolder = RemoveHtml($this->Topic->caption());

            // Message
            $this->Message->setupEditAttributes();
            $this->Message->EditValue = $this->Message->CurrentValue;
            $this->Message->PlaceHolder = RemoveHtml($this->Message->caption());

            // Date_LastUpdate
            $this->Date_LastUpdate->setupEditAttributes();
            $this->Date_LastUpdate->EditValue = FormatDateTime($this->Date_LastUpdate->CurrentValue, $this->Date_LastUpdate->formatPattern());
            $this->Date_LastUpdate->PlaceHolder = RemoveHtml($this->Date_LastUpdate->caption());

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

            // Auto_Publish
            $this->Auto_Publish->EditValue = $this->Auto_Publish->options(false);
            $this->Auto_Publish->PlaceHolder = RemoveHtml($this->Auto_Publish->caption());

            // Date_Start
            $this->Date_Start->setupEditAttributes();
            $this->Date_Start->EditValue = FormatDateTime($this->Date_Start->CurrentValue, $this->Date_Start->formatPattern());
            $this->Date_Start->PlaceHolder = RemoveHtml($this->Date_Start->caption());

            // Date_End
            $this->Date_End->setupEditAttributes();
            $this->Date_End->EditValue = FormatDateTime($this->Date_End->CurrentValue, $this->Date_End->formatPattern());
            $this->Date_End->PlaceHolder = RemoveHtml($this->Date_End->caption());

            // Date_Created
            $this->Date_Created->setupEditAttributes();
            $this->Date_Created->EditValue = FormatDateTime($this->Date_Created->CurrentValue, $this->Date_Created->formatPattern());
            $this->Date_Created->PlaceHolder = RemoveHtml($this->Date_Created->caption());

            // Created_By
            $this->Created_By->setupEditAttributes();
            $curVal = trim(strval($this->Created_By->CurrentValue));
            if ($curVal != "") {
                $this->Created_By->ViewValue = $this->Created_By->lookupCacheOption($curVal);
            } else {
                $this->Created_By->ViewValue = $this->Created_By->Lookup !== null && is_array($this->Created_By->lookupOptions()) && count($this->Created_By->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->Created_By->ViewValue !== null) { // Load from cache
                $this->Created_By->EditValue = array_values($this->Created_By->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->Created_By->Lookup->getTable()->Fields["Username"]->searchExpression(), "=", $this->Created_By->CurrentValue, $this->Created_By->Lookup->getTable()->Fields["Username"]->searchDataType(), "DB");
                }
                $sqlWrk = $this->Created_By->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                $conn = Conn();
                $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                $ari = count($rswrk);
                $rows = [];
                if ($ari > 0) { // Lookup values found
                    foreach ($rswrk as $row) {
                        $rows[] = $this->Created_By->Lookup->renderViewRow($row);
                    }
                } else {
                    $this->Created_By->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->Created_By->EditValue = $rows;
            }
            $this->Created_By->PlaceHolder = RemoveHtml($this->Created_By->caption());

            // Translated_ID
            $this->Translated_ID->setupEditAttributes();
            $curVal = trim(strval($this->Translated_ID->CurrentValue));
            if ($curVal != "") {
                $this->Translated_ID->ViewValue = $this->Translated_ID->lookupCacheOption($curVal);
            } else {
                $this->Translated_ID->ViewValue = $this->Translated_ID->Lookup !== null && is_array($this->Translated_ID->lookupOptions()) && count($this->Translated_ID->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->Translated_ID->ViewValue !== null) { // Load from cache
                $this->Translated_ID->EditValue = array_values($this->Translated_ID->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->Translated_ID->Lookup->getTable()->Fields["Announcement_ID"]->searchExpression(), "=", $this->Translated_ID->CurrentValue, $this->Translated_ID->Lookup->getTable()->Fields["Announcement_ID"]->searchDataType(), "DB");
                }
                $sqlWrk = $this->Translated_ID->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                $conn = Conn();
                $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                $ari = count($rswrk);
                $rows = [];
                if ($ari > 0) { // Lookup values found
                    foreach ($rswrk as $row) {
                        $rows[] = $this->Translated_ID->Lookup->renderViewRow($row);
                    }
                } else {
                    $this->Translated_ID->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->Translated_ID->EditValue = $rows;
            }
            $this->Translated_ID->PlaceHolder = RemoveHtml($this->Translated_ID->caption());

            // Edit refer script

            // Announcement_ID
            $this->Announcement_ID->HrefValue = "";

            // Is_Active
            $this->Is_Active->HrefValue = "";

            // Topic
            $this->Topic->HrefValue = "";

            // Message
            $this->Message->HrefValue = "";

            // Date_LastUpdate
            $this->Date_LastUpdate->HrefValue = "";

            // Language
            $this->_Language->HrefValue = "";

            // Auto_Publish
            $this->Auto_Publish->HrefValue = "";

            // Date_Start
            $this->Date_Start->HrefValue = "";

            // Date_End
            $this->Date_End->HrefValue = "";

            // Date_Created
            $this->Date_Created->HrefValue = "";

            // Created_By
            $this->Created_By->HrefValue = "";

            // Translated_ID
            $this->Translated_ID->HrefValue = "";
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
            if ($this->Announcement_ID->Visible && $this->Announcement_ID->Required) {
                if (!$this->Announcement_ID->IsDetailKey && IsEmpty($this->Announcement_ID->FormValue)) {
                    $this->Announcement_ID->addErrorMessage(str_replace("%s", $this->Announcement_ID->caption(), $this->Announcement_ID->RequiredErrorMessage));
                }
            }
            if ($this->Is_Active->Visible && $this->Is_Active->Required) {
                if ($this->Is_Active->FormValue == "") {
                    $this->Is_Active->addErrorMessage(str_replace("%s", $this->Is_Active->caption(), $this->Is_Active->RequiredErrorMessage));
                }
            }
            if ($this->Topic->Visible && $this->Topic->Required) {
                if (!$this->Topic->IsDetailKey && IsEmpty($this->Topic->FormValue)) {
                    $this->Topic->addErrorMessage(str_replace("%s", $this->Topic->caption(), $this->Topic->RequiredErrorMessage));
                }
            }
            if ($this->Message->Visible && $this->Message->Required) {
                if (!$this->Message->IsDetailKey && IsEmpty($this->Message->FormValue)) {
                    $this->Message->addErrorMessage(str_replace("%s", $this->Message->caption(), $this->Message->RequiredErrorMessage));
                }
            }
            if ($this->Date_LastUpdate->Visible && $this->Date_LastUpdate->Required) {
                if (!$this->Date_LastUpdate->IsDetailKey && IsEmpty($this->Date_LastUpdate->FormValue)) {
                    $this->Date_LastUpdate->addErrorMessage(str_replace("%s", $this->Date_LastUpdate->caption(), $this->Date_LastUpdate->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->Date_LastUpdate->FormValue, $this->Date_LastUpdate->formatPattern())) {
                $this->Date_LastUpdate->addErrorMessage($this->Date_LastUpdate->getErrorMessage(false));
            }
            if ($this->_Language->Visible && $this->_Language->Required) {
                if (!$this->_Language->IsDetailKey && IsEmpty($this->_Language->FormValue)) {
                    $this->_Language->addErrorMessage(str_replace("%s", $this->_Language->caption(), $this->_Language->RequiredErrorMessage));
                }
            }
            if ($this->Auto_Publish->Visible && $this->Auto_Publish->Required) {
                if ($this->Auto_Publish->FormValue == "") {
                    $this->Auto_Publish->addErrorMessage(str_replace("%s", $this->Auto_Publish->caption(), $this->Auto_Publish->RequiredErrorMessage));
                }
            }
            if ($this->Date_Start->Visible && $this->Date_Start->Required) {
                if (!$this->Date_Start->IsDetailKey && IsEmpty($this->Date_Start->FormValue)) {
                    $this->Date_Start->addErrorMessage(str_replace("%s", $this->Date_Start->caption(), $this->Date_Start->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->Date_Start->FormValue, $this->Date_Start->formatPattern())) {
                $this->Date_Start->addErrorMessage($this->Date_Start->getErrorMessage(false));
            }
            if ($this->Date_End->Visible && $this->Date_End->Required) {
                if (!$this->Date_End->IsDetailKey && IsEmpty($this->Date_End->FormValue)) {
                    $this->Date_End->addErrorMessage(str_replace("%s", $this->Date_End->caption(), $this->Date_End->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->Date_End->FormValue, $this->Date_End->formatPattern())) {
                $this->Date_End->addErrorMessage($this->Date_End->getErrorMessage(false));
            }
            if ($this->Date_Created->Visible && $this->Date_Created->Required) {
                if (!$this->Date_Created->IsDetailKey && IsEmpty($this->Date_Created->FormValue)) {
                    $this->Date_Created->addErrorMessage(str_replace("%s", $this->Date_Created->caption(), $this->Date_Created->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->Date_Created->FormValue, $this->Date_Created->formatPattern())) {
                $this->Date_Created->addErrorMessage($this->Date_Created->getErrorMessage(false));
            }
            if ($this->Created_By->Visible && $this->Created_By->Required) {
                if (!$this->Created_By->IsDetailKey && IsEmpty($this->Created_By->FormValue)) {
                    $this->Created_By->addErrorMessage(str_replace("%s", $this->Created_By->caption(), $this->Created_By->RequiredErrorMessage));
                }
            }
            if ($this->Translated_ID->Visible && $this->Translated_ID->Required) {
                if (!$this->Translated_ID->IsDetailKey && IsEmpty($this->Translated_ID->FormValue)) {
                    $this->Translated_ID->addErrorMessage(str_replace("%s", $this->Translated_ID->caption(), $this->Translated_ID->RequiredErrorMessage));
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

        // Is_Active
        $tmpBool = $this->Is_Active->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Is_Active->setDbValueDef($newRow, $tmpBool, $this->Is_Active->ReadOnly);

        // Topic
        $this->Topic->setDbValueDef($newRow, $this->Topic->CurrentValue, $this->Topic->ReadOnly);

        // Message
        $this->Message->setDbValueDef($newRow, $this->Message->CurrentValue, $this->Message->ReadOnly);

        // Date_LastUpdate
        $this->Date_LastUpdate->setDbValueDef($newRow, UnFormatDateTime($this->Date_LastUpdate->CurrentValue, $this->Date_LastUpdate->formatPattern()), $this->Date_LastUpdate->ReadOnly);

        // Language
        $this->_Language->setDbValueDef($newRow, $this->_Language->CurrentValue, $this->_Language->ReadOnly);

        // Auto_Publish
        $tmpBool = $this->Auto_Publish->CurrentValue;
        if ($tmpBool != "Y" && $tmpBool != "N") {
            $tmpBool = !empty($tmpBool) ? "Y" : "N";
        }
        $this->Auto_Publish->setDbValueDef($newRow, $tmpBool, $this->Auto_Publish->ReadOnly);

        // Date_Start
        $this->Date_Start->setDbValueDef($newRow, UnFormatDateTime($this->Date_Start->CurrentValue, $this->Date_Start->formatPattern()), $this->Date_Start->ReadOnly);

        // Date_End
        $this->Date_End->setDbValueDef($newRow, UnFormatDateTime($this->Date_End->CurrentValue, $this->Date_End->formatPattern()), $this->Date_End->ReadOnly);

        // Date_Created
        $this->Date_Created->setDbValueDef($newRow, UnFormatDateTime($this->Date_Created->CurrentValue, $this->Date_Created->formatPattern()), $this->Date_Created->ReadOnly);

        // Created_By
        $this->Created_By->setDbValueDef($newRow, $this->Created_By->CurrentValue, $this->Created_By->ReadOnly);

        // Translated_ID
        $this->Translated_ID->setDbValueDef($newRow, $this->Translated_ID->CurrentValue, $this->Translated_ID->ReadOnly);
        return $newRow;
    }

    /**
     * Restore edit form from row
     * @param array $row Row
     */
    protected function restoreEditFormFromRow(array $row): void
    {
        if (isset($row['Is_Active'])) { // Is_Active
            $this->Is_Active->CurrentValue = $row['Is_Active'];
        }
        if (isset($row['Topic'])) { // Topic
            $this->Topic->CurrentValue = $row['Topic'];
        }
        if (isset($row['Message'])) { // Message
            $this->Message->CurrentValue = $row['Message'];
        }
        if (isset($row['Date_LastUpdate'])) { // Date_LastUpdate
            $this->Date_LastUpdate->CurrentValue = $row['Date_LastUpdate'];
        }
        if (isset($row['Language'])) { // Language
            $this->_Language->CurrentValue = $row['Language'];
        }
        if (isset($row['Auto_Publish'])) { // Auto_Publish
            $this->Auto_Publish->CurrentValue = $row['Auto_Publish'];
        }
        if (isset($row['Date_Start'])) { // Date_Start
            $this->Date_Start->CurrentValue = $row['Date_Start'];
        }
        if (isset($row['Date_End'])) { // Date_End
            $this->Date_End->CurrentValue = $row['Date_End'];
        }
        if (isset($row['Date_Created'])) { // Date_Created
            $this->Date_Created->CurrentValue = $row['Date_Created'];
        }
        if (isset($row['Created_By'])) { // Created_By
            $this->Created_By->CurrentValue = $row['Created_By'];
        }
        if (isset($row['Translated_ID'])) { // Translated_ID
            $this->Translated_ID->CurrentValue = $row['Translated_ID'];
        }
    }

    // Set up Breadcrumb
    protected function setupBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb();
        $url = CurrentUrl();
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("announcementlist"), "", $this->TableVar, true);
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
                case "x_Is_Active":
                    break;
                case "x__Language":
                    break;
                case "x_Auto_Publish":
                    break;
                case "x_Created_By":
                    break;
                case "x_Translated_ID":
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
