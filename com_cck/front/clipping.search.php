<?php
//print_r($_POST);exit;
$vars = @json_decode(urldecode($_POST["json"]));
unset($_POST["json"]);
//print_r($vars);exit;
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

// ensure that there are no html tags, xss etc.
if (!empty($vars->filterByText)) {
	$inputFilter = new InputFilter();
	$vars->filterByText = $_POST["filterByText"] = $inputFilter->process($vars->filterByText);
}

if (!empty($vars->filterByDateFrom) && !empty($vars->filterByDateTo)) {
	$vars->filterByDateFrom = (strtotime($vars->filterByDateFrom) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDateFrom : $fieldList["startClippingDate"]["value"];
	$vars->filterByDateTo = (strtotime($vars->filterByDateTo) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDateTo : $fieldList["startClippingDate"]["value"];
	
	
	$_POST["filterByDateInterval"] = date("Y-m-d", strtotime($vars->filterByDateFrom)).",".date("Y-m-d", strtotime($vars->filterByDateTo));
	
} elseif (!empty($vars->filterByDateFrom) && empty($vars->filterByDateTo)) {
	$vars->filterByDateFrom = (strtotime($vars->filterByDateFrom) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDateFrom : $fieldList["startClippingDate"]["value"];
	$_POST["filterByDate"] = date("Y-m-d", strtotime($vars->filterByDateFrom));
	
} elseif (empty($vars->filterByDateFrom) && !empty($vars->filterByDateTo)) {
	$vars->filterByDateTo = (strtotime($vars->filterByDateTo) - strtotime($fieldList["startClippingDate"]["value"])) > 0 ? $vars->filterByDateTo : $fieldList["startClippingDate"]["value"];
	$_POST["filterByDate"] = date("Y-m-d", strtotime($vars->filterByDateTo));
	
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
	<input type="hidden" name="page" value="clipping.search" />
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
	
	<label class="customLabel">Период</label>
	от дата <input type="text" name="filterByDateFrom" value="<?=$vars->filterByDateFrom?>" />
	до дата <input type="text" name="filterByDateTo" value="<?=$vars->filterByDateTo?>" />
	<br />
	
	<label class="customLabel">Текст</label>
	<input type="text" name="filterByText" value="<?=$vars->filterByText?>" size="65"/>
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
	$j( "[name=filterByDateFrom], [name=filterByDateTo]" ).datepicker({
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