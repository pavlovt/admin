<?
# load application script on each page
get_coffee(includePath.'application.coffee');

# load the coffeescript with the same name if exists
if (@file_exists(basePath.'frontEnd/js/'.file.'.coffee')) {
  get_coffee(basePath.'frontEnd/js/'.file.'.coffee');
}
?>

</body>
<html>