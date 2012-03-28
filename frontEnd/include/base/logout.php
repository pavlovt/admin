<?
ini_set('display_errors', 1);
require_once "../../../common/config.php";
require_once classPath.'class.user.php';
$User = new User;
$User->logout();

redirect(frontPath.'include/base/login.php', 'Вие успешно излезнахте от системата');