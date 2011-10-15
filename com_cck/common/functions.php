<?php

// programatically register joomla user
function joomlaRegistration($userInfo) {
	jimport('joomla.user.helper');
	$authorize =& JFactory::getACL();
	$user =& JFactory::getUser();
	
	$user->set('username', $userInfo["username"]);
	$user->set('password', $userInfo["password"]);
	$user->set('name', $userInfo["first_name"]." ".$userInfo["last_name"]);
	$user->set('email', $userInfo["email"]);
	
	// password encryption
	$salt  = JUserHelper::genRandomPassword(32);
	$crypt = JUserHelper::getCryptedPassword($user->password, $salt);
	$user->password = "{$crypt}:{$salt}";
	
	// user group/type
	$user->set('id', 0);
	$user->set('usertype', 'Registered');
	$user->set('gid', $authorize->get_group_id( '', 'Registered', 'ARO' ));
	
	$date =& JFactory::getDate();
	$user->set('registerDate', $date->toMySQL());
	
	//echo "<pre>"; print_r($user); exit;
	
	if(!$user->save()) {
		return false;
	}
	
	if(!$user->id) {
		return false;
	}
	
	return $user->id;
	
}

// programatically register joomla user
function joomlaFBRegistration($userInfo) {
	jimport('joomla.user.helper');
	$authorize =& JFactory::getACL();
	$user =& JFactory::getUser();
	
	$user->set('username', $userInfo["username"]);
	$user->set('password', $userInfo["password"]);
	$user->set('name', $userInfo["first_name"]." ".$userInfo["last_name"]);
	$user->set('email', $userInfo["email"]);
	
	// user group/type
	$user->set('id', 0);
	$user->set('usertype', 'Registered');
	$user->set('gid', $authorize->get_group_id( '', 'Registered', 'ARO' ));
	
	$date =& JFactory::getDate();
	$user->set('registerDate', $date->toMySQL());
	
	//echo "<pre>"; print_r($user); exit;
	
	if(!$user->save()) {
		//echo "<pre>"; print_r($user); exit;
		return false;
	}
	
	if(!$user->id) {
		return false;
	}
	
	// include user id in the password
	$user->set('password', $user->id.'_'.$userInfo["password"]);
	//echo "<pre>"; print_r($user); //exit;
	
	// password encryption
	$salt  = JUserHelper::genRandomPassword(32);
	$crypt = JUserHelper::getCryptedPassword($user->password, $salt);
	$user->password = "{$crypt}:{$salt}";
	
	if(!$user->save()) {
		return false;
	}
	
	return $user->id;
	
}

// programatically register virtuemart user - needs joomla registration first
function vmRegistration($uid, $d) {
	 
	$vendor_id = $_SESSION["ps_vendor_id"];
	$hash_secret = "VirtueMartIsCool";
	$db = new ps_DB;
	$timestamp = time();
	
	// Insert billto;
	$fields = array();
	
	$fields['user_info_id'] = md5(uniqid( $hash_secret));
	$fields['user_id'] =  $uid;
	$fields['address_type'] =  'BT';
	$fields['address_type_name'] =  '-default-';
	$fields['cdate'] =  $timestamp;
	$fields['mdate'] =  $timestamp;
	$fields['perms'] =  $d['perms'];
	
	$values = array();
	
	// Get all fields which where shown to the user
	require_once( JPATH_ADMINISTRATOR . '/components/com_virtuemart/classes/ps_userfield.php');
	$userFields = ps_userfield::getUserFields('account', false, '', true);
	$skipFields = ps_userfield::getSkipFields();
	foreach( $userFields as $userField ) {
		if( !in_array($userField->name, $skipFields )) {
			$fields[$userField->name] = ps_userfield::prepareFieldDataSave( $userField->type, $userField->name, @$d[$userField->name]);
		}
	}
	$fields['user_email'] = $fields['email'];
	unset($fields['email']);
	//echo "<pre>"; print_r($fields); exit;
	
	$db->buildQuery( 'INSERT', '#__{vm}_user_info', $fields );
	if (!$db->query()) {
		echo 'INSERT', '#__{vm}_user_info';
		return false;
	}
	
	// Insert vendor relationship
	$q = "INSERT INTO #__{vm}_auth_user_vendor (user_id,vendor_id)";
	$q .= " VALUES ";
	$q .= "('" . $uid . "','$vendor_id') ";
	if (!$db->query($q)) {
		echo $q;
		return false;
	}
	
	// Insert Shopper -ShopperGroup - Relationship
	$q  = "INSERT INTO #__{vm}_shopper_vendor_xref ";
	$q .= "(user_id,vendor_id,shopper_group_id,customer_number) ";
	$q .= "VALUES ('$uid', '$vendor_id','".$d['shopper_group_id']."', '".$d['customer_number']."')";
	if (!$db->query($q)) {
		echo $q;
		return false;
	}
	
	$_REQUEST['id'] = $_REQUEST['user_id'] = $uid;
	//$vmLogger->info( $VM_LANG->_('VM_USER_ADDED') );
	
	return true;
}

// login user by his facebook id
function joomlaFBLogin($fbId) {
	global $mainframe;
	$db =& JFactory::getDBO();
	$query = "SELECT id FROM #__users where username='fb_".$fbId."'";
	$db->setQuery( $query );
	$userId = $db->loadResult();
	//echo $userId;exit;
	
	$usersipass['username'] = "fb_".$fbId;
    $usersipass['password'] = $userId."_bz_".$fbId;
    //echo "<pre>"; print_r($usersipass); //exit;
    $mainframe->login($usersipass);
    $user =& JFactory::getUser();
    if(!$user->id) {
    	return false;
    }
    
    return true;
}

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

/**
 * This function will return the number of rows a query will return
 * @param  string $sql  the SQL query
 * @return  int  the number of rows the query specified will return
 * @throws   PDOException  if the query cannot be executed
 */
function getRowCount($sql) 
{
  global $conn;
  
  $sql = trim($sql);
  $sql = preg_replace('~^SELECT\s.*\sFROM~s', 'SELECT COUNT(*) FROM', 
                         $sql);
  $sql = preg_replace('~ORDER\s+BY.*?$~sD', '', $sql);
  $stmt = $conn->query($sql);
  $r = $stmt->fetchColumn(0);
  $stmt->closeCursor();
  return $r;
}