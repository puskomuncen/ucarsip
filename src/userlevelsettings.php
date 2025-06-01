<?php

namespace PHPMaker2025\ucarsip;

/**
 * User levels
 *
 * @var array<int, string, string>
 * [0] int User level ID
 * [1] string User level name
 * [2] string User level hierarchy
 */
$USER_LEVELS = [["-2","Anonymous",""],
    ["0","Default",""]];

/**
 * User roles
 *
 * @var array<int, string>
 * [0] int User level ID
 * [1] string User role name
 */
$USER_ROLES = [["-1","ROLE_ADMIN"],
    ["0","ROLE_DEFAULT"]];

/**
 * User level permissions
 *
 * @var array<string, int, int>
 * [0] string Project ID + Table name
 * [1] int User level ID
 * [2] int Permissions
 */
// Begin of modification by Masino Sinaga, September 17, 2023
$USER_LEVEL_PRIVS_1 = [["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}announcement","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}announcement","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}help","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}help","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}help_categories","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}help_categories","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}home.php","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}home.php","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}languages","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}languages","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}settings","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}settings","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}theuserprofile","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}theuserprofile","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}userlevelpermissions","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}userlevelpermissions","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}userlevels","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}userlevels","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}users","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}users","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}dispositions","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}dispositions","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}letters","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}letters","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}tracks","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}tracks","0","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}units","-2","0"],
    ["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}units","0","0"]];
$USER_LEVEL_PRIVS_2 = [["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}breadcrumblinksaddsp","-1","8"],
					["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}breadcrumblinkschecksp","-1","8"],
					["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}breadcrumblinksdeletesp","-1","8"],
					["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}breadcrumblinksmovesp","-1","8"],
					["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}loadhelponline","-2","8"],
					["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}loadaboutus","-2","8"],
					["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}loadtermsconditions","-2","8"],
					["{CB54660F-9AFA-48FD-B6BB-4648712A04D7}printtermsconditions","-2","8"]];
$USER_LEVEL_PRIVS = array_merge($USER_LEVEL_PRIVS_1, $USER_LEVEL_PRIVS_2);
// End of modification by Masino Sinaga, September 17, 2023

/**
 * Tables
 *
 * @var array<string, string, string, bool, string>
 * [0] string Table name
 * [1] string Table variable name
 * [2] string Table caption
 * [3] bool Allowed for update (for userpriv.php)
 * [4] string Project ID
 * [5] string URL (for OthersController::index)
 */
// Begin of modification by Masino Sinaga, September 17, 2023
$USER_LEVEL_TABLES_1 = [["announcement","announcement","Announcement",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","announcementlist"],
    ["help","help","Help (Details)",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","helplist"],
    ["help_categories","help_categories","Help (Categories)",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","helpcategorieslist"],
    ["home.php","home","Home",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","home"],
    ["languages","languages","Languages",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","languageslist"],
    ["settings","settings","Application Settings",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","settingslist"],
    ["theuserprofile","theuserprofile","User Profile",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","theuserprofilelist"],
    ["userlevelpermissions","userlevelpermissions","User Level Permissions",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","userlevelpermissionslist"],
    ["userlevels","userlevels","User Levels",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","userlevelslist"],
    ["users","users","Users",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","userslist"],
    ["dispositions","dispositions","dispositions",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","dispositionslist"],
    ["letters","letters","letters",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","letterslist"],
    ["tracks","tracks","tracks",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","trackslist"],
    ["units","units","units",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","unitslist"]];
$USER_LEVEL_TABLES_2 = [["breadcrumblinksaddsp","breadcrumblinksaddsp","System - Breadcrumb Links - Add",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","breadcrumblinksaddsp"],
						["breadcrumblinkschecksp","breadcrumblinkschecksp","System - Breadcrumb Links - Check",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","breadcrumblinkschecksp"],
						["breadcrumblinksdeletesp","breadcrumblinksdeletesp","System - Breadcrumb Links - Delete",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","breadcrumblinksdeletesp"],
						["breadcrumblinksmovesp","breadcrumblinksmovesp","System - Breadcrumb Links - Move",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","breadcrumblinksmovesp"],
						["loadhelponline","loadhelponline","System - Load Help Online",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","loadhelponline"],
						["loadaboutus","loadaboutus","System - Load About Us",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","loadaboutus"],
						["loadtermsconditions","loadtermsconditions","System - Load Terms and Conditions",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","loadtermsconditions"],
						["printtermsconditions","printtermsconditions","System - Print Terms and Conditions",true,"{CB54660F-9AFA-48FD-B6BB-4648712A04D7}","printtermsconditions"]];
$USER_LEVEL_TABLES = array_merge($USER_LEVEL_TABLES_1, $USER_LEVEL_TABLES_2);
// End of modification by Masino Sinaga, September 17, 2023
