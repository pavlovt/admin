<?

require_once("include/config.frontEnd.php");
require_once("common/sessionInit.php");

require_once("class/storeMonger/class.smSalesPerson.php");

$error = NULL;

if ((isset($_POST["action"])) && ($_POST["action"] == "login")) {

	// check login
	$salesPerson = new smSalesPerson($db);
	$salesPersonId = $salesPerson->login($_POST["userName"], $_POST["password"], session_id());
	if (!$salesPersonId) {
		$error = "Incorrect username or password. Please try again (make sure CAPS lock is not on)";
	} else {
		
		// 20100311, check if profile needs to be upgraded
		if ($salesPerson->profileVersionUpgradeNeeded !== FALSE) {
			// yep, redirect to the appropriate page
			header("Location: upgradeProfileVersion.".(int)$salesPerson->profileVersionUpgradeNeeded.".php");
			exit;
		}
		
		// redirect to a different location?
		if ((isset($_SESSION["redirectAfterLogin"])) && (strlen($_SESSION["redirectAfterLogin"]) > 0)) {
			$redirectTo = $_SESSION["redirectAfterLogin"];
			$_SESSION["redirectAfterLogin"] = '';
		} else {
			$redirectTo = $storeFrontIndexLocation;
		}
		
		header("Location: ".$redirectTo);
		exit;
	}
	unset($smSalesPerson);
	
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Login</title>
	
	<?@require_once("commonHtmlHead.php");?>
</head>

<body leftmargin="0" topmargin="0" rightmargin="0" bgcolor="#ffffff" class="yui-skin-sam">

<form action="login.php" method="post">
<input type="hidden" name="action" value="login">

<? if ($error) { ?><p style="margin: 5px;"><span class="error">Error: <?=$error?></span></p><? } ?>
<? 
if ((isset($_SESSION["messages"]["loginScreen"])) && (is_array($_SESSION["messages"]["loginScreen"]))) { 
	?><p style="margin: 5px;"><span class="error">Please review the following issues:<ul><?
	foreach ($_SESSION["messages"]["loginScreen"] as $m) {
		?><li class="error"><?=$m?></li><?
	}
	?></ul></span></p><?
	unset($_SESSION["messages"]["loginScreen"]);
}
?>
<table cellspacing="2" cellpadding="2">
<tr>
	<td colspan="2"><strong>Please login</strong></td>
</tr>
<tr>
	<td style="text-align: right;"><strong>Username</strong></td>
	<td><input type="text" name="userName" value="" style="width: 150px;" maxlength="50"></td>
</tr>
<tr>
	<td style="text-align: right;"><strong>Password</strong></td>
	<td><input type="password" name="password" value="" style="width: 150px;" maxlength="50"></td>
</tr>
<tr>
	<td style="text-align: right;">&nbsp;</td>
	<td><input type="submit" value="Login" class="button"></td>
</tr>
</table>

</form>

</body>
</html>

