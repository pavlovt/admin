<?php

$privAccessAreas = array(
	"privHome" => "Home (Daily Calendar)",
	"privContent" => "Content section (All)",
    "privEventGeneral" => "Event section (Reminder, Squeeze, Infomercial, Old Players",
    "privEventBonus" => "Event section (Bonus Question)",
    "privGroup" => "Group section",
    "privCalendar" => "Calendar section",
    "privReport" => "Report section",
    "privReport_PlayerDetails" => "Report section (Player Details screen)",
    "privReport_PlayerCheck" => "Report section (Player Activity Check)",
    "privAdmin" => "Administrator section",
    "privMonitoring" => "Monitoring section"
);
    
/*
possible account types:
/for each accout their permissions are int he SALESPERSON table in the JSON profile "permissionsArray":{"admin":1,"accountantManager":1,"accountant":1,"dealerManager":1,"dealer":1}
---
admin
marketingmanager
contentmanager
monitoring
customersupport
*/

/* FOR EACH $privAccessAreas specify here who can read, create and monify */
$privs = array(
	"privHome" => array(
		"read" => array("admin", "marketingmanager"),
		"create" => array("admin", "marketingmanager"),
		"modify" => array("admin", "marketingmanager")
	),
    "privEventGeneral" => array(
		"read" => array("admin", "marketingmanager"),
		"create" => array("marketingmanager"),
		"modify" => array("marketingmanager")
	),
    "privEventBonus" => array(
		"read" => array("admin", "marketingmanager"),
		"create" => array("admin", "marketingmanager"),
		"modify" => array("admin", "marketingmanager")
	),
	"privContent" => array(
		"read" => array("admin", "marketingmanager", "contentmanager"),
		"create" => array("marketingmanager", "contentmanager"),
		"modify" => array("marketingmanager", "contentmanager")
	),
	"privGroup" => array(
		"read" => array("admin", "marketingmanager"),
		"create" => array("admin", "marketingmanager"),
		"modify" => array("admin", "marketingmanager")
	),
    "privCalendar" => array(
		"read" => array("admin", "marketingmanager"),
		"create" => array("admin", "marketingmanager"),
		"modify" => array("admin", "marketingmanager")
	),
	"privReport" => array(
		"read" => array("admin", "marketingmanager"),
		"create" => array("admin", "marketingmanager"),
		"modify" => array("admin", "marketingmanager")
	),
    "privReport_PlayerDetails" => array(
		"read" => array("admin", "marketingmanager", "customersupport"),
		"create" => array("admin", "marketingmanager", "customersupport"),
		"modify" => array("admin", "marketingmanager", "customersupport")
	),
    "privReport_PlayerCheck" => array(
		"read" => array("admin", "marketingmanager", "contentmanager"),
		"create" => array("admin", "marketingmanager", "contentmanager"),
		"modify" => array("admin", "marketingmanager", "contentmanager")
	),
    "privAdmin" => array(
		"read" => array("admin", "marketingmanager", "contentmanager"),
		"create" => array("admin", "marketingmanager", "contentmanager"),
		"modify" => array("admin", "marketingmanager", "contentmanager")
	),
	"privMonitoring" => array(
		"read" => array("admin", "marketingmanager", "monitoring"),
		"create" => array("monitoring"),
		"modify" => array("monitoring")
	)
);

$currentUserPriv = isset($salesPerson->profileArray["permissionsArray"]) ? $salesPerson->profileArray["permissionsArray"] : array();
if (!is_array($currentUserPriv)) { $currentUserPriv = array(); }

function checkUserHasPriv($userPrivs, $accessPrivs) {
	$hasPriv = 0;
	foreach ($userPrivs as $priv => $active) {
		if (($active) && (in_array($priv, $accessPrivs))) { $hasPriv = TRUE; break; }
	}
	return $hasPriv;
}

foreach ($privAccessAreas as $area => $areaCaption) {
	foreach ($privs[$area] as $permission => $whoHasAccessArray) {
		$privs[$area][$permission] = checkUserHasPriv($currentUserPriv, $privs[$area][$permission]);
	}

}


/*
$canAdmin = FALSE;
if (isset($salesPerson->profileArray["permissionsArray"]["admin"]) && ($salesPerson->profileArray["permissionsArray"]["admin"])) { $canAdmin = TRUE; }

$canFinAdmin = FALSE;
if (isset($salesPerson->profileArray["permissionsArray"]["accountantManager"]) && ($salesPerson->profileArray["permissionsArray"]["accountantManager"])) { $canFinAdmin = TRUE; }

$canAccountant = FALSE;
if (isset($salesPerson->profileArray["permissionsArray"]["accountant"]) && ($salesPerson->profileArray["permissionsArray"]["accountant"])) { $canAccountant = TRUE; }
*/

?>