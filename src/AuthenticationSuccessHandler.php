<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * Authentication success handler
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $routeName = RouteName();
        $language = Language();

        // Login check (for login link)
        if ($routeName == "login_check") {
            $session = $request->getSession();
            $flash = $session->getFlashBag();
            $user = $token->getUser();
            if (
                $request->query->get("action") == "activate" && // Activate user
                Config("REGISTER_ACTIVATE")
                && !IsEmpty(Config("USER_ACTIVATED_FIELD_NAME"))
                && !ConvertToBool($user->get(Config("USER_ACTIVATED_FIELD_NAME")))
            ) {
                if (Security()->activateUser($user)) { // Activate user
                    if (Config("REGISTER_AUTO_LOGIN")) {
                        if (Config("USE_TWO_FACTOR_AUTHENTICATION")) {
                            if (Profile()->setUser($user)->get2FAEnabled()) {
                                $session->set(SESSION_STATUS, "loggingin2fa");
                                $flash->add("success", $language->phrase("ActivateAccount")); // Set up user activated message
                                return new RedirectResponse(UrlFor("login2fa")); // Go to two factor authentication
                            }
                        }
                    } else { // If not auto login after activation
                        SecurityHelper()->logout(false);
                        FlashBag()->add("success", $language->phrase("ActivateAccount")); // Set up user activated message
                        return new RedirectResponse(UrlFor("login")); // Go to login page
                    }
                } else {
                    $flash->add("failure", $language->phrase("ActivateFailed")); // Set activation failure message
                }
            } else {
                $flash->add("success", $language->phrase("LoginLinkSuccess")); // Set up success message
            }
            return new RedirectResponse(UrlFor("login")); // Go to login page to continue login
        } elseif ($routeName == "login1fa") {
            // If JSON response expected
            if (IsJsonResponse()) {
                return new JsonResponse(["url" => UrlFor("login")]);
            }
        } elseif ($routeName == "loginldap") {
            // If JSON response expected
            if (IsJsonResponse()) {
                return new JsonResponse(["url" => UrlFor("login")]);
            } else {
                return new RedirectResponse(UrlFor("login")); // Go to login page
            }
        }

        // Return empty response => Continue to the original route
        return new Response();
    }
}
