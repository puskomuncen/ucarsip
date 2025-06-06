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
 * Table class for users
 */
class Users extends DbTable implements LookupTableInterface
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
    public bool $InlineDelete = true;
    public bool $ModalGridAdd = false;
    public bool $ModalGridEdit = false;
    public bool $ModalMultiEdit = false;

    // Fields
    public DbField $_UserID;
    public DbField $_Username;
    public DbField $_Password;
    public DbField $UserLevel;
    public DbField $FirstName;
    public DbField $LastName;
    public DbField $CompleteName;
    public DbField $BirthDate;
    public DbField $HomePhone;
    public DbField $Photo;
    public DbField $Notes;
    public DbField $ReportsTo;
    public DbField $Gender;
    public DbField $_Email;
    public DbField $Activated;
    public DbField $_Profile;
    public DbField $Avatar;
    public DbField $ActiveStatus;
    public DbField $MessengerColor;
    public DbField $CreatedAt;
    public DbField $CreatedBy;
    public DbField $UpdatedAt;
    public DbField $UpdatedBy;

    // Page ID
    public string $PageID = ""; // To be set by subclass

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        $this->TableVar = "users";
        $this->TableName = 'users';
        $this->TableType = "TABLE";
        $this->ImportUseTransaction = $this->supportsTransaction() && Config("IMPORT_USE_TRANSACTION");
        $this->UseTransaction = $this->supportsTransaction() && Config("USE_TRANSACTION");
        $this->UpdateTable = "users"; // Update table
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
        $this->BasicSearch = new BasicSearch($this, Session(), $this->language);

        // UserID
        $this->_UserID = new DbField(
            $this, // Table
            'x__UserID', // Variable name
            'UserID', // Name
            '`UserID`', // Expression
            '`UserID`', // Basic search expression
            3, // Type
            11, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`UserID`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'NO' // Edit Tag
        );
        $this->_UserID->InputTextType = "text";
        $this->_UserID->Raw = true;
        $this->_UserID->IsAutoIncrement = true; // Autoincrement field
        $this->_UserID->IsPrimaryKey = true; // Primary key field
        $this->_UserID->Nullable = false; // NOT NULL field
        $this->_UserID->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->_UserID->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN"];
        $this->Fields['UserID'] = &$this->_UserID;

        // Username
        $this->_Username = new DbField(
            $this, // Table
            'x__Username', // Variable name
            'Username', // Name
            '`Username`', // Expression
            '`Username`', // Basic search expression
            200, // Type
            50, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Username`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->_Username->InputTextType = "text";
        $this->_Username->Raw = true;
        $this->_Username->Nullable = false; // NOT NULL field
        $this->_Username->Required = true; // Required field
        $this->_Username->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['Username'] = &$this->_Username;

        // Password
        $this->_Password = new DbField(
            $this, // Table
            'x__Password', // Variable name
            'Password', // Name
            '`Password`', // Expression
            '`Password`', // Basic search expression
            200, // Type
            255, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Password`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'PASSWORD' // Edit Tag
        );
        $this->_Password->InputTextType = "text";
        $this->_Password->Raw = true;
        $this->_Password->Required = true; // Required field
        $this->_Password->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Password'] = &$this->_Password;

        // UserLevel
        $this->UserLevel = new DbField(
            $this, // Table
            'x_UserLevel', // Variable name
            'UserLevel', // Name
            '`UserLevel`', // Expression
            '`UserLevel`', // Basic search expression
            3, // Type
            11, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`UserLevel`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'SELECT' // Edit Tag
        );
        $this->UserLevel->addMethod("getDefault", fn() => 0);
        $this->UserLevel->InputTextType = "text";
        $this->UserLevel->Raw = true;
        $this->UserLevel->IsForeignKey = true; // Foreign key field
        $this->UserLevel->setSelectMultiple(false); // Select one
        $this->UserLevel->UsePleaseSelect = true; // Use PleaseSelect by default
        $this->UserLevel->PleaseSelectText = $this->language->phrase("PleaseSelect"); // "PleaseSelect" text
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->UserLevel->Lookup = new Lookup($this->UserLevel, 'userlevels', false, 'ID', ["Name","","",""], '', "", [], [], [], [], [], [], false, '', '', "`Name`");
                break;
            case "id-ID":
                $this->UserLevel->Lookup = new Lookup($this->UserLevel, 'userlevels', false, 'ID', ["Name","","",""], '', "", [], [], [], [], [], [], false, '', '', "`Name`");
                break;
            default:
                $this->UserLevel->Lookup = new Lookup($this->UserLevel, 'userlevels', false, 'ID', ["Name","","",""], '', "", [], [], [], [], [], [], false, '', '', "`Name`");
                break;
        }
        $this->UserLevel->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->UserLevel->SearchOperators = ["=", "<>", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['UserLevel'] = &$this->UserLevel;

        // FirstName
        $this->FirstName = new DbField(
            $this, // Table
            'x_FirstName', // Variable name
            'FirstName', // Name
            '`FirstName`', // Expression
            '`FirstName`', // Basic search expression
            200, // Type
            50, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`FirstName`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->FirstName->InputTextType = "text";
        $this->FirstName->Nullable = false; // NOT NULL field
        $this->FirstName->Required = true; // Required field
        $this->FirstName->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['FirstName'] = &$this->FirstName;

        // LastName
        $this->LastName = new DbField(
            $this, // Table
            'x_LastName', // Variable name
            'LastName', // Name
            '`LastName`', // Expression
            '`LastName`', // Basic search expression
            200, // Type
            50, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`LastName`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->LastName->InputTextType = "text";
        $this->LastName->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['LastName'] = &$this->LastName;

        // CompleteName
        $this->CompleteName = new DbField(
            $this, // Table
            'x_CompleteName', // Variable name
            'CompleteName', // Name
            '`CompleteName`', // Expression
            '`CompleteName`', // Basic search expression
            200, // Type
            100, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`CompleteName`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->CompleteName->InputTextType = "text";
        $this->CompleteName->Nullable = false; // NOT NULL field
        $this->CompleteName->Required = true; // Required field
        $this->CompleteName->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"];
        $this->Fields['CompleteName'] = &$this->CompleteName;

        // BirthDate
        $this->BirthDate = new DbField(
            $this, // Table
            'x_BirthDate', // Variable name
            'BirthDate', // Name
            '`BirthDate`', // Expression
            CastDateFieldForLike("`BirthDate`", 2, "DB"), // Basic search expression
            135, // Type
            19, // Size
            2, // Date/Time format
            false, // Is upload field
            '`BirthDate`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->BirthDate->InputTextType = "text";
        $this->BirthDate->Raw = true;
        $this->BirthDate->DefaultErrorMessage = str_replace("%s", DateFormat(2), $this->language->phrase("IncorrectDate"));
        $this->BirthDate->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['BirthDate'] = &$this->BirthDate;

        // HomePhone
        $this->HomePhone = new DbField(
            $this, // Table
            'x_HomePhone', // Variable name
            'HomePhone', // Name
            '`HomePhone`', // Expression
            '`HomePhone`', // Basic search expression
            200, // Type
            24, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`HomePhone`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->HomePhone->InputTextType = "text";
        $this->HomePhone->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['HomePhone'] = &$this->HomePhone;

        // Photo
        $this->Photo = new DbField(
            $this, // Table
            'x_Photo', // Variable name
            'Photo', // Name
            '`Photo`', // Expression
            '`Photo`', // Basic search expression
            200, // Type
            50, // Size
            -1, // Date/Time format
            true, // Is upload field
            '`Photo`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'IMAGE', // View Tag
            'FILE' // Edit Tag
        );
        $this->Photo->addMethod("getUploadPath", fn() => "userphotos/");
        $this->Photo->InputTextType = "text";
        $this->Photo->ImageResize = true;
        $this->Photo->SearchOperators = ["=", "<>", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Photo'] = &$this->Photo;

        // Notes
        $this->Notes = new DbField(
            $this, // Table
            'x_Notes', // Variable name
            'Notes', // Name
            '`Notes`', // Expression
            '`Notes`', // Basic search expression
            201, // Type
            2147483647, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Notes`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXTAREA' // Edit Tag
        );
        $this->Notes->InputTextType = "text";
        $this->Notes->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Notes'] = &$this->Notes;

        // ReportsTo
        $this->ReportsTo = new DbField(
            $this, // Table
            'x_ReportsTo', // Variable name
            'ReportsTo', // Name
            '`ReportsTo`', // Expression
            '`ReportsTo`', // Basic search expression
            3, // Type
            11, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`ReportsTo`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->ReportsTo->InputTextType = "text";
        $this->ReportsTo->Raw = true;
        $this->ReportsTo->DefaultErrorMessage = $this->language->phrase("IncorrectInteger");
        $this->ReportsTo->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['ReportsTo'] = &$this->ReportsTo;

        // Gender
        $this->Gender = new DbField(
            $this, // Table
            'x_Gender', // Variable name
            'Gender', // Name
            '`Gender`', // Expression
            '`Gender`', // Basic search expression
            200, // Type
            10, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Gender`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'SELECT' // Edit Tag
        );
        $this->Gender->InputTextType = "text";
        $this->Gender->Nullable = false; // NOT NULL field
        $this->Gender->Required = true; // Required field
        $this->Gender->setSelectMultiple(false); // Select one
        $this->Gender->UsePleaseSelect = true; // Use PleaseSelect by default
        $this->Gender->PleaseSelectText = $this->language->phrase("PleaseSelect"); // "PleaseSelect" text
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Gender->Lookup = new Lookup($this->Gender, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->Gender->Lookup = new Lookup($this->Gender, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Gender->Lookup = new Lookup($this->Gender, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Gender->OptionCount = 2;
        $this->Gender->SearchOperators = ["=", "<>"];
        $this->Fields['Gender'] = &$this->Gender;

        // Email
        $this->_Email = new DbField(
            $this, // Table
            'x__Email', // Variable name
            'Email', // Name
            '`Email`', // Expression
            '`Email`', // Basic search expression
            200, // Type
            255, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Email`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->_Email->InputTextType = "text";
        $this->_Email->DefaultErrorMessage = $this->language->phrase("IncorrectEmail");
        $this->_Email->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Email'] = &$this->_Email;

        // Activated
        $this->Activated = new DbField(
            $this, // Table
            'x_Activated', // Variable name
            'Activated', // Name
            '`Activated`', // Expression
            '`Activated`', // Basic search expression
            200, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Activated`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->Activated->InputTextType = "text";
        $this->Activated->Raw = true;
        $this->Activated->setDataType(DataType::BOOLEAN);
        $this->Activated->TrueValue = "Y";
        $this->Activated->FalseValue = "N";
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->Activated->Lookup = new Lookup($this->Activated, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->Activated->Lookup = new Lookup($this->Activated, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->Activated->Lookup = new Lookup($this->Activated, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->Activated->OptionCount = 2;
        $this->Activated->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['Activated'] = &$this->Activated;

        // Profile
        $this->_Profile = new DbField(
            $this, // Table
            'x__Profile', // Variable name
            'Profile', // Name
            '`Profile`', // Expression
            '`Profile`', // Basic search expression
            201, // Type
            2147483647, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`Profile`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXTAREA' // Edit Tag
        );
        $this->_Profile->InputTextType = "text";
        $this->_Profile->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Profile'] = &$this->_Profile;

        // Avatar
        $this->Avatar = new DbField(
            $this, // Table
            'x_Avatar', // Variable name
            'Avatar', // Name
            '`Avatar`', // Expression
            '`Avatar`', // Basic search expression
            200, // Type
            255, // Size
            -1, // Date/Time format
            true, // Is upload field
            '`Avatar`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'IMAGE', // View Tag
            'FILE' // Edit Tag
        );
        $this->Avatar->InputTextType = "text";
        $this->Avatar->SearchOperators = ["=", "<>", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['Avatar'] = &$this->Avatar;

        // ActiveStatus
        $this->ActiveStatus = new DbField(
            $this, // Table
            'x_ActiveStatus', // Variable name
            'ActiveStatus', // Name
            '`ActiveStatus`', // Expression
            '`ActiveStatus`', // Basic search expression
            16, // Type
            1, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`ActiveStatus`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'CHECKBOX' // Edit Tag
        );
        $this->ActiveStatus->InputTextType = "text";
        $this->ActiveStatus->Raw = true;
        $this->ActiveStatus->setDataType(DataType::BOOLEAN);
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->ActiveStatus->Lookup = new Lookup($this->ActiveStatus, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            case "id-ID":
                $this->ActiveStatus->Lookup = new Lookup($this->ActiveStatus, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
            default:
                $this->ActiveStatus->Lookup = new Lookup($this->ActiveStatus, 'users', false, '', ["","","",""], '', "", [], [], [], [], [], [], false, '', '', "");
                break;
        }
        $this->ActiveStatus->OptionCount = 2;
        $this->ActiveStatus->DefaultErrorMessage = $this->language->phrase("IncorrectValueRegExp");
        $this->ActiveStatus->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['ActiveStatus'] = &$this->ActiveStatus;

        // MessengerColor
        $this->MessengerColor = new DbField(
            $this, // Table
            'x_MessengerColor', // Variable name
            'MessengerColor', // Name
            '`MessengerColor`', // Expression
            '`MessengerColor`', // Basic search expression
            200, // Type
            255, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`MessengerColor`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->MessengerColor->InputTextType = "text";
        $this->MessengerColor->SearchOperators = ["=", "<>", "IN", "NOT IN", "STARTS WITH", "NOT STARTS WITH", "LIKE", "NOT LIKE", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY", "IS NULL", "IS NOT NULL"];
        $this->Fields['MessengerColor'] = &$this->MessengerColor;

        // CreatedAt
        $this->CreatedAt = new DbField(
            $this, // Table
            'x_CreatedAt', // Variable name
            'CreatedAt', // Name
            '`CreatedAt`', // Expression
            CastDateFieldForLike("`CreatedAt`", 1, "DB"), // Basic search expression
            135, // Type
            19, // Size
            1, // Date/Time format
            false, // Is upload field
            '`CreatedAt`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->CreatedAt->addMethod("getDefault", fn() => CurrentDateTime());
        $this->CreatedAt->InputTextType = "text";
        $this->CreatedAt->Raw = true;
        $this->CreatedAt->DefaultErrorMessage = str_replace("%s", DateFormat(1), $this->language->phrase("IncorrectDate"));
        $this->CreatedAt->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['CreatedAt'] = &$this->CreatedAt;

        // CreatedBy
        $this->CreatedBy = new DbField(
            $this, // Table
            'x_CreatedBy', // Variable name
            'CreatedBy', // Name
            '`CreatedBy`', // Expression
            '`CreatedBy`', // Basic search expression
            200, // Type
            20, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`CreatedBy`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'SELECT' // Edit Tag
        );
        $this->CreatedBy->addMethod("getDefault", fn() => CurrentUserName());
        $this->CreatedBy->InputTextType = "text";
        $this->CreatedBy->setSelectMultiple(false); // Select one
        $this->CreatedBy->UsePleaseSelect = true; // Use PleaseSelect by default
        $this->CreatedBy->PleaseSelectText = $this->language->phrase("PleaseSelect"); // "PleaseSelect" text
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->CreatedBy->Lookup = new Lookup($this->CreatedBy, 'users', false, 'Username', ["FirstName","LastName","",""], '', "", [], [], [], [], [], [], false, '', '', "CONCAT(COALESCE(`FirstName`, ''),'" . ValueSeparator(1, $this->CreatedBy) . "',COALESCE(`LastName`,''))");
                break;
            case "id-ID":
                $this->CreatedBy->Lookup = new Lookup($this->CreatedBy, 'users', false, 'Username', ["FirstName","LastName","",""], '', "", [], [], [], [], [], [], false, '', '', "CONCAT(COALESCE(`FirstName`, ''),'" . ValueSeparator(1, $this->CreatedBy) . "',COALESCE(`LastName`,''))");
                break;
            default:
                $this->CreatedBy->Lookup = new Lookup($this->CreatedBy, 'users', false, 'Username', ["FirstName","LastName","",""], '', "", [], [], [], [], [], [], false, '', '', "CONCAT(COALESCE(`FirstName`, ''),'" . ValueSeparator(1, $this->CreatedBy) . "',COALESCE(`LastName`,''))");
                break;
        }
        $this->CreatedBy->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['CreatedBy'] = &$this->CreatedBy;

        // UpdatedAt
        $this->UpdatedAt = new DbField(
            $this, // Table
            'x_UpdatedAt', // Variable name
            'UpdatedAt', // Name
            '`UpdatedAt`', // Expression
            CastDateFieldForLike("`UpdatedAt`", 1, "DB"), // Basic search expression
            135, // Type
            19, // Size
            1, // Date/Time format
            false, // Is upload field
            '`UpdatedAt`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'TEXT' // Edit Tag
        );
        $this->UpdatedAt->addMethod("getAutoUpdateValue", fn() => CurrentDateTime());
        $this->UpdatedAt->InputTextType = "text";
        $this->UpdatedAt->Raw = true;
        $this->UpdatedAt->DefaultErrorMessage = str_replace("%s", DateFormat(1), $this->language->phrase("IncorrectDate"));
        $this->UpdatedAt->SearchOperators = ["=", "<>", "IN", "NOT IN", "<", "<=", ">", ">=", "BETWEEN", "NOT BETWEEN", "IS NULL", "IS NOT NULL"];
        $this->Fields['UpdatedAt'] = &$this->UpdatedAt;

        // UpdatedBy
        $this->UpdatedBy = new DbField(
            $this, // Table
            'x_UpdatedBy', // Variable name
            'UpdatedBy', // Name
            '`UpdatedBy`', // Expression
            '`UpdatedBy`', // Basic search expression
            200, // Type
            20, // Size
            -1, // Date/Time format
            false, // Is upload field
            '`UpdatedBy`', // Virtual expression
            false, // Is virtual
            false, // Force selection
            false, // Is Virtual search
            'FORMATTED TEXT', // View Tag
            'SELECT' // Edit Tag
        );
        $this->UpdatedBy->addMethod("getAutoUpdateValue", fn() => CurrentUserName());
        $this->UpdatedBy->InputTextType = "text";
        $this->UpdatedBy->setSelectMultiple(false); // Select one
        $this->UpdatedBy->UsePleaseSelect = true; // Use PleaseSelect by default
        $this->UpdatedBy->PleaseSelectText = $this->language->phrase("PleaseSelect"); // "PleaseSelect" text
        global $CurrentLanguage;
        switch ($CurrentLanguage) {
            case "en-US":
                $this->UpdatedBy->Lookup = new Lookup($this->UpdatedBy, 'users', false, 'Username', ["FirstName","LastName","",""], '', "", [], [], [], [], [], [], false, '', '', "CONCAT(COALESCE(`FirstName`, ''),'" . ValueSeparator(1, $this->UpdatedBy) . "',COALESCE(`LastName`,''))");
                break;
            case "id-ID":
                $this->UpdatedBy->Lookup = new Lookup($this->UpdatedBy, 'users', false, 'Username', ["FirstName","LastName","",""], '', "", [], [], [], [], [], [], false, '', '', "CONCAT(COALESCE(`FirstName`, ''),'" . ValueSeparator(1, $this->UpdatedBy) . "',COALESCE(`LastName`,''))");
                break;
            default:
                $this->UpdatedBy->Lookup = new Lookup($this->UpdatedBy, 'users', false, 'Username', ["FirstName","LastName","",""], '', "", [], [], [], [], [], [], false, '', '', "CONCAT(COALESCE(`FirstName`, ''),'" . ValueSeparator(1, $this->UpdatedBy) . "',COALESCE(`LastName`,''))");
                break;
        }
        $this->UpdatedBy->SearchOperators = ["=", "<>", "IS NULL", "IS NOT NULL"];
        $this->Fields['UpdatedBy'] = &$this->UpdatedBy;

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

    // Current master table name
    public function getCurrentMasterTable(): ?string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_MASTER_TABLE")));
    }

    public function setCurrentMasterTable(?string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_MASTER_TABLE")), $v);
    }

    // Get master WHERE clause from session values
    public function getMasterFilterFromSession(): string
    {
        // Master filter
        $masterFilter = "";
        if ($this->getCurrentMasterTable() == "userlevels") {
            $masterTable = Container("userlevels");
            if ($this->UserLevel->getSessionValue() != "") {
                $masterFilter .= "" . GetKeyFilter($masterTable->ID, $this->UserLevel->getSessionValue(), $masterTable->ID->DataType, $masterTable->Dbid);
            } else {
                return "";
            }
        }
        return $masterFilter;
    }

    // Get detail WHERE clause from session values
    public function getDetailFilterFromSession(): string
    {
        // Detail filter
        $detailFilter = "";
        if ($this->getCurrentMasterTable() == "userlevels") {
            $masterTable = Container("userlevels");
            if ($this->UserLevel->getSessionValue() != "") {
                $detailFilter .= "" . GetKeyFilter($this->UserLevel, $this->UserLevel->getSessionValue(), $masterTable->ID->DataType, $this->Dbid);
            } else {
                return "";
            }
        }
        return $detailFilter;
    }

    /**
     * Get master filter
     *
     * @param object $masterTable Master Table
     * @param array $keys Detail Keys
     * @return mixed NULL is returned if all keys are empty, Empty string is returned if some keys are empty and is required
     */
    public function getMasterFilter(DbTableBase $masterTable, array $keys): ?string
    {
        $validKeys = true;
        switch ($masterTable->TableVar) {
            case "userlevels":
                $key = $keys["UserLevel"] ?? "";
                if (IsEmpty($key)) {
                    if ($masterTable->ID->Required) { // Required field and empty value
                        return ""; // Return empty filter
                    }
                    $validKeys = false;
                } elseif (!$validKeys) { // Already has empty key
                    return ""; // Return empty filter
                }
                if ($validKeys) {
                    return GetKeyFilter($masterTable->ID, $keys["UserLevel"], $this->UserLevel->DataType, $this->Dbid);
                }
                break;
        }
        return null; // All null values and no required fields
    }

    // Get detail filter
    public function getDetailFilter(DbTableBase $masterTable): string
    {
        switch ($masterTable->TableVar) {
            case "userlevels":
                return GetKeyFilter($this->UserLevel, $masterTable->ID->DbValue, $masterTable->ID->DataType, $masterTable->Dbid);
        }
        return "";
    }

    // Render X Axis for chart
    public function renderChartXAxis(string $chartVar, array $chartRow): array
    {
        return $chartRow;
    }

    // Get FROM clause
    public function getSqlFrom(): string
    {
        return ($this->SqlFrom != "") ? $this->SqlFrom : "users";
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
        // Add User ID filter
        if ($this->security->currentUserID() != "" && !$this->security->canAccess()) { // No access permission
            $filter = $this->addUserIDFilter($filter, $id);
        }
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
            if (Config("ENCRYPTED_PASSWORD") && $name == Config("LOGIN_PASSWORD_FIELD_NAME")) {
                $value = HashPassword($value);
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
            $this->_UserID->setDbValue($conn->lastInsertId());
            $row['UserID'] = $this->_UserID->DbValue;
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
            if (Config("ENCRYPTED_PASSWORD") && $name == Config("LOGIN_PASSWORD_FIELD_NAME")) {
                if ($value == $this->Fields[$name]->OldValue) { // No need to update hashed password if not changed
                    continue;
                }
                $value = HashPassword($value);
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
            if (!isset($row['UserID']) && !IsEmpty($this->_UserID->CurrentValue)) {
                $row['UserID'] = $this->_UserID->CurrentValue;
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
            if (array_key_exists('UserID', $row)) {
                AddFilter($where, QuotedName('UserID', $this->Dbid) . '=' . QuotedValue($row['UserID'], $this->_UserID->DataType, $this->Dbid));
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
        $this->_UserID->DbValue = $row['UserID'];
        $this->_Username->DbValue = $row['Username'];
        $this->_Password->DbValue = $row['Password'];
        $this->UserLevel->DbValue = $row['UserLevel'];
        $this->FirstName->DbValue = $row['FirstName'];
        $this->LastName->DbValue = $row['LastName'];
        $this->CompleteName->DbValue = $row['CompleteName'];
        $this->BirthDate->DbValue = $row['BirthDate'];
        $this->HomePhone->DbValue = $row['HomePhone'];
        $this->Photo->Upload->DbValue = $row['Photo'];
        $this->Notes->DbValue = $row['Notes'];
        $this->ReportsTo->DbValue = $row['ReportsTo'];
        $this->Gender->DbValue = $row['Gender'];
        $this->_Email->DbValue = $row['Email'];
        $this->Activated->DbValue = $row['Activated'];
        $this->_Profile->DbValue = $row['Profile'];
        $this->Avatar->Upload->DbValue = $row['Avatar'];
        $this->ActiveStatus->DbValue = $row['ActiveStatus'];
        $this->MessengerColor->DbValue = $row['MessengerColor'];
        $this->CreatedAt->DbValue = $row['CreatedAt'];
        $this->CreatedBy->DbValue = $row['CreatedBy'];
        $this->UpdatedAt->DbValue = $row['UpdatedAt'];
        $this->UpdatedBy->DbValue = $row['UpdatedBy'];
    }

    // Delete uploaded files
    public function deleteUploadedFiles(array $row)
    {
        $this->loadDbValues($row);
        $this->Photo->OldUploadPath = $this->Photo->getUploadPath(); // PHP
        $oldFiles = IsEmpty($row['Photo']) ? [] : [$row['Photo']];
        foreach ($oldFiles as $oldFile) {
            $file = PathJoin($this->Photo->OldUploadPath, $oldFile);
            if (FileExists($file)) {
                DeleteFile($file);
            }
        }
        $oldFiles = IsEmpty($row['Avatar']) ? [] : [$row['Avatar']];
        foreach ($oldFiles as $oldFile) {
            $file = PathJoin($this->Avatar->OldUploadPath, $oldFile);
            if (FileExists($file)) {
                DeleteFile($file);
            }
        }
    }

    // Record filter WHERE clause
    protected function sqlKeyFilter(): string
    {
        return "`UserID` = @_UserID@";
    }

    // Get Key from record
    public function getKeyFromRecord(array $row, ?string $keySeparator = null): string
    {
        $keys = [];
        $val = $row['UserID'];
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
        $val = $current ? $this->_UserID->CurrentValue : $this->_UserID->OldValue;
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
                $this->_UserID->CurrentValue = $keys[0];
            } else {
                $this->_UserID->OldValue = $keys[0];
            }
        }
    }

    // Get record filter
    public function getRecordFilter(?array $row = null, bool $current = false): string
    {
        $keyFilter = $this->sqlKeyFilter();
        if (is_array($row)) {
            $val = array_key_exists('UserID', $row) ? $row['UserID'] : null;
        } else {
            $val = !IsEmpty($this->_UserID->OldValue) && !$current ? $this->_UserID->OldValue : $this->_UserID->CurrentValue;
        }
        if (!is_numeric($val)) {
            return "0=1"; // Invalid key
        }
        if ($val === null) {
            return "0=1"; // Invalid key
        } else {
            $keyFilter = str_replace("@_UserID@", AdjustSql($val), $keyFilter); // Replace key value
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
        return Session($name) ?? GetUrl("userslist");
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
            "usersview" => $this->language->phrase("View"),
            "usersedit" => $this->language->phrase("Edit"),
            "usersadd" => $this->language->phrase("Add"),
            default => ""
        };
    }

    // Default route URL
    public function getDefaultRouteUrl(): string
    {
        return "userslist";
    }

    // API page name
    public function getApiPageName(string $action): string
    {
        return match (strtolower($action)) {
            Config("API_VIEW_ACTION") => "UsersView",
            Config("API_ADD_ACTION") => "UsersAdd",
            Config("API_EDIT_ACTION") => "UsersEdit",
            Config("API_DELETE_ACTION") => "UsersDelete",
            Config("API_LIST_ACTION") => "UsersList",
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
        return "userslist";
    }

    // View URL
    public function getViewUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = $this->keyUrl("usersview", $parm);
        } else {
            $url = $this->keyUrl("usersview", Config("TABLE_SHOW_DETAIL") . "=");
        }
        return $this->addMasterUrl($url);
    }

    // Add URL
    public function getAddUrl(string $parm = ""): string
    {
        if ($parm != "") {
            $url = "usersadd?" . $parm;
        } else {
            $url = "usersadd";
        }
        return $this->addMasterUrl($url);
    }

    // Edit URL
    public function getEditUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("usersedit", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline edit URL
    public function getInlineEditUrl(): string
    {
        $url = $this->keyUrl("userslist", "action=edit");
        return $this->addMasterUrl($url);
    }

    // Copy URL
    public function getCopyUrl(string $parm = ""): string
    {
        $url = $this->keyUrl("usersadd", $parm);
        return $this->addMasterUrl($url);
    }

    // Inline copy URL
    public function getInlineCopyUrl(): string
    {
        $url = $this->keyUrl("userslist", "action=copy");
        return $this->addMasterUrl($url);
    }

    // Delete URL
    public function getDeleteUrl(string $parm = ""): string
    {
        if ($this->UseAjaxActions && ConvertToBool(Param("infinitescroll")) && CurrentPageID() == "list") {
            return $this->keyUrl(GetApiUrl(Config("API_DELETE_ACTION") . "/" . $this->TableVar));
        } else {
            return $this->keyUrl("usersdelete", $parm);
        }
    }

    // Add master url
    public function addMasterUrl(string $url): string
    {
        if ($this->getCurrentMasterTable() == "userlevels" && !ContainsString($url, Config("TABLE_SHOW_MASTER") . "=")) {
            $url .= (ContainsString($url, "?") ? "&" : "?") . Config("TABLE_SHOW_MASTER") . "=" . $this->getCurrentMasterTable();
            $url .= "&" . GetForeignKeyUrl("fk_ID", $this->UserLevel->getSessionValue()); // Use Session Value
        }
        return $url;
    }

    public function keyToJson(bool $htmlEncode = false): string
    {
        $json = "";
        $json .= "\"_UserID\":" . VarToJson($this->_UserID->CurrentValue, "number");
        $json = "{" . $json . "}";
        if ($htmlEncode) {
            $json = HtmlEncode($json);
        }
        return $json;
    }

    // Add key value to URL
    public function keyUrl(string $url, string $parm = ""): string
    {
        if ($this->_UserID->CurrentValue !== null) {
            $url .= "/" . $this->encodeKeyValue($this->_UserID->CurrentValue);
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
            if (($keyValue = Param("_UserID") ?? Route("_UserID")) !== null) {
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
                $this->_UserID->CurrentValue = $key;
            } else {
                $this->_UserID->OldValue = $key;
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
        $this->Notes->setDbValue($row['Notes']);
        $this->ReportsTo->setDbValue($row['ReportsTo']);
        $this->Gender->setDbValue($row['Gender']);
        $this->_Email->setDbValue($row['Email']);
        $this->Activated->setDbValue($row['Activated']);
        $this->_Profile->setDbValue($row['Profile']);
        $this->Avatar->Upload->DbValue = $row['Avatar'];
        $this->ActiveStatus->setDbValue($row['ActiveStatus']);
        $this->MessengerColor->setDbValue($row['MessengerColor']);
        $this->CreatedAt->setDbValue($row['CreatedAt']);
        $this->CreatedBy->setDbValue($row['CreatedBy']);
        $this->UpdatedAt->setDbValue($row['UpdatedAt']);
        $this->UpdatedBy->setDbValue($row['UpdatedBy']);
    }

    // Render list content
    public function renderListContent(string $filter)
    {
        global $Response;
        $container = Container();
        $listPage = "UsersList";
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

        // Password
        $this->_Password->HrefValue = "";
        $this->_Password->TooltipValue = "";

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
                    $doc->exportCaption($this->_UserID);
                    $doc->exportCaption($this->_Username);
                    $doc->exportCaption($this->UserLevel);
                    $doc->exportCaption($this->FirstName);
                    $doc->exportCaption($this->LastName);
                    $doc->exportCaption($this->CompleteName);
                    $doc->exportCaption($this->BirthDate);
                    $doc->exportCaption($this->HomePhone);
                    $doc->exportCaption($this->Photo);
                    $doc->exportCaption($this->Notes);
                    $doc->exportCaption($this->ReportsTo);
                    $doc->exportCaption($this->Gender);
                    $doc->exportCaption($this->_Email);
                    $doc->exportCaption($this->Activated);
                    $doc->exportCaption($this->_Profile);
                    $doc->exportCaption($this->Avatar);
                    $doc->exportCaption($this->ActiveStatus);
                    $doc->exportCaption($this->MessengerColor);
                    $doc->exportCaption($this->CreatedAt);
                    $doc->exportCaption($this->CreatedBy);
                    $doc->exportCaption($this->UpdatedAt);
                    $doc->exportCaption($this->UpdatedBy);
                } else {
					$doc->exportRawCaption("#");
                    $doc->exportCaption($this->_UserID);
                    $doc->exportCaption($this->_Username);
                    $doc->exportCaption($this->_Password);
                    $doc->exportCaption($this->UserLevel);
                    $doc->exportCaption($this->FirstName);
                    $doc->exportCaption($this->LastName);
                    $doc->exportCaption($this->CompleteName);
                    $doc->exportCaption($this->BirthDate);
                    $doc->exportCaption($this->HomePhone);
                    $doc->exportCaption($this->Photo);
                    $doc->exportCaption($this->ReportsTo);
                    $doc->exportCaption($this->Gender);
                    $doc->exportCaption($this->_Email);
                    $doc->exportCaption($this->Activated);
                    $doc->exportCaption($this->Avatar);
                    $doc->exportCaption($this->ActiveStatus);
                    $doc->exportCaption($this->MessengerColor);
                    $doc->exportCaption($this->CreatedAt);
                    $doc->exportCaption($this->CreatedBy);
                    $doc->exportCaption($this->UpdatedAt);
                    $doc->exportCaption($this->UpdatedBy);
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
						$doc->exportRawCaption("#");
						$doc->exportCaption($this->_UserID);
						$doc->exportCaption($this->_Username);
						$doc->exportCaption($this->_Password);
						$doc->exportCaption($this->UserLevel);
						$doc->exportCaption($this->FirstName);
						$doc->exportCaption($this->LastName);
						$doc->exportCaption($this->CompleteName);
						$doc->exportCaption($this->BirthDate);
						$doc->exportCaption($this->HomePhone);
						$doc->exportCaption($this->Photo);
						$doc->exportCaption($this->ReportsTo);
						$doc->exportCaption($this->Gender);
						$doc->exportCaption($this->_Email);
						$doc->exportCaption($this->Activated);
						$doc->exportCaption($this->Avatar);
						$doc->exportCaption($this->ActiveStatus);
						$doc->exportCaption($this->MessengerColor);
						$doc->exportCaption($this->CreatedAt);
						$doc->exportCaption($this->CreatedBy);
						$doc->exportCaption($this->UpdatedAt);
						$doc->exportCaption($this->UpdatedBy);
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
                        $doc->exportField($this->_UserID);
                        $doc->exportField($this->_Username);
                        $doc->exportField($this->UserLevel);
                        $doc->exportField($this->FirstName);
                        $doc->exportField($this->LastName);
                        $doc->exportField($this->CompleteName);
                        $doc->exportField($this->BirthDate);
                        $doc->exportField($this->HomePhone);
                        $doc->exportField($this->Photo);
                        $doc->exportField($this->Notes);
                        $doc->exportField($this->ReportsTo);
                        $doc->exportField($this->Gender);
                        $doc->exportField($this->_Email);
                        $doc->exportField($this->Activated);
                        $doc->exportField($this->_Profile);
                        $doc->exportField($this->Avatar);
                        $doc->exportField($this->ActiveStatus);
                        $doc->exportField($this->MessengerColor);
                        $doc->exportField($this->CreatedAt);
                        $doc->exportField($this->CreatedBy);
                        $doc->exportField($this->UpdatedAt);
                        $doc->exportField($this->UpdatedBy);
                    } else {
						$doc->exportRawData($seqRec); // by Masino Sinaga, September 11, 2023
                        $doc->exportField($this->_UserID);
                        $doc->exportField($this->_Username);
                        $doc->exportField($this->_Password);
                        $doc->exportField($this->UserLevel);
                        $doc->exportField($this->FirstName);
                        $doc->exportField($this->LastName);
                        $doc->exportField($this->CompleteName);
                        $doc->exportField($this->BirthDate);
                        $doc->exportField($this->HomePhone);
                        $doc->exportField($this->Photo);
                        $doc->exportField($this->ReportsTo);
                        $doc->exportField($this->Gender);
                        $doc->exportField($this->_Email);
                        $doc->exportField($this->Activated);
                        $doc->exportField($this->Avatar);
                        $doc->exportField($this->ActiveStatus);
                        $doc->exportField($this->MessengerColor);
                        $doc->exportField($this->CreatedAt);
                        $doc->exportField($this->CreatedBy);
                        $doc->exportField($this->UpdatedAt);
                        $doc->exportField($this->UpdatedBy);
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
        if ($name == "FirstName") {
            $clone = $this->FirstName->getClone()->setViewValue($value);
            $clone->ViewValue = $clone->CurrentValue;
            return $clone->getViewValue();
        }
        if ($name == "LastName") {
            $clone = $this->LastName->getClone()->setViewValue($value);
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

    // User ID filter
    public function getUserIDFilter(mixed $userId): string
    {
        $userIdExpression = $this->Fields[Config("USER_ID_FIELD_NAME")]->Expression;
        $userIdDataType = $this->Fields[Config("USER_ID_FIELD_NAME")]->DataType;
        $userIdFilter = $userIdExpression . ' = ' . QuotedValue($userId, $userIdDataType, Config("USER_TABLE_DBID"));
        $parentUserIdExpression = $this->Fields[Config("PARENT_USER_ID_FIELD_NAME")]->Expression;
        $parentUserIdDataType = $this->Fields[Config("PARENT_USER_ID_FIELD_NAME")]->DataType;
        $parentUserIdFilter = $userIdExpression . ' IN (SELECT ' . $userIdExpression . ' FROM ' . "users" . ' WHERE ' . $parentUserIdExpression . ' = ' . QuotedValue($userId, $parentUserIdDataType, Config("USER_TABLE_DBID")) . ')';
        AddFilter($userIdFilter, $parentUserIdFilter, "OR");
        if (count($this->security->UserLevelIDs) > 0) {
            $userLevelExpression = $this->Fields[Config("USER_LEVEL_FIELD_NAME")]->Expression;
            $userLevelUserIdFilter = $userIdExpression . ' IN (SELECT ' . $userIdExpression . ' FROM ' . "users" . ' WHERE ' . $userLevelExpression . ' IN (' . implode(", ", $this->security->UserLevelIDs) . '))';
            AddFilter($userIdFilter, $userLevelUserIdFilter, "OR");
        }
        return $userIdFilter;
    }

    // Add User ID filter
    public function addUserIDFilter(string $filter = "", string $id = ""): string
    {
        $filterWrk = "";
        if ($id == "") {
            $id = CurrentPageID() == "list" ? strval($this->CurrentAction) : CurrentPageID();
        }
        if (!$this->userIDAllow($id) && !$this->security->canAccess()) {
            $filterWrk = $this->security->userIdList();
            if ($filterWrk != "") {
                $filterWrk = '`UserID` IN (' . $filterWrk . ')';
            }
        }

        // Call User ID Filtering event
        $this->userIdFiltering($filterWrk);
        AddFilter($filter, $filterWrk);
        return $filter;
    }

    // Add Parent User ID filter
    public function addParentUserIDFilter(mixed $userId, string $id = ""): string
    {
        if ($id == "") {
            $id = CurrentPageID() == "list" ? strval($this->CurrentAction) : CurrentPageID();
        }
        if (!$this->userIDAllow($id) && !$this->security->canAccess()) {
            $result = $this->security->parentUserIDList($userId);
            if ($result != "") {
                $userIdExpression = $this->Fields[Config("USER_ID_FIELD_NAME")]->Expression;
                $result = $userIdExpression . ' IN (' . $result . ')';
            }
            return $result;
        }
        return "";
    }

    // User ID subquery
    public function getUserIDSubquery(DbField &$fld, DbField &$masterfld): string
    {
        $wrk = "";
        $sql = "SELECT " . $masterfld->Expression . " FROM users";
        $filter = $this->addUserIDFilter("");
        if ($filter != "") {
            $sql .= " WHERE " . $filter;
        }

        // List all values
        $conn = Conn($this->Dbid);
        if ($rows = $conn->executeCacheQuery($sql, [], [], $this->cacheProfile)->fetchAllNumeric()) {
            $wrk = implode(",", array_map(fn($row) => QuotedValue($row[0], $masterfld->DataType, $this->Dbid), $rows));
        }
        if ($wrk != "") {
            $wrk = $fld->Expression . " IN (" . $wrk . ")";
        } else { // No User ID value found
            $wrk = "0=1";
        }
        return $wrk;
    }

    // Get file data
    public function getFileData(string $fldparm, string $key, bool $resize, int $width = 0, int $height = 0, array $plugins = []): Response
    {
        global $DownloadFileName;
        $width = ($width > 0) ? $width : Config("THUMBNAIL_DEFAULT_WIDTH");
        $height = ($height > 0) ? $height : Config("THUMBNAIL_DEFAULT_HEIGHT");

        // Set up field name / file name field / file type field
        $fldName = "";
        $fileNameFld = "";
        $fileTypeFld = "";
        if ($fldparm == 'Photo') {
            $fldName = "Photo";
            $fileNameFld = "Photo";
        } elseif ($fldparm == 'Avatar') {
            $fldName = "Avatar";
            $fileNameFld = "Avatar";
        } else {
            throw new InvalidArgumentException("Incorrect field '" . $fldparm . "'"); // Incorrect field
        }

        // Set up key values
        $ar = explode(Config("COMPOSITE_KEY_SEPARATOR"), $key);
        if (count($ar) == 1) {
            $this->_UserID->CurrentValue = $ar[0];
        } else {
            throw new InvalidArgumentException("Incorrect key '" . $key . "'"); // Incorrect key
        }

        // Set up filter (WHERE Clause)
        $filter = $this->getRecordFilter();
        $this->CurrentFilter = $filter;
        $sql = $this->getCurrentSql();
        $conn = $this->getConnection();
        $dbtype = GetConnectionType($this->Dbid);
        $response = ResponseFactory()->createResponse();
        if ($row = $conn->fetchAssociative($sql)) {
            $val = $row[$fldName];
            if (!IsEmpty($val)) {
                $fld = $this->Fields[$fldName];

                // Binary data
                if ($fld->DataType == DataType::BLOB) {
                    if ($dbtype != "MYSQL") {
                        if (is_resource($val) && get_resource_type($val) == "stream") { // Byte array
                            $val = stream_get_contents($val);
                        }
                    }
                    if ($resize) {
                        ResizeBinary($val, $width, $height, plugins: $plugins);
                    }

                    // Write file type
                    if ($fileTypeFld != "" && !IsEmpty($row[$fileTypeFld])) {
                        $response = $response->withHeader("Content-type", $row[$fileTypeFld]);
                    } else {
                        $response = $response->withHeader("Content-type", ContentType($val));
                    }

                    // Write file name
                    $downloadPdf = !Config("EMBED_PDF") && Config("DOWNLOAD_PDF_FILE");
                    if ($fileNameFld != "" && !IsEmpty($row[$fileNameFld])) {
                        $fileName = $row[$fileNameFld];
                        $pathinfo = pathinfo($fileName);
                        $ext = strtolower($pathinfo["extension"] ?? "");
                        $isPdf = SameText($ext, "pdf");
                        if ($downloadPdf || !$isPdf) { // Skip header if not download PDF
                            $response = $response->withHeader("Content-Disposition", "attachment; filename=\"" . $fileName . "\"");
                        }
                    } else {
                        $ext = ContentExtension($val);
                        $isPdf = SameText($ext, ".pdf");
                        if ($isPdf && $downloadPdf) { // Add header if download PDF
                            $response = $response->withHeader("Content-Disposition", "attachment" . ($DownloadFileName ? "; filename=\"" . $DownloadFileName . "\"" : ""));
                        }
                    }

                    // Write file data
                    if (
                        StartsString("PK", $val)
                        && ContainsString($val, "[Content_Types].xml")
                        && ContainsString($val, "_rels")
                        && ContainsString($val, "docProps")
                    ) { // Fix Office 2007 documents
                        if (!EndsString("\0\0\0", $val)) { // Not ends with 3 or 4 \0
                            $val .= "\0\0\0\0";
                        }
                    }

                    // Clear any debug message
                    if (ob_get_length()) {
                        ob_end_clean();
                    }

                    // Write binary data
                    $response = $response->write($val);

                // Upload to folder
                } else {
                    if ($fld->UploadMultiple) {
                        $files = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $val);
                    } else {
                        $files = [$val];
                    }
                    $data = [];
                    $ar = [];
                    if ($fld->hasMethod("getUploadPath")) { // Check field level upload path
                        $fld->UploadPath = $fld->getUploadPath();
                    }
                    foreach ($files as $file) {
                        if (!IsEmpty($file)) {
                            if (Config("ENCRYPT_FILE_PATH")) {
                                $ar[$file] = FullUrl(GetApiUrl(Config("API_FILE_ACTION") .
                                    "/" . $this->TableVar . "/" . Encrypt($fld->uploadPath() . $file)));
                            } else {
                                $ar[$file] = FullUrl($fld->hrefPath() . $file);
                            }
                        }
                    }
                    $data[$fld->Param] = $ar;
                    $response = $response->withJson($data);
                }
            }
        }
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
        $newRow["CreatedAt"] = CurrentDateTime();
        $newRow["CreatedBy"] = CurrentUserName();
        if (!empty($newRow["Photo"])) {
            $file_extension = substr(strtolower(strrchr($newRow["Photo"], ".")), 1);
            $user_name = $newRow["Username"];
            $user_name = str_replace("-", "_", $user_name);
            $user_name = str_replace(":", "_", $user_name);
            $user_name = str_replace(" ", "_", $user_name);
            $next_id = ExecuteScalar("SELECT COALESCE(MAX(`UserID`),0) +1 CNT FROM `users`") ;
            $newRow["Photo"] = $next_id . "_" . $user_name . "." . $file_extension;
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
        if (!empty($newRow["Photo"]) && $oldRow["Photo"] != $newRow["Photo"]) {
            $file_extension = substr(strtolower(strrchr($newRow["Photo"], ".")), 1);
            $user_name = $newRow["Username"];
            $user_name = str_replace("-", "_", $user_name);
            $user_name = str_replace(":", "_", $user_name);
            $user_name = str_replace(" ", "_", $user_name);
            $newRow["Photo"] = $newRow["UserID"] . "_" . $user_name . "." . $file_extension;
        }
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
