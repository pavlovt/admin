<? require_once 'include/base/base.php' ?>
<? require_once classPath.'class.category.php' ?>
<?
# php controller
if (empty($params->id))
   $title = 'Създаване на профил';
else
   $title = 'Редактиране на профил';

$User = new User();

if (!empty($params->id))
   $user = (object)$User->find($params->id);

if ($params->action == 'update') {
   $json['category'] = $params->category;
   $json['pages'] = $params->pages;
   $json['is_admin'] = $params->is_admin;
   $params->json = json_encode($json);

   $result = null;
   if (!empty($params->id)) {
      $result = $User->update((array)$params);
   } else {
      $result = $User->createNew((array)$params);
   }

   if (!empty($result)) {
      $message = "Профилът е запазен успешно";
      redirect('user.index.php', $message, $is_error = false);
   } else {
      $message = "Имаше грешка при запазването на профила ".$User->lastError;
      notify($message, $is_error = true);
      $user = $params;
   }

   //echo "<pre>"; print_r($_SESSION); exit;
}

$Category = new Category();
if ($Category->loadList())
   $categories = $Category->getIndexedColumn('id_name');
else
   die('Unable to connect to the clipping db');

$pages = get_pages();

?>
<!-- html body -->
<? require_once includePath.'base.html.php' ?>

<div id='content'>
   <? require_once 'include/user.edit.form.php' ?>
</div>


<!-- load Zebra_Form's stylesheet file -->
<link rel="stylesheet" href="<?=webPath?>class/zebra/public/css/zebra_form.css">

<!-- load Zebra_Form's JavaScript file -->
<script src="<?=webPath?>class/zebra/public/javascript/zebra_form.js"></script>

<? require_once includePath.'base.footer.php' ?>