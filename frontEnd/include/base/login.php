<?
# ini_set('display_errors', 1);
require_once "../../../common/config.php";

$params = (object)$_REQUEST;

if (@$params->action == 'login') {

	// check login
	require_once classPath.'class.user.php';
	$User = new User;
	if (!$User->login(@$params->username, @$params->password))
		notify("Невярно име или парола. Моля пробвайте отново (проверете дали клавишът CAPS Lock не е натиснат и дали не пишете на кирилица)", $is_error = true);
	else
		redirect(index_page);

}

$title = 'Вход в системата';
?>

<html>

<head>
   <title><?=@$title?></title>

   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

   <? require_once includePath.'commonHtmlHead.php' ?>   

</head>

<body>
<? require_once includePath.'messagesHandler.php' ?>

<div style="position:absolute; left: 40%; top: 30%;">
<form action="login.php" method="post">
<input type="hidden" name="action" value="login">

<table cellspacing="2" cellpadding="2" >
<tr>
	<td colspan="2"><strong><?=$title?></strong></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td style="text-align: right;"><strong>Име</strong></td>
	<td><input type="text" name="username" value="" style="width: 150px;" maxlength="50"></td>
</tr>
<tr>
	<td style="text-align: right;"><strong>Парола</strong></td>
	<td><input type="password" name="password" value="" style="width: 150px;" maxlength="50"></td>
</tr>
<tr>
	<td style="text-align: right;">&nbsp;</td>
	<td><input type="submit" value="Вход" class="button"></td>
</tr>
</table>

</form>
</div>

<? require_once 'base.footer.php' ?>
