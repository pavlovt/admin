<?
require_once("class/storeMonger/class.smSalesPerson.php");

// check if loggedin
$salesPerson = new smSalesPerson($db);
$ck = (isset($checkLoginProfileVersion) ? $checkLoginProfileVersion : TRUE);
$salesPersonId = $salesPerson->checkLoggedIn($ck);

if (!$salesPersonId) {
	header("Location: ".$storeFrontLoginLocation);
	exit;
}

// DO NOT UNSET THE $salesPerson instance -- it is used in some scripts after checking for login!
?>