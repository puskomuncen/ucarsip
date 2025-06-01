<?php

/**
 * PHPMaker functions
 * Copyright (c) e.World Technology Limited. All rights reserved.
*/

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ResponseFactoryInterface;
use FastRoute\RouteParser\Std;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Routing\RouteContext;
use Slim\Routing\RouteCollector;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteParserInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Result;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use DiDom\Document;
use DiDom\Element;
use Illuminate\Support\Collection;
use Illuminate\Encryption\Encrypter;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Contracts\Encryption\DecryptException;
use Dflydev\DotAccessData\Data;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Hautelook\Phpass\PasswordHash;
use AutoMapperPlus\Configuration\AutoMapperConfig;
use AutoMapperPlus\AutoMapper;
use Symfony\Component\Finder\Finder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Http\LoginLink;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\DirectoryListing;
use DebugBar\JavascriptRenderer;
use function Symfony\Component\String\u;
use function Symfony\Component\String\b;
use function Symfony\Component\String\s;
use ReflectionClass;
use NumberFormatter;
use IntlDateFormatter;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use Exception;
use Throwable;

// Custom types
Type::addType("timetz", "Doctrine\\DBAL\\Types\\VarDateTimeType"); // "timetz" type
Type::addType("geometry", "PHPMaker2025\\ucarsip\\GeometryType"); // "geometry" type
Type::addType("geography", "PHPMaker2025\\ucarsip\\GeographyType"); // "geography" type
Type::addType("hierarchyid", "PHPMaker2025\\ucarsip\\HierarchyIdType"); // "hierarchyid" type
Type::addType("bytes", "PHPMaker2025\\ucarsip\\BytesType"); // "bytes" type

/**
 * Get environment variable
 *
 * @param string $name Name
 * @param mixed $def Default value
 * @return mixed
 */
function Env(string $name, mixed $def = null): mixed
{
    return $_ENV[$name] ?? $def;
}

/**
 * Get/Set Configuration
 * Note: Do NOT use container which might not have been built
 *
 * @return mixed
 */
function Config(mixed ...$args): mixed
{
    global $CONFIG, $ConfigData;
    $ConfigData ??= new Data($CONFIG);
    $numargs = count($args);
    if ($numargs == 0) {
        return $ConfigData;
    } elseif ($numargs == 1) { // Get
        $name = $args[0];
        // Check global variables
        if (isset($GLOBALS[$name])) { // Allow overriding by global variable
            return $GLOBALS[$name];
        }
        // Check environment variables
        if (isset($_ENV[$name])) { // Allow overriding by environment variables
            return $_ENV[$name];
        }
        // Check constants
        if (defined(PROJECT_NAMESPACE . $name)) {
            return constant(PROJECT_NAMESPACE . $name);
        }
        // Check config data
        if ($ConfigData->has($name)) {
            return $ConfigData->get($name);
        }
        return null;
    } elseif ($numargs == 2) { // Set
        list($name, $newValue) = $args;
        $oldValue = $ConfigData->has($name) ? $ConfigData->get($name) : null;
        if (is_array($oldValue) && is_array($newValue)) {
            $ConfigData->set($name, array_replace_recursive($oldValue, $newValue));
        } else {
            $ConfigData->set($name, $newValue);
        }
        return $newValue;
    }
}

/**
 * Event dispatcher
 *
 * @return void
 */
function EventDispatcher(): EventDispatcher
{
    return $GLOBALS["EventDispatcher"];
}

/**
 * Add event listener
 *
 * @param string $eventName Event name
 * @param callable|array $listener Listener
 * @param int $priority Priority
 * @return void
 */
function AddListener(string $eventName, callable|array $listener, int $priority = 0)
{
    EventDispatcher()->addListener($eventName, $listener, $priority);
}

/**
 * Dispatch event
 *
 * @param Event $event Event
 * @param string $eventName Event name
 * @return Event
 */
function DispatchEvent(object $event, ?string $eventName = null): object
{
    return EventDispatcher()->dispatch($event, $eventName);
}

/**
 * Is development
 *
 * @return bool
 */
function IsDevelopment(): bool
{
    return Config("ENVIRONMENT") == "development";
}

/**
 * Is production
 *
 * @return bool
 */
function IsProduction(): bool
{
    return Config("ENVIRONMENT") == "production";
}

/**
 * Is debug mode
 *
 * @return bool
 */
function IsDebug(): bool
{
    return Config("DEBUG");
}

/**
 * Get request object
 *
 * @return Request
 */
function Request(): Request
{
    return $GLOBALS["Request"];
}

/**
 * Get response object (return by reference)
 *
 * @return Response
 */
function &Response(): Response
{
    return $GLOBALS["Response"];
}

/**
 * Get response factory
 *
 * @return ResponseFactoryInterface
 */
function ResponseFactory(): ResponseFactoryInterface
{
    return Container(ResponseFactoryInterface::class);
}

/**
 * Get notifier
 *
 * @return Notifier
 */
function Notifier(): NotifierInterface
{
    return Container(NotifierInterface::class);
}

/**
 * Get Container
 *
 * @return mixed
 */
function Container(mixed ...$args): mixed
{
    global $container;
    if (!$container) {
        return null;
    }
    $numargs = count($args);
    if ($numargs == 1) { // Get
        if (is_string($args[0])) {
            $name = $args[0];
            if ($container->has($name)) {
                return $container->get($name);
            } else {
                $class = PROJECT_NAMESPACE . $name;
                if (class_exists($class)) {
                    $obj = $container->make($class);
                    $container->set($name, $obj);
                    return $obj;
                }
            }
        } elseif (is_array($args[0])) {
            foreach ($args[0] as $key => $value) {
                $container->set($key, $value);
            }
        }
        return null;
    } elseif ($numargs == 2) { // Set
        $container->set($args[0], $args[1]);
    }
    return $container;
}

/**
 * Security container (service locator)
 *
 * @return SecurityServiceLocator|object|null
 */
function SecurityContainer(?string $id = null): ?object
{
    $locator = Container("security.container")->get(SecurityServiceLocator::class);
    if ($id === null) {
        return $locator;
    }
    try {
        return $locator?->has($id) ? $locator->get($id) : null;
    } catch (\Exception $e) {
        if (IsDebug()) {
            LogError($e->getMessage());
        }
        return null;
    }
}

/**
 * Symfony request
 *
 * @return Symfony\Component\HttpFoundation\Request
 */
function SymfonyRequest(): ?SymfonyRequest
{
    return SecurityContainer("request_stack")?->getCurrentRequest();
}

/**
 * Symfony security helper
 *
 * @return Symfony\Bundle\SecurityBundle\Security
 */
function SecurityHelper(): Security
{
    return SecurityContainer("security.helper");
}

/**
 * Get last authentication error
 *
 * @return ?AuthenticationException
 */
function GetLastAuthenticationError(bool $clearSession = true): ?AuthenticationException
{
    return SecurityContainer("security.authentication_utils")->getLastAuthenticationError($clearSession);
}

/**
 * Get last user name
 *
 * @return string
 */
function GetLastUsername(): string
{
    return SecurityContainer("security.authentication_utils")->getLastUsername();
}

/**
 * Symfony Ldap
 *
 * @return ?Symfony\Component\Ldap\Ldap
 */
function Ldap(): ?Ldap
{
    return null;
}

/**
 * Get CSRF middleware
 *
 * @return CsrfMiddleware
 */
function Csrf(): CsrfMiddleware
{
    return Container(CsrfMiddleware::class);
}

/**
 * Get CSP nonce attribute
 *
 * @return string
 */
function Nonce(): string
{
    $nonce = Container("csp.nonce");
    return $nonce ? ' nonce="' . $nonce . '"' : "";
}

/**
 * Slim app
 *
 * @return App
 */
function App(): App
{
    return Container("app.main");
}

/**
 * Route collector
 *
 * @return RouteCollector
 */
function RouteCollector(): RouteCollector
{
    return App()->getRouteCollector();
}

/**
 * Route context
 *
 * @param ?Request $request Request
 * @return ?RouteContext
 */
function RouteContext(?Request $request = null): ?RouteContext
{
    try {
        return RouteContext::fromRequest($request ?? Request());
    } catch (RuntimeException $e) { // Cannot create RouteContext before routing has been completed
        return null;
    }
}

/**
 * Route parser
 *
 * @param ?Request $request Request
 * @return ?RouteParserInterface
 */
function RouteParser(?Request $request = null): ?RouteParserInterface
{
    return RouteContext($request)?->getRouteParser();
}

/**
 * Routing result
 *
 * @param ?Request $request Request
 * @return ?RoutingResults
 */
function RoutingResults(?Request $request = null): ?RoutingResults
{
    return RouteContext($request)?->getRoutingResults();
}

/**
 * Get route
 *
 * @param ?Request $request Request
 * @return ?RouteInterface
 */
function GetRoute(?Request $request = null): ?RouteInterface
{
    return RouteContext($request)?->getRoute();
}

/**
 * Get base path
 *
 * @param ?Request $request Request
 * @return ?string
 */
function GetBasePath(?Request $request = null): ?string
{
    return RouteContext($request)?->getBasePath();
}

/**
 * Route name
 *
 * @param ?Request $request Request
 * @return ?string
 */
function RouteName(?Request $request = null): ?string
{
    return GetRoute($request)?->getName();
}

/**
 * Get URL from route name
 *
 * @param ?string $routeName Route name
 * @param array $data Route data
 * @param array $queryParams Query parameters
 * @return string URL
 */
function UrlFor(?string $routeName, array $data = [], array $queryParams = []): string
{
    return $routeName ? RouteParser()->urlFor($routeName, $data, $queryParams) : "";
}

/**
 * Get relative URL from route name
 *
 * @param ?string $routeName Route name
 * @param array $data Route data
 * @param array $queryParams Query parameters
 * @return string URL
 */
function RelativeUrlFor(?string $routeName, array $data = [], array $queryParams = []): string
{
    return $routeName ? RouteParser()->relativeUrlFor($routeName, $data, $queryParams) : "";
}

/**
 * Get full URL from route name
 *
 * @param ?string $routeName Route name
 * @param array $data Route data
 * @param array $queryParams Query parameters
 * @return string URL
 */
function FullUrlFor(?string $routeName, array $data = [], array $queryParams = []): string
{
    global $Request;
    return $routeName ? RouteParser()->fullUrlFor($Request->getUri(), $routeName, $data, $queryParams) : "";
}

/**
 * Get path (no query parameters) from route name
 *
 * @param string $routeName Route name
 * @param array $data Route data
 * @param bool $withOptionalParameters Whether with optional parameters
 * @return string Path
 */
function PathFor(?string $routeName, array $data = [], bool $withOptionalParameters = true): string
{
    if (!$routeName) {
        return "";
    }
    $route = RouteCollector()->getNamedRoute($routeName);
    if (!$route) {
        return "";
    }
    if ($withOptionalParameters) { // With optional parameters
        return rtrim(UrlFor($routeName, $data), "/"); // No trailing "/"
    } else { // Without optional parameters
        $pattern = $route->getPattern();
        $expressions = Container(Std::class)->parse($pattern);
        $expression = $expressions[0]; // The least specific expression (no optional parameters)
        $segments = [];
        $segmentName = "";
        foreach ($expression as $segment) {
            // Each $segment is either a string or an array (representing a placeholder)
            if (is_string($segment)) {
                $segments[] = $segment;
                continue;
            }
            // If no data element for this segment in the provided $data
            if (!array_key_exists($segment[0], $data)) {
                throw new \InvalidArgumentException("Missing data for URL segment: " . $segment[0]);
            }
            $segments[] = $data[$segment[0]];
        }
        return rtrim(implode("", $segments), "/"); // No trailing "/"
    }
}

/**
 * Get base path
 *
 * @param bool $withTrailingDelimiter
 * @return string
 */
function BasePath(bool $withTrailingDelimiter = false): string
{
    $scriptName = ServerVar("SCRIPT_NAME");
    $basePath = str_replace("\\", "/", dirname($scriptName));
    if (strlen($basePath) > 1) {
        return $withTrailingDelimiter ? IncludeTrailingDelimiter($basePath, false) : $basePath;
    }
    return $withTrailingDelimiter ? IncludeTrailingDelimiter($basePath, false) : ""; // Root folder "/"
}

/**
 * Get app URL (domain URL + base path)
 *
 * @param bool $withTrailingDelimiter
 * @return string
 */
function AppUrl(bool $withTrailingDelimiter = false): string
{
    return DomainUrl() . BasePath($withTrailingDelimiter);
}

/**
 * Redirect to URL
 *
 * @param string $url URL
 */
function Redirect(string $url): void
{
    global $Response;
    if ($Response instanceof Response) {
        $Response = $Response->withRedirect($url, Config("REDIRECT_STATUS_CODE"));
    }
}

/**
 * Is API request
 *
 * @return bool
 */
function IsApi(): bool
{
    global $IsApi;
    return $IsApi ?? str_starts_with(ServerVar("REQUEST_URI"), GetUrl("api/"));
}

/**
 * Is modal response
 *
 * @return bool
 */
function IsModal(): bool
{
    return ConvertToBool(Param("modal"));
}

/**
 * Is JSON response (request accepts JSON)
 *
 * @return bool
 */
function IsJsonResponse(): bool
{
    return IsApi()
        || ConvertToBool(Param("json"))
        || preg_match('/\bapplication\/json\b/', Request()->getHeaderLine("Accept") ?? "");
}

/**
 * Check if response is JSON
 *
 * @return bool
 */
function HasJsonResponse(): bool
{
    global $Response;
    return $Response instanceof Response && StartsString("application/json", $Response->getHeaderLine("Content-type") ?? "") && $Response->getBody()->getSize() > 0;
}

/**
 * Create JWT token
 *
 * @param array $values Values to be encoded
 * @param int $expiry Expiry time (seconds)
 * @return string JWT token
 */
function CreateJwt(array $values, int $expiry = 0): string
{
    $tokenId = base64_encode(NewGuid());
    $issuedAt = time();
    $notBefore = $issuedAt + Config("JWT.NOT_BEFORE_TIME"); // Adding not before time (seconds)
    $defExpiry = $notBefore + Config("JWT.EXPIRE_TIME"); // Adding expire time (seconds), default expiry time
    $serverName = ServerVar("SERVER_NAME");

    // Override default expiry time
    if ($expiry > 0) {
        $notBefore = $issuedAt;
        $exp = $notBefore + $expiry;
    } else {
        $exp = $defExpiry;
    }

    // Create the token as an array
    return \Firebase\JWT\JWT::encode(
        [
            "iat" => $issuedAt, // Issued at: time when the token was generated
            "jti" => $tokenId, // Json Token Id: a unique identifier for the token
            "iss" => $serverName, // Issuer
            "nbf" => $notBefore, // Not before
            "exp" => $exp, // Expire
            "values" => $values
        ], // Data to be encoded in the JWT
        Config("JWT.SECRET_KEY"), // The signing key
        Config("JWT.ALGORITHM") // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
    );
}

/**
 * Decode JWT token
 *
 * @param string $token JWT token
 * @return ?array
 */
function DecodeJwt(string $token): ?array
{
    try {
        $payload = (array)\Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(Config("JWT.SECRET_KEY"), Config("JWT.ALGORITHM")));
        return (array)$payload["values"];
    } catch (Exception $e) {
        if (IsDebug()) {
            return ["failureMessage" => $e->getMessage()];
        }
		return null;
    }
}

/**
 * Get JWT token
 *
 * @return string JWT token
 */
function GetJwtToken(): string
{
    $expiry = time() + max(Config("SESSION_TIMEOUT") * 60, Config("JWT.EXPIRE_TIME"), ini_get("session.gc_maxlifetime"));
    return Security()->createJwt($expiry)
        ?? CreateJwt([
            "username" => "",
            "userid" => null,
            "parentuserid" => null,
            "userlevel" => AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID,
            "userprimarykey" => null,
            "userPermission" => 0
        ], $expiry);
}

/**
 * Get request method
 *
 * @return string Request method
 */
function RequestMethod(): string
{
    return Request()->getMethod() ?? ServerVar("REQUEST_METHOD");
}

/**
 * Is GET request
 *
 * @return bool
 */
function IsGet(): bool
{
    return SameText(RequestMethod(), "GET");
}

/**
 * Is POST request
 *
 * @return bool
 */
function IsPost(): bool
{
    return SameText(RequestMethod(), "POST");
}

/**
 * Is OPTIONS request
 *
 * @return bool
 */
function IsOptions(): bool
{
    return SameText(RequestMethod(), "OPTIONS");
}

/**
 * Get querystring data
 *
 * @param string $name Name of parameter
 * @param mixed $default Default value if name not found
 * @return mixed
*/
function Get(string $name, mixed $default = null): mixed
{
    return Request()->getQueryParam($name, $default) ?? $_GET[$name] ?? $default;
}

/**
 * Get post data
 *
 * @param string $name Name of paramter
 * @param mixed $default Default value if name not found
 * @return mixed
*/
function Post(string $name, mixed $default = null): mixed
{
    return Request()->getParsedBodyParam($name, $default) ?? $_POST[$name] ?? $default;
}

/**
 * Get post/querystring data
 *
 * @param string $name Name of paramter
 * @param mixed $default Default value if name not found
 * @return mixed
*/
function Param(string $name, mixed $default = null): mixed
{
    return Request()->getParam($name, $default) ?? $_POST[$name] ?? $_GET[$name] ?? $default;
}

/**
 * Get key data from Param("key")
 *
 * @param int $i The nth (0-based) key
 * @return ?string
 */
function Key(int $i = 0): ?string
{
    $key = Param(Config("API_KEY_NAME"));
    if ($key !== null) {
        $keys = explode(Config("COMPOSITE_KEY_SEPARATOR"), strval($key));
        return $keys[$i] ?? null;
    }
    return null;
}

/**
 * Is SAML response
 *
 * @return bool
 */
function IsSamlResponse(): bool
{
    return Param("SAMLResponse") !== null;
}

/**
 * Get route data
 *
 * @param int|string $idx The nth (0-based) route value or argument name
 * @return string|string[]|null
 */
function Route(int|string|null $idx = null): string|array|null
{
    global $RouteValues;
    if ($RouteValues === null) { // Set up route values
        $routeContext = RouteContext();
        if ($routeContext != null) { // Routing has been completed
            $basePath = $routeContext?->getBasePath() ?? "";
            $routingResults = $routeContext?->getRoutingResults();
            $args = $routingResults?->getRouteArguments() ?? [];
            $uri = preg_replace("/^" . preg_quote($basePath, "/") . "/", "", $routingResults?->getUri() ?? "");
            $routes = explode("/", ltrim($uri, "/"));
            if (IsApi()) { // First part of API route is "api/", remove
                array_shift($routes);
            }
            $RouteValues = array_merge($routes, $args);
        } else { // Cannot create RouteContext before routing has been completed
            if (is_int($idx)) {
                $uri = ServerVar("REQUEST_URI"); // e.g. /basepath/api/file
                $basePath = BasePath(true); // e.g. /basepath/api/
                $uri = preg_replace("/^" . preg_quote($basePath, "/")  . "/", "", $uri);
                $routes = explode("/", ltrim($uri, "/"));
                if (IsApi()) { // First part of API route is "api/", remove
                    array_shift($routes);
                }
                return $routes[$idx] ?? null;
            }
            return null;
        }
    }
    if ($idx === null) { // Get all route values as array
        return $RouteValues;
    } else { // Get route value by name or index
        $value = $RouteValues[$idx] ?? null;
        if (IsApi()) { // Special handling for API
            // Get record key separated by key separator (for API /file/{table}/{param}[/{key:.*}])
            if (
                $idx == "key"
                && ($RouteValues[0] ?? "") == "file"
                && ContainsString($value, "/")
            ) {
                $value = implode(Config("COMPOSITE_KEY_SEPARATOR"), explode("/", $value));
            // Composite key (for API /(view|edit|delete)/{table}[/{params:.*}])
            } elseif (
                is_int($idx) && $idx >= 2
                && in_array($RouteValues[0] ?? "", ["view", "edit", "delete"])
                && ContainsString($RouteValues["params"], Config("COMPOSITE_KEY_SEPARATOR"))
            ) {
                $keys = explode(Config("COMPOSITE_KEY_SEPARATOR"), $RouteValues["params"]);
                return $keys[$idx - 2] ?? null; // For Route(i + 2) where i is 0-based index of keyFields
            // Composite key (for API /export/{param}[/{table}[/{key:.*}]])
            } elseif (
                is_int($idx) && $idx >= 3
                && ($RouteValues[0] ?? "") == "export"
                && ContainsString($RouteValues["key"] ?? "", Config("COMPOSITE_KEY_SEPARATOR"))
            ) {
                $keys = explode(Config("COMPOSITE_KEY_SEPARATOR"), $RouteValues["key"]);
                return $keys[$idx - 3] ?? null; // For Route(i + 3) where i is 0-based index of keyFields
            }
        }
        return $value;
    }
}

/**
 * Write data to response
 *
 * @param mixed $data Data being outputted
 * @return void
 */
function Write(mixed $data): void
{
    global $Response, $RenderingView;
    if ($Response instanceof Response && !$RenderingView) {
        $Response->write($data);
    } else {
        echo $data;
    }
}

/**
 * Output JSON data (UTF-8)
 *
 * @param mixed $data Data to be encoded and outputted (non UTF-8)
 * @param int $options optional JSON encoding options (same as that of json_encode())
 * @return void
 */
function WriteJson(mixed $data, int $options = 0): void
{
    global $Response;
    if ($Response instanceof Response) {
        try {
            $version = IsApi() ? ["version" => PRODUCT_VERSION] : [];
            if (is_array($data) && !array_is_list($data) && count($data) > 0) { // Associative array
                $data = array_merge($data, $version);
            }
            $Response = $Response->withJson($data, null, $options);
        } catch (RuntimeException $e) {
            $Response = $Response->withJson([
                "json_last_error_msg" => $e->getMessage(),
                "json_last_error" => $e->getCode(),
                "options" => $options
            ]);
        }
    } else {
        $json = json_encode($data, $options);
        if ($json === false) {
            $json = json_encode(["json_encode_error" => json_last_error()], $options);
        }
        if (!Config("DEBUG") && ob_get_length()) {
            ob_end_clean();
        }
        header("Content-Type: application/json; charset=utf-8");
        echo $json;
    }
}

/**
 * Add header
 *
 * @param string $name Header name
 * @param string $value Header value
 * @param bool $replace optional Replace a previous similar header, or add a second header of the same type. Default is true.
 * @return void
 */
function AddHeader(string $name, string $value, bool $replace = true): void
{
    global $Response;
    if ($Response instanceof Response) {
        $Response = $replace
            ? $Response->withHeader($name, $value) // Replace
            : $Response->withAddedHeader($name, $value); // Append
    }
}

/**
 * Remove header
 *
 * @param string $name Header name to be removed
 * @return void
 */
function RemoveHeader(string $name): void
{
    global $Response;
    if ($Response instanceof Response) {
        $Response = $Response->withoutHeader($name);
    }
}

/**
 * Read cookie from request
 *
 * @param string $name Cookie name
 * @return ?string
 */
function ReadCookie(string $name): ?string
{
    return FigRequestCookies::get(Request(), PROJECT_NAME . "_" . $name)->getValue();
}

/**
 * User has given consent to track cookie
 *
 * @return bool
 */
function CanTrackCookie(): bool
{
    return ReadCookie(Config("COOKIE_CONSENT_NAME")) == "1";
}

/**
 * Set cookie to response
 *
 * @param string $name Cookie name
 * @param string $value Cookie value
 * @param int $expiry optional Cookie expiry time
 * @param bool $httpOnly optional HTTP only
 * @return void
 */
function SetCookie(string $name, string $value, int $expiry = -1, ?bool $httpOnly = null): void
{
    global $SetCookies;
    $expiry = ($expiry > -1) ? $expiry : Config("COOKIE_EXPIRY_TIME");
    $SetCookies = $SetCookies->with(SetCookie::create(PROJECT_NAME . "_" . $name, $value)
        ->withPath(Config("COOKIE_PATH"))
        ->withExpires(gmdate("D, d-M-Y H:i:s T", $expiry))
        ->withSameSite(SameSite::fromString(Config("COOKIE_SAMESITE")))
        ->withHttpOnly($httpOnly ?? Config("COOKIE_HTTP_ONLY"))
        ->withSecure(Config("COOKIE_SAMESITE") == "None" || IsHttps() && Config("COOKIE_SECURE")));
}

/**
 * Remove/Expire response cookie
 *
 * @param string $name Cookie name
 * @return void
 */
function RemoveCookie(string $name): void
{
    global $SetCookies;
    $SetCookies = $SetCookies->without(PROJECT_NAME . "_" . $name);
    $SetCookies = $SetCookies->with(SetCookie::create(PROJECT_NAME . "_" . $name)->withPath(Config("COOKIE_PATH"))->expire());
}

/**
 * Create consent cookie
 *
 * @return string
 */
function CreateConsentCookie(): string
{
    return (string) SetCookie::create(PROJECT_NAME . "_" . Config("COOKIE_CONSENT_NAME"), 1)
        ->withPath(Config("COOKIE_PATH"))
        ->withExpires(gmdate("D, d-M-Y H:i:s T", Config("COOKIE_EXPIRY_TIME")))
        ->withSameSite(SameSite::fromString(Config("COOKIE_SAMESITE")))
        ->withHttpOnly(false) // httpOnly must be false
        ->withSecure(Config("COOKIE_SAMESITE") == "None" || IsHttps() && Config("COOKIE_SECURE"));
}

/**
 * Write cookie to response
 *
 * @param string $name Cookie name
 * @param string $value Cookie value
 * @param int $expiry optional Cookie expiry time. Default is Config("COOKIE_EXPIRY_TIME")
 * @param bool $essential optional Essential cookie, set even without user consent. Default is true.
 * @param bool $httpOnly optional HTTP only. Default is false.
 * @return void
 */
function WriteCookie(string $name, string $value, int $expiry = -1, bool $essential = true, bool $httpOnly = false): void
{
    if ($essential || CanTrackCookie()) {
        SetCookie($name, $value, $expiry, $httpOnly);
    }
}

/**
 * Send event
 *
 * @param array|string $data Data of event
 * @param string $type Type of event
 * @return void
 */
function SendEvent(array|string $data, string $type = "message"): void
{
    echo "event: " . $type . "\n",
        "data: " . (is_array($data) ? json_encode($data) : $data),
        "\n\n";

    // Flush the output buffer and send echoed messages to the browser
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
}

/**
 * Get page object
 *
 * @param string $name Page name or table name
 * @return ?object
 */
function &Page(string $name = ""): ?object
{
    global $NullValue;
    $res = $name ? $GLOBALS[$name] ?? null : $GLOBALS["Page"] ?? null;
    return is_object($res) ? $res : $NullValue;
}

/**
 * Get current language ID
 *
 * @return string
 */
function CurrentLanguageID(): string
{
    return $GLOBALS["CurrentLanguage"] ?? "";
}

/**
 * Is RTL language
 *
 * @return bool
 */
function IsRTL(): bool
{
    global $CurrentLanguage;
    $lang = explode("-", str_replace("_", "-", $CurrentLanguage))[0];
    return in_array($lang, Config("RTL_LANGUAGES"));
}

/**
 * Get current page object
 *
 * @return ?object
 */
function &CurrentPage(): ?object
{
    return $GLOBALS["Page"];
}

/**
 * Get current project ID
 *
 * @return string
 */
function CurrentProjectID(): string
{
    return CurrentPage()?->ProjectID ?? PROJECT_ID;
}

/**
 * Get user table object
 *
 * @return ?object
 */
function UserTable(): ?DbTableBase
{
    return Container("usertable");
}

/**
 * Get current main table object
 *
 * @return mixed
 */
function &CurrentTable(): mixed
{
    return $GLOBALS["Table"];
}

/**
 * Get current main table name
 *
 * @return string
 */
function CurrentTableName(): string
{
    return CurrentTable()?->TableName ?? "";
}

/**
 * Get user table object
 *
 * @param string $tblVar Table Var
 * @return string
 */
function GetTableName(string $tblVar): string
{
    global $USER_LEVEL_TABLES;
    $table = Collection::make($USER_LEVEL_TABLES)->first(fn ($tbl) => $tbl[1] == $tblVar);
    return $table[0] ?? $tblVar; // Return table name if found
}

/**
 * Get current master table object
 *
 * @return ?object
 */
function &CurrentMasterTable(): ?DbTableBase
{
    $tbl = &CurrentTable();
    if ($tbl != null && method_exists($tbl, "getCurrentMasterTable") && $masterTbl = $tbl->getCurrentMasterTable()) {
        $GLOBALS[$masterTbl] ??= Container($masterTbl);
        return $GLOBALS[$masterTbl];
    }
    return $GLOBALS["NullValue"];
}

/**
 * Get current detail table object
 *
 * @return ?object
 */
function &CurrentDetailTable(): ?DbTableBase
{
    return $GLOBALS["Grid"];
}

/**
 * Get foreign key URL
 *
 * @param string $name Key name
 * @param ?string $val Key value
 * @param mixed $dateFormat Date format
 * @return string
 */
function GetForeignKeyUrl(string $name, ?string $val, mixed $dateFormat = null): string
{
    $url = $name . "=";
    if ($val === null) {
        $val = Config("NULL_VALUE");
    } elseif ($val === "") {
        $val = Config("EMPTY_VALUE");
    } elseif (is_numeric($dateFormat)) {
        $val = UnformatDateTime($val, $dateFormat);
    }
    return $url . urlencode($val);
}

/**
 * Get filter for a primary/foreign key field
 *
 * @param DbField $fld Field object
 * @param ?string $val Value
 * @param DataType $dataType Data type of value
 * @param string $dbid Database ID
 * @return string Filter (<Field> <Opr> <Value>)
 */
function GetKeyFilter(DbField $fld, ?string $val, DataType $dataType, string $dbid = "DB"): string
{
    $expression = $fld->Expression;
    if ($val == Config("NULL_VALUE")) {
        return $expression . " IS NULL";
    } elseif ($val == Config("EMPTY_VALUE")) {
        $val = "";
    }
    $dbtype = GetConnectionType($dbid);
    if ($fld->DataType == DataType::NUMBER && ($dataType == DataType::STRING || $dataType == DataType::MEMO)) { // Find field value (number) in input value (string)
        if ($dbtype == "MYSQL") { // MySQL, use FIND_IN_SET(expr, val)
            $fldOpr = "FIND_IN_SET";
        } else { // Other database type, use expr IN (val)
            $fldOpr = "IN";
            $val = str_replace(Config("MULTIPLE_OPTION_SEPARATOR"), Config("IN_OPERATOR_VALUE_SEPARATOR"), $val);
        }
        return SearchFilter($expression, $fldOpr, $val, $dataType, $dbid);
    } elseif (($fld->DataType == DataType::STRING || $fld->DataType == DataType::MEMO) && $dataType == DataType::NUMBER) { // Find input value (number) in field value (string)
        return GetMultiValueFilter($expression, $val, $dbid);
    } else { // Assume same data type
        return SearchFilter($expression, "=", $val, $dataType, $dbid);
    }
}

/**
 * Search field for multi-value
 *
 * @param string $expression Search expression
 * @param array|string|null $val Value
 * @param string $dbid Database ID
 * @param string $opr Operator
 * @param string $cond Condition
 * @param ?string $sep Multi-value separator
 * @return string Filter (<Field> <Opr> <Value>)
 */
function GetMultiValueFilter(string $expression, array|string|null $val, string $dbid = "DB", string $opr = "=", string $cond = "OR", ?string $sep = null): string
{
	if ($val === null) {
        return $expression . " IS NULL";
    }
    $ar = is_array($val) ? $val : [$val];
    $quotedValues = array_map(fn($v) => "'" . AdjustSql($v) . "'", $ar);
    $sep ??= Config("MULTIPLE_OPTION_SEPARATOR");
    $dbType = GetConnectionType($dbid);
    if ($dbType == "MSSQL" && $cond == "OR" && intval(explode(".", Conn($dbid)->getServerVersion())[0]) >= 13) { // OR operator for MSSQL 2016 or later
        $values = count($quotedValues) ? implode(", ", $quotedValues) : "NULL";
        $sql = "EXISTS (SELECT * FROM STRING_SPLIT({$expression}, '{$sep}') WHERE value IN ({$values}))";
        return $opr == "=" ? $sql : "NOT (" . $sql . ")";
    } elseif ($dbType == "POSTGRESQL") { // PostgreSQL
        $values = count($quotedValues) ? "ARRAY[" . implode(", ", $quotedValues) . "]" : "NULL";
        $operator = $cond == "OR" ? "&&" : "@>";
        $sql = "STRING_TO_ARRAY(CAST({$expression} AS TEXT), '{$sep}') {$operator} {$values}";
        return $opr == "=" ? $sql : "NOT (" . $sql . ")";
    } elseif (in_array($dbType, ["MYSQL", "ORACLE", "SQLITE"])) { // Use GetMultiSearchSqlFilter
        $parts = array_map(fn($v) => GetMultiSearchSqlFilter($v, $expression, $opr, $dbid, $sep), $quotedValues);
        return implode($cond == "OR" ? " OR " : " AND ", $parts);
	} else { // Return format: fld = 'val' OR fld LIKE 'val,%' OR fld LIKE '%,val,%' OR fld LIKE '%,val'
        $parts = array_map(function($v) use ($expression, $dbid, $opr, $cond) {
            $sep = Config("MULTIPLE_OPTION_SEPARATOR");
            $likeOpr = $opr == "=" ? "LIKE" : "NOT LIKE";
            $sql = $expression . " " . $opr . " '" . AdjustSql($v) . "' " . $cond . " ";
            $sql .= $expression . LikeOrNotLike($likeOpr, Wildcard($v . $sep, "STARTS WITH", $dbid), $dbid) . " " . $cond . " "
                . $expression . LikeOrNotLike($likeOpr, Wildcard($sep . $v . $sep, "LIKE", $dbid), $dbid) . " " . $cond . " "
                . $expression . LikeOrNotLike($likeOpr, Wildcard($sep . $v, "ENDS WITH", $dbid), $dbid);
            return $cond == "OR" ? "(" . $sql . ")" : $sql;
        }, $ar);
        return implode($cond == "OR" ? " OR " : " AND ", $parts);
    }
}

/**
 * Get foreign key value
 *
 * @param string $val Key value
 * @return string
 */
function GetForeignKeyValue(string $val): string
{
    return match ($val) {
        Config("NULL_VALUE") => null,
        Config("EMPTY_VALUE") => "",
        default => $val
    };
}

// Begin of add by Masino Sinaga, September 10, 2023
/**
 * Get CSRF Token as query
 *
 * @return string
 */
function GetCsrfAsQuery()
{
    global $TokenNameKey, $TokenName, $TokenValueKey, $TokenValue;
    if (!EmptyValue($TokenNameKey) && !EmptyValue($TokenValueKey) && !EmptyValue($TokenName) && !EmptyValue($TokenValue)) {
        return $TokenNameKey . "=" . $TokenName . "&" . $TokenValueKey . "=" . $TokenValue;
    }
    return "";
}
// End of add by Masino Sinaga, September 10, 2023

/**
 * Get file IMG tag (for export to email/PDF/HTML only)
 *
 * @param array|string $fn File name
 * @param string $class Class name
 * @return string
 */
function GetFileImgTag(array|string $fn, string $class = ""): string
{
    if (!is_array($fn)) {
        $fn = $fn ? [$fn] : [];
    }
    $files = array_filter($fn);
    $files = array_map(fn($file) => ContainsString($file, ":\\") ? str_replace("\\", "/", $file) : $file, $files); // Replace '\' by '/' to avoid encoding issue
    $tags = array_map(fn($file) => '<img class="ew-image' . ($class ? ' ' . $class : '') . '" src="' . $file . '" alt="">', $files);
    return implode("<br>", $tags);
}

// Get file A tag
function GetFileATag(DbField $fld, string $fn): string
{
    $wrkfiles = [];
    $wrkpath = "";
    $html = "";
    if ($fld->DataType == DataType::BLOB) {
        if (!IsEmpty($fld->Upload->DbValue)) {
            $wrkfiles = [$fn];
        }
    } elseif ($fld->UploadMultiple) {
        $wrkfiles = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $fn);
        $pos = strrpos($wrkfiles[0], '/');
        if ($pos !== false) {
            $wrkpath = substr($wrkfiles[0], 0, $pos + 1); // Get path from first file name
            $wrkfiles[0] = substr($wrkfiles[0], $pos + 1);
        }
    } else {
        if (!IsEmpty($fld->Upload->DbValue)) {
            $wrkfiles = [$fn];
        }
    }
    $elements = array_map(
        fn($wrkfile) => Element::create("a", attributes: ["href" => FullUrl($wrkpath . $wrkfile, "href")])->setInnerHtml($fld->caption()),
        array_filter($wrkfiles)
    );
    return implode("<br>", array_map(fn($el) => $el->toDocument()->format()->html(), $elements));
}

// Get file temp image
function GetFileTempImage(DbField $fld, mixed $val): string
{
    if ($fld->DataType == DataType::BLOB) {
        if (!IsEmpty($fld->Upload->DbValue)) {
            $tempImage = $fld->Upload->DbValue;
            if ($fld->ImageResize) {
                ResizeBinary($tempImage, $fld->ImageWidth, $fld->ImageHeight);
            }
            return TempImage($tempImage);
        }
        return "";
    } else {
        $tempImage = ReadFile($fld->uploadPath() . $val);
        if ($fld->ImageResize) {
            ResizeBinary($tempImage, $fld->ImageWidth, $fld->ImageHeight);
        }
        return TempImage($tempImage);
    }
}

// Get file image
function GetFileImage(DbField $fld, mixed $val, int $width = 0, int $height = 0, bool $crop = false): string
{
    $image = "";
    $file = "";
    if ($fld->DataType == DataType::BLOB) {
        $image = is_resource($val) ? stream_get_contents($val) : $val;
    } elseif ($fld->UploadMultiple) {
        $file = $fld->uploadPath() . (explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $val ?? "")[0]);
    } else {
        $file = $fld->uploadPath() . $val;
    }
    if (FileExists($file)) {
        $image = ReadFile($file);
    }
    if (!IsEmpty($image) && $width > 0) {
        $func = fn($phpthumb) => $phpthumb->adaptiveResize($width, $height);
        $plugins = $crop ? [$func] : [];
        ResizeBinary($image, $width, $height, plugins: $plugins);
        return $image;
    }
    return "";
}

// Get API action URL // PHP
function GetApiUrl(string $action, string $query = ""): string
{
    return GetUrl(Config("API_URL") . $action) . ($query ? "?" : "") . $query;
}

/**
 * Get file upload URL
 *
 * @param DbField $fld Field object
 * @param mixed $val Field value
 * @param array $options optional {
 *  @var bool "resize" Resize the image
 *  @var bool "crop" Crop image
 *  @var bool "encrypt" Encrypt file path
 *  @var bool "urlencode" URL-encode file path
 * }
 * @return string URL
 */
function GetFileUploadUrl(DbField $fld, mixed $val, array $options = []): string
{
    $opts = [
        "resize" => false,
        "crop" => false,
        "encrypt" => null,
        "urlencode" => true
    ];
    if (is_bool($options)) {
        $opts["resize"] = $options;
    } elseif (is_array($options)) {
        $opts = array_merge($opts, $options);
    }
    extract($opts);
    if (!IsEmpty($val)) {
        $fileUrl = GetApiUrl(Config("API_FILE_ACTION")) . "/";
        $sessionId = SessionId();
        if ($fld->DataType == DataType::BLOB) {
            $tableVar = !IsEmpty($fld->SourceTableVar) ? $fld->SourceTableVar : $fld->TableVar;
            $fn = $fileUrl . rawurlencode($tableVar) . "/" . rawurlencode($fld->Param) . "/" . rawurlencode($val);
            if ($resize) {
                $fn .= "?resize=1&width=" . $fld->ImageWidth . "&height=" . $fld->ImageHeight . ($crop ? "&crop=1" : "");
            }
        } else {
            $encrypt ??= Config("ENCRYPT_FILE_PATH") || IsRemote($fld->uploadPath());
            $path = ($encrypt || $resize) ? $fld->uploadPath() : $fld->hrefPath();
            $key = $sessionId . Config("ENCRYPTION_KEY");
            if ($encrypt) {
                $fn = $fileUrl . $fld->TableVar . "/" . Encrypt($path . $val, $key);
                if ($resize) {
                    $fn .= "?width=" . $fld->ImageWidth . "&height=" . $fld->ImageHeight . ($crop ? "&crop=1" : "");
                }
            } elseif ($resize) {
                $fn = $fileUrl . $fld->TableVar . "/" . Encrypt($path . $val, $key) .
                    "?width=" . $fld->ImageWidth . "&height=" . $fld->ImageHeight . ($crop ? "&crop=1" : ""); // Encrypt the physical path
            } else {
                $fn = IsRemote($path) ? $path : UrlEncodeFilePath($path);
                $fn .= UrlEncodeFilePath($val, !IsRemote($path)); // S3 expects "+" in file name
                $fn = GetUrl($fn);
            }
        }
        $fn .= ContainsString($fn, "?") ? "&" : "?";
        $fn .= "session=" . Encrypt($sessionId);
        return $fn;
    }
    return "";
}

/**
 * URL encode file path
 *
 * @param string $path File path
 * @param bool $raw Use rawurlencode() or else urlencode()
 * @return string
 */
function UrlEncodeFilePath(string $path, bool $raw = true): string
{
    $ar = explode("/", $path);
    $scheme = parse_url($path, PHP_URL_SCHEME);
    foreach ($ar as &$c) {
        if ($c != $scheme . ":") {
            $c = $raw ? rawurlencode($c) : urlencode($c);
        }
    }
    return implode("/", $ar);
}

// Get file view tag
function GetFileViewTag(DbField &$fld, mixed $val, bool $tooltip = false): string
{
    global $Page;
    if (!IsEmpty($val)) {
        $val = $fld->htmlDecode($val);
        if ($fld->DataType == DataType::BLOB) {
            $wrknames = [$val];
            $wrkfiles = [$val];
        } elseif ($fld->UploadMultiple) {
            $wrknames = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $val);
            $wrkfiles = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $fld->htmlDecode($fld->Upload->DbValue));
        } else {
            $wrknames = [$val];
            $wrkfiles = [$fld->htmlDecode($fld->Upload->DbValue)];
        }
        $wrkfiles = array_filter($wrkfiles);
        $multiple = count($wrkfiles) > 1;
        $href = $tooltip ? "" : $fld->HrefValue;
        $isLazy = $tooltip ? false : IsLazy();
        $tags = [];
        $wrkcnt = 0;
        $showBase64Image = $Page?->isExport("html");
        $skipImage = $Page && ($Page->isExport("excel") && !Config("USE_PHPEXCEL") || $Page->isExport("word") && !Config("USE_PHPWORD"));
        $showTempImage = $Page && ($Page->TableType == "REPORT"
            && ($Page->isExport("excel") && Config("USE_PHPEXCEL")
            || $Page->isExport("word") && Config("USE_PHPWORD"))
            || $Page->TableType != "REPORT" && ($Page->Export == "pdf" || $Page->Export == "email"));
        foreach ($wrkfiles as $wrkfile) {
            $tag = "";
            if ($showTempImage) {
                $fn = GetFileTempImage($fld, $wrkfile);
            } elseif ($skipImage) {
                $fn = "";
            } else {
                $fn = GetFileUploadUrl($fld, $wrkfile, ["resize" => $fld->ImageResize]);
            }
            if ($fld->ViewTag == "IMAGE" && ($fld->IsBlobImage || IsImageFile($wrkfile))) { // Image
                $fld->ViewAttrs->appendClass($fld->ImageCssClass);
                if ($showBase64Image) {
                    $tag = GetFileImgTag(ImageFileToBase64Url(GetFileTempImage($fld, $wrkfile)));
                } else {
                    if ($isLazy) {
                        $fld->ViewAttrs->appendClass("ew-lazy");
                    }
                    if ($href == "" && !$fld->UseColorbox) {
                        if ($fn != "") {
                            if ($isLazy) {
                                $tag = '<img loading="lazy" alt="" src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="' . $fn . '"' . $fld->viewAttributes() . '>';
                            } else {
                                $tag = '<img alt="" src="' . $fn . '"' . $fld->viewAttributes() . '>';
                            }
                        }
                    } else {
                        if ($fld->UploadMultiple && ContainsString($href, '%u')) {
                            $fld->HrefValue = str_replace('%u', GetFileUploadUrl($fld, $wrkfile), $href);
                        }
                        if ($fn != "") {
                            if ($isLazy) {
                                $tag = '<a' . $fld->linkAttributes() . '><img loading="lazy" alt="" src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="' . $fn . '"' . $fld->viewAttributes() . '></a>';
                            } else {
                                $tag = '<a' . $fld->linkAttributes() . '><img alt="" src="' . $fn . '"' . $fld->viewAttributes() . '></a>';
                            }
                        }
                    }
                }
            } else { // Non image
                if ($fld->DataType == DataType::BLOB) {
                    $url = $href;
                    $name = ($fld->Upload->FileName != "") ? $fld->Upload->FileName : $fld->caption();
                    $ext = str_replace(".", "", ContentExtension($fld->Upload->DbValue));
                } else {
                    $url = GetFileUploadUrl($fld, $wrkfile);
                    $cnt = count($wrknames);
                    $name = $wrknames[$wrkcnt] ?? $wrknames[$cnt - 1];
                    $pathinfo = pathinfo($wrkfile);
                    $ext = strtolower($pathinfo["extension"] ?? "");
                }
                $isPdf = SameText($ext, "pdf");
                if ($url != "") {
                    $fld->LinkAttrs->removeClass("ew-lightbox"); // Remove colorbox class
                    unset($fld->LinkAttrs["title"]); // Remove title
                    if ($fld->UploadMultiple && ContainsString($href, "%u")) {
                        $fld->HrefValue = str_replace("%u", $url, $href);
                    }
                    $isEmbedPdf = $isPdf && Config("EMBED_PDF") && !($Page && $Page->isExport() && !$Page->isExport("print")); // Skip Embed PDF for export
                    if ($isEmbedPdf) {
                        $pdfFile = $fld->uploadPath() . $wrkfile;
                        $tag = "<a" . $fld->linkAttributes() . ">" . $name . "</a>";
                        if ($fld->DataType == DataType::BLOB || FileExists($pdfFile)) {
                            $tag = '<div class="ew-pdfobject" data-url="' . $url . '">' . $tag . '</div>';
                        }
                    } else {
                        if ($ext) {
                            $fld->LinkAttrs["data-extension"] = $ext;
                        }
                        $tag = "<a" . $fld->linkAttributes() . ">" . $name . "</a>";
                    }
                }
            }
            if ($tag != "") {
                $tags[] = $tag;
            }
            $wrkcnt += 1;
        }
        if ($multiple && count($tags) > 1) {
            return '<div class="d-flex flex-row ew-images">' . implode('', $tags) . '</div>';
        }
        return implode('', $tags);
    }
    return "";
}

// Get image view tag
function GetImageViewTag(DbField &$fld, mixed $val): string
{
    if (!IsEmpty($val)) {
        $href = $fld->HrefValue;
        $image = $val;
        if ($val && !ContainsString($val, "://") && !ContainsString($val, "\\") && !ContainsText($val, "javascript:")) {
            $fn = GetImageUrl($fld, $val, ["resize" => $fld->ImageResize]);
        } else {
            $fn = $val;
        }
        if (IsImageFile($val)) { // Image
            $fld->ViewAttrs->appendClass($fld->ImageCssClass);
            if (IsLazy()) {
                $fld->ViewAttrs->appendClass("ew-lazy");
            }
            if ($href == "" && !$fld->UseColorbox) {
                if ($fn != "") {
                    if (IsLazy()) {
                        $image = '<img loading="lazy" alt="" src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="' . $fn . '"' . $fld->viewAttributes() . '>';
                    } else {
                        $image = '<img alt="" src="' . $fn . '"' . $fld->viewAttributes() . '>';
                    }
                }
            } else {
                if ($fn != "") {
                    if (IsLazy()) {
                        $image = '<a' . $fld->linkAttributes() . '><img loading="lazy" alt="" src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="' . $fn . '"' . $fld->viewAttributes() . '></a>';
                    } else {
                        $image = '<a' . $fld->linkAttributes() . '><img alt="" src="' . $fn . '"' . $fld->viewAttributes() . '></a>';
                    }
                }
            }
        } else {
            $name = $val;
            if ($href != "") {
                $image = "<a" . $fld->linkAttributes() . ">" . $name . "</a>";
            } else {
                $image = $name;
            }
        }
        return $image;
    }
    return "";
}

/**
 * Get image URL
 *
 * @param DbField $fld Field object
 * @param mixed $val Field value
 * @param array $options optional {
 *  @var bool "resize" Resize the image
 *  @var bool "crop" Crop image
 *  @var bool "encrypt" Encrypt file path
 *  @var bool "urlencode" URL-encode file path
 * }
 * @return string URL
 */
function GetImageUrl(DbField $fld, mixed $val, array $options = []): string
{
    $opts = [
        "resize" => false,
        "crop" => false,
        "encrypt" => null,
        "urlencode" => true
    ];
    if (is_bool($options)) {
        $opts["resize"] = $options;
    } elseif (is_array($options)) {
        $opts = array_merge($opts, $options);
    }
    extract($opts);
    if (!IsEmpty($val)) {
        $sessionId = SessionId();
        $key = $sessionId . Config("ENCRYPTION_KEY");
        $queryString = "session=" . Encrypt($sessionId);
        $fileUrl = GetApiUrl(Config("API_FILE_ACTION")) . "/";
        $encrypt = ($encrypt === null) ? Config("ENCRYPT_FILE_PATH") : $encrypt;
        $path = ($encrypt || $resize) ? $fld->uploadPath() : $fld->hrefPath();
        if ($encrypt) {
            $fn = $fileUrl . $fld->TableVar . "/" . Encrypt($path . $val, $key) . "?" . $queryString;
            if ($resize) {
                $fn .= "&width=" . $fld->ImageWidth . "&height=" . $fld->ImageHeight . ($crop ? "&crop=1" : "");
            }
        } elseif ($resize) {
            $fn = $fileUrl . $fld->TableVar . "/" . Encrypt($path . $val, $key) . "?" . $queryString .
                "&width=" . $fld->ImageWidth . "&height=" . $fld->ImageHeight . ($crop ? "&crop=1" : "");
        } else {
            $fn = $val;
            if ($urlencode) {
                $fn = UrlEncodeFilePath($fn);
            }
            $fn = GetUrl($fn);
        }
        return $fn;
    }
    return "";
}

// Check if image file
function IsImageFile(string $fn): bool
{
    if ($fn != "") {
        $ar = parse_url($fn);
        if ($ar && array_key_exists("query", $ar)) { // Thumbnail URL
            parse_str($ar["query"], $q);
            if (isset($q["fn"])) {
                $fn = $q["fn"];
            }
        }
        $pathinfo = pathinfo($fn);
        $ext = strtolower($pathinfo["extension"] ?? "");
        return in_array($ext, explode(",", Config("IMAGE_ALLOWED_FILE_EXT")));
    }
    return false;
}

// Check if lazy loading images
function IsLazy(): bool
{
    global $ExportType;
    return Config("LAZY_LOAD") && ($ExportType == "" || $ExportType == "print");
}

// Write HTTP header
function WriteHeader(bool $cache): void
{
    global $Response;
    if ($Response instanceof Response) {
        $export = Get("export");
        $cacheProvider = Container("app.cache");
        if ($cache || IsHttps() && $export && !SameText($export, "print")) { // Allow cache
            $Response = $cacheProvider->allowCache($Response, "private", 86400, true);
        } else { // No cache
            $Response = $cacheProvider->denyCache($Response);
            $Response = $cacheProvider->withExpires($Response, "Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            $Response = $cacheProvider->withLastModified($Response, gmdate("D, d M Y H:i:s") . " GMT"); // Always modified
        }
        $Response = $Response->withHeader("X-UA-Compatible", "IE=edge");
        if (!$export || SameText($export, "print")) {
            $ct = "text/html";
            $charset = PROJECT_CHARSET;
            if ($charset != "") {
                $ct .= "; charset=" . $charset;
            }
            $Response = $Response->withHeader("Content-Type", $ct); // Charset
        }
    }
}

/**
 *  Get content file extension
 *
 * @param string $data Data
 * @param bool $dot Extension with dot
 * @return string
 */
function ContentExtension(string &$data, bool $dot = true): string
{
    $ct = ContentType($data);
    if ($ct) {
        $ext = MimeTypes()->getExtensions($ct)[0] ?? null;
        if ($ext) {
            return $dot ? "." . $ext : $ext;
        }
    }
    return ""; // Unknown extension
}

/**
 * Get content type
 * http://en.wikipedia.org/wiki/List_of_file_signatures
 * https://www.garykessler.net/library/file_sigs.html (mp3 / aac / flac / mp4 / m4v / mov)
 *
 * @param string $data Data of file
 * @param string $fn File path
 * @return string Content type
 */
function ContentType(string &$data, string $fn = ""): string
{
    $mp4Sig = strlen($data) >= 12 ? substr($data, 4, 8) : "";
    if (StartsString("\x47\x49\x46\x38\x37\x61", $data) || StartsString("\x47\x49\x46\x38\x39\x61", $data)) { // Check if gif
        return "image/gif";
    } elseif (StartsString("\xFF\xD8\xFF\xE0", $data) || StartsString("\xFF\xD8\xFF\xDB", $data) || StartsString("\xFF\xD8\xFF\xEE", $data) || StartsString("\xFF\xD8\xFF\xE1", $data)) { // Check if jpg
        return "image/jpeg";
    } elseif (StartsString("\x89\x50\x4E\x47\x0D\x0A\x1A\x0A", $data)) { // Check if png
        return "image/png";
    } elseif (StartsString("\x42\x4D", $data)) { // Check if bmp
        return "image/bmp";
    } elseif (StartsString("\x25\x50\x44\x46", $data)) { // Check if pdf
        return "application/pdf";
    } elseif (StartsString("\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1", $data)) { // xls/doc/ppt
        if (ContainsString($data, "\x77\x6F\x72\x6B\x62\x6F\x6F\x6B")) { // xls, find pattern "workbook"
            return MimeTypes()->getMimeTypes("xls")[0];
        } elseif (ContainsString($data, "\x57\x6F\x72\x64\x2E\x44\x6F\x63\x75\x6D\x65\x6E\x74")) { // doc, find pattern "Word.Document"
            return MimeTypes()->getMimeTypes("doc")[0];
        }
    } elseif (StartsString("\x50\x4B\x03\x04", $data)) { // docx/xlsx/pptx/zip
        if ($fn != "") { // Use file extension to get mime type first in case other files types with the same bytes (e.g. dotx)
            return MimeContentType($fn);
        } elseif (ContainsString($data, "\x78\x6C\x2F\x77\x6F\x72\x6B\x62\x6F\x6F\x6B")) { // xlsx, find pattern "x1/workbook"
            return MimeTypes()->getMimeTypes("xlsx")[0];
        } elseif (ContainsString($data, "\x77\x6F\x72\x64\x2F\x5F\x72\x65\x6C")) { // docx, find pattern "word/_rel"
            return MimeTypes()->getMimeTypes("docx")[0];
        }
    } elseif (StartsString("\x49\x44\x33", $data)) { // mp3
        return MimeTypes()->getMimeTypes("mp3")[0];
    } elseif (StartsString("\xFF\xF1", $data) || StartsString("\xFF\xF9", $data)) { // aac
        return MimeTypes()->getMimeTypes("aac")[0];
    } elseif (StartsString("\x66\x4C\x61\x43\x00\x00\x00\x22", $data)) { // flac
        return MimeTypes()->getMimeTypes("flac")[0];
    } elseif (SameString("\x66\x74\x79\x70\x4D\x53\x4E\x56", $mp4Sig) || SameString("\x66\x74\x79\x70\x69\x73\x6F\x6D", $mp4Sig)) { // mp4
        return MimeTypes()->getMimeTypes("mp4")[0];
    } elseif (SameString("\x66\x74\x79\x70\x6D\x70\x34\x32", $mp4Sig)) { // m4v
        return MimeTypes()->getMimeTypes("mp4v")[0];
    } elseif (SameString("\x66\x74\x79\x70\x71\x74\x20\x20", $mp4Sig)) { // mov
        return MimeTypes()->getMimeTypes("mov")[0];
    } elseif ($fn != "") { // Use file extension to get mime type
        return MimeContentType($fn);
    }
    return Config("DEFAULT_MIME_TYPE");
}

/**
 * Get MimeTypes
 *
 * @return Symfony\Component\Mime\MimeTypes
 */
function MimeTypes(): MimeTypes
{
    return Container("mime.types");
}

/**
 * Get content type for a file
 *
 * @param string $fn File path
 * @return string Content type
 */
function MimeContentType(string $fn): string
{
    $ext = pathinfo($fn, PATHINFO_EXTENSION);
    $mt = MimeTypes();
    $ct = $mt->getMimeTypes($ext)[0] ?? "";
    if (!$ct && (file_exists($fn) || is_readable($fn))) { // Check the file content if possible
        if ($mt->isGuesserSupported()) {
            $ct = $mt->guessMimeType($fn);
        } elseif (function_exists("mime_content_type")) {
            $ct = mime_content_type($fn);
        } else {
            $size = @getimagesize($filepath);
            if (!empty($size["mime"])) {
                $ct = $size["mime"];
            }
        }
    }
    return $ct ?: Config("DEFAULT_MIME_TYPE");
}

/**
 * Get file extension for a file
 *
 * @param string $fn File path
 * @param bool $dot Extension with dot
 * @return string
 */
// Get content file extension
function MimeContentExtension(string $fn, bool $dot = true): string
{
    $ext = pathinfo($fn, PATHINFO_EXTENSION);
    $ct = MimeContentType($fn);
    if ($ct) {
        $ext = MimeTypes()->getExtensions($ct)[0] ?? null;
        if ($ext) {
            return $dot ? "." . $ext : $ext;
        }
    }
    return ""; // Unknown extension
}

/**
 * Get entity manager
 *
 * @param string $dbid Database ID
 * @return EntityManager
 */
function EntityManager(string $dbid = "DB"): EntityManager
{
    return Container("entitymanager." . $dbid);
}

/**
 * Get event manager
 *
 * @return EventManager
 */
function EventManager(): EventManager
{
    return Container(EventManager::class);
}

/**
 * Get user entity manager
 *
 * @return EntityManager
 */
function UserEntityManager(): EntityManager
{
    return EntityManager(Config("USER_TABLE_DBID"));
}

/**
 * Get user repository
 *
 * @return ObjectRepository
 */
function UserRepository(): ObjectRepository
{
    return UserEntityManager()->getRepository(Config("USER_TABLE_ENTITY_CLASS"));
}

/**
 * Get an user entity by user name
 *
 * @param ?string $username User name
 * @return ?UserInterface
 */
function LoadUserByIdentifier(?string $username): ?UserInterface
{
    if (IsEntityUser() && CurrentUserIdentifier() == $username) {
        return CurrentUser();
    }
    try {
        return !IsEmpty($username) ? UserRepository()->loadUserByIdentifier($username) : null;
    } catch (UserNotFoundException $e) {
        return null;
    }
}

/**
 * Create login link
 *
 * @param UserInterface $user User
 * @param int $liftime Lifetime in seconds
 * @return LoginLinkDetails
 */
function CreateLoginLink(UserInterface $user, ?int $lifetime = null): LoginLinkDetails
{
    return SecurityContainer(LoginLinkHandlerInterface::class)->createLoginLink($user, null, $liftime ?? Config("LOGIN_LINK_LIFETIME"));
}

/**
 * Get privilege
 *
 * @param Allow|string|int $name Allow (enum) or enum name or value
 * @return int
 */
function GetPrivilege(Allow|string|int $name): int
{
    global $container;
    if ($name instanceof Allow) { // Enum
        return $name->value;
    } elseif (is_int($name)) { // Integer
        return $name;
    }
    $name = strtoupper($name);
    $reflection = $container->get("reflection.enum.allow");
    return $reflection->hasCase($name) ? $reflection->getCase($name)->getBackingValue() : 0;
}

/**
 * Get connection object
 *
 * @param string $dbid Database ID
 * @return Connection
 */
function Conn(string $dbid = "DB"): Connection
{
    return Container("connection." . $dbid);
}

/**
 * Get connection object (alias of Conn())
 *
 * @param string $dbid Database ID
 * @return Connection
 */
function GetConnection(string $dbid = "DB"): Connection
{
    return Conn($dbid);
}

/**
 * Get native connection
 *
 * @param string $dbid Database ID
 * @return resource|object
 */
function GetNativeConnection(string $dbid = "DB"): object
{
    $conn = Conn($dbid);
    return $conn->getNativeConnection();
}

/**
 * Get native connection (alias for backward compatibility)
 *
 * @param string $dbid Database ID
 * @return resource|object
 */
#[Deprecated("GetConnectionId() is deprecated and will be removed", "2025")]
function GetConnectionId(string $dbid = "DB"): object
{
    return GetNativeConnection();
}

/**
 * Get connection info
 *
 * @param string $dbid Database ID
 * @return array
 */
function Db(string $dbid = "DB"): array
{
    return Config("Databases." . $dbid);
}

/**
 * Get connection type
 *
 * @param string $dbid Database ID
 * @return ?string
 */
function GetConnectionType(string $dbid = "DB"): ?string
{
    $db = Db($dbid);
    return $db ? $db["type"] : null;
}

/**
 * Cast date/time field for LIKE
 *
 * @param string $fld Field expression
 * @param int|string $namedformat Date format
 * @param string $dbid Database ID
 * @return string SQL expression formatting the field to 'y-MM-dd HH:mm:ss'
 */
function CastDateFieldForLike(string $fld, int|string $namedformat, string $dbid = "DB"): string
{
    global $DATE_FORMAT, $DATE_SEPARATOR, $TIME_SEPARATOR;
    $dbtype = GetConnectionType($dbid);
    $dateFormat = DbDateFormat($namedformat, $dbtype);
    if ($dateFormat) {
        return match ($dbtype) {
            "MYSQL" => "DATE_FORMAT(" . $fld . ", '" . $dateFormat . "')",
            "MSSQL" => "FORMAT(" . $fld . ", '" . $dateFormat . "')",
            "ORACLE", "POSTGRESQL" => "TO_CHAR(" . $fld . ", '" . $dateFormat . "')",
            "SQLITE" => "STRFTIME('" . $dateFormat . "', " . $fld . ")"
        };
    }
    return $fld;
}

/**
 * Append LIKE operator
 *
 * @param string $pat
 * @param string $dbid Database ID
 * @return string
 */
function Like(string $pat, string $dbid = "DB"): string
{
    return LikeOrNotLike("LIKE", $pat, $dbid);
}

/**
 * Append NOT LIKE operator
 *
 * @param string $pat
 * @param string $dbid Database ID
 * @return string
 */
function NotLike(string $pat, string $dbid = "DB"): string
{
    return LikeOrNotLike("NOT LIKE", $pat, $dbid);
}

/**
 * Append LIKE or NOT LIKE operator
 *
 * @param string $opr Operator
 * @param string $pat
 * @param string $dbid Database ID
 * @return string
 */
function LikeOrNotLike(string $opr, string $pat, $dbid = "DB"): string
{
    $dbtype = GetConnectionType($dbid);
    $opr = " " . $opr . " "; // " LIKE " or " NOT LIKE "
    $pat = "'" . $pat . "'"; // Note: $pat already adjusted for "'" in AdjustSqlForLike
    if ($dbtype == "POSTGRESQL" && Config("USE_ILIKE_FOR_POSTGRESQL")) {
        return str_replace(" LIKE ", " ILIKE ", $opr) . $pat;
    } elseif ($dbtype == "MYSQL" && Config("LIKE_COLLATION_FOR_MYSQL") != "") {
        return $opr . $pat . " COLLATE " . Config("LIKE_COLLATION_FOR_MYSQL");
    } elseif ($dbtype == "MSSQL" && Config("LIKE_COLLATION_FOR_MSSQL") != "") {
        return " COLLATE " . Config("LIKE_COLLATION_FOR_MSSQL") . $opr . $pat;
    } elseif ($dbtype == "SQLITE" || $dbtype == "ORACLE") {
        $pat .= " ESCAPE '\'"; // Add ESCAPE character
    }
    return $opr . $pat;
}

/**
 * Get multi-value search SQL
 *
 * @param DbField $fld Field object
 * @param string $fldOpr Search operator
 * @param string $fldVal Converted search value
 * @param string $dbid Database ID
 * @param ?string $sep Separator
 * @return string WHERE clause
 */
function GetMultiSearchSql(DbField $fld, string $fldOpr, string $fldVal, string $dbid = "DB", ?string $sep = null): string
{
    $fldDataType = $fld->DataType;
    $fldOpr = ConvertSearchOperator($fldOpr, $fld, $fldVal);
    if (in_array($fldOpr, ["IS NULL", "IS NOT NULL", "IS EMPTY", "IS NOT EMPTY"])) {
        return SearchFilter($fld->Expression, $fldOpr, $fldVal, $fldDataType, $dbid);
    } else {
        if ($fld->UseFilter) { // Use filter
            $sep = Config("FILTER_OPTION_SEPARATOR");
            $wrk = GetMultiValueFilter($fld->Expression, explode($sep, $fldVal), $dbid, "=", "OR", $sep);
        } else {
            $sep ??= Config("MULTIPLE_OPTION_SEPARATOR");
            $arVal = explode($sep, $fldVal);
            $wrk = "";
            $dbtype = GetConnectionType($dbid);
            $searchOption = Config("SEARCH_MULTI_VALUE_OPTION");
            if ($searchOption == 1 || !IsMultiSearchOperator($fldOpr)) { // No multiple value search
                $wrk = SearchFilter($fld->Expression, $fldOpr, $fldVal, DataType::STRING, $dbid);
            } else { // Handle multiple search operator
                $searchCond = $searchOption == 3 ? "OR" : "AND"; // Search condition
                $multiSearchOpr = "=";
                if (StartsString("NOT ", $fldOpr) || $fldOpr == "<>") { // Negate for NOT search
                    $searchCond = $searchCond == "AND" ? "OR" : "AND";
                    $multiSearchOpr = "<>";
                }
                if (!IsMultiSearchOperator($fldOpr)) {
                    foreach ($arVal as $val) {
                        $val = trim($val);
                        $sql = SearchFilter($fld->Expression, $fldOpr, $val, $fldDataType, $dbid);
                        AddFilter($wrk, $sql, $searchCond);
                    }
                } else { // Build multi search SQL
                    $wrk = GetMultiValueFilter($fld->Expression, $arVal, $dbid, $multiSearchOpr, $searchCond, $sep);
                }
            }
        }
        return $wrk;
    }
}

/**
 * Multi value search operator
 *
 * @param string $opr Operator
 * @return bool
 */
function IsMultiSearchOperator(string $opr):  bool
{
    return in_array($opr, ["=", "<>"]); // Supports "=", "<>" only for multi value search
}

/**
 * Get multi search SQL filter
 *
 * @param string $fld1 Field expression 1
 * @param string $fld2 Field expression 2
 * @param string $fldOpr Search operator
 * @param string $dbid Database ID
 * @param ?string $sep Separator, e.g. Config("MULTIPLE_OPTION_SEPARATOR")
 * @return string WHERE clause
 */
function GetMultiSearchSqlFilter(string $fld1, string $fld2, string $fldOpr, string $dbid = "DB", ?string $sep = null): string
{
    $dbType = GetConnectionType($dbid);
    $negate = false;
    if (StartsString("NOT ", $fldOpr) || $fldOpr == "<>") {
        $negate = true;
    }
    $sep ??= Config("MULTIPLE_OPTION_SEPARATOR");
    $replace = Config("MYSQL_MULTI_OPTION_REPLACE_STRING");
    if ($dbType == "MYSQL") { // MySQL
        if ($sep == ",") { // FIND_IN_SET if separator is comma
            $sql = ($negate ? "NOT " : "") . "FIND_IN_SET({$fld1}, {$fld2})";
        } else { // Replace separator to comma first then FIND_IN_SET
            $sql = ($negate ? "NOT " : "") . "FIND_IN_SET(REPLACE({$fld1}, ',', '{$replace}'), REPLACE(REPLACE({$fld2}, ',', '{$replace}'), '{$sep}', ','))";
        }
    } elseif ($dbType == "MSSQL" && intval(explode(".", Conn($dbid)->getServerVersion())[0]) >= 13) { // MSSQL 2016 or later
        $sql = $fld1 . ($negate ? " NOT IN " : " IN ") . "(SELECT value FROM STRING_SPLIT({$fld2}, '{$sep}'))";
    } elseif ($dbType == "POSTGRESQL") { // PostgreSQL
        $sql = "CAST({$fld1} AS TEXT)" . ($negate ? " != ALL" : " = ANY") . "(STRING_TO_ARRAY({$fld2}, '{$sep}'))";
    } elseif ($dbType == "ORACLE") { // Oracle
        $sql = $fld1 . ($negate ? " NOT IN " : " IN ") . "(SELECT REGEXP_SUBSTR({$fld2}, '[^{$sep}]+', 1, LEVEL) FROM DUAL CONNECT BY REGEXP_SUBSTR({$fld2}, '[^{$sep}]+', 1, LEVEL) IS NOT NULL)";
    } elseif ($dbType == "SQLITE") { // SQLite
        $sql = $fld1 . ($negate ? " NOT IN " : " IN ") .
        "(WITH EV__SPLIT(EV__WORD, EV__CSV) AS (SELECT '', {$fld2}||',' UNION ALL SELECT SUBSTR(EV__CSV, 0, INSTR(EV__CSV, '{$sep}')), SUBSTR(EV__CSV, INSTR(EV__CSV, '{$sep}') + 1) FROM EV__SPLIT WHERE EV__CSV != '') SELECT EV__WORD FROM EV__SPLIT WHERE EV__WORD != '')";
	} else { // Cannot handle
        throw new Exception("Multi search for '$dbType' type cannot be supported");
    }
    return $sql;
}

/**
 * Check if float type
 *
 * @param int $fldType Field type
 * @return bool
 */
function IsFloatType(int $fldType): bool
{
    return in_array($fldType, [4, 5, 6, 131, 139]);
}

/**
 * Check if is numeric
 *
 * @param mixed $value Value
 * @return bool
 */
function IsNumeric(mixed $value): bool
{
    return is_numeric($value) || ParseNumber(strval($value)) !== false;
}

/**
 * Get dropdown filter
 *
 * @param ReportField $fld Report field object
 * @param string $fldVal Filter value
 * @param string $fldOpr Filter operator
 * @param string $dbid Database ID
 * @param string $fldVal2 Filter value 2
 * @return string WHERE clause
 */
function DropDownFilter(ReportField $fld, string $fldVal, string $fldOpr, string $dbid = "DB", string $fldVal2 = ""): string
{
    $fldName = $fld->Name;
    $fldExpression = $fld->searchExpression();
    $fldDataType = $fld->searchDataType();
    $fldOpr = $fldOpr ?: "=";
    $fldVal = ConvertSearchValue($fldVal, $fldOpr, $fld);
    $wrk = "";
    if (SameString($fldVal, Config("NULL_VALUE"))) {
        $wrk = $fld->Expression . " IS NULL";
    } elseif (SameString($fldVal, Config("NOT_NULL_VALUE"))) {
        $wrk = $fld->Expression . " IS NOT NULL";
    } elseif (SameString($fldVal, Config("EMPTY_VALUE"))) {
        $wrk = $fld->Expression . " = ''";
    } elseif (SameString($fldVal, Config("ALL_VALUE"))) {
        $wrk = "1 = 1";
    } else {
        if (StartsString("@@", $fldVal)) {
            $wrk = CustomFilter($fld, $fldVal, $dbid);
        } elseif (($fld->isMultiSelect() || $fld->UseFilter) && IsMultiSearchOperator($fldOpr) && !IsEmpty($fldVal)) {
            $wrk = GetMultiSearchSql($fld, $fldOpr, trim($fldVal), $dbid);
        } elseif ($fldOpr == "BETWEEN" && !IsEmpty($fldVal) && !IsEmpty($fldVal2)) {
            $wrk = $fldExpression . " " . $fldOpr . " " . QuotedValue($fldVal, $fldDataType, $dbid) . " AND " . QuotedValue($fldVal2, $fldDataType, $dbid);
        } else {
            if (!IsEmpty($fldVal)) {
                if ($fldDataType == DataType::DATE && $fldOpr != "") {
                    $wrk = GetDateFilterSql($fld->Expression, $fldOpr, $fldVal, $fldDataType, $dbid);
                } else {
                    $wrk = SearchFilter($fldExpression, $fldOpr, $fldVal, $fldDataType, $dbid);
                }
            }
        }
    }
    return $wrk;
}

/**
 * Get custom filter
 *
 * @param ReportField $fld Report field object
 * @param string $fldVal Filter value
 * @param string $dbid Database ID
 * @return string WHERE clause
 */
function CustomFilter(ReportField $fld, string $fldVal, string $dbid = "DB"): string
{
    $wrk = "";
    if (is_array($fld->AdvancedFilters)) {
        foreach ($fld->AdvancedFilters as $filter) {
            if ($filter->ID == $fldVal && $filter->Enabled) {
                $fldExpr = $fld->Expression;
                $fn = $filter->FunctionName;
                $wrkid = StartsString("@@", $filter->ID) ? substr($filter->ID, 2) : $filter->ID;
                $fn = $fn != "" && !function_exists($fn) ? PROJECT_NAMESPACE . $fn : $fn;
                if (function_exists($fn)) {
                    $wrk = $fn($fldExpr, $dbid);
                } else {
                    $wrk = "";
                }
                break;
            }
        }
    }
    return $wrk;
}

/**
 * Get search SQL
 *
 * @param DbField $fld Field object
 * @param string $fldVal Converted search value
 * @param string $fldOpr Converted search operator
 * @param string $fldCond Search condition
 * @param string $fldVal2 Converted search value 2
 * @param string $fldOpr2 Converted search operator 2
 * @param string $dbid Database ID
 * @return string WHERE clause
 */
function GetSearchSql(DbField $fld, string $fldVal, string $fldOpr, string $fldCond, string $fldVal2, string $fldOpr2, $dbid = "DB"): string
{
    // Build search SQL
    $sql = "";
    $virtual = $fld->VirtualSearch;
    $fldExpression = $virtual ? $fld->VirtualExpression : $fld->Expression;
    $fldDataType = $virtual ? DataType::STRING : $fld->DataType;
    if (in_array($fldOpr, ["BETWEEN", "NOT BETWEEN"])) {
        $isValidValue = $fldDataType != DataType::NUMBER || is_numeric($fldVal) && is_numeric($fldVal2);
        if ($fldVal != "" && $fldVal2 != "" && $isValidValue) {
            $sql = $fldExpression . " " . $fldOpr . " " . QuotedValue($fldVal, $fldDataType, $dbid) .
                " AND " . QuotedValue($fldVal2, $fldDataType, $dbid);
        }
    } else {
        // Handle first value
        if ($fldVal != "" && IsValidOperator($fldOpr) || IsNullOrEmptyOperator($fldOpr)) {
            $sql = SearchFilter($fldExpression, $fldOpr, $fldVal, $fldDataType, $dbid);
            if ($fld->isBoolean() && $fldVal == $fld->FalseValue && $fldOpr == "=") {
                $sql = "(" . $sql . " OR " . $fldExpression . " IS NULL)";
            }
        }
        // Handle second value
        $sql2 = "";
        if ($fldVal2 != "" && !IsEmpty($fldOpr2) && IsValidOperator($fldOpr2) || IsNullOrEmptyOperator($fldOpr2)) {
            $sql2 = SearchFilter($fldExpression, $fldOpr2, $fldVal2, $fldDataType, $dbid);
            if ($fld->isBoolean() && $fldVal2 == $fld->FalseValue && $fldOpr2 == "=") {
                $sql2 = "(" . $sql2 . " OR " . $fldExpression . " IS NULL)";
            }
        }
        // Combine SQL
        AddFilter($sql, $sql2, $fldCond == "OR" ? "OR" : "AND");
    }
    return $sql;
}

/**
 * Get search filter
 *
 * @param string $fldExpression Field expression
 * @param string $fldOpr Search operator
 * @param ?string $fldVal Converted search value
 * @param DataType $fldType Field type
 * @param string $dbid Database ID
 * @return string WHERE clause
 */
function SearchFilter(string $fldExpression, string $fldOpr, ?string $fldVal, DataType $fldType, string $dbid = "DB"): string
{
    $filter = $fldExpression;
    if (!$filter) {
        return "";
    }
    if (IsEmpty($fldOpr)) {
        $fldOpr = "=";
    }
    if (in_array($fldOpr, ["=", "<>", "<", "<=", ">", ">="])) {
        $filter .= " " . $fldOpr . " " . QuotedValue($fldVal, $fldType, $dbid);
    } elseif ($fldOpr == "IS NULL" || $fldOpr == "IS NOT NULL") {
        $filter .= " " . $fldOpr;
    } elseif ($fldOpr == "IS EMPTY") {
        $filter .= " = ''";
    } elseif ($fldOpr == "IS NOT EMPTY") {
        $filter .= " <> ''";
    } elseif (($fldOpr == "FIND_IN_SET" || $fldOpr == "NOT FIND_IN_SET") && GetConnectionType($dbid) == "MYSQL") { // MYSQL only
        $fldOpr = $fldOpr == "FIND_IN_SET" ? "=" : "<>";
        $filter = GetMultiSearchSqlFilter("'" . AdjustSql($fldVal) . "'", $fldExpression, $fldOpr, $dbid);
    } elseif ($fldOpr == "IN" || $fldOpr == "NOT IN") {
        $filter .= " " . $fldOpr . " (" . implode(", ", array_map(fn($v) => QuotedValue($v, $fldType, $dbid), explode(Config("IN_OPERATOR_VALUE_SEPARATOR"), $fldVal))) . ")";
    } elseif (in_array($fldOpr, ["STARTS WITH", "LIKE", "ENDS WITH"])) {
        $filter .= Like(Wildcard($fldVal, $fldOpr, $dbid), $dbid);
    } elseif (in_array($fldOpr, ["NOT STARTS WITH", "NOT LIKE", "NOT ENDS WITH"])) {
        $filter .= NotLike(Wildcard($fldVal, $fldOpr, $dbid), $dbid);
    } else { // Default is equal
        $filter .= " = " . QuotedValue($fldVal, $fldType, $dbid);
    }
    return $filter;
}

/**
 * Convert search operator
 *
 * @param string $fldOpr Search operator
 * @param DbField $fld Field object
 * @param array|string $fldVal Converted field value(s) (single, delimited or array)
 * @return string|false Converted search operator (false if invalid operator)
 */
function ConvertSearchOperator(string $fldOpr, DbField $fld, array|string $fldVal): string|bool
{
    if ($fld->UseFilter) {
        $fldOpr = "="; // Use "equal"
    }
    $fldOpr = array_search($fldOpr, Config("CLIENT_SEARCH_OPERATORS")) ?: $fldOpr;
    if (!IsValidOperator($fldOpr)) {
        return false;
    }
    if ($fldVal == Config("NULL_VALUE")) { // Null value
        return "IS NULL";
    } elseif ($fldVal == Config("NOT_NULL_VALUE")) { // Not Null value
        return "IS NOT NULL";
    } elseif (IsEmpty($fldOpr)) { // Not specified, ignore
        return $fldOpr;
    } elseif ($fld->DataType == DataType::NUMBER && !$fld->VirtualSearch) { // Numeric value(s)
        if (!IsNumericSearchValue($fldVal, $fldOpr, $fld) || in_array($fldOpr, ["IS EMPTY", "IS NOT EMPTY"])) {
            return false; // Invalid
        } elseif (in_array($fldOpr, ["STARTS WITH", "LIKE", "ENDS WITH"])) {
            return "=";
        } elseif (in_array($fldOpr, ["NOT STARTS WITH", "NOT LIKE", "NOT ENDS WITH"])) {
            return "<>";
        }
    } elseif (
        in_array($fldOpr, ["LIKE", "NOT LIKE", "STARTS WITH", "NOT STARTS WITH", "ENDS WITH", "NOT ENDS WITH", "IS EMPTY", "IS NOT EMPTY"])
        && !in_array($fld->DataType, [DataType::STRING, DataType::MEMO, DataType::XML])
        && !$fld->VirtualSearch
    ) { // String type
        return false; // Invalid
    }
    return $fldOpr;
}

/**
 * Check if search value is numeric
 *
 * @param array|string $fldVal Converted search value
 * @param string $fldOpr Search oeperator
 * @param DbField $fld Field object
 * @return bool
 */
function IsNumericSearchValue(array|string $fldVal, string $fldOpr, DbField $fld): bool
{
    if ($fld->UseFilter && is_string($fldVal) && ContainsString($fldVal, Config("FILTER_OPTION_SEPARATOR"))) {
        $fldVal = array_map("is_numeric", explode(Config("FILTER_OPTION_SEPARATOR"), $fldVal));
    } elseif ($fld->isMultiSelect() && is_string($fldVal) && ContainsString($fldVal, Config("MULTIPLE_OPTION_SEPARATOR"))) {
        $fldVal = array_map("is_numeric", explode(Config("MULTIPLE_OPTION_SEPARATOR"), $fldVal));
    } elseif (($fldOpr == "IN" || $fldOpr == "NOT IN") && ContainsString($fldVal, Config("IN_OPERATOR_VALUE_SEPARATOR"))) {
        $fldVal = array_map("is_numeric", explode(Config("IN_OPERATOR_VALUE_SEPARATOR"), $fldVal));
    } elseif (is_array($fldVal)) {
        $fldVal = array_map("is_numeric", $fldVal);
    }
    return is_array($fldVal)
        ? in_array(false, $fldVal, false) === false
        : is_numeric($fldVal);
}

/**
 * Check if valid search operator
 *
 * @param string $fldOpr Search operator
 * @return bool
 */
function IsValidOperator(string $fldOpr): bool
{
    return IsEmpty($fldOpr) || array_key_exists($fldOpr, Config("CLIENT_SEARCH_OPERATORS"));
}

/**
 * Check if NULL or EMPTY search operator
 *
 * @param string $fldOpr Search operator
 * @return bool
 */
function IsNullOrEmptyOperator(string $fldOpr): bool
{
    return in_array($fldOpr, ["IS NULL", "IS NOT NULL", "IS EMPTY", "IS NOT EMPTY"]);
}

/**
 * Convert search value(s)
 *
 * @param array|string $fldVal Search value(s) (single, delimited or array)
 * @param string $fldOpr Search operator
 * @param DbField $fld Field object
 * @return array|string Converted search values
 */
function ConvertSearchValue(array|string $fldVal, string $fldOpr, DbField $fld): array|string
{
    $convert = function ($val) use ($fld) {
        if ($val == Config("NULL_VALUE") || $val == Config("NOT_NULL_VALUE")) {
            return $val;
        } elseif (IsFloatType($fld->Type)) {
            return ConvertToFloatString($val);
        } elseif ($fld->isBoolean()) {
            return !IsEmpty($val) ? (ConvertToBool($val) ? $fld->TrueValue : $fld->FalseValue) : $val;
        } elseif ($fld->DataType == DataType::DATE || $fld->DataType == DataType::TIME) {
            return !IsEmpty($val) ? UnformatDateTime($val, $fld->formatPattern()) : $val;
        }
        return $val;
    };
    if ($fld->UseFilter && is_string($fldVal) && ContainsString($fldVal, Config("FILTER_OPTION_SEPARATOR"))) {
        return implode(Config("FILTER_OPTION_SEPARATOR"), array_map($convert, explode(Config("FILTER_OPTION_SEPARATOR"), $fldVal)));
    } elseif ($fld->isMultiSelect() && is_string($fldVal) && ContainsString($fldVal, Config("MULTIPLE_OPTION_SEPARATOR"))) {
        return implode(Config("MULTIPLE_OPTION_SEPARATOR"), array_map($convert, explode(Config("MULTIPLE_OPTION_SEPARATOR"), $fldVal)));
    } elseif (($fldOpr == "IN" || $fldOpr == "NOT IN") && ContainsString($fldVal, Config("IN_OPERATOR_VALUE_SEPARATOR"))) {
        return implode(Config("IN_OPERATOR_VALUE_SEPARATOR"), array_map($convert, explode(Config("IN_OPERATOR_VALUE_SEPARATOR"), $fldVal)));
    } elseif (is_array($fldVal)) {
        return array_map($convert, $fldVal);
    }
    return $convert($fldVal);
}

/**
 * Quote table/field name based on dbid
 *
 * @param string $name Name
 * @param string $dbid Database ID
 * @return string
 */
function QuotedName(string $name, string $dbid = "DB"): string
{
    $db = Config("Databases." . $dbid);
    if ($db) {
        $qs = $db["qs"];
        $qe = $db["qe"];
        return $qs . str_replace($qe, $qe . $qe, $name) . $qe;
    }
    return $name;
}

/**
 * Quote field value based on dbid
 *
 * @param mixed $value Value
 * @param DataType|DbField $fldDataType Field data type or DbField
 * @param string $dbid Database ID
 * @return mixed
 */
function QuotedValue(mixed $value, DataType|DbField $fldDataType, string $dbid = "DB"): mixed
{
    if ($value === null) {
        return "NULL";
    }
    $dbtype = GetConnectionType($dbid);
    $raw = false;
    if ($fldDataType instanceof DbField) {
        $dataType = $fldDataType->DataType;
        $removeXss = !$fldDataType->Raw;
    } else {
        $dataType = $fldDataType;
        $removeXss = Config("REMOVE_XSS");
    }
    switch ($dataType) {
        case DataType::STRING:
        case DataType::MEMO:
            $val = "'" . AdjustSql($value) . "'";
            return $dbtype == "MSSQL" ? "N" . $val : $val;
        case DataType::TIME:
            return "'" . AdjustSql($value) . "'";
        case DataType::XML:
            return "'" . AdjustSql($value) . "'";
        case DataType::BLOB:
            if ($dbtype == "MYSQL") {
                return "'" . addslashes($value) . "'";
            }
            return $value;
        case DataType::DATE:
            return "'" . AdjustSql($value) . "'";
        case DataType::GUID:
            return "'" . $value . "'";
        case DataType::BOOLEAN:
            if ($dbtype == "MYSQL" || $dbtype == "POSTGRESQL") {
                return "'" . $value . "'"; // 'Y'|'N' or 'y'|'n' or '1'|'0' or 't'|'f'
            }
            return $value;
        case DataType::BIT: // $dbtype == "MYSQL" || $dbtype == "POSTGRESQL"
            return "b'" . $value . "'";
        case DataType::NUMBER:
            if (IsNumeric($value)) {
                return $value;
            }
            return "NULL"; // Treat as null
        default:
            return $value;
    }
}

/**
 * Add wildcard (%) to value for LIKE operator
 *
 * @param mixed $value Value
 * @param string $likeOpr LIKE operator
 * @param string $dbid Database ID
 * @return string
 */
function Wildcard(mixed $value, string $likeOpr = "", string $dbid = "DB"): string
{
    if (EndsText("STARTS WITH", $likeOpr)) {
        return AdjustSqlForLike($value, $dbid) . "%";
    } elseif (EndsText("ENDS WITH", $likeOpr)) {
        return "%" . AdjustSqlForLike($value, $dbid);
    } elseif (EndsText("LIKE", $likeOpr)) {
        return "%" . AdjustSqlForLike($value, $dbid) . "%";
    }
    return strval($value);
}

// Concat string
function Concat(?string $str1, ?string $str2, string $sep): string
{
    $str1 = trim($str1 ?? "");
    $str2 = trim($str2 ?? "");
    if ($str1 != "" && $sep != "" && !EndsString($sep, $str1)) {
        $str1 .= $sep;
    }
    return $str1 . $str2;
}

// Write message to debug file
function Trace(string $msg): void
{
    $filename = "debug.txt";
    if (!$handle = fopen($filename, 'a')) {
        exit;
    }
    if (is_writable($filename)) {
        fwrite($handle, $msg . "\n");
    }
    fclose($handle);
}

// Compare values with special handling for null values
function CompareValue(mixed $v1, mixed $v2): bool
{
    if ($v1 === null && $v2 === null) {
        return true;
    } elseif ($v1 === null || $v2 === null) {
        return false;
    } else {
        return ($v1 == $v2);
    }
}

// Check if boolean value is true
function ConvertToBool(mixed $value): bool
{
    return $value === true || in_array(strtolower($value ?? ""), ["1", "true", "on", "y", "yes", "t"]);
}

// Add message
function AddMessage(string &$msg, string $newmsg, string $sep = "<br>"): void
{
    if (strval($newmsg) != "") {
        if (strval($msg) != "") {
            $msg .= $sep;
        }
        $msg .= $newmsg;
    }
}

/**
 * Add filter
 *
 * @param string $filter Filter
 * @param string|callable $newfilter New filter
 * @param string $cond Condition (AND / OR)
 * @return void
 */
function AddFilter(string &$filter, string|callable $newfilter, string $cond = "AND"): void
{
    if (is_callable($newfilter)) {
        $newfilter = $newfilter();
    }
    if (trim($newfilter ?? "") == "") {
        return;
    }
    if (trim($filter ?? "") != "") {
        $filter = AddBracketsForFilter($filter, $cond) . " " . $cond . " " . AddBracketsForFilter($newfilter, $cond);
    } else {
        $filter = $newfilter;
    }
}

/**
 * Add brackets to filter if necessary
 *
 * @param string $filter Filter
 * @param string $cond Condition (AND / OR)
 * @return string
 */
function AddBracketsForFilter(string $filter, string $cond = "AND"): string
{
    if (trim($filter) != "") {
        $filterWrk = $filter;
        $pattern = '/\([^()]+?\)/';
        while (preg_match($pattern, $filterWrk)) { // Remove nested brackets (...)
            $filterWrk = preg_replace($pattern, "", $filterWrk);
        }
        if (preg_match('/\sOR\s/i', $filterWrk) && SameText($cond, "AND")) { // Check for any OR without brackets
            $filter = "(" . $filter . ")";
        }
    }
    return $filter;
}

/**
 * Adjust value (as string) for SQL
 *
 * @param ?string $val Value
 * @return string
 */
function AdjustSql(?string $val): string
{
    $replacementMap = [
        "\0" => "\\0",
        "\n" => "\\n",
        "\r" => "\\r",
        "\t" => "\\t",
        chr(26) => "\\Z", // Substitute
        chr(8) => "\\b", // Backspace
        "'" => "''",
        '\\' => '\\\\'
    ];
    return strtr(trim($val ?? ""), $replacementMap);
}

/**
 * Adjust value for SQL LIKE operator
 *
 * @param ?string $val Value
 * @param string $dbid Database ID
 * @return string
 */
function AdjustSqlForLike(?string $val, string $dbid = "DB"): string
{
    $replacementMap = [
        "'" => "''",
        "_" => "\_",
        "%" => "\%"
    ];
    $dbtype = GetConnectionType($dbid);
    if ($dbtype == "MSSQL") {
        $replacementMap["_"] = "[_]";
    }
    return strtr($val ?? "", $replacementMap);
}

/**
 * Write audit trail
 *
 * @param string $pfx Optional log file prefix (for backward compatibility only, not used)
 * @param string $dt Optional DateTime (for backward compatibility)
 * @param string $script Optional script name (for backward compatibility)
 * @param string $usr User ID or user name
 * @param string $action Action
 * @param string $table Table
 * @param string $field Field
 * @param string $keyvalue Key value
 * @param ?string $oldvalue Old value
 * @param ?string $newvalue New value
 * @return void
 */
function WriteAuditTrail(string $pfx, string $dt, string $script, string $usr, string $action, string $table, string $field, string $keyvalue, ?string $oldvalue, ?string $newvalue): void
{
    if ($table === Config("AUDIT_TRAIL_TABLE_NAME")) {
        return;
    }
    $usrwrk = $usr;
    if (IsEmpty($usrwrk)) { // Assume Administrator (logged in) / Anonymous user (not logged in) if no user
        $usrwrk = Language()->phrase(IsLoggedIn() ? "UserAdministrator" : "UserAnonymous");
    }
    if (Config("AUDIT_TRAIL_TO_DATABASE")) {
        $row = [
            Config("AUDIT_TRAIL_FIELD_NAME_DATETIME") => $dt,
            Config("AUDIT_TRAIL_FIELD_NAME_SCRIPT") => $script,
            Config("AUDIT_TRAIL_FIELD_NAME_USER") => $usrwrk,
            Config("AUDIT_TRAIL_FIELD_NAME_ACTION") => $action,
            Config("AUDIT_TRAIL_FIELD_NAME_TABLE") => $table,
            Config("AUDIT_TRAIL_FIELD_NAME_FIELD") => $field,
            Config("AUDIT_TRAIL_FIELD_NAME_KEYVALUE") => $keyvalue,
            Config("AUDIT_TRAIL_FIELD_NAME_OLDVALUE") => $oldvalue,
            Config("AUDIT_TRAIL_FIELD_NAME_NEWVALUE") => $newvalue
        ];
    } else {
        $row = [
            "datetime" => $dt,
            "script" => $script,
            "user" => $usrwrk,
            "ew-action" => $action,
            "table" => $table,
            "field" => $field,
            "keyvalue" => $keyvalue,
            "oldvalue" => $oldvalue,
            "newvalue" => $newvalue
        ];
    }

    // Call AuditTrail Inserting event
    $writeAuditTrail = AuditTrail_Inserting($row);
    if ($writeAuditTrail) {
        if (Config("AUDIT_TRAIL_TO_DATABASE")) {
            $tbl = Container(Config("AUDIT_TRAIL_TABLE_VAR"));
            if ($tbl && (!method_exists($tbl, "rowInserting") || $tbl->rowInserting(null, $row))) {
                if ($tbl->insert($row)) {
                    if (method_exists($tbl, "rowInserted")) {
                        $tbl->rowInserted(null, $row);
                    }
                }
            }
        } else {
            $logger = Container("app.audit");
            $logger->info(__FUNCTION__, $row);
        }
    }
}

/**
 * Write audit trail
 *
 * @param ?string $usr User ID or user name
 * @param string $action Action
 * @param string $table Table
 * @param string $field Field
 * @param string $keyvalue Key value
 * @param ?string $oldvalue Old value
 * @param ?string $newvalue New value
 * @return void
 */
function WriteAuditLog(?string $usr, string $action, string $table, string $field = "", string $keyvalue = "", ?string $oldvalue = null, ?string $newvalue = null): void
{
    if (IsEmpty($usr)) { // Assume Administrator or Anonymous user
        $usr = Language()->phrase(IsSysAdmin() ? "UserAdministrator" : "UserAnonymous");
    }
    if (Config("LOG_USER_ID")) { // User ID
        $usr = CurrentUserID();
        if (IsEmpty($usr)) { // Assume Administrator or Anonymous user
            $usr = IsSysAdmin() ? AdvancedSecurity::ADMIN_USER_LEVEL_ID : AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID;
        }
    }
    WriteAuditTrail("log", DbCurrentDateTime(), ScriptName(), $usr, $action, $table, $field, $keyvalue, $oldvalue, $newvalue);
}

/**
 * Write export log
 *
 * @param string $fileId File ID
 * @param string $dt DateTime
 * @param ?string $usr User ID or user name
 * @param string $exportType Export type
 * @param string $table Table
 * @param string $keyValue Key value
 * @param string $fileName File name
 * @param string $req Request
 * @return void
 */
function WriteExportLog(string $fileId, string $dt, ?string $usr, string $exportType, string $table, string $keyValue, string $fileName, string $req): void
{
    if (IsEmpty(Config("EXPORT_LOG_TABLE_VAR"))) {
        return;
    }
    if (IsEmpty($usr)) { // Assume Administrator or Anonymous user
        $usr = Language()->phrase(IsSysAdmin() ? "UserAdministrator" : "UserAnonymous");
    }
    if (Config("LOG_USER_ID")) { // User ID
        $usr = CurrentUserID();
        if (IsEmpty($usr)) { // Assume Administrator or Anonymous user
            $usr = IsSysAdmin() ? AdvancedSecurity::ADMIN_USER_LEVEL_ID : AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID;
        }
    }
    $row = [
        Config("EXPORT_LOG_FIELD_NAME_FILE_ID") => $fileId,
        Config("EXPORT_LOG_FIELD_NAME_DATETIME") => $dt,
        Config("EXPORT_LOG_FIELD_NAME_USER") => $usr,
        Config("EXPORT_LOG_FIELD_NAME_EXPORT_TYPE") => $exportType,
        Config("EXPORT_LOG_FIELD_NAME_TABLE") => $table,
        Config("EXPORT_LOG_FIELD_NAME_KEY_VALUE") => $keyValue,
        Config("EXPORT_LOG_FIELD_NAME_FILENAME") => $fileName,
        Config("EXPORT_LOG_FIELD_NAME_REQUEST") => $req
    ];
    if (IsDebug()) {
        Log("Export: " . json_encode($row));
    }
    $tbl = Container(Config("EXPORT_LOG_TABLE_VAR"));
    if ($tbl && (!method_exists($tbl, "rowInserting") || $tbl->rowInserting(null, $row))) {
        if ($tbl->insert($row)) {
            if (method_exists($tbl, "rowInserted")) {
                $tbl->rowInserted(null, $row);
            }
        }
    }
}

/**
 * Export path
 *
 * @param bool $phyPath Physical path
 * @return string
 */
function ExportPath(bool $phyPath = false): string
{
    return $phyPath
        ? IncludeTrailingDelimiter(UploadPath(true) . Config("EXPORT_PATH"), true)
        : IncludeTrailingDelimiter(UploadPath(false) . Config("EXPORT_PATH"), false);
}

/**
 * New GUID
 *
 * @return string
 */
function NewGuid(): string
{
    return \Ramsey\Uuid\Uuid::uuid4()->toString();
}

/**
 * Unformat date time
 *
 * @param string $dt Date/Time string
 * @param int|string $dateFormat Formatter pattern
 * @return string
 */
function UnformatDateTime(?string $dt, int|string $dateFormat = ""): string
{
    global $CurrentLocale, $TIME_ZONE;
    $dt = trim($dt ?? "");
    if (
        IsEmpty($dt)
        || preg_match('/^([0-9]{4})-([0][1-9]|[1][0-2])-([0][1-9]|[1|2][0-9]|[3][0|1])( (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?)?$/', $dt) // Date/Time
        || preg_match('/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?$/', $dt)
    ) { // Time
        return $dt;
    }
    try {
        $dateFormat = DateFormat($dateFormat);
        $fmt = new IntlDateFormatter($CurrentLocale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $TIME_ZONE, null, $dateFormat);
        $ts = $fmt->parse($dt); // Parse by $dateFormat
        if ($ts !== false) {
            if (ContainsText($dateFormat, "y") && ContainsText($dateFormat, "h")) { // Date/Time
                return date("Y-m-d H:i:s", $ts);
            } elseif (ContainsText($dateFormat, "y")) { // Date
                return date("Y-m-d", $ts);
            } elseif (ContainsText($dateFormat, "h")) { // Time
                return date("H:i:s", $ts);
            }
        }
    } catch (Exception $e) {
        if (IsDebug()) {
            LogError("Unformat date time error. " . $e->getMessage());
        }
    }
    return $dt;
}

/**
 * Format a timestamp, datetime, date or time field
 *
 * @param mixed $ts Timestamp or Date/Time string
 * @param int|string $dateformat Formatter pattern
 * @return ?string
 */
function FormatDateTime(mixed $ts, int|string $dateFormat = ""): ?string
{
    global $CurrentLocale, $TIME_ZONE;
    $dt = false;
    try {
        if (is_numeric($ts)) { // Timestamp
            $dt = (new DateTimeImmutable())->setTimestamp((int)$ts);
        } elseif (is_string($ts) && !IsEmpty($ts)) {
            $dt = new DateTimeImmutable(trim($ts));
        } elseif ($ts instanceof DateTimeInterface) {
            $dt = $ts;
        }
        if ($dt !== false) {
            if ($dateFormat == 8) { // Handle edit format (show time part only if exists)
                $dateFormat = intval($dt->format('His')) == 0 ? DateFormat(0) : DateFormat(1);
            } else {
                $dateFormat = DateFormat($dateFormat);
            }
            $fmt = new IntlDateFormatter($CurrentLocale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $TIME_ZONE, null, $dateFormat);
            $res = $fmt->format($dt);
            return $res !== false ? ConvertDigits($res) : $ts;
        }
    } catch (Exception $e) {
        if (IsDebug()) {
            LogError("Format date time error. " . $e->getMessage());
        }
    }
    return $ts;
}

/**
 * Is formatted
 *
 * @param mixed $value Value
 */
function IsFormatted(mixed $value): bool
{
    if (is_float($value) || is_int($value) || $value === null || $value === "") { // Number or empty, not formatted
        return false;
    }
    if (!is_numeric($value)) { // Contains non-numeric characters, assume formatted
        return true;
    }
    global $GROUPING_SEPARATOR;
    $value = strval($value);
    if ($GROUPING_SEPARATOR == "." && ContainsString($value, ".")) { // Contains one ".", e.g. 123.456
        if (ParseInteger($value) == str_replace(".", "", $value)) { // Can be parsed, "." is grouping separator
            return true;
        }
    }
    return false;
}

/**
 * Convert digits from intl numbering system to latn
 */
function ConvertDigits(string $value): string
{
    global $CurrentLocale, $NUMBERING_SYSTEM;
    if ($NUMBERING_SYSTEM == "latn") {
        $nu = Config("INTL_NUMBERING_SYSTEMS")[$CurrentLocale] ?? "";
        if ($nu) {
            $digits = Config("NUMBERING_SYSTEMS")[$nu];
            return str_replace(mb_str_split($digits), str_split("0123456789"), $value);
        }
    }
    return $value;
}

/**
 * Format currency
 *
 * @param mixed $value Value
 * @param ?string $pattern Formatter pattern
 * @return ?string
 */
function FormatCurrency(mixed $value, ?string $pattern = ""): ?string
{
    if ($value === null) {
        return null;
    }
    if (IsFormatted($value)) {
        $value = ParseNumber(strval($value));
    }
    global $CurrentLocale, $CURRENCY_FORMAT, $CURRENCY_SYMBOL, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR;
    try {
        $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::CURRENCY);
        $fmt->setPattern($pattern ?: $CURRENCY_FORMAT);
        $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, $CURRENCY_SYMBOL);
        $fmt->setSymbol(NumberFormatter::MONETARY_SEPARATOR_SYMBOL, $DECIMAL_SEPARATOR);
        $fmt->setSymbol(NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $GROUPING_SEPARATOR);
        $res = $fmt->format((float)$value);
        return $res !== false ? ConvertDigits($res) : $value;
    } catch (Exception $e) {
        if (IsDebug()) {
            LogError("Format currency error. " . $e->getMessage());
        }
    }
    return $value;
}

/**
 * Parse currency
 *
 * @param string $value Value (Must match the locale pattern, e.g. "100\xc2\xa€")
 * @param string $pattern Formatter pattern
 * @return float
 */
function ParseCurrency(string $value, string $pattern = ""): float
{
    global $CurrentLocale, $CURRENCY_FORMAT, $CURRENCY_SYMBOL, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR;
    $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::CURRENCY);
    $fmt->setPattern($pattern ?: $CURRENCY_FORMAT);
    $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, $CURRENCY_SYMBOL);
    $fmt->setSymbol(NumberFormatter::MONETARY_SEPARATOR_SYMBOL, $DECIMAL_SEPARATOR);
    $fmt->setSymbol(NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $GROUPING_SEPARATOR);
    return $fmt->parse($value);
}

/**
 * Format number
 *
 * @param mixed $value Value
 * @param ?string $pattern Formatter pattern. If null, keep number of decimal digits.
 * @return ?string
 */
function FormatNumber(mixed $value, ?string $pattern = ""): ?string
{
    if (IsFormatted($value) || $value === null) {
        return $value;
    }
    global $CurrentLocale, $NUMBER_FORMAT, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR;
    try {
        $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::PATTERN_DECIMAL, $pattern ?: $NUMBER_FORMAT);
        if ($pattern === null) {
            $fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 1000);
        }
        $fmt->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $DECIMAL_SEPARATOR);
        $fmt->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $GROUPING_SEPARATOR);
        $res = $fmt->format((float)$value);
        return $res !== false ? ConvertDigits($res) : $value;
    } catch (Exception $e) {
        if (IsDebug()) {
            LogError("Format number error. " . $e->getMessage());
        }
    }
    return $value;
}

/**
 * Format integer
 *
 * @param mixed $value Value
 * @return string
 */
function FormatInteger(mixed $value): ?string
{
    if (IsFormatted($value) || $value === null) {
        return $value;
    }
    global $CurrentLocale, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR;
    try {
        $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::TYPE_INT32); // TYPE_INT64 does not work
        $fmt->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $DECIMAL_SEPARATOR);
        $fmt->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $GROUPING_SEPARATOR);
        $res = $fmt->format((int)$value);
        return $res !== false ? $res : $value;
    } catch (Exception $e) {
        if (IsDebug()) {
            LogError("Format integer error. " . $e->getMessage());
        }
    }
    return $value;
}

/**
 * Parse number
 *
 * @param ?string $value Value
 * @param string $pattern Formatter pattern
 * @return float|false
 */
function ParseNumber(?string $value, string $pattern = ""): float|false
{
    global $CurrentLocale, $NUMBER_FORMAT, $PERCENT_SYMBOL, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR;
    if (IsEmpty($value)) {
        return false;
    } elseif (ContainsString($value, $PERCENT_SYMBOL)) {
        return ParsePercent($value, $pattern);
    }
    $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::PATTERN_DECIMAL, $pattern ?: $NUMBER_FORMAT);
    $fmt->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $DECIMAL_SEPARATOR);
    $fmt->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $GROUPING_SEPARATOR);
    return $fmt->parse($value);
}

/**
 * Parse integer
 *
 * @param ?string $value Value
 * @param string $pattern Formatter pattern
 * @param int $type Integer type (NumberFormatter::TYPE_INT64 = 2 or NumberFormatter::TYPE_INT32 = 1)
 * @return int|false
 */
function ParseInteger(?string $value, string $pattern = "", int $type = 0): int|false
{
    global $CurrentLocale, $NUMBER_FORMAT, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR;
    $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::PATTERN_DECIMAL, $pattern ?: $NUMBER_FORMAT);
    $type = in_array($type, [NumberFormatter::TYPE_INT64, NumberFormatter::TYPE_INT32])
        ? $type
        : (PHP_INT_SIZE == 8 ? NumberFormatter::TYPE_INT64 : NumberFormatter::TYPE_INT32);
    $fmt->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $DECIMAL_SEPARATOR);
    $fmt->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $GROUPING_SEPARATOR);
    return $fmt->parse($value, $type);
}

/**
 * Convert string to float (as string)
 *
 * @param ?string $value Value
 * @param string $pattern Formatter pattern
 * @return string|false|null
 */
function ConvertToFloatString(?string $value, string $pattern = ""): string|false|null
{
    if (IsEmpty($value)) {
        return $value;
    }
    $result = ParseNumber($value, $pattern);
    return $result !== false ? strval($result) : false;
}

/**
 * Format percent
 *
 * @param mixed $value Value
 * @param ?string $pattern Formatter pattern
 * @return ?string
 */
function FormatPercent(mixed $value, ?string $pattern = ""): ?string
{
    if (IsEmpty($value)) {
        return $value;
    } elseif (IsFormatted($value)) {
        $value = ParseNumber(strval($value));
    }
    global $CurrentLocale, $PERCENT_FORMAT, $PERCENT_SYMBOL, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR;
    try {
        $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::PERCENT);
        $fmt->setPattern($pattern ?: $PERCENT_FORMAT);
        $fmt->setSymbol(NumberFormatter::PERCENT_SYMBOL, $PERCENT_SYMBOL);
        $fmt->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $DECIMAL_SEPARATOR);
        $fmt->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $GROUPING_SEPARATOR);
        $res = $fmt->format((float)$value);
        return $res !== false ? ConvertDigits($res) : $value;
    } catch (Exception $e) {
        if (IsDebug()) {
            LogError("Format percent error. " . $e->getMessage());
        }
    }
    return $value;
}

/**
 * Parse percent
 *
 * @param string $value Value (Must match the locale pattern, e.g. "100\xc2\xa0%")
 * @param string $pattern Formatter pattern
 * @return float
 */
function ParsePercent(string $value, string $pattern = ""): float
{
    global $CurrentLocale, $PERCENT_FORMAT, $PERCENT_SYMBOL, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR;
    $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::PERCENT);
    $fmt->setPattern($pattern ?: $PERCENT_FORMAT);
    $fmt->setSymbol(NumberFormatter::PERCENT_SYMBOL, $PERCENT_SYMBOL);
    $fmt->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $DECIMAL_SEPARATOR);
    $fmt->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $GROUPING_SEPARATOR);
    return $fmt->parse($value);
}

/**
 * Format sequence number
 *
 * @param int $seq Sequence number
 * @return string
 */
function FormatSequenceNumber(int|null $seq): string
{
    return sprintf(Language()->phrase("SequenceNumber"), $seq);
}

/**
 * Format phone number (https://github.com/giggsey/libphonenumber-for-php/blob/master/docs/PhoneNumberUtil.md)
 *
 * @param string $phoneNumber Phone number (e.g. US mobile: "(415)555-2671")
 * @param bool|string|null $region Region code (e.g. "US" / "GB" / "FR"), if false, skip formatting
 * @param string $format Phone number format (PhoneNumberFormat::E164/INTERNATIONAL/NATIONAL/RFC3966) (0/1/2/3)
 * @return string
 */
function FormatPhoneNumber(#[\SensitiveParameter] string $phoneNumber, mixed $region = null, int $format = 0)
{
    return $phoneNumber;
}

/**
 * Display field value separator
 *
 * @param int $idx Display field index (1|2|3)
 * @param DbField $fld field object
 * @return string
 */
function ValueSeparator(int $idx, DbField $fld): string
{
    $sep = $fld?->DisplayValueSeparator ?? ", ";
    return is_array($sep) ? @$sep[$idx - 1] : $sep;
}

/**
 * Get temp upload path root
 *
 * @return string
 */
function UploadTempPathRoot(): string
{
    return Config("UPLOAD_TEMP_PATH") ? IncludeTrailingDelimiter(Config("UPLOAD_TEMP_PATH"), false) : UploadPath(false);
}

/**
 * Get temp upload path
 *
 * @param mixed $option Option
 *  If false, return href path of the temp upload folder.
 *  If NULL, return physical path of the temp upload folder.
 *  If string, return physical path of the temp upload folder with the parameter as part of the subpath.
 *  If object (DbField), return physical path of the temp upload folder with tblvar/fldvar as part of the subpath.
 * @param int $idx Index of the field
 * @param bool $tableLevel Table level or field level
 * @return string
 */
function UploadTempPath(mixed $option = null, int $idx = -1, bool $tableLevel = false): string
{
    global $ExportId;
    if ($option !== false) { // File token or field object
        $path = UploadTempPathRoot();
        if (is_string($option)) { // API upload ($option as token)
            $path = IncludeTrailingDelimiter($path . Config("UPLOAD_TEMP_FOLDER_PREFIX") . $option, false);
        } else {
            // Create session id temp folder
            $sessionId = SessionId() ?? $ExportId;
            $path = IncludeTrailingDelimiter($path . Config("UPLOAD_TEMP_FOLDER_PREFIX") . $sessionId, false);
            if (is_object($fld = $option)) { // Normal upload
                $fldvar = ($idx < 0) ? $fld->FieldVar : substr($fld->FieldVar, 0, 1) . $idx . substr($fld->FieldVar, 1);
                $tblvar = $fld->TableVar;
                $path = IncludeTrailingDelimiter($path . $tblvar, false);
                if (!$tableLevel) {
                    $path = IncludeTrailingDelimiter($path . $fldvar, false);
                }
            }
        }
    } else { // Href path
        $path = UploadTempPathRoot();
        $path = IncludeTrailingDelimiter($path . Config("UPLOAD_TEMP_FOLDER_PREFIX") . SessionId(), false);
    }
    return $path;
}

/**
 * Get uploaded file name(s) (as comma separated value) by file token
 *
 * @param string $filetoken File token returned by API
 * @param bool $fullPath Includes full path or not
 * @return string
 */
function GetUploadedFileName(string $filetoken, bool $fullPath = true): string
{
    return HttpUpload::create()->getUploadedFileName($filetoken, $fullPath);
}

/**
 * Get uploaded file names (as array) by file token
 *
 * @param string $filetoken File token returned by API
 * @param bool $fullPath Includes full path or not
 * @return array
 */
function GetUploadedFileNames(string $filetoken, bool $fullPath = true): array
{
    return HttpUpload::create()->getUploadedFileNames($filetoken, $fullPath);
}

// Clean temp upload folders
function CleanUploadTempPaths(string $sessionid = "", string|array $lastModifiedTime = []): void
{
    $folder = UploadTempPathRoot();
    if (!DirectoryExists($folder)) {
        return;
    }
    $finder = Finder::create()->directories()->in($folder)->name('/^' . preg_quote(Config("UPLOAD_TEMP_FOLDER_PREFIX"), '/') . '/') // Find upload temp folders
        ->sortByName()->reverseSorting();
    foreach ($finder as $dir) {
        $entry = $dir->getFileName(); // Folder name
        if (Config("UPLOAD_TEMP_FOLDER_PREFIX") . $sessionid == $entry) { // Clean session folder
            CleanPath($dir->getRealPath(), true, $lastModifiedTime);
        } else {
            if (Config("UPLOAD_TEMP_FOLDER_PREFIX") . SessionId() != $entry) {
                $temp = $dir->getRealPath();
                if (IsEmptyPath($temp)) { // Empty folder
                    CleanPath($temp, true);
                } else { // Old folder
                    CleanPath($temp, true, "< now - " . Config("UPLOAD_TEMP_FOLDER_TIME_LIMIT") . " minutes");
                }
            }
        }
    }
}

// Clean temp upload folder
function CleanUploadTempPath(mixed $fld, int $idx = -1): void
{
    // Clean the upload folder
    $path = PrefixDirectoryPath(UploadTempPath($fld, $idx));
    CleanPath($path, true);
    // Remove table temp folder if empty
    $path = PrefixDirectoryPath(UploadTempPath($fld, $idx, true));
    if (IsEmptyPath($path)) {
        CleanPath($path, true);
    }
}

/**
 * Clean folder path
 *
 * @param string $path Folder path
 * @param bool $delete Delete folder path or not
 * @param string|array $lastModifiedTime Last modified time (e.g. "< now - 10 minutes")
 * @return void
 */
function CleanPath(string $path, bool $delete = false, string|array $lastModifiedTime = []): void
{
    if (!is_dir($path)) {
        return;
    }
    try {
        $finder = Finder::create()->files()->in($path)->date($lastModifiedTime);
        foreach ($finder as $file) { // Delete files
            $realpath = $file->getRealPath();
            try {
                unlink($realpath);
                if (IsDebug()) {
                    if (file_exists($realpath)) {
                        Log("Failed to delete file '" . $realpath . "'");
                    } else {
                        Log("File '" . $realpath . "' deleted");
                    }
                }
            } catch (Throwable $e) {
                if (IsDebug()) {
                    LogError("Failed to delete file '" . $realpath . "'. Exception: " . $e->getMessage());
                }
            }
        }
        if ($delete) {
            $finder->directories()->in($path)->sortByName()->reverseSorting();
            foreach ($finder as $dir) { // Delete subdirectories
                DeletePath($dir->getRealPath());
            }
            DeletePath($path); // Delete this directory
        }
    } catch (Throwable $e) {
        if (IsDebug()) {
            throw $e;
        }
    } finally {
        @gc_collect_cycles(); // Forces garbase collection (for S3)
    }
}

/**
 * Delete folder path
 *
 * @param string $path Folder path
 * @return void
 */
function DeletePath(string $path): void
{
    try {
        if (IsEmptyPath($path)) { // Delete directory
            @rmdir($path); // Suppress "Directory not empty" warning
            if (IsDebug()) {
                if (file_exists($path)) {
                    Log("Failed to delete folder '" . $path . "'");
                } else {
                    Log("Folder '" . $path . "' deleted");
                }
            }
        }
    } catch (Throwable $e) {
        if (IsDebug()) {
            LogError("Failed to delete folder '" . $path . "'. Exception: " . $e->getMessage());
        }
    }
}

/**
 * Check if empty folder path
 *
 * @param string $path Folder path
 * @return bool
 */
function IsEmptyPath(string $path): bool
{
    return @is_dir($path) && !Finder::create()->files()->in($path)->hasResults();
}

/**
 * Truncate memo field based on specified length, string truncated to nearest whitespace
 *
 * @param ?string $memostr String to be truncated
 * @param int $maxlen Max. length
 * @param bool $removehtml Remove HTML or not
 * @return string
 */
function TruncateMemo(?string $memostr, int $maxlen, bool $removehtml = false): string
{
    $str = $removehtml ? RemoveHtml($memostr) : $memostr;
    $str = preg_replace('/\s+/', " ", $str ?? "");
    $len = strlen($str);
    if ($len > 0 && $len > $maxlen) {
        $i = 0;
        while ($i >= 0 && $i < $len) {
            $j = strpos($str, " ", $i);
            if ($j === false) { // No whitespaces
                return substr($str, 0, $maxlen) . "..."; // Return the first part only
            } else {
                // Get nearest whitespace
                if ($j > 0) {
                    $i = $j;
                }
                // Get truncated text
                if ($i >= $maxlen) {
                    return substr($str, 0, $i) . "...";
                } else {
                    $i++;
                }
            }
        }
    }
    return $str;
}

/**
 * Remove HTML tags from text
 *
 * @param ?string $str HTML string
 * @return string
 */
function RemoveHtml(?string $str): string
{
    return preg_replace('/<[^>]*>/', '', strval($str));
}

/**
 * Send SMS notification
 *
 * @param string $phone Phone Number (e.g. US mobile: "(415)555-2671")
 * @param string $content SMS content (text only)
 * @param bool|string|null $region Region code (e.g. "US" / "GB" / "FR"), if false, skip formatting
 * @param int $format Phone number format (PhoneNumberFormat::E164/INTERNATIONAL/NATIONAL/RFC3966) (0/1/2/3)
 * @return bool|string success or error message
 */
function SendSmsNotification(#[\SensitiveParameter] string $phone, string $content, mixed $region = null, int $format = 0): bool|string
{
    $notification = new Notification($content, ["sms"]);
    $recipient = new Recipient(phone: FormatPhoneNumber($phone, $region, $format));
    try {
        Notifier()->send($notification, $recipient);
        return true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
        if (IsDebug()) {
            LogError($error, ["trace" => $e->getTraceAsString()]);
        }
        return $error;
    }
}

/**
 * Send email notification
 *
 * @param string $email Email address
 * @param string $subject Subject
 * @param string $content Email content (text only, not HTML)
 * @return bool|string success or error message
 */
function SendEmailNotification(#[\SensitiveParameter] string $email, string $subject, string $content): bool|string
{
    $notification = (new Notification($subject, ["email"]))->content($content);
    $recipient = new Recipient($email);
    try {
        Notifier()->send($notification, $recipient);
        return true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
        if (IsDebug()) {
            LogError($error, ["trace" => $e->getTraceAsString()]);
        }
        return $error;
    }
}

/**
 * Send email
 *
 * Note: Since v2025, the $smtpSecure parameter has been replaced by $priority.
 * TLS setting should be set up for the mailer.
 *
 * @return bool|string
 */
function SendEmail(
    string $fromEmail,
    string $toEmail,
    string $ccEmail,
    string $bccEmail,
    string $subject,
    string $mailContent,
    string $format = "html",
    string $charset = EMAIL_CHARSET,
    int|string $priority = 3,
    array $attachments = [],
    array $images = [] // Embedded images
): string|bool
{
    $mail = (new \Symfony\Component\Mime\Email())
        ->from($fromEmail)
        ->subject($subject);
    if (SameText($format, "html")) {
        $mail->html($mailContent, $charset);
    } else {
        if (strip_tags($mailContent) != $mailContent) { // Contains HTML tags
            $mail->text(HtmlToText($mailContent), $charset);
        } else {
            $mail->text($mailContent, $charset);
        }
    }
    $arTo = preg_split('/[;,]/', $toEmail, -1, PREG_SPLIT_NO_EMPTY);
    $arCc = preg_split('/[;,]/', $ccEmail, -1, PREG_SPLIT_NO_EMPTY);
    $arBcc = preg_split('/[;,]/', $bccEmail, -1, PREG_SPLIT_NO_EMPTY);
    $mail->addTo(...$arTo)
        ->addCc(...$arCc)
        ->addBcc(...$arBcc);
    if (is_integer($priority)) {
        $mail->priority($priority >= 1 && $priority <= 5 ? $priority : 3);
    } elseif (in_array(strtolower($priority), ["highest", "high", "normal", "low", "lowest"])) {
        $mail->priority(match (strtolower($priority)) {
            "highest" => 1,
            "high" => 2,
            "normal" => 3,
            "low" => 4,
            "lowest" => 5,
            default => 3
        });
    }
    if (is_array($attachments)) {
        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                $filename = $attachment["filename"] ?? "";
                $content = $attachment["content"] ?? "";
                if ($content != "" && $filename != "") {
                    $mail->attach($content, $filename);
                } elseif ($filename != "") {
                    $mail->attachFromPath($filename);
                }
            } elseif (is_string($attachment)) {
                $filename = IsAbsolutePath($attachment)
                    ? $attachment
                    : PrefixDirectoryPath(UploadTempPath()) . $attachment;
                $mail->attachFromPath($filename);
            }
        }
    }
    if (is_array($images)) {
        foreach ($images as $image) {
            $file = IsAbsolutePath($image)
                ? $image
                : PrefixDirectoryPath(UploadTempPath()) . $image;
            $cid = GetContentId($image);
            $part = (new DataPart(new File($file)))->setContentId($cid)->asInline();
            $mail->addPart($part);
        }
    }
    try {
        $mailer = Container("app.mailer");
        $mailer->send($mail);
        return true; // True on success, error info on error
    } catch (Throwable $e) {
        $result = $e->getMessage();
        if (IsDebug()) { // Error
            // Debug($result);
            LogError($result);
        }
        return $result; // Return error info on error
    }
}

/**
 * Field data type
 *
 * @param int $fldtype Field type
 * @return DataType
 */
function FieldDataType(int $fldtype): DataType
{
    switch ($fldtype) {
        case 20: // BigInt
        case 3: // Integer
        case 2:  // SmallInt
        case 16: // TinyInt
        case 4: // Single
        case 5: // Double
        case 131: // Numeric
        case 139: // VarNumeric
        case 6: // Currency
        case 17: // UnsignedTinyInt
        case 18: // UnsignedSmallInt
        case 19: // UnsignedInt
        case 21: // UnsignedBigInt
            return DataType::NUMBER;
        case 7:
        case 133:
        case 135: // Date
        case 146: // DateTimeOffset
            return DataType::DATE;
        case 134: // Time
        case 145: // Time
            return DataType::TIME;
        case 201:
        case 203: // Memo
            return DataType::MEMO;
        case 129:
        case 130:
        case 200:
        case 202: // String
            return DataType::STRING;
        case 11: // Boolean
            return DataType::BOOLEAN;
        case 72: // GUID
            return DataType::GUID;
        case 128:
        case 204:
        case 205: // Binary
            return DataType::BLOB;
        case 141: // XML
            return DataType::XML;
        default:
            return DataType::OTHER;
    }
}

/**
 * Application root
 *
 * @param bool $phyPath
 * @return string Path of the application root
 */
function AppRoot(bool $phyPath): string
{
    $path = $phyPath
        ? preg_replace('/(?<!^)\\\\\\\\/', PATH_DELIMITER, realpath(".")) // Replace '\\' (not at the start of path) by path delimiter
        : "";
    return IncludeTrailingDelimiter($path, $phyPath);
}

/**
 * Upload path
 *
 * @param bool $phyPath Physical path or not
 * @param string $destPath Destination path
 * @return string If $phyPath is true, return physical path on the server. If $phyPath is false, return relative URL.
 */
function UploadPath(bool $phyPath, string $destPath = ""): string
{
    $destPath = $destPath ?: Config("UPLOAD_DEST_PATH");
    if (IsRemote($destPath)) { // Remote
        $path = $destPath;
        $phyPath = false;
    } elseif ($phyPath) { // Physical
        $destPath = str_replace("/", PATH_DELIMITER, $destPath);
        $path = PathCombine(AppRoot(true), $destPath, true);
    } else { // Relative
        $path = PathCombine(AppRoot(false), $destPath, false);
    }
    return IncludeTrailingDelimiter($path, $phyPath);
}

// Get physical path relative to application root
function ServerMapPath(string $path, bool $isFile = false): string
{
    $pathinfo = IsRemote($path) ? [] : pathinfo($path);
    if ($isFile && @$pathinfo["basename"] != "" || @$pathinfo["extension"] != "") { // File
        return UploadPath(true, $pathinfo["dirname"]) . $pathinfo["basename"];
    } else { // Folder
        return UploadPath(true, $path);
    }
}

/**
 * Log path (physical)
 *
 * @return string
 */
function LogPath(): string
{
    return UploadPath(true, Config("LOG_PATH"));
}

// Write info for config/debug only
function Info(): void
{
    echo "UPLOAD_DEST_PATH = " . Config("UPLOAD_DEST_PATH") . "<br>";
    echo "AppRoot(true) = " . AppRoot(true) . "<br>";
    echo "AppRoot(false) = " . AppRoot(false) . "<br>";
    echo "realpath('.') = " . realpath(".") . "<br>";
    echo "DOCUMENT_ROOT = " . ServerVar("DOCUMENT_ROOT") . "<br>";
    echo "__FILE__ = " . __FILE__ . "<br>";
    echo "CurrentUserName() = " . CurrentUserName() . "<br>";
    echo "CurrentUserID() = " . CurrentUserID() . "<br>";
    echo "CurrentParentUserID() = " . CurrentParentUserID() . "<br>";
    echo "IsLoggedIn() = " . (IsLoggedIn() ? "true" : "false") . "<br>";
    echo "IsAdmin() = " . (IsAdmin() ? "true" : "false") . "<br>";
    echo "IsSysAdmin() = " . (IsSysAdmin() ? "true" : "false") . "<br>";
    Security()->showUserLevelInfo();
}

/**
 * Generate a unique file name for a folder (filename(n).ext)
 *
 * @param string|string[] $folders Output folder(s)
 * @param string $orifn Original file name
 * @param bool $indexed Index starts from '(n)' at the end of the original file name
 * @return string
 */
function UniqueFilename(string|array $folders, string $orifn, bool $indexed = false): string
{
    if ($orifn == "") {
        $orifn = date("YmdHis") . ".bin";
    }
    $info = pathinfo($orifn);
    $dirname = $info["dirname"]; // Directory name or "."
    $fn = $filename = $info["filename"];
    $ext = $info["extension"] ?? "";
    $i = 1;
    if ($indexed && preg_match('/\((\d+)\)$/', $filename, $matches)) { // Match '(n)' at the end of the file name
        $i = (int)$matches[1];
        $filename = preg_replace('/\(\d+\)$/', '', $filename); // Remove "(n)" at the end of the file name
    }
    $folders = is_array($folders) ? $folders : [$folders];
    foreach ($folders as $folder) {
        $destpath = PathJoin($folder, $dirname, $fn) . ($ext ? "." . $ext : "");
        while (FileExists($destpath)) {
            $i++;
            $fn = $filename . "(" . $i . ")"; // . ($ext ? "." . $ext : ""); // modified by Masino Sinaga, March 18, 2025
            $destpath = PathJoin($folder, $dirname, $fn) . ($ext ? "." . $ext : "");
        }
    }
    return PathJoin($dirname, $fn) . ($ext ? "." . $ext : "");
}

/**
 * Fix upload temp file names (avoid duplicate)
 *
 * @param DbField $fld Field object
 */
function FixUploadTempFileNames(DbField $fld): void
{
    if ($fld->UploadMultiple) {
        $newFiles = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $fld->Upload->FileName);
        $tempPath = UploadTempPath($fld, $fld->Upload->Index);
        $newFileCount = count($newFiles);
        for ($i = $newFileCount - 1; $i >= 0; $i--) {
            $newFile = $newFiles[$i];
            if (!IsEmpty($newFile)) {
                $tempFile = $newFile;
                for ($j = $i - 1; $j >= 0; $j--) { // Temp files with same names
                    if ($newFiles[$j] == $tempFile) {
                        $tempFile = UniqueFilename($tempPath, $newFile, true);
                    }
                }
                if ($tempFile != $newFile) { // Create a copy
                    CopyFile($tempPath . $newFile, $tempPath . $tempFile);
                }
                $newFiles[$i] = $tempFile;
            }
        }
        $fld->Upload->FileName = implode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $newFiles);
    }
}

/**
 * Fix upload file names (avoid duplicate files on upload folder)
 *
 * @param DbField $fld Field object
 */
function FixUploadFileNames(DbField $fld): void
{
    $newFiles = $fld->UploadMultiple
        ? explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $fld->Upload->FileName)
        : [ $fld->Upload->FileName ];
    $oldFiles = IsEmpty($fld->Upload->DbValue) ? [] : ($fld->UploadMultiple
        ? explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $fld->htmlDecode($fld->Upload->DbValue))
        : [ $fld->htmlDecode($fld->Upload->DbValue) ]);
    $tempPath = UploadTempPath($fld, $fld->Upload->Index);
    $workPath = IncludeTrailingDelimiter($tempPath . "__work", true);
    $newFileCount = count($newFiles);
    for ($i = 0; $i < $newFileCount; $i++) {
        if (!IsEmpty($newFiles[$i])) {
            $file = $newFiles[$i];
            if (FileExists($tempPath . $file)) {
                $oldFileFound = false;
                $oldFileCount = count($oldFiles);
                for ($j = 0; $j < $oldFileCount; $j++) {
                    $oldFile = $oldFiles[$j];
                    if ($oldFile == $file) { // Old file found, no need to delete anymore
                        array_splice($oldFiles, $j, 1);
                        $oldFileFound = true;
                        break;
                    }
                }
                if ($oldFileFound) { // No need to check if file exists further
                    continue;
                }
                MoveFile($tempPath . $file, $workPath . $file); // Move to work folder before checking
                $file1 = UniqueFilename([$fld->UploadPath, $tempPath], $file, true); // Get new file name
                if ($file1 != $file) { // Rename temp file
                    MoveFile($workPath . $file, $tempPath . $file1);
                    $newFiles[$i] = $file1;
                } else { // Move back
                    MoveFile($workPath . $file, $tempPath . $file);
                }
            }
        }
    }
    // DeletePath($workPath);
    $fld->Upload->DbValue = implode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $oldFiles);
    $fld->Upload->FileName = implode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $newFiles);
}

/**
 * Save upload files to upload folder
 *
 * @param DbField $fld Field object
 * @param ?string $fileNames File names
 * @param bool $resize Resize file
 * @return bool
 */
function SaveUploadFiles(DbField $fld, ?string $fileNames, bool $resize): bool
{
    $tempPath = UploadTempPath($fld, $fld->Upload->Index);
    $newFiles = $fld->UploadMultiple
        ? explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $fld->Upload->FileName)
        : [ $fld->Upload->FileName ];
    if (!IsEmpty($fld->Upload->FileName)) {
        if (SameString($fld->Upload->FileName, $fileNames)) { // Not changed in server event
            $fileNames = "";
        }
        $newFiles2 = IsEmpty($fileNames) ? [] : ($fld->UploadMultiple
            ? explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $fileNames)
            : [ $fileNames ]);
        $newFileCount = count($newFiles);
        for ($i = 0; $i < $newFileCount; $i++) {
            $newFile = $newFiles[$i];
            $newFile2 = count($newFiles2) > $i ? $newFiles2[$i] : "";
            if (!IsEmpty($newFile)) {
                $file = $tempPath . $newFile;
                if (FileExists($file)) {
                    if (!IsEmpty($newFile2)) { // Use correct file name
                        $newFile = $newFile2;
                    }
                    $res = $resize
                        ? $fld->Upload->resizeAndSaveToFile($fld->ImageWidth, $fld->ImageHeight, 100, $newFile, true, $i) // Resize
                        : $fld->Upload->saveToFile($newFile, true, $i); // Save
                    if (!$res) {
                        return false;
                    }
                }
            }
        }
    }
    if (Config("DELETE_UPLOADED_FILES")) {
        $oldFiles = IsEmpty($fld->Upload->DbValue) ? [] : ($fld->UploadMultiple
            ? explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $fld->htmlDecode($fld->Upload->DbValue))
            : [ $fld->htmlDecode($fld->Upload->DbValue) ]);
        foreach ($oldFiles as $oldFile) {
            if (!IsEmpty($oldFile) && !in_array($oldFile, $newFiles)) {
                DeleteFile($fld->uploadPath() . $oldFile);
            }
        }
    }
    return true;
}

// Get refer URL
function ReferUrl(): string
{
    $url = ServerVar("HTTP_REFERER");
    $pattern = '/^' . preg_quote(DomainUrl(), '/') . '(?=\/)/';
    if (preg_match($pattern, $url)) {
        $url = preg_replace($pattern, "", $url);
    }
    return $url;
}

// Get refer page name
function ReferPageName(): string
{
    return GetPageName(ReferUrl());
}

// Get script physical folder
function ScriptFolder(): string
{
    $folder = "";
    $path = ServerVar("SCRIPT_FILENAME");
    $p = strrpos($path, PATH_DELIMITER);
    if ($p !== false) {
        $folder = substr($path, 0, $p);
    }
    return ($folder != "") ? $folder : realpath(".");
}

// Get a temp folder for temp file
function TempFolder(): ?string
{
    $folders = [];
    if (IS_WINDOWS) {
        $folders[] = ServerVar("TEMP");
        $folders[] = ServerVar("TMP");
    } else {
        if (Config("USER_UPLOAD_TEMP_PATH") != "") {
            $folders[] = PrefixDirectoryPath(Config("USER_UPLOAD_TEMP_PATH"));
        }
        $folders[] = "/tmp";
    }
    if (ini_get("upload_tmp_dir")) {
        $folders[] = ini_get("upload_tmp_dir");
    }
    if ($folder = array_find($folders, fn($folder) => is_dir($folder))) {
        return $folder;
    }
    return null;
}

/**
 * Create folder (native filesystem)
 *
 * @param string|iterable $dirs Directories
 * @param int $mode Permissions
 * @return bool
 */
function CreateFolder(string|iterable $dirs, int $mode = 0777): bool
{
    try {
        Container("native.filesystem")->mkdir($dir);
        return true;
    } catch (IOException $e) {
        return false;
    }
}

// FileSystem methods
// https://flysystem.thephpleague.com/docs/usage/filesystem-api/
// - ReadFile
// - WriteFile
// - Delete
// - DeleteDirectory
// - ListContents
// - FileExists
// - DirectoryExists
// - LastModified
// - MimeType
// - FileSize
// - CreateDirectory
// - MoveFile
// - CopyFile
// https://flysystem.thephpleague.com/docs/usage/public-urls/
// - PublicUrl

/**
 * Get file system for different storage types based on Flysystem
 * See https://flysystem.thephpleague.com/docs/
 *
 * @param Filesystem|string|null $uri File system or URI, e.g. "s3://bucket", "google.storage://bucket", "azure.blob://container"
 * @return ?Filesystem
 */
function GetFileSystem(Filesystem|string|null $uri = null): ?Filesystem
{
	if ($uri instanceof Filesystem) {
        return $uri;
    }
    $info = GetRemotePathInfo($uri);
    return $info["storage"] ? Container($info["storage"]) : Container("local.filesystem");
}

/**
 * Prefix path (for local file system)
 *
 * @param string $path Path
 * @return string Prefixed path
 */
function PrefixPath(string $path): string
{
    return IsRemote($path) ? $path : Container("local.filesystem.prefixer")->prefixPath($path);
}

/**
 * Prefix directory path
 *
 * @param string $path Directory path
 * @return string Prefixed path
 */
function PrefixDirectoryPath(string $path): string
{
    return IsRemote($path) ? IncludeTrailingDelimiter($path, false) : Container("local.filesystem.prefixer")->prefixDirectoryPath($path);
}

/**
 * Get remote file path information
 *
 * @param string $uri File URI, e.g. "s3://bucket/path", "google.storage://bucket/path", "azure.blob://container/path"
 * @return array
 */
function GetRemotePathInfo(?string $uri): array
{
    if ($path = $uri && $components = parse_url($uri)) {
        $container = Container();
        $type = $components["scheme"] ?? null; // Storage type
        $bucket = $components["host"] ?? null; // Bucket or container name
        $path = $components["path"] ?? null;
        if ($type && $bucket) {
            $storage = "$type://$bucket";
            if ($container->has("$type.client") && $container->has("$type.filesystem.adapter")) {
                if (!$container->has($storage)) {
                    $parameters = in_array($type, ["http", "https"])
                        ? ["client" => $container->call($container->get("http.client"), ["baseUri" => $storage])]
                        : ["client" => $container->get("$type.client"), "bucket" => $bucket];
                    $adapter = $container->call($container->get("$type.filesystem.adapter"), $parameters);
                    $container->set($storage, new Filesystem($adapter));
                }
                return ["storage" => $storage, "path" => $path];
            }
            throw new Exception("The storage type '$type' is not supported");
        }
    }
    return ["storage" => null, "path" => $path];
}

/**
 * Get public URL by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/public-urls/
 *
 * @param string $path File path
 * @param Filesystem|string|null $fileSystem File system
 * @return string
 */
function GetFilePublicUrl(string $path, Filesystem|string|null $fileSystem = null): string
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        return $fileSystem->publicUrl($pathInfo["path"]);
    } catch (FilesystemException $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return $path;
}

/**
 * Read file by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path File path
 * @param Filesystem|string|null $fileSystem File system
 * @return string
 */
function ReadFile(string $path, Filesystem|string|null $fileSystem = null): string
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        return $fileSystem->fileExists($pathInfo["path"]) ? $fileSystem->read($pathInfo["path"]) : "";
    } catch (FilesystemException | \League\Flysystem\UnableToReadFile $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return "";
}

/**
 * Write file by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path File path
 * @param string $data File data
 * @param array $config Configuration array
 * @param Filesystem|string|null $fileSystem File system
 * @return bool
 */
function WriteFile(string $path, string $data, array $config = [], Filesystem|string|null $fileSystem = null): bool
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        $fileSystem->write($pathInfo["path"], $data, $config);
        return true;
    } catch (FilesystemException | \League\Flysystem\UnableToWriteFile $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * Delete file by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path File path
 * @param Filesystem|string|null $fileSystem File system
 * @return bool
 */
function DeleteFile(string $path, Filesystem|string|null $fileSystem = null): bool
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        if ($fileSystem->fileExists($pathInfo["path"])) {
            $fileSystem->delete($pathInfo["path"]);
        }
        return true;
    } catch (FilesystemException | \League\Flysystem\UnableToDeleteFile $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * Delete directory by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path Directory path
 * @param Filesystem|string|null $fileSystem File system
 * @return bool
 */
function DeleteDirectory(string $path, Filesystem|string|null $fileSystem = null): bool
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        if ($fileSystem->directoryExists($pathInfo["path"])) {
            $fileSystem->deleteDirectory($pathInfo["path"]);
        }
        return true;
    } catch (FilesystemException | \League\Flysystem\UnableToDeleteDirectory $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * List contents by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path Directory path
 * @param bool $recursive Recursive
 * @param Filesystem|string|null $fileSystem File system
 * @return DirectoryListing
 */
function ListContents(string $path, bool $recursive, Filesystem|string|null $fileSystem = null): DirectoryListing
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        return $fileSystem->listContents($path, $recursive);
    } catch (FilesystemException | \League\Flysystem\UnableToDeleteDirectory $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * Check if file exists by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path File path
 * @param Filesystem|string|null $fileSystem File system
 * @return bool
 */
function FileExists(string $path, Filesystem|string|null $fileSystem = null): bool
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        return $fileSystem->fileExists($pathInfo["path"]);
    } catch (FilesystemException | \League\Flysystem\UnableToCheckExistence $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * Check if directory exists by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path Directory path
 * @param Filesystem|string|null $fileSystem File system
 * @return bool
 */
function DirectoryExists(string $path, Filesystem|string|null $fileSystem = null): bool
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        return $fileSystem->directoryExists($pathInfo["path"]);
    } catch (FilesystemException | \League\Flysystem\UnableToCheckExistence $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * Get file last modified timestamp by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path File path
 * @param Filesystem|string|null $fileSystem File system
 * @return timestamp
 */
function GetFileLastModified(string $path, Filesystem|string|null $fileSystem = null): timestamp
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        return $fileSystem->fileExists($pathInfo["path"]) ? $fileSystem->lastModified($pathInfo["path"]) : 0;
    } catch (FilesystemException | \League\Flysystem\UnableToRetrieveMetadata $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return 0;
}

/**
 * Get file mime type by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path File path
 * @param Filesystem|string|null $fileSystem File system
 * @return string
 */
function GetFileMimeType(string $path, Filesystem|string|null $fileSystem = null): string
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        return $fileSystem->fileExists($pathInfo["path"]) ? $fileSystem->mimeType($pathInfo["path"]) : "";
    } catch (FilesystemException | \League\Flysystem\UnableToRetrieveMetadata $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return "";
}

/**
 * Get file size by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path File path
 * @param Filesystem|string|null $fileSystem File system
 * @return int
 */
function GetFileSize(string $path, Filesystem|string|null $fileSystem = null): int
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        return $fileSystem->fileExists($pathInfo["path"]) ? $fileSystem->fileSize($pathInfo["path"]) : -1;
    } catch (FilesystemException | \League\Flysystem\UnableToRetrieveMetadata $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return -1;
}

/**
 * Create directory by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $path Directory path
 * @param array $config Configuration array
 * @param Filesystem|string|null $fileSystem File system
 * @return bool
 */
function CreateDirectory(string $path, array $config = [], Filesystem|string|null $fileSystem = null): bool
{
    try {
        $pathInfo = GetRemotePathInfo($path);
        $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
        if (!$fileSystem->directoryExists($pathInfo["path"])) {
            $fileSystem->createDirectory($pathInfo["path"], $config);
        }
        return true;
    } catch (FilesystemException | \League\Flysystem\UnableToCreateDirectory $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * Move file by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $sourcePath Source file path
 * @param string $destinationPath Destination file path
 * @param array $config Configuration array
 * @param Filesystem|string|null $fileSystem File system
 * @return bool
 */
function MoveFile(string $sourcePath, string $destinationPath, array $config = [], Filesystem|string|null $fileSystem = null): bool
{
    try {
        $pathInfo = GetRemotePathInfo($sourcePath);
        $pathInfo2 = GetRemotePathInfo($destinationPath);
        if ($pathInfo["storage"] === $pathInfo2["storage"]) { // Same file system
            $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
            if ($fileSystem->fileExists($pathInfo["path"])) {
                $fileSystem->move($pathInfo["path"], $pathInfo2["path"], $config);
                return true;
            }
        } else {
            $fileSystem = $pathInfo["storage"] ? GetFileSystem($pathInfo["storage"]) : GetFileSystem($fileSystem);
            if ($fileSystem->fileExists($pathInfo["path"])) {
                $data = $fileSystem->read($pathInfo["path"]);
                $fileSystem2 = $pathInfo2["storage"] ? GetFileSystem($pathInfo2["storage"]) : GetFileSystem($fileSystem);
                $fileSystem2->write($pathInfo2["path"], $data);
                $fileSystem->delete($pathInfo["path"]);
                return true;
            }
        }
    } catch (FilesystemException | \League\Flysystem\UnableToMoveFile $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * Copy file by Filesystem
 * See https://flysystem.thephpleague.com/docs/usage/filesystem-api/
 *
 * @param string $sourcePath Source file path
 * @param string $destinationPath Destination file path
 * @param array $config Configuration array
 * @param Filesystem|string|null $fileSystem File system
 * @return bool
 */
function CopyFile(string $sourcePath, string $destinationPath, array $config = [], Filesystem|string|null $fileSystem = null): bool
{
    try {
        $pathInfo = GetRemotePathInfo($sourcePath);
        $pathInfo2 = GetRemotePathInfo($destinationPath);
        if ($pathInfo["storage"] === $pathInfo2["storage"]) { // Same file system
            $fileSystem = $fileSystem instanceof Filesystem ? $fileSystem : GetFileSystem($pathInfo["storage"] ?? $fileSystem);
            if ($fileSystem->fileExists($pathInfo["path"])) {
                $fileSystem->copy($pathInfo["path"], $pathInfo2["path"], $config);
                return true;
            }
        } else {
            $fileSystem = $pathInfo["storage"] ? GetFileSystem($pathInfo["storage"]) : GetFileSystem($fileSystem);
            if ($fileSystem->fileExists($pathInfo["path"])) {
                $data = $fileSystem->read($pathInfo["path"]);
                $fileSystem2 = $pathInfo2["storage"] ? GetFileSystem($pathInfo2["storage"]) : GetFileSystem($fileSystem);
                $fileSystem2->write($pathInfo2["path"], $data);
                return true;
            }
        }
    } catch (FilesystemException | \League\Flysystem\UnableToMoveFile $exception) {
        $message = $exception->getMessage();
        LogError($message);
        // if (IsDebug()) {
        //     throw $exception;
        // }
    }
    return false;
}

/**
 * Generate random number
 *
 * @param int $length Number of digits
 * @return int
 */
function Random(int $length = 8): int
{
    $min = pow(10, $length - 1);
    $max = pow(10, $length) - 1;
    return mt_rand($min, $max);
}

// Calculate field hash
function GetFieldHash(mixed $value): string
{
    return md5(GetFieldValueAsString($value));
}

// Get field value as string
function GetFieldValueAsString(mixed $value): string
{
    if ($value === null || is_resource($value)) {
        return "";
    }
    if (strlen($value) > 65535) { // BLOB/TEXT
        if (Config("BLOB_FIELD_BYTE_COUNT") > 0) {
            return substr($value, 0, Config("BLOB_FIELD_BYTE_COUNT"));
        } else {
            return $value;
        }
    }
    return strval($value);
}

/**
 * Create temp image file from binary data and return cid or base64 URL
 *
 * @param string $filedata File or file data
 * @param string $cid Output as cid URL, otherwise as base64 URL
 * @return string cid or base64 URL
 */
function TempImage(string $filedata, bool $cid = false): string
{
    global $TempImages;
    $folder = UploadTempPath();
    CreateDirectory($folder);
    $folder = PrefixDirectoryPath($folder);
    $file = @tempnam($folder, "tmp");
    if (!$file || !Path::isBasePath($folder, $file)) {
        if (IsDebug()) {
            LogError("Failed to create temporary file in " . $folder);
        }
        return "";
    }
    if (@file_put_contents($file, $filedata, LOCK_EX) === false) {
        if (IsDebug()) {
            LogError(sprintf("Failed to write temporary file %s: %s", $file, error_get_last()["message"] ?? ""));
        }
        return "";
    }
    $ct = MimeContentType($file);
    if (!in_array($ct, ["image/gif", "image/jpeg", "image/png", "image/bmp"])) {
        if (IsDebug()) {
            LogError("Image of content type '{$ct}' is not supported");
        }
        return "";
    }
    $ext = "." . MimeTypes()->getExtensions($ct)[0];
    $outfile = preg_replace('/\.tmp$/i', $ext, $file);
    rename($file, $outfile);
    $tempImage = basename($outfile);
    $TempImages[] = $tempImage;
    return $cid
        ? "cid:" . GetContentId($tempImage) // Temp image as cid URL (Symfony/Mime/Email needs the @ character for cid)
        : ImageFileToBase64Url($outfile); // Temp image as base64 URL
}

/**
 * Get content ID (cid)
 *
 * @return string
 */
function GetContentId(string $fileName): string
{
    return pathinfo($fileName, PATHINFO_FILENAME) . "@" . (Config("CID_SUFFIX") ?? CurrentHost()); // Symfony/Mime/Email needs the @ character for cid
}

// Get image tag from base64 data URL (data:mime type;base64,image data)
function ImageFileToBase64Url(string $imageFile): string
{
    if (!file_exists($imageFile)) { // File not found, ignore
        return $imageFile;
    }
    return "data:" . MimeContentType($imageFile) . ";base64," . base64_encode(file_get_contents($imageFile));
}

// Extract data from base64 data URL (data:mime type;base64,image data)
function DataFromBase64Url(string $dataUrl): ?string
{
    return StartsString("data:", $dataUrl) && ContainsString($dataUrl, ";base64,")
        ? base64_decode(substr($dataUrl, strpos($dataUrl, ";base64,") + 8))
        : null;
}

// Get temp image from base64 data URL (data:mime type;base64,image data)
function TempImageFromBase64Url(string $dataUrl): string
{
    $data = DataFromBase64Url($dataUrl);
    if ($data) {
        $fn = Random() . ContentExtension($data);
        WriteFile(UploadTempPath() . $fn, $data);
        $dataUrl = UploadTempPath() . $fn;
    }
    return $dataUrl;
}

// Add query string to URL
function UrlAddQuery(string $url, string $qry): string
{
    if (strval($qry) == "") {
        return $url;
    }
    return $url . (ContainsString($url, "?") ? "&" : "?") . $qry;
}

/**
 * Functions for image resize
 */

// Resize binary to thumbnail
// Note: $quality is deprecated and is kept for backward compatibility only
function ResizeBinary(string &$filedata, int &$width, int &$height, int $quality = 100, array $plugins = [], array $resizeOptions = []): bool
{
    if ($width <= 0 && $height <= 0) {
        return false;
    }
    if (is_array($quality) && count($plugins) == 0) {
        $plugins = $quality;
    }
    $f = @tempnam(TempFolder(), "tmp");
    $handle = @fopen($f, 'wb');
    if ($handle) {
        fwrite($handle, $filedata);
        fclose($handle);
    }
    $format = "";
    if (file_exists($f) && filesize($f) > 0) { // Temp file created
        $info = @getimagesize($f);
        @gc_collect_cycles();
        @unlink($f);
        if (!$info || !in_array($info[2], [1, 2, 3])) { // Not gif/jpg/png
            return false;
        } elseif ($info[2] == 1) {
            $format = "GIF";
        } elseif ($info[2] == 2) {
            $format = "JPG";
        } elseif ($info[2] == 3) {
            $format = "PNG";
        }
    } else { // Temp file not created
        if (StartsString("\x47\x49\x46\x38\x37\x61", $filedata) || StartsString("\x47\x49\x46\x38\x39\x61", $filedata)) {
            $format = "GIF";
        } elseif (StartsString("\xFF\xD8\xFF\xE0", $filedata) && substr($filedata, 6, 5) == "\x4A\x46\x49\x46\x00") {
            $format = "JPG";
        } elseif (StartsString("\x89\x50\x4E\x47\x0D\x0A\x1A\x0A", $filedata)) {
            $format = "PNG";
        } else {
            return false;
        }
    }
    $cls = Config("THUMBNAIL_CLASS");
    $options = array_merge(Config("RESIZE_OPTIONS") + ["isDataStream" => true, "format" => $format], $resizeOptions);
    $thumb = new $cls($filedata, $options, $plugins);
    return $thumb->resizeEx($filedata, $width, $height);
}

// Resize file to thumbnail file
function ResizeFile(string $fn, string $tn, int &$width, int &$height, array $plugins = [], array $resizeOptions = []): void
{
    $info = @getimagesize($fn);
    if (!$info || !in_array($info[2], [1, 2, 3]) || $width <= 0 && $height <= 0) {
        if ($fn != $tn) {
            copy($fn, $tn);
        }
        return;
    }
    $cls = Config("THUMBNAIL_CLASS");
    $resizeOptions = array_merge(Config("RESIZE_OPTIONS"), $resizeOptions);
    $thumb = new $cls($fn, $resizeOptions, $plugins);
    $fdata = null;
    if (!$thumb->resizeEx($fdata, $width, $height, $tn)) {
        if ($fn != $tn) {
            copy($fn, $tn);
        }
    }
}

// Resize file to binary
function ResizeFileToBinary(string $fn, int &$width, int &$height, array $plugins = []): ?string
{
    $info = @getimagesize($fn);
    if (!$info) {
        return null;
    }
    if (!in_array($info[2], [1, 2, 3]) || $width <= 0 && $height <= 0) {
        $fdata = file_get_contents($fn);
    } else {
        $cls = Config("THUMBNAIL_CLASS");
        $thumb = new $cls($fn, Config("RESIZE_OPTIONS"), $plugins);
        $fdata = null;
        if (!$thumb->resizeEx($fdata, $width, $height)) {
            $fdata = file_get_contents($fn);
        }
    }
    return $fdata;
}

/**
 * Functions for Auto-Update fields
 */

// Get user IP
function CurrentUserIP(): ?string
{
    return ServerVar("HTTP_CLIENT_IP")
        ?: ServerVar("HTTP_X_FORWARDED_FOR")
        ?: ServerVar("HTTP_X_FORWARDED")
        ?: ServerVar("HTTP_FORWARDED_FOR")
        ?: ServerVar("HTTP_FORWARDED")
        ?: ServerVar("REMOTE_ADDR");
}

// Is local host
function IsLocal(): bool
{
    return in_array(CurrentUserIP(), ["127.0.0.1", "::1"]);
}

// Get current host name, e.g. "www.mycompany.com"
function CurrentHost(): string
{
    return ServerVar("HTTP_HOST") ?: ServerVar("SERVER_NAME");
}

// Get current Windows user (for Windows Authentication)
function CurrentWindowsUser(): ?string
{
    return ServerVar(Config("WINDOWS_USER_KEY"));
}

/**
 * Get current date in default date format
 *
 * @param int $namedformat Format = -1|5|6|7 (see comment for FormatDateTime)
 * @return string
 */
function CurrentDate(int $namedformat = -1): string
{
    if (in_array($namedformat, [5, 6, 7, 9, 10, 11, 12, 13, 14, 15, 16, 17])) {
        return match ($namedformat) {
            5, 9, 12, 15 => FormatDateTime(date('Y-m-d'), 5),
            6, 10, 13, 16 => FormatDateTime(date('Y-m-d'), 6),
            default => FormatDateTime(date('Y-m-d'), 7)
        };
    }
    return date('Y-m-d');
}

// Get current time in hh:mm:ss format
function CurrentTime(): string
{
    return date("H:i:s");
}

/**
 * Get current date in default date format with time in hh:mm:ss format
 *
 * @param int $namedformat Format = -1, 5-7, 9-11 (see comment for FormatDateTime)
 * @return string
 */
function CurrentDateTime(int $namedformat = -1): string
{
    if (in_array($namedformat, [5, 6, 7, 9, 10, 11, 12, 13, 14, 15, 16, 17])) {
        return match ($namedformat) {
            5, 9, 12, 15 => FormatDateTime(date('Y-m-d H:i:s'), 9),
            6, 10, 13, 16 => FormatDateTime(date('Y-m-d H:i:s'), 10),
            default => FormatDateTime(date('Y-m-d H:i:s'), 11)
        };
    }
    return date('Y-m-d H:i:s');
}

// Get current date in standard format (yyyy/mm/dd)
function StdCurrentDate(): string
{
    return date('Y/m/d');
}

// Get date in standard format (yyyy/mm/dd)
function StdDate($ts): string
{
    return date('Y/m/d', $ts);
}

// Get current date and time in standard format (yyyy/mm/dd hh:mm:ss)
function StdCurrentDateTime(): string
{
    return date('Y/m/d H:i:s');
}

// Get date/time in standard format (yyyy/mm/dd hh:mm:ss)
function StdDateTime($ts): string
{
    return date('Y/m/d H:i:s', $ts);
}

// Get current date and time in database format (yyyy-mm-dd hh:mm:ss)
function DbCurrentDateTime(): string
{
    return date('Y-m-d H:i:s');
}

// Begin of added by Masino Sinaga, September 17, 2023
// Get current date in database format (yyyy-mm-dd)
function DbCurrentDate()
{
    return date('Y-m-d');
}
// End of added by Masino Sinaga, September 17, 2023

// Encrypt password
#[Deprecated("Password upgrade is recommended, use HashPassword() instead", "2025")]
function EncryptPassword(string $input, string $salt = ""): string
{
    if (Config("PASSWORD_HASH")) {
        return password_hash($input, PASSWORD_DEFAULT);
    } else {
        return strval($salt) != "" ? md5($input . $salt) . ":" . $salt : md5($input);
    }
}

/**
 * Compare password
 * Note: If salted, password must be stored in '<hashedstring>:<salt>'
 *
 * @param string $pwd Password to compare
 * @param string $input Input password
 * @return bool
 */
#[Deprecated("Password upgrade is recommended, use VerifyPassword() instead", "2025")]
function ComparePassword(string $pwd, string $input): bool
{
    if (preg_match('/^\$[HP]\$/', $pwd ?? "")) { // phpass
        $ar = Config("PHPASS_ITERATION_COUNT_LOG2");
        foreach ($ar as $i) {
            $hasher = new PasswordHash($i, true);
            if ($hasher->checkPassword($input, $pwd)) {
                return true;
            }
        }
        return false;
    } elseif (ContainsString($pwd, ":")) { // <hashedstring>:<salt>
        @list($crypt, $salt) = explode(":", $pwd, 2);
        if ($pwd == EncryptPassword($input, $salt)) {
            return true;
        }
    }
    if (Config("CASE_SENSITIVE_PASSWORD")) {
        if (Config("ENCRYPTED_PASSWORD")) {
            if (Config("PASSWORD_HASH")) {
                return password_verify($input, $pwd);
            } else {
                return ($pwd == EncryptPassword($input));
            }
        } else {
            return ($pwd == $input);
        }
    } else {
        if (Config("ENCRYPTED_PASSWORD")) {
            if (Config("PASSWORD_HASH")) {
                return password_verify(strtolower($input), $pwd);
            } else {
                return ($pwd == EncryptPassword(strtolower($input)));
            }
        } else {
            return SameText($pwd, $input);
        }
    }
}

/**
 * Get password hasher
 *
 * @return ?PasswordHasherInterface
 */
function GetPasswordHasher(?string $className = null): ?PasswordHasherInterface
{
    if ($className === null && !Config("ENCRYPTED_PASSWORD")) {
        return null;
    }
    $hasher = Config("SECURITY.password_hashers")[$className ?? PasswordAuthenticatedUserInterface::class] ?? null;
    if ($hasher["id"] ?? false) {
        $options = [
            "class" => $hasher["id"],
            "arguments" => []
        ];
    } else {
        $options = $hasher;
    }
    $factory = new PasswordHasherFactory(["common" => $options]);
    return $factory->getPasswordHasher("common");
}

/**
 * Hash password
 *
 * @param string $password Plain text password
 * @return string Hashed password
 */
function HashPassword(string $password): string
{
    return GetPasswordHasher()?->hash($password) ?? $password;
}

/**
 * Verify password
 *
 * @param string $hash Hashed password
 * @param mixed $password Plain text password
 * @return bool
 */
function VerifyPassword(string $hash, mixed $password): bool
{
    return GetPasswordHasher()?->verify($hash, $password) ?? $hash == $password;
}

/**
 * Get security object
 *
 * @return AdvancedSecurity
 */
function Security(): AdvancedSecurity
{
    return Container("app.security");
}

/**
 * Session helper function
 *
 * @return mixed Session value or Session
 */
function Session(mixed ...$args): mixed
{
    $session ??= Container(Session::class);
    $numargs = count($args);
    if ($numargs == 0) { // Get session object
        return $session;
    } elseif ($numargs == 1) {
        if (is_string($args[0])) { // Get
            return $session->get($args[0]);
        } elseif (is_array($args[0])) { // Set
            foreach ($args[0] as $key => $value) {
                $session->set($key, $value);
            }
            return $session;
        }
    } elseif ($numargs == 2) { // Set
        $session->set($args[0], $args[1]);
        return $args[1];
    }
}

/**
 * Get session ID
 *
 * @return string
 */
function SessionId(): string
{
    return Session()->getId();
}

/**
 * Add tab ID to session name
 *
 * @param string $name Session name
 * @param ?string $id Tab ID
 * @return string
 */
function AddTabId(string $name, ?string $id = null): string
{
    if (Config("USE_TAB_ID") && !SameString($name, Captcha()->getSessionName("login"))) { // Skip if login
        $id ??= ReadCookie("TabId") ?? "";
        return $name . ($id ? "-" . $id : "");
    }
    return $name;
}

/**
 * Get flash bag
 *
 * @return FlashBagInterface
 */
function FlashBag(): FlashBagInterface
{
    return Session()->getFlashBag();
}

/**
 * Current user
 *
 * @return ?UserInterface
 */
function CurrentUser(): ?UserInterface
{
    return SecurityHelper()?->getUser();
}

/**
 * Get/Set profile value(s)
 *
 * @param array $args
 *   If no arguments, returns UserProfile instance.
 *   If count($args) is 1 and the argument is string, get value.
 *   If count($args) is 1 and the argument is array, set values and save to storage.
 *   If count($args) is 2, set value and save to storage.
 * @return mixed
 */
function Profile(array ...$args): mixed
{
    $profile = Container("user.profile");
    if ($profile) {
        $numargs = count($args);
        if ($numargs == 1) {
            if (is_string($args[0])) { // $args[0] is string  => Get value
                return $profile->get($args[0]); // Return mixed
            } elseif (is_array($args[0])) { // $args[0] is array => Set values
                foreach ($args[0] as $key => $value) {
                    $profile->set($key, $value);
                }
                $profile->saveToStorage();
            }
        } elseif ($numargs == 2) { // Set value
            $profile->set($args[0], $args[1])->saveToStorage();
        }
    }
    return $profile; // Return UserProfile instance
}

/**
 * Get language object
 *
 * @return Language
 */
function Language(): Language
{
    return Container("app.language");
}

/**
 * Get breadcrumb object
 *
 * @return Breadcrumb
 */
function Breadcrumb(): Breadcrumb
{
    return Container(Breadcrumb::class);
}

/**
 * Get logger
 *
 * @return Monolog\Logger
 */
function Logger(): \Monolog\Logger
{
    return Container("app.logger");
}

/**
 * Get debug bar
 *
 * @param mixed $args Arguments
 * @return PhpDebugBar|DataCollectorInterface|null
 */
function DebugBar(mixed ...$args): PhpDebugBar|DataCollectorInterface|null
{
    if (!IsDebug()) {
        return null;
    }
    $debugBar = Container("debug.bar");
    $numargs = count($args);
    if ($numargs == 0) {
        return $debugBar; // PhpDebugBar
    } elseif ($numargs == 1) { // Get collector
        $name = $args[0];
        return $debugBar[$name]; // DataCollectorInterface
    }
    return null;
}

/**
 * Get debug bar JavaScript renderer
 *
 * @return JavascriptRenderer
 */
function DebugBarRenderer(): ?JavascriptRenderer
{
    return !IsExport() ? DebugBar()?->getJavascriptRenderer() : null;
}

/**
 * Add debug message
 * @param mixed $message A message which can be anything from an object to a string
 * @param string $label Label
 * @param bool $isString Message is string or not
 */
function AddDebugMessage(mixed $message, string $label = "info", bool $isString = true): void
{
    DebugBar()?->addMessage($message, $label, $isString);
}

/**
 * Adds a log record at the DEBUG level
 *
 * @param string $message The log message
 * @param array $context The log context
 */
function Log(string $msg, array $context = []): void
{
    Logger()->debug($msg, $context);
}

/**
 * Adds a log record at the ERROR level
 *
 * @param string $message The log message
 * @param array $context The log context
 */
function LogError(string $msg, array $context = []): void
{
    Logger()->error($msg, $context);
}

/**
 * Adds a log record at the INFO level
 *
 * @param string $message The log message
 * @param array $context The log context
 */
function LogInfo(string $msg, array $context = []): void
{
    Logger()->info($msg, $context);
}

/**
 * Functions for backward compatibility
 */

// Get current user name
function CurrentUserName(): string
{
    return Security()->currentUserName();
}

// Get current user ID
function CurrentUserID(): mixed
{
    return Security()->currentUserID();
}

// Get current user primary key
function CurrentUserPrimaryKey(): mixed
{
    return Security()->currentUserPrimaryKey();
}

// Get current parent user ID
function CurrentParentUserID(): mixed
{
    return Security()->currentParentUserID();
}

// Get current user level
function CurrentUserLevel(): int|string
{
    return Security()->currentUserLevelID();
}

// Get current user level name
function CurrentUserLevelName(): string
{
    return Security()->currentUserLevelName();
}

// Get current user level hierarchy (sub levels)
function CurrentUserLevelHierarchy(): array
{
    return Security()->currentUserLevelHierarchy();
}

// Get current user level list (comma separated)
function CurrentUserLevelList(): string
{
    return Security()->userLevelList();
}

// Get Current user info
function CurrentUserInfo(string $fldname): mixed
{
    return IsEntityUser() ? CurrentUser()->get($fldname) : null;
}

// Get current user identifier
function CurrentUserIdentifier(): ?string
{
    return CurrentUser()?->getUserIdentifier();
}

// Get current user email
function CurrentUserEmail(): ?string
{
    return Config("USER_EMAIL_FIELD_NAME") ? CurrentUserInfo(Config("USER_EMAIL_FIELD_NAME")) : null;
}

// Get current user image as base 64 string
function CurrentUserImageBase64(): ?string
{
    return Profile()->getUserImageBase64();
}

// Begin of added by Masino Sinaga, September 22, 2023
// Get user filter
function GetUserFilter($fieldName, $val)
{
    if ($fld = Container("users")->Fields[$fieldName]) {
        return "(" . QuotedName($fld->Name, Config("USER_TABLE_DBID")) . " = " . QuotedValue($val, $fld->DataType, Config("USER_TABLE_DBID")) . ")";
    }
    return "(0=1)"; // Show no records
}
// End of added by Masino Sinaga, September 22, 2023

// Get current page ID
function CurrentPageID(): string
{
    global $Page;
    return isset($Page) && property_exists($Page, "PageID") ? $Page->PageID : "";
}

// Get/Set current page title
function CurrentPageTitle(?string $value = null): string
{
    global $Page, $Title;
    if ($value !== null) { // Set
        if (isset($Page) && property_exists($Page, "Title")) {
            $Page->Title = $value;
        } else {
            $Title = $value;
        }
    } else { // Get
        if (isset($Page->Title)) {
            return $Page->Title;
        }
        return $Title ?? Language()->projectPhrase("BodyTitle");
    }
}

// Allow list
function AllowList(string $tableName): bool
{
    return Security()->allowList($tableName);
}

// Allow view
function AllowView(string $tableName): bool
{
    return Security()->allowView($tableName);
}

// Allow add
function AllowAdd(string $tableName): bool
{
    return Security()->allowAdd($tableName);
}

// Allow edit
function AllowEdit(string $tableName): bool
{
    return Security()->allowEdit($tableName);
}

// Allow delete
function AllowDelete(string $tableName): bool
{
    return Security()->allowDelete($tableName);
}

// Is password expired
function IsPasswordExpired(): bool
{
    return Security()->isPasswordExpired();
}

// Set session password expired
function SetSessionPasswordExpired(): void
{
    Security()->setSessionPasswordExpired();
}

// Is password reset
function IsPasswordReset(): bool
{
    return Security()->isPasswordReset();
}

// Is logging in
function IsLoggingIn(): bool
{
    return Security()->isLoggingIn();
}

// Is logging in (2FA)
function IsLoggingIn2FA(): bool
{
    return Security()->isLoggingIn2FA();
}

// Is logged in
function IsLoggedIn(): bool
{
    return Session(SESSION_STATUS) == "login" || Security()->isLoggedIn();
}

// Is admin
function IsAdmin(): bool
{
    return Session(SESSION_SYS_ADMIN) === 1 || Security()->isAdmin();
}

// Is system admin
function IsSysAdmin(): bool
{
    return Session(SESSION_SYS_ADMIN) === 1 || Security()->isSysAdmin();
}

// Is authenticated
function IsAuthenticated(): bool
{
    return CurrentUser() instanceof UserInterface;
}

/**
 * Checks if the attributes are granted against the current authentication token and optionally supplied subject, e.g.
 * IsGranted('ROLE_ALLOWED_TO_SWITCH'), IsGranted('ROLE_ADMIN'), IsGranted('ROLE_SUPER_ADMIN'),
 * IsGranted('IS_AUTHENTICATED'), IsGranted('IS_AUTHENTICATED_REMEMBERED'), IsGranted('IS_AUTHENTICATED_FULLY'),
 * IsGranted('IS_REMEMBERED'), IsGranted('IS_IMPERSONATOR'), IsGranted('PUBLIC_ACCESS')
 *
 * @param mixed $attributes Attributes
 * @param mixed $subject Subject
 * @return bool
 */
function IsGranted(mixed $attributes, mixed $subject = null): bool
{
    return SecurityHelper()?->isGranted($attributes, $subject) ?? false;
}

// Is impersonator (the current user is impersonating another user)
function IsImpersonator(): bool
{
    return IsGranted("IS_IMPERSONATOR");
}

// Original user
function OriginalUser(): ?UserInterface
{
    return IsImpersonator() ? SecurityHelper()?->getToken()->getOriginalToken()->getUser() : null;
}

// Is entity user
function IsEntityUser(?UserInterface $user = null): bool
{
    return ($user ?? CurrentUser()) instanceof AbstractEntity;
}

// Is super admin user
function IsSysAdminUser(?UserInterface $user = null): bool
{
    return ($user ??= CurrentUser())
        && $user instanceof InMemoryUser
        && $user->getUserIdentifier() == Config("ADMIN_USER_NAME");
}

// Is Windows user
function IsWindowsUser(?UserInterface $user = null): bool
{
    return ($user ?? Profile()->getUser() ?? CurrentUser()) instanceof WindowsUser;
}

// Is access token user (Google/Facebook/Microsoft/Saml)
function IsAccessTokenUser(?UserInterface $user = null): bool
{
    return ($user ?? Profile()->getUser() ?? CurrentUser()) instanceof AccessTokenUser;
}

// Is LDAP user
function IsLdapUser(?UserInterface $user = null): bool
{
    return false;
}

// Is export
function IsExport(string $format = ""): bool
{
    global $ExportType;
    $exportType = $ExportType ?: Param("export");
    return $format ? SameText($exportType, $format) : ($exportType != "");
}

// Get LDAP user
function GetLdapUser(): ?LdapUser
{
    return null;
}

// Encrypt with php-encryption
function PhpEncrypt(string $str, string $password = "", ?bool $encrypt = null): string
{
    if (!Config("ENCRYPTION_ENABLED") || IsEmpty($str)) {
        return $str;
    }
    try {
        return PhpEncryption::encryptWithPassword($str, $password ?: Config("ENCRYPTION_KEY"));
    } catch (Throwable $e) {
        if (IsDebug()) {
            LogError("Failed to encrypt. " . $e->getMessage());
        }
        return $str;
    }
}

// Decrypt with php-encryption
function PhpDecrypt(string $str, string $password = ""): string
{
    if (!Config("ENCRYPTION_ENABLED") || IsEmpty($str)) {
        return $str;
    }
    try {
        return PhpEncryption::decryptWithPassword($str, $password ?: Config("ENCRYPTION_KEY"));
    } catch (Throwable $e) {
        if (IsDebug()) {
            LogError("Failed to decrypt. " . $e->getMessage());
        }
        return $str;
    }
}

// Return encryption key (16 or 32 characters)
function AesEncryptionKey(string $key): string
{
    $size = str_contains(Config("AES_ENCRYPTION_CIPHER"), "256") ? 32 : 16;
    return strlen($key) == $size ? $key : (strlen($key) > $size ? substr($key, 0, $size) : str_pad($key, $size));
}

// Encrypt by AES
function Encrypt(string $str, string $key = ""): string
{
    if (IsEmpty($str)) {
        return $str;
    }
    try {
        if ($key) {
            return (new Encrypter(AesEncryptionKey($key), Config("AES_ENCRYPTION_CIPHER")))->encryptString($str);
        } else {
            return Container(Encrypter::class)->encryptString($str);
        }
    } catch (EncryptException $e) {
        if (IsDebug()) {
            LogError("Failed to encrypt. " . $e->getMessage());
        }
        return $str;
    }
}

// Decrypt by AES
function Decrypt(string $str, string $key = ""): string
{
    if (IsEmpty($str)) {
        return $str;
    }
    try {
        if ($key) {
            return (new Encrypter(AesEncryptionKey($key), Config("AES_ENCRYPTION_CIPHER")))->decryptString($str);
        } else {
            return Container(Encrypter::class)->decryptString($str);
        }
    } catch (DecryptException $e) {
        if (IsDebug()) {
            LogError("Failed to decrypt. " . $e->getMessage());
        }
        return $str;
    }
}

// URL-safe base64 encode
function UrlBase64Decode(string $input): string
{
    return base64_decode(strtr($input, "-_", "+/"));
}

// URL-safe base64 decode
function UrlBase64Encode(string $input): string
{
    return str_replace("=", "", strtr(base64_encode($input), "+/", "-_"));
}

/**
 * Remove XSS
 *
 * @param array|string|null $val String to be purified
 * @return array|string|null Purified string
 */
function RemoveXss(array|string|null $val): array|string|null
{
    if (IsEmpty($val)) {
        return htmlspecialchars($val); // modified by Masino Sinaga, Nov 12, 2023
    } elseif (is_array($val)) {
        return array_map(fn($v) => RemoveXss($v), $val);
    }
    return Container("html.purifier")->purify(htmlspecialchars($val)); // modified by Masino Sinaga, Nov 12, 2023
}

/**
 * HTTP request by cURL
 * Note: cURL must be enabled in PHP
 *
 * @param string $url URL
 * @param string|array $data Data for the request
 * @param string $method Request method, "GET"(default) or "POST"
 * @param array $options Other options
 * @return mixed Returns true on success or false on failure
 *  If the CURLOPT_RETURNTRANSFER option is set, returns the result on success, false on failure.
 */
function ClientUrl(string $url, string|array $data = "", string $method = "GET", array $options = []): mixed
{
    if (!function_exists("curl_init")) {
        throw new Exception("cURL not installed."); //** side effect
    }
    $ch = curl_init();
    $method = strtoupper($method);
    if ($method == "POST") {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    } elseif ($method == "GET") {
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_URL, $url . "?" . $data);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    foreach ($options as $option => $value) {
        curl_setopt($ch, $option, $value);
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * Calculate date difference
 *
 * @param string $dateTimeBegin Begin date
 * @param string $dateTimeEnd End date
 * @param string $interval Interval: "s": Seconds, "n": Minutes, "h": Hours, "d": Days (default), "w": Weeks, "ww": Calendar weeks, "m": Months, or "yyyy": Years
 * @return int|false
 */
function DateDiff(string $dateTimeBegin, string $dateTimeEnd, string $interval = "d"): int|false
{
    $dateTimeBegin = strtotime($dateTimeBegin);
    if ($dateTimeBegin === -1 || $dateTimeBegin === false) {
        return false;
    }
    $dateTimeEnd = strtotime($dateTimeEnd);
    if ($dateTimeEnd === -1 || $dateTimeEnd === false) {
        return false;
    }
    $dif = $dateTimeEnd - $dateTimeBegin;
    $arBegin = getdate($dateTimeBegin);
    $dateBegin = mktime(0, 0, 0, $arBegin["mon"], $arBegin["mday"], $arBegin["year"]);
    $arEnd = getdate($dateTimeEnd);
    $dateEnd = mktime(0, 0, 0, $arEnd["mon"], $arEnd["mday"], $arEnd["year"]);
    $difDate = $dateEnd - $dateBegin;
    switch ($interval) {
        case "s": // Seconds
            return $dif;
        case "n": // Minutes
            return ($dif > 0) ? floor($dif / 60) : ceil($dif / 60);
        case "h": // Hours
            return ($dif > 0) ? floor($dif / 3600) : ceil($dif / 3600);
        case "d": // Days
            return ($difDate > 0) ? floor($difDate / 86400) : ceil($difDate / 86400);
        case "w": // Weeks
            return ($difDate > 0) ? floor($difDate / 604800) : ceil($difDate / 604800);
        case "ww": // Calendar weeks
            $difWeek = (($dateEnd - $arEnd["wday"] * 86400) - ($dateBegin - $arBegin["wday"] * 86400)) / 604800;
            return ($difWeek > 0) ? floor($difWeek) : ceil($difWeek);
        case "m": // Months
            return (($arEnd["year"] * 12 + $arEnd["mon"]) - ($arBegin["year"] * 12 + $arBegin["mon"]));
        case "yyyy": // Years
            return ($arEnd["year"] - $arBegin["year"]);
    }
}

// Permission denied message
function DeniedMessage(): string
{
    return sprintf(Language()->phrase("NoPermission"), ScriptName());
}

// Init array
function InitArray(int $len, mixed $value): array
{
    if ($len > 0) {
        return array_fill(0, $len, $value);
    }
    return [];
}

// Init 2D array
function Init2DArray(int $len1, int $len2, mixed $value): array
{
    return InitArray($len1, InitArray($len2, $value));
}

/**
 * Validation functions
 */

/**
 * Check date
 *
 * @param mixed $value Value
 * @param int|string Formatter pattern
 * @return bool
 */
function CheckDate(mixed $value, int|string $format = ""): bool
{
    global $CurrentLocale, $TIME_ZONE;
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    $dt = trim($value);
    if (preg_match('/^([0-9]{4})-([0][1-9]|[1][0-2])-([0][1-9]|[1|2][0-9]|[3][0|1])( (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?)?$/', $dt)) { // Date/Time
        return true;
    }
    $fmt = new IntlDateFormatter($CurrentLocale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $TIME_ZONE, null, DateFormat($format));
    return $fmt->parse($dt) !== false; // Parse by $format
}

/**
 * Check time
 *
 * @param mixed $value Value
 * @param int|string Formatter pattern
 * @return bool
 */
function CheckTime(mixed $value, int|string $format = ""): bool
{
    global $CurrentLocale, $TIME_ZONE;
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    $dt = trim($value);
    if (preg_match('/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?$/', $dt)) { // Date/Time
        return true;
    }
    $fmt = new IntlDateFormatter($CurrentLocale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $TIME_ZONE, null, DateFormat($format));
    return $fmt->parse($dt) !== false; // Parse by $format
}

// Check integer
function CheckInteger(mixed $value): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    return IsNumeric($value) && ParseInteger($value) !== false;
}

// Check number
function CheckNumber(mixed $value): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    return IsNumeric($value) && ParseNumber(strval($value)) !== false;
}

/**
 * Check range (number)
 *
 * @param mixed $value Value
 * @param mixed $min Min value
 * @param mixed $max Max value
 * @return bool
 */
function CheckRange(mixed $value, mixed $min, mixed $max): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    if (is_int($min) || is_float($min) || is_int($max) || is_float($max)) { // Number
        $value = ParseNumber($value);
        if ($value === false) { // Not number format
            return false;
        }
    }
    if ($min != null && $value < $min || $max != null && $value > $max) {
        return false;
    }
    return true;
}

// Check US phone number
function CheckPhone(mixed $value): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    return preg_match('/^\(\d{3}\) ?\d{3}( |-)?\d{4}|^\d{3}( |-)?\d{3}( |-)?\d{4}$/', $value);
}

// Check US zip code
function CheckZip(mixed $value): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    return preg_match('/^\d{5}$|^\d{5}-\d{4}$/', $value);
}

// Check credit card
function CheckCreditCard(mixed $value, string $type = ""): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    $creditcard = [
        "visa" => "/^4\d{3}[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}$/",
        "mastercard" => "/^5[1-5]\d{2}[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}$/",
        "discover" => "/^6011[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}$/",
        "amex" => "/^3[4,7]\d{13}$/",
        "diners" => "/^3[0,6,8]\d{12}$/",
        "bankcard" => "/^5610[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}$/",
        "jcb" => "/^[3088|3096|3112|3158|3337|3528]\d{12}$/",
        "enroute" => "/^[2014|2149]\d{11}$/",
        "switch" => "/^[4903|4911|4936|5641|6333|6759|6334|6767]\d{12}$/"
    ];
    if (empty($type)) {
        $match = false;
        foreach ($creditcard as $type => $pattern) {
            if (@preg_match($pattern, $value) == 1) {
                $match = true;
                break;
            }
        }
        return ($match) ? CheckSum($value) : false;
    } else {
        if (!preg_match($creditcard[strtolower(trim($type))], $value)) {
            return false;
        }
        return CheckSum($value);
    }
}

// Check sum
function CheckSum(string $value): bool
{
    $value = str_replace(['-', ' '], ['', ''], $value);
    $checksum = 0;
    for ($i = (2 - (strlen($value) % 2)); $i <= strlen($value); $i += 2) {
        $checksum += (int)($value[$i - 1]);
    }
    for ($i = (strlen($value) % 2) + 1; $i < strlen($value); $i += 2) {
        $digit = (int)($value[$i - 1]) * 2;
        $checksum += ($digit < 10) ? $digit : ($digit - 9);
    }
    return ($checksum % 10 == 0);
}

// Check US social security number
function CheckSsn(mixed $value): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    return preg_match('/^(?!000)([0-6]\d{2}|7([0-6]\d|7[012]))([ -]?)(?!00)\d\d\3(?!0000)\d{4}$/', $value);
}

// Check emails
function CheckEmails(mixed $value, int $count): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    $list = str_replace(",", ";", $value);
    $emails = explode(";", $list);
    $cnt = count($emails);
    if ($cnt > $count && $count > 0) {
        return false;
    }
    return array_all($emails, fn($email) => CheckEmail($email));
}

// Check email
function CheckEmail(mixed $value): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    return preg_match('/^[\w.%+-]+@[\w.-]+\.[A-Z]{2,18}$/i', trim($value));
}

// Check GUID
function CheckGuid(mixed $value): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    return preg_match('/^(\{\w{8}-\w{4}-\w{4}-\w{4}-\w{12}\}|\w{8}-\w{4}-\w{4}-\w{4}-\w{12})$/', $value);
}

// Check file extension
function CheckFileType(mixed $value, string $exts = ""): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    $extension = substr(strtolower(strrchr($value, ".")), 1);
    $exts = $exts ?: Config("UPLOAD_ALLOWED_FILE_EXT");
    $allowExt = explode(",", strtolower($exts));
    return (in_array($extension, $allowExt) || trim($exts) == "");
}

/**
 * Symfony validator
 */
function Validator(): ValidatorInterface
{
    return Container(ValidatorInterface::class);
}

/**
 * Validate with Symfony validator
 *
 * @param mixed $value Value
 * @param Constraint|array|null $constraints Constraints
 * @param string|GroupSequence|array|null $groups Groups
 * @return ConstraintViolationListInterface
 */
function Validate(mixed $value, Constraint|array|null $constraints = null, string|GroupSequence|array|null $groups = null): ConstraintViolationListInterface
{
    return Validator()->validate($value, $constraints, $groups);
}

/**
 * Check if empty string value // PHP
 *
 * @param mixed $value Value
 * @param bool $allowWhiteSpace Allow white space
 * @return bool
 */
function IsEmpty(mixed $value, bool $allowWhiteSpace = false): bool
{
    if ($value === null || is_string($value) && strlen($value) == 0) { // Is null or empty string
        return true;
    }
    if (!$allowWhiteSpace && is_string($value)) { // Remove white spaces
        if (preg_match('/&(?:[a-z\d]+|#\d+|#x[a-f\d]+)/i', $value)) { // Contains HTML entities
            $value = html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, PROJECT_ENCODING);
        }
        return s($value)->trim()->isEmpty();
    }
    return false;
}

#[Deprecated("EmptyValue() is deprecated and will be removed, use IsEmpty() instead", "2025")]
function EmptyValue(mixed $value): bool
{
    return IsEmpty($value);
}

#[Deprecated("EmptyString() is deprecated and will be removed, use IsEmpty() instead", "2025")]
function EmptyString(mixed $value): bool
{
    return IsEmpty($value);
}

/**
 * Partially hide a value
 *
 * e.g.
 * name@domain.com => na**@dom***.com
 * myname => **name
 *
 * @param ?string $value Value
 * @return string
 */
function PartialHide(?string $value): string
{
    // Handle empty value
    if (IsEmpty($value)) {
        return "";
    }

    // Handle email (split an email by "@")
    list($name, $domain) = explode("@", $value) + ["", ""];
    list($domain, $tld) = explode(".", $domain) + ["", ""];

    // Get half the length of the name
    if ($domain) { // Hide the last part
        $len = ceil(strlen($name) / 2);
        $name = substr($name, 0, $len) . str_repeat("*", strlen($name) - $len);
    } else { // Hide the first part
        $len = ceil(strlen($name) / 2);
        $name = str_repeat("*", strlen($name) - $len) . substr($name, $len * -1);
    }

    // Get half the length of the domain
    if ($domain) {
        $len = ceil(strlen($domain) / 2);
        $domain = substr($domain, 0, $len) . str_repeat("*", strlen($domain) - $len);
    }

    // Partially hide value by "*"
    return $name . ($domain ? "@" . $domain : "") . ($tld ? "." . $tld : "");
}

// Check masked password
function IsMaskedPassword(?string $value): bool
{
    return preg_match('/^\*+$/', strval($value));
}

// Check by preg
function CheckByRegEx(mixed $value, string $pattern): bool
{
    $value = strval($value);
    if ($value == "") {
        return true;
    }
    return preg_match($pattern, $value);
}

// Check URL
function CheckUrl(?string $value): bool
{
    return CheckByRegEx($value, Config("URL_PATTERN"));
}

// Check special characters for user name
function CheckUsername(?string $value): bool
{
    return preg_match('/[' . preg_quote(Config('INVALID_USERNAME_CHARACTERS'), '/') . ']/', strval($value));
}

// Check special characters for password
function CheckPassword(?string $value): bool
{
    return preg_match('/[' . preg_quote(Config('INVALID_PASSWORD_CHARACTERS'), '/') . ']/', strval($value));
}

/**
 * Convert encoding
 *
 * @param string $from Encoding (from)
 * @param string $to Encoding (to)
 * @param string $str String being converted
 * @return string
 */
function ConvertEncoding(string $from, string $to, string $str): string
{
    return is_string($str) && $from != "" && $to != "" && !SameText($from, $to)
        ? mb_convert_encoding($str, $to, $from)
        : $str;
}

/**
 * Returns the JSON representation of a value
 *
 * @param mixed $val The value being encoded
 * @param string $type optional Specifies data type: "boolean", "string", "date" or "number"
 * @return string (No conversion to UTF-8)
 */
function VarToJson(mixed $val, ?string $type = null): string
{
    if ($val === null) { // null
        return "null";
    }
    $type = is_string($type) ? strtolower($type) : null;
    if ($type == "boolean" || is_bool($val)) { // bool
        return ConvertToBool($val) ? "true" : "false";
    } elseif ($type == "date" && (is_string($val) || is_int($val))) { // date
        return 'new Date("' . $val . '")';
    } elseif ($type == "number" && is_string($val)) { // number
        return strval((float)$val);
    } elseif ($type == "string" || is_string($val)) { // string
        if (ContainsString($val, "\0")) { // Contains null byte
            $val = "binary";
        }
        return '"' . JsEncode($val) . '"';
    }
    return strval($val); // int/float
}

/**
 * Convert array to JSON
 * If asscociative array, elements with integer key will not be outputted.
 *
 * @param array $ar The array being encoded
 * @return string (No conversion to UTF-8)
 */
function ArrayToJson(array $ar): string
{
    $res = [];
    if (!array_is_list($ar)) { // Object
        foreach ($ar as $key => $val) {
            if (!is_int($key)) { // If object, skip element with integer key
                $res[] = VarToJson($key, "string") . ":" . json_encode($val);
            }
        }
        return IsDebug() ? "{\n" . implode(",\n", $res) . "\n}" : "{" . implode(",", $res) . "}";
    } else { // Array
        foreach ($ar as $val) {
            $res[] = json_encode($val);
        }
        return IsDebug() ? "[\n" . implode(",\n", $res) . "\n]" : "[" . implode(",", $res) . "]";
    }
}

/**
 * Add script tag (async) by script
 *
 * @param string $src Path of script
 * @return void
 */
function AddClientScript(string $src, string $id = "", ?array $options = null): void
{
    LoadJs($src, $id, $options);
}

/**
 * Add <link> tag by script
 *
 * @param string $src Path of stylesheet
 * @return void
 */
function AddStylesheet(string $src, string $id = ""): void
{
    LoadJs("css!" . $src, $id);
}

/**
 * Load JavaScript or Stylesheet by loadjs
 *
 * @param string $src Path of script/stylesheet
 * @param string $id (optional) ID of the script
 * @param array $options (optional) Options (async and numRetries), see https://github.com/muicss/loadjs
 * @return void
 */
function LoadJs(string $src, string $id = "", ?array $options = null): void
{
    $prefix = "";
    if (preg_match('/^css!/i', $src, $matches)) {
        $src = preg_replace('/^css!/i', '', $src);
        $prefix = "css!";
    }
    $basePath = BasePath(true);
    if (!IsRemote($src) && $basePath != "" && !StartsString($basePath, $src)) { // PHP
        $src = $basePath . $src;
    }
    echo '<script' . Nonce() . '>loadjs("' . $prefix . $src . '"' . ($id ? ', "' . $id . '"' : '') . (is_array($options) ? ', ' . json_encode($options) : '') . ');</script>';
}

/**
 * Check boolean attribute
 *
 * @param string $attr Attribute name
 * @return bool
 */
function IsBooleanAttribute(string $attr): bool
{
    return in_array(strtolower($attr), Config("BOOLEAN_HTML_ATTRIBUTES"));
}

/**
 * Get HTML <a> tag
 *
 * @param string $phraseId Phrase ID for inner HTML
 * @param string|array|Attributes $attrs The href attribute, or array of attributes, or Attributes object
 * @return string HTML string
 */
function GetLinkHtml(string|array|Attributes $attrs, string $phraseId): string
{
    if (is_string($attrs)) {
        $attrs = new Attributes(["href" => $attrs]);
    } elseif (is_array($attrs)) {
        $attrs = new Attributes($attrs);
    } elseif (!$attrs instanceof Attributes) {
        $attrs = new Attributes();
    }
    $phrase = Language()->phrase($phraseId);
    $title = $attrs["title"];
    if (!$title) {
        $title = HtmlTitle($phrase);
        $attrs["title"] = $title;
    }
    if ($title && !$attrs["data-caption"]) {
        $attrs["data-caption"] = $title;
    }
    return Element::create("a", attributes: $attrs->toArray())->setInnerHtml($phrase)->toDocument()->format()->html();
}

/**
 * Encode HTML
 *
 * @param mixed $str String to encode
 * @return mixed Encoded string
 */
function HtmlEncode(mixed $str): mixed
{
    if (IsEmpty($str) || !is_string($str)) {
        return $str;
    }
    return @htmlspecialchars($str, ENT_COMPAT | ENT_HTML5, PROJECT_ENCODING);
}

/**
 * Decode HTML
 *
 * @param mixed $str String to decode
 * @return mixed Decoded string
 */
function HtmlDecode($str): mixed
{
    if (IsEmpty($str) || !is_string($str)) {
        return $str;
    }
    return htmlspecialchars_decode($str, ENT_COMPAT | ENT_HTML5);
}

// Get title
function HtmlTitle(string $name): string
{
    if (
        preg_match('/<span class=([\'"])visually-hidden\\1>([\s\S]*?)<\/span>/i', $name, $matches) || // Match span.visually-hidden
        preg_match('/\s+title\s*=\s*([\'"])([\s\S]*?)\\1/i', $name, $matches) || // Match title='title'
        preg_match('/\s+data-caption\s*=\s*([\'"])([\s\S]*?)\\1/i', $name, $matches) // Match data-caption='caption'
    ) {
        return $matches[2];
    }
    return $name;
}

/**
 * Get HTML for an option
 *
 * @param mixed $val Value of the option
 * @return string HTML
 */
function OptionHtml(mixed $val): string
{
    return preg_replace('/\{value\}/', $val, Config("OPTION_HTML_TEMPLATE"));
}

/**
 * Get HTML for all option
 *
 * @param array $values Array of values
 * @return string HTML
 */
function OptionsHtml(array $values): string
{
    return implode(array_map(fn($val) => OptionHtml($val), $values));
}

// Encode value for double-quoted Javascript string
function JsEncode(?string $val): string
{
    return str_replace(["\\", "\"", "\t", "\r", "\n"], ["\\\\", "\\\"", "\\t", "\\r", "\\n"], strval($val));
}

// Encode value to single-quoted Javascript string for HTML attributes
function JsEncodeAttribute(?string $val): string
{
    return  str_replace(["&", "\"", "'", "<", ">"], ["&amp;", "&quot;", "&apos;", "&lt;", "&gt;"], strval($val));
}

// Convert array to JSON for single quoted HTML attributes
function ArrayToJsonAttribute(array $ar): string
{
    return JsEncodeAttribute(ArrayToJson($ar));
}

/**
 * Get current page URL
 *
 * @param bool $withOptionalParameters Whether with optional parameters
 * @return string URL
 */
function CurrentPageUrl(bool $withOptionalParameters = true): string
{
    $route = GetRoute();
    if (!$route) {
        return "";
    }
    $url = PathFor($route->getName(), $route->getArguments(), $withOptionalParameters);
    $basePath = BasePath(false);
    if ($basePath && !StartsString(IncludeTrailingDelimiter($basePath, false), $url)) {
        $url = $basePath . $url;
    }
    return $url;
}

// Get current page name (does not contain path)
function CurrentPageName(): string
{
    return GetPageName(CurrentPageUrl());
}

// Get page name
function GetPageName(string $url): string
{
    $pageName = "";
    if ($url != "") {
        $pageName = $url;
        $p = strpos($pageName, "?");
        if ($p !== false) {
            $pageName = substr($pageName, 0, $p); // Remove QueryString
        }
        $host = ServerVar("HTTP_HOST");
        $p = strpos($pageName, $host);
        if ($p !== false) {
            $pageName = substr($pageName, $p + strlen($host)); // Remove host
        }
        $basePath = BasePath();
        if ($basePath != "" && StartsString($basePath, $pageName)) { // Remove base path
            $pageName = substr($pageName, strlen($basePath));
        }
        if (StartsString("/", $pageName)) { // Remove first "/"
            $pageName = substr($pageName, 1);
        }
        if (ContainsString($pageName, "/")) {
            $pageName = explode("/", $pageName)[0];
        }
    }
    return $pageName;
}

/**
 * Get dashboard report page URL (without arguments)
 * Note: Since there are more than one pages in dashboard report, the value of $Page changes in the View of dashboard report.
 *
 * @return string URL
 */
function CurrentDashboardPageUrl(): string
{
    global $DashboardReport, $Page;
    return $DashboardReport && $Page ? GetUrl($Page->CurrentPageName) : CurrentPageUrl(false);
}

// Get current user levels as array of user level IDs
function CurrentUserLevels(): array
{
    return Security()->UserLevelIDs;
}

// Check if menu item is allowed for current user level
function AllowListMenu(string $tableName): bool
{
    if (IsLoggedIn()) { // Get user level ID list as array
        $userlevels = CurrentUserLevels(); // Get user level ID list as array
    } else { // Get anonymous user level ID
        $userlevels = [AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID];
    }
    if (in_array(AdvancedSecurity::ADMIN_USER_LEVEL_ID, $userlevels)) {
        return true;
    } else {
        $priv = 0;
        $rows = Session(SESSION_USER_LEVEL_PRIVS);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (SameString($row[0], $tableName) && in_array($row[1], $userlevels)) {
                    $p = $row[2] ?? 0;
                    $p = (int)$p;
                    $priv = $priv | $p;
                }
            }
        }
        return ($priv & Allow::LIST->value);
    }
}

// Get script name
function ScriptName(): string
{
    $route = GetRoute();
    return $route
        ? UrlFor($route->getName(), $route->getArguments())
        : explode("?", ServerVar("REQUEST_URI"))[0];
}

// Get server variable by name
function ServerVar(string $name): string
{
    return $_SERVER[$name] ?? $_ENV[$name] ?? "";
}

// Get CSS file
function CssFile(string $f, ?bool $rtl = null, ?bool $min = null): string
{
    $rtl ??= IsRTL();
    $min ??= Config("USE_COMPRESSED_STYLESHEET");
    return $rtl
        ? ($min ? preg_replace('/(.css)$/i', ".rtl.min.css", $f) : preg_replace('/(.css)$/i', ".rtl.css", $f))
        : ($min ? preg_replace('/(.css)$/i', ".min.css", $f) : $f);
}

// Check if HTTPS
function IsHttps(): bool
{
    return ServerVar("HTTPS") != "" && ServerVar("HTTPS") != "off"
        || ServerVar("SERVER_PORT") == 443
        || ServerVar("HTTP_X_FORWARDED_PROTO") != "" && ServerVar("HTTP_X_FORWARDED_PROTO") == "https";
}

// Get domain URL
function DomainUrl(): string
{
    $ssl = IsHttps();
    $port = strval(ServerVar("SERVER_PORT"));
    if (ServerVar("HTTP_X_FORWARDED_PROTO") != "" && strval(ServerVar("HTTP_X_FORWARDED_PORT")) != "") {
        $port = strval(ServerVar("HTTP_X_FORWARDED_PORT"));
    }
    $port = in_array($port, ["80", "443"]) ? "" : (":" . $port);
    return ($ssl ? "https" : "http") . "://" . ServerVar("SERVER_NAME") . $port;
}

// Get current URL
function CurrentUrl(): string
{
    $s = ScriptName();
    $q = ServerVar("QUERY_STRING");
    if ($q != "") {
        $s .= "?" . $q;
    }
    return $s;
}

// Get full UR
function FullUrl(string $url = "", string $type = ""): string
{
    if (IsRemote($url)) { // Remote
        return $url;
    }
    if (str_starts_with($url, "/")) { // Absolute
        return PathJoin(DomainUrl(), $url);
    }
    // Relative to base path
    $fullUrl = PathJoin(DomainUrl(), BasePath());
    if ($url != "") {
        $fullUrl = PathJoin($fullUrl, $url); // Combine input URL
    }
    if ($type != "") {
        $protocol = Config("FULL_URL_PROTOCOLS." . $type);
        if ($protocol) {
            $fullUrl = preg_replace('/^\w+(?=:\/\/)/i', $protocol, $fullUrl);
        }
    }
    return $fullUrl;
}

// Get URL with base path
function GetUrl(string $url): string
{
    return !IsEmpty($url) && !str_starts_with($url, "/") && !str_contains($url, "://")  && !str_contains($url, "\\") && !str_contains($url, "javascript:")
        ? PathJoin(BasePath(true), $url)
        : $url;
}

// Check if mobile device
function IsMobile(): bool
{
    return Container("mobile.detect")->isMobile();
}

/**
 * Execute query
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return Result The executed Result
 */
function ExecuteQuery(string $sql, Connection|string|null $c = null): Result
{
    if (is_string($c)) {
        $c = Conn($c);
    }
    $conn = $c ?? $GLOBALS["Conn"] ?? Conn();
    return $conn->executeQuery($sql);
}

/**
 * Execute UPDATE, INSERT, or DELETE statements
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return int Rows affected
 */
function ExecuteStatement(string $sql, Connection|string|null $c = null): int
{
    if (is_string($c)) {
        $c = Conn($c);
    }
    $conn = $c ?? $GLOBALS["Conn"] ?? Conn();
    return $conn->executeStatement($sql);
}

/**
 * Execute SELECT statement
 *
 * @param string $sql SQL to execute
 * @param mixed $fn Callback function to be called for each row
 * @param Connection|string $c optional Connection object or database ID
 * @return mixed
 */
function Execute(string $sql, mixed $fn = null, Connection|string|null $c = null): mixed
{
    if ($c === null && (is_string($fn) || $fn instanceof Connection)) {
        $c = $fn;
    }
    $sql = trim($sql);
    if (preg_match('/^(UPDATE|INSERT|DELETE)\s/i', $sql)) {
        return ExecuteStatement($sql, $c);
    }
    $result = ExecuteQuery($sql, $c);
    if (is_callable($fn)) {
        $rows = ExecuteRows($sql, $c);
        array_walk($rows, $fn);
    }
    return $result;
}

/**
 * Execute SELECT statment to get record count
 *
 * @param string|QueryBuilder $sql SQL or QueryBuilder
 * @param Connection $conn Connection
 * @return int Record count
 */
function ExecuteRecordCount(string|QueryBuilder $sql, Connection|string|null $conn): int
{
    $cnt = -1;
    if ($sql instanceof QueryBuilder) { // Query builder
        $queryBuilder = clone $sql;
        $sqlwrk = $queryBuilder->resetOrderBy()->getSQL();
    } else {
        $conn ??= $GLOBALS["Conn"] ?? Conn();
        $sqlwrk = $sql;
    }
    if ($result = $conn->executeQuery($sqlwrk)) {
        $cnt = $result->rowCount();
        if ($cnt <= 0) { // Unable to get record count, count directly
            $cnt = 0;
            while ($result->fetchAssociative()) {
                $cnt++;
            }
        }
        return $cnt;
    }
    return $cnt;
}

/**
 * Get QueryBuilder
 *
 * @param string $dbid Database ID
 * @return QueryBuilder
 */
function QueryBuilder(string $dbid = "DB"): QueryBuilder
{
    return Conn($dbid)->createQueryBuilder();
}

/**
 * Get QueryBuilder for UPDATE
 *
 * @param string $table Table name or table variable name
 * @return QueryBuilder
 */
function Update(string $table): QueryBuilder
{
    return Container($table)->getQueryBuilder("update");
}

/**
 * Get QueryBuilder for INSERT
 *
 * @param string $table Table name or table variable name
 * @return QueryBuilder
 */
function Insert(string $table): QueryBuilder
{
    return Container($table)->getQueryBuilder("insert");
}

/**
 * Get QueryBuilder for DELETE
 *
 * @param string $table Table name or table variable name
 * @return QueryBuilder
 */
function Delete(string $table): QueryBuilder
{
    return Container($table)->getQueryBuilder("delete");
}

/**
 * Get parameter type (for backward compatibility)
 *
 * @param DbField $fld Field Object
 * @return string|int
 */
function GetParameterType(DbField $fld): string|int
{
    return $fld->getParameterType();
}

/**
 * Get field parameter type
 *
 * @param string $table Table name
 * @param string $field Field name
 * @return string|int
 */
function GetFieldParameterType(string $table, string $field): string|int
{
    return Container($table)?->Fields[$field]?->getParameterType();
}

/**
 * Executes query and returns the first column of the first row
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return mixed
 */
function ExecuteScalar(string $sql, Connection|string|null $c = null): mixed
{
    $result = ExecuteQuery($sql, $c);
    return $result->fetchOne();
}

/**
 * Executes the query, and returns the first row
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @param int $mode PDO fetch mode (PDO::FETCH_ASSOC, PDO::FETCH_NUM, or PDO::FETCH_COLUMN)
 * @return mixed
 */
function ExecuteRow(string $sql, Connection|string|null $c = null, int $mode = \PDO::FETCH_ASSOC): mixed
{
    try {
        return match ($mode) {
            \PDO::FETCH_ASSOC => ExecuteRowAssociative($sql, $c),
            \PDO::FETCH_NUM => ExecuteRowNumeric($sql, $c),
            \PDO::FETCH_COLUMN => ExecuteScalar($sql, $c)
        };
    } catch (UnhandledMatchError $e) {
        throw new UnhandledMatchError("Only PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_COLUMN modes are supported.");
    }
}

/**
 * Executes query and returns the first row (Associative)
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return array<string,mixed>|false
 */
function ExecuteRowAssociative(string $sql, Connection|string|null $c = null): array|false
{
    $result = ExecuteQuery($sql, $c);
    return $result->fetchAssociative();
}

/**
 * Executes query and returns the first row (Numeric)
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return list<mixed>|false
 */
function ExecuteRowNumeric(string $sql, Connection|string|null $c = null): array|false
{
    $result = ExecuteQuery($sql, $c);
    return $result->fetchNumeric();
}

/**
 * Executes query and returns all rows
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @param int $mode Fetch mode
 * @return list<mixed>|false
 */
function ExecuteRows(string $sql, Connection|string|null $c = null, int $mode = \PDO::FETCH_ASSOC): array|false
{
    try {
        return match ($mode) {
            \PDO::FETCH_ASSOC => ExecuteRowsAssociative($sql, $c),
            \PDO::FETCH_NUM => ExecuteRowsNumeric($sql, $c),
            \PDO::FETCH_COLUMN => ExecuteFirstColumn($sql, $c)
        };
    } catch (UnhandledMatchError $e) {
        throw new UnhandledMatchError("Only PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_COLUMN modes are supported.");
    }
}

/**
 * Executes query and returns all rows (Associative)
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return array<string,mixed>|false
 */
function ExecuteRowsAssociative(string $sql, Connection|string|null $c = null): array|false
{
    $result = ExecuteQuery($sql, $c);
    return $result->fetchAllAssociative();
}

/**
 * Executes query and returns all rows (Numeric)
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return list<mixed>|false
 */
function ExecuteRowsNumeric(string $sql, Connection|string|null $c = null): array|false
{
    $result = ExecuteQuery($sql, $c);
    return $result->fetchAllNumeric();
}

/**
 * Executes query and retrieve the value of the first column of all rows
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return list<mixed>|false
 */
function ExecuteFirstColumn(string $sql, Connection|string|null $c = null): array|false
{
    $result = ExecuteQuery($sql, $c);
    return $result->fetchFirstColumn();
}

/**
 * Execute query and fetch the data as an associative array where the key represents the first column and
 * the value is an associative array of the rest of the columns and their values
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return array<mixed,array<string,mixed>>|false
 */
function ExecuteRowsAssociativeIndexed(string $sql, Connection|string|null $c = null): array|false
{
    $result = ExecuteQuery($sql, $c);
    return $result->fetchAllAssociativeIndexed();
}

/**
 * Execute query and fetch the first two columns into an associative array as keys and values respectively
 *
 * @param string $sql SQL to execute
 * @param Connection|string $c optional Connection object or database ID
 * @return array<mixed,array<string,mixed>>|false
 */
function ExecuteRowsKeyValue(string $sql, Connection|string|null $c = null): array|false
{
    $result = ExecuteQuery($sql, $c);
    return $result->fetchAllKeyValue();
}

/**
 * Executes query and returns all rows as JSON
 *
 * @param string $sql SQL to execute
 * @param array|bool|null $options {
 *  @var bool "array" Output as array
 *  @var bool "firstonly" Output first row only
 *  @var bool "datatypes" Array of data types, key of array must be same as row(s)
 * }
 * @param Connection|string $c Connection object or DB ID
 * @return string
 */
function ExecuteJson(string $sql, array|bool|null $options = null, Connection|string|null $c = null): string
{
    $ar = is_array($options) ? $options : [];
    if (is_bool($options)) { // First only, backward compatibility
        $ar["firstonly"] = $options;
    }
    if ($c === null && is_object($options) && method_exists($options, "execute")) { // ExecuteJson($sql, $c)
        $c = $options;
    }
    $res = "false";
    $header = $ar["header"] ?? true; // Set header for JSON
    $firstonly = $ar["firstonly"] ?? false;
    $datatypes = is_array($ar["datatypes"] ?? false) ? $ar["datatypes"] : [];
    $array = $ar["array"] ?? false;
    $mode = $array ? \PDO::FETCH_NUM : \PDO::FETCH_ASSOC;
    $rows = $firstonly ? [ExecuteRow($sql, $c, $mode)] : ExecuteRows($sql, $c, $mode);
    if (is_array($rows)) {
        $arOut = [];
        foreach ($rows as $row) {
            $arwrk = [];
            foreach ($row as $k => $v) {
                if ($array && is_string($k) || !$array && is_int($k)) {
                    continue;
                }
                $key = $array ? '' : '"' . JsEncode($k) . '":';
                $datatype = $datatypes[$k] ?? null;
                $val = VarToJson($v, $datatype);
                $arwrk[] = $key . $val;
            }
            if ($array) { // Array
                $arOut[] = "[" . implode(",", $arwrk) . "]";
            } else { // Object
                $arOut[] = "{" . implode(",", $arwrk) . "}";
            }
        }
        $res = $firstonly ? $arOut[0] : "[" . implode(",", $arOut) . "]";
    }
    return $res;
}

/**
 * Get query result in HTML table
 *
 * @param string $sql SQL to execute
 * @param array $options optional {
 *  @var bool|array "fieldcaption"
 *    true Use caption and use language object
 *    false Use field names directly
 *    array An associative array for looking up the field captions by field name
 *  @var bool "horizontal" Specifies if the table is horizontal, default: false
 *  @var string|array "tablename" Table name(s) for the language object
 *  @var string "tableclass" CSS class names of the table, default: "table table-bordered table-sm ew-db-table"
 *  @var Language "language" Language object, default: the global Language object
 * }
 * @param Connection|string $c optional Connection object or DB ID
 * @return string HTML string
 */
function ExecuteHtml(string $sql, ?array $options = null, Connection|string|null $c = null): string
{
    // Internal function to get field caption
    $getFieldCaption = function ($key) use ($options) {
        $caption = "";
        if (!is_array($options)) {
            return $key;
        }
        $tableName = $options["tablename"] ?? "";
        $language = $options["language"] ?? Language();
        $useCaption = (array_key_exists("fieldcaption", $options) && $options["fieldcaption"]);
        if ($useCaption) {
            if (is_array($options["fieldcaption"])) {
                $caption = $options["fieldcaption"][$key] ?? "";
            } elseif (isset($language)) {
                if (is_array($tableName)) {
                    foreach ($tableName as $tbl) {
                        $caption = $language->fieldPhrase($tbl, $key, "FldCaption");
                        if ($caption != "") {
                            break;
                        }
                    }
                } elseif ($tableName != "") {
                    $caption = $language->fieldPhrase($tableName, $key, "FldCaption");
                }
            }
        }
        return $caption ?: $key;
    };
    $options = is_array($options) ? $options : [];
    $horizontal = array_key_exists("horizontal", $options) && $options["horizontal"];
    $result = ExecuteQuery($sql, $c);
    if ($result?->columnCount() < 1) {
        return "";
    }
    $html = "";
    $class = $options["tableclass"] ?? "table table-sm ew-db-table"; // Table CSS class name
    $rowCount = $result->rowCount();
    if ($rowCount <= 0) { // rowCount() not supported, Loop all records to get rowCount()
        $rowCount = ExecuteRecordCount($sql, $c);
    }
    if ($rowCount > 1 || $horizontal) { // Horizontal table
        $rowcnt = 0;
        while ($row = $result->fetchAssociative()) {
            if ($rowcnt == 0) {
                $html = "<table class=\"" . $class . "\">";
                $html .= "<thead><tr>";
                foreach (array_keys($row) as $key) {
                    // Begin of modification by Masino Sinaga, September 17, 2023
                    if (!is_numeric($key))
						$html .= "<th>" . $getFieldCaption($key) . "</th>";
					// End of modification by Masino Sinaga, September 17, 2023
                }
                $html .= "</tr></thead>";
                $html .= "<tbody>";
            }
            $rowcnt++;
            $html .= "<tr>";
            foreach ($row as $key => $value) {
                // Begin of modification by Masino Sinaga, September 17, 2023
                if (!is_numeric($key)) {
					if (is_numeric($value)) 
						$html .= "<td style='text-align: right;'>" . FormatNumber($value). "</td>";
					else
						$html .= "<td>" . $value . "</td>";
				}
				// End of modification by Masino Sinaga, September 17, 2023
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
    } else { // Single row, vertical table
        $html = "<table class=\"" . $class . "\"><tbody>";
        if ($row = $result->fetchAssociative()) {
            foreach ($row as $key => $value) {
                // Begin of modification by Masino Sinaga, September 17, 2023
				if (!is_numeric($key)) {
					if (!is_numeric($value)) {
						$html .= "<tr>";
						$html .= "<td>" . $getFieldCaption($key) . "</td>";
						$html .= "<td>" . $value . "</td></tr>";
					} else {
						$html .= "<tr>";
						$html .= "<td>" . $getFieldCaption($key) . "</td>";
						$html .= "<td style='text-align: right;'>" . FormatNumber($value) . "</td></tr>";
					}
				}
				// End of modification by Masino Sinaga, September 17, 2023
            }
        }
        $html .= "</tbody></table>";
    }
    return $html;
}

/**
 * Get class name(s) as array
 *
 * @param string $attr Class name(s)
 * @return array Class name(s)
 */
function ClassList(?string $attr): array
{
    return $attr
        ? array_unique(array_filter(explode(" ", $attr))) // Remove empty and duplicate values
        : [];
}

/**
 * Contains CSS class name
 *
 * @param string $attr Class name(s)
 * @param string $className Class name to search
 * @return bool
 */
function ContainsClass(?string $attr, string $className): bool
{
    return array_search($className, ClassList($attr)) !== false;
}

/**
 * Prepend CSS class name(s)
 *
 * @param string &$attr Class name(s)
 * @param string $className Class name(s) to prepend
 * @return string Class name(s)
 */
function PrependClass(?string &$attr, string $className): string
{
    if ($className) {
        $attr = $className . " " . $attr;
    }
    $attr = implode(" ", ClassList($attr));
    return $attr;
}

/**
 * Append CSS class name(s)
 *
 * @param string &$attr Class name(s)
 * @param string $className Class name(s) to append
 * @return string Class name(s)
 */
function AppendClass(?string &$attr, string $className): string
{
    if ($className) {
        $attr .= " " . $className;
    }
    $attr = implode(" ", ClassList($attr));
    return $attr;
}

/**
 * Remove CSS class name(s)
 *
 * @param string &$attr Class name(s)
 * @param string|callable $classNames Class name(s) to remove
 * @return string Class name(s)
 */
function RemoveClass(?string &$attr, string $classNames): string
{
    $ar = ClassList($attr);
    if (is_string($classNames)) { // String
        $ar = array_diff($ar, ClassList($classNames));
    } elseif (is_callable($classNames)) { // Callable to filter the class names
        $ar = array_filter($ar, $classNames);
    }
    $attr = implode(" ", $ar);
    return $attr;
}

/**
 * Check CSS class name and convert to lowercase with dashes between words
 *
 * @param string $name Class name
 * @return string Valid class name
 */
function CheckClassName(string $name): string
{
    $prefix = Config("CLASS_PREFIX");
    if (preg_match('/^(\d+)(-*)([\-\w]+)/', $name, $m)) { // Cannot start with a digit
        return $prefix . $m[1] . $m[2] . ParamCase($m[3]);
    } elseif (preg_match('/^(-{2,}|-\d+)(-*)([\-\w]+)/', $name, $m)) { // Cannot start with two hyphens or a hyphen followed by a digit
        return $prefix . $m[1] . $m[2] . ParamCase($m[3]);
    } elseif (preg_match('/^(_+)?(-*)([\-\w]+)/', $name, $m)) { // Keep leading underscores
        return $m[1] . $m[2] . ParamCase($m[3]);
    }
    return ParamCase($name);
}

/**
 * Get locale information
 */
function LocaleConvert(): array
{
    global $DATE_SEPARATOR, $TIME_SEPARATOR, $CURRENCY_CODE, $TIME_ZONE, $CurrentLocale;
    $langid = CurrentLanguageID();
    $localefile = Config("LOCALE_FOLDER") . $langid . ".json";
    if (file_exists($localefile)) { // Load from locale file
        $locale = array_merge(["id" => $langid], json_decode(file_get_contents($localefile), true));
    } else { // Load from PHP intl extension
        $locales = array_map("strtolower", \ResourceBundle::getLocales(""));
        if (!in_array(strtolower(str_replace("-", "_", $langid)), $locales)) { // Locale not supported by server
            LogError("Locale " . $langid . " not supported by server.");
            $langid = "en-US"; // Fallback to "en-US"
        }
        $locale = [
            "id" => $langid,
            "desc" => \Locale::getDisplayName($langid),
        ];
    }
    $getSeparator = fn($str) => preg_match('/[^\w]+/', $str, $m) ? $m[0] : null;
    $CurrentLocale = $locale["id"];
    $fmt = new NumberFormatter($CurrentLocale, NumberFormatter::DECIMAL);
    $currfmt = new NumberFormatter($CurrentLocale, NumberFormatter::CURRENCY);
    $pctfmt = new NumberFormatter($CurrentLocale, NumberFormatter::PERCENT);
    $currcode = $locale["currency_code"] ?? $currfmt->getTextAttribute(NumberFormatter::CURRENCY_CODE);
    $locale["currency_code"] = $currcode != "XXX" ? $currcode : $CURRENCY_CODE;
    $locale["number"] ??=  $fmt->getPattern();
    $locale["currency"] ??=  $currfmt->getPattern();
    $locale["percent"] ??=  $pctfmt->getPattern();
    $locale["percent_symbol"] ??=  $pctfmt->getSymbol(NumberFormatter::PERCENT_SYMBOL);
    $locale["currency_symbol"] ??=  $currfmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    $locale["numbering_system"] ??=  "";
    $locale["date"] ??=  (new IntlDateFormatter($CurrentLocale, IntlDateFormatter::SHORT, IntlDateFormatter::NONE))->getPattern();
    $locale["time"] ??=  (new IntlDateFormatter($CurrentLocale, IntlDateFormatter::NONE, IntlDateFormatter::SHORT))->getPattern();
    $locale["decimal_separator"] ??=  $fmt->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
    $locale["grouping_separator"] ??=  $fmt->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
    $locale["date_separator"] ??=  $getSeparator($locale["date"]) ?? $DATE_SEPARATOR;
    $locale["time_separator"] ??=  $getSeparator($locale["time"]) ?? $TIME_SEPARATOR;
    $locale["time_zone"] = !empty($locale["time_zone"]) ? $locale["time_zone"] : $TIME_ZONE;
    return $locale;
}

/**
 * Get ICU date/time format pattern
 *
 * @param int|string $dateFormat Date format
 * @return string ICU date format
 */
function DateFormat(int|string $dateFormat): string
{
    global $DATE_SEPARATOR, $TIME_SEPARATOR, $DATE_FORMAT, $TIME_FORMAT;
    if (is_numeric($dateFormat)) { // Predefined format
        $id = intval($dateFormat);
        if ($id == 1) {
            return $DATE_FORMAT . " " . $TIME_FORMAT; // DateTime
        } elseif ($id == 0 || $id == 2) {
            return $DATE_FORMAT; // Date
        } elseif ($id == 3) {
            return $TIME_FORMAT; // Time
        } else { // Predefined formats
            $formats = Config("DATE_FORMATS");
            if (array_key_exists($id, $formats)) {
                return str_replace(["/", ":"], [$DATE_SEPARATOR, $TIME_SEPARATOR], $formats[$id]);
            }
        }
    } elseif (is_string($dateFormat) && !IsEmpty($dateFormat)) { // User defined format
        return $dateFormat;
    }
    return ""; // Unknown
}

/**
 * Get database date/time format pattern
 *
 * @param int|string $dateFormat Date format
 * @param string $dbtype Database type
 * @return string Database date format
 */
function DbDateFormat(int|string $dateFormat, string $dbtype): string
{
    global $DATE_SEPARATOR, $TIME_SEPARATOR;
    $dateFormat = DateFormat($dateFormat);
    $tokens = array_reverse(preg_split('/[_\W]/', $dateFormat, -1, PREG_SPLIT_OFFSET_CAPTURE));
    $symbols = Config("DB_DATE_FORMATS." . $dbtype);
    foreach ($tokens as $token) {
        $t = $token[0];
        $dateFormat = substr_replace($dateFormat, $symbols[$t] ?? $t, $token[1], strlen($t));
    }
    return str_replace(["/", ":"], [$DATE_SEPARATOR, $TIME_SEPARATOR], $dateFormat);
}

/**
 * Joins two or more path strings into a canonical path (with '/' as separator)
 *
 * @param string $paths Paths
 * @return string
 */
function PathJoin(string ...$paths): string
{
    return Path::join(...$paths);
}

// Get path relative to a base path
function PathCombine(string $basePath, string $relPath, bool $phyPath): string
{
    if (IsRemote($relPath)) { // Allow remote file
        return $relPath;
    }
    $phyPath = !IsRemote($basePath) && $phyPath;
    $delimiter = $phyPath ? PATH_DELIMITER : '/';
    if ($basePath != $delimiter) { // If BasePath = root, do not remove delimiter
        $basePath = RemoveTrailingDelimiter($basePath, $phyPath);
    }
    $relPath = $phyPath ? str_replace(['/', '\\'], PATH_DELIMITER, $relPath) : str_replace('\\', '/', $relPath);
    $relPath = IncludeTrailingDelimiter($relPath, $phyPath);
    if ($basePath == $delimiter && !$phyPath) { // If BasePath = root and not physical path, just return relative path(?)
        return $relPath;
    }
    $p1 = strpos($relPath, $delimiter);
    $path2 = "";
    while ($p1 !== false) {
        $path = substr($relPath, 0, $p1 + 1);
        if ($path == $delimiter || $path == '.' . $delimiter) {
            // Skip
        } elseif ($path == ".." . $delimiter) {
            $p2 = strrpos($basePath, $delimiter);
            if ($p2 === 0) { // BasePath = "/xxx", cannot move up
                $basePath = $delimiter;
            } elseif ($p2 !== false && !EndsString("..", $basePath)) {
                $basePath = substr($basePath, 0, $p2);
            } elseif ($basePath != "" && $basePath != "." && $basePath != "..") {
                $basePath = "";
            } else {
                $path2 .= ".." . $delimiter;
            }
        } else {
            $path2 .= $path;
        }
        $relPath = substr($relPath, $p1 + 1);
        if ($relPath === false) {
            $relPath = "";
        }
        $p1 = strpos($relPath, $delimiter);
    }
    return (($basePath === "" || $basePath === ".") ? "" : IncludeTrailingDelimiter($basePath, $phyPath)) . $path2 . $relPath;
}

// Remove the last delimiter for a path
function RemoveTrailingDelimiter(string $path, bool $phyPath): string
{
    $delimiter = !IsRemote($path) && $phyPath ? PATH_DELIMITER : '/';
    while (substr($path, -1) == $delimiter) {
        $path = substr($path, 0, strlen($path) - 1);
    }
    return $path;
}

// Include the last delimiter for a path
function IncludeTrailingDelimiter(string $path, bool $phyPath): string
{
    $path = RemoveTrailingDelimiter($path, $phyPath);
    $delimiter = !IsRemote($path) && $phyPath ? PATH_DELIMITER : '/';
    return $path . $delimiter;
}

// Get session timeout time (seconds)
function SessionTimeoutTime(): int
{
    if (Config("SESSION_TIMEOUT") > 0) { // User specified timeout time
        $mlt = Config("SESSION_TIMEOUT") * 60;
    } else { // Get max life time from php.ini
        $mlt = (int)ini_get("session.gc_maxlifetime"); // Defaults to 1440s = 24min
        if ($mlt > 0) {
            $mlt -= 30; // Add some safety margin
        }
    }
    if ($mlt <= 0) {
        $mlt = 1440; // PHP default (1440s = 24min)
    }
    return $mlt;
}

// Contains a substring (case-sensitive)
function ContainsString(?string $haystack, string $needle): bool
{
    return str_contains($haystack ?? "", $needle);
}

// Contains a substring (case-insensitive)
function ContainsText(?string $haystack, string $needle): bool
{
    return stripos($haystack ?? "", $needle) !== false;
}

// Starts with a substring (case-sensitive)
function StartsString(string $needle, ?string $haystack): bool
{
    return str_starts_with($haystack ?? "", $needle);
}

// Starts with a substring (case-insensitive)
function StartsText(string $needle, ?string $haystack): bool
{
    return stripos($haystack ?? "", $needle) === 0;
}

// Ends with a substring (case-sensitive)
function EndsString(string $needle, ?string $haystack): bool
{
    return str_ends_with($haystack ?? "", $needle);
}

// Ends with a substring (case-insensitive)
function EndsText(string $needle, ?string $haystack): bool
{
    return strripos($haystack ?? "", $needle) === strlen($haystack ?? "") - strlen($needle);
}

// Same trimmed strings (case-sensitive)
function SameString(?string $str1, ?string $str2): bool
{
    return strcmp(trim($str1 ?? ""), trim($str2 ?? "")) === 0;
}

// Same trimmed strings (case-insensitive)
function SameText(?string $str1, ?string $str2): bool
{
    return strcasecmp(trim($str1 ?? ""), trim($str2 ?? "")) === 0;
}

// Convert to constant case (e.g. FOO_BAR)
function ConstantCase(string $str): string
{
    return (new \Jawira\CaseConverter\Convert($str))->toMacro();
}

// Convert to param case (e.g. foo-bar)
function ParamCase(string $str): string
{
    return (new \Jawira\CaseConverter\Convert($str))->toKebab();
}

// Convert to header case (e.g. Foo-Bar for HTTP/1.1 or foo-bar for HTTP/2.0)
function HeaderCase(string $str): string
{
    $protocol = ServerVar("SERVER_PROTOCOL") ? str_replace("HTTP/", "", ServerVar("SERVER_PROTOCOL")) : "1.1";
    return floatval($protocol) >= 2
        ? strtolower($str)
        : (new \Jawira\CaseConverter\Convert($str))->toTrain();
}

// Convert to Pascal case (e.g. FooBar)
function PascalCase(string $str): string
{
    return (new \Jawira\CaseConverter\Convert($str))->toPascal();
}

// Convert to camle case (e.g. fooBar)
function CamelCase(string $str): string
{
    return (new \Jawira\CaseConverter\Convert($str))->toCamel();
}

// Set client variable
function SetClientVar(string $key, mixed $value): void
{
    global $ClientVariables;
    $key = strval($key);
    if (is_array($value) && is_array($ClientVariables[$key] ?? null)) {
        $ClientVariables[$key] = array_replace_recursive($ClientVariables[$key], $value);
    } else {
        $ClientVariables[$key] = $value;
    }
}

// Get client variable
function GetClientVar(string $key = "", string $subkey = ""): mixed
{
    global $ClientVariables;
    if (!$key) {
        return $ClientVariables;
    }
    $value = $ClientVariables[$key] ?? null;
    if ($subkey) {
        $value = $value[$subkey] ?? null;
    }
    return $value;
}

// Get config client variables
function ConfigClientVars(): array
{
    $values = [];
    $data = Config();
    $names = $data->get("CONFIG_CLIENT_VARS");
    foreach ($names as $name) {
        if ($data->has($name)) {
            $values[$name] = $data->get($name);
        }
    }
    // Update PROJECT_STYLESHEET_FILENAME
    $values["PROJECT_STYLESHEET_FILENAME"] = CssFile(Config("PROJECT_STYLESHEET_FILENAME"));
    return $values;
}

// Get global client variables
function GlobalClientVars(): array
{
    global $CURRENCY_FORMAT;
    $names = Config("GLOBAL_CLIENT_VARS");
    $values = [];
    foreach ($names as $name) {
        if (isset($GLOBALS[$name])) { // Global variable
            $values[ConstantCase($name)] = $GLOBALS[$name]; // Convert key to constant case
        } elseif (defined(PROJECT_NAMESPACE . $name)) { // Global constant
            $values[ConstantCase($name)] = constant(PROJECT_NAMESPACE . $name);
        } elseif (is_callable(PROJECT_NAMESPACE . $name, false, $func)) { // Global function
            $values[ConstantCase($name)] = $func();
        }
    }
	$csrf = Csrf();
    return array_merge([
        "ROWTYPE_VIEW" => RowType::VIEW->value, // 1
        "ROWTYPE_ADD" => RowType::ADD->value, // 2
        "ROWTYPE_EDIT" => RowType::EDIT->value, // 3
        "CURRENCY_FORMAT" => str_replace('¤', '$', $CURRENCY_FORMAT),
        "IS_RTL" => IsRTL(),
        "IS_LOGGED_IN" => IsLoggedIn(),
        "IS_LOGGING_IN_2FA" => IsLoggingIn2FA(),
        "IS_REMEMBER_ME" => IsRememberMe(),
        "LANGUAGE_ID" => str_replace("_", "-", CurrentLanguageID()),
        "PATH_BASE" => BasePath(true), // Path base // PHP
        "PROJECT_NAME" => PROJECT_NAME,
        "SESSION_ID" => Encrypt(SessionId()), // Session ID // PHP
        "TOKEN_NAME_KEY" => $csrf->getTokenNameKey(), // "csrf_name" // PHP
        "TOKEN_NAME" => $csrf->getTokenName(), // CSRF token name // PHP
        "ANTIFORGERY_TOKEN_KEY" => $csrf->getTokenValueKey(), // "csrf_value" // PHP
        "ANTIFORGERY_TOKEN" => $csrf->getTokenValue(), // CSRF token // PHP
        "API_JWT_AUTHORIZATION_HEADER" => "Authorization", // API JWT authorization header
        "API_JWT_TOKEN" => GetJwtToken(), // API JWT token
        "GENERATE_PASSWORD_LENGTH" => 16,
        "MIN_PASSWORD_STRENGTH" => 73,
        "IMAGE_FOLDER" => "images/", // Image folder
        "SESSION_TIMEOUT" => Config("SESSION_TIMEOUT") > 0 ? SessionTimeoutTime() : 0, // Session timeout time (seconds)
		"SESSION_KEEP_ALIVE_INTERVAL" => Config("SESSION_KEEP_ALIVE_INTERVAL") > 0 ? Config("SESSION_KEEP_ALIVE_INTERVAL") : 0, // Session keep alive interval (seconds)
        "TIMEOUT_URL" => GetUrl("logout"), // Timeout URL // PHP
        "SERVER_SEARCH_FILTER" => Config("SEARCH_FILTER_OPTION") == "Server",
        "CLIENT_SEARCH_FILTER" => Config("SEARCH_FILTER_OPTION") == "Client",
        "COOKIE_SECURE" => Config("COOKIE_SAMESITE") == "None" || IsHttps() && Config("COOKIE_SECURE"),
		"MS_SHOW_HELP_ONLINE" => (MS_SHOW_HELP_ONLINE == true) ? true : false,
		"MS_USE_MESSAGE_BOX_INSTEAD_OF_TOAST" => (Config("MS_USE_MESSAGE_BOX_INSTEAD_OF_TOAST") == true) ? true : false
    ], $values);
}

// Get/Set global login status array
function LoginStatus(mixed ...$args): mixed
{
    $loginStatus = Container(LoginStatusEvent::class);
    $numargs = count($args);
    if ($numargs == 1) { // Get
        return $loginStatus[$args[0]] ?? null;
    } elseif ($numargs == 2) { // Set
        $loginStatus[$args[0]] = $args[1];
        return null;
    }
    return $loginStatus->getArguments();
}

// Return Two Factor Authentication Type
function TwoFactorAuthenticationType(): ?string
{
    return Session(SESSION_TWO_FACTOR_AUTHENTICATION_TYPE)
        ?? (count(Config("TWO_FACTOR_AUTHENTICATION_TYPES")) == 1 ? Config("TWO_FACTOR_AUTHENTICATION_TYPES")[0] : null);
}

/**
 * Get two factor authentication class
 *
 * @param ?string $authType Authentication type. If null, returns the default.
 * @return string
 */
function TwoFactorAuthenticationClass(?string $authType = null): ?string
{
    if ($authType === null) {
        return Config("TWO_FACTOR_AUTHENTICATION_CLASS") ?? null;
    }
    foreach (Config("TWO_FACTOR_AUTHENTICATION_CLASSES") as $class) {
        if (in_array(TwoFactorAuthenticationInterface::class, class_implements($class)) && $class::TYPE == $authType) {
            return $class;
        }
    }
    return null;
}

// Set up login status
function SetupLoginStatus(): object
{
    $language = Language();
    $profile = Profile();
    $loginStatus = Container(LoginStatusEvent::class);
    $loginStatus["isLoggedIn"] = IsLoggedIn();
    $loginStatus["isAuthenticated"] = IsAuthenticated();
    $loginStatus["currentUserName"] = IsSysAdmin() || IsSysAdminUser()
        ? $language->phrase("UserAdministrator")
        : (CurrentUserName() ?: CurrentUserIdentifier());
    if (IsLoggedIn() && IsImpersonator()) {
        $orignalUser = OriginalUser()->getUserIdentifier();
        $title = sprintf($language->phrase("SwitchUserBack", true), $orignalUser);
        $html = sprintf($language->phrase("SwitchUserBack", null), $orignalUser);
        $loginStatus["currentUserName"] .= sprintf(Config("EXIT_IMPERSONATION_TEMPLATE"), $title, $html);
    }
    $currentPage = CurrentPageName();

    // Home page
    $homePage = "home";
    $loginStatus["homeUrl"] = GetUrl($homePage);

    // Logout page
    $logoutPage = "logout";
    $logoutUrl = GetUrl($logoutPage);
    $loginStatus["logout"] = [
        "ew-action" => "redirect",
        "url" => $logoutUrl
    ];
    $loginStatus["logoutUrl"] = $logoutUrl;
    $loginStatus["logoutText"] = $language->phrase("Logout", null);
    $loginStatus["canLogout"] = $logoutPage && (IsLoggedIn() || IsAuthenticated());

    // Login page
    $loginPage = "login";
    $loginUrl = GetUrl($loginPage);
    $loginStatus["login"] = [];
    if ($currentPage != $loginPage) {
        if (Config("USE_MODAL_LOGIN") && !IsMobile()) {
            $loginStatus["login"] = [
                "ew-action" => "modal",
                "footer" => false,
                "caption" => $language->phrase("Login", true),
                "size" => "modal-md",
                "url" => $loginUrl
            ];
        } else {
            $loginStatus["login"] = [
                "ew-action" => "redirect",
                "url" => $loginUrl
            ];
        }
    } else {
        $loginStatus["login"] = ["url" => $loginUrl];
    }
    $loginStatus["loginTitle"] = $language->phrase("Login", true);
    $loginStatus["loginText"] = $language->phrase("Login");
    $loginStatus["canLogin"] = $currentPage != $loginPage && $loginUrl && !IsLoggedIn() && !IsLoggingIn2FA();

    // Reset password page
    $resetPasswordPage = "resetpassword";
    $resetPasswordUrl = GetUrl($resetPasswordPage);
    if ($currentPage != $resetPasswordPage) {
        if (Config("USE_MODAL_RESET_PASSWORD") && !IsMobile()) {
            $loginStatus["resetPassword"] = [
                "ew-action" => "modal",
                "footer" => false,
                "caption" => $language->phrase("ResetPassword"),
                "size" => "modal-md",
                "url" => $resetPasswordUrl
            ];
        } else {
            $loginStatus["resetPassword"] = [
                "ew-action" => "redirect",
                "url" => $resetPasswordUrl
            ];
        }
    }
    $loginStatus["resetPasswordUrl"] = $resetPasswordUrl;
    $loginStatus["resetPasswordText"] = $language->phrase("ResetPassword");
    $loginStatus["canResetPassword"] = $resetPasswordUrl && !IsLoggedIn();

    // Change password page
    $changePasswordPage = "changepassword";
    $changePasswordUrl = GetUrl($changePasswordPage);
    if ($currentPage != $changePasswordPage) {
        if (Config("USE_MODAL_CHANGE_PASSWORD") && !IsMobile()) {
            $loginStatus["changePassword"] = [
                "ew-action" => "modal",
                "footer" => false,
                "caption" => $language->phrase("ChangePassword"),
                "size" => "modal-md",
                "url" => $changePasswordUrl
            ];
        } else {
            $loginStatus["changePassword"] = [
                "ew-action" => "redirect",
                "url" => $changePasswordUrl
            ];
        }
    }
    $loginStatus["changePasswordUrl"] = $changePasswordUrl;
    $loginStatus["changePasswordText"] = $language->phrase("ChangePassword");
    $loginStatus["canChangePassword"] = $changePasswordUrl && IsLoggedIn() && !IsSysAdmin();

    // Personal data page
    $personalDataPage = "personaldata";
    $personalDataUrl = GetUrl($personalDataPage);
    if ($currentPage != $personalDataPage) {
        $loginStatus["personalData"] = [
            "ew-action" => "redirect",
            "url" => $personalDataUrl
        ];
    }
    $loginStatus["hasPersonalData"] = $personalDataUrl && IsLoggedIn() && !IsSysAdmin();
    $loginStatus["personalDataUrl"] = $personalDataUrl;
    $loginStatus["personalDataText"] = $language->phrase("PersonalDataBtn");

    // Dispatch login status event and return the event
    return DispatchEvent($loginStatus, LoginStatusEvent::NAME);
}

// Is absolute path
function IsAbsolutePath(string $path): bool
{
    return Path::isAbsolute($path);
}

// Is remote path
function IsRemote(string $path): bool
{
    return str_contains($path, "://");
}

// Is remember me
function IsRememberMe(): bool
{
    return Config("SECURITY.firewalls.main.remember_me.always_remember_me") // Always remember me
        || Session(SESSION_USER_PROFILE_REMEMBER_ME) // Logging in
        || FigRequestCookies::get(Request(), Config("SECURITY.firewalls.main.remember_me.name"))->getValue() != ""; // Logged in
}

// Get current page heading
function CurrentPageHeading(): string
{
    global $Page;
    if (Config("PAGE_TITLE_STYLE") != "Title" && isset($Page) && method_exists($Page, "pageHeading")) {
        $heading = $Page->pageHeading();
        if ($heading != "") {
            return $heading;
        }
    }
    return Language()->projectPhrase("BodyTitle");
}

// Get current page subheading
function CurrentPageSubheading(): string
{
    global $Page;
    $heading = "";
    if (Config("PAGE_TITLE_STYLE") != "Title" && isset($Page) && method_exists($Page, "pageSubheading")) {
        $heading = $Page->pageSubheading();
    }
    return $heading;
}

// Convert HTML to text
function HtmlToText(string $html): string
{
    return \Soundasleep\Html2Text::convert($html, true);
}

// Get captcha object
function Captcha(): object
{
    global $Captcha;
    $class = Config("CAPTCHA_CLASS");
    if (!isset($Captcha) || !$Captcha instanceof ($class)) {
        $Captcha = Container()->make($class);
    }
    return $Captcha;
}

// Attributes for drill down
function DrillDownAttributes(string $url, string $id, string $hdr, bool $popover = true): array
{
    if (trim($url) == "") {
        return [];
    } else {
        if ($popover) {
            return [
                "data-ew-action" => "drilldown",
                "data-url" => preg_replace('/&(?!amp;)/', '&amp;', $url), // Replace & to &amp;
                "data-id" => $id,
                "data-hdr" => $hdr
            ];
        } else {
            return [
                "data-ew-action" => "redirect",
                "data-url" => str_replace("?d=1&", "?d=2&", $url) // Change d parameter to 2
            ];
        }
    }
}

/**
 * Convert field value for dropdown
 *
 * @param string $t Date type
 * @param mixed $val Field value
 * @return string Converted value
 */
function ConvertDisplayValue(string $t, mixed $val): string
{
    if ($val === null) {
        return Config("NULL_VALUE");
    } elseif ($val === "") {
        return Config("EMPTY_VALUE");
    }
    if (is_float($val)) {
        $val = (float)$val;
    }
    if ($t == "") {
        return $val;
    }
    if ($ar = explode(" ", $val)) {
        $ar = explode("-", $ar[0]);
    } else {
        return $val;
    }
    if (!$ar || count($ar) != 3) {
        return $val;
    }
    [$year, $month, $day] = $ar;
    return match (strtolower($t)) {
        "year" => $year,
        "quarter" => "$year|" . ceil(intval($month) / 3),
        "month" => "$year|$month",
        "day" => "$year|$month|$day",
        "date" => "$year-$month-$day",
    };
}

/**
 * Get dropdown display value
 *
 * @param mixed $v Field value
 * @param string $t Date type
 * @param int|string $fmt Date format
 * @return string Display value of the field value
 */
function GetDropDownDisplayValue(mixed $v, string $t = "", int|string $fmt = 0): string
{
    $v = strval($v);
    $language = Language();
    if (SameString($v, Config("NULL_VALUE"))) {
        return $language->phrase("NullLabel");
    } elseif (SameString($v, Config("EMPTY_VALUE"))) {
        return $language->phrase("EmptyLabel");
    } elseif (SameText($t, "boolean")) {
        return BooleanName($v);
    }
    if ($t == "") {
        return $v;
    }
    $ar = explode("|", strval($v));
    $t = strtolower($t);
    if (in_array($t, ["y", "year", "q", "quarter"])) {
        return (count($ar) >= 2) ? QuarterName($ar[1]) . " " . $ar[0] : $v;
    } elseif (in_array($t, ["m", "month"])) {
        return (count($ar) >= 2) ?  MonthName($ar[1]) . " " . $ar[0] : $v;
    } elseif (in_array($t, ["w", "week"])) {
        return (count($ar) >= 2) ? $language->phrase("Week") . " " . $ar[1] . ", " . $ar[0] : $v;
    } elseif (in_array($t, ["d", "day"])) {
        return (count($ar) >= 3) ? FormatDateTime($ar[0] . "-" . $ar[1] . "-" . $ar[2], $fmt) : $v;
    } elseif (in_array($t, ["date"])) {
        return FormatDateTime($v, $fmt);
    }
    return $v;
}

/**
 * Get dropdown edit value
 *
 * @param DbField $fld Field object
 * @param mixed $v Field value
 */
function GetDropDownEditValue(DbField $fld, mixed $v): array
{
    $val = trim(strval($v));
    $ar = [];
    if ($val != "") {
        $arwrk = $fld->isMultiSelect() ? explode(Config("MULTIPLE_OPTION_SEPARATOR"), $val) : [$val];
        foreach ($arwrk as $wrk) {
            $format = $fld->DateFilter ?: "date";
            $ar[] = ["lf" => $wrk, "df" => GetDropDownDisplayValue($wrk, $format, $fld->formatPattern())];
        }
    }
    return $ar;
}

/**
 * Get Boolean Name
 *
 * @param mixed $v Value, treat "T", "True", "Y", "Yes", "1" as true
 * @return string
 */
function BooleanName(mixed $v): string
{
    $language = Language();
    if ($v === null) {
        return $language->phrase("NullLabel");
    } elseif (SameText($v, "T") || SameText($v, "true") || SameText($v, "Y") || SameText($v, "YES") || strval($v) == "1") {
        return $language->phrase("BooleanYes");
    } else {
        return $language->phrase("BooleanNo");
    }
}

// Quarter name
function QuarterName(int $q): string
{
    $t = mktime(1, 0, 0, $q * 3);
    return FormatDateTime($t, Config("QUARTER_PATTERN"));
}

// Month name
function MonthName(int $m): string
{
    $t = mktime(1, 0, 0, $m);
    return FormatDateTime($t, Config("MONTH_PATTERN"));
}

// Get current year
function CurrentYear(): int
{
    return intval(date('Y'));
}

// Get current quarter
function CurrentQuarter(): int
{
    return ceil(intval(date('n')) / 3);
}

// Get current month
function CurrentMonth(): int
{
    return intval(date('n'));
}

// Get current day
function CurrentDay(): int
{
    return intval(date('j'));
}

/**
 * Update sort fields
 *
 * @param string $orderBy Order By clause
 * @param string|array $sort Sort fields
 * @param int $opt Option (1: merge all fields, 2: merge $orderBy fields only)
 * @return array Order By
 */
function UpdateSortFields(string $orderBy, string|array $sort, int $opt): array
{
    $arOrderBy = GetSortFields($orderBy);
    $cntOrderBy = count($arOrderBy);
    $arSort = GetSortFields($sort);
    $cntSort = count($arSort);
    $orderfld = "";
    for ($i = 0; $i < $cntSort; $i++) {
        $sortfld = $arSort[$i][0]; // Get sort field
        for ($j = 0; $j < $cntOrderBy; $j++) {
            $orderfld = $arOrderBy[$j][0]; // Get orderby field
            if ($orderfld == $sortfld) {
                $arOrderBy[$j][1] = $arSort[$i][1]; // Replace field
                break;
            }
        }
        if ($opt == 1) { // Append field
            if ($orderfld != $sortfld) {
                $arOrderBy[] = $arSort[$i];
            }
        }
    }
    return $arOrderBy;
}

// Get sort fields as array of [fieldName, sortDirection]
function GetSortFields(string|array|null $flds): array
{
    $ar = [];
    if (is_array($flds)) {
        $ar = $flds;
    } elseif (is_string($flds)) {
        $temp = "";
        $tok = strtok($flds, ",");
        while ($tok !== false) {
            $temp .= $tok;
            if (substr_count($temp, "(") === substr_count($temp, ")")) { // Make sure not inside parentheses
                $ar[] = $temp;
                $temp = "";
            } else {
                $temp .= ",";
            }
            $tok = strtok(",");
        }
    }
    $ar = array_filter($ar, fn($fld) => is_array($fld) || is_string($fld) && trim($fld) !== "");
    return array_map(function ($fld) {
        if (is_array($fld)) {
            return $fld;
        }
        $fld = trim($fld);
        if (preg_match('/\s(ASC|DESC)$/i', $fld, $matches)) {
            return [trim(substr($fld, 0, -4)), $matches[1]];
        }
        return [trim($fld), ""];
    }, $ar);
}

// Get reverse sort
function ReverseSort(string $sorttype): string
{
    return ($sorttype == "ASC") ? "DESC" : "ASC";
}

// Construct a crosstab field name
function CrosstabFieldExpression(string $smrytype, string $smryfld, string $colfld, string $datetype, mixed $val, string $qc, string $alias = "", string $dbid = "DB"): string
{
    if (SameString($val, Config("NULL_VALUE"))) {
        $wrkval = "NULL";
        $wrkqc = "";
    } elseif (SameString($val, Config("EMPTY_VALUE"))) {
        $wrkval = "";
        $wrkqc = $qc;
    } else {
        $wrkval = $val;
        $wrkqc = $qc;
    }
    switch ($smrytype) {
        case "SUM":
            $fld = $smrytype . "(" . $smryfld . "*" . SqlDistinctFactor($colfld, $datetype, $wrkval, $wrkqc, $dbid) . ")";
            break;
        case "COUNT":
            $fld = "SUM(" . SqlDistinctFactor($colfld, $datetype, $wrkval, $wrkqc, $dbid) . ")";
            break;
        case "MIN":
        case "MAX":
            $dbtype = GetConnectionType($dbid);
            $aggwrk = SqlDistinctFactor($colfld, $datetype, $wrkval, $wrkqc, $dbid);
            $fld = $smrytype . "(IF(" . $aggwrk . "=0,NULL," . $smryfld . "))";
            if ($dbtype == "MSSQL" || $dbtype == "ORACLE" || $dbtype == "SQLITE") {
                $fld = $smrytype . "(CASE " . $aggwrk . " WHEN 0 THEN NULL ELSE " . $smryfld . " END)";
            } elseif ($dbtype == "MYSQL" || $dbtype == "POSTGRESQL") {
                $fld = $smrytype . "(IF(" . $aggwrk . "=0,NULL," . $smryfld . "))";
            }
            break;
        case "AVG":
            $sumwrk = "SUM(" . $smryfld . "*" . SqlDistinctFactor($colfld, $datetype, $wrkval, $wrkqc, $dbid) . ")";
            if ($alias != "") {
//          $sumwrk .= " AS SUM_" . $alias;
                $sumwrk .= " AS " . QuotedName("sum_" . $alias, $dbid);
            }
            $cntwrk = "SUM(" . SqlDistinctFactor($colfld, $datetype, $wrkval, $wrkqc, $dbid) . ")";
            if ($alias != "") {
//          $cntwrk .= " AS CNT_" . $alias;
                $cntwrk .= " AS " . QuotedName("cnt_" . $alias, $dbid);
            }
            return $sumwrk . ", " . $cntwrk;
    }
    if ($alias != "") {
        $fld .= " AS " . QuotedName($alias, $dbid);
    }
    return $fld;
}

/**
 * Construct SQL Distinct factor
 * - ACCESS
 * y: IIf(Year(FieldName)=1996,1,0)
 * q: IIf(DatePart(""q"",FieldName,1,0)=1,1,0))
 * m: (IIf(DatePart(""m"",FieldName,1,0)=1,1,0)))
 * others: (IIf(FieldName=val,1,0)))
 * - MS SQL
 * y: (1-ABS(SIGN(Year(FieldName)-1996)))
 * q: (1-ABS(SIGN(DatePart(q,FieldName)-1)))
 * m: (1-ABS(SIGN(DatePart(m,FieldName)-1)))
 * - MySQL
 * y: IF(YEAR(FieldName)=1996,1,0))
 * q: IF(QUARTER(FieldName)=1,1,0))
 * m: IF(MONTH(FieldName)=1,1,0))
 * - SQLITE
 * y: (CASE CAST(STRFTIME('%Y',FieldName) AS INTEGER) WHEN 1996 THEN 1 ELSE 0 END)
 * q: (CASE (CAST(STRFTIME('%m',FieldName) AS INTEGER)+2)/3 WHEN 1 THEN 1 ELSE 0 END)
 * m: (CASE CAST(STRFTIME('%m',FieldName) AS INTEGER) WHEN 1 THEN 1 ELSE 0 END)
 * - PostgreSQL
 * y: CASE WHEN TO_CHAR(FieldName,'YYYY')='1996' THEN 1 ELSE 0 END
 * q: CASE WHEN TO_CHAR(FieldName,'Q')='1' THEN 1 ELSE 0 END
 * m: CASE WHEN TO_CHAR(FieldName,'MM')=LPAD('1',2,'0') THEN 1 ELSE 0 END
 * - Oracle
 * y: DECODE(TO_CHAR(FieldName,'YYYY'),'1996',1,0)
 * q: DECODE(TO_CHAR(FieldName,'Q'),'1',1,0)
 * m: DECODE(TO_CHAR(FieldName,'MM'),LPAD('1',2,'0'),1,0)
 *
 * @param string $fld Field
 * @param string $dateType Date type
 * @param mixed $val Value
 * @param string $qc Quote character
 * @param string $dbid Database ID
 * @return string
 */
function SqlDistinctFactor(string $fld, string $dateType, mixed $val, string $qc, string $dbid = "DB"): string
{
    $dbtype = GetConnectionType($dbid);
    if ($dbtype == "MSSQL") {
        if ($dateType == "y" && is_numeric($val)) {
            return "(1-ABS(SIGN(Year(" . $fld . ")-" . $val . ")))";
        } elseif (($dateType == "q" || $dateType == "m") && is_numeric($val)) {
            return "(1-ABS(SIGN(DatePart(" . $dateType . "," . $fld . ")-" . $val . ")))";
        } elseif ($dateType == "d") {
            return "(CASE FORMAT(" . $fld . ",'yyyy-MM-dd') WHEN " . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END)";
        } elseif ($dateType == "dt") {
            return "(CASE FORMAT(" . $fld . ",'yyyy-MM-dd HH:mm:ss') WHEN " . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END)";
        } else {
            if ($val == "NULL") {
                return "(CASE WHEN " . $fld . " IS NULL THEN 1 ELSE 0 END)";
            } else {
                return "(CASE " . $fld . " WHEN " . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END)";
            }
        }
    } elseif ($dbtype == "MYSQL") {
        if ($dateType == "y" && is_numeric($val)) {
            return "IF(YEAR(" . $fld . ")=" . $val . ",1,0)";
        } elseif ($dateType == "q" && is_numeric($val)) {
            return "IF(QUARTER(" . $fld . ")=" . $val . ",1,0)";
        } elseif ($dateType == "m" && is_numeric($val)) {
            return "IF(MONTH(" . $fld . ")=" . $val . ",1,0)";
        } elseif ($dateType == "d") {
            return "(CASE DATE_FORMAT(" . $fld . ", '%Y-%m-%d') WHEN " . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END)";
        } elseif ($dateType == "dt") {
            return "(CASE DATE_FORMAT(" . $fld . ", '%Y-%m-%d %H:%i:%s') WHEN " . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END)";
        } else {
            if ($val == "NULL") {
                return "IF(" . $fld . " IS NULL,1,0)";
            } else {
                return "IF(" . $fld . "=" . $qc . AdjustSql($val) . $qc . ",1,0)";
            }
        }
    } elseif ($dbtype == "SQLITE") {
        if ($dateType == "y" && is_numeric($val)) {
            return "(CASE CAST(STRFTIME('%Y', " . $fld . ") AS INTEGER) WHEN " . $val . " THEN 1 ELSE 0 END)";
        } elseif ($dateType == "q" && is_numeric($val)) {
            return "(CASE (CAST(STRFTIME('%m', " . $fld . ") AS INTEGER)+2)/3 WHEN " . $val . " THEN 1 ELSE 0 END)";
        } elseif ($dateType == "m" && is_numeric($val)) {
            return "(CASE CAST(STRFTIME('%m', " . $fld . ") AS INTEGER) WHEN " . $val . " THEN 1 ELSE 0 END)";
        } elseif ($dateType == "d") {
            return "(CASE STRFTIME('%Y-%m-%d', " . $fld . ") WHEN " . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END)";
        } elseif ($dateType == "dt") {
            return "(CASE STRFTIME('%Y-%m-%d %H:%M:%S', " . $fld . ") WHEN " . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END)";
        } else {
            if ($val == "NULL") {
                return "(CASE WHEN " . $fld . " IS NULL THEN 1 ELSE 0 END)";
            } else {
                return "(CASE " . $fld . " WHEN " . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END)";
            }
        }
    } elseif ($dbtype == "POSTGRESQL") {
        if ($dateType == "y" && is_numeric($val)) {
            return "CASE WHEN TO_CHAR(" . $fld . ",'YYYY')='" . $val . "' THEN 1 ELSE 0 END";
        } elseif ($dateType == "q" && is_numeric($val)) {
            return "CASE WHEN TO_CHAR(" . $fld . ",'Q')='" . $val . "' THEN 1 ELSE 0 END";
        } elseif ($dateType == "m" && is_numeric($val)) {
            return "CASE WHEN TO_CHAR(" . $fld . ",'MM')=LPAD('" . $val . "',2,'0') THEN 1 ELSE 0 END";
        } elseif ($dateType == "d") {
            return "CASE WHEN TO_CHAR(" . $fld . ",'YYYY') || '-' || LPAD(TO_CHAR(" . $fld . ",'MM'),2,'0') || '-' || LPAD(TO_CHAR(" . $fld . ",'DD'),2,'0')='" . $val . "' THEN 1 ELSE 0 END";
        } elseif ($dateType == "dt") {
            return "CASE WHEN TO_CHAR(" . $fld . ",'YYYY') || '-' || LPAD(TO_CHAR(" . $fld . ",'MM'),2,'0') || '-' || LPAD(TO_CHAR(" . $fld . ",'DD'),2,'0') || ' ' || LPAD(TO_CHAR(" . $fld . ",'HH24'),2,'0') || ':' || LPAD(TO_CHAR(" . $fld . ",'MI'),2,'0') || ':' || LPAD(TO_CHAR(" . $fld . ",'SS'),2,'0')='" . $val . "' THEN 1 ELSE 0 END";
        } else {
            if ($val == "NULL") {
                return "CASE WHEN " . $fld . " IS NULL THEN 1 ELSE 0 END";
            } else {
                return "CASE WHEN " . $fld . "=" . $qc . AdjustSql($val) . $qc . " THEN 1 ELSE 0 END";
            }
        }
    } elseif ($dbtype == "ORACLE") {
        if ($dateType == "y" && is_numeric($val)) {
            return "DECODE(TO_CHAR(" . $fld . ",'YYYY'),'" . $val . "',1,0)";
        } elseif ($dateType == "q" && is_numeric($val)) {
            return "DECODE(TO_CHAR(" . $fld . ",'Q'),'" . $val . "',1,0)";
        } elseif ($dateType == "m" && is_numeric($val)) {
            return "DECODE(TO_CHAR(" . $fld . ",'MM'),LPAD('" . $val . "',2,'0'),1,0)";
        } elseif ($dateType == "d") {
            return "DECODE(" . $fld . ",TO_DATE(" . $qc . AdjustSql($val) . $qc . ",'YYYY-MM-DD'),1,0)";
        } elseif ($dateType == "dt") {
            return "DECODE(" . $fld . ",TO_DATE(" . $qc . AdjustSql($val) . $qc . ",'YYYY-MM-DD HH24:MI:SS'),1,0)";
        } else {
            if ($val == "NULL") {
                return "(CASE WHEN " . $fld . " IS NULL THEN 1 ELSE 0 END)";
            } else {
                return "DECODE(" . $fld . "," . $qc . AdjustSql($val) . $qc . ",1,0)";
            }
        }
    }
}

// Evaluate summary value
function SummaryValue(mixed $val1, mixed $val2, string $ityp): mixed
{
    if (in_array($ityp, ["SUM", "COUNT", "AVG"])) {
        if ($val2 === null || !is_numeric($val2)) {
            return $val1;
        } else {
            return ($val1 + $val2);
        }
    } elseif ($ityp == "MIN") {
        if ($val2 === null || !is_numeric($val2)) {
            return $val1; // Skip null and non-numeric
        } elseif ($val1 === null) {
            return $val2; // Initialize for first valid value
        } elseif ($val1 < $val2) {
            return $val1;
        } else {
            return $val2;
        }
    } elseif ($ityp == "MAX") {
        if ($val2 === null || !is_numeric($val2)) {
            return $val1; // Skip null and non-numeric
        } elseif ($val1 === null) {
            return $val2; // Initialize for first valid value
        } elseif ($val1 > $val2) {
            return $val1;
        } else {
            return $val2;
        }
    }
}

/**
 * Render repeat column table
 *
 * @param int $totcnt Total count
 * @param int $rowcnt Zero based row count
 * @param int $repeatcnt Repeat count
 * @param int $rendertype Render type (1 or 2)
 * @return string HTML
 */
function RepeatColumnTable(int $totcnt, int $rowcnt, int $repeatcnt, int $rendertype): string
{
    $wrk = "";
    if ($rendertype == 1) { // Render control start
        if ($rowcnt == 0) {
            $wrk .= "<table class=\"ew-item-table\">";
        }
        if ($rowcnt % $repeatcnt == 0) {
            $wrk .= "<tr>";
        }
        $wrk .= "<td>";
    } elseif ($rendertype == 2) { // Render control end
        $wrk .= "</td>";
        if ($rowcnt % $repeatcnt == $repeatcnt - 1) {
            $wrk .= "</tr>";
        } elseif ($rowcnt == $totcnt - 1) {
            for ($i = ($rowcnt % $repeatcnt) + 1; $i < $repeatcnt; $i++) {
                $wrk .= "<td></td>";
            }
            $wrk .= "</tr>";
        }
        if ($rowcnt == $totcnt - 1) {
            $wrk .= "</table>";
        }
    }
    return $wrk;
}

// Register filter group
function RegisterFilterGroup(ReportField &$fld, string $groupName): void
{
    $filters = Config("REPORT_ADVANCED_FILTERS." . $groupName) ?: [];
    foreach ($filters as $id => $functionName) {
        RegisterFilter($fld, "@@" . $id, Language()->phrase($id), $functionName);
    }
}

// Register filter
function RegisterFilter(ReportField &$fld, string $id, string $name, string $functionName = ""): void
{
    if (!is_array($fld->AdvancedFilters)) {
        $fld->AdvancedFilters = [];
    }
    $wrkid = StartsString("@@", $id) ? $id : "@@" . $id;
    $key = substr($wrkid, 2);
    $fld->AdvancedFilters[$key] = new AdvancedFilter($wrkid, $name, $functionName);
}

// Unregister filter
function UnregisterFilter(ReportField &$fld, string $id): void
{
    if (is_array($fld->AdvancedFilters)) {
        $wrkid = StartsString("@@", $id) ? $id : "@@" . $id;
        $key = substr($wrkid, 2);
        foreach ($fld->AdvancedFilters as $filter) {
            if ($filter->ID == $wrkid) {
                unset($fld->AdvancedFilters[$key]);
                break;
            }
        }
    }
}

// Return date value
function DateValue(string $fldOpr, mixed $fldVal, int $valType, string $dbid = "DB"): string
{
    // Compose date string
    switch (strtolower($fldOpr)) {
        case "year":
            if ($valType == 1) {
                $wrkVal = "$fldVal-01-01";
            } elseif ($valType == 2) {
                $wrkVal = "$fldVal-12-31";
            }
            break;
        case "quarter":
            @list($y, $q) = explode("|", $fldVal);
            if (intval($y) == 0 || intval($q) == 0) {
                $wrkVal = "0000-00-00";
            } else {
                if ($valType == 1) {
                    $m = ($q - 1) * 3 + 1;
                    $m = str_pad($m, 2, "0", STR_PAD_LEFT);
                    $wrkVal = "$y-$m-01";
                } elseif ($valType == 2) {
                    $m = ($q - 1) * 3 + 3;
                    $m = str_pad($m, 2, "0", STR_PAD_LEFT);
                    $wrkVal = "$y-$m-" . DaysInMonth($y, $m);
                }
            }
            break;
        case "month":
            @list($y, $m) = explode("|", $fldVal);
            if (intval($y) == 0 || intval($m) == 0) {
                $wrkVal = "0000-00-00";
            } else {
                if ($valType == 1) {
                    $m = str_pad($m, 2, "0", STR_PAD_LEFT);
                    $wrkVal = "$y-$m-01";
                } elseif ($valType == 2) {
                    $m = str_pad($m, 2, "0", STR_PAD_LEFT);
                    $wrkVal = "$y-$m-" . DaysInMonth($y, $m);
                }
            }
            break;
        case "day":
        default:
            $wrkVal = str_replace("|", "-", $fldVal);
            $wrkVal = preg_replace('/\s+\d{2}\:\d{2}(\:\d{2})$/', "", $wrkVal); // Remove trailing time
    }

    // Add time if necessary
    if (preg_match('/(\d{4}|\d{2})-(\d{1,2})-(\d{1,2})/', $wrkVal)) { // Date without time
        if ($valType == 1) {
            $wrkVal .= " 00:00:00";
        } elseif ($valType == 2) {
            $wrkVal .= " 23:59:59";
        }
    }

    // Check if datetime
    if (preg_match('/(\d{4}|\d{2})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})/', $wrkVal)) { // DateTime
        $dateVal = $wrkVal;
    } else {
        $dateVal = "";
    }

    // Change date format if necessary
    $dbType = GetConnectionType($dbid);
    if (!SameText($dbType, "MYSQL") && !SameText($dbType, "SQLITE")) {
        $dateVal = str_replace("-", "/", $dateVal);
    }
    return $dateVal;
}

// Past
function IsPast(string $fldExpr, string $dbid = "DB"): string
{
    $dt = date("Y-m-d H:i:s");
    $dbType = GetConnectionType($dbid);
    if (!SameText($dbType, "MYSQL") && !SameText($dbType, "SQLITE")) {
        $dt = str_replace("-", "/", $dt);
    }
    return "(" . $fldExpr . " < " . QuotedValue($dt, DataType::DATE, $dbid) . ")";
}

// Future;
function IsFuture(string $fldExpr, string $dbid = "DB"): string
{
    $dt = date("Y-m-d H:i:s");
    $dbType = GetConnectionType($dbid);
    if (!SameText($dbType, "MYSQL") && !SameText($dbType, "SQLITE")) {
        $dt = str_replace("-", "/", $dt);
    }
    return "(" . $fldExpr . " > " . QuotedValue($dt, DataType::DATE, $dbid) . ")";
}

/**
 * WHERE class for between 2 dates
 *
 * @param string $fldExpr Field expression
 * @param string $dt1 Begin date (>=)
 * @param string $dt2 End date (<)
 * @return string
 */
function IsBetween(string $fldExpr, string $dt1, string $dt2, string $dbid = "DB"): string
{
    $dbType = GetConnectionType($dbid);
    if (!SameText($dbType, "MYSQL") && !SameText($dbType, "SQLITE")) {
        $dt1 = str_replace("-", "/", $dt1);
        $dt2 = str_replace("-", "/", $dt2);
    }
    return "(" . $fldExpr . " >= " . QuotedValue($dt1, DataType::DATE, $dbid) . " AND " . $fldExpr . " < " . QuotedValue($dt2, DataType::DATE, $dbid) . ")";
}

// Last 30 days
function IsLast30Days(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d", strtotime("-29 days"));
    $dt2 = date("Y-m-d", strtotime("+1 days"));
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Last 14 days
function IsLast14Days(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d", strtotime("-13 days"));
    $dt2 = date("Y-m-d", strtotime("+1 days"));
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Last 7 days
function IsLast7Days(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d", strtotime("-6 days"));
    $dt2 = date("Y-m-d", strtotime("+1 days"));
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Next 30 days
function IsNext30Days(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d");
    $dt2 = date("Y-m-d", strtotime("+30 days"));
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Next 14 days
function IsNext14Days(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d");
    $dt2 = date("Y-m-d", strtotime("+14 days"));
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Next 7 days
function IsNext7Days(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d");
    $dt2 = date("Y-m-d", strtotime("+7 days"));
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Yesterday
function IsYesterday(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d", strtotime("-1 days"));
    $dt2 = date("Y-m-d");
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Today
function IsToday(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d");
    $dt2 = date("Y-m-d", strtotime("+1 days"));
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Tomorrow
function IsTomorrow(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m-d", strtotime("+1 days"));
    $dt2 = date("Y-m-d", strtotime("+2 days"));
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Last month
function IsLastMonth(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m", strtotime("-1 months")) . "-01";
    $dt2 = date("Y-m") . "-01";
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// This month
function IsThisMonth(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m") . "-01";
    $dt2 = date("Y-m", strtotime("+1 months")) . "-01";
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Next month
function IsNextMonth(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y-m", strtotime("+1 months")) . "-01";
    $dt2 = date("Y-m", strtotime("+2 months")) . "-01";
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Last two weeks
function IsLast2Weeks(string $fldExpr, string $dbid = "DB"): string
{
    if (strtotime("this Sunday") == strtotime("today")) {
        $dt1 = date("Y-m-d", strtotime("-14 days this Sunday"));
        $dt2 = date("Y-m-d", strtotime("this Sunday"));
    } else {
        $dt1 = date("Y-m-d", strtotime("-14 days last Sunday"));
        $dt2 = date("Y-m-d", strtotime("last Sunday"));
    }
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Last week
function IsLastWeek(string $fldExpr, string $dbid = "DB"): string
{
    if (strtotime("this Sunday") == strtotime("today")) {
        $dt1 = date("Y-m-d", strtotime("-7 days this Sunday"));
        $dt2 = date("Y-m-d", strtotime("this Sunday"));
    } else {
        $dt1 = date("Y-m-d", strtotime("-7 days last Sunday"));
        $dt2 = date("Y-m-d", strtotime("last Sunday"));
    }
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// This week
function IsThisWeek(string $fldExpr, string $dbid = "DB"): string
{
    if (strtotime("this Sunday") == strtotime("today")) {
        $dt1 = date("Y-m-d", strtotime("this Sunday"));
        $dt2 = date("Y-m-d", strtotime("+7 days this Sunday"));
    } else {
        $dt1 = date("Y-m-d", strtotime("last Sunday"));
        $dt2 = date("Y-m-d", strtotime("+7 days last Sunday"));
    }
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Next week
function IsNextWeek(string $fldExpr, string $dbid = "DB"): string
{
    if (strtotime("this Sunday") == strtotime("today")) {
        $dt1 = date("Y-m-d", strtotime("+7 days this Sunday"));
        $dt2 = date("Y-m-d", strtotime("+14 days this Sunday"));
    } else {
        $dt1 = date("Y-m-d", strtotime("+7 days last Sunday"));
        $dt2 = date("Y-m-d", strtotime("+14 days last Sunday"));
    }
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Next two week
function IsNext2Weeks(string $fldExpr, string $dbid = "DB"): string
{
    if (strtotime("this Sunday") == strtotime("today")) {
        $dt1 = date("Y-m-d", strtotime("+7 days this Sunday"));
        $dt2 = date("Y-m-d", strtotime("+21 days this Sunday"));
    } else {
        $dt1 = date("Y-m-d", strtotime("+7 days last Sunday"));
        $dt2 = date("Y-m-d", strtotime("+21 days last Sunday"));
    }
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Last year
function IsLastYear(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y", strtotime("-1 years")) . "-01-01";
    $dt2 = date("Y") . "-01-01";
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// This year
function IsThisYear(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y") . "-01-01";
    $dt2 = date("Y", strtotime("+1 years")) . "-01-01";
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Next year
function IsNextYear(string $fldExpr, string $dbid = "DB"): string
{
    $dt1 = date("Y", strtotime("+1 years")) . "-01-01";
    $dt2 = date("Y", strtotime("+2 years")) . "-01-01";
    return IsBetween($fldExpr, $dt1, $dt2, $dbid);
}

// Days in month
function DaysInMonth(int $y, int $m): int
{
    return match ($m) {
        1, 3, 5, 7, 8, 10, 12 => 31,
        4, 6, 9, 11 => 30,
        2 => ($y % 4 == 0) ? 29 : 28,
        default => 0
    };
}

/**
 * Get group value
 * Field type:
 *  1: numeric, 2: date, 3: string
 * Group type:
 *  numeric: i = interval, n = normal
 *  date: d = Day, w = Week, m = Month, q = Quarter, y = Year
 *  string: f = first nth character, n = normal
 *
 * @param DbField $fld Field
 * @param mixed $val Value
 * @return mixed
 */
function GroupValue(ReportField &$fld, mixed $val): mixed
{
    $ft = $fld->Type;
    $grp = $fld->GroupByType;
    $intv = $fld->GroupInterval;
    if (in_array($ft, [20, 3, 2, 16, 4, 5, 131, 6, 17, 18, 19, 21])) { // adBigInt, adInteger, adSmallInt, adTinyInt, adSingle, adDouble, adNumeric, adCurrency, adUnsignedTinyInt, adUnsignedSmallInt, adUnsignedInt, adUnsignedBigInt (numeric)
        if (!is_numeric($val)) {
            return $val;
        }
        $wrkIntv = intval($intv);
        if ($wrkIntv <= 0) {
            $wrkIntv = 10;
        }
        return ($grp == "i") ? intval($val / $wrkIntv) : $val;
    } elseif (in_array($ft, [201, 203, 129, 130, 200, 202])) { // adLongVarChar, adLongVarWChar, adChar, adWChar, adVarChar, adVarWChar (string)
        $wrkIntv = intval($intv);
        if ($wrkIntv <= 0) {
            $wrkIntv = 1;
        }
        return ($grp == "f") ? substr(strval($val), 0, $wrkIntv) : $val;
    }
    return $val;
}

// Display group value
function DisplayGroupValue(ReportField &$fld, mixed $val): mixed
{
    $ft = $fld->Type;
    $grp = $fld->GroupByType;
    $intv = $fld->GroupInterval;
    if ($val === null) {
        return Language()->phrase("NullLabel");
    }
    if ($val == "") {
        return Language()->phrase("EmptyLabel");
    }
    switch ($ft) {
        // Case adBigInt, adInteger, adSmallInt, adTinyInt, adSingle, adDouble, adNumeric, adCurrency, adUnsignedTinyInt, adUnsignedSmallInt, adUnsignedInt, adUnsignedBigInt (numeric)
        case 20:
        case 3:
        case 2:
        case 16:
        case 4:
        case 5:
        case 131:
        case 6:
        case 17:
        case 18:
        case 19:
        case 21:
            $wrkIntv = intval($intv);
            if ($wrkIntv <= 0) {
                $wrkIntv = 10;
            }
            switch ($grp) {
                case "i":
                    return strval($val * $wrkIntv) . " - " . strval(($val + 1) * $wrkIntv - 1);
                default:
                    return $val;
            }
            break;
        // Case adDate, adDBDate, adDBTime, adDBTimeStamp (date)
        case 7:
        case 133:
        case 135:
        case 146:
        case 134:
        case 145:
            $ar = explode("|", $val);
            switch ($grp) {
                case "y":
                    return $ar[0];
                case "q":
                    if (count($ar) < 2) {
                        return $val;
                    }
                    return FormatQuarter($ar[0], $ar[1]);
                case "m":
                    if (count($ar) < 2) {
                        return $val;
                    }
                    return FormatMonth($ar[0], $ar[1]);
                case "w":
                    if (count($ar) < 2) {
                        return $val;
                    }
                    return FormatWeek($ar[0], $ar[1]);
                case "d":
                    if (count($ar) < 3) {
                        return $val;
                    }
                    return FormatDay($ar[0], $ar[1], $ar[2]);
                case "h":
                    return FormatHour($ar[0]);
                case "min":
                    return FormatMinute($ar[0]);
                default:
                    return $val;
            }
            break;
        default: // String and others
            return $val; // Ignore
    }
}

// Format quarter
function FormatQuarter(string|int $y, string|int $q): string
{
    return "Q" . $q . "/" . $y;
}

// Format month
function FormatMonth(string|int $y, string|int $m): string
{
    return $m . "/" . $y;
}

// Format week
function FormatWeek(string|int $y, string|int $w): string
{
    return "WK" . $w . "/" . $y;
}

// Format day
function FormatDay(string|int $y, string|int $m, string|int $d): string
{
    return $y . "-" . $m . "-" . $d;
}

// Format hour
function FormatHour(string|int $h): string
{
    $h = intval($h);
    return match (true) {
        $h == 0 => "12 AM",
        $h < 12 => $h . " AM",
        $h == 12 => "12 PM",
        default => ($h - 12) . " PM"
    };
}

// Format minute
function FormatMinute(string|int $n): string
{
    return $n . " MIN";
}

// Return detail filter SQL
function DetailFilterSql(ReportField &$fld, string $fn, mixed $val, string $dbid = "DB"): string
{
    $ft = $fld->DataType;
    if ($fld->GroupSql != "") {
        $ft = DataType::STRING;
    }
    $ar = is_array($val) ? $val : [$val];
    $sqlwrk = "";
    foreach ($ar as $v) {
        if ($sqlwrk != "") {
            $sqlwrk .= " OR ";
        }
        $sqlwrk .= $fn;
        if ($v === null) {
            $sqlwrk .= " IS NULL";
        } else {
            $sqlwrk .= " = " . QuotedValue($v, $ft, $dbid);
        }
    }
    return $sqlwrk;
}

// Compare values by custom sequence
function CompareValueCustom(mixed $v1, mixed $v2, string $seq): bool
{
    if ($seq == "_number") { // Number
        if (is_numeric($v1) && is_numeric($v2)) {
            return ((float)$v1 > (float)$v2);
        }
    } elseif ($seq == "_date") { // Date
        if (is_numeric(strtotime($v1)) && is_numeric(strtotime($v2))) {
            return (strtotime($v1) > strtotime($v2));
        }
    } elseif ($seq != "") { // Custom sequence
        if (is_array($seq)) {
            $ar = $seq;
        } else {
            $ar = explode(",", $seq);
        }
        if (in_array($v1, $ar) && in_array($v2, $ar)) {
            return (array_search($v1, $ar) > array_search($v2, $ar));
        } else {
            return in_array($v2, $ar);
        }
    }
    return ($v1 > $v2);
}

// Escape chars for XML
function XmlEncode(mixed $val): string
{
    return htmlspecialchars(strval($val));
}

// Load drop down list
function LoadDropDownList(mixed &$list, mixed $val): void
{
    if (is_array($val)) {
        $ar = $val;
    } elseif ($val != Config("ALL_VALUE") && !IsEmpty($val)) {
        $ar = [$val];
    } else {
        $ar = [];
    }
    $list = [];
    foreach ($ar as $v) {
        if (!IsEmpty($v) && !StartsString("@@", $v)) {
            $list[] = $v;
        }
    }
}

// Get quick search keywords
function GetQuickSearchKeywords(?string $search, string $searchType): array
{
    if ($searchType != "=") {
        $ar = [];
        // Match quoted keywords (i.e.: "...")
        if (preg_match_all('/"([^"]*)"/i', $search ?: "", $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $p = strpos($search, $match[0]);
                $str = substr($search, 0, $p);
                $search = substr($search, $p + strlen($match[0]));
                if (strlen(trim($str)) > 0) {
                    $ar = array_merge($ar, explode(" ", trim($str)));
                }
                $ar[] = $match[1]; // Save quoted keyword
            }
        }
        // Match individual keywords
        if (strlen(trim($search)) > 0) {
            $ar = array_merge($ar, explode(" ", trim($search)));
        }
    } else {
        $ar = [$search];
    }
    return $ar;
}

// Get quick search filter
function GetQuickSearchFilter(array $flds, array $keywords, string $searchType, bool $searchAnyFields, string $dbid = "DB"): string
{
    // Search keyword in any fields
    if ((SameText($searchType, "OR") || SameText($searchType, "AND")) && $searchAnyFields) {
        $filter = "";
        foreach ($keywords as $keyword) {
            if ($keyword != "") {
                $ar = [$keyword];
                $thisFilter = array_reduce($flds, function ($res, $fld) use ($ar, $searchType, $dbid) {
                    AddFilter($res, GetQuickSearchFilterForField($fld, $ar, $searchType, $dbid), "OR");
                    return $res;
                }, "");
                AddFilter($filter, $thisFilter, $searchType);
            }
        }
    } else {
        $filter = array_reduce($flds, function ($res, $fld) use ($keywords, $searchType, $dbid) {
            AddFilter($res, GetQuickSearchFilterForField($fld, $keywords, $searchType, $dbid), "OR");
            return $res;
        }, "");
    }
    return $filter;
}

// Get quick search filter for field
function GetQuickSearchFilterForField(DbField $fld, array $keywords, string $searchType, string $dbid = "DB"): string
{
    $defCond = SameText($searchType, "OR") ? "OR" : "AND";
    $arSql = []; // Array for SQL parts
    $arCond = []; // Array for search conditions
    $j = 0; // Number of SQL parts
    foreach ($keywords as $keyword) {
        $keyword = trim($keyword);
        if (Config("BASIC_SEARCH_IGNORE_PATTERN") != "") {
            $keyword = preg_replace(Config("BASIC_SEARCH_IGNORE_PATTERN"), "\\", $keyword);
            $ar = explode("\\", $keyword);
        } else {
            $ar = [$keyword];
        }
        foreach ($ar as $keyword) {
            if ($keyword != "") {
                $wrk = "";
                if ($keyword == "OR" && $searchType == "") {
                    if ($j > 0) {
                        $arCond[$j - 1] = "OR";
                    }
                } elseif ($keyword == Config("NULL_VALUE")) {
                    $wrk = $fld->Expression . " IS NULL";
                } elseif ($keyword == Config("NOT_NULL_VALUE")) {
                    $wrk = $fld->Expression . " IS NOT NULL";
                } elseif ($fld->IsVirtual && $fld->Visible) {
                    $wrk = $fld->VirtualExpression . Like(Wildcard($keyword, "LIKE", $dbid), $dbid);
                } elseif ($fld->DataType != DataType::NUMBER || is_numeric($keyword)) {
                    $wrk = $fld->BasicSearchExpression . Like(Wildcard($keyword, "LIKE", $dbid), $dbid);
                }
                if ($wrk != "") {
                    $arSql[$j] = $wrk;
                    $arCond[$j] = $defCond;
                    $j += 1;
                }
            }
        }
    }
    $cnt = count($arSql);
    $quoted = false;
    $sql = "";
    if ($cnt > 0) {
        for ($i = 0; $i < $cnt - 1; $i++) {
            if ($arCond[$i] == "OR") {
                if (!$quoted) {
                    $sql .= "(";
                }
                $quoted = true;
            }
            $sql .= $arSql[$i];
            if ($quoted && $arCond[$i] != "OR") {
                $sql .= ")";
                $quoted = false;
            }
            $sql .= " " . $arCond[$i] . " ";
        }
        $sql .= $arSql[$cnt - 1];
        if ($quoted) {
            $sql .= ")";
        }
    }
    return $sql;
}

// Get report filter
function GetReportFilter(ReportField &$fld, bool $default = false, string $dbid = "DB"): string
{
    $dbtype = GetConnectionType($dbid);
    $fldName = $fld->Name;
    $fldExpression = $fld->searchExpression();
    $fldDataType = $fld->searchDataType();
    $fldDateTimeFormat = $fld->DateTimeFormat;
    $fldVal = $default ? $fld->AdvancedSearch->SearchValueDefault : $fld->AdvancedSearch->SearchValue;
    $fldOpr = $default ? $fld->AdvancedSearch->SearchOperatorDefault : $fld->AdvancedSearch->SearchOperator;
    $fldCond = $default ? $fld->AdvancedSearch->SearchConditionDefault : $fld->AdvancedSearch->SearchCondition;
    $fldVal2 = $default ? $fld->AdvancedSearch->SearchValue2Default : $fld->AdvancedSearch->SearchValue2;
    $fldOpr2 = $default ? $fld->AdvancedSearch->SearchOperator2Default : $fld->AdvancedSearch->SearchOperator2;
    $fldVal = ConvertSearchValue($fldVal, $fldOpr, $fld);
    $fldVal2 = ConvertSearchValue($fldVal2, $fldOpr2, $fld);
    $fldOpr = ConvertSearchOperator($fldOpr, $fld, $fldVal);
    $fldOpr2 = ConvertSearchOperator($fldOpr2, $fld, $fldVal2);
    $wrk = "";
    if (in_array($fldOpr, ["BETWEEN", "NOT BETWEEN"])) {
        $isValidValue = $fldDataType != DataType::NUMBER || $fld->VirtualSearch || IsNumericSearchValue($fldVal, $fldOpr, $fld) && IsNumericSearchValue($fldVal2, $fldOpr2, $fld);
        if ($fldVal != "" && $fldVal2 != "" && $isValidValue) {
            $wrk = $fldExpression . " " . $fldOpr . " " . QuotedValue($fldVal, $fldDataType, $dbid) .
                " AND " . QuotedValue($fldVal2, $fldDataType, $dbid);
        }
    } else {
        // Handle first value
        if ($fldVal != "" && IsValidOperator($fldOpr)) {
            $wrk = SearchFilter($fldExpression, $fldOpr, $fldVal, $fldDataType, $dbid);
        }
        // Handle second value
        $wrk2 = "";
        if ($fldVal2 != "" && !IsEmpty($fldOpr2) && IsValidOperator($fldOpr2)) {
            $wrk2 = SearchFilter($fldExpression, $fldOpr2, $fldVal2, $fldDataType, $dbid);
        }
        // Combine SQL
        AddFilter($wrk, $wrk2, $fldCond == "OR" ? "OR" : "AND");
    }
    return $wrk;
}

// Return date search string
function GetDateFilterSql(string $fldExpr, string $fldOpr, mixed $fldVal, DataType $fldType, string $dbid = "DB"): string
{
    $wrkVal1 = DateValue($fldOpr, $fldVal, 1, $dbid);
    $wrkVal2 = DateValue($fldOpr, $fldVal, 2, $dbid);
    return ($wrkVal1 != "" && $wrkVal2 != "")
        ? $fldExpr . " BETWEEN " . QuotedValue($wrkVal1, $fldType, $dbid) . " AND " . QuotedValue($wrkVal2, $fldType, $dbid)
        : "";
}

// Group filter
function GroupSql(string $fldExpr, string $grpType, int $grpInt = 0, string $dbid = "DB"): string
{
    $dbtype = GetConnectionType($dbid);
    switch ($grpType) {
        case "f": // First n characters
            return match ($dbtype) {
                "MSSQL", "MYSQL" => "SUBSTRING(" . $fldExpr . ",1," . $grpInt . ")", // MSSQL / MySQL
                default => "SUBSTR(" . $fldExpr . ",1," . $grpInt . ")" // SQLite / PostgreSQL / Oracle
            };
        case "i": // Interval
            return match ($dbtype) {
                "MSSQL" => "(" . $fldExpr . "/" . $grpInt . ")", // MSSQL
                "MYSQL" => "(" . $fldExpr . " DIV " . $grpInt . ")", // MySQL
                "SQLITE" => "CAST(" . $fldExpr . "/" . $grpInt . " AS TEXT)", // SQLite
                "POSTGRESQL" => "(" . $fldExpr . "/" . $grpInt . ")", // PostgreSQL
                default => "FLOOR(" . $fldExpr . "/" . $grpInt . ")" // Oracle
            };
        case "y": // Year
            return match ($dbtype) {
                "MSSQL", "MYSQL" => "YEAR(" . $fldExpr . ")", // MSSQL/MySQL
                "SQLITE" => "CAST(STRFTIME('%Y'," . $fldExpr . ") AS INTEGER)", // SQLite
                default => "TO_CHAR(" . $fldExpr . ",'YYYY')" // PostgreSQL/Oracle
            };
        case "xq": // Quarter
            return match ($dbtype) {
                "MSSQL" => "DATEPART(QUARTER," . $fldExpr . ")", // MSSQL
                "MYSQL" => "QUARTER(" . $fldExpr . ")", // MySQL
                "SQLITE" => "CAST(STRFTIME('%m'," . $fldExpr . ") AS INTEGER)+2)/3", // SQLite
                default => "TO_CHAR(" . $fldExpr . ",'Q')" // PostgreSQL/Oracle
            };
        case "q": // Quarter (with year)
            return match ($dbtype) {
                "MSSQL" => "(STR(YEAR(" . $fldExpr . "),4) + '|' + STR(DATEPART(QUARTER," . $fldExpr . "),1))", // MSSQL
                "MYSQL" => "CONCAT(CAST(YEAR(" . $fldExpr . ") AS CHAR(4)), '|', CAST(QUARTER(" . $fldExpr . ") AS CHAR(1)))", // MySQL
                "SQLITE" => "(CAST(STRFTIME('%Y'," . $fldExpr . ") AS TEXT) || '|' || CAST((CAST(STRFTIME('%m'," . $fldExpr . ") AS INTEGER)+2)/3 AS TEXT))", // SQLite
                default => "(TO_CHAR(" . $fldExpr . ",'YYYY') || '|' || TO_CHAR(" . $fldExpr . ",'Q'))" // PostgreSQL/Oracle
            };
        case "xm": // Month
            return match ($dbtype) {
                "MSSQL", "MYSQL" => "MONTH(" . $fldExpr . ")", // MSSQL/MySQL
                "SQLITE" => "CAST(STRFTIME('%m'," . $fldExpr . ") AS INTEGER)", // SQLite
                default => "TO_CHAR(" . $fldExpr . ",'MM')", // PostgreSQL/Oracle
            };
        case "m": // Month (with year)
            return match ($dbtype) {
                "MSSQL" => "(STR(YEAR(" . $fldExpr . "),4) + '|' + REPLACE(STR(MONTH(" . $fldExpr . "),2,0),' ','0'))", // MSSQL
                "MYSQL" => "CONCAT(CAST(YEAR(" . $fldExpr . ") AS CHAR(4)), '|', CAST(LPAD(MONTH(" . $fldExpr . "),2,'0') AS CHAR(2)))", // MySQL
                "SQLITE" => "CAST(STRFTIME('%Y|%m'," . $fldExpr . ") AS TEXT)", // SQLite
                default => "(TO_CHAR(" . $fldExpr . ",'YYYY') || '|' || TO_CHAR(" . $fldExpr . ",'MM'))" // PostgreSQL/Oracle
            };
        case "w":
            return match ($dbtype) {
                "MSSQL"  => "(STR(YEAR(" . $fldExpr . "),4) + '|' + REPLACE(STR(DATEPART(WEEK," . $fldExpr . "),2,0),' ','0'))", // MSSQL
                // "MYSQL" => "CONCAT(CAST(YEAR(" . $fldExpr . ") AS CHAR(4)), '|', CAST(LPAD(WEEKOFYEAR(" . $fldExpr . "),2,'0') AS CHAR(2)))", // MySQL
                "MYSQL" => "CONCAT(CAST(YEAR(" . $fldExpr . ") AS CHAR(4)), '|', CAST(LPAD(WEEK(" . $fldExpr . ",0),2,'0') AS CHAR(2)))", // MySQL
                "SQLITE" => "CAST(STRFTIME('%Y|%W'," . $fldExpr . ") AS TEXT)", // SQLite
                default => "(TO_CHAR(" . $fldExpr . ",'YYYY') || '|' || TO_CHAR(" . $fldExpr . ",'WW'))"
            };
        case "d":
            return match ($dbtype) {
                "MSSQL" => "(STR(YEAR(" . $fldExpr . "),4) + '|' + REPLACE(STR(MONTH(" . $fldExpr . "),2,0),' ','0') + '|' + REPLACE(STR(DAY(" . $fldExpr . "),2,0),' ','0'))", // MSSQL
                "MYSQL" => "CONCAT(CAST(YEAR(" . $fldExpr . ") AS CHAR(4)), '|', CAST(LPAD(MONTH(" . $fldExpr . "),2,'0') AS CHAR(2)), '|', CAST(LPAD(DAY(" . $fldExpr . "),2,'0') AS CHAR(2)))", // MySQL
                "SQLITE" => "CAST(STRFTIME('%Y|%m|%d'," . $fldExpr . ") AS TEXT)", // SQLite
                default => "(TO_CHAR(" . $fldExpr . ",'YYYY') || '|' || LPAD(TO_CHAR(" . $fldExpr . ",'MM'),2,'0') || '|' || LPAD(TO_CHAR(" . $fldExpr . ",'DD'),2,'0'))"
            };
        case "h":
            return match ($dbtype) {
                "MSSQL", "MYSQL" => "HOUR(" . $fldExpr . ")", // MSSQL/MySQL
                "SQLITE" => "CAST(STRFTIME('%H'," . $fldExpr . ") AS INTEGER)", // SQLite
                default => "TO_CHAR(" . $fldExpr . ",'HH24')"
            };
        case "min":
            return match ($dbtype) {
                "MSSQL", "MYSQL" => "MINUTE(" . $fldExpr . ")", // MSSQL/MySQL
                "SQLITE" => "CAST(STRFTIME('%M'," . $fldExpr . ") AS INTEGER)", // SQLite
                default => "TO_CHAR(" . $fldExpr . ",'MI')"
            };
    }
    return "";
}

/**
 * JSON encode
 *
 * @param mixed $val The value being encoded
 * @param int $flags Bitmask consisting of JSON constants (see https://www.php.net/manual/en/json.constants.php)
 * @param int $depth Set the maximum depth. Must be greater than zero.
 * @return string|false (non UTF-8)
 */
function JsonEncode(mixed $val, int $flags = 0, int $depth = 512)
{
    if ($val === null) {
        return "null";
    }
    $res = json_encode($val, $flags, $depth);
    return $res;
}

/**
 * JSON decode
 *
 * @param ?string $val The JSON string being decoded (non UTF-8)
 * @param ?bool $associative When true, JSON objects will be returned as associative arrays.
 * @param int $depth Maximum nesting depth of the structure being decoded
 * @param int $flags Bitmask of JSON_BIGINT_AS_STRING, JSON_INVALID_UTF8_IGNORE, JSON_INVALID_UTF8_SUBSTITUTE, JSON_OBJECT_AS_ARRAY, JSON_THROW_ON_ERROR
 * @return mixed null is returned if the json cannot be decoded or if the encoded data is deeper than the nesting limit
 */
function JsonDecode(?string $val, ?bool $associative = null, int $depth = 512, int $flags = 0)
{
    if ($val === null) {
        return $val;
    }
    $res = json_decode($val, $associative, $depth, $flags);
    return $res;
}

function MasinoBreadcrumbLinks() {
	global $basePath;
	$curPage = CurrentPageName();
	if (empty($curPage))
		$curPage = 'error404';
	$nav = "<ol class=\"breadcrumb float-sm-end ew-breadcrumbs\">";
	$sSql = "SELECT C.* FROM " . Config("MS_MASINO_BREADCRUMBLINKS_TABLE") . " AS B, " . Config("MS_MASINO_BREADCRUMBLINKS_TABLE") . " AS C WHERE (B.Lft BETWEEN C.Lft AND C.Rgt) AND (B.Page_URL LIKE '" . $curPage . "') ORDER BY C.Lft";
	$cnt = ExecuteScalar($sSql, "DB");
	$conn = Conn();
	if (!empty($cnt)) {
		$stmt = $conn->executeQuery($sSql);
		$i = 1;
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetchAssociative()) {
				if ($i==1) {
					$nav .= "<li><a href='" . $basePath . "index' data-message='wait' title='".Language()->phrase("Home")."' alt='".Language()->phrase("Home")."' class='ew-home' /><span class='fa fa-home ew-icon'></span></a>&nbsp;<span class=\"divider\">".MS_BREADCRUMBLINKS_DIVIDER."</span>&nbsp;</li>";
				} else {
					if ($i >= $stmt->rowCount()) {
						$nav .= "<li class=\"active\"><strong>" . Language()->phrase($row["Page_Title"]) . "</strong></li>";
					} else {
						if (MS_BREADCRUMBLINKS_NO_LINKS) {
							$nav .= "<li>".Language()->phrase($row["Page_Title"])."&nbsp;<span class=\"divider\">".MS_BREADCRUMBLINKS_DIVIDER."</span>&nbsp;</li>";
						} else {
							if (empty($row["Page_URL"])) {
								$nav .= "<li>".Language()->phrase($row["Page_Title"])."&nbsp;<span class=\"divider\">".MS_BREADCRUMBLINKS_DIVIDER."</span>&nbsp;</li>";
							} else {
								$nav .= "<li><a href='". $basePath . $row["Page_URL"] ."' data-message='wait'>".Language()->phrase($row["Page_Title"])."</a>&nbsp;<span class=\"divider\">".MS_BREADCRUMBLINKS_DIVIDER."</span>&nbsp;</li>";
							}
						}
					}
				}
				$i++;
			}
		} else {
			$nav = "";
		}
	}
	$nav .= "</ol>";
	return $nav;
}
