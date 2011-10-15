<?php
ini_set('display_errors','1');

defined( '_JEXEC' ) or die( 'Restricted access' );

if (!$isFrontEnd) {
	JToolBarHelper::title( 'Browse content', 'cpanel.png' );
	//JToolBarHelper::addNew('edit', 'new');
}

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
if (!$user->guest) {
	$dataFilter["#__cck_data.createdBy"] = $user->id;
	$dataFilter["#__cck_data.contentTypeId"] = $frontContentTypeCreation;
	$contentTypeFilter["#__cck_content_type.contentTypeId"] = $frontContentTypeCreation;
} else {
	$mainframe->redirect("/index.php", "Трябва да се логнете в сайта за да ползвате тази страница", "error");
}

if (@$_POST["action"] == "activate") {
	$dataId = isset($_POST["dataId"]) ? $_POST["dataId"] : "";

	// this is used only with new products
	if((int)$dataId) {
		if (!$data->loadById($dataId)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot load the content type '.$data->lastError , 'error' );

		} elseif (!$data->update($name = $data->p["name"], $isActive = 1)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot update the content type '.$data->lastError , 'error' );
		} else {
			JFactory::getApplication()->enqueueMessage( 'Content type '.$data->p["name"]." activated");
		}

	} else {
		JFactory::getApplication()->enqueueMessage( 'Content type id required', 'error' );
	}

	//echo '<pre>'; print_r($fieldList); exit;
} elseif (@$_POST["action"] == "deactivate") {
	$dataId = isset($_POST["dataId"]) ? $_POST["dataId"] : "";

	// this is used only with new products
	if((int)$dataId) {
		if (!$data->loadById($dataId)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot load the content type '.$data->lastError , 'error' );

		} elseif (!$data->update($name = $data->p["name"], $isActive = 0)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot update the content type '.$data->lastError , 'error' );
		} else {
			JFactory::getApplication()->enqueueMessage( 'Content type '.$data->p["name"]." deactivated");
		}

	} else {
		JFactory::getApplication()->enqueueMessage( 'Content type id required', 'error' );
	}

	//echo '<pre>'; print_r($fieldList); exit;
}  elseif (@$_POST["action"] == "delete") {
	$dataId = isset($_POST["dataId"]) ? $_POST["dataId"] : "";

	// this is used only with new products
	if((int)$dataId) {
		if (!$data->loadById($dataId)) {
			JFactory::getApplication()->enqueueMessage( 'Cannot load the content type '.$data->lastError , 'error' );

		} elseif (!$data->delete()) {
			JFactory::getApplication()->enqueueMessage( 'Cannot delete the content type '.$data->lastError , 'error' );
		} else {
			JFactory::getApplication()->enqueueMessage( 'Content type '.$data->p["name"]." deleted");
		}

	} else {
		JFactory::getApplication()->enqueueMessage( 'Content type id required', 'error' );
	}

	//echo '<pre>'; print_r($fieldList); exit;
}


$data->loadList(null, null, null, $dataFilter);

$total = $data->totalRecords;
//echo "total $total";
//This is the number of results displayed per page
$page_rows = 50;

//This tells us the page number of our last page
$last = ceil($total/$page_rows);
$pagenum = @$_GET[pagenum];
 //this makes sure the page number isn't below one, or more than our maximum pages
 if ($pagenum < 1){
 $pagenum = 1;
 } elseif ($pagenum > $last) {
 $pagenum = $last;
 }
 
 $start=($pagenum - 1) * $page_rows;
 $end=$page_rows;

$data->loadList($start, $end, null, $dataFilter);
//echo "<pre>";print_r($data->result); exit;

?>

<script type="text/javascript">
		var $j = jQuery.noConflict();

	</script>

<style>
	#dataForm th { text-align: right; font-size: 12px; color:#000000; }
	#dataForm td { text-align: right; font-size: 12px; color:#000000; }
	.ui-button { font-size: 9px !important; }
</style>

<?

if ($user->guest) {
	echo $notLoggedInMessage;
} else { ?>

<p style="float:right;">
<button onclick="submit('<?=$frontEdit?>', 'action=edit&contentTypeId=4')">Добави ново събитие</button>
</p>

<div style="width: 100%">
<form id="mainForm">

<table class="adminlistz" style="width: 100%">
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
			<th class="title">&nbsp;

			</th>
		</tr>
	</thead>
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
			<td nowrap="nowrap" width="<?=($isAdmin ? '250px' : '50px')?>" <?=$style?>>
				<button onclick="javascript:doEdit(<?=$data->p["dataId"] ?>); return false;">Edit</button>
				<button onclick="javascript:submit('<?=$frontBrowse?>', 'action=deactivate&contentTypeId=<?=$data->p["contentTypeId"]?>');">Delete</button>
			</td>
		</tr>
		<?php
		$k++;
	}
	?>
	</tbody>
	</table>
</form>
</div>

<?php 
$url = $frontBrowse;
if($last>1){
	// which pages to show - add first and last 5 pages
	$showPagesNum = 5;
	if ($showPagesNum > $last) $showPagesNum = $last;
	for ($i=1; $i<=$showPagesNum; $i++) {
		$pagesToShow[] = $i;
		$pagesToShow[] = $last + 1 - $i;
	}

	if ($pagenum == 1){
	  
	 } elseif ($pagenum == $last) {
		
	 } else {
		 // add 3 pages in the middle
		 $pagesToShow[] = $pagenum - 1;
		 $pagesToShow[] = $pagenum;
		 $pagesToShow[] = $pagenum + 1;
	 }

	// remove duplicted numbers and sort the array
	$pagesToShow = array_unique($pagesToShow);
	sort($pagesToShow);

	echo " <div style='text-align: center'>";
	$prevPage = 0;
	foreach ((array)$pagesToShow as $currPage) {

	//for ($i = 1; $i <= $last; $i++) {
		echo ((($currPage - $prevPage) > 1) ? " ... | " : "");
		if ($currPage != $pagenum) {
			echo " <a href='$url&pagenum=".$currPage."'>".$currPage."</a>";
		} else {
			echo $currPage;
		}
		echo (($currPage != $last) ? " | " : "");

		$prevPage = $currPage;
	}
	echo "</div>";
}


?>


<script type="text/javascript">
var isFrontEnd = '<?=$isFrontEnd?>' || 0;
var frontEdit = '<?=$frontEdit?>';
var backEdit = '<?=$backEdit?>';

function doCreateNew(contentTypeId) {
	if (isFrontEnd) {
		submit(frontEdit, {'action':'edit','contentTypeId':contentTypeId});
	} else {
		submit(backEdit, {'action':'edit','contentTypeId':contentTypeId});
	}

	return false;
}

function doEdit(dataId) {
	if (isFrontEnd) {
		submit(frontEdit, {"action":"edit", "dataId":dataId});
	} else {
		submit(backEdit, {"action":"edit", "dataId":dataId});
	}

	return false;
}

function doDelete(url, contentTypeId) {
	if (!confirm('Are you sure you want to delete this data?')) { return true; }

	submit(url, {"action":"delete", "contentTypeId":contentTypeId});

}

</script>

<? } ?>