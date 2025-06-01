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
 * Table class for settings
 */
class Settings extends DbTable implements LookupTableInterface
{
    protected string $SqlFrom = "";
    protected ?QueryBuilder $SqlSelect = null;
    protected ?string $SqlSelectList = null;
    protected string $SqlWhere = "";
    protected string $SqlGroupBy = "";
    protected string $SqlHaving = "";
    protected string $SqlOrderBy = "";
    public string $DbErrorMessage = "";
    public $UseSessionForListSql = true;

    // Column CSS classes
    public string $LeftColumnClass = "col-sm-4 col-form-label ew-label";
    public string $RightColumnClass = "col-sm-8";
    public string $OffsetColumnClass = "col-sm-8 offset-sm-4";
    public string $TableLeftColumnClass = "w-col-4";

    // Ajax / Modal
    public bool $UseAjaxActions = false;
    public bool $ModalSearch = false;
    public bool $ModalView = false;
    public bool $ModalAdd = false;
    public bool $ModalEdit = false;
    public bool $ModalUpdate = false;
    public bool $InlineDelete = false;
    public bool $ModalGridAdd = false;
    public bool $ModalGridEdit = false;
    public bool $ModalMultiEdit = false;

    // Fields
    public DbField $Option_ID;
    public DbField $Option_Default;
    public DbField $Show_Announcement;
    public DbField $Use_Announcement_Table;
    public DbField $Maintenance_Mode;
    public DbField $Maintenance_Finish_DateTime;
    public DbField $Auto_Normal_After_Maintenance;

    // Page ID
    public string $PageID = ""; // To be set by subclass

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        $this->TableVar = "settings";
        $this->TableName = 'settings';
        $this->TableType = "TABLE";
        $this->ImportUseTransaction = $this->supportsTransaction() && Config("IMPORT_USE_TRANSACTION");
        $this->UseTransaction = $this->supportsTransaction() && Config("USE_TRANSACTION");
        $this->UpdateTable = "settings"; // Update table
        $this->Dbid = 'DB';
        $this->ExportAll = true;
        $this->ExportPageBreakCount = 0; // Page break per every n record (PDF only)

        // PDF
        $this->ExportPageOrientation = "portrait"; // Page orientation (PDF only)
        $this->ExportPageSize = "a4"; // Page size (PDF only)

        // PhpSpreadsheet
        $this->ExportExcelPageOrientation = null; // Page orientation (PhpSpreadsheet only)
        $this->ExportExcelPageSize = null; // Page size (PhpSpreadsheet only)

        // PHPWord
        $this->ExportWordPageOrientation = ""; // Page orientation (PHPWord only)
        $this->ExportWordPageSize = ""; // Page orientation (PHPWord only)
        $this->ExportWordColumnWidth = null; // Cell width (PHPWord only)
        $this->DetailAdd = false; // Allow detail add
        $this->DetailEdit = false; // Allow detail edit
        $this->DetailView = false; // Allow detail view
        $this->ShowMultipleDetails = false; // Show multiple details
        $this->GridAddRowCount = 5;
        $this->AllowAddDeleteRow = true; // Allow add/delete row
        $this->UseAjaxActions = $this->UseAjaxActions || Config("USE_AJAX_ACTIONS");
        $this->UserIDPermission = Config("DEFAULT_USER_ID_PERMISSION"); // Default User ID permission
        $this->BasicSearch = new BasicSearch($this, Session(), $this->language);

        // Option_ID
        $this->Option_ID = new DbField(
            $this, // Table
            'x_Option_ID', // Variable name
            'Option_ID', // Name
            '`Option_ID`', // Expression
            '`Option_ID`', // Basic search expression
            3, // Type
            11, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Option_ID`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'NO' // Edit Tag
        );
        $this->Option_ID->InputTextType = "text";
        $this->Option_ID->Raw = true;
        $this->Option_ID->IsAutoIncrement = true; // Autoincrement field
        $this->Option_ID->IsPrimaryKey = true; // Primary key field
        $this->Option_ID->Nullable = false; // NOT NULL field
        $this->Option_ID->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->Option_ID->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['Option_ID'] = &$this->Option_ID;

        // Option_Default
        $this->Option_Default = new DbField(
            $this, // Table
            'x_Option_Default', // Variable name
            'Option_Default', // Name
            '`Option_Default`', // Expression
            '`Option_Default`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Option_Default`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Option_Default->addMethod("getDefault", fn() => "N");
        $this->Option_Default->InputTextType = "text";
        $this->Option_Default->Raw = true;
        $this->Option_Default->setDataType(DataType::BOOLEAN);
        $this->Option_Default->TrueValue = "Y";
        $this->Option_Default->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Option_Default->Lookup = new Lookup($this->Option_Default, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->Option_Default->Lookup = new Lookup($this->Option_Default, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Option_Default->Lookup = new Lookup($this->Option_Default, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Option_Default->OptionCount = 2;
        $this->Option_Default->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Option_Default'] = &$this->Option_Default;

        // Show_Announcement
        $this->Show_Announcement = new DbField(
            $this, // Table
            'x_Show_Announcement', // Variable name
            'Show_Announcement', // Name
            '`Show_Announcement`', // Expression
            '`Show_Announcement`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Show_Announcement`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Show_Announcement->addMethod("getDefault", fn() => "N");
        $this->Show_Announcement->InputTextType = "text";
        $this->Show_Announcement->Raw = true;
        $this->Show_Announcement->setDataType(DataType::BOOLEAN);
        $this->Show_Announcement->TrueValue = "Y";
        $this->Show_Announcement->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Show_Announcement->Lookup = new Lookup($this->Show_Announcement, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->Show_Announcement->Lookup = new Lookup($this->Show_Announcement, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Show_Announcement->Lookup = new Lookup($this->Show_Announcement, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Show_Announcement->OptionCount = 2;
        $this->Show_Announcement->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Show_Announcement'] = &$this->Show_Announcement;

        // Use_Announcement_Table
        $this->Use_Announcement_Table = new DbField(
            $this, // Table
            'x_Use_Announcement_Table', // Variable name
            'Use_Announcement_Table', // Name
            '`Use_Announcement_Table`', // Expression
            '`Use_Announcement_Table`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Use_Announcement_Table`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Use_Announcement_Table->addMethod("getDefault", fn() => "N");
        $this->Use_Announcement_Table->InputTextType = "text";
        $this->Use_Announcement_Table->Raw = true;
        $this->Use_Announcement_Table->setDataType(DataType::BOOLEAN);
        $this->Use_Announcement_Table->TrueValue = "Y";
        $this->Use_Announcement_Table->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Use_Announcement_Table->Lookup = new Lookup($this->Use_Announcement_Table, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->Use_Announcement_Table->Lookup = new Lookup($this->Use_Announcement_Table, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Use_Announcement_Table->Lookup = new Lookup($this->Use_Announcement_Table, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Use_Announcement_Table->OptionCount = 2;
        $this->Use_Announcement_Table->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Use_Announcement_Table'] = &$this->Use_Announcement_Table;

        // Maintenance_Mode
        $this->Maintenance_Mode = new DbField(
            $this, // Table
            'x_Maintenance_Mode', // Variable name
            'Maintenance_Mode', // Name
            '`Maintenance_Mode`', // Expression
            '`Maintenance_Mode`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Maintenance_Mode`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Maintenance_Mode->addMethod("getDefault", fn() => "N");
        $this->Maintenance_Mode->InputTextType = "text";
        $this->Maintenance_Mode->Raw = true;
        $this->Maintenance_Mode->setDataType(DataType::BOOLEAN);
        $this->Maintenance_Mode->TrueValue = "Y";
        $this->Maintenance_Mode->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Maintenance_Mode->Lookup = new Lookup($this->Maintenance_Mode, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->Maintenance_Mode->Lookup = new Lookup($this->Maintenance_Mode, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Maintenance_Mode->Lookup = new Lookup($this->Maintenance_Mode, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Maintenance_Mode->OptionCount = 2;
        $this->Maintenance_Mode->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Maintenance_Mode'] = &$this->Maintenance_Mode;

        // Maintenance_Finish_DateTime
        $this->Maintenance_Finish_DateTime = new DbField(
            $this, // Table
            'x_Maintenance_Finish_DateTime', // Variable name
            'Maintenance_Finish_DateTime', // Name
            '`Maintenance_Finish_DateTime`', // Expression
            CastDateFieldForLike("`Maintenance_Finish_DateTime`", 1, "DB"), // Basic search expression
            135, // Type
            19, // Size
            1, // Date/Time format
            false, // Is upload field
            '`Maintenance_Finish_DateTime`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Maintenance_Finish_DateTime->InputTextType = "text";
        $this->Maintenance_Finish_DateTime->Raw = true;
        $this->Maintenance_Finish_DateTime->DefaultErrorMessage = str_replace("%s", DateFormat(1), $this->language->phrase("IncorrectDate"));
        $this->Maintenance_Finish_DateTime->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['Maintenance_Finish_DateTime'] = &$this->Maintenance_Finish_DateTime;

        // Auto_Normal_After_Maintenance
        $this->Auto_Normal_After_Maintenance = new DbField(
            $this, // Table
            'x_Auto_Normal_After_Maintenance', // Variable name
            'Auto_Normal_After_Maintenance', // Name
            '`Auto_Normal_After_Maintenance`', // Expression
            '`Auto_Normal_After_Maintenance`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Auto_Normal_After_Maintenance`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Auto_Normal_After_Maintenance->addMethod("getDefault", fn() => "Y");
        $this->Auto_Normal_After_Maintenance->InputTextType = "text";
        $this->Auto_Normal_After_Maintenance->Raw = true;
        $this->Auto_Normal_After_Maintenance->setDataType(DataType::BOOLEAN);
        $this->Auto_Normal_After_Maintenance->TrueValue = "Y";
        $this->Auto_Normal_After_Maintenance->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Auto_Normal_After_Maintenance->Lookup = new Lookup($this->Auto_Normal_After_Maintenance, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->Auto_Normal_After_Maintenance->Lookup = new Lookup($this->Auto_Normal_After_Maintenance, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Auto_Normal_After_Maintenance->Lookup = new Lookup($this->Auto_Normal_After_Maintenance, 'settings', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Auto_Normal_After_Maintenance->OptionCount = 2;
        $this->Auto_Normal_After_Maintenance->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Auto_Normal_After_Maintenance'] = &$this->Auto_Normal_After_Maintenance;

        // Cache profile
        $this->cacheProfile = new QueryCacheProfile(0, $this->TableVar, Container("result.cache"));

        // Call Table Load event
        $this->tableLoad();
    }

    // Field Visibility
    public function getFieldVisibility(string $fldParm): bool
    {
        return $this->$fldParm->Visible; // Returns original value
    }

    // Set left column class (must be predefined col-*-* classes of Bootstrap grid system)
    public function setLeftColumnClass(string $class): void
    {
        if (preg_match('/^col\-(\w+)\-(\d+)$/', $class, $match)) {
            $this->LeftColumnClass = $class . " col-form-label ew-label";
            $this->RightColumnClass = "col-" . $match[1] . "-" . strval(12 - (int)$match[2]);
            $this->OffsetColumnClass = $this->RightColumnClass . " " . str_replace("col-", "offset-", $class);
            $this->TableLeftColumnClass = preg_replace('/^col-\w+-(\d+)$/', "w-col-$1", $class); // Change to w-col-*
        }
    }

    // Single column sort
    public function updateSort(DbField &$fld): void
    {
        if ($this->CurrentOrder == $fld->Name) {
            $sortField = $fld->Expression;
            $lastSort = $fld->getSort();
            if (in_array($this->CurrentOrderType, ["ASC", "DESC", "NO"])) {
                $curSort = $this->CurrentOrderType;
            } else {
                $curSort = $lastSort;
            }
            $orderBy = in_array($curSort, ["ASC", "DESC"]) ? $sortField . " " . $curSort : "";
            $this->setSessionOrderBy($orderBy); // Save to Session
        }
    }

    // Update field sort
    public function updateFieldSort(): void
    {
        $orderBy = $this->getSessionOrderBy(); // Get ORDER BY from Session
        $flds = GetSortFields($orderBy);
        foreach ($this->Fields as $field) {
            $fldSort = "";
            foreach ($flds as $fld) {
                if ($fld[0] == $field->Expression || $fld[0] == $field->VirtualExpression) {
                    $fldSort = $fld[1];
                }
            }
            $field->setSort($fldSort);
        }
    }

    // Render X Axis for chart
    public function renderChartXAxis(string $chartVar, array $chartRow): array
    {
        return $chartRow;
    }

    // Get FROM clause
    public function getSqlFrom(): string
    {
        return ($this->SqlFrom != "") ? $this->SqlFrom : "settings";
    }

    // Get FROM clause (for backward compatibility)
    public function sqlFrom(): string
    {
        return $this->getSqlFrom();
    }

    // Set FROM clause
    public function setSqlFrom(string $v): void
    {
        $this->SqlFrom = $v;
    }

    // Get SELECT clause
    public function getSqlSelect(): QueryBuilder // Select
    {
        return $this->SqlSelect ?? $this->getQueryBuilder()->select($this->sqlSelectFields());
    }

    // Get list of fields
    private function sqlSelectFields(): string
    {
        $useFieldNames = false;
        $fieldNames = [];
        $platform = $this->getConnection()->getDatabasePlatform();
        foreach ($this->Fields as $field) {
            $expr = $field->Expression;
            $customExpr = $field->CustomDataType?->convertToPHPValueSQL($expr, $platform) ?? $expr;
            if ($customExpr != $expr) {
                $fieldNames[] = $customExpr . " AS " . QuotedName($field->Name, $this->Dbid);
                $useFieldNames = true;
            } else {
                $fieldNames[] = $expr;
            }
        }
        return $useFieldNames ? implode(", ", $fieldNames) : "*";
    }

    // Get SELECT clause (for backward compatibility)
    public function sqlSelect(): QueryBuilder
    {
        return $this->getSqlSelect();
    }

    // Set SELECT clause
    public function setSqlSelect(QueryBuilder $v): void
    {
        $this->SqlSelect = $v;
    }

    // Get default filter
    public function getDefaultFilter(): string
    {
        return "";
    }

    // Get WHERE clause
    public function getSqlWhere(bool $delete = false): string
    {
        $where = ($this->SqlWhere != "") ? $this->SqlWhere : "";
        AddFilter($where, $this->getDefaultFilter());
        if (!$delete && !IsEmpty($this->SoftDeleteFieldName) && $this->UseSoftDeleteFilter) { // Add soft delete filter
            AddFilter($where, $this->Fields[$this->SoftDeleteFieldName]->Expression . " IS NULL");
            if ($this->TimeAware) { // Add time aware filter
                AddFilter($where, $this->Fields[$this->SoftDeleteFieldName]->Expression . " > " . $this->getConnection()->getDatabasePlatform()->getCurrentTimestampSQL(), "OR");
            }
        }
        return $where;
    }

    // Get WHERE clause (for backward compatibility)
    public function sqlWhere(): string
    {
        return $this->getSqlWhere();
    }

    // Set WHERE clause
    public function setSqlWhere(string $v): void
    {
        $this->SqlWhere = $v;
    }

    // Get GROUP BY clause
    public function getSqlGroupBy(): string
    {
        return $this->SqlGroupBy != "" ? $this->SqlGroupBy : "";
    }

    // Get GROUP BY clause (for backward compatibility)
    public function sqlGroupBy(): string
    {
        return $this->getSqlGroupBy();
    }

    // set GROUP BY clause
    public function setSqlGroupBy(string $v): void
    {
        $this->SqlGroupBy = $v;
    }

    // Get HAVING clause
    public function getSqlHaving(): string // Having
    {
        return ($this->SqlHaving != "") ? $this->SqlHaving : "";
    }

    // Get HAVING clause (for backward compatibility)
    public function sqlHaving(): string
    {
        return $this->getSqlHaving();
    }

    // Set HAVING clause
    public function setSqlHaving(string $v): void
    {
        $this->SqlHaving = $v;
    }

    // Get ORDER BY clause
    public function getSqlOrderBy(): string
    {
        return ($this->SqlOrderBy != "") ? $this->SqlOrderBy : "";
    }

    // Get ORDER BY clause (for backward compatibility)
    public function sqlOrderBy(): string
    {
        return $this->getSqlOrderBy();
    }

    // set ORDER BY clause
    public function setSqlOrderBy(string $v): void
    {
        $this->SqlOrderBy = $v;
    }

    // Apply User ID filters
    public function applyUserIDFilters(string $filter, string $id = ""): string
    {
        return $filter;
    }

    // Check if User ID security allows view all
    public function userIDAllow(string $id = ""): bool
    {
        $allow = $this->UserIDPermission;
        return match ($id) {
            "add", "copy", "gridadd", "register", "addopt" => ($allow & Allow::ADD->value) == Allow::ADD->value,
            "edit", "gridedit", "update", "changepassword", "resetpassword" => ($allow & Allow::EDIT->value) == Allow::EDIT->value,
            "delete" => ($allow & Allow::DELETE->value) == Allow::DELETE->value,
            "view" => ($allow & Allow::VIEW->value) == Allow::VIEW->value,
            "search" => ($allow & Allow::SEARCH->value) == Allow::SEARCH->value,
            "lookup" => ($allow & Allow::LOOKUP->value) == Allow::LOOKUP->value,
            default => ($allow & Allow::LIST->value) == Allow::LIST->value
        };
    }

    /**
     * Get record count
     *
     * @param string|QueryBuilder $sql SQL or QueryBuilder
     * @param Connection $c Connection
     * @return int
     */
    public function getRecordCount(string|QueryBuilder $sql, ?Connection $c = null): int
    {
        $cnt = -1;
        $sqlwrk = $sql instanceof QueryBuilder // Query builder
            ? (clone $sql)->resetOrderBy()->getSQL()
            : $sql;
        $pattern = '/^SELECT\s([\s\S]+?)\sFROM\s/i';
        // Skip Custom View / SubQuery / SELECT DISTINCT / ORDER BY
        if (
            in_array($this->TableType, ["TABLE", "VIEW", "LINKTABLE"])
            && preg_match($pattern, $sqlwrk)
            && !preg_match('/\(\s*(SELECT[^)]+)\)/i', $sqlwrk)
            && !preg_match('/^\s*SELECT\s+DISTINCT\s+/i', $sqlwrk)
            && !preg_match('/\s+ORDER\s+BY\s+/i', $sqlwrk)
        ) {
            $sqlcnt = "SELECT COUNT(*) FROM " . preg_replace($pattern, "", $sqlwrk);
        } else {
            $sqlcnt = "SELECT COUNT(*) FROM (" . $sqlwrk . ") COUNT_TABLE";
        }
        $conn = $c ?? $this->getConnection();
        $cnt = $conn->fetchOne($sqlcnt);
        if ($cnt !== false) {
            return (int)$cnt;
        }
        // Unable to get count by SELECT COUNT(*), execute the SQL to get record count directly
        $result = $conn->executeQuery($sqlwrk);
        $cnt = $result->rowCount();
        if ($cnt == 0) { // Unable to get record count, count directly
            while ($result->fetchAssociative()) {
                $cnt++;
            }
        }
        return $cnt;
    }

    // Get SQL
    public function getSql(string $where, string $orderBy = "", bool $delete = false): QueryBuilder
    {
        return $this->getSqlAsQueryBuilder($where, $orderBy, $delete);
    }

    // Get QueryBuilder
    public function getSqlAsQueryBuilder(string $where, string $orderBy = "", bool $delete = false): QueryBuilder
    {
        return $this->buildSelectSql(
            $this->getSqlSelect(),
            $this->getSqlFrom(),
            $this->getSqlWhere($delete),
            $this->getSqlGroupBy(),
            $this->getSqlHaving(),
            $this->getSqlOrderBy(),
            $where,
            $orderBy
        );
    }

    // Table SQL
    public function getCurrentSql(bool $delete = false): QueryBuilder
    {
        $filter = $this->CurrentFilter;
        $filter = $this->applyUserIDFilters($filter);
        $sort = $this->getSessionOrderBy();
        return $this->getSql($filter, $sort, $delete);
    }

    /**
     * Table SQL with List page filter
     *
     * @return QueryBuilder
     */
    public function getListSql(): QueryBuilder
    {
        $filter = $this->UseSessionForListSql ? $this->getSessionWhere() : "";
        AddFilter($filter, $this->CurrentFilter);
        $filter = $this->applyUserIDFilters($filter);
        $this->recordsSelecting($filter);
        $select = $this->getSqlSelect();
        $from = $this->getSqlFrom();
        $sort = $this->UseSessionForListSql ? $this->getSessionOrderBy() : "";
        $this->Sort = $sort;
        return $this->buildSelectSql(
            $select,
            $from,
            $this->getSqlWhere(),
            $this->getSqlGroupBy(),
            $this->getSqlHaving(),
            $this->getSqlOrderBy(),
            $filter,
            $sort
        );
    }

    // Get ORDER BY clause
    public function getOrderBy(): string
    {
        $orderBy = $this->getSqlOrderBy();
        $sort = $this->getSessionOrderBy();
        if ($orderBy != "" && $sort != "") {
            $orderBy .= ", " . $sort;
        } elseif ($sort != "") {
            $orderBy = $sort;
        }
        return $orderBy;
    }

    // Get record count based on filter
    public function loadRecordCount($filter, $delete = false): int
    {
        $origFilter = $this->CurrentFilter;
        $this->CurrentFilter = $filter;
        if ($delete == false) {
            $this->recordsSelecting($this->CurrentFilter);
        }
        $isCustomView = $this->TableType == "CUSTOMVIEW";
        $select = $isCustomView ? $this->getSqlSelect() : $this->getQueryBuilder()->select("*");
        $groupBy = $isCustomView ? $this->getSqlGroupBy() : "";
        $having = $isCustomView ? $this->getSqlHaving() : "";
        $sql = $this->buildSelectSql($select, $this->getSqlFrom(), $this->getSqlWhere($delete), $groupBy, $having, "", $this->CurrentFilter, "");
        $cnt = $this->getRecordCount($sql);
        $this->CurrentFilter = $origFilter;
        return $cnt;
    }

    // Get record count (for current List page)
    public function listRecordCount(): int
    {
        $filter = $this->getSessionWhere();
        AddFilter($filter, $this->CurrentFilter);
        $filter = $this->applyUserIDFilters($filter);
        $this->recordsSelecting($filter);
        $isCustomView = $this->TableType == "CUSTOMVIEW";
        $select = $isCustomView ? $this->getSqlSelect() : $this->getQueryBuilder()->select("*");
        $groupBy = $isCustomView ? $this->getSqlGroupBy() : "";
        $having = $isCustomView ? $this->getSqlHaving() : "";
        $sql = $this->buildSelectSql($select, $this->getSqlFrom(), $this->getSqlWhere(), $groupBy, $having, "", $filter, "");
        $cnt = $this->getRecordCount($sql);
        return $cnt;
    }

    /**
     * Get query builder for INSERT
     *
     * @param array $row Row to be inserted
     * @return QueryBuilder
     */
    public function insertSql(array $row): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder()->insert($this->UpdateTable);
        $platform = $this->getConnection()->getDatabasePlatform();
        foreach ($row as $name => $value) {
            if (!isset($this->Fields[$name]) || $this->Fields[$name]->IsCustom) {
                continue;
            }
            $field = $this->Fields[$name];
            $parm = $queryBuilder->createPositionalParameter($value, $field->getParameterType());
            $parm = $field->CustomDataType?->convertToDatabaseValueSQL($parm, $platform) ?? $parm; // Convert database SQL
            $queryBuilder->setValue($field->Expression, $parm);
        }
        return $queryBuilder;
    }

    // Insert
    public function insert(array &$row): int|bool
    {
        $conn = $this->getConnection();
        try {
            $queryBuilder = $this->insertSql($row);
            $result = $queryBuilder->executeStatement();
			if ($result) {
                $this->clearLookupCache();
            }
            $this->DbErrorMessage = "";
        } catch (Exception $e) {
            $result = false;
            $this->DbErrorMessage = $e->getMessage();
        }
        if ($result) {
            $this->Option_ID->setDbValue($conn->lastInsertId());
            $row['Option_ID'] = $this->Option_ID->DbValue;
        }
        return $result;
    }

    /**
     * Get query builder for UPDATE
     *
     * @param array $row Row to be updated
     * @param string|array $where WHERE clause
     * @return QueryBuilder
     */
    public function updateSql(array $row, string|array $where = ""): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder()->update($this->UpdateTable);
        $platform = $this->getConnection()->getDatabasePlatform();
        foreach ($row as $name => $value) {
            if (!isset($this->Fields[$name]) || $this->Fields[$name]->IsCustom || $this->Fields[$name]->IsAutoIncrement) {
                continue;
            }
            $field = $this->Fields[$name];
            $parm = $queryBuilder->createPositionalParameter($value, $field->getParameterType());
            $parm = $field->CustomDataType?->convertToDatabaseValueSQL($parm, $platform) ?? $parm; // Convert database SQL
            $queryBuilder->set($field->Expression, $parm);
        }
        $where = is_array($where) ? $this->arrayToFilter($where) : $where;
        if ($where != "") {
            $queryBuilder->where($where);
        }
        return $queryBuilder;
    }

    // Update
    public function update(array $row, string|array $where = "", ?array $old = null, bool $currentFilter = true): int|bool
    {
        // If no field is updated, execute may return 0. Treat as success
        try {
            $where = is_array($where) ? $this->arrayToFilter($where) : $where;
            $filter = $currentFilter ? $this->CurrentFilter : "";
            AddFilter($where, $filter);
            $success = $this->updateSql($row, $where)->executeStatement();
            $success = $success > 0 ? $success : true;
			if ($success) {
                $this->clearLookupCache();
            }
            $this->DbErrorMessage = "";
        } catch (Exception $e) {
            $success = false;
            $this->DbErrorMessage = $e->getMessage();
        }

        // Return auto increment field
        if ($success) {
            if (!isset($row['Option_ID']) && !IsEmpty($this->Option_ID->CurrentValue)) {
                $row['Option_ID'] = $this->Option_ID->CurrentValue;
            }
        }
        return $success;
    }

    /**
     * Get query builder for DELETE
     *
     * @param ?array $row Key values
     * @param string|array $where WHERE clause
     * @return QueryBuilder
     */
    public function deleteSql(?array $row, string|array $where = ""): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder()->delete($this->UpdateTable);
        $where = is_array($where) ? $this->arrayToFilter($where) : $where;
        if ($row) {
            if (array_key_exists('Option_ID', $row)) {
                AddFilter($where, QuotedName('Option_ID', $this->Dbid) . '=' . QuotedValue($row['Option_ID'], $this->Option_ID->DataType, $this->Dbid));
            }
        }
        return $queryBuilder->where($where != "" ? $where : "0=1");
    }

    // Delete
    public function delete(array $row, string|array $where = "", bool $currentFilter = false): int|bool
    {
        $success = true;
        if ($success) {
            try {
                // Check soft delete
                $softDelete = !IsEmpty($this->SoftDeleteFieldName)
                    && (
                        !$this->HardDelete
                        || $row[$this->SoftDeleteFieldName] === null
                        || $this->TimeAware && (new DateTimeImmutable($row[$this->SoftDeleteFieldName]))->getTimestamp() > time()
                    );
                if ($softDelete) { // Soft delete
                    $newRow = $row;
                    if ($this->TimeAware && IsEmpty($row[$this->SoftDeleteFieldName])) { // Set expiration datetime
                        $newRow[$this->SoftDeleteFieldName] = StdDateTime(strtotime($this->SoftDeleteTimeAwarePeriod));
                    } else { // Set now
                        $newRow[$this->SoftDeleteFieldName] = StdCurrentDateTime();
                    }
                    $success = $this->update($newRow, $this->getRecordFilter($row), $row);
                } else { // Delete permanently
                    $where = is_array($where) ? $this->arrayToFilter($where) : $where;
                    $filter = $currentFilter ? $this->CurrentFilter : "";
                    AddFilter($where, $filter);
                    $success = $this->deleteSql($row, $where)->executeStatement();
                    $this->DbErrorMessage = "";
                }
				if ($success) {
                    $this->clearLookupCache();
                }
            } catch (Exception $e) {
                $success = false;
                $this->DbErrorMessage = $e->getMessage();
            }
        }
        return $success;
    }

	// Clear lookup cache for this table
    protected function clearLookupCache()
    {
        Container("result.cache")->clear("lookup.cache." . $this->TableVar . ".");
    }

    // Load DbValue from result set or array
    protected function loadDbValues(?array $row)
    {
        if (!is_array($row)) {
            return;
        }
        $this->Option_ID->DbValue = $row['Option_ID'];
        $this->Option_Default->DbValue = $row['Option_Default'];
        $this->Show_Announcement->DbValue = $row['Show_Announcement'];
        $this->Use_Announcement_Table->DbValue = $row['Use_Announcement_Table'];
        $this->Maintenance_Mode->DbValue = $row['Maintenance_Mode'];
        $this->Maintenance_Finish_DateTime->DbValue = $row['Maintenance_Finish_DateTime'];
        $this->Auto_Normal_After_Maintenance->DbValue = $row['Auto_Normal_After_Maintenance'];
    }

    // Delete uploaded files
    public function deleteUploadedFiles(array $row)
    {
        $this->loadDbValues($row);
    }

    // Record filter WHERE clause
    protected function sqlKeyFilter(): string
    {
        return "`Option_ID` = @Option_ID@";
    }

    // Get Key from record
    public function getKeyFromRecord(array $row, ?string $keySeparator = null): string
    {
        $keys = [];
        $val = $row['Option_ID'];
        if (IsEmpty($val)) {
            return "";
        } else {
            $keys[] = $val;
        }
        $keySeparator ??= Config("COMPOSITE_KEY_SEPARATOR");
        return implode($keySeparator, $keys);
    }

    // Get Key
    public function getKey(bool $current = false, ?string $keySeparator = null): string
    {
        $keys = [];
        $val = $current ? $this->Option_ID->CurrentValue : $this->Option_ID->OldValue;
        if (IsEmpty($val)) {
            return "";
        } else {
            $keys[] = $val;
        }
        $keySeparator ??= Config("COMPOSITE_KEY_SEPARATOR");
        return implode($keySeparator, $keys);
    }

    // Set Key
    public function setKey(string $key, bool $current = false, ?string $keySeparator = null): void
    {
        $keySeparator ??= Config("COMPOSITE_KEY_SEPARATOR");
        $this->OldKey = $key;
        $keys = explode($keySeparator, $this->OldKey);
        if (count($keys) == 1) {
            if ($current) {
                $this->Option_ID->CurrentValue = $keys[0];
            } else {
                $this->Option_ID->OldValue = $keys[0];
            }
        }
    }

    // Get record filter
    public function getRecordFilter(?array $row = null, bool $current = false): string
    {
        $keyFilter = $this->sqlKeyFilter();
        if (is_array($row)) {
            $val = array_key_exists('Option_ID', $row) ? $row['Option_ID'] : null;
        } else {
            $val = !IsEmpty($this->Option_ID->OldValue) && !$current ? $this->Option_ID->OldValue : $this->Option_ID->CurrentValue;
        }
        if (!is_numeric($val)) {
            return "0=1"; // Invalid key
        }
        if ($val === null) {
            return "0=1"; // Invalid key
        } else {
            $keyFilter = str_replace("@Option_ID@", AdjustSql($val), $keyFilter); // Replace key value
        }
        return $keyFilter;
    }

    // Return page URL
    public function getReturnUrl(): string
    {
        $referUrl = ReferUrl();
        $referPageName = ReferPageName();
        $name = AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_RETURN_URL"));
        // Get referer URL automatically
        if ($referUrl != "" && $referPageName != CurrentPageName() && $referPageName != "login") { // Referer not same page or login page
            Session($name, $referUrl); // Save to Session
        }
        return Session($name) ?? GetUrl("settingslist");
    }

    // Set return page URL
    public function setReturnUrl(string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_RETURN_URL")), $v);
    }

    // Get modal caption
    public function getModalCaption(string $pageName): string
    {
        return match ($pageName) {
            "settingsview" => $this->language->phrase("View"),
            "settingsedit" => $this->language->phrase("Edit"),
            "settingsadd" => $this->language->phrase("Add"),
            default => ""
        };
    }

    // Default route URL
    public function getDefaultRouteUrl(): string
    {
        return "settingslist";
    }

    // API page name
    public function getApiPageName(string $action): string
    {
        return match (strtolower($action)) {
            Config("API_VIEW_ACTION") => "SettingsView",
            Config("API_ADD_ACTION") => "SettingsAdd",
            Config("API_EDIT_ACTION") => "SettingsEdit",
            Config("API_DELETE_ACTION") => "SettingsDelete",
            Config("API_LIST_ACTION") => "SettingsList",
            default => ""
        };
    }

    // Current URL
    public function getCurrentUrl(string $parm = ""): string
    {
        $url = CurrentPageUrl(false);
        if ($parm != "") {
            $url = $this->keyUrl($url, $parm);
        } else {
            $url = $this->keyUrl($url, Config("TABLE_SHOW_DETAIL") . "=");
        }
        return $this->addMasterUrl($url);
    }

    // List URL
    public function getListUrl(): string
    {
        return "settingslist";
    }

    // View URL
    public function getViewUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = $this->keyUrl("settingsview", $parm);
        } else {
            $url = $this->keyUrl("settingsview", Config("TABLE_SHOW_DETAIL") . "=");
        }
        return $this->addMasterUrl($url);
    }

    // Add URL
    public function getAddUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = "settingsadd?" . $parm;
        } else {
            $url = "settingsadd";
        }
        return $this->addMasterUrl($url);
    }

    // Edit URL
    public function getEditUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("settingsedit", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline edit URL
    public function getInlineEditUrl(): string
    {
        $url = $this->keyUrl("settingslist", "action=edit");
        return $this->addMasterUrl($url);
    }

    // Copy URL
    public function getCopyUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("settingsadd", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline copy URL
    public function getInlineCopyUrl(): string
    {
        $url = $this->keyUrl("settingslist", "action=copy");
        return $this->addMasterUrl($url);
    }

    // Delete URL
    public function getDeleteUrl(string $parm = ""): string
    {
        if ($this->UseAjaxActions && ConvertToBool(Param("infinitescroll")) && CurrentPageID() == "list") {
            return $this->keyUrl(GetApiUrl(Config("API_DELETE_ACTION") . "/" . $this->TableVar));
        } else {
            return $this->keyUrl("settingsdelete", $parm);
        }
    }

    // Add master url
    public function addMasterUrl(string $url): string
    {
        return $url;
    }

    public function keyToJson(bool $htmlEncode = false): string
    {
        $json = "";
        $json .= "\"Option_ID\":" . VarToJson($this->Option_ID->CurrentValue, "number");
        $json = "{" . $json . "}";
        if ($htmlEncode) {
            $json = HtmlEncode($json);
        }
        return $json;
    }

    // Add key value to URL
    public function keyUrl(string $url, string $parm = ""): string
    {
        if ($this->Option_ID->CurrentValue !== null) {
            $url .= "/" . $this->encodeKeyValue($this->Option_ID->CurrentValue);
        } else {
            return "javascript:ew.alert(ew.language.phrase('InvalidRecord'));";
        }
        if ($parm != "") {
            $url .= "?" . $parm;
        }
        return $url;
    }

    // Render sort
    public function renderFieldHeader(DbField $fld): string
    {
        $sortUrl = "";
        $attrs = "";
        if ($this->PageID != "grid" && $fld->Sortable) {
            $sortUrl = $this->sortUrl($fld);
            $attrs = ' role="button" data-ew-action="sort" data-ajax="' . ($this->UseAjaxActions ? "true" : "false") . '" data-sort-url="' . $sortUrl . '" data-sort-type="1"';
            if ($this->ContextClass) { // Add context
                $attrs .= ' data-context="' . HtmlEncode($this->ContextClass) . '"';
            }
        }
        $html = '<div class="ew-table-header-caption"' . $attrs . '>' . $fld->caption() . '</div>';
        if ($sortUrl) {
            $html .= '<div class="ew-table-header-sort">' . $fld->getSortIcon() . '</div>';
        }
        if ($this->PageID != "grid" && !$this->isExport() && $fld->UseFilter && $this->security->canSearch()) {
            $html .= '<div class="ew-filter-dropdown-btn" data-ew-action="filter" data-table="' . $fld->TableVar . '" data-field="' . $fld->FieldVar .
                '"><div class="ew-table-header-filter" role="button" aria-haspopup="true">' . $this->language->phrase("Filter") .
                (is_array($fld->EditValue) ? sprintf($this->language->phrase("FilterCount"), count($fld->EditValue)) : '') .
                '</div></div>';
        }
        $html = '<div class="ew-table-header-btn">' . $html . '</div>';
        if ($this->UseCustomTemplate) {
            $scriptId = str_replace("{id}", $fld->TableVar . "_" . $fld->Param, "tpc_{id}");
            $html = '<template id="' . $scriptId . '">' . $html . '</template>';
        }
        return $html;
    }

    // Sort URL
    public function sortUrl(DbField $fld): string
    {
        global $DashboardReport;
        if (
            $this->CurrentAction || $this->isExport()
        || 
            in_array($fld->Type, [128, 204, 205])
        ) { // Unsortable data type
                return "";
        } elseif ($fld->Sortable) {
            $urlParm = "order=" . urlencode($fld->Name) . "&amp;ordertype=" . $fld->getNextSort();
            if ($DashboardReport) {
                $urlParm .= "&amp;" . Config("PAGE_DASHBOARD") . "=" . $DashboardReport;
            }
            return $this->addMasterUrl($this->CurrentPageName . "?" . $urlParm);
        } else {
            return "";
        }
    }

    // Get record keys from Post/Get/Session
    public function getRecordKeys(): array
    {
        $arKeys = [];
        $arKey = [];
        if (Param("key_m") !== null) {
            $arKeys = Param("key_m");
            $cnt = count($arKeys);
        } else {
            $isApi = IsApi();
            $keyValues = $isApi
                ? (Route(0) == "export"
                    ? array_map(fn ($i) => Route($i + 3), range(0, 0))  // Export API
                    : array_map(fn ($i) => Route($i + 2), range(0, 0))) // Other API
                : []; // Non-API
            if (($keyValue = Param("Option_ID") ?? Route("Option_ID")) !== null) {
                $arKeys[] = $keyValue;
            } elseif ($isApi && (($keyValue = Key(0) ?? $keyValues[0] ?? null) !== null)) {
                $arKeys[] = $keyValue;
            } else {
                $arKeys = null; // Do not setup
            }
        }
        // Check keys
        $ar = [];
        if (is_array($arKeys)) {
            foreach ($arKeys as $key) {
                if (!is_numeric($key)) {
                    continue;
                }
                $ar[] = $key;
            }
        }
        return $ar;
    }

    // Get filter from records
    public function getFilterFromRecords(array $rows): string
    {
        return implode(" OR ", array_map(fn($row) => "(" . $this->getRecordFilter($row) . ")", $rows));
    }

    // Get filter from record keys
    public function getFilterFromRecordKeys(bool $setCurrent = true): string
    {
        $arKeys = $this->getRecordKeys();
        $keyFilter = "";
        foreach ($arKeys as $key) {
            if ($setCurrent) {
                $this->Option_ID->CurrentValue = $key;
            } else {
                $this->Option_ID->OldValue = $key;
            }
            AddFilter($keyFilter, $this->getRecordFilter(null, $setCurrent), "OR");
        }
        return $keyFilter;
    }

    // Load result set based on filter/sort
    public function loadRecords(string $filter, string $sort = ""): Result
    {
        $sql = $this->getSql($filter, $sort); // Set up filter (WHERE Clause) / sort (ORDER BY Clause)
        $conn = $this->getConnection();
        return $conn->executeQuery($sql);
    }

    // Load row values from record
    public function loadListRowValues(array &$row)
    {
        $this->Option_ID->setDbValue($row['Option_ID']);
        $this->Option_Default->setDbValue($row['Option_Default']);
        $this->Show_Announcement->setDbValue($row['Show_Announcement']);
        $this->Use_Announcement_Table->setDbValue($row['Use_Announcement_Table']);
        $this->Maintenance_Mode->setDbValue($row['Maintenance_Mode']);
        $this->Maintenance_Finish_DateTime->setDbValue($row['Maintenance_Finish_DateTime']);
        $this->Auto_Normal_After_Maintenance->setDbValue($row['Auto_Normal_After_Maintenance']);
    }

    // Render list content
    public function renderListContent(string $filter)
    {
        global $Response;
        $container = Container();
        $listPage = "SettingsList";
        $listClass = PROJECT_NAMESPACE . $listPage;
        $page = $container->make($listClass);
        $page->loadRecordsetFromFilter($filter);
        $view = $container->get("app.view");
        $template = $listPage . ".php"; // View
        $GLOBALS["Title"] ??= $page->Title; // Title
        try {
            $Response = $view->render($Response, $template, $GLOBALS);
        } finally {
            $page->terminate(); // Terminate page and clean up
        }
    }

    // Render list row values
    public function renderListRow()
    {
        global $CurrentLanguage;

        // Call Row Rendering event
        $this->rowRendering();

        // Common render codes

        // Option_ID

        // Option_Default

        // Show_Announcement

        // Use_Announcement_Table

        // Maintenance_Mode

        // Maintenance_Finish_DateTime

        // Auto_Normal_After_Maintenance

        // Option_ID
        $this->Option_ID->ViewValue = $this->Option_ID->CurrentValue;

        // Option_Default
        if (ConvertToBool($this->Option_Default->CurrentValue)) {
            $this->Option_Default->ViewValue = $this->Option_Default->tagCaption(1) != "" ? $this->Option_Default->tagCaption(1) : "Yes";
        } else {
            $this->Option_Default->ViewValue = $this->Option_Default->tagCaption(2) != "" ? $this->Option_Default->tagCaption(2) : "No";
        }

        // Show_Announcement
        if (ConvertToBool($this->Show_Announcement->CurrentValue)) {
            $this->Show_Announcement->ViewValue = $this->Show_Announcement->tagCaption(1) != "" ? $this->Show_Announcement->tagCaption(1) : "Yes";
        } else {
            $this->Show_Announcement->ViewValue = $this->Show_Announcement->tagCaption(2) != "" ? $this->Show_Announcement->tagCaption(2) : "No";
        }

        // Use_Announcement_Table
        if (ConvertToBool($this->Use_Announcement_Table->CurrentValue)) {
            $this->Use_Announcement_Table->ViewValue = $this->Use_Announcement_Table->tagCaption(2) != "" ? $this->Use_Announcement_Table->tagCaption(2) : "Yes";
        } else {
            $this->Use_Announcement_Table->ViewValue = $this->Use_Announcement_Table->tagCaption(1) != "" ? $this->Use_Announcement_Table->tagCaption(1) : "No";
        }

        // Maintenance_Mode
        if (ConvertToBool($this->Maintenance_Mode->CurrentValue)) {
            $this->Maintenance_Mode->ViewValue = $this->Maintenance_Mode->tagCaption(2) != "" ? $this->Maintenance_Mode->tagCaption(2) : "Yes";
        } else {
            $this->Maintenance_Mode->ViewValue = $this->Maintenance_Mode->tagCaption(1) != "" ? $this->Maintenance_Mode->tagCaption(1) : "No";
        }

        // Maintenance_Finish_DateTime
        $this->Maintenance_Finish_DateTime->ViewValue = $this->Maintenance_Finish_DateTime->CurrentValue;
        $this->Maintenance_Finish_DateTime->ViewValue = FormatDateTime($this->Maintenance_Finish_DateTime->ViewValue, $this->Maintenance_Finish_DateTime->formatPattern());

        // Auto_Normal_After_Maintenance
        if (ConvertToBool($this->Auto_Normal_After_Maintenance->CurrentValue)) {
            $this->Auto_Normal_After_Maintenance->ViewValue = $this->Auto_Normal_After_Maintenance->tagCaption(1) != "" ? $this->Auto_Normal_After_Maintenance->tagCaption(1) : "Yes";
        } else {
            $this->Auto_Normal_After_Maintenance->ViewValue = $this->Auto_Normal_After_Maintenance->tagCaption(2) != "" ? $this->Auto_Normal_After_Maintenance->tagCaption(2) : "No";
        }

        // Option_ID
        $this->Option_ID->HrefValue = "";
        $this->Option_ID->TooltipValue = "";

        // Option_Default
        $this->Option_Default->HrefValue = "";
        $this->Option_Default->TooltipValue = "";

        // Show_Announcement
        $this->Show_Announcement->HrefValue = "";
        $this->Show_Announcement->TooltipValue = "";

        // Use_Announcement_Table
        $this->Use_Announcement_Table->HrefValue = "";
        $this->Use_Announcement_Table->TooltipValue = "";

        // Maintenance_Mode
        $this->Maintenance_Mode->HrefValue = "";
        $this->Maintenance_Mode->TooltipValue = "";

        // Maintenance_Finish_DateTime
        $this->Maintenance_Finish_DateTime->HrefValue = "";
        $this->Maintenance_Finish_DateTime->TooltipValue = "";

        // Auto_Normal_After_Maintenance
        $this->Auto_Normal_After_Maintenance->HrefValue = "";
        $this->Auto_Normal_After_Maintenance->TooltipValue = "";

        // Call Row Rendered event
        $this->rowRendered();

        // Save data for Custom Template
        $this->Rows[] = $this->customTemplateFieldValues();
    }

    // Aggregate list row values
    public function aggregateListRowValues()
    {
    }

    // Aggregate list row (for rendering)
    public function aggregateListRow()
    {
        // Call Row Rendered event
        $this->rowRendered();
    }

    // Export data in HTML/CSV/Word/Excel/Email/PDF format
	// Now including Export Print (printer friendly), modification by Masino Sinaga, September 11, 2023 
    public function exportDocument(AbstractExportBase $doc, Result $result, int $startRec = 1, int $stopRec = 1, string $exportPageType = "")
    {
        if (!$result || !$doc) {
            return;
        }
        if (!$doc->ExportCustom) {
            // Write header
            $doc->exportTableHeader();
            if ($doc->Horizontal) { // Horizontal format, write header
                $doc->beginExportRow();
                if ($exportPageType == "view") {
                    $doc->exportCaption($this->Option_ID);
                    $doc->exportCaption($this->Option_Default);
                    $doc->exportCaption($this->Show_Announcement);
                    $doc->exportCaption($this->Use_Announcement_Table);
                    $doc->exportCaption($this->Maintenance_Mode);
                    $doc->exportCaption($this->Maintenance_Finish_DateTime);
                    $doc->exportCaption($this->Auto_Normal_After_Maintenance);
                } else {
                    $doc->exportCaption($this->Option_ID);
                    $doc->exportCaption($this->Option_Default);
                    $doc->exportCaption($this->Show_Announcement);
                    $doc->exportCaption($this->Use_Announcement_Table);
                    $doc->exportCaption($this->Maintenance_Mode);
                    $doc->exportCaption($this->Maintenance_Finish_DateTime);
                    $doc->exportCaption($this->Auto_Normal_After_Maintenance);
                }
                $doc->endExportRow();
            }
        }
        $recCnt = $startRec - 1;
        $stopRec = $stopRec > 0 ? $stopRec : PHP_INT_MAX;
		// Begin of modification Record Number in Exported Data by Masino Sinaga, September 11, 2023
		$seqRec = 0;
		if (CurrentPageID() == "view") { // Modified by Masino Sinaga, September 11, 2023, reset seq. number in View Page
		    $_SESSION["First_Record"] = 0;
			$seqRec = (empty($_SESSION["First_Record"])) ? 0 : $_SESSION["First_Record"] - 1; 
		} else {
			$seqRec = (empty($_SESSION["First_Record"])) ? $recCnt : $_SESSION["First_Record"] - 1;
		}
		// End of modification Record Number in Exported Data by Masino Sinaga, September 11, 2023
        while (($row = $result->fetchAssociative()) && $recCnt < $stopRec) {
            $recCnt++;
			$seqRec++; // Record Number in Exported Data by Masino Sinaga, September 11, 2023
            if ($recCnt >= $startRec) {
                $rowCnt = $recCnt - $startRec + 1;

                // Page break
				// Begin of modification PageBreak for Export to PDF dan Export to Word by Masino Sinaga, September 11, 2023
                if ($this->ExportPageBreakCount > 0 && ($this->Export == "pdf" || $this->Export =="word")) {
                    if ($rowCnt > 1 && ($rowCnt - 1) % $this->ExportPageBreakCount == 0) {
                        $doc->exportPageBreak();
						$doc->beginExportRow(); // Begin of modification by Masino Sinaga, September 11, 2023, table header will be repeated at the top of each page after page break, must be handled from here for Export to PDF that has the possibility to repeat the table header column in each top of page
						$doc->exportCaption($this->Option_ID);
						$doc->exportCaption($this->Option_Default);
						$doc->exportCaption($this->Show_Announcement);
						$doc->exportCaption($this->Use_Announcement_Table);
						$doc->exportCaption($this->Maintenance_Mode);
						$doc->exportCaption($this->Maintenance_Finish_DateTime);
						$doc->exportCaption($this->Auto_Normal_After_Maintenance);
						$doc->endExportRow(); // End of modification by Masino Sinaga, table header will be repeated at the top of each page after page break, September 11, 2023
                    }
                }
				// End of modification PageBreak for Export to PDF dan Export to Word by Masino Sinaga, September 11, 2023
                $this->loadListRowValues($row);

                // Render row
                $this->RowType = RowType::VIEW; // Render view
                $this->resetAttributes();
                $this->renderListRow();
                if (!$doc->ExportCustom) {
                    $doc->beginExportRow($rowCnt); // Allow CSS styles if enabled
                    if ($exportPageType == "view") {
                        $doc->exportField($this->Option_ID);
                        $doc->exportField($this->Option_Default);
                        $doc->exportField($this->Show_Announcement);
                        $doc->exportField($this->Use_Announcement_Table);
                        $doc->exportField($this->Maintenance_Mode);
                        $doc->exportField($this->Maintenance_Finish_DateTime);
                        $doc->exportField($this->Auto_Normal_After_Maintenance);
                    } else {
                        $doc->exportField($this->Option_ID);
                        $doc->exportField($this->Option_Default);
                        $doc->exportField($this->Show_Announcement);
                        $doc->exportField($this->Use_Announcement_Table);
                        $doc->exportField($this->Maintenance_Mode);
                        $doc->exportField($this->Maintenance_Finish_DateTime);
                        $doc->exportField($this->Auto_Normal_After_Maintenance);
                    }
                    $doc->endExportRow($rowCnt);
                }
            }

            // Call Row Export server event
            if ($doc->ExportCustom) {
                $this->rowExport($doc, $row);
            }
        }
        if (!$doc->ExportCustom) {
            $doc->exportTableFooter();
        }
    }

    // Render lookup field for view
    public function renderLookupForView(string $name, mixed $value): mixed
    {
        $this->RowType = RowType::VIEW;
        return $value;
    }

    // Render lookup field for edit
    public function renderLookupForEdit(string $name, mixed $value): mixed
    {
        $this->RowType = RowType::EDIT;
        return $value;
    }

    // Get file data
    public function getFileData(string $fldparm, string $key, bool $resize, int $width = 0, int $height = 0, array $plugins = []): Response
    {
        global $DownloadFileName;

        // No binary fields
        return $response;
    }

    // Table level events

    // Table Load event
    public function tableLoad(): void
    {
        // Enter your code here
    }

    // Records Selecting event
    public function recordsSelecting(string &$filter): void
    {
        // Enter your code here
    }

    // Records Selected event
    public function recordsSelected(Result $result): void
    {
        //Log("Records Selected");
    }

    // Records Search Validated event
    public function recordsSearchValidated(): void
    {
        // Example:
        //$this->MyField1->AdvancedSearch->SearchValue = "your search criteria"; // Search value
    }

    // Records Searching event
    public function recordsSearching(string &$filter): void
    {
        // Enter your code here
    }

    // Row_Selecting event
    public function rowSelecting(string &$filter): void
    {
        // Enter your code here
    }

    // Row Selected event
    public function rowSelected(array &$row): void
    {
        //Log("Row Selected");
    }

    // Row Inserting event
    public function rowInserting(?array $oldRow, array &$newRow): ?bool
    {
        // Enter your code here
        // To cancel, set return value to false
        // To skip for grid insert/update, set return value to null
        return true;
    }

    // Row Inserted event
    public function rowInserted(?array $oldRow, array $newRow): void
    {
        $record_count = ExecuteScalar("SELECT COUNT(*) FROM " . Config("MS_SETTINGS_TABLE"));
    	if ($record_count > 1) {
    		if ($rsnew["Option_Default"] == "Y") {
    			Execute("UPDATE " . Config("MS_SETTINGS_TABLE") . " SET Option_Default = 'N' WHERE Option_ID <> " . $newRow["Option_ID"]);
    		}
    	}
    }
    // Row Updating event
    public function rowUpdating(array $oldRow, array &$newRow): ?bool
    {
        // Enter your code here
        // To cancel, set return value to false
        // To skip for grid insert/update, set return value to null
        $record_count = ExecuteScalar("SELECT COUNT(*) FROM " . Config("MS_SETTINGS_TABLE"));
    	if ($record_count == 1 && $newRow["Option_Default"] == "N") {
    		$this->setFailureMessage("You cannot disable Option Default for this current record, since only one record exists in configuration table.");
    		return FALSE;
    	}
        return true;
    }
    // Row Updated event
    public function rowUpdated(array $oldRow, array $newRow): void
    {
        $this->setSuccessMessage("Please logout and login again to apply the changes.");
    }

    // Row Update Conflict event
    public function rowUpdateConflict(array $oldRow, array &$newRow): bool
    {
        // Enter your code here
        // To ignore conflict, set return value to false
        return true;
    }

    // Grid Inserting event
    public function gridInserting(): bool
    {
        // Enter your code here
        // To reject grid insert, set return value to false
        return true;
    }

    // Grid Inserted event
    public function gridInserted(array $rows): void
    {
        //Log("Grid Inserted");
    }

    // Grid Updating event
    public function gridUpdating(array $rows): bool
    {
        // Enter your code here
        // To reject grid update, set return value to false
        return true;
    }

    // Grid Updated event
    public function gridUpdated(array $oldRows, array $newRows): void
    {
        //Log("Grid Updated");
    }

    // Row Deleting event
    public function rowDeleting(array $row): ?bool
    {
        $record_count = ExecuteScalar("SELECT COUNT(*) FROM " . Config("MS_SETTINGS_TABLE"));
    	if ($record_count == 1) {
    		$this->setFailureMessage("You cannot delete this current record, since only one record exists in configuration table.");
    		return FALSE;
    	}
        return true;
    }

    // Row Deleted event
    public function rowDeleted(array $row): void
    {
        //Log("Row Deleted");
    }

    // Email Sending event
    public function emailSending(Email $email, array $args): bool
    {
        //var_dump($email, $args); exit();
        return true;
    }

    // Lookup Selecting event
    public function lookupSelecting(DbField $field, string &$filter): void
    {
        //var_dump($field->Name, $field->Lookup, $filter); // Uncomment to view the filter
        // Enter your code here
    }

    // Row Rendering event
    public function rowRendering(): void
    {
        // Enter your code here
    }

    // Row Rendered event
    public function rowRendered(): void
    {
        // To view properties of field class, use:
        //var_dump($this-><FieldName>);
    }

    // User ID Filtering event
    public function userIdFiltering(string &$filter): void
    {
        // Enter your code here
    }
}
