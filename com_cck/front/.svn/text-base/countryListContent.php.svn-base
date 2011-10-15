<?php
//echo JPATH_ADMINISTRATOR"/components/com_cck/class/class.data.php";
$db =& JFactory::getDBO();

//$db->setQuery("SET NAMES 'UTF8'");
//$db->query();

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

$data->loadList(null, null, null, array("#__cck_data.contentTypeId" => 3));
$dataList = $data->result;

//$url = JURI::current();
?>
<!--<h2>Държави</h2>-->
<div style="margin:20px 20px 0px 20px;">

<select onchange="submit('<?=$countrySingleContentUrl?>&dataId=' + this.value);" value="">
<option>Избери държава</option>
<?
	$text = '';
	while ($data->next()) {
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		$db->setQuery("SELECT count(name) FROM `jos_cck_data_field` WHERE name='countryId' AND value=".$data->p["dataId"]);
		$optionList[$data->p["dataId"]] = $fieldList["title"]["value"];
		?><option value="<?=$data->p["dataId"]?>"><?=$fieldList["title"]["value"]?></option><?
		
		//echo "SELECT count(name) FROM `jos_cck_data_field` WHERE name='categoryId' AND value=".$data->p["dataId"];
		$count = $db->loadResult();
		//echo '<pre>'; print_r($fieldList);exit;

		$text .= '<li><a href="'.$countrySingleContentUrl.'&dataId='.$data->p["dataId"].'"><img src="/media/cck/'.$fieldList["picture1"]["value"].'"></a><br>
			<a href="'.$countrySingleContentUrl.'&dataId='.$data->p["dataId"].'">'.$fieldList["title"]["value"].'</a> <small>('.$count.')</small></li>';
		
	}
?>
</select>

</div>
<br />
<div style="border-bottom:1px dashed #CCC; margin:0px 20px 0px 20px"></div>
<br />
<div style="padding:15px; width:100%">
<ul id="list-countries">
	<?php
		echo $text;
	?>
</ul>
</div>