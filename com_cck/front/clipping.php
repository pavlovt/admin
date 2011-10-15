<?php
ini_set('display_errors','1');
//echo JPATH_ADMINISTRATOR"/components/com_cck/class/class.data.php";
$db =& JFactory::getDBO();

require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.data.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataTextField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentType.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentTypeField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.inputfilter.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/common/head.php");

global $mainframe, $dbClip, $clippingUrl, $webResearchPanelUrl, $electionsUrl, $socPanelUrl, $connectionError, $clippingSorceTypeList;

$data = new data($db);
$contentType = new contentType($db);
$contentTypeField = new contentTypeField($db);
$dataField = new dataField($db);
$dataTextField = new dataTextField($db);

$currentUrl = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

$user = & JFactory::getUser();
if($user->guest) {
	$mainframe->redirect("/index.php", $notLoggedInMessage, "error");
	
// if registered user then show the services menu according to his profile
} elseif($dbClip) {
	$db->setQuery("SELECT dataId FROM bak_cck_data_field WHERE name='userId' AND value=".$user->id);
	if (($dataId = $db->loadResult()) && $data->loadById($dataId)) {
		
		?><div style=""><?
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		
		// if the client is subscribed for clipping and has clipping categories then show them 
		if (!empty($fieldList["clipping"]["value"]) && !empty($fieldList["clippingCategory"]["value"])) {
			if (empty($_SESSION["clippingRealCategory"]) || ($_SESSION["clippingUserId"] != $user->id)) {
				$r = @$dbClip->query("SELECT id FROM cats WHERE pid in (".$fieldList["clippingCategory"]["value"].")", PDO::FETCH_NUM);
				$categoryList = array();
				foreach ($r as $v) {
					$categoryList[] = $v[0];
				}
				
				if (empty($categoryList)) {
					$_SESSION["clippingRealCategory"] = $fieldList["clippingCategory"]["value"];
				} else {
					$_SESSION["clippingRealCategory"] = implode(",", $categoryList);
				}
				
				$_SESSION["clippingUserId"] = $user->id;
			}
			
			// clipping menu
			?>
			<span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.bulletin&json=<?=urlencode(json_encode(array("filterByDate" => date("d.m.Y"))))?>', 'post')"><b>Бюлетин за деня</b></span> | 
			<span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.index', 'post')"><b>Днес</b></span> | 
			<span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.search&json=<?=urlencode(json_encode(array("filterByDateFrom" => date("d.m.Y"), "filterByDateTo" => date("d.m.Y"))))?>', 'post')"><b>Търсене</b></span> 
			<br /><br /><br />
			<?
			
			//$categoryList = explode(",", $fieldList["clippingCategory"]["value"]);
			//var_dump($fieldList["startClippingDate"]["value"]);
			// which page to open?
			switch ($_POST["page"]) {
				case 'clipping.bulletin':
					require_once("clipping.bulletin.php");
					require_once("clipping.list.php");
					break;
					
				case 'clipping.search':
					require_once("clipping.search.php");
					require_once("clipping.list.php");
					break;
					
				case 'clipping.list':
					require_once("clipping.list.php");
					break;
					
				case 'clipping.article':
					require_once("clipping.article.php");
					break;
					
				case 'clipping.today':
					require_once("clipping.today.php");
					require_once("clipping.list.php");
					break;
				
				default:
					require_once("clipping.index.php");
					break;
			}
			
			/*?><a href="<?=$webResearchPanelUrl?>">УЕБ РИСЪРЧ ИНДЕКС</a><br /><?*/
		}

		?></div><?
	}
} else {
	$mainframe->enqueueMessage($clippingUrl,$connectionError, "error");
}

