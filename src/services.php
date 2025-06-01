<?php

namespace PHPMaker2025\ucarsip;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\User\InMemoryUserChecker;
use Symfony\Component\Runtime\Runner\Symfony\HttpKernelRunner;
use Symfony\Component\Runtime\Runner\Symfony\ResponseRunner;
use Symfony\Component\Runtime\SymfonyRuntime;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $values = Config("SERVICE_LOCATOR_VALUES");
    array_walk($values, fn(&$value, $key) => $value = service($value));
    $services->set(SecurityServiceLocator::class)
        ->public()
        ->args([
            service_locator([
                'security.listener.remember_me.main' => service('security.listener.remember_me.main'),
                'security.authenticator.firewall_aware_login_link_handler' => service('security.authenticator.login_link_handler.main'),
                LoginLinkHandlerInterface::class => service('security.authenticator.login_link_handler.main'),
                'security.authentication_utils' => service('security.authentication_utils'),
                'security.helper' => service('security.helper'),
                'request_stack' => service('request_stack'),
                'event_dispatcher' => service('event_dispatcher'),
                'http_kernel' => service('http_kernel'),
                'profile.storage' => service('profile.storage'),
                'app.login_rate_limiter' => service('app.login_rate_limiter'),
                'security.user_providers' => service('security.user_providers'),
                'security.authorization_checker' => service('security.authorization_checker'),
            ] + $values),
        ]);
    $services->set('request_stack', RequestStack::class)
        ->public();
    $services->alias(RequestStack::class, 'request_stack');
    if (Config('DEBUG')) {
        $services->set('logger')->synthetic();
        $services->alias(LoggerInterface::class, 'logger');
    }
    $services->set('event_dispatcher', EventDispatcher::class)
        ->public()
        ->tag('container.hot_path')
        ->tag('event_dispatcher');
    $services->alias(EventDispatcher::class, 'event_dispatcher');
    $services->alias(EventDispatcherInterface::class, 'event_dispatcher');
    $services->set('controller_resolver', ControllerResolver::class);
    $services->set('http_kernel', HttpKernel::class)
        ->public()
        ->args([
            service('event_dispatcher'),
            service('controller_resolver'),
            service('request_stack'),
            null, // $argumentResolver
            false, // $handleAllThrowables
        ])
        ->tag('container.hot_path')
        ->tag('container.preload', [
            'class' => HttpKernelRunner::class,
        ])
        ->tag('container.preload', [
            'class' => ResponseRunner::class,
        ])
        ->tag('container.preload', [
            'class' => SymfonyRuntime::class,
        ]);
    $services->alias(HttpKernelInterface::class, 'http_kernel');
    $services->set('router.request_context', RequestContext::class)
        ->factory([
            RequestContext::class,
            'fromUri',
        ])
        ->args([
            '', // Not used
            'localhost', // Not used
            'http', // Not used
            80, // Default port for http (for redirect response)
            443, // Default port for https (for redirect response)
        ]);
    $services->alias(RequestContext::class, 'router.request_context');
    $services->set('property_accessor', PropertyAccessor::class);
    $services->alias(PropertyAccessor::class, 'property_accessor');
    $services->set('user.profile')->synthetic();
    $services->set('profile.storage', ProfileStorage::class)
        ->args([
            service('user.profile'),
            service('security.authentication_utils'),
        ]);

    // Global factory for username
    $services->set('app.rate_limiter_factory.global', RateLimiterFactory::class)
        ->args([
            Config("LOGIN_RATE_LIMITERS.global"),
            service('profile.storage'),
        ]);

    // Local factory for username + IP
    $services->set('app.rate_limiter_factory.local', RateLimiterFactory::class)
        ->args([
            Config("LOGIN_RATE_LIMITERS.local"),
            service('profile.storage'),
        ]);
    $services->set('app.login_rate_limiter', LoginRateLimiter::class)
        ->arg('$globalFactory', service('app.rate_limiter_factory.global'))
        ->arg('$localFactory', service('app.rate_limiter_factory.local'));
    $services->set('router', UrlGenerator::class);
    $services->alias(UrlGeneratorInterface::class, 'router');
    $services->set(EntityUserProvider::class)
        ->args([
            Entity\User::class,
        ]);
    $services->set(AuthenticationEventSubscriber::class)
        ->arg('$profile', service('user.profile'))
        ->arg('$language', service('app.language'))
        ->arg('$security', service('app.security'))
        ->arg('$symfonySecurity', service('security.helper'))
        ->tag('kernel.event_subscriber');
    $services->set(LogoutSubscriber::class)
        ->arg('$profile', service('user.profile'))
        ->arg('$language', service('app.language'))
        ->arg('$security', service('app.security'))
        ->tag('kernel.event_subscriber');
    $services->set(FirewallRequestMatcher::class);
    $services->set(AuthenticationSuccessHandler::class);
    $services->alias(AuthenticationSuccessHandlerInterface::class, AuthenticationSuccessHandler::class);
    $services->set(AuthenticationFailureHandler::class);
    $services->alias(AuthenticationFailureHandlerInterface::class, AuthenticationFailureHandler::class);
    $services->set(LegacyPasswordHasher::class);
    $services->set(WindowsUserProvider::class);
    $services->set(AccessTokenExtractor::class);
    $services->set(AccessTokenHandler::class);
    $services->set(AccessTokenUserProvider::class);
    $services->set(AuthenticationEntryPoint::class);
    $services->set(NullAdapter::class);
    $services->set(InMemoryUserChecker::class)
        ->tag('security.user_checker.main');
    $services->set(UserChecker::class)
        ->tag('security.user_checker.main');
    $services->set('cache.app', NullAdapter::class);
    $services->set('cache.rememberme', FilesystemAdapter::class)
        ->arg('$defaultLifetime', Config("SECURITY.firewalls.main.remember_me.lifetime"))
        ->arg('$directory', Config('REMEMBER_ME.CACHE_DIR'));
    $services->set(RememberMeTokenProvider::class)
        ->arg('$cache', service('cache.rememberme'))
        ->arg('$outdatedTokenTtl', Config('REMEMBER_ME.OUTDATED_TOKEN_TTL'));
    $services->set(SwitchUserVoter::class)
        ->tag('security.voter');

    // Services Config event
    Services_Config($services);
};
