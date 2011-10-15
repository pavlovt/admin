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

$data = new data($db);
$contentType = new contentType($db);
$contentTypeField = new contentTypeField($db);
$dataField = new dataField($db);
$dataTextField = new dataTextField($db);

//print_r($_POST);exit;
//if (@$_POST["action"] == "edit") {
	$dataId = @$_GET["dataId"];
	//$dataId=69;
	// this is used only with new products
	if((int)$dataId) {
		$data->loadById($dataId);
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		//echo '<pre>'; print_r($data->p); exit;
	} else {
		$mainframe->redirect("index.php", "Incorrect parameters ware given", "error");
	}
	
//echo '<pre>'; print_r($fieldList); exit;
/*foreach ($fieldList as $v) {
	echo $v["label"]."<br>";
	echo $v["value"]."<br>";
}*/

require_once JPATH_ADMINISTRATOR.'/components/com_cck/common/head.php';
?>
<style>
	#gmap { width: 400px; height: 300px; border: 1px solid black; }
</style>

<script>
$j(document).ready(function() {
	$j("#gmap").gMap({ 
		markers: [{ address: "<?=$fieldList["mapLocation"]["value"]?>", html: "_address" }],
	    address: "<?=$fieldList["mapLocation"]["value"]?>",
	    zoom: 15 });
});
</script>

<h1><?=$fieldList["title"]["value"]?></h1>

<?=$fieldList["description"]["value"]?>

<br>

<a href="<?=$fileDirectory.$fieldList["socFile"]["value"]?>">Изтеглете социологическото проучване</a><br>
<a href="<?=$fileDownloadRedirect.'?fileName='.$fieldList["socFile"]["value"]?>">Изтеглете социологическото проучване</a>
<br>

<?/*<div class="gmap" id="gmap"></div>*/?>
<br><br>