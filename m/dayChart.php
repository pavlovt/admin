<?
// 20101702 -- todor -- created

set_time_limit(0);

// IMPORTANT: set these according to this page. session messages that need to be shown on this page are recorded 
// according to these values!
define("SECTION", "reports");
define("PAGE", "DayCompareBySlot");
$pageId = "reportsDayCompareBySlot";
$error = NULL;
$errorArray = array();
$readDbRequired = TRUE;

// includes
require_once("../frontEnd/include/config.frontEnd.php");
require_once("common/sessionInit.php");

require_once("class/storeMonger/class.smSalesPerson.php");

/*require_once("../frontEnd/checkLogin.php");

require_once("../frontEnd/privDetermine.php");
if (!$privs["privReport"]["read"]) { ?><script>alert('You do not have access to this screen or operation.'); history.back();</script><? exit; }
*/
// include this whenever want to prevent transactions
require_once("common/preventDoubleTransactions.php");

//$dbr = $db;

$typeArray = array("1" => "SMS", "2" => "Players", "3" => "New Players");
$timeIntervalArray = array("1" => "15 min", "2" => "30 min", "3" => "60 min");
$resultArray = array("1" => "By Slot", "2" => "Cumulative");
$datesArray = array("rDate1","rDate2","rDate3","rDate4","rDate5");

if ((isset($_GET["action"])) && ($_GET["action"] == "loadDetails")) {

	$rType = isset($_GET["rType"]) ? $_GET["rType"] : NULL;
	$rTimeInterval = isset($_GET["rTimeInterval"]) ? $_GET["rTimeInterval"] : NULL;
	$rResult = isset($_GET["rResult"]) ? $_GET["rResult"] : NULL;
	
	$rDate1 = isset($_GET["rDate1"]) ? $_GET["rDate1"] : NULL;
	$rDate2 = isset($_GET["rDate2"]) ? $_GET["rDate2"] : NULL;
	$rDate3 = isset($_GET["rDate3"]) ? $_GET["rDate3"] : NULL;
	$rDate4 = isset($_GET["rDate4"]) ? $_GET["rDate4"] : NULL;
	$rDate5 = isset($_GET["rDate5"]) ? $_GET["rDate5"] : NULL;

	$q = "SELECT ti.from, ti.to FROM time_interval ti where ti.interval_group = '".e($timeIntervalArray[$rTimeInterval], $dbr)."' ORDER BY ti.from";
	//echo $q; exit;
	$r = @mysql_query($q, $dbr);
			
	while ($w = @mysql_fetch_row($r)) {
		$result['timeInterval'][] = date("H:i", strtotime($w[0]))." - ".date("H:i", strtotime($w[1]));
	}
	
	mysql_free_result($r);
	
	$intervalGroup = e($timeIntervalArray[$rTimeInterval], $dbr);
	
	switch ($rType) {
		case 2:
			if ($rResult == 2) {
			    $q = 'SELECT ti.from, ti.to, COUNT(DISTINCT r.phone) FROM (time_interval ti) left outer join received r on (TIME(r.datetime) <= ti.to AND DATE(r.datetime) = \"$date\") where ti.interval_group = \"$intervalGroup\" GROUP BY DATE(r.datetime), ti.id ORDER BY ti.from';
			} else {
				$q = 'SELECT ti.from, ti.to, COUNT(DISTINCT r.phone) FROM (time_interval ti) left outer join received r on (TIME(r.datetime) between ti.from AND ti.to AND DATE(r.datetime) = \"$date\") where ti.interval_group = \"$intervalGroup\" GROUP BY DATE(r.datetime), ti.id ORDER BY ti.from';
			}
			break;
			
		CASE 3:
			if ($rResult == 2) {
			    $q = 'SELECT ti.from, ti.to, COUNT(DISTINCT p.phone) FROM (time_interval ti) left outer join players p on (TIME(p.date_registered) <= ti.to AND DATE(p.date_registered) = \"$date\") where ti.interval_group = \"$intervalGroup\" GROUP BY DATE(p.date_registered), ti.id ORDER BY ti.from';
			} else {
				$q = 'SELECT ti.from, ti.to, COUNT(DISTINCT p.phone) FROM (time_interval ti) left outer join players p on (TIME(p.date_registered) between ti.from AND ti.to AND DATE(p.date_registered) = \"$date\") where ti.interval_group = \"$intervalGroup\" GROUP BY DATE(p.date_registered), ti.id ORDER BY ti.from';
			}
			break;
			
		case 1:
		default:
			if ($rResult == 2) {
			    $q = 'SELECT ti.from, ti.to, COUNT(r.phone) FROM (time_interval ti) left outer join received r on (TIME(r.datetime) <= ti.to AND DATE(r.datetime) = \"$date\") where ti.interval_group = \"$intervalGroup\" GROUP BY DATE(r.datetime), ti.id ORDER BY ti.from';
			} else {
				$q = 'SELECT ti.from, ti.to, COUNT(r.phone) FROM (time_interval ti) left outer join received r on (TIME(r.datetime) between ti.from AND ti.to AND DATE(r.datetime) = \"$date\") where ti.interval_group = \"$intervalGroup\" GROUP BY DATE(r.datetime), ti.id ORDER BY ti.from';
			}
			break;
	}
	
	foreach ($datesArray as $rDate) {
		if (isset($$rDate) && strtotime(str_replace("/", "-",$$rDate))) {
			$date = date("Y-m-d", strtotime(str_replace("/", "-",$$rDate)));
			eval("\$qq = \"$q\";");
			
			//echo $qq; exit;
			$r = @mysql_query($qq, $dbr);

			while ($w = @mysql_fetch_row($r)) {
				$result[$rDate][] = number_format($w[2], "0",".", " ");
				$resultUnformated[$rDate][] = $w[2];
			}
		} else {
			foreach ($result["timeInterval"] as $timeInterval) {
				$result[$rDate][] = "";
			}
		}
	}
	

	// init chart components
	for ($i = 0; $i <= 24; $i++) { 
		// x-axis label
		if (!($i % 3)) { 
			$chartLabelX[] = $i.":00"; 
		} else {
			$chartLabelX[] = '';
		}
	}

	//var_dump($chartLabelX); exit;
	$result["chart"] = "";
	if (isset($result["rDate1"][0])) {
		// Process the data
		$chartMaxValue = 0;
		foreach ($resultUnformated as $type => $data) {
			//print_r($w);
			if ($type == "timeInterval") {
				continue;
			}
			
			if (max($data) > $chartMaxValue) { $chartMaxValue = (int)max($data); }
		}
		//echo "max ".$chartMaxValue; exit;
		//var_dump($result); exit;
		
		// Retrieve maximum value and round it to a value divisible by a
		$verticalValueStep = ceil($chartMaxValue / 4);
		if($verticalValueStep > 1000) {
			$verticalValueStep = (ceil($verticalValueStep / 1000)) * 1000;
		} elseif($verticalValueStep > 100) {
			$verticalValueStep = (ceil($verticalValueStep / 100)) * 100;
		}
		
		$chartMaxValue = 4 * $verticalValueStep;
		
		// Set vertical label
		$chartLabelY[] = '';
		for($i = 1; $i < 5; $i++) { $chartLabelY[] = sprintf('%.0f', $i * $verticalValueStep); }
		
		// Encode chart data and assign a color for each line
		$chartData["ff0000"] = googleChartSimpleEncode($resultUnformated["rDate1"], $chartMaxValue);
		$chartData["3072F3"] = googleChartSimpleEncode($resultUnformated["rDate2"], $chartMaxValue);
		$chartData["4EFA0F"] = googleChartSimpleEncode($resultUnformated["rDate3"], $chartMaxValue);
		$chartData["A40FFA"] = googleChartSimpleEncode($resultUnformated["rDate4"], $chartMaxValue);
		$chartData["0A0502"] = googleChartSimpleEncode($resultUnformated["rDate5"], $chartMaxValue);
		
		$labels = "";
		foreach ($datesArray as $rDate) {
			$labels .= "|".$$rDate;
		}
		$labels = substr($labels, 1);

		// Compile chart details
		$chart = '<img src="http://chart.apis.google.com/chart?';
		$chart .= 'cht=lc'.'&'; // Chart type (lc = line chart)
		$chart .= 'chd=s:'.implode(array_values($chartData), ',').'&'; // Chart data
		$chart .= 'chco='.implode(array_keys($chartData), ',').'&'; // Chart colors
		$chart .= 'chs=500x300&'; // Chart size (width x height)
		$chart .= 'chdl='.$labels.'&chdlp=b'.'&'; // Chart legend
		$chart .= 'chg=4,25,1,5&'; // Grid lines
		$chart .= 'chxt=x,y'.'&'; // Axis type (x = bottom x-axis; y = left y-axis)
		//$chart .= 'chf=c,lg,90,ffffff,0,76A4FB,0.75|bg,s,EFEFEF&'; // Linear gradient
		$chart .= 'chxl=0:|'.implode($chartLabelX, "|"); // Axis labels (bottom x-axis)
		$chart .= '|1:|'.implode($chartLabelY, "|").'" '; // Axis labels (left y-axis)
		$chart .= '" />';
		
		// make chart location is correct (IE hack)
		$chart = str_replace(" ", "", $chart); $chart = str_replace("imgsrc", "img src", $chart);
		
		// Return image
		$result["chart"] = $chart;
	}		

	
//print_r($result); exit;

	/*header("Content-type: text/plain");
	echo json_encode(array("error" => FALSE, "errorMessage" => "", "result" => $result));
	exit;*/

}

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=320" />
	<title>Day Compare by Slot</title>
	
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
<a href="status.php">Current Status</a>
<h2>Day Compare by Slot</h2>
<div style="clear: both;"></div><br />
<form action="dayChart.php" id="playerDeatils" onsubmit="javascript:showRecord(); return false;">
<input type="hidden" name="action" value="loadDetails">
Type:<br />
<select name="rType">
<option value="1">SMS</option>
<option value="2">Players</option>
<option value="3">New Players</option>
</select>

<br /><br />

Time interval:<br />
<select name="rTimeInterval">
<option value="1">15 min</option>
<option value="2">30 min</option>
<option value="3">60 min</option>
</select>

<br /><br />

Result:<br />
<select name="rResult">
<option value="1">By Slot</option>
<option value="2">Cumulative</option>
</select>

<br /><br />

Dates to compare:<br />
Date 1 <input type="text" name="rDate1" value="<?=date("d/m/Y", strtotime('now'))?>" size="10"><br>
Date 2 <input type="text" name="rDate2" value="<?=date("d/m/Y", strtotime('yesterday'))?>" size="10"><br>
Date 3 <input type="text" name="rDate3" value="" size="10"><br>
Date 4 <input type="text" name="rDate4" value="" size="10"><br>
Date 5 <input type="text" name="rDate5" value="" size="10">
<div style="clear: both;"></div>
<br /><br />
<input type="submit" value="Submit">

</form>

<div id="tableDetails" style="width: 800px;">
<br /><br />

<div id="rChart"><?=isset($result['chart']) ? $result['chart'] : ""?></div>
<br /><br />
<? 
if (isset($result["rDate1"][0])) {
?>
<div id="rDataTableDiv">
<table id="rDataTable" cellpadding="0" cellspacing="0" border="0" class="simpleTable" style="white-space: nowrap;">
<thead>
<tr style="text-align: center;">
<th>Time</th>
<th id="rDateH1"><?=isset($rDate1) ? $rDate1 : ""?></th>
<th id="rDateH2"><?=isset($rDate2) ? $rDate2 : ""?></th>
<th id="rDateH3"><?=isset($rDate3) ? $rDate3 : ""?></th>
<th id="rDateH4"><?=isset($rDate4) ? $rDate4 : ""?></th>
<th id="rDateH5"><?=isset($rDate5) ? $rDate5 : ""?></th>
</tr>
</thead>
<tbody>
<? 
foreach ($result['timeInterval'] as $key => $time) {
?>
		<tr>
		<td><?=$time?></td>
		<td style="text-align: right;"><?=isset($result['rDate1'][$key]) ? $result['rDate1'][$key] : ""?></td>
		<td style="text-align: right;"><?=isset($result['rDate2'][$key]) ? $result['rDate2'][$key] : ""?></td>
		<td style="text-align: right;"><?=isset($result['rDate3'][$key]) ? $result['rDate3'][$key] : ""?></td>
		<td style="text-align: right;"><?=isset($result['rDate4'][$key]) ? $result['rDate4'][$key] : ""?></td>
		<td style="text-align: right;"><?=isset($result['rDate5'][$key]) ? $result['rDate5'][$key] : ""?></td>
		</tr>
<? } ?>
</tbody>
</table>
</div>
<? } ?>
<br /><br />

</div>

</body>
</html>
