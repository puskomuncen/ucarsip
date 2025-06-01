<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends ControllerBase
{
    // list
    #[Route("/settingslist[/{Option_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "list.settings")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "SettingsList");
    }

    // add
    #[Route("/settingsadd[/{Option_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "add.settings")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "SettingsAdd");
    }

    // view
    #[Route("/settingsview[/{Option_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "view.settings")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "SettingsView");
    }

    // edit
    #[Route("/settingsedit[/{Option_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "edit.settings")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "SettingsEdit");
    }

    // delete
    #[Route("/settingsdelete[/{Option_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "delete.settings")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "SettingsDelete");
    }
}
