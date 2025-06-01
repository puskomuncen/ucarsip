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
class PersonalData
{
    use MessagesTrait;

    // Page ID
    public string $PageID = "personal_data";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Table name
    public string $TableName = "";

    // Table variable
    public string $TableVar = "";

    // Page object name
    public string $PageObjName = "PersonalData";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "unitsdelete";

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

    public function __construct(protected Language $language, protected AdvancedSecurity $security)
    {
        global $DashboardReport;

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Open connection
        $GLOBALS["Conn"] ??= GetConnection();
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
        DispatchEvent(new PageUnloadedEvent($this), PageUnloadedEvent::NAME);

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

    /**
     * @var DbField
     */
    public DbField $Password;

    /**
     * Page run
     *
     * @return void
     */
    public function run(): void
    {
        global $ExportType;

        // Create Password field object (used by validation only)
        $this->Password = new DbField(UserTable(), "password", "password", "password", "", 202, 255);
        $this->Password->EditAttrs->appendClass("form-control ew-form-control");

// Use layout
        $this->UseLayout = $this->UseLayout && ConvertToBool(Param(Config("PAGE_LAYOUT"), true));

        // View
        $this->View = Get(Config("VIEW"));

		// Call this new function from userfn*.php file
		My_Global_Check(); // Modified by Masino Sinaga, September 10, 2023

        // Global Page Loading event (in userfn*.php)
        DispatchEvent(new PageLoadingEvent($this), PageLoadingEvent::NAME);

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
        Breadcrumb()->add("personal_data", "PersonalDataTitle", CurrentUrl(), "ew-personal-data", "", true);
        $this->Heading = $this->language->phrase("PersonalDataTitle");
        $cmd = Param("cmd");
        if (SameText($cmd, "Download")) {
            if ($this->personalDataResult()) {
                $this->terminate();
                return;
            }
        } elseif (SameText($cmd, "Delete") && IsPost()) {
            if ($this->deletePersonalData()) {
                $this->terminate(UrlFor("logout", [], ["deleted" => "1"]));
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

    /**
     * Write personal data as JSON
     *
     * @return bool
     */
    protected function personalDataResult(): bool
    {
        $fldNames = [];
        $user = LoadUserByIdentifier(CurrentUserName());
        if ($user) {
            // Call PersonalData_Downloading event
            PersonalData_Downloading($user);
            $personalDataFileName = Get("_personaldatafilename", "personaldata.json");
            AddHeader("Content-Disposition", "attachment; filename=\"" . $personalDataFileName . "\"");
            WriteJson($user->toArray());
            return true;
        } else {
            $this->setFailureMessage($this->language->phrase("NoRecord")); // No record found
            return false;
        }
    }

    /**
     * Delete personal data
     *
     * @return bool
     */
    protected function deletePersonalData(): bool
    {
        $pwd = Post($this->Password->FieldVar, "");
        $user = CurrentUser();
        if ($user) {
            if (VerifyPassword($user->get(Config("LOGIN_PASSWORD_FIELD_NAME")), $pwd)) {
                if (Config("DELETE_UPLOADED_FILES")) { // Delete old files
                    UserTable()->deleteUploadedFiles($user->toArray());
                }
                try {
                    UserTable()->deleteSql(null, [Config("LOGIN_USERNAME_FIELD_NAME") => $user->getUserIdentifier()])
                        ->executeStatement();

                    // Call PersonalData_Deleted event
                    PersonalData_Deleted($user);
                    return true;
                } catch (\Exception $e) {
                    $this->setFailureMessage($this->language->phrase("PersonalDataDeleteFailure") . ": " . $e->getMessage());
                    return false;
                }
            } else {
                $this->Password->addErrorMessage($this->language->phrase("InvalidPassword"));
                return false;
            }
        } else {
            $this->setFailureMessage($this->language->phrase("NoRecord"));
            return false;
        }
    }
}
