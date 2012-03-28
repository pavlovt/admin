<?

function getData($url) {
	$ch = @curl_init();
	@curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_POST,1);
 	//curl_setopt($ch, CURLOPT_POSTFIELDS,$postVars);
	@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	@curl_setopt( $ch, CURLOPT_AUTOREFERER, 1 );
	@curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
	@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
	//@curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	$rawResponse = @curl_exec($ch);

	if(curl_errno($ch)) {
		@curl_close($ch);
		return(false);
	}
	
	@curl_close($ch);
	return($rawResponse);

}

function j($str) {
	// ensures a safe string to be passed as javascript function param
	return str_replace("'", "\'", $str);

} // j()

function isValidEmail($email) {
	$testAgainst = '/^[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/';
	if (preg_match($testAgainst, $email)) { return TRUE; }
	return FALSE;

} // isValidEmail

function myStripSlashes($s) {
	if (get_magic_quotes_gpc()==1) {
		return stripslashes($s);
	}
	return stripslashes($s);
}

function htmlSafe($s) {
	return str_replace('>', '&#62;', str_replace('<', '&#60;', $s));
}

function formSafe($s) {
	return str_replace('"', '&quot;', $s);
}

function stripHtml($str) {
	return eregi_replace("<[^>]*>", "", $str);
} // stripHtml()

function makecsv($a) {

	$x = count($a);
	unset($r);
	for ($i = 0; $i < $x; $i++) {

		if (eregi('[," ]', $a[$i])) {
			// replace " with "" and enclose with "
			$r[$i] = '"'.str_replace('"', '""', $a[$i]).'"';

		} else {
			$r[$i] = $a[$i];
		}

	} // $for

	// implode $r
	$result = implode(",", $r);

	return($result);
}

function unmakecsv($s) {
	// makes a csv string back to an array

	$a = split(',', $s);
	$i = count($a) - 1;

	while ($i >= 0) {

		// last char is "?
		if (substr($a[$i], -1) == '"') {
			// check if the first char is " as well. if not, merge until becomes.
			if ((substr($a[$i], 0, 1) == '"') && (substr($a[$i], 0, 2) != '""')) {
				// first char "; remove the first and the last ", replace "" with "
				$a[$i] = substr($a[$i], 1);
				$a[$i] = substr($a[$i], 0, strlen($a[$i])-1);
				$a[$i] = str_replace('""', '"', $a[$i]);

			// 20080714; mihail@picink.com; fix; allow this function to handle empty csv values
			} else if ((substr($a[$i], 0, 1) == '"') && (substr($a[$i], 0, 2) == '""')) {
				$a[$i] = "";

			} else {
				// first char not "; append this token to the prev one with , inbetween;
				$a[$i - 1] .= ",".$a[$i];

				// delete this token
				array_splice($a, $i, 1);

			} // if first char is "

		} // if the last char is "

		$i--; // check prev token
	} // checking tokens

	return $a;
} // unmakecsv();


function formatDate($date) {
	$resultArray = array("year" => "", "month" => "", "day" => "");

	// . and / to become -
	$date = ereg_replace("[\./]", "-", $date);

	// remove everything but the numbers and -
	$date = ereg_replace("[^0-9\-]", "", $date);

	// split, check if we have 3 tokens
	$strDateArray = split("-", $date);

	// check 3 toke	ns and 1st or last is 4 chars
	if ((count($strDateArray) != 3) || ((strlen($strDateArray[0]) != 4) && (strlen($strDateArray[2]) != 4))) { return NULL; }

	// if the 3rd token is only 2 chars long, assume it is the year
	if (strlen($strDateArray[2]) == 2) {
		if ((int)$strDateArray[2] < 90) {
			$strDateArray[2] = "20".$strDateArray[2];
		} else {
			$strDateArray[2] = "19".$strDateArray[2];
		}
	}

	if (strlen($strDateArray[0]) == 4) {
		$resultArray["year"] = $strDateArray[0];
		$resultArray["month"] = strlen($strDateArray[1]) == 1 ? "0".$strDateArray[1] : $strDateArray[1];
		$resultArray["day"] = strlen($strDateArray[2]) == 1 ? "0".$strDateArray[2] : $strDateArray[2];

	} else {
		$resultArray["year"] = $strDateArray[2];
		$resultArray["month"] = strlen($strDateArray[0]) == 1 ? "0".$strDateArray[0] : $strDateArray[0];
		$resultArray["day"] = strlen($strDateArray[1]) == 1 ? "0".$strDateArray[1] : $strDateArray[1];

	}

	return $resultArray;
} // formatDate

function copyUploadedFile($sourceFile, $destinationDirectory, $destinationFileName, $folderSeparator) {

	// get uploaded file name and format it to lower case
	$destinationFileName = strtolower(@basename($destinationFileName));

	// format destination file name including full file path
	$destinationFilePath = $destinationDirectory.$folderSeparator.$destinationFileName;

	// make sure destination file path is unique
	// if not unique add integer at the begining of file name
	$i = 1;

	while (@file_exists($destinationFilePath)) {
		$destinationFilePath = $destinationDirectory.$folderSeparator.$i."-".$destinationFileName;
		$i++;
	}

	// once unique file name has been generated copy uploaded file to server
	if (@move_uploaded_file($sourceFile, $destinationFilePath)) {
		// return uploaded file path
		return $destinationFilePath;
	}

	// an error occurred during file upload procedure
	return FALSE;

} // copyUploadedFile


if (!@function_exists("sendEmail")) {
	// Send email
	// sendFrom and To are email lists - q@q.com, t@t.com ...
	// message is html text
	function sendMail ($subject, $message = '') {
		global $sendFrom, $smtpInfo, $sendTo;
		//var_dump($sendFrom, $smtpInfo, $subject, $message); exit;
		echo $subject."\n";
		exit;
		
		//mail($sendTo, $subject, $message);
		//return true;
	
		require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/mail/Mail.php");
		require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/mail/Mail/mail.php");
		require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/mail/Mail_Mime/mime.php");
		
		$header["MIME-Version"] = "1.0";
		$header['Content-Type'] = 'text/html; charset="utf-8"';
		$header["Content-Transfer-Encoding"] = "quoted-printable";
		$header["From"] = $sendFrom;
		$header["Subject"] = $subject;
		
		if (!empty($message)) {
			$mime = new Mail_mime();
			$mime->setHTMLBody($htmlMsg);
			$body = $mime->get(array("html_charset" => "utf-8"));
		} else {
			$body = '';
		}
		
		$smtp = Mail::factory('smtp', $smtpInfo);
		
		$mail = $smtp->send($sendTo, $header, $body);
		
		if (PEAR::isError($mail)) {
		   return false;
		}
		
		return true;
	}
}

if (!@function_exists("db")) {
	function db($query, $db_name = "db") {
		$db = dbWrapper::getDb($db_name);
		if ($db->run($query)->rowCount() === false) {
			sendMail($GLOBALS["lastError"]);
			return false;
		}

		return $db;
	}

}

if (!@function_exists("dbr")) {
	function dbr($query, $db_name = "dbr") {
		$db = dbWrapper::getDb($db_name);
		if ($db->run($query)->rowCount() === false) {
			sendMail($GLOBALS["lastError"]);
			return false;
		}

		return $db;
	}

}

if (!@function_exists("dbErrorHandler")) {
	// db error function - see common/db.php
	function dbErrorHandler($error='') {
		$GLOBALS["lastError"] = $error;
	}
}

if (!@function_exists("logg")) {
	function logg($param) {
		?><script>console.log('<?=json_encode($param)?>');</script><?
	}

}

if (!@function_exists("datediff")) {
	function datediff($date_from, $date_to) {
		$date_from = strtotime($date_from);
		$date_to = strtotime($date_to);
		if ($date_from>0 || $date_to>0) {
			$interval  = @round(abs($date_to-$date_from)/60/60/24);
			return $interval + 1;

		} else if ($date_from>0 || $date_to>0) {
			return 1;

		} else {
			return 0;

		}
	}

}

if (!@function_exists("date_list")) {
	// get all dates in specified date interval as a list
	// i.e. date_list("1.1.2012", "7.1.2012")
	function date_list($dateFrom, $dateTo) {
		
		$startDateTimeStamp = strtotime($dateFrom);
		$endDateTimeStamp = strtotime($dateTo);

		// convert date in timestamp and then format it to be sure that mysql will understand it
		$startDate = date("Y-m-d", strtotime($dateFrom));
		$endDate = date("Y-m-d", strtotime($dateTo));

		if (!$startDateTimeStamp) $startDate = START_DATE;
		if (!$endDateTimeStamp) $endDate = END_DATE;
		$startDateTimeStamp = strtotime($startDate);
		$endDateTimeStamp = strtotime($endDate);

		// difference between dates in days
		$daysDiff = datediff($startDate, $endDate);

		if ($daysDiff == 0) return "'".$startDate."'";

		$dates = array();
		for ($i=0; $i < $daysDiff ; $i++) { 
			$dates[] = date("Y-m-d", strtotime("+{$i} days", $startDateTimeStamp));
		}

		return "'".implode("','", $dates)."'";
	}

}

function html_table_header($params) {
	$result = "";
	$params = (array)$params;

	$result = __::map($params, function($v) { 
		return "<th>{$v}</th>";
	});

	$result = __::reduce($result, function($memo, $v) { return $memo . $v; }, '');

	return $result;
}

/**
 * Generates table body or footer structure
 * $params = array(0 => array("name" => "qqq", "price" => 3.20), 1 => ...); 
 * $def = array("name" => array("type" => "string", "class" => array("center"), "price" => array("type" => "num", "class" => "right"))
 * html_element may be td or th (th is used for the table footer)
 */
function html_table_content($params, array $def, $html_element = 'td') {

	$result = "";
	$params = (array)$params;
	foreach ($params as $i => $vs) {
		$vs = (array)$vs;
		// $vs = array("name" => "qqq", "price" => 3.20);
		$res = "";
		foreach ($def as $k => $v) {
			if (empty($vs[$k]) && $v["type"] != 'link') {
				$res .= "<{$html_element}>&nbsp;</{$html_element}>";
				continue;
			}

			switch ($v["type"]) {
				case 'int':
					$vs[$k] = (int)$vs[$k];
					$add_class = ($html_element == 'th' ? 'right_pad' : '');
					$res .= "<{$html_element} class='{$v["class"]}  {$add_class}'>".@number_format($vs[$k], 0, '.', ' ')."</{$html_element}>";
					break;

				case 'float':
					$vs[$k] = (float)$vs[$k];
					$add_class = ($html_element == 'th' ? 'right_pad' : '');
					$res .= "<{$html_element} class='{$v["class"]} {$add_class}'>".@number_format($vs[$k], 1, '.', ' ')."</{$html_element}>";
					break;

				case 'link':
					$res .= "<{$html_element} class='center width20'><a class='button size9 {$v["class"]}' href='{$v['link']}{$vs[$v['id']]}'>{$k}</a></{$html_element}>";
					break;

				default:
					$res .= "<{$html_element} class='{$v["class"]}'>{$vs[$k]}</{$html_element}>";
					break;
			}
		};

		$class = ($i>0 ? "tr-noborder" : "tr-top-border");
		$result .= "<tr class='{$class}'>{$res}</tr>";

	};

	return $result;
}

/**
 * Get CoffeeScript!
 */
function get_coffee($scriptPath) {
	$coffee = file_get_contents($scriptPath);

	if (empty($coffee)) return false;

	try {
	  $js = CoffeeScript\compile($coffee);

	  echo "<script>{$js}</script>";

	} catch (Exception $e) {
		exit('An error occured while loading '.$scriptPath);
	}
}

/**
 * Set session message
 */
function notify($message, $is_error = false) {
	# save message in session
	if (!empty($message)) {
		if ($is_error) 
			$_SESSION["userSession"]["pageErrors"][] = $message;
		else
			$_SESSION["userSession"]["pageMessages"][] = $message;
	}

}

/**
 * Redirect and show message
 */
function redirect($path, $message, $is_error = false) {
	# save message in session
	notify($message, $is_error);

	# redirect
	if (!empty($path)) {
		# save the session before redirect
		session_write_close();
		header("Location: {$path}");
	}

}

/**
 * Return the date if valid, else return the current date
 */
function dates($date){
	if (strtotime($date)) {
		return date("d.m.Y", strtotime($date));
	} else {
		return date("d.m.Y");
	}
}

/**
 * Protect from cross site scripting and other atacks
 */
function clear_xss($text){
	require_once basePath.'class/class.inputfilter.php';
  $inputFilter = new InputFilter();
  $text = $inputFilter->process($text);
  return $text;
}


/**
 * Generate pdf $file from the given $url
 */
function get_pdf($url, $file){
	#if (!file_exists(pdfPath.$url))
	#	die('Избраният файл не съществува');
	//exit("wkhtml ".pdfUrl."{$url} ".pdfFilePath.$file);
	exec("wkhtml ".pdfUrl."{$url} ".pdfFilePath.$file."  2>&1");

	# We'll be outputting a PDF
	header('Content-type: application/pdf');
	header('Content-Disposition: attachment; filename="'.$file.'"');
	readfile(pdfFilePath.$file);
}
