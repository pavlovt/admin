<?php
//ini_set("memory_limit", "500M");
//exit(ini_get("memory_limit"));

global $mainframe;

if (!$mainframe) {
	ini_set('display_errors','1');
	
	define( '_JEXEC', 1 );
	define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))));
	define( 'DS', DIRECTORY_SEPARATOR );
	
	require_once (JPATH_BASE . DS . 'includes' . DS . 'defines.php');
	require_once (JPATH_BASE . DS . 'includes' . DS . 'framework.php');
	
	$mainframe = JFactory::getApplication('site');
}

require_once JPATH_ADMINISTRATOR.'/components/com_cck/common/config.php';
require_once JPATH_ADMINISTRATOR.'/components/com_cck/common/functions.php';

global $mainframe;

$fileName = @$_GET["fileName"];
//var_dump($fileDirectory.$fileName, file_exists($fileDirectory.$fileName)); exit;
if(strlen($fileName) && file_exists($fileDirectory.$fileName)) {
	fileDownload($fileDirectory.$fileName, $fileName);
} else {
	echo "not cool!";
	//$mainframe->redirect("index.php", "Търсеният от вас файл не съществува", "error");
}

