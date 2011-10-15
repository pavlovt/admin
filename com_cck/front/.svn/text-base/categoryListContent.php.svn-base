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

global $categorySingleContentUrl;

//echo '<pre>'; print_r($categorySingleContentUrl);exit;

$data = new data($db);
$contentType = new contentType($db);
$contentTypeField = new contentTypeField($db);
$dataField = new dataField($db);
$dataTextField = new dataTextField($db);

$data->loadList(null, null, null, array("#__cck_data.contentTypeId" => 2));

?>


<h2>Категории</h2>
<ul class="categories">
	<?php
	while ($data->next()) {
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		$db->setQuery("SELECT count(name) FROM `jos_cck_data_field` WHERE name='categoryId' AND value=".$data->p["dataId"]);
		$count = $db->loadResult();
		//echo <pre> print_r($fieldList);exit;
		?>
		<li>
			<a href="<?=$categorySingleContentUrl.'&dataId='.$data->p["dataId"]?>"><?=$fieldList["title"]["value"] ?></a> <small>(<?=$count?>)</small>
		</li>

		<?php
	}
	?>
</ul>