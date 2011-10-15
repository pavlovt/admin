<?php
// print article with no interface
global $mainframe;

//print_r($_POST);exit;
$_GET["dataId"] = (int)$_GET["dataId"];
if(empty($_GET["dataId"])) {
	exit("На страницата са подадени невалидни параметри");
}

if (!$mainframe) {
	ini_set('display_errors','1');
	
	define( '_JEXEC', 1 );
	define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))));
	define( 'DS', DIRECTORY_SEPARATOR );
	
	require_once (JPATH_BASE . DS . 'includes' . DS . 'defines.php');
	require_once (JPATH_BASE . DS . 'includes' . DS . 'framework.php');
	
	$mainframe = JFactory::getApplication('site');
}


require_once JPATH_ADMINISTRATOR.'/components/com_cck/common/config.php';
require_once JPATH_ADMINISTRATOR.'/components/com_cck/common/functions.php';
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.data.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.dataTextField.php");
require_once(JPATH_ADMINISTRATOR."/components/com_cck/class/class.contentTypeField.php");

$db =& JFactory::getDBO();
$user = & JFactory::getUser();

$data = new data($db);
$dataField = new dataField($db);
$dataTextField = new dataTextField($db);
$contentTypeField = new contentTypeField($db);

//var_dump($_SESSION["clippingRealCategory"]);exit;
if($user->guest) {
	exit($notLoggedInMessage);
	
// if registered user then check if he is subscribed for this service
} elseif($dbClip) {
	$db->setQuery("SELECT dataId FROM bak_cck_data_field WHERE name='userId' AND value=".$user->id);
	if (($dataId = $db->loadResult()) && $data->loadById($dataId)) {
		$fieldList = $data->loadFieldList($contentTypeField, $dataField, $dataTextField);
		
		// if clipping is not activated in he user's profile display error
		if (empty($fieldList["clipping"]["value"])) {
			exit("Не сте абонирани за тази услуга");
		}
		
		// this variable is defined in clipping.php - if it does not exists then user directly started this file without going throught clipping system which is not allowed
		if (empty($_SESSION["clippingRealCategory"]) || ($_SESSION["clippingUserId"] != $user->id)) {
			exit("Директният достъп до този файл е забранен");
		}
		
		$query = "SELECT COUNT(did) count FROM data_cats WHERE did=".$_GET["dataId"]." AND cid in (".$_SESSION["clippingRealCategory"].")";
		$r = $dbClip->query($query, PDO::FETCH_OBJ);
		if (!$r || empty($r->fetch()->count)) {
			exit("Не сте абонирани за тази услуга");
		}
		
	} else {
		exit("Не сте абонирани за тази услуга");
	
	}
	
} else {
	exit($connectionError);
}
	
$query = "
	SELECT 
		data.id, data.title, data.subtitle, data.body, data.author, data.date, CONCAT(sources.prefix,' ',sources.name) sourceName
	FROM 
		(data) 
		INNER JOIN sources ON (sources.id = data.source AND sources.active = 1) 
		INNER JOIN data_cats ON (data_cats.did = data.id) 
		INNER JOIN cats ON (cats.id = data_cats.cid AND cats.active = 1)
	WHERE
		data.id = ".(int)$_GET["dataId"];

//exit('qq'.$query);
$r = $dbClip->query($query, PDO::FETCH_OBJ);
$html = "";
if ($r) {
		$v = $r->fetch();
		$html = "
		<br />
		{$v->title}<br />
		".($v->subtitle ? $v->subtitle.'<br />' : '')."
		
		{$v->sourceName} | ".date("d.m.Y", strtotime($v->date))."<br />
		
		{$v->resume}<br />
		{$v->body}<br />
		<br /><br />
		
		";
	
} else {
	exit($connectionError);
}

//echo $html;
//exit($html);

if ($_GET["getPdf"] == 1) {
	require_once(JPATH_ADMINISTRATOR.'/components/com_cck/class/tcpdf/config/lang/rus.php');
	require_once(JPATH_ADMINISTRATOR.'/components/com_cck/class/tcpdf/tcpdf.php');

	// Extend the TCPDF class to create custom Header and Footer
	class MYPDF extends TCPDF {

		//Page header
		public function Header() {
			// Logo
			//$image_file = K_PATH_IMAGES.'api.jpg';
			//$this->Image($image_file, 5, 10, 200, 15);
			// Set font
			/*$this->SetFont('helvetica', 'B', 20);
			// Title
			$this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');*/
		}

		// Page footer
		public function Footer() {
			//empty footer
		}
	}

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	//$pdf->SetCreator(PDF_CREATOR);
	/*$pdf->SetAuthor('Nicola Asuni');
	$pdf->SetTitle('TCPDF Example 003');
	$pdf->SetSubject('TCPDF Tutorial');
	$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
	*/
	// set default header data
	//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	//set some language-dependent strings
	$pdf->setLanguageArray($l);

	// ---------------------------------------------------------


	// set default font subsetting mode
	$pdf->setFontSubsetting(true);

	// Set font
	// dejavusans is a UTF-8 Unicode font, if you only need to
	// print standard ASCII chars, you can use core fonts like
	// helvetica or times to reduce file size.
	$pdf->SetFont('dejavusans', '', 12, '', true);

	// Add a page
	// This method has several options, check the source code documentation for more information.
	$pdf->AddPage();

	//set some language-dependent strings
	$pdf->setLanguageArray($lg);

	$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

	//Close and output PDF document
	//$pdf->Output('/tmp/'.$_GET["dataId"].'.pdf', 'F');
	//$pdf->Output('article.pdf', 'I');
	$pdf->Output('article.pdf', 'D');
	//$pdf->Output('bulletin-'.date("d/m/Y H:s").'.pdf', 'D');

//if ($_GET["getPdf"] == 1)
} elseif (($_GET["sendEmail"] == 1)) {
	// get the recipient first and then send it
	if(!empty($_GET["sendTo"])) {
		if (sendMail($_GET["sendTo"], $v->title, $html)) {
			echo ("Съобщението ви беше успешно изпратено");
		} else {
			echo("Имаше проблем при изпращане на съобщението - моля опитайте по-късно");
		}
		
		//<button onclick="window.close();">Затвори прозореца</button>
		?>
		<script language="text/javascript">
		  <!--
		  window.close();
		  //-->
		</script>
		<?
	} else {?>
		<form>
			<input type="hidden" name="sendEmail" value="1" />
			<input type="hidden" name="dataId" value="<?=$_GET["dataId"]?>" />
			
			<label>Въведете имейла до който да се изпрати статията:</label><br>
			<input type="text" name="sendTo" value="" size="50" />
			<input type="submit" value="Изпрати" />
		</form>
		<?
	}
} else {
	echo $html;
	?>
	<script language="text/javascript">
	  <!--
	  window.print();
	  //-->
	</script>
	<?
}