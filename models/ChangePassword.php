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
class ChangePassword extends Users
{
    use MessagesTrait;

    // Page ID
    public string $PageID = "change_password";

    // Project ID
    public string $ProjectID = PROJECT_ID;

    // Page object name
    public string $PageObjName = "ChangePassword";

    // View file path
    public ?string $View = null;

    // Title
    public ?string $Title = null; // Title for <title> tag

    // CSS class/style
    public string $CurrentPageName = "changepassword";

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
    public bool $IsModal = false;
    public DbField $OldPassword;
    public DbField $NewPassword;
    public DbField $ConfirmPassword;
    public string $OffsetColumnClass = ""; // Override user table

    /**
     * Page run
     *
     * @return void
     */
    public function run(): void
    {
        global $ExportType, $SkipHeaderFooter;

        // Create Password fields object (used by validation only)
        $this->OldPassword = new DbField(UserTable(), "opwd", "opwd", "opwd", "", 202, 255);
        $this->OldPassword->EditAttrs->appendClass("form-control ew-form-control");
        $this->NewPassword = new DbField(UserTable(), "npwd", "npwd", "npwd", "", 202, 255);
        $this->NewPassword->EditAttrs->appendClass("form-control ew-form-control");
        $this->NewPassword->EditAttrs->appendClass("ew-password-strength");
        $this->ConfirmPassword = new DbField(UserTable(), "cpwd", "cpwd", "cpwd", "", 202, 255);
        $this->ConfirmPassword->EditAttrs->appendClass("form-control ew-form-control");
        if (Config("ENCRYPTED_PASSWORD")) {
            $this->OldPassword->Raw = true;
            $this->NewPassword->Raw = true;
            $this->ConfirmPassword->Raw = true;
        }

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

        // Check modal=>
        if ($this->IsModal) {
            $SkipHeaderFooter = true;
        }
        Breadcrumb()->add("change_password", "ChangePasswordPage", CurrentUrl(), "", "", true);
        $this->Heading = $this->language->phrase("ChangePasswordPage");
        $postBack = IsPost();
        $valid = true;
        if ($postBack) {
            $this->OldPassword->setFormValue(Post($this->OldPassword->FieldVar));
            $this->NewPassword->setFormValue(Post($this->NewPassword->FieldVar));
            $this->ConfirmPassword->setFormValue(Post($this->ConfirmPassword->FieldVar));
            $valid = $this->validateForm();
        }
        $pwdUpdated = false;
        $user = null;
        if ($postBack && $valid) {
            // Setup variables
            $userName = $this->security->currentUserName();
			$userEmail = ""; // Customization by Masino Sinaga, September 17, 2023
            if (IsPasswordReset()) {
                $userName = Session(SESSION_USER_PROFILE_USER_NAME);
				$userEmail = Session(SESSION_USER_PROFILE_USER_EMAIL); // Customization by Masino Sinaga, September 17, 2023, in order to support changing password based on Username AND Email (related/similar to the same customization in "MasinoForgotPassword25" extension)
            }
            if (IsPasswordExpired() && !IsEmpty(Session(SESSION_USER_PROFILE_USER_NAME))) {
                $userName = Session(SESSION_USER_PROFILE_USER_NAME);
				$userEmail = Session(SESSION_USER_PROFILE_USER_EMAIL); // Customization by Masino Sinaga, September 17, 2023, in order to support changing password based on Username AND Email (related/similar to the same customization in "MasinoForgotPassword25" extension)
            }

			// $filter = GetUserFilter(Config("LOGIN_USERNAME_FIELD_NAME"), $userName);			
			// Begin of customization by Masino Sinaga, September 17, 2023
			$filter = ""; // initial filter
			if ($userName <> "" && $userEmail <> "") { 
				// $filter = str_replace("%u", AdjustSql($userName, Config("USER_TABLE_DBID")), Config("USER_NAME_FILTER"));
				$filter = GetUserFilter(Config("LOGIN_USERNAME_FIELD_NAME"),  $userName);
				$filter .= " AND " . str_replace("%e", AdjustSql($userEmail, Config("USER_TABLE_DBID")), Config("USER_EMAIL_FILTER"));
			} elseif ($userName <> "" && $userEmail == "") {
				// $filter = str_replace("%u", AdjustSql($userName, Config("USER_TABLE_DBID")), Config("USER_NAME_FILTER"));
				$filter = GetUserFilter(Config("LOGIN_USERNAME_FIELD_NAME"),  $userName);
			} elseif ($userName == "" && $userEmail <> "") {
				$filter .= str_replace("%e", AdjustSql($userEmail, Config("USER_TABLE_DBID")), Config("USER_EMAIL_FILTER"));
			} else { // default: based on Username
				// $filter = str_replace("%u", AdjustSql($userName, Config("USER_TABLE_DBID")), Config("USER_NAME_FILTER"));
				$filter = GetUserFilter(Config("LOGIN_USERNAME_FIELD_NAME"),  $userName);
				//$filter .= str_replace("%e", AdjustSql($userEmail, Config("USER_TABLE_DBID")), Config("USER_EMAIL_FILTER"));
			} // End of customization by Masino Sinaga, September 17, 2023

            // Find user
            $user = UserRepository()->loadUserByIdentifier($userName);
            if ($user) {
                if (IsPasswordReset() || VerifyPassword($user->get(Config("LOGIN_PASSWORD_FIELD_NAME")), $this->OldPassword->CurrentValue)) {
                    $validPwd = true;
                    if (!IsPasswordReset()) {
                        $validPwd = $this->userChangePassword($user, $userName, $this->OldPassword->CurrentValue, $this->NewPassword->CurrentValue);
                    }
                    if ($validPwd) {
                        UserTable()->updateSql([Config("LOGIN_PASSWORD_FIELD_NAME") => $this->NewPassword->CurrentValue],
                            [Config("LOGIN_USERNAME_FIELD_NAME") => $user->getUserIdentifier()])
                            ->executeStatement();
                        $pwdUpdated = true;
                    } else {
                        $this->setFailureMessage($this->language->phrase("InvalidNewPassword"));
                    }
					// Validate password, by Masino Sinaga, September 17, 2023
					$validPassword = $this->validatePassword(Post($this->OldPassword->FieldVar), Post($this->NewPassword->FieldVar));
					if (!$validPassword) {
						return;
					}
                } else {
                    $this->setFailureMessage($this->language->phrase("InvalidPassword"));
                }
            }
        }
        if ($pwdUpdated) {
            $user = UserRepository()->loadUserByIdentifier($userName);
            if (!$this->peekSuccessMessage()) {
                $this->setSuccessMessage($this->language->phrase("PasswordChanged")); // Set up success message
            }
            if (IsPasswordReset()) {
                Session()->remove(SESSION_STATUS);
                Session()->remove(SESSION_USER_PROFILE_USER_NAME);
				Session()->remove(SESSION_USER_PROFILE_USER_EMAIL);
            }

            // Update user profile and login again
            Profile()->setUser($user)
                ->setLastPasswordChangedDate(StdCurrentDate())
                ->saveToStorage();
            if (IsPasswordExpired()) {
                if (Config("USE_TWO_FACTOR_AUTHENTICATION")) {
                    Session(SESSION_STATUS, "passwordchanged");
                    $this->terminate(UrlFor("logout")); // Logout and login again
                } else { // Login again
                    SecurityHelper()->login($user, "form_login");
                    $this->security->login();
                    $this->terminate(UrlFor("index"));
                }
                return;
            }
            $this->terminate(UrlFor("index")); // Return to default page
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
        $valid = true;
        if (!IsPasswordReset() && IsEmpty($this->OldPassword->CurrentValue)) {
            $this->OldPassword->addErrorMessage($this->language->phrase("EnterOldPassword"));
            $valid = false;
        }
        if (IsEmpty($this->NewPassword->CurrentValue)) {
            $this->NewPassword->addErrorMessage($this->language->phrase("EnterNewPassword"));
            $valid = false;
        }
        if (!$this->NewPassword->Raw && Config("REMOVE_XSS") && CheckPassword($this->NewPassword->CurrentValue)) {
            $this->NewPassword->addErrorMessage($this->language->phrase("InvalidPasswordChars"));
            $valid = false;
        }
        if ($this->NewPassword->CurrentValue != $this->ConfirmPassword->CurrentValue) {
            $this->ConfirmPassword->addErrorMessage($this->language->phrase("MismatchPassword"));
            $valid = false;
        }

        // Call Form CustomValidate event
        $formCustomError = "";
        $valid = $valid && $this->formCustomValidate($formCustomError);
        if ($formCustomError != "") {
            $this->setFailureMessage($formCustomError);
        }
        return $valid;
    }

	private function validatePassword($oldPassword, $newPassword) {
        global $Language, $FormError;

        // Check if validation required
        if (!Config("SERVER_VALIDATE"))
            return TRUE;

        // Initialize form error message
        $FormError = "";
        $validPassword = false;
		if (MS_ENABLE_PASSWORD_POLICY == TRUE) { 

			// Begin of modification Strong Password Policies/Rules, by Masino Sinaga, June 12, 2012
			if (MS_PASSWORD_MUST_COMPLY_WITH_MIN_LENGTH == TRUE) {
				if( strlen($newPassword) < MS_PASSWORD_MINIMUM_LENGTH ) {
					AddMessage($FormError, str_replace("%n", MS_PASSWORD_MINIMUM_LENGTH, $Language->phrase("ErrorPassTooShort")), "\r\n");
					$validPassword = false;
				}
			}
			if (MS_PASSWORD_MUST_COMPLY_WITH_MAX_LENGTH == TRUE) {
				if( strlen($newPassword) > MS_PASSWORD_MAXIMUM_LENGTH ) {
					AddMessage($FormError, str_replace("%n", MS_PASSWORD_MAXIMUM_LENGTH, $Language->phrase("ErrorPassTooLong")), "\r\n");
					$validPassword = false;
				}
			}
			if (MS_PASSWORD_MUST_CONTAIN_AT_LEAST_ONE_NUMERIC == TRUE) {
				if( !preg_match("#[0-9]+#", $newPassword) ) {
					AddMessage($FormError, $Language->phrase("ErrorPassDoesNotIncludeNumber"), "\r\n");
					$validPassword = false;
				}
			}
			if (MS_PASSWORD_MUST_CONTAIN_AT_LEAST_ONE_LOWERCASE == TRUE) {
				if( !preg_match("#[a-z]+#", $newPassword) ) {
					AddMessage($FormError, $Language->phrase("ErrorPassDoesNotIncludeLetter"), "\r\n");
					$validPassword = false;
				}
			}
			if (MS_PASSWORD_MUST_CONTAIN_AT_LEAST_ONE_UPPERCASE == TRUE) {
				if( !preg_match("#[A-Z]+#", $newPassword) ) {
					AddMessage($FormError, $Language->phrase("ErrorPassDoesNotIncludeCaps"), "\r\n");
					$validPassword = false;
				}
			}
			if (MS_PASSWORD_MUST_CONTAIN_AT_LEAST_ONE_SYMBOL == TRUE) {
				if( !preg_match("#\W+#", $newPassword) ) {
					AddMessage($FormError, $Language->phrase("ErrorPassDoesNotIncludeSymbol"), "\r\n");
					$validPassword = false;
				}
			}
			if (MS_PASSWORD_MUST_DIFFERENT_OLD_AND_NEW == TRUE) {
				if ($oldPassword == $newPassword) {
					AddMessage($FormError, $Language->phrase("ErrorPassCouldNotBeSame"), "\r\n");
					$validPassword = false;
				}
			}
			// End of modification Strong Password Policies/Rules, by Masino Sinaga, June 12, 2012
		} 
		if (!empty($FormError)) {
			$this->setFailureMessage($FormError);
			$validPassword = false;
		}
        // Return validate result
        $validPassword = ($FormError == "");
        return $validPassword;
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

    // User ChangePassword event
    public function userChangePassword(UserInterface $user, string $userName, string $oldPassword, string &$newPassword): bool
    {
        // Return false to abort
        return true;
    }
}
