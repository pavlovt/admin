<?php
// print article with no interface
global $mainframe;

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
		
	} else {
		exit("Не сте абонирани за тази услуга");
	
	}
	
} else {
	exit($connectionError);
}


$filter = " data.id > 0 ";

// if no filter selected show the articles from the currrent day
if (!stristr(http_build_query($_POST), "filter")) {
	$_POST["filterByCategory"] = $fieldList["clippingCategory"]["value"];
	$_POST["filterByDate"] = date("Y-m-d");
//if no category is selected show only categories available to this user
} elseif (!stristr(http_build_query($_POST), "filterByCategory")) {
	$_POST["filterByCategory"] = !empty($fieldList["clippingCategory"]["value"]) ? $fieldList["clippingCategory"]["value"] : "";
}

if (stristr(http_build_query($_POST), "filter")) {
	//print_r($_POST);exit;
	if(!empty($_POST["filterByCategory"])) {
		// get sub categories
		$r = @$dbClip->query("SELECT id FROM cats WHERE pid in (".$dbClip->quote($_POST["filterByCategory"]).")", PDO::FETCH_NUM);
		$categoryList = array();
		foreach ($r as $v) {
			$categoryList[] = $v[0];
		}
		
		if (empty($categoryList)) {
			$categoryList = explode(",", $_POST["filterByCategory"]);
		}

		$filter .= " AND cats.id in (".implode(",", $categoryList).")";
	}

	if(!empty($_POST["filterBySource"])) {
		$filter .= " AND sources.id in (".$dbClip->quote($_POST["filterBySource"]).")";
	}
	
	if(!empty($_POST["filterBySourceType"])) {
		$filter .= " AND sources.type in (".$dbClip->quote($_POST["filterBySourceType"]).")";
	}

	if(!empty($_POST["filterByDate"])) {
		$_POST["filterByDate"] = date("Y-m-d", strtotime($_POST["filterByDate"]));
		$filter .= " AND data.date = ".$dbClip->quote($_POST["filterByDate"]);
	}
	
	if(!empty($_POST["filterByDateInterval"])) {
		$_POST["filterByDateInterval"] = explode(",", $_POST["filterByDateInterval"]);
		$filter .= " AND data.date BETWEEN ".$dbClip->quote($_POST["filterByDateInterval"][0])." AND ".$dbClip->quote($_POST["filterByDateInterval"][1]);
	}
	
	if(!empty($_POST["filterByText"])) {
		$filter .= " AND match(title,subtitle,author,resume,body) against(".$dbClip->quote($_POST["filterByText"]).") ";
	}

//print_r($_POST);	
//print_r($filter);exit;

	$query = "
		SELECT 
			data.id, data.title, data.subtitle, data.resume, data.body, data.author, data.date, CONCAT(sources.prefix,' ',sources.name) sourceName
		FROM 
			(data) 
			INNER JOIN sources ON (sources.id = data.source AND sources.active = 1) 
			INNER JOIN data_cats ON (data_cats.did = data.id) 
			INNER JOIN cats ON (cats.id = data_cats.cid AND cats.active = 1)
		WHERE
			".$filter." 
		ORDER BY 
			data.id DESC
			";

	//exit('qq'.$query);
	$r = $dbClip->query($query, PDO::FETCH_OBJ);
	$html = "";
	if ($r) {
		foreach ($r as $k => $v) {
			$html .= "
			<br />
			{$v->title}<br />
			".($v->subtitle ? $v->subtitle.'<br />' : '')."
			
			{$v->sourceName} | {$v->date}<br />
			
			{$v->resume}<br />
			{$v->body}<br />
			<br /><br />
			
			";
			
		}
		

	}
	
	//exit($html);
	
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
	//$pdf->setFontSubsetting(true);
	$pdf->setFontSubsetting(false);

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

	
}