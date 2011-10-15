<?php

if (@$_POST["action"] == 'fbconnect') {
	// include joomla and virtuemart
	define( '_JEXEC', 1 );
	define('JPATH_BASE', dirname(dirname(dirname(__FILE__))));
	define( 'DS', DIRECTORY_SEPARATOR );
	
	require_once (JPATH_BASE . DS . 'includes' . DS . 'defines.php');
	require_once (JPATH_BASE . DS . 'includes' . DS . 'framework.php');
	
	$mainframe = JFactory::getApplication('site');
	$mainframe->initialise();
	JPluginHelper::importPlugin('system');
	
	require_once( JPATH_BASE . '/components/com_virtuemart/virtuemart_parser.php' );
	require_once( JPATH_SITE . '/custom/functions.php' );
	
	$accessToken = @$_POST["access_token"];
	
	if (!strlen($accessToken)) {
		echo json_encode(array("error" => "Грешка при свързването с фейсбук, моля опитайте по-късно"));
		exit;
	}
	
	// get user data from facebook
	$url = "https://graph.facebook.com/me?access_token=".$accessToken;
	
	// facebook user information
	/*
    [id] => 100000759318860
    [name] => Todor Pavlov
    [first_name] => Todor
    [last_name] => Pavlov
    [link] => http://www.facebook.com/profile.php?id=100000759318860
    [birthday] => 12/09/1975
    [education] => Array
        (
            [0] => Array
                (
                    [school] => Array
                        (
                            [id] => 108833652482273
                            [name] => СУ "Климент Охридски"
                        )

                    [year] => Array
                        (
                            [id] => 137616982934053
                            [name] => 2006
                        )

                    [type] => College
                )

        )

    [gender] => male
    [email] => thpav@abv.bg
    [timezone] => 3
    [locale] => en_US
    [verified] => 1
    [updated_time] => 2011-04-07T14:24:46+0000
	 */
	$fbUserInfo = json_decode(file_get_contents($url), true);
	//echo "<pre>"; print_r($fbUserInfo); //exit;
	
	if (!@strlen($fbUserInfo["id"])) {
		echo json_encode(array("error" => "Грешка при свързването с фейсбук, моля опитайте по-късно"));
		exit;
	}
	
	if (!@strlen($fbUserInfo["email"])) {
		echo json_encode(array("error" => "Без достъп до Вашият имейл във фейсбук не може да се регистрирате в книжарницата"));
		exit;
	}
	
	$db =& JFactory::getDBO();
	$query = "SELECT id FROM #__users where username='fb_".$fbUserInfo["id"]."'";
	$db->setQuery( $query );
	$db->query();
	
	// indicates if user is registered or only logged in
	$registered = '';
	
	// create new user
	if (!$db->getNumRows()) {
		$registered = "registered";
		
		// exit if there is already a user with the same email but not registered trought facebook
		$db =& JFactory::getDBO();
		$query = "SELECT id FROM #__users where email='".$fbUserInfo["email"]."'";
		$db->setQuery( $query );
		$db->query();
		
		if ($db->getNumRows()) {
			echo json_encode(array("error" => "В книжарницата вече има регистриран потребител с имейл ".$fbUserInfo["email"]));
			exit;
		}
		
		$userInfo["username"] = "fb_".$fbUserInfo["id"];
		$userInfo["password"] = "bz_".$fbUserInfo["id"];
		$userInfo["password2"] = "bz".$fbUserInfo["id"];
		$userInfo["vm_birth_date"] = str_replace("/", ".", $fbUserInfo["birthday"]);
		$userInfo["email"] = $fbUserInfo["email"];
		$userInfo["first_name"] = $fbUserInfo["first_name"];
		$userInfo["last_name"] = $fbUserInfo["last_name"];
		$userInfo["agreed"] = 1;
		$userInfo["useractivation"] = 1;
		$userInfo["option"] = "com_jumi";
		$userInfo["register_account"] = 1;
		$userInfo["remember"] = "yes";
		/*$userInfo["address_1"] = "facebook";
		//$userInfophone_1[""] = "facebook";
		$userInfo["city"] = "facebook";
		$userInfo["zip"] = "facebook";*/
		//$userInfo["shopper_group_id"] = "5";
		//$userInfo['customer_number'] = uniqid( rand() );
		//$userInfo["perms"] = "shopper";
		
		//record gender
		/*if (@strlen($fbUserInfo["gender"])) {
			if ($fbUserInfo["gender"] == "male") {
				$userInfo["vm_sex"] = "мъж";
			} elseif ($fbUserInfo["gender"] == "female") {
				$userInfo["vm_sex"] = "жена";
			}
		}*/
		
		//echo "<pre>"; print_r($userInfo); exit;
		
		// joomla facebook specific registration
		// Check that username is not greater than 150 characters
		// Check that password is not greater than 100 characters
		if(!$uid = joomlaFBRegistration($userInfo)) {
			echo json_encode(array("error" => "Грешка при регистрацията ви в книжарницата, моля опитайте по-късно"));
			exit;
		}
		
		
		// VirtueMart registration
		/*if(!vmRegistration($uid, $userInfo)) {
			echo json_encode(array("error" => "Грешка при регистрацията ви в книжарницата, моля опитайте по-късно"));
			exit;
		}*/
	}

	// login the user by his facebook id
	if (!joomlaFBLogin($fbUserInfo["id"])) {
		echo json_encode(array("error" => "Грешка при влизането ви в книжарницата, моля опитайте по-късно"));
		exit;
	}
    
	//$mainframe->enqueueMessage("Успешно влезнахте в системата");  
    echo json_encode(array("error" => "", "registered" => $registered));
	exit;
}
?>
<div id="fb-root" style="margin-bottom:10px;"></div>
      <script src="http://connect.facebook.net/en_US/all.js"></script>
      <script>
         FB.init({ 
            appId:'173443556032541', cookie:true, status:true, xfbml:true 
         });

        // fb answer: obj : { perms : "email,user_birthday,user_hometown,user_location", session : [access_token : "...", uid : "1000..."] }
		//FB.Event.subscribe('auth.login', function(response) {
		function fbLogin() {
			FB.getLoginStatus(function(response) {
				// do something with response.session
				//console.log(response);
				if (response.session.access_token) {
					$j.ajax({
						type: "POST",
						url: "/modules/mod_query/fbConnect.php",
						data: "action=fbconnect&" +
							  "access_token=" + response.session.access_token,
						dataType: "json",
						success: function(data){
							if(!data) {
								alert("Грешка при свързване със системата. Моля опитайте по-късно.");
							} else if(data.error.length) {
								alert(data.error);
							} else {
								if(data.registered.length) {
									//console.log('reg');
									FB.api('/me/feed', 'post', { message: 'Успешна регистрация в Bookzone.bg!' }, function(response) {
										//console.log(response);
									  /*if (!response || response.error) {
									    alert('Error occured');
									  } else {
									    alert('Post ID: ' + response.id);
									  }*/
									});
								}
								
								//alert("Успешно влезнахте в системата");
								window.location.href = window.location.protocol + "//" + window.location.host; // + window.location.pathname;
							}
						},
				        error:function (xhr, ajaxOptions, thrownError){
							alert("Грешка при свързване със системата. Моля опитайте по-късно.");
				        }
					});
				} else {
					alert("Грешка при свързване с фейсбук. Моля опитайте по-късно.");
				}
			});
		}
		//);

      </script>

<?/*autologoutlink="true" <fb:login-button show-faces="yes" perms="email"></fb:login-button>*/ ?>
<?
$user =& JFactory::getUser();
if (!$user->id) { ?>
<fb:login-button perms="email,publish_stream" onlogin="fbLogin();"></fb:login-button>
<? 
} else {
	// if user is logged in with facebook then show his picture
	$fbId = explode("_", $user->username);
	if ($fbId[0] == 'fb') {
		echo '<table border="0" align="right" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="middle"><img width="30" id="facebookUserPic" src="https://graph.facebook.com/'.$fbId[1].'/picture"/?type=square></td>
	<td valign="middle" width="5"></td>
    <td valign="middle"><span id="fbUserName">'.$user->name.'</span></td>
  </tr>
</table>

';
	}
} 
?>