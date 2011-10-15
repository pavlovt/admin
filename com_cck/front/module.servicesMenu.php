<?php
ini_set('display_errors','1');
//echo JPATH_ADMINISTRATOR"/components/com_cck/class/class.data.php";
$db =& JFactory::getDBO();

//$db->setQuery("SET NAMES 'UTF8'");
//$db->query();



require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.data.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataTextField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentType.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentTypeField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/common/config.php");

global $mainframe, $dbClip, $clippingUrl, $webResearchPanelUrl, $electionsUrl, $socPanelUrl;

$data = new data($db);
$contentType = new contentType($db);
$contentTypeField = new contentTypeField($db);
$dataField = new dataField($db);
$dataTextField = new dataTextField($db);

$currentUrl = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
//echo $currentUrl."<br>";

$user = & JFactory::getUser();
if($user->guest) {
	//$mainframe->redirect("/index.php", "Забранен досъп за нерегистрирани потребители", "error");
	//echo "not logged in";
	
// if registered user then show the services menu according to his profile
} else {
	$db->setQuery("SELECT dataId FROM bak_cck_data_field WHERE name='userId' AND value=".$user->id);
	if (($dataId = $db->loadResult()) && $data->loadById($dataId)) {
		
		?><div><ul class="menu_right"><?
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		
		if ($fieldList["webResearchPanel"]["value"]) {
			?><li><a href="<?=$webResearchPanelUrl?>"><img src="/images/stories/wrindex_icon.png" align="left" alt="УЕБ-РИСЪРЧ-ИНДЕКС"><span>УЕБ РИСЪРЧ ИНДЕКС</span></a></li><?
		}
		
		if ($fieldList["socPanel"]["value"]) {
			?><li><a href="<?=$socPanelUrl?>"><span><img src="/images/stories/socio_icon.png" align="left" alt="Социологически-панел">СОЦИОЛОГИЧЕСКИ ПАНЕЛ</span></a></li><?
		}
		
		if ($fieldList["clipping"]["value"]) {
			?><li><a href="<?=$clippingUrl?>"><span><img src="/images/stories/clipping_icon.png" align="left" alt="Клипинг">КЛИПИНГ</span></a></li><br /><?
		}
		
		//var_dump($fieldList["elections"]);
		if ($fieldList["elections"]["value"]) {
			?><li><a href="<?=$electionsUrl?>"><span><img src="/images/stories/historydata_icon.png" align="left" alt="Исторически-данни-за-изборите">ИСТОРИЧЕСКИ ДАННИ ЗА ИЗБОРИТЕ</span></a></li><?
		}
		?></ul></div><?
	}
}

