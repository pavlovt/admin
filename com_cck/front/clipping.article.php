<?php
$filter = " data.id > 0";

global $mainframe;
//print_r($_POST);exit;
if(empty($_POST["dataId"])) {
	$mainframe->redirect($clippingUrl, "На страницата са подадени невалидни параметри", "error");
}

$query = "
	SELECT 
		data.id, data.title, data.subtitle, data.body, data.author, data.date, CONCAT(sources.prefix,' ',sources.name) sourceName
	FROM 
		(data) 
		INNER JOIN sources ON (sources.id = data.source AND sources.active = 1) 
		INNER JOIN data_cats ON (data_cats.did = data.id) 
		INNER JOIN cats ON (cats.id = data_cats.cid AND cats.active = 1)
	WHERE
		data.id = ".(int)$_POST["dataId"];

//exit('qq'.$query);
$r = $dbClip->query($query, PDO::FETCH_OBJ);
if ($r) {
		$v = $r->fetch();
		?>
		<br />
		<b><?=$v->title?></b><br />
		<?=($v->subtitle ? $v->subtitle."<br />" : "")?>
		
		<?=$v->sourceName?> | <?=date("d.m.Y", strtotime($v->date))?><br />
		
		<?=$v->resume?><br />
		<?=$v->body?><br />
		<br /><br />
		
		<span class="link" onclick="submit('<?=$clippingArticleSimpleUrl?>', 'dataId=<?=$_POST["dataId"]?>&getPdf=1', 'get')">PDF</span>
		<span class="link" onclick="window.open('<?=$clippingArticleSimpleUrl?>?dataId=<?=$_POST["dataId"]?>','Print','width=400,height=200,toolbar=no,menubar=no,location=no')">Print</span>
		<span class="link" onclick="window.open('<?=$clippingArticleSimpleUrl?>?dataId=<?=$_POST["dataId"]?>&sendEmail=1','Send to email','width=400,height=200,toolbar=no,menubar=no,location=no')">Print</span>
		<?
	
} else {
	$mainframe->enqueueMessage($clippingUrl, $connectionError, "error");
}
