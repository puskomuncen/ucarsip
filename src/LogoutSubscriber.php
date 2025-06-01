<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Exception;

class LogoutSubscriber implements EventSubscriberInterface
{
    protected FlashBagInterface $flash;

    public function __construct(
        protected UserProfile $profile,
        protected Language $language,
        protected AdvancedSecurity $security,
    ) {
    }

    /**
     * Priorities of listeners of LogoutEvent:
     * DefaultLogoutListener (64)
     * RememberMeListener
     * ClearSiteDataLogoutListener
     * CsrfTokenClearingLogoutListener
     * SessionLogoutListener (if 'logout.invalidate_session' enabled)
     * LogoutListener (-127)
     * CookieClearingLogoutListener (-255)
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['onLogout', 1] // Before SessionLogoutListener
        ];
    }

    protected function setNotice(string $type, mixed $message)
    {
        if (!$this->flash->has("heading")) {
            $this->flash->add("heading", $this->language->phrase("Notice"));
        }
        if (!$this->flash->has($type)) {
            $this->flash->add($type, $message);
        }
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        $username = $this->security->currentUserName();
        $valid = true;

        // Call User LoggingOut event
        $valid = $this->userLoggingOut($username) !== false;
        if (!$valid) {
            $lastUrl = $this->security->lastUrl() ?: UrlFor("index");
            $this->redirect($event, $lastUrl); // Go to last accessed URL
            return;
        } else {
            $params = $request->query->all();
            $this->flash = $session->getFlashBag();

            // Remove last URL
            $this->security->removeLastUrl();

            // Password changed (after expired password)
            $isPasswordChanged = Config("USE_TWO_FACTOR_AUTHENTICATION") && $session->get(SESSION_STATUS) == "passwordchanged";

            // Load user profile
            if ($this->security->isLoggedIn()) {
                $this->profile->setUserName($this->security->currentUserName())->loadFromStorage(); // Load user profile
                // Check force logout or session expired
                $isForceLogout = $this->profile->isForceLogout($session->getId());
                $isValidUser = $this->profile->isValidUser($session->getId(), false);
                // Clear session ID
                $this->profile->removeUser($session->getId());
            } else {
                $isForceLogout = false;
                $isValidUser = true; // Anonymous user
            }

            // Call User LoggedOut event
            $this->userLoggedOut($username);

            // Clean upload temp folder
            CleanUploadTempPaths($session->getId());

            // Invalidate session
            $session->invalidate();

            // Personal data
            if ($params["deleted"] ?? false) {
                $this->setNotice("success", $this->language->phrase("PersonalDataDeleteSuccess"));
                $isValidUser = true;
            }

            // If force logout or session expired, show message
            if ($isForceLogout) {
                $this->setNotice("failure", $this->language->phrase("UserForceLogout"));
            } elseif (!$isValidUser) {
                $this->setNotice("failure", $this->language->phrase("SessionExpired"));
            }

            // If password changed, show login message
            if ($isPasswordChanged) {
                $this->setNotice("success", $this->language->phrase("LoginAfterPasswordChanged"));
            }

            // If session expired, show expired message
            if ($params["expired"] ?? false) {
                $this->setNotice("failure", $this->language->phrase("SessionExpired"));
            }

            // Save notice
            $session->save();

            // Reset user profile
            Container("user.profile", new UserProfile());

            // Go to login page
            $this->redirect($event, UrlFor("login"));
        }
    }

    /**
     * Redirect
     *
     * @param LogoutEvent $event Event
     * @param string $url URL
     */
    public function redirect(LogoutEvent $event, string $url): void
    {
        $this->pageRedirecting($url);
        if (IsJsonResponse() || IsModal()) {
            $event->setResponse(new JsonResponse(["url" => $url]));
            return;
        }
        $event->setResponse(new RedirectResponse($url));
    }

    // User Logging Out event
    public function userLoggingOut(string $userName): bool
    {
        // Enter your code here
        // To cancel, set return value to false;
        return true;
    }

    // User Logged Out event
    public function userLoggedOut(string $userName): void
    {
        //Log("User Logged Out");
    }

    // Page Redirecting event
    public function pageRedirecting(string &$url): void
    {
        // Example:
        //$url = "your URL";
    }
}
