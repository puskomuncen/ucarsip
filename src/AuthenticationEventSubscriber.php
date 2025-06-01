<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\TokenDeauthenticatedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

class AuthenticationEventSubscriber implements EventSubscriberInterface
{
    // Constructor
    public function __construct(
        protected UserProfile $profile,
        protected Language $language,
        protected AdvancedSecurity $security,
        protected Security $symfonySecurity,
    ) {
    }

    /**
     * Priorities of listeners of CheckPassportEvent: (The higher the priority, the earlier a listener is executed.)
     * LoginThrottlingListener (2080)
     * security.listener.<firewall>.user_provider (2048)
     * UserProviderListener (1024)
     * CsrfProtectionListener (512)
     * UserCheckerListener (256) (CheckPassportEvent and AuthenticationSuccessEvent)
     * CheckLdapCredentialsListener (144)
     * CheckCredentialsListener (0)
     *
     * Priorities of listeners of KernelEvents::REQUEST:
     * DebugHandlersListener (2048)
     * ValidateRequestListener (256)
     * AbstractSessionListener (128)
     * AddRequestFormatsListener (100)
     * FragmentListener (48)
     * RouterListener (32)
     * LocaleListener (16)
     * LocaleAwareListener (15)
     * FirewallListener (8)
     * SwitchUserListener (0)
     * LogoutListener (-127)
     * AccessListener (-255)
     *
     * Priorities of listeners of KernelEvents::FINISH_REQUEST:
     * FirewallListener (0)
     * RouterListener (0)
     * LocaleListener (0)
     * LocaleAwareListener (-15)
     *
     * Priorities of listeners of LoginSuccessEvent:
     * SessionStrategyListener (0)
     * PasswordMigratingListener (0)
     * LoginThrottlingListener (0) - Enable RememberMeBadge
     * CheckRememberMeConditionsListener (-32) - Create cookie
     * RememberMeListener (-64)
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => [
                ["onCheckPassport", 0],
            ],
            AuthenticationTokenCreatedEvent::class => "onAuthenticationTokenCreated",
            AuthenticationEvents::AUTHENTICATION_SUCCESS => "onAuthenticationSuccess",
            LoginFailureEvent::class => "onLoginFailure",
            LoginSuccessEvent::class => "onLoginSuccess",
            LogoutEvent::class => "onLogout",
            KernelEvents::REQUEST => ['onKernelRequest', 1], // After FirewallListener (8)
            KernelEvents::FINISH_REQUEST => ["onFinishRequest", -1], // After FirewallListener (0)
            SecurityEvents::SWITCH_USER => "onSwitchUser",
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        // Remember me
        if (Post(Config("SECURITY.firewalls.main.remember_me.remember_me_parameter"))) {
            Session(SESSION_USER_PROFILE_REMEMBER_ME, true); // Save remember me
        }
    }

    public function onAuthenticationTokenCreated(AuthenticationTokenCreatedEvent $event): void
    {
        $token = $event->getAuthenticatedToken();
        $user = $token->getUser();
        $this->profile->setUser($user);
        $userName = $identifier = $user->getUserIdentifier();

        // Call User_CustomValidate event
        if ($this->security->userCustomValidate($userName) != false && $userName != $identifier) {
            $identifier = $userName;
        }

        // Try to find the entity user by identifier if authenticated by others, e.g. LDAP, HybridAuth, Windows, etc.
        if (
            !IsSysAdminUser($user) // Current user is not super admin
            && $identifier && ($entityUser = LoadUserByIdentifier($identifier)) // New entity user found
            && !$entityUser->isEqualTo($user) // New entity user != current user
        ) {
            $token = new UsernamePasswordToken($entityUser, "main", $entityUser->getRoles());
            $event->setAuthenticatedToken($token); // Change token
            $this->profile->setUser($entityUser); // Set current user
            if ($this->profile->get2FAEnabled() && !IsLoggedIn() && !IsLoggingIn() && !IsLoggingIn2FA()) {
                $this->profile->setUserName($identifier)->loadFromStorage();
                Session(SESSION_STATUS, "loggingin2fa");
                $token = new TwoFactorAuthenticatingToken($entityUser, "main", $entityUser->getRoles());
                $event->setAuthenticatedToken($token); // Change token
            }
        }
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
    }

    public function onLogout(LogoutEvent $event): void
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        $token = $this->symfonySecurity->getToken();
        $user = $token?->getUser();
        if ($user) {
            $this->profile->setUser($user);

            // Password changed date not initialized, set as today
            if ($this->profile->emptyPasswordChangedDate()) {
                $this->profile->setLastPasswordChangedDate(StdCurrentDate())->saveToStorage();
            }
            if ($this->profile->passwordExpired() && !in_array(RouteName(), ["changepassword", "login2fa"])) {
                $this->security->setSessionPasswordExpired();
                Session(SESSION_USER_PROFILE_USER_NAME, $user->getUserIdentifier()); // Save user name
                $this->security->userPasswordExpired($user);
                $session->getFlashBag()->add("failure", $this->language->phrase("PasswordExpired"));
                $event->setResponse(new RedirectResponse(UrlFor("changepassword"))); // Redirect to change password page
                return;
            }
            if (
                IsEntityUser($user)
                && !$this->profile->passwordExpired() // Not password expired
                && !IsImpersonator() // Not switch user
            ) {
                $sessionId = $session->getId();

                // Check if force logout or invalid user
                if (!in_array(RouteName(), ["login", "login2fa"])) { // Not login
                    if ($this->profile->isForceLogout($sessionId)) {
                        $session->getFlashBag()->add("failure", $this->language->phrase("UserForceLogout"));
                        $event->setResponse(new RedirectResponse(UrlFor("logout"))); // Redirect to logout page
                        return;
                    }
                }
            }

            // Set up user image
            if (IsEntityUser($user) && !IsEmpty(Config("USER_IMAGE_FIELD_NAME"))) {
                $imageField = UserTable()->Fields[Config("USER_IMAGE_FIELD_NAME")];
                if ($imageField->hasMethod("getUploadPath")) {
                    $imageField->UploadPath = $imageField->getUploadPath();
                }
                $image = GetFileImage($imageField, $user->get(Config("USER_IMAGE_FIELD_NAME")), Config("USER_IMAGE_SIZE"), Config("USER_IMAGE_SIZE"), Config("USER_IMAGE_CROP"));
                $this->profile->setUserImageBase64(base64_encode($image))->saveToStorage(); // Save as base64 encoded
            }
        }
    }

    public function onFinishRequest(FinishRequestEvent $event): void
    {
        if ($user = $this->symfonySecurity->getUser()) {
            $this->profile->setUser($user);
        }
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $userIdentifier = $this->symfonySecurity->getUser()->getUserIdentifier();
        $targetUserIdentifier = $event->getTargetUser()->getUserIdentifier();

        // Clear original session ID
        $session = $event->getRequest()->getSession();
        $this->profile->setUserName($userIdentifier)->loadFromStorage()->removeUser($session->getId());

        // Logout original user
        $this->security->logout();

        // Remove last URL
        $this->security->removeLastUrl();

        // Add flash message
        $event->getRequest()->getSession()->getFlashBag()->add("success", sprintf($this->language->phrase("SwitchUserSuccess"), $targetUserIdentifier));
    }
}
