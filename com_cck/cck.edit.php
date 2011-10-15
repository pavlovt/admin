<?php
//ini_set('display_errors','1');
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!$isFrontEnd) {
	JToolBarHelper::title( 'Edit content', 'cpanel.png' );
	//JToolBarHelper::cancel( 'cancel', 'Close' );
}

global $option, $mainframe, $dbClip;

$db =& JFactory::getDBO();

//$db->setQuery("SET NAMES 'UTF8'");
//$db->query();

require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.data.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataTextField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentType.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentTypeField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/common/head.php");

$textField = '';
$data = new data($db);
$contentType = new contentType($db);
$contentTypeField = new contentTypeField($db);
$dataField = new dataField($db);
$dataTextField = new dataTextField($db);

$user =& JFactory::getUser();
$iaAdmin = false;
if (strtolower($user->usertype) == "super administrator") {
	$isAdmin = true;
	//exit('qq');
}

//print_r($data->loadById(1));exit;
if (@$_POST["action"] == "edit") {
	$dataId = isset($_POST["dataId"]) ? $_POST["dataId"] : "";
	$contentTypeId = isset($_POST["contentTypeId"]) ? $_POST["contentTypeId"] : "";

	// this is used only with new products
	if((int)$dataId) {
		$data->loadById($dataId);
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		$contentTypeId = $data->p["contentTypeId"];
		//echo '<pre>'; print_r($data->p); exit;
		//echo '<pre>'; print_r($fieldList); exit;
	} elseif((int)$contentTypeId) {
		$contentType->loadById($contentTypeId);
		$fieldList = $contentType->loadFieldList($contentTypeField);

	} else {
		$mainframe->redirect("index.php?option=com_cck", "Undefined contentTypeId", "error");
	}

	//echo '<pre>'; print_r($fieldList); exit;
} elseif (@$_POST["action"] == "submit") {
	$v = json_decode($_POST["json"], TRUE);

	//echo "<pre>"; print_r($_POST);exit;
	//echo "<pre>"; print_r($v);exit;
	$dataId = $v["dataId"];
	$contentTypeId = $v["contentTypeId"];
	unset($v["contentTypeId"]); unset($v["dataId"]); unset($v["action"]);
	//echo "<pre> "; print_r($dataId); exit;

	while (true) {
		$user =& JFactory::getUser();

		if (!(int)$contentTypeId || !(int)$user->id) {
			JFactory::getApplication()->enqueueMessage( 'Cannot load the required field '.$data->lastError , 'error' );

		// create new field & load it
		} elseif (!(int)$dataId) {
			//var_dump($data->createNew($contentTypeId, $createdBy = $user->id, $isActive = 1)); exit;
			// don't activate if the publisher is not admin
			if (!$data->createNew($contentTypeId, $createdBy = $user->id, $isActive = (!$isFrontEnd ? 1 : 0))) {
				JFactory::getApplication()->enqueueMessage( 'Cannot save this content '.$data->lastError , 'error' );
				break;
			}

		} else {
			// if there is dataId load the data
			if (!$data->loadById((int)$dataId)) {
				JFactory::getApplication()->enqueueMessage( 'Cannot load the content '.$data->lastError , 'error' );
				break;
			} elseif (!$data->update($contentTypeId, $modifiedBy = $user->id, $isActive = $data->p["isActive"])) {
				JFactory::getApplication()->enqueueMessage( 'Cannot save the content '.$data->lastError , 'error' );
				break;
			}
		}
//print_r($v); exit;
		if (!$data->saveFieldList($dataFieldList = $v, $contentTypeField, $dataField, $dataTextField)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot load the content '.$data->lastError , 'error' );
			break;
		}
//exit(($isFrontEnd ? $frontBrowse : $backBrowse));
		$mainframe->redirect( ($isFrontEnd ? $frontBrowse : $backBrowse), 'Content was saved ');

	}
	//print_r($vars);
	//exit;

}

// load users
$q = 'SELECT id, name FROM #__users ORDER BY name';
$db->setQuery( $q );
$rows = $db->loadRowList();
foreach ($rows as $w) {
	$userList[$w[0]] = $w[1];
}

?>

<script type="text/javascript">
		var $j = jQuery.noConflict();
		var destination = '<?=$destination?>';

		$j(document).ready(function() {
			//$j('#sortForm input, select').each( function() { $j(this).attr("name", $j(this).attr("id")) })

			$j("input[type=submit], input[type=button]").button();

			$j('[name=repair.fromTime], [name=repair.toTime], [name=assignmentTimeFrom], [name=assignmentTimeTo]').timepicker({});

	        validateForm($j("#mainForm"));

		} );

	</script>

<style>
	#mainForm th { text-align: right; font-size: 12px; }
	#mainForm label.error { color:red; }
	.gmap { width: 400px; height: 300px; border: 1px solid black; }
</style>

<p>Please enter the details below . The fields marked with <span style="color:red">*</span> are required. </p>
<form id="mainForm" method="post" onsubmit="return prepareSubmit();" enctype="multipart/form-data">
<input type="hidden" name="action" value="submit">
<input type="hidden" name="json" value="">
<input type="hidden" name="dataId" value="<?=@$dataId?>">
<input type="hidden" name="contentTypeId" value="<?=@$contentTypeId?>">

<table cellpadding="3" style="width: 100%">
<? foreach ((array)$fieldList as $field) {
	$requiredText = ((int)$field["option"]["isRequired"] ? 'class="required"' : "");
	$requiredLabel = ((int)$field["option"]["isRequired"] ? '<span style="color:red">*</span>' : "");
	//echo '<pre>'; print_r($fieldList); exit;
	if ($isFrontEnd && in_array($field["name"], $frontInvisibleFields)) { ?>
		<input type="hidden" name="<?=$field["name"]?>" value="<?=$field["value"]?>">
	<?} elseif ($field["type"] == "text") { ?>
		<tr>
			<td align="left"><strong><?=$field["label"]?> <?=$requiredLabel?></strong></td>
			<td>
				<input type="text" name="<?=$field["name"]?>" value="<?=@$field["value"]?>" <?=$requiredText?>>
			</td>
		</tr>
	<?} elseif ($field["type"] == "select") { 
			if ($field["option"]["selectType"] == "multiselect") {
				$field["value"] = explode(",", $field["value"]);
			}
		?>
		<tr>
			<td align="left"><strong><?=$field["label"]?> <?=$requiredLabel?></strong></td>
			<td>
				<select name="<?=$field["name"]?>" value="<?=@$field["value"]?>" <?=$requiredText?> <?=($field["option"]["selectType"] == "multiselect" ? "multiple" : "")?>>
					<option value="">Please Select</option>
					<? foreach ((array)$field["option"]["select"] as $id => $name) {
						if (in_array($id, (array)$field["value"])) {
							?><option value="<?=$id?>" selected><?=$name?></option><?
						} else {
							?><option value="<?=$id?>"><?=$name?></option><?
						}
					}?>
			  </select>
			  <?
			  if ($field["option"]["selectType"] == "multiselect") {?>
			  	<script type="text/javascript">
			  		$j("[name=<?=$field["name"]?>]").multiselect({
				   	  noneSelectedText: $j(this).attr("title"),
					  multiple: true,
					  selectedList: 10,
				      minWidth: 350,
				      width: 350
				   }).multiselectfilter();
			  	</script>
			  <? } ?>	
			</td>
		</tr
	<?} elseif ($field["type"] == "textarea") {
		$textField = $field["name"];
		?>
		<tr>
			<td align="left">
				<strong><?=$field["label"]?> <?=$requiredLabel?></strong>
<!--				<input type="hidden" name="<?=$field["name"]?>" value="<?=@$field["value"]?>">-->
			</td>
			<td>
				<textarea name="<?=$field["name"]?>" <?=$requiredText?>><?=@$field["value"]?></textarea>
				<?
				
				//$editor =& JFactory::getEditor();
				//$params = array( 'smilies'=> '0', 'style'  => '1' , 'layer'  => '0' , 'table'  => '0' , 'clear_entities'=>'0');
				//JEditor::setContent($editor, $field["value"]);
				//echo $editor->display( $field["name"], $field["value"], '400', '400', '20', '20', false, $params );

				?>
			</td>
		</tr>
	<?} elseif ($field["type"] == "file") { ?>
		<tr>
			<td align="left"><strong><?=$field["label"]?> <?=$requiredLabel?></strong></td>
			<td>
				<? if (!empty($field["value"])) { ?><span>File name: <?=@$field["value"]?></span><br> <? } ?>
				<input name="<?=@$field["name"]?>" type="file" <?=$requiredText?> />
			</td>
		</tr>
	<?} elseif ($field["type"] == "checkbox") { ?>
		<tr>
			<td align="left"><strong><?=$field["label"]?> <?=$requiredLabel?></strong></td>
			<td>
				<input name="<?=@$field["name"]?>" type="checkbox" <?=(!empty($field["value"]) ? "checked" : "")?> <?=$requiredText?> />
			</td>
		</tr>
	<?} elseif ($field["type"] == "gmap") { ?>
		<tr>
			<td align="left"><strong><?=$field["label"]?> <?=$requiredLabel?></strong></td>
			<td>
				<input type="text" name="<?=$field["name"]?>" value="<?=@$field["value"]?>" <?=$requiredText?>><button onclick="return showMap('<?=$field['name']?>_gmap',$j('[name=<?=$field['name']?>]').val());">Покажи</button>
				
				<script>
				$j(document).ready(function() {
				$j("#<?=$field["name"]?>_gmap").gMap({ markers: [
	                { address: "<?=@$field["value"]?>",
	                html: "_address" }],
				    address: "<?=@$field["value"]?>",
				    zoom: 15 });
				});
				</script>
				<div class="gmap" id="<?=$field["name"]?>_gmap"></div>
			</td>
		</tr>
	<?} elseif ($field["type"] == "date") { ?>
		<tr>
			<td align="left"><strong><?=$field["label"]?> <?=$requiredLabel?></strong></td>
			<td>
				<input type="text" name="<?=$field["name"]?>" value="<?=@$field["value"]?>" <?=$requiredText?>>
				
				<script>
				$j(document).ready(function() {
					// datepicker
					$j( "[name=<?=$field["name"]?>]" ).datepicker({
						dateFormat: 'dd.mm.yy',
						showOtherMonths: true,
						selectOtherMonths: true
						//changeMonth: true,
						//changeYear: true
					});
				});
				</script>
			</td>
		</tr>
	<?}
}
?>
<tr><td>&nbsp;</td></tr>
</table>

<br><br>
<div style="width: auto; text-align: center;">
		<input type="submit" value="Save">
		<input type="button" value="Close" onclick="javascript:doClose();">
</div>
</form>
<br>

<script type="text/javascript">
var textField = '<?=$textField?>';
var isFrontEnd = '<?=$isFrontEnd?>' || 0;
var frontBrowse = '<?=$frontBrowse?>';
var backBrowse = '<?=$backBrowse?>';

function prepareSubmit () {
	$j('#mainForm input:checkbox:checked').val(1);
	$j('#mainForm input:checkbox:not(:checked)').val(0);
	//textField && ($j('[name='+textField+']').val(tinyMCE.get(textField).save()));
	$j('#mainForm [name=json]').val(JSON.stringify(form2object('mainForm')));
	//alert($j('[name='+textField+']').val());
	
	// text field is using editor and is validated separately
	// || !$j('[name='+textField+']').val().length
	if (!$j("#mainForm").valid()) return false;

	return true;
}

function doClose() {
	if (isFrontEnd) {
		javascript:window.location.href=frontBrowse;
	} else {
		javascript:window.location.href=backBrowse;
	}
}

function showMap(id, address) {
	$j('#'+id).gMap({ markers: [{ address: address, html: '_address' }], address: address, zoom: 15 });

	return false;
}

</script>
