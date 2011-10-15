<?
ini_set('display_errors','1');

require_once("../common/config.php");
require_once("class/cck/rb/class.data.php");
try {
//R::selectDatabase('db');
//$user = R::dispense('user');
//$lifeCycle = '';
$user = R::load('user',1);
//exit($lifeCycle);
//unset($user->id);
//echo "name ".(string)$user;//exit;
$user->uname = "Тодор Кирилов Павлов";
//echo "name ".(string)$user;//exit;
print_r($user);
var_dump($id = R::store($user));
exit($lifeCycle);
//R::trash($user);

//print_r(R::getAll( 'select * from salesperson' ));
//$q = (object)R::getRow( 'select * from salesperson' );
//exit('q');
$n = R::find('user', 1);
var_dump($n);
foreach ($n as $nn) {
	print_r($nn->name);
}

/*$bean = R::dispense('bean_table'); 
$bean->setMeta('sys.idfield', 'beanID'); 
$bean->setMeta('sys.iddatatype', 'varchar(128) not null'); 
$bean->taste = 'yummy'; 
R::store($bean); 
*/
} catch(Exception $e) {
	echo ('error');
	print_r($e);
	//sendMail("Error connecting to db ".$e->getMessage());
	exit;  
}