<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Csrf\Guard;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use ArrayAccess;
use Countable;
use Exception;
use Iterator;
use RuntimeException;

/**
 * CSRF protection middleware
 * Based on https://github.com/slimphp/Slim-Csrf
 */
class CsrfMiddleware extends Guard
{
    protected bool $isEnabled = false;

    /**
     * Excluded route names
     */
    public static array $excluded = [
        "login", // Public access
        "login1fa", // Public access
        "login2fa", // Public access
        "loginldap", // Public access
        "api.login", // Protected by JWT
        "api.add", // Protected by JWT
        "api.edit", // Protected by JWT
        "api.delete", // Protected by JWT
        "api.register", // Protected by JWT
        "api.permissions", // Protected by JWT
        "api.lookup", // Protected by JWT
        "api.push", // Protected by JWT
        "api.upload", // Protected by JWT
        "api.jupload" // Protected by session id checking
    ];

    /**
     * Constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param string $prefix
     * @param SessionInterface $session
     * @param Language $language
     * @param callable|null $failureHandler
     * @param int $storageLimit
     * @param int $strength
     * @param bool $persistentTokenMode
     *
     * @throws RuntimeException if the session cannot be found
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        string $prefix,
        protected SessionInterface $session, // Replace $storage by $session
        protected Language $language,
        ?callable $failureHandler = null,
        int $storageLimit = 200,
        int $strength = 16,
        bool $persistentTokenMode = true, // Unique per user session
    ) {
        if (!$session->has($prefix) || !is_array($session->get($prefix))) {
            $session->set($prefix, []);
        }
        $storage = $session->get($prefix);
        $failureHandler ??= fn(ServerRequestInterface $request, RequestHandlerInterface $handler)
            => throw new HttpBadRequestException($request, $this->language->phrase('InvalidPostRequest'));
        parent::__construct($responseFactory, $prefix, $storage, $failureHandler, $storageLimit, $strength, $persistentTokenMode);
        // Make sure token is ready to use
        if ($persistentTokenMode && !$this->loadLastKeyPair()) {
            $this->generateToken();
        }
    }

    /**
     * Get token name header
     *
     * @return string
     */
    public function getTokenNameHeader(): string
    {
        return HeaderCase($this->getTokenNameKey());
    }

    /**
     * Get token value header
     *
     * @return string
     */
    public function getTokenValueHeader(): string
    {
        return HeaderCase($this->getTokenValueKey());
    }

    /**
     * Check if enabled for current request
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Process
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     *
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeName = RouteName($request);
        $this->isEnabled = !in_array($routeName, self::$excluded);
        if (!$this->isEnabled) {
            return $handler->handle($request);
        }
        try {
            $body = $request->getParsedBody();
            $name = null;
            $value = null;
            if (is_array($body)) {
                $name = $body[$this->getTokenNameKey()] ?? null;
                $value = $body[$this->getTokenValueKey()] ?? null;
            }
            if ($name === null && $value === null) {
                // DELETE request may not have a request body. Supply token by headers
                $name = $request->getHeader($this->getTokenNameHeader())[0] ?? null;
                $value = $request->getHeader($this->getTokenValueHeader())[0] ?? null;
            }
            if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
                $isValid = $this->validateToken((string) $name, (string) $value);
                if ($isValid && !$this->persistentTokenMode) {
                    // successfully validated token, so delete it if not in persistentTokenMode
                    $this->removeTokenFromStorage($name);
                }
                if ($name === null || $value === null || !$isValid) {
                    $request = $this->appendNewTokenToRequest($request);
                    return $this->handleFailure($request, $handler);
                }
            } else {
                // Method is GET/OPTIONS/HEAD/etc, so do not accept the token in the body of this request
                if ($name !== null) {
                    $this->enforceStorageLimit();
                    return $this->handleFailure($request, $handler);
                }
            }
            if (!$this->persistentTokenMode || !$this->loadLastKeyPair()) {
                $request = $this->appendNewTokenToRequest($request);
            } else {
                $pair = $this->loadLastKeyPair() ? $this->keyPair : $this->generateToken();
                $request = $this->appendTokenToRequest($request, $pair);
            }
            $this->enforceStorageLimit();
            $GLOBALS['TokenNameKey'] = $this->getTokenNameKey();
            $GLOBALS['TokenName'] = $this->getTokenName();
            $GLOBALS['TokenValueKey'] = $this->getTokenValueKey();
            $GLOBALS['TokenValue'] = $this->getTokenValue();
            return $handler->handle($request);
        } finally {
            $this->session->set($this->prefix, $this->storage);
        }
    }
}
