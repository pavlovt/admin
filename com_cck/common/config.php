<?php
// jumi don't show the variables if they are not defined as global
global $notLoggedInMessage, $clippingUrl, $webResearchPanelUrl, $electionsUrl, $socPanelUrl, $dbClip, $clippingSorceTypeList, $connectionError, $clippingArticleSimpleUrl,$smtpInfo, $sendFrom;

// mail config
$sendFrom = "webpublishinghouse.net@gmail.com";
$smtpInfo["host"] = 'ssl://smtp.googlemail.com';
$smtpInfo["port"] = '465';
$smtpInfo["auth"] = true;
$smtpInfo["username"] = 'webpublishinghouse.net@gmail.com';
$smtpInfo["password"] = 'bl010311berry';

// directory where all cck files are stored
$fileDirectory = JPATH_SITE."/media/cck/";

//$fileDownloadRedirect = JROUTE::_("/index.php?option=com_jumi&fileid=4&Itemid=1&tmpl=component");
$fileDownloadRedirect = "/administrator/components/com_cck/front/fileDownload.php";

$frontBrowse = "/index.php?option=com_jumi&fileid=11&Itemid=55";
$backBrowse = "/administrator/index.php?option=com_cck";

$backEdit = "/administrator/index.php?option=com_cck&task=edit";

$contentTypeBackBrowse = "index.php?option=com_cck&task=browseContentType";
$contentTypeBackEdit = "index.php?option=com_cck&task=editContentType";

// show this message on front end if user is not logged in and wants to browse the content
$notLoggedInMessage = "Забранен досъп за нерегистрирани потребители";

$googleMapsApiKey = "ABQIAAAA3gwhKIaPs2TKrXwMgE6EgBRqtwsG_iXzwypfI2AJf6DfHQCBCxQ615CYQQ6GhB2rrprjaIqLnkJhLg";

//urls of custom pages
//global $clippingUrl, $q;
//, $socialPanelUrl, $electionUrl, $userAnaliticsUrl;


$clippingUrl = JROUTE::_("index.php?option=com_jumi&fileid=5&Itemid=71");
$socPanelUrl = JROUTE::_("index.php?option=com_jumi&fileid=3&Itemid=70");
$electionsUrl = JROUTE::_("index.php?option=com_jumi&fileid=5&Itemid=70");
$webResearchPanelUrl = JROUTE::_("index.php?option=com_jumi&fileid=5&Itemid=70");

$clippingArticleSimpleUrl = "/administrator/components/com_cck/front/clipping.article.pdf.php";
$clippingListSimpleUrl = "/administrator/components/com_cck/front/clipping.list.pdf.php";

//This is the number of results displayed per page in clipping
$clippingPageRows = 20;

// error messages used in the site
$conncionError = "Грешка при зареждане на страницата - моля опитайте по-късно.";

// contain all db connections - every key is a new db object - $db, $dbFb etc.
$dbSettings = array(
	"dbClip" => array("host" => "78.128.36.189", "user" => "clipping", "password" => "NYQnWkH4eEfIVr8", "dbName" => "clipping")
);

// clipping source types shown on search and bulletin forms
$clippingSorceTypeList = array(1 => "Вестник", 5 => "Списание", 2 => "Радио", 3 => "Телевизия", 4 => "Интернет");

require_once ( 'db.php' );
?>