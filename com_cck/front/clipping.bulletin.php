<?php
//print_r($_POST);exit;
$vars = @json_decode(urldecode($_POST["json"]));
unset($_POST["json"]);

if (!empty($vars->filterByCategory)) {
	$filterByCategoryArray = $vars->filterByCategory;
	$filterByCategory = $_POST["filterByCategory"] = @implode(",", $vars->filterByCategory);
}

if (!empty($vars->filterBySource)) {
	$filterBySourceArray = $vars->filterBySource;
	$filterBySource = $_POST["filterBySource"] = @implode(",", $vars->filterBySource);
}

if (!empty($vars->filterBySourceType)) {
	$filterBySourceTypeArray = $vars->filterBySourceType;
	$filterBySourceType = $_POST["filterBySourceType"] = @implode(",", $vars->filterBySourceType);
}

if (!empty($vars->filterByDate)) {
	$vars->filterByDate = $_POST["filterByDate"] = (strtotime($vars->filterByDate) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDate : $fieldList["startClippingDate"]["value"];
} elseif(strtotime($fieldList["startClippingDate"]["value"])) {
	$vars->filterByDate = $_POST["filterByDate"] = $fieldList["startClippingDate"]["value"];
}

//get categories
$categoryList = array();
$r = $dbClip->query("SELECT id, name FROM cats WHERE id in (".$fieldList["clippingCategory"]["value"].") AND active = 1", PDO::FETCH_OBJ);
if ($r) {
	foreach ($r as $k => $v) {
		$categoryList[$v->id] = $v->name;
	}
} else {
	$mainframe->enqueueMessage($clippingUrl, $connectionError, "error");
}

//get sources - show only sources from available categories
$sourceList = array();
if (!empty($_SESSION["clippingRealCategory"])) {
	$query = "
	SELECT DISTINCT 
		sources.id, sources.name 
	FROM 
		(data) 
		INNER JOIN data_cats ON (data_cats.did = data.id) 
		INNER JOIN cats ON (cats.id = data_cats.cid AND cats.active = 1) 
		INNER JOIN sources ON (sources.id = data.source AND sources.active = 1) 
	WHERE 
		cats.id in (".$_SESSION["clippingRealCategory"].") ORDER BY sources.name";
} else {
	$query = "SELECT id, name FROM sources WHERE active = 1";
}

$r = $dbClip->query($query, PDO::FETCH_OBJ);
if ($r) {
	foreach ($r as $k => $v) {
		$sourceList[$v->id] = $v->name;
	}
} else {
	$mainframe->enqueueMessage($clippingUrl, $connectionError, "error");
}
?>

<form method="post" action="<?=$clippingUrl?>" id="mainForm" onsubmit="return prepareSubmit()">
	<input type="hidden" name="page" value="clipping.bulletin" />
	<input type="hidden" name="json" value="" />
	<label class="customLabel">Тема</label>
	<select name="filterByCategory" multiple="multiple" title="Изберете тема" value="">
		<?
		foreach ($categoryList as $k => $v) {
			?><option value="<?=$k?>" <?=(@in_array($k, $filterByCategoryArray) ? "selected" : "")?>><?=$v?></option><?
		}
		?>
	</select>
	<br />
	
	<label class="customLabel">Типове източници</label>
	<select name="filterBySourceType" multiple="multiple" value="">
		<?
		foreach ($clippingSorceTypeList as $k => $v) {
			?><option value="<?=$k?>" <?=(@in_array($k, $filterBySourceTypeArray) ? "selected" : "")?>><?=$v?></option><?
		}
		?>
	</select>
	<br />
	
	<label class="customLabel">Източник</label>
	<select name="filterBySource" multiple="multiple" title="Изберете източник" value="">
		<?
		foreach ($sourceList as $k => $v) {
			?><option value="<?=$k?>" <?=(@in_array($k, $filterBySourceArray) ? "selected" : "")?>><?=$v?></option><?
		}
		?>
	</select>
	<br />
	
	<label class="customLabel">Дата</label>
	<input type="text" name="filterByDate" value="<?=$vars->filterByDate?>" />
	<br />
	<br />
	
	<label class="customLabel">&nbsp;</label>
	<input type="submit" value="Покажи" />
	<br /><br /><br />
</form>

<script type="text/javascript">
	$j("[name=filterBySourceType], [name=filterBySource], [name=filterByCategory]").multiselect({
   	  noneSelectedText: "Изберете критерий",
	  multiple: true,
	  selectedList: 10,
      minWidth: 400,
      width: 400
   }).multiselectfilter();
				   
	// datepicker
	$j( "[name=filterByDate]" ).datepicker({
		dateFormat: 'dd.mm.yy',
		showOtherMonths: true,
		selectOtherMonths: true
		//changeMonth: true,
		//changeYear: true
	});
	
	$j('input[type=submit]').button();
	
	function prepareSubmit() {
		$j('[name=json]').val(JSON.stringify(form2object('mainForm')));
		
		return true;
	}
</script>