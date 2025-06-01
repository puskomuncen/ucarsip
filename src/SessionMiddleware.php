<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Dflydev\FigCookies\SetCookies;

/**
 * Session Middleware
 */
class SessionMiddleware implements MiddlewareInterface
{
    // Constructor
    public function __construct(
        protected SessionInterface $session,
    ) {
    }

    /**
     * Invoke middleware
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        global $SetCookies;

        // Sessions are only started if you read or write from it
        // if (!$session->isStarted()) {
        //     $session->start();
        // }
        $response = $handler->handle($request);
        if ($this->session->isStarted()) {
            $this->session->save();
        }

        // Render the Set-Cookie headers (Note: Do not use renderIntoSetCookieHeader())
        foreach ($SetCookies->getAll() as $setCookie) {
            $response = $response->withAddedHeader(SetCookies::SET_COOKIE_HEADER, (string)$setCookie);
        }
        return $response;
    }
}
