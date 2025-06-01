<?php

namespace PHPMaker2025\ucarsip;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use DebugBar\DebugBar;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\Bridge\MonologCollector;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\RawMessage;
use Throwable;

/**
 * PHP Debug Bar
 */
class PhpDebugBar extends DebugBar
{

    public function __construct(
        protected PhpHttpDriver $phpHttpDriver,
        protected LoggerInterface $logger,
        protected DebugStack $debugStack,
        protected string $headerName = 'phpdebugbar',
        protected string $variableName = 'phpdebugbar'
    ) {
        $this->setHttpDriver($phpHttpDriver);
        $this->addCollector(new PhpInfoCollector()); // 'php'
        $this->addCollector(new MessagesCollector()); // 'messages'
        $this->addCollector(new RequestDataCollector()); // 'request'
        // $this->addCollector(new TimeDataCollector()); // 'time'
        $this->addCollector(new MemoryCollector()); // 'memory'
        $this->addCollector(new ExceptionsCollector()); // 'exceptions'
        $this->addCollector(new MonologCollector($logger)); // 'monolog'
        $this->addCollector(new DoctrineCollector($debugStack)); // 'doctrine'
        $this->addCollector(new SymfonyMailCollector()); // 'symfonymailer_mails'
    }

    /**
     * Get header name
     *
     * @return string
     */
    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    /**
     * Set header name
     *
     * @param string $value Header name
     * @return static
     */
    public function setHeaderName(string $value): static
    {
        $this->headerName = $value;
        return $this;
    }

    /**
     * Return a JavaScript renderer (override)
     * @param ?string $baseUrl
     * @param ?string $basePath
     * @return JavascriptRenderer
     */
    public function getJavascriptRenderer($baseUrl = null, $basePath = null) // Note: Override, do not declare types
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = (new JavascriptRenderer($this, $baseUrl, $basePath))->setVariableName($this->variableName);
            $this->jsRenderer->setBaseUrl(PathJoin(BasePath(), $this->jsRenderer->getBaseUrl()));
            if (Config('NONCE')) {
                $this->jsRenderer->setCspNonce(Container('csp.middleware')->nonce());
            }
        }
        return $this->jsRenderer;
    }

    /**
     * Returns the variable name of the class instance
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * Sets the variable name of the class instance
     *
     * @param string $name
     */
    public function setVariableName(string $name): static
    {
        $this->variableName = $name;
        return $this;
    }

    /**
     * Add a message
     *
     * @param mixed $message Anything from an object to a string
     * @param string $label
     * @param bool $isString
     */
    public function addMessage(mixed $message, string $label = 'info', bool $isString = true): static
    {
        $this->getCollector('messages')->addMessage($message, $label, $isString);
        return $this;
    }

    /**
     * Add a failed message
     *
     * @param RawMessage $message
     */
    public function addFailedMessage(RawMessage $message): static
    {
        $this->getCollector('symfonymailer_mails')->addFailedMessage($message);
        return $this;
    }

    /**
     * Add a sent message
     *
     * @param SentMessage $message
     */
    public function addSentMessage(SentMessage $message): static
    {
        $this->getCollector('symfonymailer_mails')->addSentMessage($message);
        return $this;
    }

    /**
     * Add a Throwable
     *
     * @param Throwable $e
     */
    public function addThrowable(Throwable $e): static
    {
        $this->getCollector('exceptions')->addThrowable($e);
        return $this;
    }

    /**
     * Initialize the session for stacked data
     *
     * @return HttpDriverInterface
     * @throws DebugBarException
     */
    protected function initStackSession() // Note: Override, do not declare types
    {
        $http = $this->getHttpDriver();

        // if (!$http->isSessionStarted()) {
        //     throw new DebugBarException("Session must be started before using stack data in the debug bar");
        // }
        if (!$http->hasSessionValue($this->stackSessionNamespace)) {
            $http->setSessionValue($this->stackSessionNamespace, []);
        }
        return $http;
    }

    /**
     * Sends the data through the HTTP headers (override)
     *
     * @param ?bool $useOpenHandler
     * @param ?string $headerName Header name in header case
     * @param integer $maxHeaderLength
     * @return $this
     */
    public function sendDataInHeaders($useOpenHandler = null, $headerName = null, $maxHeaderLength = 4096) // Note: Override, do not declare types
    {
        return parent::sendDataInHeaders($useOpenHandler, HeaderCase($headerName ?? $this->headerName), $maxHeaderLength);
    }
}
