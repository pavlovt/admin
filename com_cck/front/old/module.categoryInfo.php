<?php
global $mainframe;

$db =& JFactory::getDBO();

$dataId = @$_GET["dataId"];
//exit("data ".$dataId);
if ((int)$dataId) {
	
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
	$data->loadById($dataId);
	$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
	
	//if ()
	if ($data->p["contentTypeName"] == "Категории") {?>
		<strong><?=$fieldList["title"]["value"]?></strong>

	<?} elseif (($data->p["contentTypeName"] == "Събития") && !empty($fieldList["categoryId"])) {
		// get the dataId of this event's category
		$data->loadById($fieldList["categoryId"]["value"]);
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		?>
		<strong><?=$fieldList["title"]["value"]?></strong>
	<?}
}
?>