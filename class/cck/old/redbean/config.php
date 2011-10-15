<?php
//ini_set("include_path", ini_get('include_path').":/xampplite/htdocs/admin/");

ini_set("include_path", ini_get('include_path').";A:\\xampplite\\htdocs\\admin\\");

// mail config
$sendFrom = "webpublishinghouse.net@gmail.com";
$smtpInfo["host"] = 'ssl://smtp.googlemail.com';
$smtpInfo["port"] = '465';
$smtpInfo["auth"] = true;
$smtpInfo["username"] = '';
$smtpInfo["password"] = '';

define("basePath", "a:/xampplite/htdocs/admin/");

// directory where all cck files are stored
define("filePath", basePath."/attachments/");

// show this message on front end if user is not logged in and wants to browse the content
$notLoggedInMessage = "Забранен досъп за нерегистрирани потребители";

// error messages used in the site
$connectionError = "Грешка при зареждане на страницата - моля опитайте по-късно.";

// has to be different for every site - get it from the google site
$googleMapsApiKey = "";

	// contain all db connections - every key is a new db object - $db, $dbFb etc.
$dbSettings = array(
	"db" => array("host" => "127.0.0.1", "user" => "root", "password" => "", "dbName" => "admindb")
);

// object relational mapping class
require_once ( 'class/redbean/rb.php' );
require_once ( 'common/db.php' );
require_once ( 'common/functions.php' );
