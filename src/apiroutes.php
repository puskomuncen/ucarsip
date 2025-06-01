<?php

namespace PHPMaker2025\ucarsip;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

// Handle Routes
return function (App $app) {
    $app->group('/api', function (RouteCollectorProxyInterface $group) {
        // Dispatch API action event
        DispatchEvent(new ApiActionEvent($group), ApiActionEvent::NAME);
    })->add(JwtMiddleware::class);

    // Other API actions
    $app->any('/api/[{params:.*}]', ApiController::class)
        ->add(JwtMiddleware::class)
        ->setName("catchall");
};
