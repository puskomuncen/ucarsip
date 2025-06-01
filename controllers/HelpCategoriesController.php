<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

class HelpCategoriesController extends ControllerBase
{
    // list
    #[Route("/helpcategorieslist[/{Category_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "list.help_categories")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpCategoriesList");
    }

    // add
    #[Route("/helpcategoriesadd[/{Category_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "add.help_categories")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpCategoriesAdd");
    }

    // view
    #[Route("/helpcategoriesview[/{Category_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "view.help_categories")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpCategoriesView");
    }

    // edit
    #[Route("/helpcategoriesedit[/{Category_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "edit.help_categories")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpCategoriesEdit");
    }

    // delete
    #[Route("/helpcategoriesdelete[/{Category_ID}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "delete.help_categories")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "HelpCategoriesDelete");
    }
}
