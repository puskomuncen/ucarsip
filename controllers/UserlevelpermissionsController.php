<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;

class UserlevelpermissionsController extends ControllerBase
{
    // list
    #[Route("/userlevelpermissionslist[/{keys:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "list.userlevelpermissions")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $this->getKeyParams($args), "UserlevelpermissionsList");
    }

    // add
    #[Route("/userlevelpermissionsadd[/{keys:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "add.userlevelpermissions")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $this->getKeyParams($args), "UserlevelpermissionsAdd");
    }

    // view
    #[Route("/userlevelpermissionsview[/{keys:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "view.userlevelpermissions")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $this->getKeyParams($args), "UserlevelpermissionsView");
    }

    // edit
    #[Route("/userlevelpermissionsedit[/{keys:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "edit.userlevelpermissions")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $this->getKeyParams($args), "UserlevelpermissionsEdit");
    }

    // delete
    #[Route("/userlevelpermissionsdelete[/{keys:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "delete.userlevelpermissions")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $this->getKeyParams($args), "UserlevelpermissionsDelete");
    }

    // Get keys as associative array
    protected function getKeyParams($args)
    {
        global $RouteValues;
        if (array_key_exists("keys", $args)) {
            $sep = Container("userlevelpermissions")->RouteCompositeKeySeparator;
            $keys = explode($sep, $args["keys"]);
            if (count($keys) == 2) {
                $keyArgs = array_combine(["UserLevelID","_TableName"], $keys);
                $RouteValues = array_merge(Route(), $keyArgs);
                $args = array_merge($args, $keyArgs);
            }
        }
        return $args;
    }
}
