<?php

/**
 * PHPMaker constants
 */

namespace PHPMaker2025\ucarsip;

/**
 * Constants
 */
define(__NAMESPACE__ . "\PROJECT_NAMESPACE", __NAMESPACE__ . "\\");

// System
define(PROJECT_NAMESPACE . "IS_WINDOWS", strtolower(substr(PHP_OS, 0, 3)) === "win"); // Is Windows OS
define(PROJECT_NAMESPACE . "PATH_DELIMITER", IS_WINDOWS ? "\\" : "/"); // Physical path delimiter

// Product version
define(PROJECT_NAMESPACE . "PRODUCT_VERSION", "25.10.0");

// Project
define(PROJECT_NAMESPACE . "PROJECT_NAME", "ucarsip"); // Project name
define(PROJECT_NAMESPACE . "PROJECT_ID", "{CB54660F-9AFA-48FD-B6BB-4648712A04D7}"); // Project ID

// Character encoding (utf-8)
define(PROJECT_NAMESPACE . "PROJECT_CHARSET", "utf-8"); // Charset
define(PROJECT_NAMESPACE . "EMAIL_CHARSET", "utf-8"); // Charset
define(PROJECT_NAMESPACE . "PROJECT_ENCODING", "UTF-8"); // Character encoding (uppercase)

// Cookie
define(PROJECT_NAMESPACE . "COOKIE_USER_PROFILE", "UserProfile_"); // User profile

// Session
define(PROJECT_NAMESPACE . "SESSION_STATUS", PROJECT_NAME . "_Status"); // Login status
define(PROJECT_NAMESPACE . "SESSION_USER_PROFILE", SESSION_STATUS . "_UserProfile"); // User profile
define(PROJECT_NAMESPACE . "SESSION_USER_NAME", SESSION_STATUS . "_UserName"); // User name
define(PROJECT_NAMESPACE . "SESSION_USER_ID", SESSION_STATUS . "_UserID"); // User ID
define(PROJECT_NAMESPACE . "SESSION_USER_PRIMARY_KEY", SESSION_STATUS . "_UserPrimaryKey"); // User primary key
define(PROJECT_NAMESPACE . "SESSION_USER_PROFILE_USER_NAME", SESSION_USER_PROFILE . "_UserName");
define(PROJECT_NAMESPACE . "SESSION_USER_PROFILE_PASSWORD", SESSION_USER_PROFILE . "_Password");
define(PROJECT_NAMESPACE . "SESSION_USER_PROFILE_REMEMBER_ME", SESSION_USER_PROFILE . "_RememberMe");
define(PROJECT_NAMESPACE . "SESSION_USER_PROFILE_SECRET", SESSION_USER_PROFILE . "_Secret");
define(PROJECT_NAMESPACE . "SESSION_USER_PROFILE_SECURITY_CODE", SESSION_USER_PROFILE . "_SecurityCode");
define(PROJECT_NAMESPACE . "SESSION_TWO_FACTOR_AUTHENTICATION_TYPE", PROJECT_NAME . "_TwoFactorAuthenticationType");
define(PROJECT_NAMESPACE . "SESSION_USER_LEVEL_ID", SESSION_STATUS . "_UserLevel"); // User Level ID
define(PROJECT_NAMESPACE . "SESSION_USER_LEVEL_LIST", SESSION_STATUS . "_UserLevelList"); // User Level List
define(PROJECT_NAMESPACE . "SESSION_USER_LEVEL_LIST_LOADED", SESSION_STATUS . "_UserLevelListLoaded"); // User Level List Loaded
define(PROJECT_NAMESPACE . "SESSION_USER_LEVEL", SESSION_STATUS . "_UserLevelValue"); // User Level
define(PROJECT_NAMESPACE . "SESSION_PARENT_USER_ID", SESSION_STATUS . "_ParentUserId"); // Parent User ID
define(PROJECT_NAMESPACE . "SESSION_SYS_ADMIN", PROJECT_NAME . "_SysAdmin"); // System admin
define(PROJECT_NAMESPACE . "SESSION_PROJECT_ID", PROJECT_NAME . "_ProjectId"); // User Level project ID
define(PROJECT_NAMESPACE . "SESSION_USER_LEVELS", PROJECT_NAME . "_UserLevels"); // User Levels (array)
define(PROJECT_NAMESPACE . "SESSION_USER_LEVEL_PRIVS", PROJECT_NAME . "_UserLevelPrivs"); // User Level privileges (array)
define(PROJECT_NAMESPACE . "SESSION_USER_LEVEL_MSG", PROJECT_NAME . "_UserLevelMessage"); // User Level Message
define(PROJECT_NAMESPACE . "SESSION_INLINE_MODE", PROJECT_NAME . "_InlineMode"); // Inline mode
define(PROJECT_NAMESPACE . "SESSION_BREADCRUMB", PROJECT_NAME . "_Breadcrumb"); // Breadcrumb
define(PROJECT_NAMESPACE . "SESSION_HISTORY", PROJECT_NAME . "_History"); // History (Breadcrumb)
define(PROJECT_NAMESPACE . "SESSION_TEMP_IMAGES", PROJECT_NAME . "_TempImages"); // Temp images
define(PROJECT_NAMESPACE . "SESSION_CAPTCHA_CODE", PROJECT_NAME . "_Captcha"); // Captcha code
define(PROJECT_NAMESPACE . "SESSION_LANGUAGE_ID", PROJECT_NAME . "_LanguageId"); // Language ID
define(PROJECT_NAMESPACE . "SESSION_MYSQL_ENGINES", PROJECT_NAME . "_MySqlEngines"); // MySQL table engines
define(PROJECT_NAMESPACE . "SESSION_ACTIVE_USERS", PROJECT_NAME . "_ActiveUsers"); // Active users

// Begin of modification Permission Access for Export To Feature, by Masino Sinaga, May 5, 2012
define(PROJECT_NAMESPACE . "MS_ENABLE_PERMISSION_FOR_EXPORT_DATA", true); // Enable this to allow dynamic permission of export data to media below // OK
define(PROJECT_NAMESPACE . "MS_SHOW_TERMS_CONDITIONS_ON_FOOTER", true); // Terms of Condition link
define(PROJECT_NAMESPACE . "MS_SHOW_ABOUT_US_ON_FOOTER", true); // About Us link
define(PROJECT_NAMESPACE . "MS_SHOW_EMPTY_TABLE_ON_LIST_PAGE", true); // Whether to show empty table in the List page if no records found

// Begin of modification Always Compare Root URL, by Masino Sinaga, October 18, 2015
define(PROJECT_NAMESPACE . "MS_ALWAYS_COMPARE_ROOT_URL", true);
define(PROJECT_NAMESPACE . "MS_OTHER_COMPARED_ROOT_URL", "http://www.mydomain.com/dev");
// End of modification Always Compare Root URL, by Masino Sinaga, October 18, 2015
define(PROJECT_NAMESPACE . "MS_TABLE_MAXIMUM_SELECTED_RECORDS", 50); // Maximum selected records per page

// Begin of modification Enable Help Online, by Masino Sinaga, September 19, 2014
define(PROJECT_NAMESPACE . "MS_SHOW_HELP_ONLINE", true); 
// End of modification Enable Help Online, by Masino Sinaga, September 19, 2014

// Begin of modification by Masino Sinaga, for saving the registered, last login, and last logout date time, November 6, 2011
define(PROJECT_NAMESPACE . "MS_USER_PROFILE_LAST_LOGIN_DATE_TIME", "LastLoginDateTime");
define(PROJECT_NAMESPACE . "MS_USER_PROFILE_LAST_LOGOUT_DATE_TIME", "LastLogoutDateTime");
// End of modification by Masino Sinaga, for saving the registered, last login, and last logout date time, November 6, 2011
define(PROJECT_NAMESPACE . "MS_USER_REGISTRATION", FALSE);
define(PROJECT_NAMESPACE . "MS_SHOW_PLAIN_TEXT_PASSWORD", false);
define(PROJECT_NAMESPACE . "MS_TERMS_AND_CONDITION_CHECKBOX_ON_CHANGEPWD_PAGE", true);
define(PROJECT_NAMESPACE . "MS_ENABLE_PASSWORD_POLICY", true);
define(PROJECT_NAMESPACE . "MS_PASSWORD_MINIMUM_LENGTH", 8); // default minimum 8 characters
define(PROJECT_NAMESPACE . "MS_PASSWORD_MAXIMUM_LENGTH", 20); // default maximum 20 characters
define(PROJECT_NAMESPACE . "MS_PASSWORD_MUST_COMPLY_WITH_MIN_LENGTH", true);
define(PROJECT_NAMESPACE . "MS_PASSWORD_MUST_COMPLY_WITH_MAX_LENGTH", true);
define(PROJECT_NAMESPACE . "MS_PASSWORD_MUST_CONTAIN_AT_LEAST_ONE_NUMERIC", true);
define(PROJECT_NAMESPACE . "MS_PASSWORD_MUST_CONTAIN_AT_LEAST_ONE_LOWERCASE", true);
define(PROJECT_NAMESPACE . "MS_PASSWORD_MUST_CONTAIN_AT_LEAST_ONE_UPPERCASE", true);
define(PROJECT_NAMESPACE . "MS_PASSWORD_MUST_CONTAIN_AT_LEAST_ONE_SYMBOL", false);
define(PROJECT_NAMESPACE . "MS_PASSWORD_MUST_DIFFERENT_OLD_AND_NEW", true);
define(PROJECT_NAMESPACE . "SESSION_USER_PROFILE_USER_EMAIL", SESSION_USER_PROFILE . "_Email"); // Reset based on both "Username" AND "Email" fields. Modified by Masino Sinaga, August 30, 2016
define(PROJECT_NAMESPACE . "MS_SEND_PASSWORD_DIRECTLY_IF_NOT_ENCRYPTED", false);
// Begin of modification Customizing Forgot Password Page, by Masino Sinaga, May 3, 2012
define(PROJECT_NAMESPACE . "MS_KNOWN_FIELD_OPTIONS", "EmailAndUsername"); // available: Email, Username, EmailOrUsername, and EmailAndUsername, modified by Masino Sinaga, April 21, 2014

// Begin of modification Displaying Breadcrumb Links, by Masino Sinaga, October 5, 2013
define(PROJECT_NAMESPACE . "MS_SHOW_PHPMAKER_BREADCRUMBLINKS", true);
define(PROJECT_NAMESPACE . "MS_SHOW_MASINO_BREADCRUMBLINKS", false);
define(PROJECT_NAMESPACE . "MS_BREADCRUMBLINKS_NO_LINKS", false);
define(PROJECT_NAMESPACE . "MS_BREADCRUMBLINKS_DIVIDER", "/"); // in addition to "/" character, you may also use this: "»"
// End of modification Displaying Breadcrumb Links, by Masino Sinaga, October 5, 2013
// Begin of modification Breadcrumb Links SP, October 29, 2013
define(PROJECT_NAMESPACE . "MS_BREADCRUMB_LINKS_ADD_SP", "breadcrumblinksaddsp");
define(PROJECT_NAMESPACE . "MS_BREADCRUMB_LINKS_CHECK_SP", "breadcrumblinkschecksp");
define(PROJECT_NAMESPACE . "MS_BREADCRUMB_LINKS_MOVE_SP", "breadcrumblinksmovesp");
define(PROJECT_NAMESPACE . "MS_BREADCRUMB_LINKS_DELETE_SP", "breadcrumblinksdeletesp");
// End of modification Breadcrumb Links SP, October 29, 2013
?>
