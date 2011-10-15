<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

//echo "<pre>"; var_dump($_POST); exit;

if (@$_GET["task"] == "edit") {
	require_once("components/com_cck/cck.edit.php");
} elseif (@$_GET["task"] == "editContentType") {
	require_once("components/com_cck/contentType.edit.php");
} elseif (@$_GET["task"] == "browseContentType") {
	require_once("components/com_cck/contentType.browse.php");
} else {
	require_once("components/com_cck/cck.browse.php");
}
?>