<?php

namespace PHPMaker2025\ucarsip;

use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\App;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemException;
use Closure;
use DateTime;
use DateTimeImmutable;
use DateInterval;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Notifier\Transport as NotifierTransport; // SMS transport
use Symfony\Component\Notifier\Channel\SmsChannel;
use Symfony\Component\Notifier\Event\MessageEvent as NotifierMessageEvent;
use Symfony\Component\Notifier\Event\SentMessageEvent as NotifierSentMessageEvent;
use Symfony\Component\Notifier\Event\FailedMessageEvent as NotifierFailedMessageEvent;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\HttpFoundation\RateLimiter\RequestRateLimiterInterface;

// Begin of add by Masino Sinaga, September 8, 2023 
// Instantiate PHP-DI container builder
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAttributes(true);
$isProduction = IsProduction();
// Enable container compilation
if ($isProduction && Config("COMPILE_CONTAINER") && !IsRemote($cacheFolder = Config("CACHE_FOLDER"))) {
    $containerBuilder->enableCompilation(ServerMapPath($cacheFolder)); // local.filesystem not ready yet
}
// Add definitions
$containerBuilder->addDefinitions("src/definitions.php");
// Dispatch container build event
DispatchEvent(new ContainerBuildEvent($containerBuilder), ContainerBuildEvent::NAME);
// Build PHP-DI container instance
$container = $containerBuilder->build();
// Create request object
$Request = $container->get("request.creator")->createServerRequestFromGlobals();
if (ReadCookie('theme') == 'dark') {
	Config("BODY_CLASS", Config("BODY_CLASS") . " dark-mode");
}
if (ReadCookie('aside_toggle_state') == 'collapsed') {
	Config("BODY_CLASS", Config("BODY_CLASS") . " sidebar-collapse");
} elseif (ReadCookie('aside_toggle_state') == 'closed') {
	Config("BODY_CLASS", Config("BODY_CLASS") . " sidebar-closed");
} elseif (ReadCookie('aside_toggle_state') == 'expanded') {
	Config("BODY_CLASS", Config("BODY_CLASS") . " sidebar-open");
}

// End of add by Masino Sinaga, September 8, 2023 

// Begin of add by Masino Sinaga, September 7, 2023 
function AutoVersion($url){
	$dirfile = realpath($url);
	$ver = filemtime($dirfile);
	$file_ext = ".".substr(strtolower(strrchr($url, ".")), 1);
	$file_ext = $file_ext;
	$result = str_replace($file_ext, $file_ext."?v=".$ver, $url);
	echo $result;
}
// End of add by Masino Sinaga, September 7, 2023
function getCurrentPageTitle($pt) {
    global $CurrentPageTitle, $Language;
	$CurrentPageTitle = "";
	$dbid = 0;
	$conn = Conn();
	if (@MS_SHOW_MASINO_BREADCRUMBLINKS == TRUE && Config("MS_MASINO_BREADCRUMBLINKS_TABLE") != "") {
		$sSql = "SELECT C.Page_Title FROM ".Config("MS_MASINO_BREADCRUMBLINKS_TABLE")." AS B, ".Config("MS_MASINO_BREADCRUMBLINKS_TABLE")." AS C WHERE (B.Lft BETWEEN C.Lft AND C.Rgt) AND (B.Page_URL LIKE '".$pt."') ORDER BY C.Lft";
			$stmt = $conn->executeQuery($sSql);
			if ($stmt->rowCount() > 0) {
				while ($row = $stmt->fetchAssociative()) {
					$CurrentPageTitle = $Language->breadcrumbPhrase($row["Page_Title"]);
				}
			} else {
				$CurrentPageTitle = "";
			}
	}
	if (empty($CurrentPageTitle)) {
		if ( CurrentPageID() != "custom" && CurrentPageID() != "system" ) {
			if (CurrentTableName() == trim(CurrentTableName()) && strpos(CurrentTableName(), ' ') !== false) {
				$CurrentPageTitle = ($Language->tablePhrase(str_replace(' ', '', CurrentTableName()), "TblCaption") != "") ? $Language->tablePhrase(str_replace(' ', '', CurrentTableName()), "TblCaption") : str_replace(' ', '', CurrentTableName());
			} else {
				$CurrentPageTitle = ($Language->tablePhrase(CurrentTableName(), "TblCaption") != "") ? $Language->tablePhrase(CurrentTableName(), "TblCaption") : ucwords(CurrentTableName());
			}
		} elseif ( CurrentPageID() == "custom") { // support for Custom Files
			$CurrentPageTitle = $Language->tablePhrase(CurrentPage()->PageObjName, "TblCaption"); // Modified by Masino Sinaga, September 18, 2023
		} elseif ( CurrentPageID() == "system") { // system created by Masino Sinaga, September 18, 2023
			$CurrentPageTitle = $Language->phrase(CurrentPage()->PageObjName); // Modified by Masino Sinaga, September 18, 2023
		}			
		$CurrentPageTitle = str_replace("_list", "", $CurrentPageTitle);
		$CurrentPageTitle = str_replace("_php", "", $CurrentPageTitle);
		$CurrentPageTitle = str_replace("_htm", "", $CurrentPageTitle);
		$CurrentPageTitle = str_replace("_html", "", $CurrentPageTitle);
		$CurrentPageTitle = str_replace("_", " ", $CurrentPageTitle);
		$CurrentPageTitle = ucwords($CurrentPageTitle);
	}
	if ($CurrentPageTitle == "") {
		$Language->projectPhrase("BodyTitle");
	}
	return $CurrentPageTitle;
}

/**
 * Application Root URL
 *
 * @return the url of application root
 */
function AppRootURL() {
	return str_replace(substr(strrchr(CurrentUrl(), "/"), 1), "", DomainUrl().CurrentUrl());
}

// Begin of modification LoadApplicationSettings, by Masino Sinaga, September 10, 2023
function LoadApplicationSettings() {
	$conn = Conn();
	$_SESSION["ucarsip_views"] = 1; // reset the global counter
	// Parent array of all items, initialized if not already...
	if (!isset($_SESSION["ucarsip_appset"])) {
		$_SESSION["ucarsip_appset"] = array();
	}
	$sSql = "SELECT * FROM ".Config("MS_SETTINGS_TABLE")." WHERE Option_Default = 'Y'";
	$stmt = $conn->executeQuery($sSql);
	if ($stmt->rowCount() > 0) {
		while ($row = $stmt->fetchAssociative()) {
			$x = array_keys($row);
			for ($i=0; $i<count($x); $i++) {
				if (is_string($x[$i])) {
					$sfieldname = $x[$i];
					$_SESSION["ucarsip_appset"][0][$sfieldname] = $row[$x[$i]];
				}
			}
		}
		if (!isset($_SESSION["ucarsip_errordb"]))
			$_SESSION["ucarsip_errordb"] = "";
	} else {
		if (!isset($_SESSION["ucarsip_errordb"]))
			$_SESSION["ucarsip_errordb"] = Config("MS_SETTINGS_TABLE");
	}
}
// End of modification LoadApplicationSettings, by Masino Sinaga, September 10, 2023

// Begin of modification My_Global_Check, by Masino Sinaga, September 10, 2023
function My_Global_Check() {
	global $Language, $Security, $page_type, $conn;
    $page_type = "TABLE"; 
	$dbid = 0;	
	if (!isset($_SESSION["ucarsip_Root_URL"])) { 
		$_SESSION["ucarsip_Root_URL"] = AppRootURL();
	}
	if (IsLoggedIn()) {
        if (!IsAdmin()) {
            Config("MS_USER_CARD_USER_NAME", CurrentUserName());
            Config("MS_USER_CARD_COMPLETE_NAME", CurrentUserInfo("FirstName") . " " .  CurrentUserInfo("LastName"));
		    Config("MS_USER_CARD_POSITION", Security()->currentUserLevelName());
        } else {
            Config("MS_USER_CARD_USER_NAME", CurrentUserName());
		    Config("MS_USER_CARD_COMPLETE_NAME", "Administrator");
		    Config("MS_USER_CARD_POSITION", Security()->currentUserLevelName());
        }
	}
	if (!isset($_SESSION["ucarsip_views"])) { 
		$_SESSION["ucarsip_views"] = 0;
	}
	$_SESSION["ucarsip_views"] = $_SESSION["ucarsip_views"]+1;
	if (!isset($_SESSION["ucarsip_appset"])) {
		LoadApplicationSettings();
	}
	if (@$_SESSION["ucarsip_appset"][0]["Show_Announcement"]=="Y") {
		Config("MS_SHOW_ANNOUNCEMENT", TRUE);
	} else {
		Config("MS_SHOW_ANNOUNCEMENT", FALSE);      
	}
	if (@$_SESSION["ucarsip_appset"][0]["Use_Announcement_Table"]=="Y") {
		Config("MS_SEPARATED_ANNOUNCEMENT", TRUE);
	} else {
		Config("MS_SEPARATED_ANNOUNCEMENT", FALSE);      
	}
	if (@$_SESSION["ucarsip_appset"][0]["Maintenance_Mode"]=="Y") {
		Config("MS_MAINTENANCE_MODE", TRUE);        
	} else {
		Config("MS_MAINTENANCE_MODE", FALSE);
	}
	if (@$_SESSION["ucarsip_appset"][0]["Maintenance_Finish_DateTime"]!="") {
		Config("MS_MAINTENANCE_END_DATETIME", $_SESSION["ucarsip_appset"][0]["Maintenance_Finish_DateTime"]);        
	} else {
		Config("MS_MAINTENANCE_END_DATETIME", "");
	}
	if (@$_SESSION["ucarsip_appset"][0]["Auto_Normal_After_Maintenance"]=="Y") {
		Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE", TRUE);
	} else {
		Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE", FALSE);      
	}

	// Begin of modification Announcement in All Pages, by Masino Sinaga, September 10, 2023   
	if (Config("MS_SHOW_ANNOUNCEMENT")) {
	  if (Config("MS_SEPARATED_ANNOUNCEMENT")) {
		$sSqla = "SELECT * FROM ".Config("MS_ANNOUNCEMENT_TABLE")." WHERE Is_Active = 'Y' AND Auto_Publish = 'Y' AND Language = '".CurrentLanguageID()."'";
		$rsa = ExecuteQuery($sSqla, "DB");
		if ($rsa->rowCount() > 0) {
			$today_begin = CurrentDateTime(); // date('Y-m-d')." 00:00:01";
			$today_end = CurrentDateTime(); // date('Y-m-d')." 23:59:59";
			$sTranslatedID = "";
			$sIDAnnouncement = "";
			$sAnnouncement = "";
			while ($row = $rsa->fetchAssociative()) {
				$sIDAnnouncement = $row["Announcement_ID"];
				if ($row["Translated_ID"] == $sIDAnnouncement) {
					$sTranslatedID = $row["Announcement_ID"];
					ExecuteStatement("UPDATE ".Config("MS_ANNOUNCEMENT_TABLE")." SET Is_Active = 'N' WHERE Translated_ID <> " . $sIDAnnouncement . " OR Announcement_ID <> " . $sIDAnnouncement); // reset all become Not Active
				} else {
					$sTranslatedID = $row["Translated_ID"];
				}
				$sAnnouncement = $row["Message"];
				if (IsDateBetweenTwoDates($today_begin, $today_end, $row["Date_Start"], $row["Date_End"])) {
					$sIDAnnouncement = $row["Announcement_ID"];
					ExecuteStatement("UPDATE ".Config("MS_ANNOUNCEMENT_TABLE")." SET Is_Active = 'Y' WHERE Announcement_ID = ".$sIDAnnouncement." OR Translated_ID = ".$sIDAnnouncement); // set Active for the current published announcement
				}
			}
			Config("MS_ANNOUNCEMENT_TEXT", $sAnnouncement);
		} else {
			Config("MS_ANNOUNCEMENT_TEXT", "");
		}
	  } else {
		$sSqll = "SELECT Announcement_Text FROM ".Config("MS_LANGUAGES_TABLE")." WHERE Language_Code = '".CurrentLanguageID()."'";
		$val = ExecuteScalar($sSqll);
		Config("MS_ANNOUNCEMENT_TEXT", $val);
	  }
	}
	// End of modification Announcement in All Pages, by Masino Sinaga, September 10, 2023

	// Begin of modification Maintenance Mode, by Masino Sinaga, September 10, 2023    
	if (Config("MS_MAINTENANCE_MODE")==TRUE) {
		$date_now = date("Y-m-d H:i:s");
		$date_end = Config("MS_MAINTENANCE_END_DATETIME");
		$cssfile = '<link rel="stylesheet" type="text/css" href="adminlte32/css/adminlte.css?v=1666171579">';
		$cssfile .= '<link rel="stylesheet" href="adminlte32/css/font-opensans.css?v=1666171579">';
		$cssfile .= '<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css?v=1666171579">';
		if (!$Security->isAdmin()) {
			if ((CurrentPageName()!="index") && (CurrentPageName()!="logout") && (CurrentPageName()!="login")) {
				if ($date_end != "") { // Assuming end of maintenance date/time is valid
					if ($date_end<=$date_now) {
						if (Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE")==TRUE) {
							// Normal mode here, nothing to do here; just give your user an access!
						} else {
							// Still in maintenance mode, and end of date/time not reached yet, even Auto Normal is False
							echo '<head><title>'.$Language->phrase("MaintenanceTitle").'</title>';
							echo $cssfile;
							echo '</head>';
							echo '<div class="alert alert-warning"><h5><i class="icon fas fa-exclamation-triangle"></i>Alert</h5>'.JsEncode($Language->phrase("MaintenanceUserMessageUnknown")).' <br><a href="logout">'.$Language->phrase("GoBack").'</a></div>';
							exit;
						}
					} else {
						// Still in maintenance mode, even end of date/time has been reached
						echo '<head><title>'.$Language->phrase("MaintenanceTitle").'</title>';
						echo $cssfile;
						echo '</head>';
						echo '<div class="alert alert-warning"><h5><i class="icon fas fa-exclamation-triangle"></i>Alert</h5>'.JsEncode($Language->phrase("MaintenanceUserMessage")).' '.Duration(date("Y-m-d H:i:s"), $date_end).'<br><a href="logout">'.$Language->phrase("GoBack").'</a></div>';
						exit;
					}
				} else {
					// Still in maintenance mode, the date/time value is blank!
					echo '<head><title>'.$Language->phrase("MaintenanceTitle").'</title>';
					echo $cssfile;
					echo '</head>';
					echo '<div class="alert alert-warning"><h5><i class="icon fas fa-exclamation-triangle"></i>Alert</h5>'.JsEncode($Language->phrase("MaintenanceUserMessageUnknown")).' <br><a href="logout">'.$Language->phrase("GoBack").'</a></div>';
					exit;                
				}
			} else {
				// DO NOTHING HERE !!!                
				if ($date_end != "") { // Assuming end of maintenance date/time is valid
					if ($date_end<=$date_now) {
						if (Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE")==TRUE) {
							Config("MS_MAINTENANCE_TEXT", JsEncode($Language->phrase("MaintenanceUserMessageUnknown")).' &nbsp;<a href="logout">'.$Language->phrase("GoBack").'</a>');

							// Normal mode here, just give your user an access!
						} else {
							// Still in maintenance mode, and end of date/time not reached yet, even Auto Normal is False
							Config("MS_MAINTENANCE_TEXT", JsEncode($Language->phrase("MaintenanceUserMessageUnknown")).' &nbsp;<a href="logout">'.$Language->phrase("GoBack").'</a>');
						}
					} else {
						// Still in maintenance mode, even end of date/time has been reached
						Config("MS_MAINTENANCE_TEXT", JsEncode($Language->phrase("MaintenanceUserMessage")).' '.Duration(date("Y-m-d H:i:s"), $date_end).'&nbsp;&nbsp;<a href="logout">'.$Language->phrase("GoBack").'</a>');
					}
				} else {
					// Still in maintenance mode, the date/time value is blank!
					Config("MS_MAINTENANCE_TEXT", JsEncode($Language->phrase("MaintenanceUserMessageUnknown")).' &nbsp;&nbsp;<a href="logout">'.$Language->phrase("GoBack").'</a>');
				}                
			}
		} else {  // Start from here, Maintenance Mode for Admin!
			if ((CurrentPageName()!="index") && (CurrentPageName()!="logout") && (CurrentPageName()!="login")) {
				if ($date_end != "") { // Assuming end of maintenance date/time is valid
					if ($date_end<=$date_now) {
						if (Config("MS_AUTO_NORMAL_AFTER_MAINTENANCE")==TRUE) {
							Config("MS_MAINTENANCE_TEXT", JsEncode($Language->phrase("MaintenanceUserMessageError")).' &nbsp;<a href="logout">'.$Language->phrase("GoBack").'</a>');
						} else {
						  // We are using this, in order to avoid the css conflict, so we use constant help just for admin!
						  Config("MS_MAINTENANCE_TEXT", JsEncode($Language->phrase("MaintenanceAdminMessageUnknown")).' ');
						}
					} else {
						// We are using this, in order to avoid the css conflict, so we use constant help just for admin!
						// Show the remaining time to admin!
						Config("MS_MAINTENANCE_TEXT", JsEncode($Language->phrase("MaintenanceAdminMessage")).' '.Duration(date("Y-m-d H:i:s"), $date_end).'&nbsp;&nbsp;<a href="logout">'.$Language->phrase("GoBack").'</a>');
					}
				} else {
					// We are using this, in order to avoid the css conflict, so we use constant help just for admin!
					Config("MS_MAINTENANCE_TEXT", JsEncode($Language->phrase("MaintenanceAdminMessageUnknown")).' ');
				}
			}
		}
	}
	// End of modification Maintenance Mode, by Masino Sinaga, September 10, 2023
}

// Begin of modification How Long User Should be Allowed Login in the Messages When Failed Login Exceeds the Maximum Limit, by Masino Sinaga, September 10, 2023
function CurrentDateTime_Add_Minutes($currentdate, $minute) {
  $timestamp = strtotime("$currentdate");
  $addtime = strtotime("+$minute minutes", $timestamp);
  $next_time = date('Y-m-d H:i:s', $addtime);
  return $next_time;
}

function DurationFromSeconds($iSeconds) {
	/**
	* Convert number of seconds into years, days, hours, minutes and seconds
	* and return an string containing those values
	*
	* @param integer $seconds Number of seconds to parse
	* @return string
	*/
	global $Language;
	$y = floor($iSeconds / (86400*365.25));
	$d = floor(($iSeconds - ($y*(86400*365.25))) / 86400);
	$h = gmdate('H', $iSeconds);
	$m = gmdate('i', $iSeconds);
	$s = gmdate('s', $iSeconds);
	$string = '';
	if($y > 0)
		$string .= intval($y) . " " . $Language->phrase("years")." ";
	if($d > 0) 
		$string .= intval($d) . " " . $Language->phrase("days")." ";
	if($h > 0) 
		$string .= intval($h) . " " . $Language->phrase("hours")." ";
	if($m > 0) 
		$string .= intval($m) . " " . $Language->phrase("minutes")." ";
	if($s > 0) 
		$string .= intval($s) . " " . $Language->phrase("seconds")." ";
	return preg_replace('/\s+/',' ',$string);
}

function Duration($parambegindate, $paramenddate) {
  global $Language;
  $begindate = strtotime($parambegindate);  
  $enddate = strtotime($paramenddate);
  $diff = intval($enddate) - intval($begindate);
  $diffday = intval(floor($diff/86400));                                      
  $modday = ($diff%86400);  
  $diffhour = intval(floor($modday/3600));  
  $diffminute = intval(floor(($modday%3600)/60));  
  $diffsecond = ($modday%60);  
  if ($diffday!=0 && $diffhour!=0 && $diffminute!=0 && $diffsecond==0) {
    return round($diffday)." ".$Language->phrase('days').        
    ", ".round($diffhour)." ".$Language->phrase('hours').        
    ", ".round($diffminute,0)." ".$Language->phrase('minutes');
  } elseif ($diffday!=0 && $diffhour==0 && $diffminute!=0 && $diffsecond!=0) {
    return round($diffday)." ".$Language->phrase('days').        
    ", ".round($diffminute)." ".$Language->phrase('minutes').        
    ", ".round($diffsecond,0)." ".$Language->phrase('seconds');
  } elseif ($diffday!=0 && $diffhour!=0 && $diffminute==0 && $diffsecond==0) {
    return round($diffday)." ".$Language->phrase('days').        
    ", ".round($diffhour)." ".$Language->phrase('hours');
  } elseif ($diffday!=0 && $diffhour==0 && $diffminute!=0 && $diffsecond==0) {
    return round($diffday)." ".$Language->phrase('days').        
    ", ".round($diffminute,0)." ".$Language->phrase('minutes');
  } elseif ($diffday!=0 && $diffhour==0 && $diffminute==0 && $diffsecond!=0) {
    return round($diffday)." ".$Language->phrase('days').        
    ", ".round($diffsecond,0)." ".$Language->phrase('seconds');	
  } elseif ($diffday!=0 && $diffhour==0 && $diffminute==0 && $diffsecond==0) {
    return round($diffday)." ".$Language->phrase('days');
  }	elseif ($diffday==0 && $diffhour!=0 && $diffminute!=0 && $diffsecond!=0) {
    return round($diffhour)." ".$Language->phrase('hours').
    ", ".round($diffminute,0)." ".$Language->phrase('minutes').
    ", ".round($diffsecond,0)." ".$Language->phrase('seconds')."";
  } elseif ($diffday==0 && $diffhour!=0 && $diffminute==0 && $diffsecond==0) {
    return round($diffhour)." ".$Language->phrase('hours');
  } elseif ($diffday==0 && $diffhour!=0 && $diffminute!=0 && $diffsecond==0) {
    return round($diffhour)." ".$Language->phrase('hours').
    ", ".round($diffminute,0)." ".$Language->phrase('minutes');
  } elseif ($diffday==0 && $diffhour==0 && $diffminute!=0 && $diffsecond==0) {   
    return round($diffminute,0)." ".$Language->phrase('minutes');	
  } elseif ($diffday==0 && $diffhour==0 && $diffminute!=0 && $diffsecond!=0) {   
    return round($diffminute,0)." ".$Language->phrase('minutes').
    ", ".round($diffsecond,0)." ".$Language->phrase('seconds')."";
  } elseif ($diffday==0 && $diffhour==0 && $diffminute==0 && $diffsecond!=0) {   
    return round($diffsecond,0)." ".$Language->phrase('seconds')."";   
  } else {
    return round($diffday)." ".$Language->phrase('days').        
    ", ".round($diffhour)." ".$Language->phrase('hours').        
    ", ".round($diffminute,0)." ".$Language->phrase('minutes').        
    ", ".round($diffsecond,0)." ".$Language->phrase('seconds')."";
  }
}

// End of modification How Long User Should be Allowed Login in the Messages When Failed Login Exceeds the Maximum Limit, by Masino Sinaga, September 10, 2023
function GetIntersectTwoDatesEditMode($iID, $sDateCheckBegin, $sDateCheckEnd, $sLang) {
	$sResult = "";
	$sSql = "SELECT Announcement_ID, Date_Start, Date_End
			FROM " . Config("MS_ANNOUNCEMENT_TABLE") . " 
			WHERE Date_Start IS NOT NULL 
			AND Date_End IS NOT NULL 
			AND Announcement_ID <> ".$iID." 
			AND Language = '".$sLang."'";
	$rs = ExecuteQuery($sSql, "DB");
	if ($rs->rowCount() > 0) {
		while ($row = $rs->fetchAssociative()) {
			$sDateCheckBegin = substr($sDateCheckBegin, 0, 10);
			$sDateCheckEnd = substr($sDateCheckEnd, 0, 10);
			$arrDates1 = GetAllDatesFromTwoDates($sDateCheckBegin, $sDateCheckEnd); 
			$sDateBegin = substr($row["Date_Start"], 0, 10);
			$sDateEnd = substr($row["Date_End"], 0, 10);
			$arrDates2 = GetAllDatesFromTwoDates($sDateBegin, $sDateEnd);
			$result = array_intersect($arrDates1, $arrDates2);
			if ( (count($result)>0) && ($row["Announcement_ID"] != $iID) ) {
				$sResult .= $row["Announcement_ID"]."#";
				foreach($result as $key => $value){ 
					$sResult .= $value.", ";
				} 
				unset($value);
				$sResult = trim($sResult, ", ");
				return $sResult;
			}
		}
	}
    return $sResult;
}

function UpdateDatesInOtherLanguage($sDateBegin, $sDateEnd, $iID) {
	$sResult = "";
	$sSql = "UPDATE " . Config("MS_ANNOUNCEMENT_TABLE") . " 
			SET Date_Start = '".$sDateBegin."',
			Date_End = '".$sDateEnd."' 
			WHERE Translated_ID = ".$iID;
	ExecuteStatement($sSql, "DB");
}

function GetAllDatesFromTwoDates($fromDate, $toDate)
{
    if(!$fromDate || !$toDate ) {return false;}
    $dateMonthYearArr = array();
    $fromDateTS = strtotime($fromDate);
    $toDateTS = strtotime($toDate);
    for ($currentDateTS = $fromDateTS; $currentDateTS <= $toDateTS; $currentDateTS += (60 * 60 * 24))
    {
        $currentDateStr = date("Y-m-d",$currentDateTS);
        $dateMonthYearArr[] = $currentDateStr;
    }
    return $dateMonthYearArr;
}

function IsDateBetweenTwoDates($sDateCheckBegin, $sDateCheckEnd, $sDateBegin, $sDateEnd) {
    $dDate1 = strtotime($sDateCheckBegin);
    $dDate2 = strtotime($sDateCheckEnd);
    if ( ($dDate1 >= strtotime($sDateBegin)) && ($dDate2 <= strtotime($sDateEnd)) ) {
        return TRUE;    
    } else {
        return FALSE;    
    }  
}

// Filter for 'Last Month' (example)
function GetLastMonthFilter(string $expression, string $dbid = "DB"): string
{
    $today = getdate();
    $lastmonth = mktime(0, 0, 0, $today['mon'] - 1, 1, $today['year']);
    $val = date("Y|m", $lastmonth);
    $wrk = $expression . " BETWEEN " .
        QuotedValue(DateValue("month", $val, 1, $dbid), DataType::DATE, $dbid) .
        " AND " .
        QuotedValue(DateValue("month", $val, 2, $dbid), DataType::DATE, $dbid);
    return $wrk;
}

// Filter for 'Starts With A' (example)
function GetStartsWithAFilter(string $expression, string $dbid = "DB"): string
{
    return $expression . Like("A%", $dbid);
}

// Global user functions

// Database Connecting event
function Database_Connecting(array &$info): void
{
    // Example:
    //var_dump($info);
    //if ($info["id"] == "DB" && IsLocal()) { // Testing on local PC
    //    $info["host"] = "localhost";
    //    $info["user"] = "root";
    //    $info["password"] = "";
    //}
}

// Database Connected event
function Database_Connected(Connection $conn, array $info): void
{
    // Example:
    //if ($info["id"] == "DB") {
    //    $conn->executeQuery("Your SQL");
    //}
}

// Language Load event
function Language_Load(): void
{
    // Example:
    //$this->setPhrase("MyID", "MyValue"); // Refer to language file for the actual phrase id
    //$this->setPhraseClass("MyID", "fa-solid fa-xxx ew-icon"); // Refer to https://fontawesome.com/icons?d=gallery&m=free [^] for icon name
    $this->setPhrase("CopyPermissions", "Copy Permissions");
    $this->setPhraseClass("CopyPermissions", "fa-solid fa-copy ew-icon");
    $this->setPhrase("AddCaption", "Please fill in the following form ...");
    $this->setPhrase("EditCaption", "You may edit data in the following form ...");
    $this->setPhrase("SearchCaption", "Please enter your search criteria ...");
    $this->setPhrase("UpdateCaption", "You may edit data in the following form ...");
    $this->setPhrase("ViewCaption", "Here is the information ...");
}

function MenuItem_Adding(MenuItem $item): void
{
    //var_dump($item);
    //$item->Allowed = false; // Set to false if menu item not allowed
}

function Menu_Rendering(): void
{
    // Change menu items here
}

function Menu_Rendered(): void
{
    // Clean up here
}

// Page Loading event
function Page_Loading(): void
{
    //Log("Page Loading");
}

// Page Rendering event
function Page_Rendering(): void
{
    //Log("Page Rendering");
}

// Page Unloaded event
function Page_Unloaded(): void
{
    //Log("Page Unloaded");
}

// AuditTrail Inserting event
function AuditTrail_Inserting(array &$row): bool
{
    //var_dump($row);
    return true;
}

// Personal Data Downloading event
function PersonalData_Downloading(UserInterface $user): void
{
    //Log("PersonalData Downloading");
}

// Personal Data Deleted event
function PersonalData_Deleted(UserInterface $user): void
{
    //Log("PersonalData Deleted");
}

// One Time Password Sending event
function Otp_Sending(Notification $notication, RecipientInterface $recipient): bool
{
    // Example:
    // var_dump($notication, $recipient); // View notication and recipient
    // if (in_array("email", $notication->getChannels())) { // Possible values, "email" or "sms"
    //     $notication->content("..."); // Change content
    //     $recipient->email("..."); // Change email
    //     // return false; // Return false to cancel
    // }
    return true;
}

// Route Action event
function Route_Action(RouteCollectorProxyInterface $app): void
{
    // Example:
    // $app->get('/myaction', function ($request, $response, $args) {
    //    return $response->withJson(["name" => "myaction"]); // Note: Always return Psr\Http\Message\ResponseInterface object
    // });
    // $app->get('/myaction2', function ($request, $response, $args) {
    //    return $response->withJson(["name" => "myaction2"]); // Note: Always return Psr\Http\Message\ResponseInterface object
    // });
}

// API Action event
function Api_Action(RouteCollectorProxyInterface $app): void
{
    // Example:
    // $app->get('/myaction', function ($request, $response, $args) {
    //    return $response->withJson(["name" => "myaction"]); // Note: Always return Psr\Http\Message\ResponseInterface object
    // });
    // $app->get('/myaction2', function ($request, $response, $args) {
    //    return $response->withJson(["name" => "myaction2"]); // Note: Always return Psr\Http\Message\ResponseInterface object
    // });
}

// Container Build event
function Container_Build(ContainerBuilder $builder): void
{
    // Example:
    // $builder->addDefinitions([
    //    "myservice" => function (ContainerInterface $c) {
    //        // your code to provide the service, e.g.
    //        return new MyService();
    //    },
    //    "myservice2" => function (ContainerInterface $c) {
    //        // your code to provide the service, e.g.
    //        return new MyService2();
    //    }
    // ]);
}

// Container Built event
function Container_Built(ContainerInterface $container): void
{
    // Example:
    // $container->set("foo", "bar");
    // $container->set("MyInterface", \DI\create("MyClass"));
}

// Services Config event
function Services_Config(ServicesConfigurator $services): void
{
    // Example:
    // $services->set(MyListener::class)->tag("kernel.event_listener"); // Make sure you tag your listener as "kernel.event_listener"
}

function GetUserLevelName($id) {
	$val = ExecuteScalar("SELECT `Name` FROM `userlevels` WHERE `ID` = " . $id);
	return $val;
}

// Add listeners
AddListener(DatabaseConnectingEvent::NAME, function(DatabaseConnectingEvent $event) {
    $args = $event->getArguments();
    Database_Connecting($args);
    foreach ($args as $key => $value) {
        if ($event->getArgument($key) !== $value) {
            $event->setArgument($key, $value);
        }
    }
});
AddListener(DatabaseConnectedEvent::NAME, fn(DatabaseConnectedEvent $event) => Database_Connected($event->getConnection(), $event->getArguments()));
AddListener(LanguageLoadEvent::NAME, fn(LanguageLoadEvent $event) => Language_Load(...)->bindTo($event->getLanguage())());
AddListener(MenuItemAddingEvent::NAME, fn(MenuItemAddingEvent $event) => MenuItem_Adding(...)->bindTo($event->getMenu())($event->getMenuItem()));
AddListener(MenuRenderingEvent::NAME, fn(MenuRenderingEvent $event) => Menu_Rendering(...)->bindTo($event->getMenu())($event->getMenu()));
AddListener(MenuRenderedEvent::NAME, fn(MenuRenderedEvent $event) => Menu_Rendered(...)->bindTo($event->getMenu())($event->getMenu()));
AddListener(PageLoadingEvent::NAME, fn(PageLoadingEvent $event) => Page_Loading(...)->bindTo($event->getPage())());
AddListener(PageRenderingEvent::NAME, fn(PageRenderingEvent $event) => Page_Rendering(...)->bindTo($event->getPage())());
AddListener(PageUnloadedEvent::NAME, fn(PageUnloadedEvent $event) => Page_Unloaded(...)->bindTo($event->getPage())());
AddListener(RouteActionEvent::NAME, fn(RouteActionEvent $event) => Route_Action($event->getApp()));
AddListener(ApiActionEvent::NAME, fn(ApiActionEvent $event) => Api_Action($event->getGroup()));
AddListener(ContainerBuildEvent::NAME, fn(ContainerBuildEvent $event) => Container_Build($event->getBuilder()));
AddListener(ContainerBuiltEvent::NAME, fn(ContainerBuiltEvent $event) => Container_Built($event->getContainer()));
AddListener(ServicesConfigurationEvent::NAME, fn(ServicesConfigurationEvent $event) => Services_Config($event->getServices()));
AddListener(SentMessageEvent::class, fn(SentMessageEvent $event) => DebugBar()?->addSentMessage($event->getMessage()));
AddListener(FailedMessageEvent::class, fn(FailedMessageEvent $event) => DebugBar()?->addFailedMessage($event->getMessage())->addThrowable($event->getError()));

// Begin of added by Masino Sinaga, September 20, 2023
AddListener(MenuItemAddingEvent::NAME, function(MenuItemAddingEvent $event) {
    $menuItem = $event->getSubject();
	if ($menuItem->Name == "mci_Breadcrumb_Links" && !IsAdmin()) {
		$menuItem->Allowed = false;
	}
});

// Dompdf
AddListener(ConfigurationEvent::NAME, function (ConfigurationEvent $event) {
    $event->import([
        "PDF_BACKEND" => "CPDF",
        "PDF_STYLESHEET_FILENAME" => "css/ewpdf.css", // Export PDF CSS styles
        "PDF_MEMORY_LIMIT" => "512M", // Memory limit
        "PDF_TIME_LIMIT" => 120, // Time limit
        "PDF_MAX_IMAGE_WIDTH" => 650, // Make sure image width not larger than page width or "infinite table loop" error
        "PDF_MAX_IMAGE_HEIGHT" => 900, // Make sure image height not larger than page height or "infinite table loop" error
        "PDF_IMAGE_SCALE_FACTOR" => 1.53, // Scale factor
    ]);
});
// End of added by Masino Sinaga, September 20, 2023

// Captcha
AddListener(ConfigurationEvent::NAME, function (ConfigurationEvent $event) {
    $event->set("CAPTCHA_CLASS", PhpCaptcha::class); // Override default CAPTCHA class
});
