<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr>
	<td colspan="2" style="background: #005DBC; color: #FFFFFF; font-size: 8pt; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; border-bottom: 1px solid #005DBC;">
	<div class="noPrintDiv">
	<strong>You are logged in as &nbsp;&#151;&nbsp;<?=$salesPersonUserName?></strong> (<a href="logout.php" class="whiteLink">logout</a>)
	<input type="text" id="itemOnTopHolder" style="width: 10px; height: 10px; display: none;">
	</div>
	</td>
</tr>

<tr>
	<td width="60%" nowrap colspan="2" style="background: #f1f1f1; color: #000000; font-size: 8pt; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; border-bottom: 1px solid #3399CC; margin-bottom: 70px;">
		<ul id="jsddm">
		<li><a href="index.php">Home</a></li>
		<li><a href="#">Content</a>
			<ul>
				<li><a href="question.browse.php">Questions</a></li>
				<li><a href="storyboard.browse.php">Story Boards</a></li>
				<li><a href="welcomeMessage.browse.php">Welcome Message</a></li>
				<li><a href="introsGame.browse.php">Intros</a></li>
			</ul>
		</li>
		<li><a href="#">Events</a>
			<ul>
				<li><a href="events.browse.php?pageId=1">Reminder</a></li>
				<li><a href="events.browse.php?pageId=3">Squeeze Game</a></li>
				<li><a href="events.browse.php?pageId=2">Bonus Question</a></li>
				<li><a href="events.browse.php?pageId=4">Informercial</a></li>
				<li><a href="oldPlayers.browse.php" style="width: 130px;">Old Players Campaign</a></li>
			</ul>
		</li>
		<li><a href="groups.browse.php">Groups</a>
			<? /*<ul>
				<li><a href="#" style="width: 80px;">New</a></li>
				<li><a href="groups.browse.php">All</a></li>
			</ul>*/ ?>
		</li>
		<li><a href="calendar.browse.php">Calendar</a></li>
		<li><a href="#">Reports</a>
			<ul>
                <li><a href="reports.currentStatus.php">Current Status</a></li>
                <li><a href="reports.currentStatusDetails.php">Current Status Details</a></li>
				<li><a href="reports.playerDetails.php">Player Details</a></li>
				<li><a href="reports.playerCheck.php">Player Activity Check</a></li>
				<li><a href="reports.gameReports.php">Game Reports</a></li>
				<li><a href="reports.oldPlayersResponse.php">Old Players Campaign Response</a></li>
				<li><a href="reports.playerRegByGroup.php">Players (Registred By Group)</a></li>
				<li><a href="reports.dayCompareBySlot.php">Day Compare by Slot</a></li>
				<li><a href="reports.topPlayers.php">Top Players</a></li>
				<li><a href="reports.topPlayersDetails.php">Top Players Details</a></li>
				<li><a href="reports.operatorStatus.php">Operator SMS Send Status 1</a></li>
				<li><a href="reports.operatorStatus2.php">Operator SMS Send Status 2</a></li>
				<li><a href="reports.operatorStatus3.php">Operator SMS Send Status 3</a></li>
				<li><a href="reports.operatorStatus4.php">Operator SMS Send Status 4</a></li>
			</ul>
		</li>
		<li><a href="#">Admin</a>
			<ul>
				<li><a href="playerType.browse.php">Player Type</a></li>
				<li><a href="filterPredefined.browse.php">Predefined Filter</a></li>
			</ul>
		</li>
		<li><a href="#" style="border: 0;">Monitoring</a>
			<ul>
				<li><a href="monitoring.stopStart.php" style="width: 80px; width: 150px;">Stop & Start</a></li>
				<li><a href="monitoring.systemStatus.php" style="width: 80px; width: 150px;">System Status</a></li>
				<li><a href="monitoring.systemStatusDetails.php" style="width: 80px; width: 150px;">System Status Details</a></li>
				<li><a href="monitoring.logFile.php" style="width: 80px; width: 150px;">Log File</a></li>
			</ul>
		</li>	
		</ul>
		<div class="clear"> </div>
	</td>
</tr>

<tr>

<td width="100%" colspan="2" valign="top">
	

	
	
