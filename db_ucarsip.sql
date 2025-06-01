-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2025 at 12:11 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ucarsip`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `breadcrumblinksaddsp` (IN `PageTitleParent` VARCHAR(100), `PageTitle` VARCHAR(100), `PageURL` VARCHAR(100))   GoodBye: BEGIN
-- Need three parameters (PageTitleParent, PageTitle, and PageURL),
-- look at this line --> `Page_Title` = PageTitleParent);
-- look at this line --> VALUES (PageTitle, PageURL, ParentLevel, (ParentLevel + 1));
DECLARE ParentLevel INTEGER;
DECLARE RecCount INTEGER;
DECLARE CheckRecCount INTEGER;
DECLARE MyPageTitle VARCHAR(100);
  
SET ParentLevel = (SELECT Rgt FROM `breadcrumblinks` WHERE
`Page_Title` = PageTitleParent);
  
SET CheckRecCount = (SELECT COUNT(*) AS RecCount FROM `breadcrumblinks` WHERE
`Page_Title` = PageTitle);
    IF CheckRecCount > 0 THEN
        SET MyPageTitle = CONCAT("The following Page_Title is already exists in database: ", PageTitle);
        SELECT MyPageTitle;
        LEAVE GoodBye;
  END IF;
  
UPDATE `breadcrumblinks`
   SET Lft = CASE WHEN Lft > ParentLevel THEN
      Lft + 2
    ELSE
      Lft + 0
    END,
   Rgt = CASE WHEN Rgt >= ParentLevel THEN
      Rgt + 2
   ELSE
      Rgt + 0
   END
WHERE  Rgt >= ParentLevel;
  
SET RecCount = (SELECT COUNT(*) FROM `breadcrumblinks`);
    IF RecCount = 0 THEN
        -- this is for handling the first record
        INSERT INTO `breadcrumblinks` (Page_Title, Page_URL, Lft, Rgt)
                    VALUES (PageTitle, PageURL, 1, 2);
    ELSE
        -- whereas the following is for the second record, and so forth!
        INSERT INTO `breadcrumblinks` (Page_Title, Page_URL, Lft, Rgt)
                    VALUES (PageTitle, PageURL, ParentLevel, (ParentLevel + 1));
    END IF;
  
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `breadcrumblinkschecksp` (IN `PageTitleParent` VARCHAR(100), `PageTitle` VARCHAR(100), `PageURL` VARCHAR(100))   GoodBye: BEGIN
-- Need three parameters (PageTitleParent, PageTitle, and PageURL),
-- look at this line --> `Page_Title` = PageTitleParent);
-- look at this line --> VALUES (PageTitle, PageURL, ParentLevel, (ParentLevel + 1));
DECLARE ParentLevel INTEGER;
DECLARE RecCount INTEGER;
DECLARE CheckRecCount INTEGER;
DECLARE MyPageTitle VARCHAR(100);
  
SET ParentLevel = (SELECT Rgt FROM `breadcrumblinks` WHERE
`Page_Title` = PageTitleParent);
  
SET CheckRecCount = (SELECT COUNT(*) AS RecCount FROM `breadcrumblinks` WHERE
`Page_Title` = PageTitle);
    IF CheckRecCount > 0 THEN
        SET MyPageTitle = CONCAT("The following Page_Title is already exists in database: ", PageTitle);
        SELECT MyPageTitle;
        LEAVE GoodBye;
  END IF;
  
UPDATE `breadcrumblinks`
   SET Lft = CASE WHEN Lft > ParentLevel THEN
      Lft + 2
    ELSE
      Lft + 0
    END,
   Rgt = CASE WHEN Rgt >= ParentLevel THEN
      Rgt + 2
   ELSE
      Rgt + 0
   END
WHERE  Rgt >= ParentLevel;
  
SET RecCount = (SELECT COUNT(*) FROM `breadcrumblinks`);
    IF RecCount = 0 THEN
        -- this is for handling the first record
        INSERT INTO `breadcrumblinks` (Page_Title, Page_URL, Lft, Rgt)
                    VALUES (PageTitle, PageURL, 1, 2);
    ELSE
        -- whereas the following is for the second record, and so forth!
        INSERT INTO `breadcrumblinks` (Page_Title, Page_URL, Lft, Rgt)
                    VALUES (PageTitle, PageURL, ParentLevel, (ParentLevel + 1));
    END IF;
  
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `breadcrumblinksdeletesp` (IN `PageTitle` VARCHAR(100))   BEGIN
-- Need one parameter (PageTitle), look at the line: WHERE  Page_Title = PageTitle;
DECLARE DeletedPageTitle VARCHAR(100);
DECLARE DeletedLft INTEGER;
DECLARE DeletedRgt INTEGER;
  
SELECT `Page_Title`, `Lft`, `Rgt`
INTO   DeletedPageTitle, DeletedLft, DeletedRgt
FROM   `breadcrumblinks`
WHERE `Page_Title` = PageTitle;
  
DELETE FROM `breadcrumblinks`
WHERE Lft BETWEEN DeletedLft AND DeletedRgt;
  
UPDATE `breadcrumblinks`
   SET Lft = CASE WHEN Lft > DeletedLft THEN
             Lft - (DeletedRgt - DeletedLft + 1)
          ELSE
             Lft
          END,
       Rgt = CASE WHEN Rgt > DeletedLft THEN
             Rgt - (DeletedRgt - DeletedLft + 1)
          ELSE
             Rgt
          END
   WHERE Lft > DeletedLft
      OR Rgt > DeletedLft;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `breadcrumblinksmovesp` (IN `CurrentRoot` VARCHAR(100), IN `NewParent` VARCHAR(100))   BEGIN
-- Need two parameters: (1) CurrentRoot, and (2) NewParent.
DECLARE Origin_Lft INTEGER;
DECLARE Origin_Rgt INTEGER;
DECLARE NewParent_Rgt INTEGER;
  
SELECT `Lft`, `Rgt`
    INTO Origin_Lft, Origin_Rgt
    FROM `breadcrumblinks`
    WHERE `Page_Title` = CurrentRoot;
SET NewParent_Rgt = (SELECT `Rgt` FROM `breadcrumblinks`
    WHERE `Page_Title` = NewParent);
UPDATE `breadcrumblinks`
    SET `Lft` = `Lft` +
    CASE
        WHEN NewParent_Rgt < Origin_Lft
            THEN CASE
                WHEN Lft BETWEEN Origin_Lft AND Origin_Rgt
                    THEN NewParent_Rgt - Origin_Lft
                WHEN Lft BETWEEN NewParent_Rgt  AND Origin_Lft - 1
                    THEN Origin_Rgt - Origin_Lft + 1
                ELSE 0 END
        WHEN NewParent_Rgt > Origin_Rgt
            THEN CASE
                WHEN Lft BETWEEN Origin_Lft AND Origin_Rgt
                    THEN NewParent_Rgt - Origin_Rgt - 1
                WHEN Lft BETWEEN Origin_Rgt + 1 AND NewParent_Rgt - 1
                    THEN Origin_Lft - Origin_Rgt - 1
                ELSE 0 END
            ELSE 0 END,
    Rgt = Rgt +
    CASE
        WHEN NewParent_Rgt < Origin_Lft
            THEN CASE
        WHEN Rgt BETWEEN Origin_Lft AND Origin_Rgt
            THEN NewParent_Rgt - Origin_Lft
        WHEN Rgt BETWEEN NewParent_Rgt AND Origin_Lft - 1
            THEN Origin_Rgt - Origin_Lft + 1
        ELSE 0 END
        WHEN NewParent_Rgt > Origin_Rgt
            THEN CASE
                WHEN Rgt BETWEEN Origin_Lft AND Origin_Rgt
                    THEN NewParent_Rgt - Origin_Rgt - 1
                WHEN Rgt BETWEEN Origin_Rgt + 1 AND NewParent_Rgt - 1
                    THEN Origin_Lft - Origin_Rgt - 1
                ELSE 0 END
            ELSE 0 END;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `Announcement_ID` int(10) UNSIGNED NOT NULL,
  `Is_Active` enum('N','Y') NOT NULL DEFAULT 'N',
  `Topic` varchar(50) NOT NULL,
  `Message` mediumtext NOT NULL,
  `Date_LastUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Language` char(5) NOT NULL DEFAULT 'en',
  `Auto_Publish` enum('Y','N') DEFAULT 'N',
  `Date_Start` datetime DEFAULT NULL,
  `Date_End` datetime DEFAULT NULL,
  `Date_Created` datetime DEFAULT NULL,
  `Created_By` varchar(200) DEFAULT NULL,
  `Translated_ID` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `announcement`
--

INSERT INTO `announcement` (`Announcement_ID`, `Is_Active`, `Topic`, `Message`, `Date_LastUpdate`, `Language`, `Auto_Publish`, `Date_Start`, `Date_End`, `Date_Created`, `Created_By`, `Translated_ID`) VALUES
(1, 'Y', 'First Announcement (English)', 'Please note that this is the First Announcement in English. This announcement text came from announcement table which also supports for multi-language and auto-publish. Thanks for seeing this!', '2021-10-06 01:34:53', 'en-US', 'Y', '2023-09-29 16:33:03', '2024-10-10 18:33:03', '2019-01-14 00:00:00', 'andrew', 2),
(2, 'Y', 'First Announcement (Indonesian)', '<p>Ini teks <strong>Pengumuman </strong>yang<strong> Pertama</strong> dalam <strong>Bahasa Indonesia</strong>. Teks pengumuman ini berasal dari tabel announcement yang mendukung <strong>multi-bahasa</strong> dan <strong>terbit-otomatis</strong> berdasarkan durasi tanggal tertentu. <img src=\"http://www.ilovephpmaker.com/wp-includes/images/smilies/icon_smile.gif\" alt=\":)\" class=\"wp-smiley\" /></p>', '2021-10-06 01:34:55', 'id-ID', 'Y', '2023-09-01 16:33:03', '2024-10-30 18:33:03', '2014-02-06 00:00:00', 'janet', 1),
(3, 'N', 'Second Announcement (English)', '<p>This is the <strong>Second Announcement</strong> in <strong>English</strong>. This announcement text came from announcement table which also supports for <strong>multi-language</strong> and <strong>auto-publish</strong>.</p>', '2021-10-06 01:35:03', 'en-US', 'Y', '2014-02-11 00:00:01', '2014-02-20 23:59:59', '2014-02-06 10:57:43', 'nancy', 4),
(4, 'N', 'Second Announcement (Indonesian)', '<p>Ini <strong>Pengumuman</strong> yang <strong>Kedua</strong> dalam <strong>Bahasa Indonesia</strong>.&nbsp;Teks pengumuman ini berasal dari tabel announcement yang mendukung <strong>multi-bahasa</strong> dan <strong>terbit-otomatis</strong> berdasarkan durasi tanggal tertentu. :)</p>', '2021-10-06 01:35:13', 'id-ID', 'Y', '2014-02-11 00:00:01', '2014-02-20 23:59:59', '2014-02-06 13:29:21', 'margaret', 3),
(5, 'N', 'Third Announcement (English)', '<p>This is the third Announcement in English.</p>', '2021-10-06 01:35:04', 'en-US', 'Y', '2014-08-01 00:00:01', '2014-08-31 23:59:59', '2014-02-06 10:59:24', 'janet', 6),
(6, 'N', 'Third Announcement (Indonesian)', '<p>Ini teks pengumuman yang ketiga dalam bahasa Indonesia.<em><strong><br /></strong></em></p>', '2021-10-06 01:35:15', 'id-ID', 'Y', '2014-08-01 00:00:01', '2014-08-31 23:59:59', '2014-02-06 13:30:06', 'robert', 5),
(7, 'N', 'Fourth Announcement (English)', '<p>This is the fourth announcement in English.</p>', '2021-10-06 01:35:07', 'en-US', 'Y', '2014-05-01 00:00:01', '2014-05-31 23:59:59', '2014-02-06 10:21:35', 'margaret', 8),
(8, 'N', 'Fourth Announcement (Indonesian)', '<p>Ini adalah teks pengumuman yang keempat (dalam bahasa Indonesia).</p>', '2021-10-06 01:35:17', 'id-ID', 'Y', '2014-05-01 00:00:01', '2014-05-31 23:59:59', '2014-02-06 11:06:20', 'janet', 7),
(9, 'N', 'Fifth Announcement (English)', '<p>This is the fifth announcement in English.</p>', '2021-10-06 01:35:10', 'en-US', 'Y', '2014-06-01 00:00:01', '2014-06-30 23:59:59', '2014-02-05 19:47:24', 'andrew', 10),
(10, 'N', 'Fifth Announcement (Indonesian)', '<p>Sedangkan yang ini adalah pengumuman yang kelima dalam bahasa Indonesia.</p>', '2021-10-06 01:35:19', 'id-ID', 'Y', '2014-06-01 00:00:01', '2014-06-30 23:59:59', '2014-02-05 19:47:24', 'andrew', 9),
(11, 'Y', 'First Announcement (Arabic)', '<p>Please note that this is the <strong>First Announcement</strong> in <strong>Arabic</strong>. This announcement text came from announcement table which also supports for multi-language and auto-publish. Thanks for seeing this! <img src=\"http://www.ilovephpmaker.com/wp-includes/images/smilies/icon_smile.gif\" alt=\":)\" class=\"wp-smiley\" /></p>', '2022-04-30 12:32:34', 'ar-IQ', 'Y', '2021-10-01 16:33:03', '2021-10-30 18:33:03', '2019-01-14 13:27:51', 'andrew', 1);

-- --------------------------------------------------------

--
-- Table structure for table `breadcrumblinks`
--

CREATE TABLE `breadcrumblinks` (
  `Page_Title` varchar(100) NOT NULL,
  `Page_URL` varchar(100) DEFAULT NULL,
  `Lft` int(11) NOT NULL,
  `Rgt` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `breadcrumblinks`
--

INSERT INTO `breadcrumblinks` (`Page_Title`, `Page_URL`, `Lft`, `Rgt`) VALUES
('Add Breadcrumb Links', 'breadcrumblinksaddsp', 274, 275),
('ADMIN ONLY', NULL, 236, 281),
('Alphabetical List of Products', 'alphabeticallistofproducts', 183, 184),
('Announcements', 'announcementlist', 253, 262),
('Announcements Add', 'announcementadd', 256, 257),
('Announcements Delete', 'announcementdelete', 260, 261),
('Announcements Edit', 'announcementedit', 258, 259),
('Announcements View', 'announcementview', 254, 255),
('Application Settings', 'settingslist', 263, 264),
('Breadcrumb Links', '', 265, 276),
('Breadcrumb Links Data', 'breadcrumblinkslist', 272, 273),
('CALENDAR', '', 54, 81),
('Calendar Data', 'calendarlist', 71, 80),
('Calendar Data Add', 'calendaradd', 74, 75),
('Calendar Data Delete', 'calendardelete', 78, 79),
('Calendar Data Edit', 'calendaredit', 76, 77),
('Calendar Data View', 'calendarview', 72, 73),
('Calendar Report', 'calendar1', 57, 58),
('Calendar Scheduler', 'calendarscheduler', 55, 56),
('Cars', 'carslist', 7, 18),
('Cars 2', 'cars2list', 19, 20),
('Cars Add', 'carsadd', 8, 9),
('Cars Delete', 'carsdelete', 12, 13),
('Cars Edit', 'carsedit', 10, 11),
('CARS RELATED', '', 6, 43),
('Cars Search', 'carssearch', 14, 15),
('Cars View', 'carsview', 16, 17),
('Categories', 'categorieslist', 129, 130),
('Check Breadcrumb Links', 'breadcrumblinkschecksp', 266, 267),
('Customers', 'customerslist', 91, 100),
('Customers Add', 'customersadd', 92, 93),
('Customers Edit', 'customersedit', 94, 95),
('Customers Search', 'customerssearch', 98, 99),
('Customers View', 'customersview', 96, 97),
('Dashboard', 'dashboard1', 198, 199),
('Delete Breadcrumb Links', 'breadcrumblinksdeletesp', 270, 271),
('Dow Jones Index', 'djilist', 167, 174),
('Dow Jones Index Add', 'djiadd', 172, 173),
('Dow Jones Index Edit', 'djiedit', 170, 171),
('Dow Jones Index View', 'djiview', 168, 169),
('Edit', 'helpedit', 220, 221),
('Employees', 'employeeslist', 237, 238),
('Events', 'eventslist', 59, 70),
('Events Add', 'eventsadd', 60, 61),
('Events Delete', 'eventsdelete', 68, 69),
('Events Edit', 'eventsedit', 62, 63),
('Events Search', 'eventssearch', 66, 67),
('Events View', 'eventsview', 64, 65),
('First Level', '', 4, 5),
('Front', '', 2, 3),
('Gantt', 'gantt', 193, 194),
('HELP', '', 46, 49),
('Help (Categories)', 'helpcategorieslist', 214, 233),
('Help (Categories) Add', 'helpcategoriesadd', 225, 226),
('Help (Categories) Delete', 'helpcategoriesdelete', 231, 232),
('Help (Categories) Edit', 'helpcategoriesedit', 229, 230),
('Help (Categories) View', 'helpcategoriesview', 227, 228),
('Help (Details)', 'helplist', 215, 224),
('Help (Details) Add', 'helpadd', 218, 219),
('Help (Details) Delete', 'helpdelete', 222, 223),
('Help (Details) View', 'helpview', 216, 217),
('Home', 'home', 1, 282),
('Lagi', '', 234, 235),
('Languages', 'languageslist', 243, 252),
('Languages Add', 'languagesadd', 244, 245),
('Languages Delete', 'languagesdelete', 250, 251),
('Languages Edit', 'languagesedit', 246, 247),
('Languages View', 'languagesview', 248, 249),
('LOCATIONS', '', 82, 89),
('Locations (GoogleMaps)', 'locationslist', 83, 84),
('Locations (Leaflet Mapbox)', 'locations3list', 87, 88),
('Locations (Leaflet OSM)', 'locations2list', 85, 86),
('Login', 'login', 52, 53),
('Masino Sinaga', '', 277, 278),
('Models', 'modelslist', 33, 42),
('Models Add', 'modelsadd', 34, 35),
('Models Edit', 'modelsedit', 36, 37),
('Models Search', 'modelssearch', 40, 41),
('Models View', 'modelsview', 38, 39),
('Move Breadcrumb Links', 'breadcrumblinksmovesp', 268, 269),
('News', 'news', 196, 197),
('Okay Ya', '', 279, 280),
('Order Details', 'orderdetailslist', 101, 112),
('Order Details Add', 'orderdetailsadd', 102, 103),
('Order Details Delete', 'orderdetailsdelete', 110, 111),
('Order Details Edit', 'orderdetailsedit', 104, 105),
('Order Details Extended', 'orderdetailsextendedlist', 165, 166),
('Order Details Search', 'orderdetailssearch', 108, 109),
('Order Details View', 'orderdetailsview', 106, 107),
('Orders', 'orderslist', 131, 142),
('Orders 2', 'orders2list', 143, 152),
('Orders 2 Add', 'orders2add', 144, 145),
('Orders 2 Delete', 'orders2delete', 150, 151),
('Orders 2 Edit', 'orders2edit', 148, 149),
('Orders 2 View', 'orders2view', 146, 147),
('Orders Add', 'ordersadd', 132, 133),
('Orders Delete', 'ordersdelete', 140, 141),
('Orders Edit', 'ordersedit', 136, 137),
('Orders Search', 'orderssearch', 138, 139),
('Orders View', 'ordersview', 134, 135),
('OTHER TABLES', NULL, 90, 175),
('Products', 'productslist', 153, 164),
('Products Add', 'productsadd', 154, 155),
('Products By Category', 'productsbycategory', 185, 186),
('Products Delete', 'productsdelete', 160, 161),
('Products Edit', 'productsedit', 156, 157),
('Products Search', 'productssearch', 162, 163),
('Products View', 'productsview', 158, 159),
('Quarterly Orders by Product', 'quarterlyordersbyproduct', 177, 178),
('REPORTS', '', 176, 195),
('Reports Related', '', 44, 45),
('Sales by Category for 2014', 'salesbycategoryfor2014', 187, 188),
('Sales By Customer', 'salesbycustomer', 179, 180),
('Sales By Customer (Compact)', 'salesbycustomercompact', 181, 182),
('Sales By Customer 2', 'salesbycustomer2', 191, 192),
('Sales By Year', 'salesbyyear', 189, 190),
('Shippers', 'shipperslist', 113, 116),
('Shippers Search', 'shipperssearch', 114, 115),
('STATISTICS', '', 200, 213),
('Statistics OS and Browsers', 'statscounterlist', 201, 202),
('Statistics per Date', 'statsdatelist', 203, 204),
('Statistics per Hour', 'statshourlist', 205, 206),
('Statistics per IP', 'statscounterloglist', 207, 208),
('Statistics per Month', 'statsmonthlist', 209, 210),
('Statistics per Year', 'statsyearlist', 211, 212),
('Suppliers', 'supplierslist', 117, 126),
('Suppliers Add', 'suppliersadd', 122, 123),
('Suppliers Edit', 'suppliersedit', 120, 121),
('Suppliers Search', 'supplierssearch', 124, 125),
('Suppliers View', 'suppliersview', 118, 119),
('Tasks', 'taskslist', 127, 128),
('Trademarks', 'trademarkslist', 21, 32),
('Trademarks Add', 'trademarksadd', 22, 23),
('Trademarks Delete', 'trademarksdelete', 30, 31),
('Trademarks Edit', 'trademarksedit', 24, 25),
('Trademarks Search', 'trademarkssearch', 28, 29),
('Trademarks View', 'trademarksview', 26, 27),
('User Level Permissions', 'userlevelpermissionslist', 241, 242),
('User Levels', 'userlevelslist', 239, 240),
('Users Profile', 'usersprofilelist', 47, 48),
('Visitor Statistics', '', 50, 51);

-- --------------------------------------------------------

--
-- Table structure for table `dispositions`
--

CREATE TABLE `dispositions` (
  `disposition_id` int(11) NOT NULL,
  `letter_id` int(11) NOT NULL,
  `dari_unit_id` int(11) NOT NULL,
  `ke_unit_id` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('diterima','ditolak','diproses') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `help`
--

CREATE TABLE `help` (
  `Help_ID` int(11) NOT NULL,
  `Language` char(5) NOT NULL,
  `Topic` varchar(255) NOT NULL,
  `Description` longtext NOT NULL,
  `Category` int(11) NOT NULL,
  `Order` int(11) NOT NULL,
  `Display_in_Page` varchar(100) NOT NULL,
  `Updated_By` varchar(20) DEFAULT NULL,
  `Last_Updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `help`
--

INSERT INTO `help` (`Help_ID`, `Language`, `Topic`, `Description`, `Category`, `Order`, `Display_in_Page`, `Updated_By`, `Last_Updated`) VALUES
(1, 'en-US', 'Login', 'This is help for <strong>Login</strong> page. You can maintain this help easily from <strong>Help (Details)</strong>.<br /><br />\r\n\r\nYou can also include the HTML tag in this description.<br /><br />\r\n\r\nGood luck!', 1, 1, 'login', 'Administrator', '2023-09-21 16:22:15'),
(2, 'en-US', 'Dow Jones Index', 'This description for DJI List page', 1, 1, 'djilist', 'Masino', '2016-05-27 15:19:04'),
(3, 'en-US', 'Forgot Password', 'This is Help for <strong>Forgot Password</strong> page.<br /><br />\r\n\r\nYou can manage this from <strong>Help (Details)</strong> page.<br /><br />', 1, 2, 'resetpassword', 'Masino', '2020-10-02 00:00:00'),
(4, 'en-US', 'Registration', 'This is help for <strong>Registration</strong> page. You can maintain this help easily from <strong>Help (Details)</strong>.<br /><br />\r\n\r\nYou can also include the HTML tag in this description.<br /><br />', 1, 3, 'register', 'Masino', '2019-01-14 19:46:15'),
(5, 'en-US', 'Change Password', 'This is help for <strong>Change Password</strong> page. You can maintain this help easily from <strong>Help (Details)</strong>.<br /><br />\r\n\r\nYou can also include the HTML tag in this description.<br /><br />', 1, 4, 'changepwd', 'Masino', '2020-10-02 17:17:39'),
(6, 'en-US', 'Calendar Scheduler', 'This is help for <strong>Calendar Scheduler</strong> page. You can maintain this help easily from <strong>Help (Details)</strong>.<br /><br />\r\n\r\nYou can also include the HTML tag in this description.<br /><br />', 2, 1, 'calendarscheduler', 'Masino', '2020-10-02 17:17:58'),
(7, 'en-US', 'Orders', 'This is help for <strong>Orders</strong> page. You can maintain this help easily from <strong>Help (Details)</strong>.<br /><br />\r\n\r\nYou can also include the HTML tag in this description.<br /><br />', 2, 1, 'orderslist', 'Masino', '2020-10-02 17:18:14'),
(8, 'id-ID', 'Login', 'Ini bantuan untuk halaman <strong>Login</strong>. Anda dapat mengelola konten halaman ini dengan mudah melalui menu <strong>Bantuan (Detail)</strong>.<br /><br />\r\n\r\nAnda juga dapat menyertakan tag HTML di ruas Deskripsi ini.<br /><br />', 3, 1, 'login', 'Administrator', '2023-09-21 16:24:36'),
(9, 'id-ID', 'Pendaftaran', 'Ini bantuan untuk halaman <strong>Pendaftaran</strong>. Anda dapat mengelola konten bantuan ini dengan sangat mudah melalui menu <strong>Bantuan (Detail)</strong>.<br /><br />\r\n\r\nAnda juga dapat menyertakan tag HTML ke dalam field Deskripsi ini.<br /><br />', 3, 3, 'register', 'Masino', '2019-01-14 19:46:15'),
(10, 'id-ID', 'Lupa Kata Sandi', 'Bantuan ini untuk halaman <strong>Lupa Kata Sandi</strong> page.<br /><br />\r\n\r\nAnda dapat mengelola konten halaman ini dari menu <strong>Bantuan (Detail)</strong>.<br /><br />', 3, 2, 'resetpassword', 'Masino', '2020-10-02 00:00:00'),
(11, 'id-ID', 'Ganti Kata Sandi', 'Ini bantuan untuk halaman <strong>Ganti Kata Sandi</strong>. Anda dapat mengelola konten bantuan ini dengan sangat mudah melalui menu <strong>Bantuan (Detail)</strong>.<br /><br />\r\n\r\nAnda juga dapat menyertakan tag HTML ke dalam field Deskripsi ini.<br /><br />', 3, 4, 'changepwd', 'Administrator', '2023-09-21 09:30:59'),
(12, 'id-ID', 'Kalender', 'Ini bantuan untuk halaman <strong>Kalender</strong>. Anda dapat mengelola konten bantuan ini dengan sangat mudah dari menu <strong>Bantuan (Detail)</strong>.<br /><br />\r\n\r\nAnda juga dapat menyertakan tag HTML ke dalam field Deskripsi ini.<br /><br />', 4, 1, 'calendarscheduler', 'Administrator', '2023-09-21 09:32:07'),
(13, 'id-ID', 'Pesanan', 'Ini bantuan untuk halaman <strong>Pesanan</strong>. Anda dapat mengelola konten bantuan ini dengan mudah dari menu <strong>Bantuan (Detail)</strong>.<br /><br />\r\n\r\nAnda juga dapat menyertakan tag HTML ke dalam field Deskripsi ini.<br /><br />', 4, 1, 'orderslist', 'Administrator', '2023-09-21 09:33:02'),
(14, 'id-ID', 'Dow Jones Index', 'Deskripsi ini untuk halaman DJI, oke ya?', 3, 1, 'djilist', 'Administrator', '2023-09-21 09:33:45');

-- --------------------------------------------------------

--
-- Table structure for table `help_categories`
--

CREATE TABLE `help_categories` (
  `Category_ID` int(11) NOT NULL,
  `Language` char(5) NOT NULL,
  `Category_Description` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `help_categories`
--

INSERT INTO `help_categories` (`Category_ID`, `Language`, `Category_Description`) VALUES
(1, 'en-US', 'Security'),
(2, 'en-US', 'Another Category'),
(3, 'id-ID', 'Keamanan'),
(4, 'id-ID', 'Kategori Lainnya');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `Language_Code` char(5) NOT NULL,
  `Language_Name` varchar(20) NOT NULL,
  `Default` enum('Y','N') DEFAULT 'N',
  `Site_Logo` varchar(100) NOT NULL,
  `Site_Title` varchar(100) NOT NULL,
  `Default_Thousands_Separator` varchar(5) DEFAULT NULL,
  `Default_Decimal_Point` varchar(5) DEFAULT NULL,
  `Default_Currency_Symbol` varchar(10) DEFAULT NULL,
  `Default_Money_Thousands_Separator` varchar(5) DEFAULT NULL,
  `Default_Money_Decimal_Point` varchar(5) DEFAULT NULL,
  `Terms_And_Condition_Text` text NOT NULL,
  `Announcement_Text` text NOT NULL,
  `About_Text` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`Language_Code`, `Language_Name`, `Default`, `Site_Logo`, `Site_Title`, `Default_Thousands_Separator`, `Default_Decimal_Point`, `Default_Currency_Symbol`, `Default_Money_Thousands_Separator`, `Default_Money_Decimal_Point`, `Terms_And_Condition_Text`, `Announcement_Text`, `About_Text`) VALUES
('en-US', 'English', 'Y', '-', 'PHPMaker Demo Project', ',', '.', '$', ',', '.', 'Dengan menggunakan Aplikasi Web <strong>One Time Password (OTP)</strong>, maka Anda wajib mematuhi <strong>Syarat dan Ketentuan</strong> yang kami tetapkan.<br><br>1. Aplikasi OTP berfungsi untuk mereset Kata Sandi (Password) pengguna aplikasi bisnis Pos Indonesia.<br><br>2. Aplikasi yang menggunakan OTP untuk mereset Kata Sandi penggunanya adalah System Online Payment Point (SOPP) dan Remittance Service (RS).<br><br>3. Pengguna aplikasi bisnis yang didaftarkan di Aplikasi OTP hanya mereka yang memiliki level Manajer Pelayanan dan Manajer PPLA (Pengawas Pelayanan Luar dan Agenpos)<br><br>4. Pengguna Aplikasi OTP yang ingin mendaftarkan akunnya harus sudah terdaftar sebelumnya di aplikasi bisnis yang bertalian.<br><br>5. Pengguna aplikasi bisnis yang akan menerima Password baru dari Aplikasi OTP sudah memiliki Handphone dan Email.<br><br>6. Setiap Pengguna aplikasi bisnis tidak boleh menggunakan Email dan Nomor Handphone yang sama dengan yang digunakan oleh pengguna lainnya.<br><br>7. Pengguna aplikasi bisnis yang melakukan reset Password melalui Aplikasi OTP hanya mereka yang memiliki level Manajer Pelayanan dan Manajer PPLA.<br><br>8. Setiap hari, Pengguna Aplikasi OTP wajib mereset Password milik staff yang berada di bawah pengawasannya.<br><br>9. Password baru hasil reset dari Aplikasi OTP akan dikirimkan ke Email dan atau Handphone milik pengguna aplikasi bisnis masing-masing.<br><br>10. Dilarang memberitahukan data akun (Username/ID Petugas dan atau Password) kepada pihak lain manapun, baik secara sengaja maupun tidak sengaja.<br><br>11. Baik Pengguna Aplikasi OTP maupun pengguna aplikasi bisnis harus sungguh-sungguh bertanggung jawab melindungi data akunnya.<br><br>12. Kelalaian dalam menyimpan data akun sehingga bisa diketahui oleh pihak lain menjadi tanggung jawab masing-masing.<br><br>', 'This is the announcement text from database. You can edit this text from the languages table ...', '<span class=\'dialogtitle\' style=\'white-space: nowrap;\'>Your Application Title goes here, version 1.0</span><br><br>Your application description line one goes here ... <br><br>Your application description line two goes here ... <br><br><br>Web Developer:<br></span>john samori (somarury@gmail.com)<br>You can edit this text from the languages table,<br>... <br>... <br>'),
('id-ID', 'Indonesia', 'N', '-', 'PHPMaker Proyek Demo', '.', ',', 'Rp', '.', ',', 'Dengan menggunakan Aplikasi Web <strong>One Time Password (OTP)</strong>, maka Anda wajib mematuhi <strong>Syarat dan Ketentuan</strong> yang kami tetapkan.<br><br>1. Aplikasi OTP berfungsi untuk mereset Kata Sandi (Password) pengguna aplikasi bisnis Pos Indonesia.<br><br>2. Aplikasi yang menggunakan OTP untuk mereset Kata Sandi penggunanya adalah System Online Payment Point (SOPP) dan Remittance Service (RS).<br><br>3. Pengguna aplikasi bisnis yang didaftarkan di Aplikasi OTP hanya mereka yang memiliki level Manajer Pelayanan dan Manajer PPLA (Pengawas Pelayanan Luar dan Agenpos)<br><br>4. Pengguna Aplikasi OTP yang ingin mendaftarkan akunnya harus sudah terdaftar sebelumnya di aplikasi bisnis yang bertalian.<br><br>5. Pengguna aplikasi bisnis yang akan menerima Password baru dari Aplikasi OTP sudah memiliki Handphone dan Email.<br><br>6. Setiap Pengguna aplikasi bisnis tidak boleh menggunakan Email dan Nomor Handphone yang sama dengan yang digunakan oleh pengguna lainnya.<br><br>7. Pengguna aplikasi bisnis yang melakukan reset Password melalui Aplikasi OTP hanya mereka yang memiliki level Manajer Pelayanan dan Manajer PPLA.<br><br>8. Setiap hari, Pengguna Aplikasi OTP wajib mereset Password milik staff yang berada di bawah pengawasannya.<br><br>9. Password baru hasil reset dari Aplikasi OTP akan dikirimkan ke Email dan atau Handphone milik pengguna aplikasi bisnis masing-masing.<br><br>10. Dilarang memberitahukan data akun (Username/ID Petugas dan atau Password) kepada pihak lain manapun, baik secara sengaja maupun tidak sengaja.<br><br>11. Baik Pengguna Aplikasi OTP maupun pengguna aplikasi bisnis harus sungguh-sungguh bertanggung jawab melindungi data akunnya.<br><br>12. Kelalaian dalam menyimpan data akun sehingga bisa diketahui oleh pihak lain menjadi tanggung jawab masing-masing.<br><br>', 'Ini teks pengumuman dari database. Anda dapat mengubah teks ini dari tabel languages ...', '<span class=\'dialogtitle\' style=\'white-space: nowrap;\'>Judul Aplikasi Anda di sini, versi 1.0</span><br><br>Deskripsi aplikasi baris pertama Anda di sini ... <br><br>Deskripsi aplikasi baris kedua Anda di sini ... <br><br><br>Web Developer:<br></span>john samori (somarury@gmail.com)<br>Anda dapat mengubah teks ini dari tabel languages,<br>... <br>... <br>');

-- --------------------------------------------------------

--
-- Table structure for table `letters`
--

CREATE TABLE `letters` (
  `letter_id` int(11) NOT NULL,
  `nomor_surat` varchar(50) NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `tanggal_terima` date DEFAULT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `klasifikasi` enum('biasa','penting','rahasia') NOT NULL,
  `pengirim` varchar(100) NOT NULL,
  `penerima_unit_id` int(11) DEFAULT NULL,
  `file_url` varchar(255) NOT NULL,
  `status` enum('draft','terkirim','disposisi','selesai') NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `letters`
--

INSERT INTO `letters` (`letter_id`, `nomor_surat`, `perihal`, `tanggal_surat`, `tanggal_terima`, `jenis`, `klasifikasi`, `pengirim`, `penerima_unit_id`, `file_url`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(2, '5453', 'fdfsdfsd', '2025-06-02', '2025-06-02', 'masuk', 'biasa', 'fdsfsd', 1, 'dsfdsf', 'draft', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `Option_ID` int(11) NOT NULL,
  `Option_Default` enum('Y','N') DEFAULT 'N',
  `Show_Announcement` enum('Y','N') DEFAULT 'N',
  `Use_Announcement_Table` enum('N','Y') DEFAULT 'N',
  `Maintenance_Mode` enum('N','Y') DEFAULT 'N',
  `Maintenance_Finish_DateTime` datetime DEFAULT NULL,
  `Auto_Normal_After_Maintenance` enum('Y','N') DEFAULT 'Y'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=FIXED;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`Option_ID`, `Option_Default`, `Show_Announcement`, `Use_Announcement_Table`, `Maintenance_Mode`, `Maintenance_Finish_DateTime`, `Auto_Normal_After_Maintenance`) VALUES
(1, 'Y', 'N', 'Y', 'N', '2024-11-18 17:01:00', 'Y'),
(2, 'N', 'N', 'N', 'Y', '2019-10-09 22:58:59', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `stats_counter`
--

CREATE TABLE `stats_counter` (
  `Type` varchar(50) NOT NULL DEFAULT '',
  `Variable` varchar(50) NOT NULL DEFAULT '',
  `Counter` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `stats_counter`
--

INSERT INTO `stats_counter` (`Type`, `Variable`, `Counter`) VALUES
('total', 'hits', 454),
('browser', 'WebTV', 0),
('browser', 'Lynx', 0),
('browser', 'MSIE', 0),
('browser', 'Opera', 0),
('browser', 'Konqueror', 0),
('browser', 'Netscape', 0),
('browser', 'FireFox', 0),
('browser', 'Chrome', 454),
('browser', 'Bot', 0),
('browser', 'Other', 0),
('os', 'Windows', 454),
('os', 'Linux', 0),
('os', 'Mac', 0),
('os', 'FreeBSD', 0),
('os', 'SunOS', 0),
('os', 'IRIX', 0),
('os', 'BeOS', 0),
('os', 'OS/2', 0),
('os', 'AIX', 0),
('os', 'Other', 0);

-- --------------------------------------------------------

--
-- Table structure for table `stats_counterlog`
--

CREATE TABLE `stats_counterlog` (
  `IP_Address` varchar(50) NOT NULL DEFAULT '',
  `Hostname` varchar(50) DEFAULT NULL,
  `First_Visit` datetime NOT NULL,
  `Last_Visit` datetime NOT NULL,
  `Counter` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `stats_counterlog`
--

INSERT INTO `stats_counterlog` (`IP_Address`, `Hostname`, `First_Visit`, `Last_Visit`, `Counter`) VALUES
('::1', 'DESKTOP-DPI42J6', '2024-10-23 13:23:27', '2024-10-28 18:14:15', 454);

-- --------------------------------------------------------

--
-- Table structure for table `stats_date`
--

CREATE TABLE `stats_date` (
  `Year` smallint(6) NOT NULL DEFAULT 0,
  `Month` tinyint(4) NOT NULL DEFAULT 0,
  `Date` tinyint(4) NOT NULL DEFAULT 0,
  `Hits` bigint(20) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=FIXED;

--
-- Dumping data for table `stats_date`
--

INSERT INTO `stats_date` (`Year`, `Month`, `Date`, `Hits`) VALUES
(2024, 1, 1, 0),
(2024, 1, 2, 0),
(2024, 1, 3, 0),
(2024, 1, 4, 0),
(2024, 1, 5, 0),
(2024, 1, 6, 0),
(2024, 1, 7, 0),
(2024, 1, 8, 0),
(2024, 1, 9, 0),
(2024, 1, 10, 0),
(2024, 1, 11, 0),
(2024, 1, 12, 0),
(2024, 1, 13, 0),
(2024, 1, 14, 0),
(2024, 1, 15, 0),
(2024, 1, 16, 0),
(2024, 1, 17, 0),
(2024, 1, 18, 0),
(2024, 1, 19, 0),
(2024, 1, 20, 0),
(2024, 1, 21, 0),
(2024, 1, 22, 0),
(2024, 1, 23, 0),
(2024, 1, 24, 0),
(2024, 1, 25, 0),
(2024, 1, 26, 0),
(2024, 1, 27, 0),
(2024, 1, 28, 0),
(2024, 1, 29, 0),
(2024, 1, 30, 0),
(2024, 1, 31, 0),
(2024, 2, 1, 0),
(2024, 2, 2, 0),
(2024, 2, 3, 0),
(2024, 2, 4, 0),
(2024, 2, 5, 0),
(2024, 2, 6, 0),
(2024, 2, 7, 0),
(2024, 2, 8, 0),
(2024, 2, 9, 0),
(2024, 2, 10, 0),
(2024, 2, 11, 0),
(2024, 2, 12, 0),
(2024, 2, 13, 0),
(2024, 2, 14, 0),
(2024, 2, 15, 0),
(2024, 2, 16, 0),
(2024, 2, 17, 0),
(2024, 2, 18, 0),
(2024, 2, 19, 0),
(2024, 2, 20, 0),
(2024, 2, 21, 0),
(2024, 2, 22, 0),
(2024, 2, 23, 0),
(2024, 2, 24, 0),
(2024, 2, 25, 0),
(2024, 2, 26, 0),
(2024, 2, 27, 0),
(2024, 2, 28, 0),
(2024, 2, 29, 0),
(2024, 3, 1, 0),
(2024, 3, 2, 0),
(2024, 3, 3, 0),
(2024, 3, 4, 0),
(2024, 3, 5, 0),
(2024, 3, 6, 0),
(2024, 3, 7, 0),
(2024, 3, 8, 0),
(2024, 3, 9, 0),
(2024, 3, 10, 0),
(2024, 3, 11, 0),
(2024, 3, 12, 0),
(2024, 3, 13, 0),
(2024, 3, 14, 0),
(2024, 3, 15, 0),
(2024, 3, 16, 0),
(2024, 3, 17, 0),
(2024, 3, 18, 0),
(2024, 3, 19, 0),
(2024, 3, 20, 0),
(2024, 3, 21, 0),
(2024, 3, 22, 0),
(2024, 3, 23, 0),
(2024, 3, 24, 0),
(2024, 3, 25, 0),
(2024, 3, 26, 0),
(2024, 3, 27, 0),
(2024, 3, 28, 0),
(2024, 3, 29, 0),
(2024, 3, 30, 0),
(2024, 3, 31, 0),
(2024, 4, 1, 0),
(2024, 4, 2, 0),
(2024, 4, 3, 0),
(2024, 4, 4, 0),
(2024, 4, 5, 0),
(2024, 4, 6, 0),
(2024, 4, 7, 0),
(2024, 4, 8, 0),
(2024, 4, 9, 0),
(2024, 4, 10, 0),
(2024, 4, 11, 0),
(2024, 4, 12, 0),
(2024, 4, 13, 0),
(2024, 4, 14, 0),
(2024, 4, 15, 0),
(2024, 4, 16, 0),
(2024, 4, 17, 0),
(2024, 4, 18, 0),
(2024, 4, 19, 0),
(2024, 4, 20, 0),
(2024, 4, 21, 0),
(2024, 4, 22, 0),
(2024, 4, 23, 0),
(2024, 4, 24, 0),
(2024, 4, 25, 0),
(2024, 4, 26, 0),
(2024, 4, 27, 0),
(2024, 4, 28, 0),
(2024, 4, 29, 0),
(2024, 4, 30, 0),
(2024, 5, 1, 0),
(2024, 5, 2, 0),
(2024, 5, 3, 0),
(2024, 5, 4, 0),
(2024, 5, 5, 0),
(2024, 5, 6, 0),
(2024, 5, 7, 0),
(2024, 5, 8, 0),
(2024, 5, 9, 0),
(2024, 5, 10, 0),
(2024, 5, 11, 0),
(2024, 5, 12, 0),
(2024, 5, 13, 0),
(2024, 5, 14, 0),
(2024, 5, 15, 0),
(2024, 5, 16, 0),
(2024, 5, 17, 0),
(2024, 5, 18, 0),
(2024, 5, 19, 0),
(2024, 5, 20, 0),
(2024, 5, 21, 0),
(2024, 5, 22, 0),
(2024, 5, 23, 0),
(2024, 5, 24, 0),
(2024, 5, 25, 0),
(2024, 5, 26, 0),
(2024, 5, 27, 0),
(2024, 5, 28, 0),
(2024, 5, 29, 0),
(2024, 5, 30, 0),
(2024, 5, 31, 0),
(2024, 6, 1, 0),
(2024, 6, 2, 0),
(2024, 6, 3, 0),
(2024, 6, 4, 0),
(2024, 6, 5, 0),
(2024, 6, 6, 0),
(2024, 6, 7, 0),
(2024, 6, 8, 0),
(2024, 6, 9, 0),
(2024, 6, 10, 0),
(2024, 6, 11, 0),
(2024, 6, 12, 0),
(2024, 6, 13, 0),
(2024, 6, 14, 0),
(2024, 6, 15, 0),
(2024, 6, 16, 0),
(2024, 6, 17, 0),
(2024, 6, 18, 0),
(2024, 6, 19, 0),
(2024, 6, 20, 0),
(2024, 6, 21, 0),
(2024, 6, 22, 0),
(2024, 6, 23, 0),
(2024, 6, 24, 0),
(2024, 6, 25, 0),
(2024, 6, 26, 0),
(2024, 6, 27, 0),
(2024, 6, 28, 0),
(2024, 6, 29, 0),
(2024, 6, 30, 0),
(2024, 7, 1, 0),
(2024, 7, 2, 0),
(2024, 7, 3, 0),
(2024, 7, 4, 0),
(2024, 7, 5, 0),
(2024, 7, 6, 0),
(2024, 7, 7, 0),
(2024, 7, 8, 0),
(2024, 7, 9, 0),
(2024, 7, 10, 0),
(2024, 7, 11, 0),
(2024, 7, 12, 0),
(2024, 7, 13, 0),
(2024, 7, 14, 0),
(2024, 7, 15, 0),
(2024, 7, 16, 0),
(2024, 7, 17, 0),
(2024, 7, 18, 0),
(2024, 7, 19, 0),
(2024, 7, 20, 0),
(2024, 7, 21, 0),
(2024, 7, 22, 0),
(2024, 7, 23, 0),
(2024, 7, 24, 0),
(2024, 7, 25, 0),
(2024, 7, 26, 0),
(2024, 7, 27, 0),
(2024, 7, 28, 0),
(2024, 7, 29, 0),
(2024, 7, 30, 0),
(2024, 7, 31, 0),
(2024, 8, 1, 0),
(2024, 8, 2, 0),
(2024, 8, 3, 0),
(2024, 8, 4, 0),
(2024, 8, 5, 0),
(2024, 8, 6, 0),
(2024, 8, 7, 0),
(2024, 8, 8, 0),
(2024, 8, 9, 0),
(2024, 8, 10, 0),
(2024, 8, 11, 0),
(2024, 8, 12, 0),
(2024, 8, 13, 0),
(2024, 8, 14, 0),
(2024, 8, 15, 0),
(2024, 8, 16, 0),
(2024, 8, 17, 0),
(2024, 8, 18, 0),
(2024, 8, 19, 0),
(2024, 8, 20, 0),
(2024, 8, 21, 0),
(2024, 8, 22, 0),
(2024, 8, 23, 0),
(2024, 8, 24, 0),
(2024, 8, 25, 0),
(2024, 8, 26, 0),
(2024, 8, 27, 0),
(2024, 8, 28, 0),
(2024, 8, 29, 0),
(2024, 8, 30, 0),
(2024, 8, 31, 0),
(2024, 9, 1, 0),
(2024, 9, 2, 0),
(2024, 9, 3, 0),
(2024, 9, 4, 0),
(2024, 9, 5, 0),
(2024, 9, 6, 0),
(2024, 9, 7, 0),
(2024, 9, 8, 0),
(2024, 9, 9, 0),
(2024, 9, 10, 0),
(2024, 9, 11, 0),
(2024, 9, 12, 0),
(2024, 9, 13, 0),
(2024, 9, 14, 0),
(2024, 9, 15, 0),
(2024, 9, 16, 0),
(2024, 9, 17, 0),
(2024, 9, 18, 0),
(2024, 9, 19, 0),
(2024, 9, 20, 0),
(2024, 9, 21, 0),
(2024, 9, 22, 0),
(2024, 9, 23, 0),
(2024, 9, 24, 0),
(2024, 9, 25, 0),
(2024, 9, 26, 0),
(2024, 9, 27, 0),
(2024, 9, 28, 0),
(2024, 9, 29, 0),
(2024, 9, 30, 0),
(2024, 10, 1, 0),
(2024, 10, 2, 0),
(2024, 10, 3, 0),
(2024, 10, 4, 0),
(2024, 10, 5, 0),
(2024, 10, 6, 0),
(2024, 10, 7, 0),
(2024, 10, 8, 0),
(2024, 10, 9, 0),
(2024, 10, 10, 0),
(2024, 10, 11, 0),
(2024, 10, 12, 0),
(2024, 10, 13, 0),
(2024, 10, 14, 0),
(2024, 10, 15, 0),
(2024, 10, 16, 0),
(2024, 10, 17, 0),
(2024, 10, 18, 0),
(2024, 10, 19, 0),
(2024, 10, 20, 0),
(2024, 10, 21, 0),
(2024, 10, 22, 0),
(2024, 10, 23, 9),
(2024, 10, 24, 187),
(2024, 10, 25, 0),
(2024, 10, 26, 27),
(2024, 10, 27, 129),
(2024, 10, 28, 102),
(2024, 10, 29, 0),
(2024, 10, 30, 0),
(2024, 10, 31, 0),
(2024, 11, 1, 0),
(2024, 11, 2, 0),
(2024, 11, 3, 0),
(2024, 11, 4, 0),
(2024, 11, 5, 0),
(2024, 11, 6, 0),
(2024, 11, 7, 0),
(2024, 11, 8, 0),
(2024, 11, 9, 0),
(2024, 11, 10, 0),
(2024, 11, 11, 0),
(2024, 11, 12, 0),
(2024, 11, 13, 0),
(2024, 11, 14, 0),
(2024, 11, 15, 0),
(2024, 11, 16, 0),
(2024, 11, 17, 0),
(2024, 11, 18, 0),
(2024, 11, 19, 0),
(2024, 11, 20, 0),
(2024, 11, 21, 0),
(2024, 11, 22, 0),
(2024, 11, 23, 0),
(2024, 11, 24, 0),
(2024, 11, 25, 0),
(2024, 11, 26, 0),
(2024, 11, 27, 0),
(2024, 11, 28, 0),
(2024, 11, 29, 0),
(2024, 11, 30, 0),
(2024, 12, 1, 0),
(2024, 12, 2, 0),
(2024, 12, 3, 0),
(2024, 12, 4, 0),
(2024, 12, 5, 0),
(2024, 12, 6, 0),
(2024, 12, 7, 0),
(2024, 12, 8, 0),
(2024, 12, 9, 0),
(2024, 12, 10, 0),
(2024, 12, 11, 0),
(2024, 12, 12, 0),
(2024, 12, 13, 0),
(2024, 12, 14, 0),
(2024, 12, 15, 0),
(2024, 12, 16, 0),
(2024, 12, 17, 0),
(2024, 12, 18, 0),
(2024, 12, 19, 0),
(2024, 12, 20, 0),
(2024, 12, 21, 0),
(2024, 12, 22, 0),
(2024, 12, 23, 0),
(2024, 12, 24, 0),
(2024, 12, 25, 0),
(2024, 12, 26, 0),
(2024, 12, 27, 0),
(2024, 12, 28, 0),
(2024, 12, 29, 0),
(2024, 12, 30, 0),
(2024, 12, 31, 0);

-- --------------------------------------------------------

--
-- Table structure for table `stats_hour`
--

CREATE TABLE `stats_hour` (
  `Year` smallint(6) NOT NULL DEFAULT 0,
  `Month` tinyint(4) NOT NULL DEFAULT 0,
  `Date` tinyint(4) NOT NULL DEFAULT 0,
  `Hour` tinyint(4) NOT NULL DEFAULT 0,
  `Hits` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=FIXED;

--
-- Dumping data for table `stats_hour`
--

INSERT INTO `stats_hour` (`Year`, `Month`, `Date`, `Hour`, `Hits`) VALUES
(2024, 10, 23, 0, 0),
(2024, 10, 23, 1, 0),
(2024, 10, 23, 2, 0),
(2024, 10, 23, 3, 0),
(2024, 10, 23, 4, 0),
(2024, 10, 23, 5, 0),
(2024, 10, 23, 6, 0),
(2024, 10, 23, 7, 0),
(2024, 10, 23, 8, 0),
(2024, 10, 23, 9, 0),
(2024, 10, 23, 10, 0),
(2024, 10, 23, 11, 0),
(2024, 10, 23, 12, 0),
(2024, 10, 23, 13, 9),
(2024, 10, 23, 14, 0),
(2024, 10, 23, 15, 0),
(2024, 10, 23, 16, 0),
(2024, 10, 23, 17, 0),
(2024, 10, 23, 18, 0),
(2024, 10, 23, 19, 0),
(2024, 10, 23, 20, 0),
(2024, 10, 23, 21, 0),
(2024, 10, 23, 22, 0),
(2024, 10, 23, 23, 0),
(2024, 10, 24, 0, 0),
(2024, 10, 24, 1, 0),
(2024, 10, 24, 2, 0),
(2024, 10, 24, 3, 0),
(2024, 10, 24, 4, 0),
(2024, 10, 24, 5, 0),
(2024, 10, 24, 6, 0),
(2024, 10, 24, 7, 2),
(2024, 10, 24, 8, 25),
(2024, 10, 24, 9, 0),
(2024, 10, 24, 10, 16),
(2024, 10, 24, 11, 47),
(2024, 10, 24, 12, 33),
(2024, 10, 24, 13, 4),
(2024, 10, 24, 14, 0),
(2024, 10, 24, 15, 0),
(2024, 10, 24, 16, 0),
(2024, 10, 24, 17, 0),
(2024, 10, 24, 18, 60),
(2024, 10, 24, 19, 0),
(2024, 10, 24, 20, 0),
(2024, 10, 24, 21, 0),
(2024, 10, 24, 22, 0),
(2024, 10, 24, 23, 0),
(2024, 10, 26, 0, 0),
(2024, 10, 26, 1, 0),
(2024, 10, 26, 2, 0),
(2024, 10, 26, 3, 0),
(2024, 10, 26, 4, 0),
(2024, 10, 26, 5, 0),
(2024, 10, 26, 6, 0),
(2024, 10, 26, 7, 0),
(2024, 10, 26, 8, 0),
(2024, 10, 26, 9, 0),
(2024, 10, 26, 10, 0),
(2024, 10, 26, 11, 0),
(2024, 10, 26, 12, 19),
(2024, 10, 26, 13, 8),
(2024, 10, 26, 14, 0),
(2024, 10, 26, 15, 0),
(2024, 10, 26, 16, 0),
(2024, 10, 26, 17, 0),
(2024, 10, 26, 18, 0),
(2024, 10, 26, 19, 0),
(2024, 10, 26, 20, 0),
(2024, 10, 26, 21, 0),
(2024, 10, 26, 22, 0),
(2024, 10, 26, 23, 0),
(2024, 10, 27, 0, 0),
(2024, 10, 27, 1, 0),
(2024, 10, 27, 2, 0),
(2024, 10, 27, 3, 0),
(2024, 10, 27, 4, 0),
(2024, 10, 27, 5, 0),
(2024, 10, 27, 6, 0),
(2024, 10, 27, 7, 0),
(2024, 10, 27, 8, 30),
(2024, 10, 27, 9, 42),
(2024, 10, 27, 10, 0),
(2024, 10, 27, 11, 0),
(2024, 10, 27, 12, 8),
(2024, 10, 27, 13, 12),
(2024, 10, 27, 14, 37),
(2024, 10, 27, 15, 0),
(2024, 10, 27, 16, 0),
(2024, 10, 27, 17, 0),
(2024, 10, 27, 18, 0),
(2024, 10, 27, 19, 0),
(2024, 10, 27, 20, 0),
(2024, 10, 27, 21, 0),
(2024, 10, 27, 22, 0),
(2024, 10, 27, 23, 0),
(2024, 10, 28, 0, 0),
(2024, 10, 28, 1, 0),
(2024, 10, 28, 2, 0),
(2024, 10, 28, 3, 0),
(2024, 10, 28, 4, 0),
(2024, 10, 28, 5, 0),
(2024, 10, 28, 6, 0),
(2024, 10, 28, 7, 0),
(2024, 10, 28, 8, 0),
(2024, 10, 28, 9, 0),
(2024, 10, 28, 10, 33),
(2024, 10, 28, 11, 20),
(2024, 10, 28, 12, 27),
(2024, 10, 28, 13, 1),
(2024, 10, 28, 14, 0),
(2024, 10, 28, 15, 0),
(2024, 10, 28, 16, 0),
(2024, 10, 28, 17, 11),
(2024, 10, 28, 18, 10),
(2024, 10, 28, 19, 0),
(2024, 10, 28, 20, 0),
(2024, 10, 28, 21, 0),
(2024, 10, 28, 22, 0),
(2024, 10, 28, 23, 0);

-- --------------------------------------------------------

--
-- Table structure for table `stats_month`
--

CREATE TABLE `stats_month` (
  `Year` smallint(6) NOT NULL DEFAULT 0,
  `Month` tinyint(4) NOT NULL DEFAULT 0,
  `Hits` bigint(20) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=FIXED;

--
-- Dumping data for table `stats_month`
--

INSERT INTO `stats_month` (`Year`, `Month`, `Hits`) VALUES
(2024, 1, 0),
(2024, 2, 0),
(2024, 3, 0),
(2024, 4, 0),
(2024, 5, 0),
(2024, 6, 0),
(2024, 7, 0),
(2024, 8, 0),
(2024, 9, 0),
(2024, 10, 454),
(2024, 11, 0),
(2024, 12, 0);

-- --------------------------------------------------------

--
-- Table structure for table `stats_year`
--

CREATE TABLE `stats_year` (
  `Year` smallint(6) NOT NULL DEFAULT 0,
  `Hits` bigint(20) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=FIXED;

--
-- Dumping data for table `stats_year`
--

INSERT INTO `stats_year` (`Year`, `Hits`) VALUES
(2024, 454);

-- --------------------------------------------------------

--
-- Stand-in structure for view `theuserprofile`
-- (See below for the actual view)
--
CREATE TABLE `theuserprofile` (
`UserID` int(11)
,`Username` varchar(50)
,`FirstName` varchar(50)
,`LastName` varchar(50)
,`CompleteName` varchar(100)
,`BirthDate` datetime
,`HomePhone` varchar(24)
,`Photo` varchar(50)
,`Notes` longtext
,`ReportsTo` int(11)
,`Gender` varchar(10)
,`Password` varchar(255)
,`Email` varchar(255)
,`Activated` enum('Y','N')
,`Profile` longtext
,`UserLevel` int(11)
,`Avatar` varchar(255)
,`ActiveStatus` tinyint(1)
,`MessengerColor` varchar(255)
,`CreatedAt` datetime
,`CreatedBy` varchar(20)
,`UpdatedAt` datetime
,`UpdatedBy` varchar(20)
);

-- --------------------------------------------------------

--
-- Table structure for table `tracks`
--

CREATE TABLE `tracks` (
  `track_id` int(11) NOT NULL,
  `letter_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('create','update','disposisi','selesai') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `unit_id` int(11) NOT NULL,
  `nama_unit` varchar(100) NOT NULL,
  `kode_unit` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`unit_id`, `nama_unit`, `kode_unit`, `created_at`) VALUES
(1, 'Rektorat', 'REC', '2025-06-01 09:11:58'),
(2, 'Fakultas Teknik', 'FT', '2025-06-01 09:11:58'),
(3, 'BAAK', 'BAAK', '2025-06-01 09:11:58');

-- --------------------------------------------------------

--
-- Table structure for table `userlevelpermissions`
--

CREATE TABLE `userlevelpermissions` (
  `UserLevelID` int(11) NOT NULL,
  `TableName` varchar(255) NOT NULL,
  `Permission` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `userlevelpermissions`
--

INSERT INTO `userlevelpermissions` (`UserLevelID`, `TableName`, `Permission`) VALUES
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksaddsp', 0),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinkschecksp', 0),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksdeletesp', 0),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksmovesp', 0),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}help', 0),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}help_categories', 0),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}home.php', 8),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadaboutus', 8),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadhelponline', 8),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadtermsconditions', 8),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}printtermsconditions', 8),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}userlevelpermissions', 0),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}userlevels', 0),
(-2, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}users', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}announcement', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksaddsp', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinkschecksp', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksdeletesp', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksmovesp', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}help', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}help_categories', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}home.php', 8),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}languages', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadaboutus', 8),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadhelponline', 8),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadtermsconditions', 8),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}printtermsconditions', 8),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}settings', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}theuserprofile', 44),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}userlevelpermissions', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}userlevels', 0),
(0, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}users', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}announcement', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksaddsp', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinkschecksp', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksdeletesp', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}breadcrumblinksmovesp', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}help', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}help_categories', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}home.php', 8),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}languages', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadaboutus', 8),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadhelponline', 8),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}loadtermsconditions', 8),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}printtermsconditions', 8),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}settings', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}theuserprofile', 44),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}userlevelpermissions', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}userlevels', 0),
(1, '{1CC1ED58-B5F5-4803-92AE-63548E8546D7}users', 0);

-- --------------------------------------------------------

--
-- Table structure for table `userlevels`
--

CREATE TABLE `userlevels` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Hierarchy` varchar(255) DEFAULT NULL,
  `Level_Origin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `userlevels`
--

INSERT INTO `userlevels` (`ID`, `Name`, `Hierarchy`, `Level_Origin`) VALUES
(-2, 'Anonymous', NULL, NULL),
(-1, 'Administrator', NULL, NULL),
(0, 'Default', NULL, NULL),
(1, 'Operator', NULL, -2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `CompleteName` varchar(100) NOT NULL,
  `BirthDate` datetime DEFAULT NULL,
  `HomePhone` varchar(24) DEFAULT NULL,
  `Photo` varchar(50) DEFAULT NULL,
  `Notes` longtext DEFAULT NULL,
  `ReportsTo` int(11) DEFAULT NULL,
  `Gender` varchar(10) NOT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Activated` enum('Y','N') DEFAULT NULL,
  `Profile` longtext DEFAULT NULL,
  `UserLevel` int(11) DEFAULT 0,
  `Avatar` varchar(255) DEFAULT NULL,
  `ActiveStatus` tinyint(1) DEFAULT NULL,
  `MessengerColor` varchar(255) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `CreatedBy` varchar(20) DEFAULT NULL,
  `UpdatedAt` datetime DEFAULT NULL,
  `UpdatedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `FirstName`, `LastName`, `CompleteName`, `BirthDate`, `HomePhone`, `Photo`, `Notes`, `ReportsTo`, `Gender`, `Password`, `Email`, `Activated`, `Profile`, `UserLevel`, `Avatar`, `ActiveStatus`, `MessengerColor`, `CreatedAt`, `CreatedBy`, `UpdatedAt`, `UpdatedBy`) VALUES
(1, 'masino.sinaga', 'Masino', 'Sinaga', 'Masino Sinaga', '1973-01-22 00:00:00', '081220907593', '1_masino.sinaga.jpg', NULL, NULL, 'Male', '$2y$10$BaN71m1gHmhovdwZdYKWUeKNv1Sn6.Ro6GLTEuYsaHPGjtLG/da6O', 'masino.sinaga@gmail.com', 'Y', '{\"LastPasswordChangedDate\":\"2024\\/11\\/17\",\"login-global-masino.sinaga\":[1731812383.258871,\"Tzo0MzoiU3ltZm9ueVxDb21wb25lbnRcUmF0ZUxpbWl0ZXJcUG9saWN5XFdpbmRvdyI6Mjp7czoyNjoibG9naW4tZ2xvYmFsLW1hc2luby5zaW5hZ2EiO2Q6MTczMTgxMjEyNS42Nzg4MTM7czo4OiIAAAACAAAA8CI7aToxNTt9\"],\"login-global-::1\":[1731812383.284608,\"Tzo0MzoiU3ltZm9ueVxDb21wb25lbnRcUmF0ZUxpbWl0ZXJcUG9saWN5XFdpbmRvdyI6Mjp7czoxNjoibG9naW4tZ2xvYmFsLTo6MSI7ZDoxNzMxODEyMTI1LjcwODU0ODtzOjg6IgAAAAIAAADwIjtpOjE1O30=\"],\"login-local-masino.sinaga-::1\":[1731812383.289554,\"Tzo0MzoiU3ltZm9ueVxDb21wb25lbnRcUmF0ZUxpbWl0ZXJcUG9saWN5XFdpbmRvdyI6Mjp7czoyOToibG9naW4tbG9jYWwtbWFzaW5vLnNpbmFnYS06OjEiO2Q6MTczMTgxMjEyNS43MTI4ODY7czo4OiIAAAACAAAA8CI7aTozO30=\"],\"UserImage\":\"\\/9j\\/4AAQSkZJRgABAQEAYABgAAD\\/\\/gA8Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2ODApLCBxdWFsaXR5ID0gMTAwCv\\/bAEMAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf\\/bAEMBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf\\/AABEIACgAKAMBIgACEQEDEQH\\/xAAfAAABBQEBAQEBAQAAAAAAAAAAAQIDBAUGBwgJCgv\\/xAC1EAACAQMDAgQDBQUEBAAAAX0BAgMABBEFEiExQQYTUWEHInEUMoGRoQgjQrHBFVLR8CQzYnKCCQoWFxgZGiUmJygpKjQ1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4eLj5OXm5+jp6vHy8\\/T19vf4+fr\\/xAAfAQADAQEBAQEBAQEBAAAAAAAAAQIDBAUGBwgJCgv\\/xAC1EQACAQIEBAMEBwUEBAABAncAAQIDEQQFITEGEkFRB2FxEyIygQgUQpGhscEJIzNS8BVictEKFiQ04SXxFxgZGiYnKCkqNTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqCg4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2dri4+Tl5ufo6ery8\\/T19vf4+fr\\/2gAMAwEAAhEDEQA\\/APni41d4opZdz4VHZgctnaC5OAMngHAH0FeefB3\\/AIJh\\/td\\/ttuP2i4bbTrTwbPrWpaF4X0nULjUE0ez0fRZLaO48oxNNFdSW1673GpXYjgivb6yu47cubY2lt0DSv0J4yfQnPQkj\\/Hr71\\/Ux+yf8fdC\\/Zy\\/4J6\\/DofGfSdU8O6nocfibSNVs9BtrS6xbtrl7qulanDi+jM1tq2gazomtrcRvPJImqRPKzTTrv8Az3P8XWwlGjChiY4R1XVcpuMJzmqUabjThCf2ZznGMppNQbirXkj9X4TwVHGYmtUrZdPMnSqYanGkp1adGkq7rOdWdSnopQp0Z1IQlJc8YVJLSE2v4eP2rP8Agmp+0R8JNT1cJ4QkisI4rcaleWt0t1YmCMwtIpv52dXtvPt82tsHARVTYpEpLaP7HPi34i+D9X1r4D\\/Ea5mu20jw5Z+LvBd5dyiSePRbieOC60qG4Mkn2q1h+1W1zZwoT\\/Zvl6jaljDHFFa\\/0H\\/8FC\\/2i\\/BcXhrwhHP4O8da5qnxeXWdV+HGm6RpE1xN4j8K6ZHFNPq1vbp5jrb2U0jRXszrPJHMBFFb3ErpE35u3\\/w48IponwG+MFjE1nr+peEfiN4Y8R2Exea90+9tbzwtrWi6ffyT22nyRyL4d8UWk0arbMzK\\/QiOQxedwznmZY5cuPhReGv7OliIR5JVZOcacHZTlzWn7smoKKd7ytGx9fx5wjk+WwVXKXiPr8YyrYjCup7WnRpUqNTE17zcYRg40oOooOo6keWC5HKetxtSYAneeODxj1B989+3eisNzvLHoPvDAx9Ac47Ht69T3K+2UZdEktOi8u936r1sfj7lFdfzHtEQTkHI5PQgAjr078+445ya\\/ov\\/AGDfjxp\\/xE\\/ZC8d+DdX8N2niDxd8Pbqw0e6tNRv7oWGs2B8N2ll4b1nUzBG9zZWqaN4aOnamiJdm+k0C6v1E1xd\\/ZIv50ZG3EjIGew78dTjjt+PbNfr3\\/wAEjvAGu+KPEX7Q\\/i3R729ks\\/BPgPwnp2raRas7Wcsni7W9TvLDUdTgUkNFZ2ng\\/WbGNmRmgttWvrhTGkckg8DiLB\\/WspxcoRTr4enKrSl7P2soxStW9yz5oulzN3TUXGM2rwTPq+DcxeXcQZc5yaw1fE0adaHtnRjOoqkZYVualFxlHEqCTUoScZzpqcVUlf5u\\/wCC1vx28F6L4q\\/ZmPhjSPD934m+HGg6npFtL4c1zwkfC11p2pzaPJqmizaFDqMup2+mS2Vi80NmtnIIL2XSYVup1ndW+bb7xrZat8BPh\\/ollplrpV1H438eXutQw2sNtG0mq6J8NtctoLNEA8u0s7S706yIWOP9\\/YyQx7RHMrfRn\\/BYLxB8O28TaBfeI\\/DHxC0jWtMsVgkvBaQ3Ph68tJVuba4lshb2Mi6heQ2s0kUASVrmUXRhmksoxLNH+eOq\\/FfQtB\\/Zluvit4y8LXPgKy8I+NfCGgaT4cvJpbjxQ\\/g3XZ7zR9U8RapalIw2qPqt74enXSQZLm00HQr0+YbmeG0TxuFMNGu6NeNFScI1acp80ZRlDmU6LSjNxXJKPLFqKcpPq7s\\/RvFDMsNhsHDAYHE4mOFrU8JiJUq8eSrRxkqbo42HOqUZ1I1I1JSanOXJST5lD93E6PK479c4z9eh5\\/Xk8elFcj4R8deDfH+mR6x4M8TaN4l06VQxn0m+guWgLEjy7y2VhdWM4IIe3vIYJ0IIeNSMUV97r1TT7PRr1R+FpXSaSaaTT+Ldrqt1bXtq13t5b4j+J+o6jqUegeHUuLITh1l1KJYprtGJURptkIitYm3HfIpkm3BRG8ILSL\\/Qx\\/wby\\/FOy8FfHv48+BfEGp2+m6X4z+CkXj27vtSulitoX+FXiS0iuJ7y8uXChbXSfH2r3lzcXDqFgtridyqK2Cit6aVNxcUk7pX1bd5K93fs7W2S2Q6sVOlUhLaUXe2+nvJp+TS8n1Ry3\\/BTn9r+LSvHfwo8feBPgf4fv2+Kfxf1Twr4B8KeL\\/DwubDVvhtoNnsuviSNNC291oOv+JZZ59S0u6iWH+xrtdEjmtvtcGp3Opfjl\\/wWA+GV14A\\/ZzbxBqMUukzfEjxl4SGkaSwWNDZ3txqus36RpGqxOmlXfhpLINAFhG8NEPLlVEKKyoYfD4apF4bD0MP7arGrV9hRp0vaVZcnNUnyRjzSbW8rvptodGY4zGYmM6eKxeLxccGqmDwv1rE18S6GGpRvCjSdapNwppt2jG0V0SR\\/NRojaz4fvE1LRdVv9CvbBGuW1PTL250+\\/twqs7GG6tZIZYkUAs7JLuHlgKpLblKKK9eVm7tJuy1a8k\\/8\\/v8AS3zybik1fW99WtmuzR\\/\\/2Q==\",\"Sessions\":[]}', 1, NULL, 0, NULL, '2024-11-17 09:39:52', 'admin', '2024-11-17 09:39:52', 'admin'),
(2, 'helena.malau', 'Helena', 'Malau', 'Juniar Helena Waty Malau', '1973-06-04 00:00:00', '085761642128', '2_helena.malau.jpg', NULL, NULL, 'Male', '$2y$10$6zdiMR9hMSpNpLOkkiW5vO3Fnytp2y2WkOLS/VFRIyNiGzKGHlWgO', 'helena.malau@gmail.com', 'Y', '{\"LastPasswordChangedDate\":\"2024\\/11\\/17\",\"Sessions\":[],\"UserImage\":\"\\/9j\\/4AAQSkZJRgABAQEAYABgAAD\\/\\/gA8Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2ODApLCBxdWFsaXR5ID0gMTAwCv\\/bAEMAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf\\/bAEMBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf\\/AABEIACgAKAMBIgACEQEDEQH\\/xAAfAAABBQEBAQEBAQAAAAAAAAAAAQIDBAUGBwgJCgv\\/xAC1EAACAQMDAgQDBQUEBAAAAX0BAgMABBEFEiExQQYTUWEHInEUMoGRoQgjQrHBFVLR8CQzYnKCCQoWFxgZGiUmJygpKjQ1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4eLj5OXm5+jp6vHy8\\/T19vf4+fr\\/xAAfAQADAQEBAQEBAQEBAAAAAAAAAQIDBAUGBwgJCgv\\/xAC1EQACAQIEBAMEBwUEBAABAncAAQIDEQQFITEGEkFRB2FxEyIygQgUQpGhscEJIzNS8BVictEKFiQ04SXxFxgZGiYnKCkqNTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqCg4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2dri4+Tl5ufo6ery8\\/T19vf4+fr\\/2gAMAwEAAhEDEQA\\/AP647GSJYjdyukS2iGaWWVgkYjiRpHZ2bAVUVSzHOAAST1NfhH4O+KF5\\/wAFSv2yvGXhlfE+o6L+yX+z\\/wDaBrWpWN3a6fpmo6fa3EtpLf32qakj6fZnxXf2j\\/Z5ZIxdDSbZ5opiulLLF90\\/8FHPjTqHwE\\/YQ\\/aZ+I+kTtb63pnwu13SNAuEIWWHXPFkKeFNMnhwQTLbXesJdRgENuhBUhgK+Bf+CJvwI1nR\\/wDgnv8AFrSNb8beIPgt8T\\/EnxO1fxFfeObPTNAufEa+Fk8K+G5fBtxpSeMNK1rSItP1jwvMb3T9R1HQru6hg1+9vtOWxvJ4buD4rjDH+xhhMGqij9YnzzpqtGjUqwp3nPlcmrqnCEr2akqlSjKFqkYX\\/SOBsqeJnjcw9lOcqKhhqNZYeeIhh6teUKcXKEIyfNOdWDi3FxcKVaFROnOaPtT43\\/si\\/sD+HfA+s654P+HvwYu\\/D89rKt94ylGgazDJKFKSXVz4rkluBJcRMAz3D3wIcjDqwXH55\\/8ABMr4xa3+zf8Atp23wSTxDpms\\/BH42Q6wPhxf6RqFvqWhtrWh2a6rPbwX1jNLZvfPpVnq1teWBEd0NUbQrp3la6lWf7a+J\\/h\\/4PeGv2OLH4Y6x+0B4n8b+KNe8RaTr3\\/Cf3Ettb+OdeiuLrUle5srnQdEttO\\/1Fk6w3ENikV3BZpqL7reZLw\\/ll8fPDnhv4PeH\\/2T\\/il4U+MXi7x5c\\/Dj9s34C61Fq3jbVV1DWf8AhFvFOqXU\\/i\\/woup3O7VEtL+w0dBFbalcXe26c6bbCxtryOO2+O4UrRpZo8RCc6UcZ7ScKFKcquFh7PlnNOTk+WSpzpu9nJKooSWt1+o8X5VVrcNVsPXozrzyySoTxeMpKhjqjnUVKnUjTlCLqU3VhUiul6TnG\\/K0f2n3cbSB3H+r2AgDj2P1JPA7euOhKtWjfadPtrkFG+1WcFwpXlGMsKS5TuUJb5T6c8Zor9mP5mP54\\/8AgsXokuufsEfFHQ1XH9p6v4JgMbAmOWGLxNp93OrEclUt7WWUjBLGPbgkkH85\\/wDgnx+2v4MurbUv2HvjDp1tYWmm+B44NG+I1nqc2m3Gt2Bi0jRdM8Ja3Z29qLeG40nTbq4tdM8QDUo3ubWLTNJaxe8tVu9Q\\/Vr\\/AIKKT6d4k\\/ZNv5L+4aLTLHX9OvtdCW8t5cS2Vpo3iExwWthCHnv7241CSyhsrC3SWa8vJIIYo3dkU\\/yI\\/sV+Ob+\\/\\/bN8N+Pr\\/QCfDfjPTNS8N2Npdwb7FfDcukNY6fb6lPA7W4u9VF\\/ZxWtxL5kP9qR2ccG55beeL8m47w7xWLxs6kIzpZfk08TStK1SOIu5Jxa1SnTjVi4tcrUJbySa\\/dfDfFvB5dg1RqTpV8dn0KFRq6h7GHs1duyXNTqSoyg7qUZTi1aN2\\/63f2g\\/hZqN\\/wDD7TNK0K+1LxF4XtI0fQfEU+s6P4Xj8DWBECWOm+CdK8LLoOmX9rbWKG2t\\/wC3bfUpmEUEuq3mqTrkfzJf8Fc\\/jn4R8DeGvgf+yz4c1Ge\\/+JOofFfwZ8XNf1BXsmntvDHhC01yw0mXWpdNs7HSv7Q8Q+KtQtb6yh0+3RI7Tw1qMtxbWiXlk91+in7ROpax4J1rRltNZ1q+8Fw24u4dEur7UJra1mUylltrTzZrUiJ+QSFXO7aATX8zH7X2peLf2hP2j5fFnhLQNc8VH4e+G7LQtZbw9pt1rN0bxdTvJ9K0iGDTlnupLyRnu55liWRra3t7i4uBGuWHkcDVKOKqxnK8pQhVlFNJ8tR0lTcptLVSapxhza3SSbb0+z8QMZiVldGg5KCVWjTqNRUHUpqvGo1CMfdVoqpUqqKjHWc3BT55T\\/1Df2JvjZZfHr9lf4K\\/EyCZbm51jwXp9rrAWTc9trOkxDTdRjlUFjGzzW32qOJjuFtcQHJ3AsV+GX\\/Bt\\/8AGb4han8Gvir8E\\/iBoPinR4PBN\\/4K8VeG5vF2kaj4cvZj4u8O+Vqttb6VrkNpfR29xHoumarbfZoZrOSSfU5BKkisrFfsWWzqzwOGdeM4VoU1SqqakpOpRbpSm1JJ++4c602kfzVnNClh8zxlOhKMqEqvtqLg04qlXjGvTgmm1+7jUVN67xP3h8RfCT4W618G\\/FPgqfw1f69pPjbw5rnhfUNRtLW21fWhFrFhdaTPdxfbNQ0hoYXhmcvBYT2aSQGSOZokkeU\\/iX8Ov+CP\\/wAEfhl4X1O0htfi\\/B8R\\/Gkeo+F9BvfEl34dl0bUtFttZ8B+IPC2r63pmjzQ6b4S1vRte8HJqsdrpUk4a21NrJdUu7holjKK8fNMJh8bHmxFNT9pho0prVXhWkoSTa97RNuPvaNvdNo9fKMwxmX88cLXnT9niXiYu97VcNF1KcrP3bNpKaUVdJNcskpLzvxX+xb4g8aa1qFh4gv1m0zTNPFjfW2qQzw3U+qxma2vLG8tHIEUtvKhS8LqziQy4UsNo3fhl8N\\/2Y\\/2FPht8Q9J8LeHbXx\\/8afGEuhvP4W8NeG0n0Lw5cafFqieHH8Rap9mj0e1lsrjWtU1jULK3l1C\\/R5bGyubW2uBLKpRX5HwHl9ClmuZTjKs3g5OnQjKo3CLnPEU\\/auCSi6sYU+WLa5UpzfI5OLj+6cfY+vicny2hU9nGni1CrX9nBRlNUoYasqPPrJUpVanPJJ87dOmlNRi4y+0vg23g6fU3+IPhTRLvQb3xNp\\/hjRtTs9Y01NP1OC8tYdSWKzuTHJcxT20FhbxtbtZX9zbFS6q6ymaFCiiv3JYzEThCpz8sqsXUnypJOblJN21tflTaWl231P5+q4elGrUhZyVOShDmd2oxjBpX3dtlfol2R\\/\\/2Q==\"}', 0, NULL, 0, NULL, '2024-11-17 09:57:28', 'admin', '2024-11-17 10:14:05', 'admin');

-- --------------------------------------------------------

--
-- Structure for view `theuserprofile`
--
DROP TABLE IF EXISTS `theuserprofile`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `theuserprofile`  AS SELECT `users`.`UserID` AS `UserID`, `users`.`Username` AS `Username`, `users`.`FirstName` AS `FirstName`, `users`.`LastName` AS `LastName`, `users`.`CompleteName` AS `CompleteName`, `users`.`BirthDate` AS `BirthDate`, `users`.`HomePhone` AS `HomePhone`, `users`.`Photo` AS `Photo`, `users`.`Notes` AS `Notes`, `users`.`ReportsTo` AS `ReportsTo`, `users`.`Gender` AS `Gender`, `users`.`Password` AS `Password`, `users`.`Email` AS `Email`, `users`.`Activated` AS `Activated`, `users`.`Profile` AS `Profile`, `users`.`UserLevel` AS `UserLevel`, `users`.`Avatar` AS `Avatar`, `users`.`ActiveStatus` AS `ActiveStatus`, `users`.`MessengerColor` AS `MessengerColor`, `users`.`CreatedAt` AS `CreatedAt`, `users`.`CreatedBy` AS `CreatedBy`, `users`.`UpdatedAt` AS `UpdatedAt`, `users`.`UpdatedBy` AS `UpdatedBy` FROM `users` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`Announcement_ID`) USING BTREE;

--
-- Indexes for table `breadcrumblinks`
--
ALTER TABLE `breadcrumblinks`
  ADD PRIMARY KEY (`Page_Title`) USING BTREE;

--
-- Indexes for table `dispositions`
--
ALTER TABLE `dispositions`
  ADD PRIMARY KEY (`disposition_id`),
  ADD KEY `letter_id` (`letter_id`),
  ADD KEY `dari_unit_id` (`dari_unit_id`),
  ADD KEY `ke_unit_id` (`ke_unit_id`);

--
-- Indexes for table `help`
--
ALTER TABLE `help`
  ADD PRIMARY KEY (`Help_ID`) USING BTREE;

--
-- Indexes for table `help_categories`
--
ALTER TABLE `help_categories`
  ADD PRIMARY KEY (`Category_ID`) USING BTREE;

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`Language_Code`) USING BTREE;

--
-- Indexes for table `letters`
--
ALTER TABLE `letters`
  ADD PRIMARY KEY (`letter_id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD KEY `penerima_unit_id` (`penerima_unit_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`Option_ID`) USING BTREE;

--
-- Indexes for table `stats_counter`
--
ALTER TABLE `stats_counter`
  ADD PRIMARY KEY (`Type`,`Variable`) USING BTREE;

--
-- Indexes for table `stats_counterlog`
--
ALTER TABLE `stats_counterlog`
  ADD PRIMARY KEY (`IP_Address`) USING BTREE;

--
-- Indexes for table `stats_date`
--
ALTER TABLE `stats_date`
  ADD PRIMARY KEY (`Date`,`Month`,`Year`) USING BTREE;

--
-- Indexes for table `stats_hour`
--
ALTER TABLE `stats_hour`
  ADD PRIMARY KEY (`Date`,`Hour`,`Month`,`Year`) USING BTREE;

--
-- Indexes for table `stats_month`
--
ALTER TABLE `stats_month`
  ADD PRIMARY KEY (`Year`,`Month`) USING BTREE;

--
-- Indexes for table `stats_year`
--
ALTER TABLE `stats_year`
  ADD PRIMARY KEY (`Year`) USING BTREE;

--
-- Indexes for table `tracks`
--
ALTER TABLE `tracks`
  ADD PRIMARY KEY (`track_id`),
  ADD KEY `letter_id` (`letter_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `kode_unit` (`kode_unit`);

--
-- Indexes for table `userlevelpermissions`
--
ALTER TABLE `userlevelpermissions`
  ADD PRIMARY KEY (`UserLevelID`,`TableName`) USING BTREE;

--
-- Indexes for table `userlevels`
--
ALTER TABLE `userlevels`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `Announcement_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `dispositions`
--
ALTER TABLE `dispositions`
  MODIFY `disposition_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `help`
--
ALTER TABLE `help`
  MODIFY `Help_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `help_categories`
--
ALTER TABLE `help_categories`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `letters`
--
ALTER TABLE `letters`
  MODIFY `letter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `Option_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tracks`
--
ALTER TABLE `tracks`
  MODIFY `track_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dispositions`
--
ALTER TABLE `dispositions`
  ADD CONSTRAINT `dispositions_ibfk_1` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`letter_id`),
  ADD CONSTRAINT `dispositions_ibfk_2` FOREIGN KEY (`dari_unit_id`) REFERENCES `units` (`unit_id`),
  ADD CONSTRAINT `dispositions_ibfk_3` FOREIGN KEY (`ke_unit_id`) REFERENCES `units` (`unit_id`);

--
-- Constraints for table `letters`
--
ALTER TABLE `letters`
  ADD CONSTRAINT `letters_ibfk_1` FOREIGN KEY (`penerima_unit_id`) REFERENCES `units` (`unit_id`);

--
-- Constraints for table `tracks`
--
ALTER TABLE `tracks`
  ADD CONSTRAINT `tracks_ibfk_1` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`letter_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
