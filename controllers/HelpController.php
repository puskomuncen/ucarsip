<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

class HelpController extends ControllerBase
{
    // list
    #[Route("/helplist[/{Help_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "list.help")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpList");
    }

    // add
    #[Route("/helpadd[/{Help_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "add.help")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpAdd");
    }

    // view
    #[Route("/helpview[/{Help_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "view.help")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpView");
    }

    // edit
    #[Route("/helpedit[/{Help_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "edit.help")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpEdit");
    }

    // delete
    #[Route("/helpdelete[/{Help_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "delete.help")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpDelete");
    }

    // preview
    #[Route("/helppreview", methods: ["GET", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "preview.help")]
    public function preview(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpPreview", null, false);
    }
}
