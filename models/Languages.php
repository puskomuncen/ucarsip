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
 * Table class for languages
 */
class Languages extends DbTable implements LookupTableInterface
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
    public DbField $Language_Code;
    public DbField $Language_Name;
    public DbField $Default;
    public DbField $Site_Logo;
    public DbField $Site_Title;
    public DbField $Default_Thousands_Separator;
    public DbField $Default_Decimal_Point;
    public DbField $Default_Currency_Symbol;
    public DbField $Default_Money_Thousands_Separator;
    public DbField $Default_Money_Decimal_Point;
    public DbField $Terms_And_Condition_Text;
    public DbField $Announcement_Text;
    public DbField $About_Text;

    // Page ID
    public string $PageID = ""; // To be set by subclass

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        $this->TableVar = "languages";
        $this->TableName = 'languages';
        $this->TableType = "TABLE";
        $this->ImportUseTransaction = $this->supportsTransaction() && Config("IMPORT_USE_TRANSACTION");
        $this->UseTransaction = $this->supportsTransaction() && Config("USE_TRANSACTION");
        $this->UpdateTable = "languages"; // Update table
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

        // Language_Code
        $this->Language_Code = new DbField(
            $this, // Table
            'x_Language_Code', // Variable name
            'Language_Code', // Name
            '`Language_Code`', // Expression
            '`Language_Code`', // Basic search expression
            129, // Type
            5, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Language_Code`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Language_Code->InputTextType = "text";
        $this->Language_Code->Raw = true;
        $this->Language_Code->IsPrimaryKey = true; // Primary key field
        $this->Language_Code->Nullable = false; // NOT NULL field
        $this->Language_Code->Required = true; // Required field
        $this->Language_Code->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Language_Code'] = &$this->Language_Code;

        // Language_Name
        $this->Language_Name = new DbField(
            $this, // Table
            'x_Language_Name', // Variable name
            'Language_Name', // Name
            '`Language_Name`', // Expression
            '`Language_Name`', // Basic search expression
            200, // Type
            20, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Language_Name`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Language_Name->InputTextType = "text";
        $this->Language_Name->Nullable = false; // NOT NULL field
        $this->Language_Name->Required = true; // Required field
        $this->Language_Name->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Language_Name'] = &$this->Language_Name;

        // Default
        $this->Default = new DbField(
            $this, // Table
            'x_Default', // Variable name
            'Default', // Name
            '`Default`', // Expression
            '`Default`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Default`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Default->addMethod("getDefault", fn() => "N");
        $this->Default->InputTextType = "text";
        $this->Default->Raw = true;
        $this->Default->setDataType(DataType::BOOLEAN);
        $this->Default->TrueValue = "Y";
        $this->Default->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Default->Lookup = new Lookup($this->Default, 'languages', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->Default->Lookup = new Lookup($this->Default, 'languages', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Default->Lookup = new Lookup($this->Default, 'languages', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Default->OptionCount = 2;
        $this->Default->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Default'] = &$this->Default;

        // Site_Logo
        $this->Site_Logo = new DbField(
            $this, // Table
            'x_Site_Logo', // Variable name
            'Site_Logo', // Name
            '`Site_Logo`', // Expression
            '`Site_Logo`', // Basic search expression
            200, // Type
            100, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Site_Logo`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Site_Logo->InputTextType = "text";
        $this->Site_Logo->Nullable = false; // NOT NULL field
        $this->Site_Logo->Required = true; // Required field
        $this->Site_Logo->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Site_Logo'] = &$this->Site_Logo;

        // Site_Title
        $this->Site_Title = new DbField(
            $this, // Table
            'x_Site_Title', // Variable name
            'Site_Title', // Name
            '`Site_Title`', // Expression
            '`Site_Title`', // Basic search expression
            200, // Type
            100, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Site_Title`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Site_Title->InputTextType = "text";
        $this->Site_Title->Nullable = false; // NOT NULL field
        $this->Site_Title->Required = true; // Required field
        $this->Site_Title->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Site_Title'] = &$this->Site_Title;

        // Default_Thousands_Separator
        $this->Default_Thousands_Separator = new DbField(
            $this, // Table
            'x_Default_Thousands_Separator', // Variable name
            'Default_Thousands_Separator', // Name
            '`Default_Thousands_Separator`', // Expression
            '`Default_Thousands_Separator`', // Basic search expression
            200, // Type
            5, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Default_Thousands_Separator`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Default_Thousands_Separator->InputTextType = "text";
        $this->Default_Thousands_Separator->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Default_Thousands_Separator'] = &$this->Default_Thousands_Separator;

        // Default_Decimal_Point
        $this->Default_Decimal_Point = new DbField(
            $this, // Table
            'x_Default_Decimal_Point', // Variable name
            'Default_Decimal_Point', // Name
            '`Default_Decimal_Point`', // Expression
            '`Default_Decimal_Point`', // Basic search expression
            200, // Type
            5, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Default_Decimal_Point`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Default_Decimal_Point->InputTextType = "text";
        $this->Default_Decimal_Point->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Default_Decimal_Point'] = &$this->Default_Decimal_Point;

        // Default_Currency_Symbol
        $this->Default_Currency_Symbol = new DbField(
            $this, // Table
            'x_Default_Currency_Symbol', // Variable name
            'Default_Currency_Symbol', // Name
            '`Default_Currency_Symbol`', // Expression
            '`Default_Currency_Symbol`', // Basic search expression
            200, // Type
            10, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Default_Currency_Symbol`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Default_Currency_Symbol->InputTextType = "text";
        $this->Default_Currency_Symbol->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Default_Currency_Symbol'] = &$this->Default_Currency_Symbol;

        // Default_Money_Thousands_Separator
        $this->Default_Money_Thousands_Separator = new DbField(
            $this, // Table
            'x_Default_Money_Thousands_Separator', // Variable name
            'Default_Money_Thousands_Separator', // Name
            '`Default_Money_Thousands_Separator`', // Expression
            '`Default_Money_Thousands_Separator`', // Basic search expression
            200, // Type
            5, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Default_Money_Thousands_Separator`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Default_Money_Thousands_Separator->InputTextType = "text";
        $this->Default_Money_Thousands_Separator->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Default_Money_Thousands_Separator'] = &$this->Default_Money_Thousands_Separator;

        // Default_Money_Decimal_Point
        $this->Default_Money_Decimal_Point = new DbField(
            $this, // Table
            'x_Default_Money_Decimal_Point', // Variable name
            'Default_Money_Decimal_Point', // Name
            '`Default_Money_Decimal_Point`', // Expression
            '`Default_Money_Decimal_Point`', // Basic search expression
            200, // Type
            5, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Default_Money_Decimal_Point`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Default_Money_Decimal_Point->InputTextType = "text";
        $this->Default_Money_Decimal_Point->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Default_Money_Decimal_Point'] = &$this->Default_Money_Decimal_Point;

        // Terms_And_Condition_Text
        $this->Terms_And_Condition_Text = new DbField(
            $this, // Table
            'x_Terms_And_Condition_Text', // Variable name
            'Terms_And_Condition_Text', // Name
            '`Terms_And_Condition_Text`', // Expression
            '`Terms_And_Condition_Text`', // Basic search expression
            200, // Type
            65535, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Terms_And_Condition_Text`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXTAREA' // Edit Tag
        );
        $this->Terms_And_Condition_Text->InputTextType = "text";
        $this->Terms_And_Condition_Text->Nullable = false; // NOT NULL field
        $this->Terms_And_Condition_Text->Required = true; // Required field
        $this->Terms_And_Condition_Text->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Terms_And_Condition_Text'] = &$this->Terms_And_Condition_Text;

        // Announcement_Text
        $this->Announcement_Text = new DbField(
            $this, // Table
            'x_Announcement_Text', // Variable name
            'Announcement_Text', // Name
            '`Announcement_Text`', // Expression
            '`Announcement_Text`', // Basic search expression
            200, // Type
            65535, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Announcement_Text`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXTAREA' // Edit Tag
        );
        $this->Announcement_Text->InputTextType = "text";
        $this->Announcement_Text->Nullable = false; // NOT NULL field
        $this->Announcement_Text->Required = true; // Required field
        $this->Announcement_Text->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Announcement_Text'] = &$this->Announcement_Text;

        // About_Text
        $this->About_Text = new DbField(
            $this, // Table
            'x_About_Text', // Variable name
            'About_Text', // Name
            '`About_Text`', // Expression
            '`About_Text`', // Basic search expression
            200, // Type
            65535, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`About_Text`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXTAREA' // Edit Tag
        );
        $this->About_Text->InputTextType = "text";
        $this->About_Text->Nullable = false; // NOT NULL field
        $this->About_Text->Required = true; // Required field
        $this->About_Text->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['About_Text'] = &$this->About_Text;

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
        return ($this->SqlFrom != "") ? $this->SqlFrom : "languages";
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
            if (array_key_exists('Language_Code', $row)) {
                AddFilter($where, QuotedName('Language_Code', $this->Dbid) . '=' . QuotedValue($row['Language_Code'], $this->Language_Code->DataType, $this->Dbid));
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
        $this->Language_Code->DbValue = $row['Language_Code'];
        $this->Language_Name->DbValue = $row['Language_Name'];
        $this->Default->DbValue = $row['Default'];
        $this->Site_Logo->DbValue = $row['Site_Logo'];
        $this->Site_Title->DbValue = $row['Site_Title'];
        $this->Default_Thousands_Separator->DbValue = $row['Default_Thousands_Separator'];
        $this->Default_Decimal_Point->DbValue = $row['Default_Decimal_Point'];
        $this->Default_Currency_Symbol->DbValue = $row['Default_Currency_Symbol'];
        $this->Default_Money_Thousands_Separator->DbValue = $row['Default_Money_Thousands_Separator'];
        $this->Default_Money_Decimal_Point->DbValue = $row['Default_Money_Decimal_Point'];
        $this->Terms_And_Condition_Text->DbValue = $row['Terms_And_Condition_Text'];
        $this->Announcement_Text->DbValue = $row['Announcement_Text'];
        $this->About_Text->DbValue = $row['About_Text'];
    }

    // Delete uploaded files
    public function deleteUploadedFiles(array $row)
    {
        $this->loadDbValues($row);
    }

    // Record filter WHERE clause
    protected function sqlKeyFilter(): string
    {
        return "`Language_Code` = '@Language_Code@'";
    }

    // Get Key from record
    public function getKeyFromRecord(array $row, ?string $keySeparator = null): string
    {
        $keys = [];
        $val = $row['Language_Code'];
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
        $val = $current ? $this->Language_Code->CurrentValue : $this->Language_Code->OldValue;
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
                $this->Language_Code->CurrentValue = $keys[0];
            } else {
                $this->Language_Code->OldValue = $keys[0];
            }
        }
    }

    // Get record filter
    public function getRecordFilter(?array $row = null, bool $current = false): string
    {
        $keyFilter = $this->sqlKeyFilter();
        if (is_array($row)) {
            $val = array_key_exists('Language_Code', $row) ? $row['Language_Code'] : null;
        } else {
            $val = !IsEmpty($this->Language_Code->OldValue) && !$current ? $this->Language_Code->OldValue : $this->Language_Code->CurrentValue;
        }
        if ($val === null) {
            return "0=1"; // Invalid key
        } else {
            $keyFilter = str_replace("@Language_Code@", AdjustSql($val), $keyFilter); // Replace key value
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
        return Session($name) ?? GetUrl("languageslist");
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
            "languagesview" => $this->language->phrase("View"),
            "languagesedit" => $this->language->phrase("Edit"),
            "languagesadd" => $this->language->phrase("Add"),
            default => ""
        };
    }

    // Default route URL
    public function getDefaultRouteUrl(): string
    {
        return "languageslist";
    }

    // API page name
    public function getApiPageName(string $action): string
    {
        return match (strtolower($action)) {
            Config("API_VIEW_ACTION") => "LanguagesView",
            Config("API_ADD_ACTION") => "LanguagesAdd",
            Config("API_EDIT_ACTION") => "LanguagesEdit",
            Config("API_DELETE_ACTION") => "LanguagesDelete",
            Config("API_LIST_ACTION") => "LanguagesList",
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
        return "languageslist";
    }

    // View URL
    public function getViewUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = $this->keyUrl("languagesview", $parm);
        } else {
            $url = $this->keyUrl("languagesview", Config("TABLE_SHOW_DETAIL") . "=");
        }
        return $this->addMasterUrl($url);
    }

    // Add URL
    public function getAddUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = "languagesadd?" . $parm;
        } else {
            $url = "languagesadd";
        }
        return $this->addMasterUrl($url);
    }

    // Edit URL
    public function getEditUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("languagesedit", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline edit URL
    public function getInlineEditUrl(): string
    {
        $url = $this->keyUrl("languageslist", "action=edit");
        return $this->addMasterUrl($url);
    }

    // Copy URL
    public function getCopyUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("languagesadd", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline copy URL
    public function getInlineCopyUrl(): string
    {
        $url = $this->keyUrl("languageslist", "action=copy");
        return $this->addMasterUrl($url);
    }

    // Delete URL
    public function getDeleteUrl(string $parm = ""): string
    {
        if ($this->UseAjaxActions && ConvertToBool(Param("infinitescroll")) && CurrentPageID() == "list") {
            return $this->keyUrl(GetApiUrl(Config("API_DELETE_ACTION") . "/" . $this->TableVar));
        } else {
            return $this->keyUrl("languagesdelete", $parm);
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
        $json .= "\"Language_Code\":" . VarToJson($this->Language_Code->CurrentValue, "string");
        $json = "{" . $json . "}";
        if ($htmlEncode) {
            $json = HtmlEncode($json);
        }
        return $json;
    }

    // Add key value to URL
    public function keyUrl(string $url, string $parm = ""): string
    {
        if ($this->Language_Code->CurrentValue !== null) {
            $url .= "/" . $this->encodeKeyValue($this->Language_Code->CurrentValue);
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
            if (($keyValue = Param("Language_Code") ?? Route("Language_Code")) !== null) {
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
                $this->Language_Code->CurrentValue = $key;
            } else {
                $this->Language_Code->OldValue = $key;
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
        $this->Language_Code->setDbValue($row['Language_Code']);
        $this->Language_Name->setDbValue($row['Language_Name']);
        $this->Default->setDbValue($row['Default']);
        $this->Site_Logo->setDbValue($row['Site_Logo']);
        $this->Site_Title->setDbValue($row['Site_Title']);
        $this->Default_Thousands_Separator->setDbValue($row['Default_Thousands_Separator']);
        $this->Default_Decimal_Point->setDbValue($row['Default_Decimal_Point']);
        $this->Default_Currency_Symbol->setDbValue($row['Default_Currency_Symbol']);
        $this->Default_Money_Thousands_Separator->setDbValue($row['Default_Money_Thousands_Separator']);
        $this->Default_Money_Decimal_Point->setDbValue($row['Default_Money_Decimal_Point']);
        $this->Terms_And_Condition_Text->setDbValue($row['Terms_And_Condition_Text']);
        $this->Announcement_Text->setDbValue($row['Announcement_Text']);
        $this->About_Text->setDbValue($row['About_Text']);
    }

    // Render list content
    public function renderListContent(string $filter)
    {
        global $Response;
        $container = Container();
        $listPage = "LanguagesList";
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

        // Language_Code

        // Language_Name

        // Default

        // Site_Logo

        // Site_Title

        // Default_Thousands_Separator

        // Default_Decimal_Point

        // Default_Currency_Symbol

        // Default_Money_Thousands_Separator

        // Default_Money_Decimal_Point

        // Terms_And_Condition_Text

        // Announcement_Text

        // About_Text

        // Language_Code
        $this->Language_Code->ViewValue = $this->Language_Code->CurrentValue;

        // Language_Name
        $this->Language_Name->ViewValue = $this->Language_Name->CurrentValue;

        // Default
        if (ConvertToBool($this->Default->CurrentValue)) {
            $this->Default->ViewValue = $this->Default->tagCaption(1) != "" ? $this->Default->tagCaption(1) : "Yes";
        } else {
            $this->Default->ViewValue = $this->Default->tagCaption(2) != "" ? $this->Default->tagCaption(2) : "No";
        }

        // Site_Logo
        $this->Site_Logo->ViewValue = $this->Site_Logo->CurrentValue;

        // Site_Title
        $this->Site_Title->ViewValue = $this->Site_Title->CurrentValue;

        // Default_Thousands_Separator
        $this->Default_Thousands_Separator->ViewValue = $this->Default_Thousands_Separator->CurrentValue;

        // Default_Decimal_Point
        $this->Default_Decimal_Point->ViewValue = $this->Default_Decimal_Point->CurrentValue;

        // Default_Currency_Symbol
        $this->Default_Currency_Symbol->ViewValue = $this->Default_Currency_Symbol->CurrentValue;

        // Default_Money_Thousands_Separator
        $this->Default_Money_Thousands_Separator->ViewValue = $this->Default_Money_Thousands_Separator->CurrentValue;

        // Default_Money_Decimal_Point
        $this->Default_Money_Decimal_Point->ViewValue = $this->Default_Money_Decimal_Point->CurrentValue;

        // Terms_And_Condition_Text
        $this->Terms_And_Condition_Text->ViewValue = $this->Terms_And_Condition_Text->CurrentValue;

        // Announcement_Text
        $this->Announcement_Text->ViewValue = $this->Announcement_Text->CurrentValue;

        // About_Text
        $this->About_Text->ViewValue = $this->About_Text->CurrentValue;

        // Language_Code
        $this->Language_Code->HrefValue = "";
        $this->Language_Code->TooltipValue = "";

        // Language_Name
        $this->Language_Name->HrefValue = "";
        $this->Language_Name->TooltipValue = "";

        // Default
        $this->Default->HrefValue = "";
        $this->Default->TooltipValue = "";

        // Site_Logo
        $this->Site_Logo->HrefValue = "";
        $this->Site_Logo->TooltipValue = "";

        // Site_Title
        $this->Site_Title->HrefValue = "";
        $this->Site_Title->TooltipValue = "";

        // Default_Thousands_Separator
        $this->Default_Thousands_Separator->HrefValue = "";
        $this->Default_Thousands_Separator->TooltipValue = "";

        // Default_Decimal_Point
        $this->Default_Decimal_Point->HrefValue = "";
        $this->Default_Decimal_Point->TooltipValue = "";

        // Default_Currency_Symbol
        $this->Default_Currency_Symbol->HrefValue = "";
        $this->Default_Currency_Symbol->TooltipValue = "";

        // Default_Money_Thousands_Separator
        $this->Default_Money_Thousands_Separator->HrefValue = "";
        $this->Default_Money_Thousands_Separator->TooltipValue = "";

        // Default_Money_Decimal_Point
        $this->Default_Money_Decimal_Point->HrefValue = "";
        $this->Default_Money_Decimal_Point->TooltipValue = "";

        // Terms_And_Condition_Text
        $this->Terms_And_Condition_Text->HrefValue = "";
        $this->Terms_And_Condition_Text->TooltipValue = "";

        // Announcement_Text
        $this->Announcement_Text->HrefValue = "";
        $this->Announcement_Text->TooltipValue = "";

        // About_Text
        $this->About_Text->HrefValue = "";
        $this->About_Text->TooltipValue = "";

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
                    $doc->exportCaption($this->Language_Code);
                    $doc->exportCaption($this->Language_Name);
                    $doc->exportCaption($this->Default);
                    $doc->exportCaption($this->Site_Logo);
                    $doc->exportCaption($this->Site_Title);
                    $doc->exportCaption($this->Default_Thousands_Separator);
                    $doc->exportCaption($this->Default_Decimal_Point);
                    $doc->exportCaption($this->Default_Currency_Symbol);
                    $doc->exportCaption($this->Default_Money_Thousands_Separator);
                    $doc->exportCaption($this->Default_Money_Decimal_Point);
                    $doc->exportCaption($this->Terms_And_Condition_Text);
                    $doc->exportCaption($this->Announcement_Text);
                    $doc->exportCaption($this->About_Text);
                } else {
                    $doc->exportCaption($this->Language_Code);
                    $doc->exportCaption($this->Language_Name);
                    $doc->exportCaption($this->Default);
                    $doc->exportCaption($this->Site_Logo);
                    $doc->exportCaption($this->Site_Title);
                    $doc->exportCaption($this->Default_Thousands_Separator);
                    $doc->exportCaption($this->Default_Decimal_Point);
                    $doc->exportCaption($this->Default_Currency_Symbol);
                    $doc->exportCaption($this->Default_Money_Thousands_Separator);
                    $doc->exportCaption($this->Default_Money_Decimal_Point);
                    $doc->exportCaption($this->Terms_And_Condition_Text);
                    $doc->exportCaption($this->Announcement_Text);
                    $doc->exportCaption($this->About_Text);
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
						$doc->exportCaption($this->Language_Code);
						$doc->exportCaption($this->Language_Name);
						$doc->exportCaption($this->Default);
						$doc->exportCaption($this->Site_Logo);
						$doc->exportCaption($this->Site_Title);
						$doc->exportCaption($this->Default_Thousands_Separator);
						$doc->exportCaption($this->Default_Decimal_Point);
						$doc->exportCaption($this->Default_Currency_Symbol);
						$doc->exportCaption($this->Default_Money_Thousands_Separator);
						$doc->exportCaption($this->Default_Money_Decimal_Point);
						$doc->exportCaption($this->Terms_And_Condition_Text);
						$doc->exportCaption($this->Announcement_Text);
						$doc->exportCaption($this->About_Text);
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
                        $doc->exportField($this->Language_Code);
                        $doc->exportField($this->Language_Name);
                        $doc->exportField($this->Default);
                        $doc->exportField($this->Site_Logo);
                        $doc->exportField($this->Site_Title);
                        $doc->exportField($this->Default_Thousands_Separator);
                        $doc->exportField($this->Default_Decimal_Point);
                        $doc->exportField($this->Default_Currency_Symbol);
                        $doc->exportField($this->Default_Money_Thousands_Separator);
                        $doc->exportField($this->Default_Money_Decimal_Point);
                        $doc->exportField($this->Terms_And_Condition_Text);
                        $doc->exportField($this->Announcement_Text);
                        $doc->exportField($this->About_Text);
                    } else {
                        $doc->exportField($this->Language_Code);
                        $doc->exportField($this->Language_Name);
                        $doc->exportField($this->Default);
                        $doc->exportField($this->Site_Logo);
                        $doc->exportField($this->Site_Title);
                        $doc->exportField($this->Default_Thousands_Separator);
                        $doc->exportField($this->Default_Decimal_Point);
                        $doc->exportField($this->Default_Currency_Symbol);
                        $doc->exportField($this->Default_Money_Thousands_Separator);
                        $doc->exportField($this->Default_Money_Decimal_Point);
                        $doc->exportField($this->Terms_And_Condition_Text);
                        $doc->exportField($this->Announcement_Text);
                        $doc->exportField($this->About_Text);
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
        if ($name == "Language_Name") {
            $clone = $this->Language_Name->getClone()->setViewValue($value);
            $clone->ViewValue = $clone->CurrentValue;
            return $clone->getViewValue();
        }
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
        //Log("Row Inserted");
    }

    // Row Updating event
    public function rowUpdating(array $oldRow, array &$newRow): ?bool
    {
        // Enter your code here
        // To cancel, set return value to false
        // To skip for grid insert/update, set return value to null
        return true;
    }

    // Row Updated event
    public function rowUpdated(array $oldRow, array $newRow): void
    {
        //Log("Row Updated");
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
        // Enter your code here
        // To cancel, set return value to false
        // To skip for grid insert/update, set return value to null
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
