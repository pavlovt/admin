<?

if ((isset($queryData)) && (isset($queryData["transactionId"])) && (strlen(trim($queryData["transactionId"])))) {
	
	if (isset($_SESSION["lastTenTransactions"])) {
		// check if this transaction is in the list. if it is, print error
		if (in_array($queryData["transactionId"], $_SESSION["lastTenTransactions"])) {
			// already completed
			?><html><head><title>Transaction already executed</title></head><body><p style="font-family: Verdana, Arial, Helvetica, Sans-serif; color: red; font-size: 12px; font-weight: bold;">ERROR: You can not reload this page. <a href="javascript:history.back();">Please go back</a></body></html><?
			exit;
			
		} else {
			// append this transaction. first make sure only 10 items are in the array
			array_push($_SESSION["lastTenTransactions"], $queryData["transactionId"]);
			
		}
		
	} else {
		// no previous transactions, so add this one
		$_SESSION["lastTenTransactions"] = array($queryData["transactionId"]);
	}
	
	$_SESSION["lastTenTransactions"] = array_slice($_SESSION["lastTenTransactions"], -10);
	
} 

?>
