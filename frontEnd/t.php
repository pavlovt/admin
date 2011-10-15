<?
//ini_set('display_errors','1');

require_once("../common/config.php");
//require_once(basePath."class/cck/class.data.php");
require_once(basePath."class/class.baseTable.php");
require_once(basePath."class/class.user.php");

/*$db = dbWrapper::getDb('db');
echo "<pre>";
print_r($db->select('user')->fetchAllGroup());

//var_dump($db->insert('user', array("username" => "ququ", "password" => "pipi")));
exit($db->lastInsertId());


/*$validationRules = array(
      "required,username,Полето за име на клиент е задължително",
      "required,password,Полето за парола е задължително"
      );

$u['username'] = 'ququ';
$u['password'] = 'pipi';

var_dump(validateFields($u, $validationRules));

exit;*/



/*$u = new user();
var_dump($u->loadList());
print_r($ul = $u->next());
unset($ul->password);
//unset($ul->username);
var_dump($u->isValid($ul));

exit($u->error);
*/
$db = dbWrapper::getDb('db');
var_dump($db->insert('user', array("username" => "ququ", "password" => "pipi")));
exit($db->lastInsertId());

$di = new data();

echo '<pre>';
//print_r($di->loadById(1));
var_dump($di->loadList());
echo $di->totalRecords;
print_r($di->next());
print_r($di->next());

exit(@$GLOBALS["lastError"]);




//unset($GLOBALS["lastError"]);

//var_dump(query());


$user = new user("mysql:host=localhost;dbname=admindb", "root", "");
//var_dump($user->select('user', '', '', '*', 'row'));


function query() {
	$db =& dbWrapper::getDb('db');

	return $db->select('user', 'userId=2', '', '*', 'row');

	
}

//if ($r = query("SELECT * FROM user") && $w = $

//print_r($db->query('show tables')->fetchAll());

