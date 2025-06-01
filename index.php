<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use DI\ContainerBuilder;
use Tuupola\Middleware\CorsMiddleware;
use ErrorException;

// Autoload
require_once "vendor/autoload.php";

// Require files
require_once "src/constants.php";
require_once "src/config.php";
require_once "src/phpfn.php";
require_once "src/userfn.php";

// Dispatch configuration event
DispatchEvent(new ConfigurationEvent(Config()), ConfigurationEvent::NAME);

// Check PHP extensions
$exts = array_filter(Config("PHP_EXTENSIONS"), fn($ext) => !extension_loaded($ext), ARRAY_FILTER_USE_KEY);
if (count($exts)) {
    $exts = array_map(fn($ext) => '<p><a href="https://www.php.net/manual/en/book.' . $exts[$ext] . '.php" target="_blank">' . $ext . '</a></p>', array_keys($exts));
    die("<p>Missing PHP extension(s)! Please install or enable the following required PHP extension(s) first:</p>" . implode("", $exts));
} elseif (!defined("LIBXML_HTML_NODEFDTD")) {
    die('<p>Missing PHP <a href="https://www.php.net/manual/en/book.libxml.php" target="_blank">Libxml</a> extension (>= 2.7.8). Please install or enable it first.</p>');
}

// Environment
$isProduction = IsProduction();
$isDebug = IsDebug();

// Set warnings and notices as errors
if ($isDebug && Config("REPORT_ALL_ERRORS")) {
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
    set_error_handler(function ($severity, $message, $file, $line) {
        if (error_reporting() & $severity) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    });
}

// Instantiate PHP-DI container builder
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAttributes(true);

// Enable container compilation
if ($isProduction && Config("COMPILE_CONTAINER") && !IsRemote($cacheFolder = Config("CACHE_FOLDER"))) {
    $containerBuilder->enableCompilation(ServerMapPath($cacheFolder)); // local.filesystem not ready yet
}

// Add definitions
$containerBuilder->addDefinitions("src/definitions.php");

// Dispatch container build event
DispatchEvent(new ContainerBuildEvent($containerBuilder), ContainerBuildEvent::NAME);

// Build PHP-DI container instance
$container = $containerBuilder->build();

// Dispatch container built event
DispatchEvent(new ContainerBuiltEvent($container), ContainerBuiltEvent::NAME);

// Create application via container
$app = $container->get("app.main");

// Create request object
$Request = $container->get("request.creator")->createServerRequestFromGlobals();

// Set base path
$app->setBasePath(BasePath());

// Add body parsing middleware
$app->addBodyParsingMiddleware();

// Is API
$IsApi = IsApi();

// Add CSRF protection middleware
if (Config("CSRF_PROTECTION") && !$IsApi && !IsSamlResponse()) {
    $app->add($container->get(CsrfMiddleware::class));
}

// Add CSP middleware
if (Config("CSP")) {
    $app->add($container->get("csp.middleware"));
}

// Add CORS middleware
$app->add($container->get("cors.middleware"));

// Add routing middleware (after CORS middleware so routing is performed first)
$app->addRoutingMiddleware();

// Set route cache file
if ($isProduction && Config("USE_ROUTE_CACHE") && !IsRemote($cacheFolder = Config("CACHE_FOLDER")) && CreateDirectory($cacheFolder)) {
    $app->getRouteCollector()->setCacheFile(PrefixDirectoryPath($cacheFolder) . Config($IsApi ? "API_ROUTE_CACHE_FILE" : "ROUTE_CACHE_FILE"));
}

// Register routes (and add permission middleware)
if ($IsApi) {
    RouteAttributes::registerApiRoutes($app);
    (require_once "src/apiroutes.php")($app);
} else {
    RouteAttributes::registerRoutes($app);
    (require_once "src/routes.php")($app);
}

// Add session middleware
$app->add($container->get(SessionMiddleware::class));

// Add error middleware
$logErrors = $isDebug || Config("LOG_TO_FILE");
$app->addErrorMiddleware($isDebug, $logErrors, $logErrors) // $displayErrorDetails, $logErrors, $logErrorDetails
    ->setDefaultErrorHandler($container->get(HttpErrorHandler::class));

// Add maintenance middleware
if (Config("MAINTENANCE_MODE")) {
    $app->add($container->get(MaintenanceMiddleware::class));
}

// Add debug bar middleware
if ($isDebug && Config("DEBUG_BAR")) {
    $app->add($container->get(PhpDebugBarMiddleware::class));
}

// Run app
$app->run($Request);
