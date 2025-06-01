<?php

namespace PHPMaker2025\ucarsip;

use Composer\Script\Event;
use Symfony\Component\Finder\Finder;
use Symfony\Component\VarExporter\VarExporter;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Config\FileLocator;
use Slim\App;
use DI\Container as Container;

class RouteAttributes
{
    private static ?array $routes = null;
    public static string $CONTROLLERS_FOLDER = __DIR__ . "/../controllers";
    public static string $CACHE_FOLDER = __DIR__ . "/../log/cache/"; // Cache folder
    public static string $ROUTE_ATTRIBUTES_FILE = "RouteAttributes.php"; // Route attributes file under CACHE_FOLDER
    public static string $API_ROUTE_ATTRIBUTES_FILE = "ApiRouteAttributes.php"; // API Route attributes file under CACHE_FOLDER

    /**
     * Is remote path
     *
     * @param string $path Path
     * @return bool
     */
    protected static function isRemote($path): bool
    {
        return str_contains($path, "://");
    }

    /**
     * Create folder
     *
     * @param string $dir Directory
     * @param int $mode Permissions
     * @return bool
     */
    public static function createFolder($dir, $mode = 0): bool
    {
        return is_dir($dir) || ($mode ? @mkdir($dir, $mode, true) : (@mkdir($dir, 0777, true) || @mkdir($dir, 0666, true) || @mkdir($dir, 0444, true)));
    }

    /**
     * Get route attributes from controllers folder
     *
     * @param ?bool $api For API or not. If boolean, returns route collection. If null, returns null.
     * @return ?RouteCollection
     */
    public static function getRoutes(?bool $api = null): ?RouteCollection
    {
        try {
            $loader = new AttributeDirectoryLoader(
                new FileLocator(self::$CONTROLLERS_FOLDER),
                new AttributeRouteControllerLoader()
            );
            $routes = $loader->load(self::$CONTROLLERS_FOLDER); // All routes
            $apiRoutes = new RouteCollection(); // For API routes only
            foreach ($routes as $name => $route) {
                if (str_starts_with($route->getPath(), "/api/")) { // API route
                    $apiRoutes->add($name, $route); // Add to API route collection
                    $routes->remove($name); // Remove from route collection of all routes
                }
            }
            if ($api === null || $api === true) {
                if (!self::isRemote(self::$CACHE_FOLDER) && self::createFolder(self::$CACHE_FOLDER)) {
                    file_put_contents(self::$CACHE_FOLDER . self::$API_ROUTE_ATTRIBUTES_FILE, "<?php return " . VarExporter::export($apiRoutes) . ";");
                    if ($api === true) {
                        return $apiRoutes;
                    }
                }
            }
            if ($api === null || $api === false) {
                if (!self::isRemote(self::$CACHE_FOLDER) && self::createFolder(self::$CACHE_FOLDER)) {
                    file_put_contents(self::$CACHE_FOLDER . self::$ROUTE_ATTRIBUTES_FILE, "<?php return " . VarExporter::export($routes) . ";");
                    if ($api === false) {
                        return $routes;
                    }
                }
            }
            return null;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Register route collection
     *
     * @param App $app Slim app
     * @return void
     */
    public static function registerRouteCollection(App $app, RouteCollection $routes): void
    {
        $container = $app->getContainer();
        foreach ($routes as $name => $route) {
            $defaults = $route->getDefaults();
            $middlewares = (array)($defaults["middlewares"] ?? []);
            $route = $app->map($route->getMethods(), $route->getPath(), $defaults["_controller"]);
            foreach ($middlewares as $middleware) {
                $route->add($container->get($middleware));
            }
            $route->setName($name);
        }
    }

    /**
     * Register routes
     *
     * @param App $app Slim app
     * @return void
     */
    public static function registerRoutes(App $app): void
    {
        $cacheFile = self::$CACHE_FOLDER . self::$ROUTE_ATTRIBUTES_FILE;
        if (!self::isRemote($cacheFile) && file_exists($cacheFile)) {
            $routes = require $cacheFile;
        } else {
            $routes = self::getRoutes(false);
        }
        // $app->getContainer()->set("route.collection", $routes); // Currently not used
        self::registerRouteCollection($app, $routes);
    }

    /**
     * Register API routes
     *
     * @param App $app Slim app
     * @return void
     */
    public static function registerApiRoutes(App $app): void
    {
        $cacheFile = self::$CACHE_FOLDER . self::$API_ROUTE_ATTRIBUTES_FILE;
        if (!self::isRemote($cacheFile) && file_exists($cacheFile)) {
            $routes = require $cacheFile;
        } else {
            $routes = self::getRoutes(true);
        }
        // $app->getContainer()->set("api.route.collection", $routes); // Currently not used
        self::registerRouteCollection($app, $routes);
    }

    /**
     * Dispatch route attributes to cache file
     * Note: Do NOT dispatch FastRoute cache, or routes without attributes will not be included in cache.
     *
     * @param $event Composer event
     * @return void
     */
    public static function dispatch(Event $event): void
    {
        self::getRoutes();
        if (file_exists(self::$CACHE_FOLDER . self::$ROUTE_ATTRIBUTES_FILE)) {
            echo self::$ROUTE_ATTRIBUTES_FILE . " generated\n";
        }
        if (file_exists(self::$CACHE_FOLDER . self::$API_ROUTE_ATTRIBUTES_FILE)) {
            echo self::$API_ROUTE_ATTRIBUTES_FILE . " generated\n";
        }
    }
}
