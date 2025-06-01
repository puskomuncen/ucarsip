<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

class UsersController extends ControllerBase
{
    // list
    #[Route("/userslist[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "list.users")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UsersList");
    }

    // add
    #[Route("/usersadd[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "add.users")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UsersAdd");
    }

    // view
    #[Route("/usersview[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "view.users")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UsersView");
    }

    // edit
    #[Route("/usersedit[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "edit.users")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UsersEdit");
    }

    // delete
    #[Route("/usersdelete[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "delete.users")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UsersDelete");
    }

    // search
    #[Route("/userssearch", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "search.users")]
    public function search(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UsersSearch");
    }

    // preview
    #[Route("/userspreview", methods: ["GET", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "preview.users")]
    public function preview(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "UsersPreview", null, false);
    }
}
