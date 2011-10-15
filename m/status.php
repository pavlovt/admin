<?
// 20101102 -- george -- created

set_time_limit(0);

// IMPORTANT: set these according to this page. session messages that need to be shown on this page are recorded 
// according to these values!
define("SECTION", "reportsCurrentStatus");
define("PAGE", "browse");
$pageId = "reportsCurrentStatus";
$error = NULL;
$errorArray = array();
$readDbRequired = TRUE;


// includes
require_once("../frontEnd/include/config.frontEnd.php");
require_once("common/sessionInit.php");

require_once("class/storeMonger/class.smSalesPerson.php");
require_once("class/gameMonger/class.operator.php");

/*require_once("../frontEnd/checkLogin.php");

require_once("../frontEnd/privDetermine.php");
if (!$privs["privReport"]["read"]) { ?><script>alert('You do not have access to this screen or operation.'); history.back();</script><? exit; }
$canModify = $privs["privReport"]["modify"];
*/
/*** IMPLEMENT DBR ****/
//$dbr = $db;

// include this whenever want to prevent transactions
require_once("common/preventDoubleTransactions.php");


function calculateCurrentStatus($dbr, $from, $to) {

/*"
select (IF (LENGTH(pd.phone) < 8, 1, case SUBSTRING(pd.phone, 1, 1) when 0 then 1 when 1 then 1 when 2 then 1 when 3 then 2 when 5 then 3 when 7 then 4 else 0 end)) as smsc, count(pd.phone) from player_days pd where pd.sent_sms_count > 0 and pd.date between '2010-12-11' and '2010-12-11' group by smsc

select (IF (LENGTH(pd.phone) < 8, 1, case SUBSTRING(pd.phone, 1, 1) when 0 then 1 when 1 then 1 when 2 then 1 when 3 then 2 when 5 then 3 when 7 then 4 else 0 end)) as smsc, sum(pd.sent_sms_count) from player_days pd where pd.sent_sms_count > 0 and pd.date between '2010-12-11' and '2010-12-11' group by smsc

select (IF (LENGTH(pd.phone) < 8, 1, case SUBSTRING(pd.phone, 1, 1) when 0 then 1 when 1 then 1 when 2 then 1 when 3 then 2 when 5 then 3 when 7 then 4 else 0 end)) as smsc, sum(pd.received_sms_count) from player_days pd where pd.received_sms_count > 0 and pd.date between '2010-12-11' and '2010-12-11' group by smsc

select smsc, count(phone) from players where is_stopped = 0 and date(date_registered) = date(now()) group by smsc
";*/
	
	$playersArray = array();
	$newPlayersArray = array();
	$sentArray = array();
	$receivedArray = array();

	// $periodPlayersArray
	$r = @mysql_query("select players.smsc, count(distinct players.phone) from player_days, players where player_days.phone = players.phone and  players.is_stopped = 0 and player_days.date between '".$from."' and '".$to."' and player_days.received_sms_count > 0 group by smsc", $dbr);
	while ($w = @mysql_fetch_row($r)) {
	    $playersArray[$w[0]] = $w[1];
	}

	// $periodSentArray
	/*$r = @mysql_query("select (IF (LENGTH(pd.phone) < 8, 1, case SUBSTRING(pd.phone, 1, 1) when 0 then 1 when 1 then 1 when 2 then 1 when 3 then 2 when 5 then 3 when 7 then 4 else 0 end)) as smsc, sum(pd.sent_sms_count) from player_days pd where pd.sent_sms_count > 0 and pd.date between '".$from."' and '".$to."' group by smsc", $dbr);
	while ($w = @mysql_fetch_row($r)) {
	    $sentArray[$w[0]] = $w[1];
	}*/

	// $periodReceivedArray
	$r = @mysql_query("select (IF (LENGTH(pd.phone) < 8, 1, case SUBSTRING(pd.phone, 1, 1) when 0 then 1 when 1 then 1 when 2 then 1 when 3 then 2 when 5 then 3 when 7 then 4 else 0 end)) as smsc, sum(pd.received_sms_count) from player_days pd where pd.received_sms_count > 0 and pd.date between '".$from."' and '".$to."' group by smsc", $dbr);
	while ($w = @mysql_fetch_row($r)) {
	    $receivedArray[$w[0]] = $w[1];
	}

	// $periodNewPlayersArray
	$r = @mysql_query("select smsc, count(phone) from players where is_stopped = 0 and date(date_registered) between '".$from."' and '".$to."' group by smsc", $dbr);
	while ($w = @mysql_fetch_row($r)) {
	    $newPlayersArray[$w[0]] = $w[1];
	}
	
	return array($playersArray, $newPlayersArray, $sentArray, $receivedArray);
	
}

//print_r(calculateCurrentStatus($dbr, '2010-12-11', '2010-12-11')); exit;
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=320" />
<!--	<meta name="viewport" content="target-densitydpi=device-dpi, width=device-width" />-->
	<title>Current Status</title>
	
	<style>
		.simpleTable {
			border-collapse:collapse;
			width: 100%;
			margin: 20px;
			background-color: #E7E7E7;
		}
		.simpleTable td {
			border: 1.5px solid white;
			padding: 5px;
			font-size: 11px;
			text-align:center;
			width: 70px;
		}
		.simpleTable th {
			border: 1.5px solid white;
			padding: 5px;
			font-size: 11px;
			text-align:center;
			width: 70px;
			color: white;
			background: #2373BB;
		}
		.simpleTable th.l {
			width: 390px;
		}
		.simpleTable td.l {
			text-align: left;
			width: 390px;
		}
		.simpleTable td.r {
			text-align: right;
		}
		.simpleTable tr.b {
			background: #f8f8f8;
		}

	</style>
	
	
</head>

<body leftmargin="0" topmargin="0" rightmargin="0" bgcolor="#ffffff" id="bdy" style="width: 320px;">
<a href="dayChart.php">Day Compare by Slor & Chart</a>
<h2>Current Status</h2>
<p style="float: left; margin-top: -10px;"><strong><?=date("d/m/Y", strtotime("now"))?> <?=date("H:i", strtotime("now"))?></strong></p>
<div style="clear: both;"></div><br />

<strong>Game day: <?=date("d/m/Y", strtotime("now"))?></strong>
<table id="todayTable" cellpadding="0" cellspacing="0" border="0" class="simpleTable" style="white-space: nowrap;">
<thead>
<tr>
<th style="text-align: center;">Operator ID</th>
<th style="text-align: center;">Operator</th>
<th style="text-align: center;">Unique Players</th>
<th style="text-align: center;">Unique Players %</th>
<th style="text-align: center;">New Players</th>
<th style="text-align: center;">New Players %</th>
<th style="text-align: center;">SMS</th>
<th style="text-align: center;">SMS %</th>
<? /* <th style="text-align: center;">Sent messages</th>
<th style="text-align: center;">Sent messages %</th>*/ ?>
</tr>
</thead>
<tbody>
<?
list($todayPlayersArray, $todayNewPlayersArray, $todaySentArray, $todayReceivedArray) = calculateCurrentStatus($dbr, date("Y-m-d"), date("Y-m-d"));
$sumSent = 0; $sumPlayers = 0; $sumBulkSent = 0; $sumReceived = 0;
$sumPlayers = array_sum($todayPlayersArray);
$sumNewPlayers = array_sum($todayNewPlayersArray);
$sumReceived = array_sum($todayReceivedArray);
//$sumSent = array_sum($todaySentArray);
$newPlayersTotalPercent = number_format(($sumPlayers ? $sumNewPlayers/$sumPlayers : 0)*100, 1, '.', ' ');
$operatorArray = operator::$operatorArray;
foreach ($operatorArray as $operatorId => $operatorName) {

	//calculating percent
	$playersPercent = number_format((($sumPlayers && isset($todayPlayersArray[$operatorId])) ? $todayPlayersArray[$operatorId]/$sumPlayers : 0)*100, 1, '.', ' ');
	$newPlayersPercent = number_format((($sumNewPlayers && isset($todayNewPlayersArray[$operatorId])) ? $todayNewPlayersArray[$operatorId]/$sumNewPlayers : 0)*100, 1, '.', ' ');
	$receivedPercent = number_format((($sumReceived && isset($todayReceivedArray[$operatorId])) ? $todayReceivedArray[$operatorId]/$sumReceived : 0)*100, 1, '.', ' ');
	$sentPercent = number_format((($sumSent && isset($todaySentArray[$operatorId])) ? $todaySentArray[$operatorId]/$sumSent : 0)*100, 1, '.', ' ');
	$bulkSentPercent = number_format((($sumBulkSent && isset($todayBulkSentArray[$operatorId])) ? $todayBulkSentArray[$operatorId]/$sumBulkSent : 0)*100, 1, '.', ' ');
    ?>
    <tr>
        <td style="text-align: center;"><?=$operatorId?></td>
        <td style="text-align: center;"><?=$operatorName?></td>
        <td style="text-align: right;"><?=number_format((isset($todayPlayersArray[$operatorId]) ? $todayPlayersArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$playersPercent?>%</td>
        <td style="text-align: right;"><?=number_format((isset($todayNewPlayersArray[$operatorId]) ? $todayNewPlayersArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$newPlayersPercent?>%</td>
        <td style="text-align: right;"><?=number_format((isset($todayReceivedArray[$operatorId]) ? $todayReceivedArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$receivedPercent?>%</td>
        <? /*<td style="text-align: right;"><?=number_format((isset($todaySentArray[$operatorId]) ? $todaySentArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$sentPercent?>%</td>*/ ?>
    </tr>
    <?
} 
?>
</tbody>
<tfoot>
	<tr>
		<th>&nbsp;</th>
		<th style="text-align:center;">Total:</th>
		<th style="text-align: right;" id="sumPlayers"><?=number_format($sumPlayers, 0, '.', ' ')?></th>
		<th style="text-align: right;" id="sumReceived"><?=($sumPlayers ? "100%" : "0%")?></th>
		<th style="text-align: right;" id="sumPlayers"><?=number_format($sumNewPlayers, 0, '.', ' ')?></th>
		<th style="text-align: right;" id="sumReceived"><?=$newPlayersTotalPercent?>%</th>
		<th style="text-align: right;" id="sumReceived"><?=number_format($sumReceived, 0, '.', ' ')?></th>
		<th style="text-align: right;" id="sumReceived"><?=($sumReceived ? "100%" : "0%")?></th>
		<? /*<th style="text-align: right; padding-right: 10px;" id="sumSent"><?=number_format($sumSent, 0, '.', ' ')?></th>
		<th style="text-align: right; padding-right: 10px;" id="sumReceived"><?=($sumSent ? "100%" : "0%")?></th> */ ?>
	</tr>
</tfoot>
</table>
<br /><br />

<strong>Game day: <?=date("d/m/Y", strtotime("yesterday"))?></strong>
<table id="yesterdayTable" cellpadding="0" cellspacing="0" border="0" class="simpleTable" style="white-space: nowrap;">
<thead>
<tr>
<th style="text-align: center;">Operator ID</th>
<th style="text-align: center;">Operator</th>
<th style="text-align: center;">Unique Players</th>
<th style="text-align: center;">Unique Players %</th>
<th style="text-align: center;">New Players</th>
<th style="text-align: center;">New Players %</th>
<th style="text-align: center;">SMS</th>
<th style="text-align: center;">SMS %</th>
<? /*<th style="text-align: center;">Sent messages</th>
<th style="text-align: center;">Sent messages %</th>*/ ?>
</tr>
</thead>
<tbody>
<?
list($yesterdayPlayersArray, $yesterdayNewPlayersArray, $yesterdaySentArray, $yesterdayReceivedArray) = calculateCurrentStatus($dbr, date("Y-m-d", strtotime("yesterday")), date("Y-m-d", strtotime("yesterday")));
$sumSent = 0; $sumPlayers = 0; $sumBulkSent = 0; $sumReceived = 0;
$sumPlayers = array_sum($yesterdayPlayersArray);
$sumNewPlayers = array_sum($yesterdayNewPlayersArray);
$sumReceived = array_sum($yesterdayReceivedArray);
//$sumSent = array_sum($yesterdaySentArray);
$newPlayersTotalPercent = number_format(($sumPlayers ? $sumNewPlayers/$sumPlayers : 0)*100, 1, '.', ' ');
$operatorArray = operator::$operatorArray;
foreach ($operatorArray as $operatorId => $operatorName) {

	//calculating percent
	$playersPercent = number_format((($sumPlayers && isset($yesterdayPlayersArray[$operatorId])) ? $yesterdayPlayersArray[$operatorId]/$sumPlayers : 0)*100, 1, '.', ' ');
	$newPlayersPercent = number_format((($sumNewPlayers && isset($yesterdayNewPlayersArray[$operatorId])) ? $yesterdayNewPlayersArray[$operatorId]/$sumNewPlayers : 0)*100, 1, '.', ' ');
	$receivedPercent = number_format((($sumReceived && isset($yesterdayReceivedArray[$operatorId])) ? $yesterdayReceivedArray[$operatorId]/$sumReceived : 0)*100, 1, '.', ' ');
	$sentPercent = number_format((($sumSent && isset($yesterdaySentArray[$operatorId])) ? $yesterdaySentArray[$operatorId]/$sumSent : 0)*100, 1, '.', ' ');
	$bulkSentPercent = number_format((($sumBulkSent && isset($yesterdayBulkSentArray[$operatorId])) ? $yesterdayBulkSentArray[$operatorId]/$sumBulkSent : 0)*100, 1, '.', ' ');
    ?>
    <tr>
        <td style="text-align: center;"><?=$operatorId?></td>
        <td style="text-align: center;"><?=$operatorName?></td>
        <td style="text-align: right;"><?=number_format((isset($yesterdayPlayersArray[$operatorId]) ? $yesterdayPlayersArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$playersPercent?>%</td>
        <td style="text-align: right;"><?=number_format((isset($yesterdayNewPlayersArray[$operatorId]) ? $yesterdayNewPlayersArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$newPlayersPercent?>%</td>
        <td style="text-align: right;"><?=number_format((isset($yesterdayReceivedArray[$operatorId]) ? $yesterdayReceivedArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$receivedPercent?>%</td>
        <? /*<td style="text-align: right;"><?=number_format((isset($yesterdaySentArray[$operatorId]) ? $yesterdaySentArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$sentPercent?>%</td>*/ ?>
    </tr>
    <?
} 
?>
</tbody>
<tfoot>
	<tr>
		<th>&nbsp;</th>
		<th style="text-align:center;">Total:</th>
		<th style="text-align: right;" id="sumPlayers"><?=number_format($sumPlayers, 0, '.', ' ')?></th>
		<th style="text-align: right;" id="sumReceived"><?=($sumPlayers ? "100%" : "0%")?></th>
		<th style="text-align: right;" id="sumPlayers"><?=number_format($sumNewPlayers, 0, '.', ' ')?></th>
		<th style="text-align: right;" id="sumReceived"><?=$newPlayersTotalPercent?>%</th>
		<th style="text-align: right;" id="sumReceived"><?=number_format($sumReceived, 0, '.', ' ')?></th>
		<th style="text-align: right;" id="sumReceived"><?=($sumReceived ? "100%" : "0%")?></th>
		<? /*<th style="text-align: right; padding-right: 10px;" id="sumSent"><?=number_format($sumSent, 0, '.', ' ')?></th>
		<th style="text-align: right; padding-right: 10px;" id="sumReceived"><?=($sumSent ? "100%" : "0%")?></th>*/ ?>
	</tr>
</tfoot>
</table>
<br /><br />

<strong>All game: 11/12/2010 - <?=date("d/m/Y", strtotime("now"))?>, <?=(1 + intval((strtotime("now") - strtotime("12/11/2010")) / 86400))?> days</strong>
<table id="dataTable" cellpadding="0" cellspacing="0" border="0" class="simpleTable" style="white-space: nowrap;">
<thead>
<tr>
<th style="text-align: center;">Operator ID</th>
<th style="text-align: center;">Operator</th>
<th style="text-align: center;">Unique Players</th>
<th style="text-align: center;">Unique Players %</th>
<th style="text-align: center;">SMS</th>
<th style="text-align: center;">SMS %</th>
<? /*<th style="text-align: center;">Sent messages</th>
<th style="text-align: center;">Sent messages %</th>*/ ?>
</tr>
</thead>
<tbody>
<?
list($playersArray, $tmp, $sentArray, $receivedArray) = calculateCurrentStatus($dbr, '2010-12-11', date("Y-m-d"));
$sumSent = 0; $sumPlayers = 0; $sumBulkSent = 0; $sumReceived = 0;
$sumPlayers = array_sum($playersArray);
$sumReceived = array_sum($receivedArray);
//$sumSent = array_sum($sentArray);
$operatorArray = operator::$operatorArray;
foreach ($operatorArray as $operatorId => $operatorName) {

	//calculating percent
	$playersPercent = number_format((($sumPlayers && isset($playersArray[$operatorId])) ? $playersArray[$operatorId]/$sumPlayers : 0)*100, 1, '.', ' ');
	$receivedPercent = number_format((($sumReceived && isset($receivedArray[$operatorId])) ? $receivedArray[$operatorId]/$sumReceived : 0)*100, 1, '.', ' ');
	$sentPercent = number_format((($sumSent && isset($sentArray[$operatorId])) ? $sentArray[$operatorId]/$sumSent : 0)*100, 1, '.', ' ');
	$bulkSentPercent = number_format((($sumBulkSent && isset($bulkSentArray[$operatorId])) ? $bulkSentArray[$operatorId]/$sumBulkSent : 0)*100, 1, '.', ' ');
    ?>
    <tr>
        <td style="text-align: center;"><?=$operatorId?></td>
        <td style="text-align: center;"><?=$operatorName?></td>
        <td style="text-align: right;"><?=number_format((isset($playersArray[$operatorId]) ? $playersArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$playersPercent?>%</td>
        <td style="text-align: right;"><?=number_format((isset($receivedArray[$operatorId]) ? $receivedArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$receivedPercent?>%</td>
        <? /*<td style="text-align: right;"><?=number_format((isset($sentArray[$operatorId]) ? $sentArray[$operatorId] : 0), 0, '.', ' ')?></td>
        <td style="text-align: right; font-style: italic;"><?=$sentPercent?>%</td>*/ ?>
    </tr>
    <?
} 
?>
</tbody>
<tfoot>
	<tr>
		<th>&nbsp;</th>
		<th style="text-align:center;">Total:</th>
		<th style="text-align: right;" id="sumPlayers"><?=number_format($sumPlayers, 0, '.', ' ')?></th>
		<th style="text-align: right;" id="sumReceived"><?=($sumPlayers ? "100%" : "0%")?></th>
		<th style="text-align: right;" id="sumReceived"><?=number_format($sumReceived, 0, '.', ' ')?></th>
		<th style="text-align: right;" id="sumReceived"><?=($sumReceived ? "100%" : "0%")?></th>
		<? /*<th style="text-align: right; padding-right: 10px;" id="sumSent"><?=number_format($sumSent, 0, '.', ' ')?></th>
		<th style="text-align: right; padding-right: 10px;" id="sumReceived"><?=($sumSent ? "100%" : "0%")?></th>*/ ?>
	</tr>
</tfoot>
</table>
</body>
</html>
