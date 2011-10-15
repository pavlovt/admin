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
	//$mainframe->redirect("/index.php", $notLoggedInMessage, "error");
	
// if registered user then show the services menu according to his profile
} elseif($dbClip) {
	$db->setQuery("SELECT dataId FROM bak_cck_data_field WHERE name='userId' AND value=".$user->id);
	if (($dataId = $db->loadResult()) && $data->loadById($dataId)) {
		
		?><div style=""><?
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		
		// if the client is subscribed for clipping and has clipping categories then show them 
		if (!empty($fieldList["clipping"]["value"]) && !empty($fieldList["clippingCategory"]["value"])) {
			// in the profile we have only main categories which don't have articles and we have to get their subcategories
			if (empty($_SESSION["clippingRealCategory"])) {
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
			}

			// print main categories
			$r = $dbClip->query("SELECT id, name FROM cats WHERE id in (".$fieldList["clippingCategory"]["value"].") AND active = 1 ORDER BY weight", PDO::FETCH_OBJ);
			if ($r) {
				//var_dump($r->fetchAll());
				?><br><br><br><br><?
				foreach ($r as $k => $v) {
					?><span class="cat" onclick="submit('<?=$clippingUrl?>', 'page=clipping.today&filterByCategory=<?=$v->id?>&filterByDate=<?=date("Y-m-d")?>', 'post')"><b><?=$v->name?></b></span><br /><?
				}
				?><br><br><?
			} else {
				$mainframe->enqueueMessage($clippingUrl, $connectionError, "error");
			}
			
			
			
			//clipping.search
			
			//print_r($_POST);exit;
			$vars = @json_decode(urldecode($_POST["json"]));
			//unset($_POST["json"]);
			//print_r($vars);exit;
			if (!empty($vars->filterByCategory)) {
				$filterByCategoryArray = $vars->filterByCategory;
				$filterByCategory = $_POST["filterByCategory"] = @implode(",", $vars->filterByCategory);
			}

			if (!empty($vars->filterBySource)) {
				$filterBySourceArray = $vars->filterBySource;
				$filterBySource = $_POST["filterBySource"] = @implode(",", $vars->filterBySource);
			}

			if (!empty($vars->filterBySourceType)) {
				$filterBySourceTypeArray = $vars->filterBySourceType;
				$filterBySourceType = $_POST["filterBySourceType"] = @implode(",", $vars->filterBySourceType);
			}

			// ensure that there are no html tags, xss etc.
			if (!empty($vars->filterByText)) {
				$inputFilter = new InputFilter();
				$vars->filterByText = $_POST["filterByText"] = $inputFilter->process($vars->filterByText);
			}

			if (!empty($vars->filterByDateFrom) && !empty($vars->filterByDateTo)) {
				$vars->filterByDateFrom = (strtotime($vars->filterByDateFrom) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDateFrom : $fieldList["startClippingDate"]["value"];
				$vars->filterByDateTo = (strtotime($vars->filterByDateTo) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDateTo : $fieldList["startClippingDate"]["value"];
				
				
				$_POST["filterByDateInterval"] = date("Y-m-d", strtotime($vars->filterByDateFrom)).",".date("Y-m-d", strtotime($vars->filterByDateTo));
				
			} elseif (!empty($vars->filterByDateFrom) && empty($vars->filterByDateTo)) {
				$vars->filterByDateFrom = (strtotime($vars->filterByDateFrom) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDateFrom : $fieldList["startClippingDate"]["value"];
				$_POST["filterByDate"] = date("Y-m-d", strtotime($vars->filterByDateFrom));
				
			} elseif (empty($vars->filterByDateFrom) && !empty($vars->filterByDateTo)) {
				$vars->filterByDateTo = (strtotime($vars->filterByDateTo) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDateTo : $fieldList["startClippingDate"]["value"];
				$_POST["filterByDate"] = date("Y-m-d", strtotime($vars->filterByDateTo));
				
			}

			//get categories
			$categoryList = array();
			$r = $dbClip->query("SELECT id, name FROM cats WHERE id in (".$fieldList["clippingCategory"]["value"].") AND active = 1", PDO::FETCH_OBJ);
			if ($r) {
				foreach ($r as $k => $v) {
					$categoryList[$v->id] = $v->name;
				}
			} else {
				$mainframe->enqueueMessage($connectionError, "error");
			}

			//get sources - show only sources from available categories
			$sourceList = array();
			if (!empty($_SESSION["clippingRealCategory"])) {
				$query = "
				SELECT DISTINCT 
					sources.id, sources.name 
				FROM 
					(data) 
					INNER JOIN data_cats ON (data_cats.did = data.id) 
					INNER JOIN cats ON (cats.id = data_cats.cid AND cats.active = 1) 
					INNER JOIN sources ON (sources.id = data.source AND sources.active = 1) 
				WHERE 
					cats.id in (".$_SESSION["clippingRealCategory"].") ORDER BY sources.name";
			} else {
				$query = "SELECT id, name FROM sources WHERE active = 1";
			}

			$r = $dbClip->query($query, PDO::FETCH_OBJ);
			if ($r) {
				foreach ($r as $k => $v) {
					$sourceList[$v->id] = $v->name;
				}
			} else {
				$mainframe->enqueueMessage($connectionError, "error");
			}
			?>

			<form method="post" action="<?=$clippingUrl?>" id="moduleSearchForm" onsubmit="return prepareSubmit()">
				<input type="hidden" name="page" value="clipping.search" />
				<input type="hidden" name="json" value="" />
				
				<label class="customLabel">Тема</label>
				<select name="filterByCategory" multiple="multiple" title="Изберете тема" value="">
					<?
					foreach ($categoryList as $k => $v) {
						?><option value="<?=$k?>" <?=(@in_array($k, $filterByCategoryArray) ? "selected" : "")?>><?=$v?></option><?
					}
					?>
				</select>
				<br />
				
				<label class="customLabel">Типове източници</label>
				<select name="filterBySourceType" multiple="multiple" value="">
					<?
					foreach ($clippingSorceTypeList as $k => $v) {
						?><option value="<?=$k?>" <?=(@in_array($k, $filterBySourceTypeArray) ? "selected" : "")?>><?=$v?></option><?
					}
					?>
				</select>
				<br />
				
				<label class="customLabel">Източник</label>
				<select name="filterBySource" multiple="multiple" title="Изберете източник" value="">
					<?
					foreach ($sourceList as $k => $v) {
						?><option value="<?=$k?>" <?=(@in_array($k, $filterBySourceArray) ? "selected" : "")?>><?=$v?></option><?
					}
					?>
				</select>
				<br />
				
				<label class="customLabel">Период</label>
				от дата <input type="text" name="filterByDateFrom" value="<?=$vars->filterByDateFrom?>" />
				до дата <input type="text" name="filterByDateTo" value="<?=$vars->filterByDateTo?>" />
				<br />
				
				<label class="customLabel">Текст</label>
				<input type="text" name="filterByText" value="<?=$vars->filterByText?>" size="65"/>
				<br />
				<br />
				
				<label class="customLabel">&nbsp;</label>
				<input type="submit" value="Покажи" />
				<br /><br /><br />
			</form>

			<script type="text/javascript">
				$j("#moduleSearchForm [name=filterBySourceType], #moduleSearchForm [name=filterBySource], #moduleSearchForm [name=filterByCategory]").multiselect({
				  noneSelectedText: "Изберете критерий",
				  multiple: true,
				  selectedList: 10,
				  minWidth: 200,
				  width: 200
			   }).multiselectfilter();
							   
				// datepicker
				$j( "#moduleSearchForm [name=filterByDateFrom], [name=filterByDateTo]" ).datepicker({
					dateFormat: 'dd.mm.yy',
					showOtherMonths: true,
					selectOtherMonths: true
					//changeMonth: true,
					//changeYear: true
				});
				
				$j('input[type=submit]').button();
				
				function prepareSubmit() {
					$j('#moduleSearchForm [name=json]').val(JSON.stringify(form2object('moduleSearchForm')));
					
					return true;
				}
			</script>
			<?
			
		}

		?></div><?
	}
} else {
	$mainframe->enqueueMessage($connectionError, "error");
}
