<?php

namespace PHPMaker2025\ucarsip;

use Slim\Routing\RouteContext;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Support\Collection;

/**
 * Permission middleware
 */
class ApiPermissionMiddleware
{
    // Invoke
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Set up request
        $GLOBALS["Request"] = $request;

        // Create Response
        $response = ResponseFactory()->createResponse();
        $action = Route(0);
        $table = "";
        $checkJwt = match ($action) {
            Config("API_LOOKUP_ACTION"), Config("API_SESSION_ACTION"), Config("API_EXPORT_CHART_ACTION"), Config("API_2FA_ACTION") => true,
            Config("API_JQUERY_UPLOAD_ACTION") => $request->isPost(),
            default => false,
        };

        // Validate JWT token
        if ($checkJwt) {
            $jwt = $request->getAttribute("JWT"); // Try get JWT from request attribute
            if ($jwt === null) {
                $token = preg_replace('/^Bearer\s+/', "", $request->getHeaderLine(Config("JWT.AUTH_HEADER"))); // Get bearer token from HTTP header
                if ($token) {
                    $jwt = DecodeJwt($token);
                }
            }
            if ((int)($jwt["userlevel"] ?? PHP_INT_MIN) < AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID) { // Invalid JWT token
                return $response->withStatus(401); // Not authorized
            }
        }

        // Actions for table
        $apiTableActions = [
            Config("API_EXPORT_ACTION"),
            Config("API_LIST_ACTION"),
            Config("API_VIEW_ACTION"),
            Config("API_ADD_ACTION"),
            Config("API_EDIT_ACTION"),
            Config("API_DELETE_ACTION"),
            Config("API_FILE_ACTION")
        ];
        if (in_array($action, $apiTableActions)) {
            $table = Route("table") ?? Param(Config("API_OBJECT_NAME")); // Get from route or Get/Post
        }

        // Security
        $security = Security();

        // Default no permission
        $authorized = false;

        // Check permission
        if (
            $checkJwt || // JWT token checked
            $action == Config("API_JQUERY_UPLOAD_ACTION") && $request->isGet() || // Get image during upload (GET)
            $action == Config("API_PERMISSIONS_ACTION") && $request->isGet() || // Permissions (GET)
            $action == Config("API_PERMISSIONS_ACTION") && $request->isPost() && $security->isAdmin() || // Permissions (POST)
            $action == Config("API_UPLOAD_ACTION") && $security->isLoggedIn() || // Upload
            $action == Config("API_REGISTER_ACTION") || // Register
            $action == Config("API_METADATA_ACTION") || // Metadata
            $action == Config("API_CHAT_ACTION") || // Chat
            array_key_exists($action, $GLOBALS["API_ACTIONS"]) // Custom actions (deprecated)
        ) {
            $authorized = true;
        } elseif (in_array($action, $apiTableActions) && $table != "") { // Table actions
            $security->loadTablePermissions($table);
            $authorized = $action == Config("API_LIST_ACTION") && $security->canList()
                || $action == Config("API_EXPORT_ACTION") && $security->canExport()
                || $action == Config("API_VIEW_ACTION") && $security->canView()
                || $action == Config("API_ADD_ACTION") && $security->canAdd()
                || $action == Config("API_EDIT_ACTION") && $security->canEdit()
                || $action == Config("API_DELETE_ACTION") && $security->canDelete()
                || $action == Config("API_FILE_ACTION") && ($security->canList() || $security->canView());
        } elseif ($action == Config("API_EXPORT_ACTION") && IsEmpty($table)) { // Get exported file
            $authorized = true; // Check table permission in ExportHandler.php
        } elseif ($action == Config("API_LOOKUP_ACTION")) { // Lookup
            $canLookup = function ($params) use ($security) {
                $object = $params[Config("API_LOOKUP_PAGE")]; // Get lookup page
                $fieldName = $params[Config("API_FIELD_NAME")]; // Get field name
                $lookupField = Container($object)?->Fields[$fieldName] ?? null;
                if ($lookupField) {
                    $lookup = $lookupField->Lookup;
                    if ($lookup) {
                        $tbl = $lookup->getTable();
                        if ($tbl) {
                            $security->loadTablePermissions($tbl->TableVar);
                            return $security->canLookup();
                        }
                    }
                }
            };
            if ($request->getContentType() == "application/json") { // Multiple lookup requests in JSON
                $parsedBody = $request->getParsedBody();
                if (is_array($parsedBody)) {
                    $authorized = Collection::make($parsedBody)->contains($canLookup);
                }
            } else { // Single lookup request
                $authorized = $canLookup($request->getParams());
            }
        } elseif ($action == Config("API_PUSH_NOTIFICATION_ACTION")) { // Push notification
            $actn = Route("action");
            if (in_array($actn, [Config("API_PUSH_NOTIFICATION_SUBSCRIBE"), Config("API_PUSH_NOTIFICATION_DELETE")])) {
                $authorized = Config("PUSH_ANONYMOUS") || $security->isLoggedIn();
            } elseif ($actn == Config("API_PUSH_NOTIFICATION_SEND")) {
                $security->loadTablePermissions(Config("SUBSCRIPTION_TABLE"));
                $authorized = $security->canPush();
            }
        } elseif ($action == Config("API_2FA_ACTION")) { // Two factor authentication
            $actn = Route("action");
            if (in_array($actn, [Config("API_2FA_CONFIG"), Config("API_2FA_SHOW"), Config("API_2FA_VERIFY"), Config("API_2FA_SEND_OTP")])) {
                $authorized = $security->isLoggingIn2FA() || $security->isLoggedIn();
            } elseif (in_array($actn, [Config("API_2FA_BACKUP_CODES"), Config("API_2FA_NEW_BACKUP_CODES"), Config("API_2FA_RESET"), Config("API_2FA_ENABLE"), Config("API_2FA_DISABLE")])) {
                $authorized = $security->isLoggedIn();
            }
        }
        if (!$authorized) {
            return $response->withStatus(401); // Not authorized
        }

        // Handle request
        return $handler->handle($request);
    }
}
