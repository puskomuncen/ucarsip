<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * breadcrumblinkschecksp controller
 */
class BreadcrumblinkscheckspController extends ControllerBase
{
    // custom
    #[Route("/breadcrumblinkschecksp[/{params:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "custom.breadcrumblinkschecksp")]
    public function custom(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "Breadcrumblinkschecksp");
    }
}
