<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * breadcrumblinksdeletesp controller
 */
class BreadcrumblinksdeletespController extends ControllerBase
{
    // custom
    #[Route("/breadcrumblinksdeletesp[/{params:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "custom.breadcrumblinksdeletesp")]
    public function custom(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "Breadcrumblinksdeletesp");
    }
}
