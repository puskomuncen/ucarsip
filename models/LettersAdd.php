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
class LettersAdd extends Letters
{
    use MessagesTrait;
    use FormTrait;

    // Page ID
    public string $PageID = "add";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "LettersAdd";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "lettersadd";

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
        $this->letter_id->Visible = false;
        $this->nomor_surat->setVisibility();
        $this->perihal->setVisibility();
        $this->tanggal_surat->setVisibility();
        $this->tanggal_terima->setVisibility();
        $this->jenis->setVisibility();
        $this->klasifikasi->setVisibility();
        $this->pengirim->setVisibility();
        $this->penerima_unit_id->setVisibility();
        $this->file_url->setVisibility();
        $this->status->setVisibility();
        $this->created_by->setVisibility();
        $this->created_at->setVisibility();
        $this->updated_at->setVisibility();
    }

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'letters';
        $this->TableName = 'letters';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-desktop-table ew-add-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (letters)
        if (!isset($GLOBALS["letters"]) || $GLOBALS["letters"]::class == PROJECT_NAMESPACE . "letters") {
            $GLOBALS["letters"] = &$this;
        }

        // Table name (for backward compatibility only)
        if (!defined(PROJECT_NAMESPACE . "TABLE_NAME")) {
            define(PROJECT_NAMESPACE . "TABLE_NAME", 'letters');
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
                        $result["view"] = SameString($pageName, "lettersview"); // If View page, no primary button
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
            $key .= @$ar['letter_id'];
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
            $this->letter_id->Visible = false;
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
        $this->setupLookupOptions($this->jenis);
        $this->setupLookupOptions($this->klasifikasi);
        $this->setupLookupOptions($this->penerima_unit_id);
        $this->setupLookupOptions($this->status);

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
            if (($keyValue = Get("letter_id") ?? Route("letter_id")) !== null) {
                $this->letter_id->setQueryStringValue($keyValue);
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
                    $this->terminate("letterslist"); // No matching record, return to list
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
                    if (GetPageName($returnUrl) == "letterslist") {
                        $returnUrl = $this->addMasterUrl($returnUrl); // List page, return to List page with correct master key if necessary
                    } elseif (GetPageName($returnUrl) == "lettersview") {
                        $returnUrl = $this->getViewUrl(); // View page, return to View page with keyurl directly
                    }

                    // Handle UseAjaxActions
                    if ($this->IsModal && $this->UseAjaxActions) {
                        $this->IsModal = false;
                        if (GetPageName($returnUrl) != "letterslist") {
                            FlashBag()->add("Return-Url", $returnUrl); // Save return URL
                            $returnUrl = "letterslist"; // Return list page content
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
        $this->file_url->Upload->Index = $this->FormIndex;
        $this->file_url->Upload->uploadFile();
        $this->file_url->CurrentValue = $this->file_url->Upload->FileName;
    }

    // Load default values
    protected function loadDefaultValues(): void
    {
    }

    // Load form values
    protected function loadFormValues(): void
    {
        $validate = !Config("SERVER_VALIDATE");

        // Check field name 'nomor_surat' before field var 'x_nomor_surat'
        $val = $this->getFormValue("nomor_surat", null) ?? $this->getFormValue("x_nomor_surat", null);
        if (!$this->nomor_surat->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->nomor_surat->Visible = false; // Disable update for API request
            } else {
                $this->nomor_surat->setFormValue($val);
            }
        }

        // Check field name 'perihal' before field var 'x_perihal'
        $val = $this->getFormValue("perihal", null) ?? $this->getFormValue("x_perihal", null);
        if (!$this->perihal->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->perihal->Visible = false; // Disable update for API request
            } else {
                $this->perihal->setFormValue($val);
            }
        }

        // Check field name 'tanggal_surat' before field var 'x_tanggal_surat'
        $val = $this->getFormValue("tanggal_surat", null) ?? $this->getFormValue("x_tanggal_surat", null);
        if (!$this->tanggal_surat->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->tanggal_surat->Visible = false; // Disable update for API request
            } else {
                $this->tanggal_surat->setFormValue($val, true, $validate);
            }
            $this->tanggal_surat->CurrentValue = UnformatDateTime($this->tanggal_surat->CurrentValue, $this->tanggal_surat->formatPattern());
        }

        // Check field name 'tanggal_terima' before field var 'x_tanggal_terima'
        $val = $this->getFormValue("tanggal_terima", null) ?? $this->getFormValue("x_tanggal_terima", null);
        if (!$this->tanggal_terima->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->tanggal_terima->Visible = false; // Disable update for API request
            } else {
                $this->tanggal_terima->setFormValue($val, true, $validate);
            }
            $this->tanggal_terima->CurrentValue = UnformatDateTime($this->tanggal_terima->CurrentValue, $this->tanggal_terima->formatPattern());
        }

        // Check field name 'jenis' before field var 'x_jenis'
        $val = $this->getFormValue("jenis", null) ?? $this->getFormValue("x_jenis", null);
        if (!$this->jenis->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->jenis->Visible = false; // Disable update for API request
            } else {
                $this->jenis->setFormValue($val);
            }
        }

        // Check field name 'klasifikasi' before field var 'x_klasifikasi'
        $val = $this->getFormValue("klasifikasi", null) ?? $this->getFormValue("x_klasifikasi", null);
        if (!$this->klasifikasi->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->klasifikasi->Visible = false; // Disable update for API request
            } else {
                $this->klasifikasi->setFormValue($val);
            }
        }

        // Check field name 'pengirim' before field var 'x_pengirim'
        $val = $this->getFormValue("pengirim", null) ?? $this->getFormValue("x_pengirim", null);
        if (!$this->pengirim->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->pengirim->Visible = false; // Disable update for API request
            } else {
                $this->pengirim->setFormValue($val);
            }
        }

        // Check field name 'penerima_unit_id' before field var 'x_penerima_unit_id'
        $val = $this->getFormValue("penerima_unit_id", null) ?? $this->getFormValue("x_penerima_unit_id", null);
        if (!$this->penerima_unit_id->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->penerima_unit_id->Visible = false; // Disable update for API request
            } else {
                $this->penerima_unit_id->setFormValue($val);
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

        // Check field name 'created_by' before field var 'x_created_by'
        $val = $this->getFormValue("created_by", null) ?? $this->getFormValue("x_created_by", null);
        if (!$this->created_by->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->created_by->Visible = false; // Disable update for API request
            } else {
                $this->created_by->setFormValue($val);
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

        // Check field name 'updated_at' before field var 'x_updated_at'
        $val = $this->getFormValue("updated_at", null) ?? $this->getFormValue("x_updated_at", null);
        if (!$this->updated_at->IsDetailKey) {
            if (IsApi() && $val === null) {
                $this->updated_at->Visible = false; // Disable update for API request
            } else {
                $this->updated_at->setFormValue($val);
            }
            $this->updated_at->CurrentValue = UnformatDateTime($this->updated_at->CurrentValue, $this->updated_at->formatPattern());
        }

        // Check field name 'letter_id' first before field var 'x_letter_id'
        $val = $this->hasFormValue("letter_id") ? $this->getFormValue("letter_id") : $this->getFormValue("x_letter_id");
        $this->getUploadFiles(); // Get upload files
    }

    // Restore form values
    public function restoreFormValues(): void
    {
        $this->nomor_surat->CurrentValue = $this->nomor_surat->FormValue;
        $this->perihal->CurrentValue = $this->perihal->FormValue;
        $this->tanggal_surat->CurrentValue = $this->tanggal_surat->FormValue;
        $this->tanggal_surat->CurrentValue = UnformatDateTime($this->tanggal_surat->CurrentValue, $this->tanggal_surat->formatPattern());
        $this->tanggal_terima->CurrentValue = $this->tanggal_terima->FormValue;
        $this->tanggal_terima->CurrentValue = UnformatDateTime($this->tanggal_terima->CurrentValue, $this->tanggal_terima->formatPattern());
        $this->jenis->CurrentValue = $this->jenis->FormValue;
        $this->klasifikasi->CurrentValue = $this->klasifikasi->FormValue;
        $this->pengirim->CurrentValue = $this->pengirim->FormValue;
        $this->penerima_unit_id->CurrentValue = $this->penerima_unit_id->FormValue;
        $this->status->CurrentValue = $this->status->FormValue;
        $this->created_by->CurrentValue = $this->created_by->FormValue;
        $this->created_at->CurrentValue = $this->created_at->FormValue;
        $this->created_at->CurrentValue = UnformatDateTime($this->created_at->CurrentValue, $this->created_at->formatPattern());
        $this->updated_at->CurrentValue = $this->updated_at->FormValue;
        $this->updated_at->CurrentValue = UnformatDateTime($this->updated_at->CurrentValue, $this->updated_at->formatPattern());
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
        $this->letter_id->setDbValue($row['letter_id']);
        $this->nomor_surat->setDbValue($row['nomor_surat']);
        $this->perihal->setDbValue($row['perihal']);
        $this->tanggal_surat->setDbValue($row['tanggal_surat']);
        $this->tanggal_terima->setDbValue($row['tanggal_terima']);
        $this->jenis->setDbValue($row['jenis']);
        $this->klasifikasi->setDbValue($row['klasifikasi']);
        $this->pengirim->setDbValue($row['pengirim']);
        $this->penerima_unit_id->setDbValue($row['penerima_unit_id']);
        $this->file_url->Upload->DbValue = $row['file_url'];
        $this->file_url->setDbValue($this->file_url->Upload->DbValue);
        $this->status->setDbValue($row['status']);
        $this->created_by->setDbValue($row['created_by']);
        $this->created_at->setDbValue($row['created_at']);
        $this->updated_at->setDbValue($row['updated_at']);
    }

    // Return a row with default values
    protected function newRow(): array
    {
        $row = [];
        $row['letter_id'] = $this->letter_id->DefaultValue;
        $row['nomor_surat'] = $this->nomor_surat->DefaultValue;
        $row['perihal'] = $this->perihal->DefaultValue;
        $row['tanggal_surat'] = $this->tanggal_surat->DefaultValue;
        $row['tanggal_terima'] = $this->tanggal_terima->DefaultValue;
        $row['jenis'] = $this->jenis->DefaultValue;
        $row['klasifikasi'] = $this->klasifikasi->DefaultValue;
        $row['pengirim'] = $this->pengirim->DefaultValue;
        $row['penerima_unit_id'] = $this->penerima_unit_id->DefaultValue;
        $row['file_url'] = $this->file_url->DefaultValue;
        $row['status'] = $this->status->DefaultValue;
        $row['created_by'] = $this->created_by->DefaultValue;
        $row['created_at'] = $this->created_at->DefaultValue;
        $row['updated_at'] = $this->updated_at->DefaultValue;
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

        // letter_id
        $this->letter_id->RowCssClass = "row";

        // nomor_surat
        $this->nomor_surat->RowCssClass = "row";

        // perihal
        $this->perihal->RowCssClass = "row";

        // tanggal_surat
        $this->tanggal_surat->RowCssClass = "row";

        // tanggal_terima
        $this->tanggal_terima->RowCssClass = "row";

        // jenis
        $this->jenis->RowCssClass = "row";

        // klasifikasi
        $this->klasifikasi->RowCssClass = "row";

        // pengirim
        $this->pengirim->RowCssClass = "row";

        // penerima_unit_id
        $this->penerima_unit_id->RowCssClass = "row";

        // file_url
        $this->file_url->RowCssClass = "row";

        // status
        $this->status->RowCssClass = "row";

        // created_by
        $this->created_by->RowCssClass = "row";

        // created_at
        $this->created_at->RowCssClass = "row";

        // updated_at
        $this->updated_at->RowCssClass = "row";

        // View row
        if ($this->RowType == RowType::VIEW) {
            // letter_id
            $this->letter_id->ViewValue = $this->letter_id->CurrentValue;

            // nomor_surat
            $this->nomor_surat->ViewValue = $this->nomor_surat->CurrentValue;

            // perihal
            $this->perihal->ViewValue = $this->perihal->CurrentValue;

            // tanggal_surat
            $this->tanggal_surat->ViewValue = $this->tanggal_surat->CurrentValue;
            $this->tanggal_surat->ViewValue = FormatDateTime($this->tanggal_surat->ViewValue, $this->tanggal_surat->formatPattern());

            // tanggal_terima
            $this->tanggal_terima->ViewValue = $this->tanggal_terima->CurrentValue;
            $this->tanggal_terima->ViewValue = FormatDateTime($this->tanggal_terima->ViewValue, $this->tanggal_terima->formatPattern());

            // jenis
            if (strval($this->jenis->CurrentValue) != "") {
                $this->jenis->ViewValue = $this->jenis->optionCaption($this->jenis->CurrentValue);
            } else {
                $this->jenis->ViewValue = null;
            }

            // klasifikasi
            if (strval($this->klasifikasi->CurrentValue) != "") {
                $this->klasifikasi->ViewValue = $this->klasifikasi->optionCaption($this->klasifikasi->CurrentValue);
            } else {
                $this->klasifikasi->ViewValue = null;
            }

            // pengirim
            $this->pengirim->ViewValue = $this->pengirim->CurrentValue;

            // penerima_unit_id
            $curVal = strval($this->penerima_unit_id->CurrentValue);
            if ($curVal != "") {
                $this->penerima_unit_id->ViewValue = $this->penerima_unit_id->lookupCacheOption($curVal);
                if ($this->penerima_unit_id->ViewValue === null) { // Lookup from database
                    $filterWrk = SearchFilter($this->penerima_unit_id->Lookup->getTable()->Fields["unit_id"]->searchExpression(), "=", $curVal, $this->penerima_unit_id->Lookup->getTable()->Fields["unit_id"]->searchDataType(), "DB");
                    $sqlWrk = $this->penerima_unit_id->Lookup->getSql(false, $filterWrk, '', $this, true, true);
                    $conn = Conn();
                    $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                    $ari = count($rswrk);
                    if ($ari > 0) { // Lookup values found
                        $rows = [];
                        foreach ($rswrk as $row) {
                            $rows[] = $this->penerima_unit_id->Lookup->renderViewRow($row);
                        }
                        $this->penerima_unit_id->ViewValue = $this->penerima_unit_id->displayValue($rows[0]);
                    } else {
                        $this->penerima_unit_id->ViewValue = FormatNumber($this->penerima_unit_id->CurrentValue, $this->penerima_unit_id->formatPattern());
                    }
                }
            } else {
                $this->penerima_unit_id->ViewValue = null;
            }

            // file_url
            if (!IsEmpty($this->file_url->Upload->DbValue)) {
                $this->file_url->ViewValue = $this->file_url->Upload->DbValue;
            } else {
                $this->file_url->ViewValue = "";
            }

            // status
            if (strval($this->status->CurrentValue) != "") {
                $this->status->ViewValue = $this->status->optionCaption($this->status->CurrentValue);
            } else {
                $this->status->ViewValue = null;
            }

            // created_by
            $this->created_by->ViewValue = $this->created_by->CurrentValue;
            $this->created_by->ViewValue = FormatNumber($this->created_by->ViewValue, $this->created_by->formatPattern());

            // created_at
            $this->created_at->ViewValue = $this->created_at->CurrentValue;
            $this->created_at->ViewValue = FormatDateTime($this->created_at->ViewValue, $this->created_at->formatPattern());

            // updated_at
            $this->updated_at->ViewValue = $this->updated_at->CurrentValue;
            $this->updated_at->ViewValue = FormatDateTime($this->updated_at->ViewValue, $this->updated_at->formatPattern());

            // nomor_surat
            $this->nomor_surat->HrefValue = "";

            // perihal
            $this->perihal->HrefValue = "";

            // tanggal_surat
            $this->tanggal_surat->HrefValue = "";

            // tanggal_terima
            $this->tanggal_terima->HrefValue = "";

            // jenis
            $this->jenis->HrefValue = "";

            // klasifikasi
            $this->klasifikasi->HrefValue = "";

            // pengirim
            $this->pengirim->HrefValue = "";

            // penerima_unit_id
            $this->penerima_unit_id->HrefValue = "";

            // file_url
            $this->file_url->HrefValue = "";
            $this->file_url->ExportHrefValue = $this->file_url->UploadPath . $this->file_url->Upload->DbValue;

            // status
            $this->status->HrefValue = "";

            // created_by
            $this->created_by->HrefValue = "";

            // created_at
            $this->created_at->HrefValue = "";

            // updated_at
            $this->updated_at->HrefValue = "";
        } elseif ($this->RowType == RowType::ADD) {
            // nomor_surat
            $this->nomor_surat->setupEditAttributes();
            $this->nomor_surat->EditValue = !$this->nomor_surat->Raw ? HtmlDecode($this->nomor_surat->CurrentValue) : $this->nomor_surat->CurrentValue;
            $this->nomor_surat->PlaceHolder = RemoveHtml($this->nomor_surat->caption());

            // perihal
            $this->perihal->setupEditAttributes();
            $this->perihal->EditValue = !$this->perihal->Raw ? HtmlDecode($this->perihal->CurrentValue) : $this->perihal->CurrentValue;
            $this->perihal->PlaceHolder = RemoveHtml($this->perihal->caption());

            // tanggal_surat
            $this->tanggal_surat->setupEditAttributes();
            $this->tanggal_surat->EditValue = FormatDateTime($this->tanggal_surat->CurrentValue, $this->tanggal_surat->formatPattern());
            $this->tanggal_surat->PlaceHolder = RemoveHtml($this->tanggal_surat->caption());

            // tanggal_terima
            $this->tanggal_terima->setupEditAttributes();
            $this->tanggal_terima->EditValue = FormatDateTime($this->tanggal_terima->CurrentValue, $this->tanggal_terima->formatPattern());
            $this->tanggal_terima->PlaceHolder = RemoveHtml($this->tanggal_terima->caption());

            // jenis
            $this->jenis->EditValue = $this->jenis->options(false);
            $this->jenis->PlaceHolder = RemoveHtml($this->jenis->caption());

            // klasifikasi
            $this->klasifikasi->EditValue = $this->klasifikasi->options(false);
            $this->klasifikasi->PlaceHolder = RemoveHtml($this->klasifikasi->caption());

            // pengirim
            $this->pengirim->setupEditAttributes();
            $this->pengirim->EditValue = !$this->pengirim->Raw ? HtmlDecode($this->pengirim->CurrentValue) : $this->pengirim->CurrentValue;
            $this->pengirim->PlaceHolder = RemoveHtml($this->pengirim->caption());

            // penerima_unit_id
            $this->penerima_unit_id->setupEditAttributes();
            $curVal = trim(strval($this->penerima_unit_id->CurrentValue));
            if ($curVal != "") {
                $this->penerima_unit_id->ViewValue = $this->penerima_unit_id->lookupCacheOption($curVal);
            } else {
                $this->penerima_unit_id->ViewValue = $this->penerima_unit_id->Lookup !== null && is_array($this->penerima_unit_id->lookupOptions()) && count($this->penerima_unit_id->lookupOptions()) > 0 ? $curVal : null;
            }
            if ($this->penerima_unit_id->ViewValue !== null) { // Load from cache
                $this->penerima_unit_id->EditValue = array_values($this->penerima_unit_id->lookupOptions());
            } else { // Lookup from database
                if ($curVal == "") {
                    $filterWrk = "0=1";
                } else {
                    $filterWrk = SearchFilter($this->penerima_unit_id->Lookup->getTable()->Fields["unit_id"]->searchExpression(), "=", $this->penerima_unit_id->CurrentValue, $this->penerima_unit_id->Lookup->getTable()->Fields["unit_id"]->searchDataType(), "DB");
                }
                $sqlWrk = $this->penerima_unit_id->Lookup->getSql(true, $filterWrk, "", $this, false, true);
                $conn = Conn();
                $rswrk = $conn->executeQuery($sqlWrk)->fetchAllAssociative();
                $ari = count($rswrk);
                $rows = [];
                if ($ari > 0) { // Lookup values found
                    foreach ($rswrk as $row) {
                        $rows[] = $this->penerima_unit_id->Lookup->renderViewRow($row);
                    }
                } else {
                    $this->penerima_unit_id->ViewValue = $this->language->phrase("PleaseSelect");
                }
                $this->penerima_unit_id->EditValue = $rows;
            }
            $this->penerima_unit_id->PlaceHolder = RemoveHtml($this->penerima_unit_id->caption());

            // file_url
            $this->file_url->setupEditAttributes();
            if (!IsEmpty($this->file_url->Upload->DbValue)) {
                $this->file_url->EditValue = $this->file_url->Upload->DbValue;
            } else {
                $this->file_url->EditValue = "";
            }
            if (!IsEmpty($this->file_url->CurrentValue)) {
                $this->file_url->Upload->FileName = $this->file_url->CurrentValue;
            }
            if (!Config("CREATE_UPLOAD_FILE_ON_COPY")) {
                $this->file_url->Upload->DbValue = null;
            }
            if ($this->isShow() || $this->isCopy()) {
                $this->file_url->Upload->setupTempDirectory();
            }

            // status
            $this->status->EditValue = $this->status->options(false);
            $this->status->PlaceHolder = RemoveHtml($this->status->caption());

            // created_by

            // created_at

            // updated_at

            // Add refer script

            // nomor_surat
            $this->nomor_surat->HrefValue = "";

            // perihal
            $this->perihal->HrefValue = "";

            // tanggal_surat
            $this->tanggal_surat->HrefValue = "";

            // tanggal_terima
            $this->tanggal_terima->HrefValue = "";

            // jenis
            $this->jenis->HrefValue = "";

            // klasifikasi
            $this->klasifikasi->HrefValue = "";

            // pengirim
            $this->pengirim->HrefValue = "";

            // penerima_unit_id
            $this->penerima_unit_id->HrefValue = "";

            // file_url
            $this->file_url->HrefValue = "";
            $this->file_url->ExportHrefValue = $this->file_url->UploadPath . $this->file_url->Upload->DbValue;

            // status
            $this->status->HrefValue = "";

            // created_by
            $this->created_by->HrefValue = "";

            // created_at
            $this->created_at->HrefValue = "";

            // updated_at
            $this->updated_at->HrefValue = "";
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
            if ($this->nomor_surat->Visible && $this->nomor_surat->Required) {
                if (!$this->nomor_surat->IsDetailKey && IsEmpty($this->nomor_surat->FormValue)) {
                    $this->nomor_surat->addErrorMessage(str_replace("%s", $this->nomor_surat->caption(), $this->nomor_surat->RequiredErrorMessage));
                }
            }
            if ($this->perihal->Visible && $this->perihal->Required) {
                if (!$this->perihal->IsDetailKey && IsEmpty($this->perihal->FormValue)) {
                    $this->perihal->addErrorMessage(str_replace("%s", $this->perihal->caption(), $this->perihal->RequiredErrorMessage));
                }
            }
            if ($this->tanggal_surat->Visible && $this->tanggal_surat->Required) {
                if (!$this->tanggal_surat->IsDetailKey && IsEmpty($this->tanggal_surat->FormValue)) {
                    $this->tanggal_surat->addErrorMessage(str_replace("%s", $this->tanggal_surat->caption(), $this->tanggal_surat->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->tanggal_surat->FormValue, $this->tanggal_surat->formatPattern())) {
                $this->tanggal_surat->addErrorMessage($this->tanggal_surat->getErrorMessage(false));
            }
            if ($this->tanggal_terima->Visible && $this->tanggal_terima->Required) {
                if (!$this->tanggal_terima->IsDetailKey && IsEmpty($this->tanggal_terima->FormValue)) {
                    $this->tanggal_terima->addErrorMessage(str_replace("%s", $this->tanggal_terima->caption(), $this->tanggal_terima->RequiredErrorMessage));
                }
            }
            if (!CheckDate($this->tanggal_terima->FormValue, $this->tanggal_terima->formatPattern())) {
                $this->tanggal_terima->addErrorMessage($this->tanggal_terima->getErrorMessage(false));
            }
            if ($this->jenis->Visible && $this->jenis->Required) {
                if ($this->jenis->FormValue == "") {
                    $this->jenis->addErrorMessage(str_replace("%s", $this->jenis->caption(), $this->jenis->RequiredErrorMessage));
                }
            }
            if ($this->klasifikasi->Visible && $this->klasifikasi->Required) {
                if ($this->klasifikasi->FormValue == "") {
                    $this->klasifikasi->addErrorMessage(str_replace("%s", $this->klasifikasi->caption(), $this->klasifikasi->RequiredErrorMessage));
                }
            }
            if ($this->pengirim->Visible && $this->pengirim->Required) {
                if (!$this->pengirim->IsDetailKey && IsEmpty($this->pengirim->FormValue)) {
                    $this->pengirim->addErrorMessage(str_replace("%s", $this->pengirim->caption(), $this->pengirim->RequiredErrorMessage));
                }
            }
            if ($this->penerima_unit_id->Visible && $this->penerima_unit_id->Required) {
                if (!$this->penerima_unit_id->IsDetailKey && IsEmpty($this->penerima_unit_id->FormValue)) {
                    $this->penerima_unit_id->addErrorMessage(str_replace("%s", $this->penerima_unit_id->caption(), $this->penerima_unit_id->RequiredErrorMessage));
                }
            }
            if ($this->file_url->Visible && $this->file_url->Required) {
                if ($this->file_url->Upload->FileName == "" && !$this->file_url->Upload->KeepFile) {
                    $this->file_url->addErrorMessage(str_replace("%s", $this->file_url->caption(), $this->file_url->RequiredErrorMessage));
                }
            }
            if ($this->status->Visible && $this->status->Required) {
                if ($this->status->FormValue == "") {
                    $this->status->addErrorMessage(str_replace("%s", $this->status->caption(), $this->status->RequiredErrorMessage));
                }
            }
            if ($this->created_by->Visible && $this->created_by->Required) {
                if (!$this->created_by->IsDetailKey && IsEmpty($this->created_by->FormValue)) {
                    $this->created_by->addErrorMessage(str_replace("%s", $this->created_by->caption(), $this->created_by->RequiredErrorMessage));
                }
            }
            if ($this->created_at->Visible && $this->created_at->Required) {
                if (!$this->created_at->IsDetailKey && IsEmpty($this->created_at->FormValue)) {
                    $this->created_at->addErrorMessage(str_replace("%s", $this->created_at->caption(), $this->created_at->RequiredErrorMessage));
                }
            }
            if ($this->updated_at->Visible && $this->updated_at->Required) {
                if (!$this->updated_at->IsDetailKey && IsEmpty($this->updated_at->FormValue)) {
                    $this->updated_at->addErrorMessage(str_replace("%s", $this->updated_at->caption(), $this->updated_at->RequiredErrorMessage));
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
        if ($this->file_url->Visible && !$this->file_url->Upload->KeepFile) {
            if (!IsEmpty($this->file_url->Upload->FileName)) {
                $this->file_url->Upload->DbValue = null;
                FixUploadFileNames($this->file_url);
                $this->file_url->setDbValueDef($newRow, $this->file_url->Upload->FileName, false);
            }
        }

        // Update current values
        $this->Fields->setCurrentValues($newRow);
        if ($this->nomor_surat->CurrentValue != "") { // Check field with unique index
            $filter = "(`nomor_surat` = '" . AdjustSql($this->nomor_surat->CurrentValue) . "')";
            $rsChk = $this->loadRecords($filter)->fetchAssociative();
            if ($rsChk !== false) {
                $idxErrMsg = sprintf($this->language->phrase("DuplicateIndex"), $this->nomor_surat->CurrentValue, $this->nomor_surat->caption());
                $this->setFailureMessage($idxErrMsg);
                return false;
            }
        }
        $conn = $this->getConnection();

        // Load db values from old row
        $this->loadDbValues($oldRow);

        // Call Row Inserting event
        $insertRow = $this->rowInserting($oldRow, $newRow);
        if ($insertRow) {
            $addRow = $this->insert($newRow);
            if ($addRow) {
                if ($this->file_url->Visible && !$this->file_url->Upload->KeepFile) {
                    $this->file_url->Upload->DbValue = null;
                    if (!SaveUploadFiles($this->file_url, $newRow['file_url'], false)) {
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

        // nomor_surat
        $this->nomor_surat->setDbValueDef($newRow, $this->nomor_surat->CurrentValue, false);

        // perihal
        $this->perihal->setDbValueDef($newRow, $this->perihal->CurrentValue, false);

        // tanggal_surat
        $this->tanggal_surat->setDbValueDef($newRow, UnFormatDateTime($this->tanggal_surat->CurrentValue, $this->tanggal_surat->formatPattern()), false);

        // tanggal_terima
        $this->tanggal_terima->setDbValueDef($newRow, UnFormatDateTime($this->tanggal_terima->CurrentValue, $this->tanggal_terima->formatPattern()), false);

        // jenis
        $this->jenis->setDbValueDef($newRow, $this->jenis->CurrentValue, false);

        // klasifikasi
        $this->klasifikasi->setDbValueDef($newRow, $this->klasifikasi->CurrentValue, false);

        // pengirim
        $this->pengirim->setDbValueDef($newRow, $this->pengirim->CurrentValue, false);

        // penerima_unit_id
        $this->penerima_unit_id->setDbValueDef($newRow, $this->penerima_unit_id->CurrentValue, false);

        // file_url
        if ($this->file_url->Visible && !$this->file_url->Upload->KeepFile) {
            if ($this->file_url->Upload->FileName == "") {
                $newRow['file_url'] = null;
            } else {
                FixUploadTempFileNames($this->file_url);
                $newRow['file_url'] = $this->file_url->Upload->FileName;
            }
        }

        // status
        $this->status->setDbValueDef($newRow, $this->status->CurrentValue, false);

        // created_by
        $this->created_by->CurrentValue = $this->created_by->getAutoUpdateValue(); // PHP
        $this->created_by->setDbValueDef($newRow, $this->created_by->CurrentValue, false);

        // created_at
        $this->created_at->CurrentValue = $this->created_at->getAutoUpdateValue(); // PHP
        $this->created_at->setDbValueDef($newRow, UnFormatDateTime($this->created_at->CurrentValue, $this->created_at->formatPattern()), false);

        // updated_at
        $this->updated_at->CurrentValue = $this->updated_at->getAutoUpdateValue(); // PHP
        $this->updated_at->setDbValueDef($newRow, UnFormatDateTime($this->updated_at->CurrentValue, $this->updated_at->formatPattern()), false);
        return $newRow;
    }

    // Set up Breadcrumb
    protected function setupBreadcrumb(): void
    {
        $breadcrumb = Breadcrumb();
        $url = CurrentUrl();
        $breadcrumb->add("list", $this->TableVar, $this->addMasterUrl("letterslist"), "", $this->TableVar, true);
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
                case "x_jenis":
                    break;
                case "x_klasifikasi":
                    break;
                case "x_penerima_unit_id":
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
