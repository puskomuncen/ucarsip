<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpNotFoundException;

/**
 * JWT middleware
 */
class JwtMiddleware implements MiddlewareInterface
{
    // Validate JWT token
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Set up request
        $GLOBALS["Request"] = $request;
        $route = GetRoute($request);

        // Return Not Found for non-existent route
        if (empty($route)) {
            throw new HttpNotFoundException($request);
        }
        $routeName = $route->getName();
        $security = Security();

        // Set up security from HTTP header if logged in
        if ($routeName != "api.login") {
            $token = preg_replace('/^Bearer\s+/', "", $request->getHeaderLine(Config("JWT.AUTH_HEADER"))); // Get bearer token from HTTP header
            if ($token) {
                $jwt = DecodeJwt($token);
                if (is_array($jwt) && count($jwt) > 0) {
                    if ((int)($jwt["userlevel"] ?? PHP_INT_MIN) >= AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID) { // Valid JWT token
                        $profile = Profile()->setUserName($jwt["username"] ?? "")
                            ->setUserID($jwt["userid"] ?? null)
                            ->setParentUserID($jwt["parentuserid"] ?? null)
                            ->setUserLevel($jwt["userlevel"] ?? AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID)
                            ->loadFromStorage();
                        $security->loginUser($profile); // Login user
                        $security->setUserPermissions($jwt["userPermission"] ?? 0); // Set user permissions
                    } else { // Invalid JWT token
                        $response = ResponseFactory()->createResponse();
                        $json = array_merge($jwt, ["success" => false, "version" => PRODUCT_VERSION]);
                        return $response->withJson($json);
                    }
                } else {
                    $response = ResponseFactory()->createResponse();
                    return $response->withStatus(401); // Not authorized
                }
            }
        }

        // Process request
        $response = $handler->handle($request);

        // Handle login
        if ($routeName == "api.login" && $response->getStatusCode() != "401") {
            if ($security->isLoggedIn()) {
                $expire = $request->getParam(Config("API_LOGIN_EXPIRE"));
                $permission = $request->getParam(Config("API_LOGIN_PERMISSION"));
                $expire = ParseInteger($expire ?? 0); // Get expire time in hours
                $permission = ParseInteger($permission ?? 0); // Get permission
                $minExpiry = $expire ? time() + $expire * 60 * 60 : 0;
                $jwt = $security->createJwt($minExpiry, $permission);
                $response = ResponseFactory()->createResponse();
                return $response->withJson(["JWT" => $jwt]); // Return JWT token
            } elseif (StartsString("application/json", $response->getHeaderLine("Content-type") ?? "")) { // JSON error response
                return $response;
            } else {
                return $response->withStatus(401); // Not authorized
            }
        }
        return $response;
    }
}
