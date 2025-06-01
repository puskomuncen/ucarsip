<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

class TracksController extends ControllerBase
{
    // list
    #[Route("/trackslist[/{track_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "list.tracks")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TracksList");
    }

    // add
    #[Route("/tracksadd[/{track_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "add.tracks")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TracksAdd");
    }

    // view
    #[Route("/tracksview[/{track_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "view.tracks")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TracksView");
    }

    // edit
    #[Route("/tracksedit[/{track_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "edit.tracks")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TracksEdit");
    }

    // delete
    #[Route("/tracksdelete[/{track_id}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "delete.tracks")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TracksDelete");
    }
}
