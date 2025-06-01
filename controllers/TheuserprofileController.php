<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

class TheuserprofileController extends ControllerBase
{
    // list
    #[Route("/theuserprofilelist[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "list.theuserprofile")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TheuserprofileList");
    }

    // add
    #[Route("/theuserprofileadd[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "add.theuserprofile")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TheuserprofileAdd");
    }

    // view
    #[Route("/theuserprofileview[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "view.theuserprofile")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TheuserprofileView");
    }

    // edit
    #[Route("/theuserprofileedit[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "edit.theuserprofile")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TheuserprofileEdit");
    }

    // delete
    #[Route("/theuserprofiledelete[/{_UserID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "delete.theuserprofile")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TheuserprofileDelete");
    }

    // search
    #[Route("/theuserprofilesearch", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "search.theuserprofile")]
    public function search(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "TheuserprofileSearch");
    }
}
