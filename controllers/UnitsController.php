<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

class UnitsController extends ControllerBase
{
    // list
    #[Route("/unitslist[/{unit_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "list.units")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UnitsList");
    }

    // add
    #[Route("/unitsadd[/{unit_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "add.units")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UnitsAdd");
    }

    // view
    #[Route("/unitsview[/{unit_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "view.units")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UnitsView");
    }

    // edit
    #[Route("/unitsedit[/{unit_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "edit.units")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UnitsEdit");
    }

    // delete
    #[Route("/unitsdelete[/{unit_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "delete.units")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UnitsDelete");
    }
}
