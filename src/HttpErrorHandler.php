<?php

namespace PHPMaker2025\ucarsip;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Throwable;
use ErrorException;
use RuntimeException;

class HttpErrorHandler extends ErrorHandler
{
    protected array $error;

    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        protected Language $language,
        ?LoggerInterface $logger = null,
        protected string $layoutTemplate = "",
        protected string $errorTemplate = "",
        protected bool $showSourceCode = false
    ) {
        $logger ??= Container("app.logger");
        parent::__construct($callableResolver, $responseFactory, $logger);
    }

    // Get layout template
    public function getLayoutTemplate(): string
    {
        return $this->layoutTemplate;
    }

    // Set layout template
    public function setLayoutTemplate(string $template): static
    {
        $this->layoutTemplate = $template;
        return $this;
    }

    // Get error template
    public function getErrorTemplate(): string
    {
        return $this->errorTemplate;
    }

    // Set error template
    public function setErrorTemplate(string $template): static
    {
        $this->errorTemplate = $template;
        return $this;
    }

    // Get show source code
    public function getShowSourceCode(): bool
    {
        return $this->showSourceCode;
    }

    // Set show source code
    public function setShowSourceCode(bool $value): static
    {
        $this->showSourceCode = $value;
        return $this;
    }

    // Log error
    protected function logError(string $err): void
    {
        $this->logger?->error($err);
    }

    // Set error
    protected function setError(Throwable $exception): void
    {
        $this->error = [
            "statusCode" => 200,
            "error" => [
                "class" => "text-danger",
                "type" => $this->language->phrase("Error"),
                "description" => $this->language->phrase("ServerError"),
            ],
        ];
        if ($exception instanceof RuntimeException) {
            $description = $exception->getMessage();
            if (
                $exception instanceof HttpNotFoundException || // 404
                $exception instanceof HttpMethodNotAllowedException || // 405
                $exception instanceof HttpUnauthorizedException || // 401
                $exception instanceof AuthenticationException || // 401
                $exception instanceof HttpForbiddenException || // 403
                $exception instanceof AccessDeniedException || // 403
                $exception instanceof HttpBadRequestException || // 400
                $exception instanceof HttpInternalServerErrorException || // 500
                $exception instanceof HttpNotImplementedException || // 501
                $exception instanceof HttpServiceUnavailableException // 503
            ) {
                $statusCode = $exception->getCode();
                $type = $this->language->phrase($statusCode);
                $description = $description ?: $this->language->phrase($statusCode . "Desc");
                $this->error = [
                    "statusCode" => $statusCode,
                    "error" => [
                        "class" => ($exception instanceof HttpInternalServerErrorException) ? "text-danger" : "text-warning",
                        "type" => $type,
                        "description" => $description,
                    ],
                ];
            }
        }
        if (IsDebug() || IsDevelopment()) {
            if (!$exception instanceof RuntimeException && $exception instanceof Throwable) {
                if ($exception instanceof ErrorException) {
                    $severity = $exception->getSeverity();
                    $this->error["error"]["class"] = "text-warning";
                    if ($severity === E_WARNING) {
                        $this->error["error"]["type"] = $this->language->phrase("Warning");
                    } elseif ($severity === E_NOTICE) {
                        $this->error["error"]["type"] = $this->language->phrase("Notice");
                    }
                }
                $description = $exception->getFile() . "(" . $exception->getLine() . "): " . $exception->getMessage();
                $this->error["error"]["description"] = $description;
            }
            if ($this->displayErrorDetails) {
                $this->error["error"]["trace"] = $exception->getTraceAsString();
            }
        } else {
            $this->error["error"]["class"] = "text-danger";
            $this->error["error"]["description"] = $this->language->phrase("ServerError");
        }
    }

    // Respond
    protected function respond(): Response
    {
        global $Error, $Title;
        $exception = $this->exception;

        // Set error message
        $this->setError($exception);

        // Create response object
        $response = $this->responseFactory->createResponse();

        // Show error as JSON
        $routeName = RouteName() ?? "";
        if (
            IsApi() || // API request
            preg_match('/\bpreview$/', $routeName) || // Preview page
            $this->request->getParam("modal") == "1" || // Modal request
            $this->request->getParam("d") == "1" // Drilldown request
        ) {
            return $response->withJson($this->error, $this->error["statusCode"] ?? null);
        }
        if ($this->contentType == "text/html") { // HTML
            $Title = $this->language->phrase("Error");
            if ($this->showSourceCode && $this->displayErrorDetails && !IsProduction()) { // Only show code if is debug and not production
                $handler = new PrettyPageHandler();
                $handler->setPageTitle($Title);
                $whoops = new Run();
                $whoops->allowQuit(false);
                $whoops->writeToOutput(false);
                $whoops->pushHandler($handler);
                $html = $whoops->handleException($exception);
            } else {
                $view = Container("app.view");
                $Error = $this->error;
                try { // Render with layout
                    $view->setLayout($this->layoutTemplate);
                    if ($this->displayErrorDetails) {
                        $Error["error"]["trace"] = false; // false => show trace in debug bar
                        DebugBar()?->addThrowable($exception); // Add Throwable to debug bar
                    }
                    $html = $view->fetch($this->errorTemplate, $GLOBALS, true); // Use layout
                } catch (Throwable $e) { // Error without layout
                    if ($this->displayErrorDetails) {
                        DebugBar()?->addThrowable($e); // Add Throwable to debug bar
                    }
                    $this->setError($e);
                    $Error = $this->error;
                    $basePath = BasePath(true);
                    $html = '<html>
    <head>
        <meta charset="utf-8">
       <meta name="viewport" content="width=device-width, initial-scale=1">
       <title>' . $Title . '</title>
       <link rel="stylesheet" href="' . $basePath . 'adminlte3/css/' . CssFile("adminlte.css") . '">
       <link rel="stylesheet" href="' . $basePath . 'plugins/fontawesome-free/css/all.min.css">
       <link rel="stylesheet" href="' . $basePath . CssFile(Config("PROJECT_STYLESHEET_FILENAME")) . '">
    </head>
    <body class="container-fluid">
        <div>
            ' . $view->fetch($this->errorTemplate, $GLOBALS) . '
        </div>
    </body>
</html>';
                }
            }
            return $response->write($html);
        } else { // JSON
            DebugBar()?->addThrowable($exception)->sendDataInHeaders(); // Add exception to debug bar
            return $response->withJson($this->error, $this->error["statusCode"] ?? null);
        }
    }
}
