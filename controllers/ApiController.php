<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;
use Symfony\Component\Routing\Attribute\Route;
use Illuminate\Support\Collection;
use Exception;
use stdClass;

/**
 * API controller
 */
#[Route("/api")]
class ApiController extends AbstractController
{
    protected ?string $pageName;

    /**
     * Process page
     */
    public function processPage(Request $request, Response &$response, array $args)
    {
        global $RenderingView;
        if ($this->pageName) {
            $pageClass = PROJECT_NAMESPACE . $this->pageName;
            if (class_exists($pageClass)) {
                $page = $this->container->make($pageClass);
                $page->run();
                // Render page if not terminated
                if (!$page->isTerminated()) {
                    $view = $this->container->get("app.view");
                    $RenderingView = true;
                    $layout = property_exists($page, "MultiColumnLayout") && $page->MultiColumnLayout == "cards" ? "Cards" : "Table";
                    $template = $page->TableVar . $layout . ".php"; // View
                    $GLOBALS["Title"] ??= $page->Title; // Title
                    try {
                        $response = $view->render($response, $template, $GLOBALS);
                    } finally {
                        $RenderingView = false;
                        $page->terminate(true); // Terminate page and clean up
                    }
                }
            }
        }
        return $response;
    }

    /**
     * login
     */
    #[Route("/login", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => JwtMiddleware::class], name: "api.login")]
    public function login(Request $request, Response &$response, array $args): Response
    {
        $username = $request->getParam(Config("API_LOGIN_USERNAME"));
        $password = $request->getParam(Config("API_LOGIN_PASSWORD"));
        $expire = $request->getParam(Config("API_LOGIN_EXPIRE"));
        $permission = $request->getParam(Config("API_LOGIN_PERMISSION"));
        // Validate expire
        if ($expire && (!is_numeric($expire) || ParseInteger($expire) <= 0)) {
            return $response->withJson(["error" => $this->language->phrase("IncorrectInteger", true) . ": " . Config("API_LOGIN_EXPIRE")]); // Incorrect expire
        }
        // Validate permission
		// Begin of Enable Permission of Export Data by Masino Sinaga, September 12, 2023
		if (MS_ENABLE_PERMISSION_FOR_EXPORT_DATA == true) {
			if ($permission && (!is_numeric($permission) || ParseInteger($permission) <= 0 || ParseInteger($permission) > Allow::ADMIN->value)) {
				return $response->withJson(["error" => $Language->phrase("IncorrectInteger") . ": " . Config("API_LOGIN_PERMISSION")]); // Incorrect expire
			}
		} else {
			if ($permission && (!is_numeric($permission) || ParseInteger($permission) <= 0 || ParseInteger($permission) > Allow::ADMIN->value)) {
				return $response->withJson(["error" => $this->language->phrase("IncorrectInteger", true) . ": " . Config("API_LOGIN_PERMISSION")]); // Incorrect expire
			}
		} // End of Enable Permission of Export Data by Masino Sinaga, September 12, 2023
        return $this->security->validateUser($username, $password)
            ? $response
            : $response->withStatus(401); // Not authorized
    }

    /**
     * list
     */
    #[Route("/list/{table}[/{params:.*}]", methods: ["GET", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.list")]
    public function list(Request $request, Response &$response, array $args): Response
    {
        $table = $args["table"] ?? Get(Config("API_OBJECT_NAME"));
        if ($table) {
            $this->pageName = $this->container->get($table)?->getApiPageName("list");
        }
        return $this->processPage($request, $response, $args);
    }

    /**
     * view
     */
    #[Route("/view/{table}[/{params:.*}]", methods: ["GET", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.view")]
    public function view(Request $request, Response &$response, array $args): Response
    {
        $table = $args["table"] ?? Get(Config("API_OBJECT_NAME"));
        if ($table) {
            $this->pageName = $this->container->get($table)?->getApiPageName("view");
        }
        return $this->processPage($request, $response, $args);
    }

    /**
     * add
     */
    #[Route("/add/{table}[/{params:.*}]", methods: ["POST", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.add")]
    public function add(Request $request, Response &$response, array $args): Response
    {
        $table = $args["table"] ?? Post(Config("API_OBJECT_NAME"));
        if ($table) {
            $this->pageName = $this->container->get($table)?->getApiPageName("add");
        }
        return $this->processPage($request, $response, $args);
    }

    /**
     * edit
     */
    #[Route("/edit/{table}[/{params:.*}]", methods: ["POST", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.edit")]
    public function edit(Request $request, Response &$response, array $args): Response
    {
        $table = $args["table"] ?? Post(Config("API_OBJECT_NAME"));
        if ($table) {
            $this->pageName = $this->container->get($table)?->getApiPageName("edit");
        }
        return $this->processPage($request, $response, $args);
    }

    /**
     * delete
     */
    #[Route("/delete/{table}[/{params:.*}]", methods: ["GET", "POST", "DELETE", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.delete")]
    public function delete(Request $request, Response &$response, array $args): Response
    {
        $table = $args["table"] ?? Param(Config("API_OBJECT_NAME"));
        if ($table) {
            $this->pageName = $this->container->get($table)?->getApiPageName("delete");
        }
        return $this->processPage($request, $response, $args);
    }

    /**
     * register
     */
    #[Route("/register", methods: ["POST", "OPTIONS"], defaults: ["middlewares" => ApiPermissionMiddleware::class], name: "api.register")]
    public function register(Request $request, Response &$response, array $args): Response
    {
        $this->pageName = "Register";
        return $this->processPage($request, $response, $args);
    }

    /**
     * file
     * /api/file/{table}/{field}/{key}
     * /api/file/{table}/{path}
     * $args["param"] can be {field} or {path}
     */
    #[Route("/file/{table}/{param}[/{key:.*}]", methods: ["GET", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.file")]
    public function file(Request $request, Response &$response, array $args): Response
    {
        $fileViewer = $this->container->make(FileViewer::class);
        return $fileViewer();
    }

    /**
     * export
     * /api/export/{type}/{table}/{key}
     * /api/export/{id}
     * /api/export/search
     * $args["param"] can be {type} or {id} or "search"
     */
    #[Route("/export[/{param}[/{table}[/{key:.*}]]]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.export")]
    public function export(Request $request, Response &$response, array $args): Response
    {
        $exportHandler = $this->container->make(ExportHandler::class, ["request" => $request, "response" => $response]);
        return $exportHandler();
    }

    /**
     * upload
     */
    #[Route("/upload", methods: ["POST", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.upload")]
    public function upload(Request $request, Response &$response, array $args): Response
    {
        $upload = new HttpUpload(null, $request);
        return $response->withJson($upload->getUploadedFiles());
    }

    /**
     * jupload
     */
    #[Route("/jupload", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => ApiPermissionMiddleware::class], name: "api.jupload")]
    public function jupload(Request $request, Response &$response, array $args): Response
    {
        $uploadHandler = $this->container->get(FileUploadHandler::class);
        return $uploadHandler($request, $response);
    }

    /**
     * session
     */
    #[Route("/session", methods: ["GET", "OPTIONS"], defaults: ["middlewares" => ApiPermissionMiddleware::class], name: "api.session")]
    public function session(Request $request, Response &$response, array $args): Response
    {
        $sessionHandler = $this->container->make(SessionHandler::class);
        return $sessionHandler();
    }

    /**
     * lookup
     */
    #[Route("/lookup[/{params:.*}]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.lookup")]
    public function lookup(Request $request, Response &$response, array $args): Response
    {
        if ($request->getContentType() == "application/json") { // Multiple requests
            $req = $request->getParsedBody();
            if (is_array($req)) { // Multiple requests
                $out = [];
                foreach ($req as $ar) {
                    if (is_string($ar)) { // Request is QueryString
                        parse_str($ar, $ar);
                    }
                    $object = $ar[Config("API_LOOKUP_PAGE")];
                    $fieldName = $ar[Config("API_FIELD_NAME")];
                    $res = [Config("API_LOOKUP_PAGE") => $object, Config("API_FIELD_NAME") => $fieldName];
                    $page = Container($object); // Don't use $this->container
                    $lookupField = $page?->Fields[$fieldName] ?? null;
                    if ($lookupField) {
                        $lookup = $lookupField->Lookup;
                        if ($lookup) {
                            $tbl = $lookup->getTable();
                            if ($tbl) {
                                $this->security->loadTablePermissions($tbl->TableVar);
                                if ($this->security->canLookup()) {
                                    $res = array_merge($res, $page->lookup($ar, false));
                                } else {
                                    $res = array_merge($res, ["result" => $this->language->phrase("401", true)]);
                                }
                            }
                        }
                    }
                    if ($fieldName) {
                        $out[] = $res;
                    }
                }
                $response = $response->withJson($out);
            }
        } else { // Single request
            $page = $request->getParam(Config("API_LOOKUP_PAGE"));
            Container($page)?->lookup($request->getParams()); // Don't use $this->container
        }
        return $response;
    }

    /**
     * chart
     */
    #[Route("/chart[/{params:.*}]", methods: ["GET", "OPTIONS"], defaults: ["middlewares" => ApiPermissionMiddleware::class], name: "api.chart")]
    public function exportchart(Request $request, Response &$response, array $args): Response
    {
        $chartExporter = $this->container->make(ChartExporter::class);
        return $chartExporter();
    }

    /**
     * permissions
     */
    #[Route("/permissions/{level}", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.permissions")]
    public function permissions(Request $request, Response &$response, array $args): Response
    {
        global $USER_LEVELS, $USER_LEVEL_TABLES;
        $userLevel = $args["level"] ?? null;
        if ($userLevel === null) {
            return $response;
        }

        // Set up security
        $this->security->setupUserLevel(); // Get all User Level info
        $ar = $USER_LEVEL_TABLES;

        // Get permissions
        if (IsGet()) {
            // Check user level
            $userLevels = [-2]; // Default anonymous
            if ($this->security->isLoggedIn()) {
                if ($this->security->isSysAdmin() && is_numeric($userLevel) && !SameString($userLevel, "-1")) { // Get permissions for user level
                    if ($this->security->userLevelIDExists($userLevel)) {
                        $userLevels = [$userLevel];
                    }
                } else {
                    $userLevel = $this->security->CurrentUserLevelID;
                    $userLevels = $this->security->UserLevelIDs;
                }
            }
            $userLevel = $userLevels[0];
            $privs = [];
            $cnt = count($ar);
            for ($i = 0; $i < $cnt; $i++) {
                $projectId = $ar[$i][4];
                $tableVar = $ar[$i][1];
                $tableName = $ar[$i][0];
                $allowed = $ar[$i][3];
                if ($allowed) {
                    $priv = 0;
                    foreach ($userLevels as $level) {
                        $priv |= $this->security->getUserLevelPrivEx($projectId . $tableName, $level);
                    }
                    $privs[$tableVar] = $priv;
                }
            }
            $res = ["userlevel" => $userLevel, "permissions" => $privs];
            $response = $response->withJson($res);

        // Update permissions
        } elseif (IsPost() && $this->security->isSysAdmin()) { // System admin only
            $json = $request->getContentType() == "application/json" ? $request->getParsedBody() : [];

            // Validate user level
            if (!is_numeric($userLevel) || SameString($userLevel, "-1") || !Collection::make($USER_LEVELS)->first(fn ($level) => SameString($level[0], $userLevel))) {
                $res = ["userlevel" => $userLevel, "permissions" => $json, "success" => false];
                $response = $response->withJson($res);
            }

            // Validate table names / permissions
            $newPrivs = [];
            $outPrivs = [];
            foreach ($json as $tableName => $permission) {
                $table = Collection::make($ar)->first(fn ($privs) => $privs[0] == $tableName || $privs[1] == $tableName);
                // Begin of Enable Permission of Export Data by Masino Sinaga, September 12, 2023
				if (MS_ENABLE_PERMISSION_FOR_EXPORT_DATA == true) {
					if (!$table || !is_numeric($permission) || intval($permission) < 0 || intval($permission) > Allow::ADMIN->value) {
						$res = ["userlevel" => $userLevel, "permissions" => $json, "success" => false];
						$response = $response->withJson($res);
					}
				} else {
					if (!$table || !is_numeric($permission) || intval($permission) < 0 || intval($permission) > Allow::ADMIN->value) {
						$res = ["userlevel" => $userLevel, "permissions" => $json, "success" => false];
						$response = $response->withJson($res);
					}
				}
				// End of Enable Permission of Export Data by Masino Sinaga, September 12, 2023
				// Begin of Enable Permission of Export Data by Masino Sinaga, September 12, 2023
				if (MS_ENABLE_PERMISSION_FOR_EXPORT_DATA == true) {
					$permission = intval($permission) & Allow::ADMIN->value;
				} else {
					$permission = intval($permission) & Allow::ADMIN->value;
				}
				// End of Enable Permission of Export Data by Masino Sinaga, September 12, 2023
                $newPrivs[$table[4] . $table[1]] = $permission;
                $outPrivs[$table[1]] = $permission;
            }

            // Update permissions for user level
            if (method_exists($this->security, "updatePermissions")) {
                $this->security->updatePermissions($userLevel, $newPrivs);
                $res = ["userlevel" => $userLevel, "permissions" => $outPrivs, "success" => true];
                $response = $response->withJson($res);
            } else {
                $res = ["userlevel" => $userLevel, "permissions" => $json, "success" => false];
                $response = $response->withJson($res);
            }
        }
        return $response;
    }

    /**
     * push
     */
    #[Route("/push/{action}", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => ApiPermissionMiddleware::class], name: "api.push")]
    public function push(Request $request, Response &$response, array $args): Response
    {
        $action = $args["action"] ?? null;
        $push = new PushNotification();
        match ($action) {
            Config("API_PUSH_NOTIFICATION_SUBSCRIBE") => $push->subscribe(),
            Config("API_PUSH_NOTIFICATION_SEND") => $push->send(),
            Config("API_PUSH_NOTIFICATION_DELETE") => $push->delete()
        };
        return $response;
    }

    /**
     * twofa
     */
    #[Route("/twofa/{action}/{user}[/{type}[/{parm}]]", methods: ["GET", "POST", "OPTIONS"], defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.twofa")]
    public function twofa(Request $request, Response &$response, array $args): Response
    {
        $action = $args["action"] ?? null; // secret/show/verify/reset/codes/newcodes/otp/enable/disable
        $user = $args["user"] ?? null; // user
        $authType = $args["type"] ?? null; // authtype
        $parm = $args["parm"] ?? null; // code/account
        $className = TwoFactorAuthenticationClass($authType);
        $auth = Container($className);
        try {
            if (!$auth->isValidUser($user)) {
                throw new Exception(sprintf($this->language->phrase("InvalidUsername", true), $user));
            }
            // twofa/otp/user/authtype/account
            if ($action == Config("API_2FA_SEND_OTP")) {
                if (!$auth instanceof SendOneTimePasswordInterface) {
                    throw new Exception("The authentication type '{$authType}' does not support sending one time password");
                }
                if (!$parm) {
                    throw new Exception("Missing account for the authentication type '{$authType}'");
                }
            } elseif (in_array($action, [Config("API_2FA_ENABLE"), Config("API_2FA_DISABLE")])) {
                if (IsLoggedIn()) {
                    // twofa/enable/user
                    if ($action == Config("API_2FA_ENABLE")) {
                        Profile()->set2FAEnabled(true)->saveToStorage();
                        return $response->withJson(["success" => true, "enabled" => true]);
                    // twofa/disable/user
                    } elseif ($action == Config("API_2FA_DISABLE")) {
                        Profile()->set2FAEnabled(false)->saveToStorage();
                        return $response->withJson(["success" => true, "disabled" => true]);
                    }
                }
                return $response->withJson(["success" => false]);
            }
            return match ($action) {
                // twofa/config/user (Get configuration)
                Config("API_2FA_CONFIG") => $response->withJson(["success" => true, "config" => Profile()->get2FAConfig()]),
                // twofa/show/user/authtype (Show QR Code URL or email/phone)
                Config("API_2FA_SHOW") => $response->withJson($auth->show($user)),
                // twofa/verify/user/authtype/code
                Config("API_2FA_VERIFY") => $response->withJson([...$auth->verify($user, $parm), "config" => Profile()->get2FAConfig()]),
                // twofa/reset/user[/authtype]
                Config("API_2FA_RESET") => $response->withJson([...($authType ? $auth->reset($user) : $auth->resetAll($user)), "config" => Profile()->get2FAConfig()]),
                // twofa/codes/user
                Config("API_2FA_BACKUP_CODES") => $response->withJson($auth->getBackupCodes($user)),
                // twofa/newcodes/user
                Config("API_2FA_NEW_BACKUP_CODES") => $response->withJson([...$auth->getNewBackupCodes($user), "config" => Profile()->get2FAConfig()]),
                // twofa/otp/user/authtype/account
                Config("API_2FA_SEND_OTP") => ($result = $auth->sendOneTimePassword($user, $parm)) === true
                    ? $response->withJson(["success" => true])
                    : throw new Exception($result)
            };
        } catch (Exception $e) {
            DebugBar()?->addThrowable($e); // Add exception to debug bar
            return $response->withJson(["success" => false, "error" => ["description" => $e->getMessage()]]);
        }
    }

    /**
     * metadata
     */
    #[Route("/metadata", methods: "GET", defaults: ["middlewares" => ApiPermissionMiddleware::class], name: "api.metadata")]
    public function metadata(Request $request, Response &$response, array $args): Response
    {
        return $response;
    }

    /**
     * chat
     */
    #[Route("/chat/{value:[01]}", methods: "GET", defaults: ["middlewares" => [ApiPermissionMiddleware::class, JwtMiddleware::class]], name: "api.chat")]
    public function chat(Request $request, Response &$response, array $args): Response
    {
        if (IsLoggedIn() && !IsSysAdmin()) {
            Profile()->setChatEnabled(ConvertToBool($args["value"]))->saveToStorage();
            return $response->withJson(["success" => true]);
        }
        return $response->withJson(["success" => false]);
    }

    /**
     * Other API actions
     */
    public function __invoke(Request $request, Response &$response, array $args): Response
    {
        if (count(Route()) == 0) {
            return $response;
        }

        // Handle custom actions (deprecated)
        $action = Route(1);
        if ($action && is_callable($GLOBALS["API_ACTIONS"][$action] ?? null)) {
            $func = $GLOBALS["API_ACTIONS"][$action];
            return $func($request, $response, $args);
        }
        return $response;
    }
}
