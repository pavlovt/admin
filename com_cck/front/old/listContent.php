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

$data = new data($db);
$contentType = new contentType($db);
$contentTypeField = new contentTypeField($db);
$dataField = new dataField($db);
$dataTextField = new dataTextField($db);

$data->loadList();
?>

<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th class="title">
				Data Id
			</th>
			<th class="title">
				Content Type
			</th>
			<th class="title">
				Created By
			</th>
			<th class="title">
				Modified By
			</th>
			<th class="title">
				Status
			</th>
			<th class="title" >
				Title
			</th>
			<th class="title" >
				Created on
			</th>
			<th class="title">
				Modified On
			</th>
		</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="15">&nbsp;
			
		</td>
	</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	while ($data->next()) {
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		
		$style = "";

		?>
		<tr>
			<td <?=$style?>>
				<?=$data->p["dataId"] ?>
			</td>
			<td <?=$style?>>
				<?=$data->p["contentTypeName"] ?>
			</td>
			<td <?=$style?>>
				<?=$data->p["createdByName"]?>
			</td>
			<td <?=$style?>>
				<?=$data->p["modifiedByName"] ?>
			</td>
			<td <?=$style?>>
				<?=((int)$data->p["isActive"] ? "Active" : "Inactive")?>
			</td>
			<td <?=$style?>>
				<?=@$fieldList["title"]["value"] ?>
			</td>
			<td <?=$style?>>
				<?=(strtotime($data->p["createdOn"]) ? date("d.m.Y H:i", strtotime($data->p["createdOn"])) : "") ?>
			</td>
			<td <?=$style?>>
				<?=(strtotime($data->p["modifiedOn"]) ? date("d.m.Y H:i", strtotime($data->p["modifiedOn"])) : "") ?>
			</td>
		</tr>
		<?php
		$k++;
	}
	?>
	</tbody>
	</table>