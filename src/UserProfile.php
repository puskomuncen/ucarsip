<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Exception;
use Throwable;
use Stringable;
use JsonSerializable;
use stdClass;

/**
 * User Profile
 */
class UserProfile implements AdvancedUserInterface, Stringable, JsonSerializable
{
    public static int $CONCURRENT_SESSION_COUNT = 1; // Maximum sessions allowed
    public static bool $FORCE_LOGOUT_USER_ENABLED = true; // Force logout user
    public static bool $FORCE_LOGOUT_CONCURRENT_USER = true; // Force logout concurrent user
    public static int $SESSION_CLEANUP_TIME = 60 * 24; // Clean up unused sessions if idle more than 1 day
    public static int $SESSION_TIMEOUT = 10;
    public static int $PASSWORD_EXPIRE = 90;
    public static string $CONCURRENT_SESSIONS = "Sessions";
    public static string $SESSION_ID = "SessionID";
    public static string $LAST_ACCESSED_DATE_TIME = "LastAccessedDateTime";
    public static string $FORCE_LOGOUT = "ForceLogout";
	public static string $LAST_BAD_LOGIN_DATE_TIME = "LastBadLoginDateTime"; // added by Masino Sinaga, October 20, 2024
    public static string $LAST_PASSWORD_CHANGED_DATE = "LastPasswordChangedDate";
    public static string $LANGUAGE_ID = "LanguageId";
    public static string $SEARCH_FILTERS = "SearchFilters";
    public static string $IMAGE = "UserImage";
    public static string $SECRETS = "Secrets";
    public static string $BACKUP_CODES = "BackupCodes";
    public static string $CHAT_ENABLED = "ChatEnabled";
    public static string $TWOFA_ENABLED = "TwoFAEnabled";
    protected string $userName = "";
    protected mixed $userId = null;
    protected mixed $parentUserId = null;
    protected int|string $userLevel = AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID;
    protected int $permission = 0;
    protected array $profile = [];
    protected ?UserInterface $user = null;
    protected ?UserInterface $authenticatedUser = null;
    protected int $timeoutTime;
    protected int $passwordExpiryTime;

    // Constructor
    public function __construct(string $userName = "")
    {
        $this->timeoutTime = self::$SESSION_TIMEOUT > 0 ? self::$SESSION_TIMEOUT : Config("SESSION_TIMEOUT");
        $this->passwordExpiryTime = self::$PASSWORD_EXPIRE;
        $this->setLastPasswordChangedDate(""); // Password Expiry
        if ($userName) {
            $this->setUserName($userName)->loadFromStorage();
        }
    }

    // Create
    public static function create(string $userName = ""): static
    {
        return new static($userName);
    }

    // Has user name
    public function hasUserName(): bool
    {
        return !IsEmpty($this->userName);
    }

    // Get user name (AdvancedUserInterface)
    public function userName(): string
    {
        return $this->userName;
    }

    // Get user name
    public function getUserName(): string
    {
        return $this->userName();
    }

    // Set user name
    public function setUserName(string $value): static
    {
        $this->userName = $value;
        return $this;
    }

    // Get user ID (AdvancedUserInterface)
    public function userID(): mixed
    {
        return $this->userId;
    }

    // Get user ID
    public function getUserID(): mixed
    {
        return $this->userId;
    }

    // Set user ID
    public function setUserID($value): static
    {
        $this->userId = $value;
        return $this;
    }

    // Get parent user ID (AdvancedUserInterface)
    public function parentUserID(): mixed
    {
        return $this->parentUserId;
    }

    // Set parent user ID
    public function setParentUserID($value): static
    {
        $this->parentUserId = $value;
        return $this;
    }

    // Get user level (AdvancedUserInterface)
    public function userLevel(): int|string
    {
        return $this->userLevel;
    }

    // Get user level
    public function getUserLevel(): int|string
    {
        return $this->userLevel;
    }

    // Set user level
    public function setUserLevel($value): static
    {
        $this->userLevel = $value;
        return $this;
    }

    // Get permission
    public function getPermission(): int
    {
        return $this->permission;
    }

    // Set permission
    public function setPermission(int $value): static
    {
        $this->permission = $value;
        return $this;
    }

    // Get profile
    public function getProfile(): array
    {
        return $this->profile;
    }

    // Set profile
    public function setProfile(array $value): static
    {
        $this->profile = $value;
        return $this;
    }

    // Has value in profile
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->profile);
    }

    // Get value
    public function get(string $name): mixed
    {
        return $this->profile[$name] ?? null;
    }

    // Set value
    public function set(string $name, mixed $value): static
    {
        $this->profile[$name] = $value;
        return $this;
    }

    // Get profile as array
    public function toArray(): array
    {
        return $this->profile;
    }

    // Get profile as object
    public function toObject(): object
    {
        return (object)$this->toArray();
    }

    // Set property to profile // PHP
    public function __set(string $name, mixed $value): void
    {
        if ($value === null) {
            $this->delete($name);
        } else {
            $this->set($name, $value);
        }
    }

    // Get property from profile // PHP
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    // Delete property from profile
    public function delete(string $name): static
    {
        unset($this->profile[$name]);
        return $this;
    }

    // Assign properties to profile
    public function assign(object|array $input): void
    {
        if (is_object($input)) {
            $vars = get_object_vars($input);
            if (is_array($vars["data"])) {
                $data = $vars["data"];
                unset($vars["data"]);
                $vars = array_merge($vars, $data);
            }
            $this->assign($vars);
        } elseif (is_array($input)) {
            $input = array_filter($input, fn ($v, $k) => !is_int($k) && (is_bool($v) || is_float($v) || is_int($v) || $v === null || is_string($v)), ARRAY_FILTER_USE_BOTH);
            foreach ($input as $key => $value) {
                if (preg_match('/http:\/\/schemas\.[.\/\w]+\/claims\/(\w+)/', $key, $m)) { // e.g. http://schemas.microsoft.com/identity/claims/xxx, http://schemas.xmlsoap.org/ws/2005/05/identity/claims/xxx
                    $key = $m[1];
                }
                $this->set($key, $value);
            }
        }
    }

    // Check if super admin
    public function isSysAdmin(): bool
    {
        return $this->user && IsSysAdminUser($this->user);
    }

    // Check if entity user
    public function isEntityUser(): bool
    {
        return $this->user && IsEntityUser($this->user);
    }

    // Get language ID
    public function getLanguageId(): ?string
    {
        return $this->{self::$LANGUAGE_ID};
    }

    // Set language ID
    public function setLanguageId(string $value): static
    {
        $this->{self::$LANGUAGE_ID} = $value;
        return $this;
    }

    // Get search filters
    public function getFilters(): ?array
    {
        return $this->{self::$SEARCH_FILTERS};
    }

    // Set search filters
    public function setFilters(array $value): static
    {
        $this->{self::$SEARCH_FILTERS} = $value;
        return $this;
    }

    // Get search filters for a page
    public function getSearchFilters(string $pageid): string
    {
        try {
            $allfilters = $this->getFilters();
            return $allfilters[$pageid] ?? "";
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return "";
    }

    // Set search filters for a page
    public function setSearchFilters(string $pageid, array|string $filters): static
    {
        try {
            $allfilters = $this->getFilters();
            if (!is_array($allfilters)) {
                $allfilters = [];
            }
            $allfilters[$pageid] = $filters;
            $this->setFilters($allfilters)->saveToStorage();
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return $this;
    }

    // Has user
    public function hasUser(): bool
    {
        return $this->user != null;
    }

    // Get user
    public function getUser(): ?UserInterface
    {
        if ($this->hasUserName()) {
            $userName = $this->getUserName();
            if (!$this->user || $this->user->getUserIdentifier() != $userName) {
                foreach (SecurityContainer("security.user_providers")->getProviders() as $provider) {
                    try {
                        if ($provider instanceof EntityUserProvider || $provider instanceof InMemoryUserProvider) {
                            $this->user = $provider->loadUserByIdentifier($userName);
                        }
                    } catch (UserNotFoundException) {
                        // Try next one
                    }
                }
            }
        }
        return $this->user;
    }

    // Set user
    public function setUser(?UserInterface $user): static
    {
        if ($user === null) {
            $this->user = null;
            $this->clear();
            return $this;
        }
        $changed = false;
        if ($this->user === null) {
            $changed = true;
        } elseif (get_class($this->user) != get_class($user)) {
            $this->authenticatedUser = $this->user; // Save the authenticated user
            $changed = true;
        } elseif ($user instanceof EquatableInterface) {
            $changed = !$user->isEqualTo($this->user);
        } else {
            $changed = $user->getUserIdentifier() != $this->user->getUserIdentifier();
        }
        if ($changed) {
            $this->user = $user;
            $this->setUserName($user->getUserIdentifier())->loadFromStorage();
        }
        return $this;
    }

    // Get original user (originally authenticated by middleware, e.g. LdapUser)
    public function getAuthenticatedUser(): ?UserInterface
    {
        return $this->authenticatedUser;
    }

    // Set original user (originally authenticated by middleware, e.g. LdapUser)
    public function setAuthenticatedUser(?UserInterface $user): static
    {
        $this->authenticatedUser = $user;
        return $this;
    }

    // Load profile from storage
    public function loadFromStorage(): static
    {
        if ($this->hasUserName()) {
            $user = $this->getUser();
            if (IsEntityUser($user)) { // Database user
                if ($profileField = Config("USER_PROFILE_FIELD_NAME")) {
                    $this->load($user->get($profileField));
                }
            } else { // Not database user, load from Cookie
                $this->loadFromCookie();
            }
        }
        return $this;
    }

    // Save profile to storage
    public function saveToStorage(): static
    {
        if ($this->hasUserName()) {
            $user = $this->getUser();
            if (IsEntityUser($user)) { // Database user => save to database
                if ($profileField = Config("USER_PROFILE_FIELD_NAME")) {
                    $user->set($profileField, $this->profile);
                    UserEntityManager()->flush();
                }
            } else { // Not database user, save to Cookie
                $this->saveToCookie();
            }
        }
        return $this;
    }

    // Load profile from cookie
    public function loadFromCookie(): static
    {
        if ($this->hasUserName() && $cookie = ReadCookie(COOKIE_USER_PROFILE . UrlBase64Encode($this->getUserName()))) {
            $this->load(Decrypt($cookie));
        }
        return $this;
    }

    // Save profile to cookie
    public function saveToCookie(): static
    {
        if ($this->hasUserName()) {
            WriteCookie(COOKIE_USER_PROFILE . UrlBase64Encode($this->getUserName()), Encrypt((string)$this), time() + Config("USER_PROFILE_COOKIE_EXPIRY_TIME"));
        }
        return $this;
    }

    // Load profile from session
    public function loadFromSession(): static
    {
        if ($this->hasUserName() && Session()->has(SESSION_USER_PROFILE . "_" . $this->getUserName())) {
            $this->load(Session(SESSION_USER_PROFILE . "_" . $this->getUserName()));
        }
        return $this;
    }

    // Save profile to session
    public function saveToSession(): static
    {
        if ($this->hasUserName()) {
            Session(SESSION_USER_PROFILE . "_" . $this->getUserName(), (string)$this);
        }
        return $this;
    }

    // Remove profile from session
    public function removeFromSession(): static
    {
        if ($this->hasUserName()) {
            Session()->remove(SESSION_USER_PROFILE . "_" . $this->getUserName());
        }
        return $this;
    }

    // Unserialize string to array
    public static function unserialize(?string $profile): array
    {
        $profile ??= "[]";
        return str_starts_with($profile, "a:") // Array by serialize()
            ? (@unserialize($profile) ?: [])
            : (json_decode($profile, true) ?: []);
    }

    // Load profile from string or array
    public function load(string|array|null $profile): static
    {
        $ar = is_array($profile)
            ? $profile
            : (is_string($profile) ? self::unserialize($profile) : null);
        if (is_array($ar)) {
            $this->profile = array_merge($this->profile, $ar);
        }
        return $this;
    }

    // Clear profile
    public function clear(): static
    {
        $this->userName = "";
        $this->userId = null;
        $this->parentUserId = null;
        $this->userLevel = AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID;
        $this->permission = 0;
        $this->profile = [];
        return $this;
    }

    // Implements Stringable
    public function __toString(): string
    {
        return json_encode($this->profile);
    }

    // Implements JsonSerialize
    public function jsonSerialize(): mixed
    {
        return $this->profile;
    }

    // Get concurrent sessions
    public function getConcurrentSessions(): ?array
    {
        return $this->{self::$CONCURRENT_SESSIONS};
    }

    // Set concurrent sessions
    public function setConcurrentSessions(?array $value): static
    {
        $this->{self::$CONCURRENT_SESSIONS} = $value;
        return $this;
    }

    // Is valid user
    public function isValidUser(string $sessionId, bool $addSession = true): bool
    {
        if (IsApi() || $this->isSysAdmin() || !$this->isEntityUser()) { // Ignore API, super admin and non database users
            return true;
        }
        try {
            $sessions = $this->getConcurrentSessions();
            $sessions = is_array($sessions) ? $sessions : [];
            $sessions = $this->removeUnusedSessions($sessions); // Remove unused sessions
            $valid = false;
            $cnt = 0;
            $logoutUser = self::$FORCE_LOGOUT_CONCURRENT_USER && self::$CONCURRENT_SESSION_COUNT == 1;
            foreach ($sessions as &$session) {
                $sessId = $session[self::$SESSION_ID];
                $dt = $session[self::$LAST_ACCESSED_DATE_TIME];
                $forceLogout = ConvertToBool($session[self::$FORCE_LOGOUT]);
                if (SameString($sessId, $sessionId)) {
                    $valid = true;
					// Changed StdCurrentDateTime with DbCurrentDateTime, by Masino Sinaga, September 17, 2023
                    if (!$forceLogout && ($this->timeoutTime < 0 || DateDiff($dt, DbCurrentDateTime(), "n") > $this->timeoutTime)) { // Update accessed time
						// Changed StdCurrentDateTime with DbCurrentDateTime, by Masino Sinaga, September 17, 2023
                        $session[self::$LAST_ACCESSED_DATE_TIME] = DbCurrentDateTime();
                    }
                    break;
                } elseif ($logoutUser) { // Logout concurrent user
                    $session[self::$FORCE_LOGOUT] = true;
                } else {
                    $cnt++;
                }
            }
            if (!$valid && $addSession && (self::$CONCURRENT_SESSION_COUNT < 0 || $cnt < self::$CONCURRENT_SESSION_COUNT || $logoutUser)) {
                $valid = true;
                $sessions[] = [
                    self::$SESSION_ID => $sessionId,
                    self::$LAST_ACCESSED_DATE_TIME => DbCurrentDateTime(), // Changed StdCurrentDateTime with DbCurrentDateTime, by Masino Sinaga, September 17, 2023
                    self::$FORCE_LOGOUT => false,
                ];
            }
            if ($valid) {
                $this->setConcurrentSessions($sessions)->saveToStorage();
            }
            return $valid;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Remove unused sessions
    protected function removeUnusedSessions(array $sessions): array
    {
        $cleanupTime = $this->timeoutTime > 0 ? $this->timeoutTime : self::$SESSION_CLEANUP_TIME; // Fallback to cleanup time if timeout not specified
        return array_filter($sessions, fn($session) => DateDiff($session[self::$LAST_ACCESSED_DATE_TIME], DbCurrentDateTime(), "n") <= $cleanupTime); // Changed StdCurrentDateTime with DbCurrentDateTime, by Masino Sinaga, September 17, 2023
    }

    // Remove user
    public function removeUser(string $sessionId): bool
    {
        if ($this->isSysAdmin()) { // Ignore system admin
            return true;
        }
        try {
            $sessions = $this->getConcurrentSessions();
            $sessions = is_array($sessions) ? $sessions : [];
            $sessions = array_filter($sessions, fn($session) => $session[self::$SESSION_ID] != $sessionId);
            $this->setConcurrentSessions($sessions)->saveToStorage();
            return true;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Reset concurrent user
    public function resetConcurrentUser(): bool
    {
        try {
            $this->setConcurrentSessions(null)->saveToStorage();
            return true;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Get active user session coount
    public function activeUserSessionCount(bool $active = true): int
    {
        try {
            $sessions = $this->getConcurrentSessions();
            $sessions = is_array($sessions) ? $sessions : [];
            if ($active) {
                $sessions = $this->removeUnusedSessions($sessions);
            }
            return count($sessions);
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return 0;
    }

    // Force logout user
    public function isForceLogout(?string $sessionId = null): bool
    {
        if (IsApi()) { // Ignore API
            return false;
        }
        try {
            $isForceLogout = $sessionId === null ? true : false;
            $sessions = $this->getConcurrentSessions();
            $sessions = is_array($sessions) ? $sessions : [];
            foreach ($sessions as $session) {
                if ($sessionId === null) { // All session must be force logout
                    if (!ConvertToBool($session[self::$FORCE_LOGOUT])) {
                        return false;
                    }
                } elseif (SameText($session[self::$SESSION_ID], $sessionId)) {
                    return ConvertToBool($session[self::$FORCE_LOGOUT]);
                }
            }
            return $isForceLogout;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Force logout user
    public function forceLogoutUser(): bool
    {
        if (!self::$FORCE_LOGOUT_USER_ENABLED) {
            return false;
        }
        try {
            $sessions = $this->getConcurrentSessions();
            $sessions = is_array($sessions) ? $sessions : [];
            $sessions = $this->removeUnusedSessions($sessions);
            foreach ($sessions as &$session) {
                $session[self::$FORCE_LOGOUT] = true;
            }
            $this->setConcurrentSessions($sessions)->saveToStorage();
            return true;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Reset login retry
    public function resetLoginRetry(): bool
    {
        try {
            foreach ($this->profile as $key => $value) {
                if (
                    str_starts_with($key, Config("LOGIN_RATE_LIMITERS.global.id"))
                    || str_starts_with($key, Config("LOGIN_RATE_LIMITERS.local.id"))
                ) {
                    unset($this->profile[$key]);
                }
            }
            $this->saveToStorage();
            return true;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Password expired
    public function passwordExpired(): bool
    {
        if ($this->isSysAdmin()) { // Ignore system admin
            return false;
        }
        try {
            $dt = $this->getLastPasswordChangedDate();
            if (strval($dt) == "") {
                $dt = StdCurrentDate();
            }
            return DateDiff($dt, StdCurrentDate(), "d") >= $this->passwordExpiryTime;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Empty password changed date
    public function emptyPasswordChangedDate(): bool
    {
        if ($this->isSysAdmin()) { // Ignore system admin
            return false;
        }
        try {
            $dt = $this->getLastPasswordChangedDate();
            return (strval($dt) == "");
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Set password expired
    public function setPasswordExpired(): bool
    {
        try {
            $this->setLastPasswordChangedDate(StdDate(strtotime("-" . ($this->passwordExpiryTime + 1) . " days")))
                ->saveToStorage();
            return true;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

	// Begin of modification by Masino Sinaga, October 20, 2024
	// Get last bad login date time
    public function getLastBadLoginDateTime(): ?string
    {
        return $this->{self::$LAST_BAD_LOGIN_DATE_TIME} ?? null;
    }

    // Set last bad login date time
    public function setLastBadLoginDateTime(string $value): static
    {
        $this->{self::$LAST_BAD_LOGIN_DATE_TIME} = $value;
        return $this;
    }
	// End of modification by Masino Sinaga, October 20, 2024

    // Get last password changed date
    public function getLastPasswordChangedDate(): ?string
    {
        return $this->{self::$LAST_PASSWORD_CHANGED_DATE} ?? null;
    }

    // Set last password changed date
    public function setLastPasswordChangedDate(string $value): static
    {
        $this->{self::$LAST_PASSWORD_CHANGED_DATE} = $value;
        return $this;
    }

    // Get secrets
    public function getSecrets(): array
    {
        return $this->{self::$SECRETS} ?? [];
    }

    // Set secrets
    public function setSecrets(array $value): static
    {
        $this->{self::$SECRETS} = $value;
        return $this;
    }

    // Reset secrets
    public function resetSecrets(): bool
    {
        try {
            $this->setSecrets([])
                ->setCodes([])
                ->saveToStorage();
            return true;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
            return false;
        }
    }

    // Get backup codes
    protected function getCodes(): array
    {
        return $this->{self::$BACKUP_CODES} ?? [];
    }

    // Set backup codes
    protected function setCodes(array $value): static
    {
        $this->{self::$BACKUP_CODES} = $value;
        return $this;
    }

    // Get chat enabled
    public function getChatEnabled(): ?bool
    {
        return $this->{self::$CHAT_ENABLED} ?? null;
    }

    // Set chat enabled
    public function setChatEnabled(bool $value): static
    {
        $this->{self::$CHAT_ENABLED} = $value;
        return $this;
    }

    // Get 2FA enabled
    public function get2FAEnabled(): bool
    {
        return Config("FORCE_TWO_FACTOR_AUTHENTICATION") || ($this->{self::$TWOFA_ENABLED} ?? false);
    }

    // Set 2FA enabled
    public function set2FAEnabled(bool $value): static
    {
        $this->{self::$TWOFA_ENABLED} = $value;
        return $this;
    }

    // Get email
    public function getEmail(bool $verified = true): ?string
    {
        $secret = $this->getSecret("email");
        if ($verified) { // Verified
            if ($secret && !empty($secret->account) && !empty($secret->verifiedAt)) {
                return $secret->account;
            }
        } else { // Unverified
            if ($secret && !empty($secret->account)) { // Unverified account
                return $secret->account;
            } elseif ($secret && !empty($secret->unverifiedAccount)) { // Unverified account
                return $secret->unverifiedAccount;
            } elseif ($this->user && $this->isEntityUser() && Config("USER_EMAIL_FIELD_NAME")) {
                return $this->user->get(Config("USER_EMAIL_FIELD_NAME"));
            } elseif ($this->user && $this->isSysAdmin() && Config("ADMIN_EMAIL")) {
                return Config("ADMIN_EMAIL");
            }
        }
        return null;
    }

    // Get phone
    public function getPhone(bool $verified = true): ?string
    {
        $secret = $this->getSecret("sms");
        if ($verified) { // Verified
            if ($secret && !empty($secret->account) && !empty($secret->verifiedAt)) {
                return $secret->account;
            }
        } else { // Unverified
            if ($secret && !empty($secret->account)) { // Unverified account
                return $secret->account;
            } elseif ($secret && !empty($secret->unverifiedAccount)) { // Unverified account
                return $secret->unverifiedAccount;
            } elseif ($this->user && $this->isEntityUser() && Config("USER_PHONE_FIELD_NAME")) {
                return $this->user->get(Config("USER_PHONE_FIELD_NAME"));
            } elseif ($this->user && $this->isSysAdmin() && Config("ADMIN_PHONE")) {
                return Config("ADMIN_PHONE");
            }
        }
        return null;
    }

    // Get 2FA account
    public function getAccount(string $type, bool $verified = false): ?string
    {
        if (in_array($type, ["email", "sms"])) {
            return match ($type) {
                "email" => $this->getEmail($verified),
                "sms" => $this->getPhone($verified)
            };
        }
        return null;
    }

    // Get user image (base64 encoded)
    public function getUserImageBase64(): ?string
    {
        return $this->{self::$IMAGE} ?? null;
    }

    // Set user image (base64 encoded)
    public function setUserImageBase64(string $value): static
    {
        $this->{self::$IMAGE} = $value;
        return $this;
    }

    // Has secret
    public function hasSecret(string $authType, bool $verified): bool
    {
        if (
            $this->user
            && ($this->isEntityUser() || $this->isSysAdmin())
            && ($secret = $this->getSecret($authType))
        ) {
            return !empty($secret->secret) && (!$verified || !empty($secret->verifiedAt));
        }
        return false;
    }

    // Get secret
    public function getSecret(string $authType): ?object
    {
        $secrets = $this->getSecrets();
        $secret = $secrets[$authType] ?? null;
        return $secret ? (object)$secret : null;
    }

    // Set secret
    public function setSecret(string $authType, ?object $secret): static
    {
        $secrets = $this->getSecrets();
        $secrets[$authType] = (array)$secret;
        if ($secret === null) {
            unset($secrets[$authType]);
        }
        $this->setSecrets($secrets)->saveToStorage();
        return $this;
    }

    // Has 2FA secret
    public function hasUserSecret(bool $verified = false, ?string $authType = null): bool
    {
        try {
            foreach (Config("TWO_FACTOR_AUTHENTICATION_TYPES") as $type) {
                if ($authType === null || $authType == $type) {
                    if (in_array($type, ["email", "sms"])) {
                        if ($this->getAccount($type, $verified)) {
                            return true;
                        }
                    } else {
                        if ($this->hasSecret($type, $verified)) {
                            return true;
                        }
                    }
                }
            }
            return false;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Get 2FA secret
    public function getUserSecret(string $authType): ?string
    {
        try {
            $secret = $this->getSecret($authType);
            if ($secret === null && ($class = TwoFactorAuthenticationClass($authType))) {
                $secret = new stdClass();
                $secret->secret = $class::generateSecret();
                $secret->createdAt = time();
                $this->setSecret($authType, $secret);
                $backupCodes = $this->getBackupCodes();
                if (count($backupCodes) == 0) { // Generate if no backup codes
                    $backupCodes = $class::generateBackupCodes();
                    $this->setBackupCodes($backupCodes);
                }
            }
            return $secret?->secret;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return null;
    }

    // Set one time password (Email/SMS)
    public function setOneTimePassword(string $authType, string $account, string $otp): static
    {
        try {
            if ($secret = $this->getSecret($authType)) {
                $secret->otp = $otp;
                $secret->otpCreatedAt = DbCurrentDateTime();
                $secret->account = $account;
                $this->setSecret($authType, $secret);
            }
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return $this;
    }

    // Get backup code count
    public function getBackupCodeCount(): int
    {
        try {
            return count($this->getCodes());
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
    }

    // Get decrypted backup codes
    public function getBackupCodes(): array
    {
        try {
            $codes = $this->getCodes();
            $decryptedCodes = is_array($codes)
                ? array_map(fn($code) => strlen($code) == Config("TWO_FACTOR_AUTHENTICATION_BACKUP_CODE_LENGTH") ? $code : PhpDecrypt(strval($code)), $codes) // Encrypt backup codes if necessary
                : [];
            return $decryptedCodes;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
    }

    // Set encrypted backup codes
    public function setBackupCodes(array $codes): static
    {
        try {
            $encryptedCodes = array_map(fn($code) => strlen($code) == Config("TWO_FACTOR_AUTHENTICATION_BACKUP_CODE_LENGTH") ? PhpEncrypt(strval($code)) : $code, $codes); // Encrypt backup codes if necessary
            $this->setCodes($encryptedCodes);
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return $this;
    }

    // Get new set of backup codes
    public function getNewBackupCodes(): array
    {
        try {
            $codes = TwoFactorAuthenticationClass()::generateBackupCodes();
            $this->setBackupCodes($codes)->saveToStorage();
            return $codes;
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return [];
    }

    // Verify 2FA code
    public function verify2FACode(string $code, string $authType): bool
    {
        if (!in_array($authType, Config("TWO_FACTOR_AUTHENTICATION_TYPES"))) {
            throw new Exception(sprintf("The two factor authentication type '%s' is not supported.", $authType)); // Not supported
        }
        try {
            $secret = $this->getSecret($authType);
            if (!$secret) {
                return false;
            }
            if (SameText($authType, "google")) { // Check against secret
                $storedCode = $secret->secret;
            } else { // Check against encrypted one time password
                $storedCode = Decrypt($secret->otp ?? "", $secret->secret);
            }
            if ($storedCode !== "") { // Stored code is not empty
                $valid = TwoFactorAuthenticationClass($authType)::checkCode($storedCode, $code);
                if (!$valid && strlen($code) == Config("TWO_FACTOR_AUTHENTICATION_BACKUP_CODE_LENGTH")) { // Not valid, check if $code is backup code
                    $backupCodes = $this->getBackupCodes();
                    $valid = array_search($code, $backupCodes);
                    if ($valid !== false) {
                        array_splice($backupCodes, $valid, 1); // Remove used backup code
                        $this->setBackupCodes($backupCodes);
                        $valid = true;
                    }
                }
                if ($valid) { // Update verification date/time
                    $secret->verifiedAt = time();
                    $secret->lastVerifiedCode = $code; // Update last verified code
                    $this->setSecret($authType, $secret);
                }
                return $valid;
            }
        } catch (Throwable $e) {
            if (IsDebug()) {
                throw $e;
            }
        }
        return false;
    }

    // Get 2FA config
    public function get2FAConfig(): array
    {
        $allSecrets = $this->getSecrets();
        $secrets = [];
        $verifiedCount = 0;
        foreach (Config("TWO_FACTOR_AUTHENTICATION_TYPES") as $authType) {
            $secret = $allSecrets[$authType] ?? null;
            if ($secret) {
                $secret = (object)$secret; // Convert to object
                $secret->type = $authType;
                $secret->account = isset($secret->account, $secret->verifiedAt) ? PartialHide($secret->account) : null;
                $secret->unverifiedAccount = isset($secret->unverifiedAccount) ? PartialHide($secret->unverifiedAccount) : null;
                if (!$secret->account && !$secret->unverifiedAccount && ($account = $this->getAccount($authType, false))) { // Get unverified account
                    $secret->unverifiedAccount = PartialHide($account);
                }
                unset($secret->secret); // Hide the secret
                if (isset($secret->verifiedAt) && !IsEmpty($secret->verifiedAt)) {
                    $verifiedCount++;
                }
            } else {
                $secret = new stdClass(); // Set an empty object (for JavaScript)
                $secret->type = $authType;
                $secret->secret = false;
                if ($account = $this->getAccount($authType, false)) { // Get unverified account
                    $secret->unverifiedAccount = PartialHide($account);
                }
            }
            $secret->selected = false; // For js template
            $secrets[] = $secret;
        }
        return [
            "enabled" => $this->get2FAEnabled(),
            "secrets" => $secrets,
            "verifiedCount" => $verifiedCount,
            "backupCodeCount" => $this->getBackupCodeCount()
        ];
    }

    /**
     * Returns the roles granted to the user
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = [];
        if ($user = $this->getUser()) {
            $roles = $user->getRoles();
        }
        if ($this->userLevel != null) {
            $roles = array_merge($roles, Security()->getRoles($this->userLevel));
        }
        return array_unique(array_merge($roles, ['ROLE_USER']));
    }

    /**
     * Removes sensitive data from the user
     */
    public function eraseCredentials(): void
    {
        $this->getUser()?->eraseCredentials();
    }

    /**
     * Returns the identifier for this user (e.g. username or email address)
     */
    public function getUserIdentifier(): string
    {
        return $this->getUser()?->getUserIdentifier() ?? $this->getUserName();
    }
}
