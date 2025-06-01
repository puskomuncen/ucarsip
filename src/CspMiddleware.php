<?php

namespace PHPMaker2025\ucarsip;

use ParagonIE\CSPBuilder\CSPBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Stream;

/**
 * CSP middleware
 * Based on https://github.com/middlewares/csp
 */
class CspMiddleware implements MiddlewareInterface
{
    private bool $legacy = true;
    private ?string $nonce = null;

    public static function createFromFile(string $path): self
    {
        return new static(CSPBuilder::fromFile($path));
    }

    public static function createFromData(array $data): self
    {
        return new static(new CSPBuilder($data));
    }

    /**
     * Set CSPBuilder
     */
    public function __construct(private ?CSPBuilder $builder = null)
    {
        $this->builder ??= self::createBuilder();
    }

    /**
     * Set if include legacy headers for old browsers
     */
    public function legacy(bool $legacy = true): self
    {
        $this->legacy = $legacy;
        return $this;
    }

    /**
     * Check if response is HTML
     *
     * @param ResponseInterfac $response
     * @return bool
     */
    protected function isHtmlResponse(ResponseInterface $response): bool
    {
        return strpos($response->getHeaderLine('Content-Type'), 'text/html') !== false;
    }

    /**
     * Process a request and return a response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->builder->compile();
        return $this->builder->injectCSPHeader($response, $this->legacy);
    }

    /**
     * Create a default CSP builder
     */
    private static function createBuilder(): CSPBuilder
    {
        return new CSPBuilder([
            'script-src' => ['self' => true],
            'object-src' => ['self' => true],
            'frame-ancestors' => ['self' => true],
        ]);
    }

    /**
     * Get CSP Builder
     *
     * @return CSPBuilder
     */
    public function getBuilder(): CSPBuilder
    {
        return $this->builder;
    }

    /**
     * Add a new nonce to the existing CSP
     *
     * @return ?string Nonce generated
     */
    public function nonce(): ?string
    {
        if (Config('NONCE') && !$this->nonce) {
            $this->builder->setAllowUnsafeInline('script-src', false);
            $this->builder->setStrictDynamic('script-src', true);
            $this->nonce = $this->builder->nonce('script-src');
            $this->builder->setAllowUnsafeInline('style-src', false);
            $this->builder->setStrictDynamic('style-src', true);
            $this->builder->nonce('style-src', $this->nonce);
        }
        return $this->nonce;
    }
}
