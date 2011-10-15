<?
// 20101102 -- george -- created

set_time_limit(0);

// IMPORTANT: set these according to this page. session messages that need to be shown on this page are recorded 
// according to these values!
define("SECTION", "playerType");
define("PAGE", "browse");
$pageId = "playerTypeBrowse";
$error = NULL;
$errorArray = array();

// includes
require_once("include/config.frontEnd.php");
require_once("common/sessionInit.php");

require_once("class/storeMonger/class.smSalesPerson.php");
require_once("class/gameMonger/class.playerType.php");

require_once("checkLogin.php");

require_once("privDetermine.php");
if (!$privs["privAdmin"]["read"]) { ?><script>alert('You do not have access to this screen or operation.'); history.back();</script><? exit; }
$canModify = $privs["privAdmin"]["modify"];

// include this whenever want to prevent transactions
require_once("common/preventDoubleTransactions.php");

if ((isset($_GET["ajaxaction"])) && ($_GET["ajaxaction"] == "loadDetails")) {

	$id = isset($_GET["id"]) ? $_GET["id"] : NULL;
	
	$playerType_i = new playerType($db, $salesPersonId, $salesPersonUserName);
	if (!$playerType_i->loadById($id)) {
		header("Content-type: text/plain");
		echo json_encode(array("error" => TRUE, "errorMessage" => $playerType_i->lastError));
		exit;
	}
	
	$result = array(
		"id" => $playerType_i->p["id"],
		"name" => $playerType_i->p["name"],
		"description" => $playerType_i->p["description"]
	);
	
	header("Content-type: text/plain");
	echo json_encode(array("error" => FALSE, "errorMessage" => "", "result" => $result));
	exit;

} elseif ((isset($_GET["ajaxaction"])) && ($_GET["ajaxaction"] == "saveDetails")) {

    if (!$canModify) {
        header("Content-type: text/plain");
		echo json_encode(array("error" => TRUE, "errorMessage" => "Operation not allowed"));
		exit;
    }
    
	$id = isset($_GET["id"]) ? $_GET["id"] : "";
	$name = isset($_GET["name"]) ? $_GET["name"] : "";
	$description = isset($_GET["description"]) ? $_GET["description"] : "";
	
	$playerType_i = new playerType($db, $salesPersonId, $salesPersonUserName);
	
	
	if ((int)$id) {
		// try to load this Id
		if (!$playerType_i->loadById($id)) {
			header("Content-type: text/plain");
			echo json_encode(array("error" => TRUE, "errorMessage" => "Unable to focus on existing record. ".$playerType_i->lastError));
			exit;
		}
		
		// try to update
		if (!$playerType_i->updateDetails(TRUE, $name, $description)) {
			header("Content-type: text/plain");
			echo json_encode(array("error" => TRUE, "errorMessage" => "Unable to update existing record. ".$playerType_i->lastError));
			exit;
		}
		
		$newId = $id;
	
	} else {
		// try to create new
		if (!$playerType_i->createNew(TRUE, $name, $description)) {
			header("Content-type: text/plain");
			echo json_encode(array("error" => TRUE, "errorMessage" => "Unable to create new record. ".$playerType_i->lastError));
			exit;
		}
		
		$newId = $playerType_i->p["id"];
	
	}
	
	header("Content-type: text/plain");
	echo json_encode(array("error" => FALSE, "errorMessage" => "", "newId" => $newId));
	exit;
	
} elseif ((isset($_GET["ajaxaction"])) && ($_GET["ajaxaction"] == "delete")) {
		
	$id = isset($_GET["id"]) ? $_GET["id"] : "";

	$playerType_i = new playerType($db, $salesPersonId, $salesPersonUserName);
	if (!$playerType_i->loadById($id)) {
		header("Content-type: text/plain");
		echo json_encode(array("error" => TRUE, "errorMessage" => "Unable to open the selected record for deletion.".$playerType_i->lastError));
		exit;
	}
	
	// try to delete
	if (!$playerType_i->delete(TRUE)) {
		header("Content-type: text/plain");
		echo json_encode(array("error" => TRUE, "errorMessage" => "Unable to delete existing record. ".$playerType_i->lastError));
		exit;
	}
	
	// all good
	$_SESSION["userSession"]["pageMessages"][SECTION."-".PAGE] = array("Record deleted successfully");
	
	header("Content-type: text/plain");
	echo json_encode(array("error" => FALSE, "errorMessage" => ""));
	exit;

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Predefined Filters</title>
	
	<?@require_once("commonHtmlHead.php");?>
	
	<script type="text/javascript"> 
		
		$(document).ready(function() {
			$('#dataTable').dataTable( {
				"bStateSave": true,
				"sPaginationType": "full_numbers",
				"iDisplayLength": 50,
				"bLengthChange": false,
				"sDom": 'T<"clear"><"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
				"oTableTools": {
						"sSwfPath": "../js/jquery/media/swf/copy_cvs_xls_pdf.swf",
						"aButtons": [ "csv", "pdf", "print" ],
						"sRowSelect": "single"
				},
				"aoColumns": [
					null,
					null,
					null,
					{ "bSortable": false }
				],
				"aaSorting": [[ 0, "desc" ]]
			} );
		} );
		
	</script> 
	
</head>

<body leftmargin="0" topmargin="0" rightmargin="0" bgcolor="#ffffff" id="bdy">

<?
require_once("container.top.php");
require_once("commonConfirmationMessagesHandler.php"); // displays any errors or confirmation messages as set in the session for this page
?>
<p class="heading">Predefined Filter</p>
<p style="margin-top: 15px; float: right;">
<span class="action_button" id="doSaveButton" onClick="javascript:editRecord(0);">Create New</span>
</p>

<table id="dataTable" cellpadding="0" cellspacing="0" border="0" class="display">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Description</th>
<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<?/*
$playerType_i = new playerType($db, $salesPersonId, $salesPersonUserName);
$playerType_i->loadList();
while ($playerType_i->next()) {
	?>
	<tr>
	<td><?=$playerType_i->p["id"]?></td>
	<td><?=$playerType_i->p["name"]?></td>
	<td><?=$playerType_i->p["description"]?></td>
	<td style="padding: 5px;">
	<?
	if ($canModify) {
		?><span class="action_button" onClick="javascript:editRecord(<?=$playerType_i->p["id"]?>);">Edit</span><?
	} else {
		?>&nbsp;<?
	}
	?>	
	</td>
		
	</tr>
	<?
}*/
?>
</tbody>
</table>


<?
require_once("container.bottom.php");
?>

<!-- POPUP DLG START -->
<div id="modalEditBox" style="display: none;">
<input type="text" id="scrollToTopOnFocus" style="display: none;">
<input type="hidden" id="playerTypeId" value="">

<div id="popDlgProgress" style="display: none;"><img src="images/loadingAnimation.gif"></div>
<div id="popDlgConfirmMessage" style="display: none; margin-bottom: 20px; font-size: 14px; font-weight: bold; background-color: #007909; padding: 10px; color: #ffffff; border: 1px solid #000000;"></div>
<div id="popDlgErrorMessage" style="display: none; margin-bottom: 20px; font-size: 14px; font-weight: bold; background-color: #c00000; padding: 10px; color: #ffffff; border: 1px solid #000000;"></div>

<div id="popDlgDetails" style="display: none;">

<strong>Name:</strong><br>
<input type="text" style="width: 200px;" id="name" maxlength="20">

<br><br>

<strong>Description:</strong><br>
<input type="text" style="width: 200px;" id="description" maxlength="200">

<br><br>

<strong>Create query:</strong><br>
<textarea id="createQuery" cols="60" rows="4"></textarea>


<br><br>
<span class="action_button" id="doSaveButton" onClick="javascript:doSave();">Save</span>

<br><br><br>
<span class="action_button" id="doSaveButton" onClick="javascript:closePopup();">Close</span>

</div>
<!-- POPUP DLG END -->


<script>
/*
START == POPUP VARIABLES
*/
var todayDate = '<?=date("d/m/Y")?>';
/*
END == POPUP VARIABLES
*/

function clearCurrentPopDlg() {
	
	$('#playerTypeId').val('');
	$('#playerTypeName').val('');
	$('#playerTypeDescription').val('');
	$('#doDeleteButton').hide();
	
} // clearCurrentPopDlg


function showDetails() {

	// attach the datepicker to the inputs here, otherwise on Firefox the first click on a textbox shows the datepicker somewhere outside...
	/*
	$(function() {
		$("#contractPayByDate").datepicker({
			altFormat: 'dd/mm/yy',
			dateFormat: 'dd/mm/yy',
			firstDay: 1
		});
	});
	*/
	
	$("#popDlgProgress").hide();
	$("#popDlgDetails").show();
	if (wndConfirmMessage) { showConfirmMessage(wndConfirmMessage); wndConfirmMessage = null; }
	if (wndErrorMessage) { showFatalError(wndErrorMessage); wndErrorMessage = null; }
}

function editRecord(id) {

	// scroll to top of main windows for IE... try to focus to something that is on the top!
	if (navigator.appName == 'Microsoft Internet Explorer') { $('#itemOnTopHolder').show(); $('#itemOnTopHolder').focus(); $('#itemOnTopHolder').hide(); }
	
	wndVisible = true;
	//valumUploaderMoveDivOnInternetExplorerScroll("attachmentUploader", 450);
	startEditRecord(id);
	tb_show("", "#TB_inline?height=250&width=500&inlineId=modalEditBox&modal=true", "");

} // editRecord

function doDelete() {

	if (!confirm('Do you really want to delete this Player Type?')) { return true; }

	scrollDlgToTop();
	showProgress();
	
	$.getJSON("?ajaxaction=delete" + 
		"&id=" + $('#playerTypeId').val() + 
		"",
		
		function(data) {
			if (typeof(data) != 'object') { 
				wndErrorMessage = "Bad request. Please try again";
				showDetails();
				return false; 
			}
			
			var error = false;
			var errorMessage = null;
			
			// fill-in popup values
			if (typeof(data.error) != undefined) { error = data.error;  }
			if (typeof(data.errorMessage) != undefined) { errorMessage = data.errorMessage; }
			
			if (error) {
				wndErrorMessage = errorMessage;
				showDetails();
				return false;
			}
			
			reloadOnClosePopup = true;
			closePopup();
			
		}
	);

} // doDelete

function doSave() {

	scrollDlgToTop();
	showProgress();
	
	$.getJSON("?ajaxaction=saveDetails" + 
		"&id=" + $('#playerTypeId').val() + 
		"&name=" + $('#playerTypeName').val() +
		"&description=" + $('#playerTypeDescription').val() +
		"",
		
		function(data) {
			var newId = 0;
			
			if (typeof(data) != 'object') { 
				wndErrorMessage = "Bad request. Please try again";
				showDetails();
				return false; 
			}
			
			var error = false;
			var errorMessage = null;
			
			// display error or reload
			if (typeof(data.error) != undefined) { error = data.error;  }
			if (typeof(data.errorMessage) != undefined) { errorMessage = data.errorMessage; }
			if (typeof(data.newId) == undefined) { errorMessage = "Malformed response 201010101901";  }
			
			if (!error) {
				newId = parseInt(data.newId);
				if (!newId) { error = true; errorMessage = "Unable to find new or updated record ID"; }
			}
				
			if (error) {
				wndErrorMessage = errorMessage;
				showDetails();
				return false;
			}
			
			wndConfirmMessage = "Record saved";
			reloadOnClosePopup = true;
			startEditRecord(newId);
			
		}
	);

} // doSave

function startEditRecord(id) {
		
	showProgress();
	clearCurrentPopDlg();
	
	$("#playerTypeId").val(id);
	$("#doDeleteButton").hide();
	$("#doSaveButton").show();
	
	if (id > 0) {
		// json load data and then show details when finished
		
		$.getJSON("?ajaxaction=loadDetails&id=" + id,
			function(data) {
				
				if (typeof(data) != 'object') { 
					wndErrorMessage = "Bad request. Please try again";
					showDetails();
					return false; 
				}
				
				
				var error = false;
				var errorMessage = null;
				
				// check for errors
				if (typeof(data.error) != undefined) { error = data.error;  }
				if (typeof(data.errorMessage) != undefined) { errorMessage = data.errorMessage; }
				if ((!error) && (typeof(data.result) != 'object')) { errorMessage = 'Bad response'; }
				
				if (error) {
					wndErrorMessage = errorMessage;
					showDetails();
					return false;
				}
				
				// fill-in popup values
				$.each(data.result, function(i, val) {
					if (i == "name") { $('#playerTypeName').val(val); }
					else if (i == "description") { $('#playerTypeDescription').val(val); }
				});
				
				$("#doDeleteButton").show();
				
				if (error) {
					wndErrorMessage = errorMessage;
					showDetails();
					return false;
				}
				
				showDetails();
				
			}
		);
	
	} else {
		showDetails();
		
	}

} // startEditRecord

/*
START === COMMON POPUP WINDOW FUNCTIONS DO NOT EDIT
*/
var wndConfirmMessage = null;
var wndVisible = false;
var wndLastBrowseButtonPosition = 0;
var wndErrorMessage = null;
var reloadOnClosePopup = false;

function scrollDlgToTop() {
	$("#scrollToTopOnFocus").show();
	$("#scrollToTopOnFocus").focus();
	$("#scrollToTopOnFocus").hide();
}

function showProgress() {
	$("#popDlgProgress").show();
	$("#popDlgErrorMessage").hide();
	$("#popDlgDetails").hide();
}

function showConfirmMessage(message) {
	scrollDlgToTop();
	$("#popDlgProgress").hide();
	$("#popDlgErrorMessage").hide();
	$('#popDlgConfirmMessage').html(message);
	$('#popDlgConfirmMessage').fadeIn(200);
	setTimeout('hideConfirmMessage()', 2000);
}

function hideConfirmMessage() {
	$('#popDlgConfirmMessage').fadeOut(600);
}

function showFatalError(message) {
	scrollDlgToTop();
	$('#popDlgErrorMessage').html(message); 
	$("#popDlgProgress").hide();
	$("#popDlgErrorMessage").show();
}

function closePopup() {
	
	if (reloadOnClosePopup) { 
		// show progress bar, reload may take a sec
		scrollDlgToTop();
		showProgress();

		//window.location.reload();
		window.location.href=window.location.href; // use this instead or reload(); reload() will cause some browsers such as Chrome to reload cached content
	} else {
		wndVisible = false;
		tb_remove();
	}
} // closePopup

function valumUploaderMoveDivOnInternetExplorerScroll(valumUploaderDivId, popupHeight) {
	// this is a hack which will update the position of the uploader div, when internet explrer scrolls in the popup... in IE this div will be positioned WAY
	// OFF on ie by default and not move with the scroll unless mouseovered
	
	// if the wndVisible is false (i.e. popup not open, do nothing)
	if (!wndVisible) { return true; }
	
	// if not IE, just return true
	// check done according to instructions here: http://msdn.microsoft.com/en-us/library/ms537509%28VS.85%29.aspx (supposedly the best way)
	if (navigator.appName != 'Microsoft Internet Explorer') { return true; }
	
	var position = $("#" + valumUploaderDivId).position();
	// new position?
	if (position.top != wndLastBrowseButtonPosition) {
		$("#upload-button").hide();
		// only show the upload button if it is in the popup
		if (position.top <= popupHeight) {
			$("#upload-button").show(); 
		}
		wndLastBrowseButtonPosition = position.top;
	}
	setTimeout('valumUploaderMoveDivOnInternetExplorerScroll("' + valumUploaderDivId + '", "' + popupHeight + '")', 100);
}

/*
END === COMMON POPUP WINDOW FUNCTIONS DO NOT EDIT
*/

</script>

</body>
</html>