<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Security\Http\RememberMe\ResponseListener;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 * Authentication middleware (by Symfony Security)
 */
class AuthenticationMiddleware
{
    /**
     * Invoke
     *
     * @param Request $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Set up request
        $GLOBALS["Request"] = $request;
        $cookies = [];

        // Symfony security
        $httpFoundationFactory = new HttpFoundationFactory();
        $symfonyRequest = $httpFoundationFactory->createRequest($request);
        $symfonyRequest->setSession(Session());

        // Kernel
        $kernel = SecurityContainer("http_kernel");

        // Handle Symfony request
        $symfonyResponse = $kernel->handle($symfonyRequest, catch: false); // Don't catch error

        // Copy cookies (e.g. "REMEMBERME") from Symfony response
        $cookies = $symfonyResponse->headers->getCookies();

        // Copy attributes added by Symfony
        foreach ($symfonyRequest->attributes->all() as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        // Check redirect/JSON response
        if ($symfonyResponse instanceof RedirectResponse || $symfonyResponse instanceof JsonResponse) {
            $psr17Factory = Container("psr17.factory");
            $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
            return $psrHttpFactory->createResponse($symfonyResponse);
        }

        // Handle original request
        $response = $handler->handle($request);

        // Set cookie
        foreach ($cookies as $cookie) {
            $response = $response->withHeader("Set-Cookie", (string)$cookie);
        }
        return $response;
    }
}
