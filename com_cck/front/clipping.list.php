<?php
$filter = " data.id > 0 ";

// if no filter selected show the articles from the currrent day
if (!stristr(http_build_query($_POST), "filter")) {
	//$_POST["filterByCategory"] = $fieldList["clippingCategory"]["value"];
	//$_POST["filterByDate"] = date("Y-m-d");
//if no category is selected show only categories available to this user
} elseif (!stristr(http_build_query($_POST), "filterByCategory")) {
	$_POST["filterByCategory"] = !empty($fieldList["clippingCategory"]["value"]) ? $fieldList["clippingCategory"]["value"] : "";
}

if (stristr(http_build_query($_POST), "filter")) {
	//print_r($_POST);exit;
	if(!empty($_POST["filterByCategory"])) {
		// get sub categories
		$r = @$dbClip->query("SELECT id FROM cats WHERE pid in (".$dbClip->quote($_POST["filterByCategory"]).")", PDO::FETCH_NUM);
		$categoryList = array();
		foreach ($r as $v) {
			$categoryList[] = $v[0];
		}
		
		if (empty($categoryList)) {
			$categoryList = explode(",", $_POST["filterByCategory"]);
		}

		$filter .= " AND cats.id in (".implode(",", $categoryList).")";
	}

	if(!empty($_POST["filterBySource"])) {
		$filter .= " AND sources.id in (".$dbClip->quote($_POST["filterBySource"]).")";
	}
	
	if(!empty($_POST["filterBySourceType"])) {
		$filter .= " AND sources.type in (".$dbClip->quote($_POST["filterBySourceType"]).")";
	}

	if(!empty($_POST["filterByDate"])) {
		$_POST["filterByDate"] = date("Y-m-d", strtotime($_POST["filterByDate"]));
		$filter .= " AND data.date = ".$dbClip->quote($_POST["filterByDate"]);
	}
	
	if(!empty($_POST["filterByDateInterval"])) {
		$_POST["filterByDateInterval"] = explode(",", $_POST["filterByDateInterval"]);
		$filter .= " AND data.date BETWEEN ".$dbClip->quote($_POST["filterByDateInterval"][0])." AND ".$dbClip->quote($_POST["filterByDateInterval"][1]);
	}
	
	if(!empty($_POST["filterByText"])) {
		$filter .= " AND match(title,subtitle,author,resume,body) against(".$dbClip->quote($_POST["filterByText"]).") ";
	}

//print_r($_POST);	
//print_r($filter);exit;

	$queryCount = "
		SELECT 
			count(data.id) `count`
		FROM 
			(data) 
			INNER JOIN sources ON (sources.id = data.source AND sources.active = 1) 
			INNER JOIN data_cats ON (data_cats.did = data.id) 
			INNER JOIN cats ON (cats.id = data_cats.cid AND cats.active = 1)
		WHERE
			".$filter." 
		ORDER BY 
			data.id DESC
			";

	$query = "
		SELECT 
			data.id, data.title, data.subtitle, data.resume, data.author, data.date, CONCAT(sources.prefix,' ',sources.name) sourceName
		FROM 
			(data) 
			INNER JOIN sources ON (sources.id = data.source AND sources.active = 1) 
			INNER JOIN data_cats ON (data_cats.did = data.id) 
			INNER JOIN cats ON (cats.id = data_cats.cid AND cats.active = 1)
		WHERE
			".$filter." 
		ORDER BY 
			data.id DESC
			";
	//print_r($query);exit;
	$total = @$dbClip->query($queryCount, PDO::FETCH_OBJ)->fetch()->count;	
	//echo "total $total";exit;

	//This tells us the page number of our last page
	$last = ceil($total/$clippingPageRows);
	$pagenum = @$_POST[pagenum];

	//this makes sure the page number isn't below one, or more than our maximum pages
	if ($pagenum < 1){
		$pagenum = 1;
	} elseif ($pagenum > $last) {
		$pagenum = $last;
	}
	 
	$start=($pagenum - 1) * $clippingPageRows;
	$end = $clippingPageRows;

	$query .= " LIMIT {$start}, {$end}";
	//exit('qq'.$query);
	$r = $dbClip->query($query, PDO::FETCH_OBJ);
	if ($r) {
		foreach ($r as $k => $v) {
			?>
			<span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.article&dataId=<?=$v->id?>', 'post');"><b><?=$v->title?></b></span><br />
			<? if (!empty($v->subtitle)) {?>
				<span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.article&dataId=<?=$v->id?>', 'post')"><b><?=$v->subtitle?></b></span><br />
			<? } ?>
			
			<span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.article&dataId=<?=$v->id?>', 'post')" href="#"><?=$v->sourceName?></span> | 
			<span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.article&dataId=<?=$v->id?>', 'post')" href="#"><?=date("d.m.Y", strtotime($v->date))?></span>
			<br />
			
			<?=$v->resume?><br />
			<p style="float: right;">
				<span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.article&dataId=<?=$v->id?>', 'post')">Прочети още</span>
				<span class="link" onclick="submit('<?=$clippingArticleSimpleUrl?>', 'dataId=<?=$v->id?>&getPdf=1', 'get')">PDF</span>
				<span class="link" onclick="window.open('<?=$clippingArticleSimpleUrl?>?dataId=<?=$v->id?>','Print','width=400,height=200,toolbar=no,menubar=no,location=no')">Print</span>
			</p>
			<br /><hr /><br />
			
			<?
		}
		
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
			foreach ($pagesToShow as $currPage) {
		
				echo ((($currPage - $prevPage) > 1) ? " ... | " : "");
				if ($currPage != $pagenum) {
					?><span class="link" onclick="submit('<?=$clippingUrl?>', 'page=<?=$_POST["page"]?>&json=<?=urlencode(json_encode($vars))?>&pagenum=<?=$currPage?>', 'post')"><?=$currPage?></span><?
				} else {
					echo $currPage;
				}
				echo (($currPage != $last) ? " | " : "");
		
				$prevPage = $currPage;
			}
			echo "</div>";
		}
	} else {
		$mainframe->enqueueMessage($clippingUrl, $connectionError, "error");
	}
}