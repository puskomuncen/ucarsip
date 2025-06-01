<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Session handler
 */
class SessionHandler
{

    public function __construct(
        protected CsrfMiddleware $csrf,
        protected ResponseFactoryInterface $responseFactory
    ) {
    }

    public function __invoke(): Response
    {
        if (ob_get_length()) {
            ob_end_clean();
        }
        $response = $this->responseFactory->createResponse();
        return $response->withJson([
            $this->csrf->getTokenNameKey() => $this->csrf->getTokenName(),
            $this->csrf->getTokenValueKey() => $this->csrf->getTokenValue(),
            "JWT" => GetJwtToken()
        ]);
    }
}
