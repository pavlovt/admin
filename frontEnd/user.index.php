<? require_once 'include/base/base.php' ?>

<? 
# php controller
$title = "Потребители";
$User = new User(true);

if (@$params->action == 'delete') {
   if ($User->find($params->id) && $User->delete())
      notify('Потребителят e успешно изтрит');
   else
      notify('Имаше проблем при изтриването на потребителя '.$User->lastError, $error = true);
}

if ($User->loadList())
   $users = $User->getAll();

foreach ($users as $k => $user) {
   $users[$k]->category = @join(', ', get_category_names($user->json['category']));
   $users[$k]->service = @join(', ', get_user_pages($user->json['pages']));
}
//echo "<pre>"; print_r($users); exit;      
$table_header = array('#', 'Име', 'Потребителско име', 'Парола', 'Email', 'Категории', 'Услуги', '', '');

# data row definition
# used when printing the result in html table
# each type (string, int, float) gets an apropriate representation
# the class is applied to every table cell in format: "class" => "class1 class2 class3..."
$def = array(
   "id" => array("type" => "int", "class" => ""), 
   "name" => array("type" => "string", "class" => ""),
   "username" => array("type" => "string", "class" => ""),
   "password" => array("type" => "string", "class" => ""),
   "email" => array("type" => "string", "class" => ""),
   "category" => array("type" => "string", "class" => ""),
   "service" => array("type" => "string", "class" => ""),
   "Редактирай" => array("type" => "link", "class" => "edit", "link" => "user.edit.php?action=edit&id=", "id" => "id"),
   "Изтрий" => array("type" => "link", "class" => "delete", "link" => "user.index.php?action=delete&id=", "id" => "id"),
);

?>

<? require_once includePath.'base.html.php' ?>

<div id='content'>
   <a href="user.edit.php?action=create&id=" class="button add size10 right_float" data-id="3">Създай нов</a><br><br>
   <table id="dataTable" cellpadding="0" cellspacing="0" border="0" class="display" style="white-space: nowrap;">
      <thead>
         <tr style="text-align: left;">
            <?=html_table_header($table_header)?>
         </tr>
      </thead>
      <tbody>
         <?=html_table_content($users, $def)?>
      </tbody>
   </table>
</div>

<script src="<?=includeWebPath?>datatables.config.js" type="text/javascript" charset="utf-8" async defer></script>

<? require_once includePath.'base.footer.php' ?>