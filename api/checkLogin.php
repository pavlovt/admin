<?
# check login and permissions
require_once classPath.'class.user.php';

$user_name = '';
if (!empty($_SESSION['userDetails']['id'])) {

  $CurrentUser = new User(true);
  $CurrentUser->find($_SESSION['userDetails']['id']);

  if (!$CurrentUser->has_access(base_file))
    redirect(index_page, 'Нямате достъп до тази страница');

} else {
  die('Нямате право на достъп');

}