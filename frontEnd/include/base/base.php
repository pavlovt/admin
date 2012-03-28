<? 
ini_set('display_errors', 1);
require_once '../common/config.php';
define('file', pathinfo($_SERVER['REQUEST_URI'], PATHINFO_FILENAME));

# for users.edit.php the base name is user
$file = explode('.', file);
define('base_file', $file[0]);

# check login and permissions
require_once 'checkLogin.php';

# load the class with the same base name if exists
if (file_exists(classPath.'class.'.base_file.'.php')) {
   require_once classPath.'class.'.base_file.'.php';
}

$params = (object)$_REQUEST;