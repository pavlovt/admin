<?
require_once("include/config.frontEnd.php");
require_once("common/sessionInit.php");

require_once("class/storeMonger/class.smSalesPerson.php");

require_once("checkLogin.php");

// logout
$salesPerson = new smSalesPerson($db);
if ($salesPerson->loadById($_SESSION["salesPersonId"])) {
	$salesPerson->logOut();
}

$_SESSION["restoreSessionPage"] = NULL;

header("Location: ".$storeFrontIndexLocation);
unset($smSalesPerson);
	
?>