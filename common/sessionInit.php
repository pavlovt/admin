<?php

error_reporting(E_ERROR);

function isBuggyIe() {
    $ua = $_SERVER['HTTP_USER_AGENT'];
    // quick escape for non-IEs
    if (0 !== strpos($ua, 'Mozilla/4.0 (compatible; MSIE ')
        || false !== strpos($ua, 'Opera')) {
        return false;
    }
    // no regex = faaast
    $version = (float)substr($ua, 30);
    return (
        $version < 6
        || ($version == 6  && false === strpos($ua, 'SV1'))
    );
}

// usage:
isBuggyIe() || ob_start("ob_gzhandler");

// common stuff
session_start();

// verify variables
if ((!$logDir) || (!is_writable($logDir))) { trigger_error("sessionInit.php: logDir is not set or is not writable", E_USER_ERROR); exit; }

// set timezone for php5	
if ((isset($defaultTimeZone)) && (function_exists("date_default_timezone_set"))) {
	date_default_timezone_set($defaultTimeZone);
}

// seed the random generators
mt_srand(time());
srand(time());

// format GET, POST and COOKIE
if (version_compare(PHP_VERSION, "4.1.0") < 0) {
        $_GET = $HTTP_GET_VARS;
        $_POST = $HTTP_POST_VARS;
        $_COOKIE = $HTTP_COOKIE_VARS;
}

// unescape
if (get_magic_quotes_gpc() == 1) {
        if (isset($_GET)) { foreach ($_GET as $k => $v) { $_GET[$k] = stripSlashes($v); } }
        if (isset($_POST)) { foreach ($_POST as $k => $v) { $_POST[$k] = stripSlashes($v); } }
        if (isset($_COOKIE)) { foreach ($_COOKIE as $k => $v) { $_COOKIE[$k] = stripSlashes($v); } }
}

// set $REQ
$_REQ = (getenv("REQUEST_METHOD") == "POST") ? $_POST : $_GET;

// include functions, etc
require_once("common/functions.php");

// set custom errorhandler
if (function_exists("webErrorHandler")) {
	$oldErrorHandler = @set_error_handler("webErrorHandler");
	$GLOBALS["errorLogDir"] = (isset($logDir) ? $logDir : "/");
	$GLOBALS["errorLogVars"] = (isset($errorLogVars) ? $errorLogVars : FALSE);
}

// init user session
if (isset($_SESSION["userSession"])) {
	$newSession = FALSE;

	$_SESSION["userSession"]["pageViews"]++;
	$_SESSION["userSession"]["duration"] = time() - $_SESSION["userSession"]["startedAt"];
	
} else {

	// generate set customer ID which will stick to their browser for time to come...
	// already set?
	$pcid = "";
	if ((isset($_COOKIE["pcid"])) && (strlen($_COOKIE["pcid"]))) {
		$pcid = ereg_replace("[^A-Za-z0-9]", "", $_COOKIE["pcid"]);	
	}

	// need to generate new?
	if (strlen($pcid) != 32) {
		$pcid = md5(date("YmdHis").@session_id()); // cookie value (MD5 hash of current date and time and session id)
	}
			
	// set the $pcid as a cookie or renew current cookie
	$cookieExpirationDate = time() + 311040000; // about ten years from now (60 * 60 * 24 * 30 * 12 * 10)	
	@setcookie("pcid", $pcid, $cookieExpirationDate, "/", $domainNoWWW, FALSE);

	// loginKnown is set to TRUE, if based on pcid we know which user this is. salutionName will be automatically set then, so we can greet the user
	$_SESSION["userSession"] = array(
		"startedAt" => time(),
		"duration" => 0,
		"pageViews" => 1,
		
		"ip" => getenv("REMOTE_ADDR"),
		"entryPage" => $_SERVER["REQUEST_URI"],
		"referrer" => getenv("HTTP_REFERER"),
		"external" => (isset($_GET["external"]) ? trim($_GET["external"]) : NULL),
		
		"pcid" => $pcid,
	
		"memberId" => NULL,
		"shopperReferredByMemberId" => NULL,
	
		"lang" => "bg",
	
		"calendarYear" => date("Y"),
		"calendarMonth" => date("m"),
		
		"cartContents" => array()
	
	);
	
}

// determine the current session year
if ((!isset($_SESSION["userSession"]["currentYear"])) || (!isset($_SESSION["userSession"]["availableYears"]))) {
	$_SESSION["userSession"]["currentYear"] = 0;
	$_SESSION["userSession"]["availableYears"] = array(0 => "All");
	for ($yr = 2007; $yr <= date("Y") + 2; $yr++) {
		$_SESSION["userSession"]["availableYears"][$yr] = $yr;
	}
}
if (isset($_GET["changeSessionYear"])) {
	$newSessionYear = (int)$_GET["changeSessionYear"];
	if (array_key_exists($newSessionYear, $_SESSION["userSession"]["availableYears"])) {
		$_SESSION["userSession"]["currentYear"] = $newSessionYear;
	}
}


$salesPersonRealName = isset($_SESSION["salesPersonName"]) ? $_SESSION["salesPersonName"] : "";
$salesPersonABBR = isset($_SESSION["salesPersonAbbr"]) ? $_SESSION["salesPersonAbbr"] : "XXX";
$salesPersonUserName = isset($_SESSION["salesPersonUserName"]) ? $_SESSION["salesPersonUserName"] : "N/A";

// connect to db
require_once("common/db.php");

// set db encodings
if (isset($setDefaultMySqlCharset)) {
	if ($db) { @mysql_query("SET NAMES '".$setDefaultMySqlCharset."'", $db) or trigger_error("sessionInit.php: unable to set default mysql charset. (on master)", E_USER_ERROR); }
	if ($dbr) { @mysql_query("SET NAMES '".$setDefaultMySqlCharset."'", $dbr) or trigger_error("sessionInit.php: unable to set default mysql charset. (on slave)", E_USER_ERROR); }
}

if (isset($setDefaultHTTPCharset)) {
	header("Content-Type: text/html; charset=".$setDefaultHTTPCharset);
}

?>