<?php
//ini_set('display_errors','1');

defined( '_JEXEC' ) or die( 'Restricted access' );

JToolBarHelper::title( 'Browse content types', 'cpanel.png' );

global $option, $mainframe;

//JToolBarHelper::appendButton( 'Standard', 'new', $alt, 'edit', false, false );

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

$user =& JFactory::getUser();
$isAdmin = false;
$dataFilter = array();
$contentTypeFilter = array();

if (strtolower($user->usertype) == "super administrator") {
	$isAdmin = true;
	//exit('qq');
}

if (@$_POST["action"] == "activate") {
	$contentTypeId = isset($_POST["contentTypeId"]) ? $_POST["contentTypeId"] : "";

	// this is used only with new products
	if((int)$contentTypeId) {
		if (!$contentType->loadById($contentTypeId)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot load the content type '.$contentType->lastError , 'error' );

		} elseif (!$contentType->update($name = $contentType->p["name"], $isActive = 1)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot update the content type '.$contentType->lastError , 'error' );
		} else {
			JFactory::getApplication()->enqueueMessage( 'Content type '.$contentType->p["name"]." activated");
		}

	} else {
		JFactory::getApplication()->enqueueMessage( 'Content type id required', 'error' );
	}

	//echo '<pre>'; print_r($fieldList); exit;
} elseif (@$_POST["action"] == "deactivate") {
	$contentTypeId = isset($_POST["contentTypeId"]) ? $_POST["contentTypeId"] : "";

	// this is used only with new products
	if((int)$contentTypeId) {
		if (!$contentType->loadById($contentTypeId)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot load the content type '.$contentType->lastError , 'error' );

		} elseif (!$contentType->update($name = $contentType->p["name"], $isActive = 0)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot update the content type '.$contentType->lastError , 'error' );
		} else {
			JFactory::getApplication()->enqueueMessage( 'Content type '.$contentType->p["name"]." deactivated");
		}

	} else {
		JFactory::getApplication()->enqueueMessage( 'Content type id required', 'error' );
	}

	//echo '<pre>'; print_r($fieldList); exit;
}  elseif (@$_POST["action"] == "delete") {
	$contentTypeId = isset($_POST["contentTypeId"]) ? $_POST["contentTypeId"] : "";

	// this is used only with new products
	if((int)$contentTypeId) {
		if (!$contentType->loadById($contentTypeId)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot load the content type '.$contentType->lastError , 'error' );

		} elseif (!$contentType->delete()) {
			JFactory::getApplication()->enqueueMessage( 'Cannot delete the content type '.$contentType->lastError , 'error' );
		} else {
			JFactory::getApplication()->enqueueMessage( 'Content type '.$contentType->p["name"]." deleted");
		}

	} else {
		JFactory::getApplication()->enqueueMessage( 'Content type id required', 'error' );
	}

	//echo '<pre>'; print_r($fieldList); exit;
}


// get all content types
$contentType->loadList();

?>

<script type="text/javascript">
		var $j = jQuery.noConflict();

		$j(document).ready(function() {
			//$j('#mainForm input, select').each( function() { $j(this).attr("name", $j(this).attr("id")) })

			$j("button").button();

			$j('.adminlistz').dataTable({
					"bJQueryUI": true,
					"sPaginationType": "full_numbers",
					"sDom": 'W<"clear">lfrtip',
					"oColumnFilterWidgets": {
						"aiExclude": [ 0,3,4,5,6 ],
						"bGroupTerms": true
					}
			});

		} );

	</script>

<style>
	#dataForm th { text-align: right; font-size: 12px; color:#000000; }
	#dataForm td { text-align: right; font-size: 12px; color:#000000; }
	.ui-button { font-size: 9px !important; }
</style>
<?/*
<p style="float:left;">
Filter by:
&nbsp;User&nbsp;
<select name="filterByUser" value="" onchange="javascript:filterByUser();">
	<option value="">All</option>
	<? foreach ($userList as $id => $name) {
		if ($id == $filterByUser) {
			?><option value="<?=$id?>" selected><?=$name?></option><?
		} else {
			?><option value="<?=$id?>"><?=$name?></option><?
		}
	}?>
</select>

&nbsp;Content type&nbsp;
<select name="filterByContentType" value="" onchange="javascript:filterByContetType();">
	<option value="">All</option>
	<? foreach ($contentTypeList as $v) {
		if ($v["contentTypeId"] == $filterByContentType) {
			?><option value="<?=$v["contentTypeId"]?>" selected><?=$v["name"]?></option><?
		} else {
			?><option value="<?=$v["contentTypeId"]?>"><?=$v["name"]?></option><?
		}
	}?>
</select>
</p>
*/?>

<p style="float:right;">
<button onclick="submit('<?=$contentTypeBackEdit?>', 'action=edit&contentTypeId=0');">Create New</button>
</p>

<div style="width: 100%">

<table class="adminlistz" style="width: 100%">
	<thead>
		<tr>
			<th class="title">
				Content Type Id
			</th>
			<th class="title">
				Name
			</th>
			<th class="title">
				Status
			</th>
			<th class="title" >
				Field Names
			</th>
			<th class="title" >
				Created on
			</th>
			<th class="title">
				Modified On
			</th>
			<th class="title">&nbsp;

			</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	while ($contentType->next()) {
		//if ($i++ < 2) continue;
		$fieldList = $contentType->loadFieldList($contentTypeField, $dataField, $dataTextField);
//echo '<pre>'; print_r($fieldList); echo '<br>';
		$style = "";

		?>
		<tr>
			<td <?=$style?>>
				<?=$contentType->p["contentTypeId"] ?>
			</td>
			<td <?=$style?>>
				<?=$contentType->p["name"] ?>
			</td>
			<td <?=$style?>>
				<?=((int)$contentType->p["isActive"] ? "Active" : "Inactive")?>
			</td>
			<td <?=$style?>>
				<?=@implode(", ", array_keys($fieldList)) ?>
			</td>
			<td <?=$style?>>
				<?=(strtotime($contentType->p["createdOn"]) ? date("d.m.Y H:i", strtotime($contentType->p["createdOn"])) : "") ?>
			</td>
			<td <?=$style?>>
				<?=(strtotime($contentType->p["modifiedOn"]) ? date("d.m.Y H:i", strtotime($contentType->p["modifiedOn"])) : "") ?>
			</td>
			<td nowrap="nowrap" width="<?=($isAdmin ? '250px' : '50px')?>" <?=$style?>>
				<button onclick="submit('<?=$contentTypeBackEdit?>', 'action=edit&contentTypeId=<?=$contentType->p["contentTypeId"] ?>');">Edit</button>
				<? if ($isAdmin) { ?>
				<button onclick="submit('<?=$_SERVER['REQUEST_URI']?>', 'action=activate&contentTypeId=<?=$contentType->p["contentTypeId"] ?>');">Activate</button>
				<button onclick="submit('<?=$_SERVER['REQUEST_URI']?>', 'action=deactivate&contentTypeId=<?=$contentType->p["contentTypeId"] ?>');">Deactivate</button>
				<button onclick="doDelete('<?=$_SERVER['REQUEST_URI']?>', '<?=$contentType->p["contentTypeId"] ?>');">Delete</button>
				<? } ?>
			</td>
		</tr>
		<?php
		$k++;
	}
	?>
	</tbody>
	</table>

</div>

<script type="text/javascript">
var isFrontEnd = '<?=$isFrontEnd?>' || 0;
var frontEdit = '<?=$frontEdit?>';
var backEdit = '<?=$backEdit?>';

function doDelete(url, contentTypeId) {
	if (!confirm('Are you sure you want to delete this data?')) { return true; }

	submit(url, {"action":"delete", "contentTypeId":contentTypeId});

}


</script>