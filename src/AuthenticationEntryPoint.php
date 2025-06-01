<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if ($authException) {
            $request->getSession()->getFlashBag()->add("failure", DeniedMessage()); // Set no permission
        }
        if (
            IsJsonResponse() || // JSON response expected
            IsModal() && // Modal
            !(RouteName() == "login" && Config("USE_MODAL_LOGIN")) // Not modal login
        ) {
            return new JsonResponse(["url" => UrlFor("login")]);
        }

        // Redirect to login page
        return new RedirectResponse(UrlFor("login"));
    }
}
