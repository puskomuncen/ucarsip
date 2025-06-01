<?php

namespace PHPMaker2025\ucarsip;

use Slim\Routing\RouteContext;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Permission middleware
 */
class PermissionMiddleware
{
    /**
     * Invoke
     *
     * @param Request $request Request
     * @param RequestHandler $handler Request handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Request
        $GLOBALS["Request"] = $request;

        // Get page action and table
        [$pageAction, $table, $name] = explode(".", RouteName()) + [null, null, null]; // Make sure at least two elements
        if ($pageAction == "calendar" && $name !== null) { // Set up page action and table correctly for Calendar add/view/edit/delete routes (calendar.action.table)
            $pageAction = $table;
            $table = $name;
        }

        // Security
        $security = Security();

        // Current table
        if ($table != "") {
            $GLOBALS["Table"] = Container($table);
        }

        // Login
        if (IsLoggingIn2FA()) {
            if ($user = CurrentUser()) {
                $profile = Profile()->setUserName($user->getUserIdentifier());
                $security->loginUser($profile); // Login user with user name only
            } else { // Token was deauthenticated after trying to refresh it
                return $this->redirect("logout");
            }
        } elseif (
            !$security->isLoggedIn()
            && !IsPasswordReset()
            && !IsPasswordExpired()
            && !IsLoggingIn()
        ) {
            $security->login();
        }

        // Check permission
        if ($table != "") { // Table level
            $security->loadTablePermissions($table);
            if (
                $pageAction == Config("VIEW_ACTION") && !$security->canView()
                || in_array($pageAction, [Config("EDIT_ACTION"), Config("UPDATE_ACTION")]) && !$security->canEdit()
                || $pageAction == Config("ADD_ACTION") && !$security->canAdd()
                || $pageAction == Config("DELETE_ACTION") && !$security->canDelete()
                || in_array($pageAction, [Config("SEARCH_ACTION"), Config("QUERY_ACTION")]) && !$security->canSearch()
            ) {
                $this->setFailureMessage();
                if ($security->canList()) { // Back to list
                    $pageAction = Config("LIST_ACTION");
                    $routeUrl = $GLOBALS["Table"]->getListUrl();
                    return $this->redirect($pageAction . "." . $table);
                } else {
                    return $this->redirect("login");
                }
            } elseif (
                $pageAction == Config("LIST_ACTION") && !$security->canList() || // List Permission
                in_array($pageAction, [
                    Config("CUSTOM_REPORT_ACTION"),
                    Config("SUMMARY_REPORT_ACTION"),
                    Config("CROSSTAB_REPORT_ACTION"),
                    Config("DASHBOARD_REPORT_ACTION"),
                    Config("CALENDAR_REPORT_ACTION")
                ]) && !$security->canList()
            ) { // No permission
                $this->setFailureMessage();
                return $this->redirect();
            }
        } else { // Others
            if ($pageAction == "changepassword") { // Change password
                if (!IsPasswordReset() && !IsPasswordExpired()) {
                    if (!$security->isLoggedIn() || $security->isSysAdmin()) {
                        return $this->redirect();
                    }
                }
            } elseif ($pageAction == "personaldata") { // Personal data
                if (!$security->isLoggedIn() || $security->isSysAdmin()) {
                    $this->setFailureMessage();
                    return $this->redirect();
                }
            } elseif ($pageAction == "userpriv") { // User priv
                $table = "userlevels";
                $pageAction = Config("LIST_ACTION");
                $routeUrl = Container($table)->getListUrl();
                $security->loadTablePermissions($table);
                if (!$security->isLoggedIn() || !$security->canGrant()) {
                    $this->setFailureMessage();
                    return $this->redirect($pageAction . "." . $table);
                }
            }
        }

        // Handle request
        return $handler->handle($request);
    }

    /**
     * Set failure message (no permission)
     *
     * @return void
     */
    protected function setFailureMessage(): void
    {
        FlashBag()->add("failure", DeniedMessage());
    }

    /**
     * Redirect
     *
     * @param string $routeName Route name
     * @return Response
     */
    protected function redirect(string $routeName = "login"): Response
    {
        $response = ResponseFactory()->createResponse(); // Create response
        if (
            IsJsonResponse() || // JSON response expected
            IsModal() && // Modal
            !($routeName == "login" && Config("USE_MODAL_LOGIN")) // Not modal login
        ) {
            return $response->withJson(["url" => UrlFor($routeName)]);
        }
        return $response->withHeader("Location", UrlFor($routeName))->withStatus(Config("REDIRECT_STATUS_CODE"));
    }
}
