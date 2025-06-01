<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * Authentication failure handler
 */
class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Save exception in request attribute for AuthenticationUtils::getLastAuthenticationError()
        $request->attributes->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        // Log error
        LogError($exception);

        // Get route name
        $routeName = RouteName();
        $language = Language();

        // Login page
        if (in_array($routeName, ["login", "login1fa"])) {
            $failureMessage = $language->phrase("InvalidUidPwd");
            if ($exception instanceof CustomUserMessageAuthenticationException) {
                $failureMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
            }
            if ($exception instanceof TooManyLoginAttemptsAuthenticationException) {
                $failureMessage = sprintf($language->phrase("ExceedMaxRetry"), $exception->getMessageData()["%minutes%"]); // Set up failure message
            }

            // Captcha enabled and 2FA
            if (Config("USE_PHPCAPTCHA_FOR_LOGIN") && $routeName == "login1fa") {
                $request->getSession()->getFlashBag()->add("failure", $failureMessage); // Set up failure message
                return new JsonResponse(["errorUrl" => UrlFor("login")]); // Reload login page to refresh captcha
            // If JSON response expected
            } elseif (IsJsonResponse()) {
                return new JsonResponse(["error" => $failureMessage]);
            } else {
                $request->getSession()->getFlashBag()->add("failure", $failureMessage); // Set up failure message
                return new Response(); // Empty response => Continue to the login page
            }
        // Login check (for login link)
        } elseif ($routeName == "login_check") {
            if (!$exception instanceof UserActivatedException) {
                $request->getSession()->getFlashBag()->add("failure", $language->phrase("LoginLinkFailure")); // Set up failure message
            }
            return new RedirectResponse(UrlFor("login")); // Go to login page
        }

        // Other pages
        $failureMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
        $request->getSession()->getFlashBag()->add("failure", $failureMessage); // Set up failure message
        if (
            IsJsonResponse() || // JSON response expected
            IsModal() && // Modal
            !($routeName == "login" && Config("USE_MODAL_LOGIN")) // Not modal login
        ) {
            return new JsonResponse(["url" => UrlFor("login")]);
        }

        // Redirect to login page
        return new RedirectResponse(UrlFor("login"));
    }
}
