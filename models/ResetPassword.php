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
class ResetPassword extends Users
{
    use MessagesTrait;

    // Page ID
    public string $PageID = "reset_password";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "ResetPassword";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "resetpassword";

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
    public DbField $Email;
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

        // Create Email field object (used by validation only)
        $this->Email = new DbField(UserTable(), "email", "email", "email", "", 202, 255);
        $this->Email->EditAttrs->appendClass("form-control ew-form-control");

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
        Breadcrumb()->add("reset_password", "ResetPwd", CurrentUrl(), "", "", true);
        $this->Heading = $this->language->phrase("ResetPwd");
        $postBack = IsPost();
        $validEmail = false;
        $action = "";
        $userName = "";
        $activateCode = "";
        $filter = null;
        if ($postBack) {
            // Setup variables
            $this->Email->setFormValue(Post($this->Email->FieldVar));
            $validEmail = $this->validateForm();
            if ($validEmail) {
                $action = "reset"; // Prompt user to change password
            }

            // Set up filter
            if (UserTable()->Fields[Config("USER_EMAIL_FIELD_NAME")]?->isEncrypt()) { // If encrypted, need to loop through all records
                $filter = null;
            } else {
                $filter = [Config("USER_EMAIL_PROPERTY_NAME") => $this->Email->CurrentValue];
            }

        // Handle email activation
        } elseif (Get("action") != "") {
            $action = Get("action");
            $userName = Get("user");
            $activateCode = Decrypt(Get("code"));
            @list($activateUserName, $dt) = explode(",", $activateCode);
            if (
                $userName != $activateUserName
                || IsEmpty($dt)
                || DateDiff($dt, StdCurrentDateTime(), "n") < 0
                || DateDiff($dt, StdCurrentDateTime(), "n") > Config("RESET_PASSWORD_TIME_LIMIT")
                || !SameText($action, "reset")
            ) { // Email activation
                if (!$this->peekFailureMessage()) {
                    $this->setFailureMessage($this->language->phrase("ActivateFailed")); // Set activate failed message
                }
                $this->terminate(UrlFor("login")); // Go to login page
                return;
            }
            if (SameText($action, "reset")) {
                $action = "resetpassword";
            }
            $filter = [Config("LOGIN_USERNAME_PROPERTY_NAME") => $userName];
        }
        if ($action != "") {
            $users = $filter ? UserRepository()->findBy($filter) : UserRepository()->findAll();
            if ($users) {
                $validEmail = false;
                foreach ($users as $user) {
                    if ($action == "resetpassword") { // Check username if email activation
                        $validEmail = SameString($userName, $user->get(Config("LOGIN_USERNAME_FIELD_NAME")));
                    } else {
                        $validEmail = SameText($this->Email->CurrentValue, $user->get(Config("USER_EMAIL_FIELD_NAME")));
                    }
                    if ($validEmail) {
                        // Call User Recover Password event
                        $validEmail = $this->userRecoverPassword($user);
                        if ($validEmail) {
                            $userName = $user->get(Config("LOGIN_USERNAME_FIELD_NAME"));
                            $password = $user->get(Config("LOGIN_PASSWORD_FIELD_NAME"));
                        }
                    }
                    if ($validEmail) {
                        break;
                    }
                }
                if ($validEmail) {
                    if (SameText($action, "resetpassword")) { // Reset password
                        Session(SESSION_USER_PROFILE_USER_NAME, $userName); // Save login user name
                        Session(SESSION_STATUS, "passwordreset");
                        $this->terminate("changepassword");
                        return;
                    } else {
                        $emailSent = false;
                        $activateLink = FullUrl(CurrentPageUrl(false), "resetpwd") . "?action=reset&user=" . rawurlencode($userName) .
                            "&code=" . Encrypt($userName . "," . StdCurrentDateTime());
                        $email = new Email();
                        $email->load(Config("EMAIL_RESET_PASSWORD_TEMPLATE"), data: [
                            "From" => Config("SENDER_EMAIL"), // Replace Sender
                            "To" => $this->Email->CurrentValue, // Replace Sender
                            "ActivateLink" => $activateLink,
                            "UserName" => $userName
                        ]);
						$email->replaceSubject($this->language->phrase("SubjectRequestPasswordConfirmation"). ' '.$this->language->projectPhrase("BodyTitle"));
                        $args = ["user" => $user, "row" => $user->toArray()];
                        if ($this->emailSending($email, $args)) {
                            $emailSent = $email->send();
                        }
                        if (!$emailSent) {
                            $this->setFailureMessage($email->LastError); // Set up error message
                        }
                    }
                }
            }
            $this->setSuccessMessage($this->language->phrase("ResetPasswordResponse")); // Set up success message
            $this->terminate(UrlFor("login")); // Return to login page
            return;
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
        if (IsEmpty($this->Email->CurrentValue)) {
            $this->Email->addErrorMessage(sprintf($this->language->phrase("EnterRequiredField"), $this->language->phrase("Email")));
            $validateForm = false;
        }
        if (!CheckEmail($this->Email->CurrentValue)) {
            $this->Email->addErrorMessage($this->language->phrase("IncorrectEmail"));
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

    // Email Sending event
    public function emailSending(Email $email, array $args): bool
    {
        //var_dump($email, $args); exit();
        return true;
    }

    // Form Custom Validate event
    public function formCustomValidate(string &$customError): bool
    {
        // Return error message in $customError
        return true;
    }

    // User RecoverPassword event
    public function userRecoverPassword(UserInterface $user): bool
    {
        // Return false to abort
        return true;
    }
}
