<?php

namespace PHPMaker2025\ucarsip;

// Navbar menu
$topMenu = new Menu("navbar", true, true);
echo $topMenu->toScript();

// Sidebar menu
$sideMenu = new Menu("menu", true, false);
$sideMenu->addMenuItem(4, "mi_home", $Language->menuPhrase("4", "MenuText"), "home", -1, substr("mi_home", strpos("mi_home", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}home.php'), false, false, "fa-home", "", false, true);
$sideMenu->addMenuItem(40, "mci_Master", $Language->menuPhrase("40", "MenuText"), "", -1, substr("mci_Master", strpos("mci_Master", "mi_") + 3), true, false, true, "fa-file", "", false, true);
$sideMenu->addMenuItem(44, "mi_units", $Language->menuPhrase("44", "MenuText"), "unitslist", 40, substr("mi_units", strpos("mi_units", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}units'), false, false, "fa-file", "", false, true);
$sideMenu->addMenuItem(39, "mci_Data", $Language->menuPhrase("39", "MenuText"), "", -1, substr("mci_Data", strpos("mci_Data", "mi_") + 3), true, false, true, "fa-file", "", false, true);
$sideMenu->addMenuItem(42, "mi_letters", $Language->menuPhrase("42", "MenuText"), "letterslist", 39, substr("mi_letters", strpos("mi_letters", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}letters'), false, false, "fa-envelope", "", false, true);
$sideMenu->addMenuItem(41, "mi_dispositions", $Language->menuPhrase("41", "MenuText"), "dispositionslist", 39, substr("mi_dispositions", strpos("mi_dispositions", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}dispositions'), false, false, "fa-pencil square", "", false, true);
$sideMenu->addMenuItem(43, "mi_tracks", $Language->menuPhrase("43", "MenuText"), "trackslist", 39, substr("mi_tracks", strpos("mi_tracks", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}tracks'), false, false, "fa-file", "", false, true);
$sideMenu->addMenuItem(16, "mi_theuserprofile", $Language->menuPhrase("16", "MenuText"), "theuserprofilelist", -1, substr("mi_theuserprofile", strpos("mi_theuserprofile", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}theuserprofile'), false, false, "fa-user", "", false, true);
$sideMenu->addMenuItem(5, "mi_help_categories", $Language->menuPhrase("5", "MenuText"), "helpcategorieslist", -1, substr("mi_help_categories", strpos("mi_help_categories", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}help_categories'), false, false, "fa-book", "", false, true);
$sideMenu->addMenuItem(6, "mi_help", $Language->menuPhrase("6", "MenuText"), "helplist?cmd=resetall", 5, substr("mi_help", strpos("mi_help", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}help'), false, false, "fa-book", "", false, true);
$sideMenu->addMenuItem(13, "mci_Terms_and_Condition", $Language->menuPhrase("13", "MenuText"), "javascript:void(0);|||getTermsConditions();return false;", 5, substr("mci_Terms_and_Condition", strpos("mci_Terms_and_Condition", "mi_") + 3), true, false, true, "fas fa-cannabis", "", false, true);
$sideMenu->addMenuItem(14, "mci_About_Us", $Language->menuPhrase("14", "MenuText"), "javascript:void(0);|||getAboutUs();return false;", 5, substr("mci_About_Us", strpos("mci_About_Us", "mi_") + 3), true, false, true, "fa-question", "", false, true);
$sideMenu->addMenuItem(12, "mci_ADMIN", $Language->menuPhrase("12", "MenuText"), "", -1, substr("mci_ADMIN", strpos("mci_ADMIN", "mi_") + 3), true, false, true, "fa-key", "", false, true);
$sideMenu->addMenuItem(1, "mi_users", $Language->menuPhrase("1", "MenuText"), "userslist?cmd=resetall", 12, substr("mi_users", strpos("mi_users", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}users'), false, false, "fa-user", "", false, true);
$sideMenu->addMenuItem(3, "mi_userlevels", $Language->menuPhrase("3", "MenuText"), "userlevelslist", 12, substr("mi_userlevels", strpos("mi_userlevels", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}userlevels'), false, false, "fa-tags", "", false, true);
$sideMenu->addMenuItem(2, "mi_userlevelpermissions", $Language->menuPhrase("2", "MenuText"), "userlevelpermissionslist", 12, substr("mi_userlevelpermissions", strpos("mi_userlevelpermissions", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}userlevelpermissions'), false, false, "fa-file", "", false, true);
$sideMenu->addMenuItem(8, "mi_settings", $Language->menuPhrase("8", "MenuText"), "settingslist", 12, substr("mi_settings", strpos("mi_settings", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}settings'), false, false, "fa-tools", "", false, true);
$sideMenu->addMenuItem(7, "mi_languages", $Language->menuPhrase("7", "MenuText"), "languageslist", 12, substr("mi_languages", strpos("mi_languages", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}languages'), false, false, "fa-flag", "", false, true);
$sideMenu->addMenuItem(15, "mi_announcement", $Language->menuPhrase("15", "MenuText"), "announcementlist", 12, substr("mi_announcement", strpos("mi_announcement", "mi_") + 3), AllowListMenu('{CB54660F-9AFA-48FD-B6BB-4648712A04D7}announcement'), false, false, "fas fa-bullhorn", "", false, true);
echo $sideMenu->toScript();
