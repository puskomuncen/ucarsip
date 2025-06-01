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
class Userpriv extends Userlevels
{
    use MessagesTrait;

    // Page ID
    public string $PageID = "userpriv";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "Userpriv";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "userpriv";

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

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
        global $DashboardReport;
        $this->TableVar = 'userlevels';
        $this->TableName = 'userlevels';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (userlevels)
        if (!isset($GLOBALS["userlevels"]) || $GLOBALS["userlevels"]::class == PROJECT_NAMESPACE . "userlevels") {
            $GLOBALS["userlevels"] = &$this;
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
            Redirect(GetUrl($url));
        }
        return; // Return to controller
    }
    public string $Disabled = "";
    public int $TableNameCount = 0;
    public array $Privileges = [];
    public array $UserLevelList = [];
    public array $UserLevelPrivList = [];
    public array $TableList = [];

    /**
     * Page run
     *
     * @return void
     */
    public function run(): void
    {
        global $ExportType;

// Use layout
        $this->UseLayout = $this->UseLayout && ConvertToBool(Param(Config("PAGE_LAYOUT"), true));

        // View
        $this->View = Get(Config("VIEW"));
        $this->CurrentAction = Param("action"); // Set up current action

		// Call this new function from userfn*.php file
		My_Global_Check(); // Modified by Masino Sinaga, September 10, 2023

        // Global Page Loading event (in userfn*.php)
        DispatchEvent(new PageLoadingEvent($this), PageLoadingEvent::NAME);

        // Page Load event
        if (method_exists($this, "pageLoad")) {
            $this->pageLoad();
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
        Breadcrumb()
            ->add("list", "userlevels", "userlevelslist", "", "userlevels")
            ->add("userpriv", "UserLevelPermission", CurrentUrl());
        $this->Heading = $this->language->phrase("UserLevelPermission");

        // Load user level settings
        $this->UserLevelList = $GLOBALS["USER_LEVELS"];
        $this->UserLevelPrivList = $GLOBALS["USER_LEVEL_PRIVS"];
        $ar = $GLOBALS["USER_LEVEL_TABLES"];

        // Set up allowed table list
        foreach ($ar as $t) {
            if ($t[3]) { // Allowed
                $tempPriv = $this->security->getUserLevelPrivEx($t[4] . $t[0], $this->security->CurrentUserLevelID);
                if (($tempPriv & Allow::GRANT->value) == Allow::GRANT->value) { // Allow Grant
                    $this->TableList[] = array_merge($t, [$tempPriv]);
                }
            }
        }
        $this->TableNameCount = count($this->TableList);

        // Get action
        if (Post("action") == "") {
            $this->CurrentAction = "show"; // Display with input box
            // Load key from QueryString
            if (Get("ID") !== null) {
                $this->ID->setQueryStringValue(Get("ID"));
            } else {
                $this->terminate("userlevelslist"); // Return to list
                return;
            }
            if ($this->ID->QueryStringValue == "-1") {
                $this->Disabled = " disabled";
            } else {
                $this->Disabled = "";
            }
        } else {
            $this->CurrentAction = Post("action");
            // Get fields from form
            $this->ID->setFormValue(Post("x_ID"));
			if (MS_ENABLE_PERMISSION_FOR_EXPORT_DATA == true) {
				// Begin of modification Permission Access for Export To Feature, by Masino Sinaga, September 11, 2023
				for ($i = 0; $i < $this->TableNameCount; $i++) {
					if (Post("table_" . $i) !== null) {
						if (Post("admin_" . $i) !== null) { // Admin permission
							$this->Privileges[$i] = (int)Post("admin_" . $i);
						} else { // All other permissions
							$this->Privileges[$i] = (int)Post("add_" . $i) 
								+ (int)Post("delete_" . $i) 
								+ (int)Post("edit_" . $i) 
								+ (int)Post("list_" . $i)
								+ (int)Post("access_" . $i)
								+ (int)Post("view_" . $i)
								+ (int)Post("search_" . $i)
								+ (int)Post("grant_" . $i)
								+ (int)Post("import_" . $i) 
								+ (int)Post("lookup_" . $i)
								+ (int)Post("export_" . $i) 
								+ (int)Post("push_" . $i)  
								+ (int)Post("print_" . $i) 
								+ (int)Post("excel_" . $i) 
								+ (int)Post("word_" . $i) 
								+ (int)Post("html_" . $i) 
								+ (int)Post("xml_" . $i) 
								+ (int)Post("csv_" . $i) 
								+ (int)Post("pdf_" . $i) 
								+ (int)Post("email_" . $i);
						}
					}
				}
				// End of modification Permission Access for Export To Feature, by Masino Sinaga, September 11, 2023
			} else {
				for ($i = 0; $i < $this->TableNameCount; $i++) {
					if (Post("table_" . $i) !== null) {
						if (Post("admin_" . $i) !== null) { // Admin permission
							$this->Privileges[$i] = (int)Post("admin_" . $i);
						} else { // All other permissions
							$this->Privileges[$i] = (int)Post("add_" . $i)
								+ (int)Post("delete_" . $i)
								+ (int)Post("edit_" . $i)
								+ (int)Post("list_" . $i)
								+ (int)Post("access_" . $i)
								+ (int)Post("view_" . $i)
								+ (int)Post("search_" . $i)
								+ (int)Post("grant_" . $i)
								+ (int)Post("import_" . $i)
								+ (int)Post("lookup_" . $i)
								+ (int)Post("export_" . $i)
								+ (int)Post("push_" . $i);
						}
					}
				}
			}
        }

        // Should not edit own permissions
        if ($this->security->hasUserLevelID($this->ID->CurrentValue)) {
            $this->terminate("userlevelslist"); // Return to list
            return;
        }
        switch ($this->CurrentAction) {
            case "show": // Display
                if (!$this->security->loadFromStorage()) { // Get all User Level info
                    $this->terminate("userlevelslist"); // Return to list
                    return;
                }
                $ar = [];
                for ($i = 0; $i < $this->TableNameCount; $i++) {
                    $table = $this->TableList[$i];
                    $cnt = count($table);
                    $tempPriv = $this->security->getUserLevelPrivEx($table[4] . $table[0], $this->ID->CurrentValue);
                    $ar[] = ["table" => $this->getTableCaption($i), "name" => $table[1], "index" => $i, "permission" => $tempPriv, "allowed" => $table[$cnt - 1]];
                }
                $this->Privileges["disabled"] = $this->Disabled;
                $this->Privileges["permissions"] = $ar;
                $privileges = Allow::privileges();
                $this->Privileges["ids"] = array_keys($privileges);
                foreach ($privileges as $k => $v) {
                    $this->Privileges[$k] = $v;
                }
                break;
            case "update": // Update
                if ($this->editRow()) { // Update record based on key
                    if (!$this->peekSuccessMessage()) {
                        $this->setSuccessMessage($this->language->phrase("UpdateSuccess")); // Set up update success message
                    }
                    // Alternatively, comment out the following line to go back to this page
                    $this->terminate("userlevelslist"); // Return to list
                    return;
                }
        }

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

    // Update privileges
    protected function editRow(): bool
    {
        $c = Conn(Config("USER_LEVEL_PRIV_DBID"));
        foreach ($this->Privileges as $i => $privilege) {
            $table = $this->TableList[$i];
            $cnt = count($table);
            $sql = "SELECT COUNT(*) FROM " . Config("USER_LEVEL_PRIV_TABLE") . " WHERE " .
                Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " = '" . AdjustSql($table[4] . $table[0]) . "' AND " .
                Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . " = " . $this->ID->CurrentValue;
            $privilege &= $table[$cnt - 1]; // Set maximum allowed privilege (protect from hacking)
            $count = $c->fetchOne($sql);
            if ($count > 0) {
                $sql = "UPDATE " . Config("USER_LEVEL_PRIV_TABLE") . " SET " . Config("USER_LEVEL_PRIV_PRIV_FIELD") . " = " . $privilege . " WHERE " .
                    Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " = '" . AdjustSql($table[4] . $table[0]) . "' AND " .
                    Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . " = " . $this->ID->CurrentValue;
                $c->executeStatement($sql);
            } else {
                $sql = "INSERT INTO " . Config("USER_LEVEL_PRIV_TABLE") . " (" . Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . ", " . Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . ", " . Config("USER_LEVEL_PRIV_PRIV_FIELD") . ") VALUES ('" . AdjustSql($table[4] . $table[0]) . "', " . $this->ID->CurrentValue . ", " . $privilege . ")";
                $c->executeStatement($sql);
            }
        }
        $this->security->setupUserLevel();
        return true;
    }

    // Get table caption
    protected function getTableCaption(int $i): string
    {
        $caption = "";
        if ($i < $this->TableNameCount) {
            $caption = Language()->tablePhrase($this->TableList[$i][1], "TblCaption");
            if ($caption == "") {
                $caption = $this->TableList[$i][2];
            }
            if ($caption == "") {
                $caption = $this->TableList[$i][0];
                $caption = preg_replace('/^\{\w{8}-\w{4}-\w{4}-\w{4}-\w{12}\}/', '', $caption); // Remove project id
            }
        }
        return $caption;
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
    // $type = ''|'success'|'failure'
    public function messageShowing(string &$message, string $type): void
    {
        // Example:
        //if ($type == "success") $message = "your success message";
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
}
