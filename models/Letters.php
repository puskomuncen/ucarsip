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
 * Table class for letters
 */
class Letters extends DbTable implements LookupTableInterface
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
    public DbField $letter_id;
    public DbField $nomor_surat;
    public DbField $perihal;
    public DbField $tanggal_surat;
    public DbField $tanggal_terima;
    public DbField $jenis;
    public DbField $klasifikasi;
    public DbField $pengirim;
    public DbField $penerima_unit_id;
    public DbField $file_url;
    public DbField $status;
    public DbField $created_by;
    public DbField $created_at;
    public DbField $updated_at;

    // Page ID
    public string $PageID = ""; // To be set by subclass

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        $this->TableVar = "letters";
        $this->TableName = 'letters';
        $this->TableType = "TABLE";
        $this->ImportUseTransaction = $this->supportsTransaction() && Config("IMPORT_USE_TRANSACTION");
        $this->UseTransaction = $this->supportsTransaction() && Config("USE_TRANSACTION");
        $this->UpdateTable = "letters"; // Update table
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

        // letter_id
        $this->letter_id = new DbField(
            $this, // Table
            'x_letter_id', // Variable name
            'letter_id', // Name
            '`letter_id`', // Expression
            '`letter_id`', // Basic search expression
            3, // Type
            11, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`letter_id`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'NO' // Edit Tag
        );
        $this->letter_id->InputTextType = "text";
        $this->letter_id->Raw = true;
        $this->letter_id->IsAutoIncrement = true; // Autoincrement field
        $this->letter_id->IsPrimaryKey = true; // Primary key field
        $this->letter_id->Nullable = false; // NOT NULL field
        $this->letter_id->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->letter_id->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['letter_id'] = &$this->letter_id;

        // nomor_surat
        $this->nomor_surat = new DbField(
            $this, // Table
            'x_nomor_surat', // Variable name
            'nomor_surat', // Name
            '`nomor_surat`', // Expression
            '`nomor_surat`', // Basic search expression
            200, // Type
            50, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`nomor_surat`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->nomor_surat->InputTextType = "text";
        $this->nomor_surat->Nullable = false; // NOT NULL field
        $this->nomor_surat->Required = true; // Required field
        $this->nomor_surat->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['nomor_surat'] = &$this->nomor_surat;

        // perihal
        $this->perihal = new DbField(
            $this, // Table
            'x_perihal', // Variable name
            'perihal', // Name
            '`perihal`', // Expression
            '`perihal`', // Basic search expression
            200, // Type
            255, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`perihal`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->perihal->InputTextType = "text";
        $this->perihal->Nullable = false; // NOT NULL field
        $this->perihal->Required = true; // Required field
        $this->perihal->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['perihal'] = &$this->perihal;

        // tanggal_surat
        $this->tanggal_surat = new DbField(
            $this, // Table
            'x_tanggal_surat', // Variable name
            'tanggal_surat', // Name
            '`tanggal_surat`', // Expression
            CastDateFieldForLike("`tanggal_surat`", 0, "DB"), // Basic search expression
            133, // Type
            10, // Size
            0, // Date/Time format
            false, // Is upload field
            '`tanggal_surat`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->tanggal_surat->InputTextType = "text";
        $this->tanggal_surat->Raw = true;
        $this->tanggal_surat->Nullable = false; // NOT NULL field
        $this->tanggal_surat->Required = true; // Required field
        $this->tanggal_surat->DefaultErrorMessage = str_replace("%s", $GLOBALS["DATE_FORMAT"], $this->language->phrase("IncorrectDate"));
        $this->tanggal_surat->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['tanggal_surat'] = &$this->tanggal_surat;

        // tanggal_terima
        $this->tanggal_terima = new DbField(
            $this, // Table
            'x_tanggal_terima', // Variable name
            'tanggal_terima', // Name
            '`tanggal_terima`', // Expression
            CastDateFieldForLike("`tanggal_terima`", 0, "DB"), // Basic search expression
            133, // Type
            10, // Size
            0, // Date/Time format
            false, // Is upload field
            '`tanggal_terima`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->tanggal_terima->InputTextType = "text";
        $this->tanggal_terima->Raw = true;
        $this->tanggal_terima->DefaultErrorMessage = str_replace("%s", $GLOBALS["DATE_FORMAT"], $this->language->phrase("IncorrectDate"));
        $this->tanggal_terima->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['tanggal_terima'] = &$this->tanggal_terima;

        // jenis
        $this->jenis = new DbField(
            $this, // Table
            'x_jenis', // Variable name
            'jenis', // Name
            '`jenis`', // Expression
            '`jenis`', // Basic search expression
            200, // Type
            6, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`jenis`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'RADIO' // Edit Tag
        );
        $this->jenis->InputTextType = "text";
        $this->jenis->Raw = true;
        $this->jenis->Nullable = false; // NOT NULL field
        $this->jenis->Required = true; // Required field
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->jenis->Lookup = new Lookup($this->jenis, 'letters', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->jenis->Lookup = new Lookup($this->jenis, 'letters', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->jenis->OptionCount = 2;
        $this->jenis->SearchOperators = ["=", "<>"];
        $this->Fields['jenis'] = &$this->jenis;

        // klasifikasi
        $this->klasifikasi = new DbField(
            $this, // Table
            'x_klasifikasi', // Variable name
            'klasifikasi', // Name
            '`klasifikasi`', // Expression
            '`klasifikasi`', // Basic search expression
            200, // Type
            7, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`klasifikasi`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'RADIO' // Edit Tag
        );
        $this->klasifikasi->InputTextType = "text";
        $this->klasifikasi->Raw = true;
        $this->klasifikasi->Nullable = false; // NOT NULL field
        $this->klasifikasi->Required = true; // Required field
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->klasifikasi->Lookup = new Lookup($this->klasifikasi, 'letters', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->klasifikasi->Lookup = new Lookup($this->klasifikasi, 'letters', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->klasifikasi->OptionCount = 3;
        $this->klasifikasi->SearchOperators = ["=", "<>"];
        $this->Fields['klasifikasi'] = &$this->klasifikasi;

        // pengirim
        $this->pengirim = new DbField(
            $this, // Table
            'x_pengirim', // Variable name
            'pengirim', // Name
            '`pengirim`', // Expression
            '`pengirim`', // Basic search expression
            200, // Type
            100, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`pengirim`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->pengirim->InputTextType = "text";
        $this->pengirim->Nullable = false; // NOT NULL field
        $this->pengirim->Required = true; // Required field
        $this->pengirim->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['pengirim'] = &$this->pengirim;

        // penerima_unit_id
        $this->penerima_unit_id = new DbField(
            $this, // Table
            'x_penerima_unit_id', // Variable name
            'penerima_unit_id', // Name
            '`penerima_unit_id`', // Expression
            '`penerima_unit_id`', // Basic search expression
            3, // Type
            11, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`penerima_unit_id`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->penerima_unit_id->InputTextType = "text";
        $this->penerima_unit_id->Raw = true;
        $this->penerima_unit_id->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->penerima_unit_id->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['penerima_unit_id'] = &$this->penerima_unit_id;

        // file_url
        $this->file_url = new DbField(
            $this, // Table
            'x_file_url', // Variable name
            'file_url', // Name
            '`file_url`', // Expression
            '`file_url`', // Basic search expression
            200, // Type
            255, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`file_url`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->file_url->InputTextType = "text";
        $this->file_url->Nullable = false; // NOT NULL field
        $this->file_url->Required = true; // Required field
        $this->file_url->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['file_url'] = &$this->file_url;

        // status
        $this->status = new DbField(
            $this, // Table
            'x_status', // Variable name
            'status', // Name
            '`status`', // Expression
            '`status`', // Basic search expression
            200, // Type
            9, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`status`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'RADIO' // Edit Tag
        );
        $this->status->InputTextType = "text";
        $this->status->Raw = true;
        $this->status->Nullable = false; // NOT NULL field
        $this->status->Required = true; // Required field
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->status->Lookup = new Lookup($this->status, 'letters', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->status->Lookup = new Lookup($this->status, 'letters', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->status->OptionCount = 4;
        $this->status->SearchOperators = ["=", "<>"];
        $this->Fields['status'] = &$this->status;

        // created_by
        $this->created_by = new DbField(
            $this, // Table
            'x_created_by', // Variable name
            'created_by', // Name
            '`created_by`', // Expression
            '`created_by`', // Basic search expression
            3, // Type
            11, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`created_by`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->created_by->addMethod("getAutoUpdateValue", fn() => CurrentUserName());
        $this->created_by->InputTextType = "text";
        $this->created_by->Raw = true;
        $this->created_by->Nullable = false; // NOT NULL field
        $this->created_by->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->created_by->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['created_by'] = &$this->created_by;

        // created_at
        $this->created_at = new DbField(
            $this, // Table
            'x_created_at', // Variable name
            'created_at', // Name
            '`created_at`', // Expression
            CastDateFieldForLike("`created_at`", 0, "DB"), // Basic search expression
            135, // Type
            19, // Size
            0, // Date/Time format
            false, // Is upload field
            '`created_at`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->created_at->addMethod("getAutoUpdateValue", fn() => CurrentTime());
        $this->created_at->InputTextType = "text";
        $this->created_at->Raw = true;
        $this->created_at->Nullable = false; // NOT NULL field
        $this->created_at->DefaultErrorMessage = str_replace("%s", $GLOBALS["DATE_FORMAT"], $this->language->phrase("IncorrectDate"));
        $this->created_at->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['created_at'] = &$this->created_at;

        // updated_at
        $this->updated_at = new DbField(
            $this, // Table
            'x_updated_at', // Variable name
            'updated_at', // Name
            '`updated_at`', // Expression
            CastDateFieldForLike("`updated_at`", 0, "DB"), // Basic search expression
            135, // Type
            19, // Size
            0, // Date/Time format
            false, // Is upload field
            '`updated_at`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->updated_at->addMethod("getAutoUpdateValue", fn() => CurrentTime());
        $this->updated_at->InputTextType = "text";
        $this->updated_at->Raw = true;
        $this->updated_at->Nullable = false; // NOT NULL field
        $this->updated_at->DefaultErrorMessage = str_replace("%s", $GLOBALS["DATE_FORMAT"], $this->language->phrase("IncorrectDate"));
        $this->updated_at->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['updated_at'] = &$this->updated_at;

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
        return ($this->SqlFrom != "") ? $this->SqlFrom : "letters";
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
            $this->letter_id->setDbValue($conn->lastInsertId());
            $row['letter_id'] = $this->letter_id->DbValue;
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
            if (!isset($row['letter_id']) && !IsEmpty($this->letter_id->CurrentValue)) {
                $row['letter_id'] = $this->letter_id->CurrentValue;
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
            if (array_key_exists('letter_id', $row)) {
                AddFilter($where, QuotedName('letter_id', $this->Dbid) . '=' . QuotedValue($row['letter_id'], $this->letter_id->DataType, $this->Dbid));
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
        $this->letter_id->DbValue = $row['letter_id'];
        $this->nomor_surat->DbValue = $row['nomor_surat'];
        $this->perihal->DbValue = $row['perihal'];
        $this->tanggal_surat->DbValue = $row['tanggal_surat'];
        $this->tanggal_terima->DbValue = $row['tanggal_terima'];
        $this->jenis->DbValue = $row['jenis'];
        $this->klasifikasi->DbValue = $row['klasifikasi'];
        $this->pengirim->DbValue = $row['pengirim'];
        $this->penerima_unit_id->DbValue = $row['penerima_unit_id'];
        $this->file_url->DbValue = $row['file_url'];
        $this->status->DbValue = $row['status'];
        $this->created_by->DbValue = $row['created_by'];
        $this->created_at->DbValue = $row['created_at'];
        $this->updated_at->DbValue = $row['updated_at'];
    }

    // Delete uploaded files
    public function deleteUploadedFiles(array $row)
    {
        $this->loadDbValues($row);
    }

    // Record filter WHERE clause
    protected function sqlKeyFilter(): string
    {
        return "`letter_id` = @letter_id@";
    }

    // Get Key from record
    public function getKeyFromRecord(array $row, ?string $keySeparator = null): string
    {
        $keys = [];
        $val = $row['letter_id'];
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
        $val = $current ? $this->letter_id->CurrentValue : $this->letter_id->OldValue;
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
                $this->letter_id->CurrentValue = $keys[0];
            } else {
                $this->letter_id->OldValue = $keys[0];
            }
        }
    }

    // Get record filter
    public function getRecordFilter(?array $row = null, bool $current = false): string
    {
        $keyFilter = $this->sqlKeyFilter();
        if (is_array($row)) {
            $val = array_key_exists('letter_id', $row) ? $row['letter_id'] : null;
        } else {
            $val = !IsEmpty($this->letter_id->OldValue) && !$current ? $this->letter_id->OldValue : $this->letter_id->CurrentValue;
        }
        if (!is_numeric($val)) {
            return "0=1"; // Invalid key
        }
        if ($val === null) {
            return "0=1"; // Invalid key
        } else {
            $keyFilter = str_replace("@letter_id@", AdjustSql($val), $keyFilter); // Replace key value
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
        return Session($name) ?? GetUrl("letterslist");
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
            "lettersview" => $this->language->phrase("View"),
            "lettersedit" => $this->language->phrase("Edit"),
            "lettersadd" => $this->language->phrase("Add"),
            default => ""
        };
    }

    // Default route URL
    public function getDefaultRouteUrl(): string
    {
        return "letterslist";
    }

    // API page name
    public function getApiPageName(string $action): string
    {
        return match (strtolower($action)) {
            Config("API_VIEW_ACTION") => "LettersView",
            Config("API_ADD_ACTION") => "LettersAdd",
            Config("API_EDIT_ACTION") => "LettersEdit",
            Config("API_DELETE_ACTION") => "LettersDelete",
            Config("API_LIST_ACTION") => "LettersList",
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
        return "letterslist";
    }

    // View URL
    public function getViewUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = $this->keyUrl("lettersview", $parm);
        } else {
            $url = $this->keyUrl("lettersview", Config("TABLE_SHOW_DETAIL") . "=");
        }
        return $this->addMasterUrl($url);
    }

    // Add URL
    public function getAddUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = "lettersadd?" . $parm;
        } else {
            $url = "lettersadd";
        }
        return $this->addMasterUrl($url);
    }

    // Edit URL
    public function getEditUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("lettersedit", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline edit URL
    public function getInlineEditUrl(): string
    {
        $url = $this->keyUrl("letterslist", "action=edit");
        return $this->addMasterUrl($url);
    }

    // Copy URL
    public function getCopyUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("lettersadd", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline copy URL
    public function getInlineCopyUrl(): string
    {
        $url = $this->keyUrl("letterslist", "action=copy");
        return $this->addMasterUrl($url);
    }

    // Delete URL
    public function getDeleteUrl(string $parm = ""): string
    {
        if ($this->UseAjaxActions && ConvertToBool(Param("infinitescroll")) && CurrentPageID() == "list") {
            return $this->keyUrl(GetApiUrl(Config("API_DELETE_ACTION") . "/" . $this->TableVar));
        } else {
            return $this->keyUrl("lettersdelete", $parm);
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
        $json .= "\"letter_id\":" . VarToJson($this->letter_id->CurrentValue, "number");
        $json = "{" . $json . "}";
        if ($htmlEncode) {
            $json = HtmlEncode($json);
        }
        return $json;
    }

    // Add key value to URL
    public function keyUrl(string $url, string $parm = ""): string
    {
        if ($this->letter_id->CurrentValue !== null) {
            $url .= "/" . $this->encodeKeyValue($this->letter_id->CurrentValue);
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
            if (($keyValue = Param("letter_id") ?? Route("letter_id")) !== null) {
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
                $this->letter_id->CurrentValue = $key;
            } else {
                $this->letter_id->OldValue = $key;
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
        $this->letter_id->setDbValue($row['letter_id']);
        $this->nomor_surat->setDbValue($row['nomor_surat']);
        $this->perihal->setDbValue($row['perihal']);
        $this->tanggal_surat->setDbValue($row['tanggal_surat']);
        $this->tanggal_terima->setDbValue($row['tanggal_terima']);
        $this->jenis->setDbValue($row['jenis']);
        $this->klasifikasi->setDbValue($row['klasifikasi']);
        $this->pengirim->setDbValue($row['pengirim']);
        $this->penerima_unit_id->setDbValue($row['penerima_unit_id']);
        $this->file_url->setDbValue($row['file_url']);
        $this->status->setDbValue($row['status']);
        $this->created_by->setDbValue($row['created_by']);
        $this->created_at->setDbValue($row['created_at']);
        $this->updated_at->setDbValue($row['updated_at']);
    }

    // Render list content
    public function renderListContent(string $filter)
    {
        global $Response;
        $container = Container();
        $listPage = "LettersList";
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

        // letter_id

        // nomor_surat

        // perihal

        // tanggal_surat

        // tanggal_terima

        // jenis

        // klasifikasi

        // pengirim

        // penerima_unit_id

        // file_url

        // status

        // created_by

        // created_at

        // updated_at

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
        $this->penerima_unit_id->ViewValue = $this->penerima_unit_id->CurrentValue;
        $this->penerima_unit_id->ViewValue = FormatNumber($this->penerima_unit_id->ViewValue, $this->penerima_unit_id->formatPattern());

        // file_url
        $this->file_url->ViewValue = $this->file_url->CurrentValue;

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

        // letter_id
        $this->letter_id->HrefValue = "";
        $this->letter_id->TooltipValue = "";

        // nomor_surat
        $this->nomor_surat->HrefValue = "";
        $this->nomor_surat->TooltipValue = "";

        // perihal
        $this->perihal->HrefValue = "";
        $this->perihal->TooltipValue = "";

        // tanggal_surat
        $this->tanggal_surat->HrefValue = "";
        $this->tanggal_surat->TooltipValue = "";

        // tanggal_terima
        $this->tanggal_terima->HrefValue = "";
        $this->tanggal_terima->TooltipValue = "";

        // jenis
        $this->jenis->HrefValue = "";
        $this->jenis->TooltipValue = "";

        // klasifikasi
        $this->klasifikasi->HrefValue = "";
        $this->klasifikasi->TooltipValue = "";

        // pengirim
        $this->pengirim->HrefValue = "";
        $this->pengirim->TooltipValue = "";

        // penerima_unit_id
        $this->penerima_unit_id->HrefValue = "";
        $this->penerima_unit_id->TooltipValue = "";

        // file_url
        $this->file_url->HrefValue = "";
        $this->file_url->TooltipValue = "";

        // status
        $this->status->HrefValue = "";
        $this->status->TooltipValue = "";

        // created_by
        $this->created_by->HrefValue = "";
        $this->created_by->TooltipValue = "";

        // created_at
        $this->created_at->HrefValue = "";
        $this->created_at->TooltipValue = "";

        // updated_at
        $this->updated_at->HrefValue = "";
        $this->updated_at->TooltipValue = "";

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
                    $doc->exportCaption($this->letter_id);
                    $doc->exportCaption($this->nomor_surat);
                    $doc->exportCaption($this->perihal);
                    $doc->exportCaption($this->tanggal_surat);
                    $doc->exportCaption($this->tanggal_terima);
                    $doc->exportCaption($this->jenis);
                    $doc->exportCaption($this->klasifikasi);
                    $doc->exportCaption($this->pengirim);
                    $doc->exportCaption($this->penerima_unit_id);
                    $doc->exportCaption($this->file_url);
                    $doc->exportCaption($this->status);
                    $doc->exportCaption($this->created_by);
                    $doc->exportCaption($this->created_at);
                    $doc->exportCaption($this->updated_at);
                } else {
                    $doc->exportCaption($this->letter_id);
                    $doc->exportCaption($this->nomor_surat);
                    $doc->exportCaption($this->perihal);
                    $doc->exportCaption($this->tanggal_surat);
                    $doc->exportCaption($this->tanggal_terima);
                    $doc->exportCaption($this->jenis);
                    $doc->exportCaption($this->klasifikasi);
                    $doc->exportCaption($this->pengirim);
                    $doc->exportCaption($this->penerima_unit_id);
                    $doc->exportCaption($this->file_url);
                    $doc->exportCaption($this->status);
                    $doc->exportCaption($this->created_by);
                    $doc->exportCaption($this->created_at);
                    $doc->exportCaption($this->updated_at);
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
						$doc->exportCaption($this->letter_id);
						$doc->exportCaption($this->nomor_surat);
						$doc->exportCaption($this->perihal);
						$doc->exportCaption($this->tanggal_surat);
						$doc->exportCaption($this->tanggal_terima);
						$doc->exportCaption($this->jenis);
						$doc->exportCaption($this->klasifikasi);
						$doc->exportCaption($this->pengirim);
						$doc->exportCaption($this->penerima_unit_id);
						$doc->exportCaption($this->file_url);
						$doc->exportCaption($this->status);
						$doc->exportCaption($this->created_by);
						$doc->exportCaption($this->created_at);
						$doc->exportCaption($this->updated_at);
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
                        $doc->exportField($this->letter_id);
                        $doc->exportField($this->nomor_surat);
                        $doc->exportField($this->perihal);
                        $doc->exportField($this->tanggal_surat);
                        $doc->exportField($this->tanggal_terima);
                        $doc->exportField($this->jenis);
                        $doc->exportField($this->klasifikasi);
                        $doc->exportField($this->pengirim);
                        $doc->exportField($this->penerima_unit_id);
                        $doc->exportField($this->file_url);
                        $doc->exportField($this->status);
                        $doc->exportField($this->created_by);
                        $doc->exportField($this->created_at);
                        $doc->exportField($this->updated_at);
                    } else {
                        $doc->exportField($this->letter_id);
                        $doc->exportField($this->nomor_surat);
                        $doc->exportField($this->perihal);
                        $doc->exportField($this->tanggal_surat);
                        $doc->exportField($this->tanggal_terima);
                        $doc->exportField($this->jenis);
                        $doc->exportField($this->klasifikasi);
                        $doc->exportField($this->pengirim);
                        $doc->exportField($this->penerima_unit_id);
                        $doc->exportField($this->file_url);
                        $doc->exportField($this->status);
                        $doc->exportField($this->created_by);
                        $doc->exportField($this->created_at);
                        $doc->exportField($this->updated_at);
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
        if ($name == "nomor_surat") {
            $clone = $this->nomor_surat->getClone()->setViewValue($value);
            $clone->ViewValue = $clone->CurrentValue;
            return $clone->getViewValue();
        }
        if ($name == "perihal") {
            $clone = $this->perihal->getClone()->setViewValue($value);
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
