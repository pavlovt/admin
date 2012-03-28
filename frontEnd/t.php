<?
ini_set('display_errors', 1);
require_once '../common/config.php';


echo "<pre>";

require_once classPath.'class.user.php';
$User = new User;

var_dump($User->login('webresearch', '1231'));
print_r($_SESSION);

exit($GLOBALS["lastError"]);

/*require_once classPath.'class.article.php';
$Article = new Article(true);
$cats = get_user_category();
$cats = int_array($cats);
//$res = $Article->find_by_category($cats, '', $skip = 100);
$res = $Article->find_by_category_count($cats, ' AND data.date = CURDATE() ');

echo "<pre>"; print_r($res); exit;


require_once '../class/webresearch/class.category.php';
$Category = new Category();
//var_dump($Category->loadList());
$Category->loadById(763);
//var_dump($Category->is_parent(), $Category->p);
$cats = array(776,1353);
$categories = $Category->get_selected_by_parent($cats);
echo "<pre>"; print_r($categories); //exit;


exit($GLOBALS["lastError"]);*/