<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Routing\Attribute\Route;
use Slim\Routing\RouteContext;
use Slim\Exception\HttpUnauthorizedException;

/**
 * Class others controller
 */
class OthersController extends ControllerBase
{
    // captcha
    #[Route("/captcha[/{page}]", methods: ["GET", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "captcha")]
    public function captcha(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "Captcha");
    }

    // personaldata
    #[Route("/personaldata", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "personaldata")]
    public function personaldata(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "PersonalData");
    }

    // login
    #[Route("/login[/{action}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "login")]
    public function login(Request $request, Response &$response, array $args): Response
    {
        global $Error;
        $Error = FlashBag()->get("error")[0] ?? "";
        return $this->runPage($request, $response, $args, "Login");
    }

    // resetpassword
    #[Route("/resetpassword", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "resetpassword")]
    public function resetpassword(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "ResetPassword");
    }

    // changepassword
    #[Route("/changepassword", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "changepassword")]
    public function changepassword(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "ChangePassword");
    }

    // userpriv
    #[Route("/userpriv", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "userpriv")]
    public function userpriv(Request $request, Response &$response, array $args): Response
    {
        return $this->runPage($request, $response, $args, "Userpriv");
    }

    // Login check (for login link)
    #[Route("/login_check", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => AuthenticationMiddleware::class], name: "login_check")]
    public function loginCheck(Request $request, Response &$response, array $args): Response
    {
        return $response;
    }

    // Logout)
    #[Route("/logout", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => AuthenticationMiddleware::class], name: "logout")]
    public function logout(Request $request, Response &$response, array $args): Response
    {
        return $response;
    }

    // Swagger
    #[Route("/swagger/swagger", methods: "GET", name: "swagger")]
    public function swagger(Request $request, Response &$response, array $args): Response
    {
        $basePath = GetBasePath($request);
        $language = $this->container->get("app.language");
        $title = $language->phrase("ApiTitle");
        if (!$title || $title == "ApiTitle") {
            $title = "REST API"; // Default
        }
        $data = [
            "title" => $title,
            "version" => Config("API_VERSION"),
            "basePath" => $basePath
        ];
        $view = $this->container->get("app.view");
        return $view->render($response, "swagger.php", $data);
    }

    // Index
    #[Route("/[index]", methods: "GET", defaults: ["middlewares" => [PermissionMiddleware::class, AuthenticationMiddleware::class]], name: "index")]
    public function index(Request $request, Response &$response, array $args): Response
    {
        global $USER_LEVEL_TABLES;
        $url = "";
        foreach ($USER_LEVEL_TABLES as $t) {
            if ($t[0] == "home.php") { // Check default table
                if ($this->security->allowList($t[4] . $t[0])) {
                    $url = $t[5];
                    break;
                }
            } elseif ($url == "") {
                if ($t[5] && $this->security->allowList($t[4] . $t[0])) {
                    $url = $t[5];
                }
            }
        }
        if ($url === "" && !$this->security->isLoggedIn()) {
            $url = "login";
        }
        if ($url == "") {
            throw new HttpUnauthorizedException($request, DeniedMessage());
        }
        return $response->withHeader("Location", $url)->withStatus(Config("REDIRECT_STATUS_CODE"));
    }
}
