<?php

if (!@function_exists('isSlaveUsable')) {
    function isSlaveUsable($db) {
        $w = @mysql_fetch_assoc(@mysql_query("SHOW SLAVE STATUS", $db));
        if (isset($w["Seconds_Behind_Master"])) {
            if (
               (strlen($w["Seconds_Behind_Master"]) == strlen((int)$w["Seconds_Behind_Master"])) &&
               ((int)$w["Seconds_Behind_Master"] < 30)
            ) {
                return TRUE;
            }

        }

        return FALSE;
    }

} // isSlaveUsable

if (!@function_exists('sys_get_temp_dir')) {
	function sys_get_temp_dir() {
		if ($temp = getenv('TMP')) return $temp;
		if ($temp = getenv('TEMP')) return $temp;
		if ($temp = getenv('TMPDIR')) return $temp;
		$temp = tempnam(__FILE__, '');
		if (@file_exists($temp)) {
			unlink($temp);
			return dirname($temp);
		}
		return null;
	}
} // sys_get_temp_dir
 
if (!@function_exists('unlinkArray')) {
	function unlinkArray($fileArray) {
		foreach ($fileArray as $fileName) {
			@unlink($fileName);
		}
	}
}

if (!@function_exists('arrayToXML')) {
	function arrayToXML($data, $encoding = "UTF-8") {
		
		$res = "";	
		foreach ($data as $tag => $value) {
			
			if ((!is_array($value)) && (!strlen($value))) {
				// 20090923, george: by req from bankservice, just skip empty tags, instead of leaving them here
				//$res .= "<".$tag." />";
			} else {
				$res .= "<".$tag.">";
				
				if (is_array($value)) {
					$res .= arrayToXML($value, $encoding);
					
				} else {
					$res .= htmlspecialchars($value, ENT_COMPAT, $encoding);
				}
				
				$res .= "</".$tag.">";
			}
		}
		
		return $res;
		
	} // arrayToXML
}

if (!function_exists("stringContainSpecialChars")) {
	function stringContainSpecialChars($s) {
		for ($c = 0; $c < strlen($s); $c++) {
	        $i = ord($s[$c]);
	        if ($i > 127) {
	            return TRUE;
	        }
        }
        return FALSE;
	}
	
}

if (!function_exists("stringContainLatinChars")) {
	function stringContainLatinChars($s) {
		if (strlen($s) != strlen(ereg_replace("[A-Za-z]", "", $s))) {
			return TRUE;
		}
		return FALSE;
	}
	
}

function cleanUpPhoneNumber($phone, $ext, $isUSPhone = TRUE) {

	$extParseFound = FALSE;

	$phone = strtoupper($phone);
	$ext = strtoupper($ext);

	if (!(strpos($phone, "X") === FALSE)) {
		$phone = str_replace("X", "~", $phone);
		$extParseFound = TRUE;
	}

	if ($extParseFound) {
		$pa = split("~", $phone);
		$phone = $pa[0];
		$ext = $pa[1];
	}

	// format phone; remove all but numbers;
	$phone = ereg_replace("[^0-9]", "", $phone);

	// for extention, leave numbers only
	$ext = ereg_replace("[^0-9]", "", $ext);

	// if not usa phone number, return whatever
	if (!$isUSPhone) {
		return array("phone" => $phone, "ext" => $ext);
	}

	// us phone number, if leading number 1, remove it
	$phone = ereg_replace("^1?", "", $phone);

	return array("phone" => $phone, "ext" => $ext);

} // cleanUpPhoneNumber

function cleanUpFaxNumber($fax, $faxExt, $isUSFax = TRUE) {
	
	$res = cleanUpPhoneNumber($fax, $faxExt, $isUSFax);
	
	return array("fax" => $res["phone"], "faxExt" => $res["ext"]);
	
} // cleanUpFaxNumber

function cleanUpZipCode($zip, $isUSZIP = TRUE) {

	// trim
	$zip = strtoupper(trim($zip));

	if (!$isUSZIP) {
		// if this is not us zip code, just return whatever is there
		array("zip" => $zip, "zipext" => "");
	}

	// if there is a dash or space in the zip code, split into two, after the last occurance
	$zip = str_replace(" ", "-", $zip);
	if (($zipext = strrchr($zip, "-")) === FALSE) {
		$zipext = "";
	} else {
		$zip = substr($zip, 0, strrpos($zip, "-"));
	}

	// for each, leave chars and numbers only
	$zip = ereg_replace("[^A-Z0-9]", "", $zip);
	$zipext = ereg_replace("[^A-Z0-9]", "", $zipext);

	// return
	return array("zip" => $zip, "zipext" => $zipext);

} // cleanUpZipCode

function rds($s) {
	// ..or remove double spaces
	return preg_replace("/[ ]+/", " ", $s);

} // rds

function getPageVariable($pageId, $variableName, $pool, $defaultValue, $ignoreSessionData = FALSE) {

	if (!session_id()) { session_start(); }

	if (isset($pool[$variableName])) {
		$newValue = $pool[$variableName];

	} elseif ((isset($_SESSION[$pageId."_".$variableName])) && (!$ignoreSessionData)) {
		$newValue = $_SESSION[$pageId."_".$variableName];

	} else {
		$newValue = $defaultValue;

	}

	if (!$ignoreSessionData) {
		$_SESSION[$pageId."_".$variableName] = $newValue;

	}

	return $newValue;

} // getPageVariable

function setPageVariable($pageId, $variableName, $newValue) {

	if (!session_id()) { session_start(); }
	$_SESSION[$pageId."_".$variableName] = $newValue;
	$GLOBALS[$variableName] = $newValue;

	return $newValue;

} // setPageVariable

function veSetCookie($name, $value, $time = NULL, $path = '/', $domain = NULL) {

	global $$name;

	// set as cookie
	setcookie($name, $value, $time, $path, $domain);

	// register as session variable as well.
	if (@session_is_registered($name)) {
		@session_unregister($name);
	}
	@session_register($name);
	$$name = $value;

} // veSetCookie()

function veGetCookie($name) {
	// june 21 2003, gosh@awebhome.com

	global $$name, $HTTP_COOKIE_VARS;

	// does this cookie exist in the cookie variables?
	if (isset($HTTP_COOKIE_VARS[$name])) {
		// yep, return this value
		return($HTTP_COOKIE_VARS[$name]);

	} else {
		// no. return whatever value is in the varibale with this name
		return($$name);

	}

} // veGetCookie()

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

/************************************************************************
*
* CCVal - Credit Card Validation function.
*
* Copyright (c) 1999 Holotech Enterprises. All rights reserved.
* You may freely modify and use this function for your own purposes. You
* may freely distribute it, without modification and with this notice
* and entire header intact.
*
* This function accepts a credit card number and, optionally, a code for
* a credit card name. If a Name code is specified, the number is checked
* against card-specific criteria, then validated with the Luhn Mod 10
* formula. Otherwise it is only checked against the formula. Valid name
* codes are:
*
*    mcd - Master Card
*    vis - Visa
*    amx - American Express
*    dsc - Discover
*    dnc - Diners Club
*    jcb - JCB
*
* A description of the criteria used in this function can be found at
* http://www.beachnet.com/~hstiles/cardtype.html. If you have any
* questions or comments, please direct them to ccval@holotech.net
*
*                                          Alan Little
*                                          Holotech Enterprises
*                                          http://www.holotech.net/
*                                          September 1999
*
************************************************************************/

function CCVal($Num, $Name = 'n/a') {

	//  Innocent until proven guilty
	$GoodCard = true;

	//  Get rid of any non-digits
	$Num = ereg_replace("[^[:digit:]]", "", $Num);

	if (!strlen($Num)) { return false; }

	//  Perform card-specific checks, if applicable
	switch ($Name) {

		case "mcd" :
			$GoodCard = ereg("^5[1-5].{14}$", $Num);
			break;

		case "vis" :
			$GoodCard = ereg("^4.{15}$|^4.{12}$", $Num);
			break;

		case "amx" :
			$GoodCard = ereg("^3[47].{13}$", $Num);
			break;

		case "dsc" :
			$GoodCard = ereg("^6011.{12}$", $Num);
			break;

		case "dnc" :
			$GoodCard = ereg("^30[0-5].{11}$|^3[68].{12}$", $Num);
			break;

		case "jcb" :
			$GoodCard = ereg("^3.{15}$|^2131|1800.{11}$", $Num);
			break;
	}

	//  The Luhn formula works right to left, so reverse the number.
	$Num = strrev($Num);

	$Total = 0;

	for ($x = 0; $x < strlen($Num); $x++) {
		$digit = substr($Num, $x, 1);

		//    If it's an odd digit, double it
		if ($x / 2 != floor($x / 2)) {
			$digit *= 2;

			//    If the result is two digits, add them
			if (strlen($digit) == 2)
				$digit = substr($digit, 0, 1) + substr($digit, 1, 1);
		}

		//    Add the current digit, doubled and added if applicable, to the Total
		$Total += $digit;
	}

	//  If it passed (or bypassed) the card-specific check and the Total is
	//  evenly divisible by 10, it's cool!
	if ($GoodCard && $Total % 10 == 0) return true; else return false;

}

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

if (!function_exists("PostIt")) {

	/************************************************************************ 
	* 
	* PostIt - Pretend to be a form. 
	* 
	* Copyright (c) 1999 Holotech Enterprises. All rights reserved. 
	* You may freely modify and use this function for your own purposes. You 
	* may freely distribute it, without modification and with this notice 
	* and entire header intact. 
	* 
	* This function takes an associative array and a URL. The array is URL- 
	* encoded and then POSTed to the URL. If the request succeeds, the 
	* response, if any, is returned in a scalar array. Outputting this is the 
	* caller's responsibility; bear in mind that it will include the HTTP 
	* headers. If the request fails, an associative array is returned with the 
	* elements 'errno' and 'errstr' corresponding to the error number and 
	* error message. If you have any questions or comments, please direct 
	* them to postit@holotech.net 
	* 
	*                                          Alan Little 
	*                                          Holotech Enterprises 
	*                                          http://www.holotech.net/ 
	*                                          December 1999 
	* 
	************************************************************************/ 
	// 20040621; gosh@awebhome.com; added support for HTTP_USER and HTTP_PASS
	// 20070519, gosh@awebhome.com, added support for $RawDataStream, which if true, will just pass the value of $DataStream, and not convert it into variable=value pairs
	// 20070607; mihail@awebhome.com; added timeout support
	// 20080924; mihail@picink.com; added suport to send requests over https
	function PostIt($DataStream, $URL, $HTTP_USER = NULL, $HTTP_PASS = NULL, $HTTP_VERSION = "1.1", $RawDataStream = FALSE, $timeout = 0) {
		// determine port where request must be sent
		if (stristr($URL, "http://") !== FALSE) {
			// https communication
			$communicationPort = 80;
		} else if (stristr($URL, "https://") !== FALSE) {
			// https communication
			$communicationPort = 443;
		} else {
			// by default use port 80
			$communicationPort = 80;
		}

		//  Strip http:// from the URL if present 
		$URL = ereg_replace("^http://", "", $URL); 
		$URL = ereg_replace("^https://", "", $URL); 

		//  Separate into Host and URI 
		$Host = substr($URL, 0, strpos($URL, "/")); 
		$URI = strstr($URL, "/"); 
		
		// format SSL host (allow to send requests over https)
		if ($communicationPort == 443) {
			$sslHost = "ssl://".$Host;
		} else {
			$sslHost = $Host;
		}

		//  Form up the request body 
		
		// 20070519, gosh, convert to var=value ?
		if ($RawDataStream === TRUE) {
			// nope, keep as is, jsut make sure $DataStream is not an array
			if (is_array($DataStream)) { $DataStream = ""; }
			$ReqBody = $DataStream;
		} else {
			$ReqBody = ""; 
			while (list($key, $val) = each($DataStream)) { 
				if ($ReqBody) $ReqBody.= "&"; 
				$ReqBody.= $key."=".urlencode($val); 
			}
		}
		
		$ContentLength = strlen($ReqBody); 

		//  Add Authorization?
		if (($HTTP_USER) && ($HTTP_PASS)) {
			$reqHeaderAuthStr = "Authorization: Basic ".base64_encode($HTTP_USER.":".$HTTP_PASS)."\n";

		} else {
			$reqHeaderAuthStr = "";

		}

		//  Generate the request header 
		$ReqHeader = 
			"POST $URI HTTP/".$HTTP_VERSION."\n". 
			"Host: $Host\n". 
			"User-Agent: PostIt\n". 
			"Content-Type: application/x-www-form-urlencoded\n".
			$reqHeaderAuthStr.
			"Content-Length: $ContentLength\n\n". 
			"$ReqBody\n\n"; 
			
		//  Open the connection to the host 
		// if timeout param is available use it when try to open socket to remote host
		if ((int)$timeout) {
			$socket = @fsockopen($sslHost, $communicationPort, $errno, $errstr, $timeout);
		} else {
			$socket = @fsockopen($sslHost, $communicationPort, $errno, $errstr);
		}
		
		if (!$socket) { 
			$Result["errno"] = $errno; 
			$Result["errstr"] = $errstr;
			return $Result; 
		} 
		$idx = 0; 
		
		@fputs($socket, $ReqHeader); 
			
		// 20070607; mihail@awebhome.com; set stream timeout if needed
		$Result['timed_out'] = FALSE;
		if ((int)$timeout) { stream_set_timeout($socket, $timeout);  }
		
		while (!@feof($socket)) { 
			$Result[$idx++] = @fgets($socket, 128);
			
			// check if connection timed out
			if ((int)$timeout) {
				$streamInfo = @stream_get_meta_data($socket);
				if ($streamInfo['timed_out']) { $Result['timed_out'] = TRUE; break; }
			}
		}
		
		return $Result; 

	} // PostIt

} 

// 20061205 -- mihail@awebhome.com -- functions for compress/uncompress files 
function zipFile($zipCmd, $sendOutput, $tempDirectory, $fileName, $tempFileName) {
	// format file names
	$tempNonZipFileName = $tempDirectory.$fileName;
	$tempZipFileName = $tempDirectory.$fileName.".zip";
	$downloadFileName = @basename($tempZipFileName);
	
	// compress file
	@copy($tempFileName, $tempNonZipFileName); 	
	@exec($zipCmd." ".$tempZipFileName." ".$tempNonZipFileName); 

	// send output to browser if needed
	if ($sendOutput) {
		// send compressed file for download
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$downloadFileName\"");
	
		$fp = @fopen($tempZipFileName, "r");
		@fpassthru($fp);
		@fclose($fp);
		
		// remove temp files
		@unlink($tempZipFileName);
		@unlink($tempNonZipFileName);
		
		exit();
		
	} else {
		// remove temp file
		@unlink($tempNonZipFileName);
		
		// return compressed file name
		return $tempZipFileName;
	}
	
} // zipFile

function unzipFile($unzipCmd, $sendOutput, $tempDirectory, $fileName, $tempFileName) {
	// check if zip archive
	if (strtolower(substr($fileName, -4)) != ".zip") { return FALSE; }
	
	// format file names
	$tempNonZipFileName = $tempDirectory.substr($fileName, 0, -4);
	$tempZipFileName = $tempDirectory.$fileName;
	$downloadFileName = @basename($tempNonZipFileName);
	
	// uncompress file
	@copy($tempFileName, $tempZipFileName);
	@exec($unzipCmd." ".$tempZipFileName);
	
	// send output to browser if needed
	if ($sendOutput) {
		// send uncompressed file for download
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$downloadFileName\"");
	
		$fp = @fopen($tempNonZipFileName, "r");
		@fpassthru($fp);
		@fclose($fp);
		
		// remove temp files
		@unlink($tempZipFileName);
		@unlink($tempNonZipFileName);
		
		exit();
		
	} else {
		// remove temp file
		@unlink($tempZipFileName);
		
		// return compressed file name
		return $tempNonZipFileName;
	}
	
} // unzipFile

function getCardType($Num) {
	// 20090509 -- george: changed the alorythm to tell card type. simplify, not require as many conditions for MC, AMEX and DISC;
	// old IF-s commented and kept for reference
	
	//  Get rid of any non-digits
	$Num = ereg_replace("[^[:digit:]]", "", $Num);

	if (!strlen($Num)) { return "UNKNOWN"; }
	
	// try to determine card type based on account number
	//if (ereg("^5[1-5].{14}$", $Num)) {
	if (ereg("^5.{15}$", $Num)) {
		return "MASTERCARD";
	} else if (ereg("^4.{15}$|^4.{12}$", $Num)) {
		return "VISA";
	//} else if (ereg("^3[47].{13}$", $Num)) {
	} else if (ereg("^3.{14}$", $Num)) {
		return "AMEX";
	//} else if (ereg("^6011.{12}$", $Num)) {
	} else if (ereg("^6.{15}$", $Num)) {
		return "DISCOVER";
	} else {
		return "UNKNOWN";
	}
	
} // getCardType

function xlsBOF() {
	$s = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
 
	if (isset($GLOBALS["xlsFunctionsReturnValue"])) { return $s; }

	echo $s;
    return;
}

function xlsEOF() {
    $s = pack("ss", 0x0A, 0x00);
	
	if (isset($GLOBALS["xlsFunctionsReturnValue"])) { return $s; }
	
	echo $s;
    return;
}

function xlsWriteNumber($Row, $Col, $Value) {
	$s = pack("sssss", 0x203, 14, $Row, $Col, 0x0);
	$s .= pack("d", $Value);
	
	if (isset($GLOBALS["xlsFunctionsReturnValue"])) { return $s; }
	
	echo $s;
    return;
}

function xlsWriteLabel($Row, $Col, $Value ) {
    $L = strlen($Value);
	
	$s = pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
	$s .= $Value;
	
	if (isset($GLOBALS["xlsFunctionsReturnValue"])) { return $s; }
	
	echo $s;
	return;
} 

function writeXlsRow($row, $data, $customDecimalDelimiter = ".", $srcEncoding = "UTF-8", $xlsEncoding = "Windows-1251") {
	$xlsCol = 0;
	$s = "";
	
	foreach ($data as $t) {
		
		// convert decimal delimiter to . in case it is , or something else
		$tNumber = trim(str_replace($customDecimalDelimiter, ".", $t));
		$tNumberArray = explode(".", $tNumber); // count MUST be <= 2. otherwise this may be a date like 11.10.2009, etc, which will mess it up
		
		// check if only numbers?
		if ((preg_replace("/[^0-9\.\-]/", "", $tNumber) == $tNumber) && (strlen($tNumber)) && (count($tNumberArray) <= 2)) {
			// number	
			$s .= xlsWriteNumber($row, $xlsCol, mb_convert_encoding($tNumber, $xlsEncoding, $srcEncoding));
		} else {
			$s .= xlsWriteLabel($row, $xlsCol, mb_convert_encoding($t, $xlsEncoding, $srcEncoding));
		}
    	$xlsCol++;
   	}
	
	if (!isset($GLOBALS["xlsFunctionsReturnValue"])) { $s = ""; }
	
	return $s;
}

if (!@function_exists('xmlEntities')) {
	function xmlEntities($str) {
	    $xml = array('&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;');
	    $html = array('&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;');
	    $str = str_replace($html,$xml,htmlentities($str));
	    $str = str_ireplace($html,$xml,$str);
	    return $str;
	} 
} // xmlEntities

// file download
//$file = 'ASDFGgg.pdf'; 
//_Download("files_dir/".$file, $file); 
function fileDownload($f_location,$f_name){ 
    header("Content-type: application/pdf"); // add here more headers for diff. extensions
    header("Content-Disposition: attachment; filename=\"".$f_name."\"");
    header('Content-Length: ' . filesize($f_location));
    header("Cache-control: private"); //use this to open files directly
    ob_clean();
    flush();
    readfile($f_location);

}

// Send email
// sendFrom and To are email lists - q@q.com, t@t.com ...
// message is html text
function sendMail ($sendTo, $subject, $message = '') {
	global $sendFrom;
	//echo $subject."\n";
	//return false;
	
	//mail($sendTo, $subject, $message);
	//return true;

	require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/mail/Mail.php");
	require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/mail/Mail/mail.php");
	require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/mail/Mail_Mime/mime.php");
	
	global $smtpInfo;
	
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

// db error function - see common/db.php
function dbErrorHandler($error='') {
	$GLOBALS["lastError"] = $error;
}