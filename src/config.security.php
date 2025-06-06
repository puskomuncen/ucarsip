<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\ChainUserChecker;
use Symfony\Component\Cache\Adapter\NullAdapter;

/**
 * PHPMaker security configuration
 */
return [
    'SECURITY' => [
        'password_hashers' => [
            InMemoryUser::class => [ // Don't change!
                'algorithm' => 'bcrypt',
                'cost' => 15,
            ],
            PasswordAuthenticatedUserInterface::class => [
                'id' => LegacyPasswordHasher::class,
            ],
        ],
        'providers' => [
            'database_users' => [
                'id' => EntityUserProvider::class,
            ],
            'admin_user' => [
                'memory' => [
                    'users' => [
                        'admin' => [
                            'password' => '$2y$15$f1kx/ESwPQuFRcc8au6Dpe9Rihkn9K/dzWe1sEx.M1586wSZ8DJ16',
                            'roles' => [
                                'ROLE_SUPER_ADMIN'
                            ],
                        ],
                    ],
                ],
            ],
            'all_users' => [
                'chain' => [
                    'providers' => [
                        'database_users',
                        'admin_user',
                    ],
                ],
            ],
        ],
        // See https://symfony.com/doc/current/security.html#a-authentication-firewalls
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
            'main' => [
                'provider' => 'all_users',
                'user_checker' => 'security.user_checker.chain.main',
                'switch_user' => [
                    'parameter' => 'switchuser',
                    'role' => 'ROLE_ALLOWED_TO_SWITCH',
                    'target_route' => 'login' // Don't change! The user needs to go there and log in Advanced Security again.
                ],

                // Limit login attempts
                // See https://symfony.com/doc/current/security.html#limiting-login-attempts and https://symfony.com/doc/current/rate_limiter.html
                'login_throttling' => [
                    'limiter' => 'app.login_rate_limiter',
                ],

                // This option allows users to choose to stay logged in for longer than
                // the session lasts using a cookie. Reference: https://symfony.com/doc/current/security/remember_me.html
                'remember_me' => [
                    'name' => 'REMEMBERME',
                    'remember_me_parameter' => '_remember_me',
                    'secret' => '%kernel.secret%',
                    'lifetime' => 604800, // In seconds, default: 7 days
                    'path' => "/",
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => "lax",
                    'always_remember_me' => false,
                    'token_provider' => [
                        'service' => RememberMeTokenProvider::class,
                    ],
                    'token_verifier' => RememberMeTokenProvider::class
                ],
                'login_link' => [
                    'check_route' => 'login_check',
                    'signature_properties' => [
                        'Username',
                    ],
                    'lifetime' => 600, // In seconds
                    'used_link_cache' => NullAdapter::class, // Expired links not supported
                    'success_handler' => AuthenticationSuccessHandler::class,
                    'failure_handler' => AuthenticationFailureHandler::class,
                ],

                // This allows the user to login by submitting a username and password
                // Reference: https://symfony.com/doc/current/security/form_login_setup.html
                'form_login' => [
                    'check_path' => '/login',
                    'login_path' => '/login',
                    'default_target_path' => '/',
                    'username_parameter' => 'username',
                    'password_parameter' => 'password',
                    'success_handler' => AuthenticationSuccessHandler::class,
                    'failure_handler' => AuthenticationFailureHandler::class,
                ],
                'entry_point' => AuthenticationEntryPoint::class,
                'logout' => [
                    'invalidate_session' => false,
                    'path' => '/logout'
                ],
            ],
        ],
        // Easy way to control access for large sections of your site
        // Note: Only the *first* access control that matches will be used
        // Further access control to be done by permission middleware
        'access_control' => [
            [
                'path' => '^/login',
                'roles' => 'PUBLIC_ACCESS',
            ],
            [
                'path' => '^/register',
                'roles' => 'PUBLIC_ACCESS',
            ],
            [
                'path' => '^/resetpassword',
                'roles' => 'PUBLIC_ACCESS',
            ],
            [
                'path' => '^/index',
                'roles' => 'PUBLIC_ACCESS',
            ],
            [
                'path' => '^/$',
                'roles' => 'PUBLIC_ACCESS',
            ],
            [
                'request_matcher' => FirewallRequestMatcher::class,
            ],
        ],

        // The role_hierarchy values are static, they cannot be stored in a database.
        // See https://symfony.com/doc/current/security.html#hierarchical-roles
        'role_hierarchy' => [ // for static user levels only
            'ROLE_SUPER_ADMIN' => 'ROLE_ADMIN',
            'ROLE_ADMIN' => [
                'ROLE_USER',
                'ROLE_ALLOWED_TO_SWITCH',
                'ROLE_DEFAULT'
            ],
            'ROLE_DEFAULT' => [
                'ROLE_USER',
            ],
        ],
    ]
];
