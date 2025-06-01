<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;
use Exception;
use Throwable;

/**
 * Advanced Security class
 */
class AdvancedSecurity
{
    use TargetPathTrait;
    protected bool $hierarchyLoaded = false;

    // User level contants
    public const ANONYMOUS_USER_LEVEL_ID = -2;
    public const ADMIN_USER_LEVEL_ID = -1;
    public const DEFAULT_USER_LEVEL_ID = 0;

    // User ID constant
    public const ADMIN_USER_ID = -1;

    // For all users
    public array $UserLevels = []; // All User Levels
    public array $UserLevelPrivs = []; // All User Level permissions

    // Current user
    public array $UserLevelIDs = []; // User Level ID array
    public array $UserIDs = []; // User ID array
    public array $ParentUserIDs = []; // Parent User ID array
    public int $CurrentUserLevel = 0; // Permissions
    public int|string $CurrentUserLevelID = self::ANONYMOUS_USER_LEVEL_ID; // User Level (Anonymous by default)
    public mixed $CurrentUserID = null;
    public mixed $CurrentUserPrimaryKey = null;
    protected bool $anoymousUserLevelChecked = false; // Dynamic User Level security
    protected bool $isLoggedIn = false;
    protected bool $isSysAdmin = false;
    protected string $userName = "";

    // Constructor
    public function __construct(
        protected Language $language,
        protected SessionInterface $session
    ) {
        global $Security;
        $Security = $this;

        // Init User Level
        $this->CurrentUserLevelID = $this->isLoggedIn()
            ? $this->sessionUserLevelID() // Load from session
            : self::ANONYMOUS_USER_LEVEL_ID; // Anonymous user
        $this->setUserLevelID($this->CurrentUserLevelID);
        $this->session->set(SESSION_USER_LEVEL_LIST, $this->userLevelList());

        // Load user ID, Parent User ID and primary key
        $this->setCurrentUserID($this->sessionUserID());
        $this->setParentUserID($this->sessionParentUserID());
        $this->setCurrentUserPrimaryKey($this->sessionUserPrimaryKey());

        // Load user level
        $this->loadUserLevel();
    }

    /**
     * User ID
     */

    // Get session User ID
    protected function sessionUserID(): mixed
    {
        return $this->session->has(SESSION_USER_ID) ? strval($this->session->get(SESSION_USER_ID)) : $this->CurrentUserID;
    }

    // Set session User ID
    protected function setSessionUserID(mixed $v): void
    {
        $this->CurrentUserID = trim(strval($v));
        $this->session->set(SESSION_USER_ID, $this->CurrentUserID);
    }

    // Current User ID
    public function currentUserID(): mixed
    {
        return $this->CurrentUserID;
    }

    // Set current User ID
    public function setCurrentUserID(mixed $v): void
    {
        $this->CurrentUserID = trim(strval($v));
    }

    /**
     * Parent User ID
     */

    // Get session Parent User ID
    protected function sessionParentUserID(): mixed
    {
        return $this->session->has(SESSION_PARENT_USER_ID) ? strval($this->session->get(SESSION_PARENT_USER_ID)) : $this->getParentUserID();
    }

    // Set session Parent User ID
    protected function setSessionParentUserID(mixed $v): void
    {
        $this->setParentUserID($v);
        $this->session->set(SESSION_PARENT_USER_ID, $this->getParentUserID());
    }

    // Set Parent User ID to array
    public function setParentUserID(mixed $v): void
    {
        $ids = is_array($v) ? $v : explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($v));
        $this->ParentUserIDs = [];
        foreach ($ids as $id) {
            $this->addParentUserID($id);
        }
    }

    // Get Parent User ID
    public function getParentUserID(): mixed
    {
        return implode(Config("MULTIPLE_OPTION_SEPARATOR"), $this->ParentUserIDs);
    }

    // Check if Parent User ID in array
    public function hasParentUserID(mixed $v): bool
    {
        $ids = is_array($v) ? $v : explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($v));
        return array_any($ids, fn($id) => in_array($id, $this->ParentUserID));
    }

    // Current Parent User ID
    public function currentParentUserID(): mixed
    {
        return $this->getParentUserID();
    }

    /**
     * User Level ID
     */

    // Get session User Level ID
    protected function sessionUserLevelID(): int|string
    {
        return $this->session->get(SESSION_USER_LEVEL_ID) ?? $this->CurrentUserLevelID;
    }

    // Set session User Level ID
    protected function setSessionUserLevelID(int|string $v): void
    {
        $this->setCurrentUserLevelID($v);
        $this->session->set(SESSION_USER_LEVEL_ID, $this->CurrentUserLevelID);
    }

    // Current User Level ID
    public function currentUserLevelID(): int|string
    {
        return $this->CurrentUserLevelID;
    }

    // Set current User Level ID
    public function setCurrentUserLevelID(int|string $v): void
    {
        $this->CurrentUserLevelID = $v;
        $this->setUserLevelID($v);
    }

    // Set Hierarchy
    protected function setHierarchy(int|string $v): void
    {
        if (!$this->hierarchyLoaded) { // Load only once
            $this->UserLevelIDs = array_unique(array_merge($this->UserLevelIDs, $this->getAllUserLevelsFromHierarchy($v)));
            $this->hierarchyLoaded = true;
        }
    }

    /**
     * User Level (Permissions)
     */

    // Get session User Level
    protected function sessionUserLevel(): int
    {
        return $this->session->has(SESSION_USER_LEVEL) ? (int)$this->session->get(SESSION_USER_LEVEL) : $this->CurrentUserLevel;
    }

    // Set session User Level
    protected function setSessionUserLevel(int $v): void
    {
        $this->CurrentUserLevel = $v;
        $this->session->set(SESSION_USER_LEVEL, $this->CurrentUserLevel);
    }

    // Current User Level value
    public function currentUserLevel(): int
    {
        return $this->CurrentUserLevel;
    }

    /**
     * User name
     */

    // Get current user name
    public function getCurrentUserName(): string
    {
        return $this->session->has(SESSION_USER_NAME) ? strval($this->session->get(SESSION_USER_NAME)) : $this->userName;
    }

    // Set current user name
    public function setCurrentUserName(string $v): void
    {
        $this->userName = $v;
        $this->session->set(SESSION_USER_NAME, $this->userName);
    }

    // Get current user name (alias)
    public function currentUserName(): string
    {
        return $this->getCurrentUserName();
    }

    /**
     * User primary key
     */

    // Get session user primary key
    protected function sessionUserPrimaryKey(): mixed
    {
        return $this->session->has(SESSION_USER_PRIMARY_KEY) ? strval($this->session->get(SESSION_USER_PRIMARY_KEY)) : $this->CurrentUserPrimaryKey;
    }

    // Set session user primary key
    protected function setSessionUserPrimaryKey(mixed $v): void
    {
        $this->setCurrentUserPrimaryKey($v);
        $this->session->set(SESSION_USER_PRIMARY_KEY, $this->CurrentUserPrimaryKey);
    }

    // Get current user primary key
    public function currentUserPrimaryKey(): mixed
    {
        return $this->CurrentUserPrimaryKey;
    }

    // Set current user primary key
    public function setCurrentUserPrimaryKey(mixed $v): void
    {
        $this->CurrentUserPrimaryKey = $v;
    }

    /**
     * Other methods
     */

    // Set User Level ID to array
    public function setUserLevelID(int|string|array $v): void
    {
        $ids = is_array($v) ? $v : explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($v));
        $this->UserLevelIDs = [];
        foreach ($ids as $id) {
            if ((int)$id >= self::ANONYMOUS_USER_LEVEL_ID) {
                $this->UserLevelIDs[] = (int)$id;
            }
        }
    }

    // Check if User Level ID in array
    public function hasUserLevelID(int|string|array|null $v): bool
    {
        $ids = is_array($v) ? $v : explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($v));
        return array_any($ids, fn($id) => in_array((int)$id, $this->UserLevelIDs));
    }

    // Get JWT Token
    public function createJwt(int $expiry = 0, int $permission = 0): string
    {
        return CreateJwt([
            "username" => $this->currentUserName(),
            "userid" => $this->currentUserID(),
            "parentuserid" => $this->currentParentUserID(),
            "userlevel" => $this->currentUserLevelID(),
            "userprimarykey" => $this->currentUserPrimaryKey(),
            "userPermission" => $permission
        ], $expiry);
    }

    // Can add
    public function canAdd(): bool
    {
        return ($this->CurrentUserLevel & Allow::ADD->value) == Allow::ADD->value;
    }

    // Set can add
    public function setCanAdd(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::ADD->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::ADD->value);
        }
    }

    // Can delete
    public function canDelete(): bool
    {
        return ($this->CurrentUserLevel & Allow::DELETE->value) == Allow::DELETE->value;
    }

    // Set can delete
    public function setCanDelete(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::DELETE->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::DELETE->value);
        }
    }

    // Can edit
    public function canEdit(): bool
    {
        return ($this->CurrentUserLevel & Allow::EDIT->value) == Allow::EDIT->value;
    }

    // Set can edit
    public function setCanEdit(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::EDIT->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::EDIT->value);
        }
    }

	// Can access (View all records)
    public function canAccess(): bool
    {
        return ($this->CurrentUserLevel & Allow::ACCESS->value) == Allow::ACCESS->value;
    }

    // Set can access
    public function setCanAccess(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::ACCESS->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::ACCESS->value);
        }
    }

    // Can view
    public function canView(): bool
    {
        return ($this->CurrentUserLevel & Allow::VIEW->value) == Allow::VIEW->value;
    }

    // Set can view
    public function setCanView(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::VIEW->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::VIEW->value);
        }
    }

    // Can list
    public function canList(): bool
    {
        return ($this->CurrentUserLevel & Allow::LIST->value) == Allow::LIST->value;
    }

    // Set can list
    public function setCanList(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::LIST->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::LIST->value);
        }
    }

    // Can search
    public function canSearch(): bool
    {
        return ($this->CurrentUserLevel & Allow::SEARCH->value) == Allow::SEARCH->value;
    }

    // Set can search
    public function setCanSearch(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::SEARCH->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::SEARCH->value);
        }
    }

    // Can admin
    public function canAdmin(): bool
    {
        return ($this->CurrentUserLevel & Allow::ADMIN->value) == Allow::ADMIN->value;
    }

    // Set can admin
    public function setCanAdmin(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::ADMIN->value;
        } else {
            // $this->CurrentUserLevel &= ~(Allow::ADMIN->value);
            throw new \Nette\NotSupportedException('setCanAdmin(false) is not supported.');
        }
    }

	// Can ALL_NEW
    public function canAllNew(): bool
    {
        return ($this->CurrentUserLevel & Allow::ALL_NEW->value) == Allow::ALL_NEW->value;
    }

    // Set can ALL_NEW
    public function setCanAllNew(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::ALL_NEW->value;
        } else {
            // $this->CurrentUserLevel &= ~(Allow::ALL_NEW->value);
            throw new \Nette\NotSupportedException('setCanAllNew(false) is not supported.');
        }
    }

    // Can grant
    public function canGrant(): bool
    {
        return ($this->CurrentUserLevel & Allow::GRANT->value) == Allow::GRANT->value;
    }

    // Set can grant
    public function setCanGrant(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::GRANT->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::GRANT->value);
        }
    }

    // Can import
    public function canImport(): bool
    {
        return ($this->CurrentUserLevel & Allow::IMPORT->value) == Allow::IMPORT->value;
    }

    // Set can import
    public function setCanImport(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::IMPORT->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::IMPORT->value);
        }
    }

    // Can lookup
    public function canLookup(): bool
    {
        return ($this->CurrentUserLevel & Allow::LOOKUP->value) == Allow::LOOKUP->value;
    }

    // Set can lookup
    public function setCanLookup(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::LOOKUP->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::LOOKUP->value);
        }
    }

    // Can push
    public function canPush(): bool
    {
        return ($this->CurrentUserLevel & Allow::PUSH->value) == Allow::PUSH->value;
    }

    // Set can push
    public function setCanPush(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::PUSH->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::PUSH->value);
        }
    }

    // Can export
    public function canExport(): bool
    {
        return ($this->CurrentUserLevel & Allow::EXPORT->value) == Allow::EXPORT->value;
    }

    // Set can push
    public function setCanExport(bool $b): void
    {
        if ($b) {
            $this->CurrentUserLevel |= Allow::EXPORT->value;
        } else {
            $this->CurrentUserLevel &= ~(Allow::EXPORT->value);
        }
    }

	// Begin of modification Permission Access for Export To Feature, by Masino Sinaga, September 11, 2023
    // Can export to Print
    public function canExportToPrint() {
        return (($this->CurrentUserLevel & Allow::PRINT->value) == Allow::PRINT->value || IsAdmin());
    }

    public function setCanExportToPrint($b) {
        if ($b) {
            $this->CurrentUserLevel |= Allow::PRINT->value;
        } else {
            $this->CurrentUserLevel ^= ~Allow::PRINT->value;
        }
    }

    // Can export to HTML
    public function canExportToHTML() {
        return (($this->CurrentUserLevel & Allow::HTML->value) == Allow::HTML->value || IsAdmin());
    }

    public function setCanExportToHTML($b) {
        if ($b) {
            $this->CurrentUserLevel |= Allow::HTML->value;
        } else {
            $this->CurrentUserLevel ^= ~Allow::HTML->value;
        }
    } 

    // Can export to Excel
    public function canExportToExcel() {
        return (($this->CurrentUserLevel & Allow::EXCEL->value) == Allow::EXCEL->value || IsAdmin());
    }

    public function setCanExportToExcel($b) {
        if ($b) {
            $this->CurrentUserLevel |= Allow::EXCEL->value;
        } else {
            $this->CurrentUserLevel ^= ~Allow::EXCEL->value;
        }
    }

    // Can export to Word
    public function canExportToWord() {
        return (($this->CurrentUserLevel & Allow::WORD->value) == Allow::WORD->value || IsAdmin());
    }

    public function setCanExportToWord($b) {
        if ($b) {
            $this->CurrentUserLevel |= Allow::WORD->value;
        } else {
            $this->CurrentUserLevel ^= ~Allow::WORD->value;
        }
    }     

    // Can export to PDF
    public function canExportToPDF() {
        return (($this->CurrentUserLevel & Allow::PDF->value) == Allow::PDF->value || IsAdmin());
    }

    public function setCanExportToPDF($b) {
        if ($b) {
            $this->CurrentUserLevel |= Allow::PDF->value;
        } else {
            $this->CurrentUserLevel ^= ~Allow::PDF->value;
        }
    }     

    // Can export to XML
    public function canExportToXML() {
        return (($this->CurrentUserLevel & Allow::XML->value) == Allow::XML->value || IsAdmin());
    }

    public function setCanExportToXML($b) {
        if ($b) {
            $this->CurrentUserLevel |= Allow::XML->value;
        } else {
            $this->CurrentUserLevel ^= ~Allow::XML->value;
        }
    }     

    // Can export to CSV
    public function canExportToCSV() {
        return (($this->CurrentUserLevel & Allow::CSV->value) == Allow::CSV->value || IsAdmin());
    }

    public function setCanExportToCSV($b) {
        if ($b) {
            $this->CurrentUserLevel |= Allow::CSV->value;
        } else {
            $this->CurrentUserLevel ^= ~Allow::CSV->value;
        }
    }     

    // Can export to Email
    public function canExportToEmail() {
        return (($this->CurrentUserLevel & Allow::EMAIL->value) == Allow::EMAIL->value || IsAdmin());
    }

    public function setCanExportToEmail($b) {
        if ($b) {
            $this->CurrentUserLevel |= Allow::EMAIL->value;
        } else {
            $this->CurrentUserLevel ^= ~Allow::EMAIL->value;
        }
    }     
	// End of modification Permission Access for Export To Feature, by Masino Sinaga, September 11, 2023

    // Can switch user
    public function canSwitchUser(): bool
    {
        return $this->isAdmin() || count($this->UserIDs) > 1;
    }

    // Last URL
    public function lastUrl(): ?string
    {
        return $this->getTargetPath($this->session, "main");
    }

    // Save last URL
    public function saveLastUrl(): void
    {
        $request = Request();
        $url = (string)$request->getUri();
        if ($this->lastUrl() == $url) {
            $url = "";
        }
        if (!IsModal()) {
            $this->saveTargetPath($this->session, "main", $url);
        }
    }

    // Remove last URL
    public function removeLastUrl(): void
    {
        $this->removeTargetPath($this->session, "main");
    }

    // Login current user
    public function login(): bool
    {
        $valid = false;
        if ($user = SecurityHelper()?->getUser()) {
            $valid = $this->validateUser($user->getUserIdentifier());
        }
        return $valid;
    }

    // Login user
    public function loginUser(AdvancedUserInterface $user): void
    {
        $userLevel = $user->userLevel();
        $userLevels = explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($userLevel));
        if (($userName = $user->userName()) != "") {
            $this->setCurrentUserName($userName);
            $this->isSysAdmin = $this->validateSysAdmin($userName);
            if ($this->isSysAdmin) {
                $this->session->set(SESSION_SYS_ADMIN, 1); // System administrator
            }
            if (count(array_filter($userLevels, fn($id) => (int)$id > AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID)) > 0) {
                $this->isLoggedIn = true;
                $this->session->set(SESSION_STATUS, "login");
            }
        }
        if (($userId = $user->userId()) !== null) {
            $this->setSessionUserID($userId);
        }
        if (($parentUserId = $user->parentUserId()) !== null) {
            $this->setSessionParentUserID($parentUserId);
        }
        if (count(array_filter($userLevels, fn($id) => (int)$id >= AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID)) > 0) {
            $this->setSessionUserLevelID($userLevel);
            $this->setupUserLevel();
        }
        if ($user instanceof AbstractEntity && ($id = $user->id()) !== null) {
            $this->setSessionUserPrimaryKey($id);
        }
    }

    // Logout user
    public function logout(): void
    {
        $this->isLoggedIn = false;
        $this->session->remove(SESSION_STATUS);
        $this->setCurrentUserName("");
        $this->setSessionUserID(null);
        $this->setSessionParentUserID([]);
        $this->setSessionUserLevelID(self::ANONYMOUS_USER_LEVEL_ID);
        $this->setSessionUserPrimaryKey(null);
        $this->setupUserLevel();
        Container("user.profile", new UserProfile());
    }

    /**
     * Validate user
     *
     * @param string $usr User name
     * @param string $pwd Password
     * @return bool
     */
    public function validateUser(string $usr, string $pwd = ""): bool
    {
        $valid = false;
        $customValid = IsAuthenticated(); // Is authenticated by Symfony security or not
        $user = CurrentUser();
        $profile = Profile();

        // API login
        if (IsApi() && $user === null) {
            if ($this->validateSysAdmin($usr, $pwd, false)) { // Validate super admin
                $profile->setUserName($usr)
                    ->setUserID(AdvancedSecurity::ADMIN_USER_ID)
                    ->setUserLevel(AdvancedSecurity::ADMIN_USER_LEVEL_ID);
                $this->loginUser($profile);
                return true;
            } elseif ($this->validateEntityUser($usr, $pwd, false)) { // Validate non-admin user
                $user = LoadUserByIdentifier($usr);
				$this->loginUser($user);
                return true;
            }
            return false;
        }

        // Check database users
        if (!$valid) {
            if (IsEntityUser($user)) {
                $valid = true;
                $this->isLoggedIn = true;
                $this->isSysAdmin = false;
                $this->session->set(SESSION_STATUS, "login");
                $this->session->set(SESSION_SYS_ADMIN, 0); // Non system administrator
                $this->loginUser($user);

                // Call User Validated event
                $this->userValidated($user);
            }

            // Set up user language
            if ($langId = $profile->getLanguageId()) {
                $this->language->setLanguage($langId);
            }
        }

        // Super admin
        if (IsSysAdminUser($user)) {
            $profile->setUserName($user->getUserIdentifier())
                ->setUserID(AdvancedSecurity::ADMIN_USER_ID)
                ->setUserLevel(AdvancedSecurity::ADMIN_USER_LEVEL_ID);
            $this->loginUser($profile);

            // Call User Validated event
            $this->userValidated($user);
        }

        // Use User_Validated to set privileges to profile if user not found
        if (!$valid && $customValid && !IsLoggedIn()) {
            $this->loginUser($profile);
            $this->userValidated($profile);
        }
        if (!$valid && !$customValid && !$this->isPasswordExpired()) {
            $this->isLoggedIn = false;
            $this->session->remove(SESSION_STATUS); // Clear login status
        }
        return $valid || $customValid;
    }

    // Clear Remember Me cookie
    public function clearRememberMeCookie(): void
    {
        global $SetCookies;
        $SetCookies = $SetCookies->with(SetCookie::create(Config("SECURITY.firewalls.main.remember_me.name"))
            ->withPath(Config("COOKIE_PATH"))
            ->expire());
    }

    // Validate system admin
    private function validateSysAdmin(string $username, string $password = "", bool $checkUsernameOnly = true): bool
    {
        $adminUsername = Config("ADMIN_USER_NAME");
        if ($checkUsernameOnly) {
            return $adminUsername === $username;
        } else {
            $hash = Config("SECURITY.providers.admin_user.memory.users.{$adminUsername}.password") ?? null;
            return $adminUsername === $username && GetPasswordHasher(InMemoryUser::class)->verify($hash, $password);
        }
    }

    // Validate entity user
    private function validateEntityUser(PasswordAuthenticatedUserInterface|string $user, string $password = "", bool $checkUsernameOnly = true): bool
    {
        $user = is_string($user) ? LoadUserByIdentifier($user) : $user;
        if ($user === null) {
            return false;
        }
        if (Config("REGISTER_ACTIVATE") && Config("USER_ACTIVATED_FIELD_NAME") && !ConvertToBool($user->get(Config("USER_ACTIVATED_FIELD_NAME")))) {
            return false;
        }
        if ($checkUsernameOnly) {
            return true;
        }
        return VerifyPassword($user->getPassword(), $password);
    }

    // Get User Level settings from storage
    public function setupUserLevel(): void
    {
        $this->loadFromStorage(); // Load all user levels

        // User Level loaded event
        $this->userLevelLoaded();

        // Save the User Level to session variable
        $this->saveUserLevel();
    }

    // Get all User Level settings from database
    public function loadFromStorage(): bool
    {
        global $USER_LEVELS, $USER_LEVEL_PRIVS, $USER_LEVEL_TABLES;

        // Load from user level settings first
        $this->UserLevels = $USER_LEVELS;
        $this->UserLevelPrivs = $USER_LEVEL_PRIVS;

        // Add Anonymous user level
        $conn = Conn(Config("USER_LEVEL_DBID"));
        if (!$this->anoymousUserLevelChecked) {
            $sql = "SELECT COUNT(*) FROM " . Config("USER_LEVEL_TABLE") . " WHERE " . Config("USER_LEVEL_ID_FIELD") . " = " . self::ANONYMOUS_USER_LEVEL_ID;
            if (ExecuteScalar($sql, $conn) == 0) {
                $sql = "INSERT INTO " . Config("USER_LEVEL_TABLE") .
                    " (" . Config("USER_LEVEL_ID_FIELD") . ", " . Config("USER_LEVEL_NAME_FIELD") . ") VALUES (" . self::ANONYMOUS_USER_LEVEL_ID . ", '" . AdjustSql($this->language->phrase("UserAnonymous"), Config("USER_LEVEL_DBID")) . "')";
                $conn->executeStatement($sql);
            }
        }

        // Get the User Level definitions
        $sql = "SELECT " . Config("USER_LEVEL_ID_FIELD") . ", " . Config("USER_LEVEL_NAME_FIELD") . ", " . Config("USER_LEVEL_HIERARCHY_FIELD") . " FROM " . Config("USER_LEVEL_TABLE");
        $this->UserLevels = $conn->fetchAllNumeric($sql);

        // Add Anonymous user privileges
        $conn = Conn(Config("USER_LEVEL_PRIV_DBID"));
        if (!$this->anoymousUserLevelChecked) {
            $sql = "SELECT COUNT(*) FROM " . Config("USER_LEVEL_PRIV_TABLE") . " WHERE " . Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . " = " . self::ANONYMOUS_USER_LEVEL_ID;
            if (ExecuteScalar($sql, $conn) == 0) {
                $wrkUserLevel = $USER_LEVELS;
                $wrkUserLevelPriv = $USER_LEVEL_PRIVS;
                foreach ($USER_LEVEL_TABLES as $table) {
                    $wrkPriv = 0;
                    foreach ($wrkUserLevelPriv as $userpriv) {
                        if (@$userpriv[0] == @$table[4] . @$table[0] && @$userpriv[1] == self::ANONYMOUS_USER_LEVEL_ID) {
                            $wrkPriv = @$userpriv[2];
                            break;
                        }
                    }
                    $sql = "INSERT INTO " . Config("USER_LEVEL_PRIV_TABLE") .
                        " (" . Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . ", " . Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . ", " . Config("USER_LEVEL_PRIV_PRIV_FIELD") .
                        ") VALUES (" . self::ANONYMOUS_USER_LEVEL_ID . ", '" . AdjustSql(@$table[4] . @$table[0]) . "', " . $wrkPriv . ")";
                    $conn->executeStatement($sql);
                }
            }
            $this->anoymousUserLevelChecked = true;
        }

        // Get the User Level privileges
        $userPrivSql = "SELECT " . Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . ", " . Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . ", " . Config("USER_LEVEL_PRIV_PRIV_FIELD") . " FROM " . Config("USER_LEVEL_PRIV_TABLE");
        if (!IsApi() && !$this->isAdmin() && !$this->canGrant() && count($this->UserLevelIDs) > 0 && Config("USER_LEVEL_HIERARCHY_FIELD") == "''") { // Not grant permission and no hierarchy field
            $userPrivSql .= " WHERE " . Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . " IN (" . $this->userLevelList() . ")";
            $this->session->set(SESSION_USER_LEVEL_LIST_LOADED, $this->userLevelList()); // Save last loaded list
        } else {
            $this->session->remove(SESSION_USER_LEVEL_LIST_LOADED); // Save last loaded list
        }
        $this->UserLevelPrivs = $conn->fetchAllNumeric($userPrivSql);

        // Update User Level privileges record if necessary
        $projectID = CurrentProjectID();
        $relatedProjectID = Config("RELATED_PROJECT_ID");
        $reloadUserPriv = 0;

        // Update tables for related project
        if ($relatedProjectID) {
            $sql = "SELECT COUNT(*) FROM " . Config("USER_LEVEL_PRIV_TABLE") . " WHERE EXISTS(SELECT * FROM " .
                Config("USER_LEVEL_PRIV_TABLE") . " WHERE " . Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " LIKE '" . AdjustSql($relatedProjectID) . "%')";
            if (ExecuteScalar($sql, $conn) > 0) {
                $ar = array_map(fn($t) => "'" . AdjustSql($relatedProjectID . $t[0]) . "'", $USER_LEVEL_TABLES);
                $sql = "UPDATE " . Config("USER_LEVEL_PRIV_TABLE") . " SET " .
                    Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " = " . $conn->getDatabasePlatform()->getConcatExpression("'" . AdjustSql($projectID) . "'", Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD")) . " WHERE " .
                    Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " IN (" . implode(",", $ar) . ")";
                $reloadUserPriv += $conn->executeStatement($sql);
            }
        }

        // Reload the User Level privileges
        if ($reloadUserPriv) {
            $this->UserLevelPrivs = $conn->fetchAllNumeric($userPrivSql);
        }

        // Throw error if user level not setup
        if (count($this->UserLevelPrivs) == 0) {
            throw new Exception($this->language->phrase("NoUserLevel"));
        }
        return true;
    }

    // Update user level permissions
    public function updatePermissions($userLevel, $privs): void
    {
        $c = Conn(Config("USER_LEVEL_PRIV_DBID"));
        foreach ($privs as $table => $priv) {
            if (is_numeric($priv)) {
                $sql = "SELECT * FROM " . Config("USER_LEVEL_PRIV_TABLE") . " WHERE " .
                    Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " = '" . AdjustSql($table) . "' AND " .
                    Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . " = " . $userLevel;
                if ($c->fetchAssociative($sql)) {
                    $sql = "UPDATE " . Config("USER_LEVEL_PRIV_TABLE") . " SET " . Config("USER_LEVEL_PRIV_PRIV_FIELD") . " = " . $priv . " WHERE " .
                        Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . " = '" . AdjustSql($table) . "' AND " .
                        Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . " = " . $userLevel;
                    $c->executeStatement($sql);
                } else {
                    $sql = "INSERT INTO " . Config("USER_LEVEL_PRIV_TABLE") . " (" . Config("USER_LEVEL_PRIV_TABLE_NAME_FIELD") . ", " . Config("USER_LEVEL_PRIV_USER_LEVEL_ID_FIELD") . ", " . Config("USER_LEVEL_PRIV_PRIV_FIELD") . ") VALUES ('" . AdjustSql($table) . "', " . $userLevel . ", " . $priv . ")";
                    $c->executeStatement($sql);
                }
            }
        }
    }

    // Set user permissions
    public function setUserPermissions(Allow|string|int $userPermission = 0): void
    {
        $permission = GetPrivilege($userPermission);
        if ($permission > 0) {
            foreach ($this->UserLevelPrivs as &$row) {
                $priv = &$row[2];
                if (is_numeric($priv)) {
                    $priv &= $permission;
                }
            }
        }
    }

    // Add user permission
    protected function addUserPermissionEx(string $userLevelName, string $tableName, Allow|string|int $userPermission): void
    {
        // Get User Level ID from user name
        $userLevelID = "";
        $permission = GetPrivilege($userPermission);
        foreach ($this->UserLevels as $row) {
            [$levelid, $name, $hierarchy] = $row;
            if (SameText($userLevelName, $name)) {
                $userLevelID = $levelid;
                break;
            }
        }
        if ($userLevelID != "") {
            $cnt = count($this->UserLevelPrivs);
            for ($i = 0; $i < $cnt; $i++) {
                list($table, $levelid, $priv) = $this->UserLevelPrivs[$i];
                if (SameText($table, PROJECT_ID . $tableName) && SameString($levelid, $userLevelID)) {
                    $this->UserLevelPrivs[$i][2] = $priv | $permission; // Add permission
                    return;
                }
            }
            // Add new entry
            $this->UserLevelPrivs[] = [PROJECT_ID . $tableName, $userLevelID, $permission];
        }
    }

    // Add user permission
    public function addUserPermission(string $userLevelName, string $tableName, Allow|string|int $userPermission): void
    {
        $arUserLevelName = is_array($userLevelName) ? $userLevelName : [$userLevelName];
        $arTableName = is_array($tableName) ? $tableName : [$tableName];
        foreach ($arUserLevelName as $userLevelName) {
            foreach ($arTableName as $tableName) {
                $this->addUserPermissionEx($userLevelName, $tableName, $userPermission);
            }
        }
    }

    // Delete user permission
    protected function deleteUserPermissionEx(string $userLevelName, string $tableName, Allow|string|int $userPermission): void
    {
        // Get User Level ID from user name
        $userLevelID = "";
        $permission = GetPrivilege($userPermission);
        foreach ($this->UserLevels as $row) {
            [$levelid, $name, $hierarchy] = $row;
            if (SameText($userLevelName, $name)) {
                $userLevelID = $levelid;
                break;
            }
        }
        if ($userLevelID != "") {
            $cnt = count($this->UserLevelPrivs);
            for ($i = 0; $i < $cnt; $i++) {
                list($table, $levelid, $priv) = $this->UserLevelPrivs[$i];
                if (SameText($table, PROJECT_ID . $tableName) && SameString($levelid, $userLevelID)) {
                    $this->UserLevelPrivs[$i][2] = $priv & ~$permission; // Remove permission
                    break;
                }
            }
        }
    }

    // Delete user permission
    public function deleteUserPermission(string $userLevelName, string $tableName, Allow|string|int $userPermission): void
    {
        $arUserLevelName = is_array($userLevelName) ? $userLevelName : [$userLevelName];
        $arTableName = is_array($tableName) ? $tableName : [$tableName];
        foreach ($arUserLevelName as $userLevelName) {
            foreach ($arTableName as $tableName) {
                $this->deleteUserPermissionEx($userLevelName, $tableName, $userPermission);
            }
        }
    }

    // Load table permissions
    public function loadTablePermissions(string $tblVar): void
    {
        $this->setHierarchy($this->CurrentUserLevelID);
        $tblName = GetTableName($tblVar);
        if ($this->isLoggedIn() && method_exists($this, "tablePermissionLoading")) {
            $this->tablePermissionLoading();
        }
        $this->loadCurrentUserLevel(PROJECT_ID . $tblName);
        if ($this->isLoggedIn() && method_exists($this, "tablePermissionLoaded")) {
            $this->tablePermissionLoaded();
        }
        if ($this->isLoggedIn()) {
            if (method_exists($this, "userIDLoading")) {
                $this->userIDLoading();
            }
            if (method_exists($this, "loadUserID")) {
                $this->loadUserID();
            }
            if (method_exists($this, "userIDLoaded")) {
                $this->userIDLoaded();
            }
        }
    }

    // Load current User Level
    public function loadCurrentUserLevel(string $table): void
    {
        // Load again if user level list changed
        if ($this->session->get(SESSION_USER_LEVEL_LIST_LOADED) != "" && $this->session->get(SESSION_USER_LEVEL_LIST_LOADED) != $this->session->get(SESSION_USER_LEVEL_LIST)) {
            $this->session->remove(SESSION_USER_LEVEL_PRIVS);
        }
        $this->loadUserLevel();
        $this->setSessionUserLevel($this->currentUserLevelPriv($table));
    }

    // Get current user privilege
    protected function currentUserLevelPriv(string $tableName): int
    {
        if ($this->isLoggedIn()) {
            $priv = 0;
            foreach ($this->UserLevelIDs as $userLevelID) {
                $priv |= $this->getUserLevelPrivEx($tableName, $userLevelID);
            }
            return $priv;
        } else { // Anonymous
            return $this->getUserLevelPrivEx($tableName, self::ANONYMOUS_USER_LEVEL_ID);
        }
    }

    // Get User Level ID by User Level name
    public function getUserLevelID(string $userLevelName): int
    {
        if (SameString($userLevelName, "Anonymous")) {
            return self::ANONYMOUS_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, $this->language->phrase("UserAnonymous"))) {
            return self::ANONYMOUS_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, "Administrator")) {
            return self::ADMIN_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, $this->language->phrase("UserAdministrator"))) {
            return self::ADMIN_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, "Default")) {
            return self::DEFAULT_USER_LEVEL_ID;
        } elseif (SameString($userLevelName, $this->language->phrase("UserDefault"))) {
            return self::DEFAULT_USER_LEVEL_ID;
        } elseif ($userLevelName != "") {
            foreach ($this->UserLevels as $row) {
                [$levelid, $name, $hierarchy] = $row;
                if (SameString($name, $userLevelName)) {
                    return $levelid;
                }
            }
        }
        return self::ANONYMOUS_USER_LEVEL_ID; // Anonymous
    }

    // Add User Level by name
    public function addUserLevel(string $userLevelName): void
    {
        $this->addUserLevelID($this->getUserLevelID($userLevelName));
    }

    // Add User Level by ID
    public function addUserLevelID(int $userLevelID): void
    {
        if ($userLevelID < self::ADMIN_USER_LEVEL_ID) {
            return;
        }
        if (!in_array($userLevelID, $this->UserLevelIDs)) {
            $this->UserLevelIDs[] = $userLevelID;
            $this->session->set(SESSION_USER_LEVEL_LIST, $this->userLevelList()); // Update session variable
        }
    }

    // Delete User Level by name
    public function deleteUserLevel(string $userLevelName): void
    {
        $this->deleteUserLevelID($this->getUserLevelID($userLevelName));
    }

    // Delete User Level by ID
    public function deleteUserLevelID(int $userLevelID): void
    {
        if ($userLevelID < self::ADMIN_USER_LEVEL_ID) {
            return;
        }
        $cnt = count($this->UserLevelIDs);
        for ($i = 0; $i < $cnt; $i++) {
            if ($this->UserLevelIDs[$i] == $userLevelID) {
                unset($this->UserLevelIDs[$i]);
                $this->session->set(SESSION_USER_LEVEL_LIST, $this->userLevelList()); // Update session variable
                break;
            }
        }
    }

    // User Level list
    public function userLevelList(): string
    {
        return implode(", ", $this->UserLevelIDs);
    }

    // User level ID exists
    public function userLevelIDExists(int $id): bool
    {
        return array_any($this->UserLevels, fn($row) => SameString($row[0], $id));
    }

    // User Level name list
    public function userLevelNameList(): string
    {
        $list = [];
        foreach ($this->UserLevelIDs as $userLevelID) {
            $list[] = QuotedValue($this->getUserLevelName($userLevelID), DataType::STRING, Config("USER_LEVEL_DBID"));
        }
        return implode(", ", $list);
    }

    // Get user privilege based on table name and User Level
    public function getUserLevelPrivEx(string $tableName, int|string $userLevelID): int
    {
        $ids = explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($userLevelID));
        $userPriv = 0;
        foreach ($ids as $id) {
            if ($id == self::ADMIN_USER_LEVEL_ID) { // System admin
                return Allow::ADMIN->value;
				// Begin of Enable Permission of Export Data by Masino Sinaga, September 12, 2023
				if (MS_ENABLE_PERMISSION_FOR_EXPORT_DATA == true) {
					return Allow::ALL_NEW->value;;
				} else {
					return Allow::ADMIN->value;;
				} // End of Enable Permission of Export Data by Masino Sinaga, September 12, 2023
            } elseif ((int)$id >= self::DEFAULT_USER_LEVEL_ID || $id == self::ANONYMOUS_USER_LEVEL_ID) {
                foreach ($this->UserLevelPrivs as $row) {
                    list($table, $levelid, $priv) = $row;
                    if (SameText($table, $tableName) && SameText($levelid, $id)) {
                        if (is_numeric($priv)) {
                            $userPriv |= (int)$priv;
                        }
                    }
                }
            }
        }
        return $userPriv;
    }

    // Get current User Level name
    public function currentUserLevelName(): string
    {
        return $this->getUserLevelName($this->currentUserLevelID());
    }

    // Get User Level name based on User Level
    public function getUserLevelName(?int $userLevelID, bool $lang = true): string
    {
        if ($userLevelID === self::ANONYMOUS_USER_LEVEL_ID) {
            return $lang ? $this->language->phrase("UserAnonymous") : "Anonymous";
        } elseif ($userLevelID === self::ADMIN_USER_LEVEL_ID) {
            return $lang ? $this->language->phrase("UserAdministrator") : "Administrator";
        } elseif ($userLevelID === self::DEFAULT_USER_LEVEL_ID) {
            return $lang ? $this->language->phrase("UserDefault") : "Default";
        } elseif ($userLevelID > self::DEFAULT_USER_LEVEL_ID) {
            foreach ($this->UserLevels as $row) {
                [$levelid, $name, $hierarchy] = $row;
                if (SameString($levelid, $userLevelID)) {
                    $userLevelName = "";
                    if ($lang) {
                        $userLevelName = $this->language->phrase($name);
                    }
                    return ($userLevelName != "") ? $userLevelName : $name;
                }
            }
        }
        return "";
    }

    // Get current user level hierarchy (sub levels)
    public function currentUserLevelHierarchy(): array
    {
        return $this->getUserLevelHierarchy($this->currentUserLevelID());
    }

    // Get user level hierarchy (sub levels)
    public function getUserLevelHierarchy(int|string $userLevelId): array
    {
        $userLevels = [];
        $userLevelIds = explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($userLevelId));
        foreach ($this->UserLevels as $row) {
            [$levelid, $name, $hierarchy] = $row;
            if (in_array(strval($levelid), $userLevelIds)) {
                $userLevels = array_merge($userLevels, explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($hierarchy)));
            }
        }
        return array_filter(array_unique($userLevels), fn($level) => !IsEmpty($level));
    }

    // Get all user levels from hierarchy
    public function getAllUserLevelsFromHierarchy(int|string|null $userLevelId): array
    {
        if (
            is_int($userLevelId) && $userLevelId <= AdvancedSecurity::ADMIN_USER_LEVEL_ID
            || IsEmpty($userLevelId)
        ) {
            return [];
        }
        $userLevelIds = [];
        $userLevels = $this->getUserLevelHierarchy($userLevelId);
        foreach ($userLevels as $userLevel) {
            $userLevelSubIds = $this->getAllUserLevelsFromHierarchy($userLevel); // Add sub levels
            foreach ($userLevelSubIds as $userLevelSubId) {
                if (!in_array((int)$userLevelSubId, $userLevelIds)) {
                    $userLevelIds[] = (int)$userLevelSubId;
                }
            }
        }
        return $userLevelIds;
    }

    // Get roles
    public function getRoles(int|string|null $userLevelId = null): array
    {
        $userLevelId ??= $this->CurrentUserLevelID;
        // Get roles for this user level
        return $this->getRoleNamesFromUserLevels($userLevelId);
    }

    // Get reachable roles
    public function getReachableRoles(int|string|null $userLevelId = null): array
    {
        $userLevelId ??= $this->CurrentUserLevelID;
        $roles = [];
        // Set up roles from hierarchy
        $userLevelSubIds = $this->getAllUserLevelsFromHierarchy($userLevelId);
        foreach ($userLevelSubIds as $id) {
            $roles = array_merge($roles, $this->getRoleNamesFromUserLevels($id));
        }
        return array_filter(array_unique($roles));
    }

    // Get all roles (current and reachable)
    public function getAllRoles(int|string|null $userLevelId = null): array
    {
        $userLevelId ??= $this->CurrentUserLevelID;
        return array_filter(array_unique(array_merge($this->getRoles($userLevelId), $this->getReachableRoles($userLevelId))));
    }

    // Is Granted
    public function isGranted(string $role): bool
    {
        return in_array($role, $this->getAllRoles()); // Role in current and reachable roles
    }

    // Get role names from user levels
    protected function getRoleNamesFromUserLevels(int|string|null $userLevelId = null): array
    {
        global $USER_ROLES;
        $roles = $this->isLoggedIn() ? ["ROLE_USER"] : ["PUBLIC_ACCESS"];
        $userLevelId ??= $this->CurrentUserLevelID;
        $ids = explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($userLevelId));
        foreach ($ids as $id) {
            if ((int)$id === AdvancedSecurity::ADMIN_USER_LEVEL_ID/* && $this->isSysAdmin()*/) { // Super Admin
                $roles[] = "ROLE_SUPER_ADMIN";
            } else {
                foreach ($USER_ROLES as $userRole) {
                    if (SameString($userRole[0], $id)) {
                        $roles[] = $userRole[1];
                        break;
                    }
                }
                foreach ($this->UserLevels as $row) {
                    [$levelid, $name, $hierarchy] = $row;
                    if (SameString($levelid, $id)) {
                        $roles[] = "ROLE_" . ConstantCase($name);
                        break;
                    }
                }
            }
        }
        return array_filter(array_unique($roles));
    }

    // Display all the User Level settings (for debug only)
    public function showUserLevelInfo(): void
    {
        $debugBar = DebugBar();
        $debugBar->addMessage("AdvancedSecurity::UserLevel: " . print_r($this->UserLevels, true));
        $debugBar->addMessage("AdvancedSecurity::UserLevelPriv: " . print_r($this->UserLevelPrivs, true));
        $debugBar->addMessage("AdvancedSecurity::currentUserLevelID(): " . $this->currentUserLevelID());
        $debugBar->addMessage("AdvancedSecurity::userLevelList(): " . $this->userLevelList());
    }

    // Check privilege for List page (for menu items)
    public function allowList(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::LIST->value);
    }

    // Check privilege for View page (for Allow-View / Detail-View)
    public function allowView(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::VIEW->value);
    }

    // Check privilege for Add page (for Allow-Add / Detail-Add)
    public function allowAdd(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::ADD->value);
    }

    // Check privilege for Edit page (for Detail-Edit)
    public function allowEdit(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::EDIT->value);
    }

    // Check privilege for delete
    public function allowDelete(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::DELETE->value);
    }

    // Check privilege for lookup
    public function allowLookup(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::LOOKUP->value);
    }

    // Check privilege for export
    public function allowExport(string $tableName): bool
    {
        return ($this->currentUserLevelPriv($tableName) & Allow::EXPORT->value);
    }

    // Check if user password expired
    public function isPasswordExpired(): bool
    {
        return $this->session->get(SESSION_STATUS) == "passwordexpired";
    }

    // Set session password expired
    public function setSessionPasswordExpired(): void
    {
        $this->session->set(SESSION_STATUS, "passwordexpired");
    }

    // Set login status
    public function setLoginStatus(string $status = ""): void
    {
        $this->session->set(SESSION_STATUS, $status);
    }

    // Check if user password reset
    public function isPasswordReset(): bool
    {
        return $this->session->get(SESSION_STATUS) == "passwordreset";
    }

    // Check if user is logging in (after changing password)
    public function isLoggingIn(): bool
    {
        return $this->session->get(SESSION_STATUS) == "loggingin";
    }

    // Check if user is logging in (2FA)
    public function isLoggingIn2FA(): bool
    {
        return $this->session->get(SESSION_STATUS) == "loggingin2fa"
            || SecurityHelper()?->getToken() instanceof TwoFactorAuthenticatingToken;
    }

    // Check if user is logged in
    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn || $this->session->get(SESSION_STATUS) == "login";
    }

    // Check if user is system administrator
    public function isSysAdmin(): bool
    {
        return $this->isSysAdmin || $this->session->get(SESSION_SYS_ADMIN) === 1;
    }

    // Check if user is administrator
    public function isAdmin(): bool
    {
        $isAdmin = $this->isSysAdmin();
        if (!$isAdmin) {
            $isAdmin = in_array((string)self::ADMIN_USER_LEVEL_ID, explode(Config("MULTIPLE_OPTION_SEPARATOR"), strval($this->CurrentUserLevelID)))
                || $this->hasUserLevelID(self::ADMIN_USER_LEVEL_ID) || $this->canAdmin();
        }
        if (!$isAdmin) {
            $isAdmin = SameString($this->CurrentUserID, self::ADMIN_USER_LEVEL_ID)
                || count(array_filter($this->UserIDs, fn($id) => SameString($id, self::ADMIN_USER_LEVEL_ID))) > 0;
        }
        return $isAdmin;
    }

    // Save User Level to session
    public function saveUserLevel(): void
    {
        $this->session->set(SESSION_USER_LEVELS, $this->UserLevels);
        $this->session->set(SESSION_USER_LEVEL_PRIVS, $this->UserLevelPrivs);
    }

    // Load User Level from session
    public function loadUserLevel(): void
    {
        if (empty($this->session->get(SESSION_USER_LEVELS)) || empty($this->session->get(SESSION_USER_LEVEL_PRIVS))) {
            $this->setupUserLevel();
            $this->saveUserLevel();
        } else {
            $this->UserLevels = $this->session->get(SESSION_USER_LEVELS) ?? [];
            $this->UserLevelPrivs = $this->session->get(SESSION_USER_LEVEL_PRIVS) ?? [];
        }
    }

    // Get user email
    public function currentUserEmail(): ?string
    {
        return Config("USER_EMAIL_FIELD_NAME") ? $this->currentUserInfo(Config("USER_EMAIL_FIELD_NAME")) : null;
    }

    // Get current user info
    public function currentUserInfo(string $fldname): mixed
    {
        if (!$this->isSysAdmin() && Config("USER_TABLE") && $this->currentUserName()) {
            return LoadUserByIdentifier($this->currentUserName())?->get($fldname);
        }
        return null;
    }

    // Get User ID by user name
    public function getUserIDByUserName(string $userName): mixed
    {
        return LoadUserByIdentifier($userName)?->get(Config("USER_ID_FIELD_NAME")) ?? "";
    }

    // Load User ID
    public function loadUserID(): void
    {
        $this->UserIDs = [];
        if (strval($this->CurrentUserID) == "") {
            // Handle empty User ID here
        } elseif ($this->CurrentUserID != self::ADMIN_USER_LEVEL_ID) {
            // Get first level
            $this->addUserID($this->CurrentUserID);
            $userTable = UserTable();
            $filter = "";
            if (method_exists($userTable, "getUserIDFilter")) {
                $filter = $userTable->getUserIDFilter($this->CurrentUserID);
            }
            $sql = $userTable->getSql($filter);
            $rows = Conn($userTable->Dbid)->executeQuery($sql)->fetchAllAssociative();
            foreach ($rows as $row) {
                $this->addUserID($row[Config("USER_ID_FIELD_NAME")]);
            }

            // Recurse all levels
            $curUserIDList = $this->userIDList();
            $userIDList = "";
            while ($userIDList != $curUserIDList) {
                $filter = '`ReportsTo` IN (' . $curUserIDList . ')';
                $sql = $userTable->getSql($filter);
                $rows = Conn($userTable->Dbid)->executeQuery($sql)->fetchAllAssociative();
                foreach ($rows as $row) {
                    $this->addUserID($row['UserID']);
                }
                $userIDList = $curUserIDList;
                $curUserIDList = $this->userIDList();
            }
        }
    }

    // Add user name
    public function addUserName(string $userName): void
    {
        $this->addUserID($this->getUserIDByUserName($userName));
    }

    // Add User ID
    public function addUserID(mixed $userId): void
    {
        if (strval($userId) == "") {
            return;
        }
        if (!is_numeric($userId)) {
            return;
        }
        $userId = trim($userId);
        if (!in_array($userId, $this->UserIDs)) {
            $this->UserIDs[] = $userId;
        }
    }

    // Delete user name
    public function deleteUserName(string $userName): void
    {
        $this->deleteUserID($this->getUserIDByUserName($userName));
    }

    // Delete User ID
    public function deleteUserID(mixed $userId): void
    {
        if (strval($userId) == "") {
            return;
        }
        if (!is_numeric($userId)) {
            return;
        }
        $cnt = count($this->UserIDs);
        for ($i = 0; $i < $cnt; $i++) {
            if (SameString($this->UserIDs[$i], $userId)) {
                unset($this->UserIDs[$i]);
                break;
            }
        }
    }

    // User ID list
    public function userIDList(): string
    {
        return implode(", ", array_map(fn($userId) => QuotedValue($userId, DataType::NUMBER, Config("USER_TABLE_DBID")), $this->UserIDs));
    }

    // Add Parent User ID
    public function addParentUserID(mixed $userId): void
    {
        if (strval($userId) == "" || SameString($userId, $this->CurrentUserID)) {
            return;
        }
        if (!is_numeric($userId)) {
            return;
        }
        $userId = trim($userId);
        if (!in_array($userId, $this->ParentUserIDs)) {
            $this->ParentUserIDs[] = $userId;
        }
    }

    // Delete Parent User ID
    public function deleteParentUserID(mixed $userId): void
    {
        if (strval($userId) == "" || SameString($userId, $this->CurrentUserID)) {
            return;
        }
        if (!is_numeric($userId)) {
            return;
        }
        $cnt = count($this->ParentUserIDs);
        for ($i = 0; $i < $cnt; $i++) {
            if (SameString($this->ParentUserIDs[$i], $userId)) {
                unset($this->ParentUserIDs[$i]);
                break;
            }
        }
    }

    // Parent User ID list
    public function parentUserIDList(mixed $userId): string
    {
        // Own record
        $res = [];
        if (SameString($userId, $this->CurrentUserID)) {
            foreach ($this->ParentUserIDs as $userId) {
                $res[] = QuotedValue($userId, DataType::NUMBER, Config("USER_TABLE_DBID"));
            }
        } else {
            // All users except user ID
            $ar = $this->UserIDs;
            $len = count($ar);
            for ($i = 0; $i < $len; $i++) {
                if (!SameString($ar[$i], $userId)) {
                    $res[] = QuotedValue($ar[$i], DataType::NUMBER, Config("USER_TABLE_DBID"));
                }
            }
        }
        return implode(", ", $res);
    }

    // List of allowed User IDs for this user
    public function isValidUserID(mixed $userId): bool
    {
        return strval($userId) !== "" && in_array(trim($userId), $this->UserIDs);
    }

    // Activate account based on user
    public function activateUser(UserInterface $user): bool
    {
        if (!Config("REGISTER_ACTIVATE") || IsEmpty(Config("USER_ACTIVATED_FIELD_NAME"))) {
            return false;
        }
        $flash = $this->session->getFlashBag();
        if ($user) {
            try {
                if (!ConvertToBool($user->get(Config("USER_ACTIVATED_FIELD_NAME")))) {
                    UserTable()->updateSql([Config("USER_ACTIVATED_FIELD_NAME") => Config("USER_ACTIVATED_FIELD_VALUE")],
                        [Config("LOGIN_USERNAME_FIELD_NAME") => $user->getUserIdentifier()]) // Auto register
                        ->executeStatement();
                    $user = UserRepository()->loadUserByIdentifier($user->getUserIdentifier()); // Refresh user

                    // Call User Activated event
                    $this->userActivated($user);
                    return true;
                } else {
                    $flash->add("failure", $this->language->phrase("ActivateAgain"));
                    return false;
                }
            } catch (Exception $e) {
                $flash->add("failure", $e->getMessage());
                return false;
            }
        } else {
            $flash->add("failure", $this->language->phrase("NoRecord"));
            return false;
        }
    }

    // UserID Loading event
    public function userIdLoading(): void
    {
        //Log("UserID Loading: " . $this->currentUserID());
    }

    // UserID Loaded event
    public function userIdLoaded(): void
    {
        //Log("UserID Loaded: " . $this->userIDList());
    }

    // User Level Loaded event
    public function userLevelLoaded(): void
    {
        //$this->addUserPermission(<UserLevelName>, <TableName>, <UserPermission>);
        //$this->deleteUserPermission(<UserLevelName>, <TableName>, <UserPermission>);
    }

    // Table Permission Loading event
    public function tablePermissionLoading(): void
    {
        //Log("Table Permission Loading: " . $this->CurrentUserLevelID);
    }

    // Table Permission Loaded event
    public function tablePermissionLoaded(): void
    {
        //Log("Table Permission Loaded: " . $this->CurrentUserLevel);
    }

    // User Custom Validate event
    public function userCustomValidate(string &$userName): bool
    {
        // Enter your custom code to validate user, return true if valid.
        return false;
    }

    // User Validated event
    public function userValidated(UserInterface $user): void
    {
        // Example:
        //Session('UserEmail', $user->getEmail());
    }

    // User PasswordExpired event
    public function userPasswordExpired(UserInterface $user): void
    {
        //Log("User_PasswordExpired");
    }

    // User Activated event
    public function userActivated(UserInterface $user): void
    {
        //Log("User_Activated");
    }
}
