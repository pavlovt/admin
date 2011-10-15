<?php
//ini_set('display_errors','1');
//ini_set("magic_quotes_gpc", "Off");
//ini_set("magic_quotes_runtime", "Off");
defined( '_JEXEC' ) or die( 'Restricted access' );
JToolBarHelper::title( JText::_( 'Edit content type' ), 'generic.png' );

global $option, $mainframe, $dbClip;

require_once(JPATH_ADMINISTRATOR."/components/com_cck/common/head.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentType.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentTypeField.php");

//var_dump($dbClip->query("show tables")->fetchAll());exit;

$db =& JFactory::getDBO();
$contentType = new contentType($db);
$contentTypeField = new contentTypeField($db);

$user =& JFactory::getUser();
$iaAdmin = false;
if (strtolower($user->usertype) == "super administrator") {
	$isAdmin = true;
	//exit('qq');
}

function compare($a, $b) {
    if ($a["seq"] == $b["seq"]) {return ($a["prev_seq"] > $b["prev_seq"]) ? -1 : 1;}
    return ($a["seq"] < $b["seq"]) ? -1 : 1;
}

if (@$_POST["action"] == "edit") {
	$contentTypeId = isset($_POST["contentTypeId"]) ? $_POST["contentTypeId"] : "";

	// this is used only with new products
	if((int)$contentTypeId) {
		$contentType->loadById($contentTypeId);
		$fieldList = $contentType->loadFieldList($contentTypeField);

	}

	//echo '<pre>'; print_r($fieldList); exit;
} elseif (@$_POST["action"] == "submit") {

	while (TRUE) {
		//var_dump(get_magic_quotes_gpc());
		$contentTypeId = @$_POST["contentTypeId"];
		$contentTypeName = @$_POST["contentTypeName"];
		$contentTypeIsActive = @$_POST["contentTypeIsActive"];
	    $sortArray = isset($_POST["sortJson"]) ? json_decode($_POST["sortJson"], TRUE) : "";
	    //echo '<pre>'.str_replace("\\", "", $_POST["sortJson"]); 
	    //print_r($_POST);exit;
	    //echo '<pre>'; print_r($sortArray["p"]);exit;
	    // remove elements that should be deleted and other unused elements
	    $clearSortArray = array();
	    foreach ((array)$sortArray["p"] as $i => $product) {
	    	if (!isset($product["delete"])){
	    		$clearSortArray[] = $product;
	    	};
	    }

	    // sort elements using compare function
	    usort($clearSortArray, "compare");

	    //echo '<pre>'; print_r($clearSortArray);exit;

	    if (!(int)$contentTypeId) {
			//var_dump($contentType->createNew($contentTypeId, $createdBy = $user->id, $isActive = 1)); exit;
			// don't activate if the publisher is not admin
			if (!$contentType->createNew($name = $contentTypeName, $isActive = ($contentTypeIsActive ? 1 : 0))) {
				JFactory::getApplication()->enqueueMessage( 'Cannot save this content type '.$contentType->lastError , 'error' );
				break;
			}

		} else {
			// if there is contentTypeId load the contentType
			if (!$contentType->loadById((int)$contentTypeId)) {
				JFactory::getApplication()->enqueueMessage( 'Cannot load the content '.$contentType->lastError , 'error' );
				break;
			} elseif (!$contentType->update($name = $contentTypeName, $isActive = ($contentTypeIsActive ? 1 : 0))) {
				JFactory::getApplication()->enqueueMessage( 'Cannot update the content type '.$contentType->lastError , 'error' );
				break;
			}
		}

		if (!$contentType->saveFieldList($fieldList = $clearSortArray, $contentTypeField)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot save the content type fields '.$contentType->lastError , 'error' );
			break;
		}

		$mainframe->redirect( $contentTypeBackBrowse, 'Content type was saved ');

	}
}

//$productArray = array();
//$q = 'SELECT '.$orderBy.', product_id, product_name FROM jos_vm_product WHERE '.$orderBy.' != 9999 ORDER BY '.$orderBy;

/*$db->setQuery( $q );
$rows = $db->loadRowList();

foreach ($rows as $w) {
	$lastSeq = $w[0];
	$productArray[$w[0]] = array("id" => $w[1], "name" => $w[2]);
}*/


?>

<script type="text/javascript">

		$j(document).ready(function() {
			// set name attribute to be the same as id for all inputs in this form
			$j('#sortForm input, select, textarea').each( function() { $j(this).attr("name", $j(this).attr("id")) });

			$j("button").button();

	        validateForm($j("#mainForm"));


		} );

	</script>

	<style type="text/css">
		#fResult { vertical-align: middle; text-align: left; width: 100%; }
		#fResult input[type=text], label { width: 110px; }
		#fResult textarea { width: 250px; height: 100px; }
	</style>

<form id="sortForm" method="post" onsubmit="javascript: return prepareSubmit();">
<input type="hidden" id="action" value="submit">
<input type="hidden" id="sortJson" value="">
<input type="hidden" id="contentTypeId" value="<?=@$contentTypeId?>">

<button onClick="javascript: addSeq(); return false;">Добавяне</button>
<button onClick="javascript:$j(this).parent().submit();">Запазване</button>
<button onClick="javascript:doClose(); return false;">Затваряне</button>

<br><br><br>

<label>Content Type Name:</label><br>
<input type="text"  id="contentTypeName" value="<?=@$contentType->p["name"]?>" size="70">
<br><br>

<label>Content Type is active:</label><br>
<input type="checkbox"  id="contentTypeIsActive" value="<?=@$contentType->p["isActive"]?>" <?=(@$contentType->p["isActive"] ? "checked" : "")?>>
<br><br>

<table id="fResult">
<thead>
	<th>Delete</th>
	<th>Ordering</th>
	<th>Label</th>
	<th>Name</th>
	<th>Type</th>
	<th>Options</th>
	<th>Is Active</th>
</thead>
<tbody>
	<tr><td colspan="7"><label><a href="" onclick="javascrip: $j('#sortForm :checkbox').attr('checked',true); return false;">(Всички)</a></label></td></tr>
<? foreach ((array)$fieldList as $field) {
	$seq = $field["ordering"];
	// the select option are generated automaticly - we don't want to save them to db
	if (!empty($field["option"]["selectFromDb"])) unset($field["option"]["select"]);
	//echo '<pre>'; print_r($field["option"]);exit;
	?>
	<tr>
		<td>
			<input type="checkbox" id="p[<?=$seq?>].delete" value="">
			<input type="hidden" id="p[<?=$seq?>].prev_seq" value="<?=$seq?>">
		</td>
		<td><input type="text" id="p[<?=$seq?>].seq" value="<?=$seq?>"></td>
		<td><textarea id="p[<?=$seq?>].label"><?=$field["label"]?></textarea></td>
		<td><input type="text" id="p[<?=$seq?>].name" value="<?=$field["name"]?>"></td>
		<td>
			<select type="text" id="p[<?=$seq?>].type" value="">
				<?
				foreach ($contentType->fieldType as $k => $v) { ?>
					<option value="<?=$k?>" <?=(($k == $field["type"]) ? "selected" : "")?>><?=$v?></option>
				<? } ?>
			</select>
		</td>
		<td><textarea id="p[<?=$seq?>].option"><?=(!empty($field["option"]) ? json_encode($field["option"]) : "")?></textarea></td>
		<td><input type="checkbox" id="p[<?=$seq?>].isActive" <?=($field["isActive"] ? "checked" : "")?>></td>
	</tr>
	<?
}?>
</tbody>
<tfoot></tfoot>
</table>

<br><br><br>

<button onClick="javascript: addSeq(); return false;">Добавяне</button>
<button class="fSubmit" onClick="javascript:$j(this).parent().submit();">Запазване</button>
<button onClick="javascript:doClose(); return false;">Затваряне</button>
</form>

<?
// used to add new element
$selectType = "";
foreach ($contentType->fieldType as $k => $v) {
	$selectType .= "<option value=\"{$k}\">{$v}</option>";
}

?>

<script type="text/javascript">
// if creating new content type set $seq to 0
var lastSeq = <?=($seq ? $seq : 0)?>;
var backBrowse = '<?=$contentTypeBackBrowse?>';
var selectType = '<?=$selectType?>';

function prepareSubmit() {
	$j('#sortForm input:checkbox:checked').val(1);
	$j('#sortForm input:checkbox:not(:checked)').val(0);
	$j('#sortJson').val(JSON.stringify(form2object('sortForm')));

	return true;
}

function addSeq() {
	lastSeq++;
	var tdRow = ' \
		<tr> \
			<td> \
				<input type="checkbox" name="p['+lastSeq+'].delete" value=""> \
				<input type="hidden" name="p['+lastSeq+'].prev_seq" value="'+lastSeq+'"> \
			</td> \
			<td><input type="text" name="p['+lastSeq+'].seq" value="'+lastSeq+'"></td> \
			<td><textarea name="p['+lastSeq+'].label"></textarea></td> \
			<td><input type="text" name="p['+lastSeq+'].name" value=""></td> \
			<td>\
			<select type="text" id="p['+lastSeq+'].type" value="">\
			' + selectType + ' \
			</select>\
			</td>\
			<td><textarea name="p['+lastSeq+'].option"></textarea></td> \
			<td><input type="checkbox" name="p['+lastSeq+'].isActive" checked></td> \
		</tr> \
		';
	$j("#fResult > tbody").append(tdRow);
	//$j('#sortForm input, select').each( function() { $j(this).attr("name", $j(this).attr("id")) });

	return false;
}

function doClose() {
	window.location.href=backBrowse;
}
</script>