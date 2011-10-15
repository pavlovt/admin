<?php
global $mainframe;

$db =& JFactory::getDBO();

$dataId = @$_GET["dataId"];
//exit("data ".$dataId);
if (!(int)$dataId) {
	$mainframe->redirect("/index.php", "На страницата бяха подадени невалидни параметри", "error");
}

require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.data.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataTextField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentType.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentTypeField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/common/head.php");

$data = new data($db);
$contentType = new contentType($db);
$contentTypeField = new contentTypeField($db);
$dataField = new dataField($db);
$dataTextField = new dataTextField($db);

$categoryName = $dataField->loadByName($dataId, "title");
//print_r($countryName);exit;
$data->loadList(null, null, null, array("#__cck_data.contentTypeId" => 4));
?>
<div class="breadcrumb"><a href="index.php">Начало</a> > <a href="<?=$categoryListContentUrl?>">Категории</a></div><br>

<h1>Събития в <?=$categoryName["value"]?></h1>
<div>

	<?php
	while ($data->next()) {
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		
		//show only events for this country
		if ($fieldList["categoryId"]["value"] != $dataId) continue;
		$categoryName = $dataField->loadByName($fieldList["categoryId"]["value"], "title");
		//echo '<pre>'; print_r($fieldList);exit;
		?>
			<div class="event-list-box">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td valign="top"><h2 style="margin:0; margin-left:20px;"><a href="<?=$eventSingleContentUrl.'&dataId='.$data->p["dataId"]?>"><?=$fieldList["title"]["value"] ?></a></h2></td>
      </tr>
      <tr>
        <td valign="top"><p style="margin:0;margin-left:20px;"><?=getCategory($fieldList["categoryId"]["value"], $dataField)?></a></p></td>
      </tr>
      <tr>
        <td valign="top">
		<p><strong>От: </strong><?=$fieldList["fromDate"]["value"] ?><br />
			<strong>До: </strong><?=(!empty($fieldList["toDate"]["value"]) ? " ".$fieldList["toDate"]["value"] : "")?></p></td>
      </tr>
      <tr>
        <td valign="top"><p class="resume"><?=$fieldList["resume"]["value"] ?></td>
      </tr>
      <tr>
        <td valign="top"><p><a href="<?=$eventSingleContentUrl.'&dataId='.$data->p["dataId"]?>">Виж повече</a></p><br></td>
      </tr>
      <tr>
        <td valign="top">&nbsp;</td>
      </tr>
    </table>
	</td>
    <td width="10" valign="top">&nbsp;</td>
    <td valign="top"><a href="<?=$eventSingleContentUrl.'&dataId='.$data->p["dataId"]?>"><img src="/media/cck/<?=$fieldList["picture"]["value"]?>"></a></td>
  </tr>
</table>
			</div>
		<?
	}
	?>
</div>

<?
// get all parent categories of the given category
function getCategory($categoryId, $dataField) {
	global $categorySingleContentUrl;
	//echo '<pre>'; print_r($GLOBALS);exit;
	do {
		$catList[$categoryId] = $dataField->loadByName($categoryId, "title");
		$parentCategoryId = $dataField->loadByName($categoryId, "parentCategoryId");
		//echo '<pre>'; var_dump($categoryId); print_r($parentCategoryId);
		$categoryId = $parentCategoryId["value"];
	} while ($parentCategoryId["value"] != 0);
	//echo '<pre>'; print_r($catList);exit;
	$text = '';
	foreach ($catList as $dataId => $category) {
		$text = '<a href="'.$categorySingleContentUrl.'&dataId='.$dataId.'">'.$category["value"].'</a>, '.$text;
	}
	
	if(strlen($text)) $text = substr($text, 0, -2);
	
	return $text;
}


?>