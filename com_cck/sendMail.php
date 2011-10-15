<?php
set_time_limit(0);

// includes
require_once("include/config.frontEnd.php");
require_once("common/sessionInit.php");

//ini_set('display_errors','On');
require_once("class/mail/Mail.php");
require_once("class/mail/Mail/mail.php");
require_once("class/mail/Mail_Mime/mime.php");

// separate emails in chunks of 50
$numEmailsToSend = 2;
$i = 0; $j = 0;
$emailArray = array();
$r = mysql_query("select sub.email from api_web.jos_acymailing_subscriber sub, api_web.jos_acymailing_listsub unsub where sub.subid = unsub.subid and unsub.status = 1", $db);
while ($w = mysql_fetch_row($r)) {
	$i++;
	$emailArray[$j][$i] = $w[0];
	if ($i == $numEmailsToSend) { $i = 0; $j++; }

}
mysql_free_result($r);

$htmlMsg = file_get_contents("/var/www/apiMonger/bulletin.email.html");

$header["MIME-Version"] = "1.0";
$header['Content-Type'] = 'text/html; charset="utf-8"';
$header["Content-Transfer-Encoding"] = "quoted-printable";
$header["From"] = 'api@napi.government.bg';
$header["Subject"] = 'Бюлетин за състоянието на пътищата';

$mime = new Mail_mime();
$mime->setHTMLBody($htmlMsg);
$body = $mime->get(array("html_charset" => "utf-8"));

$smtpinfo["host"] = 'ssl://smtp.googlemail.com';
$smtpinfo["port"] = '465';
$smtpinfo["auth"] = true;
$smtpinfo["username"] = 'thpav001@gmail.com';
$smtpinfo["password"] = 'qaz091275';

$smtp = Mail::factory('smtp', $smtpinfo);

foreach ($emailArray as $emails) {
	$recipients["Bcc"] = implode(",", $emails);

	$mail = $smtp->send($recipients, $header, $body);

	if (PEAR::isError($mail)) {
	   echo("\n" . date("H:i") . " " . $mail->getMessage() . "\n");
	  } else {
	   echo("\n".date("H:i")." Message successfully sent to ".$recipients["Bcc"]."\n");
	}

	time_sleep_until(strtotime("+1 min"));
}
?>