<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
JToolBarHelper::title( 'Редактиране на отстъпки', 'cpanel.png' );
//JToolBarHelper::cancel( 'cancel', 'Close' );

global $option, $mainframe;

$db =& JFactory::getDBO();

//$db->setQuery("SET NAMES 'UTF8'");
//$db->query();

require_once("components/com_wphdiscount/class/wsDiscountDb.php");

$discount_i = new wsDiscountDb($db);
/*$discount = array();
$discount_i->loadById(7);
$discount = $discount_i->p;
/*$discount_i->loadList();
$discount_i->next();
$discount_i->next();
print_r($discount_i->result);echo $discount_i->index;
print_r($discount_i->p); exit;
*/

if (@$_POST["action"] == "edit") {
	$discountId = isset($_POST["discountId"]) ? $_POST["discountId"] : "";
	$discount_i->loadById($discountId);
	$discount = $discount_i->p;
	//print_r($discount); exit;
} elseif (@$_POST["action"] == "submit") {
	$vars = json_decode($_POST["json"], TRUE);
	$discountId = $vars["discountId"];
	$price = $vars["price"];
	$type = $vars["type"];
	$seq = $vars["seq"];
	$bundleProducts = $vars["bundleProducts"];
	$category = $vars["category"];
	$customer = $vars["customer"];
	$message = $vars["message"];
	//echo '<pre>'; print_r($vars); exit;

	$indexToDelete = array("action", "discountId", "price", "type", "seq", "bundleProducts", "category", "customer", "message");

	// clear unused elements of the array
	foreach ($vars as $index => $value) {
		if (stristr($index, "multiselect") || in_array($index, $indexToDelete)) {
			unset($vars[$index]);
		}

	}

	// check if category array is empty
	if (empty($category["categoryList"]) && empty($category["publisherList"]) && empty($category["authorList"])) {
		$category = null;
	}
	
	// check if customer array is empty
	$customerFields = array("customerList","orderCount","orderSum","fromAge","toAge","sexList","zodiacList","cityList","lastLoginFromDate","lastLoginToDate","lastOrderFromDate","lastOrderToDate");
	$emptyCustomer = true;
	foreach ($customerFields as $field) {
		if (!empty($customer[$field])) {
			$emptyCustomer = false;
			break;
		}
	}
	
	if ($emptyCustomer) {
		$customer = null;
	}

	//echo '<pre>'; print_r($vars); exit;
	
	if ((int)$discountId) {
		$discount_i->loadById($discountId);
		if (!$discount_i->update($message, $price, $type, $seq, $vars, $bundleProducts, $category, $customer)) {
			JFactory::getApplication()->enqueueMessage( 'Неуспешно публикуване '.$discount_i->lastError , 'error' );
		} else {
			//JFactory::getApplication()->enqueueMessage( ' Отстъпката е публикувана ');
			//$discount = $discount_i->p;
			//header("Location: index.php?option=com_wphdiscount");
			JFactory::getApplication()->redirect("index.php?option=com_wphdiscount", ' Отстъпката е публикувана ');
		}
	} else {
		if (!$discount_i->createNew($message, $price, $type, $seq, $vars, $bundleProducts, $category, $customer)) {
			JFactory::getApplication()->enqueueMessage( 'Неуспешно публикуване '.$discount_i->lastError , 'error' );
		} else {
			/*JFactory::getApplication()->enqueueMessage( ' Отстъпката е публикувана ');
			$discount = $discount_i->p;
			header("Location: index.php?option=com_wphdiscount");*/
			JFactory::getApplication()->redirect("index.php?option=com_wphdiscount", ' Отстъпката е публикувана ');
		}
	}

	//print_r($vars);
	//exit;
}

// load products
$q = 'SELECT p.product_id, p.product_name FROM #__vm_product p, #__vm_product_price pr WHERE p.product_id = pr.product_id AND pr.price_quantity_start = 0 AND pr.product_price > 0 AND product_publish = "Y" ORDER BY product_name';
$db->setQuery( $q );
$rows = $db->loadRowList();
foreach ($rows as $w) {
	$productList[$w[0]] = $w[1];
}

// load categories and publishers
$query = "SELECT category_name, category_id FROM #__vm_category WHERE category_publish = 'Y' ORDER BY category_name";
$db->setQuery($query);
$rows = $db->loadObjectList();
$categoryList = array();
$publisherName = "";
foreach($rows as $w) {
	$categoryList[$w->category_id] = $w->category_name;
}

$query = "SELECT name, ordering FROM #__menu where menutype = 'publishers' AND published = 1 ORDER BY name";
$db->setQuery($query);
$rows = $db->loadObjectList();
$publisherList = array();
foreach($rows as $w) {
	$publisherList[$w->ordering] = $w->name;
}

$q = 'SELECT DISTINCT product_author FROM #__vm_product WHERE product_publish = "Y" ORDER BY product_author';
$db->setQuery( $q );
$rows = $db->loadRowList();
foreach ($rows as $w) {
	$authorList[$w[0]] = $w[0];
}

$q = 'SELECT DISTINCT user_id, concat(first_name, " ", last_name) FROM #__vm_user_info ORDER BY first_name';
$db->setQuery( $q );
$rows = $db->loadRowList();
foreach ($rows as $w) {
	$userList[$w[0]] = $w[1];
}

$q = 'SELECT DISTINCT coupon_id, coupon_code FROM #__vm_coupons ORDER BY coupon_code';
$db->setQuery( $q );
$rows = $db->loadRowList();
foreach ($rows as $w) {
	$couponList[$w[0]] = $w[1];
}

$q = 'SELECT DISTINCT city FROM #__vm_user_info ORDER BY city';
$db->setQuery( $q );
$rows = $db->loadRowList();
foreach ($rows as $w) {
	$cityList[] = $w[0];
}

// load gift products
$q = 'SELECT p.product_id, p.product_name FROM #__vm_product p, #__vm_product_price pr WHERE p.product_id = pr.product_id AND pr.price_quantity_start = 0 AND pr.product_price = 0 AND product_publish = "Y" ORDER BY product_name';
$db->setQuery( $q );
$rows = $db->loadRowList();
foreach ($rows as $w) {
	$giftProductList[$w[0]] = $w[1];
}


$weekdayList = array("Mon" => "Понеделник", "Tue" => "Вторник", "Wen" => "Сряда", "Thu" => "Четвъртък", "Fri" => "Петък", "Sat" => "Събота", "Sun" => "Неделя");

$discountTypeList = array("percent" => "Процент", "total" => "В лева");

$sexList = array("мъж","жена");
$zodiacList = array("козирог","водолей","риби","овен","телец","близнаци","рак","лъв","дева","везни","скорпион","стрелец");

// parameters of the discount message
$discountParameters = array( 
	"Отстъпка" => array("отстъпка"), 
	"Съставен продукт" => array("съставен продукт", "сума на поръчките на съставен продукт", "брой на поръчките на съставен продукт", "категория", "брой продукти от категория", "издателство", "брой продукти от издателство", "автор", "брой продукти от този автор"),
	"Общи критерии" => array("обща сума в кошницата", "брой продукти в кошницата", "талон"),
	"Критерии по дата и час" => array("период на валидност от", "период на валидност до", "ден от седмицата", "часова зона от", "часова зона до"),
	"Критерии по клиент" => array("клиент", "брой поръчки на клиента", "обща сума на поръчките на клиента", "възраст", "пол", "зодия", "населено място", "дата на последно логване", "дата на последна поръчка"),
	"Безплатен продукт" => array("безплатен продукт")
	);

//echo "<pre>"; print_r($discount); exit;
?>
<link href="components/com_wphdiscount/js/jquery-ui-1.8.10.custom.css" rel="stylesheet" type="text/css">
<link href="components/com_wphdiscount/js/jquery.multiselect.css" rel="stylesheet" type="text/css">
<link href="components/com_wphdiscount/js/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="components/com_wphdiscount/js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="components/com_wphdiscount/js/json2.js"></script>
<script type="text/javascript" src="components/com_wphdiscount/js/form2object.js"></script>
<script type="text/javascript" src="components/com_wphdiscount/js/jquery-ui-1.8.10.custom.min.js"></script>
<script type="text/javascript" src="components/com_wphdiscount/js/jquery.multiselect.min.js"></script>
<script type="text/javascript" src="components/com_wphdiscount/js/jquery.multiselect.filter.js"></script>
<script type="text/javascript" src="components/com_wphdiscount/js/jquery-ui-timepicker-addon.js"></script>

<script type="text/javascript">
		var $j = jQuery.noConflict();
		var lastSeq = <?=$lastSeq?>

		$j(document).ready(function() {
			//$j('#sortForm input, select').each( function() { $j(this).attr("name", $j(this).attr("id")) })

			$j("[name=bundleProducts], [name=category.categoryList], [name=category.publisherList], [name=category.authorList], [name=customer.customerList], [name=customer.sexList], [name=customer.zodiacList], [name=customer.cityList], [name=weekdayList], [name=couponList], [name=giftProducts]").each(function () {
			   $j(this).multiselect({
			   	  noneSelectedText: $j(this).attr("title"),
				  multiple: true,
				  selectedList: 10,
			      minWidth: 350,
			      width: 350
			   }).multiselectfilter();
			});

			$j("[name=type]").each(function () {
			   $j(this).multiselect({
			   	  noneSelectedText: $j(this).attr("title"),
			   	  multiple: false,
				  selectedList: 1,
				  minHeigth: 50
			   });
			});

			// datepicker
			$j( "[name=fromDate], [name=customer.lastShippingOn], [name=customer.lastLoginOn], [name=toDate], [name=customer.lastLoginToDate], [name=customer.lastLoginFromDate], [name=customer.lastOrderToDate], [name=customer.lastOrderFromDate]" ).datepicker({
				dateFormat: 'dd.mm.yy',
				showOtherMonths: true,
				selectOtherMonths: true
				//changeMonth: true,
				//changeYear: true
			});

			$j("input[type=submit], input[type=button]").button();

			$j('[name=fromTime], [name=toTime]').timepicker({});
		} );

	</script>

<style>
	#discountForm th { text-align: right; font-size: 12px; }
	/*optgroup { font-weight:bold; }*/
</style>

<form id="discountForm" method="post" onsubmit="javascript: return prepareSubmit();">
<input type="hidden" name="action" value="submit">
<input type="hidden" name="json" value="">
<input type="hidden" name="discountId" value="<?=@$discount["discountId"]?>">

<fieldset>
<legend>Отстъпка</legend>
<table cellpadding="10" style="width: 100%">
<tr>
	<th>
	Отстъпка
	</th>
	<td>
	<table><tr>
		<td>
			<input type="text" name="price" value="<?=@$discount["value"]?>">
		</td>
		<td>&nbsp;</td>
		<th>
			Тип
		</th><td>
			<select name="type" title="Изберете тип на отстъпката">
			<? foreach ($discountTypeList as $id => $name) {
				if ($id == @$discount["type"]) {
					?><option value="<?=$id?>" selected><?=$name?></option><?
				} else {
					?><option value="<?=$id?>"><?=$name?></option><?
				}
			}?>
			</select>
		</td>
	</tr></table>
	</td>
</tr><tr>
	<th>
		Максимален брой използвания на отстъпката
	</th>
	<td>
		<table><tr>
		<td>
			<input type="text" name="maxDiscountUsage" value="<?=@$discount["rules"]["maxDiscountUsage"]?>">
		</td>
		<td>&nbsp;</td>
		<th>
			Брой използвани отстъпки
		</th><td>
			<input type="text" name="discountUsage" value="<?=@$discount["rules"]["discountUsage"]?>" >
		</td>
	</tr></table>
	</td>
</tr><tr>
	<th>
		Приоритет
	</th>
	<td>
		<?
		if (!isset($discount_i->p["seq"])) {
			$seq = $discount_i->findLastSeq() + 1;
		} else {
			$seq = $discount_i->p["seq"];
		}
		?>
		<input type="text" name="seq" value="<?=$seq?>" disabled="disabled">
	</td>
</tr><tr>
	<th>
		Съобщение
	</th>
	<td>
		<textarea name="message" cols="50" rows="3"><?=@$discount["message"]?></textarea><br>
		Вмъкнете параметър:
		<select id="txtParam" onChange="javascript:insertTxtParam();">
			<option value="0">Изберете параметър</option>
			<?
			foreach ($discountParameters as $name => $paramList) {
				?><optgroup label="<?=$name?>"><?
				foreach ($paramList as $v) {
				?><option value="<?=$v?>"><?=$v?></option><?
				}
				?></optgroup><?
			}
			?>
			</optgroup>
		</select>
	</td>
</tr>
</table>
</fieldset>


<fieldset>
<legend>Критерии по съставни продукти</legend>
<table cellpadding="10" style="width: 100%">
<tr>
	<th>
	Съставни продукти
	</th>
	<td>
	<select name="bundleProducts" multiple="multiple" title="Изберете съставен продукт">
	<? foreach ($productList as $id => $name) {
		if (@in_array($id, $discount["bundleProducts"])) {
			?><option value="<?=$id?>" selected><?=$name?></option><?
		} else {
			?><option value="<?=$id?>"><?=$name?></option><?
		}
	}?>
	</select>
	</td>
</tr><tr>
	<th>
		Обща сума на поръчките на тези продукти до момента
	</th>
	<td>
		<input type="text" name="orderSumForProduct" value="<?=@$discount["rules"]["orderSumForProduct"]?>">
	</td>
</tr><tr>
	<th>
		Общ брой на поръчките на тези продукти до момента
	</th>
	<td>
		<input type="text" name="orderCountForProduct" value="<?=@$discount["rules"]["orderCountForProduct"]?>">
	</td>
</tr><tr>
	<th>
	Категория
	</th>
	<td>
	<table><tr>
		<td>
		<select name="category.categoryList" multiple="multiple" title="Изберете категория">
		<? foreach ($categoryList as $id => $name) {
			if (@in_array($id, $discount["category"]["categoryList"])) {
				?><option value="<?=$id?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$id?>"><?=$name?></option><?
			}
		}?>
		</select>
		</td>
		<td>&nbsp;</td>
		<th>
			Брой продукти
		</th><td>
			<input type="text" name="category.categoryProductCount" value="<?=@$discount["category"]["categoryProductCount"]?>">
		</td>
	</tr></table>
	</td>
</tr><tr>
	<th>
	Издателство
	</th>
	<td>
	<table><tr>
		<td>
		<select name="category.publisherList" multiple="multiple" title="Изберете издател">
		<? foreach ($publisherList as $id => $name) {
			if (@in_array($id, $discount["category"]["publisherList"])) {
				?><option value="<?=$id?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$id?>"><?=$name?></option><?
			}
		}?>
		</select>
		</td>
		<td>&nbsp;</td>
		<th>
			Брой продукти
		</th><td>
			<input type="text" name="category.publisherProductCount" value="<?=@$discount["category"]["publisherProductCount"]?>">
		</td>
	</tr></table>
	</td>
</tr><tr>
	<th>
	Автор
	</th>
	<td>
	<table><tr>
		<td>
		<select name="category.authorList" multiple="multiple" title="Изберете автор">
		<? foreach ($authorList as $id => $name) {
			if (@in_array($id, $discount["category"]["authorList"])) {
				?><option value="<?=$id?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$id?>"><?=$name?></option><?
			}
		}?>
		</select>
		</td>
		<td>&nbsp;</td>
		<th>
			Брой продукти
		</th><td>
			<input type="text" name="category.authorProductCount" value="<?=@$discount["category"]["authorProductCount"]?>">
		</td>
	</tr></table>
	</td>
</tr>
</table>
</fieldset>

<fieldset>
<legend>Общи критерии</legend>
<table cellpadding="10" style="width: 100%">
<tr>
	<th>
		Обща сума на кошницата
	</th>
	<td>
		<input type="text" name="subTotal" value="<?=@$discount["rules"]["subTotal"]?>">
	</td>
</tr><tr>
	<th>
		Брой продукти в кошницата
	</th>
	<td>
		<input type="text" name="productCount" value="<?=@$discount["rules"]["productCount"]?>">
	</td>
</tr><tr>
	<th>
		Купон
	</th>
	<td>
		<select name="couponList" multiple="multiple" title="Изберете купон">
		<? foreach ($couponList as $id => $name) {
			if (@in_array($id, $discount["rules"]["couponList"])) {
				?><option value="<?=$id?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$id?>"><?=$name?></option><?
			}
		}?>
		</select>
	</td>
</tr>
</table>
</fieldset>

<fieldset>
<legend>Критерии по дата и час</legend>
<table cellpadding="10" style="width: 100%">
<tr>
	<th>
		Период на валидност
	</th>
	<td>
	<table><tr>
		<th>от</th>
		<td><input type="text" name="fromDate" value="<?=@$discount["rules"]["fromDate"]?>"></td>
		<td>&nbsp;</td>
		<th>до</th>
		<td><input type="text" name="toDate" value="<?=@$discount["rules"]["toDate"]?>"></td>
	</tr></table>
	</td>
</tr><tr>
	<th>
	Ден от седмицата
	</th>
	<td>
		<select name="weekdayList" multiple="multiple" title="Изберете ден от седмицата">
		<? foreach ($weekdayList as $id => $name) {
			if (@in_array($id, @$discount["rules"]["weekdayList"])) {
				?><option value="<?=$id?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$id?>"><?=$name?></option><?
			}
		}?>
		</select>
	</td>
</tr><tr>
	<th>
	Часова зона
	</th>
	<td>
		<table><tr>
		<th>от</th>
		<td><input type="text" name="fromTime" value="<?=@$discount["rules"]["fromTime"]?>"></td>
		<td>&nbsp;</td>
		<th>до</th>
		<td><input type="text" name="toTime" value="<?=@$discount["rules"]["toTime"]?>"></td>
	</tr></table>
	</td>
</tr>
</table>
</fieldset>

<fieldset>
<legend>Критерии по клиент</legend>
<table cellpadding="10" style="width: 100%">
<tr>
	<th>
		Клиент
	</th>
	<td>
		<select name="customer.customerList" multiple="multiple" title="Изберете клиент">
		<? foreach ($userList as $id => $name) {
			if (@in_array($id, $discount["customer"]["customerList"])) {
				?><option value="<?=$id?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$id?>"><?=$name?></option><?
			}
		}?>
		</select>
	</td>
</tr><tr>
	<th>
		Брой поръчки на клиента
	</th>
	<td>
		<input type="text" name="customer.orderCount" value="<?=@$discount["customer"]["orderCount"]?>">
	</td>
</tr><tr>
	<th>
		Обща сума на поръчките на клиента
	</th>
	<td>
		<input type="text" name="customer.orderSum" value="<?=@$discount["customer"]["orderSum"]?>">
	</td>
</tr><tr>
	<th>
		Възраст
	</th>
	<td>
		<table><tr>
		<th>от</th>
		<td><input type="text" name="customer.fromAge" value="<?=@$discount["customer"]["fromAge"]?>"></td>
		<td>&nbsp;</td>
		<th>до</th>
		<td><input type="text" name="customer.toAge" value="<?=@$discount["customer"]["toAge"]?>"></td>
	</tr></table>
	</td>
</tr><tr>
	<th>
		Пол
	</th>
	<td>
		<select name="customer.sexList" multiple="multiple" title="Изберете Пол">
		<? foreach ($sexList as $name) {
			if (@in_array($name, $discount["customer"]["sexList"])) {
				?><option value="<?=$name?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$name?>"><?=$name?></option><?
			}
		}?>
		</select>
	</td>
</tr><tr>
	<th>
		Зодия
	</th>
	<td>
		<select name="customer.zodiacList" multiple="multiple" title="Изберете Зодия">
		<? foreach ($zodiacList as $name) {
			if (@in_array($name, $discount["customer"]["zodiacList"])) {
				?><option value="<?=$name?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$name?>"><?=$name?></option><?
			}
		}?>
		</select>
	</td>
</tr><tr>
	<th>
		Населено място
	</th>
	<td>
		<select name="customer.cityList" multiple="multiple" title="Изберете населено място">
		<? foreach ($cityList as $name) {
			if (@in_array($name, $discount["customer"]["cityList"])) {
				?><option value="<?=$name?>" selected><?=$name?></option><?
			} else {
				?><option value="<?=$name?>"><?=$name?></option><?
			}
		}?>
		</select>
	</td>
</tr><tr>
	<th>
		Дата на последно логване
	</th>
	<td>
		<table><tr>
			<th>от</th>
			<td><input type="text" name="customer.lastLoginFromDate" value="<?=@$discount["customer"]["lastLoginFromDate"]?>"></td>
			<td>&nbsp;</td>
			<th>до</th>
			<td><input type="text" name="customer.lastLoginToDate" value="<?=@$discount["customer"]["lastLoginToDate"]?>"></td>
		</tr></table>
	</td>
</tr><tr>
	<th>
		Дата на последна поръчка
	</th>
	<td>
		<table><tr>
			<th>от</th>
			<td><input type="text" name="customer.lastOrderFromDate" value="<?=@$discount["customer"]["lastOrderFromDate"]?>"></td>
			<td>&nbsp;</td>
			<th>до</th>
			<td><input type="text" name="customer.lastOrderToDate" value="<?=@$discount["customer"]["lastOrderToDate"]?>"></td>
		</tr></table>
	</td>
</tr>
</table>
</fieldset>

<fieldset>
<legend>Безплатни продукти</legend>
<table cellpadding="10" style="width: 100%">
<tr>
	<th>
	Безплатни продукти
	</th>
	<td>
	<select name="giftProducts" multiple="multiple" title="Изберете безплатен продукт">
	<? foreach ($giftProductList as $id => $name) {
		if (@in_array($id, $discount["rules"]["giftProducts"])) {
			?><option value="<?=$id?>" selected><?=$name?></option><?
		} else {
			?><option value="<?=$id?>"><?=$name?></option><?
		}
	}?>
	</select>
	</td>
</tr><tr>
	<th>
		Продуктът с най-ниската цена е безплатен<br>(валидно е при въведено ограничение по брой продукти в кошницата) 
	</th>
	<td>
		<input type="checkbox" name="minPriceGiftProduct" value=""<?=(!empty($discount["rules"]["minPriceGiftProduct"]) ? "checked" : "")?>>
	</td>
</tr>
</table>
</fieldset>

<br><br>
<div style="width: auto; text-align: center;">
		<input type="submit" value="Запази и затвори">
		<input type="button" value="Затвори" onclick="javascript:window.location.href='index.php?option=com_wphdiscount'">
</div>
</form>
<br><br><br><br><br><br><br>

<script type="text/javascript">
function prepareSubmit() {
	/*if (parseInt($j('[name=price]').val()).toString() == 'NaN') {
		alert("Моля въведете стойността на отстъпката");
		return false;
	}*/

	$j('#discountForm input:checkbox:checked').val(1);
	$j('#discountForm input:checkbox:not(:checked)').val(0);
	$j('[name=json]').val(JSON.stringify(form2object('discountForm')));

	return true;
}

function insertTxtParam() {
	if ($j("#txtParam").val() == '0') { return true; }
	var msg = $j('[name=message]').val();
	$j('[name=message]').val(msg + '{' + $j("#txtParam").val() + '}');
	$j("#txtParam").val(0);
	$(textAreaId).focus();
}

</script>
