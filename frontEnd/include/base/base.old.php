<? 
//ini_set('display_errors', 1);
require_once '../common/config.php';
define('file', pathinfo($_SERVER['REQUEST_URI'], PATHINFO_FILENAME));

# for users.edit.php the base name is user
$file = explode('.', file);
define('base_file', $file[0]);

# load the class with the same base name if exists
if (file_exists(basePath.'class/webresearch/class.'.base_file.'.php')) {
   require_once basePath.'class/webresearch/class.'.base_file.'.php';
}

startblock('session'); 
   //require_once basePath.'common/messagesHandler.php';   
endblock(); 

# php controller
startblock('controller'); 
endblock(); 
?>

<html>

<head>
   <title>
      <?startblock('page_title')?>
         Web research
      <?endblock()?>
   </title>

   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

   <? require_once basePath.'common/commonHtmlHead.php' ?>   

   <? emptyblock('head') ?>

   <script type="text/javascript">
      <? emptyblock('js_head') ?>
   </script>

</head>

<body>
   <? require_once basePath.'common/container.top.php' ?>

   <? emptyblock('start_body') ?>
   
   <div id="header">
      <h2>
         <? emptyblock('title') ?>
      </h2>
      <? emptyblock('header') ?>
   </div>

   <div id='content'>
      <? emptyblock('content') ?>
   </div>

   <div id='footer'>
      <? emptyblock('footer') ?>
   </div>

   <?
   get_coffee('common/application.coffee');

   # load the coffeescript with the same name if exists
   if (file_exists(basePath.'frontEnd/js/'.file.'.coffee')) {
      get_coffee('frontEnd/js/'.file.'.coffee');
   }
   ?>
   <script type="text/javascript">
      <? emptyblock('js') ?>
   </script>

   <? emptyblock('end') ?>
</body>

</html>
