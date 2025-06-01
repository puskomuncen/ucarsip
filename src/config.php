<?php

/**
 * PHPMaker configuration file
 */

namespace PHPMaker2025\ucarsip;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Notifier\Channel\EmailChannel;
use Symfony\Component\Notifier\Channel\SmsChannel;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Dflydev\FigCookies\SetCookies;
use PHPThumb\GD;

/**
 * Locale settings
 * Note: If you want to use custom settings, customize the locale files.
*/
$DECIMAL_SEPARATOR = ".";
$GROUPING_SEPARATOR = ",";
$CURRENCY_CODE = "USD"; // => $
$CURRENCY_SYMBOL = "$";
$CURRENCY_FORMAT = "¤#,##0.00";
$NUMBER_FORMAT = "#,##0.###";
$PERCENT_SYMBOL = "%";
$PERCENT_FORMAT = "#,##0%";
$DATE_SEPARATOR = "/";
$TIME_SEPARATOR = ":";
$DATE_FORMAT = "y/MM/dd";
$TIME_FORMAT = "HH:mm";
$TIME_ZONE = "UTC";

/**
 * Global variables
 */
$LANGUAGES = ["en-US"];
$Conn = null; // Primary connection
$Page = null; // Page
$Table = null; // Main table
$Grid = null; // Grid page object
$Language = null; // Language
$Security = null; // Security
$Title = null; // Title
$DownloadFileName = ""; // Download file name
$NullValue = null; // Null

// Current language
$CurrentLanguage = "";
$CurrentLocale = ""; // Alias of $CurrentLanguage

// Export
$ExportType = "";
$ExportId = null; // Used by export API

// Skip header/footer
$SkipHeaderFooter = false;
$OldSkipHeaderFooter = $SkipHeaderFooter;

// Temp image names
$TempImages = [];

// API
$IsApi = null;

// HTTP context
$Request = null;
$Response = null;

// CSRF
$TokenName = null;
$TokenNameKey = null;
$TokenValue = null;
$TokenValueKey = null;

// Route values
$RouteValues = null;

// Captcha
$Captcha = null;

// Dashboard report checking
$DashboardReport = null;

// Drilldown panel
$DrillDownInPanel = false;

// Chart
$Chart = null;

// Client variables
$ClientVariables = [];

// Error
$Error = null;

// Rendering View
$RenderingView = false;

// Event dispatcher
$EventDispatcher = new EventDispatcher();

// Custom API actions
$API_ACTIONS = [];

// SetCookies
$SetCookies = new SetCookies();

// User level
require_once __DIR__ . "/userlevelsettings.php";

// For use with composer scripts
global $CONFIG, $ConfigData;

/**
 * Config
 *
 * @var Dflydev\DotAccessData\Data
 */
$ConfigData = null;

/**
 * Config
 */
$CONFIG = [
    "EXIT_IMPERSONATION_TEMPLATE" => '<div><a title="%s" class="exit-user" data-ew-action="submit" data-action="switchuser" data-method="R" data-select="S" data-data="{&quot;switchuser&quot;:&quot;_exit&quot;}">%s</a></div>',

    // Environment
    "ENVIRONMENT" => "development",

    // Container
    "COMPILE_CONTAINER" => false,

    // Use route cache
    "USE_ROUTE_CACHE" => false,
    "CACHE_FOLDER" => "log/cache", // Cache folder
    "ROUTE_ATTRIBUTES_FILE" => "RouteAttributes.php", // Route attributes file under CACHE_FOLDER
    "ROUTE_CACHE_FILE" => "RouteCache.php", // Route cache file under CACHE_FOLDER
    "API_ROUTE_ATTRIBUTES_FILE" => "ApiRouteAttributes.php", // API Route attributes file under CACHE_FOLDER
    "API_ROUTE_CACHE_FILE" => "ApiRouteCache.php", // API Route cache file under CACHE_FOLDER

    // General
    "UNFORMAT_YEAR" => 50, // Unformat year
    "PROJECT_STYLESHEET_FILENAME" => "css/ucarsip.css", // Project stylesheet file name
    "USE_COMPRESSED_STYLESHEET" => true, // Compressed stylesheet
    "FONT_AWESOME_STYLESHEET" => "plugins/fontawesome-free/css/all.min.css", // Font Awesome Free stylesheet
    "EXPORT_TABLE_CELL_STYLES" => ["border" => "1px solid #dddddd", "padding" => "5px"], // Export table cell CSS styles, use inline style for Gmail
    "HIGHLIGHT_COMPARE" => true, // Highlight compare mode, true(case-insensitive)|false(case-sensitive)
    "RELATED_PROJECT_ID" => "", // Related Project ID (GUID)
    "COMPOSITE_KEY_SEPARATOR" => ',', // Composite key separator
    "CACHE" => false, // Cache
    "LAZY_LOAD" => true, // Lazy loading of images
    "BODY_CLASS" => "hold-transition layout-fixed layout-navbar-fixed sidebar-mini", // CSS class(es) for <body> tag
    "BODY_STYLE" => "", // CSS style for <body> tag
    "SIDEBAR_CLASS" => "main-sidebar sidebar-dark-navy elevation-4", // CSS class(es) for sidebar
    "NAVBAR_CLASS" => "main-header navbar navbar-expand navbar-navy navbar-dark border-bottom-0", // CSS class(es) for navbar
    "CLASS_PREFIX" => "_", // Prefix for invalid CSS class names
    "USE_JAVASCRIPT_MESSAGE" => true, // Use JavaScript message (toast)
	"MS_USE_MESSAGE_BOX_INSTEAD_OF_TOAST" => false, // Use Alertify message box, make sure USE_JAVASCRIPT_MESSAGE always set to true!
    "REDIRECT_STATUS_CODE" => 302, // Redirect status code
    "ENCRYPTION_KEY" => '', // Encryption key

    /**
     * AES encryption (NOT for php-encryption and JWT)
     * Supported values: 'aes-128-cbc', 'aes-256-cbc', 'aes-128-gcm' or 'aes-256-gcm'
     */
    "AES_ENCRYPTION_CIPHER" => "aes-256-cbc",
    "AES_ENCRYPTION_KEY" => "9vltFKz97bgUHSbMzfgWGfyuvvnJqqDW2j4sSg4Zl8M=", // AES encryption key (base64 encoded)

    // Required PHP extesions
    "PHP_EXTENSIONS" => [
        "curl" => "curl",
        "fileinfo" => "fileinfo",
        "intl" => "intl",
        "hash" => "hash",
        "mbstring" => "mbstring",
        "openssl" => "openssl",
        "gd" => "image",
        "xml" => "xml",
        "libxml" => "libxml",
    ],

    // Check CSRF token
    "CSRF_PROTECTION" => true,

    // Remove XSS
    "REMOVE_XSS" => true,

    // Local file system root
    "LOCAL_FILESYSTEM_ROOT" => ".",

    // Local file system public URL
    "LOCAL_FILESYSTEM_PUBLIC_URL" => null,

    // Model path
    "MODEL_PATH" => "models/", // With trailing delimiter

    // View path
    "VIEW_PATH" => "views/", // With trailing delimiter

    // Controler path
    "CONTROLLER_PATH" => "controllers/", // With trailing delimiter

    // Font path
    "FONT_PATH" => __DIR__ . "/../font", // No trailing delimiter

    // External JavaScripts
    "JAVASCRIPT_FILES" => [],

    // External StyleSheets
    "STYLESHEET_FILES" => [],

    // Authentication configuration for Google/Facebook/Saml
    "AUTH_CONFIG" => [
        "providers" => [
            "Google" => [
                "enabled" => false,
                "keys" => [
                    "id" => '',
                    "secret" => ''
                ],
                "userIdentifier" => "email",
                "color" => "danger"
            ],
            "Facebook" => [
                "enabled" => false,
                "keys" => [
                    "id" => '',
                    "secret" => ''
                ],
                "userIdentifier" => "email",
                "color" => "primary"
            ],
            "Microsoft" => [
                "enabled" => false,
                "adapter" => MicrosoftEntraID::class,
                "keys" => [
                    "id" => '',
                    "secret" => '' // Note: Client secret value, not client secret ID
                ],
                "userIdentifier" => "email",
                "color" => "orange"
            ],
            "Aws" => [
                "enabled" => false,
                "adapter" => Cognito::class,
                "keys" => [
                    "id" => '',
                    "secret" => ''
                ],
                "domain" => "", // e.g. https://auth.company.com/, https://<domain>.auth.<region>.amazoncognito.com/
                "color" => "orange"
            ],
            "Saml" => [
                "enabled" => false,
                "adapter" => Saml2::class,
                "idpMetadata" => '', // IdP metadata
                "entityId" => '', // SP entity ID
                "certificate" => '', // SP X.509 certificate
                "privateKey" => '', // SP private key
                "identifyingAttribute" => null,
                "userIdentifier" => "email",
                "color" => "success"
            ]
        ],
        "curl_options" => null
    ],

    /**
     * Database time zone
     * Difference to Greenwich time (GMT) with colon between hours and minutes, e.g. +02:00
     */
    "DB_TIME_ZONE" => "",

    /**
     * MySQL charset (for SET NAMES statement, not used by default)
     * Note: Read https://dev.mysql.com/doc/refman/8.0/en/charset-connection.html
     * before using this setting.
     */
    "MYSQL_CHARSET" => "utf8mb4",

    /**
     * PostgreSQL charset (for SET NAMES statement, not used by default)
     * Note: Read https://www.postgresql.org/docs/current/static/multibyte.html
     * before using this setting.
     */
    "POSTGRESQL_CHARSET" => "UTF8",

    /**
     * Oracle charset
     */
    "ORACLE_CHARSET" => "AL32UTF8",

    // Session timeout time
    "SESSION_TIMEOUT" => 10, // Session timeout time (minutes)

    // Session keep alive interval
    "SESSION_KEEP_ALIVE_INTERVAL" => 0, // Session keep alive interval (seconds)
    "SESSION_TIMEOUT_COUNTDOWN" => 60, // Session timeout count down interval (seconds)

    // Language settings
    "LANGUAGES_FILE" => "languages.xml",
    "LANGUAGE_FOLDER" => __DIR__ . "/../lang/",
    "DEFAULT_LANGUAGE_ID" => "en-US",
    "LOCALE_FOLDER" => __DIR__ . "/../locale/",
    "USE_TRANSACTION" => true,

    // Data to be passed to Custom Template
    "CUSTOM_TEMPLATE_DATATYPES" => [
        DataType::NUMBER,
        DataType::DATE,
        DataType::STRING,
        DataType::BOOLEAN,
        DataType::TIME
    ],
    "DATA_STRING_MAX_LENGTH" => 1024,

    // Table parameters
    "TABLE_REC_PER_PAGE" => "recperpage", // Records per page
    "TABLE_PAGER_TABLE_NAME" => "pagertable", // Paging table name for detail grid
    "TABLE_START_REC" => "start", // Start record
    "TABLE_PAGE_NUMBER" => "page", // Page number
    "TABLE_BASIC_SEARCH" => "search", // Basic search keyword
    "TABLE_BASIC_SEARCH_TYPE" => "searchtype", // Basic search type
    "TABLE_ADVANCED_SEARCH" => "advsrch", // Advanced search
    "TABLE_SEARCH_WHERE" => "searchwhere", // Search where clause
    "TABLE_WHERE" => "where", // Table where
    "TABLE_ORDER_BY" => "orderby", // Table order by
    "TABLE_ORDER_BY_LIST" => "orderbylist", // Table order by (list page)
    "TABLE_RULES" => "rules", // Table rules (QueryBuilder)
    "DASHBOARD_FILTER" => "dashboardfilter", // Table filter for dashboard search
    "TABLE_SORT" => "sort", // Table sort
    "TABLE_KEY" => "key", // Table key
    "TABLE_SHOW_MASTER" => "showmaster", // Table show master
    "TABLE_MASTER" => "master", // Table show master (alternate key)
    "TABLE_SHOW_DETAIL" => "showdetail", // Table show detail
    "TABLE_MASTER_TABLE" => "mastertable", // Master table
    "TABLE_DETAIL_TABLE" => "detailtable", // Detail table
    "TABLE_RETURN_URL" => "return", // Return URL
    "TABLE_EXPORT_RETURN_URL" => "exportreturn", // Export return URL
    "TABLE_GRID_ADD_ROW_COUNT" => "gridaddcnt", // Grid add row count

    // Page layout
    "PAGE_LAYOUT" => "layout", // Page layout (string|false)
    "PAGE_LAYOUTS" => ["table", "cards"], // Supported page layouts

    // Page dashboard
    "PAGE_DASHBOARD" => "dashboard", // Page is dashboard (string|false)

    // View (for PhpRenderer)
    "VIEW" => "view",

    // Log user ID or user name
    "LOG_USER_ID" => true, // Write to database

    // Audit Trail
    "AUDIT_TRAIL_TO_DATABASE" => false, // Write to database
    "AUDIT_TRAIL_DBID" => "DB", // DB ID
    "AUDIT_TRAIL_TABLE_NAME" => "", // Table name
    "AUDIT_TRAIL_TABLE_VAR" => "", // Table var
    "AUDIT_TRAIL_FIELD_NAME_DATETIME" => "", // DateTime field name
    "AUDIT_TRAIL_FIELD_NAME_SCRIPT" => "", // Script field name
    "AUDIT_TRAIL_FIELD_NAME_USER" => "", // User field name
    "AUDIT_TRAIL_FIELD_NAME_ACTION" => "", // Action field name
    "AUDIT_TRAIL_FIELD_NAME_TABLE" => "", // Table field name
    "AUDIT_TRAIL_FIELD_NAME_FIELD" => "", // Field field name
    "AUDIT_TRAIL_FIELD_NAME_KEYVALUE" => "", // Key Value field name
    "AUDIT_TRAIL_FIELD_NAME_OLDVALUE" => "", // Old Value field name
    "AUDIT_TRAIL_FIELD_NAME_NEWVALUE" => "", // New Value field name

    // Export Log
    "EXPORT_PATH" => "export-cb54660f-9afa-48fd-b6bb-4648712a04d7", // Export folder
    "EXPORT_LOG_DBID" => "DB", // DB ID
    "EXPORT_LOG_TABLE_NAME" => "", // Table name
    "EXPORT_LOG_TABLE_VAR" => "", // Table var
    "EXPORT_LOG_FIELD_NAME_FILE_ID" => "undefined", // File id (GUID) field name
    "EXPORT_LOG_FIELD_NAME_DATETIME" => "undefined", // DateTime field name
    "EXPORT_LOG_FIELD_NAME_DATETIME_ALIAS" => "datetime", // DateTime field name Alias
    "EXPORT_LOG_FIELD_NAME_USER" => "undefined", // User field name
    "EXPORT_LOG_FIELD_NAME_EXPORT_TYPE" => "undefined", // Export Type field name
    "EXPORT_LOG_FIELD_NAME_EXPORT_TYPE_ALIAS" => "type", // Export Type field name Alias
    "EXPORT_LOG_FIELD_NAME_TABLE" => "undefined", // Table field name
    "EXPORT_LOG_FIELD_NAME_TABLE_ALIAS" => "tablename", // Table field name Alias
    "EXPORT_LOG_FIELD_NAME_KEY_VALUE" => "undefined", // Key Value field name
    "EXPORT_LOG_FIELD_NAME_FILENAME" => "undefined", // File name field name
    "EXPORT_LOG_FIELD_NAME_FILENAME_ALIAS" => "filename", // File name field name Alias
    "EXPORT_LOG_FIELD_NAME_REQUEST" => "undefined", // Request field name
    "EXPORT_FILES_EXPIRY_TIME" => 0, // Files expiry time (minutes)
    "EXPORT_LOG_SEARCH" => "search", // Export log search
    "EXPORT_LOG_LIMIT" => "limit", // Search by limit
    "EXPORT_LOG_ARCHIVE_PREFIX" => "export", // Export log archive prefix
    "LOG_ALL_EXPORT_REQUESTS" => false, // Log all export requests

    // Security
    "ENCRYPTION_ENABLED" => false, // Encryption enabled
    "ENCRYPT_USER_NAME_AND_PASSWORD" => false, // Encrypt user name / password
    "ADMIN_USER_NAME" => "admin", // Administrator user name
    "PASSWORD_HASH" => true, // Use PHP password hashing functions
    "ENCRYPTED_PASSWORD" => true, // Use encrypted password
    "PHPASS_ITERATION_COUNT_LOG2" => [10, 8], // For PasswordHash (deprecated)
    "CASE_SENSITIVE_PASSWORD" => true, // Case-sensitive password (deprecated)
    "MIGRATE_PASSWORD" => false, // Migrate password
    "USE_MODAL_LOGIN" => false, // Use modal login
    "USE_MODAL_REGISTER" => false, // Use modal register
    "USE_MODAL_CHANGE_PASSWORD" => false, // Use modal change password
    "USE_MODAL_RESET_PASSWORD" => false, // Use modal reset password
    "RESET_PASSWORD_TIME_LIMIT" => 60, // Reset password time limit (minutes)
    "WINDOWS_USER_KEY" => "AUTH_USER", // Windows user key (REMOTE_USER or LOGON_USER or AUTH_USER)

    /**
     * Dynamic User Level settings
     */

    // User level definition table/field names
    "USER_LEVEL_DBID" => "DB",
    "USER_LEVEL_TABLE" => "userlevels",
    "USER_LEVEL_ID_FIELD" => "ID",
    "USER_LEVEL_NAME_FIELD" => "`Name`",
    "USER_LEVEL_HIERARCHY_FIELD" => "`Hierarchy`",

    // User Level privileges table/field names
    "USER_LEVEL_PRIV_DBID" => "DB",
    "USER_LEVEL_PRIV_TABLE" => "userlevelpermissions",
    "USER_LEVEL_PRIV_TABLE_NAME_FIELD" => "`TableName`",
    "USER_LEVEL_PRIV_TABLE_NAME_FIELD_2" => "TableName",
    "USER_LEVEL_PRIV_TABLE_NAME_FIELD_SIZE" => 191, // Max key length 767/4 = 191 bytes
    "USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD" => "`UserLevelID`",
    "USER_LEVEL_PRIV_PRIV_FIELD" => "`Permission`",

    // Default User ID permission
    "DEFAULT_USER_ID_PERMISSION" => Allow::LIST->value | Allow::VIEW->value | Allow::SEARCH->value | Allow::LOOKUP->value,

    // User table/field names
    "USER_TABLE_NAME" => "users",
    "USER_TABLE_ENTITY_CLASS" => Entity\User::class,
    "LOGIN_USERNAME_FIELD_NAME" => "Username",
    "LOGIN_USERNAME_PROPERTY_NAME" => "Username",
    "LOGIN_PASSWORD_FIELD_NAME" => "Password",
    "USER_ID_FIELD_NAME" => "UserID",
    "PARENT_USER_ID_FIELD_NAME" => "ReportsTo",
    "USER_LEVEL_FIELD_NAME" => "UserLevel",
    "USER_PROFILE_FIELD_NAME" => "Profile",
    "REGISTER_ACTIVATE" => false,
    "REGISTER_AUTO_LOGIN" => false,
    "USER_ACTIVATED_FIELD_NAME" => "Activated",
    "USER_ACTIVATED_FIELD_VALUE" => "1",
    "USER_EMAIL_FIELD_NAME" => "Email",
    "USER_EMAIL_PROPERTY_NAME" => "Email",
    "USER_PHONE_FIELD_NAME" => "HomePhone",
    "USER_IMAGE_FIELD_NAME" => "Photo",
    "USER_IMAGE_SIZE" => 40,
    "USER_IMAGE_CROP" => true,
    "LOGIN_LINK_LIFETIME" => 5 * 60,

    // User table filters
    "USER_TABLE_DBID" => "DB",
    "USER_TABLE" => "users",
    "SEARCH_FILTER_OPTION" => "Client",

    // Email
    "SENDER_EMAIL" => "masino.sinaga@gmail.com", // Sender email address
    "RECIPIENT_EMAIL" => "masino.sinaga@gmail.com", // Recipient email address
    "MAX_EMAIL_RECIPIENT" => 3,
    "MAX_EMAIL_SENT_COUNT" => 3,
    "EXPORT_EMAIL_COUNTER" => SESSION_STATUS . "_EmailCounter",
    "CID_SUFFIX" => null,

    // SMS region code
    // https://github.com/giggsey/libphonenumber-for-php/blob/master/docs/PhoneNumberUtil.md
    // - null => Use region code from locale (i.e. en-US => US)
    // - false => Skip formatting with PhoneNumberUtil
    "SMS_REGION_CODE" => false,

    // Email/SMS Templates
    "EMAIL_CHANGE_PASSWORD_TEMPLATE" => "ChangePassword.php",
    "EMAIL_NOTIFY_TEMPLATE" => "Notify.php",
    "EMAIL_REGISTER_TEMPLATE" => "Register.php",
    "EMAIL_RESET_PASSWORD_TEMPLATE" => "ResetPassword.php",
    "EMAIL_ONE_TIME_PASSWORD_TEMPLATE" => "OneTimePasswordEmail.php",
    "EMAIL_LOGIN_LINK_TEMPLATE" => "LoginLink.php",
    "SMS_ONE_TIME_PASSWORD_TEMPLATE" => "OneTimePasswordSms.php",

    // File upload
    "UPLOAD_TEMP_PATH" => "", // Upload temp path (relative to Local file system root)
    "UPLOAD_DEST_PATH" => "files/", // Upload destination path (relative to Local file system root)
    "UPLOAD_TEMP_FOLDER_PREFIX" => "temp__", // Upload temp folders prefix
    "CURRENT_UPLOAD_TEMP_FOLDER_TIME_LIMIT" => 5, // Current upload temp folder time limit (minutes)
    "UPLOAD_TEMP_FOLDER_TIME_LIMIT" => 1440, // Upload temp folder time limit (minutes)
    "UPLOAD_THUMBNAIL_FOLDER" => "thumbnail", // Temporary thumbnail folder
    "UPLOAD_THUMBNAIL_WIDTH" => 200, // Temporary thumbnail max width
    "UPLOAD_THUMBNAIL_HEIGHT" => 0, // Temporary thumbnail max height
    "UPLOAD_ALLOWED_FILE_EXT" => "gif,jpg,jpeg,bmp,png,doc,docx,xls,xlsx,pdf,zip", // Allowed file extensions
    "IMAGE_ALLOWED_FILE_EXT" => "gif,jpe,jpeg,jpg,png,bmp", // Allowed file extensions for images
    "DOWNLOAD_ALLOWED_FILE_EXT" => "csv,pdf,xls,doc,xlsx,docx", // Allowed file extensions for download (non-image)
    "ENCRYPT_FILE_PATH" => true, // Encrypt file path
    "MAX_FILE_SIZE" => 2000000, // Max file size
    "MAX_FILE_COUNT" => null, // Max file count, null => no limit
    "IMAGE_CROPPER" => false, // Upload cropper
    "THUMBNAIL_DEFAULT_WIDTH" => 100, // Thumbnail default width
    "THUMBNAIL_DEFAULT_HEIGHT" => 0, // Thumbnail default height
    "UPLOADED_FILE_MODE" => 0666, // Uploaded file mode
    "USER_UPLOAD_TEMP_PATH" => "", // User upload temp path (relative to app root) e.g. "tmp/"
    "UPLOAD_CONVERT_ACCENTED_CHARS" => false, // Convert accented chars in upload file name
    "USE_COLORBOX" => true, // Use Colorbox
    "MULTIPLE_UPLOAD_SEPARATOR" => ',', // Multiple upload separator
    "DELETE_UPLOADED_FILES" => false, // Delete uploaded file on deleting record
    "FILE_NOT_FOUND" => "/9j/4AAQSkZJRgABAQEASABIAAD/7QAuUGhvdG9zaG9wIDMuMAA4QklNBAQAAAAAABIcAigADEZpbGVOb3RGb3VuZAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAP/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFAEBAAAAAAAAAAAAAAAAAAAAAP/EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhEDEQA/AKAA/9k=", // 1x1 jpeg with IPTC data "2#040"="FileNotFound"
    "CREATE_UPLOAD_FILE_ON_COPY" => true, // Create upload file on copy

    // Save file options
    "SAVE_FILE_OPTIONS" => LOCK_EX,

    // Form hidden tag names (Note: DO NOT modify prefix "k_")
    "FORM_KEY_COUNT_NAME" => "key_count",
    "FORM_ROW_ACTION_NAME" => "k_action",
    "FORM_BLANK_ROW_NAME" => "k_blankrow",
    "FORM_OLD_KEY_NAME" => "k_oldkey",
    "FORM_ROW_HASH_NAME" => "k_hash",
    "FORM_HIDDEN_INPUT_NAME_PATTERN" => '/^[a-z]_/', // For inserting row index

    // Table actions
    "LIST_ACTION" => "list", // Table list action
    "VIEW_ACTION" => "view", // Table view action
    "ADD_ACTION" => "add", // Table add action
    "ADDOPT_ACTION" => "addopt", // Table addopt action
    "EDIT_ACTION" => "edit", // Table edit action
    "UPDATE_ACTION" => "update", // Table update action
    "DELETE_ACTION" => "delete", // Table delete action
    "SEARCH_ACTION" => "search", // Table search action
    "QUERY_ACTION" => "query", // Table search action
    "PREVIEW_ACTION" => "preview", // Table preview action
    "CUSTOM_REPORT_ACTION" => "custom", // Custom report action
    "SUMMARY_REPORT_ACTION" => "summary", // Summary report action
    "CROSSTAB_REPORT_ACTION" => "crosstab", // Crosstab report action
    "DASHBOARD_REPORT_ACTION" => "dashboard", // Dashboard report action
    "CALENDAR_REPORT_ACTION" => "calendar", // Calendar report action

    // Swagger
    "SWAGGER_ACTION" => "swagger/swagger", // API swagger action
    "API_VERSION" => "v1", // API version for swagger

    // API
    "API_URL" => "api/", // API URL
    "API_ACTION_NAME" => "action", // API action name
    "API_OBJECT_NAME" => "table", // API object name
    "API_EXPORT_NAME" => "export", // API export name
    "API_EXPORT_SAVE" => "save", // API export save file
    "API_EXPORT_OUTPUT" => "output", // API export output file as inline/attachment
    "API_EXPORT_DOWNLOAD" => "download", // API export download file => disposition=attachment
    "API_EXPORT_FILE_NAME" => "filename", // API export file name
    "API_EXPORT_CONTENT_TYPE" => "contenttype", // API export content type
    "API_EXPORT_USE_CHARSET" => "usecharset", // API export use charset in content type header
    "API_EXPORT_USE_BOM" => "usebom", // API export use BOM
    "API_EXPORT_CACHE_CONTROL" => "cachecontrol", // API export cache control header
    "API_EXPORT_DISPOSITION" => "disposition", // API export disposition (inline/attachment)
    "API_FIELD_NAME" => "field", // API field name
    "API_KEY_NAME" => "key", // API key name
    "API_FILE_TOKEN_NAME" => "filetoken", // API upload file token name
    "API_LOGIN_USERNAME" => "username", // API login user name
    "API_LOGIN_PASSWORD" => "password", // API login password
    "API_LOGIN_EXPIRE" => "expire", // API login expire (hours)
    "API_LOGIN_PERMISSION" => "permission", // API login expire permission (hours)
    "API_LOOKUP_PAGE" => "page", // API lookup page name
    "API_USERLEVEL_NAME" => "userlevel", // API userlevel name
    "API_PUSH_NOTIFICATION_SUBSCRIBE" => "subscribe", // API push notification subscribe
    "API_PUSH_NOTIFICATION_SEND" => "send", // API push notification send
    "API_PUSH_NOTIFICATION_DELETE" => "delete", // API push notification delete
    "API_2FA_CONFIG" => "config", // API two factor authentication configuration
    "API_2FA_ENABLE" => "enable", // API two factor authentication enable
    "API_2FA_DISABLE" => "disable", // API two factor authentication disable
    "API_2FA_SHOW" => "show", // API two factor authentication show
    "API_2FA_VERIFY" => "verify", // API two factor authentication verify
    "API_2FA_RESET" => "reset", // API two factor authentication reset
    "API_2FA_BACKUP_CODES" => "codes", // API two factor authentication backup codes
    "API_2FA_NEW_BACKUP_CODES" => "newcodes", // API two factor authentication new backup codes
    "API_2FA_SEND_OTP" => "otp", // API two factor authentication send one time password

    // API actions
    "API_LIST_ACTION" => "list", // API list action
    "API_VIEW_ACTION" => "view", // API view action
    "API_ADD_ACTION" => "add", // API add action
    "API_REGISTER_ACTION" => "register", // API register action
    "API_EDIT_ACTION" => "edit", // API edit action
    "API_DELETE_ACTION" => "delete", // API delete action
    "API_LOGIN_ACTION" => "login", // API login action
    "API_FILE_ACTION" => "file", // API file action
    "API_UPLOAD_ACTION" => "upload", // API upload action
    "API_JQUERY_UPLOAD_ACTION" => "jupload", // API jQuery upload action
    "API_SESSION_ACTION" => "session", // API get session action
    "API_LOOKUP_ACTION" => "lookup", // API lookup action
    "API_IMPORT_ACTION" => "import", // API import action
    "API_EXPORT_ACTION" => "export", // API export action
    "API_EXPORT_CHART_ACTION" => "chart", // API export chart action
    "API_PERMISSIONS_ACTION" => "permissions", // API permissions action
    "API_PUSH_NOTIFICATION_ACTION" => "push", // API push notification action
    "API_2FA_ACTION" => "twofa", // API two factor authentication action
    "API_METADATA_ACTION" => "metadata", // API metadata action (SP)
    "API_CHAT_ACTION" => "chat", // API chat action

    // List page inline/grid/modal settings
    "USE_AJAX_ACTIONS" => false,

    // Send push notification time limit
    "SEND_PUSH_NOTIFICATION_TIME_LIMIT" => 300,
    "PUSH_ANONYMOUS" => false,

    // Use two factor Authentication
    "USE_TWO_FACTOR_AUTHENTICATION" => false,
    "FORCE_TWO_FACTOR_AUTHENTICATION" => false,
    "TWO_FACTOR_AUTHENTICATION_TYPES" => [],
    "TWO_FACTOR_AUTHENTICATION_CLASSES" => [ // Note: These classes must implements TwoFactorAuthenticationInterface and must be defined in definitions.php for container
        EmailTwoFactorAuthentication::class,
        SmsTwoFactorAuthentication::class,
        PragmaRxTwoFactorAuthentication::class
    ],
    "TWO_FACTOR_AUTHENTICATION_CLASS" => PragmaRxTwoFactorAuthentication::class, // Default
    "TWO_FACTOR_AUTHENTICATION_ISSUER" => PROJECT_NAME,
    "TWO_FACTOR_AUTHENTICATION_DISCREPANCY" => 1,
    "TWO_FACTOR_AUTHENTICATION_QRCODE_SIZE" => 200,
    "TWO_FACTOR_AUTHENTICATION_PASS_CODE_LENGTH" => 6,
    "TWO_FACTOR_AUTHENTICATION_BACKUP_CODE_LENGTH" => 8,
    "TWO_FACTOR_AUTHENTICATION_BACKUP_CODE_COUNT" => 10,
    "OTP_ONLY" => false,
    "ADMIN_EMAIL" => "",
    "ADMIN_PHONE" => "",

    // Image resize
    "THUMBNAIL_CLASS" => GD::class,
    "RESIZE_OPTIONS" => ["keepAspectRatio" => false, "resizeUp" => !true, "jpegQuality" => 100],

    // Logging and audit trail
    "LOG_PATH" => "log/", // Logging and audit trail path (relative to app root)

    // Import records
    "IMPORT_MAX_EXECUTION_TIME" => 300, // Import max execution time
    "IMPORT_FILE_ALLOWED_EXTENSIONS" => "csv,xls,xlsx", // Import file allowed extensions
    "IMPORT_INSERT_ONLY" => true, // Import by insert only
    "IMPORT_USE_TRANSACTION" => true, // Import use transaction
    "IMPORT_MAX_FAILURES" => 1, // Import maximum number of failures

    // Export records
    "EXPORT_ALL" => true, // Export all records
    "EXPORT_ALL_TIME_LIMIT" => 120, // Export all records time limit
    "EXPORT_ORIGINAL_VALUE" => false,
    "EXPORT_FIELD_CAPTION" => false, // True to export field caption
    "EXPORT_FIELD_IMAGE" => true, // True to export field image
    "EXPORT_CSS_STYLES" => true, // True to export CSS styles
    "EXPORT_MASTER_RECORD" => true, // True to export master record
    "EXPORT_MASTER_RECORD_FOR_CSV" => false, // True to export master record for CSV
    "EXPORT_DETAIL_RECORDS" => true, // True to export detail records
    "EXPORT_DETAIL_RECORDS_FOR_CSV" => false, // True to export detail records for CSV
    "EXPORT_CLASSES" => [
        "email" => ExportEmail::class,
        "html" => ExportHtml::class,
		"print" => ExportPrint::class, // added by Masino Sinaga, September 10, 2023
        "word" => ExportWord::class,
        "excel" => ExportExcel::class,
        "pdf" => ExportPdf::class,
        "csv" => ExportCsv::class,
        "xml" => ExportXml::class,
        "json" => ExportJson::class
    ],
    "REPORT_EXPORT_CLASSES" => [
        "email" => ExportEmail::class,
        "html" => ExportHtml::class,
        "word" => ExportWord::class,
        "excel" => ExportExcel::class,
        "pdf" => ExportPdf::class
    ],

    // Full URL protocols ("http" or "https")
    "FULL_URL_PROTOCOLS" => [
        "href" => "", // Field hyperlink
        "upload" => "", // Upload page
        "resetpwd" => "", // Reset password
        "activate" => "", // Register page activate link
        "auth" => "", // OAuth base URL
        "export" => "" // Export (for reports)
    ],

    // Boolean HTML attributes
    "BOOLEAN_HTML_ATTRIBUTES" => [
        "allowfullscreen",
        "allowpaymentrequest",
        "async",
        "autofocus",
        "autoplay",
        "checked",
        "controls",
        "default",
        "defer",
        "disabled",
        "formnovalidate",
        "hidden",
        "ismap",
        "itemscope",
        "loop",
        "multiple",
        "muted",
        "nomodule",
        "novalidate",
        "open",
        "readonly",
        "required",
        "reversed",
        "selected",
        "typemustmatch"
    ],

    // Use token in URL (reserved, not used, do NOT change!)
    "USE_TOKEN_IN_URL" => false,

    // Use ILIKE for PostgreSQL
    "USE_ILIKE_FOR_POSTGRESQL" => true,

    // Use collation for MySQL
    "LIKE_COLLATION_FOR_MYSQL" => "",

    // Use collation for MsSQL
    "LIKE_COLLATION_FOR_MSSQL" => "",

    // Null / Not Null / Init / Empty / all values
    "NULL_VALUE" => "##null##",
    "NOT_NULL_VALUE" => "##notnull##",
    "EMPTY_VALUE" => "##empty##",
    "ALL_VALUE" => "##all##",

    /**
     * Search multi value option
     * 1 - no multi value
     * 2 - AND all multi values
     * 3 - OR all multi values
    */
    "SEARCH_MULTI_VALUE_OPTION" => 3,

    // Advanced search
    "SEARCH_OPTION" => "AUTO",

    // Quick search
    "BASIC_SEARCH_IGNORE_PATTERN" => "/[\?,\.\^\*\(\)\[\]\\\"]/", // Ignore special characters
    "BASIC_SEARCH_ANY_FIELDS" => false, // Search "All keywords" in any selected fields

    // Sort options
    "SORT_OPTION" => "Tristate", // Sort option (toggle/tristate)

    // Validate options
    "CLIENT_VALIDATE" => true,
    "SERVER_VALIDATE" => false,
    "INVALID_USERNAME_CHARACTERS" => "<>\"'&",
    "INVALID_PASSWORD_CHARACTERS" => "<>\"'&",
    "URL_PATTERN" => '~^
            (https?)://                                 # protocol
            (((?:[\_\.\pL\pN-]|%%[0-9A-Fa-f]{2})+:)?((?:[\_\.\pL\pN-]|%%[0-9A-Fa-f]{2})+)@)?  # basic auth
            (
                ([\pL\pN\pS\-\_\.])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                                 # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                    # an IP address
                    |                                                 # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # an IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*          # a path
            (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a query (optional)
            (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?       # a fragment (optional)
        $~ixu', // Based on https://github.com/symfony/validator/blob/5.4/Constraints/UrlValidator.php

    // Blob field byte count for hash value calculation
    "BLOB_FIELD_BYTE_COUNT" => 200,

    // Native select-one
    "USE_NATIVE_SELECT_ONE" => false,

    // Auto suggest max entries
    "AUTO_SUGGEST_MAX_ENTRIES" => 10,

    // Lookup all display fields
    "LOOKUP_ALL_DISPLAY_FIELDS" => false,

    // Use table filter for filter fields
    "USE_TABLE_FILTER_FOR_FILTER_FIELDS" => true,

    // Lookup page size
    "LOOKUP_PAGE_SIZE" => 100,

    // Filter page size
    "FILTER_PAGE_SIZE" => 100,

    // Auto fill original value
    "AUTO_FILL_ORIGINAL_VALUE" => false,

    // Lookup
    "MULTIPLE_OPTION_SEPARATOR" => ',',
    "MYSQL_MULTI_OPTION_REPLACE_STRING" => '&comma;', // Replace string for MYSQL FIND_IN_SET if MULTIPLE_OPTION_SEPARATOR is not ","
    "FILTER_OPTION_SEPARATOR" => '|',
    "USE_LOOKUP_CACHE" => true,
    "LOOKUP_CACHE_COUNT" => 100,
    "LOOKUP_CACHE_PAGE_IDS" => ["list","grid"],

    // Page Title Style
    "PAGE_TITLE_STYLE" => "Breadcrumbs",

    // Responsive table
    "USE_RESPONSIVE_TABLE" => true,
    "RESPONSIVE_TABLE_CLASS" => "table-responsive",

    // Fixed header table
    "FIXED_HEADER_TABLE_CLASS" => "table-head-fixed",
    "USE_FIXED_HEADER_TABLE" => false,
    "FIXED_HEADER_TABLE_HEIGHT" => "mh-400px", // CSS class for fixed header table height

    // Multi column list options position
    "MULTI_COLUMN_LIST_OPTIONS_POSITION" => "bottom-start",

    // RTL
    "RTL_LANGUAGES" => ["ar", "fa", "he", "iw", "ug", "ur"],

    // Multiple selection
    "OPTION_HTML_TEMPLATE" => '<span class="ew-option">{value}</span>', // Note: class="ew-option" must match CSS style in project stylesheet
    "OPTION_SEPARATOR" => ", ",

    // Cookie consent
    "COOKIE_CONSENT_NAME" => "ConsentCookie", // Cookie consent name
    "COOKIE_CONSENT_CLASS" => "text-bg-secondary", // CSS class name for cookie consent
    "COOKIE_CONSENT_BUTTON_CLASS" => "btn btn-dark btn-sm", // CSS class name for cookie consent buttons

    // Cookies
    "COOKIE_PATH" => "/",
    "COOKIE_LIFETIME" => 365 * 24 * 60 * 60,
    "COOKIE_EXPIRY_TIME" => time() + 365 * 24 * 60 * 60,
    "COOKIE_HTTP_ONLY" => true,
    "COOKIE_SECURE" => false,
    "COOKIE_SAMESITE" => "Lax",

    // User profile cookie expire time
    "USER_PROFILE_COOKIE_EXPIRY_TIME" => time() + 365 * 24 * 60 * 60,

    // Activate link lifetime
    "ACTIVATE_LINK_LIFETIME" => 600, // In seconds, 10 minutes by default

    // Mime type
    "DEFAULT_MIME_TYPE" => "application/octet-stream",

    // Auto hide pager
    "AUTO_HIDE_PAGER" => false,
    "AUTO_HIDE_PAGE_SIZE_SELECTOR" => false,

    // Extensions
    "USE_PHPCAPTCHA_FOR_LOGIN" => false,
    "USE_PHPEXCEL" => false,
    "USE_PHPWORD" => false,

    /**
     * Reports
     */

    // Chart
    "CHART_SHOW_BLANK_SERIES" => false, // Show blank series
    "CHART_SHOW_ZERO_IN_STACK_CHART" => false, // Show zero in stack chart
    "CHART_SHOW_MISSING_SERIES_VALUES_AS_ZERO" => true, // Show missing series values as zero
    "CHART_SCALE_BEGIN_WITH_ZERO" => false, // Chart scale begin with zero
    "CHART_SCALE_MINIMUM_VALUE" => 0, // Chart scale minimum value
    "CHART_SCALE_MAXIMUM_VALUE" => 0, // Chart scale maximum value
    "CHART_SHOW_PERCENTAGE" => false, // Show percentage in Pie/Doughnut charts
    "CHART_COLOR_PALETTE" => "", // Color pallette (global)

    // Drill down setting
    "USE_DRILLDOWN_PANEL" => true, // Use popover for drill down

    // Filter
    "SHOW_CURRENT_FILTER" => true, // True to show current filter
    "SHOW_DRILLDOWN_FILTER" => true, // True to show drill down filter

    // Soft delete time aware period
    "SOFT_DELETE_TIME_AWARE_PERIOD" => "now",

    // Table level constants
    "TABLE_GROUP_PER_PAGE" => "recperpage",
    "TABLE_START_GROUP" => "start",
    "TABLE_SORT_CHART" => "sortchart", // Table sort chart

    // Page break (Use old page-break-* for better compatibility)
    "PAGE_BREAK_HTML" => '<div style="page-break-after:always;"></div>',

    // Download PDF file (instead of shown in browser)
    "DOWNLOAD_PDF_FILE" => false,

    // Embed PDF documents
    "EMBED_PDF" => true,

    // Advanced Filters
    "REPORT_ADVANCED_FILTERS" => [
        "PastFuture" => ["Past" => "IsPast", "Future" => "IsFuture"],
        "RelativeDayPeriods" => ["Last30Days" => "IsLast30Days", "Last14Days" => "IsLast14Days", "Last7Days" => "IsLast7Days", "Next7Days" => "IsNext7Days", "Next14Days" => "IsNext14Days", "Next30Days" => "IsNext30Days"],
        "RelativeDays" => ["Yesterday" => "IsYesterday", "Today" => "IsToday", "Tomorrow" => "IsTomorrow"],
        "RelativeWeeks" => ["LastTwoWeeks" => "IsLast2Weeks", "LastWeek" => "IsLastWeek", "ThisWeek" => "IsThisWeek", "NextWeek" => "IsNextWeek", "NextTwoWeeks" => "IsNext2Weeks"],
        "RelativeMonths" => ["LastMonth" => "IsLastMonth", "ThisMonth" => "IsThisMonth", "NextMonth" => "IsNextMonth"],
        "RelativeYears" => ["LastYear" => "IsLastYear", "ThisYear" => "IsThisYear", "NextYear" => "IsNextYear"]
    ],

    // Chart
    "DEFAULT_CHART_RENDERER" => "",

    // Float fields default number format
    "DEFAULT_NUMBER_FORMAT" => "#,##0.##",

    // Pace options
    "PACE_OPTIONS" => [
        "ajax" => [
            "trackMethods" => ["GET", "POST"],
            "ignoreURLs" => ["/session?"]
        ],
        "eventLag" => false, // For Firefox
    ],

    // Date time formats
    "DATE_FORMATS" => [
        4 => "HH:mm",
        5 => "y/MM/dd",
        6 => "MM/dd/y",
        7 => "dd/MM/y",
        9 => "y/MM/dd HH:mm:ss",
        10 => "MM/dd/y HH:mm:ss",
        11 => "dd/MM/y HH:mm:ss",
        109 => "y/MM/dd HH:mm",
        110 => "MM/dd/y HH:mm",
        111 => "dd/MM/y HH:mm",
        12 => "yy/MM/dd",
        13 => "MM/dd/yy",
        14 => "dd/MM/yy",
        15 => "yy/MM/dd HH:mm:ss",
        16 => "MM/dd/yy HH:mm:ss",
        17 => "dd/MM/yy HH:mm:ss",
        115 => "yy/MM/dd HH:mm",
        116 => "MM/dd/yy HH:mm",
        117 => "dd/MM/yy HH:mm",
    ],

    // Database date time formats
    "DB_DATE_FORMATS" => [
        "MYSQL" => [
            "dd" => "%d",
            "d" => "%e",
            "HH" => "%H",
            "H" => "%k",
            "hh" => "%h",
            "h" => "%l",
            "MM" => "%m",
            "M" => "%c",
            "mm" => "%i",
            "m" => "%i",
            "ss" => "%S",
            "s" => "%S",
            "yy" => "%y",
            "y" => "%Y",
            "a" => "%p"
        ],
        "POSTGRESQL" => [
            "dd" => "DD",
            "d" => "FMDD",
            "HH" => "HH24",
            "H" => "FMHH24",
            "hh" => "HH12",
            "h" => "FMHH12",
            "MM" => "MM",
            "M" => "FMMM",
            "mm" => "MI",
            "m" => "FMMI",
            "ss" => "SS",
            "s" => "FMSS",
            "yy" => "YY",
            "y" => "YYYY",
            "a" => "AM"
        ],
        "MSSQL" => [
            "dd" => "dd",
            "d" => "d",
            "HH" => "HH",
            "H" => "H",
            "hh" => "hh",
            "h" => "h",
            "MM" => "MM",
            "M" => "M",
            "mm" => "mm",
            "m" => "m",
            "ss" => "ss",
            "s" => "s",
            "yy" => "yy",
            "y" => "yyyy",
            "a" => "tt"
        ],
        "ORACLE" => [
            "dd" => "DD",
            "d" => "FMDD",
            "HH" => "HH24",
            "H" => "FMHH24",
            "hh" => "HH12",
            "h" => "FMHH12",
            "MM" => "MM",
            "M" => "FMMM",
            "mm" => "MI",
            "m" => "FMMI",
            "ss" => "SS",
            "s" => "FMSS",
            "yy" => "YY",
            "y" => "YYYY",
            "a" => "AM"
        ],
        "SQLITE" => [
            "dd" => "%d",
            "d" => "%d",
            "HH" => "%H",
            "H" => "%H",
            "hh" => "%I",
            "h" => "%I",
            "MM" => "%m",
            "M" => "%m",
            "mm" => "%M",
            "m" => "%M",
            "ss" => "%S",
            "s" => "%S",
            "yy" => "%y",
            "y" => "%Y",
            "a" => "%P"
        ]
    ],

    // Quarter name
    "QUARTER_PATTERN" => "QQQQ",

    // Month name
    "MONTH_PATTERN" => "MMM",

    // Table client side variables
    "TABLE_CLIENT_VARS" => [
        "TableName",
        "tableCaption"
    ],

    // Field client side variables
    "FIELD_CLIENT_VARS" => [
        "Name",
        "caption",
        "Visible",
        "Required",
        "IsInvalid",
        "Raw",
        "clientFormatPattern",
        "clientSearchOperators"
    ],

    // Query builder search operators
    "CLIENT_SEARCH_OPERATORS" => [
        "=" => "equal",
        "<>" => "not_equal",
        "IN" => "in",
        "NOT IN" => "not_in",
        "<" => "less",
        "<=" => "less_or_equal",
        ">" => "greater",
        ">=" => "greater_or_equal",
        "BETWEEN" => "between",
        "NOT BETWEEN" => "not_between",
        "STARTS WITH" => "begins_with",
        "NOT STARTS WITH" => "not_begins_with",
        "LIKE" => "contains",
        "NOT LIKE" => "not_contains",
        "ENDS WITH" => "ends_with",
        "NOT ENDS WITH" => "not_ends_with",
        "IS EMPTY" => "is_empty",
        "IS NOT EMPTY" => "is_not_empty",
        "IS NULL" => "is_null",
        "IS NOT NULL" => "is_not_null"
    ],

    // Query builder search operators settings
    "QUERY_BUILDER_OPERATORS" => [
        "equal" => [ "type" => "equal", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["string", "number", "datetime", "boolean"] ],
        "not_equal" => [ "type" => "not_equal", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["string", "number", "datetime", "boolean"] ],
        "in" => [ "type" => "in", "nb_inputs" => 1, "multiple" => true, "apply_to" => ["string", "number", "datetime"] ],
        "not_in" => [ "type" => "not_in", "nb_inputs" => 1, "multiple" => true, "apply_to" => ["string", "number", "datetime"] ],
        "less" => [ "type" => "less", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["number", "datetime"] ],
        "less_or_equal" => [ "type" => "less_or_equal", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["number", "datetime"] ],
        "greater" => [ "type" => "greater", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["number", "datetime"] ],
        "greater_or_equal" => [ "type" => "greater_or_equal", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["number", "datetime"] ],
        "between" => [ "type" => "between", "nb_inputs" => 2, "multiple" => false, "apply_to" => ["number", "datetime"] ],
        "not_between" => [ "type" => "not_between", "nb_inputs" => 2, "multiple" => false, "apply_to" => ["number", "datetime"] ],
        "begins_with" => [ "type" => "begins_with", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["string"] ],
        "not_begins_with" => [ "type" => "not_begins_with", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["string"] ],
        "contains" => [ "type" => "contains", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["string"] ],
        "not_contains" => [ "type" => "not_contains", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["string"] ],
        "ends_with" => [ "type" => "ends_with", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["string"] ],
        "not_ends_with" => [ "type" => "not_ends_with", "nb_inputs" => 1, "multiple" => false, "apply_to" => ["string"] ],
        "is_empty" => [ "type" => "is_empty", "nb_inputs" => 0, "multiple" => false, "apply_to" => ["string"] ],
        "is_not_empty" => [ "type" => "is_not_empty", "nb_inputs" => 0, "multiple" => false, "apply_to" => ["string"] ],
        "is_null" => [ "type" => "is_null", "nb_inputs" => 0, "multiple" => false, "apply_to" => ["string", "number", "datetime", "boolean"] ],
        "is_not_null" => [ "type" => "is_not_null", "nb_inputs" => 0, "multiple" => false, "apply_to" => ["string", "number", "datetime", "boolean"] ]
    ],

    // Value separator for IN operator
    "IN_OPERATOR_VALUE_SEPARATOR" => "|",

    // Value separator for BETWEEN operator
    "BETWEEN_OPERATOR_VALUE_SEPARATOR" => "|",

    // Value separator for OR operator
    "OR_OPERATOR_VALUE_SEPARATOR" => "||",

    // Intl numbering systems
    "INTL_NUMBERING_SYSTEMS" => [
        "ar" => "arab",
        "ar-001" => "arab",
        "ar-AE" => "arab",
        "ar-BH" => "arab",
        "ar-DJ" => "arab",
        "ar-EG" => "arab",
        "ar-ER" => "arab",
        "ar-IL" => "arab",
        "ar-IQ" => "arab",
        "ar-JO" => "arab",
        "ar-KM" => "arab",
        "ar-KW" => "arab",
        "ar-LB" => "arab",
        "ar-MR" => "arab",
        "ar-OM" => "arab",
        "ar-PS" => "arab",
        "ar-QA" => "arab",
        "ar-SA" => "arab",
        "ar-SD" => "arab",
        "ar-SO" => "arab",
        "ar-SS" => "arab",
        "ar-SY" => "arab",
        "ar-TD" => "arab",
        "ar-YE" => "arab",
        "as" => "beng",
        "as-IN" => "beng",
        "bn" => "beng",
        "bn-BD" => "beng",
        "bn-IN" => "beng",
        "ccp" => "cakm",
        "ccp-BD" => "cakm",
        "ccp-IN" => "cakm",
        "ckb" => "arab",
        "ckb-IQ" => "arab",
        "ckb-IR" => "arab",
        "dz" => "tibt",
        "dz-BT" => "tibt",
        "fa" => "arabext",
        "fa-AF" => "arabext",
        "fa-IR" => "arabext",
        "ff-Adlm" => "adlm",
        "ff-Adlm-BF" => "adlm",
        "ff-Adlm-CM" => "adlm",
        "ff-Adlm-GH" => "adlm",
        "ff-Adlm-GM" => "adlm",
        "ff-Adlm-GN" => "adlm",
        "ff-Adlm-GW" => "adlm",
        "ff-Adlm-LR" => "adlm",
        "ff-Adlm-MR" => "adlm",
        "ff-Adlm-NE" => "adlm",
        "ff-Adlm-NG" => "adlm",
        "ff-Adlm-SL" => "adlm",
        "ff-Adlm-SN" => "adlm",
        "ks" => "arabext",
        "ks-Arab" => "arabext",
        "ks-Arab-IN" => "arabext",
        "lrc" => "arabext",
        "lrc-IQ" => "arabext",
        "lrc-IR" => "arabext",
        "mni" => "beng",
        "mni-Beng" => "beng",
        "mni-Beng-IN" => "beng",
        "mr" => "deva",
        "mr-IN" => "deva",
        "my" => "mymr",
        "my-MM" => "mymr",
        "mzn" => "arabext",
        "mzn-IR" => "arabext",
        "ne" => "deva",
        "ne-IN" => "deva",
        "ne-NP" => "deva",
        "pa-Arab" => "arabext",
        "pa-Arab-PK" => "arabext",
        "ps" => "arabext",
        "ps-AF" => "arabext",
        "ps-PK" => "arabext",
        "sa" => "deva",
        "sa-IN" => "deva",
        "sat" => "olck",
        "sat-Olck" => "olck",
        "sat-Olck-IN" => "olck",
        "sd" => "arab",
        "sd-Arab" => "arab",
        "sd-Arab-PK" => "arab",
        "ur-IN" => "arabext",
        "uz-Arab" => "arabext",
        "uz-Arab-AF" => "arabext"
    ],

    // Numbering systems
    "NUMBERING_SYSTEMS" => [
        "arab" => "٠١٢٣٤٥٦٧٨٩",
        "arabext" => "۰۱۲۳۴۵۶۷۸۹",
        "beng" => "০১২৩৪৫৬৭৮৯",
        "cakm" => "𑄶𑄷𑄸𑄹𑄺𑄻𑄼𑄽𑄾𑄿",
        "tibt" => "༠༡༢༣༤༥༦༧༨༩",
        "adlm" => "𞥐𞥑𞥒𞥓𞥔𞥕𞥖𞥗𞥘𞥙",
        "deva" => "०१२३४५६७८९",
        "mymr" => "၀၁၂၃၄၅၆၇၈၉",
        "olck" => "᱐᱑᱒᱓᱔᱕᱖᱗᱘᱙"
    ],

    // Config client side variables
    "CONFIG_CLIENT_VARS" => [
        "DEBUG",
        "SESSION_TIMEOUT_COUNTDOWN", // Count down time to session timeout (seconds)
        "SESSION_KEEP_ALIVE_INTERVAL", // Keep alive interval (seconds)
        "API_FILE_TOKEN_NAME", // API file token name
        "API_URL", // API file name // PHP
        "API_ACTION_NAME", // API action name
        "API_OBJECT_NAME", // API object name
        "API_LIST_ACTION", // API list action
        "API_VIEW_ACTION", // API view action
        "API_ADD_ACTION", // API add action
        "API_EDIT_ACTION", // API edit action
        "API_DELETE_ACTION", // API delete action
        "API_LOGIN_ACTION", // API login action
        "API_FILE_ACTION", // API file action
        "API_UPLOAD_ACTION", // API upload action
        "API_JQUERY_UPLOAD_ACTION", // API jQuery upload action
        "API_SESSION_ACTION", // API get session action
        "API_LOOKUP_ACTION", // API lookup action
        "API_LOOKUP_PAGE", // API lookup page name
        "API_IMPORT_ACTION", // API import action
        "API_EXPORT_ACTION", // API export action
        "API_EXPORT_CHART_ACTION", // API export chart action
        "API_CHAT_ACTION", // API chat action
        "PUSH_SERVER_PUBLIC_KEY", // Push Server Public Key
        "API_PUSH_NOTIFICATION_ACTION", // API push notification action
        "API_PUSH_NOTIFICATION_SUBSCRIBE", // API push notification subscribe
        "API_PUSH_NOTIFICATION_DELETE", // API push notification delete
        "API_2FA_ACTION", // API two factor authentication action
        "API_2FA_ENABLE", // API two factor authentication enable
        "API_2FA_DISABLE", // API two factor authentication disable
        "API_2FA_SHOW", // API two factor authentication show
        "API_2FA_VERIFY", // API two factor authentication verify
        "API_2FA_RESET", // API two factor authentication reset
        "API_2FA_BACKUP_CODES", // API two factor authentication backup codes
        "API_2FA_NEW_BACKUP_CODES", // API two factor authentication new backup codes
        "API_2FA_SEND_OTP", // API two factor authentication send one time password
        "FORCE_TWO_FACTOR_AUTHENTICATION", // Force two factor authentication
        "TWO_FACTOR_AUTHENTICATION_TYPES", // Two factor authentication types
        "TWO_FACTOR_AUTHENTICATION_PASS_CODE_LENGTH",
        "MULTIPLE_OPTION_SEPARATOR", // Multiple option separator
        "AUTO_SUGGEST_MAX_ENTRIES", // Auto-Suggest max entries
        "LOOKUP_PAGE_SIZE", // Lookup page size
        "FILTER_PAGE_SIZE", // Filter page size
        "MAX_EMAIL_RECIPIENT",
        "UPLOAD_THUMBNAIL_WIDTH", // Upload thumbnail width
        "UPLOAD_THUMBNAIL_HEIGHT", // Upload thumbnail height
        "MULTIPLE_UPLOAD_SEPARATOR", // Upload multiple separator
        "IMPORT_FILE_ALLOWED_EXTENSIONS", // Import file allowed extensions
        "USE_COLORBOX",
        "PROJECT_STYLESHEET_FILENAME", // Project style sheet
        "EMBED_PDF",
        "LAZY_LOAD",
        "REMOVE_XSS",
        "ENCRYPTED_PASSWORD",
        "INVALID_USERNAME_CHARACTERS",
        "INVALID_PASSWORD_CHARACTERS",
        "USE_RESPONSIVE_TABLE",
        "RESPONSIVE_TABLE_CLASS",
        "SEARCH_FILTER_OPTION",
        "OPTION_HTML_TEMPLATE",
        "PAGE_LAYOUT",
        "CLIENT_VALIDATE",
        "IN_OPERATOR_VALUE_SEPARATOR",
        "TABLE_BASIC_SEARCH",
        "TABLE_BASIC_SEARCH_TYPE",
        "TABLE_PAGE_NUMBER",
        "TABLE_SORT",
        "FORM_KEY_COUNT_NAME",
        "FORM_ROW_ACTION_NAME",
        "FORM_BLANK_ROW_NAME",
        "FORM_OLD_KEY_NAME",
        "IMPORT_MAX_FAILURES",
        "USE_JAVASCRIPT_MESSAGE",
		"MS_USE_MESSAGE_BOX_INSTEAD_OF_TOAST",
        "LIST_ACTION",
        "VIEW_ACTION",
        "EDIT_ACTION",
        "COOKIE_SAMESITE",
        "RTL_LANGUAGES"
    ],

    // Global client side variables
    "GLOBAL_CLIENT_VARS" => [
        "DATE_FORMAT", // Date format
        "TIME_FORMAT", // Time format
        "DATE_SEPARATOR", // Date separator
        "TIME_SEPARATOR", // Time separator
        "DECIMAL_SEPARATOR", // Decimal separator
        "GROUPING_SEPARATOR", // Grouping separator
        "NUMBER_FORMAT", // Number format
        "PERCENT_FORMAT", // Percent format
        "CURRENCY_CODE", // Currency code
        "CURRENCY_SYMBOL", // Currency code
        "NUMBERING_SYSTEM", // Numbering system
        "TokenNameKey", // Token name key
        "TokenName", // Token name
        "CurrentUserName", // Current user name
        "CurrentUserIdentifier", // Current user identifier
        "IsSysAdmin", // Is system admin
    ],

    // Doctrine ORM
    "DOCTRINE" => [
        // Path where Doctrine will cache the processed metadata when "DEV_MODE" is false
        "CACHE_DIR" => "log/cache/doctrine",

        // List of paths where Doctrine will search for metadata
        "METADATA_DIRS" => [__DIR__ . "/Entity"],

        // Result cache
        "RESULT_CACHE_DIR" => "log/cache/result",
        "RESULT_CACHE_LIFETIME" => 600, // Lookup cache lifetime in seconds
    ],

    // Captcha
    "CAPTCHA_CLASS" => CaptchaBase::class,

    // Chat
    "CHAT_URL" => "",

    // CORS
    "CORS" => [
        "origin" => ["*"],
        "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
        "headers.allow" => ["X-Requested-With", "Origin", "Authorization"],
        "credentials" => true
    ],
    "NONCE" => false,
    // CSP (See https://github.com/paragonie/csp-builder?tab=readme-ov-file#example)
    "CSP" => [
        "font-src" => [
            "self" => true,
            "data" => true,
            "allow" => [
                "https://fonts.gstatic.com",
            ],
        ],
        "form-action" => [
            "self" => true,
        ],
        "object-src" => [
            "self" => true,
        ],
        "frame-ancestors" => [
            "self" => true,
        ],
        "frame-src" => [
            "self" => true,
            "allow" => [
                "*.google.com",
                "*.youtube.com",
            ],
        ],
        "script-src" => [
            "self" => true,
            "unsafe-inline" => true,
            "unsafe-eval" => true,
            "blob" => true,
            "allow" => [
                "https://www.google-analytics.com",
                "https://*.googleapis.com",
                "https://*.gstatic.com",
                "*.google.com",
                "https://*.ggpht.com",
                "*.googleusercontent.com",
                "https://js.pusher.com",
                "https://cdn.tiny.cloud",
                "https://*.youtube.com",
            ],
        ],
        "connect-src" => [
            "self" => true,
            "blob" => true,
            "data" => true,
            "allow" => [
                "https://*.googleapis.com",
                "https://*.gstatic.com",
                "*.google.com",
            ],
        ],
        "style-src" => [
            "self" => true,
            "unsafe-inline" => true,
            "allow" => [
                "https://*.gstatic.com",
                "https://*.googleapis.com",
            ],
        ],
        "style-src-attr" => [
            "unsafe-inline" => true,
        ],
        "img-src" => [
            "self" => true,
            "blob" => true,
            "data" => true,
            "allow" => [
                "https://*.googleapis.com",
                "https://*.gstatic.com",
                "https://*.openstreetmap.org",
                "https://api.mapbox.com",
                "*.google.com",
                "*.googleusercontent.com",
            ],
        ],
        "worker-src" => [
            "self" => true,
            "blob" => true,
        ],
    ],

    // Notifier
    "NOTIFIER" => [
        "channels" => [
            "email" => \DI\create(EmailChannel::class)->constructor(\DI\get(TransportInterface::class), null, "masino.sinaga@gmail.com")
        ]
    ],

    // Services config files
    "SERVICES_CONFIG_FILES" => [
        "services.php"
    ],

    // Service locator values, e.g. ['foo' => 'bar'] will add ['foo' => service('bar')] to the service_locator() argument
    "SERVICE_LOCATOR_VALUES" => [],

    // Browser tab ID
    "USE_TAB_ID" => true,

    // Login rate limiters
    "LOGIN_RATE_LIMITERS" => [
        "global" => [
            'id' => "login-global",
            'policy' => 'fixed_window',
            // Login attempts are limited on 5 * max_attempts failed requests for IP address or username
            'limit' => 15,
            // The value of the interval option must be a number followed by any of the units accepted by the PHP date relative formats (e.g. 3 seconds, 10 hours, 1 day, etc.)
            'interval' => '4 minutes',
        ],
        "local" => [
            'id' => "login-local",
            'policy' => 'fixed_window',
            // Login attempts are limited on max_attempts failed requests for IP address + username
            'limit' => 3,
            // The value of the interval option must be a number followed by any of the units accepted by the PHP date relative formats (e.g. 3 seconds, 10 hours, 1 day, etc.)
            'interval' => '4 minutes',
        ]
    ],

    // Home page
    "HOME_PAGE" => "home",

    // Remember me
    "REMEMBER_ME" => [
        "CACHE_DIR" => "log/cache/rememberme",
        "OUTDATED_TOKEN_TTL" => 60
    ],

    // Long press delay
    "LONG_PRESS_DELAY" => 1000,

	// UserCardUserName
	"MS_USER_CARD_USER_NAME" => "UserCardUserName",
	// UserCardCompleteName
	"MS_USER_CARD_COMPLETE_NAME" => "UserCardCompleteName",
	// UserCardPosition
	"MS_USER_CARD_POSITION" => "UserCardPosition",
	// Begin of modification Enter for Moving Cursor to Next Field, by Masino Sinaga, October 10, 2014
	"MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD" => TRUE,
	// End of modification Enter for Moving Cursor to Next Field, by Masino Sinaga, October 10, 2014
	// Begin of modification Maintenance Mode, by Masino Sinaga, May 12, 2012
	"MS_MAINTENANCE_MODE" => FALSE, // Set the second parameter to TRUE if you want to display your website in Maintenance Mode
	"MS_MAINTENANCE_END_DATETIME" => "2019-10-01 23:59:59", // Set the second parameter to the future date/time value in "yyyy-MM-dd hh:mm:ss" format, if you want the system calculate how much long the system takes duration time to get the end of maintenance date/time. For example: 2011-08-30 17:28:00
	"MS_MAINTENANCE_TEXT" => "We are currently down for maintenance, please don't look so sad...", // Just for displaying maintenance message to user with admin level, nothing else!
	"MS_AUTO_NORMAL_AFTER_MAINTENANCE" => TRUE, // Set the second parameter to TRUE if you want the system to be automatically switch from the Maintenance Mode to Normal Mode whenever the end of maintenance date/time has been reached.
	// End of modification Maintenance Mode, by Masino Sinaga, May 12, 2012

	// Begin of modification Announcement in All Pages, by Masino Sinaga, May 12, 2012
	"MS_SHOW_ANNOUNCEMENT" => FALSE,  // Announcement status, set the second parameter to TRUE to show the announcement in all pages.
	"MS_ANNOUNCEMENT_TEXT" => "This announcement text can be adjusted from the config file...", // Announcement text, the value is derived from the .xml languages files
	// End of modification Announcement in All Pages, by Masino Sinaga, May 12, 2012

	// Begin of modification Add Announcement Feature, by Masino Sinaga, February 4, 2014
	"MS_SEPARATED_ANNOUNCEMENT" => TRUE, // flag to separate announcement
	"MS_ANNOUNCEMENT_TABLE" => "announcement", // Announcement table name, adjust with yours!
	// End of modification Add Announcement Feature, by Masino Sinaga, February 4, 2014

	// Begin of modification Languages Feature, by Masino Sinaga, July 3, 2012
	"MS_LANGUAGES_TABLE" => "languages",
	// End of modification Languages Feature, by Masino Sinaga, July 3, 2012

	// Begin of modification Application Settings Feature, by Masino Sinaga, July 3, 2012
	"MS_SETTINGS_TABLE" => "settings",
	// End of modification Application Settings Feature, by Masino Sinaga, July 3, 2012
	"MS_HELP_TABLE_NAME" => "help", // Help table name, adjust with yours!
	// End of modification Enable Help Online, by Masino Sinaga, September 19, 2014
	"MS_LANGUAGE_TABLE_NAME" => "languages", // Help table name, adjust with yours!
	"MS_MASINO_BREADCRUMBLINKS_TABLE" => "breadcrumblinks",
	"MS_USE_CUSTOM_DOMAIN_URL" => FALSE, // whether to use custom domain URL.
	"MS_CUSTOM_DOMAIN_URL" => "http://www.mydomain.com", // useful if you host your web app under a sub-directory, and you don't want to include that sub-directory; but you just want to show your own custom domain
	"MS_REMOVE_PORT_URL" => TRUE, // remove port from URL
	"MS_REMOVE_DIR_PATH" => FALSE, // remove directory from url path
	"MS_DIR_PATH_NAME" => "dir", // directory path name to be removed
];

// Merge environment config
$CONFIG = array_merge(
    $CONFIG,
    require("config.security.php"),
    require("config." . $CONFIG["ENVIRONMENT"] . ".php")
);
