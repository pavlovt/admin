
<?php
$r = $dbClip->query("SELECT id, name FROM cats WHERE id in (".$fieldList["clippingCategory"]["value"].") AND active = 1 ORDER BY weight", PDO::FETCH_OBJ);
if ($r) {
	//var_dump($r->fetchAll());
	foreach ($r as $k => $v) {
		?><span class="cat" onclick="submit('<?=$clippingUrl?>', 'page=clipping.today&filterByCategory=<?=$v->id?>&filterByDate=<?=date("Y-m-d")?>', 'post')"><b><?=$v->name?></b></span><br /><?
		
		$r1 = $dbClip->query("SELECT id, name FROM cats WHERE pid in (".$v->id.") AND active = 1 ORDER BY weight", PDO::FETCH_OBJ);
		if ($r1) {
			//var_dump($r->fetchAll());
			?><hr size="1px" color="#CCCCCC"><?
			foreach ($r1 as $k1 => $v1) {
				?><li><span class="link" onclick="submit('<?=$clippingUrl?>', 'page=clipping.today&filterByCategory=<?=$v1->id?>&filterByDate=<?=date("Y-m-d")?>', 'post')"><?=$v1->name?></span></li><br /><?
			}
			?><br><br><?
		}
	}
} else {
	$mainframe->enqueueMessage($clippingUrl, $connectionError, "error");
}
