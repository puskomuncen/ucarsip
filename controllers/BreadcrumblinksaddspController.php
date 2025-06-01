<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * breadcrumblinksaddsp controller
 */
class BreadcrumblinksaddspController extends ControllerBase
{
    // custom
    #[Route("/breadcrumblinksaddsp[/{params:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "custom.breadcrumblinksaddsp")]
    public function custom(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "Breadcrumblinksaddsp");
    }
}
