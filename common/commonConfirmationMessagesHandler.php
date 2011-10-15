<?php

if (isset($_SESSION["userSession"]["pageMessages"][SECTION."-".PAGE])) {
	$messageArray = $_SESSION["userSession"]["pageMessages"][SECTION."-".PAGE];
	unset($_SESSION["userSession"]["pageMessages"][SECTION."-".PAGE]);
}
if (isset($_SESSION["userSession"]["pageErrors"][SECTION."-".PAGE])) {
	$errorArray = $_SESSION["userSession"]["pageErrors"][SECTION."-".PAGE];
	unset($_SESSION["userSession"]["pageErrors"][SECTION."-".PAGE]);
}

if ((isset($messageArray)) && (is_array($messageArray)) && (count($messageArray))) {
	if (count($messageArray) > 1) { $prefix = "&mdash; "; } else { $prefix = ""; }
	?>
	<div id="managerConfirmMessage" style="display: block; margin-bottom: 20px; font-size: 14px; font-weight: bold; background-color: #007909; padding: 10px; color: #ffffff; border: 1px solid #000000;">
	<?
	foreach ($messageArray as $msg) {
		echo $prefix.$msg."<br>";
	}
	?>
	</div>
	<script>
	function hideManagerConfirmMessage() {
		$('#managerConfirmMessage').fadeOut(600);
	}
	setTimeout('hideManagerConfirmMessage()', 5000);
	</script>
	<?
}

if ((isset($errorArray)) && (is_array($errorArray)) && (count($errorArray))) {
	if (count($errorArray) > 1) { $prefix = "&mdash; "; } else { $prefix = ""; }
	?>
	<div id="managerErrorMessage" style="display: block; margin-bottom: 20px; font-size: 14px; font-weight: bold; background-color: #c00000; padding: 10px; color: #ffffff; border: 1px solid #000000;">
	ERROR:<br><br>
	<?
	foreach ($errorArray as $msg) {
		echo $prefix.$msg."<br>";
	}
	?><br><br>
	<a style="color: #ffffff; text-decoration: underline; font-size: 10px;" href="javascript:hideManagerErrorMessage();">Hide errors</a>
	</div>
	<script>
	function hideManagerErrorMessage() {
		$('#managerErrorMessage').fadeOut(600);
	}
	//setTimeout('hideManagerErrorMessage()', 20000);
	</script>
	<?
}

?>