<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\CallableResolver;
use Slim\Views\PhpRenderer;
use Slim\HttpCache\CacheProvider;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Routing\RouteCollector;
use Slim\Factory\Psr17\SlimHttpPsr17Factory;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Events;
use FastRoute\RouteParser\Std;
use Illuminate\Encryption\Encrypter;
use HTMLPurifier_Config;
use HTMLPurifier;
use Detection\MobileDetect;
use Tuupola\Middleware\CorsMiddleware;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Auth\AuthenticatorInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use PHPMailer\PHPMailer\PHPMailer;
use ReflectionEnum;
use League\Flysystem\Filesystem;
use League\Flysystem\PathPrefixer;
use League\Flysystem\Local\LocalFilesystemAdapter;

// Definitions
$definitions = [];

// Debug
if (IsDebug()) {
    // Debug bar
    $definitions["debug.bar"] = \DI\autowire(PhpDebugBar::class);
    $definitions[PhpDebugBar::class] = \DI\get("debug.bar");
    $definitions[PhpHttpDriver::class] = \DI\autowire();
    $definitions[PhpDebugBarMiddleware::class] = \DI\autowire();
    // CORS middleware
    Config("CORS.logger", \DI\get("app.logger"));
}

// Connections and entity managers
foreach (array_keys(Config("Databases")) as $dbid) {
    $definitions["connection." . $dbid] = \DI\factory([ConnectionFactory::class, "create"])
        ->parameter("dbid", $dbid)
        ->parameter("eventManager", \DI\get(EventManager::class));
    $definitions["entitymanager." . $dbid] = \DI\factory([EntityManagerFactory::class, "create"])
        ->parameter("connection", \DI\get("connection." . $dbid))
        ->parameter("config", \DI\get("entitymanager.config"))
        ->parameter("eventManager", \DI\get(EventManager::class))
        ->parameter("softDeleteable", false);
}

// Mailer
if (Config("MAILER_DSN")) { // 3rd party transport, e.g. Amazon SES
    if (Config("TRANSPORT_FACTORY") instanceof TransportFactoryInterface) {
        $definitions[TransportInterface::class] = \DI\factory([Config("TRANSPORT_FACTORY"), "create"])
            ->parameter("dsn", Dsn::fromString(Config("MAILER_DSN")));
    } else {
        $definitions[TransportInterface::class] = \DI\factory([Transport::class, "fromDsn"])
            ->parameter("dsn", Config("MAILER_DSN"))
            ->parameter("dispatcher", \DI\get("event.dispatcher"))
            ->parameter("logger", \DI\get("app.logger"));
    }
} elseif (Config("USE_PHPMAILER")) { // PHPMailer
    $definitions["mailer.dsn"] = \DI\create(Dsn::class)->constructor(
        "phpmailer",
        Config("SMTP.SERVER"),
        Config("ENCRYPT_USER_NAME_AND_PASSWORD") ? PhpDecrypt(Config("SMTP.SERVER_USERNAME")) : Config("SMTP.SERVER_USERNAME"),
        Config("ENCRYPT_USER_NAME_AND_PASSWORD") ? PhpDecrypt(Config("SMTP.SERVER_PASSWORD")) : Config("SMTP.SERVER_PASSWORD"),
        Config("SMTP.SERVER_PORT"),
        [
            "secure" => Config("SMTP.SECURE_OPTION"),
            "options" => Config("SMTP.OPTIONS"),
            "debug" => IsDebug(),
            "verify_peer" => false,
        ]
    );
    $definitions[PhpMailerTransportFactory::class] = \DI\create() // Cannot use autowire because AbstractTransportFactory uses ?EventDispatcherInterface and ?LoggerInterface
        ->constructor(dispatcher: \DI\get("event.dispatcher"), logger: \DI\get("app.logger"));
    $definitions[TransportInterface::class] = \DI\factory([PhpMailerTransportFactory::class, "create"])
        ->parameter("dsn", \DI\get("mailer.dsn"));
} else { // Symfony built-in SMTP mailer
    $definitions["mailer.dsn"] = \DI\create(Dsn::class)->constructor(
        Config("SMTP.SECURE_OPTION") != "" ? "smtps" : "smtp",
        Config("SMTP.SERVER"),
        Config("ENCRYPT_USER_NAME_AND_PASSWORD") ? PhpDecrypt(Config("SMTP.SERVER_USERNAME")) : Config("SMTP.SERVER_USERNAME"),
        Config("ENCRYPT_USER_NAME_AND_PASSWORD") ? PhpDecrypt(Config("SMTP.SERVER_PASSWORD")) : Config("SMTP.SERVER_PASSWORD"),
        Config("SMTP.SERVER_PORT"),
        ["verify_peer" => false, ...Config("SMTP.OPTIONS")],
    );
    $definitions[EsmtpTransportFactory::class] = \DI\autowire();
    $definitions[TransportInterface::class] = function (ContainerInterface $c) {
        $transport = $c->get(EsmtpTransportFactory::class)->create($c->get("mailer.dsn"));
        $authenticators = Config("MAILER_AUTHENTICATORS");
        if (
            is_array($authenticators)
            && array_all($authenticators, fn($authenticator) => $authenticator instanceof AuthenticatorInterface)
        ) {
            $transport->setAuthenticator($authenticators);
        }
        return $transport;
    };
}
$nativeFilesystem = new SymfonyFilesystem();
$htmlPurifierCacheFolder = PathJoin(__DIR__ , "..", "log/cache/htmlpurifier");
$nativeFilesystem->mkdir($htmlPurifierCacheFolder);

return [
    "app.main" => \DI\factory([AppFactory::class, "createFromContainer"]),
    "app.cache" => \DI\create(CacheProvider::class),
    "app.view" => \DI\create(PhpRenderer::class)->constructor("views/"),
    "notification.view" => \DI\create(PhpRenderer::class)->constructor("lang/"),
    "app.audit" => \DI\create(Logger::class)->constructor("audit", [\DI\create(AuditTrailHandler::class)->constructor("log/audit.log")]), // For audit trail
    "app.logger" => \DI\create(Logger::class)->constructor("log", [\DI\create(RotatingFileHandler::class)->constructor("log/log.log")]),
    LoggerInterface::class => \DI\get("app.logger"),
    "app.mailer" => \DI\autowire(Mailer::class),
    "request.creator" => \DI\factory([ServerRequestCreatorFactory::class, "create"]),
    HttpErrorHandler::class => \DI\autowire()
        ->constructorParameter("layoutTemplate", "layout.php") // Layout template
        ->constructorParameter("errorTemplate", "Error.php") // Error template
        ->constructorParameter("showSourceCode", false), // Show source code
    Psr17Factory::class => \DI\create(),
    "psr17.factory" => \DI\get(Psr17Factory::class),
    "stream.factory" => \DI\get("psr17.factory"),
    StreamFactoryInterface::class => \DI\get("stream.factory"),
    "response.factory" => \DI\factory([SlimHttpPsr17Factory::class, "createDecoratedResponseFactory"])
        ->parameter("responseFactory", \DI\get("psr17.factory"))
        ->parameter("streamFactory", \DI\get("stream.factory")),
    ResponseFactoryInterface::class => \DI\get("response.factory"),
    CallableResolverInterface::class => \DI\create(CallableResolver::class)->constructor(\DI\get(ContainerInterface::class)), // Need to set container
    InvocationStrategyInterface::class => \DI\create(RequestResponseStrategy::class),
    RouteCollectorInterface::class => \DI\autowire(RouteCollector::class)
        ->constructorParameter("container", \DI\get(ContainerInterface::class))
        ->constructorParameter("defaultInvocationStrategy", \DI\get(InvocationStrategyInterface::class)),
    EventManager::class => \DI\create(), // For entity managers
    "orm.cache" => \DI\create(FilesystemAdapter::class)->constructor(directory: Config("DOCTRINE.CACHE_DIR")),
    "result.cache" => \DI\create(FilesystemAdapter::class)->constructor(defaultLifetime: Config("DOCTRINE.RESULT_CACHE_LIFETIME"), directory: Config("DOCTRINE.RESULT_CACHE_DIR")),
    "entitymanager.config" => \DI\factory([ORMSetup::class, "createAttributeMetadataConfiguration"])
        ->parameter("paths", Config("DOCTRINE.METADATA_DIRS"))
        ->parameter("isDevMode", IsDevelopment())
        ->parameter("cache", IsDevelopment() ? \DI\get("array.cache") : \DI\get("orm.cache")),
    "connection.middlewares" => [\DI\create(\Firehed\DbalLogger\Middleware::class)->constructor(\DI\get("debug.stack"))],
    "native.filesystem" => $nativeFilesystem,
    "local.filesystem.root" => Path::canonicalize(__DIR__ . "/../" . Config("LOCAL_FILESYSTEM_ROOT")),
    "local.filesystem.prefixer" => \DI\create(PathPrefixer::class)->constructor(\DI\get("local.filesystem.root"), DIRECTORY_SEPARATOR),
    "local.filesystem.adapter" => \DI\create(LocalFilesystemAdapter::class)->constructor(\DI\get("local.filesystem.root")),
    "local.filesystem" => \DI\create(Filesystem::class)->constructor(
        \DI\get("local.filesystem.adapter"),
        ["public_url" => Config("LOCAL_FILESYSTEM_PUBLIC_URL") ?? PathJoin(AppUrl(), Config("LOCAL_FILESYSTEM_ROOT"))]
    ),
    "http.client" => \DI\value(fn(string $baseUri) => new \GuzzleHttp\Client(["base_uri" => $baseUri])),
    "http.filesystem.adapter" => \DI\value(fn(\GuzzleHttp\Client $client) => new \Netzarbeiter\FlysystemHttp\HttpAdapterPsr($client)),
    "https.client" => \DI\get("http.client"),
    "https.filesystem.adapter" => \DI\get("http.filesystem.adapter"),
    "csrf.prefix" => "csrf",
    CsrfMiddleware::class => \DI\autowire()->constructorParameter("prefix", \DI\get("csrf.prefix")),
    "html.purifier.config" => ["Cache.SerializerPath" => $htmlPurifierCacheFolder, ...[]],
    "html.purifier" => \DI\create(HTMLPurifier::class)->constructor(\DI\get("html.purifier.config")),
    DebugStack::class => \DI\autowire()->constructorParameter("enabled", IsDebug() || Config("LOG_TO_FILE")),
    "debug.stack" => \DI\get(DebugStack::class),
    "mime.types" => \DI\create(MimeTypes::class),
    "reflection.enum.allow" => \DI\create(ReflectionEnum::class)->constructor(Allow::class),
    AdvancedSecurity::class => \DI\autowire(),
    "app.security" => \DI\get(AdvancedSecurity::class),
    UserProfile::class => \DI\create(),
    "user.profile" => \DI\get(UserProfile::class),
    Language::class => \DI\create(),
    "app.language" => \DI\get(Language::class),
    "notifier.channels" => Config("NOTIFIER.channels"),
    "app.notifier" => \DI\create(Notifier::class)->constructor(\DI\get("notifier.channels")),
    NotifierInterface::class => \DI\get("app.notifier"),
    Breadcrumb::class => \DI\create(),
    "native.session.storage.options" => [
        "cookie_path" => Config("COOKIE_PATH"),
        "cookie_lifetime" => Config("COOKIE_LIFETIME"),
        "cookie_samesite" => Config("COOKIE_SAMESITE"),
        "cookie_httponly" => Config("COOKIE_HTTP_ONLY"),
        "cookie_secure" => Config("COOKIE_SECURE")
    ],
    FileUploadHandler::class => \DI\autowire(),
    "session.handler" => fn(ContainerInterface $c) => IsApi() ? new NullSessionHandler() : null, // null => default session handler
    NativeSessionStorage::class => \DI\create()->constructor(\DI\get("native.session.storage.options"), \DI\get("session.handler")),
    SessionStorageInterface::class => \DI\get(NativeSessionStorage::class),
    Session::class => \DI\autowire(),
    "app.session" => \DI\get(Session::class),
    SessionInterface::class => \DI\get(Session::class),
    "event.dispatcher" => EventDispatcher(),
    EventDispatcherInterface::class => \DI\get("event.dispatcher"),
    "cors.middleware.options" => Config("CORS"),
    CorsMiddleware::class => \DI\create()->constructor(\DI\get("cors.middleware.options")),
    "cors.middleware" => \DI\get(CorsMiddleware::class),
    "csp.middleware" => \DI\factory([CspMiddleware::class, "createFromData"])->parameter("data", Config("CSP")),
    "csp.nonce" => Config("CSP") ? \DI\factory(["csp.middleware", "nonce"]) : null,
    SessionMiddleware::class => \DI\autowire(),
    MaintenanceMiddleware::class => \DI\autowire()
        ->method("setRetryAfter", Config("MAINTENANCE_RETRY_AFTER"))
        ->method("setTemplate", Config("MAINTENANCE_TEMPLATE")),
    "encryption.key" => fn() => AesEncryptionKey(base64_decode(Config("AES_ENCRYPTION_KEY"))),
    "mobile.detect" => \DI\create(MobileDetect::class),
    LoginStatusEvent::class => \DI\create(),
    AuthenticationMiddleware::class => \DI\create(),
    PermissionMiddleware::class => \DI\create(),
    ApiPermissionMiddleware::class => \DI\create(),
    JwtMiddleware::class => \DI\create(),
    Std::class => \DI\create(),
    Encrypter::class => \DI\create()->constructor(\DI\get("encryption.key"), Config("AES_ENCRYPTION_CIPHER")),
    ArrayAdapter::class => \DI\create(),
    "array.cache" => \DI\get(ArrayAdapter::class),
    CacheItemPoolInterface::class => \DI\get(ArrayAdapter::class),
    ValidatorInterface:: class => \DI\factory([Validation::class, "createValidator"]),
    "security.container" => \DI\factory([SecurityContainerFactory::class, "create"]),

    // Tables
    "announcement" => \DI\autowire(Announcement::class),
    "help" => \DI\autowire(Help::class),
    "help_categories" => \DI\autowire(HelpCategories::class),
    "home" => \DI\autowire(Home::class),
    "languages" => \DI\autowire(Languages::class),
    "settings" => \DI\autowire(Settings::class),
    "theuserprofile" => \DI\autowire(Theuserprofile::class),
    "userlevelpermissions" => \DI\autowire(Userlevelpermissions::class),
    "userlevels" => \DI\autowire(Userlevels::class),
    "users" => \DI\autowire(Users::class),
    "dispositions" => \DI\autowire(Dispositions::class),
    "letters" => \DI\autowire(Letters::class),
    "tracks" => \DI\autowire(Tracks::class),
    "units" => \DI\autowire(Units::class),

    // User table
    "usertable" => \DI\get("users"),
] + $definitions;
