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
 * Table class for announcement
 */
class Announcement extends DbTable implements LookupTableInterface
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
    public DbField $Announcement_ID;
    public DbField $Is_Active;
    public DbField $Topic;
    public DbField $Message;
    public DbField $Date_LastUpdate;
    public DbField $_Language;
    public DbField $Auto_Publish;
    public DbField $Date_Start;
    public DbField $Date_End;
    public DbField $Date_Created;
    public DbField $Created_By;
    public DbField $Translated_ID;

    // Page ID
    public string $PageID = ""; // To be set by subclass

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        $this->TableVar = "announcement";
        $this->TableName = 'announcement';
        $this->TableType = "TABLE";
        $this->ImportUseTransaction = $this->supportsTransaction() && Config("IMPORT_USE_TRANSACTION");
        $this->UseTransaction = $this->supportsTransaction() && Config("USE_TRANSACTION");
        $this->UpdateTable = "announcement"; // Update table
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

        // Announcement_ID
        $this->Announcement_ID = new DbField(
            $this, // Table
            'x_Announcement_ID', // Variable name
            'Announcement_ID', // Name
            '`Announcement_ID`', // Expression
            '`Announcement_ID`', // Basic search expression
            19, // Type
            10, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Announcement_ID`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'NO' // Edit Tag
        );
        $this->Announcement_ID->InputTextType = "text";
        $this->Announcement_ID->Raw = true;
        $this->Announcement_ID->IsAutoIncrement = true; // Autoincrement field
        $this->Announcement_ID->IsPrimaryKey = true; // Primary key field
        $this->Announcement_ID->Nullable = false; // NOT NULL field
        $this->Announcement_ID->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->Announcement_ID->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['Announcement_ID'] = &$this->Announcement_ID;

        // Is_Active
        $this->Is_Active = new DbField(
            $this, // Table
            'x_Is_Active', // Variable name
            'Is_Active', // Name
            '`Is_Active`', // Expression
            '`Is_Active`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Is_Active`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Is_Active->addMethod("getDefault", fn() => "N");
        $this->Is_Active->InputTextType = "text";
        $this->Is_Active->Raw = true;
        $this->Is_Active->Nullable = false; // NOT NULL field
        $this->Is_Active->Required = true; // Required field
        $this->Is_Active->setDataType(DataType::BOOLEAN);
        $this->Is_Active->TrueValue = "Y";
        $this->Is_Active->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Is_Active->Lookup = new Lookup($this->Is_Active, 'announcement', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Is_Active->Lookup = new Lookup($this->Is_Active, 'announcement', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Is_Active->OptionCount = 2;
        $this->Is_Active->SearchOperators = ["=", "<>"];
        $this->Fields['Is_Active'] = &$this->Is_Active;

        // Topic
        $this->Topic = new DbField(
            $this, // Table
            'x_Topic', // Variable name
            'Topic', // Name
            '`Topic`', // Expression
            '`Topic`', // Basic search expression
            200, // Type
            50, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Topic`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Topic->InputTextType = "text";
        $this->Topic->Nullable = false; // NOT NULL field
        $this->Topic->Required = true; // Required field
        $this->Topic->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Topic'] = &$this->Topic;

        // Message
        $this->Message = new DbField(
            $this, // Table
            'x_Message', // Variable name
            'Message', // Name
            '`Message`', // Expression
            '`Message`', // Basic search expression
            201, // Type
            16777215, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Message`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXTAREA' // Edit Tag
        );
        $this->Message->InputTextType = "text";
        $this->Message->Nullable = false; // NOT NULL field
        $this->Message->Required = true; // Required field
        $this->Message->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Message'] = &$this->Message;

        // Date_LastUpdate
        $this->Date_LastUpdate = new DbField(
            $this, // Table
            'x_Date_LastUpdate', // Variable name
            'Date_LastUpdate', // Name
            '`Date_LastUpdate`', // Expression
            CastDateFieldForLike("`Date_LastUpdate`", 1, "DB"), // Basic search expression
            135, // Type
            19, // Size
            1, // Date/Time format
            false, // Is upload field
            '`Date_LastUpdate`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Date_LastUpdate->InputTextType = "text";
        $this->Date_LastUpdate->Raw = true;
        $this->Date_LastUpdate->Nullable = false; // NOT NULL field
        $this->Date_LastUpdate->Required = true; // Required field
        $this->Date_LastUpdate->DefaultErrorMessage = str_replace("%s", DateFormat(1), $this->language->phrase("IncorrectDate"));
        $this->Date_LastUpdate->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['Date_LastUpdate'] = &$this->Date_LastUpdate;

        // Language
        $this->_Language = new DbField(
            $this, // Table
            'x__Language', // Variable name
            'Language', // Name
            '`Language`', // Expression
            '`Language`', // Basic search expression
            129, // Type
            5, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Language`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'SELECT' // Edit Tag
        );
        $this->_Language->addMethod("getDefault", fn() => "en");
        $this->_Language->InputTextType = "text";
        $this->_Language->Nullable = false; // NOT NULL field
        $this->_Language->Required = true; // Required field
        $this->_Language->setSelectMultiple(false); // Select one
        $this->_Language->UsePleaseSelect = true; // Use PleaseSelect by default
        $this->_Language->PleaseSelectText = $this->language->phrase("PleaseSelect"); // "PleaseSelect" text
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->_Language->Lookup = new Lookup($this->_Language, 'languages', false, 'Language_Code', ["Language_Name","","",""], '', "", [], [], [], [], [], [], false, '', '', "`Language_Name`");
                break;
            default:
                $this->_Language->Lookup = new Lookup($this->_Language, 'languages', false, 'Language_Code', ["Language_Name","","",""], '', "", [], [], [], [], [], [], false, '', '', "`Language_Name`");
                break;
        }
        $this->_Language->SearchOperators = ["=", "<>"];
        $this->Fields['Language'] = &$this->_Language;

        // Auto_Publish
        $this->Auto_Publish = new DbField(
            $this, // Table
            'x_Auto_Publish', // Variable name
            'Auto_Publish', // Name
            '`Auto_Publish`', // Expression
            '`Auto_Publish`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Auto_Publish`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Auto_Publish->addMethod("getDefault", fn() => "N");
        $this->Auto_Publish->InputTextType = "text";
        $this->Auto_Publish->Raw = true;
        $this->Auto_Publish->setDataType(DataType::BOOLEAN);
        $this->Auto_Publish->TrueValue = "Y";
        $this->Auto_Publish->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Auto_Publish->Lookup = new Lookup($this->Auto_Publish, 'announcement', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Auto_Publish->Lookup = new Lookup($this->Auto_Publish, 'announcement', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Auto_Publish->OptionCount = 2;
        $this->Auto_Publish->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Auto_Publish'] = &$this->Auto_Publish;

        // Date_Start
        $this->Date_Start = new DbField(
            $this, // Table
            'x_Date_Start', // Variable name
            'Date_Start', // Name
            '`Date_Start`', // Expression
            CastDateFieldForLike("`Date_Start`", 1, "DB"), // Basic search expression
            135, // Type
            19, // Size
            1, // Date/Time format
            false, // Is upload field
            '`Date_Start`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Date_Start->InputTextType = "text";
        $this->Date_Start->Raw = true;
        $this->Date_Start->DefaultErrorMessage = str_replace("%s", DateFormat(1), $this->language->phrase("IncorrectDate"));
        $this->Date_Start->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['Date_Start'] = &$this->Date_Start;

        // Date_End
        $this->Date_End = new DbField(
            $this, // Table
            'x_Date_End', // Variable name
            'Date_End', // Name
            '`Date_End`', // Expression
            CastDateFieldForLike("`Date_End`", 1, "DB"), // Basic search expression
            135, // Type
            19, // Size
            1, // Date/Time format
            false, // Is upload field
            '`Date_End`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Date_End->InputTextType = "text";
        $this->Date_End->Raw = true;
        $this->Date_End->DefaultErrorMessage = str_replace("%s", DateFormat(1), $this->language->phrase("IncorrectDate"));
        $this->Date_End->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['Date_End'] = &$this->Date_End;

        // Date_Created
        $this->Date_Created = new DbField(
            $this, // Table
            'x_Date_Created', // Variable name
            'Date_Created', // Name
            '`Date_Created`', // Expression
            CastDateFieldForLike("`Date_Created`", 1, "DB"), // Basic search expression
            135, // Type
            19, // Size
            1, // Date/Time format
            false, // Is upload field
            '`Date_Created`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->Date_Created->InputTextType = "text";
        $this->Date_Created->Raw = true;
        $this->Date_Created->DefaultErrorMessage = str_replace("%s", DateFormat(1), $this->language->phrase("IncorrectDate"));
        $this->Date_Created->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['Date_Created'] = &$this->Date_Created;

        // Created_By
        $this->Created_By = new DbField(
            $this, // Table
            'x_Created_By', // Variable name
            'Created_By', // Name
            '`Created_By`', // Expression
            '`Created_By`', // Basic search expression
            200, // Type
            200, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Created_By`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'SELECT' // Edit Tag
        );
        $this->Created_By->InputTextType = "text";
        $this->Created_By->setSelectMultiple(false); // Select one
        $this->Created_By->UsePleaseSelect = true; // Use PleaseSelect by default
        $this->Created_By->PleaseSelectText = $this->language->phrase("PleaseSelect"); // "PleaseSelect" text
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Created_By->Lookup = new Lookup($this->Created_By, 'users', false, 'Username', ["FirstName","LastName","",""], '', "", [], [], [], [], [], [], false, '', '', "CONCAT(COALESCE(`FirstName`, ''),'" . ValueSeparator(1, $this->Created_By) . "',COALESCE(`LastName`,''))");
                break;
            default:
                $this->Created_By->Lookup = new Lookup($this->Created_By, 'users', false, 'Username', ["FirstName","LastName","",""], '', "", [], [], [], [], [], [], false, '', '', "CONCAT(COALESCE(`FirstName`, ''),'" . ValueSeparator(1, $this->Created_By) . "',COALESCE(`LastName`,''))");
                break;
        }
        $this->Created_By->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Created_By'] = &$this->Created_By;

        // Translated_ID
        $this->Translated_ID = new DbField(
            $this, // Table
            'x_Translated_ID', // Variable name
            'Translated_ID', // Name
            '`Translated_ID`', // Expression
            '`Translated_ID`', // Basic search expression
            3, // Type
            11, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Translated_ID`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'SELECT' // Edit Tag
        );
        $this->Translated_ID->InputTextType = "text";
        $this->Translated_ID->Raw = true;
        $this->Translated_ID->setSelectMultiple(false); // Select one
        $this->Translated_ID->UsePleaseSelect = true; // Use PleaseSelect by default
        $this->Translated_ID->PleaseSelectText = $this->language->phrase("PleaseSelect"); // "PleaseSelect" text
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Translated_ID->Lookup = new Lookup($this->Translated_ID, 'announcement', false, 'Announcement_ID', ["Topic","","",""], '', "", [], [], [], [], [], [], false, '', '', "`Topic`");
                break;
            default:
                $this->Translated_ID->Lookup = new Lookup($this->Translated_ID, 'announcement', false, 'Announcement_ID', ["Topic","","",""], '', "", [], [], [], [], [], [], false, '', '', "`Topic`");
                break;
        }
        $this->Translated_ID->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->Translated_ID->SearchOperators = ["=", "<>", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['Translated_ID'] = &$this->Translated_ID;

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
        return ($this->SqlFrom != "") ? $this->SqlFrom : "announcement";
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
            $this->Announcement_ID->setDbValue($conn->lastInsertId());
            $row['Announcement_ID'] = $this->Announcement_ID->DbValue;
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
            if (!isset($row['Announcement_ID']) && !IsEmpty($this->Announcement_ID->CurrentValue)) {
                $row['Announcement_ID'] = $this->Announcement_ID->CurrentValue;
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
            if (array_key_exists('Announcement_ID', $row)) {
                AddFilter($where, QuotedName('Announcement_ID', $this->Dbid) . '=' . QuotedValue($row['Announcement_ID'], $this->Announcement_ID->DataType, $this->Dbid));
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
        $this->Announcement_ID->DbValue = $row['Announcement_ID'];
        $this->Is_Active->DbValue = $row['Is_Active'];
        $this->Topic->DbValue = $row['Topic'];
        $this->Message->DbValue = $row['Message'];
        $this->Date_LastUpdate->DbValue = $row['Date_LastUpdate'];
        $this->_Language->DbValue = $row['Language'];
        $this->Auto_Publish->DbValue = $row['Auto_Publish'];
        $this->Date_Start->DbValue = $row['Date_Start'];
        $this->Date_End->DbValue = $row['Date_End'];
        $this->Date_Created->DbValue = $row['Date_Created'];
        $this->Created_By->DbValue = $row['Created_By'];
        $this->Translated_ID->DbValue = $row['Translated_ID'];
    }

    // Delete uploaded files
    public function deleteUploadedFiles(array $row)
    {
        $this->loadDbValues($row);
    }

    // Record filter WHERE clause
    protected function sqlKeyFilter(): string
    {
        return "`Announcement_ID` = @Announcement_ID@";
    }

    // Get Key from record
    public function getKeyFromRecord(array $row, ?string $keySeparator = null): string
    {
        $keys = [];
        $val = $row['Announcement_ID'];
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
        $val = $current ? $this->Announcement_ID->CurrentValue : $this->Announcement_ID->OldValue;
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
                $this->Announcement_ID->CurrentValue = $keys[0];
            } else {
                $this->Announcement_ID->OldValue = $keys[0];
            }
        }
    }

    // Get record filter
    public function getRecordFilter(?array $row = null, bool $current = false): string
    {
        $keyFilter = $this->sqlKeyFilter();
        if (is_array($row)) {
            $val = array_key_exists('Announcement_ID', $row) ? $row['Announcement_ID'] : null;
        } else {
            $val = !IsEmpty($this->Announcement_ID->OldValue) && !$current ? $this->Announcement_ID->OldValue : $this->Announcement_ID->CurrentValue;
        }
        if (!is_numeric($val)) {
            return "0=1"; // Invalid key
        }
        if ($val === null) {
            return "0=1"; // Invalid key
        } else {
            $keyFilter = str_replace("@Announcement_ID@", AdjustSql($val), $keyFilter); // Replace key value
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
        return Session($name) ?? GetUrl("announcementlist");
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
            "announcementview" => $this->language->phrase("View"),
            "announcementedit" => $this->language->phrase("Edit"),
            "announcementadd" => $this->language->phrase("Add"),
            default => ""
        };
    }

    // Default route URL
    public function getDefaultRouteUrl(): string
    {
        return "announcementlist";
    }

    // API page name
    public function getApiPageName(string $action): string
    {
        return match (strtolower($action)) {
            Config("API_VIEW_ACTION") => "AnnouncementView",
            Config("API_ADD_ACTION") => "AnnouncementAdd",
            Config("API_EDIT_ACTION") => "AnnouncementEdit",
            Config("API_DELETE_ACTION") => "AnnouncementDelete",
            Config("API_LIST_ACTION") => "AnnouncementList",
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
        return "announcementlist";
    }

    // View URL
    public function getViewUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = $this->keyUrl("announcementview", $parm);
        } else {
            $url = $this->keyUrl("announcementview", Config("TABLE_SHOW_DETAIL") . "=");
        }
        return $this->addMasterUrl($url);
    }

    // Add URL
    public function getAddUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = "announcementadd?" . $parm;
        } else {
            $url = "announcementadd";
        }
        return $this->addMasterUrl($url);
    }

    // Edit URL
    public function getEditUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("announcementedit", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline edit URL
    public function getInlineEditUrl(): string
    {
        $url = $this->keyUrl("announcementlist", "action=edit");
        return $this->addMasterUrl($url);
    }

    // Copy URL
    public function getCopyUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("announcementadd", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline copy URL
    public function getInlineCopyUrl(): string
    {
        $url = $this->keyUrl("announcementlist", "action=copy");
        return $this->addMasterUrl($url);
    }

    // Delete URL
    public function getDeleteUrl(string $parm = ""): string
    {
        if ($this->UseAjaxActions && ConvertToBool(Param("infinitescroll")) && CurrentPageID() == "list") {
            return $this->keyUrl(GetApiUrl(Config("API_DELETE_ACTION") . "/" . $this->TableVar));
        } else {
            return $this->keyUrl("announcementdelete", $parm);
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
        $json .= "\"Announcement_ID\":" . VarToJson($this->Announcement_ID->CurrentValue, "number");
        $json = "{" . $json . "}";
        if ($htmlEncode) {
            $json = HtmlEncode($json);
        }
        return $json;
    }

    // Add key value to URL
    public function keyUrl(string $url, string $parm = ""): string
    {
        if ($this->Announcement_ID->CurrentValue !== null) {
            $url .= "/" . $this->encodeKeyValue($this->Announcement_ID->CurrentValue);
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
            if (($keyValue = Param("Announcement_ID") ?? Route("Announcement_ID")) !== null) {
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
                $this->Announcement_ID->CurrentValue = $key;
            } else {
                $this->Announcement_ID->OldValue = $key;
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

    // Render list content
    public function renderListContent(string $filter)
    {
        global $Response;
        $container = Container();
        $listPage = "AnnouncementList";
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

        // Announcement_ID

        // Is_Active

        // Topic

        // Message

        // Date_LastUpdate

        // Language

        // Auto_Publish

        // Date_Start

        // Date_End

        // Date_Created

        // Created_By

        // Translated_ID

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
        $this->Announcement_ID->TooltipValue = "";

        // Is_Active
        $this->Is_Active->HrefValue = "";
        $this->Is_Active->TooltipValue = "";

        // Topic
        $this->Topic->HrefValue = "";
        $this->Topic->TooltipValue = "";

        // Message
        $this->Message->HrefValue = "";
        $this->Message->TooltipValue = "";

        // Date_LastUpdate
        $this->Date_LastUpdate->HrefValue = "";
        $this->Date_LastUpdate->TooltipValue = "";

        // Language
        $this->_Language->HrefValue = "";
        $this->_Language->TooltipValue = "";

        // Auto_Publish
        $this->Auto_Publish->HrefValue = "";
        $this->Auto_Publish->TooltipValue = "";

        // Date_Start
        $this->Date_Start->HrefValue = "";
        $this->Date_Start->TooltipValue = "";

        // Date_End
        $this->Date_End->HrefValue = "";
        $this->Date_End->TooltipValue = "";

        // Date_Created
        $this->Date_Created->HrefValue = "";
        $this->Date_Created->TooltipValue = "";

        // Created_By
        $this->Created_By->HrefValue = "";
        $this->Created_By->TooltipValue = "";

        // Translated_ID
        $this->Translated_ID->HrefValue = "";
        $this->Translated_ID->TooltipValue = "";

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
                    $doc->exportCaption($this->Announcement_ID);
                    $doc->exportCaption($this->Is_Active);
                    $doc->exportCaption($this->Topic);
                    $doc->exportCaption($this->Message);
                    $doc->exportCaption($this->Date_LastUpdate);
                    $doc->exportCaption($this->_Language);
                    $doc->exportCaption($this->Auto_Publish);
                    $doc->exportCaption($this->Date_Start);
                    $doc->exportCaption($this->Date_End);
                    $doc->exportCaption($this->Date_Created);
                    $doc->exportCaption($this->Created_By);
                    $doc->exportCaption($this->Translated_ID);
                } else {
                    $doc->exportCaption($this->Announcement_ID);
                    $doc->exportCaption($this->Is_Active);
                    $doc->exportCaption($this->Topic);
                    $doc->exportCaption($this->Date_LastUpdate);
                    $doc->exportCaption($this->_Language);
                    $doc->exportCaption($this->Auto_Publish);
                    $doc->exportCaption($this->Date_Start);
                    $doc->exportCaption($this->Date_End);
                    $doc->exportCaption($this->Date_Created);
                    $doc->exportCaption($this->Created_By);
                    $doc->exportCaption($this->Translated_ID);
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
						$doc->exportCaption($this->Announcement_ID);
						$doc->exportCaption($this->Is_Active);
						$doc->exportCaption($this->Topic);
						$doc->exportCaption($this->Date_LastUpdate);
						$doc->exportCaption($this->_Language);
						$doc->exportCaption($this->Auto_Publish);
						$doc->exportCaption($this->Date_Start);
						$doc->exportCaption($this->Date_End);
						$doc->exportCaption($this->Date_Created);
						$doc->exportCaption($this->Created_By);
						$doc->exportCaption($this->Translated_ID);
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
                        $doc->exportField($this->Announcement_ID);
                        $doc->exportField($this->Is_Active);
                        $doc->exportField($this->Topic);
                        $doc->exportField($this->Message);
                        $doc->exportField($this->Date_LastUpdate);
                        $doc->exportField($this->_Language);
                        $doc->exportField($this->Auto_Publish);
                        $doc->exportField($this->Date_Start);
                        $doc->exportField($this->Date_End);
                        $doc->exportField($this->Date_Created);
                        $doc->exportField($this->Created_By);
                        $doc->exportField($this->Translated_ID);
                    } else {
                        $doc->exportField($this->Announcement_ID);
                        $doc->exportField($this->Is_Active);
                        $doc->exportField($this->Topic);
                        $doc->exportField($this->Date_LastUpdate);
                        $doc->exportField($this->_Language);
                        $doc->exportField($this->Auto_Publish);
                        $doc->exportField($this->Date_Start);
                        $doc->exportField($this->Date_End);
                        $doc->exportField($this->Date_Created);
                        $doc->exportField($this->Created_By);
                        $doc->exportField($this->Translated_ID);
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
        if ($name == "Topic") {
            $clone = $this->Topic->getClone()->setViewValue($value);
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
        if (strtotime($newRow["Date_Start"]) > strtotime($newRow["Date_End"])) {
    		$this->setFailureMessage("Date End cannot be lower than Date Start. Please fix it.");
    		return false;
    	}
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
        if (strtotime($newRow["Date_Start"]) > strtotime($newRow["Date_End"])) {
    		$this->setFailureMessage("Date End cannot be lower than Date Start. Please fix it.");
    		return false;
    	}
        return true;
    }
    // Row Updated event
    public function rowUpdated(array $oldRow, array $newRow): void
    {
        ExecuteUpdate("UPDATE " . Config("MS_ANNOUNCEMENT_TABLE") . " SET Date_Start = '" . $newRow["Date_Start"] . "', Date_End = '" . $newRow["Date_End"] . "' WHERE Translated_ID = " . $oldRow["Announcement_ID"]);
    	$this->setSuccessMessage("Successfully updated the related translation record(s).");
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
