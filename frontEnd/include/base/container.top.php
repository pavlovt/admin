<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr>
	<td colspan="2" style="background: #005DBC; color: #FFFFFF; font-size: 8pt; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; border-bottom: 1px solid #005DBC;">
		<strong>You are logged in as &nbsp;&#151;&nbsp;<?=$CurrentUser->p->name?></strong> ( <a href="<?=frontPath?>include/base/logout.php" class="whiteLink">logout</a> )
	</td>
</tr>

<tr>
	<td width="60%" nowrap colspan="2" style="background: #f1f1f1; color: #000000; font-size: 8pt; font-family: Tahoma, Verdana, Arial, Helvetica, Sans-Serif; border-bottom: 1px solid #3399CC; margin-bottom: 70px;">
		<ul id="jsddm">

		<li><a href="<?=frontPath?>index.php">Начало</a></li>

		<? if ($CurrentUser->has_access('clipping')) : ?>
		<li>
			<a href="<?=frontPath?>clipping.index.php">Клипинг</a>

			<ul>
				<li><a href="<?=frontPath?>clipping.index.php">Начало</a></li>
				<li><a href="<?=frontPath?>clipping.bulletin.php">Бюлетин</a></li>
				<li><a href="<?=frontPath?>clipping.search.php">Търсене</a></li>
			</ul>

		</li>
		<? endif ?>

		<? if ($CurrentUser->has_access('trend')) : ?>
		<li><a href="<?=frontPath?>trend.index.php">Тенденции</a></li>
		<? endif ?>

		<? if ($CurrentUser->has_access('user')) : ?>
		<li><a href="<?=frontPath?>user.index.php">Потребители</a></li>
		<? endif ?>
		</ul>
		<div class="clear"> </div>
	</td>
</tr>

</table>