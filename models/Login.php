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
class Login extends Users
{
    use MessagesTrait;

    // Page ID
    public string $PageID = "login";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "Login";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "login";

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
        $this->TableVar = 'users';
        $this->TableName = 'users';

        // Table CSS class
        $this->TableClass = "table table-striped table-bordered table-hover table-sm ew-view-table";

        // Initialize
        $GLOBALS["Page"] = &$this;

        // Save if user language changed
        if (Param("language") !== null) {
            Profile()->setLanguageId(Param("language"))->saveToStorage();
        }

        // Table object (users)
        if (!isset($GLOBALS["users"]) || $GLOBALS["users"]::class == PROJECT_NAMESPACE . "users") {
            $GLOBALS["users"] = &$this;
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
                WriteJson(["url" => $url]);
            } else {
                Redirect(GetUrl($url));
            }
        }
        return; // Return to controller
    }

    // Properties
    public DbField $Username;
    public DbField $Password;
    public DbField $RememberMe;
    public bool $IsModal = false;
    public string $OffsetColumnClass = ""; // Override user table

    /**
     * Page run
     *
     * @return void
     */
    public function run(): void
    {
        global $ExportType, $SkipHeaderFooter;

        // Create Username/Password field object (used by validation only)
        $this->Username = new DbField("users", "username", "username", "username", "", 202, 255);
        $this->Username->EditAttrs->appendClass("form-control ew-form-control");
		$this->Username->PlaceHolder = $this->language->phrase("Username", true);
        $this->Password = new DbField("users", "password", "password", "password", "", 202, 255);
        $this->Password->EditAttrs->appendClass("form-control ew-form-control");
		$this->Password->PlaceHolder = $this->language->phrase("Password", true);
        if (Config("ENCRYPTED_PASSWORD")) {
            $this->Password->Raw = true;
        }
        $this->RememberMe = new DbField("users", Config("SECURITY.firewalls.main.remember_me.remember_me_parameter"), "rememberme", "", "", 202, 255);

// Is modal
        $this->IsModal = IsModal();
        $this->UseLayout = $this->UseLayout && !$this->IsModal;

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

        // Check modal
        if ($this->IsModal) {
            $SkipHeaderFooter = true;
        }
        Breadcrumb()->add("login", "LoginPage", CurrentUrl(), "", "", true);
        $this->Heading = $this->language->phrase("LoginPage");

        // Initialize
        $this->Username->setFormValue("");
        $this->Password->setFormValue("");
        $this->RememberMe->setFormValue("");

        // Get last URL
        $lastUrl = $this->security->lastUrl();
        if ($lastUrl == "") {
            $lastUrl = "index";
        }

        // Login
        $validPwd = false;
        if (IsLoggingIn()) { // After authorized by 2FA
            $this->Username->setFormValue(Session(SESSION_USER_PROFILE_USER_NAME) ?? CurrentUserIdentifier());
            $this->Password->setFormValue(Session(SESSION_USER_PROFILE_PASSWORD) ?? "");
            $validPwd = $this->security->validateUser($this->Username->CurrentValue, $this->Password->CurrentValue ?? "");
            if ($validPwd) {
                Session()->remove(SESSION_USER_PROFILE_USER_NAME);
                Session()->remove(SESSION_USER_PROFILE_PASSWORD);
                Session()->remove(SESSION_USER_PROFILE_REMEMBER_ME);
            }
        } elseif (Config("USE_TWO_FACTOR_AUTHENTICATION") && IsLoggingIn2FA()) { // Logging in via 2FA, redirect
            $this->terminate(UrlFor("login2fa"));
            return;
        } else { // Normal login
            if (!$this->security->isLoggedIn()) {
                $this->security->login();
            }
            $this->security->loadUserLevel(); // Load user level
            $valid = false;
            if (Post($this->Username->FieldVar) !== null) {
                $this->Username->setFormValue(Post($this->Username->FieldVar));
                $this->Password->setFormValue(Post($this->Password->FieldVar));
                $this->RememberMe->setFormValue(strtolower(Post($this->RememberMe->FieldVar, "")));
                $valid = $this->validateForm();
            } else { // Restore settings
                $this->Username->setFormValue(Session(SESSION_USER_PROFILE_USER_NAME) ?? "");
            }
            if (!IsEmpty($this->Username->CurrentValue)) {
                Session(SESSION_USER_PROFILE_USER_NAME, $this->Username->CurrentValue); // Save user name
            }

            // Check authentication
            if (IsAuthenticated()) {
                $valid = true;
				$this->Username->setFormValue(CurrentUserIdentifier());
            } elseif (GetLastAuthenticationError()) {
                $valid = false;
            }
            $validPwd = IsImpersonator();
            if ($valid) {
                // Call Logging In event
                $valid = $this->userLoggingIn($this->Username->CurrentValue, $this->Password->CurrentValue);
                if ($valid) {
                    $validPwd = $this->security->validateUser($this->Username->CurrentValue, $this->Password->CurrentValue ?? ""); // Manual login
                    if (!$validPwd) {
                        $this->Username->setFormValue(""); // Clear login name
                        $this->Username->addErrorMessage($this->language->phrase("InvalidUidPwd")); // Invalid user name or password
                        $this->Password->addErrorMessage($this->language->phrase("InvalidUidPwd")); // Invalid user name or password
                    }
                } else {
                    if (!$this->peekFailureMessage()) {
                        $this->setFailureMessage($this->language->phrase("LoginCancelled")); // Login cancelled
                    }
                }
            }
        }

        // Check user login session
        $profile = Profile();
        if ($validPwd && !IsSysAdmin() && !IsImpersonator() && !$profile->passwordExpired() && !$profile->isValidUser(SessionId())) {
            $message = sprintf($this->language->phrase("UserLoggedIn"), $profile->getUserIdentifier());
            SecurityHelper()?->logout(false);
            $this->security->logout();
            if ($this->IsModal) {
                WriteJson(["error" => ["description" => $message]]);
                $this->terminate();
                return;
            } else {
                $this->setFailureMessage($message); // Set failure message first
                $validPwd = false;
            }
        }

        // After login
        if ($validPwd) {
            if (IsPost() && $this->RememberMe->CurrentValue != "1" && IsRememberMe()) { // Don't remember me
                $this->security->clearRememberMeCookie();
            }

            // Save to session (for extension)
            Session(["_BasePath" => BasePath(), "_UserId" => IsSysAdmin() ? -1 : CurrentUserPrimaryKey()]);

            // Call User_LoggedIn event
            $this->userLoggedIn(CurrentUserIdentifier());

            // External provider, just redirect
            if (IsAccessTokenUser()) {
                $this->IsModal = false;
            }
            $this->terminate($lastUrl); // Return to last accessed URL
            $this->security->removeLastUrl();
            return;
        } elseif (!IsEmpty($this->Username->CurrentValue) && !IsEmpty($this->Password->CurrentValue)) {
            // Call user login error event
            $this->userLoginError($this->Username->CurrentValue, $this->Password->CurrentValue);
        }

        // Set up error message
        if (IsEmpty($this->Username->ErrorMessage)) {
            $this->Username->ErrorMessage = $this->language->phrase("EnterUserName");
        }
        if (IsEmpty($this->Password->ErrorMessage)) {
            $this->Password->ErrorMessage = $this->language->phrase("EnterPassword");
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

    // Validate form
    protected function validateForm(): bool
    {
        // Check if validation required
        if (!Config("SERVER_VALIDATE")) {
            return true;
        }
        $validateForm = true;
        if (IsEmpty($this->Username->CurrentValue)) {
            $this->Username->addErrorMessage($this->language->phrase("EnterUserName"));
            $validateForm = false;
        }
        if (IsEmpty($this->Password->CurrentValue) && !Config("OTP_ONLY")) { // Ignore if password checking disabled
            $this->Password->addErrorMessage($this->language->phrase("EnterPassword"));
            $validateForm = false;
        }

        // Call Form Custom Validate event
        $formCustomError = "";
        $validateForm = $validateForm && $this->formCustomValidate($formCustomError);
        if ($formCustomError != "") {
            $this->setFailureMessage($formCustomError);
        }
        return $validateForm;
    }

    // Page Load event
    public function pageLoad(): void
    {
        Config("MS_ENTER_MOVING_CURSOR_TO_NEXT_FIELD", TRUE);
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

    // User Logging In event
    public function userLoggingIn(string $userName, string $password): bool
    {
        // Enter your code here
        // To cancel, set return value to false
        return true;
    }

    // User Logged In event
    public function userLoggedIn(string $userName): void
    {
        //Log("User Logged In");
    }

    // User Login Error event
    public function userLoginError(string $userName, string $password): void
    {
        //Log("User Login Error");
    }

    // Form Custom Validate event
    public function formCustomValidate(string &$customError): bool
    {
        // Return error message in $customError
        return true;
    }
}
