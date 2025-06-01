<?php

namespace PHPMaker2025\ucarsip;

use Doctrine\DBAL\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use DI\Attribute\Inject;
use Exception;

/**
 * Base table class
 *
 * Common class for tables and reports
 */
class DbTableBase
{
    protected ?QueryCacheProfile $cacheProfile = null; // Doctrine cache profile
    public string $TableVar = "";
    public string $TableName = "";
    public string $TableType = "";
    public string $Dbid = "DB"; // Table database id
    public bool $Visible = true;
    public array $Charts = [];
    public array $Rows = []; // Data for Custom Template
    public string $OldKey = ""; // Old key (for edit/copy)
    public ?Result $Result = null; // Result
    public array|bool|null $CurrentRow = null; // Current row // PHP
    public bool $UseCustomTemplate = false; // Use custom template
    public string $Export = ""; // Export
    public bool $ExportAll;
    public int $ExportPageBreakCount; // Page break per every n record (PDF only)
    public string $ExportPageOrientation; // Page orientation (PDF only)
    public string $ExportPageSize; // Page size (PDF only)
    public ?string $ExportExcelPageOrientation; // Page orientation (Excel only)
    public ?int $ExportExcelPageSize; // Page size (Excel only)
    public ?int $ExportWordVersion = null; // Word version (12 => 2007, 14 => 2010, 15 => 2013, PhpWord only)
    public string $ExportWordPageOrientation; // Page orientation (Word only)
    public string $ExportWordPageSize; // Page size (Word only)
    public ?int $ExportWordColumnWidth; // Page orientation (Word only)
    public bool $SendEmail = true; // Send email on insert/update/delete
    public string $PageBreakHtml = "";
    public bool $ExportPageBreaks = true; // Page breaks when export
    public bool $ImportInsertOnly = true; // Import by insert only
    public bool $ImportUseTransaction = false; // Import use transaction
    public int $ImportMaxFailures = 0; // Import maximum number of failures
    public ?BasicSearch $BasicSearch = null; // Basic search
    public string $QueryRules = ""; // Rules from jQuery Query builder
    public string $CurrentFilter = ""; // Current filter
    public string $CurrentOrder; // Current order
    public string $CurrentOrderType; // Current order type
    public int $RowCount = 0;
    public RowType $RowType = RowType::VIEW; // Row type
    public string $CssClass = ""; // CSS class
    public string $CssStyle = ""; // CSS style
    public ?string $CurrentAction = null; // Current action
    public ?string $ActionValue = null; // Action value
    public ?string $LastAction = null; // Last action
    public int $UserIDPermission = 0; // User ID permissions
    public int $Count = 0; // Record count (as detail table)
    public string $UpdateTable = ""; // Update table
    public string $SearchOption = ""; // Search option
    public string $Filter = "";
    public ?string $Sort = null;
    public ?Pager $Pager = null;
    public bool $AutoHidePager;
    public bool $AutoHidePageSizeSelector;
    public ?QueryBuilder $QueryBuilder = null;
    protected string $TableCaption = "";
    protected array $PageCaption = [];
    public string $RouteCompositeKeySeparator = "/"; // Composite key separator for routing
    public bool $UseTransaction = false;
    public string $RowAction = ""; // Row action
    public array $ValidationErrors = []; // Server side validation errors for Grid-Add/Edit and Multi-Edit

    // Charts related
    public bool $SourceTableIsCustomView = false;
    public string $TableReportType = "";
    public bool $ShowDrillDownFilter = false;
    public bool $UseDrillDownPanel = false; // Use drill down panel
    public bool $DrillDown = false;
    public bool $DrillDownInPanel = false;

    // Table
    public string $TableClass = "";
    public string $TableGridClass = ""; // CSS class for .card (with a leading space)
    public string $TableContainerClass = ""; // CSS class for .card-body (e.g. height of the main table)
    public string $TableContainerStyle = ""; // CSS style for .card-body (e.g. height of the main table)
    public bool $UseResponsiveTable = false;
    public string $ResponsiveTableClass = "";
    public string $ContainerClass = "p-0";
    public string $ContextClass = ""; // CSS class name as context
    public bool $ShowCurrentFilter = false;

    // Soft deleted properties
    public bool $UseSoftDeleteFilter = true;
    public string $SoftDeleteFieldName = ""; // Must be DateTime
    public bool $HardDelete = true; // Hard delete
    public bool $TimeAware = false; // Set a date for future delete
    public string $SoftDeleteTimeAwarePeriod;

    // Default field properties
    public string $UploadPath;
    public string $OldUploadPath;
    public string $UploadAllowedFileExt;
    public int $UploadMaxFileSize;
    public ?int $UploadMaxFileCount = null;
    public bool $ImageCropper = false;
    public bool $UseColorbox = false;
    public bool $AutoFillOriginalValue = false;
    public bool $UseLookupCache = false;
    public int $LookupCacheCount;
    public bool $ExportOriginalValue = false;
    public bool $ExportFieldCaption = false;
    public bool $ExportFieldImage = false;
    public string $DefaultNumberFormat = "";

    // Constructor
    public function __construct(
        protected Language $language,
        protected AdvancedSecurity $security,
        public DbFields $Fields = new DbFields(),
        public Attributes $RowAttrs = new Attributes(),
    ) {
        $this->SearchOption = Config("SEARCH_OPTION");
        $this->ImportInsertOnly = Config("IMPORT_INSERT_ONLY");
        $this->ImportMaxFailures = Config("IMPORT_MAX_FAILURES");
        $this->AutoHidePager = Config("AUTO_HIDE_PAGER");
        $this->AutoHidePageSizeSelector = Config("AUTO_HIDE_PAGE_SIZE_SELECTOR");
        $this->UseResponsiveTable = !IsExport() && Config("USE_RESPONSIVE_TABLE");
        $this->ResponsiveTableClass = Config("RESPONSIVE_TABLE_CLASS");
        $this->TableContainerClass = $this->UseResponsiveTable ? $this->ResponsiveTableClass : "";
        $this->ShowCurrentFilter = Config("SHOW_CURRENT_FILTER");
        $this->SoftDeleteTimeAwarePeriod = Config("SOFT_DELETE_TIME_AWARE_PERIOD");

        // Default field properties
        $this->UploadPath = Config("UPLOAD_DEST_PATH");
        $this->OldUploadPath = Config("UPLOAD_DEST_PATH");
        $this->UploadAllowedFileExt = Config("UPLOAD_ALLOWED_FILE_EXT");
        $this->UploadMaxFileSize = Config("MAX_FILE_SIZE");
        $this->UploadMaxFileCount = Config("MAX_FILE_COUNT");
        $this->ImageCropper = Config("IMAGE_CROPPER");
        $this->UseColorbox = Config("USE_COLORBOX");
        $this->AutoFillOriginalValue = Config("AUTO_FILL_ORIGINAL_VALUE");
        $this->UseLookupCache = Config("USE_LOOKUP_CACHE");
        $this->LookupCacheCount = Config("LOOKUP_CACHE_COUNT");
        $this->ExportOriginalValue = Config("EXPORT_ORIGINAL_VALUE");
        $this->ExportFieldCaption = Config("EXPORT_FIELD_CAPTION");
        $this->ExportFieldImage = Config("EXPORT_FIELD_IMAGE");
        $this->DefaultNumberFormat = Config("DEFAULT_NUMBER_FORMAT");

        // Page break
        $this->PageBreakHtml = Config("PAGE_BREAK_HTML");
    }

    // Get database type
    public function getDbType(): string|bool
    {
        return GetConnectionType($this->Dbid);
    }

    // Get Connection
    public function getConnection(): object
    {
        return Conn($this->Dbid);
    }

    // Check if transaction supported
    public function supportsTransaction(): bool
    {
        $support = true;
        $dbtype = $this->getDbType();
        if ($dbtype == "MYSQL" && $this->TableName != "") {
            $engines = Session(SESSION_MYSQL_ENGINES) ?? [];
            $support = $engines[$this->Dbid][$this->TableName] ?? null;
            if ($support === null) {
                $sql = "SHOW TABLE STATUS WHERE Engine = 'MyISAM' AND Name = '" . AdjustSql($this->TableName) . "'";
                try {
                    $support = $this->getConnection()->executeQuery($sql)->rowCount() == 0;
                } catch (Exception $e) {
                    $support = false;
                }
                $engines[$this->Dbid][$this->TableName] = $support;
                Session(SESSION_MYSQL_ENGINES, $engines);
            }
        }
        return $support;
    }

    /**
     * Get query builder
     *
     * @param ?string $type Type of query builder: "insert", "update" or "delete"
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getQueryBuilder(?string $type = null): QueryBuilder
    {
        $qb = $this->getConnection()->createQueryBuilder();
        return match ($type) {
            "insert" => $qb->insert($this->UpdateTable),
            "update" => $qb->update($this->UpdateTable),
            "delete" => $qb->delete($this->UpdateTable),
            default => $qb
        };
    }

    // Get entity manager
    public function getEntityManager(): object
    {
        return EntityManager($this->Dbid);
    }

    // Find field by param
    public function fieldByParam(string $param): DbField
    {
        $ar = array_filter($this->Fields->getArrayCopy(), fn($fld) => $fld->Param == $param);
        return array_shift($ar);
    }

    // Fetch current row
    public function fetch(int $cnt = 1): array|bool
    {
        for ($i = 0; $i < $cnt; $i++) {
            $this->CurrentRow = $this->Result->fetchAssociative();
        }
        return $this->CurrentRow;
    }

    // Check if fixed header table
    public function isFixedHeaderTable(): bool
    {
        return ContainsClass($this->TableClass, Config("FIXED_HEADER_TABLE_CLASS"));
    }

    /**
     * Set fixed header table
     *
     * @param bool $enabled Whether enable fixed header table
     * @param string $height Height of table container (CSS class name)
     * @return void
     */
    public function setFixedHeaderTable(bool $enabled, ?string $height = null): void
    {
        if ($enabled && !$this->isExport()) {
            AppendClass($this->TableClass, Config("FIXED_HEADER_TABLE_CLASS"));
            $height ??= Config("FIXED_HEADER_TABLE_HEIGHT");
            if ($height) {
                AppendClass($this->TableContainerClass, $height);
                AppendClass($this->TableContainerClass, "overflow-y-auto");
            }
        } else {
            RemoveClass($this->TableClass, Config("FIXED_HEADER_TABLE_CLASS"));
            AppendClass($this->TableContainerClass, "h-auto"); // Override height class
            RemoveClass($this->TableContainerClass, "overflow-y-auto");
        }
    }

    /**
     * Build SELECT statement
     *
     * @param string|QueryBuilder $select
     * @param string $from
     * @param string $where
     * @param string $groupBy
     * @param string $having
     * @param string $orderBy
     * @param string $filter
     * @param string $sort
     * @return QueryBuilder
     */
    public function buildSelectSql(string|QueryBuilder $select, string $from, string $where, string $groupBy, string $having, string $orderBy, string $filter, string $sort): QueryBuilder
    {
        if (is_string($select)) {
            $queryBuilder = $this->getQueryBuilder()->select($select);
        } elseif ($select instanceof QueryBuilder) {
            $queryBuilder = $select;
        }
        if ($from != "") {
            $queryBuilder = $queryBuilder->from($from);
        }
        if ($where != "") {
            $queryBuilder->where($where);
        }
        if ($filter != "") {
            $queryBuilder->andWhere($filter);
        }
        if ($groupBy != "") {
            $queryBuilder->groupBy($groupBy);
        }
        if ($having != "") {
            $queryBuilder->having($having);
        }
        if ($sort != "") {
            $orderBy = $sort;
        }
        $flds = GetSortFields($orderBy);
        if (is_array($flds)) {
            foreach ($flds as $fld) {
                $queryBuilder->addOrderBy($fld[0], $fld[1]);
            }
        }
        return $queryBuilder;
    }

    // Build filter from array
    public function arrayToFilter(array $filters): string
    {
        $filter = "";
        foreach ($filters as $name => $value) {
            if (isset($this->Fields[$name])) {
                AddFilter($filter, QuotedName($this->Fields[$name]->Name, $this->Dbid) . "=" . QuotedValue($value, $this->Fields[$name]->DataType, $this->Dbid));
            }
        }
        return $filter;
    }

    // Reset attributes for table object
    public function resetAttributes(): void
    {
        $this->CssClass = "";
        $this->CssStyle = "";
        $this->RowAttrs = new Attributes();
        foreach ($this->Fields as $fld) {
            $fld->resetAttributes();
        }
    }

    // Setup field titles
    public function setupFieldTitles(): void
    {
        foreach ($this->Fields as $fld) {
            if (strval($fld->title()) != "") {
                $fld->EditAttrs["data-bs-toggle"] = "tooltip";
                $fld->EditAttrs["title"] = HtmlEncode($fld->title());
            }
        }
    }

    // Get field values
    public function getFieldValues(string $propertyname): array
    {
        return $this->Fields->getPropertyValues($propertyname);
    }

    // Get field cell attributes
    public function fieldCellAttributes(): array
    {
        $values = [];
        foreach ($this->Fields as $fldname => $fld) {
            $values[$fld->Param] = $fld->cellAttributes();
        }
        return $values;
    }

    // Get field database values for Custom Template
    public function customTemplateFieldValues(): array
    {
        $values = [];
        foreach ($this->Fields as $fldname => $fld) {
            if (in_array($fld->DataType, Config("CUSTOM_TEMPLATE_DATATYPES")) && $fld->Visible) {
                if (is_string($fld->DbValue) && strlen($fld->DbValue) > Config("DATA_STRING_MAX_LENGTH")) {
                    $values[$fld->Param] = substr($fld->DbValue, 0, Config("DATA_STRING_MAX_LENGTH"));
                } else {
                    $values[$fld->Param] = $fld->HtmlTag == "FILE" ? $fld->Upload->DbValue : $fld->DbValue;
                }
            }
        }
        return $values;
    }

    // Set table caption
    public function setTableCaption(string $v): void
    {
        $this->TableCaption = $v;
    }

    // Table caption
    public function tableCaption(): string
    {
        if ($this->TableCaption == "") {
            $this->TableCaption = $this->language->tablePhrase($this->TableVar, "TblCaption");
        }
        return $this->TableCaption;
    }

    // Set page caption
    public function setPageCaption(string $page, string $v): void
    {
        $this->PageCaption[$page] = $v;
    }

    // Page caption
    public function pageCaption(string $page): string
    {
        $caption = @$this->PageCaption[$page];
        if ($caption != "") {
            return $caption;
        } else {
            $caption = $this->language->tablePhrase($this->TableVar, "TblPageCaption" . $page);
            if ($caption == "") {
                $caption = "Page " . $page;
            }
            return $caption;
        }
    }

    // Row styles
    public function rowStyles(): string
    {
        $att = "";
        $style = Concat($this->CssStyle, $this->RowAttrs["style"], ";");
        $class = $this->CssClass;
        AppendClass($class, $this->RowAttrs["class"]);
        if ($style != "") {
            $att .= ' style="' . $style . '"';
        }
        if ($class != '') {
            $att .= ' class="' . $class . '"';
        }
        return $att;
    }

    // Row attributes
    public function rowAttributes(): string
    {
        $att = $this->rowStyles();
        if (!$this->isExport()) {
            $attrs = $this->RowAttrs->toString(["class", "style"]);
            if ($attrs != "") {
                $att .= $attrs;
            }
        }
        return $att;
    }

    // Field object by name
    public function fields(string $fldname): DbField
    {
        return $this->Fields[$fldname];
    }

    // Has Invalid fields
    public function hasInvalidFields(): bool
    {
        return array_any($this->Fields->getArrayCopy(), fn($fld) => $fld->IsInvalid);
    }

    // Visible field count
    public function visibleFieldCount(): int
    {
        $cnt = 0;
        foreach ($this->Fields as $fld) {
            if ($fld->Visible) {
                $cnt++;
            }
        }
        return $cnt;
    }

    // Is export
    public function isExport(string $format = ""): bool
    {
        if ($format) {
            return SameText($this->Export, $format);
        } else {
            return $this->Export != "";
        }
    }

    /**
     * Set use lookup cache
     *
     * @param bool $b Use lookup cache or not
     * @return void
     */
    public function setUseLookupCache(bool $b): void
    {
        foreach ($this->Fields as $fld) {
            $fld->UseLookupCache = $b;
        }
    }

    /**
     * Set Lookup cache count
     *
     * @param int $i Lookup cache count
     * @return void
     */
    public function setLookupCacheCount(int $i): void
    {
        foreach ($this->Fields as $fld) {
            $fld->LookupCacheCount = $i;
        }
    }

    /**
     * Convert table properties to client side variables
     *
     * @param string[] $tablePropertyNames Table property names
     * @param string[] $fieldPropertyNames Field property names
     * @return array
     */
    public function toClientVar(array $tablePropertyNames = [], array $fieldPropertyNames = []): array
    {
        if (empty($tablePropertyNames) && empty($fieldPropertyNames)) { // No arguments
            $tablePropertyNames = Config("TABLE_CLIENT_VARS"); // Use default
            $fieldPropertyNames = Config("FIELD_CLIENT_VARS"); // Use default
        }
        $props = [];
        foreach ($tablePropertyNames as $name) {
            if (method_exists($this, $name)) {
                $props[lcfirst($name)] = $this->$name();
            } elseif (property_exists($this, $name)) {
                $props[lcfirst($name)] = $this->$name;
            }
        }
        if (count($fieldPropertyNames) > 0) {
            $props["fields"] = [];
            foreach ($this->Fields as $fld) {
                $props["fields"][$fld->Param] = [];
                foreach ($fieldPropertyNames as $name) {
                    if (method_exists($fld, $name)) {
                        $props["fields"][$fld->Param][lcfirst($name)] = $fld->$name();
                    } elseif (property_exists($fld, $name)) {
                        $props["fields"][$fld->Param][lcfirst($name)] = $fld->$name;
                    }
                };
            }
        }
        return array_merge_recursive(GetClientVar("tables", $this->TableVar) ?? [], $props); // Merge $ClientVariables["tables"][$this->TableVar]
    }

    // URL encode
    public function urlEncode(string $str): string
    {
        return urlencode($str);
    }

    // Print
    public function raw(string $str): string
    {
        return $str;
    }

    // Get validation errors
    public function getValidationErrors(): ?array
    {
        // Check if validation required
        if (!Config("SERVER_VALIDATE")) {
            return null;
        }
        $errors = [];
        foreach ($this->Fields as $field) {
            if ($field->IsInvalid) {
                $errors[$field->Param] = $field->getErrorMessage();
            }
        }
        return count($errors) > 0  ? $errors : null;
    }

    // Session Rule (QueryBuilder)
    public function getSessionRules(): ?string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_RULES")));
    }

    public function setSessionRules(?string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_RULES")), $v);
    }

    // Dashboard Filter
    public function getDashboardFilter(string $dashboardVar, string $tableVar): string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $dashboardVar . "_" . $tableVar . "_" . Config("DASHBOARD_FILTER"))) ?? "";
    }

    public function setDashboardFilter(string $dashboardVar, string $tableVar, string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $dashboardVar . "_" . $tableVar . "_" . Config("DASHBOARD_FILTER")), $v);
    }

    // For obsolete properties only
    public function __set(string $name, mixed $value): void
    {
        if (EndsString("_Count", $name)) { // <DetailTable>_Count
            $t = preg_replace('/_Count$/', "", $name);
            throw new Exception("Obsolete property: " . $name . ", please use Container('" . $t . "')->Count.");
        } elseif (IsDebug()) {
            throw new Exception("Undefined property: " . $name . ".");
        }
    }

    // For obsolete properties only
    public function __get(string $name): mixed
    {
        if (EndsString("_Count", $name)) { // <DetailTable>_Count
            $t = preg_replace('/_Count$/', "", $name);
            throw new Exception("Obsolete property: " . $name . ", please use Container('" . $t . "')->Count.");
        } elseif (IsDebug()) {
            throw new Exception("Undefined property: " . $name . ".");
        }
        return null;
    }
}
